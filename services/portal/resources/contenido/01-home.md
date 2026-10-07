# 01 · Inicio

> `/` · Que quien llega por el enlace o el QR entienda qué es esto, vea la malla de Andalucía de un vistazo y llegue en un toque al servicio que busca · `../01-structure-home.md` (con `02`, `05`, `06` y `07`)

## SEO

- **Título:** `{PROJECT_NAME} · La malla LoRa de Cádiz y Andalucía` (56 caracteres)
- **Descripción:** `Mapa de la malla de radio LoRa de Cádiz y Andalucía: nodos y carga por provincia, servicios en directo, alertas y cómo sumar tu nodo.` (133)
- Open Graph con el mismo título y descripción e imagen común del proyecto (logo propio), para que el enlace se vea bien en mensajería.

## Estructura

| # | Sección | Componente (`DESIGN.md`) |
|---|---|---|
| 1 | Cabecera común | Cabecera y pie |
| 2 | Presentación: nombre y una frase | Tipografía H1 + texto en `texto-2` |
| 3 | Tres tarjetas de servicio: MeshView, PotatoMesh, Consumo y nodos en peligro | Tarjeta de servicio (portada) |
| 4 | Mapa de Andalucía: total, selector 7 días / 24 horas, mapa con burbujas, leyenda, panel de provincia, tabla accesible y notas | Mapa de provincias; Botones (secundario, activo con indicador `acento`); Tablas |
| 5 | Configura tu nodo (resumen) | Bloque de ajustes (tarjeta `superficie` sin imagen, con "Guía completa →") |
| 6 | Sube los datos de tu nodo | Bloque de ajustes (pasos numerados, tabla, botón de copiar, chip `aviso` en downlink) |
| 7 | Quién está detrás | Autoría y enlaces externos |
| 8 | Aviso "no es un servicio de emergencias" | Texto secundario en `texto-2` con icono `info` (sin caja de color) |
| 9 | Pie común | Cabecera y pie |

Orden fijo (RF-PO-CT-2, RF-PO-CT-5). Separación entre secciones según `DESIGN.md` §6.

## Borrador del texto

### Cabecera (común a todas las páginas)

- Logo propio y **{PROJECT_NAME}**, enlazados a `/`.
- Navegación: **El proyecto** (`/proyecto`) · **Configura tu nodo** · **Conecta tu gateway** · **Rankings** · **Alertas** · **Bots** · **API**.
- Conmutador de modo, texto accesible: "Cambiar a modo oscuro" / "Cambiar a modo claro".
- En móvil, botón **Menú** que despliega la navegación.

### Presentación

**H1:** {PROJECT_NAME}

La malla de radio LoRa de Cádiz y Andalucía en un solo sitio: qué nodos hay, cómo va la red y qué está fallando.

### Tarjetas de servicio

| Tarjeta | Descripción (máx. 2 líneas) | Enlace | Texto alternativo de la ilustración |
|---|---|---|---|
| **MeshView** | Todo lo que pasa por la malla en directo: paquetes, rutas, nodos y quién oye a quién. | `https://meshview.{PROJECT_DOMAIN}` | Ilustración: paquetes viajando entre nodos |
| **PotatoMesh** | Mapa y chat de los canales públicos en tiempo real, con la ficha y la telemetría de cada nodo. | `https://potato.{PROJECT_DOMAIN}` | Ilustración: mapa con nodos y un globo de chat |
| **Consumo y nodos en peligro** | Qué nodos gastan más tiempo de aire y cuáles están en apuros: reinicios en bucle, batería baja, sin señal. | `/rankings` | Ilustración: barras de consumo y un nodo con aviso |

Cambio respecto al módulo 01: "canales públicos" en plural (se muestran todos los de la lista blanca) y la tercera descripción acortada para no pasar de 2 líneas.

### Mapa de Andalucía

Encima del mapa:

> **{total_andalucia}**
> nodos en Andalucía
> Últimos 7 días · actualizado a las {hora}

Con la ventana de 24 h, la segunda línea pasa a "Últimas 24 horas · actualizado a las {hora}".

Selector (etiqueta accesible "Ventana del recuento de nodos"): **7 días** · **24 horas**. Por defecto, 7 días.

Texto accesible del mapa: "Mapa de Andalucía con el número de nodos y la carga del canal de cada provincia. Los mismos datos están en la tabla de abajo."

