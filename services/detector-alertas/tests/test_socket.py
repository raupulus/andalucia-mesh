"""Pruebas del servidor de socket UNIX de difusión de alertas."""

import asyncio
import contextlib
import json
import os
import uuid
from collections.abc import Generator
from datetime import UTC, datetime
from typing import Any
from unittest.mock import AsyncMock

import pytest

from detector.config import Settings
from detector.modelos import AlertaObjeto
from detector.motor.protocolos import TransicionAlerta
from detector.persistencia import GestorPersistencia
from detector.socket_alertas import ServidorSocketAlertas


@pytest.fixture
def socket_path() -> Generator[str]:
    """Ruta efímera corta en /tmp para evitar desbordamiento AF_UNIX (máx 104 bytes en macOS)."""
    p = f"/tmp/snm_{uuid.uuid4().hex[:8]}.sock"
    yield p
    if os.path.exists(p):
        with contextlib.suppress(OSError):
            os.unlink(p)


@pytest.fixture
def mock_persistencia() -> GestorPersistencia:
    """Mock del gestor de persistencia."""
    mock = AsyncMock(spec=GestorPersistencia)
    mock.obtener_transiciones_reenvio.return_value = []
    return mock


@pytest.mark.asyncio
async def test_socket_saludo_correcto(socket_path: str, mock_persistencia: GestorPersistencia) -> None:
    """Verifica que un cliente que envía un saludo válido queda registrado y recibe mensajes."""
    settings = Settings(alertas_socket=socket_path, saludo_timeout_s=2.0)
    servidor = ServidorSocketAlertas(settings=settings, persistencia=mock_persistencia)
    await servidor.iniciar()

    try:
        reader, writer = await asyncio.open_unix_connection(path=socket_path)
        saludo = {"cliente": "bot-test", "desde": None}
        writer.write((json.dumps(saludo) + "\n").encode("utf-8"))
        await writer.drain()

        # Dar tiempo a que el servidor registre la conexión
        await asyncio.sleep(0.05)
        assert servidor.total_clientes == 1

        # Emitir una transición
        t = TransicionAlerta(
            transicion_id="01JAC0Q4M1K2J3H4G5F6E7D801",
            alerta_id="01JAC0Q4M1K2J3H4G5F6E7D800",
            transicion="abierta",
            en=datetime(2026, 10, 1, 12, 0, 0, tzinfo=UTC),
            alerta=AlertaObjeto(
                id="01JAC0Q4M1K2J3H4G5F6E7D800",
                regla="reboot-loop",
                riesgo="alto",
                tipo="infraestructura",
                mensaje="Nodo reiniciado",
                nodo="!a1b2c3d4",
                estado="abierta",
                abierta_en="2026-10-01T12:00:00Z",
                actualizada_en="2026-10-01T12:00:00Z",
            ),
        )
        servidor.emitir_transicion(t)

        linea = await asyncio.wait_for(reader.readline(), timeout=2.0)
        datos = json.loads(linea.decode("utf-8"))
        assert datos["v"] == 1
        assert datos["transicion_id"] == "01JAC0Q4M1K2J3H4G5F6E7D801"
        assert datos["transicion"] == "abierta"
        assert datos["alerta"]["regla"] == "reboot-loop"

        writer.close()
        await writer.wait_closed()
        await asyncio.sleep(0.05)
        assert servidor.total_clientes == 0
    finally:
        await servidor.detener()


@pytest.mark.asyncio
async def test_socket_saludo_invalido_desconexion(
    socket_path: str, mock_persistencia: GestorPersistencia
) -> None:
    """Verifica que un cliente sin saludo o con JSON inválido sea desconectado inmediatamente."""
    settings = Settings(alertas_socket=socket_path, saludo_timeout_s=1.0)
    servidor = ServidorSocketAlertas(settings=settings, persistencia=mock_persistencia)
    await servidor.iniciar()

    try:
        reader, writer = await asyncio.open_unix_connection(path=socket_path)
        # Enviar JSON sin campo 'cliente'
        writer.write(b'{"sin_cliente": true}\n')
        await writer.drain()

        # El servidor debe cerrar la conexión
        linea = await reader.readline()
        assert linea == b""
        assert servidor.total_clientes == 0
        writer.close()
        await writer.wait_closed()
    finally:
        await servidor.detener()


