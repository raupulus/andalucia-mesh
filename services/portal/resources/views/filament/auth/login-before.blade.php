<div style="margin-bottom: 1.25rem;">
    <!-- Selector de idioma en login -->
    <div style="display: flex; justify-content: flex-end; align-items: center; gap: 0.5rem; margin-bottom: 0.75rem;">
        <span class="text-xs text-gray-500 dark:text-gray-400">{{ __('admin.language') }}:</span>
        <div class="flex items-center gap-1.5">
            <a href="{{ request()->fullUrlWithQuery(['lang' => 'es']) }}" class="flex items-center gap-1 px-2 py-1 rounded text-xs {{ app()->getLocale() === 'es' ? 'bg-[#E9FCEF] text-[#15612F] font-bold dark:bg-[#1C3A28] dark:text-[#9CF1BA]' : 'text-gray-600 dark:text-gray-300' }}" title="Español (Andalucía)">
                <x-icono-bandera idioma="es" :tamano="14" />
                <span>ES</span>
            </a>
            <a href="{{ request()->fullUrlWithQuery(['lang' => 'en']) }}" class="flex items-center gap-1 px-2 py-1 rounded text-xs {{ app()->getLocale() === 'en' ? 'bg-[#E9FCEF] text-[#15612F] font-bold dark:bg-[#1C3A28] dark:text-[#9CF1BA]' : 'text-gray-600 dark:text-gray-300' }}" title="English">
                <x-icono-bandera idioma="en" :tamano="14" />
                <span>EN</span>
            </a>
            <a href="{{ request()->fullUrlWithQuery(['lang' => 'pt']) }}" class="flex items-center gap-1 px-2 py-1 rounded text-xs {{ app()->getLocale() === 'pt' ? 'bg-[#E9FCEF] text-[#15612F] font-bold dark:bg-[#1C3A28] dark:text-[#9CF1BA]' : 'text-gray-600 dark:text-gray-300' }}" title="Português">
                <x-icono-bandera idioma="pt" :tamano="14" />
                <span>PT</span>
            </a>
        </div>
    </div>

    <!-- Franja tricolor de Andalucía -->
    <div style="display: flex; height: 4px; width: 100%; border-radius: 9999px; overflow: hidden; margin-bottom: 1rem;" aria-hidden="true">
        <span style="flex: 1; background-color: #007A33;"></span>
        <span style="flex: 1; background-color: #FFFFFF; border: 0.5px solid rgba(0,0,0,0.08);"></span>
        <span style="flex: 1; background-color: #007A33;"></span>
    </div>

    <div class="p-3 rounded-lg border text-xs sm:text-sm flex items-start gap-2 bg-[#E9FCEF] text-[#15612F] border-[#15612F]/20 dark:bg-[#1C3A28] dark:text-[#9CF1BA] dark:border-[#9CF1BA]/30">
        <span class="font-bold shrink-0" aria-hidden="true">🔒</span>
        <span>{{ __('admin.login_notice') }}</span>
    </div>
</div>
