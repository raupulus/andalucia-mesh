-- =============================================================================
-- databases.sql
-- Aprovisionamiento inicial de bases de datos y roles para Andalucía Mesh
-- Ejecutar como superusuario postgres: psql -U postgres -f databases.sql
-- =============================================================================

-- Desactivar mensajes ruidosos en ejecuciones repetidas
SET client_min_messages = warning;

-- =============================================================================
-- 1. Roles Propietarios de Servicios
-- Las contraseñas iniciales son provisionales y deben rotarse inmediatamente
-- con el script set-role-password.sh
-- =============================================================================

DO $$
BEGIN
    -- Portal Web
    IF NOT EXISTS (SELECT FROM pg_catalog.pg_roles WHERE rolname = 'snm_portal') THEN
        CREATE ROLE snm_portal WITH LOGIN ENCRYPTED PASSWORD 'insecure_placeholder_portal';
    END IF;

    -- MeshView
    IF NOT EXISTS (SELECT FROM pg_catalog.pg_roles WHERE rolname = 'snm_meshview') THEN
        CREATE ROLE snm_meshview WITH LOGIN ENCRYPTED PASSWORD 'insecure_placeholder_meshview';
    END IF;

    -- Ingesta de paquetes y telemetría
    IF NOT EXISTS (SELECT FROM pg_catalog.pg_roles WHERE rolname = 'snm_ingest') THEN
        CREATE ROLE snm_ingest WITH LOGIN ENCRYPTED PASSWORD 'insecure_placeholder_ingest';
    END IF;

    -- Detector de alertas
    IF NOT EXISTS (SELECT FROM pg_catalog.pg_roles WHERE rolname = 'snm_detector') THEN
        CREATE ROLE snm_detector WITH LOGIN ENCRYPTED PASSWORD 'insecure_placeholder_detector';
    END IF;

    -- PotatoMesh
    IF NOT EXISTS (SELECT FROM pg_catalog.pg_roles WHERE rolname = 'snm_potato') THEN
        CREATE ROLE snm_potato WITH LOGIN ENCRYPTED PASSWORD 'insecure_placeholder_potato';
    END IF;

    -- Chat en directo WebSocket
    IF NOT EXISTS (SELECT FROM pg_catalog.pg_roles WHERE rolname = 'snm_chatws') THEN
        CREATE ROLE snm_chatws WITH LOGIN ENCRYPTED PASSWORD 'insecure_placeholder_chatws';
    END IF;

    -- Sincronización de peers PotatoMesh
    IF NOT EXISTS (SELECT FROM pg_catalog.pg_roles WHERE rolname = 'peersync') THEN
        CREATE ROLE peersync WITH LOGIN ENCRYPTED PASSWORD 'insecure_placeholder_peersync';
    END IF;

    -- Bots y Webhooks
    IF NOT EXISTS (SELECT FROM pg_catalog.pg_roles WHERE rolname = 'snm_bot_telegram') THEN
        CREATE ROLE snm_bot_telegram WITH LOGIN ENCRYPTED PASSWORD 'insecure_placeholder_telegram';
    END IF;

    IF NOT EXISTS (SELECT FROM pg_catalog.pg_roles WHERE rolname = 'snm_bot_discord') THEN
        CREATE ROLE snm_bot_discord WITH LOGIN ENCRYPTED PASSWORD 'insecure_placeholder_discord';
    END IF;

    IF NOT EXISTS (SELECT FROM pg_catalog.pg_roles WHERE rolname = 'snm_webhooks') THEN
        CREATE ROLE snm_webhooks WITH LOGIN ENCRYPTED PASSWORD 'insecure_placeholder_webhooks';
    END IF;

    -- Roles de solo lectura del Portal
    IF NOT EXISTS (SELECT FROM pg_catalog.pg_roles WHERE rolname = 'portal_lector_ingesta') THEN
        CREATE ROLE portal_lector_ingesta WITH LOGIN ENCRYPTED PASSWORD 'insecure_placeholder_lector_ingesta';
    END IF;

    IF NOT EXISTS (SELECT FROM pg_catalog.pg_roles WHERE rolname = 'portal_lector_alertas') THEN
        CREATE ROLE portal_lector_alertas WITH LOGIN ENCRYPTED PASSWORD 'insecure_placeholder_lector_alertas';
    END IF;
