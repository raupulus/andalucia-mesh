<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Api\V1\CatalogoEndpoints;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class ApiIndexController extends Controller
{
    /**
     * Muestra el índice y documentación de endpoints de la API v1.
     */
    public function __invoke(): JsonResponse
    {
        $endpoints = CatalogoEndpoints::listar();

        return response()->json([
            'api_version' => 'v1',
            'project' => config('proyecto.nombre'),
            'domain' => config('proyecto.dominio'),
            'contact' => config('proyecto.contacto'),
            'documentation' => 'https://' . config('proyecto.dominio') . '/api',
            'generated_at' => gmdate('Y-m-d\TH:i:s\Z'),
            'stale' => false,
            'endpoints_count' => count($endpoints),
            'endpoints' => $endpoints,
        ], 200, [
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
