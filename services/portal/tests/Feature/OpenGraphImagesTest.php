<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Pruebas automatizadas de imágenes Open Graph y Twitter Cards en WebP.
 *
 * Verifica que cada ruta pública del portal declare en su HTML las etiquetas meta
 * de imagen social con su archivo WebP específico y el tipo MIME 'image/webp'.
 */
class OpenGraphImagesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Proveedor de rutas públicas y sus respectivas imágenes Open Graph asignadas.
     *
     * @return array<string, array{0: string, 1: string}>
     */
    public static function rutasEImagenesProvider(): array
    {
        return [
            'portada' => ['/', 'og-portada.webp'],
            'proyecto' => ['/proyecto', 'og-proyecto.webp'],
            'quien lo impulsa' => ['/quien-lo-impulsa', 'og-quien-lo-impulsa.webp'],
            'como se gestiona' => ['/como-se-gestiona', 'og-como-se-gestiona.webp'],
            'configura tu nodo' => ['/configura-tu-nodo', 'og-configura-nodo.webp'],
            'conecta tu gateway' => ['/conecta-tu-gateway', 'og-conecta-gateway.webp'],
            'configurador' => ['/configurador', 'og-configurador.webp'],
            'rankings' => ['/rankings', 'og-rankings.webp'],
            'routers' => ['/routers', 'og-routers.webp'],
            'alertas' => ['/alertas', 'og-alertas.webp'],
            'bots' => ['/bots', 'og-bots.webp'],
            'firmware' => ['/firmware', 'og-firmware.webp'],
            'revisa tu nodo' => ['/revisa-tu-nodo', 'og-revisa-nodo.webp'],
            'hardware' => ['/hardware', 'og-hardware.webp'],
            'paginas' => ['/paginas', 'og-paginas.webp'],
            'api' => ['/api', 'og-api.webp'],
            'sugerencias' => ['/sugerencias', 'og-sugerencias.webp'],
            'faq' => ['/faq', 'og-faq.webp'],
            'aviso legal' => ['/legal/aviso-legal', 'og-aviso-legal.webp'],
            'privacidad' => ['/legal/privacidad', 'og-privacidad.webp'],
            'cookies' => ['/legal/cookies', 'og-cookies.webp'],
        ];
    }

    /**
     * Comprueba que cada página pública incluye su imagen WebP en las meta etiquetas OG y Twitter.
     */
    #[DataProvider('rutasEImagenesProvider')]
    public function test_pagina_incluye_imagen_og_webp_especifica(string $ruta, string $imagenEsperada): void
    {
        $response = $this->get($ruta);

        $response->assertStatus(200);

        $contenido = (string) $response->getContent();

        // Debe contener la meta property og:image apuntando al archivo WebP esperado
        $this->assertStringContainsString(
            'property="og:image"',
            $contenido,
            "La ruta {$ruta} no contiene meta property og:image"
        );
        $this->assertStringContainsString(
            $imagenEsperada,
            $contenido,
            "La ruta {$ruta} no enlaza a {$imagenEsperada}"
        );

        // Debe especificar el MIME type image/webp dinámicamente
        $this->assertStringContainsString(
            '<meta property="og:image:type" content="image/webp">',
            $contenido,
            "La ruta {$ruta} no contiene og:image:type image/webp"
        );

        // Twitter Card debe apuntar a la misma imagen
        $this->assertStringContainsString(
            '<meta name="twitter:card" content="summary_large_image">',
            $contenido
        );
        $this->assertStringContainsString(
            'name="twitter:image"',
            $contenido
        );
    }

    /**
     * Comprueba que todos los archivos físicos de imagen WebP existen en public/img/og/
     * y tienen dimensiones estándar de 1200x630.
     */
    public function test_archivos_fisicos_webp_existen_con_dimensiones_correctas(): void
    {
        $directorio = public_path('img/og');
        $this->assertDirectoryExists($directorio);

        foreach (self::rutasEImagenesProvider() as $caso => [$ruta, $fichero]) {
            $rutaFichero = $directorio.'/'.$fichero;
            $this->assertFileExists(
                $rutaFichero,
                "El archivo físico de imagen {$fichero} no existe en public/img/og/"
            );

            // Verificar dimensiones de imagen
            $info = getimagesize($rutaFichero);
            $this->assertNotFalse($info, "No se pudo leer la información de imagen de {$fichero}");
            $this->assertSame(1200, $info[0], "El ancho de {$fichero} debe ser exactamente 1200 px");
            $this->assertSame(630, $info[1], "El alto de {$fichero} debe ser exactamente 630 px");
            $this->assertSame('image/webp', $info['mime'], "El tipo MIME de {$fichero} debe ser image/webp");
        }
    }
}
