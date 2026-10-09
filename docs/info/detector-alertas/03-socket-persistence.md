# 07.3 · Socket y persistencia

> Base `alertas` (tablas, retención, vistas contrato y permisos) y servidor del socket Unix con el protocolo de `../integration.md` §7. Toda transición se guarda antes de salir por el socket.

## Objetivo

Que cualquier consumidor recupere lo que se perdió (24 h) en orden, sin huecos ni duplicados; que un consumidor lento no afecte a nadie; y que el portal lea solo vistas estables con un rol de solo lectura.

## Especificación

### Orden de una transición

1. El ciclo de vida (`01-rule-engine.md`) decide la transición y genera `transicion_id` con el generador ULID monótono (estrictamente creciente en el proceso; una sola tarea de motor).
2. Una transacción: si la alerta es abierta, se verifica si ya existe una alerta abierta para `(regla, nodo)` para armonizar su ID; `INSERT … ON CONFLICT (id) DO UPDATE` en `alerta` + `INSERT` en `transicion` con el objeto `alerta` exacto que se emitirá.
3. Tras el `COMMIT`, el mensaje se reparte a la cola de cada cliente conectado.
4. PostgreSQL caído: las transiciones esperan en memoria en orden y se reintenta cada 5 s; nada se emite sin estar guardado. Más de 10.000 pendientes → se descartan primero las `actualizada` más antiguas (la siguiente transición lleva el estado completo) y se registra error.

### Servidor del socket

| Paso | Comportamiento |
|---|---|
| Arranque | `umask 007`; si `ALERTAS_SOCKET` existe y es un socket, se borra; `asyncio.start_unix_server`; `chmod 0660`; `chown(-1, ALERTAS_SOCKET_GID)`. Se restaura el estado y se recargan las alertas abiertas y resueltas recientes en `GestorCicloVida` desde DB; se abre el socket antes de conectar a MQTT |
| Saludo | Primera línea en ≤ `SALUDO_TIMEOUT_S` (10 s), máx. 4 KiB: `{"cliente": "<^[a-z0-9][a-z0-9-]{0,63}$>", "desde": "<ULID> | null"}`. Inválido, ausente o `desde` con formato no ULID → cierre y log `saludo_invalido` |
| Registro | Se crea la cola del cliente (`CLIENTE_MAX_PENDIENTES` = 1.000) y se añade al reparto **antes** de consultar el reenvío |
| Reenvío | `desde = null` → ninguno (solo directo). Si no: `SELECT id, transicion, alerta FROM transicion WHERE id > $desde AND en >= now() - make_interval(hours => $REENVIO_MAX_H) ORDER BY id`, escrito directamente con `drain()`; se recuerda el último id enviado |
| Directo | Se vacía la cola saltando los `transicion_id` ≤ último reenviado; después, cada mensaje según llega |
| Latido | Cada `LATIDO_S` (30 s): `{"latido": "<ISO 8601 UTC>"}` a la cola de cada cliente |
| Cliente lento | `put_nowait` con la cola llena → se cierra la conexión, log `cliente_lento` con `cliente`. Al reconectar recupera con `desde` |
| Entrada posterior | Todo lo que el cliente envíe tras el saludo se lee y se descarta; EOF → baja del reparto |
| Parada | Deja de aceptar, cierra clientes y borra el archivo del socket |

Mensajes del servidor (NDJSON, `json.dumps(…, ensure_ascii=False, separators=(",", ":"))` + `\n`):

```json
{"v":1,"transicion_id":"01JAC0Q4M2V9W8X7Y6Z5A4B3C2","transicion":"abierta","alerta":{"id":"01JAC0Q4M1K2J3H4G5F6E7D8C9","regla":"…"}}
{"latido":"2026-10-02T12:00:00Z"}
```

El objeto `alerta` completo está en `README.md` §4.2. Varios clientes con el mismo nombre se tratan como independientes.

