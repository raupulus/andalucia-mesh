"""Adaptador de formato neutro a HTML seguro para la Bot API de Telegram."""

import html
import re
from typing import Any

from nucleo.formato import AvisoNeutro

REGEX_ID_NODO = re.compile(r"(![0-9a-f]{8})")


class AdaptadorFormatoTelegram:
    """Serializa avisos neutros al formato HTML esperado por Telegram."""

    @staticmethod
    def serializar_contenido(aviso: AvisoNeutro) -> dict[str, Any]:
        """Genera el diccionario de contenido para sendMessage en Telegram."""
        partes_texto: list[str] = [f"<b>{html.escape(aviso.titulo)}</b>"]

        for linea in aviso.lineas:
            # Escapar primero todo el texto de la línea
            linea_escapada = html.escape(linea)
            # Envolver identificadores de nodo en <code> para facilitar copia
            linea_con_code = REGEX_ID_NODO.sub(r"<code>\1</code>", linea_escapada)
            partes_texto.append(linea_con_code)

        if aviso.enlace:
            partes_texto.append(aviso.enlace)

        texto_final = "\n".join(partes_texto)

        return {
            "text": texto_final,
            "parse_mode": "HTML",
            "disable_notification": aviso.es_silencioso,
            "link_preview_options": {"is_disabled": True},
        }
