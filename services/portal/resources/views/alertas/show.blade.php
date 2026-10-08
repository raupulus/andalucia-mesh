@php
    $currentLang = app()->getLocale();
    $langQuery = $currentLang !== 'es' ? '?lang=' . $currentLang : '';
@endphp

<x-layout :title="__('portal.alerts.single_title', ['id' => $alerta['id'] ?? '', 'name' => config('proyecto.nombre')])" :description="__('portal.alerts.single_desc')">
    <div class="contenedor seccion">
        <div style="max-width: 760px; margin: 0 auto;">
            <a href="/alertas{{ $langQuery }}" style="font-size: 0.9rem; color: var(--color-texto-2); text-decoration: none; display: inline-flex; align-items: center; gap: 0.35rem; margin-bottom: 1.5rem;">
                {{ __('portal.alerts.back_to_list') }}
            </a>

            <article class="tarjeta" style="padding: 2rem; margin-bottom: 2rem;">
                <header style="border-bottom: 1px solid var(--color-borde); padding-bottom: 1.25rem; margin-bottom: 1.5rem;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; margin-bottom: 0.75rem;">
                        <h1 style="font-size: 1.75rem; font-weight: 800; color: var(--color-texto-1); margin: 0;">
                            {{ $alerta['titulo'] ?? $alerta['regla'] ?? __('portal.alerts.detected_incident') }}
                        </h1>
                        <span style="font-family: monospace; font-size: 0.85rem; background: var(--color-superficie-sutil); padding: 0.2rem 0.5rem; border-radius: var(--radio-sm);">
                            {{ $alerta['id'] ?? '' }}
                        </span>
                    </div>

                    @php
                        $riesgoKey = strtolower((string) ($alerta['riesgo'] ?? 'medio'));
                        $textoRiesgo = __('portal.alerts.risk_levels.' . $riesgoKey);
                        if ($textoRiesgo === 'portal.alerts.risk_levels.' . $riesgoKey) {
                            $textoRiesgo = ucfirst($riesgoKey);
                        }
                        $estadoKey = strtolower((string) ($alerta['estado'] ?? 'abierta'));
                        $textoEstado = __('portal.alerts.states.' . $estadoKey);
                        if ($textoEstado === 'portal.alerts.states.' . $estadoKey) {
                            $textoEstado = ucfirst($estadoKey);
                        }
                    @endphp

                    <div style="display: flex; flex-wrap: wrap; gap: 1rem; align-items: center; font-size: 0.9rem; color: var(--color-texto-2);">
                        <span>{{ __('portal.alerts.risk_label') }} <strong>{{ $textoRiesgo }}</strong></span>
                        <span>·</span>
                        <span>{{ __('portal.alerts.type_label') }} <strong>{{ ucfirst($alerta['tipo'] ?? 'infraestructura') }}</strong></span>
                        <span>·</span>
                        <span>{{ __('portal.alerts.status_label') }} <strong style="color: {{ $estadoKey === 'resuelta' ? 'var(--mapa-verde)' : 'var(--color-critico-texto)' }};">{{ $textoEstado }}</strong></span>
                    </div>
                </header>

                <div style="line-height: 1.6; font-size: 1rem; color: var(--color-texto); margin-bottom: 1.5rem;">
                    <p>{{ $alerta['descripcion'] ?? __('portal.alerts.no_description') }}</p>
                </div>

                @if(!empty($alerta['nodo_id']))
                    <div style="background: var(--color-superficie-sutil); border: 1px solid var(--color-borde); border-radius: var(--radio-md); padding: 1rem; margin-bottom: 1.5rem;">
                        <h3 style="font-size: 0.95rem; font-weight: 700; margin-bottom: 0.25rem;">{{ __('portal.alerts.affected_node') }}</h3>
                        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                            <code>{{ $alerta['nodo_id'] }}</code>
                            <a href="/revisa-tu-nodo/{{ $alerta['nodo_id'] }}{{ $langQuery }}" class="btn btn-secundario" style="padding: 0.25rem 0.6rem; font-size: 0.85rem;">
                                {{ __('portal.alerts.audit_node') }}
                            </a>
                        </div>
                    </div>
                @endif
            </article>
        </div>
    </div>
</x-layout>
