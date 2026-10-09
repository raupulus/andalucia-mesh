<x-layout :title="$titulo" :description="$descripcion" :image="$image ?? asset('img/og/og-configura-nodo.webp')">
    <div class="contenedor seccion">
        <article style="width: 100%; margin: 0 auto;">
            <!-- Encabezado de la página -->
            <header class="encabezado-pagina" style="margin-bottom: 2rem; padding-bottom: 1.5rem;">
                <h1 class="titulo-pagina-vistoso" style="margin-bottom: 0.75rem;">{{ $h1 }}</h1>
                <p class="lead" style="margin-bottom: 1.5rem;">{{ $descripcion }}</p>
                <x-separador-bandera />
            </header>

            <!-- Tarjeta Visual Destacada: Configuración de Red LoRa SFNarrow -->
            <x-tarjeta-red-sfnarrow />

            <!-- Tarjeta Vertical Destacada: Configurador Automático de Nodos -->
            <x-tarjeta-configurador-automatico />

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
