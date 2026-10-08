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
        <section class="seccion" style="padding-top: 1rem; padding-bottom: 2rem;" aria-label="Mapa provincial y saturación">
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

        <!-- Tarjeta Horizontal Comunitaria: ¡Envía tu Sugerencia! -->
        <section class="seccion" style="padding-bottom: 3.5rem;" aria-label="{{ __('portal.home.card_suggestions_title') }}">
            <div class="tarjeta tarjeta-horizontal-destacada" style="display: flex; align-items: center; gap: 2.5rem; padding: 2rem 2.5rem; background: var(--color-superficie); border: 1px solid var(--color-borde); border-radius: var(--radio-lg); box-shadow: var(--sombra-1);">
                <div class="tarjeta-horizontal-img-wrapper" style="flex-shrink: 0; width: 170px; height: 170px; border-radius: var(--radio-md); overflow: hidden; display: flex; align-items: center; justify-content: center; background: #1B1C28;">
                    <img src="{{ asset('img/sugerencias-banner.webp') }}" alt="{{ __('portal.home.card_suggestions_title') }}" style="width: 100%; height: 100%; object-fit: cover;" width="170" height="170" loading="lazy">
                </div>
                <div class="tarjeta-horizontal-cuerpo" style="flex: 1; display: flex; flex-direction: column;">
                    <h3 style="font-size: 1.75rem; font-weight: 800; margin-bottom: 0.65rem; color: var(--color-texto-1); letter-spacing: -0.02em;">
                        {{ __('portal.home.card_suggestions_title') }}
                    </h3>
                    <p style="color: var(--color-texto-2); font-size: 1.05rem; line-height: 1.6; margin-bottom: 1.5rem;">
                        {{ __('portal.home.card_suggestions_desc') }}
                    </p>
                    <div style="display: flex; justify-content: center;">
                        <a href="/sugerencias{{ $langQuery }}" class="btn btn-primario" style="padding: 0.75rem 2rem; font-weight: 700; font-size: 1rem; letter-spacing: 0.01em;">
                            {{ __('portal.home.card_suggestions_btn') }}
                        </a>
                    </div>
                </div>
            </div>
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

        <!-- Tarjeta Horizontal Diagnóstico: Revisa tu nodo -->
        <section class="seccion" style="padding-bottom: 3.5rem;" aria-label="{{ __('portal.home.card_node_check_title') }}">
            <div class="tarjeta tarjeta-horizontal-destacada" style="display: flex; align-items: center; gap: 2.5rem; padding: 2rem 2.5rem; background: var(--color-superficie); border: 1px solid var(--color-borde); border-radius: var(--radio-lg); box-shadow: var(--sombra-1);">
                <div class="tarjeta-horizontal-img-wrapper" style="flex-shrink: 0; width: 170px; height: 170px; border-radius: var(--radio-md); overflow: hidden; display: flex; align-items: center; justify-content: center; background: #1B1C28;">
                    <img src="{{ asset('img/revisa-nodo-banner.webp') }}" alt="{{ __('portal.home.card_node_check_title') }}" style="width: 100%; height: 100%; object-fit: cover;" width="170" height="170" loading="lazy">
                </div>
                <div class="tarjeta-horizontal-cuerpo" style="flex: 1; display: flex; flex-direction: column;">
                    <h3 style="font-size: 1.75rem; font-weight: 800; margin-bottom: 0.65rem; color: var(--color-texto-1); letter-spacing: -0.02em;">
                        {{ __('portal.home.card_node_check_title') }}
                    </h3>
                    <p style="color: var(--color-texto-2); font-size: 1.05rem; line-height: 1.6; margin-bottom: 1.5rem;">
                        {{ __('portal.home.card_node_check_desc') }}
                    </p>
                    <div style="display: flex; justify-content: center;">
                        <a href="/revisa-tu-nodo{{ $langQuery }}" class="btn btn-primario" style="padding: 0.75rem 2rem; font-weight: 700; font-size: 1rem; letter-spacing: 0.01em;">
                            {{ __('portal.home.card_node_check_btn') }}
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- 6. Resumen: Quién está detrás (Tarjeta Vertical Centrada y Oxigenada) -->
        <section class="seccion" style="border-top: 1px solid var(--color-borde); padding-top: 4rem; padding-bottom: 4rem;">
            <div class="tarjeta tarjeta-quien-detras" style="max-width: 680px; margin: 0 auto; padding: 3rem 2.5rem; text-align: center; display: flex; flex-direction: column; align-items: center; background: var(--color-superficie); border: 1px solid var(--color-borde); border-radius: var(--radio-lg); box-shadow: var(--sombra-1);">
                <h2 style="font-size: 2rem; font-weight: 800; margin-bottom: 1.5rem; color: var(--color-texto-1); letter-spacing: -0.02em;">
                    {{ __('portal.home.who_title') }}
                </h2>

                <!-- Logotipo oficial de raupulus.dev enlazando a https://raupulus.dev -->
                <a href="https://raupulus.dev" target="_blank" rel="noopener noreferrer" style="display: inline-block; margin-bottom: 1.75rem; text-decoration: none; transition: transform 0.2s ease;" aria-label="raupulus.dev — Raúl Caro Pastorino">
                    <img src="{{ asset('img/raupulus-logo.webp') }}" alt="raupulus.dev" style="width: 88px; height: 88px; border-radius: 50%; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2); border: 2px solid var(--color-borde-control);" width="88" height="88">
                </a>

                <p style="color: var(--color-texto-2); font-size: 1.1rem; line-height: 1.7; max-width: 560px; margin-bottom: 2rem;">
                    {!! str_replace(
                        [':name', ':nick'],
                        ['<strong><a href="https://raupulus.dev" target="_blank" rel="noopener noreferrer" style="color: var(--color-texto-1); text-decoration: underline;">' . config('autoria.nombre') . '</a></strong>', '<code><a href="https://raupulus.dev" target="_blank" rel="noopener noreferrer">' . config('autoria.nick') . '</a></code>'],
                        __('portal.home.who_lead', ['name' => config('autoria.nombre'), 'nick' => config('autoria.nick')])
                    ) !!}
                </p>

                <div style="display: flex; gap: 1rem; flex-wrap: wrap; justify-content: center; align-items: center;">
                    <a href="/quien-lo-impulsa{{ $langQuery }}" class="btn btn-secundario" style="font-weight: 600;">
                        {{ __('portal.home.btn_who') }}
                    </a>
                    <a href="https://raupulus.dev" target="_blank" rel="noopener noreferrer" class="btn" style="border: 1px solid var(--color-borde-control); background: var(--color-superficie-sutil); color: var(--color-texto); font-weight: 500; display: inline-flex; align-items: center; gap: 0.5rem;">
                        <span>raupulus.dev</span>
                        <svg aria-hidden="true" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                            <polyline points="15 3 21 3 21 9"></polyline>
                            <line x1="10" y1="14" x2="21" y2="3"></line>
                        </svg>
                    </a>
                </div>
            </div>
        </section>

        <!-- 8. Aviso legal y descargo de emergencias -->
        <div style="margin-bottom: 3.5rem;">
            <x-aviso-emergencias />
        </div>
    </div>
</x-layout>
