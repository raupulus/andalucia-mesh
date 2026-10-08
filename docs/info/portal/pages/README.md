# Páginas del portal

> Documento de contenido de cada página pública del portal: qué lleva, de dónde salen sus datos y el borrador de su texto, para que el desarrollo no invente nada. Módulos de referencia en `../` (`portal`), `../11-public-api.md` y `../../bots-webhooks/`.

| # | Página | URL | Documento | Estado |
|---|---|---|---|---|
| 01 | Inicio | `/` | [`01-home.md`](01-home.md) | Borrador, pendiente de revisión |
| 02 | El proyecto | `/proyecto` | [`02-project.md`](02-project.md) | Borrador, pendiente de revisión |
| 03 | Quién lo impulsa | `/quien-lo-impulsa` | [`03-who.md`](03-who.md) | Borrador, pendiente de revisión |
| 04 | Cómo se gestiona | `/como-se-gestiona` | [`04-governance.md`](04-governance.md) | Borrador, pendiente de revisión |
| 05 | Configura tu nodo | `/configura-tu-nodo` | [`05-node-setup.md`](05-node-setup.md) | Borrador, pendiente de revisión |
| 06 | Conecta tu gateway | `/conecta-tu-gateway` | [`06-gateway.md`](06-gateway.md) | Borrador, pendiente de revisión |
| 07 | Bots | `/bots` | [`07-bots.md`](07-bots.md) | Borrador, pendiente de revisión |
| 08 | Rankings | `/rankings` | [`08-rankings.md`](08-rankings.md) | Borrador, pendiente de revisión |
| 09 | Alertas | `/alertas` y `/alertas/{id}` | [`09-alerts.md`](09-alerts.md) | Borrador, pendiente de revisión |
| 10 | API | `/api` | [`10-api.md`](10-api.md) | Borrador, pendiente de revisión |
| 11 | Aviso legal | `/legal/aviso-legal` | [`11-legal-notice.md`](11-legal-notice.md) | Borrador, pendiente de revisión |
| 12 | Privacidad | `/legal/privacidad` | [`12-privacy.md`](12-privacy.md) | Borrador, pendiente de revisión |
| 13 | Cookies | `/legal/cookies` | [`13-cookies.md`](13-cookies.md) | Borrador, pendiente de revisión |
| Revisa tu nodo | `/revisa-tu-nodo` | [`14-node-check.md`](14-node-check.md) | Borrador, pendiente de revisión |
| Firmware y apps | `/firmware` | [`15-firmware.md`](15-firmware.md) | Borrador, pendiente de revisión |
| 16 | FAQ | `/faq` | [`16-faq.md`](16-faq.md) | Implementado |

El panel `/admin` no está aquí: es privado y se describe en `../14-operator-panel.md`.

## Reglas del directorio

1. **Un archivo por página pública**, siempre con la misma estructura: SEO, estructura, borrador del texto, datos dinámicos y configuración y, solo si hace falta, lo pendiente de revisar. Cabecera y pie son comunes a todas las páginas y se definen una sola vez en `01-home.md`.
2. **Textos con variables entre llaves.** En MAYÚSCULAS, variables de configuración (`{PROJECT_NAME}`, `{PROJECT_DOMAIN}`, `{PROJECT_CONTACT}`, `{LORA_…}`, `{PRIMARY_CHANNEL}`…); en minúsculas, datos de la API (`{total_andalucia}`), con su endpoint en "Datos dinámicos". Nada configurable se escribe a mano en vistas ni Markdown. Los datos de autoría se escriben en claro para revisarlos, pero salen de `config/autoria.php`. Las longitudes de SEO se cuentan con el nombre actual ("Sur Nodos en Mallas").
3. **Lo que cambie en los módulos se refleja aquí** en el mismo cambio (portal, API, bots, detector, broker). Si dos documentos chocan, manda `../../integration.md` y después el módulo.
4. Los textos publicados viven en `resources/contenido/*.md` del portal; estos documentos son su fuente de revisión. Ningún texto público usa la marca del firmware ni nombra comunidades ajenas: se habla de "la malla", "los nodos", "el firmware" y "la app del nodo".

---
> Creado: 2026-10-07 · Última revisión: 2026-10-08
