{{--
    Componente: x-tabla-routers
    Tabla unificada de supervisión de repetidores y nodos de infraestructura de la malla.
    Reutilizada idénticamente en la página pública /routers y en el panel de operadores (/admin).
--}}
@props([
    'routers' => [],
    'routersPorProvincia' => [],
    'total' => 0,
    'enAlerta' => 0,
    'errorFuente' => null,
    'ambito' => 'andalucia',
    'filtroProvincia' => '',
    'langQuerySolo' => '',
])

@php
    $ambitoActual = $ambito ?? 'andalucia';
    $ambitoTexto = match($ambitoActual) {
        'ambos' => __('admin.ambitos.andalucia_title') . ' + ' . __('admin.ambitos.espana_title'),
        'espana' => __('admin.ambitos.espana_title'),
        default => __('admin.ambitos.andalucia_title'),
    };

    if (empty($langQuerySolo)) {
        $curLang = app()->getLocale();
        $langQuerySolo = $curLang !== 'es' ? '?lang=' . $curLang : '';
    }
@endphp

<section aria-label="{{ __('admin.widgets.routers.heading') }}">
    <div class="routers-table-section">
        <!-- Cabecera de la tabla -->
        <div class="routers-table-header">
            <div class="routers-table-title-group">
                <span class="routers-table-icon" aria-hidden="true">📡</span>
                <h2 class="routers-table-title">{{ __('admin.widgets.routers.heading') }}</h2>
                <span class="routers-pill routers-pill-neutral">
                    {{ trans_choice('admin.widgets.routers.routers_badge', $total, ['count' => $total]) }} · {{ $ambitoTexto }}
                </span>
                @if($enAlerta > 0)
                    <span class="routers-pill routers-pill-danger">
                        ⚠️ {{ __('admin.widgets.routers.in_attention', ['count' => $enAlerta]) }}
                    </span>
                @endif
            </div>
            <div class="routers-table-subtitle">
                {{ __('admin.widgets.routers.subtitle') }}
            </div>
        </div>

        @if($errorFuente)
            <div class="routers-alert routers-alert-warning" style="margin: 1.5rem;">
                <span aria-hidden="true">ℹ️</span>
                <div>
                    <strong>{{ __('admin.widgets.routers.unavailable_title') }}</strong>
                    <p style="font-size: 0.85rem; margin: 0.25rem 0 0 0; opacity: 0.9;">{{ $errorFuente }}</p>
                </div>
            </div>
        @elseif(empty($routers))
            <div class="routers-empty-state">
                <span style="font-size: 2.5rem;" aria-hidden="true">📻</span>
                <h3 style="font-size: 1.15rem; font-weight: 700; margin: 0.75rem 0 0.25rem 0; color: var(--color-texto, #f8fafc);">{{ __('admin.widgets.routers.empty_title') }}</h3>
                <p style="font-size: 0.9rem; color: var(--color-texto-2, #94a3b8); margin: 0; max-width: 500px;">{{ __('admin.widgets.routers.empty_desc') }}</p>
            </div>
        @else
            <!-- Barra de navegación y filtro rápido por provincia -->
            @if(!empty($routersPorProvincia) && count($routersPorProvincia) > 1)
                <div class="routers-province-nav-container">
                    <div class="routers-province-nav" role="toolbar" aria-label="{{ __('admin.widgets.routers.filter_by_province') }}">
                        <div class="routers-province-nav-title">
                            <svg aria-hidden="true" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                <circle cx="12" cy="10" r="3"></circle>
                            </svg>
                            <span>{{ __('admin.widgets.routers.filter_by_province') }}:</span>
                        </div>
                        <div class="routers-province-pills">
                            <button
                                type="button"
                                class="routers-prov-pill active"
                                data-province="all"
                                aria-pressed="true"
                            >
                                <span>{{ __('admin.widgets.routers.all_provinces') }}</span>
                                <span class="routers-prov-pill-badge">{{ $total }}</span>
                            </button>
                            @foreach($routersPorProvincia as $pCode => $pData)
                                <button
                                    type="button"
                                    class="routers-prov-pill"
                                    data-province="{{ $pData['slug'] }}"
                                    data-province-code="{{ $pData['code'] }}"
                                    data-province-name="{{ $pData['name'] }}"
                                    data-province-count="{{ $pData['count'] }}"
                                    aria-pressed="false"
                                >
                                    <span>{{ $pData['name'] }}</span>
                                    <span class="routers-prov-pill-badge">{{ $pData['count'] }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <!-- Banner de aviso cuando hay filtro por provincia activo -->
                    <div id="routers-province-notice" class="routers-province-notice" style="display: none;">
                        <div class="routers-province-notice-text">
                            <span>📍 {{ __('admin.widgets.routers.showing_province') }}:</span>
                            <strong id="routers-province-notice-target" class="routers-province-notice-target"></strong>
                        </div>
                        <button type="button" id="routers-province-notice-reset" class="routers-province-notice-btn routers-province-notice-reset">
                            ✕ {{ __('admin.widgets.routers.show_all') }}
                        </button>
                    </div>
                </div>
            @endif

            <div class="routers-table-responsive-container">
                <table class="routers-dashboard-table">
                    <thead>
                        <tr>
                            <th class="routers-th">{{ __('admin.widgets.routers.th_router') }}</th>
                            <th class="routers-th">{{ __('admin.widgets.routers.th_hardware') }}</th>
                            <th class="routers-th">{{ __('admin.widgets.routers.th_power') }}</th>
                            <th class="routers-th">{{ __('admin.widgets.routers.th_channel') }}</th>
                            <th class="routers-th">{{ __('admin.widgets.routers.th_tx') }}</th>
                            <th class="routers-th">{{ __('admin.widgets.routers.th_last_report') }}</th>
                            <th class="routers-th" style="text-align: right;">{{ __('admin.widgets.routers.th_action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(!empty($routersPorProvincia))
                            @foreach($routersPorProvincia as $pCode => $pData)
                                <tr class="routers-group-header-row" data-province="{{ $pData['slug'] }}">
                                    <td colspan="7" class="routers-group-header-td">
                                        <div class="routers-group-header-content">
                                            <div class="routers-group-header-title">
                                                <span class="routers-group-icon">📍</span>
                                                <strong class="routers-group-name">{{ $pData['name'] }}</strong>
                                                <span class="routers-group-badge">{{ $pData['code'] }}</span>
                                            </div>
                                            <div class="routers-group-header-stats">
                                                <span class="routers-group-stat-pill">
                                                    <strong>{{ $pData['count'] }}</strong> {{ trans_choice('admin.widgets.routers.routers_badge', $pData['count'], ['count' => $pData['count']]) }}
                                                </span>
                                                <span class="routers-group-stat-pill routers-group-stat-online">
                                                    <span class="routers-status-dot routers-status-dot-online"></span>
                                                    <strong>{{ $pData['online_count'] }}</strong> {{ __('admin.widgets.routers.online') }}
                                                </span>
                                                @if($pData['alert_count'] > 0)
                                                    <span class="routers-group-stat-pill routers-group-stat-alert">
                                                        ⚠️ {{ __('admin.widgets.routers.in_attention_count', ['count' => $pData['alert_count']]) }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @foreach($pData['routers'] as $r)
                                    <tr class="routers-tr" data-province="{{ $pData['slug'] }}">
                                        <!-- Nodo -->
                                        <td class="routers-td">
                                            <div style="display: flex; align-items: center; gap: 0.65rem;">
                                                <span class="routers-status-dot {{ $r['esta_online'] ? 'routers-status-dot-online' : 'routers-status-dot-offline' }}" title="{{ $r['esta_online'] ? __('admin.widgets.routers.online') : __('admin.widgets.routers.offline') }}"></span>
                                                <div style="display: flex; flex-direction: column; gap: 0.15rem;">
                                                    <div style="font-weight: 700; font-size: 0.95rem; color: var(--color-texto, #f8fafc); line-height: 1.2;">
                                                        {{ $r['short_name'] }}
                                                    </div>
                                                    @if(!empty($r['long_name']))
                                                        <div class="routers-node-longname">
                                                            {{ $r['long_name'] }}
                                                        </div>
                                                    @endif
                                                    <div style="font-family: var(--fuente-mono, monospace); font-size: 0.75rem; opacity: 0.65; letter-spacing: 0.04em;">{{ $r['id'] }}</div>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Hardware / Zona -->
                                        <td class="routers-td">
                                            <div style="font-size: 0.85rem; font-weight: 600; color: var(--color-texto, #f8fafc);">
                                                {{ $r['hw_model'] === 'Desconocido' ? __('admin.widgets.routers.unknown') : $r['hw_model'] }}
                                            </div>
                                            <div style="font-size: 0.75rem; opacity: 0.75; font-family: var(--fuente-mono, monospace);">
                                                {{ $r['province_name'] ?? $r['province'] }} · {{ $r['role'] }}
                                            </div>
                                        </td>

                                        <!-- Alimentación y Batería -->
                                        <td class="routers-td">
                                            @if($r['powered'])
                                                <div class="routers-power-badge routers-power-badge-grid">
                                                    <span>⚡ {{ __('admin.widgets.routers.power_grid_solar') }}</span>
                                                    @if($r['voltage'])
                                                        <span style="font-family: var(--fuente-mono, monospace); font-size: 0.7rem; opacity: 0.85;">{{ $r['voltage'] }}V</span>
                                                    @endif
                                                </div>
                                            @elseif($r['battery_level'] !== null)
                                                <div class="routers-battery-container">
                                                    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.8rem; font-family: var(--fuente-mono, monospace); font-weight: 700; margin-bottom: 0.25rem;">
                                                        <span style="color: {{ $r['battery_level'] < 20 ? '#f87171' : ($r['battery_level'] < 50 ? '#fbbf24' : '#4ade80') }};">🔋 {{ $r['battery_level'] }}%</span>
                                                        @if($r['voltage'])
                                                            <span style="font-size: 0.7rem; font-weight: normal; opacity: 0.7;">{{ $r['voltage'] }}V</span>
                                                        @endif
                                                    </div>
                                                    <div class="routers-progress-track">
                                                        <div class="routers-progress-fill routers-progress-{{ $r['bateria_color'] }}" style="width: {{ min(100, max(5, $r['battery_level'])) }}%;"></div>
                                                    </div>
                                                </div>
                                            @else
                                                <span style="font-size: 0.85rem; opacity: 0.5; font-family: var(--fuente-mono, monospace);">—</span>
                                            @endif
                                        </td>

                                        <!-- Presión Aire (ChUtil) -->
                                        <td class="routers-td">
                                            @if($r['chutil'] !== null)
                                                <div class="routers-chutil-container">
                                                    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.8rem; font-family: var(--fuente-mono, monospace); margin-bottom: 0.25rem;">
                                                        <span style="font-weight: 700; color: var(--color-texto, #f8fafc);">{{ $r['chutil'] }}%</span>
                                                        <span class="routers-chip-{{ $r['chutil_color'] }}" style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase;">
                                                            {{ $r['chutil'] < 20 ? __('admin.widgets.routers.chutil_light') : ($r['chutil'] < 40 ? __('admin.widgets.routers.chutil_moderate') : __('admin.widgets.routers.chutil_heavy')) }}
                                                        </span>
                                                    </div>
                                                    <div class="routers-progress-track">
                                                        <div class="routers-progress-fill routers-progress-{{ $r['chutil_color'] }}" style="width: {{ min(100, max(5, $r['chutil'])) }}%;"></div>
                                                    </div>
                                                </div>
                                            @else
                                                <span style="font-size: 0.85rem; opacity: 0.5; font-family: var(--fuente-mono, monospace);">—</span>
                                            @endif
                                        </td>

                                        <!-- Saturación TX -->
                                        <td class="routers-td" style="font-family: var(--fuente-mono, monospace); font-size: 0.85rem;">
                                            @if($airTx = $r['air_tx'])
                                                <span class="routers-text-{{ $r['tx_color'] }}" style="font-weight: 700;">
                                                    {{ $airTx }}% TX
                                                </span>
                                            @else
                                                <span style="opacity: 0.5;">—</span>
                                            @endif
                                        </td>

                                        <!-- Último Reporte -->
                                        <td class="routers-td" style="font-size: 0.85rem;">
                                            @php
                                                $lsDt = !empty($r['last_seen']) ? \Carbon\Carbon::parse($r['last_seen']) : null;
                                            @endphp
                                            <span
                                                style="{{ $r['esta_online'] ? 'opacity: 0.85;' : 'color: #f87171; font-weight: 700;' }}"
                                                @if($lsDt)
                                                    data-title-fecha-local="{{ $lsDt->toIso8601String() }}"
                                                    title="{{ $lsDt->timezone(config('proyecto.zona_horaria', 'Europe/Madrid'))->format('Y-m-d H:i:s') }}"
                                                @endif
                                            >
                                                {{ $r['hace'] }}
                                            </span>
                                        </td>

                                        <!-- Acción -->
                                        <td class="routers-td" style="text-align: right;">
                                            <a
                                                href="/revisa-tu-nodo/{{ ltrim($r['id'], '!') }}{{ $langQuerySolo }}"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="routers-btn-inspect"
                                                title="{{ __('admin.widgets.routers.inspect_title') }}"
                                                aria-label="{{ __('admin.widgets.routers.inspect_title') }}"
                                            >
                                                <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                                    <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                                                </svg>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                        @else
                            @foreach($routers as $r)
                                <tr class="routers-tr">
                                    <td class="routers-td">
                                        <div style="display: flex; align-items: center; gap: 0.65rem;">
                                            <span class="routers-status-dot {{ $r['esta_online'] ? 'routers-status-dot-online' : 'routers-status-dot-offline' }}" title="{{ $r['esta_online'] ? __('admin.widgets.routers.online') : __('admin.widgets.routers.offline') }}"></span>
                                            <div style="display: flex; flex-direction: column; gap: 0.15rem;">
                                                <div style="font-weight: 700; font-size: 0.95rem; color: var(--color-texto, #f8fafc); line-height: 1.2;">
                                                    {{ $r['short_name'] }}
                                                </div>
                                                @if(!empty($r['long_name']))
                                                    <div class="routers-node-longname">
                                                        {{ $r['long_name'] }}
                                                    </div>
                                                @endif
                                                <div style="font-family: var(--fuente-mono, monospace); font-size: 0.75rem; opacity: 0.65; letter-spacing: 0.04em;">{{ $r['id'] }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="routers-td">
                                        <div style="font-size: 0.85rem; font-weight: 600; color: var(--color-texto, #f8fafc);">
                                            {{ $r['hw_model'] === 'Desconocido' ? __('admin.widgets.routers.unknown') : $r['hw_model'] }}
                                        </div>
                                        <div style="font-size: 0.75rem; opacity: 0.75; font-family: var(--fuente-mono, monospace);">
                                            {{ $r['province_name'] ?? $r['province'] }} · {{ $r['role'] }}
                                        </div>
                                    </td>
                                    <td class="routers-td">
                                        @if($r['powered'])
                                            <div class="routers-power-badge routers-power-badge-grid">
                                                <span>⚡ {{ __('admin.widgets.routers.power_grid_solar') }}</span>
                                                @if($r['voltage'])
                                                    <span style="font-family: var(--fuente-mono, monospace); font-size: 0.7rem; opacity: 0.85;">{{ $r['voltage'] }}V</span>
                                                @endif
                                            </div>
                                        @elseif($r['battery_level'] !== null)
                                            <div class="routers-battery-container">
                                                <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.8rem; font-family: var(--fuente-mono, monospace); font-weight: 700; margin-bottom: 0.25rem;">
                                                    <span style="color: {{ $r['battery_level'] < 20 ? '#f87171' : ($r['battery_level'] < 50 ? '#fbbf24' : '#4ade80') }};">🔋 {{ $r['battery_level'] }}%</span>
                                                    @if($r['voltage'])
                                                        <span style="font-size: 0.7rem; font-weight: normal; opacity: 0.7;">{{ $r['voltage'] }}V</span>
                                                    @endif
                                                </div>
                                                <div class="routers-progress-track">
                                                    <div class="routers-progress-fill routers-progress-{{ $r['bateria_color'] }}" style="width: {{ min(100, max(5, $r['battery_level'])) }}%;"></div>
                                                </div>
                                            </div>
                                        @else
                                            <span style="font-size: 0.85rem; opacity: 0.5; font-family: var(--fuente-mono, monospace);">—</span>
                                        @endif
                                    </td>
                                    <td class="routers-td">
                                        @if($r['chutil'] !== null)
                                            <div class="routers-chutil-container">
                                                <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.8rem; font-family: var(--fuente-mono, monospace); margin-bottom: 0.25rem;">
                                                    <span style="font-weight: 700; color: var(--color-texto, #f8fafc);">{{ $r['chutil'] }}%</span>
                                                    <span class="routers-chip-{{ $r['chutil_color'] }}" style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase;">
                                                        {{ $r['chutil'] < 20 ? __('admin.widgets.routers.chutil_light') : ($r['chutil'] < 40 ? __('admin.widgets.routers.chutil_moderate') : __('admin.widgets.routers.chutil_heavy')) }}
                                                    </span>
                                                </div>
                                                <div class="routers-progress-track">
                                                    <div class="routers-progress-fill routers-progress-{{ $r['chutil_color'] }}" style="width: {{ min(100, max(5, $r['chutil'])) }}%;"></div>
                                                </div>
                                            </div>
                                        @else
                                            <span style="font-size: 0.85rem; opacity: 0.5; font-family: var(--fuente-mono, monospace);">—</span>
                                        @endif
                                    </td>
                                    <td class="routers-td" style="font-family: var(--fuente-mono, monospace); font-size: 0.85rem;">
                                        @if($airTx = $r['air_tx'])
                                            <span class="routers-text-{{ $r['tx_color'] }}" style="font-weight: 700;">
                                                {{ $airTx }}% TX
                                            </span>
                                        @else
                                            <span style="opacity: 0.5;">—</span>
                                        @endif
                                    </td>
                                    <td class="routers-td" style="font-size: 0.85rem;">
                                        @php
                                            $lsDt = !empty($r['last_seen']) ? \Carbon\Carbon::parse($r['last_seen']) : null;
                                        @endphp
                                        <span
                                            style="{{ $r['esta_online'] ? 'opacity: 0.85;' : 'color: #f87171; font-weight: 700;' }}"
                                            @if($lsDt)
                                                data-title-fecha-local="{{ $lsDt->toIso8601String() }}"
                                                title="{{ $lsDt->timezone(config('proyecto.zona_horaria', 'Europe/Madrid'))->format('Y-m-d H:i:s') }}"
                                            @endif
                                        >
                                            {{ $r['hace'] }}
                                        </span>
                                    </td>
                                    <td class="routers-td" style="text-align: right;">
                                        <a
                                            href="/revisa-tu-nodo/{{ ltrim($r['id'], '!') }}{{ $langQuerySolo }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="routers-btn-inspect"
                                            title="{{ __('admin.widgets.routers.inspect_title') }}"
                                            aria-label="{{ __('admin.widgets.routers.inspect_title') }}"
                                        >
                                            <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                                            </svg>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</section>

@once
    <style>
        /* ==============================================================================
         * Componente x-tabla-routers: Estilos visuales comunes (público y administración)
         * ============================================================================== */
        .routers-table-section {
            background: #181924;
            border: 1px solid rgba(148, 163, 184, 0.16);
            border-radius: 0.85rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);
            overflow: hidden;
            width: 100%;
        }
        .routers-table-header {
            padding: 1.5rem 1.75rem;
            background: #151620;
            border-bottom: 1px solid rgba(148, 163, 184, 0.14);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .routers-table-title-group {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.65rem;
        }
        .routers-table-icon {
            font-size: 1.35rem;
        }
        .routers-table-title {
            font-size: 1.2rem;
            font-weight: 800;
            color: #f8fafc;
            margin: 0;
            letter-spacing: -0.01em;
        }
        .routers-table-subtitle {
            font-size: 0.82rem;
            color: #94a3b8;
        }

        .routers-pill {
            font-size: 0.72rem;
            font-weight: 700;
            padding: 0.25rem 0.65rem;
            border-radius: 9999px;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }
        .routers-pill-neutral {
            background: #2a2c3d;
            color: #cbd5e1;
        }
        .routers-pill-danger {
            background: rgba(239, 68, 68, 0.18);
            color: #fca5a5;
            border: 1px solid rgba(239, 68, 68, 0.35);
        }

        .routers-table-responsive-container {
            width: 100%;
            overflow-x: auto;
        }
        .routers-dashboard-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 0.9rem;
        }
        .routers-th {
            padding: 0.95rem 1.25rem;
            font-size: 0.72rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #94a3b8;
            background: #151620;
            border-bottom: 1px solid rgba(148, 163, 184, 0.12);
            white-space: nowrap;
        }
        .routers-td {
            padding: 1.1rem 1.25rem;
            border-bottom: 1px solid rgba(148, 163, 184, 0.08);
            color: #e2e8f0;
            vertical-align: middle;
        }
        .routers-tr:hover {
            background: rgba(255, 255, 255, 0.02);
        }
        .routers-tr:last-child .routers-td {
            border-bottom: none;
        }

        /* Indicador de estado online / offline */
        .routers-status-dot {
            width: 9px;
            height: 9px;
            border-radius: 50%;
            display: inline-block;
            flex-shrink: 0;
        }
        .routers-status-dot-online {
            background: #22c55e;
            box-shadow: 0 0 8px rgba(34, 197, 94, 0.6);
        }
        .routers-status-dot-offline {
            background: #ef4444;
            box-shadow: 0 0 6px rgba(239, 68, 68, 0.4);
        }

        /* Badges de alimentación */
        .routers-power-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.3rem 0.65rem;
            border-radius: 0.375rem;
            font-size: 0.78rem;
            font-weight: 700;
        }
        .routers-power-badge-grid {
            background: rgba(16, 185, 129, 0.12);
            color: #34d399;
            border: 1px solid rgba(16, 185, 129, 0.25);
        }

        /* Nombre largo del nodo secundario */
        .routers-node-longname {
            font-size: 0.78rem;
            font-weight: 600;
            line-height: 1.25;
            color: #007A33;
        }
        [data-theme="dark"] .routers-node-longname,
        .dark .routers-node-longname {
            color: #67EA94;
        }

        /* Barras de progreso de batería y ChUtil */
        .routers-battery-container,
        .routers-chutil-container {
            min-width: 95px;
            max-width: 115px;
        }
        .routers-progress-track {
            height: 6px;
            width: 100%;
            background: #282a3a;
            border-radius: 9999px;
            overflow: hidden;
        }
        .routers-progress-fill {
            height: 100%;
            border-radius: 9999px;
            transition: width 0.3s ease;
        }
        .routers-progress-verde {
            background: #22c55e;
        }
        .routers-progress-amarillo {
            background: #eab308;
        }
        .routers-progress-rojo {
            background: #ef4444;
        }

        /* Chips y textos por color */
        .routers-chip-verde {
            color: #4ade80;
        }
        .routers-chip-amarillo {
            color: #f59e0b;
        }
        .routers-chip-rojo {
            color: #ef4444;
        }
        .routers-text-verde {
            color: #4ade80;
        }
        .routers-text-amarillo {
            color: #f59e0b;
        }
        .routers-text-rojo {
            color: #ef4444;
        }

        /* Botón de inspección y diagnóstico */
        .routers-btn-inspect {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.25rem;
            height: 2.25rem;
            background: rgba(0, 122, 51, 0.08);
            border: 1px solid rgba(0, 122, 51, 0.25);
            border-radius: 0.45rem;
            color: #007A33;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        .routers-btn-inspect:hover {
            background: #007A33;
            border-color: #007A33;
            color: #ffffff;
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0, 122, 51, 0.35);
        }
        [data-theme="dark"] .routers-btn-inspect,
        .dark .routers-btn-inspect {
            background: rgba(103, 234, 148, 0.1);
            border-color: rgba(103, 234, 148, 0.3);
            color: #67EA94;
        }
        [data-theme="dark"] .routers-btn-inspect:hover,
        .dark .routers-btn-inspect:hover {
            background: #67EA94;
            border-color: #67EA94;
            color: #1F2029;
            box-shadow: 0 0 10px rgba(103, 234, 148, 0.4);
        }

        /* Estados vacíos y alertas */
        .routers-empty-state {
            padding: 4rem 2rem;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        .routers-alert {
            display: flex;
            align-items: flex-start;
            gap: 0.85rem;
            padding: 1.15rem 1.35rem;
            border-radius: 0.65rem;
        }
        .routers-alert-warning {
            background: rgba(245, 158, 11, 0.12);
            border: 1px solid rgba(245, 158, 11, 0.3);
            color: #fde68a;
        }

        /* Navegación Rápida por Provincias y Agrupaciones */
        .routers-province-nav-container {
            padding: 1.15rem 1.5rem 0.85rem 1.5rem;
            background: #151620;
            border-bottom: 1px solid rgba(148, 163, 184, 0.12);
            display: flex;
            flex-direction: column;
            gap: 0.85rem;
        }
        .routers-province-nav-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
        }
        .routers-province-nav-label {
            font-size: 0.75rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #94a3b8;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
        }
        .routers-province-nav {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.45rem;
        }
        .routers-prov-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.35rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.82rem;
            font-weight: 600;
            color: #cbd5e1;
            background: #1f212f;
            border: 1px solid rgba(148, 163, 184, 0.2);
            cursor: pointer;
            transition: all 0.2s ease;
            user-select: none;
            line-height: 1.2;
        }
        .routers-prov-pill:hover {
            color: #ffffff;
            border-color: rgba(103, 234, 148, 0.45);
            background: #25283a;
            transform: translateY(-1px);
        }
        .routers-prov-pill.active {
            background: rgba(0, 122, 51, 0.28);
            border-color: #67EA94;
            color: #ffffff;
            font-weight: 700;
            box-shadow: 0 0 12px rgba(103, 234, 148, 0.25);
        }
        .routers-prov-pill-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 1.25rem;
            height: 1.25rem;
            padding: 0 0.35rem;
            border-radius: 9999px;
            font-size: 0.72rem;
            font-weight: 800;
            font-family: var(--fuente-mono, monospace);
            background: rgba(148, 163, 184, 0.18);
            color: #e2e8f0;
        }
        .routers-prov-pill.active .routers-prov-pill-badge {
            background: #007A33;
            color: #ffffff;
        }
        [data-theme="dark"] .routers-prov-pill.active .routers-prov-pill-badge,
        .dark .routers-prov-pill.active .routers-prov-pill-badge {
            background: #67EA94;
            color: #151620;
        }

        /* Banner de aviso al filtrar */
        .routers-province-notice {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 0.75rem;
            padding: 0.6rem 0.95rem;
            background: rgba(0, 122, 51, 0.12);
            border: 1px solid rgba(103, 234, 148, 0.35);
            border-radius: 0.5rem;
            color: #e2e8f0;
            font-size: 0.85rem;
        }
        .routers-province-notice-text {
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }
        .routers-province-notice-text strong {
            color: #67EA94;
            font-weight: 800;
        }
        .routers-province-notice-btn {
            background: transparent;
            border: 1px solid rgba(148, 163, 184, 0.3);
            color: #cbd5e1;
            padding: 0.2rem 0.6rem;
            border-radius: 0.375rem;
            font-size: 0.78rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .routers-province-notice-btn:hover {
            background: rgba(239, 68, 68, 0.15);
            border-color: rgba(239, 68, 68, 0.4);
            color: #fca5a5;
        }

        /* Filas de cabecera de grupo por provincia */
        .routers-group-header-row {
            background: #11121a;
            border-top: 1px solid rgba(148, 163, 184, 0.14);
            border-bottom: 1px solid rgba(148, 163, 184, 0.14);
        }
        .routers-group-header-td {
            padding: 0.85rem 1.25rem;
        }
        .routers-group-header-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 0.75rem;
        }
        .routers-group-header-title {
            display: flex;
            align-items: center;
            gap: 0.55rem;
        }
        .routers-group-icon {
            font-size: 1.05rem;
        }
        .routers-group-name {
            font-size: 0.98rem;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: -0.01em;
        }
        .routers-group-badge {
            font-size: 0.72rem;
            font-family: var(--fuente-mono, monospace);
            font-weight: 700;
            padding: 0.15rem 0.45rem;
            border-radius: 0.25rem;
            background: rgba(148, 163, 184, 0.14);
            color: #94a3b8;
        }
        .routers-group-header-stats {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        .routers-group-stat-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.2rem 0.55rem;
            border-radius: 9999px;
            background: rgba(148, 163, 184, 0.1);
            color: #cbd5e1;
        }
        .routers-group-stat-online {
            background: rgba(34, 197, 94, 0.12);
            color: #86efac;
            border: 1px solid rgba(34, 197, 94, 0.25);
        }
        .routers-group-stat-alert {
            background: rgba(239, 68, 68, 0.14);
            color: #fca5a5;
            border: 1px solid rgba(239, 68, 68, 0.28);
        }
    </style>

    <script>
        (function () {
            function applyProvinceFilter(container, selectedSlug, updateUrl) {
                if (!container) return;
                if (!selectedSlug) {
                    selectedSlug = 'all';
                }

                var pills = container.querySelectorAll('.routers-prov-pill');
                var notice = container.querySelector('.routers-province-notice');
                var noticeTarget = container.querySelector('.routers-province-notice-target');
                var groupHeaders = container.querySelectorAll('.routers-group-header-row');
                var routerRows = container.querySelectorAll('.routers-tr');

                // 1. Activar la píldora correspondiente
                var matchedPill = null;
                pills.forEach(function (pill) {
                    var pillSlug = pill.getAttribute('data-province');
                    if (pillSlug === selectedSlug) {
                        pill.classList.add('active');
                        pill.setAttribute('aria-pressed', 'true');
                        matchedPill = pill;
                    } else {
                        pill.classList.remove('active');
                        pill.setAttribute('aria-pressed', 'false');
                    }
                });

                // Si no coincide con ninguna píldora, volver a 'all'
                if (!matchedPill && selectedSlug !== 'all') {
                    applyProvinceFilter(container, 'all', updateUrl);
                    return;
                }

                // 2. Mostrar/ocultar filas y cabeceras
                if (selectedSlug === 'all') {
                    groupHeaders.forEach(function (header) {
                        header.style.display = '';
                    });
                    routerRows.forEach(function (row) {
                        row.style.display = '';
                    });
                    if (notice) {
                        notice.style.display = 'none';
                    }
                } else {
                    groupHeaders.forEach(function (header) {
                        var hSlug = header.getAttribute('data-province');
                        header.style.display = (hSlug === selectedSlug) ? '' : 'none';
                    });
                    routerRows.forEach(function (row) {
                        var rSlug = row.getAttribute('data-province');
                        row.style.display = (rSlug === selectedSlug) ? '' : 'none';
                    });

                    if (notice && matchedPill) {
                        var provName = matchedPill.getAttribute('data-province-name') || selectedSlug;
                        var provCount = matchedPill.getAttribute('data-province-count') || '';
                        if (noticeTarget) {
                            noticeTarget.textContent = provName + (provCount ? ' (' + provCount + ')' : '');
                        }
                        notice.style.display = 'flex';
                    }
                }

                // 3. Actualizar URL con history.replaceState sin recarga y con cero cookies (RN-06)
                if (updateUrl !== false) {
                    try {
                        var url = new URL(window.location.href);
                        if (selectedSlug === 'all') {
                            url.searchParams.delete('provincia');
                        } else {
                            url.searchParams.set('provincia', selectedSlug);
                        }
                        window.history.replaceState({}, '', url.toString());
                    } catch (e) {}
                }
            }

            function syncAllRoutersTables() {
                var initialProv = null;
                try {
                    var url = new URL(window.location.href);
                    initialProv = url.searchParams.get('provincia');
                    if (!initialProv && window.location.hash) {
                        initialProv = window.location.hash.replace(/^#/, '').toLowerCase();
                    }
                } catch (e) {}

                document.querySelectorAll('.routers-table-section').forEach(function (container) {
                    if (initialProv) {
                        applyProvinceFilter(container, initialProv.toLowerCase(), false);
                    }
                });

                if (typeof window.formatearFechasLocales === 'function') {
                    window.formatearFechasLocales();
                }
            }

            // Delegación de eventos en document para funcionar tanto en navegación estándar como con Livewire
            if (!window.__tablaRoutersClickBound) {
                window.__tablaRoutersClickBound = true;

                document.addEventListener('click', function (e) {
                    var pill = e.target.closest('.routers-prov-pill');
                    if (pill) {
                        var container = pill.closest('.routers-table-section');
                        if (container) {
                            var slug = pill.getAttribute('data-province') || 'all';
                            applyProvinceFilter(container, slug, true);
                        }
                        return;
                    }

                    var resetBtn = e.target.closest('.routers-province-notice-reset');
                    if (resetBtn) {
                        var container = resetBtn.closest('.routers-table-section');
                        if (container) {
                            applyProvinceFilter(container, 'all', true);
                        }
                        return;
                    }
                });
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', syncAllRoutersTables);
            } else {
                syncAllRoutersTables();
            }

            document.addEventListener('livewire:navigated', syncAllRoutersTables);
            document.addEventListener('livewire:initialized', function () {
                if (window.Livewire && typeof window.Livewire.hook === 'function') {
                    window.Livewire.hook('morph.updated', function () {
                        setTimeout(syncAllRoutersTables, 15);
                    });
                }
            });
        })();
    </script>
@endonce
