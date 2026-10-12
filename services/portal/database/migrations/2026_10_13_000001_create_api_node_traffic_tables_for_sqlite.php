<?php

declare(strict_types=1);

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
            if (! Schema::connection('ingesta')->hasTable('api_node_traffic_day')) {
                Schema::connection('ingesta')->create('api_node_traffic_day', function (Blueprint $table) {
                    $table->timestamp('bucket');
                    $table->string('from_id', 32);
                    $table->string('province', 10)->nullable();
                    $table->string('portnum', 32);
                    $table->integer('packets')->default(1);
                    $table->float('airtime_s')->default(0.0);
                });
            }

            if (! Schema::connection('ingesta')->hasTable('api_node_traffic_hour')) {
                Schema::connection('ingesta')->create('api_node_traffic_hour', function (Blueprint $table) {
                    $table->timestamp('bucket');
                    $table->string('from_id', 32);
                    $table->string('province', 10)->nullable();
                    $table->string('portnum', 32);
                    $table->integer('packets')->default(1);
                    $table->float('airtime_s')->default(0.0);
                });
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
            Schema::connection('ingesta')->dropIfExists('api_node_traffic_hour');
            Schema::connection('ingesta')->dropIfExists('api_node_traffic_day');
        }
    }
};
