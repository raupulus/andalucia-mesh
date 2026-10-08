<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Controllers\AlertasController;
use Tests\TestCase;

class PaginasDinamicasTest extends TestCase
{
    /**
     * Comprueba que la página de rankings devuelve 200 OK y cero cookies.
     */
    public function test_pagina_rankings_devuelve_200_y_cero_cookies(): void
    {
        $response = $this->get('/rankings');

        $response->assertStatus(200);
        $response->assertHeaderMissing('Set-Cookie');
        $response->assertSee('Rankings y Actividad de la Red', false);
        $response->assertSee('Distribución de Tráfico', false);
        $response->assertSee('Top Nodos por Uso del Espectro', false);
        $response->assertSee('Catálogo de Rankings de la Red', false);
    }

    /**
     * Comprueba los diferentes periodos de la página de rankings.
     */
    public function test_pagina_rankings_admite_periodos(): void
    {
        $response = $this->get('/rankings?period=week&which=current');

        $response->assertStatus(200);
        $response->assertHeaderMissing('Set-Cookie');
    }

    /**
     * Comprueba que la página de alertas devuelve 200 OK y cero cookies.
     */
    public function test_pagina_alertas_devuelve_200_y_cero_cookies(): void
    {
        $response = $this->get('/alertas');

        $response->assertStatus(200);
        $response->assertHeaderMissing('Set-Cookie');
        $response->assertSee('Alertas de la Malla', false);
        $response->assertSee('Catálogo de Anomalías Monitorizadas', false);
    }

    /**
     * Comprueba la landing page del buscador de "Revisa tu nodo".
     */
    public function test_revisa_tu_nodo_buscador_devuelve_200(): void
    {
        $response = $this->get('/revisa-tu-nodo');

        $response->assertStatus(200);
        $response->assertHeaderMissing('Set-Cookie');
        $response->assertSee('Revisa tu nodo', false);
        $response->assertSee('name="buscar"', false);
    }

    /**
     * Comprueba que buscar un ID directo redirige a la URL canónica.
     */
    public function test_revisa_tu_nodo_redirige_si_es_un_id(): void
    {
        $response = $this->get('/revisa-tu-nodo?buscar=!aabb1122');

        $response->assertRedirect('/revisa-tu-nodo/!aabb1122');
    }

    /**
     * Comprueba el informe de un nodo no encontrado.
     */
    public function test_revisa_tu_nodo_muestra_aviso_si_no_existe(): void
    {
        $response = $this->get('/revisa-tu-nodo/!99999999');

        $response->assertStatus(200);
        $response->assertHeaderMissing('Set-Cookie');
        $response->assertSee('Nodo no localizado', false);
        $response->assertSee('!99999999', false);
    }

    /**
     * Comprueba que una alerta inexistente devuelve 404.
     */
    public function test_pagina_alerta_inexistente_devuelve_404(): void
    {
        $response = $this->get('/alertas/01NONEXISTENTID00000000000');

        $response->assertStatus(404);
    }

    /**
     * Comprueba que el normalizador de alertas enriquece con metadatos explicativos y didácticos.
     */
    public function test_normalizador_alertas_enriquece_metadatos_explicativos(): void
    {
        $alertaCruda = [
            'id' => '01M4DHF92D5PDDQ2HMXX9H5X1T',
            'regla' => 'hops-high',
            'riesgo' => 'bajo',
            'tipo' => 'clientes',
            'mensaje' => 'GAT5 usa 6 saltos (recomendado 3, máximo 5)',
            'nodo' => '!50dd2a87',
            'nodo_info' => json_encode(['corto' => 'GAT5', 'largo' => 'CC SERRILLA', 'rol' => 'CLIENT', 'provincia' => 'FUERA']),
            'datos' => json_encode(['hop_start' => 6, 'recomendado' => 3, 'maximo_valido' => 5]),
            'estado' => 'resuelta',
            'abierta_en' => '2026-10-08 12:38:14',
            'resuelta_en' => '2026-10-08 12:43:00',
            'reaperturas' => 1,
        ];

        $normalizada = AlertasController::normalizarAlerta($alertaCruda);

        $this->assertSame('Saltos excesivos (hop_start)', $normalizada['regla_nombre']);
        $this->assertSame('🔀', $normalizada['regla_icono']);
        $this->assertStringContainsString('En una red mallada como Meshtastic', $normalizada['regla_por_que']);
        $this->assertStringContainsString('Hop Limit', $normalizada['regla_como_solucionar']);
        $this->assertSame('GAT5', $normalizada['nodo_nombre_corto']);
        $this->assertSame('CC SERRILLA', $normalizada['nodo_nombre_largo']);
        $this->assertFalse($normalizada['nodo_es_global']);
        $this->assertSame(6, $normalizada['datos']['hop_start']);
    }
}
