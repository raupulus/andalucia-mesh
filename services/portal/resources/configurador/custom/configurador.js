// ==============================================================================
// configurador.js (integrations/meshconfig/custom/)
//
// Lógica de cliente para el Configurador de Dispositivos Meshtastic en Andalucía Mesh.
// Soporta Web Serial (USB), Web Bluetooth (BLE), HTTP local, exportación YAML,
// generación de URLs oficiales y códigos QR para la app móvil de Meshtastic.
// Basado en el motor de comunicación de pdxlocations/meshconfig (Licencia MIT).
// ==============================================================================

import { create, fromBinary, fromJson, toBinary, toJson } from "https://esm.sh/@bufbuild/protobuf@2.2.5";
import { MeshDevice, Protobuf } from "https://esm.sh/jsr/@meshtastic/core@2.6.6";
import { TransportHTTP } from "https://esm.sh/jsr/@meshtastic/transport-http@0.2.1";
import yaml from "https://esm.sh/js-yaml@4.1.0";

// --- Constantes del protocolo ---
const PROTOCOL_CONFIG_TYPES = [
  ["device", Protobuf.Admin.AdminMessage_ConfigType.DEVICE_CONFIG],
  ["position", Protobuf.Admin.AdminMessage_ConfigType.POSITION_CONFIG],
  ["power", Protobuf.Admin.AdminMessage_ConfigType.POWER_CONFIG],
  ["network", Protobuf.Admin.AdminMessage_ConfigType.NETWORK_CONFIG],
  ["display", Protobuf.Admin.AdminMessage_ConfigType.DISPLAY_CONFIG],
  ["lora", Protobuf.Admin.AdminMessage_ConfigType.LORA_CONFIG],
  ["bluetooth", Protobuf.Admin.AdminMessage_ConfigType.BLUETOOTH_CONFIG],
  ["security", Protobuf.Admin.AdminMessage_ConfigType.SECURITY_CONFIG],
  ["deviceUi", Protobuf.Admin.AdminMessage_ConfigType.DEVICEUI_CONFIG]
];

const PROTOCOL_MODULE_CONFIG_TYPES = [
  ["mqtt", Protobuf.Admin.AdminMessage_ModuleConfigType.MQTT_CONFIG],
  ["serial", Protobuf.Admin.AdminMessage_ModuleConfigType.SERIAL_CONFIG],
  ["externalNotification", Protobuf.Admin.AdminMessage_ModuleConfigType.EXTNOTIF_CONFIG],
  ["storeForward", Protobuf.Admin.AdminMessage_ModuleConfigType.STOREFORWARD_CONFIG],
  ["rangeTest", Protobuf.Admin.AdminMessage_ModuleConfigType.RANGETEST_CONFIG],
  ["telemetry", Protobuf.Admin.AdminMessage_ModuleConfigType.TELEMETRY_CONFIG],
  ["cannedMessage", Protobuf.Admin.AdminMessage_ModuleConfigType.CANNEDMSG_CONFIG],
  ["audio", Protobuf.Admin.AdminMessage_ModuleConfigType.AUDIO_CONFIG],
  ["remoteHardware", Protobuf.Admin.AdminMessage_ModuleConfigType.REMOTEHARDWARE_CONFIG],
  ["neighborInfo", Protobuf.Admin.AdminMessage_ModuleConfigType.NEIGHBORINFO_CONFIG],
  ["ambientLighting", Protobuf.Admin.AdminMessage_ModuleConfigType.AMBIENTLIGHTING_CONFIG],
  ["detectionSensor", Protobuf.Admin.AdminMessage_ModuleConfigType.DETECTIONSENSOR_CONFIG],
  ["paxcounter", Protobuf.Admin.AdminMessage_ModuleConfigType.PAXCOUNTER_CONFIG],
  ["statusMessage", Protobuf.Admin.AdminMessage_ModuleConfigType.STATUSMESSAGE_CONFIG],
  ["trafficManagement", Protobuf.Admin.AdminMessage_ModuleConfigType.TRAFFICMANAGEMENT_CONFIG],
  ["tak", Protobuf.Admin.AdminMessage_ModuleConfigType.TAK_CONFIG]
];

