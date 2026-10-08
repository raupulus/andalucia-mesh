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
     * Obtiene el cálculo de saturación ponderada para las provincias y Andalucía.
     */
    public function obtener(): Resultado
    {
        return Fuente::recordar('carga_provincias', [], 60, function () {
            $filas = DB::connection('ingesta')
                ->table('api_province_load')
                ->select(['province', 'grupo', 'channel_utilization'])
                ->get()
                ->map(fn ($r) => (array) $r)
                ->toArray();

            return Saturacion::calcular($filas);
        });
    }
}
