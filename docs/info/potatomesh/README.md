# 04 · PotatoMesh

> Visor de comunidad (mapa, chat de canales, nodos, telemetría y trazas, en vivo) en `potato.${PROJECT_DOMAIN}`, alimentado por dos servicios propios: `adaptador-potato` (MQTT → API) y `sync-peers` (instancias vecinas → API y Mosquitto/ingesta). **Tipo:** comunidad + propio · **Fase:** 3 · **Complejidad:** baja-media · **Monorepo:** `integrations/potatomesh/`, `services/adaptador-potato/`, `services/sync-peers/`

## 1. Contexto

- **Punto de partida:** PotatoMesh es un visor de comunidad maduro, pero **no lee MQTT** y su imagen se suele desplegar con `latest` y parches de frontend. Aquí se usa con versión fijada, sin tocar sus archivos y alimentado por dos servicios propios.
- **Objetivo:** visor de comunidad endurecido, con chat público visible, sin parches y actualizable cambiando solo la etiqueta de la imagen.
- **Hallazgo clave:** PotatoMesh **no lee MQTT**. Solo acepta datos por `POST` autenticado a su API. Por eso existe `adaptador-potato`.
- **Consumidores:** el público por web; el portal enlaza a `potato.${PROJECT_DOMAIN}`; el panel de operadores lee la salud (`../portal/14-operator-panel.md`).

## 2. Alcance

**Incluye:** contenedor web de PotatoMesh con versión fijada, configuración, páginas Markdown propias, plantilla de layout con badge de retención (CUST-04), limpieza periódica (nodos 15d, trazas 5d), y los dos servicios propios:

| Módulo | Archivo | Qué es | Complejidad |
|---|---|---|---|
| 04.1 | [`adaptador-potato.md`](adaptador-potato.md) | `msh/EU_868/#` → descifrado → deduplicación → `POST` por lotes | Baja-media |
| 04.2 | [`sync-peers.md`](sync-peers.md) | Lectura incremental de las APIs públicas de otras instancias PotatoMesh configuradas → `POST` a PotatoMesh y publicación en Mosquitto para ingesta | Baja |

**Fuera de esta entrega:** federación nativa (`FEDERATION=0`), ingestor oficial con radio, puente Matrix, cambios en el código de PotatoMesh, parches de frontend.

## 3. Stack y versiones

| Componente | Versión | Notas |
|---|---|---|
| PotatoMesh web | `ghcr.io/l5yth/potato-mesh-web-linux-amd64:0.7.5` | Ruby (Sinatra + Puma), un único proceso; Apache-2.0. Nunca `latest` (la v0.7.0 rompió la API) |
| Base de datos | SQLite (`mesh.db`) | Motor propio de PotatoMesh; no admite PostgreSQL |
| Servicios propios | Python 3.13 sobre `python:3.13-slim-trixie` (fijar parche) | `uv` + `uv.lock`; `aiomqtt`, `meshtastic` (protobufs), `cryptography`, `httpx`, `asyncpg` (solo `sync-peers`), `aiohttp` (`/health`); versiones fijadas al crear |

## 4. Contratos

### 4.1 API de PotatoMesh (interna)

- Base: `http://potatomesh:41447` en la red `mesh`. Escritura con `Authorization: Bearer ${POTATOMESH_API_TOKEN}` (un único token compartido), solo desde `adaptador-potato` y `sync-peers`.
- `POST` usados: `/api/nodes`, `/api/positions`, `/api/telemetry`, `/api/messages`, `/api/traces`, `/api/neighbors`, `/api/waypoints`. Campos en camelCase (desde v0.7.0). El mapeo exacto está en `adaptador-potato.md` §Contratos.
- `GET` (también los públicos de los peers): admiten `?limit=` y `?since=`; las colecciones masivas `?before=`. Las lecturas masivas devuelven como máximo 7 días y ninguna consulta pasa de 28 días.
- Salud: `GET /version` (no hay endpoint de salud). Métricas: `GET /metrics` (no se publica fuera).
- Un nodo con 🛑 en su nombre corto o largo desaparece de las vistas públicas (mecanismo propio de PotatoMesh; el proyecto no añade exclusiones).

