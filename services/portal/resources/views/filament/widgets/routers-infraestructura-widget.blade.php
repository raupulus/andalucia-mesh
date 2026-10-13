<x-filament-widgets::widget>
    <div wire:poll.30s>
        <x-tabla-routers
            :routers-por-provincia="$routersPorProvincia ?? []"
            :routers="$routers ?? []"
            :total="$total ?? 0"
            :en-alerta="$en_alerta ?? $enAlerta ?? 0"
            :error-fuente="$error_fuente ?? $errorFuente ?? null"
            :ambito="$ambito ?? 'andalucia'"
        />
    </div>
</x-filament-widgets::widget>
