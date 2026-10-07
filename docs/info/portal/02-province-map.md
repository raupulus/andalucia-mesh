# 06.2 · Mapa de provincias

> Mapa de Andalucía bajo las tarjetas de la portada: total de nodos, una burbuja con los nodos de cada provincia y la provincia coloreada según la carga del canal medida por su infraestructura. Texto en `pages/01-home.md` (sección "Mapa de Andalucía").

## Objetivo

Ver la malla de Andalucía de un vistazo, sin peticiones a terceros, con una alternativa en tabla para quien no ve el color.

## Especificación

### Dibujo

- SVG propio de las 8 provincias a partir del GeoJSON de límites del CNIG simplificado (`public/mapa/andalucia.geojson`, < 50 KB, CC BY 4.0, crédito "© Instituto Geográfico Nacional" en el aviso legal). Sin teselas ni librerías de mapas.
- El SVG se genera una vez en el despliegue (`php artisan portal:mapa`: proyección, `path` por provincia y punto de la burbuja) y se incrusta en la vista. El cliente solo cambia clases y textos.
- **Punto de la burbuja:** polo de inaccesibilidad de cada provincia (precalculado, siempre dentro del contorno), no el centroide. Ajuste manual permitido en `config/proyecto.php` → `mapa.burbujas` si dos burbujas se solapan a 360 px.
- Burbuja: círculo de 44 px (36 px en móvil), número en Inter 600 tabular, fondo claro con borde oscuro; Cádiz con borde de acento (foco del proyecto). Colores y trazos de `DESIGN.md` §3 "Mapa de provincias".

### Datos

- `GET /api/v1/stats/provinces?window=7d|24h` (`12-stats-api.md`). La API admite también `30d`; el mapa solo ofrece 7 días (por defecto) y 24 horas.
- Primera pintura con los datos del servidor (`App\Datos\Ingesta\Provincias`, misma caché). Refresco cada 5 min con `fetch` mientras la pestaña está visible (`visibilitychange`).
- Encima: "**{total_andalucia}** nodos en Andalucía", la ventana activa y "actualizado hace X min" (`generated_at`).

### Color por saturación

| `level` | Color | Rango (`load.avg`, saturación ponderada) |
|---|---|---|
| `green` | Verde | ≤ 20 % |
| `orange` | Naranja | > 20 % y < 40 % |
| `red` | Rojo | ≥ 40 % |
| `nodata` | Gris | Ningún router, `CLIENT` ni `CLIENT_BASE` con telemetría en 12 h |

- La saturación la calcula la API (`12-stats-api.md`, `../integration.md` §13): nodos con posición en la provincia, media de `channel_utilization` por grupo y suma ponderada **routers 60 % + `CLIENT` y `CLIENT_BASE` juntos 40 %**; `CLIENT_MUTE` no cuenta; si falta un grupo, su peso se reparte entre los demás. Último dato de cada nodo en 12 h. El cliente no calcula niveles: usa `level`.
- No depende del selector de ventana (siempre 12 h).

### Interacción

- Provincia enfocable (`tabindex=0`, `role="button"`, `aria-label` con nombre, nodos y carga en texto). Clic, toque o Intro → panel con nodos, % del total, saturación, carga máxima y, por grupo (routers; `CLIENT` y `CLIENT_BASE` juntos), su media y cuántos nodos la miden. Hover en escritorio muestra el mismo panel.
- Leyenda con los cuatro colores y, debajo, el texto fijo de cómo se calcula:

  > "Cómo calculamos la saturación de cada provincia: con las coordenadas buscamos todos los nodos que están dentro de la provincia; sacamos la media de la ocupación del canal de los routers por un lado y la de todos los nodos CLIENT y CLIENT_BASE juntos por otro; y sumamos con pesos: routers 60 % y CLIENT con CLIENT_BASE 40 % (estos dos van juntos en una sola media). Los CLIENT_MUTE no cuentan porque suelen estar en interior o peor comunicados y su medida sale más baja de lo real. Se usa el último dato de cada nodo en las 12 últimas horas."
- **Tabla accesible** debajo (provincia · nodos · carga media · estado en texto), siempre presente (plegable en móvil).
- Notas: `notes` de la API ("Solo nodos cuya posición llega con OK to MQTT…") y, si `outside_andalucia` > 0, "y {outside_andalucia} fuera de Andalucía".

### Estados

| Estado | Comportamiento |
|---|---|
| Cargando (sin datos de servidor) | Provincias en gris claro, burbujas vacías |
| OK | Números y colores |
| `stale: true` | Igual, con "datos de hace X min" |
| API caída sin copia | Gris, burbujas sin número, "Estadísticas no disponibles ahora mismo" |
| Provincia con 0 nodos | Burbuja con "0" |

## Contratos propios

- `config/proyecto.php` → `mapa.carga` (`verde_max` 20, `rojo_min` 40; mismos valores que la API), `mapa.burbujas` (ajustes opcionales), `provincias` (código, nombre, orden).
- Componente Blade `x-mapa-provincias` + `resources/js/mapa.js` (sin dependencias).

## Unidades de trabajo

- **UT-06.2.1 — Generación del SVG.** Comando `portal:mapa`: simplificación, proyección, polos de inaccesibilidad. *Aceptación:* SVG < 60 KB; ninguna burbuja fuera de su provincia ni solapada a 360 px.
- **UT-06.2.2 — Pintado y refresco.** Datos de servidor, `fetch` cada 5 min con pestaña visible, selector 7 d / 24 h con estado en la URL (`?ventana=24h`). *Aceptación:* cambiar de ventana no recarga la página.
- **UT-06.2.3 — Panel y accesibilidad.** Panel, foco, tabla, lectores de pantalla. *Aceptación:* con lector de pantalla se obtiene la misma información que con el color.
- **UT-06.2.4 — Estados.** *Aceptación:* los cinco estados de la tabla con datos simulados.

## Escenarios de prueba

1. **Dado** provincias con carga 15, 30 y 45 %, **cuando** se pinta el mapa, **entonces** salen verde, naranja y rojo; una sin datos sale gris con "sin datos de carga" en panel y tabla.
2. **Dado** la ventana de 24 h, **cuando** se pulsa "7 días", **entonces** cambian los números, no los colores, y la URL refleja la ventana.
3. **Dado** la API caída sin copia, **cuando** se abre la portada, **entonces** el mapa sale gris con el mensaje y el resto de la portada funciona.
4. **Dado** un teclado, **cuando** se tabula por el mapa, **entonces** se recorren las 8 provincias y cada una abre su panel con Intro.
5. **Dado** un `CLIENT_MUTE` con carga 80 % en Huelva, **cuando** se pinta el mapa, **entonces** no afecta al color de Huelva; un `CLIENT_BASE` al 80 % sí entra en la media de clientes junto a los `CLIENT`.

---
> Creado: 2026-10-07 · Última revisión: 2026-10-08
