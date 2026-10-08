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
        const currentTheme = html.getAttribute('data-theme') || 'light';
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

    // 3. Selector de Idioma con bandera redonda y menú desplegable (RN-48)
    const setupSelectorIdioma = (btnId, dropdownId) => {
        const btn = document.getElementById(btnId);
        const dropdown = document.getElementById(dropdownId);
        if (!btn || !dropdown) return;

        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            const isOpen = dropdown.style.display === 'block';

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

    // Cerrar desplegable al hacer clic fuera del selector
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.selector-idioma-wrapper')) {
            document.querySelectorAll('.dropdown-idiomas').forEach(d => {
                d.style.display = 'none';
            });
            document.querySelectorAll('.btn-idioma-redondo').forEach(b => {
                b.setAttribute('aria-expanded', 'false');
            });
        }
    });

    // Cerrar desplegable al pulsar la tecla Escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            document.querySelectorAll('.dropdown-idiomas').forEach(d => {
                d.style.display = 'none';
            });
            document.querySelectorAll('.btn-idioma-redondo').forEach(b => {
                b.setAttribute('aria-expanded', 'false');
            });
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
});
