<?php

declare(strict_types=1);

use App\Models\CoordinatedRouter;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Añade columnas de coordenadas GPS geográficas directas al modelo CoordinatedRouter
     * y purga registros residuales de ruido generados automáticamente por paquetes no coordinados.
     */
    public function up(): void
    {
        // 1. Añadir columnas de coordenadas a coordinated_routers si no existen
        Schema::table('coordinated_routers', function (Blueprint $table) {
            if (! Schema::hasColumn('coordinated_routers', 'latitude')) {
                $table->decimal('latitude', 10, 7)->nullable()->after('province');
            }
            if (! Schema::hasColumn('coordinated_routers', 'longitude')) {
                $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            }
            if (! Schema::hasColumn('coordinated_routers', 'altitude')) {
                $table->integer('altitude')->nullable()->after('longitude');
            }
        });

        // 2. Limpieza de registros espurios / ruido (ej: 'Router a9be2173', 'Router 849a2518')
        // Se eliminan registros que tengan nombres genéricos autogenerados y no sean gestionados oficialmente
        try {
            DB::table('coordinated_routers')
                ->where('status', '!=', CoordinatedRouter::STATUS_MANAGED)
                ->where(function ($query) {
                    $query->where('long_name', 'like', 'Router %')
                        ->orWhere('long_name', 'like', 'Meshtastic %')
                        ->orWhereNull('long_name')
                        ->orWhere('long_name', '');
                })
                ->delete();
        } catch (Throwable) {
            // Silencioso si ocurre error en la purga inicial
        }

        // 3. Sincronizar coordenadas desde la base de ingesta para routers existentes
        try {
            $ingestaSchema = Schema::connection('ingesta');
            $sourceTable = null;
            if ($ingestaSchema->hasView('api_nodes') || $ingestaSchema->hasTable('api_nodes')) {
                $sourceTable = 'api_nodes';
            } elseif ($ingestaSchema->hasView('api_routers') || $ingestaSchema->hasTable('api_routers')) {
                $sourceTable = 'api_routers';
            }

            if ($sourceTable) {
                $routers = DB::table('coordinated_routers')->whereNull('latitude')->get();
                foreach ($routers as $r) {
                    $node = DB::connection('ingesta')
                        ->table($sourceTable)
                        ->where('id', $r->node_id)
                        ->select(['latitude', 'longitude'])
                        ->first();

                    if ($node && $node->latitude !== null && $node->longitude !== null) {
                        DB::table('coordinated_routers')
                            ->where('id', $r->id)
                            ->update([
                                'latitude' => $node->latitude,
                                'longitude' => $node->longitude,
                            ]);
                    }
                }
            }
        } catch (Throwable) {
            // Silencioso si la conexión de ingesta no está disponible en este momento
        }
    }

    /**
     * Revierte los cambios de la migración.
     */
    public function down(): void
    {
        Schema::table('coordinated_routers', function (Blueprint $table) {
            $columnsToDrop = [];
            if (Schema::hasColumn('coordinated_routers', 'altitude')) {
                $columnsToDrop[] = 'altitude';
            }
            if (Schema::hasColumn('coordinated_routers', 'longitude')) {
                $columnsToDrop[] = 'longitude';
            }
            if (Schema::hasColumn('coordinated_routers', 'latitude')) {
                $columnsToDrop[] = 'latitude';
            }
            if (! empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
