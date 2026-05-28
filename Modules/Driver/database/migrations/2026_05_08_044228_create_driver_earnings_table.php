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
        Schema::create('driver_earnings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chauffeur_id')->constrained('chauffeurs')->onDelete('cascade');
            $table->foreignId('driver_trip_id')->nullable()->constrained('driver_trips')->onDelete('set null');
            $table->decimal('amount', 10, 2);
            $table->string('type')->default('trip'); // trip, bonus, tip, penalty
            $table->string('status')->default('pending'); // pending, paid
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_earnings');
    }
};
