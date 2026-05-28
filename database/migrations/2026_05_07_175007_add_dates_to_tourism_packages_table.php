<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tourism_packages', function (Blueprint $table) {
            $table->date('departure_date')->nullable()->after('location');
            $table->date('return_date')->nullable()->after('departure_date');
            $table->integer('max_guests')->nullable()->after('return_date');
        });
    }

    public function down(): void
    {
        Schema::table('tourism_packages', function (Blueprint $table) {
            $table->dropColumn(['departure_date', 'return_date', 'max_guests']);
        });
    }
};
