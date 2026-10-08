# 08.2 · Bot de Discord

> Aplicación `bot-discord` (contenedor `bot-discord`, base `bot_discord`, monorepo `services/bot-discord/`): lleva cada transición del socket a los canales de servidores de Discord suscritos con `/subscribe` y responde a los comandos comunes como comandos de barra. Núcleo, socket, API, formato neutro, comandos, filtros, anti-ruido, salud, datos y despliegue: [`README.md`](README.md).

## Objetivo

- En Discord el bot entra en un **servidor**, no en un canal: cada canal que quiera alertas se suscribe con `/subscribe` y tiene sus propios filtros.
- Pintar el aviso neutro del núcleo como *embed*, con hilo (respuesta al mensaje original) en reaperturas, cambios de riesgo y resoluciones.
- Traducir comandos de barra, permisos y errores de la API de Discord a la interfaz común (`nucleo/comandos.py`, `nucleo/motor_envios.py`).
- Cumplir lo que promete la página de bots (`../portal/06-bots-page.md`): invitar, suscribir, configurar y quitar sin otra documentación; el bot no lee mensajes (no pide el *intent* de contenido).

## Especificación

### Conexión

| Elemento | Valor |
|---|---|
| Librería | discord.py 2.x (versión en `README.md` §3): `discord.Client` + `app_commands.CommandTree` |
| Conexión | Saliente al gateway (`gateway.discord.gg`) y REST (`discord.com/api/v10`); sin endpoint de interacciones ni puerto público. discord.py gestiona latidos, `RESUME` y reconexión |
| *Intents* | `Intents.none()` + `guilds`. **Sin** *intents* privilegiados (`message_content`, `members`, `presences`) ni `guild_messages`: el bot nunca recibe el texto de los mensajes. Los comandos de barra llegan como interacciones, sin *intent* |
| Menciones | `allowed_mentions = AllowedMentions.none()` en el cliente: ningún nombre de nodo puede mencionar a `@everyone`, roles ni usuarios |
| Comandos | Globales; `tree.sync()` en cada arranque (sobrescritura en bloque, idempotente) |
| Contextos | `contexts = [guild]`, `integration_types = [guild_install]`: sin comandos en mensajes directos (`README.md` §2) |
| *Sharding* | No hace falta por debajo de 2.500 servidores |

**Crear la aplicación (operador, una vez)** en el portal de desarrolladores de Discord:

1. *New Application*: nombre = `PROJECT_NAME`, icono con el logo propio (`services/bot-discord/assets/logo.jpg`), descripción `Avisos de los problemas de la malla de ${PROJECT_NAME}. Más información: https://${PROJECT_DOMAIN}/bots`.
2. *Installation*: solo *Guild Install*; ámbitos `bot` y `applications.commands`; permisos de la tabla de abajo.
3. *Bot*: generar el token → `DISCORD_BOT_TOKEN`; *Public Bot* activado (cualquiera puede invitarlo); *Requires OAuth2 Code Grant* desactivado; los tres *Privileged Gateway Intents* desactivados.
4. *Application ID* → `DISCORD_APP_ID`; el enlace de invitación (abajo) va a la configuración del portal (`config/proyecto.php`).

### Permisos del enlace de invitación

| Permiso (nombre en el cliente en español) | Bit | Para qué |
|---|---|---|
| `VIEW_CHANNEL` (Ver canales) | `1 << 10` = 1.024 | Ver los canales suscritos |
| `SEND_MESSAGES` (Enviar mensajes) | `1 << 11` = 2.048 | Publicar alertas |
| `EMBED_LINKS` (Insertar enlaces) | `1 << 14` = 16.384 | *Embeds* |
| `READ_MESSAGE_HISTORY` (Leer el historial de mensajes) | `1 << 16` = 65.536 | Responder al mensaje original (hilo) |

Total `84992`. Enlace: `https://discord.com/oauth2/authorize?client_id=${DISCORD_APP_ID}&scope=bot+applications.commands&permissions=84992&integration_type=0`. Ningún permiso de administración.

### Suscripción por canal

