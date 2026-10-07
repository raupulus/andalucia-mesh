"""Pruebas unitarias del clasificador de riesgo y tipo."""

import pytest

from detector.config import Settings
from detector.motor.clasificador import Clasificador


@pytest.fixture
def clasificador() -> Clasificador:
    """Fixture que inicializa el clasificador desde clasificacion.yaml."""
    settings = Settings()
    path = settings.resolve_clasificacion_path()
    return Clasificador.from_yaml(path, infra_roles=settings.parsed_infra_roles)


def test_clasificacion_infraestructura_por_afecta_malla(clasificador: Clasificador) -> None:
    """Una alerta con afecta_malla=True siempre se clasifica como infraestructura."""
    riesgo, tipo = clasificador.clasificar(
        riesgo_propuesto="alto",
        nodo_id="!12345678",
        afecta_malla=True,
        rol="CLIENT",
        tipo_propuesto="clientes",
    )
    assert riesgo == "alto"
    assert tipo == "infraestructura"


def test_clasificacion_infraestructura_por_all(clasificador: Clasificador) -> None:
    """Una alerta con nodo='all' siempre es de infraestructura."""
    riesgo, tipo = clasificador.clasificar(
        riesgo_propuesto="medio",
        nodo_id="all",
        rol="CLIENT",
    )
    assert tipo == "infraestructura"


def test_clasificacion_infraestructura_por_rol(clasificador: Clasificador) -> None:
    """Un nodo con rol ROUTER o REPEATER siempre es clasificado como infraestructura."""
    _, tipo_router = clasificador.clasificar(
        riesgo_propuesto="alto",
        nodo_id="!a1b2c3d4",
        rol="ROUTER",
    )
    assert tipo_router == "infraestructura"

    _, tipo_repeater = clasificador.clasificar(
        riesgo_propuesto="medio",
        nodo_id="!a1b2c3d4",
        rol="REPEATER",
    )
    assert tipo_repeater == "infraestructura"


def test_clasificacion_infraestructura_por_gateway(clasificador: Clasificador) -> None:
    """Un gateway conocido con rol de usuario es infraestructura."""
    _, tipo = clasificador.clasificar(
        riesgo_propuesto="medio",
        nodo_id="!gw010101",
        rol="CLIENT",
        is_gateway=True,
    )
    assert tipo == "infraestructura"


def test_clasificacion_clientes_default(clasificador: Clasificador) -> None:
    """Un nodo cliente normal sin banderas de infraestructura se clasifica como clientes."""
    _, tipo = clasificador.clasificar(
        riesgo_propuesto="bajo",
        nodo_id="!user1234",
        rol="CLIENT",
    )
    assert tipo == "clientes"

    _, tipo_mute = clasificador.clasificar(
        riesgo_propuesto="bajo",
        nodo_id="!mute1234",
        rol="CLIENT_MUTE",
    )
    assert tipo_mute == "clientes"


def test_clasificacion_riesgo_invalido(clasificador: Clasificador) -> None:
    """Un riesgo no registrado en clasificacion.yaml lanza ValueError."""
    with pytest.raises(ValueError, match="Riesgo desconocido"):
        clasificador.clasificar(
            riesgo_propuesto="catastrofico",
            nodo_id="!a1b2c3d4",
        )
