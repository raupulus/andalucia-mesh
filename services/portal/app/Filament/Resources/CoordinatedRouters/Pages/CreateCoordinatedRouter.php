<?php

declare(strict_types=1);

namespace App\Filament\Resources\CoordinatedRouters\Pages;

use App\Filament\Resources\CoordinatedRouters\CoordinatedRouterResource;
use Filament\Resources\Pages\CreateRecord;

/**
 * Página de alta de nuevo router coordinado en Filament.
 */
class CreateCoordinatedRouter extends CreateRecord
{
    protected static string $resource = CoordinatedRouterResource::class;
}
