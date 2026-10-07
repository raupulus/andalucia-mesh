@props(['activa' => null])

<header class="cabecera" style="background-color: var(--color-superficie); border-bottom: 1px solid var(--color-borde); position: sticky; top: 0; z-index: 100;">
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
            <button id="btn-tema" type="button" aria-label="Cambiar tema claro u oscuro" style="background: none; border: 1px solid var(--color-borde-control); border-radius: var(--radio-md); padding: 0.4rem 0.6rem; cursor: pointer; color: var(--color-texto); display: flex; align-items: center; justify-content: center;">
                <span id="icono-tema" aria-hidden="true">🌓</span>
            </button>
        </nav>

        <!-- Botón Menú Móvil -->
        <div class="nav-movil-toggle" style="display: none;">
            <button id="btn-menu-movil" type="button" aria-expanded="false" aria-controls="menu-movil" aria-label="Abrir menú de navegación" style="background: none; border: 1px solid var(--color-borde-control); border-radius: var(--radio-md); padding: 0.5rem; cursor: pointer; color: var(--color-texto);">
                ☰
            </button>
        </div>
    </div>

    <!-- Menú Desplegable Móvil -->
    <div id="menu-movil" style="display: none; background-color: var(--color-superficie); border-bottom: 1px solid var(--color-borde); padding: 1rem 1.25rem;">
        <nav aria-label="Navegación móvil" style="display: flex; flex-direction: column; gap: 0.75rem;">
            @foreach(config('proyecto.navegacion') as $item)
                <a href="{{ $item['url'] }}" style="text-decoration: none; font-size: 1rem; font-weight: 500; color: var(--color-texto); padding: 0.5rem 0;">
                    {{ $item['titulo'] }}
                </a>
            @endforeach
        </nav>
    </div>
</header>
