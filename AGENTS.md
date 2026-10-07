# Protocolo Operativo del Proyecto

Este documento define las normas operativas, la estructura del proyecto y el protocolo de documentación obligatorio para todos los colaboradores y agentes que trabajen en este repositorio.

## Estructura del Repositorio

Árbol verificado de directorios y ficheros del proyecto:

```text
.
├── .gitignore
├── AGENTS.md
├── CLAUDE.md
├── README.md
├── infrastructure/              host, nginx, postgresql, common/.env.example, deploy.sh, check-compose.sh
├── integrations/
│   ├── mosquitto/
│   ├── meshview/
│   └── potatomesh/
├── services/
│   ├── adaptador-potato/
│   ├── bot-discord/
│   ├── bot-telegram/
│   ├── chat-ws/
│   ├── detector-alertas/
│   ├── ingesta/
│   ├── portal/
│   ├── sync-peers/
│   └── webhooks/
└── docs/
    ├── analisis-tecnico/        (enlace local, ignorado por git: NO cargar, ver regla 14)
    ├── apis/
    │   └── README.md
    ├── deploys/
    │   └── README.md
    ├── future/
    │   └── README.md
    └── info/
        ├── README.md            índice maestro
        ├── architecture-map.md  mapa conceptual
        ├── overview.md          visión general
        ├── integration.md       contrato común entre piezas
        ├── deployment.md        requisitos, fases y orden de despliegue
        ├── decisiones-tecnicas.md
        ├── DESIGN.md
        ├── COMPONENTS.md
        ├── commands.md
        ├── _MODULE_TEMPLATE.md
        ├── apis/
        ├── infrastructure/      README + 01-server, 02-postgresql, 03-nginx-dns, 04-operations
        ├── mosquitto/
        ├── meshview/
        ├── potatomesh/          README + adaptador-potato, sync-peers
        ├── ingesta/             README + módulos 01–06
        ├── portal/              README + módulos 01–14 + pages/
        ├── detector-alertas/    README + módulos 01–03
        ├── bots-webhooks/       README + 01-bot-telegram, 02-bot-discord, 03-webhooks
        └── chat-ws/
```

*(Nota: los directorios locales efímeros `docs/planning/` y `docs/auditorias/` y el enlace local `docs/analisis-tecnico/` están excluidos del control de versiones mediante `.gitignore` y ningún fichero versionado debe enlazarlos).*

---

## Documentación

### Jerarquía de Verdad
1. **Código fuente real** (máxima autoridad sobre el estado del sistema).
2. **`docs/info/`** (documentación técnica VIVA y canónica).
3. **`AGENTS.md`** (normas operativas y mapa de arquitectura).
4. El resto.

> [!IMPORTANT]
> `docs/planning/`, `docs/future/` y `docs/auditorias/` **NUNCA** son fuente de verdad del estado actual del sistema.

### Cómo leer la documentación (obligatorio)

El repositorio contiene muchas aplicaciones independientes. **No se carga toda la documentación**: se lee solo lo que necesita la tarea.

1. **Siempre, al empezar:** este `AGENTS.md`. Nada más hasta saber en qué pieza se trabaja.
2. **Para ubicarse** (solo si hace falta): [`docs/info/README.md`](docs/info/README.md) (índice) o [`docs/info/architecture-map.md`](docs/info/architecture-map.md) (mapa).
3. **Para trabajar en una pieza:** solo sus documentos (tabla de abajo). Si se toca algo que otra pieza consume (MQTT, socket, vistas `api_*`, API, variables comunes, salud), se añade la sección correspondiente de [`docs/info/integration.md`](docs/info/integration.md), no el documento entero ni los de las otras piezas.
4. **Nunca de rutina:** los documentos de otras piezas, `docs/future/`, `docs/analisis-tecnico/` (regla 14), `archived/`.

### Qué leer según la tarea