// --- Estado global de la aplicación ---
const estado = {
  pasoActual: 1,
  modo: "asistente", // 'asistente' o 'workbench'
  rolSeleccionado: "CLIENT_MUTE",
  potenciaTx: 27,
  provincia: "",
  nodoConectado: false,
  dispositivo: null,
  transporte: null,
  myNodeNum: null,
  myNodeInfo: null,
  sessionPasskey: null,
  configSections: {},
  moduleConfigSections: {},
  channelMap: new Map(),
  liveConfig: null,
  desiredConfig: null,
  qrInstance: null,
  pendingAdminResponses: []
};

// --- Registro en consola interna ---
function logActividad(mensaje) {
  const hora = new Date().toLocaleTimeString();
  const linea = `[${hora}] ${mensaje}\n`;
  const textarea = document.getElementById("logTextarea");
  if (textarea) {
    textarea.value = linea + textarea.value;
  }
}

// --- Transportes Serie y Bluetooth ---
function createToDeviceStream() {
  return new TransformStream({
    transform(chunk, controller) {
      const bufLen = chunk.length;
      const header = new Uint8Array([0x94, 0xc3, (bufLen >> 8) & 0xff, bufLen & 0xff]);
      controller.enqueue(new Uint8Array([...header, ...chunk]));
    },
  });
}

function createFromDeviceStream() {
  let byteBuffer = new Uint8Array([]);
  const textDecoder = new TextDecoder();

  return new TransformStream({
    transform(chunk, controller) {
      byteBuffer = new Uint8Array([...byteBuffer, ...chunk]);
      let exhausted = false;
      while (byteBuffer.length && !exhausted) {
        const framingIndex = byteBuffer.findIndex((byte) => byte === 0x94);
        const framingByte2 = byteBuffer[framingIndex + 1];
        if (framingByte2 !== 0xc3) {
          exhausted = true;
          continue;
        }

        if (byteBuffer.subarray(0, framingIndex).length) {
          controller.enqueue({
            type: "debug",
            data: textDecoder.decode(byteBuffer.subarray(0, framingIndex)),
          });
          byteBuffer = byteBuffer.subarray(framingIndex);
        }

        const msb = byteBuffer[2];
        const lsb = byteBuffer[3];
        const packetLength = msb !== undefined && lsb !== undefined ? (msb << 8) + lsb : null;
        if (packetLength == null || byteBuffer.length < 4 + packetLength) {
          exhausted = true;
          continue;
        }

        const packet = byteBuffer.subarray(4, 4 + packetLength);
        const malformedIndex = packet.findIndex((byte) => byte === 0x94);
        if (malformedIndex !== -1 && packet[malformedIndex + 1] === 0xc3) {
          byteBuffer = byteBuffer.subarray(malformedIndex);
          continue;
        }

        byteBuffer = byteBuffer.subarray(4 + packetLength);
        controller.enqueue({ type: "packet", data: packet });
      }
    },
  });
}

class WebSerialTransport {
  constructor(port) {
    if (!port.readable || !port.writable) throw new Error("Puerto serie no accesible");
    this.port = port;
    this.abortController = new AbortController();
    const toDeviceStream = createToDeviceStream();
    this.pipePromise = toDeviceStream.readable.pipeTo(port.writable, { signal: this.abortController.signal });
    this._toDevice = toDeviceStream.writable;
    this._fromDevice = port.readable.pipeThrough(createFromDeviceStream());
  }

  static async create(baudRate = 115200) {
    const port = await navigator.serial.requestPort();
    await port.open({ baudRate });
    return new WebSerialTransport(port);
  }

  get toDevice() { return this._toDevice; }
  get fromDevice() { return this._fromDevice; }

  async disconnect() {
    try {
      this.abortController.abort();
      if (this.pipePromise) await this.pipePromise.catch(() => {});
      await this.port.close();
    } catch (e) {
      logActividad(`Error cerrando puerto serie: ${e.message}`);
    }
  }
}

