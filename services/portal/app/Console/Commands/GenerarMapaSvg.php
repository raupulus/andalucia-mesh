<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use RuntimeException;

class GenerarMapaSvg extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'portal:mapa';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Proyecta el GeoJSON de Andalucía y compila el componente Blade del mapa SVG (mapa-provincias.blade.php)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Iniciando proyección de límites provinciales de Andalucía...');

        // 1. Localizar el archivo GeoJSON
        $geojsonPath = base_path('../../services/ingesta/polygons/provincias-andalucia.geojson');
        if (!file_exists($geojsonPath)) {
            $geojsonPath = resource_path('geo/provincias-andalucia.geojson');
        }

        if (!file_exists($geojsonPath)) {
            $this->error("No se encontró el archivo GeoJSON en {$geojsonPath}");
            return Command::FAILURE;
        }

        $jsonRaw = (string) file_get_contents($geojsonPath);
        $geoData = json_decode($jsonRaw, true);
        if (!is_array($geoData) || empty($geoData['features'])) {
            throw new RuntimeException('GeoJSON corrupto o sin features.');
        }

        // 2. Parámetros de proyección equirrectangular (phi0 = 37.4°)
        $phi0 = deg2rad(37.4);
        $cosPhi0 = cos($phi0);

        $minLon = -7.55;
        $maxLon = -1.60;
        $minLat = 35.90;
        $maxLat = 38.75;

        $svgWidth = 1000.0;
        $padding = 20.0;
        $usableW = $svgWidth - (2 * $padding);
        $scale = $usableW / (($maxLon - $minLon) * $cosPhi0);
        $svgHeight = 620.0;

        $project = function (float $lon, float $lat) use ($minLon, $maxLat, $cosPhi0, $scale, $padding): array {
            $x = ($lon - $minLon) * $cosPhi0 * $scale + $padding;
            $y = ($maxLat - $lat) * $scale + $padding;
            return [round($x, 1), round($y, 1)];
        };

        // 3. Generar caminos SVG para cada provincia
        $paths = [];
        $names = [];

        foreach ($geoData['features'] as $f) {
            $props = $f['properties'];
            $code = $props['code'] ?? $props['ine'];
            $name = $props['name'];

            $geom = $f['geometry'];
            $coords = $geom['coordinates'];
            if ($geom['type'] === 'Polygon') {
                $coords = [$coords];
            }

            $dParts = [];
            foreach ($coords as $poly) {
                foreach ($poly as $ring) {
                    $ringPoints = [];
                    foreach ($ring as $pt) {
                        $p = $project((float) $pt[0], (float) $pt[1]);
                        $ringPoints[] = "{$p[0]},{$p[1]}";
                    }
                    if (!empty($ringPoints)) {
                        $dParts[] = 'M ' . implode(' ', $ringPoints) . ' Z';
                    }
                }
            }

            $paths[$code] = implode(' ', $dParts);
            $names[$code] = $name;
        }

        // 4. Generar la plantilla Blade
        $blade = $this->generarPlantillaBlade($paths, $names, $svgWidth, $svgHeight);
        $targetBlade = resource_path('views/components/mapa-provincias.blade.php');
        file_put_contents($targetBlade, $blade);

        $this->info("Componente generado con éxito en: {$targetBlade} (" . strlen($blade) . ' bytes)');

        return Command::SUCCESS;
    }

    /**
     * Construye el código Blade del componente SVG y su tabla accesible.
     *
     * @param array<string, string> $paths
     * @param array<string, string> $names
     */
    protected function generarPlantillaBlade(array $paths, array $names, float $w, float $h): string
    {
        $burbujas = config('proyecto.mapa.burbujas');

        $out = <<<'BLADE'
{{--
    Componente: x-mapa-provincias
    Mapa interactivo vectorial de Andalucía y tabla accesible con cálculo de saturación.
--}}
@props([
    'datos' => null, // Array o colección con estadísticas por provincia
    'ventana' => '7d',
])

@php
    $provinciasConfig = config('proyecto.provincias', []);
    $burbujasConfig = config('proyecto.mapa.burbujas', []);

    // Indexar datos recibidos por código de provincia
    $mapaDatos = [];
    if (!empty($datos['provinces'])) {
        foreach ($datos['provinces'] as $p) {
            $mapaDatos[$p['code']] = $p;
        }
    }

    $totalAndalucia = $datos['total_andalucia'] ?? 0;
    $saturacionAndalucia = $datos['andalucia_avg'] ?? null;
    $fueraAndalucia = $datos['outside_andalucia'] ?? 0;
    $actualizado = !empty($datos['generated_at']) ? \Carbon\Carbon::parse($datos['generated_at'])->diffForHumans() : 'recientemente';
@endphp

<div class="mapa-contenedor-completo" id="contenedor-mapa-andalucia">
    <!-- Encabezado de estadísticas del mapa -->
    <div class="mapa-cabecera" style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: flex-end; margin-bottom: 1.5rem; gap: 1rem;">
        <div>
            <div style="font-size: 2.25rem; font-weight: 700; color: var(--color-texto-1); line-height: 1.1;" id="mapa-total-nodos">
                {{ number_format((int) $totalAndalucia, 0, ',', '.') }}
            </div>
            <div style="font-size: 1.1rem; color: var(--color-texto-2); font-weight: 500;">
                nodos activos en Andalucía
            </div>
            <div style="font-size: 0.85rem; color: var(--color-texto-3); margin-top: 0.25rem;" id="mapa-actualizado-texto">
                <span id="mapa-ventana-label">{{ $ventana === '24h' ? 'Últimas 24 horas' : 'Últimos 7 días' }}</span> · actualizado {{ $actualizado }}
            </div>
        </div>

        <!-- Selector accesible de ventana temporal -->
        <div class="selector-ventana" role="group" aria-label="Ventana del recuento de nodos" style="display: flex; background: var(--color-superficie-sutil); padding: 4px; border-radius: var(--radio-sm); border: 1px solid var(--color-borde);">
            <button type="button" 
                    class="btn-ventana {{ $ventana === '7d' ? 'activo' : '' }}" 
                    data-ventana="7d"
                    aria-pressed="{{ $ventana === '7d' ? 'true' : 'false' }}">
                7 días
            </button>
            <button type="button" 
                    class="btn-ventana {{ $ventana === '24h' ? 'activo' : '' }}" 
                    data-ventana="24h"
                    aria-pressed="{{ $ventana === '24h' ? 'true' : 'false' }}">
                24 horas
            </button>
        </div>
    </div>

    <!-- Contenedor del Mapa SVG con Tooltip Emergente -->
    <div style="position: relative; width: 100%; border-radius: var(--radio-md); border: 1px solid var(--color-borde); background: var(--color-superficie); padding: 1rem; overflow: hidden;">
        
        <svg viewBox="0 0 1000 620" 
             class="mapa-andalucia-svg" 
             role="region" 
             aria-label="Mapa de saturación del canal y recuento de nodos por provincia en Andalucía"
             style="width: 100%; height: auto; display: block;">
            
            <defs>
                <!-- Trama diagonal accesible para provincias sin datos de telemetría -->
                <pattern id="trama-sin-datos" width="10" height="10" patternUnits="userSpaceOnUse" patternTransform="rotate(45)">
                    <rect width="10" height="10" fill="var(--color-mapa-gris)" />
                    <line x1="0" y1="0" x2="0" y2="10" stroke="rgba(255,255,255,0.4)" stroke-width="3" />
                </pattern>
            </defs>

            <!-- Trazados provinciales -->
            <g class="capa-provincias">
BLADE;

        foreach ($names as $code => $name) {
            $pathD = $paths[$code] ?? '';
            $bX = $burbujas[$code]['x'] ?? 500;
            $bY = $burbujas[$code]['y'] ?? 300;

            $out .= "\n                <!-- {$name} ({$code}) -->\n";
            $out .= "                <g class=\"provincia-grupo\" id=\"prov-{$code}\" data-code=\"{$code}\" data-name=\"{$name}\">\n";
            $out .= "                    <path class=\"provincia-path nivel-{{ \$mapaDatos['{$code}']['level'] ?? 'nodata' }}\"\n";
            $out .= "                          d=\"{$pathD}\"\n";
            $out .= "                          tabindex=\"0\"\n";
            $out .= "                          role=\"button\"\n";
            $out .= "                          aria-haspopup=\"dialog\"\n";
            $out .= "                          aria-label=\"{{ \$mapaDatos['{$code}']['name'] ?? '{$name}' }}: {{ \$mapaDatos['{$code}']['nodes'] ?? 0 }} nodos\"\n";
            $out .= "                          data-code=\"{$code}\" />\n";
            $out .= "                    <g class=\"burbuja-grupo " . ($code === 'ES-CA' ? 'burbuja-cadiz' : '') . "\" transform=\"translate({$bX}, {$bY})\" pointer-events=\"none\">\n";
            $out .= "                        <circle r=\"22\" class=\"burbuja-fondo\" />\n";
            $out .= "                        <text y=\"6\" text-anchor=\"middle\" class=\"burbuja-texto\">{{ \$mapaDatos['{$code}']['nodes'] ?? '0' }}</text>\n";
            $out .= "                    </g>\n";
            $out .= "                </g>\n";
        }

        $out .= <<<'BLADE'
            </g>
        </svg>

        <!-- Panel flotante (Tooltip) para detalles de provincia -->
        <div id="mapa-panel-detalle" 
             class="mapa-panel-detalle" 
             role="dialog" 
             aria-hidden="true" 
             style="display: none; position: absolute; pointer-events: none; z-index: 20;">
            <div class="tarjeta" style="padding: 1rem; width: 280px; box-shadow: var(--sombra-lg); border-color: var(--color-borde);">
                <div style="font-weight: 700; font-size: 1.15rem; color: var(--color-texto-1); margin-bottom: 0.25rem;" id="panel-provincia-nombre">
                    Cádiz
                </div>
                <div style="font-size: 0.95rem; color: var(--color-texto-2); margin-bottom: 0.75rem;" id="panel-provincia-recuento">
                    0 nodos (0% del total)
                </div>
                <div style="border-top: 1px solid var(--color-borde); padding-top: 0.5rem; font-size: 0.85rem;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.25rem;">
                        <span style="color: var(--color-texto-2);">Saturación estimada:</span>
                        <strong id="panel-provincia-saturacion">-</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.25rem;">
                        <span style="color: var(--color-texto-2);">Carga máxima:</span>
                        <span id="panel-provincia-max">-</span>
                    </div>
                    <div style="color: var(--color-texto-3); font-size: 0.78rem; margin-top: 0.5rem;" id="panel-provincia-detalle-nodos">
                        Sin datos de telemetría recientes
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Leyenda de colores y cálculo de saturación -->
    <div class="mapa-leyenda" style="margin-top: 1.5rem; background: var(--color-superficie-sutil); border: 1px solid var(--color-borde); border-radius: var(--radio-md); padding: 1.25rem;">
        <div style="font-weight: 600; font-size: 0.95rem; margin-bottom: 0.75rem; color: var(--color-texto-1);">
            Leyenda de ocupación del espectro (saturación):
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 0.75rem; font-size: 0.88rem; margin-bottom: 1.25rem;">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <span style="display: inline-block; width: 16px; height: 16px; border-radius: 4px; background: var(--color-mapa-verde);"></span>
                <span>≤ 20 %: canal holgado</span>
            </div>
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <span style="display: inline-block; width: 16px; height: 16px; border-radius: 4px; background: var(--color-mapa-naranja);"></span>
                <span>20 % – 40 %: canal cargado</span>
            </div>
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <span style="display: inline-block; width: 16px; height: 16px; border-radius: 4px; background: var(--color-mapa-rojo);"></span>
                <span>≥ 40 %: canal saturado</span>
            </div>
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <span style="display: inline-block; width: 16px; height: 16px; border-radius: 4px; background: var(--color-mapa-gris); border: 1px dashed var(--color-borde);"></span>
                <span>Sin datos en 12 h</span>
            </div>
        </div>
        <p style="font-size: 0.82rem; color: var(--color-texto-3); line-height: 1.5; margin-bottom: 0;">
            <strong>Cómo calculamos la saturación de cada provincia:</strong> con las coordenadas buscamos todos los nodos que están dentro de la provincia; sacamos la media de la ocupación del canal de los routers por un lado y la de todos los nodos CLIENT y CLIENT_BASE juntos por otro; y sumamos con pesos: routers 60 % y CLIENT con CLIENT_BASE 40 % (estos dos van juntos en una sola media). Los CLIENT_MUTE no cuentan porque suelen estar en interior o peor comunicados y su medida sale más baja de lo real. Se usa el último dato de cada nodo en las 12 últimas horas.
        </p>
    </div>

    <!-- Tabla accesible para lectores de pantalla y consulta alternativa -->
    <details class="tabla-accesible-contenedor" style="margin-top: 1.5rem;" open>
        <summary style="font-weight: 600; cursor: pointer; color: var(--color-texto-1); padding: 0.5rem 0;">
            Tabla de nodos y carga por provincia (alternativa accesible)
        </summary>
        <div style="overflow-x: auto; margin-top: 0.75rem;">
            <table class="tabla" style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--color-borde); text-align: left;">
                        <th style="padding: 0.6rem;">Provincia</th>
                        <th style="padding: 0.6rem; text-align: right;">Nodos</th>
                        <th style="padding: 0.6rem; text-align: right;">% Total</th>
                        <th style="padding: 0.6rem; text-align: right;">Saturación</th>
                        <th style="padding: 0.6rem;">Estado</th>
                    </tr>
                </thead>
                <tbody id="mapa-tabla-cuerpo">
                    @foreach($provinciasConfig as $codigo => $nombre)
                        @php
                            $pInfo = $mapaDatos[$codigo] ?? null;
                            $nodos = $pInfo['nodes'] ?? 0;
                            $pct = $totalAndalucia > 0 ? round(($nodos / $totalAndalucia) * 100, 1) : 0;
                            $avg = $pInfo['avg'] ?? null;
                            $level = $pInfo['level'] ?? 'nodata';
                            $estadoTexto = match($level) {
                                'green' => 'Holgado',
                                'orange' => 'Cargado',
                                'red' => 'Saturado',
                                default => 'Sin datos',
                            };
                            $chipTipo = match($level) {
                                'green' => 'correcto',
                                'orange' => 'aviso',
                                'red' => 'critico',
                                default => 'neutro',
                            };
                        @endphp
                        <tr style="border-bottom: 1px solid var(--color-borde);">
                            <td style="padding: 0.6rem; font-weight: 500;">
                                {{ $nombre }} {{ $codigo === 'ES-CA' ? '(Foco)' : '' }}
                            </td>
                            <td style="padding: 0.6rem; text-align: right; font-variant-numeric: tabular-nums;">
                                {{ number_format((int) $nodos, 0, ',', '.') }}
                            </td>
                            <td style="padding: 0.6rem; text-align: right; font-variant-numeric: tabular-nums;">
                                {{ $pct }} %
                            </td>
                            <td style="padding: 0.6rem; text-align: right; font-variant-numeric: tabular-nums;">
                                {{ $avg !== null ? $avg . ' %' : '—' }}
                            </td>
                            <td style="padding: 0.6rem;">
                                <x-chip-estado :tipo="$chipTipo" :texto="$estadoTexto" />
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($fueraAndalucia > 0)
            <div style="font-size: 0.8rem; color: var(--color-texto-3); margin-top: 0.5rem;">
                * Además, {{ $fueraAndalucia }} nodos oídos desde nuestros gateways se encuentran situados fuera de Andalucía.
            </div>
        @endif
    </details>
</div>
BLADE;

        return $out;
    }
}
