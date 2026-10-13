@php
    $currentLang = app()->getLocale();
    $langQuery = $currentLang !== 'es' ? '&lang=' . $currentLang : '';
    $langQuerySolo = $currentLang !== 'es' ? '?lang=' . $currentLang : '';

    // Enlaces dinámicos para alternar el ámbito acumulativo
    // 1. Si estamos en 'andalucia': al pulsar Andalucía no hace nada, al pulsar España suma ambos ('ambos')
    // 2. Si estamos en 'espana': al pulsar España no hace nada, al pulsar Andalucía suma ambos ('ambos')
    // 3. Si estamos en 'ambos': al pulsar Andalucía queda 'espana', al pulsar España queda 'andalucia'
    $urlToggleAndalucia = match($ambito) {
        'ambos' => '/routers?ambito=espana' . $langQuery,
        'espana' => '/routers?ambito=ambos' . $langQuery,
        default => '/routers?ambito=andalucia' . $langQuery,
    };

    $urlToggleEspana = match($ambito) {
        'ambos' => '/routers?ambito=andalucia' . $langQuery,
        'andalucia' => '/routers?ambito=ambos' . $langQuery,
        default => '/routers?ambito=espana' . $langQuery,
    };

    $activoAndalucia = in_array($ambito, ['andalucia', 'ambos'], true);
    $activoEspana = in_array($ambito, ['espana', 'ambos'], true);

    $ambitoTexto = match($ambito) {
        'ambos' => __('admin.ambitos.andalucia_title') . ' + ' . __('admin.ambitos.espana_title'),
        'espana' => __('admin.ambitos.espana_title'),
        default => __('admin.ambitos.andalucia_title'),
    };
@endphp