class WebBluetoothTransport {
  static ServiceUuid = "6ba1b218-15a8-461f-9fa8-5dcae273eafd";
  static ToRadioUuid = "f75c76d2-129e-4dad-a1dd-7866124401e7";
  static FromRadioUuid = "2c55e69e-4993-11ed-b878-0242ac120002";
  static FromNumUuid = "ed9da18c-a800-4f66-a670-aa7547e34453";

  static async create() {
    const device = await navigator.bluetooth.requestDevice({
      filters: [{ services: [this.ServiceUuid] }]
    });
    const gatt = await device.gatt?.connect();
    if (!gatt) throw new Error("No se pudo conectar al GATT de Bluetooth");
    const service = await gatt.getPrimaryService(this.ServiceUuid);
    const toRadio = await service.getCharacteristic(this.ToRadioUuid);
    const fromRadio = await service.getCharacteristic(this.FromRadioUuid);
    const fromNum = await service.getCharacteristic(this.FromNumUuid);
    return new WebBluetoothTransport(device, gatt, toRadio, fromRadio, fromNum);
  }

  constructor(device, gatt, toRadio, fromRadio, fromNum) {
    this.device = device;
    this.gatt = gatt;
    this.toRadio = toRadio;
    this.fromRadio = fromRadio;
    this.fromNum = fromNum;
    this.closed = false;

    this._fromDevice = new ReadableStream({
      start: (controller) => { this.controller = controller; }
    });

    this._toDevice = new WritableStream({
      write: async (chunk) => {
        await this.toRadio.writeValue(chunk);
      }
    });

    this.onNotify = () => {
      if (this.controller && !this.closed) this.readPackets(this.controller);
    };

    this.fromNum.addEventListener("characteristicvaluechanged", this.onNotify);
    this.fromNum.startNotifications();
  }

  get toDevice() { return this._toDevice; }
  get fromDevice() { return this._fromDevice; }

  async readPackets(controller) {
    while (!this.closed) {
      const val = await this.fromRadio.readValue();
      if (val.byteLength === 0) break;
      controller.enqueue({ type: "packet", data: new Uint8Array(val.buffer) });
    }
  }

  async disconnect() {
    this.closed = true;
    try {
      await this.fromNum.stopNotifications();
      this.fromNum.removeEventListener("characteristicvaluechanged", this.onNotify);
      this.device.gatt?.disconnect();
    } catch (e) {
      logActividad(`Error desconectando BLE: ${e.message}`);
    }
  }
}

