# 08.1 · Bot de Telegram

> Aplicación `bot-telegram` (contenedor `bot-telegram`, base `bot_telegram`, monorepo `services/bot-telegram/`): lleva cada transición del socket a los grupos, supergrupos y canales de Telegram donde la añadan y responde a los comandos comunes. Núcleo, socket, API, formato neutro, comandos, filtros, anti-ruido, salud, datos y despliegue: [`README.md`](README.md).

## Objetivo

- Alta automática al añadir el bot a un grupo o canal y baja al quitarlo, sin intervención del operador.
- Pintar el aviso neutro del núcleo como HTML de Telegram, con hilo (respuesta al mensaje original) en reaperturas, cambios de riesgo y resoluciones.
- Traducir comandos, permisos y errores de la Bot API a la interfaz común (`nucleo/comandos.py`, `nucleo/motor_envios.py`).
- Cumplir lo que promete la página de bots (`../portal/06-bots-page.md`): añadir, configurar y quitar sin otra documentación; solo se guardan ids de chat, filtros y avisos enviados; las conversaciones no se leen ni se guardan.

## Especificación

### Conexión

| Elemento | Valor |
|---|---|
| Librería | aiogram 3.x (versión en `README.md` §3), `Dispatcher` con un `Router` para `message` y otro para `channel_post` |
| Modo | *Long polling* (`getUpdates`, `timeout` 30 s). Sin webhook entrante ni puerto público. Al arrancar: `deleteWebhook(drop_pending_updates=false)` |
| `allowed_updates` | `["message", "channel_post", "my_chat_member"]` |
| Pendientes al arrancar | Telegram guarda las actualizaciones 24 h. Se procesan todas las `my_chat_member` (altas y bajas ocurridas con el bot parado); los comandos con `date` de hace más de `TELEGRAM_COMANDO_MAX_EDAD_S` se descartan sin responder |
| Arranque | `getMe` (usuario del bot, para reconocer `/comando@usuario`); `setMyCommands` y `setMyDescription`/`setMyShortDescription` solo si difieren de lo que devuelven `getMyCommands`/`getMyDescription` |
| Instancia única | Bloqueo consultivo del núcleo (UT-08.1). Si aun así llega `409 Conflict` de `getUpdates`, se registra como error y se reintenta con la espera de aiogram |

**Crear el bot (operador, una vez).** Con @BotFather: `/newbot` (nombre = `PROJECT_NAME`, usuario libre terminado en `bot`), `/setprivacy` → *Enable* (por defecto), `/setjoingroups` → *Enable*, foto con el logo propio (`services/bot-telegram/assets/logo.jpg` mediante `/setuserpic`). El token va a `TELEGRAM_BOT_TOKEN`; el usuario (sin `@`) a la configuración del portal (`config/proyecto.php`). Comandos y descripciones los publica el propio bot al arrancar: no se escriben en BotFather.

### Destinos

Un destino es un chat `group`, `supergroup` o `channel` (`destino.clase`); `plataforma_id` = `chat.id`, `servidor_id` = `NULL`. El chat privado nunca es destino: solo responde comandos de consulta.

| `my_chat_member` (estado nuevo del bot) | Grupo o supergrupo | Canal |
|---|---|---|
| `member` o `administrator` | Alta o reactivación y saludo | Alta o reactivación si `can_post_messages`; si no, inactivo `sin_permiso`. Sin saludo |
| `restricted` con `can_send_messages: false` | Inactivo `sin_permiso` (pendientes → `caducado`) | — |
| `restricted` con `can_send_messages: true` | Alta o reactivación | — |
| `left` o `kicked` | Baja `expulsado`; pendientes → `caducado` | Ídem |

- **Reactivación:** `activo = true`, filtros a `NULL` (vuelven los de por defecto, como promete la página), `motivo_baja`, `baja_en` y `fallando_desde` a `NULL`, `alta_en = now()`.
- **Alta perdida:** un comando desde un grupo o canal sin fila `destino` (el bot estuvo parado más de 24 h) crea el destino antes de responder.
- **Destino inactivo:** `/settings` responde `Este chat no recibe alertas desde el 02/10/2026 (fallos de envío). Para reactivarlo, quita el bot y vuelve a añadirlo.` (motivo `fallos` o `sin_permiso`).
- **Migración a supergrupo:** mensaje de servicio con `migrate_to_chat_id` o error `TelegramMigrateToChat` → `plataforma_id` = id nuevo, `clase = supergroup`, y se reintenta el envío al momento. Si ya existe una fila con el id nuevo (alta perdida), se conserva la antigua con sus filtros y se borra la nueva. Los mensajes anteriores tienen otro `chat_envio_id`: sus respuestas salen sin hilo.
- **Saludo** (solo grupos, no se guarda en `envio`):

