"""Pruebas unitarias para el gestor Hub y ClientSession."""

import asyncio
from unittest.mock import AsyncMock, MagicMock

import pytest
from websockets.asyncio.server import ServerConnection

from chat_ws.history import ChannelHistory
from chat_ws.hub import ClientSession, Hub


def create_mock_ws() -> ServerConnection:
    """Crea un mock de ServerConnection."""
    ws = MagicMock(spec=ServerConnection)
    ws.send = AsyncMock()
    ws.close = AsyncMock()
    return ws


@pytest.mark.asyncio
async def test_hub_registration_and_limits() -> None:
    """Verifica el registro y los límites por IP y globales."""
    history = ChannelHistory()
    hub = Hub(
        allowed_channels=["Cadiz", "sos"],
        history=history,
        max_connections=3,
        max_per_ip=2,
    )

    ws1 = create_mock_ws()
    ws2 = create_mock_ws()
    ws3 = create_mock_ws()
    ws4 = create_mock_ws()

    # 1. Registro normal
    s1, err = hub.register(ws1, "192.168.1.10")
    assert s1 is not None
    assert err is None

    # 2. Segunda conexión desde la misma IP (permitida)
    s2, err = hub.register(ws2, "192.168.1.10")
    assert s2 is not None
    assert err is None

    # 3. Tercera conexión desde la misma IP (supera max_per_ip=2)
    s3, err = hub.register(ws3, "192.168.1.10")
    assert s3 is None
    assert err == "too_many_connections"

    # 4. Conexión desde IP distinta (llega a max_connections=3)
    s4, err = hub.register(ws4, "192.168.1.20")
    assert s4 is not None
    assert err is None

    # 5. Supera max_connections total
    ws5 = create_mock_ws()
    s5, err = hub.register(ws5, "192.168.1.30")
    assert s5 is None
    assert err == "too_many_connections"

    # 6. Desregistro
    assert hub.active_connections_count == 3
    await hub.unregister(s1)
    assert hub.active_connections_count == 2

    # Ahora sí cabe una nueva
    s6, err = hub.register(ws5, "192.168.1.30")
    assert s6 is not None
    assert err is None

    await hub.unregister(s2)
    await hub.unregister(s4)
    await hub.unregister(s6)


@pytest.mark.asyncio
async def test_hub_subscriptions_and_broadcast() -> None:
    """Verifica suscripciones a canales válidos/desconocidos y difusión."""
    history = ChannelHistory()
    hub = Hub(
        allowed_channels=["Cadiz", "Sevilla"],
        history=history,
    )

    ws1 = create_mock_ws()
    ws2 = create_mock_ws()
    s1, _ = hub.register(ws1, "10.0.0.1")
    s2, _ = hub.register(ws2, "10.0.0.2")
    assert s1 is not None and s2 is not None

    # Suscripción
    valid, unknown = hub.subscribe(s1, ["Cadiz", "Madrid"])
    assert valid == ["Cadiz"]
    assert unknown == ["Madrid"]

    valid2, _ = hub.subscribe(s2, ["Sevilla"])
    assert valid2 == ["Sevilla"]

    # Difusión a Cadiz
    msg_cadiz = {"type": "message", "channel": "Cadiz", "id": 1, "text": "Hola Cadiz"}
    delivered = await hub.broadcast_message("Cadiz", msg_cadiz)
    assert delivered == 1

    # Esperar un tick para que el writer procese la cola
    await asyncio.sleep(0.05)
    assert isinstance(ws1.send, AsyncMock)
    assert isinstance(ws2.send, AsyncMock)
    ws1.send.assert_awaited()
    ws2.send.assert_not_awaited()

    # Historial actualizado
    hist = history.get("Cadiz")
    assert len(hist) == 1
    assert hist[0]["id"] == 1

    # Desuscripción
    unsub = hub.unsubscribe(s1, ["Cadiz"])
    assert unsub == ["Cadiz"]
    assert len(s1.subscribed_channels) == 0

    await hub.unregister(s1)
    await hub.unregister(s2)


@pytest.mark.asyncio
async def test_slow_client_disconnection() -> None:
    """Verifica que un cliente cuya cola de salida se sature sea desconectado con código 1013."""
    history = ChannelHistory()
    hub = Hub(
        allowed_channels=["sos"],
        history=history,
        queue_size=2,  # Cola muy pequeña para provocar saturación
    )

    ws_slow = create_mock_ws()
    session, _ = hub.register(ws_slow, "10.0.0.1")
    assert session is not None

    hub.subscribe(session, ["sos"])

    # Llenamos la cola de la sesión sin dejar tiempo al writer
    session.send_queue.put_nowait("msg1")
    session.send_queue.put_nowait("msg2")
    assert session.send_queue.full()

    # Al intentar difundir, debe detectarse como cliente lento y desregistrarse
    delivered = await hub.broadcast_message("sos", {"type": "message", "channel": "sos", "id": 99})
    assert delivered == 0
    assert hub.active_connections_count == 0


def test_client_rate_limiting_and_invalid_threshold() -> None:
    """Verifica el control de ritmo y el umbral de mensajes inválidos."""
    ws = create_mock_ws()
    session = ClientSession(
        ws=ws,
        ip="10.0.0.1",
        rate_limit_per_sec=2,
        rate_limit_max_violation_s=0.1,
        max_invalid_per_min=3,
    )

    # Mensajes normales dentro del ritmo
    is_limited, close_now = session.record_incoming_message()
    assert not is_limited and not close_now
    is_limited, close_now = session.record_incoming_message()
    assert not is_limited and not close_now

    # 3er mensaje en el mismo segundo -> rate limited
    is_limited, close_now = session.record_incoming_message()
    assert is_limited
    assert not close_now

    # Mensajes inválidos
    assert not session.record_invalid_message()
    assert not session.record_invalid_message()
    # 3er mensaje inválido alcanza el umbral
    assert session.record_invalid_message()
