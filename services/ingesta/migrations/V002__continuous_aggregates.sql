-- ==============================================================================
-- V002__continuous_aggregates.sql
-- Agregados continuos horarios y diarios en TimescaleDB para snm_ingest
-- ==============================================================================

-- 1. agg_node_hour y agg_node_day (tráfico por nodo, provincia y portnum)
CREATE MATERIALIZED VIEW IF NOT EXISTS agg_node_hour
WITH (timescaledb.continuous, timescaledb.materialized_only = false) AS
SELECT
    time_bucket(INTERVAL '1 hour', rx_first) AS bucket,
    from_id,
    province,
    COALESCE(portnum, 'other') AS portnum,
    count(*) AS packets,
    sum(airtime_ms) / 1000.0 AS airtime_s
FROM packet
WHERE portnum IS NULL OR portnum != 'map_report'
GROUP BY 1, 2, 3, 4
WITH NO DATA;

SELECT add_continuous_aggregate_policy('agg_node_hour',
    start_offset => INTERVAL '3 hours',
    end_offset => INTERVAL '5 minutes',
    schedule_interval => INTERVAL '10 minutes',
    if_not_exists => TRUE);
SELECT add_retention_policy('agg_node_hour', INTERVAL '30 days', if_not_exists => TRUE);

CREATE MATERIALIZED VIEW IF NOT EXISTS agg_node_day
WITH (timescaledb.continuous, timescaledb.materialized_only = false) AS
SELECT
    time_bucket(INTERVAL '1 day', rx_first, 'Europe/Madrid') AS bucket,
    from_id,
    province,
    COALESCE(portnum, 'other') AS portnum,
    count(*) AS packets,
    sum(airtime_ms) / 1000.0 AS airtime_s
FROM packet
WHERE portnum IS NULL OR portnum != 'map_report'
GROUP BY 1, 2, 3, 4
WITH NO DATA;

SELECT add_continuous_aggregate_policy('agg_node_day',
    start_offset => INTERVAL '3 days',
    end_offset => INTERVAL '1 hour',
    schedule_interval => INTERVAL '30 minutes',
    if_not_exists => TRUE);
SELECT add_retention_policy('agg_node_day', INTERVAL '365 days', if_not_exists => TRUE);

-- 2. agg_reception_hour y agg_reception_day (actividad por gateway y nodo)
CREATE MATERIALIZED VIEW IF NOT EXISTS agg_reception_hour
WITH (timescaledb.continuous, timescaledb.materialized_only = false) AS
SELECT
    time_bucket(INTERVAL '1 hour', rx_at) AS bucket,
    gateway_id,
    from_id,
    count(*) AS receptions,
    count(*) FILTER (WHERE direct = true) AS direct,
    sum(snr) FILTER (WHERE direct = true) AS snr_direct_sum,
    max(distance_km) FILTER (WHERE direct = true) AS max_distance_km
FROM reception
WHERE own = false
GROUP BY 1, 2, 3
WITH NO DATA;

SELECT add_continuous_aggregate_policy('agg_reception_hour',
    start_offset => INTERVAL '3 hours',
    end_offset => INTERVAL '5 minutes',
    schedule_interval => INTERVAL '10 minutes',
    if_not_exists => TRUE);
SELECT add_retention_policy('agg_reception_hour', INTERVAL '30 days', if_not_exists => TRUE);

CREATE MATERIALIZED VIEW IF NOT EXISTS agg_reception_day
WITH (timescaledb.continuous, timescaledb.materialized_only = false) AS
SELECT
    time_bucket(INTERVAL '1 day', rx_at, 'Europe/Madrid') AS bucket,
    gateway_id,
    from_id,
    count(*) AS receptions,
    count(*) FILTER (WHERE direct = true) AS direct,
    sum(snr) FILTER (WHERE direct = true) AS snr_direct_sum,
    max(distance_km) FILTER (WHERE direct = true) AS max_distance_km
FROM reception
WHERE own = false
GROUP BY 1, 2, 3
WITH NO DATA;

SELECT add_continuous_aggregate_policy('agg_reception_day',
    start_offset => INTERVAL '3 days',
    end_offset => INTERVAL '1 hour',
    schedule_interval => INTERVAL '30 minutes',
    if_not_exists => TRUE);
SELECT add_retention_policy('agg_reception_day', INTERVAL '365 days', if_not_exists => TRUE);

-- 3. agg_gateway_hour y agg_gateway_day (rendimiento de gateways)
CREATE MATERIALIZED VIEW IF NOT EXISTS agg_gateway_hour
WITH (timescaledb.continuous, timescaledb.materialized_only = false) AS
SELECT
    time_bucket(INTERVAL '1 hour', rx_first) AS bucket,
    first_gateway,
    count(*) AS packets,
    count(*) FILTER (WHERE reception_count = 1) AS exclusive
FROM packet
WHERE portnum IS NULL OR portnum != 'map_report'
GROUP BY 1, 2
WITH NO DATA;

