<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Data\Ingest\Routers;
use Filament\Widgets\Widget;

/**
 * Selector interactivo de ámbito territorial para el cuadro de mando de operadores.
 *
 * Permite conmutar o acumular la supervisión entre:
 * - andalucia: Ámbito regional prioritario (8 provincias andaluzas, activo por defecto).
 * - espana: Red nacional (resto de España).
 * - andalucia + espana: Combinación acumulada que suma ambos ámbitos.
 */
class AmbitoSelectorWidget extends Widget
{
    protected static bool $isLazy = false;

    protected static ?int $sort = -200;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.widgets.ambito-selector-widget';

    public bool $activoAndalucia = true;

    public bool $activoEspana = false;

    /**
     * Inicializa el estado desde la sesión del operador (por defecto Andalucía activo).
     */
    public function mount(): void
    {
        $this->activoAndalucia = (bool) session('dashboard_ambito_andalucia', true);
        $this->activoEspana = (bool) session('dashboard_ambito_espana', false);

        // Si ambos estuviesen desmarcados, forzar al menos Andalucía por defecto
        if (! $this->activoAndalucia && ! $this->activoEspana) {
            $this->activoAndalucia = true;
        }

        session(['dashboard_ambito' => $this->getAmbitoSlug()]);
    }

    /**
     * Alterna la activación de un ámbito.
     * Al entrar Andalucía está activo; si se pulsa España se suman ambos.
     * Se pueden acumular o ver separados.
     */
    public function toggleAmbito(string $key): void
    {
        if ($key === 'andalucia') {
            // No permitir apagar si es el único activo (para no dejar el cuadro vacío)
            if ($this->activoAndalucia && ! $this->activoEspana) {
                return;
            }
            $this->activoAndalucia = ! $this->activoAndalucia;
        } elseif ($key === 'espana') {
            // No permitir apagar si es el único activo
            if ($this->activoEspana && ! $this->activoAndalucia) {
                return;
            }
            $this->activoEspana = ! $this->activoEspana;
        }

        $slug = $this->getAmbitoSlug();

        session([
            'dashboard_ambito_andalucia' => $this->activoAndalucia,
            'dashboard_ambito_espana' => $this->activoEspana,
            'dashboard_ambito' => $slug,
        ]);

        $this->dispatch('ambito-cambiado', ambito: $slug, andalucia: $this->activoAndalucia, espana: $this->activoEspana);
    }

    /**
     * Obtiene el identificador de ámbito consolidado para las consultas SQL.
     */
    public function getAmbitoSlug(): string
    {
        if ($this->activoAndalucia && $this->activoEspana) {
            return 'ambos';
        }

        return $this->activoAndalucia ? 'andalucia' : 'espana';
    }

    /**
     * Prepara los recuentos en tiempo real para las 2 tarjetas de ámbito.
     *
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        /** @var Routers $routersService */
        $routersService = app(Routers::class);

        $conteoAndalucia = (int) ($routersService->obtenerResultado(ambito: 'andalucia')->datos['count'] ?? 0);
        $conteoEspana = (int) ($routersService->obtenerResultado(ambito: 'espana')->datos['count'] ?? 0);

        return [
            'activo_andalucia' => $this->activoAndalucia,
            'activo_espana' => $this->activoEspana,
            'conteo_andalucia' => $conteoAndalucia,
            'conteo_espana' => $conteoEspana,
        ];
    }
}
