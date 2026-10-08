# 06.8 · Rankings y alertas

> Páginas `/rankings` (destino de la tercera tarjeta), `/alertas` y `/alertas/{id}` (enlace que envían los bots). Textos en `pages/08-rankings.md` y `pages/09-alerts.md`. Datos de `12-stats-api.md` y `13-nodes-alerts-api.md`.

## Objetivo

Mostrar qué nodos están en peligro ahora, cuáles consumen más tiempo de aire y quién aporta más a la malla por periodos; y dar a cada alerta una ficha pública y compartible.

## Especificación

### `/rankings`

Orden fijo:

1. **Nodos en peligro** (siempre arriba): `GET /api/v1/nodes/at-risk?min_risk=medio`, refresco cada 30 s. Fila: chip de riesgo (icono + texto), nodo (corto, largo, id en Ubuntu Mono), provincia, motivos (`reasons`), desde cuándo, enlaces a la alerta más grave (`/alertas/{id}`), a `/revisa-tu-nodo/{id}` y a la ficha del nodo en MeshView. Orden de la API (`riesgo_max_orden` desc, `desde` asc). Vacío: "Ningún nodo en peligro ahora mismo".
2. **Selector de periodo:** Hora · Día · Semana · Mes y En curso / Anterior; por defecto Día + Anterior ("ayer", cerrado y estable). Afecta a 3, 4 y 5. Estado en la URL (`/rankings?period=week&which=previous`).
3. **Consumo de red por nodo:** `GET /api/v1/stats/rankings/network-usage?period=&which=&limit=20`. Fila: puesto, nodo, provincia, tiempo de aire (s) y % del total, paquetes, barra apilada por tipo (nodeinfo, posición, telemetría, texto, otros; `traceroute` y resto suman en "Otros", 5 categorías de `DESIGN.md`). Texto fijo: "Mucho consumo suele deberse a intervalos demasiado cortos" con enlace a `/configura-tu-nodo#intervalos`. Tono de diagnóstico.
4. **Más rankings:** un panel por ranking del catálogo (`GET /api/v1/stats/rankings`) salvo `network-usage`, con su top 10 (`limit=10`): puesto, nodo o gateway, valor con unidad.
5. **Cómo se reparte el tráfico:** `GET /api/v1/stats/traffic-mix?period=&which=`: barra apilada de paquetes y de tiempo de aire por tipo, con tabla equivalente.
6. **Sobre estos datos:** solo canales de la lista, solo lo que llega con OK to MQTT, periodos en hora de Madrid.

Refresco de 3–5: cada 60 s si el periodo está en curso; nada si está cerrado. Un bloque cuya fuente falla avisa y el resto sigue.

### `/alertas`

1. Resumen de abiertas: chips por riesgo con número (`stats/summary` → `alerts_open.by_risk`) y texto por tipo.
2. Filtros: estado (`open` por defecto, `resolved`, `all`), riesgo, tipo, provincia y nodo si viene en la URL. Estado en la URL (`/alertas?state=resolved&risk=alto&province=ES-CA&node=!a1b2c3d4`).
3. Lista (`GET /api/v1/alerts?…&sort=recent&limit=50`): chip de riesgo, tipo, regla (nombre del catálogo), mensaje, nodo o "N nodos", provincia, abierta/actualizada/resuelta. "Ver más" sigue `next_cursor`.
4. Qué significan riesgo y tipo (catálogo); "si una alerta es de tu nodo" con enlace a `/revisa-tu-nodo`; "recibe las alertas" con enlaces a `/bots` y `/api#webhooks`.

Refresco de la lista abierta cada 30 s (solo si el usuario no ha pulsado "Ver más").

### `/alertas/{id}`

- Renderizado en servidor desde `GET /api/v1/alerts/{id}`; `404` con página propia si no existe o tiene más de 1 año.
- Cabecera: chips de riesgo y estado, H1 con el nombre de la regla, mensaje. Datos de la alerta (tabla clave-valor). "Datos que la dispararon": claves de `data` con etiquetas de `lang/es/alertas.php` (clave sin etiqueta → se muestra tal cual). Nodos afectados si es de `all` (hasta 50 + "y N más"). Historial (`transitions`). Qué significa y qué hacer (descripción pública de la regla del catálogo). Enlaces: `/revisa-tu-nodo/{id}`, MeshView, listado filtrado por el nodo.
- Refresco cada 30 s mientras esté abierta.
- Indexable pero fuera de `sitemap.xml`. Open Graph con regla, riesgo y mensaje (para la vista previa en Telegram y Discord).

### Comunes

- Fechas en hora de Madrid; provincias por nombre; sin coordenadas en ninguna parte.
- Ficha del nodo en MeshView: `https://meshview.${PROJECT_DOMAIN}` + ruta de nodo de la versión fijada de MeshView en `config/proyecto.php` → `enlaces.meshview_nodo` (plantilla con `{id}` en el formato que espere MeshView).
- Severidad siempre con icono y texto, no solo color. Tablas reales (`<table>`), cabeceras con `scope`.

## Contratos propios

- `config/proyecto.php` → `enlaces.meshview_nodo`, `rankings.portada` (orden de paneles), `refresco` (30/60 s).
- `lang/es/alertas.php` → etiquetas de las claves de `data` de cada regla de `../detector-alertas/02-rule-catalog.md`.
- URL pública de alerta `https://${PROJECT_DOMAIN}/alertas/{id}`: la usan los bots y el campo `url` de la API (contrato).

## Unidades de trabajo

- **UT-06.8.1 — Nodos en peligro.** *Aceptación:* una alerta `reboot-loop` abierta en pruebas aparece en < 40 s y desaparece al resolverse.
- **UT-06.8.2 — Periodos y consumo.** Selector con URL, barra apilada. *Aceptación:* el desglose de cada fila suma 100 %; compartir la URL de "semana anterior" muestra la misma vista.
- **UT-06.8.3 — Más rankings y reparto.** *Aceptación:* los rankings del catálogo × 4 periodos × 2 modos se pintan; "Sin datos suficientes en este periodo" cuando `items` está vacío.
- **UT-06.8.4 — Listado de alertas.** Filtros, URL, "Ver más". *Aceptación:* recorrer 120 alertas con "Ver más" no repite ni salta ninguna.
- **UT-06.8.5 — Ficha de alerta.** Etiquetas, historial, Open Graph. *Aceptación:* el enlace enviado por un bot abre la ficha correcta y su vista previa muestra regla y riesgo.
- **UT-06.8.6 — Fallos parciales.** *Aceptación:* con `alertas` caída, `/rankings` muestra los bloques 2–5 y avisa en el 1; con `ingest` caída, al revés.

## Escenarios de prueba

1. **Dado** las 10:00 del 26-10-2026, **cuando** se abre `/rankings` sin parámetros, **entonces** muestra Día + Anterior (25-10, día de 25 h) y no se refresca.
2. **Dado** una alerta de `all` con 120 nodos, **cuando** se abre su ficha, **entonces** muestra 50 nodos y "y 70 más".
3. **Dado** una clave `ventana_min` en `data` con etiqueta y una `nueva_clave` sin ella, **cuando** se pinta la ficha, **entonces** salen "Ventana (min)" y `nueva_clave`.
4. **Dado** `/alertas?node=!a1b2c3d4`, **cuando** se carga, **entonces** solo salen alertas de ese nodo y el filtro aparece visible y quitable.
5. **Dado** un lector de pantalla, **cuando** recorre nodos en peligro, **entonces** oye el riesgo en texto ("riesgo alto").

---
> Creado: 2026-10-07 · Última revisión: 2026-10-08
