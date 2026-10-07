# 01.2 · PostgreSQL 17 y TimescaleDB

## Objetivo

Usar el PostgreSQL 17 nativo del servidor como motor único de los desarrollos propios y de MeshView: una base y un rol por servicio, acceso solo desde la red `mesh` con `scram-sha-256`, TimescaleDB en `ingest`, dos roles de solo lectura para el portal y una convención de migraciones que proteja las vistas contrato.

## Especificación

### Paquetes

- `postgresql-17` de Debian 13, clúster `17/main` (ya instalado). Menores por `unattended-upgrades`; el paquete reinicia el clúster y los servicios reintentan (`../integration.md` §14).
- TimescaleDB del repositorio de Timescale:
  - `/etc/apt/sources.list.d/timescaledb.list`: `deb [signed-by=/etc/apt/keyrings/timescaledb.gpg] https://packagecloud.io/timescale/timescaledb/debian/ trixie main` (clave de `https://packagecloud.io/timescale/timescaledb/gpgkey`).
  - Paquetes `timescaledb-2-postgresql-17` (versión fijada al crear) y `timescaledb-tools`; `apt-mark hold timescaledb-2-postgresql-17`.
  - Si el paquete exigiera el PostgreSQL de PGDG, se añade `apt.postgresql.org` (misma rama 17; el clúster no cambia).

### Configuración: `/etc/postgresql/17/main/conf.d/snm.conf`

Debian incluye `conf.d/` desde `postgresql.conf`; no se edita el archivo principal.

```ini
listen_addresses = 'localhost,172.30.0.1'
port = 5432
max_connections = 150
password_encryption = scram-sha-256
timezone = 'UTC'
log_timezone = 'UTC'

shared_preload_libraries = 'timescaledb'
timescaledb.telemetry_level = off
timescaledb.license = 'timescale'        # compresión y políticas de retención (valor del paquete)
timescaledb.max_background_workers = 16   # requisito de la ingesta (unas 40 tareas escalonadas)
max_worker_processes = 24                 # ≥ workers de TimescaleDB + paralelos + 3
max_parallel_workers = 4

shared_buffers = 1GB
effective_cache_size = 4GB
work_mem = 16MB
maintenance_work_mem = 256MB
max_wal_size = 2GB
checkpoint_timeout = 15min
random_page_cost = 1.1
jit = off

log_min_duration_statement = 1000
log_statement = 'none'                   # nunca 'ddl' ni 'all': las claves de ALTER ROLE irían al log
```

- `172.30.0.1` se puede enlazar aunque `br-mesh` no exista aún gracias a `net.ipv4.ip_nonlocal_bind = 1` (`01-server.md`). Sin ese ajuste, tras un reinicio PostgreSQL arrancaría solo en `localhost`.
- `max_connections = 150`: cada servicio limita su pool (orientativo: portal web + tareas ~70 entre sus tres conexiones, MeshView ~15, ingesta ~6, detector ~4, bots y webhooks ~6, sync-peers ~2, operación ~5).
- Cambios de `shared_preload_libraries` o memoria: `systemctl restart postgresql@17-main`; el resto, `reload`.

### Acceso: `/etc/postgresql/17/main/pg_hba.conf` (completo)

```text
# TYPE  DATABASE      USER                   ADDRESS          METHOD
local   all           postgres                                peer
local   all           all                                     peer
host    all           all                    127.0.0.1/32     scram-sha-256
host    all           all                    ::1/128          scram-sha-256
host    portal        portal                 172.30.0.0/24    scram-sha-256
host    meshview      meshview               172.30.0.0/24    scram-sha-256
host    ingest        ingest                 172.30.0.0/24    scram-sha-256
host    ingest        portal_lector_ingesta  172.30.0.0/24    scram-sha-256
host    alertas       alertas                172.30.0.0/24    scram-sha-256
host    alertas       portal_lector_alertas  172.30.0.0/24    scram-sha-256
host    peersync      peersync               172.30.0.0/24    scram-sha-256
host    bot_telegram  bot_telegram           172.30.0.0/24    scram-sha-256
host    bot_discord   bot_discord            172.30.0.0/24    scram-sha-256
host    webhooks      webhooks               172.30.0.0/24    scram-sha-256
# Nada más: cualquier otra combinación base/rol/origen se rechaza.
```

El cortafuegos solo deja entrar `172.30.0.0/24 → 172.30.0.1:5432` por `br-mesh` (`01-server.md`). El 5432 nunca se abre hacia internet.

### Bases y roles: `infrastructure/postgresql/databases.sql`

Idempotente. Se ejecuta con `sudo -u postgres psql -X -v ON_ERROR_STOP=1 -f databases.sql`.

