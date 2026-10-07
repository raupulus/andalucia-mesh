{{-- ==============================================================================
     tarjeta-red-sfnarrow.blade.php
     Tarjeta llamativa y altamente visual con los parámetros oficiales de radio LoRa SFNarrow.
     Material Design, contraste AAA WCAG 2.1 en modo claro y oscuro.
     ============================================================================== --}}

<div class="tarjeta-sfnarrow-destacada" style="border: 2px solid var(--color-acento); border-radius: var(--radio-lg); background: var(--color-superficie); box-shadow: var(--sombra-3); overflow: hidden; margin: 2rem 0;">
    <!-- Encabezado llamativo con contraste de acento -->
    <div style="background: linear-gradient(135deg, rgba(103, 234, 148, 0.22) 0%, rgba(21, 97, 47, 0.12) 100%); padding: 1.5rem 1.75rem; border-bottom: 1px solid var(--color-borde); display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 1rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 0.6rem; margin-bottom: 0.35rem;">
                <span style="display: inline-block; width: 12px; height: 12px; border-radius: 50%; background: var(--color-acento); box-shadow: 0 0 10px var(--color-acento);"></span>
                <span style="font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: var(--color-enlace);">
                    Parámetros Oficiales de Red LoRa
                </span>
            </div>
            <h2 style="font-size: 1.75rem; font-weight: 800; margin: 0; color: var(--color-texto); line-height: 1.2;">
                Preset SFNarrow
            </h2>
            <p style="font-size: 0.95rem; color: var(--color-texto-2); margin: 0.35rem 0 0 0;">
                Estándar mayoritario de radioenlace comunitario en Andalucía y España
            </p>
        </div>
        <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 0.4rem;">
            <span class="chip chip-correcto" style="font-size: 0.85rem; font-weight: 700; padding: 0.35rem 0.75rem;">
                Banda Estrecha (62.5 kHz)
            </span>
            <span style="font-size: 0.8rem; color: var(--color-texto-3);">
                Mayor alcance · Menos colisiones
            </span>
        </div>
    </div>

    <!-- Rejilla visual de parámetros clave -->
    <div style="padding: 1.5rem 1.75rem;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
            
            <!-- Región -->
            <div style="padding: 1rem; border-radius: var(--radio-md); background: var(--color-superficie-sutil); border: 1px solid var(--color-borde);">
                <div style="font-size: 0.8rem; font-weight: 600; text-transform: uppercase; color: var(--color-texto-3); margin-bottom: 0.25rem;">
                    Región LoRa
                </div>
                <div style="font-size: 1.25rem; font-weight: 800; color: var(--color-texto); font-family: var(--fuente-mono);">
                    EU_868
                </div>
                <div style="font-size: 0.8rem; color: var(--color-texto-2); margin-top: 0.25rem;">
                    European Union 868 MHz
                </div>
            </div>

            <!-- Usar Preset (Predefined) -->
            <div style="padding: 1rem; border-radius: var(--radio-md); background: var(--color-aviso-fondo); border: 1px solid var(--color-aviso-texto);">
                <div style="font-size: 0.8rem; font-weight: 600; text-transform: uppercase; color: var(--color-aviso-texto); margin-bottom: 0.25rem;">
                    Usar Preset (Predefined)
                </div>
                <div style="font-size: 1.25rem; font-weight: 800; color: var(--color-aviso-texto);">
                    DESACTIVAR ✕
                </div>
                <div style="font-size: 0.8rem; color: var(--color-aviso-texto); margin-top: 0.25rem;">
                    Obligatorio para desbloquear campos
                </div>
            </div>

            <!-- Bandwidth -->
            <div style="padding: 1rem; border-radius: var(--radio-md); background: var(--color-superficie-sutil); border: 1px solid var(--color-borde);">
                <div style="font-size: 0.8rem; font-weight: 600; text-transform: uppercase; color: var(--color-texto-3); margin-bottom: 0.25rem;">
                    Bandwidth (Ancho de Banda)
                </div>
                <div style="font-size: 1.25rem; font-weight: 800; color: var(--color-texto); font-family: var(--fuente-mono);">
                    62 <span style="font-size: 0.95rem; font-weight: 600; color: var(--color-texto-2);">(o 62.5 kHz)</span>
                </div>
                <div style="font-size: 0.8rem; color: var(--color-texto-2); margin-top: 0.25rem;">
                    Frente a 250 kHz de fábrica
                </div>
            </div>

            <!-- Spreading Factor -->
            <div style="padding: 1rem; border-radius: var(--radio-md); background: var(--color-superficie-sutil); border: 1px solid var(--color-borde);">
                <div style="font-size: 0.8rem; font-weight: 600; text-transform: uppercase; color: var(--color-texto-3); margin-bottom: 0.25rem;">
                    Spreading Factor (SF)
                </div>
                <div style="font-size: 1.25rem; font-weight: 800; color: var(--color-texto); font-family: var(--fuente-mono);">
                    7
                </div>
                <div style="font-size: 0.8rem; color: var(--color-texto-2); margin-top: 0.25rem;">
                    Factor de dispersión LoRa
                </div>
            </div>

            <!-- Coding Rate -->
            <div style="padding: 1rem; border-radius: var(--radio-md); background: var(--color-superficie-sutil); border: 1px solid var(--color-borde);">
                <div style="font-size: 0.8rem; font-weight: 600; text-transform: uppercase; color: var(--color-texto-3); margin-bottom: 0.25rem;">
                    Coding Rate (CR)
                </div>
                <div style="font-size: 1.25rem; font-weight: 800; color: var(--color-texto); font-family: var(--fuente-mono);">
                    5 <span style="font-size: 0.95rem; font-weight: 600; color: var(--color-texto-2);">(4/5)</span>
                </div>
                <div style="font-size: 0.8rem; color: var(--color-texto-2); margin-top: 0.25rem;">
                    Tasa de corrección de errores
                </div>
            </div>

            <!-- Frequency Slot & Override -->
            <div style="padding: 1rem; border-radius: var(--radio-md); background: var(--color-acento-suave); border: 1px solid var(--color-enlace);">
                <div style="font-size: 0.8rem; font-weight: 700; text-transform: uppercase; color: var(--color-enlace); margin-bottom: 0.25rem;">
                    Frequency Slot / Override
                </div>
                <div style="font-size: 1.25rem; font-weight: 800; color: var(--color-enlace); font-family: var(--fuente-mono);">
                    Slot 4 <span style="font-size: 0.95rem; font-weight: 600;">(869.618 MHz)</span>
                </div>
                <div style="font-size: 0.8rem; color: var(--color-texto-2); margin-top: 0.25rem;">
                    Frequency Override: 869.618 o 869.6188
                </div>
            </div>

            <!-- Canal Principal -->
            <div style="padding: 1rem; border-radius: var(--radio-md); background: var(--color-superficie-sutil); border: 1px solid var(--color-borde);">
                <div style="font-size: 0.8rem; font-weight: 600; text-transform: uppercase; color: var(--color-texto-3); margin-bottom: 0.25rem;">
                    Nombre del Canal 0 (Principal)
                </div>
                <div style="font-size: 1.25rem; font-weight: 800; color: var(--color-texto); font-family: var(--fuente-mono);">
                    SFNarrow
                </div>
                <div style="font-size: 0.8rem; color: var(--color-texto-2); margin-top: 0.25rem;">
                    Nombre del preset y canal primario
                </div>
            </div>

            <!-- Clave PSK -->
            <div style="padding: 1rem; border-radius: var(--radio-md); background: var(--color-superficie-sutil); border: 1px solid var(--color-borde);">
                <div style="font-size: 0.8rem; font-weight: 600; text-transform: uppercase; color: var(--color-texto-3); margin-bottom: 0.25rem;">
                    Clave PSK Canal 0
                </div>
                <div style="font-size: 1.25rem; font-weight: 800; color: var(--color-texto); font-family: var(--fuente-mono);">
                    AQ==
                </div>
                <div style="font-size: 0.8rem; color: var(--color-texto-2); margin-top: 0.25rem;">
                    Clave pública comunitaria por defecto
                </div>
            </div>

            <!-- Límite de Saltos -->
            <div style="padding: 1rem; border-radius: var(--radio-md); background: var(--color-superficie-sutil); border: 1px solid var(--color-borde);">
                <div style="font-size: 0.8rem; font-weight: 600; text-transform: uppercase; color: var(--color-texto-3); margin-bottom: 0.25rem;">
                    Límite de Saltos (Hop Limit)
                </div>
                <div style="font-size: 1.25rem; font-weight: 800; color: var(--color-texto); font-family: var(--fuente-mono);">
                    3 <span style="font-size: 0.95rem; font-weight: 600; color: var(--color-texto-2);">(máx. 4)</span>
                </div>
                <div style="font-size: 0.8rem; color: var(--color-texto-2); margin-top: 0.25rem;">
                    Evita saturar el canal de radio
                </div>
            </div>

        </div>

        <!-- Alerta para administración remota -->
        <div style="padding: 0.9rem 1.15rem; background: var(--color-superficie-sutil); border-left: 4px solid var(--color-enlace); border-radius: var(--radio-sm); font-size: 0.9rem; color: var(--color-texto-2); line-height: 1.5;">
            <strong>💡 Consejo para nodos remotos:</strong> Si estás configurando un nodo administrado a distancia, cambia siempre los ajustes en este orden exacto para no quedarte sin conexión: 
            <code>1. LoRa del nodo remoto</code> → <code>2. LoRa del nodo local</code> → <code>3. Canal del nodo remoto</code> → <code>4. Canal del nodo local</code>.
        </div>
    </div>
</div>
