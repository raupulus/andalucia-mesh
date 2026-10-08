@php
    $currentLang = app()->getLocale();
    $langParam = $currentLang !== 'es' ? '&lang=' . $currentLang : '';
    $langQueryOnly = $currentLang !== 'es' ? '?lang=' . $currentLang : '';
@endphp

<x-layout :title="__('portal.hardware.meta_title')" :description="__('portal.hardware.meta_description')">
    <div class="contenedor seccion">
        <!-- 1. Cabecera y Contexto -->
        <header style="margin-bottom: 2.5rem;">
            <!-- Migas de pan / retorno -->
            <div style="margin-bottom: 1.25rem;">
                <a href="/{{ $langQueryOnly }}" style="font-size: 0.9rem; text-decoration: none; color: var(--color-texto-2); display: inline-flex; align-items: center; gap: 0.35rem;">
                    ← {{ __('portal.hardware.back_to_home') }}
                </a>
            </div>

            <h1 style="font-size: clamp(2rem, 3.5vw, 2.75rem); font-weight: 800; color: var(--color-texto-1); margin-bottom: 0.75rem; letter-spacing: -0.02em;">
                {{ __('portal.hardware.heading') }}
            </h1>

            <p class="lead" style="margin-bottom: 1.25rem; font-size: 1.15rem; line-height: 1.6; max-width: 860px;">
                {{ __('portal.hardware.lead') }}
            </p>

            <!-- Aviso de transparencia técnica -->
            <div style="background-color: var(--color-superficie-sutil); border: 1px solid var(--color-borde); border-left: 4px solid var(--color-acento); border-radius: var(--radio-md); padding: 1rem 1.25rem; max-width: 860px; font-size: 0.92rem; color: var(--color-texto-2); line-height: 1.55;">
                <div style="display: flex; align-items: center; gap: 0.5rem; font-weight: 700; color: var(--color-texto-1); margin-bottom: 0.35rem;">
                    <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--color-acento); flex-shrink: 0;">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="16" x2="12" y2="12"></line>
                        <line x1="12" y1="8" x2="12.01" y2="8"></line>
                    </svg>
                    <span>{{ __('portal.hardware.transparency_title') }}</span>
                </div>
                <span>{{ __('portal.hardware.transparency_text') }}</span>
            </div>
        </header>

        <!-- 2. Barra de Filtros y Búsqueda -->
        <section aria-label="{{ __('portal.hardware.filters_heading') }}" style="margin-bottom: 2.5rem;">
            <div style="display: flex; flex-direction: column; gap: 1.25rem;">
                <!-- Buscador por texto -->
                <form method="GET" action="{{ url('/hardware') }}" style="display: flex; flex-wrap: wrap; gap: 0.5rem; align-items: center; max-width: 540px; width: 100%;">
                    @if($slugActual)
                        <input type="hidden" name="categoria" value="{{ $slugActual }}">
                    @endif
                    @if($currentLang !== 'es')
                        <input type="hidden" name="lang" value="{{ $currentLang }}">
                    @endif
                    <div style="position: relative; flex-grow: 1; min-width: 220px;">
                        <input
                            type="search"
                            name="q"
                            value="{{ $busqueda }}"
                            placeholder="{{ __('portal.hardware.search_placeholder') }}"
                            aria-label="{{ __('portal.hardware.search_placeholder') }}"
                            style="width: 100%; box-sizing: border-box; padding: 0.65rem 1rem; font-size: 0.95rem; border: 1px solid var(--color-borde-control); border-radius: var(--radio-md); background: var(--color-superficie); color: var(--color-texto); outline: none;"
                        >
                    </div>
                    <button type="submit" class="btn btn-primario" style="padding: 0.65rem 1.2rem;">
                        {{ __('portal.hardware.search_btn') }}
                    </button>
                    @if($busqueda !== '' || !empty($slugActual))
                        <a href="{{ url('/hardware') . $langQueryOnly }}" class="btn btn-secundario" style="padding: 0.65rem 1rem;">
                            {{ __('portal.hardware.clear_btn') }}
                        </a>
                    @endif
                </form>

                <!-- Filtros tipo Pills/Chips -->
                <nav aria-label="{{ __('portal.hardware.categories_label') }}" style="display: flex; flex-wrap: wrap; gap: 0.6rem; align-items: center;">
                    <!-- Opción: Todas las categorías -->
                    @php
                        $queryTodas = ($busqueda !== '' ? '?q=' . urlencode($busqueda) : '');
                        if ($currentLang !== 'es') {
                            $queryTodas .= ($queryTodas === '' ? '?lang=' . $currentLang : '&lang=' . $currentLang);
                        }
                    @endphp
                    <a
                        href="{{ url('/hardware') . $queryTodas }}"
                        class="btn {{ empty($slugActual) ? 'btn-primario' : 'btn-secundario' }}"
                        style="border-radius: var(--radio-full); padding: 0.4rem 1rem; font-size: 0.88rem; text-decoration: none; display: inline-flex; align-items: center; gap: 0.45rem;"
                    >
                        <span>{{ __('portal.hardware.all_categories') }}</span>
                        <span class="chip {{ empty($slugActual) ? 'chip-correcto' : 'chip-neutro' }}" style="font-size: 0.75rem; padding: 0.15rem 0.45rem;">
                            {{ $totalArticulos }}
                        </span>
                    </a>

                    <!-- Cada categoría activa -->
                    @foreach($categorias as $cat)
                        @php
                            $catHref = url('/hardware') . '?categoria=' . $cat->slug;
                            if ($busqueda !== '') {
                                $catHref .= '&q=' . urlencode($busqueda);
                            }
                            if ($currentLang !== 'es') {
                                $catHref .= '&lang=' . $currentLang;
                            }
                            $esActiva = ($slugActual === $cat->slug);
                        @endphp
                        <a
                            href="{{ $catHref }}"
                            class="btn {{ $esActiva ? 'btn-primario' : 'btn-secundario' }}"
                            style="border-radius: var(--radio-full); padding: 0.4rem 1rem; font-size: 0.88rem; text-decoration: none; display: inline-flex; align-items: center; gap: 0.45rem;"
                        >
                            <span>{{ $cat->translated_name }}</span>
                            <span class="chip {{ $esActiva ? 'chip-correcto' : 'chip-neutro' }}" style="font-size: 0.75rem; padding: 0.15rem 0.45rem;">
                                {{ $cat->items_count }}
                            </span>
                        </a>
                    @endforeach
                </nav>
            </div>
        </section>

        <!-- 3. Cuadrícula Responsive de Tarjetas o Estado Vacío -->
        @if($articulos->isEmpty())
            <!-- 4. Estado Vacío -->
            <div class="tarjeta" style="padding: 4rem 2rem; text-align: center; background: var(--color-superficie); border-radius: var(--radio-lg); max-width: 600px; margin: 2rem auto;">
                <div style="font-size: 3rem; margin-bottom: 1rem; line-height: 1;" aria-hidden="true">
                    🔍
                </div>
                <h2 style="font-size: 1.5rem; font-weight: 700; color: var(--color-texto-1); margin-bottom: 0.75rem;">
                    {{ __('portal.hardware.empty_title') }}
                </h2>
                <p style="color: var(--color-texto-2); font-size: 1rem; line-height: 1.6; margin-bottom: 1.75rem;">
                    {{ __('portal.hardware.empty_text') }}
                </p>
                <div>
                    <a href="{{ url('/hardware') . $langQueryOnly }}" class="btn btn-primario">
                        {{ __('portal.hardware.reset_filters') }}
                    </a>
                </div>
            </div>
        @else
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.75rem; align-items: stretch;">
                @foreach($articulos as $item)
                    <article class="tarjeta tarjeta-hover" style="display: flex; flex-direction: column; overflow: hidden; height: 100%; border-radius: var(--radio-lg); background: var(--color-superficie);">
                        <!-- Imagen cuadrada 1:1 con badges -->
                        <div style="position: relative; aspect-ratio: 1 / 1; width: 100%; background: var(--color-superficie-sutil); overflow: hidden; border-bottom: 1px solid var(--color-borde);">
                            <img
                                src="{{ $item->image_url }}"
                                alt="{{ $item->translated_name }}"
                                loading="lazy"
                                style="width: 100%; height: 100%; object-fit: cover; display: block;"
                            >

                            <!-- Badge de categoría -->
                            @if($item->category)
                                <span class="chip chip-neutro" style="position: absolute; top: 0.75rem; left: 0.75rem; background: rgba(31, 32, 41, 0.85); color: #F0F0F5; border: 1px solid rgba(255, 255, 255, 0.15); font-size: 0.75rem; backdrop-filter: blur(4px);">
                                    {{ $item->category->translated_name }}
                                </span>
                            @endif

                            <!-- Badge de destacado / recomendado -->
                            @if($item->is_featured)
                                <span class="chip chip-correcto" style="position: absolute; top: 0.75rem; right: 0.75rem; font-weight: 700; box-shadow: var(--sombra-1);">
                                    ★ {{ __('portal.hardware.featured_badge') }}
                                </span>
                            @endif
                        </div>

                        <!-- Cuerpo de la tarjeta -->
                        <div style="padding: 1.25rem; display: flex; flex-direction: column; flex-grow: 1;">
                            <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--color-texto-1); margin-bottom: 0.65rem; line-height: 1.3;">
                                {{ $item->translated_name }}
                            </h2>

                            <p style="font-size: 0.92rem; color: var(--color-texto-2); line-height: 1.55; margin-bottom: 1.25rem; flex-grow: 1; white-space: pre-line;">{{ $item->translated_description }}</p>

                            <!-- Indicador de precio orientativo -->
                            <div style="font-size: 0.88rem; color: var(--color-texto-3); padding-top: 0.75rem; margin-bottom: 1rem; border-top: 1px dashed var(--color-borde);">
                                @if($item->last_price !== null)
                                    <span>{{ __('portal.hardware.last_price_label') }}: <strong style="color: var(--color-texto-1); font-weight: 700;">~{{ $item->formatted_price }}</strong></span>
                                @else
                                    <span style="font-style: italic;">{{ __('portal.hardware.price_unknown') }}</span>
                                @endif
                            </div>

                            <!-- Botonera de acción -->
                            <div style="display: flex; flex-direction: column; gap: 0.5rem; margin-top: auto;">
                                <a
                                    href="{{ $item->buy_url }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="btn btn-primario"
                                    style="width: 100%; text-decoration: none;"
                                >
                                    <span>{{ __('portal.hardware.btn_buy') }}</span>
                                    <svg aria-hidden="true" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                                        <polyline points="15 3 21 3 21 9"></polyline>
                                        <line x1="10" y1="14" x2="21" y2="3"></line>
                                    </svg>
                                </a>

                                @if(!empty($item->guide_url))
                                    <a
                                        href="{{ $item->guide_url }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="btn btn-secundario"
                                        style="width: 100%; text-decoration: none;"
                                    >
                                        <span>{{ __('portal.hardware.btn_guide') }}</span>
                                        <svg aria-hidden="true" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
                                            <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
                                        </svg>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</x-layout>
