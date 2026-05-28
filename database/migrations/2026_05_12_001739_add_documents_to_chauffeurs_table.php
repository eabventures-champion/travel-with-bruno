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
            $table->string('license_front_path')->nullable();
            $table->string('id_card_path')->nullable();
            $table->timestamp('license_verified_at')->nullable();
            $table->timestamp('id_verified_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chauffeurs', function (Blueprint $table) {
            //
        });
    }
};
