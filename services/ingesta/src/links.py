"""Cálculo y validación de enlaces RF directos y distancias haversine.

Detecta enlaces directos entre nodo emisor y gateway (0 saltos por radio,
no via MQTT) y calcula la distancia geográfica si ambos poseen coordenadas
válidas con precisión admisible (<= 3 km).
"""

from __future__ import annotations

import math
from typing import Final

EARTH_RADIUS_KM: Final[float] = 6371.0


def calculate_haversine_distance_km(
    lat1: float,
    lon1: float,
    lat2: float,
    lon2: float,
) -> float:
    """Calcula la distancia ortodrómica en kilómetros entre dos coordenadas WGS84.

    Args:
        lat1: Latitud del punto 1 en grados.
        lon1: Longitud del punto 1 en grados.
        lat2: Latitud del punto 2 en grados.
        lon2: Longitud del punto 2 en grados.

    Returns:
        Distancia en kilómetros.
    """
    phi1 = math.radians(lat1)
    phi2 = math.radians(lat2)
    delta_phi = math.radians(lat2 - lat1)
    delta_lambda = math.radians(lon2 - lon1)

    a = (
        math.sin(delta_phi / 2.0) ** 2
        + math.cos(phi1) * math.cos(phi2) * math.sin(delta_lambda / 2.0) ** 2
    )
    c = 2.0 * math.atan2(math.sqrt(a), math.sqrt(1.0 - a))

    return EARTH_RADIUS_KM * c


def evaluate_direct_link(
    hops: int | None,
    via_mqtt: bool,
    from_id: str,
    gateway_id: str,
    node_coords: tuple[float, float, float] | None,  # (lat, lon, precision_m)
    gw_coords: tuple[float, float, float] | None,  # (lat, lon, precision_m)
    precision_max_m: float = 3000.0,
    max_link_km: float = 300.0,
) -> tuple[bool, float | None, bool]:
    """Evalúa si una recepción constituye un enlace RF directo y su distancia.

    Args:
        hops: Número de saltos calculados (debe ser 0).
        via_mqtt: Flag indicando si transitó por MQTT.
        from_id: Identificador del nodo emisor.
        gateway_id: Identificador del gateway receptor.
        node_coords: Coordenadas y precisión métrica del emisor.
        gw_coords: Coordenadas y precisión métrica del gateway.
        precision_max_m: Precisión mínima requerida en ambos extremos (3 km por defecto).
        max_link_km: Distancia máxima admisible para un enlace RF (300 km por defecto).

    Returns:
        Tupla (is_direct, distance_km, is_suspicious).
    """
    # Un paquete propio del gateway nunca es un enlace
    if from_id == gateway_id:
        return False, None, False

    # Condiciones de enlace directo RF
    if hops != 0 or via_mqtt:
        return False, None, False

    is_direct = True

    # Si falta posición en algún extremo, es enlace directo sin distancia conocida
    if node_coords is None or gw_coords is None:
        return is_direct, None, False

    n_lat, n_lon, n_prec = node_coords
    g_lat, g_lon, g_prec = gw_coords

    # Se requiere que ambas posiciones tengan precisión suficiente (<= 3 km)
    if n_prec > precision_max_m or g_prec > precision_max_m:
        return is_direct, None, False

    dist_km = calculate_haversine_distance_km(n_lat, n_lon, g_lat, g_lon)

    # Distancias superiores al umbral máximo se marcan como anómalas (coordenada falsa o túnel)
    if dist_km > max_link_km:
        return is_direct, None, True

    return is_direct, round(dist_km, 1), False
