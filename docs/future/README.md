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
| Catálogo de hardware recomendado con gestión en /admin | Sección pública de recomendaciones de compra/montaje (dispositivos, DIY, antenas) gestionable desde el panel de administración con categorías, imágenes 1:1 y precios ([ver detalle](#catalogo-de-hardware-recomendado-con-gestion-en-admin)) |

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

Sección pública en el portal con recomendaciones comunitarias de dispositivos, componentes y antenas probados con éxito, administrable de forma dinámica desde el panel de operadores (`/admin` en Filament):

- **Vista pública (frontend):**
  - Listado visual clasificado o filtrable por categorías con tarjetas de productos o componentes.
  - Tarjetas con imagen cuadrada (relación 1:1), título, descripción funcional, categoría asociada, último precio conocido orientativo y enlace/botón de compra o tutorial de montaje.
- **Gestión en el panel de administración (/admin):**
  - **Tabla y recurso de Categorías:** Mantenimiento de categorías editables por los operadores (ej. *Dispositivo Montado*, *DIY (Hazlo tú mismo)*, *Antenas*, etc.).
  - **Tabla y recurso de Hardware:** Gestión de artículos individuales con los campos requeridos:
    - **Categoría:** Relación directa con la tabla de categorías.
    - **Nombre:** Denominación del modelo, antena o componente.
    - **Descripción:** Explicación técnica y recomendaciones de uso en la malla.
    - **Imagen:** Carga de imagen con herramienta de recorte (*cropper*) forzado a aspecto 1:1.
    - **Enlace:** URL de compra directa o enlace a guía de montaje.
    - **Último precio conocido:** Precio de referencia para la comunidad.

---
> Creado: 2026-10-07 · Última revisión: 2026-10-08


