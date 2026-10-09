<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Controllers\Api\V1\AlertsApiController;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class AlertasController extends Controller
{
    /**
     * Catálogo enriquecido de metadatos de reglas del detector de anomalías.
     *
     * @return array{nombre: string, icono: string, categoria: string, descripcion: string, por_que: string, como_solucionar: string}
     */
    public static function infoRegla(string $reglaId): array
    {
        $catalogo = [
            'hops-high' => [
                'nombre' => 'Saltos excesivos (hop_start)',
                'icono' => '🔀',
                'categoria' => 'Configuración de nodo',
                'descripcion' => 'Nodo emitiendo paquetes con hop_start superior a lo recomendado en la red (6 bajo, >= 7 alto).',
                'por_que' => 'En una red mallada como Meshtastic, cada salto (hop) provoca que los repetidores en cobertura retransmitan el mensaje. Transmitir con 6 o 7 saltos hace que un solo mensaje rebote decenas de veces por toda la comunidad autónoma, saturando el tiempo de emisión de radio (airtime), provocando colisiones de paquetes y bloqueando las transmisiones de otros usuarios o llamadas de socorro.',
                'como_solucionar' => 'Abre la app Meshtastic en tu móvil o navegador → Ajustes de Radio → Configuración LoRa → cambia "Hop Limit" a 3 (o como máximo 5 si estás en un punto muy aislado sin repetidores cercanos). Guarda los cambios. La alerta se resolverá automáticamente cuando el nodo emita 2 paquetes con la nueva configuración.',
            ],
            'infra-silent' => [
                'nombre' => 'Router silente',
                'icono' => '📡',
                'categoria' => 'Infraestructura troncal',
                'descripcion' => 'Router o repetidor de infraestructura que deja de oírse según su intervalo típico (> 6 horas sin emitir).',
                'por_que' => 'Los repetidores forman la columna vertebral de la malla en Andalucía. Si uno deja de emitir, puede dejar sin enlace a comarcas o valles enteros.',
                'como_solucionar' => 'Comprueba la alimentación del repetidor (tensión de la batería, orientación del panel solar o suministro eléctrico) y verifica que la antena o cable coaxial no hayan sufrido daños por viento o lluvia. La alerta se resolverá automáticamente en cuanto el router vuelva a emitir un paquete propio a la malla.',
            ],
            'battery-low' => [
                'nombre' => 'Batería baja',
                'icono' => '🪫',
                'categoria' => 'Suministro y energía',
                'descripcion' => 'Nivel de batería crítico confirmado en 2 o más lecturas consecutivas de telemetría.',
                'por_que' => 'El nodo ha transmitido telemetría con un porcentaje de batería por debajo del umbral de seguridad (en routers: < 60 % aviso medio, < 40 % crítico; en clientes: < 35 % aviso bajo, < 15 % medio). Si la batería se agota por completo, el nodo se apagará de forma abrupta interrumpiendo las comunicaciones.',
                'como_solucionar' => 'Conecta el dispositivo a cargar o revisa el sistema fotovoltaico (batería, regulador solar y suciedad en el panel). Para routers de montaña, verifica la capacidad de descarga nocturna de las celdas LiFePO4 / 18650. La alerta se resolverá cuando el nivel supere el 70 % en routers o el 45 % en clientes, o al detectar alimentación externa continua.',
            ],
            'gateway-offline' => [
                'nombre' => 'Gateway desconectado',
                'icono' => '🌐',
                'categoria' => 'Conectividad MQTT',
                'descripcion' => 'Pasarela que deja de publicar recepciones en el broker MQTT regional (> 15 min medio, > 120 min alto).',
                'por_que' => 'Las pasarelas suben los paquetes oídos por radiofrecuencia a la plataforma web. Si una pasarela cae, los nodos de su área dejan de tener visibilidad en el mapa y en internet.',
                'como_solucionar' => 'Comprueba la conexión de red (WiFi, Ethernet o 4G) del gateway y que el servicio MQTT esté activo y autenticado. La alerta se resolverá en cuanto vuelva a subir un paquete.',
            ],
            'gateway-no-traffic' => [
                'nombre' => 'Pasarela sin tráfico LoRa (Radio sorda)',
                'icono' => '📻',
                'categoria' => 'Conectividad y radio',
                'descripcion' => 'Pasarela conectada a MQTT pero sin registrar recepciones LoRa durante más de 1 hora habiendo tenido tráfico previo.',
                'por_que' => 'El proceso de la pasarela sigue conectado a internet pero el chip de radiofrecuencia (SX1262/SX1276) ha dejado de recibir tramas por radio. Suele suceder por un bloqueo en el bus SPI entre microcontrolador y módulo LoRa, antena desconectada, atenuación severa o fallo de alimentación en la etapa de radiofrecuencia.',
                'como_solucionar' => "1. Reinicia el hardware de la pasarela (ciclo completo de alimentación).\n2. Comprueba físicamente la conexión de la antena LoRa, conectores coaxiales SMA/N y posible entrada de humedad.\n3. Revisa los registros (logs) del servicio Meshtastic o packet forwarder para verificar que el módulo SPI responde a la inicialización.\nLa alerta se resolverá tan pronto como la pasarela reciba y suba un paquete LoRa al broker.",
            ],
            'reboot-loop' => [
                'nombre' => 'Bucle de reinicios continuos',
                'icono' => '🔄',
                'categoria' => 'Estabilidad de hardware',
                'descripcion' => 'Detección de 3 reinicios en 5 minutos (aviso medio) o 5 o más en 10 minutos (alerta roja crítica).',
                'por_que' => 'Un nodo reiniciándose continuamente satura la frecuencia emitiendo paquetes de inicio (NodeInfo) repetitivos y no puede enrutar tráfico. La causa más habitual es una caída de tensión (brownout) al encender el transmisor LoRa por usar un cable USB deficiente, una batería con alta resistencia interna o condensadores agotados.',
                'como_solucionar' => "1. Revisa la alimentación: utiliza un cargador o regulador de calidad de al menos 1A a 5V con cable corto y grueso.\n2. Si funciona con batería, sustituye la celda o revisa el BMS.\n3. Si el reinicio ocurre tras una actualización de firmware, realiza un borrado completo de flash (nRF52/ESP32 erase) y reflashea la versión recomendada.\nLa alerta se resolverá automáticamente tras 30 minutos sin nuevos reinicios.",
            ],
            'chutil-high' => [
                'nombre' => 'Saturación del canal LoRa',
                'icono' => '📊',
                'categoria' => 'Uso de canal de radio',
                'descripcion' => 'Ocupación de canal superior al límite sostenible (> 20 % aviso bajo, > 30 % medio, > 40 % crítico).',
                'por_que' => 'El canal LoRa 868 MHz tiene un ancho de banda muy estrecho (duty cycle limitado y baja tasa de baudios). Una ocupación mayor al 20-30 % causa colisiones severas, pérdidas de paquetes de emergencia y un colapso generalizado de la malla en la provincia afectada.',
                'como_solucionar' => "1. Reduce los intervalos de telemetría de todos los nodos de la zona (mínimo 30-60 minutos).\n2. Baja el límite de saltos (hop_limit) a 3 en clientes.\n3. Desactiva transmisiones innecesarias de posición GPS fija o métricas de sensores secundarios.\n4. Evita el envío de imágenes o archivos pesados por la frecuencia principal.",
            ],
            'airtime-high' => [
                'nombre' => 'Uso de tiempo de aire excesivo (Airtime)',
                'icono' => '⏱️',
                'categoria' => 'Uso de canal de radio',
                'descripcion' => 'Tiempo de emisión propio por encima de límites operativos (> 4 % bajo, > 6 % medio, > 8 % alto).',
                'por_que' => 'En la banda de 868 MHz el tiempo de aire por hora (airtime) es un recurso estrictamente compartido. Consumir más del 4 al 8 % del ciclo de trabajo satura la frecuencia, activa las protecciones de duty cycle de los routers y bloquea la recepción del resto de usuarios de la comunidad.',
                'como_solucionar' => "1. Aumenta los intervalos de telemetría de dispositivo y entorno (mínimo 30-60 minutos).\n2. Configura los paquetes de posición GPS a intervalos amplios (30 min en nodos fijos o Smart Position con distancia mínima en móviles).\n3. Reduce el límite de saltos (hop_limit) a 3 para evitar retransmisiones innecesarias.\nLa alerta se resolverá automáticamente cuando el uso de canal propio descienda al 3 % o menos.",
            ],
            'text-flood' => [
                'nombre' => 'Inundación de mensajes de texto',
                'icono' => '💬',
                'categoria' => 'Tráfico de usuario',
                'descripcion' => 'Nodo emitiendo una cadencia excesiva de mensajes de texto (> 5/min bajo, 6-10/min medio, > 10/min alto).',
                'por_que' => 'Los mensajes de texto en canales públicos se difunden a través de todos los repetidores del área. Enviar más de 5 a 10 mensajes por minuto agota el ciclo de trabajo de los routers cercanos y bloquea la comunicación del resto de usuarios.',
                'como_solucionar' => 'No utilices canales públicos LoRa para conversaciones tipo chat en tiempo real continuo ni automatizaciones que envíen texto a alta velocidad. Si necesitas probar alcance, usa mensajes espaciados o canales directos punto a punto.',
            ],
            'telemetry-burst' => [
                'nombre' => 'Ráfaga excesiva de telemetría',
                'icono' => '📈',
                'categoria' => 'Intervalos de telemetría',
                'descripcion' => 'Emisiones repetitivas de métricas (ambiente, energía, batería o nodo) en menos de 1 minuto o más de 50 envíos en una hora.',
                'por_que' => 'El firmware de Meshtastic permite configurar intervalos para métricas de dispositivo, batería, sensores meteorológicos y NodeInfo. Cuando se configuran intervalos muy cortos (ej. cada 30 segundos), el nodo acapara el aire transmitiendo datos que apenas varían, perjudicando a toda la comunidad.',
                'como_solucionar' => "En la app Meshtastic → Ajustes del Módulo → Telemetría:\n• Device Metrics Interval: configurar a 1800 s (30 min) o 3600 s (1 hora).\n• Environment Metrics Interval (si tienes sensor BME280/SHT31): mínimo 900 s (15 min).\n• Power Metrics Interval: mínimo 1800 s.\nGuarda los cambios y reinicia el nodo.",
            ],
            'poll-abuse' => [
                'nombre' => 'Sondeos repetitivos a la red (Broadcast Poll)',
                'icono' => '🔍',
                'categoria' => 'Consultas de red',
                'descripcion' => 'Peticiones de sondeo masivas a toda la malla (^all) solicitando telemetría, batería o nodeinfo.',
                'por_que' => 'Una petición de sondeo broadcast obliga a TODOS los nodos en cobertura a responder simultáneamente por radio, provocando una avalancha de colisiones y congelando el canal durante minutos.',
                'como_solucionar' => 'Nunca solicites información de nodos de forma masiva a la dirección general (^all). Si necesitas conocer el estado o la telemetría de un nodo concreto, haz una consulta directa (DM/unicast) a su identificador hex.',
            ],
            'sunset-battery' => [
                'nombre' => 'Batería insuficiente al anochecer',
                'icono' => '🌇',
                'categoria' => 'Suministro y energía',
                'descripcion' => 'Router solar que llega a las 20:00 h con menos del 60 % de carga (medio) o menos del 40 % (alto).',
                'por_que' => 'En instalaciones fotovoltaicas aisladas, el nodo debe afrontar entre 10 y 14 horas de noche sin radiación solar. Si el repetidor inicia la noche con menos del 60 %, es casi seguro que sufrirá un apagón antes del amanecer, dejando a su comarca sin cobertura.',
                'como_solucionar' => 'Revisa el dimensionamiento del sistema solar: aumenta la capacidad del banco de baterías (mínimo 3 días de autonomía), limpia la superficie del panel solar o ajusta su inclinación y orientación (óptimo 35-40° sur en Andalucía). Comprueba que el regulador no tenga pérdidas.',
            ],
            'traceroute-flood' => [
                'nombre' => 'Abuso de Traceroute',
                'icono' => '🗺️',
                'categoria' => 'Herramientas de diagnóstico',
                'descripcion' => 'Ejecución masiva de traceroutes en poco tiempo (10-19 en 30 min aviso medio, >= 20 alerta alta).',
                'por_que' => 'El comando Traceroute genera paquetes especiales que recopilan las rutas de ida y vuelta de todos los nodos intermedios. Cada traceroute genera múltiples tramas de alto tamaño. Lanzar decenas seguidas congestiona la malla y satura las colas de retransmisión.',
                'como_solucionar' => 'Utiliza la herramienta de traceroute únicamente para diagnóstico puntual. Espera varios minutos entre pruebas y no uses scripts automatizados que lancen traceroutes periódicos a la malla.',
            ],
            'private-chaff' => [
                'nombre' => 'Tráfico privado o sensores en malla pública',
                'icono' => '🔒',
                'categoria' => 'Uso del canal público',
                'descripcion' => 'Nodo emitiendo ráfagas frecuentes de paquetes cifrados o tráfico de sensores privados sobre el canal público de la comunidad.',
                'por_que' => 'Ocurre habitualmente cuando un usuario instala sensores domésticos o sistemas de domótica en su vivienda utilizando la misma frecuencia comunitaria pero con claves privadas. Todos los repetidores de la comarca retransmiten obligatoriamente estos paquetes opacos, consumiendo su valioso tiempo de radio para un uso estrictamente privado.',
                'como_solucionar' => 'Para proyectos privados de telemetría doméstica o sensores entre habitaciones, configura tus dispositivos en un slot/canal de radio secundario, con un módem preestablecido diferente o reduce el hop limit a 0 para que los paquetes no salgan de tu domicilio y no obliguen a los repetidores de montaña a reenviarlos.',
            ],
            'position-flood' => [
                'nombre' => 'Posiciones GPS aceleradas',
                'icono' => '📍',
                'categoria' => 'Geolocalización',
                'descripcion' => 'Nodo emitiendo su posición geográfica con intervalos excesivamente reducidos (cada 1-3 minutos de forma continuada).',
                'por_que' => 'La posición geográfica de un nodo estático no cambia, y en un nodo móvil en malla LoRa transmitir cada 60-120 segundos satura los repetidores comunitarios. Cada paquete de posición contiene coordenadas y altitud que no necesitan refresco continuo en redes de baja velocidad.',
                'como_solucionar' => "En la app Meshtastic → Ajustes de Posición:\n• Si el nodo es fijo en casa o repetidor: configura las coordenadas fijas (Fixed Position) y pon el intervalo de envío en al menos 1800 s (30 min) o 3600 s (1 hora).\n• Si el nodo es móvil / tracker: activa Smart Position con un intervalo mínimo de 300 s (5 min) y distancia mínima de movimiento (100 m).",
            ],
            'router-role' => [
                'nombre' => 'Rol ROUTER no coordinado en Andalucía',
                'icono' => '⚠️',
                'categoria' => 'Topología y arquitectura',
                'descripcion' => 'Nodo ubicado en Andalucía configurado con rol ROUTER o REPEATER sin estar aprobado en la lista de infraestructura coordinada.',
                'por_que' => 'En Meshtastic, los roles ROUTER y REPEATER tienen privilegios de enrutamiento preferente: retransmiten paquetes antes que nadie y modifican los algoritmos de retroceso (backoff). Si un usuario activa el rol ROUTER en un nodo doméstico o portátil sin coordinación, compite con los repetidores de alta cota, provoca colisiones masivas y desestabiliza la red.',
                'como_solucionar' => 'En la app Meshtastic → Ajustes de Dispositivo → Rol del Dispositivo (Device Role): Cambia el rol a "CLIENT" (o "CLIENT_BASE" si es una estación base fija en casa). No utilices nunca "ROUTER" ni "REPEATER" a menos que formes parte del equipo de infraestructura de tu provincia y el nodo haya sido aprobado por los coordinadores.',
            ],
            'router-moving' => [
                'nombre' => 'Repetidor en movimiento físico',
                'icono' => '🚚',
                'categoria' => 'Topología e infraestructura',
                'descripcion' => 'Router o repetidor con un desplazamiento geográfico superior a 5 km en 24 horas.',
                'por_que' => 'Los roles ROUTER y REPEATER están reservados exclusivamente a infraestructuras fijas en cotas elevadas. Un desplazamiento físico superior a 5 km indica que un nodo portátil o móvil ha sido configurado indebidamente con un rol de infraestructura, o que un repetidor fijo ha sido trasladado sin actualizar ni coordinar su ubicación.',
                'como_solucionar' => "1. Si el nodo es portátil o viaja en un vehículo, cambia inmediatamente su rol a CLIENT o CLIENT_MUTE en Ajustes de Radio → Rol del Dispositivo.\n2. Si se trata de un repetidor fijo que ha sido reubicado legítimamente, coordina la nueva posición con el equipo de infraestructura de tu provincia para actualizar el inventario.\nLa alerta se resolverá automáticamente cuando el nodo permanezca estable (desplazamiento < 3 km) durante 24 horas.",
            ],
            'router-cluster' => [
                'nombre' => 'Concentración redundante de routers',
                'icono' => '👥',
                'categoria' => 'Topología e infraestructura',
                'descripcion' => 'Router enlazado directamente con 3 o más routers simultáneamente (excluye ROUTER_LATE).',
                'por_que' => 'Cuando tres o más repetidores con rol ROUTER tienen visibilidad mutua directa de radiofrecuencia (0 saltos), compiten agresivamente por retransmitir el mismo paquete. Esto provoca colisiones de tramas en el aire, ecos duplicados innecesarios y una degradación drástica del rendimiento de la malla.',
                'como_solucionar' => "1. Ajusta los roles de la zona: mantén como ROUTER solo al repetidor con mejor cota y cobertura general, y reconfigura los routers secundarios cercanos como ROUTER_LATE o CLIENT_BASE.\n2. El rol ROUTER_LATE espera deliberadamente a que otros routers retransmitan primero antes de actuar, evitando la competición directa.\nLa alerta se resolverá cuando el nodo reduzca sus enlaces directos con otros routers a 2 o menos durante 24 horas.",
            ],
            'asymmetric-link' => [
                'nombre' => 'Enlace RF asimétrico severo',
                'icono' => '⚖️',
                'categoria' => 'Radiofrecuencia y enlaces',
                'descripcion' => 'Discrepancia de señal severa (|SNR A→B − SNR B→A| > 6 dB) entre nodos fijos de infraestructura o clientes.',
                'por_que' => 'En un enlace de radio simétrico ideal, la relación señal/ruido (SNR) medida en ambos sentidos debe ser similar. Una discrepancia superior a 6 dB (excluyendo nodos CLIENT_MUTE que se mueven por interiores) indica problemas graves: un conector coaxial mal crimpado, atenuación por cables dañados, suelo de ruido local elevado en uno de los extremos o desbalance de potencia de emisión.',
                'como_solucionar' => "1. Revisa físicamente los conectores SMA/N, pigtails y cables coaxiales de las antenas en ambos extremos para descartar holguras o humedad.\n2. Comprueba si uno de los nodos está cerca de fuentes de interferencia electromagnética (fuentes conmutadas, inversores solares o routers WiFi).\n3. Verifica que la potencia de salida configurada (TX power) sea adecuada y pareja en ambos equipos.\nLa alerta se resolverá automáticamente cuando la diferencia de SNR descienda por debajo de 4 dB.",
            ],
            'key-security' => [
                'nombre' => 'Seguridad y alteración de clave pública',
                'icono' => '🔐',
                'categoria' => 'Seguridad e identidad',
                'descripcion' => 'Clave pública Curve25519 con baja entropía estructural o sustitución imprevista de clave en el mismo nodo.',
                'por_que' => 'Meshtastic utiliza claves públicas para la autenticación y cifrado de paquetes. La detección de claves con patrones repetitivos o secuencias correlativas indica un generador de números aleatorios (RNG/TRNG) defectuoso en el microcontrolador o una corrupción de memoria flash. Si además se detecta un cambio repentino de clave pública en un nodo previamente consolidado, podría indicar un intento de suplantación de identidad en la malla.',
                'como_solucionar' => "1. Si el aviso es por entropía débil, regenera el par de claves del dispositivo o reflashea el firmware limpio realizando un borrado completo de la memoria flash (flash erase).\n2. Si se ha sustituido intencionadamente el hardware manteniendo el mismo Node ID, confirma el cambio legítimo en los canales comunitarios o asigna un nuevo identificador.\nLa alerta se resolverá cuando el nodo transmita paquetes con una clave pública válida y estable.",
            ],
            'flood' => [
                'nombre' => 'Inundación de paquetes (Flood)',
                'icono' => '🌊',
                'categoria' => 'Uso de canal de radio',
                'descripcion' => 'Nodo emitiendo tráfico propio a un ritmo muy superior a lo normal en una ventana de 10 minutos.',
                'por_que' => 'El espectro de radio en 868 MHz es un medio compartido. Emitir a un ritmo desproporcionado congestiona los repetidores y puede superar el límite legal de ciclo de trabajo (duty cycle).',
                'como_solucionar' => 'Aumenta el intervalo de envío de telemetría y mensajes de posición (se recomiendan al menos 15 a 30 minutos). Desactiva plugins o scripts automáticos que envíen mensajes de texto frecuentes a canales públicos.',
            ],
            'rafaga-masiva' => [
                'nombre' => 'Ráfaga masiva simultánea',
                'icono' => '⚡',
                'categoria' => 'Evento colectivo de red',
                'descripcion' => 'Múltiples nodos emitiendo de forma sincronizada en la malla en menos de 2 minutos.',
                'por_que' => 'Se ha registrado una oleada simultánea de paquetes de más de 50 nodos a la vez. Suele suceder cuando un mensaje broadcast es respondido en masa o tras recuperarse un repetidor central.',
                'como_solucionar' => 'Evento de red colectivo. Evitar transmisiones masivas innecesarias hasta que el espectro recupere la calma.',
            ],
        ];

        return $catalogo[$reglaId] ?? [
            'nombre' => ucwords(str_replace(['-', '_'], ' ', $reglaId)),
            'icono' => '⚠️',
            'categoria' => 'Incidencia técnica',
            'descripcion' => "Anomalía detectada por la regla '{$reglaId}'.",
            'por_que' => 'El detector automático ha registrado un comportamiento que se desvía de los parámetros nominales de la red.',
            'como_solucionar' => 'Revisa la configuración del nodo o consulta la guía de configuración recomendada de la red.',
        ];
    }

    /**
     * Normaliza y enriquece un registro de alerta para su renderizado consistente en vistas.
     *
     * @param  object|array<string, mixed>  $alerta
     * @return array<string, mixed>
     */
    public static function normalizarAlerta(object|array $alerta): array
    {
        $a = (array) $alerta;

        $nodoInfo = $a['nodo_info'] ?? null;
        if (is_string($nodoInfo)) {
            $nodoInfo = json_decode($nodoInfo, true) ?: [];
        } elseif (! is_array($nodoInfo)) {
            $nodoInfo = [];
        }
        $a['nodo_info'] = $nodoInfo;

        $datos = $a['datos'] ?? null;
        if (is_string($datos)) {
            $datos = json_decode($datos, true) ?: [];
        } elseif (! is_array($datos)) {
            $datos = [];
        }
        $a['datos'] = $datos;

        $nodos = $a['nodos'] ?? [];
        if (is_string($nodos)) {
            $nodos = str_replace(['{', '}'], '', $nodos);
            $nodos = $nodos ? explode(',', $nodos) : [];
        }
        $a['nodos'] = is_array($nodos) ? $nodos : [];

        $reglaId = (string) ($a['regla'] ?? '');
        $metaRegla = self::infoRegla($reglaId);

        $a['regla_nombre'] = $metaRegla['nombre'];
        $a['regla_icono'] = $metaRegla['icono'];
        $a['regla_categoria'] = $metaRegla['categoria'];
        $a['regla_por_que'] = $metaRegla['por_que'];
        $a['regla_como_solucionar'] = $metaRegla['como_solucionar'];

        $nodoId = (string) ($a['nodo'] ?? '');
        $a['nodo_es_global'] = empty($nodoId) || $nodoId === 'all';
        $a['nodo_codigo'] = $nodoId;
        $a['nodo_nombre_corto'] = $nodoInfo['corto'] ?? $nodoInfo['short'] ?? ($a['nodo_es_global'] ? null : $nodoId);
        $a['nodo_nombre_largo'] = $nodoInfo['largo'] ?? $nodoInfo['long'] ?? null;
        $a['nodo_rol'] = $nodoInfo['rol'] ?? $nodoInfo['role'] ?? null;
        $a['nodo_provincia'] = $a['provincia'] ?? $nodoInfo['provincia'] ?? null;

        return $a;
    }

    /**
     * Catálogo canónico de temáticas o tipos de problemas para filtrado en la vista pública.
     *
     * @return array<string, array{nombre: string, icono: string, reglas: list<string>}>
     */
    public static function categoriasProblemas(): array
    {
        return [
            'bateria' => [
                'nombre' => 'Batería y Energía',
                'icono' => '🪫',
                'reglas' => ['battery-low', 'sunset-battery'],
            ],
            'posicion' => [
                'nombre' => 'Posición y GPS',
                'icono' => '📍',
                'reglas' => ['position-flood', 'router-moving'],
            ],
            'telemetria' => [
                'nombre' => 'Telemetría y Nodeinfo',
                'icono' => '📈',
                'reglas' => ['telemetry-burst', 'poll-abuse'],
            ],
            'traceroutes' => [
                'nombre' => 'Traceroutes',
                'icono' => '🗺️',
                'reglas' => ['traceroute-flood'],
            ],
            'spam' => [
                'nombre' => 'Spam e Inundación',
                'icono' => '💬',
                'reglas' => ['text-flood', 'flood', 'rafaga-masiva', 'private-chaff'],
            ],
            'canal' => [
                'nombre' => 'Canal y Saltos',
                'icono' => '📊',
                'reglas' => ['chutil-high', 'airtime-high', 'hops-high'],
            ],
            'infraestructura' => [
                'nombre' => 'Gateways e Infraestructura',
                'icono' => '🌐',
                'reglas' => ['gateway-offline', 'gateway-no-traffic', 'infra-silent', 'router-role', 'router-cluster'],
            ],
            'seguridad' => [
                'nombre' => 'Hardware y Seguridad',
                'icono' => '🔐',
                'reglas' => ['reboot-loop', 'key-security', 'asymmetric-link'],
            ],
        ];
    }

    /**
     * Listado público de alertas de la red.
     */
    public function index(Request $request): View
    {
        $estado = $request->query('estado');
        $riesgo = $request->query('riesgo');
        $problemaParam = $request->query('problemas', $request->query('problema'));

        $detectorCalibrando = false;

        $categorias = self::categoriasProblemas();

        $problemasSeleccionados = [];
        if (is_string($problemaParam) && trim($problemaParam) !== '') {
            foreach (explode(',', $problemaParam) as $t) {
                $t = trim($t);
                if (isset($categorias[$t])) {
                    $problemasSeleccionados[] = $t;
                }
            }
        } elseif (is_array($problemaParam)) {
            foreach ($problemaParam as $t) {
                if (is_string($t) && isset($categorias[trim($t)])) {
                    $problemasSeleccionados[] = trim($t);
                }
            }
        }
        $problemasSeleccionados = array_values(array_unique($problemasSeleccionados));

        try {
            $query = DB::connection('alertas')
                ->table('api_alertas')
                ->orderByDesc('inicio_at')
                ->orderByDesc('id');

            if ($estado) {
                $query->where('estado', $estado);
            }
            if ($riesgo) {
                $query->where('riesgo', $riesgo);
            }
            if (! empty($problemasSeleccionados)) {
                $reglasAFiltrar = [];
                foreach ($problemasSeleccionados as $p) {
                    $reglasAFiltrar = array_merge($reglasAFiltrar, $categorias[$p]['reglas']);
                }
                $query->whereIn('regla', array_values(array_unique($reglasAFiltrar)));
            }

            /** @var LengthAwarePaginator<object> $paginador */
            $paginador = $query->paginate(15)->withQueryString();
            $alertas = $paginador->through(fn ($f) => self::normalizarAlerta($f));
        } catch (Throwable) {
            $detectorCalibrando = true;
            $alertas = new LengthAwarePaginator([], 0, 15);
        }

        $reglas = AlertsApiController::catalogoReglas();

        return view('alertas.index', [
            'alertas' => $alertas,
            'detectorCalibrando' => $detectorCalibrando,
            'reglas' => $reglas,
            'estadoFiltro' => $estado,
            'riesgoFiltro' => $riesgo,
            'problemasFiltro' => $problemasSeleccionados,
            'categoriasProblemas' => $categorias,
        ]);
    }

    /**
     * Detalle técnico e histórico de una alerta por identificador.
     */
    public function show(string $id): View
    {
        $alerta = null;
        try {
            $alerta = DB::connection('alertas')
                ->table('api_alertas')
                ->where('id', strtoupper($id))
                ->first();
        } catch (Throwable) {
            $alerta = null;
        }

        if (! $alerta) {
            throw new NotFoundHttpException("La alerta '{$id}' no existe o ha expirado.");
        }

        return view('alertas.show', [
            'alerta' => self::normalizarAlerta($alerta),
        ]);
    }
}
