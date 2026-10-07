# 08 · Bots y webhooks

> Tres aplicaciones Python independientes que escuchan el socket de alertas del detector y llevan cada transición a su destino: grupos y canales de Telegram, canales de servidores de Discord y URLs de integradores por `POST` firmado. Los bots responden además a comandos de consulta y de filtros. **Tipo:** 3 desarrollos propios (Python) · **Fase:** 7 (Avisos) · **Complejidad:** media · **Monorepo:** `services/bot-telegram/`, `services/bot-discord/`, `services/webhooks/`

## 1. Contexto

**Punto de partida.** Cuando se construye esta pieza ya están en marcha el detector (`../detector-alertas/`), que emite cada transición de alerta por el socket Unix `/run/snm/alertas.sock`, y el portal (`../portal/`), que sirve la API pública `/api/v1` con el estado de la malla, los routers y el catálogo de alertas.

**Requisitos.**

- Un bot que enganche los eventos de la malla a Telegram y Discord, y un mecanismo para que cualquiera los reciba en tiempo real (webhooks).
- Cada bot es una aplicación independiente. **Envía cada alerta a cada canal donde esté añadido y guarda lo que envía.** En cada canal se activan o desactivan **niveles de riesgo y tipos de aviso con comandos**. Comandos **`status`** (información general de la malla), **`battery`** (batería solo de routers) y **`routers`** (todos los routers con batería, chutil y tx).
- El detector solo detecta y cataloga; las aplicaciones conectadas al socket formatean y envían.
- Página del portal que explica qué hace cada bot y cómo añadirlo: la mantiene `../portal/06-bots-page.md` con los formatos y comandos de esta ficha.


**Quién consume lo que se construye.**

| Aplicación | Consumidor |
|---|---|
| `bot-telegram` | Grupos, supergrupos y canales de Telegram de operadores y usuarios de la malla |
| `bot-discord` | Canales de texto de servidores de Discord |
| `webhooks` | Sistemas de integradores dados de alta por el operador |
| `/health` de las tres | Panel de operadores del portal (`../portal/14-operator-panel.md`), cada minuto |

## 2. Alcance

**Incluye**

- Cliente común del socket de alertas (saludo con cursor, reconexión, latidos) y persistencia del cursor.
- Base de datos propia por aplicación (`bot_telegram`, `bot_discord`, `webhooks`) con destinos, envíos (cola y registro de lo enviado) y cursor.
- Bots: alta y baja de destinos, filtros por destino (riesgos y tipos), envío con hilo (la actualización y la resolución responden al mensaje original), anti-ruido, comandos `/status`, `/battery`, `/routers`, `/levels`, `/types`, `/settings`, `/help` (y `/subscribe`, `/unsubscribe` en Discord).
- Webhooks: destinos en `webhooks.yaml`, filtros, `POST` firmado con HMAC, reintentos, desactivación por fallos, protección contra SSRF.
- `/health` en el puerto 8080, retención de 1 año, contenedores.

**Fuera de esta entrega**

- Canal o grupo de alertas propio del proyecto.
- Alta de webhooks o gestión de destinos desde el panel `/admin` (ampliación posible, fase 9).
- Informe semanal por los bots, bot que responda por radio, otros destinos (Matrix, correo): cada uno sería otra aplicación conectada al mismo socket.
- Elección del tema (topic) en supergrupos de Telegram con temas: las alertas van al tema General.
- Comandos de Discord en mensajes directos.

## 3. Stack y versiones

| Elemento | Versión | Uso |
|---|---|---|
| Python | 3.13 · imagen base `python:3.13-slim-trixie` (Docker Hub), parche fijado al crear (última probada) | Las tres aplicaciones |
| aiohttp | 3.14.x (3.14.3 a 2026-10-03; fijar al crear, PyPI) | Cliente HTTP (API del portal, webhooks) y servidor de `/health` |
| psycopg + psycopg-pool | 3.3.x (3.3.6 y 3.3.3 a 2026-10-03; fijar al crear, PyPI) | PostgreSQL asíncrono |
| aiogram | 3.x (3.31.0 a 2026-10-03; fijar al crear, PyPI) | `bot-telegram` (asíncrono, sobre aiohttp) |
| discord.py | 2.x (2.7.1 a 2026-10-03; fijar al crear, PyPI) | `bot-discord` (asíncrono, sobre aiohttp) |
| PyYAML | 6.0.x (fijar al crear, PyPI) | `webhooks.yaml` |
| pytest + pytest-asyncio | Fijar al crear | Pruebas |
| PostgreSQL | 17 nativo del servidor | Bases `bot_telegram`, `bot_discord`, `webhooks` |

- Dependencias declaradas en `pyproject.toml` y fijadas con lock (`uv.lock`); la imagen se construye con el lock y no instala nada al arrancar.
- Sin ORM: SQL explícito con migraciones numeradas (UT-08.2).
- Registro: JSON por línea a stdout (módulo `logging` estándar con formateador JSON).
- Imágenes propias construidas en el servidor (`docker compose build`), etiquetadas con la versión git; nunca `latest`.

**Núcleo común.** Las tres aplicaciones son independientes (directorio, imagen, base y despliegue propios), pero comparten código: cada una lleva una copia del paquete `nucleo/`. Una prueba comprueba que las copias son idénticas (UT-08.9).

| Archivo de `nucleo/` | bot-telegram | bot-discord | webhooks |
|---|---|---|---|
| `config.py`, `registro.py`, `salud.py`, `base.py` (conexión, migraciones, bloqueo de instancia), `socket_alertas.py` | Sí | Sí | Sí |
| `api_portal.py`, `catalogo.py`, `motor_envios.py`, `formato.py`, `comandos.py` | Sí | Sí | No |

## 4. Contratos

### 4.1 Socket de alertas (entrada de las tres aplicaciones)

| Elemento | Valor |
|---|---|
| Ruta | `/run/snm/alertas.sock` (`ALERTAS_SOCKET`), en el volumen `alertas-socket` |
| Tipo | Unix `SOCK_STREAM`; servidor: `detector-alertas`; varios clientes a la vez |
| Permisos | `0660`, GID `10500` (`ALERTAS_SOCKET_GID`); el contenedor se une a ese grupo |
| Formato | NDJSON UTF-8, un objeto por línea |
| Sentido | Del detector al cliente. Lo único que envía el cliente es el saludo |

Protocolo:

1. Al conectar, el cliente envía una línea de saludo:
   ```json
   {"cliente": "bot-telegram", "desde": "01JABCF0000000000000000000"}
   ```
   `cliente` es `bot-telegram`, `bot-discord` o `webhooks`. `desde` es el último `transicion_id` procesado (tabla `cursor`) o `null` la primera vez.
2. El detector reenvía desde su base las transiciones posteriores a `desde` (máximo 24 h) y sigue en directo.
3. Cada transición:
   ```json
   {"v": 1, "transicion_id": "01JABCF1111111111111111111", "transicion": "abierta", "alerta": { … }}
   ```
   `transicion`: `abierta`, `actualizada` o `resuelta`. Una alerta resuelta que reaparece antes de 1 h se reabre con el mismo `alerta.id` y una transición `abierta`.
4. Latido cada 30 s: `{"latido": "2026-10-03T12:00:00Z"}`. Si en 90 s no llega ninguna línea, el cliente cierra y reconecta con su último `transicion_id`.
5. Si un cliente acumula más de 1.000 mensajes sin leer, el detector lo desconecta; al reconectar recupera lo pendiente.