```sql
SELECT format('CREATE ROLE %I LOGIN', r)
FROM unnest(ARRAY['portal','meshview','ingest','alertas','peersync','bot_telegram','bot_discord',
                  'webhooks','portal_lector_ingesta','portal_lector_alertas']) AS r
WHERE NOT EXISTS (SELECT 1 FROM pg_roles WHERE rolname = r) \gexec

SELECT format('CREATE DATABASE %I OWNER %I TEMPLATE template0 ENCODING ''UTF8'' LOCALE ''C.UTF-8''', d, d)
FROM unnest(ARRAY['portal','meshview','ingest','alertas','peersync','bot_telegram','bot_discord','webhooks']) AS d
WHERE NOT EXISTS (SELECT 1 FROM pg_database WHERE datname = d) \gexec

SELECT format('REVOKE ALL ON DATABASE %I FROM PUBLIC', d)
FROM unnest(ARRAY['portal','meshview','ingest','alertas','peersync','bot_telegram','bot_discord','webhooks']) AS d \gexec

GRANT CONNECT ON DATABASE ingest  TO portal_lector_ingesta;
GRANT CONNECT ON DATABASE alertas TO portal_lector_alertas;

ALTER ROLE portal_lector_ingesta CONNECTION LIMIT 40;
ALTER ROLE portal_lector_ingesta SET default_transaction_read_only = on;
ALTER ROLE portal_lector_ingesta SET statement_timeout = '10s';
ALTER ROLE portal_lector_alertas CONNECTION LIMIT 40;
ALTER ROLE portal_lector_alertas SET default_transaction_read_only = on;
ALTER ROLE portal_lector_alertas SET statement_timeout = '10s';

\connect ingest
CREATE EXTENSION IF NOT EXISTS timescaledb;
REVOKE ALL ON SCHEMA public FROM PUBLIC;
GRANT USAGE ON SCHEMA public TO portal_lector_ingesta;

\connect alertas
REVOKE ALL ON SCHEMA public FROM PUBLIC;
GRANT USAGE ON SCHEMA public TO portal_lector_alertas;
```

- Ningún rol es superusuario ni tiene `CREATEDB`/`CREATEROLE`. El propietario de cada base es también el usuario de conexión de su servicio (migraciones y ejecución).
- Desde PostgreSQL 15 el esquema `public` pertenece a `pg_database_owner`: el rol propietario crea objetos sin más permisos.
- `C.UTF-8`: orden por punto de código; quien necesite orden español usa `COLLATE` ICU en su consulta.
- `timescaledb` no es una extensión de confianza: solo la crea `postgres` aquí; las migraciones de la ingesta comprueban que existe.

### Claves: `infrastructure/postgresql/set-role-password.sh <rol>`

Lee la clave por la entrada estándar sin eco y la aplica sin que aparezca en ninguna línea de órdenes:

```bash
read -rs SNM_CLAVE; export SNM_CLAVE
runuser -u postgres -- psql -X -v ON_ERROR_STOP=1 -v rol="$1" <<'SQL'
\getenv clave SNM_CLAVE
ALTER ROLE :"rol" PASSWORD :'clave';
SQL
```

Generación: `openssl rand -base64 48 | tr -dc 'A-Za-z0-9' | head -c 32`. La misma clave va al `.env` del servicio (`DB_PASSWORD`) o, para los lectores, al `.env` del portal (nombres de variable en `../portal/README.md`).

### Lectores del portal

- Solo tienen `CONNECT`, `USAGE` del esquema y, cuando la migración del dueño lo concede, `SELECT` sobre cada vista `api_*` (`../integration.md` §8).
- Las vistas `api_*` se ejecutan con los permisos de su propietario (comportamiento por defecto). **Prohibido `security_invoker = true`** en ellas: el lector no tiene permisos sobre las tablas y la vista fallaría.
- `default_transaction_read_only = on` y `statement_timeout = '10s'` como segunda barrera.
- Comprobación (`infrastructure/postgresql/check-readers.sql`), ejecutada en `ingest` y en `alertas`:

```sql
SELECT grantee, table_name, privilege_type
FROM information_schema.role_table_grants
WHERE grantee LIKE 'portal_lector_%'
  AND (table_name NOT LIKE 'api\_%' OR privilege_type <> 'SELECT');
-- Debe devolver 0 filas.
```

### Convención de migraciones

| Regla | Detalle |
|---|---|
| Dueño | Cada servicio migra solo su base con su rol propietario: Laravel (`php artisan migrate --force`) en el portal, Alembic propio en MeshView, la herramienta que fije cada servicio Python |
| Momento | Antes de servir tráfico con la versión nueva (paso previo del arranque o `docker compose run --rm`); lo fija cada ficha |
| Esquema | `public` de su base; nada fuera de ella |
| Extensiones | Solo las crea infraestructura (`databases.sql`); la migración comprueba y falla con un mensaje claro si faltan |
| Vistas `api_*` | Solo las crean o cambian migraciones del dueño; toda migración que crea o recrea una termina con `GRANT SELECT ON <vista> TO portal_lector_ingesta` (o `portal_lector_alertas`); un `DROP VIEW` borra los permisos |
| Cambios de columnas | `CREATE OR REPLACE VIEW` solo añade columnas al final; quitar o renombrar exige `DROP` + `CREATE` + `GRANT` en la misma transacción y cambiar antes `../integration.md` |
| Copia previa | Antes de una migración destructiva: copia con el sistema del operador |

