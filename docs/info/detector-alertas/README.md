# 07 · Detector de alertas

> Servicio Python que **solo** detecta y cataloga alertas de la malla (riesgo × tipo) a partir del flujo `decoded`, las guarda en la base `alertas` y emite cada transición por un socket Unix a aplicaciones independientes. Sin web, sin bots, sin API propia. **Tipo:** desarrollo propio (Python) · **Fase:** 6 (MVP de 7 reglas) y ampliación posterior · **Complejidad:** alta · **Monorepo:** `services/detector-alertas/`

## 1. Contexto

- **Qué existe:** nada previo; es desarrollo nuevo. Depende de que `ingesta` publique `snm/v1/decoded/#` con `from_node` (`../ingesta/06-decoded-stream.md`).
- **Requisitos:** un servicio dedicado solo a detectar y catalogar alertas, sin web ni bots. Riesgos `bajo`, `medio`, `alto`; tipos `infraestructura` (routers y lo que degrada la malla o afecta a muchos nodos) y `clientes` (nodos que no son routers), ampliables sin tocar código. Cada comprobación devuelve riesgo + tipo + mensaje + nodo (`all` si son muchos). Rol router = siempre `infraestructura`. Reglas dinámicas según el comportamiento observado, con mínimos básicos. Salida por socket Unix a aplicaciones independientes que formatean y envían. Casos citados: nodos en bucle (reinicio cada 1,5 min), baterías bajas, routers caídos, spam y errores de configuración.
- **Consumidores:** `bot-telegram`, `bot-discord` y `webhooks` por el socket (`../bots-webhooks/`); el portal por las vistas `api_*` con el rol `portal_lector_alertas` (`/alerts`, `/alerts/{id}`, `/alerts/catalog`, `/nodes/at-risk`, `/stats/summary`, `/nodes/{id}/diagnosis`; `../portal/13-nodes-alerts-api.md`).
- **Definiciones** (`../integration.md` §13): router = rol en `INFRA_ROLES` (`ROUTER`, `ROUTER_LATE`, `REPEATER`); gateway = nodo con usuario en el broker; nodo de infraestructura = router o gateway. `CLIENT_BASE` no es infraestructura.

## 2. Alcance

**Incluye**
- Suscripción a `snm/v1/decoded/#`, validación, deduplicación de seguridad y cola acotada.
- Motor: clasificador, líneas base dinámicas, estado con instantáneas, ciclo de vida, silencios y recarga en caliente (`01-rule-engine.md`).
- Las 7 reglas MVP del catálogo (`02-rule-catalog.md`).
- Base `alertas` con vistas contrato, retención de 1 año y servidor del socket con reenvío de 24 h (`03-socket-persistence.md`).
- `/health` en 8080 (solo red `mesh`), herramienta de calibración y cliente de prueba del socket.

**Fuera de esta entrega**
- Las 14 reglas de ampliación: especificadas en `02-rule-catalog.md`, se implementan tras calibrar el MVP con datos reales.
- Formatear y enviar avisos (bots y webhooks), API HTTP y páginas (portal).
- Publicar en MQTT, SSE o REST público. Leer otras bases (`ingest` incluida). Exclusión de nodos. Alta disponibilidad: una sola instancia.

**Requisitos globales**
- **RF-DA-1** Transición emitida por el socket < 10 s después del paquete que la dispara (con PostgreSQL disponible).
- **RF-DA-2** Un problema = una alerta abierta que se actualiza (clave `regla + nodo`), no una por paquete.
- **RF-DA-3** Las alertas se resuelven solas, con histéresis.
- **RF-DA-4** Umbrales, riesgos y tipos cambian sin reiniciar.
- **RF-DA-5** Un reinicio no pierde alertas abiertas ni las reemite como nuevas.
- **RF-DA-6** Añadir o quitar consumidores no toca el detector.

## 3. Stack y versiones

