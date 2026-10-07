@props([
    'tarjeta' => null,
    'titulo' => null,
    'descripcion' => null,
    'url' => null,
    'imagen' => null,
    'servicioClave' => null,
    'estado' => 'ok',
])

@php
    if ($tarjeta) {
        $titulo = $tarjeta['titulo'] ?? $titulo;
        $descripcion = $tarjeta['descripcion'] ?? $descripcion;
        $url = $tarjeta['url'] ?? $url;
        $imagen = $tarjeta['imagen'] ?? $imagen;
        $servicioClave = $tarjeta['servicio_clave'] ?? $servicioClave;
        $estado = $tarjeta['estado'] ?? $estado;
    }
@endphp

<a href="{{ $url }}" class="tarjeta tarjeta-hover" style="display: flex; flex-direction: column; text-decoration: none; color: inherit; height: 100%;">
    <!-- Zona de Imagen 16:9 con fondo suave de acento -->
    <div style="aspect-ratio: 16 / 9; background-color: var(--color-acento-suave); display: flex; align-items: center; justify-content: center; position: relative; border-bottom: 1px solid var(--color-borde); overflow: hidden;">
        @if($imagen)
            <img src="{{ $imagen }}" alt="Ilustración representativa de {{ $titulo }}" style="width: 100%; height: 100%; object-fit: cover; display: block;" loading="lazy">
        @else
            <div style="font-size: 2.5rem; font-weight: 800; color: var(--color-enlace);">
                {{ substr($titulo, 0, 1) }}
            </div>
        @endif

        @if($servicioClave)
            <div style="position: absolute; top: 0.75rem; right: 0.75rem; display: flex; align-items: center; gap: 0.35rem; background: var(--color-superficie); padding: 0.2rem 0.5rem; border-radius: var(--radio-full); font-size: 0.75rem; font-weight: 600; box-shadow: var(--sombra-1);">
                <span style="width: 8px; height: 8px; border-radius: 50%; background-color: {{ $estado === 'ok' ? 'var(--mapa-verde)' : 'var(--color-critico-texto)' }}; display: inline-block;"></span>
                <span>{{ $estado === 'ok' ? 'Activo' : 'Pausado' }}</span>
            </div>
        @endif
    </div>

    <!-- Contenido de Texto -->
    <div style="padding: 1.25rem; display: flex; flex-direction: column; flex-grow: 1;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
            <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--color-texto);">{{ $titulo }}</h3>
            <span style="color: var(--color-enlace); font-size: 1.25rem; font-weight: bold;" aria-hidden="true">→</span>
        </div>
        <p style="font-size: 0.9rem; color: var(--color-texto-2); line-height: 1.45; margin-bottom: 0; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
            {{ $descripcion }}
        </p>
    </div>
</a>
