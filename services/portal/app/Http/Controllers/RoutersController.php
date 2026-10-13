<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Data\Ingest\MapaData;
use App\Data\Ingest\Resumen;
use App\Data\Ingest\Routers;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Throwable;

/**
 * Controlador de la vista pública de repetidores y routers de infraestructura.
 *
 * Muestra el cuadro de supervisión en tiempo real con selector de ámbito
 * territorial (Andalucía, España, Ambos), tarjetas de métricas ejecutivas
 * y tabla detallada de telemetría y salud de cada nodo agrupada por provincias.
 */
class RoutersController extends Controller
{
    /**
     * Catálogo de provincias españolas conforme a ISO 3166-2:ES.
     *
     * @var array<string, string>
     */
    public const PROVINCIAS_ESPANA = [
        // 8 provincias andaluzas (orden canónico / alfabético)
        'ES-AL' => 'Almería',
        'ES-CA' => 'Cádiz',
        'ES-CO' => 'Córdoba',
        'ES-GR' => 'Granada',
        'ES-H' => 'Huelva',
        'ES-HU' => 'Huelva',
        'ES-J' => 'Jaén',
        'ES-JA' => 'Jaén',
        'ES-MA' => 'Málaga',
        'ES-SE' => 'Sevilla',
        // Resto de España
        'ES-AB' => 'Albacete',
        'ES-A' => 'Alicante',
        'ES-AV' => 'Ávila',
        'ES-B' => 'Barcelona',
        'ES-BA' => 'Badajoz',
        'ES-BI' => 'Bizkaia',
        'ES-BU' => 'Burgos',
        'ES-C' => 'A Coruña',
        'ES-CC' => 'Cáceres',
        'ES-CE' => 'Ceuta',
        'ES-CR' => 'Ciudad Real',
        'ES-CS' => 'Castellón',
        'ES-CU' => 'Cuenca',
        'ES-GC' => 'Las Palmas',
        'ES-GI' => 'Girona',
        'ES-GU' => 'Guadalajara',
        'ES-IB' => 'Illes Balears',
        'ES-PM' => 'Illes Balears',
        'ES-L' => 'Lleida',
        'ES-LE' => 'León',
        'ES-LO' => 'La Rioja',
        'ES-LU' => 'Lugo',
        'ES-M' => 'Madrid',
        'ES-ML' => 'Melilla',
        'ES-MU' => 'Murcia',
        'ES-NA' => 'Navarra',
        'ES-O' => 'Asturias',
        'ES-OR' => 'Ourense',
        'ES-P' => 'Palencia',
        'ES-PO' => 'Pontevedra',
        'ES-S' => 'Cantabria',
        'ES-SA' => 'Salamanca',
        'ES-SG' => 'Segovia',
        'ES-SO' => 'Soria',
        'ES-SS' => 'Gipuzkoa',
        'ES-T' => 'Tarragona',
        'ES-TE' => 'Teruel',
        'ES-TF' => 'Santa Cruz de Tenerife',
        'ES-TO' => 'Toledo',
        'ES-V' => 'Valencia',
        'ES-VA' => 'Valladolid',
        'ES-VI' => 'Álava',
        'ES-Z' => 'Zaragoza',
        'ES-ZA' => 'Zamora',
    ];

    /**
     * Catálogo en memoria de polígonos provinciales de España para georreferenciación inversa.
     *
     * @var list<array{code: string, name: string, ine: string, bbox: list<float>, rings: list<list<list<float>>>}>|null
     */
    protected static ?array $provinciasGeo = null;

