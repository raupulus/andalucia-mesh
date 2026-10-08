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

    /**
     * Verifica que las tipografías oficiales autoalojadas estén presentes en public/fonts/ y declaradas en app.css (DESIGN.md §5, AUD-UI-01).
     */
    public function test_tipografias_autoalojadas_presentes_y_declaradas(): void
    {
        $fuentesRequeridas = [
            'ubuntu-500.woff2',
            'ubuntu-700.woff2',
            'inter-variable.woff2',
            'ubuntu-mono-400.woff2',
            'ubuntu-mono-700.woff2',
        ];

        foreach ($fuentesRequeridas as $fuente) {
            $ruta = public_path("fonts/{$fuente}");
            $this->assertFileExists($ruta, "El fichero tipográfico {$fuente} debe existir en public/fonts/");
            $this->assertGreaterThan(5000, filesize($ruta), "El fichero {$fuente} debe ser un archivo binario válido");
        }

        $cssContent = (string) file_get_contents(resource_path('css/app.css'));
        $this->assertStringContainsString('@font-face', $cssContent);
        $this->assertStringContainsString("font-family: 'Ubuntu'", $cssContent);
        $this->assertStringContainsString("font-family: 'Inter'", $cssContent);
        $this->assertStringContainsString("font-family: 'Ubuntu Mono'", $cssContent);
        $this->assertStringContainsString('--font-titulos', $cssContent);
    }

    /**
     * Verifica que el layout incluya metatags sociales completos, SEO hreflang, robots y Schema.org.
     */
    public function test_metatags_seo_y_redes_sociales_completos(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);

        // Atributo dir="ltr" en html (W3C-B02)
        $response->assertSee('dir="ltr"', false);

        // Directivas de rastreo robots (SEO-A02)
        $response->assertSee('<meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">', false);

        // Etiquetas multilingües hreflang (SEO-A01)
        $response->assertSee('hreflang="es"', false);
        $response->assertSee('hreflang="en"', false);
        $response->assertSee('hreflang="pt"', false);
        $response->assertSee('hreflang="x-default"', false);

        // Open Graph completo (SOC-M01)
        $response->assertSee('<meta property="og:type" content="website">', false);
        $response->assertSee('<meta property="og:locale" content="es_ES">', false);
        $response->assertSee('<meta property="og:locale:alternate" content="en_GB">', false);
        $response->assertSee('<meta property="og:locale:alternate" content="pt_PT">', false);
        $response->assertSee('<meta property="og:image:width" content="1200">', false);
        $response->assertSee('<meta property="og:image:height" content="630">', false);
        $response->assertSee('<meta property="og:image:type" content="image/png">', false);

        // Twitter Card con creador (SOC-B01)
        $response->assertSee('<meta name="twitter:card" content="summary_large_image">', false);
        $response->assertSee('<meta name="twitter:creator" content="@raupulus">', false);

        // Schema.org WebSite y Organization (SEO-M02)
        $response->assertSee('application/ld+json', false);
        $response->assertSee('"@type":"WebSite"', false);
        $response->assertSee('"@type":"Organization"', false);
    }
}
