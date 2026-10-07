# 01.3 · Nginx y DNS

## Objetivo

Publicar portal, PotatoMesh, MeshView y el chat por HTTPS y el broker por MQTT con TLS (8883) usando el **Nginx nativo del servidor** (el que ya atiende 80 y 443 para otras webs), con certificados de Let's Encrypt (certbot), cabeceras de seguridad comunes y los ajustes necesarios para SSE y WebSocket.

## Especificación

### DNS

Todo cuelga de `PROJECT_DOMAIN` (en los ejemplos, `mesh.example.org`).

| Registro | Tipo | Valor | TTL | Proxy de CDN |
|---|---|---|---|---|
| `mesh.example.org` | A / AAAA | IPv4 del servidor / IPv6 fija | 3600 | No |
| `*.mesh.example.org` | A / AAAA | Ídem | 3600 | No |
| `mesh.example.org` | CAA | `0 issue "letsencrypt.org"` (opcional; cubre los subdominios) | 3600 | — |

- El comodín cubre `potato.`, `meshview.` y `mqtt.` y cualquier host futuro.
- **Sin proxy de CDN:** solo reenvía HTTP (no 1883/8883) y corta conexiones largas (SSE, WebSocket).

### Cómo llega el tráfico

```text
internet ─443─▶ Nginx (host) ─▶ 127.0.0.1:8100  portal      (contenedor :8080)
                             ─▶ 127.0.0.1:8090  chat-ws     (contenedor :8000)  solo /ws/
                             ─▶ 127.0.0.1:41447 potatomesh  (contenedor :41447)
                             ─▶ 127.0.0.1:8081  meshview    (contenedor :8081)
internet ─8883 (TLS)─▶ Nginx stream ─▶ 127.0.0.1:1883  Mosquitto nativo (MQTT plano)
internet ─1883─────────────────────▶ Mosquitto nativo
```

- Los contenedores publicados exponen su puerto **solo en `127.0.0.1`** (`ports: ["127.0.0.1:<host>:<contenedor>"]`). Ningún contenedor publica en `0.0.0.0`.
- Puertos locales fijos (contrato, `../integration.md` §4):

| Pieza | Host | Puerto local | Puerto del contenedor |
|---|---|---|---|
| `portal` | `${PROJECT_DOMAIN}` | `127.0.0.1:8100` | `8080` |
| `chat-ws` | `${PROJECT_DOMAIN}/ws/` | `127.0.0.1:8090` | `8000` |
| `potatomesh` | `potato.${PROJECT_DOMAIN}` | `127.0.0.1:41447` | `41447` |
| `meshview` | `${MESHVIEW_DOMAIN}` *(por defecto `meshview.${PROJECT_DOMAIN}`)* | `127.0.0.1:8081` | `8081` |
| Mosquitto (TLS) | `mqtt.${PROJECT_DOMAIN}:8883` | `127.0.0.1:1883` | — (nativo) |

> [!NOTE]
> **Compatibilidad con Cloudflare (TR-01 y TR-05):** El certificado gratuito de Cloudflare solo cubre un nivel (`*.dominio.tld`). Si `meshview` se publica con proxy naranja y `${PROJECT_DOMAIN}` es un subdominio (ej. `mesh.dominio.tld`), `MESHVIEW_DOMAIN` debe configurarse en un solo nivel (ej. `meshview.dominio.tld`) para evitar errores SSL. Asimismo, debido a que Cloudflare puede conectar al origen por HTTP (puerto 80), los bloques de Nginx para el puerto 80 proxifican directamente a los servicios internos en lugar de forzar redirección 301 incondicional, evitando bucles infinitos de redirección (`ERR_TOO_MANY_REDIRECTS`).

### Archivos en el repositorio: `infrastructure/nginx/`

