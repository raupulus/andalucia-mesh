<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Data\Ingest\Routers;
use Filament\Widgets\Widget;

/**
 * Selector interactivo de ámbito territorial para el cuadro de mando de operadores.
 *
 * Permite conmutar la supervisión entre:
 * - andalucia: Ámbito regional prioritario (8 provincias andaluzas, activo por defecto).
 * - espana: Ámbito nacional español (ES-*).
 * - global: Toda la malla sin restricciones territoriales (incluyendo nodos exteriores).
 */
class AmbitoSelectorWidget extends Widget
{
    protected static bool $isLazy = false;

    protected static ?int $sort = -200;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.widgets.ambito-selector-widget';

    public string $ambito = 'andalucia';

    /**
     * Inicializa el ámbito desde la sesión del operador o por defecto en 'andalucia'.
     */
    public function mount(): void
    {
        $this->ambito = session('dashboard_ambito', 'andalucia');
    }

    /**
     * Cambia el ámbito territorial seleccionado, lo persiste en sesión y notifica a los demás widgets.
     */
    public function setAmbito(string $nuevoAmbito): void
    {
        if (! in_array($nuevoAmbito, ['andalucia', 'espana', 'global'], true)) {
            return;
        }

        $this->ambito = $nuevoAmbito;
        session(['dashboard_ambito' => $nuevoAmbito]);

        $this->dispatch('ambito-cambiado', ambito: $nuevoAmbito);
    }

    /**
     * Prepara los recuentos en tiempo real para las 3 tarjetas de ámbito.
     *
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        /** @var Routers $routersService */
        $routersService = app(Routers::class);

        $conteoAndalucia = (int) ($routersService->obtenerResultado(ambito: 'andalucia')->datos['count'] ?? 0);
        $conteoEspana = (int) ($routersService->obtenerResultado(ambito: 'espana')->datos['count'] ?? 0);
        $conteoGlobal = (int) ($routersService->obtenerResultado(ambito: 'global')->datos['count'] ?? 0);

        return [
            'ambito' => $this->ambito,
            'conteo_andalucia' => $conteoAndalucia,
            'conteo_espana' => $conteoEspana,
            'conteo_global' => $conteoGlobal,
        ];
    }
}
