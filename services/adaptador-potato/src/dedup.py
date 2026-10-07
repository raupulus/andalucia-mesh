"""Módulo de deduplicación en memoria para paquetes Meshtastic.

Implementa una caché LRU acotada indexada por (from, packet_id) con ventana
temporal de caducidad para ignorar paquetes idénticos recibidos por múltiples gateways.
"""

from __future__ import annotations

import time
from collections import OrderedDict


class Deduplicator:
    """Filtro de deduplicación LRU en memoria."""

    def __init__(self, window_seconds: float = 900.0, capacity: int = 200_000) -> None:
        """Inicializa el deduplicador.

        Args:
            window_seconds: Ventana de validez en segundos (por defecto 15 min = 900s).
            capacity: Número máximo de claves en memoria.
        """
        self.window_seconds = window_seconds
        self.capacity = capacity
        self._cache: OrderedDict[tuple[int, int], float] = OrderedDict()
        self.seen_count: int = 0
        self.duplicate_count: int = 0
        self._last_cleanup: float = time.monotonic()

    def is_duplicate(
        self, from_node: int, packet_id: int, current_time: float | None = None
    ) -> bool:
        """Comprueba si un paquete ya ha sido procesado recientemente.

        Args:
            from_node: ID numérico del nodo emisor.
            packet_id: Identificador numérico del paquete.
            current_time: Marca de tiempo en segundos (opcional, para tests).

        Returns:
            True si el paquete es un duplicado dentro de la ventana temporal;
            False si es un paquete nuevo.
        """
        now = time.monotonic() if current_time is None else current_time
        key = (from_node, packet_id)

        # Limpieza periódica cada 60 segundos
        if now - self._last_cleanup > 60.0:
            self.cleanup(now)
            self._last_cleanup = now

        if key in self._cache:
            first_seen = self._cache[key]
            if (now - first_seen) <= self.window_seconds:
                self.duplicate_count += 1
                return True
            # Si caducó, se actualiza el tiempo y se considera nuevo
            self._cache.move_to_end(key)
            self._cache[key] = now
            self.seen_count += 1
            return False

        # Si supera la capacidad, expulsar el elemento más antiguo
        if len(self._cache) >= self.capacity:
            self._cache.popitem(last=False)

        self._cache[key] = now
        self.seen_count += 1
        return False

    def cleanup(self, current_time: float | None = None) -> int:
        """Elimina de la caché todas las entradas cuya ventana haya expirado.

        Args:
            current_time: Marca de tiempo actual en segundos.

        Returns:
            Número de entradas purgadas.
        """
        now = time.monotonic() if current_time is None else current_time
        threshold = now - self.window_seconds
        purged = 0

        # Al estar ordenadas por inserción, podemos iterar desde el principio
        for key, seen_at in list(self._cache.items()):
            if seen_at < threshold:
                del self._cache[key]
                purged += 1
            else:
                # Las siguientes son más recientes
                break

        return purged

    def __len__(self) -> int:
        """Devuelve el tamaño actual de la caché."""
        return len(self._cache)
