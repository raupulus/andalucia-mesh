@php
    $currentLang = app()->getLocale();
    $langQuery = $currentLang !== 'es' ? '?lang=' . $currentLang : '';
@endphp

<x-layout :title="__('portal.pages.index_title')" :description="__('portal.pages.index_subtitle')" :image="asset('img/og/og-paginas.webp')">
    <div class="contenedor seccion">
        <!-- Encabezado de la sección -->
        <header style="margin-bottom: 2.5rem; border-bottom: 1px solid var(--color-borde); padding-bottom: 1.5rem;">
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
                <div>
                    <h1 style="font-size: 2.25rem; font-weight: 800; margin-bottom: 0.5rem; letter-spacing: -0.02em;">
                        {{ __('portal.pages.index_title') }}
                    </h1>
                    <p class="lead" style="margin-bottom: 0; color: var(--color-texto-2); font-size: 1.15rem;">
                        {{ __('portal.pages.index_subtitle') }}
                    </p>
                </div>
                <a href="/{{ $langQuery }}" class="btn btn-secundario" style="font-size: 0.9rem;">
                    {{ __('portal.hardware.back_to_home') }}
                </a>
            </div>
        </header>

        <!-- Listado de páginas en tarjetas horizontales ocupando todo el ancho -->
        @if($paginas->isEmpty())
            <div class="tarjeta" style="padding: 3.5rem 2rem; text-align: center; background: var(--color-superficie); border: 1px solid var(--color-borde); border-radius: var(--radio-lg);">
                <div style="width: 56px; height: 56px; border-radius: 50%; background: var(--color-superficie-sutil); display: flex; align-items: center; justify-content: center; margin: 0 auto 1.25rem auto;">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="var(--color-texto-3)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                        <polyline points="10 9 9 9 8 9"></polyline>
                    </svg>
                </div>
                <h2 style="font-size: 1.35rem; font-weight: 700; margin-bottom: 0.5rem;">
                    {{ __('portal.pages.no_pages') }}
                </h2>
                <p style="color: var(--color-texto-2); max-width: 480px; margin: 0 auto 1.5rem auto;">
                    {{ __('portal.pages.empty_desc') }}
                </p>
                <a href="/{{ $langQuery }}" class="btn btn-primario">
                    {{ __('portal.hardware.back_to_home') }}
                </a>
            </div>
        @else
            <div class="lista-paginas-horizontal" role="feed" aria-label="{{ __('portal.pages.index_title') }}">
                @foreach($paginas as $pagina)
                    <article class="tarjeta-pagina-horizontal">
                        <!-- Imagen a la izquierda -->
                        <div class="tarjeta-pagina-img-wrapper">
                            <a href="{{ route('paginas.show', ['slug' => $pagina->slug]) }}{{ $langQuery }}" class="tarjeta-pagina-img-link" tabindex="-1" aria-hidden="true">
                                <img src="{{ $pagina->cover_image_url }}" alt="" loading="lazy" width="320" height="220">
                            </a>
                        </div>

                        <!-- Franja Andalucía (verde/blanco/verde con degradado centrado) -->
                        <div class="franja-andalucia-vertical" aria-hidden="true"></div>

                        <!-- Bloque de contenido -->
                        <div class="tarjeta-pagina-cuerpo">
                            <div class="tarjeta-pagina-cabecera">
                                <h2 class="tarjeta-pagina-titulo">
                                    <a href="{{ route('paginas.show', ['slug' => $pagina->slug]) }}{{ $langQuery }}">
                                        {{ $pagina->title }}
                                    </a>
                                </h2>
                                <time datetime="{{ $pagina->created_at->toIso8601String() }}" class="tarjeta-pagina-fecha">
                                    {{ $pagina->formatted_date }}
                                </time>
                                <p class="tarjeta-pagina-desc">
                                    {{ $pagina->description }}
                                </p>
                            </div>

                            <!-- Pie de tarjeta: Enlace a la izquierda y badges verdes a la derecha -->
                            <div class="tarjeta-pagina-footer">
                                <a href="{{ route('paginas.show', ['slug' => $pagina->slug]) }}{{ $langQuery }}" class="tarjeta-pagina-accion">
                                    <span>{{ __('portal.pages.read_article') }}</span>
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <line x1="5" y1="12" x2="19" y2="12"></line>
                                        <polyline points="12 5 19 12 12 19"></polyline>
                                    </svg>
                                </a>

                                <div class="tarjeta-pagina-keywords" aria-label="{{ __('portal.pages.keywords_label') }}">
                                    @if(!empty($pagina->keywords))
                                        @foreach($pagina->keywords as $kw)
                                            <span class="badge-keyword">{{ $kw }}</span>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif

        <!-- Banner de Emergencias -->
        <div style="margin-top: 3.5rem;">
            <x-aviso-emergencias />
        </div>
    </div>
</x-layout>
