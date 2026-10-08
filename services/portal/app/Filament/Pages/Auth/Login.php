<?php

declare(strict_types=1);

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Illuminate\Contracts\Support\Htmlable;

/**
 * Página de inicio de sesión personalizada para el panel de operador.
 *
 * Adapta los textos, encabezados y la experiencia de autenticación a la
 * identidad y lenguaje del proyecto Andalucía Mesh.
 */
class Login extends BaseLogin
{
    /**
     * Título principal visible sobre el formulario de acceso.
     */
    public function getHeading(): string | Htmlable
    {
        return 'Acceso de Operador';
    }

    /**
     * Subtítulo descriptivo con información de contexto de la red.
     */
    public function getSubHeading(): string | Htmlable | null
    {
        return 'Gestión técnica y supervisión de la red comunitaria regional';
    }
}