Objeto `alerta` (claves en español):

```json
{
  "id": "01JABCDXYZ7Q8R9S0T1V2W3X4Y",
  "regla": "reboot-loop",
  "riesgo": "alto",
  "tipo": "infraestructura",
  "mensaje": "CAD1 se ha reiniciado 7 veces en la última hora",
  "nodo": "!a1b2c3d4",
  "nodos": [],
  "nodo_info": {"corto": "CAD1", "largo": "Repetidor Sierra Cádiz", "rol": "ROUTER", "provincia": "ES-CA"},
  "datos": {"reinicios": 7, "ventana_min": 60},
  "estado": "abierta",
  "abierta_en": "2026-10-01T15:00:00Z",
  "actualizada_en": "2026-10-01T15:42:00Z",
  "resuelta_en": null
}
```

`nodo` es `"all"` cuando la alerta afecta a muchos nodos: entonces `nodos` lleva la lista de ids y `nodo_info` es `null`. Campos de `nodo_info` desconocidos llegan a `null`. Fechas ISO 8601 en UTC; ids de alerta y transición, ULID.

**Comportamiento del cliente (`nucleo/socket_alertas.py`)**

| Aspecto | Comportamiento |
|---|---|
| Conexión | `asyncio.open_unix_connection` con límite de línea de 1 MiB (una alerta `all` con 2.000 nodos ocupa ~30 KB) |
| Saludo | Inmediatamente tras conectar, con el cursor leído de la base en ese momento |
| Lectura | Una línea cada vez; las transiciones se procesan **en orden y de una en una**: la siguiente no se lee hasta que la anterior está guardada |
| Latido | Cualquier línea recibida renueva el temporizador de 90 s; el latido no se guarda |
| Validación | Exige `v == 1`, `transicion_id`, `transicion` válida y `alerta.id`, `regla`, `riesgo`, `tipo`, `mensaje`, `nodo`. Una línea inválida (JSON roto, `v` desconocida, campos que faltan) se registra como error y **se salta avanzando el cursor**: nunca bloquea la cola |
| Cursor | Se actualiza en la **misma transacción** que guarda el resultado de procesar la transición (envíos o entregas). Sin transacción confirmada, el cursor no avanza |
| Duplicados | Una transición repetida tras reconectar no produce un segundo envío: restricción única (`destino_id`, `transicion_id`) |
| Reconexión | Socket inexistente, conexión rechazada, EOF o 90 s sin líneas → espera 1, 2, 4, 8, 16 y 30 s (máximo, con ±20 % de azar) y vuelve a saludar. La espera se reinicia al recibir la primera línea |
| Parada | `SIGTERM`: deja de leer, termina la transición en curso, cierra el socket |

### 4.2 API del portal (comandos de los bots)

Base: `PORTAL_API_URL` = `https://mesh.example.org/api/v1`. Solo `GET`, sin autenticación, JSON con claves en inglés, 60 peticiones/min por IP. Las respuestas pueden llevar `stale: true` (el portal sirve su última copia porque la fuente no responde).

| Endpoint | Lo usan | Campos que lee el bot |
|---|---|---|
| `GET /stats/summary` | `/status` | `generated_at`, `nodes_active_24h`, `nodes_active_7d`, `routers_active_24h`, `gateways_publishing`, `channel_utilization.andalucia_avg`, `channel_utilization.provinces[].code` y `.avg`, `alerts_open.by_risk`, `alerts_open.by_type`, `stale` |
| `GET /routers?province=` | `/battery`, `/routers` | `generated_at`, `items[]`: `id`, `short`, `long`, `role`, `province`, `battery.level`, `battery.voltage`, `battery.powered`, `battery.at`, `chutil`, `tx`, `last_seen`; `stale` |
| `GET /alerts/catalog` | Validación de `/levels` y `/types`, nombres visibles de riesgos, tipos y reglas | `risks[].id`, `risks[].name` (en orden de menor a mayor), `types[].id`, `types[].name`, `rules[].id`, `rules[].name` |

Ejemplos (datos inventados):

```json
{
  "generated_at": "2026-10-03T12:00:00Z",
  "nodes_active_24h": 412,
  "nodes_active_7d": 655,
  "routers_active_24h": 38,
  "gateways_publishing": 9,
  "channel_utilization": {
    "andalucia_avg": 18.6,
    "provinces": [{"code": "ES-CA", "avg": 31.2}, {"code": "ES-SE", "avg": 12.0}]
  },
  "alerts_open": {
    "by_risk": {"bajo": 4, "medio": 7, "alto": 1},
    "by_type": {"infraestructura": 9, "clientes": 3}
  }
}
```

```json
{
  "generated_at": "2026-10-03T12:00:00Z",
  "items": [
    {
      "id": "!a1b2c3d4", "short": "CAD1", "long": "Repetidor Sierra Cádiz", "role": "ROUTER", "province": "ES-CA",
      "battery": {"level": 34, "voltage": 3.71, "powered": false, "at": "2026-10-03T11:40:00Z"},
      "chutil": 22.5, "tx": 3.1, "last_seen": "2026-10-03T11:58:00Z"
    }
  ]
}
```

```json
{
  "generated_at": "2026-10-03T12:00:00Z",
  "risks": [
    {"id": "bajo", "name": "Bajo", "description": "Conviene saberlo; no hay daño inmediato"},
    {"id": "medio", "name": "Medio", "description": "Riesgo real para un nodo o una zona; conviene actuar"},
    {"id": "alto", "name": "Alto", "description": "Fallo activo o daño a la malla"}
  ],
  "types": [
    {"id": "infraestructura", "name": "Infraestructura", "description": "Routers, gateways y lo que degrada la malla"},
    {"id": "clientes", "name": "Clientes", "description": "Nodos que no son infraestructura"}
  ],
  "rules": [{"id": "reboot-loop", "name": "Bucle de reinicio", "description": "…"}]
}
```

Reglas del cliente (`nucleo/api_portal.py`):

- Timeout 5 s por petición; sin reintentos en comandos (el usuario puede repetir).
- Caché en memoria por URL completa: 30 s (`API_CACHE_S`). `/battery` y `/routers` comparten la misma petición (`/routers?province=…`); el orden y las columnas se aplican en el bot.
- `User-Agent: ${PROJECT_NAME} bot-telegram/<versión> (+https://${PROJECT_DOMAIN}/bots; ${PROJECT_CONTACT})` (o `bot-discord`).
- Un campo que falta o es `null` se muestra como `—`; la respuesta nunca falla por un campo ausente.
- Error de red, timeout o `5xx`: el comando responde el texto de "datos no disponibles" (4.4).
- **Catálogo** (`nucleo/catalogo.py`): se carga al arrancar y cada hora (TTL del portal). Si no se puede cargar, se usa el último bueno en memoria y, si no hay ninguno, el catálogo mínimo de `integration.md`: riesgos `bajo`, `medio`, `alto` y tipos `infraestructura`, `clientes`; se reintenta cada 5 min. Una alerta con un riesgo, tipo o regla que no está en el catálogo se procesa igual (los filtros comparan cadenas) y dispara una recarga.

### 4.3 Formato de una alerta (bots)

El núcleo construye un **aviso neutro** y cada plataforma lo pinta: Telegram como texto HTML, Discord como *embed* (ver sus módulos).

