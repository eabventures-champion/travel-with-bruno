<?php

namespace Modules\Fleet\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Fleet\Models\VehicleType;
use Illuminate\Http\Request;

class VehicleTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $types = VehicleType::withCount('vehicles')->latest()->paginate(10);
        return view('fleet::types.index', compact('types'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:vehicle_types,slug',
            'capacity' => 'required|integer|min:1',
            'base_hourly_rate' => 'required|numeric|min:0',
            'base_daily_rate' => 'required|numeric|min:0',
        ]);

        VehicleType::create($validated);

        return redirect()->route('admin.fleet.types.index')->with('success', 'Vehicle type created successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $type = VehicleType::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:vehicle_types,slug,' . $id,
            'capacity' => 'required|integer|min:1',
            'base_hourly_rate' => 'required|numeric|min:0',
            'base_daily_rate' => 'required|numeric|min:0',
        ]);

        $type->update($validated);

        return redirect()->route('admin.fleet.types.index')->with('success', 'Vehicle type updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $type = VehicleType::findOrFail($id);
        $type->delete();

        return redirect()->route('admin.fleet.types.index')->with('success', 'Vehicle type deleted');
    }
}
