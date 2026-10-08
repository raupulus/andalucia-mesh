-- ==============================================================================
-- R__vistas_api.sql
-- Vistas contrato para el Portal y la API pública en snm_ingest
-- Se aplica de forma repetible cada vez que cambia su definición
-- Variables sustituidas por el runner: {{TZ}}, {{INFRA_ROLES}}
-- ==============================================================================

-- 1. api_nodes (catálogo de nodos para mapa, búsqueda y diagnóstico)
CREATE OR REPLACE VIEW api_nodes AS
SELECT
    n.id,
    n.short_name,
    n.long_name,
    n.role,
    n.hw_model,
    n.firmware,
    n.province,
    n.last_position_at,
    n.position_precision_m,
    n.border_uncertain,
    n.hop_start_last,
    n.is_gateway,
    n.first_seen,
    n.last_seen,
    (n.role IN ({{INFRA_ROLES}})) AS is_router,
    n.hops_min_last,
    n.metrics_at AS nodeinfo_at,
    ARRAY(
        SELECT DISTINCT ard.gateway_id
        FROM agg_reception_day ard
        WHERE ard.from_id = n.id
          AND ard.bucket >= (now() - INTERVAL '7 days')
          AND ard.gateway_id != n.id
    ) AS heard_by
FROM node n;

-- 2. api_province_load (carga de canal para mapa de calor provincial)
CREATE OR REPLACE VIEW api_province_load AS
SELECT
    n.id AS node_id,
    n.province,
    n.channel_utilization,
    n.metrics_at AS measured_at,
    CASE
        WHEN n.role IN ({{INFRA_ROLES}}) THEN 'router'
        ELSE 'cliente'
    END AS grupo,
    n.role,
    n.is_gateway,
    n.air_util_tx
FROM node n
WHERE n.province IS NOT NULL
  AND n.province != 'FUERA'
  AND n.metrics_at >= (now() - INTERVAL '30 days')
  AND n.channel_utilization IS NOT NULL
  AND (n.role IN ({{INFRA_ROLES}}) OR n.role IN ('CLIENT', 'CLIENT_BASE'));

-- 3. api_routers (routers y repetidores activos en los últimos 7 días)
CREATE OR REPLACE VIEW api_routers AS
SELECT
    n.id,
    n.short_name,
    n.long_name,
    n.role,
    n.province,
    n.battery_level,
    n.voltage,
    n.battery_at,
    n.channel_utilization,
    n.air_util_tx,
    n.metrics_at,
    n.last_seen,
    n.hw_model,
    (n.battery_level > 100) AS powered,
    n.is_gateway,
    n.last_reboot_at
FROM node n
WHERE n.role IN ({{INFRA_ROLES}})
  AND n.last_seen >= (now() - INTERVAL '7 days');

-- 4. api_gateways (gateways comunitarios registrados y estadísticas)
CREATE OR REPLACE VIEW api_gateways AS
SELECT
    g.id,
    n.short_name,
    n.long_name,
    g.last_message_at,
    g.typical_interval_s,
    (
        SELECT count(*)::integer
        FROM reception r
        WHERE r.gateway_id = g.id
          AND r.rx_at >= (now() - INTERVAL '1 hour')
    ) AS packets_last_hour,
    (
        SELECT count(DISTINCT arh.from_id)::integer
        FROM agg_reception_hour arh
        WHERE arh.gateway_id = g.id
          AND arh.bucket >= (now() - INTERVAL '24 hours')
          AND arh.from_id != g.id
    ) AS unique_nodes_24h,
    g.first_message_at,
    g.messages_total,
    n.province
FROM gateway g
LEFT JOIN node n ON g.id = n.id;

