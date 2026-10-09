<?php

declare(strict_types=1);

namespace App\Filament\Resources\HardwareCategories\Pages;

use App\Filament\Resources\HardwareCategories\HardwareCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

/**
 * Página de listado de categorías de hardware en Filament.
 */
class ListHardwareCategories extends ListRecords
{
    protected static string $resource = HardwareCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label(__('admin.hardware.btn_create_category'))
                ->visible(fn (): bool => ! (auth()->user()?->isEditor() ?? false)),
        ];
    }
}
