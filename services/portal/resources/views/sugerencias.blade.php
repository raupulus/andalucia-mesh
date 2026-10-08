<x-layout title="Buzón de sugerencias" description="Envía tus propuestas, mejoras o ideas para los servicios e infraestructura de la red Andalucía Mesh.">
    @if(!empty($siteKey))
        <x-slot:styles>
            <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
        </x-slot:styles>
    @endif

    <div class="contenedor seccion">
        <div style="max-width: 720px; margin: 0 auto;">
            <!-- Enlace superior de retorno -->
            <div style="margin-bottom: 1.5rem;">
                <a href="/" style="font-size: 0.9rem; text-decoration: none; color: var(--color-texto-2); display: inline-flex; align-items: center; gap: 0.35rem;">
                    ← Volver al inicio
                </a>
            </div>

            <header style="margin-bottom: 2rem;">
                <h1 style="font-size: 2.25rem; font-weight: 800; color: var(--color-texto-1); margin-bottom: 0.5rem; letter-spacing: -0.02em;">
                    Buzón de sugerencias
                </h1>
                <p class="lead" style="margin-bottom: 0; font-size: 1.1rem; line-height: 1.55;">
                    ¿Tienes una idea para la red, una función para el bot o una mejora para la web? Tu opinión nos ayuda a hacer crecer la malla comunitaria.
                </p>
            </header>

            <!-- Estado de confirmación de envío exitoso -->
            <div id="caja-exito" class="tarjeta" style="padding: 2.5rem; text-align: center; margin-bottom: 3rem; background: var(--color-superficie); border-top: 4px solid var(--color-acento); {{ $enviada ? '' : 'display: none;' }}">
                <div style="display: inline-flex; align-items: center; justify-content: center; width: 3.5rem; height: 3.5rem; border-radius: var(--radio-full); background: var(--color-correcto-fondo); color: var(--color-correcto-texto); font-size: 1.75rem; font-weight: 700; margin-bottom: 1.25rem;">
                    ✓
                </div>
                <h2 style="font-size: 1.6rem; font-weight: 700; color: var(--color-texto-1); margin-bottom: 0.75rem;">
                    ¡Muchas gracias por tu aportación!
                </h2>
                <p style="color: var(--color-texto-2); font-size: 1rem; line-height: 1.6; max-width: 540px; margin: 0 auto 1.75rem auto;">
                    Tu sugerencia ha sido registrada en el sistema. Los operadores de la red la revisarán internamente para valorar su viabilidad e incorporarla en futuras actualizaciones.
                </p>
                <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                    <a href="/sugerencias" class="btn btn-secundario">
                        Enviar otra sugerencia
                    </a>
                    <a href="/" class="btn btn-primario">
                        Ir a la portada
                    </a>
                </div>
            </div>

            <!-- Formulario de envío -->
            <div id="caja-formulario" class="tarjeta" style="padding: 2rem; background: var(--color-superficie); margin-bottom: 3rem; {{ $enviada ? 'display: none;' : '' }}">
                <!-- Mensaje de error general si ocurre -->
                <div id="alerta-error" style="display: {{ !empty($error) ? 'block' : 'none' }}; background: var(--color-critico-fondo); color: var(--color-critico-texto); border: 1px solid var(--color-borde); border-radius: var(--radio-md); padding: 1rem 1.25rem; margin-bottom: 1.5rem; font-size: 0.95rem;">
                    <span id="texto-error">{{ $error ?? '' }}</span>
                </div>

                <form id="form-sugerencia" action="/sugerencias" method="POST" novalidate>
                    <!-- 1. Selector de categoría -->
                    <div style="margin-bottom: 1.5rem;">
                        <label for="campo-category" style="display: block; font-weight: 600; font-size: 0.95rem; color: var(--color-texto-1); margin-bottom: 0.5rem;">
                            ¿A qué área o servicio corresponde tu sugerencia? <span style="color: var(--color-critico-texto);" aria-hidden="true">*</span>
                        </label>
                        <select id="campo-category" 
                                name="category" 
                                required 
                                style="width: 100%; padding: 0.75rem 1rem; font-size: 0.95rem; border: 1px solid var(--color-borde-control); border-radius: var(--radio-md); background: var(--color-superficie); color: var(--color-texto);">
                            <option value="" disabled {{ empty($old['category']) ? 'selected' : '' }}>Selecciona una categoría...</option>
                            <option value="bot_telegram" {{ ($old['category'] ?? '') === 'bot_telegram' ? 'selected' : '' }}>Bot Telegram</option>
                            <option value="web" {{ ($old['category'] ?? '') === 'web' ? 'selected' : '' }}>Web</option>
                            <option value="meshview" {{ ($old['category'] ?? '') === 'meshview' ? 'selected' : '' }}>Meshview</option>
                            <option value="potatomesh" {{ ($old['category'] ?? '') === 'potatomesh' ? 'selected' : '' }}>Potato Mesh</option>
                            <option value="nueva_funcionalidad" {{ ($old['category'] ?? '') === 'nueva_funcionalidad' ? 'selected' : '' }}>Nueva Funcionalidad</option>
                            <option value="otros" {{ ($old['category'] ?? '') === 'otros' ? 'selected' : '' }}>Otros</option>
                        </select>
                    </div>

                    <!-- 2. Campo de texto para la sugerencia -->
                    <div style="margin-bottom: 1.5rem;">
                        <label for="campo-content" style="display: block; font-weight: 600; font-size: 0.95rem; color: var(--color-texto-1); margin-bottom: 0.5rem;">
                            Tu sugerencia o propuesta <span style="color: var(--color-critico-texto);" aria-hidden="true">*</span>
                        </label>
                        <textarea id="campo-content" 
                                  name="content" 
                                  rows="6" 
                                  minlength="10" 
                                  maxlength="3000" 
                                  required 
                                  placeholder="Describe con detalle tu propuesta, mejora o la funcionalidad que te gustaría tener..." 
                                  style="width: 100%; box-sizing: border-box; padding: 0.85rem 1rem; font-size: 0.95rem; border: 1px solid var(--color-borde-control); border-radius: var(--radio-md); background: var(--color-superficie); color: var(--color-texto); font-family: inherit; line-height: 1.5; resize: vertical;">{{ $old['content'] ?? '' }}</textarea>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 0.35rem; font-size: 0.8rem; color: var(--color-texto-3);">
                            <span>Mínimo 10 caracteres. Por favor, sé claro y conciso.</span>
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
                            Enviar sugerencia
                        </button>
                    </div>
                </form>
            </div>

            <!-- Explicación pedagógica y garantías de privacidad -->
            <section class="tarjeta" style="padding: 1.75rem 2rem; background: var(--color-superficie-sutil);">
                <h2 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 0.75rem; color: var(--color-texto-1);">
                    ¿Cómo funciona este buzón?
                </h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem; font-size: 0.88rem; line-height: 1.55; color: var(--color-texto-2);">
                    <div>
                        <strong style="color: var(--color-texto-1); display: block; margin-bottom: 0.25rem;">1. Cero datos personales</strong>
                        <p style="margin: 0;">No solicitamos nombre, teléfono ni correo electrónico. El envío es totalmente anónimo y confidencial.</p>
                    </div>
                    <div>
                        <strong style="color: var(--color-texto-1); display: block; margin-bottom: 0.25rem;">2. Gestión interna</strong>
                        <p style="margin: 0;">Las sugerencias pasan directamente a una intranet de los operadores para priorizar mejoras de la red.</p>
                    </div>
                    <div>
                        <strong style="color: var(--color-texto-1); display: block; margin-bottom: 0.25rem;">3. Desarrollo abierto</strong>
                        <p style="margin: 0;">Las propuestas viables se programan en el repositorio libre y abierto del proyecto Andalucía Mesh.</p>
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
                    mostrarError('No se pudo verificar la comprobación de seguridad de Cloudflare. Si utilizas extensiones de privacidad o bloqueador de anuncios, permítele cargar para completar el envío.');
                };

                window.alExpirarTurnstile = function() {
                    mostrarError('La comprobación de seguridad ha caducado por inactividad. Por favor, márcala de nuevo antes de enviar.');
                    resetearTurnstile();
                };

                if (form) {
                    form.addEventListener('submit', function(e) {
                        e.preventDefault();

                        var cat = document.getElementById('campo-category').value;
                        var content = textarea ? textarea.value.trim() : '';

                        if (!cat) {
                            mostrarError('Por favor, selecciona una categoría para tu sugerencia.');
                            return;
                        }

                        if (content.length < 10) {
                            mostrarError('La sugerencia debe tener al menos 10 caracteres.');
                            return;
                        }

                        // Verificar si Turnstile está presente en el DOM y si ya ha sido resuelto
                        var widgetTurnstile = document.querySelector('.cf-turnstile');
                        if (widgetTurnstile) {
                            var inputToken = form.querySelector('[name="cf-turnstile-response"]');
                            if (!inputToken || !inputToken.value) {
                                mostrarError('Por favor, completa la verificación de seguridad antes de enviar tu propuesta.');
                                return;
                            }
                        }

                        alertaError.style.display = 'none';
                        btnSubmit.disabled = true;
                        var textoOriginal = btnSubmit.textContent;
                        btnSubmit.textContent = 'Enviando propuesta...';

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
                                var errorMsg = (result.data && result.data.error) ? result.data.error : 'No se pudo registrar la sugerencia. Por favor, inténtalo de nuevo.';
                                mostrarError(errorMsg);
                                resetearTurnstile();
                                btnSubmit.disabled = false;
                                btnSubmit.textContent = textoOriginal;
                            }
                        })
                        .catch(function(err) {
                            mostrarError('Error de conexión al enviar la sugerencia. Por favor, revisa tu red.');
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
