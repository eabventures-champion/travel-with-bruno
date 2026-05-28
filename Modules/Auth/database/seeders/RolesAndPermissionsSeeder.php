<?php

namespace Modules\Auth\Database\Seeders;

use Illuminate\Database\Seeder;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            'manage users',
            'manage roles',
            'view analytics',
            'manage tourism',
            'manage fleet',
            'manage bookings',
            'manage payments',
            'manage vendors',
            'manage chauffeur',
            'manage airport transfers',
            'view dashboard',
            'make bookings',
            'view own profile',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions
        
        // Super Admin
        $superAdmin = Role::create(['name' => 'Super Admin']);
        // Super Admin gets all permissions via a gate (usually) but we can assign all here too
        $superAdmin->givePermissionTo(Permission::all());

        // Operations Admin
        $opsAdmin = Role::create(['name' => 'Operations Admin']);
        $opsAdmin->givePermissionTo([
            'view dashboard',
            'manage tourism',
            'manage fleet',
            'manage bookings',
            'manage chauffeur',
            'manage airport transfers',
            'view analytics',
        ]);

        // Customer
        $customer = Role::create(['name' => 'Customer']);
        $customer->givePermissionTo([
            'make bookings',
            'view own profile',
        ]);

        // Driver/Chauffeur
        $driver = Role::create(['name' => 'Driver/Chauffeur']);
        $driver->givePermissionTo([
            'view dashboard',
            'view own profile',
        ]);

        // Tour Guide
        $guide = Role::create(['name' => 'Tour Guide']);
        $guide->givePermissionTo([
            'view dashboard',
            'view own profile',
        ]);

        // Vendor/Partner
        $vendor = Role::create(['name' => 'Vendor/Partner']);
        $vendor->givePermissionTo([
            'view dashboard',
            'manage bookings',
            'view own profile',
        ]);

        // Corporate Account
        $corporate = Role::create(['name' => 'Corporate Account']);
        $corporate->givePermissionTo([
            'make bookings',
            'view own profile',
        ]);
    }
}
