"""Pruebas adicionales de cobertura para protocol, config y server."""

import asyncio
import json
import logging

from websockets.asyncio.client import connect
from websockets.asyncio.server import serve
from websockets.exceptions import ConnectionClosed

from chat_ws.__main__ import setup_logging
from chat_ws.config import get_settings
from chat_ws.protocol import format_iso_utc, parse_client_message
from chat_ws.server import ChatServer


def test_config_singleton_and_logging() -> None:
    """Verifica la función get_settings y setup_logging."""
    s1 = get_settings()
    s2 = get_settings()
    assert s1 is s2
    setup_logging("DEBUG")
    assert logging.getLogger().level == logging.DEBUG


def test_protocol_edge_cases() -> None:
    """Verifica casos borde de parse_client_message y format_iso_utc."""
    # Bytes inválidos UTF-8
    data, err = parse_client_message(b"\xff\xfe\xfd")
    assert data is None
    assert "Codificación no válida" in str(err)

    # Elementos de channels no strings
    data, err = parse_client_message(json.dumps({"type": "subscribe", "channels": [123, 456]}))
    assert data is None
    assert "deben ser cadenas" in str(err)

    # JSON que no es dict (es una lista)
    data, err = parse_client_message(json.dumps(["hola"]))
    assert data is None
    assert "debe ser un objeto JSON" in str(err)

    # format_iso_utc con error de parseo
    assert format_iso_utc("no-una-fecha") == "no-una-fecha"


async def test_server_too_many_connections_rejection() -> None:
    """Verifica que el servidor rechace conexiones excedentes con código 1013."""
    s = get_settings()
    # Copiamos settings pero con max_per_ip = 1
    s_custom = s.model_copy(update={"CHAT_MAX_POR_IP": 1, "CHAT_PUERTO": 8981})
    server = ChatServer(s_custom)

    ws_server = await serve(server.handle_connection, "127.0.0.1", 8981)

    try:
        # Primera conexión: permitida
        async with connect("ws://127.0.0.1:8981/ws/chat") as c1:
            msg1 = await c1.recv()
            assert json.loads(msg1)["type"] == "hello"

            # Segunda conexión desde la misma IP: debe rechazarse con 1013
            try:
                async with connect("ws://127.0.0.1:8981/ws/chat") as c2:
                    raw_err = await c2.recv()
                    err = json.loads(raw_err)
                    assert err["type"] == "error"
                    assert err["code"] == "too_many_connections"
                    await c2.recv()
            except ConnectionClosed as exc:
                assert exc.rcvd is not None
                assert exc.rcvd.code == 1013
    finally:
        ws_server.close()
        await ws_server.wait_closed()


async def test_server_invalid_messages_closure() -> None:
    """Verifica que tras 5 mensajes no válidos se cierre con código 1008."""
    s = get_settings()
    s_custom = s.model_copy(update={
        "CHAT_PUERTO": 8982,
        "MAX_INVALID_MSGS_PER_MINUTE": 2,  # Reducido para la prueba
    })
    server = ChatServer(s_custom)

    ws_server = await serve(server.handle_connection, "127.0.0.1", 8982)

    try:
        async with connect("ws://127.0.0.1:8982/ws/chat") as client:
            await client.recv()  # hello

            # 1er mensaje no válido -> error invalid_message
            await client.send(json.dumps({"type": "invalido", "channels": []}))
            err1 = json.loads(await client.recv())
            assert err1["code"] == "invalid_message"

            # 2º mensaje no válido -> alcanza el umbral y cierra con 1008
            await client.send(json.dumps({"type": "invalido", "channels": []}))
            try:
                while True:
                    await asyncio.wait_for(client.recv(), timeout=1.0)
            except ConnectionClosed as exc:
                assert exc.rcvd is not None
                assert exc.rcvd.code == 1008
    finally:
        ws_server.close()
        await ws_server.wait_closed()
