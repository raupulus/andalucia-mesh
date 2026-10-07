"""Manejadores de eventos de Discord y conciliación de servidores y canales en on_ready."""

import logging
from datetime import UTC, datetime
from typing import Any

import discord
from discord import app_commands

from nucleo.base import GestorBase

logger = logging.getLogger("bot_discord.eventos")


class ClienteDiscord(discord.Client):
    """Cliente discord.py especializado con gestión de CommandTree y conciliación de destinos."""

    def __init__(self, gestor_base: GestorBase, *args: Any, **kwargs: Any) -> None:
        """Inicializa el cliente de Discord."""
        super().__init__(*args, **kwargs)
        self.gestor_base = gestor_base
        self.tree = app_commands.CommandTree(self)
        self._conciliado = False

    async def setup_hook(self) -> None:
        """Hook invocado antes de que el gateway inicie sesión."""
        pass

    async def on_ready(self) -> None:
        """Invocado cuando el cliente está listo y conectado al Gateway."""
        logger.info("Bot de Discord conectado como %s (id=%d).", self.user, self.user.id if self.user else 0)

        # Sincronización global idempotente de comandos
        try:
            synced = await self.tree.sync()
            logger.info("%d comandos de barra globales sincronizados con Discord.", len(synced))
        except Exception as e:
            logger.error("Error sincronizando árbol de comandos con Discord: %s", e)

        # Conciliación de servidores y canales en el arranque
        if not self._conciliado:
            await self._conciliar_destinos()
            self._conciliado = True

    async def on_guild_remove(self, guild: discord.Guild) -> None:
        """Invocado cuando el bot es expulsado de un servidor o el servidor es borrado."""
        logger.info("Bot expulsado del servidor %s (id=%d). Desactivando canales suscritos.", guild.name, guild.id)
        ahora = datetime.now(UTC)
        async with self.gestor_base.conexion() as conn, conn.transaction(), conn.cursor() as cur:
            await cur.execute(
                """
                UPDATE destino
                SET activo = false, motivo_baja = 'expulsado', baja_en = %s, actualizado_en = %s
                WHERE servidor_id = %s AND activo = true
                RETURNING id
                """,
                (ahora, ahora, guild.id),
            )
            filas = await cur.fetchall()
            for f in filas:
                dest_id = f[0]
                await cur.execute(
                    """
                    UPDATE envio
                    SET estado = 'caducado', ultimo_error = 'Servidor expulsado de Discord'
                    WHERE destino_id = %s AND estado = 'pendiente'
                    """,
                    (dest_id,),
                )

    async def on_guild_channel_delete(self, channel: discord.abc.GuildChannel) -> None:
        """Invocado cuando se elimina un canal de texto o anuncios."""
        logger.info("Canal eliminado en Discord: %s (id=%d). Desactivando destino.", channel.name, channel.id)
        ahora = datetime.now(UTC)
        async with self.gestor_base.conexion() as conn, conn.transaction(), conn.cursor() as cur:
            await cur.execute(
                """
                UPDATE destino
                SET activo = false, motivo_baja = 'canal_borrado', baja_en = %s, actualizado_en = %s
                WHERE plataforma_id = %s AND activo = true
                RETURNING id
                """,
                (ahora, ahora, channel.id),
            )
            fila = await cur.fetchone()
            if fila:
                dest_id = fila[0]
                await cur.execute(
                    """
                    UPDATE envio
                    SET estado = 'caducado', ultimo_error = 'Canal borrado en Discord'
                    WHERE destino_id = %s AND estado = 'pendiente'
                    """,
                    (dest_id,),
                )

    async def on_guild_channel_update(
        self,
        before: discord.abc.GuildChannel,
        after: discord.abc.GuildChannel,
    ) -> None:
        """Invocado cuando se actualiza un canal (detecta cambios de clase texto <-> anuncios)."""
        if (
            isinstance(before, discord.TextChannel)
            and isinstance(after, discord.TextChannel)
            and before.is_news() != after.is_news()
        ):
            nueva_clase = "anuncios" if after.is_news() else "texto"
            async with self.gestor_base.conexion() as conn, conn.transaction(), conn.cursor() as cur:
                await cur.execute(
                    """
                    UPDATE destino
                    SET clase = %s, actualizado_en = now()
                    WHERE plataforma_id = %s
                    """,
                    (nueva_clase, after.id),
                )

    async def _conciliar_destinos(self) -> None:
        """Concilia al arrancar destinos activos cuyos servidores o canales dejaron de existir."""
        logger.info("Iniciando conciliación de destinos de Discord...")
        ahora = datetime.now(UTC)

        async with self.gestor_base.conexion() as conn:
            async with conn.cursor() as cur:
                await cur.execute("SELECT id, plataforma_id, servidor_id FROM destino WHERE activo = true")
                activos = await cur.fetchall()

            for dest_id, canal_id, servidor_id in activos:
                guild = self.get_guild(servidor_id)
                if guild is None:
                    # El servidor ya no está disponible -> expulsado
                    async with conn.transaction(), conn.cursor() as cur_trx:
                        await cur_trx.execute(
                            """
                            UPDATE destino
                            SET activo = false, motivo_baja = 'expulsado', baja_en = %s, actualizado_en = %s
                            WHERE id = %s
                            """,
                            (ahora, ahora, dest_id),
                        )
                        await cur_trx.execute(
                            """
                            UPDATE envio
                            SET estado = 'caducado', ultimo_error = 'Servidor no encontrado en arranque'
                            WHERE destino_id = %s AND estado = 'pendiente'
                            """,
                            (dest_id,),
                        )
                else:
                    canal = guild.get_channel(canal_id)
                    if canal is None:
                        # El canal fue borrado mientras el bot estuvo detenido
                        async with conn.transaction(), conn.cursor() as cur_trx:
                            await cur_trx.execute(
                                """
                                UPDATE destino
                                SET activo = false, motivo_baja = 'canal_borrado', baja_en = %s, actualizado_en = %s
                                WHERE id = %s
                                """,
                                (ahora, ahora, dest_id),
                            )
                            await cur_trx.execute(
                                """
                                UPDATE envio
                                SET estado = 'caducado', ultimo_error = 'Canal no encontrado en arranque'
                                WHERE destino_id = %s AND estado = 'pendiente'
                                """,
                                (dest_id,),
                            )

        logger.info("Conciliación de destinos completada.")
