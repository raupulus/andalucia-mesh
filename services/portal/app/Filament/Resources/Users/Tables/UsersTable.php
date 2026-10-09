<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Tables;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

/**
 * Configuración de la tabla de usuarios y operadores en Filament.
 *
 * Visibilidad adaptada a roles:
 * - Superadmin: visualiza avatar, nombre, rol, email, estado activo y acciones de edición/eliminación.
 * - Admin: únicamente visualiza avatar y nombre (sin acceso a correos ni acciones de modificación).
 */
class UsersTable
{
    /**
     * Define las columnas, filtros y acciones de la tabla de operadores.
     */
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('avatar_url')
                    ->label(__('admin.users.col_avatar'))
                    ->disk('public')
                    ->circular()
                    ->size(36)
                    ->defaultImageUrl(fn (User $record): string => 'data:image/svg+xml;utf8,'.rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#007A33"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>')),

                TextColumn::make('name')
                    ->label(__('admin.users.col_name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('role')
                    ->label(__('admin.users.col_role'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        User::ROLE_SUPERADMIN => 'primary',
                        User::ROLE_ADMIN => 'warning',
                        User::ROLE_EDITOR => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        User::ROLE_SUPERADMIN => __('admin.users.role_superadmin'),
                        User::ROLE_ADMIN => __('admin.users.role_admin'),
                        User::ROLE_EDITOR => __('admin.users.role_editor'),
                        default => (string) $state,
                    })
                    ->sortable(),

                TextColumn::make('email')
                    ->label(__('admin.users.col_email'))
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->visible(fn (): bool => auth()->user()?->isSuperAdmin() ?? false),

                IconColumn::make('activo')
                    ->label(__('admin.users.col_active'))
                    ->boolean(),

                TextColumn::make('ultimo_acceso')
                    ->label(__('admin.users.col_last_login'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->visible(fn (): bool => auth()->user()?->isSuperAdmin() ?? false),
            ])
            ->defaultSort('name', 'asc')
            ->recordUrl(function (User $record): ?string {
                $user = auth()->user();
                if (! $user || ! $user->activo) {
                    return null;
                }
                if ($record->isSuperAdmin() && ! $user->isSuperAdmin()) {
                    return null;
                }
                if ($user->isSuperAdmin() || $user->isAdmin()) {
                    return UserResource::getUrl('edit', ['record' => $record]);
                }
                if ($user->isEditor() && $user->id === $record->id) {
                    return UserResource::getUrl('edit', ['record' => $record]);
                }

                return null;
            })
            ->filters([
                SelectFilter::make('role')
                    ->label(__('admin.users.col_role'))
                    ->options([
                        User::ROLE_SUPERADMIN => __('admin.users.role_superadmin'),
                        User::ROLE_ADMIN => __('admin.users.role_admin'),
                        User::ROLE_EDITOR => __('admin.users.role_editor'),
                    ]),

                TernaryFilter::make('activo')
                    ->label(__('admin.users.col_active')),
            ])
            ->recordActions([
                EditAction::make()
                    ->label(__('admin.users.action_edit'))
                    ->visible(function (User $record): bool {
                        $user = auth()->user();
                        if (! $user || ! $user->activo) {
                            return false;
                        }
                        if ($record->isSuperAdmin()) {
                            return $user->isSuperAdmin();
                        }
                        if ($user->isSuperAdmin() || $user->isAdmin()) {
                            return true;
                        }

                        return $user->isEditor() && $user->id === $record->id;
                    }),

                DeleteAction::make()
                    ->label(__('admin.users.action_delete'))
                    ->visible(fn (User $record): bool => (auth()->user()?->isSuperAdmin() ?? false) && auth()->id() !== $record->id),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn (): bool => auth()->user()?->isSuperAdmin() ?? false),
                ])->visible(fn (): bool => auth()->user()?->isSuperAdmin() ?? false),
            ]);
    }
}
