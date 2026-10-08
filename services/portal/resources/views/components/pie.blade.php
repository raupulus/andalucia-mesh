@php
    $currentLang = app()->getLocale();
    $langQuery = $currentLang !== 'es' ? '?lang=' . $currentLang : '';
@endphp

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
                    {{ __('portal.footer.description') }}
                </p>
                <div style="margin-top: 1rem;">
                    <a href="/qr.svg" target="_blank" style="font-size: 0.85rem; font-weight: 600;">{{ __('portal.footer.download_qr') }}</a>
                </div>
            </div>

            <!-- Columna 2: Navegación Institucional -->
            <div>
                <h4 style="font-size: 0.95rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--color-texto-2); margin-bottom: 0.75rem;">{{ __('portal.footer.documentation') }}</h4>
                <ul style="list-style: none; display: flex; flex-direction: column; gap: 0.5rem; font-size: 0.9rem;">
                    <li><a href="/proyecto{{ $langQuery }}" style="text-decoration: none; color: var(--color-texto);">{{ __('portal.footer.project') }}</a></li>
                    <li><a href="/quien-lo-impulsa{{ $langQuery }}" style="text-decoration: none; color: var(--color-texto);">{{ __('portal.footer.who_drives') }}</a></li>
                    <li><a href="/como-se-gestiona{{ $langQuery }}" style="text-decoration: none; color: var(--color-texto);">{{ __('portal.footer.governance') }}</a></li>
                    <li><a href="/hardware{{ $langQuery }}" style="text-decoration: none; color: var(--color-texto);">{{ __('portal.hardware.heading') }}</a></li>
                    <li><a href="/sugerencias{{ $langQuery }}" style="text-decoration: none; color: var(--color-texto);">{{ __('portal.footer.suggestions_box') }}</a></li>
                    <li><a href="/firmware{{ $langQuery }}" style="text-decoration: none; color: var(--color-texto);">{{ __('portal.footer.firmware_apps') }}</a></li>
                    <li><a href="/api{{ $langQuery }}" style="text-decoration: none; color: var(--color-texto);">{{ __('portal.footer.public_api') }}</a></li>
                </ul>
            </div>

            <!-- Columna 3: Legal y Privacidad -->
            <div>
                <h4 style="font-size: 0.95rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--color-texto-2); margin-bottom: 0.75rem;">{{ __('portal.footer.transparency_legal') }}</h4>
                <ul style="list-style: none; display: flex; flex-direction: column; gap: 0.5rem; font-size: 0.9rem;">
                    <li><a href="/legal/aviso-legal{{ $langQuery }}" style="text-decoration: none; color: var(--color-texto);">{{ __('portal.footer.legal_notice') }}</a></li>
                    <li><a href="/legal/privacidad{{ $langQuery }}" style="text-decoration: none; color: var(--color-texto);">{{ __('portal.footer.privacy_policy') }}</a></li>
                    <li><a href="/legal/cookies{{ $langQuery }}" style="text-decoration: none; color: var(--color-texto);">{{ __('portal.footer.cookies_policy') }}</a></li>
                </ul>
                <div style="margin-top: 1rem; font-size: 0.8rem; color: var(--color-texto-2); line-height: 1.4;">
                    {{ config('proyecto.legal.credito_ign') }}
                </div>
            </div>
        </div>

        <!-- Barra inferior de autoría y contacto -->
        <div style="border-top: 1px solid var(--color-borde); padding-top: 1.5rem; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 1rem; font-size: 0.85rem; color: var(--color-texto-2);">
            <div>
                {{ __('portal.footer.powered_by') }} <strong><a href="https://raupulus.dev" target="_blank" rel="noopener noreferrer" style="color: var(--color-texto-1); text-decoration: underline;">{{ config('autoria.nombre') }}</a></strong> (<code><a href="https://raupulus.dev" target="_blank" rel="noopener noreferrer">{{ config('autoria.nick') }}</a></code>) · {{ __('portal.footer.public_contact') }} <a href="mailto:{{ config('autoria.email') }}">{{ config('autoria.email') }}</a>
            </div>
            <div>
                {{ __('portal.footer.no_trackers') }}
            </div>
        </div>
    </div>
</footer>
