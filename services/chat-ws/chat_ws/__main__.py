"""Punto de entrada principal para la ejecución de snm-chat-ws."""

import asyncio
import contextlib
import logging
import signal
import sys

from chat_ws.config import get_settings
from chat_ws.server import ChatServer


def setup_logging(log_level: str) -> None:
    """Configura el formato y nivel del registro de eventos del sistema.

    Args:
        log_level: Nivel de registro ('DEBUG', 'INFO', 'WARNING', 'ERROR').
    """
    numeric_level = getattr(logging, log_level.upper(), logging.INFO)
    logging.basicConfig(
        level=numeric_level,
        format="%(asctime)s [%(levelname)s] %(name)s: %(message)s",
        datefmt="%Y-%m-%d %H:%M:%S",
        stream=sys.stdout,
        force=True,
    )


def main() -> None:
    """Función de arranque del microservicio."""
    settings = get_settings()
    setup_logging(settings.LOG_LEVEL)

    logger = logging.getLogger("chat_ws")
    logger.info("Iniciando microservicio chat-ws (versión 0.1.0)...")

    server = ChatServer(settings)
    loop = asyncio.new_event_loop()
    asyncio.set_event_loop(loop)

    for sig in (signal.SIGINT, signal.SIGTERM):
        with contextlib.suppress(NotImplementedError):
            loop.add_signal_handler(sig, server.stop)

    try:
        loop.run_until_complete(server.run())
    except (KeyboardInterrupt, SystemExit):
        server.stop()
    finally:
        loop.close()


if __name__ == "__main__":
    main()
