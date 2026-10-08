<div style="margin-bottom: 1.25rem;">
    <!-- Selector de idioma en login: solo iconos redondeados, más grandes, en línea y centrados (es, pt, en) -->
    <div style="display: flex; justify-content: center; align-items: center; gap: 0.75rem; margin-bottom: 1.25rem;">
        <a
            href="{{ request()->fullUrlWithQuery(['lang' => 'es']) }}"
            class="flex items-center justify-center w-10 h-10 rounded-full border transition {{ app()->getLocale() === 'es' ? 'border-[#15612F] bg-[#E9FCEF] dark:border-[#9CF1BA] dark:bg-[#1C3A28] ring-2 ring-[#15612F]/40 dark:ring-[#9CF1BA]/40 shadow-sm' : 'border-gray-300 dark:border-gray-700 bg-white/50 dark:bg-gray-800/50 hover:bg-gray-100 dark:hover:bg-gray-800' }}"
            title="Español (Andalucía)"
            aria-label="Español (Andalucía)"
        >
            <x-icono-bandera idioma="es" :tamano="26" />
        </a>
        <a
            href="{{ request()->fullUrlWithQuery(['lang' => 'pt']) }}"
            class="flex items-center justify-center w-10 h-10 rounded-full border transition {{ app()->getLocale() === 'pt' ? 'border-[#15612F] bg-[#E9FCEF] dark:border-[#9CF1BA] dark:bg-[#1C3A28] ring-2 ring-[#15612F]/40 dark:ring-[#9CF1BA]/40 shadow-sm' : 'border-gray-300 dark:border-gray-700 bg-white/50 dark:bg-gray-800/50 hover:bg-gray-100 dark:hover:bg-gray-800' }}"
            title="Português"
            aria-label="Português"
        >
            <x-icono-bandera idioma="pt" :tamano="26" />
        </a>
        <a
            href="{{ request()->fullUrlWithQuery(['lang' => 'en']) }}"
            class="flex items-center justify-center w-10 h-10 rounded-full border transition {{ app()->getLocale() === 'en' ? 'border-[#15612F] bg-[#E9FCEF] dark:border-[#9CF1BA] dark:bg-[#1C3A28] ring-2 ring-[#15612F]/40 dark:ring-[#9CF1BA]/40 shadow-sm' : 'border-gray-300 dark:border-gray-700 bg-white/50 dark:bg-gray-800/50 hover:bg-gray-100 dark:hover:bg-gray-800' }}"
            title="English"
            aria-label="English"
        >
            <x-icono-bandera idioma="en" :tamano="26" />
        </a>
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
