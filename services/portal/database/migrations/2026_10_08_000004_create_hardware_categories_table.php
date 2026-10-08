<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta las migraciones creando la tabla de categorías de hardware.
     */
    public function up(): void
    {
        Schema::create('hardware_categories', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->jsonb('name');
            $table->jsonb('description')->nullable();
            $table->integer('sort_order')->default(0)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    /**
     * Revierte las migraciones eliminando la tabla de categorías de hardware.
     */
    public function down(): void
    {
        Schema::dropIfExists('hardware_categories');
    }
};
