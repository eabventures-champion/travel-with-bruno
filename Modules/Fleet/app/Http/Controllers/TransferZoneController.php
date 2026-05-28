<?php

namespace Modules\Fleet\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Models\TransferZone;

class TransferZoneController extends Controller
{
    public function index()
    {
        $zones = TransferZone::latest()->paginate(10);
        return view('fleet::zones.index', compact('zones'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'additional_price' => 'required|numeric|min:0',
        ]);

        TransferZone::create($validated);

        return redirect()->back()->with('success', 'Zone added successfully');
    }

    public function update(Request $request, $id)
    {
        $zone = TransferZone::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'additional_price' => 'required|numeric|min:0',
        ]);

        $zone->update($validated);

        return redirect()->back()->with('success', 'Zone updated');
    }

    public function destroy($id)
    {
        $zone = TransferZone::findOrFail($id);
        $zone->delete();

        return redirect()->back()->with('success', 'Zone deleted');
    }
}
