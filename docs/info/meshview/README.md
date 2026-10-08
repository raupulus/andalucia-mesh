# 03 · MeshView

> Visor técnico de la malla (paquetes en vivo, conversaciones, nodos, mapa, traceroutes, grafo, fiabilidad y estadísticas), proyecto de comunidad que lee MQTT directamente y guarda en PostgreSQL. **Tipo:** comunidad (MeshView) · **Fase:** 3 · **Complejidad:** baja · **Monorepo:** `integrations/meshview/`

## 1. Contexto

- **Requisitos:** desplegar MeshView en lugar de desarrollar un visor propio, en `meshview.${PROJECT_DOMAIN}`.
- **Proyecto:** `pablorevilla-meshtastic/meshview`, continuación activa de `armooo/meshview` (sin cambios desde 2024). Versión **3.0.8** (2026-09-09), AGPL-3.0, Python asíncrono (aiohttp, aiomqtt, SQLAlchemy async, Alembic). Instancias públicas con 1.000–4.800 nodos, del orden de la malla prevista.
- **Quién lo consume:** el público (enlace desde la tarjeta de la portada) y el panel de operadores (`/health`). Nadie lee su base de datos.

## 2. Alcance

**Incluye:** compose con la imagen fijada; `config.ini` generado desde plantilla con variables (sin secretos en git); conexión al broker interno con `svc-meshview`; base `meshview` en el PostgreSQL nativo; publicación por Nginx en `127.0.0.1:8081`; idioma español, encuadre de Andalucía (con Ceuta y Melilla), textos propios y refresco reducido; retención de 14 días; salud en el panel; procedimiento de actualización.

**Fuera de esta entrega:** cambios en el código de MeshView (se proponen upstream); cobertura predicha (`pyitm`, su API responde 503); vista "net" semanal; claves secundarias de canal (solo clave por defecto); `skip_node_ids`; copias internas de MeshView (las copias son del operador); SQLite.

## 3. Stack y versiones

| Elemento | Valor |
|---|---|
| Imagen | `ghcr.io/pablorevilla-meshtastic/meshview:<etiqueta de 3.0.8>` — fijar al crear la etiqueta exacta de la release 3.0.8 en GHCR (formato `3.0.8`/`v3.0.8` sin confirmar) y anotar el digest `@sha256:` en el compose |
| Base de la imagen | `python:3.13-slim`, usuario no root UID `10001` (comprobar con `docker image inspect -f '{{.Config.User}}'`) |
| Procesos | `mvrun.py` (lector MQTT + tareas programadas + web) en un único contenedor, como el ejemplo de `README-Docker.md` |
| Puerto interno | `8081` (defecto `[server] port`) |
| Base de datos | PostgreSQL 17 nativo del servidor, `postgresql+asyncpg`, esquema migrado por Alembic al arrancar |
| Licencia | AGPL-3.0: se usa sin modificar; crédito en el aviso legal del portal |

## 4. Contratos

**Entrada MQTT** (`integration.md` §5)

| Elemento | Valor |
|---|---|
| Broker | `mosquitto:1884` (listener interno, red `mesh`) |
| Usuario | `svc-meshview` (`MQTT_USER`), contraseña `MQTT_PASSWORD`; ACL: **lectura `msh/EU_868/#`**, nada más |
| Suscripción | `${MQTT_TOPIC_ROOT}/#` = `msh/EU_868/#` |
| Topics que llegan | `msh/EU_868/2/e/<canal>/<!id_gateway>` (`ServiceEnvelope` protobuf, solo los 14 canales de `ALLOWED_CHANNELS`, filtrados por la ACL del broker) y `msh/EU_868/2/map/` (`MAP_REPORT_APP`) |
| No existen | Downlink, LWT/estado, `…/2/stat/`, JSON del firmware, `…/2/e/PKI/` (la ACL no los admite) |

MeshView descifra con la clave por defecto y descarta lo que no decodifica. No publica nada (la ACL se lo impediría).

**Salida HTTP pública:** `https://meshview.${PROJECT_DOMAIN}`: web y API JSON de solo lectura (`/api/nodes`, `/api/packets`, `/api/channels`, `/api/stats`, `/api/stats/count`, `/api/stats/top`, `/api/snapshots/daily`, `/api/edges`, `/api/config`, `/api/lang`, `/api/packets_seen/{packet_id}`, `/api/traceroute/{packet_id}`, `/api/node/{node_id}/qr`). Sin escritura ni autenticación.

