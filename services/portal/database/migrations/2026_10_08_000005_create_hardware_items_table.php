<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta las migraciones creando la tabla de artículos y componentes de hardware.
     */
    public function up(): void
    {
        Schema::create('hardware_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('hardware_categories')->cascadeOnDelete();
            $table->string('slug')->unique();
            $table->jsonb('name');
            $table->jsonb('description');
            $table->string('image_path');
            $table->string('buy_url');
            $table->string('guide_url')->nullable();
            $table->decimal('last_price', 8, 2)->nullable();
            $table->string('currency', 3)->default('EUR');
            $table->boolean('is_featured')->default(false)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->integer('sort_order')->default(0)->index();
            $table->timestamps();
        });
    }

    /**
     * Revierte las migraciones eliminando la tabla de artículos de hardware.
     */
    public function down(): void
    {
        Schema::dropIfExists('hardware_items');
    }
};
