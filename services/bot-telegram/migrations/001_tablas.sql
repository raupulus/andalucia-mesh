-- Migración 001: Esquema de base de datos para el bot de Telegram

-- 1. Tabla de cursor de consumo del socket UNIX
CREATE TABLE IF NOT EXISTS cursor (
    cliente TEXT PRIMARY KEY,
    transicion_id TEXT NOT NULL,
    actualizado_en TIMESTAMPTZ NOT NULL DEFAULT now()
);

-- 2. Tabla de destinos (grupos, supergrupos y canales de Telegram)
CREATE TABLE IF NOT EXISTS destino (
    id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    plataforma_id BIGINT UNIQUE NOT NULL, -- chat.id de Telegram
    servidor_id BIGINT NULL,              -- NULL en Telegram
    clase TEXT NOT NULL,                  -- 'group', 'supergroup', 'channel'
    riesgos TEXT[] NULL,                  -- NULL = BOT_RIESGOS_DEFECTO vigente
    tipos TEXT[] NULL,                    -- NULL = BOT_TIPOS_DEFECTO vigente
    activo BOOLEAN NOT NULL DEFAULT true,
    motivo_baja TEXT NULL,                -- 'expulsado', 'sin_permiso', 'fallos', 'chat_inexistente'
    alta_en TIMESTAMPTZ NOT NULL DEFAULT now(),
    baja_en TIMESTAMPTZ NULL,
    fallando_desde TIMESTAMPTZ NULL,
    actualizado_en TIMESTAMPTZ NOT NULL DEFAULT now()
);

-- 3. Tabla de envíos (cola persistente y registro de lo enviado)
CREATE TABLE IF NOT EXISTS envio (
    id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    destino_id BIGINT NOT NULL REFERENCES destino(id) ON DELETE CASCADE,
    chat_envio_id BIGINT NOT NULL,
    alerta_id TEXT NULL,
    transicion_id TEXT NULL,
    transicion TEXT NOT NULL,
    inicia_hilo BOOLEAN NOT NULL DEFAULT false,
    regla TEXT NOT NULL,
    riesgo TEXT NOT NULL,
    tipo TEXT NOT NULL,
    etiqueta TEXT NOT NULL,
    estado TEXT NOT NULL DEFAULT 'pendiente', -- 'pendiente', 'enviado', 'agrupado', 'fallido', 'caducado'
    resumen_id BIGINT NULL REFERENCES envio(id),
    programado_para TIMESTAMPTZ NOT NULL DEFAULT now(),
    intentos SMALLINT NOT NULL DEFAULT 0,
    mensaje_id BIGINT NULL,
    contenido JSONB NOT NULL,
    ultimo_error TEXT NULL,
    creado_en TIMESTAMPTZ NOT NULL DEFAULT now(),
    enviado_en TIMESTAMPTZ NULL,
    CONSTRAINT uq_envio_destino_transicion UNIQUE (destino_id, transicion_id)
);

-- Índices según contrato UT-08.1 y §6
CREATE INDEX IF NOT EXISTS idx_envio_pendiente ON envio (programado_para, id) WHERE estado = 'pendiente';
CREATE INDEX IF NOT EXISTS idx_envio_hilo ON envio (destino_id, alerta_id, id DESC);
CREATE INDEX IF NOT EXISTS idx_envio_creado ON envio (creado_en);
