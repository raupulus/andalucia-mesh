"""Configuración base y validación estricta de variables de entorno."""

import sys

from pydantic import ValidationError
from pydantic_settings import BaseSettings, SettingsConfigDict


class ConfiguracionBase(BaseSettings):
    """Variables de entorno comunes a las tres aplicaciones."""

    model_config = SettingsConfigDict(
        env_file=(".env", "/srv/comun/.env"),
        env_file_encoding="utf-8",
        extra="ignore",
        case_sensitive=False,
    )

    project_name: str = "Sur Nodos en Mallas"
    project_domain: str = "mesh.desdechipiona.es"
    project_contact: str = "public@raupulus.dev"
    tz: str = "Europe/Madrid"

    db_host: str = "172.30.0.1"
    db_port: int = 5432
    db_name: str
    db_user: str
    db_password: str

    alertas_socket: str = "/run/snm/alertas.sock"
    alertas_socket_gid: int = 10500
    log_level: str = "INFO"
    retencion_dias: int = 365
    health_port: int = 8080


class ConfiguracionBots(ConfiguracionBase):
    """Variables adicionales específicas para los bots de Telegram y Discord."""

    portal_api_url: str = "https://mesh.desdechipiona.es/api/v1"
    bot_riesgos_defecto: str = "alto"
    bot_tipos_defecto: str = "infraestructura"
    bot_exterior_defecto: bool = False
    bot_max_mensajes_minuto: int = 10
    bot_max_mensajes_segundo: int = 25
    bot_agrupar_umbral: int = 5
    bot_agrupar_ventana_s: int = 120
    bot_dias_fallo_desactivar: int = 3
    api_cache_s: int = 30
    api_timeout_s: int = 5

    @property
    def lista_riesgos_defecto(self) -> list[str]:
        """Devuelve la lista limpia de riesgos por defecto."""
        return [r.strip().lower() for r in self.bot_riesgos_defecto.split(",") if r.strip()]

    @property
    def lista_tipos_defecto(self) -> list[str]:
        """Devuelve la lista limpia de tipos por defecto."""
        return [t.strip().lower() for t in self.bot_tipos_defecto.split(",") if t.strip()]


def validar_o_salir(cls_config: type[BaseSettings]) -> BaseSettings:
    """Instancia y valida la configuración; si falla, sale con código 1 sin mostrar secretos."""
    try:
        return cls_config()
    except ValidationError as err:
        detalles = err.errors()
        for d in detalles:
            campo = ".".join(str(loc) for loc in d["loc"])
            sys.stderr.write(f"ERROR: Variable de entorno obligatoria o inválida: {campo.upper()}\n")
        sys.exit(1)
