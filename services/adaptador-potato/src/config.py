"""Configuración del microservicio adaptador-potato.

Carga y valida las variables de entorno necesarias para la conexión MQTT,
el descifrado de paquetes de radio y la comunicación con la API de PotatoMesh.
"""

from __future__ import annotations

import os
import unicodedata
from dataclasses import dataclass, field


def _strip_accents(text: str) -> str:
    """Elimina tildes y diacríticos normalizando a forma NFKD."""
    return "".join(
        c for c in unicodedata.normalize("NFKD", text) if not unicodedata.combining(c)
    ).lower()


@dataclass(frozen=True)
class Config:
    """Parámetros de configuración del servicio."""

    # Conexión MQTT interno
    mqtt_host: str = field(default_factory=lambda: os.getenv("MQTT_HOST", "172.30.0.1"))
    mqtt_port: int = field(default_factory=lambda: int(os.getenv("MQTT_PORT", "1884")))
    mqtt_user: str = field(default_factory=lambda: os.getenv("MQTT_USER", "svc-potato"))
    mqtt_password: str = field(default_factory=lambda: os.getenv("MQTT_PASSWORD", ""))
    mqtt_topic_root: str = field(
        default_factory=lambda: os.getenv("MQTT_TOPIC_ROOT", "msh/EU_868")
    )

    # Canales permitidos
    allowed_channels_raw: str = field(
        default_factory=lambda: os.getenv(
            "ALLOWED_CHANNELS",
            "SFNarrow,Iberia,Andalucia,Cadiz,Huelva,Almeria,Granada,Jaen,Sevilla,Cordoba,Malaga,Ceuta,Melilla,sos",
        )
    )
    primary_channel: str = field(
        default_factory=lambda: os.getenv("PRIMARY_CHANNEL", "SFNarrow")
    )
    channel_key_default: str = field(
        default_factory=lambda: os.getenv("CHANNEL_KEY_DEFAULT", "AQ==")
    )

    # Conexión REST a PotatoMesh
    potatomesh_url: str = field(
        default_factory=lambda: os.getenv("POTATOMESH_URL", "http://potatomesh:41447")
    )
    potatomesh_api_token: str = field(
        default_factory=lambda: os.getenv("POTATOMESH_API_TOKEN", "")
    )

    # Parámetros operativos
    cola_max: int = field(default_factory=lambda: int(os.getenv("COLA_MAX", "10000")))
    lote_max: int = field(default_factory=lambda: int(os.getenv("LOTE_MAX", "100")))
    lote_segundos: float = field(
        default_factory=lambda: float(os.getenv("LOTE_SEGUNDOS", "1.0"))
    )
    dedup_minutos: int = field(
        default_factory=lambda: int(os.getenv("DEDUP_MINUTOS", "15"))
    )
    log_level: str = field(default_factory=lambda: os.getenv("LOG_LEVEL", "INFO"))
    health_port: int = field(
        default_factory=lambda: int(os.getenv("HEALTH_PORT", "8080"))
    )

    # Campos computados de canales
    allowed_channels: list[str] = field(init=False)
    channel_indices: dict[str, int] = field(init=False)
    canonical_to_name: dict[str, str] = field(init=False)

    def __post_init__(self) -> None:
        """Inicializa las estructuras computadas de mapeo de canales."""
        channels = [
            c.strip() for c in self.allowed_channels_raw.split(",") if c.strip()
        ]
        object.__setattr__(self, "allowed_channels", channels)

        # Mapeo de nombre canónico (sin tildes, minúsculas) al nombre oficial
        canon_map = {_strip_accents(c): c for c in channels}
        object.__setattr__(self, "canonical_to_name", canon_map)

        # Índices estables: PRIMARY_CHANNEL es siempre 0; el resto según su posición
        indices: dict[str, int] = {}
        primary_canon = _strip_accents(self.primary_channel)
        indices[primary_canon] = 0

        current_idx = 1
        for ch in channels:
            c_canon = _strip_accents(ch)
            if c_canon != primary_canon and c_canon not in indices:
                indices[c_canon] = current_idx
                current_idx += 1

        object.__setattr__(self, "channel_indices", indices)

    def get_channel_index_and_name(self, channel_topic: str) -> tuple[int, str] | None:
        """Obtiene el índice numérico y el nombre canónico oficial de un canal.

        Args:
            channel_topic: Nombre del canal recibido en el topic MQTT.

        Returns:
            Tupla (índice, nombre_oficial) o None si el canal no está permitido.
        """
        canon = _strip_accents(channel_topic)
        if canon not in self.canonical_to_name:
            return None
        return self.channel_indices.get(canon, 0), self.canonical_to_name[canon]
