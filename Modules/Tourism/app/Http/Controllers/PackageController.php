<?php

namespace Modules\Tourism\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Tourism\Models\TourismPackage;
use Modules\Tourism\Models\TourismCategory;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Storage;

class PackageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $packages = TourismPackage::with('category')->latest()->paginate(10);
        return view('tourism::packages.index', compact('packages'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = TourismCategory::where('is_active', true)->get();
        return view('tourism::packages.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:tourism_categories,id',
            'package_type' => 'required|in:fixed,scheduled',
            'title' => 'required|string|max:255',
            'slug' => 'required|string|unique:tourism_packages,slug',
            'price' => 'required|numeric',
            'duration' => 'required|string',
            'location' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'departure_date' => 'required_if:package_type,scheduled|nullable|date',
            'return_date' => 'required_if:package_type,scheduled|nullable|date|after_or_equal:departure_date',
            'max_guests' => 'required_if:package_type,scheduled|nullable|integer|min:1',
            'short_description' => 'nullable|string',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive,archived',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('tourism/packages', 'public');
        }

        TourismPackage::create($validated);

        return redirect()->route('admin.tourism.packages.index')->with('success', 'Package created successfully');
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $package = TourismPackage::with(['category', 'itineraries'])->findOrFail($id);
        return view('tourism::packages.show', compact('package'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $package = TourismPackage::findOrFail($id);
        $categories = TourismCategory::where('is_active', true)->get();
        return view('tourism::packages.edit', compact('package', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $package = TourismPackage::findOrFail($id);
        
        $validated = $request->validate([
            'category_id' => 'required|exists:tourism_categories,id',
            'package_type' => 'required|in:fixed,scheduled',
            'title' => 'required|string|max:255',
            'slug' => 'required|string|unique:tourism_packages,slug,' . $id,
            'price' => 'required|numeric',
            'duration' => 'required|string',
            'location' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'departure_date' => 'required_if:package_type,scheduled|nullable|date',
            'return_date' => 'required_if:package_type,scheduled|nullable|date|after_or_equal:departure_date',
            'max_guests' => 'required_if:package_type,scheduled|nullable|integer|min:1',
            'short_description' => 'nullable|string',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive,archived',
        ]);

        if ($request->hasFile('image')) {
            // Delete old image
            if ($package->image) {
                Storage::disk('public')->delete($package->image);
            }
            $validated['image'] = $request->file('image')->store('tourism/packages', 'public');
        }

        $originalDeparture = $package->departure_date;
        $originalReturn = $package->return_date;

        $package->update($validated);

        if ($package->package_type === 'scheduled') {
            $departureChanged = false;
            $newDeparture = $package->departure_date;
            if (!$originalDeparture && $newDeparture) {
                $departureChanged = true;
            } elseif ($originalDeparture && !$newDeparture) {
                $departureChanged = true;
            } elseif ($originalDeparture && $newDeparture && !$originalDeparture->equalTo($newDeparture)) {
                $departureChanged = true;
            }

            $returnChanged = false;
            $newReturn = $package->return_date;
            if (!$originalReturn && $newReturn) {
                $returnChanged = true;
            } elseif ($originalReturn && !$newReturn) {
                $returnChanged = true;
            } elseif ($originalReturn && $newReturn && !$originalReturn->equalTo($newReturn)) {
                $returnChanged = true;
            }

            if ($departureChanged || $returnChanged) {
                // Find all bookings for this package
                $package->items()->with('booking')->get()->each(function ($item) use ($package, $departureChanged, $returnChanged) {
                    $booking = $item->booking;
                    if ($booking) {
                        $updates = [];
                        $isAdjustment = $booking->scheduled_at !== null;

                        if ($departureChanged && $booking->scheduled_at && $package->departure_date) {
                            $time = $booking->scheduled_at->format('H:i:s');
                            $updates['scheduled_at'] = \Carbon\Carbon::parse($package->departure_date->format('Y-m-d') . ' ' . $time);
                            $updates['driver_schedule_status'] = 'pending';
                            $updates['customer_schedule_status'] = 'pending';
                        }
                        
                        if ($returnChanged && $booking->return_scheduled_at && $package->return_date) {
                            $time = $booking->return_scheduled_at->format('H:i:s');
                            $updates['return_scheduled_at'] = \Carbon\Carbon::parse($package->return_date->format('Y-m-d') . ' ' . $time);
                            $updates['return_driver_schedule_status'] = 'pending';
                            $updates['return_customer_schedule_status'] = 'pending';
                        }

                        if (!empty($updates)) {
                            $booking->update($updates);

                            // Notify Customer
                            if ($booking->user) {
                                try {
                                    $booking->user->notify(new \App\Notifications\TripScheduledNotification($booking, $isAdjustment));
                                } catch (\Exception $e) {
                                    \Log::error("Package date update: notification to customer failed: " . $e->getMessage());
                                }
                            }

                            // Notify Driver
                            if ($booking->chauffeur && $booking->chauffeur->user) {
                                try {
                                    $booking->chauffeur->user->notify(new \App\Notifications\TripScheduledNotification($booking, $isAdjustment));
                                } catch (\Exception $e) {
                                    \Log::error("Package date update: notification to driver failed: " . $e->getMessage());
                                }
                            }
                        }
                    }
                });
            }
        }

        return redirect()->route('admin.tourism.packages.index')->with('success', 'Package updated successfully');
    }

    /**
     * Clone the specified resource.
     */
    public function clone($id)
    {
        $package = TourismPackage::with('itineraries')->findOrFail($id);
        
        $newPackage = $package->replicate();
        $newPackage->title = $package->title . ' (Copy)';
        $newPackage->slug = $package->slug . '-copy-' . time();
        $newPackage->status = 'inactive';
        $newPackage->departure_date = null;
        $newPackage->return_date = null;
        $newPackage->saveQuietly();

        // Clone itineraries
        foreach ($package->itineraries as $itinerary) {
            $newItinerary = $itinerary->replicate();
            $newItinerary->package_id = $newPackage->id;
            $newItinerary->save();
        }

        return redirect()->route('admin.tourism.packages.edit', $newPackage->id)
            ->with('success', 'Package cloned successfully! Please set the new dates, adjust the title/slug, and set the status to active.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $package = TourismPackage::findOrFail($id);
        $package->delete();

        return redirect()->route('admin.tourism.packages.index')->with('success', 'Package deleted successfully');
    }
}
