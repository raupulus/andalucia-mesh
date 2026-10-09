@php
    $currentLang = app()->getLocale();
    $langParam = $currentLang !== 'es' ? '&lang=' . $currentLang : '';
    $langQuery = $currentLang !== 'es' ? '?lang=' . $currentLang : '';

    $filtroUrl = function(?string $nuevoEstado = null, ?string $nuevoRiesgo = null, ?string $nuevoProblema = null) use ($estadoFiltro, $riesgoFiltro, $problemaFiltro, $langParam) {
        $params = [];
        $e = $nuevoEstado !== null ? $nuevoEstado : $estadoFiltro;
        $r = $nuevoRiesgo !== null ? $nuevoRiesgo : $riesgoFiltro;
        $p = $nuevoProblema !== null ? $nuevoProblema : $problemaFiltro;

        if (!empty($e)) {
            $params['estado'] = $e;
        }
        if (!empty($r)) {
            $params['riesgo'] = $r;
        }
        if (!empty($p)) {
            $params['problema'] = $p;
        }

        $query = http_build_query($params);
        $base = '/alertas';
        if (!empty($query)) {
            $base .= '?' . $query . $langParam;
        } elseif (!empty($langParam)) {
            $base .= '?' . ltrim($langParam, '&');
        }
        return $base;
    };

    $hayFiltrosActivos = !empty($estadoFiltro) || !empty($riesgoFiltro) || !empty($problemaFiltro);
@endphp