// --- Construcción del YAML Deseado según Buenas Prácticas ---
export function construirYamlDeseado() {
  const longName = document.getElementById("inputLongName")?.value.trim() || "MiNodo-Andalucia";
  const shortName = (document.getElementById("inputShortName")?.value.trim() || "AND1").slice(0, 4);
  const esClientMute = estado.rolSeleccionado === "CLIENT_MUTE";
  const hopLimit = esClientMute ? 4 : 3;
  const positionSecs = esClientMute ? 21600 : 259200; // 6h para móvil / 72h para fijo
  const nodeInfoSecs = 259200; // 72h para todos según buenas prácticas
  const txPower = Number(document.querySelector('input[name="txPowerSelect"]:checked')?.value || 27);
  const telemetriaSecs = Number(document.getElementById("telemetriaSelect")?.value || 0);
  const mqttActivo = Boolean(document.getElementById("chkMqtt")?.checked);
  const provSeleccionada = document.getElementById("provinciaSelect")?.value || "";

  // Canales
  const channels = [
    {
      index: 0,
      role: "PRIMARY",
      settings: {
        name: "SFNarrow",
        psk: "AQ==",
        uplinkEnabled: true,
        downlinkEnabled: !esClientMute,
        moduleSettings: { positionPrecision: 15 }
      }
    }
  ];

  let nextIndex = 1;
  if (provSeleccionada) {
    channels.push({
      index: nextIndex++,
      role: "SECONDARY",
      settings: {
        name: provSeleccionada,
        psk: "AQ==",
        uplinkEnabled: true,
        downlinkEnabled: true,
        moduleSettings: { positionPrecision: 15 }
      }
    });
  }

  const opcionales = [
    { id: "chkIberia", name: "Iberia" },
    { id: "chkTest", name: "Test" },
    { id: "chkBots", name: "Bots" },
    { id: "chkSos", name: "sos" }
  ];

  for (const opc of opcionales) {
    if (document.getElementById(opc.id)?.checked && nextIndex < 8) {
      channels.push({
        index: nextIndex++,
        role: "SECONDARY",
        settings: {
          name: opc.name,
          psk: "AQ==",
          uplinkEnabled: true,
          downlinkEnabled: true
        }
      });
    }
  }

  // Rellenar hasta 8 canales vacíos
  while (nextIndex < 8) {
    channels.push({
      index: nextIndex++,
      role: "SECONDARY",
      settings: {}
    });
  }

  const configDoc = {
    owner: longName,
    owner_short: shortName,
    is_unmessagable: false,
    config: {
      device: {
        role: estado.rolSeleccionado,
        nodeInfoBroadcastSecs: nodeInfoSecs,
        rebroadcastMode: esClientMute ? "CORE_PORTNUMS_ONLY" : "ALL"
      },
      lora: {
        region: "EU_868",
        usePreset: false,
        bandwidth: 62,
        spreadFactor: 7,
        codingRate: 5,
        channelNum: 4,
        hopLimit: hopLimit,
        txPower: txPower,
        txEnabled: true,
        sx126xRxBoostedGain: true
      },
      position: {
        positionBroadcastSmartEnabled: false,
        positionBroadcastSecs: positionSecs,
        positionFlags: 0
      },
      security: {
        serialEnabled: true
      }
    },
    module_config: {
      telemetry: {
        deviceUpdateInterval: telemetriaSecs
      },
      mqtt: {
        enabled: mqttActivo,
        address: "mqtt.desdechipiona.es",
        username: "meshdev",
        password: "large4cats",
        root: "msh",
        encryptionEnabled: true,
        proxyToClientEnabled: true,
        mapReportingEnabled: false
      }
    },
    channels: channels
  };

  estado.desiredConfig = configDoc;
  const yamlText = yaml.dump(configDoc, { lineWidth: 120, noRefs: true, sortKeys: false });
  
  const textareaDesired = document.getElementById("desiredYamlTextarea");
  if (textareaDesired) textareaDesired.value = yamlText;

  return { configDoc, yamlText };
}

// --- Generador de URL y Código QR oficial de Meshtastic ---
// Codifica en Base64Url un protobuf ChannelSet mínimo conforme a apponly.proto
function encodeVarint(val) {
  const bytes = [];
  while (val > 127) {
    bytes.push((val & 0x7f) | 0x80);
    val >>>= 7;
  }
  bytes.push(val & 0x7f);
  return bytes;
}

function encodeChannelSettingsProto(name, pskBytes = [1], uplink = true, downlink = true) {
  // ChannelSettings: tag 2 psk (bytes), tag 3 name (string), tag 5 uplink (bool), tag 6 downlink (bool)
  const nameBytes = new TextEncoder().encode(name);
  const body = [
    // psk tag 2 (wire 2 = 0x12)
    0x12, pskBytes.length, ...pskBytes,
    // name tag 3 (wire 2 = 0x1a)
    0x1a, nameBytes.length, ...nameBytes,
    // uplink_enabled tag 5 (wire 0 = 0x28)
    0x28, uplink ? 1 : 0,
    // downlink_enabled tag 6 (wire 0 = 0x30)
    0x30, downlink ? 1 : 0
  ];
  return [0x0a, ...encodeVarint(body.length), ...body];
}

