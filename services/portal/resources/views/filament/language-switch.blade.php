<div class="flex items-center gap-2 px-2 py-1" x-data="{ open: false }">
    <div class="relative">
        <button
            type="button"
            @click="open = !open"
            @click.outside="open = false"
            class="flex items-center justify-center w-8 h-8 rounded-full border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
            aria-label="{{ __('admin.language_selector') }}"
            title="{{ __('admin.language_selector') }}"
        >
            <x-icono-bandera :idioma="app()->getLocale()" :tamano="20" />
        </button>

        <div
            x-show="open"
            x-transition
            class="absolute right-0 mt-2 w-36 rounded-lg bg-white dark:bg-gray-800 shadow-lg border border-gray-200 dark:border-gray-700 py-1 z-50 text-xs"
            style="display: none;"
        >
            <a href="{{ request()->fullUrlWithQuery(['lang' => 'es']) }}" class="flex items-center gap-2.5 px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 {{ app()->getLocale() === 'es' ? 'font-bold text-[#15612F] dark:text-[#9CF1BA]' : 'text-gray-700 dark:text-gray-200' }}">
                <x-icono-bandera idioma="es" :tamano="18" />
                <span>Español</span>
            </a>
            <a href="{{ request()->fullUrlWithQuery(['lang' => 'pt']) }}" class="flex items-center gap-2.5 px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 {{ app()->getLocale() === 'pt' ? 'font-bold text-[#15612F] dark:text-[#9CF1BA]' : 'text-gray-700 dark:text-gray-200' }}">
                <x-icono-bandera idioma="pt" :tamano="18" />
                <span>Português</span>
            </a>
            <a href="{{ request()->fullUrlWithQuery(['lang' => 'en']) }}" class="flex items-center gap-2.5 px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 {{ app()->getLocale() === 'en' ? 'font-bold text-[#15612F] dark:text-[#9CF1BA]' : 'text-gray-700 dark:text-gray-200' }}">
                <x-icono-bandera idioma="en" :tamano="18" />
                <span>English</span>
            </a>
        </div>
    </div>
</div>
