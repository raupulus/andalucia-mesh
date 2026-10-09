# 05.1 · Entrada y descifrado

## Objetivo

Recibir del broker los mensajes de los gateways, comprobar procedencia y canal, y entregar a la deduplicación ([02](02-decoding-dedup.md)) una recepción con su cabecera; si el paquete es nuevo, obtener su `Data` en claro. Nada de lo que entra vuelve al broker por `msh/…`: `svc-ingest` no tiene permiso de escritura ahí.

## Especificación

### Conexión y suscripción

| Elemento | Valor |
|---|---|
| Broker | `${MQTT_HOST}:${MQTT_PORT}` = `mosquitto:1884` (listener interno, red `mesh`) |
| Protocolo | MQTT 3.1.1, client id `ingesta`, sesión limpia, keepalive 60 s |
| Credenciales | `MQTT_USER=svc-ingest`, `MQTT_PASSWORD` |
| Suscripciones | `${MQTT_TOPIC_ROOT}/2/e/#` y `${MQTT_TOPIC_ROOT}/2/map/#`, QoS 0 |
| Reconexión | Espera exponencial 1 → 30 s; re-suscripción al reconectar |

- No se suscribe a `…/2/json/` (retirado del firmware), `…/2/c/` (firmware < 2.3) ni a otros tipos de topic.
- La ACL del broker ya garantiza: solo los 14 canales de `ALLOWED_CHANNELS` y, en `…/2/e/`, que el último nivel del topic es el usuario que publica.

### Parseo del topic

El topic se corta tras `MQTT_TOPIC_ROOT` (puede tener varios niveles):

| Forma | Se extrae | Si no encaja |
|---|---|---|
| `<raíz>/2/e/<canal>/<!gw>` (exactamente dos niveles tras `e`) | canal (texto del topic) y gateway del topic | `descartado_topic` |
| `<raíz>/2/map/` (con o sin `<!node_id>`) | gateway del topic si termina en `!hex8`; si no, del sobre o del mensaje | — |
| Cualquier otra | — | `descartado_topic` |

- Mensaje de más de 1.024 B → `descartado_tamano` (un `ServiceEnvelope` real no pasa de ~300 B).

### Sobre (`ServiceEnvelope`) y gateway

- Campos: `packet` (`MeshPacket`), `channel_id`, `gateway_id`. Error de protobuf o sin `packet` → `descartado_protobuf` (excepto en tópicos `…/2/map/`, donde se procesa como `MapReport` crudo).
- `gateway_id` se normaliza a minúsculas y debe cumplir `^![0-9a-f]{8}$`; si no → `descartado_gateway`.
- En `…/2/e/`: gateway del topic ≠ `gateway_id` del sobre → descartado, contador `gateway_distinto` y log `WARN` con ambos ids (máximo uno por minuto y gateway).
- En `…/2/map/`: el gateway es `gateway_id` del sobre o del topic; si no viene definido en el sobre, se asume el `from_id` del nodo emisor. Se admite tanto `ServiceEnvelope` como `MapReport` directo sin sobre. La ACL no ata este topic al usuario (riesgo aceptado en el README).
- Todo mensaje que supera esta comprobación actualiza el registro de gateways ([03](03-provinces-registry.md)), aunque después se descarte por canal o sea un duplicado: el gateway está vivo.

### Lista blanca de canales (segunda barrera)

- Forma canónica: Unicode NFKD, sin marcas diacríticas y en minúsculas (`Cádiz` → `cadiz`). Se compara con la forma canónica de cada canal de `ALLOWED_CHANNELS`; el valor que se guarda y publica es la grafía de la lista (`Cadiz`).
- Canal no permitido → contador `canal_no_permitido` (con el nombre del canal en el log, máximo uno por hora y canal); no se decodifica, no se guarda, no se publica (RF: solo canales de la lista).
- `channel_id` del sobre no decide nada: manda el topic. Si difiere tras canonizar, solo sube `canal_sobre_distinto`.
- Map reports no tienen canal: `channel = null`.

### Índice de canal

