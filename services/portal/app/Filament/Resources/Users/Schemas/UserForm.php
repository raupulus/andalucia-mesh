<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use SensitiveParameter;

/**
 * Esquema del formulario para creación y edición de usuarios en Filament.
 * Solo accesible para operadores con rol superadmin.
 */
class UserForm
{
    /**
     * Configura los campos del formulario organizados en bloques de identidad y credenciales.
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make(__('admin.users.section_identity'))
                    ->description(__('admin.users.section_identity_desc'))
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(['default' => 1, 'sm' => 2])->schema([
                            TextInput::make('name')
                                ->label(__('admin.users.field_name'))
                                ->required()
                                ->maxLength(255),

                            TextInput::make('email')
                                ->label(__('admin.users.field_email'))
                                ->email()
                                ->required()
                                ->maxLength(255)
                                ->unique(ignoreRecord: true)
                                ->visible(fn (): bool => auth()->user()?->isSuperAdmin() ?? false),

                            Select::make('role')
                                ->label(__('admin.users.field_role'))
                                ->options(function (): array {
                                    $user = auth()->user();
                                    if ($user?->isSuperAdmin()) {
                                        return [
                                            User::ROLE_SUPERADMIN => __('admin.users.role_superadmin'),
                                            User::ROLE_ADMIN => __('admin.users.role_admin'),
                                            User::ROLE_EDITOR => __('admin.users.role_editor'),
                                        ];
                                    }

                                    return [
                                        User::ROLE_ADMIN => __('admin.users.role_admin'),
                                        User::ROLE_EDITOR => __('admin.users.role_editor'),
                                    ];
                                })
                                ->disabled(fn (): bool => auth()->user()?->isEditor() ?? true)
                                ->default(User::ROLE_ADMIN)
                                ->required(),

                            Toggle::make('activo')
                                ->label(__('admin.users.field_active'))
                                ->helperText(__('admin.users.field_active_helper'))
                                ->disabled(fn (): bool => auth()->user()?->isEditor() ?? false)
                                ->default(true),
                        ]),

                        FileUpload::make('avatar_url')
                            ->label(__('admin.users.field_avatar'))
                            ->avatar()
                            ->alignCenter()
                            ->image()
                            ->directory('avatars')
                            ->disk('public')
                            ->maxSize(2048)
                            ->columnSpanFull(),
                    ]),

                Section::make(__('admin.users.section_security'))
                    ->description(__('admin.users.section_security_desc'))
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(['default' => 1, 'sm' => 2])->schema([
                            TextInput::make('password')
                                ->label(__('admin.users.field_password'))
                                ->password()
                                ->revealable()
                                ->rule(Password::default())
                                ->required(fn (string $operation): bool => $operation === 'create')
                                ->dehydrated(fn (#[SensitiveParameter] ?string $state): bool => filled($state))
                                ->dehydrateStateUsing(fn (#[SensitiveParameter] string $state): string => Hash::make($state))
                                ->same('password_confirmation')
                                ->helperText(fn (string $operation): string => $operation === 'edit'
                                    ? __('admin.users.field_password_edit_helper')
                                    : __('admin.users.field_password_create_helper')
                                ),

                            TextInput::make('password_confirmation')
                                ->label(__('admin.users.field_password_confirmation'))
                                ->password()
                                ->revealable()
                                ->required(fn (string $operation): bool => $operation === 'create')
                                ->same('password')
                                ->dehydrated(false)
                                ->helperText(__('admin.users.field_password_confirmation_helper')),
                        ]),
                    ]),
            ]);
    }
}