function encodeLoraConfigProto(bw = 62, sf = 7, cr = 5, channelNum = 4, hopLimit = 4, txPower = 27) {
  // LoRaConfig: tag 3 bw (62), tag 4 sf (7), tag 5 cr (5), tag 7 region (EU_868=3), tag 8 hop_limit, tag 10 tx_power, tag 11 channel_num
  const body = [
    0x18, ...encodeVarint(bw),
    0x20, ...encodeVarint(sf),
    0x28, ...encodeVarint(cr),
    0x38, 3, // region EU_868
    0x40, ...encodeVarint(hopLimit),
    0x50, ...encodeVarint(txPower),
    0x58, ...encodeVarint(channelNum)
  ];
  return [0x12, ...encodeVarint(body.length), ...body];
}

function generarMeshtasticUrl(doc) {
  const bytes = [];
  // Canal 0: SFNarrow
  bytes.push(...encodeChannelSettingsProto("SFNarrow", [1], true, doc.config.device.role !== "CLIENT_MUTE"));

  // Canal 1 si hay provincia
  const prov = document.getElementById("provinciaSelect")?.value || "";
  if (prov) {
    bytes.push(...encodeChannelSettingsProto(prov, [1], true, true));
  }

  // LoRa config
  const lora = doc.config.lora;
  bytes.push(...encodeLoraConfigProto(lora.bandwidth, lora.spreadFactor, lora.codingRate, lora.channelNum, lora.hopLimit, lora.txPower));

  // Base64Url sin padding
  const uint8 = new Uint8Array(bytes);
  let binary = "";
  for (let i = 0; i < uint8.byteLength; i++) {
    binary += String.fromCharCode(uint8[i]);
  }
  const b64 = btoa(binary).replaceAll("+", "-").replaceAll("/", "_").replaceAll("=", "");
  return `https://meshtastic.org/e/#${b64}`;
}

export function actualizarConfiguracion() {
  const { configDoc } = construirYamlDeseado();

  // Actualizar vista previa de identidad
  const longName = configDoc.owner;
  const shortName = configDoc.owner_short;
  const avatarEl = document.getElementById("previewAvatar");
  const longEl = document.getElementById("previewLongName");
  const shortEl = document.getElementById("previewShortName");

  if (avatarEl) avatarEl.textContent = longName.charAt(0).toUpperCase();
  if (longEl) longEl.textContent = longName;
  if (shortEl) shortEl.textContent = shortName;

  // Generar URL y renderizar código QR
  const shareUrl = generarMeshtasticUrl(configDoc);
  const inputUrl = document.getElementById("qrShareUrl");
  if (inputUrl) inputUrl.value = shareUrl;

  const qrContainer = document.getElementById("qrCanvasContainer");
  if (qrContainer && window.QRCode) {
    qrContainer.innerHTML = "";
    new window.QRCode(qrContainer, {
      text: shareUrl,
      width: 220,
      height: 220,
      colorDark: "#2C2D3C",
      colorLight: "#FFFFFF",
      correctLevel: window.QRCode.CorrectLevel.M
    });
  }
}

// --- Navegación del Asistente ---
export function irAlPaso(numPaso) {
  estado.pasoActual = numPaso;
  for (let i = 1; i <= 4; i++) {
    const indicator = document.getElementById(`stepIndicator${i}`);
    const panel = document.getElementById(`stepPanel${i}`);
    if (indicator) {
      indicator.classList.toggle("active", i === numPaso);
      indicator.classList.toggle("done", i < numPaso);
    }
    if (panel) {
      panel.classList.toggle("active", i === numPaso);
    }
  }
  if (numPaso === 4) {
    actualizarConfiguracion();
  }
  window.scrollTo({ top: 0, behavior: "smooth" });
}

export function seleccionarRol(rol) {
  estado.rolSeleccionado = rol;
  const cardMute = document.getElementById("cardRoleMute");
  const cardClient = document.getElementById("cardRoleClient");
  if (cardMute) cardMute.classList.toggle("selected", rol === "CLIENT_MUTE");
  if (cardClient) cardClient.classList.toggle("selected", rol === "CLIENT");
  actualizarConfiguracion();
}

