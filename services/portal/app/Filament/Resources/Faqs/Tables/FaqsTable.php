<?php

declare(strict_types=1);

namespace App\Filament\Resources\Faqs\Tables;

use App\Models\Faq;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

/**
 * Configuración de la tabla de preguntas frecuentes (FAQ) en el panel Filament.
 */
class FaqsTable
{
    /**
     * Define las columnas, filtros, ordenación y acciones para los operadores.
     */
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sort_order')
                    ->label(__('admin.faqs.col_sort_order'))
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('question')
                    ->label(__('admin.faqs.col_question'))
                    ->limit(65)
                    ->tooltip(fn (Faq $record): string => $record->question)
                    ->searchable()
                    ->wrap(),

                TextColumn::make('answer')
                    ->label(__('admin.faqs.col_answer'))
                    ->limit(75)
                    ->tooltip(fn (Faq $record): string => $record->answer)
                    ->searchable()
                    ->wrap(),

                IconColumn::make('is_active')
                    ->label(__('admin.faqs.col_is_active'))
                    ->boolean()
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('created_at')
                    ->label(__('admin.faqs.col_created'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort_order', 'asc')
            ->filters([
                TernaryFilter::make('is_active')
                    ->label(__('admin.faqs.filter_active')),
            ])
            ->recordActions([
                EditAction::make()->label(__('admin.faqs.action_edit')),
                DeleteAction::make()->label(__('admin.faqs.action_delete')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
