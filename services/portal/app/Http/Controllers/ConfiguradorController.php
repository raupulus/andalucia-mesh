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
        return response(view('configurador')->render(), 200, [
            'Content-Type' => 'text/html; charset=utf-8',
            'Cache-Control' => 'no-cache, private',
        ]);
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
