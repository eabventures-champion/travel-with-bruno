<?php

namespace Modules\Fleet\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Fleet\Models\Vehicle;
use Modules\Fleet\Models\VehicleType;
use Modules\Tourism\Models\TourismGuestType;
use Illuminate\Http\Request;

class CustomerFleetController extends Controller
{
    public function hiringServices()
    {
        $vehicles = Vehicle::where('status', 'available')->with(['vehicleType', 'chauffeur.user'])->latest()->get();
        $guestTypes = TourismGuestType::where('status', 'active')->get();
        $transferZones = \App\Models\TransferZone::where('is_active', true)->orderBy('name')->get();
        return view('fleet::customer.hiring', compact('vehicles', 'guestTypes', 'transferZones'));
    }

    public function transferServices()
    {
        $transfers = \Modules\Fleet\Models\AirportTransfer::where('is_active', true)->with('vehicleType')->latest()->get();
        $transferZones = \App\Models\TransferZone::where('is_active', true)->orderBy('name')->get();
        $guestTypes = TourismGuestType::where('status', 'active')->get();
        return view('fleet::customer.transfers', compact('transfers', 'transferZones', 'guestTypes'));
    }
}
