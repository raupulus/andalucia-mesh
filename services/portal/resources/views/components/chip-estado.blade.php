@props([
    'nivel' => null,
    'tipo' => null,
    'texto' => '',
])

@php
$nivelReal = $nivel ?? $tipo ?? 'info';
$icono = match($nivelReal) {
    'critico' => '✕',
    'aviso' => '▲',
    'correcto' => '✓',
    'neutro' => '—',
    default => 'ℹ',
};
$clase = 'chip-' . $nivelReal;
@endphp

<span {{ $attributes->merge(['class' => 'chip ' . $clase]) }}>
    <span aria-hidden="true" style="font-weight: 800; font-size: 0.75rem;">{{ $icono }}</span>
    <span>{{ $texto ?: $slot }}</span>
</span>

