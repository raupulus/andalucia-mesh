# Mapa del sistema

> Qué hay montado y cómo se relaciona. `[ ]` = integración de terceros (se configura, no se programa). `( )` = desarrollo propio. Contratos exactos en [`integration.md`](integration.md).

```text
NODOS ──radio LoRa──▶ GATEWAYS ──MQTT 1883/8883──▶ [Mosquitto]   solo subida, un usuario por gateway
                                                       │
                                                       │ msh/EU_868/#  (paquetes cifrados)
          ┌────────────────────────────┬───────────────┴─────────────────┐
          ▼                            ▼                                 ▼
     [MeshView]                (adaptador-potato)                    (ingesta) ──▶ PG "ingest"
     visor técnico                     │ POST /api                       │          (TimescaleDB)
                                       ▼                                 │ publica snm/v1/decoded/#
     [PotatoMesh] ◀── POST /api ── (sync-peers) ◀── APIs públicas        │ (paquete único, ya descifrado)
     mapa + chat                                   de otras instancias   │
                                                                         ▼
                                          ┌──────────────── [Mosquitto] ─────────────────┐
                                          ▼                                              ▼
                                 (detector-alertas) ──▶ PG "alertas"               (chat-ws)
                                          │ socket Unix /run/snm/alertas.sock      WebSocket público
                         ┌────────────────┼────────────────┐                      por canal de chat
                         ▼                ▼                ▼
                  (bot-telegram)    (bot-discord)     (webhooks)
                         │                │
                         └── comandos: GET /api/v1 ──┐
                                                     ▼
     PG "ingest" ──vistas api_* (solo lectura)──▶ (PORTAL Laravel + Filament) ◀──vistas api_*── PG "alertas"
                                                  web · API /api/v1 · panel /admin
                                                  el panel consulta /health de todos

Base común: servidor Debian 13 · Docker · [Nginx] nativo, HTTPS y MQTT-TLS · [PostgreSQL 17 + TimescaleDB] nativo · [Mosquitto] nativo
```

## Piezas

| Propias (9) | Terceros (5) |
|---|---|
| ingesta · portal · detector-alertas · bot-telegram · bot-discord · webhooks · adaptador-potato · sync-peers · chat-ws | Mosquitto · MeshView · PotatoMesh · Nginx · PostgreSQL/TimescaleDB |

## Cómo se comunican

| Canal | De → a | Qué viaja |
|---|---|---|
| MQTT `msh/EU_868/#` | Gateways → Mosquitto → MeshView, adaptador-potato, ingesta | Paquetes de radio tal cual (protobuf cifrado) |
| MQTT `snm/v1/decoded/#` | ingesta → detector-alertas, chat-ws | Un JSON por paquete único, descifrado, con datos del nodo |
| HTTP `POST` interno | adaptador-potato y sync-peers → PotatoMesh | Nodos, posiciones, mensajes, telemetría |
| Socket Unix | detector-alertas → bots y webhooks | Transiciones de alertas (abierta, actualizada, resuelta) |
| Vistas SQL `api_*` | ingest y alertas → portal | Solo lectura, con roles dedicados |
| HTTP `GET /api/v1` | Bots e integradores → portal | Estado, rankings, routers, alertas |
| WebSocket `/ws/chat` | chat-ws → cualquiera | Mensajes de texto de los canales suscritos |
| HTTP `/health` | Panel → todos | Estado de cada pieza |

Ninguna pieza lee la base de datos de otra salvo el portal, y solo por vistas `api_*`.

---
> Creado: 2026-10-07 · Última revisión: 2026-10-07
