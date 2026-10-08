<x-filament-widgets::widget>
    <div class="fi-ambito-wrapper">
        <div class="fi-ambito-header">
            <span class="fi-ambito-title">
                {{ __('admin.ambitos.heading') }}
            </span>
            <div class="fi-ambito-context-indicator">
                <span>{{ __('admin.ambitos.active_context') }}</span>
                <strong style="color: {{ $activo_andalucia && $activo_espana ? '#67EA94' : ($activo_andalucia ? '#67EA94' : '#FCA5A5') }}; font-weight: 700; margin-left: 0.25rem;">
                    @if($activo_andalucia && $activo_espana)
                        {{ __('admin.ambitos.andalucia_title') }} + {{ __('admin.ambitos.espana_title') }}
                    @elseif($activo_andalucia)
                        {{ __('admin.ambitos.andalucia_title') }}
                    @else
                        {{ __('admin.ambitos.espana_title') }}
                    @endif
                </strong>
            </div>
        </div>

        <div class="fi-ambito-grid">
            {{-- 1. ANDALUCÍA (Activo por defecto) --}}
            <button
                type="button"
                wire:click="toggleAmbito('andalucia')"
                class="fi-ambito-card {{ $activo_andalucia ? 'fi-ambito-card-andalucia-active' : 'fi-ambito-card-inactive' }}"
                aria-pressed="{{ $activo_andalucia ? 'true' : 'false' }}"
                title="{{ $activo_andalucia ? __('admin.ambitos.click_to_deactivate') : __('admin.ambitos.click_to_activate') }}"
            >
                <div class="fi-ambito-card-left">
                    <div class="fi-ambito-flag-wrapper">
                        <x-icono-bandera idioma="es" :tamano="38" />
                    </div>
                    <div class="fi-ambito-card-info">
                        <div class="fi-ambito-card-title-row">
                            <span class="fi-ambito-card-name">
                                {{ __('admin.ambitos.andalucia_title') }}
                            </span>
                        </div>
                        <span class="fi-ambito-card-desc">
                            {{ __('admin.ambitos.andalucia_desc') }}
                        </span>
                    </div>
                </div>

                <div class="fi-ambito-card-right">
                    <span class="fi-ambito-count-badge {{ $activo_andalucia ? 'fi-ambito-count-badge-andalucia' : '' }}">
                        {{ trans_choice('admin.ambitos.routers_count', $conteo_andalucia, ['count' => $conteo_andalucia]) }}
                    </span>
                    <span class="fi-ambito-switch-pill {{ $activo_andalucia ? 'fi-ambito-switch-pill-on-andalucia' : 'fi-ambito-switch-pill-off' }}">
                        @if($activo_andalucia)
                            ✓ {{ __('admin.ambitos.active_badge') }}
                        @else
                            ＋ {{ __('admin.ambitos.add_badge') }}
                        @endif
                    </span>
                </div>
            </button>

            {{-- 2. ESPAÑA --}}
            <button
                type="button"
                wire:click="toggleAmbito('espana')"
                class="fi-ambito-card {{ $activo_espana ? 'fi-ambito-card-espana-active' : 'fi-ambito-card-inactive' }}"
                aria-pressed="{{ $activo_espana ? 'true' : 'false' }}"
                title="{{ $activo_espana ? __('admin.ambitos.click_to_deactivate') : __('admin.ambitos.click_to_activate') }}"
            >
                <div class="fi-ambito-card-left">
                    <div class="fi-ambito-flag-wrapper">
                        <x-icono-bandera idioma="espana" :tamano="38" />
                    </div>
                    <div class="fi-ambito-card-info">
                        <div class="fi-ambito-card-title-row">
                            <span class="fi-ambito-card-name">
                                {{ __('admin.ambitos.espana_title') }}
                            </span>
                        </div>
                        <span class="fi-ambito-card-desc">
                            {{ __('admin.ambitos.espana_desc') }}
                        </span>
                    </div>
                </div>

                <div class="fi-ambito-card-right">
                    <span class="fi-ambito-count-badge {{ $activo_espana ? 'fi-ambito-count-badge-espana' : '' }}">
                        {{ trans_choice('admin.ambitos.routers_count', $conteo_espana, ['count' => $conteo_espana]) }}
                    </span>
                    <span class="fi-ambito-switch-pill {{ $activo_espana ? 'fi-ambito-switch-pill-on-espana' : 'fi-ambito-switch-pill-off' }}">
                        @if($activo_espana)
                            ✓ {{ __('admin.ambitos.active_badge') }}
                        @else
                            ＋ {{ __('admin.ambitos.add_badge') }}
                        @endif
                    </span>
                </div>
            </button>
        </div>
    </div>
</x-filament-widgets::widget>
