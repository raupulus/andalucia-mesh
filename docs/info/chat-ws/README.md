# 09 · Chat en directo por WebSocket

> Servicio público de solo lectura en `wss://${PROJECT_DOMAIN}/ws/chat` para suscribirse a los mensajes de texto de cada canal de chat admitido, en directo. **Tipo:** propio (Python) · **Fase:** 8 · **Complejidad:** baja · **Monorepo:** `services/chat-ws/`

## 1. Contexto

- **Requisitos:** poder suscribirse por WebSocket a cada canal de chat, solo a los canales admitidos (`ALLOWED_CHANNELS`).
- **Qué existe:** la ingesta ya publica un mensaje por paquete único en `snm/v1/decoded/text` con el canal, el remitente (`from_node`) y el texto (`../ingesta/06-decoded-stream.md`). Los mensajes de los canales públicos ya son visibles e indexables en PotatoMesh y MeshView: este servicio no publica nada que no sea ya público.
- **Qué sigue descartado:** difusión pública de alertas o eventos por MQTT, SSE o WebSocket. Las alertas solo salen por bots y webhooks. Este servicio es **solo chat**.
- **Seguridad de la malla:** solo lectura. Nada de lo que llega por el WebSocket se publica en MQTT ni vuelve a la radio (el usuario MQTT solo tiene lectura).
- **Consumidores:** cualquiera: webs y apps de la comunidad, paneles propios, integradores. Se documenta en la página `/api` del portal.

## 2. Alcance

**Incluye:** suscripción MQTT a `snm/v1/decoded/text`, filtro de canales y difusión, protocolo de suscripción por canal, historial corto en memoria, límites por IP y globales, salud, despliegue tras Nginx en la ruta `/ws/` del dominio principal.

**Fuera:** enviar mensajes a la malla, mensajes directos, alertas o cualquier otro tipo de paquete, autenticación, persistencia en base de datos, búsqueda en el historial (para eso está PotatoMesh).

## 3. Stack y versiones

| Componente | Versión |
|---|---|
| Python | 3.13 sobre `python:3.13-slim` |
| `websockets` | `17.2` (`websockets>=13.0`); servidor asíncrono con `permessage-deflate` |
| `aiomqtt` | `2.5.1` (`aiomqtt>=2.3.0`, identificador `snm-chat-ws` según TR-04) |
| `aiohttp` | `3.14.4` (`aiohttp>=3.11.0`) para endpoint `/health` en puerto 8080 |
| `pydantic`, `pydantic-settings` | `>=2.10.0` / `>=2.7.0` para configuración tipada |
| `uv`, `pytest`, `pytest-asyncio`, `pytest-cov`, `mypy`, `ruff` | `pyproject.toml` + `uv.lock` con tipado estricto `mypy --strict` |

## 4. Contratos

### 4.1 Entrada (MQTT)

- `mosquitto:1884`, usuario `svc-chatws`, lectura `snm/v1/decoded/text` (QoS 0).
- Se acepta un mensaje si: `portnum = "text"`, `to = "^all"`, `channel` está en `ALLOWED_CHANNELS` y `payload.text` no está vacío. Lo demás se descarta sin registrar el contenido.
- Latencia esperada: unos 2 s desde la primera recepción (ventana de deduplicación de la ingesta).

### 4.2 Conexión

- URL: `wss://${PROJECT_DOMAIN}/ws/chat`. Opcional `?channels=Cadiz,sos` para suscribirse al conectar.
- Sin autenticación, sin cookies; `Origin` no se comprueba (datos públicos, solo lectura).
- Mensajes JSON en texto UTF-8, claves en inglés (como la API pública), un objeto por mensaje.

### 4.3 Protocolo

Servidor al conectar:

```json
{"type": "hello", "version": 1, "channels": ["SFNarrow", "Iberia", "Andalucia", "Cadiz", "Huelva", "Almeria", "Granada", "Jaen", "Sevilla", "Cordoba", "Malaga", "Ceuta", "Melilla", "sos"], "history_size": 20}
```

Cliente:

```json
{"type": "subscribe", "channels": ["Cadiz", "sos"]}
{"type": "unsubscribe", "channels": ["sos"]}
```

Servidor:

```json
{"type": "subscribed", "channels": ["Cadiz", "sos"]}
{"type": "history", "channel": "Cadiz", "items": [ /* hasta 20 objetos "message", del más antiguo al más reciente */ ]}
{"type": "message", "channel": "Cadiz", "id": 3735928559,
 "from": {"id": "!a1b2c3d4", "short": "CAD1", "long": "Repetidor Sierra Cádiz"},
 "text": "Buenas desde la sierra", "reply_id": null, "emoji": false,
 "at": "2026-10-04T12:00:00Z", "hops": 1, "gateways": 2}
{"type": "error", "code": "unknown_channel", "channels": ["Madrid"]}
```