Panel de provincia (al tocar o pasar por encima):

> **{name}**
> {nodes} nodos · {porcentaje} % del total de Andalucía
> Saturación estimada: {load.avg} % ({nivel})
> Carga máxima: {load.max} %
> Medida por {load.groups.routers.n} routers, {load.groups.clients.n} CLIENT o CLIENT_BASE en las últimas 12 horas

Si la provincia no tiene datos de carga: "Sin datos de carga: ningún router, CLIENT ni CLIENT_BASE de esta provincia ha enviado telemetría en las últimas 12 horas."

Leyenda:

- Verde · hasta {green_max} %: canal holgado
- Naranja · entre {green_max} % y {red_min} %: canal cargado, conviene revisar intervalos
- Rojo · {red_min} % o más: canal saturado
- Gris · sin datos

"Saturación = % de tiempo que el canal de radio está ocupado."

Cómo se calcula (texto fijo bajo la leyenda):

"Cómo calculamos la saturación de cada provincia: con las coordenadas buscamos todos los nodos que están dentro de la provincia; sacamos la media de la ocupación del canal de los routers por un lado y la de todos los nodos CLIENT y CLIENT_BASE juntos por otro; y sumamos con pesos: routers 60 % y CLIENT con CLIENT_BASE 40 % (estos dos van juntos en una sola media). Los CLIENT_MUTE no cuentan porque suelen estar en interior o peor comunicados y su medida sale más baja de lo real. Se usa el último dato de cada nodo en las 12 últimas horas."

Tabla accesible bajo el mapa, título "Nodos y carga por provincia": **Provincia** · **Nodos** · **Saturación** · **Estado** (Holgado, Cargado, Saturado o Sin datos, siempre en texto).

Notas pequeñas:

- "Solo nodos cuya posición nos ha llegado con OK to MQTT; el número real es mayor."
- "Además, {outside_andalucia} nodos oídos desde Andalucía están situados fuera." (solo si es mayor que 0)

Estados:

| Estado | Texto |
|---|---|
| Cargando | Texto accesible "Cargando datos del mapa…"; provincias en gris claro y burbujas vacías |
| Datos antiguos (`stale`) | "Datos de hace {minutos} min." |
| Error o API caída | "Estadísticas no disponibles ahora mismo. Vuelve a intentarlo en unos minutos." (sin enlace a página de estado: no existe) |
| Provincia con 0 nodos | Burbuja con "0" |

### Configura tu nodo (resumen)

**H2:** Configura tu nodo

Con estos ajustes tu nodo oye a la malla y no la satura. Es la configuración de radio que usa la mayor parte de la malla en España.

| Ajuste | Valor |
|---|---|
| Región | `{LORA_REGION}` |
| Usar preset | Desactivado |
| Ancho de banda · SF · CR | `{LORA_BANDWIDTH}` · `{LORA_SPREAD_FACTOR}` · `{LORA_CODING_RATE}` |
| Frequency slot | `{LORA_FREQUENCY_SLOT}` ({LORA_FREQUENCY_MHZ} MHz) |
| Canal 0 | `{PRIMARY_CHANNEL}`, clave `AQ==` |
| Límite de saltos | `{LORA_HOP_LIMIT}` |
| Rol | `CLIENT_MUTE` en casi todos los nodos; `CLIENT` si está en exterior despejado |
| NodeInfo y posición de nodo fijo | Cada 72 h |
| Telemetría del dispositivo | Desactivada si está enchufado; 4 h o más si es solar; 6 h o más si es troncal |

[Guía completa →](/configura-tu-nodo)

### Sube los datos de tu nodo

**H2:** Sube los datos de tu nodo

Si tu nodo tiene internet, puede hacer de gateway: sube lo que oye por radio a nuestro servidor y así la malla aparece completa en mapas, estadísticas y alertas. Solo sube: nada de lo que llega por internet vuelve a la radio.

1. Necesitas un nodo con WiFi o Ethernet (o que use la app del móvil como puente MQTT), configurado como arriba.
2. Pide tu usuario: escribe a {PROJECT_CONTACT} con el id de tu nodo (`!xxxxxxxx`) y tu zona aproximada. Te enviamos un usuario y una contraseña solo para ti.
3. Aplica estos ajustes.
4. En unos minutos tu nodo aparecerá en MeshView y PotatoMesh.

