@php
    $currentLang = app()->getLocale();
    $langQuery = $currentLang !== 'es' ? '?lang=' . $currentLang : '';

    $riesgoKey = strtolower((string) ($alerta['riesgo'] ?? 'medio'));
    $chipTipo = match($riesgoKey) {
        'alto' => 'critico',
        'medio' => 'aviso',
        'bajo' => 'info',
        default => 'neutro',
    };
    $textoRiesgo = __('portal.alerts.risk_levels.' . $riesgoKey);
    if ($textoRiesgo === 'portal.alerts.risk_levels.' . $riesgoKey) {
        $textoRiesgo = ucfirst($riesgoKey);
    }
    $estadoKey = strtolower((string) ($alerta['estado'] ?? 'abierta'));
    $textoEstado = __('portal.alerts.states.' . $estadoKey);
    if ($textoEstado === 'portal.alerts.states.' . $estadoKey) {
        $textoEstado = ucfirst($estadoKey);
    }

    $esGlobal = $alerta['nodo_es_global'] ?? false;
    $nodoCodigo = $alerta['nodo_codigo'] ?? null;
    $nodoNombre = $alerta['nodo_nombre_corto'] ?? $nodoCodigo;
    $nodoLargo = $alerta['nodo_nombre_largo'] ?? null;
    $nodoRol = $alerta['nodo_rol'] ?? null;
    $nodoProvincia = $alerta['nodo_provincia'] ?? null;

    $datos = $alerta['datos'] ?? [];
    $reaperturas = (int) ($alerta['reaperturas'] ?? 0);
@endphp

