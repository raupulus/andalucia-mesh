# 06 · Portal

> Web principal en `${PROJECT_DOMAIN}` (`mesh.example.org`): portada con servicios, mapa de Andalucía, guías, rankings, alertas, bots, firmware y legal; API pública JSON en `/api/v1` y panel privado de operadores en `/admin`. **Tipo:** propio (Laravel) · **Fase:** 5 · **Complejidad:** media · **Monorepo:** `services/portal/`

## 1. Contexto

- **Requisitos:** portada con recomendaciones de nodo, conexión al MQTT y autoría; tres tarjetas y mapa por provincias; rankings y nodos en peligro; página de bots; "Revisa tu nodo"; firmware; textos en `pages/`; API dentro del portal en Laravel; panel de operadores en Filament; sin página de estado; sin marcas de terceros salvo las excepciones de `../decisiones-tecnicas.md`.
- **Stack decidido:** Laravel + Filament, PostgreSQL nativo, dominio configurado.
- **Qué lee:** vistas `api_*` de `ingest` (`../ingesta/05-contract-views.md`) y de `alertas` (`../detector-alertas/03-socket-persistence.md`) con roles de solo lectura; nunca tablas. Salud de los servicios por HTTP/MQTT/SQL (`../integration.md` §11).
- **Quién lo consume:** el público (web), los bots (`PORTAL_API_URL`, `../bots-webhooks/README.md` §4.2), integradores (API) y los operadores (`/admin`).
- **Diseño:** [`DESIGN.md`](../DESIGN.md) es obligatorio. Nada visual fuera de él.

## 2. Alcance

| # | Archivo | Qué es | Complejidad |
|---|---|---|---|
| 06.1 | [`01-structure-home.md`](01-structure-home.md) | Rutas públicas, plantilla común, contenidos Markdown, portada, tarjetas, SEO, QR | Media |
| 06.2 | [`02-province-map.md`](02-province-map.md) | Mapa SVG de Andalucía: nodos por provincia y carga de la infraestructura | Media |
| 06.3 | [`03-node-setup-guide.md`](03-node-setup-guide.md) | Guía de radio, rol, saltos, intervalos y posición | Baja |
| 06.4 | [`04-gateway-connection.md`](04-gateway-connection.md) | Alta de gateways y ajustes MQTT | Baja |
| 06.5 | [`05-authorship.md`](05-authorship.md) | Quién está detrás, webs y redes | Baja |
| 06.6 | [`06-bots-page.md`](06-bots-page.md) | Qué hacen los bots y cómo añadirlos | Baja |
| 06.7 | [`07-firmware-page.md`](07-firmware-page.md) | Enlaces oficiales y Alpha/Beta | Baja |
| 06.8 | [`08-rankings-alerts.md`](08-rankings-alerts.md) | `/rankings`, `/alertas`, `/alertas/{id}` | Media |
| 06.9 | [`09-node-check.md`](09-node-check.md) | Diagnóstico de un nodo | Media |
| 06.10 | [`10-legal-privacy.md`](10-legal-privacy.md) | Aviso legal, privacidad, cookies, avisos, créditos | Media |
| 06.11 | [`11-public-api.md`](11-public-api.md) | Base de la API: rutas, formato, errores, caché con respaldo, límite, CORS, `/api` | Media |
| 06.12 | [`12-stats-api.md`](12-stats-api.md) | `stats/*` y `routers` | Media |
| 06.13 | [`13-nodes-alerts-api.md`](13-nodes-alerts-api.md) | `nodes*` y `alerts*` | Media |
| 06.14 | [`14-operator-panel.md`](14-operator-panel.md) | `/admin`: estado de servicios, recursos de solo lectura, operadores | Media |
| — | [`pages/`](pages/README.md) | Contenido y borrador del texto de cada página pública | — |
| — | [`DESIGN.md`](../DESIGN.md) | Sistema visual | — |

**Fuera de esta entrega:** analítica de terceros, alta de gateways desde el panel, edición de textos desde el panel, donaciones, notificaciones push, cualquier tiempo real que no sea sondeo.

### 2.1 Soporte Multidioma (RN-48)

