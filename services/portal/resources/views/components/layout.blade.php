<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="ltr" data-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php
        $currentLocale = app()->getLocale();
        $localeMap = [
            'es' => 'es_ES',
            'en' => 'en_GB',
            'pt' => 'pt_PT',
        ];
        $ogLocale = $localeMap[$currentLocale] ?? 'es_ES';
        $pageTitle = str_contains($title ?? '', config('proyecto.nombre')) ? $title : (($title ? $title . ' — ' : '') . config('proyecto.nombre'));
        $metaDesc = $description ?? config('proyecto.seo.descripcion_defecto');

        $rawImage = !empty($image) ? $image : config('proyecto.seo.imagen_defecto');
        $metaImage = (str_starts_with($rawImage, 'http://') || str_starts_with($rawImage, 'https://'))
            ? $rawImage
            : asset(ltrim($rawImage, '/'));

        $imagePath = parse_url($metaImage, PHP_URL_PATH) ?? '';
        $metaImageType = match (strtolower(pathinfo($imagePath, PATHINFO_EXTENSION))) {
            'webp' => 'image/webp',
            'jpg', 'jpeg' => 'image/jpeg',
            'svg' => 'image/svg+xml',
            default => 'image/png',
        };
        $baseUrl = url()->current();
        $cleanParams = (request()->has('lang') && request()->query('lang') !== 'es') ? ['lang' => request()->query('lang')] : [];
        $canonicalUrl = !empty($canonical)
            ? $canonical
            : (empty($cleanParams) ? $baseUrl : $baseUrl . '?' . http_build_query($cleanParams));
    @endphp

    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $metaDesc }}">
    @if(!empty($keywords))
    <meta name="keywords" content="{{ is_array($keywords) ? implode(', ', $keywords) : $keywords }}">
    @endif
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
    <link rel="canonical" href="{{ $canonicalUrl }}">

    <!-- Etiquetas multilingües hreflang (SEO internacional) -->
    <link rel="alternate" hreflang="es" href="{{ $baseUrl }}">
    <link rel="alternate" hreflang="en" href="{{ $baseUrl }}?lang=en">
    <link rel="alternate" hreflang="pt" href="{{ $baseUrl }}?lang=pt">
    <link rel="alternate" hreflang="x-default" href="{{ $baseUrl }}">

    <!-- Open Graph & Metadatos Sociales -->
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ config('proyecto.nombre') }}">
    <meta property="og:title" content="{{ $title ?? config('proyecto.nombre') }}">
    <meta property="og:description" content="{{ $metaDesc }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:image" content="{{ $metaImage }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:type" content="{{ $metaImageType }}">
    <meta property="og:image:alt" content="{{ config('proyecto.nombre') }} — {{ $metaDesc }}">
    <meta property="og:locale" content="{{ $ogLocale }}">
    @foreach($localeMap as $locKey => $locVal)
        @if($locKey !== $currentLocale)
            <meta property="og:locale:alternate" content="{{ $locVal }}">
        @endif
    @endforeach

    <!-- Twitter Card -->
    @php
        $twitterHandle = str_starts_with((string) config('autoria.nick'), '@') ? config('autoria.nick') : '@' . config('autoria.nick');
    @endphp
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:site" content="{{ $twitterHandle }}">
    <meta name="twitter:creator" content="{{ $twitterHandle }}">
    <meta name="twitter:title" content="{{ $title ?? config('proyecto.nombre') }}">
    <meta name="twitter:description" content="{{ $metaDesc }}">
    <meta name="twitter:image" content="{{ $metaImage }}">
    <meta name="twitter:image:alt" content="{{ config('proyecto.nombre') }} — {{ $metaDesc }}">

    <!-- Datos estructurados Schema.org (JSON-LD) -->
    @php
        $siteSchema = [
            '@context' => 'https://schema.org',
            '@graph' => [
                [
                    '@type' => 'WebSite',
                    '@id' => url('/') . '/#website',
                    'url' => url('/'),
                    'name' => config('proyecto.nombre'),
                    'description' => config('proyecto.seo.descripcion_defecto'),
                    'inLanguage' => $currentLocale,
                ],
                [
                    '@type' => 'Organization',
                    '@id' => url('/') . '/#organization',
                    'name' => config('proyecto.nombre'),
                    'url' => url('/'),
                    'logo' => [
                        '@type' => 'ImageObject',
                        'url' => asset('img/logo-512.png'),
                        'width' => 512,
                        'height' => 512,
                    ],
                ],
            ],
        ];

        // Añadir BreadcrumbList si no es la portada principal
        $pathSegments = array_values(array_filter(explode('/', trim(request()->path(), '/'))));
        if (!empty($pathSegments)) {
            $breadcrumbElements = [
                [
                    '@type' => 'ListItem',
                    'position' => 1,
                    'name' => __('portal.nav.home'),
                    'item' => url('/'),
                ]
            ];
            $currentAcc = '';
            foreach ($pathSegments as $idx => $segment) {
                $currentAcc .= '/' . $segment;
                $isLast = ($idx === count($pathSegments) - 1);
                $name = $isLast ? ($title ?? ucfirst($segment)) : ucfirst($segment);
                $breadcrumbElements[] = [
                    '@type' => 'ListItem',
                    'position' => $idx + 2,
                    'name' => $name,
                    'item' => url($currentAcc),
                ];
            }
            $siteSchema['@graph'][] = [
                '@type' => 'BreadcrumbList',
                '@id' => url()->current() . '/#breadcrumb',
                'itemListElement' => $breadcrumbElements,
            ];
        }
    @endphp
    <script type="application/ld+json">
    {!! json_encode($siteSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
    </script>

    <!-- Favicon & Color de tema -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <meta name="theme-color" content="#2C2D3C" media="(prefers-color-scheme: dark)">
    <meta name="theme-color" content="#FFFFFF" media="(prefers-color-scheme: light)">

    <!-- Evitar parpadeo de modo oscuro antes de la primera pintura (FOUC) - Por defecto oscuro salvo light forzado -->
    <script>
        (function() {
            try {
                var theme = localStorage.getItem('snm_theme');
                if (!theme) {
                    theme = window.matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark';
                }
                document.documentElement.setAttribute('data-theme', theme);
            } catch (e) {}
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{ $styles ?? '' }}
    @stack('head')
</head>
<body>
    <a href="#contenido-principal" class="salto-accesible">{{ __('portal.skip_to_content') }}</a>

    <x-cabecera />

    <main id="contenido-principal" class="flex-grow">
        {{ $slot }}
    </main>

    @if(!($sinPie ?? false))
    <x-pie />
    @endif

    <x-aviso-pruebas />

    {{ $scripts ?? '' }}
    @stack('scripts')
</body>
</html>