| Riesgo / estado | Icono | Color (token de `../DESIGN.md`) |
|---|---|---|
| `alto` | 🔴 | `critico` |
| `medio` | 🟠 | `aviso` |
| `bajo` | 🔵 | `info` |
| Resuelta | ✅ | `correcto` |
| Riesgo nuevo del catálogo sin icono asignado | ⚪ | `info` |

Nombres visibles: riesgo en mayúsculas (`risks[].name`), tipo (`types[].name`) y regla (`rules[].name`) del catálogo; si falta, el id. Provincia por nombre: `ES-AL` Almería, `ES-CA` Cádiz, `ES-CO` Córdoba, `ES-GR` Granada, `ES-H` Huelva, `ES-J` Jaén, `ES-MA` Málaga, `ES-SE` Sevilla, `FUERA` Fuera de Andalucía. Horas en `TZ` (Europe/Madrid): `HH:MM` si es hoy, `dd/mm HH:MM` si no. **Nunca coordenadas:** solo provincia.

Línea de sujeto: `{corto} ({largo}) · {nodo} · {rol} · {provincia}`, omitiendo lo que sea `null` (sin nombres queda el id). Con `nodo: "all"`: `{N} nodos afectados` (N = longitud de `nodos`); no se listan.

| Caso | Texto |
|---|---|
| Apertura (inicia hilo) | `🔴 ALTO · Infraestructura · Bucle de reinicio` / sujeto / `mensaje` / enlace |
| Reapertura (en el hilo) | `🔴 REABIERTA · ALTO · Bucle de reinicio` / `mensaje` / enlace |
| Cambio de riesgo (en el hilo) | `🔴 SUBE A ALTO · Bucle de reinicio` o `🔵 BAJA A BAJO · …` (según el orden del catálogo) / `mensaje` / enlace |
| Resolución (en el hilo) | `✅ RESUELTA · Bucle de reinicio` / sujeto / `Abierta 15:00 · resuelta 17:12 (2 h 12 min)` / enlace |
| Resumen de ráfaga | `🔴 7 alertas más de Bucle de reinicio en 2 min` / `CAD2, CAD3, SEV1, … (+2)` (hasta 20 etiquetas) / `https://${PROJECT_DOMAIN}/alertas` |
| Con retraso | Línea añadida si la transición tenía más de 5 min al procesarse: `⏱ Aviso con retraso: ocurrió el 03/10 04:12` |

Enlace: `https://${PROJECT_DOMAIN}/alertas/<alerta.id>`. `mensaje` se recorta a 1.000 caracteres. Ejemplo de apertura:

```
🔴 ALTO · Infraestructura · Bucle de reinicio
CAD1 (Repetidor Sierra Cádiz) · !a1b2c3d4 · ROUTER · Cádiz
CAD1 se ha reiniciado 7 veces en la última hora
https://mesh.example.org/alertas/01JABCDXYZ7Q8R9S0T1V2W3X4Y
```

Alerta que afecta a muchos nodos (un solo mensaje):

```
🔴 ALTO · Infraestructura · Ráfaga masiva
214 nodos afectados
214 nodos han emitido a la vez en 2 minutos
https://mesh.example.org/alertas/01JABD0000000000000000000A
```

### 4.4 Comandos comunes (bots)

| Comando | Quién | Dónde | Fuente | Responde |
|---|---|---|---|---|
| `/status` | Cualquiera | Grupos, canales y privado (Telegram); servidores (Discord) | `GET /stats/summary` | Estado general de la malla |
| `/battery [provincia]` | Cualquiera | Ídem | `GET /routers?province=` | Batería **solo de routers**, de menor a mayor |
| `/routers [provincia]` | Cualquiera | Ídem | `GET /routers?province=` | Todos los routers con batería, `chutil` y `tx` |
| `/levels [riesgos…]` | Ver: cualquiera. Cambiar: administradores | Destinos (no privado) | Catálogo | Riesgos activos en el destino, o los cambia |
| `/types [tipos…]` | Igual que `/levels` | Destinos | Catálogo | Tipos activos, o los cambia |
| `/settings` | Cualquiera | Destinos | Base propia | Filtros, alta y avisos enviados |
| `/help` | Cualquiera | Todos | — | Ayuda breve y enlace a `https://${PROJECT_DOMAIN}/bots` |

"Administradores" = administradores del grupo o quien publica en el canal (Telegram), o quien tiene el permiso de gestionar canales en ese canal (Discord). `/subscribe` y `/unsubscribe` existen solo en Discord (`02-bot-discord.md`).

**Argumentos**

- `provincia`: nombre (sin importar tildes ni mayúsculas: `cadiz`, `Cádiz`), código (`ES-CA`, `ca`) o `fuera`. Sin argumento: todas.
- `/levels` y `/types`: valores separados por espacios o comas, sin distinguir mayúsculas, validados contra el catálogo; `todos` = todos los del catálogo. Al menos un valor. Sin argumentos: solo muestra.

**Respuestas** (los números con coma decimal y punto de millares):

`/status`

```
📡 Estado de la malla · 12:00
Nodos activos: 412 en 24 h · 655 en 7 días
Routers activos (24 h): 38
Gateways publicando: 9
Carga media del canal en Andalucía: 18,6 %
🟠 Cádiz 31,2 % · 🟢 Sevilla 12,0 % · ⚪ Jaén sin datos
Alertas abiertas: 1 alto · 7 medio · 4 bajo
Infraestructura 9 · Clientes 3
https://mesh.example.org
```

Provincias de mayor a menor carga, icono por los cortes del mapa (🟢 ≤ 20 %, 🟠 > 20 % y < 40 %, 🔴 ≥ 40 %, ⚪ sin datos). Riesgos de mayor a menor según el catálogo.

`/battery`

```
🔋 Batería de los routers · de menor a mayor
🔴 CAD1 · Cádiz · 18 % (3,52 V) · hace 20 min
🟠 SEV2 · Sevilla · 34 % · hace 2 h
🟢 MAL4 · Málaga · 81 % · hace 5 min
Alimentados: CAD3, HUE1
Sin dato de batería: GRA2
```

Icono por los cortes de la regla `battery-low` para infraestructura: 🔴 < 20 %, 🟠 < 40 %, 🟢 el resto. `powered: true` va a "Alimentados".

`/routers`

```
📶 Routers vistos en 7 días · 38
Cádiz
CAD1 · 🔋 34 % · chutil 22,5 % · tx 3,1 % · hace 2 min
CAD3 · 🔌 · chutil 8,0 % · tx 1,2 % · hace 1 min
Sevilla
SEV2 · 🔋 34 % · chutil — · tx — · hace 3 h
```

Agrupado por provincia (orden alfabético del nombre) y, dentro, por nombre corto. 🔌 = alimentado.

`/levels medio alto` → `Riesgos activos en este chat: medio, alto.` · `/types` → `Tipos activos en este chat: infraestructura (por defecto).`

`/settings`

```
⚙️ Configuración de este chat
Riesgos: medio, alto (por defecto)
Tipos: infraestructura (por defecto)
Activo desde el 02/10/2026
Avisos enviados aquí: 37 (último: hoy 12:03)
```

`/help`

```
Bot de alertas de Sur Nodos en Mallas
/status — estado general de la malla
/battery [provincia] — batería de los routers
/routers [provincia] — routers con batería, chutil y tx
/levels [riesgos] — ver o cambiar los riesgos (administradores)
/types [tipos] — ver o cambiar los tipos (administradores)
/settings — configuración de este chat
Riesgos: bajo, medio, alto · Tipos: infraestructura, clientes
Más información: https://mesh.example.org/bots
```

