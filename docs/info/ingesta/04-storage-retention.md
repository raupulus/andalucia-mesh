# 05.4 · Persistencia y retención

## Objetivo

Guardar en la base `ingest` (PostgreSQL 17 + TimescaleDB) lo que producen los módulos 01–03, resumirlo en agregados continuos y aplicar la retención (bruto 30 días; posiciones y telemetría 90; agregados por nodo 1 año; agregados sin nodo indefinidos). Las tablas de esta página son **internas**: el único contrato hacia fuera son las vistas de [05](05-contract-views.md). Sin tabla de exclusión de nodos.

## Especificación

### Tablas (esquema `public`, propietario `ingest`)

Ids de nodo y gateway como `text` (`!a1b2c3d4`); horas `timestamptz` (hora del servidor).

**`packet`** — hypertable por `rx_first`, un registro por paquete único.

| Columna | Tipo | Nota |
|---|---|---|
| `rx_first` | timestamptz | Primera recepción |
| `from_id`, `to_id` | text | `to_id` = `^all` o id |
| `packet_id` | bigint | uint32 |
| `channel` | text | Grafía de `ALLOWED_CHANNELS`; null en map report |
| `portnum` | text | Nombre de `decoded`; null sin descifrar |
| `portnum_num` | integer | Número protobuf |
| `variant` | text | Variante de telemetría; null en otros tipos |
| `decrypt_status` | text | `claro`, `descifrado`, `cifrado_desconocido`, `pki` |
| `hop_start`, `hops_min`, `reception_count` | smallint | `hops_min` y `reception_count` se actualizan con recepciones tardías |
| `want_ack`, `via_mqtt`, `ok_to_mqtt` | boolean | |
| `size_bytes` | smallint | 16 + carga |
| `airtime_ms` | real | 0 en map report |
| `first_gateway` | text | Gateway de la primera recepción |
| `province` | text | Provincia del emisor al recibirlo |
| `payload` | jsonb | El `payload` publicado en `decoded` |

Índices: único `(from_id, packet_id, rx_first)`; `(from_id, rx_first DESC)`.

**`reception`** — hypertable por `rx_at`: `rx_at`, `from_id`, `packet_id`, `gateway_id`, `snr` real, `rssi` smallint, `hop_limit` smallint, `hops` smallint, `relay_node` smallint, `gw_rx_time` timestamptz, `own` boolean, `direct` boolean, `distance_km` real. Índices `(gateway_id, rx_at DESC)`, `(from_id, rx_at DESC)`.

**`position`** — hypertable por `at`: `at`, `node_id`, `latitude`/`longitude` double precision, `altitude` integer, `precision_bits` smallint, `precision_m` real, `source` (`position` | `map_report`), `gps_time` timestamptz, `province` text, `border_uncertain` boolean. Solo posiciones válidas.

**`telemetry_device`** — hypertable por `at`: `at`, `node_id`, `battery_level` smallint, `voltage` real, `channel_utilization` real, `air_util_tx` real, `uptime_seconds` bigint, `reboot` boolean.

**`telemetry_env`** — hypertable por `at`: `at`, `node_id`, `metrics` jsonb (`environment_metrics` tal cual).

**`local_stats`** — hypertable por `at`: `at`, `node_id`, `uptime_seconds` bigint, `channel_utilization` y `air_util_tx` real, `num_packets_tx`, `num_packets_rx`, `num_packets_rx_bad`, `num_rx_dupe`, `num_tx_relay`, `num_tx_relay_canceled`, `num_online_nodes`, `num_total_nodes` integer.

**`neighbor`** — hypertable por `at`: `at`, `node_id` (quien informa), `neighbor_id`, `snr` real.

**`node`** — tabla normal, PK `id`; columnas del registro de nodos de [03](03-provinces-registry.md) (`node_num` bigint, nombres, `role`, `hw_model`, `firmware`, `public_key_fp`, posición y provincia, saltos, `is_gateway`, batería, carga, uptime, fechas).

**`gateway`** — tabla normal, PK `id`: `first_message_at`, `last_message_at`, `messages_total` bigint, `typical_interval_s` real.

**`schema_migrations`** — `version` text PK, `checksum` text, `applied_at` timestamptz.

Otras variantes de telemetría (potencia, calidad del aire, salud, host, gestión de tráfico) solo quedan en `packet.payload` (30 días).

### Fragmentación, compresión y retención

