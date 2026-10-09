<?php

declare(strict_types=1);

namespace App\Filament\Resources\Suggestions\Tables;

use App\Models\Suggestion;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
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
                    ->label(__('admin.suggestions.col_created'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('status')
                    ->label(__('admin.suggestions.col_status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Suggestion::STATUS_APPROVED => 'success',
                        Suggestion::STATUS_REJECTED => 'danger',
                        default => 'warning',
                    })
                    ->formatStateUsing(fn (?string $state): string => ($state && __('admin.suggestions.statuses.'.$state) !== 'admin.suggestions.statuses.'.$state) ? __('admin.suggestions.statuses.'.$state) : (Suggestion::STATUSES[$state] ?? (string) $state))
                    ->sortable(),

                TextColumn::make('category')
                    ->label(__('admin.suggestions.col_category'))
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => ($state && __('admin.suggestions.categories.'.$state) !== 'admin.suggestions.categories.'.$state) ? __('admin.suggestions.categories.'.$state) : (Suggestion::CATEGORIES[$state] ?? (string) $state))
                    ->sortable(),

                TextColumn::make('content')
                    ->label(__('admin.suggestions.col_content'))
                    ->limit(65)
                    ->tooltip(fn (Suggestion $record): string => $record->content)
                    ->searchable()
                    ->wrap(),

                TextColumn::make('operator_notes')
                    ->label(__('admin.suggestions.col_has_notes'))
                    ->badge()
                    ->color(fn (?string $state): string => filled($state) ? 'info' : 'gray')
                    ->formatStateUsing(fn (?string $state): string => filled($state) ? __('admin.suggestions.has_notes_yes') : __('admin.suggestions.has_notes_no'))
                    ->tooltip(fn (Suggestion $record): ?string => $record->operator_notes)
                    ->alignCenter()
                    ->searchable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label(__('admin.suggestions.filter_status'))
                    ->options(fn () => [
                        Suggestion::STATUS_PENDING => __('admin.suggestions.statuses.pending'),
                        Suggestion::STATUS_APPROVED => __('admin.suggestions.statuses.approved'),
                        Suggestion::STATUS_REJECTED => __('admin.suggestions.statuses.rejected'),
                    ]),

                SelectFilter::make('category')
                    ->label(__('admin.suggestions.filter_category'))
                    ->options(fn () => collect(Suggestion::CATEGORIES)->mapWithKeys(fn ($v, $k) => [$k => __('admin.suggestions.categories.'.$k)])->toArray()),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label(__('admin.suggestions.action_approve'))
                    ->icon(Heroicon::OutlinedCheck)
                    ->color('success')
                    ->visible(fn (Suggestion $record): bool => ! (auth()->user()?->isEditor() ?? false) && $record->status !== Suggestion::STATUS_APPROVED)
                    ->action(function (Suggestion $record): void {
                        $record->update(['status' => Suggestion::STATUS_APPROVED]);
                        Notification::make()
                            ->title(__('admin.suggestions.action_approve_success'))
                            ->success()
                            ->send();
                    }),

                Action::make('reject')
                    ->label(__('admin.suggestions.action_reject'))
                    ->icon(Heroicon::OutlinedXMark)
                    ->color('danger')
                    ->visible(fn (Suggestion $record): bool => ! (auth()->user()?->isEditor() ?? false) && $record->status !== Suggestion::STATUS_REJECTED)
                    ->action(function (Suggestion $record): void {
                        $record->update(['status' => Suggestion::STATUS_REJECTED]);
                        Notification::make()
                            ->title(__('admin.suggestions.action_reject_success'))
                            ->danger()
                            ->send();
                    }),

                ViewAction::make(),

                EditAction::make()
                    ->label(__('admin.suggestions.action_edit'))
                    ->visible(fn (): bool => ! (auth()->user()?->isEditor() ?? false)),

                DeleteAction::make()
                    ->label(__('admin.suggestions.action_delete'))
                    ->visible(fn (): bool => ! (auth()->user()?->isEditor() ?? false)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ])->visible(fn (): bool => ! (auth()->user()?->isEditor() ?? false)),
            ]);
    }
}
