"""Cliente y procesador MQTT para el microservicio adaptador-potato.

Suscribe al broker interno de Mosquitto, filtra topics, descifra paquetes
y mapea las cargas protobuf a las llamadas REST correspondientes de PotatoMesh.
"""

from __future__ import annotations

import asyncio
import logging
import time
from typing import Any

import aiomqtt
from meshtastic.protobuf import config_pb2, mesh_pb2, mqtt_pb2, telemetry_pb2

from .config import Config
from .crypto import decrypt_packet, expand_key
from .dedup import Deduplicator
from .sender import PotatoSender

logger = logging.getLogger("adaptador-potato.mqtt")


class MqttProcessor:
    """Suscriptor y procesador de tráfico MQTT de radio."""

    def __init__(
        self,
        config: Config,
        dedup: Deduplicator,
        sender: PotatoSender,
    ) -> None:
        """Inicializa el procesador MQTT.

        Args:
            config: Configuración global del servicio.
            dedup: Instancia del filtro de deduplicación.
            sender: Despachador hacia la API de PotatoMesh.
        """
        self.config = config
        self.dedup = dedup
        self.sender = sender
        self.aes_key = expand_key(config.channel_key_default)

        # Estado de conexión MQTT
        self.is_connected: bool = False
        self.disconnected_since: float | None = None
        self._running: bool = False

    async def run(self) -> None:
        """Bucle de conexión persistente con reconexión exponencial."""
        self._running = True
        backoff = 1.0
        max_backoff = 60.0

        topic_filter = f"{self.config.mqtt_topic_root}/#"

        while self._running:
            try:
                logger.info(
                    "Conectando a broker MQTT en %s:%d como usuario '%s'...",
                    self.config.mqtt_host,
                    self.config.mqtt_port,
                    self.config.mqtt_user,
                )
                async with aiomqtt.Client(
                    hostname=self.config.mqtt_host,
                    port=self.config.mqtt_port,
                    username=self.config.mqtt_user,
                    password=self.config.mqtt_password,
                    identifier="snm-adaptador-potato",
                    clean_session=True,
                    timeout=30.0,
                ) as client:
                    self.is_connected = True
                    self.disconnected_since = None
                    backoff = 1.0
                    logger.info(
                        "Conexión MQTT establecida. Suscribiendo a %s", topic_filter
                    )
                    await client.subscribe(topic_filter, qos=0)

                    async for message in client.messages:
                        if not self._running:
                            break
                        try:
                            self.process_message(
                                message.topic.value, message.payload
                            )
                        except Exception as exc:
                            logger.error(
                                "Error procesando mensaje en topic %s: %s",
                                message.topic.value,
                                exc,
                                exc_info=True,
                            )

            except aiomqtt.MqttError as exc:
                self.is_connected = False
                if self.disconnected_since is None:
                    self.disconnected_since = time.monotonic()
                logger.warning(
                    "Conexión MQTT perdida (%s). Reintentando en %.1fs...",
                    exc,
                    backoff,
                )
                await asyncio.sleep(backoff)
                backoff = min(backoff * 2.0, max_backoff)
            except asyncio.CancelledError:
                self.is_connected = False
                break
            except Exception as exc:
                self.is_connected = False
                if self.disconnected_since is None:
                    self.disconnected_since = time.monotonic()
                logger.error("Error inesperado en cliente MQTT: %s", exc, exc_info=True)
                await asyncio.sleep(backoff)
                backoff = min(backoff * 2.0, max_backoff)

    def process_message(self, topic: str, payload_bytes: bytes | bytearray) -> None:
        """Procesa y valida un mensaje MQTT recibido.

        Args:
            topic: Topic MQTT de publicación.
            payload_bytes: Carga binaria del paquete.
        """
        # Descartar topics incompatibles
        if any(ign in topic for ign in ("/json/", "/stat/", "/c/")):
            return

        parts = topic.split("/")

        # Caso MapReport: msh/EU_868/2/map/
        is_map_topic = "/2/map" in topic

        # Caso paquete canal: msh/EU_868/2/e/<canal>/<!id_gateway>
        channel_index = 0
        if not is_map_topic:
            try:
                # Localizar el segmento 'e'
                e_idx = parts.index("e")
                channel_str = parts[e_idx + 1]
            except (ValueError, IndexError):
                return

            res = self.config.get_channel_index_and_name(channel_str)
            if res is None:
                # Canal fuera de lista blanca
                return
            channel_index, _ = res

        # Deserializar sobre ServiceEnvelope
        envelope = mqtt_pb2.ServiceEnvelope()
        try:
            envelope.ParseFromString(bytes(payload_bytes))
        except Exception:
            return

        if not envelope.HasField("packet"):
            return

        packet: mesh_pb2.MeshPacket = envelope.packet
        from_node: int = getattr(packet, "from", 0)

        # Descartar paquetes con cabecera nula o corrupta
        if from_node == 0 or packet.id == 0:
            return

        # Deduplicación temporal en memoria
        if self.dedup.is_duplicate(from_node, packet.id):
            return

        rx_time = int(packet.rx_time) if packet.rx_time else int(time.time())
        node_id_str = f"!{from_node:08x}"

        # Obtener carga Data
        data: mesh_pb2.Data | None = None

        if packet.HasField("decoded"):
            data = packet.decoded
        elif packet.HasField("encrypted"):
            # Si está cifrado con PKI (DMs cifrados con clave pública), no procesar
            if packet.pki_encrypted:
                return

            data, status = decrypt_packet(
                packet.encrypted, packet.id, from_node, self.aes_key
            )
            if status != "ok" or data is None:
                # Paquete con clave desconocida: solo si es de difusión registrar actividad como mensaje cifrado
                if packet.to in (0xFFFFFFFF, 0):
                    self.sender.enqueue(
                        "messages",
                        {
                            "id": packet.id,
                            "from": node_id_str,
                            "to": "^all",
                            "channel": channel_index,
                            "rx_time": rx_time,
                            "encrypted": True,
                        },
                        priority=0,
                    )
                return
        else:
            return

        self._dispatch_payload(
            packet, data, channel_index, rx_time, node_id_str, from_node
        )

    def _dispatch_payload(
        self,
        packet: mesh_pb2.MeshPacket,
        data: mesh_pb2.Data,
        channel_index: int,
        rx_time: int,
        node_id_str: str,
        from_node: int,
    ) -> None:
        """Traduce la carga Data decodificada y la encola hacia el endpoint correspondiente."""
        portnum = data.portnum

        # 1. NODEINFO_APP (4)
        if portnum == 4:
            user = mesh_pb2.User()
            try:
                user.ParseFromString(data.payload)
                hw_name = (
                    mesh_pb2.HardwareModel.Name(user.hw_model)
                    if user.hw_model
                    else None
                )
                try:
                    role_name = config_pb2.Config.DeviceConfig.Role.Name(user.role)
                except (ValueError, TypeError):
                    role_name = "CLIENT"

                cached_metrics = self.sender.get_cached_node_metrics(node_id_str)

                node_data: dict[str, Any] = {
                    "num": from_node,
                    "user": {
                        "id": node_id_str,
                        "shortName": user.short_name or None,
                        "longName": user.long_name or None,
                        "hwModel": hw_name,
                        "role": role_name,
                    },
                    "hwModel": hw_name,
                    "lastHeard": rx_time,
                    "snr": packet.rx_snr,
                }
                if cached_metrics:
                    node_data["deviceMetrics"] = cached_metrics

                self.sender.enqueue("nodes", {node_id_str: node_data}, priority=0)
            except Exception as exc:
                logger.debug("Error deserializando NodeInfo: %s", exc)

        # 2. POSITION_APP (3)
        elif portnum == 3:
            pos = mesh_pb2.Position()
            try:
                pos.ParseFromString(data.payload)
                lat = (
                    pos.latitude_i / 1e7
                    if pos.latitude_i and pos.latitude_i != 0
                    else None
                )
                lon = (
                    pos.longitude_i / 1e7
                    if pos.longitude_i and pos.longitude_i != 0
                    else None
                )

                if lat is not None and lon is not None:
                    pos_payload = {
                        "id": packet.id,
                        "node_id": node_id_str,
                        "node_num": from_node,
                        "from_id": node_id_str,
                        "from": node_id_str,
                        "latitude": lat,
                        "longitude": lon,
                        "altitude": pos.altitude if pos.altitude else None,
                        "rx_time": rx_time,
                        "channel": channel_index,
                    }
                    self.sender.enqueue("positions", pos_payload, priority=1)
            except Exception as exc:
                logger.debug("Error deserializando Position: %s", exc)

        # 3. TELEMETRY_APP (67)
        elif portnum == 67:
            tel = telemetry_pb2.Telemetry()
            try:
                tel.ParseFromString(data.payload)
                tel_payload: dict[str, Any] = {
                    "id": packet.id,
                    "node_id": node_id_str,
                    "node_num": from_node,
                    "from_id": node_id_str,
                    "from": node_id_str,
                    "rx_time": rx_time,
                    "channel": channel_index,
                    "telemetry": {},
                }

                if tel.HasField("device_metrics"):
                    dm = tel.device_metrics
                    metrics_camel: dict[str, Any] = {
                        "batteryLevel": (
                            dm.battery_level if dm.battery_level != 0 else None
                        ),
                        "voltage": dm.voltage if dm.voltage != 0.0 else None,
                        "channelUtilization": dm.channel_utilization,
                        "airUtilTx": dm.air_util_tx,
                        "uptimeSeconds": (
                            dm.uptime_seconds if dm.uptime_seconds != 0 else None
                        ),
                    }
                    metrics_snake: dict[str, Any] = {
                        "battery_level": (
                            dm.battery_level if dm.battery_level != 0 else None
                        ),
                        "voltage": dm.voltage if dm.voltage != 0.0 else None,
                        "channel_utilization": dm.channel_utilization,
                        "air_util_tx": dm.air_util_tx,
                        "uptime_seconds": (
                            dm.uptime_seconds if dm.uptime_seconds != 0 else None
                        ),
                    }
                    cleaned_camel = {k: v for k, v in metrics_camel.items() if v is not None}
                    cleaned_snake = {k: v for k, v in metrics_snake.items() if v is not None}

                    tel_payload["telemetry"]["deviceMetrics"] = cleaned_camel
                    tel_payload["device_metrics"] = cleaned_snake
                    self.sender.update_node_metrics(node_id_str, cleaned_camel)

                if tel.HasField("environment_metrics"):
                    em = tel.environment_metrics
                    tel_payload["telemetry"]["environmentMetrics"] = {
                        "temperature": em.temperature,
                        "relativeHumidity": em.relative_humidity,
                        "barometricPressure": em.barometric_pressure,
                        "gasResistance": (
                            em.gas_resistance if em.gas_resistance != 0.0 else None
                        ),
                    }

                if tel.HasField("power_metrics"):
                    pm = tel.power_metrics
                    tel_payload["telemetry"]["powerMetrics"] = {
                        "ch1Voltage": pm.ch1_voltage,
                        "ch1Current": pm.ch1_current,
                        "ch2Voltage": pm.ch2_voltage,
                        "ch2Current": pm.ch2_current,
                    }

                self.sender.enqueue("telemetry", tel_payload, priority=1)
            except Exception as exc:
                logger.debug("Error deserializando Telemetry: %s", exc)

        # 4. TEXT_MESSAGE_APP (1)
        elif portnum == 1:
            # Descartar mensajes directos/privados (el chat público de PotatoMesh solo difunde canal)
            if packet.to not in (0xFFFFFFFF, 0):
                return
            try:
                text = data.payload.decode("utf-8", errors="replace")
                hops = (
                    packet.hop_start - packet.hop_limit
                    if packet.hop_start >= packet.hop_limit
                    else 0
                )
                msg_payload = {
                    "id": packet.id,
                    "from": node_id_str,
                    "to": "^all",
                    "text": text,
                    "channel": channel_index,
                    "rx_time": rx_time,
                    "snr": packet.rx_snr,
                    "rssi": packet.rx_rssi,
                    "hops": hops,
                }
                self.sender.enqueue("messages", msg_payload, priority=0)
            except Exception as exc:
                logger.debug("Error procesando mensaje de texto: %s", exc)

        # 5. TRACEROUTE_APP (70)
        elif portnum == 70:
            route = mesh_pb2.RouteDiscovery()
            try:
                route.ParseFromString(data.payload)
                hops_list = [from_node] + list(route.route) + [packet.to]
                trace_payload = {
                    "id": packet.id,
                    "from": from_node,
                    "to": packet.to,
                    "rx_time": rx_time,
                    "hops": hops_list,
                    "snr": packet.rx_snr,
                    "rssi": packet.rx_rssi,
                }
                self.sender.enqueue("traces", trace_payload, priority=0)
            except Exception as exc:
                logger.debug("Error deserializando Traceroute: %s", exc)

        # 6. NEIGHBORINFO_APP (71)
        elif portnum == 71:
            n_info = mesh_pb2.NeighborInfo()
            try:
                n_info.ParseFromString(data.payload)
                neighbors = [
                    {"node_id": f"!{n.node_id:08x}", "snr": n.snr}
                    for n in n_info.neighbors
                ]
                neighbor_payload = {
                    "node_id": node_id_str,
                    "node_num": from_node,
                    "rx_time": rx_time,
                    "neighbors": neighbors,
                }
                self.sender.enqueue("neighbors", neighbor_payload, priority=1)
            except Exception as exc:
                logger.debug("Error deserializando NeighborInfo: %s", exc)

        # 7. WAYPOINT_APP (8)
        elif portnum == 8:
            wp = mesh_pb2.Waypoint()
            try:
                wp.ParseFromString(data.payload)
                wp_payload = {
                    "id": packet.id,
                    "from": node_id_str,
                    "name": wp.name,
                    "description": wp.description,
                    "latitude": wp.latitude_i / 1e7 if wp.latitude_i else None,
                    "longitude": wp.longitude_i / 1e7 if wp.longitude_i else None,
                    "rx_time": rx_time,
                }
                self.sender.enqueue("waypoints", wp_payload, priority=1)
            except Exception as exc:
                logger.debug("Error deserializando Waypoint: %s", exc)

        # 8. MAP_REPORT_APP (73)
        elif portnum == 73:
            mr = mqtt_pb2.MapReport()
            try:
                mr.ParseFromString(data.payload)
                lat = mr.latitude_i / 1e7 if mr.latitude_i else None
                lon = mr.longitude_i / 1e7 if mr.longitude_i else None

                try:
                    role_name = config_pb2.Config.DeviceConfig.Role.Name(mr.role)
                except (ValueError, TypeError):
                    role_name = "CLIENT"

                node_entry = {
                    "num": from_node,
                    "user": {
                        "id": node_id_str,
                        "shortName": mr.short_name or None,
                        "longName": mr.long_name or None,
                        "role": role_name,
                    },
                    "lastHeard": rx_time,
                }
                self.sender.enqueue("nodes", {node_id_str: node_entry}, priority=0)

                if lat is not None and lon is not None:
                    pos_entry = {
                        "id": packet.id,
                        "from": node_id_str,
                        "latitude": lat,
                        "longitude": lon,
                        "altitude": mr.altitude if mr.altitude else None,
                        "rx_time": rx_time,
                    }
                    self.sender.enqueue("positions", pos_entry, priority=1)
            except Exception as exc:
                logger.debug("Error deserializando MapReport: %s", exc)
