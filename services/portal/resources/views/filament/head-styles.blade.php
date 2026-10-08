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
</style>
