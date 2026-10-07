"""Gestor y validador de configuración de instancias vecinas (peers.json).

Permite la recarga en caliente en cada ciclo sin reiniciar el servicio,
validando la unicidad de IDs y el formato de las URLs.
"""

from __future__ import annotations

import json
import logging
import re
from dataclasses import dataclass
from pathlib import Path

logger = logging.getLogger("sync-peers.peers")

ID_REGEX = re.compile(r"^[a-z0-9-]+$")


@dataclass(frozen=True)
class PeerConfig:
    """Configuración de una instancia PotatoMesh vecina."""

    id: str
    nombre: str
    url: str
    activo: bool = True
    mensajes: bool = True
    trazas: bool = True
    nodos: bool = True
    intervalo_nodos_s: int = 180

    @classmethod
    def from_dict(cls, data: dict[str, object]) -> PeerConfig | None:
        """Parsea y valida un diccionario con la definición de un peer.

        Args:
            data: Diccionario con campos JSON.

        Returns:
            Instancia PeerConfig validada o None si es inválida.
        """
        raw_id = str(data.get("id", "")).strip().lower()
        if not ID_REGEX.match(raw_id):
            logger.warning("ID de peer inválido (debe cumplir ^[a-z0-9-]+$): '%s'", raw_id)
            return None

        raw_url = str(data.get("url", "")).strip().rstrip("/")
        if not (raw_url.startswith("http://") or raw_url.startswith("https://")):
            logger.warning("URL de peer inválida (debe empezar por http/https): '%s'", raw_url)
            return None

        nombre = str(data.get("nombre", raw_id)).strip()
        activo = bool(data.get("activo", True))
        mensajes = bool(data.get("mensajes", True))
        trazas = bool(data.get("trazas", True))
        nodos = bool(data.get("nodos", True))

        try:
            intervalo_nodos = int(data.get("intervalo_nodos_s", 180))
            if intervalo_nodos < 10:
                intervalo_nodos = 10
        except (ValueError, TypeError):
            intervalo_nodos = 180

        return cls(
            id=raw_id,
            nombre=nombre,
            url=raw_url,
            activo=activo,
            mensajes=mensajes,
            trazas=trazas,
            nodos=nodos,
            intervalo_nodos_s=intervalo_nodos,
        )


class PeerManager:
    """Administra la carga y recarga en caliente de peers.json."""

    def __init__(self, filepath: str) -> None:
        """Inicializa el gestor de peers.

        Args:
            filepath: Ruta al fichero peers.json.
        """
        self.filepath = Path(filepath)
        self._current_peers: dict[str, PeerConfig] = {}
        self._last_error: str | None = None

    def reload(self) -> dict[str, PeerConfig]:
        """Recarga el fichero peers.json si existe y valida su contenido.

        Si el fichero tiene errores de sintaxis JSON, mantiene la última configuración
        válida y registra una advertencia.

        Returns:
            Diccionario de {peer_id: PeerConfig}.
        """
        if not self.filepath.exists():
            if self._last_error != "not_found":
                logger.warning("Fichero de peers no encontrado en %s", self.filepath)
                self._last_error = "not_found"
            return self._current_peers

        try:
            with open(self.filepath, "r", encoding="utf-8") as f:
                content = f.read().strip()
                if not content:
                    return {}
                data = json.loads(content)

            if not isinstance(data, list):
                logger.warning("Formato inválido en %s: se esperaba una lista JSON.", self.filepath)
                return self._current_peers

            new_peers: dict[str, PeerConfig] = {}
            for item in data:
                if not isinstance(item, dict):
                    continue
                peer = PeerConfig.from_dict(item)
                if peer:
                    if peer.id in new_peers:
                        logger.warning("ID de peer duplicado ignorado: '%s'", peer.id)
                        continue
                    new_peers[peer.id] = peer

            self._current_peers = new_peers
            self._last_error = None
            return self._current_peers

        except json.JSONDecodeError as exc:
            err_msg = f"json_error: {exc}"
            if self._last_error != err_msg:
                logger.warning("Error de sintaxis en %s (%s). Conservando configuración anterior.", self.filepath, exc)
                self._last_error = err_msg
            return self._current_peers
        except Exception as exc:
            logger.error("Error inesperado leyendo %s: %s", self.filepath, exc)
            return self._current_peers
