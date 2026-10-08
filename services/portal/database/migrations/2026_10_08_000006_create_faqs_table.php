<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta las migraciones creando la tabla de preguntas frecuentes (FAQ) con soporte multidioma.
     */
    public function up(): void
    {
        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->jsonb('question');
            $table->jsonb('answer');
            $table->boolean('is_active')->default(true)->index();
            $table->integer('sort_order')->default(0)->index();
            $table->timestamps();
        });
    }

    /**
     * Revierte las migraciones eliminando la tabla de preguntas frecuentes.
     */
    public function down(): void
    {
        Schema::dropIfExists('faqs');
    }
};
