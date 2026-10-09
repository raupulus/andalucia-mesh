# 07.2 · Catálogo de reglas

> Todas las reglas con su riesgo por tipo, base dinámica, resolución y campos del flujo `decoded` que usan; qué entra en el MVP y qué es ampliación; `reglas.yaml` de arranque completo.

## Objetivo

Cubrir los requisitos (nodos en bucle, baterías bajas, routers caídos, spam, errores de configuración) con umbrales que son **mínimos de seguridad**: la detección real compara con las líneas base de cada nodo y de la malla (`01-rule-engine.md`). Todo se ajusta en `reglas.yaml`.

## Especificación

Leyenda: **MVP** = esta entrega, activa; **Ampl.** = ampliación, se implementa tras calibrar el MVP (su bloque llega con `activa: false`). **Red** = `afecta_malla` (siempre `infraestructura`, sea quien sea el nodo). **Infra** = nodo de infraestructura (router o gateway); **Cliente** = el resto. Campos: `fn.` = `from_node.`, `dm.` = `payload.device_metrics.`, `ls.` = `payload.local_stats.`, `rx[]` = `receptions[]`.

### A. Salud de nodos y gateways

| Id | Fase | Detecta | Riesgo por tipo | Base dinámica | Resolución | Campos |
|---|---|---|---|---|---|---|
| `reboot-loop` | MVP | Bucle de reinicio anómalo (uptime menor que el anterior o < 100 s tras reinicio) | `medio` ≥ 3 en 5 min · `alto` ≥ 5 en 10 min | — | 30 min sin reinicios | `dm.uptime_seconds`, `ls.uptime_seconds`, `rx_first` |
| `battery-low` | MVP | Batería baja confirmada en 2 muestras seguidas (batería 1–100 %; > 100 = alimentado; otros roles se ignoran) | Routers: `medio` < 60 %, `alto` < 40 % · Clientes: `bajo` < 35 %, `medio` < 15 % | — | > 70 % en routers, > 45 % en clientes | `dm.battery_level`, `dm.voltage`, `fn.role` |
| `sunset-battery` | MVP | Router solar que llega a las 20:00 h peninsulares con carga insuficiente (excluye nodos fuera de Andalucía `FUERA`) | Infra: `medio` < 60 %, `alto` < 40 % | — | > 70 % o luz solar | `dm.battery_level`, `fn.role`, `fn.province` (tick 20:00) |
| `chutil-high` | MVP | Canal saturado calculando saturación provincial (60 % peso routers, 40 % peso clientes) | Red: `bajo` > 20 %, `medio` > 30 %, `alto` > 40 % | — | ≤ 20 % | `dm.channel_utilization`, `fn.role`, `fn.province` |
| `infra-silent` | MVP | Router que deja de oírse | Infra: `medio` > max(6 h, 3 × intervalo típico) · `alto` > 24 h | Intervalo típico del nodo | Se vuelve a oír | `from`, `fn.role`, `rx_first` (tick) |
| `gateway-offline` | MVP | Gateway que deja de publicar | Infra: `medio` > max(15 min, 3 × intervalo típico) · `alto` > 120 min | Intervalo típico del gateway | Vuelve a publicar | `rx[].gateway`, `rx[].at`, `fn.is_gateway` (tick) |
| `gateway-no-traffic` | Ampl. | Pasarela con transceptor LoRa sordo/bloqueado (> 1 h sin recepciones LoRa pese a proceso activo) | Infra: `bajo` > 60 min sin paquetes | — | Vuelve a recibir paquetes LoRa | `rx[].gateway`, `rx[].at`, `fn.is_gateway` (tick) |
| `battery-drain` | Ampl. | Router solar que no recarga | Infra: `medio` | Proyección lineal del voltaje de 72 h hasta 3,3 V en < 3 días | Pendiente de 24 h ≥ 0 | `dm.voltage` |
| `airtime-high` | Ampl. | Tiempo de aire propio por encima de límites operativos | Red: `bajo` > 4 % · `medio` > 6 % · `alto` > 8 % | — | ≤ 3 % durante 30 min | `dm.air_util_tx`, `ls.air_util_tx` |
| `tx-dropped` | Ampl. | Cola de transmisión desbordada (solo infraestructura) | Infra: `medio` > 20 descartes/h | — | 120 min sin incrementos | `ls.num_tx_dropped` |
| `noise-high` | Ampl. | Interferencia (solo infraestructura) | Infra: `medio` | ≥ 10 dB sobre su mediana de 7 días durante 60 min | < mediana + 5 dB | `ls.noise_floor` |