- El canal se guarda y se publica por nombre. Cuando haga falta un índice (por ejemplo, para comparar con el campo `channel` de un `MeshPacket` ya descifrado), `PRIMARY_CHANNEL` (`SFNarrow`) es el **0**; los demás no tienen índice global, porque cada nodo ordena sus canales secundarios a su manera.
- `MeshPacket.channel` (hash del canal si llega cifrado, índice local si llega en claro) no se usa para identificar el canal.

### Cabecera

- Se leen del `MeshPacket`: `from`, `to`, `id`, `hop_limit`, `hop_start`, `want_ack`, `via_mqtt`, `relay_node`, `rx_time`, `rx_snr`, `rx_rssi`, `pki_encrypted` y `encrypted` o `decoded`.
- `from` = 0 o `0xFFFFFFFF`, o `id` = 0 → `descartado_cabecera` (no se puede deduplicar).
- Ids como `!` + 8 hexadecimales en minúsculas (`!%08x`); `to = 0xFFFFFFFF` → `^all`.
- Con la cabecera válida se pasa a la deduplicación. **Solo si el paquete es nuevo** se obtiene el contenido (un duplicado no cuesta descifrado).

### Obtención del contenido (`Data`)

| Caso | Cómo se reconoce | Qué se hace | `decrypt_status` |
|---|---|---|---|
| Ya en claro | `decoded` presente (gateway con `encryption_enabled = false`; map reports siempre) | Se usa tal cual; la carga mide `len(Data serializado)` | `claro` |
| Cifrado de canal | `encrypted` presente y `pki_encrypted = false` | AES-CTR con la clave por defecto | `descifrado` o `cifrado_desconocido` |
| PKI | `pki_encrypted = true` | Solo metadatos | `pki` |

### AES-CTR

- **Clave:** `CHANNEL_KEY_DEFAULT` en base64 → PSK. Expansión igual que el firmware: 0 B o 1 B de valor 0 → sin cifrado; 1 B de valor *n* ≥ 1 → la clave por defecto de 16 B del firmware (constante `defaultpsk` del código del firmware; no se reproduce aquí) con su último byte incrementado en *n* − 1; 16 B → AES-128; 32 B → AES-256; otra longitud → error de configuración al arrancar.
- **Solo esa clave**: los 14 canales usan la clave por defecto; no hay lista de claves.
- **Nonce (16 B):** `id` del paquete como entero de 64 bits little-endian (8 B) + `from` como uint32 little-endian (4 B) + 4 B a cero.
- **Contador:** el firmware incrementa solo los 4 últimos bytes (big-endian); `cryptography` con `modes.CTR(nonce)` incrementa los 128 bits. Es idéntico para cargas ≤ 237 B (≤ 15 bloques), porque esos 4 bytes parten de 0.
- **Éxito:** el resultado se parsea como `Data` sin error y `portnum` ≠ 0 (`UNKNOWN_APP`). CTR no tiene integridad: un falso positivo es posible pero muy raro.
- **Fallo:** `cifrado_desconocido`. Se guardan cabecera y recepciones, el tamaño cifrado sirve para el tiempo en el aire y se publica como `other` con `payload: {}` y `ok_to_mqtt: null`.

### PKI (mensajes directos)

- El firmware publica los DMs PKI en `…/2/e/PKI/<!gw>`; ese canal no está en la ACL ni en la lista blanca, así que no llega. Esta rama es defensiva: un `pki_encrypted = true` en un canal permitido.
- Se guardan cabecera y recepciones (`portnum` null, agregado como `other`); el tiempo en el aire usa la longitud de `encrypted` (incluye etiqueta y nonce extra). **No se publica** en `decoded`.

## Contratos propios

| Contrato | Valor |
|---|---|
| Entrada | `msh/EU_868/2/e/<canal>/<!gw>` y `msh/EU_868/2/map/`, `ServiceEnvelope` protobuf (`mqtt.proto`) |
| ACL requerida | `user svc-ingest` / `topic read msh/EU_868/#` / `topic write snm/v1/decoded/#` |
| Salida interna hacia 02 | Recepción: topic, canal canónico (o null), gateway, cabecera, `Data` o estado de descifrado, tamaño de carga, hora del servidor (UTC) |

