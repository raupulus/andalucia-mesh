# 06.13 · API de nodos y alertas

> Endpoints `nodes`, `nodes/{id}/diagnosis`, `nodes/at-risk` y `alerts*` de la API pública. Base común (rutas, formato, errores, caché, límite) en [11](11-public-api.md).

## Objetivo

Dar a "Revisa tu nodo", a la página de alertas, a la portada y a los bots los datos de nodos concretos y de alertas, leyendo solo las vistas `api_*` de `ingest` (rol `portal_lector_ingesta`) y de `alertas` (rol `portal_lector_alertas`).

## Especificación

### `GET /nodes?search=&limit=`

- `search` obligatorio, 2–40 caracteres. Si parece un id (`!?[0-9a-f]{8}`) busca por `id` exacto; si no, `ILIKE` sin acentos sobre `short_name` y `long_name` (prefijo primero, luego contiene).
- `limit` 10 por defecto, máximo 25. Orden: coincidencia exacta, prefijo, `last_seen` desc.
- Fuente `api_nodes`. Solo nodos vistos en 30 días.

```json
{"generated_at": "2026-10-04T10:00:00Z", "stale": false, "count": 1,
 "items": [{"id": "!a1b2c3d4", "short": "CAD1", "long": "Repetidor Sierra", "role": "ROUTER", "hw_model": "RAK4631",
            "province": "ES-CA", "is_router": true, "is_gateway": false, "last_seen": "2026-10-04T09:58:00Z"}]}
```

### `GET /nodes/{id}/diagnosis`

Ventana fija de 7 días. Fuentes: `api_nodes`, `api_node_intervals` (con `variant`), `api_node_battery_daily`, `api_node_reboots_daily`, `api_rank_network_usage` (suma de filas `day` de 7 días), `api_province_load` y `api_alertas` (abiertas del nodo). Nodo inexistente → `404`; nodo existente sin datos en 7 días → `200` con `findings: []` y `notes: ["Sin datos en los últimos 7 días"]`.

Comprobaciones (umbrales en `config('proyecto.recomendaciones')`, los mismos que muestra `/configura-tu-nodo`; ningún número en el código del controlador):

| `check` | Fuente | Hallazgo cuando | `risk` | Recomendación (texto) |
|---|---|---|---|---|
| `telemetry-interval` | intervals `telemetry`/`device_metrics` | mediana < 4 h (solar) o < 6 h (router) | `bajo` | Subir el intervalo o desactivarla si está enchufado |
| `environment-interval` | intervals `telemetry`/`environment_metrics` | mediana < 4 h | `bajo` | Desactivar o > 4 h |
| `nodeinfo-interval` | intervals `nodeinfo` | mediana < 72 h × 0,9 | `bajo` | 72 h |
| `position-interval` | intervals `position` | nodo fijo (`is_router` o sin cambio de provincia) y mediana < 72 h × 0,9; móvil < 1 h | `bajo` | 72 h fijo, ≥ 1 h móvil; sin posición inteligente |
| `battery-low-sustained` | battery_daily | `readings_below_40 / readings` > 50 % o `min_level` baja 3 días seguidos | `medio` | Revisar panel, batería o consumo |
| `reboots` | reboots_daily | ≥ 1 reinicio en 7 días | `bajo` (≥ 3/día: `medio`) | Alimentación y firmware (`/firmware`) |
| `hops` | `api_nodes.hop_start_last` | > `LORA_HOP_LIMIT` (3) | `bajo` (≥ 6: `medio`) | Bajar a 3 (4–5 solo en extremos) |
| `role` | `api_nodes.role` | rol en `INFRA_ROLES` (aviso informativo) | `bajo` | Router solo coordinado; si no, `CLIENT` o `CLIENT_MUTE` |
| `channel-busy` | `api_province_load` de su provincia | nivel `red` | `bajo` | Bajar intervalos ayuda a la zona |

- Un tipo con menos de 2 emisiones en 7 días no genera hallazgo de intervalo. Alimentado (`powered_readings` > 0 y sin lecturas válidas) no genera hallazgo de batería.
- Orden de `findings`: `risk` desc (por el orden del catálogo) y después el orden de la tabla.

