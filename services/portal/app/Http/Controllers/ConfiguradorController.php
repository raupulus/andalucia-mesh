<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;

/**
 * Controlador para el Configurador Web de Dispositivos Meshtastic.
 *
 * Sirve la herramienta interactiva en /configurador manteniendo la política estricta
 * de cero cookies del portal. Se comunica con el contenedor integrations/meshconfig
 * (puerto 8420) o resuelve los archivos estáticos montados localmente.
 */
class ConfiguradorController extends Controller
{
    /**
     * Muestra la interfaz principal del configurador de dispositivos.
     */
    public function index(): Response
    {
        $backendUrl = (string) config('portal.urls.meshconfig_internal', 'http://127.0.0.1:8420');

        try {
            $response = Http::timeout(2)->get($backendUrl . '/');
            if ($response->successful()) {
                return response($response->body(), 200, [
                    'Content-Type' => 'text/html; charset=utf-8',
                    'Cache-Control' => 'no-cache, private',
                ]);
            }
        } catch (\Throwable $e) {
            // El backend del contenedor no respondió; intentar fallback de archivo local
        }

        $fallbackPath = base_path('../../integrations/meshconfig/custom/index.html');
        if (file_exists($fallbackPath)) {
            return response((string) file_get_contents($fallbackPath), 200, [
                'Content-Type' => 'text/html; charset=utf-8',
                'Cache-Control' => 'no-cache, private',
            ]);
        }

        return response('El servicio de configuración no se encuentra disponible temporalmente.', 503);
    }

    /**
     * Entrega los recursos estáticos del configurador (estilos, módulos JS y presets YAML).
     */
    public function asset(string $file): Response
    {
        $backendUrl = (string) config('portal.urls.meshconfig_internal', 'http://127.0.0.1:8420');

        try {
            $response = Http::timeout(2)->get($backendUrl . '/' . ltrim($file, '/'));
            if ($response->successful()) {
                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
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
        } catch (\Throwable $e) {
            // Fallback a archivos locales
        }

        $customPath = base_path('../../integrations/meshconfig/custom/' . ltrim($file, '/'));
        $configPath = base_path('../../integrations/meshconfig/' . ltrim($file, '/'));

        $realFile = file_exists($customPath) ? $customPath : (file_exists($configPath) ? $configPath : null);

        if ($realFile && file_exists($realFile)) {
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

        return response('Recurso no encontrado', 404);
    }
}
