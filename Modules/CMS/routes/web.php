<?php

use Illuminate\Support\Facades\Route;
use Modules\CMS\Http\Controllers\HomepageSlideController;
use Modules\CMS\Http\Controllers\HomepageContentController;

Route::group(['middleware' => ['auth', 'role:Super Admin|Operations Admin'], 'prefix' => 'admin'], function () {
    Route::resource('slides', HomepageSlideController::class)->names([
        'index' => 'admin.slides.index',
        'create' => 'admin.slides.create',
        'store' => 'admin.slides.store',
        'edit' => 'admin.slides.edit',
        'update' => 'admin.slides.update',
        'destroy' => 'admin.slides.destroy',
    ]);
    Route::post('slides/{slide}/toggle', [HomepageSlideController::class, 'toggleStatus'])->name('admin.slides.toggle');

    Route::get('homepage-content', [HomepageContentController::class, 'index'])->name('admin.homepage.content');
    Route::post('homepage-content', [HomepageContentController::class, 'update'])->name('admin.homepage.content.update');
});
