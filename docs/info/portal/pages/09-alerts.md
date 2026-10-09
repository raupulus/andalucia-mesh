# 09 · Alertas

> `/alertas` y `/alertas/{id}` · Listar las alertas abiertas y su historial con riesgo y tipo, y dar a cada alerta una ficha propia, que es el enlace que envían los bots · `../01-structure-home.md` (datos en `../13-nodes-alerts-api.md` y `../../detector-alertas/`)

## SEO

**Listado `/alertas`**

- **Título:** `Alertas de la malla · {PROJECT_NAME}` (41 caracteres)
- **Descripción:** `Problemas que detecta el sistema en la malla de Andalucía (baterías bajas, reinicios en bucle, gateways caídos, spam) con su riesgo y su tipo.` (142)

**Ficha `/alertas/{id}`** (dinámicos, recortados a 60 y 155 caracteres)

- **Título:** `{regla} · {nodo} · {PROJECT_NAME}`. Ejemplo: "Bucle de reinicio · CAD1 · Sur Nodos en Mallas" (46). Si la alerta afecta a muchos nodos, `{nodo}` = "{n} nodos".
- **Descripción:** `{mensaje}. Riesgo {riesgo}, {tipo}, {provincia}. Abierta el {fecha}.`

## Estructura

**Listado `/alertas`**

| # | Sección | Componente (`DESIGN.md`) |
|---|---|---|
| 1 | H1 y entradilla | Tipografía H1 + Texto |
| 2 | Resumen de alertas abiertas | Chips de severidad (riesgo con número) + texto secundario (tipos) |
| 3 | Filtros: estado, riesgo, tipo, provincia (y nodo, si viene en la URL) | Botones secundarios con indicador `acento` y campos con `borde-control` |
| 4 | Lista de alertas | Tablas + Chips de severidad |
| 5 | Ver más | Botones (secundario) |
| 6 | Qué significan el riesgo y el tipo | Tablas |
| 7 | Si una alerta es de tu nodo | Texto corrido |
| 8 | Recibe las alertas | Texto con enlaces a `/bots` y `/api#webhooks` |

**Ficha `/alertas/{id}`**

| # | Sección | Componente (`DESIGN.md`) |
|---|---|---|
| 1 | Cabecera: chips de riesgo y estado, H1 con el nombre de la regla, mensaje | Chips de severidad + Tipografía H1 + Texto |
| 2 | Datos de la alerta | Bloque de ajustes (tabla clave-valor) |
| 3 | Datos que la dispararon | Bloque de ajustes |
| 4 | Nodos afectados (solo si afecta a muchos) | Tablas |
| 5 | Historial | Tablas + Chips de severidad |
| 6 | Qué significa y qué hacer | Texto corrido |
| 7 | Enlaces | Lista con enlaces en `enlace` |

Correspondencia de chips (la misma que los colores de los bots): `alto` → `critico`, `medio` → `aviso`, `bajo` → `info`, resuelta → `correcto`. Siempre icono y palabra.

## Borrador del texto

### Listado `/alertas`

**H1:** Alertas

El sistema revisa todo lo que llega de la malla y abre una alerta cuando detecta algo que la pone en riesgo: un router con la batería baja, un nodo que se reinicia sin parar, un gateway que deja de publicar, un canal saturado o un nodo que envía demasiados paquetes. Cada alerta se cierra sola cuando el problema desaparece.

**Resumen**

"Ahora hay {total} alertas abiertas:" + chips "{alto} Alto" · "{medio} Medio" · "{bajo} Bajo" + "{infraestructura} de infraestructura · {clientes} de clientes".

Sin alertas abiertas: "Ninguna alerta abierta ahora mismo."

**Filtros**

Panel oxigenado en tarjeta independiente (`DESIGN.md`):
- Estado: **Abiertas** · **Resueltas** · **Todas**
- Severidad / Riesgo: **Todos** · **Alto** · **Medio** · **Bajo**
- Temática del problema (`?problemas=` o individual `?problema=`):
  - 🔘 **Todas las temáticas**
  - 🪫 **Batería y Energía** (`battery-low`, `sunset-battery`)
  - 📍 **Posición y GPS** (`position-flood`, `router-moving`)
  - 📈 **Telemetría y Nodeinfo** (`telemetry-burst`, `poll-abuse`)
  - 🗺️ **Traceroutes** (`traceroute-flood`)
  - 💬 **Spam e Inundación** (`text-flood`, `flood`, `rafaga-masiva`, `private-chaff`)
  - 📊 **Canal y Saltos** (`chutil-high`, `airtime-high`, `hops-high`)
  - 🌐 **Gateways e Infraestructura** (`gateway-offline`, `gateway-no-traffic`, `infra-silent`, `router-role`, `router-cluster`)
  - 🔐 **Hardware y Seguridad** (`reboot-loop`, `key-security`, `asymmetric-link`)
