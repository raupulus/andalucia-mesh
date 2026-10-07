<?php

declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RutasEstaticasTest extends TestCase
{
    /**
     * @return array<string, array{string}>
     */
    public static function proveedorRutasInstitucionales(): array
    {
        return [
            'proyecto' => ['/proyecto'],
            'quien-lo-impulsa' => ['/quien-lo-impulsa'],
            'como-se-gestiona' => ['/como-se-gestiona'],
            'configura-tu-nodo' => ['/configura-tu-nodo'],
            'conecta-tu-gateway' => ['/conecta-tu-gateway'],
            'bots' => ['/bots'],
            'firmware' => ['/firmware'],
            'api' => ['/api'],
            'aviso-legal' => ['/legal/aviso-legal'],
            'privacidad' => ['/legal/privacidad'],
            'cookies' => ['/legal/cookies'],
        ];
    }

    #[DataProvider('proveedorRutasInstitucionales')]
    public function test_rutas_institucionales_devuelven_200_y_cero_cookies(string $ruta): void
    {
        $response = $this->get($ruta);

        $response->assertStatus(200);
        $response->assertHeaderMissing('Set-Cookie');

        // Metadatos de SEO y Open Graph
        $response->assertSee('<meta property="og:title"', false);
        $response->assertSee('<meta property="og:description"', false);
        $response->assertSee('<meta property="og:image"', false);
        $response->assertSee('<meta name="twitter:card"', false);
        $response->assertSee('lang="es"', false);
    }

    public function test_quien_lo_impulsa_muestra_bloque_de_autoria(): void
    {
        $response = $this->get('/quien-lo-impulsa');

        $response->assertStatus(200);
        $response->assertSee(config('autoria.nick'));
        $response->assertSee(config('autoria.email'));
    }

    public function test_pagina_bots_muestra_tarjetas_visuales_discord_proximamente_y_aviso(): void
    {
        $response = $this->get('/bots');

        $response->assertStatus(200);
        $response->assertHeaderMissing('Set-Cookie');

        // Tarjetas visuales de Telegram y Discord
        $response->assertSee('Bot de Telegram');
        $response->assertSee('Bot de Discord');
        $response->assertSee('Próximamente');
        $response->assertSee('Invitar bot a tu servidor (Próximamente)');

        // Bloques de código para ejemplos de respuesta
        $response->assertSee('Ejemplos de Respuesta');
        $response->assertSee('tarjeta-codigo', false);
        $response->assertSee('bloque-codigo', false);
        $response->assertSee('/status');
        $response->assertSee('/battery');
        $response->assertSee('/routers');

        // Sección de aviso destacado
        $response->assertSee('Así es un Aviso en Directo');
        $response->assertSee('🔴 ALTO · Infraestructura · Bucle de reinicio');
    }

    public function test_sitemap_xml_cumple_estructura(): void
    {
        $response = $this->get('/sitemap.xml');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/xml; charset=utf-8');
        $response->assertSee('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">', false);
        $response->assertSee('/configura-tu-nodo', false);
        $response->assertSee('/legal/privacidad', false);
        $response->assertHeaderMissing('Set-Cookie');
    }

    public function test_robots_txt_bloquea_admin_y_api(): void
    {
        $response = $this->get('/robots.txt');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/plain; charset=utf-8');
        $response->assertSee("User-agent: *", false);
        $response->assertSee("Disallow: /admin", false);
        $response->assertSee("Disallow: /api/v1", false);
        $response->assertSee("Sitemap: https://" . config('proyecto.dominio') . "/sitemap.xml", false);
        $response->assertHeaderMissing('Set-Cookie');
    }

    public function test_paginas_legales_estan_completas_sin_placeholders(): void
    {
        $rutas = ['/legal/cookies', '/legal/privacidad', '/legal/aviso-legal'];

        foreach ($rutas as $ruta) {
            $response = $this->get($ruta);
            $response->assertStatus(200);
            $response->assertHeaderMissing('Set-Cookie');

            $contenido = (string) $response->getContent();
            $this->assertStringNotContainsString('[completar', $contenido);
            $this->assertStringNotContainsString('[fecha de publicación]', $contenido);
            $this->assertStringContainsString('8 de octubre de 2026', $contenido);
        }

        // Verificaciones específicas
        $cookies = (string) $this->get('/legal/cookies')->getContent();
        $this->assertStringContainsString('andalucia_mesh_session', $cookies);
        $this->assertStringContainsString('XSRF-TOKEN', $cookies);
        $this->assertStringContainsString('snm_theme', $cookies);

        $aviso = (string) $this->get('/legal/aviso-legal')->getContent();
        $this->assertStringContainsString('PotatoMesh', $aviso);
        $this->assertStringContainsString('MeshView', $aviso);
        $this->assertStringContainsString('public@raupulus.dev', $aviso);

        $privacidad = (string) $this->get('/legal/privacidad')->getContent();
        $this->assertStringContainsString('public@raupulus.dev', $privacidad);
        $this->assertStringContainsString('OK to MQTT', $privacidad);
    }
}

