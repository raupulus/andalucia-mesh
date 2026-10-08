<?php

declare(strict_types=1);

namespace App\Filament\Resources\Suggestions\Pages;

use App\Filament\Resources\Suggestions\SuggestionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

/**
 * Página de listado de sugerencias en el panel Filament.
 */
class ListSuggestions extends ListRecords
{
    protected static string $resource = SuggestionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Nueva sugerencia'),
        ];
    }
}