**Salud** (`integration.md` §11): el panel hace `GET http://meshview:8081/health` → 200. También `/version`.

**PostgreSQL** (`integration.md` §8): `172.30.0.1:5432`, base `meshview`, rol propietario `meshview`, `scram-sha-256`, solo desde `172.30.0.0/24`.

## 5. Configuración

MeshView solo lee `config.ini` (no lee variables de entorno). El despliegue lo genera desde `plantilla/config.ini.plantilla` con las variables siguientes (ver UT-03.1).

| Variable | Valor o ejemplo | Común / propia | Secreto |
|---|---|---|---|
| `PROJECT_NAME` | `Sur Nodos en Mallas` | Común | No |
| `PROJECT_DOMAIN` | `mesh.example.org` | Común | No |
| `TZ` | `Europe/Madrid` | Común | No |
| `MQTT_HOST` / `MQTT_PORT` | `mosquitto` / `1884` | Común | No |
| `MQTT_TOPIC_ROOT` | `msh/EU_868` | Común | No |
| `DB_HOST` / `DB_PORT` | `172.30.0.1` / `5432` | Común | No |
| `MQTT_USER` | `svc-meshview` | Propia (`/srv/meshview/.env`) | No |
| `MQTT_PASSWORD` | 24 alfanuméricos (`mqtt-users.sh service svc-meshview`) | Propia | **Sí** |
| `DB_NAME` / `DB_USER` | `meshview` / `meshview` | Propia | No |
| `DB_PASSWORD` | 32 alfanuméricos (`set-role-password.sh meshview`) | Propia | **Sí** |

`ALLOWED_CHANNELS` no se usa aquí: el broker ya solo entrega los 14 canales.

Plantilla (`integrations/meshview/plantilla/config.ini.plantilla`, en git, sin secretos):

```ini
[server]
bind = *
port = 8081

[site]
domain = meshview.${PROJECT_DOMAIN}
title = ${PROJECT_NAME}
message = Visor técnico de la malla de ${PROJECT_NAME}. Más información: https://${PROJECT_DOMAIN}
language = es
starting = /map
nodes = True
conversations = True
everything = True
graphs = True
stats = True
net = False
map = True
top = True
map_top_left_lat = 38.8
map_top_left_lon = -7.6
map_bottom_right_lat = 35.2
map_bottom_right_lon = -1.5
map_interval = 10
firehose_interval = 10

[mqtt]
server = ${MQTT_HOST}
port = ${MQTT_PORT}
topics = ["${MQTT_TOPIC_ROOT}/#"]
username = ${MQTT_USER}
password = ${MQTT_PASSWORD}
skip_node_ids =
secondary_keys =

[database]
connection_string = postgresql+asyncpg://${DB_USER}:${DB_PASSWORD}@${DB_HOST}:${DB_PORT}/${DB_NAME}

[cleanup]
enabled = True
days_to_keep = 14
hour = 2
minute = 00
vacuum = False
backup_enabled = False

[snapshot]
hour = 1
minute = 00

[logging]
access_log = False
db_cleanup_logfile = /var/log/meshview/dbcleanup.log
```

| Clave | Motivo |
|---|---|
| `title`, `message`, `domain` | Únicos textos de marca que controlamos: nombre y portal por variable, sin marcas de terceros |
| `language = es`, `starting = /map` | Interfaz en español; entrada visual con el chat a un clic |
| Encuadre | Andalucía completa más Ceuta (35,89) y Melilla (35,29), que tienen canal propio |
| `map_interval`, `firehose_interval` = 10 | Defecto 3 s; con 100 usuarios reduce ~3× las peticiones. Se escribe `firehose_interval` (el `sample.config.ini` trae la errata `firehose_interal`; el código lee la forma correcta) |
| `net = False` | No hay "net" semanal en la comunidad |
| `skip_node_ids` vacío | Sin exclusión de nodos |
| `secondary_keys` vacío | Solo canales con clave por defecto |
| `vacuum = False`, `backup_enabled = False` | Autovacuum de PostgreSQL; copias con el sistema del operador |
| `access_log = False` | Sin registro de accesos (privacidad; Nginx tampoco lo lleva) |

## 6. Datos

