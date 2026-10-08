<div class="flex items-center gap-2 px-2 py-1" x-data="{ open: false }">
    <div class="relative">
        <button
            type="button"
            @click="open = !open"
            @click.outside="open = false"
            class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 text-xs font-medium hover:bg-gray-50 dark:hover:bg-gray-700 transition"
            aria-label="{{ __('admin.language_selector') }}"
        >
            <x-icono-bandera :idioma="app()->getLocale()" :tamano="18" />
            <span class="uppercase font-semibold">{{ app()->getLocale() }}</span>
            <svg class="w-3.5 h-3.5 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
            </svg>
        </button>

        <div
            x-show="open"
            x-transition
            class="absolute right-0 mt-1 w-36 rounded-lg bg-white dark:bg-gray-800 shadow-lg border border-gray-200 dark:border-gray-700 py-1 z-50 text-xs"
            style="display: none;"
        >
            <a href="{{ request()->fullUrlWithQuery(['lang' => 'es']) }}" class="flex items-center gap-2 px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 {{ app()->getLocale() === 'es' ? 'font-bold text-[#15612F] dark:text-[#9CF1BA]' : 'text-gray-700 dark:text-gray-200' }}">
                <x-icono-bandera idioma="es" :tamano="16" />
                <span>Español</span>
            </a>
            <a href="{{ request()->fullUrlWithQuery(['lang' => 'en']) }}" class="flex items-center gap-2 px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 {{ app()->getLocale() === 'en' ? 'font-bold text-[#15612F] dark:text-[#9CF1BA]' : 'text-gray-700 dark:text-gray-200' }}">
                <x-icono-bandera idioma="en" :tamano="16" />
                <span>English</span>
            </a>
            <a href="{{ request()->fullUrlWithQuery(['lang' => 'pt']) }}" class="flex items-center gap-2 px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 {{ app()->getLocale() === 'pt' ? 'font-bold text-[#15612F] dark:text-[#9CF1BA]' : 'text-gray-700 dark:text-gray-200' }}">
                <x-icono-bandera idioma="pt" :tamano="16" />
                <span>Português</span>
            </a>
        </div>
    </div>
</div>