| Archivo | Destino en el servidor | Contenido |
|---|---|---|
| `snippets/snm-proxy.conf` | `/etc/nginx/snippets/` | Cabeceras de proxy comunes |
| `snippets/snm-security.conf` | `/etc/nginx/snippets/` | HSTS, `nosniff`, `Referrer-Policy`, `X-Frame-Options` |
| `snippets/snm-ratelimit.conf` | `/etc/nginx/snippets/` | Zona de rate-limiting (60 req/min) para `/api/` |
| `sites/snm-portal.conf` | `/etc/nginx/sites-available/` + enlace en `sites-enabled/` | Portal y `/ws/` |
| `sites/snm-potatomesh.conf` | Ídem | PotatoMesh (SSE) |
| `sites/snm-meshview.conf` | Ídem | MeshView |
| `streams/snm-mqtts.conf` | `/etc/nginx/streams-available/` + enlace en `streams-enabled/` | TLS del 8883 hacia Mosquitto 127.0.0.1:1883 |
| `install.sh` | — | Script instalador que reemplaza variables, verifica `nginx -t` y recarga |
| `certbot-setup.sh` | — | Emite certificados Let's Encrypt para los 4 hosts por reto webroot |

Los archivos usan `mesh.example.org` como marcador; `infrastructure/nginx/install.sh` los copia sustituyendo el dominio por `PROJECT_DOMAIN` de `common/.env`, ejecuta `nginx -t` y recarga solo si la prueba pasa. No toca ninguna otra web del servidor.

`snippets/snm-proxy.conf`:

```nginx
proxy_http_version 1.1;
proxy_set_header Host              $host;
proxy_set_header X-Real-IP         $remote_addr;
proxy_set_header X-Forwarded-For   $remote_addr;   # se sustituye, no se encadena: no se confía en lo que mande el cliente
proxy_set_header X-Forwarded-Proto $scheme;
proxy_set_header X-Forwarded-Host  $host;
proxy_set_header X-Forwarded-Port  $server_port;
```

`sites/snm-portal.conf` (resumen; los otros dos siguen el mismo patrón):

```nginx
server {
    listen 80; listen [::]:80;
    server_name mesh.example.org;
    location /.well-known/acme-challenge/ { root /var/www/letsencrypt; }
    location / { return 301 https://$host$request_uri; }
}
server {
    listen 443 ssl; listen [::]:443 ssl;
    http2 on;
    server_name mesh.example.org;
    ssl_certificate     /etc/letsencrypt/live/mesh.example.org/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/mesh.example.org/privkey.pem;
    include snippets/snm-security.conf;
    access_log off;                                # privacidad: sin registro de accesos
    client_max_body_size 2m;

    location /ws/ {                                # chat en directo
        proxy_pass http://127.0.0.1:8090;
        include snippets/snm-proxy.conf;
        proxy_set_header Upgrade    $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_read_timeout 3600s;
        proxy_send_timeout 3600s;
    }
    location / {
        proxy_pass http://127.0.0.1:8100;
        include snippets/snm-proxy.conf;
    }
}
```

- **PotatoMesh (SSE):** en `location /` añade `proxy_buffering off; proxy_cache off; gzip off; proxy_read_timeout 1h;` (equivale al `flushpackets=on` de Apache). Sin eso, los eventos llegan en bloques o se cortan.
- **MeshView:** proxy simple, sin ajustes especiales.

`streams/snm-mqtts.conf`:

```nginx
server {
    listen 8883 ssl; listen [::]:8883 ssl;
    ssl_certificate     /etc/letsencrypt/live/mqtt.mesh.example.org/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/mqtt.mesh.example.org/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    proxy_pass 127.0.0.1:1883;
    proxy_timeout 24h;                             # conexiones MQTT de días
}
```

- Requiere el módulo `stream` (`libnginx-mod-stream` en Debian) y, una sola vez, en `/etc/nginx/nginx.conf` a nivel raíz: `stream { include /etc/nginx/streams-enabled/*.conf; }`.
- Un único certificado por host en el `server` del stream: un gateway sin SNI recibe el de `mqtt.${PROJECT_DOMAIN}`.
- Mosquitto ve `127.0.0.1` como origen de estas conexiones; la identificación es por usuario MQTT.

### Certificados

- certbot nativo, reto HTTP-01 con `webroot` en `/var/www/letsencrypt` (no modifica los `server` del repositorio): `certbot certonly --webroot -w /var/www/letsencrypt -d <host>` para los cuatro hosts.
- Renovación por el `certbot.timer` del sistema con `--deploy-hook "systemctl reload nginx"` (recarga también el stream del 8883).
- Primera emisión con `--staging`; después, la real.

### IP real del cliente

