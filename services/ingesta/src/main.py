"""Punto de entrada principal para el microservicio snm-ingesta.

Orquesta la inicialización de componentes, la conexión asíncrona a Mosquitto
y PostgreSQL, la canalización completa de procesamiento de paquetes y el
apagado ordenado ante señales de terminación del sistema operativo.
"""

from __future__ import annotations

import asyncio
import json
import logging
import os
import signal
import sys
from datetime import datetime, timezone
from typing import Any

import asyncpg
from aiomqtt import Client, MqttError
from meshtastic.protobuf import mesh_pb2, mqtt_pb2

from .airtime import calculate_airtime_ms
from .config import IngestaConfig, load_config
from .crypto import PacketDecryptor
from .decoder import check_ok_to_mqtt, decode_data_payload
from .dedup import PacketDeduplicator, ReceptionItem, UnifiedPacket
from .geo import GeoEngine
from .health import HealthReporter, start_health_server
from .links import evaluate_direct_link
from .migrations import MigrationRunner
from .publisher import DecodedPublisher
from .reboot import detect_node_reboot
from .registry import NodeRegistry
from .storage import StorageManager
from .validator import TopicValidator

# Configuración de registro de eventos
logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s [%(levelname)s] [%(name)s] %(message)s",
    handlers=[logging.StreamHandler(sys.stdout)],
)
logger = logging.getLogger("ingesta")


