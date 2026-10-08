"""Pruebas unitarias para el consumidor MQTT (MqttConsumer)."""

import json
from unittest.mock import AsyncMock

import pytest

from chat_ws.config import Settings
from chat_ws.history import ChannelHistory
from chat_ws.hub import Hub
from chat_ws.mqtt_consumer import MqttConsumer


@pytest.mark.asyncio
async def test_mqtt_consumer_process_valid_message() -> None:
    """Verifica que el consumidor MQTT procese, filtre y difunda mensajes válidos."""
    settings = Settings(ALLOWED_CHANNELS="Cadiz,sos")
    history = ChannelHistory()
    hub = Hub(allowed_channels=["Cadiz", "sos"], history=history)
    hub.broadcast_message = AsyncMock(return_value=1)  # type: ignore[method-assign]

    consumer = MqttConsumer(settings, hub)

    valid_payload = json.dumps({
        "v": 1,
        "packet_id": 9999,
        "from": "!12345678",
        "from_node": {"short": "T1", "long": "Test 1"},
        "to": "^all",
        "portnum": "text",
        "channel": "Cadiz",
        "rx_first": "2026-10-04T12:00:00Z",
        "payload": {"text": "Mensaje de prueba"},
    })

    await consumer._process_message(valid_payload.encode("utf-8"))

    assert consumer.last_message_at is not None
    hub.broadcast_message.assert_awaited_once()
    call_args = hub.broadcast_message.call_args[0]
    assert call_args[0] == "Cadiz"
    assert call_args[1]["text"] == "Mensaje de prueba"


@pytest.mark.asyncio
async def test_mqtt_consumer_discard_invalid() -> None:
    """Verifica que mensajes privados o malformados se descarten sin difusión."""
    settings = Settings(ALLOWED_CHANNELS="Cadiz,sos")
    hub = Hub(allowed_channels=["Cadiz", "sos"], history=ChannelHistory())
    hub.broadcast_message = AsyncMock()  # type: ignore[method-assign]

    consumer = MqttConsumer(settings, hub)

    # 1. Mensaje privado a un nodo específico (to != ^all)
    direct_msg = json.dumps({
        "v": 1,
        "packet_id": 1111,
        "from": "!12345678",
        "to": "!87654321",
        "portnum": "text",
        "channel": "Cadiz",
        "payload": {"text": "Secreto"},
    })
    await consumer._process_message(direct_msg)
    hub.broadcast_message.assert_not_awaited()

    # 2. JSON inválido
    await consumer._process_message(b"no-json-payload")
    hub.broadcast_message.assert_not_awaited()