- Diseño responsive de filtros: cuadrícula de tarjetas cuadradas (`.grid-tematicas-filtro`) que aprovecha todo el ancho disponible (`repeat(auto-fill, minmax(130px, 1fr))` en escritorio y `repeat(2, 1fr)` en móvil < 540px) y permite selección múltiple acumulativa en tiempo real.
- Navegación instantánea SPA / AJAX: las acciones de filtrado y paginación no recargan la página completa, sino que actualizan dinámicamente el contenedor `#alertas-app` vía `fetch()` y `DOMParser`, sincronizando la URL del navegador mediante `history.pushState` y evento `popstate`.
- Paginación: listado paginado (15 por página) ordenado estrictamente por las alertas más recientes primero (`inicio_at DESC, id DESC`).
- Barra de filtros activos: si hay filtros aplicados, muestra chips desmontables (`✕`) para cada filtro activo, contador de incidencias coincidentes y enlace rápido "Limpiar filtros".
- Sincronización del catálogo inferior: filtrar por temática adapta simultáneamente las tarjetas del catálogo de anomalías a las reglas de dicha categoría con opción a restablecer.
- Si la URL trae un nodo: "Alertas del nodo {short} (`{id}`) · Quitar".

**Lista**

Columnas: **Riesgo** · **Alerta** (nombre de la regla y, debajo, el mensaje) · **Nodo** · **Provincia** · **Tipo** · **Abierta** · **Estado**

- Toda la fila enlaza a la ficha.
- Si la alerta afecta a muchos nodos, la columna Nodo dice "{n} nodos".
- Abierta: tiempo relativo ("hace 2 h"), con la fecha completa como texto accesible.
- Estado: "Abierta" o chip "Resuelta" con la fecha.

Botón secundario: "Ver más alertas".

Sin resultados con los filtros: "No hay alertas con estos filtros."

**Qué significan el riesgo y el tipo** (H2)

| Riesgo | Qué significa |
|---|---|
| Alto | Fallo activo o daño a la malla |
| Medio | Riesgo real para un nodo o una zona; conviene actuar |
| Bajo | Conviene saberlo; no hay daño inmediato |

| Tipo | Qué cubre |
|---|---|
| Infraestructura | Routers, repetidores y gateways, y cualquier problema que afecte a muchos nodos o a toda la malla (spam, ráfagas, canal saturado), lo cause quien lo cause |
| Clientes | Problemas de un nodo concreto que no es infraestructura, como la batería baja de un nodo personal |

**Si una alerta es de tu nodo** (H2)

No es una acusación. Las alertas son automáticas y orientativas, y a veces se equivocan. Revisa la guía [Configura tu nodo](/configura-tu-nodo): muchas se resuelven ajustando los intervalos, el rol o los saltos. Si crees que una alerta es un error, escríbenos a {PROJECT_CONTACT} con su enlace.

Solo vemos lo que suben los gateways desde nodos con OK to MQTT activado, así que puede haber problemas que no aparezcan aquí.

**Catálogo de Anomalías Monitorizadas** (H2)

Muestra la totalidad de las 22 reglas automáticas del detector en tarjetas visuales responsivas. Cada tarjeta incluye:
- Categoría técnica del incidente (`Topología e infraestructura`, `Suministro y energía`, `Conectividad MQTT`, `Uso de canal de radio`, `Radiofrecuencia y enlaces`, `Seguridad e identidad`, etc.).
- Icono temático identificativo y nombre descriptivo en español.
- Chip semántico con el nivel de severidad o riesgo (`Alto` en rojo crítico, `Medio` en ámbar de aviso, `Bajo` en azul informativo).
- Descripción detallada del síntoma y umbrales de activación calibrados para la red de Andalucía.
- Tipo de incidencia localizado (`Infraestructura`, `Clientes`, `Red / Malla`).
- Código identificador canónico de la regla (`rule_id`, ej. `hops-high`, `gateway-no-traffic`, `router-cluster`, `key-security`).

**Recibe las alertas** (H2)

