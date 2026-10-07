<?php

declare(strict_types=1);

/**
 * config/proyecto.php
 *
 * Configuración central y agnóstica para Andalucía Mesh (Sur Nodos en Mallas).
 * Todos los valores provienen de variables de entorno o valores por defecto del proyecto.
 * Ningún texto en vistas o markdown debe tener estos datos quemados.
 */

$projectDomain = env('PROJECT_DOMAIN', 'mesh.desdechipiona.es');

return [
    'nombre' => env('PROJECT_NAME', 'Andalucía Mesh'),
    'dominio' => $projectDomain,
    'contacto' => env('PROJECT_CONTACT', 'public@raupulus.dev'),
    'zona_horaria' => env('TZ', 'Europe/Madrid'),

    // Parámetros de radio LoRa (SFNarrow oficial)
    'lora' => [
        'region' => env('LORA_REGION', 'EU_868'),
        'bandwidth' => (float) env('LORA_BANDWIDTH', 62),
        'bandwidth_khz' => (string) env('LORA_BANDWIDTH_KHZ', '62.5'),
        'spread_factor' => (int) env('LORA_SPREAD_FACTOR', 7),
        'coding_rate' => (int) env('LORA_CODING_RATE', 5),
        'frequency_slot' => (int) env('LORA_FREQUENCY_SLOT', 4),
        'frequency_mhz' => (string) env('LORA_FREQUENCY_MHZ', '869.618'),
        'hop_limit' => (int) env('LORA_HOP_LIMIT', 3),
        'preambulo' => (int) env('INGESTA_LORA_PREAMBULO', 16),
        'infra_roles' => array_filter(array_map('trim', explode(',', env('INFRA_ROLES', 'ROUTER,ROUTER_LATE,REPEATER')))),
    ],

    // Canales autorizados
    'canales' => [
        'permitidos' => array_filter(array_map('trim', explode(',', env('ALLOWED_CHANNELS', 'SFNarrow,Iberia,Andalucia,Cadiz,Huelva,Almeria,Granada,Jaen,Sevilla,Cordoba,Malaga,Ceuta,Melilla,sos')))),
        'primario' => env('PRIMARY_CHANNEL', 'SFNarrow'),
        'clave_defecto' => env('CHANNEL_KEY_DEFAULT', 'AQ=='),
    ],

    // Conexión pública a Mosquitto para gateways
    'mqtt' => [
        'host_publico' => 'mqtt.' . $projectDomain,
        'puerto_plano' => 1883,
        'puerto_tls' => 8883,
        'topic_root' => env('MQTT_TOPIC_ROOT', 'msh/EU_868'),
        'gateway_user' => env('MQTT_GATEWAY_USER', 'meshdev'),
        'gateway_password' => env('MQTT_GATEWAY_PASSWORD', 'large4cats'),
    ],

    // Configuración del Mapa Provincial
    'mapa' => [
        'centro' => [37.4, -4.5],
        'zoom' => 7,
        'carga' => [
            'verde_max' => (float) env('SATURACION_VERDE_MAX', 20.0),
            'rojo_min' => (float) env('SATURACION_ROJO_MIN', 40.0),
        ],
        'pesos' => [
            'router' => (float) env('SATURACION_PESO_ROUTER', 0.6),
            'cliente' => (float) env('SATURACION_PESO_CLIENTE', 0.4),
        ],
        // Polos de inaccesibilidad calculados dentro de cada provincia (X, Y normalizados en viewBox 1000 x 620)
        'burbujas' => [
            'ES-AL' => ['x' => 930, 'y' => 330], // Almería
            'ES-CA' => ['x' => 305, 'y' => 490], // Cádiz
            'ES-CO' => ['x' => 460, 'y' => 205], // Córdoba
            'ES-GR' => ['x' => 730, 'y' => 310], // Granada
            'ES-H'  => ['x' => 150, 'y' => 250], // Huelva
            'ES-J'  => ['x' => 675, 'y' => 165], // Jaén
            'ES-MA' => ['x' => 470, 'y' => 400], // Málaga
            'ES-SE' => ['x' => 350, 'y' => 280], // Sevilla
        ],
    ],

    // Provincias oficiales en orden canónico
    'provincias' => [
        'ES-AL' => 'Almería',
        'ES-CA' => 'Cádiz',
        'ES-CO' => 'Córdoba',
        'ES-GR' => 'Granada',
        'ES-H'  => 'Huelva',
        'ES-J'  => 'Jaén',
        'ES-MA' => 'Málaga',
        'ES-SE' => 'Sevilla',
    ],

    // Tres tarjetas fijas de la portada (en orden estricto)
    'tarjetas' => [
        [
            'id' => 'meshview',
            'titulo' => 'MeshView',
            'descripcion' => 'Visor técnico detallado con mapa topológico, nodos, enlaces y métricas de paquetes en directo.',
            'url' => 'https://' . env('MESHVIEW_DOMAIN', 'meshview.' . $projectDomain),
            'imagen' => '/img/servicios/meshview.webp',
            'servicio_clave' => 'meshview',
        ],
        [
            'id' => 'potatomesh',
            'titulo' => 'PotatoMesh',
            'descripcion' => 'Mapa web ligero y ágil optimizado para dispositivos móviles y consulta rápida en exteriores.',
            'url' => 'https://potato.' . $projectDomain,
            'imagen' => '/img/servicios/potatomesh.webp',
            'servicio_clave' => 'potatomesh',
        ],
        [
            'id' => 'rankings',
            'titulo' => 'Rankings y Actividad',
            'descripcion' => 'Estadísticas de consumo del espectro, nodos en riesgo, calidad de enlaces directos y mix de tráfico.',
            'url' => '/rankings',
            'imagen' => '/img/servicios/rankings.webp',
            'servicio_clave' => null,
        ],
    ],

    // Navegación principal y pie
    'navegacion' => [
        ['titulo' => 'Inicio', 'url' => '/'],
        ['titulo' => 'Configura tu nodo', 'url' => '/configura-tu-nodo'],
        ['titulo' => 'Conecta tu gateway', 'url' => '/conecta-tu-gateway'],
        ['titulo' => 'Rankings', 'url' => '/rankings'],
        ['titulo' => 'Alertas', 'url' => '/alertas'],
        ['titulo' => 'Bots', 'url' => '/bots'],
        ['titulo' => 'Revisa tu nodo', 'url' => '/revisa-tu-nodo'],
    ],

    'pie' => [
        ['titulo' => 'El proyecto', 'url' => '/proyecto'],
        ['titulo' => 'Quién lo impulsa', 'url' => '/quien-lo-impulsa'],
        ['titulo' => 'Cómo se gestiona', 'url' => '/como-se-gestiona'],
        ['titulo' => 'Firmware y apps', 'url' => '/firmware'],
        ['titulo' => 'API pública', 'url' => '/api'],
        ['titulo' => 'Aviso legal', 'url' => '/legal/aviso-legal'],
        ['titulo' => 'Privacidad', 'url' => '/legal/privacidad'],
        ['titulo' => 'Cookies', 'url' => '/legal/cookies'],
    ],

    // Bots y automatizaciones
    'bots' => [
        'telegram_username' => env('TELEGRAM_BOT_USERNAME', 'AndaluciaMesh_bot'),
        'discord_invite_url' => env('DISCORD_INVITE_URL', 'https://discord.gg/andalucia-mesh'),
        'riesgos_defecto' => env('BOT_RIESGOS_DEFECTO', 'alto'),
        'tipos_defecto' => env('BOT_TIPOS_DEFECTO', 'infraestructura'),
    ],

    // Firmware oficial Meshtastic
    'firmware' => [
        'cliente_web' => env('FW_URL_CLIENTE_WEB', 'https://client.meshtastic.org/'),
        'ios' => env('FW_URL_IOS', 'https://msh.to/ios'),
        'android' => env('FW_URL_ANDROID', 'https://msh.to/android'),
        'descargas' => env('FW_URL_DESCARGAS', 'https://meshtastic.org/downloads/'),
        'versiones' => env('FW_URL_VERSIONES', 'https://github.com/meshtastic/firmware/releases'),
        'web_flasher' => 'https://flasher.meshtastic.org/',
        'releases_github' => 'https://github.com/meshtastic/firmware/releases',
        'documentacion' => 'https://meshtastic.org/docs/',
        'app_android' => 'https://play.google.com/store/apps/details?id=com.geeksville.mesh',
        'app_ios' => 'https://apps.apple.com/app/meshtastic/id1586432531',
    ],

    // Aspectos legales y centro de datos
    'legal' => [
        'proveedor_hosting' => env('HOSTING_PROVIDER', 'Servidor dedicado en España / UE'),
        'ubicacion_hosting' => env('HOSTING_LOCATION', 'Unión Europea'),
        'credito_ign' => 'Límites provinciales: © Instituto Geográfico Nacional (IGN) bajo licencia CC BY 4.0',
    ],

    'seo' => [
        'descripcion_defecto' => 'Red regional comunitaria de radioenlaces de largo alcance LoRa Meshtastic en Andalucía.',
        'imagen_defecto' => '/img/og-andalucia-mesh.png',
    ],
];
