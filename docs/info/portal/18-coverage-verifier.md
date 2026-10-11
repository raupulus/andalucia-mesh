# Módulo: Verificador y Comparador de Cobertura RF («Mi Cobertura»)

> Herramienta interactiva de diagnóstico de enlaces RF 100% directos (0 saltos / sin retransmisiones multi-hop) para 1, 2 o hasta 3 nodos simultáneos, con cálculo de distancia geodésica en km, botones de compartición social, visualización matricial, filtros de calidad SNR, y exportación en PDF de alta fidelidad y Markdown.

## Qué hace y qué NO hace

- **Qué hace**:
  - Proporciona la ruta pública `/mi-cobertura` bajo la sección «Extras» de la cabecera.
  - Informa de manera inequívoca en la cabecera (banner destacado) y exportaciones que analiza **exclusivamente enlaces directos de radiofrecuencia (100% directos, 0 saltos RF)**, evaluando la calidad física de la estación con sus vecinos directos sin interferencia de tráfico multi-hop.
  - Ofrece autocompletado en vivo (`/mi-cobertura/buscar`) mediante debouncing para búsqueda por `Node ID` (ej. `!30618df9`), alias (`short_name`) o nombre largo (`long_name`).
  - Permite seleccionar 1 nodo para auditoría individual o comparar hasta 3 nodos simultáneamente en la malla.
  - Calcula la **distancia geodésica directa en km** mediante la fórmula de Haversine (`Cobertura::calcularDistanciaKm`) y la muestra debajo del distintivo provincial (`📏 X.X km`) cuando ambos extremos disponen de coordenadas geográficas en `api_nodes`.
  - Proporciona botones de compartición interactivos: **WhatsApp**, **Telegram**, **Email** y **Copiar enlace** directo al portapapeles con confirmación visual rápida («¡Copiado!»), preservando íntegramente los nodos seleccionados y el filtro activo.
  - Clasifica y estructura las recepciones a 1 salto en bloques temáticos visuales:
    - Enlace directo mutuo inter-router (si los nodos se escuchan directamente entre sí).
    - Nodos con cobertura bilateral o compartida (visión simultánea compartida).
    - Bloques de cobertura exclusiva para cada uno de los nodos evaluados (corredores y nodos que solo alcanza ese nodo).
  - Permite filtrar por calidad de señal SNR: «Solo SNR positivo» (SNR > 0 dB) o «Todos» (incluye enlaces marginales/negativos hasta -20 dB).
  - Presenta 4 tarjetas KPI en cabecera: Nodos únicos detectados, Enlace mutuo inter-nodo / Mejor enlace SNR, Cobertura bilateral/compartida / Provincias alcanzadas, y Especialización / Routers alcanzados, destacando los enlaces directos 100%.
  - Incluye un modal de espera con *spinner* corporativo con los colores de la marca (verde `#007A33`, verde claro `#67EA94`, azul `#2C2D3C`) mientras el backend procesa la telemetría.
  - Genera informes descargables en:
    - **PDF de alta fidelidad** (`barryvdh/laravel-dompdf` con extensión PHP `gd`): orientación vertical A4 (portrait), cabecera fija con datos de expedición, subtítulo de enlaces 100% directos y franja con bandera de Andalucía (verde/blanco/verde), distancias en km bajo provincia con icono vectorial de regla en base64 de alta resolución, pie de página fijo con URL centrada y franja andaluza superior, distintivo de filtro aplicado, KPIs, bloques y leyenda inferior.
    - **Markdown** (`.md`): sintaxis GFM limpia y estructurada, con bloque de aviso metodológico explícito sobre enlaces directos sin multi-hop y distancias en la columna de provincia.
  - Implementa almacenamiento en caché del servidor estricto de 1 hora (3.600 segundos) para la matriz compilada y para los binarios exportados.
  - Cumple estrictamente la regla de negocio RN-06 (cero cookies y cero almacenamiento de datos personales en el front público).
  - Ofrece soporte multidioma completo (Español, Inglés y Portugués) conforme a RN-48.

