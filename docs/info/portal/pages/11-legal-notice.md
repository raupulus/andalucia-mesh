# 11 · Aviso legal

> `/legal/aviso-legal` · Identificar al titular y fijar las condiciones de uso, la exclusión de responsabilidad y la propiedad intelectual del portal y sus servicios · `../10-legal-privacy.md`

## SEO

- **Título:** `Aviso legal · {PROJECT_NAME}` (33 caracteres)
- **Descripción:** `Titular, condiciones de uso, responsabilidad y propiedad intelectual de {PROJECT_NAME}, proyecto personal y sin ánimo de lucro.` (132)

## Estructura

| # | Sección | Componente (`DESIGN.md`) |
|---|---|---|
| 1 | H1 y fecha de actualización | Tipografía H1 + texto secundario |
| 2 | Secciones 1 a 8 | Texto corrido (H2 + Texto, línea máx. 68 caracteres) |
| 3 | Enlaces a privacidad y cookies | Texto con enlaces en `enlace` |

Enlazada desde el pie de todas las páginas (RF-PO-LG-1).

## Borrador del texto

> **Borrador orientativo.** Este texto no sustituye la revisión de un profesional del derecho. Revisarlo antes de publicar.

Cada `###` es un H2 de la página.

**H1:** Aviso legal

Última actualización: [fecha de publicación]

### 1. Titular

Este sitio web, `{PROJECT_DOMAIN}`, y los servicios publicados en sus subdominios (PotatoMesh, MeshView y el servidor MQTT) son un proyecto personal y sin ánimo de lucro de Raúl Caro Pastorino (@raupulus).

Contacto: {PROJECT_CONTACT}

El proyecto no desarrolla ninguna actividad económica: no cobra por nada ni acepta donaciones.

### 2. Objeto

Informar sobre la malla de radio LoRa de Cádiz y Andalucía y ofrecer herramientas para verla y cuidarla: mapas, estadísticas, rankings, alertas, bots, una API pública y guías de configuración.

### 3. Condiciones de uso

- El uso es libre y gratuito, sin registro. Solo el panel de administración es privado.
- Te comprometes a usar el sitio de buena fe: no intentar entrar en zonas privadas, no superar los límites de la API ni sobrecargar el servidor, y no usar los datos para localizar, molestar o perjudicar a nadie.
- Las credenciales de cada gateway son personales y no se pueden compartir. Podemos dar de baja un gateway que envíe datos falsos o que perjudique a la malla.
- Podemos cambiar, suspender o cerrar cualquier servicio en cualquier momento.

### 4. Exclusión de responsabilidad

- **No es un servicio de emergencias.** No se garantiza que un mensaje llegue a su destino ni que la red o las webs estén disponibles. En una emergencia, llama al 112.
- **Datos tal cual.** Lo que se muestra puede estar incompleto, ser aproximado o contener errores: las posiciones tienen una precisión reducida y hay nodos que no aparecen.
- **Avisos orientativos.** Las alertas se generan de forma automática. Son una ayuda para mantener la red y no implican culpa ni mala fe de quien opera el nodo.
- **Normativa de radio.** Cada persona es responsable de que su equipo cumpla la normativa radioeléctrica aplicable a la banda de 868 MHz, incluida la potencia y el ciclo de trabajo.
- **Mensajes de los canales públicos.** Los escriben sus autores. El proyecto solo los muestra tal y como se emitieron por radio y no responde de su contenido.
- **Enlaces externos.** No respondemos del contenido de los sitios de terceros a los que se enlaza.

### 5. Proyecto independiente

{PROJECT_NAME} es un proyecto independiente. No está afiliado ni respaldado por los proyectos de software ni por los fabricantes de los equipos que usa, ni por otras comunidades de la malla.

### 6. Propiedad intelectual

- Los textos, el diseño y el logo de este sitio son de Raúl Caro Pastorino. Los textos se publican bajo licencia CC BY 4.0, igual que los datos de la API; el diseño y el logo no se licencian.
- PotatoMesh se usa bajo su licencia Apache-2.0, y MeshView, bajo la licencia indicada en su repositorio. Enlace a cada proyecto.
- El resto del software libre con el que funciona el proyecto se usa según sus propias licencias: Laravel y Filament (MIT), Nginx (BSD-2-Clause), Mosquitto (EPL-2.0/EDL-1.0), PostgreSQL (licencia PostgreSQL), TimescaleDB (Timescale License, edición Community), MeshView (AGPL-3.0) y PotatoMesh (Apache-2.0), con enlace a cada proyecto. Los servicios propios que usan las definiciones oficiales del protocolo se publican con licencia GPL-3.0.
- Límites provinciales: © Instituto Geográfico Nacional (CNIG), bajo CC BY 4.0, simplificados para el mapa.
- Los mensajes y los nombres de los nodos pertenecen a quienes los emiten.

### 7. Privacidad y cookies

Cómo tratamos los datos: [política de privacidad](/legal/privacidad). Qué guardamos en tu navegador: [política de cookies](/legal/cookies).

### 8. Ley aplicable

Este aviso se rige por la legislación española.

## Datos dinámicos y configuración

| Dato | Origen |
|---|---|
| Nombre, dominio y contacto | `PROJECT_NAME`, `PROJECT_DOMAIN`, `PROJECT_CONTACT` |
| Titular | `config/autoria.php` (nombre y nick) |
| Fecha de actualización | Se escribe al publicar cada versión del texto |
| Enlaces a PotatoMesh y MeshView | Repositorios de cada proyecto (`../../potatomesh/README.md`, `../../meshview/README.md`) |
| Texto | `resources/contenido/legal/aviso-legal.md` |

## Supuestos aplicados

- Sin domicilio ni NIF mientras no haya actividad económica (LSSI-CE aplicada de forma limitada).
- Textos propios bajo CC BY 4.0; logo con todos los derechos reservados.
- La sección de software de terceros nombra cada proyecto con su licencia y enlace: es atribución exigida por las licencias, no uso de marca (la regla de marcas no aplica a créditos).

---
> Creado: 2026-10-07 · Última revisión: 2026-10-07
