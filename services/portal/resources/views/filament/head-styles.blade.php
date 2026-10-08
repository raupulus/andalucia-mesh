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
        width: 100%;
        margin-left: auto;
        margin-right: auto;
        border-radius: 1rem;
        box-shadow: 0 10px 25px -5px rgba(44, 45, 60, 0.08), 0 8px 10px -6px rgba(44, 45, 60, 0.04);
    }

    /* Pantallas simples compactas (ej. Login) */
    .fi-simple-main:not([class*="fi-width-"]),
    .fi-simple-main.fi-width-lg {
        max-width: 28rem;
    }

    /* En la página de perfil de operador, conceder ancho generoso de modal (4xl / 56rem) en pantallas de escritorio */
    .fi-simple-main.fi-width-4xl {
        max-width: 56rem !important;
    }

    .fi-simple-main.fi-width-3xl {
        max-width: 48rem !important;
    }

    /* ==============================================================================
     * Centrado visual y apilado vertical del avatar de perfil de operador
     * ============================================================================== */
    .fi-simple-page [data-field-wrapper]:has(.fi-fo-file-upload-avatar),
    .fi-simple-page .fi-operator-avatar-wrapper,
    [data-field-wrapper]:has(.fi-fo-file-upload-avatar),
    .fi-operator-avatar-wrapper {
        margin-left: auto !important;
        margin-right: auto !important;
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        justify-content: center !important;
        text-align: center !important;
        width: 100% !important;
    }

    .fi-simple-page [data-field-wrapper]:has(.fi-fo-file-upload-avatar) .fi-fo-field-label-col,
    .fi-simple-page .fi-operator-avatar-wrapper .fi-fo-field-label-col,
    .fi-simple-page [data-field-wrapper]:has(.fi-fo-file-upload-avatar) .fi-fo-field-label-ctn,
    .fi-simple-page .fi-operator-avatar-wrapper .fi-fo-field-label-ctn,
    .fi-simple-page [data-field-wrapper]:has(.fi-fo-file-upload-avatar) .fi-fo-field-label,
    .fi-simple-page .fi-operator-avatar-wrapper .fi-fo-field-label {
        display: flex !important;
        justify-content: center !important;
        align-items: center !important;
        text-align: center !important;
        width: 100% !important;
        margin-bottom: 0.5rem !important;
    }

    /* Columna de contenido: flex-direction column obligatorio para que avatar y helper-text no se coloquen en horizontal */
    .fi-simple-page [data-field-wrapper]:has(.fi-fo-file-upload-avatar) .fi-fo-field-content-col,
    .fi-simple-page .fi-operator-avatar-wrapper .fi-fo-field-content-col {
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        justify-content: center !important;
        text-align: center !important;
        width: 100% !important;
        margin-left: auto !important;
        margin-right: auto !important;
        gap: 0.6rem !important;
    }

    /* Contenedor del avatar: centrado horizontal independiente */
    .fi-simple-page .fi-fo-file-upload-avatar,
    .fi-simple-page .fi-operator-avatar-upload,
    .fi-operator-avatar-wrapper .fi-fo-file-upload,
    .fi-operator-avatar-wrapper .fi-fo-file-upload-avatar {
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        justify-content: center !important;
        width: 100% !important;
        margin-left: auto !important;
        margin-right: auto !important;
    }

    .fi-simple-page .fi-fo-file-upload-avatar .fi-fo-file-upload-input-ctn,
    .fi-simple-page .fi-operator-avatar-upload .fi-fo-file-upload-input-ctn,
    .fi-operator-avatar-wrapper .fi-fo-file-upload-input-ctn {
        display: flex !important;
        justify-content: center !important;
        align-items: center !important;
        margin-left: auto !important;
        margin-right: auto !important;
        width: 8rem !important;
        height: 8rem !important;
    }

    .fi-simple-page .fi-fo-file-upload-avatar .filepond--root,
    .fi-simple-page .fi-operator-avatar-upload .filepond--root,
    .fi-operator-avatar-wrapper .filepond--root {
        margin-left: auto !important;
        margin-right: auto !important;
        width: 8rem !important;
        height: 8rem !important;
    }

    /* Texto auxiliar debajo del circulo: centrado debajo sin invadir el avatar */
    .fi-simple-page [data-field-wrapper]:has(.fi-fo-file-upload-avatar) [id$="-helper-text"],
    .fi-simple-page .fi-operator-avatar-wrapper [id$="-helper-text"],
    .fi-simple-page [data-field-wrapper]:has(.fi-fo-file-upload-avatar) .fi-fo-field-helper-text,
    .fi-simple-page .fi-operator-avatar-wrapper .fi-fo-field-helper-text,
    [data-field-wrapper]:has(.fi-fo-file-upload-avatar) [id$="-helper-text"],
    .fi-operator-avatar-wrapper [id$="-helper-text"] {
        display: flex !important;
        justify-content: center !important;
        align-items: center !important;
        text-align: center !important;
        width: 100% !important;
        margin-top: 0.5rem !important;
        margin-left: auto !important;
        margin-right: auto !important;
    }

    .fi-simple-page [data-field-wrapper]:has(.fi-fo-file-upload-avatar) [id$="-helper-text"] .fi-sc-component,
    .fi-simple-page [data-field-wrapper]:has(.fi-fo-file-upload-avatar) [id$="-helper-text"] .fi-sc-text,
    .fi-simple-page .fi-operator-avatar-wrapper [id$="-helper-text"] .fi-sc-component,
    .fi-simple-page .fi-operator-avatar-wrapper [id$="-helper-text"] .fi-sc-text,
    .fi-operator-avatar-wrapper [id$="-helper-text"] * {
        text-align: center !important;
        display: inline-block !important;
        width: 100% !important;
        font-size: 0.8125rem !important;
        line-height: 1.4 !important;
        color: #94a3b8 !important;
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

    /* --- ESTILOS DECORATIVOS DEL DASHBOARD DE OPERADOR --- */

    /* 4 Tarjetas de Métricas de Malla: fondo verde corporativo, borde blanco y reflejo animado sutil */
    .fi-wi-stats-overview .fi-wi-stats-overview-stat {
        position: relative !important;
        overflow: hidden !important;
        background: linear-gradient(140deg, #04210e 0%, #004d20 35%, #007A33 75%, #09401d 100%) !important;
        border: 1.5px solid #FFFFFF !important;
        border-radius: 1rem !important;
        box-shadow: 0 10px 25px -4px rgba(0, 122, 51, 0.45), 0 0 10px rgba(255, 255, 255, 0.18) !important;
        transition: transform 0.2s ease, box-shadow 0.2s ease !important;
    }

    .fi-wi-stats-overview .fi-wi-stats-overview-stat:hover {
        transform: translateY(-2px);
        box-shadow: 0 14px 28px -4px rgba(0, 122, 51, 0.55), 0 0 14px rgba(255, 255, 255, 0.28) !important;
    }

    /* Reflejo animado degradado a blanco moviéndose */
    .fi-wi-stats-overview .fi-wi-stats-overview-stat::after {
        content: '';
        position: absolute;
        top: -60%;
        left: -90%;
        width: 65%;
        height: 220%;
        background: linear-gradient(
            to right,
            rgba(255, 255, 255, 0) 0%,
            rgba(255, 255, 255, 0.08) 25%,
            rgba(255, 255, 255, 0.38) 50%,
            rgba(255, 255, 255, 0.08) 75%,
            rgba(255, 255, 255, 0) 100%
        );
        transform: rotate(25deg);
        pointer-events: none;
        z-index: 10;
        animation: fi-stat-sweep-reflection 5.5s ease-in-out infinite;
    }

    @keyframes fi-stat-sweep-reflection {
        0% {
            left: -90%;
        }
        35%, 100% {
            left: 160%;
        }
    }

    /* Textos y cifras dentro de las 4 tarjetas */
    .fi-wi-stats-overview .fi-wi-stats-overview-stat-content {
        position: relative;
        z-index: 5;
    }

    .fi-wi-stats-overview .fi-wi-stats-overview-stat-label {
        color: #FFFFFF !important;
        font-weight: 600 !important;
        font-size: 0.85rem !important;
        letter-spacing: -0.01em !important;
        opacity: 0.95 !important;
        text-shadow: 0 1px 3px rgba(0, 0, 0, 0.35) !important;
    }

    .fi-wi-stats-overview .fi-wi-stats-overview-stat-value {
        color: #FFFFFF !important;
        font-weight: 800 !important;
        font-size: 1.85rem !important;
        line-height: 1.2 !important;
        text-shadow: 0 2px 5px rgba(0, 0, 0, 0.45) !important;
    }

    .fi-wi-stats-overview .fi-wi-stats-overview-stat-description {
        color: #d1fae5 !important;
        font-weight: 500 !important;
        font-size: 0.8rem !important;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3) !important;
    }

    .fi-wi-stats-overview .fi-wi-stats-overview-stat-description svg,
    .fi-wi-stats-overview .fi-wi-stats-overview-stat-label-ctn svg {
        color: #a7f3d0 !important;
    }

    .fi-wi-stats-overview .fi-wi-stats-overview-stat-chart {
        opacity: 0.85;
    }

    /* Encabezados de widgets */
    .fi-widget-header-flex {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        width: 100%;
    }
    @media (min-width: 640px) {
        .fi-widget-header-flex {
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
        }
    }

    .fi-widget-title-group {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        flex-wrap: wrap;
    }

    .fi-widget-icon {
        font-size: 1.25rem;
        line-height: 1;
    }

    .fi-widget-title {
        font-size: 1.05rem;
        font-weight: 700;
        letter-spacing: -0.01em;
    }

    .fi-widget-subtitle {
        font-size: 0.75rem;
        opacity: 0.65;
    }

    .fi-widget-actions-group {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    /* Pills de cabecera */
    .fi-widget-pill {
        display: inline-flex;
        align-items: center;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.15rem 0.55rem;
        border-radius: 9999px;
        line-height: 1.2;
    }
    .fi-widget-pill-neutral {
        background-color: rgba(148, 163, 184, 0.15);
        color: inherit;
    }
    .fi-widget-pill-success {
        background-color: rgba(16, 185, 129, 0.15);
        color: #10b981;
    }
    .dark .fi-widget-pill-success {
        color: #67EA94;
    }
    .fi-widget-pill-danger {
        background-color: rgba(244, 63, 94, 0.15);
        color: #f43f5e;
    }

    /* Contadores de estado */
    .fi-status-counter-group {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-size: 0.75rem;
    }
    .fi-status-counter {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        font-weight: 600;
    }
    .fi-status-counter-ok { color: #10b981; }
    .dark .fi-status-counter-ok { color: #67EA94; }
    .fi-status-counter-danger { color: #f43f5e; }
    .fi-status-counter-pending { opacity: 0.65; }

    .fi-status-dot-sm {
        width: 0.45rem;
        height: 0.45rem;
        border-radius: 9999px;
        display: inline-block;
    }

    /* Botón de refresco manual */
    .fi-btn-refresh {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.3rem 0.65rem;
        border-radius: 0.375rem;
        background-color: rgba(0, 122, 51, 0.12);
        color: #007A33;
        border: 1px solid rgba(0, 122, 51, 0.25);
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .dark .fi-btn-refresh {
        background-color: rgba(103, 234, 148, 0.12);
        color: #67EA94;
        border-color: rgba(103, 234, 148, 0.25);
    }
    .fi-btn-refresh:hover {
        background-color: rgba(0, 122, 51, 0.2);
    }
    .dark .fi-btn-refresh:hover {
        background-color: rgba(103, 234, 148, 0.2);
    }

    /* Tabla responsiva y filas */
    .fi-table-responsive-container {
        overflow-x: auto;
        width: 100%;
        margin-top: 0.75rem;
    }

    .fi-dashboard-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 0.825rem;
        text-align: left;
    }

    .fi-th {
        padding: 0.65rem 0.85rem;
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        opacity: 0.6;
        border-bottom: 1px solid rgba(148, 163, 184, 0.2);
        white-space: nowrap;
    }

    .fi-tr {
        transition: background-color 0.12s ease;
    }
    .fi-tr:hover {
        background-color: rgba(148, 163, 184, 0.05);
    }

    .fi-td {
        padding: 0.75rem 0.85rem;
        border-bottom: 1px solid rgba(148, 163, 184, 0.1);
        vertical-align: middle;
    }

    /* Puntos de estado de nodo */
    .fi-status-dot {
        width: 0.6rem;
        height: 0.6rem;
        border-radius: 9999px;
        flex-shrink: 0;
    }
    .fi-status-dot-online {
        background-color: #10b981;
        box-shadow: 0 0 6px rgba(16, 185, 129, 0.6);
    }
    .fi-status-dot-offline {
        background-color: #f43f5e;
    }

    /* Badge de alimentación */
    .fi-power-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.2rem 0.55rem;
        border-radius: 0.375rem;
    }
    .fi-power-badge-grid {
        background-color: rgba(16, 185, 129, 0.12);
        color: #10b981;
        border: 1px solid rgba(16, 185, 129, 0.25);
    }
    .dark .fi-power-badge-grid {
        color: #67EA94;
    }

    /* Barras de progreso de batería y ChUtil */
    .fi-battery-container,
    .fi-chutil-container {
        min-width: 6.5rem;
        max-width: 8.5rem;
    }

    .fi-progress-track {
        height: 0.35rem;
        width: 100%;
        background-color: rgba(148, 163, 184, 0.2);
        border-radius: 9999px;
        overflow: hidden;
    }

    .fi-progress-fill {
        height: 100%;
        border-radius: 9999px;
        transition: width 0.3s ease;
    }
    .fi-progress-verde { background-color: #10b981; }
    .dark .fi-progress-verde { background-color: #67EA94; }
    .fi-progress-amarillo { background-color: #f59e0b; }
    .fi-progress-rojo { background-color: #f43f5e; }

    /* Chips de texto */
    .fi-chip-verde { color: #10b981; font-weight: 600; }
    .dark .fi-chip-verde { color: #67EA94; }
    .fi-chip-amarillo { color: #f59e0b; font-weight: 600; }
    .fi-chip-rojo { color: #f43f5e; font-weight: 600; }

    .fi-text-verde { color: #10b981; }
    .dark .fi-text-verde { color: #67EA94; }
    .fi-text-amarillo { color: #f59e0b; }
    .fi-text-rojo { color: #f43f5e; }

    /* Badges tipo pill para microservicios */
    .fi-badge-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.15rem 0.55rem;
        border-radius: 9999px;
        white-space: nowrap;
    }
    .fi-badge-success {
        background-color: rgba(16, 185, 129, 0.12);
        color: #10b981;
    }
    .dark .fi-badge-success { color: #67EA94; }
    .fi-badge-warning {
        background-color: rgba(245, 158, 11, 0.12);
        color: #f59e0b;
    }
    .fi-badge-danger {
        background-color: rgba(244, 63, 94, 0.12);
        color: #f43f5e;
    }
    .fi-badge-neutral {
        background-color: rgba(148, 163, 184, 0.15);
        color: inherit;
    }

    /* Botón inspeccionar / diagnóstico */
    .fi-btn-inspect {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.25rem 0.6rem;
        border-radius: 0.375rem;
        background-color: rgba(148, 163, 184, 0.1);
        color: inherit;
        border: 1px solid rgba(148, 163, 184, 0.2);
        text-decoration: none;
        transition: all 0.15s ease;
    }
    .fi-btn-inspect:hover {
        background-color: rgba(0, 122, 51, 0.15);
        border-color: #007A33;
        color: #007A33;
    }
    .dark .fi-btn-inspect:hover {
        background-color: rgba(103, 234, 148, 0.15);
        border-color: #67EA94;
        color: #67EA94;
    }

    /* Alerta y estado vacío del dashboard */
    .fi-dashboard-alert {
        display: flex;
        align-items: flex-start;
        gap: 0.6rem;
        padding: 0.85rem 1rem;
        border-radius: 0.5rem;
        font-size: 0.825rem;
        margin-top: 0.5rem;
    }
    .fi-dashboard-alert-warning {
        background-color: rgba(245, 158, 11, 0.1);
        border: 1px solid rgba(245, 158, 11, 0.25);
        color: #d97706;
    }
    .dark .fi-dashboard-alert-warning { color: #fbbf24; }

    .fi-dashboard-empty {
        padding: 2.5rem 1rem;
        text-align: center;
        border-radius: 0.5rem;
        background-color: rgba(148, 163, 184, 0.04);
        border: 1px dashed rgba(148, 163, 184, 0.2);
        margin-top: 0.5rem;
    }

    /* Timeline de transiciones / eventos de plataforma */
    .fi-transitions-wrapper {
        margin-top: 1.5rem;
        padding-top: 1rem;
        border-top: 1px solid rgba(148, 163, 184, 0.15);
    }
    .fi-transitions-header {
        display: flex;
        align-items: baseline;
        gap: 0.5rem;
        margin-bottom: 0.75rem;
    }
    .fi-transitions-title {
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        opacity: 0.7;
    }
    .fi-transitions-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 0.5rem;
    }
    @media (min-width: 640px) {
        .fi-transitions-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    @media (min-width: 1024px) {
        .fi-transitions-grid {
            grid-template-columns: repeat(4, 1fr);
        }
    }

    .fi-transition-card {
        padding: 0.65rem 0.75rem;
        border-radius: 0.5rem;
        border: 1px solid rgba(148, 163, 184, 0.15);
        background-color: rgba(148, 163, 184, 0.04);
        transition: transform 0.12s ease;
    }
    .fi-transition-card:hover {
        transform: translateY(-1px);
    }
    .fi-transition-ok {
        border-left: 3px solid #10b981;
    }
    .dark .fi-transition-ok {
        border-left-color: #67EA94;
    }
    .fi-transition-fail {
        border-left: 3px solid #f43f5e;
    }

    /* ==============================================================================
     * Selector de idioma en topbar de Filament (idéntico al frontend)
     * ============================================================================== */
    .fi-language-switch-wrapper {
        position: relative !important;
        display: inline-flex !important;
        align-items: center !important;
    }

    .fi-btn-idioma-trigger {
        width: 36px !important;
        height: 36px !important;
        min-width: 36px !important;
        min-height: 36px !important;
        border-radius: 9999px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        cursor: pointer !important;
        background: transparent !important;
        border: 1px solid rgba(156, 163, 175, 0.35) !important;
        padding: 0 !important;
        outline: none !important;
        transition: all 0.15s ease !important;
    }

    .fi-btn-idioma-trigger:hover {
        border-color: #007A33 !important;
        background-color: rgba(0, 122, 51, 0.08) !important;
    }

    .fi-language-dropdown-menu {
        position: absolute !important;
        right: 0 !important;
        top: calc(100% + 8px) !important;
        min-width: 155px !important;
        border-radius: 0.5rem !important;
        padding: 0.35rem 0 !important;
        z-index: 999 !important;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.4), 0 8px 10px -6px rgba(0, 0, 0, 0.2) !important;
        background-color: #FFFFFF !important;
        border: 1px solid #E2E8F0 !important;
    }

    .dark .fi-language-dropdown-menu {
        background-color: #1A1C24 !important;
        border: 1px solid rgba(255, 255, 255, 0.12) !important;
    }

    .fi-lang-item {
        display: flex !important;
        align-items: center !important;
        gap: 0.65rem !important;
        padding: 0.55rem 0.9rem !important;
        text-decoration: none !important;
        font-size: 0.875rem !important;
        font-weight: 500 !important;
        color: #334155 !important;
        transition: background-color 0.12s ease, color 0.12s ease !important;
    }

    .fi-lang-item:hover {
        background-color: #F1F5F9 !important;
        color: #0F172A !important;
    }

    .fi-lang-item.activo {
        font-weight: 700 !important;
        background-color: #F1F5F9 !important;
        color: #007A33 !important;
    }

    .dark .fi-lang-item {
        color: #E2E8F0 !important;
    }

    .dark .fi-lang-item:hover {
        background-color: rgba(255, 255, 255, 0.08) !important;
        color: #FFFFFF !important;
    }

    .dark .fi-lang-item.activo {
        font-weight: 700 !important;
        background-color: rgba(255, 255, 255, 0.12) !important;
        color: #9CF1BA !important;
    }

    /* ==============================================================================
     * Guía Visual de Webhooks y Criterios de Detección (RN-11, RN-32, RN-39)
     * ============================================================================== */
    .fi-wh-guide {
        margin-top: 2rem !important;
        margin-bottom: 2.5rem !important;
        width: 100% !important;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
        display: flex !important;
        flex-direction: column !important;
        gap: 1.75rem !important;
    }

    .fi-wh-header {
        background: linear-gradient(135deg, #f8fafc 0%, #ffffff 50%, #f1f5f9 100%) !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 1rem !important;
        padding: 1.25rem 1.5rem !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03) !important;
        display: flex !important;
        flex-direction: column !important;
        gap: 1rem !important;
    }

    .dark .fi-wh-header {
        background: linear-gradient(135deg, #181924 0%, #202230 50%, #1a1b26 100%) !important;
        border-color: #373a4d !important;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.25) !important;
    }

    /* ==============================================================================
     * Selector de Ámbito Territorial del Dashboard (AmbitoSelectorWidget)
     * ============================================================================== */
    .fi-ambito-wrapper {
        margin-bottom: 1.25rem !important;
        width: 100% !important;
        display: flex !important;
        flex-direction: column !important;
        gap: 0.65rem !important;
    }

    .fi-ambito-header {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        flex-wrap: wrap !important;
        gap: 0.5rem !important;
        padding: 0 0.25rem !important;
    }

    .fi-ambito-title {
        font-size: 0.8rem !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
        color: #64748b !important;
    }
    .dark .fi-ambito-title {
        color: #94a3b8 !important;
    }

    .fi-ambito-context-indicator {
        font-size: 0.8rem !important;
        color: #64748b !important;
        display: inline-flex !important;
        align-items: center !important;
        background: rgba(148, 163, 184, 0.12) !important;
        padding: 0.25rem 0.75rem !important;
        border-radius: 9999px !important;
        border: 1px solid rgba(148, 163, 184, 0.2) !important;
    }
    .dark .fi-ambito-context-indicator {
        color: #cbd5e1 !important;
        background: rgba(30, 41, 59, 0.7) !important;
        border-color: rgba(148, 163, 184, 0.2) !important;
    }

    .fi-ambito-grid {
        display: grid !important;
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 1rem !important;
        width: 100% !important;
    }
    @media (max-width: 768px) {
        .fi-ambito-grid {
            grid-template-columns: 1fr !important;
            gap: 0.75rem !important;
        }
    }

    .fi-ambito-card {
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        gap: 1rem !important;
        padding: 1.15rem 1.4rem !important;
        border-radius: 0.85rem !important;
        text-align: left !important;
        cursor: pointer !important;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
        position: relative !important;
        width: 100% !important;
        box-sizing: border-box !important;
        outline: none !important;
    }
    .fi-ambito-card:focus-visible {
        outline: 2px solid #67EA94 !important;
        outline-offset: 2px !important;
    }

    .fi-ambito-card-inactive {
        background: #ffffff !important;
        border: 2px solid #e2e8f0 !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05) !important;
        opacity: 0.78 !important;
    }
    .dark .fi-ambito-card-inactive {
        background: #1e2029 !important;
        border: 2px solid rgba(148, 163, 184, 0.16) !important;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.25) !important;
        opacity: 0.72 !important;
    }
    .fi-ambito-card-inactive:hover {
        opacity: 1 !important;
        transform: translateY(-2px) !important;
        border-color: rgba(148, 163, 184, 0.45) !important;
    }
    .dark .fi-ambito-card-inactive:hover {
        border-color: rgba(148, 163, 184, 0.35) !important;
        background: #242632 !important;
    }

    .fi-ambito-card-andalucia-active {
        background: linear-gradient(135deg, rgba(0, 122, 51, 0.08) 0%, #ffffff 100%) !important;
        border: 2px solid #007A33 !important;
        box-shadow: 0 4px 16px rgba(0, 122, 51, 0.18) !important;
        opacity: 1 !important;
    }
    .dark .fi-ambito-card-andalucia-active {
        background: linear-gradient(135deg, rgba(0, 122, 51, 0.28) 0%, #1e2029 100%) !important;
        border: 2px solid #67EA94 !important;
        box-shadow: 0 4px 20px rgba(0, 122, 51, 0.45), inset 0 0 16px rgba(103, 234, 148, 0.08) !important;
        opacity: 1 !important;
    }
    .fi-ambito-card-andalucia-active:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 6px 20px rgba(0, 122, 51, 0.25) !important;
    }
    .dark .fi-ambito-card-andalucia-active:hover {
        box-shadow: 0 6px 24px rgba(0, 122, 51, 0.55), inset 0 0 20px rgba(103, 234, 148, 0.12) !important;
    }

    .fi-ambito-card-espana-active {
        background: linear-gradient(135deg, rgba(220, 38, 38, 0.08) 0%, #ffffff 100%) !important;
        border: 2px solid #dc2626 !important;
        box-shadow: 0 4px 16px rgba(220, 38, 38, 0.18) !important;
        opacity: 1 !important;
    }
    .dark .fi-ambito-card-espana-active {
        background: linear-gradient(135deg, rgba(220, 38, 38, 0.28) 0%, #1e2029 100%) !important;
        border: 2px solid #f87171 !important;
        box-shadow: 0 4px 20px rgba(220, 38, 38, 0.4), inset 0 0 16px rgba(248, 113, 113, 0.08) !important;
        opacity: 1 !important;
    }
    .fi-ambito-card-espana-active:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 6px 20px rgba(220, 38, 38, 0.25) !important;
    }
    .dark .fi-ambito-card-espana-active:hover {
        box-shadow: 0 6px 24px rgba(220, 38, 38, 0.5), inset 0 0 20px rgba(248, 113, 113, 0.12) !important;
    }

    .fi-ambito-card-left {
        display: flex !important;
        align-items: center !important;
        gap: 1rem !important;
        min-width: 0 !important;
    }

    .fi-ambito-flag-wrapper {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        flex-shrink: 0 !important;
        filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.25)) !important;
    }

    .fi-ambito-card-info {
        display: flex !important;
        flex-direction: column !important;
        min-width: 0 !important;
        text-align: left !important;
    }

    .fi-ambito-card-title-row {
        display: flex !important;
        align-items: center !important;
        gap: 0.5rem !important;
    }

    .fi-ambito-card-name {
        font-size: 1.05rem !important;
        font-weight: 700 !important;
        color: #0f172a !important;
        line-height: 1.25 !important;
    }
    .dark .fi-ambito-card-name {
        color: #f8fafc !important;
    }

    .fi-ambito-card-desc {
        font-size: 0.78rem !important;
        color: #64748b !important;
        line-height: 1.35 !important;
        margin-top: 0.2rem !important;
    }
    .dark .fi-ambito-card-desc {
        color: #94a3b8 !important;
    }

    .fi-ambito-card-right {
        display: flex !important;
        flex-direction: column !important;
        align-items: flex-end !important;
        gap: 0.45rem !important;
        flex-shrink: 0 !important;
    }

    .fi-ambito-count-badge {
        font-size: 0.75rem !important;
        font-weight: 700 !important;
        font-variant-numeric: tabular-nums !important;
        padding: 0.2rem 0.55rem !important;
        border-radius: 0.375rem !important;
        background: rgba(148, 163, 184, 0.15) !important;
        color: #64748b !important;
        white-space: nowrap !important;
    }
    .dark .fi-ambito-count-badge {
        background: rgba(148, 163, 184, 0.12) !important;
        color: #cbd5e1 !important;
    }
    .fi-ambito-count-badge-andalucia {
        background: rgba(0, 122, 51, 0.18) !important;
        color: #007A33 !important;
        font-weight: 800 !important;
    }
    .dark .fi-ambito-count-badge-andalucia {
        background: rgba(103, 234, 148, 0.18) !important;
        color: #67EA94 !important;
    }
    .fi-ambito-count-badge-espana {
        background: rgba(220, 38, 38, 0.15) !important;
        color: #dc2626 !important;
        font-weight: 800 !important;
    }
    .dark .fi-ambito-count-badge-espana {
        background: rgba(248, 113, 113, 0.18) !important;
        color: #fca5a5 !important;
    }

    .fi-ambito-switch-pill {
        font-size: 0.72rem !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.04em !important;
        padding: 0.32rem 0.8rem !important;
        border-radius: 9999px !important;
        transition: all 0.15s ease !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 0.3rem !important;
        white-space: nowrap !important;
    }
    .fi-ambito-switch-pill-off {
        background: rgba(148, 163, 184, 0.15) !important;
        color: #64748b !important;
        border: 1px dashed rgba(148, 163, 184, 0.4) !important;
    }
    .dark .fi-ambito-switch-pill-off {
        background: rgba(30, 41, 59, 0.5) !important;
        color: #94a3b8 !important;
        border-color: rgba(148, 163, 184, 0.25) !important;
    }
    .fi-ambito-switch-pill-on-andalucia {
        background: #007A33 !important;
        color: #ffffff !important;
        box-shadow: 0 2px 6px rgba(0, 122, 51, 0.35) !important;
    }
    .dark .fi-ambito-switch-pill-on-andalucia {
        background: #15612F !important;
        color: #ffffff !important;
        border: 1px solid #67EA94 !important;
        box-shadow: 0 2px 8px rgba(0, 122, 51, 0.5) !important;
    }
    .fi-ambito-switch-pill-on-espana {
        background: #dc2626 !important;
        color: #ffffff !important;
        box-shadow: 0 2px 6px rgba(220, 38, 38, 0.35) !important;
    }
    .dark .fi-ambito-switch-pill-on-espana {
        background: #991b1b !important;
        color: #ffffff !important;
        border: 1px solid #f87171 !important;
        box-shadow: 0 2px 8px rgba(220, 38, 38, 0.5) !important;
    }
</style>
