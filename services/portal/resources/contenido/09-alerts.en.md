# 09 · Alerts

> `/alertas` · Live listing of open and historical network alerts across Andalusia, classified by risk level and incident type.

## SEO

- **Título:** `Mesh Alerts · {PROJECT_NAME}`
- **Descripción:** `Anomalies detected across the Andalucía Mesh network (low batteries, boot loops, gateway downtime, channel spam) with risk levels and affected scopes.`

## Borrador del texto

**H1:** Alerts

The system inspects incoming packets from across the mesh and triggers an alert whenever conditions threaten network stability: low repeater battery levels, reboot loops, offline gateways, heavy channel saturation, or excessive packet rates. Alerts automatically resolve once regular conditions are restored.

### Risk Levels & Types

Every incident is tagged with a severity level and affected operational scope:

| Risk Level | Meaning |
|---|---|
| High | Active system failure or severe mesh degradation |
| Medium | Genuine risk to infrastructure or localized comarca; action advised |
| Low | Informational notice; no immediate harm to communication |

| Incident Type | Scope |
|---|---|
| Infrastructure | Routers, mountaintop repeaters, MQTT gateways, channel congestion, or network-wide issues |
| Clients | Isolated end-user node events, such as dying personal battery or non-standard configuration |

### If an alert points to your node

An alert is not an accusation: detections are automated and serve as constructive diagnostic assistance. Check the [Node setup guide](/configura-tu-nodo) to verify your settings. Most common issues are resolved by adjusting broadcast cadences, selecting `CLIENT_MUTE`, or reducing hop limit. If you believe an alert was triggered in error, contact [{PROJECT_CONTACT}](mailto:{PROJECT_CONTACT}).

### Receive live notifications

Stay informed through automated community bots or webhooks:
- [Telegram and Discord Bots](/bots)
- [Real-time Webhooks](/api)
