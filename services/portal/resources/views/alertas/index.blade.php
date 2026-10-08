@php
    $currentLang = app()->getLocale();
    $langParam = $currentLang !== 'es' ? '&lang=' . $currentLang : '';
    $langQuery = $currentLang !== 'es' ? '?lang=' . $currentLang : '';
@endphp

<x-layout :title="__('portal.alerts.meta_title')" :description="__('portal.alerts.meta_description')">
    <div class="contenedor seccion">
        <header style="margin-bottom: 2.5rem;">
            <h1 style="font-size: 2.25rem; font-weight: 800; color: var(--color-texto-1); margin-bottom: 0.5rem;">
                {{ __('portal.alerts.heading') }}
            </h1>
            <p class="lead" style="margin-bottom: 1.5rem;">
                {{ __('portal.alerts.lead') }}
            </p>

            <!-- Filtros de severidad -->
            <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                <a href="/alertas{{ $langQuery }}" class="btn {{ empty($riesgoFiltro) ? 'btn-primario' : 'btn-secundario' }}">
                    {{ __('portal.alerts.filter_all') }}
                </a>
                <a href="/alertas?riesgo=alto{{ $langParam }}" class="btn {{ $riesgoFiltro === 'alto' ? 'btn-primario' : 'btn-secundario' }}">
                    {{ __('portal.alerts.filter_high') }}
                </a>
                <a href="/alertas?riesgo=medio{{ $langParam }}" class="btn {{ $riesgoFiltro === 'medio' ? 'btn-primario' : 'btn-secundario' }}">
                    {{ __('portal.alerts.filter_medium') }}
                </a>
                <a href="/alertas?riesgo=bajo{{ $langParam }}" class="btn {{ $riesgoFiltro === 'bajo' ? 'btn-primario' : 'btn-secundario' }}">
                    {{ __('portal.alerts.filter_low') }}
                </a>
            </div>
        </header>

        <!-- Banner informativo si el detector está en calibración -->
        @if($detectorCalibrando || empty($alertas))
            <div class="tarjeta" style="padding: 2rem; margin-bottom: 3rem; background: var(--color-superficie-sutil); border-left: 4px solid var(--color-enlace);">
                <div style="display: flex; gap: 1rem; align-items: flex-start;">
                    <div style="font-size: 1.75rem; line-height: 1;">ℹ️</div>
                    <div>
                        <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--color-texto-1); margin-bottom: 0.5rem;">
                            {{ __('portal.alerts.detector_calibrating_title') }}
                        </h2>
                        <p style="color: var(--color-texto-2); font-size: 0.95rem; line-height: 1.55; margin-bottom: 0;">
                            {{ __('portal.alerts.detector_calibrating_body') }}
                        </p>
                    </div>
                </div>
            </div>
        @else
            <!-- Listado de alertas registradas -->
            <div class="tarjeta" style="padding: 1.5rem; margin-bottom: 3rem;">
                <div style="overflow-x: auto;">
                    <table class="tabla" style="width: 100%; border-collapse: collapse; font-size: 0.92rem;">
                        <thead>
                            <tr style="border-bottom: 2px solid var(--color-borde); text-align: left;">
                                <th style="padding: 0.6rem;">{{ __('portal.alerts.th_risk') }}</th>
                                <th style="padding: 0.6rem;">{{ __('portal.alerts.th_rule') }}</th>
                                <th style="padding: 0.6rem;">{{ __('portal.alerts.th_node') }}</th>
                                <th style="padding: 0.6rem;">{{ __('portal.alerts.th_start') }}</th>
                                <th style="padding: 0.6rem;">{{ __('portal.alerts.th_status') }}</th>
                                <th style="padding: 0.6rem; text-align: right;">{{ __('portal.alerts.th_detail') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($alertas as $a)
                                @php
                                    $riesgoKey = strtolower((string) $a->riesgo);
                                    $chipTipo = match($riesgoKey) {
                                        'alto' => 'critico',
                                        'medio' => 'aviso',
                                        'bajo' => 'info',
                                        default => 'neutro',
                                    };
                                    $textoRiesgo = __('portal.alerts.risk_levels.' . $riesgoKey);
                                    if ($textoRiesgo === 'portal.alerts.risk_levels.' . $riesgoKey) {
                                        $textoRiesgo = ucfirst((string) $a->riesgo);
                                    }
                                    $estadoKey = strtolower((string) ($a->estado ?? 'abierta'));
                                    $textoEstado = __('portal.alerts.states.' . $estadoKey);
                                    if ($textoEstado === 'portal.alerts.states.' . $estadoKey) {
                                        $textoEstado = ucfirst($estadoKey);
                                    }
                                @endphp
                                <tr style="border-bottom: 1px solid var(--color-borde);">
                                    <td style="padding: 0.6rem;">
                                        <x-chip-estado :tipo="$chipTipo" :texto="$textoRiesgo" />
                                    </td>
                                    <td style="padding: 0.6rem; font-weight: 600;">
                                        {{ $a->regla ?? __('portal.alerts.detected_incident') }}
                                    </td>
                                    <td style="padding: 0.6rem;">
                                        <code>{{ $a->nodo_id ?? __('portal.alerts.scope_global') }}</code>
                                    </td>
                                    <td style="padding: 0.6rem; color: var(--color-texto-2); font-size: 0.85rem;">
                                        {{ $a->inicio_at ?? '—' }}
                                    </td>
                                    <td style="padding: 0.6rem;">
                                        <span style="font-weight: 600; color: {{ $estadoKey === 'abierta' ? 'var(--color-critico-texto)' : 'var(--mapa-verde)' }};">
                                            {{ $textoEstado }}
                                        </span>
                                    </td>
                                    <td style="padding: 0.6rem; text-align: right;">
                                        <a href="/alertas/{{ $a->id }}{{ $langQuery }}" class="btn btn-secundario" style="padding: 0.2rem 0.5rem; font-size: 0.8rem;">
                                            {{ __('portal.alerts.btn_view') }}
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- Catálogo de anomalías monitorizadas -->
        <section>
            <h2 style="font-size: 1.4rem; font-weight: 700; color: var(--color-texto-1); margin-bottom: 1rem;">
                {{ __('portal.alerts.catalog_title') }}
            </h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.25rem;">
                @foreach($reglas as $regla)
                    @php
                        $riesgoKey = strtolower((string) $regla['risk']);
                        $chipTipo = match($riesgoKey) {
                            'alto' => 'critico',
                            'medio' => 'aviso',
                            'bajo' => 'info',
                            default => 'neutro',
                        };
                        $textoRiesgo = __('portal.alerts.risk_levels.' . $riesgoKey);
                        if ($textoRiesgo === 'portal.alerts.risk_levels.' . $riesgoKey) {
                            $textoRiesgo = ucfirst((string) $regla['risk']);
                        }
                    @endphp
                    <div class="tarjeta" style="padding: 1.25rem; background: var(--color-superficie-sutil);">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.5rem;">
                            <h3 style="font-size: 1.05rem; font-weight: 700; color: var(--color-texto-1); margin: 0;">
                                {{ $regla['name'] }}
                            </h3>
                            <x-chip-estado :tipo="$chipTipo" :texto="$textoRiesgo" />
                        </div>
                        <p style="font-size: 0.88rem; color: var(--color-texto-2); line-height: 1.45; margin-bottom: 0.5rem;">
                            {{ $regla['description'] }}
                        </p>
                        <div style="font-size: 0.8rem; color: var(--color-texto-3);">
                            {{ __('portal.alerts.type_label') }} <strong>{{ ucfirst($regla['type']) }}</strong>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    </div>
</x-layout>