El portal es la **única pieza** del proyecto con internacionalización y traducciones (las demás piezas operan exclusivamente en sus contratos técnicos en inglés y logs).
- **Idiomas admitidos:** Español (`es`, por defecto y fallback), Inglés (`en`) y Portugués (`pt`).
- **Detección automática:** Se evalúa la cabecera `Accept-Language` del navegador del visitante. Si coincide con una variante de `en`, `pt` o `es`, se establece automáticamente dicho idioma. Ante ausencia o cualquier otro idioma no soportado, se aplica el fallback en español.
- **Selector de idioma en Frontend (Navbar):** Ubicado en la cabecera junto al conmutador de tema (tanto en escritorio como en cabecera y menú móvil). Muestra exclusivamente el icono redondo con la bandera del idioma actual (sin texto del idioma en el activador). Al pulsar sobre el icono redondo se despliega el menú de selección con el orden estricto: **1. Español** (bandera de Andalucía), **2. Portugués** (bandera de Portugal) y **3. Inglés** (bandera de Reino Unido). Como la malla está enfocada a Andalucía, el idioma español está representado por la **bandera de Andalucía** (verde, blanca, verde) en lugar de la bandera de España. Persiste la preferencia en `localStorage` (`portal_locale`) sin emitir cookies en el front público (RN-06).
- **Selector de idioma en Backend (`/admin`):** En el panel de operadores (Filament) y en la pantalla de inicio de sesión se dispone del selector con icono redondo de la bandera activa que al pulsar despliega las opciones en el mismo orden estricto (Español, Portugués, Inglés), persistiendo la selección en la sesión técnica de administración.
- **Traducciones de contenidos:** Cadenas de interfaz localizadas mediante archivos de idioma de Laravel (`lang/es/portal.php`, `lang/en/portal.php`, `lang/pt/portal.php`, `lang/*/admin.php`). Para páginas institucionales, `ContenidoMarkdown` carga la versión localizada (`*.en.md`, `*.pt.md`) si existe, con fallback transparente a la versión española.

## 3. Stack y versiones

| Componente | Versión |
|---|---|
| PHP | 8.4 |
| Laravel | 13 (exacta en `composer.lock`) |
| Filament | 5 (exacta en `composer.lock`), solo en `/admin` |
| Imagen base | `serversideup/php:8.4-fpm-nginx` con etiqueta fijada (fijar al crear: la última probada de la serie 8.4) |
| Front público | Blade + CSS propio con los tokens de `DESIGN.md` como variables CSS; JS mínimo (sondeo, mapa, copiar, conmutador de tema) compilado con Vite al construir la imagen |
| Cliente MQTT del panel | Librería PHP MQTT fijada al crear (solo comprueba conexión) |
| Pruebas | Pest/PHPUnit; Larastan; Pint |
| Base | PostgreSQL 17 nativo (`172.30.0.1:5432`) |

## 4. Contratos

### 4.1 Rutas

| Ruta | Grupo | Módulo |
|---|---|---|
| `/`, `/proyecto`, `/quien-lo-impulsa`, `/como-se-gestiona` | Público | 01, 05 |
| `/configura-tu-nodo`, `/conecta-tu-gateway` | Público | 03, 04 |
| `/bots`, `/firmware` | Público | 06, 07 |
| `/rankings`, `/alertas`, `/alertas/{id}` | Público | 08 |
| `/revisa-tu-nodo`, `/revisa-tu-nodo/{id}` | Público | 09 |
| `/sugerencias` | Público | 01, 14 |
| `/legal/aviso-legal`, `/legal/privacidad`, `/legal/cookies` | Público | 10 |
| `/api` (documentación) | Público | 11 |
| `/sitemap.xml`, `/robots.txt`, `/robots.xml`, `/qr.svg`, `/qr.pdf` | Público | 01 |
| `/api/v1/*` | API (sin estado, `throttle:api-publica`) | 11–13 |
| `/ws/*` | No es del portal: Nginx lo envía a `chat-ws` (`location /ws/` en `snm-portal.conf`) (`../chat-ws/`) | — |
| `/admin/*` | Filament (sesión, CSRF, MFA) | 14 |

