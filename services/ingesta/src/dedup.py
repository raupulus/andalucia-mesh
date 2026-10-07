"""Deduplicación LRU y agrupación temporal de recepciones para snm-ingesta.

Garantiza que un mismo paquete transmitido por radio y capturado por múltiples
gateways (o recibido de mallas federadas vía sync-peers) se trate como una
sola entidad:
1. La primera recepción abre una ventana de acumulación de 2 segundos.
2. Recepciones concurrentes en esa ventana se agrupan en el paquete.
3. Al expirar la ventana (2s), se notifica para emisión al flujo 'decoded' y base de datos.
4. Recepciones tardías (> 2s y <= 15 min) actualizan las métricas de recepciones
   y saltos en la base de datos sin republicar en el flujo 'decoded'.
5. Los duplicados del mismo gateway para el mismo paquete se descartan.
"""

from __future__ import annotations

import asyncio
from dataclasses import dataclass, field
from datetime import datetime, timezone
from typing import Any, Callable, Coroutine


@dataclass
class ReceptionItem:
    """Información de una recepción individual de un paquete por un gateway."""

    gateway_id: str
    snr: float | None
    rssi: int | None
    hop_limit: int | None
    hops: int | None
    relay_node: int | None
    gw_rx_time: datetime | None
    at: datetime
    own: bool
    direct: bool
    distance_km: float | None

    def to_decoded_reception_dict(self) -> dict[str, Any]:
        """Formatea la recepción para la lista 'receptions' del flujo 'decoded'."""
        return {
            "gateway": self.gateway_id,
            "snr": self.snr,
            "rssi": self.rssi,
            "hops": self.hops,
            "at": self.at.isoformat().replace("+00:00", "Z"),
        }


@dataclass
class UnifiedPacket:
    """Paquete unificado consolidado a través de todos los gateways."""

    rx_first: datetime
    from_id: str
    to_id: str
    packet_id: int
    channel: str | None
    portnum: str | None
    portnum_num: int | None
    variant: str | None
    decrypt_status: str
    hop_start: int | None
    hops_min: int | None
    want_ack: bool
    via_mqtt: bool
    ok_to_mqtt: bool | None
    size_bytes: int
    airtime_ms: float
    first_gateway: str
    province: str | None
    payload: dict[str, Any]
    receptions: list[ReceptionItem] = field(default_factory=list)
    state: str = "WINDOW_OPEN"  # 'WINDOW_OPEN', 'PUBLISHED', 'DROPPED'
    _gateways_seen: set[str] = field(default_factory=set)


class PacketDeduplicator:
    """Deduplicador en memoria con ventana de publicación y retención LRU."""

    def __init__(
        self,
        on_window_closed: Callable[[UnifiedPacket], Coroutine[Any, Any, None]] | None = None,
        on_late_reception: Callable[[UnifiedPacket, ReceptionItem], Coroutine[Any, Any, None]] | None = None,
        publish_window_s: float = 2.0,
        dedup_window_min: int = 15,
    ) -> None:
        """Inicializa el deduplicador.

        Args:
            on_window_closed: Callback ejecutado cuando se cierra la ventana de 2s.
            on_late_reception: Callback ejecutado cuando entra una recepción tardía (> 2s).
            publish_window_s: Duración en segundos de la ventana de acumulación (2s).
            dedup_window_min: Ventana de deduplicación en memoria en minutos (15 min).
        """
        self.on_window_closed = on_window_closed
        self.on_late_reception = on_late_reception
        self.publish_window_s = publish_window_s
        self.dedup_window_s = dedup_window_min * 60.0

        # Caché indexada por clave (from_id, packet_id)
        self._cache: dict[tuple[str, int], UnifiedPacket] = {}
        self._timer_tasks: dict[tuple[str, int], asyncio.Task[None]] = {}

    async def _window_timer(self, key: tuple[str, int]) -> None:
        """Temporizador asíncrono para cerrar la ventana de acumulación."""
        try:
            await asyncio.sleep(self.publish_window_s)
            packet = self._cache.get(key)
            if packet and packet.state == "WINDOW_OPEN":
                packet.state = "PUBLISHED"
                if self.on_window_closed:
                    await self.on_window_closed(packet)
        except asyncio.CancelledError:
            pass
        finally:
            self._timer_tasks.pop(key, None)

    async def process_reception(
        self,
        packet_candidate: UnifiedPacket,
        reception: ReceptionItem,
    ) -> str:
        """Procesa una recepción y determina si es paquete nuevo, duplicado o tardío.

        Returns:
            Uno de: 'NEW_PACKET', 'RECEPTION_WINDOW', 'RECEPTION_LATE', 'DUPLICATE_GATEWAY'.
        """
        key = (packet_candidate.from_id, packet_candidate.packet_id)
        now = reception.at

        # 1. Paquete existente en caché
        existing = self._cache.get(key)
        if existing is not None:
            # Si el gateway ya había escuchado este paquete, es duplicado espurio
            if reception.gateway_id in existing._gateways_seen:
                return "DUPLICATE_GATEWAY"

            existing._gateways_seen.add(reception.gateway_id)
            existing.receptions.append(reception)

            # Actualizar saltos mínimos
            if reception.hops is not None:
                if existing.hops_min is None or reception.hops < existing.hops_min:
                    existing.hops_min = reception.hops

            # Si la ventana sigue abierta (< 2s)
            if existing.state == "WINDOW_OPEN":
                return "RECEPTION_WINDOW"

            # Si ya se publicó (> 2s pero <= 15 min), es una recepción tardía
            elif existing.state == "PUBLISHED":
                if self.on_late_reception:
                    await self.on_late_reception(existing, reception)
                return "RECEPTION_LATE"

            return "DUPLICATE_GATEWAY"

        # 2. Paquete completamente nuevo
        packet_candidate.state = "WINDOW_OPEN"
        packet_candidate._gateways_seen.add(reception.gateway_id)
        packet_candidate.receptions.append(reception)
        self._cache[key] = packet_candidate

        # Iniciar temporizador de cierre de ventana (2 segundos)
        task = asyncio.create_task(self._window_timer(key))
        self._timer_tasks[key] = task

        return "NEW_PACKET"

    def purge_expired(self, current_time: datetime) -> int:
        """Elimina de la memoria los paquetes cuya primera recepción supere la ventana de 15 min."""
        now_ts = current_time.timestamp()
        expired_keys = [
            key
            for key, pkt in self._cache.items()
            if (now_ts - pkt.rx_first.timestamp()) > self.dedup_window_s
        ]
        for key in expired_keys:
            timer = self._timer_tasks.pop(key, None)
            if timer and not timer.done():
                timer.cancel()
            self._cache.pop(key, None)

        return len(expired_keys)

    async def flush_all_open_windows(self) -> None:
        """Cierra inmediatamente todas las ventanas abiertas (usado durante apagado limpio)."""
        open_keys = [k for k, p in self._cache.items() if p.state == "WINDOW_OPEN"]
        for key in open_keys:
            timer = self._timer_tasks.pop(key, None)
            if timer and not timer.done():
                timer.cancel()

            packet = self._cache.get(key)
            if packet and packet.state == "WINDOW_OPEN":
                packet.state = "PUBLISHED"
                if self.on_window_closed:
                    await self.on_window_closed(packet)
