@props(['activa' => null])

@php
    $navKeyMap = [
        '/' => 'portal.nav.home',
        '/configura-tu-nodo' => 'portal.nav.node_setup',
        '/conecta-tu-gateway' => 'portal.nav.gateway',
        '/rankings' => 'portal.nav.rankings',
        '/alertas' => 'portal.nav.alerts',
        '/bots' => 'portal.nav.bots',
        '/configurador' => 'portal.nav.configurator',
        '/revisa-tu-nodo' => 'portal.nav.node_check',
        '/hardware' => 'portal.nav.hardware',
        '/sugerencias' => 'portal.nav.suggestions',
        '/faq' => 'portal.nav.faq',
    ];
    $currentLang = app()->getLocale();
@endphp

<header class="cabecera" style="background-color: var(--color-superficie); position: sticky; top: 0; z-index: 100;">
    <div class="contenedor" style="display: flex; align-items: center; justify-content: space-between; height: 4.5rem;">
        <!-- Logotipo e Identidad -->
        <a href="{{ $currentLang !== 'es' ? '/?lang=' . $currentLang : '/' }}" style="display: flex; align-items: center; gap: 0.75rem; text-decoration: none; color: var(--color-texto);">
            <img src="{{ asset('img/logo.png') }}" alt="" aria-hidden="true" style="width: 2.5rem; height: 2.5rem; object-fit: contain; flex-shrink: 0;" width="40" height="40">

            <div>
                <span style="font-weight: 700; font-size: 1.15rem; display: block; line-height: 1.2;">{{ config('proyecto.nombre') }}</span>
                <span style="font-size: 0.75rem; color: var(--color-texto-2); display: block;">{{ __('portal.regional_network') }}</span>
            </div>
        </a>

        <!-- Navegación de Escritorio -->
        <nav class="nav-escritorio" aria-label="Navegación principal" style="display: flex; align-items: center; gap: 1.25rem;">
            @foreach(config('proyecto.navegacion') as $item)
                @if($item['url'] !== '/')
                    @php
                        $tituloNav = isset($navKeyMap[$item['url']]) ? __($navKeyMap[$item['url']]) : $item['titulo'];
                        $enlaceNav = $currentLang !== 'es' ? $item['url'] . '?lang=' . $currentLang : $item['url'];
                    @endphp
                    <a href="{{ $enlaceNav }}" style="text-decoration: none; font-size: 0.95rem; font-weight: 500; color: {{ request()->is(trim($item['url'], '/')) ? 'var(--color-enlace)' : 'var(--color-texto)' }}; border-bottom: 2px solid {{ request()->is(trim($item['url'], '/')) ? 'var(--color-enlace)' : 'transparent' }}; padding-bottom: 0.25rem;">
                        {{ $tituloNav }}
                    </a>
                @endif
            @endforeach

            <!-- Menú Desplegable Extras (Configurador, Revisa tu nodo, Hardware, API/Websockets, Sugerencias, FAQ) -->
            @php
                $extrasActivo = request()->is('configurador*') || request()->is('revisa-tu-nodo*') || request()->is('hardware*') || request()->is('api*') || request()->is('sugerencias*') || request()->is('faq*');
            @endphp
            <div class="dropdown-extras-wrapper">
                <button
                    type="button"
                    id="btn-extras-nav"
                    class="btn-extras-toggle"
                    aria-expanded="false"
                    aria-haspopup="true"
                    aria-label="{{ __('portal.nav.extras') }}"
                    style="background: none; border: none; font-family: inherit; font-size: 0.95rem; font-weight: 500; cursor: pointer; display: inline-flex; align-items: center; gap: 0.35rem; color: {{ $extrasActivo ? 'var(--color-enlace)' : 'var(--color-texto)' }}; border-bottom: 2px solid {{ $extrasActivo ? 'var(--color-enlace)' : 'transparent' }}; padding-bottom: 0.25rem; padding-left: 0; padding-right: 0;"
                >
                    <span>{{ __('portal.nav.extras') }}</span>
                    <svg aria-hidden="true" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </button>
                <div
                    id="dropdown-extras-nav"
                    class="dropdown-extras-menu"
                    role="menu"
                    aria-label="{{ __('portal.nav.extras') }}"
                >
                    <a href="/configurador{{ $currentLang !== 'es' ? '?lang=' . $currentLang : '' }}" class="dropdown-extras-item {{ request()->is('configurador*') ? 'activo' : '' }}" role="menuitem">
                        <svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--color-acento);">
                            <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                            <line x1="8" y1="21" x2="16" y2="21"></line>
                            <line x1="12" y1="17" x2="12" y2="21"></line>
                        </svg>
                        <span>{{ __('portal.nav.configurator') }}</span>
                    </a>
                    <a href="/revisa-tu-nodo{{ $currentLang !== 'es' ? '?lang=' . $currentLang : '' }}" class="dropdown-extras-item {{ request()->is('revisa-tu-nodo*') ? 'activo' : '' }}" role="menuitem">
                        <svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--color-acento);">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                        <span>{{ __('portal.nav.node_check') }}</span>
                    </a>
                    <a href="/hardware{{ $currentLang !== 'es' ? '?lang=' . $currentLang : '' }}" class="dropdown-extras-item {{ request()->is('hardware*') ? 'activo' : '' }}" role="menuitem">
                        <svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--color-acento);">
                            <rect x="4" y="4" width="16" height="16" rx="2"></rect>
                            <rect x="9" y="9" width="6" height="6"></rect>
                            <line x1="9" y1="1" x2="9" y2="4"></line>
                            <line x1="15" y1="1" x2="15" y2="4"></line>
                            <line x1="9" y1="20" x2="9" y2="23"></line>
                            <line x1="15" y1="20" x2="15" y2="23"></line>
                            <line x1="20" y1="9" x2="23" y2="9"></line>
                            <line x1="20" y1="14" x2="23" y2="14"></line>
                            <line x1="1" y1="9" x2="4" y2="9"></line>
                            <line x1="1" y1="14" x2="4" y2="14"></line>
                        </svg>
                        <span>{{ __('portal.nav.hardware') }}</span>
                    </a>
                    <a href="/api{{ $currentLang !== 'es' ? '?lang=' . $currentLang : '' }}" class="dropdown-extras-item {{ request()->is('api*') ? 'activo' : '' }}" role="menuitem">
                        <svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--color-acento);">
                            <polyline points="16 18 22 12 16 6"></polyline>
                            <polyline points="8 6 2 12 8 18"></polyline>
                        </svg>
                        <span>{{ __('portal.nav.api') }}</span>
                    </a>
                    <a href="/sugerencias{{ $currentLang !== 'es' ? '?lang=' . $currentLang : '' }}" class="dropdown-extras-item {{ request()->is('sugerencias*') ? 'activo' : '' }}" role="menuitem">
                        <svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--color-acento);">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                        </svg>
                        <span>{{ __('portal.nav.suggestions') }}</span>
                    </a>
                    <a href="/faq{{ $currentLang !== 'es' ? '?lang=' . $currentLang : '' }}" class="dropdown-extras-item {{ request()->is('faq*') ? 'activo' : '' }}" role="menuitem">
                        <svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--color-acento);">
                            <circle cx="12" cy="12" r="10"></circle>
                            <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                            <line x1="12" y1="17" x2="12.01" y2="17"></line>
                        </svg>
                        <span>{{ __('portal.nav.faq') }}</span>
                    </a>
                </div>
            </div>

            <!-- Selector de Idioma: solo icono redondo con bandera (Andalucía para ES) (RN-48) -->
            <div class="selector-idioma-wrapper">
                <button
                    type="button"
                    id="btn-idioma-nav"
                    class="btn-idioma-redondo"
                    aria-expanded="false"
                    aria-haspopup="true"
                    aria-label="{{ __('portal.nav.select_language') }}"
                    title="{{ __('portal.nav.select_language') }}"
                >
                    <x-icono-bandera :idioma="$currentLang" :tamano="24" />
                </button>
                <div
                    id="dropdown-idioma-nav"
                    class="dropdown-idiomas"
                    role="menu"
                    aria-label="{{ __('portal.nav.select_language') }}"
                >
                    <a href="{{ request()->fullUrlWithQuery(['lang' => 'es']) }}" class="dropdown-idioma-item {{ $currentLang === 'es' ? 'activo' : '' }}" role="menuitem" data-lang="es">
                        <x-icono-bandera idioma="es" :tamano="20" />
                        <span>Español</span>
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['lang' => 'pt']) }}" class="dropdown-idioma-item {{ $currentLang === 'pt' ? 'activo' : '' }}" role="menuitem" data-lang="pt">
                        <x-icono-bandera idioma="pt" :tamano="20" />
                        <span>Português</span>
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['lang' => 'en']) }}" class="dropdown-idioma-item {{ $currentLang === 'en' ? 'activo' : '' }}" role="menuitem" data-lang="en">
                        <x-icono-bandera idioma="en" :tamano="20" />
                        <span>English</span>
                    </a>
                </div>
            </div>

            <!-- Selector de Tema (Claro / Oscuro) -->
            <button id="btn-tema" type="button" aria-label="{{ __('portal.nav.theme_toggle') }}" style="background: none; border: 1px solid var(--color-borde-control); border-radius: var(--radio-md); min-width: 44px; min-height: 44px; cursor: pointer; color: var(--color-texto); display: flex; align-items: center; justify-content: center;">
                <span id="icono-tema" aria-hidden="true">🌓</span>
            </button>
        </nav>

        <!-- Botón Menú Móvil -->
        <div class="nav-movil-toggle">
            <!-- Selector de Idioma en Cabecera Móvil: solo icono redondo -->
            <div class="selector-idioma-wrapper">
                <button
                    type="button"
                    id="btn-idioma-movil"
                    class="btn-idioma-redondo"
                    aria-expanded="false"
                    aria-haspopup="true"
                    aria-label="{{ __('portal.nav.select_language') }}"
                    title="{{ __('portal.nav.select_language') }}"
                >
                    <x-icono-bandera :idioma="$currentLang" :tamano="24" />
                </button>
                <div
                    id="dropdown-idioma-movil"
                    class="dropdown-idiomas"
                    role="menu"
                    aria-label="{{ __('portal.nav.select_language') }}"
                >
                    <a href="{{ request()->fullUrlWithQuery(['lang' => 'es']) }}" class="dropdown-idioma-item {{ $currentLang === 'es' ? 'activo' : '' }}" role="menuitem" data-lang="es">
                        <x-icono-bandera idioma="es" :tamano="20" />
                        <span>Español</span>
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['lang' => 'pt']) }}" class="dropdown-idioma-item {{ $currentLang === 'pt' ? 'activo' : '' }}" role="menuitem" data-lang="pt">
                        <x-icono-bandera idioma="pt" :tamano="20" />
                        <span>Português</span>
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['lang' => 'en']) }}" class="dropdown-idioma-item {{ $currentLang === 'en' ? 'activo' : '' }}" role="menuitem" data-lang="en">
                        <x-icono-bandera idioma="en" :tamano="20" />
                        <span>English</span>
                    </a>
                </div>
            </div>

            <button id="btn-menu-movil" type="button" aria-expanded="false" aria-controls="menu-movil" aria-label="{{ __('portal.nav.mobile_menu') }}" style="background: none; border: 1px solid var(--color-borde-control); border-radius: var(--radio-md); min-width: 44px; min-height: 44px; cursor: pointer; color: var(--color-texto); display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                <span aria-hidden="true">☰</span>
            </button>
        </div>
    </div>

    <!-- Menú Desplegable Móvil -->
    <div id="menu-movil" style="display: none; background-color: var(--color-superficie); padding: 1.25rem; border-top: 1px solid var(--color-borde);">
        <nav aria-label="Navegación móvil" style="display: flex; flex-direction: column; gap: 0.5rem;">
            @foreach(config('proyecto.navegacion') as $item)
                @php
                    $tituloNav = isset($navKeyMap[$item['url']]) ? __($navKeyMap[$item['url']]) : $item['titulo'];
                    $enlaceNav = $currentLang !== 'es' ? $item['url'] . '?lang=' . $currentLang : $item['url'];
                @endphp
                <a href="{{ $enlaceNav }}" style="text-decoration: none; font-size: 1.05rem; font-weight: 500; color: {{ request()->is(trim($item['url'], '/')) ? 'var(--color-enlace)' : 'var(--color-texto)' }}; padding: 0.65rem 0.5rem; border-radius: var(--radio-sm);">
                    {{ $tituloNav }}
                </a>
            @endforeach

            <!-- Grupo Extras en menú móvil -->
            <div style="border-top: 1px solid var(--color-borde); padding-top: 0.75rem; margin-top: 0.25rem;">
                <span style="display: block; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: var(--color-texto-2); padding: 0.25rem 0.5rem 0.5rem;">
                    {{ __('portal.nav.extras') }}
                </span>
                <a href="/configurador{{ $currentLang !== 'es' ? '?lang=' . $currentLang : '' }}" style="display: flex; align-items: center; gap: 0.75rem; text-decoration: none; font-size: 1.05rem; font-weight: 500; color: {{ request()->is('configurador*') ? 'var(--color-enlace)' : 'var(--color-texto)' }}; padding: 0.65rem 0.5rem; border-radius: var(--radio-sm);">
                    <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--color-acento);">
                        <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                        <line x1="8" y1="21" x2="16" y2="21"></line>
                        <line x1="12" y1="17" x2="12" y2="21"></line>
                    </svg>
                    <span>{{ __('portal.nav.configurator') }}</span>
                </a>
                <a href="/revisa-tu-nodo{{ $currentLang !== 'es' ? '?lang=' . $currentLang : '' }}" style="display: flex; align-items: center; gap: 0.75rem; text-decoration: none; font-size: 1.05rem; font-weight: 500; color: {{ request()->is('revisa-tu-nodo*') ? 'var(--color-enlace)' : 'var(--color-texto)' }}; padding: 0.65rem 0.5rem; border-radius: var(--radio-sm);">
                    <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--color-acento);">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <span>{{ __('portal.nav.node_check') }}</span>
                </a>
                <a href="/hardware{{ $currentLang !== 'es' ? '?lang=' . $currentLang : '' }}" style="display: flex; align-items: center; gap: 0.75rem; text-decoration: none; font-size: 1.05rem; font-weight: 500; color: {{ request()->is('hardware*') ? 'var(--color-enlace)' : 'var(--color-texto)' }}; padding: 0.65rem 0.5rem; border-radius: var(--radio-sm);">
                    <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--color-acento);">
                        <rect x="4" y="4" width="16" height="16" rx="2"></rect>
                        <rect x="9" y="9" width="6" height="6"></rect>
                        <line x1="9" y1="1" x2="9" y2="4"></line>
                        <line x1="15" y1="1" x2="15" y2="4"></line>
                        <line x1="9" y1="20" x2="9" y2="23"></line>
                        <line x1="15" y1="20" x2="15" y2="23"></line>
                        <line x1="20" y1="9" x2="23" y2="9"></line>
                        <line x1="20" y1="14" x2="23" y2="14"></line>
                        <line x1="1" y1="9" x2="4" y2="9"></line>
                        <line x1="1" y1="14" x2="4" y2="14"></line>
                    </svg>
                    <span>{{ __('portal.nav.hardware') }}</span>
                </a>
                <a href="/api{{ $currentLang !== 'es' ? '?lang=' . $currentLang : '' }}" style="display: flex; align-items: center; gap: 0.75rem; text-decoration: none; font-size: 1.05rem; font-weight: 500; color: {{ request()->is('api*') ? 'var(--color-enlace)' : 'var(--color-texto)' }}; padding: 0.65rem 0.5rem; border-radius: var(--radio-sm);">
                    <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--color-acento);">
                        <polyline points="16 18 22 12 16 6"></polyline>
                        <polyline points="8 6 2 12 8 18"></polyline>
                    </svg>
                    <span>{{ __('portal.nav.api') }}</span>
                </a>
                <a href="/sugerencias{{ $currentLang !== 'es' ? '?lang=' . $currentLang : '' }}" style="display: flex; align-items: center; gap: 0.75rem; text-decoration: none; font-size: 1.05rem; font-weight: 500; color: {{ request()->is('sugerencias*') ? 'var(--color-enlace)' : 'var(--color-texto)' }}; padding: 0.65rem 0.5rem; border-radius: var(--radio-sm);">
                    <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--color-acento);">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                    </svg>
                    <span>{{ __('portal.nav.suggestions') }}</span>
                </a>
                <a href="/faq{{ $currentLang !== 'es' ? '?lang=' . $currentLang : '' }}" style="display: flex; align-items: center; gap: 0.75rem; text-decoration: none; font-size: 1.05rem; font-weight: 500; color: {{ request()->is('faq*') ? 'var(--color-enlace)' : 'var(--color-texto)' }}; padding: 0.65rem 0.5rem; border-radius: var(--radio-sm);">
                    <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--color-acento);">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                    </svg>
                    <span>{{ __('portal.nav.faq') }}</span>
                </a>
            </div>

            <!-- Cambio de idioma en menú móvil: orden español, portugués, inglés -->
            <div style="border-top: 1px solid var(--color-borde); padding-top: 1rem; margin-top: 0.5rem; display: flex; align-items: center; justify-content: space-between;">
                <span style="font-size: 0.95rem; color: var(--color-texto-2); font-weight: 500;">{{ __('portal.nav.select_language') }}:</span>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <a href="{{ request()->fullUrlWithQuery(['lang' => 'es']) }}" class="dropdown-idioma-item btn-idioma-redondo {{ $currentLang === 'es' ? 'activo' : '' }}" data-lang="es" title="Español" aria-label="Español" style="text-decoration: none;">
                        <x-icono-bandera idioma="es" :tamano="22" />
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['lang' => 'pt']) }}" class="dropdown-idioma-item btn-idioma-redondo {{ $currentLang === 'pt' ? 'activo' : '' }}" data-lang="pt" title="Português" aria-label="Português" style="text-decoration: none;">
                        <x-icono-bandera idioma="pt" :tamano="22" />
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['lang' => 'en']) }}" class="dropdown-idioma-item btn-idioma-redondo {{ $currentLang === 'en' ? 'activo' : '' }}" data-lang="en" title="English" aria-label="English" style="text-decoration: none;">
                        <x-icono-bandera idioma="en" :tamano="22" />
                    </a>
                </div>
            </div>

            <!-- Cambio de tema en móvil -->
            <div style="border-top: 1px solid var(--color-borde); padding-top: 1rem; margin-top: 0.5rem; display: flex; align-items: center; justify-content: space-between;">
                <span style="font-size: 0.95rem; color: var(--color-texto-2); font-weight: 500;">{{ __('portal.nav.visual_appearance') }}</span>
                <button type="button" class="btn-tema-movil btn btn-secundario" style="padding: 0.5rem 0.85rem; font-size: 0.9rem; min-height: 44px; display: inline-flex; align-items: center; gap: 0.5rem;">
                    <span>{{ __('portal.nav.change_theme') }}</span> <span aria-hidden="true">🌓</span>
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
