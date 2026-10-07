"""Motor geoespacial y de asignación provincial para Andalucía Mesh.

Carga los polígonos de las 8 provincias andaluzas desde un archivo GeoJSON,
proyecta las geometrías a coordenadas métricas locales mediante proyección
equirectangular calibrada y clasifica posiciones geográficas determinando:
1. Provincia andaluza asignada (ISO 3166-2: ES-AL, ES-CA, etc.) o 'FUERA'.
2. Flag de incertidumbre limítrofe (border_uncertain).
3. Compensación de margen costero para puntos marítimos inmediatos.
"""

from __future__ import annotations

import json
import math
import os
from typing import Final

import numpy as np
import shapely
from shapely.geometry import Point, shape
from shapely.prepared import prep

# Códigos ISO 3166-2 oficiales de las 8 provincias de Andalucía
ANDALUCIA_PROVINCES: Final[set[str]] = {
    "ES-AL",
    "ES-CA",
    "ES-CO",
    "ES-GR",
    "ES-H",
    "ES-J",
    "ES-MA",
    "ES-SE",
}

# Parámetros geodésicos para proyección equirectangular local
EARTH_RADIUS_M: Final[float] = 6371000.0
PHI_0_DEG: Final[float] = 37.4
PHI_0_RAD: Final[float] = math.radians(PHI_0_DEG)
COS_PHI_0: Final[float] = math.cos(PHI_0_RAD)


def calculate_precision_m(precision_bits: int | None) -> float:
    """Calcula el radio de error en metros a partir de los bits de precisión.

    Fórmula oficial del firmware Meshtastic:
    precision_m = 23300.0 / 2^(bits - 10) para 10 <= bits <= 31.
    Si bits es None, 0 o >= 32, se asume precisión completa (10.0 m).

    Args:
        precision_bits: Número de bits de precisión transmitidos por el nodo.

    Returns:
        Radio de incertidumbre en metros.
    """
    if precision_bits is None or precision_bits == 0 or precision_bits >= 32:
        return 10.0
    if precision_bits < 10:
        return 23300.0
    return 23300.0 / float(1 << (precision_bits - 10))


def is_valid_position(
    latitude: float | None,
    longitude: float | None,
    precision_m: float,
    precision_max_m: float = 12000.0,
) -> bool:
    """Valida si unas coordenadas geográficas son válidas y operativas.

    Comprueba que no sean nulas, que no correspondan al origen (0, 0),
    que estén dentro del rango esférico y que su radio de error no exceda
    el umbral admisible.

    Args:
        latitude: Latitud en grados decimales.
        longitude: Longitud en grados decimales.
        precision_m: Radio de incertidumbre en metros.
        precision_max_m: Incertidumbre máxima tolerada (12.000 m por defecto).

    Returns:
        True si la posición es válida para procesamiento; False en caso contrario.
    """
    if latitude is None or longitude is None:
        return False
    if math.isclose(latitude, 0.0, abs_tol=1e-6) and math.isclose(longitude, 0.0, abs_tol=1e-6):
        return False
    if not (-90.0 <= latitude <= 90.0 and -180.0 <= longitude <= 180.0):
        return False
    if precision_m > precision_max_m:
        return False
    return True


