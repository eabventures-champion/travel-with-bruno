<?php

use Illuminate\Support\Facades\Route;
use Modules\Driver\Http\Controllers\DriverController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::middleware(['auth', 'role:Driver|Chauffeur'])->prefix('driver')->group(function () {
    Route::get('/dashboard', [\Modules\Driver\Http\Controllers\DriverController::class, 'index'])->name('driver.dashboard');
    Route::get('/trips', [\Modules\Driver\Http\Controllers\DriverController::class, 'trips'])->name('driver.trips');
    Route::get('/earnings', [\Modules\Driver\Http\Controllers\DriverController::class, 'earnings'])->name('driver.earnings');
    Route::get('/schedule', [\Modules\Driver\Http\Controllers\DriverController::class, 'schedule'])->name('driver.schedule');
    Route::get('/profile', [\Modules\Driver\Http\Controllers\DriverController::class, 'profile'])->name('driver.profile');
    Route::get('/profile/edit', [\Modules\Driver\Http\Controllers\DriverController::class, 'profileEdit'])->name('driver.profile.edit');
    Route::put('/profile/edit', [\Modules\Driver\Http\Controllers\DriverController::class, 'profileUpdate'])->name('driver.profile.update');
    Route::get('/profile/documents', [\Modules\Driver\Http\Controllers\DriverController::class, 'documents'])->name('driver.profile.documents');
    Route::put('/profile/documents', [\Modules\Driver\Http\Controllers\DriverController::class, 'documentsUpdate'])->name('driver.profile.documents.update');
    Route::get('/profile/password', [\Modules\Driver\Http\Controllers\DriverController::class, 'password'])->name('driver.profile.password');
    Route::put('/profile/password', [\Modules\Driver\Http\Controllers\DriverController::class, 'passwordUpdate'])->name('driver.profile.password.update');
    Route::get('/resources', [\Modules\Driver\Http\Controllers\DriverController::class, 'resources'])->name('driver.resources');
    Route::post('/schedule/{booking}/respond', [\Modules\Driver\Http\Controllers\DriverController::class, 'respondToSchedule'])->name('driver.schedule.respond');
    Route::post('/schedule/{booking}/return-respond', [\Modules\Driver\Http\Controllers\DriverController::class, 'respondToReturnSchedule'])->name('driver.schedule.return-respond');

    // Trip Lifecycle Routes
    Route::post('/trips/{booking}/start', [\Modules\Driver\Http\Controllers\DriverController::class, 'startTrip'])->name('driver.trips.start');
    Route::post('/trips/{booking}/cycle/{cycle}', [\Modules\Driver\Http\Controllers\DriverController::class, 'completeCycle'])->name('driver.trips.cycle');
    Route::post('/trips/{booking}/end', [\Modules\Driver\Http\Controllers\DriverController::class, 'endTrip'])->name('driver.trips.end');
    Route::post('/trips/{booking}/report', [\Modules\Driver\Http\Controllers\DriverController::class, 'reportIssue'])->name('driver.trips.report');
    Route::post('/trips/{booking}/return-start', [\Modules\Driver\Http\Controllers\DriverController::class, 'startReturnTrip'])->name('driver.trips.return-start');
    Route::post('/trips/{booking}/return-end', [\Modules\Driver\Http\Controllers\DriverController::class, 'endReturnTrip'])->name('driver.trips.return-end');

    // SOS Emergency Route
    Route::post('/sos', [\Modules\Driver\Http\Controllers\DriverController::class, 'triggerSOS'])->name('driver.sos');

    // AJAX Routes
    Route::post('/status/toggle', [\Modules\Driver\Http\Controllers\DriverApiController::class, 'toggleStatus'])->name('driver.status.toggle');
});
