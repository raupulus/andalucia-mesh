# Infraestructura

> Servidor endurecido, Docker, redes compartidas, Nginx nativo y PostgreSQL 17 nativo con TimescaleDB sobre los que se despliega el resto de piezas. **Tipo:** infraestructura · **Fase:** 1 · **Complejidad:** media · **Código:** `infrastructure/`

## 1. Contexto

- **Servidor:** un VPS con Debian 13, PostgreSQL 17 nativo, IPv4 e IPv6 públicas. Proveedor, ubicación y datos de acceso son de cada instancia y no se documentan aquí.
- **Carga de referencia (peor caso: 2.000 nodos, 50 gateways, 100 usuarios web):** ~3,9 GB de RAM, ~34 GB de disco, < 1 núcleo sostenido, 44 msg/s MQTT (`../overview.md`). Con 4 vCPU, 8 GB de RAM y 200 GB de disco sobra.
- **Consumen esta pieza:** todas las demás (redes, Nginx, PostgreSQL, `/srv/comun/.env`, despliegue). El panel (`../portal/14-operator-panel.md`) comprueba PostgreSQL con `SELECT 1`.

## 2. Alcance

**Incluye:** endurecimiento del host, Docker Engine oficial, cortafuegos, red `mesh`, volumen compartido `alertas-socket`, estructura `/srv/`, logs con rotación, vigilancia del host con avisos, PostgreSQL 17 + TimescaleDB con todas las bases y roles, DNS, Nginx nativo con certbot (HTTP, HTTPS y MQTT con TLS por `stream`), script de despliegue, actualización y rotación de secretos.

**Fuera del proyecto:** copias de seguridad (las gestiona el operador de cada instancia con su propio sistema; el proyecto solo indica qué hay que copiar, §6), integración continua, registros de imágenes, alta disponibilidad, aviso externo si cae el servidor entero, página de estado.

## 3. Stack y versiones

| Componente | Versión | Origen |
|---|---|---|
| Debian | 13 (trixie) | — |
| Docker Engine + Compose v2 | Fijar al crear (última probada); retenida con `apt-mark hold` | `download.docker.com/linux/debian` |
| Nginx (+ `libnginx-mod-stream`) y certbot | Los de Debian 13; Nginx ya presente en el servidor | Debian |
| PostgreSQL | 17 (clúster `17/main`) | Debian |
| TimescaleDB | `timescaledb-2-postgresql-17` 2.x: fijar al crear; `apt-mark hold` | Repositorio apt de Timescale |
| ufw, fail2ban, unattended-upgrades, jq, rsync, git | Las de Debian 13 | Debian |
| Let's Encrypt | ACME v2, reto HTTP-01 | — |

## 4. Contratos

### Hosts y puertos

| Host | Destino | Puertos |
|---|---|---|
| `${PROJECT_DOMAIN}` | Portal (web, `/api/v1`, `/admin`); `/ws/` → `chat-ws` | 443 (80 → 443) |
| `potato.${PROJECT_DOMAIN}` | PotatoMesh (`127.0.0.1:41447`) | 443 |
| `meshview.${PROJECT_DOMAIN}` | MeshView | 443 |
| `mqtt.${PROJECT_DOMAIN}` | Mosquitto (nativo del host) | 1883 (directo) y 8883 (TLS terminado en Nginx → `127.0.0.1:1883`) |

- Puertos abiertos en el servidor: **22, 80, 443, 1883, 8883**. 80, 443 y 8883 los atiende Nginx nativo; 1883 Mosquitto nativo; 22 SSH. Ningún contenedor publica puertos hacia fuera: los que se sirven por Nginx publican solo en `127.0.0.1` (tabla en `03-nginx-dns.md`). PostgreSQL (5432) y Mosquitto interno (1884) escuchan en `localhost` y en `172.30.0.1`, nunca hacia internet.
- DNS: `${PROJECT_DOMAIN}` y `*.${PROJECT_DOMAIN}` → IPv4 e IPv6 del servidor, **sin proxy de CDN** (`03-nginx-dns.md`).

