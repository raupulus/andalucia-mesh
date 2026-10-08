"""Pruebas unitarias para el gestor de historial en memoria ChannelHistory."""

import time

from chat_ws.history import ChannelHistory


def test_channel_history_ring_buffer() -> None:
    """Verifica que el historial actúe como búfer circular acotado a max_size."""
    history = ChannelHistory(max_size=3)

    # Añadir 5 mensajes en el canal Cadiz
    for i in range(1, 6):
        history.add("Cadiz", {"id": i, "text": f"Mensaje {i}"})

    items = history.get("Cadiz")
    assert len(items) == 3
    # Los más antiguos se descartan y se conservan los 3 más recientes ordenados
    assert items[0]["id"] == 3
    assert items[1]["id"] == 4
    assert items[2]["id"] == 5


def test_channel_history_expiration() -> None:
    """Verifica que los mensajes que superen max_age_seconds sean purgados."""
    history = ChannelHistory(max_size=10, max_age_seconds=10.0)
    now = time.time()

    # Mensaje antiguo (hace 20 segundos)
    history.add("sos", {"id": 1, "text": "Viejo"}, timestamp=now - 20.0)
    # Mensaje reciente (hace 2 segundos)
    history.add("sos", {"id": 2, "text": "Nuevo"}, timestamp=now - 2.0)

    items = history.get("sos")
    assert len(items) == 1
    assert items[0]["id"] == 2


def test_channel_history_multiple_channels() -> None:
    """Verifica el aislamiento entre canales distintos."""
    history = ChannelHistory(max_size=5)
    history.add("Cadiz", {"id": 100})
    history.add("Sevilla", {"id": 200})

    assert len(history.get("Cadiz")) == 1
    assert history.get("Cadiz")[0]["id"] == 100

    assert len(history.get("Sevilla")) == 1
    assert history.get("Sevilla")[0]["id"] == 200

    assert len(history.get("Huelva")) == 0