(Nombre del proyecto desde `PROJECT_NAME`; riesgos y tipos desde el catálogo.)

**Errores**

| Caso | Respuesta |
|---|---|
| API caída, timeout o `5xx` | `No puedo consultar los datos ahora mismo. Las alertas siguen llegando con normalidad.` |
| `stale: true` | Se responde y se añade `(datos de las 11:52; el portal no ha podido actualizarlos)` |
| Valor no válido | `Valor no válido: «muy-alto». Riesgos posibles: bajo, medio, alto (o todos).` |
| Sin permiso | `Solo los administradores pueden cambiar los filtros.` |
| Provincia no reconocida | `Provincia no reconocida. Usa el nombre o el código: Almería (ES-AL), Cádiz (ES-CA), …` |
| Comando de destino fuera de un destino | `Este comando solo funciona en grupos y canales.` (Telegram) / `Este canal no recibe alertas. Usa /subscribe.` (Discord) |

**Longitud.** Respuestas largas (p. ej. `/routers` con 100 routers) se parten por líneas en varios mensajes, como máximo 3; si no cabe, la última línea dice `… y 41 routers más. Usa /routers <provincia>.`

### 4.5 Filtros por destino

| Filtro | Valores | Por defecto al añadir el bot |
|---|---|---|
| Riesgos | Los de `risks[]` del catálogo (`bajo`, `medio`, `alto`) | `medio`, `alto` (`BOT_RIESGOS_DEFECTO`) |
| Tipos | Los de `types[]` del catálogo (`infraestructura`, `clientes`) | `infraestructura` (`BOT_TIPOS_DEFECTO`) |

- Un destino que nunca ha cambiado un filtro guarda `NULL` y usa el valor por defecto vigente; `/settings` lo muestra como "(por defecto)". Cambiar el defecto en `.env` cambia todos esos destinos.
- Un riesgo o tipo nuevo del catálogo no se añade solo a los destinos con filtros propios: aparece en `/help` y en el mensaje de error de `/levels`, y cada destino decide.
- Cambiar los filtros de un destino no afecta a ningún otro.

### 4.6 Reglas de envío y anti-ruido (bots)

Decisión por destino activo para cada transición (`nucleo/motor_envios.py`). "Hilo" = el último mensaje enviado de esa alerta en ese destino; "pasa" = riesgo y tipo de la alerta dentro de los filtros del destino.

| Transición | Sin hilo en el destino | Con hilo en el destino |
|---|---|---|
| `abierta` | Si pasa → apertura (inicia hilo). Si no → nada | Reapertura en el hilo, pase o no los filtros |
| `actualizada` | Si pasa → apertura con el riesgo nuevo (inicia hilo). Si no → nada | Respuesta en el hilo **solo si el riesgo cambia** respecto al último enviado; si no → nada |
| `resuelta` | Nada | Resolución en el hilo |

Una alerta que ya tiene hilo en un destino se sigue hasta su resolución aunque deje de pasar los filtros (si baja de `alto` a `medio`, quien la recibió se entera de que bajó y de que se resolvió).

| Mecanismo | Valor inicial | Variable |
|---|---|---|
| Agrupación | Si en los últimos 2 min ya salieron 5 aperturas de la **misma regla** a un destino, las siguientes no se envían sueltas: se acumulan en un **resumen** que sale 2 min después de la primera acumulada. Las alertas resumidas no reciben después mensajes sueltos de cambio ni de resolución (su estado está en la web) | `BOT_AGRUPAR_UMBRAL=5`, `BOT_AGRUPAR_VENTANA_S=120` |
| Límite por destino | 10 mensajes/min y al menos 1 s entre dos mensajes del mismo destino; lo que exceda espera en la cola | `BOT_MAX_MENSAJES_MINUTO=10` |
| Límite global | 25 mensajes/s entre todos los destinos | `BOT_MAX_MENSAJES_SEGUNDO=25` |
| Cambios sin cambio de riesgo | No se envían | — |
| Comandos de consulta | Como mucho uno cada 5 s por chat; el resto se ignora | — |
| Cola por destino | Persistente (tabla `envio`), FIFO por destino; un destino que falla no bloquea a los demás | — |
| Errores del destino | Un mensaje rechazado por el destino (sin permiso, etc.) se reintenta a los 10 s y 60 s y queda `fallido`. Si un destino lleva 3 días fallando sin un solo envío correcto, se desactiva | `BOT_DIAS_FALLO_DESACTIVAR=3` |
| Errores de la plataforma | Red, `5xx` o límite de la plataforma: reintento con espera creciente (1 s … 5 min) respetando `retry_after`; un mensaje que no sale en 24 h queda `caducado` | — |
| Expulsión | Baja inmediata del destino; sus pendientes pasan a `caducado` | — |

**Ciclo de un envío.** Al procesar la transición se decide, se **pinta el contenido** y se inserta la fila `envio` (`pendiente` o `agrupado`) en la misma transacción que el cursor. Un único bucle de envío toma las filas `pendiente` con `programado_para <= now()` en orden (`programado_para`, `id`), respetando FIFO y límites por destino; el mensaje al que responder (hilo) se busca **en el momento de enviar**. Se despierta al insertar y, como mínimo, cada segundo.

**Garantía.** Al menos una vez: si el proceso muere entre la confirmación de la plataforma y el `UPDATE` a `enviado`, ese mensaje se repetirá al arrancar (ventana de milisegundos, aceptado).

### 4.7 Salud

`GET http://<contenedor>:8080/health`, solo en la red `mesh`.

```json
{
  "ok": true,
  "servicio": "bot-telegram",
  "version": "1.0.0",
  "socket": {"conectado": true, "ultima_linea": "2026-10-03T12:00:30Z", "cursor": "01JABCF1111111111111111111"},
  "base": {"ok": true},
  "plataforma": {"ok": true, "ultimo_ok": "2026-10-03T12:00:41Z"},
  "cola": {"pendientes": 0, "mas_antiguo_s": 0},
  "destinos_activos": 12
}
```

`200` con `ok: true`, o `503` con `ok: false` y `motivo` cuando: la base no responde a `SELECT 1` (timeout 2 s); el socket lleva más de 120 s sin conexión o sin líneas; la plataforma lleva más de 5 min fallando (bots); o el pendiente más antiguo pasa de 30 min (bots). `webhooks` sustituye `plataforma` y `cola` por los suyos (`03-webhooks.md`).

## 5. Configuración

Cada `compose.yaml` carga `/srv/comun/.env` y después su `.env` propio.

