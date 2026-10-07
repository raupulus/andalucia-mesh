# 08.3 · Webhooks

> Aplicación `webhooks` (contenedor `webhooks`, base `webhooks`, monorepo `services/webhooks/`): entrega cada transición del socket por `POST` firmado con HMAC a las URLs que el operador da de alta. Es el "tiempo real enganchable" (sin SSE ni MQTT público). Núcleo, socket, salud, retención y despliegue: [`README.md`](README.md).

## Objetivo

- Que un integrador reciba en su sistema cada alerta que le interese, en segundos, con un JSON estable (claves en inglés, como la API pública) y una firma verificable.
- Reutilizar el núcleo común (configuración, registro, salud, base, cliente del socket con cursor). No usa API del portal, catálogo, formato de texto, comandos ni anti-ruido: las máquinas reciben todas las transiciones que les tocan.
- Cumplir lo que promete la página de bots (`../portal/06-bots-page.md`) y documenta `../portal/11-public-api.md`: qué se envía, cómo se verifica la firma y cómo se pide el alta (correo a `PROJECT_CONTACT`).

## Especificación

### Alta, cambio y baja (operador)

1. El integrador escribe a `PROJECT_CONTACT` con la URL (`https`) y los filtros que quiere.
2. El operador elige un `nombre` y añade la entrada a `/srv/webhooks/webhooks.yaml` (el repositorio solo lleva `webhooks.example.yaml`).
3. Genera el secreto (`openssl rand -hex 32`) y lo guarda en `/srv/webhooks/.env` como `WEBHOOK_<NOMBRE>_SECRETO` (nombre en mayúsculas, `-` → `_`).
4. `docker compose up -d --force-recreate` (relee `.env` y YAML; el cursor evita pérdidas).
5. Entrega el secreto al integrador por un canal privado.
6. `docker compose exec webhooks python -m webhooks probar <nombre>`: el integrador confirma que recibe el `ping` y que la firma cuadra.

**Cambio:** editar YAML o secreto y repetir el paso 4. Al rotar el secreto no hay periodo con dos secretos: el receptor lo cambia a la vez y lo rechazado en esa ventana se reintenta (hasta ~6 min). **Baja:** quitar la entrada y su secreto y repetir el paso 4 (destino `retirado`).

```yaml
# /srv/webhooks/webhooks.yaml (sin secretos)
destinos:
  - nombre: radio-club-cadiz
    url: https://alertas.example.org/snm
    riesgos: [medio, alto]
    tipos: [infraestructura]
    provincias: [ES-CA, ES-SE]
  - nombre: monitor-nodo-propio
    url: https://hooks.example.net/entrada
    nodos: ["!a1b2c3d4", "!0badc0de"]
# Secretos en .env: WEBHOOK_RADIO_CLUB_CADIZ_SECRETO, WEBHOOK_MONITOR_NODO_PROPIO_SECRETO
```

| Campo | Regla al cargar (error → sale con código 1 nombrando entrada y campo, nunca el secreto) |
|---|---|
| `nombre` | Obligatorio, `^[a-z0-9-]{1,40}$`, único |
| `url` | Obligatoria, `https://`, ≤ 2.048 caracteres, sin usuario ni contraseña, host con al menos un punto, ni `localhost` ni `*.local`/`*.internal`; si el host es una IP, debe ser pública (red segura, abajo) |
| `riesgos`, `tipos` | Opcionales, ids `^[a-z0-9-]+$`. Un valor fuera de `bajo`/`medio`/`alto` o `infraestructura`/`clientes` solo genera un aviso en el registro (catálogo ampliable) |
| `provincias` | Opcional: `ES-AL`, `ES-CA`, `ES-CO`, `ES-GR`, `ES-H`, `ES-J`, `ES-MA`, `ES-SE`, `FUERA` |
| `nodos` | Opcional: `^![0-9a-f]{8}$` |
| Secreto | `WEBHOOK_<NOMBRE>_SECRETO` presente y de ≥ 32 caracteres |
| Otras claves | Error (erratas) |

