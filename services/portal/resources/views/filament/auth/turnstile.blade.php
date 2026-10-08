@php
    $turnstile = app(\App\Servicios\TurnstileService::class);
    $siteKey = $turnstile->getSiteKey();
@endphp

@if($siteKey)
<div wire:ignore style="margin-top: 1rem; margin-bottom: 0.5rem; display: flex; flex-direction: column; align-items: center; width: 100%;">
    <div class="cf-turnstile"
         data-sitekey="{{ $siteKey }}"
         data-theme="auto"
         data-callback="alCompletarTurnstileLogin"
         data-expired-callback="alExpirarTurnstileLogin"></div>
    <input type="hidden" id="login-turnstile-token" wire:model="data.turnstile_token">
</div>

<script>
    window.alCompletarTurnstileLogin = function(token) {
        var el = document.getElementById('login-turnstile-token');
        if (el) {
            el.value = token;
            el.dispatchEvent(new Event('input', { bubbles: true }));
        }
    };
    window.alExpirarTurnstileLogin = function() {
        var el = document.getElementById('login-turnstile-token');
        if (el) {
            el.value = '';
            el.dispatchEvent(new Event('input', { bubbles: true }));
        }
        if (window.turnstile && typeof window.turnstile.reset === 'function') {
            try { window.turnstile.reset(); } catch(e) {}
        }
    };
</script>
@endif