class GeoEngine:
    """Motor de resolución geoespacial de provincias andaluzas."""

    def __init__(
        self,
        geojson_path: str,
        margen_min_m: float = 500.0,
        precision_max_m: float = 12000.0,
    ) -> None:
        """Inicializa el motor geoespacial cargando las geometrías provinciales.

        Args:
            geojson_path: Ruta absoluta o relativa al fichero GeoJSON.
            margen_min_m: Margen costero mínimo en metros (500 m por defecto).
            precision_max_m: Peor precisión métrica admisible (12.000 m por defecto).

        Raises:
            FileNotFoundError: Si el fichero no existe en la ruta dada.
            ValueError: Si el fichero no contiene las 8 provincias andaluzas.
        """
        if not os.path.isfile(geojson_path):
            raise FileNotFoundError(f"Archivo de polígonos no encontrado: {geojson_path}")

        self.margen_min_m = margen_min_m
        self.precision_max_m = precision_max_m
        self._provinces: dict[str, dict[str, object]] = {}

        self._load_polygons(geojson_path)

    def _project_coords(self, coords: np.ndarray) -> np.ndarray:
        """Proyecta un array Nx2 de coordenadas (lon, lat) a metros locales."""
        arr = np.asarray(coords)
        x = EARTH_RADIUS_M * COS_PHI_0 * np.radians(arr[:, 0])
        y = EARTH_RADIUS_M * np.radians(arr[:, 1])
        return np.column_stack((x, y))

    def _project_point(self, lon: float, lat: float) -> Point:
        """Proyecta un punto individual (lon, lat) a coordenadas planas en metros."""
        x = EARTH_RADIUS_M * COS_PHI_0 * math.radians(lon)
        y = EARTH_RADIUS_M * math.radians(lat)
        return Point(x, y)

    def _load_polygons(self, geojson_path: str) -> None:
        """Carga y proyecta las geometrías desde el archivo GeoJSON."""
        with open(geojson_path, "r", encoding="utf-8") as f:
            data = json.load(f)

        features = data.get("features", [])
        loaded_codes: set[str] = set()

        for feat in features:
            props = feat.get("properties", {})
            code = props.get("code")
            if not code or code not in ANDALUCIA_PROVINCES:
                continue

            geom_wgs = shape(feat["geometry"])
            if not geom_wgs.is_valid:
                geom_wgs = shapely.make_valid(geom_wgs)

            geom_m = shapely.transform(geom_wgs, self._project_coords)
            prepared_m = prep(geom_m)

            self._provinces[code] = {
                "name": props.get("name", code),
                "geom_m": geom_m,
                "prep_m": prepared_m,
            }
            loaded_codes.add(code)

        missing = ANDALUCIA_PROVINCES - loaded_codes
        if missing:
            raise ValueError(
                f"El GeoJSON no contiene las 8 provincias requeridas. Faltan: {missing}"
            )

    def resolve_province(
        self,
        latitude: float,
        longitude: float,
        precision_m: float,
    ) -> tuple[str, bool]:
        """Determina la provincia andaluza y la incertidumbre de frontera.

        Aplica el algoritmo de 3 fases especificado en 05.3:
        1. Comprobación directa punto en polígono (preparado).
        2. Si cae fuera, cálculo de distancia a polígono más cercano:
           si d <= max(precision_m, margen_min_m), se asigna esa provincia costera.
           En caso contrario, se devuelve 'FUERA'.
        3. Detección de borde incierto si alguna OTRA provincia andaluza
           dista menos de precision_m del punto.

        Args:
            latitude: Latitud del nodo en grados decimales.
            longitude: Longitud del nodo en grados decimales.
            precision_m: Radio de incertidumbre en metros.

        Returns:
            Tupla (codigo_provincia, border_uncertain) donde codigo_provincia
            es uno de los 8 códigos ISO ('ES-CA', 'ES-SE', etc.) o 'FUERA'.
        """
        if not is_valid_position(latitude, longitude, precision_m, self.precision_max_m):
            return "FUERA", False

        pt_m = self._project_point(longitude, latitude)

        # 1. Comprobación de inclusión directa
        matched_code: str | None = None
        for code, pdata in self._provinces.items():
            prep_geom = pdata["prep_m"]
            if prep_geom.contains(pt_m) or prep_geom.intersects(pt_m):
                matched_code = code
                break

        # 2. Si no cae dentro, evaluar margen costero o cercanía
        if matched_code is None:
            min_dist = float("inf")
            nearest_code: str | None = None

            for code, pdata in self._provinces.items():
                geom: shapely.Geometry = pdata["geom_m"]  # type: ignore
                dist = pt_m.distance(geom)
                if dist < min_dist:
                    min_dist = dist
                    nearest_code = code

            tolerancia_costera = max(precision_m, self.margen_min_m)
            if nearest_code is not None and min_dist <= tolerancia_costera:
                matched_code = nearest_code
            else:
                return "FUERA", False

        # 3. Comprobación de borde incierto contra las otras provincias andaluzas
        border_uncertain = False
        for code, pdata in self._provinces.items():
            if code == matched_code:
                continue
            geom: shapely.Geometry = pdata["geom_m"]  # type: ignore
            dist_otra = pt_m.distance(geom)
            if dist_otra <= precision_m:
                border_uncertain = True
                break

        return matched_code, border_uncertain
