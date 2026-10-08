<?php

declare(strict_types=1);

namespace App\Filament\Resources\CoordinatedRouters\Tables;

use App\Models\CoordinatedRouter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;

/**
 * Configuración de la tabla de routers coordinados en Filament con agrupación y filtros por provincia.
 */
class CoordinatedRoutersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->groups([
                Group::make('province')
                    ->label(__('admin.coordinated_routers.col_province'))
                    ->getTitleFromRecordUsing(fn (CoordinatedRouter $record) => CoordinatedRouter::provinceName($record->province)),
            ])
            ->defaultGroup('province')
            ->columns([
                ToggleColumn::make('approved')
                    ->label(__('admin.coordinated_routers.col_approved')),

                TextColumn::make('node_id')
                    ->label(__('admin.coordinated_routers.col_node_id'))
                    ->copyable()
                    ->searchable()
                    ->sortable(),

                TextColumn::make('short_name')
                    ->label(__('admin.coordinated_routers.col_short'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('long_name')
                    ->label(__('admin.coordinated_routers.col_long'))
                    ->limit(25)
                    ->searchable(),

                TextColumn::make('province')
                    ->label(__('admin.coordinated_routers.col_province'))
                    ->formatStateUsing(fn ($state) => CoordinatedRouter::provinceName((string) $state))
                    ->badge()
                    ->color('info')
                    ->sortable(),

                TextColumn::make('role')
                    ->label(__('admin.coordinated_routers.col_role'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'ROUTER' => 'success',
                        'REPEATER' => 'warning',
                        default => 'gray',
                    })
                    ->sortable(),

                IconColumn::make('is_gateway')
                    ->label(__('admin.coordinated_routers.col_is_gateway'))
                    ->boolean(),

                TextColumn::make('notes')
                    ->label(__('admin.coordinated_routers.col_notes'))
                    ->limit(25)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('last_seen_at')
                    ->label(__('admin.coordinated_routers.col_last_seen'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('province')
                    ->label(__('admin.coordinated_routers.filter_province'))
                    ->options(CoordinatedRouter::PROVINCES),

                TernaryFilter::make('approved')
                    ->label(__('admin.coordinated_routers.filter_approved')),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
