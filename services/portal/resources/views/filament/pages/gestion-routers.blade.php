<x-filament-panels::page>
    @vite(['resources/js/mesh-admin.js'])

    <div x-data="meshAdmin()" class="space-y-6">
        {{-- Tarjeta 1: Conexión con el Nodo Local Físico del Operador --}}
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center justify-between flex-wrap gap-3">
                    <div class="flex items-center gap-2">
                        <span class="text-xl">🔌</span>
                        <span class="font-bold text-base text-gray-900 dark:text-gray-100">
                            {{ __('admin.gestion_routers.local_connection_heading') }}
                        </span>
                    </div>

                    {{-- Indicador de estado de conexión --}}
                    <div class="flex items-center gap-2">
                        <template x-if="connectionStatus === 'connected'">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                {{ __('admin.gestion_routers.status_connected') }}
                                <span x-show="localNode.hexId" x-text="`(${localNode.hexId})`" class="font-mono"></span>
                            </span>
                        </template>

                        <template x-if="connectionStatus === 'connecting'">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-500/15 text-amber-600 dark:text-amber-400 border border-amber-500/30">
                                <span class="w-2 h-2 rounded-full bg-amber-500 animate-ping"></span>
                                {{ __('admin.gestion_routers.status_connecting') }}
                            </span>
                        </template>

                        <template x-if="connectionStatus === 'disconnected'">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-gray-500/15 text-gray-600 dark:text-gray-400 border border-gray-500/30">
                                <span class="w-2 h-2 rounded-full bg-gray-400"></span>
                                {{ __('admin.gestion_routers.status_disconnected') }}
                            </span>
                        </template>

                        <template x-if="connectionStatus === 'error'">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-500/15 text-rose-600 dark:text-rose-400 border border-rose-500/30">
                                <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                                {{ __('admin.gestion_routers.status_error') }}
                            </span>
                        </template>
                    </div>
                </div>
            </x-slot>

            <div class="space-y-4">
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    {{ __('admin.gestion_routers.local_connection_desc') }}
                </p>

                {{-- Selector de transporte y controles --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 items-end">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('admin.gestion_routers.transport_label') }}
                        </label>
                        <select x-model="transportType" :disabled="connectionStatus === 'connected' || connectionStatus === 'connecting'" class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 py-2 px-3 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="serial">🔌 Web Serial (USB / COM)</option>
                            <option value="bluetooth">📶 Web Bluetooth (BLE)</option>
                            <option value="http">🌐 WiFi Local (HTTP / IP)</option>
                        </select>
                    </div>

                    <div x-show="transportType === 'serial'">
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('admin.gestion_routers.baud_rate_label') }}
                        </label>
                        <select x-model="baudRate" :disabled="connectionStatus === 'connected' || connectionStatus === 'connecting'" class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 py-2 px-3 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 font-mono">
                            <option value="115200">115200 baud</option>
                            <option value="921600">921600 baud</option>
                        </select>
                    </div>

                    <div x-show="transportType === 'http'">
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('admin.gestion_routers.http_host_label') }}
                        </label>
                        <input type="text" x-model="httpHost" :disabled="connectionStatus === 'connected' || connectionStatus === 'connecting'" placeholder="192.168.1.100 o meshtastic.local" class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 py-2 px-3 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 font-mono" />
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" x-show="connectionStatus !== 'connected'" @click="connectLocalNode()" :disabled="connectionStatus === 'connecting'" class="w-full inline-flex justify-center items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 disabled:opacity-50 text-white font-bold text-xs rounded-lg transition-colors shadow-sm">
                            <span x-show="connectionStatus !== 'connecting'">⚡ {{ __('admin.gestion_routers.btn_connect') }}</span>
                            <span x-show="connectionStatus === 'connecting'">⏳ {{ __('admin.gestion_routers.status_connecting') }}</span>
                        </button>

                        <button type="button" x-show="connectionStatus === 'connected'" @click="disconnectLocalNode()" class="w-full inline-flex justify-center items-center gap-2 px-4 py-2 bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white font-bold text-xs rounded-lg transition-colors shadow-sm">
                            🔌 {{ __('admin.gestion_routers.btn_disconnect') }}
                        </button>
                    </div>
                </div>

                {{-- Mensaje de error de conexión --}}
                <div x-show="errorMessage" class="p-3 bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-800 rounded-lg text-xs text-rose-700 dark:text-rose-300 flex items-center gap-2">
                    <span>⚠️</span>
                    <span x-text="errorMessage"></span>
                </div>
            </div>
        </x-filament::section>

        {{-- Tarjeta 2: Selector del Router Objetivo de Andalucía --}}
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <span class="text-xl">🎯</span>
                    <span class="font-bold text-base text-gray-900 dark:text-gray-100">
                        {{ __('admin.gestion_routers.target_router_heading') }}
                    </span>
                </div>
            </x-slot>

            <div class="space-y-4">
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    {{ __('admin.gestion_routers.target_router_desc') }}
                </p>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('admin.gestion_routers.select_router_label') }}
                        </label>
                        <select @change="onRouterSelectChange($event)" class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 py-2.5 px-3 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="">{{ __('admin.gestion_routers.select_placeholder') }}</option>
                            <option value="manual">⚙️ {{ __('admin.gestion_routers.select_manual_option') }}</option>
                            <optgroup label="{{ __('admin.gestion_routers.andalucia_optgroup') }}">
                                @foreach($routers as $r)
                                    <option value="{{ $r['dec_id'] }}"
                                            data-hex="{{ $r['node_id'] }}"
                                            data-name="{{ $r['short_name'] }} · {{ $r['long_name'] }}"
                                            data-province="{{ $r['province'] }}"
                                            data-role="{{ $r['role'] }}"
                                            data-status="{{ $r['status'] }}">
                                        [{{ $r['province'] }}] {{ $r['short_name'] }} ({{ $r['node_id'] }}) · {{ $r['long_name'] }} [{{ $r['role'] }}]
                                    </option>
                                @endforeach
                            </optgroup>
                        </select>
                    </div>

                    <div x-show="selectedTargetMode === 'manual'">
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('admin.gestion_routers.manual_node_label') }}
                        </label>
                        <input type="text" x-model="manualNodeInput" placeholder="!2df0a1b2 o 770744754" class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 py-2.5 px-3 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 font-mono" />
                    </div>
                </div>

                {{-- Ficha resumen del router seleccionado --}}
                <div x-show="selectedRouterHex || manualNodeInput" class="p-3 bg-emerald-500/10 border border-emerald-500/20 rounded-lg flex items-center justify-between flex-wrap gap-2 text-xs">
                    <div class="flex items-center gap-3">
                        <span class="text-lg">📡</span>
                        <div>
                            <div class="font-bold text-gray-900 dark:text-gray-100" x-text="selectedTargetMode === 'manual' ? (manualNodeInput || 'Nodo Manual') : selectedRouterName"></div>
                            <div class="text-gray-500 dark:text-gray-400 font-mono text-[11px]">
                                <span class="font-bold text-emerald-700 dark:text-emerald-400" x-text="selectedTargetMode === 'manual' ? manualNodeInput : selectedRouterHex"></span>
                                <template x-if="selectedRouterProvince">
                                    <span x-text="` · Prov: ${selectedRouterProvince}`"></span>
                                </template>
                                <template x-if="selectedRouterRole">
                                    <span x-text="` · Rol actual: ${selectedRouterRole}`"></span>
                                </template>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <template x-if="selectedRouterStatus">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider"
                                  :class="{
                                      'bg-emerald-500/20 text-emerald-700 dark:text-emerald-300': selectedRouterStatus === 'managed',
                                      'bg-blue-500/20 text-blue-700 dark:text-blue-300': selectedRouterStatus === 'known',
                                      'bg-amber-500/20 text-amber-700 dark:text-amber-300': selectedRouterStatus === 'new'
                                  }"
                                  x-text="selectedRouterStatus">
                            </span>
                        </template>
                    </div>
                </div>
            </div>
        </x-filament::section>

        {{-- Tarjeta 3: Pestañas de Acciones Remotas --}}
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl shadow-sm overflow-hidden">
            {{-- Barra de navegación entre pestañas --}}
            <div class="flex border-b border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-950/40 overflow-x-auto">
                <button type="button" @click="activeTab = 'roles'" :class="activeTab === 'roles' ? 'border-emerald-600 text-emerald-700 dark:text-emerald-400 bg-white dark:bg-gray-900 font-bold' : 'border-transparent text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100'" class="px-4 py-3 border-b-2 text-xs flex items-center gap-2 transition-all whitespace-nowrap">
                    <span>🔀</span>
                    <span>{{ __('admin.gestion_routers.tab_roles') }}</span>
                </button>

                <button type="button" @click="activeTab = 'favoritos'" :class="activeTab === 'favoritos' ? 'border-emerald-600 text-emerald-700 dark:text-emerald-400 bg-white dark:bg-gray-900 font-bold' : 'border-transparent text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100'" class="px-4 py-3 border-b-2 text-xs flex items-center gap-2 transition-all whitespace-nowrap">
                    <span>⭐</span>
                    <span>{{ __('admin.gestion_routers.tab_favorites') }}</span>
                </button>

                <button type="button" @click="activeTab = 'sondeo'" :class="activeTab === 'sondeo' ? 'border-emerald-600 text-emerald-700 dark:text-emerald-400 bg-white dark:bg-gray-900 font-bold' : 'border-transparent text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100'" class="px-4 py-3 border-b-2 text-xs flex items-center gap-2 transition-all whitespace-nowrap">
                    <span>📡</span>
                    <span>{{ __('admin.gestion_routers.tab_poll') }}</span>
                </button>

                <button type="button" @click="activeTab = 'unicast'" :class="activeTab === 'unicast' ? 'border-emerald-600 text-emerald-700 dark:text-emerald-400 bg-white dark:bg-gray-900 font-bold' : 'border-transparent text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100'" class="px-4 py-3 border-b-2 text-xs flex items-center gap-2 transition-all whitespace-nowrap">
                    <span>🎯</span>
                    <span>{{ __('admin.gestion_routers.tab_unicast') }}</span>
                </button>

                <button type="button" @click="activeTab = 'mantenimiento'" :class="activeTab === 'mantenimiento' ? 'border-emerald-600 text-emerald-700 dark:text-emerald-400 bg-white dark:bg-gray-900 font-bold' : 'border-transparent text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100'" class="px-4 py-3 border-b-2 text-xs flex items-center gap-2 transition-all whitespace-nowrap">
                    <span>⚙️</span>
                    <span>{{ __('admin.gestion_routers.tab_maintenance') }}</span>
                </button>
            </div>

            <div class="p-6">
                {{-- PESTAÑA 1: ROLES --}}
                <div x-show="activeTab === 'roles'" class="space-y-5">
                    <div>
                        <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100 mb-1 flex items-center gap-1.5">
                            <span>🔀</span> {{ __('admin.gestion_routers.roles_title') }}
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                            {{ __('admin.gestion_routers.roles_desc') }}
                        </p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                        <label class="relative flex flex-col p-4 border rounded-xl cursor-pointer transition-all"
                               :class="selectedRole == 1 ? 'border-emerald-600 bg-emerald-50/50 dark:bg-emerald-950/20 ring-1 ring-emerald-600' : 'border-gray-200 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800/50'">
                            <input type="radio" name="targetRole" value="1" x-model="selectedRole" class="sr-only">
                            <div class="flex items-center justify-between mb-1">
                                <span class="font-bold text-xs text-gray-900 dark:text-gray-100">CLIENT_MUTE</span>
                                <span class="text-xs">🔇</span>
                            </div>
                            <span class="text-[11px] text-gray-500 dark:text-gray-400">Silencia el nodo. No retransmite paquetes de terceros. Ideal ante spam o bucles.</span>
                        </label>

                        <label class="relative flex flex-col p-4 border rounded-xl cursor-pointer transition-all"
                               :class="selectedRole == 2 ? 'border-emerald-600 bg-emerald-50/50 dark:bg-emerald-950/20 ring-1 ring-emerald-600' : 'border-gray-200 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800/50'">
                            <input type="radio" name="targetRole" value="2" x-model="selectedRole" class="sr-only">
                            <div class="flex items-center justify-between mb-1">
                                <span class="font-bold text-xs text-gray-900 dark:text-gray-100">ROUTER</span>
                                <span class="text-xs">⚡</span>
                            </div>
                            <span class="text-[11px] text-gray-500 dark:text-gray-400">Reenvío prioritario inmediato en malla. Solo nodos fijos en ubicaciones estratégicas.</span>
                        </label>

                        <label class="relative flex flex-col p-4 border rounded-xl cursor-pointer transition-all"
                               :class="selectedRole == 11 ? 'border-emerald-600 bg-emerald-50/50 dark:bg-emerald-950/20 ring-1 ring-emerald-600' : 'border-gray-200 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800/50'">
                            <input type="radio" name="targetRole" value="11" x-model="selectedRole" class="sr-only">
                            <div class="flex items-center justify-between mb-1">
                                <span class="font-bold text-xs text-gray-900 dark:text-gray-100">ROUTER_LATE</span>
                                <span class="text-xs">⏱️</span>
                            </div>
                            <span class="text-[11px] text-gray-500 dark:text-gray-400">Reenvío retardado para respaldo. Evita colisiones cuando hay otro router principal.</span>
                        </label>

                        <label class="relative flex flex-col p-4 border rounded-xl cursor-pointer transition-all"
                               :class="selectedRole == 0 ? 'border-emerald-600 bg-emerald-50/50 dark:bg-emerald-950/20 ring-1 ring-emerald-600' : 'border-gray-200 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800/50'">
                            <input type="radio" name="targetRole" value="0" x-model="selectedRole" class="sr-only">
                            <div class="flex items-center justify-between mb-1">
                                <span class="font-bold text-xs text-gray-900 dark:text-gray-100">CLIENT</span>
                                <span class="text-xs">📱</span>
                            </div>
                            <span class="text-[11px] text-gray-500 dark:text-gray-400">Nodo cliente estándar normal con retransmisión comunitaria equilibrada.</span>
                        </label>
                    </div>

                    <div class="pt-2">
                        <button type="button" @click="applyRemoteRole()" :disabled="roleSending || connectionStatus !== 'connected' || (!selectedRouterNodeNum && !manualNodeInput)" class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 disabled:opacity-50 text-white font-bold text-xs rounded-lg transition-colors shadow-sm">
                            <span x-show="!roleSending">🚀 {{ __('admin.gestion_routers.btn_apply_role') }}</span>
                            <span x-show="roleSending">⏳ {{ __('admin.gestion_routers.transmitting') }}</span>
                        </button>
                    </div>
                </div>

                {{-- PESTAÑA 2: FAVORITOS --}}
                <div x-show="activeTab === 'favoritos'" class="space-y-5">
                    <div>
                        <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100 mb-1 flex items-center gap-1.5">
                            <span>⭐</span> {{ __('admin.gestion_routers.favorites_title') }}
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                            {{ __('admin.gestion_routers.favorites_desc') }}
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                {{ __('admin.gestion_routers.favorite_input_label') }}
                            </label>
                            <input type="text" x-model="favoriteNodeInput" placeholder="!2df0a1b2 o ID decimal" class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 py-2.5 px-3 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 font-mono" />
                        </div>

                        <div class="flex items-center gap-2">
                            <button type="button" @click="applyRemoteFavorite('add')" :disabled="favoriteSending || !favoriteNodeInput || connectionStatus !== 'connected'" class="flex-1 inline-flex justify-center items-center gap-1.5 px-3 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 disabled:opacity-50 text-white font-bold text-xs rounded-lg transition-colors shadow-sm whitespace-nowrap">
                                <span>⭐</span> {{ __('admin.gestion_routers.btn_add_favorite') }}
                            </button>

                            <button type="button" @click="applyRemoteFavorite('remove')" :disabled="favoriteSending || !favoriteNodeInput || connectionStatus !== 'connected'" class="flex-1 inline-flex justify-center items-center gap-1.5 px-3 py-2.5 bg-rose-600 hover:bg-rose-700 active:bg-rose-800 disabled:opacity-50 text-white font-bold text-xs rounded-lg transition-colors shadow-sm whitespace-nowrap">
                                <span>🗑️</span> {{ __('admin.gestion_routers.btn_remove_favorite') }}
                            </button>
                        </div>
                    </div>

                    {{-- Lista de favoritos manipulados en la sesión --}}
                    <div x-show="sessionFavorites.length > 0" class="pt-3 border-t border-gray-100 dark:border-gray-800">
                        <div class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('admin.gestion_routers.session_favorites_heading') }}
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <template x-for="fav in sessionFavorites" :key="fav">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-mono bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-500/20">
                                    <span>⭐</span>
                                    <span x-text="fav"></span>
                                    <button type="button" @click="favoriteNodeInput = fav; applyRemoteFavorite('remove')" class="text-rose-500 hover:text-rose-700 ml-1 font-bold">×</button>
                                </span>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- PESTAÑA 3: SONDEO DE MALLA (BROADCAST) --}}
                <div x-show="activeTab === 'sondeo'" class="space-y-5">
                    <div>
                        <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100 mb-1 flex items-center gap-1.5">
                            <span>📡</span> {{ __('admin.gestion_routers.poll_title') }}
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                            {{ __('admin.gestion_routers.poll_desc') }}
                        </p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <button type="button" @click="sendMeshPoll('nodeinfo')" :disabled="pollSending || connectionStatus !== 'connected'" class="p-4 border border-gray-200 dark:border-gray-800 rounded-xl hover:border-emerald-500 hover:bg-emerald-50/20 dark:hover:bg-emerald-950/20 transition-all text-left group">
                            <div class="text-2xl mb-2">📡</div>
                            <div class="font-bold text-xs text-gray-900 dark:text-gray-100 group-hover:text-emerald-600 transition-colors">
                                {{ __('admin.gestion_routers.btn_poll_nodeinfo') }}
                            </div>
                            <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">
                                Solicita a todos los nodos en cobertura que emitan su nombre, modelo y datos básicos.
                            </div>
                        </button>

                        <button type="button" @click="sendMeshPoll('position')" :disabled="pollSending || connectionStatus !== 'connected'" class="p-4 border border-gray-200 dark:border-gray-800 rounded-xl hover:border-emerald-500 hover:bg-emerald-50/20 dark:hover:bg-emerald-950/20 transition-all text-left group">
                            <div class="text-2xl mb-2">📍</div>
                            <div class="font-bold text-xs text-gray-900 dark:text-gray-100 group-hover:text-emerald-600 transition-colors">
                                {{ __('admin.gestion_routers.btn_poll_position') }}
                            </div>
                            <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">
                                Pide a los nodos con GPS o posición fija que transmitan sus coordenadas.
                            </div>
                        </button>

                        <button type="button" @click="sendMeshPoll('telemetry')" :disabled="pollSending || connectionStatus !== 'connected'" class="p-4 border border-gray-200 dark:border-gray-800 rounded-xl hover:border-emerald-500 hover:bg-emerald-50/20 dark:hover:bg-emerald-950/20 transition-all text-left group">
                            <div class="text-2xl mb-2">🔋</div>
                            <div class="font-bold text-xs text-gray-900 dark:text-gray-100 group-hover:text-emerald-600 transition-colors">
                                {{ __('admin.gestion_routers.btn_poll_telemetry') }}
                            </div>
                            <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">
                                Solicita datos de batería, voltaje y porcentaje de utilización de canal (ChUtil).
                            </div>
                        </button>
                    </div>
                </div>

                {{-- PESTAÑA 4: PETICIÓN A UN NODO (UNICAST) --}}
                <div x-show="activeTab === 'unicast'" class="space-y-5">
                    <div>
                        <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100 mb-1 flex items-center gap-1.5">
                            <span>🎯</span> {{ __('admin.gestion_routers.unicast_title') }}
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                            {{ __('admin.gestion_routers.unicast_desc') }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('admin.gestion_routers.unicast_target_label') }}
                        </label>
                        <input type="text" x-model="unicastTargetInput" :placeholder="selectedRouterHex ? `Por defecto router actual: ${selectedRouterHex}` : 'Introduce !hex o decimal'" class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 py-2.5 px-3 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 font-mono" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                        <button type="button" @click="sendUnicastRequest('nodeinfo')" :disabled="unicastSending || connectionStatus !== 'connected'" class="p-3 border border-gray-200 dark:border-gray-800 rounded-lg hover:border-emerald-500 hover:bg-emerald-50/20 text-left transition-all">
                            <div class="font-bold text-xs text-gray-900 dark:text-gray-100 flex items-center gap-1.5">
                                <span>ℹ️</span> {{ __('admin.gestion_routers.btn_req_nodeinfo') }}
                            </div>
                            <div class="text-[10px] text-gray-500 dark:text-gray-400 mt-1">Identidad y hardware</div>
                        </button>

                        <button type="button" @click="sendUnicastRequest('position')" :disabled="unicastSending || connectionStatus !== 'connected'" class="p-3 border border-gray-200 dark:border-gray-800 rounded-lg hover:border-emerald-500 hover:bg-emerald-50/20 text-left transition-all">
                            <div class="font-bold text-xs text-gray-900 dark:text-gray-100 flex items-center gap-1.5">
                                <span>📍</span> {{ __('admin.gestion_routers.btn_req_position') }}
                            </div>
                            <div class="text-[10px] text-gray-500 dark:text-gray-400 mt-1">Ubicación GPS</div>
                        </button>

                        <button type="button" @click="sendUnicastRequest('telemetry')" :disabled="unicastSending || connectionStatus !== 'connected'" class="p-3 border border-gray-200 dark:border-gray-800 rounded-lg hover:border-emerald-500 hover:bg-emerald-50/20 text-left transition-all">
                            <div class="font-bold text-xs text-gray-900 dark:text-gray-100 flex items-center gap-1.5">
                                <span>🔋</span> {{ __('admin.gestion_routers.btn_req_telemetry') }}
                            </div>
                            <div class="text-[10px] text-gray-500 dark:text-gray-400 mt-1">Métricas y batería</div>
                        </button>

                        <button type="button" @click="sendUnicastRequest('traceroute')" :disabled="unicastSending || connectionStatus !== 'connected'" class="p-3 border border-gray-200 dark:border-gray-800 rounded-lg hover:border-emerald-500 hover:bg-emerald-50/20 text-left transition-all">
                            <div class="font-bold text-xs text-gray-900 dark:text-gray-100 flex items-center gap-1.5">
                                <span>🔄</span> {{ __('admin.gestion_routers.btn_req_traceroute') }}
                            </div>
                            <div class="text-[10px] text-gray-500 dark:text-gray-400 mt-1">Rastreo de saltos / ruta</div>
                        </button>
                    </div>
                </div>

                {{-- PESTAÑA 5: MANTENIMIENTO --}}
                <div x-show="activeTab === 'mantenimiento'" class="space-y-5">
                    <div>
                        <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100 mb-1 flex items-center gap-1.5">
                            <span>⚙️</span> {{ __('admin.gestion_routers.maintenance_title') }}
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                            {{ __('admin.gestion_routers.maintenance_desc') }}
                        </p>
                    </div>

                    <div class="p-4 bg-amber-500/10 border border-amber-500/20 rounded-xl space-y-4">
                        <div class="flex items-center gap-2 text-xs font-bold text-amber-800 dark:text-amber-300">
                            <span>⚠️</span> {{ __('admin.gestion_routers.reboot_heading') }}
                        </div>
                        <p class="text-xs text-amber-700 dark:text-amber-400">
                            {{ __('admin.gestion_routers.reboot_desc') }}
                        </p>

                        <div class="flex items-center gap-3">
                            <label class="text-xs font-semibold text-gray-700 dark:text-gray-300">
                                {{ __('admin.gestion_routers.delay_secs_label') }}:
                            </label>
                            <input type="number" min="1" max="600" x-model="rebootSeconds" class="w-24 text-xs rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 py-1.5 px-3 shadow-sm font-mono" />
                            <span class="text-xs text-gray-500 dark:text-gray-400">segundos</span>
                        </div>

                        <button type="button" @click="applyRemoteReboot()" :disabled="rebootSending || connectionStatus !== 'connected' || (!selectedRouterNodeNum && !manualNodeInput)" class="inline-flex items-center gap-2 px-4 py-2 bg-amber-600 hover:bg-amber-700 active:bg-amber-800 disabled:opacity-50 text-white font-bold text-xs rounded-lg transition-colors shadow-sm">
                            <span x-show="!rebootSending">⚠️ {{ __('admin.gestion_routers.btn_apply_reboot') }}</span>
                            <span x-show="rebootSending">⏳ {{ __('admin.gestion_routers.transmitting') }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tarjeta 4: Consola / Terminal de Actividad en Tiempo Real --}}
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center justify-between flex-wrap gap-3">
                    <div class="flex items-center gap-2">
                        <span class="text-xl">📟</span>
                        <span class="font-bold text-base text-gray-900 dark:text-gray-100">
                            {{ __('admin.gestion_routers.terminal_heading') }}
                        </span>
                        <span class="text-xs font-mono text-gray-400" x-text="`(${logs.length} eventos)`"></span>
                    </div>

                    <div class="flex items-center gap-2">
                        {{-- Filtro de log --}}
                        <select x-model="logFilter" class="text-[11px] rounded-md border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 py-1 px-2">
                            <option value="all">Todos</option>
                            <option value="tx">Solo TX (Transmitidos)</option>
                            <option value="rx">Solo RX (Recibidos)</option>
                            <option value="ack">Solo ACKs</option>
                            <option value="error">Solo Errores</option>
                        </select>

                        <button type="button" @click="copyLogs()" class="px-2.5 py-1 text-xs rounded-md border border-gray-300 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-700 dark:text-gray-300 transition-colors">
                            📋 Copiar
                        </button>

                        <button type="button" @click="clearLogs()" class="px-2.5 py-1 text-xs rounded-md border border-gray-300 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-700 dark:text-gray-300 transition-colors">
                            🗑️ Limpiar
                        </button>
                    </div>
                </div>
            </x-slot>

            <div id="meshAdminConsole" class="h-64 overflow-y-auto bg-slate-950 text-slate-100 font-mono text-[11px] p-3 rounded-lg border border-slate-800 space-y-1 select-text">
                <template x-if="filteredLogs.length === 0">
                    <div class="text-slate-500 italic py-4 text-center">
                        {{ __('admin.gestion_routers.terminal_empty') }}
                    </div>
                </template>

                <template x-for="(l, idx) in filteredLogs" :key="idx">
                    <div class="leading-relaxed flex items-start gap-2 hover:bg-slate-900/60 px-1 rounded transition-colors">
                        <span class="text-slate-500 select-none" x-text="`[${l.time}]`"></span>
                        
                        <template x-if="l.type === 'tx'">
                            <span class="px-1.5 py-0.2 rounded text-[10px] font-bold bg-blue-500/20 text-blue-400">TX ➡️</span>
                        </template>
                        <template x-if="l.type === 'rx'">
                            <span class="px-1.5 py-0.2 rounded text-[10px] font-bold bg-purple-500/20 text-purple-400">RX ⬅️</span>
                        </template>
                        <template x-if="l.type === 'ack'">
                            <span class="px-1.5 py-0.2 rounded text-[10px] font-bold bg-emerald-500/20 text-emerald-400">ACK ✅</span>
                        </template>
                        <template x-if="l.type === 'error'">
                            <span class="px-1.5 py-0.2 rounded text-[10px] font-bold bg-rose-500/20 text-rose-400">ERR ❌</span>
                        </template>
                        <template x-if="l.type === 'info'">
                            <span class="px-1.5 py-0.2 rounded text-[10px] font-bold bg-slate-500/20 text-slate-400">INFO</span>
                        </template>

                        <span class="flex-1 text-slate-200 break-all" x-text="l.text"></span>
                    </div>
                </template>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