Un archivo sin destinos es válido: la aplicación sigue leyendo el socket y avanzando el cursor.

**Sincronización al arrancar** (por `nombre`): entrada nueva → destino activo; entrada existente → actualiza `host` y `url_hash`; destino `retirado` que vuelve al YAML → se reactiva; destino `fallos` o `gone` → sigue inactivo salvo que cambie la URL (`url_hash`) o se use `reactivar`; fila sin entrada en el YAML → inactivo `retirado`, pendientes → `caducada`. Filtros, URL y secreto se leen del YAML y del entorno; la base solo guarda estado.

### Decisión por destino

Para cada transición y cada destino activo. "Pasa" = cumple todos los filtros presentes (un filtro ausente no filtra): `riesgo` en `riesgos`, `tipo` en `tipos`, `nodo_info.provincia` en `provincias` (una alerta sin provincia, como las `all`, pasa este filtro), `nodo` o algún elemento de `nodos` en `nodos`. "Entregas previas" = alguna fila `entrega` de esa alerta a ese destino, en cualquier estado.

| Transición | Sin entregas previas | Con entregas previas |
|---|---|---|
| `abierta` | Si pasa → entrega con `first_delivery: true` | Entrega (reapertura) con `first_delivery: false` |
| `actualizada` | Si pasa → entrega con `first_delivery: true` | Entrega siempre, cambie o no el riesgo |
| `resuelta` | Nada | Entrega |

Igual que en los bots, una alerta ya entregada se sigue hasta su resolución aunque deje de pasar los filtros. Sin agrupación ni límites por minuto. Las filas `entrega` (con el cuerpo ya serializado) se insertan en la misma transacción que el cursor (`README.md` §4.1).

### Envío

| Elemento | Valor |
|---|---|
| Petición | `POST` a la URL del YAML con el cuerpo exacto de `entrega.cuerpo` y las cabeceras de "Contratos propios" |
| Timeout | `WEBHOOKS_TIMEOUT_S` (10 s) en total, 5 s de conexión |
| TLS | Verificación de certificado obligatoria (CA del sistema), sin excepciones por destino |
| Redirecciones | No se siguen: `3xx` es fallo |
| Respuesta | Solo cuenta el código; el cuerpo se descarta |
| Orden | FIFO estricto por destino: la siguiente entrega no sale hasta que la anterior termina (`entregada`, `fallida` o `caducada`) |
| Concurrencia | Una petición en vuelo por destino; `WEBHOOKS_CONCURRENCIA` (10) en total. Un receptor lento solo retrasa su propia cola |
| Estado de destinos | Se lee de la base al decidir y al enviar (pocas filas, sin caché): `reactivar` surte efecto sin reiniciar |

| Resultado | Acción |
|---|---|
| `2xx` | `entregada`; `fallos_seguidos = 0`; `ultimo_ok = now()` |
| Red, DNS, timeout, `5xx`, `408`, `429` | Reintento a los 10 s, 60 s y 5 min (4 intentos). Con `Retry-After` ≤ 300 s, se respeta. Tras el cuarto → `fallida` |
| `410 Gone` | `fallida` y destino inactivo `gone` al momento |
| Otros `4xx`, `3xx`, IP no permitida | `fallida` sin reintento |
| Pendiente con más de 24 h | `caducada` |

Cada `fallida` suma 1 a `fallos_seguidos`; al llegar a `WEBHOOKS_FALLOS_DESACTIVAR` (20) el destino queda inactivo `fallos`, sus pendientes pasan a `caducada` y se registra un `warning`. Con el receptor caído, cada entrega tarda ~6 min 10 s en fallar: ~2 h hasta la desactivación.

### Red segura (SSRF)

