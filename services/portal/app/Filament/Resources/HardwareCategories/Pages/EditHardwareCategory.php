<?php

declare(strict_types=1);

namespace App\Filament\Resources\HardwareCategories\Pages;

use App\Filament\Resources\HardwareCategories\HardwareCategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

/**
 * Página de edición de categorías de hardware en Filament.
 */
class EditHardwareCategory extends EditRecord
{
    protected static string $resource = HardwareCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
