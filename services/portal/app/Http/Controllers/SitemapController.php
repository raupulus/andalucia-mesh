<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * Devuelve el sitemap.xml con las páginas públicas indexables.
     */
    public function sitemap(): Response
    {
        $baseUrl = 'https://' . (string) config('proyecto.dominio');

        $rutas = [
            ['path' => '', 'frecuencia' => 'hourly', 'prioridad' => '1.0'],
            ['path' => '/configura-tu-nodo', 'frecuencia' => 'weekly', 'prioridad' => '0.9'],
            ['path' => '/conecta-tu-gateway', 'frecuencia' => 'weekly', 'prioridad' => '0.8'],
            ['path' => '/proyecto', 'frecuencia' => 'weekly', 'prioridad' => '0.8'],
            ['path' => '/rankings', 'frecuencia' => 'hourly', 'prioridad' => '0.8'],
            ['path' => '/alertas', 'frecuencia' => 'hourly', 'prioridad' => '0.8'],
            ['path' => '/revisa-tu-nodo', 'frecuencia' => 'weekly', 'prioridad' => '0.8'],
            ['path' => '/bots', 'frecuencia' => 'weekly', 'prioridad' => '0.7'],
            ['path' => '/firmware', 'frecuencia' => 'weekly', 'prioridad' => '0.7'],
            ['path' => '/quien-lo-impulsa', 'frecuencia' => 'monthly', 'prioridad' => '0.7'],
            ['path' => '/como-se-gestiona', 'frecuencia' => 'monthly', 'prioridad' => '0.7'],
            ['path' => '/api', 'frecuencia' => 'weekly', 'prioridad' => '0.6'],
            ['path' => '/legal/aviso-legal', 'frecuencia' => 'monthly', 'prioridad' => '0.3'],
            ['path' => '/legal/privacidad', 'frecuencia' => 'monthly', 'prioridad' => '0.3'],
            ['path' => '/legal/cookies', 'frecuencia' => 'monthly', 'prioridad' => '0.3'],
        ];

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        $hoy = date('Y-m-d');
        foreach ($rutas as $r) {
            $loc = htmlspecialchars($baseUrl . $r['path'], ENT_XML1, 'UTF-8');
            $xml .= "    <url>\n";
            $xml .= "        <loc>{$loc}</loc>\n";
            $xml .= "        <lastmod>{$hoy}</lastmod>\n";
            $xml .= "        <changefreq>{$r['frecuencia']}</changefreq>\n";
            $xml .= "        <priority>{$r['prioridad']}</priority>\n";
            $xml .= "    </url>\n";
        }

        $xml .= '</urlset>';

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=utf-8',
        ]);
    }

    /**
     * Devuelve el archivo robots.txt restringiendo el rastreo de áreas privadas o APIs.
     */
    public function robots(): Response
    {
        $domain = (string) config('proyecto.dominio');

        $contenido = "User-agent: *\n"
            . "Disallow: /admin\n"
            . "Disallow: /api/v1\n"
            . "Sitemap: https://{$domain}/sitemap.xml\n";

        return response($contenido, 200, [
            'Content-Type' => 'text/plain; charset=utf-8',
        ]);
    }
}
