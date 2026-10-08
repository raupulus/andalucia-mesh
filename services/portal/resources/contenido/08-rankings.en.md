# 08 · Rankings

> `/rankings` · Spectrum consumption statistics, nodes at risk, link benchmarks, and traffic distribution across Andalusia.

## SEO

- **Título:** `Mesh Rankings & Activity · {PROJECT_NAME}`
- **Descripción:** `Discover which nodes consume the most airtime, nodes currently at risk, and top network contributors across Andalusia.`

## Borrador del texto

**H1:** Rankings

How the mesh behaves and who sustains it: identify which nodes are experiencing issues right now, which occupy the most channel airtime, and which gateways provide the greatest coverage. This is not a competition: it is a practical diagnostic tool to optimize community spectrum usage.

### Nodes at Risk

Nodes currently affected by open medium or high severity alerts: boot loops, critical battery levels, silent routers, offline gateways, or severe channel saturation.

- **High / Medium Risk:** Highlights issues demanding operator intervention.
- **Node & Province:** Identified by short name, long name, and node ID.
- **Reason & Time:** Summarizes the active rule triggering the incident.

[View all alerts →](/alertas)

### Timeframe Selector

Toggle between **Today**, **Yesterday**, **Last 7 days**, and **Last 30 days**. Completed periods remain frozen; the active period updates continuously.

### Spectrum Consumption by Node

Airtime occupied by packets transmitted natively by each node (excluding packets repeated for others). Excessive consumption is typically caused by overly frequent NodeInfo, position, or telemetry intervals. [How to tune them →](/configura-tu-nodo#intervalos)

- **Airtime & Packet Mix:** Detailed breakdown by packet type (NodeInfo, Position, Telemetry, Text, and Neighbors).

*Airtime is calculated based on payload byte length and LoRa modulation parameters. Each packet is counted once regardless of how many gateways receive it.*

### Network Rankings Catalog

- **Top Gateway Coverage:** Unique nodes heard by each gateway.
- **Essential Gateways:** Packets received exclusively by a single gateway—coverage no one else provides.
- **Longest Direct Links:** Maximum line-of-sight distance between a node and receiving gateway without intermediate hops.
- **Direct Link Quality:** Best average SNR on direct links with meaningful sample sizes.
- **Best Connected Nodes:** Highest number of verified direct RF neighbors.
- **Most Stable Nodes:** Longest continuous uptime without rebooting.
- **Healthiest Solar Stations:** Battery-powered nodes maintaining the highest minimum discharge voltage.
- **Active Chatters:** Volume of text messages shared on public channels.

### Traffic Distribution (Packet Mix)

Visualizing the proportion of network bandwidth consumed by each packet type. Text messages usually represent a tiny fraction; background automated telemetry constitutes the vast majority.
