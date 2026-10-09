-- ==============================================================================
-- limpieza.sql (integrations/potatomesh/)
--
-- Purga periódica de datos en PotatoMesh SQLite:
-- - Trazas (traceroutes) y saltos: retención de 5 días.
-- - Nodos, chat, waypoints, posiciones, telemetría y vecinos: retención de 15 días.
-- Ejecutado a diario por snm-potato-limpieza.timer a las 03:47 h en el host.
-- ==============================================================================

PRAGMA foreign_keys = ON;

-- 1. Mensajes del chat con más de 15 días
DELETE FROM messages WHERE rx_time < unixepoch('now', '-15 days');

-- 2. Waypoints con más de 15 días o con expiración vencida
DELETE FROM waypoints WHERE rx_time < unixepoch('now', '-15 days') OR (expire IS NOT NULL AND expire > 0 AND expire < unixepoch('now'));

-- 3. Posiciones con más de 15 días
DELETE FROM positions WHERE rx_time < unixepoch('now', '-15 days');

-- 4. Telemetría con más de 15 días
DELETE FROM telemetry WHERE rx_time < unixepoch('now', '-15 days');

-- 5. Trazas y sus saltos con más de 5 días
DELETE FROM traces WHERE rx_time < unixepoch('now', '-5 days');
DELETE FROM trace_hops WHERE trace_id NOT IN (SELECT id FROM traces);

-- 6. Vecinos con más de 15 días
DELETE FROM neighbors WHERE rx_time < unixepoch('now', '-15 days');

-- 7. Nodos no oídos en los últimos 15 días
DELETE FROM nodes WHERE last_heard IS NOT NULL AND last_heard < unixepoch('now', '-15 days');

-- Truncar el archivo WAL para liberar espacio sin bloquear lectura continua
PRAGMA wal_checkpoint(TRUNCATE);
