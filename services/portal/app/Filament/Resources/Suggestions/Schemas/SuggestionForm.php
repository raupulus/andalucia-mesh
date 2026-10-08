<?php

declare(strict_types=1);

namespace App\Filament\Resources\Suggestions\Schemas;

use App\Models\Suggestion;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * Esquema del formulario de gestión de sugerencias en Filament.
 */
class SuggestionForm
{
    /**
     * Configura los campos del formulario organizados en secciones legibles.
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.suggestions.section_detail'))
                    ->description(__('admin.suggestions.section_detail_desc'))
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('category')
                                ->label(__('admin.suggestions.col_category'))
                                ->options(fn () => collect(Suggestion::CATEGORIES)->mapWithKeys(fn ($v, $k) => [$k => __('admin.suggestions.categories.' . $k)])->toArray())
                                ->required(),

                            Select::make('status')
                                ->label(__('admin.suggestions.col_status'))
                                ->options(fn () => [
                                    Suggestion::STATUS_PENDING => __('admin.suggestions.statuses.pending'),
                                    Suggestion::STATUS_APPROVED => __('admin.suggestions.statuses.approved'),
                                    Suggestion::STATUS_REJECTED => __('admin.suggestions.statuses.rejected'),
                                ])
                                ->default(Suggestion::STATUS_PENDING)
                                ->required(),
                        ]),

                        Textarea::make('content')
                            ->label(__('admin.suggestions.field_proposal'))
                            ->rows(6)
                            ->required(),
                    ]),

                Section::make(__('admin.suggestions.section_admin'))
                    ->description(__('admin.suggestions.section_admin_desc'))
                    ->schema([
                        Textarea::make('operator_notes')
                            ->label(__('admin.suggestions.field_operator_notes'))
                            ->placeholder(__('admin.suggestions.placeholder_operator_notes'))
                            ->rows(4),

                        Grid::make(2)->schema([
                            TextInput::make('ip_hash')
                                ->label(__('admin.suggestions.field_ip_hash'))
                                ->disabled()
                                ->dehydrated(false),

                            DateTimePicker::make('created_at')
                                ->label(__('admin.suggestions.field_created_at'))
                                ->disabled()
                                ->dehydrated(false),
                        ]),
                    ]),
            ]);
    }
}
