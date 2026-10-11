<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Data\Ingest\Rankings;
use App\Data\Ingest\Routers;
use App\Data\Ingest\Trafico;
use App\Data\Periodos;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        if (! in_array($period, ['hour', 'day', 'week', '14d', 'month'], true)) {
            $period = 'day';
        }

        $which = (string) $request->query('which', 'current');
        if (! in_array($which, ['current', 'previous'], true)) {
            $which = 'current';
        }

        $ambito = (string) $request->query('ambito', 'andalucia');
        if (! in_array($ambito, ['andalucia', 'espana', 'ambos'], true)) {
            $ambito = 'andalucia';
        }

        // Recuentos de nodos activos para las dos tarjetas de ámbito
        $conteoAndalucia = 0;
        $conteoEspana = 0;
        try {
            $rango = Periodos::rango($period, $which);
            $table = ($period === 'hour' || $period === 'day') ? 'agg_node_hour' : 'agg_node_day';
            $provinciasAnd = "'".implode("','", Routers::PROVINCIAS_ANDALUCIA)."'";
            $conteos = DB::connection('ingesta')
                ->table($table)
                ->where('bucket', '>=', $rango['from'])
                ->where('bucket', '<', $rango['to'])
                ->selectRaw("
                    count(distinct case when province in ({$provinciasAnd}) then from_id end) as andalucia,
                    count(distinct case when province not in ({$provinciasAnd}) or province is null or province = '' then from_id end) as espana
                ")
                ->first();

            $conteoAndalucia = (int) ($conteos->andalucia ?? 0);
            $conteoEspana = (int) ($conteos->espana ?? 0);
        } catch (Throwable) {
            $conteoAndalucia = 0;
            $conteoEspana = 0;
        }

        // Obtener distribución de tráfico filtrada por ámbito
        $datosTrafico = null;
        try {
            $resTrafico = $this->trafico->obtenerResultado($period, $which, $ambito);
            $datosTrafico = $resTrafico->datos;
        } catch (Throwable) {
            $datosTrafico = [
                'total_packets' => 0,
                'total_airtime_s' => 0.0,
                'items' => [],
            ];
        }

        // Obtener ranking principal de uso de red filtrado por ámbito
        $rankingUso = null;
        try {
            $resUso = $this->rankings->obtenerRanking('network-usage', $period, $which, 10, $ambito);
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
            'ambito' => $ambito,
            'conteoAndalucia' => $conteoAndalucia,
            'conteoEspana' => $conteoEspana,
            'trafico' => $datosTrafico,
            'rankingUso' => $rankingUso,
            'catalogo' => $catalogo,
        ]);
    }
}
