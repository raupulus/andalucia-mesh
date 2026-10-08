<div
    class="fi-language-switch-wrapper"
    x-data="{ open: false }"
    @keydown.escape.window="open = false"
>
    <!-- Botón activador: solo icono redondo con la bandera actual -->
    <button
        type="button"
        @click="open = !open"
        @click.outside="open = false"
        :aria-expanded="open"
        aria-haspopup="true"
        aria-label="{{ __('admin.language_selector') }}"
        title="{{ __('admin.language_selector') }}"
        class="fi-btn-idioma-trigger"
    >
        <x-icono-bandera :idioma="app()->getLocale()" :tamano="22" />
    </button>

    <!-- Menú desplegable flotante idéntico al frontend -->
    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 transform scale-95"
        x-transition:enter-end="opacity-100 transform scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 transform scale-100"
        x-transition:leave-end="opacity-0 transform scale-95"
        class="fi-language-dropdown-menu"
        style="display: none; position: absolute; right: 0; top: calc(100% + 8px);"
    >
        <a
            href="{{ request()->fullUrlWithQuery(['lang' => 'es']) }}"
            class="fi-lang-item {{ app()->getLocale() === 'es' ? 'activo' : '' }}"
            data-lang="es"
        >
            <x-icono-bandera idioma="es" :tamano="20" />
            <span>Español</span>
        </a>
        <a
            href="{{ request()->fullUrlWithQuery(['lang' => 'pt']) }}"
            class="fi-lang-item {{ app()->getLocale() === 'pt' ? 'activo' : '' }}"
            data-lang="pt"
        >
            <x-icono-bandera idioma="pt" :tamano="20" />
            <span>Português</span>
        </a>
        <a
            href="{{ request()->fullUrlWithQuery(['lang' => 'en']) }}"
            class="fi-lang-item {{ app()->getLocale() === 'en' ? 'activo' : '' }}"
            data-lang="en"
        >
            <x-icono-bandera idioma="en" :tamano="20" />
            <span>English</span>
        </a>
    </div>
</div>