### Redes Docker y volumen compartido

| Recurso | Definición | Miembros | Uso |
|---|---|---|---|
| Red `mesh` | **`172.30.0.0/24`, puerta `172.30.0.1`**; puente `br-mesh`; no `--internal` | todos los servicios propios, portal, potatomesh, meshview | Servicios del host (PostgreSQL `172.30.0.1:5432`, Mosquitto `172.30.0.1:1884`), API interna de PotatoMesh, `/health` (8080) |
| Volumen `alertas-socket` | Volumen con nombre, externo | detector-alertas (escritura), bot-telegram, bot-discord, webhooks | `/run/snm/alertas.sock` |

Los dos se crean una vez fuera de los compose; cada `compose.yaml` los declara `external: true`.

### PostgreSQL

| Elemento | Valor |
|---|---|
| Conexión desde contenedores | `DB_HOST=172.30.0.1`, `DB_PORT=5432`, `scram-sha-256`, solo desde `172.30.0.0/24` |
| Bases (propietario = rol homónimo) | `portal`, `meshview`, `ingest` (extensión `timescaledb`), `alertas`, `peersync`, `bot_telegram`, `bot_discord`, `webhooks` |
| Roles de solo lectura | `portal_lector_ingesta` (vistas `api_*` de `ingest`), `portal_lector_alertas` (vistas `api_*` de `alertas`) |
| `GRANT SELECT` sobre `api_*` | Lo hace la migración del servicio dueño; infraestructura solo crea el rol, `CONNECT` y `USAGE` del esquema |

Detalle completo en `02-postgresql.md`.

### Despliegue en el servidor

- Cada pieza vive en `/srv/<nombre>/` con su `compose.yaml` (copiado del repositorio) y su `.env` (0600, fuera de git). Configuración común en `/srv/comun/.env` (sin secretos).
- Las imágenes propias se construyen en el propio servidor (`docker compose build`) desde el `Dockerfile` de cada servicio; no hay registro de imágenes ni CI.
- Todo compose carga `env_file: [/srv/comun/.env, .env]` en ese orden. Publicación por Nginx en `03-nginx-dns.md`; despliegue y actualización en `04-operations.md`.

## 5. Configuración

### Común: `/srv/comun/.env` (plantilla `infrastructure/common/.env.example`)

Variables y valores de referencia en `../integration.md` §12. Ninguna es secreta y ninguna se escribe en el código.

### Propias de infraestructura

| Variable | Valor o ejemplo | Dónde | Secreto |
|---|---|---|---|
| `AVISO_TELEGRAM_TOKEN` / `AVISO_TELEGRAM_CHAT_ID` | Bot y chat de operadores para avisos del host | `/etc/snm/vigilancia.env` | Token sí |
| `VIG_DISCO_PCT`, `VIG_RAM_PCT`, `VIG_CARGA5`, `VIG_MQTT_MSG_S`, `VIG_CERT_DIAS` | `70`, `80`, `2.5`, `200`, `14` | `/etc/snm/vigilancia.env` | No |
| `DB_PASSWORD` de cada rol | Generada (`openssl rand`), 32 caracteres | `.env` de cada servicio; aplicada con `set-role-password.sh` | Sí |

## 6. Datos

| Qué | Dónde | Retención |
|---|---|---|
| Clúster PostgreSQL 17 (8 bases) | `/var/lib/postgresql/17/main` | La de cada servicio |
| SQLite de PotatoMesh | `/srv/potatomesh/datos/` | 30 días (`../potatomesh/README.md`) |
| Configuración y secretos | `/srv/*/.env`, `/srv/mosquitto/credenciales/`, `/etc/letsencrypt/`, `/etc/nginx/sites-available/snm-*`, `/etc/snm/` | — |
| Logs | Contenedores `json-file` 10 MB × 5; journald 1 GB | Rotación |

