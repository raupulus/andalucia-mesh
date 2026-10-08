@php
    $currentLang = app()->getLocale();
    $langQuery = $currentLang !== 'es' ? '?lang=' . $currentLang : '';
@endphp

<x-layout :title="__('portal.faq.meta_title')" :description="__('portal.faq.meta_description')">
    <div class="contenedor seccion" style="max-width: 860px; margin: 0 auto; padding-top: 2rem; padding-bottom: 5rem;">
        
        <!-- Enlace superior de retorno al inicio -->
        <div style="margin-bottom: 1.5rem;">
            <a href="/{{ $langQuery }}" style="font-size: 0.9rem; text-decoration: none; color: var(--color-texto-2); display: inline-flex; align-items: center; gap: 0.35rem; transition: color 0.15s ease;">
                <svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                <span>{{ __('portal.faq.back_to_home') }}</span>
            </a>
        </div>

        <!-- Cabecera oxigenada y visual -->
        <header style="margin-bottom: 2.5rem; text-align: left;">
            <div style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.35rem 0.85rem; border-radius: var(--radio-full); background: var(--color-acento-suave); color: var(--color-enlace); font-size: 0.85rem; font-weight: 600; margin-bottom: 1rem; border: 1px solid var(--color-acento);">
                <svg aria-hidden="true" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                    <line x1="12" y1="17" x2="12.01" y2="17"></line>
                </svg>
                <span>{{ __('portal.faq.badge') }}</span>
            </div>

            <h1 style="font-size: clamp(2rem, 4vw, 2.75rem); font-weight: 800; color: var(--color-texto-1); letter-spacing: -0.03em; line-height: 1.15; margin-bottom: 0.75rem;">
                {{ __('portal.faq.heading') }}
            </h1>

            <p class="lead" style="font-size: 1.15rem; color: var(--color-texto-2); line-height: 1.6; margin: 0; max-width: 720px;">
                {{ __('portal.faq.lead') }}
            </p>
        </header>

        @if(count($faqs) > 0)
            <!-- Caja de búsqueda interactiva en tiempo real -->
            <div class="caja-buscador-faq" style="position: relative; margin-bottom: 1.75rem;">
                <div style="position: absolute; left: 1.15rem; top: 50%; transform: translateY(-50%); color: var(--color-texto-2); pointer-events: none; display: flex; align-items: center;">
                    <svg aria-hidden="true" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </div>

                <input 
                    type="search" 
                    id="buscador-faq" 
                    placeholder="{{ __('portal.faq.search_placeholder') }}" 
                    aria-label="{{ __('portal.faq.search_placeholder') }}"
                    autocomplete="off"
                    style="width: 100%; box-sizing: border-box; padding: 1rem 3rem 1rem 3rem; font-size: 1.05rem; background: var(--color-superficie); border: 1.5px solid var(--color-borde-control); border-radius: var(--radio-md); color: var(--color-texto); box-shadow: var(--sombra-1); outline: none; transition: border-color 0.2s, box-shadow 0.2s;"
                >

                <button 
                    type="button" 
                    id="btn-limpiar-busqueda" 
                    title="{{ __('portal.faq.search_clear') }}" 
                    aria-label="{{ __('portal.faq.search_clear') }}" 
                    style="display: none; position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); background: none; border: none; font-size: 1.25rem; line-height: 1; cursor: pointer; color: var(--color-texto-2); padding: 0.35rem; border-radius: var(--radio-full);"
                >
                    ✕
                </button>
            </div>

            <!-- Barra de estado y controles de expansión -->
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.75rem; margin-bottom: 1.5rem; padding: 0 0.25rem;">
                <span id="contador-resultados" style="font-size: 0.9rem; font-weight: 500; color: var(--color-texto-2);">
                    {{ __('portal.faq.search_results', ['count' => count($faqs)]) }}
                </span>

                <div style="display: inline-flex; align-items: center; gap: 0.5rem;">
                    <button 
                        type="button" 
                        id="btn-expandir-todas" 
                        class="btn-faq-toggle"
                        style="background: none; border: 1px solid var(--color-borde); border-radius: var(--radio-sm); padding: 0.4rem 0.85rem; font-size: 0.85rem; font-weight: 500; color: var(--color-texto-2); cursor: pointer; display: inline-flex; align-items: center; gap: 0.35rem;"
                    >
                        <svg aria-hidden="true" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="7 13 12 18 17 13"></polyline>
                            <polyline points="7 6 12 11 17 6"></polyline>
                        </svg>
                        <span>{{ __('portal.faq.expand_all') }}</span>
                    </button>
                    <button 
                        type="button" 
                        id="btn-contraer-todas" 
                        class="btn-faq-toggle"
                        style="background: none; border: 1px solid var(--color-borde); border-radius: var(--radio-sm); padding: 0.4rem 0.85rem; font-size: 0.85rem; font-weight: 500; color: var(--color-texto-2); cursor: pointer; display: inline-flex; align-items: center; gap: 0.35rem;"
                    >
                        <svg aria-hidden="true" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="17 11 12 6 7 11"></polyline>
                            <polyline points="17 18 12 13 7 18"></polyline>
                        </svg>
                        <span>{{ __('portal.faq.collapse_all') }}</span>
                    </button>
                </div>
            </div>

            <!-- Listado de Preguntas Frecuentes -->
            <div id="lista-faqs" style="display: flex; flex-direction: column; gap: 1rem;">
                @foreach($faqs as $faq)
                    <details 
                        class="faq-item tarjeta" 
                        id="faq-{{ $faq['id'] }}" 
                        data-faq-id="{{ $faq['id'] }}"
                        style="background: var(--color-superficie); border: 1px solid var(--color-borde); border-radius: var(--radio-lg); overflow: hidden; box-shadow: var(--sombra-1); transition: border-color 0.2s ease, box-shadow 0.2s ease;"
                    >
                        <summary style="padding: 1.25rem 1.5rem; display: flex; align-items: center; justify-content: space-between; gap: 1rem; cursor: pointer; list-style: none; user-select: none;">
                            <div style="display: flex; align-items: center; gap: 1rem; flex: 1;">
                                <span class="faq-indice" style="display: inline-flex; align-items: center; justify-content: center; width: 2rem; height: 2rem; border-radius: var(--radio-full); background: var(--color-superficie-sutil); border: 1px solid var(--color-borde); color: var(--color-enlace); font-weight: 700; font-size: 0.85rem; flex-shrink: 0;">
                                    {{ $loop->iteration }}
                                </span>
                                <h2 class="faq-pregunta" style="font-size: 1.15rem; font-weight: 600; color: var(--color-texto-1); margin: 0; line-height: 1.45;">
                                    {{ $faq['question'] }}
                                </h2>
                            </div>

                            <div style="flex-shrink: 0; display: flex; align-items: center; color: var(--color-texto-2);">
                                <svg class="faq-chevron" aria-hidden="true" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="transition: transform 0.25s ease;">
                                    <polyline points="6 9 12 15 18 9"></polyline>
                                </svg>
                            </div>
                        </summary>

                        <div class="faq-cuerpo" style="padding: 0 1.5rem 1.5rem 1.5rem;">
                            <div class="faq-divisor" style="height: 1px; background: var(--color-borde); margin-bottom: 1.25rem;"></div>
                            
                            <div class="faq-respuesta contenido-html" style="font-size: 1rem; line-height: 1.7; color: var(--color-texto);">
                                {!! $faq['answer_html'] !!}
                            </div>

                            <!-- Barra inferior de acciones (enlace permanente) -->
                            <div style="margin-top: 1.25rem; padding-top: 0.75rem; border-top: 1px dashed var(--color-borde); display: flex; align-items: center; justify-content: flex-end;">
                                <button 
                                    type="button" 
                                    class="btn-copiar-enlace" 
                                    data-id="faq-{{ $faq['id'] }}"
                                    title="{{ __('portal.faq.copy_link') }}"
                                    style="background: none; border: none; padding: 0.25rem 0.5rem; font-size: 0.8rem; color: var(--color-texto-2); cursor: pointer; display: inline-flex; align-items: center; gap: 0.35rem; border-radius: var(--radio-sm);"
                                >
                                    <svg aria-hidden="true" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path>
                                        <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path>
                                    </svg>
                                    <span class="btn-copiar-texto">{{ __('portal.faq.copy_link') }}</span>
                                </button>
                            </div>
                        </div>
                    </details>
                @endforeach
            </div>

            <!-- Estado sin resultados de búsqueda -->
            <div id="caja-sin-resultados" class="tarjeta" style="display: none; padding: 3rem 2rem; text-align: center; background: var(--color-superficie); border: 1px dashed var(--color-borde-control); border-radius: var(--radio-lg); margin-top: 1rem;">
                <div style="display: inline-flex; align-items: center; justify-content: center; width: 3.5rem; height: 3.5rem; border-radius: var(--radio-full); background: var(--color-superficie-sutil); color: var(--color-texto-2); margin-bottom: 1rem;">
                    <svg aria-hidden="true" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        <line x1="8" y1="11" x2="14" y2="11"></line>
                    </svg>
                </div>
                <h2 style="font-size: 1.35rem; font-weight: 700; color: var(--color-texto-1); margin-bottom: 0.5rem;">
                    {{ __('portal.faq.no_results_title') }}
                </h2>
                <p style="color: var(--color-texto-2); font-size: 0.95rem; line-height: 1.6; max-width: 480px; margin: 0 auto 1.5rem auto;">
                    {{ __('portal.faq.no_results_desc') }}
                </p>
                <button type="button" id="btn-resetear-busqueda" class="btn btn-secundario" style="font-size: 0.9rem; padding: 0.5rem 1.25rem;">
                    {{ __('portal.faq.btn_reset_search') }}
                </button>
            </div>

        @else
            <!-- Estado vacío cuando aún no hay preguntas registradas -->
            <div class="tarjeta" style="padding: 3.5rem 2rem; text-align: center; background: var(--color-superficie); border: 1px solid var(--color-borde); border-radius: var(--radio-lg);">
                <div style="display: inline-flex; align-items: center; justify-content: center; width: 3.5rem; height: 3.5rem; border-radius: var(--radio-full); background: var(--color-acento-suave); color: var(--color-enlace); margin-bottom: 1rem;">
                    <svg aria-hidden="true" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                    </svg>
                </div>
                <h2 style="font-size: 1.35rem; font-weight: 700; color: var(--color-texto-1); margin-bottom: 0.5rem;">
                    {{ __('portal.faq.empty_title') }}
                </h2>
                <p style="color: var(--color-texto-2); font-size: 0.95rem; line-height: 1.6; max-width: 480px; margin: 0 auto;">
                    {{ __('portal.faq.empty_desc') }}
                </p>
            </div>
        @endif

        <!-- Tarjeta de ayuda y soporte comunitario al pie -->
        <div class="tarjeta" style="margin-top: 3.5rem; padding: 2.25rem; background: var(--color-superficie-sutil); border: 1px solid var(--color-borde); border-radius: var(--radio-lg); display: flex; flex-direction: column; md:flex-row; align-items: center; justify-content: space-between; gap: 1.5rem;">
            <div style="max-width: 520px; text-align: left;">
                <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--color-texto-1); margin-bottom: 0.4rem;">
                    {{ __('portal.faq.help_card_title') }}
                </h3>
                <p style="color: var(--color-texto-2); font-size: 0.95rem; line-height: 1.55; margin: 0;">
                    {{ __('portal.faq.help_card_desc') }}
                </p>
            </div>

            <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                <a href="/revisa-tu-nodo{{ $langQuery }}" class="btn btn-secundario" style="font-size: 0.9rem; padding: 0.6rem 1.15rem;">
                    {{ __('portal.faq.help_card_btn_node_check') }}
                </a>
                <a href="/sugerencias{{ $langQuery }}" class="btn btn-primario" style="font-size: 0.9rem; padding: 0.6rem 1.15rem;">
                    {{ __('portal.faq.help_card_btn_suggestions') }}
                </a>
            </div>
        </div>

    </div>

    <!-- Estilos específicos del acordeón y foco visual -->
    <x-slot:styles>
        <style>
            details.faq-item summary::-webkit-details-marker {
                display: none;
            }
            details.faq-item[open] {
                border-color: var(--color-enlace) !important;
                box-shadow: var(--sombra-2) !important;
            }
            details.faq-item[open] summary .faq-chevron {
                transform: rotate(180deg);
                color: var(--color-enlace);
            }
            details.faq-item[open] summary .faq-indice {
                background: var(--color-acento-suave);
                border-color: var(--color-enlace);
            }
            details.faq-item summary:hover .faq-pregunta {
                color: var(--color-enlace);
            }
            details.faq-item summary:focus-visible {
                outline: 2px solid var(--color-foco);
                outline-offset: -2px;
                border-radius: var(--radio-lg);
            }
            #buscador-faq:focus {
                border-color: var(--color-enlace);
                box-shadow: 0 0 0 3px var(--color-acento-suave);
            }
            .faq-respuesta h1, .faq-respuesta h2, .faq-respuesta h3, .faq-respuesta h4, .faq-respuesta h5, .faq-respuesta h6 {
                color: var(--color-texto-1);
                font-weight: 700;
                margin-top: 1.25rem;
                margin-bottom: 0.5rem;
                line-height: 1.35;
            }
            .faq-respuesta h1 { font-size: 1.25rem; }
            .faq-respuesta h2 { font-size: 1.15rem; }
            .faq-respuesta h3 { font-size: 1.05rem; }
            .faq-respuesta h4, .faq-respuesta h5, .faq-respuesta h6 { font-size: 0.95rem; }
            .faq-respuesta p {
                margin-top: 0;
                margin-bottom: 0.85rem;
            }
            .faq-respuesta p:last-child {
                margin-bottom: 0;
            }
            .faq-respuesta strong {
                font-weight: 700;
                color: var(--color-texto-1);
            }
            .faq-respuesta em {
                font-style: italic;
            }
            .faq-respuesta del {
                text-decoration: line-through;
                opacity: 0.8;
            }
            .faq-respuesta ul, .faq-respuesta ol {
                margin-top: 0.35rem;
                margin-bottom: 0.85rem;
                padding-left: 1.5rem;
            }
            .faq-respuesta li {
                margin-bottom: 0.35rem;
            }
            .faq-respuesta blockquote {
                margin: 0.85rem 0;
                padding: 0.6rem 1rem;
                border-left: 3px solid var(--color-enlace);
                background: var(--color-superficie-sutil);
                border-radius: 0 var(--radio-sm) var(--radio-sm) 0;
                color: var(--color-texto-2);
                font-style: italic;
            }
            .faq-respuesta pre {
                background: var(--color-superficie-sutil);
                border: 1px solid var(--color-borde);
                border-radius: var(--radio-sm);
                padding: 0.75rem 1rem;
                overflow-x: auto;
                font-family: var(--fuente-mono);
                font-size: 0.9em;
                margin: 0.85rem 0;
                line-height: 1.5;
            }
            .faq-respuesta code {
                font-family: var(--fuente-mono);
                font-size: 0.88em;
                background: var(--color-superficie-sutil);
                padding: 0.15rem 0.35rem;
                border-radius: var(--radio-sm);
                border: 1px solid var(--color-borde);
            }
            .faq-respuesta pre code {
                background: transparent;
                padding: 0;
                border: none;
            }
            .faq-respuesta a {
                color: var(--color-enlace);
                text-decoration: underline;
                text-underline-offset: 3px;
                font-weight: 500;
            }
            .faq-respuesta a:hover {
                opacity: 0.85;
            }
            .faq-respuesta table {
                width: 100%;
                border-collapse: collapse;
                margin: 1rem 0;
                font-size: 0.95rem;
            }
            .faq-respuesta th, .faq-respuesta td {
                padding: 0.5rem 0.75rem;
                border: 1px solid var(--color-borde);
                text-align: left;
            }
            .faq-respuesta th {
                background: var(--color-superficie-sutil);
                font-weight: 600;
                color: var(--color-texto-1);
            }
            .faq-respuesta hr {
                border: none;
                border-top: 1px solid var(--color-borde);
                margin: 1.25rem 0;
            }
        </style>
    </x-slot:styles>

    <!-- Script de interactividad: búsqueda instantánea, expansión y enlaces directos -->
    <x-slot:scripts>
        <script>
            (function() {
                var inputBuscador = document.getElementById('buscador-faq');
                var btnLimpiar = document.getElementById('btn-limpiar-busqueda');
                var btnResetear = document.getElementById('btn-resetear-busqueda');
                var contador = document.getElementById('contador-resultados');
                var cajaSinResultados = document.getElementById('caja-sin-resultados');
                var listaFaqs = document.getElementById('lista-faqs');
                var items = document.querySelectorAll('.faq-item');
                var btnExpandir = document.getElementById('btn-expandir-todas');
                var btnContraer = document.getElementById('btn-contraer-todas');
                var textoResultadosPlantilla = '{{ __("portal.faq.search_results", ["count" => ":count"]) }}';

                if (!items.length) return;

                // 1. Filtrado en tiempo real
                function filtrar() {
                    var query = (inputBuscador ? inputBuscador.value : '').trim().toLowerCase();
                    var visibles = 0;

                    if (btnLimpiar) {
                        btnLimpiar.style.display = query.length > 0 ? 'block' : 'none';
                    }

                    items.forEach(function(item) {
                        var pregunta = item.querySelector('.faq-pregunta') ? item.querySelector('.faq-pregunta').textContent.toLowerCase() : '';
                        var respuesta = item.querySelector('.faq-respuesta') ? item.querySelector('.faq-respuesta').textContent.toLowerCase() : '';
                        
                        var coincide = query === '' || pregunta.indexOf(query) !== -1 || respuesta.indexOf(query) !== -1;

                        if (coincide) {
                            item.style.display = '';
                            visibles++;
                            if (query !== '') {
                                item.setAttribute('open', '');
                            }
                        } else {
                            item.style.display = 'none';
                        }
                    });

                    if (contador) {
                        contador.textContent = textoResultadosPlantilla.replace(':count', visibles);
                    }

                    if (cajaSinResultados) {
                        cajaSinResultados.style.display = visibles === 0 ? 'block' : 'none';
                    }
                }

                if (inputBuscador) {
                    inputBuscador.addEventListener('input', filtrar);
                    inputBuscador.addEventListener('keydown', function(e) {
                        if (e.key === 'Escape') {
                            inputBuscador.value = '';
                            filtrar();
                            inputBuscador.blur();
                        }
                    });
                }

                function limpiarBusqueda() {
                    if (inputBuscador) {
                        inputBuscador.value = '';
                        inputBuscador.focus();
                    }
                    filtrar();
                }

                if (btnLimpiar) btnLimpiar.addEventListener('click', limpiarBusqueda);
                if (btnResetear) btnResetear.addEventListener('click', limpiarBusqueda);

                // 2. Expandir y contraer todas las preguntas
                if (btnExpandir) {
                    btnExpandir.addEventListener('click', function() {
                        items.forEach(function(item) {
                            if (item.style.display !== 'none') {
                                item.setAttribute('open', '');
                            }
                        });
                    });
                }

                if (btnContraer) {
                    btnContraer.addEventListener('click', function() {
                        items.forEach(function(item) {
                            item.removeAttribute('open');
                        });
                    });
                }

                // 3. Copiar enlace permanente y anclaje
                var btnsCopiar = document.querySelectorAll('.btn-copiar-enlace');
                btnsCopiar.forEach(function(btn) {
                    btn.addEventListener('click', function(e) {
                        e.stopPropagation();
                        var faqId = btn.getAttribute('data-id');
                        var url = window.location.origin + window.location.pathname + '#' + faqId;
                        
                        if (navigator.clipboard && navigator.clipboard.writeText) {
                            navigator.clipboard.writeText(url).then(function() {
                                var spanTexto = btn.querySelector('.btn-copiar-texto');
                                if (spanTexto) {
                                    var orig = spanTexto.textContent;
                                    spanTexto.textContent = '{{ __("portal.faq.link_copied") }}';
                                    setTimeout(function() {
                                        spanTexto.textContent = orig;
                                    }, 2000);
                                }
                            });
                        }
                    });
                });

                // 4. Apertura automática ante URL con Hash (#faq-X)
                if (window.location.hash) {
                    var targetId = window.location.hash.substring(1);
                    var targetEl = document.getElementById(targetId);
                    if (targetEl && targetEl.classList.contains('faq-item')) {
                        targetEl.setAttribute('open', '');
                        setTimeout(function() {
                            targetEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }, 200);
                    }
                }
            })();
        </script>
    </x-slot:scripts>
</x-layout>
