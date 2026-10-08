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
                <p class="lead" style="margin-bottom: 0;">
                    {{ __('portal.node_check.lead') }}
                </p>
            </header>

            <!-- Formulario de búsqueda -->
            <form action="/revisa-tu-nodo" method="GET" style="margin-bottom: 3rem;">
                @if($currentLang !== 'es')
                    <input type="hidden" name="lang" value="{{ $currentLang }}">
                @endif
                <label for="campo-buscar-nodo" class="sr-only">
                    {{ __('portal.node_check.search_label') }}
                </label>
                <div class="form-busqueda-responsive">
                    <input type="search" 
                           id="campo-buscar-nodo"
                           name="buscar" 
                           value="{{ $busqueda }}" 
                           placeholder="{{ __('portal.node_check.search_placeholder') }}" 
                           aria-label="{{ __('portal.node_check.search_label') }}"
                           required 
                           minlength="2" 
                           style="flex-grow: 1; padding: 0.85rem 1.25rem; font-size: 1.05rem; border: 2px solid var(--color-borde); border-radius: var(--radio-md); background: var(--color-superficie); color: var(--color-texto);" />
                    <button type="submit" class="btn btn-primario" style="padding: 0.85rem 1.75rem; font-size: 1.05rem;">
                        {{ __('portal.node_check.btn_search') }}
                    </button>
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
</x-layout>
