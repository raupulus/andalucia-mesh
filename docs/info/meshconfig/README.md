# Módulo: MeshConfig (Configurador Web Meshtastic)

> Aplicación web cliente y asistente interactivo para la configuración de nodos Meshtastic en **Andalucía Mesh**, aplicando los parámetros estándar oficiales de radio (**SFNarrow**) y buenas prácticas de red. Basado en el proyecto de código abierto [`pdxlocations/meshconfig`](https://github.com/pdxlocations/meshconfig) anclado a commit fijo (`658f461`).

## Qué hace y qué NO hace
- **Qué hace**:
  - Permite a los usuarios configurar sus nodos directamente desde el navegador vía Web Serial (USB) y Web Bluetooth (BLE), o mediante HTTP local.
  - Genera códigos QR oficiales y enlaces `https://meshtastic.org/e/#...` para importar los canales y la radio en 1 clic desde la app oficial de Meshtastic en móviles (iOS y Android).
  - Permite la exportación y descarga directa del archivo YAML de configuración óptimo para el nodo.
  - Facilita los comandos CLI oficiales listos para copiar y pegar en la terminal.
  - Aplica por defecto el estándar oficial comunitario **SFNarrow** (EU_868, BW 62.5 kHz, SF 7, CR 5, Slot 4 869.61875 MHz, canal 0 SFNarrow con PSK `AQ==`).
  - Proporciona un selector de canal provincial secundario con nombres normalizados sin tildes (`Cadiz`, `Sevilla`, `Almeria`, etc.).
  - Aplica buenas prácticas de red automáticas: `positionBroadcastSmartEnabled: false`, `positionFlags: 0`, NodeInfo cada 72h, posición cada 6h (móvil) o 72h (fijo), saltos 4 (`CLIENT_MUTE`) o 3 (`CLIENT`), telemetría apagada por defecto y MQTT desactivado por defecto.
  - Ofrece un selector de potencia TX adaptado a módulos estándar (27 dBm) y amplificados (Ebyte E22P-868M30S a 8 dBm).
  - Incluye un Modo Avanzado (Workbench) con volcado live, editor YAML y comparador visual de diferencias (Diff).
- **Qué NO hace**:
  - NO flashea ni actualiza binarios de firmware en el microcontrolador (delega en Meshtastic Web Flasher).
  - NO gestiona nodos de forma remota a través de la malla LoRa por radio (solo conexión directa local por seguridad).
  - NO emite ni requiere cookies ni almacenamiento de datos personales en el servidor.

## Modelo de datos
- Documentos de configuración YAML estructurados según el esquema de Meshtastic 2.x:
  - `owner` / `owner_short`: Cadenas de texto UTF-8 de identidad.
  - `config.lora`: Parámetros de radio (región, frecuencia, ancho de banda, spreading factor, potencia, saltos).
  - `config.device`: Rol (`CLIENT_MUTE` o `CLIENT`), intervalo de difusión de NodeInfo y rebroadcast mode.
  - `config.position`: Intervalos de emisión, flags a 0 y smart position apagado.
  - `module_config.telemetry`: Intervalos de telemetría de dispositivo o ambientales.
  - `module_config.mqtt`: Datos de conexión al broker comunitario (opcional).
  - `channels`: Array de hasta 8 canales con roles `PRIMARY` y `SECONDARY`, nombres y claves PSK.
- Mensajes Protobuf de canal (`ChannelSet` de `apponly.proto`) serializados y codificados en Base64Url para enlaces `https://meshtastic.org/e/#...`.

## Flujos principales
1. **Flujo Asistente (Modo Guiado)**:
   - Paso 1: Usuario revisa el preset SFNarrow, selecciona opcionalmente su provincia y la potencia de radio.
   - Paso 2: Usuario selecciona su rol (`CLIENT_MUTE` o `CLIENT`), telemetría y MQTT.
   - Paso 3: Usuario personaliza el nombre largo y corto de su dispositivo.
   - Paso 4: El usuario escanea el código QR con la app de Meshtastic, descarga el archivo YAML o conecta directamente su nodo por USB/BLE para aplicarlo en 1 clic.
2. **Flujo Avanzado (Workbench)**:
   - El operador conecta el dispositivo, pulsa "Leer del nodo" (`downloadLiveConfig`), edita el YAML o carga un preset, pulsa "Actualizar Diff" para inspeccionar las diferencias exactas y confirma con "Escribir al nodo" (`uploadDesiredConfig`).
   - El nodo procesa la transacción, escribe en flash LittleFS y se reinicia.

## Puntos de entrada
- **Ruta Web Principal:** `/configurador` (en el portal, puerto 8100/9000).
- **Ruta de Recursos Estáticos:** `/configurador/{file}` (entrega `styles.css`, `configurador.js`, `configs/*.yaml`).
- **Servicio Independiente:** `http://127.0.0.1:8420/` (contenedor Docker `snm-meshconfig`).
- **Virtual Host Nginx:** `config.mesh.example.org` (configurado en `snm-meshconfig.conf`).
- **Autenticación:** Pública (sin login, sin cookies).

## Dependencias en ambos sentidos
- **Hacia adentro**:
  - Imagen base `nginxinc/nginx-unprivileged:1.31.1-alpine` del Containerfile de `pdxlocations/meshconfig`.
  - Módulos cliente ESM: `@meshtastic/core`, `@bufbuild/protobuf`, `@meshtastic/transport-http`, `js-yaml`, `qrcodejs`.
- **Hacia afuera**:
  - `services/portal` (Controlador `ConfiguradorController.php` y enlaces de navegación).
  - `infrastructure/nginx` (Sitio virtual `snm-meshconfig.conf` y reenvío `/configurador`).

## Configuración
| Variable | Valor por defecto | Efecto |
| :--- | :--- | :--- |
| `MESHCONFIG_PORT` | `8420` | Puerto en `127.0.0.1` donde escucha el contenedor Nginx |
| `CONFIG_DOMAIN` | `config.${PROJECT_DOMAIN}` | Subdominio asignado para acceso web directo |
| `HEALTH_URL_MESHCONFIG` | `http://127.0.0.1:8420/` | Endpoint de salud y conectividad interna |

## Trampas conocidas
- **Web Serial y Web Bluetooth en Safari / Firefox:** Estas APIs solo están disponibles en navegadores basados en Chromium (Chrome, Edge, Opera, Brave). Para usuarios de otros navegadores, el configurador ofrece de forma nativa la descarga de archivo YAML, comandos CLI y el código QR escaneable con el móvil.
- **Amplificadores de potencia (PA):** En módulos con amplificador externo o integrado como el Ebyte E22P-868M30S, configurar 27 dBm de potencia en el firmware satura el amplificador y supera con creces el límite legal europeo (ERP 27 dBm). Debe seleccionarse siempre 8 dBm para estos módulos.

## Tests que lo cubren
- `services/portal/tests/Feature/ConfiguradorTest.php`:
  - `test_configurador_responde_200_y_cero_cookies`
  - `test_configurador_contiene_elementos_clave_sfnarrow`
  - `test_configurador_entrega_estilos_css`
  - `test_configurador_entrega_modulo_javascript`
  - `test_configurador_entrega_preset_client_mute`
  - `test_configurador_entrega_preset_client`
  - `test_configurador_recurso_inexistente_devuelve_404`

## Pendiente real
- [x] Contenedor Docker compilado y corriendo en local (puerto 8420).
- [x] Presets SFNarrow creados y probados con buenas prácticas oficiales.
- [x] Generador de URL y Código QR dinámico implementado.
- [x] Integración en el portal y rutas públicas de Laravel probadas (puerto 9000).

---
> Creado: 2026-10-08 · Última revisión: 2026-10-08
