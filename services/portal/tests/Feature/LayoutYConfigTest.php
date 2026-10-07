<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class LayoutYConfigTest extends TestCase
{
    /**
     * Comprueba que las configuraciones de proyecto y autoría contengan los datos obligatorios.
     */
    public function test_configuraciones_centralizadas_cargan_correctamente(): void
    {
        $this->assertEquals('Andalucía Mesh', config('proyecto.nombre'));
        $this->assertEquals('Europe/Madrid', config('proyecto.zona_horaria'));
        $this->assertCount(14, config('proyecto.canales.permitidos'));
        $this->assertCount(3, config('proyecto.tarjetas'));

        // Autoría y privacidad estricta
        $this->assertEquals('@raupulus', config('autoria.nick'));
        $this->assertEquals('public@raupulus.dev', config('autoria.email'));
    }

    /**
     * Certifica que las peticiones web públicas NO establecen cookies de sesión (regla de cero rastreo).
     */
    public function test_rutas_publicas_no_emiten_set_cookie(): void
    {
        $response = $this->get('/');

        // Debe responder 200 OK
        $response->assertStatus(200);

        // NUNCA debe contener la cabecera Set-Cookie
        $this->assertFalse($response->headers->has('Set-Cookie'), 'La respuesta pública no debe emitir cookies');
    }

    /**
     * Verifica que el archivo CSS contenga los tokens semánticos de DESIGN.md.
     */
    public function test_tokens_css_presentes_en_app_css(): void
    {
        $cssPath = resource_path('css/app.css');
        $this->assertFileExists($cssPath);
        $cssContent = file_get_contents($cssPath);

        $tokens = [
            '--color-fondo',
            '--color-superficie',
            '--color-texto',
            '--color-acento',
            '--color-critico-texto',
            '--mapa-verde',
            '--mapa-naranja',
            '--mapa-rojo',
        ];

        foreach ($tokens as $token) {
            $this->assertStringContainsString($token, $cssContent, "El token {$token} debe estar definido en app.css");
        }
    }
}
