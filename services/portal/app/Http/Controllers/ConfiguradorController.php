<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;

/**
 * Controlador para el Configurador Web de Dispositivos Meshtastic.
 *
 * Sirve la herramienta interactiva en /configurador manteniendo la política estricta
 * de cero cookies del portal. Se comunica con el contenedor integrations/meshconfig
 * (http://meshconfig:8080 en Docker o 127.0.0.1:8420 en host) o resuelve los recursos
 * estáticos empaquetados en resources/configurador/.
 */
class ConfiguradorController extends Controller
{
    /**
     * Resuelve los endpoints candidatos del servicio interno meshconfig.
     *
     * @return list<string>
     */
    private function getBackendUrls(): array
    {
        $urls = [];
        $envUrl = env('MESHCONFIG_INTERNAL_URL');
        if (is_string($envUrl) && $envUrl !== '') {
            $urls[] = $envUrl;
        }

        $urls[] = 'http://meshconfig:8080';
        $urls[] = 'http://127.0.0.1:8420';

        return array_values(array_unique($urls));
    }

    /**
     * Muestra la interfaz principal del configurador de dispositivos.
     */
    public function index(): Response
    {
        foreach ($this->getBackendUrls() as $backendUrl) {
            try {
                $response = Http::timeout(2)->get($backendUrl.'/');
                if ($response->successful()) {
                    return response($this->prepareHtml($response->body()), 200, [
                        'Content-Type' => 'text/html; charset=utf-8',
                        'Cache-Control' => 'no-cache, private',
                    ]);
                }
            } catch (\Throwable) {
                // Siguiente endpoint
            }
        }

        $candidatePaths = [
            resource_path('configurador/custom/index.html'),
            base_path('../../integrations/meshconfig/custom/index.html'),
        ];

        foreach ($candidatePaths as $path) {
            if (file_exists($path)) {
                return response($this->prepareHtml((string) file_get_contents($path)), 200, [
                    'Content-Type' => 'text/html; charset=utf-8',
                    'Cache-Control' => 'no-cache, private',
                ]);
            }
        }

        return response('El servicio de configuración no se encuentra disponible temporalmente.', 503);
    }

    /**
     * Prepara el contenido HTML para servirse bajo el prefijo /configurador.
     * Asegura la etiqueta <base href="/configurador/"> y adapta rutas relativas para estilos y scripts.
     */
    private function prepareHtml(string $html): string
    {
        // 1. Inyectar <base href="/configurador/"> tras <head> si no existe
        if (! str_contains($html, '<base ')) {
            $html = (string) preg_replace('/<head(\s[^>]*)?>/i', "$0\n    <base href=\"/configurador/\">", $html, 1);
        }

        // 2. Normalizar enlaces a recursos estáticos locales
        return str_replace(
            ['href="./styles.css"', 'src="./configurador.js"'],
            ['href="/configurador/styles.css"', 'src="/configurador/configurador.js"'],
            $html
        );
    }

    /**
     * Entrega los recursos estáticos del configurador (estilos, módulos JS y presets YAML).
     */
    public function asset(string $file): Response
    {
        $cleanFile = ltrim($file, '/');

        foreach ($this->getBackendUrls() as $backendUrl) {
            try {
                $response = Http::timeout(2)->get($backendUrl.'/'.$cleanFile);
                if ($response->successful()) {
                    $ext = strtolower(pathinfo($cleanFile, PATHINFO_EXTENSION));
                    $contentType = match ($ext) {
                        'css' => 'text/css; charset=utf-8',
                        'js' => 'application/javascript; charset=utf-8',
                        'yaml', 'yml' => 'text/yaml; charset=utf-8',
                        'json' => 'application/json; charset=utf-8',
                        default => $response->header('Content-Type') ?? 'text/plain',
                    };

                    return response($response->body(), 200, [
                        'Content-Type' => $contentType,
                    ]);
                }
            } catch (\Throwable) {
                // Siguiente endpoint
            }
        }

        $candidatePaths = [
            resource_path('configurador/custom/'.$cleanFile),
            resource_path('configurador/'.$cleanFile),
            base_path('../../integrations/meshconfig/custom/'.$cleanFile),
            base_path('../../integrations/meshconfig/'.$cleanFile),
        ];

        foreach ($candidatePaths as $realFile) {
            if (file_exists($realFile)) {
                $ext = strtolower(pathinfo($realFile, PATHINFO_EXTENSION));
                $contentType = match ($ext) {
                    'css' => 'text/css; charset=utf-8',
                    'js' => 'application/javascript; charset=utf-8',
                    'yaml', 'yml' => 'text/yaml; charset=utf-8',
                    'json' => 'application/json; charset=utf-8',
                    default => 'text/plain; charset=utf-8',
                };

                return response((string) file_get_contents($realFile), 200, [
                    'Content-Type' => $contentType,
                ]);
            }
        }

        return response('Recurso no encontrado', 404);
    }
}
