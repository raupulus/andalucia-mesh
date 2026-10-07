# 08 · Rankings

> `/rankings` · Mostrar qué nodos están en peligro ahora, cuáles consumen más tiempo de aire y quién aporta más a la malla, por periodos · `../08-rankings-alerts.md` (datos en `../12-stats-api.md` y `03-estado-routers-y-alertas.md`)

## SEO

- **Título:** `Rankings de la malla · {PROJECT_NAME}` (42 caracteres)
- **Descripción:** `Qué nodos consumen más tiempo de aire, cuáles están en peligro y quién aporta más a la malla de Andalucía, por hora, día, semana y mes.` (135)

## Estructura

| # | Sección | Componente (`DESIGN.md`) |
|---|---|---|
| 1 | H1 y entradilla | Tipografía H1 + Texto |
| 2 | Nodos en peligro (siempre arriba) | Tablas + Chips de severidad |
| 3 | Selector de periodo (afecta a 4, 5 y 6) | Botones secundarios, el activo con indicador `acento` |
| 4 | Consumo de red por nodo | Tablas + Gráficos (barra de desglose por tipo de paquete) |
| 5 | Más rankings: un panel por ranking del catálogo con su top 10 | Paneles `superficie` (radio 16 px) con Tablas |
| 6 | Cómo se reparte el tráfico | Gráficos (barra de desglose) + Tablas |
| 7 | Sobre estos datos | Texto secundario |

Los bloques 2 y 4 van arriba del todo y en ese orden (RF-PO-RK-1). Severidad con texto además de color (RF-PO-RK-5).

## Borrador del texto

Cada `###` es un H2 de la página.

**H1:** Rankings

Cómo se comporta la malla y quién la cuida: qué nodos tienen problemas ahora mismo, cuáles ocupan más el canal y quién aporta más cobertura. No es una competición: sirve para ver qué conviene corregir.

### Nodos en peligro

Nodos con alertas abiertas de riesgo medio o alto: reinicios en bucle, batería baja, routers que han dejado de oírse, gateways caídos, saturación… Se actualiza cada 30 segundos.

Columnas: **Riesgo** · **Nodo** · **Provincia** · **Motivo** · **Desde**

- Riesgo: chip con el riesgo máximo del nodo ("Alto" o "Medio").
- Nodo: nombre corto y largo, con el id en Ubuntu Mono; enlaza a su ficha en MeshView.
- Motivo: una línea por alerta abierta ("7 reinicios en la última hora", "Batería al 34 %"), cada una enlazada a su ficha en `/alertas/{id}`.
- Desde: tiempo relativo ("hace 3 h"), con la fecha completa como texto accesible.

Orden: primero riesgo alto, después por antigüedad del problema.

Sin nodos en peligro: "Ningún nodo en peligro ahora mismo."

[Ver todas las alertas →](/alertas)

### Periodo

Selector: **Hora** · **Día** · **Semana** · **Mes**, y **En curso** · **Anterior**. Por defecto: Día, Anterior.

Bajo el selector, una línea con lo que se está viendo y sus fechas:

| Selección | Texto |
|---|---|
| Hora · en curso / anterior | "Esta hora" / "La última hora completa" |
| Día · en curso / anterior | "Hoy" / "Ayer" |
| Semana · en curso / anterior | "Esta semana" / "La semana pasada" |
| Mes · en curso / anterior | "Este mes" / "El mes pasado" |

Seguido de "· del {from} al {to}". Nota bajo el selector: "Los periodos cerrados ya no cambian; los que están en curso se van actualizando."

### Consumo de red por nodo

