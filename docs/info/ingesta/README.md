# 05 · Ingesta

> Servicio Python propio que convierte el tráfico crudo del broker en datos limpios: descifra, decodifica, deduplica, asigna provincia, guarda el histórico en TimescaleDB, publica un mensaje normalizado por paquete en `snm/v1/decoded/<portnum>` y mantiene las vistas contrato `api_*` que lee el portal.
> **Tipo:** desarrollo propio (Python) · **Fase:** paso 4 del orden de despliegue (`../integration.md` §15) · **Complejidad:** alta · **Monorepo:** `services/ingesta/`

## 1. Contexto

- Es la única pieza propia que entiende el protocolo de la malla (protobuf, cifrado de canal, duplicados entre gateways). El detector y el portal consumen datos ya limpios.
- Se apoya en lo desplegado antes: broker con usuario por gateway y ACL de 14 canales (`../mosquitto/`), PostgreSQL 17 nativo del servidor con TimescaleDB y la base `ingest` (`../infrastructure/02-postgresql.md`). Dos reglas probadas en mallas reales: el canal se identifica por el nombre del topic y `SFNarrow` es el canal 0.
- Se necesita para el mapa por provincias con color por carga, los rankings de consumo y nodos en peligro, las alertas y "Revisa tu nodo".

| Consumidor | Qué usa | Vía |
|---|---|---|
| `detector-alertas` | Flujo `snm/v1/decoded/#` | MQTT, usuario `svc-detector` |
| Portal: mapa, rankings, `/routers`, revisa tu nodo, panel | Vistas `api_*` de `ingest` | PostgreSQL, rol `portal_lector_ingesta` |
| Panel de operadores | `GET /health` | `http://ingesta:8080/health` (red `mesh`) |

## 2. Alcance

**Incluye**

- Suscripción a `msh/EU_868/2/e/#` y `msh/EU_868/2/map/#`; validación de gateway y de canal (segunda barrera tras la ACL); descifrado AES-CTR con la clave por defecto, paquetes en claro y PKI solo con metadatos.
- Decodificación de nodeinfo, posición, telemetría, texto, vecinos, traceroute, routing y map report; deduplicación `(from, id)`; recepciones por gateway; saltos; enlaces directos; tiempo en el aire; reinicios.
- Provincia por polígonos (ISO 3166-2 o `FUERA`); registros de nodos y de gateways.
- Persistencia en TimescaleDB con agregados continuos, compresión y retención; vistas `api_*` con su `GRANT`; flujo `decoded`; `/health`.

**Fuera de esta entrega**

- Reglas de alertas (`../detector-alertas/`), API pública y páginas (`../portal/`), alimentación de PotatoMesh (`../potatomesh/adaptador-potato.md`, que descifra por su cuenta).
- JSON del firmware, exclusión de nodos, MQTT público de eventos. Recuperar lo emitido mientras el servicio está parado (QoS 0 y sesión limpia, `../integration.md` §14) y más de una réplica (la deduplicación vive en memoria).
- Ampliaciones posibles: atribuir retransmisiones con `relay_node`, exportar métricas Prometheus, claves de canal adicionales, varios presets de radio.

## 3. Stack y versiones

| Componente | Versión | Uso |
|---|---|---|
| Python | 3.13, imagen `python:3.13-slim-trixie` (Docker Hub; fijar parche al crear) | Servicio `asyncio`, un proceso |
| `uv`, `pytest`, `pytest-asyncio` | Fijar al crear (última probada) | `pyproject.toml` + `uv.lock`; pruebas |
| `aiomqtt` (sobre `paho-mqtt`) | Fijar al crear | Cliente MQTT asíncrono |
| `meshtastic` (PyPI, GPL-3.0) | Fijar a la que acompañe al firmware estable de la malla | Solo protobufs oficiales (`meshtastic.protobuf.*_pb2`) |
| `protobuf` | La que exija `meshtastic` | Decodificación |
| `cryptography` | Fijar al crear | AES-CTR |
| `asyncpg` | Fijar al crear | PostgreSQL, lotes con `COPY` |
| `shapely` | 2.x, fijar al crear | Punto en polígono y distancias |
| `aiohttp` | Fijar al crear | Servidor de `/health` |
| PostgreSQL | 17, nativo del servidor | Base `ingest` |
| TimescaleDB | ≥ 2.17 (primera con PostgreSQL 17), edición Community del repositorio apt de Timescale; fijar al crear | Hypertables, agregados continuos, compresión, retención, tareas |