| Elemento | Detalle |
|---|---|
| Base `meshview` | Esquema propio de MeshView (tablas `packet`, `packet_seen`, `traceroute`, `node`, `daily_snapshot`; marcas en microsegundos). Lo crea y migra Alembic al arrancar. Nadie más la lee |
| Retención | `[cleanup]` diario a las 02:00: borra lo de más de 14 días. Peor caso ~8,6 GB (`overview.md` §6). Las instantáneas diarias se conservan (pequeñas) |
| `/srv/meshview/datos/config/` | `config.ini` generado (0600, UID 10001). Se regenera en cada despliegue; no se copia |
| `/srv/meshview/datos/logs/` | `dbcleanup.log` (una línea por día) |
| Copia | Fuera del proyecto (base `meshview`, `pg_dump -Fc`) |

## 7. Unidades de trabajo

### UT-03.1 — Plantilla y generador de `config.ini`
- **Comportamiento:** el despliegue genera `/etc/meshview/config.ini` a partir de la plantilla y del entorno (`.env.comun` + `.env`), sin secretos en git.
- **Detalle:** contenedor de un solo uso `meshview-config` con la misma imagen (trae Python) que ejecuta `plantilla/generar-config.py` (código de adaptación propio): `string.Template(...).substitute(os.environ)`; valida que `MQTT_PASSWORD` y `DB_PASSWORD` sean `[A-Za-z0-9]+` (no necesitan escape en URL ni en INI); escribe en temporal y renombra, modo 0600; `network_mode: none`. `meshview` arranca con `depends_on: condition: service_completed_successfully`.
- **Casos borde:** variable ausente → sale con código 1 nombrando la variable y `meshview` no arranca; clave con símbolos → error explícito; reinicio del host → `meshview` arranca con el `config.ini` ya generado.
- **Aceptación:** `config.ini` con valores reales y permisos 0600; `git grep` en el monorepo no encuentra ninguna contraseña; con `.env` incompleto el despliegue falla antes de levantar `meshview`.

### UT-03.2 — Base de datos y primer arranque
- **Comportamiento:** MeshView conecta a `meshview` y Alembic crea el esquema.
- **Detalle:** base y rol los crea `../infrastructure/02-postgresql.md` (`databases.sql`), el rol es propietario (puede crear tablas en `public`). `pg_hba` ya admite `172.30.0.0/24`.
- **Casos borde:** PostgreSQL caído al arrancar → el contenedor sale y `restart: unless-stopped` lo reintenta; migración a medias → se restaura el volcado previo.
- **Aceptación:** `\dt` en `meshview` muestra las tablas; los logs muestran la migración sin errores.

### UT-03.3 — Contenedor, red, Nginx y salud
- **Comportamiento:** `meshview` en `mesh`, publicado por Nginx en `meshview.${PROJECT_DOMAIN}` solo por HTTPS.
- **Detalle:** sitio `snm-meshview.conf` de Nginx; healthcheck con Python de la imagen contra `127.0.0.1:8081/health`; solo `127.0.0.1:8081:8081` en `ports:`.
- **Casos borde:** puerto publicado sin `127.0.0.1` → abierto a internet (lo detecta `check-compose.sh`); MeshView arrancado antes de que exista el usuario MQTT → reintenta conexión.
- **Aceptación:** `curl -sI https://meshview.${PROJECT_DOMAIN}` → 200 con las cabeceras de seguridad comunes; `docker ps` muestra `healthy`; `ss -tlnp` del host muestra el 8081 solo en `127.0.0.1`.

### UT-03.4 — Personalización
- **Comportamiento:** interfaz en español, mapa encuadrado, textos con el nombre y el portal, secciones activas salvo `net`, refresco a 10 s, formato de hora en 24 horas (`HH:mm:ss`) y fecha en formato peninsular español (`DD/MM/YYYY`) en `/chat` y `/net`.
- **Detalle:** configuración principal por `config.ini`; plantillas `chat.html` y `net.html` montadas en `:ro` sobre `/app/meshview/templates/` para forzar `timeZone: "Europe/Madrid"`, `hour12: false` y formato europeo (catalogado en [`../customizations.md`](../customizations.md), `CUST-01`).
- **Casos borde:** actualización de versión de MeshView → verificar y reaplicar plantillas según `customizations.md`.
- **Aceptación:** la web abre en `/map` en español con Andalucía, Ceuta y Melilla a la vista y el título `${PROJECT_NAME}`; `/chat` muestra mensajes con formato 24 horas (ej. `00:07:08 - 08/10/2026`).

### UT-03.5 — Retención
- **Comportamiento:** limpieza diaria a 14 días.
- **Detalle:** `[cleanup]` integrado (no pierde paquetes según su documentación).
- **Casos borde:** cambiar `days_to_keep` exige redesplegar (se regenera `config.ini` y se reinicia).
- **Aceptación:** tras 16 días no hay filas de `packet` con más de 14 días y el tamaño de la base se estabiliza (`pg_database_size` semanal).

