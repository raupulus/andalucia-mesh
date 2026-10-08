<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RespuestaApi
{
    /**
     * Aplica las cabeceras estándar de la API pública: ETag, CORS, nosniff y cero cookies.
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        // 1. Eliminar cualquier emisión de cookies
        $response->headers->remove('Set-Cookie');

        // 2. Cabeceras de seguridad y CORS
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Methods', 'GET, HEAD, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Accept, If-None-Match');
        $response->headers->set('Access-Control-Expose-Headers', 'ETag, X-Cache, X-RateLimit-Limit, X-RateLimit-Remaining, Retry-After');

        // Si es una respuesta JSON, gestionar ETag y 304 Not Modified
        if ($response instanceof JsonResponse || str_contains((string) $response->headers->get('Content-Type'), 'application/json')) {
            $response->headers->set('Content-Type', 'application/json; charset=utf-8');

            $content = (string) $response->getContent();
            $etag = 'W/"'.sha1($content).'"';
            $response->headers->set('ETag', $etag);

            $ifNoneMatch = $request->header('If-None-Match');
            if ($ifNoneMatch && trim($ifNoneMatch) === $etag && $request->isMethod('GET')) {
                $response->setStatusCode(304);
                $response->setContent('');
            }
        }

        return $response;
    }
}
