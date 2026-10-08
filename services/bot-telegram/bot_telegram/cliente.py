"""Inicialización del bot aiogram, registro de comandos y descripciones."""

import contextlib
import logging

from aiogram import Bot
from aiogram.types import (
    BotCommand,
    BotCommandScopeAllGroupChats,
    BotCommandScopeAllPrivateChats,
)

from bot_telegram.configuracion import ConfiguracionBotTelegram

logger = logging.getLogger("bot_telegram.cliente")


async def configurar_cliente_telegram(bot: Bot, config: ConfiguracionBotTelegram) -> str:
    """Configura webhook, comandos y descripciones en la Bot API al iniciar."""
    # 1. Obtener usuario del bot
    me = await bot.get_me()
    bot_username = me.username or ""
    logger.info("Bot de Telegram autenticado como @%s (id=%d).", bot_username, me.id)

    # 2. Desactivar cualquier webhook previo sin descartar actualizaciones pendientes
    await bot.delete_webhook(drop_pending_updates=False)

    # 3. Comandos para grupos y canales
    comandos_grupos = [
        BotCommand(command="status", description="Estado general de la malla"),
        BotCommand(command="battery", description="Batería de los routers [provincia]"),
        BotCommand(command="routers", description="Routers con batería, chutil y tx [provincia]"),
        BotCommand(command="levels", description="Ver o cambiar los riesgos (administradores)"),
        BotCommand(command="types", description="Ver o cambiar los tipos (administradores)"),
        BotCommand(command="pause", description="Silenciar o pausar alertas (administradores)"),
        BotCommand(command="resume", description="Reanudar alertas (administradores)"),
        BotCommand(command="disableexterior", description="Solo nodos de Andalucía (administradores)"),
        BotCommand(command="enableexterior", description="Incluir nodos de fuera de Andalucía (administradores)"),
        BotCommand(command="exterior", description="Estado del filtro exterior (administradores)"),
        BotCommand(command="settings", description="Configuración de este chat"),
        BotCommand(command="help", description="Ayuda y enlace a la web"),
    ]
    with contextlib.suppress(Exception):
        await bot.set_my_commands(commands=comandos_grupos, scope=BotCommandScopeAllGroupChats())

    # 4. Comandos para chats privados
    comandos_privados = [
        BotCommand(command="status", description="Estado general de la malla"),
        BotCommand(command="battery", description="Batería de los routers [provincia]"),
        BotCommand(command="routers", description="Routers con batería, chutil y tx [provincia]"),
        BotCommand(command="help", description="Ayuda y enlace a la web"),
    ]
    with contextlib.suppress(Exception):
        await bot.set_my_commands(commands=comandos_privados, scope=BotCommandScopeAllPrivateChats())

    # 5. Descripciones del bot
    desc = (
        f"Avisos de los problemas de la malla de {config.project_name}. "
        f"Añádeme a un grupo o canal. Más información: https://{config.project_domain}/bots"
    )
    desc_corta = f"Alertas de la malla de {config.project_name}"
    with contextlib.suppress(Exception):
        await bot.set_my_description(description=desc)
        await bot.set_my_short_description(short_description=desc_corta)

    return bot_username