- **Al cargar:** reglas de `url` de la tabla anterior.
- **Al enviar:** resolvedor propio de aiohttp que resuelve A y AAAA y **rechaza la entrega** si alguna dirección no es global (`ipaddress`: `is_global` falso; en IPv6 con IPv4 mapeada se mira la IPv4), está en `64:ff9b::/96` (NAT64) o en `WEBHOOKS_IPS_BLOQUEADAS` (IPs públicas del propio servidor). La conexión usa solo esas direcciones ya validadas: no hay segunda resolución (evita *DNS rebinding*). Sin proxy (`trust_env=False`).
- Bloqueado así: `0.0.0.0/8`, `10/8`, `100.64/10`, `127/8`, `169.254/16`, `172.16/12` (incluida la red `mesh` y PostgreSQL en `172.30.0.1`), `192.168/16`, rangos de documentación y multidifusión, `::1`, `fc00::/7`, `fe80::/10`, `ff00::/8`.
- En registros y en la base solo aparece el host de la URL, nunca la ruta ni la consulta (pueden llevar tokens del integrador).

### Órdenes de operador

`docker compose exec webhooks python -m webhooks <orden>` (no toma el bloqueo de instancia):

| Orden | Hace |
|---|---|
| `listar` | Por destino: nombre, host, activo, `motivo_baja`, `fallos_seguidos`, `ultimo_ok`, pendientes |
| `probar <nombre>` | Envía ahora un `ping` firmado (fuera de la cola, sin guardarlo, aunque el destino esté inactivo) y muestra código y tiempo |
| `reactivar <nombre>` | `activo = true`, `fallos_seguidos = 0`, `motivo_baja` y `baja_en` a `NULL` |

### Salud

`/health` (`README.md` §4.7) con `entregas` y `destinos` en lugar de `plataforma` y `cola`. `503` solo por base o socket: un receptor caído es un problema externo y no pone la aplicación en rojo (se ve en el JSON y en `listar`).

```json
{
  "ok": true, "servicio": "webhooks", "version": "1.0.0",
  "socket": {"conectado": true, "ultima_linea": "2026-10-03T12:00:30Z", "cursor": "01JABCF1111111111111111111"},
  "base": {"ok": true},
  "entregas": {"pendientes": 0, "mas_antiguo_s": 0, "ultima_ok": "2026-10-03T11:58:02Z"},
  "destinos": {"activos": 2, "inactivos": 1}
}
```

## Contratos propios

**Cuerpo** (claves en inglés con la correspondencia de `integration.md` §9; `transition`: `abierta→opened`, `actualizada→updated`, `resuelta→resolved`; `alert.state`: `open` o `resolved`; `alert.data` tal cual `alerta.datos`; `created_at` = creación de la entrega):

```json
{
  "v": 1,
  "transition_id": "01JABCF1111111111111111111",
  "transition": "opened",
  "first_delivery": true,
  "created_at": "2026-10-01T15:00:02Z",
  "alert": {
    "id": "01JABCDXYZ7Q8R9S0T1V2W3X4Y",
    "rule": "reboot-loop", "risk": "alto", "type": "infraestructura",
    "message": "CAD1 se ha reiniciado 7 veces en la última hora",
    "node": "!a1b2c3d4", "nodes": [],
    "node_info": {"short": "CAD1", "long": "Repetidor Sierra Cádiz", "role": "ROUTER", "province": "ES-CA"},
    "data": {"reinicios": 7, "ventana_min": 60},
    "state": "open",
    "opened_at": "2026-10-01T15:00:00Z", "updated_at": "2026-10-01T15:00:00Z", "resolved_at": null,
    "url": "https://mesh.example.org/alertas/01JABCDXYZ7Q8R9S0T1V2W3X4Y"
  }
}
```

`ping` (orden `probar`): `transition: "ping"`, `first_delivery: false`, `alert: null`. Serialización: `json.dumps(…, ensure_ascii=False, separators=(",", ":"))` en UTF-8, hecha una vez al crear la entrega y guardada en `entrega.cuerpo` (`text`, no `jsonb`, que reordena claves): todos los intentos envían los mismos bytes y la misma firma.

| Cabecera | Valor |
|---|---|
| `Content-Type` | `application/json; charset=utf-8` |
| `User-Agent` | `${PROJECT_NAME} webhooks/<versión> (+https://${PROJECT_DOMAIN}/bots; ${PROJECT_CONTACT})` |
| `X-SNM-Transicion` | `transition_id` (clave de idempotencia) |
| `X-SNM-Firma` | HMAC-SHA256 del cuerpo exacto con el secreto del destino (bytes UTF-8), en hexadecimal en minúsculas (64 caracteres) |
| `X-SNM-Intento` | `1` a `4` |