| `code` de error | Cuándo | ¿Cierra? |
|---|---|---|
| `unknown_channel` | Canal fuera de `ALLOWED_CHANNELS` (nombre exacto, mayúsculas incluidas); los válidos de la misma petición sí se suscriben | No |
| `invalid_message` | JSON inválido, `type` desconocido o mensaje > 1 KB | No (al 5.º en 1 min, sí: 1008) |
| `rate_limited` | Más de 5 mensajes/s del cliente | No (al persistir 10 s, sí: 1008) |
| `too_many_connections` | Más de `CHAT_MAX_POR_IP` conexiones de la IP o `CHAT_MAX_CONEXIONES` en total | Sí (1013) |

- `history`: al suscribirse a un canal, los últimos `CHAT_HISTORIAL` mensajes de ese canal guardados en memoria (máximo 24 h). Tras reiniciar el servicio el historial está vacío.
- Keepalive: ping de WebSocket cada 30 s; sin pong en 60 s, se cierra.
- Cliente lento: cola de envío de 500 mensajes; si se llena, cierre 1013 (el cliente reconecta y recibe el historial).
- Sin coordenadas, sin claves públicas, sin datos del gateway salvo el número de gateways que lo oyeron.

### 4.4 `/health` (puerto 8080, red `mesh`)

```json
{"ok": true, "mqtt": "conectado", "clientes": 37, "ultimo_mensaje": "2026-10-04T11:58:10Z"}
```

`503` si MQTT lleva > 60 s desconectado. Que no haya mensajes no es fallo (el chat puede estar en silencio).

## 5. Configuración

| Variable | Valor o ejemplo | Origen | Secreto |
|---|---|---|---|
| `MQTT_HOST` / `MQTT_PORT` | `mosquitto` / `1884` | Común | No |
| `MQTT_TOPIC_PREFIX` | `snm` | Común | No |
| `ALLOWED_CHANNELS` / `PRIMARY_CHANNEL` | lista de 14 / `SFNarrow` | Común | No |
| `MQTT_USER` / `MQTT_PASSWORD` | `svc-chatws` / — | Propia | Contraseña sí |
| `CHAT_PUERTO` | `8000` | Propia | No |
| `CHAT_HISTORIAL` | `20` | Propia | No |
| `CHAT_MAX_CONEXIONES` / `CHAT_MAX_POR_IP` | `2000` / `10` | Propia | No |
| `CHAT_PROXIES_CONFIABLES` | `172.30.0.1` (lee `X-Forwarded-For` solo si viene de Nginx por la puerta de `mesh`) | Propia | No |
| `LOG_LEVEL` | `INFO` | Propia | No |

## 6. Datos

Sin base de datos. Historial por canal en memoria (anillo de `CHAT_HISTORIAL`, máximo 24 h). No se registran textos ni IP en los logs del servicio (Nginx tampoco guarda registros de acceso).

## 7. Unidades de trabajo

- **UT-09.1 — Esqueleto.** `services/chat-ws/` con `pyproject.toml`, `uv.lock`, `Dockerfile` no root, `compose.yaml`, `.env.example`, `/health`. *Aceptación:* arranca sin `pip install` en tiempo de ejecución; sin secretos en git.
- **UT-09.2 — Entrada MQTT y filtro.** *Casos borde:* `to` distinto de `^all`, canal fuera de lista, texto vacío, JSON `decoded` de versión `v` desconocida (se descarta y se cuenta). *Aceptación:* solo pasan textos de difusión de los 14 canales.
- **UT-09.3 — Protocolo.** `hello`, `subscribe`, `unsubscribe`, `history`, `message`, errores; suscripción por `?channels=`. *Aceptación:* pruebas de contrato con un cliente de prueba para cada mensaje de §4.3.
- **UT-09.4 — Difusión e historial.** Registro de suscriptores por canal, anillo por canal, colas por cliente. *Aceptación:* 1.000 clientes suscritos reciben un mensaje en < 1 s.
- **UT-09.5 — Límites.** Por IP (detrás de Nginx), globales, tamaño y ritmo de mensajes, cliente lento. *Aceptación:* la 11.ª conexión de una IP recibe `too_many_connections` y cierre 1013.
- **UT-09.6 — Despliegue y documentación.** `location /ws/` de Nginx, sección "Chat en directo" en la página `/api` del portal (`../portal/pages/10-api.md`). *Aceptación:* `wss://${PROJECT_DOMAIN}/ws/chat` responde desde internet y la documentación coincide con §4.

