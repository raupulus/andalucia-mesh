-- Migración 001: Esquema de base de datos para el servicio de Webhooks

-- 1. Tabla de cursor de consumo del socket UNIX
CREATE TABLE IF NOT EXISTS cursor (
    cliente TEXT PRIMARY KEY,
    transicion_id TEXT NOT NULL,
    actualizado_en TIMESTAMPTZ NOT NULL DEFAULT now()
);

-- 2. Tabla de destinos registrados (sincronizada desde webhooks.yaml)
CREATE TABLE IF NOT EXISTS destino (
    id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    nombre TEXT UNIQUE NOT NULL,
    host TEXT NOT NULL,
    url_hash TEXT NOT NULL,
    activo BOOLEAN NOT NULL DEFAULT true,
    motivo_baja TEXT NULL, -- 'fallos', 'gone', 'retirado'
    fallos_seguidos SMALLINT NOT NULL DEFAULT 0,
    ultimo_ok TIMESTAMPTZ NULL,
    alta_en TIMESTAMPTZ NOT NULL DEFAULT now(),
    baja_en TIMESTAMPTZ NULL,
    actualizado_en TIMESTAMPTZ NOT NULL DEFAULT now()
);

-- 3. Tabla de entregas (cola persistente y registro histórico)
CREATE TABLE IF NOT EXISTS entrega (
    id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    destino_id BIGINT NOT NULL REFERENCES destino(id) ON DELETE CASCADE,
    alerta_id TEXT NULL,
    transicion_id TEXT NOT NULL,
    transicion TEXT NOT NULL,
    regla TEXT NOT NULL,
    riesgo TEXT NOT NULL,
    tipo TEXT NOT NULL,
    cuerpo TEXT NOT NULL,
    estado TEXT NOT NULL DEFAULT 'pendiente', -- 'pendiente', 'entregada', 'fallida', 'caducada'
    intentos SMALLINT NOT NULL DEFAULT 0,
    programado_para TIMESTAMPTZ NOT NULL DEFAULT now(),
    ultimo_codigo SMALLINT NULL,
    ultimo_error TEXT NULL,
    duracion_ms INTEGER NULL,
    creado_en TIMESTAMPTZ NOT NULL DEFAULT now(),
    entregado_en TIMESTAMPTZ NULL,
    CONSTRAINT uq_entrega_destino_transicion UNIQUE (destino_id, transicion_id)
);

-- Índices según contrato UT-08.3 y §6
CREATE INDEX IF NOT EXISTS idx_entrega_pendiente ON entrega (destino_id, id) WHERE estado = 'pendiente';
CREATE INDEX IF NOT EXISTS idx_entrega_alerta ON entrega (destino_id, alerta_id);
CREATE INDEX IF NOT EXISTS idx_entrega_creado ON entrega (creado_en);
