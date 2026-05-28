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
        Schema::table('vehicles', function (Blueprint $table) {
            $table->string('transmission')->nullable()->after('vin'); // manual, automatic
            $table->string('fuel_type')->nullable()->after('transmission'); // petrol, diesel, electric, hybrid
            $table->integer('seats')->nullable()->after('fuel_type');
            $table->integer('luggage_capacity')->nullable()->after('seats');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn(['transmission', 'fuel_type', 'seats', 'luggage_capacity']);
        });
    }
};
