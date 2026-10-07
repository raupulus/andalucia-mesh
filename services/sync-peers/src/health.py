"""Servidor de salud HTTP para el microservicio sync-peers.

Expone el endpoint /health en el puerto interno 8080 (solo red mesh)
para monitorización de Docker y orquestación del sistema.
"""

from __future__ import annotations

import asyncio
from typing import Any

from aiohttp import web
import asyncpg

from .db import get_all_peer_states
from .syncer import PeerSyncer


class HealthServer:
    """Servidor web aiohttp para comprobación de estado."""

    def __init__(
        self,
        port: int,
        pool: asyncpg.Pool,
        syncer: PeerSyncer,
    ) -> None:
        """Inicializa el servidor de salud.

        Args:
            port: Puerto TCP en el que escuchar (por defecto 8080).
            pool: Pool de conexiones PostgreSQL.
            syncer: Instancia del orquestador de sincronización.
        """
        self.port = port
        self.pool = pool
        self.syncer = syncer
        self.app = web.Application()
        self.app.router.add_get("/health", self.handle_health)
        self._runner: web.AppRunner | None = None

    async def handle_health(self, request: web.Request) -> web.Response:
        """Manejador HTTP para /health."""
        is_ok = True
        motivos: list[str] = []

        # 1. Comprobar base de datos
        peers_list: list[dict[str, Any]] = []
        try:
            peers_list = await get_all_peer_states(self.pool)
        except Exception as exc:
            is_ok = False
            motivos.append(f"Fallo de conexión con PostgreSQL: {exc}")

        # 2. Comprobar PotatoMesh local
        if self.syncer.potatomesh_status == "token_rechazado":
            is_ok = False
            motivos.append("Token de PotatoMesh local rechazado (401/403)")
        elif self.syncer.potatomesh_status in ("desconectado", "error_servidor"):
            now = asyncio.get_event_loop().time()
            if (now - self.syncer._potatomesh_last_fail) > 300.0:
                is_ok = False
                motivos.append("PotatoMesh local inaccesible durante más de 5 minutos")

        status_code = 200 if is_ok else 503

        data: dict[str, Any] = {
            "ok": is_ok,
            "potatomesh": self.syncer.potatomesh_status,
            "peers": peers_list,
        }
        if not is_ok:
            data["motivo"] = "; ".join(motivos)

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
