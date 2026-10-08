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

Navegación principal: Inicio · Configura tu nodo · Conecta tu gateway · Rankings · Alertas · Bots · Revisa tu nodo. Pie: El proyecto · Quién lo impulsa · Cómo se gestiona · Sugerencias · Firmware y apps · API · Aviso legal · Privacidad · Cookies · línea de autoría (`05-authorship.md`). Accesos discretos y no invasivos al buzón de sugerencias en pie, menú móvil, pie de portada y sección final de bots.

### Portada (orden fijo)

1. Cabecera común.
2. Presentación: `PROJECT_NAME` (H1) y una frase.
3. **Tres tarjetas** en este orden: MeshView (`https://meshview.${PROJECT_DOMAIN}`), PotatoMesh (`https://potato.${PROJECT_DOMAIN}`), Consumo y nodos en peligro (`/rankings`). Cada una: imagen 16:9, nombre, descripción de 2 líneas como máximo, toda la tarjeta es el enlace, misma pestaña. Fila de tres en escritorio, columna en móvil.
4. Mapa de Andalucía (`02-province-map.md`).
5. Configura tu nodo, resumen (`03-node-setup-guide.md`).
6. Sube los datos de tu nodo, resumen (`04-gateway-connection.md`).
7. Quién está detrás (`05-authorship.md`).
8. Aviso "no es un servicio de emergencias".
9. Pie común.

Bots, alertas y API no llevan tarjeta: van en navegación y pie.

### Tarjetas

- Datos en `config/proyecto.php` → `tarjetas` (título, descripción, URL construida con `PROJECT_DOMAIN`, imagen).
- Imágenes: ilustración propia por servicio (no capturas, que caducan con cada versión), WebP/AVIF < 40 KB, `alt` descriptivo, `width`/`height` fijos para no mover el diseño.
- Punto de estado opcional en MeshView y PotatoMesh: verde/rojo desde `estado_servicio` (lectura de la base `portal`, sin llamar a nada al pintar).

### Contenidos

- `resources/contenido/*.md` (un archivo por página, copiados de los borradores revisados de `pages/`). Renderizado con `league/commonmark` (GFM, sin HTML crudo), sustitución de `{VARIABLE}` desde `config/proyecto.php` antes de renderizar, caché por archivo y `filemtime`.
- Una variable desconocida o sin valor lanza excepción en pruebas (`ContenidoTest` recorre todos los archivos).
- Los datos dinámicos (`{total_andalucia}`…) no van en Markdown: los pinta la vista.

### SEO

- `sitemap.xml` generado dinámicamente mediante el paquete `spatie/laravel-sitemap` a partir de las rutas públicas registradas en la aplicación (excluyendo áreas privadas y rutas paramétricas como `/alertas/{id}` o `/revisa-tu-nodo/{id}`). Comando CLI `php artisan portal:sitemap` para exportación estática opcional. Archivos de directivas de rastreo: `robots.txt` y `robots.xml` (`Disallow: /admin`, `Disallow: /api/v1`, `Sitemap:`).
- Servidor web: la arquitectura opera bajo Nginx (en host y contenedor `serversideup/php:8.4-fpm-nginx`), por lo que `.htaccess` no aplica en producción. Las reglas de rewrite, proxies y bloqueo de archivos ocultos se gestionan directamente en la configuración de Nginx.

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

---
> Creado: 2026-10-07 · Última revisión: 2026-10-08
