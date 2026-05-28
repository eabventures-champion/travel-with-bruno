<?php

use Illuminate\Support\Facades\Route;
use Modules\Fleet\Http\Controllers\VehicleController;
use Modules\Fleet\Http\Controllers\ChauffeurController;
use Modules\Fleet\Http\Controllers\VehicleTypeController;
use Modules\Fleet\Http\Controllers\AirportTransferController;
use Modules\Fleet\Http\Controllers\TransferZoneController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:Super Admin|Operations Admin'])->prefix('admin/fleet')->group(function () {
    Route::resource('vehicles', VehicleController::class)->names('admin.fleet.vehicles');
    Route::post('chauffeurs/check-license', [ChauffeurController::class, 'checkLicenseUniqueness'])->name('admin.fleet.chauffeurs.check-license');
    Route::resource('chauffeurs', ChauffeurController::class)->names('admin.fleet.chauffeurs');
    Route::resource('types', VehicleTypeController::class)->names('admin.fleet.types');
    Route::resource('transfers', AirportTransferController::class)->names('admin.fleet.transfers');
    Route::resource('zones', TransferZoneController::class)->names('admin.fleet.zones');
});

Route::middleware(['auth'])->prefix('customer/fleet')->group(function () {
    Route::get('/hiring', [\Modules\Fleet\Http\Controllers\CustomerFleetController::class, 'hiringServices'])->name('customer.fleet.hiring');
    Route::get('/transfers', [\Modules\Fleet\Http\Controllers\CustomerFleetController::class, 'transferServices'])->name('customer.fleet.transfers');
});
