"""Configuración del microservicio sync-peers.

Carga y valida las variables de entorno requeridas para la base de datos relacional,
el broker Mosquitto, la API local de PotatoMesh y el sondeo de mallas vecinas.
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
    """Parámetros de configuración para sync-peers."""

    # Identidad y dominios
    project_name: str = field(
        default_factory=lambda: os.getenv("PROJECT_NAME", "Andalucía Mesh")
    )
    project_domain: str = field(
        default_factory=lambda: os.getenv("PROJECT_DOMAIN", "mesh.example.org")
    )
    project_contact: str = field(
        default_factory=lambda: os.getenv("PROJECT_CONTACT", "public@raupulus.dev")
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

    # Conexión MQTT
    mqtt_host: str = field(default_factory=lambda: os.getenv("MQTT_HOST", "172.30.0.1"))
    mqtt_port: int = field(default_factory=lambda: int(os.getenv("MQTT_PORT", "1884")))
    mqtt_user: str = field(default_factory=lambda: os.getenv("MQTT_USER", "svc-potato"))
    mqtt_password: str = field(default_factory=lambda: os.getenv("MQTT_PASSWORD", ""))

    # Conexión PostgreSQL (peersync)
    db_host: str = field(default_factory=lambda: os.getenv("DB_HOST", "172.30.0.1"))
    db_port: int = field(default_factory=lambda: int(os.getenv("DB_PORT", "5432")))
    db_name: str = field(default_factory=lambda: os.getenv("DB_NAME", "peersync"))
    db_user: str = field(default_factory=lambda: os.getenv("DB_USER", "peersync"))
    db_password: str = field(default_factory=lambda: os.getenv("DB_PASSWORD", ""))

    # API local PotatoMesh
    potatomesh_url: str = field(
        default_factory=lambda: os.getenv("POTATOMESH_URL", "http://potatomesh:41447")
    )
    potatomesh_api_token: str = field(
        default_factory=lambda: os.getenv("POTATOMESH_API_TOKEN", "")
    )

    # Fichero peers y temporizadores
    peers_archivo: str = field(
        default_factory=lambda: os.getenv("PEERS_ARCHIVO", "/app/peers.json")
    )
    ciclo_mensajes_s: int = field(
        default_factory=lambda: int(os.getenv("CICLO_MENSAJES_S", "60"))
    )
    paginas_max: int = field(
        default_factory=lambda: int(os.getenv("PAGINAS_MAX", "20"))
    )
    log_level: str = field(default_factory=lambda: os.getenv("LOG_LEVEL", "INFO"))
    health_port: int = field(
        default_factory=lambda: int(os.getenv("HEALTH_PORT", "8080"))
    )

    # Campos computados
    allowed_channels: list[str] = field(init=False)
    channel_indices: dict[str, int] = field(init=False)
    canonical_to_name: dict[str, str] = field(init=False)
    user_agent: str = field(init=False)

    def __post_init__(self) -> None:
        """Inicializa los mapeos de canales y la cabecera de cortesía User-Agent."""
        channels = [
            c.strip() for c in self.allowed_channels_raw.split(",") if c.strip()
        ]
        object.__setattr__(self, "allowed_channels", channels)

        canon_map = {_strip_accents(c): c for c in channels}
        object.__setattr__(self, "canonical_to_name", canon_map)

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

        # User-Agent canónico según la especificación
        ua = f"{self.project_name}-PeerSync/1.0 (+https://{self.project_domain}; {self.project_contact})"
        object.__setattr__(self, "user_agent", ua)

    def get_channel_index_and_name(self, channel_topic: str) -> tuple[int, str] | None:
        """Obtiene el índice y nombre oficial de un canal o None si no está permitido."""
        canon = _strip_accents(channel_topic)
        if canon not in self.canonical_to_name:
            return None
        return self.channel_indices.get(canon, 0), self.canonical_to_name[canon]
