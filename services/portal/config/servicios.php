<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Servicios supervisados por el panel de operador
    |--------------------------------------------------------------------------
    |
    | Define la lista de servicios y sus destinos de salud (HTTP, MQTT, DB o latido).
    | Conforme a docs/info/portal/14-operator-panel.md e integration.md §11.
    |
    */

    'timeout_segundos' => 3,

    'lista' => [
        'ingesta' => [
            'nombre' => 'Ingesta de paquetes',
            'tipo' => 'http',
            'url' => env('HEALTH_URL_INGESTA', 'http://ingesta:8080/health'),
            'fase' => 2,
        ],
        'adaptador-potato' => [
            'nombre' => 'Adaptador PotatoMesh',
            'tipo' => 'http',
            'url' => env('HEALTH_URL_ADAPTADOR', 'http://adaptador-potato:8080/health'),
            'fase' => 3,
        ],
        'sync-peers' => [
            'nombre' => 'Sincronización Peers',
            'tipo' => 'http',
            'url' => env('HEALTH_URL_SYNC_PEERS', 'http://sync-peers:8080/health'),
            'fase' => 4,
        ],
        'potatomesh' => [
            'nombre' => 'Instancia PotatoMesh',
            'tipo' => 'http',
            'url' => env('HEALTH_URL_POTATOMESH', 'http://potatomesh:41447/version'),
            'fase' => 1,
        ],
        'meshview' => [
            'nombre' => 'Instancia MeshView',
            'tipo' => 'http',
            'url' => env('HEALTH_URL_MESHVIEW', 'http://meshview:8081/health'),
            'fase' => 1,
        ],
        'mosquitto' => [
            'nombre' => 'Broker Mosquitto',
            'tipo' => 'mqtt',
            'host' => env('MQTT_HOST', '172.30.0.1'),
            'port' => (int) env('MQTT_PORT', 1884),
            'user' => env('MQTT_PANEL_USER', 'svc-panel'),
            'password' => env('MQTT_PANEL_PASSWORD', ''),
            'fase' => 1,
        ],
        'postgresql' => [
            'nombre' => 'Base de datos PostgreSQL',
            'tipo' => 'database',
            'conexiones' => ['pgsql', 'ingesta', 'alertas'],
            'fase' => 1,
        ],
        'portal-tareas' => [
            'nombre' => 'Daemon de Tareas (portal-tareas)',
            'tipo' => 'latido',
            'tarea' => 'comprobar-servicios',
            'max_retraso_segundos' => 180,
            'fase' => 5,
        ],
        'detector-alertas' => [
            'nombre' => 'Detector de Alertas',
            'tipo' => 'http',
            'url' => env('HEALTH_URL_DETECTOR', 'http://detector-alertas:8080/health'),
            'fase' => 6,
        ],
        'chat-ws' => [
            'nombre' => 'Chat WebSocket',
            'tipo' => 'http',
            'url' => env('HEALTH_URL_CHAT_WS', 'http://chat-ws:8080/health'),
            'fase' => 7,
        ],
        'bot-telegram' => [
            'nombre' => 'Bot de Telegram',
            'tipo' => 'http',
            'url' => env('HEALTH_URL_BOT_TELEGRAM', 'http://bot-telegram:8080/health'),
            'fase' => 8,
        ],
        'bot-discord' => [
            'nombre' => 'Bot de Discord',
            'tipo' => 'http',
            'url' => env('HEALTH_URL_BOT_DISCORD', 'http://bot-discord:8080/health'),
            'fase' => 8,
        ],
        'webhooks' => [
            'nombre' => 'Servicio de Webhooks',
            'tipo' => 'http',
            'url' => env('HEALTH_URL_WEBHOOKS', 'http://webhooks:8080/health'),
            'fase' => 8,
        ],
    ],
];
