<?php

use Illuminate\Support\Facades\Route;

use Modules\CMS\Models\HomepageSlide;
use Modules\CMS\Models\HomepageSetting;

Route::get('/', function () {
    $slides = HomepageSlide::where('is_active', true)->orderBy('order')->get();
    $settings = HomepageSetting::pluck('value', 'key')->toArray();
    $fixedPackages = \Modules\Tourism\Models\TourismPackage::where('status', 'active')->where('package_type', 'fixed')->with(['category', 'itineraries'])->latest()->take(4)->get();
    $scheduledPackages = \Modules\Tourism\Models\TourismPackage::where('status', 'active')->where('package_type', 'scheduled')->where('departure_date', '>=', now()->toDateString())->with(['category', 'itineraries'])->orderBy('departure_date')->get();
    $vehicles = \Modules\Fleet\Models\Vehicle::with(['vehicleType', 'chauffeur.user'])->latest()->take(4)->get();
    $transfers = \Modules\Fleet\Models\AirportTransfer::where('is_active', true)->where('category', 'airport')->with('vehicleType')->latest()->take(4)->get();
    $guestTypes = \Modules\Tourism\Models\TourismGuestType::where('status', 'active')->get();
    $transferZones = \App\Models\TransferZone::where('is_active', true)->orderBy('name')->get();
    $ongoingTour = \Modules\Tourism\Models\TourismPackage::ongoing()->where('status', 'active')->with(['category', 'itineraries'])->latest()->first();
    return view('welcome', compact('slides', 'settings', 'fixedPackages', 'scheduledPackages', 'vehicles', 'transfers', 'guestTypes', 'transferZones', 'ongoingTour'));
});

Route::get('/car-hiring', function () {
    $vehicles = \Modules\Fleet\Models\Vehicle::with(['vehicleType', 'chauffeur.user'])->latest()->get();
    $transferZones = \App\Models\TransferZone::where('is_active', true)->orderBy('name')->get();
    $guestTypes = \Modules\Tourism\Models\TourismGuestType::where('status', 'active')->get();
    return view('car-hiring', compact('vehicles', 'transferZones', 'guestTypes'));
})->name('car-hiring');

Route::get('/transfer-services', function () {
    $transfers = \Modules\Fleet\Models\AirportTransfer::where('is_active', true)->with('vehicleType')->latest()->get();
    $transferZones = \App\Models\TransferZone::where('is_active', true)->orderBy('name')->get();
    $guestTypes = \Modules\Tourism\Models\TourismGuestType::where('status', 'active')->get();
    return view('transfer-services', compact('transfers', 'transferZones', 'guestTypes'));
})->name('transfer-services');

Route::middleware(['auth'])->group(function () {
    Route::get('/chat', [App\Http\Controllers\ChatController::class, 'index'])->name('chat.index');
    Route::get('/chat/{receiver}', [App\Http\Controllers\ChatController::class, 'show'])->name('chat.show');
    Route::post('/chat', [App\Http\Controllers\ChatController::class, 'store'])->name('chat.store');
    Route::post('/bookings/{booking}/schedule', [Modules\Booking\Http\Controllers\BookingController::class, 'schedule'])->name('bookings.schedule');

    // Booking Documents
    Route::post('/bookings/{booking}/documents', [\Modules\Booking\Http\Controllers\BookingDocumentController::class, 'store'])->name('bookings.documents.store');
    Route::get('/booking-documents/{document}/download', [\Modules\Booking\Http\Controllers\BookingDocumentController::class, 'download'])->name('bookings.documents.download');
    Route::delete('/booking-documents/{document}', [\Modules\Booking\Http\Controllers\BookingDocumentController::class, 'destroy'])->name('bookings.documents.destroy');
});

Route::get('/clear-cache', function () {
    $feedback = [];
    
    // Clear Laravel caches
    \Illuminate\Support\Facades\Artisan::call('view:clear');
    $feedback[] = 'Laravel view cache cleared successfully!';
    
    \Illuminate\Support\Facades\Artisan::call('cache:clear');
    $feedback[] = 'Laravel application cache cleared successfully!';
    
    \Illuminate\Support\Facades\Artisan::call('config:clear');
    $feedback[] = 'Laravel config cache cleared successfully!';
    
    \Illuminate\Support\Facades\Artisan::call('route:clear');
    $feedback[] = 'Laravel route cache cleared successfully!';

    // File copy helper for "without .htaccess" Hostinger structure
    $sourceCss = base_path('public/assets/css/main.css');
    
    // Target 1: same directory assets (e.g. public_html is base path)
    $destCss1 = base_path('assets/css/main.css');
    // Target 2: parent directory public_html (split structure)
    $destCss2 = base_path('../public_html/assets/css/main.css');
    
    if (file_exists($sourceCss)) {
        // Copy to Target 1
        $destDir1 = dirname($destCss1);
        if (!is_dir($destDir1)) {
            mkdir($destDir1, 0755, true);
        }
        if (copy($sourceCss, $destCss1)) {
            $feedback[] = 'Copied main.css to base assets folder successfully!';
        }
        
        // Copy to Target 2 (for split bruno-core and public_html structure)
        $destDir2 = dirname($destCss2);
        if (is_dir(dirname($destDir2))) { // Check if the parent of the destination directory exists
            if (!is_dir($destDir2)) {
                mkdir($destDir2, 0755, true);
            }
            if (copy($sourceCss, $destCss2)) {
                $feedback[] = 'Copied main.css to public_html assets folder successfully!';
            }
        }
    } else {
        $feedback[] = 'Source main.css not found in public folder.';
    }

    return implode('<br>', $feedback);
});
