# 05 · Configure your node

> `/configura-tu-nodo` · Guide to configure any Meshtastic node in minutes, understand recommended community parameters, and protect the mesh radio spectrum.

## SEO

- **Título:** `Configure your node · {PROJECT_NAME}`
- **Descripción:** `Recommended community settings for your Meshtastic node in Andalusia: radio parameters, roles, hops, broadcast intervals, and privacy.`

## Borrador del texto

**H1:** Configure your node

With these settings, your node hears the community mesh without clogging the airwaves. If your device is new, it takes only minutes; if it is already running, review the parameters below and adjust any mismatches.

**Why it matters:** A radio node cannot transmit and receive simultaneously, and each packet consumes channel airtime for all nearby participants. Most mesh traffic consists of periodic background telemetry: node info, GPS position, and device metrics. The fewer packets you emit, the more capacity remains for vital messages.

### 1. Radio

The vast majority of the community in Spain uses this manual narrowband preset. If your node uses different parameters, it will not hear other nodes nor will they hear it. Narrowband provides significantly greater range and reduced collision rates compared to standard factory profiles.

| Parameter | Recommended Value |
|---|---|
| Region | `{LORA_REGION}` (European Union 868 MHz) |
| Use preset (Predefined) | Disabled (to unlock custom fields) |
| Bandwidth | `{LORA_BANDWIDTH}` (or {LORA_BANDWIDTH_KHZ} kHz) |
| Spreading factor | `{LORA_SPREAD_FACTOR}` |
| Coding rate | `{LORA_CODING_RATE}` (4/5) |
| Frequency slot | `{LORA_FREQUENCY_SLOT}` |
| Frequency override | `{LORA_FREQUENCY_MHZ}` MHz (or 869.6188 MHz) |
| Preset Name | `SFNarrow` |
| Primary Channel (0): Name | `{PRIMARY_CHANNEL}` |
| Primary Channel (0): PSK Key | `AQ==` (default public community key) |
| Hop Limit | 3 to 4 (5 for `CLIENT_MUTE` or edge regions) |

> ⚠️ **Crucial Hardware Warning:** Never power on your LoRa board without securely attaching an appropriate 868 MHz antenna first. Transmitting without an RF antenna load can permanently destroy the power amplifier (PA).

How to apply these settings in the Meshtastic mobile or web app:

1. In your node app, navigate to `Radio Configuration → LoRa` and select region `{LORA_REGION}`.
2. Toggle off **Use preset (Predefined)** to unlock custom manual parameters.
3. Set Bandwidth to `{LORA_BANDWIDTH}` (or 62.5 kHz), Spreading Factor to `{LORA_SPREAD_FACTOR}`, and Coding Rate to `{LORA_CODING_RATE}`.
4. Set **Frequency slot** to `{LORA_FREQUENCY_SLOT}` or enter `{LORA_FREQUENCY_MHZ}` MHz under **Frequency override**.
5. Under `Channels`, rename Channel 0 to `{PRIMARY_CHANNEL}` and verify the public PSK key is set to `AQ==`.
6. Set the Hop Limit to 3 or 4 (use 5 only if positioned at an isolated boundary or configured as `CLIENT_MUTE`).

If administering a node remotely over the air, change settings in this exact sequence to avoid losing connection: remote node LoRa → local node LoRa → remote node Channel → local node Channel.

### 2. Operational Role

| Role | When to use |
|---|---|
| `CLIENT_MUTE` | Default for most: personal nodes, indoor setups, or non-elevated locations. Receives and transmits local packets without repeating traffic from others |
| `CLIENT` | Outdoor stations with elevated, unobstructed line of sight (e.g. rooftops). Repeats packets |
| `ROUTER` | Strictly for permanent, strategic hilltop infrastructure, coordinated in advance with the regional community. Existing backbone sites are already established; unauthorized routers cause destructive collisions |
| `ROUTER_LATE`, `CLIENT_BASE` | Not recommended |

Not every node should act as a repeater: when too many nodes repeat, packets echo endlessly and saturate the band. When in doubt, select `CLIENT_MUTE`.

### 3. Hop Limit

- **Recommended: 3 to 4 hops.** For almost all locations in Andalusia, 3 to 4 hops provide excellent propagation without overloading the RF spectrum.
- **5 hops:** Reserved for indoor `CLIENT_MUTE` nodes or geographically isolated edge locations seeking distant mountaintop repeaters.
- **6 or more hops:** Strongly discouraged; generates packet storms and is automatically flagged by our anomaly detector (`hops-high`).

### 4. Broadcast Intervals

| Packet Type | Recommended Interval |
|---|---|
| NodeInfo | 72 hours |
| Position (fixed station) | 72 hours |
| Position (mobile node) | 1 hour minimum |
| Device telemetry (solar station) | 4 hours or more |
| Device telemetry (backbone router) | 6 hours or more |
| Device telemetry (grid-powered) | Disabled |
| Environment telemetry (sensors) | Disabled, or 4+ hours |
| Power/energy telemetry | Disabled |

Automated broadcasts constitute the lion's share of channel occupancy. If your node transmits too frequently, it will appear near the top of our [spectrum consumption rankings](/rankings).

### 5. Position & Privacy

- Disable all extra position flags to minimize packet byte size.
- Disable smart position.
- If you prefer not to broadcast exact coordinates, reduce channel position precision to 3–4 digits.
- Keep **OK to MQTT** enabled if you want your node to appear on maps and telemetry statistics. Gateways respect this flag and will not forward packets if disabled.
- **To opt out of public maps, simply disable OK to MQTT on your node.** Stored data is purged automatically under our [privacy policy](/legal/privacidad).

### 6. Practices that harm the mesh

- Configuring excessive hop limits (> 4).
- Setting the `ROUTER` role without prior community coordination.
- Broadcast intervals shorter than recommended guidelines.
- Leaving the range test module (*RangeTest*) running unattended.
- Enabling amateur radio mode (which strips encryption on public channels).

### Next step

Does your node have an internet connection? Connect it as an MQTT gateway to help monitor the regional mesh.

[Connect your gateway](/conecta-tu-gateway)
