"""Planificador asíncrono de ticks periódicos cada 60 segundos."""

import asyncio
import contextlib
import logging
from collections.abc import Callable, Coroutine
from datetime import UTC, datetime
from typing import Any

from detector.motor.motor import MotorAlertas

logger = logging.getLogger(__name__)


class TemporizadorMotor:
    """Ejecutor de ticks periódicos en segundo plano alineados a cada minuto."""

    def __init__(
        self,
        motor: MotorAlertas,
        on_transiciones: Callable[[list[Any]], Coroutine[Any, Any, None]] | None = None,
    ) -> None:
        """Inicializa el temporizador."""
        self.motor = motor
        self.on_transiciones = on_transiciones
        self.running: bool = False
        self._task: asyncio.Task[None] | None = None
        self.ticks_lentos: int = 0
        self.ticks_solapados: int = 0

    async def start(self) -> None:
        """Inicia la tarea periódica del temporizador."""
        if self.running:
            return
        self.running = True
        self._task = asyncio.create_task(self._run_loop())
        logger.info("Temporizador periódico de 60 s iniciado.")

    async def stop(self) -> None:
        """Detiene el temporizador de forma segura."""
        if not self.running:
            return
        self.running = False
        if self._task and not self._task.done():
            self._task.cancel()
            with contextlib.suppress(asyncio.CancelledError):
                await self._task
        logger.info("Temporizador periódico detenido.")

    async def _run_loop(self) -> None:
        """Bucle principal de ejecución de ticks sincronizados al minuto."""
        en_ejecucion = False

        while self.running:
            try:
                # Sincronizar al siguiente segundo 0
                ahora = datetime.now(UTC)
                segundos_restantes = 60.0 - (ahora.second + ahora.microsecond / 1_000_000.0)
                if segundos_restantes < 0.1:
                    segundos_restantes += 60.0

                await asyncio.sleep(segundos_restantes)
                tick_time = datetime.now(UTC)

                if en_ejecucion:
                    self.ticks_solapados += 1
                    logger.warning("Tick de temporizador solapado descartado (total solapados: %d).", self.ticks_solapados)
                    continue

                en_ejecucion = True
                t0 = asyncio.get_event_loop().time()

                transiciones = self.motor.ejecutar_tick(tick_time)
                if transiciones and self.on_transiciones:
                    await self.on_transiciones(transiciones)

                duracion = asyncio.get_event_loop().time() - t0
                if duracion > 5.0:
                    self.ticks_lentos += 1
                    logger.warning("Tick de temporizador lento (duración: %.2fs, umbral: 5s).", duracion)

                en_ejecucion = False

            except asyncio.CancelledError:
                break
            except Exception as e:
                en_ejecucion = False
                logger.error("Excepción imprevista en el tick de temporizador: %s", e, exc_info=True)
                await asyncio.sleep(1.0)