```json
{"generated_at": "2026-10-04T10:00:00Z", "stale": false,
 "node": {"id": "!a1b2c3d4", "short": "CAD1", "long": "Repetidor Sierra", "role": "CLIENT", "province": "ES-CA",
          "last_seen": "2026-10-04T09:58:00Z", "heard_by": ["!0badc0de", "!12345678"]},
 "window": "7d",
 "findings": [
   {"check": "telemetry-interval", "risk": "bajo", "observed": {"median_s": 720, "broadcasts": 840},
    "recommended": {"min_s": 14400}, "text": "Telemetría cada 12 min de media; lo recomendado es 4 h o más", "guide": "/configura-tu-nodo#intervalos"}],
 "open_alerts": [{"id": "01JABCDXYZ7Q8R9S0T1V2W3X4Y", "rule": "battery-low", "risk": "medio", "type": "clientes", "opened_at": "2026-10-03T08:00:00Z"}],
 "network_usage": {"rank": 14, "of": 412, "airtime_s": 412.5, "by_type": {"telemetry": 0.71, "position": 0.18, "nodeinfo": 0.11}},
 "notes": []}
```

`by_type` en fracción 0–1 con 2 decimales; tipos a 0 se omiten. Si `alertas` no responde: `open_alerts: null` y el resto sale igual.

### `GET /nodes/at-risk?province=&min_risk=&type=&limit=`

- Fuente `api_nodos_en_riesgo` (excluye alertas de `all`). `min_risk` por defecto `medio`, filtra con `riesgo_max_orden >= orden(min_risk)` del catálogo; `type` filtra si está en `tipos`; `province` ISO. `limit` 20 por defecto, 100 máximo.
- Orden: `riesgo_max_orden` desc, `desde` asc.

```json
{"generated_at": "2026-10-04T10:00:00Z", "stale": false, "count": 1,
 "items": [{"node": {"id": "!a1b2c3d4", "short": "CAD1", "long": "Repetidor Sierra", "role": "ROUTER", "province": "ES-CA"},
            "risk": "alto", "types": ["infraestructura"], "rules": ["reboot-loop", "battery-low"],
            "reasons": ["7 reinicios en la última hora", "Batería al 34 %"], "open_alerts": 2,
            "since": "2026-10-03T15:00:00Z", "updated_at": "2026-10-04T09:55:00Z"}]}
```

### `GET /alerts?state=&risk=&min_risk=&type=&rule=&province=&node=&since=&sort=&limit=&cursor=`

- Fuente `api_alertas`. `state=open|resolved|all` (defecto `open`). `risk` lista exacta; `min_risk` con `riesgo_orden`; `type`, `rule` listas; `province` cruza con `provincia` o `provincias`; `node` id; `since` ISO sobre `actualizada_en`.
- `sort=recent` (defecto: `actualizada_en` desc, `id` desc) o `risk` (`riesgo_orden` desc, `actualizada_en` desc, `id` desc). Paginación por cursor ([11](11-public-api.md)).
- Objeto alerta con el mapeo de `integration.md` §9:

```json
{"id": "01JABCDXYZ7Q8R9S0T1V2W3X4Y", "rule": "battery-low", "risk": "medio", "type": "clientes",
 "message": "Batería al 34 % y bajando", "node": "!a1b2c3d4", "nodes": ["!a1b2c3d4"], "nodes_total": 1,
 "node_info": {"short": "CAD1", "long": "Repetidor Sierra", "role": "CLIENT", "province": "ES-CA"},
 "provinces": ["ES-CA"], "data": {"nivel": 34, "tendencia_24h": -9}, "state": "open",
 "opened_at": "2026-10-03T08:00:00Z", "updated_at": "2026-10-04T09:00:00Z", "resolved_at": null, "reopenings": 0,
 "url": "https://mesh.example.org/alertas/01JABCDXYZ7Q8R9S0T1V2W3X4Y"}
```

`url` se construye con `PROJECT_DOMAIN`. Alertas de `all`: `node: "all"`, `node_info: null`, `nodes` hasta 50 ids y `nodes_total` real.

### `GET /alerts/{id}`

La alerta (mismo objeto) + `transitions`: `[{"id", "transition", "at", "risk", "message", "data"}]` de `api_alertas_transiciones`, orden `en` asc. Inexistente o con más de 1 año → `404`.

### `GET /alerts/catalog`

De `api_catalogo`: `{"risks": [{"id", "order", "name", "description"}], "types": [{"id", "name", "description"}], "rules": [{"id", "name", "description", "active", "phase", "affects_mesh", "risks", "types"}]}`, ordenados por `orden`. Solo reglas `activa = true` salvo `?all=1`.