    /**
     * Resuelve el código y nombre de provincia española a partir de coordenadas GPS.
     *
     * @return array{code: string, name: string}|null
     */
    public static function resolverProvinciaPorCoordenadas(float $lat, float $lon): ?array
    {
        if (self::$provinciasGeo === null) {
            $path = resource_path('geo/provincias-espana.json');
            if (file_exists($path)) {
                $raw = file_get_contents($path);
                self::$provinciasGeo = $raw ? json_decode($raw, true) : [];
            } else {
                self::$provinciasGeo = [];
            }
        }

        foreach (self::$provinciasGeo as $p) {
            $bbox = $p['bbox'];
            if ($lon < $bbox[0] || $lat < $bbox[1] || $lon > $bbox[2] || $lat > $bbox[3]) {
                continue;
            }

            foreach ($p['rings'] as $ring) {
                $inside = false;
                $count = count($ring);
                for ($i = 0, $j = $count - 1; $i < $count; $j = $i++) {
                    $xi = $ring[$i][0];
                    $yi = $ring[$i][1];
                    $xj = $ring[$j][0];
                    $yj = $ring[$j][1];
                    $intersect = (($yi > $lat) !== ($yj > $lat)) && ($lon < ($xj - $xi) * ($lat - $yi) / ($yj - $yi) + $xi);
                    if ($intersect) {
                        $inside = ! $inside;
                    }
                }

                if ($inside) {
                    return [
                        'code' => $p['code'],
                        'name' => $p['name'],
                    ];
                }
            }
        }

        return null;
    }

    /**
     * Resuelve el nombre, código normalizado y slug de una provincia.
     *
     * @return array{code: string, name: string, slug: string, is_andalucia: bool}
     */
    public static function resolverInfoProvincia(?string $provinciaRaw, ?float $lat = null, ?float $lon = null): array
    {
        $code = strtoupper(trim((string) $provinciaRaw));
        $esAndaluza = in_array($code, Routers::PROVINCIAS_ANDALUCIA, true);

        // Si ya es una provincia andaluza oficial conocida, devolverla directamente
        if ($esAndaluza && isset(self::PROVINCIAS_ESPANA[$code])) {
            $name = self::PROVINCIAS_ESPANA[$code];

            return [
                'code' => $code,
                'name' => $name,
                'slug' => Str::slug($name),
                'is_andalucia' => true,
            ];
        }

        // Si es de otra provincia española conocida por su código ISO
        if ($code !== 'FUERA' && $code !== '' && isset(self::PROVINCIAS_ESPANA[$code])) {
            $name = self::PROVINCIAS_ESPANA[$code];

            return [
                'code' => $code,
                'name' => $name,
                'slug' => Str::slug($name),
                'is_andalucia' => false,
            ];
        }

        // Si tiene coordenadas GPS válidas, resolver su provincia geográfica real en España
        if ($lat !== null && $lon !== null && ($lat !== 0.0 || $lon !== 0.0)) {
            $geoProv = self::resolverProvinciaPorCoordenadas($lat, $lon);
            if ($geoProv !== null) {
                $geoCode = $geoProv['code'];
                $geoName = self::PROVINCIAS_ESPANA[$geoCode] ?? $geoProv['name'];
                $esAnd = in_array($geoCode, Routers::PROVINCIAS_ANDALUCIA, true);

                return [
                    'code' => $geoCode,
                    'name' => $geoName,
                    'slug' => Str::slug($geoName),
                    'is_andalucia' => $esAnd,
                ];
            }
        }

        if ($code === 'FUERA') {
            return [
                'code' => 'FUERA',
                'name' => 'Fuera de España',
                'slug' => 'fuera',
                'is_andalucia' => false,
            ];
        }

        if ($code === '' || $code === 'UNKNOWN') {
            return [
                'code' => 'DESCONOCIDA',
                'name' => 'Desconocida',
                'slug' => 'desconocida',
                'is_andalucia' => false,
            ];
        }

        // Si ya viene con el nombre textual de la provincia (ej. 'Cádiz')
        foreach (self::PROVINCIAS_ESPANA as $c => $n) {
            if (mb_strtolower($code) === mb_strtolower($n)) {
                return [
                    'code' => $c,
                    'name' => $n,
                    'slug' => Str::slug($n),
                    'is_andalucia' => in_array($c, Routers::PROVINCIAS_ANDALUCIA, true),
                ];
            }
        }

        return [
            'code' => $code,
            'name' => self::PROVINCIAS_ESPANA[$code] ?? $code,
            'slug' => Str::slug(self::PROVINCIAS_ESPANA[$code] ?? $code),
            'is_andalucia' => false,
        ];
    }

