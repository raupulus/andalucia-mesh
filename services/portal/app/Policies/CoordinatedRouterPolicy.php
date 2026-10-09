<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\CoordinatedRouter;
use App\Models\User;

/**
 * Política de autorización para routers coordinados de infraestructura en el panel /admin.
 *
 * - Superadmin y Admin: gestión completa (alta, edición, asignación de estado y baja).
 * - Editor: solo lectura (listar y consultar detalles; NO dar de alta, NO editar, NO cambiar estado, NO eliminar).
 */
class CoordinatedRouterPolicy
{
    public function viewAny(User $user): bool
    {
        return (bool) $user->activo;
    }

    public function view(User $user, CoordinatedRouter $router): bool
    {
        return (bool) $user->activo;
    }

    public function create(User $user): bool
    {
        return (bool) $user->activo && ! $user->isEditor();
    }

    public function update(User $user, CoordinatedRouter $router): bool
    {
        return (bool) $user->activo && ! $user->isEditor();
    }

    public function delete(User $user, CoordinatedRouter $router): bool
    {
        return (bool) $user->activo && ! $user->isEditor();
    }

    public function deleteAny(User $user): bool
    {
        return (bool) $user->activo && ! $user->isEditor();
    }
}
