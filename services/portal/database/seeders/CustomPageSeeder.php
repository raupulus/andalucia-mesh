<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\CustomPage;
use Illuminate\Database\Seeder;

/**
 * Seeder de artículos y páginas técnicas originales para el portal comunitario.
 */
class CustomPageSeeder extends Seeder
{
    /**
     * Ejecuta el volcado de páginas iniciales en la base de datos.
     */
    public function run(): void
    {
        $paginas = [
            [
                'title' => 'Optimización de saltos (Hop Limit) y buenas prácticas en la malla comunitaria',
                'slug' => 'optimizacion-saltos-hop-limit',
                'description' => 'Por qué configurar 3 saltos es la clave para la salud de la red, cómo valores excesivos saturan la frecuencia 868 MHz y pautas para maximizar el alcance útil.',
                'featured_image' => 'img/servicios/rankings.webp',
                'keywords' => ['Meshtastic', 'Hop Limit', 'Radio LoRa', 'Buenas Prácticas', 'Red Mallada'],
                'is_active' => true,
                'content' => <<<'MARKDOWN'
## La importancia de una malla limpia y descongestionada

En las redes inalámbricas ad-hoc basadas en el protocolo **Meshtastic**, cada paquete emitido al aire en la banda ISM europea de **868 MHz** compite por el acceso al medio físico compartido. A diferencia de las redes celulares o enlaces Wi-Fi punto a punto, en LoRa el tiempo de transmisión en el aire (*Time on Air*) de cada ráfaga es relativamente elevado debido a los factores de dispersión (*Spreading Factor*) empleados para lograr gran alcance.

Cuando un nodo retransmite indiscriminadamente un paquete con un número elevado de saltos permitidos, se produce una propagación exponencial que puede colapsar el espectro en decenas de kilómetros a la redonda.

---

### ¿Qué es el Hop Limit y cómo funciona?

El parámetro `hop_limit` define cuántas veces puede ser retransmitido un paquete por otros nodos de la malla antes de ser descartado definitivamente:

1. **Emisión inicial:** El nodo emisor crea el paquete con un `hop_limit` inicial (por ejemplo, `3`).
2. **Reenvío por vecinos:** Cada nodo repetidor que recibe el paquete y decide retransmitirlo reduce el contador en una unidad (`hop_limit = 2`).
3. **Agotamiento del paquete:** Cuando un nodo recibe un paquete con `hop_limit = 0`, lo procesa localmente si va dirigido a él o a un canal que escucha, pero **nunca lo vuelve a retransmitir al aire**.

---

### Por qué el valor estándar recomendado es 3

En la comunidad de **Andalucía Mesh**, el estándar técnico comunitario establece **3 saltos como valor óptimo** para el 99% de las comunicaciones cotidianas:

* **Suficiente cobertura geográfica:** Gracias a la orografía andaluza y a la presencia de repetidores estratégicos en cumbres y zonas altas, con 3 saltos un paquete puede viajar con facilidad más de 100 a 180 km.
* **Prevención de colisiones:** A mayor número de saltos, mayor probabilidad de que dos nodos comiencen a transmitir simultáneamente sin detectarse mutuamente (*problema del nodo oculto*), corrompiendo ambos paquetes.
* **Uso responsable de la banda:** El ciclo de trabajo (*Duty Cycle*) legal en la banda de 868 MHz está limitado por ley al 1% o 10% según el subcanal. Reducir saltos innecesarios protege la batería y la capacidad legal de toda la comunidad.

---

### Tabla comparativa según la función del nodo

| Tipo de Nodo | Hop Limit Recomendado | Motivo y Contexto |
| :--- | :---: | :--- |
| **Cliente Móvil / Portátil** | `3` | Comunicación diaria directa con repetidores locales o comarcales. |
| **Cliente Fijo en Azotea** | `2` a `3` | Gran visibilidad directa; no necesita forzar saltos adicionales. |
| **Repetidor / Router de Cumbre** | `3` | Enlaza comarcas enteras sin generar tormentas de paquetes. |
| **Canales de Pruebas / Emergencias** | `4` *(excepcional)* | Solo justificado en valles cerrados o enlaces de muy difícil acceso. |

> **Nota técnica:** Un valor de 5 o más saltos (`hop_limit >= 5`) rara vez mejora la recepción y suele provocar duplicidades masivas detectadas por nuestro motor de alertas como anomalía de tráfico.
MARKDOWN
            ],
            [
                'title' => 'Guía de despliegue solar autónomo para nodos repetidores en cumbres y azoteas',
                'slug' => 'guia-despliegue-solar-nodos-repetidores',
                'description' => 'Cálculo de consumos, selección de placas solares, químicas de baterías Li-ion y LiFePO4, y diseño de cajas estancas para operar sin mantenimiento.',
                'featured_image' => 'img/revisa-nodo-banner.webp',
                'keywords' => ['Energía Solar', 'Repetidores', 'Hardware', 'Baterías', 'Autonomía'],
                'is_active' => true,
                'content' => <<<'MARKDOWN'
## Alimentación ininterrumpida para la infraestructura de la malla

Los nodos configurados con roles de infraestructura (`ROUTER` o `REPEATER`) son el pilar que permite comunicar ciudades, comarcas y valles en Andalucía. Al situarse habitualmente en puntos altos —torretas, azoteas comunitarias o cimas montañosas—, no suelen disponer de acceso a la red eléctrica convencional.

Diseñar una estación solar autónoma fiable exige equilibrar el consumo medio del dispositivo con la capacidad de captación fotovoltaica en los meses más desfavorables del año.

---

### 1. Elección de la plataforma de hardware

El consumo en reposo (*sleep current*) y el consumo activo son los factores determinantes para el tamaño del sistema:

* **Arquitectura Nordic nRF52840 (Recomendada):** Placas como la **RAK Wireless RAK4631** o **WisBlock Core** presentan un consumo medio inferior a **10-15 mA** en funcionamiento normal, permitiendo autonomías de semanas con baterías modestas.
* **Arquitectura ESP32:** Aunque económica y potente para entornos con Wi-Fi, su consumo medio ronda los **60-120 mA**, multiplicando por cinco la necesidad de captación solar y capacidad de almacenamiento.

---

### 2. Dimensionamiento del panel y regulador de carga

Para el clima de Andalucía, con alta radiación solar pero posibles periodos invernales de 3 a 5 días cubiertos consecutivos, se recomienda la siguiente configuración base para un nodo nRF52840:

1. **Panel solar monocristalino:** Potencia nominal de **10W a 15W** (voltaje de circuito abierto Voc ≈ 6V a 18V según el controlador).
2. **Controlador de carga MPPT / Solar Charger:** Chips específicos para energía solar como el **CN3791** (sintonizado a la tensión del panel) o la placa base integrada **WisBlock Solar** de RAK. Evitar reguladores PWM genéricos de automoción por su baja eficiencia a bajas corrientes.
3. **Protección contra descarga profunda:** Imprescindible para no destruir las celdas químicas si el voltaje cae por debajo del umbral de seguridad (3.0V en Li-ion o 2.5V en LiFePO4).

---

### 3. Química de almacenamiento: Li-ion frente a LiFePO4

| Característica | Ión de Litio (18650 / 21700) | Fosfato de Hierro y Litio (LiFePO4) |
| :--- | :--- | :--- |
| **Tensión nominal** | 3.7 V por celda | 3.2 V por celda |
| **Comportamiento en calor (>45 °C)** | Mayor degradación por temperatura en verano | Excelente estabilidad térmica y seguridad |
| **Ciclos de vida útil** | 500 – 1.000 ciclos | 2.500 – 5.000 ciclos |
| **Densidad energética** | Muy alta (menor tamaño físico) | Moderada (requiere algo más de volumen) |

---

### 4. Estanqueidad y gestión de temperatura

* **Caja estanca IP67:** Con prensaestopas estancos y membrana de compensación de presión (tipo Gore-Tex) para evitar condensación interior provocada por los ciclos de día y noche.
* **Ubicación de la batería:** Proteger la batería de la insolación directa colocándola en la parte inferior o sombreada del receptáculo.
MARKDOWN
            ],
            [
                'title' => 'Elección de antenas y líneas de transmisión para la banda 868 MHz',
                'slug' => 'eleccion-antenas-lineas-transmision-868mhz',
                'description' => 'Diferencias prácticas entre antenas colineales y compactas, pérdidas en cables coaxiales a 868 MHz y adaptación de impedancias para maximizar cobertura.',
                'featured_image' => 'img/servicios/meshview.webp',
                'keywords' => ['Antenas', '868 MHz', 'Coaxial', 'RF', 'Cobertura'],
                'is_active' => true,
                'content' => <<<'MARKDOWN'
## La antena: el componente más determinante del enlace

En comunicaciones por radiofrecuencia a frecuencias de UHF como los **868 MHz**, la antena y el cable que la conecta al transceptor LoRa tienen un impacto mucho mayor sobre el alcance real que la potencia de emisión del chip. Una pérdida de 3 dB en el cableado equivale a reducir a la mitad la potencia transmitida y la sensibilidad de recepción.

---

### 1. Ganancia y diagrama de radiación: ¿más ganancia es siempre mejor?

Existe la creencia errónea de que una antena con mayor cifra de ganancia en dBi siempre ofrecerá mejores resultados. En realidad, una antena no genera potencia adicional: concentra la energía comprimiendo el haz verticalmente:

* **Antenas de 2 a 3 dBi (Diagrama esférico amplio):** Ideales para clientes portátiles o estaciones en zonas con relieve irregular, colinas o barrancos, donde las señales pueden llegar desde ángulos elevados.
* **Antenas de 5.8 a 6.5 dBi (Diagrama aplanado tipo disco):** Ideales para repetidores elevados sobre valles abiertos, llanuras o campiña, proyectando la señal hacia el horizonte a gran distancia.
* **Peligro de antenas de más de 8 dBi:** En azoteas o cumbres de montaña, el haz puede ser tan estrecho que sobrevuele por encima de los nodos del valle inferior, dejándolos en la zona de sombra.

---

### 2. Atenuación en cables coaxiales a 868 MHz

A frecuencias de casi 900 MHz, los cables coaxiales finos de baja calidad presentan atenuaciones severas. Si la antena se instala separada del nodo, la elección del cable es crítica:

| Tipo de Cable | Diámetro exterior | Pérdida típica cada 10 metros a 868 MHz | Recomendación de uso |
| :--- | :--- | :--- | :--- |
| **RG-58 (Económico)** | ~ 5 mm | **~ 5.2 dB** *(¡pierde > 70% de la señal!)* | Desaconsejado salvo tiradas < 1 metro. |
| **LMR-195 / RG-223** | ~ 5 mm | **~ 3.6 dB** | Aceptable para tiradas cortas de 1 a 2 metros. |
| **LMR-240 / CFD-240** | ~ 6 mm | **~ 2.4 dB** | Buen equilibrio entre flexibilidad y rendimiento. |
| **LMR-400 / Ecoflex 10** | ~ 10 mm | **~ 1.3 dB** | Recomendado para mástiles con tiradas de 5 a 15 metros. |

> **Consejo pro:** Siempre que sea viable mecánicamente, es preferible instalar el nodo LoRa en una caja estanca directamente junto a la antena en el mástil y bajar únicamente el cable de datos o alimentación CC.

---

### 3. Conectores y adaptadores

Cada conector adicional introduce entre 0.1 y 0.3 dB de pérdida y un punto potencial de entrada de humedad. Se recomienda emplear conectores **N macho** o **SMA macho** crimpados profesionalmente e impermeabilizar siempre las uniones exteriores con cinta vulcanizada autoamalgamable protegida por cinta aislante de calidad.
MARKDOWN
            ],
            [
                'title' => 'Pasarelas MQTT y canales privados: cómo colaborar sin saturar la red',
                'slug' => 'pasarelas-mqtt-canales-privados-buenas-practicas',
                'description' => 'Configuración de pasarelas comunitarias en Andalucía Mesh, reglas de Uplink y Downlink, y gestión eficiente de canales secundarios.',
                'featured_image' => 'img/servicios/potatomesh.webp',
                'keywords' => ['MQTT', 'Gateway', 'Uplink', 'Privacidad', 'Topología'],
                'is_active' => true,
                'content' => <<<'MARKDOWN'
## La coexistencia entre el aire y la red IP

Las pasarelas MQTT (*Gateways*) permiten conectar islas de cobertura radioeléctrica de Meshtastic a través de internet, alimentando visores topológicos en tiempo real como **MeshView** y mapas ágiles como **PotatoMesh**.

Sin embargo, una configuración incorrecta en una pasarela doméstica puede inyectar paquetes masivos de internet al aire de la comarca, colapsando el canal de radio de los usuarios vecinos.

---

### La Regla de Oro: Uplink Sí, Downlink Siempre Desactivado

En la red comunitaria de **Andalucía Mesh**, la directriz técnica para cualquier gateway conectado a internet es tajante:

* **Uplink (Activado):** El nodo escucha el tráfico local en el aire de su entorno y lo publica hacia el broker MQTT central para que conste en los mapas, telemetría y rankings.
* **Downlink (Estrictamente Desactivado):** El nodo **nunca debe retransmitir al aire** los paquetes que le lleguen desde internet a través del broker MQTT.

> **¿Por qué está prohibido el Downlink público?** Si un nodo descarga paquetes de internet y los emite por radio, cualquier mensaje emitido en otra provincia o tráfico global de internet sería inyectado a los repetidores locales andaluces, consumiendo el ciclo de trabajo de los nodos de montaña e impidiendo que los usuarios cercanos puedan comunicarse.

---

### Canales secundarios y sensores privados

Meshtastic permite configurar hasta 8 canales en un mismo dispositivo, cada uno con su clave de cifrado AES independiente. Si utilizas nodos para telemetría personal (estaciones meteorológicas caseras, domótica o seguimiento):

1. **Ajustar intervalos de telemetría:** Los sensores de temperatura, humedad o batería no deben emitir cada 30 segundos. Un intervalo de **15 a 30 minutos** es suficiente para monitorización y no satura a los vecinos.
2. **Canales silenciados (*Muted Channels*):** Si un canal secundario privado no requiere ser reenviado por los repetidores públicos de montaña, desactiva la opción de retransmisión para ese canal específico.
3. **Respeto a la banda común:** Recuerda que la frecuencia física de 868.125 MHz (o el canal primario LongFast) es compartida por todos los entusiastas, emergencias voluntarias y aficionados de Andalucía.

La cooperación técnica y la configuración limpia son la garantía de que la red mallada permanezca rápida, resistente y disponible para toda la ciudadanía.
MARKDOWN
            ],
        ];

        foreach ($paginas as $datos) {
            CustomPage::updateOrCreate(
                ['slug' => $datos['slug']],
                $datos
            );
        }
    }
}
