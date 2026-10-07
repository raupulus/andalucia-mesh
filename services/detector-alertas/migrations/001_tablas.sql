-- ==============================================================================
-- 001_tablas.sql
-- Tablas principales del Detector de Alertas
-- ==============================================================================

CREATE TABLE IF NOT EXISTS esquema_version (
  version     integer PRIMARY KEY,
  aplicada_en timestamptz NOT NULL DEFAULT now()
);

CREATE TABLE IF NOT EXISTS alerta (
  id             text COLLATE "C" PRIMARY KEY,         -- Identificador ULID
  regla          text NOT NULL,
  nodo           text NOT NULL,                        -- '!a1b2c3d4' o 'all'
  riesgo         text NOT NULL,
  tipo           text NOT NULL,
  mensaje        text NOT NULL,
  nodos          text[] NOT NULL DEFAULT '{}',         -- Solo con nodo = 'all' (máx. 500)
  nodo_info      jsonb,                                -- {corto, largo, rol, provincia} o NULL
  provincia      text,                                 -- Provincia del nodo (NULL con 'all')
  provincias     text[] NOT NULL DEFAULT '{}',         -- Provincias distintas implicadas
  datos          jsonb NOT NULL DEFAULT '{}',
  estado         text NOT NULL CHECK (estado IN ('abierta', 'resuelta')),
  abierta_en     timestamptz NOT NULL,
  actualizada_en timestamptz NOT NULL,                 -- Última transición
  resuelta_en    timestamptz,
  reaperturas    integer NOT NULL DEFAULT 0,
  evidencia_en   timestamptz NOT NULL                  -- Último refresco de mensaje/datos
);

CREATE UNIQUE INDEX IF NOT EXISTS alerta_clave_abierta ON alerta (regla, nodo) WHERE estado = 'abierta';
CREATE INDEX IF NOT EXISTS alerta_orden ON alerta (actualizada_en DESC, id DESC);
CREATE INDEX IF NOT EXISTS alerta_nodo ON alerta (nodo, estado);
CREATE INDEX IF NOT EXISTS alerta_provincias ON alerta USING gin (provincias);

CREATE TABLE IF NOT EXISTS transicion (
  id         text COLLATE "C" PRIMARY KEY,             -- ULID monótono = orden de emisión
  alerta_id  text COLLATE "C" NOT NULL REFERENCES alerta (id) ON DELETE CASCADE,
  transicion text NOT NULL CHECK (transicion IN ('abierta', 'actualizada', 'resuelta')),
  en         timestamptz NOT NULL,
  alerta     jsonb NOT NULL                            -- Objeto alerta tal como se emitió
);

CREATE INDEX IF NOT EXISTS transicion_alerta ON transicion (alerta_id, id);
CREATE INDEX IF NOT EXISTS transicion_en ON transicion (en);

CREATE TABLE IF NOT EXISTS estado_snapshot (
  id        bigserial PRIMARY KEY,
  creado_en timestamptz NOT NULL DEFAULT now(),
  formato   integer NOT NULL,
  nodos     integer NOT NULL,
  datos     bytea NOT NULL                             -- JSON comprimido con gzip
);

CREATE TABLE IF NOT EXISTS catalogo (
  clase          text NOT NULL CHECK (clase IN ('riesgo', 'tipo', 'regla')),
  id             text NOT NULL,
  orden          integer NOT NULL,                     -- Riesgos: 1 = menor
  nombre         text NOT NULL,
  descripcion    text NOT NULL,
  activa         boolean,                              -- Solo reglas
  fase           text CHECK (fase IN ('mvp', 'ampliacion')),
  afecta_malla   boolean,
  riesgos        text[],                               -- Riesgos que puede emitir la regla
  tipos          text[],                               -- Tipos que puede emitir la regla
  actualizado_en timestamptz NOT NULL,
  PRIMARY KEY (clase, id)
);