| Tarea | Leer | No leer |
|---|---|---|
| Servidor, Docker, redes, PostgreSQL, Nginx, DNS, despliegue | `docs/info/infrastructure/` (solo el módulo afectado) | Servicios |
| Broker MQTT, usuarios, ACL | `docs/info/mosquitto/README.md` | Resto |
| MeshView | `docs/info/meshview/README.md` | Resto |
| PotatoMesh (instancia) | `docs/info/potatomesh/README.md` | Módulos de adaptador y sync si no se tocan |
| `services/adaptador-potato/` | `docs/info/potatomesh/adaptador-potato.md` | Resto |
| `services/sync-peers/` | `docs/info/potatomesh/sync-peers.md` | Resto |
| `services/ingesta/` | `docs/info/ingesta/README.md` + el módulo 01–06 afectado | Portal, detector, bots |
| `services/portal/` (web) | `docs/info/portal/README.md` + módulo 01–10 afectado + su página en `docs/info/portal/pages/` + `DESIGN.md` y `COMPONENTS.md` si hay interfaz | Ingesta, detector, bots |
| `services/portal/` (API) | `docs/info/portal/11-public-api.md` + el módulo 12 o 13 afectado | Páginas, diseño |
| `services/portal/` (panel `/admin`) | `docs/info/portal/14-operator-panel.md` | Páginas, API |
| `services/detector-alertas/` | `docs/info/detector-alertas/README.md` + módulo 01–03 afectado | Portal, bots |
| `services/bot-telegram/`, `bot-discord/`, `webhooks/` | `docs/info/bots-webhooks/README.md` (núcleo común) + el módulo de esa aplicación | Las otras dos aplicaciones |
| `services/chat-ws/` | `docs/info/chat-ws/README.md` | Resto |
| Contrato entre piezas | La sección de `docs/info/integration.md` que cambia + los documentos de las piezas que lo consumen | El resto de secciones |
| Visión general o planificación | `docs/info/overview.md`, `docs/info/deployment.md`, `docs/info/business-rules.md` | Documentos de pieza |
| Cualquier cambio de comportamiento | La sección de `docs/info/business-rules.md` que lo afecte | El resto de secciones |

### Índice de `docs/info/` (para qué es cada archivo)

| Archivo | Para qué |
|---|---|
| [`README.md`](docs/info/README.md) | Índice maestro: piezas, enlaces y estado |
| [`business-rules.md`](docs/info/business-rules.md) | **Objetivos y reglas de negocio obligatorias** (regla 21). Se consulta la sección que afecte a la tarea |
| [`architecture-map.md`](docs/info/architecture-map.md) | Mapa conceptual: qué hay montado y cómo se comunica |
| [`overview.md`](docs/info/overview.md) | Visión general: qué se construye, decisiones, descartes, recursos y limitaciones |
| [`integration.md`](docs/info/integration.md) | Contrato común entre piezas: hosts, redes, MQTT, flujo `decoded`, socket, bases, vistas, API, salud, `.env` común, estructura del repositorio. Manda sobre los documentos de pieza |
| [`deployment.md`](docs/info/deployment.md) | Requisitos, fases, orden y dependencias de despliegue |
| [`decisiones-tecnicas.md`](docs/info/decisiones-tecnicas.md) | Decisiones deliberadas que no se deben «arreglar» |
| [`DESIGN.md`](docs/info/DESIGN.md) | Sistema visual del portal (solo para interfaz) |
| [`COMPONENTS.md`](docs/info/COMPONENTS.md) | Componentes de interfaz implementados (solo para interfaz) |
| [`commands.md`](docs/info/commands.md) | Comandos y scripts disponibles |
| [`_MODULE_TEMPLATE.md`](docs/info/_MODULE_TEMPLATE.md) | Plantilla para documentar un módulo implementado |
| [`apis/README.md`](docs/info/apis/README.md) | Cómo se integran APIs de terceros |
| `infrastructure/` | Servidor (`01-server`), PostgreSQL (`02-postgresql`), Nginx y DNS (`03-nginx-dns`), operación y despliegue (`04-operations`) |
| `mosquitto/`, `meshview/`, `chat-ws/` | Una ficha por pieza |
| `potatomesh/` | Instancia (`README`), `adaptador-potato.md`, `sync-peers.md` |
| `ingesta/` | Ficha + entrada y descifrado, decodificación y deduplicación, provincias y registros, persistencia y retención, vistas contrato, flujo `decoded` |
| `portal/` | Ficha + módulos web (01–10), API (11–13), panel (14) y `pages/` (contenido de cada página pública) |
| `detector-alertas/` | Ficha + motor de reglas, catálogo de reglas, socket y persistencia |
| `bots-webhooks/` | Núcleo común (`README`) + Telegram, Discord y webhooks |

