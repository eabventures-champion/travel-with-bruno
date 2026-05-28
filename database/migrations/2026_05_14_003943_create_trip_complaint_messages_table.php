<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('trip_complaint_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_complaint_id')->constrained('trip_complaints')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->text('message');
            $table->string('image_path')->nullable();
            $table->timestamps();
        });

        // Data Migration: Move existing messages and admin responses to the new table
        $complaints = DB::table('trip_complaints')->get();
        foreach ($complaints as $complaint) {
            // Original message from customer
            DB::table('trip_complaint_messages')->insert([
                'trip_complaint_id' => $complaint->id,
                'user_id' => $complaint->user_id,
                'message' => $complaint->message,
                'image_path' => $complaint->image_path,
                'created_at' => $complaint->created_at,
                'updated_at' => $complaint->created_at,
            ]);

            // Admin response if it exists
            if ($complaint->admin_response) {
                // Find a super admin or operations admin to attribute this to, 
                // or just use the first admin found if necessary.
                // For now, we'll try to find an admin.
                $admin = DB::table('users')
                    ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
                    ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                    ->whereIn('roles.name', ['Super Admin', 'Operations Admin'])
                    ->select('users.id')
                    ->first();

                DB::table('trip_complaint_messages')->insert([
                    'trip_complaint_id' => $complaint->id,
                    'user_id' => $admin ? $admin->id : $complaint->user_id, // Fallback if no admin found (unlikely)
                    'message' => $complaint->admin_response,
                    'created_at' => $complaint->resolved_at ?? $complaint->updated_at,
                    'updated_at' => $complaint->resolved_at ?? $complaint->updated_at,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trip_complaint_messages');
    }
};
