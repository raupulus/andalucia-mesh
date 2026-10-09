# 06.17 · Servicio Interactivo "Mapa"

> Servicio de visualización geográfica en vivo, diagnóstico proactivo de configuración y estado de optimización de nodos de la red en `/mapa`.

## Qué hace y qué NO hace

- **Qué hace**:
  - Muestra un mapa interactivo de alta fluidez (60 fps con más de 3.000 nodos) con Leaflet 1.9.4 autoalojado y renderizado Canvas.
  - Distingue claramente entre clientes normales (círculos), routers/repetidores troncales (triángulos) y pasarelas comunitarias MQTT (aros concéntricos).
  - Codifica la actividad temporal: verde para activo en las últimas 2 horas, cian para activo en 24 horas y gris para nodos sin actividad reciente.
  - Detecta nodos desoptimizados y los resalta con una insignia de advertencia (`▲`) en el propio mapa.
  - Ofrece un cajón lateral inferior izquierdo desplegable con ficha técnica completa y marcador de favoritos en `localStorage`.
  - Dispone de un modal central interactivo de diagnóstico con comprobación de Hop Limit, explicación del impacto en radiofrecuencia, solución paso a paso en la app Meshtastic y desglose de paquetes en 24 horas por tipo de tráfico.
  - Proporciona un buscador predictivo instantáneo por nombre, identificador hexadecimal, identificador decimal, modelo de hardware o provincia.
  - Incluye un catálogo modal de "No optimizados" con buscador y resumen estadístico de motivos.
  - Implementa un sistema de caché de alto rendimiento precompilado con Gzip y soporte para `ETag`/`304 Not Modified`, sirviendo datos en menos de 5 ms.
  - Cumple estrictamente con la directiva de cero cookies (RN-06), soporte multidioma (ES/EN/PT, RN-48) y visualización autorizada de coordenadas públicas de nodos con OK to MQTT (RN-05, RN-03).
- **Qué NO hace**:
  - No expone coordenadas de nodos sin consentimiento de subida (`ok_to_mqtt = false`).
  - No almacena cookies ni rastreadores de terceros en el navegador.
  - No realiza llamadas a CDNs externas; Leaflet y sus estilos están empaquetados localmente con Vite.

## Modelo de datos

### Estructura Compacta de Nodo para el Mapa (`nodes.json`)
```json
[
  {
    "id": "!99101138",
    "num": 2567999800,
    "short": "Pino",
    "long": "Pinoalto7",
    "role": "CLIENT_BASE",
    "hw": "NRF52_PROMICRO_DIY",
    "fw": "2.7.19.56a4d6f",
    "lat": 36.78227,
    "lon": -6.34258,
    "prec": 4.8,
    "src": "manual",
    "prov": "ES-CA",
    "hop": 3,
    "gw": true,
    "rtr": false,
    "bat": 85,
    "volt": 4.12,
    "chutil": 12.4,
    "seen": "2026-10-09T08:15:00Z",
    "ts": 1791533700,
    "status": "active_1h",
    "warn": true,
    "w_lvl": "aviso",
    "w_cnt": 2,
    "w_keys": ["client_base_fw", "nodeinfo_frecuente"],
    "heard": ["!0badc0de"]
  }
]
```

### Estadísticas Globales (`stats.json`)
```json
{
  "total_nodes": 2848,
  "active_1h": 434,
  "active_24h": 1210,
  "gateways_count": 661,
  "routers_count": 82,
  "unoptimized_count": 844,
  "unoptimized_breakdown": {
    "hop_limit_alto": 210,
    "client_base_fw": 345,
    "nodeinfo_frecuente": 412,
    "telemetria_frecuente": 98,
    "alerta_activa": 15
  },
  "updated_at": "09:34",
  "updated_at_iso": "2026-10-09T07:34:00+00:00"
}
```

## Flujos principales

1. **Precompilación de Caché Asíncrona (`php artisan mapa:cache`)**:
   - Se ejecuta cada 2 minutos en segundo plano mediante `portal-tareas` (`routes/console.php`).
   - Lee `api_map_nodes` de PostgreSQL `snm_ingest`, las alertas abiertas de `api_alertas` y los intervalos de `api_node_intervals`.
   - Evalúa heurísticas de optimización: Hop Limit (> 3 aviso, > 5 crítico), `CLIENT_BASE >= 2.7.17` (aviso), NodeInfo < 12h y Telemetría < 2h.
   - Escribe `nodes.json`, `stats.json`, `unoptimized.json` y sus versiones comprimidas `.gz` en `public/cache/mapa/`.
   - Almacena las estructuras en la caché de Laravel/Redis.

