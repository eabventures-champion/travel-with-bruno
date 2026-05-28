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

    // Recursive folder copy helper for Hostinger split structure
    $sourcePublic = base_path('public');
    $destPublicHtml = base_path('../public_html');
    
    if (is_dir($sourcePublic)) {
        $copyCount = 0;
        
        $recurseCopy = function ($src, $dst) use (&$recurseCopy, &$copyCount, $sourcePublic) {
            if (!is_dir($src)) return;
            $dir = opendir($src);
            @mkdir($dst, 0755, true);
            while (false !== ($file = readdir($dir))) {
                if (($file != '.') && ($file != '..')) {
                    // Do not overwrite the main entry index.php in public_html root
                    if ($file === 'index.php' && $src === $sourcePublic) {
                        continue;
                    }
                    
                    $srcFile = $src . '/' . $file;
                    $dstFile = $dst . '/' . $file;
                    
                    if (is_dir($srcFile)) {
                        $recurseCopy($srcFile, $dstFile);
                    } else {
                        if (copy($srcFile, $dstFile)) {
                            $copyCount++;
                        }
                    }
                }
            }
            closedir($dir);
        };
        
        // Execute recursive sync from bruno-core/public to public_html
        $recurseCopy($sourcePublic, $destPublicHtml);
        $feedback[] = "Synced {$copyCount} public asset file(s) from bruno-core/public to public_html successfully!";
    } else {
        $feedback[] = 'Source public/ folder not found.';
    }

    return implode('<br>', $feedback);
});
