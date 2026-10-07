# 07.1 · Motor de reglas

> Cómo se escriben, clasifican y evalúan las comprobaciones, qué estado y líneas base mantiene el motor y cómo vive una alerta desde que se abre hasta que se resuelve.

## Objetivo

Que añadir una regla sea añadir una clase y su bloque en `reglas.yaml`, que añadir un riesgo o un tipo sea añadir una línea en `clasificacion.yaml`, y que cada problema produzca una sola alerta con histéresis que sobreviva a reinicios.

## Especificación

### Contratos

```python
@dataclass(frozen=True)
class Alerta:                                   # lo que devuelve una regla
    regla: str                                  # id de la regla ("reboot-loop")
    riesgo: str                                 # id de clasificacion.yaml ("bajo" | "medio" | "alto" | …)
    mensaje: str                                # español, listo para mostrar
    nodo: str                                   # "!a1b2c3d4" o "all"
    nodos: tuple[str, ...] = ()                 # solo con nodo == "all"
    datos: Mapping[str, Any] = field(default_factory=dict)  # evidencias: cifras, ventana, umbral
    tipo: str | None = None                     # None = lo decide el clasificador

class Regla(Protocol):
    id: ClassVar[str]
    nombre: ClassVar[str]                       # para api_catalogo
    descripcion: ClassVar[str]
    fase: ClassVar[Literal["mvp", "ampliacion"]]
    afecta_malla: ClassVar[bool]                # True = siempre infraestructura
    suscrita_a: ClassVar[frozenset[str]]        # portnums que la disparan; vacío = solo temporizador
    Config: ClassVar[type[BaseModel]]           # esquema de su bloque en reglas.yaml
    def comprobar(self, ctx: Contexto) -> list[Alerta]: ...                 # umbrales de apertura
    def sigue_activa(self, ctx: Contexto, abierta: AlertaAbierta) -> bool: ...  # umbrales de resolución

@dataclass
class Contexto:
    ahora: datetime                             # reloj del motor (simulado en calibración)
    evento: Paquete | Tick                      # mensaje decoded validado o tick de 60 s
    nodo: EstadoNodo | None                     # nodo origen del paquete; None en tick
    estado: EstadoMotor                         # solo lectura: nodos, gateways, ids conocidos
    bases: LineasBase
    config: BaseModel                           # bloque validado de la regla
    general: ConfigGeneral                      # bloque general de reglas.yaml
    es_infraestructura: Callable[[str], bool]
```

- Las reglas son síncronas, sin E/S y deterministas para un `ahora` dado; presupuesto total < 5 ms por paquete.
- **Registro:** decorador `@registrar` en `detector/reglas/`; al cargar `reglas.yaml` se instancian solo las de `activa: true`, validando el bloque con su `Config`. Regla registrada sin bloque → inactiva con aviso.

### Clasificador

`config/clasificacion.yaml`:

```yaml
riesgos:                     # de menor a mayor; el orden define min_risk
  - {id: bajo,  nombre: Bajo,  descripcion: "Conviene saberlo; no hay daño inmediato"}
  - {id: medio, nombre: Medio, descripcion: "Riesgo real para un nodo o una zona; conviene actuar"}
  - {id: alto,  nombre: Alto,  descripcion: "Fallo activo o daño a la malla"}
tipos:
  - {id: infraestructura, nombre: Infraestructura, descripcion: "Routers y gateways, y lo que degrada la malla o afecta a muchos nodos"}
  - {id: clientes, nombre: Clientes, descripcion: "Problemas de un nodo concreto que no es infraestructura"}
infraestructura_manual: []   # ids tratados como infraestructura además de INFRA_ROLES y gateways
```

Tipo final, en orden: 1) `nodo == "all"` o `afecta_malla` → `infraestructura`; 2) nodo de infraestructura (rol en `INFRA_ROLES`, gateway conocido o en `infraestructura_manual`) → `infraestructura`; 3) `tipo` propuesto por la regla, si existe en `tipos` → ese; 4) `clientes`. Un `riesgo` inexistente descarta la alerta y se registra error. `infraestructura` y `clientes` son obligatorios en `tipos` (son valores del contrato de la API).

### Estado

| Ámbito | Contenido |
|---|---|
| Nodo (~3 KB × 2.000) | Registro (`corto`, `largo`, `rol`, `hw`, `es_gateway`, `provincia` del último `from_node`; primera y última vez); último `uptime_seconds` y reinicios de 24 h; serie horaria de batería y voltaje de 72 h y últimas 2 muestras; recuentos deslizantes por tipo (10 min, 1 h, 24 h) y horarios de 168 h; últimos 50 intervalos; últimos `hop_start`; últimas 20 emisiones `^all` por tipo; última posición y desplazamiento de 24 h; huella de `public_key` |
| Gateway | Última recepción (`receptions[].at`) y últimos 50 intervalos |
| Ids conocidos | Id → última vez, 90 días (para `new-node-burst`) |
| Malla | Series de las líneas base (abajo) |

