<?php

use Illuminate\Support\Facades\Route;
use Modules\Admin\Http\Controllers\AdminController;
use Modules\Admin\Http\Controllers\UserController;
use Modules\Admin\Http\Controllers\UserTypeController;
use Modules\Admin\Http\Controllers\SystemSettingController;
use Modules\Admin\Http\Controllers\ReportController;
use Modules\Admin\Http\Controllers\CustomerManagementController;
use Modules\Admin\Http\Controllers\ChauffeurManagementController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->prefix('admin')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('admin.dashboard');
    Route::get('/profile', [AdminController::class, 'profile'])->name('admin.profile');
    Route::put('/profile', [AdminController::class, 'profileUpdate'])->name('admin.profile.update');
    Route::get('/notifications/{id}/read', [AdminController::class, 'markNotificationAsRead'])->name('admin.notifications.read');
    Route::post('/notifications/read-all', [AdminController::class, 'markAllNotificationsAsRead'])->name('admin.notifications.read-all');
    Route::post('/notifications/clear-all', [AdminController::class, 'clearAllNotifications'])->name('admin.notifications.clear-all');
    
    // Restricted Management Routes
    Route::middleware(['role:Super Admin|Operations Admin'])->group(function() {
        // Reports
        Route::get('/reports', [ReportController::class, 'index'])->name('admin.reports.index');

        // Settings
        Route::get('/settings', [SystemSettingController::class, 'index'])->name('admin.settings.index');
        Route::post('/settings', [SystemSettingController::class, 'update'])->name('admin.settings.update');

        // Users
        Route::post('users/check-uniqueness', [UserController::class, 'checkUniqueness'])->name('admin.users.check-uniqueness');
        Route::resource('users', UserController::class)->names('admin.users');

        // User Types
        Route::get('user-types', [UserTypeController::class, 'index'])->name('admin.user-types.index');
        Route::post('user-types', [UserTypeController::class, 'store'])->name('admin.user-types.store');
        Route::put('user-types/{id}', [UserTypeController::class, 'update'])->name('admin.user-types.update');
        Route::post('user-types/{id}/toggle', [UserTypeController::class, 'toggleStatus'])->name('admin.user-types.toggle');
        Route::delete('user-types/{id}', [UserTypeController::class, 'destroy'])->name('admin.user-types.destroy');

        // Customer Management
        Route::get('/customers', [CustomerManagementController::class, 'index'])->name('admin.customers.index');
        Route::get('/customers/{id}', [CustomerManagementController::class, 'show'])->name('admin.customers.show');

        // Chauffeur Management
        Route::get('/chauffeur-management', [ChauffeurManagementController::class, 'index'])->name('admin.chauffeur-management.index');
        Route::get('/chauffeur-management/{id}', [ChauffeurManagementController::class, 'show'])->name('admin.chauffeur-management.show');
        Route::post('/chauffeur-management/{id}/verify-document', [ChauffeurManagementController::class, 'verifyDocument'])->name('admin.chauffeur-management.verify-document');

        // Document Broadcasting
        Route::get('/documents/broadcast', [\Modules\Admin\Http\Controllers\DocumentBroadcastController::class, 'index'])->name('admin.documents.broadcast');
        Route::post('/documents/broadcast', [\Modules\Admin\Http\Controllers\DocumentBroadcastController::class, 'store'])->name('admin.documents.broadcast.store');
        Route::delete('/documents/broadcast/{document}', [\Modules\Admin\Http\Controllers\DocumentBroadcastController::class, 'destroy'])->name('admin.documents.broadcast.destroy');
        Route::get('/documents/broadcast/{document}/download', [\Modules\Admin\Http\Controllers\DocumentBroadcastController::class, 'download'])->name('admin.documents.broadcast.download');
    });
});
