"""Tests unitarios para el gestor de peers en sync-peers."""

from __future__ import annotations

import json
from pathlib import Path

from src.peers import PeerConfig, PeerManager


def test_peer_config_from_dict_valid() -> None:
    """Verifica el parseo correcto de un diccionario válido."""
    raw = {
        "id": "malla-cadiz",
        "nombre": "Malla Cádiz",
        "url": "https://potato.cadizmesh.es/",
        "activo": True,
        "mensajes": True,
        "trazas": False,
        "nodos": True,
        "intervalo_nodos_s": 120,
    }
    peer = PeerConfig.from_dict(raw)
    assert peer is not None
    assert peer.id == "malla-cadiz"
    assert peer.nombre == "Malla Cádiz"
    assert peer.url == "https://potato.cadizmesh.es"
    assert peer.activo is True
    assert peer.trazas is False
    assert peer.intervalo_nodos_s == 120


def test_peer_config_from_dict_english_aliases() -> None:
    """Verifica la compatibilidad con claves heredadas en inglés."""
    raw = {
        "id": "malla-vecina-1",
        "name": "Malla Vecina 1",
        "url": "https://potato.ejemplo.org",
        "enabled": True,
        "sync_messages": True,
        "sync_traces": True,
        "sync_nodes": True,
        "nodes_interval_seconds": 240,
    }
    peer = PeerConfig.from_dict(raw)
    assert peer is not None
    assert peer.id == "malla-vecina-1"
    assert peer.nombre == "Malla Vecina 1"
    assert peer.activo is True
    assert peer.mensajes is True
    assert peer.trazas is True
    assert peer.nodos is True
    assert peer.intervalo_nodos_s == 240


def test_peer_config_invalid_id() -> None:
    """Verifica que IDs con caracteres inválidos sean rechazados."""
    assert PeerConfig.from_dict({"id": "malla cadiz!", "url": "https://test.es"}) is None
    assert PeerConfig.from_dict({"id": "malla_cadiz", "url": "https://test.es"}) is None  # guión bajo no permitido
    assert PeerConfig.from_dict({"id": "", "url": "https://test.es"}) is None


def test_peer_config_invalid_url() -> None:
    """Verifica que URLs sin http/https sean rechazadas."""
    assert PeerConfig.from_dict({"id": "malla1", "url": "ftp://test.es"}) is None
    assert PeerConfig.from_dict({"id": "malla1", "url": "potato.test.es"}) is None


def test_peer_manager_reload(tmp_path: Path) -> None:
    """Verifica la carga, filtrado de duplicados y recarga en caliente de peers.json."""
    peers_file = tmp_path / "peers.json"
    data = [
        {"id": "peer-1", "nombre": "Peer 1", "url": "https://p1.org"},
        {"id": "peer-1", "nombre": "Peer 1 Duplicado", "url": "https://p1-dup.org"},
        {"id": "peer-2", "nombre": "Peer 2", "url": "https://p2.org", "activo": False},
    ]
    peers_file.write_text(json.dumps(data), encoding="utf-8")

    manager = PeerManager(str(peers_file))
    peers = manager.reload()

    assert len(peers) == 2
    assert "peer-1" in peers
    assert "peer-2" in peers
    assert peers["peer-1"].url == "https://p1.org"
    assert peers["peer-2"].activo is False


def test_peer_manager_json_syntax_error(tmp_path: Path) -> None:
    """Verifica que un JSON roto mantenga la configuración previa sin romper el servicio."""
    peers_file = tmp_path / "peers.json"
    valid_data = [{"id": "peer-valido", "nombre": "Valido", "url": "https://valido.org"}]
    peers_file.write_text(json.dumps(valid_data), encoding="utf-8")

    manager = PeerManager(str(peers_file))
    peers = manager.reload()
    assert len(peers) == 1

    # Corromper el fichero con sintaxis errónea
    peers_file.write_text("{json roto", encoding="utf-8")
    reloaded_peers = manager.reload()

    # Mantiene la configuración previa
    assert len(reloaded_peers) == 1
    assert "peer-valido" in reloaded_peers
