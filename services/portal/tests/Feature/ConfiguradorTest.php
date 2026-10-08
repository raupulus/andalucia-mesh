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
        $response->assertSee('chkIberia');
        $response->assertSee('chkAndalucia');
    }

    public function test_configurador_usa_layout_del_portal_y_modo_oscuro_por_defecto(): void
    {
        $response = $this->get('/configurador');

        $response->assertStatus(200);
        $response->assertSee('data-theme="dark"', false);
        $response->assertSee('class="cabecera"', false);
        $response->assertSee('<footer', false);
        $response->assertSee('btn-tema');
        $response->assertSee('statusPill');
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
        $response->assertSee('window.process');
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

    public function test_configurador_html_incluye_base_href_y_rutas_absolutas(): void
    {
        $response = $this->get('/configurador');

        $response->assertStatus(200);
        $response->assertSee('<base href="/configurador/">', false);
        $response->assertSee('href="/configurador/styles.css"', false);
        $response->assertSee('src="/configurador/configurador.js"', false);
        $response->assertSee('window.process = window.process', false);
    }

    public function test_redireccion_defensiva_para_recursos_en_raiz(): void
    {
        $css = $this->get('/styles.css');
        $css->assertStatus(301);
        $css->assertRedirect('/configurador/styles.css');

        $js = $this->get('/configurador.js');
        $js->assertStatus(301);
        $js->assertRedirect('/configurador/configurador.js');
    }

    public function test_configurador_muestra_aviso_experimental_y_enlace_a_sugerencias(): void
    {
        $response = $this->get('/configurador');

        $response->assertStatus(200);
        $response->assertSee('Experimental');
        $response->assertSee('bajo su propio riesgo');
        $response->assertSee('/sugerencias');
    }

    public function test_configurador_aviso_experimental_multidioma(): void
    {
        $en = $this->get('/configurador?lang=en');
        $en->assertStatus(200);
        $en->assertSee('Experimental');
        $en->assertSee('own risk');
        $en->assertSee('/sugerencias?lang=en');

        $pt = $this->get('/configurador?lang=pt');
        $pt->assertStatus(200);
        $pt->assertSee('Experimental');
        $pt->assertSee('conta e risco');
        $pt->assertSee('/sugerencias?lang=pt');
    }

    public function test_configurador_contiene_checkbox_colaboracion_mqtt(): void
    {
        $response = $this->get('/configurador');

        $response->assertStatus(200);
        $response->assertSee('chkMqtt');
        $response->assertSee('¿Quieres colaborar subiendo a este MQTT?');
        $response->assertSee('mqtt.desdechipiona.es');

        $en = $this->get('/configurador?lang=en');
        $en->assertStatus(200);
        $en->assertSee('Do you want to collaborate by uploading to this MQTT?');

        $pt = $this->get('/configurador?lang=pt');
        $pt->assertStatus(200);
        $pt->assertSee('Queres colaborar enviando para este MQTT?');
    }

    public function test_configurador_contiene_opciones_ampliadas_telemetria_y_posicion(): void
    {
        $response = $this->get('/configurador');

        $response->assertStatus(200);
        $response->assertSee('id="telemetriaSelect"', false);
        $response->assertSee('id="posicionSelect"', false);
        // Opciones ampliadas de tiempo (12h, 24h, 48h, 72h)
        $response->assertSee('value="43200"', false);
        $response->assertSee('value="86400"', false);
        $response->assertSee('value="172800"', false);
        $response->assertSee('value="259200"', false);
        $response->assertSee('Cada 12 horas');
        $response->assertSee('Cada 24 horas (1 día)');
        $response->assertSee('Cada 48 horas (2 días)');
        $response->assertSee('Cada 72 horas (3 días');
    }

    public function test_configurador_modo_avanzado_contiene_panel_conexion_directa(): void
    {
        $response = $this->get('/configurador');

        $response->assertStatus(200);
        $response->assertSee('id="transportSelectWorkbench"', false);
        $response->assertSee('id="btnConnectWorkbench"', false);
        $response->assertSee('id="btnDisconnectWorkbench"', false);
        $response->assertSee('id="btnDownloadLiveHeader"', false);
        $response->assertSee('id="workbenchStatusPill"', false);
        $response->assertSee('Conexión Directa con el Nodo');
    }
}

