<?php

declare(strict_types=1);

namespace App\Filament\Resources\Suggestions\Tables;

use App\Models\Suggestion;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

/**
 * Configuración de la tabla de sugerencias en el panel Filament.
 */
class SuggestionsTable
{
    /**
     * Define las columnas, filtros, ordenación y acciones disponibles para el operador.
     */
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('category')
                    ->label('Categoría')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => Suggestion::CATEGORIES[$state] ?? (string) $state)
                    ->sortable(),

                TextColumn::make('content')
                    ->label('Sugerencia')
                    ->limit(65)
                    ->tooltip(fn (Suggestion $record): string => $record->content)
                    ->searchable()
                    ->wrap(),

                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Suggestion::STATUS_APPROVED => 'success',
                        Suggestion::STATUS_REJECTED => 'danger',
                        default => 'warning',
                    })
                    ->formatStateUsing(fn (?string $state): string => Suggestion::STATUSES[$state] ?? (string) $state)
                    ->sortable(),

                TextColumn::make('operator_notes')
                    ->label('Notas operador')
                    ->limit(40)
                    ->placeholder('—')
                    ->tooltip(fn (Suggestion $record): ?string => $record->operator_notes)
                    ->searchable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Filtrar por estado')
                    ->options(Suggestion::STATUSES),

                SelectFilter::make('category')
                    ->label('Filtrar por categoría')
                    ->options(Suggestion::CATEGORIES),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('Aprobar')
                    ->icon(Heroicon::OutlinedCheck)
                    ->color('success')
                    ->visible(fn (Suggestion $record): bool => $record->status !== Suggestion::STATUS_APPROVED)
                    ->action(function (Suggestion $record): void {
                        $record->update(['status' => Suggestion::STATUS_APPROVED]);
                        Notification::make()
                            ->title('Sugerencia aprobada')
                            ->success()
                            ->send();
                    }),

                Action::make('reject')
                    ->label('Rechazar')
                    ->icon(Heroicon::OutlinedXMark)
                    ->color('danger')
                    ->visible(fn (Suggestion $record): bool => $record->status !== Suggestion::STATUS_REJECTED)
                    ->action(function (Suggestion $record): void {
                        $record->update(['status' => Suggestion::STATUS_REJECTED]);
                        Notification::make()
                            ->title('Sugerencia rechazada')
                            ->danger()
                            ->send();
                    }),

                EditAction::make()
                    ->label('Editar / Notas'),

                DeleteAction::make()
                    ->label('Eliminar'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
