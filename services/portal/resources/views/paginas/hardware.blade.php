@php
    $currentLang = app()->getLocale();
    $langParam = $currentLang !== 'es' ? '&lang=' . $currentLang : '';
    $langQueryOnly = $currentLang !== 'es' ? '?lang=' . $currentLang : '';

    $queryTodas = ($busqueda !== '' ? '?q=' . urlencode($busqueda) : '');
    if ($currentLang !== 'es') {
        $queryTodas .= ($queryTodas === '' ? '?lang=' . $currentLang : '&lang=' . $currentLang);
    }
@endphp

<x-layout :title="__('portal.hardware.meta_title')" :description="__('portal.hardware.meta_description')">
    <x-slot:styles>
        <style>
            /* ==============================================================================
             * Catálogo de Hardware — Estilos Semánticos y Accesibles
             * ============================================================================== */

            /* Disposición en cuadrícula: Menú lateral en escritorio, superior en móvil */
            .hardware-layout {
                display: grid;
                grid-template-columns: 280px 1fr;
                gap: 2.25rem;
                align-items: start;
                margin-top: 1.5rem;
            }

            /* Barra lateral (aside) en escritorio */
            .hardware-sidebar {
                position: sticky;
                top: 5.5rem;
                z-index: 10;
            }

            .hardware-sidebar-card {
                background: var(--color-superficie);
                border: 1px solid var(--color-borde);
                border-radius: var(--radio-lg);
                padding: 1.25rem;
                box-shadow: var(--sombra-1);
            }

            .hardware-sidebar-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                margin-bottom: 1rem;
                padding-bottom: 0.75rem;
                border-bottom: 1px solid var(--color-borde);
            }

            .hardware-sidebar-title {
                font-size: 0.85rem;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                color: var(--color-texto-2);
                margin: 0;
            }

            .hardware-nav-list {
                display: flex;
                flex-direction: column;
                gap: 0.4rem;
                list-style: none;
                padding: 0;
                margin: 0;
            }

            .hardware-nav-item {
                margin: 0;
                padding: 0;
            }

            .hardware-nav-link {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 0.75rem;
                padding: 0.65rem 0.85rem;
                border-radius: var(--radio-md);
                text-decoration: none;
                color: var(--color-texto-2);
                background: transparent;
                font-size: 0.92rem;
                font-weight: 500;
                min-height: 44px;
                border: 1px solid transparent;
                transition: background-color 0.15s ease, color 0.15s ease, border-color 0.15s ease;
            }

            .hardware-nav-link:hover {
                background-color: var(--color-superficie-sutil);
                color: var(--color-texto-1);
            }

            .hardware-nav-link:focus-visible {
                outline: 2px solid var(--color-foco);
                outline-offset: 2px;
            }

            /* Elemento activo con colores oficiales de la plataforma */
            .hardware-nav-link.is-active {
                background-color: var(--color-acento-suave);
                color: var(--color-enlace);
                font-weight: 700;
                border-color: var(--color-acento);
            }

            .hardware-nav-link.is-active .chip {
                background-color: var(--color-acento);
                color: var(--color-sobre-acento);
                font-weight: 700;
            }

            /* Contenido principal del catálogo */
            .hardware-content {
                display: flex;
                flex-direction: column;
                gap: 1.5rem;
            }

            /* Barra de herramientas y búsqueda */
            .hardware-toolbar {
                display: flex;
                flex-direction: column;
                gap: 1rem;
            }

            .hardware-search-form {
                display: flex;
                flex-wrap: wrap;
                gap: 0.65rem;
                align-items: center;
                width: 100%;
            }

            .hardware-search-wrapper {
                position: relative;
                flex-grow: 1;
                min-width: 240px;
            }

            .hardware-search-icon {
                position: absolute;
                left: 1rem;
                top: 50%;
                transform: translateY(-50%);
                color: var(--color-texto-2);
                pointer-events: none;
            }

            .hardware-search-input {
                width: 100%;
                box-sizing: border-box;
                padding: 0.7rem 1rem 0.7rem 2.5rem;
                font-size: 1rem;
                border: 1px solid var(--color-borde-control);
                border-radius: var(--radio-md);
                background: var(--color-superficie);
                color: var(--color-texto);
                min-height: 44px;
                outline: none;
                transition: border-color 0.15s ease, box-shadow 0.15s ease;
            }

            .hardware-search-input:focus {
                border-color: var(--color-foco);
                box-shadow: 0 0 0 2px var(--color-acento-suave);
            }

            .hardware-status-line {
                display: flex;
                align-items: center;
                justify-content: space-between;
                flex-wrap: wrap;
                gap: 0.75rem;
                font-size: 0.9rem;
                color: var(--color-texto-2);
                padding: 0.25rem 0.2rem;
            }

            /* Cuadrícula de tarjetas */
            .hardware-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
                gap: 1.75rem;
                align-items: stretch;
            }

            .hardware-card {
                display: flex;
                flex-direction: column;
                height: 100%;
                background: var(--color-superficie);
                border: 1px solid var(--color-borde);
                border-radius: var(--radio-lg);
                overflow: hidden;
                box-shadow: var(--sombra-1);
                transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
            }

            .hardware-card:hover {
                transform: translateY(-3px);
                box-shadow: var(--sombra-2);
                border-color: var(--color-borde-control);
            }

            .hardware-card-media {
                position: relative;
                aspect-ratio: 1 / 1;
                width: 100%;
                background: var(--color-superficie-sutil);
                overflow: hidden;
                border-bottom: 1px solid var(--color-borde);
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .hardware-card-img {
                width: 100%;
                height: 100%;
                object-fit: cover;
                display: block;
                transition: transform 0.3s ease;
            }

            .hardware-card:hover .hardware-card-img {
                transform: scale(1.02);
            }

            .hardware-badge-category {
                position: absolute;
                top: 0.75rem;
                left: 0.75rem;
                background: rgba(31, 32, 41, 0.88);
                color: #F0F0F5;
                border: 1px solid rgba(255, 255, 255, 0.2);
                font-size: 0.75rem;
                padding: 0.25rem 0.6rem;
                border-radius: var(--radio-full);
                backdrop-filter: blur(4px);
                font-weight: 600;
            }

            .hardware-badge-featured {
                position: absolute;
                top: 0.75rem;
                right: 0.75rem;
                background: var(--color-acento);
                color: var(--color-sobre-acento);
                font-weight: 700;
                font-size: 0.75rem;
                padding: 0.25rem 0.6rem;
                border-radius: var(--radio-full);
                box-shadow: var(--sombra-1);
                display: inline-flex;
                align-items: center;
                gap: 0.25rem;
            }

            .hardware-card-body {
                padding: 1.25rem;
                display: flex;
                flex-direction: column;
                flex-grow: 1;
            }

            .hardware-card-title {
                font-size: 1.15rem;
                font-weight: 700;
                color: var(--color-texto-1);
                margin: 0 0 0.6rem 0;
                line-height: 1.35;
            }

            .hardware-card-desc {
                font-size: 0.9rem;
                color: var(--color-texto-2);
                line-height: 1.55;
                margin: 0 0 1.25rem 0;
                flex-grow: 1;
                white-space: pre-line;
            }

            .hardware-card-price {
                font-size: 0.88rem;
                color: var(--color-texto-2);
                padding-top: 0.75rem;
                margin-bottom: 1rem;
                border-top: 1px dashed var(--color-borde);
                display: flex;
                align-items: baseline;
                justify-content: space-between;
            }

            .hardware-card-price strong {
                color: var(--color-texto-1);
                font-weight: 700;
                font-size: 1rem;
            }

            .hardware-card-actions {
                display: flex;
                flex-direction: column;
                gap: 0.5rem;
                margin-top: auto;
            }

            /* Responsive: Pantallas móviles y tablets compactas (<= 860px) */
            @media (max-width: 860px) {
                .hardware-layout {
                    grid-template-columns: 1fr;
                    gap: 1.25rem;
                    margin-top: 1rem;
                }

                .hardware-sidebar {
                    position: static;
                    top: auto;
                }

                .hardware-sidebar-card {
                    padding: 0.65rem;
                    border-radius: var(--radio-md);
                }

                .hardware-sidebar-header {
                    display: none; /* Ahorro de espacio vertical en móvil */
                }

                .hardware-nav-list {
                    flex-direction: row;
                    flex-wrap: nowrap;
                    overflow-x: auto;
                    padding-bottom: 0.35rem;
                    gap: 0.5rem;
                    -webkit-overflow-scrolling: touch;
                    scrollbar-width: thin;
                }

                .hardware-nav-link {
                    flex-shrink: 0;
                    white-space: nowrap;
                    padding: 0.45rem 0.85rem;
                    border-radius: var(--radio-full);
                    background: var(--color-superficie-sutil);
                    border: 1px solid var(--color-borde);
                }

                .hardware-nav-link.is-active {
                    background: var(--color-acento-suave);
                    border-color: var(--color-acento);
                    color: var(--color-enlace);
                }

                .hardware-grid {
                    grid-template-columns: 1fr;
                    gap: 1.25rem;
                }
            }
        </style>
    </x-slot:styles>

    <div class="contenedor seccion">
        <!-- 1. Cabecera y Contexto -->
        <header style="margin-bottom: 2rem;">
            <!-- Migas de pan / retorno -->
            <div style="margin-bottom: 1.25rem;">
                <a href="/{{ $langQueryOnly }}" style="font-size: 0.9rem; text-decoration: none; color: var(--color-texto-2); display: inline-flex; align-items: center; gap: 0.35rem; transition: color 0.15s ease;">
                    <svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                    <span>{{ __('portal.hardware.back_to_home') }}</span>
                </a>
            </div>

            <h1 style="font-size: clamp(2rem, 3.5vw, 2.75rem); font-weight: 800; color: var(--color-texto-1); margin-bottom: 0.75rem; letter-spacing: -0.02em;">
                {{ __('portal.hardware.heading') }}
            </h1>

            <p class="lead" style="margin-bottom: 1.25rem; font-size: 1.15rem; line-height: 1.6; max-width: 860px; color: var(--color-texto-2);">
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

        <!-- 2. Catálogo: Menú lateral accesible (izquierda en escritorio / arriba en móvil) + Contenido -->
        <div class="hardware-layout">
            <!-- Menú Lateral Accesible -->
            <aside class="hardware-sidebar" aria-label="{{ __('portal.hardware.categories_label') }}">
                <div class="hardware-sidebar-card">
                    <div class="hardware-sidebar-header">
                        <h2 class="hardware-sidebar-title">{{ __('portal.hardware.sidebar_heading') }}</h2>
                        <span class="chip chip-neutro" style="font-size: 0.75rem;" title="{{ __('portal.hardware.all_categories') }}">
                            {{ $totalArticulos }}
                        </span>
                    </div>

                    <nav aria-label="{{ __('portal.hardware.categories_label') }}">
                        <ul class="hardware-nav-list" role="list">
                            <!-- Enlace: Todas las categorías -->
                            <li class="hardware-nav-item">
                                <a
                                    href="{{ url('/hardware') . $queryTodas }}"
                                    class="hardware-nav-link {{ empty($slugActual) ? 'is-active' : '' }}"
                                    @if(empty($slugActual)) aria-current="page" @endif
                                >
                                    <span>{{ __('portal.hardware.all_categories') }}</span>
                                    <span class="chip {{ empty($slugActual) ? 'chip-correcto' : 'chip-neutro' }}" style="font-size: 0.75rem; padding: 0.15rem 0.5rem;">
                                        {{ $totalArticulos }}
                                    </span>
                                </a>
                            </li>

                            <!-- Enlace por cada categoría activa -->
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
                                <li class="hardware-nav-item">
                                    <a
                                        href="{{ $catHref }}"
                                        class="hardware-nav-link {{ $esActiva ? 'is-active' : '' }}"
                                        @if($esActiva) aria-current="page" @endif
                                    >
                                        <span>{{ $cat->translated_name }}</span>
                                        <span class="chip {{ $esActiva ? 'chip-correcto' : 'chip-neutro' }}" style="font-size: 0.75rem; padding: 0.15rem 0.5rem;">
                                            {{ $cat->items_count }}
                                        </span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </nav>
                </div>
            </aside>

            <!-- Contenido Principal: Buscador y Artículos -->
            <section class="hardware-content" id="catalogo-hardware" aria-label="{{ __('portal.hardware.heading') }}">
                <!-- Buscador por texto y resumen de filtros -->
                <div class="hardware-toolbar">
                    <form method="GET" action="{{ url('/hardware') }}" class="hardware-search-form" role="search" aria-label="{{ __('portal.hardware.filters_heading') }}">
                        @if($slugActual)
                            <input type="hidden" name="categoria" value="{{ $slugActual }}">
                        @endif
                        @if($currentLang !== 'es')
                            <input type="hidden" name="lang" value="{{ $currentLang }}">
                        @endif
                        <div class="hardware-search-wrapper">
                            <svg aria-hidden="true" class="hardware-search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="11" cy="11" r="8"></circle>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                            </svg>
                            <input
                                type="search"
                                name="q"
                                value="{{ $busqueda }}"
                                placeholder="{{ __('portal.hardware.search_placeholder') }}"
                                aria-label="{{ __('portal.hardware.search_placeholder') }}"
                                class="hardware-search-input"
                            >
                        </div>
                        <button type="submit" class="btn btn-primario" style="min-height: 44px; padding: 0.65rem 1.25rem;">
                            {{ __('portal.hardware.search_btn') }}
                        </button>
                        @if($busqueda !== '' || !empty($slugActual))
                            <a href="{{ url('/hardware') . $langQueryOnly }}" class="btn btn-secundario" style="min-height: 44px; display: inline-flex; align-items: center; padding: 0.65rem 1rem;">
                                {{ __('portal.hardware.clear_btn') }}
                            </a>
                        @endif
                    </form>

                    <!-- Línea de estado y recuento de resultados -->
                    <div class="hardware-status-line">
                        <span>
                            {{ __('portal.hardware.showing_count', ['count' => $articulos->count(), 'total' => $totalArticulos]) }}
                        </span>
                        @if($categoriaSeleccionada)
                            <span class="chip chip-neutro">
                                {{ $categoriaSeleccionada->translated_name }}
                            </span>
                        @endif
                    </div>
                </div>

                <!-- 3. Cuadrícula Responsive de Tarjetas o Estado Vacío -->
                @if($articulos->isEmpty())
                    <!-- Estado Vacío -->
                    <div class="tarjeta" style="padding: 4rem 2rem; text-align: center; background: var(--color-superficie); border-radius: var(--radio-lg); max-width: 600px; margin: 2rem auto; border: 1px solid var(--color-borde);">
                        <div style="font-size: 3rem; margin-bottom: 1rem; line-height: 1;" aria-hidden="true">
                            🔍
                        </div>
                        <h2 style="font-size: 1.4rem; font-weight: 700; color: var(--color-texto-1); margin-bottom: 0.75rem;">
                            {{ __('portal.hardware.empty_title') }}
                        </h2>
                        <p style="color: var(--color-texto-2); font-size: 0.95rem; line-height: 1.6; margin-bottom: 1.75rem;">
                            {{ __('portal.hardware.empty_text') }}
                        </p>
                        <div>
                            <a href="{{ url('/hardware') . $langQueryOnly }}" class="btn btn-primario" style="min-height: 44px; display: inline-flex; align-items: center;">
                                {{ __('portal.hardware.reset_filters') }}
                            </a>
                        </div>
                    </div>
                @else
                    <div class="hardware-grid">
                        @foreach($articulos as $item)
                            <article class="hardware-card" aria-labelledby="item-titulo-{{ $item->id }}">
                                <!-- Imagen cuadrada 1:1 con badges -->
                                <div class="hardware-card-media">
                                    <img
                                        src="{{ $item->image_url }}"
                                        alt="{{ $item->translated_name }}"
                                        width="400"
                                        height="400"
                                        loading="lazy"
                                        class="hardware-card-img"
                                    >

                                    <!-- Badge de categoría -->
                                    @if($item->category)
                                        <span class="hardware-badge-category">
                                            {{ $item->category->translated_name }}
                                        </span>
                                    @endif

                                    <!-- Badge de destacado / recomendado -->
                                    @if($item->is_featured)
                                        <span class="hardware-badge-featured">
                                            ★ {{ __('portal.hardware.featured_badge') }}
                                        </span>
                                    @endif
                                </div>

                                <!-- Cuerpo de la tarjeta -->
                                <div class="hardware-card-body">
                                    <h3 id="item-titulo-{{ $item->id }}" class="hardware-card-title">
                                        {{ $item->translated_name }}
                                    </h3>

                                    <p class="hardware-card-desc">{{ $item->translated_description }}</p>

                                    <!-- Indicador de precio orientativo -->
                                    <div class="hardware-card-price">
                                        <span>{{ __('portal.hardware.last_price_label') }}:</span>
                                        @if($item->last_price !== null)
                                            <strong>~{{ $item->formatted_price }}</strong>
                                        @else
                                            <span style="font-style: italic; color: var(--color-texto-3);">{{ __('portal.hardware.price_unknown') }}</span>
                                        @endif
                                    </div>

                                    <!-- Botonera de acción -->
                                    <div class="hardware-card-actions">
                                        <a
                                            href="{{ $item->buy_url }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="btn btn-primario"
                                            style="width: 100%; text-decoration: none; min-height: 44px; display: inline-flex; align-items: center; justify-content: center; gap: 0.45rem;"
                                            aria-label="{{ __('portal.hardware.btn_buy') }}: {{ $item->translated_name }} ({{ __('portal.hardware.opens_new_tab') }})"
                                        >
                                            <span>{{ __('portal.hardware.btn_buy') }}</span>
                                            <svg aria-hidden="true" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                                                <polyline points="15 3 21 3 21 9"></polyline>
                                                <line x1="10" y1="14" x2="21" y2="3"></line>
                                            </svg>
                                            <span class="sr-only">({{ __('portal.hardware.opens_new_tab') }})</span>
                                        </a>

                                        @if(!empty($item->guide_url))
                                            <a
                                                href="{{ $item->guide_url }}"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="btn btn-secundario"
                                                style="width: 100%; text-decoration: none; min-height: 44px; display: inline-flex; align-items: center; justify-content: center; gap: 0.45rem;"
                                                aria-label="{{ __('portal.hardware.btn_guide') }}: {{ $item->translated_name }} ({{ __('portal.hardware.opens_new_tab') }})"
                                            >
                                                <span>{{ __('portal.hardware.btn_guide') }}</span>
                                                <svg aria-hidden="true" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
                                                    <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
                                                </svg>
                                                <span class="sr-only">({{ __('portal.hardware.opens_new_tab') }})</span>
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>
        </div>
    </div>
</x-layout>
