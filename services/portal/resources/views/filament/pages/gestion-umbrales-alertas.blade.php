<x-filament-panels::page>
    <div class="fi-umbrales-admin-page">
        <style>
            /* ==============================================================================
             * SISTEMA VISUAL DESIGN.MD: GESTIÓN DE UMBRALES DE ALERTAS Y DIAGNÓSTICO
             * ============================================================================== */
            .fi-umbrales-admin-page {
                display: flex;
                flex-direction: column;
                gap: 1.5rem;
                width: 100%;
                font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                color: #1f2937;
            }
            .dark .fi-umbrales-admin-page {
                color: #e5e7eb;
            }

            /* Salvaguarda estricta: constreñir todos los SVGs dentro de la página para evitar explosión a 100% */
            .fi-umbrales-admin-page svg {
                width: 1.25rem !important;
                height: 1.25rem !important;
                max-width: 1.25rem !important;
                max-height: 1.25rem !important;
                display: inline-block !important;
                vertical-align: middle !important;
                flex-shrink: 0 !important;
            }
            .fi-umbrales-admin-page .fi-icon-header svg {
                width: 1.5rem !important;
                height: 1.5rem !important;
                max-width: 1.5rem !important;
                max-height: 1.5rem !important;
            }
            .fi-umbrales-admin-page .fi-icon-btn svg {
                width: 1.1rem !important;
                height: 1.1rem !important;
                max-width: 1.1rem !important;
                max-height: 1.1rem !important;
            }

            /* Banner Superior de Contexto */
            .fi-umbrales-banner {
                display: flex;
                align-items: center;
                justify-content: space-between;
                flex-wrap: wrap;
                gap: 1.25rem;
                padding: 1.25rem 1.5rem;
                border-radius: 0.75rem;
                background: linear-gradient(135deg, rgba(0, 122, 51, 0.08) 0%, rgba(103, 234, 148, 0.04) 100%);
                border: 1px solid rgba(0, 122, 51, 0.25);
            }
            .dark .fi-umbrales-banner {
                background: linear-gradient(135deg, rgba(28, 58, 40, 0.5) 0%, rgba(44, 45, 60, 0.5) 100%);
                border-color: rgba(103, 234, 148, 0.25);
            }
            .fi-umbrales-banner-left {
                display: flex;
                align-items: center;
                gap: 1rem;
                flex: 1;
                min-width: 250px;
            }
            .fi-umbrales-banner-icon {
                display: flex;
                align-items: center;
                justify-content: center;
                width: 2.75rem;
                height: 2.75rem;
                border-radius: 0.5rem;
                background: #007A33;
                color: #ffffff;
                flex-shrink: 0;
            }
            .dark .fi-umbrales-banner-icon {
                background: #15612F;
                color: #67EA94;
            }
            .fi-umbrales-banner-title {
                font-size: 1.15rem;
                font-weight: 700;
                color: #111827;
                margin: 0;
            }
            .dark .fi-umbrales-banner-title {
                color: #ffffff;
            }
            .fi-umbrales-banner-sub {
                font-size: 0.85rem;
                color: #4b5563;
                margin-top: 0.2rem;
                margin-bottom: 0;
            }
            .dark .fi-umbrales-banner-sub {
                color: #9ca3af;
            }

            /* Botones de acción */
            .fi-umbrales-btn-save {
                display: inline-flex;
                align-items: center;
                gap: 0.45rem;
                padding: 0.55rem 1.25rem;
                border-radius: 0.5rem;
                background: #007A33;
                color: #ffffff;
                font-size: 0.85rem;
                font-weight: 600;
                border: none;
                cursor: pointer;
                transition: background-color 0.15s ease;
            }
            .fi-umbrales-btn-save:hover {
                background: #005a26;
            }
            .fi-umbrales-btn-save:disabled {
                opacity: 0.6;
                cursor: not-allowed;
            }
            .dark .fi-umbrales-btn-save {
                background: #15612F;
            }
            .dark .fi-umbrales-btn-save:hover {
                background: #007A33;
            }

            /* Selector de Pestañas */
            .fi-umbrales-tabs-bar {
                border-bottom: 2px solid rgba(148, 163, 184, 0.2);
            }
            .fi-umbrales-tabs-nav {
                display: flex;
                gap: 1.5rem;
                flex-wrap: wrap;
                margin-bottom: -2px;
            }
            .fi-umbrales-tab-btn {
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                padding: 0.75rem 0.25rem;
                border: none;
                background: transparent;
                cursor: pointer;
                font-size: 0.875rem;
                font-weight: 500;
                color: #6b7280;
                border-bottom: 3px solid transparent;
                transition: all 0.15s ease;
            }
            .dark .fi-umbrales-tab-btn {
                color: #9ca3af;
            }
            .fi-umbrales-tab-btn:hover {
                color: #111827;
                border-bottom-color: #cbd5e1;
            }
            .dark .fi-umbrales-tab-btn:hover {
                color: #f3f4f6;
                border-bottom-color: #4b5563;
            }
            .fi-umbrales-tab-btn.active {
                color: #007A33;
                font-weight: 700;
                border-bottom-color: #007A33;
            }
            .dark .fi-umbrales-tab-btn.active {
                color: #67EA94;
                border-bottom-color: #67EA94;
            }

            .fi-umbrales-tab-badge {
                font-size: 0.7rem;
                font-weight: 700;
                padding: 0.15rem 0.5rem;
                border-radius: 9999px;
                text-transform: uppercase;
            }
            .fi-tab-badge-router {
                background: rgba(0, 122, 51, 0.12);
                color: #007A33;
            }
            .dark .fi-tab-badge-router {
                background: rgba(103, 234, 148, 0.15);
                color: #67EA94;
            }
            .fi-tab-badge-client {
                background: rgba(59, 130, 246, 0.12);
                color: #2563eb;
            }
            .dark .fi-tab-badge-client {
                background: rgba(96, 165, 250, 0.15);
                color: #93c5fd;
            }
            .fi-tab-badge-mute {
                background: rgba(168, 85, 247, 0.12);
                color: #9333ea;
            }
            .dark .fi-tab-badge-mute {
                background: rgba(192, 132, 252, 0.15);
                color: #d8b4fe;
            }

            .fi-umbrales-tab-desc {
                font-size: 0.8rem;
                line-height: 1.4;
                color: #6b7280;
                padding: 0.25rem 0;
            }
            .dark .fi-umbrales-tab-desc {
                color: #9ca3af;
            }

            /* Rejilla de Secciones */
            .fi-umbrales-grid {
                display: grid;
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }
            @media (min-width: 1024px) {
                .fi-umbrales-grid {
                    grid-template-columns: repeat(2, 1fr);
                }
            }

            /* Tarjetas de Parámetros */
            .fi-umbrales-card {
                border-radius: 0.75rem;
                border: 1px solid #e5e7eb;
                background: #ffffff;
                padding: 1.25rem 1.5rem;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
                display: flex;
                flex-direction: column;
                gap: 1.25rem;
            }
            .dark .fi-umbrales-card {
                background: #2C2D3C;
                border-color: #3D3E4D;
                box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
            }
            .fi-umbrales-card-header {
                display: flex;
                align-items: center;
                gap: 0.75rem;
                padding-bottom: 0.75rem;
                border-bottom: 1px solid #f3f4f6;
            }
            .dark .fi-umbrales-card-header {
                border-bottom-color: #383a4c;
            }
            .fi-umbrales-card-header-icon {
                color: #007A33;
                display: flex;
                align-items: center;
            }
            .dark .fi-umbrales-card-header-icon {
                color: #67EA94;
            }
            .fi-umbrales-card-title {
                font-size: 0.95rem;
                font-weight: 700;
                color: #111827;
                margin: 0;
            }
            .dark .fi-umbrales-card-title {
                color: #f9fafb;
            }
            .fi-umbrales-card-desc {
                font-size: 0.75rem;
                color: #6b7280;
                margin: 0.15rem 0 0 0;
            }
            .dark .fi-umbrales-card-desc {
                color: #9ca3af;
            }

            /* Campos de Formulario */
            .fi-umbrales-field-group {
                display: flex;
                flex-direction: column;
                gap: 1rem;
            }
            .fi-umbrales-field {
                display: flex;
                flex-direction: column;
                gap: 0.35rem;
            }
            .fi-umbrales-label {
                font-size: 0.8rem;
                font-weight: 600;
                color: #374151;
            }
            .dark .fi-umbrales-label {
                color: #d1d5db;
            }
            .fi-umbrales-input-wrapper {
                position: relative;
                display: flex;
                align-items: center;
            }
            .fi-umbrales-input {
                width: 100%;
                height: 2.35rem;
                border-radius: 0.5rem;
                border: 1px solid #d1d5db;
                background: #ffffff;
                color: #111827;
                padding: 0 4.5rem 0 0.75rem;
                font-size: 0.875rem;
                transition: border-color 0.15s ease, box-shadow 0.15s ease;
                outline: none;
                box-sizing: border-box;
            }
            .dark .fi-umbrales-input {
                background: #1F2029;
                border-color: #4b5563;
                color: #f9fafb;
            }
            .fi-umbrales-input:focus {
                border-color: #007A33;
                box-shadow: 0 0 0 2px rgba(0, 122, 51, 0.2);
            }
            .dark .fi-umbrales-input:focus {
                border-color: #67EA94;
                box-shadow: 0 0 0 2px rgba(103, 234, 148, 0.25);
            }
            .fi-umbrales-input:disabled {
                opacity: 0.5;
                cursor: not-allowed;
                background: #f3f4f6;
            }
            .dark .fi-umbrales-input:disabled {
                background: #161720;
            }
            .fi-umbrales-unit-tag {
                position: absolute;
                right: 0.75rem;
                font-size: 0.75rem;
                font-weight: 500;
                color: #9ca3af;
                pointer-events: none;
            }
            .dark .fi-umbrales-unit-tag {
                color: #6b7280;
            }
            .fi-umbrales-field-note {
                font-size: 0.72rem;
                color: #9ca3af;
                margin-top: 0.15rem;
            }
            .dark .fi-umbrales-field-note {
                color: #6b7280;
            }
            .fi-umbrales-error {
                font-size: 0.72rem;
                font-weight: 600;
                color: #ef4444;
                margin-top: 0.15rem;
            }

            /* Pie de página */
            .fi-umbrales-footer {
                display: flex;
                align-items: center;
                justify-content: flex-end;
                gap: 1rem;
                padding-top: 1.25rem;
                border-top: 1px solid rgba(148, 163, 184, 0.2);
            }
        </style>

        {{-- Banner Informativo de Contexto --}}
        <div class="fi-umbrales-banner">
            <div class="fi-umbrales-banner-left">
                <div class="fi-umbrales-banner-icon fi-icon-header">
                    <x-heroicon-o-adjustments-horizontal />
                </div>
                <div>
                    <h3 class="fi-umbrales-banner-title">
                        {{ __('admin.alert_thresholds.title') }}
                    </h3>
                    <p class="fi-umbrales-banner-sub">
                        {{ __('admin.alert_thresholds.subheading') }}
                    </p>
                </div>
            </div>
            <div>
                <button
                    type="button"
                    wire:click="save"
                    wire:loading.attr="disabled"
                    class="fi-umbrales-btn-save fi-icon-btn"
                >
                    <span wire:loading.remove wire:target="save">
                        <x-heroicon-m-check />
                    </span>
                    <span wire:loading wire:target="save" style="display: inline-block; animation: spin 1s linear infinite;">
                        <x-heroicon-m-arrow-path />
                    </span>
                    <span>{{ __('admin.alert_thresholds.btn_save') }}</span>
                </button>
            </div>
        </div>

        {{-- Selector de Pestañas por Grupo de Roles --}}
        <div class="fi-umbrales-tabs-bar">
            <nav class="fi-umbrales-tabs-nav" aria-label="Tabs">
                <button
                    type="button"
                    wire:click="setActiveTab('router')"
                    class="fi-umbrales-tab-btn {{ $activeTab === 'router' ? 'active' : '' }}"
                >
                    <x-heroicon-o-server-stack />
                    <span>{{ __('admin.alert_thresholds.tab_router') }}</span>
                    <span class="fi-umbrales-tab-badge fi-tab-badge-router">ROUTER</span>
                </button>

                <button
                    type="button"
                    wire:click="setActiveTab('client')"
                    class="fi-umbrales-tab-btn {{ $activeTab === 'client' ? 'active' : '' }}"
                >
                    <x-heroicon-o-device-phone-mobile />
                    <span>{{ __('admin.alert_thresholds.tab_client') }}</span>
                    <span class="fi-umbrales-tab-badge fi-tab-badge-client">CLIENT</span>
                </button>

                <button
                    type="button"
                    wire:click="setActiveTab('client_mute')"
                    class="fi-umbrales-tab-btn {{ $activeTab === 'client_mute' ? 'active' : '' }}"
                >
                    <x-heroicon-o-speaker-x-mark />
                    <span>{{ __('admin.alert_thresholds.tab_client_mute') }}</span>
                    <span class="fi-umbrales-tab-badge fi-tab-badge-mute">MUTE</span>
                </button>
            </nav>
        </div>

        {{-- Explicación del grupo activo --}}
        <div class="fi-umbrales-tab-desc">
            @if ($activeTab === 'router')
                {{ __('admin.alert_thresholds.tab_router_desc') }}
            @elseif ($activeTab === 'client')
                {{ __('admin.alert_thresholds.tab_client_desc') }}
            @else
                {{ __('admin.alert_thresholds.tab_client_mute_desc') }}
            @endif
        </div>

        {{-- Rejilla de Secciones de Parámetros --}}
        <div class="fi-umbrales-grid">

            {{-- 1. Saltos LoRa en Origen --}}
            <div class="fi-umbrales-card">
                <div class="fi-umbrales-card-header">
                    <span class="fi-umbrales-card-header-icon">
                        <x-heroicon-o-arrow-trending-up />
                    </span>
                    <div>
                        <h4 class="fi-umbrales-card-title">{{ __('admin.alert_thresholds.sec_hops') }}</h4>
                        <p class="fi-umbrales-card-desc">{{ __('admin.alert_thresholds.sec_hops_desc') }}</p>
                    </div>
                </div>

                <div class="fi-umbrales-field-group">
                    <div class="fi-umbrales-field">
                        <label class="fi-umbrales-label">
                            {{ __('admin.alert_thresholds.param_hop_limit_recommended') }}
                        </label>
                        <div class="fi-umbrales-input-wrapper">
                            <input
                                type="number"
                                step="1"
                                min="1"
                                max="7"
                                wire:model="umbrales.{{ $activeTab }}.hop_limit_recommended"
                                class="fi-umbrales-input"
                            />
                            <span class="fi-umbrales-unit-tag">saltos</span>
                        </div>
                        @error("umbrales.{$activeTab}.hop_limit_recommended")
                            <p class="fi-umbrales-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="fi-umbrales-field">
                        <label class="fi-umbrales-label">
                            {{ __('admin.alert_thresholds.param_hop_limit_warning') }}
                        </label>
                        <div class="fi-umbrales-input-wrapper">
                            <input
                                type="number"
                                step="1"
                                min="1"
                                max="7"
                                wire:model="umbrales.{{ $activeTab }}.hop_limit_warning"
                                class="fi-umbrales-input"
                            />
                            <span class="fi-umbrales-unit-tag">saltos</span>
                        </div>
                        @error("umbrales.{$activeTab}.hop_limit_warning")
                            <p class="fi-umbrales-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="fi-umbrales-field">
                        <label class="fi-umbrales-label">
                            {{ __('admin.alert_thresholds.param_hop_limit_critical') }}
                        </label>
                        <div class="fi-umbrales-input-wrapper">
                            <input
                                type="number"
                                step="1"
                                min="1"
                                max="7"
                                wire:model="umbrales.{{ $activeTab }}.hop_limit_critical"
                                class="fi-umbrales-input"
                            />
                            <span class="fi-umbrales-unit-tag">saltos</span>
                        </div>
                        @error("umbrales.{$activeTab}.hop_limit_critical")
                            <p class="fi-umbrales-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- 2. Intervalos de Emisión --}}
            <div class="fi-umbrales-card">
                <div class="fi-umbrales-card-header">
                    <span class="fi-umbrales-card-header-icon">
                        <x-heroicon-o-clock />
                    </span>
                    <div>
                        <h4 class="fi-umbrales-card-title">{{ __('admin.alert_thresholds.sec_intervals') }}</h4>
                        <p class="fi-umbrales-card-desc">{{ __('admin.alert_thresholds.sec_intervals_desc') }}</p>
                    </div>
                </div>

                <div class="fi-umbrales-field-group">
                    <div class="fi-umbrales-field">
                        <label class="fi-umbrales-label">
                            {{ __('admin.alert_thresholds.param_nodeinfo_interval_min_s') }}
                        </label>
                        <div class="fi-umbrales-input-wrapper">
                            <input
                                type="number"
                                step="60"
                                min="60"
                                wire:model="umbrales.{{ $activeTab }}.nodeinfo_interval_min_s"
                                class="fi-umbrales-input"
                            />
                            <span class="fi-umbrales-unit-tag">segundos</span>
                        </div>
                        @error("umbrales.{$activeTab}.nodeinfo_interval_min_s")
                            <p class="fi-umbrales-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="fi-umbrales-field">
                        <label class="fi-umbrales-label">
                            {{ __('admin.alert_thresholds.param_nodeinfo_interval_recommended_s') }}
                        </label>
                        <div class="fi-umbrales-input-wrapper">
                            <input
                                type="number"
                                step="60"
                                min="60"
                                wire:model="umbrales.{{ $activeTab }}.nodeinfo_interval_recommended_s"
                                class="fi-umbrales-input"
                            />
                            <span class="fi-umbrales-unit-tag">segundos</span>
                        </div>
                        @error("umbrales.{$activeTab}.nodeinfo_interval_recommended_s")
                            <p class="fi-umbrales-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="fi-umbrales-field">
                        <label class="fi-umbrales-label">
                            {{ __('admin.alert_thresholds.param_telemetry_interval_min_s') }}
                        </label>
                        <div class="fi-umbrales-input-wrapper">
                            <input
                                type="number"
                                step="60"
                                min="60"
                                wire:model="umbrales.{{ $activeTab }}.telemetry_interval_min_s"
                                class="fi-umbrales-input"
                            />
                            <span class="fi-umbrales-unit-tag">segundos</span>
                        </div>
                        @error("umbrales.{$activeTab}.telemetry_interval_min_s")
                            <p class="fi-umbrales-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="fi-umbrales-field">
                        <label class="fi-umbrales-label">
                            {{ __('admin.alert_thresholds.param_telemetry_interval_recommended_s') }}
                        </label>
                        <div class="fi-umbrales-input-wrapper">
                            <input
                                type="number"
                                step="60"
                                min="60"
                                wire:model="umbrales.{{ $activeTab }}.telemetry_interval_recommended_s"
                                class="fi-umbrales-input"
                            />
                            <span class="fi-umbrales-unit-tag">segundos</span>
                        </div>
                        @error("umbrales.{$activeTab}.telemetry_interval_recommended_s")
                            <p class="fi-umbrales-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- 3. Batería y Energía --}}
            <div class="fi-umbrales-card">
                <div class="fi-umbrales-card-header">
                    <span class="fi-umbrales-card-header-icon">
                        <x-heroicon-o-bolt />
                    </span>
                    <div>
                        <h4 class="fi-umbrales-card-title">{{ __('admin.alert_thresholds.sec_battery') }}</h4>
                        <p class="fi-umbrales-card-desc">{{ __('admin.alert_thresholds.sec_battery_desc') }}</p>
                    </div>
                </div>

                <div class="fi-umbrales-field-group">
                    <div class="fi-umbrales-field">
                        <label class="fi-umbrales-label">
                            {{ __('admin.alert_thresholds.param_battery_warning_pct') }}
                        </label>
                        <div class="fi-umbrales-input-wrapper">
                            <input
                                type="number"
                                step="1"
                                min="0"
                                max="100"
                                wire:model="umbrales.{{ $activeTab }}.battery_warning_pct"
                                class="fi-umbrales-input"
                            />
                            <span class="fi-umbrales-unit-tag">%</span>
                        </div>
                        @error("umbrales.{$activeTab}.battery_warning_pct")
                            <p class="fi-umbrales-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="fi-umbrales-field">
                        <label class="fi-umbrales-label">
                            {{ __('admin.alert_thresholds.param_battery_critical_pct') }}
                        </label>
                        <div class="fi-umbrales-input-wrapper">
                            <input
                                type="number"
                                step="1"
                                min="0"
                                max="100"
                                wire:model="umbrales.{{ $activeTab }}.battery_critical_pct"
                                class="fi-umbrales-input"
                            />
                            <span class="fi-umbrales-unit-tag">%</span>
                        </div>
                        @error("umbrales.{$activeTab}.battery_critical_pct")
                            <p class="fi-umbrales-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="fi-umbrales-field">
                        <label class="fi-umbrales-label">
                            {{ __('admin.alert_thresholds.param_battery_resolution_pct') }}
                        </label>
                        <div class="fi-umbrales-input-wrapper">
                            <input
                                type="number"
                                step="1"
                                min="0"
                                max="100"
                                wire:model="umbrales.{{ $activeTab }}.battery_resolution_pct"
                                class="fi-umbrales-input"
                            />
                            <span class="fi-umbrales-unit-tag">%</span>
                        </div>
                        @error("umbrales.{$activeTab}.battery_resolution_pct")
                            <p class="fi-umbrales-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="fi-umbrales-field">
                        <label class="fi-umbrales-label">
                            {{ __('admin.alert_thresholds.param_sunset_battery_min_pct') }}
                        </label>
                        <div class="fi-umbrales-input-wrapper">
                            <input
                                type="number"
                                step="1"
                                min="0"
                                max="100"
                                wire:model="umbrales.{{ $activeTab }}.sunset_battery_min_pct"
                                @if ($activeTab !== 'router') disabled @endif
                                class="fi-umbrales-input"
                            />
                            <span class="fi-umbrales-unit-tag">%</span>
                        </div>
                        @if ($activeTab !== 'router')
                            <p class="fi-umbrales-field-note">Control solar inactivo para este grupo de nodos (0 = desactivado).</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- 4. Silencios y Reinicios --}}
            <div class="fi-umbrales-card">
                <div class="fi-umbrales-card-header">
                    <span class="fi-umbrales-card-header-icon">
                        <x-heroicon-o-signal-slash />
                    </span>
                    <div>
                        <h4 class="fi-umbrales-card-title">{{ __('admin.alert_thresholds.sec_silence') }}</h4>
                        <p class="fi-umbrales-card-desc">{{ __('admin.alert_thresholds.sec_silence_desc') }}</p>
                    </div>
                </div>

                <div class="fi-umbrales-field-group">
                    <div class="fi-umbrales-field">
                        <label class="fi-umbrales-label">
                            {{ __('admin.alert_thresholds.param_silence_warning_h') }}
                        </label>
                        <div class="fi-umbrales-input-wrapper">
                            <input
                                type="number"
                                step="0.5"
                                min="0"
                                wire:model="umbrales.{{ $activeTab }}.silence_warning_h"
                                @if ($activeTab !== 'router') disabled @endif
                                class="fi-umbrales-input"
                            />
                            <span class="fi-umbrales-unit-tag">horas</span>
                        </div>
                        @if ($activeTab !== 'router')
                            <p class="fi-umbrales-field-note">Inactivo en nodos clientes y portables (0 = desactivado).</p>
                        @endif
                    </div>

                    <div class="fi-umbrales-field">
                        <label class="fi-umbrales-label">
                            {{ __('admin.alert_thresholds.param_silence_critical_h') }}
                        </label>
                        <div class="fi-umbrales-input-wrapper">
                            <input
                                type="number"
                                step="0.5"
                                min="0"
                                wire:model="umbrales.{{ $activeTab }}.silence_critical_h"
                                @if ($activeTab !== 'router') disabled @endif
                                class="fi-umbrales-input"
                            />
                            <span class="fi-umbrales-unit-tag">horas</span>
                        </div>
                        @if ($activeTab !== 'router')
                            <p class="fi-umbrales-field-note">Inactivo en nodos clientes y portables (0 = desactivado).</p>
                        @endif
                    </div>

                    <div class="fi-umbrales-field">
                        <label class="fi-umbrales-label">
                            {{ __('admin.alert_thresholds.param_reboot_loop_count_5m') }}
                        </label>
                        <div class="fi-umbrales-input-wrapper">
                            <input
                                type="number"
                                step="1"
                                min="1"
                                wire:model="umbrales.{{ $activeTab }}.reboot_loop_count_5m"
                                class="fi-umbrales-input"
                            />
                            <span class="fi-umbrales-unit-tag">reinicios</span>
                        </div>
                        @error("umbrales.{$activeTab}.reboot_loop_count_5m")
                            <p class="fi-umbrales-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="fi-umbrales-field">
                        <label class="fi-umbrales-label">
                            {{ __('admin.alert_thresholds.param_reboot_loop_count_10m') }}
                        </label>
                        <div class="fi-umbrales-input-wrapper">
                            <input
                                type="number"
                                step="1"
                                min="1"
                                wire:model="umbrales.{{ $activeTab }}.reboot_loop_count_10m"
                                class="fi-umbrales-input"
                            />
                            <span class="fi-umbrales-unit-tag">reinicios</span>
                        </div>
                        @error("umbrales.{$activeTab}.reboot_loop_count_10m")
                            <p class="fi-umbrales-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

        </div>

        {{-- Barra de Guardar al Pie --}}
        <div class="fi-umbrales-footer">
            <button
                type="button"
                wire:click="save"
                wire:loading.attr="disabled"
                class="fi-umbrales-btn-save fi-icon-btn"
            >
                <span wire:loading.remove wire:target="save">
                    <x-heroicon-m-check />
                </span>
                <span wire:loading wire:target="save" style="display: inline-block; animation: spin 1s linear infinite;">
                    <x-heroicon-m-arrow-path />
                </span>
                <span>{{ __('admin.alert_thresholds.btn_save') }}</span>
            </button>
        </div>
    </div>
</x-filament-panels::page>
