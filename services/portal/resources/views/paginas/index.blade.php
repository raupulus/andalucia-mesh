@php
    $currentLang = app()->getLocale();
    $langQuery = $currentLang !== 'es' ? '?lang=' . $currentLang : '';
@endphp

<x-layout :title="__('portal.pages.index_title')" :description="__('portal.pages.index_subtitle')">
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
                            <a href="{{ route('paginas.show', ['slug' => $pagina->slug]) }}{{ $langQuery }}" tabindex="-1" aria-hidden="true" style="display: block; width: 100%; height: 100%;">
                                <img src="{{ $pagina->cover_image_url }}" alt="{{ $pagina->title }}" loading="lazy" width="280" height="190">
                            </a>
                        </div>

                        <!-- Franja Andalucía (verde/blanca/verde) -->
                        <div class="franja-andalucia-vertical" aria-hidden="true">
                            <span class="franja-andalucia-verde"></span>
                            <span class="franja-andalucia-blanca"></span>
                            <span class="franja-andalucia-verde"></span>
                        </div>

                        <!-- Bloque de contenido -->
                        <div class="tarjeta-pagina-cuerpo">
                            <div>
                                <h2 style="font-size: 1.45rem; font-weight: 700; margin-bottom: 0.35rem; line-height: 1.3;">
                                    <a href="{{ route('paginas.show', ['slug' => $pagina->slug]) }}{{ $langQuery }}" style="color: inherit; text-decoration: none;">
                                        {{ $pagina->title }}
                                    </a>
                                </h2>
                                <time datetime="{{ $pagina->created_at->toIso8601String() }}" style="display: block; color: var(--color-texto-3); font-size: 0.85rem; margin-bottom: 0.75rem;">
                                    {{ $pagina->formatted_date }}
                                </time>
                                <p style="color: var(--color-texto-2); font-size: 0.98rem; line-height: 1.6; margin-bottom: 0.75rem;">
                                    {{ $pagina->description }}
                                </p>
                            </div>

                            <!-- Keywords como badges verdes alineadas abajo a la derecha -->
                            <div class="tarjeta-pagina-footer">
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
