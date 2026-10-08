// ==============================================================================
// mesh-admin.js (resources/js/mesh-admin.js)
//
// Controlador cliente para el módulo de Gestión Remota de Routers en Andalucía Mesh.
// Conecta directamente desde el navegador del operador a su nodo Meshtastic local
// mediante Web Serial (USB), Web Bluetooth (BLE) o HTTP local, y transmite
// comandos administrativos remotos (Protobuf AdminMessage) y sondeos por LoRa.
//
// Reglas y contratos:
// - Protocolo: Meshtastic Protobuf oficial vía @meshtastic/core.
// - Seguridad: Todo el tráfico de control se emite desde el dispositivo local del operador.
// ==============================================================================

import { Buffer } from "buffer";

if (typeof window !== 'undefined') {
    window.Buffer = window.Buffer || Buffer;
    globalThis.Buffer = globalThis.Buffer || Buffer;
    window.global = window.global || window;
    window.process = window.process || { env: { NODE_ENV: 'production' }, cwd: () => '/' };
}

import { create, fromBinary, toBinary } from "@bufbuild/protobuf";
import { MeshDevice, Protobuf, Constants } from "@meshtastic/core";
import { TransportWebSerial } from "@meshtastic/transport-web-serial";
import { TransportWebBluetooth } from "@meshtastic/transport-web-bluetooth";
import { TransportHTTP } from "@meshtastic/transport-http";

const { AdminMessageSchema } = Protobuf.Admin;
const { ConfigSchema, Config_DeviceConfigSchema, Config_DeviceConfig_Role } = Protobuf.Config;
const { UserSchema, PositionSchema, RouteDiscoverySchema, RoutingSchema, Routing_Error, MeshPacketSchema, ToRadioSchema } = Protobuf.Mesh;
const { TelemetrySchema } = Protobuf.Telemetry;
const { PortNum } = Protobuf.Portnums;

/**
 * Diccionario descriptivo en español de los códigos de fallo de enrutamiento de Meshtastic.
 */
export const ROUTING_ERROR_DESCRIPTIONS = {
    [Routing_Error.NONE]: 'NONE (Éxito / ACK)',
    [Routing_Error.NO_ROUTE]: 'NO_ROUTE (Sin ruta en la malla hacia el destino)',
    [Routing_Error.GOT_NAK]: 'GOT_NAK (Se recibió NAK al retransmitir por la malla)',
    [Routing_Error.TIMEOUT]: 'TIMEOUT (Tiempo de espera agotado sin confirmación)',
    [Routing_Error.NO_INTERFACE]: 'NO_INTERFACE (Sin interfaz disponible para entregar el paquete)',
    [Routing_Error.MAX_RETRANSMIT]: 'MAX_RETRANSMIT (Límite máximo de saltos/retransmisión alcanzado)',
    [Routing_Error.NO_CHANNEL]: 'NO_CHANNEL (Canal no válido o deshabilitado)',
    [Routing_Error.TOO_LARGE]: 'TOO_LARGE (Paquete excede el MTU de LoRa)',
    [Routing_Error.NO_RESPONSE]: 'NO_RESPONSE (El nodo destino no respondió)',
    [Routing_Error.DUTY_CYCLE_LIMIT]: 'DUTY_CYCLE_LIMIT (Límite legal de ciclo de trabajo LoRa alcanzado)',
    [Routing_Error.BAD_REQUEST]: 'BAD_REQUEST (Petición rechazada por ser inválida)',
    [Routing_Error.NOT_AUTHORIZED]: 'NOT_AUTHORIZED (No autorizado en este canal o nodo)',
    [Routing_Error.PKI_FAILED]: 'PKI_FAILED (Fallo de cifrado en el nodo local)',
    [Routing_Error.PKI_UNKNOWN_PUBKEY]: 'PKI_UNKNOWN_PUBKEY (El router no responde por radio o no está en cobertura)',
    [Routing_Error.ADMIN_BAD_SESSION_KEY]: 'ADMIN_BAD_SESSION_KEY (Pase de sesión administrativo no válido o expirado)',
    [Routing_Error.ADMIN_PUBLIC_KEY_UNAUTHORIZED]: 'ADMIN_PUBLIC_KEY_UNAUTHORIZED (Tu clave pública no está autorizada en admin_key del router)',
    38: 'RATE_LIMIT_EXCEEDED (Límite legal o de airtime superado para este paquete)',
    39: 'PKI_SEND_FAIL_PUBLIC_KEY (El router no responde por radio o no está en cobertura)',
};

/**
 * Formatea cualquier error devuelto por la librería de Meshtastic a un texto comprensible y amigable para el operador.
 *
 * @param {any} err
 * @param {string} [targetHex='']
 * @returns {string}
 */
export function formatMeshtasticError(err, targetHex = '') {
    if (!err && err !== 0) return 'Error desconocido';
    if (typeof err === 'object') {
        if (err.error !== undefined) {
            const code = Number(err.error);
            const req = err.id ? ` [ReqID: ${err.id}]` : '';
            const targetStr = targetHex ? ` hacia ${targetHex}` : '';
            if (code === 39 || code === 52) {
                return `El router${targetStr} no responde por radio o no está en cobertura directa/malla de tu antena. Asegúrate de que el router esté encendido y al alcance.`;
            }
            if (code === 37 || code === 54) {
                return `Tu clave pública no está autorizada en la lista de administradores del router${targetStr}.`;
            }
            if (code === 36 || code === 53) {
                return `La sesión administrativa con el router${targetStr} ha expirado.`;
            }
            if (code === 3) {
                return `Tiempo de espera agotado sin confirmación (TIMEOUT). El router${targetStr} no respondió por radio.`;
            }
            if (code === 1) {
                return `No hay ruta hacia el router${targetStr} en la malla.`;
            }
            const desc = ROUTING_ERROR_DESCRIPTIONS[code] || `Error de enrutamiento código ${code}`;
            return `${desc}${req}`;
        }
        if (err.message) return err.message;
        try {
            return JSON.stringify(err);
        } catch {
            return String(err);
        }
    }
    return String(err);
}

/**
 * Convierte un número de nodo uint32 a formato hexadecimal de Meshtastic (!XXXXXXXX).
 *
 * @param {number} num
 * @returns {string}
 */
export function numToHex(num) {
    if (!num && num !== 0) return '!00000000';
    return '!' + (num >>> 0).toString(16).padStart(8, '0');
}

/**
 * Convierte un identificador en hexadecimal o decimal a número de nodo uint32.
 *
 * @param {string|number} input
 * @returns {number}
 */
export function parseNodeNum(input) {
    if (typeof input === 'number') return input >>> 0;
    const clean = String(input).trim().replace(/^!/, '');
    if (!clean) return 0;
    // Si contiene caracteres hexadecimales [a-fA-F] o empieza con 0x, interpretar como hex
    if (/[a-fA-F]/.test(clean) || clean.startsWith('0x')) {
        return parseInt(clean.replace(/^0x/, ''), 16) >>> 0;
    }
    // Si son solo dígitos y tiene más de 8 caracteres, probablemente es decimal uint32
    const dec = Number(clean);
    if (!Number.isNaN(dec) && dec > 100000000) {
        return dec >>> 0;
    }
    // Por defecto intentar hex si son 8 caracteres
    if (clean.length === 8) {
        return parseInt(clean, 16) >>> 0;
    }
    return (Number(clean) || 0) >>> 0;
}

/**
 * Componente reactivo Alpine.js para la consola de gestión de routers.
 */