### Mapa de piezas → código → documentación

| Pieza | Código | Documentación |
|---|---|---|
| Infraestructura | `infrastructure/` | `docs/info/infrastructure/` |
| Mosquitto | `integrations/mosquitto/` | `docs/info/mosquitto/` |
| MeshView | `integrations/meshview/` | `docs/info/meshview/` |
| PotatoMesh | `integrations/potatomesh/` | `docs/info/potatomesh/README.md` |
| adaptador-potato | `services/adaptador-potato/` | `docs/info/potatomesh/adaptador-potato.md` |
| sync-peers | `services/sync-peers/` | `docs/info/potatomesh/sync-peers.md` |
| ingesta | `services/ingesta/` | `docs/info/ingesta/` |
| portal | `services/portal/` | `docs/info/portal/` (+ `DESIGN.md`, `COMPONENTS.md`) |
| detector-alertas | `services/detector-alertas/` | `docs/info/detector-alertas/` |
| bot-telegram, bot-discord, webhooks | `services/<nombre>/` | `docs/info/bots-webhooks/` |
| chat-ws | `services/chat-ws/` | `docs/info/chat-ws/` |

### Reglas Permanentes
1. **Documentar es parte de la tarea**: Ninguna tarea está terminada si su documentación no se actualiza EN EL MISMO COMMIT que el código.
2. **Jerarquía de verdad sobre el estado actual**: código > `docs/info/` > `AGENTS.md` > el resto. `docs/planning/`, `docs/future/` y `docs/auditorias/` NUNCA son fuente de verdad del estado.
3. **Discrepancia entre documentación y código**: Se corrige en el commit en que se detecta, no se anota para después.
4. **Ciclo de vida de módulos**:
   - Tocas un módulo → actualizas su `.md`.
   - Creas uno → lo creas desde `_MODULE_TEMPLATE.md` y lo indexas en `docs/info/README.md` y en `AGENTS.md`.
   - Eliminas uno → borras su `.md` y lo quitas de TODOS los índices.
5. **Pie de revisión obligatorio**: Todo archivo bajo `docs/`, en cualquier subdirectorio, termina con esta línea exacta, detrás de un separador `---`:
   `> Creado: YYYY-MM-DD · Última revisión: YYYY-MM-DD`
   La fecha de creación no se toca nunca; la de revisión se actualiza en el mismo commit que el documento.
6. **Estructura de fases/módulos en planificación**: Toda fase o módulo de una planificación empieza con una descripción y termina con un checklist `- [ ]`. `[x]` significa verificado funcionando y cumpliendo. Escribir el código no marca la casilla.
7. **Naturaleza efímera de planning y auditorías**: `docs/planning/` y `docs/auditorias/` son trabajo temporal de UN desarrollador (no compartido, no general, no existe en un clon nuevo). Ciclo: crear → trabajar → verificar → promocionar lo duradero → BORRAR. `archived/` es sala de espera hasta confirmar la implementación, no archivo histórico.
8. **Promoción obligatoria antes de borrar lo efímero**:
   - Comportamiento del módulo → `docs/info/<modulo>.md`
   - Decisión deliberada que alguien querrá «arreglar» → `docs/info/decisiones-tecnicas.md`
   - Trampa duradera → tabla de trampas de `AGENTS.md`
   - Cambio de arquitectura, rutas o comandos → `AGENTS.md`
   - Idea aplazada → `docs/future/`
   - Regresión → un test automatizado, no un documento