| Ajuste | Valor |
|---|---|
| MQTT activado | Sí |
| Servidor | `mqtt.{PROJECT_DOMAIN}` [Copiar] |
| Puerto | `1883` (`8883` con TLS) |
| Usuario / contraseña | Los tuyos: `!<id>` y la que te enviamos |
| Cifrado | Activado |
| JSON | Desactivado |
| TLS | Opcional |
| Root topic | `{MQTT_TOPIC_ROOT}` [Copiar] |
| Map reporting | Activado |
| Uplink | Activado en `{PRIMARY_CHANNEL}` y en los canales de la lista que uses |
| Downlink | Desactivado en todos los canales · chip `aviso` "Siempre desactivado" |
| OK to MQTT | Activado |
| Ignore MQTT | Activado |

¿Quieres las alertas de la malla en tu grupo de Telegram o en tu servidor de Discord? [Añade nuestros bots →](/bots)

[Guía completa →](/conecta-tu-gateway)

### Quién está detrás

**H2:** Quién está detrás

**H3:** Raúl Caro Pastorino

Desarrollador web full stack especializado en backend · @raupulus

"Soy un desarrollador backend con amplia experiencia en PHP, Laravel, Javascript y PostgreSQL. A lo largo de mi carrera he trabajado en una variedad de proyectos, desde pequeños sitios web hasta grandes aplicaciones empresariales."

{PROJECT_NAME} es un proyecto personal y sin ánimo de lucro. Contacto: {PROJECT_CONTACT}

- **Webs:** raupulus.dev · api.raupulus.dev
- **Redes:** LinkedIn · GitHub · Instagram · Telegram (canal de difusión)

[Más sobre quién lo impulsa →](/quien-lo-impulsa)

### Aviso

{PROJECT_NAME} no es un servicio de emergencias. Ni la malla ni estas webs garantizan que un mensaje llegue ni que estén disponibles. En una emergencia, llama al 112.

### Pie (común a todas las páginas)

- Enlaces: El proyecto · Quién lo impulsa · Cómo se gestiona · API · Aviso legal · Privacidad · Cookies
- "Proyecto independiente y sin ánimo de lucro. No está afiliado a los proyectos de software ni a los fabricantes de los equipos que usa."
- "Un proyecto de Raúl Caro Pastorino (@raupulus)", enlazado a https://raupulus.dev
- Contacto: {PROJECT_CONTACT}

## Datos dinámicos y configuración

| Dato | Origen |
|---|---|
| Total, nodos por provincia, carga (`avg`, `max`, `routers`, `level`), `outside_andalucia`, `generated_at`, `stale` | `GET /api/v1/stats/provinces?window=7d` (por defecto) o `?window=24h`. Refresco cada 5 min mientras la página esté visible |
| Cortes de la leyenda `{green_max}` y `{red_min}` | `load_levels` de la misma respuesta (configurables) |
| `{porcentaje}` del panel | Calculado en la página: `nodes / total_andalucia` |
| `{nivel}` y estado de la tabla | `level`: `green` → holgado, `orange` → cargado, `red` → saturado, `nodata` → sin datos |
| `{hora}` | `generated_at` en hora local (Europe/Madrid) |
| Geometría y punto de cada burbuja | GeoJSON estático del portal (CNIG/IGN simplificado, < 50 KB). Sin peticiones a terceros |
| Hosts de las tarjetas y del servidor MQTT | `PROJECT_DOMAIN` |
| Radio | `LORA_REGION`, `LORA_BANDWIDTH`, `LORA_SPREAD_FACTOR`, `LORA_CODING_RATE`, `LORA_FREQUENCY_SLOT`, `LORA_HOP_LIMIT`, `PRIMARY_CHANNEL`. `{LORA_FREQUENCY_MHZ}` es un valor derivado del slot en `config/proyecto.php` (869,618 MHz para el slot 4), no una variable nueva del `.env` |
| `{MQTT_TOPIC_ROOT}` | Configuración global; valor `msh/EU_868` |
| Contacto | `PROJECT_CONTACT` |
| Bloque de autoría y línea del pie | `config/autoria.php` |
| Textos fijos | `resources/contenido/inicio.md` |

---
> Creado: 2026-10-07 · Última revisión: 2026-10-07
