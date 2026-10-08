<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Data\Ingest\Resumen;
use App\Data\Ingest\Routers;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Livewire\Attributes\On;
use Throwable;

/**
 * Tarjetas de métricas ejecutivas de la red y presión en el aire (KPIs principales).
 * Proporciona a los operadores una vista inmediata de la salud radioeléctrica y energética.
 */
class MallaStatsOverviewWidget extends BaseWidget
{
    public string $ambito = 'andalucia';

    protected static bool $isLazy = false;

    protected static ?int $sort = -100;

    protected ?string $pollingInterval = '30s';

    public function mount(): void
    {
        $this->ambito = session('dashboard_ambito', 'andalucia');
    }

    #[On('ambito-cambiado')]
    public function actualizarAmbito(string $ambito): void
    {
        $this->ambito = $ambito;
    }

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

        // 2. Obtener datos de routers de infraestructura filtrados por el ámbito activo
        $routersData = null;
        try {
            /** @var Routers $routersService */
            $routersService = app(Routers::class);
            $routersData = (array) $routersService->obtenerResultado(sort: 'battery_asc', ambito: $this->ambito)->datos;
        } catch (Throwable) {
            $routersData = null;
        }

        // --- TARJETA 1: Presión del Aire (Channel Utilization) ---
        $chUtilAvg = $resumenData['channel_utilization']['andalucia_avg'] ?? null;
        $provincias = $resumenData['channel_utilization']['provinces'] ?? [];
        if ($this->ambito === 'andalucia') {
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

        if ($chUtilAvg !== null) {
            $chUtilVal = round((float) $chUtilAvg, 1).'%';
            if ($chUtilAvg < 20.0) {
                $chColor = 'success';
                $chDesc = __('admin.widgets.stats.chutil_light');
            } elseif ($chUtilAvg < 40.0) {
                $chColor = 'warning';
                $chDesc = __('admin.widgets.stats.chutil_moderate');
            } else {
                $chColor = 'danger';
                $chDesc = __('admin.widgets.stats.chutil_heavy');
            }
            if ($picoProvincia && $picoMax > 0) {
                $chDesc .= __('admin.widgets.stats.chutil_peak', ['peak' => $picoMax, 'province' => $picoProvincia]);
            }
        } else {
            $chUtilVal = __('admin.widgets.stats.no_data');
            $chColor = 'gray';
            $chDesc = __('admin.widgets.stats.chutil_waiting');
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
                $txDesc = __('admin.widgets.stats.tx_optimal');
            } elseif ($avgTx < 8.0) {
                $txColor = 'warning';
                $txDesc = __('admin.widgets.stats.tx_moderate');
            } else {
                $txColor = 'danger';
                $txDesc = __('admin.widgets.stats.tx_high');
            }
        } else {
            $txVal = __('admin.widgets.stats.no_data');
            $txColor = 'gray';
            $txDesc = __('admin.widgets.stats.tx_desc_none');
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

        $routersVal = trans_choice('admin.widgets.stats.routers_count', $totalRouters, ['count' => $totalRouters]);
        if ($criticos > 0) {
            $batColor = 'danger';
            $batDesc = __('admin.widgets.stats.battery_critical', ['count' => $criticos]);
        } elseif ($enBateria > 0) {
            $batMin = ! empty($bateriasValidas) ? min($bateriasValidas) : 100;
            $batColor = $batMin < 40 ? 'warning' : 'success';
            $batDesc = __('admin.widgets.stats.battery_mixed', ['grid' => $enRed, 'battery' => $enBateria, 'min' => $batMin]);
        } elseif ($totalRouters > 0) {
            $batColor = 'success';
            $batDesc = __('admin.widgets.stats.battery_all_grid');
        } else {
            $batColor = 'gray';
            $batDesc = __('admin.widgets.stats.battery_none');
        }

        // --- TARJETA 4: Tráfico y Pasarelas (24h) ---
        $paquetesHora = (int) ($resumenData['packets_last_hour'] ?? 0);
        $gateways = (int) ($resumenData['gateways_publishing'] ?? 0);
        $nodos24h = (int) ($resumenData['nodes_active_24h'] ?? 0);

        $traficoVal = number_format($paquetesHora, 0, ',', '.').' '.__('admin.widgets.stats.packets_per_hour');
        $traficoDesc = __('admin.widgets.stats.traffic_desc', ['gateways' => $gateways, 'nodes' => $nodos24h]);
        $traficoColor = $gateways > 0 ? 'info' : 'gray';

        $ambitoLabel = match ($this->ambito) {
            'ambos', 'global' => ' ('.__('admin.ambitos.andalucia_title').' + '.__('admin.ambitos.espana_title').')',
            'espana' => ' ('.__('admin.ambitos.espana_title').')',
            default => ' ('.__('admin.ambitos.andalucia_title').')',
        };

        return [
            Stat::make(__('admin.widgets.stats.channel_pressure').$ambitoLabel, $chUtilVal)
                ->description($chDesc)
                ->descriptionIcon('heroicon-m-signal')
                ->color($chColor)
                ->chart($chartChUtil),

            Stat::make(__('admin.widgets.stats.tx_saturation').$ambitoLabel, $txVal)
                ->description($txDesc)
                ->descriptionIcon('heroicon-m-arrow-up-circle')
                ->color($txColor)
                ->chart($chartTx),

            Stat::make(__('admin.widgets.stats.infrastructure_energy').$ambitoLabel, $routersVal)
                ->description($batDesc)
                ->descriptionIcon('heroicon-m-bolt')
                ->color($batColor),

            Stat::make(__('admin.widgets.stats.traffic_gateways'), $traficoVal)
                ->description($traficoDesc)
                ->descriptionIcon('heroicon-m-globe-americas')
                ->color($traficoColor),
        ];
    }
}
