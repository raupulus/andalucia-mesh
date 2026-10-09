<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Suggestion;
use App\Models\User;

/**
 * Política de autorización para sugerencias ciudadanas en el panel /admin.
 *
 * - Superadmin y Admin: gestión completa (revisar, aprobar, rechazar, notas y eliminar).
 * - Editor: solo lectura completa (visualizar listado y detalle; NO aprobar, NO rechazar, NO editar notas, NO eliminar).
 */
class SuggestionPolicy
{
    public function viewAny(User $user): bool
    {
        return (bool) $user->activo;
    }

    public function view(User $user, Suggestion $suggestion): bool
    {
        return (bool) $user->activo;
    }

    public function create(User $user): bool
    {
        return (bool) $user->activo && ! $user->isEditor();
    }

    public function update(User $user, Suggestion $suggestion): bool
    {
        return (bool) $user->activo && ! $user->isEditor();
    }

    public function delete(User $user, Suggestion $suggestion): bool
    {
        return (bool) $user->activo && ! $user->isEditor();
    }

    public function deleteAny(User $user): bool
    {
        return (bool) $user->activo && ! $user->isEditor();
    }
}
