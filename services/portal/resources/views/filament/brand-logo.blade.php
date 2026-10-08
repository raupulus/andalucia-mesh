<div style="display: inline-flex; align-items: center; gap: 0.75rem; text-decoration: none;">
    <img src="{{ asset('img/logo.png') }}" alt="{{ config('proyecto.nombre') }}" style="height: 2.25rem; width: 2.25rem; object-fit: contain; flex-shrink: 0;" width="36" height="36">
    <div style="display: flex; flex-direction: column; text-align: left; line-height: 1.2;">
        <span style="font-weight: 700; font-size: 1.1rem; color: inherit; letter-spacing: -0.01em;">{{ config('proyecto.nombre') }}</span>
        <span class="text-xs font-semibold text-[#007A33] dark:text-[#67EA94]" style="font-size: 0.75rem; font-weight: 600;">{{ __('admin.brand_panel_operator') }}</span>
    </div>
</div>
