@props([
    'idioma' => 'es',
    'tamano' => 24,
])

@php
    $lang = strtolower((string) $idioma);
    if (!in_array($lang, ['es', 'en', 'pt', 'espana', 'es-es'], true)) {
        $lang = 'es';
    }
    $uid = uniqid('flag_');
@endphp

@if($lang === 'es')
    {{-- Bandera de Andalucía (verde, blanca, verde) representativa del enfoque regional andaluz (RN-48) --}}
    <svg viewBox="0 0 32 32" width="{{ $tamano }}" height="{{ $tamano }}" {{ $attributes->merge(['class' => 'bandera-redonda']) }} aria-hidden="true" style="border-radius: 50%; box-shadow: 0 0 0 1px rgba(0,0,0,0.12); flex-shrink: 0;">
        <clipPath id="clip-and-{{ $uid }}">
            <circle cx="16" cy="16" r="16" />
        </clipPath>
        <g clip-path="url(#clip-and-{{ $uid }})">
            <rect x="0" y="0" width="32" height="10.67" fill="#007A33" />
            <rect x="0" y="10.67" width="32" height="10.66" fill="#FFFFFF" />
            <rect x="0" y="21.33" width="32" height="10.67" fill="#007A33" />
        </g>
    </svg>
@elseif($lang === 'espana' || $lang === 'es-es')
    {{-- Bandera de España (rojo, amarillo, rojo) para ámbito nacional --}}
    <svg viewBox="0 0 32 32" width="{{ $tamano }}" height="{{ $tamano }}" {{ $attributes->merge(['class' => 'bandera-redonda']) }} aria-hidden="true" style="border-radius: 50%; box-shadow: 0 0 0 1px rgba(0,0,0,0.12); flex-shrink: 0;">
        <clipPath id="clip-esp-{{ $uid }}">
            <circle cx="16" cy="16" r="16" />
        </clipPath>
        <g clip-path="url(#clip-esp-{{ $uid }})">
            <rect x="0" y="0" width="32" height="8" fill="#AA151B" />
            <rect x="0" y="8" width="32" height="16" fill="#F1BF00" />
            <rect x="0" y="24" width="32" height="8" fill="#AA151B" />
        </g>
    </svg>
@elseif($lang === 'en')
    {{-- Bandera de Reino Unido (Union Jack) para inglés --}}
    <svg viewBox="0 0 32 32" width="{{ $tamano }}" height="{{ $tamano }}" {{ $attributes->merge(['class' => 'bandera-redonda']) }} aria-hidden="true" style="border-radius: 50%; box-shadow: 0 0 0 1px rgba(0,0,0,0.12); flex-shrink: 0;">
        <clipPath id="clip-uk-{{ $uid }}">
            <circle cx="16" cy="16" r="16" />
        </clipPath>
        <g clip-path="url(#clip-uk-{{ $uid }})">
            <rect width="32" height="32" fill="#012169" />
            <path d="M 0,0 L 32,32 M 32,0 L 0,32" stroke="#FFFFFF" stroke-width="4.5" />
            <path d="M 0,0 L 16,16 M 32,0 L 16,16 M 32,32 L 16,16 M 0,32 L 16,16" stroke="#C8102E" stroke-width="2.5" />
            <path d="M 16,0 V 32 M 0,16 H 32" stroke="#FFFFFF" stroke-width="7" />
            <path d="M 16,0 V 32 M 0,16 H 32" stroke="#C8102E" stroke-width="4" />
        </g>
    </svg>
@elseif($lang === 'pt')
    {{-- Bandera de Portugal para portugués --}}
    <svg viewBox="0 0 32 32" width="{{ $tamano }}" height="{{ $tamano }}" {{ $attributes->merge(['class' => 'bandera-redonda']) }} aria-hidden="true" style="border-radius: 50%; box-shadow: 0 0 0 1px rgba(0,0,0,0.12); flex-shrink: 0;">
        <clipPath id="clip-pt-{{ $uid }}">
            <circle cx="16" cy="16" r="16" />
        </clipPath>
        <g clip-path="url(#clip-pt-{{ $uid }})">
            <rect x="0" y="0" width="13" height="32" fill="#006600" />
            <rect x="13" y="0" width="19" height="32" fill="#FF0000" />
            <circle cx="13" cy="16" r="6" fill="#FFD700" stroke="#B8860B" stroke-width="0.5" />
            <circle cx="13" cy="16" r="4.2" fill="none" stroke="#FFFFFF" stroke-width="0.6" />
            <rect x="11.5" y="13.5" width="3" height="5" rx="1" fill="#FFFFFF" stroke="#000000" stroke-width="0.3" />
            <rect x="12" y="14" width="2" height="4" fill="#C8102E" />
        </g>
    </svg>
@endif
