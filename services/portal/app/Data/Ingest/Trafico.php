<?php

declare(strict_types=1);

namespace App\Data\Ingest;

use App\Data\Fuente;
use App\Data\Periodos;
use App\Data\Resultado;
use Illuminate\Support\Facades\DB;

class Trafico
{
    /**
     * Obtiene la distribución de tráfico y tiempo de aire por tipo de paquete (portnum).
     */
    public function obtenerResultado(string $period = 'day', string $which = 'current'): Resultado
    {
        $rango = Periodos::rango($period, $which);

        $params = [
            'period' => $period,
            'which' => $which,
            'bucket_start' => $rango['bucket_start'],
        ];

        return Fuente::recordar('traffic_mix', $params, $rango['ttl'], function () use ($rango, $period, $which) {
            $filas = DB::connection('ingesta')
                ->table('api_traffic_mix')
                ->where('granularity', $rango['granularity'])
                ->where('bucket_start', $rango['bucket_start'])
                ->orderByDesc('packets')
                ->get();

            $totalPackets = 0;
            $totalAirtime = 0.0;

            foreach ($filas as $f) {
                $totalPackets += (int) $f->packets;
                $totalAirtime += (float) $f->airtime_s;
            }

            $items = [];
            foreach ($filas as $f) {
                $p = (int) $f->packets;
                $a = (float) $f->airtime_s;

                $items[] = [
                    'portnum' => (string) $f->portnum,
                    'packets' => $p,
                    'packets_percentage' => $totalPackets > 0 ? round(($p / $totalPackets) * 100, 1) : 0.0,
                    'airtime_s' => round($a, 1),
                    'airtime_percentage' => $totalAirtime > 0 ? round(($a / $totalAirtime) * 100, 1) : 0.0,
                ];
            }

            return [
                'period' => $period,
                'which' => $which,
                'bucket_start' => $rango['bucket_start'],
                'partial' => $rango['partial'],
                'total_packets' => $totalPackets,
                'total_airtime_s' => round($totalAirtime, 1),
                'count' => count($items),
                'items' => $items,
            ];
        });
    }
}
