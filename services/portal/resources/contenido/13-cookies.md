# 13 · Cookies

> `/legal/cookies` · Explicar que las páginas públicas no usan cookies, qué se guarda en el navegador y qué cookies técnicas usa el panel privado · `../10-legal-privacy.md`

## SEO

- **Título:** `Política de cookies · {PROJECT_NAME}` (41 caracteres)
- **Descripción:** `Las páginas públicas de {PROJECT_NAME} no usan cookies. Solo el panel privado de administración usa cookies técnicas de sesión.` (132)

## Estructura

| # | Sección | Componente (`DESIGN.md`) |
|---|---|---|
| 1 | H1, fecha y resumen | Tipografía H1 + texto secundario + Texto |
| 2 | Qué guardamos en tu navegador | Texto corrido |
| 3 | Panel de administración | Tablas (cookie · para qué · duración) |
| 4 | PotatoMesh y MeshView | Texto corrido |
| 5 | Analítica | Texto corrido |
| 6 | Cómo borrarlo | Texto corrido |

Sin banner de cookies: no hace falta (RF-PO-3).

## Borrador del texto

> **Borrador orientativo.** Este texto no sustituye la revisión de un profesional del derecho. Revisarlo antes de publicar.

Cada `###` es un H2 de la página.

**H1:** Política de cookies

Última actualización: 8 de octubre de 2026

Las páginas públicas de este portal no instalan ninguna cookie ni usan rastreadores. Por eso no verás ningún aviso de cookies.

### Qué guardamos en tu navegador

Solo tu preferencia de modo claro u oscuro, si la cambias con el botón de la cabecera. Se guarda en el almacenamiento local de tu navegador (`localStorage` con la clave `snm_theme`), no es una cookie, no se envía a nuestro servidor y no sirve para identificarte. Si no la modificas, la web adopta de forma automática el tema de tu sistema operativo.

### Panel de administración

La zona `/admin` es privada y está reservada en exclusiva para los administradores y operadores del proyecto. Solo si accedes a dicha zona o inicias sesión en ella, se utilizan dos cookies técnicas esenciales para el funcionamiento seguro de la sesión, plenamente exentas de consentimiento previo:

| Cookie | Para qué | Duración |
|---|---|---|
| `andalucia_mesh_session` | Mantener la sesión técnica de autenticación del operador | 120 minutos de inactividad o hasta cerrar el navegador |
| `XSRF-TOKEN` | Evitar que otra web envíe formularios no autorizados en tu nombre (protección anti-CSRF) | Sesión activa (máx. 120 minutos) |

Si eres un usuario visitante de la web pública, estas cookies nunca se instalan en tu navegador.

### PotatoMesh y MeshView

Son servicios del proyecto basados en software libre de sus respectivos autores, desplegados en sus propios subdominios. No instalan cookies publicitarias ni de seguimiento. 

PotatoMesh utiliza tecnologías de almacenamiento local del navegador (`IndexedDB` y `localStorage`) para guardar una copia de trabajo de los paquetes y nodos de la malla con el fin de agilizar la carga en dispositivos móviles. Sus mapas se descargan desde servidores de cartografía de terceros (OpenStreetMap y CARTO), que reciben tu dirección IP técnica para poder entregarte las imágenes del mapa. Más detalle en la [política de privacidad](/legal/privacidad).

### Analítica

No utilizamos Google Analytics, ni Meta Pixel, ni ningún servicio externo de analítica o rastreo. Si en el futuro se incorporasen métricas agregadas de uso, se harían mediante soluciones autoalojadas y respetuosas con la privacidad sin cookies; si alguna requiriese consentimiento previo, se te solicitaría con antelación expresa.

### Cómo borrarlo

Puedes borrar las cookies y el almacenamiento local en cualquier instante desde la configuración de tu navegador (habitualmente en la sección de Privacidad y Seguridad o Datos de Sitios). Si borras la preferencia de modo, la web volverá a usar el tema por defecto de tu sistema.

## Datos dinámicos y configuración

| Dato | Origen |
|---|---|
| Comportamiento sin cookies | Rutas públicas en un grupo sin sesión ni CSRF (`../README.md`, "Cookies") |
| Preferencia de modo | Almacenamiento local, según `../../DESIGN.md` §4 |
| Texto | `resources/contenido/legal/cookies.md` |

## Supuestos aplicados

- Se nombran OpenStreetMap y CARTO (mismo criterio que en `12-privacy.md`).
- Cookies técnicas de sesión identificadas exactamente con la configuración de Laravel en `/admin`.

---
> Creado: 2026-10-07 · Última revisión: 2026-10-08