### UT-03.6 — Carga y exposición de datos
- **Comportamiento:** aguanta 100 usuarios y no expone secretos.
- **Detalle:** prueba con 100 sesiones simuladas que piden mapa y firehose a su intervalo; revisar la respuesta de `/api/config`.
- **Casos borde:** si `/api/config` incluyese credenciales o la cadena de conexión, no se publica el host hasta resolverlo upstream.
- **Aceptación:** CPU del VPS < 60 % sostenido durante la prueba; `/api/config` sin `password` ni `connection_string`.

### UT-03.7 — Panel y actualización
- **Comportamiento:** el panel muestra MeshView en verde; actualizar es cambiar la etiqueta.
- **Detalle:** destino `http://meshview:8081/health` en `config/servicios.php` del portal; `integrations/meshview/README.md` con repositorio, versión fijada, licencia y procedimiento de §8.
- **Casos borde:** una versión que migra el esquema no se puede bajar sin restaurar el volcado previo.
- **Aceptación:** con `meshview` parado el panel lo marca caído en ≤ 1 min.

## 8. Despliegue

| Contenedor | Imagen | Redes | Volúmenes | Puertos | Publicación | Healthcheck |
|---|---|---|---|---|---|---|
| `meshview-config` (un solo uso) | La misma de `meshview` | `none` | `./plantilla:/plantilla:ro`, `/srv/meshview/datos/config:/etc/meshview` | — | — | — (termina con 0) |
| `meshview` | `ghcr.io/pablorevilla-meshtastic/meshview:<3.0.8>@sha256:…` | `mesh` | `/srv/meshview/datos/config:/etc/meshview:ro`, `/srv/meshview/datos/logs:/var/log/meshview`, `./templates/chat.html:/app/meshview/templates/chat.html:ro`, `./templates/net.html:/app/meshview/templates/net.html:ro` | `127.0.0.1:8081:8081` | Nginx (`snm-meshview.conf`) | `python3 -c "import urllib.request; urllib.request.urlopen('http://127.0.0.1:8081/health', timeout=5)"` cada 30 s, `start_period` 60 s |

Ambos con `env_file: [/srv/comun/.env, .env]`; `meshview` con `restart: unless-stopped`, `mem_limit: 768m`, `security_opt: ["no-new-privileges:true"]`.

Contenido de `integrations/meshview/`: `README.md` (referencia, versión, licencia, actualización), `compose.yaml`, `.env.example` (`MQTT_USER`, `MQTT_PASSWORD`, `DB_NAME`, `DB_USER`, `DB_PASSWORD`), `plantilla/config.ini.plantilla`, `plantilla/generar-config.py`, `templates/` (`chat.html`, `net.html`).

**Pasos en orden**

1. Requisitos: `infrastructure` (red `mesh`, Nginx con `snm-meshview.conf`, base `meshview` con su rol) y `mosquitto` en marcha.
2. Crear el usuario MQTT: `mqtt-users.sh service svc-meshview` (`../mosquitto/README.md`); guardar la contraseña.
3. `install -d -o 10001 -g 10001 -m 0700 /srv/meshview/datos/config /srv/meshview/datos/logs` (UID comprobado en la imagen fijada).
4. `/srv/meshview/.env` desde `.env.example` (0600) con las claves del rol y de MQTT.
5. `deploy.sh meshview`: genera `config.ini`, levanta `meshview` y espera a `healthy`. `meshview-config` queda parado con código 0 (`deploy.sh` solo espera a los contenedores en marcha).
6. Comprobar: `https://meshview.${PROJECT_DOMAIN}` muestra paquetes de varios gateways; panel en verde.

**Actualización:** leer el registro de cambios → cambiar etiqueta y digest (commit) → copia con el sistema del operador → verificar y reaplicar plantillas personalizadas según [`../customizations.md`](../customizations.md) → `deploy.sh meshview` → panel en verde. Si falla: revertir el commit, restaurar `meshview.dump` si hubo migración y desplegar.

**Copias de seguridad:** fuera del proyecto (sistema del operador). Qué copiar: la base `meshview` y `.env`; `datos/` se regenera. Restauración: `pg_restore -d meshview` y `deploy.sh meshview`.

## 9. Definición de hecho

