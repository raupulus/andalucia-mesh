# 10 · API

> `/api` · Documentación pública de la API JSON de solo lectura y de los webhooks de alertas, suficiente para integrarse sin preguntar · `../11-public-api.md` (módulos 01–03) y `../../bots-webhooks/03-webhooks.md`

## SEO

- **Título:** `API pública y webhooks · {PROJECT_NAME}` (44 caracteres)
- **Descripción:** `API pública de {PROJECT_NAME}: estado de la malla, provincias, rankings, routers y alertas en JSON, sin registro, y webhooks firmados.` (139)

## Estructura

| # | Sección | Componente (`DESIGN.md`) |
|---|---|---|
| 1 | H1 y entradilla | Tipografía H1 + Texto |
| 2 | Lo básico | Bloque de ajustes (botón de copiar en la URL base) |
| 3 | Endpoints | Tablas (rutas en Ubuntu Mono) |
| 4 | Detalle de cada endpoint: qué devuelve, parámetros y ejemplo | H3 + Tablas + bloque de código (ejemplos de las fichas 12 y 13) |
| 5 | Códigos de respuesta | Tablas |
| 6 | Webhooks (ancla `#webhooks`) | H2 + Texto + Bloque de ajustes + bloque de código |
| 7 | Uso responsable | Texto corrido con lista |

Índice lateral en escritorio (o al principio en móvil) con enlaces a cada endpoint y a Webhooks.

## Borrador del texto

Cada `###` es un H2 de la página; cada `####`, un H3.

**H1:** API pública

Todos los datos públicos de {PROJECT_NAME} en JSON: estado de la malla, nodos y carga por provincia, rankings, routers y alertas. Es la misma API que usan este portal y los bots. Es de solo lectura y no necesita registro.

### Lo básico

