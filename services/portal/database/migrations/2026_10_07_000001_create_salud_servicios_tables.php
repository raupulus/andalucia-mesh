<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tabla de estado actual de cada servicio
        Schema::create('estado_servicio', function (Blueprint $table) {
            $table->string('servicio')->primary();
            $table->boolean('ok')->default(false);
            $table->integer('fallos_seguidos')->default(0);
            $table->integer('codigo')->nullable();
            $table->integer('latencia_ms')->nullable();
            $table->text('motivo')->nullable();
            $table->json('detalle')->nullable();
            $table->timestamp('comprobado_en')->nullable();
        });

        // 2. Historial de transiciones de estado (retención 90 días)
        Schema::create('estado_servicio_cambio', function (Blueprint $table) {
            $table->id();
            $table->string('servicio')->index();
            $table->boolean('ok');
            $table->text('motivo')->nullable();
            $table->timestamp('en')->index();
        });

        // 3. Latido de tareas programadas (portal-tareas)
        Schema::create('tareas_latido', function (Blueprint $table) {
            $table->string('tarea')->primary();
            $table->timestamp('ultima_ejecucion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tareas_latido');
        Schema::dropIfExists('estado_servicio_cambio');
        Schema::dropIfExists('estado_servicio');
    }
};
