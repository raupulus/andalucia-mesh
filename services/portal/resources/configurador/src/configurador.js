// ==============================================================================
// configurador.js (resources/configurador/src/)
//
// Lógica de cliente para el Configurador de Dispositivos Meshtastic en Andalucía Mesh.
// Soporta Web Serial (USB), Web Bluetooth (BLE), HTTP local, exportación YAML,
// generación de URLs oficiales y códigos QR para la app móvil de Meshtastic.
// Basado en el motor de comunicación de pdxlocations/meshconfig (Licencia GNU GPLv3).
// Compilado localmente para ejecución 100% autónoma sin dependencias externas de CDN.
// ==============================================================================

import { create, fromBinary, fromJson, toBinary, toJson } from "@bufbuild/protobuf";
import { MeshDevice, Protobuf } from "@meshtastic/core";
import { TransportWebSerial } from "@meshtastic/transport-web-serial";
import { TransportWebBluetooth } from "@meshtastic/transport-web-bluetooth";
import { TransportHTTP } from "@meshtastic/transport-http";
import { dump as yamlDump, load as jsYamlLoad } from "js-yaml";

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
  ownerName: "",
  ownerShort: "",
  isUnmessagable: false,
  sessionPasskey: null,
  configSections: {},
  moduleConfigSections: {},
  channelMap: new Map(),
  liveConfig: null,
  desiredConfig: null,
  qrInstance: null,
  pendingAdminResponses: []
};

// Mapa de nodos registrados recibidos por NodeInfo (para indexar la identidad de cada nodo por su número)
const nodeInfoMap = new Map();

/**
 * Normaliza los enums numéricos de Protobuf a sus representaciones textuales canónicas
 * para que la comparación (diff) entre la configuración leída del nodo y la deseada
 * sea 100% precisa y no arroje falsos positivos.
 */
function normalizarConfigParaYaml(rawConfig, rawModuleConfig, rawChannels) {
  const config = JSON.parse(JSON.stringify(rawConfig || {}));
  const moduleConfig = JSON.parse(JSON.stringify(rawModuleConfig || {}));

  // Enums en sección device
  if (config.device) {
    if (typeof config.device.role === "number" && Protobuf.Config.Config_DeviceConfig_Role[config.device.role]) {
      config.device.role = Protobuf.Config.Config_DeviceConfig_Role[config.device.role];
    }
    if (typeof config.device.rebroadcastMode === "number" && Protobuf.Config.Config_DeviceConfig_RebroadcastMode[config.device.rebroadcastMode]) {
      config.device.rebroadcastMode = Protobuf.Config.Config_DeviceConfig_RebroadcastMode[config.device.rebroadcastMode];
    }
  }

  // Enums en sección lora
  if (config.lora) {
    if (typeof config.lora.region === "number" && Protobuf.Config.Config_LoRaConfig_RegionCode[config.lora.region]) {
      config.lora.region = Protobuf.Config.Config_LoRaConfig_RegionCode[config.lora.region];
    }
    if (typeof config.lora.modemPreset === "number" && Protobuf.Config.Config_LoRaConfig_ModemPreset[config.lora.modemPreset]) {
      config.lora.modemPreset = Protobuf.Config.Config_LoRaConfig_ModemPreset[config.lora.modemPreset];
    }
  }

  // Normalizar lista de canales
  let channels = undefined;
  if (Array.isArray(rawChannels) && rawChannels.length > 0) {
    channels = rawChannels.map((ch) => {
      const chCopy = JSON.parse(JSON.stringify(ch || {}));
      let roleName = chCopy.role;
      if (typeof roleName === "number" && Protobuf.Channel.Channel_Role[roleName]) {
        roleName = Protobuf.Channel.Channel_Role[roleName];
      }
      return {
        index: chCopy.index ?? 0,
        role: roleName ?? "DISABLED",
        settings: chCopy.settings || {}
      };
    });
  }

  return { config, moduleConfig, channels };
}

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
  const posSelectVal = document.getElementById("posicionSelect")?.value;
  const positionSecs = (posSelectVal !== undefined && posSelectVal !== "")
    ? Number(posSelectVal)
    : (esClientMute ? 21600 : 259200); // 6h para móvil / 72h para fijo por defecto
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

  const mapActivo = mqttActivo && Boolean(document.getElementById("chkMqttMap")?.checked);

  const opcionales = [
    { id: "chkIberia", name: "Iberia" },
    { id: "chkAndalucia", name: "Andalucia" },
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
          downlinkEnabled: true,
          moduleSettings: { positionPrecision: 15 }
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

  const moduleConfig = {
    telemetry: {
      deviceUpdateInterval: telemetriaSecs
    }
  };

  // Solo incluir bloque MQTT con nuestra URL si el usuario marca explícitamente el check de colaborar
  if (mqttActivo) {
    const mqttCfg = {
      enabled: true,
      address: "mqtt.desdechipiona.es",
      username: "meshdev",
      password: "large4cats",
      root: "msh/EU_868",
      encryptionEnabled: true,
      tlsEnabled: true,
      proxyToClientEnabled: true,
      mapReportingEnabled: mapActivo
    };

    if (mapActivo) {
      mqttCfg.mapReportSettings = {
        publishIntervalSecs: 259200,
        positionPrecision: 14,
        shouldReportLocation: true
      };
    }

    moduleConfig.mqtt = mqttCfg;
  }

  const configDoc = {
    owner: longName,
    owner_short: shortName,
    is_unmessagable: false,
    config: {
      device: {
        role: estado.rolSeleccionado,
        nodeInfoBroadcastSecs: nodeInfoSecs,
        rebroadcastMode: esClientMute ? "CORE_PORTNUMS_ONLY" : "ALL",
        disableTripleClick: true,
        tzdef: "GMT-1GMT,M3.5.0,M10.5.0/3"
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
        sx126xRxBoostedGain: true,
        ignoreMqtt: mqttActivo ? true : false,
        configOkToMqtt: mapActivo ? true : false
      },
      position: {
        positionBroadcastSmartEnabled: false,
        positionBroadcastSecs: positionSecs,
        positionFlags: esClientMute ? 0 : 137,
        fixedPosition: !esClientMute
      },
      security: {
        serialEnabled: true
      }
    },
    module_config: moduleConfig,
    channels: channels
  };

  estado.desiredConfig = configDoc;
  const yamlText = yamlDump(configDoc, { lineWidth: 120, noRefs: true, sortKeys: false });
  
  const textareaDesired = document.getElementById("desiredYamlTextarea");
  if (textareaDesired) textareaDesired.value = yamlText;

  return { configDoc, yamlText };
}

