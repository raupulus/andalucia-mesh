<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class RoutersFrontendTest extends TestCase
{
    /**
     * Comprueba que la ruta /routers responde 200 y no emite cookies (RN-06).
     */
    public function test_ruta_routers_responde_200_y_cero_cookies(): void
    {
        $response = $this->get('/routers');

        $response->assertStatus(200);
        $response->assertHeaderMissing('Set-Cookie');
    }

    /**
     * Comprueba que "Routers" aparece en el navbar principal situado antes de "Alertas".
     */
    public function test_routers_aparece_en_navbar_antes_de_alertas(): void
    {
        $response = $this->get('/routers');
        $contenido = $response->getContent();
        $this->assertNotFalse($contenido);

        $posRouters = strpos($contenido, 'href="/routers"');
        $posAlertas = strpos($contenido, 'href="/alertas"');

        $this->assertNotFalse($posRouters, 'El enlace a /routers debe existir en el navbar');
        $this->assertNotFalse($posAlertas, 'El enlace a /alertas debe existir en el navbar');
        $this->assertTrue($posRouters < $posAlertas, 'El enlace a /routers debe ubicarse antes de /alertas en el menú');
    }

    /**
     * Comprueba que el selector de ámbito territorial refleja Andalucía, España o Ambos.
     */
    public function test_selector_de_ambito_filtra_andalucia_espana_y_ambos(): void
    {
        // 1. Ámbito por defecto (Andalucía)
        $resAndalucia = $this->get('/routers');
        $resAndalucia->assertSee('routers-ambito-card-andalucia-active', false);
        $resAndalucia->assertSee('routers-ambito-card-inactive', false);

        // 2. Ámbito España
        $resEspana = $this->get('/routers?ambito=espana');
        $resEspana->assertSee('routers-ambito-card-espana-active', false);

        // 3. Ámbito Ambos (Andalucía + España)
        $resAmbos = $this->get('/routers?ambito=ambos');
        $resAmbos->assertSee('routers-ambito-card-andalucia-active', false);
        $resAmbos->assertSee('routers-ambito-card-espana-active', false);
    }

    /**
     * Comprueba que las 4 tarjetas KPI de resumen se renderizan con sus títulos y valores.
     */
    public function test_tarjetas_kpi_se_renderizan_correctamente(): void
    {
        $response = $this->get('/routers');

        $response->assertSee('routers-kpi-grid', false);
        $response->assertSee('routers-kpi-card', false);
        $response->assertSee('Presión del Aire (ChUtil)', false);
        $response->assertSee('Saturación TX Repetidores', false);
        $response->assertSee('Infraestructura y Energía', false);
        $response->assertSee('Tráfico y Pasarelas', false);
    }

    /**
     * Comprueba que la tabla de repetidores de infraestructura y sus columnas están presentes.
     */
    public function test_tabla_muestra_estructura_y_columnas_correctas(): void
    {
        $response = $this->get('/routers');

        $response->assertSee('routers-dashboard-table', false);
        $response->assertSee('Router / Identidad', false);
        $response->assertSee('Hardware / Zona', false);
        $response->assertSee('Alimentación y Batería', false);
        $response->assertSee('Presión Aire (ChUtil)', false);
        $response->assertSee('Saturación TX', false);
        $response->assertSee('Último Reporte', false);
        $response->assertSee('Acción', false);
    }

    /**
     * Comprueba que la página /routers soporta multidioma (inglés y portugués).
     */
    public function test_i18n_pagina_routers_en_ingles_y_portugues(): void
    {
        // En inglés
        $resEn = $this->get('/routers?lang=en');
        $resEn->assertStatus(200);
        $resEn->assertSee('Territorial Supervision Scope');
        $resEn->assertSee('Air Pressure (ChUtil)');
        $resEn->assertSee('Repeater TX Saturation');
        $resEn->assertSee('Repeaters & Infrastructure Nodes');

        // En portugués
        $resPt = $this->get('/routers?lang=pt');
        $resPt->assertStatus(200);
        $resPt->assertSee('Âmbito Territorial de Supervisão');
        $resPt->assertSee('Pressão do Ar (ChUtil)');
        $resPt->assertSee('Saturação TX Repetidores');
        $resPt->assertSee('Repetidores e Nós de Infraestrutura');
    }
}