END
$$;

-- =============================================================================
-- 2. Creación de Bases de Datos (con codificación UTF8)
-- =============================================================================

SELECT 'CREATE DATABASE snm_portal OWNER snm_portal ENCODING ''UTF8'''
WHERE NOT EXISTS (SELECT FROM pg_database WHERE datname = 'snm_portal')\gexec

SELECT 'CREATE DATABASE snm_meshview OWNER snm_meshview ENCODING ''UTF8'''
WHERE NOT EXISTS (SELECT FROM pg_database WHERE datname = 'snm_meshview')\gexec

SELECT 'CREATE DATABASE snm_ingest OWNER snm_ingest ENCODING ''UTF8'''
WHERE NOT EXISTS (SELECT FROM pg_database WHERE datname = 'snm_ingest')\gexec

SELECT 'CREATE DATABASE snm_detector OWNER snm_detector ENCODING ''UTF8'''
WHERE NOT EXISTS (SELECT FROM pg_database WHERE datname = 'snm_detector')\gexec

SELECT 'CREATE DATABASE snm_potato OWNER snm_potato ENCODING ''UTF8'''
WHERE NOT EXISTS (SELECT FROM pg_database WHERE datname = 'snm_potato')\gexec

SELECT 'CREATE DATABASE snm_chatws OWNER snm_chatws ENCODING ''UTF8'''
WHERE NOT EXISTS (SELECT FROM pg_database WHERE datname = 'snm_chatws')\gexec

SELECT 'CREATE DATABASE peersync OWNER peersync ENCODING ''UTF8'''
WHERE NOT EXISTS (SELECT FROM pg_database WHERE datname = 'peersync')\gexec

SELECT 'CREATE DATABASE snm_bot_telegram OWNER snm_bot_telegram ENCODING ''UTF8'''
WHERE NOT EXISTS (SELECT FROM pg_database WHERE datname = 'snm_bot_telegram')\gexec

SELECT 'CREATE DATABASE snm_bot_discord OWNER snm_bot_discord ENCODING ''UTF8'''
WHERE NOT EXISTS (SELECT FROM pg_database WHERE datname = 'snm_bot_discord')\gexec

SELECT 'CREATE DATABASE snm_webhooks OWNER snm_webhooks ENCODING ''UTF8'''
WHERE NOT EXISTS (SELECT FROM pg_database WHERE datname = 'snm_webhooks')\gexec

-- =============================================================================
-- 3. Habilitación de TimescaleDB y Privilegios en snm_ingest
-- =============================================================================

\connect snm_ingest

-- Extensión TimescaleDB para hipertablas de paquetes y telemetría
CREATE EXTENSION IF NOT EXISTS timescaledb;

-- Permisos para el rol de solo lectura del portal
GRANT CONNECT ON DATABASE snm_ingest TO portal_lector_ingesta;
GRANT USAGE ON SCHEMA public TO portal_lector_ingesta;
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT SELECT ON TABLES TO portal_lector_ingesta;
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT SELECT ON SEQUENCES TO portal_lector_ingesta;

-- =============================================================================
-- 4. Permisos de solo lectura en snm_detector
-- =============================================================================

\connect snm_detector

GRANT CONNECT ON DATABASE snm_detector TO portal_lector_alertas;
GRANT USAGE ON SCHEMA public TO portal_lector_alertas;
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT SELECT ON TABLES TO portal_lector_alertas;
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT SELECT ON SEQUENCES TO portal_lector_alertas;

-- =============================================================================
-- Fin del aprovisionamiento de bases de datos
-- =============================================================================
