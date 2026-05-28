<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Modules\Booking\Models\Booking;

class CustomerManagementController extends Controller
{
    /**
     * Display the customer directory listing.
     */
    public function index()
    {
        $customers = User::where(function ($q) {
                $q->whereHas('roles', function ($r) {
                    $r->whereIn('name', ['Customer', 'Corporate Account']);
                })->orWhere('user_type', 'customer');
            })
            ->with(['roles', 'profile'])
            ->withCount('bookings')
            ->withSum('bookings', 'total_amount')
            ->latest()
            ->get();

        $stats = [
            'total_customers' => $customers->count(),
            'active_bookings' => Booking::whereIn('user_id', $customers->pluck('id'))
                ->whereIn('status', ['pending', 'confirmed'])
                ->count(),
            'total_revenue' => $customers->sum('bookings_sum_total_amount') ?? 0,
            'new_this_month' => $customers->filter(function ($c) {
                return $c->created_at && $c->created_at->isCurrentMonth();
            })->count(),
        ];

        return view('admin::customers.index', compact('customers', 'stats'));
    }

    /**
     * Display a single customer's full profile.
     */
    public function show($id)
    {
        $customer = User::with(['profile', 'roles'])->findOrFail($id);

        $bookings = Booking::where(function ($q) use ($customer) {
                $q->where('user_id', $customer->id)
                  ->orWhere('customer_email', $customer->email);
            })
            ->with(['items.bookable', 'chauffeur.user', 'rating', 'complaints'])
            ->latest()
            ->get();

        $stats = [
            'total_bookings' => $bookings->count(),
            'completed_trips' => $bookings->where('trip_status', 'completed')->count(),
            'total_spend' => $bookings->sum('total_amount'),
            'avg_rating_given' => $bookings->whereNotNull('rating')
                ->pluck('rating')
                ->avg('rating') ?? 0,
            'cancelled' => $bookings->where('status', 'cancelled')->count(),
            'cancellation_rate' => $bookings->count() > 0
                ? round(($bookings->where('status', 'cancelled')->count() / $bookings->count()) * 100)
                : 0,
        ];

        $recentBookings = $bookings->take(5);

        return view('admin::customers.show', compact('customer', 'bookings', 'stats', 'recentBookings'));
    }
}
