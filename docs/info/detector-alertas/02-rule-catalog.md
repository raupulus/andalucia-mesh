# 07.2 · Catálogo de reglas

> Todas las reglas con su riesgo por tipo, base dinámica, resolución y campos del flujo `decoded` que usan; qué entra en el MVP y qué es ampliación; `reglas.yaml` de arranque completo.

## Objetivo

Cubrir los requisitos (nodos en bucle, baterías bajas, routers caídos, spam, errores de configuración) con umbrales que son **mínimos de seguridad**: la detección real compara con las líneas base de cada nodo y de la malla (`01-rule-engine.md`). Todo se ajusta en `reglas.yaml`.

## Especificación

Leyenda: **MVP** = esta entrega, activa; **Ampl.** = ampliación, se implementa tras calibrar el MVP (su bloque llega con `activa: false`). **Red** = `afecta_malla` (siempre `infraestructura`, sea quien sea el nodo). **Infra** = nodo de infraestructura (router o gateway); **Cliente** = el resto. Campos: `fn.` = `from_node.`, `dm.` = `payload.device_metrics.`, `ls.` = `payload.local_stats.`, `rx[]` = `receptions[]`.

### A. Salud de nodos y gateways

| Id | Fase | Detecta | Riesgo por tipo | Base dinámica | Resolución | Campos |
|---|---|---|---|---|---|---|
| `reboot-loop` | MVP | Bucle de reinicio. Reinicio = `uptime_seconds` menor que el anterior, o < 180 s sin otro reinicio contado en esos 180 s | Infra: `alto` ≥ 3 en 60 min · Cliente: `bajo` ≥ 3 en 60 min | — | 120 min sin reinicios | `dm.uptime_seconds`, `ls.uptime_seconds`, `rx_first` |
| `battery-low` | MVP | Batería baja en 2 muestras seguidas (`battery_level` 1–100; > 100 = alimentado; `0` con `voltage` 0 = sin sensor, se ignora) | Infra: `medio` < 40 %, `alto` < 20 % · Cliente: `bajo` < 20 % | — | > 50 % | `dm.battery_level`, `dm.voltage` |
| `infra-silent` | MVP | Router que deja de oírse | Infra: `medio` > max(6 h, 3 × intervalo típico) · `alto` > 24 h | Intervalo típico del nodo | Se vuelve a oír | `from`, `fn.role`, `rx_first` (tick) |
| `gateway-offline` | MVP | Gateway que deja de publicar | Infra: `medio` > max(15 min, 3 × intervalo típico) · `alto` > 120 min | Intervalo típico del gateway | Vuelve a publicar | `rx[].gateway`, `rx[].at`, `fn.is_gateway` (tick) |
| `battery-drain` | Ampl. | Router solar que no recarga | Infra: `medio` | Proyección lineal del voltaje de 72 h hasta 3,3 V en < 3 días | Pendiente de 24 h ≥ 0 | `dm.voltage` |
| `chutil-high` | Ampl. | Canal saturado según un router (2 muestras) | Infra: `medio` > 20 % · `alto` ≥ 40 % (cortes del mapa) | Saturación ponderada de la provincia en `datos` | ≤ 20 % | `dm.channel_utilization`, `fn.province` |
| `airtime-high` | Ampl. | Cerca del ciclo de trabajo del 10 % horario de EU_868 | Red: `medio` > 7 % · `alto` > 10 % (probable `override_duty_cycle`) | — | < 5 % | `dm.air_util_tx` |
| `tx-dropped` | Ampl. | Cola de transmisión desbordada (solo infraestructura) | Infra: `medio` > 20 descartes/h | — | 120 min sin incrementos | `ls.num_tx_dropped` |
| `noise-high` | Ampl. | Interferencia (solo infraestructura) | Infra: `medio` | ≥ 10 dB sobre su mediana de 7 días durante 60 min | < mediana + 5 dB | `ls.noise_floor` |

### B. Comportamiento que degrada la malla

| Id | Fase | Detecta | Riesgo | Base dinámica | Resolución | Campos |
|---|---|---|---|---|---|---|
| `flood` | MVP | Un nodo emite demasiados paquetes propios (spam o firmware desbocado), sin contar `routing` | Red: `medio` > max(30, 5 × ritmo) en 10 min · `alto` > max(100, 15 × ritmo) | Ritmo típico del nodo | 30 min bajo el umbral `medio` | `from`, `portnum`, `rx_first` |
| `rafaga-masiva` | MVP | Muchos nodos emiten a la vez (p. ej. 200 respondiendo telemetría a un sondeo) | Red: `alto`, `nodo: "all"` | Nodos por 2 min a esa hora; umbral max(50, 5 × base) | 15 min bajo el umbral | `from`, `portnum`, `rx_first` |
| `hops-high` | MVP | Límite de saltos excesivo en origen | Red: `medio` con `hop_start` ≥ 6 (3 recomendado; 4–5 válidos según la guía) | — | 2 paquetes seguidos con `hop_start` ≤ 5 | `hop_start` (se ignoran `null` y 0) |
| `text-flood` | Ampl. | Spam de texto de un nodo | Red: `medio` > 10 mensajes en 5 min | — | 30 min sin exceso | `portnum=text`, `from`, `channel` |
| `config-intervals` | Ampl. | Intervalos de emisión más cortos que las recomendaciones del portal (tabla abajo) | Red: `bajo`, una alerta por nodo con todos los intervalos cortos en `datos` | Nodo fijo o móvil según su desplazamiento en 24 h | 24 h dentro de los intervalos | `portnum`, `to`, `payload.*_metrics`, `payload.latitude_i/longitude_i` |
| `router-role` | Ampl. | Rol de router no coordinado (fuera de `routers_coordinados`) | Infra: `bajo` | — | Entra en la lista o cambia de rol | `fn.role` |
| `new-node-burst` | Ampl. | Aparición masiva de ids nuevos (no vistos en 90 días) | Red: `medio`, `nodo: "all"` | > max(20, 5 × nuevos habituales) en 10 min | 60 min bajo el umbral | `from` |

