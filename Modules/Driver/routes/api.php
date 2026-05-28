<?php

use Illuminate\Support\Facades\Route;
use Modules\Driver\Http\Controllers\DriverController;

/*
 *--------------------------------------------------------------------------
 * API Routes
 *--------------------------------------------------------------------------
 *
 * Here is where you can register API routes for your application. These
 * routes are loaded by the RouteServiceProvider within a group which
 * is assigned the "api" middleware group. Enjoy building your API!
 *
*/

Route::middleware(['auth'])->prefix('v1/driver')->group(function () {
    Route::post('/status/toggle', [\Modules\Driver\Http\Controllers\DriverApiController::class, 'toggleStatus']);
    Route::post('/location/sync', [\Modules\Driver\Http\Controllers\DriverApiController::class, 'syncLocation']);
    Route::post('/trips/{trip}/status', [\Modules\Driver\Http\Controllers\DriverApiController::class, 'updateTripStatus']);
});
