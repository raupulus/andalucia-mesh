@php
    $currentLang = app()->getLocale();
    $langQuery = $currentLang !== 'es' ? '?lang=' . $currentLang : '';
@endphp

<x-layout
    :title="__('portal.tools.meta_title', ['name' => config('proyecto.nombre')])"
    :description="__('portal.tools.meta_description')"
    :image="asset('img/og/og-herramientas.webp')"
    :keywords="__('portal.tools.meta_keywords')"
>
    <x-slot:styles>
        <style>
            .herramientas-hero {
                padding: 3rem 0 2rem;
                border-bottom: 1px solid var(--color-borde);
                background: linear-gradient(180deg, var(--color-superficie) 0%, var(--color-fondo) 100%);
            }

            .herramientas-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
                gap: 2rem;
                margin-top: 2.5rem;
                margin-bottom: 3.5rem;
            }

            .tarjeta-herramienta {
                display: flex;
                flex-direction: column;
                background-color: var(--color-superficie);
                border: 1px solid var(--color-borde);
                border-radius: var(--radio-lg);
                overflow: hidden;
                text-decoration: none;
                color: inherit;
                transition: transform var(--duracion-transicion) ease, box-shadow var(--duracion-transicion) ease, border-color var(--duracion-transicion) ease;
                height: 100%;
            }

            .tarjeta-herramienta:hover,
            .tarjeta-herramienta:focus-visible {
                transform: translateY(-4px);
                border-color: var(--color-enlace);
                box-shadow: var(--sombra-2);
                outline: none;
            }

            .tarjeta-herramienta-img-wrap {
                aspect-ratio: 16 / 9;
                background-color: var(--color-acento-suave);
                position: relative;
                overflow: hidden;
                border-bottom: 1px solid var(--color-borde);
            }

            .tarjeta-herramienta-img {
                width: 100%;
                height: 100%;
                object-fit: cover;
                display: block;
                transition: transform 0.3s ease;
            }

            .tarjeta-herramienta:hover .tarjeta-herramienta-img {
                transform: scale(1.03);
            }

            .tarjeta-herramienta-badge {
                position: absolute;
                top: 0.75rem;
                right: 0.75rem;
                background: rgba(14, 20, 27, 0.85);
                backdrop-filter: blur(6px);
                color: #34d399;
                border: 1px solid rgba(52, 211, 153, 0.4);
                padding: 0.25rem 0.65rem;
                border-radius: var(--radio-full);
                font-size: 0.75rem;
                font-weight: 700;
                letter-spacing: 0.03em;
                text-transform: uppercase;
            }

            .tarjeta-herramienta-cuerpo {
                padding: 1.5rem;
                display: flex;
                flex-direction: column;
                flex-grow: 1;
            }

            .tarjeta-herramienta-titulo {
                font-size: 1.3rem;
                font-weight: 700;
                color: var(--color-texto);
                margin: 0 0 0.65rem;
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 0.5rem;
            }

            .tarjeta-herramienta-flecha {
                color: var(--color-enlace);
                font-size: 1.25rem;
                transition: transform var(--duracion-transicion) ease;
            }

            .tarjeta-herramienta:hover .tarjeta-herramienta-flecha {
                transform: translateX(4px);
            }

            .tarjeta-herramienta-desc {
                font-size: 0.92rem;
                color: var(--color-texto-2);
                line-height: 1.55;
                margin-bottom: 1.25rem;
                flex-grow: 1;
            }

            .tarjeta-herramienta-accion {
                font-size: 0.9rem;
                font-weight: 600;
                color: var(--color-enlace);
                display: inline-flex;
                align-items: center;
                gap: 0.35rem;
            }

            .caja-sugerencia-herramientas {
                background: var(--color-superficie);
                border: 1px solid var(--color-borde);
                border-left: 4px solid var(--color-acento);
                border-radius: var(--radio-md);
                padding: 1.5rem;
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                justify-content: space-between;
                gap: 1.25rem;
                margin-bottom: 3.5rem;
            }
        </style>
    </x-slot:styles>

    <!-- Sección de Cabecera / Hero -->
    <section class="herramientas-hero">
        <div class="contenedor">
            <!-- Migas de pan semánticas -->
            <nav aria-label="Ruta de navegación" style="margin-bottom: 1rem;">
                <ol style="display: flex; align-items: center; gap: 0.5rem; list-style: none; padding: 0; margin: 0; font-size: 0.85rem; color: var(--color-texto-2);">
                    <li>
                        <a href="/{{ $langQuery }}" style="color: var(--color-texto-2); text-decoration: none;">{{ __('portal.nav.home') }}</a>
                    </li>
                    <li aria-hidden="true" style="color: var(--color-borde);">/</li>
                    <li>
                        <span style="color: var(--color-texto); font-weight: 600;" aria-current="page">{{ __('portal.tools.breadcrumb') }}</span>
                    </li>
                </ol>
            </nav>

            <div style="max-width: 800px;">
                <h1 style="font-size: clamp(2rem, 4vw, 2.75rem); font-weight: 800; line-height: 1.2; margin: 0 0 1rem; color: var(--color-texto);">
                    {{ __('portal.tools.title') }}
                </h1>
                <p style="font-size: 1.12rem; color: var(--color-texto-2); line-height: 1.6; margin: 0 0 1.25rem;">
                    {{ __('portal.tools.lead') }}
                </p>
                <div style="margin-top: 1.25rem;">
                    <x-botones-compartir :url="url()->current()" :titulo="__('portal.tools.title')" :texto="__('portal.tools.lead')" />
                </div>
            </div>
        </div>
    </section>

    <!-- Parrilla de Herramientas -->
    <div class="contenedor">
        <div class="herramientas-grid">
            @foreach($herramientas as $item)
                @php
                    $urlHerramienta = $item['url'];
                    if (str_starts_with($urlHerramienta, '/') && $currentLang !== 'es') {
                        $urlHerramienta .= (str_contains($urlHerramienta, '?') ? '&' : '?') . 'lang=' . $currentLang;
                    }
                @endphp
                <article>
                    <a href="{{ $urlHerramienta }}" class="tarjeta-herramienta" id="tool-card-{{ $item['id'] }}">
                        <div class="tarjeta-herramienta-img-wrap">
                            <img
                                src="{{ $item['imagen'] }}"
                                alt="Vista ilustrativa de {{ $item['titulo'] }}"
                                width="640"
                                height="360"
                                class="tarjeta-herramienta-img"
                                loading="lazy"
                            >
                            @if(!empty($item['badge']))
                                <span class="tarjeta-herramienta-badge">{{ $item['badge'] }}</span>
                            @endif
                        </div>
                        <div class="tarjeta-herramienta-cuerpo">
                            <h2 class="tarjeta-herramienta-titulo">
                                <span>{{ $item['titulo'] }}</span>
                                <span class="tarjeta-herramienta-flecha" aria-hidden="true">→</span>
                            </h2>
                            <p class="tarjeta-herramienta-desc">
                                {{ $item['descripcion'] }}
                            </p>
                            <span class="tarjeta-herramienta-accion">
                                <span>{{ __('portal.tools.explore_tool') }}</span>
                                <svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                    <polyline points="12 5 19 12 12 19"></polyline>
                                </svg>
                            </span>
                        </div>
                    </a>
                </article>
            @endforeach
        </div>

        <!-- Banner de Sugerencias / Nuevas Utilidades -->
        <div class="caja-sugerencia-herramientas">
            <div style="flex: 1 1 300px;">
                <h3 style="font-size: 1.15rem; font-weight: 700; margin: 0 0 0.35rem; color: var(--color-texto);">
                    {{ __('portal.tools.suggest_title') }}
                </h3>
                <p style="font-size: 0.9rem; color: var(--color-texto-2); margin: 0; line-height: 1.5;">
                    {{ __('portal.tools.suggest_desc', ['name' => config('proyecto.nombre')]) }}
                </p>
            </div>
            <div>
                <a href="/sugerencias{{ $langQuery }}" class="btn btn-primario" style="text-decoration: none; white-space: nowrap;">
                    {{ __('portal.tools.suggest_button') }}
                </a>
            </div>
        </div>
    </div>
</x-layout>
