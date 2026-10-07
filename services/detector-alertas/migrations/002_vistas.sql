-- ==============================================================================
-- 002_vistas.sql
-- Vistas contrato para el portal web y roles de solo lectura
-- ==============================================================================

-- 1. Vista principal de alertas (abiertas o resueltas)
CREATE OR REPLACE VIEW api_alertas AS
SELECT a.id,
       a.regla,
       a.riesgo,
       r.orden AS riesgo_orden,
       a.tipo,
       a.mensaje,
       a.nodo,
       a.nodos,
       CASE 
         WHEN a.nodo = 'all' THEN coalesce((a.datos->>'nodos_total')::int, cardinality(a.nodos)) 
         ELSE 1 
       END AS nodos_total,
       a.nodo_info,
       a.provincia,
       a.provincias,
       a.datos,
       a.estado,
       a.abierta_en,
       a.actualizada_en,
       a.resuelta_en,
       a.reaperturas,
       a.abierta_en AS inicio_at
FROM alerta a
LEFT JOIN catalogo r ON r.clase = 'riesgo' AND r.id = a.riesgo;

-- 2. Historial de transiciones por alerta
CREATE OR REPLACE VIEW api_alertas_transiciones AS
SELECT t.id AS transicion_id,
       t.alerta_id,
       t.transicion,
       t.en,
       t.alerta->>'riesgo' AS riesgo,
       t.alerta->>'mensaje' AS mensaje,
       t.alerta->'datos' AS datos
FROM transicion t;

-- 3. Resumen de alertas abiertas por riesgo y tipo (matriz completa)
CREATE OR REPLACE VIEW api_alertas_resumen AS
SELECT r.id AS riesgo,
       r.orden AS riesgo_orden,
       t.id AS tipo,
       count(a.id) AS abiertas,
       count(DISTINCT a.nodo) FILTER (WHERE a.nodo <> 'all') AS nodos_afectados,
       max(a.actualizada_en) AS ultima_en
FROM catalogo r
CROSS JOIN catalogo t
LEFT JOIN alerta a ON a.estado = 'abierta' AND a.riesgo = r.id AND a.tipo = t.id
WHERE r.clase = 'riesgo' AND t.clase = 'tipo'
GROUP BY r.id, r.orden, t.id;

-- 4. Nodos individuales en riesgo (alertas abiertas propias)
CREATE OR REPLACE VIEW api_nodos_en_riesgo AS
SELECT a.nodo,
       (array_agg(a.nodo_info ORDER BY a.actualizada_en DESC))[1] AS nodo_info,
       (array_agg(a.provincia ORDER BY a.actualizada_en DESC))[1] AS provincia,
       (array_agg(a.riesgo ORDER BY r.orden DESC))[1] AS riesgo_max,
       max(r.orden) AS riesgo_max_orden,
       array_agg(DISTINCT a.tipo) AS tipos,
       array_agg(DISTINCT a.regla) AS reglas,
       array_agg(a.mensaje ORDER BY r.orden DESC, a.abierta_en) AS motivos,
       count(*) AS alertas_abiertas,
       min(a.abierta_en) AS desde,
       max(a.actualizada_en) AS actualizada_en
FROM alerta a
JOIN catalogo r ON r.clase = 'riesgo' AND r.id = a.riesgo
WHERE a.estado = 'abierta' AND a.nodo <> 'all'
GROUP BY a.nodo;

-- 5. Catálogo de reglas, riesgos y tipos disponibles
CREATE OR REPLACE VIEW api_catalogo AS
SELECT clase,
       id,
       orden,
       nombre,
       descripcion,
       activa,
       fase,
       afecta_malla,
       riesgos,
       tipos
FROM catalogo;

-- Concesión de permisos al rol portal_lector_alertas si existe en el clúster
DO $$
BEGIN
    IF EXISTS (SELECT 1 FROM pg_roles WHERE rolname = 'portal_lector_alertas') THEN
        GRANT USAGE ON SCHEMA public TO portal_lector_alertas;
        GRANT SELECT ON api_alertas, api_alertas_transiciones, api_alertas_resumen, api_nodos_en_riesgo, api_catalogo TO portal_lector_alertas;
    END IF;
END $$;