## 8. Despliegue

| Contenedor | Imagen | Redes | Volúmenes | Puertos | Publicación | Healthcheck |
|---|---|---|---|---|---|---|
| `chat-ws` | `snm-chat-ws:<versión>` | `mesh` | — | `127.0.0.1:8090:8000` (WebSocket), 8080 (`/health`, solo en `mesh`) | Nginx (`location /ws/` en `snm-portal.conf`) | `GET http://127.0.0.1:8080/health` cada 30 s |

- `location /ws/` va antes que `/` en el sitio del portal, con cabeceras `Upgrade`/`Connection` y `proxy_read_timeout` largo (`../infrastructure/03-nginx-dns.md`).
- `env_file: [/srv/comun/.env, .env]`, `restart: unless-stopped`, límite 256 MB.

Pasos:

1. Requisitos: `mosquitto` con el usuario `svc-chatws` y `ingesta` publicando `snm/v1/decoded/text`.
2. `/srv/chat-ws/`: `compose.yaml` y `.env`.
3. `deploy.sh chat-ws`.
4. Comprobar: conectar con un cliente WebSocket, suscribirse a `SFNarrow` y ver llegar mensajes; el panel lo muestra en verde (`../portal/14-operator-panel.md`, destino `chat-ws`).

Copia: no hay datos que copiar.

## 9. Definición de hecho
 
- [x] `wss://${PROJECT_DOMAIN}/ws/chat` acepta conexiones y responde `hello` con los 14 canales.
- [x] Solo llegan textos de difusión de los canales suscritos; nunca mensajes directos ni otros tipos.
- [x] Un canal fuera de la lista recibe `unknown_channel`.
- [x] Límites por IP, globales y de cliente lento funcionando.
- [x] El servicio no puede publicar en MQTT (ACL de solo lectura) y no guarda textos ni IP.
- [x] Documentado en `/api` y vigilado en el panel.
 
 ## 10. Escenarios de prueba
 
 - **Dado** un cliente suscrito a `Cadiz`, **cuando** la ingesta publica un texto de `Cadiz`, **entonces** lo recibe en < 3 s desde la radio; uno de `Sevilla` no le llega.
 - **Dado** un cliente que pide `["Cadiz", "Madrid"]`, **cuando** se suscribe, **entonces** recibe `subscribed` con `Cadiz` y `error unknown_channel` con `Madrid`.
 - **Dado** 15 mensajes en `sos` en la última hora, **cuando** un cliente se suscribe a `sos`, **entonces** recibe `history` con los 15 en orden.
 - **Dado** un mensaje de texto directo a un nodo, **cuando** llega a `decoded`, **entonces** no se difunde.
 - **Dado** un cliente que no lee, **cuando** se acumulan 500 mensajes, **entonces** se cierra con 1013 y los demás clientes no se retrasan.
 - **Dado** MQTT caído 2 min, **cuando** se consulta `/health`, **entonces** `503`; al volver, los clientes siguen conectados y reciben lo nuevo.
 - **Dado** un cliente que envía `{"type": "publish", …}`, **cuando** llega, **entonces** `invalid_message` y no ocurre nada más.
 
 ## 11. Riesgos y limitaciones
 
 - El historial se pierde al reiniciar (no hay base de datos, a propósito).
 - Si cae la ingesta no hay mensajes nuevos (depende de `decoded`).
 - Solo se ve lo que llega con OK to MQTT a nuestros gateways.
 - Abuso por muchas conexiones: mitigado con límites por IP y globales.
 
 ## 12. Referencias
 
 - `../integration.md` §2, §4, §5, §6, §11, §12.
 - `../ingesta/06-decoded-stream.md` (formato `text`).
 
 ## Decisiones de detalle
 
 1. Ruta `/ws/chat` en el dominio principal (sin subdominio nuevo).
 2. Claves en inglés, como la API pública; códigos de cierre estándar de WebSocket.
 3. Historial de 20 mensajes por canal en memoria, máximo 24 h.
 4. Sin comprobar `Origin` ni autenticar: los datos ya son públicos y el servicio es de solo lectura.
 5. Se incluye el canal `sos` (está en la lista admitida).
 
 ---
 > Creado: 2026-10-07 · Última revisión: 2026-10-09
