<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class VerificarVistasContrato extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'portal:vistas';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica que las 20 vistas de contrato api_* existan en la base de datos de ingesta y contengan las columnas requeridas';

    /**
     * Catálogo de vistas y sus columnas contractuales requeridas.
     *
     * @var array<string, list<string>>
     */
    protected array $vistasContrato = [
        'api_summary' => ['nodes_active_24h', 'nodes_active_7d', 'routers_active_24h', 'gateways_publishing', 'packets_last_hour', 'generated_at'],
        'api_province_load' => ['node_id', 'province', 'channel_utilization', 'measured_at', 'grupo'],
        'api_routers' => ['id', 'short_name', 'long_name', 'role', 'province', 'battery_level', 'voltage', 'battery_at', 'channel_utilization', 'air_util_tx', 'metrics_at', 'last_seen', 'hw_model', 'powered', 'is_gateway'],
        'api_gateways' => ['id', 'short_name', 'long_name', 'last_message_at', 'typical_interval_s', 'packets_last_hour', 'unique_nodes_24h'],
        'api_nodes' => ['id', 'short_name', 'long_name', 'role', 'hw_model', 'firmware', 'province', 'last_position_at', 'position_precision_m', 'border_uncertain', 'hop_start_last', 'is_gateway', 'first_seen', 'last_seen', 'is_router'],
        'api_traffic_mix' => ['bucket_start', 'granularity', 'portnum', 'packets', 'airtime_s'],
        'api_rank_network_usage' => ['bucket_start', 'granularity', 'subject_id', 'value', 'extra'],
        'api_rank_gateway_coverage' => ['bucket_start', 'granularity', 'subject_id', 'value', 'extra'],
        'api_rank_gateway_exclusive' => ['bucket_start', 'granularity', 'subject_id', 'value', 'extra'],
        'api_rank_longest_links' => ['bucket_start', 'granularity', 'subject_id', 'value', 'extra'],
        'api_rank_best_links' => ['bucket_start', 'granularity', 'subject_id', 'value', 'extra'],
        'api_rank_most_neighbors' => ['bucket_start', 'granularity', 'subject_id', 'value', 'extra'],
        'api_rank_uptime' => ['bucket_start', 'granularity', 'subject_id', 'value', 'extra'],
        'api_rank_solar_health' => ['bucket_start', 'granularity', 'subject_id', 'value', 'extra'],
        'api_rank_chatters' => ['bucket_start', 'granularity', 'subject_id', 'value', 'extra'],
        'api_rank_new_nodes' => ['bucket_start', 'granularity', 'subject_id', 'value', 'extra'],
        'api_rank_provinces_growth' => ['bucket_start', 'granularity', 'subject_id', 'value', 'extra'],
        'api_node_intervals' => ['node_id', 'portnum', 'variant', 'broadcasts', 'median_interval_s'],
        'api_node_battery_daily' => ['node_id', 'day', 'min_level', 'avg_level', 'readings', 'readings_below_40', 'max_level'],
        'api_node_reboots_daily' => ['node_id', 'day', 'reboots', 'readings'],
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Iniciando auditoría de vistas contrato api_* en conexión [ingesta]...');

        try {
            DB::connection('ingesta')->getPdo();
        } catch (Throwable $e) {
            $this->error("No se pudo conectar a la base de datos de ingesta: {$e->getMessage()}");

            return Command::FAILURE;
        }

        $todasCorrectas = true;
        $filasTabla = [];

        foreach ($this->vistasContrato as $nombreVista => $columnasRequeridas) {
            // Comprobar existencia de la vista
            $existe = DB::connection('ingesta')
                ->table('information_schema.views')
                ->where('table_schema', 'public')
                ->where('table_name', $nombreVista)
                ->exists();

            if (! $existe) {
                $filasTabla[] = [$nombreVista, 'NO EXISTE', 'Faltan todas'];
                $todasCorrectas = false;

                continue;
            }

            // Obtener columnas existentes
            $columnasDb = DB::connection('ingesta')
                ->table('information_schema.columns')
                ->where('table_schema', 'public')
                ->where('table_name', $nombreVista)
                ->pluck('column_name')
                ->toArray();

            $faltantes = array_diff($columnasRequeridas, $columnasDb);

            if (empty($faltantes)) {
                $filasTabla[] = [$nombreVista, 'OK ('.count($columnasDb).' cols)', 'Ninguna'];
            } else {
                $filasTabla[] = [$nombreVista, 'INCOMPLETA', implode(', ', $faltantes)];
                $todasCorrectas = false;
            }
        }

        $this->table(['Vista', 'Estado', 'Columnas Faltantes'], $filasTabla);

        if ($todasCorrectas) {
            $this->info('Todas las 20 vistas de contrato api_* están presentes y completas.');

            return Command::SUCCESS;
        }

        $this->error('Se detectaron vistas ausentes o con columnas faltantes.');

        return Command::FAILURE;
    }
}
