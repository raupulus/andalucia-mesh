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

        <!-- Tarjeta horizontal centrada: Código Fuente / Repositorios -->
        <div class="tarjeta-repo-footer">
            <div class="tarjeta-repo-footer-info">
                <div style="display: flex; align-items: center; gap: 0.65rem; margin-bottom: 0.25rem;">
                    <img src="{{ asset('img/logo.png') }}" alt="{{ config('proyecto.nombre') }}" style="width: 1.5rem; height: 1.5rem; object-fit: contain; flex-shrink: 0;" width="24" height="24">
                    <span style="font-size: 0.95rem; font-weight: 700; color: var(--color-texto); letter-spacing: 0.02em; text-transform: uppercase;">
                        {{ __('portal.footer.source_code_title') }}
                    </span>
                </div>
                <p style="font-size: 0.85rem; color: var(--color-texto-2); margin-bottom: 0; line-height: 1.4;">
                    {{ __('portal.footer.source_code_desc') }}
                </p>
            </div>
            <div class="tarjeta-repo-footer-links">
                <a href="{{ config('proyecto.repositorios.gitlab') }}" target="_blank" rel="noopener noreferrer" class="badge-repo-link" title="GitLab ({{ __('portal.footer.repo_main') }})">
                    <svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" style="color: #e24329; flex-shrink: 0;">
                        <path d="M23.955 13.587l-1.342-4.135-2.664-8.189c-.135-.423-.73-.423-.867 0L16.418 9.45H7.582L4.918 1.263c-.136-.423-.731-.423-.867 0L1.387 9.452.045 13.587c-.121.375.014.786.331 1.015L12 23.354 23.624 14.6c.317-.228.452-.639.331-1.013z"/>
                    </svg>
                    <span>GitLab</span>
                    <span style="font-size: 0.75rem; opacity: 0.85; font-weight: 500;">({{ __('portal.footer.repo_main') }})</span>
                </a>
                <a href="{{ config('proyecto.repositorios.github') }}" target="_blank" rel="noopener noreferrer" class="badge-repo-link" title="GitHub ({{ __('portal.footer.repo_mirror') }})">
                    <svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" style="flex-shrink: 0;">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.53 1.032 1.53 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"/>
                    </svg>
                    <span>GitHub</span>
                    <span style="font-size: 0.75rem; opacity: 0.85; font-weight: 500;">({{ __('portal.footer.repo_mirror') }})</span>
                </a>
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