| Elemento | Elección | Versión |
|---|---|---|
| Lenguaje | Python, `asyncio`, un único bucle y una única tarea de motor | 3.13 |
| Imagen base | `python:3.13-slim-trixie` (Docker Hub) | Fijar al crear (última probada, por digest) |
| Dependencias | `uv` + `pyproject.toml` | Exactas en `uv.lock` |
| MQTT | `aiomqtt` (sobre `paho-mqtt` 2) | 2.x |
| PostgreSQL | `asyncpg` | 0.30.x |
| Validación y entorno | `pydantic` 2 + `pydantic-settings` | 2.x |
| YAML | `PyYAML` (`safe_load`) | 6.x |
| Ids | ULID (`python-ulid`) con generador monótono propio | 3.x |
| Salud | `aiohttp.web` | 3.x |
| Pruebas y calidad | `pytest`, `pytest-asyncio`, PostgreSQL 17 efímero en las pruebas; `ruff`, `mypy --strict` | — |
| Base de datos | PostgreSQL 17 nativo del servidor, sin extensiones | 17 |

```text
services/detector-alertas/
  compose.yaml  Dockerfile  pyproject.toml  uv.lock  .env.example  README.md
  config/        clasificacion.yaml  reglas.yaml
  migrations/   001_tablas.sql  002_vistas.sql            (tabla esquema_version)
  detector/      __main__.py (servicio | calibrar | escuchar)  config.py  entrada.py  salud.py
                 motor/ (estado, bases, clasificador, ciclo, temporizador)  reglas/ (una por archivo)
                 persistencia.py  socket_alertas.py  calibracion.py
  tests/
```

## 4. Contratos

### 4.1 Entrada: flujo `decoded`

| Elemento | Valor |
|---|---|
| Topic | `${MQTT_TOPIC_PREFIX}/v1/decoded/#` = `snm/v1/decoded/#`; `<portnum>`: `nodeinfo`, `position`, `telemetry`, `text`, `neighborinfo`, `traceroute`, `routing`, `map_report`, `other` |
| Broker | `mosquitto:1884` (red `mesh`), usuario `svc-detector`: solo lectura de `snm/v1/decoded/#` |
| Entrega | QoS 0, sin retained, sesión limpia. Un mensaje por paquete único, 2 s después de la primera recepción |

```json
{
  "v": 1,
  "packet_id": 3735928559,
  "from": "!a1b2c3d4",
  "from_node": {"short": "CAD1", "long": "Repetidor Sierra Cádiz", "role": "ROUTER", "hw": "HELTEC_V3", "is_gateway": false, "province": "ES-CA"},
  "to": "^all",
  "portnum": "telemetry",
  "channel": "SFNarrow",
  "rx_first": "2026-10-01T15:00:00Z",
  "hop_start": 3,
  "hops_min": 1,
  "via_mqtt": false,
  "ok_to_mqtt": true,
  "airtime_ms": 61.4,
  "receptions": [
    {"gateway": "!0badc0de", "snr": 6.5, "rssi": -98, "hops": 1, "at": "2026-10-01T15:00:00Z"}
  ],
  "payload": {
    "device_metrics": {"battery_level": 37, "voltage": 3.61, "channel_utilization": 18.2, "air_util_tx": 2.1, "uptime_seconds": 84}
  }
}
```

- `from_node` es el registro de nodos de `ingesta` (último nodeinfo, posición y si ha publicado como gateway); campos desconocidos a `null`. Es la única fuente de rol, nombres y provincia del detector.
- `payload` usa los nombres del protobuf en snake_case (`device_metrics`, `local_stats`, `environment_metrics`, `power_metrics`, `latitude_i`, `public_key`…); vacío en tipos no decodificados. Campos que usa cada regla: `02-rule-catalog.md`.
- Validación (pydantic): `v == 1`, `from` = `!` + 8 hex en minúsculas, `rx_first` ISO 8601. Lo inválido se descarta y se cuenta; nunca detiene el servicio.

### 4.2 Salida: socket Unix

| Elemento | Valor |
|---|---|
| Ruta | `/run/snm/alertas.sock` (`ALERTAS_SOCKET`), en el volumen `alertas-socket` |
| Tipo | Unix `SOCK_STREAM`; servidor = detector; varios clientes |
| Permisos | `0660`, GID `10500` (`ALERTAS_SOCKET_GID`) compartido con los consumidores |
| Formato | NDJSON UTF-8, un objeto por línea |

