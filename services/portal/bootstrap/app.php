<?php

declare(strict_types=1);

use App\Excepciones\FuenteNoDisponible;
use App\Http\Middleware\PortalLocaleMiddleware;
use App\Http\Middleware\RespuestaApi;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Validation\ValidationException;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'api/v1',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Confianza en proxies inversos (Cloudflare y Nginx local)
        $middleware->trustProxies(at: '*');

        // Eliminar cookies y sesiones del grupo público web para cumplir la política de cero rastreo
        $middleware->removeFromGroup('web', [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            ShareErrorsFromSession::class,
            ValidateCsrfToken::class,
            PreventRequestForgery::class,
        ]);

        // Registrar middleware de detección de idioma sin cookies (RN-48)
        $middleware->appendToGroup('web', [
            PortalLocaleMiddleware::class,
        ]);

        // Configurar middleware del grupo api
        $middleware->appendToGroup('api', [
            'throttle:api-publica',
            RespuestaApi::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->is('api/v1') || $request->is('api/v1/*')) {
                $status = 500;
                $code = 'internal_error';
                $message = 'Error interno del servidor.';
                $headers = [
                    'Content-Type' => 'application/json; charset=utf-8',
                    'Cache-Control' => 'no-store',
                ];
                $extra = [];

                if ($e instanceof ValidationException) {
                    $status = 400;
                    $code = 'invalid_parameter';
                    $message = 'Parámetros inválidos en la petición.';
                    $extra['parameters'] = $e->errors();
                } elseif ($e instanceof NotFoundHttpException) {
                    $status = 404;
                    $code = 'not_found';
                    $message = 'El recurso solicitado no fue encontrado.';
                    $headers['Cache-Control'] = 'public, max-age=60';
                } elseif ($e instanceof MethodNotAllowedHttpException) {
                    $status = 405;
                    $code = 'method_not_allowed';
                    $message = 'Método HTTP no permitido. Solo se admite GET.';
                    $headers['Allow'] = 'GET, HEAD';
                } elseif ($e instanceof ThrottleRequestsException) {
                    $status = 429;
                    $code = 'rate_limited';
                    $message = 'Demasiadas peticiones: espera un minuto antes de reintentar.';
                    if ($e->getHeaders()) {
                        $headers = array_merge($headers, $e->getHeaders());
                    }
                } elseif ($e instanceof FuenteNoDisponible) {
                    $status = 503;
                    $code = 'source_unavailable';
                    $message = $e->getMessage();
                } elseif (config('app.debug')) {
                    $message = $e->getMessage();
                }

                $payload = [
                    'error' => array_merge([
                        'status' => $status,
                        'code' => $code,
                        'message' => $message,
                    ], $extra),
                ];

                return response()->json($payload, $status, $headers);
            }

            return null;
        });
    })->create();
