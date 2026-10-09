# Mapa del sistema

> Qué hay montado y cómo se relaciona. `[ ]` = integración de terceros (se configura, no se programa). `( )` = desarrollo propio. Contratos exactos en [`integration.md`](integration.md).

```text
NODOS ──radio LoRa──▶ GATEWAYS ──MQTT 1883/8883──▶ [Mosquitto]   solo subida, un usuario por gateway
                                                        │
                                                        │ msh/EU_868/#  (paquetes cifrados)
           ┌────────────────────────────┬───────────────┴─────────────────┐
           ▼                            ▼                                 │
      [MeshView]                (adaptador-potato)                        │
      visor técnico                     │ POST /api                       │
                                        ▼                                 │
      [PotatoMesh] ◀── POST /api ── (sync-peers) ◀── APIs públicas        │
      mapa + chat                       │            de otras instancias  │
                                        │                                 │
                                        │ publica snm/v1/peer/#           │
                                        ▼                                 │
                                   [Mosquitto]                            │
                                        │                                 │
                                        ├─────────────────────────────────┘
                                        ▼
                                    (ingesta) ──▶ PG "ingest" (TimescaleDB)
                                        │         Filtro anti-duplicados (15 min)
                                        │         Guarda toda la red regional
                                        │
                                        │ publica snm/v1/decoded/#
                                        │ (paquete único, ya desduplicado y descifrado)
                                        ▼
                                   [Mosquitto]
                                  ┌─────┴────────────────────────┐
                                  ▼                              ▼
                         (detector-alertas) ──▶ PG "alertas"  (chat-ws)
                                  │                            WebSocket público
                                  │ socket Unix                chat en vivo regional
                                  ▼ /run/snm/alertas.sock
                         ┌────────┴───────┬──────────────┐
                         ▼                ▼              ▼
                  (bot-telegram)    (bot-discord)   (webhooks)
                         │                │
                         └── comandos: GET /api/v1 ──┐
                                                     ▼
     PG "ingest" ──vistas api_* (solo lectura)──▶ (PORTAL) ◀──vistas api_*── PG "alertas"
                                                  web · API /api/v1 · panel /admin · mapa
                                                  el panel consulta /health de todos
                                                  /configurador ──▶ [MeshConfig]

Base común: servidor Debian 13 · Docker · [Nginx] nativo, HTTPS y MQTT-TLS · [PostgreSQL 17 + TimescaleDB] nativo · [Mosquitto] nativo
```

## Piezas

| Propias (9) | Terceros (6) |
|---|---|
| ingesta · portal · detector-alertas · bot-telegram · bot-discord · webhooks · adaptador-potato · sync-peers · chat-ws | Mosquitto · MeshView · PotatoMesh · MeshConfig · Nginx · PostgreSQL/TimescaleDB |

## Cómo se comunican

| Canal | De → a | Qué viaja |
|---|---|---|
| MQTT `msh/EU_868/#` | Gateways → Mosquitto → MeshView, adaptador-potato, ingesta | Paquetes de radio tal cual (protobuf cifrado) |
| MQTT `snm/v1/peer/#` | sync-peers → Mosquitto → ingesta | Eventos leídos de instancias vecinas (JSON) para deduplicación y alertas |
| MQTT `snm/v1/decoded/#` | ingesta → detector-alertas, chat-ws | Un JSON por paquete único, ya desduplicado, con datos del nodo |
| HTTP `POST` interno | adaptador-potato y sync-peers → PotatoMesh | Nodos, posiciones, mensajes, telemetría |
| Socket Unix | detector-alertas → bots y webhooks | Transiciones de alertas de toda la región (abierta, actualizada, resuelta) |
| Vistas SQL `api_*` | ingest y alertas → portal | Solo lectura, con roles dedicados (cobertura regional completa) |
| HTTP `GET /api/v1` | Bots e integradores → portal | Estado, rankings, routers, alertas |
| WebSocket `/ws/chat` | chat-ws → cualquiera | Mensajes de texto de los canales suscritos en toda la región |
| HTTP `/configurador` | Portal → MeshConfig | Interfaz web de configuración (Web Serial, BLE, QR, YAML) |
| HTTP `/health` | Panel → todos | Estado de cada pieza |

Ninguna pieza lee la base de datos de otra salvo el portal, y solo por vistas `api_*`.

---
> Creado: 2026-10-07 · Última revisión: 2026-10-09
