# Módulo 16: Páginas y Artículos Dinámicos (`custom_pages`)

> Módulo de gestión y publicación de artículos divulgativos, guías técnicas y páginas dinámicas del portal con integración SEO y panel de operadores.

## Qué hace y qué NO hace
- **Qué hace**:
  - Permite a los operadores crear, editar, publicar y retirar artículos y páginas dinámicas desde el panel `/admin` (`CustomPageResource`).
  - Valida de forma estricta la obligatoriedad y unicidad del `slug` antes de guardar.
  - Ofrece listado público en `/paginas` con tarjetas horizontales de ancho completo: imagen a la izquierda, franja vertical verde/blanca/verde inspirada en la bandera de Andalucía, borde verde/blanco con contraste accesible (WCAG AAA) en temas claro y oscuro, fecha formateada, descripción y keywords como badges verdes en la esquina inferior derecha.
  - Ofrece vista de detalle en `/paginas/{slug}`: imagen de portada en la parte superior, título H1, fecha de publicación, descripción principal, badges de keywords, contenido completo renderizado desde Markdown mediante `ContenidoMarkdown` y aviso de emergencias.
  - Genera metadatos SEO completos: Open Graph, Twitter Cards y datos estructurados Schema.org `Article` en formato JSON-LD.
  - Muestra en la portada (`/`) un bloque con las 4 últimas páginas activas en tarjetas compactas (2 por fila en escritorio, 1 en móvil) colocado encima de la sección «Quién está detrás», con un botón verde centrado que enlaza al catálogo completo `/paginas`.
  - Integra automáticamente `/paginas` (prioridad 0.8) y todas las páginas activas `/paginas/{slug}` (prioridad 0.7) en `/sitemap.xml`.
  - Añade acceso a «Páginas» en el desplegable «Extras» de la barra de navegación superior (escritorio y móvil).
  - Garantiza el cumplimiento de `RN-06` (cero cookies en navegación pública).
- **Qué NO hace**:
  - No admite comentarios anónimos ni valoraciones de usuarios.
  - No expone endpoints de creación o mutación en la API pública.
  - No incluye scripts de analítica de terceros ni cookies de seguimiento.

## Modelo de datos
- **Tabla**: `custom_pages` (migración `2026_10_08_000010_create_custom_pages_table.php`):
  - `id`: unsigned bigint PK, autoincremental.
  - `title`: string(255), título del artículo.
  - `slug`: string(255) unique, identificador amigable en URL.
  - `description`: text(500), resumen o entradilla para tarjetas y metadatos SEO.
  - `content`: longText, cuerpo completo en formato Markdown.
  - `keywords`: json nullable, etiquetas temáticas del artículo (array de strings).
  - `featured_image`: string nullable, ruta en disco `public` o URL completa de la portada.
  - `is_active`: boolean (por defecto `true`), control de visibilidad pública.
  - `created_at`, `updated_at`: timestamps estándar de Laravel.
- **Modelo Eloquent**: `App\Models\CustomPage`:
  - Scopes: `active()` (filtra `is_active = true`), `recent()` (ordena por `created_at desc, id desc`).
  - Accessors: `cover_image_url` (resuelve Storage público, URLs absolutas o imagen de reserva), `formatted_date` (formato localizado según `app()->getLocale()`).

## Flujos principales
1. **Creación / Edición desde Intranet (`/admin/custom-pages`)**:
   - El operador introduce título (autogenera slug en creación), revisa o ajusta el slug (validación única), redacta en el editor Markdown, sube imagen de portada y añade keywords.
   - Al guardar, los cambios se persisten en `custom_pages`.
2. **Consulta en Portada (`/`)**:
   - `PortadaController` recupera las 4 páginas activas más recientes.
   - Se renderizan en `portada.blade.php` en una cuadrícula de 2 columnas con tarjetas compactas y botón verde de acceso general.
3. **Navegación del Catálogo (`/paginas`)**:
   - `CustomPageController@index` lista todas las páginas activas ordenadas cronológicamente en tarjetas horizontales con la franja de Andalucía y badges verdes.
4. **Lectura de Artículo (`/paginas/{slug}`)**:
   - `CustomPageController@show` busca por slug activo (devuelve 404 si está inactivo o no existe).
   - Renderiza con metadatos Open Graph, Twitter Card y Schema.org `Article`.
5. **Rastreo por Motores de Búsqueda (`/sitemap.xml`)**:
   - `SitemapController@buildSitemap` incluye `/paginas` y los slugs dinámicos activos con su última fecha de modificación.

## Puntos de entrada
- **`GET /paginas`**:
  - Autenticación: No.
  - Permisos: Público.
  - Cookies: Ninguna (cumple `RN-06`).
- **`GET /paginas/{slug}`**:
  - Autenticación: No.
  - Permisos: Público (requiere `is_active = true`).
  - Errores: 404 Not Found si no existe o está inactiva.
- **`GET|POST|PUT|DELETE /admin/custom-pages`**:
  - Autenticación: Sí (`web`, panel Filament).
  - Permisos: Operadores autenticados (`User::activo`).

## Dependencias en ambos sentidos
- **Hacia adentro**:
  - `App\Servicios\ContenidoMarkdown`: Conversión y saneamiento seguro del Markdown.
  - `resources/views/components/layout.blade.php`: Inyección de metadatos SEO dinámicos (`:image`, `:keywords`).
  - `app.css`: Clases `.tarjeta-pagina-horizontal`, `.tarjeta-pagina-compacta`, `.franja-andalucia-vertical`, `.badge-keyword`, `.btn-verde`.
- **Hacia afuera**:
  - `PortadaController`: Consumo de `$ultimasPaginas`.
  - `SitemapController`: Inclusión de URLs en el sitemap XML.
  - `cabecera.blade.php`: Enlace de navegación en el menú «Extras».

## Configuración
No requiere variables de entorno adicionales. Utiliza el almacenamiento de Laravel (`Storage::disk('public')`) para la subida de portadas locales.

## Trampas conocidas
- **Directiva `@context` de Blade**: En plantillas Blade, escribir `"@context": "https://schema.org"` es interpretado por el compilador como la directiva interna de Laravel `@context`, arrojando `syntax error, unexpected end of file, expecting "elseif" or "else" or "endif"`. Se debe generar el JSON-LD desde un array en PHP con `json_encode($articleSchema)` o escapar el carácter arroba como `@@context`.
- **Determinismo en `scopeRecent()`**: Al crear múltiples registros secuenciales en tests, los timestamps `created_at` pueden coincidir al mismo segundo; `scopeRecent()` debe incluir `orderByDesc('id')` como orden secundario para garantizar ordenación determinista.

## Tests que lo cubren
- `services/portal/tests/Feature/CustomPagesTest.php`:
  - `test_listado_paginas_devuelve_200_y_cero_cookies`
  - `test_listado_paginas_muestra_estado_vacio`
  - `test_listado_paginas_muestra_tarjetas_horizontales_con_franja_y_keywords`
  - `test_detalle_pagina_devuelve_200_con_imagen_y_seo`
  - `test_detalle_pagina_inactiva_o_inexistente_devuelve_404`
  - `test_portada_muestra_hasta_4_paginas_con_boton_verde`
  - `test_sitemap_incluye_paginas_y_slugs_dinamicos`
  - `test_operador_puede_ver_listado_en_filament`
  - `test_slug_es_unico`

## Pendiente real
- [x] Módulo implementado y verificado con 100% de tests pasando.

---
> Creado: 2026-10-08 · Última revisión: 2026-10-08