### B. Comportamiento que degrada la malla

| Id | Fase | Detecta | Riesgo | Base dinámica | Resolución | Campos |
|---|---|---|---|---|---|---|
| `text-flood` | MVP | Inundación de mensajes de texto en canales públicos | Red/Cliente: `bajo` > 5/min · `medio` 6–10/min · `alto` > 10/min | — | 15 min sin exceso | `portnum=text_message_app`, `from`, `to` |
| `telemetry-burst` | MVP | Ráfaga excesiva de telemetría (distingue 5 variantes + nodeinfo + posición; `ráfaga_combinada` vs `constante_acelerada`) | Red/Cliente: `bajo` ≥ 2 en 1 min · `alto` > 50 en 1 hora | — | 30 min sin exceso | `portnum=telemetry`, `nodeinfo`, `position` |
| `poll-abuse` | MVP | Peticiones de sondeo masivas a toda la malla (`^all`) solicitando batería o nodeinfo | Red/Cliente: `bajo` 1 sondeo · `medio` ≥ 3 en 15 min · `alto` ≥ 5 en 15 min | — | 30 min sin sondeos | `portnum=nodeinfo_app`, `telemetry_app`, `to="^all"` |
| `traceroute-flood` | MVP | Abuso y saturación por traceroutes reiterados | Red/Cliente: `medio` 10–19 en 30 min · `alto` ≥ 20 en 30 min | — | 45 min sin exceso | `portnum=traceroute_app`, `from` |
| `private-chaff` | MVP | Tráfico cifrado privado o de sensores sobre la malla pública obligando a los repetidores a reenviarlo | Red/Cliente: `medio` > 10 en 10 min o > 30/h · `alto` > 60/h | — | 30 min sin exceso | `portnum=other`, `from` |
| `position-flood` | MVP | Posiciones GPS aceleradas emitidas de forma continuada | Red/Cliente: `bajo` ≥ 4 en 5 min · `medio` ≥ 8 en 10 min · `alto` ≥ 20 en 15 min | — | 30 min dentro de norma | `portnum=position_app`, `from` |
| `router-role` | MVP | Rol ROUTER/REPEATER no coordinado en Andalucía (excluye nodos fuera de Andalucía `FUERA`) | Infra: `medio` si no figura en `routers_coordinados` | — | Entra en coordinación o cambia a CLIENT | `fn.role`, `fn.province` |
| `router-moving` | Ampl. | Repetidor de infraestructura con desplazamiento anómalo (> 5 km en 24 h) | Infra: `alto` > 5 km | — | Desplazamiento 24 h ≤ 3 km | `payload.latitude_i`, `payload.longitude_i`, `fn.role` |
| `router-cluster` | Ampl. | Router enlazado directamente con 3 o más routers vecinos (excluye `ROUTER_LATE`) | Infra: `medio` ≥ 3 routers | — | < 3 routers vecinos directos | `payload.neighbors`, `fn.role` |
| `asymmetric-link` | Ampl. | Enlace RF con asimetría severa (> 6 dB en ambos sentidos; solo CLIENT, CLIENT_BASE y ROUTER; excluye CLIENT_MUTE) | Red/Infra: `medio` Δ > 6 dB | — | Diferencia SNR ≤ 4 dB | `payload.neighbors`, `rx[].snr`, `fn.role` |
| `key-security` | Ampl. | Clave pública débil (baja entropía, secuencias, patrones periódicos) o cambio imprevisto de clave en el mismo ID | Routers: `alto` · Clientes: `medio` | — | Clave con entropía estructural válida | `payload.public_key`, `fn.role` |
| `hops-high` | MVP | Límite de saltos excesivo en origen | Red/nodo: `bajo` con `hop_start` = 6 · `alto` con `hop_start` ≥ 7 (3 recomendado; 4–5 válidos) | — | 2 paquetes seguidos con `hop_start` ≤ 5 | `hop_start` |
| `flood` | MVP | Un nodo emite demasiados paquetes propios (spam o firmware desbocado) | Red: `medio` > max(30, 5 × ritmo) en 10 min · `alto` > max(100, 15 × ritmo) | Ritmo típico del nodo | 30 min bajo el umbral `medio` | `from`, `portnum`, `rx_first` |
| `rafaga-masiva` | MVP | Muchos nodos emiten a la vez en la malla | Red: `alto`, `nodo: "all"` | Nodos por 2 min a esa hora; umbral max(50, 5 × base) | 15 min bajo el umbral | `from`, `portnum`, `rx_first` |
| `config-intervals` | Ampl. | Intervalos de emisión más cortos que las recomendaciones del portal | Red: `bajo` | Nodo fijo o móvil según desplazamiento en 24 h | 24 h dentro de los intervalos | `portnum`, `to`, `payload.*_metrics` |
| `new-node-burst` | Ampl. | Aparición masiva de ids nuevos (no vistos en 90 días) | Red: `medio`, `nodo: "all"` | > max(20, 5 × nuevos habituales) en 10 min | 60 min bajo el umbral | `from` |

