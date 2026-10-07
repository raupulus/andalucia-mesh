<x-layout :title="$titulo" :description="$descripcion">
    <div class="contenedor seccion">
        <article style="max-width: 960px; margin: 0 auto;">
            <!-- Encabezado de la página -->
            <header style="margin-bottom: 3rem; border-bottom: 1px solid var(--color-borde); padding-bottom: 2rem;">
                <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem;">
                    <span class="chip chip-correcto" style="font-weight: 700;">Integraciones Oficiales</span>
                    <span style="font-size: 0.85rem; color: var(--color-texto-3);">Telegram & Discord</span>
                </div>
                <h1 style="margin-bottom: 1rem; font-size: clamp(2.2rem, 4vw, 2.85rem);">{{ $h1 }}</h1>
                <p class="lead" style="margin-bottom: 0; line-height: 1.6; max-width: 840px;">{{ $descripcion }}</p>
            </header>

            <!-- 1. Qué avisan: Riesgos y Tipos -->
            <section style="margin-bottom: 3.5rem;">
                <h2 style="font-size: 1.75rem; font-weight: 800; margin-bottom: 0.75rem; color: var(--color-texto);">
                    Qué avisan los bots
                </h2>
                <p style="color: var(--color-texto-2); font-size: 1rem; line-height: 1.6; margin-bottom: 1.75rem;">
                    Cada alerta emitida por el sistema se clasifica con un <strong>nivel de riesgo</strong> y un <strong>tipo de problema</strong> para que puedas priorizar fácilmente lo que ocurre en la red comunitaria.
                </p>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 380px), 1fr)); gap: 1.5rem; margin-bottom: 1.75rem;">
                    <!-- Clasificación por Riesgo -->
                    <div class="tarjeta" style="padding: 1.5rem; background: var(--color-superficie);">
                        <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 1rem; color: var(--color-texto);">
                            Niveles de Riesgo
                        </h3>
                        <div style="display: flex; flex-direction: column; gap: 0.85rem;">
                            <div style="display: flex; align-items: flex-start; gap: 0.75rem; padding-bottom: 0.85rem; border-bottom: 1px solid var(--color-borde);">
                                <x-chip-estado nivel="critico" texto="Alto" />
                                <div style="font-size: 0.9rem; color: var(--color-texto-2); line-height: 1.45;">
                                    <strong>Fallo activo o daño a la malla:</strong> Caída de repetidores estratégicos, particiones de red o bucles de reinicio severos.
                                </div>
                            </div>
                            <div style="display: flex; align-items: flex-start; gap: 0.75rem; padding-bottom: 0.85rem; border-bottom: 1px solid var(--color-borde);">
                                <x-chip-estado nivel="aviso" texto="Medio" />
                                <div style="font-size: 0.9rem; color: var(--color-texto-2); line-height: 1.45;">
                                    <strong>Riesgo real o incidencia zonal:</strong> Baterías de infraestructura en nivel crítico o saturación elevada de canal.
                                </div>
                            </div>
                            <div style="display: flex; align-items: flex-start; gap: 0.75rem;">
                                <x-chip-estado nivel="info" texto="Bajo" />
                                <div style="font-size: 0.9rem; color: var(--color-texto-2); line-height: 1.45;">
                                    <strong>Aviso informativo preventivo:</strong> Parámetros anómalos o cambios que conviene vigilar sin daño inmediato.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Clasificación por Tipo -->
                    <div class="tarjeta" style="padding: 1.5rem; background: var(--color-superficie);">
                        <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 1rem; color: var(--color-texto);">
                            Tipos de Incidencia
                        </h3>
                        <div style="display: flex; flex-direction: column; gap: 1rem;">
                            <div style="padding-bottom: 1rem; border-bottom: 1px solid var(--color-borde);">
                                <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.35rem;">
                                    <span style="font-weight: 700; font-size: 0.95rem; color: var(--color-enlace);">Infraestructura</span>
                                </div>
                                <p style="font-size: 0.9rem; color: var(--color-texto-2); margin: 0; line-height: 1.45;">
                                    Repetidores, routers y gateways MQTT. Cubre cualquier problema que afecte a múltiples nodos o a la conectividad global (saturación, spam o caídas).
                                </p>
                            </div>
                            <div>
                                <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.35rem;">
                                    <span style="font-weight: 700; font-size: 0.95rem; color: var(--color-texto);">Clientes</span>
                                </div>
                                <p style="font-size: 0.9rem; color: var(--color-texto-2); margin: 0; line-height: 1.45;">
                                    Nodos individuales de usuario que no forman parte del troncal de la red (ej. batería baja de un nodo personal o parámetros fuera de norma).
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div style="padding: 1rem 1.25rem; background: var(--color-superficie-sutil); border-radius: var(--radio-md); border-left: 4px solid var(--color-enlace); font-size: 0.95rem; color: var(--color-texto-2);">
                    Puedes consultar todas las alertas activas e históricas en cualquier momento en el <a href="/alertas" style="font-weight: 600;">Panel de Alertas en directo →</a>
                </div>
            </section>

            <!-- 2. Tarjetas Visuales de Bots: Telegram & Discord -->
            <section style="margin-bottom: 4rem;">
                <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: flex-end; gap: 1rem; margin-bottom: 1.5rem;">
                    <div>
                        <h2 style="font-size: 1.75rem; font-weight: 800; margin-bottom: 0.35rem; color: var(--color-texto);">
                            Elige tu plataforma
                        </h2>
                        <p style="color: var(--color-texto-2); font-size: 0.95rem; margin: 0;">
                            Añade el bot a tu comunidad para mantener informados a tus compañeros de zona.
                        </p>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 360px), 1fr)); gap: 2rem;">
                    
                    <!-- Tarjeta Visual: Bot de Telegram -->
                    <div class="tarjeta" style="border: 2px solid var(--color-enlace); border-radius: var(--radio-lg); padding: 2rem; background: var(--color-superficie); box-shadow: var(--sombra-2); display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                                <span style="font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--color-enlace);">
                                    Grupos & Canales
                                </span>
                                <x-chip-estado nivel="correcto" texto="Activo" />
                            </div>

                            <h3 style="font-size: 1.65rem; font-weight: 800; margin-bottom: 0.6rem; color: var(--color-texto);">
                                Bot de Telegram
                            </h3>

                            <p style="color: var(--color-texto-2); font-size: 0.95rem; line-height: 1.55; margin-bottom: 1.5rem;">
                                Recibe alertas inmediatas en grupos y canales, o consulta telemetría y salud de la malla mediante mensaje privado.
                            </p>

                            <a href="https://t.me/{{ config('proyecto.bots.telegram_username') }}" 
                               target="_blank" 
                               rel="noopener noreferrer" 
                               class="btn btn-secundario" 
                               style="width: 100%; justify-content: center; margin-bottom: 2rem; font-weight: 700; padding: 0.75rem 1rem;">
                                Abrir &#64;{{ config('proyecto.bots.telegram_username') }} en Telegram ↗
                            </a>

                            <!-- Pasos oxigenados -->
                            <div style="display: flex; flex-direction: column; gap: 1rem;">
                                <!-- En un grupo -->
                                <div style="background: var(--color-superficie-sutil); border: 1px solid var(--color-borde); border-radius: var(--radio-md); padding: 1.25rem;">
                                    <h4 style="font-size: 1rem; font-weight: 700; margin-bottom: 0.6rem; color: var(--color-texto);">
                                        En un grupo
                                    </h4>
                                    <ol style="margin: 0; padding-left: 1.25rem; font-size: 0.9rem; color: var(--color-texto-2); line-height: 1.6;">
                                        <li style="margin-bottom: 0.4rem;">Añade a <code>&#64;{{ config('proyecto.bots.telegram_username') }}</code> como miembro.</li>
                                        <li style="margin-bottom: 0.4rem;">El bot saludará y publicará alertas con los filtros estándar.</li>
                                        <li>Los administradores pueden personalizar los filtros con <code>/levels</code> y <code>/types</code>.</li>
                                    </ol>
                                </div>

                                <!-- En un canal -->
                                <div style="background: var(--color-superficie-sutil); border: 1px solid var(--color-borde); border-radius: var(--radio-md); padding: 1.25rem;">
                                    <h4 style="font-size: 1rem; font-weight: 700; margin-bottom: 0.6rem; color: var(--color-texto);">
                                        En un canal
                                    </h4>
                                    <ol style="margin: 0; padding-left: 1.25rem; font-size: 0.9rem; color: var(--color-texto-2); line-height: 1.6;">
                                        <li style="margin-bottom: 0.4rem;">Añade el bot como <strong>administrador</strong> con permiso de publicación.</li>
                                        <li>Un administrador envía el comando (ej. <code>/levels medio alto</code>) en el canal. El bot lo aplica y elimina el mensaje para no ensuciar.</li>
                                    </ol>
                                </div>

                                <!-- En privado -->
                                <div style="background: var(--color-superficie-sutil); border: 1px solid var(--color-borde); border-radius: var(--radio-md); padding: 1.25rem;">
                                    <h4 style="font-size: 1rem; font-weight: 700; margin-bottom: 0.4rem; color: var(--color-texto);">
                                        En privado
                                    </h4>
                                    <p style="margin: 0; font-size: 0.9rem; color: var(--color-texto-2); line-height: 1.55;">
                                        Escríbele para consultar <code>/status</code>, <code>/battery</code> y <code>/routers</code>. En chat privado no envía alertas para no molestar.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div style="font-size: 0.85rem; color: var(--color-texto-3); padding-top: 1.5rem; margin-top: 1rem; border-top: 1px solid var(--color-borde);">
                            <strong>Para quitarlo:</strong> Expúlsalo del grupo o canal. El bot detectará su salida y se desactivará de inmediato.
                        </div>
                    </div>

                    <!-- Tarjeta Visual: Bot de Discord -->
                    <div class="tarjeta" style="border: 2px dashed var(--color-borde-control); border-radius: var(--radio-lg); padding: 2rem; background: var(--color-superficie); box-shadow: var(--sombra-1); display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                                <span style="font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--color-texto-3);">
                                    Servidores Comunitarios
                                </span>
                                <x-chip-estado nivel="aviso" texto="Próximamente" />
                            </div>

                            <h3 style="font-size: 1.65rem; font-weight: 800; margin-bottom: 0.6rem; color: var(--color-texto);">
                                Bot de Discord
                            </h3>

                            <p style="color: var(--color-texto-2); font-size: 0.95rem; line-height: 1.55; margin-bottom: 1.5rem;">
                                Integración comunitaria para servidores de Discord con alertas en hilos, canales dedicados y tarjetas enriquecidas.
                            </p>

                            <button type="button" 
                                    class="btn btn-secundario" 
                                    disabled 
                                    style="width: 100%; justify-content: center; margin-bottom: 2rem; font-weight: 600; opacity: 0.65; cursor: not-allowed; background: var(--color-superficie-sutil); padding: 0.75rem 1rem;"
                                    title="Integración en proceso de homologación">
                                Invitar bot a tu servidor (Próximamente)
                            </button>

                            <!-- Aviso de Próximamente -->
                            <div style="background: var(--color-aviso-fondo); border-left: 3px solid var(--color-aviso-texto); border-radius: var(--radio-sm); padding: 0.85rem 1.15rem; margin-bottom: 1rem;">
                                <p style="margin: 0; font-size: 0.88rem; color: var(--color-aviso-texto); line-height: 1.5;">
                                    <strong>⏳ En desarrollo:</strong> La aplicación de Discord se encuentra en fase de pruebas de carga y homologación. La invitación pública estará disponible próximamente.
                                </p>
                            </div>

                            <!-- Pasos oxigenados de Discord -->
                            <div style="display: flex; flex-direction: column; gap: 1rem;">
                                <div style="background: var(--color-superficie-sutil); border: 1px solid var(--color-borde); border-radius: var(--radio-md); padding: 1.25rem;">
                                    <h4 style="font-size: 1rem; font-weight: 700; margin-bottom: 0.6rem; color: var(--color-texto);">
                                        Configuración en tu servidor
                                    </h4>
                                    <ol style="margin: 0; padding-left: 1.25rem; font-size: 0.9rem; color: var(--color-texto-2); line-height: 1.6;">
                                        <li style="margin-bottom: 0.4rem;">Abrirás el enlace de invitación y seleccionarás tu servidor.</li>
                                        <li style="margin-bottom: 0.4rem;">En el canal deseado para las alertas, alguien con permiso escribirá <code>/subscribe</code>.</li>
                                        <li>Podrás ajustar filtros independientes por canal usando <code>/levels</code> y <code>/types</code>.</li>
                                    </ol>
                                </div>

                                <div style="background: var(--color-superficie-sutil); border: 1px solid var(--color-borde); border-radius: var(--radio-md); padding: 1.25rem;">
                                    <h4 style="font-size: 1rem; font-weight: 700; margin-bottom: 0.4rem; color: var(--color-texto);">
                                        Múltiples canales
                                    </h4>
                                    <p style="margin: 0; font-size: 0.9rem; color: var(--color-texto-2); line-height: 1.55;">
                                        Podrás habilitar varios canales en el mismo servidor (por ejemplo, uno exclusivo para alertas críticas y otro para toda la actividad).
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div style="font-size: 0.85rem; color: var(--color-texto-3); padding-top: 1.5rem; margin-top: 1rem; border-top: 1px solid var(--color-borde);">
                            <strong>Para quitarlo:</strong> Ejecuta <code>/unsubscribe</code> en el canal o expulsa al bot del servidor para revocarlo por completo.
                        </div>
                    </div>

                </div>
            </section>

            <!-- 3. Comandos Disponibles -->
            <section style="margin-bottom: 4rem;">
                <h2 style="font-size: 1.75rem; font-weight: 800; margin-bottom: 0.5rem; color: var(--color-texto);">
                    Comandos Disponibles
                </h2>
                <p style="color: var(--color-texto-2); font-size: 1rem; margin-bottom: 1.5rem;">
                    Los mismos comandos funcionan de forma análoga en Telegram y Discord para mantener una experiencia homogénea:
                </p>

                <div class="tarjeta" style="overflow-x: auto; padding: 0.5rem; background: var(--color-superficie);">
                    <table class="tabla-accesible" style="margin-top: 0;">
                        <thead>
                            <tr>
                                <th style="width: 28%;">Comando</th>
                                <th style="width: 44%;">Qué hace</th>
                                <th style="width: 28%;">Quién puede usarlo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>/status</code></td>
                                <td>Estado general de la malla: nodos activos en 24 h, routers, gateways, saturación estimada y alertas abiertas.</td>
                                <td>Cualquiera</td>
                            </tr>
                            <tr>
                                <td><code>/battery</code></td>
                                <td>Batería de los routers y repetidores, ordenada de menor a mayor con la hora del dato.</td>
                                <td>Cualquiera</td>
                            </tr>
                            <tr>
                                <td><code>/routers</code></td>
                                <td>Listado de routers con su nivel de batería, uso de canal (<code>chutil</code>) y tiempo de emisión (<code>tx</code>).</td>
                                <td>Cualquiera</td>
                            </tr>
                            <tr>
                                <td><code>/levels [riesgos…]</code></td>
                                <td>Muestra o actualiza los riesgos suscritos en este canal. Ejemplo: <code>/levels medio alto</code></td>
                                <td>Ver: cualquiera · Modificar: administradores</td>
                            </tr>
                            <tr>
                                <td><code>/types [tipos…]</code></td>
                                <td>Muestra o actualiza los tipos suscritos en este canal. Ejemplo: <code>/types infraestructura clientes</code></td>
                                <td>Ver: cualquiera · Modificar: administradores</td>
                            </tr>
                            <tr>
                                <td><code>/settings</code></td>
                                <td>Muestra la configuración de alertas activa para el canal actual.</td>
                                <td>Cualquiera</td>
                            </tr>
                            <tr>
                                <td><code>/help</code></td>
                                <td>Ayuda rápida de sintaxis y enlace directo a esta página.</td>
                                <td>Cualquiera</td>
                            </tr>
                            <tr>
                                <td><code>/subscribe</code> <span style="font-size: 0.78rem; color: var(--color-texto-3);">(Discord)</span></td>
                                <td>Activa la recepción de alertas en el canal donde se ejecuta.</td>
                                <td>Gestores de canales</td>
                            </tr>
                            <tr>
                                <td><code>/unsubscribe</code> <span style="font-size: 0.78rem; color: var(--color-texto-3);">(Discord)</span></td>
                                <td>Desactiva las alertas en el canal donde se ejecuta.</td>
                                <td>Gestores de canales</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- 4. Ejemplos de Respuesta en Bloques de Código -->
            <section style="margin-bottom: 4rem;">
                <div style="margin-bottom: 1.75rem;">
                    <h2 style="font-size: 1.75rem; font-weight: 800; margin-bottom: 0.5rem; color: var(--color-texto);">
                        Ejemplos de Respuesta
                    </h2>
                    <p style="color: var(--color-texto-2); font-size: 1rem; margin: 0;">
                        Formato textual real y conciso que devuelven los bots al procesar cada comando de consulta:
                    </p>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 420px), 1fr)); gap: 1.5rem;">
                    
                    <!-- Ejemplo: /status -->
                    <div class="tarjeta-codigo">
                        <div class="tarjeta-codigo-cabecera">
                            <span style="font-family: var(--font-mono); color: var(--color-texto);">/status</span>
                            <span>Estado general de la red</span>
                        </div>
                        <pre class="bloque-codigo" style="margin: 0; border: none; border-radius: 0;"><code>Estado de la malla · 14:35:12
