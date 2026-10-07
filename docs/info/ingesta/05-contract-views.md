# 05.5 · Vistas contrato

## Objetivo

Definir con precisión las vistas `api_*` de la base `ingest`: la **única** superficie que lee el portal, con el rol `portal_lector_ingesta` (`../integration.md` §8). Están todas las de §8 con al menos sus columnas; las marcadas con **+** son añadidas. Las tablas internas ([04](04-storage-retention.md)) pueden cambiar sin romper al portal mientras estas vistas se mantengan.

## Especificación

### Reglas comunes

- Todas en `R__vistas_api.sql`, propietario `ingest`, **sin** `security_invoker`: el lector no tiene permisos sobre tablas y la vista se ejecuta con los del propietario.
- Ids `!a1b2c3d4`; horas `timestamptz` (el portal las serializa en ISO 8601 UTC); días `date` en hora local (`{{TZ}}`).
- **Router:** `role` en `{{INFRA_ROLES}}` (`ROUTER`, `ROUTER_LATE`, `REPEATER`). **Nodo de infraestructura:** router o `is_gateway`. `CLIENT_BASE` no es infraestructura.
- Ventanas fijas (12 h, 15 min, 1 h, 24 h, 7 días) calculadas con `now()` dentro de la vista.
- `api_nodes` y las vistas de diagnóstico se consultan filtrando por nodo (`id = $1` o `node_id = $1`); el filtro llega a las subconsultas y evita recorrer todos los nodos.
- Retenciones heredadas: bruto 30 días; posiciones y telemetría 90; agregados por nodo 1 año; sin nodo indefinidos.

### `api_nodes` — una fila por nodo registrado

| Columna | Tipo | Origen / regla |
|---|---|---|
| `id` | text | `node.id` |
| `short_name`, `long_name` | text | Último nodeinfo o map report |
| `role` | text | Null si nunca envió nodeinfo ni map report |
| `hw_model` | text | |
| `firmware` | text | Solo con map report |
| `province` | text | ISO, `FUERA` o null (sin posición válida) |
| `last_position_at` | timestamptz | Última posición válida |
| `position_precision_m` | real | Radio de error |
| `border_uncertain` | boolean | |
| `hop_start_last` | smallint | |
| `is_gateway` | boolean | |
| `first_seen`, `last_seen` | timestamptz | |
| **+** `is_router` | boolean | `role` en `INFRA_ROLES` |
| **+** `hops_min_last` | smallint | |
| **+** `nodeinfo_at` | timestamptz | |
| **+** `heard_by` | text[] | Gateways que lo oyeron en 7 días (`agg_reception_day`, sin él mismo) |

Uso en el mapa: nodos con `province` y `last_position_at` dentro de la ventana (`24h`, `7d`, `30d`); `FUERA` da `outside_andalucia`; recuento de `border_uncertain`. No expone coordenadas.

### `api_province_load` — nodo con provincia y carga reciente para la saturación

| Columna | Tipo | Origen / regla |
|---|---|---|
| `node_id` | text | Nodo con `role` en `{{INFRA_ROLES}}`, `CLIENT` o `CLIENT_BASE` (`CLIENT_MUTE` y resto de roles fuera; ser gateway no cambia nada) |
| `province` | text | Solo los 8 códigos ISO (sin `FUERA` ni null) |
| `channel_utilization` | real | `node.channel_utilization` |
| `measured_at` | timestamptz | `node.metrics_at`, solo si ≥ `now() − 12 h` |
| `grupo` | text | `router` (rol en `{{INFRA_ROLES}}`) o `cliente` (`CLIENT` y `CLIENT_BASE`, mismo grupo) |
| **+** `role`, **+** `is_gateway`, **+** `air_util_tx` | | |

El portal calcula por provincia la media de cada grupo, la saturación ponderada (`../integration.md` §13), el máximo y el nivel.

### `api_routers` — router visto en 7 días