**Vector de prueba** (secreto de ejemplo, no se usa en ningún destino):

| Dato | Valor |
|---|---|
| Secreto | `secreto-de-prueba-no-usar` |
| Cuerpo (144 bytes) | `{"v":1,"transition_id":"01JABCF2222222222222222222","transition":"ping","first_delivery":false,"created_at":"2026-10-03T12:00:00Z","alert":null}` |
| `X-SNM-Firma` | `5bcfe873e8b8936754a06e0a94ba5e87545def23bd484de6df8f9bc3c04ee0e5` |
| Comprobación | `printf '%s' '<cuerpo>' \| openssl dgst -sha256 -hmac 'secreto-de-prueba-no-usar'` |

**Para receptores** (lo publica `../portal/11-public-api.md`): verificar la firma sobre el cuerpo en bruto, con comparación de tiempo constante, antes de interpretar el JSON; responder `2xx` en menos de 10 s y procesar después; deduplicar por `X-SNM-Transicion` (entrega al menos una vez); orden garantizado por destino; ignorar claves desconocidas (`v` solo cambia con cambios incompatibles); responder `410` para darse de baja.

**Variables** (además de las de `README.md` §5 que no son `BOT_*`, `API_*` ni `PORTAL_API_URL`):

| Variable | Valor o ejemplo | Común o propia | Secreto |
|---|---|---|---|
| `WEBHOOKS_ARCHIVO` | `/app/config/webhooks.yaml` | Propia | No |
| `WEBHOOKS_TIMEOUT_S` | `10` | Propia | No |
| `WEBHOOKS_CONCURRENCIA` | `10` | Propia | No |
| `WEBHOOKS_FALLOS_DESACTIVAR` | `20` | Propia | No |
| `WEBHOOKS_IPS_BLOQUEADAS` | IPv4 pública y `/64` IPv6 del servidor (p. ej. `203.0.113.10,2001:db8:1:2::/64`) | Propia | No |
| `WEBHOOK_<NOMBRE>_SECRETO` | — (una por destino) | Propia | **Sí** |

**Datos** (base `webhooks`; además `cursor` con `cliente = webhooks` y `esquema_migraciones` de `README.md` §6):

| Tabla `destino` | Tipo | Notas |
|---|---|---|
| `id` | `bigint` identidad, PK | |
| `nombre` | `text` único | El del YAML |
| `host` | `text` | Solo el host de la URL |
| `url_hash` | `text` | SHA-256 de la URL (detecta cambios sin guardarla) |
| `activo` / `motivo_baja` | `boolean` / `text` nulo | `fallos`, `gone`, `retirado` |
| `fallos_seguidos` | `smallint` | |
| `ultimo_ok`, `alta_en`, `baja_en`, `actualizado_en` | `timestamptz` | |

| Tabla `entrega` | Tipo | Notas |
|---|---|---|
| `id` | `bigint` identidad, PK | |
| `destino_id` | `bigint` FK `destino` (`ON DELETE CASCADE`) | |
| `alerta_id`, `transicion_id` | `text` | ULID |
| `transicion`, `regla`, `riesgo`, `tipo` | `text` | |
| `cuerpo` | `text` | JSON exacto enviado y firmado |
| `estado` | `text` | `pendiente`, `entregada`, `fallida`, `caducada` |
| `intentos` | `smallint` | |
| `programado_para` | `timestamptz` | |
| `ultimo_codigo` / `ultimo_error` / `duracion_ms` | `smallint` / `text` (≤ 500, sin URL) / `integer` | Del último intento |
| `creado_en` / `entregado_en` | `timestamptz` | |

`UNIQUE (destino_id, transicion_id)`; índices `(destino_id, id) WHERE estado = 'pendiente'`, `(destino_id, alerta_id)` y `(creado_en)`. Retención: `README.md` UT-08.8.