| Objeto | Chunk | Compresión (`segmentby` / `orderby`) | Retención |
|---|---|---|---|
| `packet` | 1 día | A los 2 días (`from_id` / `rx_first DESC`) | 30 días |
| `reception` | 1 día | A los 2 días (`gateway_id` / `rx_at DESC`) | 30 días |
| `neighbor` | 1 día | A los 2 días (`node_id` / `at DESC`) | 30 días |
| `position`, `telemetry_device`, `telemetry_env`, `local_stats` | 7 días | A los 7 días (`node_id` / `at DESC`) | 90 días |
| `agg_*_hour` (con nodo o gateway) | — | — | 30 días |
| `agg_*_day` (con nodo o gateway) | — | — | 365 días |
| `agg_traffic_hour`, `agg_traffic_day` (sin nodo) | — | — | Indefinida |
| `node`, `gateway` | — | — | Tarea diaria: borra lo inactivo 365 días |

- Políticas con `add_compression_policy` y `add_retention_policy` (en TimescaleDB ≥ 2.18 sus equivalentes `columnstore` sirven igual).
- Las actualizaciones tardías ocurren en los 15 min siguientes: nunca tocan fragmentos comprimidos.

### Agregados continuos

Cada uno existe en dos versiones: `_hour` (`time_bucket('1 hour', t)`) y `_day` (`time_bucket('1 day', t, '{{TZ}}')`, día local de Madrid). Los `_day` se calculan sobre la hypertable, no sobre el `_hour` (más simple; el bruto dura más que la ventana de refresco).

| Agregado | Origen y filtro | Agrupa por | Columnas |
|---|---|---|---|
| `agg_node_*` | `packet`, sin map reports | `bucket`, `from_id`, `province`, `portnum` (null → `other`) | `packets`, `airtime_s` |
| `agg_reception_*` | `reception` con `own = false` | `bucket`, `gateway_id`, `from_id` | `receptions`, `direct` (recuento con `direct`), `snr_direct_sum`, `max_distance_km` |
| `agg_gateway_*` | `packet`, sin map reports | `bucket`, `first_gateway` | `packets`, `exclusive` (`reception_count = 1`) |
| `agg_text_*` | `packet` con `portnum = 'text'` y `to_id = '^all'` | `bucket`, `from_id`, `channel` | `messages` |
| `agg_telemetry_*` | `telemetry_device` | `bucket`, `node_id` | `readings` (batería válida), `min_level`, `max_level`, `sum_level`, `readings_below_40`, `powered_readings` (> 100), `max_uptime`, `reboots` |
| `agg_neighbor_*` | `neighbor` | `bucket`, `node_id`, `neighbor_id` | `reports`, `snr_sum` |
| `agg_traffic_*` | `packet`, sin map reports | `bucket`, `portnum` (null → `other`) | `packets`, `airtime_s` |

- Batería válida: 1–100, o 0 con `voltage` > 0 ([02](02-decoding-dedup.md)); `min_level`, `sum_level` y `readings_below_40` solo con lecturas válidas.
- Todos con `timescaledb.materialized_only = false`: el periodo en curso se completa en tiempo real.
- Refresco: `_hour` cada 10 min (`start_offset` 3 h, `end_offset` 5 min); `_day` cada 30 min (`start_offset` 3 días, `end_offset` 1 h). Las recepciones tardías (≤ 15 min) quedan dentro de la ventana.
- `max_distance_km` solo de recepciones `direct` con distancia; `snr_direct_sum` / `direct` = SNR medio del enlace.

### Tareas programadas

- Políticas de TimescaleDB para refresco, compresión y retención (unas 40, escalonadas).
- `limpiar_registros` (procedimiento con `add_job`, diario a las 04:15 hora local): borra `node` con `last_seen` anterior a 365 días y `gateway` con `last_message_at` anterior a 365 días.
- Requisito de infraestructura: `timescaledb.max_background_workers` ≥ 16.

### Escritura

- Hypertables: `COPY` por lotes (`asyncpg.copy_records_to_table`), cada `INGESTA_LOTE_MS` o `INGESTA_LOTE_FILAS`.
- Recepciones tardías: `UPDATE packet SET reception_count = …, hops_min = … FROM (VALUES …)` por lote, con la clave `(from_id, packet_id, rx_first)`.
- `node` y `gateway`: `INSERT … ON CONFLICT (id) DO UPDATE` de las filas modificadas cada 5 s.
- Conflicto de clave única en un `COPY` → ese lote se repite con `INSERT … ON CONFLICT DO NOTHING`.

### Migraciones

