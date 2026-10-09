# Registro de Personalizaciones sobre Software de Terceros

Este documento registra todas las adaptaciones, parches y plantillas personalizadas que se aplican sobre componentes o imágenes externas de terceros (como MeshView o PotatoMesh).

## Protocolo Obligatorio de Actualización

Al actualizar la versión de una imagen de terceros (ej. subir de etiqueta de versión en `compose.yaml`):
1. **Revisar este registro**: Comprobar cada personalización catalogada para la pieza afectada.
2. **Inspeccionar diferencias**: Extraer los archivos equivalentes de la nueva imagen y verificar si la funcionalidad personalizada sigue requiriendo adaptación o si ya ha sido incorporada upstream.
3. **Reaplicar parches**: Si sigue siendo necesaria, adaptar los archivos en el repositorio local bajo `integrations/<pieza>/` y verificar el montaje.
4. **Validación funcional**: Probar en navegador y API que las adaptaciones funcionan correctamente antes de dar por concluida la actualización.
5. **Actualizar este registro**: Anotar la fecha y la nueva versión en la tabla correspondiente.

---

## Catálogo de Personalizaciones

| ID | Pieza | Versión base | Archivos afectados | Descripción y motivo | Estado |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `CUST-01` | **MeshView** | `3.0.8` | `integrations/meshview/templates/chat.html`<br>`integrations/meshview/templates/net.html`<br>`integrations/meshview/compose.yaml` | **Horario 24 horas y zona horaria peninsular (`Europe/Madrid`) con fecha europea (`DD/MM/YYYY`)**: upstream viene con `hour12: true` y formato americano `MM/DD/YYYY`. Se montan las plantillas personalizadas como volúmenes de solo lectura (`:ro`) en el contenedor. | Activo |
| `CUST-02` | **PotatoMesh** | `0.7.5` | `integrations/potatomesh/compose.yaml`<br>`integrations/potatomesh/.env.example` | **Desactivación obligatoria de federación (`FEDERATION=0`)**: PotatoMesh activa por defecto la federación peer-to-peer si no se declara la variable. Se asegura `FEDERATION=0` tanto en el bloque `environment` del `compose.yaml` como en `.env` para garantizar aislamiento absoluto sin anuncios salientes. | Activo |
| `CUST-03` | **MeshConfig** | `658f461` | `integrations/meshconfig/compose.yaml`<br>`integrations/meshconfig/custom/*`<br>`integrations/meshconfig/configs/*` | **Interfaz visual Material según DESIGN.md, asistente en 4 pasos, estándar SFNarrow y QR oficial**: la imagen base upstream se construye sin alterar a partir del repositorio oficial anclado al commit `658f461`. Las personalizaciones (diseño accesible, selección de provincias andaluzas, generador de QR dinámico y buenas prácticas de red) se montan como volúmenes de solo lectura (`:ro`). | Activo |
| `CUST-04` | **PotatoMesh** | `0.7.5` | `integrations/potatomesh/views/layouts/app.erb`<br>`integrations/potatomesh/compose.yaml`<br>`integrations/potatomesh/.env.example` | **Indicador visual de retención en la barra de navegación (navbar) y menú móvil**: PotatoMesh no expone de serie el período de retención a los usuarios del mapa. Se monta la plantilla `app.erb` como volumen de solo lectura con un badge configurable (`RETENTION_BADGE`, por defecto `Nodos: 15d · Trazas: 5d`) tanto en el navbar principal de escritorio (`.site-nav`) como en el menú desplegable móvil (`.mobile-nav`), indicando claramente el margen de histórico visible sin alterar la lógica de negocio ni dependencias externas. | Activo |

---

## Detalle Técnico de las Modificaciones

### CUST-01 — Formato de Fecha y Hora en MeshView (Chat y Net)

* **Problema upstream**: MeshView utiliza en el cliente JavaScript `date.toLocaleTimeString([], { hour12: true })` y concatena `(month+1)/date/year`. En España esto produce confusión al mostrar `12:07:08 a. m.` para la medianoche (confundiéndose con las 12 del mediodía) y fechas como `10/08/2026` para el 8 de octubre (pareciendo 10 de agosto).
* **Solución aplicada**:
  En `chat.html` y `net.html`:
  ```javascript
  const formattedTime = date.toLocaleTimeString("es-ES", {
      timeZone: "Europe/Madrid",
      hour: "2-digit",
      minute: "2-digit",
      second: "2-digit",
      hour12: false
  });
  const formattedDate = date.toLocaleDateString("es-ES", {
      timeZone: "Europe/Madrid",
      day: "2-digit",
      month: "2-digit",
      year: "numeric"
  });
  const formattedTimestamp = `${formattedTime} - ${formattedDate}`;
  ```
