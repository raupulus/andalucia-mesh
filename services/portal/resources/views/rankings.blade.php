<x-layout title="Rankings y Estadísticas de Tráfico" description="Estadísticas de ocupación de canal, consumo de espectro y rankings de nodos en Andalucía Mesh.">
    <div class="contenedor seccion">
        <!-- Encabezado -->
        <header style="margin-bottom: 2.5rem;">
            <h1 style="font-size: 2.25rem; font-weight: 800; color: var(--color-texto-1); margin-bottom: 0.5rem;">
                Rankings y Actividad de la Red
            </h1>
            <p class="lead" style="margin-bottom: 1.5rem;">
                Monitorización del consumo de espectro, distribución del tráfico y rendimiento de enlaces directos.
            </p>

            <!-- Selector de periodo -->
            <div role="group" aria-label="Periodo de las estadísticas" style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                <a href="/rankings?period=day&which=current" 
                   class="btn {{ ($period === 'day' && $which === 'current') ? 'btn-primario' : 'btn-secundario' }}">
                    Hoy
                </a>
                <a href="/rankings?period=day&which=previous" 
                   class="btn {{ ($period === 'day' && $which === 'previous') ? 'btn-primario' : 'btn-secundario' }}">
                    Ayer
                </a>
                <a href="/rankings?period=week&which=current" 
                   class="btn {{ ($period === 'week' && $which === 'current') ? 'btn-primario' : 'btn-secundario' }}">
                    Últimos 7 días
                </a>
                <a href="/rankings?period=month&which=current" 
                   class="btn {{ ($period === 'month' && $which === 'current') ? 'btn-primario' : 'btn-secundario' }}">
                    Últimos 30 días
                </a>
            </div>
        </header>

        <!-- 1. Distribución del tráfico (Traffic Mix) -->
        <section class="tarjeta" style="padding: 1.75rem; margin-bottom: 2.5rem;">
            <h2 style="font-size: 1.4rem; font-weight: 700; color: var(--color-texto-1); margin-bottom: 0.5rem;">
                Distribución de Tráfico (Mix de Paquetes)
            </h2>
            <p style="color: var(--color-texto-2); font-size: 0.95rem; margin-bottom: 1.5rem;">
                Total paquetes capturados: <strong>{{ number_format((int) ($trafico['total_packets'] ?? 0), 0, ',', '.') }}</strong> · 
                Tiempo total en el aire: <strong>{{ number_format((float) ($trafico['total_airtime_s'] ?? 0), 1, ',', '.') }} s</strong>
            </p>

            <!-- Barra gráfica de proporciones -->
            @php
                $coloresPortnum = [
                    'telemetry' => 'var(--color-mapa-verde)',
                    'position' => 'var(--color-enlace)',
                    'nodeinfo' => 'var(--color-mapa-naranja)',
                    'text' => 'var(--color-critico-texto)',
                    'neighborinfo' => 'var(--color-texto-3)',
                ];
            @endphp
            <div style="height: 18px; border-radius: var(--radio-full); display: flex; overflow: hidden; background: var(--color-superficie-sutil); margin-bottom: 1.5rem;">
                @foreach(($trafico['items'] ?? []) as $item)
                    @php
                        $pct = $item['packets_percentage'] ?? 0;
                        $color = $coloresPortnum[$item['portnum']] ?? 'var(--color-borde-control)';
                    @endphp
                    @if($pct > 0)
                        <div style="width: {{ $pct }}%; background: {{ $color }};" title="{{ $item['portnum'] }}: {{ $pct }}%"></div>
                    @endif
                @endforeach
            </div>

            <!-- Tabla de desglose de tráfico -->
            <div style="overflow-x: auto;">
                <table class="tabla" style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
                    <thead>
                        <tr style="border-bottom: 2px solid var(--color-borde); text-align: left;">
                            <th style="padding: 0.6rem;">Tipo de Paquete</th>
                            <th style="padding: 0.6rem; text-align: right;">Paquetes</th>
                            <th style="padding: 0.6rem; text-align: right;">% Paquetes</th>
                            <th style="padding: 0.6rem; text-align: right;">Tiempo de Aire</th>
                            <th style="padding: 0.6rem; text-align: right;">% Tiempo Aire</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(($trafico['items'] ?? []) as $item)
                            <tr style="border-bottom: 1px solid var(--color-borde);">
                                <td style="padding: 0.6rem; font-weight: 600;">
                                    <code>{{ $item['portnum'] }}</code>
                                </td>
                                <td style="padding: 0.6rem; text-align: right; font-variant-numeric: tabular-nums;">
                                    {{ number_format((int) $item['packets'], 0, ',', '.') }}
                                </td>
                                <td style="padding: 0.6rem; text-align: right; font-variant-numeric: tabular-nums;">
                                    {{ $item['packets_percentage'] }} %
                                </td>
                                <td style="padding: 0.6rem; text-align: right; font-variant-numeric: tabular-nums;">
                                    {{ number_format((float) $item['airtime_s'], 1, ',', '.') }} s
                                </td>
                                <td style="padding: 0.6rem; text-align: right; font-variant-numeric: tabular-nums;">
                                    {{ $item['airtime_percentage'] }} %
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="padding: 1rem; text-align: center; color: var(--color-texto-3);">
                                    No se registran paquetes en el periodo seleccionado.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <!-- 2. Ranking Principal: Nodos con mayor uso de red -->
        <section class="tarjeta" style="padding: 1.75rem; margin-bottom: 2.5rem;">
            <h2 style="font-size: 1.4rem; font-weight: 700; color: var(--color-texto-1); margin-bottom: 0.5rem;">
                Top Nodos por Uso del Espectro (Tiempo de Aire Originado)
            </h2>
            <p style="color: var(--color-texto-2); font-size: 0.95rem; margin-bottom: 1.5rem;">
                Nodos que más tiempo de transmisión ocupan en el canal regional durante el periodo.
            </p>

            <div style="overflow-x: auto;">
                <table class="tabla" style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
                    <thead>
                        <tr style="border-bottom: 2px solid var(--color-borde); text-align: left;">
                            <th style="padding: 0.6rem; width: 60px;">Puesto</th>
                            <th style="padding: 0.6rem;">Nodo</th>
                            <th style="padding: 0.6rem;">Provincia / Rol</th>
                            <th style="padding: 0.6rem; text-align: right;">Tiempo de Aire</th>
                            <th style="padding: 0.6rem; text-align: right;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(($rankingUso['items'] ?? []) as $r)
                            <tr style="border-bottom: 1px solid var(--color-borde);">
                                <td style="padding: 0.6rem; font-weight: 700; color: var(--color-texto-2);">
                                    #{{ $r['rank'] }}
                                </td>
                                <td style="padding: 0.6rem;">
                                    <strong>{{ $r['node']['short_name'] ?? $r['subject_id'] }}</strong>
                                    @if(!empty($r['node']['long_name']))
                                        <div style="font-size: 0.8rem; color: var(--color-texto-3);">{{ $r['node']['long_name'] }}</div>
                                    @endif
                                </td>
                                <td style="padding: 0.6rem; color: var(--color-texto-2);">
                                    {{ $r['node']['province'] ?? '—' }} · <code>{{ $r['node']['role'] ?? '—' }}</code>
                                </td>
                                <td style="padding: 0.6rem; text-align: right; font-weight: 600; font-variant-numeric: tabular-nums;">
                                    {{ number_format((float) $r['value'], 1, ',', '.') }} s
                                </td>
                                <td style="padding: 0.6rem; text-align: right;">
                                    <a href="/revisa-tu-nodo/{{ $r['subject_id'] }}" class="btn btn-secundario" style="padding: 0.25rem 0.6rem; font-size: 0.8rem;">
                                        Auditar nodo →
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="padding: 1rem; text-align: center; color: var(--color-texto-3);">
                                    Sin datos disponibles en este periodo.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <!-- 3. Catálogo completo de los 11 rankings disponibles -->
        <section>
            <h2 style="font-size: 1.4rem; font-weight: 700; color: var(--color-texto-1); margin-bottom: 1rem;">
                Catálogo de Rankings de la Red
            </h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.25rem;">
                @foreach($catalogo as $cat)
                    <div class="tarjeta" style="padding: 1.25rem; background: var(--color-superficie-sutil);">
                        <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 0.35rem; color: var(--color-texto-1);">
                            {{ $cat['nombre'] }}
                        </h3>
                        <p style="font-size: 0.88rem; color: var(--color-texto-2); line-height: 1.45; margin-bottom: 0.75rem;">
                            {{ $cat['descripcion'] }}
                        </p>
                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.8rem; color: var(--color-texto-3);">
                            <span>Métrica: <code>{{ $cat['unidad'] }}</code></span>
                            <span>Sujeto: <strong>{{ $cat['sujeto'] }}</strong></span>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    </div>
</x-layout>
