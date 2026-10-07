<x-layout :title="config('proyecto.nombre') . ' — Red Regional Comunitaria LoRa Meshtastic en Andalucía'">
    <div class="contenedor">
        <!-- 1. Presentación institucional -->
        <header class="seccion" style="padding-top: 3rem; padding-bottom: 2rem; display: flex; align-items: center; justify-content: space-between; gap: 2rem; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 280px;">
                <h1 style="font-size: 2.75rem; font-weight: 800; letter-spacing: -0.03em; margin-bottom: 1rem; color: var(--color-texto-1);">
                    {{ config('proyecto.nombre') }}
                </h1>
                <p class="lead" style="max-width: 760px; font-size: 1.25rem; color: var(--color-texto-2); line-height: 1.5; margin-bottom: 0;">
                    Red regional comunitaria de radioenlaces de largo alcance LoRa Meshtastic en Andalucía. Comunicación abierta, descentralizada y libre entre comarcas sin dependencia de internet ni operadoras.
                </p>
            </div>
            <div style="flex-shrink: 0; display: flex; align-items: center; justify-content: center;">
                <img src="{{ asset('img/logo.png') }}" alt="{{ config('proyecto.nombre') }}" style="width: 130px; height: 130px; object-fit: contain; filter: drop-shadow(0 12px 24px rgba(0, 0, 0, 0.15));" width="130" height="130">
            </div>
        </header>


        <!-- 2. Tres tarjetas fijas (en orden estricto) -->
        <section aria-label="Servicios principales de la red" style="margin-bottom: 3.5rem;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem;">
                @foreach($tarjetas as $tarjeta)
                    <x-tarjeta-servicio :tarjeta="$tarjeta" />
                @endforeach
            </div>
        </section>

        <!-- 3. Mapa provincial de Andalucía -->
        <section class="seccion" style="padding-top: 1rem; padding-bottom: 3.5rem;" aria-label="Mapa provincial y saturación">
            <header style="margin-bottom: 1.5rem;">
                <h2 style="font-size: 1.85rem; font-weight: 700; margin-bottom: 0.5rem; color: var(--color-texto-1);">
                    Malla en Andalucía
                </h2>
                <p style="color: var(--color-texto-2); margin-bottom: 0;">
                    Estado en directo de los nodos y ocupación estimada del canal en cada provincia.
                </p>
            </header>

            <x-mapa-provincias :datos="$datosMapa" :ventana="$ventana" />
        </section>

        <!-- 4. Resumen: Configura tu nodo -->
        <section class="seccion" style="border-top: 1px solid var(--color-borde); padding-top: 3.5rem; padding-bottom: 3.5rem;">
            <div style="max-width: 820px;">
                <h2 style="font-size: 1.85rem; font-weight: 700; margin-bottom: 1rem; color: var(--color-texto-1);">
                    Configura tu nodo
                </h2>
                <p style="color: var(--color-texto-2); font-size: 1.05rem; line-height: 1.6; margin-bottom: 1.5rem;">
                    Con estos ajustes de radio tu dispositivo oye a la red y evita saturar el espectro. La gran mayoría de nodos en España utilizan este preset manual de banda estrecha:
                </p>

                <div class="tarjeta" style="padding: 1.5rem; margin-bottom: 1.5rem; background: var(--color-superficie-sutil);">
                    <div style="overflow-x: auto;">
                        <table class="tabla" style="width: 100%; border-collapse: collapse; font-size: 0.92rem;">
                            <tbody>
                                <tr style="border-bottom: 1px solid var(--color-borde);">
                                    <td style="padding: 0.5rem 0; font-weight: 600; width: 40%;">Región</td>
                                    <td style="padding: 0.5rem 0;"><code>{{ config('proyecto.lora.region') }}</code></td>
                                </tr>
                                <tr style="border-bottom: 1px solid var(--color-borde);">
                                    <td style="padding: 0.5rem 0; font-weight: 600;">Usar preset (Predefined)</td>
                                    <td style="padding: 0.5rem 0;"><span class="chip chip-info" style="font-size: 0.8rem; font-weight: 600;">Desactivado</span> (para campos personalizados)</td>
                                </tr>
                                <tr style="border-bottom: 1px solid var(--color-borde);">
                                    <td style="padding: 0.5rem 0; font-weight: 600;">Ancho de banda · SF · CR</td>
                                    <td style="padding: 0.5rem 0;">BW <strong>{{ config('proyecto.lora.bandwidth') }}</strong> (62.5 kHz) · SF<strong>{{ config('proyecto.lora.spread_factor') }}</strong> · CR4/<strong>{{ config('proyecto.lora.coding_rate') }}</strong></td>
                                </tr>
                                <tr style="border-bottom: 1px solid var(--color-borde);">
                                    <td style="padding: 0.5rem 0; font-weight: 600;">Frequency slot / Override</td>
                                    <td style="padding: 0.5rem 0;">Slot <strong>{{ config('proyecto.lora.frequency_slot') }}</strong> (o <code>{{ config('proyecto.lora.frequency_mhz') }} MHz</code>)</td>
                                </tr>
                                <tr style="border-bottom: 1px solid var(--color-borde);">
                                    <td style="padding: 0.5rem 0; font-weight: 600;">Canal principal (0)</td>
                                    <td style="padding: 0.5rem 0;"><code>{{ config('proyecto.canales.primario') }}</code> (clave PSK <code>{{ config('proyecto.canales.clave_defecto') }}</code>)</td>
                                </tr>
                                <tr style="border-bottom: 1px solid var(--color-borde);">
                                    <td style="padding: 0.5rem 0; font-weight: 600;">Límite de saltos</td>
                                    <td style="padding: 0.5rem 0;">Recomendado <strong>3–4</strong> saltos (5 en <code>CLIENT_MUTE</code> o extremos)</td>
                                </tr>

                                <tr>
                                    <td style="padding: 0.5rem 0; font-weight: 600;">Rol sugerido</td>
                                    <td style="padding: 0.5rem 0;"><code>CLIENT_MUTE</code> en la mayoría de nodos personales; <code>CLIENT</code> si está en exterior despejado</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <a href="/configura-tu-nodo" class="btn btn-secundario">
                    Guía completa de configuración de radio →
                </a>
            </div>
        </section>

        <!-- 5. Resumen: Sube los datos de tu nodo (Gateways) -->
        <section class="seccion" style="border-top: 1px solid var(--color-borde); padding-top: 3.5rem; padding-bottom: 3.5rem;">
            <div style="max-width: 820px;">
                <h2 style="font-size: 1.85rem; font-weight: 700; margin-bottom: 1rem; color: var(--color-texto-1);">
                    Sube los datos de tu nodo
                </h2>
                <p style="color: var(--color-texto-2); font-size: 1.05rem; line-height: 1.6; margin-bottom: 1.5rem;">
                    Si tu nodo tiene acceso a internet (WiFi, Ethernet o app del móvil), puede actuar como gateway subiendo lo que escucha por radio a nuestro servidor para que aparezca en los mapas y estadísticas. Solo subida: ningún dato de internet se reenvía a la radio.
                </p>

                <div class="tarjeta" style="padding: 1.5rem; margin-bottom: 1.5rem; background: var(--color-superficie-sutil);">
                    <ul style="margin: 0; padding-left: 1.25rem; line-height: 1.7; font-size: 0.95rem;">
                        <li>Servidor MQTT público: <code>{{ config('proyecto.mqtt.host_publico') }}</code> (puerto <strong>{{ config('proyecto.mqtt.puerto_tls') }} con TLS</strong>)</li>
                        <li>Usuario: <code>{{ config('proyecto.mqtt.gateway_user') }}</code> · Contraseña: <code>{{ config('proyecto.mqtt.gateway_password') }}</code></li>
                        <li>Topic raíz: <code>{{ config('proyecto.mqtt.topic_root') }}</code></li>
                        <li>Uplink: activado · Downlink: <strong>siempre desactivado</strong></li>
                    </ul>
                </div>

                <a href="/conecta-tu-gateway" class="btn btn-secundario">
                    Instrucciones para conectar tu gateway MQTT →
                </a>
            </div>
        </section>

        <!-- 6. Resumen: Quién está detrás -->
        <section class="seccion" style="border-top: 1px solid var(--color-borde); padding-top: 3.5rem; padding-bottom: 3.5rem;">
            <div style="max-width: 820px;">
                <h2 style="font-size: 1.85rem; font-weight: 700; margin-bottom: 1rem; color: var(--color-texto-1);">
                    Quién está detrás
                </h2>
                <p style="color: var(--color-texto-2); font-size: 1.05rem; line-height: 1.6; margin-bottom: 1.5rem;">
                    Andalucía Mesh es una iniciativa libre, personal y sin ánimo de lucro impulsada por <strong><a href="https://raupulus.dev" target="_blank" rel="noopener noreferrer" style="color: var(--color-texto-1); text-decoration: underline;">{{ config('autoria.nombre') }}</a></strong> (<code><a href="https://raupulus.dev" target="_blank" rel="noopener noreferrer">{{ config('autoria.nick') }}</a></code>) desde Chipiona (Cádiz), orientada a vertebrar una infraestructura de comunicaciones de emergencia y experimentación ciudadana para toda la región.
                </p>


                <a href="/quien-lo-impulsa" class="btn btn-secundario">
                    Conoce más sobre quién impulsa el proyecto →
                </a>
            </div>
        </section>

        <!-- 7. Aviso legal y descargo de emergencias -->
        <div style="margin-bottom: 3.5rem;">
            <x-aviso-emergencias />
        </div>
    </div>
</x-layout>
