@props([
    'url' => null,
    'titulo' => null,
    'texto' => null,
    'align' => 'start', // 'start', 'center', 'end'
])

@php
    $targetUrl = $url ?: url()->current();
    $targetTitulo = $titulo ?: config('proyecto.nombre');
    $shareText = $texto ?: ($targetTitulo . ' — ' . config('proyecto.nombre'));
    $whatsAppUrl = 'https://api.whatsapp.com/send?text=' . urlencode($shareText . ' ' . $targetUrl);
    $telegramUrl = 'https://t.me/share/url?url=' . urlencode($targetUrl) . '&text=' . urlencode($shareText);
    $emailUrl = 'mailto:?subject=' . rawurlencode($targetTitulo) . '&body=' . rawurlencode($shareText . "\n\n" . $targetUrl);
    $uniqueId = 'share_btn_' . uniqid();
    $justifyContent = match($align) {
        'center' => 'center',
        'end' => 'flex-end',
        default => 'flex-start',
    };
@endphp

<div class="grupo-botones-compartir grupo-compartir-cobertura" style="display: flex; flex-wrap: wrap; align-items: center; justify-content: {{ $justifyContent }}; gap: 0.5rem;" {{ $attributes }}>
    <!-- WhatsApp -->
    <a
        href="{{ $whatsAppUrl }}"
        target="_blank"
        rel="noopener noreferrer"
        class="btn-compartir-red btn-compartir-whatsapp btn-share-whatsapp"
        title="{{ __('portal.share.whatsapp_title') }}"
        aria-label="{{ __('portal.share.whatsapp_title') }}"
    >
        <svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
            <path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91C2.13 13.66 2.59 15.36 3.45 16.86L2.05 22L7.3 20.63C8.75 21.41 10.38 21.83 12.04 21.83C17.5 21.83 21.95 17.38 21.95 11.92C21.95 9.27 20.92 6.78 19.05 4.91C17.18 3.03 14.69 2 12.04 2M12.05 3.67C14.25 3.67 16.31 4.53 17.87 6.09C19.42 7.65 20.28 9.72 20.28 11.92C20.28 16.46 16.58 20.15 12.04 20.15C10.56 20.15 9.11 19.76 7.85 19L7.55 18.83L4.43 19.65L5.26 16.61L5.06 16.29C4.24 15 3.8 13.47 3.8 11.91C3.81 7.37 7.5 3.67 12.05 3.67M9.53 7.07C9.36 7.07 9.09 7.14 8.86 7.39C8.63 7.64 8 8.23 8 9.44C8 10.65 8.89 11.82 9.01 11.99C9.13 12.16 10.74 14.65 13.23 15.72C15.3 16.61 15.72 16.43 16.17 16.39C16.62 16.35 17.63 15.79 17.84 15.21C18.05 14.63 18.05 14.13 17.99 14.03C17.93 13.93 17.78 13.87 17.55 13.76C17.32 13.65 16.19 13.09 15.98 13.01C15.77 12.93 15.62 12.89 15.47 13.12C15.32 13.35 14.89 13.87 14.76 14.02C14.63 14.17 14.5 14.19 14.28 14.08C14.05 13.97 13.33 13.73 12.47 12.96C11.8 12.36 11.35 11.62 11.22 11.39C11.09 11.16 11.21 11.04 11.32 10.93C11.42 10.83 11.55 10.66 11.66 10.53C11.77 10.4 11.81 10.3 11.89 10.15C11.97 10 11.93 9.87 11.87 9.76C11.81 9.65 11.36 8.54 11.17 8.09C10.99 7.65 10.8 7.71 10.66 7.7C10.53 7.7 10.38 7.69 10.23 7.69C10.08 7.69 9.83 7.75 9.63 7.97C9.43 8.19 8.86 8.72 8.86 9.82C8.86 10.92 9.66 11.98 9.77 12.13C9.88 12.28 11.34 14.52 13.56 15.48C15.42 16.28 15.8 16.12 16.2 16.08C16.6 16.04 17.5 15.54 17.69 15.02C17.88 14.5 17.88 14.05 17.82 13.96C17.76 13.87 17.63 13.82 17.42 13.72Z"/>
        </svg>
        <span>{{ __('portal.share.whatsapp') }}</span>
    </a>

    <!-- Telegram -->
    <a
        href="{{ $telegramUrl }}"
        target="_blank"
        rel="noopener noreferrer"
        class="btn-compartir-red btn-compartir-telegram btn-share-telegram"
        title="{{ __('portal.share.telegram_title') }}"
        aria-label="{{ __('portal.share.telegram_title') }}"
    >
        <svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69a.2.2 0 0 0-.05-.18c-.06-.05-.14-.03-.21-.02-.09.02-1.49.95-4.22 2.79-.4.27-.76.41-1.08.4-.36-.01-1.04-.2-1.55-.37-.63-.2-1.12-.31-1.08-.66.02-.18.27-.36.74-.55 2.92-1.27 4.86-2.11 5.83-2.51 2.78-1.16 3.35-1.36 3.73-1.36.08 0 .27.02.39.12.1.08.13.19.14.27-.01.06.01.24 0 .38z"/>
        </svg>
        <span>{{ __('portal.share.telegram') }}</span>
    </a>

    <!-- Email -->
    <a
        href="{{ $emailUrl }}"
        class="btn-compartir-red btn-compartir-email btn-share-email"
        title="{{ __('portal.share.email_title') }}"
        aria-label="{{ __('portal.share.email_title') }}"
    >
        <svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
            <polyline points="22,6 12,13 2,6"></polyline>
        </svg>
        <span>{{ __('portal.share.email') }}</span>
    </a>

    <!-- Copiar enlace -->
    <button
        type="button"
        id="{{ $uniqueId }}"
        class="btn-compartir-red btn-compartir-copiar btn-share-copiar"
        onclick="copiarEnlaceCompartirUniversal('{{ $targetUrl }}', '{{ $uniqueId }}');"
        title="{{ __('portal.share.copy_title') }}"
        aria-label="{{ __('portal.share.copy_title') }}"
    >
        <svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
            <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
        </svg>
        <span class="texto-btn-copiar-share">{{ __('portal.share.copy') }}</span>
    </button>