**Obligaciones del cliente** (para `../bots-webhooks/`): guardar su último `transicion_id` después de procesarlo; ser idempotente por `transicion_id`; reconectar con espera de 1 a 30 s si no hay latido en 90 s o se cierra la conexión; reconocer la reapertura (transición `abierta` de un `alerta.id` ya conocido); tolerar claves nuevas en `datos`.

### Esquema (`migrations/001_tablas.sql`)

```sql
CREATE TABLE alerta (
  id             text COLLATE "C" PRIMARY KEY,         -- ULID
  regla          text NOT NULL,
  nodo           text NOT NULL,                        -- '!a1b2c3d4' o 'all'
  riesgo         text NOT NULL,
  tipo           text NOT NULL,
  mensaje        text NOT NULL,
  nodos          text[] NOT NULL DEFAULT '{}',         -- solo con nodo = 'all' (máx. 500)
  nodo_info      jsonb,                                -- {corto, largo, rol, provincia} o NULL
  provincia      text,                                 -- provincia del nodo (NULL con 'all')
  provincias     text[] NOT NULL DEFAULT '{}',         -- provincias distintas implicadas (filtro)
  datos          jsonb NOT NULL DEFAULT '{}',
  estado         text NOT NULL CHECK (estado IN ('abierta', 'resuelta')),
  abierta_en     timestamptz NOT NULL,
  actualizada_en timestamptz NOT NULL,                 -- última transición
  resuelta_en    timestamptz,
  reaperturas    integer NOT NULL DEFAULT 0,
  evidencia_en   timestamptz NOT NULL                  -- último refresco de mensaje/datos
);
CREATE UNIQUE INDEX alerta_clave_abierta ON alerta (regla, nodo) WHERE estado = 'abierta';
CREATE INDEX alerta_orden ON alerta (actualizada_en DESC, id DESC);
CREATE INDEX alerta_nodo ON alerta (nodo, estado); CREATE INDEX alerta_provincias ON alerta USING gin (provincias);

CREATE TABLE transicion (
  id         text COLLATE "C" PRIMARY KEY,             -- ULID monótono = orden de emisión
  alerta_id  text COLLATE "C" NOT NULL REFERENCES alerta (id) ON DELETE CASCADE,
  transicion text NOT NULL CHECK (transicion IN ('abierta', 'actualizada', 'resuelta')),
  en         timestamptz NOT NULL,
  alerta     jsonb NOT NULL                            -- objeto alerta tal como se emitió
);
CREATE INDEX transicion_alerta ON transicion (alerta_id, id); CREATE INDEX transicion_en ON transicion (en);

CREATE TABLE estado_snapshot (
  id        bigserial PRIMARY KEY,
  creado_en timestamptz NOT NULL DEFAULT now(),
  formato   integer NOT NULL, nodos integer NOT NULL,  -- versión del formato del estado; nodos incluidos
  datos     bytea NOT NULL                             -- JSON con gzip
);

CREATE TABLE catalogo (
  clase          text NOT NULL CHECK (clase IN ('riesgo', 'tipo', 'regla')),
  id             text NOT NULL,
  orden          integer NOT NULL,                     -- riesgos: 1 = menor
  nombre         text NOT NULL,
  descripcion    text NOT NULL,
  activa         boolean,                              -- reglas
  fase           text CHECK (fase IN ('mvp', 'ampliacion')),
  afecta_malla   boolean,
  riesgos        text[],                               -- riesgos que puede emitir la regla
  tipos          text[],                               -- tipos que puede emitir la regla
  actualizado_en timestamptz NOT NULL,
  PRIMARY KEY (clase, id)
);

CREATE TABLE esquema_version (version integer PRIMARY KEY, aplicada_en timestamptz NOT NULL DEFAULT now());
```

Migraciones: archivos SQL numerados, aplicados al arrancar dentro de una transacción con `pg_advisory_lock`, como rol `alertas`. Cada cambio de vistas va en un archivo nuevo con `CREATE OR REPLACE VIEW` y repite el `GRANT`.

