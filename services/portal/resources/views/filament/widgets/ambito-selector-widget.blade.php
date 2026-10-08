<x-filament-widgets::widget>
    <div class="fi-ambito-selector-container mb-1">
        <div class="flex items-center justify-between mb-2.5 px-0.5">
            <div class="flex items-center gap-2">
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    {{ __('admin.ambitos.heading') }}
                </span>
            </div>
            <div class="text-[11px] font-medium text-gray-500 dark:text-gray-400">
                <span class="hidden sm:inline">{{ __('admin.ambitos.active_context') }}</span>
                <strong class="font-bold text-gray-900 dark:text-gray-100">
                    @if($ambito === 'andalucia')
                        {{ __('admin.ambitos.andalucia_title') }}
                    @elseif($ambito === 'espana')
                        {{ __('admin.ambitos.espana_title') }}
                    @else
                        {{ __('admin.ambitos.global_title') }}
                    @endif
                </strong>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            {{-- 1. ANDALUCÍA (Activo por defecto) --}}
            <button
                type="button"
                wire:click="setAmbito('andalucia')"
                class="group text-left w-full p-4 rounded-xl border transition-all duration-200 cursor-pointer shadow-xs relative overflow-hidden flex items-center justify-between {{ $ambito === 'andalucia' ? 'border-[#007A33] bg-[#E9FCEF] dark:bg-[#007A33]/20 ring-2 ring-[#007A33]' : 'border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 hover:border-gray-300 dark:hover:border-gray-700' }}"
                aria-pressed="{{ $ambito === 'andalucia' ? 'true' : 'false' }}"
            >
                <div class="flex items-center gap-3.5">
                    <x-icono-bandera idioma="es" :tamano="38" />
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-sm sm:text-base text-gray-900 dark:text-white">
                                {{ __('admin.ambitos.andalucia_title') }}
                            </span>
                            @if($ambito === 'andalucia')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#007A33] text-white shadow-xs">
                                    ✓ {{ __('admin.ambitos.active_badge') }}
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 font-medium">
                            {{ __('admin.ambitos.andalucia_desc') }}
                        </p>
                    </div>
                </div>

                <div class="text-right pl-2 shrink-0">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold font-mono {{ $ambito === 'andalucia' ? 'bg-[#007A33]/20 text-[#007A33] dark:text-[#9CF1BA]' : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300' }}">
                        {{ trans_choice('admin.ambitos.routers_count', $conteo_andalucia, ['count' => $conteo_andalucia]) }}
                    </span>
                </div>
            </button>

            {{-- 2. ESPAÑA --}}
            <button
                type="button"
                wire:click="setAmbito('espana')"
                class="group text-left w-full p-4 rounded-xl border transition-all duration-200 cursor-pointer shadow-xs relative overflow-hidden flex items-center justify-between {{ $ambito === 'espana' ? 'border-[#AA151B] bg-red-50/70 dark:bg-red-950/25 ring-2 ring-[#AA151B]' : 'border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 hover:border-gray-300 dark:hover:border-gray-700' }}"
                aria-pressed="{{ $ambito === 'espana' ? 'true' : 'false' }}"
            >
                <div class="flex items-center gap-3.5">
                    <x-icono-bandera idioma="espana" :tamano="38" />
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-sm sm:text-base text-gray-900 dark:text-white">
                                {{ __('admin.ambitos.espana_title') }}
                            </span>
                            @if($ambito === 'espana')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#AA151B] text-white shadow-xs">
                                    ✓ {{ __('admin.ambitos.active_badge') }}
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 font-medium">
                            {{ __('admin.ambitos.espana_desc') }}
                        </p>
                    </div>
                </div>

                <div class="text-right pl-2 shrink-0">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold font-mono {{ $ambito === 'espana' ? 'bg-red-100 dark:bg-red-900/40 text-[#AA151B] dark:text-red-300' : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300' }}">
                        {{ trans_choice('admin.ambitos.routers_count', $conteo_espana, ['count' => $conteo_espana]) }}
                    </span>
                </div>
            </button>

            {{-- 3. TODA LA MALLA (Global) --}}
            <button
                type="button"
                wire:click="setAmbito('global')"
                class="group text-left w-full p-4 rounded-xl border transition-all duration-200 cursor-pointer shadow-xs relative overflow-hidden flex items-center justify-between {{ $ambito === 'global' ? 'border-sky-600 bg-sky-50/70 dark:bg-sky-950/25 ring-2 ring-sky-600' : 'border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 hover:border-gray-300 dark:hover:border-gray-700' }}"
                aria-pressed="{{ $ambito === 'global' ? 'true' : 'false' }}"
            >
                <div class="flex items-center gap-3.5">
                    <div class="w-[38px] h-[38px] rounded-full bg-slate-800 flex items-center justify-center text-white shadow-xs shrink-0 ring-1 ring-black/10">
                        <svg class="w-5 h-5 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-sm sm:text-base text-gray-900 dark:text-white">
                                {{ __('admin.ambitos.global_title') }}
                            </span>
                            @if($ambito === 'global')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-sky-600 text-white shadow-xs">
                                    ✓ {{ __('admin.ambitos.active_badge') }}
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 font-medium">
                            {{ __('admin.ambitos.global_desc') }}
                        </p>
                    </div>
                </div>

                <div class="text-right pl-2 shrink-0">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold font-mono {{ $ambito === 'global' ? 'bg-sky-100 dark:bg-sky-900/40 text-sky-700 dark:text-sky-300' : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300' }}">
                        {{ trans_choice('admin.ambitos.routers_count', $conteo_global, ['count' => $conteo_global]) }}
                    </span>
                </div>
            </button>
        </div>
    </div>
</x-filament-widgets::widget>
