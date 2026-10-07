<x-layout :title="$titulo" :description="$descripcion">
    <div class="contenedor seccion">
        <article style="max-width: 820px; margin: 0 auto;">
            <!-- Encabezado de la página -->
            <header style="margin-bottom: 2rem; border-bottom: 1px solid var(--color-borde); padding-bottom: 1.5rem;">
                <h1 style="margin-bottom: 0.75rem;">{{ $h1 }}</h1>
                <p class="lead" style="margin-bottom: 0;">{{ $descripcion }}</p>
            </header>

            <!-- Tarjeta Visual Destacada: Configuración de Red LoRa SFNarrow -->
            <x-tarjeta-red-sfnarrow />

            <!-- Contenido detallado procesado desde Markdown -->
            <div class="prose" style="line-height: 1.7; font-size: 1.05rem; margin-top: 2.5rem;">
                {!! $html !!}
            </div>

            <!-- Banner de Emergencias -->
            <div style="margin-top: 3.5rem;">
                <x-aviso-emergencias />
            </div>
        </article>
    </div>
</x-layout>
