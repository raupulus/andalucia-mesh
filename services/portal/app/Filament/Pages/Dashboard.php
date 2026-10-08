<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Filament\Widgets\EstadoServiciosWidget;
use App\Filament\Widgets\MallaStatsOverviewWidget;
use App\Filament\Widgets\RoutersInfraestructuraWidget;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Widgets\Widget;

/**
 * Página principal del Escritorio del Operador en Filament.
 * Garantiza que las tarjetas ejecutivas de la malla se posicionen arriba de todo el panel.
 */
class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'Centro de Control Operativo';

    /**
     * Define los widgets del dashboard en orden prioritario estricto.
     *
     * @return array<class-string<Widget>>
     */
    public function getWidgets(): array
    {
        return [
            MallaStatsOverviewWidget::class,
            RoutersInfraestructuraWidget::class,
            EstadoServiciosWidget::class,
        ];
    }
}
