<x-filament-panels::page>
    <script src="/js/mesh-admin.bundle.js"></script>

    <div x-data="meshAdmin()" class="fi-router-admin-page">
        <style>
            /* ==============================================================================
               SISTEMA VISUAL DESIGN.MD: CONSOLA DE GESTIÓN REMOTA DE ROUTERS
               ============================================================================== */
            .fi-router-admin-page {
                width: 100%;
                display: flex;
                flex-direction: column;
                gap: 1.75rem;
                color: #2C2D3C;
                font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            }
            .dark .fi-router-admin-page {
                color: #F0F0F5;
            }

            /* Banner superior de contexto operativo */
            .fi-ra-hero-banner {
                background: linear-gradient(135deg, rgba(0, 122, 51, 0.08) 0%, rgba(103, 234, 148, 0.03) 100%);
                border: 1px solid rgba(0, 122, 51, 0.25);
                border-radius: 1rem;
                padding: 1.25rem 1.75rem;
                display: flex;
                align-items: center;
                justify-content: space-between;
                flex-wrap: wrap;
                gap: 1.25rem;
            }
            .dark .fi-ra-hero-banner {
                background: linear-gradient(135deg, rgba(28, 58, 40, 0.5) 0%, rgba(44, 45, 60, 0.5) 100%);
                border-color: rgba(103, 234, 148, 0.25);
            }
            .fi-ra-hero-title {
                font-size: 1.25rem;
                font-weight: 800;
                color: #007A33;
                display: flex;
                align-items: center;
                gap: 0.6rem;
                letter-spacing: -0.01em;
            }
            .dark .fi-ra-hero-title {
                color: #67EA94;
            }
            .fi-ra-hero-desc {
                font-size: 0.85rem;
                line-height: 1.5;
                color: #515267;
                max-width: 55rem;
                margin-top: 0.25rem;
            }
            .dark .fi-ra-hero-desc {
                color: #C7C8D4;
            }

            /* Tarjetas de superficie elevadas */
            .fi-ra-card {
                background-color: #FFFFFF;
                border: 1px solid #E0E1EB;
                border-radius: 1rem;
                padding: 1.75rem 2rem;
                box-shadow: 0 4px 14px rgba(44, 45, 60, 0.03);
                display: flex;
                flex-direction: column;
                gap: 1.25rem;
            }
            .dark .fi-ra-card {
                background-color: #2C2D3C;
                border-color: #3D3E4D;
                box-shadow: 0 4px 18px rgba(0, 0, 0, 0.25);
            }

            .fi-ra-card-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                flex-wrap: wrap;
                gap: 1rem;
                padding-bottom: 1rem;
                border-bottom: 1px solid #E0E1EB;
            }
            .dark .fi-ra-card-header {
                border-bottom-color: #3D3E4D;
            }

            .fi-ra-title-flex {
                display: flex;
                align-items: center;
                gap: 0.75rem;
            }
            .fi-ra-icon-badge {
                width: 2.5rem;
                height: 2.5rem;
                border-radius: 0.625rem;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                font-size: 1.25rem;
                background: linear-gradient(135deg, rgba(0, 122, 51, 0.12) 0%, rgba(103, 234, 148, 0.2) 100%);
                border: 1px solid rgba(0, 122, 51, 0.2);
                color: #007A33;
                flex-shrink: 0;
            }
            .dark .fi-ra-icon-badge {
                background: linear-gradient(135deg, rgba(103, 234, 148, 0.15) 0%, rgba(0, 122, 51, 0.25) 100%);
                border-color: rgba(103, 234, 148, 0.3);
                color: #67EA94;
            }
            .fi-ra-card-heading {
                font-size: 1.05rem;
                font-weight: 700;
                letter-spacing: -0.01em;
            }
            .fi-ra-card-subheading {
                font-size: 0.8125rem;
                color: #515267;
                margin-top: 0.15rem;
            }
            .dark .fi-ra-card-subheading {
                color: #C7C8D4;
            }

            /* Indicadores de estado de conexión */
            .fi-ra-status-pill {
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                padding: 0.45rem 1rem;
                border-radius: 9999px;
                font-size: 0.8125rem;
                font-weight: 700;
                line-height: 1;
            }
            .fi-ra-status-connected {
                background-color: rgba(16, 185, 129, 0.15);
                color: #007A33;
                border: 1.5px solid rgba(0, 122, 51, 0.35);
            }
            .dark .fi-ra-status-connected {
                background-color: rgba(103, 234, 148, 0.18);
                color: #67EA94;
                border-color: rgba(103, 234, 148, 0.4);
            }
            .fi-ra-status-connecting {
                background-color: rgba(245, 158, 11, 0.15);
                color: #B45309;
                border: 1.5px solid rgba(245, 158, 11, 0.35);
            }
            .dark .fi-ra-status-connecting {
                color: #FCD34D;
            }
            .fi-ra-status-disconnected {
                background-color: rgba(148, 163, 184, 0.15);
                color: #515267;
                border: 1.5px solid rgba(148, 163, 184, 0.3);
            }
            .dark .fi-ra-status-disconnected {
                color: #9FA0B4;
                border-color: #3D3E4D;
            }
            .fi-ra-status-error {
                background-color: rgba(244, 63, 94, 0.15);
                color: #9B1E15;
                border: 1.5px solid rgba(244, 63, 94, 0.35);
            }
            .dark .fi-ra-status-error {
                color: #FFB4AE;
            }

            .fi-ra-dot {
                width: 0.55rem;
                height: 0.55rem;
                border-radius: 9999px;
                display: inline-block;
            }
            .fi-ra-dot-connected {
                background-color: #10B981;
                box-shadow: 0 0 10px #10B981;
                animation: fi-ra-pulse 2s infinite ease-in-out;
            }
            .dark .fi-ra-dot-connected {
                background-color: #67EA94;
                box-shadow: 0 0 10px #67EA94;
            }
            .fi-ra-dot-connecting {
                background-color: #F59E0B;
                animation: fi-ra-pulse 1s infinite ease-in-out;
            }
            .fi-ra-dot-disconnected {
                background-color: #94A3B8;
            }
            .fi-ra-dot-error {
                background-color: #F43F5E;
            }

            @keyframes fi-ra-pulse {
                0%, 100% { opacity: 1; transform: scale(1); }
                50% { opacity: 0.45; transform: scale(1.25); }
            }

            /* Rejilla de formulario oxigenada */
            .fi-ra-form-grid {
                display: grid;
                grid-template-columns: 1fr;
                gap: 1.25rem;
                align-items: end;
            }
            @media (min-width: 640px) {
                .fi-ra-form-grid-2 {
                    grid-template-columns: repeat(2, 1fr);
                }
                .fi-ra-form-grid-3 {
                    grid-template-columns: 1.5fr 1fr 1.2fr;
                }
                .fi-ra-form-grid-4 {
                    grid-template-columns: 1.5fr 1fr 1fr 1.2fr;
                }
            }

            .fi-ra-form-group {
                display: flex;
                flex-direction: column;
                gap: 0.45rem;
            }
            .fi-ra-label {
                font-size: 0.8125rem;
                font-weight: 700;
                color: #2C2D3C;
                letter-spacing: -0.01em;
            }
            .dark .fi-ra-label {
                color: #F0F0F5;
            }

            .fi-ra-select,
            .fi-ra-input {
                width: 100%;
                padding: 0.65rem 0.95rem;
                font-size: 0.875rem;
                border-radius: 0.5rem;
                border: 1.5px solid #8D8EA6;
                background-color: #FFFFFF;
                color: #2C2D3C;
                transition: border-color 0.15s ease, box-shadow 0.15s ease;
                outline: none;
                box-sizing: border-box;
            }
            .dark .fi-ra-select,
            .dark .fi-ra-input {
                border-color: #7E819B;
                background-color: #1F2029;
                color: #F0F0F5;
            }
            .fi-ra-select:focus,
            .fi-ra-input:focus {
                border-color: #007A33;
                box-shadow: 0 0 0 3px rgba(0, 122, 51, 0.18);
            }
            .dark .fi-ra-select:focus,
            .dark .fi-ra-input:focus {
                border-color: #67EA94;
                box-shadow: 0 0 0 3px rgba(103, 234, 148, 0.25);
            }

            /* Botones del sistema visual */
            .fi-ra-btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 0.5rem;
                padding: 0.65rem 1.35rem;
                font-size: 0.875rem;
                font-weight: 700;
                border-radius: 0.5rem;
                cursor: pointer;
                transition: all 0.18s ease;
                border: none;
                text-decoration: none;
                box-sizing: border-box;
                line-height: 1.3;
                box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
            }
            .fi-ra-btn:disabled {
                opacity: 0.45;
                cursor: not-allowed;
                box-shadow: none !important;
                transform: none !important;
            }

            .fi-ra-btn-primary {
                background: linear-gradient(135deg, #007A33 0%, #15612F 100%);
                color: #FFFFFF;
            }
            .fi-ra-btn-primary:hover:not(:disabled) {
                background: linear-gradient(135deg, #00662A 0%, #0e4c23 100%);
                box-shadow: 0 4px 14px rgba(0, 122, 51, 0.35);
                transform: translateY(-1px);
            }
            .dark .fi-ra-btn-primary {
                background: linear-gradient(135deg, #007A33 0%, #15612F 100%);
                color: #FFFFFF;
            }
            .dark .fi-ra-btn-primary:hover:not(:disabled) {
                box-shadow: 0 4px 16px rgba(103, 234, 148, 0.25);
                transform: translateY(-1px);
            }

            .fi-ra-btn-danger {
                background: linear-gradient(135deg, #E5484D 0%, #9B1E15 100%);
                color: #FFFFFF;
            }
            .fi-ra-btn-danger:hover:not(:disabled) {
                background: linear-gradient(135deg, #D3363B 0%, #871911 100%);
                box-shadow: 0 4px 14px rgba(229, 72, 77, 0.35);
                transform: translateY(-1px);
            }

            .fi-ra-btn-warning {
                background: linear-gradient(135deg, #D97706 0%, #B45309 100%);
                color: #FFFFFF;
            }
            .fi-ra-btn-warning:hover:not(:disabled) {
                box-shadow: 0 4px 14px rgba(217, 119, 6, 0.35);
                transform: translateY(-1px);
            }

            .fi-ra-btn-lg {
                padding: 0.85rem 1.85rem;
                font-size: 0.95rem;
                border-radius: 0.625rem;
            }

            /* Ficha de nodo conectado */
            .fi-ra-node-banner {
                background: linear-gradient(135deg, rgba(0, 122, 51, 0.08) 0%, rgba(103, 234, 148, 0.06) 100%);
                border: 1.5px solid rgba(0, 122, 51, 0.25);
                border-radius: 0.75rem;
                padding: 1.15rem 1.5rem;
                display: flex;
                align-items: center;
                justify-content: space-between;
                flex-wrap: wrap;
                gap: 1rem;
            }
            .dark .fi-ra-node-banner {
                background: linear-gradient(135deg, rgba(28, 58, 40, 0.7) 0%, rgba(44, 45, 60, 0.7) 100%);
                border-color: rgba(103, 234, 148, 0.3);
            }
            .fi-ra-node-banner-title {
                font-weight: 700;
                font-size: 0.95rem;
                color: #007A33;
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }
            .dark .fi-ra-node-banner-title {
                color: #67EA94;
            }
            .fi-ra-node-banner-meta {
                font-size: 0.8125rem;
                color: #515267;
                margin-top: 0.2rem;
            }
            .dark .fi-ra-node-banner-meta {
                color: #C7C8D4;
            }

            /* Tarjeta resumen del router objetivo */
            .fi-ra-router-summary {
                background: linear-gradient(135deg, rgba(0, 122, 51, 0.06) 0%, rgba(255, 255, 255, 0.5) 100%);
                border: 1.5px solid rgba(0, 122, 51, 0.22);
                border-radius: 0.875rem;
                padding: 1.25rem 1.5rem;
                display: flex;
                align-items: center;
                justify-content: space-between;
                flex-wrap: wrap;
                gap: 1.25rem;
            }
            .dark .fi-ra-router-summary {
                background: linear-gradient(135deg, rgba(28, 58, 40, 0.5) 0%, rgba(44, 45, 60, 0.5) 100%);
                border-color: rgba(103, 234, 148, 0.25);
            }
            .fi-ra-router-name {
                font-size: 1.05rem;
                font-weight: 800;
                color: #2C2D3C;
            }
            .dark .fi-ra-router-name {
                color: #F0F0F5;
            }
            .fi-ra-router-meta-row {
                display: flex;
                align-items: center;
                flex-wrap: wrap;
                gap: 0.75rem;
                margin-top: 0.35rem;
                font-size: 0.8125rem;
            }

            /* Badges semánticos */
            .fi-ra-badge {
                display: inline-flex;
                align-items: center;
                gap: 0.35rem;
                padding: 0.25rem 0.65rem;
                border-radius: 9999px;
                font-size: 0.75rem;
                font-weight: 700;
                line-height: 1;
            }
            .fi-ra-badge-managed {
                background-color: rgba(16, 185, 129, 0.15);
                color: #007A33;
                border: 1px solid rgba(0, 122, 51, 0.3);
            }
            .dark .fi-ra-badge-managed {
                background-color: rgba(103, 234, 148, 0.15);
                color: #67EA94;
                border-color: rgba(103, 234, 148, 0.3);
            }
            .fi-ra-badge-known {
                background-color: rgba(59, 130, 246, 0.15);
                color: #18509B;
                border: 1px solid rgba(59, 130, 246, 0.3);
            }
            .dark .fi-ra-badge-known {
                color: #A9C9FF;
            }
            .fi-ra-badge-new {
                background-color: rgba(245, 158, 11, 0.15);
                color: #B45309;
                border: 1px solid rgba(245, 158, 11, 0.3);
            }
            .dark .fi-ra-badge-new {
                color: #FCD34D;
            }

            /* Barra de Pestañas elegante y oxigenada */
            .fi-ra-tabs-nav {
                display: flex;
                gap: 0.5rem;
                border-bottom: 2px solid #E0E1EB;
                overflow-x: auto;
                padding: 0.5rem 0.75rem 0;
                background-color: #F8FAFC;
                border-top-left-radius: 1rem;
                border-top-right-radius: 1rem;
            }
            .dark .fi-ra-tabs-nav {
                border-bottom-color: #3D3E4D;
                background-color: #1F2029;
            }
            .fi-ra-tab-button {
                display: inline-flex;
                align-items: center;
                gap: 0.55rem;
                padding: 0.85rem 1.4rem;
                font-size: 0.875rem;
                font-weight: 600;
                color: #515267;
                background: transparent;
                border: none;
                border-bottom: 3px solid transparent;
                margin-bottom: -2px;
                cursor: pointer;
                transition: all 0.15s ease;
                white-space: nowrap;
                border-top-left-radius: 0.6rem;
                border-top-right-radius: 0.6rem;
            }
            .dark .fi-ra-tab-button {
                color: #C7C8D4;
            }
            .fi-ra-tab-button:hover {
                color: #2C2D3C;
                background-color: rgba(0, 122, 51, 0.05);
            }
            .dark .fi-ra-tab-button:hover {
                color: #F0F0F5;
                background-color: rgba(255, 255, 255, 0.05);
            }
            .fi-ra-tab-button.active {
                color: #007A33;
                font-weight: 800;
                border-bottom-color: #007A33;
                background-color: #FFFFFF;
                box-shadow: 0 -2px 6px rgba(0, 0, 0, 0.03);
            }
            .dark .fi-ra-tab-button.active {
                color: #67EA94;
                border-bottom-color: #67EA94;
                background-color: #2C2D3C;
            }

            .fi-ra-tab-content {
                padding: 2.25rem 2.5rem;
                display: flex;
                flex-direction: column;
                gap: 2rem;
            }

            /* Tarjetas de Selección de Roles */
            .fi-ra-role-grid {
                display: grid;
                grid-template-columns: 1fr;
                gap: 1.25rem;
            }
            @media (min-width: 640px) {
                .fi-ra-role-grid {
                    grid-template-columns: repeat(2, 1fr);
                }
            }
            @media (min-width: 1024px) {
                .fi-ra-role-grid {
                    grid-template-columns: repeat(4, 1fr);
                }
            }

            .fi-ra-role-card {
                border: 2px solid #E0E1EB;
                border-radius: 0.875rem;
                padding: 1.35rem 1.45rem;
                background-color: #FFFFFF;
                cursor: pointer;
                transition: all 0.2s ease;
                display: flex;
                flex-direction: column;
                gap: 0.65rem;
                position: relative;
            }
            .dark .fi-ra-role-card {
                border-color: #3D3E4D;
                background-color: #1F2029;
            }
            .fi-ra-role-card:hover {
                border-color: #007A33;
                transform: translateY(-2px);
                box-shadow: 0 8px 18px rgba(0, 0, 0, 0.06);
            }
            .dark .fi-ra-role-card:hover {
                border-color: #67EA94;
            }
            .fi-ra-role-card.selected {
                border-color: #007A33;
                background: linear-gradient(135deg, rgba(0, 122, 51, 0.08) 0%, rgba(103, 234, 148, 0.04) 100%);
                box-shadow: 0 0 0 1px #007A33, 0 8px 22px rgba(0, 122, 51, 0.18);
            }
            .dark .fi-ra-role-card.selected {
                border-color: #67EA94;
                background: linear-gradient(135deg, rgba(28, 58, 40, 0.9) 0%, rgba(44, 45, 60, 0.9) 100%);
                box-shadow: 0 0 0 1px #67EA94, 0 8px 22px rgba(103, 234, 148, 0.22);
            }
            .fi-ra-role-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
            }
            .fi-ra-role-title {
                font-weight: 800;
                font-size: 0.95rem;
                letter-spacing: -0.01em;
            }
            .fi-ra-role-desc {
                font-size: 0.8125rem;
                line-height: 1.5;
                color: #515267;
            }
            .dark .fi-ra-role-desc {
                color: #C7C8D4;
            }

            /* Barra de Acción Oxigenada para Roles y Mantenimiento */
            .fi-ra-action-bar {
                display: flex;
                align-items: center;
                justify-content: space-between;
                flex-wrap: wrap;
                gap: 2rem;
                padding: 1.75rem 2.25rem;
                border-radius: 1rem;
                background-color: #F8F9FA;
                border: 1.5px solid #E0E1EB;
                margin-top: 2rem;
            }
            .dark .fi-ra-action-bar {
                background-color: #1A1B23;
                border-color: #353644;
            }
            .fi-ra-action-bar-info {
                display: flex;
                flex-direction: column;
                gap: 0.5rem;
                flex: 1 1 320px;
            }
            .fi-ra-action-bar-title {
                font-size: 1rem;
                font-weight: 700;
                display: flex;
                align-items: center;
                gap: 0.5rem;
                color: #2C2D3C;
            }
            .dark .fi-ra-action-bar-title {
                color: #F0F0F5;
            }
            .fi-ra-action-bar-btn {
                display: flex;
                align-items: center;
                justify-content: flex-end;
                gap: 1rem;
                margin-left: auto;
            }
            @media (max-width: 640px) {
                .fi-ra-action-bar {
                    flex-direction: column;
                    align-items: stretch;
                    padding: 1.25rem;
                    gap: 1.25rem;
                }
                .fi-ra-action-bar-btn {
                    width: 100%;
                    margin-left: 0;
                }
                .fi-ra-action-bar-btn button {
                    width: 100%;
                }
            }

            .fi-ra-reboot-box {
                padding: 2rem 2.25rem;
                border-radius: 1rem;
                background-color: rgba(245, 158, 11, 0.08);
                border: 1.5px solid rgba(245, 158, 11, 0.35);
                display: flex;
                flex-direction: column;
                gap: 1.5rem;
            }
            .fi-ra-reboot-footer-bar {
                display: flex;
                align-items: center;
                justify-content: space-between;
                flex-wrap: wrap;
                gap: 2rem;
                padding-top: 1.5rem;
                border-top: 1px solid rgba(245, 158, 11, 0.25);
                margin-top: 0.75rem;
            }
            @media (max-width: 640px) {
                .fi-ra-reboot-footer-bar {
                    flex-direction: column;
                    align-items: stretch;
                    gap: 1.25rem;
                }
                .fi-ra-reboot-footer-bar button {
                    width: 100%;
                }
            }

            /* Banner de notificación integrado */
            .fi-ra-notification-banner {
                padding: 1rem 1.35rem;
                border-radius: 0.875rem;
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 1rem;
                margin-bottom: 1.5rem;
                animation: fi-ra-fadein 0.25s ease-out;
            }
            .fi-ra-notif-success {
                background-color: rgba(16, 185, 129, 0.12);
                border: 1.5px solid rgba(16, 185, 129, 0.4);
                color: #065F46;
            }
            .dark .fi-ra-notif-success {
                color: #A7F3D0;
                border-color: rgba(16, 185, 129, 0.5);
            }
            .fi-ra-notif-error {
                background-color: rgba(239, 68, 68, 0.12);
                border: 1.5px solid rgba(239, 68, 68, 0.4);
                color: #991B1B;
            }
            .dark .fi-ra-notif-error {
                color: #FECACA;
                border-color: rgba(239, 68, 68, 0.5);
            }
            .fi-ra-notif-warning {
                background-color: rgba(245, 158, 11, 0.12);
                border: 1.5px solid rgba(245, 158, 11, 0.4);
                color: #92400E;
            }
            .dark .fi-ra-notif-warning {
                color: #FDE68A;
                border-color: rgba(245, 158, 11, 0.5);
            }
            .fi-ra-notif-info {
                background-color: rgba(59, 130, 246, 0.12);
                border: 1.5px solid rgba(59, 130, 246, 0.4);
                color: #1E40AF;
            }
            .dark .fi-ra-notif-info {
                color: #BFDBFE;
                border-color: rgba(59, 130, 246, 0.5);
            }
            .fi-ra-notif-close {
                background: none;
                border: none;
                cursor: pointer;
                font-size: 1.35rem;
                font-weight: bold;
                line-height: 1;
                opacity: 0.6;
                transition: opacity 0.15s ease;
                padding: 0.2rem 0.5rem;
            }
            .fi-ra-notif-close:hover {
                opacity: 1;
            }
            @keyframes fi-ra-fadein {
                from { opacity: 0; transform: translateY(-4px); }
                to { opacity: 1; transform: translateY(0); }
            }

            /* Tarjetas de Acción de Sondeo y Unicast */
            .fi-ra-action-grid {
                display: grid;
                grid-template-columns: 1fr;
                gap: 1.25rem;
            }
            @media (min-width: 640px) {
                .fi-ra-action-grid-3 {
                    grid-template-columns: repeat(3, 1fr);
                }
                .fi-ra-action-grid-4 {
                    grid-template-columns: repeat(2, 1fr);
                }
            }
            @media (min-width: 1024px) {
                .fi-ra-action-grid-4 {
                    grid-template-columns: repeat(4, 1fr);
                }
            }

            .fi-ra-action-card {
                border: 1.5px solid #E0E1EB;
                border-radius: 0.875rem;
                padding: 1.5rem;
                background-color: #FFFFFF;
                text-align: left;
                cursor: pointer;
                transition: all 0.2s ease;
                display: flex;
                flex-direction: column;
                gap: 0.65rem;
            }
            .dark .fi-ra-action-card {
                border-color: #3D3E4D;
                background-color: #1F2029;
            }
            .fi-ra-action-card:hover:not(:disabled) {
                border-color: #007A33;
                transform: translateY(-2px);
                box-shadow: 0 8px 22px rgba(0, 122, 51, 0.12);
            }
            .dark .fi-ra-action-card:hover:not(:disabled) {
                border-color: #67EA94;
                box-shadow: 0 8px 22px rgba(103, 234, 148, 0.16);
            }
            .fi-ra-action-card:disabled {
                opacity: 0.5;
                cursor: not-allowed;
            }
            .fi-ra-action-icon {
                font-size: 1.75rem;
                line-height: 1;
            }
            .fi-ra-action-title {
                font-weight: 700;
                font-size: 0.875rem;
                color: inherit;
            }
            .fi-ra-action-desc {
                font-size: 0.775rem;
                line-height: 1.45;
                color: #515267;
            }
            .dark .fi-ra-action-desc {
                color: #C7C8D4;
            }

            /* Consola / Terminal de Actividad */
            .fi-ra-terminal-card {
                background-color: #0B0F19;
                border: 1.5px solid #1E293B;
                border-radius: 1rem;
                overflow: hidden;
                box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.5);
                display: flex;
                flex-direction: column;
            }
            .fi-ra-terminal-topbar {
                background-color: #111827;
                border-bottom: 1px solid #1E293B;
                padding: 0.85rem 1.25rem;
                display: flex;
                align-items: center;
                justify-content: space-between;
                flex-wrap: wrap;
                gap: 0.75rem;
            }
            .fi-ra-terminal-title {
                font-size: 0.875rem;
                font-weight: 700;
                color: #E2E8F0;
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }
            .fi-ra-terminal-body {
                height: 20rem;
                overflow-y: auto;
                padding: 1.25rem 1.5rem;
                font-family: 'Ubuntu Mono', monospace;
                font-size: 0.8125rem;
                line-height: 1.6;
                color: #E2E8F0;
                display: flex;
                flex-direction: column;
                gap: 0.3rem;
                background-color: #070B14;
            }
            .fi-ra-terminal-line {
                display: flex;
                align-items: flex-start;
                gap: 0.6rem;
                padding: 0.15rem 0.4rem;
                border-radius: 0.25rem;
                transition: background-color 0.1s ease;
            }
            .fi-ra-terminal-line:hover {
                background-color: rgba(255, 255, 255, 0.05);
            }
            .fi-ra-log-badge {
                font-size: 0.7rem;
                font-weight: 800;
                padding: 0.1rem 0.4rem;
                border-radius: 0.25rem;
                line-height: 1.2;
                white-space: nowrap;
            }
            .fi-ra-log-tx { background-color: rgba(59, 130, 246, 0.25); color: #60A5FA; }
            .fi-ra-log-rx { background-color: rgba(168, 85, 247, 0.25); color: #C084FC; }
            .fi-ra-log-ack { background-color: rgba(16, 185, 129, 0.25); color: #34D399; }
            .fi-ra-log-error { background-color: rgba(244, 63, 94, 0.25); color: #FB7185; }
            .fi-ra-log-info { background-color: rgba(148, 163, 184, 0.2); color: #94A3B8; }

            /* Banner de advertencia de compatibilidad de navegador */
            .fi-ra-warning-banner {
                background: linear-gradient(135deg, rgba(245, 158, 11, 0.12) 0%, rgba(251, 191, 36, 0.05) 100%);
                border: 1.5px solid rgba(245, 158, 11, 0.45);
                border-radius: 0.875rem;
                padding: 1.25rem 1.5rem;
                display: flex;
                align-items: flex-start;
                gap: 1rem;
                color: #92400E;
                box-shadow: 0 4px 14px rgba(245, 158, 11, 0.08);
            }
            .dark .fi-ra-warning-banner {
                background: linear-gradient(135deg, rgba(245, 158, 11, 0.15) 0%, rgba(180, 83, 9, 0.1) 100%);
                border-color: rgba(245, 158, 11, 0.4);
                color: #FCD34D;
            }
            .fi-ra-warning-icon {
                font-size: 1.6rem;
                line-height: 1;
                flex-shrink: 0;
            }
            .fi-ra-warning-title {
                font-size: 0.95rem;
                font-weight: 800;
                margin-bottom: 0.35rem;
                letter-spacing: -0.01em;
            }
            .fi-ra-warning-text {
                font-size: 0.825rem;
                line-height: 1.55;
                color: #78350F;
            }
            .dark .fi-ra-warning-text {
                color: #FDE68A;
            }

            /* Guía rápida operativa de 3 pasos */
            .fi-ra-guide-grid {
                display: grid;
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            @media (min-width: 768px) {
                .fi-ra-guide-grid {
                    grid-template-columns: repeat(3, 1fr);
                }
            }
            .fi-ra-guide-step {
                background-color: #FFFFFF;
                border: 1px solid #E0E1EB;
                border-radius: 0.875rem;
                padding: 1.15rem 1.25rem;
                display: flex;
                align-items: flex-start;
                gap: 0.85rem;
                box-shadow: 0 2px 8px rgba(44, 45, 60, 0.02);
            }
            .dark .fi-ra-guide-step {
                background-color: #2C2D3C;
                border-color: #3D3E4D;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
            }
            .fi-ra-step-num {
                width: 2rem;
                height: 2rem;
                border-radius: 9999px;
                background-color: #007A33;
                color: #FFFFFF;
                font-weight: 800;
                font-size: 0.875rem;
                display: flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
            }
            .dark .fi-ra-step-num {
                background-color: #67EA94;
                color: #111827;
            }
            .fi-ra-step-title {
                font-size: 0.875rem;
                font-weight: 700;
                color: #2C2D3C;
                margin-bottom: 0.25rem;
            }
            .dark .fi-ra-step-title {
                color: #F0F0F5;
            }
            .fi-ra-step-desc {
                font-size: 0.775rem;
                line-height: 1.45;
                color: #515267;
            }
            .dark .fi-ra-step-desc {
                color: #C7C8D4;
            }
        </style>

        {{-- Aviso destacado de compatibilidad obligatoria de navegador (Google Chrome o Edge) --}}
        <div class="fi-ra-warning-banner">
            <div class="fi-ra-warning-icon">⚠️</div>
            <div class="fi-ra-warning-content">
                <div class="fi-ra-warning-title">{{ __('admin.gestion_routers.warning_browser_title') }}</div>
                <div class="fi-ra-warning-text">
                    {{ __('admin.gestion_routers.warning_browser_desc') }}
                </div>
            </div>
        </div>

        {{-- 1. Hero Banner Institucional con Verde Andalucía --}}
        <div class="fi-ra-hero-banner">
            <div>
                <div class="fi-ra-hero-title">
                    <span>📡</span>
                    <span>{{ __('admin.gestion_routers.title') }}</span>
                </div>
                <div class="fi-ra-hero-desc">
                    {{ __('admin.gestion_routers.subheading') }}
                </div>
            </div>
            <div>
                <span class="fi-ra-status-pill fi-ra-status-connected" style="font-size: 0.75rem;">
                    <span>🛡️</span> Modo Operador Oficial
                </span>
            </div>
        </div>

        {{-- Guía Visual Rápida de Operación en 3 Pasos --}}
        <div class="fi-ra-guide-grid">
            <div class="fi-ra-guide-step">
                <div class="fi-ra-step-num">1</div>
                <div>
                    <div class="fi-ra-step-title">{{ __('admin.gestion_routers.step1_title') }}</div>
                    <div class="fi-ra-step-desc">{{ __('admin.gestion_routers.step1_desc') }}</div>
                </div>
            </div>

            <div class="fi-ra-guide-step">
                <div class="fi-ra-step-num">2</div>
                <div>
                    <div class="fi-ra-step-title">{{ __('admin.gestion_routers.step2_title') }}</div>
                    <div class="fi-ra-step-desc">{{ __('admin.gestion_routers.step2_desc') }}</div>
                </div>
            </div>

            <div class="fi-ra-guide-step">
                <div class="fi-ra-step-num">3</div>
                <div>
                    <div class="fi-ra-step-title">{{ __('admin.gestion_routers.step3_title') }}</div>
                    <div class="fi-ra-step-desc">{{ __('admin.gestion_routers.step3_desc') }}</div>
                </div>
            </div>
        </div>

        {{-- 2. Tarjeta: Conexión con el Nodo Local Físico del Operador --}}
        <div class="fi-ra-card">
            <div class="fi-ra-card-header">
                <div class="fi-ra-title-flex">
                    <div class="fi-ra-icon-badge">🔌</div>
                    <div>
                        <div class="fi-ra-card-heading">{{ __('admin.gestion_routers.local_connection_heading') }}</div>
                        <div class="fi-ra-card-subheading">{{ __('admin.gestion_routers.local_connection_desc') }}</div>
                    </div>
                </div>

                <div>
                    <template x-if="connectionStatus === 'connected'">
                        <span class="fi-ra-status-pill fi-ra-status-connected">
                            <span class="fi-ra-dot fi-ra-dot-connected"></span>
                            {{ __('admin.gestion_routers.status_connected') }}
                            <span x-show="localNode.hexId" x-text="`(${localNode.hexId})`" class="font-mono"></span>
                        </span>
                    </template>

                    <template x-if="connectionStatus === 'connecting'">
                        <span class="fi-ra-status-pill fi-ra-status-connecting">
                            <span class="fi-ra-dot fi-ra-dot-connecting"></span>
                            {{ __('admin.gestion_routers.status_connecting') }}
                        </span>
                    </template>

                    <template x-if="connectionStatus === 'disconnected'">
                        <span class="fi-ra-status-pill fi-ra-status-disconnected">
                            <span class="fi-ra-dot fi-ra-dot-disconnected"></span>
                            {{ __('admin.gestion_routers.status_disconnected') }}
                        </span>
                    </template>

                    <template x-if="connectionStatus === 'error'">
                        <span class="fi-ra-status-pill fi-ra-status-error">
                            <span class="fi-ra-dot fi-ra-dot-error"></span>
                            {{ __('admin.gestion_routers.status_error') }}
                        </span>
                    </template>
                </div>
            </div>

            {{-- Formulario de conexión al nodo físico --}}
            <div class="fi-ra-form-grid fi-ra-form-grid-3">
                <div class="fi-ra-form-group">
                    <label class="fi-ra-label">{{ __('admin.gestion_routers.transport_label') }}</label>
                    <select x-model="transportType" :disabled="connectionStatus === 'connected' || connectionStatus === 'connecting'" class="fi-ra-select">
                        <option value="serial">🔌 Web Serial (USB / COM)</option>
                        <option value="bluetooth">📶 Web Bluetooth (BLE)</option>
                        <option value="http">🌐 WiFi Local (HTTP / IP)</option>
                    </select>
                </div>

                <div class="fi-ra-form-group" x-show="transportType === 'serial'">
                    <label class="fi-ra-label">{{ __('admin.gestion_routers.baud_rate_label') }}</label>
                    <select x-model="baudRate" :disabled="connectionStatus === 'connected' || connectionStatus === 'connecting'" class="fi-ra-select font-mono">
                        <option value="115200">115200 baud</option>
                        <option value="921600">921600 baud</option>
                    </select>
                </div>

                <div class="fi-ra-form-group" x-show="transportType === 'http'">
                    <label class="fi-ra-label">{{ __('admin.gestion_routers.http_host_label') }}</label>
                    <input type="text" x-model="httpHost" :disabled="connectionStatus === 'connected' || connectionStatus === 'connecting'" placeholder="192.168.1.100 o meshtastic.local" class="fi-ra-input font-mono" />
                </div>

                <div class="fi-ra-form-group">
                    <button type="button" x-show="connectionStatus !== 'connected'" @click="connectLocalNode()" :disabled="connectionStatus === 'connecting'" class="fi-ra-btn fi-ra-btn-primary" style="width: 100%;">
                        <span x-show="connectionStatus !== 'connecting'">⚡ {{ __('admin.gestion_routers.btn_connect') }}</span>
                        <span x-show="connectionStatus === 'connecting'">⏳ {{ __('admin.gestion_routers.status_connecting') }}</span>
                    </button>

                    <button type="button" x-show="connectionStatus === 'connected'" @click="disconnectLocalNode()" class="fi-ra-btn fi-ra-btn-danger" style="width: 100%;">
                        🔌 {{ __('admin.gestion_routers.btn_disconnect') }}
                    </button>
                </div>
            </div>

            {{-- Ficha informativa cuando el nodo local está conectado --}}
            <div x-show="connectionStatus === 'connected'" class="fi-ra-node-banner">
                <div>
                    <div class="fi-ra-node-banner-title">
                        <span>⚡</span>
                        <span>Nodo Local Operativo en Puesto de Trabajo</span>
                    </div>
                    <div class="fi-ra-node-banner-meta">
                        Identificador: <span class="font-mono font-bold" x-text="localNode.hexId || 'Identificando...'"></span>
                        · Puerto activo en navegador · Enlace de radiofrecuencia LoRa listo para inyectar paquetes.
                    </div>
                </div>
                <div class="font-mono text-xs" style="color: #007A33; font-weight: 700;">
                    Estado: ENLACE LORA ACTIVO
                </div>
            </div>

            {{-- Alerta de error si falla la conexión física --}}
            <div x-show="errorMessage" class="p-3 rounded-lg text-xs" style="background-color: rgba(244, 63, 94, 0.12); color: #9B1E15; border: 1px solid rgba(244, 63, 94, 0.3);">
                <strong>⚠️ Fallo de conexión:</strong> <span x-text="errorMessage"></span>
            </div>
        </div>

        {{-- 3. Tarjeta: Selector del Router Objetivo de Andalucía --}}
        <div class="fi-ra-card">
            <div class="fi-ra-card-header">
                <div class="fi-ra-title-flex">
                    <div class="fi-ra-icon-badge">🎯</div>
                    <div>
                        <div class="fi-ra-card-heading">{{ __('admin.gestion_routers.target_router_heading') }}</div>
                        <div class="fi-ra-card-subheading">{{ __('admin.gestion_routers.target_router_desc') }}</div>
                    </div>
                </div>
            </div>

            <div class="fi-ra-form-grid fi-ra-form-grid-2">
                <div class="fi-ra-form-group">
                    <label class="fi-ra-label">{{ __('admin.gestion_routers.select_router_label') }}</label>
                    <select @change="onRouterSelectChange($event)" class="fi-ra-select">
                        <option value="">{{ __('admin.gestion_routers.select_placeholder') }}</option>
                        <optgroup label="{{ __('admin.gestion_routers.andalucia_optgroup') }}">
                            @foreach($routers as $r)
                                <option value="{{ $r['dec_id'] }}"
                                        data-hex="{{ $r['node_id'] }}"
                                        data-name="{{ $r['short_name'] }} · {{ $r['long_name'] }}"
                                        data-province="{{ $r['province'] }}"
                                        data-role="{{ $r['role'] }}"
                                        data-status="{{ $r['status'] }}">
                                    [{{ $r['province'] }}] {{ $r['short_name'] }} ({{ $r['node_id'] }}) · {{ $r['long_name'] }} [{{ $r['role'] }}]
                                </option>
                            @endforeach
                        </optgroup>
                    </select>
                </div>

                <div class="fi-ra-form-group">
                    <label class="fi-ra-label">{{ __('admin.gestion_routers.manual_node_label') }}</label>
                    <input type="text" x-model="manualNodeInput" @input="onManualInputChange()" placeholder="{{ __('admin.gestion_routers.manual_node_placeholder') }}" class="fi-ra-input font-mono" />
                </div>
            </div>

            {{-- Ficha destacada del router seleccionado --}}
            <div x-show="selectedRouterHex || manualNodeInput" class="fi-ra-router-summary">
                <div>
                    <div class="fi-ra-router-name" x-text="selectedRouterName || (manualNodeInput ? `Nodo Manual ${manualNodeInput}` : 'Router Seleccionado')"></div>
                    <div class="fi-ra-router-meta-row">
                        <span>ID LoRa: <strong class="font-mono text-emerald-700 dark:text-emerald-400" x-text="selectedRouterHex || manualNodeInput"></strong></span>
                        <template x-if="selectedRouterProvince">
                            <span x-text="`· Provincia: ${selectedRouterProvince}`"></span>
                        </template>
                        <template x-if="selectedRouterRole">
                            <span x-text="`· Rol actual: ${selectedRouterRole}`"></span>
                        </template>
                    </div>
                </div>

                <div style="display: flex; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
                    {{-- Badge de estado del router --}}
                    <template x-if="selectedRouterStatus">
                        <span class="fi-ra-badge"
                              :class="{
                                  'fi-ra-badge-managed': selectedRouterStatus === 'managed',
                                  'fi-ra-badge-known': selectedRouterStatus === 'known',
                                  'fi-ra-badge-new': selectedRouterStatus === 'new'
                              }"
                              x-text="`Estado: ${selectedRouterStatus.toUpperCase()}`">
                        </span>
                    </template>

                    {{-- Indicador de clave pública en NodeDB local --}}
                    <template x-if="targetHasPublicKeyInLocalRadio()">
                        <span class="fi-ra-badge fi-ra-badge-managed" title="Clave pública registrada en memoria del nodo local.">
                            🔒 Enlace Verificado
                        </span>
                    </template>
                    <template x-if="!targetHasPublicKeyInLocalRadio() && (selectedRouterHex || manualNodeInput)">
                        <span class="fi-ra-badge fi-ra-badge-known" title="Transmisión directa por radioenlace LoRa.">
                            📡 Enlace LoRa Directo
                        </span>
                    </template>
                </div>
            </div>

            {{-- Aviso cuando no hay ningún router seleccionado todavía --}}
            <div x-show="!selectedRouterHex && !manualNodeInput" class="p-3 rounded-lg text-xs" style="background-color: rgba(148, 163, 184, 0.08); border: 1px dashed rgba(148, 163, 184, 0.3); color: #64748B;">
                <span>💡 {{ __('admin.gestion_routers.target_none_selected') }}</span>
            </div>
        </div>

        {{-- Banner de Notificaciones del Sistema Meshtastic (Éxito, Error o Advertencia) --}}
        <div x-show="notification.show" x-transition class="fi-ra-notification-banner"
             :class="{
                 'fi-ra-notif-success': notification.type === 'success',
                 'fi-ra-notif-error': notification.type === 'error',
                 'fi-ra-notif-warning': notification.type === 'warning',
                 'fi-ra-notif-info': notification.type === 'info'
             }">
            <div style="display: flex; align-items: center; gap: 0.75rem; flex: 1;">
                <span class="text-xl" x-text="notification.type === 'success' ? '✅' : (notification.type === 'error' ? '❌' : (notification.type === 'warning' ? '⚠️' : 'ℹ️'))"></span>
                <span class="text-sm font-semibold leading-normal" x-text="notification.message"></span>
            </div>
            <button type="button" @click="dismissNotification()" class="fi-ra-notif-close" title="Cerrar aviso">×</button>
        </div>

        {{-- 4. Contenedor de Pestañas de Acciones Operativas --}}
        <div class="fi-ra-card" style="padding: 0; overflow: hidden;">
            {{-- Barra de navegación de pestañas --}}
            <div class="fi-ra-tabs-nav">
                <button type="button" @click="activeTab = 'roles'" :class="activeTab === 'roles' ? 'active' : ''" class="fi-ra-tab-button">
                    <span>🔀</span>
                    <span>{{ __('admin.gestion_routers.tab_roles') }}</span>
                </button>

                <button type="button" @click="activeTab = 'favoritos'" :class="activeTab === 'favoritos' ? 'active' : ''" class="fi-ra-tab-button">
                    <span>⭐</span>
                    <span>{{ __('admin.gestion_routers.tab_favorites') }}</span>
                </button>

                <button type="button" @click="activeTab = 'sondeo'" :class="activeTab === 'sondeo' ? 'active' : ''" class="fi-ra-tab-button">
                    <span>📡</span>
                    <span>{{ __('admin.gestion_routers.tab_poll') }}</span>
                </button>

                <button type="button" @click="activeTab = 'unicast'" :class="activeTab === 'unicast' ? 'active' : ''" class="fi-ra-tab-button">
                    <span>🎯</span>
                    <span>{{ __('admin.gestion_routers.tab_unicast') }}</span>
                </button>

                <button type="button" @click="activeTab = 'mantenimiento'" :class="activeTab === 'mantenimiento' ? 'active' : ''" class="fi-ra-tab-button">
                    <span>⚙️</span>
                    <span>{{ __('admin.gestion_routers.tab_maintenance') }}</span>
                </button>
            </div>

            {{-- Contenido de las pestañas --}}
            <div class="fi-ra-tab-content">
                {{-- PESTAÑA 1: ROLES --}}
                <div x-show="activeTab === 'roles'" class="space-y-6">
                    <div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                            <span>🔀</span> {{ __('admin.gestion_routers.roles_title') }}
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 leading-relaxed">
                            {{ __('admin.gestion_routers.roles_desc') }}
                        </p>
                    </div>

                    <div class="fi-ra-role-grid">
                        <div class="fi-ra-role-card" :class="selectedRole == 1 ? 'selected' : ''" @click="selectedRole = 1">
                            <div class="fi-ra-role-header">
                                <span class="fi-ra-role-title">CLIENT_MUTE</span>
                                <span class="text-lg">🔇</span>
                            </div>
                            <div class="fi-ra-role-desc">
                                Silencia el nodo. No retransmite paquetes de otros usuarios. Ideal ante spam masivo o saturación temporal en la zona.
                            </div>
                        </div>

                        <div class="fi-ra-role-card" :class="selectedRole == 2 ? 'selected' : ''" @click="selectedRole = 2">
                            <div class="fi-ra-role-header">
                                <span class="fi-ra-role-title">ROUTER</span>
                                <span class="text-lg">⚡</span>
                            </div>
                            <div class="fi-ra-role-desc">
                                Reenvío prioritario inmediato en malla. Reservado para repetidores estratégicos en cotas elevadas aprobados en Andalucía.
                            </div>
                        </div>

                        <div class="fi-ra-role-card" :class="selectedRole == 11 ? 'selected' : ''" @click="selectedRole = 11">
                            <div class="fi-ra-role-header">
                                <span class="fi-ra-role-title">ROUTER_LATE</span>
                                <span class="text-lg">⏱️</span>
                            </div>
                            <div class="fi-ra-role-desc">
                                Reenvío retardado para respaldo. Proporciona redundancia controlada sin colisionar con el router principal.
                            </div>
                        </div>

                        <div class="fi-ra-role-card" :class="selectedRole == 0 ? 'selected' : ''" @click="selectedRole = 0">
                            <div class="fi-ra-role-header">
                                <span class="fi-ra-role-title">CLIENT</span>
                                <span class="text-lg">📱</span>
                            </div>
                            <div class="fi-ra-role-desc">
                                Rol cliente normal estándar con retransmisión comunitaria equilibrada.
                            </div>
                        </div>
                    </div>

                    <div class="fi-ra-action-bar">
                        <div class="fi-ra-action-bar-info">
                            <div class="fi-ra-action-bar-title">
                                <span>Orden preparada:</span>
                                <span class="font-mono text-emerald-700 dark:text-emerald-400 font-bold" x-text="`Cambiar rol a ${getRoleName(selectedRole)}`"></span>
                            </div>
                            <div x-show="selectedRouterHex || manualNodeInput" class="text-xs text-gray-500 dark:text-gray-400">
                                <span>Destino:</span>
                                <strong class="font-mono text-emerald-600 dark:text-emerald-400" x-text="selectedRouterHex || manualNodeInput"></strong>
                                <template x-if="selectedRouterName">
                                    <span class="text-gray-600 dark:text-gray-300" x-text="` · ${selectedRouterName}`"></span>
                                </template>
                            </div>
                            <div x-show="connectionStatus !== 'connected'" class="text-xs flex items-center gap-1.5 text-amber-700 dark:text-amber-400 font-medium">
                                <span>⚠️</span> <span>{{ __('admin.gestion_routers.connect_prompt_hint') }}</span>
                            </div>
                            <div x-show="connectionStatus === 'connected' && !selectedRouterNodeNum && !manualNodeInput" class="text-xs flex items-center gap-1.5 text-amber-700 dark:text-amber-400 font-medium">
                                <span>⚠️</span> <span>{{ __('admin.gestion_routers.select_prompt_hint') }}</span>
                            </div>
                        </div>

                        <div class="fi-ra-action-bar-btn">
                            <button type="button" @click="applyRemoteRole()" :disabled="roleSending || connectionStatus !== 'connected' || (!selectedRouterNodeNum && !manualNodeInput)" class="fi-ra-btn fi-ra-btn-primary fi-ra-btn-lg">
                                <span x-show="!roleSending">🚀 {{ __('admin.gestion_routers.btn_apply_role') }}</span>
                                <span x-show="roleSending">⏳ {{ __('admin.gestion_routers.transmitting') }}</span>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- PESTAÑA 2: FAVORITOS --}}
                <div x-show="activeTab === 'favoritos'" class="space-y-6">
                    <div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                            <span>⭐</span> {{ __('admin.gestion_routers.favorites_title') }}
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 leading-relaxed">
                            {{ __('admin.gestion_routers.favorites_desc') }} Los routers priorizan la retransmisión de sus favoritos y facilitan la administración remota.
                        </p>
                    </div>

                    {{-- Nota de contexto sobre favoritos --}}
                    <div class="p-3 rounded-lg text-xs" style="background-color: rgba(59, 130, 246, 0.08); border: 1px solid rgba(59, 130, 246, 0.25); color: #1E40AF;">
                        <span>ℹ️</span> <strong>Nodos Favoritos:</strong> Los repetidores y routers priorizan la retransmisión de paquetes y telemetría de sus nodos favoritos en la malla LoRa.
                    </div>

                    <div class="fi-ra-form-grid" style="grid-template-columns: 2fr 1fr;">
                        <div class="fi-ra-form-group">
                            <label class="fi-ra-label">{{ __('admin.gestion_routers.favorite_input_label') }}</label>
                            <input type="text" x-model="favoriteNodeInput" placeholder="!2df0a1b2 o ID decimal" class="fi-ra-input font-mono" />
                        </div>

                        <div style="display: flex; gap: 0.75rem;">
                            <button type="button" @click="applyRemoteFavorite('add')" :disabled="favoriteSending || !favoriteNodeInput || connectionStatus !== 'connected'" class="fi-ra-btn fi-ra-btn-primary" style="flex: 1;">
                                <span>⭐</span> {{ __('admin.gestion_routers.btn_add_favorite') }}
                            </button>

                            <button type="button" @click="applyRemoteFavorite('remove')" :disabled="favoriteSending || !favoriteNodeInput || connectionStatus !== 'connected'" class="fi-ra-btn fi-ra-btn-danger" style="flex: 1;">
                                <span>🗑️</span> {{ __('admin.gestion_routers.btn_remove_favorite') }}
                            </button>
                        </div>
                    </div>

                    <div x-show="connectionStatus !== 'connected'" class="text-xs mt-1 flex items-center gap-1.5" style="color: #D97706;">
                        <span>⚠️</span> <span>{{ __('admin.gestion_routers.connect_prompt_hint') }}</span>
                    </div>
                    <div x-show="connectionStatus === 'connected' && !selectedRouterNodeNum && !manualNodeInput" class="text-xs mt-1 flex items-center gap-1.5" style="color: #D97706;">
                        <span>⚠️</span> <span>{{ __('admin.gestion_routers.select_prompt_hint') }}</span>
                    </div>

                    {{-- Lista de favoritos manipulados en la sesión --}}
                    <div x-show="sessionFavorites.length > 0" style="padding-top: 1rem; border-top: 1px solid #E0E1EB;">
                        <div class="fi-ra-label" style="margin-bottom: 0.6rem;">{{ __('admin.gestion_routers.session_favorites_heading') }}</div>
                        <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                            <template x-for="fav in sessionFavorites" :key="fav">
                                <span class="fi-ra-badge fi-ra-badge-managed font-mono cursor-pointer" style="padding: 0.4rem 0.75rem;" title="Haz clic para cargar este ID en el campo de texto" @click="favoriteNodeInput = fav">
                                    <span>⭐</span>
                                    <span x-text="fav"></span>
                                    <button type="button" @click.stop="favoriteNodeInput = fav; applyRemoteFavorite('remove')" style="margin-left: 0.5rem; background: none; border: none; cursor: pointer; color: #E5484D; font-weight: bold;" title="Quitar de favoritos">×</button>
                                </span>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- PESTAÑA 3: SONDEO DE MALLA (BROADCAST) --}}
                <div x-show="activeTab === 'sondeo'" class="space-y-6">
                    <div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                            <span>📡</span> {{ __('admin.gestion_routers.poll_title') }}
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 leading-relaxed">
                            {{ __('admin.gestion_routers.poll_desc') }}
                        </p>
                    </div>

                    <div class="fi-ra-action-grid fi-ra-action-grid-3">
                        <button type="button" @click="sendMeshPoll('nodeinfo')" :disabled="pollSending || connectionStatus !== 'connected'" class="fi-ra-action-card">
                            <div class="fi-ra-action-icon">📡</div>
                            <div class="fi-ra-action-title">{{ __('admin.gestion_routers.btn_poll_nodeinfo') }}</div>
                            <div class="fi-ra-action-desc">
                                Anuncia la identidad de tu nodo local a toda la malla (^all). Los routers cercanos registrarán tu clave pública y actualizarán su NodeDB.
                            </div>
                        </button>

                        <button type="button" @click="sendMeshPoll('position')" :disabled="pollSending || connectionStatus !== 'connected'" class="fi-ra-action-card">
                            <div class="fi-ra-action-icon">📍</div>
                            <div class="fi-ra-action-title">{{ __('admin.gestion_routers.btn_poll_position') }}</div>
                            <div class="fi-ra-action-desc">
                                Transmite tus coordenadas GPS o posición fija configurada a toda la malla (^all).
                            </div>
                        </button>

                        <button type="button" @click="sendMeshPoll('telemetry')" :disabled="pollSending || connectionStatus !== 'connected'" class="fi-ra-action-card">
                            <div class="fi-ra-action-icon">🔋</div>
                            <div class="fi-ra-action-title">{{ __('admin.gestion_routers.btn_poll_telemetry') }}</div>
                            <div class="fi-ra-action-desc">
                                Emite tus métricas de batería, voltaje y saturación de canal (ChUtil / AirUtil) a la malla (^all).
                            </div>
                        </button>
                    </div>

                    <div x-show="connectionStatus !== 'connected'" class="text-xs mt-1 flex items-center gap-1.5" style="color: #D97706;">
                        <span>⚠️</span> <span>{{ __('admin.gestion_routers.connect_prompt_hint') }}</span>
                    </div>
                </div>

                {{-- PESTAÑA 4: PETICIÓN A UN NODO (UNICAST) --}}
                <div x-show="activeTab === 'unicast'" class="space-y-6">
                    <div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                            <span>🎯</span> {{ __('admin.gestion_routers.unicast_title') }}
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 leading-relaxed">
                            {{ __('admin.gestion_routers.unicast_desc') }}
                        </p>
                    </div>

                    <div class="fi-ra-form-group">
                        <label class="fi-ra-label">{{ __('admin.gestion_routers.unicast_target_label') }}</label>
                        <input type="text" x-model="unicastTargetInput" :placeholder="selectedRouterHex ? `Por defecto router actual: ${selectedRouterHex}` : 'Introduce !hex o decimal'" class="fi-ra-input font-mono" />
                    </div>

                    <div class="fi-ra-action-grid fi-ra-action-grid-4">
                        <button type="button" @click="sendUnicastRequest('nodeinfo')" :disabled="unicastSending || connectionStatus !== 'connected'" class="fi-ra-action-card">
                            <div class="fi-ra-action-icon">ℹ️</div>
                            <div class="fi-ra-action-title">{{ __('admin.gestion_routers.btn_req_nodeinfo') }}</div>
                            <div class="fi-ra-action-desc">Identidad, modelo de hardware y roles del nodo.</div>
                        </button>

                        <button type="button" @click="sendUnicastRequest('position')" :disabled="unicastSending || connectionStatus !== 'connected'" class="fi-ra-action-card">
                            <div class="fi-ra-action-icon">📍</div>
                            <div class="fi-ra-action-title">{{ __('admin.gestion_routers.btn_req_position') }}</div>
                            <div class="fi-ra-action-desc">Coordenadas y altitud GPS directa.</div>
                        </button>

                        <button type="button" @click="sendUnicastRequest('telemetry')" :disabled="unicastSending || connectionStatus !== 'connected'" class="fi-ra-action-card">
                            <div class="fi-ra-action-icon">🔋</div>
                            <div class="fi-ra-action-title">{{ __('admin.gestion_routers.btn_req_telemetry') }}</div>
                            <div class="fi-ra-action-desc">Métricas de batería y uso de radio.</div>
                        </button>

                        <button type="button" @click="sendUnicastRequest('traceroute')" :disabled="unicastSending || connectionStatus !== 'connected'" class="fi-ra-action-card">
                            <div class="fi-ra-action-icon">🔄</div>
                            <div class="fi-ra-action-title">{{ __('admin.gestion_routers.btn_req_traceroute') }}</div>
                            <div class="fi-ra-action-desc">Rastreo de saltos y calidad SNR de retorno (30s).</div>
                        </button>
                    </div>

                    {{-- Caja de estado reactivo y resultados de Traceroute (espera de 30s) --}}
                    <div x-show="tracerouteActive || tracerouteResult" class="p-4 rounded-xl border transition-all mt-3"
                         :style="tracerouteActive ? 'background-color: rgba(59, 130, 246, 0.08); border-color: rgba(59, 130, 246, 0.3);' : 'background-color: rgba(16, 185, 129, 0.08); border-color: rgba(16, 185, 129, 0.3);'">
                        <div class="flex items-center justify-between mb-2">
                            <div class="text-xs font-bold uppercase tracking-wider flex items-center gap-2"
                                 :style="tracerouteActive ? 'color: #2563EB;' : 'color: #059669;'">
                                <span x-show="tracerouteActive" class="animate-spin inline-block">🔄</span>
                                <span x-show="!tracerouteActive">📍</span>
                                <span>Ruta Traceroute hacia <strong class="font-mono" x-text="tracerouteTargetHex"></strong></span>
                            </div>
                            <template x-if="tracerouteActive">
                                <span class="font-mono text-xs font-bold px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900/60 dark:text-blue-300">
                                    <span x-text="tracerouteCountdown"></span>s restantes
                                </span>
                            </template>
                        </div>

                        <template x-if="tracerouteActive">
                            <div class="text-xs text-gray-600 dark:text-gray-300">
                                Emitiendo sonda de enrutamiento por radiofrecuencia a través de los nodos de la malla... Esperando hasta 30 segundos a que los paquetes de ida y vuelta completen el recorrido.
                            </div>
                        </template>

                        <template x-if="tracerouteResult">
                            <div class="mt-2 p-3 rounded-lg bg-white/70 dark:bg-black/30 font-mono text-xs font-bold text-emerald-800 dark:text-emerald-300 border border-emerald-500/20"
                                 x-text="tracerouteResult">
                            </div>
                        </template>
                    </div>

                    <div x-show="connectionStatus !== 'connected'" class="text-xs mt-1 flex items-center gap-1.5" style="color: #D97706;">
                        <span>⚠️</span> <span>{{ __('admin.gestion_routers.connect_prompt_hint') }}</span>
                    </div>
                    <div x-show="connectionStatus === 'connected' && !unicastTargetInput && !selectedRouterHex && !manualNodeInput" class="text-xs mt-1 flex items-center gap-1.5" style="color: #D97706;">
                        <span>⚠️</span> <span>{{ __('admin.gestion_routers.select_prompt_hint') }}</span>
                    </div>
                </div>

                {{-- PESTAÑA 5: MANTENIMIENTO --}}
                <div x-show="activeTab === 'mantenimiento'" class="space-y-6">
                    <div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                            <span>⚙️</span> {{ __('admin.gestion_routers.maintenance_title') }}
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 leading-relaxed">
                            {{ __('admin.gestion_routers.maintenance_desc') }}
                        </p>
                    </div>

                    <div class="fi-ra-reboot-box">
                        <div class="font-bold text-base text-amber-800 dark:text-amber-300 flex items-center gap-2">
                            <span>⚠️</span> {{ __('admin.gestion_routers.reboot_heading') }}
                        </div>
                        <p class="text-xs text-amber-800/80 dark:text-amber-200/80 leading-relaxed">
                            {{ __('admin.gestion_routers.reboot_desc') }}
                        </p>

                        <div class="fi-ra-reboot-footer-bar">
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <label class="fi-ra-label" style="margin: 0; font-size: 0.875rem;">{{ __('admin.gestion_routers.delay_secs_label') }}:</label>
                                <input type="number" min="1" max="600" x-model="rebootSeconds" class="fi-ra-input font-mono" style="width: 6.5rem; text-align: center; padding: 0.5rem 0.75rem;" />
                                <span class="text-xs text-gray-600 dark:text-gray-300 font-semibold">segundos</span>
                            </div>

                            <div class="fi-ra-action-bar-btn">
                                <button type="button" @click="applyRemoteReboot()" :disabled="rebootSending || connectionStatus !== 'connected' || (!selectedRouterNodeNum && !manualNodeInput)" class="fi-ra-btn fi-ra-btn-warning fi-ra-btn-lg">
                                    <span x-show="!rebootSending">⚠️ {{ __('admin.gestion_routers.btn_apply_reboot') }}</span>
                                    <span x-show="rebootSending">⏳ {{ __('admin.gestion_routers.transmitting') }}</span>
                                </button>
                            </div>
                        </div>

                        <div x-show="connectionStatus !== 'connected'" class="text-xs flex items-center gap-1.5 text-amber-800 dark:text-amber-300 font-medium">
                            <span>⚠️</span> <span>{{ __('admin.gestion_routers.connect_prompt_hint') }}</span>
                        </div>
                        <div x-show="connectionStatus === 'connected' && !selectedRouterNodeNum && !manualNodeInput" class="text-xs flex items-center gap-1.5 text-amber-800 dark:text-amber-300 font-medium">
                            <span>⚠️</span> <span>{{ __('admin.gestion_routers.select_prompt_hint') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 5. Tarjeta: Consola / Terminal de Actividad en Tiempo Real --}}
        <div class="fi-ra-terminal-card">
            <div class="fi-ra-terminal-topbar">
                <div class="fi-ra-terminal-title">
                    <span>📟</span>
                    <span>{{ __('admin.gestion_routers.terminal_heading') }}</span>
                    <span style="font-size: 0.75rem; color: #94A3B8; font-weight: normal;" x-text="`(${logs.length} eventos registrados)`"></span>
                </div>

                <div style="display: flex; align-items: center; gap: 0.65rem;">
                    {{-- Filtro de eventos --}}
                    <select x-model="logFilter" class="fi-ra-select" style="padding: 0.3rem 0.6rem; font-size: 0.75rem; width: auto; background-color: #1E293B; color: #F1F5F9; border-color: #334155;">
                        <option value="all">Todos los eventos</option>
                        <option value="tx">Solo TX (Transmitidos)</option>
                        <option value="rx">Solo RX (Recibidos)</option>
                        <option value="ack">Solo ACKs</option>
                        <option value="error">Solo Errores</option>
                    </select>

                    <button type="button" @click="copyLogs()" class="fi-ra-btn" style="background-color: #1E293B; color: #F1F5F9; border: 1px solid #334155; padding: 0.35rem 0.75rem; font-size: 0.75rem;">
                        📋 Copiar
                    </button>

                    <button type="button" @click="clearLogs()" class="fi-ra-btn" style="background-color: #1E293B; color: #F1F5F9; border: 1px solid #334155; padding: 0.35rem 0.75rem; font-size: 0.75rem;">
                        🗑️ Limpiar
                    </button>
                </div>
            </div>

            <div id="meshAdminConsole" class="fi-ra-terminal-body">
                <template x-if="filteredLogs.length === 0">
                    <div style="color: #64748B; font-style: italic; text-align: center; padding: 3rem 1rem;">
                        {{ __('admin.gestion_routers.terminal_empty') }}
                    </div>
                </template>

                <template x-for="(l, idx) in filteredLogs" :key="idx">
                    <div class="fi-ra-terminal-line">
                        <span style="color: #64748B; user-select: none;" x-text="`[${l.time}]`"></span>
                        
                        <template x-if="l.type === 'tx'">
                            <span class="fi-ra-log-badge fi-ra-log-tx">TX ➡️</span>
                        </template>
                        <template x-if="l.type === 'rx'">
                            <span class="fi-ra-log-badge fi-ra-log-rx">RX ⬅️</span>
                        </template>
                        <template x-if="l.type === 'ack'">
                            <span class="fi-ra-log-badge fi-ra-log-ack">ACK ✅</span>
                        </template>
                        <template x-if="l.type === 'error'">
                            <span class="fi-ra-log-badge fi-ra-log-error">ERR ❌</span>
                        </template>
                        <template x-if="l.type === 'info'">
                            <span class="fi-ra-log-badge fi-ra-log-info">INFO</span>
                        </template>

                        <span style="flex: 1; word-break: break-all;" x-text="l.text"></span>
                    </div>
                </template>
            </div>
        </div>
    </div>
</x-filament-panels::page>
