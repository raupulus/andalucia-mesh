@php
    $currentLang = app()->getLocale();
    $langParam = $currentLang !== 'es' ? '&lang=' . $currentLang : '';
    $langQuery = $currentLang !== 'es' ? '?lang=' . $currentLang : '';

    $problemasSeleccionados = (array) ($problemasFiltro ?? []);

    $filtroUrl = function(
        ?string $nuevoEstado = null,
        ?string $nuevoRiesgo = null,
        ?string $toggleProblema = null,
        bool $limpiarProblemas = false
    ) use ($estadoFiltro, $riesgoFiltro, $problemasSeleccionados, $langParam) {
        $params = [];

        // Estado
        $e = $nuevoEstado !== null ? $nuevoEstado : $estadoFiltro;
        if (!empty($e)) {
            $params['estado'] = $e;
        }

        // Riesgo
        $r = $nuevoRiesgo !== null ? $nuevoRiesgo : $riesgoFiltro;
        if (!empty($r)) {
            $params['riesgo'] = $r;
        }

        // Temáticas (multi-selección)
        $pList = $problemasSeleccionados;
        if ($limpiarProblemas) {
            $pList = [];
        } elseif ($toggleProblema !== null) {
            if (in_array($toggleProblema, $pList, true)) {
                $pList = array_values(array_diff($pList, [$toggleProblema]));
            } else {
                $pList[] = $toggleProblema;
            }
        }
        if (!empty($pList)) {
            $params['problemas'] = implode(',', $pList);
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

    $hayFiltrosActivos = !empty($estadoFiltro) || !empty($riesgoFiltro) || !empty($problemasSeleccionados);
@endphp

<x-layout :title="__('portal.alerts.meta_title')" :description="__('portal.alerts.meta_description')" :image="asset('img/og/og-alertas.webp')">
    <style>
        .grid-tematicas-filtro {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
            gap: 0.6rem;
        }
        @media (max-width: 540px) {
            .grid-tematicas-filtro {
                grid-template-columns: repeat(2, 1fr) !important;
            }
        }
        .tarjeta-filtro-tematica {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 0.75rem 0.5rem;
            min-height: 72px;
            border-radius: var(--radio-md);
            border: 1px solid var(--color-borde);
            background: var(--color-superficie-sutil);
            color: var(--color-texto-1);
            text-decoration: none;
            position: relative;
            transition: transform 0.1s ease, border-color 0.15s ease, background 0.15s ease;
            user-select: none;
        }
        .tarjeta-filtro-tematica:hover {
            border-color: var(--color-borde-control);
            transform: translateY(-1px);
        }
        .tarjeta-filtro-tematica.activa {
            background: var(--color-acento-suave) !important;
            border: 2px solid var(--color-enlace) !important;
            box-shadow: 0 0 0 1px var(--color-enlace);
        }
    </style>

    <div class="contenedor seccion">
        <header style="margin-bottom: 2rem;">
            <h1 style="font-size: 2.25rem; font-weight: 800; color: var(--color-texto-1); margin-bottom: 0.5rem;">
                {{ __('portal.alerts.heading') }}
            </h1>
            <p class="lead" style="margin-bottom: 1.5rem;">
                {{ __('portal.alerts.lead') }}
            </p>
        </header>

        <!-- Contenedor dinámico interactivo (AJAX / sin recarga completa) -->
        <div id="alertas-app">
            <!-- Panel oxigenado de filtros -->
            <div class="tarjeta" style="padding: 1.5rem; margin-bottom: 2rem; background: var(--color-superficie); border: 1px solid var(--color-borde); border-radius: var(--radio-md);">
                
                <!-- Nivel 1: Filtros de Control (Estado y Severidad) -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1.25rem; align-items: flex-start; padding-bottom: 1.25rem; border-bottom: 1px solid var(--color-borde);">
                    
                    <!-- Estado de la incidencia -->
                    <div>
                        <span style="display: block; font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; color: var(--color-texto-3); margin-bottom: 0.5rem;">
                            {{ __('portal.alerts.status_label') }}
                        </span>
                        <div style="display: flex; flex-wrap: wrap; gap: 0.4rem;">
                            <a href="{{ $filtroUrl('', null) }}" data-filtro-action="1" class="btn {{ empty($estadoFiltro) ? 'btn-primario' : 'btn-secundario' }}" style="padding: 0.35rem 0.75rem; font-size: 0.82rem;">
                                {{ __('portal.alerts.filter_all') }}
                            </a>
                            <a href="{{ $filtroUrl('abierta', null) }}" data-filtro-action="1" class="btn {{ $estadoFiltro === 'abierta' ? 'btn-primario' : 'btn-secundario' }}" style="padding: 0.35rem 0.75rem; font-size: 0.82rem;">
                                🔴 {{ __('portal.alerts.states.abierta') }}
                            </a>
                            <a href="{{ $filtroUrl('resuelta', null) }}" data-filtro-action="1" class="btn {{ $estadoFiltro === 'resuelta' ? 'btn-primario' : 'btn-secundario' }}" style="padding: 0.35rem 0.75rem; font-size: 0.82rem;">
                                🟢 {{ __('portal.alerts.states.resuelta') }}
                            </a>
                        </div>
                    </div>

                    <!-- Severidad / Riesgo (palabra riesgo recortada para máxima claridad en móvil) -->
                    <div>
                        <span style="display: block; font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; color: var(--color-texto-3); margin-bottom: 0.5rem;">
                            {{ __('portal.alerts.risk_label') }}
                        </span>
                        <div style="display: flex; flex-wrap: wrap; gap: 0.4rem;">
                            <a href="{{ $filtroUrl(null, '') }}" data-filtro-action="1" class="btn {{ empty($riesgoFiltro) ? 'btn-primario' : 'btn-secundario' }}" style="padding: 0.35rem 0.75rem; font-size: 0.82rem;">
                                {{ __('portal.alerts.filter_all') }}
                            </a>
                            <a href="{{ $filtroUrl(null, 'alto') }}" data-filtro-action="1" class="btn {{ $riesgoFiltro === 'alto' ? 'btn-primario' : 'btn-secundario' }}" style="padding: 0.35rem 0.75rem; font-size: 0.82rem;">
                                🔴 {{ __('portal.alerts.filter_high') }}
                            </a>
                            <a href="{{ $filtroUrl(null, 'medio') }}" data-filtro-action="1" class="btn {{ $riesgoFiltro === 'medio' ? 'btn-primario' : 'btn-secundario' }}" style="padding: 0.35rem 0.75rem; font-size: 0.82rem;">
                                🟠 {{ __('portal.alerts.filter_medium') }}
                            </a>
                            <a href="{{ $filtroUrl(null, 'bajo') }}" data-filtro-action="1" class="btn {{ $riesgoFiltro === 'bajo' ? 'btn-primario' : 'btn-secundario' }}" style="padding: 0.35rem 0.75rem; font-size: 0.82rem;">
                                🔵 {{ __('portal.alerts.filter_low') }}
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Nivel 2: Temáticas de problemas (cuadradas, a ancho completo y multi-selección instantánea) -->
                <div style="padding-top: 1.25rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem; flex-wrap: wrap; gap: 0.5rem;">
                        <span style="font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; color: var(--color-texto-3);">
                            🏷️ {{ __('portal.alerts.problem_label') }}
                        </span>
                        <a href="{{ $filtroUrl(null, null, null, true) }}" data-filtro-action="1" class="btn {{ empty($problemasSeleccionados) ? 'btn-primario' : 'btn-secundario' }}" style="padding: 0.3rem 0.75rem; font-size: 0.8rem; border-radius: var(--radio-sm);">
                            🔘 {{ __('portal.alerts.filter_all_problems') }}
                        </a>
                    </div>

                    <!-- Rejilla estructurada de tarjetas temáticas -->
                    <div class="grid-tematicas-filtro">
                        @foreach($categoriasProblemas as $slug => $cat)
                            @php
                                $nombreTraducido = __('portal.alerts.problem_types.' . $slug);
                                if ($nombreTraducido === 'portal.alerts.problem_types.' . $slug) {
                                    $nombreTraducido = $cat['nombre'];
                                }
                                $activo = in_array($slug, $problemasSeleccionados, true);
                            @endphp
                            <a href="{{ $filtroUrl(null, null, $slug) }}" data-filtro-action="1" class="tarjeta-filtro-tematica {{ $activo ? 'activa' : '' }}" title="{{ $nombreTraducido }}">
                                <span style="font-size: 1.4rem; line-height: 1; margin-bottom: 0.35rem;" aria-hidden="true">{{ $cat['icono'] }}</span>
                                <span class="nombre-tematica" style="font-size: 0.8rem; font-weight: {{ $activo ? '700' : '600' }}; line-height: 1.25;">{{ $nombreTraducido }}</span>
                                @if($activo)
                                    <span style="position: absolute; top: 4px; right: 6px; font-size: 0.7rem; font-weight: 800; color: var(--color-enlace);" aria-hidden="true">✓</span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>

                <!-- Nivel 3: Barra de Filtros Activos (solo visible si hay filtros aplicados) -->
                @if($hayFiltrosActivos)
                    <div style="margin-top: 1.25rem; padding-top: 1rem; border-top: 1px dashed var(--color-borde); display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 0.75rem; font-size: 0.85rem;">
                        <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 0.45rem;">
                            <span style="font-weight: 700; color: var(--color-texto-2); margin-right: 0.25rem;">
                                {{ __('portal.alerts.active_filters') }}
                            </span>
                            @if(!empty($estadoFiltro))
                                <a href="{{ $filtroUrl('', null) }}" data-filtro-action="1" class="badge" style="background: var(--color-superficie-sutil); border: 1px solid var(--color-borde); padding: 0.2rem 0.5rem; border-radius: var(--radio-sm); color: var(--color-texto-1); text-decoration: none; display: inline-flex; align-items: center; gap: 0.35rem;">
                                    <span>{{ $estadoFiltro === 'abierta' ? '🔴' : '🟢' }} {{ __('portal.alerts.states.' . $estadoFiltro) }}</span>
                                    <span style="color: var(--color-texto-3); font-weight: 700;">✕</span>
                                </a>
                            @endif
                            @if(!empty($riesgoFiltro))
                                <a href="{{ $filtroUrl(null, '') }}" data-filtro-action="1" class="badge" style="background: var(--color-superficie-sutil); border: 1px solid var(--color-borde); padding: 0.2rem 0.5rem; border-radius: var(--radio-sm); color: var(--color-texto-1); text-decoration: none; display: inline-flex; align-items: center; gap: 0.35rem;">
                                    <span>{{ __('portal.alerts.risk_label') }} {{ __('portal.alerts.risk_levels.' . $riesgoFiltro) }}</span>
                                    <span style="color: var(--color-texto-3); font-weight: 700;">✕</span>
                                </a>
                            @endif
                            @foreach($problemasSeleccionados as $p)
                                @if(isset($categoriasProblemas[$p]))
                                    @php
                                        $pNombre = __('portal.alerts.problem_types.' . $p);
                                        if ($pNombre === 'portal.alerts.problem_types.' . $p) {
                                            $pNombre = $categoriasProblemas[$p]['nombre'];
                                        }
                                    @endphp
                                    <a href="{{ $filtroUrl(null, null, $p) }}" data-filtro-action="1" class="badge" style="background: var(--color-superficie-sutil); border: 1px solid var(--color-borde); padding: 0.2rem 0.5rem; border-radius: var(--radio-sm); color: var(--color-texto-1); text-decoration: none; display: inline-flex; align-items: center; gap: 0.35rem;">
                                        <span>{{ $categoriasProblemas[$p]['icono'] }} {{ $pNombre }}</span>
                                        <span style="color: var(--color-texto-3); font-weight: 700;">✕</span>
                                    </a>
                                @endif
                            @endforeach
                        </div>

                        <div style="display: flex; align-items: center; gap: 1rem;">
                            @php
                                $totalIncidencias = ($alertas instanceof \Illuminate\Pagination\LengthAwarePaginator) ? $alertas->total() : count($alertas);
                            @endphp
                            <span style="color: var(--color-texto-2); font-weight: 600;">
                                {{ trans_choice('portal.alerts.showing_alerts_count', $totalIncidencias, ['count' => $totalIncidencias]) }}
                            </span>
                            <a href="{{ $filtroUrl('', '', null, true) }}" data-filtro-action="1" style="font-weight: 700; color: var(--color-critico-texto, #9B1E15); text-decoration: none; font-size: 0.82rem;">
                                🗑️ {{ __('portal.alerts.clear_filters') }}
                            </a>
                        </div>
                    </div>
                @endif
            </div>

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
            @elseif(($alertas instanceof \Illuminate\Pagination\LengthAwarePaginator ? $alertas->total() : count($alertas)) === 0)
                <div class="tarjeta" style="padding: 2.5rem 1.5rem; margin-bottom: 3rem; background: var(--color-superficie-sutil); border: 1px solid var(--color-borde); text-align: center; border-radius: var(--radio-md);">
                    <div style="font-size: 2.25rem; margin-bottom: 0.5rem;" aria-hidden="true">🔍</div>
                    <h2 style="font-size: 1.2rem; font-weight: 700; color: var(--color-texto-1); margin-bottom: 0.5rem;">
                        {{ __('portal.alerts.no_alerts_with_filters') }}
                    </h2>
                    @if($hayFiltrosActivos)
                        <p style="color: var(--color-texto-2); font-size: 0.92rem; margin-bottom: 1.25rem;">
                            No existen incidencias que coincidan con la combinación de filtros seleccionada.
                        </p>
                        <a href="{{ $filtroUrl('', '', null, true) }}" data-filtro-action="1" class="btn btn-secundario" style="font-size: 0.85rem; padding: 0.4rem 1rem;">
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

                    <!-- Controles accesibles de paginación (más reciente al principio) -->
                    @if($alertas instanceof \Illuminate\Pagination\LengthAwarePaginator && $alertas->hasPages())
                        <nav role="navigation" aria-label="Paginación de alertas" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-top: 1.5rem; padding-top: 1.25rem; border-top: 1px solid var(--color-borde);">
                            <div style="font-size: 0.85rem; color: var(--color-texto-2);">
                                Mostrando <strong>{{ $alertas->firstItem() }}</strong> a <strong>{{ $alertas->lastItem() }}</strong> de <strong>{{ $alertas->total() }}</strong> incidencias
                            </div>
                            <div style="display: flex; align-items: center; gap: 0.35rem; flex-wrap: wrap;">
                                @if($alertas->onFirstPage())
                                    <span class="btn btn-secundario" style="opacity: 0.45; pointer-events: none; padding: 0.35rem 0.65rem; font-size: 0.82rem;">← Anterior</span>
                                @else
                                    <a href="{{ $alertas->previousPageUrl() }}" data-filtro-action="1" class="btn btn-secundario" style="padding: 0.35rem 0.65rem; font-size: 0.82rem;">← Anterior</a>
                                @endif

                                @foreach(range(1, $alertas->lastPage()) as $p)
                                    @if($p == $alertas->currentPage())
                                        <span class="btn btn-primario" style="padding: 0.35rem 0.65rem; font-size: 0.82rem; min-width: 32px; text-align: center;">{{ $p }}</span>
                                    @elseif($p == 1 || $p == $alertas->lastPage() || abs($p - $alertas->currentPage()) <= 2)
                                        <a href="{{ $alertas->url($p) }}" data-filtro-action="1" class="btn btn-secundario" style="padding: 0.35rem 0.65rem; font-size: 0.82rem; min-width: 32px; text-align: center;">{{ $p }}</a>
                                    @elseif(abs($p - $alertas->currentPage()) == 3)
                                        <span style="padding: 0 0.25rem; color: var(--color-texto-3);">…</span>
                                    @endif
                                @endforeach

                                @if($alertas->hasMorePages())
                                    <a href="{{ $alertas->nextPageUrl() }}" data-filtro-action="1" class="btn btn-secundario" style="padding: 0.35rem 0.65rem; font-size: 0.82rem;">Siguiente →</a>
                                @else
                                    <span class="btn btn-secundario" style="opacity: 0.45; pointer-events: none; padding: 0.35rem 0.65rem; font-size: 0.82rem;">Siguiente →</span>
                                @endif
                            </div>
                        </nav>
                    @endif
                </div>
            @endif

            <!-- Catálogo de anomalías monitorizadas (filtrado dinámico si hay temáticas seleccionadas) -->
            <section>
                @php
                    $reglasFiltradas = $reglas;
                    if (!empty($problemasSeleccionados)) {
                        $reglasValidas = [];
                        foreach ($problemasSeleccionados as $p) {
                            if (isset($categoriasProblemas[$p])) {
                                $reglasValidas = array_merge($reglasValidas, $categoriasProblemas[$p]['reglas']);
                            }
                        }
                        $reglasValidas = array_unique($reglasValidas);
                        $reglasFiltradas = array_values(array_filter($reglas, fn($r) => in_array($r['rule_id'] ?? '', $reglasValidas, true)));
                    }
                @endphp
                <div style="display: flex; justify-content: space-between; align-items: baseline; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 1rem;">
                    <h2 style="font-size: 1.4rem; font-weight: 700; color: var(--color-texto-1); margin: 0;">
                        {{ __('portal.alerts.catalog_title') }}
                    </h2>
                    @if(!empty($problemasSeleccionados))
                        <div style="font-size: 0.88rem; color: var(--color-texto-2); display: flex; align-items: center; gap: 0.5rem;">
                            <span>
                                {{ __('portal.alerts.catalog_category_showing', ['count' => count($reglasFiltradas), 'total' => count($reglas)]) }}
                            </span>
                            <a href="{{ $filtroUrl(null, null, null, true) }}" data-filtro-action="1" style="color: var(--color-enlace); font-weight: 600; text-decoration: none;">
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
    </div>

    <!-- Script de navegación instantánea sin recarga completa de página -->
    <script>
        (function() {
            let abortCtrl = null;

            document.addEventListener('click', function(e) {
                const link = e.target.closest('#alertas-app a[data-filtro-action]');
                if (!link) return;

                // Permitir comportamiento nativo si el usuario usa Ctrl / Cmd / Shift
                if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;

                const url = link.getAttribute('href');
                if (!url) return;

                e.preventDefault();
                cargarVistaAsincrona(url, true);
            });

            window.addEventListener('popstate', function() {
                cargarVistaAsincrona(window.location.href, false);
            });

            function cargarVistaAsincrona(url, pushState) {
                const app = document.getElementById('alertas-app');
                if (!app) {
                    window.location.href = url;
                    return;
                }

                if (abortCtrl) {
                    abortCtrl.abort();
                }
                abortCtrl = new AbortController();

                app.style.opacity = '0.55';
                app.style.pointerEvents = 'none';

                fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    signal: abortCtrl.signal
                })
                .then(function(res) {
                    if (!res.ok) {
                        throw new Error('HTTP ' + res.status);
                    }
                    return res.text();
                })
                .then(function(html) {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const nuevoContenido = doc.getElementById('alertas-app');
                    if (nuevoContenido) {
                        app.innerHTML = nuevoContenido.innerHTML;
                        if (pushState) {
                            window.history.pushState(null, '', url);
                        }
                    } else {
                        window.location.href = url;
                    }
                })
                .catch(function(err) {
                    if (err.name !== 'AbortError') {
                        window.location.href = url;
                    }
                })
                .finally(function() {
                    app.style.opacity = '1';
                    app.style.pointerEvents = 'auto';
                });
            }
        })();
    </script>
</x-layout>
