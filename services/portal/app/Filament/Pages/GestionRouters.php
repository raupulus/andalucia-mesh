<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\CoordinatedRouter;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Collection;

/**
 * Página administrativa de control y gestión remota de routers de Andalucía Mesh.
 * Permite al operador conectar su nodo local físico (Web Serial / USB, BLE, HTTP)
 * y emitir comandos administrativos (cambio de roles, favoritos, sondeo, traceroute, reinicio diferido)
 * a través de la malla LoRa.
 */
class GestionRouters extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWrenchScrewdriver;

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.gestion-routers';

    public static function getNavigationGroup(): ?string
    {
        return __('admin.coordinated_routers.nav_group');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.gestion_routers.nav_label');
    }

    public function getTitle(): string|Htmlable
    {
        return __('admin.gestion_routers.title');
    }

    public function getSubheading(): ?string
    {
        return __('admin.gestion_routers.subheading');
    }

    /**
     * Provee a la vista Blade la lista de routers coordinados de Andalucía.
     *
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        /** @var Collection<int, CoordinatedRouter> $routers */
        $routers = CoordinatedRouter::andalucia()
            ->orderBy('province')
            ->orderBy('short_name')
            ->get();

        $formattedRouters = $routers->map(function (CoordinatedRouter $r): array {
            $cleanHex = ltrim($r->node_id, '!');
            $decId = (int) hexdec($cleanHex);

            return [
                'node_id' => $r->node_id,
                'dec_id' => (string) $decId,
                'short_name' => $r->short_name,
                'long_name' => $r->long_name,
                'province' => $r->province,
                'role' => $r->role,
                'status' => $r->status,
            ];
        })->values()->all();

        return [
            'routers' => $formattedRouters,
        ];
    }
}
