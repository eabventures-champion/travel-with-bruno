<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Fleet\Models\Chauffeur;
use Modules\Booking\Models\Booking;
use Modules\Booking\Models\DriverRating;

class ChauffeurManagementController extends Controller
{
    /**
     * Display the chauffeur directory listing.
     */
    public function index()
    {
        $chauffeurs = Chauffeur::with(['user'])
            ->withCount('bookings')
            ->get()
            ->map(function ($chauffeur) {
                // Determine if chauffeur has an active trip (outbound or return)
                $activeBooking = Booking::where('chauffeur_id', $chauffeur->id)
                    ->where(function($q) {
                        $q->where('trip_status', 'in_progress')
                          ->orWhere('return_trip_status', 'in_progress');
                    })
                    ->with(['items.bookable', 'user'])
                    ->first();

                // Also check for tourism bookings that are in the "waiting for return" gap
                $isCommittedTourism = !$activeBooking ? Booking::where('chauffeur_id', $chauffeur->id)
                    ->where(function($q) {
                        $q->where('trip_status', 'completed')
                          ->where('return_trip_status', '!=', 'completed')
                          ->where('trip_leg', 'return');
                    })
                    ->with(['items.bookable', 'user'])
                    ->first() : null;
                
                $engagedBooking = $activeBooking ?: $isCommittedTourism;

                if ($engagedBooking && $chauffeur->status !== 'engaged') {
                    $chauffeur->status = 'engaged';
                } elseif (!$engagedBooking && $chauffeur->status === 'engaged') {
                    // Check if they have accepted schedules
                    $hasAcceptedSchedule = Booking::where('chauffeur_id', $chauffeur->id)
                        ->where(function($q) {
                            $q->where(function($sq) {
                                $sq->where('trip_status', 'idle')
                                  ->where('driver_schedule_status', 'accepted');
                            })->orWhere(function($sq) {
                                $sq->where('return_trip_status', 'idle')
                                  ->where('return_driver_schedule_status', 'accepted');
                            });
                        })
                        ->whereNotIn('status', ['completed', 'cancelled'])
                        ->with(['items.bookable', 'user'])
                        ->first();
                    
                    $chauffeur->status = $hasAcceptedSchedule ? 'schedule_accepted' : 'available';
                    $chauffeur->active_booking = $hasAcceptedSchedule;
                } else {
                    $chauffeur->active_booking = $engagedBooking;
                    
                    if (!$engagedBooking) {
                         $hasAcceptedSchedule = Booking::where('chauffeur_id', $chauffeur->id)
                            ->where(function($q) {
                                $q->where(function($sq) {
                                    $sq->where('trip_status', 'idle')
                                      ->where('driver_schedule_status', 'accepted');
                                })->orWhere(function($sq) {
                                    $sq->where('return_trip_status', 'idle')
                                      ->where('return_driver_schedule_status', 'accepted');
                                });
                            })
                            ->whereNotIn('status', ['completed', 'cancelled'])
                            ->with(['items.bookable', 'user'])
                            ->first();
                        
                        if ($hasAcceptedSchedule) {
                            $chauffeur->status = 'schedule_accepted';
                            $chauffeur->active_booking = $hasAcceptedSchedule;
                        }
                    }
                }

                // Correctly count completed trips (only fully completed)
                $chauffeur->completed_trips = Booking::where('chauffeur_id', $chauffeur->id)
                    ->get()
                    ->filter(fn($b) => $b->isFullyCompleted())
                    ->count();

                $chauffeur->avg_rating = DriverRating::where('chauffeur_id', $chauffeur->id)->avg('rating') ?? 0;
                $chauffeur->total_ratings = DriverRating::where('chauffeur_id', $chauffeur->id)->count();
                return $chauffeur;
            })
            ->sortBy(function($c) {
                if ($c->status === 'engaged') return 0;
                if ($c->status === 'schedule_accepted') return 1;
                return 2;
            });

        $stats = [
            'total_chauffeurs' => $chauffeurs->count(),
            'online_now' => $chauffeurs->where('is_online', true)->count(),
            'engaged' => $chauffeurs->where('status', 'engaged')->count(),
            'schedule_accepted' => $chauffeurs->where('status', 'schedule_accepted')->count(),
            'avg_rating' => $chauffeurs->avg('avg_rating') ? round($chauffeurs->avg('avg_rating'), 1) : 0,
        ];

        return view('admin::chauffeur-management.index', compact('chauffeurs', 'stats'));
    }

