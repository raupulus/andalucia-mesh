<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\CustomPage;
use Illuminate\Http\Response;
use Illuminate\Routing\Route as LaravelRoute;
use Illuminate\Support\Facades\Route;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class SitemapController extends Controller
{
    /**
     * Patrones de rutas del sistema y áreas privadas que deben excluirse del sitemap.
     *
     * @var list<string>
     */
    protected const EXCLUDED_PATTERNS = [
        'admin*',
        'filament*',
        'livewire*',
        'api*',
        'health*',
        'storage*',
        'up',
        'sitemap*',
        'robots*',
    ];

    /**
     * Devuelve el sitemap.xml con las páginas públicas indexables generado dinámicamente con Spatie Sitemap.
     */
    public function sitemap(): Response
    {
        $sitemap = $this->buildSitemap();

        return response($sitemap->render(), 200, [
            'Content-Type' => 'application/xml; charset=utf-8',
        ]);
    }

    /**
     * Devuelve el archivo robots.txt restringiendo el rastreo de áreas privadas o APIs.
     */
    public function robots(): Response
    {
        $domain = (string) config('proyecto.dominio', 'mesh.desdechipiona.es');

        $contenido = "User-agent: *\n"
            ."Disallow: /admin\n"
            ."Disallow: /api/v1\n"
            ."Sitemap: https://{$domain}/sitemap.xml\n";

        return response($contenido, 200, [
            'Content-Type' => 'text/plain; charset=utf-8',
        ]);
    }

    /**
     * Devuelve el archivo robots.xml con directivas equivalentes en formato XML.
     */
    public function robotsXml(): Response
    {
        $domain = (string) config('proyecto.dominio', 'mesh.desdechipiona.es');

        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n"
            ."<robots>\n"
            ."    <sitemap>https://{$domain}/sitemap.xml</sitemap>\n"
            ."    <rules>\n"
            ."        <user-agent>*</user-agent>\n"
            ."        <disallow>/admin</disallow>\n"
            ."        <disallow>/api/v1</disallow>\n"
            ."    </rules>\n"
            ."</robots>\n";

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=utf-8',
        ]);
    }

    /**
     * Construye dinámicamente el objeto Sitemap a partir de las rutas registradas en la aplicación.
     */
    public function buildSitemap(): Sitemap
    {
        $domain = (string) config('proyecto.dominio', 'mesh.desdechipiona.es');
        $baseUrl = 'https://'.$domain;
        $sitemap = Sitemap::create();

        $routes = Route::getRoutes()->getRoutes();
        $urlsAgregadas = [];

        foreach ($routes as $route) {
            if (! $this->isIndexableRoute($route)) {
                continue;
            }

            $uri = $route->uri();
            $path = ($uri === '/' || $uri === '') ? '' : '/'.ltrim($uri, '/');
            $fullUrl = $baseUrl.$path;

            if (isset($urlsAgregadas[$fullUrl])) {
                continue;
            }
            $urlsAgregadas[$fullUrl] = true;

            $meta = $this->resolveRouteMetadata($uri);

            $tag = Url::create($fullUrl)
                ->setPriority($meta['priority'])
                ->setChangeFrequency($meta['frequency'])
                ->setLastModificationDate(now());

            $sitemap->add($tag);
        }

        // Añadir páginas dinámicas activas del portal (/paginas/{slug})
        try {
            $paginas = CustomPage::active()->get();
            foreach ($paginas as $pagina) {
                $pageUrl = $baseUrl.'/paginas/'.$pagina->slug;

                if (isset($urlsAgregadas[$pageUrl])) {
                    continue;
                }
                $urlsAgregadas[$pageUrl] = true;

                $tag = Url::create($pageUrl)
                    ->setPriority(0.7)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                    ->setLastModificationDate($pagina->updated_at ?? now());

                $sitemap->add($tag);
            }
        } catch (\Throwable) {
            // Silencioso en caso de pruebas sin base de datos
        }

        return $sitemap;
    }

    /**
     * Determina si una ruta de Laravel es indexable en el sitemap público.
     */
    protected function isIndexableRoute(LaravelRoute $route): bool
    {
        if (! in_array('GET', $route->methods(), true)) {
            return false;
        }

        $uri = trim($route->uri(), '/');

        // Excluir rutas con parámetros dinámicos (p. ej. alertas/{id}, revisa-tu-nodo/{id})
        if (str_contains($uri, '{')) {
            return false;
        }

        // Excluir patrones de administración, API, salud o assets
        foreach (self::EXCLUDED_PATTERNS as $pattern) {
            if (fnmatch($pattern, $uri)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Resuelve la prioridad y frecuencia de rastreo para una URI pública.
     *
     * @return array{priority: float, frequency: string}
     */
    protected function resolveRouteMetadata(string $uri): array
    {
        $clean = trim($uri, '/');

        if ($clean === '') {
            return ['priority' => 1.0, 'frequency' => Url::CHANGE_FREQUENCY_HOURLY];
        }

        if (in_array($clean, ['rankings', 'alertas'], true)) {
            return ['priority' => 0.8, 'frequency' => Url::CHANGE_FREQUENCY_HOURLY];
        }

        if (in_array($clean, ['configura-tu-nodo', 'conecta-tu-gateway', 'proyecto', 'revisa-tu-nodo', 'paginas', 'hardware'], true)) {
            return ['priority' => 0.8, 'frequency' => Url::CHANGE_FREQUENCY_WEEKLY];
        }

        if (in_array($clean, ['bots', 'firmware', 'quien-lo-impulsa', 'como-se-gestiona'], true)) {
            return ['priority' => 0.7, 'frequency' => Url::CHANGE_FREQUENCY_WEEKLY];
        }

        if ($clean === 'api') {
            return ['priority' => 0.6, 'frequency' => Url::CHANGE_FREQUENCY_WEEKLY];
        }

        if ($clean === 'sugerencias') {
            return ['priority' => 0.5, 'frequency' => Url::CHANGE_FREQUENCY_MONTHLY];
        }

        if (str_starts_with($clean, 'legal/')) {
            return ['priority' => 0.3, 'frequency' => Url::CHANGE_FREQUENCY_MONTHLY];
        }

        return ['priority' => 0.7, 'frequency' => Url::CHANGE_FREQUENCY_WEEKLY];
    }
}
