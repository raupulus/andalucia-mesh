"""Módulo de configuración y variables de entorno para el Detector de Alertas."""

from pathlib import Path

from pydantic import Field
from pydantic_settings import BaseSettings, SettingsConfigDict


class Settings(BaseSettings):
    """Configuración tipada del microservicio cargada desde entorno y archivos .env."""

    model_config = SettingsConfigDict(
        env_file=("/srv/comun/.env", ".env"),
        env_file_encoding="utf-8",
        extra="ignore",
    )

    # Entorno y zona horaria
    tz: str = Field(default="Europe/Madrid", validation_alias="TZ")
    log_level: str = Field(default="INFO", validation_alias="LOG_LEVEL")

    # Configuración de red y roles de infraestructura
    mqtt_host: str = Field(default="mosquitto", validation_alias="MQTT_HOST")
    mqtt_port: int = Field(default=1884, validation_alias="MQTT_PORT")
    mqtt_topic_prefix: str = Field(default="snm", validation_alias="MQTT_TOPIC_PREFIX")
    infra_roles: str = Field(default="ROUTER,ROUTER_LATE,REPEATER", validation_alias="INFRA_ROLES")
    saturacion_peso_routers: float = Field(default=0.6, validation_alias="SATURACION_PESO_ROUTERS")
    saturacion_peso_clientes: float = Field(default=0.4, validation_alias="SATURACION_PESO_CLIENTES")

    # Base de datos PostgreSQL
    db_host: str = Field(default="172.30.0.1", validation_alias="DB_HOST")
    db_port: int = Field(default=5432, validation_alias="DB_PORT")
    db_name: str = Field(default="snm_detector", validation_alias="DB_NAME")
    db_user: str = Field(default="snm_detector", validation_alias="DB_USER")
    db_password: str = Field(default="", validation_alias="DB_PASSWORD")

    # Socket UNIX de salida
    alertas_socket: str = Field(default="/run/snm/alertas.sock", validation_alias="ALERTAS_SOCKET")
    alertas_socket_gid: int = Field(default=10500, validation_alias="ALERTAS_SOCKET_GID")

    # Credenciales de cliente MQTT
    mqtt_user: str = Field(default="svc-detector", validation_alias="MQTT_USER")
    mqtt_client_id: str = Field(default="detector-alertas", validation_alias="MQTT_CLIENT_ID")
    mqtt_password: str = Field(default="", validation_alias="MQTT_PASSWORD")

    # Rutas de archivos de configuración YAML
    clasificacion_path: str = Field(default="/config/clasificacion.yaml", validation_alias="CLASIFICACION_PATH")
    reglas_path: str = Field(default="/config/reglas.yaml", validation_alias="REGLAS_PATH")

    # Parámetros operativos y de rendimiento
    recarga_s: int = Field(default=30, validation_alias="RECARGA_S")
    health_port: int = Field(default=8080, validation_alias="HEALTH_PORT")
    cola_max: int = Field(default=10000, validation_alias="COLA_MAX")
    snapshot_s: int = Field(default=60, validation_alias="SNAPSHOT_S")
    retencion_dias: int = Field(default=365, validation_alias="RETENCION_DIAS")

    # Parámetros del servidor de socket
    reenvio_max_h: int = Field(default=24, validation_alias="REENVIO_MAX_H")
    latido_s: int = Field(default=30, validation_alias="LATIDO_S")
    cliente_max_pendientes: int = Field(default=1000, validation_alias="CLIENTE_MAX_PENDIENTES")
    saludo_timeout_s: float = Field(default=10.0, validation_alias="SALUDO_TIMEOUT_S")

    # Calibración y diagnósticos
    grabacion_dias: int = Field(default=0, validation_alias="GRABACION_DIAS")

    @property
    def parsed_infra_roles(self) -> frozenset[str]:
        """Devuelve el conjunto de roles considerados como infraestructura en mayúsculas."""
        return frozenset(r.strip().upper() for r in self.infra_roles.split(",") if r.strip())

    def resolve_clasificacion_path(self) -> Path:
        """Resuelve la ruta real existente para el archivo clasificacion.yaml."""
        p = Path(self.clasificacion_path)
        if p.is_file():
            return p
        # Alternativa en desarrollo local
        local_p = Path(__file__).resolve().parent.parent / "config" / "clasificacion.yaml"
        if local_p.is_file():
            return local_p
        return p

    def resolve_reglas_path(self) -> Path:
        """Resuelve la ruta real existente para el archivo reglas.yaml."""
        p = Path(self.reglas_path)
        if p.is_file():
            return p
        # Alternativa en desarrollo local
        local_p = Path(__file__).resolve().parent.parent / "config" / "reglas.yaml"
        if local_p.is_file():
            return local_p
        return p
