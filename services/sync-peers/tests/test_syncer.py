"""Tests unitarios para la lógica de filtrado y contratos de sync-peers."""

from __future__ import annotations

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
