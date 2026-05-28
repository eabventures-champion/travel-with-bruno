<?php

namespace Modules\Auth\Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DefaultUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@travelwithbruno.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('Admin@123'),
                'phone' => '+233000000000',
                'user_type' => 'admin',
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        $admin->assignRole('Super Admin');

        $this->command->info('Default Admin User Created: admin@travelwithbruno.com / Admin@123');
    }
}
