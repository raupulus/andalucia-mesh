# 05 · Configura tu nodo

> `/configura-tu-nodo` · Que cualquiera deje su nodo bien configurado en cinco minutos y que quien ya tiene uno sepa qué corregir, con el porqué de cada ajuste · `../03-node-setup-guide.md`

## SEO

- **Título:** `Configura tu nodo · {PROJECT_NAME}` (39 caracteres)
- **Descripción:** `Ajustes recomendados para tu nodo en la malla de Andalucía: radio, rol, saltos, intervalos y posición, con el porqué de cada uno.` (129)

## Estructura

| # | Sección | Componente (`DESIGN.md`) |
|---|---|---|
| 1 | H1, entradilla y por qué importa | Tipografía H1 + Texto |
| 2 | Radio: tabla y cómo ponerla | Bloque de ajustes (tabla clave-valor, valores en Ubuntu Mono) + pasos numerados |
| 3 | Rol | Bloque de ajustes (rol · cuándo) |
| 4 | Saltos | Texto corrido con lista |
| 5 | Intervalos (ancla `#intervalos`, la enlaza Rankings) | Bloque de ajustes |
| 6 | Posición y privacidad | Texto corrido con lista |
| 7 | Lo que perjudica a toda la malla | Texto corrido con lista |
| 8 | Siguiente paso | Botón primario "Conecta tu gateway" |

La página sustituye a la antigua "Buenas prácticas". Sin columna de variables (es información interna).

## Borrador del texto

Cada `###` es un H2 de la página.

**H1:** Configura tu nodo

Con estos ajustes tu nodo oye a la malla y no la satura. Si tu nodo es nuevo, te llevará unos minutos; si ya tienes uno, repasa la lista y corrige lo que no coincida.

**Por qué importa.** Un nodo no puede emitir y escuchar a la vez, y cada paquete ocupa el canal para todos. La mayor parte del tráfico de la malla no son mensajes, sino paquetes automáticos: información del nodo, posición y telemetría. Cuantos menos envíes, más hueco queda para lo importante.

### 1. Radio

La mayor parte de la malla en España usa esta configuración manual. Si tu nodo usa otra, no oirá a los demás ni ellos a él. Es de banda estrecha: más alcance y menos interferencias que las configuraciones de fábrica.

| Ajuste | Valor |
|---|---|
| Región (Region) | `{LORA_REGION}` (European Union 868 MHz) |
| Usar preset (Predefined) | Desactivado (para desbloquear los campos personalizados) |
| Ancho de banda (Bandwidth) | `{LORA_BANDWIDTH}` (o {LORA_BANDWIDTH_KHZ} kHz) |
| Spreading factor | `{LORA_SPREAD_FACTOR}` |
| Coding rate | `{LORA_CODING_RATE}` (4/5) |
| Frequency slot | `{LORA_FREQUENCY_SLOT}` |
| Frequency override | `{LORA_FREQUENCY_MHZ}` MHz (o 869.6188 MHz) |
| Nombre del preset | `SFNarrow` |
| Canal principal (0): nombre | `{PRIMARY_CHANNEL}` |
| Canal principal (0): clave PSK | `AQ==` (clave pública por defecto) |
| Límite de saltos (Hop Limit) | `{LORA_HOP_LIMIT}` (máximo 4) |

Cómo ponerla en la app de Meshtastic:

1. En la app del nodo, abre los ajustes de LoRa (`Radio Configuration → LoRa`) y elige la región `{LORA_REGION}`.
2. Desactiva **Usar preset (Predefined)** para desbloquear los campos manuales.
3. Escribe ancho de banda `{LORA_BANDWIDTH}` (o 62.5 kHz), spreading factor `{LORA_SPREAD_FACTOR}` y coding rate `{LORA_CODING_RATE}`.
4. Pon el **Frequency slot** en `{LORA_FREQUENCY_SLOT}` o, como alternativa equivalente, escribe la frecuencia en **Frequency override** `{LORA_FREQUENCY_MHZ}` MHz.
5. En `Channels`, renombra el canal 0 a `{PRIMARY_CHANNEL}` y pon la clave PSK por defecto (`AQ==`).
6. Pon el límite de saltos a `{LORA_HOP_LIMIT}`.

Si administras un nodo a distancia, cambia los ajustes en este orden para no perder el acceso: primero la radio del nodo remoto, luego la del local; después el canal del remoto y, por último, el del local.

### 2. Rol

