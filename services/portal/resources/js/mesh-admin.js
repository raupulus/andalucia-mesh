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

import { create, fromBinary, toBinary } from "@bufbuild/protobuf";
import { MeshDevice, Protobuf, Constants } from "@meshtastic/core";
import { TransportWebSerial } from "@meshtastic/transport-web-serial";
import { TransportWebBluetooth } from "@meshtastic/transport-web-bluetooth";
import { TransportHTTP } from "@meshtastic/transport-http";

const { AdminMessageSchema } = Protobuf.Admin;
const { ConfigSchema, Config_DeviceConfigSchema, Config_DeviceConfig_Role } = Protobuf.Config;
const { UserSchema, PositionSchema, RouteDiscoverySchema } = Protobuf.Mesh;
const { TelemetrySchema } = Protobuf.Telemetry;
const { PortNum } = Protobuf.Portnums;

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
        activeTab: 'roles',               // 'roles' | 'favoritos' | 'sondeo' | 'unicast' | 'mantenimiento'

        // Datos del formulario de roles
        selectedRole: 1,                  // Default CLIENT_MUTE (1)
        roleSending: false,

        // Datos del formulario de favoritos
        favoriteNodeInput: '',
        favoriteActionType: 'add',        // 'add' | 'remove'
        favoriteSending: false,
        sessionFavorites: [],

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

        // Referencias internas del SDK
        _device: null,
        _transport: null,

        /**
         * Inicialización del componente.
         */
        init() {
            this.log('info', 'Consola de Gestión Remota de Routers inicializada.');
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

                // Suscripción a eventos de estado del dispositivo
                device.events.onDeviceStatus.subscribe((status) => {
                    this.log('info', `Estado del dispositivo local: ${status}`);
                    if (status === 'connected' || status === 'configured') {
                        this.connectionStatus = 'connected';
                    } else if (status === 'disconnected') {
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
                    }
                });

                // Suscripción a paquetes entrantes de la malla
                device.events.onMeshPacket.subscribe((packet) => {
                    this.handleIncomingMeshPacket(packet);
                });

                // Solicitar configuración y estado inicial
                await device.configure();

                this.connectionStatus = 'connected';
                this.log('info', '¡Conexión establecida con el nodo local! Listo para transmitir.');
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

                // ACK de enrutamiento
                if (port === PortNum.ROUTING_APP) {
                    this.log('ack', `ACK recibido de ${fromHex} hacia ${toHex} (ID: ${packet.id}) ${meta ? `[${meta}]` : ''}`);
                    return;
                }

                // Respuesta administrativa
                if (port === PortNum.ADMIN_APP) {
                    try {
                        const adminMsg = fromBinary(AdminMessageSchema, rawBytes);
                        const variant = adminMsg.payloadVariant?.case || 'desconocido';
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
                        this.log('rx', `NodeInfo de ${fromHex}: "${user.longName}" (${user.shortName}) [HW: ${user.hwModel}]`);
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
                        const hopsList = (route.route || []).map((h) => numToHex(h)).join(' ➔ ');
                        this.log('rx', `Traceroute respuesta de ${fromHex}: ${hopsList || 'Directo (0 saltos)'}`);
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
            }
        },

        /**
         * Envía comando remoto de cambio de rol al router seleccionado.
         */
        async applyRemoteRole() {
            if (this.connectionStatus !== 'connected' || !this._device) {
                alert('Debes conectar primero tu nodo Meshtastic local.');
                return;
            }

            this.roleSending = true;
            try {
                const targetNum = this.resolveTargetNodeNum();
                const targetHex = numToHex(targetNum);
                const roleEnum = Number(this.selectedRole);
                const roleName = Config_DeviceConfig_Role[roleEnum] || `ROL_${roleEnum}`;

                this.log('tx', `Preparando cambio de rol a '${roleName}' para el router ${targetHex}...`);

                // Construir AdminMessage con setConfig.device.role
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

                const payload = toBinary(AdminMessageSchema, adminMsg);

                // Enviar paquete administrativo al destino con wantAck=true y wantResponse=true
                await this._device.sendPacket(payload, PortNum.ADMIN_APP, targetNum, 0, true, true);

                this.log('tx', `🚀 Comando 'setConfig.device.role = ${roleName}' transmitido a ${targetHex}. Esperando confirmación ACK...`);
                alert(`Comando de cambio de rol (${roleName}) transmitido al router ${targetHex}. Observa la consola para el ACK.`);
            } catch (err) {
                console.error('Error enviando cambio de rol:', err);
                this.log('error', `Error al enviar rol: ${err.message}`);
                alert(`Error al enviar cambio de rol: ${err.message}`);
            } finally {
                this.roleSending = false;
            }
        },

        /**
         * Gestiona favoritos (añadir o eliminar) en el router remoto.
         */
        async applyRemoteFavorite(action) {
            if (this.connectionStatus !== 'connected' || !this._device) {
                alert('Debes conectar primero tu nodo Meshtastic local.');
                return;
            }

            this.favoriteSending = true;
            try {
                const targetNum = this.resolveTargetNodeNum();
                const targetHex = numToHex(targetNum);
                const favNum = parseNodeNum(this.favoriteNodeInput);

                if (!favNum) {
                    throw new Error('Introduce un Node ID válido para gestionar en favoritos.');
                }
                const favHex = numToHex(favNum);

                const isAdd = action === 'add';
                this.log('tx', `Preparando ${isAdd ? 'setFavoriteNode' : 'removeFavoriteNode'} (${favHex}) en router ${targetHex}...`);

                const adminMsg = create(AdminMessageSchema, {
                    payloadVariant: isAdd
                        ? { case: 'setFavoriteNode', value: favNum }
                        : { case: 'removeFavoriteNode', value: favNum },
                });

                const payload = toBinary(AdminMessageSchema, adminMsg);
                await this._device.sendPacket(payload, PortNum.ADMIN_APP, targetNum, 0, true, true);

                if (isAdd && !this.sessionFavorites.includes(favHex)) {
                    this.sessionFavorites.push(favHex);
                } else if (!isAdd) {
                    this.sessionFavorites = this.sessionFavorites.filter((f) => f !== favHex);
                }

                this.log('tx', `⭐ Comando ${isAdd ? 'Añadir a' : 'Quitar de'} favoritos (${favHex}) transmitido a ${targetHex}.`);
                alert(`Comando de favoritos (${favHex}) transmitido a ${targetHex}.`);
            } catch (err) {
                console.error('Error gestionando favorito:', err);
                this.log('error', `Error en favorito: ${err.message}`);
                alert(`Error en favoritos: ${err.message}`);
            } finally {
                this.favoriteSending = false;
            }
        },

        /**
         * Emite un sondeo broadcast a toda la malla.
         */
        async sendMeshPoll(pollType) {
            if (this.connectionStatus !== 'connected' || !this._device) {
                alert('Debes conectar primero tu nodo Meshtastic local.');
                return;
            }

            this.pollSending = true;
            try {
                let portnum;
                let label;

                if (pollType === 'nodeinfo') {
                    portnum = PortNum.NODEINFO_APP;
                    label = 'NodeInfo (Identificación)';
                } else if (pollType === 'position') {
                    portnum = PortNum.POSITION_APP;
                    label = 'Posición GPS';
                } else if (pollType === 'telemetry') {
                    portnum = PortNum.TELEMETRY_APP;
                    label = 'Telemetría y Baterías';
                } else {
                    throw new Error(`Tipo de sondeo no soportado: ${pollType}`);
                }

                this.log('tx', `📡 Emitiendo sondeo de malla broadcast: ${label} (^all)...`);
                await this._device.sendPacket(new Uint8Array(0), portnum, Constants.broadcastNum, 0, false, true);
                this.log('tx', `Sondeo broadcast '${label}' emitido a la malla. Las respuestas aparecerán en la consola.`);
            } catch (err) {
                console.error('Error enviando sondeo:', err);
                this.log('error', `Error en sondeo: ${err.message}`);
                alert(`Error en sondeo: ${err.message}`);
            } finally {
                this.pollSending = false;
            }
        },

        /**
         * Envía una petición unicast a un único nodo específico.
         */
        async sendUnicastRequest(reqType) {
            if (this.connectionStatus !== 'connected' || !this._device) {
                alert('Debes conectar primero tu nodo Meshtastic local.');
                return;
            }

            this.unicastSending = true;
            try {
                // Si hay un nodo especificado en la pestaña unicast usarlo, si no el router seleccionado
                let targetNum;
                if (this.unicastTargetInput.trim()) {
                    targetNum = parseNodeNum(this.unicastTargetInput);
                } else {
                    targetNum = this.resolveTargetNodeNum();
                }

                const targetHex = numToHex(targetNum);

                if (reqType === 'traceroute') {
                    this.log('tx', `🔄 Iniciando Traceroute hacia ${targetHex}...`);
                    const routeMsg = create(RouteDiscoverySchema, { route: [] });
                    const payload = toBinary(RouteDiscoverySchema, routeMsg);
                    await this._device.sendPacket(payload, PortNum.TRACEROUTE_APP, targetNum, 0, true, true);
                    this.log('tx', `Traceroute transmitido hacia ${targetHex}. Esperando paquetes de retorno...`);
                } else {
                    let portnum;
                    let label;
                    if (reqType === 'nodeinfo') {
                        portnum = PortNum.NODEINFO_APP;
                        label = 'NodeInfo';
                    } else if (reqType === 'position') {
                        portnum = PortNum.POSITION_APP;
                        label = 'Posición GPS';
                    } else if (reqType === 'telemetry') {
                        portnum = PortNum.TELEMETRY_APP;
                        label = 'Telemetría';
                    } else {
                        throw new Error(`Petición no reconocida: ${reqType}`);
                    }

                    this.log('tx', `Petición unicast '${label}' transmitida a ${targetHex}...`);
                    await this._device.sendPacket(new Uint8Array(0), portnum, targetNum, 0, true, true);
                }
            } catch (err) {
                console.error('Error enviando petición unicast:', err);
                this.log('error', `Error unicast: ${err.message}`);
                alert(`Error en petición unicast: ${err.message}`);
            } finally {
                this.unicastSending = false;
            }
        },

        /**
         * Envía comando de reinicio remoto diferido al router.
         */
        async applyRemoteReboot() {
            if (this.connectionStatus !== 'connected' || !this._device) {
                alert('Debes conectar primero tu nodo Meshtastic local.');
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

                const payload = toBinary(AdminMessageSchema, adminMsg);
                await this._device.sendPacket(payload, PortNum.ADMIN_APP, targetNum, 0, true, true);

                this.log('tx', `⚠️ Comando reboot_seconds(${secs}) enviado a ${targetHex}. El nodo se reiniciará en ${secs}s.`);
                alert(`Comando de reinicio enviado con éxito al router ${targetHex}.`);
            } catch (err) {
                console.error('Error al reiniciar router:', err);
                this.log('error', `Error reinicio: ${err.message}`);
                alert(`Error al enviar reinicio: ${err.message}`);
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
                this.$nextTick(() => {
                    const consoleEl = document.getElementById('meshAdminConsole');
                    if (consoleEl) {
                        consoleEl.scrollTop = consoleEl.scrollHeight;
                    }
                });
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
                alert('Logs de actividad copiados al portapapeles.');
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

