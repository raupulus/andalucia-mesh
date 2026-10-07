"""Punto de entrada principal para el microservicio adaptador-potato.

Coordina la ejecución de las tareas asíncronas de recepción MQTT,
despacho por lotes a PotatoMesh y servidor HTTP de salud.
"""

from __future__ import annotations

import asyncio
import logging
import signal
import sys

from .config import Config
from .dedup import Deduplicator
from .health import HealthServer
from .mqtt import MqttProcessor
from .sender import PotatoSender


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
    logger = logging.getLogger("adaptador-potato")
    logger.info("Iniciando adaptador-potato (Andalucía Mesh)...")

    # Validar credenciales y tokens requeridos
    if not config.mqtt_password:
        logger.error("Variable de entorno MQTT_PASSWORD no configurada.")
        return 1
    if not config.potatomesh_api_token:
        logger.error("Variable de entorno POTATOMESH_API_TOKEN no configurada.")
        return 1

    dedup = Deduplicator(
        window_seconds=float(config.dedup_minutos * 60), capacity=200_000
    )
    sender = PotatoSender(
        base_url=config.potatomesh_url,
        api_token=config.potatomesh_api_token,
        cola_max=config.cola_max,
        lote_max=config.lote_max,
        lote_segundos=config.lote_segundos,
    )
    mqtt_proc = MqttProcessor(config=config, dedup=dedup, sender=sender)
    health_server = HealthServer(
        port=config.health_port, mqtt_proc=mqtt_proc, sender=sender
    )

    stop_event = asyncio.Event()
    loop = asyncio.get_running_loop()

    def handle_signal() -> None:
        logger.info("Señal de apagado recibida. Iniciando parada limpia...")
        stop_event.set()

    for sig in (signal.SIGINT, signal.SIGTERM):
        try:
            loop.add_signal_handler(sig, handle_signal)
        except NotImplementedError:
            # En entornos específicos donde add_signal_handler no está disponible
            pass

    # Iniciar servidor de salud
    await health_server.start()
    logger.info("Servidor de salud escuchando en puerto %d", config.health_port)

    # Iniciar despachador y receptor MQTT
    sender_task = asyncio.create_task(sender.run())
    mqtt_task = asyncio.create_task(mqtt_proc.run())

    await stop_event.wait()

    # Cancelar tareas y detener limpiamente
    sender.stop()
    sender_task.cancel()
    mqtt_task.cancel()

    await asyncio.gather(sender_task, mqtt_task, return_exceptions=True)
    await health_server.stop()

    logger.info("adaptador-potato detenido exitosamente.")
    return 0


def main() -> None:
    """Punto de entrada de consola."""
    sys.exit(asyncio.run(main_async()))


if __name__ == "__main__":
    main()