Un nodo sin oírse 30 días sale del estado. Routers y gateways se vigilan mientras se hayan oído en `general.seguimiento_dias` (7).

### Líneas base

| Base | Cálculo | Uso |
|---|---|---|
| Intervalo típico de nodo | Mediana de los últimos 50 intervalos entre paquetes propios (7 días) | `infra-silent` |
| Ritmo típico de nodo | Mediana de los recuentos horarios con actividad (168 h, sin `routing`) ÷ 6 = paquetes por 10 min | `flood` |
| Intervalo típico de gateway | Mediana de los últimos 50 intervalos entre recepciones del gateway | `gateway-offline` |
| Actividad de malla | Nodos distintos en 2 min, muestreado cada minuto, 7 días; base = mediana de la misma hora local (`TZ`) | `rafaga-masiva` |
| Nodos nuevos | Ids nuevos por hora, 7 días; base = mediana de la misma hora local | `new-node-burst` |
| Provincia | Saturación ponderada de la provincia (`../integration.md` §13): último `channel_utilization` (≤ 12 h) de cada nodo con `fn.province`, media por grupo (routers 0,6 · `CLIENT` y `CLIENT_BASE` juntos 0,4, pesos de `SATURACION_PESO_*`; `CLIENT_MUTE` fuera), cada minuto | `chutil-high` (en `datos`) |
| Ruido de nodo | Mediana de `noise_floor` de 7 días | `noise-high` |

Umbral dinámico: `max(mínimo_fijo, k × base)`. Sin `general.historia_minima_h` (24 h) de historia del nodo, gateway o malla, se usa solo el mínimo.

### Procesado y temporizador

1. Paquete validado → actualizar estado y bases → `comprobar` de las reglas con su `portnum` en `suscrita_a` → `sigue_activa` de las alertas abiertas de ese nodo.
2. Tick cada 60 s (segundo 0): `comprobar` de las reglas con `suscrita_a` vacío; `sigue_activa` de todas las abiertas; caducidad de silencios; cierre del minuto de las series de malla; instantánea si toca. Un tick que dura > 5 s se registra; si se solapa con el siguiente, este se salta y se cuenta.
3. Cada `Alerta` → clasificador → silencios → ciclo de vida → persistencia y socket (`03-socket-persistence.md`).
4. Ausencias: el silencio se mide desde `max(último visto, arranque del detector)`.

### Ciclo de vida

```text
 comprobar() ──▶ abierta ── riesgo sube / baja, nodos ±20 % ──▶ actualizada (sigue abierta)
                    │                                                 │
                    └── sigue_activa() == False ──▶ resuelta ◀────────┘
                                                      └── reaparece < 60 min → misma alerta, transición abierta
```

- **Clave:** `regla + nodo` (con `all`, `regla + all`). Una abierta por clave.
- **Abrir:** `comprobar` devuelve una alerta para una clave sin abierta → `abierta` con `id` ULID nuevo. Si la misma clave se resolvió hace < `general.reapertura_min` (60) → se reabre esa: mismo `id`, `resuelta_en = null`, `reaperturas + 1`, transición `abierta`.
- **Actualizar:** riesgo mayor → `actualizada` inmediata. Riesgo menor o `len(nodos)` ±20 % respecto a la última transición → `actualizada` solo si han pasado `general.actualizacion_min` (15) desde la última transición. Si no, se refrescan `mensaje` y `datos` en la tabla (máx. 1/min) sin transición.
- **Resolver:** `sigue_activa == False` → `resuelta`, `datos.cierre = "condicion"`. Otros cierres: `fuera_de_seguimiento` y `regla_desactivada`.
- `nodo_info` se toma del registro en cada transición; `actualizada_en` = hora de la última transición.

### Silencios

```yaml
silencios:
  - {nodo: "!a1b2c3d4", regla: null, hasta: "2026-10-15T00:00:00Z", motivo: "Mantenimiento"}
```

`regla: null` = todas; `nodo: "all"` vale para reglas de malla; `hasta` obligatorio (máx. 90 días). Silenciado: no se abre ni se emite `actualizada`; lo abierto puede resolverse; las bases siguen alimentándose.

### Recarga en caliente

Sondeo de `mtime` cada `RECARGA_S` y `SIGHUP`. Se valida todo junto: riesgos y tipos usados existen, umbrales coherentes (el riesgo mayor, más estricto), bloque desconocido con `activa: true` → error (con `activa: false` → aviso), no se quita un riesgo o tipo usado por una alerta abierta. Válido → sustitución atómica entre dos eventos, `catalogo` reescrito, alertas de reglas desactivadas resueltas (`regla_desactivada`). Inválido → sigue la anterior, log `config_invalida` y `/health.config = "error: …"`.

### Instantáneas

Cada `SNAPSHOT_S` y en `SIGTERM`: estado de nodos, gateways, ids conocidos y bases en JSON con gzip → `estado_snapshot` (`formato`, `nodos`, `datos`). Al arrancar se carga la última con el `formato` actual; si no hay o falla, estado vacío (solo mínimos). Las alertas abiertas se leen de `alerta`, nunca de la instantánea. Pérdida máxima: 60 s de estado.

