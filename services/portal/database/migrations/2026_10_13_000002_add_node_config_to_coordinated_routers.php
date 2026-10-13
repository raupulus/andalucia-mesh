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
            $table->json('node_config')->nullable()->after('blocked_nodes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('coordinated_routers', function (Blueprint $table) {
            $table->dropColumn(['node_config']);
        });
    }
};