**Grupo público:** sin `StartSession`, `ShareErrorsFromSession`, `VerifyCsrfToken` ni cookies (`EncryptCookies` fuera). Respuestas con `Cache-Control: public, max-age=60` (páginas con datos) o `3600` (estáticas). Los datos vivos se piden al cargar con `fetch` a `/api/v1/*` (mismo origen) y se refrescan por sondeo; la primera pintura lleva los datos ya renderizados en servidor con las mismas clases `App\Datos\*` y la misma caché (`11-public-api.md`).

### 4.2 Bases de datos

| Conexión | Base | Rol | Uso |
|---|---|---|---|
| `pgsql` | `portal` | `portal` (propietario) | Operadores, sesiones de `/admin`, caché, estado de servicios |
| `ingesta` | `ingest` | `portal_lector_ingesta` | Solo vistas `api_*` |
| `alertas` | `alertas` | `portal_lector_alertas` | Solo vistas `api_*` |

`php artisan portal:vistas` comprueba las columnas que el portal espera (UT-06.11.8).

### 4.3 Configuración en código

- `config/proyecto.php`: nombre, dominio, contacto, zona horaria, radio (`LORA_*`), canales (`ALLOWED_CHANNELS`, `PRIMARY_CHANNEL`), `MQTT_TOPIC_ROOT`, host MQTT público `mqtt.${PROJECT_DOMAIN}`, mapa (centro, niveles de carga), provincias, recomendaciones de intervalos, URLs de firmware, usuario del bot de Telegram, invitación de Discord, filtros por defecto de los bots, sección `api`.
- `config/autoria.php`: nombre, nick, rol, presentación, webs y redes (`05-authorship.md`).
- `config/servicios.php`: destinos de salud del panel (`14-operator-panel.md`).
- Ningún valor de estos archivos se escribe a mano en vistas ni Markdown.

### 4.4 Salud

`GET /healthcheck` de la imagen (nginx + PHP-FPM vivos) para Docker. El panel comprueba el portal con el latido de `portal-tareas`.

## 5. Configuración

| Variable | Valor o ejemplo | Origen | Secreto |
|---|---|---|---|
| `PROJECT_NAME`, `PROJECT_DOMAIN`, `PROJECT_CONTACT`, `TZ` | — | Común | No |
| `LORA_*`, `ALLOWED_CHANNELS`, `PRIMARY_CHANNEL`, `MQTT_TOPIC_ROOT`, `INFRA_ROLES`, `MAP_CENTER`, `MAP_ZOOM`, `SATURACION_PESO_*` | — | Común | No |
| `DB_HOST` / `DB_PORT` | `172.30.0.1` / `5432` | Común | No |
| `APP_KEY` | — | Propia | Sí |
| `APP_ENV` / `APP_DEBUG` / `APP_URL` / `APP_TIMEZONE` | `production` / `false` / `https://${PROJECT_DOMAIN}` / `UTC` | Propia | No |
| `DB_DATABASE` / `DB_USERNAME` / `DB_PASSWORD` | `portal` / `portal` / — | Propia | Contraseña sí |
| `DB_INGESTA_DATABASE` / `DB_INGESTA_USERNAME` / `DB_INGESTA_PASSWORD` | `ingest` / `portal_lector_ingesta` / — | Propia | Contraseña sí |
| `DB_ALERTAS_DATABASE` / `DB_ALERTAS_USERNAME` / `DB_ALERTAS_PASSWORD` | `alertas` / `portal_lector_alertas` / — | Propia | Contraseña sí |
| `CACHE_STORE` / `SESSION_DRIVER` | `database` / `database` | Propia | No |
| `MQTT_HOST` / `MQTT_PORT` / `MQTT_PANEL_USER` / `MQTT_PANEL_PASSWORD` | `mosquitto` / `1884` / `svc-panel` / — | Común / propia | Contraseña sí |
| `API_RATE_LIMIT_PER_MINUTE` / `API_RATE_LIMIT_EXEMPT` | `60` / IP pública del servidor y `172.30.0.0/24` | Propia | No |
| `TELEGRAM_BOT_USERNAME` / `DISCORD_INVITE_URL` | usuario del bot / enlace de invitación | Propia | No |
| `HOSTING_PROVIDER` / `HOSTING_LOCATION` | Proveedor del servidor / ubicación del centro de datos (textos legales) | Propia | No |
| `BOT_RIESGOS_DEFECTO` / `BOT_TIPOS_DEFECTO` | `alto` / `infraestructura` | Propia | No |
| `TURNSTILE_SITE_KEY` / `TURNSTILE_SECRET_KEY` | Claves pública y secreta de Cloudflare Turnstile (opcionales) | Propia | Secreta sí |
| `AUTORUN_ENABLED` | `true` en `portal`, `false` en `portal-tareas` | Propia | No |

