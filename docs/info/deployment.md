# Despliegue

> Orden y pasos conceptuales para levantar una instancia completa. El detalle de cada pieza está en su documento (enlazado en cada fase). Procedimiento común (`deploy.sh`, actualización, secretos): [`infrastructure/04-operations.md`](infrastructure/04-operations.md). Mapa: [`architecture-map.md`](architecture-map.md).

## Requisitos previos (del operador de la instancia)

| Qué | Para qué | Cuándo hace falta |
|---|---|---|
| Servidor Debian 13 con IPv4 e IPv6 públicas (4 vCPU, 8 GB, 200 GB recomendados) | Todo | Fase 1 |
| Dominio con registros `PROJECT_DOMAIN` y `*.PROJECT_DOMAIN` hacia el servidor, sin proxy de CDN | Web, MQTT, certificados | Fase 1 |
| Clonar este repositorio en `/srv/repo` del servidor | Despliegue | Fase 1 |
| Bot de Telegram (token y nombre de usuario) | `bot-telegram` | Fase 7 |
| Aplicación y bot de Discord (token, id e invitación) | `bot-discord` | Fase 7 |
| `peers.json` con las instancias PotatoMesh que se quieran leer (fuera de git) y aviso a sus administradores | `sync-peers` | Fase 3 (opcional) |
| Sistema de copias propio | Fuera del proyecto; qué copiar en [`infrastructure/README.md`](infrastructure/README.md) §6 | Antes de abrir al público |

## Fases

| Fase | Qué se levanta | Documentación | Código | Listo cuando |
|---|---|---|---|---|
| 1 | Servidor, Docker, red `mesh`, PostgreSQL + TimescaleDB, Nginx y certbot nativos | [`infrastructure/`](infrastructure/README.md) | `infrastructure/` | HTTPS válido en los 4 hosts, PostgreSQL con bases y roles |
| 2 | Broker MQTT | [`mosquitto/`](mosquitto/README.md) | `integrations/mosquitto/` | Gateways con usuario propio publicando; ACL probadas |
| 3 | Visores: MeshView, PotatoMesh, `adaptador-potato`, `sync-peers` | [`meshview/`](meshview/README.md), [`potatomesh/`](potatomesh/README.md) | `integrations/meshview/`, `integrations/potatomesh/`, `services/adaptador-potato/`, `services/sync-peers/` | Los dos visores con datos de todos los gateways |
| 4 | Ingesta | [`ingesta/`](ingesta/README.md) | `services/ingesta/` | Vistas `api_*` con datos y flujo `decoded` publicando |
| 5 | Portal (web, API de estadísticas, panel) | [`portal/`](portal/README.md) | `services/portal/` | Portada con mapa real, páginas, panel con estado de servicios |
| 6 | Detector de alertas + endpoints de alertas del portal | [`detector-alertas/`](detector-alertas/README.md) | `services/detector-alertas/` | Reglas MVP emitiendo por el socket |
| 7 | Bots y webhooks + página de bots | [`bots-webhooks/`](bots-webhooks/README.md) | `services/bot-telegram/`, `services/bot-discord/`, `services/webhooks/` | Bots en grupos de prueba con filtros y comandos |
| 8 | Chat en directo | [`chat-ws/`](chat-ws/README.md) | `services/chat-ws/` | Suscripción por canal desde internet |
| 9 | Rankings | Vistas de rankings en [`ingesta/05-contract-views.md`](ingesta/05-contract-views.md) + [`portal/08-rankings-alerts.md`](portal/08-rankings-alerts.md) | `services/ingesta/`, `services/portal/` | Rankings de los cuatro periodos con datos reales |
| 10 | Ampliación | Reglas de ampliación del detector, funciones del panel | — | — |

Para pasar de fase: la pieza aparece en verde en el panel (desde la fase 5) y su documento refleja lo desplegado.

## Pasos comunes a cada pieza

1. Requisitos de la pieza (base de datos y rol, usuario MQTT) creados según su documento.
2. `/srv/<nombre>/.env` desde su `.env.example` (secretos solo ahí, 0600).
3. `deploy.sh <nombre>`: actualiza `/srv/repo`, copia la pieza a `/srv/<nombre>/`, construye (propias) o descarga (terceros) la imagen y levanta.
4. Comprobar `healthy`, sus escenarios de prueba y el panel.

## Dependencias entre piezas

| Pieza | Necesita antes |
|---|---|
| Mosquitto | Infraestructura |
| MeshView, PotatoMesh, adaptador-potato | Infraestructura, Mosquitto |
| sync-peers | PotatoMesh |
| ingesta | Infraestructura, Mosquitto |
| portal | Infraestructura (ingesta y detector son opcionales: sin ellos muestra "datos no disponibles") |
| detector-alertas | Mosquitto, ingesta |
| bots y webhooks | detector-alertas, portal (API) |
| chat-ws | Mosquitto, ingesta |

Cada pieza se puede volver a desplegar sola sin parar las demás.

---
> Creado: 2026-10-07 · Última revisión: 2026-10-07