-- 5. api_summary (resumen ejecutivo de la red en una sola fila)
CREATE OR REPLACE VIEW api_summary AS
SELECT
    (SELECT count(*)::integer FROM node WHERE last_seen >= (now() - INTERVAL '24 hours')) AS nodes_active_24h,
    (SELECT count(*)::integer FROM node WHERE last_seen >= (now() - INTERVAL '7 days')) AS nodes_active_7d,
    (SELECT count(*)::integer FROM node WHERE role IN ({{INFRA_ROLES}}) AND last_seen >= (now() - INTERVAL '24 hours')) AS routers_active_24h,
    (SELECT count(*)::integer FROM gateway WHERE last_message_at >= (now() - INTERVAL '15 minutes')) AS gateways_publishing,
    (SELECT count(*)::integer FROM packet WHERE rx_first >= (now() - INTERVAL '1 hour') AND (portnum IS NULL OR portnum != 'map_report')) AS packets_last_hour,
    now() AS generated_at;

-- 6. api_traffic_mix (distribución de tráfico por tipo y periodo)
CREATE OR REPLACE VIEW api_traffic_mix AS
-- Granularidad: hour
SELECT
    bucket AS bucket_start,
    'hour'::text AS granularity,
    portnum,
    packets,
    airtime_s
FROM agg_traffic_hour
UNION ALL
-- Granularidad: day
SELECT
    bucket AS bucket_start,
    'day'::text AS granularity,
    portnum,
    packets,
    airtime_s
FROM agg_traffic_day
UNION ALL
-- Granularidad: week (semanas ISO)
SELECT
    date_trunc('week', bucket)::timestamptz AS bucket_start,
    'week'::text AS granularity,
    portnum,
    sum(packets)::bigint AS packets,
    sum(airtime_s)::double precision AS airtime_s
FROM agg_traffic_day
GROUP BY 1, 2, 3
UNION ALL
-- Granularidad: month (meses naturales)
SELECT
    date_trunc('month', bucket)::timestamptz AS bucket_start,
    'month'::text AS granularity,
    portnum,
    sum(packets)::bigint AS packets,
    sum(airtime_s)::double precision AS airtime_s
FROM agg_traffic_day
GROUP BY 1, 2, 3;

-- 7. Rankings de la red (api_rank_<id>)
-- 7.1 api_rank_network_usage (Uso del espectro y tiempo de aire)
CREATE OR REPLACE VIEW api_rank_network_usage AS
SELECT
    bucket AS bucket_start,
    'hour'::text AS granularity,
    from_id AS subject_id,
    sum(airtime_s)::double precision AS value,
    jsonb_build_object('packets', sum(packets), 'airtime_s', round(sum(airtime_s)::numeric, 2)) AS extra
FROM agg_node_hour
GROUP BY 1, 2, 3
UNION ALL
SELECT
    bucket AS bucket_start,
    'day'::text AS granularity,
    from_id AS subject_id,
    sum(airtime_s)::double precision AS value,
    jsonb_build_object('packets', sum(packets), 'airtime_s', round(sum(airtime_s)::numeric, 2)) AS extra
FROM agg_node_day
GROUP BY 1, 2, 3
UNION ALL
SELECT
    date_trunc('week', bucket)::timestamptz AS bucket_start,
    'week'::text AS granularity,
    from_id AS subject_id,
    sum(airtime_s)::double precision AS value,
    jsonb_build_object('packets', sum(packets), 'airtime_s', round(sum(airtime_s)::numeric, 2)) AS extra
FROM agg_node_day
WHERE bucket >= (now() - INTERVAL '70 days')
GROUP BY 1, 2, 3
UNION ALL
SELECT
    date_trunc('month', bucket)::timestamptz AS bucket_start,
    'month'::text AS granularity,
    from_id AS subject_id,
    sum(airtime_s)::double precision AS value,
    jsonb_build_object('packets', sum(packets), 'airtime_s', round(sum(airtime_s)::numeric, 2)) AS extra
FROM agg_node_day
WHERE bucket >= (now() - INTERVAL '70 days')
GROUP BY 1, 2, 3;

CREATE OR REPLACE VIEW api_rank_airtime AS SELECT * FROM api_rank_network_usage;

