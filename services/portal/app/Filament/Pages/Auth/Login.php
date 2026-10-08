<?php

declare(strict_types=1);

namespace App\Filament\Pages\Auth;

use App\Servicios\TurnstileService;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Validation\ValidationException;

/**
 * Página de inicio de sesión personalizada para el panel de operador.
 *
 * Adapta los textos, encabezados y la experiencia de autenticación a la
 * identidad y lenguaje del proyecto Andalucía Mesh, e incorpora la validación
 * de seguridad Cloudflare Turnstile (captcha).
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

    /**
     * Define el esquema del formulario de autenticación, integrando el captcha
     * de Cloudflare Turnstile cuando el servicio esté activo en la configuración.
     */
    public function form(Schema $schema): Schema
    {
        $components = [
            $this->getEmailFormComponent(),
            $this->getPasswordFormComponent(),
            $this->getRememberFormComponent(),
        ];

        $turnstile = app(TurnstileService::class);
        if ($turnstile->isEnabled()) {
            $components[] = View::make('filament.auth.turnstile');
        }

        return $schema->components($components);
    }

    /**
     * Procesa la autenticación del operador validando previamente el token del captcha.
     *
     * @throws ValidationException Si el token del captcha es inválido o no se resuelve
     */
    public function authenticate(): ?LoginResponse
    {
        $turnstile = app(TurnstileService::class);

        if ($turnstile->isEnabled()) {
            $token = $this->data['turnstile_token']
                ?? request()->input('cf-turnstile-response')
                ?? request()->input('data.turnstile_token');

            $ip = request()->ip();

            if (empty($token) || ! $turnstile->verify($token, $ip)) {
                throw ValidationException::withMessages([
                    'data.email' => 'La comprobación de seguridad (captcha) no es válida o ha caducado. Por favor, resuélvela de nuevo.',
                ]);
            }
        }

        return parent::authenticate();
    }
}
