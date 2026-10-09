# 04.1 · Adaptador MQTT → PotatoMesh (`adaptador-potato`)

## Objetivo

Que PotatoMesh muestre la malla completa vista por todos los gateways. Evita los fallos típicos de los puentes caseros: secretos en el código, recepción bloqueada cuando PotatoMesh va lento, duplicados y trazas mal interpretadas.

Lee el **tráfico crudo** de MQTT, no el flujo `decoded` de la ingesta: PotatoMesh no depende de otro servicio propio y sigue funcionando si la ingesta cae.

## Especificación

### Recepción

- Suscripción `${MQTT_TOPIC_ROOT}/#` (`msh/EU_868/#`) en `mosquitto:1884` con `svc-potato` (solo lectura), QoS 0, sesión limpia, reconexión con espera exponencial (1 s → 60 s).
- Topic esperado `msh/EU_868/2/e/<canal>/<!id gateway>`. Se descartan antes de decodificar: otros formatos (`/json/`, `/stat/`, `/c/`), canales fuera de `ALLOWED_CHANNELS` (segunda barrera; el broker ya filtra) y mensajes directos (`to` distinto de `0xffffffff`).
- `map` (`msh/EU_868/2/map/`): se admite como MapReport.

### Descifrado y decodificación

- `ServiceEnvelope` → `MeshPacket`. Si viene `encrypted`: AES-CTR con la clave del canal (`CHANNEL_KEY_DEFAULT=AQ==`, expandida a la clave por defecto de 16 bytes) y nonce = id de paquete (u64 LE) + nodo origen (u32 LE) + 4 bytes a cero. Mismo algoritmo que `../ingesta/01-input-decryption.md`.
- Si no descifra (canal con clave propia): se envía a PotatoMesh como mensaje cifrado (sin contenido) para que registre actividad del nodo, como hoy.
- Paquetes PKI: no se descifran ni se envían.

### Normalización de canal

`PRIMARY_CHANNEL` (`SFNarrow`) → índice 0. El resto → índice estable por nombre: posición en `ALLOWED_CHANNELS` (1…13). Nombres canónicos sin tildes, tal como vienen en el topic.

### Deduplicación

Clave (`from`, `id`), ventana de 15 min en memoria (LRU, hasta 200.000 claves). Un paquete oído por 10 gateways se envía una vez. Las recepciones posteriores solo actualizan el SNR/RSSI de vecinos si el tipo lo lleva; no generan otro `POST`.

### Envío

- Cola interna acotada (10.000 elementos) entre recepción y envío; el envío va en otra tarea y agrupa hasta 1 s o 100 elementos por endpoint.
- PotatoMesh lento o caído: reintentos con espera exponencial (1 s → 60 s) sin bloquear la recepción. Cola llena: se descartan primero posiciones y telemetría; nodeinfo, mensajes y trazas se conservan.
- Respuesta `4xx` distinta de `401/403/429`: se registra el elemento y se descarta (no se reintenta). `401/403`: `/health` en rojo.
- Caché de métricas por nodo (última telemetría de dispositivo) para no vaciarlas al enviar un nodeinfo nuevo.

## Contratos propios

### Tipos y endpoints

| `portnum` | Endpoint PotatoMesh | Notas |
|---|---|---|
| `NODEINFO_APP` | `POST /api/nodes` | Nombres, `num`, `hwModel`, rol (`config_pb2.Config.DeviceConfig.Role.Name`), `lastHeard`; mezcla con la caché de métricas |
| `POSITION_APP` | `POST /api/positions` | Lat/lon en grados (`latitude_i` × 1e-7), altitud, precisión, `node_id`, `node_num` |
| `TELEMETRY_APP` | `POST /api/telemetry` | Dispositivo, entorno y energía. Emite `telemetry.deviceMetrics` (camelCase) y `device_metrics` (snake_case) para actualizar tanto la tabla `telemetry` como las columnas del nodo en `nodes` |
| `TEXT_MESSAGE_APP` | `POST /api/messages` | Solo canales (nunca directos) |
| `TRACEROUTE_APP` | `POST /api/traces` | Origen = `from`, destino = `to`, `route` + `snr_towards`, y si existen `route_back` + `snr_back` |
| `NEIGHBORINFO_APP` | `POST /api/neighbors` | Vecinos con SNR, `node_id`, `node_num` |
| `WAYPOINT_APP` | `POST /api/waypoints` | |
| MapReport | `POST /api/nodes` + `POST /api/positions` | Portnum 73 (`mqtt_pb2.MapReport`); nodos que solo informan por map report |
| No descifrado | `POST /api/messages` como cifrado | Sin texto |