<x-layout :title="__('portal.routers.title')" :description="__('portal.routers.lead')" :image="asset('img/og/og-routers.webp')">
    <div class="contenedor" style="padding-top: 2rem; padding-bottom: 4rem;">

        <!-- 1. Encabezado y Breadcrumbs -->
        <nav aria-label="Ruta de navegación" style="margin-bottom: 1.25rem;">
            <ol style="display: flex; gap: 0.5rem; list-style: none; padding: 0; margin: 0; font-size: 0.85rem; color: var(--color-texto-2);">
                <li><a href="/{{ $langQuerySolo }}" style="color: var(--color-texto-2); text-decoration: none;">{{ __('portal.nav.home') }}</a></li>
                <li aria-hidden="true">/</li>
                <li aria-current="page" style="color: var(--color-texto); font-weight: 600;">{{ __('portal.routers.breadcrumb') }}</li>
            </ol>
        </nav>

        <header style="margin-bottom: 2.25rem;">
            <h1 style="font-size: 2.25rem; font-weight: 800; color: var(--color-texto); margin-bottom: 0.5rem; letter-spacing: -0.02em;">
                {{ __('portal.routers.title') }}
            </h1>
            <p class="lead" style="color: var(--color-texto-2); max-width: 820px; font-size: 1.05rem; line-height: 1.6; margin-bottom: 0;">
                {{ __('portal.routers.lead') }}
            </p>
        </header>

        <!-- 2. BLOQUE 1: ÁMBITO TERRITORIAL DE SUPERVISIÓN -->
        <section aria-label="{{ __('admin.ambitos.heading') }}" style="margin-bottom: 1.75rem;">
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
                                {{ trans_choice('admin.ambitos.routers_count', $conteoAndalucia, ['count' => $conteoAndalucia]) }}
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
                                {{ trans_choice('admin.ambitos.routers_count', $conteoEspana, ['count' => $conteoEspana]) }}
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

        <!-- 3. BLOQUE 2: TARJETAS DE MÉTRICAS EJECUTIVAS (4 KPIs) -->
        <section aria-label="Métricas ejecutivas de la red" style="margin-bottom: 2.25rem;">
            <div class="routers-kpi-grid">
                <!-- KPI 1: Presión del Aire (ChUtil) -->
                <div class="routers-kpi-card">
                    <div class="routers-kpi-card-header">
                        <span class="routers-kpi-card-title">
                            {{ __('admin.widgets.stats.channel_pressure') }} ({{ $ambitoTexto }})
                        </span>
                    </div>
                    <div class="routers-kpi-card-value">
                        {{ $kpiChutil['val'] }}
                    </div>
                    <div class="routers-kpi-card-desc">
                        {{ $kpiChutil['desc'] }}
                    </div>

                    <!-- Sparkline SVG decorativa y sutil en el fondo -->
                    <div class="routers-kpi-chart-bg" aria-hidden="true">
                        <svg viewBox="0 0 160 40" preserveAspectRatio="none" style="width: 100%; height: 38px; display: block;">
                            <path d="M0,28 Q25,10 50,22 T100,14 T160,20 L160,40 L0,40 Z" fill="rgba(103, 234, 148, 0.12)" />
                            <path d="M0,28 Q25,10 50,22 T100,14 T160,20" fill="none" stroke="rgba(103, 234, 148, 0.45)" stroke-width="2" />
                        </svg>
                    </div>

                    <!-- Icono flotante inferior derecho -->
                    <div class="routers-kpi-corner-icon" aria-hidden="true">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4.93 19.07a10 10 0 0 1 0-14.14"></path>
                            <path d="M7.76 16.24a6 6 0 0 1 0-8.48"></path>
                            <circle cx="12" cy="12" r="2"></circle>
                            <path d="M16.24 7.76a6 6 0 0 1 0 8.48"></path>
                            <path d="M19.07 4.93a10 10 0 0 1 0 14.14"></path>
                        </svg>
                    </div>
                </div>

                <!-- KPI 2: Saturación TX Repetidores -->
                <div class="routers-kpi-card">
                    <div class="routers-kpi-card-header">
                        <span class="routers-kpi-card-title">
                            {{ __('admin.widgets.stats.tx_saturation') }} ({{ $ambitoTexto }})
                        </span>
                    </div>
                    <div class="routers-kpi-card-value">
                        {{ $kpiTx['val'] }}
                    </div>
                    <div class="routers-kpi-card-desc">
                        {{ $kpiTx['desc'] }}
                    </div>

                    <!-- Sparkline SVG decorativa -->
                    <div class="routers-kpi-chart-bg" aria-hidden="true">
                        <svg viewBox="0 0 160 40" preserveAspectRatio="none" style="width: 100%; height: 38px; display: block;">
                            <path d="M0,32 Q30,18 65,24 T120,12 T160,16 L160,40 L0,40 Z" fill="rgba(103, 234, 148, 0.12)" />
                            <path d="M0,32 Q30,18 65,24 T120,12 T160,16" fill="none" stroke="rgba(103, 234, 148, 0.45)" stroke-width="2" />
                        </svg>
                    </div>

                    <!-- Icono flotante inferior derecho -->
                    <div class="routers-kpi-corner-icon" aria-hidden="true">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="16 12 12 8 8 12"></polyline>
                            <line x1="12" y1="16" x2="12" y2="8"></line>
                        </svg>
                    </div>
                </div>

                <!-- KPI 3: Infraestructura y Energía -->
                <div class="routers-kpi-card">
                    <div class="routers-kpi-card-header">
                        <span class="routers-kpi-card-title">
                            {{ __('admin.widgets.stats.infrastructure_energy') }} ({{ $ambitoTexto }})
                        </span>
                    </div>
                    <div class="routers-kpi-card-value">
                        {{ $kpiEnergia['val'] }}
                    </div>
                    <div class="routers-kpi-card-desc">
                        {{ $kpiEnergia['desc'] }}
                    </div>

                    <!-- Icono flotante inferior derecho -->
                    <div class="routers-kpi-corner-icon" aria-hidden="true">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>
                        </svg>
                    </div>
                </div>

                <!-- KPI 4: Tráfico y Pasarelas (24h) -->
                <div class="routers-kpi-card">
                    <div class="routers-kpi-card-header">
                        <span class="routers-kpi-card-title">
                            {{ __('admin.widgets.stats.traffic_gateways') }}
                        </span>
                    </div>
                    <div class="routers-kpi-card-value">
                        {{ $kpiTrafico['val'] }}
                    </div>
                    <div class="routers-kpi-card-desc">
                        {{ $kpiTrafico['desc'] }}
                    </div>

                    <!-- Icono flotante inferior derecho -->
                    <div class="routers-kpi-corner-icon" aria-hidden="true">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="2" y1="12" x2="22" y2="12"></line>
                            <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </section>

        <!-- 4. BLOQUE 3: TABLA DE REPETIDORES Y NODOS DE INFRAESTRUCTURA -->
        <x-tabla-routers
            :routers-por-provincia="$routersPorProvincia"
            :routers="$routers"
            :total="$total"
            :en-alerta="$enAlerta"
            :error-fuente="$errorFuente"
            :ambito="$ambito"
            :filtro-provincia="$filtroProvincia"
            :lang-query-solo="$langQuerySolo"
        />

    </div>

    <!-- Estilos exactos inspirados en el cuadro de mando de operadores (adaptados a tema público) -->
    <style>
        /* ==============================================================================
         * 1. Selector de Ámbito Territorial
         * ============================================================================== */
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
            color: #94a3b8;
        }
        .routers-ambito-context-indicator {
            font-size: 0.82rem;
            color: #cbd5e1;
            display: inline-flex;
            align-items: center;
            background: rgba(30, 41, 59, 0.7);
            padding: 0.3rem 0.85rem;
            border-radius: 9999px;
            border: 1px solid rgba(148, 163, 184, 0.2);
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
            background: #181924;
            border: 2px solid rgba(148, 163, 184, 0.16);
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.25);
            opacity: 0.72;
        }
        .routers-ambito-card-inactive:hover {
            opacity: 1;
            transform: translateY(-2px);
            border-color: rgba(148, 163, 184, 0.35);
            background: #1f212f;
        }

        .routers-ambito-card-andalucia-active {
            background: linear-gradient(135deg, rgba(0, 122, 51, 0.28) 0%, #181924 100%);
            border: 2px solid #67EA94;
            box-shadow: 0 4px 20px rgba(0, 122, 51, 0.45), inset 0 0 16px rgba(103, 234, 148, 0.08);
            opacity: 1;
        }
        .routers-ambito-card-andalucia-active:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 24px rgba(0, 122, 51, 0.55), inset 0 0 20px rgba(103, 234, 148, 0.12);
        }

        .routers-ambito-card-espana-active {
            background: linear-gradient(135deg, rgba(220, 38, 38, 0.28) 0%, #181924 100%);
            border: 2px solid #f87171;
            box-shadow: 0 4px 20px rgba(220, 38, 38, 0.4), inset 0 0 16px rgba(248, 113, 113, 0.08);
            opacity: 1;
        }
        .routers-ambito-card-espana-active:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 24px rgba(220, 38, 38, 0.5), inset 0 0 20px rgba(248, 113, 113, 0.12);
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
            color: #f8fafc;
            line-height: 1.25;
        }
        .routers-ambito-card-desc {
            font-size: 0.82rem;
            color: #94a3b8;
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
            color: #cbd5e1;
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
            background: rgba(30, 41, 59, 0.5);
            color: #94a3b8;
            border: 1px dashed rgba(148, 163, 184, 0.3);
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

        /* ==============================================================================
         * 2. Tarjetas KPI de Métricas Ejecutivas
         * ============================================================================== */
        .routers-kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.25rem;
            width: 100%;
        }
        @media (max-width: 1024px) {
            .routers-kpi-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        @media (max-width: 640px) {
            .routers-kpi-grid {
                grid-template-columns: 1fr;
            }
        }

        .routers-kpi-card {
            background: #0f2d1e;
            border: 1px solid #10b981;
            border-radius: 0.85rem;
            padding: 1.5rem;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            box-shadow: 0 4px 20px rgba(0, 122, 51, 0.22);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .routers-kpi-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 24px rgba(0, 122, 51, 0.35);
        }
        .routers-kpi-card-header {
            margin-bottom: 0.65rem;
        }
        .routers-kpi-card-title {
            font-size: 0.88rem;
            font-weight: 700;
            color: #e2e8f0;
            line-height: 1.35;
        }
        .routers-kpi-card-value {
            font-size: 2.2rem;
            font-weight: 800;
            color: #ffffff;
            line-height: 1.1;
            letter-spacing: -0.02em;
            margin-bottom: 0.5rem;
            font-variant-numeric: tabular-nums;
        }
        .routers-kpi-card-desc {
            font-size: 0.82rem;
            color: #94a3b8;
            line-height: 1.45;
            z-index: 2;
        }
        .routers-kpi-chart-bg {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            pointer-events: none;
            opacity: 0.85;
            z-index: 1;
        }
        .routers-kpi-corner-icon {
            position: absolute;
            bottom: 1.25rem;
            right: 1.25rem;
            color: rgba(255, 255, 255, 0.85);
            pointer-events: none;
            z-index: 2;
        }

    </style>
</x-layout>

