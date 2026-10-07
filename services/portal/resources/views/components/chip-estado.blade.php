@props([
    'nivel' => 'info', // 'critico', 'aviso', 'info', 'correcto'
    'texto' => '',
])

@php
$icono = match($nivel) {
    'critico' => '✕',
    'aviso' => '▲',
    'correcto' => '✓',
    default => 'ℹ',
};
$clase = 'chip-' . $nivel;
@endphp

<span class="chip {{ $clase }}">
    <span aria-hidden="true" style="font-weight: 800; font-size: 0.75rem;">{{ $icono }}</span>
    <span>{{ $texto ?: $slot }}</span>
</span>
