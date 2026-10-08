<?php

declare(strict_types=1);

namespace App\Filament\Resources\Faqs;

use App\Filament\Resources\Faqs\Pages\CreateFaq;
use App\Filament\Resources\Faqs\Pages\EditFaq;
use App\Filament\Resources\Faqs\Pages\ListFaqs;
use App\Filament\Resources\Faqs\Schemas\FaqForm;
use App\Filament\Resources\Faqs\Tables\FaqsTable;
use App\Models\Faq;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

/**
 * Recurso Filament para gestionar las preguntas frecuentes (FAQ) del portal.
 */
class FaqResource extends Resource
{
    protected static ?string $model = Faq::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQuestionMarkCircle;

    public static function getNavigationLabel(): string
    {
        return __('admin.faqs.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('admin.faqs.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.faqs.plural_model_label');
    }

    /**
     * Configuración del formulario de creación y edición.
     */
    public static function form(Schema $schema): Schema
    {
        return FaqForm::configure($schema);
    }

    /**
     * Configuración de la tabla de listado.
     */
    public static function table(Table $table): Table
    {
        return FaqsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFaqs::route('/'),
            'create' => CreateFaq::route('/create'),
            'edit' => EditFaq::route('/{record}/edit'),
        ];
    }
}
