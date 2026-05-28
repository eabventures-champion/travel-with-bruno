<?php

namespace Modules\Tourism\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Tourism\Models\TourismGuestType;
use Illuminate\Http\Request;

class GuestTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $guestTypes = TourismGuestType::latest()->paginate(10);
        return view('tourism::guest-types.index', compact('guestTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $validated['status'] = $request->has('status') ? 'active' : 'inactive';

        TourismGuestType::create($validated);

        return redirect()->route('admin.tourism.guest-types.index')->with('success', 'Guest type created successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $guestType = TourismGuestType::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $guestType->update([
            'name' => $validated['name'],
            'status' => $request->has('status') ? 'active' : 'inactive',
        ]);

        return redirect()->route('admin.tourism.guest-types.index')->with('success', 'Guest type updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $guestType = TourismGuestType::findOrFail($id);
        $guestType->delete();

        return redirect()->route('admin.tourism.guest-types.index')->with('success', 'Guest type deleted successfully');
    }
}
