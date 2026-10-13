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

        // Hidratar coordenadas desde ingesta (api_routers o api_nodes) si faltan en coordinated_routers
        try {
            $schema = Schema::connection('ingesta');
            $sourceTable = null;
            if ($schema->hasView('api_routers') || $schema->hasTable('api_routers')) {
                $sourceTable = 'api_routers';
            } elseif ($schema->hasView('api_nodes') || $schema->hasTable('api_nodes')) {
                $sourceTable = 'api_nodes';
            }

            if ($sourceTable) {
                $missingCoords = $routers->filter(fn (CoordinatedRouter $r): bool => $r->latitude === null || $r->longitude === null);
                if ($missingCoords->isNotEmpty()) {
                    $ingestaCoords = DB::connection('ingesta')->table($sourceTable)
                        ->whereIn('id', $missingCoords->pluck('node_id')->all())
                        ->select(['id', 'latitude', 'longitude'])
                        ->get()
                        ->keyBy('id');

                    foreach ($missingCoords as $r) {
                        if (isset($ingestaCoords[$r->node_id])) {
                            $row = $ingestaCoords[$r->node_id];
                            if ($row->latitude !== null && $row->longitude !== null) {
                                $r->latitude = (float) $row->latitude;
                                $r->longitude = (float) $row->longitude;
                                try {
                                    $r->save();
                                } catch (\Throwable) {
                                }
                            }
                        }
                    }
                }
            }
        } catch (\Throwable) {
        }

        // Filtrar estrictamente: solo routers de Andalucía con GPS dentro del territorio andaluz y sin ruido genérico
        $routers = $routers->filter(function (CoordinatedRouter $r): bool {
            if (! $r->hasValidGpsInAndalucia()) {
                return false;
            }

            if ($r->isNoiseName()) {
                return false;
            }

            return true;
        })->values();

        $normalizeList = function (?array $list): array {
            if (! is_array($list)) {
                return [];
            }

            return array_values(array_map(function ($item): array {
                $hex = is_array($item) ? ($item['hex'] ?? '') : (string) $item;
                $sName = is_array($item) ? ($item['short_name'] ?? $item['shortName'] ?? '') : '';
                $lName = is_array($item) ? ($item['long_name'] ?? $item['longName'] ?? '') : '';
                $role = is_array($item) ? ($item['role'] ?? '') : '';

                $clean = ltrim($hex, '!');
                $fallbackShort = substr($clean, -4);

                $isGenericShort = empty($sName) || $sName === $hex || strcasecmp($sName, $fallbackShort) === 0;
                $isGenericLong = empty($lName) || $lName === $hex || str_starts_with($lName, 'Router ') || str_starts_with($lName, 'Meshtastic ');
                $isGenericRole = empty($role) || $role === 'NODE' || $role === 'MANUAL';

                // Si falta nombre o tiene valores genéricos/provisionales, resolver identidad en base de datos
                if ($isGenericShort || $isGenericLong || $isGenericRole) {
                    $identity = $this->resolveNodeIdentity($hex, is_array($item) ? $item : null);
                    $sName = $identity['short_name'];
                    $lName = $identity['long_name'];
                    $role = $identity['role'];
                }

                $num = ctype_xdigit($clean) ? (int) hexdec($clean) : 0;

                return [
                    'hex' => $hex,
                    'num' => $num,
                    'short_name' => $sName,
                    'shortName' => $sName,
                    'long_name' => $lName,
                    'longName' => $lName,
                    'role' => $role ?: 'ROUTER',
                    'added_at' => is_array($item) ? ($item['added_at'] ?? null) : null,
                ];
            }, $list));
        };

        $formattedRouters = $routers->map(function (CoordinatedRouter $r) use ($normalizeList): array {
            $cleanHex = ltrim($r->node_id, '!');
            $decId = ctype_xdigit($cleanHex) ? (int) hexdec($cleanHex) : 0;

            $normalizedFavs = $normalizeList($r->favorite_nodes);
            $normalizedBlocked = $normalizeList($r->blocked_nodes);

            // Auto-heal: persistir en BD si las listas almacenadas contenían nombres incompletos
            $needsSave = false;
            if (is_array($r->favorite_nodes) && json_encode($r->favorite_nodes) !== json_encode($normalizedFavs)) {
                $r->favorite_nodes = $normalizedFavs;
                $needsSave = true;
            }
            if (is_array($r->blocked_nodes) && json_encode($r->blocked_nodes) !== json_encode($normalizedBlocked)) {
                $r->blocked_nodes = $normalizedBlocked;
                $needsSave = true;
            }
            if ($needsSave) {
                try {
                    $r->save();
                } catch (\Throwable) {
                    // Silencioso en lectura si ocurre restricción concurrente
                }
            }

            return [
                'node_id' => $r->node_id,
                'dec_id' => (string) $decId,
                'short_name' => $r->short_name,
                'long_name' => $r->long_name,
                'province' => $r->province,
                'latitude' => $r->latitude,
                'longitude' => $r->longitude,
                'altitude' => $r->altitude,
                'role' => $r->role,
                'status' => $r->status,
                'favorite_nodes' => $normalizedFavs,
                'blocked_nodes' => $normalizedBlocked,
                'node_config' => $r->node_config ?? [],
            ];
        })->values()->all();

        // Construir catálogo de búsqueda a partir de routers coordinados y base de datos de ingesta
        $allKnownNodes = $formattedRouters;
        try {
            $schema = Schema::connection('ingesta');
            $tablesToQuery = [];
            if ($schema->hasView('api_routers') || $schema->hasTable('api_routers')) {
                $tablesToQuery[] = 'api_routers';
            }
            if ($schema->hasView('api_nodes') || $schema->hasTable('api_nodes')) {
                $tablesToQuery[] = 'api_nodes';
            }

            $validProvinces = ['ES-AL', 'ES-CA', 'ES-CO', 'ES-GR', 'ES-H', 'ES-J', 'ES-MA', 'ES-SE', 'AL', 'CA', 'CO', 'GR', 'H', 'J', 'MA', 'SE'];
            foreach ($tablesToQuery as $tName) {
                $existingIds = array_map('strtolower', array_column($allKnownNodes, 'node_id'));
                $cols = array_intersect(['id', 'short_name', 'long_name', 'role', 'province', 'latitude', 'longitude'], $schema->getColumnListing($tName));
                $ingestaNodes = DB::connection('ingesta')->table($tName)
                    ->select($cols)
                    ->limit(300)
                    ->get();
                foreach ($ingestaNodes as $in) {
                    if (in_array(strtolower($in->id), $existingIds, true)) {
                        continue;
                    }

                    // Descartar registros que no pertenezcan a Andalucía
                    $prov = strtoupper(trim($in->province ?? ''));
                    if (! empty($prov) && ! in_array($prov, $validProvinces, true)) {
                        continue;
                    }

                    // Si cuenta con coordenadas, asegurar que caigan dentro de Andalucía
                    if (isset($in->latitude, $in->longitude) && $in->latitude !== null && $in->longitude !== null) {
                        if (! CoordinatedRouter::isWithinAndaluciaBounds((float) $in->latitude, (float) $in->longitude)) {
                            continue;
                        }
                    }

                    // Descartar nombres genéricos autogenerados de ruido
                    $lName = trim($in->long_name ?? '');
                    if (! empty($lName) && preg_match('/^(Router|Meshtastic)\s+[0-9a-fA-F]+$/i', $lName)) {
                        continue;
                    }

                    $cleanHex = ltrim($in->id, '!');
                    $decId = ctype_xdigit($cleanHex) ? (string) hexdec($cleanHex) : '0';
                    $allKnownNodes[] = [
                        'node_id' => $in->id,
                        'dec_id' => $decId,
                        'short_name' => $in->short_name ?? '',
                        'long_name' => $in->long_name ?? '',
                        'province' => $in->province ?? '',
                        'role' => $in->role ?? 'CLIENT',
                        'status' => 'known',
                        'favorite_nodes' => [],
                        'blocked_nodes' => [],
                    ];
                    $existingIds[] = strtolower($in->id);
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
     * Resuelve el nombre y rol conocido de un nodo a partir de la base de datos si vienen vacíos o genéricos.
     *
     * @param  array<string, mixed>|null  $nodeData
     * @return array{short_name: string, long_name: string, role: string}
     */
    protected function resolveNodeIdentity(string $targetHex, ?array $nodeData): array
    {
        $shortName = is_array($nodeData) ? ($nodeData['short_name'] ?? $nodeData['shortName'] ?? '') : '';
        $longName = is_array($nodeData) ? ($nodeData['long_name'] ?? $nodeData['longName'] ?? '') : '';
        $role = is_array($nodeData) ? ($nodeData['role'] ?? '') : '';

        $cleanHex = ltrim($targetHex, '!');
        $fallbackShort = substr($cleanHex, -4);

        $needsShort = empty($shortName) || $shortName === $targetHex || strcasecmp($shortName, $fallbackShort) === 0;
        $needsLong = empty($longName) || $longName === $targetHex || str_starts_with($longName, 'Router ') || str_starts_with($longName, 'Meshtastic ');
        $needsRole = empty($role) || $role === 'NODE' || $role === 'MANUAL';

        if ($needsShort || $needsLong || $needsRole) {
            $known = CoordinatedRouter::where('node_id', $targetHex)
                ->orWhereRaw('LOWER(node_id) = ?', [strtolower($targetHex)])
                ->first();
            if ($known) {
                if ($needsShort && ! empty($known->short_name)) {
                    $shortName = $known->short_name;
                    $needsShort = false;
                }
                if ($needsLong && ! empty($known->long_name)) {
                    $longName = $known->long_name;
                    $needsLong = false;
                }
                if ($needsRole && ! empty($known->role) && $known->role !== 'NODE') {
                    $role = $known->role;
                    $needsRole = false;
                }
            }

            if ($needsShort || $needsLong || $needsRole) {
                try {
                    $apiNode = DB::connection('ingesta')->table('api_nodes')
                        ->where('id', $targetHex)
                        ->orWhereRaw('LOWER(id) = ?', [strtolower($targetHex)])
                        ->first();
                    if ($apiNode) {
                        if ($needsShort && ! empty($apiNode->short_name)) {
                            $shortName = $apiNode->short_name;
                            $needsShort = false;
                        }
                        if ($needsLong && ! empty($apiNode->long_name)) {
                            $longName = $apiNode->long_name;
                            $needsLong = false;
                        }
                        if ($needsRole && ! empty($apiNode->role) && $apiNode->role !== 'NODE') {
                            $role = $apiNode->role;
                            $needsRole = false;
                        }
                    }
                } catch (\Throwable) {
                    try {
                        $apiNode = DB::connection('ingesta')->table('node')
                            ->where('id', $targetHex)
                            ->orWhereRaw('LOWER(id) = ?', [strtolower($targetHex)])
                            ->first();
                        if ($apiNode) {
                            if ($needsShort && ! empty($apiNode->short_name)) {
                                $shortName = $apiNode->short_name;
                                $needsShort = false;
                            }
                            if ($needsLong && ! empty($apiNode->long_name)) {
                                $longName = $apiNode->long_name;
                                $needsLong = false;
                            }
                            if ($needsRole && ! empty($apiNode->role) && $apiNode->role !== 'NODE') {
                                $role = $apiNode->role;
                                $needsRole = false;
                            }
                        }
                    } catch (\Throwable) {
                        // Ignorar si ingesta no está accesible
                    }
                }
            }
        }

        return [
            'short_name' => $shortName ?: $fallbackShort,
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
        $router = CoordinatedRouter::where('node_id', $routerId)
            ->orWhereRaw('LOWER(node_id) = ?', [strtolower($routerId)])
            ->first();
        if (! $router) {
            return;
        }

        $favs = $router->favorite_nodes ?? [];
        if ($isAdd) {
            $exists = false;
            foreach ($favs as $idx => $f) {
                if (strtolower($f['hex'] ?? (string) $f) === strtolower($targetHex)) {
                    $exists = true;
                    if (empty($f['short_name']) || empty($f['long_name']) || ($f['short_name'] ?? '') === $targetHex) {
                        $identity = $this->resolveNodeIdentity($targetHex, $nodeData);
                        $favs[$idx]['short_name'] = $identity['short_name'];
                        $favs[$idx]['shortName'] = $identity['short_name'];
                        $favs[$idx]['long_name'] = $identity['long_name'];
                        $favs[$idx]['longName'] = $identity['long_name'];
                        $favs[$idx]['role'] = $identity['role'];
                    }
                    break;
                }
            }
            if (! $exists) {
                $identity = $this->resolveNodeIdentity($targetHex, $nodeData);
                $clean = ltrim($targetHex, '!');
                $num = ctype_xdigit($clean) ? (int) hexdec($clean) : 0;

                $favs[] = [
                    'hex' => $targetHex,
                    'num' => $num,
                    'short_name' => $identity['short_name'],
                    'shortName' => $identity['short_name'],
                    'long_name' => $identity['long_name'],
                    'longName' => $identity['long_name'],
                    'role' => $identity['role'],
                    'added_at' => now()->toIso8601String(),
                ];
            }
        } else {
            $favs = array_values(array_filter($favs, fn ($f): bool => strtolower($f['hex'] ?? (string) $f) !== strtolower($targetHex)));
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
        $router = CoordinatedRouter::where('node_id', $routerId)
            ->orWhereRaw('LOWER(node_id) = ?', [strtolower($routerId)])
            ->first();
        if (! $router) {
            return;
        }

        $blocked = $router->blocked_nodes ?? [];
        if ($isAdd) {
            $exists = false;
            foreach ($blocked as $idx => $b) {
                if (strtolower($b['hex'] ?? (string) $b) === strtolower($targetHex)) {
                    $exists = true;
                    if (empty($b['short_name']) || empty($b['long_name']) || ($b['short_name'] ?? '') === $targetHex) {
                        $identity = $this->resolveNodeIdentity($targetHex, $nodeData);
                        $blocked[$idx]['short_name'] = $identity['short_name'];
                        $blocked[$idx]['shortName'] = $identity['short_name'];
                        $blocked[$idx]['long_name'] = $identity['long_name'];
                        $blocked[$idx]['longName'] = $identity['long_name'];
                        $blocked[$idx]['role'] = $identity['role'];
                    }
                    break;
                }
            }
            if (! $exists) {
                $identity = $this->resolveNodeIdentity($targetHex, $nodeData);
                $clean = ltrim($targetHex, '!');
                $num = ctype_xdigit($clean) ? (int) hexdec($clean) : 0;

                $blocked[] = [
                    'hex' => $targetHex,
                    'num' => $num,
                    'short_name' => $identity['short_name'],
                    'shortName' => $identity['short_name'],
                    'long_name' => $identity['long_name'],
                    'longName' => $identity['long_name'],
                    'role' => $identity['role'],
                    'added_at' => now()->toIso8601String(),
                ];
            }
        } else {
            $blocked = array_values(array_filter($blocked, fn ($b): bool => strtolower($b['hex'] ?? (string) $b) !== strtolower($targetHex)));
        }

        $router->blocked_nodes = $blocked;
        $router->save();
    }

    /**
     * Limpia la lista completa de favoritos para el router indicado.
     */
    public function clearRouterFavorites(string $routerId): void
    {
        $router = CoordinatedRouter::where('node_id', $routerId)
            ->orWhereRaw('LOWER(node_id) = ?', [strtolower($routerId)])
            ->first();
        if ($router) {
            $router->favorite_nodes = null;
            $router->save();
        }
    }

    /**
     * Alias de compatibilidad para limpiar favoritos.
     */
    public function clearRouterFavoriteNodes(string $routerId): void
    {
        $this->clearRouterFavorites($routerId);
    }

    /**
     * Limpia la lista completa de bloqueados para el router indicado.
     */
    public function clearRouterBlocked(string $routerId): void
    {
        $router = CoordinatedRouter::where('node_id', $routerId)
            ->orWhereRaw('LOWER(node_id) = ?', [strtolower($routerId)])
            ->first();
        if ($router) {
            $router->blocked_nodes = null;
            $router->save();
        }
    }

    /**
     * Alias de compatibilidad para limpiar bloqueados.
     */
    public function clearRouterBlockedNodes(string $routerId): void
    {
        $this->clearRouterBlocked($routerId);
    }

    /**
     * Persiste la configuración detectada o enviada para el router indicado.
     *
     * @param  array<string, mixed>  $configData
     */
    public function updateRouterNodeConfig(string $routerId, array $configData): void
    {
        $router = CoordinatedRouter::where('node_id', $routerId)
            ->orWhereRaw('LOWER(node_id) = ?', [strtolower($routerId)])
            ->first();
        if (! $router) {
            return;
        }

        $current = $router->node_config ?? [];
        $cleanConfig = [];
        foreach ($configData as $k => $v) {
            if ($v !== null && $v !== '') {
                $cleanConfig[$k] = $v;
            }
        }
        $updated = array_merge($current, $cleanConfig);
        $updated['updated_at'] = now()->toIso8601String();

        if (! empty($configData['short_name'])) {
            $router->short_name = (string) $configData['short_name'];
        }
        if (! empty($configData['long_name'])) {
            $router->long_name = (string) $configData['long_name'];
        }

        if (isset($configData['latitude']) && is_numeric($configData['latitude'])) {
            $lat = (float) $configData['latitude'];
            if ($lat !== 0.0) {
                $router->latitude = $lat;
            }
        }
        if (isset($configData['longitude']) && is_numeric($configData['longitude'])) {
            $lon = (float) $configData['longitude'];
            if ($lon !== 0.0) {
                $router->longitude = $lon;
            }
        }
        if (isset($configData['altitude']) && is_numeric($configData['altitude'])) {
            $router->altitude = (int) $configData['altitude'];
        }

        $router->node_config = $updated;
        $router->save();
    }
}