export function meshAdminComponent() {
    return {
        // Estado de conexión local
        connectionStatus: 'disconnected', // 'disconnected' | 'connecting' | 'connected' | 'error'
        transportType: 'serial',          // 'serial' | 'bluetooth' | 'http'
        httpHost: '192.168.1.100',
        baudRate: 115200,
        errorMessage: '',

        // Datos del nodo local conectado
        localNode: {
            nodeNum: null,
            hexId: '',
            shortName: '',
            longName: '',
            batteryLevel: null,
            voltage: null,
            firmwareVersion: '',
        },

        // Router remoto seleccionado
        selectedTargetMode: 'preset',     // 'preset' | 'manual'
        selectedRouterNodeNum: '',        // Valor numérico del router
        selectedRouterHex: '',
        selectedRouterName: '',
        selectedRouterProvince: '',
        selectedRouterRole: '',
        selectedRouterStatus: '',
        manualNodeInput: '',

        // Pestaña activa
        activeTab: 'roles',               // 'roles' | 'favoritos' | 'bloqueados' | 'sondeo' | 'unicast' | 'mantenimiento'

        // Datos del formulario de roles
        selectedRole: 1,                  // Default CLIENT_MUTE (1)
        roleSending: false,

        // Datos del formulario de favoritos
        favoriteNodeInput: '',
        favoriteActionType: 'add',        // 'add' | 'remove'
        favoriteSending: false,
        sessionFavorites: [],
        searchFavoriteQuery: '',

        // Datos del formulario de bloqueados / ignorados
        blockedNodeInput: '',
        blockedActionType: 'add',         // 'add' | 'remove'
        blockedSending: false,
        searchBlockedQuery: '',

        // Almacén reactivo de favoritos y bloqueados por router { [routerHex]: Array<{ hex, num, shortName, longName, role }> }
        routerFavorites: {},
        routerBlocked: {},
        portalKnownNodes: [],

        // Datos de sondeo y unicast
        pollSending: false,
        unicastSending: false,
        unicastTargetInput: '',

        // Datos de mantenimiento / reinicio
        rebootSeconds: 10,
        rebootSending: false,

        // Consola de actividad y paquetes
        logs: [],
        logFilter: 'all',                 // 'all' | 'tx' | 'rx' | 'ack' | 'error'
        autoScroll: true,

        // Notificaciones internas no bloqueantes (en lugar de alert())
        notification: {
            show: false,
            type: 'info',                 // 'success' | 'error' | 'warning' | 'info'
            message: '',
            timeout: null,
        },

        // Seguridad y sesiones administrativas remotas (v2.5+)
        adminChannelIndex: 0,             // Canal administrativo (0 por defecto)
        adminSessions: {},                // Mapa de claves de sesión: { [nodeNum]: Uint8Array }
        _sessionKeyWaiters: {},           // Resolvers para ensureSessionKey
        _nodeInfoCount: 0,                // Contador de nodos recibidos de la radio local
        _pkNodeCount: 0,                  // Contador de nodos con clave pública de 32 bytes
        _nodeInfoSummaryTimer: null,      // Temporizador para resumen de NodeDB

        // Nodos descubiertos en el NodeDB del dispositivo local
        knownNodes: {},                   // { [num]: { num, hex, longName, shortName, isFavorite, hasPublicKey, role } }

        // Traceroute activo con temporizador de 30 segundos
        tracerouteActive: false,
        tracerouteCountdown: 0,
        tracerouteTimer: null,
        tracerouteTargetHex: '',
        tracerouteResult: '',
        tracerouteHops: [],

        // Referencias internas del SDK
        _device: null,
        _transport: null,

        /**
         * Inicialización del componente.
         */
        init() {
            if (typeof window !== 'undefined' && window.portalKnownNodes && Array.isArray(window.portalKnownNodes)) {
                this.portalKnownNodes = window.portalKnownNodes.slice();
                for (const r of this.portalKnownNodes) {
                    if (r.node_id) {
                        this.routerFavorites[r.node_id] = Array.isArray(r.favorite_nodes) ? r.favorite_nodes : [];
                        this.routerBlocked[r.node_id] = Array.isArray(r.blocked_nodes) ? r.blocked_nodes : [];
                    }
                }
            }
            this.loadRouterListsFromStorage();
            this.log('info', 'Consola de Gestión Remota de Routers inicializada.');
        },

        /**
         * Recupera listas de favoritos y bloqueados guardadas en localStorage.
         */
        loadRouterListsFromStorage() {
            try {
                if (typeof window === 'undefined' || !window.localStorage) return;
                for (let i = 0; i < localStorage.length; i++) {
                    const key = localStorage.key(i);
                    if (key && key.startsWith('mesh_favs_')) {
                        const rHex = key.replace('mesh_favs_', '');
                        const val = JSON.parse(localStorage.getItem(key) || '[]');
                        if (!this.routerFavorites[rHex] || this.routerFavorites[rHex].length === 0) {
                            this.routerFavorites[rHex] = val;
                        }
                    } else if (key && key.startsWith('mesh_blocked_')) {
                        const rHex = key.replace('mesh_blocked_', '');
                        const val = JSON.parse(localStorage.getItem(key) || '[]');
                        if (!this.routerBlocked[rHex] || this.routerBlocked[rHex].length === 0) {
                            this.routerBlocked[rHex] = val;
                        }
                    }
                }
            } catch (e) {
                console.warn('Error leyendo listas de localStorage:', e);
            }
        },

        /**
         * Persiste en localStorage las listas de favoritos y bloqueados del router.
         */
        saveRouterListsToStorage(rHex) {
            try {
                if (typeof window === 'undefined' || !window.localStorage || !rHex) return;
                if (this.routerFavorites[rHex]) {
                    localStorage.setItem('mesh_favs_' + rHex, JSON.stringify(this.routerFavorites[rHex]));
                }
                if (this.routerBlocked[rHex]) {
                    localStorage.setItem('mesh_blocked_' + rHex, JSON.stringify(this.routerBlocked[rHex]));
                }
            } catch (e) {
                console.warn('Error guardando listas en localStorage:', e);
            }
        },

        /**
         * Retorna el ID hexadecimal del router objetivo actualmente seleccionado.
         */
        getActiveRouterHex() {
            try {
                const targetNum = this.resolveTargetNodeNum();
                return numToHex(targetNum);
            } catch {
                return this.selectedRouterHex || (this.manualNodeInput ? this.manualNodeInput.trim() : '');
            }
        },

        /**
         * Retorna la lista de favoritos configurados para el router activo.
         */
        getActiveRouterFavorites() {
            const hex = this.getActiveRouterHex();
            if (!hex) return [];
            return this.routerFavorites[hex] || [];
        },

        /**
         * Retorna la lista de bloqueados configurados para el router activo.
         */
        getActiveRouterBlocked() {
            const hex = this.getActiveRouterHex();
            if (!hex) return [];
            return this.routerBlocked[hex] || [];
        },

        /**
         * Comprueba si un nodo dado ya consta en los favoritos del router activo.
         */
        isNodeFavoriteInActiveRouter(targetHex) {
            if (!targetHex) return false;
            const favs = this.getActiveRouterFavorites();
            return favs.some((f) => (f.hex || f) === targetHex);
        },

        /**
         * Comprueba si un nodo dado ya consta en los bloqueados del router activo.
         */
        isNodeBlockedInActiveRouter(targetHex) {
            if (!targetHex) return false;
            const blk = this.getActiveRouterBlocked();
            return blk.some((b) => (b.hex || b) === targetHex);
        },

        /**
         * Combina todos los nodos disponibles del catálogo del portal y de la radio local.
         */
        getAllAvailableNodes() {
            const map = new Map();
            // Nodos del catálogo del portal
            for (const n of this.portalKnownNodes) {
                if (n.node_id) {
                    map.set(n.node_id, {
                        hex: n.node_id,
                        num: Number(n.dec_id) || parseNodeNum(n.node_id),
                        shortName: n.short_name || '',
                        longName: n.long_name || '',
                        role: n.role || 'ROUTER',
                        province: n.province || '',
                    });
                }
            }
            // Nodos descubiertos en la sesión de radio local
            for (const [num, n] of Object.entries(this.knownNodes)) {
                if (n.hex && !map.has(n.hex)) {
                    map.set(n.hex, {
                        hex: n.hex,
                        num: Number(num),
                        shortName: n.shortName || '',
                        longName: n.longName || '',
                        role: n.role !== null ? this.getRoleName(n.role) : 'CLIENT',
                        province: '',
                    });
                }
            }
            return Array.from(map.values());
        },

        /**
         * Filtra candidatos para añadir a favoritos según el término de búsqueda.
         */
        getFilteredFavoriteCandidates() {
            const q = (this.searchFavoriteQuery || '').trim().toLowerCase();
            const activeHex = this.getActiveRouterHex();
            const all = this.getAllAvailableNodes().filter((n) => n.hex !== activeHex);

            if (!q) {
                return all.slice(0, 8);
            }

            const results = all.filter((n) => {
                const sName = (n.shortName || '').toLowerCase();
                const lName = (n.longName || '').toLowerCase();
                const hex = (n.hex || '').toLowerCase();
                const prov = (n.province || '').toLowerCase();
                return sName.includes(q) || lName.includes(q) || hex.includes(q) || prov.includes(q);
            });

            const isHexOrDec = /^!?([0-9a-fA-F]{8})$/.test(q) || (/^\d{8,10}$/.test(q) && Number(q) > 0);
            if (isHexOrDec) {
                const manualNum = parseNodeNum(q);
                const manualHex = numToHex(manualNum);
                const exists = results.some((r) => r.hex === manualHex);
                if (!exists && manualHex !== activeHex) {
                    results.unshift({
                        hex: manualHex,
                        num: manualNum,
                        shortName: manualHex.substring(1, 5),
                        longName: `Nodo Personalizado ${manualHex}`,
                        role: 'MANUAL',
                        province: '-',
                        isCustom: true,
                    });
                }
            }

            return results.slice(0, 15);
        },

        /**
         * Filtra candidatos para añadir a bloqueados según el término de búsqueda.
         */
        getFilteredBlockedCandidates() {
            const q = (this.searchBlockedQuery || '').trim().toLowerCase();
            const activeHex = this.getActiveRouterHex();
            const all = this.getAllAvailableNodes().filter((n) => n.hex !== activeHex);

            if (!q) {
                return all.slice(0, 8);
            }

            const results = all.filter((n) => {
                const sName = (n.shortName || '').toLowerCase();
                const lName = (n.longName || '').toLowerCase();
                const hex = (n.hex || '').toLowerCase();
                const prov = (n.province || '').toLowerCase();
                return sName.includes(q) || lName.includes(q) || hex.includes(q) || prov.includes(q);
            });

            const isHexOrDec = /^!?([0-9a-fA-F]{8})$/.test(q) || (/^\d{8,10}$/.test(q) && Number(q) > 0);
            if (isHexOrDec) {
                const manualNum = parseNodeNum(q);
                const manualHex = numToHex(manualNum);
                const exists = results.some((r) => r.hex === manualHex);
                if (!exists && manualHex !== activeHex) {
                    results.unshift({
                        hex: manualHex,
                        num: manualNum,
                        shortName: manualHex.substring(1, 5),
                        longName: `Nodo Personalizado ${manualHex}`,
                        role: 'MANUAL',
                        province: '-',
                        isCustom: true,
                    });
                }
            }

            return results.slice(0, 15);
        },

        /**
         * Conecta al nodo Meshtastic físico local del operador.
         */
        async connectLocalNode() {
            this.connectionStatus = 'connecting';
            this.errorMessage = '';
            this.log('info', `Iniciando conexión local por ${this.transportType}...`);

            try {
                let transport;
                if (this.transportType === 'serial') {
                    if (!('serial' in navigator)) {
                        throw new Error('Web Serial API no está soportada en este navegador. Usa Chrome, Edge u Opera en escritorio.');
                    }
                    transport = await TransportWebSerial.create(Number(this.baudRate) || 115200);
                } else if (this.transportType === 'bluetooth') {
                    if (!('bluetooth' in navigator)) {
                        throw new Error('Web Bluetooth API no está soportada en este navegador. Usa Chrome o Edge.');
                    }
                    transport = await TransportWebBluetooth.create();
                } else if (this.transportType === 'http') {
                    const host = this.httpHost.trim() || 'meshtastic.local';
                    transport = new TransportHTTP(host, false);
                } else {
                    throw new Error(`Tipo de transporte desconocido: ${this.transportType}`);
                }

                this._transport = transport;
                const device = new MeshDevice(transport);
                this._device = device;

                const statusNames = {
                    1: 'Reiniciando (DeviceRestarting)',
                    2: 'Desconectado (DeviceDisconnected)',
                    3: 'Conectando (DeviceConnecting)',
                    4: 'Reconectando (DeviceReconnecting)',
                    5: 'Conectado (DeviceConnected)',
                    6: 'Configurando (DeviceConfiguring)',
                    7: 'Configurado (DeviceConfigured)',
                };

                // Suscripción a eventos de estado del dispositivo
                device.events.onDeviceStatus.subscribe((status) => {
                    const statusText = statusNames[status] || status;
                    this.log('info', `Estado del dispositivo local: ${statusText}`);

                    // 5 = DeviceConnected, 6 = DeviceConfiguring, 7 = DeviceConfigured
                    if (status === 5 || status === 6 || status === 7 || status === 'connected' || status === 'configured' || status === 'DeviceConnected' || status === 'DeviceConfigured') {
                        if (this.connectionStatus !== 'connected') {
                            this.connectionStatus = 'connected';
                            this.log('info', '¡Conexión establecida con el nodo local! Listo para transmitir.');
                        }
                    } else if (status === 2 || status === 'disconnected' || status === 'DeviceDisconnected') {
                        if (this.connectionStatus === 'connected') {
                            this.log('warn', 'Dispositivo local desconectado.');
                            this.connectionStatus = 'disconnected';
                        }
                    }
                });

                // Suscripción a información del nodo local
                device.events.onMyNodeInfo.subscribe((info) => {
                    if (info) {
                        const num = info.myNodeNum >>> 0;
                        this.localNode.nodeNum = num;
                        this.localNode.hexId = numToHex(num);
                        this.log('info', `Nodo local identificado: ${this.localNode.hexId}`);

                        // Si recibimos MyNodeInfo, el enlace serie está indudablemente activo y comunicando
                        if (this.connectionStatus !== 'connected') {
                            this.connectionStatus = 'connected';
                            this.log('info', '¡Conexión establecida con el nodo local! Listo para transmitir.');
                        }
                    }
                });

                // Suscripción a información de nodos almacenados en el NodeDB local
                device.events.onNodeInfoPacket.subscribe((nodeInfo) => {
                    if (!nodeInfo || !nodeInfo.num) return;
                    const nNum = nodeInfo.num >>> 0;
                    const nHex = numToHex(nNum);
                    const hasPk = Boolean(nodeInfo.user?.publicKey && nodeInfo.user.publicKey.length === 32);
                    const isNew = !this.knownNodes[nNum];

                    this.knownNodes[nNum] = {
                        num: nNum,
                        hex: nHex,
                        longName: nodeInfo.user?.longName || '',
                        shortName: nodeInfo.user?.shortName || '',
                        isFavorite: Boolean(nodeInfo.isFavorite),
                        hasPublicKey: hasPk,
                        role: nodeInfo.user?.role ?? null,
                    };

                    if (nodeInfo.isFavorite && !this.sessionFavorites.includes(nHex)) {
                        this.sessionFavorites.push(nHex);
                    }

                    if (isNew) {
                        this._nodeInfoCount = (this._nodeInfoCount || 0) + 1;
                        if (hasPk) this._pkNodeCount = (this._pkNodeCount || 0) + 1;
                        clearTimeout(this._nodeInfoSummaryTimer);
                        this._nodeInfoSummaryTimer = setTimeout(() => {
                            this.log('info', `📋 Memoria de la radio local: ${this._nodeInfoCount} nodos cargados (${this._pkNodeCount || 0} con clave pública verificada).`);
                            this.checkRouterStatusInRadio();
                        }, 1200);
                    }
                });

                // Suscripción a paquetes entrantes de la malla
                device.events.onMeshPacket.subscribe((packet) => {
                    this.handleIncomingMeshPacket(packet);
                });

                // Marcar como conectado de inmediato tras inicializar el transporte serie
                this.connectionStatus = 'connected';
                this.log('info', '¡Puerto serie abierto con éxito! Listo para transmitir.');

                // Disparar configuración inicial en segundo plano sin bloquear el hilo principal (evita el timeout de 60s)
                device.configure().catch((cfgErr) => {
                    console.warn('Configuración inicial en segundo plano completada:', cfgErr);
                });
            } catch (err) {
                console.error('Error de conexión Meshtastic:', err);
                this.connectionStatus = 'error';
                this.errorMessage = err.message || 'Error desconocido al conectar.';
                this.log('error', `Fallo de conexión: ${this.errorMessage}`);
            }
        },

        /**
         * Desconecta del nodo físico local.
         */
        async disconnectLocalNode() {
            this.log('info', 'Cerrando conexión con el nodo local...');
            if (this.tracerouteTimer) {
                clearInterval(this.tracerouteTimer);
                this.tracerouteTimer = null;
                this.tracerouteActive = false;
            }
            try {
                if (this._device) {
                    await this._device.disconnect();
                } else if (this._transport) {
                    await this._transport.disconnect();
                }
            } catch (err) {
                console.warn('Error durante desconexión:', err);
            } finally {
                this._device = null;
                this._transport = null;
                this.connectionStatus = 'disconnected';
                this.localNode.nodeNum = null;
                this.localNode.hexId = '';
                this.log('info', 'Dispositivo local desconectado con éxito.');
            }
        },

        /**
         * Procesa un paquete entrante de la malla recibido por el nodo local.
         */
        handleIncomingMeshPacket(packet) {
            const fromNum = packet.from >>> 0;
            const fromHex = numToHex(fromNum);
            const toNum = packet.to >>> 0;
            const toHex = toNum === Constants.broadcastNum ? '^all' : numToHex(toNum);
            const snr = packet.rxSnr !== undefined ? `SNR: ${packet.rxSnr.toFixed(1)}dB` : '';
            const rssi = packet.rxRssi !== undefined ? `RSSI: ${packet.rxRssi}dBm` : '';
            const hops = packet.hopLimit !== undefined ? `Hops: ${packet.hopLimit}` : '';
            const meta = [snr, rssi, hops].filter(Boolean).join(' | ');

            const payloadCase = packet.payloadVariant?.case;

            if (payloadCase === 'decoded') {
                const decoded = packet.payloadVariant.value;
                const port = decoded.portnum;
                const rawBytes = decoded.payload;

                // Paquete de enrutamiento / ACK / NAK
                if (port === PortNum.ROUTING_APP) {
                    try {
                        const routing = fromBinary(RoutingSchema, rawBytes);
                        if (routing.variant?.case === 'errorReason') {
                            const errCode = routing.variant.value;
                            const reqId = decoded.requestId || packet.id;
                            if (errCode === Routing_Error.NONE) {
                                this.log('ack', `✅ Confirmación ACK de ${fromHex} hacia ${toHex} (ReqID: ${reqId}) ${meta ? `[${meta}]` : ''}`);
                            } else if (errCode === 39 || errCode === Routing_Error.PKI_UNKNOWN_PUBKEY) {
                                this.log('error', `❌ El nodo ${toHex} no responde por radio LoRa o no está en cobertura de tu antena (ReqID: ${reqId})`);
                            } else {
                                const desc = ROUTING_ERROR_DESCRIPTIONS[errCode] || `Código ${errCode}`;
                                this.log('error', `❌ Rechazo de enrutamiento [${desc}] de ${fromHex} (ReqID: ${reqId}) ${meta ? `[${meta}]` : ''}`);
                            }
                        } else if (routing.variant?.case === 'routeReply') {
                            this.log('rx', `Respuesta de ruta (routeReply) recibida de ${fromHex} hacia ${toHex}`);
                        } else if (routing.variant?.case === 'routeRequest') {
                            this.log('rx', `Petición de ruta (routeRequest) de ${fromHex}`);
                        } else {
                            this.log('rx', `Paquete de enrutamiento de ${fromHex} hacia ${toHex}`);
                        }
                    } catch {
                        this.log('rx', `Paquete ROUTING recibido de ${fromHex} hacia ${toHex} (${rawBytes.length} bytes)`);
                    }
                    return;
                }

                // Respuesta administrativa
                if (port === PortNum.ADMIN_APP) {
                    try {
                        const adminMsg = fromBinary(AdminMessageSchema, rawBytes);
                        const variant = adminMsg.payloadVariant?.case || 'desconocido';

                        // Si el mensaje incluye una session_passkey, guardarla en sesión para este router
                        if (adminMsg.sessionPasskey && adminMsg.sessionPasskey.length > 0) {
                            this.adminSessions[fromNum] = adminMsg.sessionPasskey;
                            this.log('info', `🔑 Clave de sesión administrativa (SessionKey) guardada para ${fromHex}`);
                            this.setNotification('success', `Clave de sesión administrativa establecida con éxito para ${fromHex}.`);
                            if (this._sessionKeyWaiters[fromNum]) {
                                this._sessionKeyWaiters[fromNum](adminMsg.sessionPasskey);
                                delete this._sessionKeyWaiters[fromNum];
                            }
                        }

                        this.log('rx', `Respuesta ADMIN de ${fromHex}: caso '${variant}' [ID: ${packet.id}]`);
                    } catch {
                        this.log('rx', `Paquete ADMIN recibido de ${fromHex} (${rawBytes.length} bytes)`);
                    }
                    return;
                }

                // NodeInfo
                if (port === PortNum.NODEINFO_APP) {
                    try {
                        const user = fromBinary(UserSchema, rawBytes);
                        const hasPk = Boolean(user.publicKey && user.publicKey.length === 32);
                        this.knownNodes[fromNum] = {
                            num: fromNum,
                            hex: fromHex,
                            longName: user.longName || '',
                            shortName: user.shortName || '',
                            isFavorite: false,
                            hasPublicKey: hasPk,
                            role: user.role ?? null,
                        };
                        const pkNotice = hasPk ? ' [PKI disponible]' : '';
                        this.log('rx', `NodeInfo de ${fromHex}: "${user.longName}" (${user.shortName}) [HW: ${user.hwModel}]${pkNotice}`);
                    } catch {
                        this.log('rx', `NodeInfo recibido de ${fromHex}`);
                    }
                    return;
                }

                // Posición GPS
                if (port === PortNum.POSITION_APP) {
                    try {
                        const pos = fromBinary(PositionSchema, rawBytes);
                        const lat = (pos.latitudeI / 1e7).toFixed(5);
                        const lon = (pos.longitudeI / 1e7).toFixed(5);
                        this.log('rx', `Posición de ${fromHex}: Lat ${lat}, Lon ${lon} (Alt: ${pos.altitude || 0}m)`);
                    } catch {
                        this.log('rx', `Posición recibida de ${fromHex}`);
                    }
                    return;
                }

                // Telemetría
                if (port === PortNum.TELEMETRY_APP) {
                    try {
                        const telem = fromBinary(TelemetrySchema, rawBytes);
                        const dev = telem.deviceMetrics;
                        if (dev) {
                            const bat = dev.batteryLevel !== undefined ? `Batería: ${dev.batteryLevel}%` : '';
                            const volt = dev.voltage !== undefined ? `Voltaje: ${dev.voltage.toFixed(2)}V` : '';
                            const chUtil = dev.channelUtilization !== undefined ? `ChUtil: ${dev.channelUtilization.toFixed(1)}%` : '';
                            this.log('rx', `Telemetría de ${fromHex}: ${[bat, volt, chUtil].filter(Boolean).join(' | ')}`);
                            return;
                        }
                    } catch {
                        // Continuar si no decodifica
                    }
                    this.log('rx', `Telemetría recibida de ${fromHex}`);
                    return;
                }

                // Traceroute
                if (port === PortNum.TRACEROUTE_APP) {
                    try {
                        const route = fromBinary(RouteDiscoverySchema, rawBytes);
                        const hops = (route.route || []).map((h) => numToHex(h));
                        const hopsFormatted = hops.length > 0 ? hops.join(' ➔ ') : 'Directo (0 saltos intermedios)';
                        const fullRouteStr = `${this.localNode.hexId} ➔ ${hops.length > 0 ? hops.join(' ➔ ') + ' ➔ ' : ''}${fromHex}`;

                        this.log('rx', `🎯 Traceroute respuesta de ${fromHex}: ${hopsFormatted}`);
                        this.tracerouteHops = hops;
                        this.tracerouteResult = fullRouteStr;

                        if (this.tracerouteActive) {
                            this.tracerouteActive = false;
                            if (this.tracerouteTimer) {
                                clearInterval(this.tracerouteTimer);
                                this.tracerouteTimer = null;
                            }
                            this.setNotification('success', `Traceroute completado: ${fullRouteStr}`);
                        }
                    } catch {
                        this.log('rx', `Traceroute recibido de ${fromHex}`);
                    }
                    return;
                }

                this.log('rx', `Paquete de ${fromHex} en puerto ${port} (${rawBytes.length} bytes) ${meta ? `[${meta}]` : ''}`);
            } else {
                this.log('rx', `Paquete cifrado de ${fromHex} hacia ${toHex} ${meta ? `[${meta}]` : ''}`);
            }
        },

        /**
         * Obtiene el número uint32 del router objetivo actual.
         */
        resolveTargetNodeNum() {
            // Prioridad al input manual si el usuario ha escrito o pegado un ID
            const manual = (this.manualNodeInput || '').trim();
            if (manual) {
                const parsed = parseNodeNum(manual);
                if (parsed > 0) return parsed;
            }

            const fromSelect = parseNodeNum(this.selectedRouterNodeNum);
            if (fromSelect > 0) {
                return fromSelect;
            }

            throw new Error('Selecciona un router de Andalucía de la lista o introduce un Node ID manual válido (!XXXXXXXX o decimal).');
        },

        /**
         * Actualiza los datos del router remoto al cambiar el selector de la lista.
         */
        onRouterSelectChange(event) {
            const val = event.target ? event.target.value : event;
            if (!val) {
                if (!this.manualNodeInput.trim()) {
                    this.selectedRouterHex = '';
                    this.selectedRouterNodeNum = '';
                    this.selectedRouterName = '';
                }
                return;
            }

            if (val === 'manual') {
                this.selectedTargetMode = 'manual';
                return;
            }

            this.selectedTargetMode = 'preset';
            this.selectedRouterNodeNum = val;
            const opt = event.target && event.target.selectedOptions ? event.target.selectedOptions[0] : null;
            if (opt) {
                this.selectedRouterHex = opt.getAttribute('data-hex') || numToHex(Number(val));
                this.selectedRouterName = opt.getAttribute('data-name') || '';
                this.selectedRouterProvince = opt.getAttribute('data-province') || '';
                this.selectedRouterRole = opt.getAttribute('data-role') || '';
                this.selectedRouterStatus = opt.getAttribute('data-status') || '';
            } else {
                this.selectedRouterHex = numToHex(Number(val));
            }

            // Sincronizar el input manual y el destino unicast
            this.manualNodeInput = this.selectedRouterHex;
            this.unicastTargetInput = this.selectedRouterHex;
            this.log('info', `Router seleccionado: ${this.selectedRouterName || this.selectedRouterHex}`);
            this.checkRouterStatusInRadio();
        },

        /**
         * Maneja cambios cuando el operador escribe o pega directamente un ID manual.
         */
        onManualInputChange() {
            const clean = (this.manualNodeInput || '').trim();
            if (!clean) {
                if (!this.selectedRouterNodeNum) {
                    this.selectedRouterHex = '';
                    this.selectedRouterName = '';
                }
                return;
            }
            const num = parseNodeNum(clean);
            if (num > 0) {
                this.selectedTargetMode = 'manual';
                this.selectedRouterNodeNum = String(num);
                this.selectedRouterHex = numToHex(num);
                this.selectedRouterName = `Nodo Manual ${this.selectedRouterHex}`;
                this.selectedRouterProvince = '';
                this.selectedRouterRole = '';
                this.selectedRouterStatus = '';
                this.unicastTargetInput = this.selectedRouterHex;
                this.checkRouterStatusInRadio();
            }
        },

        /**
         * Comprueba si el router objetivo consta en la memoria de la radio local.
         */
        checkRouterStatusInRadio() {
            try {
                const num = this.resolveTargetNodeNum();
                const hex = numToHex(num);
                const node = this.knownNodes[num];
                if (node) {
                    if (node.hasPublicKey) {
                        this.log('info', `🔒 Router ${hex} ("${node.longName || node.shortName || hex}") detectado con enlace seguro verificado.`);
                    } else {
                        this.log('info', `ℹ️ Router ${hex} ("${node.longName || node.shortName || hex}") detectado en la memoria local.`);
                    }
                } else if (hex && hex !== '!00000000') {
                    this.log('info', `📡 Router ${hex} seleccionado para transmisión directa por radio.`);
                }
            } catch {
                // Sin selección activa
            }
        },

        /**
         * Retorna el nombre legible del rol a partir de su número enum.
         *
         * @param {number|string} roleNum
         * @returns {string}
         */
        getRoleName(roleNum) {
            const num = Number(roleNum);
            return Config_DeviceConfig_Role[num] || `ROL_${num}`;
        },

        /**
         * Muestra una notificación visual en la parte superior sin bloquear el navegador.
         *
         * @param {'success'|'error'|'warning'|'info'} type
         * @param {string} message
         * @param {number} [durationMs=8000]
         */
        setNotification(type, message, durationMs = 8000) {
            if (this.notification.timeout) {
                clearTimeout(this.notification.timeout);
            }
            this.notification.type = type;
            this.notification.message = message;
            this.notification.show = true;

            if (durationMs > 0) {
                this.notification.timeout = setTimeout(() => {
                    this.notification.show = false;
                }, durationMs);
            }
        },

        /**
         * Cierra la notificación activa.
         */
        dismissNotification() {
            this.notification.show = false;
            if (this.notification.timeout) {
                clearTimeout(this.notification.timeout);
            }
        },

        /**
         * Comprueba si disponemos de una clave de sesión administrativa para el objetivo actual.
         *
         * @returns {boolean}
         */
        hasTargetSessionKey() {
            try {
                const targetNum = this.resolveTargetNodeNum();
                return Boolean(this.adminSessions[targetNum]);
            } catch {
                return false;
            }
        },

        /**
         * Comprueba si la radio física local tiene en su NodeDB la clave pública del router objetivo.
         *
         * @returns {boolean}
         */
        targetHasPublicKeyInLocalRadio() {
            try {
                const targetNum = this.resolveTargetNodeNum();
                return Boolean(this.knownNodes[targetNum]?.hasPublicKey);
            } catch {
                return false;
            }
        },

        /**
         * Asegura la disponibilidad de un pase de sesión administrativo antes de operaciones críticas.
         *
         * @param {number} targetNum
         * @returns {Promise<Uint8Array|null>}
         */
        async ensureSessionKey(targetNum) {
            // El nodo local no necesita sessionKey
            if (targetNum === this.localNode.nodeNum || targetNum === 0) {
                return null;
            }

            if (this.adminSessions[targetNum] && this.adminSessions[targetNum].length > 0) {
                return this.adminSessions[targetNum];
            }

            // Si la radio física local aún no tiene la clave pública de este router, no intentar handshake PKI
            if (!this.targetHasPublicKeyInLocalRadio()) {
                return null;
            }

            const targetHex = numToHex(targetNum);
            this.log('info', `🔑 Solicitando pase de sesión administrativa (SessionKey) a ${targetHex}...`);

            return new Promise(async (resolve) => {
                const timer = setTimeout(() => {
                    delete this._sessionKeyWaiters[targetNum];
                    resolve(null); // Continuar sin bloquear la orden
                }, 6000);

                this._sessionKeyWaiters[targetNum] = (key) => {
                    clearTimeout(timer);
                    resolve(key);
                };

                try {
                    const adminMsg = create(AdminMessageSchema, {
                        payloadVariant: {
                            case: 'getConfigRequest',
                            value: 8, // SESSIONKEY_CONFIG
                        },
                    });

                    await this.sendMeshPacketCustom({
                        payloadBytes: toBinary(AdminMessageSchema, adminMsg),
                        portNum: PortNum.ADMIN_APP,
                        destinationNum: targetNum,
                        channel: this.adminChannelIndex || 0,
                        wantAck: true,
                        wantResponse: true,
                        pkiEncrypted: true,
                        timeoutMs: 6000,
                    });
                } catch {
                    clearTimeout(timer);
                    delete this._sessionKeyWaiters[targetNum];
                    resolve(null);
                }
            });
        },

        /**
         * Envía un paquete a través de la radio local envolviendo en ToRadio y gestionando el rechazo.
         *
         * @param {Object} options
         * @param {Uint8Array} options.payloadBytes
         * @param {number} options.portNum
         * @param {number} options.destinationNum
         * @param {number} [options.channel=0]
         * @param {boolean} [options.wantAck=true]
         * @param {boolean} [options.wantResponse=true]
         * @param {boolean} [options.pkiEncrypted=false]
         * @param {number} [options.timeoutMs=15000]
         * @returns {Promise<number>} ID del paquete enviado
         */
        async sendMeshPacketCustom({
            payloadBytes,
            portNum,
            destinationNum,
            channel = 0,
            wantAck = true,
            wantResponse = true,
            pkiEncrypted = false,
            timeoutMs = 15000,
        }) {
            if (!this._device || this.connectionStatus !== 'connected') {
                throw new Error('Debes conectar primero tu nodo Meshtastic local.');
            }

            const isBroadcast = destinationNum === Constants.broadcastNum;
            const randId = this._device.generateRandId();
            const fromNum = this.localNode.nodeNum || this._device.myNodeInfo?.myNodeNum || 0;

            const meshPacket = create(MeshPacketSchema, {
                id: randId,
                from: fromNum,
                to: destinationNum,
                channel: channel,
                wantAck: isBroadcast ? false : Boolean(wantAck),
                pkiEncrypted: Boolean(pkiEncrypted),
                payloadVariant: {
                    case: 'decoded',
                    value: {
                        payload: payloadBytes,
                        portnum: portNum,
                        wantResponse: isBroadcast ? false : Boolean(wantResponse),
                        dest: 0,
                        requestId: 0,
                        source: 0,
                        emoji: 0,
                        replyId: 0,
                    },
                },
            });

            const toRadio = create(ToRadioSchema, {
                payloadVariant: {
                    case: 'packet',
                    value: meshPacket,
                },
            });

            const binaryToRadio = toBinary(ToRadioSchema, toRadio);

            if (isBroadcast || !wantAck) {
                // Para emisiones broadcast o sin ACK, transmitimos directamente por la cola serie
                // y confirmamos inmediatamente tras el envío al dispositivo físico, evitando el falso TIMEOUT de 60s
                this._device.queue.push({
                    id: randId,
                    data: binaryToRadio,
                });
                await this._device.queue.processQueue(this._device.transport.toDevice);
                this._device.queue.processAck(randId);
                return randId;
            }

            // Para unicast con ACK, aplicamos un timeout controlado (por defecto 15s) evitando bloqueos de 60s
            try {
                const sendPromise = this._device.sendRaw(binaryToRadio, randId);
                const effectiveTimeout = timeoutMs || 15000;

                const timeoutPromise = new Promise((_, reject) => {
                    setTimeout(() => {
                        if (this._device && this._device.queue) {
                            this._device.queue.remove(randId);
                        }
                        reject({
                            id: randId,
                            error: Routing_Error.TIMEOUT,
                        });
                    }, effectiveTimeout);
                });

                await Promise.race([sendPromise, timeoutPromise]);
                return randId;
            } catch (rawErr) {
                throw new Error(formatMeshtasticError(rawErr));
            }
        },

        /**
         * Envía un mensaje administrativo a un router remoto gestionando sesión y cifrado automáticamente.
         *
         * @param {number} targetNum
         * @param {any} adminMsg
         * @param {string} operationLabel
         * @returns {Promise<number>}
         */
        async sendAdminMessageToTarget(targetNum, adminMsg, operationLabel) {
            const targetHex = numToHex(targetNum);
            const isLocal = (targetNum === this.localNode.nodeNum || targetNum === 0);

            // Si es un router remoto, asegurar SessionKey si dispone de clave pública
            if (!isLocal) {
                if (this.targetHasPublicKeyInLocalRadio() && !this.adminSessions[targetNum]) {
                    await this.ensureSessionKey(targetNum);
                }
                if (this.adminSessions[targetNum]) {
                    adminMsg.sessionPasskey = this.adminSessions[targetNum];
                }
            }

            const payload = toBinary(AdminMessageSchema, adminMsg);
            const hasPkInRadio = this.targetHasPublicKeyInLocalRadio();
            const effectivePki = Boolean(!isLocal && hasPkInRadio);

            this.log('tx', `Preparando orden '${operationLabel}' hacia router ${targetHex}...`);

            try {
                const pktId = await this.sendMeshPacketCustom({
                    payloadBytes: payload,
                    portNum: PortNum.ADMIN_APP,
                    destinationNum: targetNum,
                    channel: this.adminChannelIndex || 0,
                    wantAck: true,
                    wantResponse: true,
                    pkiEncrypted: effectivePki,
                });

                const okMsg = `Orden '${operationLabel}' transmitida con éxito a ${targetHex} (ID: ${pktId}). Esperando confirmación de la malla...`;
                this.setNotification('success', okMsg);
                this.log('tx', `🚀 ${okMsg}`);
                return pktId;
            } catch (err) {
                const errMsg = formatMeshtasticError(err, targetHex);
                this.setNotification('error', `No se pudo aplicar '${operationLabel}': ${errMsg}`);
                this.log('error', `❌ Error al enviar '${operationLabel}' hacia ${targetHex}: ${errMsg}`);
                throw err;
            }
        },

        /**
         * Solicita la clave de sesión administrativa (SessionKey) al router destino.
         */
        async requestAdminSessionKey() {
            if (this.connectionStatus !== 'connected' || !this._device) {
                this.setNotification('warning', 'Debes conectar primero tu nodo Meshtastic local.');
                return;
            }

            try {
                const targetNum = this.resolveTargetNodeNum();
                const targetHex = numToHex(targetNum);

                this.log('tx', `🔑 Solicitando clave de sesión (SESSIONKEY_CONFIG) al router ${targetHex}...`);

                const adminMsg = create(AdminMessageSchema, {
                    payloadVariant: {
                        case: 'getConfigRequest',
                        value: 8, // SESSIONKEY_CONFIG
                    },
                });

                await this.sendAdminMessageToTarget(targetNum, adminMsg, 'Solicitud de SessionKey');
                this.setNotification('info', `Petición de SessionKey enviada a ${targetHex}. La clave se guardará automáticamente al recibir respuesta.`);
            } catch (err) {
                console.error('Error solicitando SessionKey:', err);
            }
        },

        /**
         * Transmite nuestra identidad User al router remoto para que conozca nuestra clave pública.
         */
        async exchangeNodeInfoWithTarget() {
            if (this.connectionStatus !== 'connected' || !this._device) {
                this.setNotification('warning', 'Debes conectar primero tu nodo Meshtastic local.');
                return;
            }

            try {
                const targetNum = this.resolveTargetNodeNum();
                const targetHex = numToHex(targetNum);

                this.log('tx', `📡 Iniciando intercambio de identidad (NodeInfo / Claves) con ${targetHex}...`);

                const userMsg = create(UserSchema, {
                    id: this.localNode.hexId,
                    longName: this.localNode.longName || 'Operador Andalucía Mesh',
                    shortName: this.localNode.shortName || 'OP',
                    hwModel: 0,
                });

                const payload = toBinary(UserSchema, userMsg);

                const pktId = await this.sendMeshPacketCustom({
                    payloadBytes: payload,
                    portNum: PortNum.NODEINFO_APP,
                    destinationNum: targetNum,
                    channel: 0,
                    wantAck: true,
                    wantResponse: true,
                    pkiEncrypted: false,
                });

                const okMsg = `Identidad transmitida a ${targetHex} (ID: ${pktId}). Esperando respuesta con clave pública...`;
                this.setNotification('success', okMsg);
                this.log('tx', `📡 ${okMsg}`);
            } catch (err) {
                const errMsg = formatMeshtasticError(err);
                this.setNotification('error', `Error en intercambio con router: ${errMsg}`);
                this.log('error', `❌ Error en intercambio con ${numToHex(this.resolveTargetNodeNum())}: ${errMsg}`);
            }
        },

        /**
         * Envía comando remoto de cambio de rol al router seleccionado.
         */
        async applyRemoteRole() {
            if (this.connectionStatus !== 'connected' || !this._device) {
                this.setNotification('warning', 'Debes conectar primero tu nodo Meshtastic local.');
                return;
            }

            this.roleSending = true;
            try {
                const targetNum = this.resolveTargetNodeNum();
                const targetHex = numToHex(targetNum);
                const roleEnum = Number(this.selectedRole);
                const roleName = this.getRoleName(roleEnum);

                const adminMsg = create(AdminMessageSchema, {
                    payloadVariant: {
                        case: 'setConfig',
                        value: create(ConfigSchema, {
                            payloadVariant: {
                                case: 'device',
                                value: create(Config_DeviceConfigSchema, {
                                    role: roleEnum,
                                }),
                            },
                        }),
                    },
                });

                await this.sendAdminMessageToTarget(targetNum, adminMsg, `Cambio de Rol a ${roleName}`);
            } catch (err) {
                console.error('Error enviando cambio de rol:', err);
            } finally {
                this.roleSending = false;
            }
        },

        /**
         * Gestiona favoritos (añadir o eliminar) en el router remoto.
         *
         * @param {string|number|object|null} targetNode - ID hex, num o candidato
         * @param {'add'|'remove'} action
         */
        async applyRemoteFavorite(targetNode = null, action = 'add') {
            if (this.connectionStatus !== 'connected' || !this._device) {
                this.setNotification('warning', 'Debes conectar primero tu nodo Meshtastic local.');
                return;
            }

            let favNum;
            let nodeData = null;

            if (typeof targetNode === 'object' && targetNode !== null) {
                favNum = targetNode.num || parseNodeNum(targetNode.hex);
                nodeData = targetNode;
            } else if (typeof targetNode === 'string' || typeof targetNode === 'number') {
                favNum = parseNodeNum(targetNode);
            } else {
                favNum = parseNodeNum(this.favoriteNodeInput);
            }

            if (!favNum) {
                this.setNotification('warning', 'Introduce o selecciona un Node ID válido para favoritos (ej. !5f3a3a29).');
                return;
            }

            const favHex = numToHex(favNum);
            const isAdd = action === 'add';
            const activeRouterHex = this.getActiveRouterHex();

            if (!nodeData) {
                nodeData = this.getAllAvailableNodes().find((n) => n.hex === favHex) || {
                    hex: favHex,
                    num: favNum,
                    shortName: favHex.substring(1, 5),
                    longName: favHex,
                    role: 'ROUTER',
                };
            }

            // Comprobación previa para evitar duplicados innecesarios
            if (isAdd && this.isNodeFavoriteInActiveRouter(favHex)) {
                this.setNotification('info', `El nodo ${favHex} ya consta en la lista de favoritos de este router.`);
                return;
            }

            this.favoriteSending = true;
            try {
                const targetNum = this.resolveTargetNodeNum();
                const targetHex = numToHex(targetNum);

                const adminMsg = create(AdminMessageSchema, {
                    payloadVariant: isAdd
                        ? { case: 'setFavoriteNode', value: favNum }
                        : { case: 'removeFavoriteNode', value: favNum },
                });

                const opLabel = `${isAdd ? 'Añadir a' : 'Quitar de'} favoritos (${favHex})`;
                await this.sendAdminMessageToTarget(targetNum, adminMsg, opLabel);

                // Actualizar lista local de favoritos del router
                if (!this.routerFavorites[activeRouterHex]) {
                    this.routerFavorites[activeRouterHex] = [];
                }

                if (isAdd) {
                    if (!this.routerFavorites[activeRouterHex].some((f) => (f.hex || f) === favHex)) {
                        this.routerFavorites[activeRouterHex].push({
                            hex: favHex,
                            num: favNum,
                            shortName: nodeData.shortName || favHex.substring(1, 5),
                            longName: nodeData.longName || favHex,
                            role: nodeData.role || 'ROUTER',
                            added_at: new Date().toISOString(),
                        });
                    }
                    // Si estaba bloqueado, retirarlo automáticamente de bloqueados
                    if (this.routerBlocked[activeRouterHex]) {
                        this.routerBlocked[activeRouterHex] = this.routerBlocked[activeRouterHex].filter((b) => (b.hex || b) !== favHex);
                    }
                } else {
                    this.routerFavorites[activeRouterHex] = this.routerFavorites[activeRouterHex].filter((f) => (f.hex || f) !== favHex);
                }

                this.saveRouterListsToStorage(activeRouterHex);

                // Sincronizar en base de datos si Livewire está disponible
                if (window.Livewire && this.$wire && typeof this.$wire.updateRouterFavoriteNode === 'function') {
                    try {
                        this.$wire.updateRouterFavoriteNode(activeRouterHex, favHex, isAdd, nodeData);
                    } catch (lwErr) {
                        console.warn('Error sincronizando con Livewire:', lwErr);
                    }
                }

                this.setNotification('success', `Orden '${opLabel}' enviada con éxito al router ${targetHex}.`);
                this.favoriteNodeInput = '';
            } catch (err) {
                console.error('Error gestionando favorito:', err);
            } finally {
                this.favoriteSending = false;
            }
        },

        /**
         * Gestiona nodos bloqueados / ignorados en el router remoto.
         *
         * @param {string|number|object|null} targetNode - ID hex, num o candidato
         * @param {'add'|'remove'} action
         */
        async applyRemoteBlocked(targetNode = null, action = 'add') {
            if (this.connectionStatus !== 'connected' || !this._device) {
                this.setNotification('warning', 'Debes conectar primero tu nodo Meshtastic local.');
                return;
            }

            let ignNum;
            let nodeData = null;

            if (typeof targetNode === 'object' && targetNode !== null) {
                ignNum = targetNode.num || parseNodeNum(targetNode.hex);
                nodeData = targetNode;
            } else if (typeof targetNode === 'string' || typeof targetNode === 'number') {
                ignNum = parseNodeNum(targetNode);
            } else {
                ignNum = parseNodeNum(this.blockedNodeInput);
            }

            if (!ignNum) {
                this.setNotification('warning', 'Introduce o selecciona un Node ID válido para bloquear (ej. !5f3a3a29).');
                return;
            }

            const ignHex = numToHex(ignNum);
            const isAdd = action === 'add';
            const activeRouterHex = this.getActiveRouterHex();

            if (!nodeData) {
                nodeData = this.getAllAvailableNodes().find((n) => n.hex === ignHex) || {
                    hex: ignHex,
                    num: ignNum,
                    shortName: ignHex.substring(1, 5),
                    longName: ignHex,
                    role: 'NODE',
                };
            }

            // Comprobación previa para evitar duplicados innecesarios
            if (isAdd && this.isNodeBlockedInActiveRouter(ignHex)) {
                this.setNotification('info', `El nodo ${ignHex} ya consta en la lista de bloqueados de este router.`);
                return;
            }

            this.blockedSending = true;
            try {
                const targetNum = this.resolveTargetNodeNum();
                const targetHex = numToHex(targetNum);

                const adminMsg = create(AdminMessageSchema, {
                    payloadVariant: isAdd
                        ? { case: 'setIgnoredNode', value: ignNum }
                        : { case: 'removeIgnoredNode', value: ignNum },
                });

                const opLabel = `${isAdd ? 'Bloquear' : 'Desbloquear'} nodo (${ignHex})`;
                await this.sendAdminMessageToTarget(targetNum, adminMsg, opLabel);

                // Actualizar lista local de bloqueados del router
                if (!this.routerBlocked[activeRouterHex]) {
                    this.routerBlocked[activeRouterHex] = [];
                }

                if (isAdd) {
                    if (!this.routerBlocked[activeRouterHex].some((b) => (b.hex || b) === ignHex)) {
                        this.routerBlocked[activeRouterHex].push({
                            hex: ignHex,
                            num: ignNum,
                            shortName: nodeData.shortName || ignHex.substring(1, 5),
                            longName: nodeData.longName || ignHex,
                            role: nodeData.role || 'NODE',
                            added_at: new Date().toISOString(),
                        });
                    }
                    // Si estaba en favoritos, retirarlo automáticamente de favoritos
                    if (this.routerFavorites[activeRouterHex]) {
                        this.routerFavorites[activeRouterHex] = this.routerFavorites[activeRouterHex].filter((f) => (f.hex || f) !== ignHex);
                    }
                } else {
                    this.routerBlocked[activeRouterHex] = this.routerBlocked[activeRouterHex].filter((b) => (b.hex || b) !== ignHex);
                }

                this.saveRouterListsToStorage(activeRouterHex);

                // Sincronizar en base de datos si Livewire está disponible
                if (window.Livewire && this.$wire && typeof this.$wire.updateRouterBlockedNode === 'function') {
                    try {
                        this.$wire.updateRouterBlockedNode(activeRouterHex, ignHex, isAdd, nodeData);
                    } catch (lwErr) {
                        console.warn('Error sincronizando con Livewire:', lwErr);
                    }
                }

                this.setNotification('success', `Orden '${opLabel}' enviada con éxito al router ${targetHex}.`);
                this.blockedNodeInput = '';
            } catch (err) {
                console.error('Error gestionando nodo bloqueado:', err);
            } finally {
                this.blockedSending = false;
            }
        },

        /**
         * Emite un sondeo o anuncio broadcast a toda la malla.
         */
        async sendMeshPoll(pollType) {
            if (this.connectionStatus !== 'connected' || !this._device) {
                this.setNotification('warning', 'Debes conectar primero tu nodo Meshtastic local.');
                return;
            }

            this.pollSending = true;
            try {
                let portnum;
                let label;
                let payload;

                if (pollType === 'nodeinfo') {
                    portnum = PortNum.NODEINFO_APP;
                    label = 'Anuncio broadcast de Identidad (NodeInfo)';
                    const userMsg = create(UserSchema, {
                        id: this.localNode.hexId,
                        longName: this.localNode.longName || 'Operador Andalucía Mesh',
                        shortName: this.localNode.shortName || 'OP',
                        hwModel: 0,
                    });
                    payload = toBinary(UserSchema, userMsg);
                } else if (pollType === 'position') {
                    portnum = PortNum.POSITION_APP;
                    label = 'Anuncio broadcast de Posición GPS';
                    const posMsg = create(PositionSchema, {
                        latitudeI: 0,
                        longitudeI: 0,
                        altitude: 0,
                    });
                    payload = toBinary(PositionSchema, posMsg);
                } else if (pollType === 'telemetry') {
                    portnum = PortNum.TELEMETRY_APP;
                    label = 'Anuncio broadcast de Telemetría';
                    const telemMsg = create(TelemetrySchema, {
                        time: Math.trunc(Date.now() / 1000),
                        deviceMetrics: {
                            batteryLevel: 100,
                            voltage: 4.2,
                        },
                    });
                    payload = toBinary(TelemetrySchema, telemMsg);
                } else {
                    throw new Error(`Tipo de sondeo no soportado: ${pollType}`);
                }

                this.log('tx', `📡 Emitiendo ${label} (^all)...`);

                const pktId = await this.sendMeshPacketCustom({
                    payloadBytes: payload,
                    portNum: portnum,
                    destinationNum: Constants.broadcastNum,
                    channel: 0,
                    wantAck: false,
                    wantResponse: false,
                    pkiEncrypted: false,
                });

                const okMsg = `${label} emitido a la malla (ID: ${pktId}). Los nodos cercanos actualizarán su NodeDB.`;
                this.setNotification('success', okMsg);
                this.log('tx', `📡 ${okMsg}`);
            } catch (err) {
                const errMsg = formatMeshtasticError(err);
                this.setNotification('error', `Error en sondeo broadcast: ${errMsg}`);
                this.log('error', `❌ Error en emisión broadcast: ${errMsg}`);
            } finally {
                this.pollSending = false;
            }
        },

        /**
         * Envía una petición unicast a un único nodo específico.
         */
        async sendUnicastRequest(reqType) {
            if (this.connectionStatus !== 'connected' || !this._device) {
                this.setNotification('warning', 'Debes conectar primero tu nodo Meshtastic local.');
                return;
            }

            this.unicastSending = true;
            try {
                let targetNum;
                if (this.unicastTargetInput.trim()) {
                    targetNum = parseNodeNum(this.unicastTargetInput);
                } else {
                    targetNum = this.resolveTargetNodeNum();
                }

                const targetHex = numToHex(targetNum);

                if (reqType === 'traceroute') {
                    if (this.tracerouteActive) {
                        this.setNotification('warning', 'Ya hay un Traceroute en curso. Espera a que finalice.');
                        return;
                    }

                    this.tracerouteActive = true;
                    this.tracerouteCountdown = 30;
                    this.tracerouteTargetHex = targetHex;
                    this.tracerouteResult = '';
                    this.tracerouteHops = [];

                    this.log('tx', `🔄 Iniciando Traceroute hacia ${targetHex} (esperando hasta 30 segundos)...`);

                    // Temporizador reactivo de 30 segundos
                    this.tracerouteTimer = setInterval(() => {
                        this.tracerouteCountdown--;
                        if (this.tracerouteCountdown <= 0) {
                            clearInterval(this.tracerouteTimer);
                            this.tracerouteTimer = null;
                            if (this.tracerouteActive) {
                                this.tracerouteActive = false;
                                this.tracerouteResult = `Tiempo de espera agotado (30s) sin respuesta de ruta de ${targetHex}.`;
                                this.setNotification('warning', `Traceroute: tiempo de espera agotado (30s) sin respuesta de ${targetHex}.`);
                                this.log('warn', `⏱️ Traceroute hacia ${targetHex}: tiempo de espera agotado (30s). El nodo no respondió.`);
                            }
                        }
                    }, 1000);

                    const routeMsg = create(RouteDiscoverySchema, { route: [] });
                    const payload = toBinary(RouteDiscoverySchema, routeMsg);

                    try {
                        const pktId = await this.sendMeshPacketCustom({
                            payloadBytes: payload,
                            portNum: PortNum.TRACEROUTE_APP,
                            destinationNum: targetNum,
                            channel: 0,
                            wantAck: true,
                            wantResponse: true,
                            pkiEncrypted: false,
                            timeoutMs: 8000,
                        });
                        this.setNotification('info', `Traceroute enviado a ${targetHex} (ID: ${pktId}). Esperando respuesta de los saltos de malla (30s)...`);
                    } catch (txErr) {
                        this.log('warn', `⚠️ Aviso al transmitir sonda Traceroute: ${txErr.message}`);
                    }
                    return;
                } else {
                    let portnum;
                    let label;
                    let payloadBytes;

                    if (reqType === 'nodeinfo') {
                        portnum = PortNum.NODEINFO_APP;
                        label = 'NodeInfo';
                        const userMsg = create(UserSchema, {});
                        payloadBytes = toBinary(UserSchema, userMsg);
                    } else if (reqType === 'position') {
                        portnum = PortNum.POSITION_APP;
                        label = 'Posición GPS';
                        const posMsg = create(PositionSchema, {});
                        payloadBytes = toBinary(PositionSchema, posMsg);
                    } else if (reqType === 'telemetry') {
                        portnum = PortNum.TELEMETRY_APP;
                        label = 'Telemetría';
                        const telemMsg = create(TelemetrySchema, {});
                        payloadBytes = toBinary(TelemetrySchema, telemMsg);
                    } else {
                        throw new Error(`Petición no reconocida: ${reqType}`);
                    }

                    this.log('tx', `Petición unicast '${label}' transmitida a ${targetHex}...`);
                    const pktId = await this.sendMeshPacketCustom({
                        payloadBytes: payloadBytes,
                        portNum: portnum,
                        destinationNum: targetNum,
                        channel: 0,
                        wantAck: true,
                        wantResponse: true,
                        pkiEncrypted: false,
                    });
                    this.setNotification('success', `Petición '${label}' transmitida a ${targetHex} (ID: ${pktId}).`);
                    this.log('tx', `Petición '${label}' en vuelo hacia ${targetHex} (ID: ${pktId}).`);
                }
            } catch (err) {
                const errMsg = formatMeshtasticError(err, targetHex);
                this.setNotification('error', `Error en petición: ${errMsg}`);
                this.log('error', `❌ Error unicast: ${errMsg}`);
            } finally {
                this.unicastSending = false;
            }
        },

        /**
         * Envía comando de reinicio remoto diferido al router.
         */
        async applyRemoteReboot() {
            if (this.connectionStatus !== 'connected' || !this._device) {
                this.setNotification('warning', 'Debes conectar primero tu nodo Meshtastic local.');
                return;
            }

            const targetNum = this.resolveTargetNodeNum();
            const targetHex = numToHex(targetNum);
            const secs = Number(this.rebootSeconds) || 10;

            const conf = confirm(`¿Estás seguro de que deseas reiniciar en remoto el router ${targetHex} en ${secs} segundos?`);
            if (!conf) return;

            this.rebootSending = true;
            try {
                this.log('tx', `⚠️ Preparando comando de reinicio diferido (${secs}s) a ${targetHex}...`);

                const adminMsg = create(AdminMessageSchema, {
                    payloadVariant: {
                        case: 'rebootSeconds',
                        value: secs,
                    },
                });

                await this.sendAdminMessageToTarget(targetNum, adminMsg, `Reinicio diferido (${secs}s)`);
            } catch (err) {
                console.error('Error al reiniciar router:', err);
            } finally {
                this.rebootSending = false;
            }
        },

        /**
         * Registra un evento en la consola de actividad.
         */
        log(type, text) {
            const time = new Date().toLocaleTimeString();
            this.logs.push({ time, type, text });

            if (this.logs.length > 500) {
                this.logs.shift();
            }

            if (this.autoScroll) {
                const scrollConsole = () => {
                    const consoleEl = document.getElementById('meshAdminConsole');
                    if (consoleEl) {
                        consoleEl.scrollTop = consoleEl.scrollHeight;
                    }
                };

                if (typeof this.$nextTick === 'function') {
                    this.$nextTick(scrollConsole);
                } else {
                    setTimeout(scrollConsole, 50);
                }
            }
        },

        /**
         * Limpia todos los logs de la consola.
         */
        clearLogs() {
            this.logs = [];
        },

        /**
         * Copia los logs al portapapeles.
         */
        copyLogs() {
            const txt = this.logs.map((l) => `[${l.time}] [${l.type.toUpperCase()}] ${l.text}`).join('\n');
            navigator.clipboard.writeText(txt).then(() => {
                this.setNotification('info', 'Logs de actividad copiados al portapapeles.');
            });
        },

        /**
         * Retorna los logs filtrados según la selección del usuario.
         */
        get filteredLogs() {
            if (this.logFilter === 'all') return this.logs;
            return this.logs.filter((l) => l.type === this.logFilter);
        },
    };
}

// Exposición global en window y registro en Alpine.js
if (typeof window !== 'undefined') {
    window.meshAdmin = meshAdminComponent;
    window.numToHex = numToHex;
    window.parseNodeNum = parseNodeNum;

    if (window.Alpine) {
        window.Alpine.data('meshAdmin', meshAdminComponent);
    }
    document.addEventListener('alpine:init', () => {
        if (window.Alpine) {
            window.Alpine.data('meshAdmin', meshAdminComponent);
        }
    });
}

