/**
 * mapa.js - Cliente interactivo del Mapa de Andalucía Mesh
 *
 * Visualización de alta fluidez en Leaflet 1.9.4 con diagnóstico proactivo de nodos,
 * filtrado por favoritos (localStorage), buscador predictivo, modo en directo
 * y catálogo modal de nodos no optimizados.
 */

import L from 'leaflet';

document.addEventListener('DOMContentLoaded', () => {
    const contenedor = document.getElementById('mapa-lienzo');
    if (!contenedor) return;

    // Configuración base leída desde los atributos de datos del DOM
    const latDefecto = parseFloat(contenedor.dataset.lat || '37.4');
    const lonDefecto = parseFloat(contenedor.dataset.lon || '-4.5');
    const zoomDefecto = parseInt(contenedor.dataset.zoom || '7', 10);
    const dominio = contenedor.dataset.dominio || window.location.hostname;
    const cartoKey = contenedor.dataset.cartoKey || '';
    const cartoUrl = 'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png'
        + (cartoKey ? `?key=${encodeURIComponent(cartoKey)}` : '');

    // 1. Inicialización del Mapa Leaflet
    const map = L.map('mapa-lienzo', {
        center: [latDefecto, lonDefecto],
        zoom: zoomDefecto,
        minZoom: 5,
        maxZoom: 18,
        zoomControl: false,
    });

    // Control de zoom en esquina superior izquierda
    L.control.zoom({ position: 'topleft' }).addTo(map);

    // Capa base de azulejos (CARTO Dark Matter por defecto)
    const capaDark = L.tileLayer(cartoUrl, {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">OpenStreetMap</a> &copy; <a href="https://carto.com/attributions" target="_blank" rel="noopener">CARTO</a>',
        subdomains: 'abcd',
        maxZoom: 19
    }).addTo(map);

    // Capa alternativa satelital (Esri World Imagery)
    const capaSatelite = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
        attribution: '&copy; <a href="https://www.esri.com/" target="_blank" rel="noopener">Esri</a> · DigitalGlobe, Earthstar',
        maxZoom: 19
    });

    // Capa alternativa estándar (OpenStreetMap)
    const capaOSM = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">OpenStreetMap</a>',
        maxZoom: 19
    });

    // Selector de capas base (posicionado bajo la barra superior derecha)
    L.control.layers({
        '🌙 Modo Oscuro': capaDark,
        '🛰️ Satélite': capaSatelite,
        '🗺️ OpenStreetMap': capaOSM,
    }, null, { position: 'topright' }).addTo(map);

    // 2. Estado local
    let nodos = [];
    let marcadoresPorId = {};
    let markerGroup = L.layerGroup().addTo(map);
    let favoritos = new Set(JSON.parse(localStorage.getItem('mesh_favs') || '[]'));
    let filtroSoloFavs = false;
    let nodoSeleccionado = null;
    let liveActivo = false;
    let wsLive = null;

    // 3. Crear HTML para Marcadores SVG Personalizados
    function crearIconoNodo(n) {
        let formaHtml = '';
        if (n.rtr) {
            formaHtml = `<div class="mesh-marker-triangle"></div>`;
        } else if (n.gw) {
            formaHtml = `<div class="mesh-marker-gw"></div>`;
        } else {
            formaHtml = `<div class="mesh-marker-dot ${n.status}"></div>`;
        }

        let warnBadgeHtml = '';
        if (n.warn) {
            warnBadgeHtml = `<div class="mesh-marker-warn-badge ${n.w_lvl === 'critico' ? 'critico' : ''}">▲</div>`;
        }

        return L.divIcon({
            className: 'mesh-marker-custom-icon',
            html: `<div class="mesh-marker-wrap">${formaHtml}${warnBadgeHtml}</div>`,
            iconSize: [22, 22],
            iconAnchor: [11, 11]
        });
    }

    // 4. Formatear Tooltip informativo
    function crearTooltipContenido(n) {
        const vistoMin = Math.round((Date.now() - n.ts * 1000) / 60000);
        let textoVisto = 'hace instantes';
        if (vistoMin >= 60) {
            const horas = Math.floor(vistoMin / 60);
            textoVisto = `hace ${horas}h`;
        } else if (vistoMin > 1) {
            textoVisto = `hace ${vistoMin}m`;
        }

        let badges = `<span style="background: rgba(255,255,255,0.15); padding: 2px 6px; border-radius: 4px; font-size: 0.7rem; font-weight: 700;">${n.role}</span>`;
        if (n.gw) {
            badges += ` <span style="background: rgba(234, 179, 8, 0.25); color: #fde047; padding: 2px 6px; border-radius: 4px; font-size: 0.7rem; font-weight: 700;">GATEWAY MQTT</span>`;
        }
        if (n.warn) {
            badges += ` <span style="background: rgba(245, 158, 11, 0.25); color: #fbbf24; padding: 2px 6px; border-radius: 4px; font-size: 0.7rem; font-weight: 700;">▲ AVISO</span>`;
        }

        return `
            <div style="font-family: inherit; font-size: 0.82rem; min-width: 170px;">
                <div style="font-weight: 700; font-size: 0.95rem; color: #ffffff; margin-bottom: 0.2rem;">${n.long || n.short}</div>
                <div style="font-family: monospace; font-size: 0.75rem; color: #94a3b8; margin-bottom: 0.4rem;">${n.id} · ${n.prov}</div>
                <div style="margin-bottom: 0.4rem;">${badges}</div>
                <div style="font-size: 0.75rem; color: #cbd5e1; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 0.35rem;">
                    <div>HW: <strong>${n.hw}</strong></div>
                    <div>Visto: <strong>${textoVisto}</strong></div>
                </div>
            </div>
        `;
    }

    // 5. Cargar Nodos desde la API / Caché
    async function cargarNodos() {
        try {
            const resp = await fetch('/api/v1/mapa/nodes');
            if (!resp.ok) return;
            nodos = await resp.json();
            renderizarNodos();
        } catch (e) {
            console.error('Error al cargar nodos del mapa:', e);
        }
    }

    // 6. Renderizar Nodos en el Mapa
    function renderizarNodos() {
        markerGroup.clearLayers();
        marcadoresPorId = {};

        nodos.forEach(n => {
            if (filtroSoloFavs && !favoritos.has(n.id)) {
                return;
            }

            const marker = L.marker([n.lat, n.lon], {
                icon: crearIconoNodo(n)
            });

            marker.bindPopup(crearTooltipContenido(n), {
                closeButton: false,
                offset: [0, -10]
            });

            marker.on('mouseover', function () {
                this.openPopup();
            });

            marker.on('click', () => {
                abrirCajonNodo(n);
            });

            markerGroup.addLayer(marker);
            marcadoresPorId[n.id] = marker;
        });
    }

    // 7. Abrir Cajón Lateral de Detalle (Bottom-Left Drawer)
    function abrirCajonNodo(n) {
        nodoSeleccionado = n;
        const drawer = document.getElementById('mapa-drawer');
        if (!drawer) return;

        // Título y favoritos
        const elTitulo = document.getElementById('drawer-node-title');
        if (elTitulo) elTitulo.textContent = n.long || n.short;

        const btnFav = document.getElementById('btn-drawer-fav');
        if (btnFav) {
            btnFav.classList.toggle('active', favoritos.has(n.id));
        }

        // Aviso si está desoptimizado
        const boxWarn = document.getElementById('drawer-warning-box');
        const listWarn = document.getElementById('drawer-warning-list');
        if (boxWarn && listWarn) {
            if (n.warn && n.w_cnt > 0) {
                boxWarn.style.display = 'block';
                listWarn.innerHTML = '';
                (n.w_keys || []).forEach(k => {
                    const li = document.createElement('li');
                    li.textContent = formatearClaveAviso(k);
                    listWarn.appendChild(li);
                });
            } else {
                boxWarn.style.display = 'none';
            }
        }

        // Llenar tabla de propiedades
        document.getElementById('prop-id').textContent = n.id;
        document.getElementById('prop-dec').textContent = n.num || '-';
        document.getElementById('prop-short').textContent = n.short;
        document.getElementById('prop-hw').textContent = n.hw;
        document.getElementById('prop-role').textContent = n.role;
        document.getElementById('prop-fw').textContent = n.fw || 'Desconocido';
        document.getElementById('prop-gw').textContent = n.gw ? 'Sí' : 'No';
        document.getElementById('prop-coord').textContent = `${n.lat}, ${n.lon}`;
        document.getElementById('prop-prov').textContent = n.prov;

        const vistoMin = Math.round((Date.now() - n.ts * 1000) / 60000);
        let textoVisto = 'hace instantes';
        if (vistoMin >= 60) {
            textoVisto = `hace ${Math.floor(vistoMin / 60)}h`;
        } else if (vistoMin > 1) {
            textoVisto = `hace ${vistoMin}m`;
        }
        document.getElementById('prop-seen').textContent = textoVisto;

        // Enlace a revisa tu nodo
        const linkRevisa = document.getElementById('btn-drawer-revisa');
        if (linkRevisa) {
            linkRevisa.href = `/revisa-tu-nodo/${n.id}`;
        }

        drawer.classList.add('open');
    }

    function formatearClaveAviso(clave) {
        const mapaClaves = {
            'hop_limit_alto': 'Hop Limit configurado superior a lo recomendado',
            'client_base_fw': 'CLIENT_BASE ≥ 2.7.17 actúa como ROUTER_LATE',
            'nodeinfo_frecuente': 'NodeInfo emitido con demasiada frecuencia',
            'telemetria_frecuente': 'Telemetría enviada con frecuencia elevada',
            'saltos_excesivos': 'Hop Limit excesivo (> 5 saltos)',
            'saltos_altos': 'Hop Limit superior al recomendado',
        };
        return mapaClaves[clave] || (clave.startsWith('alerta_') ? `Alerta activa: ${clave.replace('alerta_', '')}` : clave);
    }

    // 8. Cerrar Cajón
    const btnCloseDrawer = document.getElementById('btn-drawer-close');
    if (btnCloseDrawer) {
        btnCloseDrawer.addEventListener('click', () => {
            const drawer = document.getElementById('mapa-drawer');
            if (drawer) drawer.classList.remove('open');
            nodoSeleccionado = null;
        });
    }

    // 9. Alternar Favorito
    const btnFavDrawer = document.getElementById('btn-drawer-fav');
    if (btnFavDrawer) {
        btnFavDrawer.addEventListener('click', () => {
            if (!nodoSeleccionado) return;
            const id = nodoSeleccionado.id;
            if (favoritos.has(id)) {
                favoritos.delete(id);
                btnFavDrawer.classList.remove('active');
            } else {
                favoritos.add(id);
                btnFavDrawer.classList.add('active');
            }
            localStorage.setItem('mesh_favs', JSON.stringify(Array.from(favoritos)));
            if (filtroSoloFavs) renderizarNodos();
        });
    }

    // Botón Favoritos en Topbar (Escritorio) y Bottombar (Móvil)
    const btnTopFavs = document.getElementById('btn-toggle-favs');
    const btnTopFavsMobile = document.getElementById('btn-toggle-favs-mobile');

    function toggleFiltroFavoritos() {
        filtroSoloFavs = !filtroSoloFavs;
        if (btnTopFavs) btnTopFavs.classList.toggle('active', filtroSoloFavs);
        if (btnTopFavsMobile) btnTopFavsMobile.classList.toggle('active', filtroSoloFavs);
        renderizarNodos();
    }

    if (btnTopFavs) btnTopFavs.addEventListener('click', toggleFiltroFavoritos);
    if (btnTopFavsMobile) btnTopFavsMobile.addEventListener('click', toggleFiltroFavoritos);

    // 10. Buscador Predictivo
    const inputBuscar = document.getElementById('mapa-search-input');
    const boxResultados = document.getElementById('mapa-search-results');

    if (inputBuscar && boxResultados) {
        inputBuscar.addEventListener('input', (e) => {
            const q = e.target.value.trim().toLowerCase();
            if (q.length < 2) {
                boxResultados.classList.remove('open');
                boxResultados.innerHTML = '';
                return;
            }

            const coincidencias = nodos.filter(n => {
                return (n.id && n.id.toLowerCase().includes(q)) ||
                       (n.short && n.short.toLowerCase().includes(q)) ||
                       (n.long && n.long.toLowerCase().includes(q)) ||
                       (n.prov && n.prov.toLowerCase().includes(q)) ||
                       (n.hw && n.hw.toLowerCase().includes(q)) ||
                       (n.num && String(n.num).includes(q));
            }).slice(0, 15);

            if (coincidencias.length === 0) {
                boxResultados.innerHTML = '<div style="padding: 0.8rem; color: #8b949e; text-align: center; font-size: 0.8rem;">Sin resultados</div>';
                boxResultados.classList.add('open');
                return;
            }

            boxResultados.innerHTML = '';
            coincidencias.forEach(n => {
                const item = document.createElement('div');
                item.className = 'mapa-search-item';
                item.innerHTML = `
                    <div>
                        <div style="font-weight: 700; color: #ffffff; font-size: 0.85rem;">${n.long || n.short}</div>
                        <div style="font-family: monospace; font-size: 0.72rem; color: #8b949e;">${n.id} · ${n.prov} · ${n.role}</div>
                    </div>
                    <div>
                        ${n.warn ? '<span style="color: #f59e0b; font-size: 0.75rem; font-weight: 700;">▲ Aviso</span>' : ''}
                    </div>
                `;
                item.addEventListener('click', () => {
                    boxResultados.classList.remove('open');
                    inputBuscar.value = '';
                    enfocarNodo(n);
                });
                boxResultados.appendChild(item);
            });
            boxResultados.classList.add('open');
        });

        document.addEventListener('click', (e) => {
            if (!e.target.closest('.mapa-search-wrapper')) {
                boxResultados.classList.remove('open');
            }
        });
    }

    function enfocarNodo(n) {
        map.flyTo([n.lat, n.lon], 13, { duration: 1.2 });
        abrirCajonNodo(n);
        const marker = marcadoresPorId[n.id];
        if (marker) {
            marker.openPopup();
        }
    }

    // 11. Modal Central de Diagnóstico
    const modalDiag = document.getElementById('mapa-modal-diag');
    const btnAbrirDiag = document.getElementById('btn-drawer-diag');
    const btnCerrarDiag = document.getElementById('btn-modal-diag-close');

    if (btnAbrirDiag) {
        btnAbrirDiag.addEventListener('click', () => {
            if (nodoSeleccionado) {
                abrirDiagnostico(nodoSeleccionado.id);
            }
        });
    }

    if (btnCerrarDiag && modalDiag) {
        btnCerrarDiag.addEventListener('click', () => {
            modalDiag.classList.remove('open');
        });

        modalDiag.addEventListener('click', (e) => {
            if (e.target === modalDiag) {
                modalDiag.classList.remove('open');
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && modalDiag.classList.contains('open')) {
                modalDiag.classList.remove('open');
            }
        });
    }

    async function abrirDiagnostico(nodeId) {
        if (!modalDiag) return;
        modalDiag.classList.add('open');

        document.getElementById('diag-node-name').textContent = 'Cargando diagnóstico...';
        document.getElementById('diag-node-sub').textContent = '';
        document.getElementById('diag-problems-container').innerHTML = '<div style="color: #94a3b8; font-size: 0.85rem;">Analizando parámetros de nodo...</div>';
        document.getElementById('diag-traffic-container').innerHTML = '';

        try {
            const resp = await fetch(`/api/v1/mapa/node/${nodeId}`);
            if (!resp.ok) throw new Error('No se pudo cargar el diagnóstico');
            const data = await resp.json();
            const n = data.node;

            document.getElementById('diag-node-name').textContent = n.long_name;
            document.getElementById('diag-node-sub').textContent = `${n.id} · ${n.node_num || ''} · ${n.province}`;

            // Hop limit
            const hopBox = document.getElementById('diag-hop-box');
            if (hopBox) {
                hopBox.className = `mapa-hop-box ${n.hop_status}`;
                hopBox.innerHTML = `ℹ️ ${n.hop_mensaje}`;
            }

            // Problemas detectados
            const containerProb = document.getElementById('diag-problems-container');
            containerProb.innerHTML = '';
            if (n.problemas && n.problemas.length > 0) {
                n.problemas.forEach(p => {
                    const card = document.createElement('div');
                    card.className = `mapa-problem-card ${p.severidad === 'critico' ? 'critico' : ''}`;
                    card.innerHTML = `
                        <div class="mapa-problem-card-title">
                            ${p.severidad === 'critico' ? '🔴' : '🟡'} ${p.titulo}
                        </div>
                        <div class="mapa-problem-card-desc">${p.descripcion}</div>
                        ${p.solucion ? `<div class="mapa-problem-card-solution"><strong>Solución:</strong> ${p.solucion}</div>` : ''}
                    `;
                    containerProb.appendChild(card);
                });
            } else {
                containerProb.innerHTML = `
                    <div style="padding: 1rem; background-color: rgba(0, 122, 51, 0.15); border: 1px solid rgba(0, 122, 51, 0.35); border-radius: 0.5rem; color: #4ade80; font-size: 0.85rem;">
                        ✓ No se han detectado anomalías de configuración en este nodo. Cumple las recomendaciones de la red.
                    </div>
                `;
            }

            // Desglose de paquetes 24h
            const containerTraf = document.getElementById('diag-traffic-container');
            containerTraf.innerHTML = '';
            if (n.packets_breakdown && n.packets_breakdown.length > 0) {
                n.packets_breakdown.forEach(row => {
                    const el = document.createElement('div');
                    el.className = 'mapa-breakdown-row';
                    el.innerHTML = `
                        <div class="mapa-breakdown-label">${row.nombre}</div>
                        <div class="mapa-breakdown-bar-wrap">
                            <div class="mapa-breakdown-bar-fill" style="width: ${row.porcentaje}%;"></div>
                        </div>
                        <div class="mapa-breakdown-value">${row.count} (${row.porcentaje}%)</div>
                    `;
                    containerTraf.appendChild(el);
                });
            }

            // Enlaces de pie de modal
            const linkMeshview = document.getElementById('diag-link-meshview');
            if (linkMeshview) linkMeshview.href = n.meshview_url;
            const linkRevisa = document.getElementById('diag-link-revisa');
            if (linkRevisa) linkRevisa.href = n.revisa_nodo_url;

        } catch (e) {
            document.getElementById('diag-problems-container').innerHTML = '<div style="color: #ef4444; font-size: 0.85rem;">Error al cargar diagnóstico del nodo.</div>';
        }
    }

    // 12. Modal de Catálogo "No optimizados" (Escritorio y Móvil)
    const modalCatalog = document.getElementById('mapa-modal-catalog');
    const btnOpenCatalog = document.getElementById('btn-no-optimizados');
    const btnOpenCatalogMobile = document.getElementById('btn-no-optimizados-mobile');
    const btnCloseCatalog = document.getElementById('btn-modal-catalog-close');

    const abrirCatalogoHandler = async () => {
        if (!modalCatalog) return;
        modalCatalog.classList.add('open');
        await cargarCatalogoNoOptimizados();
    };

    if (btnOpenCatalog && modalCatalog) {
        btnOpenCatalog.addEventListener('click', abrirCatalogoHandler);
    }
    if (btnOpenCatalogMobile && modalCatalog) {
        btnOpenCatalogMobile.addEventListener('click', abrirCatalogoHandler);
    }

    if (btnCloseCatalog && modalCatalog) {
        btnCloseCatalog.addEventListener('click', () => {
            modalCatalog.classList.remove('open');
        });
        modalCatalog.addEventListener('click', (e) => {
            if (e.target === modalCatalog) modalCatalog.classList.remove('open');
        });
    }

    async function cargarCatalogoNoOptimizados() {
        const contList = document.getElementById('catalog-list-content');
        if (!contList) return;
        contList.innerHTML = '<div style="color: #94a3b8; font-size: 0.85rem; padding: 1rem;">Cargando catálogo...</div>';

        try {
            const resp = await fetch('/api/v1/mapa/unoptimized');
            if (!resp.ok) throw new Error('Error al cargar');
            const data = await resp.json();
            const items = data.items || [];

            if (items.length === 0) {
                contList.innerHTML = '<div style="color: #4ade80; padding: 1rem;">No hay nodos desoptimizados detectados actualmente.</div>';
                return;
            }

            contList.innerHTML = '';
            items.forEach(it => {
                const row = document.createElement('div');
                row.style.cssText = 'display: flex; align-items: center; justify-content: space-between; padding: 0.75rem 1rem; border-bottom: 1px solid rgba(255,255,255,0.06);';
                row.innerHTML = `
                    <div>
                        <div style="font-weight: 700; color: #ffffff; font-size: 0.9rem;">
                            ${it.w_lvl === 'critico' ? '🔴' : '🟡'} ${it.long || it.short}
                            <span style="font-family: monospace; font-size: 0.75rem; color: #94a3b8; margin-left: 0.4rem;">${it.id} (${it.prov})</span>
                        </div>
                        <div style="font-size: 0.78rem; color: #fbbf24; margin-top: 0.2rem;">
                            ${(it.problemas || []).map(p => p.titulo).join(' · ')}
                        </div>
                    </div>
                    <div>
                        <button type="button" class="mapa-btn-action" style="font-size: 0.75rem; padding: 0.35rem 0.65rem;">
                            Ver en mapa →
                        </button>
                    </div>
                `;
                row.querySelector('button').addEventListener('click', () => {
                    modalCatalog.classList.remove('open');
                    enfocarNodo(it);
                });
                contList.appendChild(row);
            });
        } catch (e) {
            contList.innerHTML = '<div style="color: #ef4444; padding: 1rem;">Error al cargar el listado.</div>';
        }
    }

    // Pestañas en modal catálogo
    const tabBtns = document.querySelectorAll('.mapa-catalog-tab-btn');
    tabBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            tabBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            const target = btn.dataset.tab;
            document.querySelectorAll('.mapa-catalog-tab-pane').forEach(p => {
                p.style.display = p.id === target ? 'block' : 'none';
            });
        });
    });

    // 13. Botones de Control de Mapa
    const btnCentrarAndalucia = document.getElementById('btn-center-andalucia');
    if (btnCentrarAndalucia) {
        btnCentrarAndalucia.addEventListener('click', () => {
            map.flyTo([latDefecto, lonDefecto], zoomDefecto, { duration: 1.0 });
        });
    }

    const btnLocalizar = document.getElementById('btn-locate-me');
    if (btnLocalizar) {
        btnLocalizar.addEventListener('click', () => {
            if ('geolocation' in navigator) {
                navigator.geolocation.getCurrentPosition((pos) => {
                    map.flyTo([pos.coords.latitude, pos.coords.longitude], 12, { duration: 1.2 });
                }, () => {
                    alert('No se pudo obtener tu ubicación.');
                });
            }
        });
    }

    // 14. Control de apertura/cierre de la Leyenda de Nodos
    const legendCard = document.getElementById('mapa-legend');
    const btnCloseLegend = document.getElementById('btn-close-legend');
    const btnOpenLegend = document.getElementById('btn-open-legend');

    if (legendCard && btnCloseLegend && btnOpenLegend) {
        btnCloseLegend.addEventListener('click', () => {
            legendCard.style.display = 'none';
            btnOpenLegend.style.display = 'flex';
        });

        btnOpenLegend.addEventListener('click', () => {
            legendCard.style.display = 'block';
            btnOpenLegend.style.display = 'none';
        });
    }

    // 15. Carga inicial
    cargarNodos();
});
