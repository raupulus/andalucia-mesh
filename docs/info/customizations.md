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

> Creado: 2026-10-08 · Última revisión: 2026-10-08
