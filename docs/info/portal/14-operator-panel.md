# 06.14 · Panel de operadores (`/admin`)

> Zona privada del portal en Filament 5: estado de servicios, gateways, nodos, alertas y usuarios. Sustituye a cualquier panel externo y a una página de estado pública.

## Objetivo

Que los operadores vean de un vistazo si cada pieza funciona y qué pasa en la malla, sin escribir nunca en bases de otros servicios: el panel solo escribe en la base `portal`.

## Especificación

### Acceso

- Panel Filament único `admin` en `/admin` (`App\Providers\Filament\AdminPanelProvider`), con sesión y CSRF (grupo `web`; las rutas públicas van en otro grupo sin sesión).
- **Identidad y diseño de acceso:** Página de login personalizada (`App\Filament\Pages\Auth\Login`), logotipo integrado con tipografía corporativa (`filament.brand-logo`), paleta de acento verde esmeralda (`Color::Emerald` acorde a `DESIGN.md`), franja tricolor andaluza decorativa, advertencia de sesión protegida con 2FA y enlace de retorno al portal público (`filament.auth.login-after`).
- **Assets de interfaz:** Publicación garantizada en el build de producción mediante `RUN php artisan filament:assets` en el `Dockerfile` y versión persistente en `public/css/filament/`, `public/js/` y `public/fonts/`.
- Sin registro ni recuperación pública: los operadores se crean con `php artisan operador:crear {email} {nombre}` (pide la contraseña por consola, mínimo 8 caracteres) y se desactivan con `operador:desactivar {email}`. Cambio de contraseña disponible desde el perfil del operador (`->profile()`).
- Segundo factor TOTP obligatorio (autenticación multifactor de Filament 5): en el primer acceso el operador lo configura; sin él no entra. Códigos de recuperación de un solo uso.
- `throttle` de inicio de sesión: 5 intentos fallidos por IP y email → bloqueo 15 min. IP real por `trustProxies` (ver [11](11-public-api.md)).
- `User::canAccessPanel()` = `activo = true`. Sesión de 8 h; cookie `Secure`, `HttpOnly`, `SameSite=Lax`.
- Cabeceras: `X-Robots-Tag: noindex, nofollow` en todo `/admin`.

### Estado de servicios (página de inicio del panel)

- Comprobación cada minuto con `Schedule::job(new ComprobarServicios)->everyMinute()->withoutOverlapping()` en `portal-tareas`.
- Destinos en `config/servicios.php` (contrato `integration.md` §11):

| Clave | Comprobación | Tiempo de espera |
|---|---|---|
| `ingesta`, `detector-alertas`, `bot-telegram`, `bot-discord`, `webhooks`, `adaptador-potato`, `sync-peers`, `chat-ws` | `GET http://<contenedor>:8080/health` → `200` y `ok: true` | 3 s |
| `potatomesh` | `GET http://potatomesh:41447/version` → `200` | 3 s |
| `meshview` | `GET http://meshview:8081/health` → `200` | 3 s |
| `mosquitto` | Conexión MQTT a `mosquitto:1884` con `svc-panel` (`MQTT_PANEL_USER`/`MQTT_PANEL_PASSWORD`), CONNACK correcto y desconexión | 3 s |
| `postgresql` | `SELECT 1` por `pgsql`, `ingesta` y `alertas` | 3 s |
| `portal-tareas` | Latido propio: la tarea escribe `ultima_ejecucion`; si tiene > 3 min, el panel lo marca en rojo al cargar | — |

- Resultado en la tabla `estado_servicio` (base `portal`): última comprobación, `ok`, código, latencia, `motivo` del JSON de `/health` y el JSON completo (máx. 4 KB). Historial de cambios de estado en `estado_servicio_cambio` (90 días).
- Widget con una fila por servicio: verde/rojo, hace cuánto, latencia y motivo. Rojo si falla 2 comprobaciones seguidas (evita parpadeos). Detalle con el JSON de salud (`sync-peers` muestra el estado de cada peer y destaca los caídos > 1 h).
- Sin avisos externos: si cae el servidor entero, no hay aviso (limitación aceptada).

### Recursos de solo lectura

| Sección | Fuente | Contenido |
|---|---|---|
| Gateways | `api_gateways` (`ingesta`) | Id, nombres, provincia, último paquete, paquetes/hora, nodos que oye; filtro "sin publicar > 1 h" |
| Nodos | `api_nodes` | Búsqueda, rol, provincia, `is_router`, `is_gateway`, último visto; acción "Ver diagnóstico" → `/revisa-tu-nodo/{id}` |
| Routers | `api_routers` | Batería, carga, `tx`, último visto |
| Alertas | `api_alertas`, `api_alertas_transiciones` | Filtros por estado, riesgo, tipo, provincia, regla; detalle con historial |
| Catálogo | `api_catalogo` | Reglas activas, fase y descripción |

