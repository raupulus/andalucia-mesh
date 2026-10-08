-- ==============================================================================
-- limpieza.sql (integrations/potatomesh/)
--
-- Purga periódica de datos con más de 30 días de antigüedad en PotatoMesh SQLite.
-- Ejecutado a diario por snm-potato-limpieza.timer a las 03:30 h en el host.
-- ==============================================================================

PRAGMA foreign_keys = ON;

-- 1. Mensajes con más de 30 días
DELETE FROM messages WHERE rx_time < unixepoch('now', '-30 days');

-- 2. Posiciones con más de 30 días
DELETE FROM positions WHERE rx_time < unixepoch('now', '-30 days');

-- 3. Telemetría con más de 30 días
DELETE FROM telemetry WHERE rx_time < unixepoch('now', '-30 days');

-- 4. Trazas y sus saltos con más de 30 días
DELETE FROM traces WHERE rx_time < unixepoch('now', '-30 days');
DELETE FROM trace_hops WHERE trace_id NOT IN (SELECT id FROM traces);

-- 5. Vecinos con más de 30 días
DELETE FROM neighbors WHERE rx_time < unixepoch('now', '-30 days');

-- 6. Nodos no oídos en los últimos 30 días
DELETE FROM nodes WHERE last_heard IS NOT NULL AND last_heard < unixepoch('now', '-30 days');

-- Truncar el archivo WAL para liberar espacio sin bloquear lectura continua
PRAGMA wal_checkpoint(TRUNCATE);