SELECT add_continuous_aggregate_policy('agg_gateway_hour',
    start_offset => INTERVAL '3 hours',
    end_offset => INTERVAL '5 minutes',
    schedule_interval => INTERVAL '10 minutes',
    if_not_exists => TRUE);
SELECT add_retention_policy('agg_gateway_hour', INTERVAL '30 days', if_not_exists => TRUE);

CREATE MATERIALIZED VIEW IF NOT EXISTS agg_gateway_day
WITH (timescaledb.continuous, timescaledb.materialized_only = false) AS
SELECT
    time_bucket(INTERVAL '1 day', rx_first, 'Europe/Madrid') AS bucket,
    first_gateway,
    count(*) AS packets,
    count(*) FILTER (WHERE reception_count = 1) AS exclusive
FROM packet
WHERE portnum IS NULL OR portnum != 'map_report'
GROUP BY 1, 2
WITH NO DATA;

SELECT add_continuous_aggregate_policy('agg_gateway_day',
    start_offset => INTERVAL '3 days',
    end_offset => INTERVAL '1 hour',
    schedule_interval => INTERVAL '30 minutes',
    if_not_exists => TRUE);
SELECT add_retention_policy('agg_gateway_day', INTERVAL '365 days', if_not_exists => TRUE);

-- 4. agg_text_hour y agg_text_day (mensajes de texto públicos)
CREATE MATERIALIZED VIEW IF NOT EXISTS agg_text_hour
WITH (timescaledb.continuous, timescaledb.materialized_only = false) AS
SELECT
    time_bucket(INTERVAL '1 hour', rx_first) AS bucket,
    from_id,
    channel,
    count(*) AS messages
FROM packet
WHERE portnum = 'text' AND to_id = '^all'
GROUP BY 1, 2, 3
WITH NO DATA;

SELECT add_continuous_aggregate_policy('agg_text_hour',
    start_offset => INTERVAL '3 hours',
    end_offset => INTERVAL '5 minutes',
    schedule_interval => INTERVAL '10 minutes',
    if_not_exists => TRUE);
SELECT add_retention_policy('agg_text_hour', INTERVAL '30 days', if_not_exists => TRUE);

CREATE MATERIALIZED VIEW IF NOT EXISTS agg_text_day
WITH (timescaledb.continuous, timescaledb.materialized_only = false) AS
SELECT
    time_bucket(INTERVAL '1 day', rx_first, 'Europe/Madrid') AS bucket,
    from_id,
    channel,
    count(*) AS messages
FROM packet
WHERE portnum = 'text' AND to_id = '^all'
GROUP BY 1, 2, 3
WITH NO DATA;

SELECT add_continuous_aggregate_policy('agg_text_day',
    start_offset => INTERVAL '3 days',
    end_offset => INTERVAL '1 hour',
    schedule_interval => INTERVAL '30 minutes',
    if_not_exists => TRUE);
SELECT add_retention_policy('agg_text_day', INTERVAL '365 days', if_not_exists => TRUE);

-- 5. agg_telemetry_hour y agg_telemetry_day (batería y salud de dispositivos)
CREATE MATERIALIZED VIEW IF NOT EXISTS agg_telemetry_hour
WITH (timescaledb.continuous, timescaledb.materialized_only = false) AS
SELECT
    time_bucket(INTERVAL '1 hour', at) AS bucket,
    node_id,
    count(*) FILTER (WHERE (battery_level BETWEEN 1 AND 100) OR (battery_level = 0 AND voltage > 0)) AS readings,
    min(battery_level) FILTER (WHERE (battery_level BETWEEN 1 AND 100) OR (battery_level = 0 AND voltage > 0)) AS min_level,
    max(battery_level) FILTER (WHERE (battery_level BETWEEN 1 AND 100) OR (battery_level = 0 AND voltage > 0)) AS max_level,
    sum(battery_level) FILTER (WHERE (battery_level BETWEEN 1 AND 100) OR (battery_level = 0 AND voltage > 0)) AS sum_level,
    count(*) FILTER (WHERE battery_level < 40 AND ((battery_level BETWEEN 1 AND 100) OR (battery_level = 0 AND voltage > 0))) AS readings_below_40,
    count(*) FILTER (WHERE battery_level > 100) AS powered_readings,
    max(uptime_seconds) AS max_uptime,
    count(*) FILTER (WHERE reboot = true) AS reboots
FROM telemetry_device
GROUP BY 1, 2
WITH NO DATA;

SELECT add_continuous_aggregate_policy('agg_telemetry_hour',
    start_offset => INTERVAL '3 hours',
    end_offset => INTERVAL '5 minutes',
    schedule_interval => INTERVAL '10 minutes',
    if_not_exists => TRUE);
SELECT add_retention_policy('agg_telemetry_hour', INTERVAL '30 days', if_not_exists => TRUE);

