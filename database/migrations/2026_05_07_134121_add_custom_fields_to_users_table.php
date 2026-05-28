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
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->string('user_type')->default('customer')->after('phone'); // customer, driver, guide, admin, etc.
            $table->string('status')->default('active')->after('user_type');
            $table->timestamp('last_login_at')->nullable()->after('status');
            $table->string('avatar_url')->nullable()->after('last_login_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'user_type', 'status', 'last_login_at', 'avatar_url']);
        });
    }
};
