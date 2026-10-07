# Detector de Alertas · Andalucía Mesh

Servicio de detección, clasificación y emisión de anomalías e incidencias de la red mallada Meshtastic para Andalucía Mesh (`services/detector-alertas/`).

## Estructura

- `config/`: Archivos YAML de clasificación de riesgos y reglas de supervisión.
- `detector/`: Código fuente principal del servicio Python (motor, catálogo de reglas, socket UNIX y persistencia).
- `migrations/`: Scripts SQL de inicialización de esquema y vistas de contrato para PostgreSQL.
- `tests/`: Batería de pruebas automatizadas con pytest.