- Recursos Filament sobre modelos Eloquent de solo lectura (`$connection = 'ingesta'` o `'alertas'`, sin `create/edit/delete`; políticas que devuelven `false`). Las vistas no tienen clave primaria real: `getKeyName()` = `id` (o `node_id`).
- Paginación de tablas en servidor; sin exportaciones.

### Sugerencias (`SuggestionResource`)

Gestión interna de propuestas ciudadanas (base `portal`, tabla `suggestions`):
- Listado formateado con filtros por estado (`pending`, `approved`, `rejected`) y categoría (`bot_telegram`, `web`, `meshview`, `potatomesh`, `nueva_funcionalidad`, `otros`).
- Acciones directas por fila: Aprobar, Rechazar y Editar para redactar notas privadas de operador (`operator_notes`).
- Badge en el menú de navegación con el recuento de propuestas pendientes en color ámbar.
- Privacidad estricta: los usuarios únicamente envían su propuesta de forma anónima; las notas y el estado son visibles exclusivamente para los operadores en Filament.

### Usuarios

Recurso `Operadores` (base `portal`): listar, desactivar/activar, forzar nuevo TOTP. Crear solo por comando (evita que un panel comprometido cree cuentas).

## Contratos propios

### Tablas (base `portal`)

| Tabla | Columnas clave |
|---|---|
| `users` | `id`, `name`, `email` único, `password`, `activo` bool, campos de MFA de Filament, `ultimo_acceso` |
| `estado_servicio` | `servicio` PK, `ok` bool, `fallos_seguidos` int, `codigo` int null, `latencia_ms` int null, `motivo` text null, `detalle` jsonb null, `comprobado_en` timestamptz |
| `estado_servicio_cambio` | `id`, `servicio`, `ok`, `motivo`, `en`; purga diaria > 90 días |
| `tareas_latido` | `tarea` PK, `ultima_ejecucion` |
| `suggestions` | `id`, `category`, `content`, `status`, `operator_notes`, `ip_hash`, `created_at`, `updated_at` |

### Configuración

| Variable | Valor o ejemplo | Origen | Secreto |
|---|---|---|---|
| `MQTT_HOST` / `MQTT_PORT` | `mosquitto` / `1884` | Común | No |
| `MQTT_PANEL_USER` / `MQTT_PANEL_PASSWORD` | `svc-panel` / — | Propia | Contraseña sí |
| `ADMIN_PATH` | `admin` | Propia | No |
| `SESSION_LIFETIME` | `480` | Propia | No |

## Unidades de trabajo

- **UT-06.14.1 — Panel y acceso.** Provider, comandos `operador:*`, MFA obligatorio, `throttle`, `noindex`. *Aceptación:* sin TOTP no se accede a ninguna página; 5 fallos → 15 min de bloqueo.
- **UT-06.14.2 — Comprobación de servicios.** Job, `config/servicios.php`, cliente MQTT de conexión (librería PHP MQTT fijada al crear), tablas de estado. *Bordes:* `/health` que responde `200` con `ok: false` → rojo; JSON inválido → rojo con motivo. *Aceptación:* parar `detector-alertas` pone su fila en rojo en < 2 min y vuelve a verde al arrancarlo.
- **UT-06.14.3 — Widget de estado.** Refresco con `poll` de Livewire cada 30 s. *Aceptación:* muestra los 13 destinos con hace cuánto.
- **UT-06.14.4 — Recursos de solo lectura.** Gateways, nodos, routers, alertas, catálogo. *Aceptación:* ningún botón de crear/editar/borrar; una petición forzada de edición devuelve `403`.
- **UT-06.14.5 — Operadores.** *Aceptación:* desactivar un operador cierra sus sesiones.
- **UT-06.14.6 — Purga.** `estado_servicio_cambio` > 90 días. *Aceptación:* tarea diaria registrada en el scheduler.

## Escenarios de prueba

1. **Dado** un operador sin TOTP configurado, **cuando** inicia sesión, **entonces** solo puede configurar el TOTP antes de ver nada.
2. **Dado** `adaptador-potato` respondiendo `503` con `motivo: "token rechazado"`, **cuando** pasan 2 minutos, **entonces** su fila está en rojo con ese motivo.
3. **Dado** `portal-tareas` parado, **cuando** un operador abre el panel 5 min después, **entonces** `portal-tareas` aparece en rojo y el resto con la última comprobación de hace 5 min.
4. **Dado** `sync-peers` con un peer caído desde hace 2 h, **cuando** se abre su detalle, **entonces** el peer aparece destacado; el servicio sigue en verde.
5. **Dado** la base `alertas` caída, **cuando** se abre la sección Alertas, **entonces** aviso "fuente no disponible" sin error 500 y el resto del panel funciona.
6. **Dado** un intento de `POST` a la edición de un gateway, **cuando** se envía, **entonces** `403`.

## Ampliaciones posibles (fuera de la base)

Alta y baja de gateways en Mosquitto, gestión de destinos de webhooks y edición de textos del portal. Cada una exige definir cómo el portal entrega la configuración al otro servicio sin escribir en su base.

---
> Creado: 2026-10-07 · Última revisión: 2026-10-08
