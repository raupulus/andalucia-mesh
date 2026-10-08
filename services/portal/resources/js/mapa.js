/**
 * resources/js/mapa.js
 *
 * Interactividad ligera y accesible para el mapa provincial de Andalucía:
 * - Carga instantánea con datos serializados por el servidor (o fetch de respaldo).
 * - Foco, navegación por teclado, clic y tooltip de detalles.
 * - Conmutación entre ventana de 7 días y 24 horas vía fetch a /api/v1/stats/provinces.
 * - Refresco automático cada 5 minutos si la pestaña está activa.
 */

document.addEventListener('DOMContentLoaded', () => {
    const contenedor = document.getElementById('contenedor-mapa-andalucia');
    if (!contenedor) return;

    const panel = document.getElementById('mapa-panel-detalle');
    const panelNombre = document.getElementById('panel-provincia-nombre');
    const panelRecuento = document.getElementById('panel-provincia-recuento');
    const panelSaturacion = document.getElementById('panel-provincia-saturacion');
    const panelMax = document.getElementById('panel-provincia-max');
    const panelDetalle = document.getElementById('panel-provincia-detalle-nodos');

    const totalNodosEl = document.getElementById('mapa-total-nodos');
    const ventanaLabel = document.getElementById('mapa-ventana-label');
    const actualizadoTexto = document.getElementById('mapa-actualizado-texto');
    const botonesVentana = contenedor.querySelectorAll('.btn-ventana');

    // Diccionario i18n con respaldo en español
    let i18n = {
        locale: 'es-ES',
        panel_loading: 'Cargando datos…',
        querying: 'Consultando estadísticas en tiempo real…',
        nodes_count: ':count nodos (:pct% del total)',
        measured_by: 'Medida por :routers routers y :clients clientes en 12 h',
        estimation: 'Estimación con telemetría de las últimas 12 horas',
        status_nodata: 'Sin datos',
        no_telemetry_12h: 'Sin datos de telemetría en las últimas 12 horas',
        window_30m: 'Últimos 30 minutos',
        window_1h: 'Última hora',
        window_6h: 'Últimas 6 horas',
        window_12h: 'Últimas 12 horas',
        window_1d: 'Últimas 24 horas',
        window_24h: 'Últimas 24 horas',
        window_7d: 'Últimos 7 días',
        updated: 'actualizado :time',
        updated_recently: 'actualizado recientemente',
        aria_province: ':name: :count nodos, saturación :saturation',
        status_clear: 'Holgado',
        status_busy: 'Cargado',
        status_saturated: 'Saturado',
    };

    const i18nEl = document.getElementById('mapa-i18n');
    if (i18nEl && i18nEl.textContent) {
        try {
            i18n = Object.assign(i18n, JSON.parse(i18nEl.textContent));
        } catch (e) {
            console.error('Error al inicializar i18n del mapa:', e);
        }
    }

    /**
     * Retorna la etiqueta formateada para una clave de ventana dada
     */
    function obtenerVentanaLabel(v) {
        switch (v) {
            case '30m': return i18n.window_30m || 'Últimos 30 minutos';
            case '1h': return i18n.window_1h || 'Última hora';
            case '6h': return i18n.window_6h || 'Últimas 6 horas';
            case '12h': return i18n.window_12h || 'Últimas 12 horas';
            case '1d':
            case '24h': return i18n.window_1d || i18n.window_24h || 'Últimas 24 horas';
            case '7d': return i18n.window_7d || 'Últimos 7 días';
            default: return i18n.window_30m || 'Últimos 30 minutos';
        }
    }

    // Estado local en memoria (30m por defecto)
    let datosActuales = null;
    const urlVentana = new URLSearchParams(window.location.search).get('ventana');
    const ventanasValidas = ['30m', '1h', '6h', '12h', '1d', '24h', '7d'];
    let ventanaActiva = (urlVentana && ventanasValidas.includes(urlVentana)) ? urlVentana : '30m';
    if (ventanaActiva === '24h') {
        ventanaActiva = '1d';
    }

    const cajaMapa = document.getElementById('caja-mapa-svg') || (panel && panel.parentElement) || contenedor;

    /**
     * Posiciona el panel flotante de detalles relativo a la caja del mapa
     * o en la esquina superior izquierda si se navega por teclado / sin coordenadas.
     */
    function posicionarPanel(evt) {
        if (!panel) return;
        panel.style.display = 'block';
        panel.setAttribute('aria-hidden', 'false');

        if (evt && typeof evt.clientX === 'number' && typeof evt.clientY === 'number') {
            const rect = cajaMapa.getBoundingClientRect();
            const panelWidth = panel.offsetWidth || 280;
            const panelHeight = panel.offsetHeight || 170;

            let x = evt.clientX - rect.left + 15;
            let y = evt.clientY - rect.top + 15;

            // Si sobrepasa por la derecha, invertir horizontalmente a la izquierda del cursor
            if (x + panelWidth > rect.width - 15) {
                x = evt.clientX - rect.left - panelWidth - 15;
            }

            // Si sobrepasa por abajo, invertir verticalmente hacia arriba del cursor
            if (y + panelHeight > rect.height - 15) {
                y = evt.clientY - rect.top - panelHeight - 15;
            }

            // Confinar estrictamente dentro de los límites visibles de la caja
            const maxX = Math.max(10, rect.width - panelWidth - 10);
            const maxY = Math.max(10, rect.height - panelHeight - 10);
            x = Math.max(10, Math.min(x, maxX));
            y = Math.max(10, Math.min(y, maxY));

            panel.style.left = `${Math.round(x)}px`;
            panel.style.top = `${Math.round(y)}px`;
        } else {
            panel.style.left = '20px';
            panel.style.top = '20px';
        }
    }

    /**
     * Muestra el panel con los datos de una provincia
     */
    function mostrarPanel(code, evt) {
        if (!panel) return;

        const grupo = document.getElementById(`prov-${code}`);
        const nombrePorDefecto = (grupo && grupo.dataset.name) || code;

        // Si los datos todavía no han llegado o están vacíos
        if (!datosActuales || !Array.isArray(datosActuales.provinces)) {
            if (panelNombre) panelNombre.textContent = nombrePorDefecto;
            if (panelRecuento) panelRecuento.textContent = i18n.panel_loading;
            if (panelSaturacion) panelSaturacion.textContent = '…';
            if (panelMax) panelMax.textContent = '—';
            if (panelDetalle) panelDetalle.textContent = i18n.querying;
            posicionarPanel(evt);
            return;
        }

        const pData = datosActuales.provinces.find(p => p.code === code);
        const nombre = (pData && pData.name) || nombrePorDefecto;
        const total = (typeof datosActuales.total_andalucia === 'number') ? datosActuales.total_andalucia : 0;
        const nodos = pData ? pData.nodes : 0;
        const pct = total > 0 ? ((nodos / total) * 100).toFixed(1) : '0';

        if (panelNombre) panelNombre.textContent = nombre;
        if (panelRecuento) {
            panelRecuento.textContent = i18n.nodes_count
                .replace(':count', new Intl.NumberFormat(i18n.locale).format(nodos))
                .replace(':pct', pct);
        }

        if (pData && pData.avg !== null && pData.avg !== undefined) {
            if (panelSaturacion) panelSaturacion.textContent = `${pData.avg}%`;
            if (panelMax) panelMax.textContent = `${pData.max}%`;

            const rCount = (pData.groups && pData.groups.routers && pData.groups.routers.n) || 0;
            const cCount = (pData.groups && pData.groups.clients && pData.groups.clients.n) || 0;
            if (panelDetalle) {
                if (rCount > 0 || cCount > 0) {
                    panelDetalle.textContent = i18n.measured_by
                        .replace(':routers', rCount)
                        .replace(':clients', cCount);
                } else {
                    panelDetalle.textContent = i18n.estimation;
                }
            }
        } else {
            if (panelSaturacion) panelSaturacion.textContent = i18n.status_nodata;
            if (panelMax) panelMax.textContent = '—';
            if (panelDetalle) panelDetalle.textContent = i18n.no_telemetry_12h;
        }

        posicionarPanel(evt);
    }

    function ocultarPanel() {
        if (!panel) return;
        panel.style.display = 'none';
        panel.setAttribute('aria-hidden', 'true');
    }

    // Escuchar eventos en las provincias
    const paths = contenedor.querySelectorAll('.provincia-path');
    paths.forEach(path => {
        const code = path.dataset.code;

        path.addEventListener('mouseenter', (e) => mostrarPanel(code, e));
        path.addEventListener('mousemove', (e) => mostrarPanel(code, e));
        path.addEventListener('mouseleave', ocultarPanel);

        path.addEventListener('click', (e) => {
            mostrarPanel(code, e);
        });

        path.addEventListener('focus', (e) => mostrarPanel(code, null));
        path.addEventListener('blur', ocultarPanel);

        path.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                mostrarPanel(code, null);
            } else if (e.key === 'Escape') {
                ocultarPanel();
            }
        });
    });

    // Cerrar panel al hacer clic fuera del contenedor (para dispositivos táctiles)
    document.addEventListener('click', (e) => {
        if (!contenedor.contains(e.target)) {
            ocultarPanel();
        }
    });

    /**
     * Actualiza el DOM con los datos recibidos de la API o renderizados inicialmente
     */
    function actualizarMapa(datos) {
        datosActuales = datos;

        const total = typeof datos.total_andalucia === 'number' ? datos.total_andalucia : 0;
        if (totalNodosEl && typeof datos.total_andalucia === 'number') {
            totalNodosEl.textContent = new Intl.NumberFormat(i18n.locale).format(datos.total_andalucia);
        }

        if (actualizadoTexto && ventanaLabel) {
            const vLabel = obtenerVentanaLabel(ventanaActiva);
            actualizadoTexto.innerHTML = `<span id="mapa-ventana-label">${vLabel}</span> · ${i18n.updated_recently}`;
        }

        if (datos.provinces && Array.isArray(datos.provinces)) {
            datos.provinces.forEach(p => {
                const grupo = document.getElementById(`prov-${p.code}`);
                if (grupo) {
                    const path = grupo.querySelector('.provincia-path');
                    const textoBurbuja = grupo.querySelector('.burbuja-texto');

                    if (textoBurbuja) {
                        textoBurbuja.textContent = p.nodes;
                    }

                    if (path) {
                        path.classList.remove('nivel-green', 'nivel-orange', 'nivel-red', 'nivel-nodata');
                        path.classList.add(`nivel-${p.level || 'nodata'}`);
                        const satText = p.avg !== null ? `${p.avg}%` : i18n.status_nodata.toLowerCase();
                        path.setAttribute('aria-label', i18n.aria_province
                            .replace(':name', p.name)
                            .replace(':count', p.nodes)
                            .replace(':saturation', satText));
                    }
                }

                // Actualizar fila de la tabla accesible
                const fila = document.getElementById(`fila-prov-${p.code}`);
                if (fila) {
                    const colNodos = fila.querySelector('.col-nodos');
                    const colPct = fila.querySelector('.col-pct');
                    const colAvg = fila.querySelector('.col-avg');
                    const colEstado = fila.querySelector('.col-estado');

                    if (colNodos) {
                        colNodos.textContent = new Intl.NumberFormat(i18n.locale).format(p.nodes);
                    }
                    if (colPct) {
                        const pct = total > 0 ? ((p.nodes / total) * 100).toFixed(1) : '0';
                        colPct.textContent = `${pct} %`;
                    }
                    if (colAvg) {
                        colAvg.textContent = p.avg !== null && p.avg !== undefined ? `${p.avg} %` : '—';
                    }
                    if (colEstado) {
                        const nivel = p.level === 'green' ? 'correcto' :
                                      (p.level === 'orange' ? 'aviso' :
                                      (p.level === 'red' ? 'critico' : 'neutro'));
                        const icono = nivel === 'correcto' ? '✓' :
                                      (nivel === 'aviso' ? '▲' :
                                      (nivel === 'critico' ? '✕' : '—'));
                        const texto = p.level === 'green' ? i18n.status_clear :
                                      (p.level === 'orange' ? i18n.status_busy :
                                      (p.level === 'red' ? i18n.status_saturated : i18n.status_nodata));
                        colEstado.innerHTML = `<span class="chip chip-${nivel}"><span aria-hidden="true" style="font-weight: 800; font-size: 0.75rem;">${icono}</span> <span>${texto}</span></span>`;
                    }
                }
            });
        }
    }

    /**
     * Carga los datos de la ventana seleccionada vía API
     */
    function cargarDatos(ventana) {
        fetch(`/api/v1/stats/provinces?window=${ventana}`)
            .then(res => {
                if (!res.ok) throw new Error(`HTTP ${res.status}`);
                return res.json();
            })
            .then(data => {
                actualizarMapa(data);
            })
            .catch(() => {
                // Silencioso ante errores de red; la vista renderizada en el servidor sirve de respaldo
            });
    }

    // Inicializar datos con el JSON renderizado en el servidor si está presente
    const datosInicialesEl = document.getElementById('mapa-datos-iniciales');
    if (datosInicialesEl && datosInicialesEl.textContent) {
        try {
            const parsed = JSON.parse(datosInicialesEl.textContent);
            if (parsed && Array.isArray(parsed.provinces) && parsed.provinces.length > 0) {
                datosActuales = parsed;
                actualizarMapa(datosActuales);
            }
        } catch (e) {
            console.error('Error al inicializar datos del mapa:', e);
        }
    }

    // Si no vinieron datos iniciales válidos, consultar de inmediato a la API
    if (!datosActuales || !Array.isArray(datosActuales.provinces) || datosActuales.provinces.length === 0) {
        cargarDatos(ventanaActiva);
    }

    // Conmutador de ventana 7d / 24h
    botonesVentana.forEach(btn => {
        btn.addEventListener('click', () => {
            const v = btn.dataset.ventana;
            if (v === ventanaActiva) return;

            ventanaActiva = v;
            botonesVentana.forEach(b => {
                const activo = b.dataset.ventana === v;
                b.classList.toggle('activo', activo);
                b.setAttribute('aria-pressed', activo ? 'true' : 'false');
            });

            if (ventanaLabel) {
                ventanaLabel.textContent = obtenerVentanaLabel(v);
            }

            const url = new URL(window.location.href);
            if (v === '30m') {
                url.searchParams.delete('ventana');
            } else {
                url.searchParams.set('ventana', v);
            }
            window.history.replaceState({}, '', url.toString());

            cargarDatos(v);
        });
    });

    // Refresco periódico cada 5 min si la pestaña está activa
    setInterval(() => {
        if (document.visibilityState === 'visible') {
            cargarDatos(ventanaActiva);
        }
    }, 300000);
});