    /**
     * Display a single chauffeur's full profile.
     */
    public function show($id)
    {
        $chauffeur = Chauffeur::with(['user'])->findOrFail($id);

        $bookings = Booking::where('chauffeur_id', $chauffeur->id)
            ->with(['items.bookable', 'user', 'rating', 'complaints', 'reports'])
            ->latest()
            ->get();

        // Dynamic status check for display consistency
        $hasActiveTrip = $bookings->filter(function($b) {
            return $b->trip_status === 'in_progress' || $b->return_trip_status === 'in_progress';
        })->first();

        $isCommittedTourism = $bookings->filter(function($b) {
            return $b->trip_status === 'completed' && $b->return_trip_status !== 'completed' && $b->trip_leg === 'return';
        })->first();

        if (($hasActiveTrip || $isCommittedTourism) && $chauffeur->status !== 'engaged') {
            $chauffeur->status = 'engaged';
        } elseif (!$hasActiveTrip && !$isCommittedTourism && $chauffeur->status === 'engaged') {
            $hasAcceptedSchedule = $bookings->filter(function($b) {
                return ($b->trip_status === 'idle' && $b->driver_schedule_status === 'accepted') ||
                       ($b->return_trip_status === 'idle' && $b->return_driver_schedule_status === 'accepted');
            })->whereNotIn('status', ['completed', 'cancelled'])->first();
            $chauffeur->status = $hasAcceptedSchedule ? 'schedule_accepted' : 'available';
        }

        $ratings = DriverRating::where('chauffeur_id', $chauffeur->id)
            ->with(['booking', 'user'])
            ->latest()
            ->get();

        $stats = [
            'total_trips' => $bookings->count(),
            'completed_trips' => $bookings->filter(fn($b) => $b->isFullyCompleted())->count(),
            'completion_rate' => $bookings->count() > 0
                ? round(($bookings->filter(fn($b) => $b->isFullyCompleted())->count() / $bookings->count()) * 100)
                : 0,
            'total_revenue' => $bookings->sum('total_amount'),
            'avg_rating' => $ratings->count() > 0 ? round($ratings->avg('rating'), 1) : 0,
            'total_ratings' => $ratings->count(),
            'active_trips' => $bookings->filter(fn($b) => !$b->isFullyCompleted() && !in_array($b->status, ['cancelled', 'declined']))->count(),
        ];

        // Rating distribution
        $ratingDistribution = [];
        for ($i = 5; $i >= 1; $i--) {
            $count = $ratings->where('rating', $i)->count();
            $ratingDistribution[$i] = [
                'count' => $count,
                'percentage' => $ratings->count() > 0 ? round(($count / $ratings->count()) * 100) : 0,
            ];
        }

        $recentRatings = $ratings->take(5);

        // Trip reports filed by this chauffeur
        $tripReports = \Modules\Booking\Models\TripReport::where('chauffeur_id', $chauffeur->id)
            ->with('booking')
            ->latest()
            ->get();

        return view('admin::chauffeur-management.show', compact(
            'chauffeur', 'bookings', 'ratings', 'stats', 'ratingDistribution', 'recentRatings', 'tripReports'
        ));
    }

    public function verifyDocument(Request $request, $id)
    {
        $chauffeur = Chauffeur::findOrFail($id);
        $type = $request->type; // 'license' or 'id_card'
        
        if ($type === 'license') {
            $chauffeur->update(['license_verified_at' => now()]);
        } elseif ($type === 'id_card') {
            $chauffeur->update(['id_verified_at' => now()]);
        }
        
        return back()->with('success', ucfirst(str_replace('_', ' ', $type)) . ' verified successfully.');
    }
}
