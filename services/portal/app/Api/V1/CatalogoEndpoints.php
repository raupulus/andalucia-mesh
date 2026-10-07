<?php

declare(strict_types=1);

namespace App\Api\V1;

class CatalogoEndpoints
{
    /**
     * Devuelve el catálogo completo de endpoints de la API pública v1.
     *
     * @return array<int, array{
     *     method: string,
     *     path: string,
     *     description: string,
     *     parameters: array<string, string>,
     *     cache_ttl_seconds: int,
     *     example_url: string
     * }>
     */
    public static function listar(): array
    {
        $domain = (string) config('proyecto.dominio', 'mesh.desdechipiona.es');
        $base = "https://{$domain}/api/v1";

        return [
            [
                'method' => 'GET',
                'path' => '/api/v1/',
                'description' => 'Índice y catálogo de endpoints de la API v1.',
                'parameters' => [],
                'cache_ttl_seconds' => 3600,
                'example_url' => "{$base}/",
            ],
            [
                'method' => 'GET',
                'path' => '/api/v1/stats/summary',
                'description' => 'Resumen global de actividad, nodos activos, routers, gateways y alertas.',
                'parameters' => [],
                'cache_ttl_seconds' => 60,
                'example_url' => "{$base}/stats/summary",
            ],
            [
                'method' => 'GET',
                'path' => '/api/v1/stats/provinces',
                'description' => 'Recuento de nodos y ocupación de canal estimada por provincia.',
                'parameters' => [
                    'window' => 'Ventana de tiempo para el recuento: 24h, 7d (por defecto) o 30d.',
                ],
                'cache_ttl_seconds' => 60,
                'example_url' => "{$base}/stats/provinces?window=7d",
            ],
            [
                'method' => 'GET',
                'path' => '/api/v1/stats/rankings',
                'description' => 'Listado y metadatos de los 11 rankings disponibles en la red.',
                'parameters' => [],
                'cache_ttl_seconds' => 3600,
                'example_url' => "{$base}/stats/rankings",
            ],
            [
                'method' => 'GET',
                'path' => '/api/v1/stats/rankings/{id}',
                'description' => 'Clasificación de nodos para un ranking específico y periodo seleccionado.',
                'parameters' => [
                    'period' => 'Granularidad: hour, day (por defecto), week o month.',
                    'which' => 'Momento: current (por defecto) o previous.',
                    'limit' => 'Número de elementos (1 a 50, por defecto 10).',
                ],
                'cache_ttl_seconds' => 60,
                'example_url' => "{$base}/stats/rankings/network-usage?period=day&limit=10",
            ],
            [
                'method' => 'GET',
                'path' => '/api/v1/stats/traffic-mix',
                'description' => 'Distribución de paquetes y tiempo de aire por tipo de mensaje (portnum).',
                'parameters' => [
                    'period' => 'Granularidad: hour, day (por defecto), week o month.',
                    'which' => 'Momento: current (por defecto) o previous.',
                ],
                'cache_ttl_seconds' => 300,
                'example_url' => "{$base}/stats/traffic-mix?period=day",
            ],
            [
                'method' => 'GET',
                'path' => '/api/v1/routers',
                'description' => 'Listado de routers de infraestructura, estado de batería y uso de espectro.',
                'parameters' => [
                    'province' => 'Filtro por código ISO provincial (ej. ES-CA).',
                    'sort' => 'Orden: battery_asc (por defecto), last_seen_desc o chutil_desc.',
                ],
                'cache_ttl_seconds' => 60,
                'example_url' => "{$base}/routers?sort=battery_asc",
            ],
            [
                'method' => 'GET',
                'path' => '/api/v1/nodes',
                'description' => 'Búsqueda de nodos por id (!xxxxxxxx), nombre corto o largo.',
                'parameters' => [
                    'search' => 'Término de búsqueda (mínimo 2 caracteres).',
                    'limit' => 'Límite de resultados (1 a 50, por defecto 20).',
                ],
                'cache_ttl_seconds' => 60,
                'example_url' => "{$base}/nodes?search=Chipiona",
            ],
            [
                'method' => 'GET',
                'path' => '/api/v1/nodes/{id}/diagnosis',
                'description' => 'Diagnóstico de configuración, batería, reinicios e intervalos de un nodo.',
                'parameters' => [],
                'cache_ttl_seconds' => 300,
                'example_url' => "{$base}/nodes/!1234abcd/diagnosis",
            ],
            [
                'method' => 'GET',
                'path' => '/api/v1/alerts/catalog',
                'description' => 'Catálogo oficial de reglas y riesgos del detector de anomalías.',
                'parameters' => [],
                'cache_ttl_seconds' => 3600,
                'example_url' => "{$base}/alerts/catalog",
            ],
            [
                'method' => 'GET',
                'path' => '/api/v1/alerts',
                'description' => 'Listado paginado de alertas activas y resueltas en la red.',
                'parameters' => [
                    'risk' => 'Filtro por riesgo: bajo, medio, alto.',
                    'type' => 'Filtro por tipo: infraestructura, clientes.',
                    'state' => 'open o resolved.',
                    'limit' => 'Cantidad por página (1 a 500, por defecto 50).',
                ],
                'cache_ttl_seconds' => 30,
                'example_url' => "{$base}/alerts?state=open",
            ],
            [
                'method' => 'GET',
                'path' => '/api/v1/alerts/{id}',
                'description' => 'Detalle histórico y técnico de una alerta por su identificador ULID.',
                'parameters' => [],
                'cache_ttl_seconds' => 30,
                'example_url' => "{$base}/alerts/01HN8...XYZ",
            ],
        ];
    }
}
