"""Pruebas unitarias para el endpoint de comprobación de salud (/health)."""

from unittest.mock import MagicMock

import pytest
from aiohttp.test_utils import TestClient, TestServer

from chat_ws.config import Settings
from chat_ws.health import create_health_app
from chat_ws.history import ChannelHistory
from chat_ws.hub import Hub
from chat_ws.mqtt_consumer import MqttConsumer


@pytest.mark.asyncio
async def test_health_endpoint_healthy() -> None:
    """Verifica que /health devuelva 200 y ok: true cuando MQTT está conectado."""
    settings = Settings()
    history = ChannelHistory()
    hub = Hub(allowed_channels=["Cadiz"], history=history)

    mqtt_mock = MagicMock(spec=MqttConsumer)
    mqtt_mock.is_connected = True
    mqtt_mock.disconnected_seconds = 0.0
    mqtt_mock.last_message_at = "2026-10-04T12:00:00Z"

    app = create_health_app(settings, hub, mqtt_mock)
    client = TestClient(TestServer(app))
    await client.start_server()

    try:
        resp = await client.get("/health")
        assert resp.status == 200
        data = await resp.json()
        assert data["ok"] is True
        assert data["servicio"] == "chat-ws"
        assert data["mqtt"] == "conectado"
        assert data["clientes"] == 0
        assert data["ultimo_mensaje"] == "2026-10-04T12:00:00Z"
    finally:
        await client.close()


@pytest.mark.asyncio
async def test_health_endpoint_grace_period() -> None:
    """Verifica que /health siga devolviendo 200 durante los primeros 60s de desconexión MQTT."""
    settings = Settings()
    hub = Hub(allowed_channels=["Cadiz"], history=ChannelHistory())

    mqtt_mock = MagicMock(spec=MqttConsumer)
    mqtt_mock.is_connected = False
    mqtt_mock.disconnected_seconds = 45.0
    mqtt_mock.last_message_at = None

    app = create_health_app(settings, hub, mqtt_mock)
    client = TestClient(TestServer(app))
    await client.start_server()

    try:
        resp = await client.get("/health")
        assert resp.status == 200
        data = await resp.json()
        assert data["ok"] is True
        assert data["mqtt"] == "desconectado"
    finally:
        await client.close()


@pytest.mark.asyncio
async def test_health_endpoint_unhealthy_after_60s() -> None:
    """Verifica que /health devuelva 503 y ok: false si MQTT lleva más de 60s desconectado."""
    settings = Settings()
    hub = Hub(allowed_channels=["Cadiz"], history=ChannelHistory())

    mqtt_mock = MagicMock(spec=MqttConsumer)
    mqtt_mock.is_connected = False
    mqtt_mock.disconnected_seconds = 65.0
    mqtt_mock.last_message_at = None

    app = create_health_app(settings, hub, mqtt_mock)
    client = TestClient(TestServer(app))
    await client.start_server()

    try:
        resp = await client.get("/health")
        assert resp.status == 503
        data = await resp.json()
        assert data["ok"] is False
        assert data["mqtt"] == "desconectado"
    finally:
        await client.close()