El servicio se publica con licencia AGPL-3.0, compatible con los protobufs oficiales bajo GPL-3.0.

## 4. Contratos

### 4.1 Entrada (MQTT)

| Elemento | Valor |
|---|---|
| Conexión | `mosquitto:1884` (listener interno, red `mesh`), MQTT 3.1.1, client id `ingesta`, sesión limpia |
| Usuario | `svc-ingest` (`MQTT_USER` / `MQTT_PASSWORD`) |
| Suscripciones (QoS 0) | `msh/EU_868/2/e/#` (`ServiceEnvelope` con `MeshPacket`), `msh/EU_868/2/map/#` (map report en claro) y `snm/v1/peer/#` (eventos de instancias PotatoMesh vecinas) |
| Topic de gateway | `msh/EU_868/2/e/<canal>/<!id_gateway>`, `<canal>` ∈ `ALLOWED_CHANNELS` |
| Topic de peers | `snm/v1/peer/<peer_id>/<type>`, publicado por `sync-peers` |

ACL que necesita en el broker (`../mosquitto/`):

```text
user svc-ingest
topic read msh/EU_868/#
topic read snm/v1/peer/#
topic write snm/v1/decoded/#
```

Validación, canal y descifrado: [01-input-decryption.md](01-input-decryption.md).

### 4.2 Salida `decoded` (MQTT)

- Topic `snm/v1/decoded/<portnum>`, `<portnum>` ∈ `nodeinfo`, `position`, `telemetry`, `text`, `neighborinfo`, `traceroute`, `routing`, `map_report`, `other`. QoS 0, sin retained, JSON UTF-8. Un mensaje por paquete único, al cerrar la ventana de 2 s desde la primera recepción (retraso < 5 s).
- Campos: `v` (1), `packet_id`, `from`, `from_node` (`short`, `long`, `role`, `hw`, `is_gateway`, `province`), `to`, `portnum`, `channel`, `rx_first`, `hop_start`, `hops_min`, `via_mqtt`, `ok_to_mqtt`, `airtime_ms`, `receptions[]` (`gateway`, `snr`, `rssi`, `hops`, `at`), `payload`.
- Esquema completo, ejemplos por tipo y reglas de nulos: [06-decoded-stream.md](06-decoded-stream.md).

### 4.3 Base de datos

| Elemento | Valor |
|---|---|
| Conexión | `172.30.0.1:5432`, base `ingest`, rol propietario `ingest`, `scram-sha-256` |
| Superficie para otros | Solo vistas `api_*`, con `GRANT SELECT` a `portal_lector_ingesta` en cada migración que las toque |
| Vistas | `api_nodes`, `api_province_load`, `api_routers`, `api_gateways`, `api_summary`, `api_traffic_mix`, 11 `api_rank_<id>`, `api_node_intervals`, `api_node_battery_daily`, `api_node_reboots_daily` |

Columnas, tipos, origen y ventana: [05-contract-views.md](05-contract-views.md). Tablas internas (sin contrato): [04-storage-retention.md](04-storage-retention.md).

### 4.4 Salud

`GET http://ingesta:8080/health` → `200` con `{"ok": true, …}`, o `503` con `"ok": false` si MQTT o la base llevan más de 60 s caídos. Incluye antigüedad del último mensaje, ritmo y contadores ([06-decoded-stream.md](06-decoded-stream.md)). El panel lo marca en rojo si el último mensaje supera 10 min.

## 5. Configuración

`env_file`: `/srv/comun/.env` (común) y después `/srv/ingesta/.env` (propio).

