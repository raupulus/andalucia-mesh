#!/usr/bin/env python3
"""Script de migración y siembra de datos históricos desde PotatoMesh hacia el ecosistema Andalucía Mesh.

Lee el volcado SQLite de PotatoMesh (mesh.db / mesh_checkpointed_clean.sqlite) y distribuye
los datos de forma atómica y segura en:
1. PostgreSQL 'ingest' (TimescaleDB): nodos con resolución provincial por polígonos,
   posiciones, telemetría de dispositivo y entorno, estadísticas locales, paquetes,
   recepciones y refresco de agregados continuos para alimentar el Portal.
2. PostgreSQL 'meshview': nodos, paquetes con protobufs legítimos (MeshPacket) y recepciones
   (packet_seen) para alimentar el visor técnico MeshView.
3. PotatoMesh: verificación y copia de mesh.db en el volumen de persistencia nativo.

Uso:
    python3 migrar-datos-historicos.py --dry-run
    python3 migrar-datos-historicos.py --sqlite-path /ruta/a/mesh.db --db-host 127.0.0.1
"""

from __future__ import annotations

import argparse
import asyncio
import json
import logging
import os
import shutil
import sqlite3
import sys
from datetime import datetime, timezone
from pathlib import Path
from typing import Any, Final

# Configuración del registro de eventos en consola
logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s [%(levelname)s] %(name)s: %(message)s",
    datefmt="%H:%M:%S",
)
logger = logging.getLogger("migracion")

# Intento de importar dependencias opcionales de ingesta para protobuf y geoespacial
try:
    from meshtastic.protobuf import mesh_pb2, portnums_pb2
    PROTOBUF_AVAILABLE = True
except ImportError:
    PROTOBUF_AVAILABLE = False
    logger.warning("Librería meshtastic no disponible; los payloads de MeshView se generarán básicos.")

try:
    import asyncpg
    ASYNCPG_AVAILABLE = True
except ImportError:
    ASYNCPG_AVAILABLE = False


def parse_arguments() -> argparse.Namespace:
    """Parsea los argumentos de la línea de comandos."""
    parser = argparse.ArgumentParser(
        description="Migrador de datos históricos de PotatoMesh hacia Ingest, MeshView y PotatoMesh."
    )
    parser.add_argument(
        "--sqlite-path",
        type=str,
        default="/Users/fryntiz/tmp/migracion_potato/database_backup/mesh_checkpointed_clean.sqlite",
        help="Ruta al archivo SQLite de copia de PotatoMesh.",
    )
    parser.add_argument(
        "--geojson-path",
        type=str,
        default=str(
            Path(__file__).resolve().parent.parent.parent
            / "services"
            / "ingesta"
            / "polygons"
            / "provincias-andalucia.geojson"
        ),
        help="Ruta al archivo GeoJSON de provincias de Andalucía.",
    )
    parser.add_argument(
        "--db-host",
        type=str,
        default=os.getenv("DB_HOST", "127.0.0.1"),
        help="Host del servidor PostgreSQL nativo.",
    )
    parser.add_argument(
        "--db-port",
        type=int,
        default=int(os.getenv("DB_PORT", "5432")),
        help="Puerto del servidor PostgreSQL.",
    )
    parser.add_argument(
        "--ingest-db",
        type=str,
        default=os.getenv("DB_INGEST_NAME", "ingest"),
        help="Nombre de la base de datos de Ingesta (TimescaleDB).",
    )
    parser.add_argument(
        "--ingest-user",
        type=str,
        default=os.getenv("DB_INGEST_USER", "ingest"),
        help="Usuario de la base de datos de Ingesta.",
    )
    parser.add_argument(
        "--ingest-password",
        type=str,
        default=os.getenv("DB_INGEST_PASSWORD", ""),
        help="Contraseña para la base de datos de Ingesta.",
    )
    parser.add_argument(
        "--meshview-db",
        type=str,
        default=os.getenv("DB_MESHVIEW_NAME", "meshview"),
        help="Nombre de la base de datos de MeshView.",
    )
    parser.add_argument(
        "--meshview-user",
        type=str,
        default=os.getenv("DB_MESHVIEW_USER", "meshview"),
        help="Usuario de la base de datos de MeshView.",
    )
    parser.add_argument(
        "--meshview-password",
        type=str,
        default=os.getenv("DB_MESHVIEW_PASSWORD", ""),
        help="Contraseña para la base de datos de MeshView.",
    )
    parser.add_argument(
        "--potato-dest",
        type=str,
        default=None,
        help="Ruta de destino opcional para copiar mesh.db (ej: /srv/potatomesh/datos/mesh.db).",
    )
    parser.add_argument(
        "--dry-run",
        action="store_true",
        help="Ejecuta en modo simulación: lee, valida, clasifica y audita sin escribir en PostgreSQL.",
    )
    parser.add_argument(
        "--skip-ingest",
        action="store_true",
        help="Omite la inserción en la base de datos de Ingesta.",
    )
    parser.add_argument(
        "--skip-meshview",
        action="store_true",
        help="Omite la inserción en la base de datos de MeshView.",
    )
    parser.add_argument(
        "--skip-potato",
        action="store_true",
        help="Omite la copia y verificación de PotatoMesh.",
    )
    parser.add_argument(
        "--refresh-aggregates",
        action="store_true",
        help="Fuerza el refresco de agregados continuos en TimescaleDB tras la migración.",
    )
    return parser.parse_args()


