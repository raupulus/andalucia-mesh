<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Http\Controllers\RoutersController;
use Filament\Widgets\Widget;
use Livewire\Attributes\On;

/**
 * Widget de supervisión en tiempo real de los repetidores y routers de infraestructura.
 * Permite vigilar batería, alimentación, canal (chutil) y saturación TX de cada nodo crítico.
 */
class RoutersInfraestructuraWidget extends Widget
{
    public string $ambito = 'andalucia';

    protected static bool $isLazy = false;

    protected string $view = 'filament.widgets.routers-infraestructura-widget';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 2;

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
     * Prepara los datos de telemetría y agrupación provincial de los routers de la malla.
     *
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $datos = RoutersController::prepararDatosSupervision($this->ambito, 'battery_asc');

        return [
            'routers' => $datos['routers'],
            'routersPorProvincia' => $datos['routersPorProvincia'],
            'total' => $datos['total'],
            'en_alerta' => $datos['enAlerta'],
            'enAlerta' => $datos['enAlerta'],
            'error_fuente' => $datos['errorFuente'],
            'errorFuente' => $datos['errorFuente'],
            'ambito' => $this->ambito,
        ];
    }
}