<x-layout :title="__('portal.alerts.meta_title')" :description="__('portal.alerts.meta_description')" :image="asset('img/og/og-alertas.webp')">
    <div class="contenedor seccion">
        <header style="margin-bottom: 2rem;">
            <h1 style="font-size: 2.25rem; font-weight: 800; color: var(--color-texto-1); margin-bottom: 0.5rem;">
                {{ __('portal.alerts.heading') }}
            </h1>
            <p class="lead" style="margin-bottom: 1.5rem;">
                {{ __('portal.alerts.lead') }}
            </p>

            <!-- Panel oxigenado de filtros -->
            <div class="tarjeta" style="padding: 1.5rem; margin-bottom: 1.75rem; background: var(--color-superficie); border: 1px solid var(--color-borde); border-radius: var(--radio-md);">
                
                <!-- Nivel 1: Filtros de Control (Estado y Severidad) -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.25rem; align-items: flex-start; padding-bottom: 1.25rem; border-bottom: 1px solid var(--color-borde);">
                    
                    <!-- Estado de la incidencia -->
                    <div>
                        <span style="display: block; font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; color: var(--color-texto-3); margin-bottom: 0.5rem;">
                            {{ __('portal.alerts.status_label') }}
                        </span>
                        <div style="display: flex; flex-wrap: wrap; gap: 0.4rem;">
                            <a href="{{ $filtroUrl('', null, null) }}" class="btn {{ empty($estadoFiltro) ? 'btn-primario' : 'btn-secundario' }}" style="padding: 0.35rem 0.75rem; font-size: 0.82rem;">
                                {{ __('portal.alerts.filter_all') }}
                            </a>
                            <a href="{{ $filtroUrl('abierta', null, null) }}" class="btn {{ $estadoFiltro === 'abierta' ? 'btn-primario' : 'btn-secundario' }}" style="padding: 0.35rem 0.75rem; font-size: 0.82rem;">
                                🔴 {{ __('portal.alerts.states.abierta') }}
                            </a>
                            <a href="{{ $filtroUrl('resuelta', null, null) }}" class="btn {{ $estadoFiltro === 'resuelta' ? 'btn-primario' : 'btn-secundario' }}" style="padding: 0.35rem 0.75rem; font-size: 0.82rem;">
                                🟢 {{ __('portal.alerts.states.resuelta') }}
                            </a>
                        </div>
                    </div>

                    <!-- Severidad / Riesgo -->
                    <div>
                        <span style="display: block; font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; color: var(--color-texto-3); margin-bottom: 0.5rem;">
                            {{ __('portal.alerts.risk_label') }}
                        </span>
                        <div style="display: flex; flex-wrap: wrap; gap: 0.4rem;">
                            <a href="{{ $filtroUrl(null, '', null) }}" class="btn {{ empty($riesgoFiltro) ? 'btn-primario' : 'btn-secundario' }}" style="padding: 0.35rem 0.75rem; font-size: 0.82rem;">
                                {{ __('portal.alerts.filter_all') }}
                            </a>
                            <a href="{{ $filtroUrl(null, 'alto', null) }}" class="btn {{ $riesgoFiltro === 'alto' ? 'btn-primario' : 'btn-secundario' }}" style="padding: 0.35rem 0.75rem; font-size: 0.82rem;">
                                {{ __('portal.alerts.filter_high') }}
                            </a>
                            <a href="{{ $filtroUrl(null, 'medio', null) }}" class="btn {{ $riesgoFiltro === 'medio' ? 'btn-primario' : 'btn-secundario' }}" style="padding: 0.35rem 0.75rem; font-size: 0.82rem;">
                                {{ __('portal.alerts.filter_medium') }}
                            </a>
                            <a href="{{ $filtroUrl(null, 'bajo', null) }}" class="btn {{ $riesgoFiltro === 'bajo' ? 'btn-primario' : 'btn-secundario' }}" style="padding: 0.35rem 0.75rem; font-size: 0.82rem;">
                                {{ __('portal.alerts.filter_low') }}
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Nivel 2: Temáticas de problemas (espacioso y con iconos) -->
                <div style="padding-top: 1.25rem;">
                    <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 0.65rem;">
                        <span style="font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; color: var(--color-texto-3);">
                            🏷️ {{ __('portal.alerts.problem_label') }}
                        </span>
                        @if(!empty($problemaFiltro))
                            <a href="{{ $filtroUrl(null, null, '') }}" style="font-size: 0.8rem; color: var(--color-enlace); text-decoration: none;">
                                ✕ {{ __('portal.alerts.filter_all_problems') }}
                            </a>
                        @endif
                    </div>

                    <div style="display: flex; flex-wrap: wrap; gap: 0.45rem;">
                        <a href="{{ $filtroUrl(null, null, '') }}" class="btn {{ empty($problemaFiltro) ? 'btn-primario' : 'btn-secundario' }}" style="padding: 0.35rem 0.75rem; font-size: 0.82rem; border-radius: var(--radio-pill, 9999px);">
                            🔘 {{ __('portal.alerts.filter_all_problems') }}
                        </a>
                        @foreach($categoriasProblemas as $slug => $cat)
                            @php
                                $nombreTraducido = __('portal.alerts.problem_types.' . $slug);
                                if ($nombreTraducido === 'portal.alerts.problem_types.' . $slug) {
                                    $nombreTraducido = $cat['nombre'];
                                }
                                $activo = ($problemaFiltro === $slug);
                            @endphp
                            <a href="{{ $filtroUrl(null, null, $activo ? '' : $slug) }}" class="btn {{ $activo ? 'btn-primario' : 'btn-secundario' }}" style="padding: 0.35rem 0.75rem; font-size: 0.82rem; border-radius: var(--radio-pill, 9999px); display: inline-flex; align-items: center; gap: 0.35rem;">
                                <span aria-hidden="true">{{ $cat['icono'] }}</span>
                                <span>{{ $nombreTraducido }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>

                <!-- Nivel 3: Barra de Filtros Activos (solo visible si hay filtros aplicados) -->
                @if($hayFiltrosActivos)
                    <div style="margin-top: 1.25rem; padding-top: 1rem; border-top: 1px dashed var(--color-borde); display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 0.75rem; font-size: 0.85rem;">
                        <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 0.5rem;">
                            <span style="font-weight: 700; color: var(--color-texto-2);">
                                {{ __('portal.alerts.active_filters') }}
                            </span>
                            @if(!empty($estadoFiltro))
                                <a href="{{ $filtroUrl('', null, null) }}" class="badge" style="background: var(--color-superficie-sutil); border: 1px solid var(--color-borde); padding: 0.2rem 0.5rem; border-radius: var(--radio-sm); color: var(--color-texto-1); text-decoration: none; display: inline-flex; align-items: center; gap: 0.35rem;">
                                    <span>{{ $estadoFiltro === 'abierta' ? '🔴' : '🟢' }} {{ __('portal.alerts.states.' . $estadoFiltro) }}</span>
                                    <span style="color: var(--color-texto-3); font-weight: 700;">✕</span>
                                </a>
                            @endif
                            @if(!empty($riesgoFiltro))
                                <a href="{{ $filtroUrl(null, '', null) }}" class="badge" style="background: var(--color-superficie-sutil); border: 1px solid var(--color-borde); padding: 0.2rem 0.5rem; border-radius: var(--radio-sm); color: var(--color-texto-1); text-decoration: none; display: inline-flex; align-items: center; gap: 0.35rem;">
                                    <span>{{ __('portal.alerts.risk_label') }} {{ __('portal.alerts.risk_levels.' . $riesgoFiltro) }}</span>
                                    <span style="color: var(--color-texto-3); font-weight: 700;">✕</span>
                                </a>
                            @endif
                            @if(!empty($problemaFiltro) && isset($categoriasProblemas[$problemaFiltro]))
                                @php
                                    $pNombre = __('portal.alerts.problem_types.' . $problemaFiltro);
                                    if ($pNombre === 'portal.alerts.problem_types.' . $problemaFiltro) {
                                        $pNombre = $categoriasProblemas[$problemaFiltro]['nombre'];
                                    }
                                @endphp
                                <a href="{{ $filtroUrl(null, null, '') }}" class="badge" style="background: var(--color-superficie-sutil); border: 1px solid var(--color-borde); padding: 0.2rem 0.5rem; border-radius: var(--radio-sm); color: var(--color-texto-1); text-decoration: none; display: inline-flex; align-items: center; gap: 0.35rem;">
                                    <span>{{ $categoriasProblemas[$problemaFiltro]['icono'] }} {{ $pNombre }}</span>
                                    <span style="color: var(--color-texto-3); font-weight: 700;">✕</span>
                                </a>
                            @endif
                        </div>

                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <span style="color: var(--color-texto-2); font-weight: 600;">
                                {{ trans_choice('portal.alerts.showing_alerts_count', count($alertas), ['count' => count($alertas)]) }}
                            </span>
                            <a href="{{ $filtroUrl('', '', '') }}" style="font-weight: 700; color: var(--color-critico-texto, #9B1E15); text-decoration: none; font-size: 0.82rem;">
                                🗑️ {{ __('portal.alerts.clear_filters') }}
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </header>

        <!-- Banner si el detector está en calibración o estado vacío -->
        @if($detectorCalibrando)
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
        @elseif(empty($alertas))
            <div class="tarjeta" style="padding: 2.5rem 1.5rem; margin-bottom: 3rem; background: var(--color-superficie-sutil); border: 1px solid var(--color-borde); text-align: center; border-radius: var(--radio-md);">
                <div style="font-size: 2.25rem; margin-bottom: 0.5rem;" aria-hidden="true">🔍</div>
                <h2 style="font-size: 1.2rem; font-weight: 700; color: var(--color-texto-1); margin-bottom: 0.5rem;">
                    {{ __('portal.alerts.no_alerts_with_filters') }}
                </h2>
                @if($hayFiltrosActivos)
                    <p style="color: var(--color-texto-2); font-size: 0.92rem; margin-bottom: 1.25rem;">
                        No existen incidencias que coincidan con la combinación de filtros seleccionada.
                    </p>
                    <a href="{{ $filtroUrl('', '', '') }}" class="btn btn-secundario" style="font-size: 0.85rem; padding: 0.4rem 1rem;">
                        {{ __('portal.alerts.clear_filters') }}
                    </a>
                @endif
            </div>
        @else
            <!-- Listado enriquecido de alertas registradas -->
            <div class="tarjeta" style="padding: 1.5rem; margin-bottom: 3rem;">
                <div style="overflow-x: auto;">
                    <table class="tabla" style="width: 100%; border-collapse: collapse; font-size: 0.92rem;">
                        <thead>
                            <tr style="border-bottom: 2px solid var(--color-borde); text-align: left;">
                                <th style="padding: 0.75rem 0.6rem;">{{ __('portal.alerts.th_risk') }}</th>
                                <th style="padding: 0.75rem 0.6rem;">{{ __('portal.alerts.th_rule') }}</th>
                                <th style="padding: 0.75rem 0.6rem;">{{ __('portal.alerts.th_node') }}</th>
                                <th style="padding: 0.75rem 0.6rem;">{{ __('portal.alerts.th_start') }}</th>
                                <th style="padding: 0.75rem 0.6rem;">{{ __('portal.alerts.th_status') }}</th>
                                <th style="padding: 0.75rem 0.6rem; text-align: right;">{{ __('portal.alerts.th_detail') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($alertas as $a)
                                @php
                                    $riesgoKey = strtolower((string) ($a['riesgo'] ?? 'medio'));
                                    $chipTipo = match($riesgoKey) {
                                        'alto' => 'critico',
                                        'medio' => 'aviso',
                                        'bajo' => 'info',
                                        default => 'neutro',
                                    };
                                    $textoRiesgo = __('portal.alerts.risk_levels.' . $riesgoKey);
                                    if ($textoRiesgo === 'portal.alerts.risk_levels.' . $riesgoKey) {
                                        $textoRiesgo = ucfirst($riesgoKey);
                                    }
                                    $estadoKey = strtolower((string) ($a['estado'] ?? 'abierta'));
                                    $textoEstado = __('portal.alerts.states.' . $estadoKey);
                                    if ($textoEstado === 'portal.alerts.states.' . $estadoKey) {
                                        $textoEstado = ucfirst($estadoKey);
                                    }
                                    $esGlobal = $a['nodo_es_global'] ?? false;
                                    $nombreNodo = $a['nodo_nombre_corto'] ?? $a['nodo_codigo'] ?? null;
                                    $nombreLargo = $a['nodo_nombre_largo'] ?? null;
                                    $rol = $a['nodo_rol'] ?? null;
                                    $provincia = $a['nodo_provincia'] ?? null;
                                @endphp
                                <tr style="border-bottom: 1px solid var(--color-borde); vertical-align: top;">
                                    <td style="padding: 0.85rem 0.6rem;">
                                        <x-chip-estado :tipo="$chipTipo" :texto="$textoRiesgo" />
                                    </td>
                                    <td style="padding: 0.85rem 0.6rem;">
                                        <div style="display: flex; align-items: baseline; gap: 0.4rem; margin-bottom: 0.2rem;">
                                            <span style="font-size: 1.05rem;" aria-hidden="true">{{ $a['regla_icono'] ?? '⚠️' }}</span>
                                            <a href="/alertas/{{ $a['id'] }}{{ $langQuery }}" style="font-weight: 700; color: var(--color-texto-1); text-decoration: none;">
                                                {{ $a['regla_nombre'] ?? $a['regla'] }}
                                            </a>
                                        </div>
                                        <div style="font-size: 0.84rem; color: var(--color-texto-2); line-height: 1.4;">
                                            {{ $a['mensaje'] ?? ($a['regla_descripcion'] ?? '') }}
                                        </div>
                                    </td>
                                    <td style="padding: 0.85rem 0.6rem;">
                                        @if($esGlobal)
                                            <div style="font-weight: 600; color: var(--color-texto-1);">
                                                🌐 {{ __('portal.alerts.scope_global') }}
                                            </div>
                                            <div style="font-size: 0.78rem; color: var(--color-texto-3);">
                                                Toda la malla
                                            </div>
                                        @else
                                            <div style="font-weight: 700; color: var(--color-texto-1);">
                                                <a href="/revisa-tu-nodo/{{ $a['nodo_codigo'] }}{{ $langQuery }}" style="color: var(--color-enlace); text-decoration: none;">
                                                    {{ $nombreNodo }}
                                                </a>
                                            </div>
                                            @if($nombreLargo && $nombreLargo !== $nombreNodo)
                                                <div style="font-size: 0.8rem; color: var(--color-texto-2); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 180px;">
                                                    {{ $nombreLargo }}
                                                </div>
                                            @endif
                                            <div style="display: flex; align-items: center; gap: 0.35rem; margin-top: 0.2rem;">
                                                <code style="font-size: 0.75rem; background: var(--color-superficie-sutil); padding: 0.1rem 0.3rem; border-radius: var(--radio-sm);">
                                                    {{ $a['nodo_codigo'] }}
                                                </code>
                                                @if($provincia)
                                                    <span style="font-size: 0.75rem; color: var(--color-texto-3);">· {{ $provincia }}</span>
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                    <td style="padding: 0.85rem 0.6rem; color: var(--color-texto-2); font-size: 0.85rem; white-space: nowrap;">
                                        {{ $a['inicio_at'] ?? '—' }}
                                    </td>
                                    <td style="padding: 0.85rem 0.6rem;">
                                        <div style="display: inline-flex; align-items: center; gap: 0.35rem;">
                                            <span style="font-weight: 700; color: {{ $estadoKey === 'abierta' ? 'var(--color-critico-texto)' : 'var(--mapa-verde)' }};">
                                                {{ $estadoKey === 'abierta' ? '🔴' : '🟢' }} {{ $textoEstado }}
                                            </span>
                                        </div>
                                        @if(($a['reaperturas'] ?? 0) > 0)
                                            <div style="margin-top: 0.25rem;">
                                                <span style="font-size: 0.75rem; background: var(--color-superficie-sutil); border: 1px solid var(--color-borde); padding: 0.1rem 0.4rem; border-radius: var(--radio-sm); color: var(--color-texto-2);">
                                                    {{ __('portal.alerts.reopen_count', ['count' => $a['reaperturas']]) }}
                                                </span>
                                            </div>
                                        @endif
                                    </td>
                                    <td style="padding: 0.85rem 0.6rem; text-align: right; white-space: nowrap;">
                                        <a href="/alertas/{{ $a['id'] }}{{ $langQuery }}" class="btn btn-secundario" style="padding: 0.3rem 0.65rem; font-size: 0.82rem;">
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
            @php
                $reglasFiltradas = $reglas;
                if (!empty($problemaFiltro) && isset($categoriasProblemas[$problemaFiltro])) {
                    $reglasValidas = $categoriasProblemas[$problemaFiltro]['reglas'];
                    $reglasFiltradas = array_values(array_filter($reglas, fn($r) => in_array($r['rule_id'] ?? '', $reglasValidas, true)));
                }
            @endphp
            <div style="display: flex; justify-content: space-between; align-items: baseline; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 1rem;">
                <h2 style="font-size: 1.4rem; font-weight: 700; color: var(--color-texto-1); margin: 0;">
                    {{ __('portal.alerts.catalog_title') }}
                </h2>
                @if(!empty($problemaFiltro) && isset($categoriasProblemas[$problemaFiltro]))
                    <div style="font-size: 0.88rem; color: var(--color-texto-2); display: flex; align-items: center; gap: 0.5rem;">
                        <span>
                            {{ __('portal.alerts.catalog_category_showing', ['count' => count($reglasFiltradas), 'total' => count($reglas)]) }}
                        </span>
                        <a href="{{ $filtroUrl(null, null, '') }}" style="color: var(--color-enlace); font-weight: 600; text-decoration: none;">
                            {{ __('portal.alerts.catalog_view_all') }} →
                        </a>
                    </div>
                @endif
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 300px), 1fr)); gap: 1.25rem;">
                @foreach($reglasFiltradas as $regla)
                    @php
                        $riesgoKey = strtolower((string) ($regla['risk'] ?? 'medio'));
                        $chipTipo = match($riesgoKey) {
                            'alto' => 'critico',
                            'medio' => 'aviso',
                            'bajo' => 'info',
                            default => 'neutro',
                        };
                        $textoRiesgo = __('portal.alerts.risk_levels.' . $riesgoKey);
                        if ($textoRiesgo === 'portal.alerts.risk_levels.' . $riesgoKey) {
                            $textoRiesgo = ucfirst($riesgoKey);
                        }
                        $infoLocal = \App\Http\Controllers\AlertasController::infoRegla($regla['rule_id'] ?? '');
                        $tipoKey = strtolower((string) ($regla['type'] ?? ''));
                        $textoTipo = __('portal.alerts.types.' . $tipoKey);
                        if ($textoTipo === 'portal.alerts.types.' . $tipoKey) {
                            $textoTipo = ucfirst($tipoKey ?: 'malla');
                        }
                    @endphp
                    <div class="tarjeta" style="padding: 1.25rem; background: var(--color-superficie-sutil); display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                            @if(!empty($infoLocal['categoria']))
                                <div style="font-size: 0.72rem; color: var(--color-texto-3); text-transform: uppercase; letter-spacing: 0.05em; font-weight: 700; margin-bottom: 0.4rem;">
                                    {{ $infoLocal['categoria'] }}
                                </div>
                            @endif
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 0.5rem; margin-bottom: 0.5rem;">
                                <h3 style="font-size: 1.05rem; font-weight: 700; color: var(--color-texto-1); margin: 0; display: flex; align-items: center; gap: 0.35rem;">
                                    <span>{{ $infoLocal['icono'] }}</span>
                                    <span>{{ $infoLocal['nombre'] ?? $regla['name'] }}</span>
                                </h3>
                                <x-chip-estado :tipo="$chipTipo" :texto="$textoRiesgo" />
                            </div>
                            <p style="font-size: 0.88rem; color: var(--color-texto-2); line-height: 1.45; margin-bottom: 0.75rem;">
                                {{ $regla['description'] ?? $infoLocal['descripcion'] }}
                            </p>
                        </div>
                        <div style="font-size: 0.8rem; color: var(--color-texto-3); border-top: 1px solid var(--color-borde); padding-top: 0.5rem; display: flex; justify-content: space-between; align-items: center;">
                            <span>{{ __('portal.alerts.type_label') }} <strong style="color: var(--color-texto-2);">{{ $textoTipo }}</strong></span>
                            <code style="font-size: 0.75rem; background: var(--color-superficie); padding: 0.15rem 0.35rem; border-radius: var(--radio-sm); border: 1px solid var(--color-borde);">{{ $regla['rule_id'] }}</code>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    </div>
</x-layout>
