<?php

declare(strict_types=1);

namespace App\Filament\Resources\Webhooks\Pages;

use App\Filament\Resources\Webhooks\WebhookDestinationResource;
use App\Servicios\WebhooksClient;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Log;

/**
 * Página de listado de destinos de webhooks en Filament.
 *
 * Sincroniza el estado en tiempo real desde el microservicio webhooks al cargar.
 */
class ListWebhookDestinations extends ListRecords
{
    protected static string $resource = WebhookDestinationResource::class;

    public function mount(): void
    {
        parent::mount();

        try {
            app(WebhooksClient::class)->syncLocalDestinations();
        } catch (\Throwable $e) {
            Log::warning('No se pudo sincronizar destinos desde microservicio webhooks', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Nuevo destino'),
        ];
    }
}
