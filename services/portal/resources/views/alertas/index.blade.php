<x-layout title="Alertas y Estado de Incidencias" description="Registro automático de incidencias y anomalías detectadas en la red de Andalucía Mesh.">
    <div class="contenedor seccion">
        <header style="margin-bottom: 2.5rem;">
            <h1 style="font-size: 2.25rem; font-weight: 800; color: var(--color-texto-1); margin-bottom: 0.5rem;">
                Alertas de la Malla
            </h1>
            <p class="lead" style="margin-bottom: 1.5rem;">
                Monitorización en tiempo real de anomalías de radio, bucles de reinicio, saturación de canal y caídas de infraestructura.
            </p>

            <!-- Filtros de severidad -->
            <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                <a href="/alertas" class="btn {{ empty($riesgoFiltro) ? 'btn-primario' : 'btn-secundario' }}">
                    Todas
                </a>
                <a href="/alertas?riesgo=alto" class="btn {{ $riesgoFiltro === 'alto' ? 'btn-primario' : 'btn-secundario' }}">
                    Riesgo Alto
                </a>
                <a href="/alertas?riesgo=medio" class="btn {{ $riesgoFiltro === 'medio' ? 'btn-primario' : 'btn-secundario' }}">
                    Riesgo Medio
                </a>
                <a href="/alertas?riesgo=bajo" class="btn {{ $riesgoFiltro === 'bajo' ? 'btn-primario' : 'btn-secundario' }}">
                    Riesgo Bajo
                </a>
            </div>
        </header>

        <!-- Banner informativo si el detector está en calibración -->
        @if($detectorCalibrando || empty($alertas))
            <div class="tarjeta" style="padding: 2rem; margin-bottom: 3rem; background: var(--color-superficie-sutil); border-left: 4px solid var(--color-enlace);">
                <div style="display: flex; gap: 1rem; align-items: flex-start;">
                    <div style="font-size: 1.75rem; line-height: 1;">ℹ️</div>
                    <div>
                        <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--color-texto-1); margin-bottom: 0.5rem;">
                            Detector Automático de Anomalías (Fase 6)
                        </h2>
                        <p style="color: var(--color-texto-2); font-size: 0.95rem; line-height: 1.55; margin-bottom: 0;">
                            El motor inteligente de detección continua de incidencias y caídas de infraestructura se encuentra actualmente en proceso de despliegue y calibración de umbrales. Ninguna anomalía crítica activa ha sido notificada en este momento.
                        </p>
                    </div>
                </div>
            </div>
        @else
            <!-- Listado de alertas registradas -->
            <div class="tarjeta" style="padding: 1.5rem; margin-bottom: 3rem;">
                <div style="overflow-x: auto;">
                    <table class="tabla" style="width: 100%; border-collapse: collapse; font-size: 0.92rem;">
                        <thead>
                            <tr style="border-bottom: 2px solid var(--color-borde); text-align: left;">
                                <th style="padding: 0.6rem;">Riesgo</th>
                                <th style="padding: 0.6rem;">Regla / Incidencia</th>
                                <th style="padding: 0.6rem;">Nodo / Ámbito</th>
                                <th style="padding: 0.6rem;">Inicio</th>
                                <th style="padding: 0.6rem;">Estado</th>
                                <th style="padding: 0.6rem; text-align: right;">Detalle</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($alertas as $a)
                                @php
                                    $chipTipo = match(strtolower((string) $a->riesgo)) {
                                        'alto' => 'critico',
                                        'medio' => 'aviso',
                                        'bajo' => 'info',
                                        default => 'neutro',
                                    };
                                @endphp
                                <tr style="border-bottom: 1px solid var(--color-borde);">
                                    <td style="padding: 0.6rem;">
                                        <x-chip-estado :tipo="$chipTipo" :texto="ucfirst($a->riesgo)" />
                                    </td>
                                    <td style="padding: 0.6rem; font-weight: 600;">
                                        {{ $a->regla ?? 'Anomalía detectada' }}
                                    </td>
                                    <td style="padding: 0.6rem;">
                                        <code>{{ $a->nodo_id ?? 'Global' }}</code>
                                    </td>
                                    <td style="padding: 0.6rem; color: var(--color-texto-2); font-size: 0.85rem;">
                                        {{ $a->inicio_at ?? '—' }}
                                    </td>
                                    <td style="padding: 0.6rem;">
                                        <span style="font-weight: 600; color: {{ $a->estado === 'abierta' ? 'var(--color-critico-texto)' : 'var(--mapa-verde)' }};">
                                            {{ ucfirst($a->estado ?? 'abierta') }}
                                        </span>
                                    </td>
                                    <td style="padding: 0.6rem; text-align: right;">
                                        <a href="/alertas/{{ $a->id }}" class="btn btn-secundario" style="padding: 0.2rem 0.5rem; font-size: 0.8rem;">
                                            Ver →
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- Catálogo de anomalías monitorizadas -->
        <section>
            <h2 style="font-size: 1.4rem; font-weight: 700; color: var(--color-texto-1); margin-bottom: 1rem;">
                Catálogo de Anomalías Monitorizadas
            </h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.25rem;">
                @foreach($reglas as $regla)
                    @php
                        $chipTipo = match($regla['risk']) {
                            'alto' => 'critico',
                            'medio' => 'aviso',
                            'bajo' => 'info',
                            default => 'neutro',
                        };
                    @endphp
                    <div class="tarjeta" style="padding: 1.25rem; background: var(--color-superficie-sutil);">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.5rem;">
                            <h3 style="font-size: 1.05rem; font-weight: 700; color: var(--color-texto-1); margin: 0;">
                                {{ $regla['name'] }}
                            </h3>
                            <x-chip-estado :tipo="$chipTipo" :texto="ucfirst($regla['risk'])" />
                        </div>
                        <p style="font-size: 0.88rem; color: var(--color-texto-2); line-height: 1.45; margin-bottom: 0.5rem;">
                            {{ $regla['description'] }}
                        </p>
                        <div style="font-size: 0.8rem; color: var(--color-texto-3);">
                            Tipo: <strong>{{ ucfirst($regla['type']) }}</strong>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    </div>
</x-layout>
