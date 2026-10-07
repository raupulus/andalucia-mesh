# 05.2 · Decodificación y deduplicación

## Objetivo

Convertir cada paquete nuevo en un **paquete único** con sus campos por tipo, agrupar las recepciones de todos los gateways y calcular saltos, enlaces directos, tiempo en el aire (`airtime_ms`) y reinicios. Es la base de lo que se guarda ([04](04-storage-retention.md)) y se publica ([06](06-decoded-stream.md)).

## Especificación

### Tipos decodificados

| `portnum` protobuf (nº) | En `decoded` | Mensaje | Se extrae | Tabla de detalle |
|---|---|---|---|---|
| `TEXT_MESSAGE_APP` (1) | `text` | UTF-8 | texto (bytes inválidos → U+FFFD), `reply_id`, `emoji` | — |
| `POSITION_APP` (3) | `position` | `Position` | `latitude_i`/`longitude_i` × 1e-7, `altitude`, `precision_bits`, `time`, `sats_in_view` | `position` |
| `NODEINFO_APP` (4) | `nodeinfo` | `User` | `id`, `long_name`, `short_name`, `hw_model` y `role` (nombre del enum; `role` ausente = `CLIENT`), `is_licensed`, huella de `public_key` | `node` |
| `ROUTING_APP` (5) | `routing` | `Routing` | `error_reason` (nombre), `request_id` del `Data` | — |
| `TELEMETRY_APP` (67) | `telemetry` | `Telemetry` | la variante presente con sus campos: `device_metrics`, `environment_metrics`, `local_stats`, `power_metrics`, `air_quality_metrics`, `health_metrics`, `host_metrics`, `traffic_management_stats` | `telemetry_device`, `telemetry_env`, `local_stats` |
| `TRACEROUTE_APP` (70) | `traceroute` | `RouteDiscovery` | `route`, `snr_towards`, `route_back`, `snr_back` (SNR en cuartos de dB → dB) | — |
| `NEIGHBORINFO_APP` (71) | `neighborinfo` | `NeighborInfo` | vecinos (`node_id`, `snr`), `node_broadcast_interval_secs` | `neighbor` |
| `MAP_REPORT_APP` (73) | `map_report` | `MapReport` | todos sus campos (nombres, rol, hw, firmware, región, preset, posición, precisión, nodos locales) | `node`, `position` |
| Resto (incluido `TEXT_MESSAGE_COMPRESSED_APP`) | `other` | — | nada; se guarda el número de `portnum` | — |

- Error al parsear el mensaje interno → se trata como `other` y sube `error_decodificacion`.
- Campos `optional` ausentes no se emiten (ni como `null`); flotantes redondeados a 3 decimales.
- Huella de clave pública: primeros 16 caracteres hex del SHA-256 de `public_key`. No se guardan ni publican la clave, `macaddr` ni mensajes de administración.
- `User.id` distinto de `from` → manda `from` (`nodeinfo_id_distinto`).
- La variante de telemetría se guarda como `variant` en `packet` (necesaria para los intervalos de "Revisa tu nodo").

### Campos de cabecera del paquete único

| Campo | Origen | Regla |
|---|---|---|
| `packet_id` | `MeshPacket.id` | uint32 |
| `from` / `to` | cabecera | `!%08x`; `to = 0xFFFFFFFF` → `^all` |
| `hop_start` | primera recepción | 0 o ausente (firmware antiguo) → null |
| `hops` (por recepción) | `hop_start − hop_limit` | null si `hop_start` es null o el resultado sale de 0–7 |
| `hops_min` | mínimo de las recepciones | null si todas son null; se actualiza con recepciones tardías |
| `via_mqtt` | OR de las recepciones | |
| `want_ack` | primera recepción | |
| `ok_to_mqtt` | bit 0 de `Data.bitfield` | null sin `Data` (no descifrado) y en map reports |
| `size_bytes` | 16 + longitud de la carga | ver tiempo en el aire |

