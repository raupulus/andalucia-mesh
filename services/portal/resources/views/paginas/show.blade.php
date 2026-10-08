@php
    $currentLang = app()->getLocale();
    $langQuery = $currentLang !== 'es' ? '?lang=' . $currentLang : '';
@endphp

<x-layout :title="$pagina->title" :description="$pagina->description" :image="$pagina->cover_image_url ?: asset('img/og/og-paginas.webp')" :keywords="$pagina->keywords">
    <div class="contenedor seccion">
        <article class="pagina-detalle" style="max-width: 860px; margin: 0 auto;">
            <!-- Migas de pan / Volver -->
            <nav aria-label="Navegación secundaria" style="margin-bottom: 1.5rem;">
                <a href="{{ route('paginas.index') }}{{ $langQuery }}" class="enlace-retorno" style="display: inline-flex; align-items: center; gap: 0.5rem; color: var(--color-texto-2); font-size: 0.95rem; text-decoration: none;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <polyline points="15 18 9 12 15 6"></polyline>
                    </svg>
                    <span>{{ __('portal.pages.btn_back') }}</span>
                </a>
            </nav>

            <!-- Imagen de portada arriba -->
            <div class="pagina-detalle-img-wrapper">
                <img src="{{ $pagina->cover_image_url }}" alt="{{ $pagina->title }}" width="860" height="440" loading="eager">
            </div>

            <!-- Cabecera de la página: Título, fecha, descripción -->
            <header style="margin-bottom: 2.5rem; border-bottom: 1px solid var(--color-borde); padding-bottom: 1.75rem;">
                <h1 style="font-size: 2.35rem; font-weight: 800; line-height: 1.25; margin-bottom: 0.85rem; letter-spacing: -0.02em;">
                    {{ $pagina->title }}
                </h1>

                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.25rem;">
                    <time datetime="{{ $pagina->created_at->toIso8601String() }}" style="display: inline-flex; align-items: center; gap: 0.4rem; color: var(--color-texto-3); font-size: 0.92rem;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                        <span>{{ __('portal.pages.published_on', ['date' => $pagina->formatted_date]) }}</span>
                    </time>

                    @if(!empty($pagina->keywords))
                        <div style="display: flex; gap: 0.4rem; flex-wrap: wrap;" aria-label="{{ __('portal.pages.keywords_label') }}">
                            @foreach($pagina->keywords as $kw)
                                <span class="badge-keyword">{{ $kw }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>

                <p class="lead" style="font-size: 1.2rem; color: var(--color-texto-2); line-height: 1.6; margin-bottom: 0;">
                    {{ $pagina->description }}
                </p>
            </header>

            <!-- Contenido completo formateado (Markdown procesado de forma segura) -->
            <div class="prose" style="line-height: 1.8; font-size: 1.05rem;">
                {!! $contenidoHtml !!}
            </div>

            <!-- Footer del artículo con keywords y vuelta al listado -->
            @if(!empty($pagina->keywords))
                <div style="margin-top: 3rem; padding-top: 1.5rem; border-top: 1px solid var(--color-borde); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
                    <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                        <span style="font-size: 0.85rem; font-weight: 600; color: var(--color-texto-3);">{{ __('portal.pages.keywords_label') }}</span>
                        @foreach($pagina->keywords as $kw)
                            <span class="badge-keyword">{{ $kw }}</span>
                        @endforeach
                    </div>
                    <a href="{{ route('paginas.index') }}{{ $langQuery }}" class="tarjeta-pagina-accion">
                        <span>← {{ __('portal.pages.btn_back') }}</span>
                    </a>
                </div>
            @endif

            <!-- Banner de Emergencias -->
            <div style="margin-top: 3.5rem;">
                <x-aviso-emergencias />
            </div>
        </article>
    </div>

    <!-- Metadatos estructurados Schema.org Article para SEO -->
    @php
        $articleSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $pagina->title,
            'description' => $pagina->description,
            'image' => $pagina->cover_image_url,
            'datePublished' => $pagina->created_at->toIso8601String(),
            'dateModified' => $pagina->updated_at->toIso8601String(),
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => url()->current(),
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => config('proyecto.nombre'),
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => asset('img/logo-512.png'),
                ],
            ],
        ];
    @endphp
    <x-slot:scripts>
        <script type="application/ld+json">
        {!! json_encode($articleSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
        </script>
    </x-slot:scripts>
</x-layout>
