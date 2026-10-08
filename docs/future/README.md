# Ideas y Funcionalidades Futuras

Registro de funcionalidades y conceptos que han sido decididos formalmente pero cuyo desarrollo está deliberadamente aplazado para fases posteriores.

> [!NOTE]
> Este directorio no contiene deuda técnica ni registro de errores (bugs); su propósito exclusivo es registrar decisiones de alcance pospuesto.

## Elementos Aplazados

| Idea | Nota |
|---|---|
| Bot que responda por radio (`!status`, `!ping`) | Transmite: evaluar antes el impacto en el canal |
| Mapa de cobertura medida | A partir de enlaces directos nodo ↔ gateway con SNR |
| Informe semanal por los bots | Nodos nuevos, rankings, alertas resueltas |
| Alta y baja de gateways desde el panel | Definir cómo el portal entrega la configuración a Mosquitto sin escribir en su base |
| Gestión de destinos de webhooks y edición de textos desde el panel | Ídem |
| Reglas de ampliación del detector | Tras calibrar el MVP (`docs/info/detector-alertas/02-rule-catalog.md`) |
| Portal en inglés | — |
| Revisión del margen costero de ingesta | Evaluar ampliación del margen de 500 m en costas para evitar nodos legítimos marcados como FUERA |
| Inclusión de Ceuta y Melilla como provincias | Tratar Ceuta y Melilla como provincias de pleno derecho en polígonos, rankings y mapa (conexión natural hacia la península) |
| Configurador de nodos con descarga de configuración | Asistente interactivo en el portal para generar y descargar la configuración óptima (exportación en YAML / backup o código QR para la app de Meshtastic) adaptada al rol, tipo de nodo e intervalos recomendados de la malla |
| Tiempos de telemetría de energía y clima en «Configura tu nodo» | Ampliar `/configura-tu-nodo` con recomendaciones e intervalos detallados para telemetría de energía (batería, paneles solares) y sensores climáticos/ambientales (temperatura, humedad, presión), orientados a minimizar el uso de canal |
| Página de reporte de ideas y sugerencias | Formulario público en el portal para el envío de sugerencias e ideas de la comunidad, con persistencia en el backend de Laravel y gestión desde `/admin` |
| Sección visual de malas prácticas y problemas frecuentes | Diapositivas o carrusel gráfico didáctico en el portal sobre errores que saturan la red: saturación por rol ROUTER, cuándo elegir CLIENT vs CLIENT_MUTE, telemetría excesiva, Range Test emitiendo por el canal principal, etc. |
| Indicación de provincia de origen en reportes del bot | Al emitir alertas, avisos o informes en los bots (Telegram/Discord), indicar expresamente la provincia de la que procede la transmisión o el reporte (a partir del nodo o del gateway por el que entra) |
| Capturas reales de la app (iOS/iPhone) en las guías | Tomar capturas de la app oficial de Meshtastic en iPhone/iOS para ilustrar visualmente cada ajuste en `/configura-tu-nodo` y guías del portal junto al texto explicativo |
| Gestión visual de routers en /admin (mapa, Web Serial y CLI) | Interfaz en el panel de operadores para seleccionar routers en un mapa interactivo o desplegable y enviar comandos/acciones; integrando conexión local por Web Serial (Chromium) y generación de comandos CLI de Meshtastic listos para copiar y pegar en terminal |
| Sistema de bloqueos y baneo federado (autoban y manual) | Panel de reglas para autoban, bloqueos manuales, listado unificado y sincronización bidireccional por API autenticada (Sanctum) con instancias amigas ([ver detalle](#sistema-de-bloqueos-y-baneo-federado)) |
| Desbloqueador público de nodos en el frontend | Comprobador y autoservicio de desbloqueo por Node ID para nodos autobaneados (excluyendo bloqueos manuales o federados), con guía de motivos de ban y FAQ ([ver detalle](#desbloqueador-publico-de-nodos-en-el-frontend)) |
| Catálogo de hardware recomendado con gestión en /admin | Sección y página pública dinámica de recomendaciones de compra/montaje (dispositivos, DIY, antenas) con soporte multi-idioma y gestión desde /admin con cropper 1:1 y precios ([ver detalle](#catalogo-de-hardware-recomendado-con-gestion-en-admin)) |

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

### Catálogo de hardware recomendado con gestión en /admin

Sección y página dinámica pública en el portal para centralizar recomendaciones comunitarias de dispositivos, kits DIY, componentes, antenas y sistemas de alimentación probados con éxito en la malla:

1. **Objetivo y Filosofía:**
   - Orientar a nuevos integrantes y operadores veteranos sobre qué adquirir para participar en la red comunitaria sin cometer errores de compra (frecuencias incorrectas, antenas de mala calidad o dispositivos inadecuados para el rol deseado).
   - Contenido curado y avalado por las pruebas reales de la comunidad, independiente y transparente.

2. **Modelo de Datos en Base de Datos (PostgreSQL en Laravel):**
   - **Tabla `hardware_categories`:**
     - `id`: Identificador único (bigint / ULID).
     - `slug`: Identificador URL único (ej. `dispositivo-montado`, `diy`, `antenas`, `energia-solar`).
     - `name`: Nombre transducible en formato JSONB (`{"es": "Dispositivo Montado", "en": "Assembled Device"}`).
     - `description`: Descripción transducible en JSONB (`{"es": "...", "en": "..."}`), nullable.
     - `sort_order`: Entero para ordenación manual personalizada (por defecto 0).
     - `is_active`: Booleano para visibilidad pública.
     - Timestamps estándar (`created_at`, `updated_at`).
   - **Tabla `hardware_items`:**
     - `id`: Identificador único.
     - `category_id`: Clave foránea referenciando a `hardware_categories.id`.
     - `slug`: Identificador URL amigable.
     - `name`: Nombre comercial o título del proyecto (JSONB o cadena).
     - `description`: Explicación técnica y recomendaciones de uso transducibles en JSONB (`{"es": "...", "en": "..."}`).
     - `image_path`: Ruta del fichero de imagen recortada.
     - `buy_url`: Enlace externo directo a tienda, distribuidor oficial o fabricante.
     - `guide_url`: Enlace opcional a tutorial de montaje, flasheo o esquemático (nullable).
     - `last_price`: Valor decimal orientativo del coste de mercado (nullable).
     - `currency`: Moneda de referencia (por defecto `EUR`).
     - `is_featured`: Booleano para resaltar productos destacados en cabecera.
     - `is_active`: Booleano para activar o pausar visibilidad.
     - `sort_order`: Entero de ordenación.
     - Timestamps estándar (`created_at`, `updated_at`).

3. **Soporte Multi-idioma y Traducciones (i18n):**
   - **Campos de contenido:** Almacenamiento transducible en JSONB (`es` y `en`) compatible con la configuración de localización de Laravel y Filament (mediante *translatable fields* / Spatie Translatable).
   - **Panel `/admin`:** Pestañas o conmutador de idioma en el formulario para rellenar títulos, descripciones y notas técnicas en español (idioma canónico) e inglés.
   - **Frontend:** Resolución automática del texto según el idioma activo (`app()->getLocale()`) con fallback seguro a español.
   - **Textos de interfaz:** Claves de localización en ficheros de idioma (`lang/es.json`, `lang/en.json`) para botones, etiquetas de filtro ("Todas las categorías", "Último precio conocido", "Dónde comprar", "Guía de montaje", "Sin resultados").

4. **Gestión en el Panel de Operadores (/admin en Filament):**
   - **Recurso `HardwareCategoryResource`:**
     - Listado con reordenación interactiva (*drag and drop*), contador de productos vinculados e interruptor rápido de activación.
     - Formulario con campos de nombre y descripción traducibles, y generación automática de *slug*.
   - **Recurso `HardwareItemResource`:**
     - Selector desplegable de categoría.
     - Nombre y descripción con soporte bilingüe (ES / EN).
     - Campo de subida de imagen (`FileUpload`) con editor integrado configurado obligatoriamente en recorte cuadrado 1:1 (`imageEditorAspectRatios(['1:1'])`).
     - Entradas para URL de compra (`buy_url`) y URL de guía técnica (`guide_url`).
     - Campo monetario para último precio conocido con sufijo de divisa (`€`).
     - Controles de estado: interruptor de activo/inactivo, interruptor de destacado (*featured*) y ordenación.

5. **Nueva Página Dinámica en el Frontend (`/hardware`):**
   - **Ruta y Controlador:** `GET /hardware` atendido por `HardwareController@index`, consultando categorías activas y artículos visibles con eager loading (`with('category')`).
   - **Diseño visual (conforme a `DESIGN.md`):**
     - Cabecera con H1, resumen explicativo y texto aclaratorio sobre la independencia de las recomendaciones comunitarias.
     - **Barra de navegación y filtros interactivos:**
       - Chips / botones estilo *pill* (`x-chip`) para filtrar por categoría (*Todas*, *Dispositivos montados*, *DIY*, *Antenas*, *Alimentación / Solar*...).
       - Filtrado reactivo en URL (`/hardware?categoria=antenas`) o interactividad ligera vía Alpine.js sin recarga obligatoria.
       - Buscador de texto en tiempo real para localizar modelos o componentes específicos.
     - **Rejilla responsive de tarjetas (Grid):**
       - Imagen cuadrada 1:1 optimizada en WebP con *lazy loading* y texto alternativo semántico.
       - Etiqueta o *badge* de categoría.
       - Título y distintivo de recomendado/destacado si aplica.
       - Resumen técnico destacando por qué la comunidad lo recomienda y su comportamiento en la malla.
       - Indicador de precio estimado ("Último precio conocido: ~XX €").
       - Botonera de acción: Enlace principal "Comprar / Adquirir" y enlace secundario "Guía de montaje / Flasheo" si está disponible.
     - **Estado vacío:** Mensaje visual amigable si no hay productos disponibles bajo el filtro seleccionado.

---
> Creado: 2026-10-07 · Última revisión: 2026-10-08


