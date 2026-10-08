<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware que gestiona la selección e idioma en el panel privado de administración (Filament).
 *
 * Permite a los operadores alternar entre ES, EN y PT, persistiendo su preferencia en la sesión técnica.
 */
class FilamentLocaleMiddleware
{
    /**
     * Idiomas admitidos en el panel de control.
     *
     * @var array<string>
     */
    public const SUPPORTED_LOCALES = ['es', 'en', 'pt'];

    /**
     * Idioma por defecto.
     */
    public const DEFAULT_LOCALE = 'es';

    /**
     * Clave utilizada en la sesión para recordar el idioma del operador.
     */
    public const SESSION_KEY = 'filament_locale';

    /**
     * Procesa la petición entrante y configura el idioma para Filament.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->determineLocale($request);

        app()->setLocale($locale);
        Carbon::setLocale($locale);

        return $next($request);
    }

    /**
     * Determina el idioma para el panel comprobando parámetros de consulta, sesión y navegador.
     */
    public function determineLocale(Request $request): string
    {
        // 1. Cambio manual explícito por parámetro en la URL
        $solicitado = $request->query('lang') ?? $request->query('locale');
        if (is_string($solicitado)) {
            $solicitado = strtolower(trim($solicitado));
            if (in_array($solicitado, self::SUPPORTED_LOCALES, true)) {
                if ($request->hasSession()) {
                    $request->session()->put(self::SESSION_KEY, $solicitado);
                }

                return $solicitado;
            }
        }

        // 2. Idioma recordado previamente en la sesión del operador
        if ($request->hasSession() && $request->session()->has(self::SESSION_KEY)) {
            $guardado = (string) $request->session()->get(self::SESSION_KEY);
            if (in_array($guardado, self::SUPPORTED_LOCALES, true)) {
                return $guardado;
            }
        }

        // 3. Detección automática por preferencias del navegador si la cabecera está presente
        if ($request->headers->has('Accept-Language') && ! empty($request->header('Accept-Language'))) {
            $navegadorLangs = $request->getLanguages();
            foreach ($navegadorLangs as $lang) {
                $codigo = strtolower(substr(trim($lang), 0, 2));
                if (in_array($codigo, self::SUPPORTED_LOCALES, true)) {
                    return $codigo;
                }
            }
        }

        // 4. Fallback en español
        return self::DEFAULT_LOCALE;
    }
}