| Variable | Valor o ejemplo | Común / propia | Secreto |
|---|---|---|---|
| `PROJECT_NAME` | `Sur Nodos en Mallas` | común | no |
| `TZ` | `Europe/Madrid` (días de agregados y rankings) | común | no |
| `MQTT_HOST` / `MQTT_PORT` | `mosquitto` / `1884` | común | no |
| `MQTT_TOPIC_ROOT` | `msh/EU_868` | común | no |
| `MQTT_TOPIC_PREFIX` | `snm` | común | no |
| `ALLOWED_CHANNELS` | `SFNarrow,Iberia,Andalucia,Cadiz,Huelva,Almeria,Granada,Jaen,Sevilla,Cordoba,Malaga,Ceuta,Melilla,sos` | común | no |
| `PRIMARY_CHANNEL` | `SFNarrow` | común | no |
| `CHANNEL_KEY_DEFAULT` | PSK por defecto en base64 (valor en `.env.comun`) | común | no |
| `INFRA_ROLES` | `ROUTER,ROUTER_LATE,REPEATER` | común | no |
| `LORA_BANDWIDTH` / `LORA_SPREAD_FACTOR` / `LORA_CODING_RATE` | `62` / `7` / `5` | común | no |
| `DB_HOST` / `DB_PORT` | `172.30.0.1` / `5432` | común | no |
| `MQTT_USER` | `svc-ingest` | propia | no |
| `MQTT_PASSWORD` | — | propia | sí |
| `DB_NAME` / `DB_USER` | `ingest` / `ingest` | propia | no |
| `DB_PASSWORD` | — | propia | sí |
| `INGESTA_VENTANA_PUBLICACION_S` / `INGESTA_VENTANA_DEDUP_MIN` | `2` / `15` | propia | no |
| `INGESTA_POLIGONOS` | `/app/polygons/provincias-andalucia.geojson` | propia | no |
| `INGESTA_PRECISION_MAX_M` / `INGESTA_MARGEN_MIN_M` | `12000` (peor precisión válida) / `500` (margen costero) | propia | no |
| `INGESTA_ENLACE_PRECISION_MAX_M` / `INGESTA_ENLACE_MAX_KM` | `3000` / `300` | propia | no |
| `INGESTA_LORA_PREAMBULO` | `16` | propia | no |
| `INGESTA_LOTE_FILAS` / `INGESTA_LOTE_MS` / `INGESTA_BUFFER_MAX_FILAS` | `500` / `1000` / `100000` | propia | no |
| `HEALTH_PORT` / `LOG_LEVEL` | `8080` / `INFO` | propia | no |
| `INGESTA_IMAGEN` | `snm-ingesta:<versión>` (solo para `compose.yaml`) | propia | no |

## 6. Datos

| Elemento | Detalle | Retención |
|---|---|---|
| Hypertables en bruto | `packet` (paquete único), `reception` (una fila por gateway), `neighbor` | 30 días; compresión a los 2 días |
| Hypertables de posición y telemetría | `position`, `telemetry_device`, `telemetry_env`, `local_stats` | 90 días; compresión a los 7 días |
| Registros | `node`, `gateway` | Se borran tras 1 año sin actividad |
| Agregados por hora con nodo o gateway | `agg_*_hour` | 30 días |
| Agregados por día con nodo o gateway | `agg_*_day` | 1 año |
| Agregados sin nodo | `agg_traffic_hour`, `agg_traffic_day` | Indefinida |
| Vistas contrato | `api_*` | Heredan las anteriores |
| Volumen | `./poligonos` → `/app/poligonos` (solo lectura), `provincias-andalucia.geojson` | En git |

- Sin tabla de exclusión de nodos. Orientativo (2026-10): 200.000–400.000 paquetes únicos/día y 2–3 recepciones por paquete; < 5 GB en disco con compresión.
- Esquema, agregados, tareas y migraciones: [04](04-storage-retention.md). Vistas: [05](05-contract-views.md). Polígonos: [03](03-provinces-registry.md).

## 7. Unidades de trabajo

Canalización por mensaje (un proceso `asyncio`, una réplica):

