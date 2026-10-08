<style>
    /* Tipografía e identidad visual de DESIGN.md */
    body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
    }

    /* Cifras tabulares en tablas, métricas y contadores */
    table, .font-mono, [data-tabular-nums] {
        font-feature-settings: 'tnum' 1;
        font-variant-numeric: tabular-nums;
    }

    /* Centrado y proporciones de la tarjeta de login */
    .fi-simple-main-ctn {
        display: flex;
        flex-direction: column;
        justify-content: center;
        min-height: 100vh;
        padding: 1.5rem 1rem;
    }

    .fi-simple-main {
        max-width: 28rem !important;
        width: 100%;
        margin-left: auto;
        margin-right: auto;
        border-radius: 1rem;
        box-shadow: 0 10px 25px -5px rgba(44, 45, 60, 0.08), 0 8px 10px -6px rgba(44, 45, 60, 0.04);
    }

    /* Cabecera superior con acento institucional verde de Andalucía */
    .fi-topbar {
        border-bottom: 2px solid #007A33 !important;
    }

    /* Modo oscuro: superficies y fondos alineados con DESIGN.md */
    .dark body {
        background-color: #1F2029;
    }

    .dark .fi-section,
    .dark .fi-modal-window,
    .dark .fi-dropdown-panel {
        background-color: #2C2D3C;
        border-color: #3D3E4D;
    }

    /* Radios estándar del sistema visual: 8px para botones y campos */
    .fi-btn,
    .fi-input,
    .fi-select-input,
    .fi-fo-field-wrp {
        border-radius: 0.5rem;
    }

    /* Anillo de foco accesible WCAG AAA según DESIGN.md */
    :focus-visible {
        outline: 2px solid #15612F !important;
        outline-offset: 2px !important;
    }

    .dark :focus-visible {
        outline: 2px solid #67EA94 !important;
        outline-offset: 2px !important;
    }
</style>
