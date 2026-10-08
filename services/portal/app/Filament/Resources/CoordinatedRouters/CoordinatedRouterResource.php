<?php

declare(strict_types=1);

namespace App\Filament\Resources\CoordinatedRouters;

use App\Filament\Resources\CoordinatedRouters\Pages\CreateCoordinatedRouter;
use App\Filament\Resources\CoordinatedRouters\Pages\EditCoordinatedRouter;
use App\Filament\Resources\CoordinatedRouters\Pages\ListCoordinatedRouters;
use App\Filament\Resources\CoordinatedRouters\Schemas\CoordinatedRouterForm;
use App\Filament\Resources\CoordinatedRouters\Tables\CoordinatedRoutersTable;
use App\Models\CoordinatedRouter;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

/**
 * Recurso Filament para gestionar los routers coordinados oficiales de Andalucía Mesh.
 */
class CoordinatedRouterResource extends Resource
{
    protected static ?string $model = CoordinatedRouter::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedServerStack;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.coordinated_routers.nav_group');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.coordinated_routers.nav_label');
    }

    public static function getModelLabel(): string
    {
        return __('admin.coordinated_routers.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.coordinated_routers.plural_model_label');
    }

    public static function form(Schema $schema): Schema
    {
        return CoordinatedRouterForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CoordinatedRoutersTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCoordinatedRouters::route('/'),
            'create' => CreateCoordinatedRouter::route('/create'),
            'edit' => EditCoordinatedRouter::route('/{record}/edit'),
        ];
    }
}