9. **Aislamiento en git**: Nada versionado puede enlazar a `docs/planning/` ni a `docs/auditorias/`. No se deben sacar del `.gitignore` por conveniencia puntual.
10. **Lectura dirigida**: Al trabajar en un módulo lees SOLO su `.md` (tabla «Qué leer según la tarea»). Si tocas frontend añades `DESIGN.md` y `COMPONENTS.md`. Si tocas una API de terceros añades `docs/apis/<api>/` en este orden: `README.md` → `00-fundamentos.md` + `ERRATAS.md` + `LIMITACIONES.md` → solo el dominio que necesites. No leas el resto de `docs/info/`, ni `docs/future/`, ni `archived/`, ni `src/`.
11. **Verificación real de APIs externas**: Nunca configures nada a partir de la especificación oficial de una API externa sin verificarlo con una petición real. Lo no comprobado se marca como no verificado (`⚠️ sin verificar`).
12. **Convención de idiomas**:
   - **En inglés:** todo lo que es estructura de código: nombres de vistas, tablas, columnas, variables (incluidas las de entorno), funciones, clases, métodos, claves JSON, topics, rutas, archivos y directorios de código, y mensajes de log.
   - **En español:** nombres de contenedores (y de su directorio en `services/` o `integrations/`), documentación, comentarios y textos de usuario.
   - Excepción: los campos de una API de terceros se leen tal como los envía.
13. **Atribución de autoría**: Nick `@raupulus`, email `public@raupulus.dev`. Sin firmas de agentes en commits, PRs ni documentación (nada de `Co-Authored-By`, «Generated with…» ni identificadores de sesión).
14. **`docs/analisis-tecnico/` no se carga**: es un enlace local (ignorado por git) al análisis técnico y la planificación original. Contexto histórico solamente: **no se lee al abrir sesión ni de rutina** (economía de tokens). Solo se consulta si el usuario lo pide o si `docs/info/` no responde una duda concreta, y entonces se lee únicamente el archivo necesario. Lo que se rescate de ahí se promociona a `docs/info/`; nada versionado lo enlaza.
15. **Lectura al empezar una sesión**: solo `AGENTS.md`. El resto, según «Cómo leer la documentación» y «Qué leer según la tarea».
16. **Repositorio agnóstico y público**: nada versionado describe una instancia concreta (servidores, IP, proveedores, cuentas, credenciales, instancias o nodos privados de terceros). Lo de cada instancia va en `.env`, `peers.json` u otros archivos ignorados por git, con su `*.example` versionado con datos inventados.
17. **Identificadores documentados en español**: los nombres de vistas, columnas, variables o claves que aún aparezcan en español en `docs/info/` se traducen al inglés **antes** de implementarlos, actualizando en el mismo commit `docs/info/integration.md` y todos los documentos que los usen (regla 12). Los nombres de contenedores se mantienen.
18. **Sin CI ni registro de imágenes**: las imágenes propias se construyen en el servidor; las pruebas se ejecutan antes de etiquetar una versión. Repositorio principal en GitLab y copia en GitHub. Las copias de seguridad no forman parte del proyecto.
19. **Código tipado**: todo el código que el lenguaje permita va tipado (PHP: `declare(strict_types=1)`, tipos en parámetros, retornos y propiedades; Python: anotaciones completas comprobadas con `mypy --strict`; JS: JSDoc con tipos o TypeScript). Nada de tipos implícitos donde se puedan declarar.
20. **Código comentado**: todo el código lleva comentarios en español de España, con el formato estándar de cada lenguaje: PHPDoc en PHP, docstrings PEP 257 (estilo Google) en Python, JSDoc en JavaScript, comentarios `--` de cabecera en SQL y `#` en shell y YAML. Cada archivo, clase, función y método público se documenta; dentro del código se comenta el porqué, no lo obvio.
21. **Reglas de negocio obligatorias**: [`docs/info/business-rules.md`](docs/info/business-rules.md) define los objetivos y reglas del proyecto. Todo código y documento debe cumplirlas. Si una tarea las contradice, se para y se avisa; nunca se cambia una regla por iniciativa propia ni por comodidad de implementación. Solo el responsable del proyecto puede cambiarlas: entonces se actualiza **primero** `business-rules.md` (regla y fila en su historial con fecha), después el documento de la pieza y el código, en el mismo commit.