| Variable | Valor o ejemplo | Común o propia | Secreto |
|---|---|---|---|
| `PROJECT_NAME` | `Sur Nodos en Mallas` | Común | No |
| `PROJECT_DOMAIN` | `mesh.example.org` | Común | No |
| `PROJECT_CONTACT` | `public@raupulus.dev` | Común | No |
| `TZ` | `Europe/Madrid` | Común | No |
| `DB_HOST` / `DB_PORT` | `172.30.0.1` / `5432` | Común | No |
| `ALERTAS_SOCKET` / `ALERTAS_SOCKET_GID` | `/run/snm/alertas.sock` / `10500` | Común | No |
| `PORTAL_API_URL` | `https://mesh.example.org/api/v1` | Común (solo bots) | No |
| `DB_NAME` / `DB_USER` | `bot_telegram` · `bot_discord` · `webhooks` | Propia | No |
| `DB_PASSWORD` | — | Propia | **Sí** |
| `LOG_LEVEL` | `INFO` | Propia | No |
| `RETENCION_DIAS` | `365` | Propia | No |
| `BOT_RIESGOS_DEFECTO` | `medio,alto` | Propia (bots) | No |
| `BOT_TIPOS_DEFECTO` | `infraestructura` | Propia (bots) | No |
| `BOT_MAX_MENSAJES_MINUTO` | `10` | Propia (bots) | No |
| `BOT_MAX_MENSAJES_SEGUNDO` | `25` | Propia (bots) | No |
| `BOT_AGRUPAR_UMBRAL` / `BOT_AGRUPAR_VENTANA_S` | `5` / `120` | Propia (bots) | No |
| `BOT_DIAS_FALLO_DESACTIVAR` | `3` | Propia (bots) | No |
| `API_CACHE_S` / `API_TIMEOUT_S` | `30` / `5` | Propia (bots) | No |

Las variables de cada plataforma (`TELEGRAM_BOT_TOKEN`, `DISCORD_BOT_TOKEN`, `DISCORD_APP_ID`, `WEBHOOKS_*`, `WEBHOOK_<NOMBRE>_SECRETO`) están en su módulo. Al arrancar se valida todo: una variable obligatoria que falta o un valor inválido → el proceso sale con código 1 y un mensaje que nombra la variable (sin mostrar su valor). Los secretos nunca se registran.

## 6. Datos

**Bases.** PostgreSQL 17 nativo: `bot_telegram`, `bot_discord` y `webhooks`, cada una con su rol propietario homónimo, acceso desde `172.30.0.0/24` con `scram-sha-256`. Las crea `../infrastructure/02-postgresql.md`; como referencia:

```sql
CREATE ROLE bot_telegram LOGIN PASSWORD '…';      -- contraseña en /srv/bot-telegram/.env
CREATE DATABASE bot_telegram OWNER bot_telegram;
REVOKE ALL ON DATABASE bot_telegram FROM PUBLIC;
```

```text
# pg_hba.conf
host  bot_telegram  bot_telegram  172.30.0.0/24  scram-sha-256
host  bot_discord   bot_discord   172.30.0.0/24  scram-sha-256
host  webhooks      webhooks      172.30.0.0/24  scram-sha-256
```

Ningún otro servicio lee estas bases (el panel solo usa `/health`).

**Esquema común de los bots** (las tablas de `webhooks` están en `03-webhooks.md`):

`destino` — un chat o canal donde el bot publica.

| Columna | Tipo | Notas |
|---|---|---|
| `id` | `bigint` identidad, PK | |
| `plataforma_id` | `bigint` único, no nulo | Telegram: `chat.id`; Discord: id del canal |
| `servidor_id` | `bigint` nulo | Discord: id del servidor; Telegram: `NULL` |
| `clase` | `text` | Telegram: `group`, `supergroup`, `channel`; Discord: `texto`, `anuncios` |
| `riesgos` | `text[]` nulo | `NULL` = `BOT_RIESGOS_DEFECTO` vigente |
| `tipos` | `text[]` nulo | `NULL` = `BOT_TIPOS_DEFECTO` vigente |
| `activo` | `boolean` | |
| `motivo_baja` | `text` nulo | `expulsado`, `sin_permiso`, `fallos`, `unsubscribe`, `canal_borrado`, `chat_inexistente` |
| `alta_en` | `timestamptz` | Primera alta o última reactivación |
| `baja_en` | `timestamptz` nulo | |
| `fallando_desde` | `timestamptz` nulo | Primer error de destino de la racha actual; se borra con el primer envío correcto |
| `actualizado_en` | `timestamptz` | |

`envio` — cola y registro de lo enviado (guarda lo que envía).

| Columna | Tipo | Notas |
|---|---|---|
| `id` | `bigint` identidad, PK | |
| `destino_id` | `bigint` FK `destino` (`ON DELETE CASCADE`) | |
| `chat_envio_id` | `bigint` | Chat o canal donde salió (en Telegram cambia si el grupo migra a supergrupo) |
| `alerta_id` | `text` nulo | ULID; `NULL` en resúmenes |
| `transicion_id` | `text` nulo | ULID; `NULL` en resúmenes |
| `transicion` | `text` | `abierta`, `actualizada`, `resuelta`, `resumen` |
| `inicia_hilo` | `boolean` | `true` en aperturas (las que cuenta la agrupación) |
| `regla`, `riesgo`, `tipo` | `text` | De la alerta en esa transición |
| `etiqueta` | `text` | Nombre corto o id del nodo (`all` → `N nodos`), para los resúmenes |
| `estado` | `text` | `pendiente`, `enviado`, `agrupado`, `fallido`, `caducado` |
| `resumen_id` | `bigint` nulo FK `envio` | Resumen que incluye esta fila (`agrupado`) |
| `programado_para` | `timestamptz` | Cuándo puede salir (reintentos y resúmenes) |
| `intentos` | `smallint` | |
| `mensaje_id` | `bigint` nulo | `message_id` de Telegram o id del mensaje de Discord |
| `contenido` | `jsonb` | Lo enviado: Telegram `{"text", "parse_mode"}`; Discord `{"embeds": […]}` |
| `ultimo_error` | `text` nulo | Recortado a 500 caracteres |
| `creado_en` / `enviado_en` | `timestamptz` | |

Restricciones e índices: `UNIQUE (destino_id, transicion_id)`; índice parcial `(programado_para, id) WHERE estado = 'pendiente'`; `(destino_id, alerta_id, id DESC)` para encontrar el hilo; `(creado_en)` para la retención.

`cursor` — último `transicion_id` procesado.

| Columna | Tipo | Notas |
|---|---|---|
| `cliente` | `text` PK | `bot-telegram`, `bot-discord`, `webhooks` |
| `transicion_id` | `text` no nulo | |
| `actualizado_en` | `timestamptz` | |

`esquema_migraciones` (`version int` PK, `aplicada_en timestamptz`): control de migraciones.

**Privacidad.** Solo se guardan ids de chat, canal y servidor, la clase, los filtros y los avisos enviados. **No** se guardan nombres de grupos o canales, ids ni nombres de usuarios, ni el texto de los mensajes de la gente: los comandos se procesan en memoria. Coincide con lo que dice la página de bots.

**Retención** (tarea diaria a las 04:30 hora local, UT-08.8): `envio` 1 año (`RETENCION_DIAS`); `destino` inactivo con `baja_en` de hace más de 1 año se borra con sus envíos. El cursor y los destinos activos no caducan.

**Volumen estimado** (peor caso): 50 alertas/día × 20 destinos × 2 mensajes × 365 días ≈ 730.000 filas × ~0,6 KB ≈ 450 MB por bot; lo normal es un orden de magnitud menos. Dentro de "< 1 GB" de `overview.md` para alertas, bots y webhooks juntos en el caso real; si se acerca al peor caso, bajar `RETENCION_DIAS`.

**Volúmenes Docker.** Solo `alertas-socket` (compartido, de solo lectura para estas aplicaciones) y, en `webhooks`, el archivo `webhooks.yaml` montado de solo lectura. Nada más persiste fuera de PostgreSQL.

## 7. Unidades de trabajo

### Módulos

