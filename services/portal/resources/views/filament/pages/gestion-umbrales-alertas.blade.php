<x-filament-panels::page>
    <div class="fi-umbrales-admin-page space-y-6">
        {{-- Banner Informativo de Contexto --}}
        <div class="rounded-xl border border-emerald-600/30 bg-emerald-50/50 p-5 dark:border-emerald-500/20 dark:bg-emerald-950/20">
            <div class="flex items-start gap-4">
                <div class="rounded-lg bg-emerald-600 p-2.5 text-white dark:bg-emerald-500 dark:text-gray-950">
                    <x-heroicon-o-adjustments-horizontal class="h-6 w-6" />
                </div>
                <div class="flex-1">
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">
                        {{ __('admin.alert_thresholds.title') }}
                    </h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                        {{ __('admin.alert_thresholds.subheading') }}
                    </p>
                </div>
                <div>
                    <button
                        type="button"
                        wire:click="save"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center gap-2 rounded-lg bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-500 dark:bg-emerald-600 dark:hover:bg-emerald-500"
                    >
                        <span wire:loading.remove wire:target="save">
                            <x-heroicon-m-check class="h-5 w-5" />
                        </span>
                        <span wire:loading wire:target="save" class="inline-block animate-spin">
                            <x-heroicon-m-arrow-path class="h-5 w-5" />
                        </span>
                        <span>{{ __('admin.alert_thresholds.btn_save') }}</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Selector de Pestañas por Grupo de Roles --}}
        <div class="border-b border-gray-200 dark:border-gray-800">
            <nav class="-mb-px flex space-x-6" aria-label="Tabs">
                <button
                    type="button"
                    wire:click="setActiveTab('router')"
                    class="group inline-flex items-center gap-2 border-b-2 py-4 px-1 text-sm font-medium transition {{ $activeTab === 'router' ? 'border-emerald-600 text-emerald-700 dark:border-emerald-500 dark:text-emerald-400 font-bold' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' }}"
                >
                    <x-heroicon-o-server-stack class="h-5 w-5" />
                    <span>{{ __('admin.alert_thresholds.tab_router') }}</span>
                    <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300">ROUTER</span>
                </button>

                <button
                    type="button"
                    wire:click="setActiveTab('client')"
                    class="group inline-flex items-center gap-2 border-b-2 py-4 px-1 text-sm font-medium transition {{ $activeTab === 'client' ? 'border-emerald-600 text-emerald-700 dark:border-emerald-500 dark:text-emerald-400 font-bold' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' }}"
                >
                    <x-heroicon-o-device-phone-mobile class="h-5 w-5" />
                    <span>{{ __('admin.alert_thresholds.tab_client') }}</span>
                    <span class="rounded-full bg-blue-100 px-2 py-0.5 text-xs text-blue-800 dark:bg-blue-900/50 dark:text-blue-300">CLIENT</span>
                </button>

                <button
                    type="button"
                    wire:click="setActiveTab('client_mute')"
                    class="group inline-flex items-center gap-2 border-b-2 py-4 px-1 text-sm font-medium transition {{ $activeTab === 'client_mute' ? 'border-emerald-600 text-emerald-700 dark:border-emerald-500 dark:text-emerald-400 font-bold' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' }}"
                >
                    <x-heroicon-o-speaker-x-mark class="h-5 w-5" />
                    <span>{{ __('admin.alert_thresholds.tab_client_mute') }}</span>
                    <span class="rounded-full bg-purple-100 px-2 py-0.5 text-xs text-purple-800 dark:bg-purple-900/50 dark:text-purple-300">MUTE</span>
                </button>
            </nav>
        </div>

        {{-- Explicación del grupo activo --}}
        <div class="text-xs text-gray-500 dark:text-gray-400">
            @if ($activeTab === 'router')
                {{ __('admin.alert_thresholds.tab_router_desc') }}
            @elseif ($activeTab === 'client')
                {{ __('admin.alert_thresholds.tab_client_desc') }}
            @else
                {{ __('admin.alert_thresholds.tab_client_mute_desc') }}
            @endif
        </div>

        {{-- Grid de Secciones de Parámetros --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

            {{-- 1. Saltos LoRa en Origen --}}
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center gap-2 border-b border-gray-100 pb-3 dark:border-gray-800">
                    <x-heroicon-o-arrow-trending-up class="h-5 w-5 text-emerald-600 dark:text-emerald-400" />
                    <div>
                        <h4 class="font-bold text-gray-900 dark:text-white">{{ __('admin.alert_thresholds.sec_hops') }}</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('admin.alert_thresholds.sec_hops_desc') }}</p>
                    </div>
                </div>

                <div class="mt-4 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">
                            {{ __('admin.alert_thresholds.param_hop_limit_recommended') }}
                        </label>
                        <div class="relative mt-1">
                            <input
                                type="number"
                                step="1"
                                min="1"
                                max="7"
                                wire:model="umbrales.{{ $activeTab }}.hop_limit_recommended"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                            />
                            <span class="absolute inset-y-0 right-0 flex items-center pr-3 text-xs text-gray-400">saltos</span>
                        </div>
                        @error("umbrales.{$activeTab}.hop_limit_recommended")
                            <p class="mt-1 text-xs font-semibold text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">
                            {{ __('admin.alert_thresholds.param_hop_limit_warning') }}
                        </label>
                        <div class="relative mt-1">
                            <input
                                type="number"
                                step="1"
                                min="1"
                                max="7"
                                wire:model="umbrales.{{ $activeTab }}.hop_limit_warning"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                            />
                            <span class="absolute inset-y-0 right-0 flex items-center pr-3 text-xs text-gray-400">saltos</span>
                        </div>
                        @error("umbrales.{$activeTab}.hop_limit_warning")
                            <p class="mt-1 text-xs font-semibold text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">
                            {{ __('admin.alert_thresholds.param_hop_limit_critical') }}
                        </label>
                        <div class="relative mt-1">
                            <input
                                type="number"
                                step="1"
                                min="1"
                                max="7"
                                wire:model="umbrales.{{ $activeTab }}.hop_limit_critical"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                            />
                            <span class="absolute inset-y-0 right-0 flex items-center pr-3 text-xs text-gray-400">saltos</span>
                        </div>
                        @error("umbrales.{$activeTab}.hop_limit_critical")
                            <p class="mt-1 text-xs font-semibold text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- 2. Intervalos de Emisión --}}
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center gap-2 border-b border-gray-100 pb-3 dark:border-gray-800">
                    <x-heroicon-o-clock class="h-5 w-5 text-emerald-600 dark:text-emerald-400" />
                    <div>
                        <h4 class="font-bold text-gray-900 dark:text-white">{{ __('admin.alert_thresholds.sec_intervals') }}</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('admin.alert_thresholds.sec_intervals_desc') }}</p>
                    </div>
                </div>

                <div class="mt-4 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">
                            {{ __('admin.alert_thresholds.param_nodeinfo_interval_min_s') }}
                        </label>
                        <div class="relative mt-1">
                            <input
                                type="number"
                                step="60"
                                min="60"
                                wire:model="umbrales.{{ $activeTab }}.nodeinfo_interval_min_s"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                            />
                            <span class="absolute inset-y-0 right-0 flex items-center pr-3 text-xs text-gray-400">segundos</span>
                        </div>
                        @error("umbrales.{$activeTab}.nodeinfo_interval_min_s")
                            <p class="mt-1 text-xs font-semibold text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">
                            {{ __('admin.alert_thresholds.param_nodeinfo_interval_recommended_s') }}
                        </label>
                        <div class="relative mt-1">
                            <input
                                type="number"
                                step="60"
                                min="60"
                                wire:model="umbrales.{{ $activeTab }}.nodeinfo_interval_recommended_s"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                            />
                            <span class="absolute inset-y-0 right-0 flex items-center pr-3 text-xs text-gray-400">segundos</span>
                        </div>
                        @error("umbrales.{$activeTab}.nodeinfo_interval_recommended_s")
                            <p class="mt-1 text-xs font-semibold text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">
                            {{ __('admin.alert_thresholds.param_telemetry_interval_min_s') }}
                        </label>
                        <div class="relative mt-1">
                            <input
                                type="number"
                                step="60"
                                min="60"
                                wire:model="umbrales.{{ $activeTab }}.telemetry_interval_min_s"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                            />
                            <span class="absolute inset-y-0 right-0 flex items-center pr-3 text-xs text-gray-400">segundos</span>
                        </div>
                        @error("umbrales.{$activeTab}.telemetry_interval_min_s")
                            <p class="mt-1 text-xs font-semibold text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">
                            {{ __('admin.alert_thresholds.param_telemetry_interval_recommended_s') }}
                        </label>
                        <div class="relative mt-1">
                            <input
                                type="number"
                                step="60"
                                min="60"
                                wire:model="umbrales.{{ $activeTab }}.telemetry_interval_recommended_s"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                            />
                            <span class="absolute inset-y-0 right-0 flex items-center pr-3 text-xs text-gray-400">segundos</span>
                        </div>
                        @error("umbrales.{$activeTab}.telemetry_interval_recommended_s")
                            <p class="mt-1 text-xs font-semibold text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- 3. Batería y Energía --}}
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center gap-2 border-b border-gray-100 pb-3 dark:border-gray-800">
                    <x-heroicon-o-bolt class="h-5 w-5 text-emerald-600 dark:text-emerald-400" />
                    <div>
                        <h4 class="font-bold text-gray-900 dark:text-white">{{ __('admin.alert_thresholds.sec_battery') }}</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('admin.alert_thresholds.sec_battery_desc') }}</p>
                    </div>
                </div>

                <div class="mt-4 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">
                            {{ __('admin.alert_thresholds.param_battery_warning_pct') }}
                        </label>
                        <div class="relative mt-1">
                            <input
                                type="number"
                                step="1"
                                min="0"
                                max="100"
                                wire:model="umbrales.{{ $activeTab }}.battery_warning_pct"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                            />
                            <span class="absolute inset-y-0 right-0 flex items-center pr-3 text-xs text-gray-400">%</span>
                        </div>
                        @error("umbrales.{$activeTab}.battery_warning_pct")
                            <p class="mt-1 text-xs font-semibold text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">
                            {{ __('admin.alert_thresholds.param_battery_critical_pct') }}
                        </label>
                        <div class="relative mt-1">
                            <input
                                type="number"
                                step="1"
                                min="0"
                                max="100"
                                wire:model="umbrales.{{ $activeTab }}.battery_critical_pct"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                            />
                            <span class="absolute inset-y-0 right-0 flex items-center pr-3 text-xs text-gray-400">%</span>
                        </div>
                        @error("umbrales.{$activeTab}.battery_critical_pct")
                            <p class="mt-1 text-xs font-semibold text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">
                            {{ __('admin.alert_thresholds.param_battery_resolution_pct') }}
                        </label>
                        <div class="relative mt-1">
                            <input
                                type="number"
                                step="1"
                                min="0"
                                max="100"
                                wire:model="umbrales.{{ $activeTab }}.battery_resolution_pct"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                            />
                            <span class="absolute inset-y-0 right-0 flex items-center pr-3 text-xs text-gray-400">%</span>
                        </div>
                        @error("umbrales.{$activeTab}.battery_resolution_pct")
                            <p class="mt-1 text-xs font-semibold text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">
                            {{ __('admin.alert_thresholds.param_sunset_battery_min_pct') }}
                        </label>
                        <div class="relative mt-1">
                            <input
                                type="number"
                                step="1"
                                min="0"
                                max="100"
                                wire:model="umbrales.{{ $activeTab }}.sunset_battery_min_pct"
                                @if ($activeTab !== 'router') disabled @endif
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white disabled:opacity-50"
                            />
                            <span class="absolute inset-y-0 right-0 flex items-center pr-3 text-xs text-gray-400">%</span>
                        </div>
                        @if ($activeTab !== 'router')
                            <p class="mt-0.5 text-xs text-gray-400">Control solar inactivo para este grupo de nodos (0 = desactivado).</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- 4. Silencios y Reinicios --}}
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center gap-2 border-b border-gray-100 pb-3 dark:border-gray-800">
                    <x-heroicon-o-signal-slash class="h-5 w-5 text-emerald-600 dark:text-emerald-400" />
                    <div>
                        <h4 class="font-bold text-gray-900 dark:text-white">{{ __('admin.alert_thresholds.sec_silence') }}</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('admin.alert_thresholds.sec_silence_desc') }}</p>
                    </div>
                </div>

                <div class="mt-4 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">
                            {{ __('admin.alert_thresholds.param_silence_warning_h') }}
                        </label>
                        <div class="relative mt-1">
                            <input
                                type="number"
                                step="0.5"
                                min="0"
                                wire:model="umbrales.{{ $activeTab }}.silence_warning_h"
                                @if ($activeTab !== 'router') disabled @endif
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white disabled:opacity-50"
                            />
                            <span class="absolute inset-y-0 right-0 flex items-center pr-3 text-xs text-gray-400">horas</span>
                        </div>
                        @if ($activeTab !== 'router')
                            <p class="mt-0.5 text-xs text-gray-400">Inactivo en nodos clientes y portables (0 = desactivado).</p>
                        @endif
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">
                            {{ __('admin.alert_thresholds.param_silence_critical_h') }}
                        </label>
                        <div class="relative mt-1">
                            <input
                                type="number"
                                step="0.5"
                                min="0"
                                wire:model="umbrales.{{ $activeTab }}.silence_critical_h"
                                @if ($activeTab !== 'router') disabled @endif
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white disabled:opacity-50"
                            />
                            <span class="absolute inset-y-0 right-0 flex items-center pr-3 text-xs text-gray-400">horas</span>
                        </div>
                        @if ($activeTab !== 'router')
                            <p class="mt-0.5 text-xs text-gray-400">Inactivo en nodos clientes y portables (0 = desactivado).</p>
                        @endif
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">
                            {{ __('admin.alert_thresholds.param_reboot_loop_count_5m') }}
                        </label>
                        <div class="relative mt-1">
                            <input
                                type="number"
                                step="1"
                                min="1"
                                wire:model="umbrales.{{ $activeTab }}.reboot_loop_count_5m"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                            />
                            <span class="absolute inset-y-0 right-0 flex items-center pr-3 text-xs text-gray-400">reinicios</span>
                        </div>
                        @error("umbrales.{$activeTab}.reboot_loop_count_5m")
                            <p class="mt-1 text-xs font-semibold text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">
                            {{ __('admin.alert_thresholds.param_reboot_loop_count_10m') }}
                        </label>
                        <div class="relative mt-1">
                            <input
                                type="number"
                                step="1"
                                min="1"
                                wire:model="umbrales.{{ $activeTab }}.reboot_loop_count_10m"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                            />
                            <span class="absolute inset-y-0 right-0 flex items-center pr-3 text-xs text-gray-400">reinicios</span>
                        </div>
                        @error("umbrales.{$activeTab}.reboot_loop_count_10m")
                            <p class="mt-1 text-xs font-semibold text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

        </div>

        {{-- Barra de Guardar al Pie --}}
        <div class="flex items-center justify-end gap-3 border-t border-gray-200 pt-5 dark:border-gray-800">
            <button
                type="button"
                wire:click="save"
                wire:loading.attr="disabled"
                class="inline-flex items-center gap-2 rounded-lg bg-emerald-700 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-500 dark:bg-emerald-600 dark:hover:bg-emerald-500"
            >
                <span wire:loading.remove wire:target="save">
                    <x-heroicon-m-check class="h-5 w-5" />
                </span>
                <span wire:loading wire:target="save" class="inline-block animate-spin">
                    <x-heroicon-m-arrow-path class="h-5 w-5" />
                </span>
                <span>{{ __('admin.alert_thresholds.btn_save') }}</span>
            </button>
        </div>
    </div>
</x-filament-panels::page>
