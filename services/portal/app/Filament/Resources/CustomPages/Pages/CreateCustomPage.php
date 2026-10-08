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
}
