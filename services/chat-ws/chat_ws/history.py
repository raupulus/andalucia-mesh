"""Búfer circular de historial de mensajes en memoria por canal."""

import time
from collections import deque
from typing import Any


class ChannelHistory:
    """Gestor de historial de mensajes en memoria para canales de chat.

    Mantiene un búfer circular (deque) por canal de longitud máxima acotada
    y purga mensajes con antigüedad superior a 24 horas.
    """

    def __init__(self, max_size: int = 20, max_age_seconds: float = 86400.0) -> None:
        """Inicializa el almacén de historial.

        Args:
            max_size: Capacidad máxima de mensajes almacenados por canal.
            max_age_seconds: Tiempo máximo de retención en segundos (24h por defecto).
        """
        self.max_size = max_size
        self.max_age_seconds = max_age_seconds
        # Mapeo de nombre de canal a deque de tuplas (timestamp_epoch, mensaje_dict)
        self._buffers: dict[str, deque[tuple[float, dict[str, Any]]]] = {}

    def add(
        self,
        channel: str,
        message: dict[str, Any],
        timestamp: float | None = None,
    ) -> None:
        """Añade un mensaje al historial del canal correspondiente.

        Args:
            channel: Nombre del canal.
            message: Diccionario del mensaje ya transformado para clientes WebSocket.
            timestamp: Marca temporal en segundos (time.time() si es None).
        """
        if timestamp is None:
            timestamp = time.time()

        if channel not in self._buffers:
            self._buffers[channel] = deque(maxlen=self.max_size)

        self._buffers[channel].append((timestamp, message))

    def get(self, channel: str) -> list[dict[str, Any]]:
        """Obtiene los mensajes vigentes del canal del más antiguo al más reciente.

        Purga automáticamente los mensajes que hayan superado max_age_seconds.

        Args:
            channel: Nombre del canal consultado.

        Returns:
            Lista de mensajes de chat (hasta max_size elementos).
        """
        buffer = self._buffers.get(channel)
        if not buffer:
            return []

        now = time.time()
        cutoff = now - self.max_age_seconds

        # Purgar elementos expirados desde la cabeza (los más antiguos)
        while buffer and buffer[0][0] < cutoff:
            buffer.popleft()

        return [msg for _, msg in buffer]

    def clear(self) -> None:
        """Limpia todo el historial de todos los canales."""
        self._buffers.clear()
