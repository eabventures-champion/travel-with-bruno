<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();

        // Redirect drivers to their specific dashboard
        if ($user->hasRole(['Driver', 'Chauffeur']) || $user->user_type === 'driver') {
            return redirect()->route('driver.dashboard');
        }

        $stats = [
            'total_customers' => \App\Models\User::where('user_type', 'customer')->count(),
            'total_packages' => \Modules\Tourism\Models\TourismPackage::count(),
            'active_vehicles' => \Modules\Fleet\Models\Vehicle::where('status', 'available')->count(),
            'total_chauffeurs' => \Modules\Fleet\Models\Chauffeur::count(),
            'active_bookings' => \Modules\Booking\Models\Booking::where('status', 'pending')->count(),
            'ongoing_organized_tours' => \Modules\Tourism\Models\TourismPackage::ongoing()->count(),
            'completed_organized_tours' => \Modules\Tourism\Models\TourismPackage::completedOrganized()->count(),
            'ongoing_fixed_tours' => \Modules\Booking\Models\Booking::whereHas('items', function($q) {
                $q->where('bookable_type', 'Modules\Tourism\Models\TourismPackage')
                  ->whereIn('bookable_id', \Modules\Tourism\Models\TourismPackage::where('package_type', 'fixed')->pluck('id'));
            })->where(function($q) {
                $q->where('trip_status', 'in_progress')
                  ->orWhere('return_trip_status', 'in_progress')
                  ->orWhere(function($subq) {
                      $subq->where('trip_status', 'completed')
                           ->where('return_trip_status', 'idle');
                  });
            })->count(),
            'completed_fixed_tours' => \Modules\Booking\Models\Booking::whereHas('items', function($q) {
                $q->where('bookable_type', 'Modules\Tourism\Models\TourismPackage')
                  ->whereIn('bookable_id', \Modules\Tourism\Models\TourismPackage::where('package_type', 'fixed')->pluck('id'));
            })->where('trip_status', 'completed')->where(function($q) {
                $q->where('return_trip_status', 'completed')->orWhereNull('return_trip_status');
            })->count(),
        ];

        $user = auth()->user();
        $bookingsQuery = \Modules\Booking\Models\Booking::with(['items.bookable', 'user', 'chauffeur.user'])
            ->latest();

        if (!$user->hasAnyRole(['Super Admin', 'Operations Admin'])) {
            $bookingsQuery->where(function($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('customer_email', $user->email);
            });
        }

        $recentBookings = $bookingsQuery->take(5)->get();

        $upcomingTours = \Modules\Tourism\Models\TourismPackage::where('status', 'active')
            ->where('package_type', 'scheduled')
            ->whereNotNull('departure_date')
            ->where('departure_date', '>=', now()->toDateString())
            ->with('category')
            ->withSum(['items as guests_count' => function($query) {
                $query->whereHas('booking', function($q) {
                    $q->whereIn('status', ['confirmed', 'completed'])
                      ->where('payment_status', 'paid');
                });
            }], 'quantity')
            ->orderBy('departure_date')
            ->take(5)
            ->get();

        $ongoingTours = \Modules\Tourism\Models\TourismPackage::ongoing()->with(['category', 'items.booking.chauffeur.user'])
            ->withSum(['items as guests_count' => function($query) {
                $query->whereHas('booking', function($q) {
                    $q->whereIn('status', ['confirmed', 'completed'])
                      ->where('payment_status', 'paid');
                });
            }], 'quantity')
            ->get();
        $completedTours = \Modules\Tourism\Models\TourismPackage::completedOrganized()->with('category')
            ->withSum(['items as guests_count' => function($query) {
                $query->whereHas('booking', function($q) {
                    $q->whereIn('status', ['confirmed', 'completed'])
                      ->where('payment_status', 'paid');
                });
            }], 'quantity')
            ->get();

        $ongoingFixedTours = \Modules\Booking\Models\Booking::whereHas('items', function($q) {
            $q->where('bookable_type', 'Modules\Tourism\Models\TourismPackage')
              ->whereIn('bookable_id', \Modules\Tourism\Models\TourismPackage::where('package_type', 'fixed')->pluck('id'));
        })->where(function($q) {
            $q->where('trip_status', 'in_progress')
              ->orWhere('return_trip_status', 'in_progress')
              ->orWhere(function($subq) {
                  $subq->where('trip_status', 'completed')
                       ->where('return_trip_status', 'idle');
              });
        })->with(['items.bookable', 'user', 'chauffeur.user'])->get();

        $completedFixedTours = \Modules\Booking\Models\Booking::whereHas('items', function($q) {
            $q->where('bookable_type', 'Modules\Tourism\Models\TourismPackage')
              ->whereIn('bookable_id', \Modules\Tourism\Models\TourismPackage::where('package_type', 'fixed')->pluck('id'));
        })->where('trip_status', 'completed')->where(function($q) {
            $q->where('return_trip_status', 'completed')->orWhereNull('return_trip_status');
        })->with(['items.bookable', 'user', 'chauffeur.user'])->get();

        // Fetch Broadcasted Documents for non-admins (Customers)
        $broadcasts = collect();
        if (!$user->hasAnyRole(['Super Admin', 'Operations Admin'])) {
            $broadcasts = \App\Models\BroadcastDocument::whereIn('target_audience', ['all', 'customers'])
                ->orWhere(function($query) use ($user) {
                    $query->where('target_audience', 'selected')
                          ->whereHas('users', function($q) use ($user) {
                              $q->where('users.id', $user->id);
                          });
                })
                ->latest()
                ->get();
        }

        return view('admin::index', compact('stats', 'upcomingTours', 'recentBookings', 'ongoingTours', 'completedTours', 'ongoingFixedTours', 'completedFixedTours', 'broadcasts'));
    }

    public function profile()
    {
        $user = auth()->user();
        return view('admin::profile', compact('user'));
    }

    public function profileUpdate(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20|unique:users,phone,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            // Profile fields
            'bio' => 'nullable|string',
            'address' => 'nullable|string|max:255',
            'emergency_contact' => 'nullable|string|max:255',
            'nationality' => 'nullable|string|max:255',
            'id_document_number' => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|date',
            'travel_preferences' => 'nullable|string',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => $validated['password'] ?? $user->password,
            'bio' => $validated['bio'],
            'address' => $validated['address'],
            'emergency_contact' => $validated['emergency_contact'],
            'nationality' => $validated['nationality'],
            'id_document' => $validated['id_document_number'],
            'dob' => $validated['date_of_birth'],
            'travel_preferences' => $validated['travel_preferences'],
        ]);

        return back()->with('success', 'Profile updated successfully.');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('admin::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('admin::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }
    public function markNotificationAsRead($id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        // Try 'url' first, then 'action_url', then fallback to dashboard
        $url = $notification->data['url'] ?? $notification->data['action_url'] ?? null;

        // If the URL is empty, '#', or points to the bare /admin dashboard, try to build
        // a proper booking detail URL from booking_id stored in the notification data.
        if (empty($url) || $url === '#' || preg_match('#/admin/?$#', $url)) {
            if (isset($notification->data['booking_id'])) {
                $url = route('admin.bookings.show', $notification->data['booking_id']);
            } else {
                $url = route('admin.dashboard');
            }
        }

        // Handle legacy URLs that might be stored in the database for older notifications
        if (str_contains($url, '/dashboard')) {
            if (isset($notification->data['booking_id'])) {
                $url = route('admin.bookings.show', $notification->data['booking_id']);
            } else {
                $url = route('admin.dashboard');
            }
        } elseif (str_contains($url, '/customer/bookings')) {
            // customer.bookings.show route doesn't exist; redirect to the admin booking show page
            if (isset($notification->data['booking_id'])) {
                $url = route('admin.bookings.show', $notification->data['booking_id']);
            } else {
                $url = route('admin.bookings.index');
            }
        }

        if (!empty($url)) {
            $parsed = parse_url($url);
            if (!empty($parsed['host']) && (in_array($parsed['host'], ['localhost', '127.0.0.1']) || str_contains($parsed['host'], 'localhost'))) {
                $path = $parsed['path'] ?? '/';
                $query = isset($parsed['query']) ? '?' . $parsed['query'] : '';
                $fragment = isset($parsed['fragment']) ? '#' . $parsed['fragment'] : '';
                $url = $path . $query . $fragment;
            }
        }

        return redirect($url);
    }

    public function markAllNotificationsAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();
        return back()->with('success', 'All notifications marked as read.');
    }

    public function clearAllNotifications()
    {
        auth()->user()->notifications()->delete();
        return back()->with('success', 'All notifications cleared successfully.');
    }
}
