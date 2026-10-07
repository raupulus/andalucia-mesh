"""Servidor de salud HTTP para el microservicio adaptador-potato.

Expone el endpoint /health en el puerto interno 8080 (solo red mesh)
para monitorización de Docker y orquestación del sistema.
"""

from __future__ import annotations

import time
from typing import Any

from aiohttp import web

from .mqtt import MqttProcessor
from .sender import PotatoSender


class HealthServer:
    """Servidor web aiohttp para inspección de salud."""

    def __init__(
        self,
        port: int,
        mqtt_proc: MqttProcessor,
        sender: PotatoSender,
    ) -> None:
        """Inicializa el servidor de salud.

        Args:
            port: Puerto TCP en el que escuchar (por defecto 8080).
            mqtt_proc: Procesador MQTT para verificar estado de conexión.
            sender: Despachador para verificar cola y último envío.
        """
        self.port = port
        self.mqtt_proc = mqtt_proc
        self.sender = sender
        self.app = web.Application()
        self.app.router.add_get("/health", self.handle_health)
        self._runner: web.AppRunner | None = None

    async def handle_health(self, request: web.Request) -> web.Response:
        """Manejador HTTP para /health."""
        now = time.monotonic()
        reasons: list[str] = []

        # 1. Estado MQTT
        mqtt_status = "conectado" if self.mqtt_proc.is_connected else "desconectado"
        if not self.mqtt_proc.is_connected and self.mqtt_proc.disconnected_since:
            if (now - self.mqtt_proc.disconnected_since) > 60.0:
                reasons.append("MQTT desconectado > 60s")

        # 2. Cola y envíos a PotatoMesh
        cola_len = self.sender.queue_size
        if cola_len > 8000:
            reasons.append("cola interna > 8000")

        # 3. Estado de autenticación o caída en PotatoMesh
        if self.sender.potatomesh_status == "token_rechazado":
            reasons.append("token rechazado por PotatoMesh")

        # 4. Sin envíos correctos con cola acumulada durante más de 5 minutos
        if cola_len > 0 and self.sender.ultimo_intento > 0:
            time_since_attempt = now - self.sender.ultimo_intento
            if time_since_attempt > 300.0:
                reasons.append("sin envíos correctos a PotatoMesh > 5m habiendo cola")

        is_ok = len(reasons) == 0
        status_code = 200 if is_ok else 503

        data: dict[str, Any] = {
            "ok": is_ok,
            "mqtt": mqtt_status,
            "potatomesh": self.sender.potatomesh_status,
            "cola": cola_len,
            "ultimo_envio_ok": self.sender.ultimo_envio_ok,
            "descartados_1h": self.sender.descartados_1h,
        }
        if not is_ok:
            data["motivo"] = "; ".join(reasons)

        return web.json_response(data, status=status_code)

    async def start(self) -> None:
        """Arranca el servidor web asíncrono."""
        self._runner = web.AppRunner(self.app)
        await self._runner.setup()
        site = web.TCPSite(self._runner, host="0.0.0.0", port=self.port)
        await site.start()

    async def stop(self) -> None:
        """Detiene el servidor web."""
        if self._runner:
            await self._runner.cleanup()
