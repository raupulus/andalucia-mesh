<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Controllers\Api\V1\AlertsApiController;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class AlertasController extends Controller
{
    /**
     * Listado público de alertas de la red.
     */
    public function index(Request $request): View
    {
        $estado = $request->query('estado');
        $riesgo = $request->query('riesgo');

        $alertas = [];
        $detectorCalibrando = false;

        try {
            $query = DB::connection('alertas')
                ->table('api_alertas')
                ->orderByDesc('inicio_at')
                ->limit(50);

            if ($estado) {
                $query->where('estado', $estado);
            }
            if ($riesgo) {
                $query->where('riesgo', $riesgo);
            }

            $alertas = $query->get()->toArray();
        } catch (Throwable) {
            $detectorCalibrando = true;
            $alertas = [];
        }

        $reglas = AlertsApiController::catalogoReglas();

        return view('alertas.index', [
            'alertas' => $alertas,
            'detectorCalibrando' => $detectorCalibrando,
            'reglas' => $reglas,
            'estadoFiltro' => $estado,
            'riesgoFiltro' => $riesgo,
        ]);
    }

    /**
     * Detalle técnico e histórico de una alerta por identificador.
     */
    public function show(string $id): View
    {
        $alerta = null;
        try {
            $alerta = DB::connection('alertas')
                ->table('api_alertas')
                ->where('id', strtoupper($id))
                ->first();
        } catch (Throwable) {
            $alerta = null;
        }

        if (!$alerta) {
            throw new NotFoundHttpException("La alerta '{$id}' no existe o ha expirado.");
        }

        return view('alertas.show', [
            'alerta' => (array) $alerta,
        ]);
    }
}