    public function __construct(
        protected Routers $routers,
        protected Resumen $resumen,
    ) {}

    /**
     * Prepara la colección de routers y su agrupación provincial para la tabla de supervisión.
     * Compartido entre el controlador público y el widget del cuadro de mandos de administración.
     *
     * @return array{
     *     routers: array<int, array<string, mixed>>,
     *     routersPorProvincia: array<string, array<string, mixed>>,
     *     total: int,
     *     enAlerta: int,
     *     errorFuente: ?string,
     *     ambito: string,
     * }
     */
    public static function prepararDatosSupervision(
        string $ambito = 'andalucia',
        string $sort = 'battery_asc',
        ?Routers $routersService = null,
    ): array {
        if (! in_array($ambito, ['andalucia', 'espana', 'ambos'], true)) {
            $ambito = 'andalucia';
        }

        if (! in_array($sort, ['battery_asc', 'last_seen_desc', 'chutil_desc'], true)) {
            $sort = 'battery_asc';
        }

        $routersService = $routersService ?? app(Routers::class);

        // Cargar mapa de coordenadas en caso de necesitar georreferenciación inversa
        $mapaCoords = [];
        $cacheNodesPath = public_path(MapaData::CACHE_DIR.'/nodes.json');
        if (file_exists($cacheNodesPath)) {
            try {
                $rawNodes = json_decode((string) file_get_contents($cacheNodesPath), true);
                if (is_array($rawNodes)) {
                    foreach ($rawNodes as $mn) {
                        if (isset($mn['id'], $mn['lat'], $mn['lon'])) {
                            $mapaCoords[$mn['id']] = ['lat' => (float) $mn['lat'], 'lon' => (float) $mn['lon']];
                        }
                    }
                }
            } catch (Throwable) {
                $mapaCoords = [];
            }
        }

        // Obtener listado de routers según el ámbito activo
        $items = [];
        $totalRouters = 0;
        $enAlerta = 0;
        $errorFuente = null;

        try {
            $resRouters = $routersService->obtenerResultado(sort: $sort, ambito: $ambito);
            $rawItems = $resRouters->datos['items'] ?? [];
            $totalRouters = count($rawItems);

            foreach ($rawItems as $r) {
                $lastSeenDt = ! empty($r['last_seen']) ? Carbon::parse($r['last_seen']) : null;
                $minutosDesconectado = $lastSeenDt ? now()->diffInMinutes($lastSeenDt) : 9999;
                $estaOnline = $minutosDesconectado <= 60;

                $lat = $r['latitude'] ?? ($mapaCoords[$r['id']]['lat'] ?? null);
                $lon = $r['longitude'] ?? ($mapaCoords[$r['id']]['lon'] ?? null);

                $batteryLevel = $r['battery_level'] !== null ? (int) $r['battery_level'] : null;
                $powered = (bool) ($r['powered'] ?? false);
                $voltage = $r['voltage'] !== null ? (float) $r['voltage'] : null;
                $chutil = $r['channel_utilization'] !== null ? (float) $r['channel_utilization'] : null;
                $airTx = $r['air_util_tx'] !== null ? (float) $r['air_util_tx'] : null;

                // Color y estado de batería
                $bateriaColor = 'verde';
                if ($powered) {
                    $bateriaColor = 'red';
                } elseif ($batteryLevel !== null) {
                    if ($batteryLevel < 20) {
                        $bateriaColor = 'rojo';
                        $enAlerta++;
                    } elseif ($batteryLevel < 50) {
                        $bateriaColor = 'amarillo';
                    } else {
                        $bateriaColor = 'verde';
                    }
                } else {
                    $bateriaColor = 'desconocido';
                }

                // Color de presión local ChUtil
                $chutilColor = 'verde';
                if ($chutil !== null) {
                    if ($chutil >= 40.0) {
                        $chutilColor = 'rojo';
                        $enAlerta++;
                    } elseif ($chutil >= 20.0) {
                        $chutilColor = 'amarillo';
                    }
                }

                // Color de emisión TX
                $txColor = 'verde';
                if ($airTx !== null) {
                    if ($airTx >= 8.0) {
                        $txColor = 'rojo';
                    } elseif ($airTx >= 3.0) {
                        $txColor = 'amarillo';
                    }
                }

                $items[] = array_merge($r, [
                    'latitude' => $lat,
                    'longitude' => $lon,
                    'esta_online' => $estaOnline,
                    'hace' => $lastSeenDt ? $lastSeenDt->diffForHumans() : __('admin.widgets.routers.offline'),
                    'bateria_color' => $bateriaColor,
                    'chutil_color' => $chutilColor,
                    'tx_color' => $txColor,
                    'battery_level' => $batteryLevel,
                    'voltage' => $voltage,
                    'chutil' => $chutil,
                    'air_tx' => $airTx,
                    'powered' => $powered,
                ]);
            }
        } catch (Throwable $e) {
            $errorFuente = __('admin.widgets.routers.error_source');
            $items = [];
            $totalRouters = 0;
        }

        // Agrupar y ordenar routers por provincia
        $gruposProvincia = [];
        $ordenAndalucia = array_flip(Routers::PROVINCIAS_ANDALUCIA);

        foreach ($items as $r) {
            $lat = $r['latitude'] ?? null;
            $lon = $r['longitude'] ?? null;
            $infoProv = self::resolverInfoProvincia($r['province'] ?? null, $lat, $lon);
            $pCode = $infoProv['code'];

            if (! isset($gruposProvincia[$pCode])) {
                $gruposProvincia[$pCode] = [
                    'code' => $infoProv['code'],
                    'name' => $infoProv['name'],
                    'slug' => $infoProv['slug'],
                    'is_andalucia' => $infoProv['is_andalucia'],
                    'count' => 0,
                    'online_count' => 0,
                    'alert_count' => 0,
                    'routers' => [],
                ];
            }

            $r['province_code'] = $infoProv['code'];
            $r['province_name'] = $infoProv['name'];
            $r['province_slug'] = $infoProv['slug'];
            $r['is_andalucia'] = $infoProv['is_andalucia'];

            $gruposProvincia[$pCode]['count']++;
            if ($r['esta_online']) {
                $gruposProvincia[$pCode]['online_count']++;
            }
            if ($r['bateria_color'] === 'rojo' || $r['chutil_color'] === 'rojo' || $r['tx_color'] === 'rojo') {
                $gruposProvincia[$pCode]['alert_count']++;
            }
            $gruposProvincia[$pCode]['routers'][] = $r;
        }

        uasort($gruposProvincia, function (array $a, array $b) use ($ordenAndalucia): int {
            $esAndA = $a['is_andalucia'];
            $esAndB = $b['is_andalucia'];

            if ($esAndA && ! $esAndB) {
                return -1;
            }
            if (! $esAndA && $esAndB) {
                return 1;
            }

            if ($esAndA && $esAndB) {
                $posA = $ordenAndalucia[$a['code']] ?? 99;
                $posB = $ordenAndalucia[$b['code']] ?? 99;
                if ($posA !== $posB) {
                    return $posA <=> $posB;
                }
            }

            $esFueraA = in_array($a['code'], ['FUERA', 'DESCONOCIDA'], true);
            $esFueraB = in_array($b['code'], ['FUERA', 'DESCONOCIDA'], true);

            if ($esFueraA && ! $esFueraB) {
                return 1;
            }
            if (! $esFueraA && $esFueraB) {
                return -1;
            }

            $reemplazos = ['Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U', 'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u'];
            $nombreA = strtr($a['name'], $reemplazos);
            $nombreB = strtr($b['name'], $reemplazos);

            return strcasecmp($nombreA, $nombreB);
        });

        // Aplanar los routers en el orden provincial resultante
        $itemsOrdenados = [];
        foreach ($gruposProvincia as $grupo) {
            foreach ($grupo['routers'] as $routerItem) {
                $itemsOrdenados[] = $routerItem;
            }
        }

        return [
            'routers' => $itemsOrdenados,
            'routersPorProvincia' => $gruposProvincia,
            'total' => $totalRouters,
            'enAlerta' => $enAlerta,
            'errorFuente' => $errorFuente,
            'ambito' => $ambito,
        ];
    }

