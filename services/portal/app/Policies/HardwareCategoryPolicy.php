<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\HardwareCategory;
use App\Models\User;

/**
 * Política de autorización para categorías de hardware en el panel /admin.
 *
 * - Superadmin y Admin: gestión completa (crear, editar, activar, reordenar y eliminar).
 * - Editor: solo lectura (listar y consultar detalles; NO crear, NO editar, NO cambiar orden/estado, NO eliminar).
 */
class HardwareCategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return (bool) $user->activo;
    }

    public function view(User $user, HardwareCategory $category): bool
    {
        return (bool) $user->activo;
    }

    public function create(User $user): bool
    {
        return (bool) $user->activo && ! $user->isEditor();
    }

    public function update(User $user, HardwareCategory $category): bool
    {
        return (bool) $user->activo && ! $user->isEditor();
    }

    public function delete(User $user, HardwareCategory $category): bool
    {
        return (bool) $user->activo && ! $user->isEditor();
    }

    public function deleteAny(User $user): bool
    {
        return (bool) $user->activo && ! $user->isEditor();
    }

    public function reorder(User $user): bool
    {
        return (bool) $user->activo && ! $user->isEditor();
    }
}
