# 04.2 · Sincronización con instancias vecinas (`sync-peers`)

## Objetivo

Mostrar en la instancia lo que reciben otras comunidades que no suben a su broker, leyendo solo las APIs públicas de las instancias PotatoMesh que el operador configure. Los datos se envían a PotatoMesh local (mapa y chat) y se publican en Mosquitto (`snm/v1/peer/<peer_id>/...`) para que el servicio de `ingesta` aplique el filtro anti-duplicados y alimente TimescaleDB, el detector de alertas y los bots con la cobertura regional completa.

No es la federación nativa de PotatoMesh (desactivada): nunca se escribe ni se envía nada a instancias ajenas.

## Especificación

### Peers

`peers.json` montado en solo lectura (`/srv/sync-peers/peers.json`, **fuera de git**: las instancias que se leen son de cada operador; en el repositorio solo `peers.example.json` con datos inventados). Se relee al empezar cada ciclo; los cambios se aplican sin reiniciar.

```json
[
  {"id": "vecina-1", "nombre": "Instancia vecina", "url": "https://<instancia>", "activo": true,
   "mensajes": true, "trazas": true, "nodos": true, "intervalo_nodos_s": 60}
]
```

`id`: `[a-z0-9-]+`, único. Un `id` que desaparece del archivo deja de sincronizarse; su cursor se conserva.

### Ciclos (por peer, en tareas independientes)

- **Mensajes y trazas:** cada 60 s, `GET /api/messages?since=<cursor>&limit=100` y `GET /api/traces?since=<cursor>&limit=100`. Si una respuesta trae 100 elementos se pide la siguiente página en el mismo ciclo hasta vaciar (tope 20 páginas por ciclo).
- **Nodos:** cada `intervalo_nodos_s` (por defecto 60 s), `GET /api/nodes?limit=<lote>`. En el arranque en frío (primer sync sin cursor previo) se solicitan 1.000 nodos para capturar todo el catálogo histórico; en las sincronizaciones continuas de mantenimiento se solicitan 30 nodos más recientes, cubriendo la actividad viva con un impacto de red y CPU insignificante.
- Filtro: solo canales de `ALLOWED_CHANNELS`; `PRIMARY_CHANNEL` → índice 0 y el resto con el mismo índice estable que el adaptador (posición en la lista). Mensajes directos nunca.
- **Envío doble:**
  1. A nuestra API de PotatoMesh con `POST` y el token propio (para actualizar el visor local).
  2. Publicación MQTT en `snm/v1/peer/<peer_id>/<tipo>` (`messages`, `nodes`, `traces`) en `mosquitto:1884` con usuario `svc-potato` para que el servicio de `ingesta` aplique el filtro anti-duplicados (15 min) y alimente TimescaleDB y las alertas. La publicación se fragmenta en bloques de máximo 10 elementos (o < 12 KB) para no exceder el límite `max_packet_size` (16 KB) de Mosquitto.
- Compatibilidad de claves en `peers.json`: admite tanto claves en español (`nombre`, `activo`, `mensajes`, `trazas`, `nodos`, `intervalo_nodos_s`) como alias heredados en inglés (`name`, `enabled`, `sync_messages`, `sync_traces`, `sync_nodes`, `nodes_interval_seconds`).
- El cursor se actualiza **en una transacción solo después** de confirmar el envío del lote: un corte reenvía como mucho el último lote, que PotatoMesh e ingesta absorben sin duplicar.
- Marca de origen: si la versión fijada de PotatoMesh admite un campo de procedencia, se rellena con el `id` del peer; si no, no se marca (limitación anotada).

### Peers caídos

Espera progresiva por peer: 60 s → 5 min → 15 min mientras falle; vuelve al ciclo normal en el primer éxito. Un peer caído no retrasa a los demás. Tiempo de espera por petición 20 s; respuesta > 8 MiB → se corta y cuenta como fallo.

### Cortesía

User-Agent `${PROJECT_NAME}-PeerSync/1.0 (+https://${PROJECT_DOMAIN}; ${PROJECT_CONTACT})`. Antes de añadir un peer se avisa a su administrador.

## Contratos propios

### Base `peersync` (PostgreSQL nativo, propietario `peersync`)

| Tabla `peer_cursor` | Tipo | Notas |
|---|---|---|
| `peer_id` | `text` PK | `id` de `peers.json` |
| `last_message_time` / `last_trace_time` / `last_node_sync` | `timestamptz` null | Cursores |
| `ultimo_ok` | `timestamptz` null | Último ciclo completo correcto |
| `fallos_consecutivos` | `int` default 0 | Para la espera progresiva |
| `ultimo_error` | `text` null | Mensaje corto, sin cuerpos de respuesta |
| `actualizado` | `timestamptz` default `now()` | |

Migraciones versionadas al arrancar. Sin datos personales. Copia: la del PostgreSQL del servidor.

