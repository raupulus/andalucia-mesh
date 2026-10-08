@php
    $currentLang = app()->getLocale();
    $langQuery = $currentLang !== 'es' ? '?lang=' . $currentLang : '';
@endphp

<x-layout :title="config('proyecto.nombre') . ' — ' . __('portal.home.meta_title', ['name' => config('proyecto.nombre')])">
    <div class="contenedor">
        <!-- 1. Presentación institucional -->
        <header class="seccion" style="padding-top: 3rem; padding-bottom: 2rem; display: flex; align-items: center; justify-content: space-between; gap: 2rem; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 280px;">
                <h1 style="font-size: 2.75rem; font-weight: 800; letter-spacing: -0.03em; margin-bottom: 1rem; color: var(--color-texto-1);">
                    {{ config('proyecto.nombre') }}
                </h1>
                <p class="lead" style="max-width: 760px; font-size: 1.25rem; color: var(--color-texto-2); line-height: 1.5; margin-bottom: 0;">
                    {{ __('portal.home.hero_lead') }}
                </p>
            </div>
            <div style="flex-shrink: 0; display: flex; align-items: center; justify-content: center;">
                <img src="{{ asset('img/logo.png') }}" alt="{{ config('proyecto.nombre') }}" style="width: 130px; height: 130px; object-fit: contain; filter: drop-shadow(0 12px 24px rgba(0, 0, 0, 0.15));" width="130" height="130">
            </div>
        </header>

        <!-- 2. Tres tarjetas fijas (en orden estricto) -->
        <section aria-label="{{ __('portal.home.services_aria') }}" style="margin-bottom: 3.5rem;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem;">
                @foreach($tarjetas as $tarjeta)
                    <x-tarjeta-servicio :tarjeta="$tarjeta" />
                @endforeach
            </div>
        </section>

        <!-- 3. Mapa provincial de Andalucía -->
        <section class="seccion" style="padding-top: 1rem; padding-bottom: 3.5rem;" aria-label="Mapa provincial y saturación">
            <header style="margin-bottom: 1.5rem;">
                <h2 style="font-size: 1.85rem; font-weight: 700; margin-bottom: 0.5rem; color: var(--color-texto-1);">
                    {{ __('portal.home.mesh_in_andalusia') }}
                </h2>
                <p style="color: var(--color-texto-2); margin-bottom: 0;">
                    {{ __('portal.home.mesh_subtitle') }}
                </p>
            </header>

            <x-mapa-provincias :datos="$datosMapa" :ventana="$ventana" />
        </section>

        <!-- 4. Resumen: Configura tu nodo -->
        <section class="seccion" style="border-top: 1px solid var(--color-borde); padding-top: 3.5rem; padding-bottom: 3.5rem;">
            <div style="max-width: 820px;">
                <h2 style="font-size: 1.85rem; font-weight: 700; margin-bottom: 1rem; color: var(--color-texto-1);">
                    {{ __('portal.home.node_setup_title') }}
                </h2>
                <p style="color: var(--color-texto-2); font-size: 1.05rem; line-height: 1.6; margin-bottom: 1.5rem;">
                    {{ __('portal.home.node_setup_lead') }}
                </p>

                <div class="tarjeta" style="padding: 1.5rem; margin-bottom: 1.5rem; background: var(--color-superficie-sutil);">
                    <div style="overflow-x: auto;">
                        <table class="tabla" style="width: 100%; border-collapse: collapse; font-size: 0.92rem;">
                            <tbody>
                                <tr style="border-bottom: 1px solid var(--color-borde);">
                                    <td style="padding: 0.5rem 0; font-weight: 600; width: 40%;">{{ __('portal.table_radio.region') }}</td>
                                    <td style="padding: 0.5rem 0;"><code>{{ config('proyecto.lora.region') }}</code></td>
                                </tr>
                                <tr style="border-bottom: 1px solid var(--color-borde);">
                                    <td style="padding: 0.5rem 0; font-weight: 600;">{{ __('portal.table_radio.preset') }}</td>
                                    <td style="padding: 0.5rem 0;"><span class="chip chip-info" style="font-size: 0.8rem; font-weight: 600;">{{ __('portal.table_radio.disabled') }}</span> {{ __('portal.table_radio.disabled_note') }}</td>
                                </tr>
                                <tr style="border-bottom: 1px solid var(--color-borde);">
                                    <td style="padding: 0.5rem 0; font-weight: 600;">{{ __('portal.table_radio.bandwidth') }}</td>
                                    <td style="padding: 0.5rem 0;">BW <strong>{{ config('proyecto.lora.bandwidth') }}</strong> (62.5 kHz) · SF<strong>{{ config('proyecto.lora.spread_factor') }}</strong> · CR4/<strong>{{ config('proyecto.lora.coding_rate') }}</strong></td>
                                </tr>
                                <tr style="border-bottom: 1px solid var(--color-borde);">
                                    <td style="padding: 0.5rem 0; font-weight: 600;">{{ __('portal.table_radio.frequency_slot') }}</td>
                                    <td style="padding: 0.5rem 0;">{{ __('portal.table_radio.slot') }} <strong>{{ config('proyecto.lora.frequency_slot') }}</strong> (o <code>{{ config('proyecto.lora.frequency_mhz') }} MHz</code>)</td>
                                </tr>
                                <tr style="border-bottom: 1px solid var(--color-borde);">
                                    <td style="padding: 0.5rem 0; font-weight: 600;">{{ __('portal.table_radio.primary_channel') }}</td>
                                    <td style="padding: 0.5rem 0;"><code>{{ config('proyecto.canales.primario') }}</code> ({{ __('portal.table_radio.psk_key') }} <code>{{ config('proyecto.canales.clave_defecto') }}</code>)</td>
                                </tr>
                                <tr style="border-bottom: 1px solid var(--color-borde);">
                                    <td style="padding: 0.5rem 0; font-weight: 600;">{{ __('portal.table_radio.hop_limit') }}</td>
                                    <td style="padding: 0.5rem 0;">{!! str_replace(['**3–4**', '`CLIENT_MUTE`'], ['<strong>3–4</strong>', '<code>CLIENT_MUTE</code>'], __('portal.table_radio.hop_limit_desc')) !!}</td>
                                </tr>

                                <tr>
                                    <td style="padding: 0.5rem 0; font-weight: 600;">{{ __('portal.table_radio.suggested_role') }}</td>
                                    <td style="padding: 0.5rem 0;">{!! str_replace(['`CLIENT_MUTE`', '`CLIENT`'], ['<code>CLIENT_MUTE</code>', '<code>CLIENT</code>'], __('portal.table_radio.suggested_role_desc')) !!}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <a href="/configura-tu-nodo{{ $langQuery }}" class="btn btn-secundario">
                    {{ __('portal.home.btn_full_guide') }}
                </a>
            </div>
        </section>

        <!-- 5. Resumen: Sube los datos de tu nodo (Gateways) -->
        <section class="seccion" style="border-top: 1px solid var(--color-borde); padding-top: 3.5rem; padding-bottom: 3.5rem;">
            <div style="max-width: 820px;">
                <h2 style="font-size: 1.85rem; font-weight: 700; margin-bottom: 1rem; color: var(--color-texto-1);">
                    {{ __('portal.home.gateway_title') }}
                </h2>
                <p style="color: var(--color-texto-2); font-size: 1.05rem; line-height: 1.6; margin-bottom: 1.5rem;">
                    {{ __('portal.home.gateway_lead') }}
                </p>

                <div class="tarjeta" style="padding: 1.5rem; margin-bottom: 1.5rem; background: var(--color-superficie-sutil);">
                    <ul style="margin: 0; padding-left: 1.25rem; line-height: 1.7; font-size: 0.95rem;">
                        <li>{{ __('portal.gateway_card.mqtt_public') }} <code>{{ config('proyecto.mqtt.host_publico') }}</code> ({{ __('portal.gateway_card.port_tls') }} <strong>{{ config('proyecto.mqtt.puerto_tls') }}</strong>)</li>
                        <li>{{ __('portal.gateway_card.user') }} <code>{{ config('proyecto.mqtt.gateway_user') }}</code> · {{ __('portal.gateway_card.password') }} <code>{{ config('proyecto.mqtt.gateway_password') }}</code></li>
                        <li>{{ __('portal.gateway_card.topic_root') }} <code>{{ config('proyecto.mqtt.topic_root') }}</code></li>
                        <li>{!! str_replace('**siempre desactivado**', '<strong>' . (app()->getLocale() === 'en' ? 'always disabled' : (app()->getLocale() === 'pt' ? 'sempre desativado' : 'siempre desactivado')) . '</strong>', __('portal.gateway_card.uplink_downlink')) !!}</li>
                    </ul>
                </div>

                <a href="/conecta-tu-gateway{{ $langQuery }}" class="btn btn-secundario">
                    {{ __('portal.home.btn_gateway_guide') }}
                </a>
            </div>
        </section>

        <!-- 6. Resumen: Quién está detrás -->
        <section class="seccion" style="border-top: 1px solid var(--color-borde); padding-top: 3.5rem; padding-bottom: 3.5rem;">
            <div style="max-width: 820px;">
                <h2 style="font-size: 1.85rem; font-weight: 700; margin-bottom: 1rem; color: var(--color-texto-1);">
                    {{ __('portal.home.who_title') }}
                </h2>
                <p style="color: var(--color-texto-2); font-size: 1.05rem; line-height: 1.6; margin-bottom: 1.5rem;">
                    {!! str_replace(
                        [':name', ':nick'],
                        ['<strong><a href="https://raupulus.dev" target="_blank" rel="noopener noreferrer" style="color: var(--color-texto-1); text-decoration: underline;">' . config('autoria.nombre') . '</a></strong>', '<code><a href="https://raupulus.dev" target="_blank" rel="noopener noreferrer">' . config('autoria.nick') . '</a></code>'],
                        __('portal.home.who_lead', ['name' => config('autoria.nombre'), 'nick' => config('autoria.nick')])
                    ) !!}
                </p>

                <a href="/quien-lo-impulsa{{ $langQuery }}" class="btn btn-secundario">
                    {{ __('portal.home.btn_who') }}
                </a>
            </div>
        </section>

        <!-- 7. Buzón de sugerencias comunitario -->
        <section class="seccion" style="border-top: 1px solid var(--color-borde); padding-top: 3rem; padding-bottom: 3rem;">
            <div style="max-width: 820px; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1.5rem; background: var(--color-superficie-sutil); border: 1px solid var(--color-borde); border-radius: var(--radio-lg); padding: 1.75rem 2rem;">
                <div>
                    <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--color-texto-1); margin-bottom: 0.35rem;">
                        {{ __('portal.home.suggestions_title') }}
                    </h3>
                    <p style="color: var(--color-texto-2); font-size: 0.95rem; margin: 0; line-height: 1.5;">
                        {{ __('portal.home.suggestions_lead') }}
                    </p>
                </div>
                <a href="/sugerencias{{ $langQuery }}" class="btn btn-secundario" style="flex-shrink: 0;">
                    {{ __('portal.home.btn_suggestions') }}
                </a>
            </div>
        </section>

        <!-- 8. Aviso legal y descargo de emergencias -->
        <div style="margin-bottom: 3.5rem;">
            <x-aviso-emergencias />
        </div>
    </div>
</x-layout>
