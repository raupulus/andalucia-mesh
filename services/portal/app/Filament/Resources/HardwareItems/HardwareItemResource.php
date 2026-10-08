<?php

declare(strict_types=1);

namespace App\Filament\Resources\HardwareItems;

use App\Filament\Resources\HardwareItems\Pages\CreateHardwareItem;
use App\Filament\Resources\HardwareItems\Pages\EditHardwareItem;
use App\Filament\Resources\HardwareItems\Pages\ListHardwareItems;
use App\Filament\Resources\HardwareItems\Schemas\HardwareItemForm;
use App\Filament\Resources\HardwareItems\Tables\HardwareItemsTable;
use App\Models\HardwareItem;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

/**
 * Recurso Filament para gestionar artículos, placas y componentes de hardware recomendado.
 */
class HardwareItemResource extends Resource
{
    protected static ?string $model = HardwareItem::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCpuChip;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.hardware.group');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.hardware.items_nav');
    }

    public static function getModelLabel(): string
    {
        return __('admin.hardware.item_model');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.hardware.item_plural');
    }

    public static function form(Schema $schema): Schema
    {
        return HardwareItemForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HardwareItemsTable::configure($table);
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
            'index' => ListHardwareItems::route('/'),
            'create' => CreateHardwareItem::route('/create'),
            'edit' => EditHardwareItem::route('/{record}/edit'),
        ];
    }
}
