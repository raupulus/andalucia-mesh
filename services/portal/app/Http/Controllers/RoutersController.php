<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Data\Ingest\Resumen;
use App\Data\Ingest\Routers;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Throwable;

/**
 * Controlador de la vista pública de repetidores y routers de infraestructura.
 *
 * Muestra el cuadro de supervisión en tiempo real con selector de ámbito
 * territorial (Andalucía, España, Ambos), tarjetas de métricas ejecutivas
 * y tabla detallada de telemetría y salud de cada nodo.
 */
class RoutersController extends Controller
{
    public function __construct(
        protected Routers $routers,
        protected Resumen $resumen,
    ) {}

    public function index(Request $request): View
    {
        // 1. Resolver ámbito territorial (por defecto Andalucía)
        $ambito = (string) $request->query('ambito', 'andalucia');
        if (! in_array($ambito, ['andalucia', 'espana', 'ambos'], true)) {
            $ambito = 'andalucia';
        }

        // 2. Resolver ordenación (por defecto menor batería primero)
        $sort = (string) $request->query('sort', 'battery_asc');
        if (! in_array($sort, ['battery_asc', 'last_seen_desc', 'chutil_desc'], true)) {
            $sort = 'battery_asc';
        }

        // 3. Recuentos para el selector de ámbito
        $conteoAndalucia = 0;
        $conteoEspana = 0;
        try {
            $conteoAndalucia = (int) ($this->routers->obtenerResultado(ambito: 'andalucia')->datos['count'] ?? 0);
            $conteoEspana = (int) ($this->routers->obtenerResultado(ambito: 'espana')->datos['count'] ?? 0);
        } catch (Throwable) {
            $conteoAndalucia = 0;
            $conteoEspana = 0;
        }

        // 4. Obtener listado de routers según el ámbito activo
        $items = [];
        $totalRouters = 0;
        $enAlerta = 0;
        $errorFuente = null;

        try {
            $resRouters = $this->routers->obtenerResultado(sort: $sort, ambito: $ambito);
            $rawItems = $resRouters->datos['items'] ?? [];
            $totalRouters = count($rawItems);

            foreach ($rawItems as $r) {
                $lastSeenDt = ! empty($r['last_seen']) ? Carbon::parse($r['last_seen']) : null;
                $minutosDesconectado = $lastSeenDt ? now()->diffInMinutes($lastSeenDt) : 9999;
                $estaOnline = $minutosDesconectado <= 60;

                $batteryLevel = $r['battery_level'] !== null ? (int) $r['battery_level'] : null;
                $powered = (bool) ($r['powered'] ?? false);
                $voltage = $r['voltage'] !== null ? (float) $r['voltage'] : null;
                $chutil = $r['channel_utilization'] !== null ? (float) $r['channel_utilization'] : null;
                $airTx = $r['air_util_tx'] !== null ? (float) $r['air_util_tx'] : null;

                // Color y estado de batería
                $bateriaColor = 'verde';
                if ($powered) {
                    $bateriaColor = 'red';
                } elseif ($batteryLevel !== null) {
                    if ($batteryLevel < 20) {
                        $bateriaColor = 'rojo';
                        $enAlerta++;
                    } elseif ($batteryLevel < 50) {
                        $bateriaColor = 'amarillo';
                    } else {
                        $bateriaColor = 'verde';
                    }
                } else {
                    $bateriaColor = 'desconocido';
                }

                // Color de presión local ChUtil
                $chutilColor = 'verde';
                if ($chutil !== null) {
                    if ($chutil >= 40.0) {
                        $chutilColor = 'rojo';
                        $enAlerta++;
                    } elseif ($chutil >= 20.0) {
                        $chutilColor = 'amarillo';
                    }
                }

                // Color de emisión TX
                $txColor = 'verde';
                if ($airTx !== null) {
                    if ($airTx >= 8.0) {
                        $txColor = 'rojo';
                    } elseif ($airTx >= 3.0) {
                        $txColor = 'amarillo';
                    }
                }

                $items[] = array_merge($r, [
                    'esta_online' => $estaOnline,
                    'hace' => $lastSeenDt ? $lastSeenDt->diffForHumans() : __('admin.widgets.routers.offline'),
                    'bateria_color' => $bateriaColor,
                    'chutil_color' => $chutilColor,
                    'tx_color' => $txColor,
                    'battery_level' => $batteryLevel,
                    'voltage' => $voltage,
                    'chutil' => $chutil,
                    'air_tx' => $airTx,
                    'powered' => $powered,
                ]);
            }
        } catch (Throwable $e) {
            $errorFuente = __('admin.widgets.routers.error_source');
            $items = [];
            $totalRouters = 0;
        }

        // 5. Métricas de resumen para las 4 tarjetas KPI
        $resumenData = null;
        try {
            $resumenData = (array) $this->resumen->obtenerResultado()->datos;
        } catch (Throwable) {
            $resumenData = null;
        }

        // KPI 1: Presión del Aire (ChUtil)
        $chUtilAvg = $resumenData['channel_utilization']['andalucia_avg'] ?? null;
        $provincias = $resumenData['channel_utilization']['provinces'] ?? [];
        if ($ambito === 'andalucia') {
            $provincias = array_values(array_filter($provincias, fn ($p) => in_array($p['code'] ?? '', Routers::PROVINCIAS_ANDALUCIA, true)));
        }

        $picoProvincia = null;
        $picoMax = 0.0;
        foreach ($provincias as $p) {
            if (($p['max'] ?? 0) > $picoMax) {
                $picoMax = (float) $p['max'];
                $picoProvincia = (string) ($p['name'] ?? $p['code']);
            }
        }

        $kpiChutil = [
            'val' => $chUtilAvg !== null ? round((float) $chUtilAvg, 1).'%' : __('admin.widgets.stats.no_data'),
            'desc' => __('admin.widgets.stats.chutil_waiting'),
            'color' => 'gray',
            'chart' => ! empty($provincias)
                ? array_map(fn ($p) => (float) ($p['avg'] ?? 0), array_slice($provincias, 0, 8))
                : [2.0, 3.5, 4.0, 3.8, 5.0, 4.2, $chUtilAvg ? (float) $chUtilAvg : 4.0],
        ];

        if ($chUtilAvg !== null) {
            if ($chUtilAvg < 20.0) {
                $kpiChutil['color'] = 'success';
                $kpiChutil['desc'] = __('admin.widgets.stats.chutil_light');
            } elseif ($chUtilAvg < 40.0) {
                $kpiChutil['color'] = 'warning';
                $kpiChutil['desc'] = __('admin.widgets.stats.chutil_moderate');
            } else {
                $kpiChutil['color'] = 'danger';
                $kpiChutil['desc'] = __('admin.widgets.stats.chutil_heavy');
            }
            if ($picoProvincia && $picoMax > 0) {
                $kpiChutil['desc'] .= __('admin.widgets.stats.chutil_peak', ['peak' => $picoMax, 'province' => $picoProvincia]);
            }
        }

        // KPI 2: Saturación TX de Repetidores
        $txList = array_filter(array_map(fn ($r) => $r['air_tx'] ?? null, $items), fn ($v) => $v !== null);
        $avgTx = ! empty($txList) ? round(array_sum($txList) / count($txList), 1) : null;

        $kpiTx = [
            'val' => $avgTx !== null ? "{$avgTx}% TX" : __('admin.widgets.stats.no_data'),
            'desc' => __('admin.widgets.stats.tx_desc_none'),
            'color' => 'gray',
            'chart' => ! empty($txList) ? array_values(array_map('floatval', $txList)) : [1.0, 1.5, 1.2, 2.0, 1.4, 1.1],
        ];

        if ($avgTx !== null) {
            if ($avgTx < 3.0) {
                $kpiTx['color'] = 'success';
                $kpiTx['desc'] = __('admin.widgets.stats.tx_optimal');
            } elseif ($avgTx < 8.0) {
                $kpiTx['color'] = 'warning';
                $kpiTx['desc'] = __('admin.widgets.stats.tx_moderate');
            } else {
                $kpiTx['color'] = 'danger';
                $kpiTx['desc'] = __('admin.widgets.stats.tx_high');
            }
        }

        // KPI 3: Infraestructura y Baterías
        $enRed = 0;
        $enBateria = 0;
        $criticos = 0;
        $bateriasValidas = [];

        foreach ($items as $r) {
            if ($r['powered'] ?? false) {
                $enRed++;
            } else {
                $enBateria++;
                $bat = $r['battery_level'] ?? null;
                if ($bat !== null) {
                    $bateriasValidas[] = $bat;
                    if ($bat < 20) {
                        $criticos++;
                    }
                }
            }
        }

        $kpiEnergia = [
            'val' => trans_choice('admin.widgets.stats.routers_count', $totalRouters, ['count' => $totalRouters]),
            'desc' => __('admin.widgets.stats.battery_none'),
            'color' => 'gray',
            'criticos' => $criticos,
        ];

        if ($criticos > 0) {
            $kpiEnergia['color'] = 'danger';
            $kpiEnergia['desc'] = __('admin.widgets.stats.battery_critical', ['count' => $criticos]);
        } elseif ($enBateria > 0) {
            $batMin = ! empty($bateriasValidas) ? min($bateriasValidas) : 100;
            $kpiEnergia['color'] = $batMin < 40 ? 'warning' : 'success';
            $kpiEnergia['desc'] = __('admin.widgets.stats.battery_mixed', ['grid' => $enRed, 'battery' => $enBateria, 'min' => $batMin]);
        } elseif ($totalRouters > 0) {
            $kpiEnergia['color'] = 'success';
            $kpiEnergia['desc'] = __('admin.widgets.stats.battery_all_grid');
        }

        // KPI 4: Tráfico y Pasarelas (24h)
        $paquetesHora = (int) ($resumenData['packets_last_hour'] ?? 0);
        $gateways = (int) ($resumenData['gateways_publishing'] ?? 0);
        $nodos24h = (int) ($resumenData['nodes_active_24h'] ?? 0);

        $kpiTrafico = [
            'val' => number_format($paquetesHora, 0, ',', '.').' '.__('admin.widgets.stats.packets_per_hour'),
            'desc' => __('admin.widgets.stats.traffic_desc', ['gateways' => $gateways, 'nodes' => $nodos24h]),
            'color' => $gateways > 0 ? 'info' : 'gray',
        ];

        return view('routers', [
            'ambito' => $ambito,
            'sort' => $sort,
            'conteoAndalucia' => $conteoAndalucia,
            'conteoEspana' => $conteoEspana,
            'routers' => $items,
            'total' => $totalRouters,
            'enAlerta' => $enAlerta,
            'errorFuente' => $errorFuente,
            'kpiChutil' => $kpiChutil,
            'kpiTx' => $kpiTx,
            'kpiEnergia' => $kpiEnergia,
            'kpiTrafico' => $kpiTrafico,
        ]);
    }
}
