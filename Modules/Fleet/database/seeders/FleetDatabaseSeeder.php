<?php

namespace Modules\Fleet\Database\Seeders;

use Illuminate\Database\Seeder;

class FleetDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Vehicle Types
        $types = [
            [
                'name' => 'Luxury SUV',
                'slug' => 'luxury-suv',
                'description' => 'Premium SUVs for maximum comfort and style.',
                'capacity' => 7,
                'base_hourly_rate' => 150.00,
                'base_daily_rate' => 1200.00,
                'is_active' => true,
            ],
            [
                'name' => 'Executive Sedan',
                'slug' => 'executive-sedan',
                'description' => 'Sleek sedans for business travel.',
                'capacity' => 4,
                'base_hourly_rate' => 100.00,
                'base_daily_rate' => 800.00,
                'is_active' => true,
            ],
            [
                'name' => 'Economy Compact',
                'slug' => 'economy-compact',
                'description' => 'Budget-friendly options for city travel.',
                'capacity' => 4,
                'base_hourly_rate' => 60.00,
                'base_daily_rate' => 450.00,
                'is_active' => true,
            ],
        ];

        foreach ($types as $typeData) {
            $type = \Modules\Fleet\Models\VehicleType::updateOrCreate(['slug' => $typeData['slug']], $typeData);

            // 2. Vehicles for each type
            if ($type->slug === 'luxury-suv') {
                \Modules\Fleet\Models\Vehicle::create([
                    'vehicle_type_id' => $type->id,
                    'make' => 'Toyota',
                    'model' => 'Land Cruiser V8',
                    'year' => '2023',
                    'license_plate' => 'GX-8844-23',
                    'color' => 'Black',
                    'status' => 'available',
                ]);
            } elseif ($type->slug === 'executive-sedan') {
                \Modules\Fleet\Models\Vehicle::create([
                    'vehicle_type_id' => $type->id,
                    'make' => 'Mercedes-Benz',
                    'model' => 'E-Class',
                    'year' => '2022',
                    'license_plate' => 'GT-1010-22',
                    'color' => 'Silver',
                    'status' => 'available',
                ]);
            } else {
                \Modules\Fleet\Models\Vehicle::create([
                    'vehicle_type_id' => $type->id,
                    'make' => 'Hyundai',
                    'model' => 'Elantra',
                    'year' => '2021',
                    'license_plate' => 'GW-5522-21',
                    'color' => 'White',
                    'status' => 'available',
                ]);
            }
        }

        // 3. Chauffeurs
        $chauffeursData = [
            [
                'name' => 'Kofi Mensah',
                'email' => 'kofi.mensah@example.com',
                'phone' => '0244111222',
                'license' => 'DL-99887766',
                'exp' => 10,
                'bio' => 'Expert driver with 10 years of experience in VIP transportation.',
            ],
            [
                'name' => 'Amaka Okafor',
                'email' => 'amaka.o@example.com',
                'phone' => '0200333444',
                'license' => 'DL-11223344',
                'exp' => 7,
                'bio' => 'Professional chauffeur specialized in airport transfers and city tours.',
            ],
            [
                'name' => 'Emmanuel Tetteh',
                'email' => 'emmanuel.t@example.com',
                'phone' => '0555666777',
                'license' => 'DL-55667788',
                'exp' => 12,
                'bio' => 'Highly experienced driver with deep knowledge of Ghanaian routes.',
            ],
        ];

        foreach ($chauffeursData as $data) {
            $user = \App\Models\User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'phone' => $data['phone'],
                    'password' => bcrypt('password'),
                    'user_type' => 'driver',
                    'status' => 'active',
                ]
            );

            // Assign driver role if it exists
            $role = \Spatie\Permission\Models\Role::where('name', 'Driver')->first();
            if ($role) {
                $user->assignRole($role);
            }

            \Modules\Fleet\Models\Chauffeur::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'license_number' => $data['license'],
                    'license_expiry' => now()->addYears(3),
                    'years_of_experience' => $data['exp'],
                    'bio' => $data['bio'],
                    'status' => 'available',
                ]
            );
        }
    }
}
