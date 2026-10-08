<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

/**
 * Política de autorización para el CRUD de usuarios y operadores en el panel /admin.
 *
 * - Superadmin: puede listar (con vista completa de email y rol), crear, editar y eliminar usuarios (excepto a sí mismo).
 * - Admin: solo puede listar la existencia de operadores (vista restringida solo con avatar y nombre, sin emails, sin crear ni modificar).
 */
class UserPolicy
{
    /**
     * Determina si el operador puede listar los usuarios.
     */
    public function viewAny(User $user): bool
    {
        return (bool) $user->activo;
    }

    /**
     * Determina si el operador puede ver la ficha de un usuario.
     */
    public function view(User $user, User $model): bool
    {
        return (bool) $user->activo && $user->isSuperAdmin();
    }

    /**
     * Determina si el operador puede crear nuevos usuarios.
     */
    public function create(User $user): bool
    {
        return (bool) $user->activo && $user->isSuperAdmin();
    }

    /**
     * Determina si el operador puede editar usuarios existentes.
     */
    public function update(User $user, User $model): bool
    {
        return (bool) $user->activo && $user->isSuperAdmin();
    }

    /**
     * Determina si el operador puede eliminar un usuario.
     */
    public function delete(User $user, User $model): bool
    {
        return (bool) $user->activo && $user->isSuperAdmin() && $user->id !== $model->id;
    }

    /**
     * Determina si el operador puede restaurar un usuario eliminado.
     */
    public function restore(User $user, User $model): bool
    {
        return (bool) $user->activo && $user->isSuperAdmin();
    }

    /**
     * Determina si el operador puede eliminar permanentemente un usuario.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return (bool) $user->activo && $user->isSuperAdmin() && $user->id !== $model->id;
    }
}