### 4.2 Web pública

- `https://potato.${PROJECT_DOMAIN}` por Nginx (certificado comodín, `snm-potatomesh.conf`). Rutas: `/`, `/nodes/:id`, `/pages/:slug`, `/api/*` (lectura pública, incluida `/api/events` SSE de PotatoMesh), `/robots.txt`, `/sitemap.xml`.

### 4.3 MQTT (lo usan el adaptador y sync-peers)

Usuario `svc-potato`, lectura `msh/EU_868/#` (adaptador) y escritura `snm/v1/peer/#` (sync-peers) en `mosquitto:1884`. Solo canales de `ALLOWED_CHANNELS` (el broker ya filtra; el adaptador comprueba otra vez).

## 5. Configuración

`potatomesh` (`/srv/potatomesh/.env`, carga antes `/srv/comun/.env`):

| Variable | Valor o ejemplo | Origen | Secreto |
|---|---|---|---|
| `API_TOKEN` | `${POTATOMESH_API_TOKEN}` | Propia | Sí |
| `INSTANCE_DOMAIN` | `https://potato.${PROJECT_DOMAIN}` (obligatoria: sin ella usa `Host`, riesgo de envenenamiento de caché) | Propia | No |
| `SITE_NAME` | `${PROJECT_NAME}` | Común | No |
| `MESHTASTIC_PRESET` / `MESHTASTIC_FREQ` | `#SFNarrow` / `868MHz` | Común | No |
| `MAP_CENTER` / `MAP_ZOOM` | `36.48,-5.85` / `9` | Común | No |
| `MAX_DISTANCE` | `600` | Propia | No |
| `MIN_THREADS` / `MAX_THREADS` | `32` / `500` | Propia: holgura para 100 clientes SSE | No |
| `EVENTS` | `1` | Propia | No |
| `FEDERATION` | `0` | Propia | No |
| `PRIVATE` | `0` (chat visible e indexable) | Propia | No |
| `CONTACT_LINK` | Enlace de contacto de la instancia | Propia | No |
| `PAGES_DIR` | `/app/pages` | Propia | No |
| `APP_VERSION`, `FREQUENCY`, `CHANNEL` | **No se definen** (obsoletas o solo útiles para parches) | — | — |

Host (`/srv/potatomesh/.env`): `POTATOMESH_SQLITE=/srv/potatomesh/datos/mesh.db` (lo usa la limpieza diaria y el sistema de copias del operador).

Variables de los dos servicios propios: en sus módulos.

## 6. Datos

| Dato | Dónde | Retención |
|---|---|---|
| `mesh.db` (nodos, mensajes, posiciones, telemetría, trazas, vecinos, waypoints) | Bind mount `/srv/potatomesh/datos/` → directorio de datos de la imagen (`$XDG_DATA_HOME/potato-mesh`) | **Trazas y saltos: 5 días**; **Nodos, chat, waypoints, posiciones, telemetría y vecinos: 15 días** con limpieza propia diaria a las 03:47 h (UT-04.4); la purga nativa de PotatoMesh (365 días, fija en su código) queda como red de seguridad |
| Clave de instancia (`keyfile`) | `/srv/potatomesh/config/` → `$XDG_CONFIG_HOME/potato-mesh` | Permanente |
| Páginas | `integrations/potatomesh/pages/` montado en `/app/pages:ro` | En git |
| Layout con badge de retención | `integrations/potatomesh/views/layouts/app.erb` montado en `/app/views/layouts/app.erb:ro` | En git (CUST-04) |
| Cursores de peers | PostgreSQL `peersync` (`sync-peers.md`) | Permanente |

Páginas propias (Markdown, `<orden>-<slug>.md`, sin marcas de terceros salvo las permitidas):

- `1-acerca.md`: qué es la instancia y enlace al portal `https://${PROJECT_DOMAIN}`.
- `5-normas.md`: buenas prácticas de la malla (resumen de `../portal/03-node-setup-guide.md`).
- `9-legal.md`: enlaces al aviso legal y la privacidad del portal; menciona que los mosaicos del mapa se cargan de OSM HOT y CARTO.

