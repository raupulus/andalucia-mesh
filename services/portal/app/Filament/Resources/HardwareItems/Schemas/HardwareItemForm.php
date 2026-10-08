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
 * Esquema del formulario de gestión de artículos de hardware en Filament con soporte multi-idioma (ES/EN/PT).
 */
class HardwareItemForm
{
    /**
     * Configura los campos del formulario con la distribución requerida:
     * 1. Bloque de imagen arriba, separado y centrado.
     * 2. Bloque de dispositivo a ancho completo con pestañas de idioma para traducción.
     * 3. Bloque de enlaces y precios a ancho completo.
     * 4. Bloque de estado y prioridad a ancho completo al final.
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // 1. Bloque de imagen arriba, separado y centrado
                Section::make(__('admin.hardware.section_image'))
                    ->description(__('admin.hardware.section_image_desc'))
                    ->columnSpanFull()
                    ->schema([
                        FileUpload::make('image_path')
                            ->label(__('admin.hardware.field_image'))
                            ->disk('public')
                            ->directory('hardware')
                            ->image()
                            ->imageEditor()
                            ->imageEditorAspectRatios(['1:1'])
                            ->alignCenter()
                            ->required(),
                    ]),

                // 2. Bloque de dispositivo a ancho completo con selector de idiomas arriba para traducir
                Section::make(__('admin.hardware.section_device'))
                    ->description(__('admin.hardware.section_device_desc'))
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(['default' => 1, 'md' => 2])
                            ->schema([
                                Select::make('category_id')
                                    ->label(__('admin.hardware.field_category'))
                                    ->relationship('category', 'slug')
                                    ->getOptionLabelFromRecordUsing(fn (HardwareCategory $record): string => $record->translated_name)
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                TextInput::make('slug')
                                    ->label(__('admin.hardware.field_slug'))
                                    ->required()
                                    ->unique(ignoreRecord: true),
                            ]),

                        Tabs::make('Idiomas')
                            ->columnSpanFull()
                            ->tabs([
                                Tab::make(__('admin.hardware.tab_es'))
                                    ->badge('ES')
                                    ->schema([
                                        TextInput::make('name.es')
                                            ->label(__('admin.hardware.field_name_es'))
                                            ->placeholder('Ej. LilyGO T-Echo BME280')
                                            ->required()
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function (string $operation, mixed $state, callable $set): void {
                                                if ($operation === 'create' && is_string($state) && $state !== '') {
                                                    $set('slug', Str::slug($state));
                                                }
                                            }),

                                        Textarea::make('description.es')
                                            ->label(__('admin.hardware.field_description_es'))
                                            ->placeholder('Explicación técnica, consumo energético, rendimiento y comportamiento en la malla...')
                                            ->rows(4)
                                            ->required(),
                                    ]),

                                Tab::make(__('admin.hardware.tab_en'))
                                    ->badge('EN')
                                    ->schema([
                                        TextInput::make('name.en')
                                            ->label(__('admin.hardware.field_name_en'))
                                            ->placeholder('E.g. LilyGO T-Echo BME280'),

                                        Textarea::make('description.en')
                                            ->label(__('admin.hardware.field_description_en'))
                                            ->placeholder('Technical description, power consumption, mesh behavior...')
                                            ->rows(4),
                                    ]),

                                Tab::make(__('admin.hardware.tab_pt'))
                                    ->badge('PT')
                                    ->schema([
                                        TextInput::make('name.pt')
                                            ->label(__('admin.hardware.field_name_pt'))
                                            ->placeholder('Ex. LilyGO T-Echo BME280'),

                                        Textarea::make('description.pt')
                                            ->label(__('admin.hardware.field_description_pt'))
                                            ->placeholder('Explicação técnica, consumo de energia, comportamento na malha...')
                                            ->rows(4),
                                    ]),
                            ]),
                    ]),

                // 3. Bloque de enlaces y precios a ancho completo
                Section::make(__('admin.hardware.section_links'))
                    ->description(__('admin.hardware.section_links_desc'))
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(['default' => 1, 'md' => 2])
                            ->schema([
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

                        Grid::make(['default' => 1, 'md' => 2])
                            ->schema([
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

                // 4. Bloque de estado y visibilidad a ancho completo debajo del todo (la prioridad de ordenación se gestiona desde la tabla)
                Section::make(__('admin.hardware.section_status_priority'))
                    ->description(__('admin.hardware.section_status_priority_desc'))
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(['default' => 1, 'md' => 2])
                            ->schema([
                                Toggle::make('is_featured')
                                    ->label(__('admin.hardware.field_is_featured'))
                                    ->default(false),

                                Toggle::make('is_active')
                                    ->label(__('admin.hardware.field_is_active'))
                                    ->default(true),
                            ]),
                    ]),
            ]);
    }
}
