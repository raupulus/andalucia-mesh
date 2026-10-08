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
MARKDOWN,
            ],
            [
                'title' => 'Guía de inicio rápido: primeros pasos con tu radio LoRa Meshtastic en Andalucía',
                'slug' => 'primeros-pasos-meshtastic-andalucia',
                'description' => 'Desempaquetado, conexión por Bluetooth, carga del canal comunitario SFNarrow y pautas esenciales para emitir tus primeros mensajes sin saturar la malla.',
                'featured_image' => 'img/sugerencias-banner.webp',
                'keywords' => ['Meshtastic', 'Guía Principiantes', 'LoRa', 'Bluetooth', 'Andalucía Mesh'],
                'is_active' => true,
                'content' => <<<'MARKDOWN'
## Bienvenido a la red comunitaria libre y descentralizada

Las redes malladas abiertas basadas en **Meshtastic** permiten enviar mensajes de texto, compartir posiciones geográficas y coordinar grupos sin depender de la cobertura de telefonía móvil, de proveedores de telecomunicaciones ni de internet. Cada nodo transmite ráfagas de radio de baja potencia mediante modulación **LoRa** en la banda ciudadana de **868 MHz**, colaborando como repetidor solidario para extender la señal a través del territorio.

Si acabas de adquirir tu primer dispositivo o quieres integrarte en la comunidad de **Andalucía Mesh**, esta guía te muestra el camino directo para configurar tu radio correctamente y evitar los errores más comunes.

---

### 1. El hardware necesario y primera puesta en marcha

Existen diversas familias de placas en el mercado, cada una optimizada para distintos escenarios:

* **LilyGO T-Echo / T-Beam:** Equipos autónomos con receptor GNSS/GPS y pantalla de tinta electrónica (e-ink) o display OLED. Ideales para senderismo, montaña y uso móvil en vehículo.
* **RAK Wireless WisBlock (nRF52840):** La arquitectura de referencia para repetidores fijos y estaciones solares domésticas gracias a su consumo extremadamente bajo en reposo (< 15 mA).
* **Heltec V3 / ESP32 LoRa:** Muy económicas y fáciles de conseguir, perfectas como estación base conectada por USB a un ordenador o enchufada a la red eléctrica en casa.

> ⚠️ **¡Regla de oro de la radiofrecuencia!** Nunca enciendas el dispositivo ni pulses transmitir sin la antena conectada. El chip de radiofrecuencia (Semtech SX1262) intenta evacuar toda su potencia al aire; si la antena no está acoplada, la energía se refleja hacia el chip y puede quemar el paso final de amplificación (*High SWR*).

---

### 2. Conexión y emparejamiento con la aplicación

1. **Instalación de la app oficial:** Descarga la aplicación **Meshtastic** en tu smartphone (disponible en Google Play para Android, App Store para iOS o en F-Droid). También puedes gestionar tu nodo desde cualquier navegador web (Chrome, Edge u Opera) mediante Web Serial o Web Bluetooth en `client.meshtastic.org`.
2. **Encendido:** Conecta la batería o el cable USB de alimentación con la antena firmemente enroscada.
3. **Emparejamiento Bluetooth:** Abre la app, pulsa en buscar nodos Bluetooth y selecciona tu equipo (suele identificarse como `Meshtastic_xxxx`). Introduce el código PIN numérico que aparecerá en la pantalla del dispositivo (por defecto suele ser `123456`).

---

### 3. Ajustes obligatorios para la región de Andalucía

Para poder comunicarte con los repetidores de montaña y el resto de la comunidad andaluza, tu radio debe compartir exactamente los mismos parámetros de modulación:

1. **Región LoRa:** Accede a `Radio Config > LoRa` y selecciona **`EU_868`**. Cualquier otra opción (como US_915) no solo impedirá recibir paquetes comunitarios, sino que es ilegal en la Unión Europea.
2. **Canal primario comunitario (Slot 4 / SFNarrow):**
   * **Nombre del canal:** `SFNarrow`
   * **Frecuencia / Ranura:** `869.618 MHz` (Slot 4)
   * **Factor de dispersión:** `SF7`
   * **Ancho de banda:** `62.5 kHz`
   * **Coding Rate:** `4/5`
   * **Clave de cifrado:** `AQ==` (clave comunitaria por defecto pública).
   * *Consejo:* Puedes cargar estos parámetros de forma automática utilizando nuestro configurador guiado en [Configura tu nodo](/configura-tu-nodo).
3. **Identidad del dispositivo:**
   * **Nombre largo (Long Name):** Un nombre representativo (ejemplo: `Carlos - Chipiona Centro` o `EA7XYZ - Sevilla`).
   * **Nombre corto (Short Name):** 4 caracteres alfanuméricos que se mostrarán en pantallas compactas y listas de chat (ejemplo: `CHIP` o `SEV1`).
   * *Evita recargar el nombre con ristras de emojis:* cada carácter adicional incrementa el tamaño del paquete de radio emitido al aire.

---

### 4. Elección del rol: ¿CLIENT o CLIENT_MUTE?

La salud de la red depende del rol asignado a cada nodo:

* **CLIENT (Por defecto):** Indicado para nodos instalados en azoteas, pisos altos o ubicaciones con buena visibilidad exterior. Escucha, emite y colabora retransmitiendo paquetes ajenos con **3 saltos** (`hop_limit = 3`).
* **CLIENT_MUTE (Recomendado para interiores):** Si utilizas el nodo dentro de casa en una planta baja, en una habitación interior o en el bolsillo, configúralo en `CLIENT_MUTE`. Podrás enviar y recibir mensajes exactamente igual, pero tu nodo **no retransmitirá** paquetes débiles de otros nodos, evitando consumir batería innecesaria y reduciendo colisiones en el aire de tu ciudad.
* **ROUTER / REPEATER:** Reservado exclusivamente a repetidores fijos en cumbres y mástiles elevados dedicados a dar servicio comarcal ininterrumpido.

---

### 5. Tu primer mensaje de prueba en la malla

Una vez configurado:

1. Sal al balcón, azotea o un lugar despejado.
2. En la pestaña del canal público, escribe un mensaje breve identificando tu zona:
   > *"¡Hola a la malla desde Puerto Real (Cádiz)! Probando primer nodo T-Echo portátil."*
3. Observa el estado del envío: una pequeña marca de verificación (o indicador ACK) confirmará que al menos un nodo repetidor cercano ha recibido tu paquete y lo ha propagado.
4. **Respeta el aire:** Evita enviar mensajes repetitivos de «probando» cada minuto. La propagación en LoRa es asíncrona; da tiempo a que otros compañeros consulten la aplicación o deja tu nodo a la escucha mientras exploras los mapas en [MeshView](/meshview) y [PotatoMesh](/potatomesh).
MARKDOWN
            ],
            [
                'title' => 'Pruebas de cobertura y mapeo en ruta (Wardriving): cómo colaborar sin saturar',
                'slug' => 'pruebas-campo-mapeo-cobertura',
                'description' => 'Consejos para realizar pruebas de alcance en carretera, senderismo o puertos de montaña usando GPS y telemetría sin colapsar el ciclo de trabajo del canal común.',
                'featured_image' => 'img/servicios/meshview.webp',
                'keywords' => ['Wardriving', 'Cobertura', 'GPS', 'Mapeo', 'Radioenlace'],
                'is_active' => true,
                'content' => <<<'MARKDOWN'
## Mapear la malla: el valor de conocer el alcance real

Los cálculos teóricos de cobertura radioeléctrica sobre modelos digitales de elevación ofrecen una primera aproximación útil, pero la realidad del terreno en Andalucía —con masa forestal densa en parques naturales, apantallamiento de cascos históricos y microclimas costeros— sólo puede verificarse sobre el terreno.

El mapeo en ruta (habitualmente conocido como *wardriving* o *wardiaging* de radio) consiste en desplazarse en coche, bicicleta o a pie portando un nodo con receptor GPS activo para registrar qué repetidores captan nuestras señales y con qué nivel de calidad.

---

### 1. El gran peligro del mapeo descontrolado: La saturación del espectro

Un error frecuente entre usuarios entusiastas que salen de ruta es configurar su nodo para emitir coordenadas GPS cada 10 o 20 segundos pensando que así obtendrán un mapa más detallado.

**Las consecuencias para la comunidad son catastróficas:**
* En modulación LoRa a 868 MHz, una ráfaga de posición transmitida con 3 saltos mantiene ocupado el canal de radio durante varios cientos de milisegundos en cada repetidor que la replica.
* Si el paquete viaja a través de tres repetidores encadenados, un único envío bloquea el medio físico durante más de un segundo completo en toda la comarca.
* Emitir cada 15 segundos agota el ciclo de trabajo legal (*Duty Cycle*) de la banda, hace que los mensajes de texto de emergencias o de otros usuarios se pierdan por colisión y provoca que los sistemas automáticos del portal marquen el nodo como anómalo por spam de paquetes.

---

### 2. Configuración responsable del GPS para pruebas de campo

Para obtener un mapa de cobertura preciso sin degradar el servicio de los demás usuarios, sigue estas directrices:

1. **Intervalo de difusión temporal:** No configures nunca una cadencia fija inferior a **5 minutos (300 segundos)** en movimiento. Para trayectos por carretera o autovía, un intervalo de **10 a 15 minutos (600 a 900 s)** es más que suficiente para trazar la cobertura comarcal.
2. **Emisión inteligente por movimiento (Smart Position):** Si tu versión de firmware lo permite, activa la actualización por distancia en lugar de por tiempo fijo:
   * **Umbral de distancia mínima:** Configurar entre **500 y 1.000 metros**.
   * **Ángulo de giro (Heading change):** Entre **25° y 30°** para registrar cambios de rumbo significativos.
   * **Tiempo mínimo de guarda (Min interval):** Al menos **120 segundos** para impedir ráfagas consecutivas al arrancar o frenar en semáforos.
3. **Hop Limit moderado:** Ajusta el límite de saltos a **3** (`hop_limit = 3`). Si circulas por una carretera de montaña o puerto elevado con visión directa hacia un repetidor comarcal, reduce el valor a **2 saltos** para no propagar paquetes innecesarios hacia provincias limítrofes.

---

### 3. Cómo interpretar las métricas de señal recibida

Al revisar las confirmaciones recibidas en tu aplicación o consultar los visores cartográficos comunitarios:

| Métrica | Rango Típico | Interpretación Práctica |
| :--- | :---: | :--- |
| **SNR (Relación Señal/Ruido)** | `+5 a +13 dB` | Enlace muy potente y despejado; señal por encima del ruido de fondo. |
| **SNR Medio** | `0 a -8 dB` | Enlace estándar para enlaces a media distancia (15 a 40 km). |
| **SNR Límite** | `-9 a -18 dB` | **Límite extremo de sensibilidad.** LoRa demodula el paquete por debajo del umbral de ruido gracias a la ganancia de procesado del Factor de Dispersión (SF). |
| **RSSI** | `-50 a -80 dBm` | Señal de gran potencia en las inmediaciones del repetidor. |
| **RSSI Lejano** | `-110 a -124 dBm` | Señal débil al borde de la cobertura geográfica. |
| **Hops Consumidos** | `0, 1, 2 o 3` | Indica cuántos nodos intermedios han retransmitido la trama antes de llegar al receptor. |

---

### 4. Dónde consultar los datos recogidos

Toda la información captada por las pasarelas MQTT conectadas se proyecta en tiempo real en nuestras herramientas oficiales:

* **[PotatoMesh](/potatomesh):** Mapa ágil y de carga instantánea con la ubicación y telemetría de todos los nodos activos en el sur peninsular.
* **[MeshView](/meshview):** Visor topológico con matrices de enlaces directos (Traceroute) y líneas de conectividad entre repetidores.
* **[Revisa tu nodo](/revisa-tu-nodo):** Herramienta de autodiagnóstico del portal donde puedes introducir el identificador de tu radio para verificar si tus emisiones en ruta cumplen las buenas prácticas de la comunidad.
MARKDOWN
            ],
            [
                'title' => 'Criptografía y administración remota en Meshtastic 2.5+: canales simétricos frente a PKI',
                'slug' => 'seguridad-cifrado-pki-meshtastic',
                'description' => 'Diferencias entre claves simétricas de canal y cifrado asimétrico PKI para mensajes directos y administración remota de nodos sin compartir contraseñas maestras.',
                'featured_image' => 'img/paginas/default-banner.svg',
                'keywords' => ['Seguridad', 'PKI', 'Cifrado AES', 'Firmware 2.5', 'Administración Remota'],
                'is_active' => true,
                'content' => <<<'MARKDOWN'
## La evolución de la privacidad y el control en la red mallada

Desde sus primeras versiones, Meshtastic incorporó cifrado simétrico mediante el algoritmo estándar **AES** (con variantes de 128 y 256 bits). Esto permitía que grupos de usuarios compartieran una misma contraseña o clave precompartida (*PSK*) para mantener conversaciones ilegibles para receptores externos.

Sin embargo, a medida que la red andaluza fue madurando y se instalaron repetidores solares permanentes en cumbres y torretas de difícil acceso físico, la gestión de esos equipos y la privacidad de los mensajes personales exigieron una arquitectura criptográfica más avanzada.

La llegada de la infraestructura de clave pública (**PKI**) en el firmware **Meshtastic 2.5+** solucionó de raíz las limitaciones históricas del protocolo.

---

### 1. Canales de difusión: Cifrado simétrico AES

En los canales convencionales de difusión (broadcast), todos los participantes utilizan la misma clave secreta compartida:

* **Canal primario comunitario:** Utiliza una clave por defecto estándar y pública (`AQ==`), de modo que cualquier radio sintonizada en la misma frecuencia y slot pueda descifrar los paquetes y participar en la conversación abierta.
* **Canales secundarios privados:** Permiten a familias, asociaciones vecinales o agrupaciones de protección civil generar una clave AES aleatoria de 256 bits. Aunque los paquetes viajen por el aire a través de repetidores públicos comunitarios, los nodos repetidores solo actúan como enlaces ciegos: retransmiten la trama pero no pueden descifrar su contenido de texto.

---

### 2. Mensajería directa segura con PKI (Curve25519)

A partir de la versión 2.5, cada dispositivo Meshtastic genera localmente un par de claves criptográficas asimétricas al instalar el firmware:

1. **Clave privada:** Reside de forma protegida en la memoria interna del microcontrolador y nunca abandona el dispositivo bajo ningún concepto.
2. **Clave pública:** Se difunde de manera periódica y transparente a la red dentro del paquete de identidad del nodo (`NodeInfo`).

#### ¿Cómo funciona un mensaje privado (DM)?
Cuando envías un mensaje directo a otro nodo de la malla:
1. Tu radio utiliza la clave pública del receptor combinada con su propia clave privada para derivar un secreto compartido efímero mediante el algoritmo Diffie-Hellman en curva elíptica (**ECDH / Curve25519**).
2. El mensaje se cifra con ese secreto exclusivo y se emite al aire.
3. Únicamente el dispositivo destinatario —que posee la clave privada matemática complementaria— es capaz de desencriptar el mensaje.
4. **Candado verde verificado:** En la interfaz de la aplicación móvil, los nodos con clave pública aprendida y verificada muestran un candado de seguridad, garantizando que nadie en la cadena de repetidores puede espiar la conversación ni suplantar la identidad del emisor.

---

### 3. Administración remota de repetidores de infraestructura

Gestionar un nodo situado en lo alto de una sierra por radiofrecuencia (*Over-The-Air* o OTA) requería antiguamente crear un canal secundario denominado `admin` con una clave compartida. Si esa clave se filtraba o caía en manos de terceros, cualquiera podía alterar la configuración del router o dejarlo fuera de servicio.

En el ecosistema moderno:
* El administrador registra en el router la **clave pública** autorizada de su nodo de control personal (`admin_key` o lista de administradores autorizados).
* Para enviar comandos de configuración o consultar estadísticas avanzadas de telemetría, la consola establece un intercambio criptográfico autenticado mediante una clave de sesión efímera (**SessionKey**).
* Ningún usuario no autorizado puede enviar órdenes de administración aunque conozca el nombre del canal o la frecuencia de radio.

---

### 4. El temido «Código 39» (PKI Send Fail) y cómo resolverlo

Al interactuar con repetidores remotos o enviar mensajes directos, algunos operadores se encuentran con el siguiente aviso en consola:

```text
[ERROR] ❌ Rechazo de enrutamiento [Código 39] (PKI_SEND_FAIL_PUBLIC_KEY)
```

#### ¿Por qué ocurre?
Tu nodo emisor intenta transmitir un paquete cifrado con PKI dirigido a un nodo remoto, pero la memoria volátil de tu radio local todavía no ha recibido ni registrado la clave pública de ese destinatario.

#### Solución inmediata:
1. **Intercambio de identidad:** Envía una solicitud de información de nodo (`NodeInfo`) hacia el router de destino.
2. **Espera de recepción:** Cuando el router responda confirmando su identidad y entregando su clave pública, tu radio la almacenará en su base de datos interna de nodos (*NodeDB*).
3. **Reintento:** A partir de ese instante, cualquier orden administrativa o mensaje directo cifrado con PKI se transmitirá con éxito de forma transparente.
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
