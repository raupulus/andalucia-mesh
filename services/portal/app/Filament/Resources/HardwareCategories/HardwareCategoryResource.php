<?php

declare(strict_types=1);

namespace App\Filament\Resources\HardwareCategories;

use App\Filament\Resources\HardwareCategories\Pages\CreateHardwareCategory;
use App\Filament\Resources\HardwareCategories\Pages\EditHardwareCategory;
use App\Filament\Resources\HardwareCategories\Pages\ListHardwareCategories;
use App\Filament\Resources\HardwareCategories\Schemas\HardwareCategoryForm;
use App\Filament\Resources\HardwareCategories\Tables\HardwareCategoriesTable;
use App\Models\HardwareCategory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

/**
 * Recurso Filament para gestionar las categorías de hardware recomendado.
 */
class HardwareCategoryResource extends Resource
{
    protected static ?string $model = HardwareCategory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.hardware.group');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.hardware.categories_nav');
    }

    public static function getModelLabel(): string
    {
        return __('admin.hardware.category_model');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.hardware.category_plural');
    }

    public static function form(Schema $schema): Schema
    {
        return HardwareCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HardwareCategoriesTable::configure($table);
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
            'index' => ListHardwareCategories::route('/'),
            'create' => CreateHardwareCategory::route('/create'),
            'edit' => EditHardwareCategory::route('/{record}/edit'),
        ];
    }
}
