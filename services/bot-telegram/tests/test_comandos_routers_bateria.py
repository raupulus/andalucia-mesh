"""Pruebas unitarias para el formateo estético y exclusión de nodos exteriores en /battery y /routers."""

from typing import Any
from unittest.mock import AsyncMock, MagicMock

import pytest

from nucleo.comandos import GestorComandos
from nucleo.config import ConfiguracionBots


def _crear_gestor_comandos(items_routers: list[dict[str, Any]] | None = None) -> GestorComandos:
    """Crea una instancia de GestorComandos con portal simulado que devuelve los items indicados."""
    config = ConfiguracionBots(
        db_name="test",
        db_user="test",
        db_password="password",
        tz="Europe/Madrid",
    )
    cliente_portal = MagicMock()
    cliente_portal.obtener_routers = AsyncMock(return_value={"items": items_routers or []})
    catalogo = MagicMock()
    gestor_base = MagicMock()

    return GestorComandos(config, cliente_portal, catalogo, gestor_base)


@pytest.mark.asyncio
async def test_battery_agrupado_por_provincia_y_formato_bonito() -> None:
    """Verifica que /battery agrupe por provincia, ordene por nivel de batería y muestre resumen."""
    items = [
        # Cádiz
        {
            "id": "!cad0001",
            "short": "CAD1",
            "role": "ROUTER",
            "province": "ES-CA",
            "battery": {"level": 18, "voltage": 3.52, "powered": False, "at": "2026-10-08T02:30:00Z"},
            "last_seen": "2026-10-08T02:30:00Z",
        },
        {
            "id": "!cad0002",
            "short": "CAD2",
            "role": "ROUTER",
            "province": "ES-CA",
            "battery": {"level": 85, "voltage": 4.10, "powered": False, "at": "2026-10-08T02:40:00Z"},
            "last_seen": "2026-10-08T02:40:00Z",
        },
        {
            "id": "!cad0003",
            "short": "CAD3",
            "role": "ROUTER",
            "province": "ES-CA",
            "battery": {"level": None, "voltage": None, "powered": True, "at": "2026-10-08T02:49:00Z"},
            "last_seen": "2026-10-08T02:49:00Z",
        },
        # Sevilla
        {
            "id": "!sev0001",
            "short": "SEV1",
            "role": "ROUTER",
            "province": "ES-SE",
            "battery": {"level": 34, "voltage": 3.71, "powered": False, "at": "2026-10-08T00:50:00Z"},
            "last_seen": "2026-10-08T00:50:00Z",
        },
        {
            "id": "!sev0002",
            "short": "SEV2",
            "role": "ROUTER",
            "province": "ES-SE",
            "battery": {"level": None, "voltage": None, "powered": False, "at": "2026-10-07T02:50:00Z"},
            "last_seen": "2026-10-07T02:50:00Z",
        },
    ]

    comandos = _crear_gestor_comandos(items)
    resultado = await comandos.ejecutar_battery()

    # Cabecera y resumen
    assert "🔋 Batería de routers · Andalucía (5 activos)" in resultado
    assert "🔴 1 crítico · 🟠 1 bajo · 🟢 1 normal · 🔌 1 alimentado · ⚪ 1 sin datos" in resultado

    # Secciones provinciales
    assert "📍 Cádiz (3)" in resultado
    assert "📍 Sevilla (2)" in resultado

    # Cádiz: CAD1 (18%) debe aparecer antes que CAD2 (85%) y CAD3 (Alimentado)
    idx_cad1 = resultado.index("• 🔴 CAD1 · 18 % (3,5 V)")
    idx_cad2 = resultado.index("• 🟢 CAD2 · 85 % (4,1 V)")
    idx_cad3 = resultado.index("• 🔌 CAD3 · Alimentado")
    assert idx_cad1 < idx_cad2 < idx_cad3

    # Sevilla
    assert "• 🟠 SEV1 · 34 % (3,7 V)" in resultado
    assert "• ⚪ SEV2 · Sin telemetría" in resultado


@pytest.mark.asyncio
async def test_battery_excluye_fuera_andalucia() -> None:
    """Verifica que /battery nunca incluya nodos fuera de Andalucía ni sin provincia."""
    items: list[dict[str, Any]] = [
        {
            "id": "!cad0001",
            "short": "CAD1",
            "role": "ROUTER",
            "province": "ES-CA",
            "battery": {"level": 50, "voltage": 3.9, "powered": False},
        },
        {
            "id": "!ext0001",
            "short": "EXT1",
            "role": "ROUTER",
            "province": "FUERA",
            "battery": {"level": 10, "voltage": 3.4, "powered": False},
        },
        {
            "id": "!mad0001",
            "short": "MAD1",
            "role": "ROUTER",
            "province": "ES-M",
            "battery": {"level": 90, "voltage": 4.15, "powered": False},
        },
        {
            "id": "!sin0001",
            "short": "SIN1",
            "role": "ROUTER",
            "province": None,
            "battery": {"level": 80, "voltage": 4.0, "powered": False},
        },
    ]

    comandos = _crear_gestor_comandos(items)
    resultado = await comandos.ejecutar_battery()

    assert "CAD1" in resultado
    assert "EXT1" not in resultado
    assert "MAD1" not in resultado
    assert "SIN1" not in resultado
    assert "Fuera de Andalucía" not in resultado
    assert "Madrid" not in resultado