## 6. Datos

| Tabla (base `portal`) | Contenido | Retención |
|---|---|---|
| `users` | Operadores (`14`) | Mientras estén dados de alta |
| `sessions` | Solo `/admin` | `SESSION_LIFETIME` (8 h) |
| `cache`, `cache_locks` | Caché y copia de respaldo de la API, límite por IP (con hash) | Purga horaria de expirados |
| `estado_servicio`, `estado_servicio_cambio`, `tareas_latido` | Salud | Cambios 90 días |
| `suggestions` | Buzón de sugerencias ciudadanas, estado y notas privadas de operador | Mientras sean de utilidad |

Sin datos de visitantes: Nginx no guarda registros de acceso (`access_log off`, `../infrastructure/03-nginx-dns.md`). Copias: fuera del proyecto.

## 7. Unidades de trabajo

Transversales (las de cada módulo están en su archivo):

- **UT-06.1 — Proyecto base.** Laravel 13 + Filament 5, `bootstrap/app.php` con los tres grupos de rutas, conexiones, `config/*.php`, Pint, Larastan, Pest. *Aceptación:* `php artisan route:list` muestra los grupos; ninguna ruta pública añade middleware de sesión.
- **UT-06.2 — Imagen y pruebas.** `Dockerfile` sobre `serversideup/php:8.4-fpm-nginx` fijada, `composer install --no-dev`, assets de Vite, `php artisan optimize` en el arranque (`AUTORUN_ENABLED`). Pruebas locales antes de etiquetar: tests, análisis, `portal:vistas` contra fixtures, verificador de enlaces externos (autoría, firmware). *Aceptación:* imagen sin herramientas de desarrollo; pruebas en verde.
- **UT-06.3 — Plantilla común y DESIGN.** Cabecera, pie, tokens, modo claro/oscuro con conmutador, componentes de `DESIGN.md` §9 como componentes Blade. *Aceptación:* contraste AAA de los tokens verificado; ningún color fuera de los tokens (una prueba busca literales `#` en vistas).
- **UT-06.4 — Contenidos en Markdown.** `resources/contenido/*.md` desde `pages/`, renderizado en servidor con variables `{PROJECT_NAME}`… sustituidas desde configuración y caché por archivo. *Aceptación:* cambiar un texto = commit y despliegue; una variable desconocida hace fallar las pruebas.
- **UT-06.5 — SEO.** Título, descripción, Open Graph e imagen por página (borradores en `pages/`), `sitemap.xml` (sin fichas de alertas), `robots.txt` (bloquea `/admin` y `/api/v1`). *Aceptación:* el enlace compartido en Telegram muestra título, descripción e imagen.
- **UT-06.6 — Seguridad de cabeceras.** CSP sin orígenes externos (`default-src 'self'`; `img-src 'self' data:`), `Referrer-Policy: strict-origin-when-cross-origin`, `Permissions-Policy` restrictiva; HSTS lo pone Nginx. *Aceptación:* ninguna petición a terceros desde las páginas públicas.
- **UT-06.7 — Rendimiento y accesibilidad.** *Aceptación:* Lighthouse móvil ≥ 90 en rendimiento y accesibilidad; primera carga < 200 KB sin el GeoJSON; navegación completa por teclado.

## 8. Despliegue

| Contenedor | Imagen | Redes | Volúmenes | Puertos | Publicación | Healthcheck |
|---|---|---|---|---|---|---|
| `portal` | `snm-portal:<versión>` | `mesh` | — | `127.0.0.1:8100:8080` | Nginx (`snm-portal.conf`) | `GET http://127.0.0.1:8080/healthcheck` |
| `portal-tareas` | La misma | `mesh` | — | — | No se publica | Proceso `schedule:work` vivo |

