{{--
    Componente: x-aviso-pruebas
    Aviso flotante global persistente/minimizable que informa a los usuarios
    que la plataforma está en fase de pruebas con datos no 100% verificados.
--}}
<aside id="aviso-flotante-pruebas" 
       class="aviso-flotante-pruebas" 
       role="status" 
       aria-live="polite" 
       aria-label="{{ __('portal.testing_notice.aria_label') }}">
    <!-- Tarjeta desplegada del aviso -->
    <div class="aviso-pruebas-tarjeta" id="aviso-pruebas-contenido">
        <div class="aviso-pruebas-icono" aria-hidden="true">
            <span>⚠️</span>
        </div>
        <div class="aviso-pruebas-cuerpo">
            <div class="aviso-pruebas-cabecera">
                <span class="chip chip-aviso">
                    <span aria-hidden="true" style="font-weight: 800; font-size: 0.75rem;">▲</span>
                    <span>{{ __('portal.testing_notice.badge') }}</span>
                </span>
                <button type="button" 
                        class="btn-cerrar-aviso" 
                        id="btn-minimizar-aviso"
                        aria-label="{{ __('portal.testing_notice.close') }}" 
                        title="{{ __('portal.testing_notice.close') }}">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
            <p class="aviso-pruebas-texto">
                {{ __('portal.testing_notice.message') }}
            </p>
            <div class="aviso-pruebas-acciones">
                <button type="button" class="btn-entendido-aviso" id="btn-entendido-aviso">
                    {{ __('portal.testing_notice.dismiss') }}
                </button>
            </div>
        </div>
    </div>

    <!-- Píldora minimizada (se muestra al cerrar o si ya se minimizó) -->
    <button type="button" 
            class="aviso-pruebas-pildora" 
            id="btn-expandir-aviso" 
            style="display: none;" 
            aria-label="{{ __('portal.testing_notice.reopen') }}"
            title="{{ __('portal.testing_notice.reopen') }}">
        <span class="aviso-pildora-icono" aria-hidden="true">⚠️</span>
        <span class="aviso-pildora-texto">{{ __('portal.testing_notice.badge') }}</span>
    </button>
</aside>