- [x] Imagen fijada a 3.0.8 con digest; ningún `latest`.
- [x] Ningún secreto en `integrations/meshview/`; `config.ini` generado con permisos 0600.
- [x] `svc-meshview` conecta a `mosquitto:1884` y solo puede leer `msh/EU_868/#`.
- [x] Esquema creado en la base `meshview` del PostgreSQL nativo.
- [x] `https://meshview.${PROJECT_DOMAIN}` en español, abre en `/map` con Andalucía, Ceuta y Melilla.
- [x] Título y mensaje con `PROJECT_NAME` y enlace al portal; sin otras marcas en lo configurable.
- [x] Conversaciones de los 14 canales visibles e indexables.
- [x] Limpieza a 14 días activa en la configuración de MeshView (`prune_hours = 336`).
- [x] `/health` en verde en el panel; contenedor `healthy`; sin puertos publicados.
- [x] Prueba de 100 usuarios superada; `/api/config` sin secretos.
- [x] Plantillas de chat montadas con soporte para hora 24h y fechas `Europe/Madrid` (`customizations.md`).

## 10. Escenarios de prueba

- **Dado** un gateway que publica en `msh/EU_868/2/e/Cadiz/!<id>`, **cuando** pasa un mensaje de texto, **entonces** aparece en conversaciones y firehose en ≤ 10 s.
- **Dado** un map report en `msh/EU_868/2/map/`, **cuando** llega, **entonces** el nodo aparece en el mapa con su posición aproximada.
- **Dado** `.env` sin `DB_PASSWORD`, **cuando** se ejecuta `deploy.sh meshview`, **entonces** no despliega y nombra la variable.
- **Dado** Mosquitto reiniciado, **cuando** vuelve, **entonces** MeshView recibe paquetes de nuevo en < 1 min sin intervención.
- **Dado** PostgreSQL reiniciado, **cuando** vuelve, **entonces** MeshView sigue guardando (reconexión o reinicio automático del contenedor).
- **Dado** `svc-meshview`, **cuando** intenta publicar en `msh/EU_868/2/e/Cadiz/!ffff0001`, **entonces** el broker lo rechaza.
- **Dado** paquetes de hace 15 días, **cuando** corre la limpieza de las 02:00, **entonces** desaparecen.
- **Dado** una etiqueta nueva que rompe la web, **cuando** se revierte y se restaura el volcado, **entonces** vuelve la versión anterior con sus datos.

## 11. Riesgos y limitaciones

- **Marcas en la interfaz:** textos o enlaces internos de MeshView pueden nombrar a terceros. No se parchean (cada actualización obligaría a rehacerlos); MeshView es una excepción permitida. Si molesta, se propone upstream una opción de configuración.
- **Migraciones de esquema irreversibles:** bajar de versión exige restaurar el volcado previo (de ahí la copia antes de actualizar).
- **Reconexión MQTT:** depende de aiomqtt; si una versión no reconectara sola, el proceso seguiría vivo sin datos y `/health` podría no reflejarlo. Se prueba en cada actualización (escenario 4).
- **API pública sin límite propio:** la API JSON de MeshView es pública y de solo lectura (datos de canales públicos); sin límite de peticiones en esta entrega.
- **Recuentos mínimos:** solo ve lo que suben nuestros gateways con OK to MQTT.
- **AGPL-3.0:** si algún día se modificase el código, habría que publicar las fuentes modificadas.

## 12. Referencias

- Contratos comunes: [`../integration.md`](../integration.md) §4, §5, §8, §11, §12. Broker y usuarios: [`../mosquitto/README.md`](../mosquitto/README.md). Nginx: [`../infrastructure/03-nginx-dns.md`](../infrastructure/03-nginx-dns.md). Personalizaciones: [`../customizations.md`](../customizations.md).

## Decisiones de detalle

1. `config.ini` generado por un contenedor de un solo uso con la propia imagen y un script propio (`string.Template`), guardado en `/srv/meshview/datos/config/` (fuera del `rsync` de `deploy.sh`).
2. Claves de `svc-meshview` y del rol `meshview` solo alfanuméricas; el generador falla si no lo son.
3. Puerto interno 8081 (defecto de la imagen); el panel usa `http://meshview:8081/health`.
4. Encuadre ampliado al sur (35,2) para incluir Ceuta y Melilla.
5. `vacuum` y copias internas desactivados: autovacuum de PostgreSQL y copias del operador.
6. Todas las secciones activas salvo `net`; refresco a 10 s.
7. Plantillas `chat.html` y `net.html` montadas para forzar formato 24h y zona `Europe/Madrid` (DT-36, CUST-01).

---
> Creado: 2026-10-07 · Última revisión: 2026-10-08
