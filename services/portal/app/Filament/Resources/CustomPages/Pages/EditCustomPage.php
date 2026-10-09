<?php

declare(strict_types=1);

namespace App\Filament\Resources\CustomPages\Pages;

use App\Filament\Resources\CustomPages\CustomPageResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

/**
 * Página de edición de página en Filament.
 */
class EditCustomPage extends EditRecord
{
    protected static string $resource = CustomPageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->visible(fn (): bool => ! (auth()->user()?->isEditor() ?? false)),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (auth()->user()?->isEditor()) {
            $data['is_active'] = $this->record->is_active;
        }

        return $data;
    }
}
