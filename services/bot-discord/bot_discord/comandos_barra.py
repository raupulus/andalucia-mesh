"""Comandos de barra (Slash Commands) de Discord y registro en CommandTree."""

import time
from datetime import UTC, datetime

import discord
from discord import app_commands

from bot_discord.configuracion import ConfiguracionBotDiscord
from nucleo.base import GestorBase
from nucleo.comandos import GestorComandos

OPCIONES_PROVINCIAS = [
    app_commands.Choice(name="Almería", value="ES-AL"),
    app_commands.Choice(name="Cádiz", value="ES-CA"),
    app_commands.Choice(name="Córdoba", value="ES-CO"),
    app_commands.Choice(name="Granada", value="ES-GR"),
    app_commands.Choice(name="Huelva", value="ES-H"),
    app_commands.Choice(name="Jaén", value="ES-J"),
    app_commands.Choice(name="Málaga", value="ES-MA"),
    app_commands.Choice(name="Sevilla", value="ES-SE"),
]


def registrar_comandos_barra(
    tree: app_commands.CommandTree,
    config: ConfiguracionBotDiscord,
    comandos: GestorComandos,
    gestor_base: GestorBase,
) -> None:
    """Registra los 9 comandos de barra canónicos en el árbol de comandos de Discord."""
    ultimo_comando_canal: dict[int, float] = {}

    def verificar_antispam_canal(channel_id: int) -> bool:
        ahora = time.monotonic()
        ult = ultimo_comando_canal.get(channel_id, 0.0)
        if ahora - ult < 5.0:
            return False
        ultimo_comando_canal[channel_id] = ahora
        return True

    def verificar_admin_interaccion(interaction: discord.Interaction) -> bool:
        if not interaction.channel or not isinstance(interaction.channel, discord.abc.GuildChannel):
            return False
        permisos = interaction.channel.permissions_for(interaction.user)  # type: ignore[arg-type]
        return bool(permisos.manage_channels)

    # 1. /status
    @tree.command(name="status", description="Estado general de la malla")
    async def cmd_status(interaction: discord.Interaction) -> None:
        if not interaction.channel_id or not verificar_antispam_canal(interaction.channel_id):
            await interaction.response.send_message("Espera unos segundos entre consultas.", ephemeral=True)
            return

        await interaction.response.defer(thinking=True)
        texto = await comandos.ejecutar_status()
        trozos = GestorComandos.partir_en_mensajes(texto, max_caracteres=2000)

        for i, trozo in enumerate(trozos):
            if i == 0:
                await interaction.followup.send(trozo, suppress_embeds=True)
            else:
                await interaction.followup.send(trozo, suppress_embeds=True)

    # 2. /battery
    @tree.command(name="battery", description="Batería de los routers [provincia]")
    @app_commands.describe(provincia="Filtrar por provincia")
    @app_commands.choices(provincia=OPCIONES_PROVINCIAS)
    async def cmd_battery(
        interaction: discord.Interaction,
        provincia: app_commands.Choice[str] | None = None,
    ) -> None:
        if not interaction.channel_id or not verificar_antispam_canal(interaction.channel_id):
            await interaction.response.send_message("Espera unos segundos entre consultas.", ephemeral=True)
            return

        await interaction.response.defer(thinking=True)
        cod_prov = provincia.value if provincia else None
        texto = await comandos.ejecutar_battery(cod_prov)
        trozos = GestorComandos.partir_en_mensajes(texto, max_caracteres=2000)

        for trozo in trozos:
            await interaction.followup.send(trozo, suppress_embeds=True)

    # 3. /routers
    @tree.command(name="routers", description="Routers con batería, chutil y tx [provincia]")
    @app_commands.describe(provincia="Filtrar por provincia")
    @app_commands.choices(provincia=OPCIONES_PROVINCIAS)
    async def cmd_routers(
        interaction: discord.Interaction,
        provincia: app_commands.Choice[str] | None = None,
    ) -> None:
        if not interaction.channel_id or not verificar_antispam_canal(interaction.channel_id):
            await interaction.response.send_message("Espera unos segundos entre consultas.", ephemeral=True)
            return

        await interaction.response.defer(thinking=True)
        cod_prov = provincia.value if provincia else None
        texto = await comandos.ejecutar_routers(cod_prov)
        trozos = GestorComandos.partir_en_mensajes(texto, max_caracteres=2000)

        for trozo in trozos:
            await interaction.followup.send(trozo, suppress_embeds=True)

    # 4. /levels
    @tree.command(name="levels", description="Ver o cambiar los riesgos (administradores)")
    @app_commands.describe(riesgos="Niveles de riesgo separados por espacios (bajo, medio, alto o todos)")
    async def cmd_levels(
        interaction: discord.Interaction,
        riesgos: str | None = None,
    ) -> None:
        if not interaction.channel_id:
            return

        # Verificar si el canal está suscrito
        async with gestor_base.conexion() as conn, conn.cursor() as cur:
            await cur.execute("SELECT id FROM destino WHERE plataforma_id = %s", (interaction.channel_id,))
            if not await cur.fetchone():
                await interaction.response.send_message("Este canal no recibe alertas. Usa /subscribe.", ephemeral=True)
                return

        argumentos = riesgos.split() if riesgos else []
        es_admin = verificar_admin_interaccion(interaction)

        if argumentos and not es_admin:
            await interaction.response.send_message("Solo quien puede gestionar este canal puede cambiar los filtros.", ephemeral=True)
            return

        texto = await comandos.ejecutar_levels(interaction.channel_id, es_admin, argumentos)
        await interaction.response.send_message(texto)

    # 5. /types
    @tree.command(name="types", description="Ver o cambiar los tipos (administradores)")
    @app_commands.describe(tipos="Tipos de aviso separados por espacios (infraestructura, clientes o todos)")
    async def cmd_types(
        interaction: discord.Interaction,
        tipos: str | None = None,
    ) -> None:
        if not interaction.channel_id:
            return

        async with gestor_base.conexion() as conn, conn.cursor() as cur:
            await cur.execute("SELECT id FROM destino WHERE plataforma_id = %s", (interaction.channel_id,))
            if not await cur.fetchone():
                await interaction.response.send_message("Este canal no recibe alertas. Usa /subscribe.", ephemeral=True)
                return

        argumentos = tipos.split() if tipos else []
        es_admin = verificar_admin_interaccion(interaction)

        if argumentos and not es_admin:
            await interaction.response.send_message("Solo quien puede gestionar este canal puede cambiar los filtros.", ephemeral=True)
            return

        texto = await comandos.ejecutar_types(interaction.channel_id, es_admin, argumentos)
        await interaction.response.send_message(texto)

    # 6. /settings
    @tree.command(name="settings", description="Configuración de este chat")
    async def cmd_settings(interaction: discord.Interaction) -> None:
        if not interaction.channel_id:
            return
        texto = await comandos.ejecutar_settings(interaction.channel_id)
        if texto == "Este chat no recibe alertas.":
            await interaction.response.send_message("Este canal no recibe alertas. Usa /subscribe.", ephemeral=True)
        else:
            await interaction.response.send_message(texto)

    # 7. /help
    @tree.command(name="help", description="Ayuda y enlace a la web")
    async def cmd_help(interaction: discord.Interaction) -> None:
        texto = comandos.ejecutar_help()
        await interaction.response.send_message(texto, ephemeral=True)

    # 8. /subscribe
    @tree.command(name="subscribe", description="Activar alertas en este canal")
    async def cmd_subscribe(interaction: discord.Interaction) -> None:
        if not interaction.guild or not interaction.channel or not isinstance(interaction.channel, discord.TextChannel):
            await interaction.response.send_message("Este tipo de canal no admite alertas. Usa un canal de texto o de anuncios.", ephemeral=True)
            return

        if not verificar_admin_interaccion(interaction):
            await interaction.response.send_message("Solo quien puede gestionar este canal puede activar o quitar las alertas.", ephemeral=True)
            return

        # Comprobar permisos del bot en el canal
        me = interaction.guild.me
        permisos_bot = interaction.channel.permissions_for(me)
        faltantes: list[str] = []
        if not permisos_bot.view_channel:
            faltantes.append("Ver canales")
        if not permisos_bot.send_messages:
            faltantes.append("Enviar mensajes")
        if not permisos_bot.embed_links:
            faltantes.append("Insertar enlaces")
        if not permisos_bot.read_message_history:
            faltantes.append("Leer el historial de mensajes")

        if faltantes:
            await interaction.response.send_message(f"Me faltan permisos en este canal: {', '.join(faltantes)}.", ephemeral=True)
            return

        clase = "anuncios" if interaction.channel.is_news() else "texto"
        canal_id = interaction.channel.id
        guild_id = interaction.guild.id

        # Comprobar estado previo en base de datos
        async with gestor_base.conexion() as conn, conn.transaction(), conn.cursor() as cur:
            await cur.execute("SELECT activo FROM destino WHERE plataforma_id = %s", (canal_id,))
            dest = await cur.fetchone()

            if dest and dest[0]:
                await interaction.response.send_message("Este canal ya recibe alertas. Usa /settings para ver su configuración.", ephemeral=True)
                return

            await cur.execute(
                """
                INSERT INTO destino (plataforma_id, servidor_id, clase, activo, alta_en, actualizado_en)
                VALUES (%s, %s, %s, true, now(), now())
                ON CONFLICT (plataforma_id) DO UPDATE
                SET servidor_id = EXCLUDED.servidor_id,
                    clase = EXCLUDED.clase,
                    activo = true,
                    motivo_baja = NULL,
                    baja_en = NULL,
                    fallando_desde = NULL,
                    riesgos = NULL,
                    tipos = NULL,
                    alta_en = now(),
                    actualizado_en = now()
                """,
                (canal_id, guild_id, clase),
            )

        resp = (
            f"✅ Este canal recibirá las alertas de {config.project_name}.\n"
            f"Riesgos: {', '.join(config.lista_riesgos_defecto)} · Tipos: {', '.join(config.lista_tipos_defecto)} (por defecto)\n"
            "Cámbialos con /levels y /types. Ayuda: /help"
        )
        await interaction.response.send_message(resp)

    # 9. /unsubscribe
    @tree.command(name="unsubscribe", description="Desactivar alertas en este canal")
    async def cmd_unsubscribe(interaction: discord.Interaction) -> None:
        if not interaction.channel_id:
            return

        if not verificar_admin_interaccion(interaction):
            await interaction.response.send_message("Solo quien puede gestionar este canal puede activar o quitar las alertas.", ephemeral=True)
            return

        canal_id = interaction.channel_id
        ahora = datetime.now(UTC)

        async with gestor_base.conexion() as conn, conn.transaction(), conn.cursor() as cur:
            await cur.execute("SELECT id, activo FROM destino WHERE plataforma_id = %s", (canal_id,))
            dest = await cur.fetchone()

            if not dest or not dest[1]:
                await interaction.response.send_message("Este canal no recibe alertas.", ephemeral=True)
                return

            dest_id = dest[0]
            await cur.execute(
                """
                UPDATE destino
                SET activo = false, motivo_baja = 'unsubscribe', baja_en = %s, actualizado_en = %s
                WHERE id = %s
                """,
                (ahora, ahora, dest_id),
            )
            await cur.execute(
                """
                UPDATE envio
                SET estado = 'caducado', ultimo_error = 'Canal desuscrito con /unsubscribe'
                WHERE destino_id = %s AND estado = 'pendiente'
                """,
                (dest_id,),
            )

        await interaction.response.send_message("Este canal ya no recibirá alertas.")

    # 10. /pause
    @tree.command(name="pause", description="Pausar o silenciar temporalmente las alertas")
    async def cmd_pause(interaction: discord.Interaction) -> None:
        if not interaction.channel_id:
            return
        es_admin = verificar_admin_interaccion(interaction)
        resp = await comandos.ejecutar_pause(interaction.channel_id, es_admin=es_admin)
        await interaction.response.send_message(resp, ephemeral=not es_admin)

    # 11. /resume
    @tree.command(name="resume", description="Reanudar el envío de alertas")
    async def cmd_resume(interaction: discord.Interaction) -> None:
        if not interaction.channel_id:
            return
        es_admin = verificar_admin_interaccion(interaction)
        resp = await comandos.ejecutar_resume(interaction.channel_id, es_admin=es_admin)
        await interaction.response.send_message(resp, ephemeral=not es_admin)

    # 12. /disable_exterior
    @tree.command(name="disable_exterior", description="Recibir solo alertas de nodos de Andalucía")
    async def cmd_disable_exterior(interaction: discord.Interaction) -> None:
        if not interaction.channel_id:
            return
        es_admin = verificar_admin_interaccion(interaction)
        resp = await comandos.ejecutar_disable_exterior(interaction.channel_id, es_admin=es_admin)
        await interaction.response.send_message(resp, ephemeral=not es_admin)

    # 13. /enable_exterior
    @tree.command(name="enable_exterior", description="Incluir alertas de nodos de fuera de Andalucía")
    async def cmd_enable_exterior(interaction: discord.Interaction) -> None:
        if not interaction.channel_id:
            return
        es_admin = verificar_admin_interaccion(interaction)
        resp = await comandos.ejecutar_enable_exterior(interaction.channel_id, es_admin=es_admin)
        await interaction.response.send_message(resp, ephemeral=not es_admin)

    # 14. /exterior
    @tree.command(name="exterior", description="Consultar o cambiar el filtro de nodos de fuera de Andalucía")
    @app_commands.describe(accion="Activar o desactivar alertas de fuera de Andalucía")
    @app_commands.choices(
        accion=[
            app_commands.Choice(name="Activar (permitir fuera de Andalucía)", value="activar"),
            app_commands.Choice(name="Desactivar (solo Andalucía)", value="desactivar"),
        ]
    )
    async def cmd_exterior(
        interaction: discord.Interaction,
        accion: app_commands.Choice[str] | None = None,
    ) -> None:
        if not interaction.channel_id:
            return
        es_admin = verificar_admin_interaccion(interaction)
        valor_accion = accion.value if accion else None
        resp = await comandos.ejecutar_exterior(interaction.channel_id, es_admin=es_admin, opcion=valor_accion)
        await interaction.response.send_message(resp, ephemeral=not es_admin)
