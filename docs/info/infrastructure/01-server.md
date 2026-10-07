# 01.1 · Servidor

## Objetivo

Dejar el servidor endurecido y reproducible: acceso solo por clave, parches automáticos, Docker del repositorio oficial, cortafuegos que no se deje engañar por Docker, las redes compartidas, la estructura `/srv/`, logs acotados y una vigilancia mínima que avise al superar los umbrales de `../overview.md`. Todo lo aplica un script idempotente (`infrastructure/host/install-host.sh`).

## Especificación

### Máquina

| Dato | Valor |
|---|---|
| Mínimo recomendado | 4 vCPU / 8 GB de RAM (con swap o zRAM) / 200 GB SSD |
| Red | IPv4 dedicada + IPv6 |
| IPv6 | Se fija una dirección del /64 (p. ej. `<prefijo>::1`) en la interfaz; es la que va al AAAA |
| SO | Debian 13 (trixie); zona horaria `Europe/Madrid` |

### Sistema base y acceso

- Usuario de operación con `sudo` (nombre libre). Docker siempre con `sudo`; nadie en el grupo `docker` (equivale a root).
- `/etc/ssh/sshd_config.d/10-snm.conf`:

```text
PermitRootLogin no
PasswordAuthentication no
KbdInteractiveAuthentication no
PubkeyAuthentication yes
AllowUsers <usuario-de-operación>
MaxAuthTries 3
X11Forwarding no
```

- `unattended-upgrades`: orígenes Debian `${distro_codename}-security` y `${distro_codename}-updates`; `Unattended-Upgrade::Automatic-Reboot "true"` y `Automatic-Reboot-Time "05:00"`. Los repositorios de Docker y Timescale **no** entran (actualización manual, `04-operations.md`).
- `fail2ban`, `/etc/fail2ban/jail.d/sshd.local`: `enabled = true`, `backend = systemd`, `maxretry = 5`, `findtime = 10m`, `bantime = 1h`, `banaction = ufw`.
- `/etc/sysctl.d/60-snm.conf`: `net.ipv4.ip_nonlocal_bind = 1` (PostgreSQL puede escuchar en `172.30.0.1` aunque la red `mesh` no exista al arrancar; ver `02-postgresql.md`).
- Paquetes: `ca-certificates curl gnupg ufw fail2ban unattended-upgrades needrestart sqlite3 jq rsync git`.

### Docker Engine

- Repositorio `https://download.docker.com/linux/debian`, suite `trixie`, clave en `/etc/apt/keyrings/docker.asc`. Paquetes `docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin`, retenidos con `apt-mark hold`.
- `/etc/docker/daemon.json`, **antes de crear ningún contenedor** (solo afecta a los contenedores nuevos):

```json
{
  "log-driver": "json-file",
  "log-opts": { "max-size": "10m", "max-file": "5" },
  "live-restore": true,
  "default-address-pools": [ { "base": "172.20.0.0/16", "size": 24 } ]
}
```

- `live-restore`: los contenedores siguen vivos si se reinicia el daemon. El pool evita que una red automática coja `172.30.0.0/24`.
- Todos los contenedores con `restart: unless-stopped`.

### Cortafuegos y trampa Docker/ufw

```bash
ufw default deny incoming
ufw default allow outgoing
ufw allow 22/tcp
ufw allow 80/tcp
ufw allow 443/tcp
ufw allow 1883/tcp
ufw allow 8883/tcp
ufw allow in on br-mesh from 172.30.0.0/24 to 172.30.0.1 port 5432 proto tcp
ufw allow in on br-mesh from 172.30.0.0/24 to 172.30.0.1 port 1884 proto tcp
ufw --force enable
```

- **La trampa:** un puerto publicado con `ports:` entra por IPv4 con DNAT antes de llegar a las reglas de ufw, así que queda abierto a internet aunque ufw lo niegue. Regla del proyecto: ningún contenedor publica en `0.0.0.0`; los que sirve Nginx publican solo en `127.0.0.1` (`127.0.0.1:<host>:<contenedor>`, tabla en `03-nginx-dns.md`). Nginx (80, 443, 8883), Mosquitto (1883) y SSH (22) escuchan en el host; un puerto de depuración se publica también en `127.0.0.1` y se quita al acabar. Lo vigila `check-compose.sh` (`README.md`, UT-01.2).
- **IPv6 e IPv4 nativos:** Nginx y Mosquitto escuchan directamente en el host, así que su tráfico sí pasa por ufw. Por eso hacen falta las reglas de 80/443/1883/8883.
- **Contenedores → host:** pasan por la cadena de entrada; las únicas permitidas son PostgreSQL (5432) y Mosquitto interno (1884) desde `br-mesh`. Sin esas reglas, las conexiones a `172.30.0.1` se descartan.

### Redes y volumen compartido