@pytest.mark.asyncio
async def test_battery_parametro_fuera() -> None:
    """Verifica que solicitar /battery fuera devuelva un mensaje explicativo y ningún nodo."""
    comandos = _crear_gestor_comandos()
    resultado = await comandos.ejecutar_battery("fuera")

    assert "Solo se muestran routers ubicados en Andalucía" in resultado
    assert "Almería, Cádiz, Córdoba, Granada, Huelva, Jaén, Málaga y Sevilla" in resultado


@pytest.mark.asyncio
async def test_battery_filtro_provincia_concreta() -> None:
    """Verifica que filtrar por provincia en /battery muestre solo dicha provincia."""
    items = [
        {
            "id": "!cad0001",
            "short": "CAD1",
            "role": "ROUTER",
            "province": "ES-CA",
            "battery": {"level": 15, "voltage": 3.5, "powered": False},
        },
    ]

    comandos = _crear_gestor_comandos(items)
    resultado = await comandos.ejecutar_battery("cadiz")

    assert "🔋 Batería de routers · Cádiz (1 activos)" in resultado
    assert "📍 Cádiz (1)" in resultado
    assert "• 🔴 CAD1 · 15 % (3,5 V)" in resultado


@pytest.mark.asyncio
async def test_routers_estetico_agrupado_por_provincia() -> None:
    """Verifica el formato estético de /routers: cabecera, iconos, chutil, tx y viñetas."""
    items: list[dict[str, Any]] = [
        {
            "id": "!cad0001",
            "short": "CAD1",
            "role": "ROUTER",
            "province": "ES-CA",
            "battery": {"level": 34, "powered": False},
            "chutil": 22.5,
            "tx": 3.1,
            "last_seen": "2026-10-08T02:48:00Z",
        },
        {
            "id": "!cad0003",
            "short": "CAD3",
            "role": "ROUTER",
            "province": "ES-CA",
            "battery": {"level": None, "powered": True},
            "chutil": 8.0,
            "tx": 1.2,
            "last_seen": "2026-10-08T02:49:00Z",
        },
        {
            "id": "!sev0001",
            "short": "SEV1",
            "role": "ROUTER",
            "province": "ES-SE",
            "battery": {"level": 75, "powered": False},
            "chutil": None,
            "tx": None,
            "last_seen": "2026-10-07T23:50:00Z",
        },
    ]

    comandos = _crear_gestor_comandos(items)
    resultado = await comandos.ejecutar_routers()

    # Cabecera
    assert "📶 Routers de la red · Andalucía (3 activos en 7d)" in resultado

    # Agrupación por provincia con contador
    assert "📍 Cádiz (2)" in resultado
    assert "📍 Sevilla (1)" in resultado

    # Nodos de Cádiz
    assert "• CAD1 · 🟠 34 % · 📡 ch 22,5 % · ⬆️ tx 3,1 %" in resultado
    assert "• CAD3 · 🔌 Red · 📡 ch 8,0 % · ⬆️ tx 1,2 %" in resultado

    # Nodo de Sevilla con telemetría ausente
    assert "• SEV1 · 🔋 75 % · 📡 ch — · ⬆️ tx —" in resultado


@pytest.mark.asyncio
async def test_routers_excluye_fuera_andalucia() -> None:
    """Verifica que /routers nunca muestre nodos de fuera de Andalucía."""
    items: list[dict[str, Any]] = [
        {
            "id": "!cad0001",
            "short": "CAD1",
            "role": "ROUTER",
            "province": "ES-CA",
            "battery": {"level": 80, "powered": False},
            "chutil": 5.0,
            "tx": 1.0,
        },
        {
            "id": "!ext0001",
            "short": "EXT1",
            "role": "ROUTER",
            "province": "FUERA",
            "battery": {"level": 80, "powered": False},
            "chutil": 5.0,
            "tx": 1.0,
        },
        {
            "id": "!por0001",
            "short": "POR1",
            "role": "ROUTER",
            "province": "ES-PO",
            "battery": {"level": 80, "powered": False},
            "chutil": 5.0,
            "tx": 1.0,
        },
    ]

    comandos = _crear_gestor_comandos(items)
    resultado = await comandos.ejecutar_routers()

    assert "CAD1" in resultado
    assert "EXT1" not in resultado
    assert "POR1" not in resultado
    assert "Fuera de Andalucía" not in resultado


@pytest.mark.asyncio
async def test_routers_parametro_fuera() -> None:
    """Verifica que /routers fuera devuelva el mensaje explicativo sin listar nodos."""
    comandos = _crear_gestor_comandos()
    resultado = await comandos.ejecutar_routers("fuera")

    assert "Solo se muestran routers ubicados en Andalucía" in resultado