<x-layout :title="__('portal.alerts.single_title', ['id' => $alerta['id'] ?? '', 'name' => config('proyecto.nombre')])" :description="__('portal.alerts.single_desc')">
    <div class="contenedor seccion">
        <div style="max-width: 820px; margin: 0 auto;">
            <!-- Enlace volver -->
            <a href="/alertas{{ $langQuery }}" style="font-size: 0.9rem; color: var(--color-texto-2); text-decoration: none; display: inline-flex; align-items: center; gap: 0.35rem; margin-bottom: 1.5rem;">
                {{ __('portal.alerts.back_to_list') }}
            </a>

            <!-- Encabezado principal de la incidencia -->
            <article class="tarjeta" style="padding: 2rem; margin-bottom: 1.75rem;">
                <header style="border-bottom: 1px solid var(--color-borde); padding-bottom: 1.25rem; margin-bottom: 1.5rem;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; margin-bottom: 0.85rem; flex-wrap: wrap;">
                        <h1 style="font-size: 1.85rem; font-weight: 800; color: var(--color-texto-1); margin: 0; display: flex; align-items: center; gap: 0.5rem;">
                            <span aria-hidden="true">{{ $alerta['regla_icono'] ?? '⚠️' }}</span>
                            <span>{{ $alerta['regla_nombre'] ?? $alerta['regla'] ?? __('portal.alerts.detected_incident') }}</span>
                        </h1>
                        <span style="font-family: monospace; font-size: 0.82rem; background: var(--color-superficie-sutil); border: 1px solid var(--color-borde); padding: 0.25rem 0.6rem; border-radius: var(--radio-sm); color: var(--color-texto-2);">
                            {{ $alerta['id'] ?? '' }}
                        </span>
                    </div>

                    <!-- Badges y metadatos del estado -->
                    <div style="display: flex; flex-wrap: wrap; gap: 1rem; align-items: center; font-size: 0.9rem; color: var(--color-texto-2);">
                        <div style="display: flex; align-items: center; gap: 0.35rem;">
                            <span>{{ __('portal.alerts.risk_label') }}</span>
                            <x-chip-estado :tipo="$chipTipo" :texto="$textoRiesgo" />
                        </div>
                        <span>·</span>
                        <div style="display: flex; align-items: center; gap: 0.35rem;">
                            <span>{{ __('portal.alerts.status_label') }}</span>
                            <strong style="color: {{ $estadoKey === 'abierta' ? 'var(--color-critico-texto)' : 'var(--mapa-verde)' }};">
                                {{ $estadoKey === 'abierta' ? '🔴' : '🟢' }} {{ $textoEstado }}
                            </strong>
                        </div>
                        <span>·</span>
                        <div>
                            <span>{{ __('portal.alerts.type_label') }}</span>
                            <strong>{{ $alerta['regla_categoria'] ?? ucfirst($alerta['tipo'] ?? 'red') }}</strong>
                        </div>
                        @if($reaperturas > 0)
                            <span>·</span>
                            <span style="background: var(--color-superficie-sutil); border: 1px solid var(--color-borde); padding: 0.15rem 0.5rem; border-radius: var(--radio-sm); font-size: 0.8rem; font-weight: 600;">
                                🔄 {{ __('portal.alerts.reopen_count', ['count' => $reaperturas]) }}
                            </span>
                        @endif
                    </div>
                </header>

                <!-- Resumen directo del aviso -->
                <div style="background: var(--color-superficie-sutil); border-left: 4px solid {{ $estadoKey === 'abierta' ? 'var(--color-critico-texto)' : 'var(--mapa-verde)' }}; padding: 1.25rem; border-radius: var(--radio-sm); margin-bottom: 2rem;">
                    <div style="font-size: 0.82rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--color-texto-3); margin-bottom: 0.35rem;">
                        {{ __('portal.alerts.detected_incident') }}
                    </div>
                    <div style="font-size: 1.15rem; font-weight: 700; color: var(--color-texto-1); line-height: 1.45;">
                        {{ $alerta['mensaje'] ?? ($alerta['regla_descripcion'] ?? __('portal.alerts.no_description')) }}
                    </div>
                    @if($estadoKey === 'resuelta' && !empty($alerta['resuelta_en']))
                        <div style="font-size: 0.85rem; color: var(--mapa-verde); margin-top: 0.5rem; font-weight: 600;">
                            ✓ {{ __('portal.alerts.resolved_at') }} {{ $alerta['resuelta_en'] }}
                        </div>
                    @endif
                </div>

                <!-- Sección explicativa: ¿Por qué ocurre este aviso? -->
                <div style="margin-bottom: 2rem;">
                    <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--color-texto-1); margin-bottom: 0.65rem; display: flex; align-items: center; gap: 0.4rem;">
                        <span>💡</span>
                        <span>{{ __('portal.alerts.why_title') }}</span>
                    </h2>
                    <p style="color: var(--color-texto-2); line-height: 1.6; font-size: 0.96rem; margin: 0;">
                        {{ $alerta['regla_por_que'] ?? $alerta['regla_descripcion'] }}
                    </p>
                </div>

                <!-- Sección didáctica: ¿Cómo solucionarlo? -->
                <div style="background: rgba(43, 140, 241, 0.07); border: 1px solid rgba(43, 140, 241, 0.25); border-radius: var(--radio-md); padding: 1.25rem; margin-bottom: 2rem;">
                    <h2 style="font-size: 1.15rem; font-weight: 700; color: var(--color-texto-1); margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.4rem;">
                        <span>🛠️</span>
                        <span>{{ __('portal.alerts.how_to_fix_title') }}</span>
                    </h2>
                    <div style="color: var(--color-texto-1); line-height: 1.6; font-size: 0.94rem;">
                        {!! nl2br(e($alerta['regla_como_solucionar'])) !!}
                    </div>
                </div>

                <!-- Tarjeta del nodo afectado -->
                @if(!$esGlobal && !empty($nodoCodigo))
                    <div style="background: var(--color-superficie-sutil); border: 1px solid var(--color-borde); border-radius: var(--radio-md); padding: 1.25rem; margin-bottom: 2rem;">
                        <h2 style="font-size: 1.05rem; font-weight: 700; margin-bottom: 0.75rem; color: var(--color-texto-1); display: flex; align-items: center; gap: 0.35rem;">
                            <span>📻</span>
                            <span>{{ __('portal.alerts.node_details_title') }}</span>
                        </h2>

                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 0.75rem; margin-bottom: 1.25rem;">
                            <div>
                                <div style="font-size: 0.75rem; color: var(--color-texto-3); text-transform: uppercase;">Nombre Corto</div>
                                <div style="font-size: 1.05rem; font-weight: 700; color: var(--color-texto-1);">{{ $nodoNombre }}</div>
                            </div>
                            @if($nodoLargo)
                                <div>
                                    <div style="font-size: 0.75rem; color: var(--color-texto-3); text-transform: uppercase;">Nombre Completo</div>
                                    <div style="font-size: 0.95rem; font-weight: 600; color: var(--color-texto-2);">{{ $nodoLargo }}</div>
                                </div>
                            @endif
                            <div>
                                <div style="font-size: 0.75rem; color: var(--color-texto-3); text-transform: uppercase;">Identificador Hex</div>
                                <code>{{ $nodoCodigo }}</code>
                            </div>
                            @if($nodoRol)
                                <div>
                                    <div style="font-size: 0.75rem; color: var(--color-texto-3); text-transform: uppercase;">Rol Configurado</div>
                                    <span style="font-size: 0.85rem; font-weight: 600; background: var(--color-superficie); padding: 0.15rem 0.4rem; border-radius: var(--radio-sm); border: 1px solid var(--color-borde);">
                                        {{ $nodoRol }}
                                    </span>
                                </div>
                            @endif
                            @if($nodoProvincia)
                                <div>
                                    <div style="font-size: 0.75rem; color: var(--color-texto-3); text-transform: uppercase;">Provincia</div>
                                    <div style="font-size: 0.95rem; font-weight: 600; color: var(--color-texto-2);">{{ $nodoProvincia }}</div>
                                </div>
                            @endif
                        </div>

                        <div style="display: flex; flex-wrap: wrap; gap: 0.75rem; align-items: center; border-top: 1px solid var(--color-borde); padding-top: 1rem;">
                            <a href="/revisa-tu-nodo/{{ $nodoCodigo }}{{ $langQuery }}" class="btn btn-primario" style="font-size: 0.88rem; padding: 0.4rem 0.85rem;">
                                {{ __('portal.alerts.audit_node') }}
                            </a>
                            <a href="/configurador{{ $langQuery }}" class="btn btn-secundario" style="font-size: 0.88rem; padding: 0.4rem 0.85rem;">
                                {{ __('portal.alerts.btn_configurator') }}
                            </a>
                        </div>
                    </div>
                @endif

                <!-- Parámetros técnicos y evidencias numéricas -->
                @if(!empty($datos) && is_array($datos))
                    <div style="margin-bottom: 2rem;">
                        <h2 style="font-size: 1.15rem; font-weight: 700; color: var(--color-texto-1); margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.4rem;">
                            <span>📊</span>
                            <span>{{ __('portal.alerts.evidence_title') }}</span>
                        </h2>

                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 0.75rem;">
                            @foreach($datos as $clave => $valor)
                                @php
                                    $label = ucwords(str_replace('_', ' ', (string) $clave));
                                    $valorStr = is_array($valor) ? json_encode($valor) : (string) $valor;
                                    if ($valorStr === 'true') $valorStr = 'Sí';
                                    if ($valorStr === 'false') $valorStr = 'No';
                                @endphp
                                <div style="background: var(--color-superficie-sutil); border: 1px solid var(--color-borde); border-radius: var(--radio-sm); padding: 0.75rem;">
                                    <div style="font-size: 0.74rem; text-transform: uppercase; color: var(--color-texto-3); font-weight: 600; margin-bottom: 0.25rem;">
                                        {{ $label }}
                                    </div>
                                    <div style="font-family: monospace; font-size: 1rem; font-weight: 700; color: var(--color-texto-1);">
                                        {{ $valorStr }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Línea temporal de la alerta -->
                <div style="border-top: 1px solid var(--color-borde); padding-top: 1.25rem;">
                    <h2 style="font-size: 1.05rem; font-weight: 700; color: var(--color-texto-1); margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.35rem;">
                        <span>⏱️</span>
                        <span>{{ __('portal.alerts.timeline_title') }}</span>
                    </h2>

                    <div style="display: flex; flex-direction: column; gap: 0.5rem; font-size: 0.88rem; color: var(--color-texto-2);">
                        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                            <span>{{ __('portal.alerts.first_detected') }}:</span>
                            <strong style="color: var(--color-texto-1); font-family: monospace;">{{ $alerta['inicio_at'] ?? ($alerta['abierta_en'] ?? '—') }}</strong>
                        </div>
                        @if(!empty($alerta['actualizada_en']))
                            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                                <span>{{ __('portal.alerts.last_update') }}:</span>
                                <strong style="color: var(--color-texto-1); font-family: monospace;">{{ $alerta['actualizada_en'] }}</strong>
                            </div>
                        @endif
                        @if(!empty($alerta['resuelta_en']))
                            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                                <span>{{ __('portal.alerts.resolved_at') }}:</span>
                                <strong style="color: var(--mapa-verde); font-family: monospace;">{{ $alerta['resuelta_en'] }}</strong>
                            </div>
                        @endif
                    </div>
                </div>
            </article>
        </div>
    </div>
</x-layout>
