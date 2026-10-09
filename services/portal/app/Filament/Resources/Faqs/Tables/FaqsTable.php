<?php

declare(strict_types=1);

namespace App\Filament\Resources\Faqs\Tables;

use App\Models\Faq;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

/**
 * Configuración de la tabla de preguntas frecuentes (FAQ) en el panel Filament.
 */
class FaqsTable
{
    /**
     * Define las columnas, reordenación interactiva arrastrando filas, filtros y acciones.
     */
    public static function configure(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->defaultSort('sort_order', 'asc')
            ->columns([
                TextColumn::make('translated_question')
                    ->label(__('admin.faqs.col_question'))
                    ->lineClamp(2)
                    ->tooltip(fn (Faq $record): string => $record->getTranslatedQuestion())
                    ->searchable(query: function ($query, string $search): void {
                        $driver = $query->getConnection()->getDriverName();
                        if ($driver === 'pgsql') {
                            $query->whereRaw('question::text ILIKE ?', ["%{$search}%"]);
                        } else {
                            $query->where('question', 'like', "%{$search}%");
                        }
                    })
                    ->wrap(),

                TextColumn::make('translated_answer')
                    ->label(__('admin.faqs.col_answer'))
                    ->formatStateUsing(fn (string $state): string => preg_replace('/[*_#`]/', '', strip_tags($state)))
                    ->lineClamp(2)
                    ->tooltip(fn (Faq $record): string => strip_tags($record->getTranslatedAnswer()))
                    ->searchable(query: function ($query, string $search): void {
                        $driver = $query->getConnection()->getDriverName();
                        if ($driver === 'pgsql') {
                            $query->whereRaw('answer::text ILIKE ?', ["%{$search}%"]);
                        } else {
                            $query->where('answer', 'like', "%{$search}%");
                        }
                    })
                    ->wrap(),

                ToggleColumn::make('is_active')
                    ->label(__('admin.faqs.col_is_active'))
                    ->alignCenter(),

                TextColumn::make('created_at')
                    ->label(__('admin.faqs.col_created'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label(__('admin.faqs.filter_active')),
            ])
            ->recordActions([
                EditAction::make()->label(__('admin.faqs.action_edit')),
                DeleteAction::make()
                    ->label(__('admin.faqs.action_delete'))
                    ->visible(fn (): bool => ! (auth()->user()?->isEditor() ?? false)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ])->visible(fn (): bool => ! (auth()->user()?->isEditor() ?? false)),
            ]);
    }
}