## 7. Unidades de trabajo

- **UT-04.1 — Integración PotatoMesh.** `integrations/potatomesh/`: `compose.yaml`, `.env.example` sin valores secretos, `pages/`, `views/layouts/app.erb` con badge de retención en navbar (CUST-04), `README.md` con versión fijada, enlace al repositorio y notas de actualización. *Aceptación:* `docker compose config` válido con `check-compose.sh`.
- **UT-04.2 — Publicación.** Sitio de Nginx y comprobación de SSE sin búfer (`proxy_buffering off`). *Aceptación:* `curl -sN https://potato.${PROJECT_DOMAIN}/api/events` recibe latidos cada 15 s.
- **UT-04.3 — Datos para copias.** `POTATOMESH_SQLITE` documentado para que el sistema de copias del operador haga `sqlite3 .backup` en caliente (fuera del proyecto). *Aceptación:* una copia hecha con `.backup` arranca en un contenedor aparte.
- **UT-04.4 — Limpieza periódica (Nodos 15d, Trazas 5d).** `integrations/potatomesh/limpieza.sql` + `snm-potato-limpieza.timer` en el host (diario 03:47 h): `sqlite3 -cmd '.timeout 30000' "$POTATOMESH_SQLITE" < limpieza.sql` borra trazas de más de 5 días, y nodos inactivos, chat, waypoints, posiciones, telemetría y vecinos de más de 15 días usando marcas de tiempo UNIX enteras con `unixepoch()` (`TR-12`); `PRAGMA wal_checkpoint(TRUNCATE)` al final, sin `VACUUM` (bloquea). Los nombres de tablas y columnas son los del esquema de la versión fijada: se revisan en cada actualización. *Aceptación:* tras la ejecución no hay filas fuera de la ventana y PotatoMesh sigue respondiendo durante la limpieza.
- Módulos: UT-04.1.x (adaptador) y UT-04.2.x (sync-peers) en sus archivos.

## 8. Despliegue

| Contenedor | Imagen | Redes | Volúmenes | Puertos | Publicación | Healthcheck |
|---|---|---|---|---|---|---|
| `potatomesh` | `ghcr.io/l5yth/potato-mesh-web-linux-amd64:0.7.5` | `mesh` | `/srv/potatomesh/datos`, `/srv/potatomesh/config`, `./pages:/app/pages:ro`, `./views/layouts/app.erb:/app/views/layouts/app.erb:ro` | `127.0.0.1:41447:41447` | Nginx (`snm-potatomesh.conf`) | `GET http://127.0.0.1:41447/version` cada 30 s |
| `adaptador-potato` | `snm-adaptador-potato:<versión>` | `mesh` | — | 8080 solo en `mesh` | No se publica | `GET /health` |
| `sync-peers` | `snm-sync-peers:<versión>` | `mesh` | `./peers.json:/app/peers.json:ro` | 8080 solo en `mesh` | No se publica | `GET /health` |

Sitio de Nginx de `potatomesh`: `../infrastructure/03-nginx-dns.md` (SSE en `/api/events` sin búfer).

Límites: `potatomesh` 768 MB; `adaptador-potato` y `sync-peers` 256 MB cada uno. El healthcheck usa la herramienta HTTP que traiga la imagen (comprobar `curl`/`wget` en 0.7.5; si no hay, healthcheck con `ruby -e` y `Net::HTTP`).

Pasos, en orden:

1. Requisitos: `infrastructure` (red `mesh`, Nginx con `snm-potatomesh.conf`, base `peersync`) y `mosquitto` (usuario `svc-potato`).
2. `/srv/potatomesh/`: `compose.yaml`, `.env`, `pages/`, `datos/` (vacío o con una `mesh.db` previa), `config/`.
3. `docker compose up -d` de `potatomesh`; comprobar `/version` y la web.
4. Desplegar `adaptador-potato`; comprobar que entran nodos nuevos en < 1 min.
5. Desplegar `sync-peers` con el `peers.json` de la instancia (fuera de git); avisar antes a los administradores de las instancias que se van a leer.

