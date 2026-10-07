"""Configuración específica del bot de Telegram."""

from nucleo.config import ConfiguracionBots


class ConfiguracionBotTelegram(ConfiguracionBots):
    """Variables de entorno específicas para el bot de Telegram."""

    telegram_bot_token: str
    telegram_comando_max_edad_s: int = 120
    telegram_borrar_comandos_canal_s: int = 60
