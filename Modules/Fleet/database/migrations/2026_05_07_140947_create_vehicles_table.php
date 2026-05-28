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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_type_id')->constrained('vehicle_types')->onDelete('cascade');
            $table->string('make'); // e.g. Toyota
            $table->string('model'); // e.g. Land Cruiser
            $table->string('year')->nullable();
            $table->string('license_plate')->unique();
            $table->string('color')->nullable();
            $table->string('vin')->nullable(); // Vehicle Identification Number
            $table->string('image')->nullable();
            $table->json('features')->nullable(); // e.g. ["AC", "WiFi", "Leather Seats"]
            $table->string('status')->default('available'); // available, on_trip, maintenance, inactive
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