### Volcados y actualización de TimescaleDB

- Copias (fuera del proyecto): volcado por base (`pg_dump -Fc`) y de roles (`pg_dumpall --globals-only`). En `ingest`, `pg_dump` avisa de claves foráneas circulares en tablas internas de TimescaleDB: es normal. Restaurar exige la **misma versión** de TimescaleDB y `timescaledb_pre_restore()`/`timescaledb_post_restore()`.
- `infrastructure/postgresql/upgrade-timescaledb.sh <versión>`: copia previa → `apt-mark unhold` → `apt-get install timescaledb-2-postgresql-17=<versión>` → `systemctl restart postgresql@17-main` → `psql -X -d ingest -c 'ALTER EXTENSION timescaledb UPDATE;'` (`-X` obligatorio, sin `psqlrc`) → `apt-mark hold` → muestra `SELECT extversion FROM pg_extension WHERE extname = 'timescaledb'`.

## Contratos propios

- `DB_HOST=172.30.0.1`, `DB_PORT=5432`, `scram-sha-256`, origen `172.30.0.0/24`.
- 8 bases con propietario homónimo; `portal_lector_ingesta` y `portal_lector_alertas` con solo lectura de `api_*`.
- Las migraciones de los dueños conceden los `GRANT SELECT` sobre `api_*` y nunca usan `security_invoker`.

## Unidades de trabajo

| UT | Comportamiento | Detalle | Casos borde | Aceptación |
|---|---|---|---|---|
| **UT-01.2.1 — Configuración del clúster** | `conf.d/snm.conf` aplicado; escucha en `127.0.0.1` y `172.30.0.1` | `ss -tlnp \| grep 5432`; `SHOW shared_preload_libraries;` | Reinicio del host con Docker arrancando después de PostgreSQL | Tras `reboot`, un contenedor en `mesh` conecta sin reiniciar PostgreSQL |
| **UT-01.2.2 — TimescaleDB** | Repositorio de Timescale, paquete fijado y retenido, extensión en `ingest` | `SELECT extversion FROM pg_extension WHERE extname='timescaledb';`; `SHOW timescaledb.telemetry_level;` → `off` | Dependencias que pidan PGDG (ver arriba) | El rol `ingest` crea una hipertabla y una política de compresión de prueba y las borra |
| **UT-01.2.3 — `pg_hba.conf` y acceso desde `mesh`** | Cada rol solo entra en su base desde `172.30.0.0/24` | `SELECT * FROM pg_hba_file_rules;` sin errores; `reload` | IPv6 local (`::1`) para operación | `portal` contra `ingest` → rechazado; `ingest` contra `ingest` → conecta |
| **UT-01.2.4 — Bases, roles y claves** | `databases.sql` crea 8 bases y 10 roles y es repetible; `set-role-password.sh` fija claves sin dejarlas en historial ni en `ps` | `\l`, `\du`; ejecutar `databases.sql` dos veces seguidas | Rol existente con otra configuración: el script no lo recrea (se corrige a mano) | Cada servicio conecta con la clave de su `.env`; la clave no aparece en `/var/log/postgresql` |
| **UT-01.2.5 — Lectores del portal** | Solo leen `api_*` y no escriben | `check-readers.sql` en ambas bases, también en las pruebas de ingesta y detector contra una base de prueba tras migrar | Vista recreada sin `GRANT` → el portal recibe `permission denied` | 0 filas en la comprobación; `SELECT` a una tabla → `permission denied`; `CREATE TEMP TABLE` → error de solo lectura |
| **UT-01.2.6 — Convención de migraciones** | La tabla de convención de esta ficha y la siguen portal, ingesta, detector, sync-peers, bots y webhooks | Plantilla de migración de vista con `DROP`/`CREATE`/`GRANT` en una transacción | MeshView usa sus migraciones Alembic y no tiene vistas contrato | Una migración de prueba que recrea una vista mantiene el acceso del lector |

## Escenarios de prueba

- **Dado** un contenedor en `mesh` con las credenciales de `bot_telegram`, **cuando** conecta a `172.30.0.1:5432/bot_telegram`, **entonces** entra; contra `alertas`, **entonces** `pg_hba` lo rechaza.
- **Dado** `portal_lector_alertas`, **cuando** consulta `api_alertas`, **entonces** obtiene filas; **cuando** consulta la tabla interna de alertas, **entonces** `permission denied`.
- **Dado** una consulta del lector que tarda más de 10 s, **cuando** se ejecuta, **entonces** se cancela por `statement_timeout`.
- **Dado** `databases.sql` ya aplicado, **cuando** se vuelve a ejecutar, **entonces** termina sin errores y sin cambios.
- **Dado** una actualización de TimescaleDB, **cuando** se ejecuta `upgrade-timescaledb.sh`, **entonces** `extversion` coincide con el paquete y la ingesta sigue escribiendo.

---
> Creado: 2026-10-07 · Última revisión: 2026-10-07
