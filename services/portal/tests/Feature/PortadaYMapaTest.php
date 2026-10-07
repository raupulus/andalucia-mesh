<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class PortadaYMapaTest extends TestCase
{
    /**
     * Comprueba que la portada principal devuelve código 200 y cero cookies.
     */
    public function test_portada_devuelve_200_y_cero_cookies(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertHeaderMissing('Set-Cookie');
    }

    /**
     * Comprueba que las 3 tarjetas de servicio se muestran en el orden estricto de especificación.
     */
    public function test_tres_tarjetas_en_orden_estricto(): void
    {
        $response = $this->get('/');

        $contenido = $response->getContent();
        $this->assertNotFalse($contenido);

        $posMeshView = strpos($contenido, 'MeshView');
        $posPotatoMesh = strpos($contenido, 'PotatoMesh');
        $posRankings = strpos($contenido, 'Rankings y Actividad');

        $this->assertNotFalse($posMeshView, 'Tarjeta MeshView debe existir');
        $this->assertNotFalse($posPotatoMesh, 'Tarjeta PotatoMesh debe existir');
        $this->assertNotFalse($posRankings, 'Tarjeta Rankings debe existir');

        $this->assertTrue($posMeshView < $posPotatoMesh, 'MeshView debe preceder a PotatoMesh');
        $this->assertTrue($posPotatoMesh < $posRankings, 'PotatoMesh debe preceder a Rankings');
    }

    /**
     * Comprueba que el mapa SVG vectorial contiene las 8 provincias y la burbuja destacada de Cádiz.
     */
    public function test_mapa_svg_contiene_todas_las_provincias_y_cadiz_destacado(): void
    {
        $response = $this->get('/');

        $response->assertSee('class="mapa-andalucia-svg"', false);

        $provincias = ['ES-AL', 'ES-CA', 'ES-CO', 'ES-GR', 'ES-H', 'ES-J', 'ES-MA', 'ES-SE'];
        foreach ($provincias as $p) {
            $response->assertSee("id=\"prov-{$p}\"", false);
        }

        // Cádiz destacado
        $response->assertSee('burbuja-cadiz', false);
    }

    /**
     * Comprueba que la tabla accesible y el selector de ventana existen.
     */
    public function test_tabla_accesible_y_selector_ventana_presentes(): void
    {
        $response = $this->get('/');

        $response->assertSee('Tabla de nodos y carga por provincia', false);
        $response->assertSee('data-ventana="7d"', false);
        $response->assertSee('data-ventana="24h"', false);
        $response->assertSee('NO es un servicio de emergencias', false);
        $response->assertSee(config('autoria.nick'));
    }

    /**
     * Comprueba el soporte de la query string de ventana temporal (?ventana=24h).
     */
    public function test_ventana_24h_modifica_label_y_selector(): void
    {
        $response = $this->get('/?ventana=24h');

        $response->assertStatus(200);
        $response->assertSee('Últimas 24 horas', false);
    }
}
