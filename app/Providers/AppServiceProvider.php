<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        view()->composer(['layouts.main', 'welcome'], function ($view) {
            try {
                $nextTour = \Modules\Tourism\Models\TourismPackage::where('status', 'active')
                    ->where('package_type', 'scheduled')
                    ->where('departure_date', '>=', now()->toDateString())
                    ->with(['category', 'itineraries'])
                    ->orderBy('departure_date')
                    ->first();
                $view->with('nextTour', $nextTour);
            } catch (\Exception $e) {
                // Ignore database issues during migrations/console
            }
        });
    }
}