```bash
docker network create --subnet 172.30.0.0/24 --gateway 172.30.0.1 \
  -o com.docker.network.bridge.name=br-mesh mesh
docker volume create alertas-socket
```

| Red | Subred | Miembros |
|---|---|---|
| `mesh` | `172.30.0.0/24`, puerta `172.30.0.1` | todos los servicios propios, portal, potatomesh, meshview |

- `mesh` **no** es `--internal`: bots, webhooks y sync-peers necesitan salir a internet.
- En cada `compose.yaml`: `networks: { mesh: { external: true } }` y `volumes: { alertas-socket: { external: true } }` donde aplique.

### Estructura de directorios

```text
/srv/repo/            checkout del repositorio (solo lo actualiza deploy.sh)
/srv/comun/.env       configuración común (0644 root; sin secretos)
/srv/<nombre>/        compose.yaml y configuración copiados del repositorio; .env propio (0600 root); datos/ si hay
/etc/snm/             vigilancia.env (0600 root)
/usr/local/lib/snm/   scripts del host: monitor.sh, deploy.sh
/usr/local/bin/       snm-compose (envoltorio de docker compose)
/var/lib/snm/         estado de la vigilancia
```

`<nombre>`: `mosquitto`, `meshview`, `potatomesh`, `ingesta`, `portal`, `detector-alertas`, `bot-telegram`, `bot-discord`, `webhooks`, `adaptador-potato`, `sync-peers`, `chat-ws`.

### Logs

| Fuente | Rotación |
|---|---|
| Contenedores | `json-file` 10 MB × 5 por contenedor (`daemon.json`) |
| journald | `SystemMaxUse=1G` en `/etc/systemd/journald.conf.d/snm.conf` |
| PostgreSQL | `/var/log/postgresql/`, logrotate del paquete |
| Nginx | `error_log` en `warn` con logrotate del paquete; `access_log off` en los sitios del proyecto (privacidad; se activa a mano para depurar) |

### Vigilancia del host

`snm-vigilancia.timer` (cada 5 min) lanza `monitor.sh`, que carga `/srv/comun/.env` y `/etc/snm/vigilancia.env`, evalúa y avisa **solo al cambiar de estado** (entra en alarma o se recupera) con `logger -t snm-vigilancia` y por Telegram al chat de operadores (`AVISO_TELEGRAM_TOKEN`, `AVISO_TELEGRAM_CHAT_ID`; sin ellos, solo journald). Texto: `[${PROJECT_NAME}] <host>: <comprobación> <valor> (umbral <u>)`.

| Comprobación | Umbral (variable) | Fuente |
|---|---|---|
| Disco `/` | > 70 % (`VIG_DISCO_PCT`) | `df` |
| RAM usada | > 80 % en 3 lecturas seguidas (`VIG_RAM_PCT`) | `MemAvailable` de `/proc/meminfo` |
| Carga | media de 5 min > 2,5 (`VIG_CARGA5`) | `/proc/loadavg` |
| Mensajes MQTT | > 200/s, es decir > 12.000/min (`VIG_MQTT_MSG_S`) | `mosquitto_sub -h 127.0.0.1 -p 1885 -t '$SYS/broker/load/publish/received/5min' -C 1 -W 15` (listener local en el host, `../mosquitto/README.md`) |
| Contenedores y servicios | algún contenedor `unhealthy`, `restarting` o parado sin `docker stop`, o `systemctl is-active mosquitto` falla | `docker ps -a --format json` y `systemctl is-active` |
| PostgreSQL | `pg_isready -h 172.30.0.1 -p 5432` falla | — |
| Certificados | algún host caduca en < 14 días (`VIG_CERT_DIAS`) | `openssl s_client` a `${PROJECT_DOMAIN}`, `potato.`, `meshview.` y `mqtt.…:8883` |

Acciones ante cada umbral (`../overview.md`): disco → reducir retención de MeshView o PotatoMesh o ampliar disco; RAM → desactivar opcionales o subir de plan; carga → revisar refrescos web y consultas de MeshView; MQTT → buscar gateways con duplicados excesivos o spam.

## Contratos propios

- Red `mesh` (subred, puerta, puente `br-mesh`) y volumen `alertas-socket`, creados fuera de los compose.
- Puertos abiertos: 22, 80, 443, 1883, 8883. Ningún otro `ports:`.
- Rutas `/srv/<nombre>/`, `/srv/comun/.env`, `/etc/snm/`, `/var/backups/snm/`, `/var/lib/snm/`.

## Unidades de trabajo

