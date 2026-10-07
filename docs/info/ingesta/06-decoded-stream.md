# 05.6 · Flujo `decoded` y salud

## Objetivo

Publicar un mensaje JSON por paquete único en `snm/v1/decoded/<portnum>` para el detector de alertas (`../integration.md` §6), y exponer `/health` con el estado y los contadores del servicio (§11).

## Especificación

### Publicación

| Elemento | Valor |
|---|---|
| Topic | `${MQTT_TOPIC_PREFIX}/v1/decoded/<portnum>` = `snm/v1/decoded/<portnum>` |
| `<portnum>` | `nodeinfo`, `position`, `telemetry`, `text`, `neighborinfo`, `traceroute`, `routing`, `map_report`, `other` (minúsculas, sin sufijo) |
| QoS / retained | 0 / no |
| Conexión | La misma de la entrada (`svc-ingest`, escritura en `snm/v1/decoded/#`) |
| Momento | Al cerrar la ventana de 2 s desde la primera recepción; retraso total < 5 s |
| Codificación | JSON UTF-8 compacto, claves en inglés; típico < 2 KB |

- **Se publica:** todo paquete único aceptado, incluidos `portnum` desconocidos y paquetes no descifrables (`other`, `payload: {}`).
- **No se publica:** PKI, lo descartado por topic, gateway, canal, cabecera u OK to MQTT, ni las recepciones tardías.
- Broker desconectado al cerrar la ventana → el mensaje se pierde (`decoded_perdidos`); no hay cola. La escritura en la base sigue.
- Orden: el de cierre de ventana (≈ orden de `rx_first`).

### Mensaje

```json
{
  "v": 1,
  "packet_id": 3735928559,
  "from": "!a1b2c3d4",
  "from_node": {"short": "CAD1", "long": "Repetidor Sierra Cádiz", "role": "ROUTER", "hw": "HELTEC_V3", "is_gateway": false, "province": "ES-CA"},
  "to": "^all",
  "portnum": "telemetry",
  "channel": "SFNarrow",
  "rx_first": "2026-10-01T15:00:00.120Z",
  "hop_start": 3,
  "hops_min": 1,
  "via_mqtt": false,
  "ok_to_mqtt": true,
  "airtime_ms": 231.9,
  "receptions": [
    {"gateway": "!0badc0de", "snr": 6.5, "rssi": -98, "hops": 1, "at": "2026-10-01T15:00:00.120Z"},
    {"gateway": "!0c0ffee0", "snr": -4.25, "rssi": -117, "hops": 2, "at": "2026-10-01T15:00:01.480Z"}
  ],
  "payload": {
    "device_metrics": {"battery_level": 37, "voltage": 3.61, "channel_utilization": 18.2, "air_util_tx": 2.1, "uptime_seconds": 84}
  }
}
```

| Campo | Tipo | Null cuando | Regla |
|---|---|---|---|
| `v` | int | — | Versión del esquema: 1 |
| `packet_id` | int | — | uint32 |
| `from`, `to` | string | — | `!a1b2c3d4`; `to` = `^all` en difusión |
| `from_node` | objeto | — | Registro de nodos tras aplicar el paquete ([03](03-provinces-registry.md)); cada campo null si se desconoce, salvo `is_gateway` (booleano) |
| `portnum` | string | — | Igual que el último nivel del topic |
| `channel` | string | Map report | Grafía de `ALLOWED_CHANNELS` |
| `rx_first` | string | — | ISO 8601 UTC con milisegundos, hora del servidor |
| `hop_start` | int | Firmware antiguo, map report | |
| `hops_min` | int | Sin `hop_start` | Mínimo de las recepciones de la ventana |
| `via_mqtt` | bool | — | OR de las recepciones |
| `ok_to_mqtt` | bool | No descifrado, map report | Bit 0 de `bitfield` |
| `airtime_ms` | número | — | 1 decimal; 0 en map report ([02](02-decoding-dedup.md)) |
| `receptions` | lista | — | Recepciones dentro de la ventana, por `at`; `snr`, `rssi`, `hops` null en paquetes propios del gateway y en map reports |
| `payload` | objeto | — | Según tipo; `{}` si no se decodifica |

