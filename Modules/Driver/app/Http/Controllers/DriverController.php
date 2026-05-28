<?php

namespace Modules\Driver\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Booking\Models\Booking;
use Modules\Driver\Models\DriverEarning;

class DriverController extends Controller
{
    /**
     * Display earnings and wallet.
     */
    public function earnings()
    {
        $chauffeur = auth()->user()->chauffeurProfile;
        if (!$chauffeur) return redirect()->route('driver.dashboard')->with('error', 'Chauffeur profile not found.');

        $transactions = DriverEarning::where('chauffeur_id', $chauffeur->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $balance = $transactions->where('status', 'pending')->sum(function($t) {
            return $t->transaction_type === 'credit' ? $t->amount : -$t->amount;
        });

        $weekEarnings = $transactions->where('created_at', '>=', now()->startOfWeek())->sum(function($t) {
            return $t->transaction_type === 'credit' ? $t->amount : -$t->amount;
        });

        $monthEarnings = $transactions->where('created_at', '>=', now()->startOfMonth())->sum(function($t) {
            return $t->transaction_type === 'credit' ? $t->amount : -$t->amount;
        });

        return view('driver::earnings', compact('chauffeur', 'balance', 'weekEarnings', 'monthEarnings', 'transactions'));
    }

    /**
     * Sync chauffeur status based on current bookings.
     */
    private function syncChauffeurStatus($chauffeur)
    {
        if (!$chauffeur) return;
        \Modules\Fleet\Models\Chauffeur::syncStatus($chauffeur->id);
    }

    /**
     * Group unified bookings for the driver display.
     */
    private function groupBookings($bookings)
    {
        return $bookings->groupBy(function($b) {
            if ($b->isTourismBooking()) {
                $packageId = $b->items->first()?->bookable_id ?? 0;
                $date = $b->scheduled_at ? $b->scheduled_at->format('Y-m-d') : 'no-date';
                return "tour_{$packageId}_{$date}";
            }
            return "bkg_{$b->id}";
        })->map(function($group) {
            $first = $group->first();
            if ($group->count() > 1) {
                $first->total_amount = $group->sum('total_amount');
                $first->group_count = $group->count();
                if (!isset($first->original_customer_name)) {
                    $first->original_customer_name = $first->customer_name;
                }
                
                $first->grouped_customers = $group->map(function($b) {
                    return [
                        'name' => $b->original_customer_name ?? $b->customer_name ?? 'Guest',
                        'phone' => $b->customer_phone ?? 'N/A',
                        'ref' => $b->booking_reference
                    ];
                })->toArray();

                $first->customer_name = $first->original_customer_name . " + " . ($group->count() - 1) . " others";
            } else {
                $first->group_count = 1;
                $first->grouped_customers = [[
                    'name' => $first->original_customer_name ?? $first->customer_name ?? 'Guest',
                    'phone' => $first->customer_phone ?? 'N/A',
                    'ref' => $first->booking_reference
                ]];
            }
            return $first;
        })->values();
    }

    private function getSiblingBookings(Booking $booking)
    {
        $siblings = collect([$booking]);
        if ($booking->isTourismBooking()) {
            $packageId = $booking->items->first()?->bookable_id;
            $date = $booking->scheduled_at ? $booking->scheduled_at->format('Y-m-d') : null;
            if ($packageId && $date) {
                $siblings = Booking::where('chauffeur_id', $booking->chauffeur_id)
                    ->whereHas('items', function($q) use ($packageId) {
                        $q->where('bookable_type', 'Modules\Tourism\Models\TourismPackage')
                          ->where('bookable_id', $packageId);
                    })
                    ->whereDate('scheduled_at', $date)
                    ->where('status', '!=', 'cancelled')
                    ->get();
            }
        }
        return $siblings;
    }

    /**
     * Display the overview dashboard.
     */
    public function index()
    {
        $chauffeur = auth()->user()->chauffeurProfile;
        
        $activeTripsCount = 0;
        $completedTripsCount = 0;
        $earnedToday = 0;
        $currentTrip = null;
        
        if ($chauffeur) {
            // Perform robust status sync
            $this->syncChauffeurStatus($chauffeur);

            $bookings = Booking::where('chauffeur_id', $chauffeur->id)
                ->with(['items.bookable', 'reports'])
                ->orderBy('created_at', 'desc')
                ->get();
                
            $activeTripsCount = $this->groupBookings($bookings->filter(function($b) {
                if (in_array($b->status, ['pending', 'confirmed', 'in_progress'])) {
                    if ($b->trip_status !== 'completed') return true;
                    if ($b->trip_status === 'completed' && in_array($b->return_trip_status, ['idle', 'pending', 'in_progress'])) return true;
                }
                return false;
            }))->count();
            
            $completedTripsCount = $this->groupBookings($bookings->filter(function($b) {
                if ($b->isTourismBooking()) {
                    return $b->trip_status === 'completed' && $b->return_trip_status === 'completed';
                }
                return $b->trip_status === 'completed';
            }))->count();
            
            // Calculate today's earnings
            $earnedToday = DriverEarning::where('chauffeur_id', $chauffeur->id)
                ->where('created_at', '>=', now()->startOfDay())
                ->get()
                ->sum(function($t) {
                    return $t->transaction_type === 'credit' ? $t->amount : -$t->amount;
                });
            
            // Prioritize in_progress trip
            $currentTrip = $bookings->where('trip_status', 'in_progress')->first() 
                          ?? $bookings->where('return_trip_status', 'in_progress')->first();

            if (!$currentTrip) {
                $currentTrip = $bookings->filter(function($b) {
                    if ($b->status === 'confirmed' && $b->trip_status === 'idle') return true;
                    if ($b->trip_status === 'completed' && in_array($b->return_trip_status, ['idle', 'pending'])) return true;
                    if ($b->status === 'pending') return true;
                    return false;
                })->sortBy(function($b) {
                    if ($b->trip_status === 'completed' && in_array($b->return_trip_status, ['idle', 'pending'])) {
                        return $b->return_scheduled_at ? $b->return_scheduled_at->timestamp : PHP_INT_MAX;
                    }
                    return $b->scheduled_at ? $b->scheduled_at->timestamp : PHP_INT_MAX;
                })->first();
            }

            if ($currentTrip) {
                // Replace with unified trip
                $currentTrip = $this->groupBookings(
                    $bookings->filter(function($b) use ($currentTrip) {
                        if (!$b->isTourismBooking() || !$currentTrip->isTourismBooking()) return $b->id === $currentTrip->id;
                        $packageId = $b->items->first()?->bookable_id;
                        $cPackageId = $currentTrip->items->first()?->bookable_id;
                        $date = $b->scheduled_at ? $b->scheduled_at->format('Y-m-d') : null;
                        $cDate = $currentTrip->scheduled_at ? $currentTrip->scheduled_at->format('Y-m-d') : null;
                        return $packageId === $cPackageId && $date === $cDate;
                    })
                )->first();
            }
        }

        return view('driver::index', compact('chauffeur', 'activeTripsCount', 'completedTripsCount', 'earnedToday', 'currentTrip'));
    }

    public function startTrip(Booking $booking)
    {
        if ($booking->chauffeur_id !== auth()->user()->chauffeurProfile->id) {
            abort(403);
        }

        if ($booking->driver_schedule_status !== 'accepted' || $booking->customer_schedule_status !== 'accepted') {
            return back()->with('error', 'Both you and the customer must accept the schedule.');
        }

        if ($booking->scheduled_at && now()->addMinutes(30)->lt($booking->scheduled_at)) {
            return back()->with('error', 'You cannot start this trip yet.');
        }

        $tripEndCode = strtoupper(\Illuminate\Support\Str::random(6));

        $siblings = $this->getSiblingBookings($booking);

        // AUTO-CANCELLATION LOGIC FOR UNPAID GUESTS
        // If this trip is part of an organized tour, cancel all other UNPAID bookings for this package/date
        if ($booking->isTourismBooking()) {
            $packageItem = $booking->items->where('bookable_type', 'Modules\Tourism\Models\TourismPackage')->first();
            if ($packageItem && ($packageItem->bookable->package_type ?? '') === 'scheduled') {
                $packageId = $packageItem->bookable_id;
                
                $departureDate = $packageItem->bookable->departure_date ?? null;
                $compareDate = $booking->scheduled_at ? $booking->scheduled_at->toDateString() : ($departureDate ? $departureDate->toDateString() : null);

                // Find all bookings for this same package on the same scheduled date that are NOT fully paid
                $query = Booking::whereHas('items', function($q) use ($packageId) {
                        $q->where('bookable_type', 'Modules\Tourism\Models\TourismPackage')
                          ->where('bookable_id', $packageId);
                    })
                    ->where('id', '!=', $booking->id)
                    ->whereIn('payment_status', ['pending', 'partially_paid'])
                    ->where('status', '!=', 'cancelled');
                
                if ($compareDate) {
                    $query->where(function($q) use ($compareDate) {
                        $q->whereDate('scheduled_at', $compareDate)
                          ->orWhereNull('scheduled_at');
                    });
                }

                $unpaidBookings = $query->get();

                foreach ($unpaidBookings as $ub) {
                    $ub->update([
                        'status' => 'cancelled',
                        'notes' => ($ub->notes ? $ub->notes . "\n" : "") . "[Auto-Cancelled] Trip started for paid passengers. This booking was cancelled due to incomplete payment.",
                        'payment_status' => $ub->payment_status === 'partially_paid' ? 'refund' : $ub->payment_status
                    ]);

                    if ($ub->user) {
                        $ub->user->notify(new \App\Notifications\TourDepartureCancellationNotification($ub, $packageItem->bookable->title ?? 'Tour'));
                    }
                    
                    // Release chauffeur if assigned to this specific unpaid booking
                    if ($ub->chauffeur_id) {
                         \Modules\Fleet\Models\Chauffeur::where('id', $ub->chauffeur_id)->update(['status' => 'available']);
                    }
                }
            }
        }

        // Safety check: Don't allow starting the trip for any sibling if it's not paid
        foreach ($siblings as $key => $sibling) {
            if ($sibling->payment_status !== 'paid') {
                $sibling->update([
                    'status' => 'cancelled',
                    'notes' => ($sibling->notes ? $sibling->notes . "\n" : "") . "[Auto-Cancelled] Attempted to start trip without full payment.",
                    'payment_status' => $sibling->payment_status === 'partially_paid' ? 'refund' : $sibling->payment_status
                ]);
                $siblings->forget($key);
            }
        }
        
        if ($siblings->isEmpty()) {
            auth()->user()->chauffeurProfile->update(['status' => 'available']);
            return back()->with('error', 'Trip cancelled automatically. Full payment was required to start.');
        }

        foreach ($siblings as $sibling) {
            $sibling->update([
                'trip_status' => 'in_progress',
                'trip_started_at' => now(),
                'status' => 'confirmed',
                'trip_end_code' => $tripEndCode
            ]);
            if ($sibling->user) {
                $sibling->user->notify(new \App\Notifications\TripStarted($sibling, 'outbound'));
            }
        }

        auth()->user()->chauffeurProfile->update(['status' => 'engaged']);

        $admins = \App\Models\User::role('Super Admin')->get();
        \Illuminate\Support\Facades\Notification::send($admins, new \App\Notifications\TripStarted($booking, 'outbound'));

        return back()->with('success', 'Trip started successfully!');
    }

    public function endTrip(Booking $booking)
    {
        if ($booking->chauffeur_id !== auth()->user()->chauffeurProfile->id) {
            abort(403);
        }

        $endTime = now();
        $startTime = $booking->trip_started_at;
        $durationText = "N/A";
        
        if ($startTime) {
            $diff = $endTime->diff($startTime);
            $durationText = $diff->format('%h hrs %i mins');
        }

        $siblings = $this->getSiblingBookings($booking);
        $booking->load('items.bookable');
        $isTourism = $booking->isTourismBooking();

        if ($isTourism) {
            foreach ($siblings as $sibling) {
                $sibling->update([
                    'trip_status' => 'completed',
                    'trip_ended_at' => $endTime,
                    'trip_duration' => $durationText,
                    'trip_leg' => 'return',
                    'return_trip_status' => 'idle',
                    'trip_end_code' => null,
                ]);
                if ($sibling->user) {
                    $sibling->user->notify(new \App\Notifications\TripEnded($sibling, 'outbound'));
                }
            }

            // For tourism, keep chauffeur engaged until the very end of the return trip
            auth()->user()->chauffeurProfile->update(['status' => 'engaged']);

            $admins = \App\Models\User::role('Super Admin')->get();
            \Illuminate\Support\Facades\Notification::send($admins, new \App\Notifications\TripEnded($booking, 'outbound'));

            return back()->with('success', 'Outbound trip ended! Duration: ' . $durationText . '. Chauffeur remains engaged for the return leg.');
        }

        foreach ($siblings as $sibling) {
            $sibling->update([
                'trip_status' => 'completed',
                'trip_ended_at' => $endTime,
                'trip_duration' => $durationText,
                'status' => 'completed'
            ]);
            if ($sibling->user) {
                $sibling->user->notify(new \App\Notifications\TripEnded($sibling));
            }
        }

        auth()->user()->chauffeurProfile->update(['status' => 'available']);

        $admins = \App\Models\User::role('Super Admin')->get();
        \Illuminate\Support\Facades\Notification::send($admins, new \App\Notifications\TripEnded($booking));

        return back()->with('success', 'Trip ended successfully!');
    }

    public function startReturnTrip(Booking $booking)
    {
        if ($booking->chauffeur_id !== auth()->user()->chauffeurProfile->id) {
            abort(403);
        }

        if ($booking->trip_leg !== 'return' || $booking->return_trip_status !== 'idle') {
            return back()->with('error', 'Return trip is not available.');
        }

        if ($booking->return_driver_schedule_status !== 'accepted' || $booking->return_customer_schedule_status !== 'accepted') {
            return back()->with('error', 'Confirmations pending.');
        }

        $returnEndCode = strtoupper(\Illuminate\Support\Str::random(6));

        $siblings = $this->getSiblingBookings($booking);
        foreach ($siblings as $sibling) {
            $sibling->update([
                'return_trip_status' => 'in_progress',
                'return_started_at' => now(),
                'return_end_code' => $returnEndCode,
            ]);
            if ($sibling->user) {
                $sibling->user->notify(new \App\Notifications\TripStarted($sibling, 'return'));
            }
        }

        auth()->user()->chauffeurProfile->update(['status' => 'engaged']);

        $admins = \App\Models\User::role('Super Admin')->get();
        \Illuminate\Support\Facades\Notification::send($admins, new \App\Notifications\TripStarted($booking, 'return'));

        return back()->with('success', 'Return trip started successfully!');
    }

    public function endReturnTrip(Booking $booking)
    {
        if ($booking->chauffeur_id !== auth()->user()->chauffeurProfile->id) {
            abort(403);
        }

        $endTime = now();
        $startTime = $booking->return_started_at;
        $durationText = "N/A";
        
        if ($startTime) {
            $diff = $endTime->diff($startTime);
            $durationText = $diff->format('%h hrs %i mins');
        }

        $siblings = $this->getSiblingBookings($booking);
        foreach ($siblings as $sibling) {
            $sibling->update([
                'return_trip_status' => 'completed',
                'return_ended_at' => $endTime,
                'return_duration' => $durationText,
                'return_end_code' => null,
                'status' => 'completed',
            ]);
            if ($sibling->user) {
                $sibling->user->notify(new \App\Notifications\TripEnded($sibling, 'return'));
            }
        }

        // FINALLY release chauffeur status
        auth()->user()->chauffeurProfile->update(['status' => 'available']);

        $admins = \App\Models\User::role('Super Admin')->get();
        \Illuminate\Support\Facades\Notification::send($admins, new \App\Notifications\TripEnded($booking, 'return'));

        return back()->with('success', 'Booking completed! Chauffeur is now available.');
    }

    public function reportIssue(Request $request, Booking $booking)
    {
        if ($booking->chauffeur_id !== auth()->user()->chauffeurProfile->id) abort(403);

        $request->validate(['type' => 'required|string', 'description' => 'required|string']);

        \Modules\Booking\Models\TripReport::create([
            'booking_id' => $booking->id,
            'chauffeur_id' => auth()->user()->chauffeurProfile->id,
            'type' => $request->type,
            'description' => $request->description,
        ]);

        return back()->with('success', 'Issue reported.');
    }

    public function trips()
    {
        $chauffeur = auth()->user()->chauffeurProfile;
        if ($chauffeur) {
            $this->syncChauffeurStatus($chauffeur);
            $allTrips = Booking::where('chauffeur_id', $chauffeur->id)->with(['items.bookable', 'rating', 'user'])->orderBy('created_at', 'desc')->get();
            $activeTrips = $this->groupBookings($allTrips->filter(fn($b) => in_array($b->status, ['pending', 'confirmed', 'in_progress']) && ($b->trip_status !== 'completed' || ($b->trip_status === 'completed' && in_array($b->return_trip_status, ['idle', 'pending', 'in_progress'])))));
            $historyTrips = $this->groupBookings($allTrips->filter(fn($b) => $b->isTourismBooking() ? ($b->trip_status === 'completed' && $b->return_trip_status === 'completed') : $b->trip_status === 'completed'));
            return view('driver::trips', compact('activeTrips', 'historyTrips'));
        }
        return redirect()->route('home');
    }

    public function schedule()
    {
        $chauffeur = auth()->user()->chauffeurProfile;
        if ($chauffeur) {
            $this->syncChauffeurStatus($chauffeur);
            $scheduledTrips = $this->groupBookings(Booking::where('chauffeur_id', $chauffeur->id)
                ->where(fn($q) => $q
                    ->where(fn($sq) => $sq->whereNotNull('scheduled_at')->where('trip_status', 'idle'))
                    ->orWhere(fn($sq) => $sq->whereNotNull('return_scheduled_at')->where('return_trip_status', 'idle'))
                    ->orWhere(fn($sq) => $sq->whereNotNull('scheduled_at')->where('status', 'cancelled'))
                )
                ->with(['items.bookable', 'user'])->get()->sortBy(fn($t) => $t->scheduled_at ?? $t->return_scheduled_at)->values());
            
            $activeDays = $scheduledTrips->groupBy(fn($t) => ($t->scheduled_at ?? $t->return_scheduled_at)->format('Y-m-d'))->map(fn($ts) => [
                'has_trip' => true,
                'is_accepted' => $ts->every(fn($t) => $t->status !== 'cancelled' && ($t->trip_status === 'idle' ? $t->driver_schedule_status === 'accepted' : $t->return_driver_schedule_status === 'accepted')),
                'is_declined' => $ts->contains(fn($t) => $t->status !== 'cancelled' && ($t->trip_status === 'idle' ? $t->driver_schedule_status === 'declined' : $t->return_driver_schedule_status === 'declined')),
                'is_cancelled' => $ts->every(fn($t) => $t->status === 'cancelled'),
            ])->toArray();

            $weekDays = [];
            $startOfWeek = now()->startOfWeek(\Carbon\Carbon::MONDAY);
            for ($i = 0; $i < 7; $i++) {
                $day = $startOfWeek->copy()->addDays($i);
                $dayKey = $day->format('Y-m-d');
                $weekDays[] = [
                    'day_name' => $day->format('D'), 'day_number' => $day->day, 'full_date' => $dayKey, 'is_today' => $day->isToday(),
                    'has_trip' => isset($activeDays[$dayKey]), 'is_accepted' => $activeDays[$dayKey]['is_accepted'] ?? false, 'is_declined' => $activeDays[$dayKey]['is_declined'] ?? false,
                    'is_cancelled' => $activeDays[$dayKey]['is_cancelled'] ?? false
                ];
            }
            $currentMonth = now()->format('F Y');
            return view('driver::schedule', compact('scheduledTrips', 'weekDays', 'currentMonth'));
        }
        return redirect()->route('home');
    }

    public function profile()
    {
        $chauffeur = auth()->user()->chauffeurProfile;
        if ($chauffeur) {
            $ratings = \Modules\Booking\Models\DriverRating::where('chauffeur_id', $chauffeur->id)->with(['booking', 'user'])->orderBy('created_at', 'desc')->get();
            $totalRatings = $ratings->count();
            $avgRating = $totalRatings > 0 ? round($ratings->avg('rating'), 1) : 0;
            $allBookings = Booking::where('chauffeur_id', $chauffeur->id)->get();
            $totalTrips = $allBookings->count();
            $totalCompleted = $allBookings->where('trip_status', 'completed')->count();
            $completionRate = $totalTrips > 0 ? round(($totalCompleted / $totalTrips) * 100) . '%' : 'N/A';
            $recentRatings = $ratings;
            return view('driver::profile', compact('chauffeur', 'avgRating', 'totalRatings', 'completionRate', 'totalCompleted', 'totalTrips', 'recentRatings'));
        }
        return redirect()->route('home');
    }

    public function respondToSchedule(Request $request, Booking $booking)
    {
        $profile = auth()->user()->chauffeurProfile;
        if (!$profile || $booking->chauffeur_id !== $profile->id) return back()->with('error', 'Unauthorized.');
        $request->validate(['status' => 'required|in:accepted,declined']);
        
        $siblings = $this->getSiblingBookings($booking);
        foreach ($siblings as $sibling) {
            $sibling->update(['driver_schedule_status' => $request->status, 'driver_schedule_feedback' => $request->feedback]);
        }
        
        $this->syncChauffeurStatus($profile);
        return back()->with('success', 'Responded.');
    }

    public function respondToReturnSchedule(Request $request, Booking $booking)
    {
        $profile = auth()->user()->chauffeurProfile;
        if (!$profile || $booking->chauffeur_id !== $profile->id) return back()->with('error', 'Unauthorized.');
        $request->validate(['status' => 'required|in:accepted,declined']);
        
        $siblings = $this->getSiblingBookings($booking);
        foreach ($siblings as $sibling) {
            $sibling->update(['return_driver_schedule_status' => $request->status]);
        }
        
        $this->syncChauffeurStatus($profile);
        return back()->with('success', 'Responded.');
    }

    public function password()
    {
        return view('driver::profile.password');
    }

    public function passwordUpdate(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = auth()->user();

        if (!\Illuminate\Support\Facades\Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'The provided password does not match your current password.']);
        }

        $user->update([
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
        ]);

        return back()->with('success', 'Password updated successfully.');
    }
}