- **Qué NO hace**:
  - No incluye enlaces multi-hop ni paquetes reenviados por routers intermediarios (exclusivamente enlaces directos de 0 saltos RF).
  - No emite paquetes de radio ni modifica la configuración remota de los nodos.
  - No almacena cookies de sesión en el navegador de los visitantes.

## Modelo de datos

- **Vistas contrato consumidas** (en conexión `ingesta` con rol `portal_lector_ingesta`):
  - `api_nodes`: Metadatos del nodo (`id`, `short_name`, `long_name`, `role`, `hw_model`, `firmware`, `province`, `last_seen`, `is_gateway`, `is_router`).
  - `api_coverage_links`: Agregado de enlaces directos RF 100% (`gateway_id`, `from_id`, `packets`, `snr_avg`, `snr_last`, `last_rx_at`, `distance_km`), unificando recepciones directas en gateways, reportes de vecindad (`neighbor`) y saltos en rutas de ida y vuelta (`traceroute`) en ventana de 7 días, excluyendo valores centinela de SNR desconocido (`-32.0 dB`) y difusiones (`!ffffffff`).
  - `api_direct_receptions`: Recepciones directas sin agregar (`gateway_id`, `from_id`, `rx_at`, `snr`, `rssi`).
- **Estructura interna compilada** (`App\Data\Ingest\Cobertura`):
  - `valido`: boolean indicando si la compilación tuvo éxito.
  - `filtro`: string (`snr_positivo` | `todos`).
  - `nodos_evaluados`: array con metadatos de los 1 a 3 nodos evaluados.
  - `ventana_texto`: string con el rango temporal de análisis detectado.
  - `kpis`: array de 4 elementos con `cifra`, `titulo` y `subtitulo`.
  - `fila_mutuo`: array asociativo con datos de enlace directo entre los evaluados (o `null`).
  - `bloque_bilateral`: array de filas de nodos alcanzados por todos los evaluados.
  - `bloques_exclusivos`: array indexado por ID de nodo evaluado con los nodos que solo él alcanza.
  - `generado_en`: fecha y hora de compilación en servidor.

## Flujos principales

1. **Búsqueda y selección interactiva**:
   - El usuario escribe al menos 2 caracteres en el input de búsqueda.
   - Petición asíncrona a `/mi-cobertura/buscar?q=...` con debouncing de 250 ms.
   - Se muestran sugerencias con ID, alias, nombre y provincia. Al hacer clic, se añade el nodo como píldora (máximo 3).
2. **Consulta y compilación**:
   - Al pulsar «Consultar Cobertura» o cambiar el filtro SNR, se activa el modal con spinner corporativo.
   - Petición `GET /mi-cobertura?nodes=!id1,!id2&filter=...`.
   - El servicio busca en la caché de servidor `cobertura:matriz:{hash}`. Si expira o no existe, ejecuta las consultas agregadas sobre `api_coverage_links` y `api_nodes`.
   - Se categorizan los enlaces en mutuo, compartidos y exclusivos, se calculan los 4 KPIs y se guarda en caché por 3.600 segundos.
   - Se renderiza la vista `cobertura.index` con la tabla estructurada y la leyenda de colores SNR.
3. **Descarga de informes**:
   - El usuario pulsa «Descargar PDF» o «Descargar Markdown».
   - Rutas `/mi-cobertura/pdf` y `/mi-cobertura/markdown` con los mismos parámetros.
   - Se recupera la matriz desde la caché o se compila.
   - Para PDF: se renderiza `cobertura.pdf` y se procesa mediante Dompdf en formato A4 apaisado.
   - Para Markdown: se genera la cadena GFM con cabeceras, KPIs y tablas Markdown.
   - Se devuelven las respuestas con `Content-Disposition: attachment` y `Cache-Control: public, max-age=3600`.

## Puntos de entrada

