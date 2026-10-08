<?php

declare(strict_types=1);

namespace App\Filament\Resources\Webhooks\Pages;

use App\Filament\Resources\Webhooks\WebhookDestinationResource;
use App\Servicios\WebhooksClient;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

/**
 * Página de creación de nuevo destino de webhook en Filament.
 *
 * Registra el destino en el microservicio webhooks vía API HTTP antes de persistir la réplica local.
 */
class CreateWebhookDestination extends CreateRecord
{
    protected static string $resource = WebhookDestinationResource::class;

    public function getSubheading(): ?string
    {
        return __('admin.webhooks.create_subheading');
    }

    protected function handleRecordCreation(array $data): Model
    {
        $client = app(WebhooksClient::class);

        try {
            $client->createDestination([
                'nombre' => $data['nombre'],
                'url' => $data['url'],
                'secreto' => $data['secreto'],
                'riesgos' => ! empty($data['riesgos']) ? array_values((array) $data['riesgos']) : null,
                'tipos' => ! empty($data['tipos']) ? array_values((array) $data['tipos']) : null,
                'provincias' => ! empty($data['provincias']) ? array_values((array) $data['provincias']) : null,
                'nodos' => ! empty($data['nodos']) ? array_values((array) $data['nodos']) : null,
            ]);
        } catch (\Throwable $e) {
            throw ValidationException::withMessages([
                'url' => 'Error al registrar en microservicio: '.$e->getMessage(),
            ]);
        }

        $host = (string) parse_url((string) $data['url'], PHP_URL_HOST);
        $data['host'] = $host;
        $data['activo'] = true;

        return static::getModel()::create($data);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
