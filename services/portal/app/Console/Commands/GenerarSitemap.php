<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Http\Controllers\SitemapController;
use Illuminate\Console\Command;

class GenerarSitemap extends Command
{
    /**
     * El nombre y firma del comando de consola.
     *
     * @var string
     */
    protected $signature = 'portal:sitemap';

    /**
     * La descripción del comando de consola.
     *
     * @var string
     */
    protected $description = 'Genera el archivo sitemap.xml en el directorio public/ dinamizando las rutas públicas existentes';

    /**
     * Ejecuta el comando de consola.
     */
    public function handle(SitemapController $controller): int
    {
        $this->info('Extrayendo rutas públicas e indexables de la aplicación...');

        $sitemap = $controller->buildSitemap();
        $rutaDestino = public_path('sitemap.xml');

        $sitemap->writeToFile($rutaDestino);

        $tamano = file_exists($rutaDestino) ? filesize($rutaDestino) : 0;
        $this->info("Sitemap generado con éxito en {$rutaDestino} ({$tamano} bytes).");

        return Command::SUCCESS;
    }
}
