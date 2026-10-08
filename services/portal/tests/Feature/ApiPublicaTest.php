<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Data\Fuente;
use App\Excepciones\FuenteNoDisponible;
use Illuminate\Support\Facades\Cache;
use RuntimeException;
use Tests\TestCase;

class ApiPublicaTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Sembrar caché fresca para stats_summary
        $paramsSummary = [];
        $hashSummary = sha1(json_encode($paramsSummary) ?: '');
        Cache::put("api1:stats_summary:{$hashSummary}:fresco", [
            'datos' => [
                'nodes_active_24h' => 42,
                'nodes_active_7d' => 120,
                'routers_active_24h' => 8,
                'gateways_publishing' => 5,
                'packets_last_hour' => 1250,
                'channel_utilization' => [
                    'window' => '12h',
                    'andalucia_avg' => 18.5,
                    'andalucia_max' => 35.0,
                    'provinces' => [],
                ],
                'alerts_open' => null,
                'notes' => ['Prueba'],
            ],
            'generado_en' => '2026-10-07T12:00:00Z',
        ], 60);

        // Sembrar caché fresca para stats_provincias (30m por defecto y 7d)
        foreach (['30m', '7d'] as $v) {
            $paramsProv = ['ventana' => $v];
            ksort($paramsProv);
            $hashProv = sha1(json_encode($paramsProv) ?: '');
            Cache::put("api1:stats_provincias:{$hashProv}:fresco", [
                'datos' => [
                    'window' => $v,
                    'load_window' => $v,
                    'total_andalucia' => 120,
                    'andalucia_avg' => 18.5,
                    'outside_andalucia' => 3,
                    'provinces' => [],
                    'notes' => ['Prueba'],
                ],
                'generado_en' => '2026-10-07T12:00:00Z',
            ], 60);
        }
    }

    /**
     * Comprueba el endpoint índice de la API v1.
     */
    public function test_indice_api_v1_devuelve_catalogo_y_cabeceras_correctas(): void
    {
        $response = $this->get('/api/v1');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json; charset=utf-8');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Access-Control-Allow-Origin', '*');
        $response->assertHeaderMissing('Set-Cookie');

        $response->assertJsonStructure([
            'api_version',
            'project',
            'domain',
            'contact',
            'documentation',
            'generated_at',
            'stale',
            'endpoints_count',
            'endpoints',
        ]);
    }

    /**
     * Comprueba el soporte de ETag y 304 Not Modified.
     */
    public function test_soporte_etag_y_respuesta_304(): void
    {
        $response1 = $this->get('/api/v1');
        $response1->assertStatus(200);

        $etag = $response1->headers->get('ETag');
        $this->assertNotEmpty($etag, 'La cabecera ETag debe estar presente');

        $response2 = $this->withHeaders([
            'If-None-Match' => $etag,
        ])->get('/api/v1');

        $response2->assertStatus(304);
        $this->assertEmpty($response2->getContent());
    }

    /**
     * Comprueba el endpoint /stats/summary.
     */
    public function test_stats_summary_devuelve_estructura_canonica(): void
    {
        $response = $this->get('/api/v1/stats/summary');

        $response->assertStatus(200);
        $response->assertHeaderMissing('Set-Cookie');
        $response->assertJsonStructure([
            'generated_at',
            'stale',
            'nodes_active_24h',
            'nodes_active_7d',
            'routers_active_24h',
            'gateways_publishing',
            'packets_last_hour',
            'channel_utilization' => [
                'window',
                'andalucia_avg',
                'andalucia_max',
                'provinces',
            ],
        ]);
    }

    /**
     * Comprueba /stats/provinces con parámetro válido e inválido.
     */
    public function test_stats_provinces_valida_parametro_window(): void
    {
        $responseDefault = $this->get('/api/v1/stats/provinces');
        $responseDefault->assertStatus(200);
        $responseDefault->assertJsonPath('window', '30m');

        $responseOk = $this->get('/api/v1/stats/provinces?window=7d');
        $responseOk->assertStatus(200);
        $responseOk->assertJsonStructure([
            'generated_at',
            'stale',
            'total_andalucia',
            'provinces',
        ]);

        $responseErr = $this->get('/api/v1/stats/provinces?window=invalido');
        $responseErr->assertStatus(400);
        $responseErr->assertJson([
            'error' => [
                'status' => 400,
                'code' => 'invalid_parameter',
            ],
        ]);
    }

    /**
     * Comprueba /stats/rankings y catálogo.
     */
    public function test_stats_rankings_devuelve_los_11_rankings(): void
    {
        $response = $this->get('/api/v1/stats/rankings');

        $response->assertStatus(200);
        $response->assertJsonPath('count', 11);
    }

    /**
     * Comprueba el catálogo de reglas de alertas.
     */
    public function test_alerts_catalog_devuelve_reglas_oficiales(): void
    {
        $response = $this->get('/api/v1/alerts/catalog');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'generated_at',
            'stale',
            'count',
            'items',
        ]);
    }

    /**
     * Comprueba que una ruta inexistente bajo /api/v1 devuelve 404 en formato JSON canónico.
     */
    public function test_ruta_inexistente_api_devuelve_404_json(): void
    {
        $response = $this->get('/api/v1/ruta-desconocida-xyz');

        $response->assertStatus(404);
        $response->assertHeader('Content-Type', 'application/json; charset=utf-8');
        $response->assertJson([
            'error' => [
                'status' => 404,
                'code' => 'not_found',
            ],
        ]);
    }

    /**
     * Comprueba la resiliencia y entrega de datos stale: true ante fallo de base de datos.
     */
    public function test_fuente_entrega_datos_stale_ante_fallo(): void
    {
        $consulta = 'test_resiliencia';
        $params = ['id' => 1];
        $hash = sha1(json_encode($params) ?: '');

        // 1. Inyectar un respaldo previo en caché
        $keyUltimo = "api1:{$consulta}:{$hash}:ultimo";
        Cache::put($keyUltimo, [
            'datos' => ['valor' => 'dato_respaldado'],
            'generado_en' => '2026-10-07T12:00:00Z',
        ], 86400);

        // 2. Ejecutar con una Closure que lanza excepción (simulando corte de base de datos)
        $resultado = Fuente::recordar($consulta, $params, 60, function () {
            throw new RuntimeException('Error de conexión a PostgreSQL');
        });

        $this->assertTrue($resultado->stale);
        $this->assertSame('STALE', $resultado->xCache);
        $this->assertSame('dato_respaldado', $resultado->datos['valor']);
    }

    /**
     * Comprueba que si no hay respaldo previo, se lanza FuenteNoDisponible (HTTP 503).
     */
    public function test_fuente_lanza_excepcion_sin_respaldo(): void
    {
        $this->expectException(FuenteNoDisponible::class);

        Fuente::recordar('test_sin_respaldo', ['rand' => uniqid()], 60, function () {
            throw new RuntimeException('Fallo sin respaldo');
        });
    }
}
