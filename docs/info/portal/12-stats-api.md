# 06.12 · API de estadísticas

> `stats/summary`, `stats/provinces`, `stats/rankings`, `stats/traffic-mix` y `routers`, desde las vistas `api_*` de `ingest` (y `api_alertas_resumen` de `alertas` en el resumen). Formato, errores, caché y límite: [11](11-public-api.md). Columnas exactas de las vistas: `../ingesta/05-contract-views.md`.

## Objetivo

Dar a la portada, al mapa, a la página de rankings y a los comandos `/status`, `/battery` y `/routers` de los bots todo lo que necesitan. La API filtra, ordena, limita, pone nombre a las claves y calcula medias y niveles de carga; **no reagrega periodos**: las vistas `api_rank_<id>` y `api_traffic_mix` ya traen hora, día, semana y mes en hora de Madrid.

## Especificación

### Reglas comunes

- Provincias (`config('proyecto.provincias')`): `ES-AL` Almería, `ES-CA` Cádiz, `ES-CO` Córdoba, `ES-GR` Granada, `ES-H` Huelva, `ES-J` Jaén, `ES-MA` Málaga, `ES-SE` Sevilla; `FUERA` = fuera de Andalucía. Las listas por provincia traen siempre las 8, en este orden, aunque estén vacías.
- Saturación: filas de `api_province_load` (ya filtra 12 h, los 8 códigos y los grupos `router`, `cliente`; `CLIENT_MUTE` no está). Por provincia: media de cada grupo y `avg` = 0,6 × media de routers + 0,4 × media conjunta de `CLIENT` y `CLIENT_BASE` (pesos de `config('proyecto.mapa.pesos')` desde `SATURACION_PESO_*`); un grupo sin filas reparte su peso en proporción entre los demás; sin filas = `null`. `max` = máximo de todas las filas; `groups` = media y número de nodos de cada grupo. Andalucía: misma fórmula con todas las filas de las 8 provincias.
- Nivel (`config('proyecto.mapa.carga')`: `verde_max` 20, `rojo_min` 40): `green` ≤ 20, `orange` > 20 y < 40, `red` ≥ 40, `nodata` sin filas. Se decide con el valor sin redondear y se publica con 1 decimal.
- `App\Datos\Ingesta\CargaProvincias`: una consulta (`SELECT province, grupo, channel_utilization FROM api_province_load`) y el cálculo ponderado en `App\Datos\Saturacion` (una sola implementación), caché 60 s; la usan `summary`, `provinces`, el diagnóstico y la página del mapa.

### Periodos (`period` × `which`)

`App\Datos\Periodos::rango(string $period, string $which)` calcula en `TZ` (`Europe/Madrid`) y devuelve UTC:

| `period` | `current` empieza | `previous` | TTL `current` / `previous` |
|---|---|---|---|
| `hour` | Inicio de la hora en curso (Madrid tiene desfase entero: la hora UTC coincide) | Hora anterior | 60 s / 1 h |
| `day` | Hoy 00:00 local | Ayer 00:00 local | 5 min / 1 h |
| `week` | Lunes 00:00 local (semana ISO) | Lunes anterior | 15 min / 1 h |
| `month` | Día 1 00:00 local | Mes anterior | 15 min / 1 h |

`from` = inicio; `to` = inicio del periodo siguiente (los días de cambio de hora duran 23 o 25 h). Con `current`, `partial: true`. Se filtra `bucket_start = :from`: si el periodo en curso aún no tiene filas, `items: []`, nunca el anterior. Por defecto `period=day`, `which=current`; otro valor → `400`.

### `GET /stats/summary`

Sin parámetros. Fuentes: `api_summary` (una fila), `CargaProvincias` y `api_alertas_resumen` (`riesgo`, `riesgo_orden`, `tipo`, `abiertas`).

```json
{
  "generated_at": "2026-10-03T12:00:00Z", "stale": false,
  "nodes_active_24h": 412, "nodes_active_7d": 655, "routers_active_24h": 38,
  "gateways_publishing": 9, "packets_last_hour": 15840,
  "channel_utilization": {
    "window": "12h", "andalucia_avg": 18.6, "andalucia_max": 52.1,
    "provinces": [{"code": "ES-CA", "name": "Cádiz", "avg": 27.4, "max": 44.5, "level": "orange"}]
  },
  "alerts_open": {"total": 12, "by_risk": {"bajo": 4, "medio": 7, "alto": 1}, "by_type": {"infraestructura": 9, "clientes": 3}},
  "notes": ["Solo cuenta lo que llega a nuestros gateways con OK to MQTT activado."]
}
```