// --- Acciones de descarga y copia ---
export function descargarYamlDeseado() {
  const { yamlText } = construirYamlDeseado();
  const blob = new Blob([yamlText], { type: "text/yaml;charset=utf-8" });
  const url = URL.createObjectURL(blob);
  const a = document.createElement("a");
  a.href = url;
  a.download = "andalucia-mesh-sfnarrow.yaml";
  document.body.appendChild(a);
  a.click();
  a.remove();
  URL.revokeObjectURL(url);
  logActividad("Archivo andalucia-mesh-sfnarrow.yaml descargado con éxito.");
}

export function copiarEnlaceQR() {
  const inputUrl = document.getElementById("qrShareUrl");
  if (inputUrl && inputUrl.value) {
    navigator.clipboard.writeText(inputUrl.value).then(() => {
      alert("Enlace oficial de Meshtastic copiado al portapapeles.");
      logActividad("Enlace de canales copiado al portapapeles.");
    });
  }
}

export function copiarComandosCli() {
  const { configDoc } = construirYamlDeseado();
  const lora = configDoc.config.lora;
  const dev = configDoc.config.device;
  const pos = configDoc.config.position;

  const comandos = [
    `# Configuración oficial Andalucía Mesh (SFNarrow)`,
    `meshtastic --set-owner "${configDoc.owner}" --set-owner-short "${configDoc.owner_short}"`,
    `meshtastic --set lora.region ${lora.region} --set lora.use_preset false`,
    `meshtastic --set lora.bandwidth ${lora.bandwidth} --set lora.spread_factor ${lora.spreadFactor} --set lora.coding_rate ${lora.codingRate}`,
    `meshtastic --set lora.channel_num ${lora.channelNum} --set lora.hop_limit ${lora.hopLimit} --set lora.tx_power ${lora.txPower}`,
    `meshtastic --set device.role ${dev.role} --set device.node_info_broadcast_secs ${dev.nodeInfoBroadcastSecs}`,
    `meshtastic --set position.position_broadcast_smart_enabled false --set position.position_flags 0 --set position.position_broadcast_secs ${pos.positionBroadcastSecs}`,
    `meshtastic --ch-set name "SFNarrow" --ch-set psk "AQ==" --ch-index 0`
  ].join("\n");

  navigator.clipboard.writeText(comandos).then(() => {
    alert("Comandos CLI de Meshtastic copiados al portapapeles.");
    logActividad("Comandos CLI copiados al portapapeles.");
  });
}

// --- Conexión directa al nodo y programación ---
export async function conectarDispositivo() {
  const tipoTransporte = document.getElementById("transportSelect")?.value || "serial";
  const btnConnect = document.getElementById("btnConnectDirect");
  const btnDisconnect = document.getElementById("btnDisconnectDirect");
  const statusPill = document.getElementById("statusPill");

  try {
    if (btnConnect) btnConnect.disabled = true;
    logActividad(`Iniciando conexión directa por ${tipoTransporte}...`);

    let transport;
    if (tipoTransporte === "serial") {
      transport = await WebSerialTransport.create(115200);
    } else if (tipoTransporte === "bluetooth") {
      transport = await WebBluetoothTransport.create();
    } else if (tipoTransporte === "http") {
      const host = document.getElementById("httpIpInput")?.value.trim() || "meshtastic.local";
      transport = new TransportHTTP(host, false);
    }

    estado.transporte = transport;
    const device = new MeshDevice(transport);
    estado.dispositivo = device;
    estado.nodoConectado = true;

    if (btnDisconnect) btnDisconnect.disabled = false;
    if (statusPill) {
      statusPill.textContent = "⚡ Conectado";
      statusPill.style.background = "var(--color-correcto-fondo)";
      statusPill.style.color = "var(--color-correcto-texto)";
    }

    logActividad("Nodo conectado correctamente. Leyendo parámetros de radio...");
    alert("¡Nodo conectado! Puedes aplicar la configuración ahora o inspeccionar en el modo Workbench.");
  } catch (e) {
    logActividad(`Error de conexión: ${e.message}`);
    alert(`No se pudo conectar al dispositivo: ${e.message}`);
    if (btnConnect) btnConnect.disabled = false;
  }
}

