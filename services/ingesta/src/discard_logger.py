"""Registro estructurado, muestreo y estadísticas de paquetes descartados en snm-ingesta.

Garantiza la trazabilidad de los descartes para diagnósticos continuos (1-2 semanas)
sin provocar saturación de disco ni degradar el rendimiento del bucle de eventos:
- Agrega contadores en memoria por motivo específico de descarte.
- Registra muestras en logs con límite de frecuencia (rate-limiting por motivo).
- Mantiene un búfer circular en memoria con los últimos eventos para inspección inmediata.
- Emite resúmenes periódicos consolidados en el log.
- Proporciona métricas serializables para el endpoint de salud (/health).
"""

from __future__ import annotations

import collections
from dataclasses import asdict, dataclass
from datetime import datetime, timezone
import logging
import time
from typing import Any

logger = logging.getLogger("ingesta.descartes")


@dataclass(frozen=True, slots=True)
class DiscardEvent:
    """Información de un paquete o evento descartado durante la ingesta."""

    at: str
    reason: str
    topic: str
    from_id: str | None
    gateway_id: str | None
    detail: str


class DiscardTracker:
    """Acumulador en memoria y registrador ligero de descartes."""

    def __init__(
        self,
        rate_limit_seconds: float = 30.0,
        ring_buffer_size: int = 50,
    ) -> None:
        """Inicializa el seguidor de descartes.

        Args:
            rate_limit_seconds: Segundos mínimos entre mensajes de log del mismo motivo.
            ring_buffer_size: Capacidad máxima del historial reciente en memoria.
        """
        self.rate_limit_seconds = rate_limit_seconds
        self.ring_buffer: collections.deque[DiscardEvent] = collections.deque(maxlen=ring_buffer_size)
        self.counts: dict[str, int] = collections.defaultdict(int)
        self.total_discards: int = 0
        self._last_log_time: dict[str, float] = {}

        # Estado para cálculos de deltas periódicos
        self._prev_total: int = 0
        self._prev_counts: dict[str, int] = collections.defaultdict(int)

    def record_discard(
        self,
        reason: str,
        topic: str = "",
        from_id: str | None = None,
        gateway_id: str | None = None,
        detail: str = "",
    ) -> None:
        """Registra un descarte con motivo, contexto y limitación de tasa de log.

        Args:
            reason: Motivo estandarizado del descarte.
            topic: Tópico MQTT donde se recibió el paquete.
            from_id: Identificador del nodo emisor si se conoce.
            gateway_id: Identificador del gateway Meshtastic si se conoce.
            detail: Información diagnóstica adicional (ej. claves presentes, tamaño).
        """
        self.total_discards += 1
        self.counts[reason] += 1

        now_utc = datetime.now(timezone.utc).isoformat().replace("+00:00", "Z")
        event = DiscardEvent(
            at=now_utc,
            reason=reason,
            topic=topic,
            from_id=from_id,
            gateway_id=gateway_id,
            detail=detail,
        )
        self.ring_buffer.append(event)

        now_mono = time.monotonic()
        last_logged = self._last_log_time.get(reason, 0.0)
        if now_mono - last_logged >= self.rate_limit_seconds:
            self._last_log_time[reason] = now_mono
            logger.info(
                "[DESCARTE %s] topic=%s from=%s gw=%s detalle=%s (total_%s=%d)",
                reason,
                topic or "-",
                from_id or "-",
                gateway_id or "-",
                detail or "-",
                reason,
                self.counts[reason],
            )

    def log_periodic_summary(self, interval_desc: str = "10m") -> None:
        """Emite una línea consolidada con los descartes transcurridos en el intervalo.

        Args:
            interval_desc: Etiqueta descriptiva del periodo (ej. '10m', '1h').
        """
        diff_total = self.total_discards - self._prev_total
        deltas: dict[str, int] = {}
        for reason, count in self.counts.items():
            delta = count - self._prev_counts.get(reason, 0)
            if delta > 0:
                deltas[reason] = delta

        self._prev_total = self.total_discards
        self._prev_counts = dict(self.counts)

        if diff_total == 0:
            logger.info(
                "[RESUMEN DESCARTES (%s)] 0 descartes en el periodo (total acumulado: %d)",
                interval_desc,
                self.total_discards,
            )
            return

        breakdown = ", ".join(f"{k}: +{v} (total {self.counts[k]})" for k, v in sorted(deltas.items()))
        logger.info(
            "[RESUMEN DESCARTES (%s)] +%d descartes en periodo (total acumulado: %d). Desglose: %s",
            interval_desc,
            diff_total,
            self.total_discards,
            breakdown,
        )

    def get_summary(self) -> dict[str, Any]:
        """Devuelve un diccionario estructurado apto para serialización en /health."""
        return {
            "total_acumulado": self.total_discards,
            "por_motivo": dict(self.counts),
            "ultimos_50": [asdict(e) for e in reversed(self.ring_buffer)],
        }