## Contratos propios

- Clases de datos: `App\Datos\Ingesta\Nodos`, `App\Datos\Ingesta\Diagnostico`, `App\Datos\Alertas\Alertas`, `App\Datos\Alertas\Catalogo`. Recursos JSON: `NodoResource`, `DiagnosticoResource`, `AlertaResource` (mapeo de claves en un único sitio: `AlertaResource`).
- `config/proyecto.php` → `recomendaciones`: `telemetria_solar_s` 14400, `telemetria_router_s` 21600, `entorno_s` 14400, `nodeinfo_s` 259200, `posicion_fija_s` 259200, `posicion_movil_s` 3600, `tolerancia` 0.9, `bateria_baja_pct` 40, `bateria_baja_fraccion` 0.5, `saltos_max` = `LORA_HOP_LIMIT`, `saltos_altos` 6.
- Los bots usan `alerts/{id}` (enlace), `alerts/catalog` y `nodes/at-risk`; los campos que leen están en `../bots-webhooks/README.md` §4.2 y no se pueden quitar.

## Unidades de trabajo

- **UT-06.13.1 — Búsqueda de nodos.** Normalización de id, búsqueda sin acentos (`unaccent` no está en la vista: se compara con `lower()` y una versión sin tildes calculada en PHP sobre los candidatos por prefijo). *Aceptación:* `cadiz` encuentra `Cádiz Norte`; `a1b2c3d4` y `!A1B2C3D4` dan el mismo nodo.
- **UT-06.13.2 — Diagnóstico.** Una clase por comprobación (`App\Diagnostico\Comprobaciones\*`), cada una devuelve 0 o 1 hallazgo con `observed`, `recommended`, `text` y `guide`. *Bordes:* nodo sin telemetría; alimentado; intervalos con 1 emisión. *Aceptación:* un nodo de prueba con la configuración recomendada da `findings: []`.
- **UT-06.13.3 — Nodos en riesgo.** *Aceptación:* orden por `riesgo_max_orden` y `desde`; `min_risk=alto` excluye `medio`.
- **UT-06.13.4 — Listado de alertas.** Filtros, orden y cursor. *Bordes:* cursor con otros filtros → `400`; `province` en `provincias` de una alerta de `all`. *Aceptación:* recorrer 1.200 alertas con `limit=500` da 3 páginas sin repetir ni saltar.
- **UT-06.13.5 — Ficha y catálogo.** *Aceptación:* `alerts/catalog` responde aunque no haya alertas; un riesgo nuevo en `clasificacion.yaml` aparece sin tocar el portal.
- **UT-06.13.6 — Contrato con los bots.** Prueba que valida las respuestas contra los campos de `../bots-webhooks/README.md` §4.2. *Aceptación:* pruebas en verde.

## Escenarios de prueba

1. **Dado** un nodo cliente con telemetría cada 12 min durante 7 días, **cuando** se pide su diagnóstico, **entonces** sale `telemetry-interval` con `observed.median_s` ≈ 720 y `guide` a la sección de intervalos.
2. **Dado** un nodo con nodeinfo cada 70 h, **cuando** se diagnostica, **entonces** no hay hallazgo (tolerancia 0,9 × 72 h = 64,8 h).
3. **Dado** un nodo alimentado (nivel 101 en todas las lecturas), **cuando** se diagnostica, **entonces** no aparece `battery-low-sustained`.
4. **Dado** 2 alertas abiertas de un nodo (`alto` y `medio`) y una de `all`, **cuando** se pide `/nodes/at-risk`, **entonces** el nodo aparece una vez con `risk: "alto"` y `open_alerts: 2`; la de `all` no cuenta.
5. **Dado** una alerta reabierta 2 veces, **cuando** se pide `/alerts/{id}`, **entonces** `reopenings: 2` y `transitions` con 5 entradas en orden.
6. **Dado** la base `alertas` caída sin copia en caché, **cuando** se pide `/alerts`, **entonces** `503` `source_unavailable`; `/nodes?search=` sigue funcionando.
7. **Dado** `/alerts?state=all&sort=risk&limit=2`, **cuando** se sigue `next_cursor` hasta `null`, **entonces** se recorren todas en orden de riesgo sin duplicados.

---
> Creado: 2026-10-07 · Última revisión: 2026-10-07