```
Hola. Avisaré aquí de los problemas de la malla de Sur Nodos en Mallas.
Riesgos: alto · Tipos: infraestructura (por defecto)
Los administradores pueden cambiarlos con /levels y /types. Ayuda: /help
```

### Comandos

- Reconoce `/comando` y `/comando@usuario_del_bot` (sin distinguir mayúsculas); `/comando@otro_bot` se ignora. Argumentos: el resto de la línea. `/start` en privado responde como `/help`; en grupos se ignora (ya hubo saludo). Admite alias comunes como `/silenciar`, `/mute` o `/stop` para `/pause`, y `/activar` o `/unmute` para `/resume`.
- **Administrador** (`es_admin` para `nucleo/comandos.py`): en un canal, siempre (solo publican administradores); en un grupo, mensaje con `sender_chat.id == chat.id` (administrador anónimo) o `getChatMember` con estado `creator` o `administrator` (caché en memoria 60 s por chat y usuario). Un mensaje en nombre de otro canal (`sender_chat` distinto del chat) no es administrador.
- En privado, `/levels`, `/types`, `/settings`, `/pause` y `/resume` responden `Este comando solo funciona en grupos y canales.`
- **Canales:** la respuesta a `/levels`, `/types`, `/settings`, `/pause` y `/resume` se publica y, a los `TELEGRAM_BORRAR_COMANDOS_CANAL_S`, el bot borra el comando y su respuesta (`deleteMessage`; sin permiso de borrar, se quedan). `/status`, `/battery`, `/routers` y `/help` quedan publicados.
- **Temas:** en supergrupos con temas la respuesta va al mismo `message_thread_id`; las alertas van al tema General (`README.md` §2).
- Respuestas en texto plano (sin `parse_mode`), previsualización de enlaces desactivada, troceadas por líneas a 4.000 caracteres (máximo 3 mensajes, regla de `README.md` §4.4). Límite de un comando cada 5 s por chat: el resto se ignora.
- **Privacidad:** con el modo privacidad, en grupos solo llegan comandos y respuestas al bot. Si alguien lo hace administrador de un grupo, o en canales, Telegram entrega **todos** los mensajes: todo lo que no es un comando del bot se descarta en memoria, sin registrarlo ni guardarlo. Nunca se registra el texto de un mensaje, ni `from.id`, ni nombres.

### Formato del mensaje

El aviso neutro (`README.md` §4.3) se pinta en HTML (`parse_mode: HTML`): título en `<b>`, id del nodo en `<code>` (se copia con un toque), resto en texto escapado con `html.escape` (nombres, `mensaje`, etiquetas). Enlace en claro; `link_preview_options.is_disabled = true`. `disable_notification = true` para riesgo `bajo` y para resoluciones (llegan sin sonido).

```html
<b>🔴 ALTO · Infraestructura · Bucle de reinicio</b>
CAD1 (Repetidor Sierra Cádiz) · <code>!a1b2c3d4</code> · ROUTER · Cádiz
CAD1 se ha reiniciado 7 veces en la última hora
https://mesh.example.org/alertas/01JABCDXYZ7Q8R9S0T1V2W3X4Y
```

Hilo: `reply_parameters = {"message_id": <mensaje_id del hilo>, "allow_sending_without_reply": true}` solo si `chat_envio_id` del hilo coincide con el `plataforma_id` actual; si el original se borró, sale sin respuesta. Los resúmenes de ráfaga nunca van en hilo.

### Límites de la Bot API

