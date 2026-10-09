<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\AlertsApiController;
use App\Http\Controllers\Api\V1\ApiIndexController;
use App\Http\Controllers\Api\V1\NodesApiController;
use App\Http\Controllers\Api\V1\StatsApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes (/api/v1)
|--------------------------------------------------------------------------
|
| Rutas públicas de la API REST v1 de Andalucía Mesh.
| Solo métodos de lectura (GET/HEAD), sin cookies ni sesiones,
| con rate limiting y cabeceras estrictas de caché y ETag.
|
*/

// Índice de la API
Route::get('/', ApiIndexController::class);

// Estadísticas generales y métricas
Route::prefix('stats')->group(function () {
    Route::get('/summary', [StatsApiController::class, 'summary']);
    Route::get('/provinces', [StatsApiController::class, 'provinces']);
    Route::get('/rankings', [StatsApiController::class, 'rankingsList']);
    Route::get('/rankings/{id}', [StatsApiController::class, 'rankingDetail']);
    Route::get('/traffic-mix', [StatsApiController::class, 'trafficMix']);
});

// Routers de infraestructura
Route::get('/routers', [StatsApiController::class, 'routers']);

// Nodos, búsqueda y diagnóstico
Route::get('/nodes', [NodesApiController::class, 'search']);
Route::get('/nodes/at-risk', [NodesApiController::class, 'atRisk']);
Route::get('/nodes/{id}/diagnosis', [NodesApiController::class, 'diagnosis'])->where('id', '!?[0-9A-Fa-f]{8}');

// Detector de alertas (catálogo antes de {id})
Route::get('/alerts/catalog', [AlertsApiController::class, 'catalog']);
Route::get('/alerts', [AlertsApiController::class, 'index']);
Route::get('/alerts/{id}', [AlertsApiController::class, 'show'])->where('id', '[0-9A-HJKMNP-TV-Za-hjkmnp-tv-z]{26}');

// Endpoints de datos del Mapa
Route::prefix('mapa')->group(function () {
    Route::get('/nodes', [\App\Http\Controllers\MapaController::class, 'nodes']);
    Route::get('/stats', [\App\Http\Controllers\MapaController::class, 'stats']);
    Route::get('/unoptimized', [\App\Http\Controllers\MapaController::class, 'unoptimized']);
    Route::get('/node/{id}', [\App\Http\Controllers\MapaController::class, 'nodeDetail']);
});