-- 7.2 api_rank_gateway_coverage (Cobertura de gateways)
CREATE OR REPLACE VIEW api_rank_gateway_coverage AS
SELECT
    bucket AS bucket_start,
    'hour'::text AS granularity,
    gateway_id AS subject_id,
    count(DISTINCT from_id)::double precision AS value,
    jsonb_build_object('gateway_id', gateway_id, 'nodes', count(DISTINCT from_id), 'direct_links', sum(direct)) AS extra
FROM agg_reception_hour
GROUP BY 1, 2, 3
UNION ALL
SELECT
    bucket AS bucket_start,
    'day'::text AS granularity,
    gateway_id AS subject_id,
    count(DISTINCT from_id)::double precision AS value,
    jsonb_build_object('gateway_id', gateway_id, 'nodes', count(DISTINCT from_id), 'direct_links', sum(direct)) AS extra
FROM agg_reception_day
GROUP BY 1, 2, 3
UNION ALL
SELECT
    date_trunc('week', bucket)::timestamptz AS bucket_start,
    'week'::text AS granularity,
    gateway_id AS subject_id,
    count(DISTINCT from_id)::double precision AS value,
    jsonb_build_object('gateway_id', gateway_id, 'nodes', count(DISTINCT from_id), 'direct_links', sum(direct)) AS extra
FROM agg_reception_day
WHERE bucket >= (now() - INTERVAL '70 days')
GROUP BY 1, 2, 3
UNION ALL
SELECT
    date_trunc('month', bucket)::timestamptz AS bucket_start,
    'month'::text AS granularity,
    gateway_id AS subject_id,
    count(DISTINCT from_id)::double precision AS value,
    jsonb_build_object('gateway_id', gateway_id, 'nodes', count(DISTINCT from_id), 'direct_links', sum(direct)) AS extra
FROM agg_reception_day
WHERE bucket >= (now() - INTERVAL '70 days')
GROUP BY 1, 2, 3;

CREATE OR REPLACE VIEW api_rank_gateways_heard AS SELECT * FROM api_rank_gateway_coverage;

-- 7.3 api_rank_gateway_exclusive (Gateways exclusivos)
CREATE OR REPLACE VIEW api_rank_gateway_exclusive AS
SELECT
    bucket AS bucket_start,
    'hour'::text AS granularity,
    first_gateway AS subject_id,
    sum(exclusive)::double precision AS value,
    jsonb_build_object('gateway_id', first_gateway, 'packets', sum(packets), 'exclusive', sum(exclusive)) AS extra
FROM agg_gateway_hour
GROUP BY 1, 2, 3
UNION ALL
SELECT
    bucket AS bucket_start,
    'day'::text AS granularity,
    first_gateway AS subject_id,
    sum(exclusive)::double precision AS value,
    jsonb_build_object('gateway_id', first_gateway, 'packets', sum(packets), 'exclusive', sum(exclusive)) AS extra
FROM agg_gateway_day
GROUP BY 1, 2, 3
UNION ALL
SELECT
    date_trunc('week', bucket)::timestamptz AS bucket_start,
    'week'::text AS granularity,
    first_gateway AS subject_id,
    sum(exclusive)::double precision AS value,
    jsonb_build_object('gateway_id', first_gateway, 'packets', sum(packets), 'exclusive', sum(exclusive)) AS extra
FROM agg_gateway_day
WHERE bucket >= (now() - INTERVAL '70 days')
GROUP BY 1, 2, 3
UNION ALL
SELECT
    date_trunc('month', bucket)::timestamptz AS bucket_start,
    'month'::text AS granularity,
    first_gateway AS subject_id,
    sum(exclusive)::double precision AS value,
    jsonb_build_object('gateway_id', first_gateway, 'packets', sum(packets), 'exclusive', sum(exclusive)) AS extra
FROM agg_gateway_day
WHERE bucket >= (now() - INTERVAL '70 days')
GROUP BY 1, 2, 3;

-- 7.4 api_rank_longest_links (Enlaces directos de mayor alcance)
CREATE OR REPLACE VIEW api_rank_longest_links AS
SELECT
    bucket AS bucket_start,
    'hour'::text AS granularity,
    from_id AS subject_id,
    max(max_distance_km)::double precision AS value,
    jsonb_build_object('gateway', gateway_id, 'distance_km', max(max_distance_km)) AS extra
