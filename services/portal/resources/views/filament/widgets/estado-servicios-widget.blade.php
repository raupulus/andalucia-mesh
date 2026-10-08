<x-filament-widgets::widget>
    <x-filament::section wire:poll.30s>
        <x-slot name="heading">
            <div class="fi-widget-header-flex">
                <div class="fi-widget-title-group">
                    <span class="fi-widget-icon" aria-hidden="true">🖥️</span>
                    <span class="fi-widget-title">Estado de la Red y Microservicios</span>
                    <span class="fi-widget-pill {{ $latido_ok ? 'fi-widget-pill-success' : 'fi-widget-pill-danger' }}">
                        Daemon: {{ $latido_ok ? 'Activo (' . $latido_hace . ')' : 'Sin latido reciente' }}
                    </span>
                </div>

                <div class="fi-widget-actions-group">
                    <div class="fi-status-counter-group">
                        <span class="fi-status-counter fi-status-counter-ok">
                            <span class="fi-status-dot-sm bg-emerald-500"></span> {{ $operativos }} OK
                        </span>
                        @if($caidos > 0)
                            <span class="fi-status-counter fi-status-counter-danger">
                                <span class="fi-status-dot-sm bg-rose-500"></span> {{ $caidos }} Caídos
                            </span>
                        @endif
                        <span class="fi-status-counter fi-status-counter-pending">
                            <span class="fi-status-dot-sm bg-gray-400"></span> {{ $pendientes }} Pendientes
                        </span>
                    </div>

                    <button
                        type="button"
                        wire:click="comprobarAhora"
                        wire:loading.attr="disabled"
                        class="fi-btn-refresh"
                    >
                        <span wire:loading.remove wire:target="comprobarAhora">↻ Comprobar ahora</span>
                        <span wire:loading wire:target="comprobarAhora">Comprobando...</span>
                    </button>
                </div>
            </div>
        </x-slot>

        {{-- Tabla de servicios --}}
        <div class="fi-table-responsive-container mt-2">
            <table class="fi-dashboard-table">
                <thead>
                    <tr>
                        <th class="fi-th">Servicio</th>
                        <th class="fi-th">Tipo / Fase</th>
                        <th class="fi-th">Estado</th>
                        <th class="fi-th">Latencia</th>
                        <th class="fi-th">Último chequeo</th>
                        <th class="fi-th">Detalle / Diagnóstico</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($servicios as $s)
                        <tr class="fi-tr">
                            <td class="fi-td">
                                <div class="font-bold text-sm text-gray-900 dark:text-gray-100">{{ $s['nombre'] }}</div>
                                <div class="font-mono text-[11px] opacity-60">{{ $s['clave'] }}</div>
                            </td>
                            <td class="fi-td font-mono text-xs opacity-80">
                                <span class="uppercase">{{ $s['tipo'] }}</span>
                                <span class="opacity-60">· F{{ $s['fase'] }}</span>
                            </td>
                            <td class="fi-td">
                                @if($s['color'] === 'verde')
                                    <span class="fi-badge-pill fi-badge-success">
                                        <span class="fi-status-dot-sm bg-emerald-500"></span> {{ $s['etiqueta'] }}
                                    </span>
                                @elseif($s['color'] === 'amarillo')
                                    <span class="fi-badge-pill fi-badge-warning">
                                        <span class="fi-status-dot-sm bg-amber-500"></span> {{ $s['etiqueta'] }}
                                    </span>
                                @elseif($s['color'] === 'rojo')
                                    <span class="fi-badge-pill fi-badge-danger">
                                        <span class="fi-status-dot-sm bg-rose-500"></span> {{ $s['etiqueta'] }}
                                    </span>
                                @else
                                    <span class="fi-badge-pill fi-badge-neutral">
                                        <span class="fi-status-dot-sm bg-gray-400"></span> {{ $s['etiqueta'] }}
                                    </span>
                                @endif
                            </td>
                            <td class="fi-td font-mono text-xs">
                                @if($s['latencia_ms'] !== null)
                                    <span>{{ $s['latencia_ms'] }} ms</span>
                                @else
                                    <span class="opacity-40">—</span>
                                @endif
                            </td>
                            <td class="fi-td text-xs opacity-75">
                                {{ $s['hace'] }}
                            </td>
                            <td class="fi-td text-xs font-mono max-w-xs truncate">
                                @if($s['motivo'])
                                    <span class="{{ $s['color'] === 'rojo' ? 'text-rose-500 font-semibold' : 'opacity-80' }}">{{ $s['motivo'] }}</span>
                                @elseif($s['clave'] === 'sync-peers' && is_array($s['detalle']) && isset($s['detalle']['peers']))
                                    @php $peers = (array) $s['detalle']['peers']; @endphp
                                    <span class="opacity-80">{{ count($peers) }} peers ({{ collect($peers)->where('ok', true)->count() }} activos)</span>
                                @elseif($s['ok'])
                                    <span class="opacity-60">Operativo</span>
                                @else
                                    <span class="opacity-40">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Historial reciente de transiciones estilizado --}}
        @if($transiciones->isNotEmpty())
            <div class="fi-transitions-wrapper">
                <div class="fi-transitions-header">
                    <span class="fi-transitions-title">Historial de Eventos Recientes</span>
                    <span class="text-xs opacity-60">Últimos cambios de estado registrados en la plataforma</span>
                </div>
                <div class="fi-transitions-grid">
                    @foreach($transiciones as $t)
                        <div class="fi-transition-card {{ $t['ok'] ? 'fi-transition-ok' : 'fi-transition-fail' }}">
                            <div class="flex items-center justify-between font-mono text-xs">
                                <span class="font-bold {{ $t['ok'] ? 'text-emerald-500' : 'text-rose-500' }}">
                                    {{ $t['ok'] ? '● RECUPERADO' : '▲ CAÍDA' }}
                                </span>
                                <span class="opacity-60 text-[11px]">{{ $t['hace'] }}</span>
                            </div>
                            <div class="font-bold text-sm mt-1 text-gray-900 dark:text-gray-100">{{ $t['servicio'] }}</div>
                            @if($t['motivo'])
                                <div class="text-[11px] opacity-75 truncate mt-0.5" title="{{ $t['motivo'] }}">{{ $t['motivo'] }}</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
