@php
    $currentLang = app()->getLocale();
    $langQuery = $currentLang !== 'es' ? '?lang=' . $currentLang : '';
@endphp

<x-layout :title="__('portal.suggestions.meta_title')" :description="__('portal.suggestions.meta_description')">
    @if(!empty($siteKey))
        <x-slot:styles>
            <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
        </x-slot:styles>
    @endif

    <div class="contenedor seccion">
        <div style="max-width: 720px; margin: 0 auto;">
            <!-- Enlace superior de retorno -->
            <div style="margin-bottom: 1.5rem;">
                <a href="/{{ $langQuery }}" style="font-size: 0.9rem; text-decoration: none; color: var(--color-texto-2); display: inline-flex; align-items: center; gap: 0.35rem;">
                    {{ __('portal.suggestions.back_to_home') }}
                </a>
            </div>

            <header style="margin-bottom: 2rem;">
                <h1 style="font-size: 2.25rem; font-weight: 800; color: var(--color-texto-1); margin-bottom: 0.5rem; letter-spacing: -0.02em;">
                    {{ __('portal.suggestions.heading') }}
                </h1>
                <p class="lead" style="margin-bottom: 0; font-size: 1.1rem; line-height: 1.55;">
                    {{ __('portal.suggestions.lead') }}
                </p>
            </header>

            <!-- Estado de confirmación de envío exitoso -->
            <div id="caja-exito" class="tarjeta" style="padding: 2.5rem; text-align: center; margin-bottom: 3rem; background: var(--color-superficie); border-top: 4px solid var(--color-acento); {{ $enviada ? '' : 'display: none;' }}">
                <div style="display: inline-flex; align-items: center; justify-content: center; width: 3.5rem; height: 3.5rem; border-radius: var(--radio-full); background: var(--color-correcto-fondo); color: var(--color-correcto-texto); font-size: 1.75rem; font-weight: 700; margin-bottom: 1.25rem;">
                    ✓
                </div>
                <h2 style="font-size: 1.6rem; font-weight: 700; color: var(--color-texto-1); margin-bottom: 0.75rem;">
                    {{ __('portal.suggestions.success_title') }}
                </h2>
                <p style="color: var(--color-texto-2); font-size: 1rem; line-height: 1.6; max-width: 540px; margin: 0 auto 1.75rem auto;">
                    {{ __('portal.suggestions.success_body') }}
                </p>
                <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                    <a href="/sugerencias{{ $langQuery }}" class="btn btn-secundario">
                        {{ __('portal.suggestions.btn_send_another') }}
                    </a>
                    <a href="/{{ $langQuery }}" class="btn btn-primario">
                        {{ __('portal.suggestions.btn_go_home') }}
                    </a>
                </div>
            </div>

            <!-- Formulario de envío -->
            <div id="caja-formulario" class="tarjeta" style="padding: 2rem; background: var(--color-superficie); margin-bottom: 3rem; {{ $enviada ? 'display: none;' : '' }}">
                <!-- Mensaje de error general si ocurre -->
                <div id="alerta-error" style="display: {{ !empty($error) ? 'block' : 'none' }}; background: var(--color-critico-fondo); color: var(--color-critico-texto); border: 1px solid var(--color-borde); border-radius: var(--radio-md); padding: 1rem 1.25rem; margin-bottom: 1.5rem; font-size: 0.95rem;">
                    <span id="texto-error">{{ $error ?? '' }}</span>
                </div>

                <form id="form-sugerencia" action="/sugerencias{{ $langQuery }}" method="POST" novalidate>
                    <!-- 1. Selector de categoría -->
                    <div style="margin-bottom: 1.5rem;">
                        <label for="campo-category" style="display: block; font-weight: 600; font-size: 0.95rem; color: var(--color-texto-1); margin-bottom: 0.5rem;">
                            {{ __('portal.suggestions.field_category') }} <span style="color: var(--color-critico-texto);" aria-hidden="true">*</span>
                        </label>
                        <select id="campo-category" 
                                name="category" 
                                required 
                                style="width: 100%; padding: 0.75rem 1rem; font-size: 0.95rem; border: 1px solid var(--color-borde-control); border-radius: var(--radio-md); background: var(--color-superficie); color: var(--color-texto);">
                            <option value="" disabled {{ empty($old['category']) ? 'selected' : '' }}>{{ __('portal.suggestions.select_category') }}</option>
                            <option value="bot_telegram" {{ ($old['category'] ?? '') === 'bot_telegram' ? 'selected' : '' }}>{{ __('portal.suggestions.categories.bot_telegram') }}</option>
                            <option value="web" {{ ($old['category'] ?? '') === 'web' ? 'selected' : '' }}>{{ __('portal.suggestions.categories.web') }}</option>
                            <option value="meshview" {{ ($old['category'] ?? '') === 'meshview' ? 'selected' : '' }}>{{ __('portal.suggestions.categories.meshview') }}</option>
                            <option value="potatomesh" {{ ($old['category'] ?? '') === 'potatomesh' ? 'selected' : '' }}>{{ __('portal.suggestions.categories.potatomesh') }}</option>
                            <option value="nueva_funcionalidad" {{ ($old['category'] ?? '') === 'nueva_funcionalidad' ? 'selected' : '' }}>{{ __('portal.suggestions.categories.nueva_funcionalidad') }}</option>
                            <option value="otros" {{ ($old['category'] ?? '') === 'otros' ? 'selected' : '' }}>{{ __('portal.suggestions.categories.otros') }}</option>
                        </select>
                    </div>

                    <!-- 2. Campo de texto para la sugerencia -->
                    <div style="margin-bottom: 1.5rem;">
                        <label for="campo-content" style="display: block; font-weight: 600; font-size: 0.95rem; color: var(--color-texto-1); margin-bottom: 0.5rem;">
                            {{ __('portal.suggestions.content_label') }} <span style="color: var(--color-critico-texto);" aria-hidden="true">*</span>
                        </label>
                        <textarea id="campo-content" 
                                  name="content" 
                                  rows="6" 
                                  minlength="10" 
                                  maxlength="3000" 
                                  required 
                                  placeholder="{{ __('portal.suggestions.placeholder_content') }}" 
                                  style="width: 100%; box-sizing: border-box; padding: 0.85rem 1rem; font-size: 0.95rem; border: 1px solid var(--color-borde-control); border-radius: var(--radio-md); background: var(--color-superficie); color: var(--color-texto); font-family: inherit; line-height: 1.5; resize: vertical;">{{ $old['content'] ?? '' }}</textarea>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 0.35rem; font-size: 0.8rem; color: var(--color-texto-3);">
                            <span>{{ __('portal.suggestions.char_hint') }}</span>
                            <span id="contador-caracteres">0 / 3000</span>
                        </div>
                    </div>

                    <!-- 3. Cloudflare Turnstile (si está habilitado con clave pública) -->
                    @if(!empty($siteKey))
                        <div style="margin-top: 1.5rem; margin-bottom: 1.5rem; display: flex; justify-content: flex-start;">
                            <div class="cf-turnstile" 
                                 data-sitekey="{{ $siteKey }}" 
                                 data-theme="auto"
                                 data-callback="alCompletarTurnstile"
                                 data-error-callback="alErrorTurnstile"
                                 data-expired-callback="alExpirarTurnstile"></div>
                        </div>
                    @endif

                    <!-- 4. Botón de acción -->
                    <div style="margin-top: 2rem;">
                        <button type="submit" id="btn-submit" class="btn btn-primario" style="width: 100%; padding: 0.85rem 1.5rem; font-size: 1.05rem;">
                            {{ __('portal.suggestions.btn_submit') }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Explicación pedagógica y garantías de privacidad -->
            <section class="tarjeta" style="padding: 1.75rem 2rem; background: var(--color-superficie-sutil);">
                <h2 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 0.75rem; color: var(--color-texto-1);">
                    {{ __('portal.suggestions.how_it_works') }}
                </h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem; font-size: 0.88rem; line-height: 1.55; color: var(--color-texto-2);">
                    <div>
                        <strong style="color: var(--color-texto-1); display: block; margin-bottom: 0.25rem;">{{ __('portal.suggestions.how_1_title') }}</strong>
                        <p style="margin: 0;">{{ __('portal.suggestions.how_1_desc') }}</p>
                    </div>
                    <div>
                        <strong style="color: var(--color-texto-1); display: block; margin-bottom: 0.25rem;">{{ __('portal.suggestions.how_2_title') }}</strong>
                        <p style="margin: 0;">{{ __('portal.suggestions.how_2_desc') }}</p>
                    </div>
                    <div>
                        <strong style="color: var(--color-texto-1); display: block; margin-bottom: 0.25rem;">{{ __('portal.suggestions.how_3_title') }}</strong>
                        <p style="margin: 0;">{{ __('portal.suggestions.how_3_desc') }}</p>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <!-- Script de soporte para envío interactivo con fetch -->
    <x-slot:scripts>
        <script>
            (function() {
                var textarea = document.getElementById('campo-content');
                var contador = document.getElementById('contador-caracteres');
                var form = document.getElementById('form-sugerencia');
                var btnSubmit = document.getElementById('btn-submit');
                var alertaError = document.getElementById('alerta-error');
                var textoError = document.getElementById('texto-error');
                var cajaExito = document.getElementById('caja-exito');
                var cajaFormulario = document.getElementById('caja-formulario');

                var msgTurnstileFail = @json(__('portal.suggestions.js.err_turnstile_fail'));
                var msgTurnstileExpired = @json(__('portal.suggestions.js.err_turnstile_expired'));
                var msgSelectCategory = @json(__('portal.suggestions.js.err_select_category'));
                var msgContentShort = @json(__('portal.suggestions.js.err_content_short'));
                var msgCompleteSecurity = @json(__('portal.suggestions.js.err_complete_security'));
                var msgSendingBtn = @json(__('portal.suggestions.js.sending_btn'));
                var msgGenericErr = @json(__('portal.suggestions.js.err_generic'));
                var msgNetworkErr = @json(__('portal.suggestions.js.err_network'));

                if (textarea && contador) {
                    var actualizarContador = function() {
                        var len = textarea.value.length;
                        contador.textContent = len + ' / 3000';
                    };
                    textarea.addEventListener('input', actualizarContador);
                    actualizarContador();
                }

                // Callbacks globales de Cloudflare Turnstile
                window.alCompletarTurnstile = function(token) {
                    if (alertaError) {
                        alertaError.style.display = 'none';
                    }
                };

                window.alErrorTurnstile = function() {
                    mostrarError(msgTurnstileFail);
                };

                window.alExpirarTurnstile = function() {
                    mostrarError(msgTurnstileExpired);
                    resetearTurnstile();
                };

                if (form) {
                    form.addEventListener('submit', function(e) {
                        e.preventDefault();

                        var cat = document.getElementById('campo-category').value;
                        var content = textarea ? textarea.value.trim() : '';

                        if (!cat) {
                            mostrarError(msgSelectCategory);
                            return;
                        }

                        if (content.length < 10) {
                            mostrarError(msgContentShort);
                            return;
                        }

                        // Verificar si Turnstile está presente en el DOM y si ya ha sido resuelto
                        var widgetTurnstile = document.querySelector('.cf-turnstile');
                        if (widgetTurnstile) {
                            var inputToken = form.querySelector('[name="cf-turnstile-response"]');
                            if (!inputToken || !inputToken.value) {
                                mostrarError(msgCompleteSecurity);
                                return;
                            }
                        }

                        alertaError.style.display = 'none';
                        btnSubmit.disabled = true;
                        var textoOriginal = btnSubmit.textContent;
                        btnSubmit.textContent = msgSendingBtn;

                        var formData = new FormData(form);

                        fetch('/sugerencias', {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(function(res) {
                            return res.json().then(function(data) {
                                return { status: res.status, ok: res.ok, data: data };
                            });
                        })
                        .then(function(result) {
                            if (result.ok && result.data && result.data.ok) {
                                cajaFormulario.style.display = 'none';
                                cajaExito.style.display = 'block';
                                cajaExito.scrollIntoView({ behavior: 'smooth' });
                            } else {
                                var errorMsg = (result.data && result.data.error) ? result.data.error : msgGenericErr;
                                mostrarError(errorMsg);
                                resetearTurnstile();
                                btnSubmit.disabled = false;
                                btnSubmit.textContent = textoOriginal;
                            }
                        })
                        .catch(function(err) {
                            mostrarError(msgNetworkErr);
                            resetearTurnstile();
                            btnSubmit.disabled = false;
                            btnSubmit.textContent = textoOriginal;
                        });
                    });
                }

                function mostrarError(msg) {
                    if (textoError && alertaError) {
                        textoError.textContent = msg;
                        alertaError.style.display = 'block';
                        alertaError.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    }
                }

                function resetearTurnstile() {
                    if (window.turnstile && typeof window.turnstile.reset === 'function') {
                        try {
                            window.turnstile.reset();
                        } catch (e) {}
                    }
                }
            })();
        </script>
    </x-slot:scripts>
</x-layout>
