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
        Schema::table('bookings', function (Blueprint $table) {
            $table->timestamp('trip_started_at')->nullable();
            $table->timestamp('trip_ended_at')->nullable();
            $table->timestamp('cycle_1_completed_at')->nullable();
            $table->timestamp('cycle_2_completed_at')->nullable();
            $table->string('trip_duration')->nullable();
            $table->string('trip_status')->default('idle'); // idle, in_progress, completed
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['trip_started_at', 'trip_ended_at', 'cycle_1_completed_at', 'cycle_2_completed_at', 'trip_duration', 'trip_status']);
        });
    }
};
