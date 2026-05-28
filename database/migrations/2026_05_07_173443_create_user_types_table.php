<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed default types
        DB::table('user_types')->insert([
            ['name' => 'Administrator', 'slug' => 'admin', 'description' => 'Full system access', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Staff', 'slug' => 'staff', 'description' => 'Internal staff member', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Agent', 'slug' => 'agent', 'description' => 'External agent or partner', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Driver', 'slug' => 'driver', 'description' => 'Vehicle operator', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Vendor', 'slug' => 'vendor', 'description' => 'Service provider', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Customer', 'slug' => 'customer', 'description' => 'End user / client', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('user_types');
    }
};