2. **Acceso de Usuario y Carga del Lienzo (`GET /mapa`)**:
   - `MapaController@index` renderiza la vista Blade `mapa.index`.
   - El cliente descarga `resources/js/mapa/mapa.js` y `resources/css/mapa.css` minificados y versionados por Vite.
   - El script solicita `/api/v1/mapa/nodes` con cabecera `If-None-Match`. Si no ha cambiado, recibe un `304 Not Modified` instantáneo.
   - Se pintan los nodos en Leaflet con marcadores Canvas/SVG vectoriales de alta eficiencia.

3. **Diagnóstico al Clic en Nodo**:
   - Al hacer clic en un marcador, el mapa centra el nodo y abre el cajón lateral (`#mapa-drawer`).
   - Al pulsar "Ver diagnóstico completo", se abre el modal central que realiza una llamada a `GET /api/v1/mapa/node/{id}`.
   - Se presenta el desglose porcentual de paquetes en las últimas 24 horas (`api_node_packets_24h`), el estado del Hop Limit y las tarjetas de problemas con su solución exacta en la aplicación.

## Puntos de entrada

- **`GET /mapa`**:
  - Vista pública web principal del servicio.
  - Autenticación: No (pública, cero cookies).
  - Rate limit: 60 peticiones/min por IP.
  - Metadatos SEO y Open Graph: Imagen dedicada `1200x630` WebP (`img/og/og-mapa.webp`), etiquetas multilingües `hreflang` (es, en, pt), Schema.org `WebApplication` y `BreadcrumbList`.
  - Indexación: Incluido en `sitemap.xml` con frecuencia horaria (`hourly`), prioridad `0.9` y etiqueta `<image:image>`.
- **`GET /api/v1/mapa/nodes`**:
  - Datos compactos de nodos geolocalizados para el lienzo.
  - Soporta `Content-Encoding: gzip`, `ETag` y respuesta 304.
- **`GET /api/v1/mapa/stats`**:
  - Métricas de red y recuentos de nodos activos y desoptimizados.
- **`GET /api/v1/mapa/unoptimized`**:
  - Listado detallado de nodos con avisos de configuración para el catálogo.
- **`GET /api/v1/mapa/node/{id}`**:
  - Diagnóstico exhaustivo y desglose de 24h para el modal interactivo.
- **`php artisan mapa:cache`**:
  - Comando de consola CLI para regeneración programada o forzada.

## Dependencias en ambos sentidos

- **Hacia adentro (lo que este módulo consume)**:
  - Base de datos `ingesta`: vistas `api_map_nodes`, `api_nodes`, `api_node_intervals`, `api_node_packets_24h`.
  - Base de datos `alertas`: vista `api_alertas`.
  - Frontend: `leaflet` (npm), Vite, Tailwind CSS.
- **Hacia afuera (quien consume este módulo)**:
  - Visitantes y operadores a través del navegador web.
  - Barra de navegación (`cabecera.blade.php`).
  - Motor de búsqueda e indexación (`SitemapController`).

## Configuración

| Variable | Valor por defecto | Efecto |
| :--- | :--- | :--- |
| `PROJECT_DOMAIN` | `mesh.desdechipiona.es` | Dominio base para enlaces y URLs canónicas |
| `CACHE_STORE` | `database` / `redis` | Driver de almacenamiento en memoria intermedia |
| `MAPA_CENTRO_LAT` | `37.4` | Latitud central por defecto para el encuadre |
| `MAPA_CENTRO_LON` | `-4.5` | Longitud central por defecto |
| `MAPA_ZOOM` | `7` | Nivel de zoom inicial (encuadre regional Andalucía) |

## Trampas conocidas

- **TR-12 (Coordenadas en nodos sin GPS)**: Algunos nodos configuran su posición fija manualmente con baja precisión o radio amplio. El cajón lateral advierte explícitamente cuando la posición proviene de configuración manual y no de antena GPS.
- **TR-13 (CLIENT_BASE en firmwares nuevos)**: El firmware oficial 2.7.17 modificó el comportamiento del rol `CLIENT_BASE` para actuar con retardo deliberado (`ROUTER_LATE`). La regla `client-base-fw` previene la degradación de propagación recomendando `CLIENT` o `CLIENT_MUTE`.

## Tests que lo cubren

- `services/portal/tests/Feature/MapaInteractivoTest.php`: Cobertura completa de rutas web, directiva cero cookies, presencia en navbar, endpoints JSON y comando Artisan.
- `services/detector-alertas/tests/test_reglas_nuevas.py`: Pruebas de la regla `client-base-fw`.

## Pendiente real

- [ ] Soporte de clustering opcional para niveles de zoom muy lejanos (< 6) si la densidad de nodos supera los 10.000.

---
> Creado: 2026-10-09 · Última revisión: 2026-10-09
