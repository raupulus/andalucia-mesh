"""Cliente HTTP asíncrono para consumir la API pública del portal con caché en memoria."""

import logging
import time
from typing import Any

import aiohttp

from nucleo.config import ConfiguracionBots

logger = logging.getLogger("nucleo.api_portal")


class ClientePortal:
    """Consulta los endpoints /api/v1 del portal para comandos de consulta (/status, /battery, /routers)."""

    def __init__(self, config: ConfiguracionBots, servicio: str) -> None:
        """Inicializa el cliente de la API del portal."""
        self.config = config
        self.servicio = servicio
        self.base_url = config.portal_api_url.rstrip("/")
        self.user_agent = (
            f"{config.project_name} {servicio}/1.0.0 "
            f"(+https://{config.project_domain}/bots; {config.project_contact})"
        )
        self.session: aiohttp.ClientSession | None = None
        self._cache: dict[str, tuple[float, dict[str, Any]]] = {}

    async def iniciar(self) -> None:
        """Abre la sesión HTTP de aiohttp."""
        timeout = aiohttp.ClientTimeout(total=float(self.config.api_timeout_s))
        self.session = aiohttp.ClientSession(
            timeout=timeout,
            headers={"User-Agent": self.user_agent},
        )

    async def cerrar(self) -> None:
        """Cierra la sesión HTTP."""
        if self.session:
            await self.session.close()
            self.session = None

    async def _get(self, path: str, params: dict[str, str] | None = None) -> dict[str, Any] | None:
        """Realiza una petición GET con soporte de caché en memoria de 30 segundos."""
        if not self.session:
            await self.iniciar()
            assert self.session is not None

        url = f"{self.base_url}{path}"
        clave_cache = f"{url}?{sorted(params.items()) if params else ''}"

        # Comprobar caché
        ahora = time.monotonic()
        if clave_cache in self._cache:
            expira, datos = self._cache[clave_cache]
            if ahora < expira:
                return datos

        try:
            async with self.session.get(url, params=params) as resp:
                if resp.status == 200:
                    datos_json: dict[str, Any] = await resp.json()
                    self._cache[clave_cache] = (ahora + float(self.config.api_cache_s), datos_json)
                    return datos_json
                logger.warning("Respuesta no exitosa de la API del portal (%s): HTTP %d", url, resp.status)
                return None
        except TimeoutError:
            logger.warning("Timeout consultando la API del portal (%s).", url)
            return None
        except Exception as e:
            logger.warning("Error de red consultando la API del portal (%s): %s", url, e)
            return None

    async def obtener_resumen(self) -> dict[str, Any] | None:
        """Obtiene el estado general de la malla (GET /stats/summary)."""
        return await self._get("/stats/summary")

    async def obtener_routers(self, provincia: str | None = None) -> dict[str, Any] | None:
        """Obtiene la lista de routers (GET /routers?province=...)."""
        params = {"province": provincia} if provincia else None
        return await self._get("/routers", params=params)

    async def obtener_catalogo(self) -> dict[str, Any] | None:
        """Obtiene el catálogo de alertas (GET /alerts/catalog)."""
        return await self._get("/alerts/catalog")
