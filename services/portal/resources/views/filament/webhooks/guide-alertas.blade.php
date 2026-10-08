{{-- Vista didáctica y detallada de Criterios de Riesgo, Tipos de Red y Gobernanza (RN-11, RN-32, RN-39) --}}
<div class="space-y-5 text-sm leading-relaxed text-slate-700 dark:text-slate-200">

    {{-- 1. Barra de KPIs y Umbrales Clave en la Cabecera --}}
    <div class="rounded-xl border border-slate-200/90 bg-gradient-to-r from-slate-50 via-white to-slate-50 p-4 shadow-sm dark:border-slate-800 dark:bg-gradient-to-r dark:from-[#181922] dark:via-[#1e1f2b] dark:to-[#181922]">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200/70 pb-3 dark:border-slate-800">
            <div class="flex items-center gap-2">
                <span class="flex h-2.5 w-2.5 rounded-full bg-emerald-500 animate-pulse" style="width:10px;height:10px;min-width:10px;"></span>
                <span class="font-semibold tracking-wide text-xs uppercase text-slate-800 dark:text-slate-100">
                    {{ __('admin.webhooks.guide_engine_badge') }}
                </span>
            </div>
            <span class="text-xs text-slate-500 dark:text-slate-400">
                {{ __('admin.webhooks.guide_rules_reference') }}
            </span>
        </div>

        <div class="mt-3 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2.5 text-xs">
            <div class="rounded-lg border border-slate-200/80 bg-white/80 p-2 text-center dark:border-slate-800/80 dark:bg-[#14151e]/80">
                <span class="block text-[11px] text-slate-500 dark:text-slate-400 font-medium">Baterías Routers</span>
                <strong class="font-semibold text-rose-600 dark:text-rose-400">&lt; 40%</strong>
                <span class="text-slate-400 dark:text-slate-500">/</span>
                <strong class="font-semibold text-amber-600 dark:text-amber-400">&lt; 60%</strong>
            </div>
            <div class="rounded-lg border border-slate-200/80 bg-white/80 p-2 text-center dark:border-slate-800/80 dark:bg-[#14151e]/80">
                <span class="block text-[11px] text-slate-500 dark:text-slate-400 font-medium">Baterías Clientes</span>
                <strong class="font-semibold text-amber-600 dark:text-amber-400">&lt; 15%</strong>
                <span class="text-slate-400 dark:text-slate-500">/</span>
                <strong class="font-semibold text-sky-600 dark:text-sky-400">&lt; 35%</strong>
            </div>
            <div class="rounded-lg border border-slate-200/80 bg-white/80 p-2 text-center dark:border-slate-800/80 dark:bg-[#14151e]/80">
                <span class="block text-[11px] text-slate-500 dark:text-slate-400 font-medium">Canal LoRa (60/40)</span>
                <strong class="font-semibold text-slate-700 dark:text-slate-200">&gt; 20%</strong> · <strong class="font-semibold text-amber-600 dark:text-amber-400">&gt; 30%</strong> · <strong class="font-semibold text-rose-600 dark:text-rose-400">&gt; 40%</strong>
            </div>
            <div class="rounded-lg border border-slate-200/80 bg-white/80 p-2 text-center dark:border-slate-800/80 dark:bg-[#14151e]/80">
                <span class="block text-[11px] text-slate-500 dark:text-slate-400 font-medium">Reinicios Anómalos</span>
                <strong class="font-semibold text-amber-600 dark:text-amber-400">≥3 en 5m</strong>
                <span class="text-slate-400 dark:text-slate-500">/</span>
                <strong class="font-semibold text-rose-600 dark:text-rose-400">≥5 en 10m</strong>
            </div>
            <div class="rounded-lg border border-slate-200/80 bg-white/80 p-2 text-center dark:border-slate-800/80 dark:bg-[#14151e]/80">
                <span class="block text-[11px] text-slate-500 dark:text-slate-400 font-medium">Atardecer Solar</span>
                <strong class="font-semibold text-slate-700 dark:text-slate-200">20:00 h</strong>
                <span class="text-slate-400 dark:text-slate-500">(RN-32)</span>
            </div>
        </div>
    </div>

    {{-- 2. Tarjetas de Niveles de Riesgo (Severidad del Evento) --}}
    <div>
        <div class="mb-2.5 flex items-center gap-2">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-amber-500" style="width:18px;height:18px;min-width:18px;min-height:18px;display:inline-block;flex-shrink:0;">
                <path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <h4 class="text-sm font-semibold tracking-wide uppercase text-slate-900 dark:text-white">
                {{ __('admin.webhooks.guide_risks_title') }}
            </h4>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-3.5">
            {{-- ALTO (CRÍTICO) --}}
            <div class="rounded-xl border border-rose-300/80 bg-rose-50/50 p-4 shadow-sm dark:border-rose-900/60 dark:bg-rose-950/20 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between gap-2 border-b border-rose-200/60 pb-2.5 dark:border-rose-900/40">
                        <span class="inline-flex items-center gap-1.5 rounded-md bg-rose-600 px-2 py-0.5 text-[11px] font-bold uppercase tracking-wider text-white shadow-xs">
                            <span class="h-1.5 w-1.5 rounded-full bg-white" style="width:6px;height:6px;min-width:6px;"></span>
                            {{ __('admin.webhooks.risk_high') }}
                        </span>
                        <span class="text-[11px] font-semibold text-rose-700 dark:text-rose-300">Crítico · Ruptura</span>
                    </div>
                    <p class="mt-2.5 text-xs text-rose-950 dark:text-rose-200 font-medium">
                        Incidencias graves que degradan o interrumpen de inmediato la red troncal:
                    </p>
                    <ul class="mt-2 space-y-1.5 text-xs text-rose-900/90 dark:text-rose-200/90 list-disc list-inside">
                        <li><strong>Batería crítica:</strong> Router &lt; 40% (o al atardecer 20:00 h).</li>
                        <li><strong>Reinicios severos:</strong> ≥ 5 reinicios en 10 min.</li>
                        <li><strong>Caída prolongada:</strong> Router &gt; 24 h sin señal o gateway MQTT &gt; 2 h desconectado.</li>
                        <li><strong>Saturación de canal:</strong> Ocupación provincial &gt; 40%.</li>
                        <li><strong>Ráfagas masivas:</strong> &gt; 10 textos/min, &gt; 50 telemetrías/h, ≥ 5 sondeos ^all/15m, ≥ 20 traceroutes/30m o &gt; 60 paquetes privados/h.</li>
                        <li><strong>Saltos excesivos:</strong> Paquetes con ≥ 7 saltos (RN-22).</li>
                    </ul>
                </div>
            </div>

            {{-- MEDIO (AVISO OPERATIVO) --}}
            <div class="rounded-xl border border-amber-300/80 bg-amber-50/50 p-4 shadow-sm dark:border-amber-900/60 dark:bg-amber-950/20 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between gap-2 border-b border-amber-200/60 pb-2.5 dark:border-amber-900/40">
                        <span class="inline-flex items-center gap-1.5 rounded-md bg-amber-500 px-2 py-0.5 text-[11px] font-bold uppercase tracking-wider text-slate-900 shadow-xs">
                            <span class="h-1.5 w-1.5 rounded-full bg-slate-900" style="width:6px;height:6px;min-width:6px;"></span>
                            {{ __('admin.webhooks.risk_medium') }}
                        </span>
                        <span class="text-[11px] font-semibold text-amber-800 dark:text-amber-300">Operativo · Seguimiento</span>
                    </div>
                    <p class="mt-2.5 text-xs text-amber-950 dark:text-amber-200 font-medium">
                        Problemas operacionales y degradación preventiva que exigen atención:
                    </p>
                    <ul class="mt-2 space-y-1.5 text-xs text-amber-900/90 dark:text-amber-200/90 list-disc list-inside">
                        <li><strong>Batería baja:</strong> Router &lt; 60% (o al anochecer) o cliente &lt; 15%.</li>
                        <li><strong>Reinicios moderados:</strong> ≥ 3 reinicios en 5 min.</li>
                        <li><strong>Silencio de nodo:</strong> Router &gt; 6 h o gateway sin publicar &gt; 15 min.</li>
                        <li><strong>Saturación elevada:</strong> Ocupación provincial &gt; 30%.</li>
                        <li><strong>Gobernanza (RN-39):</strong> Router/Repeater no registrado en <a href="{{ route('filament.admin.resources.coordinated-routers.index') }}" class="underline font-semibold hover:text-amber-950 dark:hover:text-white">Routers Coordinados</a>.</li>
                        <li><strong>Ráfagas anómalas:</strong> 6–10 textos/min, ≥ 3 sondeos ^all/15m, 10–19 traceroutes/30m, &gt; 30 paquetes privados/h.</li>
                    </ul>
                </div>
            </div>

            {{-- BAJO (INFORMATIVO) --}}
            <div class="rounded-xl border border-sky-300/80 bg-sky-50/50 p-4 shadow-sm dark:border-sky-900/60 dark:bg-sky-950/20 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between gap-2 border-b border-sky-200/60 pb-2.5 dark:border-sky-900/40">
                        <span class="inline-flex items-center gap-1.5 rounded-md bg-sky-600 px-2 py-0.5 text-[11px] font-bold uppercase tracking-wider text-white shadow-xs">
                            <span class="h-1.5 w-1.5 rounded-full bg-white" style="width:6px;height:6px;min-width:6px;"></span>
                            {{ __('admin.webhooks.risk_low') }}
                        </span>
                        <span class="text-[11px] font-semibold text-sky-800 dark:text-sky-300">Informativo · Clientes</span>
                    </div>
                    <p class="mt-2.5 text-xs text-sky-950 dark:text-sky-200 font-medium">
                        Incidencias leves acotadas a nodos de usuario final o desajustes menores:
                    </p>
                    <ul class="mt-2 space-y-1.5 text-xs text-sky-900/90 dark:text-sky-200/90 list-disc list-inside">
                        <li><strong>Batería cliente:</strong> Nodo usuario particular &lt; 35%.</li>
                        <li><strong>Saturación preventiva:</strong> Ocupación provincial &gt; 20%.</li>
                        <li><strong>Emisión acelerada leve:</strong> &gt; 5 textos/min o ≥ 2 telemetrías/min.</li>
                        <li><strong>Sondeos aislados:</strong> 1 petición a toda la malla (^all).</li>
                        <li><strong>Posición GPS acelerada:</strong> ≥ 4 posiciones en 5 min.</li>
                        <li><strong>Saltos fuera de norma:</strong> Paquetes emitidos con 6 saltos (RN-22).</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. Tarjetas de Tipos de Incidencia (Ámbito de Red) --}}
    <div>
        <div class="mb-2.5 flex items-center gap-2">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-emerald-500" style="width:18px;height:18px;min-width:18px;min-height:18px;display:inline-block;flex-shrink:0;">
                <path d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
            </svg>
            <h4 class="text-sm font-semibold tracking-wide uppercase text-slate-900 dark:text-white">
                {{ __('admin.webhooks.guide_types_title') }}
            </h4>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
            {{-- INFRAESTRUCTURA --}}
            <div class="rounded-xl border border-emerald-300/80 bg-emerald-50/50 p-4 shadow-sm dark:border-emerald-900/60 dark:bg-emerald-950/20">
                <div class="flex items-center gap-2 border-b border-emerald-200/60 pb-2.5 dark:border-emerald-900/40">
                    <span class="inline-flex items-center gap-1.5 rounded-md bg-emerald-600 px-2 py-0.5 text-[11px] font-bold uppercase tracking-wider text-white shadow-xs">
                        <span class="h-1.5 w-1.5 rounded-full bg-white" style="width:6px;height:6px;min-width:6px;"></span>
                        {{ __('admin.webhooks.type_infra') }}
                    </span>
                    <span class="text-xs font-semibold text-emerald-800 dark:text-emerald-300">Repetidores, Routers y Pasarelas</span>
                </div>
                <p class="mt-2 text-xs text-emerald-950 dark:text-emerald-200">
                    Afecta a nodos troncales de la infraestructura (<code class="rounded bg-emerald-100 px-1 py-0.5 font-mono text-[11px] text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-200">ROUTER</code>, <code class="rounded bg-emerald-100 px-1 py-0.5 font-mono text-[11px] text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-200">ROUTER_LATE</code>, <code class="rounded bg-emerald-100 px-1 py-0.5 font-mono text-[11px] text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-200">REPEATER</code>), pasarelas MQTT conectadas al broker, y eventos que degradan la salud general del espectro LoRa (saturación provincial, ráfagas globales). Incluye gobernanza y control de routers coordinados.
                </p>
            </div>

            {{-- CLIENTES --}}
            <div class="rounded-xl border border-indigo-300/80 bg-indigo-50/50 p-4 shadow-sm dark:border-indigo-900/60 dark:bg-indigo-950/20">
                <div class="flex items-center gap-2 border-b border-indigo-200/60 pb-2.5 dark:border-indigo-900/40">
                    <span class="inline-flex items-center gap-1.5 rounded-md bg-indigo-600 px-2 py-0.5 text-[11px] font-bold uppercase tracking-wider text-white shadow-xs">
                        <span class="h-1.5 w-1.5 rounded-full bg-white" style="width:6px;height:6px;min-width:6px;"></span>
                        {{ __('admin.webhooks.type_clients') }}
                    </span>
                    <span class="text-xs font-semibold text-indigo-800 dark:text-indigo-300">Nodos de Usuario Final</span>
                </div>
                <p class="mt-2 text-xs text-indigo-950 dark:text-indigo-200">
                    Incidencias circunscritas exclusivamente a nodos cliente particulares de usuarios (<code class="rounded bg-indigo-100 px-1 py-0.5 font-mono text-[11px] text-indigo-800 dark:bg-indigo-900/60 dark:text-indigo-200">CLIENT</code>, <code class="rounded bg-indigo-100 px-1 py-0.5 font-mono text-[11px] text-indigo-800 dark:bg-indigo-900/60 dark:text-indigo-200">CLIENT_BASE</code>, <code class="rounded bg-indigo-100 px-1 py-0.5 font-mono text-[11px] text-indigo-800 dark:bg-indigo-900/60 dark:text-indigo-200">CLIENT_MUTE</code>). Contempla baterías bajas individuales, reinicios locales o configuraciones particulares sin impacto en el enrutamiento troncal ni en la cobertura comunitaria.
                </p>
            </div>
        </div>
    </div>

    {{-- 4. Gobernanza, Territorio y Comportamiento de Filtrado (RN-39) --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-3.5">
        <div class="rounded-xl border border-slate-200 bg-white p-3.5 shadow-sm dark:border-slate-800 dark:bg-[#161722] text-xs space-y-1.5">
            <div class="flex items-center gap-2 font-semibold text-slate-800 dark:text-slate-100">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-emerald-600 dark:text-emerald-400" style="width:16px;height:16px;min-width:16px;min-height:16px;display:inline-block;flex-shrink:0;">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="m4.93 4.93 4.24 4.24M14.83 14.83l4.24 4.24M14.83 9.17l4.24-4.24M4.93 19.07l4.24-4.24"/>
                </svg>
                <span>Ámbito Territorial (RN-39)</span>
            </div>
            <p class="text-slate-600 dark:text-slate-300">
                Toda alerta identifica <code class="rounded bg-slate-100 px-1 py-0.5 font-mono text-[11px] dark:bg-slate-800">dentro_andalucia</code> y su provincia (<code class="font-mono text-[11px]">ES-AL</code> a <code class="font-mono text-[11px]">ES-SE</code> o <code class="font-mono text-[11px]">FUERA</code>). Reglas solares y de gobernanza descartan estrictamente nodos exteriores.
            </p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-3.5 shadow-sm dark:border-slate-800 dark:bg-[#161722] text-xs space-y-1.5">
            <div class="flex items-center gap-2 font-semibold text-slate-800 dark:text-slate-100">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-sky-600 dark:text-sky-400" style="width:16px;height:16px;min-width:16px;min-height:16px;display:inline-block;flex-shrink:0;">
                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>Routers Coordinados</span>
            </div>
            <p class="text-slate-600 dark:text-slate-300">
                La gobernanza comunitaria de repetidores en Andalucía está centralizada en el panel de operadores. Cualquier router no homologado dispara una alerta automática para evitar colisiones en el aire.
            </p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-3.5 shadow-sm dark:border-slate-800 dark:bg-[#161722] text-xs space-y-1.5">
            <div class="flex items-center gap-2 font-semibold text-slate-800 dark:text-slate-100">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-amber-600 dark:text-amber-400" style="width:16px;height:16px;min-width:16px;min-height:16px;display:inline-block;flex-shrink:0;">
                    <path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ __('admin.webhooks.guide_rule_title') }}</span>
            </div>
            <p class="text-slate-600 dark:text-slate-300">
                {{ __('admin.webhooks.guide_wildcard_notice') }}
            </p>
        </div>
    </div>

</div>
