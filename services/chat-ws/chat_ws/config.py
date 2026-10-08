"""Configuración del microservicio chat-ws mediante variables de entorno."""

from functools import lru_cache

from pydantic_settings import BaseSettings, SettingsConfigDict


class Settings(BaseSettings):
    """Configuración tipada del microservicio chat-ws.

    Carga variables de entorno del sistema y de archivos .env opcionales.
    """

    model_config = SettingsConfigDict(
        env_file=(".env", "/srv/chat-ws/.env", "/srv/comun/.env"),
        env_file_encoding="utf-8",
        extra="ignore",
    )

    # Entorno y logs
    TZ: str = "Europe/Madrid"
    LOG_LEVEL: str = "INFO"

    # Broker MQTT
    MQTT_HOST: str = "172.30.0.1"
    MQTT_PORT: int = 1884
    MQTT_TOPIC_PREFIX: str = "snm"
    MQTT_USER: str = "svc-chatws"
    MQTT_PASSWORD: str = ""

    # Canales admitidos
    ALLOWED_CHANNELS: str = (
        "SFNarrow,Iberia,Andalucia,Cadiz,Huelva,Almeria,Granada,Jaen,Sevilla,Cordoba,Malaga,Ceuta,Melilla,sos"
    )
    PRIMARY_CHANNEL: str = "SFNarrow"

    # Servidor WebSocket
    CHAT_PUERTO: int = 8000
    CHAT_HOST: str = "0.0.0.0"

    # Servidor de salud interno
    HEALTH_PORT: int = 8080
    HEALTH_HOST: str = "0.0.0.0"

    # Límites operativos e historial en memoria
    CHAT_HISTORIAL: int = 20
    CHAT_MAX_CONEXIONES: int = 2000
    CHAT_MAX_POR_IP: int = 10
    CHAT_PROXIES_CONFIABLES: str = "172.30.0.1,127.0.0.1"

    # Parámetros de protocolo y control de flujo
    PING_INTERVAL: float = 30.0
    PING_TIMEOUT: float = 60.0
    CLIENT_QUEUE_SIZE: int = 500
    RATE_LIMIT_MSGS_PER_SEC: int = 5
    RATE_LIMIT_MAX_VIOLATION_SECONDS: float = 10.0
    MAX_INVALID_MSGS_PER_MINUTE: int = 5

    @property
    def allowed_channels_list(self) -> list[str]:
        """Devuelve la lista ordenada de canales permitidos limpios de espacios."""
        return [ch.strip() for ch in self.ALLOWED_CHANNELS.split(",") if ch.strip()]

    @property
    def trusted_proxies_set(self) -> set[str]:
        """Devuelve el conjunto de direcciones IP de proxies de confianza."""
        return {ip.strip() for ip in self.CHAT_PROXIES_CONFIABLES.split(",") if ip.strip()}


@lru_cache
def get_settings() -> Settings:
    """Devuelve la instancia singleton de la configuración."""
    return Settings()
