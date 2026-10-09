<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware para registrar el timestamp de último acceso de operadores logueados en la intranet.
 *
 * Se ejecuta en el stack de autenticación del panel de administración (/admin).
 * Para evitar consultas repetitivas innecesarias a la base de datos, solo actualiza el timestamp
 * si aún no está registrado o si han transcurrido más de 5 minutos desde la última actualización.
 */
class RegistrarUltimoAcceso
{
    /**
     * Umbral en minutos para actualizar el timestamp de acceso en peticiones consecutivas.
     */
    public const UMBRAL_MINUTOS = 5;

    /**
     * Procesa la petición entrante para operadores autenticados en el panel.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user instanceof User) {
            $ultimoAcceso = $user->ultimo_acceso;

            if ($ultimoAcceso === null || $ultimoAcceso->diffInMinutes(now()) >= self::UMBRAL_MINUTOS) {
                $user->timestamps = false;
                $user->forceFill([
                    'ultimo_acceso' => now(),
                ])->saveQuietly();
            }
        }

        return $next($request);
    }
}
