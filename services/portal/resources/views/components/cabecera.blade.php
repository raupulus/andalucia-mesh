@props(['activa' => null])

<header class="cabecera" style="background-color: var(--color-superficie); position: sticky; top: 0; z-index: 100;">
    <div class="contenedor" style="display: flex; align-items: center; justify-content: space-between; height: 4.5rem;">
        <!-- Logotipo e Identidad -->
        <a href="/" style="display: flex; align-items: center; gap: 0.75rem; text-decoration: none; color: var(--color-texto);">
            <img src="{{ asset('img/logo.png') }}" alt="{{ config('proyecto.nombre') }}" style="width: 2.5rem; height: 2.5rem; object-fit: contain; flex-shrink: 0;" width="40" height="40">

            <div>
                <span style="font-weight: 700; font-size: 1.15rem; display: block; line-height: 1.2;">{{ config('proyecto.nombre') }}</span>
                <span style="font-size: 0.75rem; color: var(--color-texto-2); display: block;">Red Meshtastic Regional</span>
            </div>
        </a>

        <!-- Navegación de Escritorio -->
        <nav class="nav-escritorio" aria-label="Navegación principal" style="display: flex; align-items: center; gap: 1.25rem;">
            @foreach(config('proyecto.navegacion') as $item)
                @if($item['url'] !== '/')
                    <a href="{{ $item['url'] }}" style="text-decoration: none; font-size: 0.95rem; font-weight: 500; color: {{ request()->is(trim($item['url'], '/')) ? 'var(--color-enlace)' : 'var(--color-texto)' }}; border-bottom: 2px solid {{ request()->is(trim($item['url'], '/')) ? 'var(--color-enlace)' : 'transparent' }}; padding-bottom: 0.25rem;">
                        {{ $item['titulo'] }}
                    </a>
                @endif
            @endforeach

            <!-- Selector de Tema (Claro / Oscuro) -->
            <button id="btn-tema" type="button" aria-label="Cambiar tema claro u oscuro" style="background: none; border: 1px solid var(--color-borde-control); border-radius: var(--radio-md); min-width: 44px; min-height: 44px; cursor: pointer; color: var(--color-texto); display: flex; align-items: center; justify-content: center;">
                <span id="icono-tema" aria-hidden="true">🌓</span>
            </button>
        </nav>

        <!-- Botón Menú Móvil -->
        <div class="nav-movil-toggle">
            <button id="btn-menu-movil" type="button" aria-expanded="false" aria-controls="menu-movil" aria-label="Abrir menú de navegación" style="background: none; border: 1px solid var(--color-borde-control); border-radius: var(--radio-md); min-width: 44px; min-height: 44px; cursor: pointer; color: var(--color-texto); display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                <span aria-hidden="true">☰</span>
            </button>
        </div>
    </div>

    <!-- Menú Desplegable Móvil -->
    <div id="menu-movil" style="display: none; background-color: var(--color-superficie); padding: 1.25rem; border-top: 1px solid var(--color-borde);">
        <nav aria-label="Navegación móvil" style="display: flex; flex-direction: column; gap: 0.5rem;">
            @foreach(config('proyecto.navegacion') as $item)
                <a href="{{ $item['url'] }}" style="text-decoration: none; font-size: 1.05rem; font-weight: 500; color: {{ request()->is(trim($item['url'], '/')) ? 'var(--color-enlace)' : 'var(--color-texto)' }}; padding: 0.65rem 0.5rem; border-radius: var(--radio-sm);">
                    {{ $item['titulo'] }}
                </a>
            @endforeach

            <!-- Cambio de tema en móvil -->
            <div style="border-top: 1px solid var(--color-borde); padding-top: 1rem; margin-top: 0.5rem; display: flex; align-items: center; justify-content: space-between;">
                <span style="font-size: 0.95rem; color: var(--color-texto-2); font-weight: 500;">Apariencia visual:</span>
                <button type="button" class="btn-tema-movil btn btn-secundario" style="padding: 0.5rem 0.85rem; font-size: 0.9rem; min-height: 44px; display: inline-flex; align-items: center; gap: 0.5rem;">
                    <span>Cambiar tema</span> <span aria-hidden="true">🌓</span>
                </button>
            </div>
        </nav>
    </div>

    <!-- Borde inferior con la bandera de Andalucía (verde, blanca, verde) -->
    <div class="borde-bandera-andalucia" aria-hidden="true">
        <span class="linea-verde"></span>
        <span class="linea-blanca"></span>
        <span class="linea-verde"></span>
    </div>
</header>
