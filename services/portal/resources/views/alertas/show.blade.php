<x-layout :title="'Alerta ' . ($alerta['id'] ?? '') . ' — ' . config('proyecto.nombre')" description="Detalle técnico e histórico de la anomalía detectada.">
    <div class="contenedor seccion">
        <div style="max-width: 760px; margin: 0 auto;">
            <a href="/alertas" style="font-size: 0.9rem; color: var(--color-texto-2); text-decoration: none; display: inline-flex; align-items: center; gap: 0.35rem; margin-bottom: 1.5rem;">
                ← Volver al listado de alertas
            </a>

            <article class="tarjeta" style="padding: 2rem; margin-bottom: 2rem;">
                <header style="border-bottom: 1px solid var(--color-borde); padding-bottom: 1.25rem; margin-bottom: 1.5rem;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; margin-bottom: 0.75rem;">
                        <h1 style="font-size: 1.75rem; font-weight: 800; color: var(--color-texto-1); margin: 0;">
                            {{ $alerta['titulo'] ?? $alerta['regla'] ?? 'Incidencia detectada' }}
                        </h1>
                        <span style="font-family: monospace; font-size: 0.85rem; background: var(--color-superficie-sutil); padding: 0.2rem 0.5rem; border-radius: var(--radio-sm);">
                            {{ $alerta['id'] ?? '' }}
                        </span>
                    </div>

                    <div style="display: flex; flex-wrap: wrap; gap: 1rem; align-items: center; font-size: 0.9rem; color: var(--color-texto-2);">
                        <span>Riesgo: <strong>{{ ucfirst($alerta['riesgo'] ?? 'medio') }}</strong></span>
                        <span>·</span>
                        <span>Tipo: <strong>{{ ucfirst($alerta['tipo'] ?? 'infraestructura') }}</strong></span>
                        <span>·</span>
                        <span>Estado: <strong style="color: {{ ($alerta['estado'] ?? '') === 'resuelta' ? 'var(--mapa-verde)' : 'var(--color-critico-texto)' }};">{{ ucfirst($alerta['estado'] ?? 'abierta') }}</strong></span>
                    </div>
                </header>

                <div style="line-height: 1.6; font-size: 1rem; color: var(--color-texto); margin-bottom: 1.5rem;">
                    <p>{{ $alerta['descripcion'] ?? 'Sin descripción detallada disponible.' }}</p>
                </div>

                @if(!empty($alerta['nodo_id']))
                    <div style="background: var(--color-superficie-sutil); border: 1px solid var(--color-borde); border-radius: var(--radio-md); padding: 1rem; margin-bottom: 1.5rem;">
                        <h3 style="font-size: 0.95rem; font-weight: 700; margin-bottom: 0.25rem;">Nodo afectado:</h3>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <code>{{ $alerta['nodo_id'] }}</code>
                            <a href="/revisa-tu-nodo/{{ $alerta['nodo_id'] }}" class="btn btn-secundario" style="padding: 0.25rem 0.6rem; font-size: 0.85rem;">
                                Auditar este nodo →
                            </a>
                        </div>
                    </div>
                @endif
            </article>
        </div>
    </div>
</x-layout>
