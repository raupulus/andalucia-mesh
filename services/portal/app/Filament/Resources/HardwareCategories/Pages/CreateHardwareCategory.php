<?php

declare(strict_types=1);

namespace App\Filament\Resources\HardwareCategories\Pages;

use App\Filament\Resources\HardwareCategories\HardwareCategoryResource;
use Filament\Resources\Pages\CreateRecord;

/**
 * Página de creación de categorías de hardware en Filament.
 */
class CreateHardwareCategory extends CreateRecord
{
    protected static string $resource = HardwareCategoryResource::class;
}
