<?php

declare(strict_types=1);

namespace App\Data\Ingest;

use App\Data\Fuente;
use App\Data\Resultado;
use Illuminate\Support\Facades\DB;

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
            $query = DB::connection('ingesta')
                ->table('api_routers')
                ->select([
                    'id',
                    'short_name',
                    'long_name',
                    'role',
                    'province',
                    'hw_model',
                    'battery_level',
                    'voltage',
                    'powered',
                    'battery_at',
                    'channel_utilization',
                    'air_util_tx',
                    'metrics_at',
                    'last_seen',
                    'is_gateway',
                    'last_reboot_at',
                ]);

            if ($province !== null) {
                $query->where('province', $province);
            } elseif ($ambito === 'andalucia') {
                $query->whereIn('province', self::PROVINCIAS_ANDALUCIA);
            } elseif ($ambito === 'espana') {
                $query->where('province', 'like', 'ES-%')
                    ->where('province', '!=', 'FUERA');
            }
            // En ámbito 'global' se cargan todos los routers sin filtro de provincia

            switch ($sort) {
                case 'last_seen_desc':
                    $query->orderByDesc('last_seen');
                    break;
                case 'chutil_desc':
                    $query->orderByDesc('channel_utilization');
                    break;
                case 'battery_asc':
                default:
                    // Primero los que tienen menor batería, luego los alimentados a red
                    $query->orderBy('battery_level', 'asc');
                    break;
            }

            $items = $query->get()->map(function ($r) {
                return [
                    'id' => (string) $r->id,
                    'short_name' => $r->short_name,
                    'long_name' => $r->long_name,
                    'role' => (string) $r->role,
                    'province' => $r->province,
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
