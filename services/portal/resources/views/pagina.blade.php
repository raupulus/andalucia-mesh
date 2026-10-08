<x-layout :title="$titulo" :description="$descripcion" :image="$image ?? null">
    <div class="contenedor seccion">
        <article style="max-width: 760px; margin: 0 auto;">
            <!-- Encabezado de la página -->
            <header style="margin-bottom: 2.5rem; border-bottom: 1px solid var(--color-borde); padding-bottom: 1.5rem;">
                <h1 style="margin-bottom: 0.75rem;">{{ $h1 }}</h1>
                <p class="lead" style="margin-bottom: 0;">{{ $descripcion }}</p>
            </header>

            <!-- Contenido HTML procesado desde Markdown -->
            <div class="prose" style="line-height: 1.7; font-size: 1.05rem;">
                {!! $html !!}
            </div>

            <!-- Bloque especial de autoría (si aplica) -->
            @if(!empty($mostrarBloqueAutoria))
                <section class="tarjeta" style="margin-top: 3rem; padding: 2rem; background-color: var(--color-superficie-sutil);">
                    <h2 style="font-size: 1.35rem; margin-bottom: 0.75rem;">{{ __('portal.authorship_title') }}</h2>
                    <p style="margin-bottom: 1rem;">
                        <strong><a href="https://raupulus.dev" target="_blank" rel="noopener noreferrer" style="color: var(--color-texto-1); text-decoration: underline;">{{ config('autoria.nombre') }}</a></strong> (<code><a href="https://raupulus.dev" target="_blank" rel="noopener noreferrer">{{ config('autoria.nick') }}</a></code>)
                    </p>

                    <p style="color: var(--color-texto-2); font-size: 0.95rem; margin-bottom: 1.25rem;">
                        {{ __('portal.authorship_bio') ?: config('autoria.presentacion') }}
                    </p>
                    <div style="display: flex; flex-wrap: wrap; gap: 1rem; align-items: center; font-size: 0.9rem;">
                        <span>{{ __('portal.public_email') }} <a href="mailto:{{ config('autoria.email') }}">{{ config('autoria.email') }}</a></span>
                        @foreach(config('autoria.webs') as $web)
                            <span>· <a href="{{ $web['url'] }}" target="_blank" rel="noopener noreferrer">{{ $web['nombre'] }}</a></span>
                        @endforeach
                    </div>
                </section>
            @endif

            <!-- Banner de Emergencias -->
            <div style="margin-top: 3rem;">
                <x-aviso-emergencias />
            </div>
        </article>
    </div>
</x-layout>
