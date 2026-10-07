# 01.4 · Operación

## Objetivo

Un único procedimiento de despliegue para todas las piezas, actualizaciones con vuelta atrás y rotación de secretos. Las copias de seguridad quedan fuera del proyecto (las gestiona cada operador; qué copiar en `README.md` §6).

## Especificación

### Despliegue: `infrastructure/deploy.sh <nombre|all>` y `check-compose.sh`

| `<nombre>` | Tipo / Ruta | Acción de despliegue |
|---|---|---|
| `mosquitto` | Nativo (`integrations/mosquitto`) | Valida sintaxis con `mosquitto -c ... -t` y recarga systemd |
| `postgresql` | Nativo (`infrastructure/postgresql`) | Comprueba estado del clúster con `pg_isready` |
| `nginx` | Nativo (`infrastructure/nginx`) | Valida sintaxis con `nginx -t` y recarga Nginx |
| `meshview`, `potatomesh` | `integrations/<nombre>` | Comprueba `.env`, descarga imagen y levanta contenedor |
| `portal`, `ingesta`, `detector-alertas`, `bot-telegram`, `bot-discord`, `webhooks`, `adaptador-potato`, `sync-peers`, `chat-ws` | `services/<nombre>` | Comprueba `.env`, construye imagen si hay Dockerfile (`build --pull`) y levanta contenedor |
| `all` | Global | Despliega todos los servicios nativos, integraciones y microservicios |

Pasos del script `deploy.sh`:

1. **Auditoría de seguridad Compose obligatoria:** Ejecuta `infrastructure/check-compose.sh` para verificar que ningún contenedor exponga puertos en `0.0.0.0` y que todos los puertos mapeados apunten explícitamente a `127.0.0.1`. Si la auditoría detecta una exposición insegura, el despliegue se detiene de inmediato.
2. Carga variables de entorno comunes desde `/var/www/storage/sur-nodos-en-mallas/common/.env` (o plantilla `infrastructure/common/.env.example`).
3. En servicios nativos: verifica configuración y recarga el servicio correspondiente.
4. En servicios Docker: carga el `.env` específico de la pieza, compila imagen propia o descarga externa (`pull`), y levanta con `docker compose up -d --remove-orphans`.
5. Comprueba salud: verifica que los contenedores arranquen correctamente y no entren en estado de reinicio cíclico.
6. Registra cada operación con marca de tiempo y usuario en `/var/www/storage/sur-nodos-en-mallas/logs/deploy.log`.

### Versiones de los servicios propios

- La versión es la del código en `/srv/repo` (rama o etiqueta git desplegada). La imagen se etiqueta `snm-<servicio>:<versión>` al construir (`VERSION` en el `.env`, por defecto el `git describe` del despliegue).
- Volver atrás: `git -C /srv/repo checkout <etiqueta anterior>` y `deploy.sh <nombre>`. Las migraciones de base de datos solo añaden; una que no se pueda deshacer lo indica en sus notas.

### Actualizaciones

| Qué | Cómo | Cuándo |
|---|---|---|
| Debian (seguridad), PostgreSQL 17 y Mosquitto menores | `unattended-upgrades`, reinicio automático a las 05:00 si hace falta | Automático |
| Docker Engine | `apt-mark unhold` → `apt-get install` → comprobar que Nginx sigue sirviendo los 4 hosts (los puertos de `127.0.0.1` vuelven a estar publicados) → `apt-mark hold` | Mensual, tras leer las notas |
| TimescaleDB | `upgrade-timescaledb.sh <versión>` (`02-postgresql.md`) | Cuando lo pida la ingesta |
| Imágenes de terceros (PotatoMesh, MeshView, base PHP del portal, base Python) | Leer el registro de cambios → cambiar la etiqueta en el repositorio (commit) → hacer copia con el sistema del operador → `deploy.sh <nombre>` → comprobar salud en el panel → si falla: revertir el commit y desplegar | Revisión mensual; nunca `latest` |
| Servicios propios | Nueva etiqueta git → `deploy.sh <nombre>` | Por versión |

### Rotación de secretos

Claves de base de datos de 32 caracteres (`02-postgresql.md`), de MQTT de 24 alfanuméricos (`../mosquitto/README.md`). Nunca se reutiliza un secreto que haya estado en git o en un archivo en claro.

| Secreto | Dónde vive | Cómo se rota |
|---|---|---|
| Clave de rol PostgreSQL de un servicio | `/srv/<servicio>/.env` | `set-role-password.sh <rol>` → `.env` → `deploy.sh <servicio>` |
| Lectores del portal | `/srv/portal/.env` | Ídem con `portal_lector_*` |
| Usuario MQTT de servicio | `credenciales/passwd-servicios` + `.env` del servicio | `mqtt-users.sh service <usuario>` → `.env` → desplegar |
| Usuario MQTT de gateway | `credenciales/passwd-gateways` | `mqtt-users.sh rotate '!<id>'` y entrega al operador |
| `POTATOMESH_API_TOKEN` | `.env` de `potatomesh`, `adaptador-potato` y `sync-peers` | Nuevo valor en los tres → desplegar los tres |
| `TELEGRAM_BOT_TOKEN`, `DISCORD_BOT_TOKEN` | `.env` de cada bot | Regenerar en el proveedor → `.env` → desplegar |
| Secretos de webhooks | `.env` de `webhooks` | `../bots-webhooks/03-webhooks.md` |
| Clave de aplicación del portal | `/srv/portal/.env` | `../portal/README.md` (invalida sesiones) |
| Token de avisos del host | `/etc/snm/vigilancia.env` | Regenerar el bot de operadores |

## Unidades de trabajo

- **UT-01.4.1 — `deploy.sh` y `snm-compose`.** *Casos borde:* `.env` incompleto → no despliega; `build` o `pull` falla → no se toca lo que corre. *Aceptación:* `deploy.sh mosquitto` no reinicia ningún otro contenedor; un fallo de salud devuelve código distinto de 0.
- **UT-01.4.2 — Procedimiento de actualización.** La tabla de actualizaciones de este documento, con la vuelta atrás de cada tipo. *Aceptación:* ensayo de subida y bajada de etiqueta de MeshView.
- **UT-01.4.3 — Rotación de secretos.** Ensayo con un rol de base de datos y un usuario MQTT de servicio. *Aceptación:* tras rotar, el servicio vuelve a `healthy` y la clave antigua ya no autentica.

## Escenarios de prueba

- **Dado** un `.env` sin `DB_PASSWORD`, **cuando** se ejecuta `deploy.sh ingesta`, **entonces** no despliega y nombra la variable que falta.
- **Dado** un error de compilación en un servicio propio, **cuando** se ejecuta `deploy.sh`, **entonces** la versión anterior sigue corriendo.
- **Dado** una etiqueta nueva de MeshView que rompe la web, **cuando** se revierte el commit y se despliega, **entonces** vuelve la versión anterior.

---
> Creado: 2026-10-07 · Última revisión: 2026-10-07
