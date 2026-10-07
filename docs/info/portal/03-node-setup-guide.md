# 06.3 · Configura tu nodo

> Bloque resumido en la portada y página completa `/configura-tu-nodo`: radio, rol, saltos, intervalos y posición. Texto en `pages/05-node-setup.md`. Basado en las buenas prácticas públicas de la comunidad (roles, saltos, intervalos, posición) y en el preset SFNarrow.

## Objetivo

Que quien llega con un nodo nuevo lo deje bien configurado en cinco minutos y que quien ya tiene uno sepa qué corregir. Los valores son los mismos que vigila el detector y que usa "Revisa tu nodo": un nodo que los sigue no dispara alertas de configuración ni sale con hallazgos.

## Especificación

### Radio (preset personalizado SFNarrow)

| Ajuste | Valor | Origen |
|---|---|---|
| Región | `EU_868` | `LORA_REGION` |
| Usar preset | Desactivado | Fijo |
| Ancho de banda | `62` (62,5 kHz) | `LORA_BANDWIDTH` |
| Spreading factor | `7` | `LORA_SPREAD_FACTOR` |
| Coding rate | `5` | `LORA_CODING_RATE` |
| Frequency slot | `4` (869,618 MHz) | `LORA_FREQUENCY_SLOT`, `LORA_FREQUENCY_MHZ` |
| Canal 0: nombre | `SFNarrow` | `PRIMARY_CHANNEL` |
| Canal 0: clave | `AQ==` (clave pública por defecto) | `CHANNEL_KEY_DEFAULT` |
| Límite de saltos | `3` | `LORA_HOP_LIMIT` |

Todos salen de `.env.comun` → `config/proyecto.php`; cambiar la malla = cambiar una variable y redesplegar.

### Rol

| Rol | Cuándo |
|---|---|
| `CLIENT_MUTE` | La mayoría: personales, de interior o con mala cobertura |
| `CLIENT` | Exterior bien situado |
| `ROUTER` | Solo ubicaciones estratégicas y coordinado con el proyecto |
| `ROUTER_LATE`, `CLIENT_BASE` | No recomendados |

### Saltos

3 por defecto; hasta 4 en nodos bien conectados; 5 solo en extremos de la malla o `CLIENT_MUTE` de interior. Desde 6 el detector lo marca (`hops-high`).

### Intervalos (`config/proyecto.php` → `recomendaciones`, compartido con `13-nodes-alerts-api.md`)

| Qué | Valor |
|---|---|
| NodeInfo | 72 h |
| Posición, nodo fijo | 72 h |
| Posición, nodo móvil | ≥ 1 h |
| Telemetría de dispositivo, solar | ≥ 4 h |
| Telemetría de dispositivo, troncal | ≥ 6 h |
| Telemetría de dispositivo, enchufado | Desactivada |
| Telemetría de entorno | Desactivada o > 4 h |
| Telemetría eléctrica | Desactivada |

### Posición y privacidad

Banderas de posición desactivadas, posición inteligente desactivada, precisión reducida si no se quiere mostrar la ubicación exacta. OK to MQTT activado para aparecer en mapas y estadísticas; desactivarlo es la única forma de no aparecer.

### Presentación

- **Portada:** radio, rol, saltos e intervalos en una tabla por bloque, sin columna de variables, y enlace "Guía completa →".
- **Página:** todo lo anterior con el porqué de cada ajuste, anclas (`#radio`, `#rol`, `#saltos`, `#intervalos`, `#posicion`) que usa "Revisa tu nodo", enlace a `/firmware` y a `/revisa-tu-nodo`.
- Componente "Bloque de ajustes" de `DESIGN.md`: tabla clave-valor, valores en Ubuntu Mono, botón de copiar en los valores de radio.
- Sin marcas de terceros: "el firmware", "la app del nodo".

## Contratos propios

- `config/proyecto.php` → `radio.*` (desde `LORA_*`), `recomendaciones.*`, `roles_recomendados`.
- Anclas de la página (contrato con `09-node-check.md` y `13-nodes-alerts-api.md`, campo `guide`).

## Unidades de trabajo

- **UT-06.3.1 — Configuración.** `radio` y `recomendaciones` en `config/proyecto.php` desde `.env`. *Aceptación:* ningún valor de las tablas está escrito en vistas ni Markdown.
- **UT-06.3.2 — Bloque de portada.** *Aceptación:* cabe en una pantalla de escritorio; enlace a la guía.
- **UT-06.3.3 — Página completa.** Texto de `pages/`, anclas, copiar. *Aceptación:* las 5 anclas existen y las usa el diagnóstico.
- **UT-06.3.4 — Coherencia con el detector.** Prueba que compara `recomendaciones` con los umbrales del catálogo de reglas de configuración (`../detector-alertas/02-rule-catalog.md`, vía `api_catalogo` o fixture). *Aceptación:* si cambia un umbral sin cambiar el otro, las pruebas fallan.

## Escenarios de prueba

1. **Dado** `LORA_FREQUENCY_SLOT=5` en `.env.comun`, **cuando** se redespliega, **entonces** portada y página muestran 5 y la frecuencia correspondiente.
2. **Dado** un nodo configurado solo con esta página, **cuando** pasan 7 días, **entonces** su diagnóstico sale sin hallazgos de intervalos ni saltos.
3. **Dado** un clic en "Copiar" de un valor, **cuando** se pega, **entonces** se obtiene el valor exacto sin espacios.
4. **Dado** un enlace `/configura-tu-nodo#intervalos`, **cuando** se abre, **entonces** la página se posiciona en la tabla de intervalos.

---
> Creado: 2026-10-07 · Última revisión: 2026-10-07
