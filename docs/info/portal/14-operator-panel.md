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
  - **Identidad:** Cambio de nombre visible, correo y subida de avatar / icono de perfil con editor interactivo de recorte en proporción cuadrada estricta 1:1 (`circleCropper()`, `imageCropAspectRatio('1:1')`, máx. 2 MB) almacenado en `disk('public')` bajo el directorio `avatars/`. Disposición centrada con apilado vertical estricto (etiqueta superior centrada, zona circular de subida de 8rem en el eje central y texto de ayuda ubicado debajo sin invasión horizontal). Eliminación automática de avatares antiguos reemplazados.
  - **Seguridad:** Modificación de contraseña verificada requiriendo la clave actual mediante `currentPassword(guard: Filament::getAuthGuard())`, validación de complejidad mínima y confirmación idéntica obligatoria.
  - **Baja de cuenta:** Acción destructiva de eliminación de cuenta personal tanto en cabecera como al pie del formulario, con modal de confirmación reforzado que exige la contraseña actual antes de cerrar la sesión, invalidar tokens y eliminar al usuario y su avatar.
- `throttle` de inicio de sesión: 5 intentos fallidos por IP y email → bloqueo 15 min. IP real por `trustProxies` (ver [11](11-public-api.md)).
- **Internacionalización y selector de idioma (RN-48):** Soporte de interfaz en español (por defecto), portugués e inglés. Selector interactivo con solo el icono redondo de la bandera activa en la barra superior (topbar antes del menú de usuario) y en la pantalla de inicio de sesión, desplegando las opciones en orden estricto (Español, Portugués, Inglés) y persistiendo la selección en la sesión de administración (`filament_locale`) mediante `FilamentLocaleMiddleware`.
- `User::canAccessPanel()` = `activo = true`. Sesión de 8 h; cookie `Secure`, `HttpOnly`, `SameSite=Lax`.
- Cabeceras: `X-Robots-Tag: noindex, nofollow` en todo `/admin`.

### Página de inicio del panel (Dashboard operativo y widgets)

El panel recibe a los operadores con un centro de control operativo estructurado en 4 niveles de supervisión en tiempo real con refresco automático (`wire:poll.30s`):

1. **AmbitoSelectorWidget (Selector interactivo de ámbito territorial):**
   - Dos tarjetas interactivas de gran formato situadas en la parte superior del cuadro de mando (`$sort = -200`, ancho completo) con diseño estructurado y estilos aislados `.fi-ambito-*` optimizados para modo oscuro y claro:
     - **Andalucía (activo por defecto al entrar):** Centrado estrictamente en las 8 provincias andaluzas (`ES-AL` .. `ES-SE`) mediante bandera andaluza redonda. Asegura que los operadores supervisen de entrada únicamente los repetidores e infraestructura gestionada por la comunidad andaluza, evitando sobrecargas de contexto con nodos foráneos.
     - **España:** Ámbito nacional centrado en el resto de comunidades del territorio (`ES-*` excluyendo Andalucía y excluyendo `FUERA`) mediante bandera de España redonda.
   - **Comportamiento acumulativo y combinable:**
     - Al entrar al panel, Andalucía se encuentra activa por defecto.
     - Al pulsar España se suman ambos, permitiendo supervisar de forma acumulada toda la infraestructura española (`andalucia` + `espana` = `ambos`).
     - Los operadores pueden alternar libremente las tres combinaciones operativas:
       1. **Solo Andalucía** (por defecto).
       2. **Solo España** (resto de comunidades nacionales).
       3. **Andalucía y España** (ambas activas simultáneamente sumando coberturas).
     - **Salvaguarda de contexto:** Al menos un ámbito debe permanecer activo en todo momento (no se permite desactivar el último activo para evitar dejar el cuadro de mando en blanco).
   - **Reactividad Livewire y persistencia:** Cada tarjeta presenta el recuento en tiempo real de routers y una píldora conmutadora (`✓ Activo` en verde/rojo o `＋ Sumar` sutil). Al conmutar, se persisten las selecciones en sesión (`dashboard_ambito_andalucia`, `dashboard_ambito_espana` y `dashboard_ambito`) y se despacha el evento reactivo `ambito-cambiado`, actualizando al instante los KPIs y la tabla de repetidores sin recargar la página.

