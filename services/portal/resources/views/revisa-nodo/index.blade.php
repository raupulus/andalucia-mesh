<x-layout title="Revisa tu nodo" description="Audita la configuración de radio, saltos, telemetría y batería de tu nodo Meshtastic.">
    <div class="contenedor seccion">
        <div style="max-width: 760px; margin: 0 auto;">
            <header style="text-align: center; margin-bottom: 2.5rem;">
                <h1 style="font-size: 2.5rem; font-weight: 800; color: var(--color-texto-1); margin-bottom: 0.75rem;">
                    Revisa tu nodo
                </h1>
                <p class="lead" style="margin-bottom: 0;">
                    Comprueba en 5 segundos si los parámetros de tu nodo se ajustan a las buenas prácticas de la malla y protegen el espectro de radio.
                </p>
            </header>

            <!-- Formulario de búsqueda -->
            <form action="/revisa-tu-nodo" method="GET" style="margin-bottom: 3rem;">
                <label for="campo-buscar-nodo" class="sr-only">
                    Introduce el ID o nombre del nodo
                </label>
                <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                    <input type="search" 
                           id="campo-buscar-nodo"
                           name="buscar" 
                           value="{{ $busqueda }}" 
                           placeholder="Introduce el ID (!a1b2c3d4) o nombre del nodo..." 
                           aria-label="Introduce el ID o nombre del nodo"
                           required 
                           minlength="2" 
                           style="flex-grow: 1; padding: 0.85rem 1.25rem; font-size: 1.05rem; border: 2px solid var(--color-borde); border-radius: var(--radio-md); background: var(--color-superficie); color: var(--color-texto);" />
                    <button type="submit" class="btn btn-primario" style="padding: 0.85rem 1.75rem; font-size: 1.05rem;">
                        Buscar nodo
                    </button>
                </div>
            </form>

            <!-- Resultados de búsqueda -->
            @if(!empty($busqueda))
                <section style="margin-bottom: 3.5rem;">
                    <h2 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 1rem; color: var(--color-texto-1);">
                        Resultados para "{{ $busqueda }}" ({{ count($resultados) }})
                    </h2>

                    @if(empty($resultados))
                        <div class="tarjeta" style="padding: 2rem; text-align: center; color: var(--color-texto-2);">
                            No se ha encontrado ningún nodo que coincida con ese término en los registros recientes. Comprueba que el nodo haya emitido paquetes con posición a la red.
                        </div>
                    @else
                        <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                            @foreach($resultados as $nodo)
                                <a href="/revisa-tu-nodo/{{ $nodo->id }}" class="tarjeta tarjeta-hover" style="display: flex; justify-content: space-between; align-items: center; padding: 1.25rem; text-decoration: none; color: inherit;">
                                    <div>
                                        <div style="font-size: 1.1rem; font-weight: 700; color: var(--color-texto-1);">
                                            {{ $nodo->short_name ?? $nodo->id }}
                                            @if(!empty($nodo->long_name))
                                                <span style="font-size: 0.9rem; font-weight: normal; color: var(--color-texto-2);">({{ $nodo->long_name }})</span>
                                            @endif
                                        </div>
                                        <div style="font-size: 0.85rem; color: var(--color-texto-3); margin-top: 0.25rem;">
                                            <code>{{ $nodo->id }}</code> · {{ $nodo->province ?? 'Sin provincia' }} · Rol: <code>{{ $nodo->role ?? 'CLIENT' }}</code>
                                        </div>
                                    </div>
                                    <span class="btn btn-secundario" style="padding: 0.35rem 0.75rem; font-size: 0.85rem;">
                                        Ver informe →
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </section>
            @endif

            <!-- Explicación pedagógica de la auditoría -->
            <section class="tarjeta" style="padding: 2rem; background: var(--color-superficie-sutil);">
                <h2 style="font-size: 1.35rem; font-weight: 700; margin-bottom: 1rem; color: var(--color-texto-1);">
                    ¿Qué analizamos en la salud de tu nodo?
                </h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem; font-size: 0.92rem; line-height: 1.55; color: var(--color-texto-2);">
                    <div>
                        <h3 style="font-size: 1.05rem; font-weight: 700; color: var(--color-texto-1); margin-bottom: 0.25rem;">1. Límite de saltos (Hop Limit)</h3>
                        <p style="margin: 0;">Verificamos que el nodo no emita con saltos superiores a 3 o 4. Emitir con 7 saltos genera duplicados innecesarios por toda Andalucía.</p>
                    </div>
                    <div>
                        <h3 style="font-size: 1.05rem; font-weight: 700; color: var(--color-texto-1); margin-bottom: 0.25rem;">2. Intervalo de NodeInfo y Posición</h3>
                        <p style="margin: 0;">Un nodo fijo debe emitir su posición y nombre cada 72 horas. Emitir cada pocos minutos satura el espectro de todos.</p>
                    </div>
                    <div>
                        <h3 style="font-size: 1.05rem; font-weight: 700; color: var(--color-texto-1); margin-bottom: 0.25rem;">3. Intervalo de Telemetría</h3>
                        <p style="margin: 0;">Comprobamos la cadencia de métricas del dispositivo. En nodos solares, el intervalo aconsejado es de 2 a 4 horas.</p>
                    </div>
                    <div>
                        <h3 style="font-size: 1.05rem; font-weight: 700; color: var(--color-texto-1); margin-bottom: 0.25rem;">4. Batería y Bucles de Reinicio</h3>
                        <p style="margin: 0;">Evaluamos la curva de descarga solar nocturna y detectamos reinicios anómalos causados por caídas de tensión.</p>
                    </div>
                </div>
            </section>
        </div>
    </div>
</x-layout>
