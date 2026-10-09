{{--
  Componente: Separador con la bandera de Andalucía (verde, blanca, verde).
  Uso: <x-separador-bandera /> o <x-separador-bandera class="mi-clase" />
--}}
<div {{ $attributes->merge(['class' => 'borde-bandera-andalucia']) }} aria-hidden="true">
    <span class="linea-verde"></span>
    <span class="linea-blanca"></span>
    <span class="linea-verde"></span>
</div>
