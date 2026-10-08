{{-- ==============================================================================
     tarjeta-gateway-mqtt.blade.php
     Tarjeta visual destacada con los parámetros de conexión pública MQTT para pasarelas.
     Credenciales públicas compartidas (meshdev / large4cats) con TLS obligatorio.
     ============================================================================== --}}

<div class="tarjeta-mqtt-destacada" style="border: 2px solid var(--color-enlace); border-radius: var(--radio-lg); background: var(--color-superficie); box-shadow: var(--sombra-3); overflow: hidden; margin: 2rem 0;">
    <!-- Encabezado llamativo -->
    <div style="background: linear-gradient(135deg, rgba(21, 97, 47, 0.15) 0%, rgba(103, 234, 148, 0.15) 100%); padding: 1.5rem 1.75rem; border-bottom: 1px solid var(--color-borde); display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 1rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 0.6rem; margin-bottom: 0.35rem;">
                <span style="display: inline-block; width: 12px; height: 12px; border-radius: 50%; background: var(--color-enlace); box-shadow: 0 0 10px var(--color-enlace);"></span>
                <span style="font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: var(--color-enlace);">
                    {{ __('portal.gateway_mqtt_details.badge') }}
                </span>
            </div>
            <h2 style="font-size: 1.75rem; font-weight: 800; margin: 0; color: var(--color-texto); line-height: 1.2;">
                {{ __('portal.gateway_mqtt_details.title') }}
            </h2>
            <p style="font-size: 0.95rem; color: var(--color-texto-2); margin: 0.35rem 0 0 0;">
                {{ __('portal.gateway_mqtt_details.subtitle') }}
            </p>
        </div>
        <div class="tarjeta-destacada-badge-wrapper" style="display: flex; flex-direction: column; align-items: flex-end; gap: 0.4rem;">
            <span class="chip chip-correcto" style="font-size: 0.85rem; font-weight: 700; padding: 0.35rem 0.75rem;">
                {{ __('portal.gateway_mqtt_details.badge_tls') }}
            </span>
            <span style="font-size: 0.8rem; color: var(--color-texto-3);">
                {{ __('portal.gateway_mqtt_details.benefit') }}
            </span>
        </div>
    </div>

    <!-- Parámetros clave en cuadrícula -->
    <div style="padding: 1.5rem 1.75rem;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">

            <!-- Dirección del Servidor -->
            <div style="padding: 1rem; border-radius: var(--radio-md); background: var(--color-superficie-sutil); border: 1px solid var(--color-borde);">
                <div style="font-size: 0.8rem; font-weight: 600; text-transform: uppercase; color: var(--color-texto-3); margin-bottom: 0.25rem;">
                    {{ __('portal.gateway_mqtt_details.server_title') }}
                </div>
                <div style="font-size: 1.15rem; font-weight: 800; color: var(--color-texto); font-family: var(--fuente-mono); word-break: break-all;">
                    {{ config('proyecto.mqtt.host_publico') }}
                </div>
                <div style="font-size: 0.8rem; color: var(--color-texto-2); margin-top: 0.25rem;">
                    {{ __('portal.gateway_mqtt_details.server_val', ['port' => config('proyecto.mqtt.puerto_tls')]) }}
                </div>
            </div>

            <!-- Usuario -->
            <div style="padding: 1rem; border-radius: var(--radio-md); background: var(--color-superficie-sutil); border: 1px solid var(--color-borde);">
                <div style="font-size: 0.8rem; font-weight: 600; text-transform: uppercase; color: var(--color-texto-3); margin-bottom: 0.25rem;">
                    {{ __('portal.gateway_mqtt_details.user_title') }}
                </div>
                <div style="font-size: 1.25rem; font-weight: 800; color: var(--color-enlace); font-family: var(--fuente-mono);">
                    {{ config('proyecto.mqtt.gateway_user') }}
                </div>
                <div style="font-size: 0.8rem; color: var(--color-texto-2); margin-top: 0.25rem;">
                    {{ __('portal.gateway_mqtt_details.user_desc') }}
                </div>
            </div>

            <!-- Contraseña -->
            <div style="padding: 1rem; border-radius: var(--radio-md); background: var(--color-superficie-sutil); border: 1px solid var(--color-borde);">
                <div style="font-size: 0.8rem; font-weight: 600; text-transform: uppercase; color: var(--color-texto-3); margin-bottom: 0.25rem;">
                    {{ __('portal.gateway_mqtt_details.password_title') }}
                </div>
                <div style="font-size: 1.25rem; font-weight: 800; color: var(--color-enlace); font-family: var(--fuente-mono);">
                    {{ config('proyecto.mqtt.gateway_password') }}
                </div>
                <div style="font-size: 0.8rem; color: var(--color-texto-2); margin-top: 0.25rem;">
                    {{ __('portal.gateway_mqtt_details.password_desc') }}
                </div>
            </div>

            <!-- Root Topic -->
            <div style="padding: 1rem; border-radius: var(--radio-md); background: var(--color-superficie-sutil); border: 1px solid var(--color-borde);">
                <div style="font-size: 0.8rem; font-weight: 600; text-transform: uppercase; color: var(--color-texto-3); margin-bottom: 0.25rem;">
                    {{ __('portal.gateway_mqtt_details.topic_title') }}
                </div>
                <div style="font-size: 1.25rem; font-weight: 800; color: var(--color-texto); font-family: var(--fuente-mono);">
                    {{ config('proyecto.mqtt.topic_root') }}
                </div>
                <div style="font-size: 0.8rem; color: var(--color-texto-2); margin-top: 0.25rem;">
                    {{ __('portal.gateway_mqtt_details.topic_desc') }}
                </div>
            </div>

            <!-- Uplink / Downlink -->
            <div style="padding: 1rem; border-radius: var(--radio-md); background: var(--color-superficie-sutil); border: 1px solid var(--color-borde);">
                <div style="font-size: 0.8rem; font-weight: 600; text-transform: uppercase; color: var(--color-texto-3); margin-bottom: 0.25rem;">
                    {{ __('portal.gateway_mqtt_details.uplink_downlink_title') }}
                </div>
                <div style="font-size: 1.1rem; font-weight: 800; color: var(--color-texto);">
                    <span style="color: var(--color-enlace);">{{ __('portal.gateway_mqtt_details.uplink_yes') }}</span> · <span style="color: var(--color-critico-texto);">{{ __('portal.gateway_mqtt_details.downlink_no') }}</span>
                </div>
                <div style="font-size: 0.8rem; color: var(--color-texto-2); margin-top: 0.25rem;">
                    {{ __('portal.gateway_mqtt_details.downlink_desc') }}
                </div>
            </div>

            <!-- TLS y Cifrado -->
            <div style="padding: 1rem; border-radius: var(--radio-md); background: var(--color-superficie-sutil); border: 1px solid var(--color-borde);">
                <div style="font-size: 0.8rem; font-weight: 600; text-transform: uppercase; color: var(--color-texto-3); margin-bottom: 0.25rem;">
                    {{ __('portal.gateway_mqtt_details.security_title') }}
                </div>
                <div style="font-size: 1.15rem; font-weight: 800; color: var(--color-texto);">
                    {{ __('portal.gateway_mqtt_details.security_val') }}
                </div>
                <div style="font-size: 0.8rem; color: var(--color-texto-2); margin-top: 0.25rem;">
                    {{ __('portal.gateway_mqtt_details.security_desc') }}
                </div>
            </div>

        </div>

        <!-- Advertencia de Downlink para salvaguardar la malla -->
        <div style="padding: 0.9rem 1.15rem; background: var(--color-critico-fondo); border-left: 4px solid var(--color-critico-texto); border-radius: var(--radio-sm); font-size: 0.9rem; color: var(--color-critico-texto); line-height: 1.5;">
            <strong>🛡️ {{ __('portal.gateway_mqtt_details.radio_protection_title') }}</strong> {!! __('portal.gateway_mqtt_details.radio_protection_text') !!}
        </div>
    </div>
</div>
