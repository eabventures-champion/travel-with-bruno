<?php

namespace Modules\Fleet\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Fleet\Models\Vehicle;
use Modules\Fleet\Models\VehicleType;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $vehicles = Vehicle::with('vehicleType')->latest()->paginate(10);
        return view('fleet::vehicles.index', compact('vehicles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $types = VehicleType::where('is_active', true)->get();
        $chauffeurs = \Modules\Fleet\Models\Chauffeur::with('user')->where('status', 'available')->get();
        return view('fleet::vehicles.create', compact('types', 'chauffeurs'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehicle_type_id' => 'required|exists:vehicle_types,id',
            'make' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'license_plate' => 'required|string|unique:vehicles,license_plate',
            'year' => 'nullable|string',
            'color' => 'nullable|string|max:100',
            'vin' => 'nullable|string|max:100',
            'transmission' => 'nullable|in:manual,automatic',
            'fuel_type' => 'nullable|in:petrol,diesel,electric,hybrid',
            'seats' => 'nullable|integer|min:1',
            'luggage_capacity' => 'nullable|integer|min:0',
            'status' => 'required|in:available,on_trip,maintenance,inactive',
            'chauffeur_id' => 'nullable|exists:chauffeurs,id|unique:vehicles,chauffeur_id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ], [
            'chauffeur_id.unique' => 'This chauffeur is already assigned to another vehicle.'
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('vehicles', 'public');
            $validated['image'] = $path;
        }

        $vehicle = Vehicle::create($validated);

        // Note: Assigning a vehicle does NOT change chauffeur status.
        // Chauffeur becomes 'engaged' only when they start a trip.

        return redirect()->route('admin.fleet.vehicles.index')->with('success', 'Vehicle added to fleet successfully');
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return redirect()->route('admin.fleet.vehicles.edit', $id);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $vehicle = Vehicle::findOrFail($id);
        $types = VehicleType::where('is_active', true)->get();
        $chauffeurs = \Modules\Fleet\Models\Chauffeur::with('user')->where('status', 'available')->get();
        return view('fleet::vehicles.edit', compact('vehicle', 'types', 'chauffeurs'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $vehicle = Vehicle::findOrFail($id);
        $oldChauffeurId = $vehicle->chauffeur_id;
        
        $validated = $request->validate([
            'vehicle_type_id' => 'required|exists:vehicle_types,id',
            'make' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'license_plate' => 'required|string|unique:vehicles,license_plate,' . $id,
            'year' => 'nullable|string',
            'color' => 'nullable|string|max:100',
            'vin' => 'nullable|string|max:100',
            'transmission' => 'nullable|in:manual,automatic',
            'fuel_type' => 'nullable|in:petrol,diesel,electric,hybrid',
            'seats' => 'nullable|integer|min:1',
            'luggage_capacity' => 'nullable|integer|min:0',
            'status' => 'required|in:available,on_trip,maintenance,inactive',
            'chauffeur_id' => 'nullable|exists:chauffeurs,id|unique:vehicles,chauffeur_id,' . $id,
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ], [
            'chauffeur_id.unique' => 'This chauffeur is already assigned to another vehicle.'
        ]);

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($vehicle->image && \Storage::disk('public')->exists($vehicle->image)) {
                \Storage::disk('public')->delete($vehicle->image);
            }
            $path = $request->file('image')->store('vehicles', 'public');
            $validated['image'] = $path;
        }

        $vehicle->update($validated);

        // Handle Chauffeur Status Changes
        if ($oldChauffeurId != $vehicle->chauffeur_id) {
            // Note: We only release the old chauffeur. New chauffeur status stays as-is.
            // Chauffeur becomes 'engaged' only when they start a trip.
            if ($oldChauffeurId) {
                \Modules\Fleet\Models\Chauffeur::where('id', $oldChauffeurId)->update(['status' => 'available']);
            }
        }

        return redirect()->route('admin.fleet.vehicles.index')->with('success', 'Vehicle updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $vehicle = Vehicle::findOrFail($id);
        $vehicle->delete();

        return redirect()->route('admin.fleet.vehicles.index')->with('success', 'Vehicle removed from fleet');
    }
}