En tu grupo o canal, con los [bots de Telegram y Discord](/bots). En tu propio sistema, con los [webhooks](/api#webhooks).

### Ficha `/alertas/{id}`

**Cabecera:** chip de riesgo ("Alto") y chip de estado ("Abierta" o "Resuelta").

**H1:** {nombre de la regla} (por ejemplo, "Bucle de reinicio")

{mensaje} (por ejemplo, "CAD1 se ha reiniciado 7 veces en la última hora")

**Datos de la alerta**

| Campo | Valor |
|---|---|
| Nodo | {corto} ({largo}) · `{id}` |
| Rol | `{rol}` |
| Provincia | {provincia} |
| Tipo | {tipo} |
| Abierta | {abierta_en} |
| Última actualización | {actualizada_en} |
| Resuelta | {fecha de la transición `resuelta`} o "Sigue abierta" |
| Duración | {duración} |

**Datos que la dispararon** (H2)

Tabla clave-valor con los datos de la alerta, con etiquetas legibles. Ejemplo para un bucle de reinicio: "Reinicios: 7 · Ventana: 60 min · Tiempo encendido mínimo: 84 s".

**Nodos afectados** (H2, solo si la alerta afecta a muchos nodos)

"Esta alerta afecta a {n} nodos." Tabla: **Nodo** · **Provincia**.

**Historial** (H2)

Columnas: **Fecha** · **Cambio** (Abierta, Actualizada, Resuelta) · **Riesgo**

**Qué significa y qué hacer** (H2)

{descripción pública de la regla}

Si es tu nodo, revisa la guía [Configura tu nodo](/configura-tu-nodo). Si crees que es un error, escríbenos a {PROJECT_CONTACT} con el enlace de esta página.

**Enlaces**

- Ficha del nodo en MeshView
- [Otras alertas de este nodo](/alertas?node={id}&state=all)
- [Todas las alertas](/alertas)

**Alerta que no existe:** "Esta alerta no existe o ya no se conserva. Las alertas se guardan durante un año." + enlace a `/alertas`. Respuesta 404.

### Estados (listado y ficha)

| Estado | Texto |
|---|---|
| Cargando | "Cargando alertas…" |
| Datos antiguos (`stale`) | "Datos de hace {minutos} min." |
| Error | "Las alertas no están disponibles ahora mismo. Vuelve a intentarlo en unos minutos." |

## Datos dinámicos y configuración

| Dato | Endpoint u origen | Refresco |
|---|---|---|
| Lista | `GET /api/v1/alerts?state={state}&risk={risk}&type={type}&province={province}&node={node}&limit={n}&cursor={cursor}` | Cada 30 s mientras la página esté visible |
| Resumen | `GET /api/v1/stats/summary` → `alerts_open.by_risk` y `alerts_open.by_type`; `{total}` = suma de `by_risk` | Igual |
| Nombres de regla, descripción pública, riesgos y tipos | `GET /api/v1/alerts/catalog` | Al cargar (TTL 1 h) |
| Ficha | `GET /api/v1/alerts/{id}`: campos de la alerta (`regla`, `riesgo`, `tipo`, `mensaje`, `nodo`, `nodos`, `nodo_info`, `datos`, `estado`, `abierta_en`, `actualizada_en`) e historial de transiciones | Cada 30 s si sigue abierta |

- **URL compartible** del listado: `/alertas?state=resolved&risk=alto&province=ES-CA&node=!a1b2c3d4` (supuesto, igual que en Rankings). Por defecto, `state=open`.
- **Alerta que afecta a muchos nodos:** `nodo = "all"` y la lista en `nodos`.
- **Etiquetas de "Datos que la dispararon":** el campo `datos` cambia según la regla. Hace falta una etiqueta legible por clave (por ejemplo, `reinicios` → "Reinicios", `ventana_min` → "Ventana (min)"); las etiquetas viven en el portal (`lang/es/alertas.php`, una por clave de `datos` de las reglas de `../../detector-alertas/02-rule-catalog.md`). Si una clave no tiene etiqueta, se muestra la clave tal cual.
- **Fechas** en hora local (Europe/Madrid). Provincias de código ISO a nombre.
- **Ficha del nodo en MeshView:** misma configuración que en `08-rankings.md`.
- **Conservación de un año:** la de las alertas según `../10-legal-privacy.md`.
- Sin coordenadas en ninguna parte: solo provincia.

## Supuestos aplicados

- `/alertas` y las fichas `/alertas/{id}` son indexables (criterio: todo es público); las fichas no van en `sitemap.xml` por volumen.

---
> Creado: 2026-10-07 · Última revisión: 2026-10-09
