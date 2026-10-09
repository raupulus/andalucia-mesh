<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Data\Ingest\MapaData;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class MapaInteractivoTest extends TestCase
{
    /**
     * Verifica que la ruta pública /mapa responda 200 OK y cumpla la directiva cero cookies (RN-06).
     */
    public function test_ruta_mapa_devuelve_200_y_cero_cookies(): void
    {
        $response = $this->get('/mapa');

        $response->assertStatus(200);
        $response->assertCookieMissing('laravel_session');
        $response->assertSee('id="mapa-lienzo"', false);
        $response->assertSee('id="mapa-drawer"', false);
        $response->assertSee('id="mapa-modal-diag"', false);
        $response->assertSee('id="mapa-modal-catalog"', false);
        $response->assertSee('id="mapa-search-input"', false);
        $response->assertSee('btn-no-optimizados');
    }

    /**
     * Verifica que "Mapa" esté presente en la navegación principal del portal.
     */
    public function test_mapa_aparece_en_el_navbar(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('href="/mapa"', false);
        $response->assertSee('Mapa');
    }

    /**
     * Verifica que el endpoint API de estadísticas del mapa responda JSON con los campos requeridos.
     */
    public function test_api_mapa_stats_devuelve_estructura_valida(): void
    {
        $response = $this->getJson('/api/v1/mapa/stats');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'total_nodes',
            'active_1h',
            'active_24h',
            'gateways_count',
            'routers_count',
            'unoptimized_count',
            'unoptimized_breakdown' => [
                'hop_limit_alto',
                'client_base_fw',
                'nodeinfo_frecuente',
                'telemetria_frecuente',
                'alerta_activa',
            ],
            'updated_at',
            'updated_at_iso',
        ]);
    }

    /**
     * Verifica que el endpoint API de nodos responda con código 200 y cabeceras de caché.
     */
    public function test_api_mapa_nodes_responde_correctamente(): void
    {
        $response = $this->getJson('/api/v1/mapa/nodes');

        $response->assertStatus(200);
        $response->assertHeader('Cache-Control');
    }

    /**
     * Verifica que el endpoint de catálogo de nodos no optimizados responda JSON.
     */
    public function test_api_mapa_unoptimized_responde_correctamente(): void
    {
        $response = $this->getJson('/api/v1/mapa/unoptimized');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'count',
            'items',
        ]);
    }

    /**
     * Verifica la ejecución del comando Artisan mapa:cache y la creación de archivos estáticos.
     */
    public function test_comando_artisan_mapa_cache_ejecuta_y_genera_archivos(): void
    {
        $this->artisan('mapa:cache')
            ->assertExitCode(0);

        $this->assertTrue(File::exists(public_path(MapaData::CACHE_DIR.'/nodes.json')));
        $this->assertTrue(File::exists(public_path(MapaData::CACHE_DIR.'/stats.json')));
        $this->assertTrue(File::exists(public_path(MapaData::CACHE_DIR.'/unoptimized.json')));
    }

    /**
     * Verifica el soporte multidioma en la página /mapa.
     */
    public function test_mapa_i18n_soporta_ingles_y_portugues(): void
    {
        $respEn = $this->get('/mapa?lang=en');
        $respEn->assertStatus(200);
        $respEn->assertSee('Map ·');

        $respPt = $this->get('/mapa?lang=pt');
        $respPt->assertStatus(200);
        $respPt->assertSee('Mapa ·');
    }
}
