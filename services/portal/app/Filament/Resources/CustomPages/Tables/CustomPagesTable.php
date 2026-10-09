<?php

declare(strict_types=1);

namespace App\Filament\Resources\CustomPages\Tables;

use App\Models\CustomPage;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

/**
 * Configuración de la tabla de páginas y artículos en Filament.
 */
class CustomPagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                ImageColumn::make('featured_image')
                    ->label(__('admin.custom_pages.col_image'))
                    ->disk('public')
                    ->square()
                    ->defaultImageUrl(asset('img/revisa-nodo-banner.webp')),

                TextColumn::make('title')
                    ->label(__('admin.custom_pages.col_title'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (CustomPage $record): string => '/paginas/'.$record->slug),

                TextColumn::make('slug')
                    ->label(__('admin.custom_pages.col_slug'))
                    ->badge()
                    ->color('gray')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('keywords')
                    ->label(__('admin.custom_pages.col_keywords'))
                    ->badge()
                    ->color('success')
                    ->separator(','),

                ToggleColumn::make('is_active')
                    ->label(__('admin.custom_pages.col_is_active'))
                    ->disabled(fn (): bool => auth()->user()?->isEditor() ?? false),

                TextColumn::make('created_at')
                    ->label(__('admin.custom_pages.col_created_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label(__('admin.custom_pages.filter_is_active')),
            ])
            ->recordActions([
                Action::make('view_on_site')
                    ->label(__('admin.custom_pages.action_view_on_site'))
                    ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                    ->url(fn (CustomPage $record): string => '/paginas/'.$record->slug)
                    ->openUrlInNewTab(),
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
