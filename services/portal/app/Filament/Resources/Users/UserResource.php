<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\Tables\UsersTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

/**
 * Recurso Filament para gestionar los usuarios y operadores de la plataforma.
 *
 * Control de acceso basado en roles:
 * - superadmin: visualiza listado completo (nombre, rol, email, avatar, estado), crea, edita y elimina otros operadores.
 * - admin: puede acceder al listado para constancia de operadores, visualizando únicamente avatar y nombre. No puede ver emails, ni crear ni modificar.
 */
class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?int $navigationSort = 50;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.users.nav_group');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.users.nav_label');
    }

    public static function getModelLabel(): string
    {
        return __('admin.users.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.users.plural_model_label');
    }

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
    }

    public static function canViewAny(): bool
    {
        return (bool) (auth()->user()?->activo ?? false);
    }

    public static function canCreate(): bool
    {
        return (bool) (auth()->user()?->isSuperAdmin() ?? false);
    }

    public static function canEdit(Model $record): bool
    {
        return (bool) (auth()->user()?->isSuperAdmin() ?? false);
    }

    public static function canDelete(Model $record): bool
    {
        return (bool) (auth()->user()?->isSuperAdmin() ?? false) && auth()->id() !== $record->id;
    }

    public static function canView(Model $record): bool
    {
        return (bool) (auth()->user()?->isSuperAdmin() ?? false);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
