"""Adaptador de formato neutro a Discord Embeds con colores de tokens de diseño."""

import re
from typing import Any

import discord.utils

from nucleo.formato import AvisoNeutro

# Enteros RGB de los tokens de DESIGN.md en modo oscuro
COLORES_DISCORD: dict[str, int] = {
    "critico": 0xFFB4AE,
    "aviso": 0xFFD48A,
    "info": 0xA9C9FF,
    "correcto": 0x9CF1BA,
}

REGEX_ID_NODO = re.compile(r"(![0-9a-f]{8})")


class AdaptadorFormatoDiscord:
    """Serializa avisos neutros a embeds de Discord."""

    def __init__(self, project_name: str) -> None:
        """Inicializa el adaptador con el nombre del proyecto."""
        self.project_name = project_name

    def serializar_contenido(self, aviso: AvisoNeutro) -> dict[str, Any]:
        """Genera el diccionario de contenido para un mensaje con Embed en Discord."""
        color_rgb = COLORES_DISCORD.get(aviso.color_token, COLORES_DISCORD["info"])

        lineas_procesadas: list[str] = []
        for linea in aviso.lineas:
            # Escapar caracteres especiales de markdown en texto general
            linea_esc = discord.utils.escape_markdown(linea, ignore_links=True)
            # Envolver id de nodo entre backticks
            linea_formateada = REGEX_ID_NODO.sub(r"`\1`", linea_esc)
            lineas_procesadas.append(linea_formateada)

        descripcion = "\n".join(lineas_procesadas)

        embed_dict: dict[str, Any] = {
            "title": aviso.titulo,
            "url": aviso.enlace,
            "description": descripcion,
            "color": color_rgb,
            "timestamp": aviso.fecha.strftime("%Y-%m-%dT%H:%M:%SZ"),
            "footer": {"text": self.project_name},
        }

        return {
            "embeds": [embed_dict],
            "allowed_mentions": {"parse": []},
        }
