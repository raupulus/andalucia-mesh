@php
    $currentLang = app()->getLocale();
    $langQuery = $currentLang !== 'es' ? '?lang=' . $currentLang : '';
@endphp

<x-layout
    :title="__('portal.tools.battery.meta_title', ['name' => config('proyecto.nombre')])"
    :description="__('portal.tools.battery.meta_description')"
    :image="asset('img/og/og-calculadora-bateria.webp')"
    :keywords="__('portal.tools.battery.meta_keywords')"
>
    <x-slot:styles>
        <style>
            .calc-hero {
                padding: 2.5rem 0 2rem;
                border-bottom: 1px solid var(--color-borde);
                background: linear-gradient(180deg, var(--color-superficie) 0%, var(--color-fondo) 100%);
            }

            .calc-grid {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 2rem;
                margin-top: 2rem;
            }

            @media (max-width: 900px) {
                .calc-grid {
                    grid-template-columns: 1fr;
                }
            }

            .calc-panel {
                background: var(--color-superficie);
                border: 1px solid var(--color-borde);
                border-radius: var(--radio-lg);
                padding: 1.75rem;
                box-shadow: var(--sombra-1);
            }

            .calc-form-group {
                margin-bottom: 1.5rem;
            }

            .calc-form-label {
                display: block;
                font-size: 0.92rem;
                font-weight: 700;
                color: var(--color-texto);
                margin-bottom: 0.4rem;
            }

            .calc-form-hint {
                display: block;
                font-size: 0.8rem;
                color: var(--color-texto-2);
                margin-top: 0.35rem;
                line-height: 1.4;
            }

            .calc-input-wrap {
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }

            .calc-input {
                flex: 1;
                background: var(--color-fondo);
                border: 1px solid var(--color-borde-control);
                border-radius: var(--radio-md);
                padding: 0.65rem 0.85rem;
                color: var(--color-texto);
                font-family: var(--fuente-mono);
                font-size: 1.1rem;
                font-weight: 600;
                outline: none;
                transition: border-color var(--duracion-transicion) ease;
            }

            .calc-input:focus {
                border-color: var(--color-enlace);
            }

            .calc-select {
                background: var(--color-fondo);
                border: 1px solid var(--color-borde-control);
                border-radius: var(--radio-md);
                padding: 0.65rem 0.75rem;
                color: var(--color-texto);
                font-family: inherit;
                font-size: 0.95rem;
                font-weight: 600;
                cursor: pointer;
            }

            .calc-quick-pills {
                display: flex;
                flex-wrap: wrap;
                gap: 0.4rem;
                margin-top: 0.5rem;
            }

            .calc-pill {
                background: var(--color-superficie-sutil);
                border: 1px solid var(--color-borde);
                color: var(--color-texto-2);
                padding: 0.25rem 0.65rem;
                border-radius: var(--radio-full);
                font-size: 0.78rem;
                font-family: var(--fuente-mono);
                cursor: pointer;
                transition: all var(--duracion-transicion) ease;
            }

            .calc-pill:hover {
                border-color: var(--color-enlace);
                color: var(--color-enlace);
                background: var(--color-superficie);
            }

            .kpi-calc-card {
                background: var(--color-fondo);
                border: 1px solid var(--color-borde);
                border-radius: var(--radio-md);
                padding: 1.25rem;
                margin-bottom: 1.25rem;
                position: relative;
            }

            .kpi-calc-label {
                font-size: 0.82rem;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                font-weight: 700;
                color: var(--color-texto-2);
                margin-bottom: 0.35rem;
            }

            .kpi-calc-value {
                font-family: var(--fuente-mono);
                font-size: 2.2rem;
                font-weight: 800;
                color: var(--color-enlace);
                line-height: 1.1;
                display: flex;
                align-items: baseline;
                gap: 0.5rem;
            }

            .kpi-calc-sub {
                font-size: 0.85rem;
                color: var(--color-texto-2);
                margin-top: 0.35rem;
                line-height: 1.4;
            }

            .badge-seguridad {
                display: inline-flex;
                align-items: center;
                gap: 0.4rem;
                padding: 0.3rem 0.75rem;
                border-radius: var(--radio-full);
                font-size: 0.82rem;
                font-weight: 700;
                margin-top: 0.5rem;
            }

            .badge-seguridad.seguro {
                background: rgba(16, 185, 129, 0.15);
                color: #10b981;
                border: 1px solid rgba(16, 185, 129, 0.35);
            }

            .badge-seguridad.precaucion {
                background: rgba(245, 158, 11, 0.15);
                color: #f59e0b;
                border: 1px solid rgba(245, 158, 11, 0.35);
            }

            .badge-seguridad.peligro {
                background: rgba(239, 68, 68, 0.2);
                color: #ef4444;
                border: 1px solid rgba(239, 68, 68, 0.4);
            }

            .recs-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
                gap: 1.5rem;
                margin-top: 1.5rem;
                margin-bottom: 2.5rem;
            }

            .rec-card {
                background: var(--color-superficie);
                border: 1px solid var(--color-borde);
                border-top: 4px solid var(--color-acento);
                border-radius: var(--radio-lg);
                padding: 1.5rem;
                display: flex;
                flex-direction: column;
            }

            .rec-card-tag {
                font-size: 0.75rem;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                color: var(--color-acento);
                margin-bottom: 0.5rem;
            }

            .rec-card-title {
                font-size: 1.6rem;
                font-weight: 800;
                font-family: var(--fuente-mono);
                color: var(--color-texto);
                margin: 0 0 0.75rem;
            }

            .rec-card-stat {
                display: flex;
                justify-content: space-between;
                font-size: 0.88rem;
                padding: 0.4rem 0;
                border-bottom: 1px dashed var(--color-borde);
            }

            .rec-card-stat-label {
                color: var(--color-texto-2);
            }

            .rec-card-stat-val {
                font-family: var(--fuente-mono);
                font-weight: 700;
                color: var(--color-texto);
            }

            .btn-rec-aplicar {
                margin-top: 1.25rem;
                width: 100%;
                background: var(--color-superficie-sutil);
                border: 1px solid var(--color-borde);
                color: var(--color-texto);
                padding: 0.65rem 1rem;
                border-radius: var(--radio-md);
                font-size: 0.9rem;
                font-weight: 600;
                cursor: pointer;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 0.5rem;
                transition: all var(--duracion-transicion) ease;
            }

            .btn-rec-aplicar:hover {
                border-color: var(--color-enlace);
                color: var(--color-enlace);
                background: var(--color-fondo);
            }

            .code-box {
                background: var(--color-fondo);
                border: 1px solid var(--color-borde);
                border-radius: var(--radio-md);
                padding: 0.85rem 1rem;
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 1rem;
                margin-top: 0.5rem;
                font-family: var(--fuente-mono);
                font-size: 0.95rem;
            }

            .btn-copiar {
                background: var(--color-superficie);
                border: 1px solid var(--color-borde-control);
                color: var(--color-texto);
                padding: 0.35rem 0.75rem;
                border-radius: var(--radio-sm);
                font-size: 0.8rem;
                font-weight: 600;
                cursor: pointer;
                transition: all var(--duracion-transicion) ease;
                white-space: nowrap;
            }

            .btn-copiar:hover {
                border-color: var(--color-enlace);
                color: var(--color-enlace);
            }

            .schematic-svg {
                width: 100%;
                max-width: 480px;
                height: auto;
                display: block;
                margin: 1.5rem auto 1rem;
            }
        </style>
    </x-slot:styles>

    <!-- Hero / Encabezado -->
    <header class="calc-hero">
        <div class="contenedor">
            <nav aria-label="Ruta de navegación" style="margin-bottom: 1rem;">
                <ol style="display: flex; align-items: center; gap: 0.5rem; list-style: none; padding: 0; margin: 0; font-size: 0.85rem; color: var(--color-texto-2);">
                    <li>
                        <a href="/{{ $langQuery }}" style="color: var(--color-texto-2); text-decoration: none;">{{ __('portal.nav.home') }}</a>
                    </li>
                    <li aria-hidden="true" style="color: var(--color-borde);">/</li>
                    <li>
                        <a href="/herramientas{{ $langQuery }}" style="color: var(--color-texto-2); text-decoration: none;">{{ __('portal.tools.breadcrumb') }}</a>
                    </li>
                    <li aria-hidden="true" style="color: var(--color-borde);">/</li>
                    <li>
                        <span style="color: var(--color-texto); font-weight: 600;" aria-current="page">{{ __('portal.tools.battery.breadcrumb') }}</span>
                    </li>
                </ol>
            </nav>

            <div style="max-width: 860px;">
                <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                    <a href="/herramientas{{ $langQuery }}" style="display: inline-flex; align-items: center; gap: 0.35rem; font-size: 0.85rem; font-weight: 600; color: var(--color-enlace); text-decoration: none;">
                        ← {{ __('portal.tools.battery.back_to_tools') }}
                    </a>
                </div>
                <h1 style="font-size: clamp(2rem, 3.8vw, 2.6rem); font-weight: 800; line-height: 1.2; margin: 0 0 1rem; color: var(--color-texto);">
                    {{ __('portal.tools.battery.title') }}
                </h1>
                <p style="font-size: 1.08rem; color: var(--color-texto-2); line-height: 1.6; margin: 0 0 1.25rem;">
                    {{ __('portal.tools.battery.lead') }}
                </p>
                <div style="margin-top: 1.25rem;">
                    <x-botones-compartir :url="url()->current()" :titulo="__('portal.tools.battery.title')" :texto="__('portal.tools.battery.lead')" />
                </div>
            </div>
        </div>
    </header>

    <div class="contenedor" style="padding-top: 2.5rem; padding-bottom: 4rem;">

        <!-- Contexto Técnico: Por qué el divisor -->
        <section class="calc-panel" style="margin-bottom: 2.5rem;">
            <div style="display: grid; grid-template-columns: 1fr 340px; gap: 2rem; align-items: center;">
                <div>
                    <h2 style="font-size: 1.35rem; font-weight: 700; margin: 0 0 0.85rem; color: var(--color-texto);">
                        {{ __('portal.tools.battery.why_title') }}
                    </h2>
                    <p style="font-size: 0.95rem; color: var(--color-texto-2); line-height: 1.6; margin-bottom: 1rem;">
                        {{ __('portal.tools.battery.why_desc') }}
                    </p>
                    <div style="background: var(--color-fondo); border: 1px solid var(--color-borde); border-radius: var(--radio-md); padding: 1rem 1.25rem; font-size: 0.9rem; line-height: 1.5; color: var(--color-texto);">
                        <strong>{{ __('portal.tools.battery.ratio_formula') }}</strong> ·
                        <span style="color: var(--color-texto-2);">{{ __('portal.tools.battery.pin_formula') }}</span>
                    </div>
                </div>

                <!-- Esquema SVG Visual -->
                <div style="background: var(--color-fondo); border: 1px solid var(--color-borde); border-radius: var(--radio-md); padding: 1.25rem; text-align: center;">
                    <svg viewBox="0 0 280 200" class="schematic-svg" aria-label="Diagrama esquemático del divisor de tensión">
                        <!-- Cable principal -->
                        <line x1="140" y1="15" x2="140" y2="45" stroke="var(--color-texto)" stroke-width="2.5" />
                        <!-- Resistencia R1 -->
                        <rect x="126" y="45" width="28" height="40" fill="var(--color-superficie)" stroke="#10b981" stroke-width="2" rx="4" />
                        <text x="140" y="70" fill="var(--color-texto)" font-size="12" font-weight="bold" text-anchor="middle" font-family="monospace">R1</text>
                        <!-- Nodo medio -->
                        <line x1="140" y1="85" x2="140" y2="120" stroke="var(--color-texto)" stroke-width="2.5" />
                        <circle cx="140" cy="102" r="4.5" fill="#3b82f6" />
                        <!-- Salida a Pin ADC -->
                        <line x1="140" y1="102" x2="230" y2="102" stroke="#3b82f6" stroke-width="2.5" stroke-dasharray="4" />
                        <polygon points="230,98 238,102 230,106" fill="#3b82f6" />
                        <text x="210" y="92" fill="#60a5fa" font-size="11" font-weight="bold" font-family="monospace">Pin ADC</text>
                        <text x="210" y="118" fill="var(--color-texto-2)" font-size="9" font-family="sans-serif">&lt; 3.3V</text>
                        <!-- Resistencia R2 -->
                        <rect x="126" y="120" width="28" height="40" fill="var(--color-superficie)" stroke="#10b981" stroke-width="2" rx="4" />
                        <text x="140" y="145" fill="var(--color-texto)" font-size="12" font-weight="bold" text-anchor="middle" font-family="monospace">R2</text>
                        <!-- Conexión a GND -->
                        <line x1="140" y1="160" x2="140" y2="180" stroke="var(--color-texto)" stroke-width="2.5" />
                        <line x1="120" y1="180" x2="160" y2="180" stroke="var(--color-texto)" stroke-width="2.5" />
                        <line x1="126" y1="185" x2="154" y2="185" stroke="var(--color-texto)" stroke-width="2" />
                        <line x1="133" y1="190" x2="147" y2="190" stroke="var(--color-texto)" stroke-width="1.5" />
                        <text x="140" y="10" fill="#ef4444" font-size="11" font-weight="bold" text-anchor="middle" font-family="monospace">+ Vbat (4.2V)</text>
                        <text x="140" y="200" fill="var(--color-texto-2)" font-size="9" text-anchor="middle" font-family="monospace">GND</text>
                    </svg>
                </div>
            </div>
            <div style="margin-top: 1rem; font-size: 0.88rem; color: var(--color-texto-2); background: var(--color-fondo); border: 1px solid var(--color-borde); border-radius: var(--radio-md); padding: 0.75rem 1rem;">
                💡 {{ __('portal.tools.battery.schematic_cap_tip') }}
            </div>
        </section>

        <!-- Sección Parejas Recomendadas (Para comprar) -->
        <section style="margin-bottom: 3rem;">
            <div style="max-width: 800px; margin-bottom: 1.5rem;">
                <h2 style="font-size: 1.5rem; font-weight: 800; color: var(--color-texto); margin: 0 0 0.5rem;">
                    {{ __('portal.tools.battery.rec_title') }}
                </h2>
                <p style="font-size: 1rem; color: var(--color-texto-2); margin: 0;">
                    {{ __('portal.tools.battery.rec_lead') }}
                </p>
            </div>

            <div class="recs-grid">
                <!-- Pareja 1: 1M + 1M -->
                <div class="rec-card" style="border-top-color: #10b981;">
                    <span class="rec-card-tag">{{ __('portal.tools.battery.rec_1_tag') }}</span>
                    <h3 class="rec-card-title">1 MΩ + 1 MΩ</h3>
                    <p style="font-size: 0.9rem; color: var(--color-texto-2); line-height: 1.5; margin-bottom: 1rem; flex-grow: 1;">
                        {{ __('portal.tools.battery.rec_1_desc') }}
                    </p>
                    <div style="margin-bottom: 1rem;">
                        <div class="rec-card-stat">
                            <span class="rec-card-stat-label">{{ __('portal.tools.battery.multiplier_in_app') }}:</span>
                            <span class="rec-card-stat-val" style="color: #10b981;">2.05 ({{ __('portal.tools.battery.calibrated_tag') }})</span>
                        </div>
                        <div class="rec-card-stat">
                            <span class="rec-card-stat-label">{{ __('portal.tools.battery.voltage_at_42') }}:</span>
                            <span class="rec-card-stat-val">2.10 V</span>
                        </div>
                        <div class="rec-card-stat">
                            <span class="rec-card-stat-label">{{ __('portal.tools.battery.quiescent_drain') }}:</span>
                            <span class="rec-card-stat-val">2.10 µA</span>
                        </div>
                    </div>
                    <button type="button" class="btn-rec-aplicar" onclick="cargarValores(1, 'M', 1, 'M', 2.05)">
                        <span>⚡</span> {{ __('portal.tools.battery.load_preset') }}
                    </button>
                </div>

                <!-- Pareja 2: 1M + 1.5M -->
                <div class="rec-card" style="border-top-color: #3b82f6;">
                    <span class="rec-card-tag">{{ __('portal.tools.battery.rec_2_tag') }}</span>
                    <h3 class="rec-card-title">1 MΩ + 1.5 MΩ</h3>
                    <p style="font-size: 0.9rem; color: var(--color-texto-2); line-height: 1.5; margin-bottom: 1rem; flex-grow: 1;">
                        {{ __('portal.tools.battery.rec_2_desc') }}
                    </p>
                    <div style="margin-bottom: 1rem;">
                        <div class="rec-card-stat">
                            <span class="rec-card-stat-label">{{ __('portal.tools.battery.multiplier_in_app') }}:</span>
                            <span class="rec-card-stat-val" style="color: #60a5fa;">1.67 ({{ __('portal.tools.battery.ratio_tag') }})</span>
                        </div>
                        <div class="rec-card-stat">
                            <span class="rec-card-stat-label">{{ __('portal.tools.battery.voltage_at_42') }}:</span>
                            <span class="rec-card-stat-val">2.52 V</span>
                        </div>
                        <div class="rec-card-stat">
                            <span class="rec-card-stat-label">{{ __('portal.tools.battery.quiescent_drain') }}:</span>
                            <span class="rec-card-stat-val">1.68 µA</span>
                        </div>
                    </div>
                    <button type="button" class="btn-rec-aplicar" onclick="cargarValores(1, 'M', 1.5, 'M', 1.67)">
                        <span>⚡</span> {{ __('portal.tools.battery.load_preset') }}
                    </button>
                </div>
            </div>
        </section>

        <!-- Calculadora Interactiva Principal -->
        <section id="calculadora-interactiva" style="margin-bottom: 3.5rem;">
            <div style="max-width: 800px; margin-bottom: 1.5rem;">
                <h2 style="font-size: 1.5rem; font-weight: 800; color: var(--color-texto); margin: 0 0 0.5rem;">
                    {{ __('portal.tools.battery.calc_heading') }}
                </h2>
                <p style="font-size: 1rem; color: var(--color-texto-2); margin: 0;">
                    {{ __('portal.tools.battery.calc_lead') }}
                </p>
            </div>

            <div class="calc-grid">
                <!-- Columna Izquierda: Parámetros del Circuito -->
                <div class="calc-panel">
                    <h3 style="font-size: 1.2rem; font-weight: 700; margin: 0 0 1.5rem; color: var(--color-texto); padding-bottom: 0.75rem; border-bottom: 1px solid var(--color-borde);">
                        {{ __('portal.tools.battery.divider_params') }}
                    </h3>

                    <!-- R1 Input -->
                    <div class="calc-form-group">
                        <label for="input-r1" class="calc-form-label">{{ __('portal.tools.battery.r1_label') }}</label>
                        <div class="calc-input-wrap">
                            <input type="number" id="input-r1" class="calc-input" value="1" step="any" min="0.001" placeholder="Ej. 1">
                            <select id="unit-r1" class="calc-select" aria-label="Unidad R1">
                                <option value="M" selected>MΩ (Megaohmios)</option>
                                <option value="k">kΩ (Kiloohmios)</option>
                                <option value="1">Ω (Ohmios)</option>
                            </select>
                        </div>
                        <span class="calc-form-hint">{{ __('portal.tools.battery.r1_hint') }}</span>
                        <div class="calc-quick-pills">
                            <button type="button" class="calc-pill" onclick="setPill('r1', 1, 'M')">1 MΩ</button>
                            <button type="button" class="calc-pill" onclick="setPill('r1', 1.5, 'M')">1.5 MΩ</button>
                            <button type="button" class="calc-pill" onclick="setPill('r1', 470, 'k')">470 kΩ</button>
                            <button type="button" class="calc-pill" onclick="setPill('r1', 100, 'k')">100 kΩ</button>
                        </div>
                    </div>

                    <!-- R2 Input -->
                    <div class="calc-form-group">
                        <label for="input-r2" class="calc-form-label">{{ __('portal.tools.battery.r2_label') }}</label>
                        <div class="calc-input-wrap">
                            <input type="number" id="input-r2" class="calc-input" value="1" step="any" min="0.001" placeholder="Ej. 1">
                            <select id="unit-r2" class="calc-select" aria-label="Unidad R2">
                                <option value="M" selected>MΩ (Megaohmios)</option>
                                <option value="k">kΩ (Kiloohmios)</option>
                                <option value="1">Ω (Ohmios)</option>
                            </select>
                        </div>
                        <span class="calc-form-hint">{{ __('portal.tools.battery.r2_hint') }}</span>
                        <div class="calc-quick-pills">
                            <button type="button" class="calc-pill" onclick="setPill('r2', 1, 'M')">1 MΩ</button>
                            <button type="button" class="calc-pill" onclick="setPill('r2', 1.5, 'M')">1.5 MΩ</button>
                            <button type="button" class="calc-pill" onclick="setPill('r2', 470, 'k')">470 kΩ</button>
                            <button type="button" class="calc-pill" onclick="setPill('r2', 100, 'k')">100 kΩ</button>
                        </div>
                    </div>

                    <!-- Voltaje Batería de Prueba -->
                    <div class="calc-form-group" style="margin-bottom: 0;">
                        <label for="input-vbat" class="calc-form-label">{{ __('portal.tools.battery.battery_voltage_label') }}</label>
                        <div class="calc-input-wrap">
                            <input type="number" id="input-vbat" class="calc-input" value="4.20" step="0.05" min="1.0" max="15.0">
                            <span style="font-family: var(--fuente-mono); font-weight: 700; font-size: 1.1rem; padding: 0 0.5rem; color: var(--color-texto-2);">{{ __('portal.tools.battery.volts') }}</span>
                        </div>
                        <span class="calc-form-hint">{{ __('portal.tools.battery.battery_voltage_hint') }}</span>
                        <div class="calc-quick-pills">
                            <button type="button" class="calc-pill" onclick="setVbat(4.20)">{{ __('portal.tools.battery.quick_bat_42') }}</button>
                            <button type="button" class="calc-pill" onclick="setVbat(3.70)">{{ __('portal.tools.battery.quick_bat_37') }}</button>
                            <button type="button" class="calc-pill" onclick="setVbat(3.20)">{{ __('portal.tools.battery.quick_bat_32') }}</button>
                        </div>
                    </div>
                </div>

                <!-- Columna Derecha: Resultados Calculados -->
                <div class="calc-panel" style="display: flex; flex-direction: column;">
                    <h3 style="font-size: 1.2rem; font-weight: 700; margin: 0 0 1.5rem; color: var(--color-texto); padding-bottom: 0.75rem; border-bottom: 1px solid var(--color-borde);">
                        {{ __('portal.tools.battery.results_heading') }}
                    </h3>

                    <!-- KPI 1: Multiplicador Meshtastic -->
                    <div class="kpi-calc-card">
                        <div class="kpi-calc-label">{{ __('portal.tools.battery.ratio_theoretical') }} (adc_multiplier)</div>
                        <div class="kpi-calc-value">
                            <span id="res-ratio">2.000</span>
                            <span id="res-ratio-rec-tag" style="font-size: 0.95rem; font-weight: 600; color: #10b981;">{{ __('portal.tools.battery.recommended_205') }}</span>
                        </div>
                        <div class="kpi-calc-sub">
                            {{ __('portal.tools.battery.ratio_formula') }}
                        </div>
                    </div>

                    <!-- KPI 2: Voltaje en el Pin GPIO -->
                    <div class="kpi-calc-card">
                        <div class="kpi-calc-label">{{ __('portal.tools.battery.pin_voltage_at_bat') }}</div>
                        <div class="kpi-calc-value">
                            <span id="res-vpin">2.100</span>
                            <span style="font-size: 1.1rem; color: var(--color-texto-2);">V</span>
                        </div>
                        <div id="badge-seguridad-pin" class="badge-seguridad seguro">
                            <span id="badge-seguridad-icono">✓</span>
                            <span id="badge-seguridad-texto">{{ __('portal.tools.battery.pin_voltage_status_safe') }}</span>
                        </div>
                    </div>

                    <!-- KPI 3: Consumo en Reposo -->
                    <div class="kpi-calc-card" style="margin-bottom: 0;">
                        <div class="kpi-calc-label">{{ __('portal.tools.battery.quiescent_current') }}</div>
                        <div class="kpi-calc-value">
                            <span id="res-corriente">2.10</span>
                            <span id="res-corriente-unidad" style="font-size: 1.1rem; color: var(--color-texto-2);">µA</span>
                        </div>
                        <div class="kpi-calc-sub" id="res-corriente-eval">
                            {{ __('portal.tools.battery.quiescent_status_optimal') }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla de Comportamiento por Niveles de Batería -->
            <div class="calc-panel" style="margin-top: 2rem;">
                <h3 style="font-size: 1.15rem; font-weight: 700; margin: 0 0 1rem; color: var(--color-texto);">
                    {{ __('portal.tools.battery.table_title') }}
                </h3>
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.92rem; text-align: left;">
                        <thead>
                            <tr style="border-bottom: 2px solid var(--color-borde); color: var(--color-texto-2);">
                                <th style="padding: 0.65rem 0.5rem;">{{ __('portal.tools.battery.th_state') }}</th>
                                <th style="padding: 0.65rem 0.5rem;">{{ __('portal.tools.battery.th_vbat') }}</th>
                                <th style="padding: 0.65rem 0.5rem;">{{ __('portal.tools.battery.th_vpin') }}</th>
                                <th style="padding: 0.65rem 0.5rem;">{{ __('portal.tools.battery.th_margin') }}</th>
                            </tr>
                        </thead>
                        <tbody id="tabla-niveles-body">
                            <!-- Rellenado dinámicamente por JS -->
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- Calibración Fina con Multímetro -->
        <section class="calc-panel" style="margin-bottom: 3.5rem;">
            <div style="max-width: 800px; margin-bottom: 1.5rem;">
                <h2 style="font-size: 1.35rem; font-weight: 700; margin: 0 0 0.4rem; color: var(--color-texto);">
                    {{ __('portal.tools.battery.calibration_heading') }}
                </h2>
                <p style="font-size: 0.95rem; color: var(--color-texto-2); line-height: 1.5; margin: 0;">
                    {{ __('portal.tools.battery.calibration_lead') }}
                </p>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.5rem; align-items: end;">
                <div>
                    <label for="calib-multimetro" class="calc-form-label">{{ __('portal.tools.battery.multimeter_reading') }}</label>
                    <input type="number" id="calib-multimetro" class="calc-input" value="4.18" step="0.01" style="width: 100%;">
                </div>

                <div>
                    <label for="calib-app" class="calc-form-label">{{ __('portal.tools.battery.app_reading') }}</label>
                    <input type="number" id="calib-app" class="calc-input" value="4.05" step="0.01" style="width: 100%;">
                </div>

                <div>
                    <label for="calib-actual" class="calc-form-label">{{ __('portal.tools.battery.current_ratio') }}</label>
                    <input type="number" id="calib-actual" class="calc-input" value="2.00" step="0.01" style="width: 100%;">
                </div>

                <div style="background: var(--color-fondo); border: 1px solid var(--color-borde); border-radius: var(--radio-md); padding: 0.65rem 1rem;">
                    <div style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: var(--color-texto-2);">
                        {{ __('portal.tools.battery.new_calibrated_ratio') }}
                    </div>
                    <div style="font-family: var(--fuente-mono); font-size: 1.6rem; font-weight: 800; color: #10b981;" id="calib-resultado">
                        2.064
                    </div>
                </div>
            </div>
            <div style="margin-top: 1rem; font-size: 0.82rem; color: var(--color-texto-2);">
                {{ __('portal.tools.battery.calibration_formula') }}
            </div>
        </section>

        <!-- Cómo aplicar en Meshtastic -->
        <section class="calc-panel" style="margin-bottom: 2.5rem;">
            <div style="max-width: 800px; margin-bottom: 1.5rem;">
                <h2 style="font-size: 1.35rem; font-weight: 700; margin: 0 0 0.4rem; color: var(--color-texto);">
                    {{ __('portal.tools.battery.how_to_apply_title') }}
                </h2>
                <p style="font-size: 0.95rem; color: var(--color-texto-2); line-height: 1.5; margin: 0;">
                    {{ __('portal.tools.battery.how_to_apply_lead') }}
                </p>
            </div>

            <div style="display: grid; grid-template-columns: 1fr; gap: 1.25rem;">
                <!-- Método 1: App -->
                <div style="background: var(--color-fondo); border: 1px solid var(--color-borde); border-radius: var(--radio-md); padding: 1.25rem;">
                    <h3 style="font-size: 1.05rem; font-weight: 700; margin: 0 0 0.4rem; color: var(--color-texto);">
                        {{ __('portal.tools.battery.method_app_title') }}
                    </h3>
                    <p style="font-size: 0.9rem; color: var(--color-texto-2); margin: 0; line-height: 1.5;">
                        {{ __('portal.tools.battery.method_app_steps') }}
                    </p>
                </div>

                <!-- Método 2: CLI -->
                <div style="background: var(--color-fondo); border: 1px solid var(--color-borde); border-radius: var(--radio-md); padding: 1.25rem;">
                    <h3 style="font-size: 1.05rem; font-weight: 700; margin: 0 0 0.4rem; color: var(--color-texto);">
                        {{ __('portal.tools.battery.method_cli_title') }}
                    </h3>
                    <p style="font-size: 0.9rem; color: var(--color-texto-2); margin: 0 0 0.5rem; line-height: 1.5;">
                        {{ __('portal.tools.battery.method_cli_lead') }}
                    </p>
                    <div class="code-box">
                        <code id="cli-command" style="color: #60a5fa;">meshtastic --set power.adc_multiplier_override 2.05</code>
                        <button type="button" class="btn-copiar" onclick="copiarAlPortapapeles('cli-command', this)">
                            {{ __('portal.tools.battery.copy') }}
                        </button>
                    </div>
                </div>

                <!-- Método 3: YAML -->
                <div style="background: var(--color-fondo); border: 1px solid var(--color-borde); border-radius: var(--radio-md); padding: 1.25rem;">
                    <h3 style="font-size: 1.05rem; font-weight: 700; margin: 0 0 0.4rem; color: var(--color-texto);">
                        {{ __('portal.tools.battery.method_yaml_title') }}
                    </h3>
                    <p style="font-size: 0.9rem; color: var(--color-texto-2); margin: 0 0 0.5rem; line-height: 1.5;">
                        {{ __('portal.tools.battery.method_yaml_lead') }}
                    </p>
                    <div class="code-box" style="align-items: flex-start;">
                        <pre id="yaml-snippet" style="margin: 0; color: #34d399; font-family: inherit;">power:
  adc_multiplier_override: 2.05</pre>
                        <button type="button" class="btn-copiar" onclick="copiarAlPortapapeles('yaml-snippet', this)">
                            {{ __('portal.tools.battery.copy') }}
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Consejos Adicionales de Hardware y ESP32 -->
        <section class="calc-panel" style="background: var(--color-superficie-sutil); border-left: 4px solid #10b981;">
            <h3 style="font-size: 1.15rem; font-weight: 700; margin: 0 0 0.5rem; color: var(--color-texto);">
                {{ __('portal.tools.battery.tips_title') }}
            </h3>
            <ul style="margin: 0; padding-left: 1.25rem; font-size: 0.92rem; color: var(--color-texto-2); line-height: 1.6;">
                <li><strong>{{ __('portal.tools.battery.tip_resistors_title') }}:</strong> {{ __('portal.tools.battery.tip_resistors_desc') }}</li>
                <li><strong>{{ __('portal.tools.battery.tip_pins_title') }}:</strong> {!! __('portal.tools.battery.tip_pins_desc') !!}</li>
                <li><strong>{{ __('portal.tools.battery.tip_filter_title') }}:</strong> {{ __('portal.tools.battery.tip_filter_desc') }}</li>
            </ul>
        </section>

    </div>

    <x-slot:scripts>
        <script>
            (function() {
                var inputR1 = document.getElementById('input-r1');
                var unitR1 = document.getElementById('unit-r1');
                var inputR2 = document.getElementById('input-r2');
                var unitR2 = document.getElementById('unit-r2');
                var inputVbat = document.getElementById('input-vbat');

                var resRatio = document.getElementById('res-ratio');
                var resRatioRecTag = document.getElementById('res-ratio-rec-tag');
                var resVpin = document.getElementById('res-vpin');
                var badgeSeguridad = document.getElementById('badge-seguridad-pin');
                var badgeSeguridadTexto = document.getElementById('badge-seguridad-texto');
                var badgeSeguridadIcono = document.getElementById('badge-seguridad-icono');
                var resCorriente = document.getElementById('res-corriente');
                var resCorrienteUnidad = document.getElementById('res-corriente-unidad');
                var resCorrienteEval = document.getElementById('res-corriente-eval');

                var cliCommand = document.getElementById('cli-command');
                var yamlSnippet = document.getElementById('yaml-snippet');
                var tablaBody = document.getElementById('tabla-niveles-body');

                var calibMulti = document.getElementById('calib-multimetro');
                var calibApp = document.getElementById('calib-app');
                var calibActual = document.getElementById('calib-actual');
                var calibResultado = document.getElementById('calib-resultado');

                var i18n = {
                    rec205: @js(__('portal.tools.battery.recommended_205')),
                    rec167: @js(__('portal.tools.battery.recommended_167')),
                    safe: @js(__('portal.tools.battery.pin_voltage_status_safe')),
                    caution: @js(__('portal.tools.battery.pin_voltage_status_caution')),
                    danger: @js(__('portal.tools.battery.pin_voltage_status_danger')),
                    optimalDrain: @js(__('portal.tools.battery.quiescent_status_optimal')),
                    lowDrain: @js(__('portal.tools.battery.quiescent_status_low')),
                    highDrain: @js(__('portal.tools.battery.quiescent_status_warning')),
                    marginOptimal: @js(__('portal.tools.battery.margin_optimal')),
                    marginAcceptable: @js(__('portal.tools.battery.margin_acceptable')),
                    marginDanger: @js(__('portal.tools.battery.margin_danger')),
                    levels: [
                        { estado: @js(__('portal.tools.battery.level_100')), vbat: 4.20 },
                        { estado: @js(__('portal.tools.battery.level_80')), vbat: 4.00 },
                        { estado: @js(__('portal.tools.battery.level_50')), vbat: 3.70 },
                        { estado: @js(__('portal.tools.battery.level_20')), vbat: 3.40 },
                        { estado: @js(__('portal.tools.battery.level_0')), vbat: 3.00 }
                    ]
                };

                function getOhms(val, unit) {
                    var n = parseFloat(val) || 0;
                    if (unit === 'M') return n * 1000000;
                    if (unit === 'k') return n * 1000;
                    return n;
                }

                function calcular() {
                    var r1 = getOhms(inputR1.value, unitR1.value);
                    var r2 = getOhms(inputR2.value, unitR2.value);
                    var vbat = parseFloat(inputVbat.value) || 4.2;

                    if (r1 <= 0 || r2 <= 0) return;

                    // Ratio = (R1 + R2) / R2
                    var ratio = (r1 + r2) / r2;
                    // Vpin = Vbat * (R2 / (R1 + R2))
                    var vpin = vbat * (r2 / (r1 + r2));
                    // I = Vbat / (R1 + R2)
                    var iAmps = vbat / (r1 + r2);
                    var iMicroAmps = iAmps * 1000000;

                    // Si es simétrico 1M + 1M se sugiere 2.05 empírico
                    var esUnMeg = (Math.abs(r1 - 1000000) < 1000 && Math.abs(r2 - 1000000) < 1000);
                    var ratioRecomendado = esUnMeg ? 2.05 : ratio;

                    resRatio.textContent = ratio.toFixed(3);
                    if (esUnMeg) {
                        resRatioRecTag.style.display = 'inline';
                        resRatioRecTag.textContent = i18n.rec205;
                    } else if (Math.abs(r1 - 1000000) < 1000 && Math.abs(r2 - 1500000) < 1000) {
                        resRatioRecTag.style.display = 'inline';
                        resRatioRecTag.textContent = i18n.rec167;
                    } else {
                        resRatioRecTag.style.display = 'none';
                    }

                    resVpin.textContent = vpin.toFixed(3);

                    // Evaluación de seguridad del pin
                    badgeSeguridad.className = 'badge-seguridad';
                    if (vpin < 3.0) {
                        badgeSeguridad.classList.add('seguro');
                        badgeSeguridadIcono.textContent = '✓';
                        badgeSeguridadTexto.textContent = i18n.safe;
                    } else if (vpin <= 3.3) {
                        badgeSeguridad.classList.add('precaucion');
                        badgeSeguridadIcono.textContent = '⚠️';
                        badgeSeguridadTexto.textContent = i18n.caution;
                    } else {
                        badgeSeguridad.classList.add('peligro');
                        badgeSeguridadIcono.textContent = '⛔';
                        badgeSeguridadTexto.textContent = i18n.danger;
                    }

                    // Evaluación de consumo parásito
                    if (iMicroAmps < 1000) {
                        resCorriente.textContent = iMicroAmps.toFixed(2);
                        resCorrienteUnidad.textContent = 'µA';
                    } else {
                        resCorriente.textContent = (iMicroAmps / 1000).toFixed(2);
                        resCorrienteUnidad.textContent = 'mA';
                    }

                    if (iMicroAmps < 10) {
                        resCorrienteEval.textContent = i18n.optimalDrain;
                        resCorrienteEval.style.color = '#10b981';
                    } else if (iMicroAmps < 100) {
                        resCorrienteEval.textContent = i18n.lowDrain.replace(':val', iMicroAmps.toFixed(1));
                        resCorrienteEval.style.color = 'var(--color-texto-2)';
                    } else {
                        resCorrienteEval.textContent = i18n.highDrain;
                        resCorrienteEval.style.color = '#f59e0b';
                    }

                    // Actualizar Snippets
                    var valorAjuste = ratioRecomendado.toFixed(esUnMeg ? 2 : 2);
                    cliCommand.textContent = 'meshtastic --set power.adc_multiplier_override ' + valorAjuste;
                    yamlSnippet.textContent = 'power:\n  adc_multiplier_override: ' + valorAjuste;

                    // Actualizar tabla de niveles
                    actualizarTabla(r1, r2, ratioRecomendado);
                }

                function actualizarTabla(r1, r2, ratio) {
                    var html = '';
                    i18n.levels.forEach(function(item) {
                        var vpin = item.vbat * (r2 / (r1 + r2));
                        var esSeguro = vpin < 3.0;
                        var estiloBadge = esSeguro ? 'color: #10b981;' : (vpin <= 3.3 ? 'color: #f59e0b;' : 'color: #ef4444;');
                        var margenTexto = vpin < 3.0 ? i18n.marginOptimal : (vpin <= 3.3 ? i18n.marginAcceptable : i18n.marginDanger);
                        html += '<tr style="border-bottom: 1px solid var(--color-borde);">';
                        html += '<td style="padding: 0.65rem 0.5rem; font-weight: 500;">' + item.estado + '</td>';
                        html += '<td style="padding: 0.65rem 0.5rem; font-family: var(--fuente-mono); font-weight: 700;">' + item.vbat.toFixed(2) + ' V</td>';
                        html += '<td style="padding: 0.65rem 0.5rem; font-family: var(--fuente-mono); font-weight: 700;">' + vpin.toFixed(3) + ' V</td>';
                        html += '<td style="padding: 0.65rem 0.5rem; font-weight: 600; ' + estiloBadge + '">' + margenTexto + '</td>';
                        html += '</tr>';
                    });
                    tablaBody.innerHTML = html;
                }

                function calcularCalibracion() {
                    var vMulti = parseFloat(calibMulti.value) || 0;
                    var vApp = parseFloat(calibApp.value) || 0;
                    var rActual = parseFloat(calibActual.value) || 0;

                    if (vMulti > 0 && vApp > 0 && rActual > 0) {
                        var nuevoRatio = rActual * (vMulti / vApp);
                        calibResultado.textContent = nuevoRatio.toFixed(3);
                    }
                }

                // Eventos
                [inputR1, unitR1, inputR2, unitR2, inputVbat].forEach(function(el) {
                    el.addEventListener('input', calcular);
                    el.addEventListener('change', calcular);
                });

                [calibMulti, calibApp, calibActual].forEach(function(el) {
                    el.addEventListener('input', calcularCalibracion);
                    el.addEventListener('change', calcularCalibracion);
                });

                // Funciones globales para botones
                window.setPill = function(cual, val, unit) {
                    if (cual === 'r1') {
                        inputR1.value = val;
                        unitR1.value = unit;
                    } else {
                        inputR2.value = val;
                        unitR2.value = unit;
                    }
                    calcular();
                };

                window.setVbat = function(val) {
                    inputVbat.value = val.toFixed(2);
                    calcular();
                };

                window.cargarValores = function(r1Val, r1Unit, r2Val, r2Unit, ratioAjuste) {
                    inputR1.value = r1Val;
                    unitR1.value = r1Unit;
                    inputR2.value = r2Val;
                    unitR2.value = r2Unit;
                    calcular();
                    var seccion = document.getElementById('calculadora-interactiva');
                    if (seccion) {
                        seccion.scrollIntoView({ behavior: 'smooth' });
                    }
                };

                window.copiarAlPortapapeles = function(elementId, btn) {
                    var texto = document.getElementById(elementId).innerText || document.getElementById(elementId).textContent;
                    navigator.clipboard.writeText(texto.trim()).then(function() {
                        var original = btn.textContent;
                        btn.textContent = '{{ __("portal.tools.battery.copied") }}';
                        btn.style.borderColor = '#10b981';
                        btn.style.color = '#10b981';
                        setTimeout(function() {
                            btn.textContent = original;
                            btn.style.borderColor = '';
                            btn.style.color = '';
                        }, 2000);
                    });
                };

                // Inicializar
                calcular();
                calcularCalibracion();
            })();
        </script>
    </x-slot:scripts>
</x-layout>
