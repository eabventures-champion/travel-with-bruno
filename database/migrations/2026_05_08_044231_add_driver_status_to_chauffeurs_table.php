<?php

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
        Schema::table('chauffeurs', function (Blueprint $table) {
            $table->boolean('is_online')->default(false)->after('status');
            $table->decimal('current_lat', 10, 8)->nullable()->after('is_online');
            $table->decimal('current_lng', 11, 8)->nullable()->after('current_lat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chauffeurs', function (Blueprint $table) {
            $table->dropColumn(['is_online', 'current_lat', 'current_lng']);
        });
    }
};
