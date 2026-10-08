# Módulo 15: Catálogo de Hardware Recomendado (`services/portal/`)

> Catálogo comunitario y dinámico de dispositivos, antenas y componentes probados para la red Meshtastic, con gestión multi-idioma (ES/EN), recorte de imágenes 1:1 en Filament y front interactivo en `/hardware`.

## Qué hace y qué NO hace
- **Qué hace**:
  - Permite a los operadores del portal gestionar categorías y artículos de hardware recomendado para la malla a través de recursos Filament en `/admin/hardware-categories` y `/admin/hardware-items`.
  - Soporta internacionalización (ES/EN/PT) en nombres y descripciones técnicas con fallback transparente a español.
  - Proporciona subida obligatoria de imagen con recorte y relación de aspecto cuadrada 1:1 en el disco público mediante el editor de imágenes de Filament.
  - Expone un catálogo público interactivo en `/hardware` con filtros tipo *pill* / *chip* por categoría (con contadores), buscador por texto libre sobre nombre y descripción, distintivos de productos recomendados/destacados y enlaces directos a tiendas y guías de montaje.
  - Informa a los usuarios del último precio conocido orientativo de cada dispositivo.
  - Cumple estrictamente la directiva de privacidad y cero cookies en el frontend público ([RN-06](../business-rules.md)).
- **Qué NO hace**:
  - No gestiona compras, carritos, stock ni pasarelas de pago.
  - No utiliza enlaces de afiliado comercial ni persigue comisiones de venta (las sugerencias son puramente técnicas y comunitarias).
  - No permite edición ni subidas por parte de visitantes no autenticados.

## Modelo de datos

### 1. `hardware_categories`
| Campo | Tipo | Restricciones / Defecto | Descripción |
|---|---|---|---|
| `id` | `bigint` | PK auto-incremental | Identificador de la categoría |
| `slug` | `string` | Único, indexado | Slug amigable para URL (`?categoria=...`) |
| `name` | `jsonb` | Requerido | Objeto JSON con nombres localizados (`{"es": "...", "en": "..."}`) |
| `description` | `jsonb` | Nullable | Objeto JSON con descripciones localizadas |
| `sort_order` | `integer` | Default `0`, indexado | Orden numérico de visualización |
| `is_active` | `boolean` | Default `true`, indexado | Indicador de publicación de la categoría |
| `timestamps` | `timestamp` | Requerido | `created_at` y `updated_at` |

### 2. `hardware_items`
| Campo | Tipo | Restricciones / Defecto | Descripción |
|---|---|---|---|
| `id` | `bigint` | PK auto-incremental | Identificador del artículo |
| `category_id` | `foreignId` | FK `hardware_categories`, cascade | Categoría a la que pertenece |
| `slug` | `string` | Único, indexado | Slug amigable para identificación |
| `name` | `jsonb` | Requerido | Nombre comercial / modelo (admite JSON o string) |
| `description` | `jsonb` | Requerido | Notas técnicas y comportamiento en malla (`{"es": "...", "en": "..."}`) |
| `image_path` | `string` | Requerido | Ruta relativa del archivo en el disco público de Laravel |
| `buy_url` | `string` | Requerido, URL válida | Enlace externo a distribuidor o tienda |
| `guide_url` | `string` | Nullable, URL válida | Enlace a guía de montaje, flasheo o documentación |
| `last_price` | `decimal(8,2)` | Nullable | Último precio conocido orientativo |
| `currency` | `string(3)` | Default `'EUR'` | Código de divisa ISO |
| `is_featured` | `boolean` | Default `false`, indexado | Destacado / recomendado en la interfaz |
| `is_active` | `boolean` | Default `true`, indexado | Visibilidad en el catálogo público |
| `sort_order` | `integer` | Default `0`, indexado | Prioridad de orden dentro de la lista |
| `timestamps` | `timestamp` | Requerido | `created_at` y `updated_at` |

## Flujos principales

1. **Consulta pública (`/hardware`)**:
   - El visitante entra en `/hardware`.
   - `HardwareController::index` recupera las categorías activas con `withCount(['items' => fn($q) => $q->active()])` ordenadas por `sort_order`.
   - Si se especifica el parámetro `?categoria={slug}`, se filtran los artículos de dicha categoría.
   - Si se especifica el parámetro `?q={termino}`, se ejecuta un filtro textual con `LOWER(CAST(name AS TEXT)) LIKE ?` y `LOWER(CAST(description AS TEXT)) LIKE ?`.
   - Se devuelven los artículos ordenados por `is_featured DESC`, `sort_order ASC`, `created_at DESC` para renderizar `paginas.hardware` con la plantilla `<x-layout>`.
   - La respuesta no emite `Set-Cookie` en cabeceras HTTP.