Nodos activos (24 h): 142
Routers activos (24 h): 18
Gateways publicando: 9
Saturación estimada del canal en Andalucía: 14.2 %
Cádiz 18.5 % · Sevilla 12.1 % · Málaga 15.3 % · Córdoba 9.8 %
Alertas abiertas: 0 alto · 1 medio · 3 bajo
Infraestructura: 1 · Clientes: 3</code></pre>
                    </div>

                    <!-- Ejemplo: /battery -->
                    <div class="tarjeta-codigo">
                        <div class="tarjeta-codigo-cabecera">
                            <span style="font-family: var(--font-mono); color: var(--color-texto);">/battery</span>
                            <span>Batería de repetidores</span>
                        </div>
                        <pre class="bloque-codigo" style="margin: 0; border: none; border-radius: 0;"><code>Batería de los routers (de menor a mayor)
!3a8f1c04 · Cádiz · 38 % · hace 4 min
!7b29a100 · Sevilla · 62 % · hace 11 min
!90de45f1 · Málaga · 89 % · hace 2 min
Alimentados por red: !10cc44ab, !8811ee30</code></pre>
                    </div>

                    <!-- Ejemplo: /routers -->
                    <div class="tarjeta-codigo">
                        <div class="tarjeta-codigo-cabecera">
                            <span style="font-family: var(--font-mono); color: var(--color-texto);">/routers</span>
                            <span>Métricas de canal y transmisión</span>
                        </div>
                        <pre class="bloque-codigo" style="margin: 0; border: none; border-radius: 0;"><code>Routers vistos en los últimos 7 días
