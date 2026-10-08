<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\CustomPage;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomPagesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Comprueba que /paginas devuelve 200 OK y no establece cookies (RN-06).
     */
    public function test_listado_paginas_devuelve_200_y_cero_cookies(): void
    {
        $response = $this->get('/paginas');

        $response->assertStatus(200);
        $response->assertHeaderMissing('Set-Cookie');
        $response->assertSee('Páginas y Artículos', false);
    }

    /**
     * Comprueba que se muestra el estado vacío amigable cuando no hay páginas.
     */
    public function test_listado_paginas_muestra_estado_vacio(): void
    {
        $response = $this->get('/paginas');

        $response->assertStatus(200);
        $response->assertSee('Aún no se han publicado páginas', false);
    }

    /**
     * Comprueba que se listan páginas activas con tarjetas horizontales, franja andaluza y keywords verdes.
     */
    public function test_listado_paginas_muestra_tarjetas_horizontales_con_franja_y_keywords(): void
    {
        CustomPage::create([
            'title' => 'Guía de Antenas para Malla',
            'slug' => 'guia-antenas-malla',
            'description' => 'Aprende a elegir y colocar tu antena para máxima cobertura.',
            'content' => '## Introducción a las antenas LoRa...',
            'keywords' => ['Antenas', 'LoRa', 'Cobertura'],
            'is_active' => true,
        ]);

        CustomPage::create([
            'title' => 'Página Borrador Oculta',
            'slug' => 'pagina-borrador-oculta',
            'description' => 'No debe aparecer en el listado público.',
            'content' => 'Borrador...',
            'is_active' => false,
        ]);

        $response = $this->get('/paginas');

        $response->assertStatus(200);
        $response->assertSee('Guía de Antenas para Malla', false);
        $response->assertSee('Aprende a elegir y colocar tu antena', false);
        $response->assertSee('tarjeta-pagina-horizontal', false);
        $response->assertSee('franja-andalucia-vertical', false);
        $response->assertSee('tarjeta-pagina-accion', false);
        $response->assertSee('badge-keyword', false);
        $response->assertSee('Antenas', false);
        $response->assertSee('LoRa', false);
        // La inactiva no debe verse
        $response->assertDontSee('Página Borrador Oculta', false);
    }

    /**
     * Comprueba que cover_image_url resuelve correctamente assets locales y fallback animado oficial.
     */
    public function test_cover_image_url_resuelve_local_y_fallback(): void
    {
        $paginaSinImagen = new CustomPage;
        $this->assertStringContainsString('img/paginas/default-banner.svg', $paginaSinImagen->cover_image_url);

        $paginaConImagenLocal = new CustomPage(['featured_image' => 'img/servicios/rankings.webp']);
        $this->assertStringContainsString('/img/servicios/rankings.webp', $paginaConImagenLocal->cover_image_url);
        $this->assertStringNotContainsString('/storage/', $paginaConImagenLocal->cover_image_url);
    }

    /**
     * Comprueba que el detalle de página carga correctamente con su imagen, metadatos y contenido Markdown.
     */
    public function test_detalle_pagina_devuelve_200_con_imagen_y_seo(): void
    {
        $pagina = CustomPage::create([
            'title' => 'Despliegue Solar de Repetidores',
            'slug' => 'despliegue-solar-repetidores',
            'description' => 'Cómo alimentar un nodo router en montaña de forma ininterrumpida.',
            'content' => "## Componentes necesarios\n\n* Panel solar de 10W\n* Batería 18650\n* BMS 1S",
            'keywords' => ['Solar', 'Hardware', 'Energía'],
            'featured_image' => 'https://mesh.desdechipiona.es/img/revisa-nodo-banner.webp',
            'is_active' => true,
        ]);

        $response = $this->get('/paginas/despliegue-solar-repetidores');

        $response->assertStatus(200);
        $response->assertHeaderMissing('Set-Cookie');
        $response->assertSee('Despliegue Solar de Repetidores', false);
        $response->assertSee('Cómo alimentar un nodo router', false);
        $response->assertSee('Componentes necesarios', false);
        $response->assertSee('Panel solar de 10W', false);
        $response->assertSee('pagina-detalle-img-wrapper', false);
        $response->assertSee('https://mesh.desdechipiona.es/img/revisa-nodo-banner.webp', false);
        $response->assertSee('badge-keyword', false);
        $response->assertSee('Solar', false);
        // Comprobar SEO Schema.org Article
        $response->assertSee('"@type":"Article"', false);
    }

    /**
     * Comprueba que acceder a una página inactiva o un slug inexistente devuelve 404.
     */
    public function test_detalle_pagina_inactiva_o_inexistente_devuelve_404(): void
    {
        CustomPage::create([
            'title' => 'Oculto al Público',
            'slug' => 'oculto-al-publico',
            'description' => 'Página privada',
            'content' => 'Contenido secreto',
            'is_active' => false,
        ]);

        $response404Inexistente = $this->get('/paginas/no-existe-este-slug');
        $response404Inexistente->assertStatus(404);

        $response404Inactiva = $this->get('/paginas/oculto-al-publico');
        $response404Inactiva->assertStatus(404);
    }

    /**
     * Comprueba que la portada incluye hasta 4 tarjetas compactas de páginas y el botón verde.
     */
    public function test_portada_muestra_hasta_4_paginas_con_boton_verde(): void
    {
        for ($i = 1; $i <= 6; $i++) {
            CustomPage::create([
                'title' => "Artículo {$i} de Prueba",
                'slug' => "articulo-{$i}-prueba",
                'description' => "Descripción breve del artículo {$i}.",
                'content' => "Contenido del artículo {$i}.",
                'keywords' => ["Tag{$i}"],
                'is_active' => true,
                'created_at' => now()->addMinutes($i),
            ]);
        }

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('grid-paginas-portada', false);
        $response->assertSee('tarjeta-pagina-compacta', false);
        $response->assertSee('btn-verde', false);
        $response->assertSee('/paginas', false);

        // Deben verse los 4 más recientes (6, 5, 4, 3)
        $response->assertSee('Artículo 6 de Prueba', false);
        $response->assertSee('Artículo 5 de Prueba', false);
        $response->assertSee('Artículo 4 de Prueba', false);
        $response->assertSee('Artículo 3 de Prueba', false);
        // Los artículos 1 y 2 quedan fuera de los 4 primeros de la portada
        $response->assertDontSee('Artículo 1 de Prueba', false);
        $response->assertDontSee('Artículo 2 de Prueba', false);
    }

    /**
     * Comprueba que sitemap.xml incluye la ruta /paginas y las URLs de slugs de páginas activas.
     */
    public function test_sitemap_incluye_paginas_y_slugs_dinamicos(): void
    {
        CustomPage::create([
            'title' => 'Página para Sitemap',
            'slug' => 'pagina-para-sitemap',
            'description' => 'Descripción',
            'content' => 'Contenido',
            'is_active' => true,
        ]);

        CustomPage::create([
            'title' => 'Página Oculta Fuera de Sitemap',
            'slug' => 'pagina-oculta-fuera-de-sitemap',
            'description' => 'Descripción',
            'content' => 'Contenido',
            'is_active' => false,
        ]);

        $response = $this->get('/sitemap.xml');

        $response->assertStatus(200);
        $response->assertSee('/paginas</loc>', false);
        $response->assertSee('/paginas/pagina-para-sitemap</loc>', false);
        $response->assertDontSee('/paginas/pagina-oculta-fuera-de-sitemap</loc>', false);
    }

    /**
     * Comprueba que un operador autenticado puede ver el listado de páginas en Filament.
     */
    public function test_operador_puede_ver_listado_en_filament(): void
    {
        $operador = User::factory()->create([
            'email' => 'operador@andalucia.mesh',
            'activo' => true,
        ]);

        CustomPage::create([
            'title' => 'Página Panel Operador',
            'slug' => 'pagina-panel-operador',
            'description' => 'Descripción del artículo en panel.',
            'content' => 'Contenido.',
            'is_active' => true,
        ]);

        $response = $this->actingAs($operador)
            ->get('/admin/custom-pages');

        $response->assertStatus(200);
        $response->assertSee('Páginas');
        $response->assertSee('Página Panel Operador');
        $response->assertSee('/paginas/pagina-panel-operador');
    }

    /**
     * Comprueba que la creación en base de datos valida slug único.
     */
    public function test_slug_es_unico(): void
    {
        CustomPage::create([
            'title' => 'Primera Página',
            'slug' => 'slug-duplicado',
            'description' => 'Primera descripción.',
            'content' => 'Contenido.',
            'is_active' => true,
        ]);

        $this->expectException(UniqueConstraintViolationException::class);

        CustomPage::create([
            'title' => 'Segunda Página',
            'slug' => 'slug-duplicado',
            'description' => 'Segunda descripción.',
            'content' => 'Contenido.',
            'is_active' => true,
        ]);
    }
}
