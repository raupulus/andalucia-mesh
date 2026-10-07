/**
 * app.js (services/portal/resources/js/app.js)
 *
 * Lógica esencial de interfaz: conmutador de modo claro/oscuro y menú responsive.
 * Sin dependencias pesadas ni rastreadores de terceros.
 */

import './mapa.js';

document.addEventListener('DOMContentLoaded', () => {
    // 1. Conmutador de Tema Claro / Oscuro
    const btnTema = document.getElementById('btn-tema');
    if (btnTema) {
        btnTema.addEventListener('click', () => {
            const html = document.documentElement;
            const currentTheme = html.getAttribute('data-theme') || 'light';
            const nextTheme = currentTheme === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-theme', nextTheme);
            try {
                localStorage.setItem('snm_theme', nextTheme);
            } catch (e) {}
        });
    }

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
});
