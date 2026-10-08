@php
    $currentLang = app()->getLocale();
    $langQuery = $currentLang !== 'es' ? '?lang=' . $currentLang : '';
@endphp

<x-layout :title="$titulo" :description="$descripcion">
    <div class="contenedor seccion">
        <article style="max-width: 960px; margin: 0 auto;">
            <!-- Encabezado de la página -->
            <header style="margin-bottom: 3rem; border-bottom: 1px solid var(--color-borde); padding-bottom: 2rem;">
                <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem;">
                    <span class="chip chip-correcto" style="font-weight: 700;">{{ __('portal.bots.badge') }}</span>
                    <span style="font-size: 0.85rem; color: var(--color-texto-3);">{{ __('portal.bots.subtitle') }}</span>
                </div>
                <h1 style="margin-bottom: 1rem; font-size: clamp(2.2rem, 4vw, 2.85rem);">{{ $h1 }}</h1>
                <p class="lead" style="margin-bottom: 0; line-height: 1.6; max-width: 840px;">{{ $descripcion }}</p>
            </header>

            <!-- 1. Qué avisan: Riesgos y Tipos -->
            <section style="margin-bottom: 3.5rem;">
                <h2 style="font-size: 1.75rem; font-weight: 800; margin-bottom: 0.75rem; color: var(--color-texto);">
                    {{ __('portal.bots.heading') }}
                </h2>
                <p style="color: var(--color-texto-2); font-size: 1rem; line-height: 1.6; margin-bottom: 1.75rem;">
                    {{ __('portal.bots.lead') }}
                </p>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 380px), 1fr)); gap: 1.5rem; margin-bottom: 1.75rem;">
                    <!-- Clasificación por Riesgo -->
                    <div class="tarjeta" style="padding: 1.5rem; background: var(--color-superficie);">
                        <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 1rem; color: var(--color-texto);">
                            {{ __('portal.bots.risk_levels') }}
                        </h3>
                        <div style="display: flex; flex-direction: column; gap: 0.85rem;">
                            <div style="display: flex; align-items: flex-start; gap: 0.75rem; padding-bottom: 0.85rem; border-bottom: 1px solid var(--color-borde);">
                                <x-chip-estado nivel="critico" :texto="__('portal.bots.risk_high')" />
                                <div style="font-size: 0.9rem; color: var(--color-texto-2); line-height: 1.45;">
                                    <strong>{{ __('portal.bots.risk_active_fail') }}</strong> {{ __('portal.bots.risk_active_fail_desc') }}
                                </div>
                            </div>
                            <div style="display: flex; align-items: flex-start; gap: 0.75rem; padding-bottom: 0.85rem; border-bottom: 1px solid var(--color-borde);">
                                <x-chip-estado nivel="aviso" :texto="__('portal.bots.risk_medium')" />
                                <div style="font-size: 0.9rem; color: var(--color-texto-2); line-height: 1.45;">
                                    <strong>{{ __('portal.bots.risk_real_risk') }}</strong> {{ __('portal.bots.risk_real_risk_desc') }}
                                </div>
                            </div>
                            <div style="display: flex; align-items: flex-start; gap: 0.75rem;">
                                <x-chip-estado nivel="info" :texto="__('portal.bots.risk_low')" />
                                <div style="font-size: 0.9rem; color: var(--color-texto-2); line-height: 1.45;">
                                    <strong>{{ __('portal.bots.risk_preventive') }}</strong> {{ __('portal.bots.risk_preventive_desc') }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Clasificación por Tipo -->
                    <div class="tarjeta" style="padding: 1.5rem; background: var(--color-superficie);">
                        <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 1rem; color: var(--color-texto);">
                            {{ __('portal.bots.incident_types') }}
                        </h3>
                        <div style="display: flex; flex-direction: column; gap: 1rem;">
                            <div style="padding-bottom: 1rem; border-bottom: 1px solid var(--color-borde);">
                                <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.35rem;">
                                    <span style="font-weight: 700; font-size: 0.95rem; color: var(--color-enlace);">{{ __('portal.bots.type_infra') }}</span>
                                </div>
                                <p style="font-size: 0.9rem; color: var(--color-texto-2); margin: 0; line-height: 1.45;">
                                    {{ __('portal.bots.type_infra_desc_long') }}
                                </p>
                            </div>
                            <div>
                                <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.35rem;">
                                    <span style="font-weight: 700; font-size: 0.95rem; color: var(--color-texto);">{{ __('portal.bots.type_clients') }}</span>
                                </div>
                                <p style="font-size: 0.9rem; color: var(--color-texto-2); margin: 0; line-height: 1.45;">
                                    {{ __('portal.bots.type_client_desc_long') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div style="padding: 1rem 1.25rem; background: var(--color-superficie-sutil); border-radius: var(--radio-md); border-left: 4px solid var(--color-enlace); font-size: 0.95rem; color: var(--color-texto-2);">
                    {{ __('portal.bots.live_alerts_notice') }} <a href="/alertas{{ $langQuery }}" style="font-weight: 600;">{{ __('portal.bots.live_alerts_link') }}</a>
                </div>
            </section>

            <!-- 2. Tarjetas Visuales de Bots: Telegram & Discord -->
            <section style="margin-bottom: 4rem;">
                <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: flex-end; gap: 1rem; margin-bottom: 1.5rem;">
                    <div>
                        <h2 style="font-size: 1.75rem; font-weight: 800; margin-bottom: 0.35rem; color: var(--color-texto);">
                            {{ __('portal.bots.choose_platform') }}
                        </h2>
                        <p style="color: var(--color-texto-2); font-size: 0.95rem; margin: 0;">
                            {{ __('portal.bots.choose_platform_lead') }}
                        </p>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 360px), 1fr)); gap: 2rem;">
                    
                    <!-- Tarjeta Visual: Bot de Telegram -->
                    <div class="tarjeta" style="border: 2px solid var(--color-enlace); border-radius: var(--radio-lg); padding: 2rem; background: var(--color-superficie); box-shadow: var(--sombra-2); display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                                <span style="font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--color-enlace);">
                                    {{ __('portal.bots.groups_and_channels') }}
                                </span>
                                <x-chip-estado nivel="correcto" :texto="__('portal.bots.status_active')" />
                            </div>

                            <h3 style="font-size: 1.65rem; font-weight: 800; margin-bottom: 0.6rem; color: var(--color-texto);">
                                {{ __('portal.bots.telegram_card_title') }}
                            </h3>

                            <p style="color: var(--color-texto-2); font-size: 0.95rem; line-height: 1.55; margin-bottom: 1.5rem;">
                                {{ __('portal.bots.telegram_desc') }}
                            </p>

                            <a href="https://t.me/{{ config('proyecto.bots.telegram_username') }}" 
                               target="_blank" 
                               rel="noopener noreferrer" 
                               class="btn btn-secundario" 
                               style="width: 100%; justify-content: center; margin-bottom: 2rem; font-weight: 700; padding: 0.75rem 1rem;">
                                {{ __('portal.bots.open_telegram', ['username' => config('proyecto.bots.telegram_username')]) }}
                            </a>

                            <!-- Pasos oxigenados -->
                            <div style="display: flex; flex-direction: column; gap: 1rem;">
                                <!-- En un grupo -->
                                <div style="background: var(--color-superficie-sutil); border: 1px solid var(--color-borde); border-radius: var(--radio-md); padding: 1.25rem;">
                                    <h4 style="font-size: 1rem; font-weight: 700; margin-bottom: 0.6rem; color: var(--color-texto);">
                                        {{ __('portal.bots.in_a_group') }}
                                    </h4>
                                    <ol style="margin: 0; padding-left: 1.25rem; font-size: 0.9rem; color: var(--color-texto-2); line-height: 1.6;">
                                        <li style="margin-bottom: 0.4rem;">{!! __('portal.bots.group_step_1', ['username' => config('proyecto.bots.telegram_username')]) !!}</li>
                                        <li style="margin-bottom: 0.4rem;">{{ __('portal.bots.group_step_2') }}</li>
                                        <li>{!! __('portal.bots.group_step_3') !!}</li>
                                    </ol>
                                </div>

                                <!-- En un canal -->
                                <div style="background: var(--color-superficie-sutil); border: 1px solid var(--color-borde); border-radius: var(--radio-md); padding: 1.25rem;">
                                    <h4 style="font-size: 1rem; font-weight: 700; margin-bottom: 0.6rem; color: var(--color-texto);">
                                        {{ __('portal.bots.in_a_channel') }}
                                    </h4>
                                    <ol style="margin: 0; padding-left: 1.25rem; font-size: 0.9rem; color: var(--color-texto-2); line-height: 1.6;">
                                        <li style="margin-bottom: 0.4rem;">{!! __('portal.bots.channel_step_1') !!}</li>
                                        <li>{!! __('portal.bots.channel_step_2') !!}</li>
                                    </ol>
                                </div>

                                <!-- En privado -->
                                <div style="background: var(--color-superficie-sutil); border: 1px solid var(--color-borde); border-radius: var(--radio-md); padding: 1.25rem;">
                                    <h4 style="font-size: 1rem; font-weight: 700; margin-bottom: 0.4rem; color: var(--color-texto);">
                                        {{ __('portal.bots.in_private') }}
                                    </h4>
                                    <p style="margin: 0; font-size: 0.9rem; color: var(--color-texto-2); line-height: 1.55;">
                                        {!! __('portal.bots.private_desc') !!}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div style="font-size: 0.85rem; color: var(--color-texto-3); padding-top: 1.5rem; margin-top: 1rem; border-top: 1px solid var(--color-borde);">
                            <strong>{{ __('portal.bots.to_remove') }}</strong> {{ __('portal.bots.telegram_remove_desc') }}
                        </div>
                    </div>

                    <!-- Tarjeta Visual: Bot de Discord -->
                    <div class="tarjeta" style="border: 2px dashed var(--color-borde-control); border-radius: var(--radio-lg); padding: 2rem; background: var(--color-superficie); box-shadow: var(--sombra-1); display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                                <span style="font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--color-texto-3);">
                                    {{ __('portal.bots.community_servers') }}
                                </span>
                                <x-chip-estado nivel="aviso" :texto="__('portal.bots.status_coming_soon')" />
                            </div>

                            <h3 style="font-size: 1.65rem; font-weight: 800; margin-bottom: 0.6rem; color: var(--color-texto);">
                                {{ __('portal.bots.discord_card_title') }}
                            </h3>

                            <p style="color: var(--color-texto-2); font-size: 0.95rem; line-height: 1.55; margin-bottom: 1.5rem;">
                                {{ __('portal.bots.discord_desc') }}
                            </p>

                            <button type="button" 
                                    class="btn btn-secundario" 
                                    disabled 
                                    style="width: 100%; justify-content: center; margin-bottom: 2rem; font-weight: 600; opacity: 0.65; cursor: not-allowed; background: var(--color-superficie-sutil); padding: 0.75rem 1rem;"
                                    title="Integración en proceso de homologación">
                                {{ __('portal.bots.invite_discord') }}
                            </button>

                            <!-- Aviso de Próximamente -->
                            <div style="background: var(--color-aviso-fondo); border-left: 3px solid var(--color-aviso-texto); border-radius: var(--radio-sm); padding: 0.85rem 1.15rem; margin-bottom: 1rem;">
                                <p style="margin: 0; font-size: 0.88rem; color: var(--color-aviso-texto); line-height: 1.5;">
                                    <strong>{{ __('portal.bots.discord_in_dev_title') }}</strong> {{ __('portal.bots.discord_in_dev_desc') }}
                                </p>
                            </div>

                            <!-- Pasos oxigenados de Discord -->
                            <div style="display: flex; flex-direction: column; gap: 1rem;">
                                <div style="background: var(--color-superficie-sutil); border: 1px solid var(--color-borde); border-radius: var(--radio-md); padding: 1.25rem;">
                                    <h4 style="font-size: 1rem; font-weight: 700; margin-bottom: 0.6rem; color: var(--color-texto);">
                                        {{ __('portal.bots.discord_config_title') }}
                                    </h4>
                                    <ol style="margin: 0; padding-left: 1.25rem; font-size: 0.9rem; color: var(--color-texto-2); line-height: 1.6;">
                                        <li style="margin-bottom: 0.4rem;">{{ __('portal.bots.discord_step_1') }}</li>
                                        <li style="margin-bottom: 0.4rem;">{!! __('portal.bots.discord_step_2') !!}</li>
                                        <li>{!! __('portal.bots.discord_step_3') !!}</li>
                                    </ol>
                                </div>

                                <div style="background: var(--color-superficie-sutil); border: 1px solid var(--color-borde); border-radius: var(--radio-md); padding: 1.25rem;">
                                    <h4 style="font-size: 1rem; font-weight: 700; margin-bottom: 0.4rem; color: var(--color-texto);">
                                        {{ __('portal.bots.multiple_channels_title') }}
                                    </h4>
                                    <p style="margin: 0; font-size: 0.9rem; color: var(--color-texto-2); line-height: 1.55;">
                                        {{ __('portal.bots.multiple_channels_desc') }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div style="font-size: 0.85rem; color: var(--color-texto-3); padding-top: 1.5rem; margin-top: 1rem; border-top: 1px solid var(--color-borde);">
                            <strong>{{ __('portal.bots.to_remove') }}</strong> {!! __('portal.bots.discord_remove_desc') !!}
                        </div>
                    </div>

                </div>
            </section>

            <!-- 3. Comandos Disponibles -->
            <section style="margin-bottom: 4rem;">
                <h2 style="font-size: 1.75rem; font-weight: 800; margin-bottom: 0.5rem; color: var(--color-texto);">
                    {{ __('portal.bots.commands_title') }}
                </h2>
                <p style="color: var(--color-texto-2); font-size: 1rem; margin-bottom: 1.5rem;">
                    {{ __('portal.bots.commands_lead') }}
                </p>

                <div class="tarjeta" style="overflow-x: auto; padding: 0.5rem; background: var(--color-superficie);">
                    <table class="tabla-accesible" style="margin-top: 0;">
                        <thead>
                            <tr>
                                <th style="width: 28%;">{{ __('portal.bots.th_command') }}</th>
                                <th style="width: 44%;">{{ __('portal.bots.th_action') }}</th>
                                <th style="width: 28%;">{{ __('portal.bots.th_permission') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>/status</code></td>
                                <td>{{ __('portal.bots.cmd_status_desc') }}</td>
                                <td>{{ __('portal.bots.perm_anyone') }}</td>
                            </tr>
                            <tr>
                                <td><code>/battery</code></td>
                                <td>{{ __('portal.bots.cmd_battery_desc') }}</td>
                                <td>{{ __('portal.bots.perm_anyone') }}</td>
                            </tr>
                            <tr>
                                <td><code>/routers</code></td>
                                <td>{!! __('portal.bots.cmd_routers_desc') !!}</td>
                                <td>{{ __('portal.bots.perm_anyone') }}</td>
                            </tr>
                            <tr>
                                <td><code>/levels [riesgos…]</code></td>
                                <td>{!! __('portal.bots.cmd_levels_desc') !!}</td>
                                <td>{{ __('portal.bots.perm_levels') }}</td>
                            </tr>
                            <tr>
                                <td><code>/types [tipos…]</code></td>
                                <td>{!! __('portal.bots.cmd_types_desc') !!}</td>
                                <td>{{ __('portal.bots.perm_levels') }}</td>
                            </tr>
                            <tr>
                                <td><code>/settings</code></td>
                                <td>{{ __('portal.bots.cmd_settings_desc') }}</td>
                                <td>{{ __('portal.bots.perm_anyone') }}</td>
                            </tr>
                            <tr>
                                <td><code>/help</code></td>
                                <td>{{ __('portal.bots.cmd_help_desc') }}</td>
                                <td>{{ __('portal.bots.perm_anyone') }}</td>
                            </tr>
                            <tr>
                                <td><code>/subscribe</code> <span style="font-size: 0.78rem; color: var(--color-texto-3);">(Discord)</span></td>
                                <td>{{ __('portal.bots.cmd_subscribe_desc') }}</td>
                                <td>{{ __('portal.bots.perm_managers') }}</td>
                            </tr>
                            <tr>
                                <td><code>/unsubscribe</code> <span style="font-size: 0.78rem; color: var(--color-texto-3);">(Discord)</span></td>
                                <td>{{ __('portal.bots.cmd_unsubscribe_desc') }}</td>
                                <td>{{ __('portal.bots.perm_managers') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- 4. Ejemplos de Respuesta en Bloques de Código -->
            <section style="margin-bottom: 4rem;">
                <div style="margin-bottom: 1.75rem;">
                    <h2 style="font-size: 1.75rem; font-weight: 800; margin-bottom: 0.5rem; color: var(--color-texto);">
                        {{ __('portal.bots.commands_examples') }}
                    </h2>
                    <p style="color: var(--color-texto-2); font-size: 1rem; margin: 0;">
                        {{ __('portal.bots.examples_lead') }}
                    </p>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 420px), 1fr)); gap: 1.5rem;">
                    
                    <!-- Ejemplo: /status -->
                    <div class="tarjeta-codigo">
                        <div class="tarjeta-codigo-cabecera">
                            <span style="font-family: var(--font-mono); color: var(--color-texto);">/status</span>
                            <span>{{ __('portal.bots.ex_status_header') }}</span>
                        </div>
                        <pre class="bloque-codigo" style="margin: 0; border: none; border-radius: 0;"><code>Estado de la malla · 14:35:12
Nodos activos (24 h): 142
Routers activos (24 h): 18
Gateways publicando: 9
Saturación estimada del canal en Andalucía: 14.2 %
Cádiz 18.5 % · Sevilla 12.1 % · Málaga 15.3 % · Córdoba 9.8 %
Alertas abiertas: 0 alto · 1 medio · 3 bajo
Infraestructura: 1 · Clientes: 3</code></pre>
                    </div>

                    <!-- Ejemplo: /battery -->
                    <div class="tarjeta-codigo">
                        <div class="tarjeta-codigo-cabecera">
                            <span style="font-family: var(--font-mono); color: var(--color-texto);">/battery</span>
                            <span>{{ __('portal.bots.ex_battery_header') }}</span>
                        </div>
                        <pre class="bloque-codigo" style="margin: 0; border: none; border-radius: 0;"><code>🔋 Batería de routers · Andalucía (5 activos)
🔴 1 crítico · 🟠 1 bajo · 🟢 2 normales · 🔌 1 alimentado

📍 Cádiz (2)
• 🔴 CAD1 · 18 % (3,5 V) · hace 20 min
• 🔌 CAD3 · Alimentado · hace 1 min

📍 Sevilla (1)
• 🟠 SEV2 · 34 % (3,7 V) · hace 2 h

📍 Málaga (2)
• 🟢 MAL4 · 81 % (4,1 V) · hace 5 min
• 🟢 MAL5 · 92 % (4,2 V) · hace 12 min</code></pre>
                    </div>

                    <!-- Ejemplo: /routers -->
                    <div class="tarjeta-codigo">
                        <div class="tarjeta-codigo-cabecera">
                            <span style="font-family: var(--font-mono); color: var(--color-texto);">/routers</span>
                            <span>{{ __('portal.bots.ex_routers_header') }}</span>
                        </div>
                        <pre class="bloque-codigo" style="margin: 0; border: none; border-radius: 0;"><code>📶 Routers de la red · Andalucía (4 activos en 7d)

📍 Cádiz (2)
• CAD1 · 🟠 34 % · 📡 ch 22,5 % · ⬆️ tx 3,1 % · hace 2 min
• CAD3 · 🔌 Red · 📡 ch 8,0 % · ⬆️ tx 1,2 % · hace 1 min

📍 Sevilla (1)
• SEV2 · 🔋 75 % · 📡 ch — · ⬆️ tx — · hace 3 h

📍 Málaga (1)
• MAL4 · 🔋 81 % · 📡 ch 14,2 % · ⬆️ tx 0,9 % · hace 5 min</code></pre>
                    </div>

                    <!-- Ejemplo: /levels y /types -->
                    <div class="tarjeta-codigo">
                        <div class="tarjeta-codigo-cabecera">
                            <span style="font-family: var(--font-mono); color: var(--color-texto);">/levels · /types</span>
                            <span>{{ __('portal.bots.ex_filters_header') }}</span>
                        </div>
                        <pre class="bloque-codigo" style="margin: 0; border: none; border-radius: 0;"><code># Modificación de niveles de severidad:
> /levels medio alto
Riesgos activos en este canal: medio, alto

# Modificación de tipos de incidente:
> /types infraestructura
Tipos activos en este canal: infraestructura</code></pre>
                    </div>

                    <!-- Ejemplo: /settings -->
                    <div class="tarjeta-codigo">
                        <div class="tarjeta-codigo-cabecera">
                            <span style="font-family: var(--font-mono); color: var(--color-texto);">/settings</span>
                            <span>{{ __('portal.bots.ex_settings_header') }}</span>
                        </div>
                        <pre class="bloque-codigo" style="margin: 0; border: none; border-radius: 0;"><code>Configuración de este canal:
Este canal recibe alertas de riesgo medio y alto, de tipo infraestructura.
Activo desde: 2026-09-15 10:20 UTC</code></pre>
                    </div>

                    <!-- Ejemplo: /help -->
                    <div class="tarjeta-codigo">
                        <div class="tarjeta-codigo-cabecera">
                            <span style="font-family: var(--font-mono); color: var(--color-texto);">/help</span>
                            <span>{{ __('portal.bots.ex_help_header') }}</span>
                        </div>
                        <pre class="bloque-codigo" style="margin: 0; border: none; border-radius: 0;"><code>/status · /battery · /routers · /levels · /types · /settings
Cómo usar el bot: https://{{ config('proyecto.dominio') }}/bots</code></pre>
                    </div>

                </div>
            </section>

            <!-- 5. Filtros por Defecto -->
            <section style="margin-bottom: 4rem;">
                <div class="tarjeta" style="padding: 2rem; background: var(--color-superficie); border-left: 4px solid var(--color-acento);">
                    <h2 style="font-size: 1.5rem; font-weight: 800; margin-bottom: 0.75rem; color: var(--color-texto);">
                        {{ __('portal.bots.filters_title') }}
                    </h2>
                    <p style="color: var(--color-texto-2); font-size: 0.95rem; line-height: 1.6; margin-bottom: 1.25rem;">
                        {{ __('portal.bots.filters_lead') }}
                    </p>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
                        <div style="padding: 1rem; border-radius: var(--radio-md); background: var(--color-superficie-sutil); border: 1px solid var(--color-borde);">
                            <div style="font-size: 0.8rem; font-weight: 700; text-transform: uppercase; color: var(--color-texto-3); margin-bottom: 0.25rem;">
                                {{ __('portal.bots.default_risks') }}
                            </div>
                            <div style="font-size: 1.15rem; font-weight: 800; color: var(--color-texto); font-family: var(--font-mono);">
                                {{ config('proyecto.bots.riesgos_defecto') }}
                            </div>
                            <div style="font-size: 0.8rem; color: var(--color-texto-2); margin-top: 0.25rem;">
                                {{ __('portal.bots.default_risks_desc') }}
                            </div>
                        </div>

                        <div style="padding: 1rem; border-radius: var(--radio-md); background: var(--color-superficie-sutil); border: 1px solid var(--color-borde);">
                            <div style="font-size: 0.8rem; font-weight: 700; text-transform: uppercase; color: var(--color-texto-3); margin-bottom: 0.25rem;">
                                {{ __('portal.bots.default_types') }}
                            </div>
                            <div style="font-size: 1.15rem; font-weight: 800; color: var(--color-texto); font-family: var(--font-mono);">
                                {{ config('proyecto.bots.tipos_defecto') }}
                            </div>
                            <div style="font-size: 0.8rem; color: var(--color-texto-2); margin-top: 0.25rem;">
                                {{ __('portal.bots.default_types_desc') }}
                            </div>
                        </div>
                    </div>

                    <p style="color: var(--color-texto-2); font-size: 0.9rem; line-height: 1.5; margin: 0;">
                        <strong>{{ __('portal.bots.practical_recommendations') }}</strong><br>
                        • {{ __('portal.bots.rec_all') }} <code>/levels bajo medio alto</code> {{ __('y') ?? 'y' }} <code>/types infraestructura clientes</code>.<br>
                        • {{ __('portal.bots.rec_critical') }} <code>/levels alto</code>.
                    </p>
                </div>
            </section>

            <!-- 6. Así es un Aviso (Resaltado Destacado) -->
            <section style="margin-bottom: 4rem;">
                <div style="margin-bottom: 1.5rem;">
                    <h2 style="font-size: 1.75rem; font-weight: 800; margin-bottom: 0.5rem; color: var(--color-texto);">
                        {{ __('portal.bots.live_alert_title') }}
                    </h2>
                    <p style="color: var(--color-texto-2); font-size: 1rem; margin: 0;">
                        {{ __('portal.bots.live_alert_lead') }}
                    </p>
                </div>

                <!-- Tarjeta llamativa destacada -->
                <div class="tarjeta" style="border: 2px solid var(--color-critico-texto); border-radius: var(--radio-lg); overflow: hidden; box-shadow: var(--sombra-2); background: var(--color-superficie);">
                    
                    <!-- Cabecera de la notificación -->
                    <div style="background: var(--color-critico-fondo); padding: 1.25rem 1.75rem; border-bottom: 1px solid var(--color-borde); display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 0.75rem;">
                        <div style="display: flex; align-items: center; gap: 0.6rem;">
                            <span style="font-size: 1.25rem;">🔴</span>
                            <span style="font-weight: 800; font-size: 0.95rem; color: var(--color-critico-texto); text-transform: uppercase; letter-spacing: 0.05em;">
                                {{ __('portal.bots.live_alert_badge') }}
                            </span>
                        </div>
                        <div style="display: flex; gap: 0.5rem;">
                            <x-chip-estado nivel="critico" :texto="__('portal.bots.risk_high')" />
                            <x-chip-estado nivel="info" :texto="__('portal.bots.type_infra')" />
                        </div>
                    </div>

                    <!-- Mensaje en bloque de código resaltado -->
                    <div style="padding: 1.75rem;">
                        <div style="margin-bottom: 1.5rem;">
                            <div style="font-size: 0.8rem; font-weight: 700; text-transform: uppercase; color: var(--color-texto-3); margin-bottom: 0.5rem; letter-spacing: 0.06em;">
                                {{ __('portal.bots.chat_format') }}
                            </div>
                            <div class="bloque-codigo" style="margin: 0; font-size: 1rem; line-height: 1.6; background: var(--color-superficie-sutil); border: 1px solid var(--color-borde); border-left: 5px solid var(--color-critico-texto); padding: 1.25rem 1.5rem;"><code>🔴 ALTO · Infraestructura · Bucle de reinicio
!3a8f1c04 (Cádiz-Repetidor-Norte) · ROUTER · Cádiz
!3a8f1c04 se ha reiniciado 7 veces en la última hora
https://{{ config('proyecto.dominio') }}/alertas/alt-90412</code></div>
                        </div>

                        <!-- Características clave del sistema de avisos -->
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 260px), 1fr)); gap: 1rem; margin-top: 1.5rem;">
                            <div style="padding: 1rem; border-radius: var(--radio-md); background: var(--color-superficie-sutil); border: 1px solid var(--color-borde);">
                                <div style="font-weight: 700; font-size: 0.92rem; color: var(--color-texto); margin-bottom: 0.35rem; display: flex; align-items: center; gap: 0.4rem;">
                                    <span>🔵 🟠 🔴 ✅</span>
                                    <span>{{ __('portal.bots.feat_severity_title') }}</span>
                                </div>
                                <p style="font-size: 0.85rem; color: var(--color-texto-2); margin: 0; line-height: 1.5;">
                                    {{ __('portal.bots.feat_severity_desc') }}
                                </p>
                            </div>

                            <div style="padding: 1rem; border-radius: var(--radio-md); background: var(--color-superficie-sutil); border: 1px solid var(--color-borde);">
                                <div style="font-weight: 700; font-size: 0.92rem; color: var(--color-texto); margin-bottom: 0.35rem; display: flex; align-items: center; gap: 0.4rem;">
                                    <span>🧵</span>
                                    <span>{{ __('portal.bots.feat_threads_title') }}</span>
                                </div>
                                <p style="font-size: 0.85rem; color: var(--color-texto-2); margin: 0; line-height: 1.5;">
                                    {{ __('portal.bots.feat_threads_desc') }}
                                </p>
                            </div>

                            <div style="padding: 1rem; border-radius: var(--radio-md); background: var(--color-superficie-sutil); border: 1px solid var(--color-borde);">
                                <div style="font-weight: 700; font-size: 0.92rem; color: var(--color-texto); margin-bottom: 0.35rem; display: flex; align-items: center; gap: 0.4rem;">
                                    <span>📍</span>
                                    <span>{{ __('portal.bots.feat_location_title') }}</span>
                                </div>
                                <p style="font-size: 0.85rem; color: var(--color-texto-2); margin: 0; line-height: 1.5;">
                                    {{ __('portal.bots.feat_location_desc') }}
                                </p>
                            </div>

                            <div style="padding: 1rem; border-radius: var(--radio-md); background: var(--color-superficie-sutil); border: 1px solid var(--color-borde);">
                                <div style="font-weight: 700; font-size: 0.92rem; color: var(--color-texto); margin-bottom: 0.35rem; display: flex; align-items: center; gap: 0.4rem;">
                                    <span>🛡️</span>
                                    <span>{{ __('portal.bots.feat_antispam_title') }}</span>
                                </div>
                                <p style="font-size: 0.85rem; color: var(--color-texto-2); margin: 0; line-height: 1.5;">
                                    {{ __('portal.bots.feat_antispam_desc') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- 7. Para no llenar tu canal, Webhooks y Privacidad -->
            <section style="margin-bottom: 4rem;">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 280px), 1fr)); gap: 1.5rem;">
                    
                    <!-- Para no llenar tu canal -->
                    <div class="tarjeta" style="padding: 1.75rem; background: var(--color-superficie);">
                        <div style="font-size: 1.5rem; margin-bottom: 0.5rem;">🔇</div>
                        <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 0.5rem; color: var(--color-texto);">
                            {{ __('portal.bots.card_rate_limit_title') }}
                        </h3>
                        <p style="font-size: 0.9rem; color: var(--color-texto-2); line-height: 1.55; margin: 0;">
                            {{ __('portal.bots.card_rate_limit_desc') }}
                        </p>
                    </div>

                    <!-- Webhooks -->
                    <div class="tarjeta" style="padding: 1.75rem; background: var(--color-superficie);">
                        <div style="font-size: 1.5rem; margin-bottom: 0.5rem;">⚡</div>
                        <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 0.5rem; color: var(--color-texto);">
                            {{ __('portal.bots.card_webhooks_title') }}
                        </h3>
                        <p style="font-size: 0.9rem; color: var(--color-texto-2); line-height: 1.55; margin-bottom: 0.75rem;">
                            {!! __('portal.bots.card_webhooks_desc') !!}
                        </p>
                        <a href="/api{{ $langQuery }}" style="font-size: 0.88rem; font-weight: 600;">{{ __('portal.bots.card_webhooks_link') }}</a>
                    </div>

                    <!-- Privacidad -->
                    <div class="tarjeta" style="padding: 1.75rem; background: var(--color-superficie);">
                        <div style="font-size: 1.5rem; margin-bottom: 0.5rem;">🔒</div>
                        <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 0.5rem; color: var(--color-texto);">
                            {{ __('portal.bots.card_privacy_title') }}
                        </h3>
                        <p style="font-size: 0.9rem; color: var(--color-texto-2); line-height: 1.55; margin-bottom: 0.75rem;">
                            {{ __('portal.bots.card_privacy_desc') }}
                        </p>
                        <a href="/legal/privacidad{{ $langQuery }}" style="font-size: 0.88rem; font-weight: 600;">{{ __('portal.bots.card_privacy_link') }}</a>
                    </div>

                </div>
            </section>

            <!-- Sugerencias para bots -->
            <section style="margin-bottom: 3rem; background: var(--color-superficie-sutil); border: 1px solid var(--color-borde); border-radius: var(--radio-md); padding: 1.25rem 1.5rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
                <div style="font-size: 0.92rem; color: var(--color-texto-2);">
                    {{ __('portal.bots.suggestions_cta') }}
                </div>
                <a href="/sugerencias{{ $langQuery }}" class="btn btn-secundario" style="font-size: 0.85rem; padding: 0.45rem 0.9rem;">
                    {{ __('portal.bots.btn_propose') }}
                </a>
            </section>

            <!-- Banner de Emergencias -->
            <div style="margin-top: 3.5rem;">
                <x-aviso-emergencias />
            </div>
        </article>
    </div>
</x-layout>