| Rol | Cuándo |
|---|---|
| `CLIENT_MUTE` | La mayoría: nodos personales, de interior o con mala cobertura. No repite paquetes de otros |
| `CLIENT` | Nodos de exterior bien situados, en una azotea o terraza despejada. Repite paquetes |
| `ROUTER` | Solo en ubicaciones estratégicas y coordinado con el proyecto: escríbenos a {PROJECT_CONTACT} antes de activarlo. El sistema de alertas avisa de los routers no coordinados |
| `ROUTER_LATE`, `CLIENT_BASE` | No recomendados |

No hace falta que todos los nodos repitan: si todos lo hacen, el mismo paquete ocupa el canal muchas veces. Ante la duda, `CLIENT_MUTE`.

### 3. Saltos

- Por defecto, **{LORA_HOP_LIMIT}**: suficiente en casi toda la malla.
- Hasta **4** en nodos bien conectados.
- **5** solo en los extremos de la malla o en `CLIENT_MUTE` de interior.
- Más de 5 satura la red para todos, y el sistema de alertas lo marca.

### 4. Intervalos de emisión

| Qué | Cada cuánto |
|---|---|
| NodeInfo | 72 h |
| Posición, nodo fijo | 72 h |
| Posición, nodo móvil | 1 h como mínimo |
| Telemetría del dispositivo, nodo solar | 4 h o más |
| Telemetría del dispositivo, nodo troncal | 6 h o más |
| Telemetría del dispositivo, nodo enchufado | Desactivada |
| Telemetría de entorno | Desactivada, o más de 4 h |
| Telemetría eléctrica | Desactivada |

Estos paquetes automáticos son la mayor parte del tráfico de la malla, y NodeInfo es el que más pesa aunque casi nunca cambie. Si tu nodo los envía más a menudo, aparecerá arriba en el [ranking de consumo de red](/rankings).

### 5. Posición y privacidad

- Desactiva todas las banderas de posición (*position flags*): hacen cada paquete más grande.
- Desactiva la posición inteligente (*smart position*).
- Si no quieres mostrar tu ubicación exacta, reduce la precisión de posición del canal.
- Activa **OK to MQTT** si quieres que tu nodo aparezca en los mapas y en las estadísticas. Sin él, los gateways no suben tus paquetes.
- **Si no quieres aparecer en los mapas, desactiva OK to MQTT.** No hay otra forma de exclusión: lo que se sube es lo que cada nodo permite. Lo que ya se haya subido se borra solo al cumplirse los plazos de la [política de privacidad](/legal/privacidad); si quieres que se borre antes, escríbenos.

### 6. Lo que perjudica a toda la malla

- Encender el nodo sin antena: puede dañar la radio.
- Poner más saltos de la cuenta.
- Poner el rol `ROUTER` sin coordinarlo.
- Intervalos más cortos que los de esta guía.
- Dejar activado el módulo de prueba de alcance (*RangeTest*).
- Activar el modo radioaficionado: quita el cifrado.

### Siguiente paso

¿Tu nodo tiene internet? Conéctalo como gateway y ayuda a que se vea toda la malla.

**Botón primario:** [Conecta tu gateway](/conecta-tu-gateway)

## Datos dinámicos y configuración

| Dato | Origen |
|---|---|
| Radio | `LORA_REGION`, `LORA_BANDWIDTH`, `LORA_SPREAD_FACTOR`, `LORA_CODING_RATE`, `LORA_FREQUENCY_SLOT`, `LORA_HOP_LIMIT`, `PRIMARY_CHANNEL`. Valores actuales: `EU_868`, `62`, `7`, `5`, `4`, `3`, `SFNarrow` |
| `{LORA_BANDWIDTH_KHZ}` y `{LORA_FREQUENCY_MHZ}` | Valores derivados en `config/proyecto.php` (62 → 62,5 kHz; slot 4 → 869,618 MHz), no variables nuevas del `.env`. Si cambia el slot o el ancho, cambian solos |
| Clave `AQ==` | Fija (clave pública por defecto) |
| Contacto | `PROJECT_CONTACT` |
| Rol, saltos 4/5, intervalos y posición | Texto fijo en `resources/contenido/configura-tu-nodo.md`. Deben coincidir con los umbrales del bloque B de `../../detector-alertas/02-rule-catalog.md` (RF-PO-RN-3) |

El orden de cambio en nodos remotos, la lista de "lo que perjudica" y el motivo de las banderas de posición salen de las buenas prácticas públicas de la comunidad, reescritas.

## Supuestos aplicados

- `hops-high` empieza en 6 saltos (`medio`): un nodo con 4 o 5 saltos según esta guía no recibe alerta (corregido en `../../detector-alertas/02-rule-catalog.md`).

---
> Creado: 2026-10-07 · Última revisión: 2026-10-07
