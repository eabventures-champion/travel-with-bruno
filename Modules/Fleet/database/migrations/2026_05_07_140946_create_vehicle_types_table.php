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
        Schema::create('vehicle_types', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g. SUV, Luxury Sedan, Minibus
            $table->string('slug')->unique();
            $table->integer('capacity'); // number of passengers
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->decimal('base_hourly_rate', 10, 2)->default(0);
            $table->decimal('base_daily_rate', 10, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_types');
    }
};
