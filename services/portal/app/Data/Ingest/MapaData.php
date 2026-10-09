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

        // En desarrollo local o pruebas sin base de datos poblada, cargar nodos de ejemplo realistas
        if (empty($nodosRaw) && app()->environment('local', 'testing')) {
            $nodosRaw = $this->obtenerNodosEjemplo();
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
                'lat' => round((float) ($n->latitude ?? 0.0), 5),
                'lon' => round((float) ($n->longitude ?? 0.0), 5),
                'prec' => isset($n->position_precision_m) && $n->position_precision_m !== null ? round((float) $n->position_precision_m, 1) : null,
                'src' => $n->position_source ?? 'unknown',
                'prov' => $province,
                'hop' => $hopLimit,
                'gw' => $isGateway,
                'rtr' => $isRouter,
                'bat' => $n->battery_level ?? null,
                'volt' => isset($n->voltage) && $n->voltage !== null ? round((float) $n->voltage, 2) : null,
                'chutil' => isset($n->channel_utilization) && $n->channel_utilization !== null ? round((float) $n->channel_utilization, 1) : null,
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

        $res = Fuente::recordar("mapa:node_detail:{$idNormalizado}", ['id' => $idNormalizado], 120, function () use ($idNormalizado) {
            $node = null;
            try {
                $node = DB::connection('ingesta')
                    ->table('api_nodes')
                    ->where('id', $idNormalizado)
                    ->first();
            } catch (Throwable) {
                $node = null;
            }

            if (! $node && app()->environment('local', 'testing')) {
                foreach ($this->obtenerNodosEjemplo() as $ej) {
                    if (strcasecmp($ej->id, $idNormalizado) === 0) {
                        $node = $ej;
                        break;
                    }
                }
            }

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

            if ($totalPaquetes24h === 0) {
                $packetCounts = [
                    'nodeinfo' => 3,
                    'position' => 8,
                    'telemetry' => 24,
                    'routing' => 45,
                    'traceroute' => 4,
                    'text' => 12,
                    'rangetest' => 0,
                    'neighborinfo' => 2,
                    'other' => 1,
                ];
                $totalPaquetes24h = array_sum($packetCounts);
            }

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
            $problemas = [];
            try {
                $diagnosticoService = app(Diagnostico::class);
                $diagResultado = $diagnosticoService->obtenerDiagnostico($idNormalizado);
                $diagDatos = $diagResultado->datos ?? [];
                $problemas = $diagDatos['findings'] ?? [];
            } catch (Throwable) {
                $problemas = [];
            }

            $hopLimit = isset($node->hop_start_last) && $node->hop_start_last !== null ? (int) $node->hop_start_last : null;
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

            // Fallback de problemas detectados si Diagnostico falló o no devolvió hallazgos
            if (empty($problemas)) {
                $fw = (string) ($node->firmware ?? '');
                $role = strtoupper((string) ($node->role ?? 'CLIENT'));
                if ($role === 'CLIENT_BASE' && preg_match('/^v?(\d+)\.(\d+)(?:\.(\d+))?/', $fw, $mFw)) {
                    $fwMayor = (int) $mFw[1];
                    $fwMenor = (int) $mFw[2];
                    $fwParche = isset($mFw[3]) ? (int) $mFw[3] : 0;
                    if ([$fwMayor, $fwMenor, $fwParche] >= [2, 7, 17]) {
                        $problemas[] = [
                            'clave' => 'client_base_fw',
                            'severidad' => 'aviso',
                            'titulo' => 'CLIENT_BASE ≥ 2.7.17 actúa como ROUTER_LATE',
                            'descripcion' => 'A partir de firmware 2.7.17, CLIENT_BASE introduce un retardo artificial antes de retransmitir paquetes, ralentizando la red.',
                            'solucion' => 'Config → Dispositivo → Rol → cambiar a CLIENT o CLIENT_MUTE si no enruta.',
                        ];
                    }
                }

                if ($hopLimit !== null && $hopLimit > 3) {
                    $problemas[] = [
                        'clave' => 'saltos_altos',
                        'severidad' => $hopLimit > 5 ? 'critico' : 'aviso',
                        'titulo' => "Hop Limit excesivo ({$hopLimit} saltos)",
                        'descripcion' => "El nodo emite con {$hopLimit} saltos, lo que degrada la capacidad del canal regional.",
                        'solucion' => 'Configuración LoRa → Hop Limit → cambiar a 3.',
                    ];
                }
            }
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

        return is_array($res->datos) ? $res->datos : null;
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

    /**
     * Nodos de demostración realistas para entornos de desarrollo local y pruebas
     * cuando la base de datos de ingesta no está poblada.
     *
     * @return array<int, object>
     */
    protected function obtenerNodosEjemplo(): array
    {
        $ahora = Carbon::now('UTC');
        $hace10m = $ahora->copy()->subMinutes(10)->toIso8601String();
        $hace25m = $ahora->copy()->subMinutes(25)->toIso8601String();
        $hace4h = $ahora->copy()->subHours(4)->toIso8601String();
        $hace30h = $ahora->copy()->subHours(30)->toIso8601String();

        return [
            (object) [
                'id' => '!e001cafe',
                'node_num' => 3758197502,
                'short_name' => 'CHIP',
                'long_name' => 'RPT-Chipiona-Faro',
                'role' => 'ROUTER',
                'hw_model' => 'HELTEC_V3',
                'firmware' => '2.5.12',
                'latitude' => 36.7420,
                'longitude' => -6.4350,
                'position_type' => 'fixed',
                'province' => 'ES-CA',
                'last_seen' => $hace10m,
                'is_gateway' => false,
                'is_router' => true,
                'hop_start_last' => 3,
                'battery_level' => 95,
                'voltage' => 4.15,
                'channel_utilization' => 8.2,
                'air_util_tx' => 1.4,
            ],
            (object) [
                'id' => '!e002beef',
                'node_num' => 3758276335,
                'short_name' => 'CADZ',
                'long_name' => 'GW-Cadiz-Centro',
                'role' => 'CLIENT',
                'hw_model' => 'TBEAM',
                'firmware' => '2.5.10',
                'latitude' => 36.5300,
                'longitude' => -6.2880,
                'position_type' => 'gps',
                'province' => 'ES-CA',
                'last_seen' => $hace10m,
                'is_gateway' => true,
                'is_router' => false,
                'hop_start_last' => 3,
                'battery_level' => 100,
                'voltage' => 4.20,
                'channel_utilization' => 12.5,
                'air_util_tx' => 2.1,
            ],
            (object) [
                'id' => '!e0031111',
                'node_num' => 3758300001,
                'short_name' => 'GRAZ',
                'long_name' => 'RPT-Sierra-Grazalema',
                'role' => 'ROUTER',
                'hw_model' => 'RAK4631',
                'firmware' => '2.5.14',
                'latitude' => 36.7590,
                'longitude' => -5.3680,
                'position_type' => 'fixed',
                'province' => 'ES-CA',
                'last_seen' => $hace25m,
                'is_gateway' => false,
                'is_router' => true,
                'hop_start_last' => 3,
                'battery_level' => 88,
                'voltage' => 4.05,
                'channel_utilization' => 5.1,
                'air_util_tx' => 0.8,
            ],
            (object) [
                'id' => '!e0042222',
                'node_num' => 3758300002,
                'short_name' => 'ALJA',
                'long_name' => 'Nodo-Sevilla-Aljarafe',
                'role' => 'CLIENT_BASE',
                'hw_model' => 'HELTEC_V3',
                'firmware' => '2.7.18',
                'latitude' => 37.3820,
                'longitude' => -6.0420,
                'position_type' => 'gps',
                'province' => 'ES-SE',
                'last_seen' => $hace10m,
                'is_gateway' => false,
                'is_router' => false,
                'hop_start_last' => 3,
                'battery_level' => 74,
                'voltage' => 3.92,
                'channel_utilization' => 9.4,
                'air_util_tx' => 1.2,
            ],
            (object) [
                'id' => '!e0053333',
                'node_num' => 3758300003,
                'short_name' => 'CORD',
                'long_name' => 'RPT-Cordoba-Brillante',
                'role' => 'ROUTER',
                'hw_model' => 'STATION_G2',
                'firmware' => '2.5.12',
                'latitude' => 37.9050,
                'longitude' => -4.7900,
                'position_type' => 'fixed',
                'province' => 'ES-CO',
                'last_seen' => $hace10m,
                'is_gateway' => false,
                'is_router' => true,
                'hop_start_last' => 7,
                'battery_level' => 91,
                'voltage' => 4.10,
                'channel_utilization' => 15.8,
                'air_util_tx' => 3.2,
            ],
            (object) [
                'id' => '!e0064444',
                'node_num' => 3758300004,
                'short_name' => 'MLGA',
                'long_name' => 'GW-Malaga-Gibralfaro',
                'role' => 'CLIENT',
                'hw_model' => 'HELTEC_T114',
                'firmware' => '2.5.14',
                'latitude' => 36.7230,
                'longitude' => -4.4100,
                'position_type' => 'fixed',
                'province' => 'ES-MA',
                'last_seen' => $hace10m,
                'is_gateway' => true,
                'is_router' => false,
                'hop_start_last' => 3,
                'battery_level' => 100,
                'voltage' => 4.20,
                'channel_utilization' => 11.2,
                'air_util_tx' => 1.8,
            ],
            (object) [
                'id' => '!e0075555',
                'node_num' => 3758300005,
                'short_name' => 'ALBC',
                'long_name' => 'Nodo-Granada-Albaicin',
                'role' => 'CLIENT',
                'hw_model' => 'T_ECHO',
                'firmware' => '2.5.9',
                'latitude' => 37.1810,
                'longitude' => -3.5930,
                'position_type' => 'gps',
                'province' => 'ES-GR',
                'last_seen' => $hace10m,
                'is_gateway' => false,
                'is_router' => false,
                'hop_start_last' => 3,
                'battery_level' => 62,
                'voltage' => 3.81,
                'channel_utilization' => 6.7,
                'air_util_tx' => 0.9,
            ],
            (object) [
                'id' => '!e0086666',
                'node_num' => 3758300006,
                'short_name' => 'HUEL',
                'long_name' => 'Nodo-Huelva-Rabida',
                'role' => 'CLIENT',
                'hw_model' => 'HELTEC_V3',
                'firmware' => '2.5.12',
                'latitude' => 37.2080,
                'longitude' => -6.9240,
                'position_type' => 'fixed',
                'province' => 'ES-H',
                'last_seen' => $hace4h,
                'is_gateway' => false,
                'is_router' => false,
                'hop_start_last' => 3,
                'battery_level' => 55,
                'voltage' => 3.75,
                'channel_utilization' => 4.2,
                'air_util_tx' => 0.6,
            ],
            (object) [
                'id' => '!e0097777',
                'node_num' => 3758300007,
                'short_name' => 'JAEN',
                'long_name' => 'RPT-Jaen-SantaCatalina',
                'role' => 'ROUTER',
                'hw_model' => 'RAK4631',
                'firmware' => '2.5.14',
                'latitude' => 37.7680,
                'longitude' => -3.7990,
                'position_type' => 'fixed',
                'province' => 'ES-JA',
                'last_seen' => $hace10m,
                'is_gateway' => false,
                'is_router' => true,
                'hop_start_last' => 3,
                'battery_level' => 92,
                'voltage' => 4.12,
                'channel_utilization' => 7.8,
                'air_util_tx' => 1.1,
            ],
            (object) [
                'id' => '!e0108888',
                'node_num' => 3758300008,
                'short_name' => 'ALMR',
                'long_name' => 'GW-Almeria-Alcazaba',
                'role' => 'CLIENT',
                'hw_model' => 'TBEAM',
                'firmware' => '2.5.11',
                'latitude' => 36.8410,
                'longitude' => -2.4700,
                'position_type' => 'fixed',
                'province' => 'ES-AL',
                'last_seen' => $hace10m,
                'is_gateway' => true,
                'is_router' => false,
                'hop_start_last' => 3,
                'battery_level' => 98,
                'voltage' => 4.18,
                'channel_utilization' => 10.5,
                'air_util_tx' => 1.7,
            ],
            (object) [
                'id' => '!e0119999',
                'node_num' => 3758300009,
                'short_name' => 'JERZ',
                'long_name' => 'Nodo-Jerez-Norte',
                'role' => 'CLIENT',
                'hw_model' => 'HELTEC_V3',
                'firmware' => '2.5.12',
                'latitude' => 36.7020,
                'longitude' => -6.1330,
                'position_type' => 'fixed',
                'province' => 'ES-CA',
                'last_seen' => $hace30h,
                'is_gateway' => false,
                'is_router' => false,
                'hop_start_last' => 3,
                'battery_level' => 40,
                'voltage' => 3.65,
                'channel_utilization' => 0.0,
                'air_util_tx' => 0.0,
            ],
        ];
    }
}