// --- Generador de URL y Código QR oficial de Meshtastic ---
// Codifica en Base64Url un protobuf ChannelSet conforme a apponly.proto
export function generarMeshtasticUrl(doc) {
  const channelSettingsList = [];
  if (Array.isArray(doc.channels)) {
    for (const ch of doc.channels) {
      if (ch.settings && ch.settings.name) {
        const isPrimary = ch.role === "PRIMARY";
        const downlink = isPrimary ? (doc.config.device.role !== "CLIENT_MUTE") : (ch.settings.downlinkEnabled ?? true);
        const uplink = ch.settings.uplinkEnabled ?? true;

        let pskBytes = new Uint8Array([1]);
        if (typeof ch.settings.psk === "string" && ch.settings.psk !== "AQ==" && ch.settings.psk !== "") {
          try {
            const raw = atob(ch.settings.psk);
            pskBytes = new Uint8Array(raw.length);
            for (let i = 0; i < raw.length; i++) pskBytes[i] = raw.charCodeAt(i);
          } catch (_) {
            pskBytes = new Uint8Array([1]);
          }
        }

        channelSettingsList.push(create(Protobuf.Channel.ChannelSettingsSchema, {
          name: ch.settings.name,
          psk: pskBytes,
          uplinkEnabled: uplink,
          downlinkEnabled: downlink,
          moduleSettings: ch.settings.moduleSettings ? {
            positionPrecision: ch.settings.moduleSettings.positionPrecision ?? 15
          } : undefined
        }));
      }
    }
  }

  const lora = doc.config.lora;
  const channelSet = create(Protobuf.AppOnly.ChannelSetSchema, {
    settings: channelSettingsList,
    loraConfig: {
      usePreset: false,
      bandwidth: Number(lora.bandwidth) || 62,
      spreadFactor: Number(lora.spreadFactor) || 7,
      codingRate: Number(lora.codingRate) || 5,
      region: 3, // EU_868
      hopLimit: Number(lora.hopLimit) || 4,
      txPower: Number(lora.txPower) || 27,
      channelNum: Number(lora.channelNum) || 4
    }
  });

  const bin = toBinary(Protobuf.AppOnly.ChannelSetSchema, channelSet);
  let binary = "";
  for (let i = 0; i < bin.byteLength; i++) {
    binary += String.fromCharCode(bin[i]);
  }
  const b64 = btoa(binary).replaceAll("+", "-").replaceAll("/", "_").replaceAll("=", "");
  return `https://meshtastic.org/e/#${b64}`;
}

