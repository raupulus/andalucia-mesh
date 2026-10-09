"""Gestión de instantáneas del estado del motor comprimidas en gzip."""

import gzip
import json
import logging
from datetime import datetime
from typing import Any

import asyncpg

from detector.motor.estado import EstadoGateway, EstadoMotor, EstadoNodo

logger = logging.getLogger(__name__)

FORMATO_SNAPSHOT = 1


class GestorInstantaneas:
    """Serializa y restaura instantáneas del estado del motor en PostgreSQL."""

    @staticmethod
    def serializar(estado: EstadoMotor) -> bytes:
        """Serializa el estado del motor a JSON y lo comprime con gzip."""
        nodes_data: dict[str, Any] = {}
        for nid, n in estado.nodes.items():
            nodes_data[nid] = {
                "short": n.short,
                "long": n.long,
                "role": n.role,
                "hw": n.hw,
                "is_gateway": n.is_gateway,
                "province": n.province,
                "first_seen": n.first_seen.isoformat(),
                "last_seen": n.last_seen.isoformat(),
                "last_uptime_seconds": n.last_uptime_seconds,
                "reboots_24h": [dt.isoformat() for dt in n.reboots_24h],
                "battery_samples": [
                    (dt.isoformat(), bat, volt) for dt, bat, volt in n.battery_samples
                ],
                "intervals": list(n.intervals),
                "hourly_packets": {str(k): v for k, v in n.hourly_packets.items()},
                "hop_starts": list(n.hop_starts),
                "last_channel_utilization": n.last_channel_utilization,
                "last_chutil_at": n.last_chutil_at.isoformat() if n.last_chutil_at else None,
            }

        gateways_data: dict[str, Any] = {}
        for gid, g in estado.gateways.items():
            gateways_data[gid] = {
                "last_seen_at": g.last_seen_at.isoformat(),
                "reception_intervals": list(g.reception_intervals),
            }

        known_ids_data = {nid: dt.isoformat() for nid, dt in estado.known_ids_90d.items()}

        payload = {
            "version": FORMATO_SNAPSHOT,
            "started_at": estado.started_at.isoformat(),
            "nodes": nodes_data,
            "gateways": gateways_data,
            "known_ids_90d": known_ids_data,
        }

        json_bytes = json.dumps(payload, ensure_ascii=False).encode("utf-8")
        return gzip.compress(json_bytes)

    @staticmethod
    def deserializar(blob: bytes) -> EstadoMotor:
        """Descomprime un blob gzip y reconstruye el objeto EstadoMotor."""
        json_bytes = gzip.decompress(blob)
        data: dict[str, Any] = json.loads(json_bytes.decode("utf-8"))

        started_at = datetime.fromisoformat(data["started_at"])
        estado = EstadoMotor(started_at=started_at)

        for nid, nd in data.get("nodes", {}).items():
            nodo = EstadoNodo(
                node_id=nid,
                short=nd.get("short"),
                long=nd.get("long"),
                role=nd.get("role"),
                hw=nd.get("hw"),
                is_gateway=bool(nd.get("is_gateway", False)),
                province=nd.get("province"),
                first_seen=datetime.fromisoformat(nd["first_seen"]),
                last_seen=datetime.fromisoformat(nd["last_seen"]),
                last_uptime_seconds=nd.get("last_uptime_seconds"),
            )
            nodo.reboots_24h.extend(datetime.fromisoformat(ts) for ts in nd.get("reboots_24h", []))
            for ts, bat, volt in nd.get("battery_samples", []):
                nodo.battery_samples.append((datetime.fromisoformat(ts), int(bat), float(volt)))
            nodo.intervals.extend(float(iv) for iv in nd.get("intervals", []))
            nodo.hourly_packets = {int(k): int(v) for k, v in nd.get("hourly_packets", {}).items()}
            nodo.hop_starts.extend(int(h) for h in nd.get("hop_starts", []))
            nodo.last_channel_utilization = nd.get("last_channel_utilization")
            if nd.get("last_chutil_at"):
                nodo.last_chutil_at = datetime.fromisoformat(nd["last_chutil_at"])

            estado.nodes[nid] = nodo

        for gid, gd in data.get("gateways", {}).items():
            gw = EstadoGateway(
                gateway_id=gid,
                last_seen_at=datetime.fromisoformat(gd["last_seen_at"]),
            )
            gw.reception_intervals.extend(float(iv) for iv in gd.get("reception_intervals", []))
            estado.gateways[gid] = gw

        for nid, ts in data.get("known_ids_90d", {}).items():
            estado.known_ids_90d[nid] = datetime.fromisoformat(ts)

        return estado

    @classmethod
    async def guardar_en_db(cls, conn: asyncpg.Connection, estado: EstadoMotor) -> None:
        """Guarda una instantánea comprimida en la base de datos."""
        blob = cls.serializar(estado)
        num_nodos = len(estado.nodes)
        await conn.execute(
            """
            INSERT INTO estado_snapshot (formato, nodos, datos)
            VALUES ($1, $2, $3)
            """,
            FORMATO_SNAPSHOT,
            num_nodos,
            blob,
        )
        logger.debug("Instantánea de estado guardada en DB (%d nodos, %d bytes comprimidos).", num_nodos, len(blob))

    @classmethod
    async def restaurar_desde_db(cls, conn: asyncpg.Connection) -> EstadoMotor | None:
        """Restaura la última instantánea válida desde la base de datos."""
        row = await conn.fetchrow(
            """
            SELECT datos FROM estado_snapshot
            WHERE formato = $1
            ORDER BY id DESC LIMIT 1
            """,
            FORMATO_SNAPSHOT,
        )
        if not row:
            logger.info("No se encontraron instantáneas de estado previas en DB.")
            return None

        try:
            blob: bytes = row["datos"]
            estado = cls.deserializar(blob)
            logger.info("Estado restaurado desde DB (%d nodos, %d gateways).", len(estado.nodes), len(estado.gateways))
            return estado
        except Exception as e:
            logger.error("Error al deserializar la instantánea de estado: %s", e)
            return None

    @classmethod
    async def restaurar_ciclo_desde_db(cls, conn: asyncpg.Connection, ciclo: Any) -> None:
        """Restaura en memoria las alertas abiertas y resueltas recientes en el GestorCicloVida."""
        from detector.motor.protocolos import AlertaAbierta

        query_abiertas = """
            SELECT id, regla, nodo, riesgo, tipo, mensaje, nodos, nodo_info,
                   datos, estado, abierta_en, actualizada_en, resuelta_en,
                   reaperturas, evidencia_en
            FROM alerta
            WHERE estado = 'abierta';
        """
        try:
            filas_abiertas = await conn.fetch(query_abiertas)
            for r in filas_abiertas:
                nodo_info = r["nodo_info"]
                if isinstance(nodo_info, str):
                    nodo_info = json.loads(nodo_info)
                datos = r["datos"]
                if isinstance(datos, str):
                    datos = json.loads(datos)

                alerta = AlertaAbierta(
                    id=r["id"],
                    regla=r["regla"],
                    nodo=r["nodo"],
                    riesgo=r["riesgo"],
                    tipo=r["tipo"],
                    mensaje=r["mensaje"],
                    nodos=list(r["nodos"] or []),
                    nodo_info=nodo_info,
                    datos=dict(datos or {}),
                    estado=r["estado"],
                    abierta_en=r["abierta_en"],
                    actualizada_en=r["actualizada_en"],
                    resuelta_en=r["resuelta_en"],
                    reaperturas=r["reaperturas"],
                    evidencia_en=r["evidencia_en"],
                    ultima_transicion_en=r["actualizada_en"],
                )
                ciclo.alertas_abiertas[alerta.clave] = alerta
            logger.info("Restauradas %d alertas abiertas en ciclo de vida desde DB.", len(filas_abiertas))

            query_resueltas = """
                SELECT id, regla, nodo, riesgo, tipo, mensaje, nodos, nodo_info,
                       datos, estado, abierta_en, actualizada_en, resuelta_en,
                       reaperturas, evidencia_en
                FROM alerta
                WHERE estado = 'resuelta'
                  AND resuelta_en >= now() - interval '2 hours';
            """
            filas_resueltas = await conn.fetch(query_resueltas)
            for r in filas_resueltas:
                nodo_info = r["nodo_info"]
                if isinstance(nodo_info, str):
                    nodo_info = json.loads(nodo_info)
                datos = r["datos"]
                if isinstance(datos, str):
                    datos = json.loads(datos)

                alerta = AlertaAbierta(
                    id=r["id"],
                    regla=r["regla"],
                    nodo=r["nodo"],
                    riesgo=r["riesgo"],
                    tipo=r["tipo"],
                    mensaje=r["mensaje"],
                    nodos=list(r["nodos"] or []),
                    nodo_info=nodo_info,
                    datos=dict(datos or {}),
                    estado=r["estado"],
                    abierta_en=r["abierta_en"],
                    actualizada_en=r["actualizada_en"],
                    resuelta_en=r["resuelta_en"],
                    reaperturas=r["reaperturas"],
                    evidencia_en=r["evidencia_en"],
                    ultima_transicion_en=r["actualizada_en"],
                )
                ciclo.historial_resueltas[alerta.clave] = alerta
            logger.info("Restauradas %d alertas resueltas recientes en ciclo de vida desde DB.", len(filas_resueltas))
        except Exception as e:
            logger.error("Error al restaurar alertas en ciclo de vida desde DB: %s", e)

