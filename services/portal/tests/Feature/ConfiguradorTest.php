<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class ConfiguradorTest extends TestCase
{
    public function test_configurador_responde_200_y_cero_cookies(): void
    {
        $response = $this->get('/configurador');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/html; charset=utf-8');
        $this->assertEmpty($response->headers->getCookies(), 'Las rutas públicas del configurador no deben emitir cookies.');
    }

    public function test_configurador_contiene_elementos_clave_sfnarrow(): void
    {
        $response = $this->get('/configurador');

        $response->assertStatus(200);
        $response->assertSee('Configurador de Dispositivos Meshtastic');
        $response->assertSee('SFNarrow');
        $response->assertSee('CLIENT_MUTE');
        $response->assertSee('CLIENT');
        $response->assertSee('pdxlocations/meshconfig');
        $response->assertSee('E22P-868M30S');
    }

    public function test_configurador_entrega_estilos_css(): void
    {
        $response = $this->get('/configurador/styles.css');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/css; charset=utf-8');
        $response->assertSee('--color-acento');
    }

    public function test_configurador_entrega_modulo_javascript(): void
    {
        $response = $this->get('/configurador/configurador.js');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/javascript; charset=utf-8');
        $response->assertSee('construirYamlDeseado');
        $response->assertSee('actualizarConfiguracion');
    }

    public function test_configurador_entrega_preset_client_mute(): void
    {
        $response = $this->get('/configurador/configs/andalucia-sfnarrow-client-mute.yaml');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/yaml; charset=utf-8');
        $response->assertSee('role: CLIENT_MUTE');
        $response->assertSee('hopLimit: 4');
        $response->assertSee('positionBroadcastSecs: 21600');
        $response->assertSee('nodeInfoBroadcastSecs: 259200');
    }

    public function test_configurador_entrega_preset_client(): void
    {
        $response = $this->get('/configurador/configs/andalucia-sfnarrow-client.yaml');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/yaml; charset=utf-8');
        $response->assertSee('role: CLIENT');
        $response->assertSee('hopLimit: 3');
        $response->assertSee('positionBroadcastSecs: 259200');
    }

    public function test_configurador_recurso_inexistente_devuelve_404(): void
    {
        $response = $this->get('/configurador/archivo-inexistente.xyz');

        $response->assertStatus(404);
    }
}