| Acción | Regla |
|---|---|
| Quién | `/subscribe` y `/unsubscribe`: quien tenga **Gestionar canales** en ese canal (`interaction.permissions.manage_channels`, que ya incluye las sobrescrituras del canal). Los comandos llevan además `default_member_permissions = manage_channels` (ocultos por defecto a los demás; el servidor puede cambiarlo, por eso se comprueba siempre). Sin permiso: `Solo quien puede gestionar este canal puede activar o quitar las alertas.` |
| Canales admitidos | Texto (tipo 0 → `clase = texto`) y anuncios (tipo 5 → `anuncios`). Hilos, foros, voz y escenarios: `Este tipo de canal no admite alertas. Usa un canal de texto o de anuncios.` |
| Permisos del bot | Antes de suscribir se comprueba `channel.permissions_for(guild.me)` con los 4 permisos: si falta alguno, `Me faltan permisos en este canal: Insertar enlaces, Leer el historial de mensajes.` y no se suscribe |
| `/subscribe` en canal nuevo o inactivo | Alta o reactivación (`plataforma_id` = id del canal, `servidor_id` = id del servidor, filtros `NULL`, `motivo_baja`, `baja_en` y `fallando_desde` a `NULL`) y respuesta pública (abajo) |
| `/subscribe` en canal activo | `Este canal ya recibe alertas. Usa /settings para ver su configuración.` |
| `/unsubscribe` | Activo → inactivo `unsubscribe`, pendientes → `caducado`, `Este canal ya no recibirá alertas.`; inactivo → `Este canal no recibe alertas.` |
| Expulsión del servidor | `on_guild_remove` → todos los destinos de ese `servidor_id` inactivos `expulsado`, pendientes `caducado`. `on_guild_unavailable` (caída de Discord) **no** es una baja |
| Canal borrado | `on_guild_channel_delete` → `canal_borrado`. `on_guild_channel_update` que cambia texto ↔ anuncios actualiza `clase` |
| Conciliación | En cada `on_ready` (no en `RESUME`): destinos activos cuyo servidor no está en `client.guilds` → `expulsado`; cuyo canal no existe → `canal_borrado` (bajas ocurridas con el bot parado) |

Respuesta de `/subscribe` (riesgos y tipos por defecto desde `BOT_RIESGOS_DEFECTO`/`BOT_TIPOS_DEFECTO` y el catálogo):

```
✅ Este canal recibirá las alertas de Sur Nodos en Mallas.
Riesgos: alto · Tipos: infraestructura (por defecto)
Cámbialos con /levels y /types. Ayuda: /help
```

Los anuncios se publican en el canal de anuncios, pero no se difunden (*crosspost*) a los servidores que lo siguen.

### Comandos de barra

| Comando | Opción | Quién | Respuesta |
|---|---|---|---|
| `/status` | — | Cualquiera | Pública |
| `/battery`, `/routers` | `provincia` (opcional, 9 opciones fijas: valor `ES-AL` … `ES-SE`, `FUERA`; nombre visible Almería … Sevilla, Fuera de Andalucía) | Cualquiera | Pública |
| `/levels` | `riesgos` (texto opcional: `medio alto`, `todos`) | Ver: cualquiera. Cambiar: Gestionar canales | Pública |
| `/types` | `tipos` (texto opcional) | Igual que `/levels` | Pública |
| `/pause` | — | Gestionar canales | Efímera si error/sin permiso; pública si pausa |
| `/resume` | — | Gestionar canales | Efímera si error/sin permiso; pública si reanuda |
| `/disable_exterior` | — | Gestionar canales | Efímera si error/sin permiso; pública si cambia |
| `/enable_exterior` | — | Gestionar canales | Efímera si error/sin permiso; pública si cambia |
| `/exterior` | `accion` (opcional: activar, desactivar) | Ver: cualquiera. Cambiar: Gestionar canales | Efímera si error/sin permiso; pública si consulta o cambia |
| `/settings` | — | Cualquiera | Pública |
| `/help` | — | Cualquiera | Efímera |
| `/subscribe`, `/unsubscribe` | — | Gestionar canales | Pública |

- Descripciones de comandos y opciones en español (≤ 100 caracteres), las mismas que en Telegram (`01-bot-telegram.md`).
- Errores, falta de permiso, canal no suscrito (`Este canal no recibe alertas. Usa /subscribe.`) y validaciones se responden **efímeros** y sin `defer`.
- Lo que consulta la API (`/status`, `/battery`, `/routers`): `defer(thinking=True)` inmediato (límite de 3 s) y `followup.send` después; el resto de trozos, con más `followup.send`.
- Límite de un comando de consulta cada 5 s por canal: Discord no permite ignorar una interacción (mostraría un fallo), así que se responde efímero `Espera unos segundos entre consultas.`
- Respuestas en texto (`content`), troceadas por líneas a 2.000 caracteres con `max_caracteres = 2000` para `nucleo/comandos.py` (máximo 3 mensajes); con `discord.utils.escape_markdown(texto, ignore_links=True)` y la marca `SUPPRESS_EMBEDS` (el enlace al portal no genera vista previa).

### Embeds

