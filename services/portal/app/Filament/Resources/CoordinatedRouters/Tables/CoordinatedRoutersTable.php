<?php

declare(strict_types=1);

namespace App\Filament\Resources\CoordinatedRouters\Tables;

use App\Models\CoordinatedRouter;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

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
                TextColumn::make('status')
                    ->label(__('admin.coordinated_routers.col_status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        CoordinatedRouter::STATUS_MANAGED => 'success',
                        CoordinatedRouter::STATUS_KNOWN => 'warning',
                        CoordinatedRouter::STATUS_NEW => 'danger',
                        default => 'gray',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        CoordinatedRouter::STATUS_MANAGED => 'heroicon-m-check-badge',
                        CoordinatedRouter::STATUS_KNOWN => 'heroicon-m-eye',
                        CoordinatedRouter::STATUS_NEW => 'heroicon-m-sparkles',
                        default => 'heroicon-m-question-mark-circle',
                    })
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        CoordinatedRouter::STATUS_MANAGED => __('admin.coordinated_routers.status_managed'),
                        CoordinatedRouter::STATUS_KNOWN => __('admin.coordinated_routers.status_known'),
                        CoordinatedRouter::STATUS_NEW => __('admin.coordinated_routers.status_new'),
                        default => (string) $state,
                    })
                    ->sortable(),

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
                SelectFilter::make('status')
                    ->label(__('admin.coordinated_routers.filter_status'))
                    ->options([
                        CoordinatedRouter::STATUS_MANAGED => __('admin.coordinated_routers.status_managed'),
                        CoordinatedRouter::STATUS_KNOWN => __('admin.coordinated_routers.status_known'),
                        CoordinatedRouter::STATUS_NEW => __('admin.coordinated_routers.status_new'),
                    ]),

                SelectFilter::make('province')
                    ->label(__('admin.coordinated_routers.filter_province'))
                    ->options(CoordinatedRouter::PROVINCES),
            ])
            ->recordActions([
                Action::make('mark_managed')
                    ->label(__('admin.coordinated_routers.action_mark_managed'))
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn (CoordinatedRouter $record): bool => ! $record->isManaged())
                    ->action(function (CoordinatedRouter $record): void {
                        $record->update(['status' => CoordinatedRouter::STATUS_MANAGED]);
                        Notification::make()
                            ->title(__('admin.coordinated_routers.notify_status_managed', ['node' => $record->short_name ?: $record->node_id]))
                            ->success()
                            ->send();
                    }),

                Action::make('mark_known')
                    ->label(__('admin.coordinated_routers.action_mark_known'))
                    ->icon('heroicon-o-eye')
                    ->color('warning')
                    ->visible(fn (CoordinatedRouter $record): bool => ! $record->isKnown())
                    ->action(function (CoordinatedRouter $record): void {
                        $record->update(['status' => CoordinatedRouter::STATUS_KNOWN]);
                        Notification::make()
                            ->title(__('admin.coordinated_routers.notify_status_known', ['node' => $record->short_name ?: $record->node_id]))
                            ->warning()
                            ->send();
                    }),

                Action::make('mark_new')
                    ->label(__('admin.coordinated_routers.action_mark_new'))
                    ->icon('heroicon-o-sparkles')
                    ->color('danger')
                    ->visible(fn (CoordinatedRouter $record): bool => ! $record->isNew())
                    ->action(function (CoordinatedRouter $record): void {
                        $record->update(['status' => CoordinatedRouter::STATUS_NEW]);
                        Notification::make()
                            ->title(__('admin.coordinated_routers.notify_status_new', ['node' => $record->short_name ?: $record->node_id]))
                            ->danger()
                            ->send();
                    }),

                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('mark_managed_bulk')
                        ->label(__('admin.coordinated_routers.action_mark_managed'))
                        ->icon('heroicon-o-check-badge')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each(fn (CoordinatedRouter $r) => $r->update(['status' => CoordinatedRouter::STATUS_MANAGED]));
                            Notification::make()
                                ->title(__('admin.coordinated_routers.notify_bulk_success'))
                                ->success()
                                ->send();
                        }),

                    BulkAction::make('mark_known_bulk')
                        ->label(__('admin.coordinated_routers.action_mark_known'))
                        ->icon('heroicon-o-eye')
                        ->color('warning')
                        ->action(function (Collection $records): void {
                            $records->each(fn (CoordinatedRouter $r) => $r->update(['status' => CoordinatedRouter::STATUS_KNOWN]));
                            Notification::make()
                                ->title(__('admin.coordinated_routers.notify_bulk_success'))
                                ->warning()
                                ->send();
                        }),

                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