Protocolo (exacto, con casos borde, en `03-socket-persistence.md`): saludo `{"cliente": "bot-telegram", "desde": "<transicion_id> | null"}` → reenvío desde la base de las transiciones posteriores a `desde` (máx. 24 h) → directo. Cada transición: `{"v": 1, "transicion_id": "<ULID>", "transicion": "abierta|actualizada|resuelta", "alerta": {…}}`. Latido `{"latido": "<ISO 8601>"}` cada 30 s; sin latido en 90 s el cliente reconecta con su último `transicion_id`. Cliente con más de 1.000 mensajes sin leer → desconectado.

Objeto `alerta` (claves en español, internas):

```json
{
  "id": "01JABCDXYZ7Q8R9S0T1V2W3X4Y",
  "regla": "reboot-loop", "riesgo": "alto", "tipo": "infraestructura",
  "mensaje": "CAD1 se ha reiniciado 7 veces en la última hora",
  "nodo": "!a1b2c3d4",
  "nodos": [],
  "nodo_info": {"corto": "CAD1", "largo": "Repetidor Sierra Cádiz", "rol": "ROUTER", "provincia": "ES-CA"},
  "datos": {"reinicios": 7, "ventana_min": 60, "uptime_minimo_s": 84, "umbral": 3},
  "estado": "abierta",
  "abierta_en": "2026-10-01T15:00:00Z", "actualizada_en": "2026-10-01T15:42:00Z", "resuelta_en": null
}
```

- `nodo = "all"` → `nodos` lleva la lista (máx. 500; total en `datos.nodos_total`) y `nodo_info` es `null`.
- `estado`: `abierta` o `resuelta` (una alerta actualizada sigue `abierta`). Mismo `id` toda su vida, reaperturas incluidas. Fechas ISO 8601 UTC con `Z`.
- La API del portal traduce las claves (`regla→rule`, `riesgo→risk`…, `../integration.md` §9); el detector no.

### 4.3 Vistas contrato y archivos de configuración

- Vistas de la base `alertas`, solo lectura para `portal_lector_alertas`: `api_alertas`, `api_alertas_transiciones`, `api_alertas_resumen`, `api_nodos_en_riesgo`, `api_catalogo`. Columnas, SQL y `GRANT` en `03-socket-persistence.md`.
- `config/clasificacion.yaml` (riesgos ordenados, tipos, `infraestructura_manual`; `01-rule-engine.md`) y `config/reglas.yaml` (`general`, un bloque por regla, `silencios`; completo en `02-rule-catalog.md`). Versionados en git, montados de solo lectura, recargables en caliente.

### 4.4 Salud

`GET http://detector-alertas:8080/health` (solo red `mesh`) → `200` con `ok: true` o `503` con `ok: false`:

```json
{"ok": true, "version": "1.0.0", "arranque": "2026-10-01T09:00:00Z", "mqtt": "conectado", "postgres": "ok", "config": "ok",
 "cola": 12, "descartes": {"invalidos": 3, "duplicados": 0, "posicion": 0, "total": 0}, "ultimo_paquete": "2026-10-01T15:00:02Z",
 "alertas_abiertas": 14, "transiciones_pendientes": 0, "clientes_socket": 3}
```

`ok: false` si MQTT lleva > 60 s desconectado, PostgreSQL > 60 s inaccesible, el socket no escucha o nunca se cargó una configuración válida. Un error de recarga deja `ok: true` con `config: "error: …"` (sigue la configuración anterior).

## 5. Configuración

