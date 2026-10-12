<?php

declare(strict_types=1);

namespace App\Data\Ingest;

use App\Data\Fuente;
use App\Data\Periodos;
use App\Data\Resultado;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Trafico
{
    /**
     * Asegura la existencia de las tablas api_node_traffic en entornos de pruebas SQLite.
     */
    public static function ensureApiNodeTrafficTablesExist(): void
    {
        $schema = Schema::connection('ingesta');
        $driver = DB::connection('ingesta')->getDriverName();

        if ($driver === 'sqlite') {
            if (! $schema->hasTable('api_node_traffic_day')) {
                $schema->create('api_node_traffic_day', function (Blueprint $table) {
                    $table->timestamp('bucket');
                    $table->string('from_id', 32);
                    $table->string('province', 10)->nullable();
                    $table->string('portnum', 32);
                    $table->integer('packets')->default(1);
                    $table->float('airtime_s')->default(0.0);
                });
            }

            if (! $schema->hasTable('api_node_traffic_hour')) {
                $schema->create('api_node_traffic_hour', function (Blueprint $table) {
                    $table->timestamp('bucket');
                    $table->string('from_id', 32);
                    $table->string('province', 10)->nullable();
                    $table->string('portnum', 32);
                    $table->integer('packets')->default(1);
                    $table->float('airtime_s')->default(0.0);
                });
            }
        }
    }

    /**
     * Obtiene la distribución de tráfico y tiempo de aire por tipo de paquete (portnum)
     * filtrado opcionalmente por ámbito territorial (Andalucía, España, Ambos).
     */
    public function obtenerResultado(string $period = 'day', string $which = 'current', string $ambito = 'andalucia'): Resultado
    {
        self::ensureApiNodeTrafficTablesExist();

        $rango = Periodos::rango($period, $which);

        $params = [
            'period' => $period,
            'which' => $which,
            'ambito' => $ambito,
            'from' => $rango['from'],
            'to' => $rango['to'],
        ];

        return Fuente::recordar('traffic_mix', $params, $rango['ttl'], function () use ($rango, $period, $which, $ambito) {
            $table = ($period === 'hour') ? 'api_node_traffic_hour' : 'api_node_traffic_day';
            $query = DB::connection('ingesta')
                ->table($table)
                ->where('bucket', '>=', $rango['from'])
                ->where('bucket', '<', $rango['to']);

            if ($ambito === 'andalucia') {
                $query->whereIn('province', Routers::PROVINCIAS_ANDALUCIA);
            } elseif ($ambito === 'espana') {
                $query->where(function ($q) {
                    $q->whereNotIn('province', Routers::PROVINCIAS_ANDALUCIA)
                        ->orWhereNull('province');
                });
            }

            $filas = $query->select([
                'portnum',
                DB::raw('sum(packets) as packets'),
                DB::raw('sum(airtime_s) as airtime_s'),
            ])
                ->groupBy('portnum')
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
                'ambito' => $ambito,
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