FROM agg_reception_hour
WHERE direct > 0 AND max_distance_km IS NOT NULL
GROUP BY 1, 2, 3, gateway_id
UNION ALL
SELECT
    bucket AS bucket_start,
    'day'::text AS granularity,
    from_id AS subject_id,
    max(max_distance_km)::double precision AS value,
    jsonb_build_object('gateway', gateway_id, 'distance_km', max(max_distance_km)) AS extra
FROM agg_reception_day
WHERE direct > 0 AND max_distance_km IS NOT NULL
GROUP BY 1, 2, 3, gateway_id
UNION ALL
SELECT
    date_trunc('week', bucket)::timestamptz AS bucket_start,
    'week'::text AS granularity,
    from_id AS subject_id,
    max(max_distance_km)::double precision AS value,
    jsonb_build_object('gateway', gateway_id, 'distance_km', max(max_distance_km)) AS extra
FROM agg_reception_day
WHERE direct > 0 AND max_distance_km IS NOT NULL AND bucket >= (now() - INTERVAL '70 days')
GROUP BY 1, 2, 3, gateway_id
UNION ALL
SELECT
    date_trunc('month', bucket)::timestamptz AS bucket_start,
    'month'::text AS granularity,
    from_id AS subject_id,
    max(max_distance_km)::double precision AS value,
    jsonb_build_object('gateway', gateway_id, 'distance_km', max(max_distance_km)) AS extra
FROM agg_reception_day
WHERE direct > 0 AND max_distance_km IS NOT NULL AND bucket >= (now() - INTERVAL '70 days')
GROUP BY 1, 2, 3, gateway_id;

-- 7.5 api_rank_best_links (Mejor calidad de enlace directo SNR)
CREATE OR REPLACE VIEW api_rank_best_links AS
SELECT
    bucket AS bucket_start,
    'hour'::text AS granularity,
    from_id AS subject_id,
    round((sum(snr_direct_sum) / NULLIF(sum(direct), 0))::numeric, 2)::double precision AS value,
    jsonb_build_object('gateway', gateway_id, 'direct_receptions', sum(direct)) AS extra
FROM agg_reception_hour
WHERE direct >= 3
GROUP BY 1, 2, 3, gateway_id
UNION ALL
SELECT
    bucket AS bucket_start,
    'day'::text AS granularity,
    from_id AS subject_id,
    round((sum(snr_direct_sum) / NULLIF(sum(direct), 0))::numeric, 2)::double precision AS value,
    jsonb_build_object('gateway', gateway_id, 'direct_receptions', sum(direct)) AS extra
FROM agg_reception_day
WHERE direct >= 5
GROUP BY 1, 2, 3, gateway_id
UNION ALL
SELECT
    date_trunc('week', bucket)::timestamptz AS bucket_start,
    'week'::text AS granularity,
    from_id AS subject_id,
    round((sum(snr_direct_sum) / NULLIF(sum(direct), 0))::numeric, 2)::double precision AS value,
    jsonb_build_object('gateway', gateway_id, 'direct_receptions', sum(direct)) AS extra
FROM agg_reception_day
WHERE direct >= 10 AND bucket >= (now() - INTERVAL '70 days')
GROUP BY 1, 2, 3, gateway_id
UNION ALL
SELECT
    date_trunc('month', bucket)::timestamptz AS bucket_start,
    'month'::text AS granularity,
    from_id AS subject_id,
    round((sum(snr_direct_sum) / NULLIF(sum(direct), 0))::numeric, 2)::double precision AS value,
    jsonb_build_object('gateway', gateway_id, 'direct_receptions', sum(direct)) AS extra
FROM agg_reception_day
WHERE direct >= 10 AND bucket >= (now() - INTERVAL '70 days')
GROUP BY 1, 2, 3, gateway_id;