### Bit OK to MQTT (segunda barrera)

- Con `Data` disponible, si `from` ≠ gateway de la primera recepción y (`bitfield` ausente o bit 0 = 0) → **descartado**: ni se guarda ni se publica (`sin_ok_mqtt`). La entrada de deduplicación queda marcada para ignorar también sus duplicados.
- Es la misma regla que aplica el firmware de un gateway que sube a un broker en IP pública (nuestro caso); aquí solo atrapa gateways antiguos o modificados. No es un mecanismo de exclusión: respeta lo que pidió el emisor.
- Los paquetes propios del gateway (`from` = gateway) se aceptan siempre, como en el firmware.

### Deduplicación

- Clave `(from, packet_id)`, ventana `INGESTA_VENTANA_DEDUP_MIN` = 15 min desde `rx_first` (ids aleatorios de 32 bits por nodo).
- **Entrada unificada:** aplica tanto al tráfico de radio local (`msh/EU_868/#`) como a los eventos sincronizados de peers (`snm/v1/peer/#`).
- Entrada de caché: `rx_first`, gateways ya vistos, estado (`ventana`, `publicado`, `descartado`), `reception_count`, `hops_min`.
- Primera recepción → abre la ventana de publicación (`INGESTA_VENTANA_PUBLICACION_S` = 2 s). Al cerrarse: mensaje `decoded` (06) y filas a 04.
- Recepción posterior (sea por radio local o por sincronización de peer posterior): origen ya presente o paquete ya publicado → se ignora (`recepcion_repetida`); si llega por un gateway nuevo dentro de la ventana → entra en `receptions`; tras el cierre (**tardía**) → fila de `reception` y actualización de `reception_count` y `hops_min` de `packet`, sin republicar en `decoded`.
- Si un paquete solo fue escuchado por un peer y nunca por nuestros gateways locales: entra como paquete nuevo, se guarda en la base `ingest` y se emite exactamente **1 sola vez** al flujo `decoded` (alimentando el detector de alertas, bots y el chat en directo).
- Pasados 15 min la entrada se borra; un `(from, id)` posterior cuenta como paquete nuevo.
- Al arrancar se precargan desde la base los paquetes y recepciones de los últimos 15 min (un reinicio no duplica).
- La deduplicación va antes de descifrar ([01](01-input-decryption.md)). Tamaño: unos 5.000 elementos al ritmo de referencia.

### Recepciones

- Una por (paquete, gateway): `gateway`, `snr` (`rx_snr`), `rssi` (`rx_rssi`), `hop_limit`, `hops`, `relay_node` (1 byte, solo se guarda), `rx_time` del gateway (solo diagnóstico) y `at` (hora del servidor, UTC). Toda hora de los contratos es del servidor.
- Paquete propio del gateway (`from` = gateway): `snr` y `rssi` a null (el firmware pone 0), `hops = 0`, `own = true`, nunca enlace.
- Map report: no genera fila de `reception` (no llega por radio). En `decoded` figura el gateway con `snr`, `rssi` y `hops` a null.

### Enlaces directos

- `hops = 0`, `from` ≠ gateway y `via_mqtt = false` → **enlace RF directo** nodo → gateway (`direct = true`).
- Distancia haversine (R = 6.371 km) si ambos tienen posición válida con precisión ≤ `INGESTA_ENLACE_PRECISION_MAX_M` (3 km), tomada del registro de nodos ([03](03-provinces-registry.md)) en ese momento.
- Distancia > `INGESTA_ENLACE_MAX_KM` (300 km) → `distance_km` null y `enlace_sospechoso` (posición falsa o MQTT mal marcado).
- Alimenta `longest-links`, `best-links` y `most-neighbors` ([05](05-contract-views.md)).

### Tiempo en el aire (`airtime_ms`)

Fórmula de Semtech (AN1200.13) con los parámetros de `.env.comun`:

| Parámetro | Variable | SFNarrow |
|---|---|---|
| Ancho de banda BW | `LORA_BANDWIDTH` (código del firmware: 31 → 31,25 kHz, 62 → 62,5 kHz; resto, el valor en kHz) | 62,5 kHz |
| Factor de dispersión SF | `LORA_SPREAD_FACTOR` | 7 |
| Tasa de codificación | `LORA_CODING_RATE` (5 → 4/5, CR = 1; 8 → 4/8, CR = 4) | 4/5 |
| Preámbulo n_pre | `INGESTA_LORA_PREAMBULO` | 16 símbolos |
| Cabecera LoRa | explícita (IH = 0) | — |
| CRC | activado (CRC = 1) | — |
| Optimización de baja tasa DE | 1 si T_sym > 16 ms | 0 |

```text
T_sym      = 2^SF / BW                                        SFNarrow: 2,048 ms
T_pre      = (n_pre + 4,25) · T_sym                           SFNarrow: 41,472 ms
n_carga    = 8 + max(ceil((8·PL − 4·SF + 28 + 16·CRC − 20·IH) / (4·(SF − 2·DE))) · (CR + 4), 0)
airtime_ms = T_pre + n_carga · T_sym
PL         = 16 (cabecera de radio) + longitud de la carga
```

- **Carga:** longitud de `encrypted` (cifrado de canal o PKI); si llegó en claro, longitud de `Data` serializado (CTR conserva la longitud; la re-serialización puede variar ±2 B). **Map report → 0** (no se emite por radio).
- Un solo preset en toda la zona: los 14 canales comparten radio. Se publica redondeado a 0,1 ms.

Valores de referencia SFNarrow (pruebas):

| PL (B) | Símbolos de carga | `airtime_ms` |
|---|---|---|
| 30 | 58 | 160,3 |
| 56 | 93 | 231,9 |
| 100 | 158 | 365,1 |
| 253 (máximo: 16 + 237) | 373 | 805,4 |

### Reinicios

- Con cada `device_metrics.uptime_seconds` > 0 se compara con la lectura anterior del nodo (registro de 03): `esperado = uptime_anterior + (at − at_anterior)`. Si `uptime < esperado − max(300 s, 5 % de (at − at_anterior))` → **reinicio**.
- Se marca `telemetry_device.reboot = true` y `node.last_reboot_at`. Varios reinicios entre dos lecturas cuentan como uno.
- Primera lectura del nodo: sin comparación. Lectura con `at` ≤ `at_anterior`: no se evalúa. `local_stats.uptime_seconds` no se usa.

### Telemetría: valores derivados

- `battery_level` > 100 → alimentado externamente (`powered`).
- Lectura de batería válida: 1–100, o 0 con `voltage` > 0 (0 con 0 V = sin sensor).
- `channel_utilization` y `air_util_tx` llegan en `device_metrics` y en `local_stats`; el registro guarda el más reciente.

## Contratos propios

Paquete único (estructura interna que consumen 04 y 06): cabecera de la tabla anterior, `channel`, `portnum` (nombre y número), `variant`, `decrypt_status`, `payload` (dict ya con los nombres de 06), `airtime_ms`, `size_bytes`, `rx_first`, `province` del emisor, `first_gateway`, `reception_count` y lista de recepciones.

Contadores: `paquetes_unicos`, `duplicados`, `recepcion_repetida`, `recepciones_tardias`, `sin_ok_mqtt`, `error_decodificacion`, `nodeinfo_id_distinto`, `enlace_sospechoso`, `reinicios`.

## Unidades de trabajo

### UT-05.2.1 — Decodificación por tipo

- **Comportamiento:** produce el `payload` de cada tipo de la tabla y las filas de detalle.
- **Detalle:** protobufs de `meshtastic.protobuf`; nombres de enum como texto.
- **Casos borde:** `Telemetry` sin variante; `Position` sin `latitude_i`; texto con UTF-8 inválido; `portnum` desconocido.
- **Aceptación:** un caso de prueba por tipo y variante con su `payload` esperado (JSON de [06](06-decoded-stream.md)).

