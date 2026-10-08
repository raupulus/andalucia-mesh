# 06.1 · Estructura y portada

> Mapa del sitio, plantilla común, contenidos institucionales, portada con las tres tarjetas, SEO y QR. Texto de cada página en `pages/` (`01-home.md` a `04-governance.md`).

## Objetivo

Que quien llega por el enlace o el QR entienda en 10 segundos qué es esto, vea la malla de Andalucía y llegue en un toque al servicio que busca; y que desde la portada, sin cambiar de página, sepa qué poner en su nodo, cómo subir datos y quién está detrás.

## Especificación

### Mapa del sitio

| Página | Ruta | Controlador | Texto |
|---|---|---|---|
| Inicio | `/` | `PortadaController` | `pages/01-home.md` |
| El proyecto | `/proyecto` | `PaginaController` (Markdown) | `pages/02-project.md` |
| Quién lo impulsa | `/quien-lo-impulsa` | `PaginaController` + bloque de autoría | `pages/03-who.md` |
| Cómo se gestiona | `/como-se-gestiona` | `PaginaController` | `pages/04-governance.md` |
| Sugerencias | `/sugerencias` | `SuggestionController` | Buzón ciudadano con Turnstile y categorías |
| Resto | Ver `README.md` §4.1 | Su módulo | Su documento en `pages/` |

Navegación principal: Inicio · Configura tu nodo · MQTT (`/conecta-tu-gateway`) · Rankings · Routers (`/routers`) · Alertas · Bots · Desplegable **Extras** (`/configurador`, `/revisa-tu-nodo`, `/hardware`, `/paginas` ["Páginas"], `/api` ["API/Websockets"], `/sugerencias`, `/faq`). Pie: El proyecto · Quién lo impulsa · Cómo se gestiona · Sugerencias · Firmware y apps · API · Aviso legal · Privacidad · Cookies · tarjeta rectangular de código fuente con enlaces a GitLab (principal) y GitHub (mirror) · línea de autoría (`05-authorship.md`). Por defecto la interfaz se presenta en modo oscuro (`data-theme="dark"`), respetando el modo claro solo cuando el sistema operativo tiene explícitamente activada la preferencia `prefers-color-scheme: light` o el usuario seleccionó un tema en `localStorage`.

### Portada (orden fijo)

1. Cabecera común con logotipo, selector de idioma, navegación y desplegable de Extras.
2. Presentación institucional: `.portada-hero` con disposición horizontal (texto a la izquierda, logotipo de 130px a la derecha) en escritorio, y disposición vertical equilibrada (`flex-direction: column-reverse`, logotipo centrado arriba, título H1 centrado con escala fluida `clamp(1.85rem, 6vw, 2.35rem)` y texto lead centrado) en pantallas móviles (<= 768px).
3. **Tres tarjetas** en este orden: MeshView (`https://meshview.${PROJECT_DOMAIN}`), PotatoMesh (`https://potato.${PROJECT_DOMAIN}`), Consumo y nodos en peligro (`/rankings`). Cada una: imagen 16:9, nombre, descripción de 2 líneas como máximo, toda la tarjeta es el enlace. MeshView y PotatoMesh abren en nueva pestaña (`target="_blank" rel="noopener noreferrer"`) para no sacar al usuario de la web del portal; Rankings abre en la misma pestaña. Fila de tres en escritorio, columna en móvil.
4. Mapa de Andalucía (`02-province-map.md`) con selector temporal (24h / 7d) y tabla accesible de nodos y saturación.
5. **Tarjeta horizontal comunitaria: ¡Envía tu Sugerencia!**, con ilustración corporativa en formato 1:1 a la izquierda, descripción de ideas y botón verde del diseño centrado enlazando a `/sugerencias`.
6. Configura tu nodo, resumen (`03-node-setup-guide.md`).
7. Sube los datos de tu nodo, resumen (`04-gateway-connection.md`).
8. **Tarjeta horizontal de diagnóstico: Revisa tu nodo**, con ilustración de lupa sobre PCB en formato 1:1 a la izquierda, texto descriptivo y botón verde centrado enlazando a `/revisa-tu-nodo`.
9. **Últimas Páginas y Divulgación** (`16-custom-pages.md`): bloque con las 4 últimas páginas activas en tarjetas compactas (2 columnas en escritorio, 1 en móvil) con imagen a la izquierda, franja de Andalucía, badges verdes de keywords y botón verde centrado enlazando a `/paginas`.
10. Quién está detrás (`05-authorship.md`): tarjeta vertical centrada y oxigenada con el logotipo de `raupulus.dev` enlazado a la web personal y botón a `/quien-lo-impulsa`.
11. Aviso "no es un servicio de emergencias".
12. Pie común.