| | |
|---|---|
| URL base | `https://{PROJECT_DOMAIN}/api/v1` [Copiar] |
| Métodos | Solo `GET` |
| Autenticación | Ninguna |
| Formato | JSON |
| Límite | 60 peticiones por minuto por IP |
| CORS | Abierto para `GET` |
| Fechas | ISO 8601 en UTC. Los días, semanas y meses de los rankings se cortan en hora de España |
| Provincias | Códigos ISO 3166-2: `ES-AL`, `ES-CA`, `ES-CO`, `ES-GR`, `ES-H`, `ES-J`, `ES-MA`, `ES-SE` |
| Tiempo real | No hay. Para enterarte al momento usa los [bots](/bots) o los [webhooks](#webhooks). Si consultas de forma periódica, no lo hagas más de una vez cada 30 segundos |

Todas las respuestas llevan:

- `generated_at`: cuándo se generaron los datos.
- La ventana o el periodo que cubren.
- `notes`: advertencias para interpretarlos (por ejemplo, que solo cuentan los nodos con OK to MQTT).
- `stale: true` si la fuente no responde y te servimos la última copia guardada.

Lo que no vas a encontrar: coordenadas exactas. La API da recuentos, nombres públicos, ids y provincia, nunca la ubicación de un nodo.

### Endpoints

| Ruta | Devuelve | Se actualiza cada |
|---|---|---|
| `GET /stats/summary` | Estado general de la malla | 60 s |
| `GET /stats/provinces` | Nodos y carga del canal por provincia, y total de Andalucía | 60 s |
| `GET /stats/rankings` | Catálogo de rankings | 1 h |
| `GET /stats/rankings/{id}` | Un ranking | De 60 s a 15 min, según el periodo |
| `GET /stats/traffic-mix` | Reparto del tráfico por tipo de paquete | 5 min |
| `GET /routers` | Routers con batería, utilización del canal y tiempo de transmisión | 60 s |
| `GET /alerts` | Alertas, con filtros | 30 s |
| `GET /alerts/{id}` | Una alerta con su historial | 30 s |
| `GET /alerts/catalog` | Riesgos, tipos y reglas activas | 1 h |
| `GET /nodes/at-risk` | Nodos en peligro | 30 s |

### Detalle

#### Estado general · `GET /stats/summary`

Nodos activos en 24 h y en 7 días, routers activos en 24 h, gateways publicando, paquetes por hora, utilización media del canal en Andalucía y por provincia, y alertas abiertas por riesgo y por tipo.

```json
{
  "generated_at": "2026-10-02T12:00:00Z",
  "nodes_active_24h": 412,
  "nodes_active_7d": 655,
  "routers_active_24h": 38,
  "gateways_publishing": 9,
  "packets_per_hour": 15840,
  "channel_utilization": {
    "andalucia_avg": 18.6,
    "provinces": [{"code": "ES-CA", "avg": 31.2, "level": "orange"}]
  },
  "alerts_open": {
    "by_risk": {"bajo": 4, "medio": 7, "alto": 1},
    "by_type": {"infraestructura": 9, "clientes": 3}
  },
  "notes": ["Solo nodos cuyos paquetes llegan con OK to MQTT"]
}
```

*Cifras de ejemplo.*

#### Nodos y carga por provincia · `GET /stats/provinces`

| Parámetro | Valores |
|---|---|
| `window` | `24h`, `7d` (por defecto) o `30d`. Otro valor → `400` |

- **Nodos:** un nodo cuenta en la provincia donde cae su última posición válida recibida dentro de la ventana. No cuentan los nodos sin posición, las posiciones `0,0` ni las que tienen un error de más de unos 12 km.
- **Carga (`load`):** saturación estimada del canal en las últimas 12 horas, sea cual sea la ventana. Se toman los nodos situados en la provincia y se separan en routers (`ROUTER`, `ROUTER_LATE`, `REPEATER`) y clientes (`CLIENT` y `CLIENT_BASE` juntos) (los `CLIENT_MUTE` no cuentan). `avg` = 0,6 × media de routers + 0,4 × media conjunta de `CLIENT` y `CLIENT_BASE` (si falta un grupo, su peso se reparte entre los demás). `max` es el valor más alto y `groups` trae la media y el número de nodos de cada grupo.
- **Nivel (`level`):** `green`, `orange`, `red` o `nodata`, con los cortes de `load_levels`.
- `outside_andalucia`: nodos oídos desde Andalucía pero situados fuera. `border_uncertain`: nodos cuya posición imprecisa toca el límite entre dos provincias (se asignan por su punto central).

```json
{
  "window": "7d",
  "load_window": "12h",
  "generated_at": "2026-10-01T16:00:00Z",
  "total_andalucia": 412,
  "outside_andalucia": 37,
  "load_levels": {"green_max": 20, "red_min": 40},
  "provinces": [
    {"code": "ES-CA", "name": "Cádiz", "nodes": 138, "load": {"avg": 27.4, "max": 44.5, "level": "orange",
      "groups": {"routers": {"avg": 31.2, "n": 9}, "clients": {"avg": 22.0, "n": 16}}}},
    {"code": "ES-CO", "name": "Córdoba", "nodes": 19, "load": {"avg": null, "max": null, "level": "nodata",
      "groups": {"routers": {"avg": null, "n": 0}, "clients": {"avg": null, "n": 0}}}}
  ],
  "border_uncertain": 5,
  "notes": [
    "Solo nodos cuya posición ha llegado a nuestros gateways con OK to MQTT activado.",
    "Saturación estimada: media de routers (60 %) y media conjunta de CLIENT y CLIENT_BASE (40 %), últimas 12 h; CLIENT_MUTE no cuenta."
  ]
}
```

*Cifras de ejemplo, recortado a dos provincias.*

#### Rankings · `GET /stats/rankings` y `GET /stats/rankings/{id}`

`/stats/rankings` devuelve el catálogo. Cada ranking se pide por su `id`:

| Parámetro | Valores |
|---|---|
| `period` | `hour`, `day`, `week` o `month` |
| `which` | `current` (en curso) o `previous` (el anterior, ya cerrado) |
| `limit` | 10 por defecto, 50 como máximo |

| `period` | `current` | `previous` |
|---|---|---|
| `hour` | La hora en curso | La última hora completa |
| `day` | Hoy | Ayer |
| `week` | La semana en curso (ISO) | La semana anterior |
| `month` | El mes en curso | El mes anterior |

Los periodos cerrados no cambian.

| `id` | Qué mide |
|---|---|
| `network-usage` | Tiempo de aire de los paquetes que origina cada nodo (no los que retransmite), con número de paquetes y desglose por tipo |
| `gateway-coverage` | Nodos distintos que oye cada gateway |
| `gateway-exclusive` | Paquetes que solo ha subido ese gateway |
| `longest-links` | Distancia en km de los enlaces directos (sin saltos) entre nodo y gateway |
| `best-links` | SNR medio de los enlaces directos con al menos 10 recepciones |
| `most-neighbors` | Vecinos directos distintos |
| `uptime` | Mayor tiempo encendido sin reiniciarse |
| `solar-health` | Batería mínima más alta del periodo |
| `chatters` | Mensajes de texto en los canales públicos |
| `new-nodes` | Nodos vistos por primera vez en el periodo |
| `provinces-growth` | Diferencia de nodos activos frente al periodo anterior, por provincia |

Para no premiar la casualidad, se excluyen los nodos con pocos datos en el periodo. En `network-usage`, cada paquete cuenta una vez aunque lo oigan varios gateways.

```json
{
  "id": "network-usage",
  "period": "day",
  "which": "previous",
  "from": "2026-09-30T22:00:00Z",
  "to": "2026-10-01T22:00:00Z",
  "items": [
    {
      "rank": 1,
      "node": {"id": "!a1b2c3d4", "short": "XYZ1", "long": "Nodo ejemplo", "province": "ES-CA"},
      "value": 412.6, "unit": "s",
      "share_pct": 3.8,
      "packets": 1290,
      "by_type": {"nodeinfo": 61.0, "position": 22.5, "telemetry": 15.1, "text": 0.4, "other": 1.0}
    }
  ],
  "notes": ["Tiempo de aire estimado de paquetes originados por el nodo; no incluye retransmisiones."]
}
```

*Datos de ejemplo.*

#### Reparto del tráfico · `GET /stats/traffic-mix`

Qué parte del tráfico es cada tipo de paquete (mensajes, información del nodo, posición, telemetría y otros) en el periodo pedido. Parámetro: `period` (por ejemplo, `day`).

#### Routers · `GET /routers`

Todos los nodos con rol `ROUTER`, `ROUTER_LATE` o `REPEATER` vistos en los últimos 7 días.

| Parámetro | Valores |
|---|---|
| `province` | Código de provincia |
| `sort` | `battery`, `chutil`, `tx` o `last_seen` |

- `chutil`: utilización del canal (%). `tx`: porcentaje de tiempo que transmite el propio router. Último dato de las últimas 12 h; `null` si no hay.
- `battery.powered` es `true` cuando el nodo está alimentado por red (informa un nivel por encima de 100).

```json
{
  "generated_at": "2026-10-02T12:00:00Z",
  "items": [
    {
      "id": "!a1b2c3d4",
      "short": "CAD1",
      "long": "Repetidor Sierra Cádiz",
      "role": "ROUTER",
      "province": "ES-CA",
      "battery": {"level": 34, "voltage": 3.71, "powered": false, "at": "2026-10-02T11:40:00Z"},
      "chutil": 22.5,
      "tx": 3.1,
      "last_seen": "2026-10-02T11:58:00Z"
    }
  ]
}
```

*Datos de ejemplo.*

#### Alertas · `GET /alerts`, `GET /alerts/{id}` y `GET /alerts/catalog`

| Parámetro de `/alerts` | Valores |
|---|---|
| `state` | `open` (por defecto), `resolved` o `all` |
| `risk` | `bajo`, `medio`, `alto` |
| `type` | `infraestructura`, `clientes` |
| `province` | Código de provincia |
| `node` | Id del nodo (`!a1b2c3d4`) |
| `since` | Fecha ISO 8601 |
| `limit` | Hasta 500 |
| `cursor` | Para pedir la página siguiente |

Los campos de las alertas van en español:

| Campo | Qué es |
|---|---|
| `id` | Identificador de la alerta |
| `regla` | Regla que la ha disparado (`reboot-loop`, `battery-low`…) |
| `riesgo` | `bajo`, `medio` o `alto` |
| `tipo` | `infraestructura` o `clientes` |
| `mensaje` | Texto legible |
| `nodo` | Id del nodo afectado, o `all` si son muchos |
| `nodos` | Lista de nodos cuando `nodo` es `all` |
| `nodo_info` | Nombre corto y largo, rol y provincia |
| `datos` | Valores que la dispararon (cambian según la regla) |
| `estado` | `abierta` o `resuelta` |
| `abierta_en`, `actualizada_en` | Fechas |

`/alerts/{id}` añade el historial de cambios de la alerta. `/alerts/catalog` devuelve los riesgos y tipos vigentes y las reglas activas con su descripción pública; si aparece un riesgo o un tipo nuevo, sale ahí.

```json
{
  "id": "01JABCDXYZ7Q8R9S0T1U2V3W4X",
  "regla": "reboot-loop",
  "riesgo": "alto",
  "tipo": "infraestructura",
  "mensaje": "CAD1 se ha reiniciado 7 veces en la última hora",
  "nodo": "!a1b2c3d4",
  "nodos": [],
  "nodo_info": {"corto": "CAD1", "largo": "Repetidor Sierra Cádiz", "rol": "ROUTER", "provincia": "ES-CA"},
  "datos": {"reinicios": 7, "ventana_min": 60, "uptime_minimo_s": 84},
  "estado": "abierta",
  "abierta_en": "2026-10-01T15:00:00Z",
  "actualizada_en": "2026-10-01T15:42:00Z"
}
```

*Datos de ejemplo.*

#### Nodos en peligro · `GET /nodes/at-risk`

Un elemento por nodo con alertas abiertas, ordenados por riesgo máximo y antigüedad, con el motivo de cada alerta y su `alert_id`.

| Parámetro | Valores |
|---|---|
| `province` | Código de provincia |
| `min_risk` | Riesgo mínimo; `medio` por defecto |
| `type` | `infraestructura` o `clientes` |

```json
{
  "generated_at": "2026-10-02T12:00:00Z",
  "items": [
    {
      "node": {"id": "!a1b2c3d4", "short": "CAD1", "long": "Repetidor Sierra Cádiz", "role": "ROUTER", "province": "ES-CA"},
      "risk": "alto",
      "type": "infraestructura",
      "since": "2026-10-01T15:00:00Z",
      "reasons": [
        {"rule": "reboot-loop", "risk": "alto", "text": "7 reinicios en la última hora", "alert_id": "01JABCDXYZ7Q8R9S0T1U2V3W4X"},
        {"rule": "battery-low", "risk": "medio", "text": "Batería al 34 %", "alert_id": "01JABCE0000000000000000000"}
      ]
    }
  ]
}
```

*Datos de ejemplo.*

### Códigos de respuesta

| Código | Cuándo |
|---|---|
| `200` | Todo bien (con `stale: true` si son datos de la última copia guardada) |
| `400` | Parámetro no válido (por ejemplo, `window=3d`) |
| `404` | No existe (una alerta o un ranking) |
| `429` | Has superado las 60 peticiones por minuto |
| `503` | La fuente no responde y no hay copia guardada |

### Webhooks

Si quieres las alertas en tu propio sistema en el momento en que ocurren, te las enviamos por webhook: una petición `POST` con JSON a tu URL cada vez que una alerta que pase tus filtros se abre, cambia o se resuelve.

#### Cómo darte de alta

Escribe a {PROJECT_CONTACT} con:

- La URL que recibirá los avisos (tiene que ser `https://`).
- Los filtros que quieras: riesgos, tipos, provincias o nodos concretos.
- Un nombre para identificar tu integración.

Con el alta recibirás un secreto propio para comprobar la firma de cada envío.

#### Qué recibes

| Elemento | Valor |
|---|---|
| Método | `POST`, cuerpo JSON |
| Cuerpo | El cambio completo de la alerta: la alerta, con los mismos campos que en `/alerts`, y qué ha pasado (abierta, actualizada o resuelta) |
| `X-SNM-Transicion` | Id del cambio. Úsalo para descartar duplicados |
| `X-SNM-Firma` | `sha256=` seguido del HMAC-SHA256 del cuerpo calculado con tu secreto |

#### Cómo comprobar la firma

1. Lee el cuerpo tal y como llega, sin volver a formatearlo.
2. Calcula su HMAC-SHA256 con tu secreto.
3. Compáralo con lo que va detrás de `sha256=` en `X-SNM-Firma`, con una comparación de tiempo constante.
4. Si no coincide, descarta la petición.

#### Reintentos

- Responde con un código `2xx` para confirmar que lo has recibido.
- Si falla, lo reintentamos 3 veces: a los 10 segundos, al minuto y a los 5 minutos.
- Tras 20 fallos seguidos, tu destino se desactiva. Escríbenos para reactivarlo.
- Puede llegarte un mismo cambio dos veces si tu servidor tarda en contestar: usa `X-SNM-Transicion` para ignorar los repetidos.

#### Requisitos de tu URL

Solo `https://`. No se admiten direcciones privadas, de red local ni del propio servidor.

### Chat en directo (WebSocket)

Para seguir en directo los mensajes de los canales de chat de la lista, sin pedir nada cada pocos segundos.

- Dirección: `wss://{PROJECT_DOMAIN}/ws/chat`. Sin registro ni clave. Solo lectura: no se puede enviar nada a la malla.
- Al conectar recibes la lista de canales admitidos. Suscríbete con `{"type": "subscribe", "channels": ["Cadiz", "sos"]}` (o conecta con `?channels=Cadiz,sos`) y date de baja con `"unsubscribe"`.
- Al suscribirte recibes los últimos mensajes de ese canal y después cada mensaje nuevo con canal, remitente (id y nombres), texto y hora.
- Solo mensajes de difusión de los canales de la lista: nunca mensajes directos.
- Límites: 10 conexiones por IP. Si no lees los mensajes a tiempo, se cierra la conexión: vuelve a conectar.
- Formato completo y códigos de error: ejemplos de `../../chat-ws/README.md` §4.

### Uso responsable

- Respeta el límite y guarda las respuestas: los datos cambian como mucho cada 30 o 60 segundos.
- No intentes reconstruir la ubicación de nadie a partir de los datos.
- Son datos orientativos: pueden estar incompletos o tener errores.
- La versión va en la ruta (`/v1`).
- Si haces algo con estos datos, nos encantará saberlo: {PROJECT_CONTACT}.

## Datos dinámicos y configuración

| Dato | Origen |
|---|---|
| URL base | `https://{PROJECT_DOMAIN}/api/v1` |
| Contacto | `PROJECT_CONTACT` |
| Límite, CORS, TTL, códigos | `../11-public-api.md`. Si cambian allí, se cambian aquí |
| Ejemplos JSON | Copiados de `../11-public-api.md` y `../../detector-alertas/02-rule-catalog.md`, marcados como ejemplo. Se revisan en cada cambio de la API |
| Webhooks | `../../bots-webhooks/03-webhooks.md` |
| Texto | `resources/contenido/api.md`. Sin datos en vivo |

Los huecos que tenía este borrador están cerrados en las fichas; al maquetar, los ejemplos se copian de ahí:

- Cuerpo, cabeceras y firma HMAC de los webhooks: `../../bots-webhooks/03-webhooks.md`.
- Paginación de `/alerts` (`next_cursor`), errores y caché: `../11-public-api.md`.
- Periodos de `traffic-mix` y ejemplos de `rankings`: `../12-stats-api.md`.
- Ejemplos de `alerts/catalog`, `nodes/*` y `alerts/*`: `../13-nodes-alerts-api.md`.

## Supuestos aplicados

- Datos de la API bajo licencia CC BY 4.0, citando `{PROJECT_NAME}` y `{PROJECT_DOMAIN}`.
- Ejemplos JSON: componente "Bloque de código" de `DESIGN.md`.

---
> Creado: 2026-10-07 · Última revisión: 2026-10-07