| Aviso neutro (`README.md` §4.3) | *Embed* |
|---|---|
| `icono` + `titulo` | `title` (≤ 256), p. ej. `🔴 ALTO · Infraestructura · Bucle de reinicio` |
| `enlace` | `url` (el título enlaza a la ficha de la alerta o a `/alertas` en los resúmenes) |
| `lineas` (sujeto, mensaje, duración, retraso) | `description`, unidas por `\n`; texto con `escape_markdown`, id del nodo entre `` ` `` |
| `color` | `color`: entero RGB del valor **en modo oscuro** del token de `../DESIGN.md` (`critico`, `aviso`, `info`, `correcto`), en una tabla de constantes de `formato_discord.py` con el nombre del token |
| `fecha` | `timestamp` (Discord la muestra en la hora local de quien lee) |
| — | `footer.text` = `PROJECT_NAME` |

```jsonc
{
  "embeds": [{
    "title": "🔴 ALTO · Infraestructura · Bucle de reinicio",
    "url": "https://mesh.example.org/alertas/01JABCDXYZ7Q8R9S0T1V2W3X4Y",
    "description": "CAD1 (Repetidor Sierra Cádiz) · `!a1b2c3d4` · ROUTER · Cádiz\nCAD1 se ha reiniciado 7 veces en la última hora",
    "color": 0,                      // entero del token `critico` (modo oscuro)
    "timestamp": "2026-10-01T15:00:00Z",
    "footer": {"text": "Sur Nodos en Mallas"}
  }],
  "allowed_mentions": {"parse": []}
}
```

Hilo: `reference = MessageReference(message_id=<mensaje_id del hilo>, channel_id=<canal>, fail_if_not_exists=False)`; si el original se borró, sale sin referencia. Los resúmenes nunca van en hilo; su color es el del riesgo más alto de las alertas agrupadas.

### Límites de Discord

| Límite | Valor | Cómo se respeta |
|---|---|---|
| Texto de un mensaje | 2.000 caracteres | Respuestas troceadas a 2.000 |
| *Embed* | Título 256, descripción 4.096, pie 2.048, total 6.000 | Un *embed* por aviso; `mensaje` ≤ 1.000 (núcleo) |
| Mensajes por canal | 5 cada 5 s | `BOT_MAX_MENSAJES_MINUTO=10` y 1 s por destino |
| Global | 50 peticiones/s | `BOT_MAX_MENSAJES_SEGUNDO=25` |
| `429` | discord.py espera `retry_after` y reintenta por su cuenta | Si aun así sale `429` → `ErrorTransitorio(retry_after)` |
| Peticiones inválidas | 10.000 respuestas `401`/`403`/`429` en 10 min → bloqueo temporal de la IP | `ErrorDestino` solo se reintenta 2 veces y el destino se desactiva a los 3 días |
| Interacciones | Primera respuesta ≤ 3 s; token válido 15 min | `defer` inmediato |
| Verificación | Sin verificar la aplicación no entra en más de 100 servidores (se solicita desde 75) | Riesgo aceptado; ver `destinos_activos` en `/health` |

## Contratos propios

**Variables** (además de las de `README.md` §5):

| Variable | Valor o ejemplo | Común o propia | Secreto |
|---|---|---|---|
| `DISCORD_BOT_TOKEN` | — | Propia | **Sí** |
| `DISCORD_APP_ID` | Id numérico de la aplicación | Propia | No |

`DB_NAME` = `DB_USER` = `bot_discord`. **`envio.contenido`**: `{"embeds": […], "allowed_mentions": {"parse": []}}`; `mensaje_id` = id del mensaje; `chat_envio_id` = id del canal. `destino.servidor_id` = id del servidor (sin su nombre).

**Errores de la API → clases del motor** (`README.md` UT-08.5):

| Respuesta (código JSON) | discord.py | Clase |
|---|---|---|
| `429` tras los reintentos de la librería | `RateLimited`, `HTTPException` 429 | `ErrorTransitorio(retry_after)` |
| `5xx`, red, timeout | `DiscordServerError`, `aiohttp.ClientError`, `asyncio.TimeoutError` | `ErrorTransitorio` |
| `403` `50001` *Missing Access*, `50013` *Missing Permissions* | `Forbidden` | `ErrorDestino` |
| `404` `10003` *Unknown Channel* | `NotFound` | `DestinoPerdido` (`canal_borrado`) |
| `404` `10004` *Unknown Guild* | `NotFound` | `DestinoPerdido` (`expulsado`) |
| `400` `50035` *Invalid Form Body* | `HTTPException` | `ErrorDestino` y registro `error` (fallo propio) |
| `401` al conectar | `LoginFailure` | Token inválido: sale con código 1 |

`plataforma.ok` de `/health` = cliente listo (`is_ready()` y no cerrado); `ultimo_ok` se renueva con cada latido confirmado o envío correcto.

## Unidades de trabajo

### UT-08.2.1 — Conexión, *intents* y comandos
- **Comportamiento:** cliente con `guilds` como único *intent*, menciones desactivadas, sincronización global de los 9 comandos con sus opciones, contextos y permisos por defecto.
- **Casos borde:** token inválido (código 1 sin mostrarlo); gateway caído (discord.py reconecta; `/health` en `503` tras 5 min); `RESUME` frente a `IDENTIFY` (la conciliación solo en `on_ready`).
- **Aceptación:** en un servidor de prueba aparecen los 9 comandos, `/subscribe` oculto a quien no gestiona canales; el registro no contiene el token.

### UT-08.2.2 — Suscripción y ciclo de destinos
- **Comportamiento:** tabla de suscripción: `/subscribe`, `/unsubscribe`, comprobación de tipo de canal y de permisos del bot, eventos de servidor y canal, conciliación al arrancar.
- **Casos borde:** canal privado donde el bot no tiene `VIEW_CHANNEL`; dos canales del mismo servidor con filtros distintos; servidor que expulsa al bot con el proceso parado.
- **Aceptación:** escenarios 1–4.

### UT-08.2.3 — Comandos de barra
- **Comportamiento:** traducción de interacciones a `nucleo/comandos.py` (`es_admin` = `manage_channels` en el canal), `defer`, efímeros, límite por canal, troceado y escapado.
- **Casos borde:** `/routers` con 100 routers (3 mensajes y aviso final); `/levels` sin argumentos en canal no suscrito; nombre de nodo con `**`, `@everyone` o `` ` ``.
- **Aceptación:** escenarios 5–6; las respuestas coinciden carácter a carácter con los textos de `README.md` §4.4 (salvo el escapado de *markdown*).

