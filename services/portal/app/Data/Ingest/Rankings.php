<?php

declare(strict_types=1);

namespace App\Data\Ingest;

use App\Data\Fuente;
use App\Data\Periodos;
use App\Data\Resultado;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class Rankings
{
    /**
     * Catálogo maestro de los 11 rankings de la red Andalucía Mesh.
     *
     * @return array<string, array{id: string, nombre: string, descripcion: string, unidad: string, sujeto: string, tabla: string}>
     */
    public static function catalogo(): array
    {
        return [
            'network-usage' => [
                'id' => 'network-usage',
                'nombre' => 'Uso del espectro y tiempo de aire',
                'descripcion' => 'Segundos de emisión acumulados por los paquetes que origina cada nodo.',
                'unidad' => 'segundos',
                'sujeto' => 'node',
                'tabla' => 'api_rank_network_usage',
            ],
            'gateway-coverage' => [
                'id' => 'gateway-coverage',
                'nombre' => 'Cobertura de gateways',
                'descripcion' => 'Número de nodos distintos recibidos por cada gateway.',
                'unidad' => 'nodos',
                'sujeto' => 'gateway',
                'tabla' => 'api_rank_gateway_coverage',
            ],
            'gateway-exclusive' => [
                'id' => 'gateway-exclusive',
                'nombre' => 'Gateways exclusivos',
                'descripcion' => 'Paquetes capturados y subidos únicamente por ese gateway.',
                'unidad' => 'paquetes',
                'sujeto' => 'gateway',
                'tabla' => 'api_rank_gateway_exclusive',
            ],
            'longest-links' => [
                'id' => 'longest-links',
                'nombre' => 'Enlaces directos de mayor alcance',
                'descripcion' => 'Distancia en kilómetros del enlace directo más largo entre nodo y gateway.',
                'unidad' => 'km',
                'sujeto' => 'node',
                'tabla' => 'api_rank_longest_links',
            ],
            'best-links' => [
                'id' => 'best-links',
                'nombre' => 'Mejor calidad de enlace directo (SNR)',
                'descripcion' => 'Relación señal/ruido media en decibelios con un mínimo de 10 paquetes.',
                'unidad' => 'dB',
                'sujeto' => 'node',
                'tabla' => 'api_rank_best_links',
            ],
            'most-neighbors' => [
                'id' => 'most-neighbors',
                'nombre' => 'Nodos con mayor vecindad directa',
                'descripcion' => 'Número de nodos vecinos escuchados directamente a 0 saltos.',
                'unidad' => 'vecinos',
                'sujeto' => 'node',
                'tabla' => 'api_rank_most_neighbors',
            ],
            'uptime' => [
                'id' => 'uptime',
                'nombre' => 'Tiempo activo ininterrumpido',
                'descripcion' => 'Horas continuas de funcionamiento sin reinicio.',
                'unidad' => 'horas',
                'sujeto' => 'node',
                'tabla' => 'api_rank_uptime',
            ],
            'solar-health' => [
                'id' => 'solar-health',
                'nombre' => 'Salud de nodos solares',
                'descripcion' => 'Nivel mínimo de batería registrado durante el ciclo nocturno.',
                'unidad' => '%',
                'sujeto' => 'node',
                'tabla' => 'api_rank_solar_health',
            ],
            'chatters' => [
                'id' => 'chatters',
                'nombre' => 'Emisión de mensajes de texto',
                'descripcion' => 'Mensajes públicos enviados a canales de la red.',
                'unidad' => 'mensajes',
                'sujeto' => 'node',
                'tabla' => 'api_rank_chatters',
            ],
            'new-nodes' => [
                'id' => 'new-nodes',
                'nombre' => 'Nuevos nodos registrados',
                'descripcion' => 'Nodos escuchados por primera vez en la malla durante el periodo.',
                'unidad' => 'fecha',
                'sujeto' => 'node',
                'tabla' => 'api_rank_new_nodes',
            ],
            'provinces-growth' => [
                'id' => 'provinces-growth',
                'nombre' => 'Crecimiento por provincia',
                'descripcion' => 'Variación neta de nodos activos respecto al periodo anterior.',
                'unidad' => 'nodos',
                'sujeto' => 'province',
                'tabla' => 'api_rank_provinces_growth',
            ],
        ];
    }

    /**
     * Obtiene los resultados de un ranking concreto.
     */
    public function obtenerRanking(string $id, string $period = 'day', string $which = 'current', int $limit = 10): Resultado
    {
        $catalogo = self::catalogo();
        if (!isset($catalogo[$id])) {
            throw new InvalidArgumentException("Ranking desconocido '{$id}'.");
        }

        $meta = $catalogo[$id];
        $rango = Periodos::rango($period, $which);
        $limit = max(1, min(50, $limit));

        $params = [
            'id' => $id,
            'period' => $period,
            'which' => $which,
            'limit' => $limit,
            'bucket_start' => $rango['bucket_start'],
        ];

        return Fuente::recordar("rank:{$id}", $params, $rango['ttl'], function () use ($meta, $rango, $limit, $period, $which) {
            $filas = DB::connection('ingesta')
                ->table($meta['tabla'])
                ->where('granularity', $rango['granularity'])
                ->where('bucket_start', $rango['bucket_start'])
                ->orderByDesc('value')
                ->limit($limit)
                ->get();

            // Si los sujetos son nodos, enriquecer con nombres públicos
            $nodeIds = [];
            if ($meta['sujeto'] === 'node') {
                foreach ($filas as $f) {
                    $nodeIds[] = (string) $f->subject_id;
                }
            }

            $infoNodos = [];
            if (!empty($nodeIds)) {
                $infoNodos = DB::connection('ingesta')
                    ->table('api_nodes')
                    ->whereIn('id', $nodeIds)
                    ->select(['id', 'short_name', 'long_name', 'province', 'role', 'hw_model'])
                    ->get()
                    ->keyBy('id');
            }

            $items = [];
            $posicion = 1;
            foreach ($filas as $f) {
                $sid = (string) $f->subject_id;
                $extra = is_string($f->extra) ? json_decode($f->extra, true) : (array) $f->extra;

                $item = [
                    'rank' => $posicion++,
                    'subject_id' => $sid,
                    'value' => round((float) $f->value, 1),
                    'extra' => $extra,
                ];

                if (isset($infoNodos[$sid])) {
                    $n = $infoNodos[$sid];
                    $item['node'] = [
                        'short_name' => $n->short_name,
                        'long_name' => $n->long_name,
                        'province' => $n->province,
                        'role' => $n->role,
                        'hw_model' => $n->hw_model,
                    ];
                }

                $items[] = $item;
            }

            return [
                'id' => $meta['id'],
                'name' => $meta['nombre'],
                'description' => $meta['descripcion'],
                'unit' => $meta['unidad'],
                'subject_type' => $meta['sujeto'],
                'period' => $period,
                'which' => $which,
                'bucket_start' => $rango['bucket_start'],
                'partial' => $rango['partial'],
                'count' => count($items),
                'items' => $items,
            ];
        });
    }
}