**Qué debe copiar el sistema de copias del operador** (fuera del proyecto): las 8 bases (`pg_dump` por base + `pg_dumpall --globals-only`), la SQLite de PotatoMesh en caliente (`sqlite3 .backup`) y la configuración de `/srv` y `/etc/snm`.

## 7. Unidades de trabajo

| Módulo | Archivo | Contenido | UT |
|---|---|---|---|
| 01.1 Servidor | [`01-server.md`](01-server.md) | Endurecimiento, Docker, cortafuegos y trampa Docker/ufw, redes, `/srv`, logs, vigilancia | UT-01.1.x |
| 01.2 PostgreSQL | [`02-postgresql.md`](02-postgresql.md) | PG17 + TimescaleDB, `pg_hba.conf`, bases y roles, lectores del portal, migraciones | UT-01.2.x |
| 01.3 Nginx y DNS | [`03-nginx-dns.md`](03-nginx-dns.md) | DNS, `server` por host, puertos locales, certificados, MQTT con TLS, SSE y WebSocket | UT-01.3.x |
| 01.4 Operación | [`04-operations.md`](04-operations.md) | Despliegue, actualización, rotación de secretos | UT-01.4.x |

- **UT-01.1 — Esqueleto de `infrastructure/`.** `host/`, `nginx/`, `postgresql/`, `common/.env.example`, `deploy.sh`, `check-compose.sh`. *Aceptación:* `git ls-files | grep -E '(^|/)\.env$'` no devuelve nada.
- **UT-01.2 — Comprobación de los compose.** `check-compose.sh` (se ejecuta a mano antes de desplegar) revisa con `docker compose config --format json` + `jq`: `ports:` solo con la forma `127.0.0.1:<puerto>:<puerto>` y solo en portal, chat-ws, potatomesh y meshview; ninguna imagen de terceros sin etiqueta o con `latest`; `restart: unless-stopped`, `healthcheck` y redes externas en todo servicio; ningún `network_mode: host`. *Aceptación:* un compose con `ports: ["5432:5432"]` hace fallar el script.
- **UT-01.3 — Verificación externa.** `nmap` IPv4 e IPv6 → solo 22, 80, 443, 1883, 8883; HTTP → 301; certificado válido en 8883; 5432 sin respuesta desde fuera.

## 8. Despliegue

| Contenedor | Imagen | Redes | Volúmenes | Puertos | Healthcheck |
|---|---|---|---|---|---|
| — | Sin contenedores propios de infraestructura: Nginx, certbot, PostgreSQL y Mosquitto son nativos | — | — | — | — |

Servicios del host: `nginx`, `certbot.timer`, `postgresql@17-main`, `mosquitto`, `snm-vigilancia.timer` (cada 5 min), `fail2ban`, `unattended-upgrades`.

Orden (el global de todas las piezas está en `../deployment.md`):

1. **Host** (`01-server.md`): `infrastructure/host/install-host.sh`, Docker Engine.
2. **Repositorio y configuración común:** clonar el repositorio en `/srv/repo`; copiar `infrastructure/common/.env.example` a `/srv/comun/.env` y ajustar valores.
3. **Redes y volumen:**

   ```bash
   docker network create --subnet 172.30.0.0/24 --gateway 172.30.0.1 \
     -o com.docker.network.bridge.name=br-mesh mesh
   docker volume create alertas-socket
   ```

4. **PostgreSQL** (`02-postgresql.md`): TimescaleDB, `conf.d/snm.conf`, `pg_hba.conf`, `databases.sql`.
5. **DNS y Nginx** (`03-nginx-dns.md`): registros, certificados con certbot (primero `--staging`), `infrastructure/nginx/install.sh`.
6. **Vigilancia** activa.

## 9. Definición de hecho

