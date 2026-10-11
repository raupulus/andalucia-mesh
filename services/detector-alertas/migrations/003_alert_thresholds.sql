-- ==============================================================================
-- 003_alert_thresholds.sql
-- Tabla para persistencia de umbrales dinámicos calibrados en el detector
-- ==============================================================================

CREATE TABLE IF NOT EXISTS alert_thresholds (
    role_group VARCHAR(32) NOT NULL,
    parameter VARCHAR(64) NOT NULL,
    value_numeric DOUBLE PRECISION NOT NULL,
    unit VARCHAR(16) NOT NULL DEFAULT 'number',
    description TEXT,
    updated_at TIMESTAMPTZ NOT NULL DEFAULT now(),
    PRIMARY KEY (role_group, parameter)
);

CREATE INDEX IF NOT EXISTS idx_alert_thresholds_group ON alert_thresholds (role_group);
