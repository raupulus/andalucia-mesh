"""Gestor de retención de datos históricos y destinos inactivos."""

import asyncio
import contextlib
import logging
from datetime import UTC, datetime, time
from zoneinfo import ZoneInfo

from nucleo.base import GestorBase

logger = logging.getLogger("nucleo.retencion")


class GestorRetencion:
    """Ejecuta purgas periódicas de registros con más de RETENCION_DIAS días."""

    def __init__(
        self,
        gestor_base: GestorBase,
        tabla_principal: str,  # 'envio' para bots o 'entrega' para webhooks
        dias_retencion: int = 365,
        zona_horaria: str = "Europe/Madrid",
    ) -> None:
        """Inicializa el gestor de retención."""
        self.gestor_base = gestor_base
        self.tabla_principal = tabla_principal
        self.dias_retencion = dias_retencion
        self.tz = ZoneInfo(zona_horaria)
        self._tarea: asyncio.Task[None] | None = None
        self._deteniendo: bool = False
        self.ultima_ejecucion_at: datetime | None = None

    async def iniciar(self) -> None:
        """Inicia la tarea programada en segundo plano."""
        self._deteniendo = False
        self._tarea = asyncio.create_task(self._bucle_programado())
        logger.info(
            "Gestor de retención iniciado para tabla %s (%d días).",
            self.tabla_principal,
            self.dias_retencion,
        )

    async def detener(self) -> None:
        """Detiene de forma limpia la tarea de retención."""
        self._deteniendo = True
        if self._tarea:
            self._tarea.cancel()
            with contextlib.suppress(asyncio.CancelledError):
                await self._tarea
            self._tarea = None
        logger.info("Gestor de retención detenido.")

    async def purgar_ahora(self) -> tuple[int, int]:
        """Ejecuta la purga inmediata de registros antiguos por lotes de 5.000."""
        total_registros_borrados = 0
        total_destinos_borrados = 0

        async with self.gestor_base.conexion() as conn:
            # 1. Purgar tabla principal (envio o entrega) por lotes
            while True:
                async with conn.transaction(), conn.cursor() as cur:
                    consulta = f"""
                            DELETE FROM {self.tabla_principal}
                            WHERE id IN (
                                SELECT id FROM {self.tabla_principal}
                                WHERE creado_en < now() - (%s || ' days')::interval
                                LIMIT 5000
                            )
                        """
                    await cur.execute(consulta, (self.dias_retencion,))
                    borrados = cur.rowcount
                    total_registros_borrados += max(0, borrados)
                    if borrados < 5000:
                        break

            # 2. Purgar destinos inactivos con baja_en anterior al periodo de retención
            async with conn.transaction(), conn.cursor() as cur:
                await cur.execute(
                    """
                        DELETE FROM destino
                        WHERE id IN (
                            SELECT id FROM destino
                            WHERE activo = false
                              AND baja_en IS NOT NULL
                              AND baja_en < now() - (%s || ' days')::interval
                            LIMIT 5000
                        )
                        """,
                    (self.dias_retencion,),
                )
                total_destinos_borrados = max(0, cur.rowcount)

        self.ultima_ejecucion_at = datetime.now(UTC)
        logger.info(
            "Purga de retención completada: %d filas en %s, %d destinos inactivos eliminados.",
            total_registros_borrados,
            self.tabla_principal,
            total_destinos_borrados,
        )
        return total_registros_borrados, total_destinos_borrados

    async def _bucle_programado(self) -> None:
        """Ejecuta una pasada inicial si hace falta y programa la ejecución diaria a las 04:30."""
        # Pasada inicial al arrancar
        try:
            await self.purgar_ahora()
        except Exception as e:
            logger.error("Error en pasada inicial de retención: %s", e)

        while not self._deteniendo:
            try:
                ahora_local = datetime.now(self.tz)
                hora_objetivo = time(4, 30, 0)
                proxima = datetime.combine(ahora_local.date(), hora_objetivo, tzinfo=self.tz)

                if proxima <= ahora_local:
                    from datetime import timedelta
                    proxima = datetime.combine(ahora_local.date() + timedelta(days=1), hora_objetivo, tzinfo=self.tz)

                segundos_espera = (proxima - ahora_local).total_seconds()
                logger.info(
                    "Próxima purga de retención programada para %s (en %.0f s).",
                    proxima.isoformat(),
                    segundos_espera,
                )
                await asyncio.sleep(segundos_espera)

                if not self._deteniendo:
                    await self.purgar_ahora()

            except asyncio.CancelledError:
                break
            except Exception as e:
                logger.error("Error en bucle de retención: %s. Esperando 1 hora...", e)
                await asyncio.sleep(3600)