| Límite de Telegram | Valor | Cómo se respeta |
|---|---|---|
| Longitud de un mensaje | 4.096 caracteres tras procesar entidades | `mensaje` recortado a 1.000 (núcleo); respuestas troceadas a 4.000 |
| Mensajes en un mismo grupo | ~20/min; ~1/s por chat | `BOT_MAX_MENSAJES_MINUTO=10` y 1 s entre mensajes del mismo destino |
| Difusión total | ~30 mensajes/s | `BOT_MAX_MENSAJES_SEGUNDO=25` |
| Exceso | `429` con `parameters.retry_after` | `ErrorTransitorio(retry_after)` |
| Comandos | Nombre 1–32 caracteres `[a-z0-9_]`, descripción hasta 256 | Tabla de abajo |
| Descripciones | `setMyDescription` ≤ 512, `setMyShortDescription` ≤ 120 caracteres | Textos de abajo |
| Actualizaciones | Se guardan 24 h; un solo consumidor de `getUpdates` | Instancia única |

## Contratos propios

**Variables** (además de las de `README.md` §5):

| Variable | Valor o ejemplo | Común o propia | Secreto |
|---|---|---|---|
| `TELEGRAM_BOT_TOKEN` | — | Propia | **Sí** |
| `TELEGRAM_COMANDO_MAX_EDAD_S` | `120` | Propia | No |
| `TELEGRAM_BORRAR_COMANDOS_CANAL_S` | `60` | Propia | No |

`DB_NAME` = `DB_USER` = `bot_telegram`. El token nunca aparece en registros: aiogram incluye el token en la URL de la API, así que el registro de aiohttp/aiogram queda en `WARNING` y los errores se registran por clase y descripción, nunca por URL.

**Comandos publicados** (`setMyCommands`, descripciones en español):

| Ámbito (`scope`) | Comandos |
|---|---|
| `all_group_chats` | `status` Estado general de la malla · `battery` Batería de los routers [provincia] · `routers` Routers con batería, chutil y tx [provincia] · `levels` Ver o cambiar los riesgos (administradores) · `types` Ver o cambiar los tipos (administradores) · `pause` Pausar o silenciar alertas · `resume` Reanudar alertas · `settings` Configuración de este chat · `help` Ayuda y enlace a la web |
| `all_private_chats` | `status`, `battery`, `routers`, `help` |

Descripción (`setMyDescription`): `Avisos de los problemas de la malla de ${PROJECT_NAME}. Añádeme a un grupo o canal. Más información: https://${PROJECT_DOMAIN}/bots`. Corta: `Alertas de la malla de ${PROJECT_NAME}`.

**Enlaces para la página de bots** (con el usuario de la configuración del portal):

| Acción | Enlace |
|---|---|
| Abrir el bot en privado | `https://t.me/<usuario>` |
| Añadirlo a un grupo | `https://t.me/<usuario>?startgroup=alertas` |
| Añadirlo a un canal como administrador | `https://t.me/<usuario>?startchannel&admin=post_messages+delete_messages` |

**`envio.contenido`** de Telegram: `{"text": "…", "parse_mode": "HTML", "disable_notification": false, "link_preview_options": {"is_disabled": true}}`. `mensaje_id` = `message_id` devuelto; `chat_envio_id` = `chat.id` al que se envió.

**Errores de la Bot API → clases del motor** (`README.md` UT-08.5):

