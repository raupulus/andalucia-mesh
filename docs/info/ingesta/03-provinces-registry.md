# 05.3 · Provincias y registros

## Objetivo

Asignar a cada nodo su provincia andaluza (o `FUERA`) a partir de su última posición válida, y mantener los registros de **nodos** y **gateways**: el estado vivo que necesitan el flujo `decoded` (`from_node`), los enlaces directos, los reinicios y las vistas del portal.

## Especificación

### Polígonos

- **Fuente:** límites provinciales oficiales del CNIG (Centro de Descargas del IGN; producto de líneas límite / recintos provinciales en ETRS89; fijar producto y fecha de descarga al crear). Licencia CC BY 4.0: el portal acredita "© Instituto Geográfico Nacional" en sus créditos (`../portal/10-legal-privacy.md`).
- **Preparación** (una vez; pasos en `services/ingesta/polygons/README.md`): filtrar los códigos INE de las 8 provincias; reproyectar a WGS 84 (EPSG:4326); disolver por provincia; simplificar conservando topología (tolerancia ≈ 50 m, por ejemplo mapshaper `-simplify` con `keep-shapes`); coordenadas con 5 decimales; tamaño < 1 MB.
- **Formato:** GeoJSON `FeatureCollection` con 8 `MultiPolygon` y propiedades `code`, `name`, `ine`. Archivo `provincias-andalucia.geojson` en el volumen `./poligonos` (solo lectura).

| INE | `code` (ISO 3166-2) | `name` |
|---|---|---|
| 04 | `ES-AL` | Almería |
| 11 | `ES-CA` | Cádiz |
| 14 | `ES-CO` | Córdoba |
| 18 | `ES-GR` | Granada |
| 21 | `ES-H` | Huelva |
| 23 | `ES-J` | Jaén |
| 29 | `ES-MA` | Málaga |
| 41 | `ES-SE` | Sevilla |

- **Carga:** al arrancar se proyectan a metros con una equirectangular local (φ₀ = 37,4°; x = R·cos φ₀·λ, y = R·φ, R = 6.371.000 m; error < 2 % en Andalucía) y se preparan con `shapely.prepared`. Archivo ausente, ilegible o con ≠ 8 provincias → el servicio no arranca. Sin `pyproj`.

### Precisión

- Bits: `precision_bits` (posición) o `position_precision` (map report).
- Radio de error: `precision_m = 23.300 / 2^(bits − 10)` para 10–31 bits (11 → 11,7 km; 13 → 2,9 km; 14 → 1,5 km; 15 → 729 m; 16 → 364 m; 19 → 45 m). 32, 0 o ausente → **10 m** (precisión completa; firmware antiguo no rellena el campo).
- **Posición válida:** lat/lon ≠ (0, 0), dentro de rango (±90, ±180) y `precision_m` ≤ `INGESTA_PRECISION_MAX_M` (12 km, es decir 11 bits o más). Las inválidas no se guardan (`posicion_invalida`).
- Map report con `has_opted_report_location = false` (lat/lon a 0) → sin posición.

### Asignación de provincia

1. Punto dentro de un polígono → esa provincia.
2. Fuera de todos → la provincia más cercana si su distancia ≤ max(`precision_m`, `INGESTA_MARGEN_MIN_M` = 500 m). Cubre posiciones costeras truncadas que caen en el mar y puertos recortados por la simplificación. Si no → `FUERA` (Portugal, resto de España, Ceuta, Melilla, Gibraltar, mar abierto).
3. `border_uncertain = true` si otra provincia andaluza está a menos de `precision_m` del punto (el círculo de error corta un límite provincial).

- Cuenta la última posición válida por hora de recepción, venga de un paquete de posición o de un map report.
- El nodo conserva su provincia aunque la posición envejezca; las vistas filtran por `last_position_at` (ventana del mapa).
- Cambio de provincia → log `INFO` y se aplica desde el siguiente paquete (`packet.province`).
- Coste: 8 comprobaciones punto-polígono y, solo cerca de un borde, 8 distancias; despreciable al ritmo de referencia.

### Registro de nodos

En memoria (unas 2.000 entradas), cargado de la tabla `node` al arrancar; las filas modificadas se escriben cada 5 s (`INSERT … ON CONFLICT (id) DO UPDATE`).

| Campos | Se actualizan con | Notas |
|---|---|---|
| `id`, `node_num` | primer paquete originado o primera vez como gateway | `id = !%08x` |
| `first_seen`, `last_seen` | cualquier paquete originado (incluido map report) | `first_seen` también al verse como gateway |
| `short_name`, `long_name`, `hw_model`, `role`, `nodeinfo_at` | nodeinfo o map report, el más reciente | `role` ausente en nodeinfo = `CLIENT` |
| `public_key_fp` | nodeinfo | Huella, nunca la clave |
| `firmware` | map report | Solo nodos con map report |
| `latitude`, `longitude`, `position_precision_m`, `position_source`, `last_position_at`, `province`, `border_uncertain` | posición válida o map report | Ver asignación |
| `hop_start_last`, `hops_min_last` | paquete originado con `hop_start` | Para "Revisa tu nodo" |
| `is_gateway`, `gateway_first_at` | primera vez que su id aparece como gateway válido | No caduca |
| `battery_level`, `voltage`, `battery_at` | `device_metrics` con batería | |
| `channel_utilization`, `air_util_tx`, `metrics_at` | `device_metrics` o `local_stats`, el más reciente | Carga del mapa y `/routers` |
| `uptime_seconds`, `uptime_at`, `last_reboot_at` | `device_metrics` | Detección de reinicios (02) |

