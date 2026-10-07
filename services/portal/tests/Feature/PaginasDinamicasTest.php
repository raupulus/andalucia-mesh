<?php

declare(strict_types=1);

namespace Tests\Feature;

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
}
