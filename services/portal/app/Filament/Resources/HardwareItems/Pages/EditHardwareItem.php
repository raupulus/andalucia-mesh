<?php

declare(strict_types=1);

namespace App\Filament\Resources\HardwareItems\Pages;

use App\Filament\Resources\HardwareItems\HardwareItemResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

/**
 * Página de edición de artículos de hardware en Filament.
 */
class EditHardwareItem extends EditRecord
{
    protected static string $resource = HardwareItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
