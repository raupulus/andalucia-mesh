<?php

declare(strict_types=1);

namespace App\Data\Ingest;

use App\Data\Fuente;
use App\Data\Resultado;
use Illuminate\Support\Facades\DB;

class Provincias
{
    public function __construct(
        protected CargaProvincias $cargaProvincias
    ) {}

    /**
     * Obtiene los nodos y saturación provincial según la ventana de tiempo.
     *
     * @param  string  $ventana  '30m', '1h', '6h', '12h', '1d', '24h', '7d' o '30d'
     * @return array<string, mixed>
     */
    public function obtener(string $ventana = '12h'): array
    {
        $res = $this->obtenerResultado($ventana);

        return $res->aRespuesta();
    }

    /**
     * Obtiene el Resultado completo con metadatos de caché.
     */
    public function obtenerResultado(string $ventana = '12h'): Resultado
    {
        $intervalo = match ($ventana) {
            '30m' => '30 minutes',
            '1h' => '1 hour',
            '6h' => '6 hours',
            '12h' => '12 hours',
            '1d', '24h' => '24 hours',
            '7d' => '7 days',
            '30d' => '30 days',
            default => '30 minutes',
        };

        return Fuente::recordar('stats_provincias', ['ventana' => $ventana], 60, function () use ($ventana, $intervalo) {
            // 1. Obtener recuento de nodos con posición en la ventana
            $conteos = DB::connection('ingesta')
                ->table('api_nodes')
                ->select([
                    'province',
                    DB::raw('count(*) as nodes'),
                    DB::raw('count(*) filter (where border_uncertain) as border_uncertain'),
                ])
                ->whereNotNull('province')
                ->where('last_position_at', '>=', DB::raw("now() - interval '{$intervalo}'"))
                ->groupBy('province')
                ->get()
                ->keyBy('province');

            // 2. Obtener saturación calculada para la ventana especificada
            $resCarga = $this->cargaProvincias->obtener($ventana);
            $datosCarga = (array) $resCarga->datos;
            $provinciasCarga = $datosCarga['provinces'] ?? [];

            // 3. Estructurar respuesta para las 8 provincias oficiales
            $provinciasConfig = config('proyecto.provincias', [
                'ES-AL' => 'Almería',
                'ES-CA' => 'Cádiz',
                'ES-CO' => 'Córdoba',
                'ES-GR' => 'Granada',
                'ES-H' => 'Huelva',
                'ES-J' => 'Jaén',
                'ES-MA' => 'Málaga',
                'ES-SE' => 'Sevilla',
            ]);

            $listaProvincias = [];
            $totalAndalucia = 0;

            foreach ($provinciasConfig as $code => $name) {
                $filaNodo = $conteos->get($code);
                $nodos = $filaNodo ? (int) $filaNodo->nodes : 0;
                $borderUncertain = $filaNodo ? (int) $filaNodo->border_uncertain : 0;

                $totalAndalucia += $nodos;

                $carga = $provinciasCarga[$code] ?? null;

                $listaProvincias[] = [
                    'code' => $code,
                    'name' => $name,
                    'nodes' => $nodos,
                    'border_uncertain' => $borderUncertain,
                    'avg' => $carga['avg'] ?? null,
                    'max' => $carga['max'] ?? null,
                    'level' => $carga['level'] ?? 'nodata',
                    'groups' => $carga['groups'] ?? [
                        'routers' => ['avg' => null, 'n' => 0],
                        'clients' => ['avg' => null, 'n' => 0],
                    ],
                ];
            }

            // Nodos fuera de Andalucía
            $filaFuera = $conteos->get('FUERA');
            $outsideAndalucia = $filaFuera ? (int) $filaFuera->nodes : 0;

            return [
                'window' => $ventana,
                'load_window' => $ventana,
                'total_andalucia' => $totalAndalucia,
                'andalucia_avg' => $datosCarga['andalucia_avg'] ?? null,
                'outside_andalucia' => $outsideAndalucia,
                'provinces' => $listaProvincias,
                'notes' => [
                    'Solo cuenta nodos con posición recibida con OK to MQTT activo.',
                ],
            ];
        });
    }
}
