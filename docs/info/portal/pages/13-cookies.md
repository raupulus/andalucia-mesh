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

Última actualización: [fecha de publicación]

Las páginas públicas de este portal no instalan ninguna cookie ni usan rastreadores. Por eso no verás ningún aviso de cookies.

### Qué guardamos en tu navegador

Solo tu preferencia de modo claro u oscuro, si la cambias con el botón de la cabecera. Se guarda en el almacenamiento local de tu navegador, no es una cookie, no se envía a nuestro servidor y no sirve para identificarte.

### Panel de administración

La zona `/admin` es privada y solo la usan los administradores del proyecto. Usa dos cookies técnicas, necesarias para iniciar sesión de forma segura y exentas de consentimiento:

| Cookie | Para qué | Duración |
|---|---|---|
| Sesión | Mantener la sesión iniciada | [completar en desarrollo] |
| Protección CSRF | Evitar que otra web envíe formularios en tu nombre | [completar en desarrollo] |

Si no eres administrador, nunca se instalan en tu navegador.

### PotatoMesh y MeshView

Son servicios del proyecto con el software de sus autores, en sus propios subdominios. Sus mapas se descargan de servidores de cartografía de terceros (OpenStreetMap y CARTO), que reciben tu dirección IP. PotatoMesh, además, guarda en tu navegador una copia de los datos de la malla para cargar más rápido. Más detalle en la [política de privacidad](/legal/privacidad).

### Analítica

No hay. Si algún día se añade, será sin cookies y alojada en nuestro propio servidor; si eso no fuera posible, te pediríamos permiso antes.

### Cómo borrarlo

Puedes borrar las cookies y el almacenamiento local desde la configuración de tu navegador. Si borras la preferencia de modo, la web volverá a usar la de tu sistema.

## Datos dinámicos y configuración

| Dato | Origen |
|---|---|
| Comportamiento sin cookies | Rutas públicas en un grupo sin sesión ni CSRF (`../README.md`, "Cookies") |
| Preferencia de modo | Almacenamiento local, según `../../DESIGN.md` §4 |
| Texto | `resources/contenido/legal/cookies.md` |

Notas para desarrollo:

- Completar el nombre exacto y la duración de las cookies de `/admin` según la configuración de sesión del portal.
- Comprobar en las versiones fijadas de PotatoMesh y MeshView qué guardan en el navegador (cookies, almacenamiento local, IndexedDB) y reflejarlo aquí. PotatoMesh guarda una copia de datos en IndexedDB.
- Criterio de aceptación: las páginas públicas no depositan cookies (comprobado con las herramientas del navegador); `/admin`, solo cookies técnicas.

## Supuestos aplicados

- Se nombran OpenStreetMap y CARTO (mismo criterio que en `12-privacy.md`).

---
> Creado: 2026-10-07 · Última revisión: 2026-10-07
