<x-layout
    :title="__('portal.nav.map') . ' · ' . config('proyecto.nombre')"
    activa="/mapa"
    :description="__('portal.mapa.meta_description')"
    :keywords="__('portal.mapa.meta_keywords')"
    :image="asset('img/og/og-mapa.webp')"
    :sinPie="true"
>
    <x-slot:styles>
        @vite(['resources/css/mapa.css'])
    </x-slot:styles>
    <x-slot:scripts>
        @vite(['resources/js/mapa/mapa.js'])
        @php
            $mapSchema = [
                chr(64).'context' => 'https://schema.org',
                chr(64).'type' => 'WebApplication',
                'name' => __('portal.mapa.title') . ' — ' . config('proyecto.nombre'),
                'url' => url('/mapa'),
                'applicationCategory' => 'GeographicInformationSystem',
                'operatingSystem' => 'All',
                'browserRequirements' => 'Requires JavaScript. Requires HTML5 Canvas or WebGL.',
                'description' => __('portal.mapa.meta_description'),
                'screenshot' => asset('img/og/og-mapa.webp'),
            ];
        @endphp
        <script type="application/ld+json">
        {!! json_encode($mapSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
        </script>
    </x-slot:scripts>

    <div class="mapa-pantalla" aria-label="{{ __('portal.mapa.title') }}">
        <!-- Barra Superior Flotante: Métricas de red y controles interactivos -->
        <div class="mapa-topbar">
            <!-- Fila 1 en móvil / Lado izquierdo en escritorio -->
            <div class="mapa-topbar-left">
                <div class="mapa-pill-stat" title="Total de nodos con posición geográfica conocida">
                    <span class="val" id="top-total-nodes">{{ $stats['total_nodes'] ?? 0 }}</span>
                    <span class="lbl">{{ __('portal.map_stats.nodes') ?? 'NODOS' }}</span>
                </div>
                <div class="mapa-pill-stat" title="Nodos con actividad recibida en los últimos 60 minutos">
                    <span class="val" id="top-active-1h">{{ $stats['active_1h'] ?? 0 }}</span>
                    <span class="lbl">{{ __('portal.map_stats.active_1h') ?? 'ACTIVOS 1H' }}</span>
                </div>
                <div class="mapa-pill-stat" title="Pasarelas comunitarias conectadas al broker MQTT">
                    <span class="val" id="top-gateways">{{ $stats['gateways_count'] ?? 0 }}</span>
                    <span class="lbl">{{ __('portal.map_stats.gateways') ?? 'GATEWAYS' }}</span>
                </div>
                <div class="mapa-pill-stat mapa-pill-time" title="Hora peninsular de la última compilación de la caché">
                    <span class="lbl mapa-desktop-only" style="font-weight: 500;">act.</span>
                    <span class="val" id="top-updated" style="color: #94a3b8;">{{ $stats['updated_at'] ?? '--:--' }}</span>
                </div>
            </div>

            <!-- Fila 2 en móvil / Lado derecho en escritorio -->
            <div class="mapa-topbar-right">
                <!-- Botón No Optimizados (Escritorio) -->
                <button
                    type="button"
                    id="btn-no-optimizados"
                    class="mapa-btn-action btn-unoptimized mapa-desktop-only"
                    title="Ver catálogo de nodos con problemas de configuración detectados"
                >
                    <span>⚠️</span>
                    <span>No optimizados (<strong id="top-unoptimized-count">{{ $stats['unoptimized_count'] ?? 0 }}</strong>)</span>
                </button>

                <!-- Botón Favoritos (Escritorio) -->
                <button
                    type="button"
                    id="btn-toggle-favs"
                    class="mapa-btn-action btn-fav mapa-desktop-only"
                    title="Filtrar y ver únicamente nodos marcados como favoritos en este navegador"
                >
                    <span>⭐</span>
                    <span>Favoritos</span>
                </button>

                <!-- Controles de navegación y ubicación (en móvil se sitúan a la izquierda de la fila 2) -->
                <div class="mapa-topbar-nav-btns">
                    <!-- Botón Centrar Andalucía -->
                    <button
                        type="button"
                        id="btn-center-andalucia"
                        class="mapa-btn-action"
                        title="Centrar vista en Andalucía"
                        style="padding: 0.45rem 0.65rem;"
                    >
                        <svg aria-hidden="true" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="12 2 19 21 12 17 5 21 12 2"></polygon>
                        </svg>
                    </button>

                    <!-- Botón Mi Ubicación -->
                    <button
                        type="button"
                        id="btn-locate-me"
                        class="mapa-btn-action"
                        title="Centrar mapa en mi ubicación geográfica actual"
                        style="padding: 0.45rem 0.65rem;"
                    >
                        <svg aria-hidden="true" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                    </button>
                </div>

                <!-- Buscador Predictivo (en móvil ocupa el resto a la derecha de centrar y ubicación) -->
                <div class="mapa-search-wrapper">
                    <svg class="mapa-search-icon" aria-hidden="true" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <input
                        type="text"
                        id="mapa-search-input"
                        class="mapa-search-input"
                        placeholder="Buscar nodo..."
                        autocomplete="off"
                        aria-label="Buscar nodo por nombre, identificador hexadecimal o provincia"
                    />
                    <div id="mapa-search-results" class="mapa-search-results"></div>
                </div>
            </div>
        </div>

        <!-- Esquina Inferior Izquierda (Solo Móvil): Favoritos y No optimizados -->
        <div class="mapa-bottombar-left mapa-mobile-only" aria-label="{{ __('portal.mapa.fast_controls') }}">
            <button
                type="button"
                id="btn-toggle-favs-mobile"
                class="mapa-btn-action btn-fav mapa-btn-icon-only"
                title="{{ __('portal.mapa.filter_favs') }}"
                aria-label="{{ __('portal.mapa.filter_favs') }}"
            >
                <span>⭐</span>
            </button>
            <button
                type="button"
                id="btn-no-optimizados-mobile"
                class="mapa-btn-action btn-unoptimized mapa-btn-icon-only"
                title="{{ __('portal.mapa.unoptimized_nodes') }}"
                aria-label="{{ __('portal.mapa.unoptimized_nodes') }}"
            >
                <span>⚠️</span>
                <span class="mapa-mini-badge" id="top-unoptimized-count-mobile">{{ $stats['unoptimized_count'] ?? 0 }}</span>
            </button>
        </div>

        <!-- Lienzo Leaflet -->
        <div
            id="mapa-lienzo"
            data-lat="{{ $mapaConfig['centro'][0] ?? 37.4 }}"
            data-lon="{{ $mapaConfig['centro'][1] ?? -4.5 }}"
            data-zoom="{{ $mapaConfig['zoom'] ?? 7 }}"
            data-dominio="{{ $dominio }}"
            data-carto-key="{{ $mapaConfig['carto_api_key'] ?? '' }}"
        ></div>

        <!-- Cajón Lateral de Detalle de Nodo (Bottom-Left Drawer) -->
        <div id="mapa-drawer" class="mapa-drawer" role="dialog" aria-labelledby="drawer-node-title">
            <div class="mapa-drawer-header">
                <div class="mapa-drawer-title-wrap">
                    <span id="drawer-node-title" class="mapa-drawer-title">Nombre del nodo</span>
                    <button type="button" id="btn-drawer-fav" class="mapa-btn-fav" title="Marcar como favorito" aria-label="Marcar nodo como favorito">★</button>
                </div>
                <button type="button" id="btn-drawer-close" class="mapa-drawer-close" aria-label="Cerrar ficha">
                    <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>

            <div class="mapa-drawer-body">
                <!-- Alerta si el nodo no está optimizado -->
                <div id="drawer-warning-box" class="mapa-drawer-warning-box" style="display: none;">
                    <div class="mapa-drawer-warning-title">
                        <span>⚠️</span>
                        <span>Este nodo puede estar no optimizado</span>
                    </div>
                    <ul id="drawer-warning-list" class="mapa-drawer-warning-list"></ul>
                </div>

                <!-- Tabla de propiedades técnicas -->
                <table class="mapa-props-table">
                    <tbody>
                        <tr>
                            <td class="prop-lbl">ID (Hex)</td>
                            <td class="prop-val" id="prop-id" style="color: var(--color-acento);">--</td>
                        </tr>
                        <tr>
                            <td class="prop-lbl">ID (Dec)</td>
                            <td class="prop-val" id="prop-dec">--</td>
                        </tr>
                        <tr>
                            <td class="prop-lbl">Nombre Corto</td>
                            <td class="prop-val" id="prop-short">--</td>
                        </tr>
                        <tr>
                            <td class="prop-lbl">Hardware</td>
                            <td class="prop-val" id="prop-hw">--</td>
                        </tr>
                        <tr>
                            <td class="prop-lbl">Rol</td>
                            <td class="prop-val" id="prop-role">--</td>
                        </tr>
                        <tr>
                            <td class="prop-lbl">Firmware</td>
                            <td class="prop-val" id="prop-fw">--</td>
                        </tr>
                        <tr>
                            <td class="prop-lbl">Gateway MQTT</td>
                            <td class="prop-val" id="prop-gw">--</td>
                        </tr>
                        <tr>
                            <td class="prop-lbl">Coordenadas</td>
                            <td class="prop-val" id="prop-coord">--</td>
                        </tr>
                        <tr>
                            <td class="prop-lbl">Provincia</td>
                            <td class="prop-val" id="prop-prov">--</td>
                        </tr>
                        <tr>
                            <td class="prop-lbl">Último Visto</td>
                            <td class="prop-val" id="prop-seen">--</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mapa-drawer-actions">
                <button type="button" id="btn-drawer-diag" class="mapa-btn-diag">
                    <span>⚠️</span>
                    <span>Ver diagnóstico completo</span>
                </button>
                <a
                    id="btn-drawer-revisa"
                    href="/revisa-tu-nodo"
                    class="mapa-btn-action"
                    style="text-align: center; justify-content: center; text-decoration: none;"
                >
                    Informe de salud 7 días →
                </a>
            </div>
        </div>

        <!-- Leyenda Inferior Derecha (Colapsable) -->
        <div id="mapa-legend" class="mapa-legend" aria-label="{{ __('portal.mapa.legend_title') }}">
            <div class="mapa-legend-header">
                <div class="mapa-legend-title">{{ __('portal.mapa.legend_title') }}</div>
                <button
                    type="button"
                    id="btn-close-legend"
                    class="mapa-legend-btn-close"
                    title="{{ __('portal.mapa.legend_minimize') }}"
                    aria-label="{{ __('portal.mapa.legend_minimize') }}"
                >
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
            <div class="mapa-legend-item">
                <div class="mesh-marker-dot active-1h" style="width: 10px; height: 10px;"></div>
                <span>Activo &lt; 2 h</span>
            </div>
            <div class="mapa-legend-item">
                <div class="mesh-marker-dot active-24h" style="width: 10px; height: 10px;"></div>
                <span>Activo &lt; 24 h</span>
            </div>
            <div class="mapa-legend-item">
                <div class="mesh-marker-dot inactive" style="width: 10px; height: 10px;"></div>
                <span>Sin actividad reciente</span>
            </div>
            <div class="mapa-legend-item">
                <div class="mesh-marker-triangle" style="border-bottom-width: 11px; border-left-width: 6px; border-right-width: 6px;"></div>
                <span>Router / Repetidor</span>
            </div>
            <div class="mapa-legend-item">
                <div class="mesh-marker-gw" style="width: 11px; height: 11px; border-width: 1.5px;"></div>
                <span>Gateway MQTT</span>
            </div>
            <div class="mapa-legend-item">
                <span style="color: #f59e0b; font-weight: 900; font-size: 11px;">▲</span>
                <span>No optimizado (aviso)</span>
            </div>
        </div>

        <!-- Botón flotante para reabrir la leyenda con flecha de triángulo ancho saltando y brillo verde -->
        <button
            type="button"
            id="btn-open-legend"
            class="mapa-btn-open-legend"
            title="{{ __('portal.mapa.legend_expand') }}"
            aria-label="{{ __('portal.mapa.legend_expand') }}"
        >
            <svg class="mapa-arrow-up-triangle" width="16" height="13" viewBox="0 0 20 16" fill="currentColor" aria-hidden="true">
                <polygon points="10,1 19,15 1,15"></polygon>
            </svg>
        </button>
    </div>

    <!-- Modal Central de Diagnóstico ("Diagnóstico del nodo") -->
    <div id="mapa-modal-diag" class="mapa-modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="diag-node-name">
        <div class="mapa-modal-card">
            <div class="mapa-modal-header">
                <h3>
                    <span>⚠️</span>
                    <span>Diagnóstico del nodo</span>
                </h3>
                <button type="button" id="btn-modal-diag-close" class="mapa-drawer-close" aria-label="Cerrar diagnóstico">
                    <svg aria-hidden="true" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>

            <div class="mapa-modal-body">
                <div>
                    <h4 id="diag-node-name" style="margin: 0; font-family: var(--fuente-titulos); font-size: 1.3rem; color: #ffffff;">--</h4>
                    <div id="diag-node-sub" class="mapa-modal-node-sub">--</div>
                </div>

                <!-- Hop Limit Box -->
                <div id="diag-hop-box" class="mapa-hop-box recomendado">
                    ℹ️ Comprobando configuración de saltos...
                </div>

                <!-- Problemas Detectados -->
                <div>
                    <div style="font-weight: 700; font-size: 0.8rem; text-transform: uppercase; color: #8b949e; letter-spacing: 0.05em; margin-bottom: 0.6rem;">
                        Problemas detectados
                    </div>
                    <div id="diag-problems-container" style="display: flex; flex-direction: column; gap: 0.6rem;">
                        <div style="color: #94a3b8; font-size: 0.85rem;">Analizando...</div>
                    </div>
                </div>

                <!-- Desglose de Paquetes en 24h -->
                <div>
                    <div style="font-weight: 700; font-size: 0.8rem; text-transform: uppercase; color: #8b949e; letter-spacing: 0.05em; margin-bottom: 0.6rem;">
                        Desglose de paquetes (últimas 24 horas)
                    </div>
                    <div id="diag-traffic-container"></div>
                </div>

                <!-- Enlaces de navegación a visores técnicos -->
                <div style="display: flex; align-items: center; justify-content: space-between; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 1rem; margin-top: 0.5rem; flex-wrap: wrap; gap: 0.75rem;">
                    <a id="diag-link-meshview" href="#" target="_blank" rel="noopener noreferrer" style="color: #38bdf8; font-size: 0.85rem; font-weight: 600; text-decoration: none;">
                        Ver actividad técnica en MeshView →
                    </a>
                    <a id="diag-link-revisa" href="#" style="color: #4ade80; font-size: 0.85rem; font-weight: 600; text-decoration: none;">
                        Ver informe de batería y reinicios en Revisa tu nodo →
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Catálogo "No Optimizados" -->
    <div id="mapa-modal-catalog" class="mapa-modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="catalog-title">
        <div class="mapa-modal-card" style="width: 760px;">
            <div class="mapa-modal-header">
                <h3 id="catalog-title">
                    <span>⚠️</span>
                    <span>Nodos no optimizados en la red</span>
                </h3>
                <button type="button" id="btn-modal-catalog-close" class="mapa-drawer-close" aria-label="Cerrar catálogo">
                    <svg aria-hidden="true" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>

            <div class="mapa-modal-body">
                <div class="mapa-catalog-tabs">
                    <button type="button" class="mapa-catalog-tab-btn active" data-tab="tab-catalog-list">
                        Listado de nodos con avisos
                    </button>
                    <button type="button" class="mapa-catalog-tab-btn" data-tab="tab-catalog-stats">
                        Resumen de motivos
                    </button>
                </div>

                <!-- Tab 1: Listado de Nodos -->
                <div id="tab-catalog-list" class="mapa-catalog-tab-pane">
                    <div id="catalog-list-content" style="max-height: 480px; overflow-y: auto;">
                        <div style="color: #94a3b8; font-size: 0.85rem; padding: 1rem;">Cargando catálogo...</div>
                    </div>
                </div>

                <!-- Tab 2: Estadísticas -->
                <div id="tab-catalog-stats" class="mapa-catalog-tab-pane" style="display: none;">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; padding: 0.5rem 0;">
                        <div style="background-color: #1a202c; border-radius: 0.5rem; padding: 1rem; border-left: 4px solid #ef4444;">
                            <div style="font-size: 0.75rem; color: #94a3b8; text-transform: uppercase;">Hop Limit Excesivo</div>
                            <div style="font-size: 1.6rem; font-weight: 700; color: #ffffff;">{{ $stats['unoptimized_breakdown']['hop_limit_alto'] ?? 0 }}</div>
                            <div style="font-size: 0.75rem; color: #f87171; margin-top: 0.2rem;">Saltos superiores a 3 o 5</div>
                        </div>
                        <div style="background-color: #1a202c; border-radius: 0.5rem; padding: 1rem; border-left: 4px solid #f59e0b;">
                            <div style="font-size: 0.75rem; color: #94a3b8; text-transform: uppercase;">CLIENT_BASE ≥ 2.7.17</div>
                            <div style="font-size: 1.6rem; font-weight: 700; color: #ffffff;">{{ $stats['unoptimized_breakdown']['client_base_fw'] ?? 0 }}</div>
                            <div style="font-size: 0.75rem; color: #fbbf24; margin-top: 0.2rem;">Actúa como ROUTER_LATE</div>
                        </div>
                        <div style="background-color: #1a202c; border-radius: 0.5rem; padding: 1rem; border-left: 4px solid #38bdf8;">
                            <div style="font-size: 0.75rem; color: #94a3b8; text-transform: uppercase;">NodeInfo Frecuente</div>
                            <div style="font-size: 1.6rem; font-weight: 700; color: #ffffff;">{{ $stats['unoptimized_breakdown']['nodeinfo_frecuente'] ?? 0 }}</div>
                            <div style="font-size: 0.75rem; color: #7dd3fc; margin-top: 0.2rem;">Emisión &lt; 12 horas</div>
                        </div>
                        <div style="background-color: #1a202c; border-radius: 0.5rem; padding: 1rem; border-left: 4px solid #a855f7;">
                            <div style="font-size: 0.75rem; color: #94a3b8; text-transform: uppercase;">Telemetría Acelerada</div>
                            <div style="font-size: 1.6rem; font-weight: 700; color: #ffffff;">{{ $stats['unoptimized_breakdown']['telemetria_frecuente'] ?? 0 }}</div>
                            <div style="font-size: 0.75rem; color: #c084fc; margin-top: 0.2rem;">Intervalo &lt; 2 horas</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layout>
