<footer style="background-color: var(--color-superficie-sutil); border-top: 1px solid var(--color-borde); padding-top: 3.5rem; padding-bottom: 3.5rem; margin-top: auto;">
    <div class="contenedor">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 2.5rem; margin-bottom: 2.5rem;">
            <!-- Columna 1: Proyecto -->
            <div>
                <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem;">
                    <img src="{{ asset('img/logo.png') }}" alt="{{ config('proyecto.nombre') }}" style="width: 2rem; height: 2rem; object-fit: contain; flex-shrink: 0;" width="32" height="32">
                    <h3 style="font-size: 1.1rem; margin: 0; color: var(--color-texto);">{{ config('proyecto.nombre') }}</h3>
                </div>
                <p style="font-size: 0.9rem; color: var(--color-texto-2); line-height: 1.5;">
                    Red regional ciudadana de telecomunicaciones en malla LoRa Meshtastic. Proyecto libre, abierto y sin ánimo de lucro para Cádiz y Andalucía.
                </p>
                <div style="margin-top: 1rem;">
                    <a href="/qr.svg" target="_blank" style="font-size: 0.85rem; font-weight: 600;">Descargar QR del proyecto (.svg)</a>
                </div>
            </div>

            <!-- Columna 2: Navegación Institucional -->
            <div>
                <h4 style="font-size: 0.95rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--color-texto-2); margin-bottom: 0.75rem;">Documentación</h4>
                <ul style="list-style: none; display: flex; flex-direction: column; gap: 0.5rem; font-size: 0.9rem;">
                    <li><a href="/proyecto" style="text-decoration: none; color: var(--color-texto);">El proyecto</a></li>
                    <li><a href="/quien-lo-impulsa" style="text-decoration: none; color: var(--color-texto);">Quién lo impulsa</a></li>
                    <li><a href="/como-se-gestiona" style="text-decoration: none; color: var(--color-texto);">Cómo se gestiona</a></li>
                    <li><a href="/firmware" style="text-decoration: none; color: var(--color-texto);">Firmware y apps</a></li>
                    <li><a href="/api" style="text-decoration: none; color: var(--color-texto);">API pública</a></li>
                </ul>
            </div>

            <!-- Columna 3: Legal y Privacidad -->
            <div>
                <h4 style="font-size: 0.95rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--color-texto-2); margin-bottom: 0.75rem;">Transparencia y Legal</h4>
                <ul style="list-style: none; display: flex; flex-direction: column; gap: 0.5rem; font-size: 0.9rem;">
                    <li><a href="/legal/aviso-legal" style="text-decoration: none; color: var(--color-texto);">Aviso legal</a></li>
                    <li><a href="/legal/privacidad" style="text-decoration: none; color: var(--color-texto);">Política de privacidad</a></li>
                    <li><a href="/legal/cookies" style="text-decoration: none; color: var(--color-texto);">Política de cookies</a></li>
                </ul>
                <div style="margin-top: 1rem; font-size: 0.8rem; color: var(--color-texto-2); line-height: 1.4;">
                    {{ config('proyecto.legal.credito_ign') }}
                </div>
            </div>
        </div>

        <!-- Barra inferior de autoría y contacto -->
        <div style="border-top: 1px solid var(--color-borde); padding-top: 1.5rem; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 1rem; font-size: 0.85rem; color: var(--color-texto-2);">
            <div>
                Impulsado con dedicación técnica por <strong><a href="https://raupulus.dev" target="_blank" rel="noopener noreferrer" style="color: var(--color-texto-1); text-decoration: underline;">{{ config('autoria.nombre') }}</a></strong> (<code><a href="https://raupulus.dev" target="_blank" rel="noopener noreferrer">{{ config('autoria.nick') }}</a></code>) · Contacto público: <a href="mailto:{{ config('autoria.email') }}">{{ config('autoria.email') }}</a>

            </div>
            <div>
                Sin rastreadores ni cookies de terceros · Infraestructura en la UE
            </div>
        </div>
    </div>
</footer>
