{{-- ==============================================================================
     tarjeta-configurador-automatico.blade.php
     Tarjeta vertical destacada, oxigenada y accesible que enlaza al configurador web
     de dispositivos. Conforme a DESIGN.md (Material Design, contrastes AAA, modo claro/oscuro).
     ============================================================================== --}}

@php
    $currentLang = app()->getLocale();
    $configuradorUrl = '/configurador' . ($currentLang !== 'es' ? '?lang=' . $currentLang : '');
@endphp

<section
    class="tarjeta-configurador-vertical"
    style="
        border: 2px solid var(--color-acento);
        border-radius: var(--radio-lg);
        background: var(--color-superficie);
        box-shadow: var(--sombra-2);
        padding: 2.25rem 2rem;
        margin: 2.5rem 0;
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    "
    aria-labelledby="titulo-configurador-auto"
>
    <!-- Fondo decorativo sutil en gradiente de acento -->
    <div
        style="
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, var(--color-primario) 0%, var(--color-acento) 100%);
        "
        aria-hidden="true"
    ></div>

    <!-- Cabecera de la tarjeta -->
    <div style="display: flex; flex-direction: column; gap: 0.6rem;">
        <div style="display: flex; align-items: center; gap: 0.6rem;">
            <span style="display: inline-block; width: 10px; height: 10px; border-radius: 50%; background: var(--color-acento); box-shadow: 0 0 8px var(--color-acento);" aria-hidden="true"></span>
            <span style="font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: var(--color-enlace);">
                {{ __('portal.auto_configurator_card.badge') }}
            </span>
        </div>

        <h2 id="titulo-configurador-auto" style="font-size: 1.85rem; font-weight: 800; color: var(--color-texto); line-height: 1.25; margin: 0;">
            {{ __('portal.auto_configurator_card.title') }}
        </h2>

        <p style="font-size: 1.05rem; color: var(--color-texto-2); line-height: 1.65; margin: 0; max-width: 720px;">
            {{ __('portal.auto_configurator_card.lead') }}
        </p>
    </div>

    <!-- Rejilla vertical / horizontal de ventajas clave -->
    <div
        style="
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.25rem;
            background: var(--color-superficie-sutil);
            border: 1px solid var(--color-borde);
            border-radius: var(--radio-md);
            padding: 1.5rem;
        "
    >
        <div style="display: flex; align-items: flex-start; gap: 0.75rem;">
            <span style="font-size: 1.35rem; line-height: 1; flex-shrink: 0;" aria-hidden="true">⚡</span>
            <div>
                <strong style="display: block; font-size: 0.95rem; color: var(--color-texto); margin-bottom: 0.25rem;">
                    Preset SFNarrow
                </strong>
                <span style="font-size: 0.85rem; color: var(--color-texto-2); line-height: 1.5; display: block;">
                    {{ __('portal.auto_configurator_card.feature_preset') }}
                </span>
            </div>
        </div>

        <div style="display: flex; align-items: flex-start; gap: 0.75rem;">
            <span style="font-size: 1.35rem; line-height: 1; flex-shrink: 0;" aria-hidden="true">📍</span>
            <div>
                <strong style="display: block; font-size: 0.95rem; color: var(--color-texto); margin-bottom: 0.25rem;">
                    Canal Provincial
                </strong>
                <span style="font-size: 0.85rem; color: var(--color-texto-2); line-height: 1.5; display: block;">
                    {{ __('portal.auto_configurator_card.feature_provinces') }}
                </span>
            </div>
        </div>

        <div style="display: flex; align-items: flex-start; gap: 0.75rem;">
            <span style="font-size: 1.35rem; line-height: 1; flex-shrink: 0;" aria-hidden="true">📲</span>
            <div>
                <strong style="display: block; font-size: 0.95rem; color: var(--color-texto); margin-bottom: 0.25rem;">
                    USB, BLE o QR
                </strong>
                <span style="font-size: 0.85rem; color: var(--color-texto-2); line-height: 1.5; display: block;">
                    {{ __('portal.auto_configurator_card.feature_methods') }}
                </span>
            </div>
        </div>
    </div>

    <!-- Botonera de llamada a la acción (CTA) -->
    <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1.25rem; padding-top: 0.5rem;">
        <a
            href="{{ $configuradorUrl }}"
            class="btn btn-primario"
            style="
                background: var(--color-primario);
                color: #FFFFFF;
                font-size: 1.05rem;
                font-weight: 700;
                padding: 0.85rem 1.75rem;
                border-radius: var(--radio-md);
                display: inline-flex;
                align-items: center;
                gap: 0.65rem;
                text-decoration: none;
                box-shadow: 0 4px 14px rgba(0, 122, 51, 0.3);
                transition: transform 0.15s ease, box-shadow 0.15s ease;
            "
        >
            <svg aria-hidden="true" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                <line x1="8" y1="21" x2="16" y2="21"></line>
                <line x1="12" y1="17" x2="12" y2="21"></line>
            </svg>
            <span>{{ __('portal.auto_configurator_card.btn_open') }}</span>
        </a>

        <span style="font-size: 0.85rem; color: var(--color-texto-3); line-height: 1.4; max-width: 420px;">
            {{ __('portal.auto_configurator_card.compatibility_note') }}
        </span>
    </div>
</section>
