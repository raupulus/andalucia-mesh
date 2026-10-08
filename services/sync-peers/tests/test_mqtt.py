"""Tests unitarios para el publicador MQTT de sync-peers."""

from __future__ import annotations

from unittest.mock import AsyncMock, MagicMock
import pytest

from src.config import Config
from src.mqtt import MqttPublisher


@pytest.mark.asyncio
async def test_publish_event_chunking_lists() -> None:
    """Verifica que listas grandes se fragmenten en paquetes pequeños para no exceder 16 KB."""
    cfg = Config()
    publisher = MqttPublisher(cfg)
    mock_client = MagicMock()
    mock_client.publish = AsyncMock()
    publisher._client = mock_client
    publisher._connected = True

    # Creamos una lista con 25 elementos
    items = [{"id": f"msg-{i}", "text": "Mensaje de prueba con algo de texto"} for i in range(25)]

    success = await publisher.publish_event("malla-remota", "messages", items)
    assert success is True

    # Debe haber publicado 3 bloques: 10 + 10 + 5
    assert mock_client.publish.call_count == 3


@pytest.mark.asyncio
async def test_publish_event_empty_list() -> None:
    """Verifica que listas vacías no generen publicaciones innecesarias."""
    cfg = Config()
    publisher = MqttPublisher(cfg)
    mock_client = MagicMock()
    mock_client.publish = AsyncMock()
    publisher._client = mock_client
    publisher._connected = True

    success = await publisher.publish_event("malla-remota", "messages", [])
    assert success is True
    assert mock_client.publish.call_count == 0
