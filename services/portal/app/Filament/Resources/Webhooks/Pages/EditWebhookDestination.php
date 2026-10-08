<?php

declare(strict_types=1);

namespace App\Filament\Resources\Webhooks\Pages;

use App\Filament\Resources\Webhooks\WebhookDestinationResource;
use App\Models\WebhookDestination;
use App\Servicios\WebhooksClient;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

/**
 * Página de edición de destino de webhook en Filament.
 *
 * Propaga los cambios al microservicio de webhooks vía API HTTP.
 */
class EditWebhookDestination extends EditRecord
{
    protected static string $resource = WebhookDestinationResource::class;

    public function getSubheading(): ?string
    {
        return __('admin.webhooks.edit_subheading');
    }

    /**
     * Renderiza el formulario de edición con sus botones de acción y, debajo, la guía visual explicativa.
     */
    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getFormContentComponent(),
                $this->getRelationManagersContentComponent(),
                View::make('filament.webhooks.guide-alertas'),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label(__('admin.webhooks.action_delete'))
                ->before(function (WebhookDestination $record, WebhooksClient $client): void {
                    $client->deleteDestination($record->nombre);
                }),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var WebhookDestination $record */
        $client = app(WebhooksClient::class);

        $payload = [
            'url' => $data['url'],
            'riesgos' => ! empty($data['riesgos']) ? array_values((array) $data['riesgos']) : null,
            'tipos' => ! empty($data['tipos']) ? array_values((array) $data['tipos']) : null,
            'provincias' => ! empty($data['provincias']) ? array_values((array) $data['provincias']) : null,
            'nodos' => ! empty($data['nodos']) ? array_values((array) $data['nodos']) : null,
        ];

        if (! empty($data['secreto'])) {
            $payload['secreto'] = $data['secreto'];
        }

        try {
            $client->updateDestination($record->nombre, $payload);
        } catch (\Throwable $e) {
            throw ValidationException::withMessages([
                'url' => 'Error al actualizar en microservicio: '.$e->getMessage(),
            ]);
        }

        $host = (string) parse_url((string) $data['url'], PHP_URL_HOST);
        $data['host'] = $host;

        if (empty($data['secreto'])) {
            unset($data['secreto']);
        }

        $record->update($data);

        return $record;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
