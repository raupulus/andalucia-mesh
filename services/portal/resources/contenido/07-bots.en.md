# 07 · Bots

> `/bots` · How community bots operate and how to invite, configure, and manage them on your Telegram groups and Discord servers.

## SEO

- **Título:** `Telegram and Discord Bots · {PROJECT_NAME}`
- **Descripción:** `Receive regional Andalucía Mesh alerts directly inside your Telegram group or Discord server, and query live mesh telemetry via commands.`

## Borrador del texto

**H1:** Bots

Bring live mesh alerts into your community Telegram groups and Discord servers. Bots automatically notify when the system detects an infrastructure issue (low repeater batteries, reboot loops, offline gateways, channel storms) and when the incident resolves. They also answer query commands regarding overall network health.

### What they alert on

Every alert carries an assigned **risk level** and **incident type**.

| Risk Level | Meaning |
|---|---|
| High | Active system failure or severe mesh degradation |
| Medium | Genuine risk to infrastructure or localized comarca; action advised |
| Low | Informational notice; no immediate harm to communication |

| Incident Type | Scope |
|---|---|
| Infrastructure | Routers, mountaintop repeaters, MQTT gateways, channel congestion, or network-wide issues |
| Clients | Isolated end-user node events, such as dying personal battery or non-standard configuration |

View all active and historical alerts on the [Alerts Panel](/alertas).

### Telegram Bot

[Open @{TELEGRAM_BOT_USERNAME} in Telegram](https://t.me/{TELEGRAM_BOT_USERNAME})

**In a group**

1. Add `@{TELEGRAM_BOT_USERNAME}` as a member to your group.
2. The bot posts an introduction message and starts forwarding alerts using default filters.
3. Group admins can customize subscriptions anytime using `/levels` and `/types`.

**In a channel**

1. Add the bot as an **administrator** with "Post Messages" permission.
2. An admin posts the configuration command directly in the channel (e.g., `/levels medio alto`). The bot applies the setting and immediately deletes the command to keep the channel uncluttered.

**In private direct message**

Query live status commands (`/status`, `/battery`, `/routers`). No automated push alerts are sent to private chats to avoid disturbance.

**To remove the bot**, simply kick or ban it from the group or channel. It detects removal and cancels subscriptions instantly.

### Discord Bot (Coming Soon)

**Status:** Under development and certification testing.

In Discord, the bot joins a server rather than an individual channel. Once invited, you pick which channel receives alerts.

1. Open the invite link and select your server. The bot requests permissions to view channels, send messages, attach links, and read history.
2. In the desired alert channel, a member with channel management permissions types `/subscribe`.
3. Adjust subscriptions in that channel with `/levels` and `/types`. You can enable multiple channels with different filter profiles.

**To remove:** type `/unsubscribe` in the channel, or kick the bot from your server.

### Available Commands

| Command | Description | Permissions |
|---|---|---|
| `/status` | Overall mesh status: active nodes in 24h, active routers, publishing gateways, channel saturation in Andalusia and per province, open alerts | Anyone |
| `/battery` | Router and repeater battery levels, sorted lowest to highest | Anyone |
| `/routers` | All routers with battery voltage, channel utilization (`chutil`), and airtime transmit percentage (`tx`) | Anyone |
| `/levels [risks...]` | Displays or updates subscribed risk severities. Example: `/levels medio alto` | View: anyone. Edit: admins |
| `/types [types...]` | Displays or updates subscribed incident types. Example: `/types infraestructura clientes` | View: anyone. Edit: admins |
| `/settings` | Displays active alert configuration for current channel | Anyone |
| `/help` | Quick syntax summary and link to documentation | Anyone |
| `/subscribe` (Discord) | Activates alerts in the current channel | Channel managers |
| `/unsubscribe` (Discord) | Deactivates alerts in the current channel | Channel managers |

### Default Filters

Newly joined channels start with default filters (`{BOT_RIESGOS_DEFECTO}` / `{BOT_TIPOS_DEFECTO}`), covering moderate and severe infrastructure incidents.

### Channel Rate-Limiting & Anti-Spam

The bot enforces channel rate limits, bundles burst notifications from the same rule, and only re-notifies open alerts if their risk severity escalates.

### Webhooks

Prefer receiving alerts on your own server or monitoring dashboard? We deliver signed `POST` requests to your endpoint. See [API & Webhooks documentation](/api).

### Privacy

Bots store only the chat ID and selected filters. They never read or log member conversations and operate in strict Telegram privacy mode.
