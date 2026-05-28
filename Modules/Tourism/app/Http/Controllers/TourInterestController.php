<?php

namespace Modules\Tourism\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Tourism\Models\TourInterest;
use Modules\Tourism\Models\TourismPackage;
use App\Models\User;
use App\Notifications\TourInterestNotification;
use Illuminate\Support\Facades\Notification;

class TourInterestController extends Controller
{
    public function index()
    {
        $interests = TourInterest::with('package')->latest()->paginate(20);
        return view('tourism::interests.index', compact('interests'));
    }

    public function specialBooking($token)
    {
        $interest = TourInterest::where('token', $token)->firstOrFail();
        $package = $interest->package;
        
        session(['specialBooking' => [
            'interest' => $interest,
            'package' => $package
        ]]);

        return redirect('/');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'package_id' => 'required|exists:tourism_packages,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
        ]);

        // Check for existing users (same logic as BookingController)
        if (\App\Models\User::where('email', $request->email)->exists()) {
            return back()->with('error', 'This email is already registered to an account. Please login or use a different email.');
        }

        if ($request->phone && \App\Models\User::where('phone', $request->phone)->exists()) {
            return back()->with('error', 'This phone number is already registered. Please login or use a different number.');
        }

        // Check for pending bookings
        $pendingBooking = \Modules\Booking\Models\Booking::where('status', 'pending')
            ->where(function($q) use ($request) {
                $q->where('customer_email', $request->email);
                if ($request->phone) {
                    $q->orWhere('customer_phone', $request->phone);
                }
            })->exists();

        if ($pendingBooking) {
            return back()->with('error', 'You already have a pending booking with this email/phone. Please complete that first or use a different account.');
        }

        // Check for existing interest for this package
        $exists = TourInterest::where('package_id', $request->package_id)
            ->where(function($q) use ($request) {
                $q->where('email', $request->email);
                if ($request->phone) {
                    $q->orWhere('phone', $request->phone);
                }
            })->exists();

        if ($exists) {
            return back()->with('error', 'You have already sent a request of interest for this tour. Management will contact you shortly.');
        }

        $interest = TourInterest::create($validated);

        // Notify Super Admins
        $admins = User::role(['Super Admin', 'Operations Admin'])->get();
        Notification::send($admins, new TourInterestNotification($interest));

        return back()->with('interest_success', 'Your interest has been recorded. Management will contact you shortly regarding the tour capacity.');
    }

    public function checkDuplicate(Request $request)
    {
        $email = $request->email;
        $phone = $request->phone;
        $packageId = $request->package_id;

        // Check for existing users (same logic as BookingController)
        if ($email && \App\Models\User::where('email', $email)->exists()) {
            return response()->json([
                'exists' => true,
                'message' => 'This email is already registered to an account. Please login or use a different email.'
            ]);
        }

        if ($phone && \App\Models\User::where('phone', $phone)->exists()) {
            return response()->json([
                'exists' => true,
                'message' => 'This phone number is already registered. Please login or use a different number.'
            ]);
        }

        // Check for pending bookings (same logic as BookingController)
        $pendingBooking = \Modules\Booking\Models\Booking::where('status', 'pending')
            ->where(function($q) use ($email, $phone) {
                if ($email) $q->where('customer_email', $email);
                if ($phone) $q->orWhere('customer_phone', $phone);
            })->exists();

        if ($pendingBooking) {
            return response()->json([
                'exists' => true,
                'message' => 'You already have a pending booking with this email/phone. Please complete that first or use a different account.'
            ]);
        }

        // Check for existing interest for this package
        $exists = TourInterest::where('package_id', $packageId)
            ->where(function($q) use ($email, $phone) {
                if ($email) $q->where('email', $email);
                if ($phone) $q->orWhere('phone', $phone);
            })->exists();

        return response()->json([
            'exists' => $exists,
            'message' => $exists ? 'You have already sent a request of interest for this tour.' : ''
        ]);
    }
}
