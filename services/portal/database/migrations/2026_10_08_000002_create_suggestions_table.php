<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta las migraciones creando la tabla de sugerencias ciudadanas.
     */
    public function up(): void
    {
        Schema::create('suggestions', function (Blueprint $table) {
            $table->id();
            $table->string('category', 50)->index();
            $table->text('content');
            $table->string('status', 20)->default('pending')->index();
            $table->text('operator_notes')->nullable();
            $table->string('ip_hash', 64)->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Revierte las migraciones eliminando la tabla de sugerencias.
     */
    public function down(): void
    {
        Schema::dropIfExists('suggestions');
    }
};
