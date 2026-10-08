<x-layout :title="$titulo" :description="$descripcion" :image="$image ?? asset('img/og/og-conecta-gateway.webp')">
    <div class="contenedor seccion">
        <article style="width: 100%; margin: 0 auto;">
            <!-- Encabezado de la página -->
            <header style="margin-bottom: 2rem; border-bottom: 1px solid var(--color-borde); padding-bottom: 1.5rem;">
                <h1 style="margin-bottom: 0.75rem;">{{ $h1 }}</h1>
                <p class="lead" style="margin-bottom: 0;">{{ $descripcion }}</p>
            </header>

            <!-- Tarjeta Visual Destacada: Conexión MQTT Gateway -->
            <x-tarjeta-gateway-mqtt />

            <!-- Contenido detallado procesado desde Markdown -->
            <div class="prose" style="line-height: 1.7; font-size: 1.05rem; margin-top: 2.5rem; width: 100%;">
                {!! $html !!}
            </div>

            <!-- Banner de Emergencias -->
            <div style="margin-top: 3.5rem;">
                <x-aviso-emergencias />
            </div>
        </article>
    </div>
</x-layout>
