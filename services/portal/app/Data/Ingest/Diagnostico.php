<?php

declare(strict_types=1);

namespace App\Data\Ingest;

use App\Data\Fuente;
use App\Data\Resultado;
use App\Services\AlertThresholdService;
use Illuminate\Support\Facades\DB;

class Diagnostico
{
    protected AlertThresholdService $thresholdService;

    public function __construct(?AlertThresholdService $thresholdService = null)
    {
        $this->thresholdService = $thresholdService ?? app(AlertThresholdService::class);
    }

    /**
     * Busca nodos por id, nombre corto o largo.
     */
    public function buscar(string $termino, int $limit = 20): Resultado
    {
        $limit = max(1, min(50, $limit));
        $termino = trim($termino);

        $params = [
            'termino' => $termino,
            'limit' => $limit,
        ];

        return Fuente::recordar('buscar_nodos', $params, 60, function () use ($termino, $limit) {
            $like = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $termino).'%';

            $driver = DB::connection('ingesta')->getDriverName();
            $likeOp = $driver === 'pgsql' ? 'ilike' : 'like';

            $items = DB::connection('ingesta')
                ->table('api_nodes')
                ->select([
                    'id',
                    'short_name',
                    'long_name',
                    'role',
                    'hw_model',
                    'firmware',
                    'province',
                    'last_seen',
                    'is_gateway',
                    'is_router',
                ])
                ->where(function ($q) use ($like, $likeOp) {
                    $q->where('id', $likeOp, $like)
                        ->orWhere('short_name', $likeOp, $like)
                        ->orWhere('long_name', $likeOp, $like);
                })
                ->orderByDesc('last_seen')
                ->limit($limit)
                ->get()
                ->toArray();

            return [
                'count' => count($items),
                'items' => $items,
            ];
        });
    }

    /**
     * Genera el diagnóstico completo de un nodo para la herramienta "Revisa tu nodo".
     */
    public function obtenerDiagnostico(string $nodeId): Resultado
    {
        $nodeId = self::normalizarId($nodeId);

        return Fuente::recordar("diagnostico:{$nodeId}", ['id' => $nodeId], 300, function () use ($nodeId) {
            // 1. Datos básicos del nodo
            $node = DB::connection('ingesta')
                ->table('api_nodes')
                ->where('id', $nodeId)
                ->first();

            if (! $node) {
                return null;
            }

            // 2. Intervalos de emisión
            $intervalos = DB::connection('ingesta')
                ->table('api_node_intervals')
                ->where('node_id', $nodeId)
                ->get()
                ->map(fn ($r) => (array) $r)
                ->toArray();

            // 3. Histórico de batería (7 días)
            $bateria = DB::connection('ingesta')
                ->table('api_node_battery_daily')
                ->where('node_id', $nodeId)
                ->orderBy('day', 'asc')
                ->get()
                ->map(fn ($r) => (array) $r)
                ->toArray();

            // 4. Reinicios diarios (7 días)
            $reinicios = DB::connection('ingesta')
                ->table('api_node_reboots_daily')
                ->where('node_id', $nodeId)
                ->orderBy('day', 'asc')
                ->get()
                ->map(fn ($r) => (array) $r)
                ->toArray();

            // 5. Análisis de hallazgos y salud del nodo con umbrales dinámicos por rol
            $hallazgos = [];
            $rol = (string) ($node->role ?? '');

            $hopCritical = (int) $this->thresholdService->getForRole($rol, 'hop_limit_critical', 7.0);
            $hopWarning = (int) $this->thresholdService->getForRole($rol, 'hop_limit_warning', 6.0);
            $hopRec = (int) $this->thresholdService->getForRole($rol, 'hop_limit_recommended', 3.0);

            $nodeinfoMin = $this->thresholdService->getForRole($rol, 'nodeinfo_interval_min_s', 43200.0);
            $nodeinfoRec = $this->thresholdService->getForRole($rol, 'nodeinfo_interval_recommended_s', 259200.0);
            $nodeinfoRecHours = (int) round($nodeinfoRec / 3600);

            $telemetryMin = $this->thresholdService->getForRole($rol, 'telemetry_interval_min_s', 3600.0);
            $telemetryRec = $this->thresholdService->getForRole($rol, 'telemetry_interval_recommended_s', 7200.0);
            $telemetryMinHours = round($telemetryMin / 3600, 1);
            $telemetryRecHours = round($telemetryRec / 3600, 1);

            $rebootCritCount = (int) $this->thresholdService->getForRole($rol, 'reboot_loop_count_10m', 5.0);
            $batteryWarnPct = $this->thresholdService->getForRole($rol, 'battery_warning_pct', 25.0);

            // A) Límite de saltos
            $hopLimit = $node->hop_start_last !== null ? (int) $node->hop_start_last : null;
            if ($hopLimit !== null && $hopLimit >= $hopCritical) {
                $hallazgos[] = [
                    'clave' => 'saltos_excesivos',
                    'severidad' => 'critico',
                    'titulo' => 'Límite de saltos excesivo ('.$hopLimit.')',
                    'descripcion' => 'Tu nodo tiene configurado un límite de saltos superior o igual a '.$hopCritical.', lo que propaga paquetes innecesariamente por toda Andalucía saturando el canal de radio. Se recomienda configurarlo en '.$hopRec.' saltos.',
                ];
            } elseif ($hopLimit !== null && $hopLimit >= $hopWarning) {
                $hallazgos[] = [
                    'clave' => 'saltos_altos',
                    'severidad' => 'aviso',
                    'titulo' => 'Límite de saltos mayor al estándar ('.$hopLimit.')',
                    'descripcion' => 'Tu nodo emite con '.$hopLimit.' saltos. El estándar de la red es '.$hopRec.' saltos, salvo en zonas remotas o aisladas.',
                ];
            }

            // B) Intervalos rápidos
            foreach ($intervalos as $inv) {
                $portnum = $inv['portnum'] ?? '';
                $mediana = $inv['median_interval_s'] !== null ? (float) $inv['median_interval_s'] : null;

                if ($portnum === 'nodeinfo' && $mediana !== null && $mediana < $nodeinfoMin) {
                    $hallazgos[] = [
                        'clave' => 'nodeinfo_frecuente',
                        'severidad' => 'aviso',
                        'titulo' => 'NodeInfo emitido con demasiada frecuencia',
                        'descripcion' => 'El nodo emite su información cada '.round($mediana / 3600, 1).' horas. En tu grupo de nodos se recomienda un intervalo de '.$nodeinfoRecHours.' horas para no ocupar espectro.',
                    ];
                }

                if ($portnum === 'telemetry' && ($inv['variant'] ?? '') === 'device_metrics' && $mediana !== null && $mediana < $telemetryMin) {
                    $hallazgos[] = [
                        'clave' => 'telemetria_rapida',
                        'severidad' => 'aviso',
                        'titulo' => 'Telemetría enviada con frecuencia elevada',
                        'descripcion' => 'Las métricas del dispositivo se transmiten cada '.round($mediana / 60).' minutos. Se aconseja al menos '.$telemetryMinHours.' a '.$telemetryRecHours.' horas en nodos solares o desactivarla si está enchufado.',
                    ];
                }
            }

            // C) Bucles de reinicio
            $totalReinicios = 0;
            foreach ($reinicios as $reb) {
                $totalReinicios += (int) ($reb['reboots'] ?? 0);
            }
            if ($totalReinicios >= $rebootCritCount) {
                $hallazgos[] = [
                    'clave' => 'reboot_loop',
                    'severidad' => 'critico',
                    'titulo' => 'Posible bucle de reinicio detectado',
                    'descripcion' => "Se han registrado {$totalReinicios} reinicios en los últimos 7 días. Comprueba la estabilidad de la alimentación o actualiza el firmware a la última beta estable.",
                ];
            }

            // D) Batería baja recurrente
            $diasBateriaBaja = 0;
            foreach ($bateria as $bat) {
                if (isset($bat['min_level']) && $bat['min_level'] !== null && (float) $bat['min_level'] < $batteryWarnPct) {
                    $diasBateriaBaja++;
                }
            }
            if ($diasBateriaBaja >= 2) {
                $hallazgos[] = [
                    'clave' => 'bateria_critica',
                    'severidad' => 'aviso',
                    'titulo' => 'Batería descendiendo a niveles críticos',
                    'descripcion' => "En {$diasBateriaBaja} días de los últimos 7, la batería ha descendido por debajo del {$batteryWarnPct} %. Revisa la orientación del panel solar o la degradación de la celda.",
                ];
            }

            // E) Rol CLIENT_BASE con firmware >= 2.7.17
            $rol = strtoupper((string) ($node->role ?? ''));
            $firmware = (string) ($node->firmware ?? '');
            if ($rol === 'CLIENT_BASE' && preg_match('/^v?(\d+)\.(\d+)(?:\.(\d+))?/', $firmware, $mFw)) {
                $fwMayor = (int) $mFw[1];
                $fwMenor = (int) $mFw[2];
                $fwParche = isset($mFw[3]) ? (int) $mFw[3] : 0;
                if ([$fwMayor, $fwMenor, $fwParche] >= [2, 7, 17]) {
                    $hallazgos[] = [
                        'clave' => 'client_base_fw',
                        'severidad' => 'aviso',
                        'titulo' => 'CLIENT_BASE en firmware ≥ 2.7.17 actúa como ROUTER_LATE',
                        'descripcion' => 'A partir del firmware 2.7.17, el rol CLIENT_BASE introduce retardos de enrutamiento (comportamiento ROUTER_LATE), ralentizando los mensajes en la red. Se recomienda cambiar a CLIENT o CLIENT_MUTE.',
                        'solucion' => 'Config → Dispositivo → Rol → cambiar a CLIENT o CLIENT_MUTE si el nodo no necesita enrutar',
                    ];
                }
            }

            return [
                'node' => [
                    'id' => (string) $node->id,
                    'short_name' => $node->short_name,
                    'long_name' => $node->long_name,
                    'role' => $node->role,
                    'hw_model' => $node->hw_model,
                    'firmware' => $node->firmware,
                    'province' => $node->province,
                    'last_seen' => $node->last_seen,
                    'hop_start_last' => $node->hop_start_last,
                    'is_gateway' => (bool) $node->is_gateway,
                    'is_router' => (bool) $node->is_router,
                    'heard_by' => $node->heard_by ?? [],
                ],
                'intervals' => $intervalos,
                'battery_daily' => $bateria,
                'reboots_daily' => $reinicios,
                'findings' => $hallazgos,
                'health_status' => empty($hallazgos) ? 'optimo' : (collect($hallazgos)->contains('severidad', 'critico') ? 'atencion_requerida' : 'mejorable'),
            ];
        });
    }

    /**
     * Normaliza un identificador de nodo al formato canónico !xxxxxxxx.
     */
    public static function normalizarId(string $id): string
    {
        $limpio = ltrim(trim($id), '!');

        return '!'.strtolower($limpio);
    }
}
