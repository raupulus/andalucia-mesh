<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Pruebas automatizadas para el catálogo de Herramientas y la Calculadora ADC de Batería.
 *
 * Verifica accesibilidad, cero cookies (RN-06), metadatos SEO, presencia en sitemap,
 * soporte multidioma (RN-48) y lógica de cálculo técnico del divisor de tensión.
 */
class HerramientasTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Comprueba que la página de índice /herramientas responde 200, no emite cookies
     * y muestra las tarjetas del Configurador y la Calculadora de Batería.
     */
    public function test_catalogo_herramientas_responde_200_con_cero_cookies_y_tarjetas(): void
    {
        $response = $this->get('/herramientas');

        $response->assertStatus(200);
        $response->assertHeaderMissing('Set-Cookie');

        $contenido = (string) $response->getContent();

        // Título y metadatos
        $response->assertSee('Herramientas para Nodos y Red', false);
        $this->assertStringContainsString('og-herramientas.webp', $contenido);

        // Tarjeta 1: Configurador automático
        $response->assertSee('Configurador Automático', false);
        $response->assertSee('/configurador', false);
        $this->assertStringContainsString('herramientas/configurador.webp', $contenido);

        // Tarjeta 2: Calculadora ADC de batería
        $response->assertSee('Calculadora ADC de Batería', false);
        $response->assertSee('/herramientas/calculadora-bateria-adc', false);
        $this->assertStringContainsString('herramientas/calculadora-bateria.webp', $contenido);

        // Widget para compartir en redes sociales debajo de la cabecera
        $this->assertStringContainsString('grupo-botones-compartir', $contenido);
        $response->assertSee('btn-compartir-whatsapp', false);
        $response->assertSee('btn-compartir-telegram', false);
    }

    /**
     * Comprueba que la calculadora técnica /herramientas/calculadora-bateria-adc responde 200,
     * no emite cookies y contiene las recomendaciones, inputs y comandos oficiales de Meshtastic.
     */
    public function test_calculadora_bateria_adc_muestra_recomendaciones_y_comandos(): void
    {
        $response = $this->get('/herramientas/calculadora-bateria-adc');

        $response->assertStatus(200);
        $response->assertHeaderMissing('Set-Cookie');

        $contenido = (string) $response->getContent();

        // Pareja recomendada 1: 1M + 1M (multiplicador 2.05 empírico)
        $response->assertSee('1 MΩ + 1 MΩ', false);
        $response->assertSee('2.05 (calibrado)', false);

        // Pareja recomendada 2: 1M + 1.5M (multiplicador 1.67)
        $response->assertSee('1 MΩ + 1.5 MΩ', false);
        $response->assertSee('1.67 (ratio)', false);

        // Inputs interactivos
        $response->assertSee('id="input-r1"', false);
        $response->assertSee('id="input-r2"', false);
        $response->assertSee('id="input-vbat"', false);

        // Fórmulas y comandos Meshtastic
        $response->assertSee('(R1 + R2) / R2', false);
        $response->assertSee('power.adc_multiplier_override', false);
        $response->assertSee('meshtastic --set power.adc_multiplier_override', false);

        // Calibración fina con multímetro
        $response->assertSee('id="calib-multimetro"', false);
        $response->assertSee('id="calib-app"', false);

        // Widget para compartir en redes sociales debajo de la cabecera
        $this->assertStringContainsString('grupo-botones-compartir', $contenido);
        $response->assertSee('btn-compartir-whatsapp', false);
    }

    /**
     * Comprueba que la cabecera incluye el enlace a Herramientas en el menú Extras.
     */
    public function test_cabecera_extras_incluye_enlace_a_herramientas(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('/herramientas', false);
        $response->assertSee('Herramientas', false);
    }

    /**
     * Comprueba que ambas rutas están incluidas en el sitemap dinámico con imágenes y alternativas hreflang.
     */
    public function test_sitemap_incluye_rutas_de_herramientas(): void
    {
        $response = $this->get('/sitemap.xml');

        $response->assertStatus(200);
        $contenido = (string) $response->getContent();

        $this->assertStringContainsString('/herramientas</loc>', $contenido);
        $this->assertStringContainsString('/herramientas/calculadora-bateria-adc</loc>', $contenido);
        $this->assertStringContainsString('og-herramientas.webp', $contenido);
        $this->assertStringContainsString('og-calculadora-bateria.webp', $contenido);
    }

    /**
     * Comprueba la internacionalización de las herramientas en inglés y portugués,
     * y garantiza que no haya texto CSS '@media' fugado en el HTML.
     */
    public function test_herramientas_traducciones_en_y_pt(): void
    {
        // En español (sin fuga de @media en HTML)
        $responseEs = $this->get('/herramientas/calculadora-bateria-adc');
        $responseEs->assertStatus(200);
        $this->assertStringNotContainsString('@media (max-width: 768px)', (string) $responseEs->getContent());
        $responseEs->assertSee('💡 Recomendaciones de Hardware para Meshtastic DIY', false);
        $responseEs->assertSee('Margen de seguridad', false);

        // En inglés
        $responseEn = $this->get('/herramientas?lang=en');
        $responseEn->assertStatus(200);
        $responseEn->assertSee('Tools for Nodes', false);
        $responseEn->assertSee('ADC Battery Calculator', false);

        $responseBatEn = $this->get('/herramientas/calculadora-bateria-adc?lang=en');
        $responseBatEn->assertStatus(200);
        $this->assertStringNotContainsString('@media (max-width: 768px)', (string) $responseBatEn->getContent());
        $responseBatEn->assertSee('ADC Battery Calculator (adc_multiplier)', false);
        $responseBatEn->assertSee('Why do Meshtastic nodes require a voltage divider?', false);
        $responseBatEn->assertSee('Hardware Tips for DIY Meshtastic', false);
        $responseBatEn->assertSee('Use 1% metal film resistors', false);
        $responseBatEn->assertSee('Safety margin', false);

        // En portugués
        $responsePt = $this->get('/herramientas?lang=pt');
        $responsePt->assertStatus(200);
        $responsePt->assertSee('Ferramentas para Nós e Rede', false);
        $responsePt->assertSee('Calculadora ADC de Bateria', false);

        $responseBatPt = $this->get('/herramientas/calculadora-bateria-adc?lang=pt');
        $responseBatPt->assertStatus(200);
        $this->assertStringNotContainsString('@media (max-width: 768px)', (string) $responseBatPt->getContent());
        $responseBatPt->assertSee('Calculadora ADC de Bateria (adc_multiplier)', false);
        $responseBatPt->assertSee('Por que motivo é necessário um divisor de tensão no Meshtastic?', false);
        $responseBatPt->assertSee('Recomendações de Hardware para Meshtastic DIY', false);
        $responseBatPt->assertSee('Usa resistências metálicas de 1%', false);
        $responseBatPt->assertSee('Margem de segurança', false);
    }
}
