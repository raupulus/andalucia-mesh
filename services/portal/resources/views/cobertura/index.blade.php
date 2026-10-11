<x-layout
    :title="__('portal.nav.my_coverage') . ' — Verificador de Cobertura RF'"
    description="Analiza la cobertura RF real de tu nodo Meshtastic o compara la visión compartida y exclusiva de hasta 3 nodos simultáneamente con informes en PDF y Markdown."
>
    <!-- Modal de espera con Spinner Corporativo y Logotipo -->
    <div id="modal-cargando-cobertura" class="modal-cobertura-backdrop" style="display: none;" role="dialog" aria-modal="true" aria-labelledby="modal-cargando-titulo">
        <div class="modal-cobertura-card">
            <img src="{{ asset('img/logo-pdf.png') }}" alt="" aria-hidden="true" style="width: 48px; height: 48px; object-fit: contain; margin: 0 auto 0.85rem auto; display: block;" width="48" height="48">
            <div class="spinner-cobertura-anillo" aria-hidden="true"></div>
            <h3 id="modal-cargando-titulo" class="modal-cobertura-titulo">Compilando análisis de cobertura RF</h3>
            <p class="modal-cobertura-texto">Consultando telemetría de enlaces y trazas LoRa de la red Meshtastic...</p>
            <div class="separador-bandera-modal" aria-hidden="true">
                <span class="franja-verde"></span>
                <span class="franja-blanca"></span>
                <span class="franja-verde"></span>
            </div>
        </div>
    </div>

    <div class="contenedor" style="padding-top: 1.5rem; padding-bottom: 4rem;">
        <!-- Cabecera Superior de la Herramienta -->
        <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: flex-start; gap: 1rem; margin-bottom: 1.5rem;">
            <div>
                <div class="pildora-header-cobertura">
                    SUR NODOS EN MALLAS · RED MESHTASTIC ANDALUCÍA
                </div>
                <h1 style="font-family: 'Ubuntu', sans-serif; font-size: 2.1rem; font-weight: 800; color: var(--color-texto); margin: 0.5rem 0 0.35rem 0; line-height: 1.2;">
                    @if(!empty($datos['valido']))
                        @if(($datos['filtro'] ?? '') === 'todos')
                            Matriz de Enlaces Completa (Todos)
                        @else
                            Matriz de Enlaces Positivos (SNR &gt; 0 dB)
                        @endif
                    @else
                        Mi Cobertura — Verificador de Enlaces RF
                    @endif
                </h1>
                <p style="margin: 0; color: var(--color-texto-2); font-size: 0.95rem;">
                    @if(!empty($datos['valido']))
                        @php
                            $nombresNodos = array_map(function ($n) {
                                return ($n['short_name'] ?? '') . ' (' . ($n['id'] ?? '') . ')';
                            }, $datos['nodos_evaluados'] ?? []);
                        @endphp
                        Nodos analizados: <strong style="color: var(--color-texto);">{{ implode(', ', $nombresNodos) }}</strong>
                    @else
                        Escribe tu nodo para auditar sus enlaces a 1 salto o compara hasta 3 nodos simultáneamente en la malla.
                    @endif
                </p>
            </div>

            @if(!empty($datos['valido']))
                <div class="tarjeta-ventana-analisis">
                    <span style="font-size: 0.72rem; text-transform: uppercase; font-weight: 700; letter-spacing: 0.05em; color: var(--color-texto-2); display: block;">
                        Ventana de Análisis
                    </span>
                    <span style="font-size: 0.85rem; font-weight: 600; color: var(--color-texto); font-family: 'Inter', sans-serif;">
                        {{ $datos['ventana_texto'] ?? 'Últimas 24 horas' }}
                    </span>
                </div>
            @endif
        </div>

        <!-- Banner Informativo: Enlaces 100% Directos (0 saltos RF) -->
        <div class="banner-cobertura-directa" role="note" aria-label="Metodología de enlaces directos">
            <div class="banner-cobertura-directa-icono" aria-hidden="true">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 12.55a11 11 0 0 1 14.08 0"></path>
                    <path d="M1.42 9a16 16 0 0 1 21.16 0"></path>
                    <path d="M8.53 16.11a6 6 0 0 1 6.95 0"></path>
                    <line x1="12" y1="20" x2="12.01" y2="20"></line>
                </svg>
            </div>
            <div class="banner-cobertura-directa-cuerpo">
                <strong class="banner-cobertura-directa-titulo">Enlaces 100% directos (0 saltos RF · Sin saltos multi-hop)</strong>
                <p class="banner-cobertura-directa-texto">
                    Este verificador analiza única y exclusivamente las conexiones directas de radiofrecuencia entre cada estación evaluada y sus vecinos físicos inmediatos. No incluye tráfico intermediado ni retransmisiones multi-hop, evaluando el alcance físico real y la calidad de la instalación en su emplazamiento geográfico.
                </p>
            </div>
        </div>

        <!-- Panel del Buscador Interactivo y Filtros -->
        <div class="card-cobertura-buscador" style="background: var(--color-superficie); border: 1px solid var(--color-borde); border-radius: 14px; padding: 1.25rem; margin-bottom: 2rem; box-shadow: var(--sombra-tarjeta);">
            <form id="form-cobertura" method="GET" action="/mi-cobertura" onsubmit="return prepararEnvioFormulario(event);">
                <input type="hidden" name="nodes" id="input-hidden-nodes" value="{{ implode(',', $nodosSeleccionados ?? []) }}">

                <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                    <!-- Selector de Filtro SNR -->
                    <div style="display: inline-flex; align-items: center; gap: 0.5rem; background: var(--color-superficie-sutil); border: 1px solid var(--color-borde-control); border-radius: 9999px; padding: 3px 6px;">
                        <label style="cursor: pointer; display: inline-flex; align-items: center; gap: 0.35rem; font-size: 0.82rem; font-weight: 700; padding: 4px 10px; border-radius: 9999px; transition: all 0.15s; {{ ($filtro ?? 'snr_positivo') === 'snr_positivo' ? 'background: var(--color-acento); color: var(--color-sobre-acento);' : 'color: var(--color-texto-2);' }}">
                            <input type="radio" name="filter" value="snr_positivo" {{ ($filtro ?? 'snr_positivo') === 'snr_positivo' ? 'checked' : '' }} onchange="cambiarFiltro('snr_positivo');" style="display: none;">
                            Solo SNR positivo (&gt; 0 dB)
                        </label>
                        <label style="cursor: pointer; display: inline-flex; align-items: center; gap: 0.35rem; font-size: 0.82rem; font-weight: 700; padding: 4px 10px; border-radius: 9999px; transition: all 0.15s; {{ ($filtro ?? '') === 'todos' ? 'background: var(--color-acento); color: var(--color-sobre-acento);' : 'color: var(--color-texto-2);' }}">
                            <input type="radio" name="filter" value="todos" {{ ($filtro ?? '') === 'todos' ? 'checked' : '' }} onchange="cambiarFiltro('todos');" style="display: none;">
                            Todos (incluye marginales)
                        </label>
                    </div>

                    <!-- Botones de Acción, Descarga y Compartir -->
                    @if(!empty($datos['valido']))
                        <div style="display: inline-flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                            <a
                                href="/mi-cobertura/pdf?nodes={{ implode(',', $nodosSeleccionados) }}&filter={{ $filtro }}"
                                class="btn-descarga-cobertura"
                                onclick="event.preventDefault(); descargarFichero(this.href, 'cobertura.pdf');"
                                title="Descargar informe completo en PDF estilizado"
                            >
                                <svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                    <polyline points="14 2 14 8 20 8"></polyline>
                                    <line x1="12" y1="18" x2="12" y2="12"></line>
                                    <line x1="9" y1="15" x2="15" y2="15"></line>
                                </svg>
                                <span>Descargar PDF</span>
                            </a>

                            <a
                                href="/mi-cobertura/markdown?nodes={{ implode(',', $nodosSeleccionados) }}&filter={{ $filtro }}"
                                class="btn-descarga-cobertura btn-descarga-md"
                                onclick="event.preventDefault(); descargarFichero(this.href, 'cobertura.md');"
                                title="Descargar informe estructurado en Markdown (GFM)"
                            >
                                <svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path>
                                    <polyline points="13 2 13 9 20 9"></polyline>
                                </svg>
                                <span>Descargar Markdown</span>
                            </a>

                            <!-- Botones de Compartir -->
                            <div class="grupo-compartir-cobertura">
                                <a
                                    href="{{ $shareWhatsApp ?? '#' }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="btn-share-icon btn-share-whatsapp"
                                    title="Compartir análisis por WhatsApp"
                                    aria-label="Compartir en WhatsApp"
                                >
                                    <svg aria-hidden="true" width="15" height="15" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91C2.13 13.66 2.59 15.36 3.45 16.86L2.05 22L7.3 20.63C8.75 21.41 10.38 21.83 12.04 21.83C17.5 21.83 21.95 17.38 21.95 11.92C21.95 9.27 20.92 6.78 19.05 4.91C17.18 3.03 14.69 2 12.04 2M12.05 3.67C14.25 3.67 16.31 4.53 17.87 6.09C19.42 7.65 20.28 9.72 20.28 11.92C20.28 16.46 16.58 20.15 12.04 20.15C10.56 20.15 9.11 19.76 7.85 19L7.55 18.83L4.43 19.65L5.26 16.61L5.06 16.29C4.24 15 3.8 13.47 3.8 11.91C3.81 7.37 7.5 3.67 12.05 3.67M9.53 7.07C9.36 7.07 9.09 7.14 8.86 7.39C8.63 7.64 8 8.23 8 9.44C8 10.65 8.89 11.82 9.01 11.99C9.13 12.16 10.74 14.65 13.23 15.72C15.3 16.61 15.72 16.43 16.17 16.39C16.62 16.35 17.63 15.79 17.84 15.21C18.05 14.63 18.05 14.13 17.99 14.03C17.93 13.93 17.78 13.87 17.55 13.76C17.32 13.65 16.19 13.09 15.98 13.01C15.77 12.93 15.62 12.89 15.47 13.12C15.32 13.35 14.89 13.87 14.76 14.02C14.63 14.17 14.5 14.19 14.28 14.08C14.05 13.97 13.33 13.73 12.47 12.96C11.8 12.36 11.35 11.62 11.22 11.39C11.09 11.16 11.21 11.04 11.32 10.93C11.42 10.83 11.55 10.66 11.66 10.53C11.77 10.4 11.81 10.3 11.89 10.15C11.97 10 11.93 9.87 11.87 9.76C11.81 9.65 11.36 8.54 11.17 8.09C10.99 7.65 10.8 7.71 10.66 7.7C10.53 7.7 10.38 7.69 10.23 7.69C10.08 7.69 9.83 7.75 9.63 7.97C9.43 8.19 8.86 8.72 8.86 9.82C8.86 10.92 9.66 11.98 9.77 12.13C9.88 12.28 11.34 14.52 13.56 15.48C15.42 16.28 15.8 16.12 16.2 16.08C16.6 16.04 17.5 15.54 17.69 15.02C17.88 14.5 17.88 14.05 17.82 13.96C17.76 13.87 17.63 13.82 17.42 13.72Z"/>
                                    </svg>
                                    <span>WhatsApp</span>
                                </a>

                                <a
                                    href="{{ $shareTelegram ?? '#' }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="btn-share-icon btn-share-telegram"
                                    title="Compartir análisis por Telegram"
                                    aria-label="Compartir en Telegram"
                                >
                                    <svg aria-hidden="true" width="15" height="15" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69a.2.2 0 0 0-.05-.18c-.06-.05-.14-.03-.21-.02-.09.02-1.49.95-4.22 2.79-.4.27-.76.41-1.08.4-.36-.01-1.04-.2-1.55-.37-.63-.2-1.12-.31-1.08-.66.02-.18.27-.36.74-.55 2.92-1.27 4.86-2.11 5.83-2.51 2.78-1.16 3.35-1.36 3.73-1.36.08 0 .27.02.39.12.1.08.13.19.14.27-.01.06.01.24 0 .38z"/>
                                    </svg>
                                    <span>Telegram</span>
                                </a>

                                <a
                                    href="{{ $shareEmail ?? '#' }}"
                                    class="btn-share-icon btn-share-email"
                                    title="Compartir análisis por Correo electrónico"
                                    aria-label="Compartir por Correo"
                                >
                                    <svg aria-hidden="true" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                        <polyline points="22,6 12,13 2,6"></polyline>
                                    </svg>
                                    <span>Email</span>
                                </a>

                                <button
                                    type="button"
                                    class="btn-share-icon btn-share-copiar"
                                    onclick="copiarEnlaceCompartir('{{ $urlCompartir ?? '' }}');"
                                    title="Copiar enlace permanente"
                                    aria-label="Copiar enlace"
                                >
                                    <svg aria-hidden="true" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                                        <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                                    </svg>
                                    <span id="texto-btn-copiar">Copiar enlace</span>
                                </button>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Lista de Nodos Seleccionados (Pills) -->
                <div style="margin-bottom: 0.75rem;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.35rem;">
                        <span style="font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--color-texto-2);">
                            Nodos Seleccionados (<span id="contador-nodos">{{ count($nodosSeleccionados ?? []) }}</span>/3):
                        </span>
                        @if(empty($nodosSeleccionados) || (count($nodosSeleccionados) === 2 && !in_array('!30618df9', $nodosSeleccionados)))
                            <button
                                type="button"
                                onclick="cargarEjemploSierraLijar();"
                                style="background: none; border: none; font-size: 0.78rem; font-weight: 600; color: var(--color-enlace); cursor: pointer; text-decoration: underline;"
                            >
                                ⚡ Cargar ejemplo: CA01 y CA05 (Sierra de Líjar)
                            </button>
                        @endif
                    </div>

                    <div id="contenedor-pills-nodos" style="display: flex; flex-wrap: wrap; gap: 0.5rem; min-height: 38px; align-items: center;">
                        @forelse($nodosSeleccionados as $idx => $nid)
                            @php
                                $infoN = null;
                                if (!empty($datos['nodos_evaluados'])) {
                                    foreach ($datos['nodos_evaluados'] as $ne) {
                                        if ($ne['id'] === $nid) { $infoN = $ne; break; }
                                    }
                                }
                                $alias = $infoN['short_name'] ?? strtoupper(substr(ltrim($nid, '!'), -4));
                            @endphp
                            <div class="pill-nodo-seleccionado" data-node-id="{{ $nid }}">
                                <span class="pill-nodo-mono">{{ $nid }}</span>
                                <strong class="pill-nodo-alias">{{ $alias }}</strong>
                                <button type="button" class="btn-quitar-nodo" onclick="quitarNodo('{{ $nid }}');" aria-label="Quitar nodo {{ $nid }}">×</button>
                            </div>
                        @empty
                            <span id="texto-sin-nodos" style="font-size: 0.85rem; color: var(--color-texto-3); font-style: italic;">
                                Ningún nodo seleccionado. Escribe abajo para buscar tu nodo o repetidor.
                            </span>
                        @endforelse
                    </div>
                </div>

                <!-- Campo de Entrada con Autocompletado Reactivo -->
                <div style="position: relative; margin-bottom: 0.75rem;">
                    <div style="display: flex; gap: 0.5rem;">
                        <div style="position: relative; flex: 1;">
                            <input
                                type="text"
                                id="input-buscar-nodo"
                                placeholder="Escribe tu ID (ej. !30618df9) o nombre del nodo..."
                                autocomplete="off"
                                class="input-cobertura-buscar"
                                oninput="enInputBuscar(this.value);"
                                onkeydown="enKeydownBuscar(event);"
                            >
                            <div id="spinner-busqueda-inline" style="display: none; position: absolute; right: 12px; top: 11px;">
                                <div class="spinner-inline-mini"></div>
                            </div>
                        </div>

                        <button
                            type="submit"
                            id="btn-consultar-cobertura"
                            class="btn-cobertura-principal"
                            {{ empty($nodosSeleccionados) ? 'disabled' : '' }}
                        >
                            Consultar Cobertura
                        </button>
                    </div>

                    <!-- Desplegable de Sugerencias -->
                    <ul id="lista-sugerencias-nodos" class="desplegable-sugerencias-cobertura" style="display: none;" role="listbox">
                    </ul>
                </div>
            </form>
        </div>

        @if(!empty($datos['valido']))
            <!-- ============================================================== -->
            <!-- 4 Tarjetas KPI Superiores (Idénticas a la imagen de referencia) -->
            <!-- ============================================================== -->
            <div class="grid-kpis-cobertura" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
                @foreach($datos['kpis'] ?? [] as $kpi)
                    <div class="card-kpi-cobertura">
                        <div class="card-kpi-cifra">{{ $kpi['cifra'] }}</div>
                        <div class="card-kpi-titulo">{{ $kpi['titulo'] }}</div>
                        <div class="card-kpi-subtitulo">{{ $kpi['subtitulo'] }}</div>
                    </div>
                @endforeach
            </div>

            <!-- ============================================================== -->
            <!-- Tabla Estructurada con Bloques Temáticos (Idéntica a referencia)-->
            <!-- ============================================================== -->
            <div class="tabla-cobertura-contenedor" style="background: var(--color-superficie); border: 1px solid var(--color-borde); border-radius: 12px; overflow: hidden; box-shadow: var(--sombra-tarjeta);">
                <div style="overflow-x: auto;">
                    <table class="tabla-matriz-cobertura" style="width: 100%; border-collapse: collapse; text-align: left;">
                        <thead>
                            <tr style="background: var(--color-superficie-sutil); border-bottom: 1px solid var(--color-borde);">
                                <th style="padding: 12px 14px; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--color-texto-2); width: 140px;">
                                    NODO ID
                                </th>
                                <th style="padding: 12px 14px; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--color-texto-2);">
                                    IDENTIDAD (ALIAS / NOMBRE)
                                </th>
                                <th style="padding: 12px 14px; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--color-texto-2); width: 110px;">
                                    ROL
                                </th>
                                <th style="padding: 12px 14px; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--color-texto-2); width: 100px;">
                                    PROVINCIA
                                </th>
                                <th style="padding: 12px 14px; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--color-texto-2); width: 115px;">
                                    COBERTURA
                                </th>
                                @foreach($datos['nodos_evaluados'] ?? [] as $ne)
                                    <th style="padding: 12px 14px; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--color-texto-2); width: 135px; text-align: center;">
                                        ENLACE {{ strtoupper($ne['short_name']) }}
                                    </th>
                                @endforeach
                                <th style="padding: 12px 14px; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--color-texto-2); width: 115px; text-align: right;">
                                    ÚLTIMO CONTACTO
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Bloque 1: Enlace Mutuo Inter-Router (si existe) -->
                            @if(!empty($datos['fila_mutuo']))
                                @php $fm = $datos['fila_mutuo']; @endphp
                                <tr class="fila-cabecera-bloque">
                                    <td colspan="{{ 5 + count($datos['nodos_evaluados'] ?? []) + 1 }}" style="padding: 9px 14px; font-size: 0.73rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--color-texto); background: var(--color-superficie-sutil); border-top: 1px solid var(--color-borde); border-bottom: 1px solid var(--color-borde);">
                                        🔄 ENLACE DIRECTO INTER-ROUTER
                                    </td>
                                </tr>
                                <tr class="fila-dato-cobertura" style="border-bottom: 1px solid var(--color-borde);">
                                    <td class="celda-mono" style="padding: 10px 14px; font-family: 'Ubuntu Mono', monospace; font-size: 0.8rem; color: var(--color-texto-2);">
                                        {{ $fm['id'] }}
                                    </td>
                                    <td style="padding: 10px 14px;">
                                        <strong style="color: var(--color-texto); font-size: 0.88rem;">{{ $fm['identidad_titulo'] }}</strong>
                                        <span style="display: block; font-size: 0.73rem; color: var(--color-texto-2);">{{ $fm['identidad_subtitulo'] }}</span>
                                    </td>
                                    <td style="padding: 10px 14px;">
                                        <span class="badge-rol {{ $fm['clase_rol'] }}">{{ $fm['role'] }}</span>
                                    </td>
                                    <td style="padding: 10px 14px;">
                                        <span class="badge-prov {{ $fm['clase_provincia'] }}">{{ $fm['province'] }}</span>
                                        @if(!empty($fm['distancia_texto']))
                                            <span class="cobertura-distancia-enlace" title="Distancia geodésica directa">
                                                📏 {{ $fm['distancia_texto'] }}
                                            </span>
                                        @endif
                                    </td>
                                    <td style="padding: 10px 14px;">
                                        <span class="badge-cob {{ $fm['cobertura_clase'] }}">
                                            <span class="punto-cob">●</span> {{ $fm['cobertura_label'] }}
                                        </span>
                                    </td>
                                    @foreach($datos['nodos_evaluados'] ?? [] as $ne)
                                        @php $col = $fm['enlaces'][$ne['id']] ?? null; @endphp
                                        <td style="padding: 10px 14px; text-align: center;">
                                            @if($col)
                                                <div class="caja-snr {{ $col['clase_calidad'] }}">
                                                    <span class="caja-snr-valor">{{ $col['snr_formateado'] }}</span>
                                                    <span class="caja-snr-meta">{{ $col['snr_avg_formateado'] }}</span>
                                                </div>
                                            @else
                                                <span style="color: var(--color-texto-3); font-weight: bold;">—</span>
                                            @endif
                                        </td>
                                    @endforeach
                                    <td style="padding: 10px 14px; text-align: right; font-size: 0.75rem; color: var(--color-texto-2); font-family: 'Inter', sans-serif;">
                                        {{ $fm['ultimo_contacto'] }}
                                    </td>
                                </tr>
                            @endif

                            <!-- Bloque 2: Cobertura Bilateral / Compartida -->
                            @if(!empty($datos['bloque_bilateral']))
                                @php $cantBilat = count($datos['bloque_bilateral']); @endphp
                                <tr class="fila-cabecera-bloque">
                                    <td colspan="{{ 5 + count($datos['nodos_evaluados'] ?? []) + 1 }}" style="padding: 9px 14px; font-size: 0.73rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--color-texto); background: var(--color-superficie-sutil); border-top: 1px solid var(--color-borde); border-bottom: 1px solid var(--color-borde);">
                                        🟢 {{ $datos['titulo_bloque_comun'] ?? "NODOS CON COBERTURA BILATERAL (ALCANZADOS POR AMBOS REPETIDORES — {$cantBilat} NODOS)" }}
                                    </td>
                                </tr>
                                @foreach($datos['bloque_bilateral'] as $fila)
                                    <tr class="fila-dato-cobertura" style="border-bottom: 1px solid var(--color-borde);">
                                        <td class="celda-mono" style="padding: 10px 14px; font-family: 'Ubuntu Mono', monospace; font-size: 0.8rem; color: var(--color-texto-2);">
                                            {{ $fila['id'] }}
                                        </td>
                                        <td style="padding: 10px 14px;">
                                            <strong style="color: var(--color-texto); font-size: 0.88rem;">{{ $fila['short_name'] }}</strong>
                                            <span style="display: block; font-size: 0.73rem; color: var(--color-texto-2);">{{ $fila['long_name'] }}</span>
                                        </td>
                                        <td style="padding: 10px 14px;">
                                            <span class="badge-rol {{ $fila['clase_rol'] }}">{{ $fila['role'] }}</span>
                                        </td>
                                        <td style="padding: 10px 14px;">
                                            <span class="badge-prov {{ $fila['clase_provincia'] }}">{{ $fila['province'] }}</span>
                                            @if(!empty($fila['distancia_texto']))
                                                <span class="cobertura-distancia-enlace" title="Distancia geodésica directa">
                                                    📏 {{ $fila['distancia_texto'] }}
                                                </span>
                                            @endif
                                        </td>
                                        <td style="padding: 10px 14px;">
                                            <span class="badge-cob {{ $fila['cobertura_clase'] }}">
                                                <span class="punto-cob">●</span> {{ $fila['cobertura_label'] }}
                                            </span>
                                        </td>
                                        @foreach($datos['nodos_evaluados'] ?? [] as $ne)
                                            @php $col = $fila['enlaces'][$ne['id']] ?? null; @endphp
                                            <td style="padding: 10px 14px; text-align: center;">
                                                @if($col)
                                                    <div class="caja-snr {{ $col['clase_calidad'] }}">
                                                        <span class="caja-snr-valor">{{ $col['snr_formateado'] }}</span>
                                                        <span class="caja-snr-meta">{{ $col['snr_avg_formateado'] }}</span>
                                                    </div>
                                                @else
                                                    <span style="color: var(--color-texto-3); font-weight: bold;">—</span>
                                                @endif
                                            </td>
                                        @endforeach
                                        <td style="padding: 10px 14px; text-align: right; font-size: 0.75rem; color: var(--color-texto-2); font-family: 'Inter', sans-serif;">
                                            {{ $fila['ultimo_contacto'] }}
                                        </td>
                                    </tr>
                                @endforeach
                            @endif

                            <!-- Bloques de Cobertura Exclusiva de cada Nodo -->
                            @foreach($datos['bloques_exclusivos'] ?? [] as $nid => $filasExcl)
                                @if(!empty($filasExcl))
                                    @php
                                        $alias = '';
                                        foreach ($datos['nodos_evaluados'] ?? [] as $ne) {
                                            if ($ne['id'] === $nid) { $alias = $ne['short_name']; break; }
                                        }
                                        $cantExcl = count($filasExcl);
                                        $subtitulo = $datos['subtitulos_bloques'][$nid] ?? '';
                                        $sub = $subtitulo ? ' — ' . $subtitulo : '';
                                        $iconoPunto = $loop->first ? '🔵' : '🟠';
                                    @endphp
                                    <tr class="fila-cabecera-bloque">
                                        <td colspan="{{ 5 + count($datos['nodos_evaluados'] ?? []) + 1 }}" style="padding: 9px 14px; font-size: 0.73rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--color-texto); background: var(--color-superficie-sutil); border-top: 1px solid var(--color-borde); border-bottom: 1px solid var(--color-borde);">
                                            {{ $iconoPunto }} COBERTURA EXCLUSIVA {{ $alias }}{{ $sub }} ({{ $cantExcl }} NODOS)
                                        </td>
                                    </tr>
                                    @foreach($filasExcl as $fila)
                                        <tr class="fila-dato-cobertura" style="border-bottom: 1px solid var(--color-borde);">
                                            <td class="celda-mono" style="padding: 10px 14px; font-family: 'Ubuntu Mono', monospace; font-size: 0.8rem; color: var(--color-texto-2);">
                                                {{ $fila['id'] }}
                                            </td>
                                            <td style="padding: 10px 14px;">
                                                <strong style="color: var(--color-texto); font-size: 0.88rem;">{{ $fila['short_name'] }}</strong>
                                                <span style="display: block; font-size: 0.73rem; color: var(--color-texto-2);">{{ $fila['long_name'] }}</span>
                                            </td>
                                            <td style="padding: 10px 14px;">
                                                <span class="badge-rol {{ $fila['clase_rol'] }}">{{ $fila['role'] }}</span>
                                            </td>
                                            <td style="padding: 10px 14px;">
                                                <span class="badge-prov {{ $fila['clase_provincia'] }}">{{ $fila['province'] }}</span>
                                                @if(!empty($fila['distancia_texto']))
                                                    <span class="cobertura-distancia-enlace" title="Distancia geodésica directa">
                                                        📏 {{ $fila['distancia_texto'] }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td style="padding: 10px 14px;">
                                                <span class="badge-cob {{ $fila['cobertura_clase'] }}">
                                                    <span class="punto-cob">●</span> {{ $fila['cobertura_label'] }}
                                                </span>
                                            </td>
                                            @foreach($datos['nodos_evaluados'] ?? [] as $ne)
                                                @php $col = $fila['enlaces'][$ne['id']] ?? null; @endphp
                                                <td style="padding: 10px 14px; text-align: center;">
                                                    @if($col)
                                                        <div class="caja-snr {{ $col['clase_calidad'] }}">
                                                            <span class="caja-snr-valor">{{ $col['snr_formateado'] }}</span>
                                                            <span class="caja-snr-meta">{{ $col['snr_avg_formateado'] }}</span>
                                                        </div>
                                                    @else
                                                        <span style="color: var(--color-texto-3); font-weight: bold;">—</span>
                                                    @endif
                                                </td>
                                            @endforeach
                                            <td style="padding: 10px 14px; text-align: right; font-size: 0.75rem; color: var(--color-texto-2); font-family: 'Inter', sans-serif;">
                                                {{ $fila['ultimo_contacto'] }}
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Leyenda Inferior de Calidad SNR y Pie de Tabla -->
                <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 1rem; padding: 12px 16px; background: var(--color-superficie-sutil); border-top: 1px solid var(--color-borde); font-size: 0.75rem; color: var(--color-texto-2);">
                    <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 0.85rem;">
                        <span style="font-weight: 700; color: var(--color-texto);">Calidad de SNR:</span>
                        <span style="display: inline-flex; align-items: center; gap: 4px;">
                            <span style="width: 8px; height: 8px; border-radius: 50%; background: var(--color-acento);"></span>
                            ≥ +10 dB (Excelente)
                        </span>
                        <span style="display: inline-flex; align-items: center; gap: 4px;">
                            <span style="width: 8px; height: 8px; border-radius: 50%; background: #4ade80;"></span>
                            +5 a +9.9 dB (Muy buena)
                        </span>
                        <span style="display: inline-flex; align-items: center; gap: 4px;">
                            <span style="width: 8px; height: 8px; border-radius: 50%; background: #f59e0b;"></span>
                            0 a +4.9 dB (Buena / Límite)
                        </span>
                        @if(($datos['filtro'] ?? '') === 'todos')
                            <span style="display: inline-flex; align-items: center; gap: 4px;">
                                <span style="width: 8px; height: 8px; border-radius: 50%; background: #ef4444;"></span>
                                &lt; 0 dB (Límite / Marginal)
                            </span>
                        @endif
                    </div>
                    <div style="font-style: italic; color: var(--color-texto-3);">
                        Sur Nodos en Mallas · Generado desde telemetría y trazas RF reales de la red Meshtastic (Enlaces directos 100% / 0 saltos RF)
                    </div>
                </div>
            </div>
        @else
            <!-- Estado Inicial / Vacío sin búsqueda -->
            <div style="text-align: center; padding: 3rem 1.5rem; background: var(--color-superficie); border: 1px dashed var(--color-borde); border-radius: 14px;">
                <svg aria-hidden="true" width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="color: var(--color-enlace); margin-bottom: 1rem;">
                    <path d="M5 12.55a11 11 0 0 1 14.08 0"></path>
                    <path d="M1.42 9a16 16 0 0 1 21.16 0"></path>
                    <path d="M8.53 16.11a6 6 0 0 1 6.95 0"></path>
                    <line x1="12" y1="20" x2="12.01" y2="20"></line>
                </svg>
                <h2 style="font-size: 1.35rem; font-weight: 700; color: var(--color-texto); margin-bottom: 0.5rem;">
                    Introduce hasta 3 nodos para auditar su cobertura
                </h2>
                <p style="font-size: 0.95rem; color: var(--color-texto-2); max-width: 580px; margin: 0 auto 1.5rem auto;">
                    Al teclear el ID o alias en el buscador superior se autocompletará la lista. Puedes seleccionar tu propio nodo o comparar 2 o 3 repetidores para obtener la matriz completa de enlaces directos.
                </p>
                <button
                    type="button"
                    onclick="cargarEjemploSierraLijar();"
                    class="btn-cobertura-principal"
                    style="display: inline-flex; align-items: center; gap: 0.5rem;"
                >
                    <span>⚡ Ver ejemplo real: CA01 y CA05</span>
                </button>
            </div>
        @endif
    </div>

    <!-- Scripts del Buscador Reactivo, Pills Dinámicas, Modal y Descargas -->
    <script>
        let timerBusqueda = null;
        let nodosActivos = [
            @foreach($nodosSeleccionados as $nid)
                @php
                    $infoN = null;
                    if (!empty($datos['nodos_evaluados'])) {
                        foreach ($datos['nodos_evaluados'] as $ne) {
                            if ($ne['id'] === $nid) { $infoN = $ne; break; }
                        }
                    }
                    $alias = $infoN['short_name'] ?? strtoupper(substr(ltrim($nid, '!'), -4));
                @endphp
                { id: '{{ $nid }}', alias: '{{ $alias }}' },
            @endforeach
        ];

        function mostrarModalCarga() {
            const m = document.getElementById('modal-cargando-cobertura');
            if (m) m.style.display = 'flex';
        }

        function ocultarModalCarga() {
            const m = document.getElementById('modal-cargando-cobertura');
            if (m) m.style.display = 'none';
        }

        function renderizarPills() {
            const contenedor = document.getElementById('contenedor-pills-nodos');
            const inputHidden = document.getElementById('input-hidden-nodes');
            const cont = document.getElementById('contador-nodos');
            const btnConsultar = document.getElementById('btn-consultar-cobertura');
            const inputBuscar = document.getElementById('input-buscar-nodo');

            const ids = nodosActivos.map(n => n.id);
            if (inputHidden) inputHidden.value = ids.join(',');
            if (cont) cont.textContent = nodosActivos.length;

            if (btnConsultar) {
                const inputVal = (inputBuscar?.value || '').trim();
                btnConsultar.disabled = (nodosActivos.length === 0 && inputVal.length === 0);
            }

            if (!contenedor) return;
            contenedor.innerHTML = '';

            if (nodosActivos.length === 0) {
                contenedor.innerHTML = `
                    <span id="texto-sin-nodos" style="font-size: 0.85rem; color: var(--color-texto-3); font-style: italic;">
                        Ningún nodo seleccionado. Escribe abajo para buscar tu nodo o repetidor.
                    </span>
                `;
                return;
            }

            nodosActivos.forEach(n => {
                const pill = document.createElement('div');
                pill.className = 'pill-nodo-seleccionado';
                pill.dataset.nodeId = n.id;
                pill.innerHTML = `
                    <span class="pill-nodo-mono">${n.id}</span>
                    <strong class="pill-nodo-alias">${n.alias || n.id}</strong>
                    <button type="button" class="btn-quitar-nodo" onclick="quitarNodo('${n.id}');" aria-label="Quitar nodo ${n.alias || n.id}">×</button>
                `;
                contenedor.appendChild(pill);
            });
        }

        function ejecutarConsulta() {
            if (nodosActivos.length === 0) return;
            mostrarModalCarga();
            const filtro = document.querySelector('input[name="filter"]:checked')?.value || 'snr_positivo';
            const ids = nodosActivos.map(n => n.id).join(',');
            window.location.href = `/mi-cobertura?nodes=${encodeURIComponent(ids)}&filter=${encodeURIComponent(filtro)}`;
        }

        function anadirNodoYConsultar(id, alias) {
            const yaExiste = nodosActivos.some(n => n.id.toLowerCase() === id.toLowerCase());
            if (!yaExiste) {
                if (nodosActivos.length >= 3) {
                    nodosActivos = [{ id, alias: alias || id }];
                } else {
                    nodosActivos.push({ id, alias: alias || id });
                }
            }
            renderizarPills();
            ejecutarConsulta();
        }

        function anadirNodoSinConsultar(id, alias) {
            if (nodosActivos.length >= 3) {
                alert('Puedes seleccionar un máximo de 3 nodos para comparar a la vez.');
                return;
            }
            const yaExiste = nodosActivos.some(n => n.id.toLowerCase() === id.toLowerCase());
            if (!yaExiste) {
                nodosActivos.push({ id, alias: alias || id });
                renderizarPills();
            }
            const inputBuscar = document.getElementById('input-buscar-nodo');
            if (inputBuscar) {
                inputBuscar.value = '';
                inputBuscar.focus();
            }
            const desplegable = document.getElementById('lista-sugerencias-nodos');
            if (desplegable) desplegable.style.display = 'none';
        }

        function quitarNodo(id) {
            nodosActivos = nodosActivos.filter(n => n.id.toLowerCase() !== id.toLowerCase());
            renderizarPills();
            if (nodosActivos.length > 0) {
                ejecutarConsulta();
            } else {
                mostrarModalCarga();
                window.location.href = '/mi-cobertura';
            }
        }

        function cambiarFiltro(nuevoFiltro) {
            if (nodosActivos.length > 0) {
                mostrarModalCarga();
                const ids = nodosActivos.map(n => n.id).join(',');
                window.location.href = `/mi-cobertura?nodes=${encodeURIComponent(ids)}&filter=${encodeURIComponent(nuevoFiltro)}`;
            }
        }

        function cargarEjemploSierraLijar() {
            mostrarModalCarga();
            window.location.href = '/mi-cobertura?nodes=!30618df9,!63760c00&filter=snr_positivo';
        }

        function enInputBuscar(valor) {
            clearTimeout(timerBusqueda);
            const desplegable = document.getElementById('lista-sugerencias-nodos');
            const spinner = document.getElementById('spinner-busqueda-inline');
            const btn = document.getElementById('btn-consultar-cobertura');

            if (btn) {
                btn.disabled = (nodosActivos.length === 0 && (!valor || valor.trim().length === 0));
            }

            if (!valor || valor.trim().length < 2) {
                desplegable.style.display = 'none';
                spinner.style.display = 'none';
                return;
            }

            spinner.style.display = 'block';
            timerBusqueda = setTimeout(async () => {
                try {
                    const resp = await fetch('/mi-cobertura/buscar?q=' + encodeURIComponent(valor.trim()));
                    const items = await resp.json();
                    renderizarSugerencias(items);
                } catch (e) {
                    console.error('Error buscando nodos:', e);
                } finally {
                    spinner.style.display = 'none';
                }
            }, 180);
        }

        function renderizarSugerencias(items) {
            const desplegable = document.getElementById('lista-sugerencias-nodos');
            desplegable.innerHTML = '';

            if (!items || items.length === 0) {
                desplegable.innerHTML = '<li style="padding: 10px 14px; font-size: 0.85rem; color: var(--color-texto-3); font-style: italic;">Sin coincidencias en la red</li>';
                desplegable.style.display = 'block';
                return;
            }

            items.forEach(item => {
                const li = document.createElement('li');
                li.className = 'item-sugerencia-nodo';
                li.dataset.nodeId = item.id;
                li.dataset.alias = item.short_name || '';
                const yaSeleccionado = nodosActivos.some(n => n.id.toLowerCase() === item.id.toLowerCase());

                li.innerHTML = `
                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 0.5rem;">
                        <div style="flex: 1;">
                            <span style="font-family: 'Ubuntu Mono', monospace; font-size: 0.8rem; color: var(--color-texto-2);">${item.id}</span>
                            <strong style="color: var(--color-texto); font-size: 0.9rem; margin-left: 0.35rem;">${item.short_name || ''}</strong>
                            <span style="font-size: 0.78rem; color: var(--color-texto-2); margin-left: 0.35rem;">${item.long_name || ''}</span>
                        </div>
                        <div style="display: flex; gap: 0.4rem; align-items: center;">
                            <span class="badge-rol badge-rol-client" style="font-size: 0.65rem;">${item.role || 'CLIENT'}</span>
                            <span class="badge-prov badge-prov-general" style="font-size: 0.65rem;">${item.province || 'General'}</span>
                            ${yaSeleccionado ? '<span style="font-size: 0.85rem; color: var(--color-acento); font-weight: bold;" title="Ya seleccionado">✓</span>' : `
                                <button type="button" class="btn-comparar-nodo-inline" style="background: var(--color-superficie-sutil); border: 1px solid var(--color-borde-control); border-radius: 4px; font-size: 0.7rem; font-weight: 600; padding: 2px 7px; cursor: pointer; color: var(--color-texto);" title="Añadir a la comparación sin consultar">
                                    + Comparar
                                </button>
                            `}
                        </div>
                    </div>
                `;

                li.onclick = (e) => {
                    const btnComparar = e.target.closest('.btn-comparar-nodo-inline');
                    if (btnComparar) {
                        e.stopPropagation();
                        anadirNodoSinConsultar(item.id, item.short_name);
                        return;
                    }

                    anadirNodoYConsultar(item.id, item.short_name);
                };

                desplegable.appendChild(li);
            });

            desplegable.style.display = 'block';
        }

        function enKeydownBuscar(event) {
            if (event.key === 'Escape') {
                document.getElementById('lista-sugerencias-nodos').style.display = 'none';
            } else if (event.key === 'Enter') {
                event.preventDefault();
                const desplegable = document.getElementById('lista-sugerencias-nodos');
                const primeraSugerencia = desplegable ? desplegable.querySelector('.item-sugerencia-nodo') : null;

                if (desplegable && desplegable.style.display !== 'none' && primeraSugerencia && primeraSugerencia.dataset.nodeId) {
                    anadirNodoYConsultar(primeraSugerencia.dataset.nodeId, primeraSugerencia.dataset.alias || '');
                } else {
                    const inputVal = (document.getElementById('input-buscar-nodo')?.value || '').trim();
                    if (inputVal) {
                        anadirNodoYConsultar(inputVal, inputVal);
                    } else if (nodosActivos.length > 0) {
                        ejecutarConsulta();
                    }
                }
            }
        }

        function prepararEnvioFormulario(e) {
            if (e) e.preventDefault();
            const inputVal = (document.getElementById('input-buscar-nodo')?.value || '').trim();
            if (inputVal) {
                anadirNodoYConsultar(inputVal, inputVal);
                return false;
            }
            if (nodosActivos.length > 0) {
                ejecutarConsulta();
            }
            return false;
        }

        async function descargarFichero(url, fallbackName) {
            mostrarModalCarga();
            try {
                const resp = await fetch(url);
                if (!resp.ok) {
                    throw new Error('Error al preparar el informe: ' + resp.status);
                }
                const blob = await resp.blob();
                const dispo = resp.headers.get('Content-Disposition') || '';
                let filename = fallbackName;
                const match = dispo.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/);
                if (match && match[1]) {
                    filename = match[1].replace(/['"]/g, '').trim();
                }
                const blobUrl = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = blobUrl;
                a.download = filename;
                document.body.appendChild(a);
                a.click();
                a.remove();
                setTimeout(() => window.URL.revokeObjectURL(blobUrl), 1000);
            } catch (err) {
                console.error('Error descargando informe:', err);
                alert('Hubo un error al preparar la descarga. Por favor, inténtalo de nuevo.');
            } finally {
                ocultarModalCarga();
            }
        }

        function copiarEnlaceCompartir(url) {
            const targetUrl = url || window.location.href;
            const feedbackExito = () => {
                const txt = document.getElementById('texto-btn-copiar');
                if (txt) {
                    const original = txt.textContent;
                    txt.textContent = '¡Copiado!';
                    setTimeout(() => { txt.textContent = original; }, 2000);
                }
            };

            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(targetUrl).then(feedbackExito).catch(() => {
                    copiarConFallback(targetUrl, feedbackExito);
                });
            } else {
                copiarConFallback(targetUrl, feedbackExito);
            }
        }

        function copiarConFallback(texto, onExito) {
            try {
                const textArea = document.createElement('textarea');
                textArea.value = texto;
                textArea.style.position = 'fixed';
                textArea.style.top = '-9999px';
                textArea.style.left = '-9999px';
                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();
                const copiado = document.execCommand('copy');
                document.body.removeChild(textArea);
                if (copiado) {
                    if (onExito) onExito();
                    return;
                }
            } catch (e) {
                // Fallback final a prompt
            }
            prompt('Copia este enlace directo:', texto);
        }

        // Cerrar sugerencias al hacer clic fuera
        document.addEventListener('click', (e) => {
            if (!e.target.closest('#form-cobertura')) {
                const d = document.getElementById('lista-sugerencias-nodos');
                if (d) d.style.display = 'none';
            }
        });

        // Inicializar renderizado de pills
        document.addEventListener('DOMContentLoaded', () => {
            renderizarPills();
        });
    </script>
</x-layout>