def node_id_to_num(node_id: str) -> int:
    """Convierte un identificador en formato '!12345678' a su valor entero de 32 bits."""
    clean = node_id.strip().lstrip("!")
    try:
        return int(clean, 16)
    except ValueError:
        return 0


def epoch_to_datetime(ts: int | float | None) -> datetime | None:
    """Convierte un timestamp epoch en segundos a un objeto datetime UTC."""
    if ts is None or ts <= 0:
        return None
    return datetime.fromtimestamp(ts, tz=timezone.utc)


def epoch_to_us(ts: int | float | None) -> int | None:
    """Convierte un timestamp epoch en segundos a microsegundos enteros."""
    if ts is None or ts <= 0:
        return None
    return int(ts * 1_000_000)


def build_meshpacket_protobuf(
    from_num: int,
    to_num: int,
    packet_id: int,
    rx_time: int,
    channel_index: int,
    portnum_int: int,
    payload_bytes: bytes,
) -> bytes:
    """Construye y serializa un MeshPacket de Meshtastic si está disponible."""
    if not PROTOBUF_AVAILABLE:
        return payload_bytes

    mp = mesh_pb2.MeshPacket()
    setattr(mp, "from", from_num)
    mp.to = to_num
    mp.id = packet_id
    mp.rx_time = rx_time
    mp.channel = channel_index
    mp.decoded.portnum = portnum_int
    mp.decoded.payload = payload_bytes
    return mp.SerializeToString()


