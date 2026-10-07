-- ==============================================================================
-- V003__maintenance_jobs.sql
-- Tareas de mantenimiento programadas en TimescaleDB para snm_ingest
-- ==============================================================================

-- Procedimiento para purgar registros de nodos y gateways inactivos tras 365 días
CREATE OR REPLACE PROCEDURE limpiar_registros(job_id int, config jsonb)
LANGUAGE plpgsql
AS $$
BEGIN
    -- Borrado de nodos sin actividad registrada en el último año
    DELETE FROM node WHERE last_seen < now() - INTERVAL '365 days';

    -- Borrado de gateways sin actividad registrada en el último año
    DELETE FROM gateway WHERE last_message_at < now() - INTERVAL '365 days';
END;
$$;

-- Registrar tarea programada diaria si no existe
DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM timescaledb_information.jobs WHERE proc_name = 'limpiar_registros'
    ) THEN
        PERFORM add_job('limpiar_registros', INTERVAL '1 day', job_name => 'limpiar_registros_inactivos');
    END IF;
END;
$$;
