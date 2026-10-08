<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware que gestiona la detección y aplicación del idioma en el portal público.
 *
 * Cumple con RN-48 (ES/EN/PT, fallback ES) y con RN-06 (cero cookies en front público).
 */
class PortalLocaleMiddleware
{
    /**
     * Idiomas soportados oficialmente por el portal.
     *
     * @var array<string>
     */
    public const SUPPORTED_LOCALES = ['es', 'en', 'pt'];

    /**
     * Idioma por defecto y fallback de seguridad.
     */
    public const DEFAULT_LOCALE = 'es';

    /**
     * Procesa la petición entrante y configura el locale activo en el contenedor de Laravel.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->determineLocale($request);

        app()->setLocale($locale);

        return $next($request);
    }

    /**
     * Determina el idioma a aplicar en base a parámetros de consulta y cabeceras del navegador.
     */
    public function determineLocale(Request $request): string
    {
        // 1. Precedencia explícita por parámetro de consulta (?lang=xx o ?locale=xx)
        $solicitado = $request->query('lang') ?? $request->query('locale');
        if (is_string($solicitado)) {
            $solicitado = strtolower(trim($solicitado));
            if (in_array($solicitado, self::SUPPORTED_LOCALES, true)) {
                return $solicitado;
            }
        }

        // 2. Detección automática por la cabecera HTTP Accept-Language del navegador si está presente
        if ($request->headers->has('Accept-Language') && !empty($request->header('Accept-Language'))) {
            $navegadorLangs = $request->getLanguages();
            foreach ($navegadorLangs as $lang) {
                $codigo = strtolower(substr(trim($lang), 0, 2));
                if (in_array($codigo, self::SUPPORTED_LOCALES, true)) {
                    return $codigo;
                }
            }
        }

        // 3. Fallback obligatorio en español
        return self::DEFAULT_LOCALE;
    }
}