`config-intervals`, alineado con `../portal/03-node-setup-guide.md` (cuenta solo emisiones `to: "^all"`; mediana de las últimas 20; mínimo 3 intervalos):

| Paquete | Recomendación del portal | Alerta si la mediana es menor que |
|---|---|---|
| `nodeinfo` | 72 h | 36 h |
| `position`, nodo fijo (desplazamiento 24 h < 500 m) | 72 h | 36 h |
| `position`, nodo móvil | 1 h como mínimo | 30 min |
| `telemetry` con `device_metrics` | ≥ 4 h solar, ≥ 6 h troncal, desactivada enchufado | 2 h (el detector no distingue solar, troncal o enchufado: aplica la más permisiva) |
| `telemetry` con `environment_metrics` | Desactivada o > 4 h | 2 h |
| `telemetry` con `power_metrics` | Desactivada | Cualquier emisión: ≥ 3 en 24 h |

Umbral = recomendación × `tolerancia` (0,5). Un nodo que sigue la guía no dispara ninguna regla del bloque B.

### C. Suplantación e inyección

| Id | Fase | Detecta | Riesgo | Base / ventana | Resolución | Campos |
|---|---|---|---|---|---|---|
| `mqtt-into-rf` | Ampl. | Paquetes con `via_mqtt` oídos por radio (alguien reinyecta internet en la malla) | Red: `medio`, `nodo: "all"`, `nodos` = gateways que los oyen | ≥ 5 en 60 min | 6 h sin casos | `via_mqtt`, `rx[].gateway` |
| `ok-to-mqtt-violation` | Ampl. | Gateway que sube paquetes ajenos sin el bit OK to MQTT | Infra (el gateway): `medio` | ≥ 3 en 24 h | 24 h sin casos | `ok_to_mqtt` (`false`; `null` se ignora), `from`, `rx[].gateway` |
| `impossible-jump` | Ampl. | Velocidad implícita > 300 km/h entre posiciones separadas > 1 km | Cliente: `bajo` · Infra: `medio` | — | 24 h sin saltos | `payload.latitude_i/longitude_i`, `rx_first` |
| `duplicate-pubkey` | Ampl. | Misma clave pública en ids distintos | Red: `alto`; `nodo` = id menor, el resto en `datos.ids` | 7 días | 7 días sin coincidencia | `portnum=nodeinfo`, `payload.public_key` |
| `identity-conflict` | Ampl. | Mismo id con otra clave pública | Red: `medio` | < 24 h | 24 h sin cambios | `payload.public_key` |

`topic-mismatch` no existe: la ACL del broker solo deja publicar a cada gateway en su propio topic (`%u`) y `decoded` no lleva el `gateway_id` del sobre.

### Datos y mensaje de las reglas MVP

`{corto}` = `short` del nodo o, si falta, su id. Toda resolución añade `datos.cierre`.

| Regla | `datos` | `mensaje` |
|---|---|---|
| `reboot-loop` | `reinicios`, `ventana_min`, `uptime_minimo_s`, `umbral` | `{corto} se ha reiniciado {reinicios} veces en la última hora` |
| `battery-low` | `bateria`, `voltaje`, `umbral`, `muestras` | `{corto} tiene la batería al {bateria} %` |
| `infra-silent` | `ultimo_visto`, `silencio_h`, `intervalo_tipico_s`, `umbral_h` | `{corto} no se oye desde hace {silencio_h} h` |
| `gateway-offline` | `ultimo_mensaje`, `silencio_min`, `intervalo_tipico_s`, `umbral_min` | `El gateway {corto} no publica desde hace {silencio_min} min` |
| `flood` | `paquetes`, `ventana_min`, `ritmo_tipico`, `umbral`, `por_tipo` | `{corto} ha emitido {paquetes} paquetes en {ventana_min} min` |
| `rafaga-masiva` | `nodos_total`, `ventana_s`, `base`, `umbral`, `por_tipo` | `{nodos_total} nodos han emitido a la vez en {ventana_s} s` |
| `hops-high` | `hop_start`, `recomendado`, `maximo_valido` | `{corto} usa {hop_start} saltos (recomendado 3, máximo 5)` |