### Calibración

`python -m detector calibrar --entrada 'grabaciones/*.ndjson.gz' --reglas candidato.yaml [--clasificacion …] [--json]`: reproduce los mensajes por orden de `rx_first` con reloj simulado (tick por minuto de tiempo de evento), sin base ni socket, y muestra por regla × riesgo × tipo aperturas, actualizaciones, resoluciones, abiertas al final y duración mediana, más los 10 nodos con más alertas. Entrada: grabación propia (`GRABACION_DIAS` > 0 → `/datos/grabaciones/AAAA-MM-DD.ndjson.gz`, sin `payload.text`, borrado a los N días) o captura con `mosquitto_sub -t 'snm/v1/decoded/#'`.

## Contratos propios

`Alerta`, `Regla`, `Contexto` (arriba), `clasificacion.yaml` y el bloque `general` y `silencios` de `reglas.yaml` (`02-rule-catalog.md`).

## Unidades de trabajo

- **UT-07.1.1 — Contratos y registro.** Clases y decorador; carga de reglas activas con su `Config`. *Bordes:* dos reglas con el mismo `id` → error al arrancar. *Aceptación:* una regla de prueba nueva funciona sin tocar el motor.
- **UT-07.1.2 — Clasificador.** Carga y validación de `clasificacion.yaml`; tipo final en el orden descrito. *Bordes:* rol `null`, `CLIENT_BASE` (no es infraestructura), gateway con rol cliente (es infraestructura). *Aceptación:* tabla de verdad completa en pruebas.
- **UT-07.1.3 — Estado.** Registro desde `from_node` y `receptions`; contadores y series acotados. *Bordes:* `from_node` todo `null`, nodo que pasa de `ROUTER` a `CLIENT`, purga a 30 días. *Aceptación:* 2.000 nodos sintéticos ocupan < 15 MB.
- **UT-07.1.4 — Líneas base.** Cálculos de la tabla con historia mínima. *Bordes:* nodo con un solo paquete, cambio de hora (franjas en hora local). *Aceptación:* con 23 h de historia solo se aplican mínimos; con 25 h, `max(mínimo, k × base)`.
- **UT-07.1.5 — Ciclo de vida.** Abrir, actualizar con límite, resolver, reabrir. *Bordes:* riesgo que oscila cada minuto; resolución y reaparición en el mismo tick. *Aceptación:* escenarios de este módulo.
- **UT-07.1.6 — Temporizador.** Tick de 60 s y ausencias desde `max(último visto, arranque)`. *Bordes:* tick lento, reloj que salta. *Aceptación:* tras reiniciar no se abre ninguna ausencia antes de su umbral mínimo.
- **UT-07.1.7 — Silencios y recarga.** Sondeo, `SIGHUP`, validación y sustitución atómica. *Bordes:* archivo a medio escribir (YAML inválido → se reintenta en el siguiente sondeo). *Aceptación:* cambiar un umbral surte efecto en ≤ 30 s sin perder estado.
- **UT-07.1.8 — Instantáneas.** Guardado periódico y en parada, restauración por `formato`. *Bordes:* instantánea corrupta. *Aceptación:* tras `restart` las bases siguen (sin volver a mínimos) y no se reemiten abiertas.
- **UT-07.1.9 — Calibración y grabación.** Subcomando `calibrar` y grabador diario. *Bordes:* archivos desordenados, días sin datos. *Aceptación:* sobre la misma entrada y `reglas.yaml`, dos ejecuciones dan el mismo resultado.

## Escenarios de prueba

- Dado un router al 40 % / Cuando baja a 35 % y oscila entre 38 % y 45 % durante 6 h / Entonces hay una sola alerta `battery-low` `medio · infraestructura`, que se resuelve al pasar de 50 %.
- Dado un nodo personal (`CLIENT_MUTE`) / Cuando informa 15 % dos veces seguidas / Entonces se abre `battery-low` `bajo · clientes`.
- Dado una malla con base de 10 nodos por 2 min / Cuando 200 nodos emiten telemetría en 2 min / Entonces se abre una sola `rafaga-masiva` `alto · infraestructura` con `nodo: "all"` y la lista en `nodos`.
- Dado `flood` abierta en `medio` / Cuando sube a `alto` a los 3 min y baja a `medio` a los 6 min / Entonces sale `actualizada` a `alto` en el acto y la bajada no se emite hasta cumplir 15 min desde esa transición.
- Dado `battery-low` resuelta hace 20 min / Cuando el nodo vuelve a 35 % / Entonces se reabre con el mismo `id` y `reaperturas = 1`.
- Dado `reglas.yaml` con un riesgo inexistente / Cuando se guarda / Entonces sigue la configuración anterior y `/health.config` muestra el error.

---
> Creado: 2026-10-07 · Última revisión: 2026-10-07
