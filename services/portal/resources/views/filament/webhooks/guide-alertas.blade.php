{{--
    Guía Visual Ejecutiva: Criterios de Riesgo, Tipos de Infraestructura y Gobernanza (RN-11, RN-32, RN-39)
    Renderizada debajo de los botones del formulario de webhooks para consulta operativa inmediata.
--}}
<div class="fi-wh-guide">
    <style>
        /* ==========================================================================
           ESTILOS AUTÓNOMOS PARA LA GUÍA VISUAL DE WEBHOOKS (FILAMENT ADMIN)
           ========================================================================== */
        .fi-wh-guide {
            margin-top: 2rem;
            margin-bottom: 2.5rem;
            width: 100%;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            color: #334155;
            display: flex;
            flex-direction: column;
            gap: 1.75rem;
        }

        .dark .fi-wh-guide {
            color: #e2e8f0;
        }

        /* 1. Cabecera Ejecutiva */
        .fi-wh-header {
            background: linear-gradient(135deg, #f8fafc 0%, #ffffff 50%, #f1f5f9 100%);
            border: 1px solid #cbd5e1;
            border-radius: 1rem;
            padding: 1.25rem 1.5rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .dark .fi-wh-header {
            background: linear-gradient(135deg, #181924 0%, #202230 50%, #1a1b26 100%);
            border-color: #373a4d;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.25);
        }

        .fi-wh-header-top {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 0.85rem;
        }

        .dark .fi-wh-header-top {
            border-bottom-color: #2e3142;
        }

        .fi-wh-title-group {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .fi-wh-pulse-dot {
            width: 10px;
            height: 10px;
            min-width: 10px;
            background-color: #10b981;
            border-radius: 9999px;
            box-shadow: 0 0 8px #10b981;
            animation: fi-wh-pulse 2s infinite ease-in-out;
        }

        @keyframes fi-wh-pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.4; transform: scale(1.15); }
        }

        .fi-wh-main-title {
            font-size: 1.05rem;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
            letter-spacing: -0.01em;
        }

        .dark .fi-wh-main-title {
            color: #ffffff;
        }

        .fi-wh-badge-sub {
            display: inline-flex;
            align-items: center;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.2rem 0.6rem;
            border-radius: 9999px;
            background-color: #e2e8f0;
            color: #475569;
        }

        .dark .fi-wh-badge-sub {
            background-color: #2c2f40;
            color: #94a3b8;
        }

        /* 2. Barra de KPIs / Umbrales Rápidos */
        .fi-wh-kpi-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.75rem;
        }

        @media (min-width: 640px) {
            .fi-wh-kpi-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (min-width: 1024px) {
            .fi-wh-kpi-grid {
                grid-template-columns: repeat(5, 1fr);
            }
        }

        .fi-wh-kpi-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            padding: 0.75rem 0.6rem;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 0.25rem;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }

        .dark .fi-wh-kpi-card {
            background: #14151f;
            border-color: #2b2e40;
        }

        .fi-wh-kpi-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }

        .fi-wh-kpi-label {
            font-size: 0.7rem;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .dark .fi-wh-kpi-label {
            color: #94a3b8;
        }

        .fi-wh-kpi-value {
            font-size: 0.85rem;
            font-weight: 700;
            color: #1e293b;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.35rem;
            white-space: nowrap;
        }

        .dark .fi-wh-kpi-value {
            color: #f1f5f9;
        }

        /* 3. Títulos de Bloque */
        .fi-wh-section-header {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.85rem;
        }

        .fi-wh-section-header svg {
            width: 20px;
            height: 20px;
            min-width: 20px;
            flex-shrink: 0;
        }

        .fi-wh-section-title {
            font-size: 0.925rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #0f172a;
            margin: 0;
        }

        .dark .fi-wh-section-title {
            color: #ffffff;
        }

        /* 4. Tarjetas de Severidad (Riesgos: Alto / Medio / Bajo) */
        .fi-wh-grid-3 {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        @media (min-width: 768px) {
            .fi-wh-grid-3 {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        .fi-wh-card-risk {
            border-radius: 1rem;
            padding: 1.25rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
            border-width: 1.5px;
            border-style: solid;
        }

        /* Alto / Crítico */
        .fi-wh-card-risk-high {
            background-color: #fff5f5;
            border-color: #fca5a5;
        }
        .dark .fi-wh-card-risk-high {
            background-color: #2b1418;
            border-color: #7f1d1d;
        }

        /* Medio / Operativo */
        .fi-wh-card-risk-medium {
            background-color: #fffbeb;
            border-color: #fcd34d;
        }
        .dark .fi-wh-card-risk-medium {
            background-color: #2d1d09;
            border-color: #78350f;
        }

        /* Bajo / Informativo */
        .fi-wh-card-risk-low {
            background-color: #f0f9ff;
            border-color: #7dd3fc;
        }
        .dark .fi-wh-card-risk-low {
            background-color: #0c1e30;
            border-color: #075985;
        }

        .fi-wh-card-topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.08);
            padding-bottom: 0.65rem;
            margin-bottom: 0.85rem;
        }

        .dark .fi-wh-card-topbar {
            border-bottom-color: rgba(255, 255, 255, 0.1);
        }

        .fi-wh-pill-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.725rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            padding: 0.25rem 0.65rem;
            border-radius: 9999px;
            color: #ffffff;
        }

        .fi-wh-pill-high { background-color: #dc2626; }
        .fi-wh-pill-medium { background-color: #d97706; color: #ffffff; }
        .fi-wh-pill-low { background-color: #0284c7; }

        .fi-wh-card-subtitle {
            font-size: 0.75rem;
            font-weight: 700;
        }
        .fi-wh-card-risk-high .fi-wh-card-subtitle { color: #b91c1c; }
        .dark .fi-wh-card-risk-high .fi-wh-card-subtitle { color: #fca5a5; }

        .fi-wh-card-risk-medium .fi-wh-card-subtitle { color: #b45309; }
        .dark .fi-wh-card-risk-medium .fi-wh-card-subtitle { color: #fde68a; }

        .fi-wh-card-risk-low .fi-wh-card-subtitle { color: #0369a1; }
        .dark .fi-wh-card-risk-low .fi-wh-card-subtitle { color: #7dd3fc; }

        .fi-wh-card-desc {
            font-size: 0.775rem;
            line-height: 1.45;
            margin-bottom: 0.75rem;
            font-weight: 500;
        }
        .fi-wh-card-risk-high .fi-wh-card-desc { color: #7f1d1d; }
        .dark .fi-wh-card-risk-high .fi-wh-card-desc { color: #fecaca; }

        .fi-wh-card-risk-medium .fi-wh-card-desc { color: #78350f; }
        .dark .fi-wh-card-risk-medium .fi-wh-card-desc { color: #fef3c7; }

        .fi-wh-card-risk-low .fi-wh-card-desc { color: #075985; }
        .dark .fi-wh-card-risk-low .fi-wh-card-desc { color: #e0f2fe; }

        .fi-wh-list {
            margin: 0;
            padding: 0;
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            font-size: 0.75rem;
            line-height: 1.4;
        }

        .fi-wh-list li {
            position: relative;
            padding-left: 1.25rem;
        }

        .fi-wh-list li::before {
            content: "•";
            position: absolute;
            left: 0.25rem;
            font-size: 1rem;
            line-height: 1;
        }

        .fi-wh-card-risk-high .fi-wh-list li::before { color: #dc2626; }
        .dark .fi-wh-card-risk-high .fi-wh-list li::before { color: #f87171; }

        .fi-wh-card-risk-medium .fi-wh-list li::before { color: #d97706; }
        .dark .fi-wh-card-risk-medium .fi-wh-list li::before { color: #fbbf24; }

        .fi-wh-card-risk-low .fi-wh-list li::before { color: #0284c7; }
        .dark .fi-wh-card-risk-low .fi-wh-list li::before { color: #38bdf8; }

        /* 5. Tarjetas de Tipos de Red (Infraestructura vs Clientes) */
        .fi-wh-grid-2 {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        @media (min-width: 768px) {
            .fi-wh-grid-2 {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        .fi-wh-card-type {
            border-radius: 1rem;
            padding: 1.25rem;
            border-width: 1.5px;
            border-style: solid;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
        }

        .fi-wh-card-type-infra {
            background-color: #f0fdf4;
            border-color: #86efac;
        }
        .dark .fi-wh-card-type-infra {
            background-color: #062b16;
            border-color: #14532d;
        }

        .fi-wh-card-type-clients {
            background-color: #f5f3ff;
            border-color: #c4b5fd;
        }
        .dark .fi-wh-card-type-clients {
            background-color: #181534;
            border-color: #3b2f6b;
        }

        .fi-wh-pill-infra { background-color: #059669; }
        .fi-wh-pill-clients { background-color: #6366f1; }

        .fi-wh-tag-code {
            display: inline-block;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 0.7rem;
            font-weight: 700;
            padding: 0.1rem 0.35rem;
            border-radius: 0.25rem;
            margin: 0 0.15rem;
        }

        .fi-wh-card-type-infra .fi-wh-tag-code {
            background-color: #dcfce7;
            color: #166534;
        }
        .dark .fi-wh-card-type-infra .fi-wh-tag-code {
            background-color: #14532d;
            color: #86efac;
        }

        .fi-wh-card-type-clients .fi-wh-tag-code {
            background-color: #ede9fe;
            color: #4338ca;
        }
        .dark .fi-wh-card-type-clients .fi-wh-tag-code {
            background-color: #3730a3;
            color: #c7d2fe;
        }

        /* 6. Grilla de 4 Grupos Funcionales (Baterías, Ráfagas/NodeInfo, Reinicios, Gobernanza) */
        .fi-wh-grid-4 {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        @media (min-width: 640px) {
            .fi-wh-grid-4 {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (min-width: 1200px) {
            .fi-wh-grid-4 {
                grid-template-columns: repeat(4, 1fr);
            }
        }

        .fi-wh-card-group {
            background-color: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 0.875rem;
            padding: 1.15rem;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
            transition: all 0.15s ease;
        }

        .dark .fi-wh-card-group {
            background-color: #1a1b26;
            border-color: #333649;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        .fi-wh-card-group:hover {
            border-color: #94a3b8;
            transform: translateY(-2px);
        }

        .dark .fi-wh-card-group:hover {
            border-color: #4b506d;
        }

        .fi-wh-group-header {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 1.5px solid #f1f5f9;
        }

        .dark .fi-wh-group-header {
            border-bottom-color: #272a3a;
        }

        .fi-wh-group-icon {
            width: 28px;
            height: 28px;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.95rem;
            flex-shrink: 0;
        }

        .fi-wh-group-icon-battery { background-color: #ecfdf5; color: #059669; }
        .dark .fi-wh-group-icon-battery { background-color: #064e3b; color: #34d399; }

        .fi-wh-group-icon-traffic { background-color: #f5f3ff; color: #7c3aed; }
        .dark .fi-wh-group-icon-traffic { background-color: #3b1b68; color: #a78bfa; }

        .fi-wh-group-icon-reboot { background-color: #fff7ed; color: #ea580c; }
        .dark .fi-wh-group-icon-reboot { background-color: #592207; color: #fb923c; }

        .fi-wh-group-icon-gov { background-color: #f0fdf4; color: #15803d; }
        .dark .fi-wh-group-icon-gov { background-color: #052e16; color: #4ade80; }

        .fi-wh-group-title {
            font-size: 0.825rem;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
        }

        .dark .fi-wh-group-title {
            color: #ffffff;
        }

        .fi-wh-group-content {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            font-size: 0.725rem;
            line-height: 1.4;
            color: #475569;
        }

        .dark .fi-wh-group-content {
            color: #cbd5e1;
        }

        .fi-wh-badge-mini {
            display: inline-flex;
            align-items: center;
            padding: 0.1rem 0.4rem;
            border-radius: 0.25rem;
            font-weight: 700;
            font-size: 0.675rem;
        }

        .fi-wh-bm-red { background: #fee2e2; color: #991b1b; }
        .dark .fi-wh-bm-red { background: #7f1d1d; color: #fecaca; }

        .fi-wh-bm-amber { background: #fef3c7; color: #92400e; }
        .dark .fi-wh-bm-amber { background: #78350f; color: #fde68a; }

        .fi-wh-bm-blue { background: #e0f2fe; color: #075985; }
        .dark .fi-wh-bm-blue { background: #075985; color: #bae6fd; }

        .fi-wh-bm-green { background: #dcfce7; color: #166534; }
        .dark .fi-wh-bm-green { background: #14532d; color: #bbf7d0; }

        /* 7. Caja Resumen de Modo Comodín y Seguridad */
        .fi-wh-wildcard-card {
            background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
            border: 1.5px dashed #94a3b8;
            border-radius: 1rem;
            padding: 1.25rem 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .dark .fi-wh-wildcard-card {
            background: linear-gradient(135deg, #181924 0%, #151620 100%);
            border-color: #475569;
        }

        .fi-wh-wildcard-header {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.85rem;
            font-weight: 700;
            color: #0f172a;
        }

        .dark .fi-wh-wildcard-header {
            color: #f8fafc;
        }

        .fi-wh-wildcard-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
            font-size: 0.75rem;
            line-height: 1.45;
            color: #475569;
        }

        @media (min-width: 768px) {
            .fi-wh-wildcard-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        .dark .fi-wh-wildcard-grid {
            color: #cbd5e1;
        }

        .fi-wh-wildcard-item {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .fi-wh-wildcard-item strong {
            color: #0f172a;
        }

        .dark .fi-wh-wildcard-item strong {
            color: #ffffff;
        }
    </style>

    {{-- CABECERA EJECUTIVA Y UMBRALES RÁPIDOS (RN-11, RN-32, RN-39) --}}
    <div class="fi-wh-header">
        <div class="fi-wh-header-top">
            <div class="fi-wh-title-group">
                <span class="fi-wh-pulse-dot"></span>
                <h3 class="fi-wh-main-title">
                    {{ __('admin.webhooks.section_guide') }}
                </h3>
            </div>
            <div style="display:flex;align-items:center;gap:0.5rem;flex-wrap:wrap;">
                <span class="fi-wh-badge-sub">
                    {{ __('admin.webhooks.guide_engine_badge') }}
                </span>
                <span class="fi-wh-badge-sub" style="background-color:rgba(0,122,51,0.12);color:#007A33;" class="dark:text-[#67EA94]">
                    {{ __('admin.webhooks.guide_rules_reference') }}
                </span>
            </div>
        </div>

        {{-- 5 KPIs de Umbrales Críticos --}}
        <div class="fi-wh-kpi-grid">
            <div class="fi-wh-kpi-card">
                <span class="fi-wh-kpi-label">Batería Routers</span>
                <div class="fi-wh-kpi-value">
                    <span style="color:#dc2626;">&lt; 40%</span>
                    <span style="opacity:0.4;">/</span>
                    <span style="color:#d97706;">&lt; 60%</span>
                </div>
            </div>

            <div class="fi-wh-kpi-card">
                <span class="fi-wh-kpi-label">Batería Clientes</span>
                <div class="fi-wh-kpi-value">
                    <span style="color:#d97706;">&lt; 15%</span>
                    <span style="opacity:0.4;">/</span>
                    <span style="color:#0284c7;">&lt; 35%</span>
                </div>
            </div>

            <div class="fi-wh-kpi-card">
                <span class="fi-wh-kpi-label">Canal LoRa (60/40)</span>
                <div class="fi-wh-kpi-value">
                    <span style="color:#0284c7;">&gt; 20%</span>
                    <span style="opacity:0.4;">·</span>
                    <span style="color:#d97706;">&gt; 30%</span>
                    <span style="opacity:0.4;">·</span>
                    <span style="color:#dc2626;">&gt; 40%</span>
                </div>
            </div>

            <div class="fi-wh-kpi-card">
                <span class="fi-wh-kpi-label">Bucle Reinicios</span>
                <div class="fi-wh-kpi-value">
                    <span style="color:#d97706;">≥ 3 en 5m</span>
                    <span style="opacity:0.4;">/</span>
                    <span style="color:#dc2626;">≥ 5 en 10m</span>
                </div>
            </div>

            <div class="fi-wh-kpi-card">
                <span class="fi-wh-kpi-label">Atardecer Solar</span>
                <div class="fi-wh-kpi-value">
                    <span style="color:#059669;">20:00 h</span>
                    <span style="font-size:0.675rem;opacity:0.75;">(RN-32)</span>
                </div>
            </div>
        </div>
    </div>

    {{-- BLOQUE 1: NIVELES DE RIESGO (SEVERIDADES) --}}
    <div>
        <div class="fi-wh-section-header">
            <svg viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <h4 class="fi-wh-section-title">
                {{ __('admin.webhooks.guide_risks_title') }}
            </h4>
        </div>

        <div class="fi-wh-grid-3">
            {{-- TARJETA ALTO --}}
            <div class="fi-wh-card-risk fi-wh-card-risk-high">
                <div>
                    <div class="fi-wh-card-topbar">
                        <span class="fi-wh-pill-badge fi-wh-pill-high">
                            {{ __('admin.webhooks.risk_high') }}
                        </span>
                        <span class="fi-wh-card-subtitle">Crítico · Ruptura Troncal</span>
                    </div>
                    <p class="fi-wh-card-desc">
                        Incidencias graves y daño activo que degradan o interrumpen de inmediato la red comunitaria:
                    </p>
                    <ul class="fi-wh-list">
                        <li><strong>Batería crítica:</strong> Router troncal &lt; 40% (o al atardecer 20:00 h).</li>
                        <li><strong>Reinicios severos:</strong> ≥ 5 reinicios en 10 minutos.</li>
                        <li><strong>Caída prolongada:</strong> Router &gt; 24 h sin señal o Gateway MQTT &gt; 2 h desconectado.</li>
                        <li><strong>Saturación extrema de canal:</strong> Ocupación provincial &gt; 40%.</li>
                        <li><strong>Ráfagas masivas:</strong> &gt; 10 textos/min, &gt; 50 telemetrías/h, ≥ 5 sondeos ^all/15m o &gt; 60 paquetes privados/h.</li>
                        <li><strong>Saltos excesivos:</strong> Paquetes en origen con ≥ 7 saltos (RN-22).</li>
                    </ul>
                </div>
            </div>

            {{-- TARJETA MEDIO --}}
            <div class="fi-wh-card-risk fi-wh-card-risk-medium">
                <div>
                    <div class="fi-wh-card-topbar">
                        <span class="fi-wh-pill-badge fi-wh-pill-medium">
                            {{ __('admin.webhooks.risk_medium') }}
                        </span>
                        <span class="fi-wh-card-subtitle">Operativo · Seguimiento</span>
                    </div>
                    <p class="fi-wh-card-desc">
                        Degradación de servicio o anomalías preventivas que requieren supervisión técnica:
                    </p>
                    <ul class="fi-wh-list">
                        <li><strong>Batería en descenso:</strong> Router &lt; 60% (o al anochecer) o Cliente &lt; 15%.</li>
                        <li><strong>Reinicios moderados:</strong> ≥ 3 reinicios en 5 minutos.</li>
                        <li><strong>Silencio de nodo:</strong> Router &gt; 6 h sin emitir o Gateway MQTT &gt; 15 min sin publicar.</li>
                        <li><strong>Saturación elevada:</strong> Ocupación de canal provincial &gt; 30%.</li>
                        <li><strong>Gobernanza (RN-39):</strong> Router/Repeater no registrado en <a href="{{ route('filament.admin.resources.coordinated-routers.index') }}" style="text-decoration:underline;font-weight:700;">Routers Coordinados</a>.</li>
                        <li><strong>Ráfagas operativas:</strong> 6–10 textos/min, ≥ 3 sondeos ^all/15m, 10–19 traceroutes/30m o &gt; 30 paquetes privados/h.</li>
                    </ul>
                </div>
            </div>

            {{-- TARJETA BAJO --}}
            <div class="fi-wh-card-risk fi-wh-card-risk-low">
                <div>
                    <div class="fi-wh-card-topbar">
                        <span class="fi-wh-pill-badge fi-wh-pill-low">
                            {{ __('admin.webhooks.risk_low') }}
                        </span>
                        <span class="fi-wh-card-subtitle">Informativo · Preventivo</span>
                    </div>
                    <p class="fi-wh-card-desc">
                        Avisos leves y desajustes acotados a nodos clientes particulares sin impacto troncal:
                    </p>
                    <ul class="fi-wh-list">
                        <li><strong>Batería cliente:</strong> Nodo particular de usuario &lt; 35%.</li>
                        <li><strong>Saturación preventiva:</strong> Ocupación provincial &gt; 20%.</li>
                        <li><strong>Emisión acelerada:</strong> &gt; 5 textos/min o ≥ 2 telemetrías/min.</li>
                        <li><strong>Sondeos aislados:</strong> 1 sondeo a toda la malla (^all).</li>
                        <li><strong>Posición acelerada:</strong> ≥ 4 posiciones GPS en 5 min.</li>
                        <li><strong>Saltos fuera de norma:</strong> Paquetes en origen con 6 saltos (RN-22).</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- BLOQUE 2: ÁMBITO DE RED (TIPOS DE INCIDENCIA) --}}
    <div>
        <div class="fi-wh-section-header">
            <svg viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
            </svg>
            <h4 class="fi-wh-section-title">
                {{ __('admin.webhooks.guide_types_title') }}
            </h4>
        </div>

        <div class="fi-wh-grid-2">
            {{-- INFRAESTRUCTURA --}}
            <div class="fi-wh-card-type fi-wh-card-type-infra">
                <div class="fi-wh-card-topbar">
                    <span class="fi-wh-pill-badge fi-wh-pill-infra">
                        {{ __('admin.webhooks.type_infra') }}
                    </span>
                    <span style="font-size:0.75rem;font-weight:700;color:#047857;" class="dark:text-[#6ee7b7]">
                        Troncal, Pasarelas y Cobertura
                    </span>
                </div>
                <p style="font-size:0.775rem;line-height:1.45;color:#064e3b;margin-bottom:0.5rem;" class="dark:text-[#d1fae5]">
                    Afecta a nodos troncales de la infraestructura (<span class="fi-wh-tag-code">ROUTER</span>, <span class="fi-wh-tag-code">ROUTER_LATE</span>, <span class="fi-wh-tag-code">REPEATER</span>), pasarelas MQTT conectadas al broker, y eventos que degradan la salud general del espectro LoRa (saturación provincial, ráfagas globales). Incluye la gobernanza y homologación de repetidores coordinados.
                </p>
            </div>

            {{-- CLIENTES --}}
            <div class="fi-wh-card-type fi-wh-card-type-clients">
                <div class="fi-wh-card-topbar">
                    <span class="fi-wh-pill-badge fi-wh-pill-clients">
                        {{ __('admin.webhooks.type_clients') }}
                    </span>
                    <span style="font-size:0.75rem;font-weight:700;color:#4f46e5;" class="dark:text-[#a5b4fc]">
                        Nodos de Usuario Final
                    </span>
                </div>
                <p style="font-size:0.775rem;line-height:1.45;color:#312e81;margin-bottom:0.5rem;" class="dark:text-[#e0e7ff]">
                    Incidencias circunscritas exclusivamente a nodos cliente particulares de usuarios (<span class="fi-wh-tag-code">CLIENT</span>, <span class="fi-wh-tag-code">CLIENT_BASE</span>, <span class="fi-wh-tag-code">CLIENT_MUTE</span>). Contempla baterías bajas individuales, reinicios locales o configuraciones particulares sin impacto en el enrutamiento troncal ni en la cobertura comunitaria.
                </p>
            </div>
        </div>
    </div>

    {{-- BLOQUE 3: 4 GRUPOS FUNCIONALES (Baterías, NodeInfo/Ráfagas, Reinicios, Gobernanza/Territorio) --}}
    <div>
        <div class="fi-wh-section-header">
            <svg viewBox="0 0 24 24" fill="none" stroke="#6366f1" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
            </svg>
            <h4 class="fi-wh-section-title">
                Grupos y Comportamiento Específico de Anomalías
            </h4>
        </div>

        <div class="fi-wh-grid-4">
            {{-- GRUPO 1: BATERÍAS Y ENERGÍA --}}
            <div class="fi-wh-card-group">
                <div class="fi-wh-group-header">
                    <div class="fi-wh-group-icon fi-wh-group-icon-battery">🔋</div>
                    <h5 class="fi-wh-group-title">Baterías y Alimentación</h5>
                </div>
                <div class="fi-wh-group-content">
                    <div>
                        <strong>Routers Troncales:</strong>
                        <div style="margin-top:2px;">
                            <span class="fi-wh-badge-mini fi-wh-bm-red">&lt; 40% Crítico</span>
                            <span class="fi-wh-badge-mini fi-wh-bm-amber">&lt; 60% Bajo</span>
                        </div>
                    </div>
                    <div>
                        <strong>Clientes Particulares:</strong>
                        <div style="margin-top:2px;">
                            <span class="fi-wh-badge-mini fi-wh-bm-amber">&lt; 15% Medio</span>
                            <span class="fi-wh-badge-mini fi-wh-bm-blue">&lt; 35% Bajo</span>
                        </div>
                    </div>
                    <div>
                        <strong>Atardecer Solar (20:00 h):</strong>
                        <span>Routers solares evaluados al anochecer (RN-32). Descarta nodos exteriores.</span>
                    </div>
                    <div>
                        <strong>Alimentación Fija:</strong>
                        <span>Nodos con carga &gt; 100% se consideran enchufados y no generan alerta.</span>
                    </div>
                </div>
            </div>

            {{-- GRUPO 2: NODEINFO, SONDEOS Y RÁFAGAS --}}
            <div class="fi-wh-card-group">
                <div class="fi-wh-group-header">
                    <div class="fi-wh-group-icon fi-wh-group-icon-traffic">📡</div>
                    <h5 class="fi-wh-group-title">Tráfico, NodeInfo y Ráfagas</h5>
                </div>
                <div class="fi-wh-group-content">
                    <div>
                        <strong>Sondeos Masivos (^all):</strong>
                        <div style="margin-top:2px;">
                            <span class="fi-wh-badge-mini fi-wh-bm-blue">1 Bajo</span>
                            <span class="fi-wh-badge-mini fi-wh-bm-amber">≥ 3/15m Medio</span>
                            <span class="fi-wh-badge-mini fi-wh-bm-red">≥ 5/15m Alto</span>
                        </div>
                    </div>
                    <div>
                        <strong>Inundación de Texto:</strong>
                        <span>&gt; 5/min (Bajo) · 6–10/min (Medio) · &gt; 10/min (Alto).</span>
                    </div>
                    <div>
                        <strong>Ráfagas Telemetría:</strong>
                        <span>≥ 2/min (Bajo) · &gt; 50/h (Alto). Distingue ráfagas combinadas.</span>
                    </div>
                    <div>
                        <strong>Traceroutes y Tráfico Privado:</strong>
                        <span>Traceroute ≥ 20/30m (Alto). Tráfico privado masivo &gt; 60/h (Alto).</span>
                    </div>
                    <div>
                        <strong>Saltos de Red (RN-22):</strong>
                        <span>6 saltos (Bajo) · ≥ 7 saltos (Alto). Recomendado: 3 saltos.</span>
                    </div>
                </div>
            </div>

            {{-- GRUPO 3: REINICIOS Y SILENCIOS --}}
            <div class="fi-wh-card-group">
                <div class="fi-wh-group-header">
                    <div class="fi-wh-group-icon fi-wh-group-icon-reboot">🔄</div>
                    <h5 class="fi-wh-group-title">Reinicios y Silencios</h5>
                </div>
                <div class="fi-wh-group-content">
                    <div>
                        <strong>Bucle de Reinicios:</strong>
                        <div style="margin-top:2px;">
                            <span class="fi-wh-badge-mini fi-wh-bm-amber">≥ 3 en 5 min</span>
                            <span class="fi-wh-badge-mini fi-wh-bm-red">≥ 5 en 10 min</span>
                        </div>
                        <span style="font-size:0.675rem;opacity:0.8;">Resolución automática tras 30 min sin reinicios.</span>
                    </div>
                    <div>
                        <strong>Silencio de Routers:</strong>
                        <span>&gt; 6 h sin emitir (Medio) · &gt; 24 h sin señal (Alto / Caída).</span>
                    </div>
                    <div>
                        <strong>Pasarelas MQTT:</strong>
                        <span>Sin publicar &gt; 15 min (Medio) · Caída &gt; 2 h (Alto).</span>
                    </div>
                </div>
            </div>

            {{-- GRUPO 4: GOBERNANZA Y TERRITORIO --}}
            <div class="fi-wh-card-group">
                <div class="fi-wh-group-header">
                    <div class="fi-wh-group-icon fi-wh-group-icon-gov">🛡️</div>
                    <h5 class="fi-wh-group-title">Gobernanza y Territorio</h5>
                </div>
                <div class="fi-wh-group-content">
                    <div>
                        <strong>Routers Coordinados:</strong>
                        <span>Nodos con rol <span class="fi-wh-tag-code">ROUTER</span> o <span class="fi-wh-tag-code">REPEATER</span> no registrados en el panel de operadores generan alerta Media inmediata (RN-39).</span>
                    </div>
                    <div>
                        <strong>Ámbito Territorial:</strong>
                        <span>Toda alerta incluye <code style="font-size:0.675rem;">dentro_andalucia</code> y su provincia (<code style="font-size:0.675rem;">ES-AL</code> a <code style="font-size:0.675rem;">ES-SE</code> o <code style="font-size:0.675rem;">FUERA</code>).</span>
                    </div>
                    <div>
                        <strong>Descarte Exterior:</strong>
                        <span>Reglas de gobernanza y solares descartan tajantemente nodos <code style="font-size:0.675rem;">FUERA</code>.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- BLOQUE 4: REGLAS DE ENTREGA, FIRMA Y MODO COMODÍN --}}
    <div class="fi-wh-wildcard-card">
        <div class="fi-wh-wildcard-header">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#007A33" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="dark:text-[#67EA94]" style="width:20px;height:20px;min-width:20px;">
                <circle cx="12" cy="12" r="10"/>
                <path d="M12 16v-4m0-4h.01"/>
            </svg>
            <span>{{ __('admin.webhooks.guide_rule_title') }}</span>
        </div>

        <div class="fi-wh-wildcard-grid">
            <div class="fi-wh-wildcard-item">
                <strong>Modo Abierto (Comodín):</strong>
                <span>{{ __('admin.webhooks.guide_wildcard_notice') }}</span>
            </div>

            <div class="fi-wh-wildcard-item">
                <strong>Firma y Validación HMAC:</strong>
                <span>El servicio envía el encabezado <code style="font-size:0.7rem;font-weight:700;">X-SNM-Firma</code> con el hash HMAC-SHA256 del cuerpo exacto (UTF-8) calculado con el secreto configurado. Verifique con comparación en tiempo constante antes de procesar el JSON.</span>
            </div>

            <div class="fi-wh-wildcard-item">
                <strong>Contrato de Recepción:</strong>
                <span>Su endpoint debe responder con código HTTP <code style="font-size:0.7rem;font-weight:700;">2xx</code> en menos de 10 segundos. Si falla 20 veces consecutivas, el destino se auto-suspende para proteger la cola. Puede usar el botón <em>Probar (Ping)</em> para verificar conectividad.</span>
            </div>
        </div>
    </div>
</div>
