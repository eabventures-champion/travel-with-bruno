<?php

namespace Modules\Tourism\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Tourism\Models\TourismPackage;
use Illuminate\Http\Request;

class TourismController extends Controller
{
    public function allDestinations()
    {
        $packages = TourismPackage::where('status', 'active')
            ->where('package_type', 'fixed')
            ->with(['category', 'itineraries'])
            ->latest()
            ->paginate(12);

        $guestTypes = \Modules\Tourism\Models\TourismGuestType::where('status', 'active')->get();
        $transferZones = \App\Models\TransferZone::where('is_active', true)->orderBy('name')->get();
            
        return view('tourism::destinations', compact('packages', 'guestTypes', 'transferZones'));
    }

    public function allGroupTours()
    {
        $packages = TourismPackage::where('status', 'active')
            ->where('package_type', 'scheduled')
            ->where('departure_date', '>=', now()->toDateString())
            ->with(['category', 'itineraries'])
            ->withSum(['items as guests_count' => function($query) {
                $query->whereHas('booking', function($q) {
                    $q->whereIn('status', ['confirmed', 'completed']);
                });
            }], 'quantity')
            ->orderBy('departure_date')
            ->paginate(12);

        $guestTypes = \Modules\Tourism\Models\TourismGuestType::where('status', 'active')->get();
        $transferZones = \App\Models\TransferZone::where('is_active', true)->orderBy('name')->get();
            
        return view('tourism::group-tours', compact('packages', 'guestTypes', 'transferZones'));
    }

    public function customerFixedTours()
    {
        $packages = TourismPackage::where('status', 'active')
            ->where('package_type', 'fixed')
            ->with(['category', 'itineraries'])
            ->latest()
            ->paginate(12);

        $guestTypes = \Modules\Tourism\Models\TourismGuestType::where('status', 'active')->get();
        $transferZones = \App\Models\TransferZone::where('is_active', true)->orderBy('name')->get();
            
        return view('tourism::customer.fixed-tours', compact('packages', 'guestTypes', 'transferZones'));
    }

    public function customerOrganizedTours()
    {
        $packages = TourismPackage::where('status', 'active')
            ->where('package_type', 'scheduled')
            ->where('departure_date', '>=', now()->toDateString())
            ->with(['category', 'itineraries'])
            ->withSum(['items as guests_count' => function($query) {
                $query->whereHas('booking', function($q) {
                    $q->whereIn('status', ['confirmed', 'completed']);
                });
            }], 'quantity')
            ->orderBy('departure_date')
            ->paginate(12);

        $guestTypes = \Modules\Tourism\Models\TourismGuestType::where('status', 'active')->get();
        $transferZones = \App\Models\TransferZone::where('is_active', true)->orderBy('name')->get();
            
        return view('tourism::customer.organized-tours', compact('packages', 'guestTypes', 'transferZones'));
    }
}
