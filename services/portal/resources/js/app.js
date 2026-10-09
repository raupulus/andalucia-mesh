/**
 * app.js (services/portal/resources/js/app.js)
 *
 * Lógica esencial de interfaz: conmutador de modo claro/oscuro, selector de idioma y menú responsive.
 * Sin dependencias pesadas ni rastreadores de terceros (cumple RN-06 y RN-48).
 */

import './mapa.js';

document.addEventListener('DOMContentLoaded', () => {
    // 1. Conmutador de Tema Claro / Oscuro (escritorio y móvil)
    const toggleTema = () => {
        const html = document.documentElement;
        const currentTheme = html.getAttribute('data-theme') || 'dark';
        const nextTheme = currentTheme === 'dark' ? 'light' : 'dark';
        html.setAttribute('data-theme', nextTheme);
        try {
            localStorage.setItem('snm_theme', nextTheme);
        } catch (e) {}
    };

    const btnTema = document.getElementById('btn-tema');
    if (btnTema) {
        btnTema.addEventListener('click', toggleTema);
    }
    const btnsTemaMovil = document.querySelectorAll('.btn-tema-movil');
    btnsTemaMovil.forEach(btn => btn.addEventListener('click', toggleTema));

    // 2. Menú Desplegable Móvil
    const btnMenuMovil = document.getElementById('btn-menu-movil');
    const menuMovil = document.getElementById('menu-movil');
    if (btnMenuMovil && menuMovil) {
        btnMenuMovil.addEventListener('click', () => {
            const expanded = btnMenuMovil.getAttribute('aria-expanded') === 'true';
            btnMenuMovil.setAttribute('aria-expanded', !expanded);
            menuMovil.style.display = expanded ? 'none' : 'block';
        });
    }

    // 3. Menú Desplegable Extras (Revisa tu nodo, Sugerencias)
    const btnExtras = document.getElementById('btn-extras-nav');
    const dropdownExtras = document.getElementById('dropdown-extras-nav');
    if (btnExtras && dropdownExtras) {
        btnExtras.addEventListener('click', (e) => {
            e.stopPropagation();
            const isOpen = dropdownExtras.style.display === 'block';

            // Cerrar cualquier dropdown de idioma abierto
            document.querySelectorAll('.dropdown-idiomas').forEach(d => {
                d.style.display = 'none';
            });
            document.querySelectorAll('.btn-idioma-redondo').forEach(b => {
                b.setAttribute('aria-expanded', 'false');
            });

            dropdownExtras.style.display = isOpen ? 'none' : 'block';
            btnExtras.setAttribute('aria-expanded', !isOpen);
        });
    }

    // 4. Selector de Idioma con bandera redonda y menú desplegable (RN-48)
    const setupSelectorIdioma = (btnId, dropdownId) => {
        const btn = document.getElementById(btnId);
        const dropdown = document.getElementById(dropdownId);
        if (!btn || !dropdown) return;

        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            const isOpen = dropdown.style.display === 'block';

            // Cerrar dropdown de extras si está abierto
            if (dropdownExtras) dropdownExtras.style.display = 'none';
            if (btnExtras) btnExtras.setAttribute('aria-expanded', 'false');

            // Cerrar cualquier otro dropdown abierto
            document.querySelectorAll('.dropdown-idiomas').forEach(d => {
                if (d !== dropdown) d.style.display = 'none';
            });
            document.querySelectorAll('.btn-idioma-redondo').forEach(b => {
                if (b !== btn) b.setAttribute('aria-expanded', 'false');
            });

            dropdown.style.display = isOpen ? 'none' : 'block';
            btn.setAttribute('aria-expanded', !isOpen);
        });
    };

    setupSelectorIdioma('btn-idioma-nav', 'dropdown-idioma-nav');
    setupSelectorIdioma('btn-idioma-movil', 'dropdown-idioma-movil');

    // Cerrar desplegables al hacer clic fuera del selector
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.selector-idioma-wrapper')) {
            document.querySelectorAll('.dropdown-idiomas').forEach(d => {
                d.style.display = 'none';
            });
            document.querySelectorAll('.btn-idioma-redondo').forEach(b => {
                b.setAttribute('aria-expanded', 'false');
            });
        }
        if (!e.target.closest('.dropdown-extras-wrapper')) {
            if (dropdownExtras) dropdownExtras.style.display = 'none';
            if (btnExtras) btnExtras.setAttribute('aria-expanded', 'false');
        }
    });

    // Cerrar desplegables al pulsar la tecla Escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            document.querySelectorAll('.dropdown-idiomas').forEach(d => {
                d.style.display = 'none';
            });
            document.querySelectorAll('.btn-idioma-redondo').forEach(b => {
                b.setAttribute('aria-expanded', 'false');
            });
            if (dropdownExtras) dropdownExtras.style.display = 'none';
            if (btnExtras) btnExtras.setAttribute('aria-expanded', 'false');
        }
    });

    // Guardar preferencia de idioma en localStorage sin cookies (RN-06 / RN-48)
    const itemsIdioma = document.querySelectorAll('.dropdown-idioma-item');
    itemsIdioma.forEach(item => {
        item.addEventListener('click', () => {
            const lang = item.getAttribute('data-lang');
            if (lang) {
                try {
                    localStorage.setItem('portal_locale', lang);
                } catch (err) {}
            }
        });
    });

    // 4. Recordar preferencia de idioma en el navegador sin cookies (RN-06 / RN-48)
    try {
        const savedLocale = localStorage.getItem('portal_locale');
        const currentDocLocale = (document.documentElement.lang || 'es').split('-')[0].toLowerCase();
        const hasLangParam = new URLSearchParams(window.location.search).has('lang');

        if (!hasLangParam && savedLocale && ['es', 'en', 'pt'].includes(savedLocale) && savedLocale !== currentDocLocale) {
            const targetUrl = new URL(window.location.href);
            targetUrl.searchParams.set('lang', savedLocale);
            window.location.replace(targetUrl.toString());
        }
    } catch (e) {}

    // 5. Gestión del aviso flotante global de entorno en pruebas (x-aviso-pruebas)
    const avisoContenido = document.getElementById('aviso-pruebas-contenido');
    const btnMinimizar = document.getElementById('btn-minimizar-aviso');
    const btnEntendido = document.getElementById('btn-entendido-aviso');
    const btnExpandir = document.getElementById('btn-expandir-aviso');

    if (avisoContenido && btnExpandir) {
        const STORAGE_KEY = 'snm_aviso_pruebas_minimizado';

        const aplicarEstado = (minimizado) => {
            if (minimizado) {
                avisoContenido.style.display = 'none';
                btnExpandir.style.display = 'inline-flex';
            } else {
                avisoContenido.style.display = 'flex';
                btnExpandir.style.display = 'none';
            }
        };

        // Comprobar preferencia previa en sessionStorage
        let estaMinimizado = false;
        try {
            estaMinimizado = sessionStorage.getItem(STORAGE_KEY) === '1';
        } catch (e) {}

        aplicarEstado(estaMinimizado);

        const minimizar = () => {
            aplicarEstado(true);
            try {
                sessionStorage.setItem(STORAGE_KEY, '1');
            } catch (e) {}
        };

        const expandir = () => {
            aplicarEstado(false);
            try {
                sessionStorage.removeItem(STORAGE_KEY);
            } catch (e) {}
        };

        if (btnMinimizar) btnMinimizar.addEventListener('click', minimizar);
        if (btnEntendido) btnEntendido.addEventListener('click', minimizar);
        btnExpandir.addEventListener('click', expandir);
    }

    // 6. Formateo de fechas y timestamps a la hora local del navegador
    const formatearFechasLocales = (contenedor = document) => {
        const elementos = contenedor.querySelectorAll('time.fecha-local, [data-fecha-local]');
        elementos.forEach((el) => {
            const raw = el.getAttribute('datetime') || el.getAttribute('data-fecha-local') || el.textContent.trim();
            if (!raw || raw === '—' || raw === '-') return;

            let strIso = raw.trim();
            // Normalizar formatos con espacio como "2026-10-08 21:20:54+02" -> "2026-10-08T21:20:54+02:00"
            if (strIso.includes(' ') && !strIso.includes('T')) {
                strIso = strIso.replace(' ', 'T');
            }
            if (/[+-]\d{2}$/.test(strIso)) {
                strIso += ':00';
            }

            const fecha = new Date(strIso);
            if (isNaN(fecha.getTime())) return;

            const formato = el.getAttribute('data-formato') || 'completo';
            const yyyy = fecha.getFullYear();
            const mm = String(fecha.getMonth() + 1).padStart(2, '0');
            const dd = String(fecha.getDate()).padStart(2, '0');
            const hh = String(fecha.getHours()).padStart(2, '0');
            const min = String(fecha.getMinutes()).padStart(2, '0');
            const ss = String(fecha.getSeconds()).padStart(2, '0');

            let textoFormateado = `${yyyy}-${mm}-${dd} ${hh}:${min}:${ss}`;
            if (formato === 'sin-segundos') {
                textoFormateado = `${yyyy}-${mm}-${dd} ${hh}:${min}`;
            } else if (formato === 'hora-corta') {
                textoFormateado = `${hh}:${min}`;
            } else if (formato === 'fecha') {
                textoFormateado = `${yyyy}-${mm}-${dd}`;
            } else if (formato === 'hora') {
                textoFormateado = `${hh}:${min}:${ss}`;
            }

            if (!el.getAttribute('title')) {
                el.setAttribute('title', raw);
            }

            el.textContent = textoFormateado;
        });

        const elementosTitle = contenedor.querySelectorAll('[data-title-fecha-local]');
        elementosTitle.forEach((el) => {
            const raw = el.getAttribute('data-title-fecha-local');
            if (!raw) return;

            let strIso = raw.trim();
            if (strIso.includes(' ') && !strIso.includes('T')) {
                strIso = strIso.replace(' ', 'T');
            }
            if (/[+-]\d{2}$/.test(strIso)) {
                strIso += ':00';
            }

            const fecha = new Date(strIso);
            if (isNaN(fecha.getTime())) return;

            const yyyy = fecha.getFullYear();
            const mm = String(fecha.getMonth() + 1).padStart(2, '0');
            const dd = String(fecha.getDate()).padStart(2, '0');
            const hh = String(fecha.getHours()).padStart(2, '0');
            const min = String(fecha.getMinutes()).padStart(2, '0');
            const ss = String(fecha.getSeconds()).padStart(2, '0');
            el.setAttribute('title', `${yyyy}-${mm}-${dd} ${hh}:${min}:${ss}`);
        });
    };

    window.formatearFechasLocales = formatearFechasLocales;
    formatearFechasLocales();
});
