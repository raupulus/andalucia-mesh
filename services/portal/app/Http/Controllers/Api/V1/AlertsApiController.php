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
                'rule_id' => 'hops-high',
                'name' => 'Saltos excesivos (hop_start)',
                'risk' => 'medio',
                'type' => 'clientes',
                'description' => 'Un nodo cliente u origen transmite con un número de saltos superior a lo recomendado en la red de Andalucía (3 recomendado, máx. 5), saturando repetidores y multiplicando tráfico innecesario en la malla.',
            ],
            [
                'rule_id' => 'infra-silent',
                'name' => 'Router de infraestructura silente',
                'risk' => 'medio',
                'type' => 'infraestructura',
                'description' => 'Un repetidor o router de infraestructura deja de emitir paquetes periódicos según su intervalo habitual (> 6 h), indicando posible caída de suministro eléctrico o avería.',
            ],
            [
                'rule_id' => 'battery-low',
                'name' => 'Batería baja',
                'risk' => 'medio',
                'type' => 'infraestructura',
                'description' => 'Nivel de batería crítico en routers (< 60 % medio, < 40 % alto) o clientes (< 35 % bajo, < 15 % medio) confirmado en lecturas consecutivas.',
            ],
            [
                'rule_id' => 'gateway-offline',
                'name' => 'Pasarela (Gateway) desconectada',
                'risk' => 'medio',
                'type' => 'infraestructura',
                'description' => 'Una pasarela MQTT de la malla ha dejado de subir recepciones al broker superando su intervalo típico (> 15 min).',
            ],
            [
                'rule_id' => 'reboot-loop',
                'name' => 'Bucle de reinicios anómalo',
                'risk' => 'alto',
                'type' => 'infraestructura',
                'description' => 'Un nodo sufre 3 reinicios en 5 minutos (medio) o 5 o más en 10 minutos (alto), indicando fallo de alimentación (brownout), firmware o sobrecalentamiento.',
            ],
            [
                'rule_id' => 'chutil-high',
                'name' => 'Saturación del canal LoRa',
                'risk' => 'medio',
                'type' => 'red',
                'description' => 'Ocupación de canal elevada calculada ponderando un 60 % los routers y un 40 % los clientes (> 20 % bajo, > 30 % medio, > 40 % alto).',
            ],
            [
                'rule_id' => 'text-flood',
                'name' => 'Inundación de mensajes de texto',
                'risk' => 'medio',
                'type' => 'clientes',
                'description' => 'Cadencia abusiva de mensajes de texto en canales públicos (> 5/min bajo, 6-10/min medio, > 10/min alto).',
            ],
            [
                'rule_id' => 'telemetry-burst',
                'name' => 'Ráfaga excesiva de telemetría',
                'risk' => 'bajo',
                'type' => 'clientes',
                'description' => 'Emisiones repetitivas de métricas (ambiente, energía, batería o nodo) en menos de 1 minuto o más de 50 transmisiones por hora.',
            ],
            [
                'rule_id' => 'poll-abuse',
                'name' => 'Sondeos repetitivos a la red',
                'risk' => 'medio',
                'type' => 'clientes',
                'description' => 'Peticiones de sondeo masivas a toda la malla (^all) solicitando telemetría o nodeinfo (1 bajo, >= 3 en 15 min medio, >= 5 en 15 min alto).',
            ],
            [
                'rule_id' => 'sunset-battery',
                'name' => 'Batería insuficiente al anochecer',
                'risk' => 'medio',
                'type' => 'infraestructura',
                'description' => 'Router solar en Andalucía que llega a las 20:00 h peninsulares con carga insuficiente (< 60 % medio, < 40 % alto) para superar la noche.',
            ],
            [
                'rule_id' => 'traceroute-flood',
                'name' => 'Abuso de Traceroute',
                'risk' => 'medio',
                'type' => 'clientes',
                'description' => 'Ejecución masiva de consultas traceroute en una ventana móvil de 30 minutos (10-19 medio, >= 20 alto).',
            ],
            [
                'rule_id' => 'private-chaff',
                'name' => 'Tráfico privado o sensores en malla pública',
                'risk' => 'medio',
                'type' => 'clientes',
                'description' => 'Nodo emitiendo ráfagas de paquetes cifrados o sensores sobre la frecuencia pública obligando a los repetidores a retransmitirlos.',
            ],
            [
                'rule_id' => 'position-flood',
                'name' => 'Posiciones GPS aceleradas',
                'risk' => 'medio',
                'type' => 'clientes',
                'description' => 'Nodo emitiendo su posición geográfica con intervalos excesivamente reducidos (cada 1-3 minutos de forma continua).',
            ],
            [
                'rule_id' => 'router-role',
                'name' => 'Rol ROUTER no coordinado en Andalucía',
                'risk' => 'medio',
                'type' => 'infraestructura',
                'description' => 'Nodo ubicado en Andalucía con rol ROUTER o REPEATER sin estar aprobado en la lista de infraestructura coordinada comunitaria.',
            ],
            [
                'rule_id' => 'flood',
                'name' => 'Inundación de paquetes (Flood)',
                'risk' => 'medio',
                'type' => 'red',
                'description' => 'Un nodo emite tráfico propio a un ritmo muy superior a su línea base habitual en una ventana móvil de 10 minutos, degradando la capacidad del canal.',
            ],
            [
                'rule_id' => 'rafaga-masiva',
                'name' => 'Ráfaga masiva simultánea',
                'risk' => 'alto',
                'type' => 'red',
                'description' => 'Más de 50 nodos emiten paquetes simultáneamente en la malla en menos de 2 minutos, indicando tormenta de difusión o reactivación en cadena.',
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

        if (! $alerta) {
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
