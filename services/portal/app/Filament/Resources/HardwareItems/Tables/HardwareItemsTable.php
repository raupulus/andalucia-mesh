<?php

declare(strict_types=1);

namespace App\Filament\Resources\HardwareItems\Tables;

use App\Models\HardwareCategory;
use App\Models\HardwareItem;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

/**
 * Configuración de la tabla de artículos de hardware en Filament.
 */
class HardwareItemsTable
{
    /**
     * Define las columnas, filtros, ordenación y acciones para los artículos de hardware.
     */
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_path')
                    ->label(__('admin.hardware.col_image'))
                    ->disk('public')
                    ->square()
                    ->size(48),

                TextColumn::make('name.es')
                    ->label(__('admin.hardware.col_name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('category.name')
                    ->label(__('admin.hardware.col_category'))
                    ->formatStateUsing(function (mixed $state, HardwareItem $record): string {
                        return $record->category?->translated_name ?? (is_array($state) ? ($state[app()->getLocale()] ?? $state['es'] ?? '') : (string) $state);
                    })
                    ->badge()
                    ->color('gray')
                    ->sortable(),

                TextColumn::make('last_price')
                    ->label(__('admin.hardware.col_price'))
                    ->formatStateUsing(function (mixed $state, HardwareItem $record): string {
                        return $record->formatted_price ?? '—';
                    })
                    ->sortable(),

                ToggleColumn::make('is_featured')
                    ->label(__('admin.hardware.col_is_featured')),

                ToggleColumn::make('is_active')
                    ->label(__('admin.hardware.col_is_active')),

                TextColumn::make('sort_order')
                    ->label(__('admin.hardware.col_sort_order'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort_order', 'asc')
            ->reorderable('sort_order')
            ->filters([
                SelectFilter::make('category_id')
                    ->label(__('admin.hardware.filter_category'))
                    ->relationship('category', 'slug')
                    ->getOptionLabelFromRecordUsing(fn (HardwareCategory $record): string => $record->translated_name),

                TernaryFilter::make('is_active')
                    ->label(__('admin.hardware.filter_active')),

                TernaryFilter::make('is_featured')
                    ->label(__('admin.hardware.filter_featured')),
            ])
            ->recordActions([
                EditAction::make(),
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
