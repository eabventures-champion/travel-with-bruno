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
        Schema::table('airport_transfers', function (Blueprint $table) {
            $table->foreignId('vehicle_id')->nullable()->after('airport_name')->constrained('vehicles')->onDelete('set null');
            $table->unsignedBigInteger('vehicle_type_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('airport_transfers', function (Blueprint $table) {
            $table->dropForeign(['vehicle_id']);
            $table->dropColumn('vehicle_id');
            $table->unsignedBigInteger('vehicle_type_id')->nullable(false)->change();
        });
    }
};
