-- ==============================================================================
-- limpieza.sql (integrations/potatomesh/)
--
-- Purga periódica de datos con más de 30 días de antigüedad en PotatoMesh SQLite.
-- Ejecutado a diario por snm-potato-limpieza.timer a las 03:30 h en el host.
-- ==============================================================================

DELETE FROM messages WHERE created_at < datetime('now', '-30 days');
DELETE FROM positions WHERE created_at < datetime('now', '-30 days');
DELETE FROM telemetry WHERE created_at < datetime('now', '-30 days');
DELETE FROM traces WHERE created_at < datetime('now', '-30 days');
DELETE FROM neighbors WHERE updated_at < datetime('now', '-30 days');
DELETE FROM nodes WHERE last_heard < datetime('now', '-30 days');

-- Truncar el archivo WAL para liberar espacio sin bloquear lectura continua
PRAGMA wal_checkpoint(TRUNCATE);