1. **Entrada (01):** topic → `ServiceEnvelope` → gateway → canal permitido → cabecera válida.
2. **Deduplicación (02):** `(from, id)` visto en 15 min → solo se añade la recepción. Fin.
3. **Contenido (01):** paquete nuevo → `Data` en claro (ya descifrado, AES-CTR o solo metadatos).
4. **Decodificación (02):** campos por tipo, bit OK to MQTT, tiempo en el aire, reinicios.
5. **Registros y provincia (03):** se actualizan antes de publicar, para que `from_node` refleje el propio paquete.
6. **Ventana de 2 s (02):** acumula recepciones.
7. **Cierre:** publica `decoded` (06) y encola filas (04). Las recepciones tardías se guardan sin republicar.

| Módulo | Archivo | Resuelve | UT |
|---|---|---|---|
| 01 | [01-input-decryption.md](01-input-decryption.md) | Suscripción, topic, gateway, lista blanca, canal, AES-CTR, PKI | UT-05.1.1 – UT-05.1.5 |
| 02 | [02-decoding-dedup.md](02-decoding-dedup.md) | Tipos, cabecera, OK to MQTT, deduplicación, recepciones, saltos, enlaces, tiempo en el aire, reinicios | UT-05.2.1 – UT-05.2.6 |
| 03 | [03-provinces-registry.md](03-provinces-registry.md) | Polígonos, provincia, precisión, registros de nodos y gateways | UT-05.3.1 – UT-05.3.4 |
| 04 | [04-storage-retention.md](04-storage-retention.md) | Esquema, hypertables, agregados, compresión, retención, migraciones | UT-05.4.1 – UT-05.4.4 |
| 05 | [05-contract-views.md](05-contract-views.md) | Vistas `api_*` y permisos | UT-05.5.1 – UT-05.5.4 |
| 06 | [06-decoded-stream.md](06-decoded-stream.md) | Publicación `decoded`, salud y contadores | UT-05.6.1 – UT-05.6.3 |

Orden sugerido: UT-05.1 → UT-05.2 → módulos 01 a 06 → UT-05.3 a UT-05.6.

### UT-05.1 — Esqueleto, configuración y logs

- **Comportamiento:** al arrancar valida la configuración, aplica migraciones, carga registros y caché de deduplicación, conecta a MQTT y abre `/health`.
- **Detalle:** validación de `ALLOWED_CHANNELS` no vacío, `PRIMARY_CHANNEL` incluido, PSK de 0, 1, 16 o 32 B y polígonos con 8 provincias. Logs JSON por línea en stdout (`ts`, `nivel`, `evento`, campos); nunca el texto de los mensajes ni payloads completos. Resumen cada 60 s con ritmo y contadores.
- **Casos borde:** variable obligatoria ausente o inválida → sale con código 2 y un mensaje que la nombra; `LOG_LEVEL=DEBUG` sigue sin volcar payloads.
- **Aceptación:** con un `.env` incompleto el contenedor sale nombrando la variable; con uno correcto `/health` responde en < 60 s.

### UT-05.2 — Migraciones al arrancar

- **Comportamiento:** aplica las migraciones pendientes antes de leer MQTT; si fallan, el servicio no arranca.
- **Detalle:** runner propio descrito en [04](04-storage-retention.md) (bloqueo consultivo, archivos sin transacción para agregados, vistas en migración repetible con `{{TZ}}` e `{{INFRA_ROLES}}`, re-`GRANT`).
- **Casos borde:** dos arranques simultáneos (uno espera al bloqueo); migración ya aplicada con checksum distinto → error y parada.
- **Aceptación:** desde una base vacía con la extensión creada, el primer arranque deja todo el esquema; el segundo no cambia nada.

### UT-05.3 — Canalización MQTT, reconexión y apagado

- **Comportamiento:** lee del broker sin bloquearse, procesa en orden de llegada y publica por la misma conexión.
- **Detalle:** reconexión con espera 1 → 30 s y re-suscripción; `SIGTERM`: deja de leer, cierra las ventanas abiertas publicándolas, vacía lotes y sale en ≤ 25 s.
- **Casos borde:** broker caído al arrancar (reintenta sin salir; `ok=false` tras 60 s); mensaje que provoca excepción (se cuenta `error_interno`, se registra y se sigue).
- **Aceptación:** reiniciar Mosquitto no requiere intervención; `docker compose stop` no pierde filas ya recibidas.

