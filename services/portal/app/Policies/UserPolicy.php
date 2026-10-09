<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

/**
 * Política de autorización para el CRUD de usuarios y operadores en el panel /admin.
 *
 * - Superadmin: puede listar con vista completa de emails y accesos, crear, editar a cualquier usuario y eliminar (excepto a sí mismo).
 * - Admin: lista con privacidad (sin ver emails ni último acceso), puede editar a otros usuarios (admin, editor) y a sí mismo, pero NUNCA a un superadmin ni eliminar usuarios.
 * - Editor: lista con privacidad (sin ver emails ni último acceso), solo puede editar su propia cuenta y NUNCA eliminar a nadie.
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
     * Determina si el operador puede ver la ficha o detalles de un usuario.
     */
    public function view(User $user, User $model): bool
    {
        if (! $user->activo) {
            return false;
        }

        if ($user->isSuperAdmin() || $user->isAdmin()) {
            return true;
        }

        // El editor solo puede acceder a su propio registro
        return $user->id === $model->id;
    }

    /**
     * Determina si el operador puede crear nuevos usuarios.
     * Exclusivo para superadministradores.
     */
    public function create(User $user): bool
    {
        return (bool) $user->activo && $user->isSuperAdmin();
    }

    /**
     * Determina si el operador puede editar un usuario existente.
     *
     * - Ningún rol puede editar a un superadmin excepto otro superadmin.
     * - Superadmin puede editar a cualquier usuario.
     * - Admin puede editar a otros usuarios excepto superadmins.
     * - Editor solo puede editar su propia cuenta.
     */
    public function update(User $user, User $model): bool
    {
        if (! $user->activo) {
            return false;
        }

        // Nadie que no sea superadmin puede editar a un superadmin
        if ($model->isSuperAdmin()) {
            return $user->isSuperAdmin();
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isEditor()) {
            return $user->id === $model->id;
        }

        return false;
    }

    /**
     * Determina si el operador puede eliminar un usuario.
     * Exclusivo para superadministradores (y nunca a sí mismo).
     */
    public function delete(User $user, User $model): bool
    {
        return (bool) $user->activo && $user->isSuperAdmin() && $user->id !== $model->id;
    }

    /**
     * Determina si el operador puede eliminar masivamente usuarios.
     */
    public function deleteAny(User $user): bool
    {
        return (bool) $user->activo && $user->isSuperAdmin();
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
