<x-filament-widgets::widget>
    <x-filament::section wire:poll.30s>
        <x-slot name="heading">
            <div class="fi-widget-header-flex">
                <div class="fi-widget-title-group">
                    <span class="fi-widget-icon" aria-hidden="true">📡</span>
                    <span class="fi-widget-title">{{ __('admin.widgets.routers.heading') }}</span>
                    <span class="fi-widget-pill fi-widget-pill-neutral">
                        {{ trans_choice('admin.widgets.routers.routers_badge', $total, ['count' => $total]) }}
                        <span class="opacity-75">· 
                            @if(($ambito ?? 'andalucia') === 'ambos')
                                {{ __('admin.ambitos.andalucia_title') }} + {{ __('admin.ambitos.espana_title') }}
                            @elseif(($ambito ?? 'andalucia') === 'espana')
                                {{ __('admin.ambitos.espana_title') }}
                            @else
                                {{ __('admin.ambitos.andalucia_title') }}
                            @endif
                        </span>
                    </span>
                    @if($en_alerta > 0)
                        <span class="fi-widget-pill fi-widget-pill-danger">
                            ⚠️ {{ __('admin.widgets.routers.in_attention', ['count' => $en_alerta]) }}
                        </span>
                    @endif
                </div>

                <div class="fi-widget-meta-group">
                    <span class="fi-widget-subtitle">{{ __('admin.widgets.routers.subtitle') }}</span>
                </div>
            </div>
        </x-slot>

        @if($error_fuente)
            <div class="fi-dashboard-alert fi-dashboard-alert-warning">
                <span class="fi-alert-icon" aria-hidden="true">ℹ️</span>
                <div>
                    <strong>{{ __('admin.widgets.routers.unavailable_title') }}</strong>
                    <p class="text-xs mt-0.5 opacity-90">{{ $error_fuente }}</p>
                </div>
            </div>
        @elseif(empty($routers))
            <div class="fi-dashboard-empty">
                <span class="text-2xl" aria-hidden="true">📻</span>
                <p class="font-semibold text-sm mt-1">{{ __('admin.widgets.routers.empty_title') }}</p>
                <p class="text-xs opacity-75 mt-0.5">{{ __('admin.widgets.routers.empty_desc') }}</p>
            </div>
        @else
            <div class="fi-table-responsive-container">
                <table class="fi-dashboard-table">
                    <thead>
                        <tr>
                            <th class="fi-th">{{ __('admin.widgets.routers.th_router') }}</th>
                            <th class="fi-th">{{ __('admin.widgets.routers.th_hardware') }}</th>
                            <th class="fi-th">{{ __('admin.widgets.routers.th_power') }}</th>
                            <th class="fi-th">{{ __('admin.widgets.routers.th_channel') }}</th>
                            <th class="fi-th">{{ __('admin.widgets.routers.th_tx') }}</th>
                            <th class="fi-th">{{ __('admin.widgets.routers.th_last_report') }}</th>
                            <th class="fi-th text-right">{{ __('admin.widgets.routers.th_action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($routers as $r)
                            <tr class="fi-tr">
                                <td class="fi-td">
                                    <div class="flex items-center gap-2">
                                        <span class="fi-status-dot {{ $r['esta_online'] ? 'fi-status-dot-online' : 'fi-status-dot-offline' }}" title="{{ $r['esta_online'] ? __('admin.widgets.routers.online') : __('admin.widgets.routers.offline') }}"></span>
                                        <div>
                                            <div class="font-bold text-sm text-gray-900 dark:text-gray-100 flex items-center gap-1.5">
                                                <span>{{ $r['short_name'] }}</span>
                                                @if($r['long_name'])
                                                    <span class="text-xs font-normal opacity-60">({{ $r['long_name'] }})</span>
                                                @endif
                                            </div>
                                            <div class="font-mono text-xs opacity-60 tracking-wider">{{ $r['id'] }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="fi-td">
                                    <div class="text-xs font-medium">{{ $r['hw_model'] === 'Desconocido' ? __('admin.widgets.routers.unknown') : $r['hw_model'] }}</div>
                                    <div class="text-[11px] opacity-70 font-mono">{{ $r['province'] }} · {{ $r['role'] }}</div>
                                </td>
                                <td class="fi-td">
                                    @if($r['powered'])
                                        <div class="fi-power-badge fi-power-badge-grid">
                                            <span>⚡ {{ __('admin.widgets.routers.power_grid_solar') }}</span>
                                            @if($r['voltage'])
                                                <span class="font-mono text-[10px] opacity-80">{{ $r['voltage'] }}V</span>
                                            @endif
                                        </div>
                                    @elseif($r['battery_level'] !== null)
                                        <div class="fi-battery-container">
                                            <div class="flex justify-between items-center text-xs font-mono font-bold mb-1">
                                                <span>🔋 {{ $r['battery_level'] }}%</span>
                                                @if($r['voltage'])
                                                    <span class="text-[10px] font-normal opacity-70">{{ $r['voltage'] }}V</span>
                                                @endif
                                            </div>
                                            <div class="fi-progress-track">
                                                <div class="fi-progress-fill fi-progress-{{ $r['bateria_color'] }}" style="width: {{ min(100, max(5, $r['battery_level'])) }}%;"></div>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-xs opacity-50 font-mono">—</span>
                                    @endif
                                </td>
                                <td class="fi-td">
                                    @if($r['chutil'] !== null)
                                        <div class="fi-chutil-container">
                                            <div class="flex justify-between items-center text-xs font-mono mb-1">
                                                <span class="font-bold">{{ $r['chutil'] }}%</span>
                                                <span class="text-[10px] fi-chip-{{ $r['chutil_color'] }}">
                                                    {{ $r['chutil'] < 20 ? __('admin.widgets.routers.chutil_light') : ($r['chutil'] < 40 ? __('admin.widgets.routers.chutil_moderate') : __('admin.widgets.routers.chutil_heavy')) }}
                                                </span>
                                            </div>
                                            <div class="fi-progress-track">
                                                <div class="fi-progress-fill fi-progress-{{ $r['chutil_color'] }}" style="width: {{ min(100, max(5, $r['chutil'])) }}%;"></div>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-xs opacity-50 font-mono">—</span>
                                    @endif
                                </td>
                                <td class="fi-td font-mono text-xs">
                                    @if($airTx = $r['air_tx'])
                                        <span class="font-semibold fi-text-{{ $r['tx_color'] }}">
                                            {{ $airTx }}% TX
                                        </span>
                                    @else
                                        <span class="opacity-50">—</span>
                                    @endif
                                </td>
                                <td class="fi-td text-xs">
                                    <span class="{{ $r['esta_online'] ? 'opacity-80' : 'text-rose-500 font-semibold' }}">
                                        {{ $r['hace'] }}
                                    </span>
                                </td>
                                <td class="fi-td text-right">
                                    <a
                                        href="/revisa-tu-nodo/{{ ltrim($r['id'], '!') }}"
                                        target="_blank"
                                        class="fi-btn-inspect"
                                        title="{{ __('admin.widgets.routers.inspect_title') }}"
                                    >
                                        {{ __('admin.widgets.routers.btn_inspect') }}
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
