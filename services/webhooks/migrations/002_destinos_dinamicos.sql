-- Migración 002: Campos para gestión dinámica de destinos de webhooks desde el panel de operador

ALTER TABLE destino ADD COLUMN IF NOT EXISTS url TEXT NOT NULL DEFAULT '';
ALTER TABLE destino ADD COLUMN IF NOT EXISTS secreto TEXT NOT NULL DEFAULT '';
ALTER TABLE destino ADD COLUMN IF NOT EXISTS riesgos TEXT[] NULL;
ALTER TABLE destino ADD COLUMN IF NOT EXISTS tipos TEXT[] NULL;
ALTER TABLE destino ADD COLUMN IF NOT EXISTS provincias TEXT[] NULL;
ALTER TABLE destino ADD COLUMN IF NOT EXISTS nodos TEXT[] NULL;
