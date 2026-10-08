<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Data\Ingest\Resumen;
use App\Data\Ingest\Routers;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Throwable;

/**
 * Tarjetas de métricas ejecutivas de la red y presión en el aire (KPIs principales).
 * Proporciona a los operadores una vista inmediata de la salud radioeléctrica y energética.
 */
class MallaStatsOverviewWidget extends BaseWidget
{
    protected static bool $isLazy = false;

    protected static ?int $sort = -100;

    protected ?string $pollingInterval = '30s';

    /**
     * Construye las tarjetas de resumen y presión del aire.
     *
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        // 1. Obtener datos de resumen de la red con degradación segura
        $resumenData = null;
        try {
            /** @var Resumen $resumenService */
            $resumenService = app(Resumen::class);
            $resumenData = (array) $resumenService->obtenerResultado()->datos;
        } catch (Throwable) {
            $resumenData = null;
        }

        // 2. Obtener datos de routers de infraestructura
        $routersData = null;
        try {
            /** @var Routers $routersService */
            $routersService = app(Routers::class);
            $routersData = (array) $routersService->obtenerResultado()->datos;
        } catch (Throwable) {
            $routersData = null;
        }

        // --- TARJETA 1: Presión del Aire (Channel Utilization) ---
        $chUtilAvg = $resumenData['channel_utilization']['andalucia_avg'] ?? null;
        $provincias = $resumenData['channel_utilization']['provinces'] ?? [];

        $picoProvincia = null;
        $picoMax = 0.0;
        foreach ($provincias as $p) {
            if (($p['max'] ?? 0) > $picoMax) {
                $picoMax = (float) $p['max'];
                $picoProvincia = (string) ($p['name'] ?? $p['code']);
            }
        }

        if ($chUtilAvg !== null) {
            $chUtilVal = round((float) $chUtilAvg, 1).'%';
            if ($chUtilAvg < 20.0) {
                $chColor = 'success';
                $chDesc = 'Holgado (≤ 20%)';
            } elseif ($chUtilAvg < 40.0) {
                $chColor = 'warning';
                $chDesc = 'Cargado (20–40%)';
            } else {
                $chColor = 'danger';
                $chDesc = 'Saturado (≥ 40%)';
            }
            if ($picoProvincia && $picoMax > 0) {
                $chDesc .= " · Pico {$picoMax}% en {$picoProvincia}";
            }
        } else {
            $chUtilVal = 'Sin datos';
            $chColor = 'gray';
            $chDesc = 'Telemetría de canal en espera';
        }

        $chartChUtil = ! empty($provincias)
            ? array_map(fn ($p) => (float) ($p['avg'] ?? 0), array_slice($provincias, 0, 8))
            : [2.0, 3.5, 4.0, 3.8, 5.0, 4.2, $chUtilAvg ? (float) $chUtilAvg : 4.0];

        // --- TARJETA 2: Saturación TX de Infraestructura ---
        $routersItems = $routersData['items'] ?? [];
        $totalRouters = count($routersItems);
        $txList = array_filter(array_map(fn ($r) => $r['air_util_tx'] ?? null, $routersItems), fn ($v) => $v !== null);
        $avgTx = ! empty($txList) ? round(array_sum($txList) / count($txList), 1) : null;

        if ($avgTx !== null) {
            $txVal = "{$avgTx}% TX";
            if ($avgTx < 3.0) {
                $txColor = 'success';
                $txDesc = 'Emisión óptima · Duty-cycle < 10%';
            } elseif ($avgTx < 8.0) {
                $txColor = 'warning';
                $txDesc = 'Emisión moderada · Tráfico alto';
            } else {
                $txColor = 'danger';
                $txDesc = 'Emisión elevada · Riesgo colisión';
            }
        } else {
            $txVal = 'Sin datos';
            $txColor = 'gray';
            $txDesc = 'Ocupación TX de repetidores';
        }

        $chartTx = ! empty($txList) ? array_values(array_map('floatval', $txList)) : [1.0, 1.5, 1.2, 2.0, 1.4, 1.1];

        // --- TARJETA 3: Infraestructura y Baterías ---
        $enRed = 0;
        $enBateria = 0;
        $criticos = 0;
        $bateriasValidas = [];

        foreach ($routersItems as $r) {
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

        $routersVal = $totalRouters > 0 ? "{$totalRouters} routers" : '0 routers';
        if ($criticos > 0) {
            $batColor = 'danger';
            $batDesc = "{$criticos} en batería crítica (< 20%)";
        } elseif ($enBateria > 0) {
            $batMin = ! empty($bateriasValidas) ? min($bateriasValidas) : 100;
            $batColor = $batMin < 40 ? 'warning' : 'success';
            $batDesc = "{$enRed} en red ⚡ · {$enBateria} batería (mín {$batMin}%)";
        } elseif ($totalRouters > 0) {
            $batColor = 'success';
            $batDesc = 'Todos conectados a red/solar ⚡';
        } else {
            $batColor = 'gray';
            $batDesc = 'Sin telemetría de repetidores';
        }

        // --- TARJETA 4: Tráfico y Pasarelas (24h) ---
        $paquetesHora = (int) ($resumenData['packets_last_hour'] ?? 0);
        $gateways = (int) ($resumenData['gateways_publishing'] ?? 0);
        $nodos24h = (int) ($resumenData['nodes_active_24h'] ?? 0);

        $traficoVal = number_format($paquetesHora, 0, ',', '.').' paq/h';
        $traficoDesc = "{$gateways} gateways activos · {$nodos24h} nodos 24h";
        $traficoColor = $gateways > 0 ? 'info' : 'gray';

        return [
            Stat::make('Presión del Aire (ChUtil)', $chUtilVal)
                ->description($chDesc)
                ->descriptionIcon('heroicon-m-signal')
                ->color($chColor)
                ->chart($chartChUtil),

            Stat::make('Saturación TX Repetidores', $txVal)
                ->description($txDesc)
                ->descriptionIcon('heroicon-m-arrow-up-circle')
                ->color($txColor)
                ->chart($chartTx),

            Stat::make('Infraestructura y Energía', $routersVal)
                ->description($batDesc)
                ->descriptionIcon('heroicon-m-bolt')
                ->color($batColor),

            Stat::make('Tráfico y Pasarelas', $traficoVal)
                ->description($traficoDesc)
                ->descriptionIcon('heroicon-m-globe-americas')
                ->color($traficoColor),
        ];
    }
}
