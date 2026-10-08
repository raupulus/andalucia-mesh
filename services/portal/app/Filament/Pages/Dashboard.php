<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Filament\Widgets\AmbitoSelectorWidget;
use App\Filament\Widgets\EstadoServiciosWidget;
use App\Filament\Widgets\MallaStatsOverviewWidget;
use App\Filament\Widgets\RoutersInfraestructuraWidget;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Widgets\Widget;
use Illuminate\Contracts\Support\Htmlable;

/**
 * Página principal del Escritorio del Operador en Filament.
 * Garantiza que las tarjetas ejecutivas de la malla se posicionen arriba de todo el panel.
 */
class Dashboard extends BaseDashboard
{
    public static function getNavigationLabel(): string
    {
        return __('admin.dashboard_title');
    }

    public function getTitle(): string|Htmlable
    {
        return __('admin.dashboard_title');
    }

    /**
     * Define los widgets del dashboard en orden prioritario estricto.
     *
     * @return array<class-string<Widget>>
     */
    public function getWidgets(): array
    {
        return [
            AmbitoSelectorWidget::class,
            MallaStatsOverviewWidget::class,
            RoutersInfraestructuraWidget::class,
            EstadoServiciosWidget::class,
        ];
    }
}
