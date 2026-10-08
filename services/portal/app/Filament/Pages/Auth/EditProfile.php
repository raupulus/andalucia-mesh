<?php

declare(strict_types=1);

namespace App\Filament\Pages\Auth;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Facades\Filament;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use SensitiveParameter;

/**
 * Página de perfil y edición de cuenta del operador en Filament.
 * Permite cambiar nombre, avatar con recorte 1:1, contraseña verificada y dar de baja la cuenta.
 */
class EditProfile extends BaseEditProfile
{
    protected static bool $isDiscovered = false;

    /**
     * Ancho del modal/página ampliado a 4xl (56rem / 896px) para una visualización espaciosa en escritorio.
     */
    protected Width | string | null $maxWidth = Width::FourExtraLarge;

    public function getMaxWidth(): Width | string | null
    {
        return $this->maxWidth ?? Width::FourExtraLarge;
    }

    public static function getLabel(): string
    {
        return __('admin.profile.title');
    }

    public function getTitle(): string|Htmlable
    {
        return __('admin.profile.title');
    }

    /**
     * Define el esquema del formulario dividido en secciones de identidad y seguridad.
     */
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.profile.identity_section'))
                    ->description(__('admin.profile.identity_desc'))
                    ->columns(['default' => 1, 'sm' => 2])
                    ->schema([
                        $this->getAvatarFormComponent(),
                        $this->getNameFormComponent(),
                        $this->getEmailFormComponent(),
                    ]),

                Section::make(__('admin.profile.security_section'))
                    ->description(__('admin.profile.security_desc'))
                    ->columns(['default' => 1, 'sm' => 2])
                    ->schema([
                        $this->getPasswordFormComponent(),
                        $this->getPasswordConfirmationFormComponent(),
                        $this->getCurrentPasswordFormComponent(),
                    ]),
            ]);
    }

    /**
     * Componente para subir el avatar o icono de operador con editor y cropper estricto 1:1 centrado.
     */
    protected function getAvatarFormComponent(): Component
    {
        return FileUpload::make('avatar_url')
            ->label(__('admin.profile.avatar_label'))
            ->avatar()
            ->alignCenter()
            ->image()
            ->imageEditor()
            ->circleCropper()
            ->imageCropAspectRatio('1:1')
            ->imageEditorAspectRatios(['1:1'])
            ->directory('avatars')
            ->disk('public')
            ->maxSize(2048)
            ->helperText(__('admin.profile.avatar_helper'))
            ->columnSpanFull()
            ->extraFieldWrapperAttributes([
                'class' => 'flex flex-col items-center justify-center text-center',
                'style' => 'text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center;',
            ]);
    }

    /**
     * Componente de nombre visible.
     */
    protected function getNameFormComponent(): Component
    {
        return TextInput::make('name')
            ->label(__('admin.profile.name_label'))
            ->required()
            ->maxLength(255)
            ->autofocus();
    }

    /**
     * Componente de correo electrónico con validación única.
     */
    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label(__('admin.profile.email_label'))
            ->email()
            ->required()
            ->maxLength(255)
            ->unique(ignoreRecord: true);
    }

    /**
     * Campo para la nueva contraseña con reglas de seguridad.
     */
    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label(__('admin.profile.new_password'))
            ->password()
            ->revealable(filament()->arePasswordsRevealable())
            ->rule(Password::default())
            ->autocomplete('new-password')
            ->dehydrated(fn (#[SensitiveParameter] $state): bool => filled($state))
            ->dehydrateStateUsing(fn (#[SensitiveParameter] $state): string => Hash::make($state))
            ->live(debounce: 500)
            ->same('passwordConfirmation');
    }

    /**
     * Campo de confirmación de la nueva contraseña.
     */
    protected function getPasswordConfirmationFormComponent(): Component
    {
        return TextInput::make('passwordConfirmation')
            ->label(__('admin.profile.confirm_password'))
            ->password()
            ->autocomplete('new-password')
            ->revealable(filament()->arePasswordsRevealable())
            ->required()
            ->visible(fn (Get $get): bool => filled($get('password')))
            ->dehydrated(false);
    }

    /**
     * Campo de verificación de la contraseña actual del operador.
     */
    protected function getCurrentPasswordFormComponent(): Component
    {
        return TextInput::make('currentPassword')
            ->label(__('admin.profile.current_password'))
            ->helperText(__('admin.profile.current_password_helper'))
            ->password()
            ->autocomplete('current-password')
            ->currentPassword(guard: Filament::getAuthGuard())
            ->revealable(filament()->arePasswordsRevealable())
            ->required()
            ->visible(fn (Get $get): bool => filled($get('password')) || ($get('email') !== $this->getUser()->getAttributeValue('email')))
            ->columnSpanFull()
            ->dehydrated(false);
    }

    /**
     * Acciones del pie del formulario, incluyendo el botón de eliminar cuenta.
     *
     * @return array<Action|ActionGroup>
     */
    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction(),
            $this->getCancelFormAction(),
            $this->getDeleteAccountAction(),
        ];
    }

    /**
     * Acciones de cabecera de la página.
     *
     * @return array<Action|ActionGroup>
     */
    protected function getHeaderActions(): array
    {
        return [
            $this->getDeleteAccountAction(),
        ];
    }

    /**
     * Acción destructiva para eliminar la cuenta personal del operador con confirmación por contraseña.
     */
    protected function getDeleteAccountAction(): Action
    {
        return Action::make('deleteAccount')
            ->label(__('admin.profile.delete_section'))
            ->icon('heroicon-o-trash')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading(__('admin.profile.delete_modal_heading'))
            ->modalDescription(__('admin.profile.delete_modal_desc'))
            ->modalSubmitActionLabel(__('admin.profile.btn_delete_confirm'))
            ->form([
                TextInput::make('delete_confirm_password')
                    ->label(__('admin.profile.current_password_confirm'))
                    ->password()
                    ->revealable()
                    ->required()
                    ->currentPassword(guard: Filament::getAuthGuard()),
            ])
            ->action(function (): void {
                /** @var User $user */
                $user = $this->getUser();

                // Purgar avatar almacenado en el disco público si procede
                if ($user->avatar_url && ! str_starts_with($user->avatar_url, 'http://') && ! str_starts_with($user->avatar_url, 'https://')) {
                    Storage::disk('public')->delete($user->avatar_url);
                }

                // Cerrar sesión e invalidar tokens
                Filament::auth()->logout();

                if (request()->hasSession()) {
                    request()->session()->invalidate();
                    request()->session()->regenerateToken();
                }

                // Borrar registro de la base de datos
                $user->delete();

                Notification::make()
                    ->title(__('admin.profile.delete_success'))
                    ->success()
                    ->send();

                $this->redirect(Filament::getLoginUrl());
            });
    }

    /**
     * Limpia el archivo de avatar anterior si el usuario lo reemplaza o vacía.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(#[SensitiveParameter] array $data): array
    {
        $currentUser = $this->getUser();
        $oldAvatar = $currentUser->getAttributeValue('avatar_url');
        $newAvatar = $data['avatar_url'] ?? null;

        if ($oldAvatar && $oldAvatar !== $newAvatar && ! str_starts_with((string) $oldAvatar, 'http://') && ! str_starts_with((string) $oldAvatar, 'https://')) {
            Storage::disk('public')->delete((string) $oldAvatar);
        }

        return parent::mutateFormDataBeforeSave($data);
    }
}