Las claves del sobre están siempre presentes (con `null` si procede). Dentro de `payload` solo aparecen los campos recibidos.

### `payload` por tipo

| `portnum` | Ejemplo de `payload` |
|---|---|
| `nodeinfo` | `{"id": "!a1b2c3d4", "long_name": "Repetidor Sierra Cádiz", "short_name": "CAD1", "hw_model": "HELTEC_V3", "role": "ROUTER", "is_licensed": false, "public_key_fp": "3f9a0c1d2e4b5a69"}` |
| `position` | `{"latitude": 36.7612, "longitude": -5.3921, "altitude": 950, "precision_bits": 16, "precision_m": 364, "time": "2026-10-01T14:59:58Z", "sats_in_view": 9}` |
| `telemetry` (dispositivo) | `{"device_metrics": {"battery_level": 101, "voltage": 4.2, "channel_utilization": 22.5, "air_util_tx": 3.1, "uptime_seconds": 86400}}` |
| `telemetry` (entorno) | `{"environment_metrics": {"temperature": 21.4, "relative_humidity": 63.0, "barometric_pressure": 1016.2}}` |
| `telemetry` (estadísticas locales) | `{"local_stats": {"uptime_seconds": 86400, "channel_utilization": 21.3, "air_util_tx": 1.9, "num_packets_tx": 512, "num_packets_rx": 9830, "num_packets_rx_bad": 41, "num_online_nodes": 63, "num_total_nodes": 180}}` |
| `text` | `{"text": "Buenas desde la sierra"}` (con `reply_id` y `emoji` si vienen) |
| `neighborinfo` | `{"broadcast_interval_s": 21600, "neighbors": [{"id": "!0badc0de", "snr": 7.25}]}` |
| `traceroute` | `{"route": ["!0badc0de"], "snr_towards": [6.25, 4.0], "route_back": [], "snr_back": []}` |
| `routing` | `{"error_reason": "NO_RESPONSE", "request_id": 3735928000}` |
| `map_report` | `{"long_name": "Gateway Costa", "short_name": "GWC1", "role": "CLIENT_MUTE", "hw_model": "HELTEC_V3", "firmware_version": "2.7.x", "region": "EU_868", "modem_preset": "LONG_FAST", "has_default_channel": false, "latitude": 36.6, "longitude": -6.3, "altitude": 20, "position_precision": 14, "num_online_local_nodes": 57, "has_opted_report_location": true}` |
| `other` | `{}` |

- Otras variantes de telemetría (`power_metrics`, `air_quality_metrics`, `health_metrics`, `host_metrics`, `traffic_management_stats`) con sus campos tal cual, bajo su nombre.
- Telemetría: una sola variante por mensaje. `precision_m` se añade calculado; `public_key_fp` sustituye a la clave.

Map report (sobre): `"channel": null`, `"hop_start": null`, `"hops_min": null`, `"via_mqtt": false`, `"ok_to_mqtt": null`, `"airtime_ms": 0`, `"receptions": [{"gateway": "!0badc0de", "snr": null, "rssi": null, "hops": null, "at": "…"}]`.

### Salud

`GET /health` en `0.0.0.0:${HEALTH_PORT}` (8080), solo alcanzable en la red `mesh`, sin autenticación (servidor `aiohttp`).

```json
{
  "ok": true,
  "servicio": "ingesta",
  "version": "1.0.0",
  "mqtt": {"conectado": true, "desde": "2026-10-01T09:12:03Z"},
  "bd": {"ok": true, "ultimo_lote": "2026-10-01T15:00:01Z", "filas_pendientes": 120},
  "ultimo_mensaje_s": 1.4,
  "mensajes_s": 38.2,
  "paquetes_unicos_s": 6.1,
  "ventanas_abiertas": 14,
  "cache_dedup": 4210,
  "contadores": {"mensajes": 1520331, "paquetes_unicos": 240118, "duplicados": 1270004, "canal_no_permitido": 0, "sin_ok_mqtt": 3, "cifrado_desconocido": 12, "decoded_publicados": 240100, "decoded_perdidos": 0, "filas_descartadas_bd": 0}
}
```

