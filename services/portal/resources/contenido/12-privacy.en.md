# 12 · Privacy Policy

> `/legal/privacidad` · Full disclosure of processed radio telemetry, retention periods, zero-cookie web policy, and opt-out instructions.

## SEO

- **Título:** `Privacy Policy · {PROJECT_NAME}`
- **Descripción:** `Data processing, retention policies, zero-cookie browsing, and opt-out controls for the {PROJECT_NAME} community mesh.`

## Borrador del texto

**H1:** Privacy Policy

Last updated: October 8, 2026

In summary:
- We process packets broadcast over radio on public channels that explicitly authorize cloud upload with **OK to MQTT**.
- Public web routes operate with a strict **zero-cookie, zero-tracker** policy.
- To exclude your node from public ingestion and maps, simply disable **OK to MQTT** on your device.
- For data rights and privacy inquiries: [{PROJECT_CONTACT}](mailto:{PROJECT_CONTACT}).

### 1. Data Controller

Raúl Caro Pastorino (@raupulus). Contact: [{PROJECT_CONTACT}](mailto:{PROJECT_CONTACT}).

### 2. Data Categories & Sources

The radio mesh is public, yet specific technical identifiers may correlate to individuals.

| Data Category | Source | Scope of Disclosure |
|---|---|---|
| Node ID, Short Name, Long Name | Radio broadcasts uploaded by community gateways | PotatoMesh, MeshView, rankings, alerts, bots, and public API |
| Position & Coarse Location | Transmitted with user-configured precision | PotatoMesh and MeshView. On portal & API, aggregated provincially only |
| Public Channel Messages | Over-the-air broadcasts on whitelist channels | PotatoMesh, MeshView, real-time WebSocket chat. Indexed by search engines |
| Node Telemetry (voltage, channel util, uptime) | Radio packets with OK to MQTT enabled | Telemetry tables, rankings, alerts, bots, and API |
| Gateway Identifiers | Authenticated MQTT connections | Ingestion audit logs and infrastructure alerts |
| Server Access Logs (IP address) | Web server HTTP requests | Server security logs and API rate limiting |
| Direct Inquiries | Emails sent to project contact | Handled privately by maintainer |

*What we never process: Private direct messages, custom encrypted channels, or exact personal residential coordinates.*

### 3. Purpose of Processing

- Visualizing mesh status, connectivity metrics, and regional rankings.
- Detecting infrastructure anomalies (battery depletion, boot loops, gateway outages).
- Managing gateway connections and providing public API services.
- Defending infrastructure against abusive traffic and DDoS attacks.

### 4. Legal Basis

Processing is grounded in legitimate interests to ensure the operation, health, and security of a citizen mesh network:
- Data is broadcast openly over public radio waves and uploaded strictly because the node operator selected "OK to MQTT".
- Only designated open public channels are ingested.
- The web portal serves aggregated counts, never exact locations.
- Data is governed by strict automatic retention and purging schedules.

### 5. Retention Periods

| Repository | Retention Schedule |
|---|---|
| MeshView | 14 days |
| PotatoMesh | 30 days |
| Raw Packets & Telemetry | 30 days for packets; 90 days for telemetry |
| Historical Alerts | 1 year |
| Web Server Logs (IP) | 30 days |
| Aggregated Anonymous Statistics | Permanent |

### 6. Third Parties & Hosting

Infrastructure runs in an EU data center ({HOSTING_LOCATION}) under {HOSTING_PROVIDER}. No data is sold, monetized, or transferred outside the European Economic Area.

### 7. How to opt out of the mesh portal

To prevent your node from appearing on maps and dashboards:
1. Open the Meshtastic app on your device.
2. In `Radio Configuration → LoRa` or `Module Configuration → MQTT`, **disable OK to MQTT**.
3. Gateways respect this flag and will immediately stop uploading your node's packets.
4. Historical records will automatically purge when their retention window expires. For expedited removal, contact [{PROJECT_CONTACT}](mailto:{PROJECT_CONTACT}).

### 8. Website Visitors

Public visitors browse this portal without cookies, local sessions, or analytics trackers. Web server access logs retain IP addresses for a maximum of 30 days strictly for rate-limiting and security diagnostics.
