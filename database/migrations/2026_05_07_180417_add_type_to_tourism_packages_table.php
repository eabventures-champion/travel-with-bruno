<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tourism_packages', function (Blueprint $table) {
            $table->string('package_type')->default('fixed')->after('category_id'); // fixed, scheduled
        });
    }

    public function down(): void
    {
        Schema::table('tourism_packages', function (Blueprint $table) {
            $table->dropColumn('package_type');
        });
    }
};