| Columna | Tipo | Origen / regla |
|---|---|---|
| `id`, `short_name`, `long_name`, `role`, `province` | text | `role` en `INFRA_ROLES` y `last_seen ≥ now() − 7 días` |
| `battery_level` | smallint | Último valor (> 100 = alimentado) |
| `voltage` | real | |
| `battery_at` | timestamptz | |
| `channel_utilization`, `air_util_tx` | real | Último valor de `device_metrics` o `local_stats` |
| `metrics_at` | timestamptz | |
| `last_seen` | timestamptz | |
| **+** `hw_model` | text | |
| **+** `powered` | boolean | `battery_level > 100` |
| **+** `is_gateway` | boolean | |
| **+** `last_reboot_at` | timestamptz | |

Valores en bruto con su hora: el corte de 12 h de `/routers` lo aplica el portal con `metrics_at` y `battery_at`.

### `api_gateways` — una fila por gateway

| Columna | Tipo | Origen / regla |
|---|---|---|
| `id` | text | `gateway.id` |
| `short_name`, `long_name` | text | Del nodo del gateway |
| `last_message_at` | timestamptz | |
| `typical_interval_s` | real | Mediana de 100 intervalos; null al principio |
| `packets_last_hour` | integer | Filas de `reception` del gateway con `rx_at ≥ now() − 1 h` |
| `unique_nodes_24h` | integer | `from_id` distintos en `agg_reception_hour` de las últimas 24 h, sin él mismo |
| **+** `first_message_at` | timestamptz | |
| **+** `messages_total` | bigint | |
| **+** `province` | text | Del nodo del gateway |

### `api_summary` — una fila

| Columna | Tipo | Regla |
|---|---|---|
| `nodes_active_24h`, `nodes_active_7d` | integer | `node.last_seen` en la ventana |
| `routers_active_24h` | integer | Routers con `last_seen` en 24 h |
| `gateways_publishing` | integer | `gateway.last_message_at ≥ now() − 15 min` |
| `packets_last_hour` | integer | Paquetes únicos (sin map reports) con `rx_first ≥ now() − 1 h` |
| `generated_at` | timestamptz | `now()` |

### `api_traffic_mix` — periodo × tipo de paquete

`bucket_start` timestamptz, `granularity` text (`hour`, `day`, **+** `week`, **+** `month`), `portnum` text (nombres de `decoded` salvo `map_report`), `packets` bigint, `airtime_s` double precision. Origen `agg_traffic_*` (sin nodo, sin retención); semanas ISO (lunes 00:00 local) y meses naturales a partir de `agg_traffic_day`.

### Rankings `api_rank_<id>`

Columnas comunes: `bucket_start` timestamptz, `granularity` text, `subject_id` text, `value` double precision, `extra` jsonb.

- Filas `hour` desde `agg_*_hour` (30 días) y `day` desde `agg_*_day` (1 año).
- **+** Filas `week` (ISO, lunes 00:00 local) y `month` (natural) calculadas en la vista desde `agg_*_day`, solo para periodos iniciados en los últimos 70 días (actual y anterior; 100 días en `provinces-growth`). En los rankings de recuentos distintos o medias, sumar días da un resultado erróneo: el portal debe usar estas filas.
- Mínimos de datos aplicados en la vista. El portal filtra `granularity` y `bucket_start`, ordena por `value` descendente y limita (10 por defecto, 50 máximo).