### UT-05.4 — Escritura por lotes y caídas de la base

- **Comportamiento:** escribe en la base sin frenar la canalización y aguanta caídas cortas de PostgreSQL.
- **Detalle:** buffer por tabla, `COPY` cada `INGESTA_LOTE_MS` o `INGESTA_LOTE_FILAS`; en el mismo ciclo, actualizaciones por recepciones tardías y filas sucias de `node` y `gateway`. Si la base falla: reintento 1 → 30 s conservando hasta `INGESTA_BUFFER_MAX_FILAS`; por encima se descartan primero las filas más antiguas de `reception` (`filas_descartadas_bd`). `decoded` se sigue publicando.
- **Casos borde:** conflicto de clave única en un lote → se reintenta ese lote con `INSERT … ON CONFLICT DO NOTHING`.
- **Aceptación:** con PostgreSQL parado 2 min a 50 msg/s no se pierde ninguna fila y el detector sigue recibiendo `decoded`.

### UT-05.5 — Contenedor y despliegue

- **Comportamiento:** imagen propia reproducible, desplegable con `deploy.sh ingesta` (construye en el servidor).
- **Detalle:** Dockerfile multietapa sobre la imagen fijada, usuario no root, `uv sync --frozen`; pruebas antes de etiquetar; imagen `snm-ingesta:<versión>` construida en el servidor; `compose.yaml` y `.env.example` de §8 en `services/ingesta/`, junto a `polygons/` y `migrations/`.
- **Casos borde:** volumen de polígonos ausente → el servicio no arranca (UT-05.1).
- **Aceptación:** en `/srv/ingesta/` el contenedor queda `healthy` sin pasos manuales extra.

### UT-05.6 — Pruebas extremo a extremo y de carga

- **Comportamiento:** batería que levanta Mosquitto y PostgreSQL + TimescaleDB en contenedores y un inyector de `ServiceEnvelope` sintéticos (varios gateways, duplicados, cifrados y en claro, map reports).
- **Detalle:** comprueba filas, vistas y mensajes `decoded`; carga de 200 msg/s durante 15 min con 30 % de duplicados. Paquetes de prueba generados por la propia batería (sin capturas reales en git).
- **Casos borde:** ráfaga de 1.000 msg en 1 s.
- **Aceptación:** retraso p99 de `decoded` < 5 s, CPU < 1 vCPU, memoria < 400 MB, sin cola creciente.

## 8. Despliegue

| Contenedor | Imagen | Redes | Volúmenes | Puertos | Publicación | Healthcheck |
|---|---|---|---|---|---|---|
| `ingesta` | `${INGESTA_IMAGEN}` (`snm-ingesta:<versión>`, base `python:3.13-slim-trixie` fijada) | `mesh` (externa) | `./poligonos:/app/poligonos:ro` | Ninguno publicado; 8080 solo en `mesh` | Ninguna | `GET http://127.0.0.1:8080/health` cada 30 s |

```yaml
services:
  ingesta:
    image: ${INGESTA_IMAGEN}
    container_name: ingesta
    restart: unless-stopped
    env_file: [/srv/comun/.env, .env]
    networks: [mesh]
    volumes:
      - ./poligonos:/app/poligonos:ro
    stop_grace_period: 30s
    mem_limit: 512m
    healthcheck:
      test: ["CMD", "python", "-c", "import urllib.request; urllib.request.urlopen('http://127.0.0.1:8080/health', timeout=4)"]
      interval: 30s
      timeout: 5s
      retries: 3
      start_period: 60s
networks:
  mesh:
    external: true
```

Pasos, en orden:

