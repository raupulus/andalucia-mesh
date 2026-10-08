# 06 · Connect your gateway

> `/conecta-tu-gateway` · Step-by-step instructions for an internet-enabled node operator to upload packets to the community server, with TLS and downlink disabled.

## SEO

- **Título:** `Connect your gateway · {PROJECT_NAME}`
- **Descripción:** `Upload what your node hears to the community mesh: open MQTT credentials, exact setup values, and why downlink is strictly disabled.`

## Borrador del texto

**H1:** Connect your gateway

A gateway is an internet-connected node that forwards packets heard over the air to our community server. The more gateways operating in the region, the more accurate and comprehensive our maps, statistics, and alerts become. Traffic flows in one direction only: nothing received over the internet is ever repeated back to radio.

### What you need

- A Meshtastic node equipped with WiFi or Ethernet, or tethered via the mobile app acting as an MQTT bridge.
- Configured according to our [node configuration guide](/configura-tu-nodo).
- Immediate access: you can connect right away using open community upload credentials.

### Steps

1. **Open MQTT settings** in the Meshtastic mobile or web app (`Module Configuration → MQTT`).
2. **Apply the parameters** listed in the table below.
3. **Verify:** Within minutes, your node will appear uploading packets in MeshView and PotatoMesh.

### Configuration Parameters

| Parameter | Required Value |
|---|---|
| MQTT Enabled | Yes |
| Address | `mqtt.{PROJECT_DOMAIN}` |
| Port | `8883` (mandatory TLS) |
| Username | `{MQTT_GATEWAY_USER}` |
| Password | `{MQTT_GATEWAY_PASSWORD}` |
| Encryption | Enabled |
| JSON | Disabled |
| TLS | Enabled |
| Root Topic | `{MQTT_TOPIC_ROOT}` |
| Map reporting | Enabled |
| Uplink | Enabled on `{PRIMARY_CHANNEL}` and whitelist channels |
| Downlink | Disabled across all channels (Always disabled) |
| OK to MQTT | Enabled |
| Ignore MQTT | Enabled |

### Whitelisted Server Channels

The ingestion server only accepts packets on these authorized channels, spelled exactly as shown including capitalization:

`{ALLOWED_CHANNELS}`

All of these are public channels: their messages appear on PotatoMesh and MeshView and can be indexed. Never transmit private or sensitive information on public channels.

### Rationale behind settings

- **Encryption Enabled:** Packets travel in native binary format. Only public channels with the default open key are decoded by the server.
- **JSON Disabled:** Modern firmware uses binary protobuf exclusively; JSON is obsolete and rejected.
- **Root topic `{MQTT_TOPIC_ROOT}`:** Enter manually. When pointing to a custom broker, firmware does not append regional paths automatically.
- **Mandatory TLS (Port 8883):** All broker connections are encrypted end-to-end to prevent eavesdropping and in-flight manipulation.
- **Map reporting:** Promotes your node identity and coordinates to regional map viewers.
- **OK to MQTT:** Signals that your packets are authorized for internet ingestion.
- **Ignore MQTT:** Prevents your radio node from retransmitting packets injected into the mesh via internet.

### Secure Access & Downlink Protection

- The community broker is strictly uplink-only. Gateways lack permission to read or subscribe to global radio streams. Even if downlink is mistakenly enabled locally, no traffic from the Internet will ever be broadcast onto the airwaves.

### Frequently Asked Questions

**My node does not show up.** Verify that your root topic exactly matches `{MQTT_TOPIC_ROOT}`, port is `8883` with TLS enabled, uplink is toggled on, and channel names match our whitelist. If problems persist, contact [{PROJECT_CONTACT}](mailto:{PROJECT_CONTACT}).

**Can I connect with the public credentials?** Yes, `{MQTT_GATEWAY_USER}` / `{MQTT_GATEWAY_PASSWORD}` are open to the entire community with strict uplink-only rights.

**What happens if my gateway loses power?** Nothing serious. If it stops publishing, the system opens an infrastructure alert that clears automatically upon reconnection.

**How do I disconnect?** Simply toggle off the MQTT module in your node application.

### Data Privacy

Ingested packets are rendered in real time on PotatoMesh and MeshView, feed regional statistics and alerts, and are purged automatically per our [privacy policy](/legal/privacidad).
