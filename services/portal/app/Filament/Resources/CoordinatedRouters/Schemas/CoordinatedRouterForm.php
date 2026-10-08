<?php

declare(strict_types=1);

namespace App\Filament\Resources\CoordinatedRouters\Schemas;

use App\Models\CoordinatedRouter;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * Esquema del formulario de gestión de routers coordinados en Filament.
 */
class CoordinatedRouterForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.coordinated_routers.section_details'))
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('node_id')
                            ->label(__('admin.coordinated_routers.col_node_id'))
                            ->placeholder('!a1b2c3d4')
                            ->required()
                            ->unique(ignoreRecord: true),

                        TextInput::make('short_name')
                            ->label(__('admin.coordinated_routers.col_short'))
                            ->maxLength(32),

                        TextInput::make('long_name')
                            ->label(__('admin.coordinated_routers.col_long'))
                            ->maxLength(128),

                        Select::make('province')
                            ->label(__('admin.coordinated_routers.col_province'))
                            ->options(CoordinatedRouter::PROVINCES)
                            ->required(),

                        Select::make('role')
                            ->label(__('admin.coordinated_routers.col_role'))
                            ->options([
                                'ROUTER' => 'ROUTER',
                                'ROUTER_LATE' => 'ROUTER_LATE',
                                'REPEATER' => 'REPEATER',
                            ])
                            ->default('ROUTER')
                            ->required(),

                        TextInput::make('hw_model')
                            ->label(__('admin.coordinated_routers.col_hw'))
                            ->maxLength(64),

                        Toggle::make('is_gateway')
                            ->label(__('admin.coordinated_routers.col_is_gateway')),
                    ]),

                Section::make(__('admin.coordinated_routers.section_approval'))
                    ->columnSpanFull()
                    ->schema([
                        Select::make('status')
                            ->label(__('admin.coordinated_routers.col_status'))
                            ->options([
                                CoordinatedRouter::STATUS_MANAGED => __('admin.coordinated_routers.status_managed_desc'),
                                CoordinatedRouter::STATUS_KNOWN => __('admin.coordinated_routers.status_known_desc'),
                                CoordinatedRouter::STATUS_NEW => __('admin.coordinated_routers.status_new_desc'),
                            ])
                            ->default(CoordinatedRouter::STATUS_NEW)
                            ->required()
                            ->helperText(__('admin.coordinated_routers.helper_status')),

                        Textarea::make('notes')
                            ->label(__('admin.coordinated_routers.col_notes'))
                            ->placeholder('Ubicación física, altitud, contacto del custodio o notas de coordinación...')
                            ->rows(3),
                    ]),
            ]);
    }
}
