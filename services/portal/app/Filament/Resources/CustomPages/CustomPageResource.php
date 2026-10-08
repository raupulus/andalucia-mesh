<?php

declare(strict_types=1);

namespace App\Filament\Resources\CustomPages;

use App\Filament\Resources\CustomPages\Pages\CreateCustomPage;
use App\Filament\Resources\CustomPages\Pages\EditCustomPage;
use App\Filament\Resources\CustomPages\Pages\ListCustomPages;
use App\Filament\Resources\CustomPages\Schemas\CustomPageForm;
use App\Filament\Resources\CustomPages\Tables\CustomPagesTable;
use App\Models\CustomPage;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

/**
 * Recurso Filament para la gestión editorial de páginas y artículos del portal.
 */
class CustomPageResource extends Resource
{
    protected static ?string $model = CustomPage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.custom_pages.nav_group');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.custom_pages.nav_label');
    }

    public static function getModelLabel(): string
    {
        return __('admin.custom_pages.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.custom_pages.plural_model_label');
    }

    public static function form(Schema $schema): Schema
    {
        return CustomPageForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CustomPagesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCustomPages::route('/'),
            'create' => CreateCustomPage::route('/create'),
            'edit' => EditCustomPage::route('/{record}/edit'),
        ];
    }
}
