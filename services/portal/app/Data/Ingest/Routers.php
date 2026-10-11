<?php

declare(strict_types=1);

namespace App\Data\Ingest;

use App\Data\Fuente;
use App\Data\Resultado;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class Routers
{
    /**
     * Provincias oficiales de la comunidad de Andalucía (ISO 3166-2:ES).
     *
     * @var list<string>
     */
    public const PROVINCIAS_ANDALUCIA = [
        'ES-AL', 'ES-CA', 'ES-CO', 'ES-GR', 'ES-H', 'ES-HU', 'ES-J', 'ES-JA', 'ES-MA', 'ES-SE',
    ];

    /**
     * Obtiene el listado de routers de infraestructura y su telemetría.
     *
     * @param  string|null  $province  Filtro opcional por código ISO provincial
     * @param  string  $sort  battery_asc, last_seen_desc, chutil_desc
     * @param  string  $ambito  'andalucia', 'espana', 'global'
     */
    public function obtenerResultado(?string $province = null, string $sort = 'battery_asc', string $ambito = 'andalucia'): Resultado
    {
        $params = [
            'province' => $province,
            'sort' => $sort,
            'ambito' => $ambito,
        ];

        return Fuente::recordar('routers', $params, 60, function () use ($province, $sort, $ambito) {
            $connection = DB::connection('ingesta');
            $schema = Schema::connection('ingesta');

            $hasDirectCoords = false;
            $hasMapNodes = false;
            try {
                $hasDirectCoords = $schema->hasColumn('api_routers', 'latitude');
                if (! $hasDirectCoords) {
                    $hasMapNodes = $schema->hasTable('api_map_nodes') || $schema->hasView('api_map_nodes');
                }
            } catch (Throwable) {
                $hasDirectCoords = false;
                $hasMapNodes = false;
            }

            $query = $connection->table('api_routers');

            if ($hasDirectCoords) {
                $query->select([
                    'api_routers.id',
                    'api_routers.short_name',
                    'api_routers.long_name',
                    'api_routers.role',
                    'api_routers.province',
                    'api_routers.latitude',
                    'api_routers.longitude',
                    'api_routers.hw_model',
                    'api_routers.battery_level',
                    'api_routers.voltage',
                    'api_routers.powered',
                    'api_routers.battery_at',
                    'api_routers.channel_utilization',
                    'api_routers.air_util_tx',
                    'api_routers.metrics_at',
                    'api_routers.last_seen',
                    'api_routers.is_gateway',
                    'api_routers.last_reboot_at',
                ]);
            } elseif ($hasMapNodes) {
                $query->leftJoin('api_map_nodes as m', 'api_routers.id', '=', 'm.id')
                    ->select([
                        'api_routers.id',
                        'api_routers.short_name',
                        'api_routers.long_name',
                        'api_routers.role',
                        'api_routers.province',
                        'api_routers.hw_model',
                        'api_routers.battery_level',
                        'api_routers.voltage',
                        'api_routers.powered',
                        'api_routers.battery_at',
                        'api_routers.channel_utilization',
                        'api_routers.air_util_tx',
                        'api_routers.metrics_at',
                        'api_routers.last_seen',
                        'api_routers.is_gateway',
                        'api_routers.last_reboot_at',
                        'm.latitude',
                        'm.longitude',
                    ]);
            } else {
                $query->select([
                    'api_routers.id',
                    'api_routers.short_name',
                    'api_routers.long_name',
                    'api_routers.role',
                    'api_routers.province',
                    'api_routers.hw_model',
                    'api_routers.battery_level',
                    'api_routers.voltage',
                    'api_routers.powered',
                    'api_routers.battery_at',
                    'api_routers.channel_utilization',
                    'api_routers.air_util_tx',
                    'api_routers.metrics_at',
                    'api_routers.last_seen',
                    'api_routers.is_gateway',
                    'api_routers.last_reboot_at',
                ]);
            }

            if ($province !== null) {
                $query->where('api_routers.province', $province);
            } elseif ($ambito === 'andalucia') {
                $query->whereIn('api_routers.province', self::PROVINCIAS_ANDALUCIA);
            } elseif ($ambito === 'espana') {
                $query->where(function ($q) {
                    $q->whereNotIn('api_routers.province', self::PROVINCIAS_ANDALUCIA)
                        ->orWhereNull('api_routers.province');
                });
            } elseif ($ambito === 'ambos' || $ambito === 'global') {
                // Sin restricción de provincia: incluye Andalucía y resto de la red
            }

            switch ($sort) {
                case 'last_seen_desc':
                    $query->orderByDesc('api_routers.last_seen');
                    break;
                case 'chutil_desc':
                    $query->orderByDesc('api_routers.channel_utilization');
                    break;
                case 'battery_asc':
                default:
                    // Primero los que tienen menor batería, luego los alimentados a red
                    $query->orderBy('api_routers.battery_level', 'asc');
                    break;
            }

            $items = $query->get()->map(function ($r) {
                return [
                    'id' => (string) $r->id,
                    'short_name' => $r->short_name,
                    'long_name' => $r->long_name,
                    'role' => (string) $r->role,
                    'province' => $r->province,
                    'latitude' => isset($r->latitude) && $r->latitude !== null ? (float) $r->latitude : null,
                    'longitude' => isset($r->longitude) && $r->longitude !== null ? (float) $r->longitude : null,
                    'hw_model' => $r->hw_model,
                    'battery_level' => $r->battery_level !== null ? (int) $r->battery_level : null,
                    'voltage' => $r->voltage !== null ? round((float) $r->voltage, 2) : null,
                    'powered' => (bool) $r->powered,
                    'battery_at' => $r->battery_at,
                    'channel_utilization' => $r->channel_utilization !== null ? round((float) $r->channel_utilization, 1) : null,
                    'air_util_tx' => $r->air_util_tx !== null ? round((float) $r->air_util_tx, 1) : null,
                    'metrics_at' => $r->metrics_at,
                    'last_seen' => (string) $r->last_seen,
                    'is_gateway' => (bool) $r->is_gateway,
                    'last_reboot_at' => $r->last_reboot_at,
                ];
            })->toArray();

            return [
                'count' => count($items),
                'items' => $items,
            ];
        });
    }
}
