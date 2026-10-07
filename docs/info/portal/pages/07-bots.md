# 07 · Bots

> `/bots` · Explicar qué hace cada bot y cómo añadirlo, configurarlo y quitarlo de tus propios grupos y canales, sin otra documentación · `../06-bots-page.md` (y `../../bots-webhooks/`)

## SEO

- **Título:** `Bots de Telegram y Discord · {PROJECT_NAME}` (48 caracteres)
- **Descripción:** `Recibe en tu grupo de Telegram o en tu servidor de Discord las alertas de la malla de Andalucía y consulta su estado con comandos.` (130)

## Estructura

| # | Sección | Componente (`DESIGN.md`) |
|---|---|---|
| 1 | H1 y entradilla | Tipografía H1 + Texto |
| 2 | Qué avisan: riesgos y tipos | Chips de severidad (riesgos) + Tablas |
| 3 | Bot de Telegram | Botones (secundario "Abrir en Telegram") + pasos numerados del Bloque de ajustes |
| 4 | Bot de Discord | Botones (secundario "Invitar a tu servidor") + pasos numerados del Bloque de ajustes |
| 5 | Comandos | Tablas |
| 6 | Ejemplos de respuesta | Bloque de texto preformateado (formato de `../../bots-webhooks/`) |
| 7 | Filtros | Texto corrido + Tablas |
| 8 | Así es un aviso | Bloque de texto preformateado (formato de `../../bots-webhooks/`) + Chips de severidad para la leyenda |
| 9 | Para no llenar tu canal | Texto corrido |
| 10 | Webhooks | Texto corrido con enlace a `/api#webhooks` |
| 11 | Privacidad | Texto corrido |

Sin botón primario: los dos bots tienen el mismo peso, así que ambos botones son secundarios (`DESIGN.md`: un solo primario por pantalla). Sin logos de Telegram ni de Discord: solo su nombre en texto.

Correspondencia de chips (la misma que los colores de los bots): `alto` → `critico`, `medio` → `aviso`, `bajo` → `info`, resuelta → `correcto`. El chip lleva siempre icono y palabra ("Alto", "Medio", "Bajo", "Resuelta").

## Borrador del texto

Cada `###` es un H2 de la página.

**H1:** Bots

Lleva las alertas de la malla a tu grupo de Telegram o a tu servidor de Discord. Los bots avisan cuando el sistema detecta un problema (un repetidor con la batería baja, un nodo en bucle de reinicio, un gateway que deja de publicar…) y cuando se resuelve. También responden a comandos para consultar el estado de la red.

### Qué avisan

Cada alerta tiene un **riesgo** y un **tipo**.

| Riesgo | Qué significa |
|---|---|
| Alto | Fallo activo o daño a la malla |
| Medio | Riesgo real para un nodo o una zona; conviene actuar |
| Bajo | Conviene saberlo; no hay daño inmediato |

| Tipo | Qué cubre |
|---|---|
| Infraestructura | Routers, repetidores y gateways, y cualquier problema que afecte a muchos nodos o a toda la malla (spam, ráfagas, canal saturado), lo cause quien lo cause |
| Clientes | Problemas de un nodo concreto que no es infraestructura, como la batería baja de un nodo personal |

Puedes ver todas las alertas, abiertas y resueltas, en la página de [Alertas](/alertas).

### Bot de Telegram

**Botón secundario:** "Abrir @{TELEGRAM_BOT_USERNAME} en Telegram" → `https://t.me/{TELEGRAM_BOT_USERNAME}`

**En un grupo**

1. Añade a @{TELEGRAM_BOT_USERNAME} como miembro (búscalo por su nombre de usuario).
2. El bot saluda y empieza a enviar alertas con los filtros por defecto.
3. Si quieres otros filtros, un administrador del grupo los cambia con `/levels` y `/types`.

**En un canal**

1. Añade el bot como **administrador** con permiso para publicar mensajes. En los canales de Telegram no se puede añadir como simple miembro.
2. Para cambiar los filtros, un administrador publica el comando en el canal (por ejemplo, `/levels medio alto`). El bot aplica el cambio y borra el mensaje del comando para no ensuciar el canal.