| `id` → vista | Sujeto | `value` | `extra` | Origen y reglas | Semana/mes sumando días |
|---|---|---|---|---|---|
| `network-usage` → `api_rank_network_usage` | nodo | s de aire de los paquetes que origina | `{"packets": n, "by_type": {"nodeinfo": s, "position": s, "telemetry": s, "text": s, "traceroute": s, "other": s}}` | `agg_node_*`; cada paquete una vez; sin map reports; `other` agrupa vecinos, routing, resto y no descifrados | Sí (suma) |
| `gateway-coverage` → `api_rank_gateway_coverage` | gateway | nodos distintos oídos | `{"receptions": n}` | `agg_reception_*`, sin él mismo | No |
| `gateway-exclusive` → `api_rank_gateway_exclusive` | gateway | paquetes que solo subió él | `{"receptions": n}` (total subido) | `agg_gateway_*.exclusive` | Sí (suma) |
| `longest-links` → `api_rank_longest_links` | nodo | km del enlace directo más largo | `{"gateway": "!id", "snr": dB}` | `agg_reception_*.max_distance_km` (0 saltos, precisión ≤ 3 km, ≤ 300 km); par con mayor distancia | Sí (máximo) |
| `best-links` → `api_rank_best_links` | nodo | SNR medio (dB) de su mejor enlace directo | `{"gateway": "!id", "receptions": n}` | `snr_direct_sum / direct` por par; solo pares con ≥ 10 recepciones directas | No |
| `most-neighbors` → `api_rank_most_neighbors` | nodo | vecinos directos distintos | `{"neighborinfo": n, "direct_rx": m}` | `agg_neighbor_*` ∪ pares de `agg_reception_*` con `direct > 0` en ambos sentidos | No |
| `uptime` → `api_rank_uptime` | nodo | `uptime_seconds` máximo | `{"readings": n}` | `agg_telemetry_*.max_uptime` | Sí (máximo) |
| `solar-health` → `api_rank_solar_health` | nodo | batería mínima (%) | `{"avg_level": x, "readings": n}` | `agg_telemetry_*`; sin periodos con lecturas > 100; ≥ 3 lecturas (1 en `hour`) | Aprox. (mínimo) |
| `chatters` → `api_rank_chatters` | nodo | mensajes de texto a `^all` | `{"by_channel": {"Cadiz": n}}` | `agg_text_*` | Sí (suma) |
| `new-nodes` → `api_rank_new_nodes` | nodo | `first_seen` en segundos Unix (más reciente primero) | `{"first_seen": "ISO 8601"}` | `node.first_seen` dentro del periodo | Sí (unión) |
| `provinces-growth` → `api_rank_provinces_growth` | provincia (ISO) | nodos activos − nodos activos del periodo anterior | `{"active": n, "previous": m}` | Emisores distintos de `agg_node_*` por `province` (8 códigos) | No |

`api_rank_network_usage` también alimenta "Revisa tu nodo": puesto y desglose de los últimos 7 días (suma de filas `day`).

### Diagnóstico ("Revisa tu nodo")

**`api_node_intervals`** — nodo × tipo × variante, 7 días: `node_id` text, `portnum` text, **+** `variant` text (variante de telemetría; null en otros tipos), `broadcasts` integer, `median_interval_s` real, **+** `last_at` timestamptz. Origen `packet` con `from_id = node_id`, `to_id = '^all'`, `rx_first ≥ now() − 7 días`, sin map reports; mediana (`percentile_cont(0.5)`) de los intervalos entre `rx_first` consecutivos; null con menos de 2 emisiones. El portal compara `telemetry`/`device_metrics`, `nodeinfo` y `position` con las recomendaciones.

**`api_node_battery_daily`** — nodo × día local, 7 días (hoy incluido): `node_id`, `day` date, `min_level` smallint, `avg_level` real, `readings` integer, `readings_below_40` integer, **+** `max_level` smallint, **+** `powered_readings` integer. Origen `agg_telemetry_day`; solo lecturas válidas en mínimos, medias y recuentos.

**`api_node_reboots_daily`** — nodo × día local, 7 días: `node_id`, `day` date, `reboots` integer, **+** `readings` integer (lecturas de telemetría del día: distingue "0 reinicios" de "sin datos"). Origen `agg_telemetry_day`.

### Permisos

Al final de `R__vistas_api.sql` (y de cualquier migración que recree vistas):