### C. Ámbito geográfico y provincial

Todas las alertas emitidas por el motor incorporan obligatoriamente en su carga JSON los metadatos geográficos:
- `provincia`: Código ISO (`ES-AL`, `ES-CA`, `ES-CO`, `ES-GR`, `ES-H`, `ES-J`, `ES-MA`, `ES-SE`, o `FUERA` si no pertenece a la comunidad andaluza).
- `dentro_andalucia`: Booleano estricto (`true` solo si la provincia es una de las 8 de Andalucía).
- Las reglas de gobernanza comunitaria (`router-role`) y operativas solares (`sunset-battery`) descartan de forma tajante a los nodos de fuera de Andalucía (`FUERA`), preservando la autonomía y competencia de las comunidades colindantes (Murcia, Extremadura, Castilla-La Mancha, Portugal).

### D. Datos y mensaje de las reglas principales

`{corto}` = `short` del nodo o, si falta, su id. Toda resolución añade `datos.cierre`.

| Regla | `datos` | `mensaje` |
|---|---|---|
| `reboot-loop` | `reinicios_5m`, `reinicios_10m`, `ventana_min`, `umbral` | `{corto} sufre un bucle de reinicios: {reinicios_5m} en 5m` |
| `battery-low` | `bateria`, `voltaje`, `rol`, `umbral`, `muestras` | `{corto} ({rol}) tiene la batería al {bateria} %` |
| `chutil-high` | `chutil_provincial`, `provincia`, `chutil_nodo`, `peso_routers`, `peso_clientes` | `Canal saturado en {provincia}: {chutil_provincial}% de ocupación` |
| `text-flood` | `mensajes_1m`, `ventana_s`, `umbral` | `{corto} emite un exceso de texto: {mensajes_1m} mensajes en 1 minuto` |
| `telemetry-burst` | `tipo_anomalia`, `emisiones_1m`, `emisiones_1h`, `distribucion` | `{corto} emite ráfaga excesiva de telemetría ({tipo_anomalia})` |
| `poll-abuse` | `sondeos_15m`, `tipo_sondeo`, `destino` | `{corto} abusa de sondeos masivos a la malla (^all)` |
| `sunset-battery` | `bateria_actual`, `hora_evaluacion`, `provincia`, `umbral` | `{corto} llega al anochecer con solo el {bateria_actual} % de batería` |
| `traceroute-flood` | `traceroutes_30m`, `ventana_min`, `umbral` | `{corto} satura la malla con {traceroutes_30m} traceroutes en 30 min` |
| `private-chaff` | `paquetes_privados_10m`, `paquetes_privados_1h` | `{corto} emite tráfico privado o sensores en el canal público` |
| `position-flood` | `posiciones_5m`, `posiciones_10m`, `posiciones_15m` | `{corto} transmite posiciones GPS repetitivas cada pocos minutos` |
| `router-role` | `rol`, `provincia`, `aprobado` | `{corto} emite como {rol} en {provincia} sin coordinar con la comunidad` |
| `infra-silent` | `ultimo_visto`, `silencio_h`, `intervalo_tipico_s`, `umbral_h` | `{corto} no se oye desde hace {silencio_h} h` |
| `gateway-offline` | `ultimo_mensaje`, `silencio_min`, `intervalo_tipico_s`, `umbral_min` | `El gateway {corto} no publica desde hace {silencio_min} min` |
| `hops-high` | `hop_start`, `recomendado`, `maximo_valido` | `{corto} usa {hop_start} saltos (recomendado 3, máximo 5)` |
| `flood` | `paquetes`, `ventana_min`, `ritmo_tipico`, `umbral`, `por_tipo` | `{corto} ha emitido {paquetes} paquetes en {ventana_min} min` |
| `rafaga-masiva` | `nodos_total`, `ventana_s`, `base`, `umbral`, `por_tipo` | `{nodos_total} nodos han emitido a la vez en {ventana_s} s` |
| `gateway-no-traffic` | `ultimo_paquete`, `silencio_min`, `umbral_min` | `El gateway {corto} no recibe tráfico LoRa desde hace {silencio_min} min (posible radio bloqueada)` |
| `airtime-high` | `air_util_tx`, `umbral` | `{corto} supera el límite de ocupación de transmisión: {air_util_tx}% de tiempo de aire` |
| `router-moving` | `desplazamiento_km`, `umbral_km`, `muestras`, `rol` | `Router {corto} en movimiento físico: se ha desplazado {desplazamiento_km} km en 24h (umbral 5 km)` |
| `router-cluster` | `routers_enlazados`, `vecinos`, `umbral` | `Router {corto} enlazado con {routers_enlazados} routers más: posible clúster redundante` |
| `asymmetric-link` | `delta_db`, `snr_peor`, `snr_mejor`, `otro_nodo` | `Enlace asimétrico en {corto} (Δ > 6 dB): oye a {otro_nodo} a {snr_peor} dB pero él le oye a {snr_mejor} dB` |
| `key-security` | `motivo`, `rol`, `clave_fingerprint`, `tipo_anomalia` | `{corto} ({rol}) presenta anomalía de clave pública: {motivo}` |

