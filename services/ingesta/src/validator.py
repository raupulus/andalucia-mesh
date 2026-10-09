"""Validación de tópicos MQTT, gateways y lista blanca de canales para snm-ingesta.

Asegura que los paquetes procedan de tópicos conformes a la especificación,
que los canales pertenezcan a la lista blanca autorizada y que los identificadores
de gateways cumplan el formato canónico (!hex de 8 caracteres).
"""

from __future__ import annotations

import re
import unicodedata
from dataclasses import dataclass
from typing import Final

# Expresión regular para gateways válidos: signo de admiración y 8 caracteres hexadecimales
GATEWAY_ID_REGEX: Final[re.Pattern[str]] = re.compile(r"^![0-9a-f]{8}$")


def normalize_channel_name(channel: str) -> str:
    """Normaliza un nombre de canal a minúsculas, sin acentos ni marcas diacríticas.

    Ejemplos:
        'Cádiz' -> 'cadiz'
        'Jaén'  -> 'jaen'
        'SFNarrow' -> 'sfnarrow'
    """
    nfkd = unicodedata.normalize("NFKD", channel.strip().lower())
    return "".join(c for c in nfkd if not unicodedata.combining(c))


def validate_gateway_id(gateway_id: str) -> str | None:
    """Valida y normaliza el identificador de un gateway Meshtastic.

    Args:
        gateway_id: Cadena con el id del gateway (ej. '!1a2b3c4d').

    Returns:
        Id normalizado en minúsculas si cumple el patrón, o None si es inválido.
    """
    clean_id = gateway_id.strip().lower()
    if GATEWAY_ID_REGEX.match(clean_id):
        return clean_id
    return None


@dataclass(frozen=True, slots=True)
class TopicParseResult:
    """Resultado del análisis estructurado de un topic MQTT."""

    topic_type: str  # 'envelope', 'map', 'peer', 'unknown'
    channel: str | None = None  # Canal canónico de la lista blanca
    gateway_id: str | None = None  # Id del gateway extraído del topic (en 'envelope')
    peer_id: str | None = None  # Id de la instancia peer (en 'peer')
    event_type: str | None = None  # Tipo de evento peer (en 'peer')
    error_reason: str | None = None  # Motivo del descarte si topic_type == 'unknown'


class TopicValidator:
    """Validador de tópicos MQTT y lista blanca de canales."""

    def __init__(
        self,
        allowed_channels: list[str],
        topic_root: str = "msh/EU_868",
        topic_prefix: str = "snm",
    ) -> None:
        """Inicializa el validador con la lista de canales admitidos.

        Args:
            allowed_channels: Lista de nombres de canal permitidos con su grafía oficial.
            topic_root: Raíz de tópicos de radio Meshtastic ('msh/EU_868').
            topic_prefix: Prefijo de tópicos internos del sistema ('snm').
        """
        self.topic_root = topic_root.rstrip("/")
        self.topic_prefix = topic_prefix.rstrip("/")

        # Mapa de canal normalizado (sin acentos, minúsculas) -> grafía canónica oficial
        self._canonical_channels: dict[str, str] = {}
        for ch in allowed_channels:
            norm = normalize_channel_name(ch)
            self._canonical_channels[norm] = ch

    def match_channel(self, raw_channel: str) -> str | None:
        """Coteja un nombre de canal contra la lista blanca.

        Args:
            raw_channel: Nombre del canal tal como aparece en el topic o sobre.

        Returns:
            Grafía oficial del canal si está en la lista blanca; None si no está permitido.
        """
        norm = normalize_channel_name(raw_channel)
        return self._canonical_channels.get(norm)

    def parse_topic(self, topic: str, payload_len: int) -> TopicParseResult:
        """Analiza un topic MQTT y extrae sus metadatos estructurados.

        Args:
            topic: Cadena completa del topic MQTT recibido.
            payload_len: Longitud en bytes de la carga recibida.

        Returns:
            Objeto TopicParseResult con la clasificación y campos extraídos.
        """
        # 1. Tráfico federado de peers: snm/v1/peer/<peer_id>/<type> (hasta 16 KB)
        peer_prefix = f"{self.topic_prefix}/v1/peer/"
        if topic.startswith(peer_prefix):
            if payload_len > 16384:
                return TopicParseResult(topic_type="unknown", error_reason="tamano_excedido")
            remainder = topic[len(peer_prefix) :]
            parts = remainder.split("/")
            if len(parts) == 2:
                p_id, ev_type = parts
                return TopicParseResult(
                    topic_type="peer",
                    peer_id=p_id,
                    event_type=ev_type,
                )
            return TopicParseResult(topic_type="unknown", error_reason="peer_topic_invalido")

        # Descarte preventivo de paquetes de radio LoRa excesivamente grandes (> 1024 B)
        if payload_len > 1024:
            return TopicParseResult(topic_type="unknown", error_reason="tamano_excedido")

        # 2. Tráfico estándar de radio: msh/EU_868/2/e/<canal>/<!gateway>
        envelope_prefix = f"{self.topic_root}/2/e/"
        if topic.startswith(envelope_prefix):
            remainder = topic[len(envelope_prefix) :]
            parts = remainder.split("/")
            if len(parts) == 2:
                raw_channel, raw_gw = parts
                canonical_channel = self.match_channel(raw_channel)
                gw_id = validate_gateway_id(raw_gw)
                if canonical_channel and gw_id:
                    return TopicParseResult(
                        topic_type="envelope",
                        channel=canonical_channel,
                        gateway_id=gw_id,
                    )
                if not canonical_channel:
                    return TopicParseResult(topic_type="unknown", error_reason="canal_no_permitido")
                if not gw_id:
                    return TopicParseResult(topic_type="unknown", error_reason="gateway_invalido")
            return TopicParseResult(topic_type="unknown", error_reason="envelope_partes_invalidas")

        # 3. Map reports: msh/EU_868/2/map/
        map_prefix = f"{self.topic_root}/2/map/"
        if topic.startswith(map_prefix) or topic == f"{self.topic_root}/2/map":
            remainder = topic[len(map_prefix) :] if topic.startswith(map_prefix) else ""
            parts = [p for p in remainder.split("/") if p]
            gw_id = None
            for p in reversed(parts):
                cand = validate_gateway_id(p)
                if cand:
                    gw_id = cand
                    break
            return TopicParseResult(topic_type="map", gateway_id=gw_id)

        return TopicParseResult(topic_type="unknown", error_reason="topic_desconocido")
