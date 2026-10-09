<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\CustomPage;
use App\Models\User;

/**
 * Política de autorización para páginas y artículos del portal en el panel /admin.
 *
 * - Superadmin y Admin: gestión completa (redactar, editar, publicar/despublicar y eliminar).
 * - Editor: acceso completo para crear y editar contenidos; NO puede eliminar ni publicar/despublicar (nacen como borrador).
 */
class CustomPagePolicy
{
    public function viewAny(User $user): bool
    {
        return (bool) $user->activo;
    }

    public function view(User $user, CustomPage $page): bool
    {
        return (bool) $user->activo;
    }

    public function create(User $user): bool
    {
        return (bool) $user->activo;
    }

    public function update(User $user, CustomPage $page): bool
    {
        return (bool) $user->activo;
    }

    public function delete(User $user, CustomPage $page): bool
    {
        return (bool) $user->activo && ! $user->isEditor();
    }

    public function deleteAny(User $user): bool
    {
        return (bool) $user->activo && ! $user->isEditor();
    }
}