1. **Infraestructura** (`../infrastructure/02-postgresql.md`): base `ingest` con propietario `ingest`; `CREATE EXTENSION timescaledb` como superusuario; `shared_preload_libraries = 'timescaledb'`, `timescaledb.license = 'timescale'`, `timescaledb.max_background_workers ≥ 16`; rol `portal_lector_ingesta` creado sin permisos; `pg_hba`: `host ingest ingest 172.30.0.0/24 scram-sha-256` (y la línea del lector).
2. **Broker** (`../mosquitto/`): usuario `svc-ingest` con la ACL de §4.1.
3. En `/srv/ingesta/`: `compose.yaml`, `.env` desde `.env.example`, `polygons/provincias-andalucia.geojson` del monorepo.
4. `deploy.sh ingesta`; en los logs: migraciones aplicadas y conexión MQTT.
5. Salud desde la red `mesh`: `docker run --rm --network mesh <imagen curl fijada> -s http://ingesta:8080/health`.
6. Datos: `SELECT count(*) FROM packet WHERE rx_first > now() - interval '5 minutes'` (rol `ingest`) y `SELECT * FROM api_summary` (rol lector).
7. Flujo: `mosquitto_sub -t 'snm/v1/decoded/#'` con el usuario `svc-detector` cuando exista.

Actualización: nueva etiqueta git → `deploy.sh ingesta`; las migraciones corren solas. Una versión que recree agregados lo indica en sus notas.

**Copias de seguridad:** fuera del proyecto (sistema del operador). Restaurar `ingest` exige `timescaledb_pre_restore()` / `timescaledb_post_restore()` y la misma versión de TimescaleDB. Polígonos y migraciones están en git; la caché de deduplicación se reconstruye sola.

## 9. Definición de hecho

- [x] Todas las UT (transversales y de módulo) cumplen su criterio de aceptación.
- [x] Contenedor `ingesta` `healthy` en el servidor, solo en la red `mesh`, sin `ports:`.
- [x] Migraciones aplicadas desde base vacía y re-ejecutables sin cambios.
- [x] Deduplicación y acumulación multigateway: 1 fila en `packet` y N en `reception` por paquete.
- [x] `snm/v1/decoded/#` emitido tras ventana de 2 s con `from_node` y `airtime_ms`.
- [x] Existen todas las 20 vistas de `../integration.md` §8; `portal_lector_ingesta` lee `api_*` y tiene denegado el acceso a tablas base.
- [x] Asignación provincial geoespacial operativa (Jerez → `ES-CA`, Sevilla → `ES-SE`).
- [x] Tareas de TimescaleDB programadas (`limpiar_registros`, compresión y retención).
- [x] Sin secretos en git; `.env.example` y documentación completa.

## 10. Escenarios de prueba

- **Dado** dos gateways que oyen el mismo paquete de telemetría con 1 s de diferencia, **cuando** llegan ambos, **entonces** hay 1 fila en `packet`, 2 en `reception` y 1 mensaje en `snm/v1/decoded/telemetry` con 2 recepciones.
- **Dado** un mensaje en un canal fuera de la lista (si la ACL fallara), **cuando** llega, **entonces** sube `canal_no_permitido` y no aparece ni en la base ni en `decoded`.
- **Dado** el servicio reiniciado, **cuando** llegan duplicados de paquetes de los 15 min anteriores, **entonces** no se crean filas nuevas en `packet`.
- **Dado** PostgreSQL parado 2 min, **cuando** vuelve, **entonces** las filas del intervalo están en la base y el detector recibió `decoded` durante la caída.
- **Dado** el rol `portal_lector_ingesta`, **cuando** ejecuta `SELECT * FROM packet`, **entonces** obtiene `permission denied`; `SELECT * FROM api_summary` funciona.
- **Dado** 200 msg/s durante 15 min, **cuando** termina la prueba, **entonces** el retraso p99 de `decoded` es < 5 s y la memoria es estable.

## 11. Riesgos y limitaciones