- [ ] SSH solo con clave, sin root; `fail2ban` activo; `unattended-upgrades` con reinicio automático a las 05:00.
- [ ] Docker oficial con `daemon.json` (rotación de logs, `live-restore`, pool `172.20.0.0/16`) y paquetes retenidos.
- [ ] Escaneo externo (IPv4 e IPv6) solo encuentra 22, 80, 443, 1883 y 8883.
- [ ] Red `mesh` y volumen `alertas-socket` creados.
- [ ] Un contenedor en `mesh` conecta a `172.30.0.1:5432`; desde internet, no.
- [ ] Las 8 bases y 10 roles existen; `ingest` tiene `timescaledb`; `pg_hba.conf` solo admite cada rol en su base.
- [ ] Los lectores del portal no pueden leer ninguna tabla ni escribir.
- [ ] Certificados válidos para los 4 hosts; HTTP → HTTPS con 301; 8883 acepta clientes con y sin SNI.
- [ ] El SSE de PotatoMesh y el WebSocket del chat se mantienen más de 5 minutos a través de Nginx.
- [ ] Los puertos locales (8100, 8090, 41447, 8081) no responden desde fuera.
- [ ] `snm-vigilancia` avisa al superar cada umbral.
- [ ] Ningún secreto en git.

## 10. Escenarios de prueba

- **Dado** un `compose.yaml` con `ports: ["8080:8080"]` (sin `127.0.0.1`), **cuando** se ejecuta `check-compose.sh`, **entonces** falla con el nombre del servicio.
- **Dado** el servidor recién reiniciado, **cuando** arrancan PostgreSQL y Docker, **entonces** PostgreSQL acepta conexiones en `172.30.0.1:5432` sin intervención.
- **Dado** `portal_lector_ingesta`, **cuando** hace `SELECT` sobre una tabla o `INSERT` en una vista, **entonces** `permission denied` o error de solo lectura.
- **Dado** el disco al 71 %, **cuando** corre `snm-vigilancia`, **entonces** llega un aviso y otro al recuperarse.

## 11. Riesgos y limitaciones

- **Servidor único:** si cae, no hay servicio ni aviso.
- **Docker se salta ufw** en los puertos publicados (IPv4): ningún contenedor publica fuera de `127.0.0.1` y `check-compose.sh` lo vigila.
- **Nginx compartido** con otras webs del servidor: los archivos del proyecto llevan prefijo `snm-` y `install.sh` solo recarga si `nginx -t` pasa.
- **TimescaleDB de repositorio externo:** podría exigir el PostgreSQL de PGDG (misma rama 17, mismo clúster).
- **IP de clientes:** los contenedores ven `172.30.0.1`; la IP real llega en `X-Forwarded-For`, que Nginx sustituye y las aplicaciones solo aceptan de `172.30.0.1`.

## Decisiones de detalle

1. Proxy inverso: el Nginx nativo ya existente en el servidor (no un proxy en contenedor); puertos locales fijos por servicio.
2. `net.ipv4.ip_nonlocal_bind = 1`: PostgreSQL escucha en `172.30.0.1` aunque `br-mesh` no exista al arrancar.
3. Regla ufw explícita `172.30.0.0/24 → 172.30.0.1:5432` en `br-mesh`, y reglas para 80/443/1883/8883 (por IPv6 el tráfico pasa por ufw).
4. Pool de direcciones de Docker `172.20.0.0/16` para no chocar con `mesh`.
5. Host en hora de Madrid, PostgreSQL en UTC.
6. Docker y TimescaleDB retenidos y actualizados a mano.
7. Despliegue: checkout en `/srv/repo` + `deploy.sh <nombre>` y envoltorio `snm-compose`; operación con `sudo`, nadie en el grupo `docker`.
8. Secretos del host en `/etc/snm/` (0600 root).
9. Nginx: un archivo por host con prefijo `snm-`, sin access log (privacidad), `proxy_buffering off` para SSE, upgrade para WebSocket, `stream` para el 8883.

---
> Creado: 2026-10-07 · Última revisión: 2026-10-07
