<?php

use Illuminate\Support\Facades\Route;
use Modules\Tourism\Http\Controllers\PackageController;
use Modules\Tourism\Http\Controllers\CategoryController;
use Modules\Tourism\Http\Controllers\ItineraryController;
use Modules\Tourism\Http\Controllers\TourismController;
use Modules\Tourism\Http\Controllers\GuestTypeController;

Route::get('/destinations', [TourismController::class, 'allDestinations'])->name('tourism.destinations');
Route::get('/group-tours', [TourismController::class, 'allGroupTours'])->name('tourism.group-tours');
Route::post('/tour-interest', [\Modules\Tourism\Http\Controllers\TourInterestController::class, 'store'])->name('tourism.tour-interest.store');
Route::post('/tour-interest/check-duplicate', [\Modules\Tourism\Http\Controllers\TourInterestController::class, 'checkDuplicate'])->name('tourism.tour-interest.check-duplicate');
Route::get('/tour-interest/{token}/book', [\Modules\Tourism\Http\Controllers\TourInterestController::class, 'specialBooking'])->name('tourism.special-booking');

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:Super Admin|Operations Admin'])->prefix('admin/tourism')->group(function () {
    Route::resource('packages', PackageController::class)->names('admin.tourism.packages');
    Route::post('packages/{package}/clone', [PackageController::class, 'clone'])->name('admin.tourism.packages.clone');
    Route::get('packages/{package}/gallery', [\Modules\Tourism\Http\Controllers\PackageGalleryController::class, 'adminGallery'])->name('admin.tourism.packages.gallery');
    Route::resource('packages.itineraries', ItineraryController::class)->names('admin.tourism.packages.itineraries');
    Route::resource('categories', CategoryController::class)->names('admin.tourism.categories');
    Route::resource('guest-types', GuestTypeController::class)->names('admin.tourism.guest-types');
    Route::get('interests', [\Modules\Tourism\Http\Controllers\TourInterestController::class, 'index'])->name('admin.tourism.interests');
});

Route::middleware(['auth'])->prefix('customer/tourism')->group(function () {
    Route::get('/fixed', [TourismController::class, 'customerFixedTours'])->name('customer.tourism.fixed');
    Route::get('/organized', [TourismController::class, 'customerOrganizedTours'])->name('customer.tourism.organized');
});

// Tour Gallery Routes
Route::get('/gallery', [\Modules\Tourism\Http\Controllers\PackageGalleryController::class, 'publicGallery'])->name('tourism.gallery');
Route::get('/tourism/packages/{package}/gallery', [\Modules\Tourism\Http\Controllers\PackageGalleryController::class, 'index'])->name('tourism.packages.gallery.index');
Route::post('/tourism/packages/{package}/gallery', [\Modules\Tourism\Http\Controllers\PackageGalleryController::class, 'store'])->name('tourism.packages.gallery.store')->middleware('auth');
Route::delete('/tourism/packages/{package}/gallery/{image}', [\Modules\Tourism\Http\Controllers\PackageGalleryController::class, 'destroy'])->name('tourism.packages.gallery.destroy')->middleware('auth');