### Retención

Tarea diaria a las 04:20 (`TZ`), en el propio detector:

```sql
DELETE FROM alerta WHERE estado = 'resuelta' AND resuelta_en < now() - make_interval(days => $RETENCION_DIAS);  -- arrastra sus transiciones
DELETE FROM transicion WHERE en < now() - make_interval(days => $RETENCION_DIAS);
DELETE FROM estado_snapshot WHERE id NOT IN (SELECT id FROM estado_snapshot ORDER BY id DESC LIMIT 3);
```

Las alertas abiertas no se borran nunca. 1 año = mismo plazo que los agregados por nodo de `ingesta`.

### Vistas contrato (`migrations/002_vistas.sql`)

Propietario `alertas`, sin `security_invoker`: el lector no necesita permisos sobre las tablas.

```sql
-- Una fila por alerta (abierta o resuelta, 1 año). Filtros del portal: estado, riesgo_orden, tipo, provincias, nodo, actualizada_en.
CREATE VIEW api_alertas AS
SELECT a.id, a.regla, a.riesgo, r.orden AS riesgo_orden, a.tipo, a.mensaje, a.nodo, a.nodos,
       CASE WHEN a.nodo = 'all' THEN coalesce((a.datos->>'nodos_total')::int, cardinality(a.nodos)) ELSE 1 END AS nodos_total,
       a.nodo_info, a.provincia, a.provincias, a.datos, a.estado,
       a.abierta_en, a.actualizada_en, a.resuelta_en, a.reaperturas
FROM alerta a LEFT JOIN catalogo r ON r.clase = 'riesgo' AND r.id = a.riesgo;

-- Una fila por transición (historial de /alerts/{id}).
CREATE VIEW api_alertas_transiciones AS
SELECT t.id AS transicion_id, t.alerta_id, t.transicion, t.en,
       t.alerta->>'riesgo' AS riesgo, t.alerta->>'mensaje' AS mensaje, t.alerta->'datos' AS datos
FROM transicion t;

-- Una fila por riesgo × tipo del catálogo, con ceros (summary, portada).
CREATE VIEW api_alertas_resumen AS
SELECT r.id AS riesgo, r.orden AS riesgo_orden, t.id AS tipo,
       count(a.id) AS abiertas,
       count(DISTINCT a.nodo) FILTER (WHERE a.nodo <> 'all') AS nodos_afectados,
       max(a.actualizada_en) AS ultima_en
FROM catalogo r CROSS JOIN catalogo t
LEFT JOIN alerta a ON a.estado = 'abierta' AND a.riesgo = r.id AND a.tipo = t.id
WHERE r.clase = 'riesgo' AND t.clase = 'tipo'
GROUP BY r.id, r.orden, t.id;

-- Una fila por nodo con alertas abiertas propias (nodos en peligro). Las de 'all' no cuentan.
CREATE VIEW api_nodos_en_riesgo AS
SELECT a.nodo,
       (array_agg(a.nodo_info ORDER BY a.actualizada_en DESC))[1] AS nodo_info,
       (array_agg(a.provincia ORDER BY a.actualizada_en DESC))[1] AS provincia,
       (array_agg(a.riesgo ORDER BY r.orden DESC))[1] AS riesgo_max,
       max(r.orden) AS riesgo_max_orden,
       array_agg(DISTINCT a.tipo) AS tipos,
       array_agg(DISTINCT a.regla) AS reglas,
       array_agg(a.mensaje ORDER BY r.orden DESC, a.abierta_en) AS motivos,
       count(*) AS alertas_abiertas, min(a.abierta_en) AS desde, max(a.actualizada_en) AS actualizada_en
FROM alerta a JOIN catalogo r ON r.clase = 'riesgo' AND r.id = a.riesgo
WHERE a.estado = 'abierta' AND a.nodo <> 'all'
GROUP BY a.nodo;

-- Una fila por riesgo, tipo o regla (/alerts/catalog, filtros de los bots).
CREATE VIEW api_catalogo AS
SELECT clase, id, orden, nombre, descripcion, activa, fase, afecta_malla, riesgos, tipos FROM catalogo;
```