* **Montaje en `compose.yaml`**:
  ```yaml
  volumes:
    - /srv/meshview/datos/config:/etc/meshview:ro
    - /srv/meshview/datos/logs:/var/log/meshview
    - ./templates/chat.html:/app/meshview/templates/chat.html:ro
    - ./templates/net.html:/app/meshview/templates/net.html:ro
  ```

---

### CUST-02 — Desactivación de Federación en PotatoMesh

* **Problema upstream**: PotatoMesh activa por defecto la federación (`ENV.fetch("FEDERATION", "1")`), anunciándose a otros peers remotos y realizando crawling periódico hacia instancias externas en internet.
* **Solución aplicada**:
  En `compose.yaml`:
  ```yaml
  environment:
    - FEDERATION=0
  ```
  Y en `.env` de producción (`/srv/potatomesh/.env`):
  ```ini
  FEDERATION=0
  ```

---

### CUST-03 — Adaptación Visual, SFNarrow y Buenas Prácticas en MeshConfig

* **Problema upstream**: `pdxlocations/meshconfig` upstream presenta una interfaz densa de desarrollador sin asistente guiado, orientada a presets norteamericanos (LongFast / BayMesh) con saltos elevados e intervalos de telemetría agresivos no adaptados a la normativa europea ni a las buenas prácticas de Andalucía Mesh.
* **Solución aplicada**:
  1. Construcción directa de la imagen Docker upstream a partir de `https://github.com/pdxlocations/meshconfig.git` anclada estrictamente al commit `658f46111da28d4444e45ab3eacd89fd53cc2ab3`.
  2. Montaje de plantillas visuales limpias ([`DESIGN.md`](DESIGN.md)) con modo claro y oscuro, tipografía `Space Grotesk` y asistente guiado en 4 pasos.
  3. Integración del preset estándar **SFNarrow** por defecto (EU_868, BW 62.5 kHz, SF 7, CR 5, Slot 4), selector de potencia TX (27 dBm y 8 dBm para módulos Ebyte E22P con PA), selector provincial normalizado sin acentos (`Cadiz`, `Sevilla`, etc.), buenas prácticas oficiales (`positionBroadcastSmartEnabled: false`, `positionFlags: 0`, NodeInfo cada 72h, posición 6h/72h, saltos 3/4) y generador de enlaces y códigos QR oficiales `https://meshtastic.org/e/#...`.
  4. Montaje en `compose.yaml`:
  ```yaml
  volumes:
    - ./configs:/usr/share/nginx/html/configs:ro
    - ./custom/index.html:/usr/share/nginx/html/index.html:ro
    - ./custom/styles.css:/usr/share/nginx/html/styles.css:ro
    - ./custom/configurador.js:/usr/share/nginx/html/configurador.js:ro
  ```

---

### CUST-04 — Indicador de Retención en Navbar de PotatoMesh

* **Problema upstream**: PotatoMesh no indica en su interfaz el límite temporal de los datos cargados en el mapa y tablas, lo que genera incertidumbre sobre la frescura de los enlaces o la razón por la que ciertos nodos antiguos ya no aparecen.
* **Solución aplicada**:
  1. Extracción de la plantilla de layout oficial `/app/views/layouts/app.erb` de la versión 0.7.5.
  2. Inserción de la variable configurable `RETENTION_BADGE` (por defecto `"Nodos: 15d · Trazas: 5d"`).
  3. Renderizado de un badge accesible y no intrusivo dentro de `.site-nav` (escritorio) y en el panel desplegable de `.mobile-nav` (móviles).
  4. Montaje de la plantilla en `compose.yaml`:
  ```yaml
  volumes:
    - /srv/potatomesh/datos:/app/.local/share/potato-mesh
    - /srv/potatomesh/config:/app/.config/potato-mesh
    - ./pages:/app/pages:ro
    - ./views/layouts/app.erb:/app/views/layouts/app.erb:ro
  ```

---

> Creado: 2026-10-08 · Última revisión: 2026-10-09