- `ok = false` y código `503` si MQTT lleva desconectado más de 60 s o la base falla durante más de 60 s; si no, `200`.
- `ultimo_mensaje_s`: segundos desde el último mensaje de `msh/…`. No cambia `ok` (una malla callada no es un fallo del servicio); el panel lo marca en rojo por encima de 600 s.
- Ritmos: media móvil de 60 s. Contadores: acumulados desde el arranque; `contadores` incluye todos los definidos en [01](01-input-decryption.md), [02](02-decoding-dedup.md) y [03](03-provinces-registry.md), más `decoded_publicados`, `decoded_perdidos`, `filas_descartadas_bd` y `error_interno`.
- Sin `/metrics` Prometheus (el proyecto no tiene Prometheus); los mismos contadores van al log de resumen cada 60 s.
- El `healthcheck` de Docker usa `http://127.0.0.1:8080/health` (ver README §8).

## Contratos propios

- Topic y mensaje de esta página (copia fiel de `../integration.md` §6 con las reglas de nulos y por tipo).
- `GET /health` (`../integration.md` §11).
- Consumidor: `detector-alertas` con `svc-detector` (lectura `snm/v1/decoded/#`).

## Unidades de trabajo

### UT-05.6.1 — Construcción y publicación del mensaje

- **Comportamiento:** al cerrar cada ventana, un mensaje en su topic con el sobre completo.
- **Detalle:** serialización compacta; horas con milisegundos y `Z`; `from_node` del registro.
- **Casos borde:** broker caído (contador, sin cola); `hop_start` null; nodo desconocido (`from_node` con nulls e `is_gateway: false`); map report.
- **Aceptación:** un validador de esquema JSON (en las pruebas) acepta todos los mensajes de la batería extremo a extremo; ningún mensaje para PKI ni descartados.

### UT-05.6.2 — `payload` por tipo

- **Comportamiento:** cada tipo produce el `payload` de la tabla.
- **Detalle:** omitir campos ausentes; redondeo de flotantes; SNR de traceroute en dB.
- **Casos borde:** telemetría con variante sin campos; texto vacío; neighborinfo sin vecinos.
- **Aceptación:** un caso de prueba por fila de la tabla compara el JSON exacto.

### UT-05.6.3 — Salud y contadores

- **Comportamiento:** `/health` refleja conexión, base, ritmo y contadores; Docker y el panel lo usan.
- **Detalle:** reglas de `ok` y código HTTP de esta página; resumen en el log cada 60 s.
- **Casos borde:** base caída 30 s (sigue `200`) y 90 s (`503`); arranque sin mensajes aún (`ultimo_mensaje_s` null).
- **Aceptación:** con MQTT parado, `/health` pasa a `503` en ≤ 90 s y vuelve a `200` al reconectar.

## Escenarios de prueba

- **Dado** un paquete de telemetría oído por dos gateways en 1,4 s, **cuando** se cierra la ventana, **entonces** `snm/v1/decoded/telemetry` recibe un mensaje con 2 recepciones y `from_node` del emisor.
- **Dado** un nodeinfo que cambia el nombre corto, **cuando** se publica, **entonces** `from_node.short` ya es el nuevo.
- **Dado** un map report, **cuando** se publica en `snm/v1/decoded/map_report`, **entonces** lleva `channel: null`, `airtime_ms: 0` y una recepción con radio a null.
- **Dado** un paquete con `portnum` 8 (sin decodificar), **cuando** se publica, **entonces** va a `snm/v1/decoded/other` con `payload: {}`.
- **Dado** MQTT caído 2 min, **cuando** se consulta `/health`, **entonces** responde `503` con `"ok": false` y `mqtt.conectado: false`.

---
> Creado: 2026-10-07 · Última revisión: 2026-10-07
