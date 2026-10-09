<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Faq;
use App\Models\User;

/**
 * Política de autorización para las preguntas frecuentes (FAQs) en el panel /admin.
 *
 * - Superadmin y Admin: acceso completo (crear, editar, reordenar y eliminar).
 * - Editor: puede crear, editar y reordenar preguntas frecuentes; NO puede eliminar.
 */
class FaqPolicy
{
    public function viewAny(User $user): bool
    {
        return (bool) $user->activo;
    }

    public function view(User $user, Faq $faq): bool
    {
        return (bool) $user->activo;
    }

    public function create(User $user): bool
    {
        return (bool) $user->activo;
    }

    public function update(User $user, Faq $faq): bool
    {
        return (bool) $user->activo;
    }

    public function delete(User $user, Faq $faq): bool
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