2. **MallaStatsOverviewWidget (KPIs ejecutivos de la red y presión en el aire):**
   - Reactivo a `ambito-cambiado` y sincronizado con el ámbito activo (`Andalucía`, `España` o `Andalucía + España`).
   - **Presión del Aire (`channel_utilization`):** % de utilización promedio del canal LoRa (restringido a las provincias del ámbito seleccionado), chip semántico de severidad (🟢 Holgado $\le 20\%$, 🟡 Cargado $20\%-40\%$, 🔴 Saturado $\ge 40\%$) y provincia con el pico máximo detectado.
   - **Saturación TX Repetidores (`air_util_tx`):** Tiempo medio de ocupación del aire en emisión de la infraestructura filtrada para verificar el cumplimiento del *duty cycle* legal ($< 10\%$).
   - **Infraestructura y Energía:** Total de routers del ámbito activo, desglose de nodos alimentados permanentemente a red/solar (`⚡`) frente a nodos a batería, con alerta prioritaria si hay nodos en nivel crítico ($< 20\%$).
   - **Tráfico y Pasarelas:** Nodos activos 24h, total de paquetes por hora procesados y número de gateways publicando en el broker MQTT.

3. **RoutersInfraestructuraWidget (Monitor de repetidores y nodos clave):**
   - Tabla interactiva conectada a la vista de contrato `api_routers` de Ingesta (con degradación segura en caché y manejo de caídas vía `Fuente::recordar`), filtrada según el ámbito activo mediante `Routers::obtenerResultado(..., ambito: $this->ambito)`.
   - Cabecera con badge dinámico indicando el número de routers y el contexto territorial activo (`· Andalucía` / `· España` / `· Andalucía + España`).
   - Muestra para cada router: identificador (`!hex`), modelo de hardware (`hw_model`), provincia, estado de alimentación (`⚡ Red/Solar` con voltaje o barra de batería porcentual con código de color dinámico), nivel de presión local ChUtil, saturación TX, tiempo desde el último contacto y botón de inspección directa a `/revisa-tu-nodo/{id}`.

4. **EstadoServiciosWidget (Salud de la plataforma y microservicios):**
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
- Listado formateado con columnas optimizadas: Fecha (`created_at`), Estado (`status` con badge y color por estado), Categoría (`category` con badge), Propuesta (`content` con tooltip completo) e indicador compacto de notas de operador (`¿Nota?` con badge Sí/No y tooltip del texto de la nota).
- Filtros interactivos por estado (`pending`, `approved`, `rejected`) y categoría (`bot_telegram`, `web`, `meshview`, `potatomesh`, `nueva_funcionalidad`, `otros`).
- Acciones directas por fila: Aprobar, Rechazar, Editar (para ver detalles y redactar notas de operador) y Eliminar.
- Formulario de creación y edición estructurado en dos bloques a ancho completo (`columnSpanFull`): bloque superior con «Detalle de la Sugerencia» (categoría, estado y texto de propuesta a ancho completo) y bloque inferior con «Gestión Interna (Operador)» (notas privadas de operador a ancho completo, hash de IP y fecha de recepción).
- Badge en el menú de navegación con el recuento de propuestas pendientes en color ámbar.
- Privacidad estricta: los usuarios únicamente envían su propuesta de forma anónima; las notas y el estado son visibles exclusivamente para los operadores en Filament.

### Preguntas Frecuentes (`FaqResource`)

Gestión editorial de preguntas y respuestas frecuentes (base `portal`, tabla `faqs`):
- Selector superior de idiomas por pestañas (`Tabs`: Español ES, English EN, Português PT) para traducir pregunta y respuesta.
- En pestaña principal: bloque superior con pregunta a la izquierda (9 columnas) y visibilidad pública (`is_active`) a la derecha (3 columnas). Bloque inferior a ancho completo con editor enriquecido de Markdown (`answer`) validado hasta 1024 caracteres (soporte de listas, negritas, cursiva, enlaces, títulos y bloques de código).
- Reordenación interactiva arrastrando filas en la tabla mediante `$table->reorderable('sort_order')` (sin campo manual en el formulario).
- Listado interactivo con visualización traducida (pregunta y respuesta limitadas a 2 líneas visibles con `lineClamp(2)` y tooltip completo), filtro por visibilidad y acciones de edición y eliminación.

### Destinos de Webhooks (`WebhookDestinationResource`)

