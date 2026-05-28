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
            $table->string('driver_schedule_status')->default('pending')->after('scheduled_at'); // pending, accepted, declined
            $table->text('driver_schedule_feedback')->nullable()->after('driver_schedule_status');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['driver_schedule_status', 'driver_schedule_feedback']);
        });
    }
};
