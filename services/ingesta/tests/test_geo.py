"""Pruebas unitarias para el motor geoespacial de provincias andaluzas (geo.py)."""

import os
import pytest

from src.geo import (
    GeoEngine,
    calculate_precision_m,
    is_valid_position,
    ANDALUCIA_PROVINCES,
)

# Ruta relativa al GeoJSON de provincias
POLYGONS_PATH = os.path.join(
    os.path.dirname(__file__), "..", "polygons", "provincias-andalucia.geojson"
)


def test_calculate_precision_m() -> None:
    """Verifica el cálculo de precisión métrica a partir de bits."""
    # Bits predeterminados o sin ofuscación
    assert calculate_precision_m(None) == 10.0
    assert calculate_precision_m(0) == 10.0
    assert calculate_precision_m(32) == 10.0
    assert calculate_precision_m(35) == 10.0

    # Valores intermedios según fórmula
    assert calculate_precision_m(10) == 23300.0
    assert calculate_precision_m(11) == 11650.0
    assert calculate_precision_m(13) == 2912.5
    assert calculate_precision_m(14) == 1456.25
    assert calculate_precision_m(16) == 364.0625
    assert calculate_precision_m(19) == 45.5078125


def test_is_valid_position() -> None:
    """Valida los filtros de coordenadas geográficas válidas."""
    # Origen nulo (0, 0)
    assert not is_valid_position(0.0, 0.0, 10.0)
    assert not is_valid_position(0.0000001, 0.0000001, 10.0)

    # Valores nulos
    assert not is_valid_position(None, -5.0, 10.0)
    assert not is_valid_position(36.0, None, 10.0)

    # Fuera de rango esférico
    assert not is_valid_position(95.0, -5.0, 10.0)
    assert not is_valid_position(-92.0, -5.0, 10.0)
    assert not is_valid_position(36.0, 185.0, 10.0)
    assert not is_valid_position(36.0, -195.0, 10.0)

    # Precisión inaceptable (> 12.000 m)
    assert not is_valid_position(37.3891, -5.9845, 12500.0)

    # Coordenadas válidas en Sevilla
    assert is_valid_position(37.3891, -5.9845, 10.0)
    assert is_valid_position(37.3891, -5.9845, 11650.0)


def test_geo_engine_initialization() -> None:
    """Verifica que el motor cargue correctamente el GeoJSON y falle si no existe."""
    engine = GeoEngine(POLYGONS_PATH)
    assert len(engine._provinces) == 8

    with pytest.raises(FileNotFoundError):
        GeoEngine("/ruta/inexistente/poligonos.geojson")


def test_geo_engine_capitals() -> None:
    """Verifica que las 8 capitales andaluzas se ubiquen en su provincia correspondiente."""
    engine = GeoEngine(POLYGONS_PATH)

    capitals = {
        "ES-SE": (37.3891, -5.9845),  # Sevilla
        "ES-MA": (36.7213, -4.4214),  # Málaga
        "ES-CA": (36.5298, -6.2926),  # Cádiz
        "ES-CO": (37.8882, -4.7794),  # Córdoba
        "ES-GR": (37.1773, -3.5986),  # Granada
        "ES-J": (37.7796, -3.7849),   # Jaén
        "ES-H": (37.2614, -6.9447),   # Huelva
        "ES-AL": (36.8340, -2.4637),  # Almería
    }

    for expected_code, (lat, lon) in capitals.items():
        prov, uncertain = engine.resolve_province(lat, lon, precision_m=10.0)
        assert prov == expected_code, f"Fallo en capital {expected_code}: obtenido {prov}"
        assert not uncertain, f"Capital {expected_code} no debería ser incierta con precisión 10m"


def test_coastal_and_marine_positions() -> None:
    """Verifica la asignación de posiciones en la costa y descarte mar adentro."""
    engine = GeoEngine(POLYGONS_PATH, margen_min_m=500.0)

    # Coordenada en tierra firme en Chipiona (Cádiz)
    prov, _ = engine.resolve_province(36.7410, -6.4410, precision_m=10.0)
    assert prov == "ES-CA"

    # Coordenada en el agua a ~400 metros de la costa de Chipiona (36.7420, -6.4440)
    # Debe ser absorbida por el margen costero mínimo de 500m
    prov_costa, _ = engine.resolve_province(36.7420, -6.4440, precision_m=10.0)
    assert prov_costa == "ES-CA", "Punto costero dentro del margen de 500m debe asignarse a Cádiz"

    # Coordenada a ~660 metros en el agua con precisión ofuscada a 14 bits (1.456m)
    # Debe ser absorbida gracias a max(precision_m, margen_min_m)
    prov_ofuscada, _ = engine.resolve_province(36.7420, -6.4480, precision_m=1456.0)
    assert prov_ofuscada == "ES-CA", "Punto costero con precisión ofuscada debe asignarse a Cádiz"

    # Coordenada mar adentro en el Golfo de Cádiz (~40 km mar adentro)
    prov_mar, _ = engine.resolve_province(36.3000, -7.0000, precision_m=100.0)
    assert prov_mar == "FUERA", "Punto mar adentro debe ser FUERA"


def test_international_and_outside_positions() -> None:
    """Verifica que ubicaciones fuera de Andalucía se clasifiquen como FUERA."""
    engine = GeoEngine(POLYGONS_PATH)

    outside_points = [
        ("Lisboa (Portugal)", 38.7223, -9.1393),
        ("Faro (Portugal)", 37.0194, -7.9304),
        ("Gibraltar", 36.1408, -5.3536),
        ("Madrid", 40.4168, -3.7038),
        ("Badajoz (Extremadura)", 38.8794, -6.9706),
        ("Ceuta", 35.8894, -5.3198),
        ("Melilla", 35.2923, -2.9381),
    ]

    for label, lat, lon in outside_points:
        prov, _ = engine.resolve_province(lat, lon, precision_m=10.0)
        assert prov == "FUERA", f"{label} debería clasificarse como FUERA pero fue {prov}"


def test_border_uncertainty() -> None:
    """Verifica la detección de incertidumbre en zonas limítrofes entre provincias."""
    engine = GeoEngine(POLYGONS_PATH)

    # Punto en término de Lebrija/Trebujena a ~3.7 km del límite Cádiz-Sevilla
    lat_borde, lon_borde = 36.8850, -6.0150

    # Con alta precisión (10 m), la provincia está definida y no es incierta (distancia > 10m)
    prov_fino, uncertain_fino = engine.resolve_province(lat_borde, lon_borde, precision_m=10.0)
    assert prov_fino in {"ES-CA", "ES-SE"}
    assert not uncertain_fino

    # Con baja precisión (5.000 m de radio de error), el círculo corta la provincia vecina (3.7 km < 5.0 km)
    prov_grueso, uncertain_grueso = engine.resolve_province(lat_borde, lon_borde, precision_m=5000.0)
    assert prov_grueso in {"ES-CA", "ES-SE"}
    assert uncertain_grueso, "Con 5km de error en zona limítrofe debe marcarse border_uncertain"