export function actualizarConfiguracion() {
  const chkMqtt = document.getElementById("chkMqtt");
  const chkMqttMap = document.getElementById("chkMqttMap");
  const mapWrapper = document.getElementById("mqttMapOptionWrapper");
  if (chkMqtt && mapWrapper) {
    if (chkMqtt.checked) {
      mapWrapper.style.display = "block";
    } else {
      mapWrapper.style.display = "none";
      if (chkMqttMap) chkMqttMap.checked = false;
    }
  }

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
  try {
    const shareUrl = generarMeshtasticUrl(configDoc);
    const inputUrl = document.getElementById("qrShareUrl");
    if (inputUrl) inputUrl.value = shareUrl;

    const qrContainer = document.getElementById("qrCanvasContainer");
    if (qrContainer && typeof window.QRCode === "function") {
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

    // Actualizar resumen legible de canales en el paso 4
    const channelsListEl = document.getElementById("channelsSummaryList");
    if (channelsListEl && Array.isArray(configDoc.channels)) {
      const activeChannels = configDoc.channels.filter(ch => ch.settings && ch.settings.name);
      let html = "";
      activeChannels.forEach(ch => {
        const isPrimary = ch.role === "PRIMARY";
        const tag = isPrimary ? "Canal 0 · Principal" : `Canal ${ch.index} · Secundario`;
        const badgeBg = isPrimary ? "var(--color-acento)" : "var(--color-superficie)";
        const badgeColor = isPrimary ? "var(--color-sobre-acento)" : "var(--color-texto-1)";
        html += `
          <div style="display: flex; align-items: center; justify-content: space-between; background: ${badgeBg}; color: ${badgeColor}; padding: 0.35rem 0.6rem; border-radius: 4px; border: 1px solid var(--color-borde); font-size: 0.8rem;">
            <span style="font-weight: 700;">${escapeHtml(ch.settings.name)}</span>
            <span style="font-size: 0.75rem; opacity: 0.85;">${tag}</span>
          </div>
        `;
      });
      channelsListEl.innerHTML = html;
    }
  } catch (err) {
    console.error("Error al generar URL o código QR:", err);
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
      panel.style.display = (i === numPaso) ? "block" : "none";
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

  const posSelect = document.getElementById("posicionSelect");
  if (posSelect) {
    posSelect.value = rol === "CLIENT_MUTE" ? "21600" : "259200";
  }

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
  const esClientMute = dev.role === "CLIENT_MUTE";
  const telemetriaSecs = Number(document.getElementById("telemetriaSelect")?.value || 0);

  const comandos = [
    `# Configuración oficial Andalucía Mesh (SFNarrow)`,
    `meshtastic --set-owner "${configDoc.owner}" --set-owner-short "${configDoc.owner_short}"`,
    `meshtastic --set lora.region ${lora.region} --set lora.use_preset false`,
    `meshtastic --set lora.bandwidth ${lora.bandwidth} --set lora.spread_factor ${lora.spreadFactor} --set lora.coding_rate ${lora.codingRate}`,
    `meshtastic --set lora.channel_num ${lora.channelNum} --set lora.hop_limit ${lora.hopLimit} --set lora.tx_power ${lora.txPower}`,
    `meshtastic --set device.role ${dev.role} --set device.node_info_broadcast_secs ${dev.nodeInfoBroadcastSecs} --set device.disable_triple_click true --set device.tzdef "${dev.tzdef}"`,
    `meshtastic --set position.position_broadcast_smart_enabled false --set position.position_broadcast_secs ${pos.positionBroadcastSecs} --set position.fixed_position ${pos.fixedPosition || false}`,
    `meshtastic --set telemetry.device_update_interval ${telemetriaSecs}`,
    `meshtastic --ch-set name "SFNarrow" --ch-set psk "AQ==" --ch-set uplink_enabled true --ch-set downlink_enabled ${!esClientMute} --ch-index 0`
  ];

  // Canales secundarios añadidos
  if (configDoc.channels && configDoc.channels.length > 1) {
    for (let i = 1; i < configDoc.channels.length; i++) {
      const ch = configDoc.channels[i];
      if (ch && ch.settings && ch.settings.name) {
        comandos.push(`meshtastic --ch-set name "${ch.settings.name}" --ch-set psk "${ch.settings.psk || 'AQ=='}" --ch-set uplink_enabled true --ch-index ${i}`);
      }
    }
  }

  // Configuración MQTT comunitaria solo si el usuario ha marcado el check de colaborar
  if (configDoc.module_config?.mqtt?.enabled) {
    const mqtt = configDoc.module_config.mqtt;
    comandos.push(`meshtastic --set mqtt.enabled true --set mqtt.address "${mqtt.address}" --set mqtt.username "${mqtt.username}" --set mqtt.password "${mqtt.password}" --set mqtt.root "${mqtt.root}" --set mqtt.encryption_enabled true --set mqtt.tls_enabled true`);
    comandos.push(`meshtastic --set lora.ignore_mqtt true`);

    if (mqtt.mapReportingEnabled) {
      comandos.push(`meshtastic --set mqtt.map_reporting_enabled true --set mqtt.map_report_settings.publish_interval_secs 259200 --set mqtt.map_report_settings.position_precision 14 --set mqtt.map_report_settings.should_report_location true`);
      comandos.push(`meshtastic --set lora.config_ok_to_mqtt true`);
    }
  }

  const textoComandos = comandos.join("\n");

  navigator.clipboard.writeText(textoComandos).then(() => {
    alert("Comandos CLI de Meshtastic copiados al portapapeles.");
    logActividad("Comandos CLI copiados al portapapeles.");
  });
}

// --- Sincronización de interfaz de conexión ---
function actualizarUiEstadoConexion(conectado) {
  estado.nodoConectado = conectado;

  const statusPill = document.getElementById("statusPill");
  if (statusPill) {
    statusPill.textContent = conectado ? "⚡ Conectado" : "🔌 Desconectado";
    statusPill.style.background = conectado ? "var(--color-correcto-fondo)" : "";
    statusPill.style.color = conectado ? "var(--color-correcto-texto)" : "";
  }

  const wbStatusPill = document.getElementById("workbenchStatusPill");
  if (wbStatusPill) {
    wbStatusPill.textContent = conectado ? "⚡ Conectado" : "🔌 Desconectado";
    wbStatusPill.style.background = conectado ? "var(--color-correcto-fondo)" : "";
    wbStatusPill.style.color = conectado ? "var(--color-correcto-texto)" : "";
  }

  const btnConnectDirect = document.getElementById("btnConnectDirect");
  const btnDisconnectDirect = document.getElementById("btnDisconnectDirect");
  const connectedNodeCard = document.getElementById("connectedNodeCard");
  const btnProgramDirect = document.getElementById("btnProgramDirect");
  const nodeNameEl = document.getElementById("connectedNodeName");
  const nodeIdEl = document.getElementById("connectedNodeId");
  const feedbackEl = document.getElementById("directStatusFeedback");

  if (btnConnectDirect) {
    btnConnectDirect.disabled = conectado;
    btnConnectDirect.textContent = conectado ? "✓ Conectado" : "🔌 Conectar al Nodo";
  }
  if (btnDisconnectDirect) btnDisconnectDirect.disabled = !conectado;

  if (connectedNodeCard) {
    connectedNodeCard.style.display = conectado ? "flex" : "none";
  }
  if (btnProgramDirect) {
    btnProgramDirect.disabled = !conectado;
  }

  if (conectado) {
    const hex = estado.myNodeNum !== null ? "!" + estado.myNodeNum.toString(16).padStart(8, "0") : "!desconocido";
    if (nodeNameEl) nodeNameEl.textContent = estado.ownerName || "Nodo Meshtastic";
    if (nodeIdEl) nodeIdEl.textContent = hex;
    if (feedbackEl && !feedbackEl.dataset.isProgramming) {
      feedbackEl.innerHTML = `💡 Nodo conectado (<strong>${escapeHtml(estado.ownerName || "Meshtastic")}</strong>). Pulsa en <strong>🚀 Programar Nodo Ahora</strong> para volcar los ajustes de Andalucía Mesh y reiniciar el nodo automáticamente.`;
    }
  } else {
    if (feedbackEl && !feedbackEl.dataset.isProgramming) {
      feedbackEl.innerHTML = `💡 <em>Conecta tu nodo por cable USB Serial o Bluetooth para volcar los ajustes. Ningún parámetro se alterará en el dispositivo hasta que pulses en <strong>Programar Nodo Ahora</strong>. El nodo se reiniciará automáticamente tras aplicar los cambios.</em>`;
    }
  }

  const btnConnectWb = document.getElementById("btnConnectWorkbench");
  const btnDisconnectWb = document.getElementById("btnDisconnectWorkbench");
  const btnDownloadLive = document.getElementById("btnDownloadLive");
  const btnDownloadLiveHeader = document.getElementById("btnDownloadLiveHeader");
  const btnUploadConfig = document.getElementById("btnUploadConfig");

  if (btnConnectWb) btnConnectWb.disabled = conectado;
  if (btnDisconnectWb) btnDisconnectWb.disabled = !conectado;
  if (btnDownloadLive) btnDownloadLive.disabled = !conectado;
  if (btnDownloadLiveHeader) btnDownloadLiveHeader.disabled = !conectado;
  if (btnUploadConfig) btnUploadConfig.disabled = !conectado;
}

// --- Regenerar vista previa YAML del nodo conectado ---
function regenerarYamlLive() {
  const channelsList = Array.from(estado.channelMap.values()).sort((a, b) => (a.index ?? 0) - (b.index ?? 0));

  const user = (estado.myNodeNum !== null) ? nodeInfoMap.get(estado.myNodeNum) : null;
  const ownerName = estado.ownerName || user?.longName || "Nodo Meshtastic";
  const ownerShort = estado.ownerShort || user?.shortName || "MESH";
  const isUnmessagable = estado.isUnmessagable !== undefined ? estado.isUnmessagable : Boolean(user?.isUnmessagable);

  const { config, moduleConfig, channels } = normalizarConfigParaYaml(
    estado.configSections,
    estado.moduleConfigSections,
    channelsList
  );

  const liveDoc = {
    owner: ownerName,
    owner_short: ownerShort,
    is_unmessagable: isUnmessagable,
    config: config,
    module_config: moduleConfig,
    channels: channels
  };

  estado.liveConfig = liveDoc;
  try {
    const yamlStr = yamlDump(liveDoc, { lineWidth: 120, noRefs: true, sortKeys: false });
    const liveTextarea = document.getElementById("liveYamlTextarea");
    if (liveTextarea) {
      liveTextarea.value = yamlStr;
    }
    actualizarDiff();
  } catch (e) {
    console.warn("Error serializando live config a YAML:", e);
  }
}

// --- Comparador de Diferencias (Diff) visual ---
export function actualizarDiff() {
  const liveText = document.getElementById("liveYamlTextarea")?.value.trim() || "";
  const desiredText = document.getElementById("desiredYamlTextarea")?.value.trim() || "";
  const badge = document.getElementById("diffBadge");
  const container = document.getElementById("diffOutputContainer");

  if (!badge || !container) return;

  if (!liveText) {
    badge.textContent = "Sin comparación activa";
    badge.className = "badge-tag";
    badge.style.background = "";
    badge.style.color = "";
    container.innerHTML = "<em>Conéctate a tu nodo y lee su configuración para ver las diferencias exactas respecto al estándar SFNarrow.</em>";
    return;
  }

  if (liveText === desiredText) {
    badge.textContent = "✓ Idéntica (Sin cambios)";
    badge.className = "badge-tag";
    badge.style.background = "var(--color-correcto-fondo)";
    badge.style.color = "var(--color-correcto-texto)";
    container.innerHTML = '<div style="color: var(--color-correcto-texto); padding: 0.5rem 0;">✓ La configuración actual del nodo coincide con la configuración deseada.</div>';
    return;
  }

  const liveLines = liveText.split("\n");
  const desiredLines = desiredText.split("\n");
  let diffHtml = '<div style="font-family: var(--fuente-mono); font-size: 0.85rem; line-height: 1.5; max-height: 320px; overflow-y: auto; background: var(--color-superficie-sutil); border: 1px solid var(--color-borde); border-radius: var(--radio-sm); padding: 0.75rem;">';
  let diffCount = 0;

  const maxLines = Math.max(liveLines.length, desiredLines.length);
  for (let i = 0; i < maxLines; i++) {
    const lLine = liveLines[i];
    const dLine = desiredLines[i];
    if (lLine !== dLine) {
      diffCount++;
      if (lLine !== undefined) {
        diffHtml += `<div style="background: rgba(220, 38, 38, 0.15); color: #ef4444; padding: 1px 4px; border-radius: 2px;">- ${escapeHtml(lLine)}</div>`;
      }
      if (dLine !== undefined) {
        diffHtml += `<div style="background: rgba(22, 163, 74, 0.15); color: #22c55e; padding: 1px 4px; border-radius: 2px;">+ ${escapeHtml(dLine)}</div>`;
      }
    } else {
      diffHtml += `<div style="color: var(--color-texto-2); padding: 1px 4px;">  ${escapeHtml(lLine || "")}</div>`;
    }
  }
  diffHtml += "</div>";

  badge.textContent = `⚠️ ${diffCount} diferencia${diffCount > 1 ? "s" : ""}`;
  badge.className = "badge-tag badge-tag-aviso";
  badge.style.background = "";
  badge.style.color = "";
  container.innerHTML = diffHtml;
}

function escapeHtml(str) {
  return String(str).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
}

// --- Conexión directa al nodo y programación ---
export async function conectarDispositivo(origen = "assistant") {
  const isWorkbench = origen === "workbench" || estado.modo === "workbench";
  const selectId = isWorkbench ? "transportSelectWorkbench" : "transportSelect";
  const tipoTransporte = document.getElementById(selectId)?.value || "serial";

  if (tipoTransporte === "serial" && !("serial" in navigator)) {
    const msg = "Web Serial API no está soportada en este navegador. Para conectar directamente por cable USB, utiliza Google Chrome, Microsoft Edge, Brave u Opera en tu ordenador.";
    alert(msg);
    logActividad(`Error de compatibilidad: ${msg}`);
    return;
  }

  if (tipoTransporte === "bluetooth" && !("bluetooth" in navigator)) {
    const msg = "Web Bluetooth API no está soportada en este navegador. Utiliza Google Chrome o Microsoft Edge.";
    alert(msg);
    logActividad(`Error de compatibilidad: ${msg}`);
    return;
  }

  const btnConnect = document.getElementById(isWorkbench ? "btnConnectWorkbench" : "btnConnectDirect");
  if (btnConnect) btnConnect.disabled = true;

  try {
    logActividad(`Iniciando conexión directa por ${tipoTransporte.toUpperCase()}...`);

    let transport;
    if (tipoTransporte === "serial") {
      transport = await TransportWebSerial.create(115200);
    } else if (tipoTransporte === "bluetooth") {
      transport = await TransportWebBluetooth.create();
    } else if (tipoTransporte === "http") {
      const inputId = isWorkbench ? "httpIpInputWorkbench" : "httpIpInput";
      const host = document.getElementById(inputId)?.value.trim() || "meshtastic.local";
      transport = new TransportHTTP(host, false);
    }

    estado.transporte = transport;
    const device = new MeshDevice(transport);
    estado.dispositivo = device;
    estado.nodoConectado = true;

    // Limpiar caché de secciones previas y mapa de nodos
    nodeInfoMap.clear();
    estado.myNodeNum = null;
    estado.myNodeInfo = null;
    estado.ownerName = "";
    estado.ownerShort = "";
    estado.isUnmessagable = false;
    estado.configSections = {};
    estado.moduleConfigSections = {};
    estado.channelMap.clear();

    // Suscripción a eventos del dispositivo
    device.events.onDeviceStatus.subscribe((status) => {
      logActividad(`Estado del enlace local: ${status}`);
      if (status === 2 || status === "disconnected" || status === "DeviceDisconnected") {
        actualizarUiEstadoConexion(false);
      }
    });

    device.events.onMyNodeInfo.subscribe((info) => {
      if (info) {
        estado.myNodeNum = info.myNodeNum >>> 0;
        estado.myNodeInfo = info;
        const hex = estado.myNodeNum.toString(16).padStart(8, "0");
        logActividad(`Nodo local identificado: !${hex}`);

        // Si ya habíamos recibido el nodeInfo correspondiente a este nodo propio
        if (nodeInfoMap.has(estado.myNodeNum)) {
          const u = nodeInfoMap.get(estado.myNodeNum);
          if (u?.longName) estado.ownerName = u.longName;
          if (u?.shortName) estado.ownerShort = u.shortName;
          if (u?.isUnmessagable !== undefined) estado.isUnmessagable = Boolean(u.isUnmessagable);
        }
        actualizarUiEstadoConexion(true);
        regenerarYamlLive();
      }
    });

    device.events.onNodeInfoPacket.subscribe((nodeInfo) => {
      if (!nodeInfo) return;
      const num = nodeInfo.num !== undefined ? (nodeInfo.num >>> 0) : null;
      if (num !== null && nodeInfo.user) {
        nodeInfoMap.set(num, nodeInfo.user);
        // Solo actualizar identidad si coincide exactamente con el nodo propio conectado
        if (estado.myNodeNum !== null && num === estado.myNodeNum) {
          if (nodeInfo.user.longName) estado.ownerName = nodeInfo.user.longName;
          if (nodeInfo.user.shortName) estado.ownerShort = nodeInfo.user.shortName;
          if (nodeInfo.user.isUnmessagable !== undefined) estado.isUnmessagable = Boolean(nodeInfo.user.isUnmessagable);
          logActividad(`Identidad del nodo propio confirmada: ${estado.ownerName} (${estado.ownerShort})`);
          regenerarYamlLive();
        }
      }
    });

    device.events.onUserPacket.subscribe((packet) => {
      if (!packet?.data) return;
      const fromNum = packet.from !== undefined ? (packet.from >>> 0) : null;
      // Descartar paquetes de otros nodos de la malla o del volcado de la NodeDB
      const esPropio = (fromNum === 0) || (estado.myNodeNum !== null && fromNum === estado.myNodeNum);
      if (!esPropio && (fromNum !== null || estado.myNodeNum !== null)) {
        return;
      }
      if (packet.data.longName) estado.ownerName = packet.data.longName;
      if (packet.data.shortName) estado.ownerShort = packet.data.shortName;
      if (packet.data.isUnmessagable !== undefined) estado.isUnmessagable = Boolean(packet.data.isUnmessagable);
      regenerarYamlLive();
    });

    device.events.onConfigPacket.subscribe((config) => {
      if (config?.payloadVariant?.case && config.payloadVariant.value) {
        const sec = config.payloadVariant.case;
        try {
          const json = toJson(Protobuf.Config.ConfigSchema, config);
          if (json && json[sec]) {
            estado.configSections[sec] = json[sec];
          }
        } catch (e) {
          console.warn(`Error parseando config.${sec}:`, e);
        }
        regenerarYamlLive();
      }
    });

    device.events.onModuleConfigPacket.subscribe((moduleConfig) => {
      if (moduleConfig?.payloadVariant?.case && moduleConfig.payloadVariant.value) {
        const sec = moduleConfig.payloadVariant.case;
        try {
          const json = toJson(Protobuf.ModuleConfig.ModuleConfigSchema, moduleConfig);
          if (json && json[sec]) {
            estado.moduleConfigSections[sec] = json[sec];
          }
        } catch (e) {
          console.warn(`Error parseando module_config.${sec}:`, e);
        }
        regenerarYamlLive();
      }
    });

    device.events.onChannelPacket.subscribe((channel) => {
      if (channel) {
        try {
          const json = toJson(Protobuf.Channel.ChannelSchema, channel);
          if (json && json.index !== undefined) {
            estado.channelMap.set(json.index, json);
          }
        } catch (e) {
          console.warn("Error parseando channel:", e);
        }
        regenerarYamlLive();
      }
    });

    actualizarUiEstadoConexion(true);
    logActividad("✓ Conexión establecida con éxito con el nodo Meshtastic.");
    logActividad("Solicitando configuración al dispositivo...");

    // Handshake inicial para volcar ajustes completos
    try {
      await device.configure();
    } catch (err) {
      console.warn("Aviso en device.configure():", err);
    }

    try {
      await device.getOwner();
    } catch (_) {}

    for (let i = 0; i < 8; i++) {
      try {
        await device.getChannel(i);
      } catch (_) {}
    }

    actualizarUiEstadoConexion(true);
    logActividad("✓ Parámetros iniciales leídos. Dispositivo listo para configurar.");
  } catch (e) {
    actualizarUiEstadoConexion(false);
    logActividad(`Error de conexión: ${e.message}`);
    alert(`No se pudo conectar al dispositivo: ${e.message}`);
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
      logActividad(`Aviso al desconectar: ${e.message}`);
    }
  }
  estado.transporte = null;
  estado.dispositivo = null;
  estado.nodoConectado = false;
  actualizarUiEstadoConexion(false);
  logActividad("Dispositivo desconectado.");
}

// --- Operaciones de Workbench (Modo Avanzado) ---
export async function descargarConfiguracionNodo() {
  if (!estado.dispositivo || !estado.nodoConectado) {
    alert("Debes conectar tu nodo primero por cable USB Serial o Bluetooth para leer su configuración.");
    logActividad("Intento de lectura sin dispositivo conectado.");
    return;
  }
  logActividad("Solicitando parámetros actualizados al dispositivo...");
  try {
    await estado.dispositivo.configure();
    await estado.dispositivo.getOwner();
    for (let i = 0; i < 8; i++) {
      await estado.dispositivo.getChannel(i);
    }
    if (estado.myNodeNum !== null && nodeInfoMap.has(estado.myNodeNum)) {
      const u = nodeInfoMap.get(estado.myNodeNum);
      if (u?.longName) estado.ownerName = u.longName;
      if (u?.shortName) estado.ownerShort = u.shortName;
      if (u?.isUnmessagable !== undefined) estado.isUnmessagable = Boolean(u.isUnmessagable);
    }
    regenerarYamlLive();
    logActividad("Configuración leída y volcada en el panel actual.");
  } catch (err) {
    logActividad(`Error solicitando configuración: ${err.message}`);
    alert(`Error al leer del nodo: ${err.message}`);
  }
}

export function copiarLiveADeseado() {
  const live = document.getElementById("liveYamlTextarea")?.value;
  const desired = document.getElementById("desiredYamlTextarea");
  if (live && desired) {
    desired.value = live;
    logActividad("Configuración leída copiada a panel deseado.");
    actualizarDiff();
  }
}

export async function aplicarDeseadoANodo() {
  if (!estado.dispositivo || !estado.nodoConectado) {
    alert("Conecta tu nodo por cable USB Serial o Bluetooth para volcar los cambios.");
    return;
  }

  const desiredYaml = document.getElementById("desiredYamlTextarea")?.value.trim();
  if (!desiredYaml) {
    alert("No hay configuración deseada para escribir en el nodo.");
    return;
  }

  let doc;
  try {
    doc = jsYamlLoad(desiredYaml);
  } catch (err) {
    alert(`Error de formato en el YAML deseado: ${err.message}`);
    return;
  }

  const btnUpload = document.getElementById("btnUploadConfig");
  if (btnUpload) btnUpload.disabled = true;

  try {
    logActividad("Escribiendo configuración deseada en el nodo...");
    try {
      await estado.dispositivo.beginEditSettings();
    } catch (_) {}
    await new Promise(r => setTimeout(r, 150));

    // 1. Identidad (Owner)
    if (doc.owner || doc.owner_short) {
      const user = create(Protobuf.Mesh.UserSchema, {
        longName: doc.owner || "MiNodo-Andalucia",
        shortName: (doc.owner_short || "AND1").slice(0, 4)
      });
      await estado.dispositivo.setOwner(user);
      logActividad("✓ Identidad (Owner) actualizada.");
      await new Promise(r => setTimeout(r, 200));
    }

    // 2. Secciones de configuración (Device, LoRa, Position)
    if (doc.config?.device) {
      const esMute = doc.config.device.role === "CLIENT_MUTE";
      const dev = create(Protobuf.Config.Config_DeviceConfigSchema, {
        role: esMute ? 1 : 0,
        rebroadcastMode: esMute ? 5 : 0,
        nodeInfoBroadcastSecs: doc.config.device.nodeInfoBroadcastSecs || 259200,
        disableTripleClick: Boolean(doc.config.device.disableTripleClick),
        tzdef: doc.config.device.tzdef || "GMT-1GMT,M3.5.0,M10.5.0/3"
      });
      const cfg = create(Protobuf.Config.ConfigSchema, {
        payloadVariant: { case: "device", value: dev }
      });
      await estado.dispositivo.setConfig(cfg);
      logActividad("✓ Parámetros de Dispositivo (Role / NodeInfo / TZ / Botón) enviados.");
      await new Promise(r => setTimeout(r, 200));
    }

    if (doc.config?.lora) {
      const lora = create(Protobuf.Config.Config_LoRaConfigSchema, {
        region: 3, // EU_868
        usePreset: Boolean(doc.config.lora.usePreset),
        bandwidth: Number(doc.config.lora.bandwidth) || 62,
        spreadFactor: Number(doc.config.lora.spreadFactor) || 7,
        codingRate: Number(doc.config.lora.codingRate) || 5,
        channelNum: Number(doc.config.lora.channelNum) || 4,
        hopLimit: Number(doc.config.lora.hopLimit) || 4,
        txPower: Number(doc.config.lora.txPower) || 27,
        txEnabled: true,
        sx126xRxBoostedGain: true,
        ignoreMqtt: Boolean(doc.config.lora.ignoreMqtt),
        configOkToMqtt: Boolean(doc.config.lora.configOkToMqtt)
      });
      const cfg = create(Protobuf.Config.ConfigSchema, {
        payloadVariant: { case: "lora", value: lora }
      });
      await estado.dispositivo.setConfig(cfg);
      logActividad("✓ Parámetros de Radio LoRa (SFNarrow EU_868) enviados.");
      await new Promise(r => setTimeout(r, 200));
    }

    if (doc.config?.position) {
      const pos = create(Protobuf.Config.Config_PositionConfigSchema, {
        positionBroadcastSmartEnabled: Boolean(doc.config.position.positionBroadcastSmartEnabled),
        positionBroadcastSecs: Number(doc.config.position.positionBroadcastSecs) || 21600,
        positionFlags: Number(doc.config.position.positionFlags) || 0,
        fixedPosition: Boolean(doc.config.position.fixedPosition)
      });
      const cfg = create(Protobuf.Config.ConfigSchema, {
        payloadVariant: { case: "position", value: pos }
      });
      await estado.dispositivo.setConfig(cfg);
      logActividad("✓ Parámetros de Posición enviados.");
      await new Promise(r => setTimeout(r, 200));
    }

    // 3. Módulos (Telemetry, MQTT)
    if (doc.module_config?.telemetry) {
      const tel = create(Protobuf.ModuleConfig.ModuleConfig_TelemetryConfigSchema, {
        deviceUpdateInterval: Number(doc.module_config.telemetry.deviceUpdateInterval) || 0
      });
      const mod = create(Protobuf.ModuleConfig.ModuleConfigSchema, {
        payloadVariant: { case: "telemetry", value: tel }
      });
      await estado.dispositivo.setModuleConfig(mod);
      logActividad("✓ Módulo de Telemetría enviado.");
      await new Promise(r => setTimeout(r, 200));
    }

    if (doc.module_config?.mqtt) {
      const mqttData = {
        enabled: Boolean(doc.module_config.mqtt.enabled),
        address: doc.module_config.mqtt.address || "mqtt.desdechipiona.es",
        username: doc.module_config.mqtt.username || "meshdev",
        password: doc.module_config.mqtt.password || "large4cats",
        root: doc.module_config.mqtt.root || "msh/EU_868",
        encryptionEnabled: true,
        tlsEnabled: true,
        proxyToClientEnabled: true,
        mapReportingEnabled: Boolean(doc.module_config.mqtt.mapReportingEnabled)
      };

      if (doc.module_config.mqtt.mapReportSettings) {
        mqttData.mapReportSettings = create(Protobuf.ModuleConfig.ModuleConfig_MapReportSettingsSchema, {
          publishIntervalSecs: Number(doc.module_config.mqtt.mapReportSettings.publishIntervalSecs) || 259200,
          positionPrecision: Number(doc.module_config.mqtt.mapReportSettings.positionPrecision) || 14,
          shouldReportLocation: Boolean(doc.module_config.mqtt.mapReportSettings.shouldReportLocation)
        });
      }

      const mqtt = create(Protobuf.ModuleConfig.ModuleConfig_MQTTConfigSchema, mqttData);
      const mod = create(Protobuf.ModuleConfig.ModuleConfigSchema, {
        payloadVariant: { case: "mqtt", value: mqtt }
      });
      await estado.dispositivo.setModuleConfig(mod);
      logActividad("✓ Módulo MQTT comunitario y reporte en mapa enviados.");
      await new Promise(r => setTimeout(r, 200));
    }

    // 4. Canales
    if (Array.isArray(doc.channels)) {
      for (const ch of doc.channels) {
        if (ch && ch.settings && ch.settings.name) {
          const chObj = create(Protobuf.Channel.ChannelSchema, {
            index: ch.index,
            role: ch.role === "PRIMARY" ? 1 : 2,
            settings: {
              name: ch.settings.name,
              psk: new Uint8Array([1]),
              uplinkEnabled: ch.settings.uplinkEnabled ?? true,
              downlinkEnabled: ch.settings.downlinkEnabled ?? true,
              moduleSettings: ch.settings.moduleSettings ? {
                positionPrecision: ch.settings.moduleSettings.positionPrecision ?? 15
              } : undefined
            }
          });
          await estado.dispositivo.setChannel(chObj);
          logActividad(`✓ Canal ${ch.index} (${ch.settings.name}) actualizado.`);
          await new Promise(r => setTimeout(r, 150));
        }
      }
    }

    // 5. Confirmar cambios y reiniciar
    logActividad("Confirmando cambios en memoria no volátil y reiniciando...");
    await estado.dispositivo.commitEditSettings();
    await new Promise(r => setTimeout(r, 300));
    await estado.dispositivo.reboot(3);

    logActividad("¡Configuración escrita con éxito en el nodo y reinicio enviado!");
    alert("¡Configuración volcada con éxito al dispositivo! El nodo se reiniciará con los nuevos ajustes.");
  } catch (err) {
    logActividad(`Error al escribir configuración: ${err.message}`);
    alert(`Error al escribir en el nodo: ${err.message}`);
  } finally {
    if (btnUpload) btnUpload.disabled = false;
  }
}

// --- Programación directa desde el Asistente (Paso 4) ---
export async function programarNodoDesdeAsistente() {
  if (!estado.dispositivo || !estado.nodoConectado) {
    alert("Primero debes conectar tu nodo Meshtastic por cable USB Serial o Bluetooth.");
    return;
  }

  const { configDoc } = construirYamlDeseado();
  const btnProgram = document.getElementById("btnProgramDirect");
  const feedbackEl = document.getElementById("directStatusFeedback");
  if (btnProgram) {
    btnProgram.disabled = true;
    btnProgram.textContent = "⏳ Programando nodo...";
  }

  if (feedbackEl) {
    feedbackEl.dataset.isProgramming = "true";
  }

  const setProgress = (paso, texto, icono = "⏳") => {
    logActividad(`[Asistente] ${texto}`);
    if (feedbackEl) {
      feedbackEl.innerHTML = `
        <div style="display: flex; flex-direction: column; gap: 0.35rem;">
          <div style="font-weight: 700; color: var(--color-texto-1); display: flex; align-items: center; gap: 0.5rem;">
            <span>${icono}</span> <span>${paso}</span>
          </div>
          <div style="font-size: 0.8rem; color: var(--color-texto-2);">${texto}</div>
        </div>
      `;
    }
  };

  try {
    setProgress("Iniciando sesión segura", "Preparando el dispositivo para recibir los parámetros...", "⏳");
    try {
      await estado.dispositivo.beginEditSettings();
    } catch (_) {}
    await new Promise(r => setTimeout(r, 200));

    // 1. Identidad (Owner)
    if (configDoc.owner || configDoc.owner_short) {
      setProgress("Paso 1/6: Identidad", `Aplicando nombre "${configDoc.owner}" y corto "${configDoc.owner_short}"...`, "👤");
      const user = create(Protobuf.Mesh.UserSchema, {
        longName: configDoc.owner || "MiNodo-Andalucia",
        shortName: (configDoc.owner_short || "AND1").slice(0, 4)
      });
      await estado.dispositivo.setOwner(user);
      await new Promise(r => setTimeout(r, 250));
    }

    // 2. Dispositivo y Rol
    const esClientMute = configDoc.config.device.role === "CLIENT_MUTE";
    setProgress("Paso 2/6: Rol del dispositivo", `Configurando rol ${configDoc.config.device.role} y rebroadcast...`, "⚙️");
    const dev = create(Protobuf.Config.Config_DeviceConfigSchema, {
      role: esClientMute ? 1 : 0,
      rebroadcastMode: esClientMute ? 5 : 0,
      nodeInfoBroadcastSecs: configDoc.config.device.nodeInfoBroadcastSecs || 259200,
      disableTripleClick: Boolean(configDoc.config.device.disableTripleClick),
      tzdef: configDoc.config.device.tzdef || "GMT-1GMT,M3.5.0,M10.5.0/3"
    });
    await estado.dispositivo.setConfig(create(Protobuf.Config.ConfigSchema, {
      payloadVariant: { case: "device", value: dev }
    }));
    await new Promise(r => setTimeout(r, 250));

    // 3. Radio LoRa (SFNarrow)
    setProgress("Paso 3/6: Parámetros LoRa", `Configurando SFNarrow (BW ${configDoc.config.lora.bandwidth} / SF${configDoc.config.lora.spreadFactor} / CR 4/${configDoc.config.lora.codingRate} / EU_868)...`, "📻");
    const lora = create(Protobuf.Config.Config_LoRaConfigSchema, {
      region: 3, // EU_868
      usePreset: Boolean(configDoc.config.lora.usePreset),
      bandwidth: Number(configDoc.config.lora.bandwidth) || 62,
      spreadFactor: Number(configDoc.config.lora.spreadFactor) || 7,
      codingRate: Number(configDoc.config.lora.codingRate) || 5,
      channelNum: Number(configDoc.config.lora.channelNum) || 4,
      hopLimit: Number(configDoc.config.lora.hopLimit) || 4,
      txPower: Number(configDoc.config.lora.txPower) || 27,
      txEnabled: true,
      sx126xRxBoostedGain: true,
      ignoreMqtt: Boolean(configDoc.config.lora.ignoreMqtt),
      configOkToMqtt: Boolean(configDoc.config.lora.configOkToMqtt)
    });
    await estado.dispositivo.setConfig(create(Protobuf.Config.ConfigSchema, {
      payloadVariant: { case: "lora", value: lora }
    }));
    await new Promise(r => setTimeout(r, 250));

    // 4. Posición y Telemetría
    setProgress("Paso 4/6: Posición y telemetría", `Configurando intervalos de emisión y protección de espectro...`, "📡");
    const pos = create(Protobuf.Config.Config_PositionConfigSchema, {
      positionBroadcastSmartEnabled: Boolean(configDoc.config.position.positionBroadcastSmartEnabled),
      positionBroadcastSecs: Number(configDoc.config.position.positionBroadcastSecs) || 21600,
      positionFlags: Number(configDoc.config.position.positionFlags) || 0,
      fixedPosition: Boolean(configDoc.config.position.fixedPosition)
    });
    await estado.dispositivo.setConfig(create(Protobuf.Config.ConfigSchema, {
      payloadVariant: { case: "position", value: pos }
    }));
    await new Promise(r => setTimeout(r, 250));

    if (configDoc.module_config?.telemetry) {
      const tel = create(Protobuf.ModuleConfig.ModuleConfig_TelemetryConfigSchema, {
        deviceUpdateInterval: Number(configDoc.module_config.telemetry.deviceUpdateInterval) || 0
      });
      await estado.dispositivo.setModuleConfig(create(Protobuf.ModuleConfig.ModuleConfigSchema, {
        payloadVariant: { case: "telemetry", value: tel }
      }));
      await new Promise(r => setTimeout(r, 250));
    }

    if (configDoc.module_config?.mqtt?.enabled) {
      const mqttData = {
        enabled: true,
        address: configDoc.module_config.mqtt.address || "mqtt.desdechipiona.es",
        username: configDoc.module_config.mqtt.username || "meshdev",
        password: configDoc.module_config.mqtt.password || "large4cats",
        root: configDoc.module_config.mqtt.root || "msh/EU_868",
        encryptionEnabled: true,
        tlsEnabled: true,
        proxyToClientEnabled: true,
        mapReportingEnabled: Boolean(configDoc.module_config.mqtt.mapReportingEnabled)
      };
      if (configDoc.module_config.mqtt.mapReportSettings) {
        mqttData.mapReportSettings = create(Protobuf.ModuleConfig.ModuleConfig_MapReportSettingsSchema, {
          publishIntervalSecs: Number(configDoc.module_config.mqtt.mapReportSettings.publishIntervalSecs) || 259200,
          positionPrecision: Number(configDoc.module_config.mqtt.mapReportSettings.positionPrecision) || 14,
          shouldReportLocation: Boolean(configDoc.module_config.mqtt.mapReportSettings.shouldReportLocation)
        });
      }
      const mqtt = create(Protobuf.ModuleConfig.ModuleConfig_MQTTConfigSchema, mqttData);
      await estado.dispositivo.setModuleConfig(create(Protobuf.ModuleConfig.ModuleConfigSchema, {
        payloadVariant: { case: "mqtt", value: mqtt }
      }));
      await new Promise(r => setTimeout(r, 250));
    }

    // 5. Canales
    setProgress("Paso 5/6: Canales comunitarios", "Escribiendo canales configurados...", "📻");
    const configuredIndices = new Set();
    if (Array.isArray(configDoc.channels)) {
      for (const ch of configDoc.channels) {
        if (ch && ch.settings && ch.settings.name) {
          configuredIndices.add(ch.index);
          const chObj = create(Protobuf.Channel.ChannelSchema, {
            index: ch.index,
            role: ch.role === "PRIMARY" ? 1 : 2,
            settings: {
              name: ch.settings.name,
              psk: new Uint8Array([1]),
              uplinkEnabled: ch.settings.uplinkEnabled ?? true,
              downlinkEnabled: ch.settings.downlinkEnabled ?? true,
              moduleSettings: ch.settings.moduleSettings ? {
                positionPrecision: ch.settings.moduleSettings.positionPrecision ?? 15
              } : undefined
            }
          });
          await estado.dispositivo.setChannel(chObj);
          logActividad(`✓ Canal ${ch.index} (${ch.settings.name}) programado.`);
          await new Promise(r => setTimeout(r, 200));
        }
      }
    }

    // Limpiar canales sobrantes si está marcado el checkbox
    const limpiarSobrantes = Boolean(document.getElementById("chkClearUnusedChannels")?.checked);
    if (limpiarSobrantes) {
      for (let i = 0; i < 8; i++) {
        if (!configuredIndices.has(i)) {
          if (estado.channelMap.has(i)) {
            const chActual = estado.channelMap.get(i);
            if (chActual?.settings?.name || (chActual?.role && chActual.role > 0)) {
              logActividad(`Limpiando canal sobrante en slot ${i}...`);
              await estado.dispositivo.clearChannel(i);
              await new Promise(r => setTimeout(r, 200));
            }
          }
        }
      }
    }

    // 6. Confirmar cambios en NVS y reiniciar dispositivo
    setProgress("Paso 6/6: Guardando cambios", "Confirmando parámetros en memoria no volátil...", "💾");
    await estado.dispositivo.commitEditSettings();
    await new Promise(r => setTimeout(r, 300));

    setProgress("Reinicio automático", "Reiniciando nodo en 3 segundos...", "🔄");
    await estado.dispositivo.reboot(3);

    // Feedback final de éxito
    if (feedbackEl) {
      const canalesNombres = configDoc.channels.filter(c => c.settings?.name).map(c => `<code>${escapeHtml(c.settings.name)}</code>`).join(", ");
      feedbackEl.innerHTML = `
        <div style="background: rgba(34, 197, 94, 0.12); border: 1px solid rgba(34, 197, 94, 0.4); border-radius: var(--radio-sm); padding: 0.85rem; color: var(--color-texto-1);">
          <div style="font-weight: 800; font-size: 0.95rem; color: var(--color-correcto-texto); display: flex; align-items: center; gap: 0.4rem; margin-bottom: 0.35rem;">
            <span>✅</span> <span>¡Nodo programado y reiniciado con éxito!</span>
          </div>
          <div style="font-size: 0.82rem; line-height: 1.5; color: var(--color-texto-2);">
            Identidad: <strong>${escapeHtml(configDoc.owner)}</strong> (${escapeHtml(configDoc.owner_short)})<br/>
            Rol: <strong>${escapeHtml(configDoc.config.device.role)}</strong> · LoRa: <strong>SFNarrow (EU_868)</strong><br/>
            Canales activos: ${canalesNombres}<br/>
            El nodo se está reiniciando con los nuevos parámetros de Andalucía Mesh.
          </div>
        </div>
      `;
    }

    logActividad(`✓ ¡Nodo programado con éxito y reiniciado en 3 segundos!`);
  } catch (err) {
    console.error("Error al programar nodo:", err);
    logActividad(`Error al programar nodo: ${err.message}`);
    if (feedbackEl) {
      feedbackEl.innerHTML = `
        <div style="background: rgba(239, 68, 68, 0.12); border: 1px solid rgba(239, 68, 68, 0.4); border-radius: var(--radio-sm); padding: 0.75rem; color: #ef4444;">
          <strong>❌ Error al programar nodo:</strong> ${escapeHtml(err.message)}
        </div>
      `;
    }
    alert(`Ocurrió un error al enviar la configuración al nodo: ${err.message}`);
  } finally {
    if (btnProgram) {
      btnProgram.disabled = false;
      btnProgram.textContent = "🚀 Programar Nodo Ahora";
    }
    if (feedbackEl) {
      delete feedbackEl.dataset.isProgramming;
    }
  }
}

export function alEditarYamlDeseado() {
  const desired = document.getElementById("desiredYamlTextarea")?.value;
  if (desired) {
    estado.desiredConfig = null;
  }
  actualizarDiff();
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
    actualizarDiff();
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

// --- Exposición inmediata de funciones globales en window (para onclicks inline) ---
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
window.programarNodoDesdeAsistente = programarNodoDesdeAsistente;
window.alEditarYamlDeseado = alEditarYamlDeseado;
window.actualizarDiff = actualizarDiff;
window.setModo = setModo;
window.toggleTema = toggleTema;
window.limpiarLog = () => {
  const t = document.getElementById("logTextarea");
  if (t) t.value = "";
};

// Delegación global de clics para robustez total de pestañas y botones
document.addEventListener("click", (e) => {
  const target = e.target;
  if (!target) return;

  const btnModo = target.closest("#tabAssistantMode, #tabWorkbenchMode");
  if (btnModo) {
    if (btnModo.id === "tabAssistantMode") setModo("asistente");
    if (btnModo.id === "tabWorkbenchMode") setModo("workbench");
  }
});

// --- Inicialización del configurador ---
function iniciarConfigurador() {
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

  // Event Listeners directos
  document.getElementById("themeToggleBtn")?.addEventListener("click", toggleTema);
  document.getElementById("tabAssistantMode")?.addEventListener("click", () => setModo("asistente"));
  document.getElementById("tabWorkbenchMode")?.addEventListener("click", () => setModo("workbench"));

  document.getElementById("transportSelect")?.addEventListener("change", (e) => {
    const isHttp = e.target.value === "http";
    const httpGroup = document.getElementById("httpIpGroup");
    if (httpGroup) httpGroup.style.display = isHttp ? "flex" : "none";
  });

  document.getElementById("transportSelectWorkbench")?.addEventListener("change", (e) => {
    const isHttp = e.target.value === "http";
    const httpGroup = document.getElementById("httpIpGroupWorkbench");
    if (httpGroup) httpGroup.style.display = isHttp ? "flex" : "none";
  });

  document.getElementById("chkMqtt")?.addEventListener("change", actualizarConfiguracion);
  document.getElementById("chkMqttMap")?.addEventListener("change", actualizarConfiguracion);

  // Inicializar configuración y QR
  actualizarConfiguracion();
  logActividad("Configurador de Andalucía Mesh iniciado con preset SFNarrow.");
}

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", iniciarConfigurador);
} else {
  iniciarConfigurador();
}
