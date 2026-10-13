<?php

declare(strict_types=1);

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $ingestaDriver = DB::connection('ingesta')->getDriverName();
        if ($ingestaDriver === 'sqlite') {
            // 1. Asegurar tabla api_nodes con todas sus columnas en SQLite
            if (! Schema::connection('ingesta')->hasTable('api_nodes')) {
                Schema::connection('ingesta')->create('api_nodes', function (Blueprint $table) {
                    $table->string('id', 32)->primary();
                    $table->string('short_name', 32)->nullable();
                    $table->string('long_name', 128)->nullable();
                    $table->string('role', 32)->default('CLIENT');
                    $table->string('hw_model', 64)->nullable();
                    $table->string('firmware', 32)->nullable();
                    $table->string('province', 10)->nullable();
                    $table->decimal('latitude', 10, 7)->nullable();
                    $table->decimal('longitude', 10, 7)->nullable();
                    $table->timestamp('last_position_at')->nullable();
                    $table->integer('position_precision_m')->nullable();
                    $table->boolean('border_uncertain')->default(false);
                    $table->integer('hop_start_last')->nullable();
                    $table->integer('hops_min_last')->nullable();
                    $table->boolean('is_gateway')->default(false);
                    $table->boolean('is_router')->default(false);
                    $table->timestamp('first_seen')->nullable();
                    $table->timestamp('last_seen')->nullable();
                    $table->timestamp('nodeinfo_at')->nullable();
                });
            } else {
                $schema = Schema::connection('ingesta');
                $cols = [
                    'latitude' => fn ($t) => $t->decimal('latitude', 10, 7)->nullable(),
                    'longitude' => fn ($t) => $t->decimal('longitude', 10, 7)->nullable(),
                    'last_position_at' => fn ($t) => $t->timestamp('last_position_at')->nullable(),
                    'position_precision_m' => fn ($t) => $t->integer('position_precision_m')->nullable(),
                    'border_uncertain' => fn ($t) => $t->boolean('border_uncertain')->default(false),
                    'hops_min_last' => fn ($t) => $t->integer('hops_min_last')->nullable(),
                    'first_seen' => fn ($t) => $t->timestamp('first_seen')->nullable(),
                    'nodeinfo_at' => fn ($t) => $t->timestamp('nodeinfo_at')->nullable(),
                ];
                foreach ($cols as $colName => $colDef) {
                    if (! $schema->hasColumn('api_nodes', $colName)) {
                        $schema->table('api_nodes', function (Blueprint $table) use ($colDef) {
                            $colDef($table);
                        });
                    }
                }
            }

            // 2. Asegurar tablas auxiliares de diagnóstico
            if (! Schema::connection('ingesta')->hasTable('api_node_intervals')) {
                Schema::connection('ingesta')->create('api_node_intervals', function (Blueprint $table) {
                    $table->string('node_id', 32);
                    $table->string('portnum', 32);
                    $table->string('variant', 32)->nullable();
                    $table->float('median_interval_s')->nullable();
                });
            }

            if (! Schema::connection('ingesta')->hasTable('api_node_battery_daily')) {
                Schema::connection('ingesta')->create('api_node_battery_daily', function (Blueprint $table) {
                    $table->string('node_id', 32);
                    $table->date('day');
                    $table->integer('min_level')->nullable();
                });
            }

            if (! Schema::connection('ingesta')->hasTable('api_node_reboots_daily')) {
                Schema::connection('ingesta')->create('api_node_reboots_daily', function (Blueprint $table) {
                    $table->string('node_id', 32);
                    $table->date('day');
                    $table->integer('reboots')->default(0);
                });
            }

            // 3. Sincronizar todos los routers de api_routers en api_nodes con telemetría de diagnóstico simulada
            if (Schema::connection('ingesta')->hasTable('api_routers')) {
                $routers = DB::connection('ingesta')->table('api_routers')->get();
                $now = Carbon::now();

                foreach ($routers as $r) {
                    $exists = DB::connection('ingesta')->table('api_nodes')->where('id', $r->id)->exists();
                    if (! $exists) {
                        DB::connection('ingesta')->table('api_nodes')->insert([
                            'id' => $r->id,
                            'short_name' => $r->short_name,
                            'long_name' => $r->long_name,
                            'role' => $r->role ?? 'ROUTER',
                            'hw_model' => $r->hw_model ?? 'HELTEC_V3',
                            'firmware' => '2.5.6',
                            'province' => $r->province,
                            'latitude' => $r->latitude ?? null,
                            'longitude' => $r->longitude ?? null,
                            'hop_start_last' => 3,
                            'hops_min_last' => 1,
                            'is_gateway' => (bool) ($r->is_gateway ?? false),
                            'is_router' => true,
                            'last_seen' => $r->last_seen ?? $now->toDateTimeString(),
                            'first_seen' => $r->last_seen ?? $now->toDateTimeString(),
                            'nodeinfo_at' => $r->last_seen ?? $now->toDateTimeString(),
                        ]);
                    }

                    // Datos de telemetría e intervalos de diagnóstico
                    $hasIntervals = DB::connection('ingesta')->table('api_node_intervals')->where('node_id', $r->id)->exists();
                    if (! $hasIntervals) {
                        DB::connection('ingesta')->table('api_node_intervals')->insert([
                            [
                                'node_id' => $r->id,
                                'portnum' => 'nodeinfo',
                                'variant' => null,
                                'median_interval_s' => 10800.0,
                            ],
                            [
                                'node_id' => $r->id,
                                'portnum' => 'telemetry',
                                'variant' => 'device_metrics',
                                'median_interval_s' => 1800.0,
                            ],
                        ]);
                    }

                    $hasBattery = DB::connection('ingesta')->table('api_node_battery_daily')->where('node_id', $r->id)->exists();
                    if (! $hasBattery) {
                        for ($i = 6; $i >= 0; $i--) {
                            DB::connection('ingesta')->table('api_node_battery_daily')->insert([
                                'node_id' => $r->id,
                                'day' => $now->copy()->subDays($i)->toDateString(),
                                'min_level' => 82,
                            ]);
                        }
                    }

                    $hasReboots = DB::connection('ingesta')->table('api_node_reboots_daily')->where('node_id', $r->id)->exists();
                    if (! $hasReboots) {
                        for ($i = 6; $i >= 0; $i--) {
                            DB::connection('ingesta')->table('api_node_reboots_daily')->insert([
                                'node_id' => $r->id,
                                'day' => $now->copy()->subDays($i)->toDateString(),
                                'reboots' => 0,
                            ]);
                        }
                    }
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $ingestaDriver = DB::connection('ingesta')->getDriverName();
        if ($ingestaDriver === 'sqlite') {
            Schema::connection('ingesta')->dropIfExists('api_node_reboots_daily');
            Schema::connection('ingesta')->dropIfExists('api_node_battery_daily');
            Schema::connection('ingesta')->dropIfExists('api_node_intervals');
            Schema::connection('ingesta')->dropIfExists('api_nodes');
        }
    }
};