- Los contenedores reciben la conexión desde la puerta de su red (`172.30.0.1`). La IP real va en `X-Forwarded-For` / `X-Real-IP`, que Nginx **sustituye** (nunca reenvía lo que mande el cliente).
- Las aplicaciones solo confían en esas cabeceras si la conexión viene de `172.30.0.1` (portal: `trustProxies(at: ['172.30.0.1'])`; chat-ws: `CHAT_PROXIES_CONFIABLES=172.30.0.1`).

### Protección y Anti-Scraping (Rate Limiting y Fail2ban)

Para evitar que otras instancias o terceros extraigan masivamente datos o abusen de los endpoints del servidor:

- **Rate Limiting en Nginx:** zona `limit_req_zone $binary_remote_addr zone=snm_api:10m rate=60r/m;` aplicada sobre `/api/` en el portal (`limit_req zone=snm_api burst=20 nodelay;`). Superar el umbral devuelve `429 Too Many Requests`.
- **Detección y bloqueo con Fail2ban:** cárcel `snm-api-abuse` que monitoriza los errores `429` en el log de Nginx. Si una misma IP genera más de 10 respuestas `429` en 1 minuto:
  1. Se bloquea temporalmente la IP en el cortafuegos UFW durante 1 hora.
  2. Se genera un aviso inmediato al operador (log de auditoría / panel / bot privado) para evaluar si es una instancia amiga mal configurada o un scraping abusivo.
- **Protección de PotatoMesh:** los endpoints de inyección/escritura (`/api/nodes`, `/api/messages`, etc.) exigen el token `POTATOMESH_API_TOKEN` interno; el acceso externo no autenticado solo permite lectura web normal.

## Contratos propios

- Puertos locales de la tabla anterior; hosts; 8883 terminado en Nginx hacia `127.0.0.1:1883`.
- Cabeceras `X-Forwarded-*` sustituidas por Nginx; confianza solo en `172.30.0.1`.

## Unidades de trabajo

| UT | Comportamiento | Aceptación |
|---|---|---|
| **UT-01.3.1 — DNS** | A/AAAA de `${PROJECT_DOMAIN}` y `*.${PROJECT_DOMAIN}` hacia el servidor, sin CDN | Los cuatro hosts resuelven por IPv4 e IPv6 |
| **UT-01.3.2 — Archivos e instalador** | `infrastructure/nginx/` + `install.sh` (sustitución de dominio, `nginx -t`, recarga) | Un error de sintaxis no se aplica y no afecta a las otras webs del servidor |
| **UT-01.3.3 — Certificados** | certbot webroot para los cuatro hosts, renovación con recarga | `certbot renew --dry-run` correcto |
| **UT-01.3.4 — HTTPS y cabeceras** | 80 → 301; cabeceras de seguridad; TLS ≥ 1.2 | `curl -sI` muestra HSTS, `nosniff`, `Referrer-Policy`, `X-Frame-Options` |
| **UT-01.3.5 — MQTT con TLS** | Stream 8883 → `127.0.0.1:1883` | `openssl s_client -connect mqtt.${PROJECT_DOMAIN}:8883` con y sin `-servername`; un gateway de prueba publica |
| **UT-01.3.6 — SSE y WebSocket** | PotatoMesh sin búfer; `/ws/` con upgrade | `curl -sN https://potato.${PROJECT_DOMAIN}/api/events` 5 min sin cortes; cliente WebSocket conectado 10 min |

## Escenarios de prueba

- **Dado** un `X-Forwarded-For` falso enviado por un cliente, **cuando** llega al portal, **entonces** la IP usada es la real (Nginx sustituye la cabecera).
- **Dado** un gateway con TLS, **cuando** conecta a `mqtt.${PROJECT_DOMAIN}:8883`, **entonces** Mosquitto lo autentica igual que por el 1883.
- **Dado** un contenedor parado, **cuando** se pide su host, **entonces** `502` y el resto de hosts sigue respondiendo.
- **Dado** un certificado a 30 días de caducar, **cuando** corre `certbot.timer`, **entonces** se renueva y Nginx recarga.
- **Dado** un puerto local (`8100`), **cuando** se intenta desde internet, **entonces** no responde.

---
> Creado: 2026-10-07 · Última revisión: 2026-10-07
