-- ==============================================================================
-- V001__initial_schema.sql
-- Esquema inicial de tablas maestras e hipertablas TimescaleDB para snm_ingest
-- ==============================================================================

-- 1. Control de versiones de migraciones
CREATE TABLE IF NOT EXISTS schema_migrations (
    version text PRIMARY KEY,
    checksum text NOT NULL,
    applied_at timestamptz NOT NULL DEFAULT now()
);

-- 2. Tablas maestras de nodos y gateways (tablas normales en esquema public)
CREATE TABLE IF NOT EXISTS node (
    id text PRIMARY KEY,
    node_num bigint,
    short_name text,
    long_name text,
    role text,
    hw_model text,
    firmware text,
    public_key_fp text,
    latitude double precision,
    longitude double precision,
    position_precision_m real,
    position_source text,
    last_position_at timestamptz,
    province text,
    border_uncertain boolean DEFAULT false,
    hop_start_last smallint,
    hops_min_last smallint,
    is_gateway boolean DEFAULT false,
    gateway_first_at timestamptz,
    battery_level smallint,
    voltage real,
    battery_at timestamptz,
    channel_utilization real,
    air_util_tx real,
    metrics_at timestamptz,
    uptime_seconds bigint,
    uptime_at timestamptz,
    last_reboot_at timestamptz,
    first_seen timestamptz NOT NULL,
    last_seen timestamptz NOT NULL
);

CREATE INDEX IF NOT EXISTS idx_node_last_seen ON node (last_seen DESC);
CREATE INDEX IF NOT EXISTS idx_node_province ON node (province) WHERE province IS NOT NULL;
CREATE INDEX IF NOT EXISTS idx_node_metrics_at ON node (metrics_at DESC);

CREATE TABLE IF NOT EXISTS gateway (
    id text PRIMARY KEY,
    first_message_at timestamptz NOT NULL,
    last_message_at timestamptz NOT NULL,
    messages_total bigint DEFAULT 1,
    typical_interval_s real
);

CREATE INDEX IF NOT EXISTS idx_gateway_last_message ON gateway (last_message_at DESC);

-- 3. Hipertabla: packet (un registro por paquete único)
CREATE TABLE IF NOT EXISTS packet (
    rx_first timestamptz NOT NULL,
    from_id text NOT NULL,
    to_id text NOT NULL,
    packet_id bigint NOT NULL,
    channel text,
    portnum text,
    portnum_num integer,
    variant text,
    decrypt_status text NOT NULL,
    hop_start smallint,
    hops_min smallint,
    reception_count smallint DEFAULT 1,
    want_ack boolean DEFAULT false,
    via_mqtt boolean DEFAULT false,
    ok_to_mqtt boolean,
    size_bytes smallint,
    airtime_ms real,
    first_gateway text NOT NULL,
    province text,
    payload jsonb DEFAULT '{}'::jsonb
);

SELECT create_hypertable('packet', 'rx_first', chunk_time_interval => INTERVAL '1 day', if_not_exists => TRUE);
CREATE UNIQUE INDEX IF NOT EXISTS idx_packet_uniq ON packet (from_id, packet_id, rx_first);
CREATE INDEX IF NOT EXISTS idx_packet_from_rx ON packet (from_id, rx_first DESC);
CREATE INDEX IF NOT EXISTS idx_packet_portnum ON packet (portnum, rx_first DESC);

ALTER TABLE packet SET (
    timescaledb.compress,
    timescaledb.compress_segmentby = 'from_id, packet_id',
    timescaledb.compress_orderby = 'rx_first DESC'
);
SELECT add_compression_policy('packet', INTERVAL '2 days', if_not_exists => TRUE);
SELECT add_retention_policy('packet', INTERVAL '30 days', if_not_exists => TRUE);

-- 4. Hipertabla: reception (una fila por cada gateway que escucha el paquete)
CREATE TABLE IF NOT EXISTS reception (
    rx_at timestamptz NOT NULL,
    from_id text NOT NULL,
    packet_id bigint NOT NULL,
    gateway_id text NOT NULL,
    snr real,
    rssi smallint,
    hop_limit smallint,
    hops smallint,
    relay_node smallint,
    gw_rx_time timestamptz,
    own boolean DEFAULT false,
    direct boolean DEFAULT false,
    distance_km real
);

SELECT create_hypertable('reception', 'rx_at', chunk_time_interval => INTERVAL '1 day', if_not_exists => TRUE);
CREATE INDEX IF NOT EXISTS idx_reception_gw_rx ON reception (gateway_id, rx_at DESC);
CREATE INDEX IF NOT EXISTS idx_reception_from_rx ON reception (from_id, rx_at DESC);

ALTER TABLE reception SET (
    timescaledb.compress,
    timescaledb.compress_segmentby = 'gateway_id',
    timescaledb.compress_orderby = 'rx_at DESC'
);
SELECT add_compression_policy('reception', INTERVAL '2 days', if_not_exists => TRUE);
SELECT add_retention_policy('reception', INTERVAL '30 days', if_not_exists => TRUE);

