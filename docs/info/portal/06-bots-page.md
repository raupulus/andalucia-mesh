# 06.6 · Página de bots

> `/bots`: qué hace cada bot y cómo añadirlo, configurarlo y quitarlo de tus propios grupos y canales, más los webhooks para integraciones. Texto en `pages/07-bots.md`. Comportamiento real de los bots en `../bots-webhooks/` (la página no puede prometer nada que no hagan).

## Objetivo

Que una persona sin experiencia añada el bot a un grupo de Telegram o a un canal de Discord siguiendo solo esta página.

## Especificación

### Secciones

1. **Qué son.** Avisos de los problemas de la malla detectados por el sistema de alertas, con riesgo (`bajo`, `medio`, `alto`) y tipo (`infraestructura`, `clientes`). Nombres y descripciones de riesgos y tipos desde `GET /api/v1/alerts/catalog` (no escritos a mano).
2. **Bot de Telegram.** Botón secundario a `https://t.me/{TELEGRAM_BOT_USERNAME}`; añadir a un grupo, a un canal (como administrador con permiso de publicar), usarlo en privado (solo consultas, sin alertas), quitarlo. Pasos de `../bots-webhooks/01-bot-telegram.md`.
3. **Bot de Discord.** Botón secundario a `{DISCORD_INVITE_URL}`; activar un canal con `/subscribe`, desactivarlo con `/unsubscribe`, quitar el bot. Pasos de `../bots-webhooks/02-bot-discord.md`.
4. **Comandos.** Tabla común: `/status`, `/battery [provincia]`, `/routers [provincia]`, `/levels`, `/types`, `/settings`, `/help` (+ `/subscribe`, `/unsubscribe` solo en Discord), quién puede usarlos (administradores para los que cambian filtros) y un ejemplo de respuesta de cada uno.
5. **Filtros por defecto** al añadirlo: riesgos `{BOT_RIESGOS_DEFECTO}` (`medio, alto`) y tipos `{BOT_TIPOS_DEFECTO}` (`infraestructura`); cómo cambiarlos con `/levels` y `/types`.
6. **Así es un aviso.** Ejemplo con el formato real de `../bots-webhooks/README.md` §4.3 y datos inventados marcados como ejemplo (nunca capturas de conversaciones reales).
7. **Webhooks.** Qué se envía, cómo verificar la firma y cómo pedir el alta (correo a `PROJECT_CONTACT`); detalle técnico en `/api` (`../bots-webhooks/03-webhooks.md`).
8. **Privacidad.** Los bots guardan solo el id del chat o canal, sus filtros y los avisos enviados (1 año); no leen conversaciones ni guardan nombres de grupos o usuarios.

### Reglas de presentación

- Dos botones secundarios (mismo peso para ambos bots; un solo primario por pantalla, `DESIGN.md`). Sin logos de Telegram ni Discord: solo su nombre en texto (excepción decidida).
- Ejemplos en el componente "Bloque de código".
- Catálogo con TTL 1 h; si la API no responde, se muestran los nombres de riesgo y tipo sin descripción.
- Enlazada desde la navegación, desde "Sube los datos de tu nodo" (portada y `/conecta-tu-gateway`) y desde `/alertas`.

## Contratos propios

- `config/proyecto.php` → `bots.telegram_usuario` (`TELEGRAM_BOT_USERNAME`), `bots.discord_invitacion` (`DISCORD_INVITE_URL`), `bots.riesgos_por_defecto` (`BOT_RIESGOS_DEFECTO`), `bots.tipos_por_defecto` (`BOT_TIPOS_DEFECTO`). Deben coincidir con la configuración de los bots.
- Texto en `resources/contenido/bots.md`.

## Unidades de trabajo

- **UT-06.6.1 — Página y configuración.** *Aceptación:* ningún enlace de bot escrito a mano; con `DISCORD_INVITE_URL` vacío, la sección de Discord muestra "Próximamente" sin botón.
- **UT-06.6.2 — Catálogo en vivo.** Riesgos y tipos desde la API. *Aceptación:* un riesgo nuevo en `clasificacion.yaml` aparece en la página sin tocar el portal.
- **UT-06.6.3 — Ejemplos.** Respuestas y aviso con el formato de los bots. *Aceptación:* prueba que compara el ejemplo de aviso con el formateador de los bots (fixture compartido en el monorepo).

## Escenarios de prueba

1. **Dado** una persona sin experiencia, **cuando** sigue la sección de Telegram, **entonces** el bot queda en su grupo y responde a `/status`.
2. **Dado** la API caída, **cuando** se abre `/bots`, **entonces** la página carga con los textos y los riesgos sin descripción.
3. **Dado** `BOT_RIESGOS_DEFECTO=alto`, **cuando** se redespliega, **entonces** la sección 5 dice "alto".
4. **Dado** la página, **cuando** se inspeccionan imágenes y peticiones, **entonces** no hay logos ni recursos de Telegram o Discord.

---
> Creado: 2026-10-07 · Última revisión: 2026-10-07
