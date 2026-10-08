<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Faq;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Pruebas de integración para la página pública de preguntas frecuentes (FAQ) y su gestión en Filament.
 */
class FaqTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Comprueba que la página /faq devuelva 200 OK y cumpla la directiva estricta de cero cookies.
     */
    public function test_pagina_faq_es_accesible_y_devuelve_200_con_cero_cookies(): void
    {
        $response = $this->get('/faq');

        $response->assertStatus(200);
        $response->assertHeaderMissing('Set-Cookie');
        $response->assertSee('Preguntas Frecuentes');
        $response->assertSee('Centro de Ayuda');
        $response->assertSee('Volver al inicio');
        $response->assertSee('buscador-faq', false);
    }

    /**
     * Comprueba que únicamente se muestren preguntas activas y ordenadas por sort_order ascendente.
     */
    public function test_pagina_faq_muestra_preguntas_activas_en_orden_correcto(): void
    {
        Faq::create([
            'question' => '¿Cómo configuro el canal secundario?',
            'answer' => 'Debes acceder a los ajustes de canal en la app.',
            'sort_order' => 20,
            'is_active' => true,
        ]);

        Faq::create([
            'question' => '¿Cuál es el canal primario en Andalucía?',
            'answer' => 'El canal primario oficial es SFNarrow.',
            'sort_order' => 10,
            'is_active' => true,
        ]);

        Faq::create([
            'question' => '¿Pregunta en borrador interno?',
            'answer' => 'Esta respuesta no debería verse públicamente.',
            'sort_order' => 5,
            'is_active' => false,
        ]);

        $response = $this->get('/faq');

        $response->assertStatus(200);
        $response->assertSee('¿Cuál es el canal primario en Andalucía?');
        $response->assertSee('¿Cómo configuro el canal secundario?');
        $response->assertDontSee('¿Pregunta en borrador interno?');
        $response->assertDontSee('Esta respuesta no debería verse públicamente.');

        // Verificar el orden de visualización relativo
        $content = $response->getContent();
        $this->assertNotFalse($content);
        $posicionPrimera = strpos($content, '¿Cuál es el canal primario en Andalucía?');
        $posicionSegunda = strpos($content, '¿Cómo configuro el canal secundario?');
        $this->assertLessThan($posicionSegunda, $posicionPrimera, 'Las preguntas deben ordenarse por sort_order ascendente');
    }

    /**
     * Comprueba que la respuesta procese formato Markdown y sustituya variables del sistema.
     */
    public function test_pagina_faq_renderiza_markdown_y_variables_en_respuestas(): void
    {
        Faq::create([
            'question' => '¿Qué parámetros de radio usamos?',
            'answer' => 'Usamos **SFNarrow** en la red {PROJECT_NAME}. Más info en `client.meshtastic.org`.',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $response = $this->get('/faq');

        $response->assertStatus(200);
        $response->assertSee('<strong>SFNarrow</strong>', false);
        $response->assertSee(config('proyecto.nombre'), false);
        $response->assertSee('<code>client.meshtastic.org</code>', false);
    }

    /**
     * Comprueba que se muestre un estado vacío amigable cuando aún no hay preguntas.
     */
    public function test_pagina_faq_muestra_estado_vacio_si_no_hay_preguntas(): void
    {
        $response = $this->get('/faq');

        $response->assertStatus(200);
        $response->assertSee('No hay preguntas disponibles por el momento');
    }

    /**
     * Comprueba que el menú desplegable Extras en la cabecera incluya el enlace a /faq.
     */
    public function test_menu_extras_en_navbar_enlaza_a_faq(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('/faq');
        $response->assertSee('FAQ');
    }

    /**
     * Comprueba que los operadores autorizados puedan acceder a la gestión de FAQs en Filament.
     */
    public function test_operador_puede_ver_y_gestionar_faqs_en_filament(): void
    {
        $operador = User::factory()->create([
            'email' => 'operador@andalucia.mesh',
            'activo' => true,
        ]);

        $faq = Faq::create([
            'question' => '¿Por qué no recibo telemetría?',
            'answer' => 'Verifica la visibilidad óptica directa hacia el repetidor más cercano.',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $response = $this->actingAs($operador)
            ->get('/admin/faqs');

        $response->assertStatus(200);
        $response->assertSee('Preguntas Frecuentes');
        $response->assertSee('¿Por qué no recibo telemetría?');

        // Actualizar datos del registro
        $faq->update([
            'question' => '¿Por qué no recibo telemetría actualizada?',
            'is_active' => false,
        ]);

        $this->assertEquals('¿Por qué no recibo telemetría actualizada?', $faq->fresh()->question);
        $this->assertFalse($faq->fresh()->is_active);
    }

    /**
     * Comprueba la correcta traducción de la página FAQ a inglés y portugués manteniendo cero cookies.
     */
    public function test_traducciones_pagina_faq(): void
    {
        Faq::create([
            'question' => 'Sample Question',
            'answer' => 'Sample Answer',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        // Inglés
        $respEn = $this->get('/faq?lang=en');
        $respEn->assertStatus(200);
        $respEn->assertHeaderMissing('Set-Cookie');
        $respEn->assertSee('Frequently Asked Questions');
        $respEn->assertSee('Help Center');
        $respEn->assertSee('Back to home');

        // Portugués
        $respPt = $this->get('/faq?lang=pt');
        $respPt->assertStatus(200);
        $respPt->assertHeaderMissing('Set-Cookie');
        $respPt->assertSee('Perguntas Frequentes');
        $respPt->assertSee('Centro de Ajuda');
        $respPt->assertSee('Voltar ao início');
    }
}
