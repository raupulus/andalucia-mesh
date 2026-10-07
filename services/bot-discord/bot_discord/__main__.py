"""Punto de entrada principal del bot de Discord."""

import asyncio
import contextlib
import signal
from datetime import UTC, datetime
from pathlib import Path
from typing import Any

import discord

from bot_discord.adaptador_envio import AdaptadorEnvioDiscord
from bot_discord.comandos_barra import registrar_comandos_barra
from bot_discord.configuracion import ConfiguracionBotDiscord
from bot_discord.eventos import ClienteDiscord
from nucleo.api_portal import ClientePortal
from nucleo.base import GestorBase
from nucleo.catalogo import GestorCatalogo
from nucleo.comandos import GestorComandos
from nucleo.config import validar_o_salir
from nucleo.motor_envios import MotorEnvios
from nucleo.registro import configurar_registro
from nucleo.retencion import GestorRetencion
from nucleo.salud import ServidorSalud
from nucleo.socket_alertas import ClienteSocketAlertas

ID_BLOQUEO_BOT_DISCORD = 10820


async def ejecutar_bot(config: ConfiguracionBotDiscord) -> None:
    """Orquesta todos los componentes del bot de Discord."""
    logger = configurar_registro("bot-discord", config.log_level)
    logger.info("Iniciando Bot de Discord para Andalucía Mesh...")

    # Base de datos y migraciones
    gestor_base = GestorBase(config, "bot-discord")
    await gestor_base.conectar()

    dir_migraciones = Path(__file__).resolve().parents[1] / "migrations"
    migradas = await gestor_base.aplicar_migraciones(dir_migraciones)
    logger.info("Migraciones verificadas (%d aplicadas).", migradas)

    # Bloqueo consultivo de instancia única
    await gestor_base.adquirir_bloqueo_instancia(ID_BLOQUEO_BOT_DISCORD)

    # API del portal, catálogo y comandos
    cliente_portal = ClientePortal(config, "bot-discord")
    await cliente_portal.iniciar()

    catalogo = GestorCatalogo(cliente_portal)
    await catalogo.iniciar()

    comandos = GestorComandos(config, cliente_portal, catalogo, gestor_base)

    # Cliente Discord con intents mínimos y menciones desactivadas
    intents = discord.Intents.none()
    intents.guilds = True

    cliente = ClienteDiscord(
        gestor_base=gestor_base,
        intents=intents,
        allowed_mentions=discord.AllowedMentions.none(),
    )
    registrar_comandos_barra(cliente.tree, config, comandos, gestor_base)

    # Motor de envíos y adaptador
    adaptador_envio = AdaptadorEnvioDiscord(cliente, config.project_name)
    motor = MotorEnvios(config, gestor_base, catalogo, adaptador_envio)
    await motor.iniciar()

    # Cliente del socket UNIX
    cliente_socket = ClienteSocketAlertas(
        ruta_socket=config.alertas_socket,
        nombre_cliente="bot-discord",
        gestor_base=gestor_base,
        manejador_transicion=motor.procesar_transicion,
    )
    await cliente_socket.iniciar()

    # Gestor de retención de 365 días
    retencion = GestorRetencion(
        gestor_base=gestor_base,
        tabla_principal="envio",
        dias_retencion=config.retencion_dias,
        zona_horaria=config.tz,
    )
    await retencion.iniciar()

    # Verificar si el token es una plantilla o está deshabilitado
    token_configurado = (
        bool(config.discord_bot_token)
        and not config.discord_bot_token.startswith("cambiar_por_")
        and config.discord_bot_token != "desactivado"
    )

    tarea_discord: asyncio.Task[None] | None = None
    if token_configurado:
        tarea_discord = asyncio.create_task(cliente.start(config.discord_bot_token))
    else:
        logger.warning(
            "DISCORD_BOT_TOKEN tiene un valor de plantilla ('%s'). "
            "El servicio permanece activo con servidor de salud y socket a la espera de credenciales.",
            config.discord_bot_token,
        )

    # Servidor de salud HTTP en 8080
    ultimo_ok_plataforma: datetime = datetime.now(UTC)

    async def obtener_estado_salud() -> dict[str, Any]:
        nonlocal ultimo_ok_plataforma
        base_ok = await gestor_base.comprobar_salud()
        socket_ok = cliente_socket.conectado
        plataforma_ok = bool(
            tarea_discord and cliente.is_ready() and not cliente.is_closed() and not tarea_discord.done()
        )

        if plataforma_ok:
            ultimo_ok_plataforma = datetime.now(UTC)

        pendientes = 0
        mas_antiguo_s = 0
        destinos_activos = 0

        if base_ok:
            try:
                async with gestor_base.conexion() as conn, conn.cursor() as cur:
                    await cur.execute(
                        """
                        SELECT count(*), min(creado_en)
                        FROM envio
                        WHERE estado = 'pendiente'
                        """
                    )
                    fila_env = await cur.fetchone()
                    if fila_env:
                        pendientes = fila_env[0] or 0
                        if fila_env[1]:
                            mas_antiguo_s = int((datetime.now(UTC) - fila_env[1]).total_seconds())

                    await cur.execute("SELECT count(*) FROM destino WHERE activo = true")
                    fila_act = await cur.fetchone()
                    destinos_activos = fila_act[0] if fila_act else 0
            except Exception as err:
                logger.error("Error consultando métricas de salud: %s", err)

        es_ok = base_ok and socket_ok and plataforma_ok

        ult_linea_iso = (
            cliente_socket.ultima_linea_at.strftime("%Y-%m-%dT%H:%M:%SZ")
            if cliente_socket.ultima_linea_at
            else None
        )

        return {
            "ok": es_ok,
            "servicio": "bot-discord",
            "version": "1.0.0",
            "socket": {
                "conectado": socket_ok,
                "ultima_linea": ult_linea_iso,
                "cursor": cliente_socket.cursor_actual,
            },
            "base": {"ok": base_ok},
            "plataforma": {
                "ok": plataforma_ok,
                "ultimo_ok": ultimo_ok_plataforma.strftime("%Y-%m-%dT%H:%M:%SZ"),
            },
            "cola": {"pendientes": pendientes, "mas_antiguo_s": mas_antiguo_s},
            "destinos_activos": destinos_activos,
        }

    servidor_salud = ServidorSalud(puerto=config.health_port, proveedor_estado=obtener_estado_salud)
    await servidor_salud.iniciar()

    # Bucle de espera hasta señal de parada
    loop = asyncio.get_running_loop()
    parada_evento = asyncio.Event()

    for sig in (signal.SIGINT, signal.SIGTERM):
        with contextlib.suppress(NotImplementedError):
            loop.add_signal_handler(sig, parada_evento.set)

    logger.info("Bot de Discord en ejecución y a la escucha.")
    await parada_evento.wait()

    logger.info("Señal de parada recibida. Deteniendo componentes de Discord...")
    await servidor_salud.detener()
    if tarea_discord:
        await cliente.close()
        tarea_discord.cancel()
        with contextlib.suppress(asyncio.CancelledError):
            await tarea_discord
    await retencion.detener()
    await cliente_socket.detener()
    await motor.detener()
    await catalogo.detener()
    await cliente_portal.cerrar()
    await gestor_base.cerrar()
    logger.info("Bot de Discord detenido limpiamente.")


def main() -> None:
    """Función de arranque CLI."""
    config = validar_o_salir(ConfiguracionBotDiscord)
    assert isinstance(config, ConfiguracionBotDiscord)
    asyncio.run(ejecutar_bot(config))


if __name__ == "__main__":
    main()