| Respuesta | Excepción de aiogram | Clase |
|---|---|---|
| `429` | `TelegramRetryAfter` | `ErrorTransitorio(retry_after)` |
| `5xx`, red, timeout | `TelegramServerError`, `TelegramNetworkError` | `ErrorTransitorio` |
| `400` con `migrate_to_chat_id` | `TelegramMigrateToChat` | `DestinoMigrado(nuevo_id)` |
| `403` *bot was kicked*, *bot is not a member*, *group chat was deleted* | `TelegramForbiddenError` | `DestinoPerdido` (`expulsado`) |
| `400` *chat not found* | `TelegramBadRequest` | `DestinoPerdido` (`chat_inexistente`) |
| Otros `403`; `400` *not enough rights*, *CHAT_WRITE_FORBIDDEN* | `TelegramForbiddenError`, `TelegramBadRequest` | `ErrorDestino` |
| Otros `400` (*can't parse entities*, *message is too long*) | `TelegramBadRequest` | `ErrorDestino` y registro `error` (fallo propio) |
| `401` | `TelegramUnauthorizedError` | Token inválido o revocado: sale con código 1 |

`plataforma.ultimo_ok` de `/health` se renueva con cada `getUpdates` o envío correctos.

## Unidades de trabajo

### UT-08.1.1 — Conexión, arranque y publicación de comandos
- **Comportamiento:** *long polling* con los `allowed_updates` de arriba; `getMe`, comandos y descripciones al arrancar; descarte de comandos antiguos.
- **Casos borde:** token inválido (código 1 sin mostrarlo); Telegram inaccesible al arrancar (reintenta, `/health` en `503` tras 5 min); descripciones ya iguales (no se llama a `set*`).
- **Aceptación:** tras arrancar, el menú de comandos de un grupo muestra los 7 y el privado 4; ningún registro contiene el token.

### UT-08.1.2 — Altas, bajas y migración de destinos
- **Comportamiento:** tabla de `my_chat_member`, reactivación con filtros por defecto, alta perdida, migración, saludo.
- **Casos borde:** bot añadido a un canal sin `can_post_messages` y promovido después; grupo creado con el bot dentro; migración con fila duplicada; `my_chat_member` de un chat privado (bloqueo del bot: se ignora).
- **Aceptación:** escenarios 1–3 y 8.

### UT-08.1.3 — Comandos, permisos y privacidad
- **Comportamiento:** reconocimiento de comandos, `es_admin`, reglas de canal (borrado diferido), temas, privado, límite por chat; descarte silencioso de lo que no es comando.
- **Detalle:** delega en `nucleo/comandos.py` con `max_caracteres = 4000`; la plataforma solo publica.
- **Casos borde:** administrador anónimo; mensaje en nombre de un canal vinculado; `/levels@otro_bot`; comando editado (no se procesa: `edited_message` no se pide).
- **Aceptación:** escenarios 4–6 y 9.

### UT-08.1.4 — Pintado HTML y envío
- **Comportamiento:** implementa `enviar(destino, contenido, responder_a)` del motor: HTML escapado, hilo, sin previsualización, notificación silenciosa en `bajo` y resoluciones; clasificación de errores de la tabla.
- **Casos borde:** nombre largo con `<`, `&` o emojis; hilo en otro `chat_envio_id`; mensaje original borrado; resumen con 20 etiquetas.
- **Aceptación:** pruebas de instantánea del HTML de cada caso de `README.md` §4.3; con un servidor falso de la Bot API, cada fila de la tabla de errores acaba en su clase.

## Escenarios de prueba

1. **Alta en grupo.** Dado el bot fuera de un grupo de prueba, cuando un miembro lo añade con el enlace `startgroup`, entonces se crea el destino (`clase = group`, filtros `NULL`), el grupo recibe el saludo con los filtros por defecto y una `abierta` `alto · infraestructura` simulada llega en menos de 30 s.
2. **Canal sin permiso de publicar.** Dado un canal donde el bot entra como administrador sin `can_post_messages`, entonces el destino queda inactivo `sin_permiso`; cuando le dan el permiso, entonces se reactiva sin publicar saludo.
3. **Migración con hilo.** Dado un grupo con la apertura de una alerta enviada, cuando el grupo pasa a supergrupo y llega la `resuelta`, entonces sale en el supergrupo (id nuevo), sin respuesta, y `destino.plataforma_id` es el nuevo.
4. **Comando para otro bot.** Dado un grupo con varios bots, cuando alguien escribe `/status@otro_bot`, entonces no responde; con `/status@<usuario>` responde.
5. **Administrador anónimo.** Dado un administrador que escribe como el grupo, cuando envía `/levels bajo medio alto`, entonces cambian los riesgos y responde `Riesgos activos en este chat: bajo, medio, alto.`; un miembro normal con el mismo comando recibe `Solo los administradores pueden cambiar los filtros.` y nada cambia.
6. **Comando en canal.** Dado un canal, cuando se publica `/types infraestructura clientes`, entonces el bot responde y a los 60 s desaparecen el comando y la respuesta.
7. **Caída de 2 h.** Dado el bot parado mientras lo añaden a un grupo y alguien escribe `/status` allí, cuando arranca, entonces da de alta el grupo (con saludo) y no responde al `/status` antiguo.
8. **Expulsión.** Dado un grupo con 4 envíos pendientes por el límite de 10/min, cuando expulsan al bot, entonces el destino queda inactivo `expulsado`, los 4 pasan a `caducado` y no hay más llamadas a `sendMessage` para ese chat.
9. **Privacidad.** Dado el bot administrador de un canal, cuando se publican 10 mensajes normales, entonces no se escribe nada en la base ni en el registro (salvo, en `DEBUG`, "actualización descartada" sin contenido).

---
> Creado: 2026-10-07 · Última revisión: 2026-10-08
