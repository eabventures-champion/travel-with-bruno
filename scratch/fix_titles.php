<?php

$files = [
    'Modules/Fleet/resources/views/vehicles/index.blade.php' => 'Vehicle Fleet',
    'Modules/Fleet/resources/views/vehicles/create.blade.php' => 'Add New Vehicle',
    'Modules/Fleet/resources/views/vehicles/edit.blade.php' => 'Edit Vehicle',
    'Modules/Fleet/resources/views/types/index.blade.php' => 'Vehicle Types',
    'Modules/Fleet/resources/views/transfers/index.blade.php' => 'Transfer Services',
    'Modules/Fleet/resources/views/zones/index.blade.php' => 'Pricing Zones',
    'Modules/Fleet/resources/views/chauffeurs/index.blade.php' => 'Chauffeurs',
    'Modules/Booking/resources/views/admin/index.blade.php' => 'All Bookings',
    'Modules/Admin/resources/views/users/index.blade.php' => 'User Management',
    'Modules/Admin/resources/views/user-types/index.blade.php' => 'User Types',
    'Modules/Admin/resources/views/settings/index.blade.php' => 'System Settings',
    'Modules/Admin/resources/views/customers/index.blade.php' => 'Customer Directory',
    'Modules/Admin/resources/views/chauffeur-management/index.blade.php' => 'Chauffeur Directory',
    'Modules/Admin/resources/views/documents/index.blade.php' => 'Document Manager',
    'Modules/CMS/resources/views/slides/index.blade.php' => 'Homepage Slides',
    'Modules/CMS/resources/views/content/index.blade.php' => 'Homepage Content',
    'Modules/Tourism/resources/views/packages/index.blade.php' => 'Tourism Packages',
    'Modules/Tourism/resources/views/categories/index.blade.php' => 'Package Categories',
    'Modules/Tourism/resources/views/guest-types/index.blade.php' => 'Customer Types',
];

foreach ($files as $path => $title) {
    $fullPath = "c:/laragon/www/bruno/" . $path;
    if (file_exists($fullPath)) {
        $content = file_get_contents($fullPath);
        if (strpos($content, "@section('title'") === false) {
            $content = str_replace("@extends('admin::layouts.master')", "@extends('admin::layouts.master')\n@section('title', '$title')", $content);
            file_put_contents($fullPath, $content);
            echo "Fixed $path\n";
        } else {
            echo "Skipped $path (already has title)\n";
        }
    } else {
        echo "Missing $path\n";
    }
}
