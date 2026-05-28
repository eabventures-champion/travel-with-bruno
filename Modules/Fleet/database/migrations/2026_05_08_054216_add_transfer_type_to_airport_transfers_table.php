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
            $table->enum('transfer_type', ['pickup', 'dropoff', 'both'])->default('both')->after('location');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('airport_transfers', function (Blueprint $table) {
            $table->dropColumn('transfer_type');
        });
    }
};