-- 7.6 api_rank_most_neighbors (Nodos con mayor vecindad directa)
CREATE OR REPLACE VIEW api_rank_most_neighbors AS
SELECT
    bucket AS bucket_start,
    'hour'::text AS granularity,
    node_id AS subject_id,
    count(DISTINCT neighbor_id)::double precision AS value,
    jsonb_build_object('node_id', node_id, 'unique_neighbors', count(DISTINCT neighbor_id)) AS extra
FROM agg_neighbor_hour
GROUP BY 1, 2, 3
UNION ALL
SELECT
    bucket AS bucket_start,
    'day'::text AS granularity,
    node_id AS subject_id,
    count(DISTINCT neighbor_id)::double precision AS value,
    jsonb_build_object('node_id', node_id, 'unique_neighbors', count(DISTINCT neighbor_id)) AS extra
FROM agg_neighbor_day
GROUP BY 1, 2, 3
UNION ALL
SELECT
    date_trunc('week', bucket)::timestamptz AS bucket_start,
    'week'::text AS granularity,
    node_id AS subject_id,
    count(DISTINCT neighbor_id)::double precision AS value,
    jsonb_build_object('node_id', node_id, 'unique_neighbors', count(DISTINCT neighbor_id)) AS extra
FROM agg_neighbor_day
WHERE bucket >= (now() - INTERVAL '70 days')
GROUP BY 1, 2, 3
UNION ALL
SELECT
    date_trunc('month', bucket)::timestamptz AS bucket_start,
    'month'::text AS granularity,
    node_id AS subject_id,
    count(DISTINCT neighbor_id)::double precision AS value,
    jsonb_build_object('node_id', node_id, 'unique_neighbors', count(DISTINCT neighbor_id)) AS extra
FROM agg_neighbor_day
WHERE bucket >= (now() - INTERVAL '70 days')
GROUP BY 1, 2, 3;

-- 7.7 api_rank_uptime (Tiempo activo ininterrumpido)
CREATE OR REPLACE VIEW api_rank_uptime AS
SELECT
    bucket AS bucket_start,
    'hour'::text AS granularity,
    node_id AS subject_id,
    round((max(max_uptime) / 3600.0)::numeric, 1)::double precision AS value,
    jsonb_build_object('node_id', node_id, 'uptime_seconds', max(max_uptime), 'readings', sum(readings)) AS extra
FROM agg_telemetry_hour
WHERE max_uptime IS NOT NULL
GROUP BY 1, 2, 3
UNION ALL
SELECT
    bucket AS bucket_start,
    'day'::text AS granularity,
    node_id AS subject_id,
    round((max(max_uptime) / 3600.0)::numeric, 1)::double precision AS value,
    jsonb_build_object('node_id', node_id, 'uptime_seconds', max(max_uptime), 'readings', sum(readings)) AS extra
FROM agg_telemetry_day
WHERE max_uptime IS NOT NULL
GROUP BY 1, 2, 3
UNION ALL
SELECT
    date_trunc('week', bucket)::timestamptz AS bucket_start,
    'week'::text AS granularity,
    node_id AS subject_id,
    round((max(max_uptime) / 3600.0)::numeric, 1)::double precision AS value,
    jsonb_build_object('node_id', node_id, 'uptime_seconds', max(max_uptime), 'readings', sum(readings)) AS extra
FROM agg_telemetry_day
WHERE max_uptime IS NOT NULL AND bucket >= (now() - INTERVAL '70 days')
GROUP BY 1, 2, 3
UNION ALL
SELECT
    date_trunc('month', bucket)::timestamptz AS bucket_start,
    'month'::text AS granularity,
    node_id AS subject_id,
    round((max(max_uptime) / 3600.0)::numeric, 1)::double precision AS value,
    jsonb_build_object('node_id', node_id, 'uptime_seconds', max(max_uptime), 'readings', sum(readings)) AS extra
FROM agg_telemetry_day
WHERE max_uptime IS NOT NULL AND bucket >= (now() - INTERVAL '70 days')
GROUP BY 1, 2, 3;