Bots, alertas y API no llevan tarjeta: van en navegación y pie.

### Tarjetas

- Datos en `config/proyecto.php` → `tarjetas` (título, descripción, URL construida con `PROJECT_DOMAIN`, imagen).
- Imágenes: ilustración propia por servicio (no capturas, que caducan con cada versión), WebP/AVIF < 40 KB, `alt` descriptivo, `width`/`height` fijos para no mover el diseño.
- Punto de estado opcional en MeshView y PotatoMesh: verde/rojo desde `estado_servicio` (lectura de la base `portal`, sin llamar a nada al pintar).

### Contenidos

- `resources/contenido/*.md` (un archivo por página, copiados de los borradores revisados de `pages/`). Renderizado con `league/commonmark` (GFM, sin HTML crudo), sustitución de `{VARIABLE}` desde `config/proyecto.php` antes de renderizar, caché por archivo y `filemtime`.
- Una variable desconocida o sin valor lanza excepción en pruebas (`ContenidoTest` recorre todos los archivos).
- Los datos dinámicos (`{total_andalucia}`…) no van en Markdown: los pinta la vista.

### SEO y Accesibilidad W3C

- `sitemap.xml` generado dinámicamente mediante el paquete `spatie/laravel-sitemap` a partir de las rutas públicas registradas en la aplicación (incluyendo `/paginas` y los slugs dinámicos activos de `custom_pages`, y excluyendo áreas privadas y rutas paramétricas como `/alertas/{id}` o `/revisa-tu-nodo/{id}`). Comando CLI `php artisan portal:sitemap` para exportación estática opcional. Archivos de directivas de rastreo: `robots.txt` y `robots.xml` (`Disallow: /admin`, `Disallow: /api/v1`, `Sitemap:`), acompañados de directivas avanzadas en cabecera `<meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">`.
- Servidor web: la arquitectura opera bajo Nginx (en host y contenedor `serversideup/php:8.5-fpm-nginx`), por lo que `.htaccess` no aplica en producción. Las reglas de rewrite, proxies y bloqueo de archivos ocultos se gestionan directamente en la configuración de Nginx.
- Etiquetas `hreflang` multilingües dinámicas para `es`, `en`, `pt` y `x-default` enlazando a las variantes idiomáticas correspondientes.
- Metadatos sociales completos: Open Graph con `og:locale`, `og:locale:alternate` (`es_ES`, `pt_PT`, `en_GB`), dimensiones `1200x630`, catálogo completo de 21 imágenes temáticas exclusivas y optimizadas por sección en formato WebP (`public/img/og/*.webp`, peso < 120 KB), MIME type dinámico (`image/webp` / `image/png`) y `og:image:alt`; Twitter Card en formato `summary_large_image` con atribución de autoría `twitter:site` y `twitter:creator` (`@raupulus`).
- Datos estructurados Schema.org (JSON-LD): entidad global `WebSite` y `Organization` en `x-layout`, esquema semántico dinámico `FAQPage` con preguntas y respuestas (`Question`, `Answer`) en `/faq`, y esquema `Article` en cada página individual `/paginas/{slug}`.
- Conformidad W3C y WCAG 2.1 AAA: `dir="ltr"` en el elemento raíz `<html>`, atributos dimensionales `width` y `height` en imágenes para prevención de Cumulative Layout Shift (CLS), y atributos `alt=""` con `aria-hidden="true"` en logotipos e iconos decorativos adyacentes a texto para evitar duplicidad de lectura en lectores de pantalla (criterio WCAG H67 / Fallo F39).
- `lang="es|en|pt"` dinámico según idioma activo (RN-48), `meta theme-color` según modo. Selector de idiomas accesible en navbar con solo icono redondo de bandera (Andalucía para `es`, Portugal para `pt`, Reino Unido para `en`) que despliega el menú en orden estricto (español/portugués/inglés) y cero cookies (RN-06).