Gestión dinámica de integradores de webhooks (base `portal`, tabla local replicada `webhook_destinations` sincronizada con el microservicio `webhooks` vía API interna HTTP en red Docker `mesh`):
- **Aislamiento estricto de base de datos:** El portal nunca escribe en la base de datos de `webhooks`. Toda mutación se efectúa mediante `App\Servicios\WebhooksClient` consumiendo `http://webhooks:8080/internal/destinos` protegido con la cabecera `X-Internal-Secret: ${INTERNAL_API_SECRET}`.
- **Tabla interactiva:**
  - Columnas: Nombre, Host, Filtros activos (chips/badges semánticos de riesgos, tipos, provincias y nodos), Estado (`Activo` en verde, `Suspendido` en rojo con motivo de baja), Fallos consecutivos, Entregas pendientes y Último OK.
  - Acción por fila «Probar (Ping)» (icono `bolt` / rayo): Invoca `POST /internal/destinos/{nombre}/probar` ejecutando un ping fuera de cola firmado con HMAC-SHA256 y notifica en interfaz el código HTTP y la latencia obtenida. Ante fallos de conexión o caídas del microservicio (como host no resuelto o timeout), captura la excepción de forma transparente emitiendo una notificación explicativa sin interrumpir la ejecución ni disparar errores 500.
  - Acción por fila «Reactivar» (icono `arrow-path`): Visible únicamente si el destino está inactivo (`activo = false`); invoca `POST /internal/destinos/{nombre}/reactivar` para reponer el destino (`activo = true`, `fallos_seguidos = 0`, `motivo_baja = NULL`) y despertar el despachador sin reiniciar contenedores.
  - Acciones de «Editar» y «Eliminar» (baja lógica a `retirado` y purga de entregas pendientes en el microservicio, con captura preventiva de errores de conexión).
