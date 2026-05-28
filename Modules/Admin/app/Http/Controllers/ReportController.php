<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Booking\Models\Booking;
use Modules\Fleet\Models\Vehicle;
use Modules\Fleet\Models\Chauffeur;
use Modules\Tourism\Models\TourismPackage;
use Illuminate\Support\Facades\DB;
use App\Models\SystemSetting;

class ReportController extends Controller
{
    public function index()
    {
        // Define bookable types
        $tourismType = 'Modules\Tourism\Models\TourismPackage';
        $fleetType = 'Modules\Fleet\Models\Vehicle';

        // Tourism Stats
        $tourismBookings = Booking::whereHas('items', function($q) use ($tourismType) {
            $q->where('bookable_type', $tourismType);
        });

        // Car Hire Stats
        $carHireBookings = Booking::whereHas('items', function($q) use ($fleetType) {
            $q->where('bookable_type', $fleetType);
        });

        $stats = [
            'tourism_bookings' => $tourismBookings->count(),
            'tourism_revenue' => $tourismBookings->where('status', 'confirmed')->sum('total_amount'),
            'car_hire_bookings' => $carHireBookings->count(),
            'car_hire_revenue' => $carHireBookings->where('status', 'confirmed')->sum('total_amount'),
            'active_vehicles' => Vehicle::where('status', 'available')->count(),
            'total_chauffeurs' => Chauffeur::count(),
            'total_packages' => TourismPackage::count(),
            'currency_symbol' => SystemSetting::where('key', 'default_currency')->value('value') == 'USD' ? '$' : '₵',
        ];

        // Recent Bookings Trend (Last 7 days)
        $bookingTrend = Booking::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get();
        
        // Latest Bookings
        $latestBookings = Booking::with(['user', 'items.bookable'])->latest()->take(5)->get();

        return view('admin::reports.index', compact('stats', 'bookingTrend', 'latestBookings'));
    }
}
