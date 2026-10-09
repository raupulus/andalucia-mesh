<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\WebhookDestination;

/**
 * Política de autorización para destinos de webhooks técnicos en el panel /admin.
 *
 * - Superadmin y Admin: gestión completa (listar, dar de alta, editar, probar ping y eliminar).
 * - Editor: sin acceso (módulo oculto y bloqueado para este rol).
 */
class WebhookDestinationPolicy
{
    public function viewAny(User $user): bool
    {
        return (bool) $user->activo && ! $user->isEditor();
    }

    public function view(User $user, WebhookDestination $destination): bool
    {
        return (bool) $user->activo && ! $user->isEditor();
    }

    public function create(User $user): bool
    {
        return (bool) $user->activo && ! $user->isEditor();
    }

    public function update(User $user, WebhookDestination $destination): bool
    {
        return (bool) $user->activo && ! $user->isEditor();
    }

    public function delete(User $user, WebhookDestination $destination): bool
    {
        return (bool) $user->activo && ! $user->isEditor();
    }

    public function deleteAny(User $user): bool
    {
        return (bool) $user->activo && ! $user->isEditor();
    }
}
