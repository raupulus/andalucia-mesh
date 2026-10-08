@php
    $currentLang = app()->getLocale();
    $langQuery = $currentLang !== 'es' ? '?lang=' . $currentLang : '';
@endphp

<x-layout :title="config('proyecto.nombre') . ' — ' . __('portal.home.meta_title', ['name' => config('proyecto.nombre')])" :description="__('portal.home.hero_lead')">
    <div class="contenedor">
        <!-- 1. Presentación institucional -->
        <header class="seccion portada-hero">
            <div class="portada-hero-texto">
                <h1 class="portada-hero-titulo">
                    {{ config('proyecto.nombre') }}
                </h1>
                <p class="lead portada-hero-lead">
                    {{ __('portal.home.hero_lead') }}
                </p>
            </div>
            <div class="portada-hero-logo">
                <img src="{{ asset('img/logo.png') }}" alt="{{ config('proyecto.nombre') }}" width="130" height="130">
            </div>
        </header>

        <!-- 2. Tres tarjetas fijas (en orden estricto) -->
        <section aria-label="{{ __('portal.home.services_aria') }}" style="margin-bottom: 3.5rem;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 280px), 1fr)); gap: 1.5rem;">
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

                <div style="display: flex; flex-wrap: wrap; gap: 1rem; align-items: center;">
                    <a href="/configura-tu-nodo{{ $langQuery }}" class="btn btn-secundario">
                        {{ __('portal.home.btn_full_guide') }}
                    </a>
                    <a href="/configurador{{ $langQuery }}" class="btn btn-primario" style="background: var(--color-primario); color: #FFFFFF; display: inline-flex; align-items: center; gap: 0.5rem; text-decoration: none; font-weight: 600; box-shadow: 0 2px 8px rgba(0, 122, 51, 0.25);">
                        <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                            <line x1="8" y1="21" x2="16" y2="21"></line>
                            <line x1="12" y1="17" x2="12" y2="21"></line>
                        </svg>
                        <span>{{ __('portal.home.btn_auto_configurator') }}</span>
                    </a>
                </div>
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

        <!-- 5.5. Sección: Últimas Páginas y Divulgación (2 por fila, máx 4) -->
        @if(isset($ultimasPaginas) && $ultimasPaginas->isNotEmpty())
            <section class="seccion" style="padding-bottom: 3.5rem;" aria-label="{{ __('portal.pages.home_title') }}">
                <div style="margin-bottom: 2rem; text-align: center;">
                    <h2 style="font-size: 2rem; font-weight: 800; margin-bottom: 0.5rem; color: var(--color-texto-1); letter-spacing: -0.02em;">
                        {{ __('portal.pages.home_title') }}
                    </h2>
                    <p style="color: var(--color-texto-2); font-size: 1.1rem; max-width: 620px; margin: 0 auto;">
                        {{ __('portal.pages.home_subtitle') }}
                    </p>
                </div>

                <div class="grid-paginas-portada">
                    @foreach($ultimasPaginas as $pag)
                        <article class="tarjeta-pagina-compacta">
                            <!-- Imagen a la izquierda -->
                            <div class="tarjeta-pagina-img-wrapper">
                                <a href="{{ route('paginas.show', ['slug' => $pag->slug]) }}{{ $langQuery }}" class="tarjeta-pagina-img-link" tabindex="-1" aria-hidden="true">
                                    <img src="{{ $pag->cover_image_url }}" alt="" loading="lazy" width="200" height="170">
                                </a>
                            </div>

                            <!-- Franja Andalucía (verde/blanca/verde) -->
                            <div class="franja-andalucia-vertical" aria-hidden="true">
                                <span class="franja-andalucia-verde"></span>
                                <span class="franja-andalucia-blanca"></span>
                                <span class="franja-andalucia-verde"></span>
                            </div>

                            <!-- Cuerpo de la tarjeta compacta -->
                            <div class="tarjeta-pagina-cuerpo">
                                <div class="tarjeta-pagina-cabecera">
                                    <h3 class="tarjeta-pagina-titulo">
                                        <a href="{{ route('paginas.show', ['slug' => $pag->slug]) }}{{ $langQuery }}">
                                            {{ $pag->title }}
                                        </a>
                                    </h3>
                                    <time datetime="{{ $pag->created_at->toIso8601String() }}" class="tarjeta-pagina-fecha">
                                        {{ $pag->formatted_date }}
                                    </time>
                                    <p class="tarjeta-pagina-desc">
                                        {{ $pag->description }}
                                    </p>
                                </div>

                                <div class="tarjeta-pagina-footer">
                                    <a href="{{ route('paginas.show', ['slug' => $pag->slug]) }}{{ $langQuery }}" class="tarjeta-pagina-accion">
                                        <span>{{ __('portal.pages.read_article') }}</span>
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <line x1="5" y1="12" x2="19" y2="12"></line>
                                            <polyline points="12 5 19 12 12 19"></polyline>
                                        </svg>
                                    </a>

                                    <div class="tarjeta-pagina-keywords" aria-label="{{ __('portal.pages.keywords_label') }}">
                                        @if(!empty($pag->keywords))
                                            @foreach(array_slice($pag->keywords, 0, 3) as $kw)
                                                <span class="badge-keyword">{{ $kw }}</span>
                                            @endforeach
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div style="display: flex; justify-content: center; margin-top: 2rem;">
                    <a href="{{ route('paginas.index') }}{{ $langQuery }}" class="btn-verde">
                        <span>{{ __('portal.pages.btn_view_all') }}</span>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                            <polyline points="12 5 19 12 12 19"></polyline>
                        </svg>
                    </a>
                </div>
            </section>
        @endif

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
