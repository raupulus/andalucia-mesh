<?php

declare(strict_types=1);

namespace App\Filament\Resources\HardwareItems\Pages;

use App\Filament\Resources\HardwareItems\HardwareItemResource;
use Filament\Resources\Pages\CreateRecord;

/**
 * Página de creación de artículos de hardware en Filament.
 */
class CreateHardwareItem extends CreateRecord
{
    protected static string $resource = HardwareItemResource::class;
}
