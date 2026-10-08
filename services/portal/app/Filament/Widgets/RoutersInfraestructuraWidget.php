<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Data\Ingest\Routers;
use Carbon\Carbon;
use Filament\Widgets\Widget;
use Throwable;

/**
 * Widget de supervisión en tiempo real de los repetidores y routers de infraestructura.
 * Permite vigilar batería, alimentación, canal (chutil) y saturación TX de cada nodo crítico.
 */
class RoutersInfraestructuraWidget extends Widget
{
    protected static bool $isLazy = false;

    protected string $view = 'filament.widgets.routers-infraestructura-widget';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 2;

    /**
     * Prepara los datos de telemetría de los routers de la malla.
     *
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $routers = [];
        $total = 0;
        $enAlerta = 0;
        $errorFuente = null;

        try {
            /** @var Routers $routersService */
            $routersService = app(Routers::class);
            $datos = (array) $routersService->obtenerResultado(sort: 'battery_asc')->datos;
            $items = $datos['items'] ?? [];
            $total = count($items);

            foreach ($items as $r) {
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

                // Color y nivel de presión local ChUtil
                $chutilColor = 'verde';
                if ($chutil !== null) {
                    if ($chutil >= 40.0) {
                        $chutilColor = 'rojo';
                        $enAlerta++;
                    } elseif ($chutil >= 20.0) {
                        $chutilColor = 'amarillo';
                    }
                }

                // Color y nivel de emisión TX
                $txColor = 'verde';
                if ($airTx !== null) {
                    if ($airTx >= 8.0) {
                        $txColor = 'rojo';
                    } elseif ($airTx >= 3.0) {
                        $txColor = 'amarillo';
                    }
                }

                $routers[] = [
                    'id' => (string) ($r['id'] ?? ''),
                    'short_name' => (string) ($r['short_name'] ?? '—'),
                    'long_name' => (string) ($r['long_name'] ?? ''),
                    'role' => (string) ($r['role'] ?? 'ROUTER'),
                    'province' => (string) ($r['province'] ?? '—'),
                    'hw_model' => (string) ($r['hw_model'] ?? 'Desconocido'),
                    'powered' => $powered,
                    'voltage' => $voltage,
                    'battery_level' => $batteryLevel,
                    'bateria_color' => $bateriaColor,
                    'chutil' => $chutil,
                    'chutil_color' => $chutilColor,
                    'air_tx' => $airTx,
                    'tx_color' => $txColor,
                    'esta_online' => $estaOnline,
                    'hace' => $lastSeenDt ? $lastSeenDt->diffForHumans() : __('admin.widgets.routers.no_connection'),
                ];
            }
        } catch (Throwable $e) {
            $errorFuente = __('admin.widgets.routers.error_source');
        }

        return [
            'routers' => $routers,
            'total' => $total,
            'en_alerta' => $enAlerta,
            'error_fuente' => $errorFuente,
        ];
    }
}
