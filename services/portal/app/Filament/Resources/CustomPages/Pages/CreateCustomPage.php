<?php

declare(strict_types=1);

namespace App\Filament\Resources\CustomPages\Pages;

use App\Filament\Resources\CustomPages\CustomPageResource;
use Filament\Resources\Pages\CreateRecord;

/**
 * Página de creación de página en Filament.
 */
class CreateCustomPage extends CreateRecord
{
    protected static string $resource = CustomPageResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (auth()->user()?->isEditor()) {
            $data['is_active'] = false;
        }

        return $data;
    }
}