-- 7.8 api_rank_solar_health (Salud de nodos solares)
CREATE OR REPLACE VIEW api_rank_solar_health AS
SELECT
    bucket AS bucket_start,
    'hour'::text AS granularity,
    node_id AS subject_id,
    min(min_level)::double precision AS value,
    jsonb_build_object('node_id', node_id, 'min_level', min(min_level), 'avg_level', round((sum(sum_level) / NULLIF(sum(readings), 0))::numeric, 1), 'readings', sum(readings)) AS extra
FROM agg_telemetry_hour
WHERE min_level IS NOT NULL
GROUP BY 1, 2, 3
UNION ALL
SELECT
    bucket AS bucket_start,
    'day'::text AS granularity,
    node_id AS subject_id,
    min(min_level)::double precision AS value,
    jsonb_build_object('node_id', node_id, 'min_level', min(min_level), 'avg_level', round((sum(sum_level) / NULLIF(sum(readings), 0))::numeric, 1), 'readings', sum(readings)) AS extra
FROM agg_telemetry_day
WHERE min_level IS NOT NULL
GROUP BY 1, 2, 3
UNION ALL
SELECT
    date_trunc('week', bucket)::timestamptz AS bucket_start,
    'week'::text AS granularity,
    node_id AS subject_id,
    min(min_level)::double precision AS value,
    jsonb_build_object('node_id', node_id, 'min_level', min(min_level), 'avg_level', round((sum(sum_level) / NULLIF(sum(readings), 0))::numeric, 1), 'readings', sum(readings)) AS extra
FROM agg_telemetry_day
WHERE min_level IS NOT NULL AND bucket >= (now() - INTERVAL '70 days')
GROUP BY 1, 2, 3
UNION ALL
SELECT
    date_trunc('month', bucket)::timestamptz AS bucket_start,
    'month'::text AS granularity,
    node_id AS subject_id,
    min(min_level)::double precision AS value,
    jsonb_build_object('node_id', node_id, 'min_level', min(min_level), 'avg_level', round((sum(sum_level) / NULLIF(sum(readings), 0))::numeric, 1), 'readings', sum(readings)) AS extra
FROM agg_telemetry_day
WHERE min_level IS NOT NULL AND bucket >= (now() - INTERVAL '70 days')
GROUP BY 1, 2, 3;

CREATE OR REPLACE VIEW api_rank_battery_lowest AS SELECT * FROM api_rank_solar_health;

-- 7.9 api_rank_chatters (Emisión de mensajes de texto)
CREATE OR REPLACE VIEW api_rank_chatters AS
SELECT
    bucket AS bucket_start,
    'hour'::text AS granularity,
    from_id AS subject_id,
    sum(messages)::double precision AS value,
    jsonb_build_object('from_id', from_id, 'messages', sum(messages)) AS extra
FROM agg_text_hour
GROUP BY 1, 2, 3
UNION ALL
SELECT
    bucket AS bucket_start,
    'day'::text AS granularity,
    from_id AS subject_id,
    sum(messages)::double precision AS value,
    jsonb_build_object('from_id', from_id, 'messages', sum(messages)) AS extra
FROM agg_text_day
GROUP BY 1, 2, 3
UNION ALL
SELECT
    date_trunc('week', bucket)::timestamptz AS bucket_start,
    'week'::text AS granularity,
    from_id AS subject_id,
    sum(messages)::double precision AS value,
    jsonb_build_object('from_id', from_id, 'messages', sum(messages)) AS extra
FROM agg_text_day
WHERE bucket >= (now() - INTERVAL '70 days')
GROUP BY 1, 2, 3
UNION ALL
SELECT
    date_trunc('month', bucket)::timestamptz AS bucket_start,
    'month'::text AS granularity,
    from_id AS subject_id,
    sum(messages)::double precision AS value,
    jsonb_build_object('from_id', from_id, 'messages', sum(messages)) AS extra
FROM agg_text_day
WHERE bucket >= (now() - INTERVAL '70 days')
GROUP BY 1, 2, 3;

CREATE OR REPLACE VIEW api_rank_messages AS SELECT * FROM api_rank_chatters;