| # | Módulo | Qué es | Complejidad |
|---|---|---|---|
| 08.1 | [`01-bot-telegram.md`](01-bot-telegram.md) | Bot de Telegram: *long polling*, grupos, supergrupos y canales, comandos, migración, expulsión | Media |
| 08.2 | [`02-bot-discord.md`](02-bot-discord.md) | Bot de Discord: gateway, comandos de barra, `/subscribe`, *embeds* | Media |
| 08.3 | [`03-webhooks.md`](03-webhooks.md) | `POST` firmado a URLs registradas, reintentos y protección SSRF | Baja-media |

Orden recomendado: UT-08.1 → UT-08.4 y UT-08.8 (núcleo), después `03-webhooks.md` (el más sencillo, valida núcleo y socket), luego UT-08.5 → UT-08.7 y los dos bots.

### UT-08.1 — Esqueleto común de servicio

- **Comportamiento:** arranque, configuración, registro, bloqueo de instancia única, servidor de salud y parada ordenada idénticos en las tres aplicaciones.
- **Detalle:** `nucleo/config.py` lee y valida variables (5); `nucleo/registro.py` emite JSON por línea (`ts`, `nivel`, `servicio`, `evento`, campos); `nucleo/salud.py` sirve `GET /health` en `0.0.0.0:8080` con aiohttp; bloqueo `pg_try_advisory_lock` con una constante por aplicación al arrancar: si no se obtiene, sale con código 1 ("otra instancia activa"). `SIGTERM`: deja de leer el socket, termina el envío en curso (máximo 10 s), cierra conexiones.
- **Casos borde:** variable obligatoria ausente; PostgreSQL caído al arrancar (reintenta cada 5 s, `/health` en `503`, sin salir); dos contenedores con la misma base.
- **Aceptación:** sin `DB_PASSWORD` sale con código 1 y el mensaje nombra la variable; con dos instancias, la segunda sale; ningún registro contiene tokens, secretos ni URLs con token.

### UT-08.2 — Base de datos y migraciones

- **Comportamiento:** al arrancar se aplican en orden las migraciones pendientes de `migrations/NNN-descripcion.sql` dentro de una transacción cada una, registradas en `esquema_migraciones`.
- **Detalle:** pool de psycopg (mínimo 1, máximo 5 conexiones); `application_name` = nombre del servicio; la migración 001 crea el esquema común (6) y las propias de cada plataforma; reintento con espera creciente (1 s … 30 s) ante pérdida de conexión.
- **Casos borde:** migración fallida (sale con código 1 sin aplicar nada de ella); base restaurada de una copia (las migraciones ya aplicadas no se repiten).
- **Aceptación:** sobre una base vacía crea todo el esquema; un segundo arranque no aplica nada; parar PostgreSQL 1 min no tumba el proceso y al volver sigue procesando.

### UT-08.3 — Cliente del socket de alertas

- **Comportamiento:** el de 4.1.
- **Detalle:** `nucleo/socket_alertas.py` expone un bucle que llama a un manejador asíncrono por transición; el manejador recibe la conexión de base y hace todo en una transacción que incluye `UPSERT` del cursor. Estado expuesto a `/health`: conectado, hora de la última línea, cursor.
- **Casos borde:** el socket no existe aún (detector sin desplegar); latidos que dejan de llegar con la conexión abierta; línea de 1 MiB; JSON inválido; `v: 2`; la misma transición dos veces; la base cae a mitad de procesar (la transición se reprocesa al reconectar).
- **Aceptación:** con un servidor de socket de prueba: recibe el saludo con el cursor correcto; tras 90 s sin líneas reconecta; una línea inválida se registra, avanza el cursor y no detiene el bucle; matar el proceso en mitad de una transición no la pierde ni la duplica.

### UT-08.4 — Catálogo y cliente de la API del portal

- **Comportamiento:** el de 4.2.
- **Detalle:** cliente aiohttp con `User-Agent` propio, timeout 5 s, caché por URL de 30 s; catálogo con recarga horaria, último bueno en memoria y catálogo mínimo de reserva; funciones de nombres visibles (riesgo, tipo, regla, provincia) y orden de riesgos.
- **Casos borde:** respuesta sin `risks`; catálogo con un riesgo nuevo (`critico`); `stale: true`; `429` del portal (se trata como no disponible).
- **Aceptación:** con el portal caído, `/levels alto` se valida con el catálogo mínimo; con un catálogo que añade `critico`, `/levels critico` se acepta sin reiniciar tras la recarga.

### UT-08.5 — Motor de envío de los bots

- **Comportamiento:** el de 4.6: decisión por destino, agrupación, cola persistente, límites, clasificación de errores y desactivación.
- **Detalle:** `nucleo/motor_envios.py` define la interfaz que implementa cada plataforma: `enviar(destino, contenido, responder_a) → mensaje_id`, que lanza errores clasificados (`ErrorTransitorio(retry_after)`, `ErrorDestino`, `DestinoPerdido`, `DestinoMigrado(nuevo_id)`). El motor decide, pinta (UT-08.6), inserta, programa, reintenta y desactiva; la plataforma solo traduce.
- **Casos borde:** la resolución llega cuando la apertura aún está pendiente por el límite (sale después, como respuesta); el mensaje original se borró (la respuesta sale sin hilo); 30 aperturas de la misma regla en 1 min (5 sueltas + 1 resumen); alerta resumida que se resuelve (nada); destino expulsado con 12 pendientes (`caducado`); destino que pasa de fallar a funcionar (se borra `fallando_desde`).
- **Aceptación:** pruebas con una plataforma simulada que cubren cada fila de las tablas de 4.6; un reinicio con pendientes los envía sin duplicar; nunca se superan 10 mensajes/min ni se envían dos mensajes al mismo destino con menos de 1 s.

### UT-08.6 — Aviso neutro y textos

- **Comportamiento:** convierte (transición, caso, catálogo) en un aviso neutro (`icono`, `titulo`, `lineas`, `enlace`, `color`, `fecha`) según 4.3; los resúmenes se pintan al enviarse a partir de `etiqueta` y `riesgo` de las filas agrupadas.
- **Detalle:** todos los textos en un único módulo (`formato.py`) para que la página de bots los copie; formato de números y horas en español y `TZ`; recorte de `mensaje` a 1.000 caracteres; nombres de provincia.
- **Casos borde:** `nodo_info` `null`; nombres con `<`, `&` o emojis; riesgo sin icono; `resuelta_en` `null` en una resolución (se usa `actualizada_en`); alerta `all` con `nodos` vacío.
- **Aceptación:** pruebas de instantánea (*snapshot*) con los ejemplos de 4.3 y 4.4; ningún aviso contiene coordenadas ni el campo `datos` en bruto.

### UT-08.7 — Comandos comunes

- **Comportamiento:** el de 4.4, independiente de la plataforma.
- **Detalle:** `nucleo/comandos.py` recibe (comando, argumentos, contexto: destino o privado, `es_admin`) y devuelve texto o lista de textos; la plataforma resuelve `es_admin`, publica la respuesta y aplica sus reglas (canales, efímeros). `/levels` y `/types` con argumentos actualizan `destino` y responden con el estado nuevo; `todos` guarda la lista completa del catálogo; volver exactamente al defecto guarda `NULL`.
- **Casos borde:** argumentos repetidos o con comas (`medio,alto alto`); `/levels todos`; provincia `fuera`; `/routers` con 0 routers ("No hay routers vistos en los últimos 7 días."); respuesta que no cabe (partido en 3 mensajes).
- **Aceptación:** con respuestas de la API simuladas se obtienen exactamente los textos de 4.4; un no administrador no cambia nada.