- **Ruta Web Principal**: `GET /mi-cobertura`
  - Parámetros query: `nodes` (lista separada por comas, ej. `!30618df9,!63760c00`), `filter` (`snr_positivo` | `todos`).
  - Autenticación: No requerida (público).
  - Cookies: Ninguna (`RN-06`).
- **Endpoint Autocompletado**: `GET /mi-cobertura/buscar`
  - Parámetros query: `q` (término de búsqueda, mínimo 2 caracteres).
  - Autenticación: No requerida (público).
  - Rate limiting: Controlado por grupo web.
- **Descarga PDF**: `GET /mi-cobertura/pdf`
  - Parámetros query: `nodes`, `filter`.
  - Autenticación: No requerida.
- **Descarga Markdown**: `GET /mi-cobertura/markdown`
  - Parámetros query: `nodes`, `filter`.
  - Autenticación: No requerida.

## Dependencias en ambos sentidos

- **Hacia adentro**:
  - `barryvdh/laravel-dompdf` (generación de documentos PDF, requiere extensión PHP `gd`).
  - Base de datos `ingest` (conexión `ingesta`, vistas `api_coverage_links`, `api_nodes`).
  - Cache de Laravel (`Illuminate\Support\Facades\Cache`).
- **Hacia afuera**:
  - `resources/views/components/cabecera.blade.php`: Menú desplegable «Extras» y menú móvil enlazan a `/mi-cobertura`.
  - Archivos de internacionalización (`lang/es/portal.php`, `lang/en/portal.php`, `lang/pt/portal.php`).

## Configuración

| Variable | Valor por defecto | Efecto |
| :--- | :--- | :--- |
| `APP_URL` | `https://mesh.example.org` | Dominio impreso en pie de página del PDF |
| Caché TTL matriz | `3600` | Tiempo de vida en segundos de la matriz y PDFs en servidor |

## Trampas conocidas

- **TR-12 (Dompdf / Rendimiento y fuentes en tablas densas)**: En matrices de cobertura con más de 50 filas, Dompdf puede ralentizarse o desbordar márgenes si se usan etiquetas CSS complejas como `flexbox` o fuentes externas pesadas. *Solución*: Utilizar tablas HTML estándar (`border-collapse: collapse`), fuentes locales integradas (`DejaVu Sans`) y estilos inline o bloques `<style>` sencillos sin `display: flex`.

## Tests que lo cubren

- `tests/Feature/CoberturaFrontendTest.php`:
  - `test_mi_cobertura_ruta_responde_200_y_cero_cookies`
  - `test_mi_cobertura_aparece_en_navbar_bajo_extras`
  - `test_autocompletado_busca_por_id_o_nombre_e_ignora_acentos`
  - `test_resolucion_de_alias_cortos_en_peticion`
  - `test_consulta_caso_referencia_sierra_de_lijar_muestra_50_nodos_y_kpis`
  - `test_filtro_snr_positivo_vs_todos`
  - `test_descarga_pdf_devuelve_pdf_valido_con_cabeceras`
  - `test_plantilla_pdf_renderiza_logo_y_separadores_bandera`
  - `test_descarga_markdown_devuelve_gfm_valido`
  - `test_consulta_un_solo_nodo_muestra_informe_individual`
  - `test_cache_de_una_hora_en_servidor`
  - `test_traducciones_mi_cobertura`
  - `test_distancias_geodesicas_calculadas_y_renderizadas`
  - `test_enlaces_100_por_ciento_directos_aviso_y_metodologia`
  - `test_botones_compartir_y_restauracion_exacta_url`
  - `test_normalizacion_de_filtros_y_saneamiento_descargas`
  - `test_exclusion_centinela_snr_y_difusion`
  - `test_ausencia_enlace_fantasma_rau0_y_ja01`

## Pendiente real

- [x] Ninguno. Módulo completamente implementado, probado y verificado.

---
> Creado: 2026-10-11 · Última revisión: 2026-10-12