2. **Administración en el panel (`/admin`)**:
   - El operador técnico inicia sesión en `/admin`.
   - En el menú lateral accede al grupo **Catálogo** → **Categorías Hardware** o **Artículos Hardware**.
   - En el formulario de categorías, introduce los nombres en español e inglés; el slug se sugiere automáticamente desde el nombre en español.
   - En el formulario de artículos, selecciona la categoría asociada, redacta notas técnicas en pestañas (ES/EN) y sube una fotografía recortándola con la herramienta 1:1 integrada.
   - En las tablas dispone de interruptores en vivo (`ToggleColumn`) para publicar o despublicar inmediatamente artículos y categorías, así como reordenación visual arrastrable.

## Puntos de entrada

- **`GET /hardware`**:
  - Autenticación: No requerida.
  - Permisos: Público general.
  - Límites de tasa: Grupo `web` estándar. Cero cookies de sesión (RN-06).
- **`GET|POST|PUT|DELETE /admin/hardware-categories*`**:
  - Autenticación: Sí (`auth:web` en Filament).
  - Permisos: Operador activo registrado en `users`.
- **`GET|POST|PUT|DELETE /admin/hardware-items*`**:
  - Autenticación: Sí (`auth:web` en Filament).
  - Permisos: Operador activo registrado en `users`.

## Dependencias en ambos sentidos

- **Hacia adentro**:
  - `App\Models\HardwareCategory` y `App\Models\HardwareItem`.
  - Componentes Filament v5 (`FileUpload`, `Select`, `TextInput`, `Textarea`, `Tabs`, `Table`, `ImageColumn`, `ToggleColumn`).
  - Plantilla Blade `<x-layout>`, cabecera `<x-cabecera>` y pie `<x-pie>`.
  - Sistema visual de tokens semánticos en `resources/css/app.css` (`.tarjeta`, `.chip`, `.btn-primario`, etc.).
  - Traducciones en `lang/*.json` y archivos `lang/*/portal.php`, `lang/*/admin.php`.
- **Hacia afuera**:
  - Enlace en desplegable de Extras del navbar (`<x-cabecera>`).
  - Enlace en la columna institucional del pie de página (`<x-pie>`).
  - Rutas web del portal (`routes/web.php`).

## Configuración

| Variable | Valor por defecto | Efecto |
| :--- | :--- | :--- |
| `FILESYSTEM_DISK` | `local` (o `public`) | Disco de almacenamiento para imágenes de hardware |
| `APP_URL` | `http://localhost` | Base para generar URLs absolutas públicas de las imágenes |

## Trampas conocidas

- **Búsquedas LIKE sobre JSONB**: En PostgreSQL no se puede invocar directamente `LOWER(jsonb_column) LIKE ?` porque la función `LOWER` requiere tipo texto y PostgreSQL lanza `ERROR: function lower(jsonb) does not exist`. Se debe invocar `LOWER(CAST(name AS TEXT))` o `LOWER(name::text)`.
- **Relaciones Filament con modelos que tienen casts JSON**: Si un modelo almacena el nombre como array JSONB y Filament enlaza la relación (`category.name`) en un `Select` o `TextColumn`, PHP puede lanzar `Array to string conversion` si no se utiliza `getOptionLabelFromRecordUsing` o un mutador/accesor de cadena (`translated_name`).

## Tests que lo cubren

- `tests/Feature/HardwareCatalogoTest.php`:
  - `test_pagina_hardware_es_accesible_y_muestra_articulos_activos`
  - `test_filtro_por_categoria_funciona_correctamente`
  - `test_articulos_inactivos_no_se_muestran_en_el_frontend`
  - `test_admin_puede_acceder_a_recursos_de_hardware`
  - `test_busqueda_textual_filtra_por_nombre_y_descripcion`
  - `test_articulos_multidioma_se_traducen_segun_locale`

## Pendiente real

- [x] Migraciones `hardware_categories` y `hardware_items` con PostgreSQL y SQLite.
- [x] Modelos Eloquent con scopes, casts y accesores multi-idioma.
- [x] Recursos Filament completos con cropper 1:1 y ToggleColumns.
- [x] Controlador y vista pública Blade `/hardware` con filtros pills y tarjetas responsivas.
- [x] Traducciones ES / EN / PT en JSON y archivos PHP.
- [x] Tests de integración automatizados al 100%.

---
> Creado: 2026-10-08 · Última revisión: 2026-10-08
