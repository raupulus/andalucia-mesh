@php
    $currentLang = app()->getLocale();
    $langParam = $currentLang !== 'es' ? '&lang=' . $currentLang : '';
    $langQuery = $currentLang !== 'es' ? '?lang=' . $currentLang : '';

    $periodParam = '&period=' . $period . '&which=' . $which;

    $urlToggleAndalucia = match($ambito) {
        'ambos' => '/rankings?ambito=espana' . $periodParam . $langParam,
        'espana' => '/rankings?ambito=ambos' . $periodParam . $langParam,
        default => '/rankings?ambito=andalucia' . $periodParam . $langParam,
    };

    $urlToggleEspana = match($ambito) {
        'ambos' => '/rankings?ambito=andalucia' . $periodParam . $langParam,
        'andalucia' => '/rankings?ambito=ambos' . $periodParam . $langParam,
        default => '/rankings?ambito=espana' . $periodParam . $langParam,
    };

    $activoAndalucia = in_array($ambito, ['andalucia', 'ambos'], true);
    $activoEspana = in_array($ambito, ['espana', 'ambos'], true);

    $ambitoTexto = match($ambito) {
        'ambos' => __('admin.ambitos.andalucia_title') . ' + ' . __('admin.ambitos.espana_title'),
        'espana' => __('admin.ambitos.espana_title'),
        default => __('admin.ambitos.andalucia_title'),
    };
@endphp

