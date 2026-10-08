<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta las migraciones creando la tabla de páginas dinámicas del portal.
     */
    public function up(): void
    {
        if (! Schema::hasTable('custom_pages')) {
            Schema::create('custom_pages', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('slug')->unique()->index();
                $table->text('description');
                $table->longText('content');
                $table->jsonb('keywords')->nullable();
                $table->string('featured_image')->nullable();
                $table->boolean('is_active')->default(true)->index();
                $table->timestamps();
            });
        }
    }

    /**
     * Revierte las migraciones eliminando la tabla de páginas dinámicas.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_pages');
    }
};