## Unidades de trabajo

### UT-08.3.1 — Configuración y sincronización de destinos
- **Comportamiento:** carga y validación de `webhooks.yaml` y secretos; sincronización con `destino`.
- **Casos borde:** dos entradas con el mismo nombre; secreto ausente o corto; URL `https://192.168.1.5/`; destino `fallos` con URL nueva (se reactiva).
- **Aceptación:** escenarios 7 y 8; ningún registro muestra secretos ni rutas de URL.

### UT-08.3.2 — Decisión y cuerpo
- **Comportamiento:** filtros, tabla de decisión, `first_delivery`, cuerpo en inglés serializado una vez, inserción con el cursor.
- **Casos borde:** alerta `all` con filtro de provincia; `nodo_info` nulo; `resuelta` sin entregas previas; alerta `all` de 2.000 nodos (~30 KB).
- **Aceptación:** escenarios 2 y 3; pruebas de instantánea del cuerpo de cada transición.

### UT-08.3.3 — Envío firmado y red segura
- **Comportamiento:** `POST` con cabeceras y firma, resolvedor validado, sin redirecciones ni proxy.
- **Aceptación:** escenarios 1, 6 y 9.

### UT-08.3.4 — Reintentos, orden y desactivación
- **Comportamiento:** tabla de resultados, FIFO por destino, concurrencia, caducidad, desactivación por fallos y `410`.
- **Aceptación:** escenarios 4, 5 y 10.

### UT-08.3.5 — Órdenes de operador y salud
- **Comportamiento:** `listar`, `probar`, `reactivar`; `/health` con `entregas` y `destinos`.
- **Aceptación:** `probar` contra un receptor de prueba devuelve `2xx` y el receptor valida la firma; `reactivar` hace salir la siguiente entrega sin reiniciar.

## Escenarios de prueba

1. **Firma.** Dado el vector de prueba, cuando un receptor calcula HMAC-SHA256 del cuerpo con el secreto, entonces obtiene exactamente el valor de `X-SNM-Firma`; con un byte cambiado, no.
2. **Filtros.** Dado un destino con `provincias: [ES-CA]` y `riesgos: [alto]`, cuando llegan `alto` en Sevilla, `alto` en Cádiz y `alto` con `nodo: "all"`, entonces recibe las dos últimas.
3. **Reapertura y seguimiento.** Dado un destino con `riesgos: [alto]` que recibió la apertura `alto`, cuando la alerta baja a `medio`, se resuelve y se reabre en `medio` antes de 1 h, entonces recibe `updated`, `resolved` y `opened` con `first_delivery: false`.
4. **Receptor caído.** Dado un receptor que responde `503`, cuando llegan 3 transiciones, entonces la primera se intenta a 0 s, 10 s, 70 s y ~6 min 10 s, queda `fallida`, y solo entonces empieza la segunda; otro destino sano recibe las 3 sin retraso.
5. **Baja por el receptor.** Dado un receptor que responde `410`, cuando recibe una entrega, entonces el destino queda inactivo `gone` y sus pendientes `caducada`.
6. **SSRF.** Dado un destino cuyo DNS pasa a resolver `192.168.1.10`, cuando toca enviarle, entonces la entrega queda `fallida` (`ip_no_permitida`) sin abrir conexión.
7. **IP privada al cargar.** Dada una entrada con `url: https://10.0.0.1/`, cuando arranca, entonces sale con código 1 y el mensaje nombra la entrada y el campo `url`.
8. **Retirada.** Dado un destino con 2 pendientes, cuando se quita del YAML y se recrea el contenedor, entonces queda `retirado` y las 2 pasan a `caducada`.
9. **Redirección.** Dado un receptor que responde `302` hacia otra URL, entonces no se sigue y la entrega queda `fallida` sin reintentos.
10. **Reinicio a mitad.** Dadas 5 entregas pendientes, cuando se reinicia el contenedor, entonces salen en orden con el mismo cuerpo y la misma firma que antes, sin filas nuevas.

---
> Creado: 2026-10-07 · Última revisión: 2026-10-07
