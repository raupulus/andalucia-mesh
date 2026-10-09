"""Capa de persistencia asíncrona masiva en PostgreSQL 17 + TimescaleDB.

Gestiona buffers en memoria desacoplados de la recepción y escribe en bloque
mediante COPY nativo (asyncpg.copy_records_to_table) para hypertables y
UPSERT periódico cada 5 segundos para las tablas maestras de nodos y gateways.
"""

from __future__ import annotations

import asyncio
import json
import logging
from datetime import datetime, timezone
from typing import Any

import asyncpg

from .dedup import ReceptionItem, UnifiedPacket
from .registry import GatewayRecord, NodeRecord, NodeRegistry

logger = logging.getLogger("ingesta.storage")


class StorageManager:
    """Gestor de persistencia en bloque con tolerancia a caídas temporales de PostgreSQL."""

    def __init__(
        self,
        db_pool: asyncpg.Pool,
        registry: NodeRegistry,
        batch_rows: int = 500,
        batch_interval_ms: int = 1000,
        max_buffer_rows: int = 100000,
    ) -> None:
        """Inicializa el gestor de almacenamiento.

        Args:
            db_pool: Pool de conexiones asyncpg.
            registry: Registro vivo de nodos y gateways en memoria.
            batch_rows: Umbral de filas para vaciado inmediato.
            batch_interval_ms: Intervalo temporal en ms para vaciado periódico.
            max_buffer_rows: Capacidad máxima del buffer antes de descarte FIFO.
        """
        self.pool = db_pool
        self.registry = registry
        self.batch_rows = batch_rows
        self.batch_interval_s = batch_interval_ms / 1000.0
        self.max_buffer_rows = max_buffer_rows

        # Colas de inserción por tabla
        self._q_packet: list[tuple[Any, ...]] = []
        self._q_reception: list[tuple[Any, ...]] = []
        self._q_neighbor: list[tuple[Any, ...]] = []
        self._q_position: list[tuple[Any, ...]] = []
        self._q_telem_dev: list[tuple[Any, ...]] = []
        self._q_telem_env: list[tuple[Any, ...]] = []
        self._q_local_stats: list[tuple[Any, ...]] = []
        self._q_late_updates: list[tuple[int | None, str, int, datetime]] = []

        # Contadores de persistencia
        self.filas_guardadas_bd: int = 0
        self.filas_descartadas_bd: int = 0
        self.db_status: str = "conectada"
        self._last_error_time: float | None = None

        self._flush_task: asyncio.Task[None] | None = None
        self._registry_task: asyncio.Task[None] | None = None
        self._running: bool = False

    @property
    def total_queued_rows(self) -> int:
        """Devuelve el total acumulado de filas pendientes de inserción en el buffer."""
        return (
            len(self._q_packet)
            + len(self._q_reception)
            + len(self._q_neighbor)
            + len(self._q_position)
            + len(self._q_telem_dev)
            + len(self._q_telem_env)
            + len(self._q_local_stats)
            + len(self._q_late_updates)
        )

    def start_background_tasks(self) -> None:
        """Inicia los bucles asíncronos periódicos de vaciado."""
        self._running = True
        self._flush_task = asyncio.create_task(self._periodic_flush_loop())
        self._registry_task = asyncio.create_task(self._periodic_registry_loop())

    async def stop(self) -> None:
        """Detiene los bucles y drena las colas pendientes de escritura."""
        self._running = False
        if self._flush_task:
            self._flush_task.cancel()
        if self._registry_task:
            self._registry_task.cancel()

        # Drenar todo lo que quede en cola
        await self.flush_all_batches()
        await self.flush_dirty_registry()

    def enqueue_unified_packet(self, packet: UnifiedPacket) -> None:
        """Encola un paquete unificado y sus entidades hijas para inserción."""
        self._check_buffer_limits()

        # 1. Fila de tabla packet
        payload_json = json.dumps(packet.payload, ensure_ascii=False, separators=(",", ":"))
        self._q_packet.append((
            packet.rx_first,
            packet.from_id,
            packet.to_id,
            packet.packet_id,
            packet.channel,
            packet.portnum,
            packet.portnum_num,
            packet.variant,
            packet.decrypt_status,
            packet.hop_start,
            packet.hops_min,
            len(packet.receptions),
            packet.want_ack,
            packet.via_mqtt,
            packet.ok_to_mqtt,
            packet.size_bytes,
            packet.airtime_ms,
            packet.first_gateway,
            packet.province,
            payload_json,
        ))

        # 2. Filas de tabla reception
        for r in packet.receptions:
            self._q_reception.append((
                r.at,
                packet.from_id,
                packet.packet_id,
                r.gateway_id,
                r.snr,
                r.rssi,
                r.hop_limit,
                r.hops,
                r.relay_node,
                r.gw_rx_time,
                r.own,
                r.direct,
                r.distance_km,
            ))

        # 3. Filas especializadas según payload
        p = packet.payload
        if packet.portnum in ("position", "map_report"):
            if "latitude" in p and "longitude" in p:
                gps_time: datetime | None = None
                raw_time = p.get("time")
                if raw_time:
                    try:
                        gps_time = datetime.fromtimestamp(raw_time, timezone.utc)
                    except (ValueError, OSError, OverflowError):
                        gps_time = None

                alt_val = p.get("altitude")
                self._q_position.append((
                    packet.rx_first,
                    packet.from_id,
                    p["latitude"],
                    p["longitude"],
                    int(alt_val) if alt_val is not None else None,
                    p.get("precision_bits"),
                    p.get("precision_m"),
                    packet.portnum,
                    gps_time,
                    packet.province,
                    p.get("border_uncertain", False),
                ))

        elif packet.portnum == "telemetry":
            if "device_metrics" in p:
                dm = p["device_metrics"]
                self._q_telem_dev.append((
                    packet.rx_first,
                    packet.from_id,
                    dm.get("battery_level"),
                    dm.get("voltage"),
                    dm.get("channel_utilization"),
                    dm.get("air_util_tx"),
                    dm.get("uptime_seconds"),
                    dm.get("reboot", False),
                ))
            elif "environment_metrics" in p:
                self._q_telem_env.append((
                    packet.rx_first,
                    packet.from_id,
                    json.dumps(p["environment_metrics"], ensure_ascii=False),
                ))
            elif "local_stats" in p:
                ls = p["local_stats"]
                self._q_local_stats.append((
                    packet.rx_first,
                    packet.from_id,
                    ls.get("uptime_seconds"),
                    ls.get("channel_utilization"),
                    ls.get("air_util_tx"),
                    ls.get("num_packets_tx"),
                    ls.get("num_packets_rx"),
                    ls.get("num_packets_rx_bad"),
                    ls.get("num_rx_dupe"),
                    ls.get("num_tx_relay"),
                    ls.get("num_tx_relay_canceled"),
                    ls.get("num_online_nodes"),
                    ls.get("num_total_nodes"),
                ))

        elif packet.portnum == "neighborinfo":
            if "neighbors" in p:
                for n in p["neighbors"]:
                    self._q_neighbor.append((
                        packet.rx_first,
                        packet.from_id,
                        n["id"],
                        n["snr"],
                    ))

    def enqueue_late_reception(self, packet: UnifiedPacket, r: ReceptionItem) -> None:
        """Encola una recepción tardía para inserción y actualización de packet."""
        self._check_buffer_limits()

        self._q_reception.append((
            r.at,
            packet.from_id,
            packet.packet_id,
            r.gateway_id,
            r.snr,
            r.rssi,
            r.hop_limit,
            r.hops,
            r.relay_node,
            r.gw_rx_time,
            r.own,
            r.direct,
            r.distance_km,
        ))

        self._q_late_updates.append((
            r.hops,
            packet.from_id,
            packet.packet_id,
            packet.rx_first,
        ))

    def enqueue_position(
        self,
        at: datetime,
        node_id: str,
        lat: float,
        lon: float,
        alt: int | float | None,
        precision_bits: int | None,
        precision_m: float | None,
        source: str,
        gps_time: datetime | None,
        province: str | None,
        border_uncertain: bool = False,
    ) -> None:
        """Encola directamente una posición geográfica para inserción en base de datos."""
        self._check_buffer_limits()
        self._q_position.append((
            at,
            node_id,
            lat,
            lon,
            int(alt) if alt is not None else None,
            precision_bits,
            precision_m,
            source,
            gps_time,
            province,
            border_uncertain,
        ))

    def _check_buffer_limits(self) -> None:
        """Controla el descarte FIFO si el buffer supera max_buffer_rows."""
        if self.total_queued_rows > self.max_buffer_rows:
            # Descartar primero recepciones secundarias más antiguas
            discard_count = min(1000, len(self._q_reception))
            if discard_count > 0:
                self._q_reception = self._q_reception[discard_count:]
                self.filas_descartadas_bd += discard_count

    async def _periodic_flush_loop(self) -> None:
        """Bucle periódico de vaciado de batches a base de datos."""
        while self._running:
            try:
                await asyncio.sleep(self.batch_interval_s)
                if self.total_queued_rows > 0:
                    await self.flush_all_batches()
            except asyncio.CancelledError:
                break
            except Exception as e:
                logger.warning("Error en bucle de vaciado de storage: %s", e)

    async def _periodic_registry_loop(self) -> None:
        """Bucle periódico de sincronización de nodos y gateways a base de datos cada 5s."""
        while self._running:
            try:
                await asyncio.sleep(5.0)
                await self.flush_dirty_registry()
            except asyncio.CancelledError:
                break
            except Exception as e:
                logger.warning("Error en bucle de sincronización de registro: %s", e)

    async def flush_all_batches(self) -> None:
        """Vuelca todas las colas mediante COPY masivo dentro de una transacción."""
        if self.total_queued_rows == 0:
            return

        # Snapshot de colas actuales
        packets = self._q_packet[:]
        receptions = self._q_reception[:]
        neighbors = self._q_neighbor[:]
        positions = self._q_position[:]
        telem_dev = self._q_telem_dev[:]
        telem_env = self._q_telem_env[:]
        local_stats = self._q_local_stats[:]
        late_updates = self._q_late_updates[:]

        try:
            async with self.pool.acquire() as conn:
                async with conn.transaction():
                    if packets:
                        await conn.copy_records_to_table(
                            "packet",
                            records=packets,
                            columns=[
                                "rx_first", "from_id", "to_id", "packet_id", "channel",
                                "portnum", "portnum_num", "variant", "decrypt_status",
                                "hop_start", "hops_min", "reception_count", "want_ack",
                                "via_mqtt", "ok_to_mqtt", "size_bytes", "airtime_ms",
                                "first_gateway", "province", "payload"
                            ],
                        )

                    if receptions:
                        await conn.copy_records_to_table(
                            "reception",
                            records=receptions,
                            columns=[
                                "rx_at", "from_id", "packet_id", "gateway_id", "snr",
                                "rssi", "hop_limit", "hops", "relay_node", "gw_rx_time",
                                "own", "direct", "distance_km"
                            ],
                        )

                    if neighbors:
                        await conn.copy_records_to_table(
                            "neighbor",
                            records=neighbors,
                            columns=["at", "node_id", "neighbor_id", "snr"],
                        )

                    if positions:
                        await conn.copy_records_to_table(
                            "position",
                            records=positions,
                            columns=[
                                "at", "node_id", "latitude", "longitude", "altitude",
                                "precision_bits", "precision_m", "source", "gps_time",
                                "province", "border_uncertain"
                            ],
                        )

                    if telem_dev:
                        await conn.copy_records_to_table(
                            "telemetry_device",
                            records=telem_dev,
                            columns=[
                                "at", "node_id", "battery_level", "voltage",
                                "channel_utilization", "air_util_tx", "uptime_seconds", "reboot"
                            ],
                        )

                    if telem_env:
                        await conn.copy_records_to_table(
                            "telemetry_env",
                            records=telem_env,
                            columns=["at", "node_id", "metrics"],
                        )

                    if local_stats:
                        await conn.copy_records_to_table(
                            "local_stats",
                            records=local_stats,
                            columns=[
                                "at", "node_id", "uptime_seconds", "channel_utilization",
                                "air_util_tx", "num_packets_tx", "num_packets_rx",
                                "num_packets_rx_bad", "num_rx_dupe", "num_tx_relay",
                                "num_tx_relay_canceled", "num_online_nodes", "num_total_nodes"
                            ],
                        )

                    # Procesar actualizaciones tardías en packet
                    if late_updates:
                        for hops_val, from_id, packet_id, rx_first in late_updates:
                            await conn.execute(
                                """
                                UPDATE packet
                                SET reception_count = packet.reception_count + 1,
                                    hops_min = LEAST(packet.hops_min, $1)
                                WHERE from_id = $2 AND packet_id = $3 AND rx_first = $4;
                                """,
                                hops_val,
                                from_id,
                                packet_id,
                                rx_first,
                            )

            # Limpiar colas procesadas
            saved_count = (
                len(packets)
                + len(receptions)
                + len(neighbors)
                + len(positions)
                + len(telem_dev)
                + len(telem_env)
                + len(local_stats)
            )
            self.filas_guardadas_bd += saved_count

            self._q_packet = self._q_packet[len(packets):]
            self._q_reception = self._q_reception[len(receptions):]
            self._q_neighbor = self._q_neighbor[len(neighbors):]
            self._q_position = self._q_position[len(positions):]
            self._q_telem_dev = self._q_telem_dev[len(telem_dev):]
            self._q_telem_env = self._q_telem_env[len(telem_env):]
            self._q_local_stats = self._q_local_stats[len(local_stats):]
            self._q_late_updates = self._q_late_updates[len(late_updates):]

            self.db_status = "conectada"

        except Exception as e:
            logger.error("Error vaciando lotes a PostgreSQL: %s", e)
            self.db_status = "error"

    async def flush_dirty_registry(self) -> None:
        """Vuelca nodos y gateways modificados a la base de datos con UPSERT."""
        nodes, gateways = self.registry.pop_dirty_records()
        if not nodes and not gateways:
            return

        try:
            async with self.pool.acquire() as conn:
                async with conn.transaction():
                    # UPSERT de nodos
                    if nodes:
                        node_records = [
                            (
                                n.id, n.node_num, n.short_name, n.long_name, n.role,
                                n.hw_model, n.firmware, n.public_key_fp, n.latitude,
                                n.longitude, n.position_precision_m, n.position_source,
                                n.last_position_at, n.province, n.border_uncertain,
                                n.hop_start_last, n.hops_min_last, n.is_gateway,
                                n.gateway_first_at, n.battery_level, n.voltage,
                                n.battery_at, n.channel_utilization, n.air_util_tx,
                                n.metrics_at, n.uptime_seconds, n.uptime_at,
                                n.last_reboot_at, n.first_seen, n.last_seen
                            )
                            for n in nodes
                        ]
                        await conn.executemany(
                            """
                            INSERT INTO node (
                                id, node_num, short_name, long_name, role, hw_model, firmware,
                                public_key_fp, latitude, longitude, position_precision_m,
                                position_source, last_position_at, province, border_uncertain,
                                hop_start_last, hops_min_last, is_gateway, gateway_first_at,
                                battery_level, voltage, battery_at, channel_utilization,
                                air_util_tx, metrics_at, uptime_seconds, uptime_at,
                                last_reboot_at, first_seen, last_seen
                            ) VALUES (
                                $1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13,
                                $14, $15, $16, $17, $18, $19, $20, $21, $22, $23, $24,
                                $25, $26, $27, $28, $29, $30
                            )
                            ON CONFLICT (id) DO UPDATE SET
                                short_name = COALESCE(EXCLUDED.short_name, node.short_name),
                                long_name = COALESCE(EXCLUDED.long_name, node.long_name),
                                role = COALESCE(EXCLUDED.role, node.role),
                                hw_model = COALESCE(EXCLUDED.hw_model, node.hw_model),
                                firmware = COALESCE(EXCLUDED.firmware, node.firmware),
                                public_key_fp = COALESCE(EXCLUDED.public_key_fp, node.public_key_fp),
                                latitude = COALESCE(EXCLUDED.latitude, node.latitude),
                                longitude = COALESCE(EXCLUDED.longitude, node.longitude),
                                position_precision_m = COALESCE(EXCLUDED.position_precision_m, node.position_precision_m),
                                position_source = COALESCE(EXCLUDED.position_source, node.position_source),
                                last_position_at = COALESCE(EXCLUDED.last_position_at, node.last_position_at),
                                province = COALESCE(EXCLUDED.province, node.province),
                                border_uncertain = EXCLUDED.border_uncertain,
                                hop_start_last = COALESCE(EXCLUDED.hop_start_last, node.hop_start_last),
                                hops_min_last = COALESCE(EXCLUDED.hops_min_last, node.hops_min_last),
                                is_gateway = node.is_gateway OR EXCLUDED.is_gateway,
                                gateway_first_at = COALESCE(node.gateway_first_at, EXCLUDED.gateway_first_at),
                                battery_level = COALESCE(EXCLUDED.battery_level, node.battery_level),
                                voltage = COALESCE(EXCLUDED.voltage, node.voltage),
                                battery_at = COALESCE(EXCLUDED.battery_at, node.battery_at),
                                channel_utilization = COALESCE(EXCLUDED.channel_utilization, node.channel_utilization),
                                air_util_tx = COALESCE(EXCLUDED.air_util_tx, node.air_util_tx),
                                metrics_at = COALESCE(EXCLUDED.metrics_at, node.metrics_at),
                                uptime_seconds = COALESCE(EXCLUDED.uptime_seconds, node.uptime_seconds),
                                uptime_at = COALESCE(EXCLUDED.uptime_at, node.uptime_at),
                                last_reboot_at = COALESCE(EXCLUDED.last_reboot_at, node.last_reboot_at),
                                last_seen = EXCLUDED.last_seen;
                            """,
                            node_records,
                        )

                    # UPSERT de gateways
                    if gateways:
                        gw_records = [
                            (g.id, g.first_message_at, g.last_message_at, g.messages_total, g.typical_interval_s)
                            for g in gateways
                        ]
                        await conn.executemany(
                            """
                            INSERT INTO gateway (
                                id, first_message_at, last_message_at, messages_total, typical_interval_s
                            ) VALUES ($1, $2, $3, $4, $5)
                            ON CONFLICT (id) DO UPDATE SET
                                last_message_at = EXCLUDED.last_message_at,
                                messages_total = EXCLUDED.messages_total,
                                typical_interval_s = COALESCE(EXCLUDED.typical_interval_s, gateway.typical_interval_s);
                            """,
                            gw_records,
                        )

            self.db_status = "conectada"

        except Exception as e:
            logger.error("Error sincronizando registro de nodos/gateways: %s", e)
            self.db_status = "error"
