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
        Schema::create('webhook_destinations', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre')->unique();
            $table->string('host');
            $table->text('url');
            $table->boolean('activo')->default(true);
            $table->string('motivo_baja')->nullable();
            $table->unsignedSmallInteger('fallos_seguidos')->default(0);
            $table->timestamp('ultimo_ok')->nullable();
            $table->unsignedInteger('pendientes')->default(0);
            $table->json('riesgos')->nullable();
            $table->json('tipos')->nullable();
            $table->json('provincias')->nullable();
            $table->json('nodos')->nullable();
            $table->text('secreto')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webhook_destinations');
    }
};