- `by_risk` y `by_type` suman `abiertas` (la vista trae todas las combinaciones del catálogo, con 0); claves en orden de `riesgo_orden`.
- `alertas` no disponible y sin copia (p. ej. antes de desplegar el detector): `alerts_open: null` + nota; el resto se sirve. `ingest` no disponible: copia de respaldo o `503` (11).
- Lo que leen los bots (`../bots-webhooks/README.md` §4.2): `generated_at`, `nodes_active_24h`, `nodes_active_7d`, `routers_active_24h`, `gateways_publishing`, `channel_utilization.andalucia_avg`, `channel_utilization.provinces[].code` y `.avg`, `alerts_open.by_risk`, `alerts_open.by_type`, `stale`. Ninguno puede faltar.

### `GET /stats/provinces?window=30m|1h|6h|12h|1d|7d|30d`

`window` por defecto `30m`. Un nodo cuenta si su última posición válida está en la ventana; la provincia la asigna `ingest` (sin coordenadas en la API). Asimismo, la saturación y niveles de carga (`load`) se calculan con respecto a las métricas recibidas en la misma ventana de tiempo:

```sql
SELECT province, count(*) AS nodes, count(*) FILTER (WHERE border_uncertain) AS border_uncertain
FROM api_nodes
WHERE province IS NOT NULL AND last_position_at >= now() - CAST(:ventana AS interval)
GROUP BY province;
```

```json
{
  "window": "30m", "load_window": "30m", "generated_at": "…", "stale": false,
  "total_andalucia": 412, "outside_andalucia": 37, "border_uncertain": 5,
  "load_levels": {"green_max": 20, "red_min": 40},
  "provinces": [
    {"code": "ES-AL", "name": "Almería", "nodes": 21, "load": {"avg": 12.4, "max": 18.0, "level": "green",
      "groups": {"routers": {"avg": 13.0, "n": 3}, "clients": {"avg": 11.5, "n": 7}}}},
    {"code": "ES-CO", "name": "Córdoba", "nodes": 19, "load": {"avg": null, "max": null, "level": "nodata",
      "groups": {"routers": {"avg": null, "n": 0}, "clients": {"avg": null, "n": 0}}}}
  ],
  "notes": ["Solo nodos cuya posición llega con OK to MQTT.", "Saturación estimada: media de routers (60 %) y media conjunta de CLIENT y CLIENT_BASE (40 %) en la ventana seleccionada; CLIENT_MUTE no cuenta."]
}
```

`total_andalucia` = suma de las 8; `outside_andalucia` = fila `FUERA`; `border_uncertain` solo de las 8. La carga provincial refleja la presión real calculada para la ventana solicitada.

### `GET /stats/rankings`

Catálogo fijo en código (`App\Api\V1\Rankings\Catalogo`), que es también la lista blanca `id → vista` (`api_rank_` + id con `_`):

| `id` | `name` | `unit` | `subject_type` |
|---|---|---|---|
| `network-usage` | Consumo de red | `s` | `node` |
| `gateway-coverage` | Gateways que más cubren | `nodes` | `gateway` |
| `gateway-exclusive` | Gateways imprescindibles | `packets` | `gateway` |
| `longest-links` | Enlaces directos más largos | `km` | `node` |
| `best-links` | Mejores enlaces | `dB` | `node` |
| `most-neighbors` | Nodos mejor conectados | `neighbors` | `node` |
| `uptime` | Nodos más estables | `s` | `node` |
| `solar-health` | Solares más sanos | `%` | `node` |
| `chatters` | Más conversadores | `messages` | `node` |
| `new-nodes` | Nodos nuevos | `unix_time` | `node` |
| `provinces-growth` | Provincias que más crecen | `nodes` | `province` |

Respuesta: `items[]` con `id`, `name`, `description` (una frase; la de `network-usage` aclara que es diagnóstico, no competición), `unit`, `subject_type`, `periods` (`["hour","day","week","month"]`), `default_limit` 10, `max_limit` 50.