| Variable | Valor o ejemplo | Origen | Secreto |
|---|---|---|---|
| `TZ` | `Europe/Madrid` (franjas horarias de la línea base de malla, hora de retención) | común | no |
| `MQTT_HOST` / `MQTT_PORT` | `mosquitto` / `1884` | común | no |
| `MQTT_TOPIC_PREFIX` | `snm` | común | no |
| `INFRA_ROLES` | `ROUTER,ROUTER_LATE,REPEATER` | común | no |
| `SATURACION_PESO_ROUTERS` / `SATURACION_PESO_CLIENTES` | `0.6` / `0.4` | común | no |
| `DB_HOST` / `DB_PORT` | `172.30.0.1` / `5432` | común | no |
| `ALERTAS_SOCKET` / `ALERTAS_SOCKET_GID` | `/run/snm/alertas.sock` / `10500` | común | no |
| `MQTT_USER` / `MQTT_CLIENT_ID` | `svc-detector` / `detector-alertas` | propia | no |
| `MQTT_PASSWORD` | — | propia | sí |
| `DB_NAME` / `DB_USER` | `alertas` / `alertas` | propia | no |
| `DB_PASSWORD` | — | propia | sí |
| `CLASIFICACION_PATH` / `REGLAS_PATH` | `/config/clasificacion.yaml` / `/config/reglas.yaml` | propia | no |
| `RECARGA_S` | `30` (sondeo de cambios en los YAML; también `SIGHUP`) | propia | no |
| `HEALTH_PORT` / `COLA_MAX` / `SNAPSHOT_S` / `RETENCION_DIAS` | `8080` / `10000` / `60` / `365` | propia | no |
| `REENVIO_MAX_H` / `LATIDO_S` / `CLIENTE_MAX_PENDIENTES` / `SALUDO_TIMEOUT_S` | `24` / `30` / `1000` / `10` | propia | no |
| `GRABACION_DIAS` | `0` (desactivada; `14` mientras se calibra) | propia | no |
| `LOG_LEVEL` | `INFO` | propia | no |

Las comunes vienen de `/srv/comun/.env` (`env_file` antes del `.env` propio). Ningún valor se escribe en el código.

## 6. Datos

- PostgreSQL 17 nativo, base `alertas`, propietario `alertas`; conexión `172.30.0.1:5432`, `scram-sha-256`, solo desde `172.30.0.0/24`. Base, rol propietario, `portal_lector_alertas` y `pg_hba` los crea `../infrastructure/02-postgresql.md`; tablas, vistas y permisos sobre ellas, las migraciones del detector.

| Tabla | Contenido | Retención |
|---|---|---|
| `alerta` | Una fila por alerta, estado actual y última evidencia | Abiertas siempre; resueltas 1 año |
| `transicion` | Cada transición con el objeto `alerta` tal como se emitió | 1 año |
| `estado_snapshot` | Instantánea comprimida del estado del motor | Las 3 últimas |
| `catalogo` | Riesgos, tipos y reglas cargados (alimenta `api_catalogo`) | Se reescribe en cada carga |

| Volumen | Montaje | Contenido | Copia |
|---|---|---|---|
| `alertas-socket` (externo, compartido) | `/run/snm` | `alertas.sock` | No |
| `detector-grabaciones` | `/datos/grabaciones` | NDJSON diario del flujo `decoded` sin `payload.text`, solo si `GRABACION_DIAS` > 0 | No |
| `./config` (bind, solo lectura) | `/config` | `clasificacion.yaml`, `reglas.yaml` | En git |

Privacidad: ids de nodo y evidencias técnicas; 1 año, igual que los agregados por nodo de `ingesta`.

## 7. Unidades de trabajo

| # | Módulo | Contenido | UT | Complejidad |
|---|---|---|---|---|
| 01 | [`01-rule-engine.md`](01-rule-engine.md) | Contratos, clasificador, estado, líneas base, ciclo de vida, temporizador, silencios, recarga, calibración | UT-07.1.1 – UT-07.1.9 | Alta |
| 02 | [`02-rule-catalog.md`](02-rule-catalog.md) | Catálogo con riesgo por tipo, base dinámica, resolución y campos; `reglas.yaml` de arranque | UT-07.2.1 – UT-07.2.7 | Media |
| 03 | [`03-socket-persistence.md`](03-socket-persistence.md) | Esquema, retención, vistas contrato, permisos, servidor del socket | UT-07.3.1 – UT-07.3.7 | Media |

Orden: UT-07.1 → UT-07.3.1 → UT-07.1.1–07.1.6 → UT-07.2 → UT-07.2.1–07.2.4 → UT-07.3.2–07.3.7 → UT-07.1.7–07.1.9 → UT-07.3, UT-07.4 → UT-07.5. Ampliación (UT-07.2.5–07.2.7) después de calibrar.

