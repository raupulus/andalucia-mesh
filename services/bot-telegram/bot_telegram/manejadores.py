"""Manejadores de eventos, comandos y ciclo de vida de destinos de aiogram."""

import asyncio
import contextlib
import logging
import time
from datetime import UTC, datetime

from aiogram import Bot, Router
from aiogram.enums import ChatMemberStatus, ChatType
from aiogram.exceptions import TelegramBadRequest
from aiogram.types import (
    ChatMemberAdministrator,
    ChatMemberOwner,
    ChatMemberRestricted,
    ChatMemberUpdated,
    LinkPreviewOptions,
    Message,
)

from bot_telegram.configuracion import ConfiguracionBotTelegram
from nucleo.base import GestorBase
from nucleo.comandos import GestorComandos

logger = logging.getLogger("bot_telegram.manejadores")

COMANDOS_VALIDOS = {"status", "battery", "routers", "levels", "types", "settings", "help", "start"}


class ManejadoresTelegram:
    """Registra y despacha los eventos de Telegram delegando la lógica en GestorComandos."""

    def __init__(
        self,
        bot: Bot,
        config: ConfiguracionBotTelegram,
        gestor_base: GestorBase,
        comandos: GestorComandos,
        bot_username: str,
    ) -> None:
        """Inicializa los manejadores."""
        self.bot = bot
        self.config = config
        self.gestor_base = gestor_base
        self.comandos = comandos
        self.bot_username = bot_username.lower()

        self.router = Router()
        self._cache_admins: dict[tuple[int, int], tuple[float, bool]] = {}
        self._ultimo_comando_chat: dict[int, float] = {}

        self._registrar_rutas()

    def _registrar_rutas(self) -> None:
        """Asocia los métodos a los eventos de aiogram."""
        self.router.my_chat_member.register(self.manejar_my_chat_member)
        self.router.message.register(self.manejar_mensaje)
        self.router.channel_post.register(self.manejar_channel_post)

    async def asegurar_destino_existe(self, chat_id: int, clase: str) -> int:
        """Asegura que el chat o canal esté registrado como destino en la base de datos."""
        async with self.gestor_base.conexion() as conn, conn.transaction(), conn.cursor() as cur:
            await cur.execute(
                """
                INSERT INTO destino (plataforma_id, clase, activo, alta_en, actualizado_en)
                VALUES (%s, %s, true, now(), now())
                ON CONFLICT (plataforma_id) DO UPDATE
                SET activo = true,
                    clase = EXCLUDED.clase,
                    motivo_baja = NULL,
                    baja_en = NULL,
                    fallando_desde = NULL,
                    actualizado_en = now()
                RETURNING id
                """,
                (chat_id, clase),
            )
            fila = await cur.fetchone()
            return int(fila[0]) if fila else 0

    async def manejar_my_chat_member(self, event: ChatMemberUpdated) -> None:
        """Gestiona altas, bajas, promociones y cambios de permisos del bot en grupos y canales."""
        chat = event.chat
        if chat.type == ChatType.PRIVATE:
            return  # Ignorar eventos de chat privado

        clase = "channel" if chat.type == ChatType.CHANNEL else ("supergroup" if chat.type == ChatType.SUPERGROUP else "group")
        estado_nuevo = event.new_chat_member.status

        logger.info(
            "my_chat_member recibido para chat %d (%s) con estado %s.",
            chat.id,
            clase,
            estado_nuevo,
        )

        if estado_nuevo in (ChatMemberStatus.MEMBER, ChatMemberStatus.ADMINISTRATOR):
            if chat.type == ChatType.CHANNEL:
                # Comprobar si tiene permiso de publicar mensajes
                can_post = False
                if isinstance(event.new_chat_member, ChatMemberAdministrator):
                    can_post = bool(event.new_chat_member.can_post_messages)
                if can_post:
                    await self.asegurar_destino_existe(chat.id, clase)
                else:
                    await self._desactivar_chat(chat.id, "sin_permiso")
            else:
                # Grupo o supergrupo: alta y saludo
                await self.asegurar_destino_existe(chat.id, clase)
                saludo = (
                    f"Hola. Avisaré aquí de los problemas de la malla de {self.config.project_name}.\n"
                    f"Riesgos: {', '.join(self.config.lista_riesgos_defecto)} · Tipos: {', '.join(self.config.lista_tipos_defecto)} (por defecto)\n"
                    "Los administradores pueden cambiarlos con /levels y /types. Ayuda: /help"
                )
                with contextlib.suppress(Exception):
                    await self.bot.send_message(
                        chat_id=chat.id,
                        text=saludo,
                        link_preview_options=LinkPreviewOptions(is_disabled=True),
                    )

        elif estado_nuevo == ChatMemberStatus.RESTRICTED:
            can_send = False
            if isinstance(event.new_chat_member, ChatMemberRestricted):
                can_send = bool(event.new_chat_member.can_send_messages)
            if can_send:
                await self.asegurar_destino_existe(chat.id, clase)
            else:
                await self._desactivar_chat(chat.id, "sin_permiso")

        elif estado_nuevo in (ChatMemberStatus.LEFT, ChatMemberStatus.KICKED):
            await self._desactivar_chat(chat.id, "expulsado")

    async def _desactivar_chat(self, chat_id: int, motivo: str) -> None:
        """Marca un destino como inactivo y caduca sus envíos pendientes."""
        ahora = datetime.now(UTC)
        async with self.gestor_base.conexion() as conn, conn.transaction(), conn.cursor() as cur:
            await cur.execute(
                """
                UPDATE destino
                SET activo = false, motivo_baja = %s, baja_en = %s, actualizado_en = %s
                WHERE plataforma_id = %s
                RETURNING id
                """,
                (motivo, ahora, ahora, chat_id),
            )
            fila = await cur.fetchone()
            if fila:
                dest_id = fila[0]
                await cur.execute(
                    """
                    UPDATE envio
                    SET estado = 'caducado', ultimo_error = %s
                    WHERE destino_id = %s AND estado = 'pendiente'
                    """,
                    (f"Destino dado de baja: {motivo}", dest_id),
                )

    async def manejar_mensaje(self, message: Message) -> None:
        """Procesa mensajes de texto en grupos y chats privados."""
        if not message.text:
            return  # Descarte silencioso por privacidad

        # Descartar mensajes antiguos (> 120s) al reconectar
        if message.date:
            edad = (datetime.now(UTC) - message.date.astimezone(UTC)).total_seconds()
            if edad > self.config.telegram_comando_max_edad_s:
                return

        # Solo procesar comandos
        if not message.text.startswith("/"):
            return

        await self._despachar_comando(message, es_canal=False)

    async def manejar_channel_post(self, message: Message) -> None:
        """Procesa publicaciones de comandos en canales."""
        if not message.text or not message.text.startswith("/"):
            return
        await self._despachar_comando(message, es_canal=True)

    async def _despachar_comando(self, message: Message, es_canal: bool) -> None:
        """Parsea el comando, valida permisos, invoca el gestor y emite la respuesta."""
        chat_id = message.chat.id
        texto = message.text or ""
        partes = texto.strip().split()
        comando_raw = partes[0][1:].lower()
        argumentos = partes[1:]

        # Evaluar si el comando va dirigido a este bot (/status@mi_bot)
        if "@" in comando_raw:
            nombre_cmd, target_bot = comando_raw.split("@", 1)
            if target_bot != self.bot_username:
                return  # Comando para otro bot: ignorar silenciosamente
            comando_raw = nombre_cmd

        if comando_raw not in COMANDOS_VALIDOS:
            return

        # Anti-spam: límite de 1 comando cada 5s por chat
        ahora_m = time.monotonic()
        ultimo_m = self._ultimo_comando_chat.get(chat_id, 0.0)
        if ahora_m - ultimo_m < 5.0:
            return  # Ignorar comando en exceso de tasa
        self._ultimo_comando_chat[chat_id] = ahora_m

        es_privado = message.chat.type == ChatType.PRIVATE

        # En grupos y canales, asegurar que el destino exista
        if not es_privado:
            clase = "channel" if es_canal else ("supergroup" if message.chat.type == ChatType.SUPERGROUP else "group")
            await self.asegurar_destino_existe(chat_id, clase)

        # Tratar /start
        if comando_raw == "start":
            if es_privado:
                comando_raw = "help"
            else:
                return  # En grupos /start se ignora

        # Verificar si los comandos de destino se ejecutaron en privado
        if es_privado and comando_raw in ("levels", "types", "settings"):
            await message.reply("Este comando solo funciona en grupos y canales.")
            return

        # Comprobar permisos de administrador
        es_admin = False
        if es_canal:
            es_admin = True
        elif not es_privado:
            es_admin = await self._verificar_admin_grupo(message)

        # Ejecutar lógica canónica del comando
        respuesta = ""
        if comando_raw == "status":
            respuesta = await self.comandos.ejecutar_status()
        elif comando_raw == "battery":
            prov = argumentos[0] if argumentos else None
            respuesta = await self.comandos.ejecutar_battery(prov)
        elif comando_raw == "routers":
            prov = argumentos[0] if argumentos else None
            respuesta = await self.comandos.ejecutar_routers(prov)
        elif comando_raw == "levels":
            respuesta = await self.comandos.ejecutar_levels(chat_id, es_admin, argumentos)
        elif comando_raw == "types":
            respuesta = await self.comandos.ejecutar_types(chat_id, es_admin, argumentos)
        elif comando_raw == "settings":
            respuesta = await self.comandos.ejecutar_settings(chat_id)
        elif comando_raw == "help":
            respuesta = self.comandos.ejecutar_help()

        # Partir en mensajes si supera 4.000 caracteres (máximo 3)
        trozos = GestorComandos.partir_en_mensajes(respuesta, max_caracteres=4000)

        # Enviar respetando temas si aplica
        thread_id = message.message_thread_id if message.chat.is_forum else None
        mensajes_enviados: list[Message] = []

        for trozo in trozos:
            try:
                enviado = await self.bot.send_message(
                    chat_id=chat_id,
                    text=trozo,
                    message_thread_id=thread_id,
                    link_preview_options=LinkPreviewOptions(is_disabled=True),
                )
                mensajes_enviados.append(enviado)
            except Exception as e:
                logger.error("Error enviando respuesta a comando /%s en chat %d: %s", comando_raw, chat_id, e)

        # En canales: borrado diferido de comandos /levels, /types y /settings
        if es_canal and comando_raw in ("levels", "types", "settings"):
            asyncio.create_task(
                self._borrar_comando_canal_diferido(
                    chat_id=chat_id,
                    cmd_msg_id=message.message_id,
                    resp_msg_ids=[m.message_id for m in mensajes_enviados],
                )
            )

    async def _verificar_admin_grupo(self, message: Message) -> bool:
        """Comprueba si el emisor del mensaje es administrador del grupo."""
        # 1. Administrador anónimo (escribe en nombre del propio grupo)
        if message.sender_chat and message.sender_chat.id == message.chat.id:
            return True

        # 2. Mensaje en nombre de otro canal distinto -> no administrador
        if message.sender_chat and message.sender_chat.id != message.chat.id:
            return False

        if not message.from_user:
            return False

        usuario_id = message.from_user.id
        chat_id = message.chat.id
        clave = (chat_id, usuario_id)
        ahora = time.monotonic()

        # Comprobar caché de 60 segundos
        if clave in self._cache_admins:
            exp, es_adm = self._cache_admins[clave]
            if ahora < exp:
                return es_adm

        try:
            member = await self.bot.get_chat_member(chat_id, usuario_id)
            es_adm = isinstance(member, (ChatMemberAdministrator, ChatMemberOwner))
            self._cache_admins[clave] = (ahora + 60.0, es_adm)
            return es_adm
        except Exception:
            return False

    async def _borrar_comando_canal_diferido(
        self,
        chat_id: int,
        cmd_msg_id: int,
        resp_msg_ids: list[int],
    ) -> None:
        """Borra tras TELEGRAM_BORRAR_COMANDOS_CANAL_S segundos el comando y sus respuestas en canal."""
        await asyncio.sleep(self.config.telegram_borrar_comandos_canal_s)
        # Intentar borrar el comando original
        with contextlib.suppress(TelegramBadRequest):
            await self.bot.delete_message(chat_id=chat_id, message_id=cmd_msg_id)

        # Borrar las respuestas
        for mid in resp_msg_ids:
            with contextlib.suppress(TelegramBadRequest):
                await self.bot.delete_message(chat_id=chat_id, message_id=mid)
