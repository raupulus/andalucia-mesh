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

### Edición de contenidos y textos desde el panel (/admin)

- **Problema actual:** Los textos de las páginas públicas (`/configura-tu-nodo`, `/conecta-tu-gateway`, aviso legal, quién lo impulsa, etc.) residen en ficheros Markdown estáticos en `resources/contenido/*.md`. Cualquier ajuste, errata o ampliación requiere editar código fuente en git, crear un commit y reconstruir/redesplegar la imagen Docker del portal.
- **Solución en `/admin`:**
  - Gestor de contenidos en Filament con editor visual Markdown y pestañas multi-idioma (ES / EN / PT).
  - Persistencia de textos en la base de datos del portal (`page_contents`) manteniendo como *fallback* automático los ficheros Markdown del repositorio si no existe versión personalizada.
  - Soporte para interpolación de variables del sistema (`{PROJECT_NAME}`, `{LORA_REGION}`, etc.).
  - Invalidación automática de caché de vistas al guardar cambios, garantizando actualización inmediata sin caída de rendimiento.

---
> Creado: 2026-10-07 · Última revisión: 2026-10-09


