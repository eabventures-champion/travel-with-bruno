<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('trip_leg')->default('outbound')->after('customer_schedule_status');
            $table->string('return_trip_status')->default('idle')->after('trip_leg');
            $table->timestamp('return_started_at')->nullable()->after('return_trip_status');
            $table->timestamp('return_ended_at')->nullable()->after('return_started_at');
            $table->string('return_duration')->nullable()->after('return_ended_at');
            $table->string('return_end_code')->nullable()->after('return_duration');
            $table->string('return_driver_schedule_status')->default('pending')->after('return_end_code');
            $table->string('return_customer_schedule_status')->default('pending')->after('return_driver_schedule_status');
            $table->timestamp('return_scheduled_at')->nullable()->after('return_customer_schedule_status');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'trip_leg',
                'return_trip_status',
                'return_started_at',
                'return_ended_at',
                'return_duration',
                'return_end_code',
                'return_driver_schedule_status',
                'return_customer_schedule_status',
                'return_scheduled_at',
            ]);
        });
    }
};
