# 04 · Cómo se gestiona

> `/como-se-gestiona` · Contar con transparencia quién administra el proyecto, cómo se decide, dónde funciona, cómo se protege la malla y quién paga · `../01-structure-home.md`

## SEO

- **Título:** `Cómo se gestiona · {PROJECT_NAME}` (38 caracteres)
- **Descripción:** `Quién administra {PROJECT_NAME}, dónde está el servidor, cómo se toman las decisiones, quién paga y cómo se protegen la malla y tus datos.` (143)

## Estructura

| # | Sección | Componente (`DESIGN.md`) |
|---|---|---|
| 1 | H1 y entradilla | Tipografía H1 + Texto |
| 2 | Quién lo administra | Texto corrido |
| 3 | Cómo se toman las decisiones | Texto corrido |
| 4 | Dónde funciona | Texto corrido con lista |
| 5 | Cómo se protege la malla | Texto corrido con lista |
| 6 | Disponibilidad | Texto corrido |
| 7 | Costes y donaciones | Texto corrido |
| 8 | Tus datos | Texto corrido con enlace |

## Borrador del texto

Cada `###` es un H2 de la página.

**H1:** Cómo se gestiona

Un proyecto pequeño, llevado por una persona y con las cuentas claras. Esto es lo que hay detrás.

### Quién lo administra

Raúl Caro Pastorino administra todo el sistema: el servidor, los servicios, las altas de gateways, los bots y las webs. Los gateways son de personas voluntarias que suben datos con su propio usuario. El proyecto no gestiona los nodos de nadie: cada nodo es responsabilidad de quien lo tiene.

### Cómo se toman las decisiones

Las decisiones las toma Raúl como impulsor y responsable del proyecto. Las propuestas, quejas y avisos de errores son bienvenidos en [{PROJECT_CONTACT}](mailto:{PROJECT_CONTACT}). Los routers nuevos se coordinan con el proyecto antes de activarlos, porque afectan a toda la malla.

### Dónde funciona

- Todo funciona en un único servidor virtual, en un centro de datos de {HOSTING_LOCATION}. El proveedor figura en la [política de privacidad](/legal/privacidad).
- Cada servicio funciona por separado: si uno falla, los demás siguen.
- Combina software libre de la comunidad, como MeshView y PotatoMesh, con desarrollos propios: este portal, el procesado de los datos, el detector de alertas, los bots y la API.
- Las versiones del software de terceros se fijan y solo se actualizan después de comprobar que todo sigue funcionando.

### Cómo se protege la malla

- El servidor solo recibe. Nada de lo que llega por internet vuelve a la radio, aunque un gateway tenga el downlink activado por error.
- Cada gateway tiene su propio usuario y solo puede publicar en su nombre.
- Solo se aceptan los canales públicos de una lista cerrada.
- Un sistema de alertas vigila la red y avisa de baterías bajas, reinicios en bucle, gateways caídos, spam y otros problemas.

### Disponibilidad

No hay garantía de servicio. Todo depende de un único servidor: si se cae, se caen a la vez las webs, la API y los bots. Tampoco hay página de estado pública, porque estaría en ese mismo servidor y caería con él. El estado de cada servicio se vigila desde un panel privado de administración.

### Costes y donaciones

Los costes del servidor y del dominio los asume Raúl. Por ahora no se aceptan donaciones; se valorará cuando termine el desarrollo.

### Tus datos

Qué datos se tratan, durante cuánto tiempo y cómo no aparecer: [política de privacidad](/legal/privacidad).

## Datos dinámicos y configuración

| Dato | Origen |
|---|---|
| Contacto | `PROJECT_CONTACT` |
| Texto | `resources/contenido/como-se-gestiona.md`. Sin datos de la API |


## Supuestos aplicados

- Código del proyecto: no se enlaza mientras no se decida publicarlo.

---
> Creado: 2026-10-07 · Última revisión: 2026-10-07
