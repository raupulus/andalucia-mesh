<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ str_contains($title ?? '', config('proyecto.nombre')) ? $title : (($title ? $title . ' — ' : '') . config('proyecto.nombre')) }}</title>
    <meta name="description" content="{{ $description ?? config('proyecto.seo.descripcion_defecto') }}">

    <!-- Open Graph & Metadatos Sociales -->
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ config('proyecto.nombre') }}">
    <meta property="og:title" content="{{ $title ?? config('proyecto.nombre') }}">
    <meta property="og:description" content="{{ $description ?? config('proyecto.seo.descripcion_defecto') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ asset(config('proyecto.seo.imagen_defecto')) }}">
    <link rel="canonical" href="{{ url()->current() }}">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $title ?? config('proyecto.nombre') }}">
    <meta name="twitter:description" content="{{ $description ?? config('proyecto.seo.descripcion_defecto') }}">
    <meta name="twitter:image" content="{{ asset(config('proyecto.seo.imagen_defecto')) }}">

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
</head>
<body>
    <a href="#contenido-principal" class="salto-accesible">{{ __('portal.skip_to_content') }}</a>

    <x-cabecera />

    <main id="contenido-principal" class="flex-grow">
        {{ $slot }}
    </main>

    <x-pie />

    {{ $scripts ?? '' }}
</body>
</html>
