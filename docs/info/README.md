# Documentación Técnica Viva — Índice Maestro

Documentación técnica canónica del proyecto. Todavía no hay código: cada documento de pieza describe lo **especificado** para implementarla y desplegarla. Al implementar un módulo, su documento pasa a describir lo real (plantilla [`_MODULE_TEMPLATE.md`](_MODULE_TEMPLATE.md)) y se actualiza en el mismo commit que el código.

## Empieza aquí

1. [architecture-map.md](architecture-map.md): qué hay montado y cómo se relaciona (mapa conceptual).
2. [overview.md](overview.md): qué es el proyecto, qué se construye, decisiones, descartes y recursos.
3. [integration.md](integration.md): contrato común entre piezas (hosts, redes, MQTT, flujo `decoded`, socket, bases, vistas, API, salud, configuración común, estructura del repositorio). **Si un documento de pieza y este discrepan, manda este.**
4. [deployment.md](deployment.md): requisitos, fases y orden de despliegue.
5. [business-rules.md](business-rules.md): **objetivos y reglas de negocio obligatorias**.

## Documentos generales

- [commands.md](commands.md): catálogo de comandos y scripts.
- [decisiones-tecnicas.md](decisiones-tecnicas.md): decisiones deliberadas que no se deben «arreglar».
- [DESIGN.md](DESIGN.md): sistema visual del portal (obligatorio).
- [COMPONENTS.md](COMPONENTS.md): componentes de interfaz.
- [_MODULE_TEMPLATE.md](_MODULE_TEMPLATE.md): plantilla de módulo.

## Piezas (en orden de despliegue)

| Pieza | Documentación | Código | Estado |
|---|---|---|---|
| Infraestructura | [infrastructure/](infrastructure/README.md): [servidor](infrastructure/01-server.md), [PostgreSQL](infrastructure/02-postgresql.md), [Nginx y DNS](infrastructure/03-nginx-dns.md), [operación](infrastructure/04-operations.md) | `infrastructure/` | Desplegado y verificado |
| Broker MQTT (Mosquitto) | [mosquitto/](mosquitto/README.md) | `integrations/mosquitto/` | Desplegado y verificado |
| MeshView | [meshview/](meshview/README.md) | `integrations/meshview/` | Desplegado y verificado |
| PotatoMesh | [potatomesh/](potatomesh/README.md) | `integrations/potatomesh/` | Desplegado y verificado |
| adaptador-potato | [potatomesh/adaptador-potato.md](potatomesh/adaptador-potato.md) | `services/adaptador-potato/` | Desplegado y verificado |
| sync-peers | [potatomesh/sync-peers.md](potatomesh/sync-peers.md) | `services/sync-peers/` | Desplegado y verificado |
| Ingesta | [ingesta/](ingesta/README.md): [entrada y descifrado](ingesta/01-input-decryption.md), [decodificación y deduplicación](ingesta/02-decoding-dedup.md), [provincias y registros](ingesta/03-provinces-registry.md), [persistencia y retención](ingesta/04-storage-retention.md), [vistas contrato](ingesta/05-contract-views.md), [flujo `decoded`](ingesta/06-decoded-stream.md) | `services/ingesta/` | Especificado |
| Portal | [portal/](portal/README.md): módulos 01–14 y [páginas](portal/pages/README.md) | `services/portal/` | Especificado |
| Detector de alertas | [detector-alertas/](detector-alertas/README.md): [motor](detector-alertas/01-rule-engine.md), [catálogo de reglas](detector-alertas/02-rule-catalog.md), [socket y persistencia](detector-alertas/03-socket-persistence.md) | `services/detector-alertas/` | Especificado |
| Bots y webhooks | [bots-webhooks/](bots-webhooks/README.md): [Telegram](bots-webhooks/01-bot-telegram.md), [Discord](bots-webhooks/02-bot-discord.md), [webhooks](bots-webhooks/03-webhooks.md) | `services/bot-telegram/`, `services/bot-discord/`, `services/webhooks/` | Especificado |
| Chat en directo | [chat-ws/](chat-ws/README.md) | `services/chat-ws/` | Especificado |

## Formato de los documentos de pieza

Ficha de desarrollo: 1 Contexto · 2 Alcance · 3 Stack y versiones · 4 Contratos · 5 Configuración · 6 Datos · 7 Unidades de trabajo (`UT-NN.n`) · 8 Despliegue · 9 Definición de hecho · 10 Escenarios de prueba · 11 Riesgos · 12 Referencias · Decisiones de detalle. Los módulos de una pieza: Objetivo, Especificación, Contratos propios, Unidades de trabajo, Escenarios de prueba.

## Integración con APIs de terceros

Consulte [apis/README.md](apis/README.md).

---
> Creado: 2026-10-07 · Última revisión: 2026-10-07