```sql
GRANT USAGE ON SCHEMA public TO portal_lector_ingesta;
REVOKE ALL ON ALL TABLES IN SCHEMA public FROM portal_lector_ingesta;
DO $$
DECLARE v text;
BEGIN
  FOR v IN SELECT viewname FROM pg_views
           WHERE schemaname = 'public' AND viewname LIKE 'api\_%' LOOP
    EXECUTE format('GRANT SELECT ON public.%I TO portal_lector_ingesta', v);
  END LOOP;
END $$;
```

El rol lo crea infraestructura sin permisos; los agregados (`agg_*`) no empiezan por `api_` y no se conceden.

## Contratos propios

Las vistas de esta página. Quitar o renombrar una columna exige cambiar antes `../integration.md` §8 y la ficha del portal; añadir columnas o filas no.

## Unidades de trabajo

### UT-05.5.1 — Nodos, carga, routers, gateways y resumen

- **Comportamiento:** `api_nodes`, `api_province_load`, `api_routers`, `api_gateways` y `api_summary` con las reglas de esta página.
- **Detalle:** `INFRA_ROLES` sustituido en la migración; `heard_by` con subconsulta lateral.
- **Casos borde:** router sin posición (fuera de `api_province_load`); `CLIENT_MUTE` (fuera); gateway con rol `CLIENT_MUTE` (fuera); gateway sin nodeinfo (nombres null); nodo con batería 101.
- **Aceptación:** con datos sintéticos, una provincia con routers a 15, 25 y 35 % devuelve esas 3 filas; `api_summary` coincide con consultas manuales.

### UT-05.5.2 — Rankings

- **Comportamiento:** las 11 vistas con filas `hour`, `day`, `week` y `month`.
- **Detalle:** tabla de rankings; mínimos; ventanas de 70 y 100 días.
- **Casos borde:** semana que cruza un cambio de mes; periodo sin datos (sin filas); empate de valores.
- **Aceptación:** con un juego de datos fijo, cada vista devuelve los valores calculados a mano; un periodo cerrado devuelve siempre lo mismo; `by_type` suma `value`.

### UT-05.5.3 — Diagnóstico y tráfico

- **Comportamiento:** `api_node_intervals`, `api_node_battery_daily`, `api_node_reboots_daily`, `api_traffic_mix`.
- **Detalle:** mediana de intervalos por variante; días locales.
- **Casos borde:** nodo con una sola emisión; día solo con lecturas > 100; cambio de hora.
- **Aceptación:** un nodo con telemetría cada 12 min durante 7 días da `median_interval_s ≈ 720` en `device_metrics`; con filtro por nodo cada vista responde en < 50 ms.

### UT-05.5.4 — Permisos

- **Comportamiento:** el lector solo ve `api_*`.
- **Detalle:** bloque de permisos al final de la migración repetible.
- **Casos borde:** vista recreada con `DROP … CASCADE` (pierde el permiso y el bloque lo restaura).
- **Aceptación:** con `portal_lector_ingesta`, `SELECT` sobre cualquier `api_*` funciona y sobre `packet`, `node` o `agg_node_day` da `permission denied`.

## Escenarios de prueba

- **Dado** 3 routers de Cádiz con carga 15, 25 y 35 % medida hace 1 h, **cuando** el portal lee `api_province_load`, **entonces** obtiene 3 filas `ES-CA` con `grupo = 'router'`.
- **Dado** un router cuya última telemetría es de hace 13 h, **cuando** se lee `api_province_load`, **entonces** no aparece.
- **Dado** un gateway que oye los mismos 40 nodos el lunes y el martes, **cuando** se lee `api_rank_gateway_coverage` con `granularity = 'week'`, **entonces** `value = 40` (sumar las dos filas `day` daría 80).
- **Dado** un nodo con 20 recepciones directas a SNR 6 dB y otro con 5 a 10 dB, **cuando** se lee `api_rank_best_links`, **entonces** solo aparece el primero.
- **Dado** el rol lector, **cuando** consulta `api_node_intervals` de un nodo, **entonces** obtiene una fila por tipo (y por variante en telemetría).

---
> Creado: 2026-10-07 · Última revisión: 2026-10-07
