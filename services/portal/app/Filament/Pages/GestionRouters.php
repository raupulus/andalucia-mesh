<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\CoordinatedRouter;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Página administrativa de control y gestión remota de routers de Andalucía Mesh.
 * Permite al operador conectar su nodo local físico (Web Serial / USB, BLE, HTTP)
 * y emitir comandos administrativos (cambio de roles, favoritos, bloqueados, sondeo, traceroute, reinicio diferido)
 * a través de la malla LoRa.
 */
class GestionRouters extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWrenchScrewdriver;

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.gestion-routers';

    public function getMaxContentWidth(): Width|string|null
    {
        return Width::Full;
    }

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
     * Provee a la vista Blade la lista de routers coordinados de Andalucía y catálogo completo de nodos para búsqueda.
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
                'favorite_nodes' => $r->favorite_nodes ?? [],
                'blocked_nodes' => $r->blocked_nodes ?? [],
            ];
        })->values()->all();

        // Construir catálogo de búsqueda a partir de routers coordinados y base de datos de ingesta
        $allKnownNodes = $formattedRouters;
        try {
            $schema = Schema::connection('ingesta');
            if ($schema->hasTable('api_routers') || $schema->hasView('api_routers')) {
                $ingestaRouters = DB::connection('ingesta')->table('api_routers')->get();
                $existingIds = array_column($allKnownNodes, 'node_id');
                foreach ($ingestaRouters as $ir) {
                    if (! in_array($ir->id, $existingIds, true)) {
                        $cleanHex = ltrim($ir->id, '!');
                        $allKnownNodes[] = [
                            'node_id' => $ir->id,
                            'dec_id' => (string) hexdec($cleanHex),
                            'short_name' => $ir->short_name ?? '',
                            'long_name' => $ir->long_name ?? '',
                            'province' => $ir->province ?? '',
                            'role' => $ir->role ?? 'ROUTER',
                            'status' => 'known',
                            'favorite_nodes' => [],
                            'blocked_nodes' => [],
                        ];
                    }
                }
            }
        } catch (\Throwable) {
            // Continuar con los routers coordinados si ingesta no está disponible
        }

        return [
            'routers' => $formattedRouters,
            'allKnownNodes' => $allKnownNodes,
        ];
    }

    /**
     * Persiste la adición o eliminación de un nodo favorito para el router indicado.
     *
     * @param  array<string, mixed>|null  $nodeData
     */
    public function updateRouterFavoriteNode(string $routerId, string $targetHex, bool $isAdd, ?array $nodeData = null): void
    {
        $router = CoordinatedRouter::where('node_id', $routerId)->first();
        if (! $router) {
            $cleanHex = ltrim($routerId, '!');
            $router = CoordinatedRouter::create([
                'node_id' => $routerId,
                'short_name' => substr($cleanHex, -4),
                'long_name' => 'Router '.$cleanHex,
                'province' => 'ES-CA',
                'role' => 'ROUTER',
                'status' => 'known',
            ]);
        }

        $favs = $router->favorite_nodes ?? [];
        if ($isAdd) {
            $exists = false;
            foreach ($favs as $f) {
                if (($f['hex'] ?? $f) === $targetHex) {
                    $exists = true;
                    break;
                }
            }
            if (! $exists) {
                $favs[] = [
                    'hex' => $targetHex,
                    'short_name' => $nodeData['short_name'] ?? '',
                    'long_name' => $nodeData['long_name'] ?? '',
                    'role' => $nodeData['role'] ?? '',
                    'added_at' => now()->toIso8601String(),
                ];
            }
        } else {
            $favs = array_values(array_filter($favs, fn ($f): bool => ($f['hex'] ?? $f) !== $targetHex));
        }

        $router->favorite_nodes = $favs;
        $router->save();
    }

    /**
     * Persiste la adición o eliminación de un nodo bloqueado para el router indicado.
     *
     * @param  array<string, mixed>|null  $nodeData
     */
    public function updateRouterBlockedNode(string $routerId, string $targetHex, bool $isAdd, ?array $nodeData = null): void
    {
        $router = CoordinatedRouter::where('node_id', $routerId)->first();
        if (! $router) {
            $cleanHex = ltrim($routerId, '!');
            $router = CoordinatedRouter::create([
                'node_id' => $routerId,
                'short_name' => substr($cleanHex, -4),
                'long_name' => 'Router '.$cleanHex,
                'province' => 'ES-CA',
                'role' => 'ROUTER',
                'status' => 'known',
            ]);
        }

        $blocked = $router->blocked_nodes ?? [];
        if ($isAdd) {
            $exists = false;
            foreach ($blocked as $b) {
                if (($b['hex'] ?? $b) === $targetHex) {
                    $exists = true;
                    break;
                }
            }
            if (! $exists) {
                $blocked[] = [
                    'hex' => $targetHex,
                    'short_name' => $nodeData['short_name'] ?? '',
                    'long_name' => $nodeData['long_name'] ?? '',
                    'role' => $nodeData['role'] ?? '',
                    'added_at' => now()->toIso8601String(),
                ];
            }
        } else {
            $blocked = array_values(array_filter($blocked, fn ($b): bool => ($b['hex'] ?? $b) !== $targetHex));
        }

        $router->blocked_nodes = $blocked;
        $router->save();
    }
}