-- 7.10 api_rank_new_nodes (Nuevos nodos registrados)
CREATE OR REPLACE VIEW api_rank_new_nodes AS
SELECT
    date_trunc('hour', first_seen) AS bucket_start,
    'hour'::text AS granularity,
    id AS subject_id,
    EXTRACT(EPOCH FROM first_seen)::double precision AS value,
    jsonb_build_object('node_id', id, 'first_seen', to_char(first_seen at time zone 'UTC', 'YYYY-MM-DD"T"HH24:MI:SS"Z"')) AS extra
FROM node
UNION ALL
SELECT
    date_trunc('day', first_seen at time zone 'Europe/Madrid')::timestamptz AS bucket_start,
    'day'::text AS granularity,
    id AS subject_id,
    EXTRACT(EPOCH FROM first_seen)::double precision AS value,
    jsonb_build_object('node_id', id, 'first_seen', to_char(first_seen at time zone 'UTC', 'YYYY-MM-DD"T"HH24:MI:SS"Z"')) AS extra
FROM node
UNION ALL
SELECT
    date_trunc('week', first_seen at time zone 'Europe/Madrid')::timestamptz AS bucket_start,
    'week'::text AS granularity,
    id AS subject_id,
    EXTRACT(EPOCH FROM first_seen)::double precision AS value,
    jsonb_build_object('node_id', id, 'first_seen', to_char(first_seen at time zone 'UTC', 'YYYY-MM-DD"T"HH24:MI:SS"Z"')) AS extra
FROM node
UNION ALL
SELECT
    date_trunc('month', first_seen at time zone 'Europe/Madrid')::timestamptz AS bucket_start,
    'month'::text AS granularity,
    id AS subject_id,
    EXTRACT(EPOCH FROM first_seen)::double precision AS value,
    jsonb_build_object('node_id', id, 'first_seen', to_char(first_seen at time zone 'UTC', 'YYYY-MM-DD"T"HH24:MI:SS"Z"')) AS extra
FROM node;

-- 7.11 api_rank_provinces_growth (Crecimiento por provincia)
CREATE OR REPLACE VIEW api_rank_provinces_growth AS
SELECT
    bucket AS bucket_start,
    'hour'::text AS granularity,
    province AS subject_id,
    count(DISTINCT from_id)::double precision AS value,
    jsonb_build_object('province', province, 'nodes', count(DISTINCT from_id)) AS extra
FROM agg_node_hour
WHERE province IS NOT NULL AND province != 'FUERA'
GROUP BY 1, 2, 3
UNION ALL
SELECT
    bucket AS bucket_start,
    'day'::text AS granularity,
    province AS subject_id,
    count(DISTINCT from_id)::double precision AS value,
    jsonb_build_object('province', province, 'nodes', count(DISTINCT from_id)) AS extra
FROM agg_node_day
WHERE province IS NOT NULL AND province != 'FUERA'
GROUP BY 1, 2, 3
UNION ALL
SELECT
    date_trunc('week', bucket)::timestamptz AS bucket_start,
    'week'::text AS granularity,
    province AS subject_id,
    count(DISTINCT from_id)::double precision AS value,
    jsonb_build_object('province', province, 'nodes', count(DISTINCT from_id)) AS extra
FROM agg_node_day
WHERE province IS NOT NULL AND province != 'FUERA' AND bucket >= (now() - INTERVAL '100 days')
GROUP BY 1, 2, 3
UNION ALL
SELECT
    date_trunc('month', bucket)::timestamptz AS bucket_start,
    'month'::text AS granularity,
    province AS subject_id,
    count(DISTINCT from_id)::double precision AS value,
    jsonb_build_object('province', province, 'nodes', count(DISTINCT from_id)) AS extra
FROM agg_node_day
WHERE province IS NOT NULL AND province != 'FUERA' AND bucket >= (now() - INTERVAL '100 days')
GROUP BY 1, 2, 3;

CREATE OR REPLACE VIEW api_rank_active_nodes AS SELECT * FROM api_rank_provinces_growth;

