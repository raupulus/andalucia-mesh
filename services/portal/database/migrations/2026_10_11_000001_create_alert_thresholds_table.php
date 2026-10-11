<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración para la tabla alert_thresholds.
 * Almacena los umbrales dinámicos de alertas y diagnósticos organizados por grupo de roles.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('alert_thresholds', function (Blueprint $table): void {
            $table->id();
            $table->string('role_group', 32);
            $table->string('parameter', 64);
            $table->double('value_numeric');
            $table->string('unit', 16);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['role_group', 'parameter'], 'uq_role_parameter');
            $table->index('role_group', 'idx_alert_thresholds_group');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alert_thresholds');
    }
};
