# Polígonos de Provincias de Andalucía

Este directorio contiene las geometrías simplificadas de las 8 provincias de la Comunidad Autónoma de Andalucía para la asignación geoespacial de paquetes en el servicio de ingesta (`snm-ingesta`).

## Ficheros

- `provincias-andalucia.geojson`: `FeatureCollection` con 8 entidades `MultiPolygon` o `Polygon` correspondientes a:
  - `ES-AL`: Almería (INE `04`)
  - `ES-CA`: Cádiz (INE `11`)
  - `ES-CO`: Córdoba (INE `14`)
  - `ES-GR`: Granada (INE `18`)
  - `ES-H`: Huelva (INE `21`)
  - `ES-J`: Jaén (INE `23`)
  - `ES-MA`: Málaga (INE `29`)
  - `ES-SE`: Sevilla (INE `41`)

## Fuente y Licencia

- **Fuente Original:** Límites municipales y provinciales oficiales del Instituto Geográfico Nacional de España (IGN / CNIG).
- **Licencia:** Compatible con Creative Commons Atribución 4.0 Internacional (CC BY 4.0).
- **Atribución Requerida:** «© Instituto Geográfico Nacional». Esta mención se incluye en los créditos legales del portal web (`docs/info/portal/10-legal-privacy.md`).

## Preparación y Optimización

1. Filtrado exclusivo de las 8 provincias andaluzas por código INE.
2. Reproyección a WGS 84 (EPSG:4326).
3. Reducción de resolución a 5 decimales (precisión submétrica suficiente).
4. Tamaño optimizado (< 50 KB en disco, < 1 MB en memoria tras preparación con `shapely`).