### `/health` (puerto 8080, solo red `mesh`)

```json
{"ok": true, "potatomesh": "ok", "peers": [
  {"id": "vecina-1", "estado": "ok", "activo": true, "ok": true, "ultimo_ok": "2026-10-04T10:14:00Z", "fallos_consecutivos": 0},
  {"id": "vecina-2", "estado": "caido", "activo": true, "ok": false, "ultimo_ok": "2026-10-04T08:02:00Z", "fallos_consecutivos": 9},
  {"id": "vecina-3", "estado": "desactivado", "activo": false, "ok": false, "ultimo_ok": null, "fallos_consecutivos": 0}]}
```

`503` solo si falla la base o nuestra API de PotatoMesh (`401/403` o caída > 5 min). Un peer caído **no** pone el servicio en rojo: aparece con `estado: "caido"` (o `estado: "desactivado"` si `activo: false`) y el panel de operadores lo destaca si lleva > 1 h (`../portal/14-operator-panel.md`).

### Configuración

| Variable | Valor o ejemplo | Origen | Secreto |
|---|---|---|---|
| `PROJECT_NAME` / `PROJECT_DOMAIN` / `PROJECT_CONTACT` | — | Común | No |
| `ALLOWED_CHANNELS` / `PRIMARY_CHANNEL` | — | Común | No |
| `MQTT_HOST` / `MQTT_PORT` | `172.30.0.1` / `1884` | Común | No |
| `MQTT_USER` / `MQTT_PASSWORD` | `svc-potato` / — | Propia | Contraseña sí |
| `DB_HOST` / `DB_PORT` | `172.30.0.1` / `5432` | Común | No |
| `DB_NAME` / `DB_USER` / `DB_PASSWORD` | `peersync` / `peersync` / — | Propia | Contraseña sí |
| `POTATOMESH_URL` | `http://potatomesh:41447` | Propia | No |
| `POTATOMESH_API_TOKEN` | — | Propia | Sí |
| `PEERS_ARCHIVO` | `/app/peers.json` | Propia | No |
| `CICLO_MENSAJES_S` / `PAGINAS_MAX` | `60` / `20` | Propia | No |
| `LOG_LEVEL` | `INFO` | Propia | No |

## Unidades de trabajo

- **UT-04.2.1 — Esqueleto.** `services/sync-peers/` con `pyproject.toml`, `uv.lock`, `Dockerfile` no root, `compose.yaml`, `.env.example`, `peers.example.json` (y `peers.json` en `.gitignore`), `/health`. *Aceptación:* sin tokens en el repositorio; arranca sin `pip install`.
- **UT-04.2.2 — Migraciones y cursores.** Tabla `peer_cursor`. *Aceptación:* tras un reinicio, el primer ciclo no repite lo ya importado.
- **UT-04.2.3 — Lectura de `peers.json`.** Validación (esquema, `id` únicos); un archivo inválido mantiene la última configuración válida y lo registra. *Aceptación:* desactivar un peer surte efecto en el siguiente ciclo sin reiniciar.
- **UT-04.2.4 — Ciclos con paginación.** Tareas por peer y tipo. *Aceptación:* 250 mensajes nuevos en un ciclo → se importan los 250.
- **UT-04.2.5 — Envío y transacción de cursor.** *Aceptación:* matar el proceso entre el `POST` y el `COMMIT` no deja huecos; el reenvío no duplica en PotatoMesh.
- **UT-04.2.6 — Espera progresiva y salud.** *Aceptación:* peer apagado 2 h → peticiones espaciadas a 15 min, `estado: "caido"` y recuperación sola al volver.
- **UT-04.2.7 — Tests de contrato.** Contra la imagen fijada de PotatoMesh como peer simulado y como destino. *Aceptación:* verdes.

## Escenarios de prueba

1. **Dado** un peer con 250 mensajes nuevos de canales permitidos, **cuando** corre el ciclo, **entonces** se importan los 250 en ese ciclo (3 páginas).
2. **Dado** mensajes del peer en el canal `Madrid`, **cuando** se sincroniza, **entonces** no se envían.
3. **Dado** un corte del proceso a mitad de lote, **cuando** arranca, **entonces** reenvía como mucho ese lote y no hay duplicados visibles.
4. **Dado** `vecina-2` caída y `vecina-1` activa, **cuando** pasan 10 min, **entonces** `vecina-1` se sincroniza cada 60 s y `vecina-2` se reintenta con espera progresiva.
5. **Dado** `peers.json` con JSON roto, **cuando** se relee, **entonces** sigue con la configuración anterior y lo registra una vez.
6. **Dado** un peer que responde 12 MiB, **cuando** se lee, **entonces** se corta, cuenta como fallo y no afecta a los demás.

---
> Creado: 2026-10-07 · Última revisión: 2026-10-09

