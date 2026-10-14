# Módulo 19 · Catálogo de Herramientas y Calculadora ADC de Batería

> Catálogo de herramientas especializadas para nodos y red Meshtastic (`/herramientas`) y calculadora interactiva para divisores de tensión resistivos y calibración del parámetro `adc_multiplier` (`/herramientas/calculadora-bateria-adc`).

## Qué hace y qué NO hace

- **Qué hace**:
  - Proporciona un hub centralizado (`/herramientas`) accesible desde el menú principal para descubrir y acceder a herramientas técnicas de la red.
  - Muestra tarjetas de herramientas con imagen 16:9 (`640x360`), título, descripción, badges y botón de acceso directo, con ilustraciones exclusivas basadas en el logotipo oficial del proyecto.
  - Incluye el widget de compartir en redes sociales (`<x-botones-compartir>`) directamente debajo del título y la descripción tanto en `/herramientas` como en `/herramientas/calculadora-bateria-adc` (WhatsApp, Telegram, Correo y Copiar enlace).
  - Alberga el acceso al **Configurador Automático** (`/configurador`) con imagen ilustrativa `configurador.webp` y previsualización social `og-configurador.webp`.
  - Proporciona una herramienta interactiva dedicada (`/herramientas/calculadora-bateria-adc`) con imagen ilustrativa `calculadora-bateria.webp` y previsualización social `og-calculadora-bateria.webp` para calcular el ratio `adc_multiplier_override` de un divisor de tensión resistivo para baterías de litio (Li-Ion / LiPo de 4.2V).
  - Destaca y detalla las dos combinaciones de resistencias recomendadas por la comunidad: `1 MΩ + 1 MΩ` (simétrico 1:1, ratio teórico 2.00, recomendado 2.05 en Meshtastic) y `1 MΩ + 1.5 MΩ` (asimétrico, ratio 1.67).
  - Realiza cálculos en tiempo real en el cliente (JavaScript puro): multiplicador teórico y recomendado, voltaje en el pin GPIO con evaluación de seguridad (< 3.0V seguro, 3.0V–3.3V precaución, > 3.3V peligro), y corriente de consumo parásito en reposo (µA).
  - Incluye herramienta de calibración fina empírica con multímetro para corregir desviaciones entre la lectura de la aplicación y la medida física del voltímetro.
  - Ofrece instrucciones y fragmentos de código listos para copiar con un clic para la App oficial de Meshtastic, la CLI (`meshtastic --set power.adc_multiplier_override <VALOR>`) y archivos YAML (`power: { adc_multiplier_override: <VALOR> }`).
  - Indexa ambas páginas en `sitemap.xml` con prioridad 0.8, frecuencia semanal, imágenes WebP dedicadas de 1200x630 y marcado alternativo multilingüe (`es`, `en`, `pt`).
  - Cumple estrictamente la directiva de cero cookies para visitantes (RN-06) y ofrece soporte multidioma completo (RN-48).

- **Qué NO hace**:
  - No aplica configuraciones directamente por radio sobre nodos remotos (la configuración se realiza localmente por el usuario en su dispositivo).
  - No almacena cookies ni datos en el servidor sobre los cálculos realizados.

## Modelo de datos

Las herramientas y combinaciones recomendadas se configuran a nivel de controlador (`HerramientasController`):

```php
// Catálogo de herramientas (/herramientas)
[
    'id' => 'configurador',
    'titulo' => string,
    'descripcion' => string,
    'url' => string,
    'imagen' => string,
    'badge' => string,
    'destacado' => bool,
]

// Parejas recomendadas (/herramientas/calculadora-bateria-adc)
[
    'id' => string,
    'nombre' => string,
    'tipo' => string,
    'r1' => int, // Ohmios
    'r2' => int, // Ohmios
    'r1_formato' => string,
    'r2_formato' => string,
    'ratio_teorico' => float,
    'ratio_recomendado' => float,
    'voltaje_pin_42' => float,
    'corriente_microamperios' => float,
    'descripcion' => string,
]
```

## Flujos principales

1. **Navegación al Hub de Herramientas**:
   - El visitante despliega el menú "Extras" en la cabecera (escritorio o móvil).
   - Pulsa sobre "Herramientas" (`/herramientas`).
   - Visualiza la parrilla de tarjetas: Configurador Automático y Calculadora ADC de Batería.
   - Puede acceder a cualquiera de las utilidades o proponer nuevas mediante el buzón de sugerencias.