### UT-07.1 — Esqueleto, configuración y ciclo del proceso
- **Comportamiento:** `python -m detector` carga entorno y YAML, aplica migraciones, restaura la instantánea, abre el socket, arranca salud y temporizador y conecta a MQTT. `SIGTERM`: deja de leer MQTT, vacía la cola, guarda instantánea, cierra clientes, borra el socket y sale.
- **Detalle:** `pydantic-settings`; logs JSON a stdout (`nivel`, `evento`, `regla`, `nodo`, `alerta_id`, `cliente`); `SIGHUP` = recargar YAML.
- **Casos borde:** PostgreSQL caído al arrancar → reintenta (1–30 s) sin abrir el socket; YAML inválido al arrancar → sale con código 1.
- **Aceptación:** con base vacía crea el esquema y queda `healthy`; `docker compose stop` termina en < 20 s con instantánea guardada.

### UT-07.2 — Entrada MQTT y cola
- **Comportamiento:** suscripción QoS 0 a `snm/v1/decoded/#`; validación; cola de `COLA_MAX` hacia la tarea única del motor.
- **Detalle:** reconexión con espera exponencial 1–30 s; caché `(from, packet_id)` de 10 min que descarta repetidos; cola > 80 % → se descartan `position`; llena → todo lo nuevo; contadores en `/health`. Grabación opcional (`01-rule-engine.md`).
- **Casos borde:** JSON corrupto, `v` ≠ 1, `portnum` desconocido (se trata como `other`), `rx_first` > 5 min en el futuro (se usa la hora de llegada), `from_node` ausente (todo `null`).
- **Aceptación:** 100 msg/s durante 10 min sin descartes, latencia p95 < 2 s y < 30 % de un núcleo.

### UT-07.3 — Salud
- **Comportamiento:** `/health` según §4.4, en `0.0.0.0:8080` del contenedor (sin `ports:`, solo red `mesh`). **Casos borde:** respuesta < 100 ms aunque el motor esté ocupado (no toca la cola); PostgreSQL comprobado con `SELECT 1` cada 15 s, no por petición.
- **Aceptación:** el panel del portal ve `200` en marcha y `503` con MQTT parado más de 60 s.

### UT-07.4 — Imagen y compose
- **Comportamiento:** `Dockerfile` multietapa con `uv`; usuario `10501:10500`; `/run/snm` con propietario `10501:10500` y modo `2770`; `compose.yaml` según §8.
- **Detalle:** `ruff`, `mypy --strict` y `pytest` (con PostgreSQL 17) se ejecutan antes de etiquetar una versión; la imagen se construye en el servidor desde el `Dockerfile`.
- **Aceptación:** `deploy.sh detector-alertas` construye y levanta la versión del checkout.

### UT-07.5 — Prueba de extremo a extremo
- **Comportamiento:** broker y PostgreSQL de prueba; un guion publica un NDJSON sintético (los escenarios de §10 y de los módulos) y comprueba la salida con `python -m detector escuchar` y las vistas `api_*`. **Aceptación:** todos los escenarios de §10 pasan.

## 8. Despliegue

| Contenedor | Imagen | Redes | Volúmenes | Puertos | Publicación | Healthcheck |
|---|---|---|---|---|---|---|
| `detector-alertas` | `snm-detector-alertas:<versión>` (construida en el servidor, base fijada) | `mesh` (externa) | `alertas-socket:/run/snm`, `detector-grabaciones:/datos/grabaciones`, `./config:/config:ro` | Ninguno | No se publica | `python -c "import urllib.request; urllib.request.urlopen('http://127.0.0.1:8080/health', timeout=3)"` cada 30 s, 3 fallos, `start_period` 60 s |

Además: `user: "10501:10500"`, `restart: unless-stopped`, `stop_grace_period: 30s`, `read_only: true` con `tmpfs: /tmp`, `env_file: [/srv/comun/.env, .env]`, logs `json-file` con `max-size: 10m` y `max-file: 3`. Volúmenes `alertas-socket` y red `mesh` declarados `external: true`.

