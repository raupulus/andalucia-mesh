@php
    $currentLang = app()->getLocale();
    $langQuery = $currentLang !== 'es' ? '?lang=' . $currentLang : '';
@endphp

<x-layout :title="__('portal.node_check.report_title', ['id' => $idBuscado, 'name' => config('proyecto.nombre')])" :description="__('portal.node_check.report_description')" :image="asset('img/og/og-revisa-nodo.webp')">
    <div class="contenedor seccion">
        <div style="max-width: 820px; margin: 0 auto;">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem;">
                <a href="/revisa-tu-nodo{{ $langQuery }}" style="font-size: 0.9rem; color: var(--color-texto-2); text-decoration: none; display: inline-flex; align-items: center; gap: 0.35rem;">
                    {{ __('portal.node_check.back_to_search') }}
                </a>
                <div style="position: relative; width: 280px;">
                    <input type="search"
                           id="campo-buscar-nodo-nav"
                           placeholder="{{ __('portal.node_check.search_placeholder') }}"
                           autocomplete="off"
                           oninput="enInputBuscarRevisa(this.value);"
                           onkeydown="enKeydownBuscarRevisa(event);"
                           style="width: 100%; padding: 0.45rem 2rem 0.45rem 0.85rem; font-size: 0.85rem; border: 1px solid var(--color-borde); border-radius: var(--radio-sm, 6px); background: var(--color-superficie); color: var(--color-texto);" />
                    <div id="spinner-busqueda-revisa" style="display: none; position: absolute; right: 8px; top: 50%; transform: translateY(-50%); pointer-events: none;">
                        <div class="spinner-inline-mini" style="width: 14px; height: 14px;"></div>
                    </div>
                    <ul id="lista-sugerencias-revisa" class="desplegable-sugerencias-cobertura" style="display: none; top: calc(100% + 4px);" role="listbox"></ul>
                </div>
            </div>

            @if(!empty($noEncontrado))
                <div class="tarjeta" style="padding: 2.5rem; text-align: center;">
                    <div style="font-size: 2.5rem; margin-bottom: 1rem;">🔍</div>
                    <h1 style="font-size: 1.5rem; font-weight: 700; color: var(--color-texto-1); margin-bottom: 0.5rem;">
                        {{ __('portal.node_check.not_found_title', ['id' => $idBuscado]) }}
                    </h1>
                    <p style="color: var(--color-texto-2); max-width: 500px; margin: 0 auto 1.5rem auto; line-height: 1.5;">
                        {{ __('portal.node_check.not_found_desc') }}
                    </p>
                    <a href="/configura-tu-nodo{{ $langQuery }}" class="btn btn-primario">
                        {{ __('portal.node_check.btn_view_setup_guide') }}
                    </a>
                </div>
            @else
                <!-- Cabecera de identidad del nodo -->
                <header class="tarjeta" style="padding: 2rem; margin-bottom: 2rem;">
                    <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: flex-start; gap: 1rem; margin-bottom: 1rem;">
                        <div>
                            <h1 style="font-size: 1.85rem; font-weight: 800; color: var(--color-texto-1); margin: 0 0 0.25rem 0;">
                                {{ $informe['node']['short_name'] ?? $informe['node']['id'] }}
                                @if(!empty($informe['node']['long_name']))
                                    <span style="font-size: 1.1rem; font-weight: normal; color: var(--color-texto-2);">({{ $informe['node']['long_name'] }})</span>
                                @endif
                            </h1>
                            <div style="font-family: monospace; font-size: 1rem; color: var(--color-enlace);">
                                {{ $informe['node']['id'] }}
                            </div>
                        </div>

                        <!-- Veredicto general de salud -->
                        @php
                            $estadoSalud = $informe['health_status'] ?? 'optimo';
                            $chipTipo = match($estadoSalud) {
                                'optimo' => 'correcto',
                                'mejorable' => 'aviso',
                                'atencion_requerida' => 'critico',
                                default => 'neutro',
                            };
                            $estadoTexto = match($estadoSalud) {
                                'optimo' => __('portal.node_check.health_optimal'),
                                'mejorable' => __('portal.node_check.health_improvable'),
                                'atencion_requerida' => __('portal.node_check.health_action_required'),
                                default => __('portal.node_check.health_unclassified'),
                            };
                        @endphp
                        <div>
                            <x-chip-estado :tipo="$chipTipo" :texto="$estadoTexto" />
                        </div>
                    </div>

                    <!-- Ficha rápida de hardware y rol -->
                    <div style="display: flex; flex-wrap: wrap; gap: 1.5rem; font-size: 0.9rem; color: var(--color-texto-2); border-top: 1px solid var(--color-borde); padding-top: 1rem;">
                        <div>{{ __('portal.node_check.province') }} <strong>{{ $informe['node']['province'] ?? __('portal.node_check.unknown_province') }}</strong></div>
                        <div>{{ __('portal.node_check.role') }} <code>{{ $informe['node']['role'] ?? 'CLIENT' }}</code></div>
                        <div>{{ __('portal.node_check.hw_model') }} <strong>{{ $informe['node']['hw_model'] ?? __('portal.node_check.not_specified') }}</strong></div>
                        <div>
                            {{ __('portal.node_check.last_seen') }}
                            <strong>
                                @if(!empty($informe['node']['last_seen']))
                                    @php
                                        $lsDt = null;
                                        try { $lsDt = \Carbon\Carbon::parse($informe['node']['last_seen']); } catch (\Throwable) {}
                                    @endphp
                                    @if($lsDt)
                                        <time class="fecha-local" datetime="{{ $lsDt->toIso8601String() }}">
                                            {{ $lsDt->timezone(config('proyecto.zona_horaria', 'Europe/Madrid'))->format('Y-m-d H:i:s') }}
                                        </time>
                                    @else
                                        {{ $informe['node']['last_seen'] }}
                                    @endif
                                @else
                                    {{ __('portal.node_check.recent_seen') }}
                                @endif
                            </strong>
                        </div>
                    </div>
                </header>

                <div style="margin-bottom: 2rem; display: flex; justify-content: flex-end;">
                    <x-botones-compartir :url="url()->current()" :titulo="'Informe de Salud del Nodo ' . ($informe['node']['short_name'] ?? $informe['node']['id'])" />
                </div>

                <!-- 1. Hallazgos y recomendaciones constructivas -->
                @if(!empty($informe['findings']))
                    <section class="tarjeta" style="padding: 1.75rem; margin-bottom: 2rem; border-left: 4px solid var(--color-aviso-texto);">
                        <h2 style="font-size: 1.3rem; font-weight: 700; color: var(--color-texto-1); margin-bottom: 1rem;">
                            {{ __('portal.node_check.section_findings') }}
                        </h2>
                        <div style="display: flex; flex-direction: column; gap: 1rem;">
                            @foreach($informe['findings'] as $f)
                                @php
                                    $chipTipo = match($f['severidad']) {
                                        'critico' => 'critico',
                                        'aviso' => 'aviso',
                                        default => 'info',
                                    };
                                    $sevKey = strtolower((string) $f['severidad']);
                                    $textoSev = match($sevKey) {
                                        'critico' => __('portal.alerts.risk_levels.alto'),
                                        'aviso' => __('portal.alerts.risk_levels.medio'),
                                        default => __('portal.alerts.risk_levels.bajo'),
                                    };
                                @endphp
                                <div style="background: var(--color-superficie-sutil); padding: 1.25rem; border-radius: var(--radio-md); border: 1px solid var(--color-borde);">
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 0.5rem; margin-bottom: 0.5rem;">
                                        <h3 style="font-size: 1.05rem; font-weight: 700; color: var(--color-texto-1); margin: 0;">
                                            {{ $f['titulo'] }}
                                        </h3>
                                        <x-chip-estado :tipo="$chipTipo" :texto="$textoSev" />
                                    </div>
                                    <p style="font-size: 0.9rem; color: var(--color-texto-2); line-height: 1.5; margin: 0;">
                                        {{ $f['descripcion'] }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif

                <!-- 2. Parámetros de Radio y Saltos -->
                <section class="tarjeta" style="padding: 1.75rem; margin-bottom: 2rem;">
                    <h2 style="font-size: 1.3rem; font-weight: 700; color: var(--color-texto-1); margin-bottom: 1rem;">
                        {{ __('portal.node_check.section_radio') }}
                    </h2>
                    <table class="tabla" style="width: 100%; border-collapse: collapse; font-size: 0.92rem;">
                        <tbody>
                            <tr style="border-bottom: 1px solid var(--color-borde);">
                                <td style="padding: 0.6rem 0; font-weight: 600; width: 45%;">{{ __('portal.node_check.th_hop_limit') }}</td>
                                <td style="padding: 0.6rem 0;">
                                    <strong>{{ $informe['node']['hop_start_last'] ?? __('portal.node_check.hops_default') }}</strong> {{ __('portal.node_check.hops_unit') }}
                                    @if(($informe['node']['hop_start_last'] ?? 3) <= 3)
                                        <span style="color: var(--mapa-verde); margin-left: 0.5rem;">{{ __('portal.node_check.status_correct') }}</span>
                                    @else
                                        <span style="color: var(--color-critico-texto); margin-left: 0.5rem;">{{ __('portal.node_check.status_max_recommended') }}</span>
                                    @endif
                                </td>
                            </tr>
                            <tr style="border-bottom: 1px solid var(--color-borde);">
                                <td style="padding: 0.6rem 0; font-weight: 600;">{{ __('portal.node_check.th_operational_role') }}</td>
                                <td style="padding: 0.6rem 0;">
                                    <code>{{ $informe['node']['role'] ?? 'CLIENT' }}</code>
                                    @if(($informe['node']['role'] ?? '') === 'ROUTER')
                                        <span style="color: var(--color-aviso-texto); margin-left: 0.5rem;">{{ __('portal.node_check.role_coordination') }}</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 0.6rem 0; font-weight: 600;">{{ __('portal.node_check.th_gateway_function') }}</td>
                                <td style="padding: 0.6rem 0;">
                                    {{ !empty($informe['node']['is_gateway']) ? __('portal.node_check.gateway_yes') : __('portal.node_check.gateway_no') }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </section>

                <!-- 3. Cadencia de Emisiones observada -->
                <section class="tarjeta" style="padding: 1.75rem; margin-bottom: 2rem;">
                    <h2 style="font-size: 1.3rem; font-weight: 700; color: var(--color-texto-1); margin-bottom: 1rem;">
                        {{ __('portal.node_check.section_cadence') }}
                    </h2>
                    <div style="overflow-x: auto;">
                        <table class="tabla" style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
                            <thead>
                                <tr style="border-bottom: 2px solid var(--color-borde); text-align: left;">
                                    <th style="padding: 0.6rem;">{{ __('portal.node_check.th_packet_type') }}</th>
                                    <th style="padding: 0.6rem; text-align: right;">{{ __('portal.node_check.th_broadcasts') }}</th>
                                    <th style="padding: 0.6rem; text-align: right;">{{ __('portal.node_check.th_median_interval') }}</th>
                                    <th style="padding: 0.6rem;">{{ __('portal.node_check.th_recommendation') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($informe['intervals'] as $inv)
                                    @php
                                        $mediana = $inv['median_interval_s'] !== null ? (float) $inv['median_interval_s'] : null;
                                        $textoMediana = $mediana !== null 
                                            ? ($mediana >= 3600 ? round($mediana / 3600, 1) . ' h' : round($mediana / 60) . ' min')
                                            : __('portal.node_check.less_than_2_broadcasts');
                                    @endphp
                                    <tr style="border-bottom: 1px solid var(--color-borde);">
                                        <td style="padding: 0.6rem; font-weight: 600;">
                                            <code>{{ $inv['portnum'] }}</code>
                                            @if(!empty($inv['variant']))
                                                <span style="font-size: 0.8rem; color: var(--color-texto-3);">({{ $inv['variant'] }})</span>
                                            @endif
                                        </td>
                                        <td style="padding: 0.6rem; text-align: right; font-variant-numeric: tabular-nums;">
                                            {{ $inv['broadcasts'] ?? 0 }}
                                        </td>
                                        <td style="padding: 0.6rem; text-align: right; font-weight: 600; font-variant-numeric: tabular-nums;">
                                            {{ $textoMediana }}
                                        </td>
                                        <td style="padding: 0.6rem; font-size: 0.85rem; color: var(--color-texto-2);">
                                            @if($inv['portnum'] === 'nodeinfo')
                                                {{ __('portal.node_check.rec_nodeinfo') }}
                                            @elseif($inv['portnum'] === 'position')
                                                {{ __('portal.node_check.rec_position') }}
                                            @elseif($inv['portnum'] === 'telemetry')
                                                {{ __('portal.node_check.rec_telemetry') }}
                                            @else
                                                {{ __('portal.node_check.rec_as_needed') }}
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" style="padding: 1rem; text-align: center; color: var(--color-texto-3);">
                                            {{ __('portal.node_check.no_cadence') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>

                <!-- Compartir informe al pie -->
                <div style="margin-top: 2rem; padding: 1.25rem 1.5rem; background: var(--color-superficie-sutil); border-radius: var(--radio-md, 8px); border: 1px solid var(--color-borde); display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 1rem;">
                    <span style="font-size: 0.95rem; font-weight: 700; color: var(--color-texto);">{{ __('portal.share.title') }}:</span>
                    <x-botones-compartir :url="url()->current()" :titulo="'Auditoría del Nodo ' . ($informe['node']['short_name'] ?? $informe['node']['id'])" />
                </div>
            @endif
        </div>
    </div>

    <script>
        let timerBusquedaRevisa = null;
        const langQuery = '{{ $langQuery }}';

        function enInputBuscarRevisa(valor) {
            clearTimeout(timerBusquedaRevisa);
            const desplegable = document.getElementById('lista-sugerencias-revisa');
            const spinner = document.getElementById('spinner-busqueda-revisa');

            if (!desplegable) return;

            if (!valor || valor.trim().length < 2) {
                desplegable.style.display = 'none';
                if (spinner) spinner.style.display = 'none';
                return;
            }

            if (spinner) spinner.style.display = 'block';
            timerBusquedaRevisa = setTimeout(async () => {
                try {
                    const resp = await fetch('/revisa-tu-nodo/buscar?q=' + encodeURIComponent(valor.trim()));
                    const items = await resp.json();
                    renderizarSugerenciasRevisa(items);
                } catch (e) {
                    console.error('Error buscando nodos:', e);
                } finally {
                    if (spinner) spinner.style.display = 'none';
                }
            }, 180);
        }

        function renderizarSugerenciasRevisa(items) {
            const desplegable = document.getElementById('lista-sugerencias-revisa');
            if (!desplegable) return;
            desplegable.innerHTML = '';

            if (!items || items.length === 0) {
                desplegable.innerHTML = '<li style="padding: 10px 14px; font-size: 0.85rem; color: var(--color-texto-3); font-style: italic;">Sin coincidencias en la red</li>';
                desplegable.style.display = 'block';
                return;
            }

            items.forEach(item => {
                const li = document.createElement('li');
                li.className = 'item-sugerencia-nodo';
                li.dataset.nodeId = item.id;
                li.dataset.alias = item.short_name || '';

                li.innerHTML = `
                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 0.5rem;">
                        <div style="flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                            <span style="font-family: 'Ubuntu Mono', monospace; font-size: 0.85rem; color: var(--color-enlace); font-weight: 600;">${item.id}</span>
                            <strong style="color: var(--color-texto); font-size: 0.95rem; margin-left: 0.35rem;">${item.short_name || ''}</strong>
                            ${item.long_name ? `<span style="font-size: 0.82rem; color: var(--color-texto-2); margin-left: 0.35rem;">(${item.long_name})</span>` : ''}
                        </div>
                        <div style="display: flex; gap: 0.4rem; align-items: center; flex-shrink: 0;">
                            <span class="badge-rol badge-rol-client" style="font-size: 0.65rem;">${item.role || 'CLIENT'}</span>
                            <span class="badge-prov badge-prov-general" style="font-size: 0.65rem;">${item.province || 'General'}</span>
                            <span style="color: var(--color-acento); font-size: 0.8rem; font-weight: 700; margin-left: 0.25rem;">Auditar →</span>
                        </div>
                    </div>
                `;

                li.onclick = () => {
                    window.location.href = `/revisa-tu-nodo/${encodeURIComponent(item.id)}${langQuery}`;
                };

                desplegable.appendChild(li);
            });

            desplegable.style.display = 'block';
        }

        function enKeydownBuscarRevisa(event) {
            if (event.key === 'Escape') {
                const desplegable = document.getElementById('lista-sugerencias-revisa');
                if (desplegable) desplegable.style.display = 'none';
            } else if (event.key === 'Enter') {
                const desplegable = document.getElementById('lista-sugerencias-revisa');
                const primera = desplegable ? desplegable.querySelector('.item-sugerencia-nodo') : null;
                if (desplegable && desplegable.style.display !== 'none' && primera && primera.dataset.nodeId) {
                    event.preventDefault();
                    window.location.href = `/revisa-tu-nodo/${encodeURIComponent(primera.dataset.nodeId)}${langQuery}`;
                }
            }
        }

        document.addEventListener('click', (e) => {
            const desplegable = document.getElementById('lista-sugerencias-revisa');
            const input = document.getElementById('campo-buscar-nodo-nav');
            if (desplegable && !desplegable.contains(e.target) && e.target !== input) {
                desplegable.style.display = 'none';
            }
        });
    </script>
</x-layout>