2. **Cálculo del Divisor de Tensión y Multiplicador ADC**:
   - El usuario accede a `/herramientas/calculadora-bateria-adc`.
   - Puede pulsar "Cargar en la calculadora" en las parejas recomendadas (`1 MΩ + 1 MΩ` o `1 MΩ + 1.5 MΩ`) o introducir valores manuales de $R_1$ y $R_2$ con selectores de unidad (MΩ, kΩ, Ω).
   - La calculadora actualiza instantáneamente:
     $$\text{Multiplicador} = \frac{R_1 + R_2}{R_2}$$
     $$V_{\text{pin}} = V_{\text{bat}} \times \frac{R_2}{R_1 + R_2}$$
     $$I_{\text{reposo}} = \frac{V_{\text{bat}}}{R_1 + R_2}$$
   - Si detecta $1\text{ M}\Omega + 1\text{ M}\Omega$, destaca la recomendación de calibración empírica `2.05` para Meshtastic (compensando impedancia del ADC y tolerancias).
   - Muestra el badge de seguridad para avisar si el voltaje supera el límite de 3.3V del microcontrolador.
   - Genera los comandos CLI y fragmentos YAML listos para copiar con un clic.

3. **Calibración Fina con Multímetro**:
   - El usuario introduce el voltaje real medido con polímetro (ej. 4.18V), el que muestra la app (ej. 4.05V) y el multiplicador configurado actualmente.
   - Obtiene el nuevo ratio corregido para aplicar en la app.

## Puntos de entrada

- **Ruta `/herramientas`**:
  - Método: `GET`
  - Controlador: `HerramientasController@index`
  - Autenticación: Pública (cero cookies).
  - SEO: Metadatos Open Graph, Twitter Cards, Breadcrumb Schema.org, imagen `og-herramientas.webp`.

- **Ruta `/herramientas/calculadora-bateria-adc`**:
  - Método: `GET`
  - Controlador: `HerramientasController@calculadoraBateria`
  - Autenticación: Pública (cero cookies).
  - SEO: Metadatos Open Graph, Twitter Cards, Breadcrumb Schema.org, imagen `og-calculadora-bateria.webp`.

## Dependencias en ambos sentidos

- **Hacia adentro**:
  - `<x-layout>` para estructura HTML, SEO, CSS semántico y soporte multidioma.
  - `SitemapController` para indexación en `sitemap.xml`.
  - Diccionarios de idiomas `lang/{es,en,pt}/portal.php`.
- **Hacia afuera**:
  - Cabecera pública `<x-cabecera>` (enlace en el menú "Extras").
  - Menú de pie o enlaces cruzados hacia `/configurador` y `/sugerencias`.

## Configuración

| Variable | Valor por defecto | Efecto |
| :--- | :--- | :--- |
| `config('proyecto.extras')` | Incluye `Herramientas` (`/herramientas`) | Define los enlaces mostrados en el menú Extras de la cabecera |

## Trampas conocidas

- **Amperios vs Microamperios en Divisores**: Resistencias del orden de Kiloohmios (ej. 1 kΩ + 1 kΩ) proporcionan el mismo ratio de división de tensión (2:1), pero provocan un drenaje continuo inaceptable de $2.1\text{ mA}$ en reposo que vaciaría una celda 18650 en pocas semanas. Se deben utilizar siempre resistencias del orden de Megaohmios ($1\text{ M}\Omega$) para mantener el consumo parásito en el rango de los $\sim 2\ \mu\text{A}$.
- **Tolerancia y offset del ADC en ESP32**: El ADC del ESP32 presenta no linealidades en los extremos inferiores (<0.1V) y superiores (>2.6V). Al usar $1\text{ M}\Omega + 1\text{ M}\Omega$, la tensión a plena carga es de 2.10V, ideal para la zona lineal con atenuación `ADC_ATTEN_DB_11`. Por ello, un multiplicador de `2.05` suele compensar con precisión la ligera caída producida por la impedancia de entrada.

## Tests que lo cubren

- `tests/Feature/HerramientasTest.php` (5 tests, cobertura completa de catálogo, calculadora, recomendaciones, sitemap y multidioma).
- `tests/Feature/OpenGraphImagesTest.php` (verificación de presencia y dimensiones 1200x630 de las imágenes WebP).

## Pendiente real

- [x] Catálogo de herramientas en `/herramientas`.
- [x] Tarjetas con imagen 16:9 (`640x360`), título, descripción y enlace directo.
- [x] Imágenes basadas en el logotipo oficial para cada herramienta (`configurador.webp`, `calculadora-bateria.webp`, `og-herramientas.webp`).
- [x] Widget de compartir en redes sociales (`<x-botones-compartir>`) bajo el título y descripción en `/herramientas` y calculadora.
- [x] Herramienta dedicada de cálculo de `adc_multiplier` en `/herramientas/calculadora-bateria-adc`.
- [x] Parejas recomendadas 1M+1M y 1M+1.5M con explicación de 2.05.
- [x] Inclusión en sitemap y metadatos SEO.

---
> Creado: 2026-10-13 · Última revisión: 2026-10-14
