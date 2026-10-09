<?php

declare(strict_types=1);

namespace App\Filament\Resources\HardwareCategories\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

/**
 * Configuración de la tabla de categorías de hardware en Filament.
 */
class HardwareCategoriesTable
{
    /**
     * Define las columnas, ordenación y acciones para las categorías de hardware.
     */
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name.es')
                    ->label(__('admin.hardware.col_name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('slug')
                    ->label(__('admin.hardware.col_slug'))
                    ->copyable()
                    ->searchable()
                    ->sortable(),

                TextColumn::make('items_count')
                    ->label(__('admin.hardware.col_items_count'))
                    ->counts('items')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                TextColumn::make('sort_order')
                    ->label(__('admin.hardware.col_sort_order'))
                    ->sortable(),

                ToggleColumn::make('is_active')
                    ->label(__('admin.hardware.col_is_active'))
                    ->disabled(fn (): bool => auth()->user()?->isEditor() ?? false),
            ])
            ->defaultSort('sort_order', 'asc')
            ->reorderable('sort_order', fn (): bool => ! (auth()->user()?->isEditor() ?? false))
            ->recordActions([
                ViewAction::make(),
                EditAction::make()
                    ->visible(fn (): bool => ! (auth()->user()?->isEditor() ?? false)),
                DeleteAction::make()
                    ->visible(fn (): bool => ! (auth()->user()?->isEditor() ?? false)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ])->visible(fn (): bool => ! (auth()->user()?->isEditor() ?? false)),
            ]);
    }
}
