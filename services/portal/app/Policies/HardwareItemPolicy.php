<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\HardwareItem;
use App\Models\User;

/**
 * Política de autorización para artículos y componentes de hardware en el panel /admin.
 *
 * - Superadmin y Admin: gestión completa (crear, editar, activar, destacar y eliminar).
 * - Editor: acceso completo a crear, editar, destacar y activar componentes; NO puede eliminar artículos.
 */
class HardwareItemPolicy
{
    public function viewAny(User $user): bool
    {
        return (bool) $user->activo;
    }

    public function view(User $user, HardwareItem $item): bool
    {
        return (bool) $user->activo;
    }

    public function create(User $user): bool
    {
        return (bool) $user->activo;
    }

    public function update(User $user, HardwareItem $item): bool
    {
        return (bool) $user->activo;
    }

    public function delete(User $user, HardwareItem $item): bool
    {
        return (bool) $user->activo && ! $user->isEditor();
    }

    public function deleteAny(User $user): bool
    {
        return (bool) $user->activo && ! $user->isEditor();
    }

    public function reorder(User $user): bool
    {
        return (bool) $user->activo;
    }
}
