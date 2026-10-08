# 06.14 · Panel de operadores (`/admin`)

> Zona privada del portal en Filament 5: estado de servicios, gateways, nodos, alertas y usuarios. Sustituye a cualquier panel externo y a una página de estado pública.

## Objetivo

Que los operadores vean de un vistazo si cada pieza funciona y qué pasa en la malla, sin escribir nunca en bases de otros servicios: el panel solo escribe en la base `portal`.

## Especificación

### Acceso
 
- Panel Filament único `admin` en `/admin` (`App\Providers\Filament\AdminPanelProvider`), con sesión y CSRF (grupo `web`; las rutas públicas van en otro grupo sin sesión).
- **Identidad y diseño corporativo:** Logotipo integrado con tipografía corporativa (`filament.brand-logo`), paleta semántica estricta acorde a `DESIGN.md` (primario verde institucional `#007A33`, éxito `#15612F`, peligro `Color::Rose`, aviso `Color::Amber`, info `Color::Blue`, grises `Color::Slate`), tipografía `Inter` con cifras tabulares activadas en tablas y métricas, modo oscuro con superficies Navy (`#1F2029`, `#2C2D3C`, `#363748`) y foco accesible WCAG AAA de 2 px (`#15612F` en claro / `#67EA94` en oscuro). Franja tricolor andaluza en cabecera de autenticación (`filament.auth.login-before`) y enlace accesible de retorno al portal público (`filament.auth.login-after`).
- **Assets de interfaz:** Publicación garantizada en el build de producción mediante `RUN php artisan filament:assets` en el `Dockerfile` y versión persistente en `public/css/filament/`, `public/js/` y `public/fonts/`.
- Sin registro ni recuperación pública: los operadores se crean con `php artisan operador:crear {email} {nombre}` (pide la contraseña por consola, mínimo 8 caracteres) y se desactivan con `operador:desactivar {email}`.
- **Editor de cuenta y perfil de operador (`App\Filament\Pages\Auth\EditProfile`):**
  - **Identidad:** Cambio de nombre visible, correo y subida de avatar / icono de perfil con editor interactivo de recorte en proporción cuadrada estricta 1:1 (`circleCropper()`, `imageCropAspectRatio('1:1')`, máx. 2 MB) almacenado en `disk('public')` bajo el directorio `avatars/`. Eliminación automática de avatares antiguos reemplazados.
  - **Seguridad:** Modificación de contraseña verificada requiriendo la clave actual mediante `currentPassword(guard: Filament::getAuthGuard())`, validación de complejidad mínima y confirmación idéntica obligatoria.
  - **Baja de cuenta:** Acción destructiva de eliminación de cuenta personal tanto en cabecera como al pie del formulario, con modal de confirmación reforzado que exige la contraseña actual antes de cerrar la sesión, invalidar tokens y eliminar al usuario y su avatar.
- `throttle` de inicio de sesión: 5 intentos fallidos por IP y email → bloqueo 15 min. IP real por `trustProxies` (ver [11](11-public-api.md)).
- **Internacionalización y selector de idioma (RN-48):** Soporte de interfaz en español (por defecto), portugués e inglés. Selector interactivo con solo el icono redondo de la bandera activa en la barra superior (topbar antes del menú de usuario) y en la pantalla de inicio de sesión, desplegando las opciones en orden estricto (Español, Portugués, Inglés) y persistiendo la selección en la sesión de administración (`filament_locale`) mediante `FilamentLocaleMiddleware`.
- `User::canAccessPanel()` = `activo = true`. Sesión de 8 h; cookie `Secure`, `HttpOnly`, `SameSite=Lax`.
- Cabeceras: `X-Robots-Tag: noindex, nofollow` en todo `/admin`.

### Página de inicio del panel (Dashboard operativo y widgets)

El panel recibe a los operadores con un centro de control operativo estructurado en 3 niveles de supervisión en tiempo real con refresco automático (`wire:poll.30s`):

1. **MallaStatsOverviewWidget (KPIs ejecutivos de la red y presión en el aire):**
   - **Presión del Aire (`channel_utilization`):** % de utilización promedio del canal LoRa en Andalucía, chip semántico de severidad (🟢 Holgado $\le 20\%$, 🟡 Cargado $20\%-40\%$, 🔴 Saturado $\ge 40\%$) y provincia con el pico máximo detectado.
   - **Saturación TX Repetidores (`air_util_tx`):** Tiempo medio de ocupación del aire en emisión de la infraestructura para verificar el cumplimiento del *duty cycle* legal ($< 10\%$).
   - **Infraestructura y Energía:** Total de routers activos, desglose de nodos alimentados permanentemente a red/solar (`⚡`) frente a nodos a batería, con alerta prioritaria si hay nodos en nivel crítico ($< 20\%$).
   - **Tráfico y Pasarelas:** Nodos activos 24h, total de paquetes por hora procesados y número de gateways publicando en el broker MQTT.

2. **RoutersInfraestructuraWidget (Monitor de repetidores y nodos clave):**
   - Tabla interactiva conectada a la vista de contrato `api_routers` de Ingesta (con degradación segura en caché y manejo de caídas vía `Fuente::recordar`).
   - Muestra para cada router: identificador (`!hex`), modelo de hardware (`hw_model`), provincia, estado de alimentación (`⚡ Red/Solar` con voltaje o barra de batería porcentual con código de color dinámico), nivel de presión local ChUtil, saturación TX, tiempo desde el último contacto y botón de inspección directa a `/revisa-tu-nodo/{id}`.

3. **EstadoServiciosWidget (Salud de la plataforma y microservicios):**
   - Supervisión periódica (cada minuto por `portal-tareas`, con botón de forzado manual `comprobarAhora`).
   - Destinos en `config/servicios.php` (contrato `integration.md` §11):

| Clave | Comprobación | Tiempo de espera |
|---|---|---|
| `ingesta`, `detector-alertas`, `bot-telegram`, `bot-discord`, `webhooks`, `adaptador-potato`, `sync-peers`, `chat-ws` | `GET http://<contenedor>:8080/health` → `200` y `ok: true` | 3 s |
| `potatomesh` | `GET http://potatomesh:41447/version` → `200` | 3 s |
| `meshview` | `GET http://meshview:8081/health` → `200` | 3 s |
| `mosquitto` | Conexión MQTT a `mosquitto:1884` con `svc-panel` (`MQTT_PANEL_USER`/`MQTT_PANEL_PASSWORD`), CONNACK correcto y desconexión | 3 s |
| `postgresql` | `SELECT 1` por `pgsql`, `ingesta` y `alertas` | 3 s |
| `portal-tareas` | Latido propio: la tarea escribe `ultima_ejecucion`; si tiene > 3 min, el panel lo marca en rojo al cargar | — |

- Resultado en la tabla `estado_servicio` (base `portal`): última comprobación, `ok`, código, latencia, `motivo` del JSON de `/health` y el JSON completo (máx. 4 KB).
- Historial reciente de transiciones en `estado_servicio_cambio` presentado en cuadrícula de tarjetas de evento con badges de recuperación/caída (`● RECUPERADO` / `▲ CAÍDA`).
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
| `users` | `id`, `name`, `email` único, `password`, `avatar_url` null, `activo` bool, campos de MFA de Filament, `ultimo_acceso` |
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
