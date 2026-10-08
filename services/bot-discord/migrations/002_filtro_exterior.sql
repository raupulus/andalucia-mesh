-- Migración 002: Filtro para incluir o excluir nodos de fuera de Andalucía
ALTER TABLE destino ADD COLUMN IF NOT EXISTS incluir_exterior BOOLEAN NULL;
