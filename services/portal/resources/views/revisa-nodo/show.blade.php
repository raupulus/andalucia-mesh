<x-layout :title="'Informe de Salud ' . $idBuscado . ' — ' . config('proyecto.nombre')" description="Auditoría técnica de configuración, radio, intervalos y batería del nodo.">
    <div class="contenedor seccion">
        <div style="max-width: 820px; margin: 0 auto;">
            <a href="/revisa-tu-nodo" style="font-size: 0.9rem; color: var(--color-texto-2); text-decoration: none; display: inline-flex; align-items: center; gap: 0.35rem; margin-bottom: 1.5rem;">
                ← Volver al buscador de nodos
            </a>

            @if(!empty($noEncontrado))
                <div class="tarjeta" style="padding: 2.5rem; text-align: center;">
                    <div style="font-size: 2.5rem; margin-bottom: 1rem;">🔍</div>
                    <h1 style="font-size: 1.5rem; font-weight: 700; color: var(--color-texto-1); margin-bottom: 0.5rem;">
                        Nodo no localizado: <code>{{ $idBuscado }}</code>
                    </h1>
                    <p style="color: var(--color-texto-2); max-width: 500px; margin: 0 auto 1.5rem auto; line-height: 1.5;">
                        No existen registros de paquetes válidos para este identificador en los últimos días. Asegúrate de que el nodo esté encendido, configurado con el preset regional y con la opción de posición activada.
                    </p>
                    <a href="/configura-tu-nodo" class="btn btn-primario">
                        Consultar guía de configuración regional →
                    </a>
                </div>
            @else
                <!-- Cabecera de identidad del nodo -->
                <header class="tarjeta" style="padding: 2rem; margin-bottom: 2rem;">
                    <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: flex-start; gap: 1rem; margin-bottom: 1rem;">
                        <div>
                            <h1 style="font-size: 1.85rem; font-weight: 800; color: var(--color-texto-1); margin: 0 0 0.25rem 0;">
                                {{ $informe['node']['short_name'] ?? $informe['node']['id'] }}
                                @if(!empty($informe['node']['long_name']))
                                    <span style="font-size: 1.1rem; font-weight: normal; color: var(--color-texto-2);">({{ $informe['node']['long_name'] }})</span>
                                @endif
                            </h1>
                            <div style="font-family: monospace; font-size: 1rem; color: var(--color-enlace);">
                                {{ $informe['node']['id'] }}
                            </div>
                        </div>

                        <!-- Veredicto general de salud -->
                        @php
                            $estadoSalud = $informe['health_status'] ?? 'optimo';
                            $chipTipo = match($estadoSalud) {
                                'optimo' => 'correcto',
                                'mejorable' => 'aviso',
                                'atencion_requerida' => 'critico',
                                default => 'neutro',
                            };
                            $estadoTexto = match($estadoSalud) {
                                'optimo' => 'Configuración óptima',
                                'mejorable' => 'Mejoras recomendadas',
                                'atencion_requerida' => 'Requiere atención',
                                default => 'Sin clasificar',
                            };
                        @endphp
                        <div>
                            <x-chip-estado :tipo="$chipTipo" :texto="$estadoTexto" />
                        </div>
                    </div>

                    <!-- Ficha rápida de hardware y rol -->
                    <div style="display: flex; flex-wrap: wrap; gap: 1.5rem; font-size: 0.9rem; color: var(--color-texto-2); border-top: 1px solid var(--color-borde); padding-top: 1rem;">
                        <div>Provincia: <strong>{{ $informe['node']['province'] ?? 'Desconocida' }}</strong></div>
                        <div>Rol: <code>{{ $informe['node']['role'] ?? 'CLIENT' }}</code></div>
                        <div>Hardware: <strong>{{ $informe['node']['hw_model'] ?? 'No especificado' }}</strong></div>
                        <div>Última vez visto: <strong>{{ $informe['node']['last_seen'] ?? 'Recientemente' }}</strong></div>
                    </div>
                </header>

                <!-- 1. Hallazgos y recomendaciones constructivas -->
                @if(!empty($informe['findings']))
                    <section class="tarjeta" style="padding: 1.75rem; margin-bottom: 2rem; border-left: 4px solid var(--color-aviso-texto);">
                        <h2 style="font-size: 1.3rem; font-weight: 700; color: var(--color-texto-1); margin-bottom: 1rem;">
                            Recomendaciones de Optimización Detectadas
                        </h2>
                        <div style="display: flex; flex-direction: column; gap: 1rem;">
                            @foreach($informe['findings'] as $f)
                                @php
                                    $chipTipo = match($f['severidad']) {
                                        'critico' => 'critico',
                                        'aviso' => 'aviso',
                                        default => 'info',
                                    };
                                @endphp
                                <div style="background: var(--color-superficie-sutil); padding: 1.25rem; border-radius: var(--radio-md); border: 1px solid var(--color-borde);">
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 0.5rem; margin-bottom: 0.5rem;">
                                        <h3 style="font-size: 1.05rem; font-weight: 700; color: var(--color-texto-1); margin: 0;">
                                            {{ $f['titulo'] }}
                                        </h3>
                                        <x-chip-estado :tipo="$chipTipo" :texto="ucfirst($f['severidad'])" />
                                    </div>
                                    <p style="font-size: 0.9rem; color: var(--color-texto-2); line-height: 1.5; margin: 0;">
                                        {{ $f['descripcion'] }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif

                <!-- 2. Parámetros de Radio y Saltos -->
                <section class="tarjeta" style="padding: 1.75rem; margin-bottom: 2rem;">
                    <h2 style="font-size: 1.3rem; font-weight: 700; color: var(--color-texto-1); margin-bottom: 1rem;">
                        Radio y Propagación
                    </h2>
                    <table class="tabla" style="width: 100%; border-collapse: collapse; font-size: 0.92rem;">
                        <tbody>
                            <tr style="border-bottom: 1px solid var(--color-borde);">
                                <td style="padding: 0.6rem 0; font-weight: 600; width: 45%;">Límite de saltos emitido (Hop Limit)</td>
                                <td style="padding: 0.6rem 0;">
                                    <strong>{{ $informe['node']['hop_start_last'] ?? '3 (por defecto)' }}</strong> saltos
                                    @if(($informe['node']['hop_start_last'] ?? 3) <= 3)
                                        <span style="color: var(--mapa-verde); margin-left: 0.5rem;">✓ Correcto</span>
                                    @else
                                        <span style="color: var(--color-critico-texto); margin-left: 0.5rem;">⚠️ Recomendado máximo 3</span>
                                    @endif
                                </td>
                            </tr>
                            <tr style="border-bottom: 1px solid var(--color-borde);">
                                <td style="padding: 0.6rem 0; font-weight: 600;">Rol operativo</td>
                                <td style="padding: 0.6rem 0;">
                                    <code>{{ $informe['node']['role'] ?? 'CLIENT' }}</code>
                                    @if(($informe['node']['role'] ?? '') === 'ROUTER')
                                        <span style="color: var(--color-aviso-texto); margin-left: 0.5rem;">(Coordinación requerida)</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 0.6rem 0; font-weight: 600;">Función de gateway</td>
                                <td style="padding: 0.6rem 0;">
                                    {{ !empty($informe['node']['is_gateway']) ? 'Sí, conectado a MQTT' : 'No (nodo solo radio)' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </section>

                <!-- 3. Cadencia de Emisiones observada -->
                <section class="tarjeta" style="padding: 1.75rem; margin-bottom: 2rem;">
                    <h2 style="font-size: 1.3rem; font-weight: 700; color: var(--color-texto-1); margin-bottom: 1rem;">
                        Cadencia de Intervalos Observada (Últimos 7 días)
                    </h2>
                    <div style="overflow-x: auto;">
                        <table class="tabla" style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
                            <thead>
                                <tr style="border-bottom: 2px solid var(--color-borde); text-align: left;">
                                    <th style="padding: 0.6rem;">Tipo de Paquete</th>
                                    <th style="padding: 0.6rem; text-align: right;">Emisiones</th>
                                    <th style="padding: 0.6rem; text-align: right;">Intervalo Mediano</th>
                                    <th style="padding: 0.6rem;">Recomendación Regional</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($informe['intervals'] as $inv)
                                    @php
                                        $mediana = $inv['median_interval_s'] !== null ? (float) $inv['median_interval_s'] : null;
                                        $textoMediana = $mediana !== null 
                                            ? ($mediana >= 3600 ? round($mediana / 3600, 1) . ' h' : round($mediana / 60) . ' min')
                                            : 'Menos de 2 emisiones';
                                    @endphp
                                    <tr style="border-bottom: 1px solid var(--color-borde);">
                                        <td style="padding: 0.6rem; font-weight: 600;">
                                            <code>{{ $inv['portnum'] }}</code>
                                            @if(!empty($inv['variant']))
                                                <span style="font-size: 0.8rem; color: var(--color-texto-3);">({{ $inv['variant'] }})</span>
                                            @endif
                                        </td>
                                        <td style="padding: 0.6rem; text-align: right; font-variant-numeric: tabular-nums;">
                                            {{ $inv['broadcasts'] ?? 0 }}
                                        </td>
                                        <td style="padding: 0.6rem; text-align: right; font-weight: 600; font-variant-numeric: tabular-nums;">
                                            {{ $textoMediana }}
                                        </td>
                                        <td style="padding: 0.6rem; font-size: 0.85rem; color: var(--color-texto-2);">
                                            @if($inv['portnum'] === 'nodeinfo')
                                                72 horas (nodos fijos)
                                            @elseif($inv['portnum'] === 'position')
                                                72 horas (fijos) / 15 min (móviles)
                                            @elseif($inv['portnum'] === 'telemetry')
                                                2 a 4 horas (solar) o desactivada
                                            @else
                                                Según necesidad
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" style="padding: 1rem; text-align: center; color: var(--color-texto-3);">
                                            No se han capturado emisiones periódicas en los últimos 7 días.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            @endif
        </div>
    </div>
</x-layout>