Sitio de Nginx y cabeceras: `../infrastructure/03-nginx-dns.md`. `trustProxies(at: ['172.30.0.1'])`.

`portal-tareas`: `command: ["php", "artisan", "schedule:work"]`, `AUTORUN_ENABLED=false`. Ambos con `env_file: [/srv/comun/.env, .env]`, `restart: unless-stopped`, límite 512 MB cada uno.

Tareas programadas: `ComprobarServicios` (cada minuto), purga de caché (horaria), purga de `estado_servicio_cambio` (diaria), latido (cada minuto).

Pasos:

1. Requisitos: `infrastructure` (base `portal`, roles lectores, `pg_hba`, red `mesh`, Nginx con `snm-portal.conf`), `mosquitto` (`svc-panel`). `ingesta` y `detector-alertas` no son obligatorios: sin ellos la API responde `503` y las páginas muestran "datos no disponibles".
2. `/srv/portal/`: `compose.yaml`, `.env` desde `.env.example` (generar `APP_KEY`).
3. `deploy.sh portal`: `build` + `up -d`; el arranque migra (`AUTORUN_ENABLED`) y cachea configuración, rutas y vistas.
4. `php artisan operador:crear` para el primer operador; configurar TOTP.
5. Comprobar: portada, `/api/v1/stats/summary`, `/admin` con estado de servicios y `php artisan portal:vistas`.

Actualización: nueva etiqueta git → `deploy.sh portal`.

## 9. Definición de hecho

- [x] Todas las rutas de §4.1 responden; las públicas sin ninguna cookie.
- [x] Textos desde `resources/contenido/` con variables de configuración; nada escrito a mano.
- [x] Ninguna petición a terceros desde las páginas públicas.
- [x] Con `ingest` o `alertas` caídas, las páginas cargan y avisan del bloque afectado.
- [x] API según 11–13, con prueba de contrato de los bots.
- [x] Panel con MFA y estado de los 13 destinos.
- [x] `DESIGN.md` cumplido en claro y oscuro; Lighthouse ≥ 90.
- [x] Créditos legales (software y "© Instituto Geográfico Nacional") en el aviso legal.

## 10. Escenarios de prueba

- **Dado** un visitante nuevo desde el QR en móvil, **cuando** abre la portada, **entonces** ve tarjetas, mapa y bloques de nodo, MQTT y autoría sin recibir cookies.
- **Dado** `ingest` parada sin copia en caché, **cuando** se abre la portada, **entonces** el mapa sale gris con "Estadísticas no disponibles ahora mismo" y el resto se ve.
- **Dado** un cambio de `LORA_FREQUENCY_SLOT` en `/srv/comun/.env` y un redespliegue, **cuando** se abre `/configura-tu-nodo`, **entonces** muestra el valor nuevo.
- **Dado** un `POST` a `/proyecto`, **cuando** llega, **entonces** `405` sin error de CSRF ni cookie.

## 11. Riesgos y limitaciones

- Datos mínimos: solo lo que llega con OK to MQTT; se dice en el mapa, rankings y API.
- Sin monitorización externa: si cae el servidor, no hay aviso.
- Textos legales orientativos: conviene revisión profesional.
- El contrato de vistas es el punto frágil entre servicios: lo vigila `portal:vistas` en las pruebas y en el despliegue.

## 12. Referencias

- `../integration.md` §2, §4, §8, §9, §11, §12; `../overview.md`.

## Decisiones de detalle

1. Páginas públicas con datos renderizados en servidor + sondeo con `fetch` a la API del mismo origen; sin Livewire fuera del panel.
2. Caché y sesiones en la base `portal` (`database`), sin Redis.
3. `/healthcheck` de la imagen para Docker; la salud funcional del portal la da el latido de `portal-tareas`.
4. CSP estricta sin orígenes externos: el mapa es SVG propio y no hay fuentes de terceros.
5. `robots.txt` bloquea `/admin` y `/api/v1`; `/api` (documentación) sí se indexa.
6. Las decisiones de detalle de la API y el panel están en sus módulos (11–14).

---
> Creado: 2026-10-07 · Última revisión: 2026-10-08