</div>

@once
<style>
.grupo-botones-compartir {
    display: inline-flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.5rem;
}
.btn-compartir-red {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    padding: 0.45rem 0.85rem;
    font-size: 0.82rem;
    font-weight: 600;
    line-height: 1;
    border-radius: var(--radio-sm, 6px);
    text-decoration: none;
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    border: 1px solid var(--color-borde-control);
    background: var(--color-superficie-elevada);
    color: var(--color-texto);
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.08);
}
.btn-compartir-red svg {
    flex-shrink: 0;
    transition: transform 0.2s ease;
}
.btn-compartir-red:hover svg {
    transform: scale(1.1);
}

/* WhatsApp: Verde distintivo */
.btn-compartir-whatsapp {
    color: #25d366;
    border-color: rgba(37, 211, 102, 0.35);
    background: rgba(37, 211, 102, 0.08);
}
.btn-compartir-whatsapp:hover {
    background: #25d366;
    color: #ffffff;
    border-color: #25d366;
    box-shadow: 0 4px 12px rgba(37, 211, 102, 0.35);
}

/* Telegram: Azul distintivo */
.btn-compartir-telegram {
    color: #229ed9;
    border-color: rgba(34, 158, 217, 0.35);
    background: rgba(34, 158, 217, 0.08);
}
.btn-compartir-telegram:hover {
    background: #229ed9;
    color: #ffffff;
    border-color: #229ed9;
    box-shadow: 0 4px 12px rgba(34, 158, 217, 0.35);
}

/* Email: Rojo / Coral distintivo */
.btn-compartir-email {
    color: #ea4335;
    border-color: rgba(234, 67, 53, 0.35);
    background: rgba(234, 67, 53, 0.08);
}
.btn-compartir-email:hover {
    background: #ea4335;
    color: #ffffff;
    border-color: #ea4335;
    box-shadow: 0 4px 12px rgba(234, 67, 53, 0.35);
}

/* Copiar enlace: Acento de marca */
.btn-compartir-copiar {
    color: var(--color-texto);
    border-color: var(--color-borde-control);
    background: var(--color-superficie);
}
.btn-compartir-copiar:hover {
    background: var(--color-superficie-elevada);
    border-color: var(--color-acento, #67ea94);
    color: var(--color-enlace, #15612f);
    box-shadow: 0 4px 12px rgba(103, 234, 148, 0.25);
}
.btn-compartir-copiar.btn-copiado-exito {
    background: var(--color-acento, #67ea94) !important;
    border-color: var(--color-acento, #67ea94) !important;
    color: var(--color-sobre-acento, #2c2d3c) !important;
}
</style>

<script>
function copiarEnlaceCompartirUniversal(url, btnId) {
    const targetUrl = url || window.location.href;
    const btn = document.getElementById(btnId);
    const txtSpan = btn ? btn.querySelector('.texto-btn-copiar-share') : null;
    const textoOriginal = txtSpan ? txtSpan.textContent : '';

    const feedbackExito = () => {
        if (btn) btn.classList.add('btn-copiado-exito');
        if (txtSpan) txtSpan.textContent = '✓ {{ __('portal.share.copied') }}';
        setTimeout(() => {
            if (btn) btn.classList.remove('btn-copiado-exito');
            if (txtSpan) txtSpan.textContent = textoOriginal;
        }, 2200);
    };

    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(targetUrl).then(feedbackExito).catch(() => {
            copiarFallbackShare(targetUrl, feedbackExito);
        });
    } else {
        copiarFallbackShare(targetUrl, feedbackExito);
    }
}

function copiarFallbackShare(texto, onExito) {
    const area = document.createElement('textarea');
    area.value = texto;
    area.style.position = 'fixed';
    area.style.left = '-9999px';
    document.body.appendChild(area);
    area.select();
    try {
        document.execCommand('copy');
        if (onExito) onExito();
    } catch (e) {
        console.error('Error al copiar:', e);
    } finally {
        document.body.removeChild(area);
    }
}
</script>
@endonce