### `GET /stats/rankings/{id}?period=&which=&limit=`

`id` fuera del catálogo → `404`; `limit` 1–50 (10).

```sql
SELECT subject_id, value, extra FROM api_rank_network_usage
WHERE granularity = :period AND bucket_start = :from
ORDER BY value DESC, subject_id ASC LIMIT :limit;
-- nombres:            SELECT id, short_name, long_name, province FROM api_nodes WHERE id = ANY(:ids)
-- solo network-usage: SELECT sum(airtime_s) FROM api_traffic_mix WHERE granularity = :period AND bucket_start = :from
```

```json
{
  "id": "network-usage", "name": "Consumo de red", "unit": "s", "subject_type": "node",
  "period": "day", "which": "previous", "from": "2026-09-30T22:00:00Z", "to": "2026-10-01T22:00:00Z", "partial": false,
  "generated_at": "…", "stale": false,
  "items": [{"rank": 1, "subject": {"id": "!a1b2c3d4", "short": "XYZ1", "long": "Nodo ejemplo", "province": "ES-CA"},
             "value": 412.6, "share_pct": 3.8,
             "extra": {"packets": 1290, "by_type": {"nodeinfo": 251.7, "position": 92.8, "telemetry": 62.3, "text": 1.6, "traceroute": 0.0, "other": 4.2}}}],
  "notes": ["Tiempo de aire estimado de los paquetes que origina el nodo; no incluye retransmisiones."]
}
```

- `subject`: nodo o gateway → `id`, `short`, `long`, `province` (de `api_nodes`; `null` si falta); provincia → `code`, `name`.
- `rank` = posición 1…N; empate de `value` → `subject_id` ascendente (determinista).
- `extra` tal cual la vista. `share_pct` solo en `network-usage` = `value` / aire total del periodo × 100 (`null` si el total es 0).
- `new-nodes`: `value` en segundos Unix (más reciente primero); fecha legible en `extra.first_seen`.
- Filas `hour` disponibles 30 días; `week` y `month`, solo actual y anterior (lo que publica la vista).

### `GET /stats/traffic-mix?period=&which=`

`SELECT portnum, packets, airtime_s FROM api_traffic_mix WHERE granularity = :period AND bucket_start = :from`. Respuesta: `period`, `which`, `from`, `to`, `partial`, `total: {packets, airtime_s}`, `items[]` (`portnum`, `packets`, `airtime_s`, `packets_pct`, `airtime_pct`) por `airtime_s` descendente. Sin map reports (no ocupan aire).

### `GET /routers?province=&sort=`

Solo routers (rol en `INFRA_ROLES`) vistos en 7 días, es decir, las filas de `api_routers`; un gateway que no es router no aparece. `province`: uno de los 8 códigos o `FUERA`. `sort`: `name` (defecto: `short_name` y luego `id`), `battery` (nivel ascendente; alimentados y sin dato al final), `chutil` y `tx` (descendente, sin dato al final), `last_seen` (descendente). Corte de 12 h y orden en PHP (cientos de filas como mucho).

```json
{
  "generated_at": "…", "stale": false, "province": null, "sort": "name", "count": 38,
  "items": [{
    "id": "!a1b2c3d4", "short": "CAD1", "long": "Repetidor Sierra Cádiz", "role": "ROUTER", "hw_model": "HELTEC_V3",
    "province": "ES-CA", "is_gateway": false,
    "battery": {"level": 34, "voltage": 3.71, "powered": false, "at": "2026-10-03T11:40:00Z"},
    "chutil": 22.5, "tx": 3.1, "metrics_at": "2026-10-03T11:40:00Z",
    "last_seen": "2026-10-03T11:58:00Z", "last_reboot_at": "2026-10-01T03:12:00Z"
  }]
}
```

- `battery` sale de `battery_level`, `voltage`, `battery_at` y `powered`; con `battery_at` de hace más de 12 h, sus cuatro campos van a `null`. `powered: true` → `level: null` (no se publica 101 %).
- `chutil` = `channel_utilization`, `tx` = `air_util_tx`; `null` si `metrics_at` tiene más de 12 h.

## Contratos propios