### `reglas.yaml` de arranque

```yaml
general:
  seguimiento_dias: 7          # routers y gateways vigilados si se oyeron en este plazo
  historia_minima_h: 24        # sin esta historia, solo mínimos fijos
  reapertura_min: 60
  actualizacion_min: 15
  nodos_max_lista: 500

# --- Reglas activas ---
reboot-loop:
  activa: true
  ventana_5m: 300
  ventana_10m: 600
  umbral_medio: 3
  umbral_alto: 5
  resolver_min: 30

battery-low:
  activa: true
  muestras_minimas: 2
  router: {medio: 60, alto: 40, resolver: 70}
  cliente: {bajo: 35, medio: 15, resolver: 45}

chutil-high:
  activa: true
  peso_routers: 0.60
  peso_clientes: 0.40
  bajo: 20.0
  medio: 30.0
  alto: 40.0
  resolver: 20.0

text-flood:
  activa: true
  ventana_s: 60
  bajo: 5
  medio_max: 10
  resolver_min: 15

telemetry-burst:
  activa: true
  umbral_1m: 2
  umbral_1h: 50
  resolver_min: 30

poll-abuse:
  activa: true
  bajo: 1
  medio: 3
  alto: 5
  ventana_s: 900
  resolver_min: 30

sunset-battery:
  activa: true
  hora_evaluacion: 20
  medio: 60
  alto: 40
  resolver: 70

traceroute-flood:
  activa: true
  ventana_s: 1800
  medio: 10
  alto: 20
  resolver_min: 45

private-chaff:
  activa: true
  medio_10m: 10
  medio_1h: 30
  alto_1h: 60
  resolver_min: 30

position-flood:
  activa: true
  bajo_5m: 4
  medio_10m: 8
  alto_15m: 20
  resolver_min: 30

router-role:
  activa: true
  routers_coordinados: []

hops-high:
  activa: true
  bajo_desde: 6
  alto_desde: 7
  resolver_paquetes: 2

infra-silent:
  activa: true
  medio: {minimo_h: 6, factor_intervalo: 3}
  alto_h: 24

gateway-offline:
  activa: true
  medio: {minimo_min: 15, factor_intervalo: 3}
  alto_min: 120

flood:
  activa: true
  ventana_min: 10
  excluir: [routing]
  resolver_min: 30
  medio: {minimo: 30, factor_ritmo: 5}
  alto: {minimo: 100, factor_ritmo: 15}

rafaga-masiva:
  activa: true
  ventana_s: 120
  nodos_minimos: 50
  factor_linea_base: 5
  resolver_min: 15

gateway-no-traffic:
  activa: true
  ventana_s: 3600
  umbral_min: 60.0

airtime-high:
  activa: true
  bajo: 4.0
  medio: 6.0
  alto: 8.0
  resolver: 3.0

router-moving:
  activa: true
  umbral_km: 5.0
  ventana_h: 24
  resolver_km: 3.0

router-cluster:
  activa: true
  umbral_routers: 3
  ventana_h: 24
  resolver_routers: 2

asymmetric-link:
  activa: true
  delta_snr_db: 6.0
  resolver_db: 4.0

key-security:
  activa: true

silencios: []
```