### UT-05.2.2 — Bit OK to MQTT

- **Comportamiento:** descarta paquetes ajenos sin consentimiento.
- **Detalle:** regla de esta página; marca la entrada de deduplicación.
- **Casos borde:** `bitfield` ausente; paquete propio del gateway sin el bit; duplicado de un descartado.
- **Aceptación:** el paquete ajeno sin bit no llega ni a la base ni a `decoded`; el propio del gateway sí.

### UT-05.2.3 — Deduplicación, ventana y recepciones tardías

- **Comportamiento:** un paquete = un `packet` y un mensaje `decoded`; N gateways = N `reception`.
- **Detalle:** caché con caducidad de 15 min; temporizador de 2 s por paquete; actualización por lotes de `reception_count` y `hops_min`; precarga al arrancar.
- **Casos borde:** recepción a los 14 min 59 s y a los 15 min 1 s; mismo gateway dos veces; reinicio con ventanas abiertas (se publican al apagar).
- **Aceptación:** con 3 gateways a 0 s, 1 s y 5 s: un `decoded` con 2 recepciones, 3 filas de `reception` y `reception_count = 3`.

### UT-05.2.4 — Saltos y enlaces directos

- **Comportamiento:** calcula `hops`, `hops_min`, `direct` y `distance_km`.
- **Detalle:** reglas de esta página; posiciones del registro.
- **Casos borde:** `hop_start = 0`; `hop_limit > hop_start`; nodo sin posición; distancia de 400 km; paquete propio del gateway.
- **Aceptación:** la distancia coincide con un cálculo haversine manual (±0,1 km) en tres casos.

### UT-05.2.5 — Tiempo en el aire

- **Comportamiento:** `airtime_ms` por paquete con los parámetros de radio comunes.
- **Detalle:** fórmula y mapeo de códigos de ancho de banda y tasa de codificación.
- **Casos borde:** map report (0); PKI; carga vacía.
- **Aceptación:** los cuatro valores de la tabla de referencia exactos; ±5 % frente a una calculadora LoRa externa con SF 7, 62,5 kHz, CR 4/5, preámbulo 16.

### UT-05.2.6 — Reinicios

- **Comportamiento:** detecta reinicios por caída de `uptime_seconds`.
- **Detalle:** regla del esperado con tolerancia; estado previo en el registro de nodos.
- **Casos borde:** primera lectura; reinicio tras un hueco de 12 h en que el uptime ya superó al anterior; lecturas desordenadas.
- **Aceptación:** secuencia 3.600 s → (1 h después) 120 s marca un reinicio; 3.600 s → (1 h después) 7.150 s no.

## Escenarios de prueba

- **Dado** un paquete de posición oído por 2 gateways en 1 s, **cuando** se cierra la ventana, **entonces** se publica una vez con 2 recepciones y `hops_min` igual al menor.
- **Dado** una cuarta recepción a los 6 s, **cuando** llega, **entonces** se guarda, `reception_count` pasa a 3 y no hay segundo mensaje `decoded`.
- **Dado** un paquete ajeno con `bitfield` sin el bit 0, **cuando** llega, **entonces** sube `sin_ok_mqtt` y no queda rastro en la base.
- **Dado** un paquete de 40 B de carga en SFNarrow, **cuando** se procesa, **entonces** `airtime_ms` = 231,9 (PL = 56).
- **Dado** un router con uptime 86.400 s y, 1 h después, 60 s, **cuando** llega la segunda lectura, **entonces** `reboot = true` y `last_reboot_at` se actualiza.
- **Dado** un map report, **cuando** se procesa, **entonces** `airtime_ms = 0`, no hay fila de `reception` y el nodo actualiza firmware y posición.

---
> Creado: 2026-10-07 · Última revisión: 2026-10-07