Contadores de este módulo (expuestos en `/health`): `mensajes`, `descartado_topic`, `descartado_tamano`, `descartado_protobuf`, `descartado_gateway`, `gateway_distinto`, `map_gateway_distinto`, `canal_no_permitido`, `canal_sobre_distinto`, `descartado_cabecera`, `cifrado_desconocido`, `pki`.

## Unidades de trabajo

### UT-05.1.1 — Cliente MQTT y suscripción

- **Comportamiento:** conecta con `svc-ingest`, se suscribe a los dos filtros y entrega cada mensaje con su topic a la canalización.
- **Detalle:** `aiomqtt`; tarea lectora única; reconexión 1 → 30 s; estado de conexión visible en `/health`.
- **Casos borde:** credenciales erróneas (reintenta con log `ERROR`, `ok=false` tras 60 s); broker reiniciado.
- **Aceptación:** tras reiniciar Mosquitto, los mensajes vuelven a procesarse en < 35 s sin intervención.

### UT-05.1.2 — Topic, sobre y gateway

- **Comportamiento:** valida forma del topic, sobre y coherencia del gateway.
- **Detalle:** tablas y reglas de esta página; normalización a minúsculas.
- **Casos borde:** topic con niveles extra en `e`; `gateway_id` con mayúsculas; `gateway_id` vacío; map report con `from` distinto del gateway.
- **Aceptación:** cada caso incrementa exactamente su contador y ninguno llega a la base.

### UT-05.1.3 — Lista blanca y canal canónico

- **Comportamiento:** solo los canales de `ALLOWED_CHANNELS` siguen adelante, con su grafía de la lista.
- **Detalle:** forma canónica NFKD sin diacríticos y en minúsculas.
- **Casos borde:** `Cádiz`, `CADIZ` y `cadiz` → `Cadiz`; `Murcia` → descartado; `PKI` → descartado.
- **Aceptación:** pruebas unitarias con los 14 canales, variantes de grafía y 3 canales ajenos.

### UT-05.1.4 — Descifrado AES-CTR y paquetes en claro

- **Comportamiento:** obtiene `Data` de paquetes cifrados con la clave por defecto o ya descifrados.
- **Detalle:** expansión de PSK, nonce y criterio de éxito de esta página.
- **Casos borde:** carga de 237 B; `encrypted` vacío; `decoded` y `encrypted` ausentes (descartado_protobuf); PSK de 32 B en configuración.
- **Aceptación:** paquetes cifrados por la batería de pruebas con la misma expansión se descifran byte a byte; el mismo contenido llegado en claro produce idéntico `Data`.

### UT-05.1.5 — PKI y cifrado desconocido

- **Comportamiento:** guarda metadatos de lo que no se puede leer, sin perder la recepción.
- **Detalle:** `decrypt_status` `pki` o `cifrado_desconocido`; publicación solo para el segundo.
- **Casos borde:** paquete cifrado con otra clave (falla el parseo o `portnum` = 0).
- **Aceptación:** ambos casos crean `packet` y `reception` con `portnum` null; solo `cifrado_desconocido` aparece en `snm/v1/decoded/other`.

## Escenarios de prueba

- **Dado** un mensaje en `msh/EU_868/2/e/SFNarrow/!0badc0de` con `gateway_id = !0badc0de` y telemetría cifrada con la clave por defecto, **cuando** llega, **entonces** se descifra, `channel = SFNarrow` y pasa a 02.
- **Dado** el mismo topic con `gateway_id = !deadbeef`, **cuando** llega, **entonces** sube `gateway_distinto` y no se guarda nada.
- **Dado** un mensaje en `msh/EU_868/2/e/Cádiz/!0badc0de`, **cuando** llega, **entonces** se guarda con `channel = Cadiz`.
- **Dado** un gateway con `encryption_enabled = false`, **cuando** publica un paquete en claro, **entonces** se procesa igual que el cifrado y `decrypt_status = claro`.
- **Dado** un paquete cifrado con una clave privada desconocida, **cuando** llega, **entonces** se guarda con `cifrado_desconocido` y se publica como `other` con `payload` vacío.
- **Dado** un duplicado de un paquete ya visto, **cuando** llega por otro gateway, **entonces** no se descifra de nuevo (contador de descifrados sin cambios).

---
> Creado: 2026-10-07 · Última revisión: 2026-10-09
