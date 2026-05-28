<?php

use Illuminate\Support\Facades\Route;
use Modules\Booking\Http\Controllers\BookingController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->prefix('admin/bookings')->group(function () {
    Route::get('/', [BookingController::class, 'index'])->name('admin.bookings.index');
    Route::get('/{booking}', [BookingController::class, 'show'])->name('admin.bookings.show');
    
    Route::middleware(['role:Super Admin|Operations Admin'])->group(function () {
        Route::post('/bulk-assign-chauffeur', [BookingController::class, 'bulkAssignChauffeur'])->name('bookings.bulk-assign-chauffeur');
        Route::post('/bulk-assign-schedule', [BookingController::class, 'bulkAssignSchedule'])->name('bookings.bulk-assign-schedule');
        Route::post('/{booking}/split', [BookingController::class, 'splitBooking'])->name('bookings.split');
        Route::post('/{booking}/status', [BookingController::class, 'updateStatus'])->name('bookings.update-status');
        Route::post('/{booking}/cancel', [BookingController::class, 'cancelBooking'])->name('bookings.cancel');
        Route::post('/{booking}/reverse-cancellation', [BookingController::class, 'reverseCancellation'])->name('bookings.reverse-cancellation');
        Route::post('/{booking}/payment', [BookingController::class, 'updatePaymentStatus'])->name('bookings.update-payment');
        Route::post('/{booking}/assign-chauffeur', [BookingController::class, 'assignChauffeur'])->name('bookings.assign-chauffeur');
        Route::post('/{booking}/schedule-return', [BookingController::class, 'scheduleReturn'])->name('bookings.schedule-return');
        Route::post('/{booking}/update-guests', [BookingController::class, 'updateGuestCount'])->name('bookings.update-guests');
        Route::post('/merge-bookings', [BookingController::class, 'mergeBookings'])->name('bookings.merge');
        Route::post('/complaints/{complaint}/resolve', [BookingController::class, 'resolveComplaint'])->name('bookings.complaints.resolve');
        
        Route::middleware(['role:Super Admin'])->group(function () {
            Route::post('/change-requests/{changeRequest}/approve', [BookingController::class, 'approveChangeRequest'])->name('bookings.change-requests.approve');
            Route::post('/change-requests/{changeRequest}/reject', [BookingController::class, 'rejectChangeRequest'])->name('bookings.change-requests.reject');
            Route::delete('/{booking}', [BookingController::class, 'destroy'])->name('admin.bookings.destroy');
        });
    });

    Route::post('/complaints/{complaint}/message', [BookingController::class, 'addComplaintMessage'])->name('bookings.complaints.message');

    // Customer Actions
    Route::post('/{booking}/request-increase', [BookingController::class, 'requestGuestIncrease'])->name('bookings.request-increase');
    Route::post('/{booking}/complaint', [BookingController::class, 'submitComplaint'])->name('bookings.complaint');
    Route::post('/{booking}/rating', [BookingController::class, 'submitRating'])->name('bookings.rating');
    Route::post('/{booking}/customer-end', [BookingController::class, 'customerEndTrip'])->name('bookings.customer-end');
    Route::post('/{booking}/confirm-schedule', [BookingController::class, 'confirmSchedule'])->name('bookings.confirm-schedule');
    Route::post('/{booking}/confirm-return-schedule', [BookingController::class, 'confirmReturnSchedule'])->name('bookings.confirm-return-schedule');
});

// Frontend Booking Routes
Route::get('bookings/create', [BookingController::class, 'create'])->name('bookings.create');
Route::post('bookings/store', [BookingController::class, 'store'])->name('bookings.store');
Route::post('bookings/check-duplicate', [BookingController::class, 'checkDuplicate'])->name('bookings.check-duplicate');
Route::get('bookings/success', [BookingController::class, 'success'])->name('bookings.success');
