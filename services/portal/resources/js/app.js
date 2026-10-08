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

    // 3. Selector de Idioma (Frontend, RN-48)
    const selectoresIdioma = document.querySelectorAll('.selector-idioma-select');
    selectoresIdioma.forEach(select => {
        select.addEventListener('change', (e) => {
            const url = e.target.value;
            try {
                const urlObj = new URL(url, window.location.origin);
                const lang = urlObj.searchParams.get('lang');
                if (lang) {
                    localStorage.setItem('portal_locale', lang);
                }
            } catch (err) {}
            window.location.href = url;
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
