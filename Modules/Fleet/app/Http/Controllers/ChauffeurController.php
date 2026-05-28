<?php

namespace Modules\Fleet\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Fleet\Models\Chauffeur;
use App\Models\User;
use Illuminate\Http\Request;
use Modules\Booking\Models\Booking;

class ChauffeurController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $chauffeurs = Chauffeur::with(['user', 'bookings' => function($query) {
            // Include active legs and tourism packages not yet fully completed
            $query->whereNotIn('status', ['completed', 'cancelled']);
        }])
        ->orderByRaw("CASE status 
            WHEN 'engaged' THEN 1 
            WHEN 'schedule_accepted' THEN 2 
            WHEN 'available' THEN 3 
            ELSE 4 
        END ASC")
        ->latest()
        ->paginate(10);

        $chauffeurs->getCollection()->transform(function ($chauffeur) {
            // Determine if chauffeur has an active trip (outbound or return)
            $hasActiveTrip = $chauffeur->bookings->filter(function($b) {
                return $b->trip_status === 'in_progress' || $b->return_trip_status === 'in_progress';
            })->first();

            // Also check for tourism bookings that are in the "waiting for return" gap
            $isCommittedTourism = $chauffeur->bookings->filter(function($b) {
                return $b->isTourismBooking() && 
                       $b->trip_status === 'completed' && 
                       $b->return_trip_status !== 'completed' && 
                       $b->trip_leg === 'return';
            })->first();

            // If suspended or off_duty, don't auto-change
            if (in_array($chauffeur->status, ['suspended', 'off_duty'])) {
                return $chauffeur;
            }

            $newStatus = 'available';
            if ($hasActiveTrip || $isCommittedTourism) {
                $newStatus = 'engaged';
            } else {
                $hasAcceptedSchedule = $chauffeur->bookings->filter(function($b) {
                    return ($b->trip_status === 'idle' && $b->driver_schedule_status === 'accepted') ||
                           ($b->return_trip_status === 'idle' && $b->return_driver_schedule_status === 'accepted');
                })->first();

                if ($hasAcceptedSchedule) {
                    $newStatus = 'schedule_accepted';
                }
            }

            if ($chauffeur->getRawOriginal('status') !== $newStatus) {
                $chauffeur->status = $newStatus;
                $chauffeur->save();
            }

            return $chauffeur;
        });

        return view('fleet::chauffeurs.index', compact('chauffeurs'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $users = User::where('user_type', 'driver')->whereDoesntHave('chauffeurProfile')->get();
        return view('fleet::chauffeurs.create', compact('users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'license_number' => 'required|unique:chauffeurs,license_number',
            'license_expiry' => 'required|date',
            'years_of_experience' => 'required|integer|min:0',
        ]);

        Chauffeur::create([
            'user_id' => $request->user_id,
            'license_number' => $request->license_number,
            'license_expiry' => $request->license_expiry,
            'years_of_experience' => $request->years_of_experience,
            'status' => 'available'
        ]);

        $user = User::find($request->user_id);
        if ($user && !$user->hasRole('Driver')) {
            $user->assignRole('Driver');
        }

        return redirect()->route('admin.fleet.chauffeurs.index')->with('success', 'Chauffeur added successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $chauffeur = Chauffeur::with('user')->findOrFail($id);
        return view('fleet::chauffeurs.edit', compact('chauffeur'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $chauffeur = Chauffeur::findOrFail($id);
        
        $request->validate([
            'license_number' => 'required|unique:chauffeurs,license_number,' . $chauffeur->id,
            'license_expiry' => 'required|date',
            'years_of_experience' => 'required|integer|min:0',
            'status' => 'required|in:available,engaged,schedule_accepted,suspended,off_duty',
        ]);

        $chauffeur->update($request->all());

        return redirect()->route('admin.fleet.chauffeurs.index')->with('success', 'Chauffeur updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $chauffeur = Chauffeur::findOrFail($id);
        $chauffeur->delete();

        return redirect()->route('admin.fleet.chauffeurs.index')->with('success', 'Chauffeur removed successfully.');
    }

    public function checkLicenseUniqueness(Request $request)
    {
        $license = $request->license_number;
        $chauffeurId = $request->chauffeur_id;

        $query = Chauffeur::where('license_number', $license);
        if ($chauffeurId) {
            $query->where('id', '!=', $chauffeurId);
        }

        $exists = $query->exists();

        return response()->json([
            'exists' => $exists,
            'message' => $exists ? "This license number is already registered to another chauffeur." : ""
        ]);
    }
}
