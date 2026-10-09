<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

/**
 * Página de edición de usuarios (exclusiva para superadmin).
 */
class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label(__('admin.users.action_delete'))
                ->visible(fn (User $record): bool => (auth()->user()?->isSuperAdmin() ?? false) && auth()->id() !== $record->id),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $user = auth()->user();

        // El editor nunca puede cambiar su propio rol ni su estado activo
        if ($user?->isEditor()) {
            $data['role'] = $this->record->role;
            $data['activo'] = $this->record->activo;
        }

        // El admin nunca puede asignar rol superadmin
        if ($user?->isAdmin() && isset($data['role']) && $data['role'] === User::ROLE_SUPERADMIN) {
            $data['role'] = $this->record->role;
        }

        // Nadie salvo superadmin puede modificar el email
        if (! $user?->isSuperAdmin()) {
            unset($data['email']);
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