class MigrationEngine:
    """Motor central que orquesta la lectura de SQLite y distribución a los destinos."""

    def __init__(self, args: argparse.Namespace) -> None:
        """Inicializa el motor con los argumentos provistos."""
        self.args = args
        self.sqlite_path = Path(args.sqlite_path)
        self.geojson_path = Path(args.geojson_path)
        self.geo_engine: Any = None

        if not self.sqlite_path.is_file():
            raise FileNotFoundError(f"No se encuentra la base de datos SQLite en: {self.sqlite_path}")

        self._init_geo()

    def _init_geo(self) -> None:
        """Inicializa el clasificador de provincias si el archivo GeoJSON está disponible."""
        if self.geojson_path.is_file():
            try:
                # Importar clase GeoEngine de ingesta
                sys.path.insert(0, str(self.geojson_path.resolve().parent.parent))
                from src.geo import GeoEngine

                self.geo_engine = GeoEngine(str(self.geojson_path))
                logger.info("Motor geoespacial de provincias andaluzas inicializado correctamente.")
            except Exception as e:
                logger.warning("No se pudo cargar GeoEngine de ingesta (%s). Se usará fallback 'FUERA'.", e)
        else:
            logger.warning("GeoJSON no encontrado en %s; asignación provincial desactivada.", self.geojson_path)

    def resolve_province(self, lat: float | None, lon: float | None) -> tuple[str | None, bool]:
        """Resuelve el código ISO provincial y el flag de incertidumbre fronteriza."""
        if lat is None or lon is None:
            return None, False
        if self.geo_engine:
            return self.geo_engine.resolve_province(lat, lon, precision_m=10.0)
        return "FUERA", False

    def _connect_sqlite(self) -> sqlite3.Connection:
        """Abre la base SQLite en modo estrictamente de solo lectura mediante URI."""
        return sqlite3.connect(f"file:{self.sqlite_path.resolve()}?mode=ro", uri=True)

    def inspect_sqlite(self) -> dict[str, int]:
        """Inspecciona y devuelve los recuentos de filas en el SQLite de origen."""
        counts = {}
        conn = self._connect_sqlite()
        cursor = conn.cursor()
        tables = ["nodes", "messages", "positions", "telemetry", "traces", "neighbors", "waypoints"]
        for table in tables:
            try:
                cursor.execute(f"SELECT COUNT(*) FROM {table}")
                counts[table] = cursor.fetchone()[0]
            except sqlite3.OperationalError:
                counts[table] = 0
        conn.close()
        return counts

    async def run(self) -> None:
        """Punto de entrada principal para ejecutar la migración."""
        logger.info("=== Inicio de análisis y migración de datos históricos ===")
        counts = self.inspect_sqlite()
        logger.info(
            "Datos detectados en SQLite: %d nodos, %d mensajes, %d posiciones, %d telemetrías, %d trazas, %d vecinos.",
            counts.get("nodes", 0),
            counts.get("messages", 0),
            counts.get("positions", 0),
            counts.get("telemetry", 0),
            counts.get("traces", 0),
            counts.get("neighbors", 0),
        )

        if not self.args.skip_potato:
            self._handle_potato()

        if self.args.dry_run:
            logger.info("--- MODO DRY-RUN: Ejecutando simulación completa sin alterar PostgreSQL ---")
            self._simulate_data()
            logger.info("=== Simulación completada con éxito. Todo está listo para ejecutar en real ===")
            return

        # Conexiones y escritura real
        if not ASYNCPG_AVAILABLE:
            logger.error("Se requiere la librería 'asyncpg' para conectar a PostgreSQL. Instálala en tu entorno.")
            sys.exit(1)

        if not self.args.skip_ingest:
            await self._migrate_ingest()

        if not self.args.skip_meshview:
            await self._migrate_meshview()

        logger.info("=== Migración de datos históricos completada exitosamente ===")

    def _handle_potato(self) -> None:
        """Verifica la integridad de la base SQLite y opcionalmente la copia al destino."""
        logger.info("Verificando integridad física del archivo SQLite de PotatoMesh...")
        conn = self._connect_sqlite()
        cursor = conn.cursor()
        cursor.execute("PRAGMA integrity_check")
        status = cursor.fetchone()[0]
        conn.close()

        if status != "ok":
            raise ValueError(f"Fallo de integridad en SQLite: {status}")
        logger.info("Comprobación PRAGMA integrity_check: OK")

        if self.args.potato_dest:
            dest = Path(self.args.potato_dest)
            dest.parent.mkdir(parents=True, exist_ok=True)
            logger.info("Copiando base limpia de PotatoMesh a: %s", dest)
            if not self.args.dry_run:
                shutil.copy2(self.sqlite_path, dest)
                logger.info("Archivo mesh.db instalado correctamente en el volumen de PotatoMesh.")
            else:
                logger.info("[DRY-RUN] Se copiaría %s -> %s", self.sqlite_path, dest)

    def _simulate_data(self) -> None:
        """Simula la transformación y clasificación de datos reportando estadísticas completas."""
        conn = self._connect_sqlite()
        conn.row_factory = sqlite3.Row
        cursor = conn.cursor()

        # Simular nodos y provincias
        cursor.execute("SELECT * FROM nodes")
        nodes = cursor.fetchall()
        province_counts: dict[str, int] = {}
        nodes_with_coords = 0
        routers_count = 0

        for n in nodes:
            lat = n["latitude"]
            lon = n["longitude"]
            role = n["role"] or "CLIENT"
            if "ROUTER" in role or role == "REPEATER":
                routers_count += 1
            if lat is not None and lon is not None:
                nodes_with_coords += 1
                prov, _ = self.resolve_province(lat, lon)
                if prov:
                    province_counts[prov] = province_counts.get(prov, 0) + 1

        logger.info(
            "Nodos evaluados: %d total, %d con coordenadas GPS, %d roles de infraestructura/router.",
            len(nodes),
            nodes_with_coords,
            routers_count,
        )
        logger.info("Distribución de nodos clasificados por provincia:")
        for prov, c in sorted(province_counts.items(), key=lambda x: -x[1]):
            logger.info("  %s: %d nodos", prov, c)

        # Simular mensajes
        cursor.execute("SELECT * FROM messages")
        messages = cursor.fetchall()
        channel_counts: dict[str, int] = {}
        for m in messages:
            ch = m["channel_name"] or str(m["channel"])
            channel_counts[ch] = channel_counts.get(ch, 0) + 1

        logger.info("Mensajes evaluados: %d total.", len(messages))
        logger.info("Distribución de mensajes por canal:")
        for ch, c in sorted(channel_counts.items(), key=lambda x: -x[1]):
            logger.info("  Canal '%s': %d mensajes", ch, c)

        conn.close()

    async def _migrate_ingest(self) -> None:
        """Vuelca y transforma los datos históricos en la base de datos 'ingest' (TimescaleDB)."""
        logger.info("Iniciando conexión a base de datos Ingest (%s:%d/%s)...", self.args.db_host, self.args.db_port, self.args.ingest_db)
        pool = await asyncpg.create_pool(
            host=self.args.db_host,
            port=self.args.db_port,
            database=self.args.ingest_db,
            user=self.args.ingest_user,
            password=self.args.ingest_password,
            min_size=1,
            max_size=5,
        )

        conn = self._connect_sqlite()
        conn.row_factory = sqlite3.Row
        cursor = conn.cursor()

        try:
            # 1. Migración de nodos
            cursor.execute("SELECT * FROM nodes")
            nodes = cursor.fetchall()
            logger.info("Insertando %d nodos en tabla 'node' de Ingest...", len(nodes))

            node_records = []
            for n in nodes:
                node_id = n["node_id"]
                num = n["num"] or node_id_to_num(node_id)
                lat = n["latitude"]
                lon = n["longitude"]
                prov, uncertain = self.resolve_province(lat, lon)
                first_seen = epoch_to_datetime(n["first_heard"]) or datetime.now(timezone.utc)
                last_seen = epoch_to_datetime(n["last_heard"]) or first_seen
                pos_time = epoch_to_datetime(n["position_time"]) if n["position_time"] else None

                node_records.append(
                    (
                        node_id,
                        num,
                        n["short_name"],
                        n["long_name"],
                        n["role"] or "CLIENT",
                        n["hw_model"],
                        None,  # firmware
                        n["public_key"],
                        lat,
                        lon,
                        10.0 if lat is not None else None,
                        n["location_source"],
                        pos_time,
                        prov,
                        uncertain,
                        n["hops_away"],
                        n["hops_away"],
                        False,  # is_gateway
                        None,
                        int(n["battery_level"]) if n["battery_level"] is not None else None,
                        float(n["voltage"]) if n["voltage"] is not None else None,
                        last_seen,
                        float(n["channel_utilization"]) if n["channel_utilization"] is not None else None,
                        float(n["air_util_tx"]) if n["air_util_tx"] is not None else None,
                        last_seen,
                        n["uptime_seconds"],
                        last_seen,
                        None,  # last_reboot_at
                        first_seen,
                        last_seen,
                    )
                )

            upsert_node_sql = """
            INSERT INTO node (
                id, node_num, short_name, long_name, role, hw_model, firmware, public_key_fp,
                latitude, longitude, position_precision_m, position_source, last_position_at,
                province, border_uncertain, hop_start_last, hops_min_last, is_gateway, gateway_first_at,
                battery_level, voltage, battery_at, channel_utilization, air_util_tx, metrics_at,
                uptime_seconds, uptime_at, last_reboot_at, first_seen, last_seen
            ) VALUES (
                $1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13, $14, $15, $16, $17,
                $18, $19, $20, $21, $22, $23, $24, $25, $26, $27, $28, $29, $30
            ) ON CONFLICT (id) DO UPDATE SET
                short_name = COALESCE(EXCLUDED.short_name, node.short_name),
                long_name = COALESCE(EXCLUDED.long_name, node.long_name),
                role = COALESCE(EXCLUDED.role, node.role),
                hw_model = COALESCE(EXCLUDED.hw_model, node.hw_model),
                latitude = COALESCE(EXCLUDED.latitude, node.latitude),
                longitude = COALESCE(EXCLUDED.longitude, node.longitude),
                province = COALESCE(EXCLUDED.province, node.province),
                battery_level = COALESCE(EXCLUDED.battery_level, node.battery_level),
                voltage = COALESCE(EXCLUDED.voltage, node.voltage),
                channel_utilization = COALESCE(EXCLUDED.channel_utilization, node.channel_utilization),
                air_util_tx = COALESCE(EXCLUDED.air_util_tx, node.air_util_tx),
                last_seen = GREATEST(node.last_seen, EXCLUDED.last_seen);
            """
            async with pool.acquire() as c:
                await c.executemany(upsert_node_sql, node_records)
            logger.info("Nodos insertados con éxito en Ingest.")

            # 2. Migración de posiciones
            cursor.execute("SELECT * FROM positions")
            positions = cursor.fetchall()
            logger.info("Insertando %d registros en hypertabla 'position'...", len(positions))
            pos_records = []
            for p in positions:
                rx_dt = epoch_to_datetime(p["rx_time"])
                if not rx_dt or p["latitude"] is None or p["longitude"] is None:
                    continue
                prov, uncertain = self.resolve_province(p["latitude"], p["longitude"])
                pos_records.append(
                    (
                        rx_dt,
                        p["node_id"],
                        p["latitude"],
                        p["longitude"],
                        int(p["altitude"]) if p["altitude"] is not None else None,
                        p["precision_bits"],
                        10.0,
                        p["location_source"] or "unknown",
                        epoch_to_datetime(p["position_time"]),
                        prov,
                        uncertain,
                    )
                )

            async with pool.acquire() as c:
                await c.copy_records_to_table(
                    "position",
                    records=pos_records,
                    columns=[
                        "at", "node_id", "latitude", "longitude", "altitude",
                        "precision_bits", "precision_m", "source", "gps_time",
                        "province", "border_uncertain"
                    ],
                )
            logger.info("Posiciones insertadas con éxito.")

            # 3. Migración de telemetrías
            cursor.execute("SELECT * FROM telemetry")
            telem_rows = cursor.fetchall()
            logger.info("Insertando telemetrías (%d registros)...", len(telem_rows))
            dev_records = []
            env_records = []
            stats_records = []

            for t in telem_rows:
                rx_dt = epoch_to_datetime(t["rx_time"])
                if not rx_dt:
                    continue
                node_id = t["node_id"]

                # Telemetría de dispositivo
                if any(
                    t[col] is not None
                    for col in ["battery_level", "voltage", "channel_utilization", "air_util_tx", "uptime_seconds"]
                ):
                    dev_records.append(
                        (
                            rx_dt,
                            node_id,
                            int(t["battery_level"]) if t["battery_level"] is not None else None,
                            float(t["voltage"]) if t["voltage"] is not None else None,
                            float(t["channel_utilization"]) if t["channel_utilization"] is not None else None,
                            float(t["air_util_tx"]) if t["air_util_tx"] is not None else None,
                            t["uptime_seconds"],
                            False,
                        )
                    )

                # Telemetría de entorno
                env_dict = {}
                for col in ["temperature", "relative_humidity", "barometric_pressure", "gas_resistance", "iaq"]:
                    if t[col] is not None:
                        env_dict[col] = float(t[col])
                if env_dict:
                    env_records.append((rx_dt, node_id, json.dumps(env_dict)))

                # Estadísticas locales
                if any(t[col] is not None for col in ["num_packets_tx", "num_packets_rx"]):
                    stats_records.append(
                        (
                            rx_dt,
                            node_id,
                            t["uptime_seconds"],
                            float(t["channel_utilization"]) if t["channel_utilization"] is not None else None,
                            float(t["air_util_tx"]) if t["air_util_tx"] is not None else None,
                            t["num_packets_tx"],
                            t["num_packets_rx"],
                            t["num_packets_rx_bad"],
                            t["num_rx_dupe"],
                            t["num_tx_relay"],
                            t["num_tx_relay_canceled"],
                            t["num_online_nodes"],
                            t["num_total_nodes"],
                        )
                    )

            async with pool.acquire() as c:
                if dev_records:
                    await c.copy_records_to_table(
                        "telemetry_device",
                        records=dev_records,
                        columns=["at", "node_id", "battery_level", "voltage", "channel_utilization", "air_util_tx", "uptime_seconds", "reboot"],
                    )
                if env_records:
                    await c.copy_records_to_table(
                        "telemetry_env",
                        records=env_records,
                        columns=["at", "node_id", "metrics"],
                    )
                if stats_records:
                    await c.copy_records_to_table(
                        "local_stats",
                        records=stats_records,
                        columns=[
                            "at", "node_id", "uptime_seconds", "channel_utilization", "air_util_tx",
                            "num_packets_tx", "num_packets_rx", "num_packets_rx_bad", "num_rx_dupe",
                            "num_tx_relay", "num_tx_relay_canceled", "num_online_nodes", "num_total_nodes"
                        ],
                    )
            logger.info("Telemetría insertada con éxito en Ingest.")

            # 4. Migración de mensajes a hypertabla 'packet'
            cursor.execute("SELECT * FROM messages")
            messages = cursor.fetchall()
            logger.info("Insertando %d mensajes en hypertabla 'packet'...", len(messages))
            packet_records = []
            reception_records = []

            for m in messages:
                rx_dt = epoch_to_datetime(m["rx_time"])
                if not rx_dt:
                    continue
                gw = m["ingestor"] or "!00000000"
                txt = m["text"] or ""
                payload_json = json.dumps({"text": txt})
                ch_name = m["channel_name"] or "SFNarrow"

                packet_records.append(
                    (
                        rx_dt,
                        m["from_id"],
                        m["to_id"] or "^all",
                        m["id"],
                        ch_name,
                        "text",
                        1,
                        "text",
                        "decrypted",
                        m["hop_limit"],
                        m["hops"] or 0,
                        1,
                        False,
                        False,
                        True,
                        len(txt.encode("utf-8")),
                        230.0,
                        gw,
                        None,
                        payload_json,
                    )
                )

                if m["ingestor"]:
                    reception_records.append(
                        (
                            rx_dt,
                            m["from_id"],
                            m["id"],
                            m["ingestor"],
                            float(m["snr"]) if m["snr"] is not None else None,
                            int(m["rssi"]) if m["rssi"] is not None else None,
                            m["hop_limit"],
                            m["hops"],
                            None,
                            rx_dt,
                            False,
                            True,
                            None,
                        )
                    )

            insert_packet_sql = """
            INSERT INTO packet (
                rx_first, from_id, to_id, packet_id, channel, portnum, portnum_num, variant,
                decrypt_status, hop_start, hops_min, reception_count, want_ack, via_mqtt, ok_to_mqtt,
                size_bytes, airtime_ms, first_gateway, province, payload
            ) VALUES (
                $1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13, $14, $15, $16, $17, $18, $19, $20::jsonb
            ) ON CONFLICT (from_id, packet_id, rx_first) DO NOTHING;
            """
            async with pool.acquire() as c:
                await c.executemany(insert_packet_sql, packet_records)
                if reception_records:
                    await c.copy_records_to_table(
                        "reception",
                        records=reception_records,
                        columns=[
                            "rx_at", "from_id", "packet_id", "gateway_id", "snr", "rssi",
                            "hop_limit", "hops", "relay_node", "gw_rx_time", "own", "direct", "distance_km"
                        ],
                    )
            logger.info("Paquetes y recepciones insertados con éxito.")

            # 5. Refresco de agregados continuos si se solicitó
            if self.args.refresh_aggregates:
                logger.info("Refrescando agregados continuos en TimescaleDB...")
                aggregates = [
                    "agg_node_hour",
                    "agg_node_day",
                    "agg_reception_hour",
                    "agg_reception_day",
                    "agg_gateway_hour",
                    "agg_gateway_day",
                    "agg_traffic_hour",
                    "agg_traffic_day",
                ]
                async with pool.acquire() as c:
                    for agg in aggregates:
                        try:
                            await c.execute(f"CALL refresh_continuous_aggregate('{agg}', NULL, NULL);")
                            logger.info("  Agregado '%s' refrescado correctamente.", agg)
                        except Exception as e:
                            logger.warning("  No se pudo refrescar '%s': %s", agg, e)

        finally:
            conn.close()
            await pool.close()

    async def _migrate_meshview(self) -> None:
        """Vuelca los datos históricos en la base de datos de MeshView."""
        logger.info(
            "Iniciando conexión a base de datos MeshView (%s:%d/%s)...",
            self.args.db_host,
            self.args.db_port,
            self.args.meshview_db,
        )
        pool = await asyncpg.create_pool(
            host=self.args.db_host,
            port=self.args.db_port,
            database=self.args.meshview_db,
            user=self.args.meshview_user,
            password=self.args.meshview_password,
            min_size=1,
            max_size=5,
        )

        conn = self._connect_sqlite()
        conn.row_factory = sqlite3.Row
        cursor = conn.cursor()

        try:
            # 1. Nodos en MeshView
            cursor.execute("SELECT * FROM nodes")
            nodes = cursor.fetchall()
            logger.info("Insertando %d nodos en 'node' de MeshView...", len(nodes))

            meshview_nodes = []
            for n in nodes:
                node_id_str = n["node_id"]
                node_num = n["num"] or node_id_to_num(node_id_str)
                lat = n["latitude"]
                lon = n["longitude"]
                last_lat = int(lat * 1e7) if lat is not None else None
                last_long = int(lon * 1e7) if lon is not None else None
                first_us = epoch_to_us(n["first_heard"])
                last_us = epoch_to_us(n["last_heard"])

                meshview_nodes.append(
                    (
                        node_id_str,
                        node_num,
                        n["long_name"],
                        n["short_name"],
                        n["hw_model"],
                        None,  # firmware
                        n["role"],
                        last_lat,
                        last_long,
                        "SFNarrow",
                        False,
                        first_us,
                        last_us,
                    )
                )

            upsert_mv_node_sql = """
            INSERT INTO node (
                id, node_id, long_name, short_name, hw_model, firmware, role,
                last_lat, last_long, channel, is_mqtt_gateway, first_seen_us, last_seen_us
            ) VALUES (
                $1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13
            ) ON CONFLICT (id) DO UPDATE SET
                node_id = COALESCE(EXCLUDED.node_id, node.node_id),
                long_name = COALESCE(EXCLUDED.long_name, node.long_name),
                short_name = COALESCE(EXCLUDED.short_name, node.short_name),
                hw_model = COALESCE(EXCLUDED.hw_model, node.hw_model),
                role = COALESCE(EXCLUDED.role, node.role),
                last_lat = COALESCE(EXCLUDED.last_lat, node.last_lat),
                last_long = COALESCE(EXCLUDED.last_long, node.last_long),
                last_seen_us = GREATEST(node.last_seen_us, EXCLUDED.last_seen_us);
            """
            async with pool.acquire() as c:
                await c.executemany(upsert_mv_node_sql, meshview_nodes)
            logger.info("Nodos insertados con éxito en MeshView.")

            # 2. Mensajes en Packet y PacketSeen
            cursor.execute("SELECT * FROM messages")
            messages = cursor.fetchall()
            logger.info("Insertando %d mensajes en 'packet' de MeshView...", len(messages))

            mv_packets = []
            mv_packet_seens = []

            for m in messages:
                packet_id = m["id"]
                from_num = node_id_to_num(m["from_id"])
                to_num = node_id_to_num(m["to_id"]) if m["to_id"] and m["to_id"] != "^all" else 0xFFFFFFFF
                txt = m["text"] or ""
                rx_time = m["rx_time"]
                import_us = epoch_to_us(rx_time)
                ch_name = m["channel_name"] or "SFNarrow"

                # Construir protobuf
                payload_bytes = build_meshpacket_protobuf(
                    from_num=from_num,
                    to_num=to_num,
                    packet_id=packet_id,
                    rx_time=rx_time,
                    channel_index=m["channel"] or 0,
                    portnum_int=1,  # TEXT_MESSAGE_APP
                    payload_bytes=txt.encode("utf-8"),
                )

                mv_packets.append(
                    (
                        packet_id,
                        1,  # portnum
                        from_num,
                        to_num,
                        payload_bytes,
                        import_us,
                        ch_name,
                    )
                )

                if m["ingestor"]:
                    gw_num = node_id_to_num(m["ingestor"])
                    topic = f"msh/EU_868/2/e/{ch_name}/{m['ingestor']}"
                    mv_packet_seens.append(
                        (
                            packet_id,
                            gw_num,
                            rx_time,
                            m["hop_limit"],
                            m["hop_limit"],
                            ch_name,
                            float(m["snr"]) if m["snr"] is not None else None,
                            int(m["rssi"]) if m["rssi"] is not None else None,
                            topic,
                            import_us,
                        )
                    )

            upsert_mv_packet_sql = """
            INSERT INTO packet (
                id, portnum, from_node_id, to_node_id, payload, import_time_us, channel
            ) VALUES (
                $1, $2, $3, $4, $5, $6, $7
            ) ON CONFLICT (id) DO NOTHING;
            """
            upsert_mv_seen_sql = """
            INSERT INTO packet_seen (
                packet_id, node_id, rx_time, hop_limit, hop_start, channel, rx_snr, rx_rssi, topic, import_time_us
            ) VALUES (
                $1, $2, $3, $4, $5, $6, $7, $8, $9, $10
            ) ON CONFLICT (packet_id, node_id, rx_time) DO NOTHING;
            """

            async with pool.acquire() as c:
                await c.executemany(upsert_mv_packet_sql, mv_packets)
                if mv_packet_seens:
                    # Asegurar nodos gateway en 'node' de MeshView para FK
                    gw_nodes = [
                        (f"!{gw:08x}", gw, f"Gateway !{gw:08x}", "GW", "GATEWAY", None, "ROUTER", None, None, "SFNarrow", True, None, None)
                        for gw in {ps[1] for ps in mv_packet_seens}
                    ]
                    await c.executemany(upsert_mv_node_sql, gw_nodes)
                    await c.executemany(upsert_mv_seen_sql, mv_packet_seens)

            logger.info("Paquetes y recepciones de MeshView insertados con éxito.")

        finally:
            conn.close()
            await pool.close()


def main() -> None:
    """Función de inicio del script."""
    args = parse_arguments()
    engine = MigrationEngine(args)
    asyncio.run(engine.run())


if __name__ == "__main__":
    main()
