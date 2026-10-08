<?php

declare(strict_types=1);

namespace App\Filament\Resources\Webhooks;

use App\Filament\Resources\Webhooks\Pages\CreateWebhookDestination;
use App\Filament\Resources\Webhooks\Pages\EditWebhookDestination;
use App\Filament\Resources\Webhooks\Pages\ListWebhookDestinations;
use App\Filament\Resources\Webhooks\Schemas\WebhookDestinationForm;
use App\Filament\Resources\Webhooks\Tables\WebhookDestinationsTable;
use App\Models\WebhookDestination;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

/**
 * Recurso Filament para la gestión de destinos de webhooks desde el panel de operador (/admin).
 */
class WebhookDestinationResource extends Resource
{
    protected static ?string $model = WebhookDestination::class;

    protected static ?string $slug = 'webhook-destinations';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPaperAirplane;

    public static function getNavigationLabel(): string
    {
        return __('admin.webhooks.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('admin.webhooks.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.webhooks.plural_model_label');
    }

    public static function form(Schema $schema): Schema
    {
        return WebhookDestinationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WebhookDestinationsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWebhookDestinations::route('/'),
            'create' => CreateWebhookDestination::route('/create'),
            'edit' => EditWebhookDestination::route('/{record}/edit'),
        ];
    }
}