### Disparadores
- **Cambias un objetivo o regla de negocio (solo si lo pide el responsable)** → `docs/info/business-rules.md` primero, con fila en su historial.
- **Modificas módulo** (campos, relaciones, lógica, rutas, contratos) → actualizar su `.md`.
- **Cambias un contrato público** → contrato documentado + comprobar qué clientes lo consumen antes de romperlo.
- **Integras o tocas API de terceros** → `docs/info/apis/<api>.md` enlazando a `docs/apis/<api>/`, sin duplicar el dato oficial.
- **Tocas frontend/estilos** → `DESIGN.md` y/o `COMPONENTS.md`.
- **Añades comando o script** → `commands.md`.
- **Añades o quitas directorios** → árbol de estructura de `AGENTS.md`.
- **Planificas** → fase en `docs/planning/` con descripción + checklist.
- **Cierras fase** → verificar, promocionar, borrar.
- **Surge una duda que bloquea o condiciona** → `docs/planning/dudas/`, y al resolverla la consecuencia va a `docs/info/`.
- **Te piden auditoría** → informe en `docs/auditorias/`; al cerrar todos los hallazgos, promocionar y borrar.
- **Idea decidida pero aplazada** → `docs/future/`, nunca `docs/info/`.

### Plantillas Canónicas

#### 1. Módulo (`docs/info/<modulo>.md`)
- Qué hace y qué NO hace
- Modelo de datos
- Flujos principales
- Puntos de entrada (con auth, permisos y límites)
- Dependencias en ambos sentidos
- Configuración (variables, valor por defecto y efecto)
- Trampas conocidas
- Tests que lo cubren
- Pendiente real

#### 2. Fase de Planificación (`docs/planning/<NN>-<fase>.md`)
- Objetivo
- Contexto y motivo
- Alcance (dentro / fuera explícito)
- Trabajo por módulo con checklist que incluya «documentación actualizada» y «tests»
- Criterio de cierre

#### 3. Duda (`docs/planning/dudas/D<NN>.md`)
- Identificador `D<NN>`
- Estado: `[ ] Pendiente | [x] Resuelta | [ ] Ignorada`
- Qué bloquea
- Contexto
- Opciones con implicaciones
- Respuesta
- Consecuencia aplicada (una duda resuelta no se borra sin promocionar su consecuencia a `docs/info/`)

#### 4. Auditoría (`docs/auditorias/<nombre>.md`)
- Cabecera: fecha, revisión del código, alcance y método
- Resumen por severidad (Crítico / Alto / Medio / Bajo)
- Hallazgos con ID estable `<PREFIJO>-C01`, estado, ubicación `fichero:línea`, qué pasa, CÓMO SE HA VERIFICADO, impacto, corrección propuesta
- No hallazgos relevantes
- Recomendaciones fuera de alcance no verificadas
*(Nota: una sospecha sin comprobación no es un hallazgo. Crítico es lo que rompe datos, seguridad o disponibilidad).*

#### 5. API de Terceros (`docs/apis/<api>/`)
- Cabecera con fuentes, fecha de descarga y fecha de verificación real.
- Estructura: `README.md`, `00-fundamentos.md`, `ERRATAS.md`, `LIMITACIONES.md`, `<dominio>.md`, `src/`.

---

## Trampas Conocidas