- **Edición de TimescaleDB:** el paquete de Debian es la edición Apache, sin agregados continuos, compresión ni políticas. Debe instalarse del repositorio de Timescale; la primera migración comprueba la licencia y aborta si no es `timescale`.
- **Cambios del firmware:** campos nuevos se ignoran y `portnum` nuevos van a `other`; se actualiza `meshtastic` tras probar. Licencia AGPL-3.0 (compatible con GPL-3.0 por los protobufs).
- **Map reports:** la ACL no ata `…/2/map/` al usuario que publica; un gateway podría enviar map reports con otro id. Se descarta si `from` ≠ `gateway_id`, pero no evita una suplantación deliberada. Aceptado.
- **Pérdida mientras está parado:** QoS 0 sin sesión persistente (`../integration.md` §14); igual para el detector con `decoded`. Una sola réplica: si el proceso cae, no hay datos nuevos hasta que Docker lo reinicia.
- **Estimaciones:** el tiempo en el aire no incluye retransmisiones ni reintentos (±2 B en paquetes en claro); solo se ve lo que llega con OK to MQTT a nuestros gateways.
- **Provincias:** la frontera exterior (Portugal, otras comunidades, Gibraltar) no se marca como incierta; posiciones costeras se asignan por cercanía; simplificación de polígonos.
- **Rendimiento:** filas `week`/`month` de rankings (70 días) en ~100–300 ms; el portal las cachea (TTL de su ficha).

## 12. Referencias

- Contrato común: `../integration.md` (§4 redes, §5 MQTT, §6 `decoded`, §8 vistas, §11 salud, §12 `.env.comun`, §14 fallos).
- Piezas relacionadas: `../infrastructure/02-postgresql.md`, `../mosquitto/README.md`, `../portal/12-stats-api.md`, `../portal/13-nodes-alerts-api.md`, `../portal/09-node-check.md`, `../detector-alertas/README.md`.
- Fuentes externas: documentación MQTT del firmware (`ServiceEnvelope`, OK to MQTT, map reports; cifrado, precisión, telemetría).
- Semtech AN1200.13 (tiempo en el aire LoRa); protobufs oficiales (`meshtastic/protobufs`); límites provinciales del CNIG.

## Decisiones de detalle

1. Un solo proceso y una réplica; deduplicación en memoria con precarga de los últimos 15 min al arrancar.
2. Recepciones tardías (> 2 s): se guardan y actualizan `packet`, pero no se republican.
3. Paquete no descifrable: metadatos y publicado como `other` con `payload: {}`. PKI: solo metadatos, no se publica.
4. Bit OK to MQTT como segunda barrera: paquete ajeno al gateway sin el bit → descartado (misma regla que el firmware).
5. Map reports: no son radio. `airtime_ms = 0`, sin filas de `reception`, fuera de tráfico y rankings de consumo; en `decoded`, `channel` null y una recepción del gateway con valores de radio null.
6. Paquetes propios del gateway: recepción con `snr`/`rssi` null, nunca enlace directo. Canal comparado sin tildes ni mayúsculas, guardado con la grafía de `ALLOWED_CHANNELS`.
7. Provincia fuera de los polígonos: la más cercana si está a menos de max(precisión, 500 m); si no, `FUERA`. `precision_bits` 0 o ausente = precisión completa.
8. Reinicio: `uptime` menor que el esperado menos max(300 s, 5 % del tiempo transcurrido). Batería válida: 1–100, o 0 con tensión > 0; > 100 = alimentado.
9. Rankings y `api_traffic_mix` traen además filas `week` y `month` (actual y anterior); son imprescindibles en los rankings de recuentos distintos.
10. `api_node_intervals` añade `variant` (variante de telemetría): una fila por nodo × tipo × variante.
11. Agregados por hora con nodo: 30 días; por día: 1 año; registros `node`/`gateway` se borran tras 1 año sin actividad.
12. Migraciones SQL propias numeradas (sin Alembic); vistas en una migración repetible. Sin `/metrics` Prometheus: contadores en `/health`. Horas con milisegundos en `decoded`.
13. Trazabilidad de descartes mediante `DiscardTracker`: contadores agregados en memoria por motivo, registro muestreado con limitación de tasa (30 s), búfer circular de 50 eventos recientes y resumen consolidado cada 10 minutos (y al apagar), expuesto en `/health` para monitorización operativa sin sobrecarga de disco.

---
> Creado: 2026-10-07 · Última revisión: 2026-10-09