CREATE MATERIALIZED VIEW IF NOT EXISTS agg_telemetry_day
WITH (timescaledb.continuous, timescaledb.materialized_only = false) AS
SELECT
    time_bucket(INTERVAL '1 day', at, 'Europe/Madrid') AS bucket,
    node_id,
    count(*) FILTER (WHERE (battery_level BETWEEN 1 AND 100) OR (battery_level = 0 AND voltage > 0)) AS readings,
    min(battery_level) FILTER (WHERE (battery_level BETWEEN 1 AND 100) OR (battery_level = 0 AND voltage > 0)) AS min_level,
    max(battery_level) FILTER (WHERE (battery_level BETWEEN 1 AND 100) OR (battery_level = 0 AND voltage > 0)) AS max_level,
    sum(battery_level) FILTER (WHERE (battery_level BETWEEN 1 AND 100) OR (battery_level = 0 AND voltage > 0)) AS sum_level,
    count(*) FILTER (WHERE battery_level < 40 AND ((battery_level BETWEEN 1 AND 100) OR (battery_level = 0 AND voltage > 0))) AS readings_below_40,
    count(*) FILTER (WHERE battery_level > 100) AS powered_readings,
    max(uptime_seconds) AS max_uptime,
    count(*) FILTER (WHERE reboot = true) AS reboots
FROM telemetry_device
GROUP BY 1, 2
WITH NO DATA;

SELECT add_continuous_aggregate_policy('agg_telemetry_day',
    start_offset => INTERVAL '3 days',
    end_offset => INTERVAL '1 hour',
    schedule_interval => INTERVAL '30 minutes',
    if_not_exists => TRUE);
SELECT add_retention_policy('agg_telemetry_day', INTERVAL '365 days', if_not_exists => TRUE);

-- 6. agg_neighbor_hour y agg_neighbor_day (reportes de vecindad)
CREATE MATERIALIZED VIEW IF NOT EXISTS agg_neighbor_hour
WITH (timescaledb.continuous, timescaledb.materialized_only = false) AS
SELECT
    time_bucket(INTERVAL '1 hour', at) AS bucket,
    node_id,
    neighbor_id,
    count(*) AS reports,
    sum(snr) AS snr_sum
FROM neighbor
GROUP BY 1, 2, 3
WITH NO DATA;

SELECT add_continuous_aggregate_policy('agg_neighbor_hour',
    start_offset => INTERVAL '3 hours',
    end_offset => INTERVAL '5 minutes',
    schedule_interval => INTERVAL '10 minutes',
    if_not_exists => TRUE);
SELECT add_retention_policy('agg_neighbor_hour', INTERVAL '30 days', if_not_exists => TRUE);

CREATE MATERIALIZED VIEW IF NOT EXISTS agg_neighbor_day
WITH (timescaledb.continuous, timescaledb.materialized_only = false) AS
SELECT
    time_bucket(INTERVAL '1 day', at, 'Europe/Madrid') AS bucket,
    node_id,
    neighbor_id,
    count(*) AS reports,
    sum(snr) AS snr_sum
FROM neighbor
GROUP BY 1, 2, 3
WITH NO DATA;

SELECT add_continuous_aggregate_policy('agg_neighbor_day',
    start_offset => INTERVAL '3 days',
    end_offset => INTERVAL '1 hour',
    schedule_interval => INTERVAL '30 minutes',
    if_not_exists => TRUE);
SELECT add_retention_policy('agg_neighbor_day', INTERVAL '365 days', if_not_exists => TRUE);

-- 7. agg_traffic_hour y agg_traffic_day (tráfico global sin retención)
CREATE MATERIALIZED VIEW IF NOT EXISTS agg_traffic_hour
WITH (timescaledb.continuous, timescaledb.materialized_only = false) AS
SELECT
    time_bucket(INTERVAL '1 hour', rx_first) AS bucket,
    COALESCE(portnum, 'other') AS portnum,
    count(*) AS packets,
    sum(airtime_ms) / 1000.0 AS airtime_s
FROM packet
WHERE portnum IS NULL OR portnum != 'map_report'
GROUP BY 1, 2
WITH NO DATA;

SELECT add_continuous_aggregate_policy('agg_traffic_hour',
    start_offset => INTERVAL '3 hours',
    end_offset => INTERVAL '5 minutes',
    schedule_interval => INTERVAL '10 minutes',
    if_not_exists => TRUE);

CREATE MATERIALIZED VIEW IF NOT EXISTS agg_traffic_day
WITH (timescaledb.continuous, timescaledb.materialized_only = false) AS
SELECT
    time_bucket(INTERVAL '1 day', rx_first, 'Europe/Madrid') AS bucket,
    COALESCE(portnum, 'other') AS portnum,
    count(*) AS packets,
    sum(airtime_ms) / 1000.0 AS airtime_s
FROM packet
WHERE portnum IS NULL OR portnum != 'map_report'
GROUP BY 1, 2
WITH NO DATA;

SELECT add_continuous_aggregate_policy('agg_traffic_day',
    start_offset => INTERVAL '3 days',
    end_offset => INTERVAL '1 hour',
    schedule_interval => INTERVAL '30 minutes',
    if_not_exists => TRUE);