### UT-08.8 — Retención

- **Comportamiento:** tarea diaria a las 04:30 hora local (y una vez al arrancar si la última pasada tiene más de 24 h).
- **Detalle:** borrado por lotes de 5.000 filas: `envio` con `creado_en` anterior a `RETENCION_DIAS`; `destino` inactivo con `baja_en` anterior a `RETENCION_DIAS`. En `webhooks`, `entrega` con la misma regla. Registra cuántas filas borra.
- **Aceptación:** con datos sembrados de 400 días, tras la pasada no queda nada anterior a 365 días y los destinos activos siguen.

### UT-08.9 — Imagen y compose

- **Comportamiento:** cada aplicación tiene `Dockerfile`, `compose.yaml`, `.env.example`, `Dockerfile`, `compose.yaml` y `.env.example`.
- **Detalle:** imagen sobre `python:3.13-slim-trixie` (versión fijada), usuario `app` UID `10501` sin privilegios, `WORKDIR /app`, dependencias del lock; antes de etiquetar: lint, pruebas y comprobación de que `nucleo/` es idéntico en las tres (hash de cada archivo compartido según la tabla de 3). `.env.example` con todas las variables de esta ficha y su módulo, sin valores secretos.
- **Aceptación:** cambiar un archivo de `nucleo/` en una sola aplicación hace fallar las pruebas; la imagen arranca con sistema de archivos de solo lectura.

## 8. Despliegue

| Contenedor | Imagen | Redes | Volúmenes | Puertos | Publicación | Healthcheck |
|---|---|---|---|---|---|---|
| `bot-telegram` | Propia `snm-bot-telegram:<versión>` (construida en el servidor) | `mesh` | `alertas-socket:/run/snm:ro` | Ninguno | No se publica | `/health` en 8080 cada 30 s |
| `bot-discord` | Propia `bot-discord:<versión>` | `mesh` | `alertas-socket:/run/snm:ro` | Ninguno | No se publica | Ídem |
| `webhooks` | Propia `webhooks:<versión>` | `mesh` | `alertas-socket:/run/snm:ro`, `./webhooks.yaml:/app/config/webhooks.yaml:ro` | Ninguno | No se publica | Ídem |

- Salida a internet por la red `mesh` (bridge sin `internal`): `api.telegram.org`, `discord.com` y `gateway.discord.gg`, las URLs de los webhooks y `PROJECT_DOMAIN` (API del portal). Ninguna entrada desde internet.
- El socket se monta `:ro`: conectar a un socket Unix no necesita escribir en el sistema de archivos; el permiso lo da el GID `10500`.

Fragmento de `compose.yaml` (patrón común; `webhooks` añade su archivo de configuración):

```yaml
services:
  bot-telegram:
    build: .
    image: snm-bot-telegram:${VERSION}
    container_name: bot-telegram
    restart: unless-stopped
    env_file:
      - /srv/comun/.env
      - .env
    user: "10501:10501"
    group_add: ["10500"]          # ALERTAS_SOCKET_GID
    read_only: true
    tmpfs: [/tmp]
    mem_limit: 128m               # bot-discord: 160m · webhooks: 96m
    volumes:
      - alertas-socket:/run/snm:ro
    networks: [mesh]
    healthcheck:
      test: ["CMD", "python", "-c", "import urllib.request,sys; sys.exit(0 if urllib.request.urlopen('http://127.0.0.1:8080/health', timeout=3).status == 200 else 1)"]
      interval: 30s
      timeout: 5s
      retries: 3
      start_period: 30s
    logging:
      driver: json-file
      options: {max-size: "10m", max-file: "3"}

volumes:
  alertas-socket:
    external: true

networks:
  mesh:
    external: true
```

**Pasos de despliegue** (cada aplicación, en `/srv/<nombre>/`):

1. Requisitos: `infrastructure` (red `mesh`, PostgreSQL), `detector-alertas` desplegado (crea el volumen `alertas-socket` y el socket), endpoints de alertas y estado del portal publicados (`../portal/`).
2. Crear rol y base (6) y la línea de `pg_hba.conf`; recargar PostgreSQL.
3. Crear el bot en la plataforma (módulo correspondiente) y guardar el token en `.env`.
4. Copiar `compose.yaml` y `.env.example` → `.env`; rellenar secretos (`chmod 600 .env`).
5. `deploy.sh bot-telegram` (y equivalentes).
6. Comprobar `docker compose ps` (sano), el registro (`socket conectado`, `migraciones aplicadas`) y la fila del servicio en verde en el panel.
7. Prueba de humo del módulo (grupo o canal de prueba / receptor de prueba).
8. Añadir el servicio a `config/servicios.php` del portal si no estaba (destino de salud `http://<contenedor>:8080/health`).

Actualizar: cambiar la etiqueta de la imagen en `compose.yaml` y repetir el paso 5; las migraciones se aplican solas. Volver atrás: etiqueta anterior (las migraciones solo añaden; nunca borran columnas en la misma versión que deja de usarlas).

**Copias de seguridad:** fuera del proyecto (sistema del operador). Qué copiar: las tres bases y `/srv/<nombre>/.env`. Tras restaurar una copia de ayer, el cursor vuelve atrás: el detector reenvía lo posterior (máximo 24 h) y la restricción única evita duplicados.

## 9. Definición de hecho

- [ ] Las tres aplicaciones arrancan con `docker compose up -d`, sin puertos publicados, como usuario no root, con sistema de archivos de solo lectura.
- [ ] Las tres aparecen en verde en el panel y su `/health` cambia a `503` al parar el detector más de 2 min.
- [ ] `nucleo/` idéntico en las tres, comprobado en las pruebas.
- [ ] Una transición simulada `alto · infraestructura` llega a un grupo de Telegram de prueba, a un canal de Discord de prueba y a un receptor de webhook de prueba en menos de 30 s.
- [ ] La `actualizada` con cambio de riesgo y la `resuelta` llegan como respuesta al mensaje original en Telegram y Discord.
- [ ] Reiniciar cada aplicación con 10 transiciones generadas mientras estaba parada: llegan todas, en orden, sin duplicados.
- [ ] `/status`, `/battery` y `/routers` responden en menos de 3 s con caché del portal caliente, con los formatos de 4.4.
- [ ] `/levels` y `/types` cambian los filtros solo del destino donde se ejecutan y solo con permiso de administrador.
- [ ] 30 aperturas de la misma regla en 1 min producen 5 mensajes sueltos y un resumen por destino.
- [ ] Expulsar el bot desactiva el destino y deja de intentar enviarle.
- [ ] Un webhook a `https://10.0.0.1/` se rechaza al cargar y uno cuyo DNS resuelve a una IP privada se rechaza al enviar.
- [ ] La firma de un webhook se verifica con el vector de prueba de `03-webhooks.md`.
- [ ] La tabla `envio` guarda el contenido de cada mensaje enviado y la retención borra lo de más de 1 año.
- [ ] `.env.example` completo, sin secretos; `README.md` de cada directorio del monorepo enlaza a esta ficha.
- [ ] La página de bots del portal (`../portal/06-bots-page.md`) usa los formatos, comandos y filtros por defecto de esta ficha.

## 10. Escenarios de prueba

