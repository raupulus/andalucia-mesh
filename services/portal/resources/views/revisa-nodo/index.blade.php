@php
    $currentLang = app()->getLocale();
    $langQuery = $currentLang !== 'es' ? '?lang=' . $currentLang : '';
@endphp

<x-layout :title="__('portal.node_check.meta_title')" :description="__('portal.node_check.meta_description')" :image="asset('img/og/og-revisa-nodo.webp')">
    <div class="contenedor seccion">
        <div style="max-width: 760px; margin: 0 auto;">
            <header style="text-align: center; margin-bottom: 2.5rem;">
                <h1 style="font-size: 2.5rem; font-weight: 800; color: var(--color-texto-1); margin-bottom: 0.75rem;">
                    {{ __('portal.node_check.heading') }}
                </h1>
                <p class="lead" style="margin-bottom: 1.25rem;">
                    {{ __('portal.node_check.lead') }}
                </p>
                <div style="display: flex; justify-content: center; margin-top: 1rem;">
                    <x-botones-compartir :url="url()->current()" :titulo="__('portal.node_check.heading')" :texto="__('portal.node_check.lead')" align="center" />
                </div>
            </header>

            <!-- Formulario de búsqueda con Autocompletado Reactivo -->
            <form action="/revisa-tu-nodo" method="GET" style="margin-bottom: 3rem; position: relative;">
                @if($currentLang !== 'es')
                    <input type="hidden" name="lang" value="{{ $currentLang }}">
                @endif
                <label for="campo-buscar-nodo" class="sr-only">
                    {{ __('portal.node_check.search_label') }}
                </label>
                <div class="form-busqueda-responsive" style="position: relative;">
                    <div style="position: relative; flex-grow: 1;">
                        <input type="search" 
                               id="campo-buscar-nodo"
                               name="buscar" 
                               value="{{ $busqueda }}" 
                               placeholder="{{ __('portal.node_check.search_placeholder') }}" 
                               aria-label="{{ __('portal.node_check.search_label') }}"
                               autocomplete="off"
                               required 
                               minlength="2" 
                               oninput="enInputBuscarRevisa(this.value);"
                               onkeydown="enKeydownBuscarRevisa(event);"
                               style="width: 100%; padding: 0.85rem 2.75rem 0.85rem 1.25rem; font-size: 1.05rem; border: 2px solid var(--color-borde); border-radius: var(--radio-md); background: var(--color-superficie); color: var(--color-texto);" />
                        <div id="spinner-busqueda-revisa" style="display: none; position: absolute; right: 14px; top: 50%; transform: translateY(-50%); pointer-events: none;">
                            <div class="spinner-inline-mini"></div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primario" style="padding: 0.85rem 1.75rem; font-size: 1.05rem;">
                        {{ __('portal.node_check.btn_search') }}
                    </button>

                    <!-- Desplegable de Sugerencias -->
                    <ul id="lista-sugerencias-revisa" class="desplegable-sugerencias-cobertura" style="display: none; top: calc(100% + 6px);" role="listbox">
                    </ul>
                </div>
            </form>

            <!-- Resultados de búsqueda -->
            @if(!empty($busqueda))
                <section style="margin-bottom: 3.5rem;">
                    <h2 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 1rem; color: var(--color-texto-1);">
                        {{ __('portal.node_check.results_for', ['term' => $busqueda, 'count' => count($resultados)]) }}
                    </h2>

                    @if(empty($resultados))
                        <div class="tarjeta" style="padding: 2rem; text-align: center; color: var(--color-texto-2);">
                            {{ __('portal.node_check.no_results') }}
                        </div>
                    @else
                        <div style="flex-direction: column; display: flex; gap: 0.75rem;">
                            @foreach($resultados as $nodo)
                                <a href="/revisa-tu-nodo/{{ $nodo->id }}{{ $langQuery }}" class="tarjeta tarjeta-hover" style="display: flex; justify-content: space-between; align-items: center; padding: 1.25rem; text-decoration: none; color: inherit; flex-wrap: wrap; gap: 0.75rem;">
                                    <div>
                                        <div style="font-size: 1.1rem; font-weight: 700; color: var(--color-texto-1);">
                                            {{ $nodo->short_name ?? $nodo->id }}
                                            @if(!empty($nodo->long_name))
                                                <span style="font-size: 0.9rem; font-weight: normal; color: var(--color-texto-2);">({{ $nodo->long_name }})</span>
                                            @endif
                                        </div>
                                        <div style="font-size: 0.85rem; color: var(--color-texto-3); margin-top: 0.25rem;">
                                            <code>{{ $nodo->id }}</code> · {{ $nodo->province ?? __('portal.node_check.unknown_province') }} · {{ __('portal.node_check.role') }} <code>{{ $nodo->role ?? 'CLIENT' }}</code>
                                        </div>
                                    </div>
                                    <span class="btn btn-secundario" style="padding: 0.35rem 0.75rem; font-size: 0.85rem;">
                                        {{ __('portal.node_check.btn_view_report') }}
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </section>
            @endif

            <!-- Explicación pedagógica de la auditoría -->
            <section class="tarjeta" style="padding: 2rem; background: var(--color-superficie-sutil);">
                <h2 style="font-size: 1.35rem; font-weight: 700; margin-bottom: 1rem; color: var(--color-texto-1);">
                    {{ __('portal.node_check.what_we_analyze') }}
                </h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 280px), 1fr)); gap: 1.5rem; font-size: 0.92rem; line-height: 1.55; color: var(--color-texto-2);">
                    <div>
                        <h3 style="font-size: 1.05rem; font-weight: 700; color: var(--color-texto-1); margin-bottom: 0.25rem;">{{ __('portal.node_check.check_1_title') }}</h3>
                        <p style="margin: 0;">{{ __('portal.node_check.check_1_desc') }}</p>
                    </div>
                    <div>
                        <h3 style="font-size: 1.05rem; font-weight: 700; color: var(--color-texto-1); margin-bottom: 0.25rem;">{{ __('portal.node_check.check_2_title') }}</h3>
                        <p style="margin: 0;">{{ __('portal.node_check.check_2_desc') }}</p>
                    </div>
                    <div>
                        <h3 style="font-size: 1.05rem; font-weight: 700; color: var(--color-texto-1); margin-bottom: 0.25rem;">{{ __('portal.node_check.check_3_title') }}</h3>
                        <p style="margin: 0;">{{ __('portal.node_check.check_3_desc') }}</p>
                    </div>
                    <div>
                        <h3 style="font-size: 1.05rem; font-weight: 700; color: var(--color-texto-1); margin-bottom: 0.25rem;">{{ __('portal.node_check.check_4_title') }}</h3>
                        <p style="margin: 0;">{{ __('portal.node_check.check_4_desc') }}</p>
                    </div>
                </div>
            </section>
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
            const input = document.getElementById('campo-buscar-nodo');
            if (desplegable && !desplegable.contains(e.target) && e.target !== input) {
                desplegable.style.display = 'none';
            }
        });
    </script>
</x-layout>