- Directorio `services/ingesta/migrations/`: archivos versionados `NNNN_descripcion.sql` y uno repetible, `R__vistas_api.sql` ([05](05-contract-views.md)).
- Runner propio al arrancar: `pg_advisory_lock(hashtext('ingesta-migraciones'))`; aplica en orden los pendientes; cada archivo en su transacción salvo los que empiezan por `-- sin-transaccion` (TimescaleDB no crea agregados continuos dentro de una transacción); registra versión y checksum.
- Sustitución previa de `{{TZ}}` e `{{INFRA_ROLES}}` desde el entorno. El repetible se vuelve a ejecutar cuando cambia su checksum ya sustituido (por ejemplo, al cambiar `INFRA_ROLES`).
- Checksum distinto en una versionada ya aplicada → error y parada.
- `CREATE EXTENSION timescaledb` lo hace infraestructura (exige superusuario). La primera migración comprueba `pg_extension` y `current_setting('timescaledb.license') = 'timescale'` y aborta con un mensaje claro si falla.
- Cambiar `TZ` después exige una migración que recree los `_day` (se recalculan solo los últimos 30 días de bruto).

| Archivo inicial | Contenido |
|---|---|
| `0001_comprobaciones_y_tablas.sql` | Comprobación de extensión y licencia; tablas e índices |
| `0002_hypertables.sql` | `create_hypertable`, compresión, retención |
| `0003_agregados.sql` (`-- sin-transaccion`) | Los 14 agregados continuos `WITH NO DATA` |
| `0004_politicas_agregados.sql` | Refresco y retención de agregados |
| `0005_tareas.sql` | `limpiar_registros` y su `add_job` |
| `R__vistas_api.sql` | Vistas `api_*` y permisos |

## Contratos propios

Ninguno hacia fuera: tablas y agregados pueden cambiar sin aviso mientras las vistas `api_*` mantengan sus columnas. El portal no tiene permisos sobre nada de esta página.

## Unidades de trabajo

### UT-05.4.1 — Esquema e hypertables

- **Comportamiento:** las tablas e índices de esta página, con sus hypertables y políticas de compresión y retención.
- **Detalle:** migraciones 0001 y 0002; chunks de 1 y 7 días.
- **Casos borde:** extensión ausente o licencia Apache → aborta con mensaje claro.
- **Aceptación:** `timescaledb_information.hypertables` lista las 7 hypertables y `jobs` sus políticas.

### UT-05.4.2 — Agregados continuos

- **Comportamiento:** los 7 agregados en versión hora y día, con agregación en tiempo real.
- **Detalle:** migraciones 0003 y 0004; zona horaria en los diarios.
- **Casos borde:** recepción tardía que invalida una hora ya materializada; cambio de hora de verano (el día local dura 23 o 25 h).
- **Aceptación:** para un día de prueba, cada agregado coincide con la misma consulta sobre el bruto.

### UT-05.4.3 — Retención, compresión y limpieza de registros

- **Comportamiento:** los datos desaparecen en sus plazos y los registros inactivos se borran.
- **Detalle:** políticas y `limpiar_registros`.
- **Casos borde:** nodo inactivo 364 días (se conserva); agregado diario de hace 13 meses (borrado); `agg_traffic_day` de hace 2 años (se conserva).
- **Aceptación:** con datos sintéticos fechados, una ejecución manual de cada tarea deja exactamente lo esperado.

### UT-05.4.4 — Runner de migraciones

- **Comportamiento:** aplica versionadas una vez y el repetible cuando cambia.
- **Detalle:** bloqueo consultivo, `-- sin-transaccion`, sustitución de marcadores, checksums.
- **Casos borde:** dos contenedores a la vez; migración fallida a medias (transacción revertida; en las sin transacción, deben ser idempotentes con `IF NOT EXISTS`).
- **Aceptación:** base vacía → esquema completo; segundo arranque → ningún cambio; cambiar `INFRA_ROLES` → vistas recreadas y permisos restaurados.

## Escenarios de prueba

- **Dado** una base vacía con TimescaleDB, **cuando** arranca el servicio, **entonces** existen tablas, hypertables, 14 agregados, políticas y vistas.
- **Dado** el paquete de 3 recepciones de [02](02-decoding-dedup.md), **cuando** se escribe, **entonces** `packet.reception_count = 3` y hay 3 filas en `reception`.
- **Dado** datos de hace 31 días en `packet`, **cuando** corre la retención, **entonces** desaparecen y los agregados diarios de ese día siguen.
- **Dado** un nodo sin actividad desde hace 366 días, **cuando** corre `limpiar_registros`, **entonces** su fila de `node` desaparece.
- **Dado** PostgreSQL con TimescaleDB en edición Apache, **cuando** arranca el servicio, **entonces** se detiene con un mensaje que pide la edición Community.

---
> Creado: 2026-10-07 · Última revisión: 2026-10-08
