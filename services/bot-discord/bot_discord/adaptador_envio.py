"""Adaptador de envío a través de discord.py traduciendo errores a excepciones del motor."""

import logging
import sys
from typing import Any

import aiohttp
import discord

from bot_discord.formato_discord import AdaptadorFormatoDiscord
from nucleo.formato import AvisoNeutro
from nucleo.motor_envios import (
    DestinoPerdido,
    ErrorDestino,
    ErrorTransitorio,
)

logger = logging.getLogger("bot_discord.adaptador")


class AdaptadorEnvioDiscord:
    """Implementa AdaptadorPlataforma para interactuar con la API REST y Gateway de Discord."""

    def __init__(self, client: discord.Client, project_name: str) -> None:
        """Inicializa el adaptador con el cliente de discord.py."""
        self.client = client
        self.formateador = AdaptadorFormatoDiscord(project_name)

    def serializar_contenido(self, aviso: AvisoNeutro) -> dict[str, Any]:
        """Delega la serialización a embeds de Discord."""
        return self.formateador.serializar_contenido(aviso)

    async def enviar(
        self,
        plataforma_id: int,
        contenido: dict[str, Any],
        responder_a_mensaje_id: int | None,
        chat_envio_id_hilo: int | None,
    ) -> int:
        """Emite el mensaje con Embeds a Discord clasificando errores."""
        channel = self.client.get_channel(plataforma_id)
        if channel is None:
            try:
                channel = await self.client.fetch_channel(plataforma_id)
            except discord.NotFound as e:
                if e.code == 10003:
                    raise DestinoPerdido("canal_borrado") from e
                if e.code == 10004:
                    raise DestinoPerdido("expulsado") from e
                raise ErrorDestino(f"Canal no encontrado: {e}") from e
            except (discord.DiscordServerError, aiohttp.ClientError, TimeoutError) as e:
                raise ErrorTransitorio(f"Fallo de conexión al obtener canal: {e}") from e
            except Exception as e:
                raise ErrorDestino(f"Error accediendo al canal: {e}") from e

        if not isinstance(channel, (discord.TextChannel, discord.VoiceChannel, discord.StageChannel, discord.Thread)):
            raise ErrorDestino(f"Tipo de canal no soportado para envío: {type(channel)}")

        embeds_raw = contenido.get("embeds", [])
        embeds = [discord.Embed.from_dict(e) for e in embeds_raw]

        referencia: discord.MessageReference | None = None
        if responder_a_mensaje_id and (chat_envio_id_hilo is None or chat_envio_id_hilo == plataforma_id):
            referencia = discord.MessageReference(
                message_id=responder_a_mensaje_id,
                channel_id=plataforma_id,
                fail_if_not_exists=False,
            )

        try:
            msg = await channel.send(
                embeds=embeds,
                reference=referencia,  # type: ignore[arg-type]
                allowed_mentions=discord.AllowedMentions.none(),
            )
            return int(msg.id)

        except discord.RateLimited as e:
            raise ErrorTransitorio(f"Discord Rate Limited: espera {e.retry_after}s", retry_after=float(e.retry_after)) from e

        except discord.Forbidden as e:
            raise ErrorDestino(f"Permisos insuficientes en canal de Discord: {e}") from e

        except discord.NotFound as e:
            if e.code == 10003:
                raise DestinoPerdido("canal_borrado") from e
            if e.code == 10004:
                raise DestinoPerdido("expulsado") from e
            raise ErrorDestino(f"NotFound en Discord: {e}") from e

        except discord.HTTPException as e:
            if e.status == 429:
                retry_after = getattr(e, "retry_after", 5.0)
                raise ErrorTransitorio(f"HTTP 429 en Discord: {e}", retry_after=float(retry_after)) from e
            if e.status >= 500:
                raise ErrorTransitorio(f"HTTP {e.status} en Discord: {e}") from e
            raise ErrorDestino(f"HTTP {e.status} en Discord: {e}") from e

        except (discord.DiscordServerError, aiohttp.ClientError, TimeoutError) as e:
            raise ErrorTransitorio(f"Error de red con Discord: {e}") from e

        except discord.LoginFailure:
            logger.critical("Token de Discord inválido o revocado. Saliendo.")
            sys.exit(1)
