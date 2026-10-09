<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Data\Ingest\MapaData;
use Illuminate\Console\Command;
use Throwable;

class CompilarCacheMapa extends Command
{
    /**
     * El nombre y firma del comando de consola.
     *
     * @var string
     */
    protected $signature = 'mapa:cache {--force : Forzar recompilación inmediata}';

    /**
     * La descripción del comando de consola.
     *
     * @var string
     */
    protected $description = 'Compila los volcados estáticos JSON pre-gzipeados y la caché Redis de nodos para el servicio interactivo Mapa';

    /**
     * Ejecuta el comando de consola.
     */
    public function handle(MapaData $mapaData): int
    {
        $this->info('Iniciando compilación de datos y auditoría para el servicio Mapa...');
        $inicio = microtime(true);

        try {
            $resultado = $mapaData->compilarCache();
            $duracion = round((microtime(true) - $inicio) * 1000, 2);

            $this->info("✓ Caché compilada con éxito en {$duracion} ms.");
            $this->line("  - Total nodos mapeados: {$resultado['nodes_count']}");
            $this->line("  - Nodos no optimizados con aviso: {$resultado['unoptimized_count']}");
            $this->line("  - Activos 1h: {$resultado['stats']['active_1h']}");
            $this->line("  - Gateways: {$resultado['stats']['gateways_count']}");
            $this->line("  - Archivos generados en: public/cache/mapa/");

            return Command::SUCCESS;
        } catch (Throwable $e) {
            $this->error('Error al compilar la caché del mapa: '.$e->getMessage());

            return Command::FAILURE;
        }
    }
}