!3a8f1c04 · Cádiz · batería 38 % · chutil 18 % · tx 2.4 %
!7b29a100 · Sevilla · batería 62 % · chutil 12 % · tx 1.1 %
!90de45f1 · Málaga · batería 89 % · chutil 15 % · tx 1.8 %</code></pre>
                    </div>

                    <!-- Ejemplo: /levels y /types -->
                    <div class="tarjeta-codigo">
                        <div class="tarjeta-codigo-cabecera">
                            <span style="font-family: var(--font-mono); color: var(--color-texto);">/levels · /types</span>
                            <span>Ajuste de filtros</span>
                        </div>
                        <pre class="bloque-codigo" style="margin: 0; border: none; border-radius: 0;"><code># Modificación de niveles de severidad:
> /levels medio alto
Riesgos activos en este canal: medio, alto

# Modificación de tipos de incidente:
> /types infraestructura
Tipos activos en este canal: infraestructura</code></pre>
                    </div>

                    <!-- Ejemplo: /settings -->
                    <div class="tarjeta-codigo">
                        <div class="tarjeta-codigo-cabecera">
                            <span style="font-family: var(--font-mono); color: var(--color-texto);">/settings</span>
                            <span>Configuración activa</span>
                        </div>
                        <pre class="bloque-codigo" style="margin: 0; border: none; border-radius: 0;"><code>Configuración de este canal:
