"""Servidor HTTP de salud y telemetría operativa (/health) para snm-ingesta.

Expone métricas operativas en tiempo real en el puerto 8080 (solo red 'mesh')
para consumo por el panel de operadores y el healthcheck de Docker.
"""

from __future__ import annotations

import asyncio
from datetime import datetime, timezone
from typing import Any

from aiohttp import web

VERSION: str = "0.1.0"


class HealthReporter:
    """Acumulador de telemetría y generador de estados de salud."""

    def __init__(self) -> None:
        self.start_time = datetime.now(timezone.utc)
        self.mqtt_connected = False
        self.db_status = "conectada"

        self.last_message_at: datetime | None = None
        self._recent_msg_timestamps: list[float] = []

        # Contadores de tráfico
        self.mensajes_recibidos: int = 0
        self.paquetes_unicos: int = 0
        self.duplicados: int = 0
        self.recepciones_tardias: int = 0
        self.sin_ok_mqtt: int = 0
        self.descartado_canal: int = 0
        self.descartado_tamano: int = 0
        self.descartado_protobuf: int = 0
        self.descartado_gateway: int = 0
        self.cifrado_desconocido: int = 0
        self.pki: int = 0
        self.reinicios_detectados: int = 0

        # Enlace con publicador y almacenamiento
        self.get_decoded_stats: Any = None
        self.get_storage_stats: Any = None

    def record_incoming_message(self) -> None:
        """Registra la recepción de un nuevo mensaje para cálculo de ritmos."""
        now = datetime.now(timezone.utc)
        self.last_message_at = now
        self.mensajes_recibidos += 1

        now_ts = now.timestamp()
        self._recent_msg_timestamps.append(now_ts)
        # Mantener solo los últimos 60 segundos
        cutoff = now_ts - 60.0
        self._recent_msg_timestamps = [t for t in self._recent_msg_timestamps if t > cutoff]

    @property
    def current_rate_msg_s(self) -> float:
        """Calcula el ritmo medio de recepción de mensajes en el último minuto."""
        now_ts = datetime.now(timezone.utc).timestamp()
        cutoff = now_ts - 60.0
        recent = [t for t in self._recent_msg_timestamps if t > cutoff]
        if not recent:
            return 0.0
        return round(len(recent) / 60.0, 2)

    def generate_health_dict(self) -> tuple[dict[str, Any], int]:
        """Genera el diccionario de salud y el código HTTP correspondiente."""
        now = datetime.now(timezone.utc)
        uptime_s = int((now - self.start_time).total_seconds())

        sec_since_last: int | None = None
        if self.last_message_at:
            sec_since_last = int((now - self.last_message_at).total_seconds())

        decoded_enviados = 0
        decoded_perdidos = 0
        if self.get_decoded_stats:
            env, perd = self.get_decoded_stats()
            decoded_enviados = env
            decoded_perdidos = perd

        cola_bd = 0
        filas_guardadas = 0
        filas_descartadas = 0
        if self.get_storage_stats:
            q_len, saved, discarded, db_st = self.get_storage_stats()
            cola_bd = q_len
            filas_guardadas = saved
            filas_descartadas = discarded
            self.db_status = db_st

        # Estado global de salud
        is_ok = self.mqtt_connected and (self.db_status == "conectada")
        status_code = 200 if is_ok else 503

        data = {
            "ok": is_ok,
            "servicio": "ingesta",
            "version": VERSION,
            "uptime_s": uptime_s,
            "mqtt": "conectado" if self.mqtt_connected else "desconectado",
            "base_datos": self.db_status,
            "ultimo_mensaje_at": (
                self.last_message_at.isoformat().replace("+00:00", "Z")
                if self.last_message_at
                else None
            ),
            "segundos_desde_ultimo": sec_since_last,
            "ritmo_msg_s": self.current_rate_msg_s,
            "cola_bd": cola_bd,
            "contadores": {
                "mensajes_recibidos": self.mensajes_recibidos,
                "paquetes_unicos": self.paquetes_unicos,
                "duplicados": self.duplicados,
                "recepciones_tardias": self.recepciones_tardias,
                "sin_ok_mqtt": self.sin_ok_mqtt,
                "descartado_canal": self.descartado_canal,
                "descartado_tamano": self.descartado_tamano,
                "descartado_protobuf": self.descartado_protobuf,
                "descartado_gateway": self.descartado_gateway,
                "cifrado_desconocido": self.cifrado_desconocido,
                "pki": self.pki,
                "decoded_enviados": decoded_enviados,
                "decoded_perdidos": decoded_perdidos,
                "filas_guardadas_bd": filas_guardadas,
                "filas_descartadas_bd": filas_descartadas,
                "reinicios_detectados": self.reinicios_detectados,
            },
        }

        return data, status_code


async def start_health_server(
    reporter: HealthReporter,
    port: int = 8080,
) -> web.AppRunner:
    """Inicia el servidor web aiohttp para el endpoint /health."""
    app = web.Application()

    async def handle_health(request: web.Request) -> web.Response:
        data, code = reporter.generate_health_dict()
        return web.json_response(data, status=code)

    app.router.add_get("/health", handle_health)
    runner = web.AppRunner(app)
    await runner.setup()
    site = web.TCPSite(runner, "0.0.0.0", port)
    await site.start()
    return runner