### Permisos

El rol `portal_lector_alertas` (`LOGIN`, contraseña en el `.env` del portal) y el `pg_hba` los crea `../infrastructure/02-postgresql.md`. La migración, como propietario, aplica:

```sql
REVOKE ALL ON DATABASE alertas FROM PUBLIC;
REVOKE ALL ON ALL TABLES IN SCHEMA public FROM PUBLIC;
GRANT CONNECT ON DATABASE alertas TO portal_lector_alertas;
GRANT USAGE ON SCHEMA public TO portal_lector_alertas;
GRANT SELECT ON api_alertas, api_alertas_transiciones, api_alertas_resumen, api_nodos_en_riesgo, api_catalogo TO portal_lector_alertas;
```

## Contratos propios

Protocolo del socket (arriba), objeto `alerta` (`README.md` §4.2) y vistas `api_*` con sus columnas. Se pueden añadir columnas; quitar o renombrar una es cambio de contrato con `../portal/`.

## Unidades de trabajo

- **UT-07.3.1 — Migraciones.** Ejecutor de SQL numerado con `esquema_version` y bloqueo consultivo. *Aceptación:* base vacía → esquema completo; segundo arranque → no hace nada.
- **UT-07.3.2 — Persistencia de transiciones.** Guardar antes de emitir; cola de pendientes con PostgreSQL caído; refresco de `datos` sin transición (máx. 1/min). *Bordes:* caída a mitad de transacción. *Aceptación:* escenario 4.
- **UT-07.3.3 — Servidor y saludo.** Arranque, permisos, validación del saludo y cierre. *Bordes:* socket huérfano de una parada brusca, saludo de 5 KiB. *Aceptación:* escenario 5.
- **UT-07.3.4 — Reenvío y directo.** Registro antes del reenvío y salto de duplicados. *Bordes:* transiciones generadas durante el reenvío, `desde` purgado o de hace más de 24 h. *Aceptación:* escenarios 1 y 2.
- **UT-07.3.5 — Latido y clientes lentos.** *Aceptación:* escenario 3; los demás clientes no notan nada.
- **UT-07.3.6 — Vistas y permisos.** *Aceptación:* `portal_lector_alertas` lee las 5 vistas y recibe `permission denied` en las tablas; `api_alertas_resumen` devuelve 6 filas (3 riesgos × 2 tipos) con la base vacía.
- **UT-07.3.7 — Retención y cliente de prueba.** Tarea diaria y subcomando `python -m detector escuchar [--desde ULID]`, que se conecta como `cliente: "escuchar"` e imprime cada línea. *Aceptación:* una alerta resuelta hace 366 días desaparece con sus transiciones; una abierta de hace 400 días sigue.

## Escenarios de prueba

1. Dado dos clientes conectados / Cuando se abre una alerta / Entonces ambos reciben la misma línea con el mismo `transicion_id`.
2. Dado un cliente parado 10 min con último id `X` / Cuando se generan 30 transiciones y otras 3 mientras reconecta / Entonces recibe las 33 en orden de `transicion_id`, sin duplicados.
3. Dado un cliente que no lee / Cuando acumula 1.001 mensajes / Entonces se le desconecta y el resto sigue recibiendo en < 1 s.
4. Dado PostgreSQL parado / Cuando se abren 2 alertas / Entonces no sale nada por el socket hasta que vuelve la base; después salen las 2 en orden y están en `transicion`.
5. Dado `socat - UNIX-CONNECT:/run/snm/alertas.sock` en un contenedor sin el GID `10500` / Cuando conecta / Entonces `Permission denied`; con el GID y sin saludo en 10 s, el servidor cierra.

---
> Creado: 2026-10-07 · Última revisión: 2026-10-09

