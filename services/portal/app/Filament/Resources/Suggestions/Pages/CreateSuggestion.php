<?php

declare(strict_types=1);

namespace App\Filament\Resources\Suggestions\Pages;

use App\Filament\Resources\Suggestions\SuggestionResource;
use Filament\Resources\Pages\CreateRecord;

/**
 * Página de creación manual de sugerencias en el panel Filament.
 */
class CreateSuggestion extends CreateRecord
{
    protected static string $resource = SuggestionResource::class;
}