export async function desconectarDispositivo() {
  if (estado.transporte) {
    await estado.transporte.disconnect();
    estado.transporte = null;
    estado.dispositivo = null;
    estado.nodoConectado = false;

    const btnConnect = document.getElementById("btnConnectDirect");
    const btnDisconnect = document.getElementById("btnDisconnectDirect");
    const statusPill = document.getElementById("statusPill");

    if (btnConnect) btnConnect.disabled = false;
    if (btnDisconnect) btnDisconnect.disabled = true;
    if (statusPill) {
      statusPill.textContent = "🔌 Desconectado";
      statusPill.style.background = "";
      statusPill.style.color = "";
    }
    logActividad("Dispositivo desconectado.");
  }
}

// --- Conmutación de Modo (Asistente vs Workbench) ---
export function setModo(modo) {
  estado.modo = modo;
  const tabAsistente = document.getElementById("tabAssistantMode");
  const tabWorkbench = document.getElementById("tabWorkbenchMode");
  const viewAsistente = document.getElementById("viewAssistant");
  const viewWorkbench = document.getElementById("viewWorkbench");

  const esAsistente = modo === "asistente";
  if (tabAsistente) tabAsistente.classList.toggle("active", esAsistente);
  if (tabWorkbench) tabWorkbench.classList.toggle("active", !esAsistente);
  if (viewAsistente) viewAsistente.style.display = esAsistente ? "block" : "none";
  if (viewWorkbench) viewWorkbench.style.display = esAsistente ? "none" : "block";

  if (!esAsistente) {
    construirYamlDeseado();
  }
}

// --- Tema claro / oscuro ---
export function toggleTema() {
  const html = document.documentElement;
  const temaActual = html.getAttribute("data-tema") || "light";
  const nuevoTema = temaActual === "light" ? "dark" : "light";
  html.setAttribute("data-tema", nuevoTema);
  localStorage.setItem("snm_tema", nuevoTema);

  const icon = document.getElementById("themeIcon");
  if (icon) icon.textContent = nuevoTema === "light" ? "🌙" : "☀️";
}

// --- Inicialización al cargar la página ---
window.addEventListener("DOMContentLoaded", () => {
  // Restaurar tema
  const temaGuardado = localStorage.getItem("snm_tema") || "light";
  document.documentElement.setAttribute("data-tema", temaGuardado);
  const icon = document.getElementById("themeIcon");
  if (icon) icon.textContent = temaGuardado === "light" ? "🌙" : "☀️";

  // Event Listeners
  document.getElementById("themeToggleBtn")?.addEventListener("click", toggleTema);
  document.getElementById("tabAssistantMode")?.addEventListener("click", () => setModo("asistente"));
  document.getElementById("tabWorkbenchMode")?.addEventListener("click", () => setModo("workbench"));

  document.getElementById("transportSelect")?.addEventListener("change", (e) => {
    const isHttp = e.target.value === "http";
    const httpGroup = document.getElementById("httpIpGroup");
    if (httpGroup) httpGroup.style.display = isHttp ? "flex" : "none";
  });

  // Exportar funciones globales para onclick en el HTML
  window.irAlPaso = irAlPaso;
  window.seleccionarRol = seleccionarRol;
  window.actualizarConfiguracion = actualizarConfiguracion;
  window.descargarYamlDeseado = descargarYamlDeseado;
  window.copiarEnlaceQR = copiarEnlaceQR;
  window.copiarComandosCli = copiarComandosCli;
  window.conectarDispositivo = conectarDispositivo;
  window.desconectarDispositivo = desconectarDispositivo;
  window.limpiarLog = () => {
    const t = document.getElementById("logTextarea");
    if (t) t.value = "";
  };

  // Inicializar configuración y QR
  actualizarConfiguracion();
  logActividad("Configurador de Andalucía Mesh iniciado con preset SFNarrow.");
});
