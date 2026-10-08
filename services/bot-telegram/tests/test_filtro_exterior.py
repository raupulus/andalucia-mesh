"""Pruebas unitarias para la detección y filtrado de nodos exteriores (fuera de Andalucía)."""

from typing import Any
from unittest.mock import AsyncMock, MagicMock

import pytest

from nucleo.comandos import GestorComandos
from nucleo.config import ConfiguracionBots
from nucleo.formato import PROVINCIAS_ANDALUCIA, es_nodo_exterior


def test_provincias_andalucia_completas() -> None:
    """Verifica que las 8 provincias andaluzas canónicas están presentes."""
    assert len(PROVINCIAS_ANDALUCIA) == 8
    assert "ES-AL" in PROVINCIAS_ANDALUCIA
    assert "ES-CA" in PROVINCIAS_ANDALUCIA
    assert "ES-CO" in PROVINCIAS_ANDALUCIA
    assert "ES-GR" in PROVINCIAS_ANDALUCIA
    assert "ES-H" in PROVINCIAS_ANDALUCIA
    assert "ES-J" in PROVINCIAS_ANDALUCIA
    assert "ES-MA" in PROVINCIAS_ANDALUCIA
    assert "ES-SE" in PROVINCIAS_ANDALUCIA


def test_es_nodo_exterior() -> None:
    """Comprueba la correcta clasificación de nodos interiores vs exteriores."""
    # Nodos en Andalucía
    assert not es_nodo_exterior("!cadiz1", {"provincia": "ES-CA"})
    assert not es_nodo_exterior("!sevilla1", {"provincia": "ES-SE"})
    assert not es_nodo_exterior("!malaga1", {"provincia": "es-ma"})  # insensible a mayúsculas

    # Nodos fuera de Andalucía
    assert es_nodo_exterior("!fuera1", {"provincia": "FUERA"})
    assert es_nodo_exterior("!fuera2", {"provincia": "fuera"})
    assert es_nodo_exterior("!madrid1", {"provincia": "ES-M"})
    assert es_nodo_exterior("!albacete1", {"provincia": "ES-AB"})

    # Casos especiales
    assert not es_nodo_exterior("all", {"provincia": "FUERA"})  # evento global de malla
    assert not es_nodo_exterior("!desconocido", None)
    assert not es_nodo_exterior("!sin_provincia", {})
    assert not es_nodo_exterior("!sin_gps", {"provincia": None})


@pytest.mark.asyncio
async def test_comandos_exterior_permisos() -> None:
    """Verifica que solo los administradores puedan alterar el filtro de exterior."""
    config = ConfiguracionBots(
        db_name="test",
        db_user="test",
        db_password="password",
    )
    gestor_base = MagicMock()
    catalogo = MagicMock()
    api_portal = MagicMock()

    comandos = GestorComandos(config, gestor_base, catalogo, api_portal)

    # Usuario no administrador
    resp_enable = await comandos.ejecutar_enable_exterior(12345, es_admin=False)
    assert "Solo los administradores" in resp_enable

    resp_disable = await comandos.ejecutar_disable_exterior(12345, es_admin=False)
    assert "Solo los administradores" in resp_disable
