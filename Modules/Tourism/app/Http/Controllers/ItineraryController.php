<?php

namespace Modules\Tourism\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Tourism\Models\TourismPackage;
use Modules\Tourism\Models\PackageItinerary;
use Illuminate\Http\Request;

class ItineraryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($packageId)
    {
        $package = TourismPackage::with('itineraries')->findOrFail($packageId);
        return view('tourism::itineraries.index', compact('package'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, $packageId)
    {
        $package = TourismPackage::findOrFail($packageId);

        $validated = $request->validate([
            'day_number' => 'required|integer',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $package->itineraries()->create($validated);

        return redirect()->route('admin.tourism.packages.itineraries.index', $packageId)
            ->with('success', 'Itinerary day added successfully');
    }

    public function update(Request $request, $packageId, $id)
    {
        $itinerary = PackageItinerary::where('package_id', $packageId)->findOrFail($id);

        $validated = $request->validate([
            'day_number' => 'required|integer',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $itinerary->update($validated);

        return redirect()->route('admin.tourism.packages.itineraries.index', $packageId)
            ->with('success', 'Itinerary day updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($packageId, $id)
    {
        $itinerary = PackageItinerary::where('package_id', $packageId)->findOrFail($id);
        $itinerary->delete();

        return redirect()->route('admin.tourism.packages.itineraries.index', $packageId)
            ->with('success', 'Itinerary day removed successfully');
    }
}
