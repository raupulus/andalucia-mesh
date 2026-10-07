# Visión general del proyecto

> Qué es Sur Nodos en Mallas, qué se construye y con qué decisiones. Cómo se conectan las piezas: [`integration.md`](integration.md). Mapa: [`architecture-map.md`](architecture-map.md). Orden de despliegue: [`deployment.md`](deployment.md).

## 1. Qué es

Un portal y un conjunto de servicios para una malla LoRa (Meshtastic) regional. Está pensado para **Cádiz** (foco) y **Andalucía** (alcance), pero todo lo que depende de la instancia (nombre, dominio, contacto, canales, radio, centro del mapa) va en variables de entorno. Los gateways de la comunidad suben por MQTT a un servidor propio lo que oyen por radio. Con eso el proyecto:

1. **Enseña la malla:** mapa y chat en vivo (PotatoMesh), visor técnico de paquetes y rutas (MeshView), un portal propio con el mapa por provincias y chat en directo por WebSocket.
2. **La mide:** nodos por provincia, saturación del canal por provincia, rankings por hora, día, semana y mes, consumo de red por nodo.
3. **La protege:** detecta problemas en tiempo real (reinicios en bucle, baterías bajas, routers caídos, spam, saturación, malas configuraciones), los cataloga por riesgo y tipo y avisa por bots de Telegram y Discord y por webhooks.
4. **Ayuda a configurarla bien:** guía de configuración, conexión al broker, diagnóstico de cada nodo ("Revisa tu nodo") y enlaces de firmware.