async def main() -> None:
    """Función principal asíncrona del microservicio."""
    logger.info("Iniciando servicio snm-ingesta (Andalucía Mesh)...")

    # 1. Carga y validación de configuración
    config: IngestaConfig = load_config()
    logger.setLevel(getattr(logging, config.LOG_LEVEL.upper(), logging.INFO))

    # 2. Inicialización del motor geoespacial
    logger.info("Cargando geometrías provinciales desde %s...", config.INGESTA_POLIGONOS)
    geo_engine = GeoEngine(
        geojson_path=config.INGESTA_POLIGONOS,
        margen_min_m=config.INGESTA_MARGEN_MIN_M,
        precision_max_m=config.INGESTA_PRECISION_MAX_M,
    )

    # 3. Conexión a PostgreSQL 17 y ejecución de migraciones
    logger.info(
        "Conectando a base de datos PostgreSQL en %s:%d/%s...",
        config.DB_HOST,
        config.DB_PORT,
        config.DB_NAME,
    )
    db_pool = await asyncpg.create_pool(
        host=config.DB_HOST,
        port=config.DB_PORT,
        database=config.DB_NAME,
        user=config.DB_USER,
        password=config.DB_PASSWORD,
        min_size=2,
        max_size=10,
    )

    # Directorio de migraciones SQL relativo a este script
    migrations_dir = os.path.join(os.path.dirname(__file__), "..", "migrations")
    migrator = MigrationRunner(
        db_pool=db_pool,
        migrations_dir=migrations_dir,
        tz=config.TZ,
        infra_roles=config.infra_roles_list,
    )
    await migrator.run_all()

    # 4. Inicialización de estructuras en memoria y reportería
    registry = NodeRegistry()
    reporter = HealthReporter()

    # Precargar últimos 15 min de paquetes y estado de nodos para deduplicación tras reinicio
    async with db_pool.acquire() as conn:
        logger.info("Precargando últimos nodos y gateways conocidos de la base de datos...")
        node_rows = await conn.fetch("SELECT * FROM node WHERE last_seen >= now() - INTERVAL '30 days';")
        now_utc = datetime.now(timezone.utc)
        for r in node_rows:
            n = registry.get_or_create_node(r["id"], r["last_seen"])
            n.short_name = r["short_name"]
            n.long_name = r["long_name"]
            n.role = r["role"]
            n.hw_model = r["hw_model"]
            n.firmware = r["firmware"]
            n.latitude = r["latitude"]
            n.longitude = r["longitude"]
            n.position_precision_m = r["position_precision_m"]
            n.province = r["province"]
            n.is_gateway = r["is_gateway"]
            n.battery_level = r["battery_level"]
            n.channel_utilization = r["channel_utilization"]
            n.uptime_seconds = r["uptime_seconds"]
            n.first_seen = r["first_seen"]
            n.last_seen = r["last_seen"]

        gw_rows = await conn.fetch("SELECT * FROM gateway;")
        for g in gw_rows:
            gw = registry.gateways.get(g["id"])
            if gw is None:
                gw = registry.gateways[g["id"]] = registry.gateways.get(g["id"]) or registry.get_or_create_node(g["id"], g["last_message_at"])
            registry.record_gateway_seen(g["id"], g["last_message_at"])

    # Limpiar estado dirty de la precarga
    registry.dirty_nodes.clear()
    registry.dirty_gateways.clear()
    logger.info("Precarga finalizada: %d nodos, %d gateways.", len(registry.nodes), len(registry.gateways))

    # 5. Componentes de procesamiento
    validator = TopicValidator(
        allowed_channels=config.channels_list,
        topic_root=config.MQTT_TOPIC_ROOT,
        topic_prefix=config.MQTT_TOPIC_PREFIX,
    )
    decryptor = PacketDecryptor(config.CHANNEL_KEY_DEFAULT)

    storage = StorageManager(
        db_pool=db_pool,
        registry=registry,
        batch_rows=config.INGESTA_LOTE_FILAS,
        batch_interval_ms=config.INGESTA_LOTE_MS,
        max_buffer_rows=config.INGESTA_BUFFER_MAX_FILAS,
    )
    storage.start_background_tasks()

    publisher: DecodedPublisher | None = None  # Se instanciará al conectar MQTT

    # Callbacks de deduplicación
    async def on_window_closed(pkt: UnifiedPacket) -> None:
        reporter.paquetes_unicos += 1
        storage.enqueue_unified_packet(pkt)
        if publisher:
            await publisher.publish_packet(pkt)

    async def on_late_reception(pkt: UnifiedPacket, r: ReceptionItem) -> None:
        reporter.recepciones_tardias += 1
        storage.enqueue_late_reception(pkt, r)

    dedup = PacketDeduplicator(
        on_window_closed=on_window_closed,
        on_late_reception=on_late_reception,
        publish_window_s=config.INGESTA_VENTANA_PUBLICACION_S,
        dedup_window_min=config.INGESTA_VENTANA_DEDUP_MIN,
    )

    # Enlazar estadísticas con el servidor de salud
    reporter.get_decoded_stats = lambda: (
        publisher.decoded_enviados if publisher else 0,
        publisher.decoded_perdidos if publisher else 0,
    )
    reporter.get_storage_stats = lambda: (
        storage.total_queued_rows,
        storage.filas_guardadas_bd,
        storage.filas_descartadas_bd,
        storage.db_status,
    )

    # 6. Iniciar servidor HTTP /health
    logger.info("Iniciando servidor de salud en puerto %d...", config.HEALTH_PORT)
    health_runner = await start_health_server(reporter, port=config.HEALTH_PORT)

    # 7. Control de señales para apagado ordenado
    stop_event = asyncio.Event()

    def handle_signal() -> None:
        logger.info("Señal de apagado recibida. Deteniendo ingesta...")
        stop_event.set()

    loop = asyncio.get_running_loop()
    for sig in (signal.SIGINT, signal.SIGTERM):
        loop.add_signal_handler(sig, handle_signal)

    # 8. Bucle principal con cliente MQTT y reconexión automática
    backoff = 1.0
    while not stop_event.is_set():
        try:
            logger.info(
                "Conectando cliente MQTT a %s:%d (usuario: %s)...",
                config.MQTT_HOST,
                config.MQTT_PORT,
                config.MQTT_USER,
            )
            async with Client(
                hostname=config.MQTT_HOST,
                port=config.MQTT_PORT,
                username=config.MQTT_USER,
                password=config.MQTT_PASSWORD,
                client_id="ingesta",
                clean_session=True,
                keepalive=60,
            ) as client:
                logger.info("Cliente MQTT conectado exitosamente.")
                reporter.mqtt_connected = True
                backoff = 1.0

                publisher = DecodedPublisher(
                    mqtt_client=client,
                    registry=registry,
                    topic_prefix=config.MQTT_TOPIC_PREFIX,
                )

                # Suscribirse a tópicos requeridos
                topics = [
                    (f"{config.MQTT_TOPIC_ROOT}/2/e/#", 0),
                    (f"{config.MQTT_TOPIC_ROOT}/2/map/#", 0),
                    (f"{config.MQTT_TOPIC_PREFIX}/v1/peer/#", 0),
                ]
                for t, qos in topics:
                    await client.subscribe(t, qos=qos)
                    logger.info("Suscrito a tópico MQTT: %s", t)

                # Bucle de recepción de mensajes
                async for message in client.messages:
                    if stop_event.is_set():
                        break

                    reporter.record_incoming_message()
                    topic_str = str(message.topic)
                    raw_payload = bytes(message.payload)

                    # 1. Validar tópico y extraer metadatos
                    t_info = validator.parse_topic(topic_str, len(raw_payload))
                    if t_info.topic_type == "unknown":
                        reporter.descartado_canal += 1
                        continue

                    now = datetime.now(timezone.utc)

                    # 2. Desempaquetar ServiceEnvelope o evento federado
                    envelope = mqtt_pb2.ServiceEnvelope()
                    try:
                        envelope.ParseFromString(raw_payload)
                    except Exception:
                        reporter.descartado_protobuf += 1
                        continue

                    if not envelope.HasField("packet"):
                        reporter.descartado_protobuf += 1
                        continue

                    packet = envelope.packet
                    packet_id = packet.id

                    # Regla TR-02: Acceso protegido al campo 'from'
                    from_num = getattr(packet, "from", 0)
                    to_num = getattr(packet, "to", 0)

                    if from_num == 0 or packet_id == 0:
                        continue

                    from_id = f"!{from_num:08x}"
                    to_id = "^all" if to_num in (0xFFFFFFFF, 0) else f"!{to_num:08x}"

                    # Obtener y validar gateway
                    gw_raw = envelope.gateway_id or t_info.gateway_id or ""
                    gateway_id = f"!{gw_raw.lstrip('!'):0>8}".lower()
                    if len(gateway_id) != 9:
                        reporter.descartado_gateway += 1
                        continue

                    # Registrar actividad viva de nodo y gateway
                    registry.record_gateway_seen(gateway_id, now)
                    node_rec = registry.get_or_create_node(from_id, now)

                    # 3. Descifrado y decodificación
                    data_obj, decrypt_status = decryptor.decrypt_packet(packet)
                    if decrypt_status == "pki":
                        reporter.pki += 1
                        continue
                    elif decrypt_status == "cifrado_desconocido":
                        reporter.cifrado_desconocido += 1

                    if data_obj:
                        decoded_res = decode_data_payload(from_id, gateway_id, data_obj)
                        if not decoded_res.is_valid_consent:
                            reporter.sin_ok_mqtt += 1
                            continue
                        portnum_name = decoded_res.portnum_name
                        portnum_num = decoded_res.portnum_num
                        variant = decoded_res.variant
                        payload_dict = decoded_res.payload_dict
                        ok_to_mqtt = decoded_res.ok_to_mqtt
                    else:
                        portnum_name = "other"
                        portnum_num = 0
                        variant = None
                        payload_dict = {}
                        ok_to_mqtt = None

                    # 4. Actualizar metadatos del nodo según tipo de paquete recibido
                    if portnum_name == "nodeinfo" and payload_dict:
                        node_rec.short_name = payload_dict.get("short_name", node_rec.short_name)
                        node_rec.long_name = payload_dict.get("long_name", node_rec.long_name)
                        node_rec.role = payload_dict.get("role", node_rec.role)
                        node_rec.hw_model = payload_dict.get("hw_model", node_rec.hw_model)
                        node_rec.public_key_fp = payload_dict.get("public_key_fp", node_rec.public_key_fp)
                        node_rec.metrics_at = now
                        registry.dirty_nodes.add(from_id)

                    elif portnum_name == "position" and payload_dict:
                        p_lat = payload_dict.get("latitude")
                        p_lon = payload_dict.get("longitude")
                        p_prec = payload_dict.get("precision_m", 10.0)
                        if p_lat is not None and p_lon is not None:
                            prov, uncertain = geo_engine.resolve_province(p_lat, p_lon, p_prec)
                            node_rec.latitude = p_lat
                            node_rec.longitude = p_lon
                            node_rec.position_precision_m = p_prec
                            node_rec.position_source = "position"
                            node_rec.last_position_at = now
                            node_rec.province = prov
                            node_rec.border_uncertain = uncertain
                            payload_dict["border_uncertain"] = uncertain
                            registry.dirty_nodes.add(from_id)

                    elif portnum_name == "telemetry" and payload_dict:
                        if "device_metrics" in payload_dict:
                            dm = payload_dict["device_metrics"]
                            node_rec.battery_level = dm.get("battery_level", node_rec.battery_level)
                            node_rec.voltage = dm.get("voltage", node_rec.voltage)
                            node_rec.battery_at = now
                            node_rec.channel_utilization = dm.get("channel_utilization", node_rec.channel_utilization)
                            node_rec.air_util_tx = dm.get("air_util_tx", node_rec.air_util_tx)
                            node_rec.metrics_at = now

                            # Detección de reinicios
                            uptime = dm.get("uptime_seconds")
                            if uptime is not None:
                                prev_ts = node_rec.uptime_at.timestamp() if node_rec.uptime_at else None
                                is_reboot = detect_node_reboot(uptime, now.timestamp(), node_rec.uptime_seconds, prev_ts)
                                if is_reboot:
                                    reporter.reinicios_detectados += 1
                                    node_rec.last_reboot_at = now
                                    dm["reboot"] = True
                                node_rec.uptime_seconds = uptime
                                node_rec.uptime_at = now

                            registry.dirty_nodes.add(from_id)

                    # 5. Cálculos de enlace y tiempo de aire
                    hop_start = packet.hop_start if packet.hop_start else None
                    hop_limit = packet.hop_limit if packet.hop_limit else None
                    hops = None
                    if hop_start is not None and hop_limit is not None:
                        calc_hops = hop_start - hop_limit
                        if 0 <= calc_hops <= 7:
                            hops = calc_hops

                    payload_len = len(packet.encrypted) if packet.encrypted else (len(data_obj.payload) if data_obj else 0)
                    airtime = calculate_airtime_ms(
                        payload_len=payload_len,
                        spread_factor=config.LORA_SPREAD_FACTOR,
                        bandwidth_khz=config.LORA_BANDWIDTH,
                        coding_rate_code=config.LORA_CODING_RATE,
                        preamble_symbols=config.INGESTA_LORA_PREAMBULO,
                        is_map_report=(t_info.topic_type == "map"),
                    )

                    node_coords = registry.get_node_coords(from_id)
                    gw_coords = registry.get_node_coords(gateway_id)
                    is_direct, dist_km, is_susp = evaluate_direct_link(
                        hops=hops,
                        via_mqtt=packet.via_mqtt,
                        from_id=from_id,
                        gateway_id=gateway_id,
                        node_coords=node_coords,
                        gw_coords=gw_coords,
                        precision_max_m=config.INGESTA_ENLACE_PRECISION_MAX_M,
                        max_link_km=config.INGESTA_ENLACE_MAX_KM,
                    )

                    reception = ReceptionItem(
                        gateway_id=gateway_id,
                        snr=round(packet.rx_snr, 2) if packet.rx_snr else None,
                        rssi=packet.rx_rssi if packet.rx_rssi else None,
                        hop_limit=hop_limit,
                        hops=hops,
                        relay_node=getattr(packet, "relay_node", None),
                        gw_rx_time=datetime.fromtimestamp(packet.rx_time, timezone.utc) if packet.rx_time else None,
                        at=now,
                        own=(from_id == gateway_id),
                        direct=is_direct,
                        distance_km=dist_km,
                    )

                    unified_candidate = UnifiedPacket(
                        rx_first=now,
                        from_id=from_id,
                        to_id=to_id,
                        packet_id=packet_id,
                        channel=t_info.channel,
                        portnum=portnum_name,
                        portnum_num=portnum_num,
                        variant=variant,
                        decrypt_status=decrypt_status,
                        hop_start=hop_start,
                        hops_min=hops,
                        want_ack=packet.want_ack,
                        via_mqtt=packet.via_mqtt,
                        ok_to_mqtt=ok_to_mqtt,
                        size_bytes=16 + payload_len,
                        airtime_ms=airtime,
                        first_gateway=gateway_id,
                        province=node_rec.province,
                        payload=payload_dict,
                    )

                    # 6. Despachar a deduplicación
                    status = await dedup.process_reception(unified_candidate, reception)
                    if status in ("RECEPTION_WINDOW", "DUPLICATE_GATEWAY"):
                        reporter.duplicados += 1

        except MqttError as e:
            logger.warning("Error de conexión MQTT: %s. Reintentando en %.1f s...", e, backoff)
            reporter.mqtt_connected = False
            await asyncio.sleep(backoff)
            backoff = min(backoff * 2.0, 30.0)
        except Exception as e:
            logger.error("Excepción inesperada en bucle de ingesta: %s", e, exc_info=True)
            await asyncio.sleep(1.0)

    # 9. Drenaje y apagado ordenado
    logger.info("Cerrando ventanas abiertas de deduplicación...")
    await dedup.flush_all_open_windows()

    logger.info("Drenando almacenamiento y volcando estado de registros...")
    await storage.stop()

    logger.info("Deteniendo servidor de salud...")
    await health_runner.cleanup()

    logger.info("Cerrando pool de base de datos...")
    await db_pool.close()

    logger.info("Servicio snm-ingesta finalizado correctamente.")


if __name__ == "__main__":
    asyncio.run(main())
