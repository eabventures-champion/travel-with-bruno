<?php

namespace Modules\Fleet\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Fleet\Models\AirportTransfer;
use Modules\Fleet\Models\VehicleType;
use Modules\Fleet\Models\Vehicle;
use Illuminate\Http\Request;

class AirportTransferController extends Controller
{
    public function index()
    {
        $transfers = AirportTransfer::with(['vehicle.vehicleType', 'vehicleType'])->latest()->paginate(10);
        return view('fleet::transfers.index', compact('transfers'));
    }

    public function create()
    {
        $types = VehicleType::where('is_active', true)->get();
        $vehicles = Vehicle::with('vehicleType')->where('status', '!=', 'inactive')->get();
        return view('fleet::transfers.create', compact('types', 'vehicles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'airport_name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'vehicle_id' => 'required|exists:vehicles,id',
            'vehicle_type_id' => 'nullable|exists:vehicle_types,id',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'transfer_type' => 'required|in:pickup,dropoff,both',
        ]);

        AirportTransfer::create($validated);

        return redirect()->route('admin.fleet.transfers.index')->with('success', 'Airport transfer service added successfully');
    }

    public function edit($id)
    {
        $transfer = AirportTransfer::findOrFail($id);
        $types = VehicleType::where('is_active', true)->get();
        $vehicles = Vehicle::with('vehicleType')->where('status', '!=', 'inactive')->get();
        return view('fleet::transfers.edit', compact('transfer', 'types', 'vehicles'));
    }

    public function update(Request $request, $id)
    {
        $transfer = AirportTransfer::findOrFail($id);
        
        $validated = $request->validate([
            'airport_name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'vehicle_id' => 'required|exists:vehicles,id',
            'vehicle_type_id' => 'nullable|exists:vehicle_types,id',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'transfer_type' => 'required|in:pickup,dropoff,both',
        ]);

        $transfer->update($validated);

        return redirect()->route('admin.fleet.transfers.index')->with('success', 'Airport transfer service updated');
    }

    public function destroy($id)
    {
        $transfer = AirportTransfer::findOrFail($id);
        $transfer->delete();

        return redirect()->route('admin.fleet.transfers.index')->with('success', 'Airport transfer service deleted');
    }
}
