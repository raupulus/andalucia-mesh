"""Configuración específica del bot de Discord."""

from nucleo.config import ConfiguracionBots


class ConfiguracionBotDiscord(ConfiguracionBots):
    """Variables de entorno específicas para el bot de Discord."""

    discord_bot_token: str
    discord_app_id: int
