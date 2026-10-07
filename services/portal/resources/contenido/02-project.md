# 02 · El proyecto

> `/proyecto` · Explicar qué es el proyecto, para qué sirve, su foco en Cádiz y Andalucía y su relación con el resto de la malla · `../01-structure-home.md`

## SEO

- **Título:** `El proyecto · {PROJECT_NAME}` (33 caracteres)
- **Descripción:** `Qué es {PROJECT_NAME}: la puerta de entrada a la malla de radio LoRa de Cádiz y Andalucía, con mapas, estadísticas y avisos para cuidarla.` (143)

## Estructura

| # | Sección | Componente (`DESIGN.md`) |
|---|---|---|
| 1 | H1 y entradilla | Tipografía H1 + Texto |
| 2 | Qué es la malla | Texto corrido (H2 + Texto, línea máx. 68 caracteres) |
| 3 | Qué encontrarás aquí | Lista con enlaces en `enlace` |
| 4 | Para qué | Texto corrido con lista |
| 5 | Cádiz, y toda Andalucía | Texto corrido |
| 6 | De dónde salen los datos | Texto corrido con lista |
| 7 | Relación con otras comunidades | Texto corrido |
| 8 | Sin ánimo de lucro | Texto corrido con enlaces |
| 9 | Llamada a la acción | Botones: primario "Configura tu nodo", secundario "Conecta tu gateway" |

## Borrador del texto

Cada `###` es un H2 de la página.

**H1:** El proyecto

{PROJECT_NAME} es la puerta de entrada a la malla de radio LoRa de Cádiz y del resto de Andalucía. Reúne en un solo sitio lo que pasa en la red: qué nodos hay, cómo de cargado va el canal, qué nodos tienen problemas y cómo puedes sumarte.

### Qué es la malla

Una red de pequeños equipos de radio, los nodos, que se pasan mensajes unos a otros sin internet ni cobertura móvil. Los mensajes saltan de nodo en nodo hasta llegar a su destino. Cada nodo es de una persona que lo ha puesto en su casa, en su coche o en un punto alto, y entre todos cubren el territorio. Funciona en la banda de uso libre de 868 MHz, con muy poca potencia.

### Qué encontrarás aquí

- **MeshView:** el tráfico de la malla en directo, paquete a paquete, con sus rutas y quién oye a quién.
- **PotatoMesh:** mapa y chat de los canales públicos, con la ficha de cada nodo.
- **Mapa por provincias** (en la [portada](/)): cuántos nodos hay y cómo de cargado va el canal en cada provincia.
- **[Rankings](/rankings):** qué nodos consumen más red, cuáles están en peligro y quién aporta más.
- **[Alertas](/alertas):** los problemas que detecta el sistema, con su riesgo y a quién afectan.
- **[Bots](/bots)** de Telegram y Discord que llevan esas alertas a tu grupo o a tu canal.
- **[API pública](/api)** para quien quiera usar los datos.
- **Guías** para [configurar tu nodo](/configura-tu-nodo) y [conectar tu gateway](/conecta-tu-gateway).

### Para qué

La malla es de todos, y un solo nodo mal configurado puede entorpecerla al resto. Este proyecto quiere:

- Ver cómo está la red de un vistazo.
- Detectar a tiempo lo que la pone en riesgo: un repetidor con la batería baja, un nodo que se reinicia sin parar, un canal saturado, spam o configuraciones que la degradan.
- Ayudar a corregirlo, con guías claras y avisos que llegan a quien puede hacer algo.
- Dar a la comunidad un punto de entrada con información, herramientas y avisos.

### Cádiz, y toda Andalucía

El proyecto nace en Cádiz y se centra en ella, pero recoge y muestra la malla de las ocho provincias andaluzas.

### De dónde salen los datos

- Los nodos con internet, los gateways, suben a nuestro servidor lo que oyen por radio. Los gestionan personas voluntarias, cada una con su propio usuario.
- Solo se sube lo que cada nodo permite: si un nodo no tiene activado "OK to MQTT", sus paquetes no se suben.
- Solo se aceptan los canales públicos de una lista cerrada. Los mensajes directos y los canales privados no se ven.
- Por eso las cifras son siempre un mínimo: hay nodos que no llegan a ningún gateway o que no permiten subir sus datos.
- Nada vuelve de internet a la radio: el servidor solo recibe.

### Relación con otras comunidades

{PROJECT_NAME} es un proyecto independiente: no depende de otras comunidades ni de sus servidores, y nuestro servidor no intercambia datos con ellos. La única excepción es PotatoMesh, que además muestra datos públicos de instancias vecinas: los leemos de sus webs públicas y no les enviamos nada.

La radio sí es compartida. Recomendamos la misma configuración que usa la mayor parte de la malla en España, así que tu nodo se comunica con los del resto del país. Y cualquiera, sea de la comunidad que sea, puede usar nuestros datos a través de la [API pública](/api).

### Sin ánimo de lucro

Es un proyecto personal de Raúl Caro Pastorino, sin ánimo de lucro y sin cookies ni rastreadores en sus páginas públicas. Más en [Quién lo impulsa](/quien-lo-impulsa) y [Cómo se gestiona](/como-se-gestiona).

No es un servicio de emergencias: ni la malla ni estas webs garantizan que un mensaje llegue.

**Botones:** [Configura tu nodo](/configura-tu-nodo) (primario) · [Conecta tu gateway](/conecta-tu-gateway) (secundario)

## Datos dinámicos y configuración

| Dato | Origen |
|---|---|
| Nombre | `PROJECT_NAME` |
| Enlaces a MeshView y PotatoMesh | `https://meshview.{PROJECT_DOMAIN}`, `https://potato.{PROJECT_DOMAIN}` |
| Texto | `resources/contenido/overview.md`. Sin datos de la API |

---
> Creado: 2026-10-07 · Última revisión: 2026-10-07