1. **Reconexión con cursor.** Dado `bot-telegram` con cursor `T10` y el detector emitiendo, cuando se para el bot, se generan `T11`–`T40` y se arranca, entonces saluda con `desde: T10`, procesa `T11`–`T40` en orden y cada destino recibe solo lo que pasa sus filtros, una vez.
2. **Latido perdido.** Dado un cliente conectado, cuando el detector deja de enviar líneas sin cerrar la conexión, entonces a los 90 s el cliente cierra, reconecta con su último `transicion_id` y `/health` refleja la reconexión.
3. **Filtros independientes.** Dados dos grupos, A con defecto y B con `/levels bajo medio alto` y `/types infraestructura clientes`, cuando llega `bajo · clientes`, entonces solo B lo recibe.
4. **Hilo con bajada de riesgo.** Dado un destino con `/levels alto` que recibió la apertura `alto` de una alerta, cuando llega `actualizada` a `medio` y después `resuelta`, entonces recibe ambas como respuesta al mensaje original.
5. **Subida de riesgo sin hilo.** Dado un destino con `/levels alto`, cuando una alerta se abre en `medio` (no se envía) y se actualiza a `alto`, entonces el destino recibe una apertura en `alto` que inicia el hilo.
6. **Actualización sin cambio de riesgo.** Dada una alerta `all` enviada, cuando llega `actualizada` con el mismo riesgo y más nodos, entonces no se envía nada.
7. **Ráfaga.** Dado un destino, cuando llegan 12 aperturas de `battery-low` en 1 min, entonces salen 5 mensajes sueltos y, 2 min después de la sexta, un resumen con las 7 restantes; sus resoluciones no se envían.
8. **Portal caído.** Dado el portal parado, cuando alguien escribe `/status`, entonces el bot responde el texto de datos no disponibles, y una alerta que llega a la vez se envía con normalidad.
9. **Reinicio con cola.** Dados 8 mensajes pendientes por el límite de 10/min, cuando se reinicia el contenedor, entonces salen los 8 en orden y ninguno se repite.
10. **Destino que falla.** Dado un grupo donde el bot ya no puede escribir (sin expulsarlo), cuando pasan 3 días sin un envío correcto, entonces el destino queda inactivo con `motivo_baja = fallos` y sus pendientes caducan.
11. **Webhook firmado.** Dado un receptor con su secreto, cuando llega una `abierta` que pasa sus filtros, entonces recibe un `POST` cuyo `X-SNM-Firma` coincide con el HMAC-SHA256 en hexadecimal del cuerpo y `X-SNM-Transicion` con el `transicion_id`.

## 11. Riesgos y limitaciones

| Riesgo | Efecto | Mitigación |
|---|---|---|
| Bot parado más de 24 h | Se pierden las transiciones anteriores a la ventana de reenvío del detector | Límite del contrato; el panel muestra la caída en < 2 min |
| Límite de la API del portal (60/min por IP) compartido: las peticiones de los bots salen por la IP del propio servidor | Comandos que responden "no disponible" en picos | Caché de 30 s por URL y una sola petición para `/battery` y `/routers` (≤ 22 peticiones/min por bot en el peor caso). Si no basta, excepción en el portal para las IPs del servidor |
| Petición en horquilla (contenedor → IP pública del propio servidor → Nginx) | Si el host no la permite, los comandos fallan siempre | Comprobar en el despliegue; alternativa: `extra_hosts: ["mesh.example.org:host-gateway"]` |
| Modo privacidad de Telegram con varios bots en un grupo | `/status` sin `@usuario` puede no llegar al bot | La ayuda y la página de bots indican usar el menú de comandos (añade `@usuario`) |
| Abuso: alguien añade el bot a muchos grupos | Más carga de envío | Límite global de 25 mensajes/s y agrupación; datos públicos, sin daño. Revisar `destinos_activos` en `/health` |
| Cambios en las plataformas o en sus librerías | Rotura del bot | Versiones fijadas, pruebas con plataforma simulada y prueba de humo antes de actualizar |
| Al menos una vez | Un mensaje repetido si el proceso muere justo tras enviarlo | Ventana mínima; webhooks deduplicables por `X-SNM-Transicion` |
| Colores del *embed* de Discord | Un solo color por *embed*: no se adapta al tema claro | Icono y palabra del riesgo siempre en el título (el color nunca es la única señal) |
| Receptor de webhook lento o caído | Retiene su propia cola hasta 6 min por entrega | Cola por destino: no afecta a otros; desactivación tras 20 fallos |

## 12. Referencias

- Contrato común: [`../integration.md`](../integration.md) (§4 volumen, §7 socket, §8 bases, §9 API, §11 salud, §12 `.env.comun`).
- Detector y socket: [`../detector-alertas/03-socket-persistence.md`](../detector-alertas/03-socket-persistence.md).
- API del portal: [`../portal/12-stats-api.md`](../portal/12-stats-api.md) (`/stats/summary`) y [`../portal/13-nodes-alerts-api.md`](../portal/13-nodes-alerts-api.md) (`/routers`, `/alerts/catalog`).
- Página de bots y documentación de webhooks: [`../portal/06-bots-page.md`](../portal/06-bots-page.md), [`../portal/11-public-api.md`](../portal/11-public-api.md).
- Diseño (colores de los *embeds*): [`../DESIGN.md`](../DESIGN.md).
- Panel y salud: [`../portal/14-operator-panel.md`](../portal/14-operator-panel.md).
- Bases: [`../infrastructure/02-postgresql.md`](../infrastructure/02-postgresql.md), [`../infrastructure/04-operations.md`](../infrastructure/04-operations.md).

## Decisiones de detalle

1. **Núcleo copiado, no paquete compartido:** cada aplicación lleva su copia de `nucleo/` y una prueba exige que sean idénticas. Respeta la independencia del monorepo sin añadir un directorio nuevo.
2. **La tabla `envio` es a la vez cola y registro;** el cursor avanza en la misma transacción que se insertan los envíos. Entrega al menos una vez, con restricción única (`destino_id`, `transicion_id`).
3. **Continuidad del hilo:** una alerta ya avisada en un destino se sigue hasta su resolución aunque deje de pasar los filtros; una `actualizada` que pasa los filtros por primera vez se envía como apertura. Igual en webhooks.
4. **Agrupación:** a partir de la sexta apertura de la misma regla en 2 min por destino, resumen diferido; las alertas resumidas no tienen mensajes posteriores.
5. **Filtros `NULL` = defecto vigente;** se admite `todos` en `/levels` y `/types`.
6. **`/battery` y `/routers` admiten `[provincia]` opcional** (parámetro `province` que ya tiene la API) para no superar los límites de longitud de los mensajes.
7. **Marca de retraso** si la transición tenía más de 5 min al procesarse (recuperaciones tras una caída).
8. **Iconos de batería** con los cortes de `battery-low` de infraestructura (20 % y 40 %); iconos de carga con los del mapa.
9. **Instancia única** por bloqueo consultivo de PostgreSQL.
10. **aiogram** para Telegram: las tres aplicaciones usan aiohttp por debajo (mismo cliente HTTP y mismo servidor de salud).
11. **Campos esperados del catálogo** (`risks[]`, `types[]`, `rules[]` con `id` y `name`, riesgos de menor a mayor), con catálogo mínimo de reserva si el portal no responde.
12. **Sin nombres de grupos, canales ni usuarios en la base,** para que coincida con lo que promete la página de privacidad de los bots.
13. **Retención 1 año** para envíos y entregas; destinos inactivos se borran al año de su baja.

---
> Creado: 2026-10-07 · Última revisión: 2026-10-07
