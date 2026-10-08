<?php

declare(strict_types=1);

use App\Http\Controllers\AlertasController;
use App\Http\Controllers\ConfiguradorController;
use App\Http\Controllers\DiagnosticoController;
use App\Http\Controllers\PaginaController;
use App\Http\Controllers\PortadaController;
use App\Http\Controllers\RankingsController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\SuggestionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Rutas públicas del portal Andalucía Mesh.
| Estas rutas se ejecutan SIN cookies de sesión ni tokens CSRF,
| cumpliendo con la directiva estricta de cero cookies para visitantes.
|
*/

// Portada principal
Route::get('/', PortadaController::class)->name('inicio');

// Páginas institucionales basadas en Markdown
Route::get('/proyecto', [PaginaController::class, 'proyecto'])->name('pagina.proyecto');
Route::get('/quien-lo-impulsa', [PaginaController::class, 'quienLoImpulsa'])->name('pagina.quien-lo-impulsa');
Route::get('/como-se-gestiona', [PaginaController::class, 'comoSeGestiona'])->name('pagina.como-se-gestiona');
Route::get('/configura-tu-nodo', [PaginaController::class, 'configuraTuNodo'])->name('pagina.configura-tu-nodo');
Route::get('/conecta-tu-gateway', [PaginaController::class, 'conectaTuGateway'])->name('pagina.conecta-tu-gateway');
Route::get('/bots', [PaginaController::class, 'bots'])->name('pagina.bots');
Route::get('/firmware', [PaginaController::class, 'firmware'])->name('pagina.firmware');
Route::get('/api', [PaginaController::class, 'apiDocs'])->name('pagina.api');

// Páginas dinámicas y herramientas
Route::get('/rankings', [RankingsController::class, 'index'])->name('rankings');
Route::get('/alertas', [AlertasController::class, 'index'])->name('alertas');
Route::get('/alertas/{id}', [AlertasController::class, 'show'])->name('alertas.show');
Route::get('/revisa-tu-nodo', [DiagnosticoController::class, 'index'])->name('revisa-nodo');
Route::get('/revisa-tu-nodo/{id}', [DiagnosticoController::class, 'show'])->name('revisa-nodo.show');
Route::get('/configurador', [ConfiguradorController::class, 'index'])->name('configurador');
Route::get('/configurador/{file}', [ConfiguradorController::class, 'asset'])->where('file', '.*')->name('configurador.asset');
Route::get('/sugerencias', [SuggestionController::class, 'create'])->name('sugerencias.create');
Route::post('/sugerencias', [SuggestionController::class, 'store'])->name('sugerencias.store');

// Textos legales
Route::prefix('legal')->group(function () {
    Route::get('/aviso-legal', [PaginaController::class, 'avisoLegal'])->name('legal.aviso-legal');
    Route::get('/privacidad', [PaginaController::class, 'privacidad'])->name('legal.privacidad');
    Route::get('/cookies', [PaginaController::class, 'cookies'])->name('legal.cookies');
});

// Indexación y robots de rastreo
Route::get('/sitemap.xml', [SitemapController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');
Route::get('/robots.xml', [SitemapController::class, 'robotsXml'])->name('robots.xml');

// Endpoints de salud para Docker healthcheck y red mesh
Route::get('/health', fn () => response()->json(['ok' => true, 'service' => 'portal', 'timestamp' => now()->toIso8601String()]))->name('health');
Route::get('/healthcheck', fn () => response()->json(['ok' => true, 'service' => 'portal', 'timestamp' => now()->toIso8601String()]))->name('healthcheck');
