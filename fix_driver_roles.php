<?php

use Spatie\Permission\Models\Role;
use App\Models\User;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

Role::firstOrCreate(['name' => 'Driver']);
User::where('user_type', 'driver')->get()->each(function($user) {
    if (!$user->hasRole('Driver')) {
        $user->assignRole('Driver');
    }
});
echo "Done\n";
