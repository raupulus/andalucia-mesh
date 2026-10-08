{{-- ==============================================================================
     tarjeta-red-sfnarrow.blade.php
     Tarjeta llamativa y altamente visual con los parámetros oficiales de radio LoRa SFNarrow.
     Material Design, contraste AAA WCAG 2.1 en modo claro y oscuro.
     ============================================================================== --}}

<div class="tarjeta-sfnarrow-destacada" style="border: 2px solid var(--color-acento); border-radius: var(--radio-lg); background: var(--color-superficie); box-shadow: var(--sombra-3); overflow: hidden; margin: 2rem 0;">
    <!-- Encabezado llamativo con contraste de acento -->
    <div style="background: linear-gradient(135deg, rgba(103, 234, 148, 0.22) 0%, rgba(21, 97, 47, 0.12) 100%); padding: 1.5rem 1.75rem; border-bottom: 1px solid var(--color-borde); display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 1rem;">
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
    <div style="padding: 1.5rem 1.75rem;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
            
            <!-- Región -->
            <div style="padding: 1rem; border-radius: var(--radio-md); background: var(--color-superficie-sutil); border: 1px solid var(--color-borde);">
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
            <div style="padding: 1rem; border-radius: var(--radio-md); background: var(--color-aviso-fondo); border: 1px solid var(--color-aviso-texto);">
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
            <div style="padding: 1rem; border-radius: var(--radio-md); background: var(--color-superficie-sutil); border: 1px solid var(--color-borde);">
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
            <div style="padding: 1rem; border-radius: var(--radio-md); background: var(--color-superficie-sutil); border: 1px solid var(--color-borde);">
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
            <div style="padding: 1rem; border-radius: var(--radio-md); background: var(--color-superficie-sutil); border: 1px solid var(--color-borde);">
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
            <div style="padding: 1rem; border-radius: var(--radio-md); background: var(--color-acento-suave); border: 1px solid var(--color-enlace);">
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
            <div style="padding: 1rem; border-radius: var(--radio-md); background: var(--color-superficie-sutil); border: 1px solid var(--color-borde);">
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
            <div style="padding: 1rem; border-radius: var(--radio-md); background: var(--color-superficie-sutil); border: 1px solid var(--color-borde);">
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
            <div style="padding: 1rem; border-radius: var(--radio-md); background: var(--color-superficie-sutil); border: 1px solid var(--color-borde);">
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
        <div style="padding: 0.9rem 1.15rem; background: var(--color-superficie-sutil); border-left: 4px solid var(--color-enlace); border-radius: var(--radio-sm); font-size: 0.9rem; color: var(--color-texto-2); line-height: 1.5;">
            <strong>💡 {{ __('portal.sfnarrow.remote_advice_title') }}</strong> {{ __('portal.sfnarrow.remote_advice_text') }} 
            <code>{{ __('portal.sfnarrow.remote_advice_order') }}</code>
        </div>
    </div>
</div>
