<x-filament-widgets::widget>
    <x-filament::section wire:poll.30s>
        <x-slot name="heading">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="flex items-center gap-3">
                    <span class="text-lg font-bold text-gray-900 dark:text-white">Estado de la Red y Microservicios</span>
                    <span class="text-xs px-2 py-0.5 rounded-full font-mono {{ $latido_ok ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300' }}">
                        Daemon: {{ $latido_ok ? 'Activo (' . $latido_hace . ')' : 'Sin latido reciente' }}
                    </span>
                </div>

                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-2 text-xs">
                        <span class="inline-flex items-center gap-1 font-semibold text-emerald-600 dark:text-emerald-400">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span> {{ $operativos }} OK
                        </span>
                        @if($caidos > 0)
                            <span class="inline-flex items-center gap-1 font-semibold text-rose-600 dark:text-rose-400">
                                <span class="w-2 h-2 rounded-full bg-rose-500"></span> {{ $caidos }} Caídos
                            </span>
                        @endif
                        <span class="inline-flex items-center gap-1 text-gray-500 dark:text-gray-400">
                            <span class="w-2 h-2 rounded-full bg-gray-400"></span> {{ $pendientes }} Pendientes
                        </span>
                    </div>

                    <button
                        type="button"
                        wire:click="comprobarAhora"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center gap-1 text-xs px-2.5 py-1 rounded bg-indigo-50 text-indigo-700 hover:bg-indigo-100 dark:bg-indigo-950 dark:text-indigo-300 dark:hover:bg-indigo-900 transition-colors font-medium border border-indigo-200 dark:border-indigo-800"
                    >
                        <span wire:loading.remove wire:target="comprobarAhora">↻ Comprobar ahora</span>
                        <span wire:loading wire:target="comprobarAhora">Comprobando...</span>
                    </button>
                </div>
            </div>
        </x-slot>

        {{-- Tabla de servicios --}}
        <div class="overflow-x-auto mt-2">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-800 text-gray-500 dark:text-gray-400 font-mono uppercase tracking-wider">
                        <th class="py-2 px-3">Servicio</th>
                        <th class="py-2 px-3">Tipo / Fase</th>
                        <th class="py-2 px-3">Estado</th>
                        <th class="py-2 px-3">Latencia</th>
                        <th class="py-2 px-3">Último chequeo</th>
                        <th class="py-2 px-3">Detalle / Diagnóstico</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach($servicios as $s)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors">
                            <td class="py-2.5 px-3 font-semibold text-gray-900 dark:text-gray-100">
                                {{ $s['nombre'] }}
                                <span class="block text-[11px] font-mono text-gray-400 font-normal">{{ $s['clave'] }}</span>
                            </td>
                            <td class="py-2.5 px-3 font-mono text-gray-500 dark:text-gray-400">
                                <span class="uppercase">{{ $s['tipo'] }}</span>
                                <span class="text-gray-400">· F{{ $s['fase'] }}</span>
                            </td>
                            <td class="py-2.5 px-3">
                                @if($s['color'] === 'verde')
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        {{ $s['etiqueta'] }}
                                    </span>
                                @elseif($s['color'] === 'amarillo')
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        {{ $s['etiqueta'] }}
                                    </span>
                                @elseif($s['color'] === 'rojo')
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                        {{ $s['etiqueta'] }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                        <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                                        {{ $s['etiqueta'] }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-2.5 px-3 font-mono text-gray-600 dark:text-gray-300">
                                @if($s['latencia_ms'] !== null)
                                    {{ $s['latencia_ms'] }} ms
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="py-2.5 px-3 text-gray-500 dark:text-gray-400">
                                {{ $s['hace'] }}
                            </td>
                            <td class="py-2.5 px-3 max-w-xs truncate text-gray-600 dark:text-gray-300 font-mono text-[11px]">
                                @if($s['motivo'])
                                    <span class="{{ $s['color'] === 'rojo' ? 'text-rose-600 dark:text-rose-400 font-semibold' : '' }}">{{ $s['motivo'] }}</span>
                                @elseif($s['clave'] === 'sync-peers' && is_array($s['detalle']) && isset($s['detalle']['peers']))
                                    @php $peers = (array) $s['detalle']['peers']; @endphp
                                    <span>{{ count($peers) }} peers ({{ collect($peers)->where('ok', true)->count() }} activos)</span>
                                @elseif($s['ok'])
                                    <span class="text-gray-400">OK</span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Historial reciente de transiciones --}}
        @if($transiciones->isNotEmpty())
            <div class="mt-6 pt-4 border-t border-gray-100 dark:border-gray-800">
                <h4 class="text-xs font-mono font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2">Transiciones de Estado Recientes</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-2">
                    @foreach($transiciones as $t)
                        <div class="p-2 rounded bg-gray-50 dark:bg-gray-800/40 border border-gray-100 dark:border-gray-800 text-[11px]">
                            <div class="flex items-center justify-between font-mono">
                                <span class="font-bold {{ $t['ok'] ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                    {{ $t['ok'] ? '● RECUPERADO' : '▲ CAÍDA' }}
                                </span>
                                <span class="text-gray-400">{{ $t['hace'] }}</span>
                            </div>
                            <div class="font-semibold text-gray-800 dark:text-gray-200 mt-0.5">{{ $t['servicio'] }}</div>
                            @if($t['motivo'])
                                <div class="text-gray-500 truncate text-[10px] mt-0.5" title="{{ $t['motivo'] }}">{{ $t['motivo'] }}</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