### UT-08.2.4 — *Embeds* y envío
- **Comportamiento:** implementa `enviar(destino, contenido, responder_a)`: *embed* de cada caso de `README.md` §4.3, referencia con `fail_if_not_exists=False`, clasificación de errores de la tabla.
- **Casos borde:** mensaje original borrado; resumen con 20 etiquetas; riesgo nuevo sin color (token `info`); `resuelta_en` nulo.
- **Aceptación:** pruebas de instantánea del JSON de cada caso; con un servidor falso de la API, cada fila de la tabla de errores acaba en su clase.

## Escenarios de prueba

1. **Invitar y suscribir.** Dado el bot invitado con el enlace de 84992 a un servidor de prueba, cuando alguien con Gestionar canales ejecuta `/subscribe` en `#avisos`, entonces se crea el destino (`clase = texto`), responde el texto de alta y una `abierta` `alto · infraestructura` simulada llega como *embed* con el color `critico` en menos de 30 s.
2. **Sin permiso.** Dado un usuario sin Gestionar canales, cuando ejecuta `/subscribe` o `/levels bajo`, entonces recibe un efímero (`Solo los administradores pueden cambiar los filtros.` en `/levels`) y nada cambia; `/levels` sin argumentos sí le muestra los riesgos.
3. **Faltan permisos del bot.** Dado `#avisos` donde el rol del bot no puede insertar enlaces, cuando se ejecuta `/subscribe`, entonces responde `Me faltan permisos en este canal: Insertar enlaces.` y no hay destino.
4. **Expulsión con el bot parado.** Dados dos canales suscritos en un servidor, cuando el bot se para, lo expulsan y vuelve a arrancar, entonces en `on_ready` ambos destinos quedan inactivos `expulsado` y sus pendientes `caducado`.
5. **Hilo.** Dado un canal que recibió la apertura de una alerta, cuando llegan la `actualizada` a `medio` y la `resuelta`, entonces ambas salen como respuesta al *embed* original, con los colores `aviso` y `correcto`.
6. **Consulta con portal lento.** Dado el portal respondiendo en 4 s, cuando alguien ejecuta `/status`, entonces Discord muestra "pensando" y la respuesta llega por `followup` sin fallo de interacción; un segundo `/status` en menos de 5 s recibe `Espera unos segundos entre consultas.`
7. **Menciones neutralizadas.** Dado un nodo llamado `@everyone`, cuando se envía su alerta y alguien pide `/routers`, entonces nadie recibe una mención.
8. **Canal borrado.** Dado un canal suscrito con 3 envíos pendientes, cuando se borra el canal, entonces el destino queda `canal_borrado` y no se hacen más peticiones a ese canal.

---
> Creado: 2026-10-07 · Última revisión: 2026-10-08