- **`from_node`** del flujo `decoded` = `short_name` → `short`, `long_name` → `long`, `role`, `hw_model` → `hw`, `is_gateway`, `province`, **después** de aplicar el propio paquete (un nodeinfo publica ya sus nombres nuevos). Desconocidos a `null`; `is_gateway` siempre booleano.
- "Router" = `role` en `INFRA_ROLES`; "nodo de infraestructura" = router o `is_gateway`. `CLIENT_BASE` no es infraestructura. Se evalúa en las vistas, no se guarda.
- Los nodos sin actividad en 365 días se borran (tarea diaria de [04](04-storage-retention.md)); si vuelven, empiezan de cero.

### Registro de gateways

| Campo | Regla |
|---|---|
| `id` | Id del gateway (el nodo correspondiente tiene `is_gateway = true`) |
| `first_message_at`, `last_message_at` | Cualquier mensaje que pasa la validación de sobre y gateway ([01](01-input-decryption.md)), incluidos map reports, duplicados y descartados por canal |
| `messages_total` | Contador acumulado |
| `typical_interval_s` | Mediana de los últimos 100 intervalos entre mensajes del gateway (en memoria); null con menos de 10 |

- Sin LWT ni topic de estado: la caída se deduce del silencio (`now − last_message_at` frente a `typical_interval_s`). Lo evalúa el detector con `decoded` y lo muestra el panel con `api_gateways`.
- `packets_last_hour` y `unique_nodes_24h` no se guardan: los calcula la vista `api_gateways` ([05](05-contract-views.md)).
- Escritura cada 5 s de las filas modificadas; borrado tras 365 días sin mensajes. Tras un reinicio, la mediana se reconstruye con los mensajes nuevos.

## Contratos propios

- Archivo `provincias-andalucia.geojson` (formato anterior) en `/app/polygons/` (`INGESTA_POLIGONOS`).
- Valores de provincia: los 8 códigos ISO, `FUERA` o null (sin posición válida). Los mismos en `node`, `position`, `packet`, `decoded` y vistas.
- Contadores: `posicion_invalida`, `cambios_provincia`, `nodos_registrados`, `gateways_registrados`.

## Unidades de trabajo

### UT-05.3.1 — Polígonos de provincias

- **Comportamiento:** el repositorio contiene el GeoJSON listo y el servicio lo carga y valida.
- **Detalle:** preparación documentada y reproducible; proyección local; geometrías preparadas.
- **Casos borde:** archivo con 7 provincias; geometría inválida (se repara con `make_valid` al cargar y se registra).
- **Aceptación:** puntos de control dentro de cada capital dan su código; el archivo pesa < 1 MB.

### UT-05.3.2 — Asignación de provincia y precisión

- **Comportamiento:** provincia, `precision_m` y `border_uncertain` para cada posición válida.
- **Detalle:** fórmula de precisión, margen costero y regla de borde de esta página.
- **Casos borde:** (0, 0); 10 bits; punto en el mar a 1 km de Chipiona con 14 bits; punto en Gibraltar con precisión completa; punto en Ayamonte con 13 bits.
- **Aceptación:** Jerez de la Frontera → `ES-CA`; Sevilla capital → `ES-SE`; mar frente a Chipiona (14 bits) → `ES-CA`; Gibraltar (32 bits) → `FUERA`; un punto a 1 km del límite Cádiz–Sevilla con 13 bits → `border_uncertain = true`.

### UT-05.3.3 — Registro de nodos

- **Comportamiento:** estado vivo de cada nodo, persistido y usado por 02, 06 y las vistas.
- **Detalle:** tabla de campos; escritura de filas modificadas cada 5 s; carga al arrancar.
- **Casos borde:** nodo visto solo como gateway; nodeinfo sin `role`; map report más antiguo que un nodeinfo ya recibido (gana el más reciente por hora de recepción).
- **Aceptación:** tras un nodeinfo nuevo, el `decoded` de ese mismo paquete ya lleva los nombres nuevos en `from_node`; tras reiniciar, el registro coincide con la tabla.

### UT-05.3.4 — Registro de gateways

- **Comportamiento:** último mensaje, contador e intervalo típico de cada gateway.
- **Detalle:** mediana móvil de 100 intervalos; escritura cada 5 s.
- **Casos borde:** gateway que solo envía map reports (intervalo ~1 h); gateway nuevo con < 10 intervalos.
- **Aceptación:** con un inyector a 1 mensaje cada 10 s, `typical_interval_s` ≈ 10 tras 11 mensajes; un gateway parado conserva `last_message_at`.

## Escenarios de prueba

- **Dado** un nodo con posición en Jerez de la Frontera y 16 bits, **cuando** llega su posición, **entonces** `province = ES-CA`, `precision_m = 364` y `border_uncertain = false`.
- **Dado** un nodo costero cuya posición truncada cae 800 m mar adentro con 14 bits, **cuando** llega, **entonces** se asigna la provincia de la costa más cercana.
- **Dado** un nodo en Portugal con precisión completa, **cuando** llega su posición, **entonces** `province = FUERA`.
- **Dado** un map report de un gateway con firmware y posición, **cuando** llega, **entonces** `node.firmware`, la posición y la provincia se actualizan y `is_gateway = true`.
- **Dado** un nodo que pasa de Cádiz a Málaga, **cuando** llega la nueva posición, **entonces** los paquetes siguientes se guardan con `province = ES-MA` y los anteriores no cambian.

---
> Creado: 2026-10-07 · Última revisión: 2026-10-07