### Ejemplo de transición (`rafaga-masiva`, lista recortada)

```json
{"v": 1, "transicion_id": "01JAC0Q4M2V9W8X7Y6Z5A4B3C2", "transicion": "abierta",
 "alerta": {"id": "01JAC0Q4M1K2J3H4G5F6E7D8C9", "regla": "rafaga-masiva", "riesgo": "alto", "tipo": "infraestructura",
  "mensaje": "212 nodos han emitido a la vez en 120 s", "nodo": "all", "nodos": ["!0a1b2c3d", "!0a1b2c3e", "!0a1b2c40"],
  "nodo_info": null,
  "datos": {"nodos_total": 212, "ventana_s": 120, "base": 9.5, "umbral": 50, "por_tipo": {"telemetry": 198, "nodeinfo": 14}},
  "estado": "abierta", "abierta_en": "2026-10-01T18:02:00Z", "actualizada_en": "2026-10-01T18:02:00Z", "resuelta_en": null}}
```

## Contratos propios

- `reglas.yaml` con el esquema de arriba; cada bloque se valida con el `Config` de su regla. Las claves de `umbral` son ids de `clasificacion.yaml` (`tipos` → `riesgos` → valor).
- `mensaje` y claves de `datos` de cada regla MVP (tabla anterior): los bots y el portal pueden mostrarlas; cambiarlas es cambio de contrato con `bots-webhooks`.

## Unidades de trabajo

- **UT-07.2.1 — `reboot-loop` y `battery-low`.** *Bordes:* uptime que vuelve a 0 por desbordamiento, telemetría de `local_stats` y `device_metrics` del mismo reinicio (cuenta uno), `battery_level` 101. *Aceptación:* escenarios 1 y 2.
- **UT-07.2.2 — `infra-silent` y `gateway-offline`.** Evaluadas en el tick. *Bordes:* nodo que deja de ser router (se resuelve con `fuera_de_seguimiento` si no vuelve), gateway que solo oye su propio nodo. *Aceptación:* escenario 3.
- **UT-07.2.3 — `flood`, `rafaga-masiva` y `hops-high`.** *Bordes:* nodo nuevo sin base (solo mínimos), `hop_start` `null`. *Aceptación:* escenarios 4 y 5.
- **UT-07.2.4 — Mensajes y `datos` del MVP.** Plantillas de la tabla con números enteros y `{corto}` de respaldo. *Aceptación:* prueba de instantánea (golden) por regla.
- **UT-07.2.5 — Ampliación: salud** (`battery-drain`, `chutil-high`, `airtime-high`, `gateway-no-traffic`, `tx-dropped`, `noise-high`).
- **UT-07.2.6 — Ampliación: comportamiento y topología** (`text-flood`, `config-intervals`, `router-role`, `router-moving`, `router-cluster`, `asymmetric-link`, `new-node-burst`).
- **UT-07.2.7 — Ampliación: seguridad de claves y suplantación** (`key-security`, `mqtt-into-rf`, `ok-to-mqtt-violation`, `impossible-jump`, `duplicate-pubkey`, `identity-conflict`). Para 07.2.5–07.2.7: *aceptación* = pruebas de apertura y resolución por regla y calibración sobre 7 días antes de pasar a `activa: true`.

## Escenarios de prueba

1. Dado el router `CAD1` / Cuando su `uptime_seconds` pasa de 3.600 a 84, de 170 a 90 y de 160 a 75 en 40 min / Entonces `reboot-loop` `alto · infraestructura` con `datos.reinicios = 3`; 120 min sin reinicios la resuelven.
2. Dado un nodo `CLIENT` / Cuando informa `battery_level` 18 y 17 / Entonces `battery-low` `bajo · clientes`; con 101 (alimentado) no se abre nada.
3. Dado un gateway que publica cada ~2 min / Cuando deja de publicar / Entonces `gateway-offline` `medio` a los 15 min y `actualizada` a `alto` a los 120 min; resuelta con la siguiente recepción.
4. Dado un nodo con ritmo típico de 2 paquetes por 10 min / Cuando emite 40 en 10 min / Entonces `flood` `medio · infraestructura` aunque sea `CLIENT_MUTE`.
5. Dado un nodo con `hop_start` 7 / Cuando emite dos paquetes con `hop_start` 3 / Entonces `hops-high` se resuelve; con `hop_start` 5 nunca se abre.

---
> Creado: 2026-10-07 · Última revisión: 2026-10-09
