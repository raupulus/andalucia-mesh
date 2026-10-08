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
        Schema::table('coordinated_routers', function (Blueprint $table) {
            $table->json('favorite_nodes')->nullable()->after('hw_model');
            $table->json('blocked_nodes')->nullable()->after('favorite_nodes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('coordinated_routers', function (Blueprint $table) {
            $table->dropColumn(['favorite_nodes', 'blocked_nodes']);
        });
    }
};
