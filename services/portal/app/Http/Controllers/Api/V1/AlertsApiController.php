<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class AlertsApiController extends Controller
{
    /**
     * Catálogo estático canónico de reglas del detector de anomalías.
     *
     * @return array<int, array{rule_id: string, name: string, risk: string, type: string, description: string}>
     */
    public static function catalogoReglas(): array
    {
        return [
            [
                'rule_id' => 'battery_critical',
                'name' => 'Batería crítica en infraestructura',
                'risk' => 'alto',
                'type' => 'infraestructura',
                'description' => 'Un repetidor o router tiene un nivel de batería inferior al 15% o voltaje crítico.',
            ],
            [
                'rule_id' => 'reboot_loop',
                'name' => 'Bucle de reinicios anómalo',
                'risk' => 'alto',
                'type' => 'infraestructura',
                'description' => 'Un nodo troncal ha sufrido múltiples reinicios consecutivos en una ventana breve.',
            ],
            [
                'rule_id' => 'gateway_down',
                'name' => 'Gateway desconectado',
                'risk' => 'medio',
                'type' => 'infraestructura',
                'description' => 'Un gateway activo ha dejado de publicar telemetría o paquetes en MQTT.',
            ],
            [
                'rule_id' => 'excessive_hop_limit',
                'name' => 'Configuración de saltos excesiva',
                'risk' => 'medio',
                'type' => 'clientes',
                'description' => 'Un nodo cliente transmite con hop_limit mayor a 5 saturando la red.',
            ],
            [
                'rule_id' => 'channel_saturated',
                'name' => 'Saturación grave del canal',
                'risk' => 'alto',
                'type' => 'infraestructura',
                'description' => 'La ocupación del espectro supera el 40% sostenido en una provincia.',
            ],
        ];
    }

    /**
     * GET /api/v1/alerts/catalog
     */
    public function catalog(): JsonResponse
    {
        $reglas = self::catalogoReglas();

        return response()->json([
            'generated_at' => gmdate('Y-m-d\TH:i:s\Z'),
            'stale' => false,
            'count' => count($reglas),
            'items' => $reglas,
        ], 200, [
            'Cache-Control' => 'public, max-age=3600',
            'X-Cache' => 'HIT',
        ]);
    }

    /**
     * GET /api/v1/alerts
     */
    public function index(Request $request): JsonResponse
    {
        $state = $request->query('state');
        $risk = $request->query('risk');
        $type = $request->query('type');
        $limit = max(1, min(500, (int) $request->query('limit', 50)));

        $items = [];
        try {
            $query = DB::connection('alertas')
                ->table('api_alertas')
                ->limit($limit);

            if ($state !== null) {
                $query->where('estado', $state);
            }
            if ($risk !== null) {
                $query->where('riesgo', $risk);
            }
            if ($type !== null) {
                $query->where('tipo', $type);
            }

            $filas = $query->orderByDesc('inicio_at')->get();
            foreach ($filas as $f) {
                $items[] = (array) $f;
            }
        } catch (Throwable) {
            // Si la base de alertas aún no tiene datos o está caída, servimos array vacío
            $items = [];
        }

        return response()->json([
            'generated_at' => gmdate('Y-m-d\TH:i:s\Z'),
            'stale' => false,
            'limit' => $limit,
            'count' => count($items),
            'next_cursor' => null,
            'items' => $items,
        ], 200, [
            'Cache-Control' => 'public, max-age=30',
            'X-Cache' => 'MISS',
        ]);
    }

    /**
     * GET /api/v1/alerts/{id}
     */
    public function show(string $id): JsonResponse
    {
        $alerta = null;
        try {
            $alerta = DB::connection('alertas')
                ->table('api_alertas')
                ->where('id', strtoupper($id))
                ->first();
        } catch (Throwable) {
            $alerta = null;
        }

        if (!$alerta) {
            return response()->json([
                'error' => [
                    'status' => 404,
                    'code' => 'not_found',
                    'message' => "Alerta con id '{$id}' no encontrada.",
                ],
            ], 404, ['Cache-Control' => 'public, max-age=60']);
        }

        return response()->json([
            'generated_at' => gmdate('Y-m-d\TH:i:s\Z'),
            'stale' => false,
            'alert' => (array) $alerta,
        ], 200, [
            'Cache-Control' => 'public, max-age=30',
            'X-Cache' => 'MISS',
        ]);
    }
}
