"""Módulo de logging estructurado en formato JSON por línea a stdout."""

import json
import logging
import sys
from datetime import UTC, datetime
from typing import Any


class FormateadorJson(logging.Formatter):
    """Formateador que emite registros en formato JSON estructurado."""

    def __init__(self, servicio: str) -> None:
        """Inicializa el formateador con el nombre del servicio."""
        super().__init__()
        self.servicio = servicio

    def format(self, record: logging.LogRecord) -> str:
        """Serializa el registro de log en una línea JSON."""
        ahora = datetime.now(UTC).isoformat().replace("+00:00", "Z")

        # Estructura canónica según UT-08.1
        datos: dict[str, Any] = {
            "ts": ahora,
            "nivel": record.levelname,
            "servicio": self.servicio,
            "evento": record.getMessage(),
        }

        # Extraer campos adicionales pasados en extra o atributos personalizados
        campos_extra = getattr(record, "extra_campos", None)
        if isinstance(campos_extra, dict):
            for clave, valor in campos_extra.items():
                if clave not in datos:
                    # Enmascarar posibles secretos o contraseñas
                    if any(s in clave.lower() for s in ("token", "secreto", "password", "clave")):
                        datos[clave] = "***"
                    else:
                        datos[clave] = valor

        if record.exc_info:
            datos["error_detalle"] = self.formatException(record.exc_info)

        return json.dumps(datos, ensure_ascii=False)


def configurar_registro(servicio: str, nivel: str = "INFO") -> logging.Logger:
    """Configura el registrador raíz con el formateador JSON."""
    nivel_num = getattr(logging, nivel.upper(), logging.INFO)
    logger = logging.getLogger()
    logger.setLevel(nivel_num)

    # Eliminar manejadores previos para evitar duplicación
    for handler in list(logger.handlers):
        logger.removeHandler(handler)

    manejador = logging.StreamHandler(sys.stdout)
    manejador.setLevel(nivel_num)
    manejador.setFormatter(FormateadorJson(servicio))
    logger.addHandler(manejador)

    # Silenciar verbosidad excesiva de librerías externas
    for lib in ("aiogram", "aiohttp", "discord", "urllib3"):
        logging.getLogger(lib).setLevel(logging.WARNING)

    return logging.getLogger(servicio)
