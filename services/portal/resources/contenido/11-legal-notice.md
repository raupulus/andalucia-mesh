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

Última actualización: 8 de octubre de 2026

### 1. Titular

Este sitio web, `{PROJECT_DOMAIN}`, y los servicios publicados en sus subdominios (PotatoMesh, MeshView y el servidor MQTT) son un proyecto personal y sin ánimo de lucro de Raúl Caro Pastorino (@raupulus).

Contacto: {PROJECT_CONTACT}

El proyecto no desarrolla ninguna actividad económica: no comercializa productos ni servicios, no cobra suscripciones ni acepta donaciones económicas.

### 2. Objeto

Informar sobre la malla de radio LoRa de Cádiz y Andalucía y ofrecer herramientas abiertas para observarla y mantenerla: mapas cartográficos, métricas de calidad de enlace, estadísticas provinciales, rankings de actividad, catálogo de alertas operativas, bots de notificación, una API pública y guías prácticas de configuración de equipos.

### 3. Condiciones de uso

- El uso del portal y de sus consultas públicas es libre y gratuito para toda la comunidad, sin requerir registro previo de usuario. El acceso a la consola de administración `/admin` está restringido exclusivamente a tareas de soporte y mantenimiento técnico de los operadores.
- Te comprometes a hacer un uso diligente y de buena fe: no intentar vulnerar zonas privadas del sistema, respetar los límites de frecuencia de peticiones de la API pública para evitar la sobrecarga de la infraestructura, y abstenerte de utilizar la información para geolocalizar, hostigar o menoscabar los derechos de ningún usuario u operador.
- Las credenciales asignadas para la interconexión de gateways son personales e intransferibles. La administración se reserva la facultad de suspender o dar de baja el acceso de cualquier gateway que inyecte telemetría falseada o dañina para la estabilidad de la malla.
- Dado el carácter voluntario y no retribuido del servicio, podemos actualizar, suspender o cesar cualquiera de las herramientas publicadas en cualquier instante.

### 4. Exclusión de responsabilidad

- **No es un servicio de emergencias.** Las redes de radio comunitaria experimental y este portal web no sustituyen en ningún caso a los canales oficiales de socorro. No se garantiza la entrega de paquetes ni la disponibilidad ininterrumpida de las transmisiones. Ante situaciones de urgencia vital o emergencias, contacta de inmediato con el número de emergencias 112.
- **Datos tal cual.** La información se presenta según se recibe por ondas de radio: puede estar incompleta, diferir temporalmente de la realidad o contener imprecisiones derivadas de la propagación o de configuraciones con precisión geográfica deliberadamente atenuada.
- **Avisos orientativos.** Las alertas se generan de forma automatizada mediante reglas analíticas para facilitar la detección de incidencias comunes en la red; su emisión es meramente orientativa y no prejuzga la intención ni la pericia del operador del nodo.
- **Normativa de telecomunicaciones.** Cada titular de una estación de radio es responsable exclusivo de que su dispositivo opere dentro de los parámetros legalmente autorizados para la banda ISM de 868 MHz en España y la Unión Europea, respetando las limitaciones de potencia radiada (PIRE) y ciclo de trabajo (*duty cycle*).
- **Mensajes de los canales públicos.** Los mensajes transmitidos en canales comunitarios reflejan exclusivamente la opinión y expresión de sus emisores. El sistema se limita a procesar de forma automática y transparente paquetes transmitidos por radiofrecuencia con clave abierta por defecto.
- **Enlaces externos.** No nos responsabilizamos de los contenidos, servicios ni políticas de privacidad de plataformas y sitios web externos enlazados desde nuestras páginas.

### 5. Proyecto independiente

{PROJECT_NAME} es una iniciativa ciudadana e independiente sin vinculación societaria, laboral ni de patrocinio con el proyecto oficial Meshtastic, con fabricantes de hardware ni con otras asociaciones o redes en malla.

### 6. Propiedad intelectual y licencias

- **Contenidos y marca:** Los textos explicativos de este portal se publican bajo licencia Creative Commons Reconocimiento 4.0 Internacional ([CC BY 4.0](https://creativecommons.org/licenses/by/4.0/deed.es)), al igual que los datos servidos a través de la API pública. El diseño visual, la maquetación y el logotipo de {PROJECT_NAME} son titularidad exclusiva de Raúl Caro Pastorino y quedan reservados todos los derechos.
- **Cartografía oficial:** Los límites provinciales y contornos geográficos mostrados en los mapas proceden de las delimitaciones cartográficas del Instituto Geográfico Nacional ([CNIG/IGN](https://www.ign.es)), reutilizados bajo licencia [CC BY 4.0](https://creativecommons.org/licenses/by/4.0/deed.es) y simplificados técnicamente para optimizar su representación web.
- **Software libre de terceros:**
  - [PotatoMesh](https://github.com/cameronfabbri/PotatoMesh): mapa web ágil de visualización de nodos LoRa, publicado bajo licencia Apache 2.0.
  - [MeshView](https://github.com/pdxlocations/MeshView): analizador y visor topológico de redes en malla, publicado bajo licencia AGPL-3.0.
  - [meshconfig](https://github.com/pdxlocations/meshconfig): configurador web de nodos Meshtastic, publicado bajo licencia GNU GPLv3.
  - [Laravel](https://laravel.com) y [Filament](https://filamentphp.com): entorno de desarrollo web y panel de administración, bajo licencia MIT.
  - [Eclipse Mosquitto](https://mosquitto.org): servidor de mensajería MQTT, bajo licencias EPL-2.0 y EDL-1.0.
  - [PostgreSQL](https://www.postgresql.org) y [TimescaleDB](https://www.timescale.com): motor de base de datos relacional y series temporales (licencias PostgreSQL y Timescale Community Edition).
  - [Nginx](https://nginx.org): servidor proxy inverso y terminador TLS, bajo licencia BSD de 2 cláusulas.
- **Software y servicios propios:** El código fuente del proyecto y sus servicios asociados se licencian bajo la licencia [GNU Affero General Public License v3.0 (AGPL-3.0)](https://www.gnu.org/licenses/agpl-3.0.html).
- **Emisiones y mensajes en malla:** Los nombres públicos de los nodos, sus identificadores y los mensajes transmitidos en canales abiertos pertenecen a sus respectivos emisores y operadores.

### 7. Privacidad y cookies

Para conocer en detalle el tratamiento de información técnica y el ejercicio de tus derechos, consulta nuestra [política de privacidad](/legal/privacidad) y nuestra [política de cookies](/legal/cookies).

### 8. Ley aplicable y jurisdicción

Este aviso legal y las relaciones entre el portal y sus usuarios se rigen por la legislación civil y administrativa española.

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
> Creado: 2026-10-07 · Última revisión: 2026-10-09
