@props(['activa' => null])

@php
    $currentLang = app()->getLocale();
    $langQuery = $currentLang !== 'es' ? '?lang=' . $currentLang : '';

    // Detección de sección activa para resaltar los botones de la barra de navegación
    $tuNodoActivo = request()->is('configura-tu-nodo*') || request()->is('configurador*') || request()->is('mi-cobertura*') || request()->is('conecta-tu-gateway*') || request()->is('revisa-tu-nodo*');
    $statsActivo = request()->is('alertas*') || request()->is('rankings*') || request()->is('routers*');
    $docActivo = request()->is('api*') || request()->is('bots*') || request()->is('faq*') || request()->is('hardware*');
    $extrasActivo = request()->is('como-se-gestiona*') || request()->is('proyecto*') || request()->is('firmware*') || request()->is('quien-lo-impulsa*') || request()->is('sugerencias*');
@endphp

<header class="cabecera" style="background-color: var(--color-superficie); position: sticky; top: 0; z-index: 100;">
    <div class="contenedor" style="display: flex; align-items: center; justify-content: space-between; height: 4.5rem;">
        <!-- Logotipo e Identidad -->
        <a href="/{{ $langQuery }}" style="display: flex; align-items: center; gap: 0.75rem; text-decoration: none; color: var(--color-texto);">
            <img src="{{ asset('img/logo.png') }}" alt="" aria-hidden="true" style="width: 2.5rem; height: 2.5rem; object-fit: contain; flex-shrink: 0;" width="40" height="40">

            <div>
                <span style="font-weight: 700; font-size: 1.15rem; display: block; line-height: 1.2;">{{ config('proyecto.nombre') }}</span>
                <span style="font-size: 0.75rem; color: var(--color-texto-2); display: block;">{{ __('portal.regional_network') }}</span>
            </div>
        </a>

        <!-- Navegación de Escritorio -->
        <nav class="nav-escritorio" aria-label="Navegación principal" style="display: flex; align-items: center; gap: 1.15rem;">
            <!-- 1. Mapa (enlace directo) -->
            <a href="/mapa{{ $langQuery }}" style="text-decoration: none; font-size: 0.95rem; font-weight: 500; color: {{ request()->is('mapa*') ? 'var(--color-enlace)' : 'var(--color-texto)' }}; border-bottom: 2px solid {{ request()->is('mapa*') ? 'var(--color-enlace)' : 'transparent' }}; padding-bottom: 0.25rem;">
                {{ __('portal.nav.map') }}
            </a>

            <!-- 2. Tu Nodo (Menú Desplegable) -->
            <div class="dropdown-nav-wrapper dropdown-extras-wrapper">
                <button
                    type="button"
                    id="btn-tunodo-nav"
                    class="dropdown-nav-toggle btn-extras-toggle"
                    aria-expanded="false"
                    aria-haspopup="true"
                    aria-controls="dropdown-tunodo-nav"
                    aria-label="{{ __('portal.nav.your_node') }}"
                    style="background: none; border: none; font-family: inherit; font-size: 0.95rem; font-weight: 500; cursor: pointer; display: inline-flex; align-items: center; gap: 0.35rem; color: {{ $tuNodoActivo ? 'var(--color-enlace)' : 'var(--color-texto)' }}; border-bottom: 2px solid {{ $tuNodoActivo ? 'var(--color-enlace)' : 'transparent' }}; padding-bottom: 0.25rem; padding-left: 0; padding-right: 0;"
                >
                    <span>{{ __('portal.nav.your_node') }}</span>
                    <svg aria-hidden="true" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </button>
                <div
                    id="dropdown-tunodo-nav"
                    class="dropdown-nav-menu dropdown-extras-menu"
                    role="menu"
                    aria-label="{{ __('portal.nav.your_node') }}"
                >
                    <!-- Configura tu nodo -->
                    <a href="/configura-tu-nodo{{ $langQuery }}" class="dropdown-nav-item dropdown-extras-item {{ request()->is('configura-tu-nodo*') ? 'activo' : '' }}" role="menuitem">
                        <svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--color-acento);">
                            <path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                        <span>{{ __('portal.nav.node_setup') }}</span>
                    </a>
                    <!-- Configurador automático -->
                    <a href="/configurador{{ $langQuery }}" class="dropdown-nav-item dropdown-extras-item {{ request()->is('configurador*') ? 'activo' : '' }}" role="menuitem">
                        <svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--color-acento);">
                            <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                            <line x1="8" y1="21" x2="16" y2="21"></line>
                            <line x1="12" y1="17" x2="12" y2="21"></line>
                        </svg>
                        <span>{{ __('portal.nav.configurator') }}</span>
                    </a>
                    <!-- Mi Cobertura -->
                    <a href="/mi-cobertura{{ $langQuery }}" class="dropdown-nav-item dropdown-extras-item {{ request()->is('mi-cobertura*') ? 'activo' : '' }}" role="menuitem">
                        <svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--color-acento);">
                            <path d="M5 12.55a11 11 0 0 1 14.08 0"></path>
                            <path d="M1.42 9a16 16 0 0 1 21.16 0"></path>
                            <path d="M8.53 16.11a6 6 0 0 1 6.95 0"></path>
                            <line x1="12" y1="20" x2="12.01" y2="20"></line>
                        </svg>
                        <span>{{ __('portal.nav.my_coverage') }}</span>
                    </a>
                    <!-- MQTT -->
                    <a href="/conecta-tu-gateway{{ $langQuery }}" class="dropdown-nav-item dropdown-extras-item {{ request()->is('conecta-tu-gateway*') ? 'activo' : '' }}" role="menuitem">
                        <svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--color-acento);">
                            <rect x="2" y="2" width="20" height="8" rx="2" ry="2"></rect>
                            <rect x="2" y="14" width="20" height="8" rx="2" ry="2"></rect>
                            <line x1="6" y1="6" x2="6.01" y2="6"></line>
                            <line x1="6" y1="18" x2="6.01" y2="18"></line>
                        </svg>
                        <span>{{ __('portal.nav.gateway') }}</span>
                    </a>
                    <!-- Revisa tu nodo -->
                    <a href="/revisa-tu-nodo{{ $langQuery }}" class="dropdown-nav-item dropdown-extras-item {{ request()->is('revisa-tu-nodo*') ? 'activo' : '' }}" role="menuitem">
                        <svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--color-acento);">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                        <span>{{ __('portal.nav.node_check') }}</span>
                    </a>
                </div>
            </div>

            <!-- 3. Estadísticas (Menú Desplegable) -->
            <div class="dropdown-nav-wrapper dropdown-extras-wrapper">
                <button
                    type="button"
                    id="btn-stats-nav"
                    class="dropdown-nav-toggle btn-extras-toggle"
                    aria-expanded="false"
                    aria-haspopup="true"
                    aria-controls="dropdown-stats-nav"
                    aria-label="{{ __('portal.nav.stats') }}"
                    style="background: none; border: none; font-family: inherit; font-size: 0.95rem; font-weight: 500; cursor: pointer; display: inline-flex; align-items: center; gap: 0.35rem; color: {{ $statsActivo ? 'var(--color-enlace)' : 'var(--color-texto)' }}; border-bottom: 2px solid {{ $statsActivo ? 'var(--color-enlace)' : 'transparent' }}; padding-bottom: 0.25rem; padding-left: 0; padding-right: 0;"
                >
                    <span>{{ __('portal.nav.stats') }}</span>
                    <svg aria-hidden="true" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </button>
                <div
                    id="dropdown-stats-nav"
                    class="dropdown-nav-menu dropdown-extras-menu"
                    role="menu"
                    aria-label="{{ __('portal.nav.stats') }}"
                >
                    <!-- Alertas -->
                    <a href="/alertas{{ $langQuery }}" class="dropdown-nav-item dropdown-extras-item {{ request()->is('alertas*') ? 'activo' : '' }}" role="menuitem">
                        <svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--color-acento);">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                        </svg>
                        <span>{{ __('portal.nav.alerts') }}</span>
                    </a>
                    <!-- Rankings -->
                    <a href="/rankings{{ $langQuery }}" class="dropdown-nav-item dropdown-extras-item {{ request()->is('rankings*') ? 'activo' : '' }}" role="menuitem">
                        <svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--color-acento);">
                            <line x1="18" y1="20" x2="18" y2="10"></line>
                            <line x1="12" y1="20" x2="12" y2="4"></line>
                            <line x1="6" y1="20" x2="6" y2="14"></line>
                        </svg>
                        <span>{{ __('portal.nav.rankings') }}</span>
                    </a>
                    <!-- Routers -->
                    <a href="/routers{{ $langQuery }}" class="dropdown-nav-item dropdown-extras-item {{ request()->is('routers*') ? 'activo' : '' }}" role="menuitem">
                        <svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--color-acento);">
                            <circle cx="12" cy="12" r="2"></circle>
                            <path d="M16.24 7.76a6 6 0 0 1 0 8.49m-8.48-.01a6 6 0 0 1 0-8.49m11.31-2.82a10 10 0 0 1 0 14.14m-14.14 0a10 10 0 0 1 0-14.14"></path>
                        </svg>
                        <span>{{ __('portal.nav.routers') }}</span>
                    </a>
                </div>
            </div>

            <!-- 4. Documentación (Menú Desplegable) -->
            <div class="dropdown-nav-wrapper dropdown-extras-wrapper">
                <button
                    type="button"
                    id="btn-doc-nav"
                    class="dropdown-nav-toggle btn-extras-toggle"
                    aria-expanded="false"
                    aria-haspopup="true"
                    aria-controls="dropdown-doc-nav"
                    aria-label="{{ __('portal.nav.documentation') }}"
                    style="background: none; border: none; font-family: inherit; font-size: 0.95rem; font-weight: 500; cursor: pointer; display: inline-flex; align-items: center; gap: 0.35rem; color: {{ $docActivo ? 'var(--color-enlace)' : 'var(--color-texto)' }}; border-bottom: 2px solid {{ $docActivo ? 'var(--color-enlace)' : 'transparent' }}; padding-bottom: 0.25rem; padding-left: 0; padding-right: 0;"
                >
                    <span>{{ __('portal.nav.documentation') }}</span>
                    <svg aria-hidden="true" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </button>
                <div
                    id="dropdown-doc-nav"
                    class="dropdown-nav-menu dropdown-extras-menu"
                    role="menu"
                    aria-label="{{ __('portal.nav.documentation') }}"
                >
                    <!-- API/Websockets -->
                    <a href="/api{{ $langQuery }}" class="dropdown-nav-item dropdown-extras-item {{ request()->is('api*') ? 'activo' : '' }}" role="menuitem">
                        <svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--color-acento);">
                            <polyline points="16 18 22 12 16 6"></polyline>
                            <polyline points="8 6 2 12 8 18"></polyline>
                        </svg>
                        <span>{{ __('portal.nav.api') }}</span>
                    </a>
                    <!-- Bots -->
                    <a href="/bots{{ $langQuery }}" class="dropdown-nav-item dropdown-extras-item {{ request()->is('bots*') ? 'activo' : '' }}" role="menuitem">
                        <svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--color-acento);">
                            <rect x="3" y="11" width="18" height="10" rx="2"></rect>
                            <circle cx="12" cy="5" r="2"></circle>
                            <path d="M12 7v4"></path>
                            <line x1="8" y1="16" x2="8.01" y2="16"></line>
                            <line x1="16" y1="16" x2="16.01" y2="16"></line>
                        </svg>
                        <span>{{ __('portal.nav.bots') }}</span>
                    </a>
                    <!-- FAQ -->
                    <a href="/faq{{ $langQuery }}" class="dropdown-nav-item dropdown-extras-item {{ request()->is('faq*') ? 'activo' : '' }}" role="menuitem">
                        <svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--color-acento);">
                            <circle cx="12" cy="12" r="10"></circle>
                            <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                            <line x1="12" y1="17" x2="12.01" y2="17"></line>
                        </svg>
                        <span>{{ __('portal.nav.faq') }}</span>
                    </a>
                    <!-- Hardware -->
                    <a href="/hardware{{ $langQuery }}" class="dropdown-nav-item dropdown-extras-item {{ request()->is('hardware*') ? 'activo' : '' }}" role="menuitem">
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
                </div>
            </div>

            <!-- 5. Herramientas (enlace directo) -->
            <a href="/herramientas{{ $langQuery }}" style="text-decoration: none; font-size: 0.95rem; font-weight: 500; color: {{ request()->is('herramientas*') ? 'var(--color-enlace)' : 'var(--color-texto)' }}; border-bottom: 2px solid {{ request()->is('herramientas*') ? 'var(--color-enlace)' : 'transparent' }}; padding-bottom: 0.25rem;">
                {{ __('portal.nav.tools') }}
            </a>

            <!-- 6. Páginas (enlace directo) -->
            <a href="/paginas{{ $langQuery }}" style="text-decoration: none; font-size: 0.95rem; font-weight: 500; color: {{ request()->is('paginas*') ? 'var(--color-enlace)' : 'var(--color-texto)' }}; border-bottom: 2px solid {{ request()->is('paginas*') ? 'var(--color-enlace)' : 'transparent' }}; padding-bottom: 0.25rem;">
                {{ __('portal.nav.pages') }}
            </a>

            <!-- 7. Extras (Menú Desplegable) -->
            <div class="dropdown-nav-wrapper dropdown-extras-wrapper">
                <button
                    type="button"
                    id="btn-extras-nav"
                    class="dropdown-nav-toggle btn-extras-toggle"
                    aria-expanded="false"
                    aria-haspopup="true"
                    aria-controls="dropdown-extras-nav"
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
                    class="dropdown-nav-menu dropdown-extras-menu dropdown-derecha"
                    role="menu"
                    aria-label="{{ __('portal.nav.extras') }}"
                >
                    <!-- Cómo se gestiona -->
                    <a href="/como-se-gestiona{{ $langQuery }}" class="dropdown-nav-item dropdown-extras-item {{ request()->is('como-se-gestiona*') ? 'activo' : '' }}" role="menuitem">
                        <svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--color-acento);">
                            <line x1="6" y1="3" x2="6" y2="15"></line>
                            <circle cx="18" cy="6" r="3"></circle>
                            <circle cx="6" cy="18" r="3"></circle>
                            <path d="M18 9a9 9 0 0 1-9 9"></path>
                        </svg>
                        <span>{{ __('portal.nav.how_it_is_managed') }}</span>
                    </a>
                    <!-- El proyecto -->
                    <a href="/proyecto{{ $langQuery }}" class="dropdown-nav-item dropdown-extras-item {{ request()->is('proyecto*') ? 'activo' : '' }}" role="menuitem">
                        <svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--color-acento);">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="16" x2="12" y2="12"></line>
                            <line x1="12" y1="8" x2="12.01" y2="8"></line>
                        </svg>
                        <span>{{ __('portal.nav.project') }}</span>
                    </a>
                    <!-- Firmware y apps -->
                    <a href="/firmware{{ $langQuery }}" class="dropdown-nav-item dropdown-extras-item {{ request()->is('firmware*') ? 'activo' : '' }}" role="menuitem">
                        <svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--color-acento);">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="7 10 12 15 17 10"></polyline>
                            <line x1="12" y1="15" x2="12" y2="3"></line>
                        </svg>
                        <span>{{ __('portal.nav.firmware') }}</span>
                    </a>
                    <!-- Quién lo impulsa -->
                    <a href="/quien-lo-impulsa{{ $langQuery }}" class="dropdown-nav-item dropdown-extras-item {{ request()->is('quien-lo-impulsa*') ? 'activo' : '' }}" role="menuitem">
                        <svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--color-acento);">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                        <span>{{ __('portal.nav.who_drives_it') }}</span>
                    </a>
                    <!-- Sugerencias -->
                    <a href="/sugerencias{{ $langQuery }}" class="dropdown-nav-item dropdown-extras-item {{ request()->is('sugerencias*') ? 'activo' : '' }}" role="menuitem">
                        <svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--color-acento);">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                        </svg>
                        <span>{{ __('portal.nav.suggestions') }}</span>
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

    <!-- Menú Desplegable Móvil Organizado por Bloques -->
    <div id="menu-movil" style="display: none; background-color: var(--color-superficie); padding: 1.25rem; border-top: 1px solid var(--color-borde);">
        <nav aria-label="Navegación móvil" style="display: flex; flex-direction: column; gap: 0.5rem;">
            <!-- 1. Mapa -->
            <a href="/mapa{{ $langQuery }}" style="text-decoration: none; font-size: 1.05rem; font-weight: 500; color: {{ request()->is('mapa*') ? 'var(--color-enlace)' : 'var(--color-texto)' }}; padding: 0.65rem 0.5rem; border-radius: var(--radio-sm);">
                {{ __('portal.nav.map') }}
            </a>

            <!-- 2. Tu Nodo -->
            <div style="border-top: 1px solid var(--color-borde); padding-top: 0.75rem; margin-top: 0.25rem;">
                <span style="display: block; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: var(--color-texto-2); padding: 0.25rem 0.5rem 0.5rem;">
                    {{ __('portal.nav.your_node') }}
                </span>
                <a href="/configura-tu-nodo{{ $langQuery }}" style="display: flex; align-items: center; gap: 0.75rem; text-decoration: none; font-size: 1.05rem; font-weight: 500; color: {{ request()->is('configura-tu-nodo*') ? 'var(--color-enlace)' : 'var(--color-texto)' }}; padding: 0.65rem 0.5rem; border-radius: var(--radio-sm);">
                    <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--color-acento);">
                        <path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                    <span>{{ __('portal.nav.node_setup') }}</span>
                </a>
                <a href="/configurador{{ $langQuery }}" style="display: flex; align-items: center; gap: 0.75rem; text-decoration: none; font-size: 1.05rem; font-weight: 500; color: {{ request()->is('configurador*') ? 'var(--color-enlace)' : 'var(--color-texto)' }}; padding: 0.65rem 0.5rem; border-radius: var(--radio-sm);">
                    <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--color-acento);">
                        <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                        <line x1="8" y1="21" x2="16" y2="21"></line>
                        <line x1="12" y1="17" x2="12" y2="21"></line>
                    </svg>
                    <span>{{ __('portal.nav.configurator') }}</span>
                </a>
                <a href="/mi-cobertura{{ $langQuery }}" style="display: flex; align-items: center; gap: 0.75rem; text-decoration: none; font-size: 1.05rem; font-weight: 500; color: {{ request()->is('mi-cobertura*') ? 'var(--color-enlace)' : 'var(--color-texto)' }}; padding: 0.65rem 0.5rem; border-radius: var(--radio-sm);">
                    <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--color-acento);">
                        <path d="M5 12.55a11 11 0 0 1 14.08 0"></path>
                        <path d="M1.42 9a16 16 0 0 1 21.16 0"></path>
                        <path d="M8.53 16.11a6 6 0 0 1 6.95 0"></path>
                        <line x1="12" y1="20" x2="12.01" y2="20"></line>
                    </svg>
                    <span>{{ __('portal.nav.my_coverage') }}</span>
                </a>
                <a href="/conecta-tu-gateway{{ $langQuery }}" style="display: flex; align-items: center; gap: 0.75rem; text-decoration: none; font-size: 1.05rem; font-weight: 500; color: {{ request()->is('conecta-tu-gateway*') ? 'var(--color-enlace)' : 'var(--color-texto)' }}; padding: 0.65rem 0.5rem; border-radius: var(--radio-sm);">
                    <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--color-acento);">
                        <rect x="2" y="2" width="20" height="8" rx="2" ry="2"></rect>
                        <rect x="2" y="14" width="20" height="8" rx="2" ry="2"></rect>
                        <line x1="6" y1="6" x2="6.01" y2="6"></line>
                        <line x1="6" y1="18" x2="6.01" y2="18"></line>
                    </svg>
                    <span>{{ __('portal.nav.gateway') }}</span>
                </a>
                <a href="/revisa-tu-nodo{{ $langQuery }}" style="display: flex; align-items: center; gap: 0.75rem; text-decoration: none; font-size: 1.05rem; font-weight: 500; color: {{ request()->is('revisa-tu-nodo*') ? 'var(--color-enlace)' : 'var(--color-texto)' }}; padding: 0.65rem 0.5rem; border-radius: var(--radio-sm);">
                    <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--color-acento);">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <span>{{ __('portal.nav.node_check') }}</span>
                </a>
            </div>

            <!-- 3. Estadísticas -->
            <div style="border-top: 1px solid var(--color-borde); padding-top: 0.75rem; margin-top: 0.25rem;">
                <span style="display: block; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: var(--color-texto-2); padding: 0.25rem 0.5rem 0.5rem;">
                    {{ __('portal.nav.stats') }}
                </span>
                <a href="/alertas{{ $langQuery }}" style="display: flex; align-items: center; gap: 0.75rem; text-decoration: none; font-size: 1.05rem; font-weight: 500; color: {{ request()->is('alertas*') ? 'var(--color-enlace)' : 'var(--color-texto)' }}; padding: 0.65rem 0.5rem; border-radius: var(--radio-sm);">
                    <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--color-acento);">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                    </svg>
                    <span>{{ __('portal.nav.alerts') }}</span>
                </a>
                <a href="/rankings{{ $langQuery }}" style="display: flex; align-items: center; gap: 0.75rem; text-decoration: none; font-size: 1.05rem; font-weight: 500; color: {{ request()->is('rankings*') ? 'var(--color-enlace)' : 'var(--color-texto)' }}; padding: 0.65rem 0.5rem; border-radius: var(--radio-sm);">
                    <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--color-acento);">
                        <line x1="18" y1="20" x2="18" y2="10"></line>
                        <line x1="12" y1="20" x2="12" y2="4"></line>
                        <line x1="6" y1="20" x2="6" y2="14"></line>
                    </svg>
                    <span>{{ __('portal.nav.rankings') }}</span>
                </a>
                <a href="/routers{{ $langQuery }}" style="display: flex; align-items: center; gap: 0.75rem; text-decoration: none; font-size: 1.05rem; font-weight: 500; color: {{ request()->is('routers*') ? 'var(--color-enlace)' : 'var(--color-texto)' }}; padding: 0.65rem 0.5rem; border-radius: var(--radio-sm);">
                    <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--color-acento);">
                        <circle cx="12" cy="12" r="2"></circle>
                        <path d="M16.24 7.76a6 6 0 0 1 0 8.49m-8.48-.01a6 6 0 0 1 0-8.49m11.31-2.82a10 10 0 0 1 0 14.14m-14.14 0a10 10 0 0 1 0-14.14"></path>
                    </svg>
                    <span>{{ __('portal.nav.routers') }}</span>
                </a>
            </div>

            <!-- 4. Documentación -->
            <div style="border-top: 1px solid var(--color-borde); padding-top: 0.75rem; margin-top: 0.25rem;">
                <span style="display: block; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: var(--color-texto-2); padding: 0.25rem 0.5rem 0.5rem;">
                    {{ __('portal.nav.documentation') }}
                </span>
                <a href="/api{{ $langQuery }}" style="display: flex; align-items: center; gap: 0.75rem; text-decoration: none; font-size: 1.05rem; font-weight: 500; color: {{ request()->is('api*') ? 'var(--color-enlace)' : 'var(--color-texto)' }}; padding: 0.65rem 0.5rem; border-radius: var(--radio-sm);">
                    <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--color-acento);">
                        <polyline points="16 18 22 12 16 6"></polyline>
                        <polyline points="8 6 2 12 8 18"></polyline>
                    </svg>
                    <span>{{ __('portal.nav.api') }}</span>
                </a>
                <a href="/bots{{ $langQuery }}" style="display: flex; align-items: center; gap: 0.75rem; text-decoration: none; font-size: 1.05rem; font-weight: 500; color: {{ request()->is('bots*') ? 'var(--color-enlace)' : 'var(--color-texto)' }}; padding: 0.65rem 0.5rem; border-radius: var(--radio-sm);">
                    <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--color-acento);">
                        <rect x="3" y="11" width="18" height="10" rx="2"></rect>
                        <circle cx="12" cy="5" r="2"></circle>
                        <path d="M12 7v4"></path>
                        <line x1="8" y1="16" x2="8.01" y2="16"></line>
                        <line x1="16" y1="16" x2="16.01" y2="16"></line>
                    </svg>
                    <span>{{ __('portal.nav.bots') }}</span>
                </a>
                <a href="/faq{{ $langQuery }}" style="display: flex; align-items: center; gap: 0.75rem; text-decoration: none; font-size: 1.05rem; font-weight: 500; color: {{ request()->is('faq*') ? 'var(--color-enlace)' : 'var(--color-texto)' }}; padding: 0.65rem 0.5rem; border-radius: var(--radio-sm);">
                    <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--color-acento);">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                    </svg>
                    <span>{{ __('portal.nav.faq') }}</span>
                </a>
                <a href="/hardware{{ $langQuery }}" style="display: flex; align-items: center; gap: 0.75rem; text-decoration: none; font-size: 1.05rem; font-weight: 500; color: {{ request()->is('hardware*') ? 'var(--color-enlace)' : 'var(--color-texto)' }}; padding: 0.65rem 0.5rem; border-radius: var(--radio-sm);">
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
            </div>

            <!-- 5. Herramientas -->
            <div style="border-top: 1px solid var(--color-borde); padding-top: 0.75rem; margin-top: 0.25rem;">
                <a href="/herramientas{{ $langQuery }}" style="display: flex; align-items: center; gap: 0.75rem; text-decoration: none; font-size: 1.05rem; font-weight: 500; color: {{ request()->is('herramientas*') ? 'var(--color-enlace)' : 'var(--color-texto)' }}; padding: 0.65rem 0.5rem; border-radius: var(--radio-sm);">
                    <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--color-acento);">
                        <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path>
                    </svg>
                    <span>{{ __('portal.nav.tools') }}</span>
                </a>
            </div>

            <!-- 6. Páginas -->
            <div style="border-top: 1px solid var(--color-borde); padding-top: 0.75rem; margin-top: 0.25rem;">
                <a href="/paginas{{ $langQuery }}" style="display: flex; align-items: center; gap: 0.75rem; text-decoration: none; font-size: 1.05rem; font-weight: 500; color: {{ request()->is('paginas*') ? 'var(--color-enlace)' : 'var(--color-texto)' }}; padding: 0.65rem 0.5rem; border-radius: var(--radio-sm);">
                    <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--color-acento);">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                        <polyline points="10 9 9 9 8 9"></polyline>
                    </svg>
                    <span>{{ __('portal.nav.pages') }}</span>
                </a>
            </div>

            <!-- 7. Extras -->
            <div style="border-top: 1px solid var(--color-borde); padding-top: 0.75rem; margin-top: 0.25rem;">
                <span style="display: block; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: var(--color-texto-2); padding: 0.25rem 0.5rem 0.5rem;">
                    {{ __('portal.nav.extras') }}
                </span>
                <a href="/como-se-gestiona{{ $langQuery }}" style="display: flex; align-items: center; gap: 0.75rem; text-decoration: none; font-size: 1.05rem; font-weight: 500; color: {{ request()->is('como-se-gestiona*') ? 'var(--color-enlace)' : 'var(--color-texto)' }}; padding: 0.65rem 0.5rem; border-radius: var(--radio-sm);">
                    <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--color-acento);">
                        <line x1="6" y1="3" x2="6" y2="15"></line>
                        <circle cx="18" cy="6" r="3"></circle>
                        <circle cx="6" cy="18" r="3"></circle>
                        <path d="M18 9a9 9 0 0 1-9 9"></path>
                    </svg>
                    <span>{{ __('portal.nav.how_it_is_managed') }}</span>
                </a>
                <a href="/proyecto{{ $langQuery }}" style="display: flex; align-items: center; gap: 0.75rem; text-decoration: none; font-size: 1.05rem; font-weight: 500; color: {{ request()->is('proyecto*') ? 'var(--color-enlace)' : 'var(--color-texto)' }}; padding: 0.65rem 0.5rem; border-radius: var(--radio-sm);">
                    <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--color-acento);">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="16" x2="12" y2="12"></line>
                        <line x1="12" y1="8" x2="12.01" y2="8"></line>
                    </svg>
                    <span>{{ __('portal.nav.project') }}</span>
                </a>
                <a href="/firmware{{ $langQuery }}" style="display: flex; align-items: center; gap: 0.75rem; text-decoration: none; font-size: 1.05rem; font-weight: 500; color: {{ request()->is('firmware*') ? 'var(--color-enlace)' : 'var(--color-texto)' }}; padding: 0.65rem 0.5rem; border-radius: var(--radio-sm);">
                    <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--color-acento);">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="7 10 12 15 17 10"></polyline>
                        <line x1="12" y1="15" x2="12" y2="3"></line>
                    </svg>
                    <span>{{ __('portal.nav.firmware') }}</span>
                </a>
                <a href="/quien-lo-impulsa{{ $langQuery }}" style="display: flex; align-items: center; gap: 0.75rem; text-decoration: none; font-size: 1.05rem; font-weight: 500; color: {{ request()->is('quien-lo-impulsa*') ? 'var(--color-enlace)' : 'var(--color-texto)' }}; padding: 0.65rem 0.5rem; border-radius: var(--radio-sm);">
                    <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--color-acento);">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                    <span>{{ __('portal.nav.who_drives_it') }}</span>
                </a>
                <a href="/sugerencias{{ $langQuery }}" style="display: flex; align-items: center; gap: 0.75rem; text-decoration: none; font-size: 1.05rem; font-weight: 500; color: {{ request()->is('sugerencias*') ? 'var(--color-enlace)' : 'var(--color-texto)' }}; padding: 0.65rem 0.5rem; border-radius: var(--radio-sm);">
                    <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--color-acento);">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                    </svg>
                    <span>{{ __('portal.nav.suggestions') }}</span>
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