**En privado**

Escríbele y te responde a los comandos de consulta (`/status`, `/battery`, `/routers`). En privado no envía alertas.

**Para quitarlo**, expúlsalo del grupo o del canal. El bot lo detecta y deja de enviar.

### Bot de Discord (Próximamente)

**Estado:** Próximamente (en fase de desarrollo y homologación).
**Botón secundario:** Deshabilitado durante la fase de desarrollo ("Invitar bot a tu servidor (Próximamente)"). Enlace de producción previsto: `{DISCORD_INVITE_URL}`

En Discord el bot entra en un servidor, no en un canal. Por eso, después de invitarlo, eliges en qué canales publica.

1. Abrirás el enlace de invitación y elegirás tu servidor. El bot pide permiso para ver canales, enviar mensajes, insertar enlaces y leer el historial de mensajes.
2. En el canal donde quieras las alertas, alguien con permiso para gestionar canales escribe `/subscribe`. El canal queda activo con los filtros por defecto.
3. Ajusta los filtros con `/levels` y `/types` en ese mismo canal. Puedes activar varios canales, cada uno con sus filtros.

**Para quitarlo** de un canal, usa `/unsubscribe`. Para quitarlo del servidor entero, expúlsalo: se desactivan todos sus canales.

### Comandos

| Comando | Qué hace | Quién puede usarlo |
|---|---|---|
| `/status` | Estado general de la malla: nodos activos en 24 h, routers activos, gateways publicando, carga media del canal en Andalucía y por provincia, y alertas abiertas por riesgo y tipo | Cualquiera |
| `/battery` | Batería de los routers, de menor a mayor, con la hora de cada dato | Cualquiera |
| `/routers` | Todos los routers con su batería, la utilización del canal (`chutil`) y el porcentaje de tiempo que transmiten (`tx`) | Cualquiera |
| `/levels [riesgos…]` | Sin nada más, muestra los riesgos activos en este canal; con riesgos, los cambia. Ejemplo: `/levels medio alto` | Ver: cualquiera. Cambiar: administradores del grupo o canal (Telegram) o quien pueda gestionar canales (Discord) |
| `/types [tipos…]` | Igual, con los tipos. Ejemplo: `/types infraestructura clientes` | Igual que `/levels` |
| `/settings` | Configuración de este canal | Cualquiera |
| `/help` | Ayuda breve y enlace a esta página | Cualquiera |
| `/subscribe` (solo Discord) | Activa las alertas en este canal | Quien pueda gestionar canales |
| `/unsubscribe` (solo Discord) | Desactiva las alertas en este canal | Quien pueda gestionar canales |

### Ejemplos de respuesta

`/status`

```
Estado de la malla · {hora}
Nodos activos (24 h): {nodes_active_24h}
Routers activos (24 h): {routers_active_24h}
Gateways publicando: {gateways_publishing}
Saturación estimada del canal en Andalucía: {andalucia_avg} %
{provincia} {avg} % · {provincia} {avg} % · …
Alertas abiertas: {alto} alto · {medio} medio · {bajo} bajo
Infraestructura: {infraestructura} · Clientes: {clientes}
```

`/battery`

```
Batería de los routers (de menor a mayor)
{short} · {provincia} · {level} % · hace {tiempo}
…
Alimentados por red: {short}, {short}…
```

`/routers`

```
Routers vistos en los últimos 7 días
{short} · {provincia} · batería {level} % · chutil {chutil} % · tx {tx} %
…
```

`/levels medio alto`

```
Riesgos activos en este canal: medio, alto
```

`/settings`

```
Este canal recibe alertas de riesgo alto, de tipo infraestructura.
Activo desde el {fecha}.
```

`/help`

```
/status · /battery · /routers · /levels · /types · /settings
Cómo usar el bot: https://{PROJECT_DOMAIN}/bots
```

### Filtros

Cada canal tiene sus propios filtros; cambiarlos en uno no afecta a los demás. Al añadir el bot, empieza con estos:

