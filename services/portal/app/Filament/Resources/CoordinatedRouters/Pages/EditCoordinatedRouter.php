<?php

declare(strict_types=1);

namespace App\Filament\Resources\CoordinatedRouters\Pages;

use App\Filament\Resources\CoordinatedRouters\CoordinatedRouterResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

/**
 * Página de edición de router coordinado en Filament.
 */
class EditCoordinatedRouter extends EditRecord
{
    protected static string $resource = CoordinatedRouterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
