# Ideas y Funcionalidades Futuras

Registro de funcionalidades y conceptos que han sido decididos formalmente pero cuyo desarrollo está deliberadamente aplazado para fases posteriores.

> [!NOTE]
> Este directorio no contiene deuda técnica ni registro de errores (bugs); su propósito exclusivo es registrar decisiones de alcance pospuesto.

## Elementos Aplazados

| Idea | Nota |
|---|---|
| Mapa de cobertura medida | A partir de enlaces directos nodo ↔ gateway con SNR |
| Informe semanal por los bots | Nodos nuevos, rankings, alertas resueltas |
| Alta y baja de gateways desde el panel | Definir cómo el portal entrega la configuración a Mosquitto sin escribir en su base |
| Edición de contenidos y textos desde el panel (CMS) | Administración en Filament de los textos de páginas públicas con fallback a Markdown ([ver detalle](#edicion-de-contenidos-y-textos-desde-el-panel-admin)) |
| Reglas de ampliación del detector | Tras calibrar el MVP (`docs/info/detector-alertas/02-rule-catalog.md`) |
| Revisión del margen costero de ingesta | Evaluar ampliación del margen de 500 m en costas para evitar nodos legítimos marcados como FUERA |
| Inclusión de Ceuta y Melilla como provincias | Tratar Ceuta y Melilla como provincias de pleno derecho en polígonos, rankings y mapa (conexión natural hacia la península) |
| Tiempos de telemetría de energía y clima en «Configura tu nodo» | Ampliar `/configura-tu-nodo` con recomendaciones e intervalos detallados para telemetría de energía (batería, paneles solares) y sensores climáticos/ambientales (temperatura, humedad, presión), orientados a minimizar el uso de canal |
| Sección visual de malas prácticas y problemas frecuentes | Diapositivas o carrusel gráfico didáctico en el portal sobre errores que saturan la red: saturación por rol ROUTER, cuándo elegir CLIENT vs CLIENT_MUTE, telemetría excesiva, Range Test emitiendo por el canal principal, etc. |
| Indicación de provincia de origen en reportes del bot | Al emitir alertas, avisos o informes en los bots (Telegram/Discord), indicar expresamente la provincia de la que procede la transmisión o el reporte (a partir del nodo o del gateway por el que entra) |
| Capturas reales de la app (iOS/iPhone) en las guías | Tomar capturas de la app oficial de Meshtastic en iPhone/iOS para ilustrar visualmente cada ajuste en `/configura-tu-nodo` y guías del portal junto al texto explicativo |
| Gestión visual de routers en /admin (mapa, Web Serial y CLI) | Interfaz en el panel de operadores para seleccionar routers en un mapa interactivo o desplegable y enviar comandos/acciones; integrando conexión local por Web Serial (Chromium) y generación de comandos CLI de Meshtastic listos para copiar y pegar en terminal |
| Sistema de bloqueos y baneo federado (autoban y manual) | Panel de reglas para autoban, bloqueos manuales, listado unificado y sincronización bidireccional por API autenticada (Sanctum) con instancias amigas ([ver detalle](#sistema-de-bloqueos-y-baneo-federado)) |
| Desbloqueador público de nodos en el frontend | Comprobador y autoservicio de desbloqueo por Node ID para nodos autobaneados (excluyendo bloqueos manuales o federados), con guía de motivos de ban y FAQ ([ver detalle](#desbloqueador-publico-de-nodos-en-el-frontend)) |
| Estadísticas básicas y anónimas de uso | Métricas agregadas y autoalojadas sin cookies (visitas por página, uso del flasheador/configurador, clics en hardware y diagnósticos) respetando RN-06 ([ver detalle](#estadisticas-basicas-y-anonimas-de-uso)) |
| Sección o ficha dedicada por provincia | Páginas provinciales con estadísticas exclusivas (nodos, carga del canal, routers) y alertas focalizadas de cada provincia ([ver detalle](#seccion-o-ficha-dedicada-por-provincia)) |
| Catálogo de videotutoriales y guías audiovisuales | Página formativa /videotutoriales con vídeos y píldoras prácticas organizadas por temática y nivel, con fachada "click-to-load" respetuosa con RN-06 y gestión en /admin ([ver detalle](#catalogo-de-videotutoriales-y-guias-audiovisuales)) |
| Sistema de votaciones comunitarias por hardware y Telegram | Consultas participativas con validación de nodo físico por Web Serial y pertenencia verificada al grupo de Telegram de Andalucía ([ver detalle](#sistema-de-votaciones-comunitarias-por-hardware-y-telegram)) |

## Detalle de Ideas

### Sistema de bloqueos y baneo federado

Mecanismo para identificar, bloquear y aislar nodos perjudiciales (spam, flood o degradación deliberada de la malla) de forma local y coordinada:

- **Autoban por reglas configurables:** Panel de condiciones en `/admin` donde definir criterios de detección que apliquen baneo automático al superarse ciertos umbrales (ej. saturación extrema de paquetes, patrones lesivos).
- **Baneos manuales:** Apartado en el panel para registrar bloqueos manuales permanentes dictados por operadores.
- **Listado unificado de baneados:** Vista consolidada con todos los nodos bloqueados, registrando metadatos de trazabilidad: identificador de nodo, fecha, motivo, estado y origen (local automático, manual local o instancia remota).
- **Criterio de revocación según origen:** La procedencia del bloqueo determina si es revocable por autoservicio en la web (solo autobaneos locales) o si requiere mediación de operadores (manuales y remotos).
- **Sincronización API con instancias amigas (Laravel Sanctum):**
  - **Recepción autenticada:** Endpoint para recibir listas de baneos de instancias amigas verificadas, permitiendo incorporar y gestionar altas o bajas controladas conservando el origen.
  - **Emisión autenticada:** Exportación o push de la lista de baneados locales hacia las instancias asociadas.

### Desbloqueador público de nodos en el frontend

Página y herramienta pública de autoservicio en el portal para verificar si un nodo está bloqueado y solicitar su reactivación tras corregir la causa:

- **Verificación y desbloqueo por Node ID:**
  - El usuario ingresa exclusivamente su `Node ID` (ej. `!a1b2c3d4`). No se gestionan ni bloquean direcciones IP.
  - **Desbloqueo directo (autoservicio):** Si el bloqueo se originó por una regla automática (autoban local), el usuario puede ejecutar el desbloqueo de forma autónoma una vez subsanada la configuración del nodo.
  - **Exclusión de desbloqueo directo:** Si el bloqueo es manual (aplicado por un operador) o proviene de una instancia externa federada, el sistema no permite el desbloqueo automático e informa al usuario del motivo y el canal de contacto para revisión.
- **Panel informativo de causas habituales de autoban:**
  - Demasiados o incoherentes saltos (`hop_limit`).
  - Telemetría excesiva o mal ajustada (intervalos demasiado cortos).
  - Range Test emitiendo en canales colectivos/primarios.
  - Configuración indebida de roles core (`ROUTER` / `REPEATER` en conflicto).
  - Mensajes abusivos o flood continuado de paquetes.
- **Guía de localización y FAQ:**
  - Ayuda visual o modal sobre cómo localizar el `Node ID` en la app oficial de Meshtastic.
  - Acordeón interactivo de preguntas frecuentes: significado del bloqueo, motivos para proteger el canal común, cómo evitar reincidencias y funcionamiento del sistema de protección.

### Estadísticas básicas y anónimas de uso

Sistema de analítica agregada, autoalojada y orientada a la privacidad para conocer el impacto de la web y el uso real de las herramientas comunitarias, sin intrusión hacia los visitantes:

- **Principios de privacidad y cumplimiento normativo (`RN-06`):**
  - **Cero cookies:** No utiliza cookies, etiquetas de seguimiento externas ni almacenamiento local persistente de rastreo.
  - **Sin datos personales ni perfiles:** No se almacenan direcciones IP ni identificadores únicos de dispositivo; las peticiones no se asocian a identidades de usuario ni huellas de navegador (*fingerprinting*).
  - **Autoalojado e independiente:** 100% procesado internamente por el portal sin compartir datos con terceros (sin Google Analytics, Meta Pixel ni servicios externos). Cumplimiento GDPR estricto sin necesidad de banner de cookies.
  - **Persistencia en contadores agregados:** Las métricas se consolidan por fecha, ruta o categoría de evento directamente en tablas agregadas de PostgreSQL (ej. `metric_daily_views`, `metric_event_counters`).

- **Métricas y herramientas a evaluar:**
  - **Tráfico web general y navegación:**
    - Páginas vistas y visitas agregadas por ruta (`/`, `/configura-tu-nodo`, `/configurador`, `/hardware`, `/revisa-tu-nodo`, `/conecta-tu-gateway`, `/sugerencias`).
    - *Referrers* agrupados y limpios de parámetros de tracking (conocer procedencia comunitaria: Telegram, GitHub, foros o búsquedas orgánicas).
    - Idioma preferido de consulta (ES / EN / PT) para priorizar traducciones.
  - **Uso del Configurador y Flasheador Web (`/configurador`):**
    - Número de accesos a la herramienta.
    - Flasheos o escrituras de configuración completadas por USB (Web Serial API).
    - Conexiones y ajustes completados por Bluetooth (Web BLE API).
    - Generación y escaneo de códigos QR de configuración.
    - Descargas de archivos de configuración (`.yaml` o exportación de canales).
    - Distribución agregada de roles configurados (Client, Client Mute, Router, Tracker, etc.).
  - **Comprobador de Nodos (`/revisa-tu-nodo`):**
    - Número total de diagnósticos solicitados.
    - Proporción de nodos con estado óptimo frente a nodos con advertencias.
    - Tipos de malas prácticas detectadas con mayor recurrencia (saltos excesivos, intervalos de telemetría agresivos, modo HAM indebido).
  - **Catálogo de Hardware Recomendado (`/hardware`):**
    - Clics salientes hacia enlaces de adquisición o tiendas recomendadas por modelo.
    - Clics hacia guías de montaje o proyectos DIY.
    - Categorías de mayor interés para la comunidad (dispositivos comerciales, montajes DIY, antenas, sistemas solares/baterías).
  - **Otras herramientas e interacciones:**
    - Volumen de propuestas recibidas en `/sugerencias` por temática.
    - Acciones de copiado de comandos CLI o cadenas de configuración en guías.
    - Consultas y desbloqueos completados en el desbloqueador de nodos (cuando esté operativo).

- **Persistencia y captura en Laravel:**
  - Middleware ligero para rutas públicas web que incremente el contador diario de la ruta (con amortiguación en caché de Laravel/Redis si hay picos).
  - Endpoint interno protegido y rate-limited (`POST /api/v1/metrics/event`) consumido por JavaScript asíncrono no bloqueante (`navigator.sendBeacon` o `fetch`) para registrar eventos de interacción (clics en hardware, uso de Web Serial, descargas).

- **Visualización en el panel de administración (`/admin`):**
  - Panel o pestaña de métricas en Filament con widgets interactivos (gráficos temporales, rankings y contadores acumulados).
  - Filtros temporales (últimos 7 días, 30 días, meses, histórico anual).
  - Tarea periódica de consolidación y limpieza para mantener un tamaño de datos ultraligero y constante en base de datos.

### Sección o ficha dedicada por provincia

Vistas provinciales independientes (`/provincias/{slug}`) orientadas a proporcionar a las comunidades locales de radioaficionados, grupos de mensajería territoriales (Telegram/Discord) y operadores de comarca una panorámica limpia, exclusiva y compartible de su propia provincia:

- **Motivación y casos de uso:**
  - Gran parte de la comunidad de Meshtastic se organiza en células o canales provinciales (ej. *Meshtastic Cádiz*, *Meshtastic Sevilla*, *Meshtastic Málaga*).
  - La portada y `/rankings` ofrecen una visión global de toda Andalucía que diluye los datos comarcales. Una página propia permite seguir la salud, routers e incidencias de la provincia sin ruido del resto de la región.
- **Rutas y catálogo territorial:**
  - Rutas amigables: `/provincias/{slug}` (con alias o redirección desde `/provincia/{slug}`): `/provincias/almeria`, `/provincias/cadiz`, `/provincias/cordoba`, `/provincias/granada`, `/provincias/huelva`, `/provincias/jaen`, `/provincias/malaga` y `/provincias/sevilla` (ampliable a `ceuta` y `melilla`).
  - Selector rápido desplegable o fila de píldoras en cabecera para alternar entre provincias sin regresar a la portada.
- **Estadísticas y métricas exclusivas de la provincia:**
  - **Tarjetas KPI locales:** Nodos activos en la ventana seleccionada (`1h`, `6h`, `12h`, `1d`, `7d`), porcentaje de peso sobre el total de Andalucía, saturación de canal media ponderada (routers 60 % + clientes 40 %) y estado semafórico (`Holgado`, `Cargado`, `Saturado`).
  - **Infraestructura provincial:** Listado y estado (online/offline) de los routers de infraestructura y repetidores ubicados en la provincia, con niveles de batería y ocupación de canal.
  - **Gateways y tráfico:** Gateways MQTT que dan cobertura a la zona y reparto de tráfico (nodeinfo, posición, telemetría, texto).
  - **Rankings comarcales:** Top de nodos de la provincia por tiempo de emisión al aire y paquetes transmitidos, fomentando buenas prácticas de ajuste.
  - **Silueta gráfica SVG:** Contorno visual vectorial de la provincia con su burbuja de nodos y polo de inaccesibilidad.
- **Alertas y estado de salud comarcal:**
  - **Alertas activas exclusivas:** Listado filtrado automáticamente donde solo aparecen incidencias abiertas relativas a nodos geolocalizados en la provincia o gateways locales.
  - **Nodos en riesgo locales:** Identificación de anomalías (saltos excesivos, intervalos de telemetría agresivos, saturación) para que los coordinadores locales puedan intervenir o contactar al propietario.
  - **Historial provincial:** Alertas resueltas recientemente en el territorio.
- **Integración, compartibilidad y privacidad:**
  - Enlace directo desde el mapa SVG de la portada y su tabla accesible (al pulsar sobre la provincia, además del modal de carga rápida, se ofrece un botón a "Ver detalle provincial").
  - Metadatos Open Graph y Twitter Cards dinámicos por provincia (ej. *"Estado de la red Meshtastic en Cádiz — 42 nodos activos, canal holgado"*), facilitando previsualizaciones enriquecidas al compartir el enlace en canales comunitarios.
  - Soporte multidioma completo (ES / EN / PT) según `RN-48`.
  - Cero cookies (`RN-06`) y datos derivados de las vistas de ingesta y alertas existentes sin peticiones a terceros.

### Catálogo de videotutoriales y guías audiovisuales

Sección dedicada en el portal (`/videotutoriales`) orientada a proporcionar formación visual, demostraciones prácticas y guías paso a paso para la comunidad de Meshtastic en Andalucía:

- **Motivación y valor formativo:**
  - Complementar las guías de texto del portal (`/configura-tu-nodo`, `/conecta-tu-gateway`, `/faq`) con vídeos didácticos que resuelvan dudas visuales complejas (flasheo por navegador, ajustes de roles, ensamblaje de cajas estancas, conexionado de antenas y optimización solar).
  - Servir de biblioteca organizada de conocimiento comunitario, reuniendo tanto producciones audiovisuales propias de la red como tutoriales curados y contrastados de creadores de referencia.
- **Ruta y navegación:**
  - Ruta pública: `/videotutoriales` (con redirección o alias `/videos`).
  - Accesible desde el menú desplegable **«Extras»** de la barra de navegación y mediante enlaces contextuales destacados ("¿Prefieres verlo en vídeo?") dentro de las guías de configuración.
- **Organización y filtros del catálogo:**
  - **Categorías temáticas:**
    - *Primeros pasos y flasheo:* Flasheo web, actualización de firmware, configuración de región `EU_868`, nombre del nodo y canales primario/secundarios.
    - *Roles y salud de la red:* Diferencias prácticas entre `CLIENT`, `CLIENT_MUTE` y `ROUTER`, ajuste óptimo de saltos (`hop_limit`) y cómo evitar saturar la malla.
    - *Hardware y montaje:* Placas recomendadas (Heltec, T-Beam, T-Echo, RAK Wireless), tipos de antenas (colineales, dipolos, planos de tierra), conectores SMA/IPEX y cajas para exterior.
    - *Gateways y MQTT:* Conexión al servidor MQTT de la comunidad, configuración de subida y prevención de bucles de retransmisión.
    - *Alimentación y nodos solares:* Baterías 18650/LiFePO4, controladores de carga solar, paneles fotovoltaicos y cálculo de autonomía en ubicaciones remotas.
    - *Aplicación móvil:* Uso de las aplicaciones Meshtastic en Android e iOS, mapas offline, chats de difusión y mensajería directa punto a punto.
  - **Filtros por nivel de dificultad:** Principiante, Intermedio y Avanzado.
  - **Buscador integrado:** Localización rápida por palabras clave, componentes o autor.
- **Privacidad y visualización sin cookies (`RN-06`):**
  - **Fachada "Click-to-Load" (Privacy-friendly facade):**
    - La página carga únicamente miniaturas estáticas optimizadas (WebP generadas localmente o cacheadas) con un botón visual de reproducción. No se insertan *iframes* ni scripts de rastreo de terceros (como Google/YouTube) durante la carga inicial.
    - Al pulsar en reproducir, el reproductor incrustado se carga bajo demanda utilizando el dominio de privacidad estricta `www.youtube-nocookie.com` con parámetro `dnt=1` (Do Not Track) y sin emisión de cookies hacia el visitante, o bien ofreciendo enlaces directos para su visualización en plataformas externas (YouTube, PeerTube, Odysee).
- **Gestión desde el panel de operadores (`/admin`):**
  - Recurso en Filament (`VideoTutorialResource`) para la administración dinámica del catálogo:
    - Campos: título, slug, descripción breve, URL del vídeo (YouTube, PeerTube, Vimeo o archivo MP4 local), categoría, nivel de dificultad, duración aproximada (minutos), autor/canal, miniatura personalizada (o captura automática optimizada), estado (publicado/borrador) y orden de prioridad.
- **Multidioma (`RN-48`) y optimización SEO:**
  - Textos de interfaz y metadatos localizados en Español, Inglés y Portugués, con indicación del idioma del audio y subtítulos disponibles.
  - Esquema semántico `VideoObject` (Schema.org / JSON-LD) y metadatos Open Graph para enriquecer los resultados de búsqueda y previsualizaciones en aplicaciones de mensajería (Telegram/Discord).

### Sistema de votaciones comunitarias por hardware y Telegram

Mecanismo de toma de decisiones participativas y gobernanza comunitaria en el portal (`/votaciones`) para dirimir acuerdos técnicos (cambios de slots de frecuencia, canales comunitarios, directrices de despliegue de repetidores estratégicos o normas de red), blindado contra fraude mediante doble validación física y social:

- **Motivación y gobernanza sin bots (Anti-Sybil):**
  - Las encuestas abiertas tradicionales en la web o en plataformas de mensajería sufren de vulnerabilidad ante multicuentas, bots o votantes ajenos al ámbito territorial de la red.
  - Para asegurar legitimidad democrática y que las decisiones las tomen quienes realmente operan nodos en Andalucía, el voto requiere demostrar simultáneamente la posesión de un nodo físico y la pertenencia a la comunidad regional.
- **Doble validación de autenticidad:**
  - **1. Verificación de nodo físico por puerto serie (`Web Serial API`):**
    - El votante conecta su dispositivo Meshtastic mediante cable USB al ordenador (navegadores Chromium, Chrome, Edge u Opera).
    - Mediante la API nativa de Web Serial, el navegador se comunica por puerto serie con el microcontrolador usando los paquetes Protobuf de Meshtastic (`FromRadio` / `ToRadio`).
    - Se extrae la huella de hardware del dispositivo: identificador `Node ID` (ej. `!a1b2c3d4`), MAC de radio/Bluetooth, modelo de placa y número de serie único del procesador (Chip ID inmutable de ESP32 o nRF52).
    - Se ejecuta un desafío local en tiempo real (*challenge-response*) con el nodo para certificar que hay un hardware real conectado físicamente y no un emulador o un identificador inyectado artificialmente.
  - **2. Pertenencia verificada al grupo de Telegram de Andalucía:**
    - Autenticación complementaria mediante el widget oficial de Telegram Login o a través del bot comunitario (`bot-telegram`).
    - La API de Telegram verifica de forma transparente e instantánea (`getChatMember`) que la cuenta del usuario es miembro activo (`member`, `administrator` o `creator`) del supergrupo oficial de *Meshtastic Andalucía*.
    - Impide que usuarios ajenos a la red regional participen en decisiones que afectan al espectro radioeléctrico andaluz.
- **Garantías de voto democrático, secreto y privacidad (`RN-06`):**
  - **Principio 1 nodo = 1 voto:** Cada nodo físico (por Chip ID / Node ID) y cada identificador de Telegram pueden emitir un único voto por consulta activa.
  - **Disociación criptográfica (Voto secreto / Blind voting):**
    - Para garantizar el secreto del voto y evitar sesgos o presiones, se separa estrictamente la acreditación del votante del contenido de la papeleta:
      - Tabla de participación (`poll_participants`): Registra únicamente que un determinado hash de nodo y usuario de Telegram ya han ejercido su derecho en la consulta `poll_id`.
      - Tabla de votos (`poll_votes`): Registra de manera independiente y anónima la opción seleccionada, sin almacenar claves foráneas ni referencias directas que permitan rastrear qué usuario votó qué opción.
  - **Cero cookies (`RN-06`):** El proceso opera mediante tokens efímeros en memoria con expiración corta (15 min) durante el flujo de validación, sin fijar cookies de sesión en el navegador.
- **Flujo de usuario en el portal (`/votaciones`):**
  - 1. *Consulta activa:* Lectura del contexto de la propuesta técnica, alternativas disponibles y plazo de votación.
  - 2. *Identificación:* Inicio de sesión rápido con Telegram para validar pertenencia comunitaria.
  - 3. *Detección de hardware:* Conexión del nodo por USB; un solo clic en "Detectar mi nodo" lee y valida el chip mediante Web Serial.
  - 4. *Emisión de voto:* Selección de la opción y confirmación irreversible.
  - 5. *Escrutinio:* Visualización de barras de resultados y porcentaje de quórum (en tiempo real o tras el cierre, según las bases de la consulta).
- **Gestión en el panel de operadores (`/admin`):**
  - Recurso en Filament (`CommunityPollResource`):
    - Configuración de consultas: título, descripción detallada en Markdown, opciones de respuesta, fechas de inicio y cierre, umbrales de quórum mínimo requerido y visibilidad de resultados.
    - Auditoría del proceso con métricas de participación y generación de actas de escrutinio para transparencia pública.

### Edición de contenidos y textos desde el panel (/admin)

- **Problema actual:** Los textos de las páginas públicas (`/configura-tu-nodo`, `/conecta-tu-gateway`, aviso legal, quién lo impulsa, etc.) residen en ficheros Markdown estáticos en `resources/contenido/*.md`. Cualquier ajuste, errata o ampliación requiere editar código fuente en git, crear un commit y reconstruir/redesplegar la imagen Docker del portal.
- **Solución en `/admin`:**
  - Gestor de contenidos en Filament con editor visual Markdown y pestañas multi-idioma (ES / EN / PT).
  - Persistencia de textos en la base de datos del portal (`page_contents`) manteniendo como *fallback* automático los ficheros Markdown del repositorio si no existe versión personalizada.
  - Soporte para interpolación de variables del sistema (`{PROJECT_NAME}`, `{LORA_REGION}`, etc.).
  - Invalidación automática de caché de vistas al guardar cambios, garantizando actualización inmediata sin caída de rendimiento.

---
> Creado: 2026-10-07 · Última revisión: 2026-10-14


