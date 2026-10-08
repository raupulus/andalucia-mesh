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
| Gestión de destinos de webhooks y edición de textos desde el panel | Administración dinámica en Filament de suscripciones webhook (sin tocar YAML por SSH) y CMS Markdown para textos del portal sin redesplegar ([ver detalle](#gestion-de-webhooks-y-edicion-de-contenidos-desde-el-panel-admin)) |
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

### Gestión de webhooks y edición de contenidos desde el panel (/admin)

Dos funcionalidades orientadas a eliminar la dependencia de acceso SSH y redeploys para tareas operativas frecuentes:

1. **Gestión dinámica de destinos de webhooks:**
   - **Problema actual:** Dar de alta un webhook exige entrar por consola al servidor, editar manualmente `/srv/webhooks/webhooks.yaml` y `.env` (secreto HMAC), y reiniciar contenedores con `docker compose up -d --force-recreate`.
   - **Solución en `/admin`:**
     - Interfaz CRUD en Filament para añadir, pausar, editar y eliminar destinos de integradores.
     - Configuración de filtros (riesgos, tipos, provincias y nodos específicos) y generación automática de secretos criptográficos seguros.
     - Botón de prueba interactivo (*Test / Ping*) para verificar conectividad y firma antes de activar el webhook.
     - **Desacoplamiento técnico:** Para no vulnerar el principio de aislamiento escribiendo directamente en la base de datos de `webhooks`, el portal se comunica con el servicio Python mediante una API interna de administración en `aiohttp` (puerto interno autenticado con token de servicio) o mediante sincronización de volumen/señal de recarga en caliente (*hot-reload*).

2. **Edición dinámica de textos y contenidos del portal (CMS):**
   - **Problema actual:** Los textos de las páginas públicas (`/configura-tu-nodo`, `/conecta-tu-gateway`, aviso legal, quién lo impulsa, etc.) residen en ficheros Markdown estáticos en `resources/contenido/*.md`. Cualquier ajuste, errata o ampliación requiere editar código fuente en git, crear un commit y reconstruir/redesplegar la imagen Docker del portal.
   - **Solución en `/admin`:**
     - Gestor de contenidos en Filament con editor visual Markdown y pestañas multi-idioma (ES / EN / PT).
     - Persistencia de textos en la base de datos del portal (`page_contents`) manteniendo como *fallback* automático los ficheros Markdown del repositorio si no existe versión personalizada.
     - Soporte para interpolación de variables del sistema (`{PROJECT_NAME}`, `{LORA_REGION}`, etc.).
     - Invalidación automática de caché de vistas al guardar cambios, garantizando actualización inmediata sin caída de rendimiento.

---
> Creado: 2026-10-07 · Última revisión: 2026-10-08