Actualización: cambiar la etiqueta, `docker compose pull && up -d`; antes, leer el CHANGELOG y pasar los tests de contrato de los dos servicios propios contra la versión nueva.

Copia: diaria con `.backup`, etiqueta `potato`, 7 diarias (`../infrastructure/04-operations.md`).

## 9. Definición de hecho

- [x] `potato.${PROJECT_DOMAIN}` sirve PotatoMesh 0.7.5 por HTTPS con `SITE_NAME`, preset `#SFNarrow` y `868MHz`.
- [x] Ningún archivo del contenedor sustituido; actualizar = cambiar la etiqueta.
- [x] Federación desactivada (`/api/instances` sin anuncios propios).
- [x] El chat muestra solo los 14 canales y una pestaña SFNarrow.
- [x] SSE en vivo a través de Nginx.
- [ ] Copia diaria verificada y restauración probada.
- [x] Limpieza periódica activa con timer systemd (`snm-potato-limpieza.timer` a las 03:47 h): nodos a 15 días, trazas a 5 días; `mesh.db` estable.
- [x] Los dos servicios propios (`adaptador-potato` y `sync-peers`) cumplen su definición de hecho.

## 10. Escenarios de prueba

- **Dado** PotatoMesh en marcha, **cuando** se hace `POST /api/nodes` sin token o con un token incorrecto, **entonces** `401/403`.
- **Dado** 100 conexiones SSE abiertas, **cuando** se pide `/api/nodes`, **entonces** responde en < 500 ms.
- **Dado** `adaptador-potato` parado 5 min, **cuando** vuelve, **entonces** PotatoMesh sigue sirviendo lo que tenía y recupera lo nuevo.
- **Dado** trazas de hace 6 días o nodos de hace 16 días, **cuando** corre la limpieza, **entonces** desaparecen y la web sigue respondiendo durante la ejecución.
- **Dado** un nodo con 🛑 en su nombre, **cuando** se busca en la web, **entonces** no aparece.

## 11. Riesgos y limitaciones

- SQLite y un solo proceso Puma: suficiente para la escala prevista; cada cliente SSE retiene un hilo.
- Cambios incompatibles entre versiones de PotatoMesh (ya pasó en 0.7.0): mitigado con versión fijada y tests de contrato.
- La API pública nunca muestra más de 28 días; la limpieza periódica depende del esquema SQLite de PotatoMesh y puede romperse al actualizar (se revisa con cada versión).
- Los mosaicos del mapa salen de OSM HOT y CARTO: única petición a terceros, declarada en privacidad.
- `sync-peers` depende de APIs públicas ajenas que pueden cambiar o cerrarse.

## 12. Referencias

- `../integration.md` §2, §4, §10, §11, §15.

## Decisiones de detalle

1. Limpieza periódica (trazas 5 días; nodos, chat, waypoints, telemetría y posiciones 15 días) con un timer del host (03:47 h) y SQL versionado junto a la integración (PotatoMesh solo purga a 365 días, saturando el mapa Leaflet en el cliente).
2. `POTATOMESH_SQLITE=/srv/potatomesh/datos/mesh.db` (bind mount en lugar de volumen con nombre, para que el host lo copie).
3. Sin parches directos sobre el código fuente empaquetado de la imagen base; las adaptaciones visuales mínimas se inyectan por bind mount de plantilla (CUST-04).
4. `/metrics` no se publica en Nginx (`location /metrics { return 404; }`).
5. Federación desactivada de forma obligatoria en `compose.yaml` (`environment: - FEDERATION=0`) y en `.env` para garantizar aislamiento total frente a anuncios y crawling de peers no controlados (DT-37, CUST-02).
6. Plantilla `app.erb` personalizada montada en `/app/views/layouts/app.erb:ro` para mostrar el indicador de retención (`RETENTION_BADGE`) en el navbar de escritorio y móvil (CUST-04).

---
> Creado: 2026-10-07 · Última revisión: 2026-10-09