### QR

- `/qr.svg` y `/qr.pdf` (A6, para pegatinas y carteles en repetidores): QR estático a `https://${PROJECT_DOMAIN}` sin parámetros, logo en el centro con corrección de errores alta, URL legible debajo. Generado en el despliegue (`php artisan portal:qr`) y servido como archivo estático.

## Contratos propios

- `config/proyecto.php` → `tarjetas`, `navegacion`, `seo.imagen_por_defecto`.
- Componentes Blade: `x-tarjeta-servicio`, `x-cabecera`, `x-pie`, `x-aviso-emergencias` (estilos de `DESIGN.md` §9).

## Unidades de trabajo

- **UT-06.1.1 — Plantilla y navegación.** Layout, cabecera, pie, menú móvil accesible, conmutador de tema. *Aceptación:* navegación completa por teclado; menú móvil con `aria-expanded`.
- **UT-06.1.2 — Contenidos Markdown.** Renderizador, variables, caché, prueba de variables. *Aceptación:* cambiar `PROJECT_NAME` cambia todos los textos tras limpiar caché.
- **UT-06.1.3 — Portada.** Orden fijo, tarjetas, aviso. *Bordes:* descripción larga (recorte a 2 líneas a 360 px). *Aceptación:* las tres tarjetas en orden y cada una lleva a su servicio.
- **UT-06.1.4 — Páginas institucionales.** `/proyecto`, `/quien-lo-impulsa`, `/como-se-gestiona`. *Aceptación:* texto de `pages/` sin cambios de contenido.
- **UT-06.1.5 — SEO, sitemap y robots.** *Aceptación:* validador de Open Graph sin errores; `sitemap.xml` válido.
- **UT-06.1.6 — QR.** *Aceptación:* el QR impreso a 3 cm se lee con un móvil y abre la portada.

## Escenarios de prueba

1. **Dado** un móvil de 360 px, **cuando** se abre la portada, **entonces** las tarjetas van en columna, ninguna descripción pasa de 2 líneas y cada tarjeta entera es pulsable.
2. **Dado** el contenido `overview.md` con `{PROJECT_DOMAIN}`, **cuando** se renderiza, **entonces** sale `mesh.example.org`; con `{NO_EXISTE}`, la prueba falla.
3. **Dado** MeshView caído según `estado_servicio`, **cuando** se pinta la portada, **entonces** su tarjeta muestra el punto rojo con texto oculto "servicio no disponible" y sigue enlazando.
4. **Dado** el enlace de la portada pegado en Telegram, **cuando** se genera la vista previa, **entonces** muestra título, descripción e imagen propias.
5. **Dado** la portada, **cuando** se inspeccionan las peticiones, **entonces** ninguna sale a otro dominio y no hay `Set-Cookie`.
6. **Dado** un móvil <= 768 px, **cuando** se visualiza el hero, **entonces** se presenta centrado con el logotipo arriba y sin desalineaciones.

---
> Creado: 2026-10-07 · Última revisión: 2026-10-09
