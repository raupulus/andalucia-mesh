# 04 · How it is governed

> `/como-se-gestiona` · Transparent overview of who administers the project, how decisions are made, where it runs, and how the mesh and data are protected.

## SEO

- **Título:** `How it is governed · {PROJECT_NAME}`
- **Descripción:** `Who administers {PROJECT_NAME}, where the server is located, how decisions are made, and how the mesh and user privacy are safeguarded.`

## Borrador del texto

**H1:** How it is governed

A lean, transparent project maintained by a single technical coordinator with clear and open accounts.

### Who administers it

{AUTOR_NOMBRE} ({AUTOR_NICK}) administers the entire system: virtual servers, software services, gateway onboarding, alerts, bots, and the public web portal. Gateways are hosted and operated by community volunteers uploading packets under their own dedicated credentials. The project never manages individual user nodes: each device remains the sole responsibility of its respective owner.

### How decisions are made

Architectural and technical decisions are steered by Raúl as the project initiator and technical maintainer. Inquiries, suggestions, and defect reports are welcome at [{PROJECT_CONTACT}](mailto:{PROJECT_CONTACT}). New strategic routers and high-elevation repeaters are coordinated with the community prior to deployment to preserve overall mesh channel capacity.

### Where it runs

- All services operate on a dedicated cloud instance in a European data center ({HOSTING_LOCATION}). Hosting details are documented in the [privacy policy](/legal/privacidad).
- Every service runs isolated in containers: if one encounters an issue, remaining services continue unimpeded.
- Combines community open-source software, such as MeshView and PotatoMesh, with tailored internal developments: this portal, ingestion telemetry pipeline, rule-based anomaly detector, bots, and the public API.
- Third-party software versions are pinned and thoroughly validated before upgrading.

### How the mesh is protected

- The server is strictly uplink-only. Nothing received from the Internet is ever repeated back onto the radio spectrum, even if a gateway accidentally has downlink enabled.
- Every gateway uses isolated MQTT credentials and may only publish on its designated topic tree.
- Only approved public community channels from an established whitelist are ingested: the primary channel `SFNarrow`, regional channels `Andalucia` and `Iberia`, and each province-specific channel (`Almeria`, `Cadiz`, `Cordoba`, `Granada`, `Huelva`, `Jaen`, `Malaga`, `Sevilla`, plus `Ceuta` and `Melilla`). Direct messages and private non-whitelisted channels are strictly ignored and discarded.
- An automated detector monitors the mesh around the clock, warning of dying repeater batteries, reboot loops, offline gateways, packet floods, and anomalous hop counts.

### Service Availability

No formal SLA or uptime guarantee is provided. Services run on a single virtual host: in the event of an outage, portal, API, and bot services may be temporarily unavailable. Service health is monitored continuously from a private administration dashboard.

### Costs and funding

Infrastructure, domain, and server hosting expenses are personally assumed by the project initiator. No donations are currently accepted; community support mechanisms may be evaluated once foundational development concludes.

### Your data

Details regarding collected metadata, retention policies, and opt-out procedures are detailed in our [privacy policy](/legal/privacidad).
