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

    public static function getLabel(): string
    {
        return 'Mi perfil de operador';
    }

    public function getTitle(): string|Htmlable
    {
        return 'Mi perfil de operador';
    }

    /**
     * Define el esquema del formulario dividido en secciones de identidad y seguridad.
     */
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identidad del Operador')
                    ->description('Gestiona tu avatar personal (formato 1:1 con recorte circular), nombre visible y correo de acceso.')
                    ->schema([
                        $this->getAvatarFormComponent(),
                        $this->getNameFormComponent(),
                        $this->getEmailFormComponent(),
                    ]),

                Section::make('Seguridad y Contraseña')
                    ->description('Para modificar tu contraseña, introduce tu clave actual y define la nueva clave con confirmación.')
                    ->schema([
                        $this->getPasswordFormComponent(),
                        $this->getPasswordConfirmationFormComponent(),
                        $this->getCurrentPasswordFormComponent(),
                    ]),
            ]);
    }

    /**
     * Componente para subir el avatar o icono de operador con editor y cropper estricto 1:1.
     */
    protected function getAvatarFormComponent(): Component
    {
        return FileUpload::make('avatar_url')
            ->label('Icono de perfil / Avatar')
            ->avatar()
            ->image()
            ->imageEditor()
            ->circleCropper()
            ->imageCropAspectRatio('1:1')
            ->imageEditorAspectRatios(['1:1'])
            ->directory('avatars')
            ->disk('public')
            ->maxSize(2048)
            ->helperText('Formato cuadrado 1:1 con recorte circular disponible. Máximo 2 MB.');
    }

    /**
     * Componente de nombre visible.
     */
    protected function getNameFormComponent(): Component
    {
        return TextInput::make('name')
            ->label('Nombre del operador')
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
            ->label('Correo electrónico')
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
            ->label('Nueva contraseña')
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
            ->label('Confirmar nueva contraseña')
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
            ->label('Contraseña actual')
            ->helperText('Obligatoria para validar tu identidad al cambiar la contraseña o el correo.')
            ->password()
            ->autocomplete('current-password')
            ->currentPassword(guard: Filament::getAuthGuard())
            ->revealable(filament()->arePasswordsRevealable())
            ->required()
            ->visible(fn (Get $get): bool => filled($get('password')) || ($get('email') !== $this->getUser()->getAttributeValue('email')))
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
            ->label('Eliminar cuenta')
            ->icon('heroicon-o-trash')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('¿Eliminar tu cuenta de operador?')
            ->modalDescription('Esta acción es irreversible y eliminará definitivamente tu usuario, tu avatar y todos tus accesos al panel de administración. Para confirmar tu identidad, introduce tu contraseña actual.')
            ->modalSubmitActionLabel('Sí, eliminar mi cuenta definitivamente')
            ->form([
                TextInput::make('delete_confirm_password')
                    ->label('Contraseña actual para confirmar')
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
                    ->title('Tu cuenta ha sido eliminada correctamente')
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
