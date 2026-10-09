<?php

declare(strict_types=1);

namespace App\Data\Ingest;

use App\Data\Fuente;
use App\Data\Resultado;
use App\Http\Controllers\AlertasController;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Throwable;

/**
 * Servicio de datos y motor de auditoría para el servicio interactivo "Mapa".
 * Gestiona la compilación de cachés estáticas/Redis, cálculo de métricas de red
 * y diagnósticos de configuración por nodo en tiempo real.
 */
class MapaData
{
    /**
     * Directorio físico donde se almacenan los volcados estáticos precompilados.
     */
    public const CACHE_DIR = 'cache/mapa';

    /**
     * Compila y guarda en disco/caché la estructura completa del mapa de nodos.
     * Genera nodes.json, stats.json y unoptimized.json junto con sus versiones pre-gzipeadas.
     *
     * @return array{nodes_count: int, unoptimized_count: int, stats: array<string, mixed>}
     */
    public function compilarCache(): array
    {
        $ahora = Carbon::now('UTC');
        $ahoraMadrid = Carbon::now('Europe/Madrid');
        $hace1h = $ahora->copy()->subHour();
        $hace24h = $ahora->copy()->subHours(24);

        // 1. Obtener todos los nodos con posición geográfica válida
        $nodosRaw = [];
        try {
            $nodosRaw = DB::connection('ingesta')
                ->table('api_map_nodes')
                ->get()
                ->toArray();
        } catch (Throwable $e) {
            // Si la vista api_map_nodes no existiera aún en un entorno de desarrollo, fallback
            try {
                $nodosRaw = DB::connection('ingesta')
                    ->table('node')
                    ->whereNotNull('latitude')
                    ->whereNotNull('longitude')
                    ->where('latitude', '!=', 0.0)
                    ->where('longitude', '!=', 0.0)
                    ->get()
                    ->toArray();
            } catch (Throwable) {
                $nodosRaw = [];
            }
        }

        // 2. Obtener alertas abiertas activas desde snm_alerts si la conexión está disponible
        $alertasPorNodo = [];
        try {
            $alertasRaw = DB::connection('alertas')
                ->table('api_alertas')
                ->where('estado', 'abierta')
                ->get();

            foreach ($alertasRaw as $alt) {
                $nodoId = (string) $alt->nodo;
                if (! isset($alertasPorNodo[$nodoId])) {
                    $alertasPorNodo[$nodoId] = [];
                }
                $alertasPorNodo[$nodoId][] = (array) $alt;
            }
        } catch (Throwable) {
            $alertasPorNodo = [];
        }

        // 3. Obtener intervalos típicos de emisión por nodo
        $intervalosPorNodo = [];
        try {
            $intervalosRaw = DB::connection('ingesta')
                ->table('api_node_intervals')
                ->get();

            foreach ($intervalosRaw as $inv) {
                $nId = (string) $inv->node_id;
                if (! isset($intervalosPorNodo[$nId])) {
                    $intervalosPorNodo[$nId] = [];
                }
                $intervalosPorNodo[$nId][] = (array) $inv;
            }
        } catch (Throwable) {
            $intervalosPorNodo = [];
        }

        // 4. Procesar y auditar cada nodo
        $listaNodos = [];
        $noOptimizados = [];
        $breakdownMotivos = [
            'hop_limit_alto' => 0,
            'client_base_fw' => 0,
            'nodeinfo_frecuente' => 0,
            'telemetria_frecuente' => 0,
            'alerta_activa' => 0,
        ];

        $activos1h = 0;
        $activos24h = 0;
        $gatewaysTotal = 0;
        $routersTotal = 0;

        foreach ($nodosRaw as $n) {
            $id = (string) $n->id;
            $shortName = $n->short_name ?: $id;
            $longName = $n->long_name ?: $shortName;
            $role = strtoupper((string) ($n->role ?? 'CLIENT'));
            $hwModel = (string) ($n->hw_model ?? 'DESCONOCIDO');
            $firmware = (string) ($n->firmware ?? '');
            $province = (string) ($n->province ?? 'FUERA');
            $hopLimit = $n->hop_start_last !== null ? (int) $n->hop_start_last : null;
            $isGateway = (bool) ($n->is_gateway ?? false);
            $isRouter = (bool) ($n->is_router ?? false);

            $lastSeenStr = (string) ($n->last_seen ?? $ahora->toIso8601String());
            $lastSeenCarbon = Carbon::parse($lastSeenStr, 'UTC');
            $lastSeenTs = $lastSeenCarbon->getTimestamp();

            // Clasificación temporal de actividad
            $status = 'inactive';
            if ($lastSeenCarbon->greaterThanOrEqualTo($hace1h)) {
                $status = 'active_1h';
                $activos1h++;
                $activos24h++;
            } elseif ($lastSeenCarbon->greaterThanOrEqualTo($hace24h)) {
                $status = 'active_24h';
                $activos24h++;
            }

            if ($isGateway) {
                $gatewaysTotal++;
            }
            if ($isRouter) {
                $routersTotal++;
            }

            // Auditoría y detección de problemas de configuración
            $problemas = [];

            // A) Hop limit alto o excesivo
            if ($hopLimit !== null) {
                if ($hopLimit > 5) {
                    $problemas[] = [
                        'clave' => 'hop_limit_alto',
                        'severidad' => 'critico',
                        'titulo' => "Hop Limit excesivo ({$hopLimit} saltos)",
                        'descripcion' => "El nodo emite con {$hopLimit} saltos, lo que causa tormentas de retransmisión innecesarias por toda la región.",
                        'solucion' => 'Configuración LoRa → Hop Limit → cambiar a 3 (máximo 5 en zonas aisladas)',
                    ];
                    $breakdownMotivos['hop_limit_alto']++;
                } elseif ($hopLimit > 3) {
                    $problemas[] = [
                        'clave' => 'hop_limit_alto',
                        'severidad' => 'aviso',
                        'titulo' => "Hop Limit superior al recomendado ({$hopLimit} saltos)",
                        'descripcion' => "Tu nodo emite con {$hopLimit} saltos. El estándar de la red es 3 saltos para proteger el aire.",
                        'solucion' => 'Configuración LoRa → Hop Limit → cambiar a 3',
                    ];
                    $breakdownMotivos['hop_limit_alto']++;
                }
            }

            // B) Rol CLIENT_BASE con firmware >= 2.7.17
            if ($role === 'CLIENT_BASE' && preg_match('/^v?(\d+)\.(\d+)(?:\.(\d+))?/', $firmware, $mFw)) {
                $fwMayor = (int) $mFw[1];
                $fwMenor = (int) $mFw[2];
                $fwParche = isset($mFw[3]) ? (int) $mFw[3] : 0;
                if ([$fwMayor, $fwMenor, $fwParche] >= [2, 7, 17]) {
                    $problemas[] = [
                        'clave' => 'client_base_fw',
                        'severidad' => 'aviso',
                        'titulo' => 'CLIENT_BASE ≥ 2.7.17 actúa como ROUTER_LATE',
                        'descripcion' => 'A partir de firmware 2.7.17, CLIENT_BASE introduce un retardo artificial antes de retransmitir paquetes, ralentizando la propagación.',
                        'solucion' => 'Config → Dispositivo → Rol → cambiar a CLIENT o CLIENT_MUTE si no enruta',
                    ];
                    $breakdownMotivos['client_base_fw']++;
                }
            }

            // C) Intervalos de emisión rápidos
            if (isset($intervalosPorNodo[$id])) {
                foreach ($intervalosPorNodo[$id] as $inv) {
                    $pnum = $inv['portnum'] ?? '';
                    $mediana = $inv['median_interval_s'] !== null ? (float) $inv['median_interval_s'] : null;

                    if ($pnum === 'nodeinfo' && $mediana !== null && $mediana < 43200) { // < 12h
                        $horas = round($mediana / 3600, 1);
                        $problemas[] = [
                            'clave' => 'nodeinfo_frecuente',
                            'severidad' => 'aviso',
                            'titulo' => "NodeInfo frecuente (~cada {$horas}h)",
                            'descripcion' => 'La información básica del nodo cambia rara vez. Emitir con cadencia menor a 12 horas satura el espectro.',
                            'solucion' => 'Config → Dispositivo → NodeInfo Broadcast Interval → 86400 (24h) o 259200 (72h)',
                        ];
                        $breakdownMotivos['nodeinfo_frecuente']++;
                    }

                    if ($pnum === 'telemetry' && ($inv['variant'] ?? '') === 'device_metrics' && $mediana !== null && $mediana < 7200) { // < 2h
                        $minutos = round($mediana / 60);
                        $problemas[] = [
                            'clave' => 'telemetria_frecuente',
                            'severidad' => 'aviso',
                            'titulo' => "Telemetría frecuente (~cada {$minutos}m)",
                            'descripcion' => 'La telemetría de dispositivo se transmite con periodicidad excesiva para nodos en batería o solares.',
                            'solucion' => 'Config → Telemetría → Device Metrics Interval → al menos 7200s (2h) o desactivar si está enchufado',
                        ];
                        $breakdownMotivos['telemetria_frecuente']++;
                    }
                }
            }

            // D) Alertas activas abiertas en detector-alertas
            if (! empty($alertasPorNodo[$id])) {
                foreach ($alertasPorNodo[$id] as $alt) {
                    $reglaId = (string) ($alt['regla'] ?? '');
                    $infoR = AlertasController::infoRegla($reglaId);
                    $riesgo = (string) ($alt['riesgo'] ?? 'medio');
                    $severidad = ($riesgo === 'alto') ? 'critico' : 'aviso';

                    $problemas[] = [
                        'clave' => 'alerta_'.$reglaId,
                        'severidad' => $severidad,
                        'titulo' => $infoR['nombre'],
                        'descripcion' => (string) ($alt['mensaje'] ?? $infoR['descripcion']),
                        'solucion' => $infoR['como_solucionar'],
                    ];
                    $breakdownMotivos['alerta_activa']++;
                }
            }

            $hasWarning = ! empty($problemas);
            $warningLevel = 'optimo';
            if ($hasWarning) {
                $hasCritico = collect($problemas)->contains('severidad', 'critico');
                $warningLevel = $hasCritico ? 'critico' : 'aviso';
            }

            // Preparar registro compacto para el mapa
            $nodoCompacto = [
                'id' => $id,
                'num' => $n->node_num ?? null,
                'short' => $shortName,
                'long' => $longName,
                'role' => $role,
                'hw' => $hwModel,
                'fw' => $firmware,
                'lat' => round((float) $n->latitude, 5),
                'lon' => round((float) $n->longitude, 5),
                'prec' => $n->position_precision_m !== null ? round((float) $n->position_precision_m, 1) : null,
                'src' => $n->position_source ?? 'unknown',
                'prov' => $province,
                'hop' => $hopLimit,
                'gw' => $isGateway,
                'rtr' => $isRouter,
                'bat' => $n->battery_level ?? null,
                'volt' => $n->voltage !== null ? round((float) $n->voltage, 2) : null,
                'chutil' => $n->channel_utilization !== null ? round((float) $n->channel_utilization, 1) : null,
                'seen' => $lastSeenStr,
                'ts' => $lastSeenTs,
                'status' => $status,
                'warn' => $hasWarning,
                'w_lvl' => $warningLevel,
                'w_cnt' => count($problemas),
                'w_keys' => array_values(array_unique(array_column($problemas, 'clave'))),
                'heard' => $n->heard_by ?? [],
            ];

            $listaNodos[] = $nodoCompacto;

            if ($hasWarning) {
                $noOptimizados[] = [
                    'id' => $id,
                    'short' => $shortName,
                    'long' => $longName,
                    'role' => $role,
                    'hw' => $hwModel,
                    'fw' => $firmware,
                    'prov' => $province,
                    'w_lvl' => $warningLevel,
                    'w_cnt' => count($problemas),
                    'problemas' => $problemas,
                    'lat' => $nodoCompacto['lat'],
                    'lon' => $nodoCompacto['lon'],
                    'seen' => $lastSeenStr,
                ];
            }
        }

        // Ordenar no optimizados: críticos primero, luego por cantidad de problemas
        usort($noOptimizados, function ($a, $b) {
            if ($a['w_lvl'] === 'critico' && $b['w_lvl'] !== 'critico') {
                return -1;
            }
            if ($b['w_lvl'] === 'critico' && $a['w_lvl'] !== 'critico') {
                return 1;
            }

            return $b['w_cnt'] <=> $a['w_cnt'];
        });

        // Estadísticas globales
        $stats = [
            'total_nodes' => count($listaNodos),
            'active_1h' => $activos1h,
            'active_24h' => $activos24h,
            'gateways_count' => $gatewaysTotal,
            'routers_count' => $routersTotal,
            'unoptimized_count' => count($noOptimizados),
            'unoptimized_breakdown' => $breakdownMotivos,
            'updated_at' => $ahoraMadrid->format('H:i'),
            'updated_at_iso' => $ahora->toIso8601String(),
        ];

        // 5. Guardar ficheros en disco público (public/cache/mapa/)
        $publicCachePath = public_path(self::CACHE_DIR);
        if (! File::isDirectory($publicCachePath)) {
            File::makeDirectory($publicCachePath, 0755, true, true);
        }

        $nodesJson = json_encode($listaNodos, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $statsJson = json_encode($stats, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $unoptimizedJson = json_encode($noOptimizados, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        if ($nodesJson) {
            File::put($publicCachePath.'/nodes.json', $nodesJson);
            $gz = gzencode($nodesJson, 9);
            if ($gz !== false) {
                File::put($publicCachePath.'/nodes.json.gz', $gz);
            }
        }

        if ($statsJson) {
            File::put($publicCachePath.'/stats.json', $statsJson);
            $gz = gzencode($statsJson, 9);
            if ($gz !== false) {
                File::put($publicCachePath.'/stats.json.gz', $gz);
            }
        }

        if ($unoptimizedJson) {
            File::put($publicCachePath.'/unoptimized.json', $unoptimizedJson);
            $gz = gzencode($unoptimizedJson, 9);
            if ($gz !== false) {
                File::put($publicCachePath.'/unoptimized.json.gz', $gz);
            }
        }

        // 6. Guardar en caché de Laravel (Redis o Driver de entorno)
        Cache::put('mapa:stats', $stats, 600);
        Cache::put('mapa:unoptimized', $noOptimizados, 600);

        return [
            'nodes_count' => count($listaNodos),
            'unoptimized_count' => count($noOptimizados),
            'stats' => $stats,
        ];
    }

    /**
     * Obtiene el informe detallado de diagnóstico y desglose de 24h para el modal de un nodo.
     *
     * @return array<string, mixed>|null
     */
    public function obtenerDetalleNodo(string $nodeId): ?array
    {
        $idNormalizado = Diagnostico::normalizarId($nodeId);

        return Fuente::recordar("mapa:node_detail:{$idNormalizado}", ['id' => $idNormalizado], 120, function () use ($idNormalizado) {
            // 1. Obtener nodo de la base de datos
            $node = DB::connection('ingesta')
                ->table('api_nodes')
                ->where('id', $idNormalizado)
                ->first();

            if (! $node) {
                return null;
            }

            // 2. Obtener desglose de paquetes en las últimas 24 horas
            $packetCounts = [
                'nodeinfo' => 0,
                'position' => 0,
                'telemetry' => 0,
                'routing' => 0,
                'traceroute' => 0,
                'text' => 0,
                'rangetest' => 0,
                'neighborinfo' => 0,
                'other' => 0,
            ];

            try {
                $filas24h = DB::connection('ingesta')
                    ->table('api_node_packets_24h')
                    ->where('node_id', $idNormalizado)
                    ->get();

                foreach ($filas24h as $f) {
                    $pnum = strtolower((string) $f->portnum);
                    $cant = (int) $f->packets_24h;

                    if (isset($packetCounts[$pnum])) {
                        $packetCounts[$pnum] += $cant;
                    } elseif ($pnum === 'range_test' || $pnum === 'rangetest') {
                        $packetCounts['rangetest'] += $cant;
                    } elseif ($pnum === 'neighbor_info' || $pnum === 'neighborinfo') {
                        $packetCounts['neighborinfo'] += $cant;
                    } else {
                        $packetCounts['other'] += $cant;
                    }
                }
            } catch (Throwable) {
                // Fallback silencioso si la vista aún no está disponible
            }

            $totalPaquetes24h = array_sum($packetCounts);
            $breakdown = [];
            $nombresLegibles = [
                'nodeinfo' => 'NodeInfo',
                'position' => 'Posición',
                'telemetry' => 'Telemetría',
                'routing' => 'Enrutamiento',
                'traceroute' => 'Traceroute',
                'text' => 'Texto',
                'rangetest' => 'Range Test',
                'neighborinfo' => 'Neighbor Info',
            ];

            foreach ($nombresLegibles as $clave => $etiqueta) {
                $c = $packetCounts[$clave] ?? 0;
                $pct = $totalPaquetes24h > 0 ? round(($c / $totalPaquetes24h) * 100, 1) : 0.0;
                $breakdown[] = [
                    'clave' => $clave,
                    'nombre' => $etiqueta,
                    'count' => $c,
                    'porcentaje' => $pct,
                ];
            }

            // 3. Auditoría completa de salud para este nodo
            $diagnosticoService = app(Diagnostico::class);
            $diagResultado = $diagnosticoService->obtenerDiagnostico($idNormalizado);
            $diagDatos = $diagResultado->datos;

            $hopLimit = $node->hop_start_last !== null ? (int) $node->hop_start_last : null;
            $hopStatus = 'desconocido';
            $hopMensaje = 'Hop Limit no registrado';

            if ($hopLimit !== null) {
                if ($hopLimit <= 3) {
                    $hopStatus = 'recomendado';
                    $hopMensaje = "Hop Limit configurado: {$hopLimit} — dentro del rango recomendado (estándar de la red)";
                } elseif ($hopLimit <= 5) {
                    $hopStatus = 'aviso';
                    $hopMensaje = "Hop Limit configurado: {$hopLimit} — mayor al estándar recomendado (3 saltos)";
                } else {
                    $hopStatus = 'critico';
                    $hopMensaje = "Hop Limit configurado: {$hopLimit} — excesivo (genera saturación en la malla)";
                }
            }

            // Unificar hallazgos y enriquecer soluciones
            $problemas = $diagDatos['findings'] ?? [];
            foreach ($problemas as &$p) {
                if (! isset($p['solucion'])) {
                    if ($p['clave'] === 'saltos_excesivos' || $p['clave'] === 'saltos_altos') {
                        $p['solucion'] = 'Ajustes de Radio → Configuración LoRa → Hop Limit: cambiar a 3.';
                    } elseif ($p['clave'] === 'nodeinfo_frecuente') {
                        $p['solucion'] = 'Ajustes de Dispositivo → NodeInfo Broadcast Interval: fijar en 86400 (24h) o 259200 (72h).';
                    } elseif ($p['clave'] === 'telemetria_rapida') {
                        $p['solucion'] = 'Ajustes de Telemetría → Device Metrics Interval: al menos 7200s (2h) o desactivar.';
                    } elseif ($p['clave'] === 'reboot_loop') {
                        $p['solucion'] = 'Verificar alimentación, cables USB, tensión de celda y actualizar a la última versión beta estable de firmware.';
                    } elseif ($p['clave'] === 'bateria_critica') {
                        $p['solucion'] = 'Revisar regulador de carga, orientación del panel solar o sustituir celda 18650/LiFePO4 degradada.';
                    } else {
                        $p['solucion'] = 'Revisar la guía de configuración recomendada en la plataforma.';
                    }
                }
            }
            unset($p);

            return [
                'id' => $idNormalizado,
                'node_num' => $node->node_num ?? null,
                'short_name' => $node->short_name ?: $idNormalizado,
                'long_name' => $node->long_name ?: ($node->short_name ?: $idNormalizado),
                'role' => $node->role ?: 'CLIENT',
                'hw_model' => $node->hw_model ?: 'DESCONOCIDO',
                'firmware' => $node->firmware ?: 'No disponible',
                'province' => $node->province ?: 'FUERA',
                'last_seen' => $node->last_seen,
                'hop_limit' => $hopLimit,
                'hop_status' => $hopStatus,
                'hop_mensaje' => $hopMensaje,
                'is_gateway' => (bool) $node->is_gateway,
                'is_router' => (bool) $node->is_router,
                'problemas' => $problemas,
                'packets_24h_total' => $totalPaquetes24h,
                'packets_breakdown' => $breakdown,
                'meshview_url' => 'https://'.config('proyecto.dominio').'/meshview/nodes/'.$idNormalizado,
                'revisa_nodo_url' => '/revisa-tu-nodo/'.$idNormalizado,
            ];
        });
    }

    /**
     * Devuelve las estadísticas globales del mapa desde caché o compila si no existen.
     *
     * @return array<string, mixed>
     */
    public function obtenerStats(): array
    {
        $stats = Cache::get('mapa:stats');
        if (is_array($stats)) {
            return $stats;
        }

        $res = $this->compilarCache();

        return $res['stats'];
    }

    /**
     * Devuelve el catálogo de nodos no optimizados desde caché o compila si no existen.
     *
     * @return array<int, array<string, mixed>>
     */
    public function obtenerNoOptimizados(): array
    {
        $cached = Cache::get('mapa:unoptimized');
        if (is_array($cached)) {
            return $cached;
        }

        $this->compilarCache();

        return Cache::get('mapa:unoptimized', []);
    }
}