-- Vistas auxiliares compatibles
CREATE OR REPLACE VIEW api_rank_ch_util AS
SELECT
    date_trunc('day', n.metrics_at)::timestamptz AS bucket_start,
    'day'::text AS granularity,
    n.id AS subject_id,
    n.channel_utilization::double precision AS value,
    jsonb_build_object('node_id', n.id, 'channel_utilization', n.channel_utilization, 'province', n.province) AS extra
FROM node n
WHERE n.role IN ({{INFRA_ROLES}}) AND n.channel_utilization IS NOT NULL;

CREATE OR REPLACE VIEW api_rank_battery_highest AS
SELECT
    bucket AS bucket_start,
    'day'::text AS granularity,
    node_id AS subject_id,
    max(max_level)::double precision AS value,
    jsonb_build_object('node_id', node_id, 'max_level', max(max_level), 'readings', sum(readings)) AS extra
FROM agg_telemetry_day
WHERE max_level IS NOT NULL
GROUP BY 1, 2, 3;

CREATE OR REPLACE VIEW api_rank_reboots AS
SELECT
    bucket AS bucket_start,
    'day'::text AS granularity,
    node_id AS subject_id,
    sum(reboots)::double precision AS value,
    jsonb_build_object('node_id', node_id, 'reboots', sum(reboots)) AS extra
FROM agg_telemetry_day
WHERE reboots > 0
GROUP BY 1, 2, 3;

-- 8. Vistas de diagnóstico personal ("Revisa tu nodo")

-- 8.1 api_node_intervals (intervalos típicos entre emisiones por tipo y variante)
CREATE OR REPLACE VIEW api_node_intervals AS
SELECT
    p.from_id AS node_id,
    COALESCE(p.portnum, 'other') AS portnum,
    COALESCE(p.variant, 'default') AS variant,
    count(*)::integer AS broadcasts,
    round(
        (EXTRACT(EPOCH FROM (max(p.rx_first) - min(p.rx_first))) / NULLIF(count(*) - 1, 0))::numeric,
        1
    )::real AS median_interval_s
FROM packet p
WHERE p.rx_first >= (now() - INTERVAL '7 days')
GROUP BY 1, 2, 3;

-- 8.2 api_node_battery_daily (histórico diario de batería)
CREATE OR REPLACE VIEW api_node_battery_daily AS
SELECT
    node_id,
    bucket::date AS day,
    min_level,
    round((sum_level / NULLIF(readings, 0))::numeric, 1)::real AS avg_level,
    readings::integer,
    readings_below_40::integer
FROM agg_telemetry_day
WHERE bucket >= (now() - INTERVAL '7 days');

-- 8.3 api_node_reboots_daily (histórico diario de reinicios)
CREATE OR REPLACE VIEW api_node_reboots_daily AS
SELECT
    node_id,
    bucket::date AS day,
    reboots::integer
FROM agg_telemetry_day
WHERE bucket >= (now() - INTERVAL '7 days');

-- ==============================================================================
-- 9. Asignación de Permisos de Seguridad
-- portal_lector_ingesta solo puede leer las vistas de contrato api_*
-- ==============================================================================

REVOKE ALL ON ALL TABLES IN SCHEMA public FROM portal_lector_ingesta;

GRANT SELECT ON
    api_nodes,
    api_province_load,
    api_routers,
    api_gateways,
    api_summary,
    api_traffic_mix,
    api_rank_network_usage,
    api_rank_airtime,
    api_rank_gateway_coverage,
    api_rank_gateways_heard,
    api_rank_gateway_exclusive,
    api_rank_longest_links,
    api_rank_best_links,
    api_rank_most_neighbors,
    api_rank_uptime,
    api_rank_solar_health,
    api_rank_battery_lowest,
    api_rank_battery_highest,
    api_rank_chatters,
    api_rank_messages,
    api_rank_new_nodes,
    api_rank_provinces_growth,
    api_rank_active_nodes,
    api_rank_ch_util,
    api_rank_reboots,
    api_node_intervals,
    api_node_battery_daily,
    api_node_reboots_daily
TO portal_lector_ingesta;