- **Formulario (Crear / Editar) y Guía Visual Inferior:**
  - Estructurado en dos secciones operativas a ancho completo (`columnSpanFull`):
    - «Configuración del Destino»: Nombre (`^[a-z0-9-]{1,40}$`, obligatorio y de solo lectura en edición), URL HTTPS completa y Secreto HMAC-SHA256 (mínimo 32 caracteres) con máscara de contraseña revelable y acción de generación automática de clave segura aleatoria de 64 caracteres hexadecimales.
    - «Filtros de Entrega»: Selección múltiple con descripciones detalladas bajo cada opción y texto de ayuda para riesgos (`bajo`, `medio`, `alto`), tipos (`infraestructura`, `clientes`), provincias (`ES-AL`, `ES-CA`, etc.) y etiquetas de nodos Meshtastic (`^![0-9a-f]{8}$`).
  - **Guía visual de criterios y gobernanza (`guide-alertas.blade.php`) ubicada tras los botones de acción:**
    - Renderizada inmediatamente debajo de los botones del formulario (`content(Schema $schema)` en `CreateWebhookDestination` y `EditWebhookDestination`) para no entorpecer la introducción de datos en la parte superior.
    - Diseñada mediante tarjetas con colores semánticos, iconos y estilos autónomos compatibles con modo claro y oscuro según `DESIGN.md`.
    - Presenta barra superior de KPIs con umbrales críticos de RN-11, RN-32 y RN-39, tarjetas de niveles de riesgo (**Alto**: baterías router < 40% o al atardecer 20:00 h < 40%, bucles ≥ 5/10 min, silencio > 24 h, gateways caídos > 2 h, saturación ChUtil > 40%, ráfagas masivas de texto > 10/min, telemetría > 50/h, sondeos ^all ≥ 5/15m, traceroutes ≥ 20/30m, paquetes privados > 60/h o saltos ≥ 7; **Medio**: baterías router < 60% o cliente < 15%, reinicios ≥ 3/5 min, silencio > 6 h, gateway sin publicar > 15 min, saturación provincial > 30%, routers no coordinados en Andalucía `router-role`, ráfagas de texto 6–10/min o traceroutes 10–19/30m; **Bajo**: batería cliente < 35%, saturación > 20%, textos > 5/min, telemetrías ≥ 2/1m, sondeo aislado, GPS acelerado o 6 saltos).
    - Incluye cuadrícula de 4 grupos funcionales (Baterías, Ráfagas/NodeInfo, Reinicios y Gobernanza con enlace a [Routers Coordinados](14-operator-panel.md#routers-coordinados-coordinatedrouterresource)), diferenciación entre `infraestructura` y `clientes`, discriminación geográfica andaluza con descarte tajante de nodos exteriores `FUERA`, y aviso del modo comodín (campos vacíos reciben todas las alertas del criterio).

### Routers Coordinados (`CoordinatedRouterResource`)

Gestión y gobernanza comunitaria de la infraestructura de repetidores en Andalucía (base `portal`, tabla `coordinated_routers`):
- **Objetivo:** Registrar formalmente qué repetidores y routers (`ROUTER`, `ROUTER_LATE`, `REPEATER`) están coordinados y aprobados por la comunidad andaluza, clasificar los conocidos y detectar routers nuevos no controlados para evitar bucles o saturación de la malla.
- **Estados de gobernanza y coordinación:**
  - **Gestionado (`managed`, badge verde):** Routers aprobados, conocidos y coordinados oficialmente por los administradores de Andalucía Mesh. Representan la infraestructura formal de la red.
  - **Conocido (`known`, badge ámbar/aviso):** Routers cuya existencia es asumida y tolerada por la comunidad, pero no están bajo control directo de los administradores (por ejemplo, instalados por usuarios particulares sin consulta previa y que no desean retirar).
  - **Nuevo (`new`, badge rojo/peligro):** Routers recién detectados en la malla que aún no han sido evaluados. Son el foco prioritario de atención porque la activación de roles router de forma descontrolada o en cotas inadecuadas causa saturación de tráfico y bucles destructivos en la red LoRa.
- **Agrupación y filtros por provincia:**
  - La tabla está agrupada por defecto por provincia andaluza (`Group::make('province')->defaultGroup('province')`), presentando el nombre oficial de cada provincia (`Cádiz`, `Sevilla`, `Málaga`, etc.) como encabezado de sección.
  - Filtro por provincia y filtro por estado de gestión (`status`: Gestionado / Conocido / Nuevo).
- **Columnas operativas:** Estado con badge de severidad e icono semántico (`status`), identificador hex (`node_id`), nombre corto (`short_name`), nombre largo (`long_name`), provincia con badge, rol configurado, indicador de pasarela (`is_gateway`), modelo de hardware (`hw_model`), notas del operador (`notes`) y fecha del último contacto (`last_seen_at`).
- **Acciones rápidas de fila y lote:** Acciones directas para transicionar estados en un solo clic («Marcar como Gestionado», «Marcar como Conocido», «Marcar como Nuevo») tanto a nivel de fila individual como en lote mediante acciones masivas de tabla.
- **Acción «Sincronizar de la Malla»:**
  - Acción en cabecera que consulta la vista de contrato `api_routers` de la base de ingesta y vuelca/actualiza automáticamente los nodos detectados con rol de infraestructura.
  - **Detección de nuevos y preservación de estado:** Los routers detectados que no figuren en la tabla se dan de alta automáticamente con estado `new` (Nuevo). Para los routers ya registrados previamente, la sincronización actualiza su telemetría y metadatos (`short_name`, `long_name`, `role`, `hw_model`, `is_gateway`, `last_seen_at`), preservando rigurosamente su estado (`managed` / `known`) y las notas privadas del operador.
  - **Filtro geográfico estricto:** Solo importa y actualiza routers situados dentro de las 8 provincias de Andalucía (`ES-AL` .. `ES-SE`), descartando de forma tajante cualquier nodo fuera de la comunidad (`FUERA`), cuya coordinación y supervisión corresponde a las comunidades autónomas vecinas o Portugal.
  - **Resiliencia en entornos locales:** En entornos de desarrollo local y pruebas con SQLite, el sistema inicializa y asegura la estructura de `api_routers` poblándola con nodos de prueba si se encuentra vacía, evitando excepciones de base de datos ausente.
- **Edición manual:** Permite registrar nuevos routers de forma anticipada antes de su despliegue físico o añadir anotaciones de operador sobre ubicación, responsable o cota de instalación.

### Gestión y Control Remoto de Routers (`GestionRouters`)

Consola de operaciones y control administrativo de routers de la malla (`/admin/gestion-routers`, grupo `Infraestructura`):
- **Arquitectura de conexión cliente-nodo:**
  - El portal se ejecuta en el servidor/VPS. El operador físico conecta su nodo Meshtastic controlador directamente a su equipo local mediante Web Serial (USB @ 115200 baudios), Web Bluetooth (BLE) o red local (HTTP).
  - La interfaz web del operador en Filament se comunica directamente desde el navegador con su nodo local físico utilizando las APIs estándares del navegador (`navigator.serial`, `navigator.bluetooth`), sin que el VPS requiera pasarelas físicas ni puertos de radiofrecuencia acoplados.
  - El nodo local físico del operador actúa como puente de radiofrecuencia (transceiver) para inyectar paquetes administrativos `AdminMessage` de Meshtastic en la malla comunitaria LoRa hacia los routers remotos.
  - **Aviso de compatibilidad de navegador:** Banner superior de advertencia en ámbar/amarillo destacando el requisito de utilizar **Google Chrome**, **Microsoft Edge**, **Brave** o navegadores Chromium en escritorio para habilitar Web Serial y Web Bluetooth, indicando que Firefox y Safari solo pueden operar mediante WiFi Local (HTTP) por políticas de seguridad del fabricante.
  - **Guía operativa en 3 pasos:** Indicadores visuales numerados (Paso 1: Conectar nodo local; Paso 2: Elegir router objetivo; Paso 3: Transmitir acción por LoRa).
- **Selector de router objetivo dual:**
  - Disposición en dos columnas siempre visibles: **Opción A** (menú desplegable de routers coordinados de Andalucía con filtrado estricto a las 8 provincias vía `CoordinatedRouter::andalucia()`) y **Opción B** (campo manual para escribir o pegar cualquier Node ID en formato `!XXXXXXXX` o decimal uint32 para nodos nuevos o en despliegue).
  - Sincronización reactiva automática: seleccionar en el desplegable rellena el campo manual y el destinatario unicast; escribir en el campo manual actualiza de inmediato el ID LoRa y la ficha de estado sin campos ocultos.
- **Pestañas operativas de control:**
  - **1. Roles (`roles`):** Permite reconfigurar el rol de un router a través de la malla mediante `AdminMessage.setConfig` con `Config.DeviceConfig.role`. Posibilita la conmutación ágil a `CLIENT_MUTE` ante incidentes de saturación o emisores de spam cercanos para silenciar los reenvíos de un repetidor sin apagarlo, y restablecerlo posteriormente a `ROUTER` o `ROUTER_LATE`. Admite igualmente `CLIENT` y `REPEATER`. En firmware v2.5+, negocia automáticamente la clave de sesión administrativa (`ensureSessionKey`) antes del envío.
  - **2. Favoritos (`favoritos`):** Gestión de la lista de nodos prioritarios del router mediante `setFavoriteNode` y `removeFavoriteNode` de `AdminMessage`. Permite que el router priorice los mensajes y telemetrías de los nodos clave de su zona. Incorpora un buscador reactivo en tiempo real por nombre corto, nombre largo, provincia o ID hexadecimal contra el catálogo de la red, listado de favoritos actuales del router con badges semánticos, botón de quitar con 1 clic, entrada manual rápida y persistencia en base de datos (`coordinated_routers.favorite_nodes`) vía Livewire y `localStorage`.
  - **3. Bloqueados (`bloqueados`):** Gestión dedicada de nodos ignorados y bloqueados mediante `setIgnoredNode` y `removeIgnoredNode` de `AdminMessage`. Los paquetes y telemetría de estos nodos son descartados por el router remoto, impidiendo el consumo de tiempo de aire ante emisores de spam o interferencias. Incluye buscador reactivo sobre el catálogo, listado de bloqueados configurados con botón de desbloqueo en 1 clic, entrada manual y persistencia en base de datos (`coordinated_routers.blocked_nodes`).
  - **Prevención de duplicados y coherencia de listas:** La interfaz detecta si un nodo ya consta en favoritos o bloqueados, adaptando el botón de acción a «Quitar» o «Desbloquear» para evitar duplicados o errores. Además, al añadir un nodo a favoritos se retira automáticamente de bloqueados si estaba presente (y viceversa).
  - **4. Sondeo de Malla (`sondeo`):** Emisión de paquetes broadcast (`^all`, constante `0xFFFFFFFF`) con `want_response = true` para sondeo masivo: identificación (`NodeInfo`), coordenadas (`Position`) y estado energético/canal (`Telemetry`). La cola de transmisión resuelve de inmediato los paquetes broadcast sin bloquear en esperas espurias de ACK (inexistentes en broadcast).
  - **5. Petición a un Nodo (`unicast`):** Envío de solicitudes directas a un nodo concreto: `NodeInfo`, `Position`, `Telemetry` y trazado de saltos de ruta `Traceroute` (`TRACEROUTE_APP` con `RouteDiscoverySchema`). El trazador activa una cuenta atrás reactiva de 30 segundos en la interfaz y decodifica visualmente la lista de nodos por los que ha transitado la trama en la malla.
  - **6. Mantenimiento (`mantenimiento`):** Envío de comandos de reinicio remoto diferido (`reboot_seconds`) con confirmación de seguridad y retardo configurable en segundos para permitir la propagación y confirmación ACK del paquete antes del reinicio del hardware.
  - **Avisos de estado bajo cada acción:** Mensajes contextuales cuando el nodo local aún no está conectado o no se ha seleccionado ningún router, guiando al operador paso a paso.
- **Protocolo de Seguridad PKI y Sesión Administrativa Transparente (Meshtastic v2.5+):**
  - **Operación simplificada en 1 clic:** La interfaz elimina conmutadores manuales y advertencias criptográficas que confunden al operador. Toda la gestión criptográfica se resuelve bajo el capó de forma totalmente automatizada.
  - **Handshake de Clave de Sesión transparente (`ensureSessionKey`):** El firmware v2.5+ de Meshtastic exige un pase de sesión administrativa para comandos críticos. Al pulsar «Aplicar Rol», «Añadir a Favoritos» o «Reinicio», si el router dispone de clave pública verificada en la radio, el panel negocia en segundo plano el `SESSIONKEY_CONFIG` (enum 8) y adjunta el `session_passkey` sin requerir acciones manuales del usuario.
  - **Diagnóstico y claridad de cobertura LoRa:** Si un router objetivo aún no ha sido escuchado en la radio local o se encuentra fuera de cobertura física directa/malla, los rechazos de hardware (como el código 39 `PKI_SEND_FAIL_PUBLIC_KEY` o timeouts) se traducen en mensajes claros y en lenguaje natural: el sistema informa de inmediato que el router no responde por radio LoRa o está fuera de alcance, recomendando verificar su encendido y cobertura sin exponer códigos de error internos.
- **Consola de actividad en tiempo real:**
  - Terminal interactiva integrada con registro cronológico de eventos: eventos de conexión física, paquetes transmitidos (`TX ➡️`), paquetes recibidos (`RX ⬅️`), confirmaciones de entrega (`ACK ✅`), rechazos de enrutamiento con código traducido y fallos.
  - Descodificación en caliente de telemetría (batería, voltaje, ChUtil), posiciones GPS, respuestas `AdminMessage` y rutas de Traceroute.
  - Filtros por tipo de tráfico, limpieza de terminal y copiado directo al portapapeles.
- **Empaquetado, compatibilidad de navegador y eliminación de dependencias de CDN:**
  - Bundle compilado con Vite en formato IIFE autónomo (`resources/js/mesh-admin.js` -> `public/js/mesh-admin.bundle.js` mediante `npm run build:mesh-admin` / `build-mesh-admin.js`) e integración de `@meshtastic/core`, `@meshtastic/transport-web-serial`, `@meshtastic/transport-web-bluetooth` y `@bufbuild/protobuf` como dependencias locales versionadas.
  - Inyección de shims y polyfills para `window.process`, `window.global`, `Buffer` (incluyendo `Buffer.isBuffer`), `util` (`formatWithOptions`, `types`), `os` y `path` tanto mediante el plugin de módulos virtuales de Vite (`nodeShimsPlugin`) como en `define` y en el banner de salida, garantizando compatibilidad total en entornos de navegador puro sin Node.js runtime.
  - La carga mediante `/js/mesh-admin.bundle.js` es completamente síncrona, port-agnóstica (inmune a puertos locales como 9000 o 80 en `APP_URL`) y expone `window.meshAdmin` de forma inmediata antes de la evaluación del árbol reactivo de Alpine.js.
  - **Mecanismo de permisos Web Serial:** Por políticas de seguridad de Chromium/Web Serial, la ventana modal de selección de puerto USB/COM (`navigator.serial.requestPort()`) no puede saltar de forma automática al cargar la página; requiere estrictamente un gesto físico de usuario (clic en el botón «⚡ Conectar al Nodo Local»), idéntico al comportamiento del cliente oficial `client.meshtastic.org`.
  - Compilación autónoma de `configurador.js` (`npm run build:configurador`) eliminando las dependencias externas a `esm.sh` para garantizar funcionamiento autónomo e inmune a cortes de CDN externos.

### Usuarios y Perfil de Operador

Recurso `UserResource` (`/admin/users`, base `portal`):
- **Modelo de roles:** Dos niveles de privilegio: `superadmin` (superadministrador) y `admin` (administrador estándar).
- **Superadmin:**
  - Visualización completa en tabla: avatar, nombre, rol (badge destacado), correo electrónico, indicador de cuenta activa y fecha de último acceso.
  - Filtros por rol y estado activo.
  - Creación de nuevos usuarios (`CreateUser`) con nombre, correo, rol, contraseña segura y subida de avatar.
  - Edición de cuentas existentes (`EditUser`) con actualización de datos, cambio opcional de contraseña y reasignación de rol.
  - Eliminación de usuarios con salvaguarda que impide la autoeliminación de la cuenta propia para evitar bloqueos accidentales.
- **Admin (acceso restringido para operadores estándar):**
  - Acceso de solo lectura al listado (`ListUsers`) para constancia de operadores existentes.
  - **Privacidad estricta:** Solo visualiza avatar y nombre. Las columnas de correo electrónico (`email`), rol (`role`), estado (`activo`) y fecha de acceso quedan ocultas.
  - Sin botones de acción de fila (editar o borrar) y sin botón de cabecera para crear nuevos usuarios. Las filas no son clicables como enlace.
- **Autorización y seguridad en capas:**
  - Control mediante `App\Policies\UserPolicy` registrado en `AppServiceProvider` y métodos de autorización del recurso (`canCreate`, `canEdit`, `canDelete`, `canView`, `canViewAny`).
  - Cualquier intento de acceso directo por URL a `/admin/users/create` o `/admin/users/{id}/edit` por parte de un usuario con rol `admin` devuelve inmediatamente `403 Forbidden`.
- **Comando de consola:** `php artisan operador:crear {email} {nombre} {--password=} {--role=admin}` permite dar de alta o actualizar operadores desde el servidor, admitiendo la asignación explícita de `--role=superadmin` o `--role=admin`.

La página de edición de perfil personal (`/admin/profile`, `EditProfile`) utiliza un modal amplio para escritorio (`Width::FourExtraLarge`, 56rem / 896px) en lugar del ancho compacto de login, con avatar circular centrado horizontalmente y distribución en dos columnas (`sm: 2`) para optimizar la ergonomía en pantallas grandes.

### Páginas y Artículos (`CustomPageResource`)

Gestión y publicación de páginas y artículos divulgativos (base `portal`, tabla `custom_pages`):
- **Formulario estructurado en dos secciones:**
  - «Información General»: Título (genera slug automático en creación), Slug único y obligatorio (`alphaDash`, validación de unicidad), Descripción (resumen/lead hasta 500 caracteres) y Contenido completo mediante `MarkdownEditor` (soporte de estilos de texto, listas, encabezados, citas y bloques de código).
  - «Multimedia y Metadatos»: Imagen de portada (`FileUpload` en disco `public` dentro de `paginas/` con editor de recorte), Etiquetas temáticas (`TagsInput` para keywords en formato array JSON) y conmutador de visibilidad (`is_active`).
- **Tabla interactiva:**
  - Miniatura de portada, Título con subtítulo de ruta (`/paginas/{slug}`), Badges verdes de keywords, Conmutador directo de estado activo (`ToggleColumn`), Fecha de creación y Acciones de fila (Ver en sitio público abriendo en pestaña nueva, Editar y Eliminar).

## Contratos propios

### Tablas (base `portal`)

| Tabla | Columnas clave |
|---|---|
| `users` | `id`, `name`, `email` único, `role` (`superadmin` \| `admin`), `password`, `avatar_url` null, `activo` bool, campos de MFA de Filament, `ultimo_acceso` |
| `estado_servicio` | `servicio` PK, `ok` bool, `fallos_seguidos` int, `codigo` int null, `latencia_ms` int null, `motivo` text null, `detalle` jsonb null, `comprobado_en` timestamptz |
| `estado_servicio_cambio` | `id`, `servicio`, `ok`, `motivo`, `en`; purga diaria > 90 días |
| `tareas_latido` | `tarea` PK, `ultima_ejecucion` |
| `suggestions` | `id`, `category`, `content`, `status`, `operator_notes`, `ip_hash`, `created_at`, `updated_at` |
| `faqs` | `id`, `question`, `answer`, `is_active`, `sort_order`, `created_at`, `updated_at` |
| `webhook_destinations` | `id`, `name` único, `url`, `host`, `active` bool, `disabled_reason` null, `consecutive_failures`, `pending_deliveries`, `last_ok_at`, `risks` json, `types` json, `provinces` json, `nodes` json, `created_at`, `updated_at` |
| `coordinated_routers` | `id`, `node_id` único, `short_name`, `long_name`, `province`, `role`, `status` varchar, `favorite_nodes` json, `blocked_nodes` json, `approved` bool, `is_gateway` bool, `hw_model`, `notes`, `last_seen_at`, `created_at`, `updated_at` |
| `custom_pages` | `id`, `title`, `slug` único, `description`, `content` markdown, `keywords` json, `featured_image`, `is_active` bool, `created_at`, `updated_at` |

### Configuración

| Variable | Valor o ejemplo | Origen | Secreto |
|---|---|---|---|
| `MQTT_HOST` / `MQTT_PORT` | `mosquitto` / `1884` | Común | No |
| `MQTT_PANEL_USER` / `MQTT_PANEL_PASSWORD` | `svc-panel` / — | Propia | Contraseña sí |
| `ADMIN_PATH` | `admin` | Propia | No |
| `SESSION_LIFETIME` | `480` | Propia | No |
| `WEBHOOKS_INTERNAL_URL` | `http://webhooks:8080` | Común / Propia | No |
| `INTERNAL_API_SECRET` | Token compartido para endpoints internos en red `mesh` | Común / Propia | **Sí** |

## Unidades de trabajo

- **UT-06.14.1 — Panel y acceso.** Provider, comandos `operador:*`, MFA obligatorio, `throttle`, `noindex`. *Aceptación:* sin TOTP no se accede a ninguna página; 5 fallos → 15 min de bloqueo.
- **UT-06.14.2 — Comprobación de servicios.** Job, `config/servicios.php`, cliente MQTT de conexión (librería PHP MQTT fijada al crear), tablas de estado. *Bordes:* `/health` que responde `200` con `ok: false` → rojo; JSON inválido → rojo con motivo. *Aceptación:* parar `detector-alertas` pone su fila en rojo en < 2 min y vuelve a verde al arrancarlo.
- **UT-06.14.3 — Widget de estado.** Refresco con `poll` de Livewire cada 30 s. *Aceptación:* muestra los 13 destinos con hace cuánto.
- **UT-06.14.4 — Recursos de solo lectura.** Gateways, nodos, routers, alertas, catálogo. *Aceptación:* ningún botón de crear/editar/borrar; una petición forzada de edición devuelve `403`.
- **UT-06.14.5 — Operadores.** *Aceptación:* desactivar un operador cierra sus sesiones.
- **UT-06.14.6 — Tareas programadas y purga.** `portal-tareas` ejecuta `php artisan schedule:work` de forma continua: `ComprobarServicios` cada minuto (con auditoría de salud y purga de `estado_servicio_cambio` > 90 días) y `portal:sitemap` cada noche a las 04:00 (regeneración de `sitemap.xml`).
- **UT-06.14.7 — Gestión de webhooks.** Integración con `WebhooksClient`, réplica local `webhook_destinations`, acciones de ping HMAC y reactivación dinámica.

## Escenarios de prueba

1. **Dado** un operador sin TOTP configurado, **cuando** inicia sesión, **entonces** solo puede configurar el TOTP antes de ver nada.
2. **Dado** `adaptador-potato` respondiendo `503` con `motivo: "token rechazado"`, **cuando** pasan 2 minutos, **entonces** su fila está en rojo con ese motivo.
3. **Dado** `portal-tareas` parado, **cuando** un operador abre el panel 5 min después, **entonces** `portal-tareas` aparece en rojo y el resto con la última comprobación de hace 5 min.
4. **Dado** `sync-peers` con un peer caído desde hace 2 h, **cuando** se abre su detalle, **entonces** el peer aparece destacado; el servicio sigue en verde.
5. **Dado** la base `alertas` caída, **cuando** se abre la sección Alertas, **entonces** aviso "fuente no disponible" sin error 500 y el resto del panel funciona.
6. **Dado** un intento de `POST` a la edición de un gateway, **cuando** se envía, **entonces** `403`.
7. **Dado** un destino de webhook suspendido por fallos, **cuando** el operador pulsa "Reactivar", **entonces** el cliente llama a la API interna y su estado pasa a Activo en verde sin reiniciar servicios.

## Ampliaciones posibles (fuera de la base)

Alta y baja de gateways en Mosquitto y edición de textos del portal. Cada una exige definir cómo el portal entrega la configuración al otro servicio sin escribir en su base.

---
> Creado: 2026-10-07 · Última revisión: 2026-10-08