### `reglas.yaml` de arranque

```yaml
general:
  seguimiento_dias: 7          # routers y gateways vigilados si se oyeron en este plazo
  historia_minima_h: 24        # sin esta historia, solo mínimos fijos
  reapertura_min: 60
  actualizacion_min: 15
  nodos_max_lista: 500
# --- MVP ---
reboot-loop:     {activa: true, ventana_min: 60, uptime_arranque_s: 180, resolver_min: 120,
                  umbral: {infraestructura: {alto: 3}, clientes: {bajo: 3}}}
battery-low:     {activa: true, muestras_minimas: 2, resolver: 50,
                  umbral: {infraestructura: {medio: 40, alto: 20}, clientes: {bajo: 20}}}
infra-silent:    {activa: true, medio: {minimo_h: 6, factor_intervalo: 3}, alto_h: 24}
gateway-offline: {activa: true, medio: {minimo_min: 15, factor_intervalo: 3}, alto_min: 120}
flood:           {activa: true, ventana_min: 10, excluir: [routing], resolver_min: 30,
                  medio: {minimo: 30, factor_ritmo: 5}, alto: {minimo: 100, factor_ritmo: 15}}
rafaga-masiva:   {activa: true, ventana_s: 120, nodos_minimos: 50, factor_linea_base: 5, resolver_min: 15}
hops-high:       {activa: true, medio_desde: 6, resolver_paquetes: 2}
# --- Ampliación (activa: false hasta implementarlas y calibrarlas) ---
battery-drain:        {activa: false, voltaje_vacio: 3.3, dias_proyeccion: 3, ventana_h: 72}
chutil-high:          {activa: false, medio: 20, alto: 40, resolver: 20, muestras_minimas: 2}
airtime-high:         {activa: false, medio: 7, alto: 10, resolver: 5}
tx-dropped:           {activa: false, medio_por_hora: 20, resolver_min: 120}
noise-high:           {activa: false, db_sobre_mediana: 10, duracion_min: 60, resolver_db: 5}
text-flood:           {activa: false, mensajes: 10, ventana_min: 5, resolver_min: 30}
config-intervals:     {activa: false, tolerancia: 0.5, muestras_minimas: 3, resolver_h: 24, movil_desde_m: 500,
                       recomendado_h: {nodeinfo: 72, posicion_fijo: 72, posicion_movil: 1, telemetria_dispositivo: 4, telemetria_entorno: 4},
                       telemetria_energia: {maximo_24h: 2}}
router-role:          {activa: false, routers_coordinados: []}
new-node-burst:       {activa: false, minimo: 20, factor_linea_base: 5, ventana_min: 10, resolver_min: 60}
mqtt-into-rf:         {activa: false, paquetes_minimos: 5, ventana_min: 60, resolver_h: 6}
ok-to-mqtt-violation: {activa: false, paquetes_minimos: 3, ventana_h: 24, resolver_h: 24}
impossible-jump:      {activa: false, velocidad_kmh: 300, distancia_minima_km: 1, resolver_h: 24}
duplicate-pubkey:     {activa: false, ventana_dias: 7}
identity-conflict:    {activa: false, ventana_h: 24}
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
- **UT-07.2.5 — Ampliación: salud** (`battery-drain`, `chutil-high`, `airtime-high`, `tx-dropped`, `noise-high`).
- **UT-07.2.6 — Ampliación: comportamiento** (`text-flood`, `config-intervals`, `router-role`, `new-node-burst`).
- **UT-07.2.7 — Ampliación: suplantación** (`mqtt-into-rf`, `ok-to-mqtt-violation`, `impossible-jump`, `duplicate-pubkey`, `identity-conflict`). Para 07.2.5–07.2.7: *aceptación* = pruebas de apertura y resolución por regla y calibración sobre 7 días antes de pasar a `activa: true`.

## Escenarios de prueba

1. Dado el router `CAD1` / Cuando su `uptime_seconds` pasa de 3.600 a 84, de 170 a 90 y de 160 a 75 en 40 min / Entonces `reboot-loop` `alto · infraestructura` con `datos.reinicios = 3`; 120 min sin reinicios la resuelven.
2. Dado un nodo `CLIENT` / Cuando informa `battery_level` 18 y 17 / Entonces `battery-low` `bajo · clientes`; con 101 (alimentado) no se abre nada.
3. Dado un gateway que publica cada ~2 min / Cuando deja de publicar / Entonces `gateway-offline` `medio` a los 15 min y `actualizada` a `alto` a los 120 min; resuelta con la siguiente recepción.
4. Dado un nodo con ritmo típico de 2 paquetes por 10 min / Cuando emite 40 en 10 min / Entonces `flood` `medio · infraestructura` aunque sea `CLIENT_MUTE`.
5. Dado un nodo con `hop_start` 7 / Cuando emite dos paquetes con `hop_start` 3 / Entonces `hops-high` se resuelve; con `hop_start` 5 nunca se abre.

---
> Creado: 2026-10-07 · Última revisión: 2026-10-07