Tiempo de aire que ocupan los paquetes que emite cada nodo (no los que repite para otros). Mucho consumo suele deberse a intervalos de información del nodo, posición o telemetría demasiado cortos. [Cómo ajustarlos →](/configura-tu-nodo#intervalos)

Columnas: **Puesto** · **Nodo** · **Provincia** · **Tiempo de aire** (s) · **% del total** · **Paquetes** · **Por tipo** (barra apilada)

Leyenda de la barra: NodeInfo · Posición · Telemetría · Texto · Otros. Los porcentajes de cada tipo también se leen en texto (al enfocar la fila o en la tabla accesible de la barra).

Nota: "Tiempo de aire estimado a partir del tamaño de cada paquete y de la configuración de radio. Cada paquete cuenta una vez aunque lo oigan varios gateways."

### Más rankings

Un panel por ranking, con su nombre, una línea de explicación y los 10 primeros (**Puesto** · **Nodo** · **Valor**). El nombre del nodo enlaza a su ficha en MeshView.

| Ranking | Explicación |
|---|---|
| Gateways que más cubren | Nodos distintos que oye cada gateway |
| Gateways imprescindibles | Paquetes que solo ha subido ese gateway: cobertura que nadie más aporta |
| Enlaces directos más largos | Distancia entre un nodo y el gateway que lo oye sin saltos intermedios |
| Mejores enlaces | Relación señal/ruido media de los enlaces directos con al menos 10 recepciones |
| Nodos mejor conectados | Vecinos directos distintos de cada nodo |
| Nodos más estables | Más tiempo seguido encendidos sin reiniciarse |
| Solares más sanos | Nodos con batería cuya carga mínima del periodo fue la más alta |
| Más conversadores | Mensajes de texto en los canales públicos |
| Nodos nuevos | Nodos vistos por primera vez en este periodo |
| Provincias que más crecen | Diferencia de nodos activos frente al periodo anterior |

Sin datos suficientes: "Sin datos suficientes en este periodo."

### Cómo se reparte el tráfico

Qué parte del tráfico de la malla es cada tipo de paquete en el periodo elegido. Los mensajes de texto suelen ser una parte pequeña: el resto son paquetes automáticos.

Barra apilada (NodeInfo · Posición · Telemetría · Texto · Otros) y tabla con el porcentaje de cada tipo.

### Sobre estos datos

- Solo cuenta lo que llega a nuestros gateways desde nodos con OK to MQTT activado.
- Solo se tienen en cuenta los canales públicos que acepta el servidor.
- Los rankings en curso se actualizan cada pocos minutos; los de periodos cerrados no cambian.

### Estados (comunes a todos los bloques)

| Estado | Texto |
|---|---|
| Cargando | "Cargando…" |
| Datos antiguos (`stale`) | "Datos de hace {minutos} min." |
| Error en un bloque | "Este bloque no está disponible ahora mismo. El resto de la página sigue funcionando." |

## Datos dinámicos y configuración

| Bloque | Endpoint | Refresco |
|---|---|---|
| Nodos en peligro | `GET /api/v1/nodes/at-risk` (`min_risk=medio` por defecto) | Cada 30 s sin recargar (RF-PO-RK-2) |
| Catálogo (nombre y orden de los paneles) | `GET /api/v1/stats/rankings` | Al cargar |
| Consumo de red | `GET /api/v1/stats/rankings/network-usage?period={period}&which={which}` | 60 s si el periodo está en curso; nada si está cerrado |
| Resto de rankings | `GET /api/v1/stats/rankings/{id}?period={period}&which={which}&limit=10` | Igual |
| Reparto del tráfico | `GET /api/v1/stats/traffic-mix?period={period}` | Igual |

- **URL compartible:** `/rankings?period=week&which=previous` (RF-PO-RK-4). Por defecto `period=day&which=previous`.
- **`{from}` y `{to}`** de la respuesta, en hora local (Europe/Madrid).
- **Unidades** de la columna Valor: campo `unit` de cada respuesta (s, km, nodos, %…), no escritas a mano.
- **Desglose por tipo:** `by_type` de cada fila; `traceroute` y cualquier tipo sin color propio se suman en "Otros" (`DESIGN.md` define 5 categorías). La suma es 100 %.
- **Provincia:** código ISO (`ES-CA`) traducido a nombre en el portal.
- **Ficha del nodo en MeshView:** `https://meshview.{PROJECT_DOMAIN}` + la ruta de ficha de nodo de la versión fijada de MeshView, en configuración (no escrita en la vista).
- **Nombres y explicaciones de los rankings:** la tabla de "Más rankings" es el borrador de lo que debe devolver el catálogo (`/stats/rankings`); si el catálogo no trae descripción, se toma de `resources/contenido/rankings.md`. Solo aparecen los rankings que devuelva el catálogo.
- **Cambio respecto al módulo 04:** la severidad "crítico / aviso" pasa a riesgo `alto` / `medio`, con chips `critico` / `aviso`.

---
> Creado: 2026-10-07 · Última revisión: 2026-10-07
