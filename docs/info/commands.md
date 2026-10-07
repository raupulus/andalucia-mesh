# Comandos y Scripts

Catálogo de comandos, scripts de compilación, ejecución, pruebas y tareas operativas del proyecto.

## Comandos y Scripts Operativos

| Script / Comando | Descripción | Entorno / Requisitos |
| :--- | :--- | :--- |
| `infrastructure/deploy.sh <pieza\|all>` | Despliega o actualiza componentes individuales o todo el sistema | Bash, Docker, permisos de ejecución |
| `infrastructure/check-compose.sh` | Auditoría estática de seguridad y binds en archivos Docker Compose | Bash, Docker Compose (opcional) |
| `infrastructure/host/install-host.sh` | Instalador maestro del host Debian 13 (sysctl, UFW, redes, vigilancia) | Bash, root / sudo |
| `infrastructure/host/install-sysctl.sh` | Aplica parámetros de kernel para red y memoria | Bash, root / sudo |
| `infrastructure/host/ufw-setup.sh` | Configura reglas estrictas de cortafuegos UFW | Bash, root / sudo, ufw |
| `infrastructure/host/redes.sh` | Crea la red Docker fija `mesh` (172.30.0.0/24) y volumen `alertas-socket` | Bash, Docker |
| `infrastructure/host/vigilancia.sh` | Comprobación de salud del host (disco, RAM, carga, SSL, servicios) | Bash |
| `infrastructure/host/fail2ban/install-fail2ban.sh` | Instala filtro y cárcel Fail2ban para rate-limiting en Nginx | Bash, root / sudo, fail2ban |
| `infrastructure/postgresql/install-timescale.sh` | Instala TimescaleDB 2.x en PostgreSQL 17 y configura pg_hba | Bash, root / sudo, postgresql-17 |
| `infrastructure/postgresql/set-role-password.sh <rol> [env]` | Genera contraseña criptográfica para un rol de PostgreSQL | Bash, PostgreSQL local |
| `infrastructure/postgresql/check-db.sh` | Verifica el estado del clúster PostgreSQL y bases de datos | Bash, psql, pg_isready |
| `infrastructure/postgresql/migrar-datos-historicos.py [opciones]` | Siembra y migra datos históricos desde SQLite hacia Ingest, MeshView y PotatoMesh | Python 3.13, asyncpg, meshtastic |
| `infrastructure/nginx/install.sh` | Instala sitios, snippets y streams en Nginx nativo | Bash, root / sudo, nginx |
| `infrastructure/nginx/certbot-setup.sh [opciones]` | Emite certificados Let's Encrypt mediante reto webroot | Bash, root / sudo, certbot |
| `integrations/mosquitto/install.sh` | Instala Mosquitto nativo, inicializa credenciales y configura listeners | Bash, root / sudo, mosquitto |
| `integrations/mosquitto/tools/generate-acl.sh [--init]` | Generador atómico de ACLs para gateways, microservicios y diagnóstico local | Bash |
| `integrations/mosquitto/tools/mqtt-users.sh <comando>` | Gestión de credenciales para gateways Meshtastic y microservicios internos | Bash, mosquitto_passwd |
| `integrations/mosquitto/tools/test-acl.sh` | Batería de pruebas automatizadas de ACL, bloqueo de downlink y límites | Bash, mosquitto-clients |
| `php artisan portal:mapa` | Proyecta el GeoJSON provincial en SVG interactivo calculando centroides y polos | PHP 8.4, Laravel, `services/portal/` |
| `php artisan portal:qr` | Genera los códigos QR vectoriales del portal (`public/qr.svg`, `public/qr.pdf`) | PHP 8.4, Laravel, `services/portal/` |
| `php artisan portal:vistas` | Verifica la existencia y tipos de las vistas contrato `api_*` en `snm_ingest` | PHP 8.4, Laravel, `services/portal/` |
| `php artisan operador:crear {email} {nombre}` | Da de alta un operador en el panel `/admin` solicitando contraseña de 12+ chars | PHP 8.4, Laravel, `services/portal/` |
| `php artisan operador:desactivar {email}` | Revoca el acceso y desactiva a un operador técnico en `/admin` | PHP 8.4, Laravel, `services/portal/` |

---
> Creado: 2026-10-07 · Última revisión: 2026-10-08
