<?php

declare(strict_types=1);

namespace App\Filament\Resources\Suggestions;

use App\Filament\Resources\Suggestions\Pages\CreateSuggestion;
use App\Filament\Resources\Suggestions\Pages\EditSuggestion;
use App\Filament\Resources\Suggestions\Pages\ListSuggestions;
use App\Filament\Resources\Suggestions\Schemas\SuggestionForm;
use App\Filament\Resources\Suggestions\Tables\SuggestionsTable;
use App\Models\Suggestion;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

/**
 * Recurso Filament para gestionar las sugerencias y propuestas recibidas de la comunidad.
 */
class SuggestionResource extends Resource
{
    protected static ?string $model = Suggestion::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLightBulb;

    public static function getNavigationLabel(): string
    {
        return __('admin.suggestions.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('admin.suggestions.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.suggestions.plural_model_label');
    }

    /**
     * Muestra una insignia con el total de sugerencias pendientes de revisión en el menú de navegación.
     */
    public static function getNavigationBadge(): ?string
    {
        $count = Suggestion::where('status', Suggestion::STATUS_PENDING)->count();

        return $count > 0 ? (string) $count : null;
    }

    /**
     * Color de la insignia de navegación (ámbar/aviso para sugerencias pendientes).
     */
    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    /**
     * Configuración del formulario de creación y edición.
     */
    public static function form(Schema $schema): Schema
    {
        return SuggestionForm::configure($schema);
    }

    /**
     * Configuración de la tabla de listado.
     */
    public static function table(Table $table): Table
    {
        return SuggestionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSuggestions::route('/'),
            'create' => CreateSuggestion::route('/create'),
            'edit' => EditSuggestion::route('/{record}/edit'),
        ];
    }
}