**Pasos, en orden**
1. Requisitos: `infrastructure` (red `mesh`, base `alertas`, roles `alertas` y `portal_lector_alertas`, `pg_hba`), `mosquitto` (usuario `svc-detector` con lectura de `snm/v1/decoded/#`) y `ingesta` publicando.
2. En `/srv/detector-alertas/`: `compose.yaml`, `.env` (desde `.env.example`, con `MQTT_PASSWORD` y `DB_PASSWORD`) y `config/` con los dos YAML.
3. Solo la primera vez: `docker volume create alertas-socket` y `docker run --rm --user 0 -v alertas-socket:/run/snm --entrypoint sh <imagen> -c 'chown 10501:10500 /run/snm && chmod 2770 /run/snm'`.
4. `deploy.sh detector-alertas` (construye la imagen y levanta). Al arrancar aplica migraciones (tablas, vistas, `GRANT`) y rellena `catalogo`.
5. Comprobar: `docker compose ps` en `healthy`; `docker compose exec detector-alertas python -m detector escuchar` recibe latidos; `psql -h 172.30.0.1 -U portal_lector_alertas -d alertas -c 'SELECT count(*) FROM api_catalogo'` devuelve filas y `SELECT * FROM alerta` es rechazado.
6. Calibrar con `GRABACION_DIAS=14` y 7 días de datos (`01-rule-engine.md`) antes de desplegar `bots-webhooks`.

**Copias de seguridad:** fuera del proyecto (sistema del operador). Qué copiar: la base `alertas`; configuración en git; volúmenes sin copia (socket efímero, grabaciones desechables). Restauración: restaurar la base y desplegar; los consumidores reconectan con su `desde`.

## 9. Definición de hecho

- [ ] Contenedor `healthy` en el servidor, solo en la red `mesh`, sin puertos publicados.
- [ ] Migraciones crean las 4 tablas, las 5 vistas y los `GRANT`; `portal_lector_alertas` lee las vistas y no las tablas.
- [ ] Las 7 reglas MVP con pruebas de apertura, actualización, resolución, reapertura e histéresis; cobertura ≥ 85 % en `motor/` y `reglas/`.
- [ ] Clasificador: router, gateway, `infraestructura_manual`, `all` o `afecta_malla` → `infraestructura`; resto → `clientes`; riesgos o tipos desconocidos rechazados al cargar.
- [ ] Recarga en caliente de ambos YAML (sondeo y `SIGHUP`) que conserva la configuración anterior si la nueva es inválida.
- [ ] Instantánea cada 60 s y al parar; un reinicio no reemite alertas abiertas ni abre ausencias en falso.
- [ ] Socket `0660` con GID `10500` y protocolo de `../integration.md` §7: saludo, reenvío de 24 h sin huecos ni duplicados, latido de 30 s, desconexión por más de 1.000 pendientes.
- [ ] Retención diaria ejecutada (resueltas y transiciones de más de 1 año, instantáneas antiguas).
- [ ] `/health` con `200`/`503` según §4.4 y visto desde el panel.
- [ ] Prueba de carga: 100 msg/s, p95 < 2 s, < 30 % de un núcleo.
- [ ] Calibración sobre 7 días grabados revisada y umbrales de `reglas.yaml` ajustados antes de activar los bots.
- [ ] `compose.yaml`, `.env.example`, `README.md` y los dos YAML en `services/detector-alertas/`, sin secretos.

## 10. Escenarios de prueba

- **Bucle de reinicio:** Dado el router `CAD1` (`ROUTER`, `ES-CA`) / Cuando envía telemetría con `uptime_seconds` 84, 90 y 75 en 40 min / Entonces sale una transición `abierta` `reboot-loop` `alto · infraestructura`, aparece en `api_alertas` y `CAD1` en `api_nodos_en_riesgo`.
- **Recuperación de un consumidor:** Dado un cliente con último id `X` parado 10 min / Cuando se generan 30 transiciones / Entonces al reconectar con `desde: X` recibe las 30 en orden, sin duplicados, y sigue en directo.
- **Reinicio del detector:** Dado el detector con 14 alertas abiertas / Cuando se reinicia / Entonces no emite ninguna `abierta` repetida y `gateway-offline` no abre nada en los primeros 15 min.
- **Riesgo nuevo sin código:** Dado `clasificacion.yaml` / Cuando se añade `critico` al final de `riesgos` y `battery-low` lo usa (`umbral.infraestructura.critico: 10`) / Entonces se aplica en ≤ 30 s sin reiniciar y aparece en `api_catalogo` y `api_alertas_resumen`.
- **PostgreSQL caído:** Dado PostgreSQL parado 2 min / Cuando se cumple una condición de alerta / Entonces la transición sale al volver la base, en orden, y `/health` da `503` pasados 60 s.
- **Socket protegido:** Dado un contenedor sin el GID `10500` / Cuando intenta conectar a `/run/snm/alertas.sock` / Entonces recibe permiso denegado.