    public function index(Request $request): View
    {
        // 1. Resolver ámbito territorial (por defecto Andalucía)
        $ambito = (string) $request->query('ambito', 'andalucia');
        if (! in_array($ambito, ['andalucia', 'espana', 'ambos'], true)) {
            $ambito = 'andalucia';
        }

        // 2. Resolver ordenación (por defecto menor batería primero)
        $sort = (string) $request->query('sort', 'battery_asc');
        if (! in_array($sort, ['battery_asc', 'last_seen_desc', 'chutil_desc'], true)) {
            $sort = 'battery_asc';
        }

        // 3. Recuentos para el selector de ámbito
        $conteoAndalucia = 0;
        $conteoEspana = 0;
        try {
            $conteoAndalucia = (int) ($this->routers->obtenerResultado(ambito: 'andalucia')->datos['count'] ?? 0);
            $conteoEspana = (int) ($this->routers->obtenerResultado(ambito: 'espana')->datos['count'] ?? 0);
        } catch (Throwable) {
            $conteoAndalucia = 0;
            $conteoEspana = 0;
        }

        // 4. Preparar datos consolidados de routers y agrupación provincial
        $datosSupervision = self::prepararDatosSupervision($ambito, $sort, $this->routers);
        $items = $datosSupervision['routers'];
        $gruposProvincia = $datosSupervision['routersPorProvincia'];
        $totalRouters = $datosSupervision['total'];
        $enAlerta = $datosSupervision['enAlerta'];
        $errorFuente = $datosSupervision['errorFuente'];

        // Filtro opcional por provincia
        $filtroProvincia = trim((string) $request->query('provincia', ''));

        // 6. Métricas de resumen para las 4 tarjetas KPI
        $resumenData = null;
        try {
            $resumenData = (array) $this->resumen->obtenerResultado()->datos;
        } catch (Throwable) {
            $resumenData = null;
        }

        // KPI 1: Presión del Aire (ChUtil)
        $chUtilAvg = $resumenData['channel_utilization']['andalucia_avg'] ?? null;
        $provincias = $resumenData['channel_utilization']['provinces'] ?? [];
        if ($ambito === 'andalucia') {
            $provincias = array_values(array_filter($provincias, fn ($p) => in_array($p['code'] ?? '', Routers::PROVINCIAS_ANDALUCIA, true)));
        }

        $picoProvincia = null;
        $picoMax = 0.0;
        foreach ($provincias as $p) {
            if (($p['max'] ?? 0) > $picoMax) {
                $picoMax = (float) $p['max'];
                $picoProvincia = (string) ($p['name'] ?? $p['code']);
            }
        }

        $kpiChutil = [
            'val' => $chUtilAvg !== null ? round((float) $chUtilAvg, 1).'%' : __('admin.widgets.stats.no_data'),
            'desc' => __('admin.widgets.stats.chutil_waiting'),
            'color' => 'gray',
            'chart' => ! empty($provincias)
                ? array_map(fn ($p) => (float) ($p['avg'] ?? 0), array_slice($provincias, 0, 8))
                : [2.0, 3.5, 4.0, 3.8, 5.0, 4.2, $chUtilAvg ? (float) $chUtilAvg : 4.0],
        ];

        if ($chUtilAvg !== null) {
            if ($chUtilAvg < 20.0) {
                $kpiChutil['color'] = 'success';
                $kpiChutil['desc'] = __('admin.widgets.stats.chutil_light');
            } elseif ($chUtilAvg < 40.0) {
                $kpiChutil['color'] = 'warning';
                $kpiChutil['desc'] = __('admin.widgets.stats.chutil_moderate');
            } else {
                $kpiChutil['color'] = 'danger';
                $kpiChutil['desc'] = __('admin.widgets.stats.chutil_heavy');
            }
            if ($picoProvincia && $picoMax > 0) {
                $kpiChutil['desc'] .= __('admin.widgets.stats.chutil_peak', ['peak' => $picoMax, 'province' => $picoProvincia]);
            }
        }

        // KPI 2: Saturación TX de Repetidores
        $txList = array_filter(array_map(fn ($r) => $r['air_tx'] ?? null, $items), fn ($v) => $v !== null);
        $avgTx = ! empty($txList) ? round(array_sum($txList) / count($txList), 1) : null;

        $kpiTx = [
            'val' => $avgTx !== null ? "{$avgTx}% TX" : __('admin.widgets.stats.no_data'),
            'desc' => __('admin.widgets.stats.tx_desc_none'),
            'color' => 'gray',
            'chart' => ! empty($txList) ? array_values(array_map('floatval', $txList)) : [1.0, 1.5, 1.2, 2.0, 1.4, 1.1],
        ];

        if ($avgTx !== null) {
            if ($avgTx < 3.0) {
                $kpiTx['color'] = 'success';
                $kpiTx['desc'] = __('admin.widgets.stats.tx_optimal');
            } elseif ($avgTx < 8.0) {
                $kpiTx['color'] = 'warning';
                $kpiTx['desc'] = __('admin.widgets.stats.tx_moderate');
            } else {
                $kpiTx['color'] = 'danger';
                $kpiTx['desc'] = __('admin.widgets.stats.tx_high');
            }
        }

        // KPI 3: Infraestructura y Baterías
        $enRed = 0;
        $enBateria = 0;
        $criticos = 0;
        $bateriasValidas = [];

        foreach ($items as $r) {
            if ($r['powered'] ?? false) {
                $enRed++;
            } else {
                $enBateria++;
                $bat = $r['battery_level'] ?? null;
                if ($bat !== null) {
                    $bateriasValidas[] = $bat;
                    if ($bat < 20) {
                        $criticos++;
                    }
                }
            }
        }

        $kpiEnergia = [
            'val' => trans_choice('admin.widgets.stats.routers_count', $totalRouters, ['count' => $totalRouters]),
            'desc' => __('admin.widgets.stats.battery_none'),
            'color' => 'gray',
            'criticos' => $criticos,
        ];

        if ($criticos > 0) {
            $kpiEnergia['color'] = 'danger';
            $kpiEnergia['desc'] = __('admin.widgets.stats.battery_critical', ['count' => $criticos]);
        } elseif ($enBateria > 0) {
            $batMin = ! empty($bateriasValidas) ? min($bateriasValidas) : 100;
            $kpiEnergia['color'] = $batMin < 40 ? 'warning' : 'success';
            $kpiEnergia['desc'] = __('admin.widgets.stats.battery_mixed', ['grid' => $enRed, 'battery' => $enBateria, 'min' => $batMin]);
        } elseif ($totalRouters > 0) {
            $kpiEnergia['color'] = 'success';
            $kpiEnergia['desc'] = __('admin.widgets.stats.battery_all_grid');
        }

        // KPI 4: Tráfico y Pasarelas (24h)
        $paquetesHora = (int) ($resumenData['packets_last_hour'] ?? 0);
        $gateways = (int) ($resumenData['gateways_publishing'] ?? 0);
        $nodos24h = (int) ($resumenData['nodes_active_24h'] ?? 0);

        $kpiTrafico = [
            'val' => number_format($paquetesHora, 0, ',', '.').' '.__('admin.widgets.stats.packets_per_hour'),
            'desc' => __('admin.widgets.stats.traffic_desc', ['gateways' => $gateways, 'nodes' => $nodos24h]),
            'color' => $gateways > 0 ? 'info' : 'gray',
        ];

        return view('routers', [
            'ambito' => $ambito,
            'sort' => $sort,
            'conteoAndalucia' => $conteoAndalucia,
            'conteoEspana' => $conteoEspana,
            'routers' => $items,
            'routersPorProvincia' => $gruposProvincia,
            'filtroProvincia' => $filtroProvincia,
            'total' => $totalRouters,
            'enAlerta' => $enAlerta,
            'errorFuente' => $errorFuente,
            'kpiChutil' => $kpiChutil,
            'kpiTx' => $kpiTx,
            'kpiEnergia' => $kpiEnergia,
            'kpiTrafico' => $kpiTrafico,
        ]);
    }
}
