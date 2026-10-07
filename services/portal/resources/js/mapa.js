/**
 * resources/js/mapa.js
 *
 * Interactividad ligera y accesible para el mapa provincial de Andalucía:
 * - Foco, navegación por teclado y tooltip de detalles.
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
    const botonesVentana = contenedor.querySelectorAll('.btn-ventana');

    // Estado local en memoria
    let datosActuales = null;
    let ventanaActiva = new URLSearchParams(window.location.search).get('ventana') === '24h' ? '24h' : '7d';

    /**
     * Muestra el panel con los datos de una provincia
     */
    function mostrarPanel(code, evt) {
        if (!panel) return;

        const pData = datosActuales && datosActuales.provinces
            ? datosActuales.provinces.find(p => p.code === code)
            : null;

        const grupo = document.getElementById(`prov-${code}`);
        const nombre = (pData && pData.name) || (grupo && grupo.dataset.name) || code;
        const total = (datosActuales && datosActuales.total_andalucia) || 0;
        const nodos = pData ? pData.nodes : 0;
        const pct = total > 0 ? ((nodos / total) * 100).toFixed(1) : '0';

        if (panelNombre) panelNombre.textContent = nombre;
        if (panelRecuento) panelRecuento.textContent = `${nodos} nodos (${pct}% del total)`;

        if (pData && pData.avg !== null && pData.avg !== undefined) {
            if (panelSaturacion) panelSaturacion.textContent = `${pData.avg}%`;
            if (panelMax) panelMax.textContent = `${pData.max}%`;
            
            const rCount = (pData.groups && pData.groups.routers && pData.groups.routers.n) || 0;
            const cCount = (pData.groups && pData.groups.clients && pData.groups.clients.n) || 0;
            if (panelDetalle) {
                panelDetalle.textContent = `Medida por ${rCount} routers y ${cCount} clientes en 12 h`;
            }
        } else {
            if (panelSaturacion) panelSaturacion.textContent = 'Sin datos';
            if (panelMax) panelMax.textContent = '—';
            if (panelDetalle) panelDetalle.textContent = 'Sin datos de telemetría en las últimas 12 horas';
        }

        // Posicionar panel cerca del ratón o elemento
        panel.style.display = 'block';
        panel.setAttribute('aria-hidden', 'false');

        if (evt && evt.clientX && evt.clientY) {
            const rect = contenedor.getBoundingClientRect();
            let x = evt.clientX - rect.left + 15;
            let y = evt.clientY - rect.top + 15;

            // Evitar salir del contenedor horizontalmente
            if (x + 290 > rect.width) {
                x = x - 310;
            }
            panel.style.left = `${Math.max(10, x)}px`;
            panel.style.top = `${Math.max(10, y)}px`;
        } else {
            panel.style.left = '20px';
            panel.style.top = '20px';
        }
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

    /**
     * Actualiza el DOM con los datos recibidos de la API
     */
    function actualizarMapa(datos) {
        datosActuales = datos;

        const total = typeof datos.total_andalucia === 'number' ? datos.total_andalucia : 0;
        if (totalNodosEl && typeof datos.total_andalucia === 'number') {
            totalNodosEl.textContent = new Intl.NumberFormat('es-ES').format(datos.total_andalucia);
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
                        const satText = p.avg !== null ? `${p.avg}%` : 'sin datos';
                        path.setAttribute('aria-label', `${p.name}: ${p.nodes} nodos, saturación ${satText}`);
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
                        colNodos.textContent = new Intl.NumberFormat('es-ES').format(p.nodes);
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
                        const texto = p.level === 'green' ? 'Holgado' :
                                      (p.level === 'orange' ? 'Cargado' :
                                      (p.level === 'red' ? 'Saturado' : 'Sin datos'));
                        colEstado.innerHTML = `<span class="chip chip-${nivel}"><span aria-hidden="true" style="font-weight: 800; font-size: 0.75rem;">${icono}</span> <span>${texto}</span></span>`;
                    }
                }
            });
        }
    }

    /**
     * Carga los datos de la ventana seleccionada
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
                ventanaLabel.textContent = v === '24h' ? 'Últimas 24 horas' : 'Últimos 7 días';
            }

            const url = new URL(window.location.href);
            if (v === '24h') {
                url.searchParams.set('ventana', '24h');
            } else {
                url.searchParams.delete('ventana');
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