Proyecto sin ánimo de lucro de **Raúl Caro Pastorino** (@raupulus · public@raupulus.dev · https://raupulus.dev). Nombre configurable con `PROJECT_NAME`. Sin marcas de terceros en lo público salvo las excepciones de [`decisiones-tecnicas.md`](decisiones-tecnicas.md).

## 2. Qué ve cada persona

| Quién | Qué usa |
|---|---|
| Visitante | Portal `${PROJECT_DOMAIN}`: tres tarjetas (MeshView, PotatoMesh, consumo y nodos en peligro), mapa por provincias con saturación del canal, cómo configurar su nodo, cómo subir datos, quién está detrás |
| Usuario de la malla | PotatoMesh (`potato.`), MeshView (`meshview.`), rankings, alertas, "Revisa tu nodo", firmware y apps |
| Operador de nodo o gateway | Credenciales propias del broker, avisos de los bots en sus grupos o canales con los filtros que elija |
| Integrador | API pública de solo lectura, webhooks firmados y chat en directo por WebSocket |
| Operador de la instancia | Panel `/admin`: estado de servicios, gateways, alertas |

Páginas públicas del portal: inicio, el proyecto, quién lo impulsa, cómo se gestiona, configura tu nodo, conecta tu gateway, bots, rankings, alertas (lista y ficha), revisa tu nodo, firmware y apps, API, aviso legal, privacidad y cookies. Contenido en [`portal/pages/`](portal/pages/README.md).

## 3. Qué se construye

| # | Pieza | Tipo | Qué hace | Documentación | Código |
|---|---|---|---|---|---|
| 1 | Infraestructura | Servidor | Docker, Nginx nativo, PostgreSQL 17 nativo + TimescaleDB, DNS, despliegue | [`infrastructure/`](infrastructure/README.md) | `infrastructure/` |
| 2 | Broker MQTT | Integración (Mosquitto) | Recibe a los gateways con usuario propio, solo subida | [`mosquitto/`](mosquitto/README.md) | `integrations/mosquitto/` |
| 3 | MeshView | Integración | Visor técnico de paquetes, rutas y nodos | [`meshview/`](meshview/README.md) | `integrations/meshview/` |
| 4 | PotatoMesh | Integración | Mapa y chat en vivo | [`potatomesh/`](potatomesh/README.md) | `integrations/potatomesh/` |
| 5 | adaptador-potato | Propio (Python) | Alimenta PotatoMesh desde MQTT | [`potatomesh/adaptador-potato.md`](potatomesh/adaptador-potato.md) | `services/adaptador-potato/` |
| 6 | sync-peers | Propio (Python) | Trae datos públicos de otras instancias PotatoMesh configuradas | [`potatomesh/sync-peers.md`](potatomesh/sync-peers.md) | `services/sync-peers/` |
| 7 | Ingesta | Propio (Python) | Descifra, deduplica, asigna provincia, guarda histórico y publica el flujo normalizado | [`ingesta/`](ingesta/README.md) | `services/ingesta/` |
| 8 | Portal | Propio (Laravel + Filament) | Web pública, API `/api/v1` y panel de operadores | [`portal/`](portal/README.md) | `services/portal/` |
| 9 | Detector de alertas | Propio (Python) | Detecta y cataloga alertas (riesgo × tipo) y las emite por socket Unix | [`detector-alertas/`](detector-alertas/README.md) | `services/detector-alertas/` |
| 10 | Bots y webhooks | 3 propios (Python) | Bot de Telegram, bot de Discord y webhooks | [`bots-webhooks/`](bots-webhooks/README.md) | `services/bot-telegram/`, `services/bot-discord/`, `services/webhooks/` |
| 11 | Chat en directo | Propio (Python) | WebSocket público de solo lectura por canal de chat | [`chat-ws/`](chat-ws/README.md) | `services/chat-ws/` |

Total: 9 desarrollos propios y 5 piezas de terceros (Mosquitto, MeshView, PotatoMesh, Nginx, PostgreSQL/TimescaleDB).

## 4. Decisiones que dan forma al proyecto

Resumen; el registro está en [`decisiones-tecnicas.md`](decisiones-tecnicas.md).

| Tema | Decisión |
|---|---|
| Reutilizar | PotatoMesh y MeshView de la comunidad; solo se desarrolla lo que no existe |
| Independencia | Servicios pequeños en Docker, cada uno desplegable por separado; se comunican solo por MQTT, HTTP o el socket de alertas; sin bridges con otros brokers |
| Bases de datos | PostgreSQL nativo del servidor para todo lo propio: una base y un rol por servicio. El portal solo lee vistas contrato `api_*` |
| Broker | Topic raíz `msh/EU_868`, lista blanca de canales en la ACL, canal primario siempre índice 0, solo clave por defecto; solo subida (nada vuelve a la radio); sin exclusión de nodos: quien no quiere aparecer desactiva OK to MQTT |
| Chat | Canales públicos de la lista, visibles e indexables; WebSocket de solo lectura por canal |
| Radio recomendada | SFNarrow: EU_868, BW 62, SF 7, CR 5, slot 4, 3 saltos (configurable) |
| Mapa | Ventana 7 días con opción 24 h; color por saturación = routers 60 % + `CLIENT` y `CLIENT_BASE` juntos 40 %, sin `CLIENT_MUTE`: ≤ 20 % verde, < 40 % naranja, ≥ 40 % rojo |
| API | Dentro del portal, en Laravel, solo lectura |
| Panel | `/admin` de Filament; sin página de estado pública |
| Alertas | Servicio independiente; riesgos bajo/medio/alto y tipos infrastructure/clientes ampliables; emisión por socket Unix |
| Bots | Aplicaciones independientes; filtros por canal; comandos `status`, `battery`, `routers` |
| Repositorio | Monorepo sin código de terceros; versiones de terceros fijadas a la última probada |
| Despliegue | Imágenes propias construidas en el servidor; sin CI ni registro de imágenes; copias de seguridad fuera del proyecto |

## 5. Descartado y por qué

| Qué | Motivo |
|---|---|
| Malla (analítica de comunidad) | Inmadura; la cubren los rankings propios |
| Uptime Kuma / página de estado | En el mismo servidor no sirve si cae el servidor; el estado está en el panel |
| MeshMonitor, Grafana, exportadores Prometheus | El panel es Filament; duplicarían la ingesta |
| SSE y difusión pública de alertas o eventos | Las alertas van por bots y webhooks; el único tiempo real público es el chat |
| Exclusión de nodos (opt-out) y feed filtrado | Lo que se sube ya viene filtrado por OK to MQTT |
| Bridge con otros brokers | Independencia |
| Otros mapas de comunidad | Redundantes con PotatoMesh y MeshView |
| Brokers con más funciones (EMQX…) | Sobredimensionados para unos 50 gateways |
| Federación nativa de PotatoMesh | Desactivada; los datos de otras instancias llegan por `sync-peers` |

Ideas aplazadas: [`../future/README.md`](../future/README.md).

## 6. Recursos de referencia

Dimensionado para el peor caso: 2.000 nodos, 50 gateways, 100 usuarios web simultáneos.

| Recurso | Necesidad (peor caso) |
|---|---|
| MQTT | 44 msg/s, ~107 kbit/s de entrada |
| CPU | < 1 núcleo sostenido; picos por la web |
| RAM | ~3,9 GB |
| Disco | ~34 GB |

| Servicio | Retención | Disco peor caso |
|---|---|---|
| MeshView | 14 días | 8,6 GB |
| ingesta (TimescaleDB) | Bruto 30 días, posiciones y telemetría 90, agregados por nodo 1 año, sin nodo indefinidos | ~6 GB |
| PotatoMesh | 30 días (limpieza propia) | 3,5 GB |
| Alertas, bots, webhooks | 1 año | < 1 GB |
| Sistema, imágenes, logs | — | ~15 GB |

RAM por pieza: PostgreSQL 1,5 GB · MeshView 400 MB · PotatoMesh + adaptador + sync 550 MB · portal 300 MB · detector 250 MB · bots y webhooks 240 MB · ingesta 150 MB · chat-ws 60 MB · Nginx 30 MB · Mosquitto 50 MB · sistema 300 MB.

Cuándo ampliar: disco > 70 %, RAM > 80 % sostenida, carga > 2,5 durante 5 min, MQTT > 200 msg/s sostenido.

## 7. Limitaciones conocidas

- Los recuentos son siempre un mínimo: solo se ve lo que suben los gateways con OK to MQTT.
- Si cae el servidor entero no hay aviso.
- La detección depende de lo que emite cada nodo: con telemetría cada 4–6 h, un bucle de reinicio tarda en verse.
- El firmware no avisa cuando un gateway se desconecta: se deduce de su silencio.
- PotatoMesh no lee MQTT: depende del adaptador propio mientras su proyecto no lo incorpore.

---
> Creado: 2026-10-07 · Última revisión: 2026-10-07
