"""Tarea programada diaria para retención y purgado de histórico en base de datos."""

import asyncio
import contextlib
import logging
from datetime import UTC, datetime
from zoneinfo import ZoneInfo

from detector.config import Settings
from detector.persistencia import GestorPersistencia

logger = logging.getLogger("detector.retencion")


class TareaRetencion:
    """Ejecuta diariamente a las 04:20 la limpieza de alertas resueltas y snapshots antiguos."""

    def __init__(self, settings: Settings, persistencia: GestorPersistencia) -> None:
        """Inicializa la tarea de retención."""
        self.settings = settings
        self.persistencia = persistencia
        self.ultima_ejecucion: datetime | None = None
        self.alertas_purgadas_total = 0
        self.snapshots_purgados_total = 0
        self._activo = False
        self._tarea: asyncio.Task[None] | None = None

    async def iniciar(self) -> None:
        """Inicia el bucle de supervisión diaria."""
        self._activo = True
        self._tarea = asyncio.create_task(self._bucle_supervision())
        logger.info("Tarea de retención programada (retención: %d días)", self.settings.retencion_dias)

    async def detener(self) -> None:
        """Detiene la tarea de retención."""
        self._activo = False
        if self._tarea:
            self._tarea.cancel()
            with contextlib.suppress(asyncio.CancelledError):
                await self._tarea
            self._tarea = None

    async def ejecutar_ahora(self) -> tuple[int, int]:
        """Ejecuta inmediatamente el purgado de retención."""
        alertas, snapshots = await self.persistencia.purgar_retencion(self.settings.retencion_dias)
        self.ultima_ejecucion = datetime.now(UTC)
        self.alertas_purgadas_total += alertas
        self.snapshots_purgados_total += snapshots
        return alertas, snapshots

    async def _bucle_supervision(self) -> None:
        """Supervisa el reloj y ejecuta la retención cuando son las 04:20 en la zona configurada."""
        zona = ZoneInfo(self.settings.tz)
        ultimo_dia_ejecutado: int = -1

        while self._activo:
            try:
                ahora_local = datetime.now(zona)
                if ahora_local.hour == 4 and ahora_local.minute == 20 and ahora_local.day != ultimo_dia_ejecutado:
                    logger.info("Ejecutando retención programada a las 04:20...")
                    await self.ejecutar_ahora()
                    ultimo_dia_ejecutado = ahora_local.day

                await asyncio.sleep(30.0)
            except asyncio.CancelledError:
                break
            except Exception as e:
                logger.error("Error en bucle de retención: %s", e)
                await asyncio.sleep(60.0)
