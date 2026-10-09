<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Models\User;
use Illuminate\Auth\Events\Login;

/**
 * Registra el timestamp del último acceso cuando un usuario inicia sesión en la plataforma.
 */
class ActualizarUltimoAcceso
{
    /**
     * Procesa el evento de autenticación exitosa en el portal o panel de administración.
     *
     * @param  Login  $event  Evento de inicio de sesión con el usuario autenticado.
     */
    public function handle(Login $event): void
    {
        if ($event->user instanceof User) {
            $user = $event->user;
            $user->timestamps = false;
            $user->forceFill([
                'ultimo_acceso' => now(),
            ])->saveQuietly();
        }
    }
}
