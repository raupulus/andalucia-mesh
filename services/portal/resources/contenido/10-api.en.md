# 10 · Public API & Webhooks

> `/api` · Complete public documentation for the read-only JSON API and signed alert webhooks.

## SEO

- **Título:** `Public API & Webhooks · {PROJECT_NAME}`
- **Descripción:** `Public read-only API of {PROJECT_NAME}: mesh telemetry, provincial stats, rankings, routers, and alerts in JSON, no registration needed.`

## Borrador del texto

**H1:** Public API

All public data of {PROJECT_NAME} available via clean JSON endpoints: network status, node counts, channel load per province, rankings, routers, and alerts. This is the exact same API powering this portal and community bots. It is strictly read-only and requires no account registration.

### Fundamentals

| Parameter | Specification |
|---|---|
| Base URL | `https://{PROJECT_DOMAIN}/api/v1` |
| HTTP Methods | `GET` only |
| Authentication | None |
| Content-Type | `application/json` |
| Rate Limit | 60 requests per minute per IP |
| CORS | Enabled (`*`) for `GET` |
| Timestamps | ISO 8601 in UTC |
| Provinces | ISO 3166-2: `ES-AL`, `ES-CA`, `ES-CO`, `ES-GR`, `ES-H`, `ES-J`, `ES-MA`, `ES-SE` |

All responses provide:
- `generated_at`: Timestamp when the dataset was built.
- `notes`: Operational guidance (e.g. data strictly reflects nodes emitting with OK to MQTT enabled).
- `stale: true`: Returned when upstream cache is served during ingestion restarts.

*No exact coordinates are ever disclosed. The API serves aggregated statistics, public names, hex IDs, and provincial assignments.*

### Endpoints Overview

| Route | Description | Cache TTL |
|---|---|---|
| `GET /stats/summary` | Global mesh health overview | 60 s |
| `GET /stats/provinces` | Node counts and channel utilization per province | 60 s |
| `GET /stats/rankings` | Catalog of available rankings | 1 h |
| `GET /stats/rankings/{id}` | Detailed leaderboard metrics | 60 s – 15 min |
| `GET /stats/traffic-mix` | Packet airtime and count distribution | 5 min |
| `GET /routers` | Active routers, battery voltage, channel util, and airtime | 60 s |
| `GET /alerts` | Filterable list of open and resolved alerts | 30 s |
| `GET /alerts/{id}` | Alert details with historical event lifecycle | 30 s |
| `GET /alerts/catalog` | Catalog of all active detection rules | 1 h |
| `GET /nodes/at-risk` | Nodes currently experiencing open alerts | 30 s |

### Webhooks

For external servers and dashboards wishing to receive real-time notifications when an alert opens, updates severity, or resolves, we provide HMAC-signed HTTP `POST` webhooks.

- **Payload:** Full alert schema with transition metadata.
- **Security:** Verified using `X-Hub-Signature-256` HMAC SHA-256 signatures.

To register a webhook endpoint for your monitoring server, contact [{PROJECT_CONTACT}](mailto:{PROJECT_CONTACT}).
