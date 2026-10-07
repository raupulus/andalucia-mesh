"""Pruebas del temporizador periódico sincronizado del motor."""

from unittest.mock import MagicMock

import pytest

from detector.config import Settings
from detector.motor.motor import MotorAlertas
from detector.motor.temporizador import TemporizadorMotor


@pytest.mark.asyncio
async def test_temporizador_inicio_y_parada() -> None:
    """Verifica que el temporizador inicie y se detenga limpiamente sin fugas de tareas."""
    settings = Settings()
    motor = MotorAlertas(settings=settings)
    callback = MagicMock()

    temporizador = TemporizadorMotor(motor=motor, on_transiciones=callback)
    assert not temporizador.running

    await temporizador.start()
    assert temporizador.running
    assert temporizador._task is not None

    # Doble inicio idempotente
    await temporizador.start()
    assert temporizador.running

    await temporizador.stop()
    assert not temporizador.running
    assert temporizador._task.done()

    # Doble parada idempotente
    await temporizador.stop()
    assert not temporizador.running
