<?php

declare(strict_types=1);

namespace App\Data\Ingest;

use App\Data\Fuente;
use App\Data\Resultado;
use App\Data\Saturacion;
use Illuminate\Support\Facades\DB;

class CargaProvincias
{
    /**
     * Obtiene el cálculo de saturación ponderada para las provincias y Andalucía en la ventana dada.
     *
     * @param  string  $ventana  '30m', '1h', '6h', '12h', '1d', '24h', '7d', '30d'
     */
    public function obtener(string $ventana = '30m'): Resultado
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

        return Fuente::recordar('carga_provincias', ['ventana' => $ventana], 60, function () use ($intervalo) {
            $filas = DB::connection('ingesta')
                ->table('api_province_load')
                ->select(['province', 'grupo', 'channel_utilization'])
                ->where('measured_at', '>=', DB::raw("now() - interval '{$intervalo}'"))
                ->get()
                ->map(fn ($r) => (array) $r)
                ->toArray();

            return Saturacion::calcular($filas);
        });
    }
}
