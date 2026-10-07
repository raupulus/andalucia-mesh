"""Configuración y validación de variables de entorno para snm-ingesta.

Combina variables comunes (/srv/comun/.env) y específicas de la pieza
(/srv/ingesta/.env), realizando validaciones estrictas al arrancar.
"""

from __future__ import annotations

import os
import sys
from typing import Final
from pydantic import Field, field_validator
from pydantic_settings import BaseSettings, SettingsConfigDict


class IngestaConfig(BaseSettings):
    """Configuración principal del microservicio de ingesta."""

    model_config = SettingsConfigDict(
        env_file=(".env", "/srv/comun/.env"),
        env_file_encoding="utf-8",
        extra="ignore",
    )

    # Identidad y zona horaria
    PROJECT_NAME: str = "Andalucía Mesh"
    TZ: str = "Europe/Madrid"

    # Parámetros de canales y radio
    ALLOWED_CHANNELS: str = (
        "SFNarrow,Iberia,Andalucia,Cadiz,Huelva,Almeria,Granada,Jaen,Sevilla,Cordoba,Malaga,Ceuta,Melilla,sos"
    )
    PRIMARY_CHANNEL: str = "SFNarrow"
    CHANNEL_KEY_DEFAULT: str = "AQ=="
    INFRA_ROLES: str = "ROUTER,ROUTER_LATE,REPEATER"

    LORA_BANDWIDTH: float = 62.5
    LORA_SPREAD_FACTOR: int = 7
    LORA_CODING_RATE: int = 5  # 5 = 4/5
    INGESTA_LORA_PREAMBULO: int = 16

    # Broker MQTT (Mosquitto)
    MQTT_HOST: str = "172.30.0.1"
    MQTT_PORT: int = 1884
    MQTT_USER: str = "svc-ingest"
    MQTT_PASSWORD: str = ""
    MQTT_TOPIC_ROOT: str = "msh/EU_868"
    MQTT_TOPIC_PREFIX: str = "snm"

    # Base de datos PostgreSQL 17 + TimescaleDB
    DB_HOST: str = "172.30.0.1"
    DB_PORT: int = 5432
    DB_NAME: str = "snm_ingest"
    DB_USER: str = "snm_ingest"
    DB_PASSWORD: str = ""

    # Parámetros operativos y buffers
    INGESTA_VENTANA_PUBLICACION_S: float = 2.0
    INGESTA_VENTANA_DEDUP_MIN: int = 15
    INGESTA_POLIGONOS: str = "polygons/provincias-andalucia.geojson"
    INGESTA_PRECISION_MAX_M: float = 12000.0
    INGESTA_MARGEN_MIN_M: float = 500.0
    INGESTA_ENLACE_PRECISION_MAX_M: float = 3000.0
    INGESTA_ENLACE_MAX_KM: float = 300.0

    INGESTA_LOTE_FILAS: int = 500
    INGESTA_LOTE_MS: int = 1000
    INGESTA_BUFFER_MAX_FILAS: int = 100000

    HEALTH_PORT: int = 8080
    LOG_LEVEL: str = "INFO"

    @property
    def channels_list(self) -> list[str]:
        """Devuelve la lista de canales permitidos limpios."""
        return [c.strip() for c in self.ALLOWED_CHANNELS.split(",") if c.strip()]

    @property
    def infra_roles_list(self) -> list[str]:
        """Devuelve la lista de roles considerados infraestructura."""
        return [r.strip() for r in self.INFRA_ROLES.split(",") if r.strip()]

    @field_validator("PRIMARY_CHANNEL")
    @classmethod
    def validate_primary_channel(cls, v: str, info) -> str:
        """Verifica que el canal primario esté en la lista permitida."""
        return v


def load_config() -> IngestaConfig:
    """Carga y valida la configuración con reporte detallado de errores.

    Returns:
        Instancia validada de IngestaConfig.
    """
    try:
        cfg = IngestaConfig()
        # Validar variables críticas obligatorias en producción
        missing: list[str] = []
        if not cfg.MQTT_PASSWORD:
            missing.append("MQTT_PASSWORD")
        if not cfg.DB_PASSWORD:
            missing.append("DB_PASSWORD")

        if missing:
            # En modo test se permite ejecutar si no se conectan a red
            if os.getenv("SNM_TESTING") != "true":
                sys.stderr.write(
                    f"[ERROR] Variables de entorno obligatorias ausentes: {', '.join(missing)}\n"
                )
                sys.exit(2)

        if cfg.PRIMARY_CHANNEL not in cfg.channels_list:
            sys.stderr.write(
                f"[ERROR] PRIMARY_CHANNEL '{cfg.PRIMARY_CHANNEL}' no está en ALLOWED_CHANNELS\n"
            )
            sys.exit(2)

        return cfg
    except Exception as e:
        sys.stderr.write(f"[ERROR] Fallo al validar configuración de ingesta: {e}\n")
        sys.exit(2)
