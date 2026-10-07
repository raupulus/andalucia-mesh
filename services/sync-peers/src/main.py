"""Punto de entrada principal para el microservicio sync-peers.

Coordina la conexión a PostgreSQL (peersync), Mosquitto y el bucle
asíncrono de sincronización de mallas vecinas.
"""

from __future__ import annotations

import asyncio
import logging
import signal
import sys

import asyncpg

from .config import Config
from .db import init_db
from .health import HealthServer
from .mqtt import MqttPublisher
from .peers import PeerManager
from .syncer import PeerSyncer


def setup_logging(log_level: str) -> None:
    """Configura el formato estándar de registro de eventos."""
    level = getattr(logging, log_level.upper(), logging.INFO)
    logging.basicConfig(
        level=level,
        format="%(asctime)s [%(levelname)s] [%(name)s] %(message)s",
        datefmt="%Y-%m-%d %H:%M:%S",
    )


async def main_async() -> int:
    """Función principal asíncrona."""
    config = Config()
    setup_logging(config.log_level)
    logger = logging.getLogger("sync-peers")
    logger.info("Iniciando sync-peers (Andalucía Mesh)...")

    # Validar credenciales obligatorias
    if not config.db_password:
        logger.error("Variable de entorno DB_PASSWORD no configurada.")
        return 1
    if not config.mqtt_password:
        logger.error("Variable de entorno MQTT_PASSWORD no configurada.")
        return 1
    if not config.potatomesh_api_token:
        logger.error("Variable de entorno POTATOMESH_API_TOKEN no configurada.")
        return 1

    # Crear pool de base de datos PostgreSQL
    logger.info(
        "Conectando a base de datos PostgreSQL en %s:%d/%s...",
        config.db_host,
        config.db_port,
        config.db_name,
    )
    try:
        pool = await asyncpg.create_pool(
            host=config.db_host,
            port=config.db_port,
            user=config.db_user,
            password=config.db_password,
            database=config.db_name,
            min_size=2,
            max_size=10,
        )
    except Exception as exc:
        logger.critical("No se pudo conectar a PostgreSQL: %s", exc)
        return 1

    # Aplicar migraciones
    await init_db(pool)

    # Iniciar componentes
    peer_manager = PeerManager(config.peers_archivo)
    mqtt_publisher = MqttPublisher(config)
    await mqtt_publisher.connect()

    syncer = PeerSyncer(
        config=config,
        pool=pool,
        peer_manager=peer_manager,
        mqtt_publisher=mqtt_publisher,
    )

    health_server = HealthServer(
        port=config.health_port,
        pool=pool,
        syncer=syncer,
    )
    await health_server.start()
    logger.info("Servidor de salud escuchando en puerto %d", config.health_port)

    syncer_task = asyncio.create_task(syncer.run())

    stop_event = asyncio.Event()
    loop = asyncio.get_running_loop()

    def handle_signal() -> None:
        logger.info("Señal de apagado recibida. Deteniendo sync-peers...")
        stop_event.set()

    for sig in (signal.SIGINT, signal.SIGTERM):
        try:
            loop.add_signal_handler(sig, handle_signal)
        except NotImplementedError:
            pass

    await stop_event.wait()

    syncer.stop()
    syncer_task.cancel()
    await asyncio.gather(syncer_task, return_exceptions=True)

    await health_server.stop()
    await mqtt_publisher.close()
    await pool.close()

    logger.info("sync-peers detenido exitosamente.")
    return 0


def main() -> None:
    """Punto de entrada de consola."""
    sys.exit(asyncio.run(main_async()))


if __name__ == "__main__":
    main()