| ID | Módulo / Área | Trampa | Solución / Mitigación |
| :--- | :--- | :--- | :--- |
| `TR-01` | Infraestructura / DNS / SSL | Los certificados SSL gratuitos de Cloudflare (Universal SSL) solo cubren un nivel de subdominio (`*.dominio.tld`). Subdominios de segundo nivel con proxy naranja (`*.mesh.dominio.tld` como `meshview.mesh...`) fallan con error SSL de certificado no válido. | Los servicios que usen proxy naranja deben configurarse en un único nivel (ej. `meshview.dominio.tld` mediante `MESHVIEW_DOMAIN`) o mantenerse en gris si van en segundo nivel. |
| `TR-02` | Ingesta / Protobuf / Python | El campo `from` en `meshtastic.protobuf.mesh_pb2.MeshPacket` es una palabra reservada en Python. Acceder mediante `packet.from` o `packet.from_node` provoca `SyntaxError` o `AttributeError`. | Usar obligatoriamente `getattr(packet, "from", 0)` para lectura y `setattr(packet, "from", val)` para asignación. |
| `TR-03` | MeshView / Configuración | `meshview:3.0.8` requiere la directiva `tls_cert =` en la sección `[server]` de `config.ini` aunque no se utilice TLS internamente; de lo contrario `web.py` falla al iniciar con `KeyError: 'tls_cert'`. | Definir siempre `tls_cert =` vacío en la plantilla `plantilla/config.ini.plantilla`. |
| `TR-04` | Ingesta / aiomqtt / Python | En `aiomqtt>=2.0`, el parámetro para el identificador de cliente en `Client()` se denomina `identifier`, no `client_id` (utilizar `client_id` lanza `TypeError: Client.__init__() got an unexpected keyword argument 'client_id'`). | Pasar siempre `identifier="<nombre>"` al instanciar `aiomqtt.Client`. |
| `TR-05` | Nginx / Cloudflare SSL / Redirecciones | Si Nginx redirige incondicionalmente HTTP a HTTPS en el puerto 80 en el origen mientras Cloudflare conecta al origen por HTTP (puerto 80), se produce un bucle infinito de redirecciones (`ERR_TOO_MANY_REDIRECTS`). | Nginx en el host debe proxificar tanto el puerto 80 como el 443 a sus upstreams internos (Portal 8100, MeshView 8081, PotatoMesh 41447), manteniendo `location /.well-known/acme-challenge/` para Certbot. En Laravel además se confía en proxies (`$middleware->trustProxies(at: '*')`) y se fuerza HTTPS (`URL::forceScheme('https')`). |
| `TR-06` | Git / macOS / Sensibilidad a mayúsculas en .gitignore | Una regla no anclada `datos/` en `.gitignore` ignora silenciosamente directorios de código fuente con ese nombre en sistemas de ficheros insensibles a mayúsculas (como APFS en macOS para `app/Datos/`). | Anclar siempre las rutas locales de datos en la raíz con barra inicial (`/datos/`) y usar nombres en inglés para directorios de código (`app/Data/`). |
| `TR-07` | Detector / Python / Registro Dinámico | Los decoradores de registro como `@registrar` en submódulos solo se ejecutan cuando el módulo que contiene la clase es importado por el intérprete. Si el motor busca reglas en `REGISTRO_REGLAS` sin haber importado el paquete `reglas`, el diccionario estará vacío (`0 reglas activas`). | Asegurar `import detector.reglas` explícito en el módulo que orquesta la carga dinámica. |
| `TR-08` | Docker Compose / Rutas relativas en volúmenes | Al invocar `docker compose -f <ruta>` desde un directorio de trabajo distinto al del archivo compose, los volúmenes con rutas relativas (`./config`) se resuelven contra el directorio de trabajo actual (CWD) y no contra la ubicación del compose. | Usar rutas absolutas en scripts de despliegue (`${dir_path}/config`) o empaquetar la configuración dentro de la imagen (`/app/config`). |
| `TR-09` | Despliegue / Permisos / .env | Los archivos `.env` creados por scripts de superusuario como `set-role-password.sh` con permisos `0600` de root provocan `permission denied` cuando el usuario de despliegue invoca `docker compose`. | Asegurar propiedad `usuario:www-data` y permisos `0640` en los archivos de entorno de servicios en `/srv/<servicio>/.env`. |

---

## Checklist de Finalización de Tarea

Antes de dar cualquier tarea por terminada:
- [ ] Ninguna regla de `docs/info/business-rules.md` incumplida
- [ ] Documentación del módulo tocado actualizada en el MISMO commit
- [ ] Fechas de «Última revisión» al día en los archivos tocados
- [ ] Módulos nuevos indexados en `docs/info/README.md` y en `AGENTS.md`
- [ ] Módulos eliminados fuera de TODOS los índices
- [ ] Ningún archivo versionado enlaza a `docs/planning/` ni a `docs/auditorias/`
- [ ] `docs/planning/` y `docs/auditorias/` siguen en `.gitignore`
- [ ] Lo duradero de planificaciones o auditorías cerradas promocionado, y el archivo borrado
- [ ] Checklists `[x]` verificados, no asumidos
- [ ] Árbol de estructura de `AGENTS.md` refleja los directorios reales
