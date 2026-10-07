"""Adaptador de envío a través de aiogram traduciendo errores a excepciones del motor."""

import logging
import sys
from typing import Any

from aiogram import Bot
from aiogram.exceptions import (
    TelegramBadRequest,
    TelegramForbiddenError,
    TelegramMigrateToChat,
    TelegramNetworkError,
    TelegramRetryAfter,
    TelegramServerError,
    TelegramUnauthorizedError,
)
from aiogram.types import LinkPreviewOptions, ReplyParameters

from bot_telegram.formato_telegram import AdaptadorFormatoTelegram
from nucleo.formato import AvisoNeutro
from nucleo.motor_envios import (
    DestinoMigrado,
    DestinoPerdido,
    ErrorDestino,
    ErrorTransitorio,
)

logger = logging.getLogger("bot_telegram.adaptador")


class AdaptadorEnvioTelegram:
    """Implementa AdaptadorPlataforma para interactuar con la Bot API de Telegram."""

    def __init__(self, bot: Bot) -> None:
        """Inicializa el adaptador con la instancia del bot de aiogram."""
        self.bot = bot

    def serializar_contenido(self, aviso: AvisoNeutro) -> dict[str, Any]:
        """Delega la serialización a HTML."""
        return AdaptadorFormatoTelegram.serializar_contenido(aviso)

    async def enviar(
        self,
        plataforma_id: int,
        contenido: dict[str, Any],
        responder_a_mensaje_id: int | None,
        chat_envio_id_hilo: int | None,
    ) -> int:
        """Emite el mensaje a Telegram capturando y clasificando errores."""
        texto = str(contenido.get("text", ""))
        parse_mode = contenido.get("parse_mode", "HTML")
        disable_notification = bool(contenido.get("disable_notification", False))

        reply_params: ReplyParameters | None = None
        # Solo enlazar en hilo si el mensaje previo ocurrió en el mismo chat actual
        if responder_a_mensaje_id and (chat_envio_id_hilo is None or chat_envio_id_hilo == plataforma_id):
            reply_params = ReplyParameters(
                message_id=responder_a_mensaje_id,
                allow_sending_without_reply=True,
            )

        link_preview = LinkPreviewOptions(is_disabled=True)

        try:
            msg = await self.bot.send_message(
                chat_id=plataforma_id,
                text=texto,
                parse_mode=parse_mode,
                disable_notification=disable_notification,
                reply_parameters=reply_params,
                link_preview_options=link_preview,
            )
            return int(msg.message_id)

        except TelegramRetryAfter as e:
            raise ErrorTransitorio(f"429 Too Many Requests: espera {e.retry_after}s", retry_after=float(e.retry_after)) from e

        except (TelegramServerError, TelegramNetworkError) as e:
            raise ErrorTransitorio(f"Fallo de red/servidor Telegram: {e}") from e

        except TelegramMigrateToChat as e:
            raise DestinoMigrado(nuevo_id=int(e.migrate_to_chat_id)) from e

        except TelegramUnauthorizedError:
            logger.critical("Token de Telegram inválido o revocado. Saliendo con código 1.")
            sys.exit(1)

        except TelegramForbiddenError as e:
            msg_error = str(e).lower()
            if any(k in msg_error for k in ("kicked", "not a member", "deleted", "blocked")):
                raise DestinoPerdido("expulsado") from e
            raise ErrorDestino(f"Permiso denegado en destino: {e}") from e

        except TelegramBadRequest as e:
            msg_error = str(e).lower()
            if "chat not found" in msg_error:
                raise DestinoPerdido("chat_inexistente") from e
            if "migrate to" in msg_error:
                # Extraer número si está en el mensaje
                for p in msg_error.split():
                    if p.lstrip("-").isdigit():
                        raise DestinoMigrado(nuevo_id=int(p)) from e
            if any(k in msg_error for k in ("rights", "forbidden", "not enough")):
                raise ErrorDestino(f"Falta de permisos en destino: {e}") from e

            # Fallo propio de parsing o longitud
            logger.error("Error BadRequest en Telegram (posible fallo propio de entidad o tamaño): %s", e)
            raise ErrorDestino(f"BadRequest: {e}") from e
