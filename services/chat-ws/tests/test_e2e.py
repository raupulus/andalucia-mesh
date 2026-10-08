"""Pruebas de integración extremo a extremo (E2E) del servidor WebSocket de chat."""

import asyncio
import json

import pytest
from websockets.asyncio.client import connect
from websockets.asyncio.server import serve

from chat_ws.config import Settings
from chat_ws.server import ChatServer


@pytest.mark.asyncio
async def test_websocket_chat_e2e_flow() -> None:
    """Verifica el flujo integral del protocolo WebSocket con un cliente real."""
    settings = Settings(
        ALLOWED_CHANNELS="SFNarrow,Cadiz,Huelva,sos",
        CHAT_PUERTO=8975,
        CHAT_HOST="127.0.0.1",
        HEALTH_PORT=8976,
    )
    server = ChatServer(settings)

    # Pre-popular un mensaje en el historial de Cadiz
    server.history.add("Cadiz", {
        "type": "message",
        "channel": "Cadiz",
        "id": 100,
        "from": {"id": "!001", "short": "C1", "long": "Nodo 1"},
        "text": "Mensaje en historial",
        "reply_id": None,
        "emoji": False,
        "at": "2026-10-04T12:00:00Z",
        "hops": 1,
        "gateways": 1,
    })

    # Iniciar servidor WebSocket en puerto 8975
    ws_server = await serve(
        server.handle_connection,
        "127.0.0.1",
        8975,
    )

    try:
        uri = "ws://127.0.0.1:8975/ws/chat?channels=Cadiz,Madrid"
        async with connect(uri) as client:
            # 1. El servidor debe enviar 'hello'
            msg1_raw = await asyncio.wait_for(client.recv(), timeout=2.0)
            msg1 = json.loads(msg1_raw)
            assert msg1["type"] == "hello"
            assert "Cadiz" in msg1["channels"]
            assert msg1["history_size"] == 20

            # 2. Debe procesar el query param ?channels=Cadiz,Madrid:
            # - Notifica error unknown_channel con Madrid
            # - Confirma subscribed con Cadiz
            # - Envía history con Cadiz
            incoming = []
            for _ in range(3):
                raw = await asyncio.wait_for(client.recv(), timeout=2.0)
                incoming.append(json.loads(raw))

            types = {m["type"] for m in incoming}
            assert "error" in types
            assert "subscribed" in types
            assert "history" in types

            err_msg = next(m for m in incoming if m["type"] == "error")
            assert err_msg["code"] == "unknown_channel"
            assert err_msg["channels"] == ["Madrid"]

            sub_msg = next(m for m in incoming if m["type"] == "subscribed")
            assert sub_msg["channels"] == ["Cadiz"]

            hist_msg = next(m for m in incoming if m["type"] == "history")
            assert hist_msg["channel"] == "Cadiz"
            assert len(hist_msg["items"]) == 1
            assert hist_msg["items"][0]["text"] == "Mensaje en historial"

            # 3. Suscribirse por comando a 'sos'
            await client.send(json.dumps({"type": "subscribe", "channels": ["sos"]}))
            sub_sos_raw = await asyncio.wait_for(client.recv(), timeout=2.0)
            sub_sos = json.loads(sub_sos_raw)
            assert sub_sos["type"] == "subscribed"
            assert sub_sos["channels"] == ["sos"]

            hist_sos_raw = await asyncio.wait_for(client.recv(), timeout=2.0)
            hist_sos = json.loads(hist_sos_raw)
            assert hist_sos["type"] == "history"
            assert hist_sos["channel"] == "sos"
            assert len(hist_sos["items"]) == 0

            # 4. Difundir mensaje en vivo a Cadiz
            await server.hub.broadcast_message("Cadiz", {
                "type": "message",
                "channel": "Cadiz",
                "id": 101,
                "text": "Nuevo mensaje en directo",
            })

            live_msg_raw = await asyncio.wait_for(client.recv(), timeout=2.0)
            live_msg = json.loads(live_msg_raw)
            assert live_msg["type"] == "message"
            assert live_msg["id"] == 101
            assert live_msg["text"] == "Nuevo mensaje en directo"

            # 5. Difundir a Huelva (no suscrito) -> no debe llegar
            await server.hub.broadcast_message("Huelva", {
                "type": "message",
                "channel": "Huelva",
                "id": 999,
                "text": "Mensaje para Huelva",
            })

            # 6. Desuscribirse de sos
            await client.send(json.dumps({"type": "unsubscribe", "channels": ["sos"]}))
            unsub_raw = await asyncio.wait_for(client.recv(), timeout=2.0)
            unsub_msg = json.loads(unsub_raw)
            assert unsub_msg["type"] == "unsubscribed"
            assert unsub_msg["channels"] == ["sos"]

            # 7. Enviar mensaje no válido
            await client.send(json.dumps({"type": "desconocido", "channels": []}))
            err_raw = await asyncio.wait_for(client.recv(), timeout=2.0)
            err_res = json.loads(err_raw)
            assert err_res["type"] == "error"
            assert err_res["code"] == "invalid_message"

    finally:
        ws_server.close()
        await ws_server.wait_closed()
