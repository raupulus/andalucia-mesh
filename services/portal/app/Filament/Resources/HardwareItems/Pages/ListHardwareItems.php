<?php

declare(strict_types=1);

namespace App\Filament\Resources\HardwareItems\Pages;

use App\Filament\Resources\HardwareItems\HardwareItemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

/**
 * Página de listado de artículos de hardware en Filament.
 */
class ListHardwareItems extends ListRecords
{
    protected static string $resource = HardwareItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label(__('admin.hardware.btn_create_item')),
        ];
    }
}