## 11. Riesgos y limitaciones

- Solo se ve lo que se emite y se sube (OK to MQTT): con telemetría cada 4–6 h un bucle de reinicio tarda en verse; zonas sin gateway son invisibles.
- `local_stats` (ruido, colas) solo lo envían algunos nodos.
- Los mínimos son de arranque: hasta calibrar habrá falsos positivos (por eso la ampliación llega desactivada y se calibra antes de los bots).
- Si el detector está caído no se detecta nada de ese periodo: `ingesta` no reenvía lo pasado. Las alertas abiertas se conservan.
- Servidor entero caído = sin alertas ni aviso (limitación aceptada).
- Los nombres de `payload` dependen de `ingesta`; un cambio allí rompe reglas en silencio (las pruebas de contrato con ejemplos reales lo cubren).
- Una sola instancia: dos detectores duplicarían alertas y competirían por el socket.

## 12. Referencias

- `../integration.md` §4 (volumen), §5 (MQTT), §6 (`decoded`), §7 (socket), §8 (bases y vistas), §11 (salud), §12 (`.env.comun`), §13 (definiciones).
- `../ingesta/06-decoded-stream.md`, `../bots-webhooks/`, `../portal/13-nodes-alerts-api.md`, `../portal/03-node-setup-guide.md`, `../infrastructure/02-postgresql.md`, `../infrastructure/04-operations.md`, `../mosquitto/`.

## Decisiones de detalle

1. Los roles de infraestructura salen solo de `INFRA_ROLES`; `clasificacion.yaml` no los repite (solo `infraestructura_manual`).
2. Un gateway se conoce por `receptions[].gateway` o `from_node.is_gateway`; su actividad es el último `receptions[].at`.
3. `topic-mismatch` sale del catálogo: la ACL (`%u`) lo impide y `decoded` no lleva el `gateway_id` del sobre.
4. Routers y gateways sin oírse 7 días (`seguimiento_dias`) dejan de vigilarse; su alerta se resuelve con `datos.cierre = "fuera_de_seguimiento"`.
5. El silencio se cuenta desde `max(último visto, arranque del detector)`: sin avalancha de ausencias tras un reinicio.
6. `actualizada`: subida de riesgo inmediata; bajada de riesgo o cambio > 20 % en `nodos`, como mucho una cada 15 min por alerta.
7. Reapertura (< 1 h): mismo `id`, transición `abierta`; el recuento va en la columna `reaperturas` de `api_alertas`, no en el objeto del socket.
8. Saludo con `desde: null` = solo directo (un consumidor nuevo no recibe 24 h de golpe). Saludo inválido o ausente en 10 s = cierre.
9. Se guarda antes de emitir; con PostgreSQL caído las transiciones esperan en memoria, en orden.
10. `api_nodos_en_riesgo` solo cuenta alertas de un nodo (las de `all` no ponen en riesgo a cada nodo de la lista).
11. Tabla `catalogo` reescrita en cada carga para alimentar `api_catalogo` y el orden de riesgos (`min_risk`).
12. Campos de `payload` = protobuf en snake_case (posición en `latitude_i`/`longitude_i`); si `../ingesta/06-decoded-stream.md` fija otros nombres, manda ese.
13. Calibración sin leer `ingest`: grabación propia opcional (sin texto, 14 días) o captura con `mosquitto_sub`.
14. Contenedor `10501:10500`; el volumen `alertas-socket` se crea externo con `/run/snm` en `2770`.
15. `afecta_malla` es propiedad fija de cada regla (código), no de `reglas.yaml`.

---
> Creado: 2026-10-07 · Última revisión: 2026-10-07