Este canal recibe alertas de riesgo medio y alto, de tipo infraestructura.
Activo desde: 2026-09-15 10:20 UTC</code></pre>
                    </div>

                    <!-- Ejemplo: /help -->
                    <div class="tarjeta-codigo">
                        <div class="tarjeta-codigo-cabecera">
                            <span style="font-family: var(--font-mono); color: var(--color-texto);">/help</span>
                            <span>Sintaxis y documentación</span>
                        </div>
                        <pre class="bloque-codigo" style="margin: 0; border: none; border-radius: 0;"><code>/status · /battery · /routers · /levels · /types · /settings
Cómo usar el bot: https://{{ config('proyecto.dominio') }}/bots</code></pre>
                    </div>

                </div>
            </section>

            <!-- 5. Filtros por Defecto -->
            <section style="margin-bottom: 4rem;">
                <div class="tarjeta" style="padding: 2rem; background: var(--color-superficie); border-left: 4px solid var(--color-acento);">
                    <h2 style="font-size: 1.5rem; font-weight: 800; margin-bottom: 0.75rem; color: var(--color-texto);">
                        Filtros por defecto y personalización
                    </h2>
                    <p style="color: var(--color-texto-2); font-size: 0.95rem; line-height: 1.6; margin-bottom: 1.25rem;">
                        Cada canal o grupo tiene sus propios filtros independientes; modificarlos en uno nunca afecta a los demás. Al invitar al bot, comienza automáticamente con estos parámetros:
                    </p>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
                        <div style="padding: 1rem; border-radius: var(--radio-md); background: var(--color-superficie-sutil); border: 1px solid var(--color-borde);">
                            <div style="font-size: 0.8rem; font-weight: 700; text-transform: uppercase; color: var(--color-texto-3); margin-bottom: 0.25rem;">
                                Riesgos por defecto
                            </div>
                            <div style="font-size: 1.15rem; font-weight: 800; color: var(--color-texto); font-family: var(--font-mono);">
                                {{ config('proyecto.bots.riesgos_defecto') }}
                            </div>
                            <div style="font-size: 0.8rem; color: var(--color-texto-2); margin-top: 0.25rem;">
                                Incidencias moderadas y graves
                            </div>
                        </div>

                        <div style="padding: 1rem; border-radius: var(--radio-md); background: var(--color-superficie-sutil); border: 1px solid var(--color-borde);">
                            <div style="font-size: 0.8rem; font-weight: 700; text-transform: uppercase; color: var(--color-texto-3); margin-bottom: 0.25rem;">
                                Tipos por defecto
                            </div>
                            <div style="font-size: 1.15rem; font-weight: 800; color: var(--color-texto); font-family: var(--font-mono);">
                                {{ config('proyecto.bots.tipos_defecto') }}
                            </div>
                            <div style="font-size: 0.8rem; color: var(--color-texto-2); margin-top: 0.25rem;">
                                Solo repetidores y nodos troncales
                            </div>
                        </div>
                    </div>

                    <p style="color: var(--color-texto-2); font-size: 0.9rem; line-height: 1.5; margin: 0;">
                        <strong>Recomendaciones prácticas:</strong><br>
                        • Para recibir absolutamente todo: <code>/levels bajo medio alto</code> y <code>/types infraestructura clientes</code>.<br>
                        • Para canales de máxima urgencia: <code>/levels alto</code>.
                    </p>
                </div>
            </section>

            <!-- 6. Así es un Aviso (Resaltado Destacado) -->
            <section style="margin-bottom: 4rem;">
                <div style="margin-bottom: 1.5rem;">
                    <h2 style="font-size: 1.75rem; font-weight: 800; margin-bottom: 0.5rem; color: var(--color-texto);">
                        Así es un Aviso en Directo
                    </h2>
                    <p style="color: var(--color-texto-2); font-size: 1rem; margin: 0;">
                        Estructura limpia, concisa y sin ruido de las notificaciones que emite el bot cuando salta una regla:
                    </p>
                </div>

                <!-- Tarjeta llamativa destacada -->
                <div class="tarjeta" style="border: 2px solid var(--color-critico-texto); border-radius: var(--radio-lg); overflow: hidden; box-shadow: var(--sombra-2); background: var(--color-superficie);">
                    
                    <!-- Cabecera de la notificación -->
                    <div style="background: var(--color-critico-fondo); padding: 1.25rem 1.75rem; border-bottom: 1px solid var(--color-borde); display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 0.75rem;">
                        <div style="display: flex; align-items: center; gap: 0.6rem;">
                            <span style="font-size: 1.25rem;">🔴</span>
                            <span style="font-weight: 800; font-size: 0.95rem; color: var(--color-critico-texto); text-transform: uppercase; letter-spacing: 0.05em;">
                                Notificación de Alerta de la Malla
                            </span>
                        </div>
                        <div style="display: flex; gap: 0.5rem;">
                            <x-chip-estado nivel="critico" texto="Alto" />
                            <x-chip-estado nivel="info" texto="Infraestructura" />
                        </div>
                    </div>

                    <!-- Mensaje en bloque de código resaltado -->
                    <div style="padding: 1.75rem;">
                        <div style="margin-bottom: 1.5rem;">
                            <div style="font-size: 0.8rem; font-weight: 700; text-transform: uppercase; color: var(--color-texto-3); margin-bottom: 0.5rem; letter-spacing: 0.06em;">
                                Formato del mensaje recibido en el chat
                            </div>
                            <div class="bloque-codigo" style="margin: 0; font-size: 1rem; line-height: 1.6; background: var(--color-superficie-sutil); border: 1px solid var(--color-borde); border-left: 5px solid var(--color-critico-texto); padding: 1.25rem 1.5rem;"><code>🔴 ALTO · Infraestructura · Bucle de reinicio
