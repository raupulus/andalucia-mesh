<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migración para incorporar el estado de gobernanza (managed, known, new)
 * en la tabla coordinated_routers y asegurar la compatibilidad con SQLite en local.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Añadir columna status a la tabla coordinated_routers
        Schema::table('coordinated_routers', function (Blueprint $table) {
            if (! Schema::hasColumn('coordinated_routers', 'status')) {
                $table->string('status', 20)->default('new')->index()->after('role');
            }
        });

        // 2. Migrar registros existentes: los aprobados pasan a 'managed', el resto a 'new'
        if (Schema::hasColumn('coordinated_routers', 'approved')) {
            DB::table('coordinated_routers')
                ->where('approved', true)
                ->update(['status' => 'managed']);

            DB::table('coordinated_routers')
                ->where('approved', false)
                ->update(['status' => 'new']);
        }

        // 3. Si la conexión de ingesta o por defecto utiliza SQLite (entorno local / pruebas),
        // asegurar que la tabla api_routers existe en SQLite para permitir sincronización sin errores.
        $ingestaDriver = DB::connection('ingesta')->getDriverName();
        if ($ingestaDriver === 'sqlite') {
            if (! Schema::connection('ingesta')->hasTable('api_routers')) {
                Schema::connection('ingesta')->create('api_routers', function (Blueprint $table) {
                    $table->string('id', 32)->primary();
                    $table->string('short_name', 32)->nullable();
                    $table->string('long_name', 128)->nullable();
                    $table->string('role', 32)->default('ROUTER');
                    $table->string('province', 10)->nullable();
                    $table->integer('battery_level')->nullable();
                    $table->decimal('voltage', 5, 2)->nullable();
                    $table->timestamp('battery_at')->nullable();
                    $table->decimal('channel_utilization', 5, 2)->nullable();
                    $table->decimal('air_util_tx', 5, 2)->nullable();
                    $table->timestamp('metrics_at')->nullable();
                    $table->timestamp('last_seen')->nullable();
                    $table->string('hw_model', 64)->nullable();
                    $table->boolean('powered')->default(false);
                    $table->boolean('is_gateway')->default(false);
                    $table->timestamp('last_reboot_at')->nullable();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('coordinated_routers', function (Blueprint $table) {
            if (Schema::hasColumn('coordinated_routers', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
