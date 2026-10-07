# Ideas y Funcionalidades Futuras

Registro de funcionalidades y conceptos que han sido decididos formalmente pero cuyo desarrollo está deliberadamente aplazado para fases posteriores.

> [!NOTE]
> Este directorio no contiene deuda técnica ni registro de errores (bugs); su propósito exclusivo es registrar decisiones de alcance pospuesto.

## Elementos Aplazados

| Idea | Nota |
|---|---|
| Bot que responda por radio (`!status`, `!ping`) | Transmite: evaluar antes el impacto en el canal |
| Mapa de cobertura medida | A partir de enlaces directos nodo ↔ gateway con SNR |
| Informe semanal por los bots | Nodos nuevos, rankings, alertas resueltas |
| Alta y baja de gateways desde el panel | Definir cómo el portal entrega la configuración a Mosquitto sin escribir en su base |
| Gestión de destinos de webhooks y edición de textos desde el panel | Ídem |
| Reglas de ampliación del detector | Tras calibrar el MVP (`docs/info/detector-alertas/02-rule-catalog.md`) |
| Portal en inglés | — |
| Revisión del margen costero de ingesta | Evaluar ampliación del margen de 500 m en costas para evitar nodos legítimos marcados como FUERA |
| Inclusión de Ceuta y Melilla como provincias | Tratar Ceuta y Melilla como provincias de pleno derecho en polígonos, rankings y mapa (conexión natural hacia la península) |
| Configurador de nodos con descarga de configuración | Asistente interactivo en el portal para generar y descargar la configuración óptima (exportación en YAML / backup o código QR para la app de Meshtastic) adaptada al rol, tipo de nodo e intervalos recomendados de la malla |
| Tiempos de telemetría de energía y clima en «Configura tu nodo» | Ampliar `/configura-tu-nodo` con recomendaciones e intervalos detallados para telemetría de energía (batería, paneles solares) y sensores climáticos/ambientales (temperatura, humedad, presión), orientados a minimizar el uso de canal |
| Página de reporte de ideas y sugerencias | Formulario público en el portal para el envío de sugerencias e ideas de la comunidad, con persistencia en el backend de Laravel y gestión desde `/admin` |
| Sección visual de malas prácticas y problemas frecuentes | Diapositivas o carrusel gráfico didáctico en el portal sobre errores que saturan la red: saturación por rol ROUTER, cuándo elegir CLIENT vs CLIENT_MUTE, telemetría excesiva, Range Test emitiendo por el canal principal, etc. |
| Indicación de provincia de origen en reportes del bot | Al emitir alertas, avisos o informes en los bots (Telegram/Discord), indicar expresamente la provincia de la que procede la transmisión o el reporte (a partir del nodo o del gateway por el que entra) |
| Capturas reales de la app (iOS/iPhone) en las guías | Tomar capturas de la app oficial de Meshtastic en iPhone/iOS para ilustrar visualmente cada ajuste en `/configura-tu-nodo` y guías del portal junto al texto explicativo |

---
> Creado: 2026-10-07 · Última revisión: 2026-10-08

