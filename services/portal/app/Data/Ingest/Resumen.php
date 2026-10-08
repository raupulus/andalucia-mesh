<?php

declare(strict_types=1);

namespace App\Data\Ingest;

use App\Data\Fuente;
use App\Data\Resultado;
use Illuminate\Support\Facades\DB;
use Throwable;

class Resumen
{
    public function __construct(
        protected CargaProvincias $cargaProvincias
    ) {}

    /**
     * Obtiene el resumen general de la red Andalucía Mesh.
     */
    public function obtenerResultado(): Resultado
    {
        return Fuente::recordar('stats_summary', [], 60, function () {
            // 1. Obtener fila de api_summary
            $summary = DB::connection('ingesta')
                ->table('api_summary')
                ->first();

            // 2. Obtener saturación de canales (ventana canónica de 12 horas en resumen)
            $resCarga = $this->cargaProvincias->obtener('12h');
            $datosCarga = (array) $resCarga->datos;

            $provinciasResumen = [];
            foreach ($datosCarga['provinces'] ?? [] as $p) {
                $provinciasResumen[] = [
                    'code' => $p['code'],
                    'name' => $p['name'],
                    'avg' => $p['avg'],
                    'max' => $p['max'],
                    'level' => $p['level'],
                ];
            }

            // 3. Obtener alertas abiertas desde la base de alertas si está disponible
            $alertasOpen = null;
            try {
                $filasAlertas = DB::connection('alertas')
                    ->table('api_alertas_resumen')
                    ->get();

                if ($filasAlertas->isNotEmpty()) {
                    $total = 0;
                    $byRisk = ['bajo' => 0, 'medio' => 0, 'alto' => 0];
                    $byType = ['infraestructura' => 0, 'clientes' => 0];

                    foreach ($filasAlertas as $fa) {
                        $abiertas = (int) $fa->abiertas;
                        $total += $abiertas;

                        $r = strtolower((string) $fa->riesgo);
                        if (isset($byRisk[$r])) {
                            $byRisk[$r] += $abiertas;
                        }

                        $t = strtolower((string) $fa->tipo);
                        if (isset($byType[$t])) {
                            $byType[$t] += $abiertas;
                        }
                    }

                    $alertasOpen = [
                        'total' => $total,
                        'by_risk' => $byRisk,
                        'by_type' => $byType,
                    ];
                }
            } catch (Throwable) {
                $alertasOpen = null;
            }

            return [
                'nodes_active_24h' => $summary ? (int) $summary->nodes_active_24h : 0,
                'nodes_active_7d' => $summary ? (int) $summary->nodes_active_7d : 0,
                'routers_active_24h' => $summary ? (int) $summary->routers_active_24h : 0,
                'gateways_publishing' => $summary ? (int) $summary->gateways_publishing : 0,
                'packets_last_hour' => $summary ? (int) $summary->packets_last_hour : 0,
                'channel_utilization' => [
                    'window' => '12h',
                    'andalucia_avg' => $datosCarga['andalucia_avg'] ?? null,
                    'andalucia_max' => $datosCarga['andalucia_max'] ?? null,
                    'provinces' => $provinciasResumen,
                ],
                'alerts_open' => $alertasOpen,
                'notes' => [
                    'Solo cuenta paquetes y nodos que llegan a nuestros gateways con OK to MQTT activado.',
                ],
            ];
        });
    }
}
