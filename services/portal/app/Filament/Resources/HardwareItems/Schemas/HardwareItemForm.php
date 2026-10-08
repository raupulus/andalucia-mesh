<?php

declare(strict_types=1);

namespace App\Filament\Resources\HardwareItems\Schemas;

use App\Models\HardwareCategory;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

/**
 * Esquema del formulario de gestión de artículos de hardware en Filament.
 */
class HardwareItemForm
{
    /**
     * Configura los campos del formulario de creación y edición de dispositivos de hardware.
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.hardware.section_item_info'))
                    ->description(__('admin.hardware.section_item_info_desc'))
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('category_id')
                                ->label(__('admin.hardware.field_category'))
                                ->relationship('category', 'slug')
                                ->getOptionLabelFromRecordUsing(fn (HardwareCategory $record): string => $record->translated_name)
                                ->searchable()
                                ->preload()
                                ->required(),

                            TextInput::make('name')
                                ->label(__('admin.hardware.field_model_name'))
                                ->placeholder('Ej. Heltec Wireless Tracker V1.1')
                                ->required()
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (string $operation, mixed $state, callable $set): void {
                                    if ($operation === 'create' && is_string($state) && $state !== '') {
                                        $set('slug', Str::slug($state));
                                    }
                                }),
                        ]),

                        TextInput::make('slug')
                            ->label(__('admin.hardware.field_slug'))
                            ->required()
                            ->unique(ignoreRecord: true),

                        Tabs::make('Descriptions')
                            ->tabs([
                                Tab::make(__('admin.hardware.tab_description_es'))
                                    ->schema([
                                        Textarea::make('description.es')
                                            ->label(__('admin.hardware.field_description_es'))
                                            ->placeholder('Explicación técnica, consumo energético, rendimiento y comportamiento en la malla...')
                                            ->rows(4)
                                            ->required(),
                                    ]),
                                Tab::make(__('admin.hardware.tab_description_en'))
                                    ->schema([
                                        Textarea::make('description.en')
                                            ->label(__('admin.hardware.field_description_en'))
                                            ->placeholder('Technical description, power consumption, mesh behavior...')
                                            ->rows(4),
                                    ]),
                            ]),
                    ]),

                Section::make(__('admin.hardware.section_media_links'))
                    ->schema([
                        FileUpload::make('image_path')
                            ->label(__('admin.hardware.field_image'))
                            ->disk('public')
                            ->directory('hardware')
                            ->image()
                            ->imageEditor()
                            ->imageEditorAspectRatios(['1:1'])
                            ->required(),

                        Grid::make(2)->schema([
                            TextInput::make('buy_url')
                                ->label(__('admin.hardware.field_buy_url'))
                                ->url()
                                ->placeholder('https://es.aliexpress.com/item/...')
                                ->required(),

                            TextInput::make('guide_url')
                                ->label(__('admin.hardware.field_guide_url'))
                                ->url()
                                ->placeholder('https://meshtastic.org/docs/hardware/devices/...')
                                ->nullable(),
                        ]),

                        Grid::make(2)->schema([
                            TextInput::make('last_price')
                                ->label(__('admin.hardware.field_last_price'))
                                ->numeric()
                                ->prefix('€')
                                ->placeholder('28.50'),

                            TextInput::make('currency')
                                ->label(__('admin.hardware.field_currency'))
                                ->default('EUR')
                                ->maxLength(3)
                                ->required(),
                        ]),
                    ]),

                Section::make(__('admin.hardware.section_visibility'))
                    ->schema([
                        Grid::make(3)->schema([
                            Toggle::make('is_featured')
                                ->label(__('admin.hardware.field_is_featured'))
                                ->default(false),

                            Toggle::make('is_active')
                                ->label(__('admin.hardware.field_is_active'))
                                ->default(true),

                            TextInput::make('sort_order')
                                ->label(__('admin.hardware.field_sort_order'))
                                ->numeric()
                                ->default(0),
                        ]),
                    ]),
            ]);
    }
}
