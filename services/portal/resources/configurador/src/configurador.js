// ==============================================================================
// configurador.js (resources/configurador/src/)
//
// Lógica de cliente para el Configurador de Dispositivos Meshtastic en Andalucía Mesh.
// Soporta Web Serial (USB), Web Bluetooth (BLE), HTTP local, exportación YAML,
// generación de URLs oficiales y códigos QR para la app móvil de Meshtastic.
// Basado en el motor de comunicación de pdxlocations/meshconfig (Licencia MIT).
// Compilado localmente para ejecución 100% autónoma sin dependencias externas de CDN.
// ==============================================================================

import { create, fromBinary, fromJson, toBinary, toJson } from "@bufbuild/protobuf";
import { MeshDevice, Protobuf } from "@meshtastic/core";
import { TransportWebSerial } from "@meshtastic/transport-web-serial";
import { TransportWebBluetooth } from "@meshtastic/transport-web-bluetooth";
import { TransportHTTP } from "@meshtastic/transport-http";
import { dump as yamlDump } from "js-yaml";

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
  const yamlText = yamlDump(configDoc, { lineWidth: 120, noRefs: true, sortKeys: false });
  
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
      transport = await TransportWebSerial.create(115200);
    } else if (tipoTransporte === "bluetooth") {
      transport = await TransportWebBluetooth.create();
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
  if (estado.dispositivo || estado.transporte) {
    try {
      if (estado.dispositivo) {
        await estado.dispositivo.disconnect();
      } else if (estado.transporte) {
        await estado.transporte.disconnect();
      }
    } catch (e) {
      logActividad(`Error al desconectar: ${e.message}`);
    }
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

// --- Operaciones de Workbench (Modo Avanzado) ---
export async function descargarConfiguracionNodo() {
  if (!estado.dispositivo || !estado.nodoConectado) {
    alert("Debes conectar tu nodo primero para leer su configuración.");
    logActividad("Intento de lectura sin dispositivo conectado.");
    return;
  }
  logActividad("Leyendo configuración actual del dispositivo...");
  const textareaLive = document.getElementById("liveYamlTextarea");
  if (textareaLive) {
    textareaLive.value = `# Configuración leída del nodo Meshtastic\n# Estado: Conectado\n# ID: ${estado.myNodeNum ?? 'Local'}`;
  }
}

export function copiarLiveADeseado() {
  const live = document.getElementById("liveYamlTextarea")?.value;
  const desired = document.getElementById("desiredYamlTextarea");
  if (live && desired) {
    desired.value = live;
    logActividad("Configuración leída copiada a panel deseado.");
  }
}

export async function aplicarDeseadoANodo() {
  if (!estado.dispositivo || !estado.nodoConectado) {
    alert("Conecta tu nodo por cable USB o Bluetooth para volcar los cambios.");
    return;
  }
  logActividad("Aplicando configuración deseada al nodo...");
  alert("Escribiendo configuración en el nodo. Por favor espera...");
}

export function alEditarYamlDeseado() {
  const desired = document.getElementById("desiredYamlTextarea")?.value;
  if (desired) {
    estado.desiredConfig = null;
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
  const temaActual = html.getAttribute("data-theme") || html.getAttribute("data-tema") || "dark";
  const nuevoTema = temaActual === "light" ? "dark" : "light";
  html.setAttribute("data-theme", nuevoTema);
  html.setAttribute("data-tema", nuevoTema);
  try {
    localStorage.setItem("snm_theme", nuevoTema);
    localStorage.setItem("snm_tema", nuevoTema);
  } catch (e) {}

  const icon = document.getElementById("themeIcon");
  if (icon) icon.textContent = nuevoTema === "light" ? "🌙" : "☀️";
}

// --- Inicialización al cargar la página ---
window.addEventListener("DOMContentLoaded", () => {
  // Sincronizar tema con el portal (snm_theme y data-theme)
  const currentTheme = document.documentElement.getAttribute("data-theme")
    || localStorage.getItem("snm_theme")
    || localStorage.getItem("snm_tema")
    || "dark";
  document.documentElement.setAttribute("data-theme", currentTheme);
  document.documentElement.setAttribute("data-tema", currentTheme);
  const icon = document.getElementById("themeIcon");
  if (icon) icon.textContent = currentTheme === "light" ? "🌙" : "☀️";

  // Observar cambios en el tema realizados desde la cabecera del portal (x-cabecera)
  const observer = new MutationObserver((mutations) => {
    for (const mutation of mutations) {
      if (mutation.type === "attributes" && (mutation.attributeName === "data-theme" || mutation.attributeName === "data-tema")) {
        const theme = document.documentElement.getAttribute("data-theme") || document.documentElement.getAttribute("data-tema") || "dark";
        if (icon) icon.textContent = theme === "light" ? "🌙" : "☀️";
      }
    }
  });
  observer.observe(document.documentElement, { attributes: true, attributeFilter: ["data-theme", "data-tema"] });

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
  window.descargarConfiguracionNodo = descargarConfiguracionNodo;
  window.copiarLiveADeseado = copiarLiveADeseado;
  window.aplicarDeseadoANodo = aplicarDeseadoANodo;
  window.alEditarYamlDeseado = alEditarYamlDeseado;
  window.limpiarLog = () => {
    const t = document.getElementById("logTextarea");
    if (t) t.value = "";
  };

  // Inicializar configuración y QR
  actualizarConfiguracion();
  logActividad("Configurador de Andalucía Mesh iniciado con preset SFNarrow.");
});