### UT-01.1.1 — Sistema base y acceso
- **Comportamiento:** `install-host.sh` deja usuario de operación, SSH, `unattended-upgrades`, `fail2ban`, sysctl, zona horaria e IPv6 fija configurados; repetirlo no cambia nada.
- **Detalle:** archivos de `infrastructure/host/` copiados a `/etc/...`; `sshd -t` antes de recargar SSH.
- **Casos borde:** no recargar SSH si `sshd -t` falla; mantener una sesión abierta mientras se prueba otra.
- **Aceptación:** `ssh root@<servidor>` y acceso por contraseña rechazados; 6 intentos fallidos banean la IP 1 h; `unattended-upgrade --dry-run` lista solo orígenes Debian.

### UT-01.1.2 — Docker Engine
- **Comportamiento:** Docker del repositorio oficial, retenido, con `daemon.json`.
- **Detalle:** `docker info` muestra `json-file`, `Live Restore Enabled: true`; `apt-mark showhold` lista los cinco paquetes.
- **Casos borde:** si ya había contenedores, recrearlos tras cambiar `daemon.json`.
- **Aceptación:** `docker run --rm hello-world` funciona; `docker inspect` de un contenedor nuevo muestra `max-size=10m`.

### UT-01.1.3 — Cortafuegos
- **Comportamiento:** reglas de ufw de esta ficha activas en IPv4 e IPv6.
- **Detalle:** `ufw status verbose`; `ss -tlnp` para revisar qué escucha en `0.0.0.0` y `[::]`.
- **Casos borde:** activar ufw con la regla 22 ya creada (no perder la sesión).
- **Aceptación:** escaneo externo solo ve 22, 80, 443, 1883, 8883 (cuando Nginx y Mosquitto están arriba); los puertos de `127.0.0.1` no responden.

### UT-01.1.4 — Redes y volumen compartido
- **Comportamiento:** `infrastructure/host/redes.sh` crea `mesh` y `alertas-socket` si no existen.
- **Detalle:** `docker network inspect mesh` → `172.30.0.0/24`, gateway `172.30.0.1`; `ip a show br-mesh`.
- **Casos borde:** si `mesh` existe con otra subred, el script falla y no la toca.
- **Aceptación:** un contenedor de prueba en `mesh` hace `nc -z 172.30.0.1 5432`; otro en la red `bridge` por defecto, no.

### UT-01.1.5 — Estructura de directorios y configuración común
- **Comportamiento:** existen las rutas de esta ficha con sus permisos; `/srv/comun/.env` creado desde la plantilla.
- **Detalle:** `install -d -m 0700 /etc/snm`; `/srv/<nombre>/.env` 0600 root.
- **Casos borde:** `/srv/comun/.env` nunca contiene secretos (lo revisa `check-compose.sh` sobre la plantilla).
- **Aceptación:** `stat` de cada ruta coincide con lo especificado.

### UT-01.1.6 — Logs
- **Comportamiento:** ningún log crece sin límite.
- **Detalle:** `journalctl --disk-usage` ≤ 1 GB; tamaño de `/var/lib/docker/containers/*/*-json.log` ≤ 50 MB por contenedor.
- **Casos borde:** un contenedor que escribe mucho rota sin pararse.
- **Aceptación:** tras generar 100 MB de salida en un contenedor de prueba, ocupa ≤ 50 MB.

### UT-01.1.7 — Vigilancia del host
- **Comportamiento:** `monitor.sh` + `snm-vigilancia.timer` con las comprobaciones de la tabla y avisos al cambiar de estado.
- **Detalle:** estado previo por comprobación en `/var/lib/snm/vigilancia/<nombre>`; contador de lecturas para la RAM; `curl -fsS --max-time 10` a la API de Telegram.
- **Casos borde:** Mosquitto caído → avisa la comprobación de servicios del host (`systemctl is-active`); sin token de Telegram → solo journald; fallo de envío → se reintenta en la siguiente pasada.
- **Aceptación:** forzar disco > 70 % (archivo con `fallocate`) y parar un contenedor genera dos avisos, y sus recuperaciones al deshacerlo.

## Escenarios de prueba

- **Dado** un `compose.yaml` de prueba con `ports: ["9999:80"]`, **cuando** se levanta, **entonces** el puerto es alcanzable por IPv4 desde fuera pese a ufw (demostración de la trampa) y `check-compose.sh` lo habría rechazado.
- **Dado** el servidor reiniciado por `unattended-upgrades`, **cuando** vuelve, **entonces** todos los contenedores están `healthy` en menos de 5 minutos sin intervención.
- **Dado** el daemon de Docker reiniciado, **cuando** termina, **entonces** los contenedores no se han parado (`live-restore`).
- **Dado** un contenedor en `mesh`, **cuando** pide una URL de internet, **entonces** la obtiene.
- **Dado** la RAM al 85 % durante 15 minutos, **cuando** corre la vigilancia, **entonces** llega un único aviso y no se repite cada 5 minutos.

---
> Creado: 2026-10-07 · Última revisión: 2026-10-07
