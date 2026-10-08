<?php

declare(strict_types=1);

namespace App\Filament\Resources\HardwareCategories\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;


/**
 * Esquema del formulario de gestión de categorías de hardware en Filament con soporte multi-idioma (ES/EN/PT).
 */
class HardwareCategoryForm
{
    /**
     * Configura los campos del formulario con pestañas de idioma en la cabecera del bloque de textos
     * a ancho completo y bloque de configuración y visibilidad debajo a ancho completo.
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.hardware.section_translations'))
                    ->description(__('admin.hardware.section_translations_desc'))
                    ->columnSpanFull()
                    ->schema([
                        Tabs::make('Idiomas')
                            ->tabs([
                                Tab::make(__('admin.hardware.tab_es'))
                                    ->badge('ES')
                                    ->schema([
                                        TextInput::make('name.es')
                                            ->label(__('admin.hardware.field_name_es'))
                                            ->required()
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function (string $operation, mixed $state, callable $set): void {
                                                if ($operation === 'create' && is_string($state) && $state !== '') {
                                                    $set('slug', Str::slug($state));
                                                }
                                            }),
                                        Textarea::make('description.es')
                                            ->label(__('admin.hardware.field_description_es'))
                                            ->rows(3),
                                    ]),

                                Tab::make(__('admin.hardware.tab_en'))
                                    ->badge('EN')
                                    ->schema([
                                        TextInput::make('name.en')
                                            ->label(__('admin.hardware.field_name_en')),
                                        Textarea::make('description.en')
                                            ->label(__('admin.hardware.field_description_en'))
                                            ->rows(3),
                                    ]),

                                Tab::make(__('admin.hardware.tab_pt'))
                                    ->badge('PT')
                                    ->schema([
                                        TextInput::make('name.pt')
                                            ->label(__('admin.hardware.field_name_pt')),
                                        Textarea::make('description.pt')
                                            ->label(__('admin.hardware.field_description_pt'))
                                            ->rows(3),
                                    ]),
                            ])
                            ->columnSpanFull(),
                    ]),

                Section::make(__('admin.hardware.section_settings'))
                    ->description(__('admin.hardware.section_settings_desc'))
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('slug')
                            ->label(__('admin.hardware.field_slug'))
                            ->required()
                            ->unique(ignoreRecord: true),

                        Toggle::make('is_active')
                            ->label(__('admin.hardware.field_is_active'))
                            ->default(true),
                    ]),

            ]);
    }
}