-- 5. Hipertabla: neighbor (vecinos detectados en neighborinfo)
CREATE TABLE IF NOT EXISTS neighbor (
    at timestamptz NOT NULL,
    node_id text NOT NULL,
    neighbor_id text NOT NULL,
    snr real NOT NULL
);

SELECT create_hypertable('neighbor', 'at', chunk_time_interval => INTERVAL '1 day', if_not_exists => TRUE);
CREATE INDEX IF NOT EXISTS idx_neighbor_node ON neighbor (node_id, at DESC);

ALTER TABLE neighbor SET (
    timescaledb.compress,
    timescaledb.compress_segmentby = 'node_id',
    timescaledb.compress_orderby = 'at DESC'
);
SELECT add_compression_policy('neighbor', INTERVAL '2 days', if_not_exists => TRUE);
SELECT add_retention_policy('neighbor', INTERVAL '30 days', if_not_exists => TRUE);

-- 6. Hipertabla: position (histórico de posiciones válidas)
CREATE TABLE IF NOT EXISTS position (
    at timestamptz NOT NULL,
    node_id text NOT NULL,
    latitude double precision NOT NULL,
    longitude double precision NOT NULL,
    altitude integer,
    precision_bits smallint,
    precision_m real,
    source text NOT NULL,
    gps_time timestamptz,
    province text,
    border_uncertain boolean DEFAULT false
);

SELECT create_hypertable('position', 'at', chunk_time_interval => INTERVAL '7 days', if_not_exists => TRUE);
CREATE INDEX IF NOT EXISTS idx_position_node ON position (node_id, at DESC);

ALTER TABLE position SET (
    timescaledb.compress,
    timescaledb.compress_segmentby = 'node_id',
    timescaledb.compress_orderby = 'at DESC'
);
SELECT add_compression_policy('position', INTERVAL '7 days', if_not_exists => TRUE);
SELECT add_retention_policy('position', INTERVAL '90 days', if_not_exists => TRUE);

-- 7. Hipertabla: telemetry_device (métricas de dispositivo y batería)
CREATE TABLE IF NOT EXISTS telemetry_device (
    at timestamptz NOT NULL,
    node_id text NOT NULL,
    battery_level smallint,
    voltage real,
    channel_utilization real,
    air_util_tx real,
    uptime_seconds bigint,
    reboot boolean DEFAULT false
);

SELECT create_hypertable('telemetry_device', 'at', chunk_time_interval => INTERVAL '7 days', if_not_exists => TRUE);
CREATE INDEX IF NOT EXISTS idx_telem_dev_node ON telemetry_device (node_id, at DESC);

ALTER TABLE telemetry_device SET (
    timescaledb.compress,
    timescaledb.compress_segmentby = 'node_id',
    timescaledb.compress_orderby = 'at DESC'
);
SELECT add_compression_policy('telemetry_device', INTERVAL '7 days', if_not_exists => TRUE);
SELECT add_retention_policy('telemetry_device', INTERVAL '90 days', if_not_exists => TRUE);

-- 8. Hipertabla: telemetry_env (métricas meteorológicas y de entorno)
CREATE TABLE IF NOT EXISTS telemetry_env (
    at timestamptz NOT NULL,
    node_id text NOT NULL,
    metrics jsonb NOT NULL
);

SELECT create_hypertable('telemetry_env', 'at', chunk_time_interval => INTERVAL '7 days', if_not_exists => TRUE);
CREATE INDEX IF NOT EXISTS idx_telem_env_node ON telemetry_env (node_id, at DESC);

ALTER TABLE telemetry_env SET (
    timescaledb.compress,
    timescaledb.compress_segmentby = 'node_id',
    timescaledb.compress_orderby = 'at DESC'
);
SELECT add_compression_policy('telemetry_env', INTERVAL '7 days', if_not_exists => TRUE);
SELECT add_retention_policy('telemetry_env', INTERVAL '90 days', if_not_exists => TRUE);

-- 9. Hipertabla: local_stats (estadísticas locales de firmware)
CREATE TABLE IF NOT EXISTS local_stats (
    at timestamptz NOT NULL,
    node_id text NOT NULL,
    uptime_seconds bigint,
    channel_utilization real,
    air_util_tx real,
    num_packets_tx integer,
    num_packets_rx integer,
    num_packets_rx_bad integer,
    num_rx_dupe integer,
    num_tx_relay integer,
    num_tx_relay_canceled integer,
    num_online_nodes integer,
    num_total_nodes integer
);

SELECT create_hypertable('local_stats', 'at', chunk_time_interval => INTERVAL '7 days', if_not_exists => TRUE);
CREATE INDEX IF NOT EXISTS idx_local_stats_node ON local_stats (node_id, at DESC);

ALTER TABLE local_stats SET (
    timescaledb.compress,
    timescaledb.compress_segmentby = 'node_id',
    timescaledb.compress_orderby = 'at DESC'
);
SELECT add_compression_policy('local_stats', INTERVAL '7 days', if_not_exists => TRUE);
SELECT add_retention_policy('local_stats', INTERVAL '90 days', if_not_exists => TRUE);