<x-layout :title="__('portal.rankings.meta_title')" :description="__('portal.rankings.meta_description')" :image="asset('img/og/og-rankings.webp')">
    <div class="contenedor seccion">
        <!-- Encabezado -->
        <header style="margin-bottom: 2rem;">
            <h1 style="font-size: 2.25rem; font-weight: 800; color: var(--color-texto-1); margin-bottom: 0.5rem;">
                {{ __('portal.rankings.heading') }}
            </h1>
            <p class="lead" style="margin-bottom: 1.5rem;">
                {{ __('portal.rankings.lead') }}
            </p>

            <!-- Selector de periodo -->
            <div role="group" aria-label="{{ __('portal.rankings.heading') }}" style="display: flex; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 1.5rem;">
                <a href="/rankings?period=day&which=current&ambito={{ $ambito }}{{ $langParam }}" 
                   class="btn {{ ($period === 'day' && $which === 'current') ? 'btn-primario' : 'btn-secundario' }}">
                    {{ __('portal.rankings.period_today') }}
                </a>
                <a href="/rankings?period=day&which=previous&ambito={{ $ambito }}{{ $langParam }}" 
                   class="btn {{ ($period === 'day' && $which === 'previous') ? 'btn-primario' : 'btn-secundario' }}">
                    {{ __('portal.rankings.period_yesterday') }}
                </a>
                <a href="/rankings?period=week&which=current&ambito={{ $ambito }}{{ $langParam }}" 
                   class="btn {{ ($period === 'week' && $which === 'current') ? 'btn-primario' : 'btn-secundario' }}">
                    {{ __('portal.rankings.period_last_7d') }}
                </a>
                <a href="/rankings?period=14d&which=current&ambito={{ $ambito }}{{ $langParam }}" 
                   class="btn {{ (in_array($period, ['14d', 'month'], true) && $which === 'current') ? 'btn-primario' : 'btn-secundario' }}">
                    {{ __('portal.rankings.period_last_14d') }}
                </a>
            </div>
        </header>

        <!-- Selector de Ámbito Territorial (Andalucía / España) -->
        <section aria-label="{{ __('admin.ambitos.heading') }}" style="margin-bottom: 2rem;">
            <div class="routers-ambito-wrapper">
                <div class="routers-ambito-header">
                    <span class="routers-ambito-title">
                        {{ __('admin.ambitos.heading') }}
                    </span>
                    <div class="routers-ambito-context-indicator">
                        <span>{{ __('admin.ambitos.active_context') }}</span>
                        <strong style="color: {{ $activoAndalucia && $activoEspana ? '#67EA94' : ($activoAndalucia ? '#67EA94' : '#FCA5A5') }}; font-weight: 700; margin-left: 0.35rem;">
                            {{ $ambitoTexto }}
                        </strong>
                    </div>
                </div>

                <div class="routers-ambito-grid">
                    <!-- Tarjeta 1: ANDALUCÍA -->
                    <a
                        href="{{ $urlToggleAndalucia }}"
                        class="routers-ambito-card {{ $activoAndalucia ? 'routers-ambito-card-andalucia-active' : 'routers-ambito-card-inactive' }}"
                        aria-pressed="{{ $activoAndalucia ? 'true' : 'false' }}"
                        title="{{ $activoAndalucia ? __('admin.ambitos.click_to_deactivate') : __('admin.ambitos.click_to_activate') }}"
                    >
                        <div class="routers-ambito-card-left">
                            <div class="routers-ambito-flag-wrapper">
                                <x-icono-bandera idioma="es" :tamano="38" />
                            </div>
                            <div class="routers-ambito-card-info">
                                <div class="routers-ambito-card-title-row">
                                    <span class="routers-ambito-card-name">
                                        {{ __('admin.ambitos.andalucia_title') }}
                                    </span>
                                </div>
                                <span class="routers-ambito-card-desc">
                                    {{ __('admin.ambitos.andalucia_desc') }}
                                </span>
                            </div>
                        </div>

                        <div class="routers-ambito-card-right">
                            <span class="routers-ambito-count-badge {{ $activoAndalucia ? 'routers-ambito-count-badge-andalucia' : '' }}">
                                {{ trans_choice('portal.rankings.nodes_count', $conteoAndalucia, ['count' => $conteoAndalucia]) }}
                            </span>
                            <span class="routers-ambito-switch-pill {{ $activoAndalucia ? 'routers-ambito-switch-pill-on-andalucia' : 'routers-ambito-switch-pill-off' }}">
                                @if($activoAndalucia)
                                    ✓ {{ __('admin.ambitos.active_badge') }}
                                @else
                                    ＋ {{ __('admin.ambitos.add_badge') }}
                                @endif
                            </span>
                        </div>
                    </a>

                    <!-- Tarjeta 2: ESPAÑA -->
                    <a
                        href="{{ $urlToggleEspana }}"
                        class="routers-ambito-card {{ $activoEspana ? 'routers-ambito-card-espana-active' : 'routers-ambito-card-inactive' }}"
                        aria-pressed="{{ $activoEspana ? 'true' : 'false' }}"
                        title="{{ $activoEspana ? __('admin.ambitos.click_to_deactivate') : __('admin.ambitos.click_to_activate') }}"
                    >
                        <div class="routers-ambito-card-left">
                            <div class="routers-ambito-flag-wrapper">
                                <x-icono-bandera idioma="espana" :tamano="38" />
                            </div>
                            <div class="routers-ambito-card-info">
                                <div class="routers-ambito-card-title-row">
                                    <span class="routers-ambito-card-name">
                                        {{ __('admin.ambitos.espana_title') }}
                                    </span>
                                </div>
                                <span class="routers-ambito-card-desc">
                                    {{ __('admin.ambitos.espana_desc') }}
                                </span>
                            </div>
                        </div>

                        <div class="routers-ambito-card-right">
                            <span class="routers-ambito-count-badge {{ $activoEspana ? 'routers-ambito-count-badge-espana' : '' }}">
                                {{ trans_choice('portal.rankings.nodes_count', $conteoEspana, ['count' => $conteoEspana]) }}
                            </span>
                            <span class="routers-ambito-switch-pill {{ $activoEspana ? 'routers-ambito-switch-pill-on-espana' : 'routers-ambito-switch-pill-off' }}">
                                @if($activoEspana)
                                    ✓ {{ __('admin.ambitos.active_badge') }}
                                @else
                                    ＋ {{ __('admin.ambitos.add_badge') }}
                                @endif
                            </span>
                        </div>
                    </a>
                </div>
            </div>
        </section>

        <!-- 1. Distribución del tráfico (Traffic Mix) -->
        <section class="tarjeta" style="padding: 1.75rem; margin-bottom: 2.5rem;">
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.75rem; margin-bottom: 0.5rem;">
                <h2 style="font-size: 1.4rem; font-weight: 700; color: var(--color-texto-1); margin: 0;">
                    {{ __('portal.rankings.traffic_mix_title') }}
                </h2>
                <span class="routers-ambito-context-indicator" style="font-size: 0.78rem;">
                    {{ $ambitoTexto }}
                </span>
            </div>
            <p style="color: var(--color-texto-2); font-size: 0.95rem; margin-bottom: 1.5rem;">
                {{ __('portal.rankings.total_packets') }} <strong>{{ number_format((int) ($trafico['total_packets'] ?? 0), 0, ',', '.') }}</strong> · 
                {{ __('portal.rankings.total_airtime') }} <strong>{{ number_format((float) ($trafico['total_airtime_s'] ?? 0), 1, ',', '.') }} s</strong>
            </p>

            <!-- Barra gráfica de proporciones -->
            @php
                $coloresPortnum = [
                    'telemetry' => 'var(--mapa-verde, #10b981)',
                    'position' => 'var(--color-enlace, #3b82f6)',
                    'nodeinfo' => 'var(--mapa-naranja, #f59e0b)',
                    'text' => 'var(--color-critico-texto, #ef4444)',
                    'neighborinfo' => 'var(--color-texto-3, #94a3b8)',
                    'traceroute' => '#8b5cf6',
                    'routing' => '#06b6d4',
                    'other' => '#64748b',
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
                            <th style="padding: 0.6rem;">{{ __('portal.rankings.th_packet_type') }}</th>
                            <th style="padding: 0.6rem; text-align: right;">{{ __('portal.rankings.th_packets') }}</th>
                            <th style="padding: 0.6rem; text-align: right;">{{ __('portal.rankings.th_packets_pct') }}</th>
                            <th style="padding: 0.6rem; text-align: right;">{{ __('portal.rankings.th_airtime') }}</th>
                            <th style="padding: 0.6rem; text-align: right;">{{ __('portal.rankings.th_airtime_pct') }}</th>
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
                                    {{ __('portal.rankings.no_records') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <!-- 2. Ranking Principal: Nodos con mayor uso de red (Widget con diseño verde) -->
        <section class="tarjeta widget-rankings-verde" style="padding: 1.75rem; margin-bottom: 2.5rem; border-top: 3px solid var(--color-acento, #67EA94); position: relative; overflow: hidden;">
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.75rem; margin-bottom: 0.75rem;">
                <div>
                    <h2 style="font-size: 1.4rem; font-weight: 800; color: var(--color-texto-1); margin: 0 0 0.35rem 0; display: flex; align-items: center; gap: 0.5rem;">
                        <span style="color: var(--color-acento, #67EA94); font-size: 1.35rem;" aria-hidden="true">📡</span>
                        {{ __('portal.rankings.top_nodes_airtime_title') }}
                    </h2>
                    <p style="color: var(--color-texto-2); font-size: 0.95rem; margin: 0;">
                        {{ __('portal.rankings.top_nodes_airtime_desc') }} ({{ $ambitoTexto }})
                    </p>
                </div>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <span class="routers-ambito-count-badge routers-ambito-count-badge-andalucia" style="font-size: 0.8rem; padding: 0.3rem 0.75rem;">
                        Top 10 {{ $ambitoTexto }}
                    </span>
                </div>
            </div>

            <div style="overflow-x: auto; margin-top: 1.25rem;">
                <table class="tabla tabla-widget-verde" style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
                    <thead>
                        <tr class="cabecera-widget-verde">
                            <th style="padding: 0.75rem 0.6rem; width: 60px;">{{ __('portal.rankings.th_rank') }}</th>
                            <th style="padding: 0.75rem 0.6rem;">{{ __('portal.rankings.th_node') }}</th>
                            <th style="padding: 0.75rem 0.6rem;">{{ __('portal.rankings.th_province') }}</th>
                            <th style="padding: 0.75rem 0.6rem;">{{ __('portal.rankings.th_role') }}</th>
                            <th style="padding: 0.75rem 0.6rem; text-align: right;">{{ __('portal.rankings.th_airtime') }}</th>
                            <th style="padding: 0.75rem 0.6rem; text-align: right;">{{ __('portal.rankings.th_actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(($rankingUso['items'] ?? []) as $r)
                            @php
                                $provCode = $r['node']['province'] ?? null;
                                $provNombre = $provCode ? (\App\Http\Controllers\RoutersController::PROVINCIAS_ESPANA[$provCode] ?? $provCode) : '—';
                                $roleCode = $r['node']['role'] ?? 'CLIENT';
                                $roleBadgeClass = match($roleCode) {
                                    'ROUTER' => 'badge-rol-router',
                                    'ROUTER_LATE' => 'badge-rol-router-late',
                                    'CLIENT_BASE' => 'badge-rol-client-base',
                                    'CLIENT_MUTE' => 'badge-rol-client-mute',
                                    default => 'badge-rol-client',
                                };
                            @endphp
                            <tr class="fila-widget-verde" style="border-bottom: 1px solid var(--color-borde);">
                                <td style="padding: 0.75rem 0.6rem; font-weight: 800; color: var(--color-acento, #67EA94); font-size: 0.95rem;">
                                    #{{ $r['rank'] }}
                                </td>
                                <td style="padding: 0.75rem 0.6rem;">
                                    <div style="font-weight: 700; color: var(--color-texto);">
                                        {{ $r['node']['short_name'] ?? $r['subject_id'] }}
                                    </div>
                                    @if(!empty($r['node']['long_name']))
                                        <div style="font-size: 0.8rem; color: var(--color-texto-3);">{{ $r['node']['long_name'] }}</div>
                                    @else
                                        <div style="font-family: 'Ubuntu Mono', monospace; font-size: 0.78rem; color: var(--color-texto-3);">{{ $r['subject_id'] }}</div>
                                    @endif
                                </td>
                                <!-- Columna Provincia separada -->
                                <td style="padding: 0.75rem 0.6rem;">
                                    <span class="badge-prov badge-prov-general" style="font-size: 0.75rem;">
                                        {{ $provNombre }}
                                    </span>
                                </td>
                                <!-- Columna Rol separada -->
                                <td style="padding: 0.75rem 0.6rem;">
                                    <span class="badge-rol {{ $roleBadgeClass }}" style="font-size: 0.72rem;">
                                        {{ $roleCode }}
                                    </span>
                                </td>
                                <!-- Tiempo de aire -->
                                <td style="padding: 0.75rem 0.6rem; text-align: right; font-weight: 700; font-variant-numeric: tabular-nums; color: var(--color-texto);">
                                    <span style="background: rgba(103, 234, 148, 0.12); color: var(--color-enlace, #15612f); padding: 3px 8px; border-radius: 4px; border: 1px solid rgba(103, 234, 148, 0.25);">
                                        {{ number_format((float) $r['value'], 1, ',', '.') }} s
                                    </span>
                                </td>
                                <td style="padding: 0.75rem 0.6rem; text-align: right;">
                                    <a href="/revisa-tu-nodo/{{ $r['subject_id'] }}{{ $langQuery }}" class="btn btn-secundario" style="padding: 0.3rem 0.7rem; font-size: 0.8rem; border-color: var(--color-acento, #67EA94);">
                                        {{ __('portal.rankings.btn_audit_node') }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="padding: 1.5rem; text-align: center; color: var(--color-texto-3);">
                                    {{ __('portal.rankings.no_records') }}
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
                {{ __('portal.rankings.catalog_title') }}
            </h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 280px), 1fr)); gap: 1.25rem;">
                @foreach($catalogo as $cat)
                    <div class="tarjeta" style="padding: 1.25rem; background: var(--color-superficie-sutil);">
                        <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 0.35rem; color: var(--color-texto-1);">
                            {{ $cat['nombre'] }}
                        </h3>
                        <p style="font-size: 0.88rem; color: var(--color-texto-2); line-height: 1.45; margin-bottom: 0.75rem;">
                            {{ $cat['descripcion'] }}
                        </p>
                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.8rem; color: var(--color-texto-3);">
                            <span>{{ __('portal.rankings.metric_label') }} <code>{{ $cat['unidad'] }}</code></span>
                            <span>{{ __('portal.rankings.subject_label') }} <strong>{{ $cat['sujeto'] }}</strong></span>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    </div>
</x-layout>

<style>
/* Selector de Ámbito Territorial */
.routers-ambito-wrapper {
    margin-bottom: 1.25rem;
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}
.routers-ambito-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 0.5rem;
    padding: 0 0.25rem;
}
.routers-ambito-title {
    font-size: 0.82rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--color-texto-3, #94a3b8);
}
.routers-ambito-context-indicator {
    font-size: 0.82rem;
    color: var(--color-texto-2, #cbd5e1);
    display: inline-flex;
    align-items: center;
    background: var(--color-superficie-sutil, rgba(30, 41, 59, 0.7));
    padding: 0.3rem 0.85rem;
    border-radius: 9999px;
    border: 1px solid var(--color-borde, rgba(148, 163, 184, 0.2));
}
.routers-ambito-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
    width: 100%;
}
@media (max-width: 768px) {
    .routers-ambito-grid {
        grid-template-columns: 1fr;
    }
}
.routers-ambito-card {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: 1.25rem 1.5rem;
    border-radius: 0.85rem;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    box-sizing: border-box;
}
.routers-ambito-card-inactive {
    background: var(--color-superficie);
    border: 2px solid var(--color-borde);
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
    opacity: 0.75;
}
.routers-ambito-card-inactive:hover {
    opacity: 1;
    transform: translateY(-2px);
    border-color: var(--color-borde-control);
    background: var(--color-superficie-elevada);
}
.routers-ambito-card-andalucia-active {
    background: linear-gradient(135deg, rgba(0, 122, 51, 0.22) 0%, var(--color-superficie) 100%);
    border: 2px solid #67EA94;
    box-shadow: 0 4px 20px rgba(0, 122, 51, 0.35), inset 0 0 16px rgba(103, 234, 148, 0.08);
    opacity: 1;
}
.routers-ambito-card-andalucia-active:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 24px rgba(0, 122, 51, 0.45), inset 0 0 20px rgba(103, 234, 148, 0.12);
}
.routers-ambito-card-espana-active {
    background: linear-gradient(135deg, rgba(220, 38, 38, 0.22) 0%, var(--color-superficie) 100%);
    border: 2px solid #f87171;
    box-shadow: 0 4px 20px rgba(220, 38, 38, 0.35), inset 0 0 16px rgba(248, 113, 113, 0.08);
    opacity: 1;
}
.routers-ambito-card-espana-active:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 24px rgba(220, 38, 38, 0.45), inset 0 0 20px rgba(248, 113, 113, 0.12);
}
.routers-ambito-card-left {
    display: flex;
    align-items: center;
    gap: 1rem;
    min-width: 0;
}
.routers-ambito-flag-wrapper {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.35));
}
.routers-ambito-card-info {
    display: flex;
    flex-direction: column;
    min-width: 0;
    text-align: left;
}
.routers-ambito-card-title-row {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.routers-ambito-card-name {
    font-size: 1.15rem;
    font-weight: 800;
    color: var(--color-texto);
    line-height: 1.25;
}
.routers-ambito-card-desc {
    font-size: 0.82rem;
    color: var(--color-texto-2);
    line-height: 1.35;
    margin-top: 0.25rem;
}
.routers-ambito-card-right {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 0.5rem;
    flex-shrink: 0;
}
.routers-ambito-count-badge {
    font-size: 0.78rem;
    font-weight: 700;
    font-variant-numeric: tabular-nums;
    padding: 0.25rem 0.65rem;
    border-radius: 0.375rem;
    background: rgba(148, 163, 184, 0.12);
    color: var(--color-texto-2);
    white-space: nowrap;
}
.routers-ambito-count-badge-andalucia {
    background: rgba(103, 234, 148, 0.18);
    color: #67EA94;
    font-weight: 800;
}
.routers-ambito-count-badge-espana {
    background: rgba(248, 113, 113, 0.18);
    color: #fca5a5;
    font-weight: 800;
}
.routers-ambito-switch-pill {
    font-size: 0.72rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    padding: 0.35rem 0.85rem;
    border-radius: 9999px;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    white-space: nowrap;
}
.routers-ambito-switch-pill-off {
    background: var(--color-superficie-sutil);
    color: var(--color-texto-3);
    border: 1px dashed var(--color-borde-control);
}
.routers-ambito-switch-pill-on-andalucia {
    background: #15612F;
    color: #ffffff;
    border: 1px solid #67EA94;
    box-shadow: 0 2px 8px rgba(0, 122, 51, 0.5);
}
.routers-ambito-switch-pill-on-espana {
    background: #991b1b;
    color: #ffffff;
    border: 1px solid #f87171;
    box-shadow: 0 2px 8px rgba(220, 38, 38, 0.5);
}

/* Tabla Widget Verde */
.widget-rankings-verde {
    box-shadow: 0 4px 20px rgba(103, 234, 148, 0.08);
}
.cabecera-widget-verde {
    background: rgba(103, 234, 148, 0.08);
    border-bottom: 2px solid var(--color-acento, #67EA94) !important;
    color: var(--color-texto);
    text-align: left;
}
.fila-widget-verde {
    transition: background 0.15s ease;
}
.fila-widget-verde:hover {
    background: rgba(103, 234, 148, 0.04);
}
</style>