Formato de cada cuerpo: el que envía el ingestor oficial de la versión fijada (campos camelCase desde 0.7.0 y claves snake_case en métricas para actualización directa del registro del nodo en SQLite). Los tests de contrato lo fijan contra `ghcr.io/l5yth/potato-mesh-web-linux-amd64:0.7.5`.

### `/health` (puerto 8080, solo red `mesh`)

```json
{"ok": true, "mqtt": "conectado", "potatomesh": "ok", "cola": 12, "ultimo_envio_ok": "2026-10-04T10:15:02Z", "descartados_1h": 0}
```

`503` con `ok: false` y `motivo` si: MQTT desconectado > 60 s, sin envío correcto > 5 min habiendo cola, `401/403` de PotatoMesh, o cola > 8.000.

### Configuración

| Variable | Valor o ejemplo | Origen | Secreto |
|---|---|---|---|
| `MQTT_HOST` / `MQTT_PORT` | `mosquitto` / `1884` | Común | No |
| `MQTT_USER` / `MQTT_PASSWORD` | `svc-potato` / — | Propia | Contraseña sí |
| `MQTT_TOPIC_ROOT` | `msh/EU_868` | Común | No |
| `ALLOWED_CHANNELS` / `PRIMARY_CHANNEL` | lista de 14 / `SFNarrow` | Común | No |
| `CHANNEL_KEY_DEFAULT` | `AQ==` | Común | No |
| `POTATOMESH_URL` | `http://potatomesh:41447` | Propia | No |
| `POTATOMESH_API_TOKEN` | — | Propia | Sí |
| `COLA_MAX` / `LOTE_MAX` / `LOTE_SEGUNDOS` | `10000` / `100` / `1` | Propia | No |
| `DEDUP_MINUTOS` | `15` | Propia | No |
| `LOG_LEVEL` | `INFO` | Propia | No |

Sin base de datos: todo el estado es en memoria y se reconstruye solo.

## Unidades de trabajo

- **UT-04.1.1 — Esqueleto.** Proyecto `services/adaptador-potato/` con `pyproject.toml`, `uv.lock`, `Dockerfile` (usuario no root), `compose.yaml`, `.env.example` sin valores secretos, `/health`. *Aceptación:* la imagen arranca sin `pip install` en tiempo de ejecución y sin secretos en el repositorio.
- **UT-04.1.2 — Recepción y filtros.** Suscripción, parseo de topic, lista blanca, descarte de directos. *Casos borde:* topic con canal en otra capitalización → se descarta (los nombres son exactos). *Aceptación:* un paquete de un canal fuera de lista no llega a decodificarse.
- **UT-04.1.3 — Descifrado y decodificación.** Con vectores de prueba compartidos con la ingesta. *Aceptación:* los vectores dan el mismo `Data` que en `ingesta`.
- **UT-04.1.4 — Normalización y deduplicación.** *Aceptación:* 3 gateways con el mismo paquete → 1 `POST`; SFNarrow siempre índice 0.
- **UT-04.1.5 — Traducción a la API.** Una función por tipo; traceroute con ida y vuelta. *Aceptación:* un traceroute de A a B con 2 saltos aparece en PotatoMesh con origen A, destino B y los 2 saltos.
- **UT-04.1.6 — Envío desacoplado.** Cola, lotes, reintentos, prioridad de descarte. *Aceptación:* con PotatoMesh parado 5 min llegan después todos los mensajes del intervalo.
- **UT-04.1.7 — Tests de contrato.** `pytest` levanta la imagen fijada de PotatoMesh con base vacía, envía un ejemplo de cada tipo y lo lee por su `GET`. *Aceptación:* verdes; se repiten antes de subir de versión.

## Escenarios de prueba

1. **Dado** el mismo mensaje de texto oído por 3 gateways en 2 s, **cuando** el adaptador lo procesa, **entonces** PotatoMesh recibe un único `POST /api/messages`.
2. **Dado** un paquete del canal `Madrid`, **cuando** llega (por fallo de ACL), **entonces** se descarta sin decodificar y suma a `descartados_1h`.
3. **Dado** PotatoMesh respondiendo en 10 s, **cuando** llegan 50 msg/s, **entonces** la recepción MQTT no se retrasa y la cola crece hasta vaciarse al recuperar.
4. **Dado** la cola en 10.000 con telemetría y mensajes, **cuando** llega un mensaje de texto, **entonces** se descarta una telemetría y el mensaje entra.
5. **Dado** un token rotado sin redesplegar el adaptador, **cuando** envía, **entonces** `/health` devuelve `503` con `motivo: "token rechazado"`.
6. **Dado** un paquete de un canal con clave propia, **cuando** no descifra, **entonces** PotatoMesh registra actividad del nodo sin contenido.
7. **Dado** un mensaje directo (`to` ≠ difusión), **cuando** llega, **entonces** no se envía nada.

---
> Creado: 2026-10-07 · Última revisión: 2026-10-09
