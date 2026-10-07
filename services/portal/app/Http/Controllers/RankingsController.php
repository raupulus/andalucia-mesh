<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Datos\Ingesta\Rankings;
use App\Datos\Ingesta\Trafico;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Throwable;

class RankingsController extends Controller
{
    public function __construct(
        protected Rankings $rankings,
        protected Trafico $trafico,
    ) {}

    public function index(Request $request): View
    {
        $period = (string) $request->query('period', 'day');
        if (!in_array($period, ['hour', 'day', 'week', 'month'], true)) {
            $period = 'day';
        }

        $which = (string) $request->query('which', 'current');
        if (!in_array($which, ['current', 'previous'], true)) {
            $which = 'current';
        }

        // Obtener distribución de tráfico
        $datosTrafico = null;
        try {
            $resTrafico = $this->trafico->obtenerResultado($period, $which);
            $datosTrafico = $resTrafico->datos;
        } catch (Throwable) {
            $datosTrafico = [
                'total_packets' => 0,
                'total_airtime_s' => 0.0,
                'items' => [],
            ];
        }

        // Obtener ranking principal de uso de red
        $rankingUso = null;
        try {
            $resUso = $this->rankings->obtenerRanking('network-usage', $period, $which, 10);
            $rankingUso = $resUso->datos;
        } catch (Throwable) {
            $rankingUso = [
                'items' => [],
            ];
        }

        $catalogo = Rankings::catalogo();

        return view('rankings', [
            'period' => $period,
            'which' => $which,
            'trafico' => $datosTrafico,
            'rankingUso' => $rankingUso,
            'catalogo' => $catalogo,
        ]);
    }
}
