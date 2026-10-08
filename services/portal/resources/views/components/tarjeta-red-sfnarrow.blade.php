{{-- ==============================================================================
     tarjeta-red-sfnarrow.blade.php
     Tarjeta llamativa y altamente visual con los parámetros oficiales de radio LoRa SFNarrow.
     Material Design, contraste AAA WCAG 2.1 en modo claro y oscuro.
     ============================================================================== --}}

<div class="tarjeta-sfnarrow-destacada tarjeta-tabla-andalucia" style="padding: 0; margin: 2rem 0; width: 100%;">
    <!-- Encabezado llamativo con contraste de acento -->
    <div class="tarjeta-tabla-andalucia-cabecera">
        <div>
            <div style="display: flex; align-items: center; gap: 0.6rem; margin-bottom: 0.35rem;">
                <span style="display: inline-block; width: 12px; height: 12px; border-radius: 50%; background: var(--color-acento); box-shadow: 0 0 10px var(--color-acento);"></span>
                <span style="font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: var(--color-enlace);">
                    {{ __('portal.sfnarrow.badge') }}
                </span>
            </div>
            <h2 style="font-size: 1.75rem; font-weight: 800; margin: 0; color: var(--color-texto); line-height: 1.2;">
                {{ __('portal.sfnarrow.title') }}
            </h2>
            <p style="font-size: 0.95rem; color: var(--color-texto-2); margin: 0.35rem 0 0 0;">
                {{ __('portal.sfnarrow.subtitle') }}
            </p>
        </div>
        <div class="tarjeta-destacada-badge-wrapper" style="display: flex; flex-direction: column; align-items: flex-end; gap: 0.4rem;">
            <span class="chip chip-correcto" style="font-size: 0.85rem; font-weight: 700; padding: 0.35rem 0.75rem;">
                {{ __('portal.sfnarrow.badge_narrowband') }}
            </span>
            <span style="font-size: 0.8rem; color: var(--color-texto-3);">
                {{ __('portal.sfnarrow.benefit') }}
            </span>
        </div>
    </div>

    <!-- Rejilla visual de parámetros clave -->
    <div class="tarjeta-tabla-andalucia-cuerpo">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
            
            <!-- Región -->
            <div class="param-box-andalucia">
                <div style="font-size: 0.8rem; font-weight: 600; text-transform: uppercase; color: var(--color-texto-3); margin-bottom: 0.25rem;">
                    {{ __('portal.sfnarrow.region_title') }}
                </div>
                <div style="font-size: 1.25rem; font-weight: 800; color: var(--color-texto); font-family: var(--fuente-mono);">
                    EU_868
                </div>
                <div style="font-size: 0.8rem; color: var(--color-texto-2); margin-top: 0.25rem;">
                    {{ __('portal.sfnarrow.region_desc') }}
                </div>
            </div>

            <!-- Usar Preset (Predefined) -->
            <div class="param-box-andalucia-aviso">
                <div style="font-size: 0.8rem; font-weight: 600; text-transform: uppercase; color: var(--color-aviso-texto); margin-bottom: 0.25rem;">
                    {{ __('portal.sfnarrow.preset_title') }}
                </div>
                <div style="font-size: 1.25rem; font-weight: 800; color: var(--color-aviso-texto);">
                    {{ __('portal.sfnarrow.preset_disable') }}
                </div>
                <div style="font-size: 0.8rem; color: var(--color-aviso-texto); margin-top: 0.25rem;">
                    {{ __('portal.sfnarrow.preset_desc') }}
                </div>
            </div>

            <!-- Bandwidth -->
            <div class="param-box-andalucia">
                <div style="font-size: 0.8rem; font-weight: 600; text-transform: uppercase; color: var(--color-texto-3); margin-bottom: 0.25rem;">
                    {{ __('portal.sfnarrow.bw_title') }}
                </div>
                <div style="font-size: 1.25rem; font-weight: 800; color: var(--color-texto); font-family: var(--fuente-mono);">
                    {{ __('portal.sfnarrow.bw_val') }} <span style="font-size: 0.95rem; font-weight: 600; color: var(--color-texto-2);">{{ __('portal.sfnarrow.bw_sub') }}</span>
                </div>
                <div style="font-size: 0.8rem; color: var(--color-texto-2); margin-top: 0.25rem;">
                    {{ __('portal.sfnarrow.bw_desc') }}
                </div>
            </div>

            <!-- Spreading Factor -->
            <div class="param-box-andalucia">
                <div style="font-size: 0.8rem; font-weight: 600; text-transform: uppercase; color: var(--color-texto-3); margin-bottom: 0.25rem;">
                    {{ __('portal.sfnarrow.sf_title') }}
                </div>
                <div style="font-size: 1.25rem; font-weight: 800; color: var(--color-texto); font-family: var(--fuente-mono);">
                    7
                </div>
                <div style="font-size: 0.8rem; color: var(--color-texto-2); margin-top: 0.25rem;">
                    {{ __('portal.sfnarrow.sf_desc') }}
                </div>
            </div>

            <!-- Coding Rate -->
            <div class="param-box-andalucia">
                <div style="font-size: 0.8rem; font-weight: 600; text-transform: uppercase; color: var(--color-texto-3); margin-bottom: 0.25rem;">
                    {{ __('portal.sfnarrow.cr_title') }}
                </div>
                <div style="font-size: 1.25rem; font-weight: 800; color: var(--color-texto); font-family: var(--fuente-mono);">
                    {{ __('portal.sfnarrow.cr_val') }} <span style="font-size: 0.95rem; font-weight: 600; color: var(--color-texto-2);">{{ __('portal.sfnarrow.cr_sub') }}</span>
                </div>
                <div style="font-size: 0.8rem; color: var(--color-texto-2); margin-top: 0.25rem;">
                    {{ __('portal.sfnarrow.cr_desc') }}
                </div>
            </div>

            <!-- Frequency Slot & Override -->
            <div class="param-box-andalucia-destacado">
                <div style="font-size: 0.8rem; font-weight: 700; text-transform: uppercase; color: var(--color-enlace); margin-bottom: 0.25rem;">
                    {{ __('portal.sfnarrow.slot_title') }}
                </div>
                <div style="font-size: 1.25rem; font-weight: 800; color: var(--color-enlace); font-family: var(--fuente-mono);">
                    {{ __('portal.sfnarrow.slot_val') }} <span style="font-size: 0.95rem; font-weight: 600;">{{ __('portal.sfnarrow.slot_freq') }}</span>
                </div>
                <div style="font-size: 0.8rem; color: var(--color-texto-2); margin-top: 0.25rem;">
                    {{ __('portal.sfnarrow.override_freq') }}
                </div>
            </div>

            <!-- Canal Principal -->
            <div class="param-box-andalucia">
                <div style="font-size: 0.8rem; font-weight: 600; text-transform: uppercase; color: var(--color-texto-3); margin-bottom: 0.25rem;">
                    {{ __('portal.sfnarrow.primary_channel_title') }}
                </div>
                <div style="font-size: 1.25rem; font-weight: 800; color: var(--color-texto); font-family: var(--fuente-mono);">
                    SFNarrow
                </div>
                <div style="font-size: 0.8rem; color: var(--color-texto-2); margin-top: 0.25rem;">
                    {{ __('portal.sfnarrow.primary_channel_desc') }}
                </div>
            </div>

            <!-- Clave PSK -->
            <div class="param-box-andalucia">
                <div style="font-size: 0.8rem; font-weight: 600; text-transform: uppercase; color: var(--color-texto-3); margin-bottom: 0.25rem;">
                    {{ __('portal.sfnarrow.psk_title') }}
                </div>
                <div style="font-size: 1.25rem; font-weight: 800; color: var(--color-texto); font-family: var(--fuente-mono);">
                    AQ==
                </div>
                <div style="font-size: 0.8rem; color: var(--color-texto-2); margin-top: 0.25rem;">
                    {{ __('portal.sfnarrow.psk_desc') }}
                </div>
            </div>

            <!-- Límite de Saltos -->
            <div class="param-box-andalucia">
                <div style="font-size: 0.8rem; font-weight: 600; text-transform: uppercase; color: var(--color-texto-3); margin-bottom: 0.25rem;">
                    {{ __('portal.sfnarrow.hops_title') }}
                </div>
                <div style="font-size: 1.25rem; font-weight: 800; color: var(--color-texto); font-family: var(--fuente-mono);">
                    {{ __('portal.sfnarrow.hops_val') }} <span style="font-size: 0.95rem; font-weight: 600; color: var(--color-texto-2);">{{ __('portal.sfnarrow.hops_sub') }}</span>
                </div>
                <div style="font-size: 0.8rem; color: var(--color-texto-2); margin-top: 0.25rem;">
                    {{ __('portal.sfnarrow.hops_desc') }}
                </div>
            </div>

        </div>

        <!-- Alerta para administración remota -->
        <div style="padding: 0.9rem 1.15rem; background: rgba(0, 122, 51, 0.08); border-left: 4px solid var(--color-enlace); border-radius: var(--radio-sm); font-size: 0.9rem; color: var(--color-texto-2); line-height: 1.5;">
            <strong>💡 {{ __('portal.sfnarrow.remote_advice_title') }}</strong> {{ __('portal.sfnarrow.remote_advice_text') }} 
            <code>{{ __('portal.sfnarrow.remote_advice_order') }}</code>
        </div>
    </div>
</div>
