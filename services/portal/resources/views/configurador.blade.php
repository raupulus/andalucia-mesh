@php
    $currentLang = app()->getLocale();
    $langQuery = $currentLang !== 'es' ? '?lang=' . $currentLang : '';
@endphp

<x-layout 
    :title="__('portal.configurator.meta_title', ['name' => config('proyecto.nombre')])" 
    :description="__('portal.configurator.lead')"
    :image="asset('img/og/og-configurador.webp')"
>
    <x-slot:styles>
        <base href="/configurador/">
        <link rel="stylesheet" href="/configurador/styles.css">
    </x-slot:styles>

    <div class="contenedor seccion">
        <div class="main-shell" style="padding-top: 0;">
            <!-- Encabezado de la herramienta con Status Pill y Badge Experimental -->
            <section class="hero-banner">
                <div style="display: flex; justify-content: center; align-items: center; gap: 0.85rem; flex-wrap: wrap; margin-bottom: 0.75rem;">
                    <h1 style="margin-bottom: 0;">{{ __('portal.configurator.title') }}</h1>
                    <div id="statusPill" class="badge-tag">🔌 {{ __('portal.configurator.disconnected') }}</div>
                    <span class="badge-tag badge-tag-aviso">⚠️ {{ __('portal.configurator.warning_experimental_badge') }}</span>
                </div>
                <p>{{ __('portal.configurator.lead') }}</p>

                <!-- Aviso de advertencia: herramienta experimental y reporte en sugerencias -->
                <div class="banner-aviso-configurador" role="alert">
                    <span style="font-size: 1.35rem; flex-shrink: 0;" aria-hidden="true">⚠️</span>
                    <div style="font-size: 0.92rem; line-height: 1.45;">
                        <span class="badge-tag badge-tag-aviso" style="margin-right: 0.4rem; font-size: 0.75rem; vertical-align: middle;">
                            ▲ {{ __('portal.configurator.warning_experimental_badge') }}
                        </span>
                        <span>{{ __('portal.configurator.warning_experimental_lead') }}</span>
                        <span>{!! __('portal.configurator.warning_experimental_report', [
                            'link' => '<a href="/sugerencias' . $langQuery . '">' . e(__('portal.configurator.warning_experimental_link')) . '</a>'
                        ]) !!}</span>
                    </div>
                </div>
            </section>

            <!-- Selector de Modo -->
            <div class="mode-tabs">
                <button id="tabAssistantMode" type="button" class="mode-tab-btn active" onclick="setModo('asistente')">✨ Modo Asistente (Recomendado)</button>
                <button id="tabWorkbenchMode" type="button" class="mode-tab-btn" onclick="setModo('workbench')">🛠️ Modo Avanzado (Workbench)</button>
            </div>

            <!-- ===================================================================
                 VISTA 1: MODO ASISTENTE (GUIADO EN 4 PASOS)
                 =================================================================== -->
            <div id="viewAssistant">
                <!-- Navegación de Pasos -->
                <nav class="stepper-nav" aria-label="Pasos de configuración">
                    <div class="step-item active" id="stepIndicator1" onclick="irAlPaso(1)">
                        <div class="step-num">1</div>
                        <div class="step-label">Radio y Canales</div>
                    </div>
                    <div class="step-item" id="stepIndicator2" onclick="irAlPaso(2)">
                        <div class="step-num">2</div>
                        <div class="step-label">Rol y Telemetría</div>
                    </div>
                    <div class="step-item" id="stepIndicator3" onclick="irAlPaso(3)">
                        <div class="step-num">3</div>
                        <div class="step-label">Identidad</div>
                    </div>
                    <div class="step-item" id="stepIndicator4" onclick="irAlPaso(4)">
                        <div class="step-num">4</div>
                        <div class="step-label">Aplicar y Compartir</div>
                    </div>
                </nav>

                <!-- PASO 1: Radio y Canales -->
                <section id="stepPanel1" class="step-panel active card">
                    <div class="card-header">
                        <div>
                            <p class="field-label" style="text-transform: uppercase; letter-spacing: 0.05em; color: var(--color-enlace);">Paso 1 de 4</p>
                            <h2>Parámetros de Red y Canales LoRa</h2>
                        </div>
                        <span class="badge-tag" style="background: var(--color-correcto-fondo); color: var(--color-correcto-texto); border-color: var(--color-correcto-texto);">
                            ✓ Estándar Oficial SFNarrow
                        </span>
                    </div>
                    <div class="card-body">
                        <!-- Tarjeta resumen SFNarrow -->
                        <div style="background: var(--color-superficie-sutil); border: 1px solid var(--color-borde); border-radius: var(--radio-md); padding: 1.25rem; margin-bottom: 1.5rem;">
                            <h3 style="font-size: 1.1rem; margin-bottom: 0.35rem; color: var(--color-texto);">Preset SFNarrow (Banda Estrecha)</h3>
                            <p style="font-size: 0.9rem; color: var(--color-texto-2); line-height: 1.5;">
                                Región <strong>EU_868</strong> · Ancho de banda <strong>62.5 kHz</strong> · Spreading Factor <strong>7</strong> · Coding Rate <strong>5 (4/5)</strong> · Slot de Frecuencia <strong>4 (869.61875 MHz)</strong>. Maximiza el alcance y reduce colisiones en toda la comunidad andaluza.
                            </p>
                        </div>

                        <div class="form-grid">
                            <!-- Potencia de TX -->
                            <div class="field-group">
                                <label class="field-label">Potencia de Transmisión (Límite legal en EU_868: 27 dBm)</label>
                                <div style="display: flex; flex-direction: column; gap: 0.5rem; margin-top: 0.25rem;">
                                    <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.95rem; cursor: pointer;">
                                        <input type="radio" name="txPowerSelect" value="27" checked onchange="actualizarConfiguracion()" />
                                        <span><strong>Predeterminado (Normal / Fábrica)</strong> — 27 dBm (Heltec V3, T-Beam, T-Echo)</span>
                                    </label>
                                    <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.95rem; cursor: pointer;">
                                        <input type="radio" name="txPowerSelect" value="8" onchange="actualizarConfiguracion()" />
                                        <span><strong>Módulo amplificado Ebyte E22P-868M30S</strong> — 8 dBm (PA entrega 27 dBm reales)</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Selector de Canal Provincial -->
                            <div class="field-group">
                                <label for="provinciaSelect" class="field-label">Canal Provincial Secundario (Opcional)</label>
                                <select id="provinciaSelect" class="form-select" onchange="actualizarConfiguracion()">
                                    <option value="">— Ninguno (Solo canal general SFNarrow) —</option>
                                    <option value="Almeria">Almería (Almeria)</option>
                                    <option value="Cadiz">Cádiz (Cadiz)</option>
                                    <option value="Cordoba">Córdoba (Cordoba)</option>
                                    <option value="Granada">Granada (Granada)</option>
                                    <option value="Huelva">Huelva (Huelva)</option>
                                    <option value="Jaen">Jaén (Jaen)</option>
                                    <option value="Malaga">Málaga (Malaga)</option>
                                    <option value="Sevilla">Sevilla (Sevilla)</option>
                                    <option value="Ceuta">Ceuta (Ceuta)</option>
                                    <option value="Melilla">Melilla (Melilla)</option>
                                </select>
                                <span style="font-size: 0.8rem; color: var(--color-texto-2);">Los nombres se normalizan sin tildes para garantizar total compatibilidad con terminales y pantallas OLED.</span>
                            </div>
                        </div>

                        <!-- Canales adicionales -->
                        <div class="field-group" style="margin-top: 0.5rem;">
                            <label class="field-label">Otros Canales Comunitarios Adicionales</label>
                            <div style="display: flex; flex-wrap: wrap; gap: 1.25rem; margin-top: 0.35rem;">
                                <label style="display: flex; align-items: center; gap: 0.4rem; font-size: 0.9rem; cursor: pointer;">
                                    <input type="checkbox" id="chkIberia" onchange="actualizarConfiguracion()" />
                                    <span>Iberia</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 0.4rem; font-size: 0.9rem; cursor: pointer;">
                                    <input type="checkbox" id="chkAndalucia" onchange="actualizarConfiguracion()" />
                                    <span>Andalucía (Andalucia)</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 0.4rem; font-size: 0.9rem; cursor: pointer;">
                                    <input type="checkbox" id="chkTest" onchange="actualizarConfiguracion()" />
                                    <span>Test</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 0.4rem; font-size: 0.9rem; cursor: pointer;">
                                    <input type="checkbox" id="chkBots" onchange="actualizarConfiguracion()" />
                                    <span>Bots</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 0.4rem; font-size: 0.9rem; cursor: pointer;">
                                    <input type="checkbox" id="chkSos" onchange="actualizarConfiguracion()" />
                                    <span>sos (Emergencias Mesh)</span>
                                </label>
                            </div>
                        </div>

                        <div style="display: flex; justify-content: flex-end; margin-top: 2rem;">
                            <button type="button" class="btn btn-primary" onclick="irAlPaso(2)">Siguiente: Rol y Telemetría →</button>
                        </div>
                    </div>
                </section>

                <!-- PASO 2: Rol y Buenas Prácticas -->
                <section id="stepPanel2" class="step-panel card">
                    <div class="card-header">
                        <div>
                            <p class="field-label" style="text-transform: uppercase; letter-spacing: 0.05em; color: var(--color-enlace);">Paso 2 de 4</p>
                            <h2>Rol del Nodo y Buenas Prácticas de Red</h2>
                        </div>
                    </div>
                    <div class="card-body">
                        <label class="field-label" style="margin-bottom: 0.75rem; display: block;">Selecciona cómo vas a utilizar tu nodo en la red:</label>
                        <div class="role-cards-grid">
                            <!-- Opción CLIENT_MUTE -->
                            <div id="cardRoleMute" class="role-card-option selected" onclick="seleccionarRol('CLIENT_MUTE')">
                                <div class="role-card-header">
                                    <span class="role-card-title">CLIENT_MUTE (Recomendado)</span>
                                    <span class="badge-tag" style="background: var(--color-correcto-fondo); color: var(--color-correcto-texto);">Mayoría</span>
                                </div>
                                <p class="role-card-desc">
                                    Permite enviar y recibir mensajes sin reenviar los paquetes de otros. Ideal para nodos personales, móviles de bolsillo, interiores o con cobertura variable. Ahorra batería y protege la red de saturación.
                                </p>
                                <div class="role-card-badges">
                                    <span class="badge-tag">4 saltos</span>
                                    <span class="badge-tag">Posición c/ 6h</span>
                                    <span class="badge-tag">NodeInfo c/ 72h</span>
                                </div>
                            </div>

                            <!-- Opción CLIENT -->
                            <div id="cardRoleClient" class="role-card-option" onclick="seleccionarRol('CLIENT')">
                                <div class="role-card-header">
                                    <span class="role-card-title">CLIENT (Base Fija)</span>
                                    <span class="badge-tag">Exterior</span>
                                </div>
                                <p class="role-card-desc">
                                    Para nodos en ubicación fija despejada (azotea o terraza alta) con línea de vista hacia varios nodos. Reenvía mensajes para extender la cobertura comunitaria de su zona.
                                </p>
                                <div class="role-card-badges">
                                    <span class="badge-tag">3 saltos</span>
                                    <span class="badge-tag">Posición c/ 72h</span>
                                    <span class="badge-tag">NodeInfo c/ 72h</span>
                                </div>
                            </div>
                        </div>

                        <!-- Parámetros adicionales de telemetría y MQTT -->
                        <div class="form-grid">
                            <div class="field-group">
                                <label for="telemetriaSelect" class="field-label">Telemetría del dispositivo</label>
                                <select id="telemetriaSelect" class="form-select" onchange="actualizarConfiguracion()">
                                    <option value="0" selected>Desactivada — (Enchufado en casa o móvil sin sensores)</option>
                                    <option value="14400">Cada 4 horas — (Nodo Solar: Batería y paneles)</option>
                                    <option value="21600">Cada 6 horas — (Troncal / Infraestructura)</option>
                                    <option value="43200">Cada 12 horas</option>
                                    <option value="86400">Cada 24 horas (1 día)</option>
                                    <option value="172800">Cada 48 horas (2 días)</option>
                                    <option value="259200">Cada 72 horas (3 días)</option>
                                </select>
                                <span style="font-size: 0.8rem; color: var(--color-texto-2);">Evita emitir telemetría innecesaria para reservar el espectro de radio a la mensajería.</span>
                            </div>

                            <div class="field-group">
                                <label for="posicionSelect" class="field-label">Envío de posición (GPS / Ubicación)</label>
                                <select id="posicionSelect" class="form-select" onchange="actualizarConfiguracion()">
                                    <option value="0">Desactivada — (Sin emisión periódica de posición)</option>
                                    <option value="14400">Cada 4 horas</option>
                                    <option value="21600" selected>Cada 6 horas — (Recomendado móvil / CLIENT_MUTE)</option>
                                    <option value="43200">Cada 12 horas</option>
                                    <option value="86400">Cada 24 horas (1 día)</option>
                                    <option value="172800">Cada 48 horas (2 días)</option>
                                    <option value="259200">Cada 72 horas (3 días — Recomendado fijo / CLIENT)</option>
                                </select>
                                <span style="font-size: 0.8rem; color: var(--color-texto-2);">Intervalo para refrescar la posición del nodo en la malla. Emisiones frecuentes saturan el canal.</span>
                            </div>

                            <div class="field-group" style="grid-column: 1 / -1;">
                                <label class="field-label">Conexión MQTT Comunitaria</label>
                                <div style="background: var(--color-superficie-sutil); border: 1px solid var(--color-borde); border-radius: var(--radio-md); padding: 0.85rem; margin-top: 0.25rem;">
                                    <label style="display: flex; align-items: flex-start; gap: 0.6rem; cursor: pointer; font-size: 0.9rem;">
                                        <input type="checkbox" id="chkMqtt" onchange="actualizarConfiguracion()" style="margin-top: 0.2rem;" />
                                        <span>
                                            <strong>{{ __('portal.configurator.mqtt_colaborar_label') }}</strong> (<code>mqtt.mesh.desdechipiona.es</code>)<br />
                                            <span style="font-size: 0.8rem; color: var(--color-texto-2);">{{ __('portal.configurator.mqtt_colaborar_desc') }}</span>
                                        </span>
                                    </label>

                                    <!-- Opción condicional: Aparecer en mapa público de cobertura (Map Reporting) -->
                                    <div id="mqttMapOptionWrapper" style="display: none; margin-top: 0.75rem; padding-top: 0.75rem; border-top: 1px dashed var(--color-borde);">
                                        <label style="display: flex; align-items: flex-start; gap: 0.6rem; cursor: pointer; font-size: 0.9rem;">
                                            <input type="checkbox" id="chkMqttMap" onchange="actualizarConfiguracion()" style="margin-top: 0.2rem;" />
                                            <span>
                                                <strong>{{ __('portal.configurator.mqtt_map_label') }}</strong><br />
                                                <span style="font-size: 0.8rem; color: var(--color-texto-2);">{{ __('portal.configurator.mqtt_map_desc') }}</span>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div style="display: flex; justify-content: space-between; margin-top: 2rem;">
                            <button type="button" class="btn btn-secondary" onclick="irAlPaso(1)">← Anterior</button>
                            <button type="button" class="btn btn-primary" onclick="irAlPaso(3)">Siguiente: Nombre e Identidad →</button>
                        </div>
                    </div>
                </section>

                <!-- PASO 3: Identidad del Dispositivo -->
                <section id="stepPanel3" class="step-panel card">
                    <div class="card-header">
                        <div>
                            <p class="field-label" style="text-transform: uppercase; letter-spacing: 0.05em; color: var(--color-enlace);">Paso 3 de 4</p>
                            <h2>Identidad y Nombre del Nodo</h2>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="form-grid">
                            <div class="field-group">
                                <label for="inputLongName" class="field-label">Nombre Largo (Long Name)</label>
                                <input id="inputLongName" type="text" class="form-control" placeholder="Ej: MiNodo-Cadiz, Eco-Chipiona" value="MiNodo-Andalucia" oninput="actualizarConfiguracion()" />
                                <span style="font-size: 0.8rem; color: var(--color-texto-2);">El nombre visible con el que otros usuarios te identificarán en el mapa y la lista de nodos.</span>
                            </div>
                            <div class="field-group">
                                <label for="inputShortName" class="field-label">Nombre Corto (Short Name — Máx 4 caracteres)</label>
                                <input id="inputShortName" type="text" maxlength="4" class="form-control" placeholder="AND1" value="AND1" oninput="actualizarConfiguracion()" />
                                <span style="font-size: 0.8rem; color: var(--color-texto-2);">Aparece junto a tus mensajes en pantallas pequeñas o en el chat.</span>
                            </div>
                        </div>

                        <div style="background: var(--color-superficie-sutil); border: 1px solid var(--color-borde); border-radius: var(--radio-md); padding: 1.25rem; margin-top: 1rem;">
                            <span class="field-label" style="display: block; margin-bottom: 0.35rem;">Vista previa de tu nodo:</span>
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <div style="width: 44px; height: 44px; border-radius: 50%; background: var(--color-acento); display: flex; align-items: center; justify-content: center; font-weight: 800; color: var(--color-sobre-acento); font-family: var(--fuente-titulos);">
                                    <span id="previewAvatar">A</span>
                                </div>
                                <div>
                                    <div id="previewLongName" style="font-weight: 700; font-size: 1.05rem;">MiNodo-Andalucia</div>
                                    <div style="font-size: 0.85rem; color: var(--color-texto-2); font-family: var(--fuente-mono);">
                                        <span id="previewShortName">AND1</span> · SFNarrow (EU_868)
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div style="display: flex; justify-content: space-between; margin-top: 2rem;">
                            <button type="button" class="btn btn-secondary" onclick="irAlPaso(2)">← Anterior</button>
                            <button type="button" class="btn btn-primary" onclick="irAlPaso(4)">Siguiente: Aplicar y Compartir →</button>
                        </div>
                    </div>
                </section>

                <!-- PASO 4: Aplicar y Compartir -->
                <section id="stepPanel4" class="step-panel card">
                    <div class="card-header">
                        <div>
                            <p class="field-label" style="text-transform: uppercase; letter-spacing: 0.05em; color: var(--color-enlace);">Paso 4 de 4</p>
                            <h2>Aplicar o Descargar Configuración</h2>
                        </div>
                    </div>
                    <div class="card-body">
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 2rem;">
                            <!-- Columna Izquierda: QR y enlaces móviles -->
                            <div>
                                <h3 style="font-size: 1.15rem; margin-bottom: 0.75rem;">📱 Escanea con la App de Meshtastic</h3>
                                <p style="font-size: 0.9rem; color: var(--color-texto-2); margin-bottom: 1.25rem;">
                                    Abre la app oficial de Meshtastic en tu móvil (iOS o Android), pulsa en <em>Canales → Escanear código QR</em> y apunta a la pantalla:
                                </p>

                                <div class="qr-preview-box">
                                    <div class="qr-canvas-wrapper" id="qrCanvasContainer">
                                        <!-- El QR dinámico se inserta aquí -->
                                    </div>
                                    <div class="share-url-input-group">
                                        <input id="qrShareUrl" type="text" class="form-control" readonly />
                                        <button type="button" class="btn btn-secondary" onclick="copiarEnlaceQR()" title="Copiar enlace">Copiar</button>
                                    </div>

                                    <!-- Resumen claro de canales incluidos en el QR -->
                                    <div id="channelsSummaryBox" style="margin-top: 1rem; width: 100%; background: var(--color-superficie-sutil); border: 1px solid var(--color-borde); border-radius: var(--radio-sm); padding: 0.75rem; font-size: 0.82rem; text-align: left;">
                                        <div style="font-weight: 700; color: var(--color-texto-1); margin-bottom: 0.4rem; display: flex; align-items: center; gap: 0.35rem;">
                                            <span>📻</span> <span>Canales incluidos en esta configuración:</span>
                                        </div>
                                        <div id="channelsSummaryList" style="display: flex; flex-direction: column; gap: 0.35rem;">
                                            <!-- Rellenado dinámicamente -->
                                        </div>
                                    </div>
                                </div>

                                <div style="display: flex; gap: 0.75rem; flex-wrap: wrap; margin-top: 1rem;">
                                    <button type="button" class="btn btn-secondary" onclick="descargarYamlDeseado()">📥 Descargar archivo YAML</button>
                                    <button type="button" class="btn btn-secondary" onclick="copiarComandosCli()">📋 Copiar comandos CLI</button>
                                </div>
                            </div>

                            <!-- Columna Derecha: Conexión directa por cable o Bluetooth -->
                            <div style="border-left: 1px solid var(--color-borde); padding-left: 1.5rem;">
                                <h3 style="font-size: 1.15rem; margin-bottom: 0.75rem;">🔌 Conexión Directa y Programación</h3>
                                <p style="font-size: 0.9rem; color: var(--color-texto-2); margin-bottom: 1.25rem;">
                                    Si estás en un navegador Chromium (Chrome, Edge o Brave), conecta tu nodo por cable USB o enciende el Bluetooth para volcar los ajustes directamente:
                                </p>

                                <div style="background: var(--color-superficie-sutil); border: 1px solid var(--color-borde); border-radius: var(--radio-md); padding: 1.25rem; display: flex; flex-direction: column; gap: 1rem; margin-bottom: 1.25rem;">
                                    <div class="field-group">
                                        <label for="transportSelect" class="field-label">Método de conexión directa</label>
                                        <select id="transportSelect" class="form-select">
                                            <option value="serial">USB Serial (Cable conectado al ordenador)</option>
                                            <option value="bluetooth">Bluetooth (Inalámbrico Web BLE)</option>
                                            <option value="http">HTTP(S) local (Nodo en la misma WiFi)</option>
                                        </select>
                                    </div>

                                    <div id="httpIpGroup" style="display: none;" class="field-group">
                                        <label for="httpIpInput" class="field-label">Dirección IP o Host del nodo</label>
                                        <input id="httpIpInput" type="text" class="form-control" placeholder="192.168.1.100 o meshtastic.local" />
                                    </div>

                                    <div style="display: flex; gap: 0.75rem;">
                                        <button id="btnConnectDirect" type="button" class="btn btn-primary" style="flex: 1;" onclick="conectarDispositivo('assistant')">🔌 Conectar al Nodo</button>
                                        <button id="btnDisconnectDirect" type="button" class="btn btn-secondary" disabled onclick="desconectarDispositivo()">Desconectar</button>
                                    </div>

                                    <!-- Tarjeta de estado de nodo conectado y botón de programación -->
                                    <div id="connectedNodeCard" style="display: none; background: rgba(34, 197, 94, 0.08); border: 1px solid rgba(34, 197, 94, 0.3); border-radius: var(--radio-sm); padding: 0.85rem; flex-direction: column; gap: 0.5rem;">
                                        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.5rem;">
                                            <span style="font-size: 0.9rem; font-weight: 700; color: var(--color-correcto-texto);">
                                                🟢 Conectado: <span id="connectedNodeName">Nodo Meshtastic</span>
                                            </span>
                                            <span id="connectedNodeId" style="font-family: var(--fuente-mono); font-size: 0.8rem; background: var(--color-superficie); padding: 2px 6px; border-radius: 4px; border: 1px solid var(--color-borde);">!ffffffff</span>
                                        </div>
                                        <div id="connectedNodeRole" style="font-size: 0.8rem; color: var(--color-texto-2);">
                                            Listo para aplicar la configuración deseada de Andalucía Mesh.
                                        </div>

                                        <label style="font-size: 0.8rem; color: var(--color-texto-2); display: flex; align-items: center; gap: 0.4rem; margin-top: 0.25rem; cursor: pointer;">
                                            <input type="checkbox" id="chkClearUnusedChannels" checked style="cursor: pointer;" />
                                            <span>Limpiar canales secundarios sobrantes (dejar solo los seleccionados)</span>
                                        </label>

                                        <button id="btnProgramDirect" type="button" class="btn btn-primary" style="width: 100%; margin-top: 0.5rem; font-weight: 700; font-size: 1rem; background: var(--color-acento); color: var(--color-sobre-acento); border: none; padding: 0.75rem;" onclick="programarNodoDesdeAsistente()">
                                            🚀 Programar Nodo Ahora
                                        </button>
                                    </div>
                                </div>

                                <div id="directStatusFeedback" style="font-size: 0.85rem; color: var(--color-texto-2); line-height: 1.45; background: var(--color-superficie-sutil); border: 1px solid var(--color-borde); border-radius: var(--radio-sm); padding: 0.75rem;">
                                    💡 <em>Conecta tu nodo por cable USB Serial o Bluetooth para volcar los ajustes. Ningún parámetro se alterará en el dispositivo hasta que pulses en <strong>Programar Nodo Ahora</strong>. El nodo se reiniciará automáticamente tras aplicar los cambios.</em>
                                </div>
                            </div>
                        </div>

                        <div style="display: flex; justify-content: flex-start; margin-top: 2rem;">
                            <button type="button" class="btn btn-secondary" onclick="irAlPaso(3)">← Anterior</button>
                        </div>
                    </div>
                </section>
            </div>

            <!-- ===================================================================
                 VISTA 2: MODO AVANZADO (WORKBENCH DE OPERADOR)
                 =================================================================== -->
            <div id="viewWorkbench" style="display: none;">
                <!-- Tarjeta de Conexión directa en Modo Avanzado -->
                <div class="card" style="margin-bottom: 1.5rem;">
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
                        <div style="display: flex; align-items: center; gap: 0.6rem;">
                            <span style="font-size: 1.25rem;">🔌</span>
                            <h3 style="margin: 0;">Conexión Directa con el Nodo</h3>
                        </div>
                        <div id="workbenchStatusPill" class="badge-tag">🔌 {{ __('portal.configurator.disconnected') }}</div>
                    </div>
                    <div class="card-body">
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1rem; align-items: end;">
                            <div class="field-group">
                                <label for="transportSelectWorkbench" class="field-label">Método de conexión directa</label>
                                <select id="transportSelectWorkbench" class="form-select">
                                    <option value="serial">USB Serial (Cable conectado al ordenador)</option>
                                    <option value="bluetooth">Bluetooth (Inalámbrico Web BLE)</option>
                                    <option value="http">HTTP(S) local (Nodo en la misma WiFi)</option>
                                </select>
                            </div>
                            <div id="httpIpGroupWorkbench" style="display: none;" class="field-group">
                                <label for="httpIpInputWorkbench" class="field-label">Dirección IP o Host del nodo</label>
                                <input id="httpIpInputWorkbench" type="text" class="form-control" placeholder="192.168.1.100 o meshtastic.local" />
                            </div>
                            <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                                <button id="btnConnectWorkbench" type="button" class="btn btn-primary" onclick="conectarDispositivo('workbench')">
                                    ⚡ Conectar al nodo
                                </button>
                                <button id="btnDisconnectWorkbench" type="button" class="btn btn-secondary" disabled onclick="desconectarDispositivo()">
                                    Desconectar
                                </button>
                                <button id="btnDownloadLiveHeader" type="button" class="btn btn-secondary" disabled onclick="descargarConfiguracionNodo()">
                                    📥 Leer del nodo
                                </button>
                            </div>
                        </div>
                        <div style="margin-top: 0.85rem; font-size: 0.85rem; color: var(--color-texto-2); line-height: 1.45;">
                            💡 <em>Para Web Serial (USB) se requiere un navegador Chromium (Google Chrome, Edge, Brave u Opera en escritorio). Al pulsar en conectar, selecciona el puerto serie/USB asignado a tu radio.</em>
                        </div>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.5rem;">
                    <!-- Tarjeta 1: Configuración Actual en el Nodo -->
                    <div class="card">
                        <div class="card-header">
                            <h3>Configuración en el Nodo</h3>
                            <button id="btnDownloadLive" type="button" class="btn btn-secondary" style="padding: 0.35rem 0.75rem; font-size: 0.85rem;" disabled onclick="descargarConfiguracionNodo()">Leer del nodo</button>
                        </div>
                        <div class="card-body">
                            <textarea id="liveYamlTextarea" class="form-control yaml-textarea" readonly placeholder="Conecta tu nodo para inspeccionar la configuración actual..."></textarea>
                        </div>
                    </div>

                    <!-- Tarjeta 2: Configuración Deseada -->
                    <div class="card">
                        <div class="card-header">
                            <h3>Configuración Deseada</h3>
                            <div style="display: flex; gap: 0.5rem;">
                                <button type="button" class="btn btn-secondary" style="padding: 0.35rem 0.75rem; font-size: 0.85rem;" onclick="copiarLiveADeseado()">Usar actual</button>
                                <button id="btnUploadConfig" type="button" class="btn btn-primary" style="padding: 0.35rem 0.75rem; font-size: 0.85rem;" disabled onclick="aplicarDeseadoANodo()">Escribir al nodo</button>
                            </div>
                        </div>
                        <div class="card-body">
                            <textarea id="desiredYamlTextarea" class="form-control yaml-textarea" placeholder="El YAML generado por el configurador aparecerá aquí..." oninput="alEditarYamlDeseado()"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Tarjeta 3: Comparador visual Diff -->
                <div class="card" style="margin-top: 1.5rem;">
                    <div class="card-header">
                        <h3>Diferencias antes de escribir (Diff)</h3>
                        <span id="diffBadge" class="badge-tag">Sin comparación activa</span>
                    </div>
                    <div class="card-body">
                        <div id="diffOutputContainer" style="font-size: 0.9rem; color: var(--color-texto-2);">
                            Conéctate a tu nodo y lee su configuración para ver las diferencias exactas respecto al estándar SFNarrow.
                        </div>
                    </div>
                </div>

                <!-- Tarjeta 4: Consola de actividad -->
                <div class="card">
                    <div class="card-header">
                        <h3>Registro de Actividad y Protocolo</h3>
                        <button type="button" class="btn btn-secondary" style="padding: 0.35rem 0.75rem; font-size: 0.85rem;" onclick="limpiarLog()">Limpiar</button>
                    </div>
                    <div class="card-body">
                        <textarea id="logTextarea" class="form-control log-textarea" readonly></textarea>
                    </div>
                </div>
            </div>

            <!-- Pie de herramienta con atribución de código abierto -->
            <div class="configurador-creditos" style="text-align: center; margin-top: 3.5rem; padding-top: 1.5rem; border-top: 1px solid var(--color-borde); font-size: 0.85rem; color: var(--color-texto-2);">
                Motor de comunicación basado en el proyecto de código abierto
                <a href="https://github.com/pdxlocations/meshconfig" target="_blank" rel="noopener noreferrer" style="color: var(--color-enlace); text-decoration: underline;">pdxlocations/meshconfig</a> bajo licencia GNU GPLv3.
            </div>
        </div>
    </div>

    <x-slot:scripts>
        <script>
            window.global = window.global || window;
            window.process = window.process || { env: { NODE_ENV: "production" }, version: "", versions: {}, platform: "browser", cwd: function() { return ""; } };
        </script>
        <script src="/js/qrcode.min.js"></script>
        <script>
            if (typeof QRCode === 'undefined') {
                document.write('<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"><\/script>');
            }
        </script>
        <script type="module" src="/configurador/configurador.js"></script>
    </x-slot:scripts>
</x-layout>
