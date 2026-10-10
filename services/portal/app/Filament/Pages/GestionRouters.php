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

        $normalizeList = function (?array $list): array {
            if (! is_array($list)) {
                return [];
            }

            return array_values(array_map(function ($item): array {
                $hex = is_array($item) ? ($item['hex'] ?? '') : (string) $item;
                $sName = is_array($item) ? ($item['short_name'] ?? $item['shortName'] ?? '') : '';
                $lName = is_array($item) ? ($item['long_name'] ?? $item['longName'] ?? '') : '';
                $role = is_array($item) ? ($item['role'] ?? 'ROUTER') : 'ROUTER';

                return [
                    'hex' => $hex,
                    'short_name' => $sName,
                    'shortName' => $sName,
                    'long_name' => $lName,
                    'longName' => $lName,
                    'role' => $role,
                    'added_at' => is_array($item) ? ($item['added_at'] ?? null) : null,
                ];
            }, $list));
        };

        $formattedRouters = $routers->map(function (CoordinatedRouter $r) use ($normalizeList): array {
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
                'favorite_nodes' => $normalizeList($r->favorite_nodes),
                'blocked_nodes' => $normalizeList($r->blocked_nodes),
            ];
        })->values()->all();

        // Construir catálogo de búsqueda a partir de routers coordinados y base de datos de ingesta
        $allKnownNodes = $formattedRouters;
        try {
            $schema = Schema::connection('ingesta');
            $tableName = null;
            if ($schema->hasView('api_nodes') || $schema->hasTable('api_nodes')) {
                $tableName = 'api_nodes';
            } elseif ($schema->hasView('api_routers') || $schema->hasTable('api_routers')) {
                $tableName = 'api_routers';
            }

            if ($tableName) {
                $ingestaNodes = DB::connection('ingesta')->table($tableName)->select(['id', 'short_name', 'long_name', 'role', 'province'])->get();
                $existingIds = array_column($allKnownNodes, 'node_id');
                foreach ($ingestaNodes as $in) {
                    if (! in_array($in->id, $existingIds, true)) {
                        $cleanHex = ltrim($in->id, '!');
                        $allKnownNodes[] = [
                            'node_id' => $in->id,
                            'dec_id' => (string) hexdec($cleanHex),
                            'short_name' => $in->short_name ?? '',
                            'long_name' => $in->long_name ?? '',
                            'province' => $in->province ?? '',
                            'role' => $in->role ?? 'CLIENT',
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
     * Resuelve el nombre y rol conocido de un nodo a partir de la base de datos si vienen vacíos.
     *
     * @return array{short_name: string, long_name: string, role: string}
     */
    protected function resolveNodeIdentity(string $targetHex, ?array $nodeData): array
    {
        $shortName = $nodeData['short_name'] ?? $nodeData['shortName'] ?? '';
        $longName = $nodeData['long_name'] ?? $nodeData['longName'] ?? '';
        $role = $nodeData['role'] ?? '';

        if (empty($shortName) || empty($longName)) {
            $known = CoordinatedRouter::where('node_id', $targetHex)->first();
            if ($known) {
                $shortName = $shortName ?: ($known->short_name ?? '');
                $longName = $longName ?: ($known->long_name ?? '');
                $role = $role ?: ($known->role ?? 'ROUTER');
            } else {
                try {
                    $apiNode = DB::connection('ingesta')->table('api_nodes')->where('id', $targetHex)->first();
                    if ($apiNode) {
                        $shortName = $shortName ?: ($apiNode->short_name ?? '');
                        $longName = $longName ?: ($apiNode->long_name ?? '');
                        $role = $role ?: ($apiNode->role ?? 'ROUTER');
                    }
                } catch (\Throwable) {
                    // Ignorar si ingesta no está accesible
                }
            }
        }

        return [
            'short_name' => $shortName ?: substr(ltrim($targetHex, '!'), -4),
            'long_name' => $longName ?: $targetHex,
            'role' => $role ?: 'ROUTER',
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
                $identity = $this->resolveNodeIdentity($targetHex, $nodeData);

                $favs[] = [
                    'hex' => $targetHex,
                    'short_name' => $identity['short_name'],
                    'shortName' => $identity['short_name'],
                    'long_name' => $identity['long_name'],
                    'longName' => $identity['long_name'],
                    'role' => $identity['role'],
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
                $identity = $this->resolveNodeIdentity($targetHex, $nodeData);

                $blocked[] = [
                    'hex' => $targetHex,
                    'short_name' => $identity['short_name'],
                    'shortName' => $identity['short_name'],
                    'long_name' => $identity['long_name'],
                    'longName' => $identity['long_name'],
                    'role' => $identity['role'],
                    'added_at' => now()->toIso8601String(),
                ];
            }
        } else {
            $blocked = array_values(array_filter($blocked, fn ($b): bool => ($b['hex'] ?? $b) !== $targetHex));
        }

        $router->blocked_nodes = $blocked;
        $router->save();
    }

    /**
     * Limpia la lista completa de favoritos para el router indicado.
     */
    public function clearRouterFavorites(string $routerId): void
    {
        $router = CoordinatedRouter::where('node_id', $routerId)->first();
        if ($router) {
            $router->favorite_nodes = null;
            $router->save();
        }
    }

    /**
     * Limpia la lista completa de bloqueados para el router indicado.
     */
    public function clearRouterBlocked(string $routerId): void
    {
        $router = CoordinatedRouter::where('node_id', $routerId)->first();
        if ($router) {
            $router->blocked_nodes = null;
            $router->save();
        }
    }
}