!3a8f1c04 (Cádiz-Repetidor-Norte) · ROUTER · Cádiz
!3a8f1c04 se ha reiniciado 7 veces en la última hora
https://{{ config('proyecto.dominio') }}/alertas/alt-90412</code></div>
                        </div>

                        <!-- Características clave del sistema de avisos -->
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 260px), 1fr)); gap: 1rem; margin-top: 1.5rem;">
                            <div style="padding: 1rem; border-radius: var(--radio-md); background: var(--color-superficie-sutil); border: 1px solid var(--color-borde);">
                                <div style="font-weight: 700; font-size: 0.92rem; color: var(--color-texto); margin-bottom: 0.35rem; display: flex; align-items: center; gap: 0.4rem;">
                                    <span>🔵 🟠 🔴 ✅</span>
                                    <span>Severidad Inmediata</span>
                                </div>
                                <p style="font-size: 0.85rem; color: var(--color-texto-2); margin: 0; line-height: 1.5;">
                                    El icono inicial marca el estado: azul (bajo), naranja (medio), rojo (alto) y verde con marca de verificación (resuelta).
                                </p>
                            </div>

                            <div style="padding: 1rem; border-radius: var(--radio-md); background: var(--color-superficie-sutil); border: 1px solid var(--color-borde);">
                                <div style="font-weight: 700; font-size: 0.92rem; color: var(--color-texto); margin-bottom: 0.35rem; display: flex; align-items: center; gap: 0.4rem;">
                                    <span>🧵</span>
                                    <span>Actualizaciones en Hilo</span>
                                </div>
                                <p style="font-size: 0.85rem; color: var(--color-texto-2); margin: 0; line-height: 1.5;">
                                    Si una alerta cambia de riesgo o concluye, el bot responde al mensaje original para no dispersar la conversación.
                                </p>
                            </div>

                            <div style="padding: 1rem; border-radius: var(--radio-md); background: var(--color-superficie-sutil); border: 1px solid var(--color-borde);">
                                <div style="font-weight: 700; font-size: 0.92rem; color: var(--color-texto); margin-bottom: 0.35rem; display: flex; align-items: center; gap: 0.4rem;">
                                    <span>📍</span>
                                    <span>Privacidad de Ubicación</span>
                                </div>
                                <p style="font-size: 0.85rem; color: var(--color-texto-2); margin: 0; line-height: 1.5;">
                                    Solo se menciona la provincia. Jamás se publican coordenadas GPS exactas ni información privada del operador.
                                </p>
                            </div>

                            <div style="padding: 1rem; border-radius: var(--radio-md); background: var(--color-superficie-sutil); border: 1px solid var(--color-borde);">
                                <div style="font-weight: 700; font-size: 0.92rem; color: var(--color-texto); margin-bottom: 0.35rem; display: flex; align-items: center; gap: 0.4rem;">
                                    <span>🛡️</span>
                                    <span>Control Anti-Ráfagas</span>
                                </div>
                                <p style="font-size: 0.85rem; color: var(--color-texto-2); margin: 0; line-height: 1.5;">
                                    Si una anomalía afecta a varios nodos a la vez, se agrupa en un único mensaje resumen con enlace a la lista detallada.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- 7. Para no llenar tu canal, Webhooks y Privacidad -->
            <section style="margin-bottom: 4rem;">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 280px), 1fr)); gap: 1.5rem;">
                    
                    <!-- Para no llenar tu canal -->
                    <div class="tarjeta" style="padding: 1.75rem; background: var(--color-superficie);">
                        <div style="font-size: 1.5rem; margin-bottom: 0.5rem;">🔇</div>
                        <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 0.5rem; color: var(--color-texto);">
                            Para no llenar tu canal
                        </h3>
                        <p style="font-size: 0.9rem; color: var(--color-texto-2); line-height: 1.55; margin: 0;">
                            El bot aplica limitación de tasa (rate limiting) por canal, agrupa ráfagas continuas de una misma regla y solo vuelve a notificar una alerta abierta si su nivel de severidad cambia.
                        </p>
                    </div>

                    <!-- Webhooks -->
                    <div class="tarjeta" style="padding: 1.75rem; background: var(--color-superficie);">
                        <div style="font-size: 1.5rem; margin-bottom: 0.5rem;">⚡</div>
                        <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 0.5rem; color: var(--color-texto);">
                            Webhooks para Sistemas
                        </h3>
                        <p style="font-size: 0.9rem; color: var(--color-texto-2); line-height: 1.55; margin-bottom: 0.75rem;">
                            ¿Prefieres recibir las alertas en tu propia infraestructura o servidor? Enviamos peticiones <code>POST</code> firmadas a tu URL cada vez que una alerta cambia de estado.
                        </p>
                        <a href="/api" style="font-size: 0.88rem; font-weight: 600;">Ver documentación de API y Webhooks →</a>
                    </div>

                    <!-- Privacidad -->
                    <div class="tarjeta" style="padding: 1.75rem; background: var(--color-superficie);">
                        <div style="font-size: 1.5rem; margin-bottom: 0.5rem;">🔒</div>
                        <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 0.5rem; color: var(--color-texto);">
                            Privacidad Garantizada
                        </h3>
                        <p style="font-size: 0.9rem; color: var(--color-texto-2); line-height: 1.55; margin-bottom: 0.75rem;">
                            Los bots solo guardan el ID de chat o canal donde están presentes y sus filtros. No leen ni almacenan mensajes ajenos y operan en modo privacidad estricto en Telegram.
                        </p>
                        <a href="/legal/privacidad" style="font-size: 0.88rem; font-weight: 600;">Leer política de privacidad →</a>
                    </div>

                </div>
            </section>

            <!-- Banner de Emergencias -->
            <div style="margin-top: 3.5rem;">
                <x-aviso-emergencias />
            </div>
        </article>
    </div>
</x-layout>
