<?php

declare(strict_types=1);

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'api/v1',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Confianza en el proxy inverso Nginx local y red interna mesh
        $middleware->trustProxies(at: ['172.30.0.1', '127.0.0.1']);

        // Eliminar cookies y sesiones del grupo público web para cumplir la política de cero rastreo
        $middleware->removeFromGroup('web', [
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
        ]);

        // Configurar middleware del grupo api
        $middleware->appendToGroup('api', [
            'throttle:api-publica',
            \App\Http\Middleware\RespuestaApi::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Throwable $e, \Illuminate\Http\Request $request) {
            if ($request->is('api/v1') || $request->is('api/v1/*')) {
                $status = 500;
                $code = 'internal_error';
                $message = 'Error interno del servidor.';
                $headers = [
                    'Content-Type' => 'application/json; charset=utf-8',
                    'Cache-Control' => 'no-store',
                ];
                $extra = [];

                if ($e instanceof \Illuminate\Validation\ValidationException) {
                    $status = 400;
                    $code = 'invalid_parameter';
                    $message = 'Parámetros inválidos en la petición.';
                    $extra['parameters'] = $e->errors();
                } elseif ($e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
                    $status = 404;
                    $code = 'not_found';
                    $message = 'El recurso solicitado no fue encontrado.';
                    $headers['Cache-Control'] = 'public, max-age=60';
                } elseif ($e instanceof \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException) {
                    $status = 405;
                    $code = 'method_not_allowed';
                    $message = 'Método HTTP no permitido. Solo se admite GET.';
                    $headers['Allow'] = 'GET, HEAD';
                } elseif ($e instanceof \Illuminate\Http\Exceptions\ThrottleRequestsException) {
                    $status = 429;
                    $code = 'rate_limited';
                    $message = 'Demasiadas peticiones: espera un minuto antes de reintentar.';
                    if ($e->getHeaders()) {
                        $headers = array_merge($headers, $e->getHeaders());
                    }
                } elseif ($e instanceof \App\Excepciones\FuenteNoDisponible) {
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