@pytest.mark.asyncio
async def test_socket_reenvio_historico(socket_path: str, mock_persistencia: GestorPersistencia) -> None:
    """Verifica que un cliente que especifica 'desde' recibe transiciones históricas antes del directo."""
    t_historica: tuple[str, str, dict[str, Any]] = (
        "01JAC0Q4M1K2J3H4G5F6E7D801",
        "abierta",
        {
            "id": "01JAC0Q4M1K2J3H4G5F6E7D800",
            "regla": "battery-low",
            "riesgo": "medio",
            "tipo": "infraestructura",
            "mensaje": "Batería baja",
            "nodo": "!b2c3d4e5",
            "estado": "abierta",
            "abierta_en": "2026-10-01T11:00:00Z",
            "actualizada_en": "2026-10-01T11:00:00Z",
        },
    )
    mock_persistencia.obtener_transiciones_reenvio = AsyncMock(return_value=[t_historica])  # type: ignore[method-assign]

    settings = Settings(alertas_socket=socket_path, saludo_timeout_s=2.0)
    servidor = ServidorSocketAlertas(settings=settings, persistencia=mock_persistencia)
    await servidor.iniciar()

    try:
        reader, writer = await asyncio.open_unix_connection(path=socket_path)
        saludo = {"cliente": "bot-reconectado", "desde": "01JAC0Q4M1K2J3H4G5F6E7D800"}
        writer.write((json.dumps(saludo) + "\n").encode("utf-8"))
        await writer.drain()

        linea = await asyncio.wait_for(reader.readline(), timeout=2.0)
        datos = json.loads(linea.decode("utf-8"))
        assert datos["transicion_id"] == "01JAC0Q4M1K2J3H4G5F6E7D801"
        assert datos["alerta"]["regla"] == "battery-low"

        writer.close()
        await writer.wait_closed()
    finally:
        await servidor.detener()


@pytest.mark.asyncio
async def test_socket_cliente_lento_desconexion(
    socket_path: str, mock_persistencia: GestorPersistencia
) -> None:
    """Verifica que un cliente que acumula más mensajes de su límite es desconectado."""
    settings = Settings(
        alertas_socket=socket_path,
        saludo_timeout_s=2.0,
        cliente_max_pendientes=5,  # Límite bajo para la prueba
    )
    servidor = ServidorSocketAlertas(settings=settings, persistencia=mock_persistencia)
    await servidor.iniciar()

    try:
        reader, writer = await asyncio.open_unix_connection(path=socket_path)
        saludo = {"cliente": "bot-lento", "desde": None}
        writer.write((json.dumps(saludo) + "\n").encode("utf-8"))
        await writer.drain()

        await asyncio.sleep(0.05)
        assert servidor.total_clientes == 1

        # Emitir 10 transiciones sin que el cliente las lea del reader
        for i in range(10):
            t = TransicionAlerta(
                transicion_id=f"01JAC0Q4M1K2J3H4G5F6E7D8{i:02d}",
                alerta_id="01JAC0Q4M1K2J3H4G5F6E7D800",
                transicion="actualizada",
                en=datetime.now(UTC),
                alerta=AlertaObjeto(
                    id="01JAC0Q4M1K2J3H4G5F6E7D800",
                    regla="reboot-loop",
                    riesgo="alto",
                    tipo="infraestructura",
                    mensaje="Nodo reiniciado",
                    nodo="!a1b2c3d4",
                    estado="abierta",
                    abierta_en="2026-10-01T12:00:00Z",
                    actualizada_en="2026-10-01T12:00:00Z",
                ),
            )
            servidor.emitir_transicion(t)

        await asyncio.sleep(0.05)
        # El cliente lento debió ser expulsado
        assert servidor.total_clientes == 0
        writer.close()
        await writer.wait_closed()
    finally:
        await servidor.detener()
