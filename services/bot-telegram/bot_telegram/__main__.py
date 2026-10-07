"""Punto de entrada principal del bot de Telegram."""

import asyncio
import contextlib
import signal
from datetime import UTC, datetime
from pathlib import Path
from typing import Any

from aiogram import Bot, Dispatcher

from bot_telegram.adaptador_envio import AdaptadorEnvioTelegram
from bot_telegram.cliente import configurar_cliente_telegram
from bot_telegram.configuracion import ConfiguracionBotTelegram
from bot_telegram.manejadores import ManejadoresTelegram
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

ID_BLOQUEO_BOT_TELEGRAM = 10810


async def ejecutar_bot(config: ConfiguracionBotTelegram) -> None:
    """Orquesta todos los componentes del bot de Telegram."""
    logger = configurar_registro("bot-telegram", config.log_level)
    logger.info("Iniciando Bot de Telegram para Andalucía Mesh...")

    # Base de datos y migraciones
    gestor_base = GestorBase(config, "bot-telegram")
    await gestor_base.conectar()

    dir_migraciones = Path(__file__).resolve().parents[1] / "migrations"
    migradas = await gestor_base.aplicar_migraciones(dir_migraciones)
    logger.info("Migraciones verificadas (%d aplicadas).", migradas)

    # Bloqueo consultivo de instancia única
    await gestor_base.adquirir_bloqueo_instancia(ID_BLOQUEO_BOT_TELEGRAM)

    # API del portal, catálogo y comandos
    cliente_portal = ClientePortal(config, "bot-telegram")
    await cliente_portal.iniciar()

    catalogo = GestorCatalogo(cliente_portal)
    await catalogo.iniciar()

    comandos = GestorComandos(config, cliente_portal, catalogo, gestor_base)

    # Verificar si el token es una plantilla o está deshabilitado
    token_configurado = (
        bool(config.telegram_bot_token)
        and not config.telegram_bot_token.startswith("cambiar_por_")
        and config.telegram_bot_token != "desactivado"
        and ":" in config.telegram_bot_token
    )

    # Cliente Bot aiogram
    bot: Bot | None = None
    dp: Dispatcher | None = None
    tarea_polling: asyncio.Task[None] | None = None

    if token_configurado:
        bot = Bot(token=config.telegram_bot_token)
        bot_username = await configurar_cliente_telegram(bot, config)

        # Motor de envíos y adaptador
        adaptador_envio = AdaptadorEnvioTelegram(bot)
        motor = MotorEnvios(config, gestor_base, catalogo, adaptador_envio)
        await motor.iniciar()

        # Manejadores de aiogram y Dispatcher
        manejadores = ManejadoresTelegram(bot, config, gestor_base, comandos, bot_username)
        dp = Dispatcher()
        dp.include_router(manejadores.router)

        # Tarea en segundo plano para long polling
        tarea_polling = asyncio.create_task(
            dp.start_polling(
                bot,
                allowed_updates=["message", "channel_post", "my_chat_member"],
            )
        )
    else:
        logger.warning(
            "TELEGRAM_BOT_TOKEN tiene un valor de plantilla ('%s'). "
            "El servicio permanece activo con servidor de salud y socket a la espera de credenciales.",
            config.telegram_bot_token,
        )
        # Adaptador dummy sin bot real
        bot = Bot(token="123456789:AAABBBCCCDDDEEEFFFGGGHHHIIIJJJKKKLL")
        adaptador_envio = AdaptadorEnvioTelegram(bot)
        motor = MotorEnvios(config, gestor_base, catalogo, adaptador_envio)
        await motor.iniciar()

    # Cliente del socket UNIX
    cliente_socket = ClienteSocketAlertas(
        ruta_socket=config.alertas_socket,
        nombre_cliente="bot-telegram",
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

    # Servidor de salud HTTP en 8080
    ultimo_ok_plataforma: datetime = datetime.now(UTC)

    async def obtener_estado_salud() -> dict[str, Any]:
        nonlocal ultimo_ok_plataforma
        base_ok = await gestor_base.comprobar_salud()
        socket_ok = cliente_socket.conectado
        plataforma_ok = bool(tarea_polling and not tarea_polling.done())

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

        # 503 si la base falla, socket > 120s o plataforma parada
        es_ok = base_ok and socket_ok and plataforma_ok

        ult_linea_iso = (
            cliente_socket.ultima_linea_at.strftime("%Y-%m-%dT%H:%M:%SZ")
            if cliente_socket.ultima_linea_at
            else None
        )

        return {
            "ok": es_ok,
            "servicio": "bot-telegram",
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

    logger.info("Bot de Telegram en ejecución y a la escucha.")
    await parada_evento.wait()

    logger.info("Señal de parada recibida. Deteniendo componentes de Telegram...")
    await servidor_salud.detener()
    if dp and bot and tarea_polling:
        await dp.stop_polling()
        tarea_polling.cancel()
        with contextlib.suppress(asyncio.CancelledError):
            await tarea_polling
        await bot.session.close()
    await retencion.detener()
    await cliente_socket.detener()
    await motor.detener()
    await catalogo.detener()
    await cliente_portal.cerrar()
    await gestor_base.cerrar()
    logger.info("Bot de Telegram detenido limpiamente.")


def main() -> None:
    """Función de arranque CLI."""
    config = validar_o_salir(ConfiguracionBotTelegram)
    assert isinstance(config, ConfiguracionBotTelegram)
    asyncio.run(ejecutar_bot(config))


if __name__ == "__main__":
    main()
