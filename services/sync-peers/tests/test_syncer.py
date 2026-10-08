"""Tests unitarios para la lógica de filtrado y contratos de sync-peers."""

from __future__ import annotations

from typing import Any
import pytest

from src.config import Config


def test_config_user_agent_format() -> None:
    """Verifica el formato canónico de cortesía de la cabecera User-Agent."""
    cfg = Config(
        project_name="Andalucía Mesh",
        project_domain="mesh.desdechipiona.es",
        project_contact="public@raupulus.dev",
    )
    expected = "Andalucía Mesh-PeerSync/1.0 (+https://mesh.desdechipiona.es; public@raupulus.dev)"
    assert cfg.user_agent == expected


def test_channel_index_mapping() -> None:
    """Verifica que el canal primario reciba índice 0 y el resto índices estables."""
    cfg = Config(
        allowed_channels_raw="SFNarrow,Cadiz,Sevilla",
        primary_channel="SFNarrow",
    )
    res_prim = cfg.get_channel_index_and_name("SFNarrow")
    assert res_prim is not None
    assert res_prim[0] == 0
    assert res_prim[1] == "SFNarrow"

    # Con acentos: 'Cádiz' mapea a 'Cadiz'
    res_cadiz = cfg.get_channel_index_and_name("Cádiz")
    assert res_cadiz is not None
    assert res_cadiz[0] == 1
    assert res_cadiz[1] == "Cadiz"

    # Canal no permitido
    assert cfg.get_channel_index_and_name("Madrid") is None


@pytest.mark.asyncio
async def test_health_server_deactivated_peer(monkeypatch: pytest.MonkeyPatch) -> None:
    """Verifica que un peer desactivado se marque con estado 'desactivado' y ok False."""
    from unittest.mock import AsyncMock, MagicMock
    from src.health import HealthServer
    from src.peers import PeerConfig

    mock_pool = MagicMock()
    mock_syncer = MagicMock()
    mock_syncer.potatomesh_status = "ok"

    # PeerConfig en el gestor: malla-1 activa, malla-2 desactivada
    p1 = PeerConfig("malla-1", "Malla 1", "https://potato1.example.org", activo=True)
    p2 = PeerConfig("malla-2", "Malla 2", "https://potato2.example.org", activo=False)
    mock_syncer.peer_manager._current_peers = {"malla-1": p1, "malla-2": p2}

    # Simular get_all_peer_states
    async def mock_get_states(pool: Any) -> list[dict[str, Any]]:
        return [
            {"id": "malla-1", "estado": "ok", "ultimo_ok": "2026-10-08T00:00:00Z", "fallos_consecutivos": 0},
            {"id": "malla-2", "estado": "caido", "ultimo_ok": None, "fallos_consecutivos": 5},
        ]

    monkeypatch.setattr("src.health.get_all_peer_states", mock_get_states)

    server = HealthServer(port=8080, pool=mock_pool, syncer=mock_syncer)
    mock_request = MagicMock()
    resp = await server.handle_health(mock_request)
    assert resp.status == 200

    import json
    data = json.loads(resp.text)
    assert data["ok"] is True
    assert len(data["peers"]) == 2

    peer_map = {p["id"]: p for p in data["peers"]}
    assert peer_map["malla-1"]["estado"] == "ok"
    assert peer_map["malla-1"]["activo"] is True
    assert peer_map["malla-1"]["ok"] is True

    assert peer_map["malla-2"]["estado"] == "desactivado"
    assert peer_map["malla-2"]["activo"] is False
    assert peer_map["malla-2"]["ok"] is False


@pytest.mark.asyncio
async def test_sync_nodes_formats_dict() -> None:
    """Verifica que _sync_nodes transforme una lista de nodos en un diccionario para PotatoMesh."""
    from unittest.mock import AsyncMock, MagicMock
    from src.syncer import PeerSyncer
    from src.peers import PeerConfig

    cfg = Config()
    mock_pool = MagicMock()
    mock_peer_manager = MagicMock()
    mock_publisher = MagicMock()
    syncer = PeerSyncer(cfg, mock_pool, mock_peer_manager, mock_publisher)

    # Simular _fetch_json devolviendo una lista de nodos
    node_list = [
        {"node_id": "!11111111", "short_name": "N1"},
        {"node_id": "!22222222", "short_name": "N2"},
    ]
    syncer._fetch_json = AsyncMock(return_value=node_list)
    syncer._post_local_potatomesh = AsyncMock()
    syncer.mqtt_publisher = MagicMock()
    syncer.mqtt_publisher.publish_event = AsyncMock()

    peer = PeerConfig("malla-remota", "Malla Remota", "https://potato.example.org", activo=True)
    mock_session = MagicMock()

    # Parchear update_cursor_nodes
    with pytest.MonkeyPatch.context() as mp:
        mp.setattr("src.syncer.update_cursor_nodes", AsyncMock())
        await syncer._sync_nodes(peer, mock_session, {}, {})

    # Verificar que _post_local_potatomesh recibió un diccionario
    assert syncer._post_local_potatomesh.call_count == 1
    call_args = syncer._post_local_potatomesh.call_args
    assert call_args[0][1] == "nodes"
    posted_payload = call_args[0][2]
    assert isinstance(posted_payload, dict)
    assert "!11111111" in posted_payload
    assert "!22222222" in posted_payload
    assert posted_payload["!11111111"]["short_name"] == "N1"