| Filtro | Por defecto |
|---|---|
| Riesgos | {BOT_RIESGOS_DEFECTO} |
| Tipos | {BOT_TIPOS_DEFECTO} |

Es decir: los problemas que afectan a la red, sin las incidencias menores de nodos personales.

- Para recibirlo todo: `/levels bajo medio alto` y `/types infraestructura clientes`.
- Para recibir solo lo grave: `/levels alto`.

Los riesgos y tipos que admiten los comandos son los de esta página. Si se añade uno nuevo, aparecerá aquí y en los bots sin que tengas que hacer nada.

### Así es un aviso

Ejemplo:

```
🔴 ALTO · Infraestructura · Bucle de reinicio
{short} ({long}) · ROUTER · Cádiz
{short} se ha reiniciado 7 veces en la última hora
https://{PROJECT_DOMAIN}/alertas/{id}
```

- El icono marca el riesgo: 🔵 bajo · 🟠 medio · 🔴 alto · ✅ resuelta.
- Si la alerta cambia de riesgo o se resuelve, el bot responde al mensaje original, así cada alerta queda en su propio hilo.
- Si un problema afecta a muchos nodos a la vez, llega un solo mensaje con el número de nodos y un enlace a la lista.
- Nunca incluye coordenadas: solo la provincia.
- En Discord llega como mensaje enriquecido con el color del riesgo.

### Para no llenar tu canal

El bot agrupa en un solo resumen las ráfagas de alertas de la misma regla, limita los mensajes por minuto en cada canal y solo vuelve a avisar de una alerta abierta si cambia su riesgo.

### Webhooks

¿Quieres las alertas en tu propio sistema? Te las enviamos por webhook: una petición `POST` firmada a tu URL cada vez que una alerta se abre, cambia o se resuelve, con filtros por riesgo, tipo, provincia o nodo. Para darte de alta, escribe a {PROJECT_CONTACT}. [Detalles técnicos en la documentación de la API →](/api#webhooks)

### Privacidad

Los bots solo guardan el identificador de cada grupo o canal donde están, sus filtros y los avisos que han enviado allí (para responder en el mismo hilo y no repetirlos). Solo procesan los comandos: no guardan ni leen el resto de mensajes. En los grupos de Telegram funcionan además en modo privacidad, así que ni siquiera los reciben. Más en la [política de privacidad](/legal/privacidad).

## Datos dinámicos y configuración

| Dato | Origen |
|---|---|
| `{TELEGRAM_BOT_USERNAME}` | `config/proyecto.php` desde `.env` (`../06-bots-page.md`) |
| `{DISCORD_INVITE_URL}` | `config/proyecto.php` desde `.env` |
| Nombres y descripciones de riesgos y tipos | `GET /api/v1/alerts/catalog` (TTL 1 h). Los textos de las tablas de "Qué avisan" son el borrador de la descripción pública que debe servir el catálogo (`clasificacion.yaml`), tomado de `../../detector-alertas/README.md` |
| `{BOT_RIESGOS_DEFECTO}`, `{BOT_TIPOS_DEFECTO}` | Configuración del portal con los mismos nombres y valores que los bots: `alto` e `infraestructura` (`../../bots-webhooks/README.md`) |
| Comandos y quién puede usarlos | Texto en `resources/contenido/bots.md`; si cambian en los bots, se cambian aquí |
| Ejemplos de respuesta | Formato orientativo con los campos de `GET /api/v1/stats/summary` y `GET /api/v1/routers`; el formato definitivo está en `../../bots-webhooks/01-bot-telegram.md` y `02-bot-discord.md` y esta página lo copia. Son imágenes o texto generados a partir del formato, nunca de una conversación real |
| Ejemplo de aviso | Formato de `../../bots-webhooks/README.md` con datos inventados marcados como ejemplo |

## Supuestos aplicados

- Sin canal de alertas propio del proyecto por ahora: cada uno añade los bots a sus canales.
- Mensajes de ejemplo y código: componente "Bloque de código" de `DESIGN.md`.

---
> Creado: 2026-10-07 · Última revisión: 2026-10-08
