"""Servidor HTTP aiohttp para métricas y comprobación de salud en GET /health."""

import logging
from collections.abc import Awaitable, Callable
from typing import Any

from aiohttp import web

logger = logging.getLogger("nucleo.salud")


class ServidorSalud:
    """Expone el endpoint HTTP /health para el monitoreo del portal y orquestador."""

    def __init__(
        self,
        puerto: int,
        proveedor_estado: Callable[[], Awaitable[dict[str, Any]]],
    ) -> None:
        """Inicializa el servidor de salud."""
        self.puerto = puerto
        self.proveedor_estado = proveedor_estado
        self.app = web.Application()
        self.app.router.add_get("/health", self._handle_health)
        self.app.router.add_get("/", self._handle_health)
        self.runner: web.AppRunner | None = None
        self.site: web.TCPSite | None = None

    async def iniciar(self) -> None:
        """Arranca el servidor HTTP en el puerto configurado."""
        self.runner = web.AppRunner(self.app)
        await self.runner.setup()
        self.site = web.TCPSite(self.runner, host="0.0.0.0", port=self.puerto)
        await self.site.start()
        logger.info("Servidor de salud HTTP escuchando en http://0.0.0.0:%d/health", self.puerto)

    async def detener(self) -> None:
        """Detiene de forma limpia el servidor HTTP."""
        if self.runner:
            await self.runner.cleanup()
            self.runner = None
            self.site = None
            logger.info("Servidor de salud HTTP detenido.")

    async def _handle_health(self, request: web.Request) -> web.Response:
        """Genera el JSON de estado del servicio y retorna 200 o 503."""
        try:
            estado = await self.proveedor_estado()
            es_ok = bool(estado.get("ok", False))
            status_code = 200 if es_ok else 503
            return web.json_response(estado, status=status_code)
        except Exception as e:
            logger.error("Error generando respuesta de salud: %s", e)
            return web.json_response(
                {"ok": False, "error": str(e)},
                status=503,
            )