Las respuestas de esta página (claves y significado), el catálogo de rankings (ids, `unit`, `subject_type`), los valores de `period`, `which`, `window` y `sort`, y las secciones `provincias` y `mapa.carga` de `config/proyecto.php`.

## Unidades de trabajo

- **UT-06.12.1 — Carga por provincia y niveles.** `CargaProvincias` y `NivelCarga`. *Bordes:* 20,0 y 40,0 exactos; provincia sin filas; router sin provincia (no está en la vista). *Aceptación:* escenario 1; 20,0 → `green`, 40,0 → `red`.
- **UT-06.12.2 — `summary`.** *Bordes:* `alertas` caída o sin desplegar; catálogo con un riesgo nuevo (aparece en `by_risk`). *Aceptación:* prueba de contrato con los campos de los bots; con `alertas` caída, `alerts_open: null` y `200`.
- **UT-06.12.3 — `provinces`.** *Bordes:* `window=invalido` → `400`; provincia con 0 nodos (aparece con `nodes: 0`). *Aceptación:* `window` por defecto `30m`; opciones `30m, 1h, 6h, 12h, 1d, 7d, 30d` funcionales; suma de provincias = `total_andalucia`; coincide con un recuento SQL manual sobre una muestra.
- **UT-06.12.4 — Periodos.** `Periodos::rango`. *Bordes:* cambio de hora (25-10-2026), semana que cruza el año (lunes 28-12-2026), `previous` de enero. *Aceptación:* escenario 2 y una tabla de casos fijos con reloj congelado.
- **UT-06.12.5 — Rankings.** Catálogo, lista blanca, nombres, `share_pct`. *Bordes:* `id` inexistente → `404`; `limit=51` → `400`; nodo del ranking sin fila en `api_nodes`. *Aceptación:* los 11 ids × 4 periodos × 2 `which` responden; un periodo cerrado devuelve el mismo `ETag` en dos peticiones; escenario 3.
- **UT-06.12.6 — `traffic-mix`.** *Aceptación:* `packets_pct` y `airtime_pct` suman 100 ± 0,1; periodo sin datos → `items: []` y totales 0.
- **UT-06.12.7 — `routers`.** Corte de 12 h, `powered`, órdenes. *Bordes:* `battery_level` 0 con voltaje 0 (sin sensor: se publica tal cual la vista), router sin provincia. *Aceptación:* escenarios 4 y 5; los comandos `/battery` y `/routers` de los bots se construyen solo con esta respuesta.

## Escenarios de prueba

1. **Dado** en Cádiz 3 routers al 15, 25 y 35 % (media 25), 3 `CLIENT` al 10 % y 1 `CLIENT_BASE` al 30 % (media conjunta de clientes 15), medidos hace 1 h, **cuando** se pide `/stats/provinces`, **entonces** `ES-CA` trae `avg` = 0,6 × 25 + 0,4 × 15 = 21,0, `level: "orange"`, `max: 35.0` y `groups.clients.n: 4`; un `CLIENT_MUTE` al 60 % no cambia nada.
2. **Dado** el 26-10-2026 a las 10:00 de Madrid, **cuando** se pide `/stats/rankings/network-usage?period=day&which=previous`, **entonces** `from` = `2026-10-24T22:00:00Z` y `to` = `2026-10-25T23:00:00Z` (día de 25 h).
3. **Dado** un gateway que oye los mismos 40 nodos el lunes y el martes, **cuando** se pide `/stats/rankings/gateway-coverage?period=week`, **entonces** `value` = 40 (fila `week` de la vista; sumar días daría 80).
4. **Dado** un router con `battery_level` 101 hace 1 h y otro al 30 %, **cuando** se pide `/routers?sort=battery`, **entonces** el del 30 % va primero y el alimentado después con `powered: true` y `level: null`.
5. **Dado** un router oído hace 2 h cuya última telemetría es de hace 13 h, **cuando** se pide `/routers`, **entonces** aparece con `chutil`, `tx` y `battery.*` a `null`.
6. **Dado** `period=year`, **cuando** se pide cualquier endpoint con periodos, **entonces** `400` `invalid_parameter` con `parameters.period`.

---
> Creado: 2026-10-07 · Última revisión: 2026-10-08
