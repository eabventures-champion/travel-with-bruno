<?php

namespace Modules\Booking\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Booking\Models\Booking;
use Modules\Booking\Models\BookingItem;
use Modules\Booking\Models\BookingChangeRequest;
use Modules\Tourism\Models\TourismPackage;
use Modules\Fleet\Models\Vehicle;
use Modules\Booking\Emails\BookingConfirmation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;

use Modules\Booking\Emails\BookingApproved;
use Modules\Fleet\Models\AirportTransfer;
use App\Models\User;
use App\Models\TransferZone;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Auto-cancel any tourism package bookings where the tour has completed but payment remains pending
        $pendingCompletedBookings = Booking::where('status', '!=', 'cancelled')
            ->where('payment_status', 'pending')
            ->whereHas('items', function($q) {
                $q->where('bookable_type', 'Modules\Tourism\Models\TourismPackage');
            })
            ->get();

        foreach ($pendingCompletedBookings as $b) {
            $firstItem = $b->items->first();
            if ($firstItem && $firstItem->bookable_type === 'Modules\Tourism\Models\TourismPackage') {
                $package = $firstItem->bookable;
                if ($package && $package->package_type === 'scheduled') {
                    $returnDate = $package->return_date ?: $package->departure_date;
                    if ($returnDate && now()->startOfDay()->greaterThan($returnDate->endOfDay())) {
                        $b->update([
                            'status' => 'cancelled',
                            'notes' => ($b->notes ? $b->notes . "\n" : "") . '[System] Auto-cancelled: Tour is completed but payment was never completed.'
                        ]);
                    }
                }
            }
        }

        $query = Booking::with(['user', 'items.bookable'])->latest();

        // If not admin, only show own bookings
        if (!auth()->user()->hasAnyRole(['Super Admin', 'Operations Admin'])) {
            $query->where(function($q) {
                $q->where('user_id', auth()->id())
                  ->orWhere('customer_email', auth()->user()->email);
            });
        } else {
            if ($request->filled('search')) {
                $search = $request->input('search');
                $query->where(function($q) use ($search) {
                    $q->where('booking_reference', 'like', "%{$search}%")
                      ->orWhere('customer_name', 'like', "%{$search}%")
                      ->orWhere('customer_email', 'like', "%{$search}%")
                      ->orWhere('customer_phone', 'like', "%{$search}%")
                      ->orWhereHas('items', function($itemQuery) use ($search) {
                          $itemQuery->whereHasMorph('bookable', [Vehicle::class, TourismPackage::class, AirportTransfer::class], function($morphQuery, $type) use ($search) {
                              if ($type === Vehicle::class) {
                                  $morphQuery->where('license_plate', 'like', "%{$search}%")
                                             ->orWhere('make', 'like', "%{$search}%")
                                             ->orWhere('model', 'like', "%{$search}%");
                              } elseif ($type === TourismPackage::class) {
                                  $morphQuery->where('title', 'like', "%{$search}%")
                                             ->orWhere('location', 'like', "%{$search}%");
                              } elseif ($type === AirportTransfer::class) {
                                  $morphQuery->where('airport_name', 'like', "%{$search}%")
                                             ->orWhere('location', 'like', "%{$search}%");
                              }
                          });
                      });
                });
            }
        }

        $allBookings = $query->get();
        
        // Group by custom logic
        $groupedBookings = [
            'pending' => $allBookings->where('status', 'pending'),
            'confirmed' => $allBookings->where('status', 'confirmed')->filter(function($b) {
                return $b->trip_status === 'idle' || !$b->trip_status;
            }),
            'live_outbound' => $allBookings->where('status', 'confirmed')->filter(function($b) {
                return $b->trip_status === 'in_progress';
            }),
            'live_return' => $allBookings->where('status', 'confirmed')->filter(function($b) {
                return $b->return_trip_status === 'in_progress' || 
                       ($b->trip_status === 'completed' && $b->return_trip_status !== 'completed' && $b->isTourismBooking());
            }),
            'completed' => $allBookings->where('status', 'completed'),
            'cancelled' => $allBookings->where('status', 'cancelled'),
        ];
        
        $chauffeurs = [];
        if (auth()->user()->hasAnyRole(['Super Admin', 'Operations Admin'])) {
            $chauffeurs = \Modules\Fleet\Models\Chauffeur::with('user')
                ->where('status', '!=', 'suspended')
                ->where('status', '!=', 'engaged')
                ->get();
        }
        
        return view('booking::admin.index', compact('groupedBookings', 'allBookings', 'chauffeurs'));
    }

    /**
     * Display the specified booking.
     */
    public function show(Booking $booking)
    {
        // Security check: if not admin, must be owner
        if (!auth()->user()->hasAnyRole(['Super Admin', 'Operations Admin'])) {
            $isCustomer = $booking->user_id === auth()->id() || $booking->customer_email === auth()->user()->email;
            $isAssignedChauffeur = $booking->chauffeur && $booking->chauffeur->user_id === auth()->id();

            if (!$isCustomer && !$isAssignedChauffeur) {
                abort(403, 'Unauthorized access to this booking.');
            }
        }

        $booking->load(['user', 'items.bookable', 'chauffeur.user', 'complaints.messages.user', 'payments', 'activities.causer']);
        
        $chauffeurs = [];
        if (auth()->user()->hasAnyRole(['Super Admin', 'Operations Admin'])) {
            $chauffeurs = \Modules\Fleet\Models\Chauffeur::with('user')
                ->where('status', '!=', 'suspended')
                // ->where('is_online', true) // Removed to allow advanced scheduling even if driver is offline
                ->where(function($query) use ($booking) {
                    $query->where('status', 'available')
                          ->orWhere('status', 'schedule_accepted'); // Also show those who have accepted schedules for other trips so they can be double-checked?
                    if ($booking->chauffeur_id) {
                        $query->orWhere('id', $booking->chauffeur_id);
                    }
                })->get();
        }

        return view('booking::admin.show', compact('booking', 'chauffeurs'));
    }

    public function assignChauffeur(Request $request, Booking $booking)
    {
        $request->validate([
            'chauffeur_id' => 'nullable|exists:chauffeurs,id',
        ]);

        // Duty & Suspension Check
        if ($request->chauffeur_id) {
            if (in_array($booking->payment_status, ['partially_paid', 'pending'])) {
                return back()->with('error', 'Assignment Failed: Cannot assign a driver to a booking with partial or pending payment.');
            }

            if (!$booking->scheduled_at) {
                return back()->with('error', 'Assignment Failed: Cannot assign a driver to an unscheduled booking. Please schedule the tour first.');
            }

            $chauffeur = \Modules\Fleet\Models\Chauffeur::find($request->chauffeur_id);
            if ($chauffeur->status === 'suspended') {
                return back()->with('error', 'Assignment Failed: This chauffeur is currently suspended.');
            }
            // Removed is_online check to allow advanced scheduling
        }

        // Conflict Check: Ensure driver doesn't have another trip at the same time
        if ($request->chauffeur_id && $booking->scheduled_at) {
            $conflicts = Booking::where('chauffeur_id', $request->chauffeur_id)
                ->where('id', '!=', $booking->id)
                ->whereNotIn('status', ['cancelled', 'completed'])
                ->where('trip_status', '!=', 'completed')
                ->whereBetween('scheduled_at', [
                    $booking->scheduled_at->copy()->subHours(4),
                    $booking->scheduled_at->copy()->addHours(4)
                ])
                ->get();

            $conflict = $conflicts->first(function($c) use ($booking) {
                if ($booking->isTourismBooking() && $c->isTourismBooking()) {
                    $pkg1 = $booking->items->first()?->bookable_id;
                    $pkg2 = $c->items->first()?->bookable_id;
                    $date1 = $booking->scheduled_at->format('Y-m-d');
                    $date2 = $c->scheduled_at ? $c->scheduled_at->format('Y-m-d') : null;
                    if ($pkg1 === $pkg2 && $date1 === $date2) {
                        return false; // same tour on the same day is not a conflict
                    }
                }
                return true;
            });

            if ($conflict) {
                return back()->with('error', "Assignment Failed: This chauffeur is already assigned to trip {$conflict->booking_reference} at {$conflict->scheduled_at->format('M d, h:i A')}. Please choose a different driver or adjust the schedule.");
            }
        }

        $oldChauffeurId = $booking->chauffeur_id;
        $booking->update(['chauffeur_id' => $request->chauffeur_id]);

        $newChauffeur = $request->chauffeur_id ? \Modules\Fleet\Models\Chauffeur::with('user')->find($request->chauffeur_id) : null;
        $chauffeurName = $newChauffeur ? ($newChauffeur->user->name ?? 'Unknown Chauffeur') : 'None';
        
        activity('booking')
            ->performedOn($booking)
            ->event('chauffeur_assigned')
            ->withProperties([
                'chauffeur_id' => $request->chauffeur_id,
                'chauffeur_name' => $chauffeurName,
                'old_chauffeur_id' => $oldChauffeurId,
            ])
            ->log($request->chauffeur_id ? "Assigned chauffeur: {$chauffeurName}" : "Removed chauffeur assignment");

        // Notify new driver if trip is already scheduled
        if ($request->chauffeur_id && $booking->scheduled_at) {
            $newChauffeur = \Modules\Fleet\Models\Chauffeur::with('user')->find($request->chauffeur_id);
            if ($newChauffeur && $newChauffeur->user) {
                try {
                    $newChauffeur->user->notify(new \App\Notifications\TripScheduledNotification($booking->fresh(), false));
                } catch (\Exception $e) {
                    \Log::error("Chauffeur assignment notification failed: " . $e->getMessage());
                }
            }
        }

        // Release old chauffeur if any
        if ($oldChauffeurId && $oldChauffeurId != $request->chauffeur_id) {
            \Modules\Fleet\Models\Chauffeur::syncStatus($oldChauffeurId);
        }

        // Sync new chauffeur status if any
        if ($request->chauffeur_id) {
            \Modules\Fleet\Models\Chauffeur::syncStatus($request->chauffeur_id);
        }

        // Engaged status will now only be set when the driver starts the trip
        /*
        if ($request->chauffeur_id && in_array($booking->status, ['confirmed', 'in_progress'])) {
            \Modules\Fleet\Models\Chauffeur::where('id', $request->chauffeur_id)->update(['status' => 'engaged']);
        }
        */

        return back()->with('success', 'Chauffeur assigned to booking successfully');
    }

    /**
     * Handle bulk assignment of chauffeurs for organized tours.
     */
    public function bulkAssignChauffeur(Request $request)
    {
        $request->validate([
            'assignments' => 'required|array',
            'assignments.*.booking_id' => 'required|exists:bookings,id',
            'assignments.*.chauffeur_id' => 'nullable|exists:chauffeurs,id',
        ]);

        $updatedCount = 0;

        foreach ($request->assignments as $assignment) {
            $booking = Booking::find($assignment['booking_id']);
            $newChauffeurId = $assignment['chauffeur_id'] ?: null; // Handle empty string as null
            
            if ($booking->chauffeur_id != $newChauffeurId) {
                if ($newChauffeurId) {
                    if (in_array($booking->payment_status, ['partially_paid', 'pending'])) {
                        continue; // cannot assign to unpaid/partial bookings
                    }
                    if (!$booking->scheduled_at) {
                        continue; // cannot assign to unscheduled bookings
                    }
                }
                $oldChauffeurId = $booking->chauffeur_id;
                
                // Update booking
                $booking->update(['chauffeur_id' => $newChauffeurId]);
                $updatedCount++;

                $newChauffeur = $newChauffeurId ? \Modules\Fleet\Models\Chauffeur::with('user')->find($newChauffeurId) : null;
                $chauffeurName = $newChauffeur ? ($newChauffeur->user->name ?? 'Unknown Chauffeur') : 'None';
                
                activity('booking')
                    ->performedOn($booking)
                    ->event('chauffeur_assigned')
                    ->withProperties([
                        'chauffeur_id' => $newChauffeurId,
                        'chauffeur_name' => $chauffeurName,
                        'old_chauffeur_id' => $oldChauffeurId,
                    ])
                    ->log($newChauffeurId ? "Assigned chauffeur (Bulk): {$chauffeurName}" : "Removed chauffeur assignment (Bulk)");

                // Notify new driver if trip is already scheduled
                if ($newChauffeurId && $booking->scheduled_at) {
                    $newChauffeur = \Modules\Fleet\Models\Chauffeur::with('user')->find($newChauffeurId);
                    if ($newChauffeur && $newChauffeur->user) {
                        try {
                            $newChauffeur->user->notify(new \App\Notifications\TripScheduledNotification($booking->fresh(), false));
                        } catch (\Exception $e) {
                            \Log::error("Bulk chauffeur assignment notification failed: " . $e->getMessage());
                        }
                    }
                }
                
                // Release old chauffeur if any
                if ($oldChauffeurId && $oldChauffeurId != $newChauffeurId) {
                    \Modules\Fleet\Models\Chauffeur::where('id', $oldChauffeurId)->update(['status' => 'available']);
                }
            }
        }

        return back()->with('success', "Successfully updated driver assignments for {$updatedCount} bookings.");
    }

    /**
     * Handle bulk assignment of schedule dates for organized tours.
     */
    public function bulkAssignSchedule(Request $request)
    {
        $request->validate([
            'assignments' => 'required|array',
            'assignments.*.booking_id' => 'required|exists:bookings,id',
            'assignments.*.scheduled_at' => 'nullable|date',
            'assignments.*.return_scheduled_at' => 'nullable|date',
        ]);

        $updatedCount = 0;
        $errors = [];

        foreach ($request->assignments as $assignment) {
            $booking = Booking::find($assignment['booking_id']);
            $newSchedule = $assignment['scheduled_at'] ? \Carbon\Carbon::parse($assignment['scheduled_at']) : null;
            $newReturnSchedule = ($assignment['return_scheduled_at'] ?? null) ? \Carbon\Carbon::parse($assignment['return_scheduled_at']) : null;
            
            $updates = [];
            $isAdjustment = false;

            if ($newSchedule && (!$booking->scheduled_at || !$newSchedule->equalTo($booking->scheduled_at))) {
                // Conflict Check if driver is already assigned
                if ($booking->chauffeur_id) {
                    $conflicts = Booking::where('chauffeur_id', $booking->chauffeur_id)
                        ->where('id', '!=', $booking->id)
                        ->whereNotIn('status', ['cancelled', 'completed'])
                        ->where('trip_status', '!=', 'completed')
                        ->whereBetween('scheduled_at', [
                            $newSchedule->copy()->subHours(4),
                            $newSchedule->copy()->addHours(4)
                        ])
                        ->get();

                    $conflict = $conflicts->first(function($c) use ($booking, $newSchedule) {
                        if ($booking->isTourismBooking() && $c->isTourismBooking()) {
                            $pkg1 = $booking->items->first()?->bookable_id;
                            $pkg2 = $c->items->first()?->bookable_id;
                            $date1 = $newSchedule->format('Y-m-d');
                            $date2 = $c->scheduled_at ? $c->scheduled_at->format('Y-m-d') : null;
                            if ($pkg1 === $pkg2 && $date1 === $date2) {
                                return false; // same tour on the same day is not a conflict
                            }
                        }
                        return true;
                    });

                    if ($conflict) {
                        $errors[] = "Booking {$booking->booking_reference} (Outbound): Driver already assigned to trip {$conflict->booking_reference} at {$conflict->scheduled_at->format('M d, h:i A')}.";
                        continue;
                    }
                }

                $isAdjustment = $booking->scheduled_at !== null;
                $updates['scheduled_at'] = $newSchedule;
                $updates['driver_schedule_status'] = 'pending';
                $updates['customer_schedule_status'] = 'pending';
            }

            if ($newReturnSchedule && (!$booking->return_scheduled_at || !$newReturnSchedule->equalTo($booking->return_scheduled_at))) {
                $isAdjustment = true;
                $updates['return_scheduled_at'] = $newReturnSchedule;
                $updates['return_driver_schedule_status'] = 'pending';
                $updates['return_customer_schedule_status'] = 'pending';
            }

            if (!empty($updates)) {
                // If it's a tourism booking and we are updating return or outbound schedule, we should sync siblings too
                if ($booking->isTourismBooking()) {
                    $siblings = $booking->getSiblingBookings();
                    foreach ($siblings as $sibling) {
                        $oldSiblingScheduledAt = $sibling->scheduled_at;
                        $sibling->update($updates);
                        
                        if (isset($updates['scheduled_at'])) {
                            activity('booking')
                                ->performedOn($sibling)
                                ->event('schedule_updated')
                                ->withProperties([
                                    'scheduled_at' => $newSchedule?->toDateTimeString(),
                                    'old_scheduled_at' => $oldSiblingScheduledAt ? $oldSiblingScheduledAt->toDateTimeString() : null,
                                ])
                                ->log("Trip " . ($oldSiblingScheduledAt ? "rescheduled" : "scheduled") . " (Bulk) to " . ($newSchedule ? $newSchedule->format('M d, Y h:i A') : 'TBD'));
                        }
                        
                        // Notify Sibling's customer if outbound schedule changed
                        if (isset($updates['scheduled_at']) && $sibling->user) {
                            try {
                                $sibling->user->notify(new \App\Notifications\TripScheduledNotification($sibling, $isAdjustment));
                            } catch (\Exception $e) {
                                \Log::error("Bulk schedule notification to customer failed: " . $e->getMessage());
                            }
                        }
                    }
                } else {
                    $oldBookingScheduledAt = $booking->scheduled_at;
                    $booking->update($updates);
                    
                    if (isset($updates['scheduled_at'])) {
                        activity('booking')
                            ->performedOn($booking)
                            ->event('schedule_updated')
                            ->withProperties([
                                'scheduled_at' => $newSchedule?->toDateTimeString(),
                                'old_scheduled_at' => $oldBookingScheduledAt ? $oldBookingScheduledAt->toDateTimeString() : null,
                            ])
                            ->log("Trip " . ($oldBookingScheduledAt ? "rescheduled" : "scheduled") . " (Bulk) to " . ($newSchedule ? $newSchedule->format('M d, Y h:i A') : 'TBD'));
                    }
                    
                    if (isset($updates['scheduled_at']) && $booking->user) {
                        try {
                            $booking->user->notify(new \App\Notifications\TripScheduledNotification($booking, $isAdjustment));
                        } catch (\Exception $e) {
                            \Log::error("Bulk schedule notification to customer failed: " . $e->getMessage());
                        }
                    }
                }
                $updatedCount++;

                // Notify Driver
                if ($booking->chauffeur && $booking->chauffeur->user) {
                    try {
                        $booking->chauffeur->user->notify(new \App\Notifications\TripScheduledNotification($booking, $isAdjustment));
                    } catch (\Exception $e) {
                        \Log::error("Bulk schedule notification to driver failed: " . $e->getMessage());
                    }
                }

                // Sync driver status since schedule accepted state might change
                if ($booking->chauffeur_id) {
                    \Modules\Fleet\Models\Chauffeur::syncStatus($booking->chauffeur_id);
                }
            }
        }

        if (count($errors) > 0) {
            $errorString = implode("<br>", $errors);
            return back()->with('warning', "Scheduled {$updatedCount} bookings, but some failed due to driver conflicts:<br>{$errorString}");
        }

        return back()->with('success', "Successfully scheduled {$updatedCount} bookings.");
    }

    /**
     * Split a booking into two separate records to allow multi-driver dispatch.
     */
    public function splitBooking(Request $request, Booking $booking)
    {
        $totalQuantity = $booking->items->sum('quantity');
        $request->validate([
            'split_quantity' => "required|integer|min:1|max:" . ($totalQuantity - 1),
        ]);

        $splitQuantity = $request->split_quantity;
        $remainingQuantity = $totalQuantity - $splitQuantity;

        return DB::transaction(function () use ($booking, $splitQuantity, $remainingQuantity) {
            // Filter out old system notes from original booking notes
            $originalNotes = $booking->notes ?? '';
            $nonSystemNotes = collect(explode("\n", $originalNotes))
                ->filter(function($line) {
                    return !str_starts_with(trim($line), '[System]');
                })
                ->implode("\n");

            // 1. Clone the booking
            $newBooking = $booking->replicate();
            
            // Generate a unique suffix
            $suffixCount = Booking::where('booking_reference', 'like', $booking->booking_reference . '%')->count();
            $newBooking->booking_reference = $booking->booking_reference . '-S' . $suffixCount;
            
            // Proportionally split the total amount
            $originalTotalAmount = $booking->total_amount;
            $totalBase = $splitQuantity + $remainingQuantity;
            
            $newBooking->total_amount = round(($originalTotalAmount / $totalBase) * $splitQuantity, 2);
            $newBooking->notes = ($nonSystemNotes ? $nonSystemNotes . "\n" : "") . "[System] Guest count changed: {$splitQuantity} was transferred from the original {$totalBase}. Total adjusted to ₵" . number_format($newBooking->total_amount, 2) . " (Transferred from {$booking->booking_reference}, original total: {$totalBase} persons)";
            
            // Ensure no driver is assigned to the split-off portion by default
            $newBooking->chauffeur_id = null;
            $newBooking->driver_schedule_status = 'pending';
            
            $newBooking->save();

            activity('booking')
                ->performedOn($newBooking)
                ->event('booking_created')
                ->withProperties([
                    'transferred_from_reference' => $booking->booking_reference,
                    'transferred_from_id' => $booking->id,
                    'quantity' => $splitQuantity,
                ])
                ->log("Booking created via split from {$booking->booking_reference} with {$splitQuantity} guests.");

            // 2. Clone the items and update quantities
            foreach ($booking->items as $item) {
                $newItem = $item->replicate();
                $newItem->booking_id = $newBooking->id;
                $newItem->quantity = $splitQuantity;
                $newItem->save();
                
                // Update original item quantity
                $item->update(['quantity' => $remainingQuantity]);
            }

            // 3. Update original booking amount and notes
            $newTotalAmount = round(($originalTotalAmount / $totalBase) * $remainingQuantity, 2);
            $booking->total_amount = $newTotalAmount;
            $booking->notes = ($nonSystemNotes ? $nonSystemNotes . "\n" : "") . "[System] Guest count changed from {$totalBase} to {$remainingQuantity}. Total adjusted to ₵" . number_format($newTotalAmount, 2) . " (Transferred {$splitQuantity} persons to new booking {$newBooking->booking_reference})";
            $booking->save();

            activity('booking')
                ->performedOn($booking)
                ->event('booking_split')
                ->withProperties([
                    'split_quantity' => $splitQuantity,
                    'remaining_quantity' => $remainingQuantity,
                    'new_booking_reference' => $newBooking->booking_reference,
                    'new_booking_id' => $newBooking->id,
                ])
                ->log("Booking split: {$splitQuantity} guests transferred to {$newBooking->booking_reference}.");

            return back()->with('success', "Booking successfully split. New booking: {$newBooking->booking_reference}. You can now assign a different driver to either group.");
        });
    }

    /**
     * Update the status of a booking.
     */
    public function updateStatus(Request $request, Booking $booking)
    {
        if ($booking->trip_status === 'in_progress' || $booking->return_trip_status === 'in_progress') {
            return back()->with('error', 'Cannot update status of a live trip.');
        }

        $request->validate([
            'status' => 'required|in:pending,confirmed,cancelled,completed',
        ]);

        $bookingsToUpdate = $request->propagate_group ? $booking->getSiblingBookings() : collect([$booking]);
        $updatedCount = 0;

        foreach ($bookingsToUpdate as $b) {
            if ($b->trip_status === 'in_progress' || $b->return_trip_status === 'in_progress') {
                continue;
            }

            $oldStatus = $b->status;
            $b->update(['status' => $request->status]);

            activity('booking')
                ->performedOn($b)
                ->event('status_updated')
                ->withProperties([
                    'status' => $request->status,
                    'old_status' => $oldStatus,
                ])
                ->log("Status changed from " . ucfirst($oldStatus) . " to " . ucfirst($request->status) . ($request->propagate_group ? ' (Group Update)' : ''));

            // Chauffeur Status Logic
            if ($b->chauffeur_id) {
                if (in_array($request->status, ['completed', 'cancelled'])) {
                    \Modules\Fleet\Models\Chauffeur::where('id', $b->chauffeur_id)->update(['status' => 'available']);
                }
            }

            // Logic for approval and account creation
            if ($request->status === 'confirmed' && $oldStatus !== 'confirmed') {
                $user = $b->user;
                $newAccountCreated = false;

                if (!$user) {
                    // Check if user exists by email
                    $user = \App\Models\User::where('email', $b->customer_email)->first();
                    
                    if (!$user) {
                        // Create new user account for guest
                        $user = \App\Models\User::create([
                            'name' => $b->customer_name,
                            'email' => $b->customer_email,
                            'phone' => $b->customer_phone,
                            'password' => Hash::make('password'),
                        ]);
                        // Attempt to assign role
                        try {
                            if (method_exists($user, 'assignRole')) {
                                $user->assignRole('Customer');
                            }
                        } catch (\Exception $e) {
                            \Log::warning('Could not assign Customer role: ' . $e->getMessage());
                        }
                        $newAccountCreated = true;
                    }
                    
                    // Link the booking to this user
                    $b->update(['user_id' => $user->id]);
                }

                // Activate user account
                $user->update(['status' => 'active']);
                
                // Send Approval and Account Emails
                try {
                    // Send Booking Approval Email
                    Mail::to($b->customer_email)->send(new \Modules\Booking\Emails\BookingApproved($b, $newAccountCreated));
                    
                    // Send System Notification (Bell + Email via Notification class)
                    $user->notify(new \App\Notifications\BookingConfirmedNotification($b));

                    // If a new account was created, send dedicated welcome/activation email
                    if ($newAccountCreated) {
                        Mail::to($b->customer_email)->send(new \Modules\Booking\Emails\CustomerAccountCreated($user, 'password'));
                    }
                } catch (\Exception $e) {
                    \Log::error('Approval/Account Mails failed: ' . $e->getMessage());
                }
            }
            $updatedCount++;
        }

        if ($request->propagate_group) {
            return back()->with('success', "Updated status of {$updatedCount} bookings in the group to " . ucfirst($request->status));
        }

        return back()->with('success', 'Booking status updated to ' . ucfirst($request->status));
    }

    /**
     * Cancel a booking with a reason.
     */
    public function cancelBooking(Request $request, Booking $booking)
    {
        if ($booking->trip_status === 'in_progress' || $booking->return_trip_status === 'in_progress') {
            return back()->with('error', 'Cannot cancel a live trip.');
        }

        $request->validate([
            'cancellation_reason' => 'required|string|max:1000',
        ]);

        $bookingsToUpdate = $request->propagate_group ? $booking->getSiblingBookings() : collect([$booking]);
        $cancelledCount = 0;

        foreach ($bookingsToUpdate as $b) {
            if ($b->trip_status === 'in_progress' || $b->return_trip_status === 'in_progress') {
                continue;
            }

            $oldStatus = $b->status;

            if (!in_array($oldStatus, ['pending', 'confirmed'])) {
                continue;
            }

            $b->update([
                'status' => 'cancelled',
                'previous_status' => $oldStatus,
                'cancellation_reason' => $request->cancellation_reason,
            ]);

            activity('booking')
                ->performedOn($b)
                ->event('status_updated')
                ->withProperties([
                    'status' => 'cancelled',
                    'old_status' => $oldStatus,
                    'cancellation_reason' => $request->cancellation_reason,
                    'previous_status' => $oldStatus,
                ])
                ->log("Booking cancelled" . ($request->propagate_group ? ' (Group Update)' : '') . ": " . $request->cancellation_reason);

            // Notify and release chauffeur if any
            if ($b->chauffeur_id) {
                $chauffeur = \Modules\Fleet\Models\Chauffeur::with('user')->find($b->chauffeur_id);
                if ($chauffeur && $chauffeur->user) {
                    try {
                        $chauffeur->user->notify(new \App\Notifications\BookingCancelledNotification($b, $request->cancellation_reason));
                    } catch (\Exception $e) {
                        \Log::error('Booking cancellation notification to driver failed: ' . $e->getMessage());
                    }
                }
                \Modules\Fleet\Models\Chauffeur::where('id', $b->chauffeur_id)->update(['status' => 'available']);
                \Modules\Fleet\Models\Chauffeur::syncStatus($b->chauffeur_id);
            }
            $cancelledCount++;
        }

        if ($request->propagate_group) {
            return back()->with('success', "Successfully cancelled {$cancelledCount} bookings in the group.");
        }

        return back()->with('success', 'Booking successfully cancelled.');
    }

    /**
     * Reverse booking cancellation.
     */
    public function reverseCancellation(Request $request, Booking $booking)
    {
        if ($booking->status !== 'cancelled') {
            return back()->with('error', 'Only cancelled bookings can be reversed.');
        }

        $bookingsToUpdate = $request->propagate_group ? $booking->getSiblingBookings() : collect([$booking]);
        $reversedCount = 0;

        foreach ($bookingsToUpdate as $b) {
            if ($b->status !== 'cancelled') {
                continue;
            }

            $targetStatus = $b->previous_status ?: 'pending';

            $b->update([
                'status' => $targetStatus,
                'previous_status' => null,
                'cancellation_reason' => null,
            ]);

            activity('booking')
                ->performedOn($b)
                ->event('status_updated')
                ->withProperties([
                    'status' => $targetStatus,
                    'old_status' => 'cancelled',
                ])
                ->log("Cancellation reversed" . ($request->propagate_group ? ' (Group Update)' : '') . ". Status restored to " . ucfirst($targetStatus));

            // Notify and sync chauffeur status if any
            if ($b->chauffeur_id) {
                $chauffeur = \Modules\Fleet\Models\Chauffeur::with('user')->find($b->chauffeur_id);
                if ($chauffeur && $chauffeur->user) {
                    try {
                        $chauffeur->user->notify(new \App\Notifications\BookingReversedNotification($b, $targetStatus));
                    } catch (\Exception $e) {
                        \Log::error('Booking reversal notification to driver failed: ' . $e->getMessage());
                    }
                }
                \Modules\Fleet\Models\Chauffeur::syncStatus($b->chauffeur_id);
            }
            $reversedCount++;
        }

        if ($request->propagate_group) {
            return back()->with('success', "Cancellation reversed successfully for {$reversedCount} bookings in the group. Status restored.");
        }

        return back()->with('success', 'Cancellation reversed successfully.');
    }

    public function schedule(Request $request, Booking $booking)
    {
        if (!auth()->user()->hasAnyRole(['Super Admin', 'Operations Admin'])) {
            abort(403, 'Unauthorized action.');
        }
 
        $request->validate([
            'scheduled_at' => 'required|date|after:now',
        ]);
 
        $newSchedule = \Carbon\Carbon::parse($request->scheduled_at);
 
        // Conflict Check: If chauffeur is already assigned, check if they are free at this new time
        if ($booking->chauffeur_id) {
            $conflicts = Booking::where('chauffeur_id', $booking->chauffeur_id)
                ->where('id', '!=', $booking->id)
                ->whereNotIn('status', ['cancelled', 'completed'])
                ->where('trip_status', '!=', 'completed')
                ->whereBetween('scheduled_at', [
                    $newSchedule->copy()->subHours(4),
                    $newSchedule->copy()->addHours(4)
                ])
                ->get();

            $conflict = $conflicts->first(function($c) use ($booking, $newSchedule) {
                if ($booking->isTourismBooking() && $c->isTourismBooking()) {
                    $pkg1 = $booking->items->first()?->bookable_id;
                    $pkg2 = $c->items->first()?->bookable_id;
                    $date1 = $newSchedule->format('Y-m-d');
                    $date2 = $c->scheduled_at ? $c->scheduled_at->format('Y-m-d') : null;
                    if ($pkg1 === $pkg2 && $date1 === $date2) {
                        return false; // same tour on the same day is not a conflict
                    }
                }
                return true;
            });
 
            if ($conflict) {
                return back()->with('error', "Scheduling Failed: The assigned chauffeur is already booked for trip {$conflict->booking_reference} at {$conflict->scheduled_at->format('M d, h:i A')}.");
            }
        }
 
        $siblings = collect([$booking]);
        if ($booking->isTourismBooking()) {
            $siblings = $booking->getSiblingBookings();
        }

        $isAdjustment = $booking->scheduled_at !== null;

        foreach ($siblings as $sibling) {
            $oldSiblingScheduledAt = $sibling->scheduled_at;
            $sibling->update([
                'scheduled_at' => $request->scheduled_at,
                'driver_schedule_status' => 'pending',
                'customer_schedule_status' => 'pending'
            ]);
            
            activity('booking')
                ->performedOn($sibling)
                ->event('schedule_updated')
                ->withProperties([
                    'scheduled_at' => $newSchedule->toDateTimeString(),
                    'old_scheduled_at' => $oldSiblingScheduledAt ? $oldSiblingScheduledAt->toDateTimeString() : null,
                ])
                ->log("Trip " . ($oldSiblingScheduledAt ? "rescheduled" : "scheduled") . " to " . $newSchedule->format('M d, Y h:i A'));
     
            // Notify Customer
            if ($sibling->user) {
                try {
                    $sibling->user->notify(new \App\Notifications\TripScheduledNotification($sibling, $isAdjustment));
                } catch (\Exception $e) {
                    \Log::error("Reschedule notification for customer failed: " . $e->getMessage());
                }
            }
        }
 
        // Notify Driver
        if ($booking->chauffeur && $booking->chauffeur->user) {
            try {
                $booking->chauffeur->user->notify(new \App\Notifications\TripScheduledNotification($booking, $isAdjustment));
            } catch (\Exception $e) {
                \Log::error("Reschedule notification for chauffeur failed: " . $e->getMessage());
            }
        }

        // Sync chauffeur status since status might revert to available if schedule reset to pending
        if ($booking->chauffeur_id) {
            \Modules\Fleet\Models\Chauffeur::syncStatus($booking->chauffeur_id);
        }
 
        return back()->with('success', 'Trip ' . ($isAdjustment ? 're-scheduled' : 'scheduled') . ' successfully.');
    }

    /**
     * Handle guest count change request from customer.
     */
    public function requestGuestIncrease(Request $request, Booking $booking)
    {
        $currentGuests = $booking->items->sum('quantity');
        
        $request->validate([
            'new_guest_count' => 'required|integer|min:1|not_in:' . $currentGuests,
            'reason' => 'nullable|string|max:500',
        ]);

        // Check if there's already a pending request
        $existingRequest = $booking->changeRequests()->where('status', 'pending')->where('type', 'guest_count')->first();
        if ($existingRequest) {
            return back()->with('error', 'You already have a pending request for guest count change.');
        }

        $typeText = $request->new_guest_count > $currentGuests ? 'increase' : 'decrease';

        BookingChangeRequest::create([
            'booking_id' => $booking->id,
            'user_id' => auth()->id(),
            'type' => 'guest_count',
            'old_value' => $currentGuests,
            'new_value' => $request->new_guest_count,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        activity('booking')
            ->performedOn($booking)
            ->event('change_request_created')
            ->withProperties([
                'old_value' => $currentGuests,
                'new_value' => $request->new_guest_count,
                'reason' => $request->reason,
            ])
            ->log("Customer requested guest count change from {$currentGuests} to {$request->new_guest_count}");

        // Notify all Super Admins
        $customerName = $booking->customer_name ?: ($booking->user->name ?? 'A customer');
        $superAdmins = User::role('Super Admin')->get();
        foreach ($superAdmins as $admin) {
            $admin->notify(new \App\Notifications\GenericNotification([
                'title' => 'Guest Count Modification Request',
                'message' => "{$customerName} has requested to {$typeText} guests from {$currentGuests} to {$request->new_guest_count} for booking {$booking->booking_reference}.",
                'type' => 'warning',
                'action_url' => route('admin.bookings.show', $booking),
                'action_text' => 'Review Request',
                'footer' => 'Please review and approve or reject this request.',
            ]));
        }

        return back()->with('success', "Your request to {$typeText} the number of guests has been submitted and is awaiting Super Admin approval.");
    }

    /**
     * Admin direct update of guest count.
     */
    public function updateGuestCount(Request $request, Booking $booking)
    {
        if (!auth()->user()->hasAnyRole(['Super Admin', 'Operations Admin'])) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'new_guest_count' => 'required|integer|min:1',
        ]);

        $this->performGuestCountUpdate($booking, $request->new_guest_count);

        return back()->with('success', 'Guest count updated successfully.');
    }

    /**
     * Approve a pending change request.
     */
    public function approveChangeRequest(BookingChangeRequest $changeRequest)
    {
        if (!auth()->user()->hasRole('Super Admin')) {
            abort(403, 'Only Super Admin can approve change requests.');
        }

        $booking = $changeRequest->booking;

        DB::transaction(function () use ($changeRequest, $booking) {
            if ($changeRequest->type === 'guest_count') {
                $this->performGuestCountUpdate($booking, $changeRequest->new_value);
            }

            $changeRequest->update([
                'status' => 'approved',
                'processed_at' => now(),
                'processed_by' => auth()->id(),
            ]);

            activity('booking')
                ->performedOn($booking)
                ->event('change_request_approved')
                ->withProperties([
                    'change_request_id' => $changeRequest->id,
                    'old_value' => $changeRequest->old_value,
                    'new_value' => $changeRequest->new_value,
                ])
                ->log("Change request approved (guest count {$changeRequest->old_value} → {$changeRequest->new_value})");
        });

        // Notify Customer
        if ($booking->user) {
            $booking->user->notify(new \App\Notifications\GenericNotification([
                'title' => 'Guest Count Change Approved',
                'message' => "Your request to change guests to {$changeRequest->new_value} for booking {$booking->booking_reference} has been approved.",
                'type' => 'success',
                'action_url' => route('admin.bookings.show', $booking),
                'booking_id' => $booking->id,
            ]));
        }

        return back()->with('success', 'Change request approved successfully.');
    }

    /**
     * Reject a pending change request.
     */
    public function rejectChangeRequest(Request $request, BookingChangeRequest $changeRequest)
    {
        if (!auth()->user()->hasRole('Super Admin')) {
            abort(403, 'Only Super Admin can reject change requests.');
        }

        $changeRequest->update([
            'status' => 'rejected',
            'admin_notes' => $request->admin_notes,
            'processed_at' => now(),
            'processed_by' => auth()->id(),
        ]);

        $booking = $changeRequest->booking;
        activity('booking')
            ->performedOn($booking)
            ->event('change_request_rejected')
            ->withProperties([
                'change_request_id' => $changeRequest->id,
                'admin_notes' => $request->admin_notes,
                'old_value' => $changeRequest->old_value,
                'new_value' => $changeRequest->new_value,
            ])
            ->log("Change request rejected");

        // Notify Customer
        if ($changeRequest->booking->user) {
            $changeRequest->booking->user->notify(new \App\Notifications\GenericNotification([
                'title' => 'Guest Change Rejected',
                'message' => "Your request to change the number of guests for booking {$changeRequest->booking->booking_reference} has been rejected.",
                'type' => 'error',
                'action_url' => route('admin.bookings.show', $changeRequest->booking),
                'booking_id' => $changeRequest->booking->id,
            ]));
        }

        return back()->with('success', 'Change request rejected.');
    }

    /**
     * Helper to perform the actual guest count update and recalculate price.
     */
    private function performGuestCountUpdate(Booking $booking, $newCount)
    {
        $currentCount = $booking->items->sum('quantity');
        if ($currentCount == 0) return;

        DB::transaction(function () use ($booking, $newCount, $currentCount) {
            // Recalculate Total Amount proportionally
            // Formula: new_total = (old_total / old_guests) * new_guests
            $newTotal = round(($booking->total_amount / $currentCount) * $newCount, 2);

            $booking->update([
                'total_amount' => $newTotal,
                'notes' => ($booking->notes ? $booking->notes . "\n" : "") . "[System] Guest count changed from {$currentCount} to {$newCount}. Total adjusted to ₵" . number_format($newTotal, 2)
            ]);

            activity('booking')
                ->performedOn($booking)
                ->event('guest_count_updated')
                ->withProperties([
                    'old_value' => $currentCount,
                    'new_value' => $newCount,
                    'new_total_amount' => $newTotal,
                ])
                ->log("Guest count changed from {$currentCount} to {$newCount}. Total adjusted to ₵" . number_format($newTotal, 2));

            // Update individual items (assuming simple split for now, or just update the first/only item)
            // If it's a tourism package, we usually have one main item.
            foreach ($booking->items as $item) {
                // If multiple items, this simple logic might need refinement, 
                // but for tourism it's usually 1 package item.
                $item->update(['quantity' => $newCount]);
            }
        });
    }

    public function updatePaymentStatus(Request $request, Booking $booking)
    {
        if ($booking->trip_status === 'in_progress' || $booking->return_trip_status === 'in_progress') {
            return back()->with('error', 'Cannot update payment status of a live trip.');
        }

        if ($request->propagate_group) {
            $request->validate([
                'payment_status' => 'required|in:pending,paid,partially_paid,refund,refunded',
            ]);

            $bookingsToUpdate = $booking->getSiblingBookings();
            $updatedCount = 0;

            foreach ($bookingsToUpdate as $b) {
                if ($b->trip_status === 'in_progress' || $b->return_trip_status === 'in_progress') {
                    continue;
                }

                $oldPaymentStatus = $b->payment_status;

                if ($request->payment_status === 'pending') {
                    $b->payments()->delete();
                    $b->update([
                        'payment_status' => 'pending',
                        'partial_amount' => null
                    ]);

                    activity('booking')
                        ->performedOn($b)
                        ->event('payment_updated')
                        ->withProperties([
                            'payment_status' => 'pending',
                            'old_payment_status' => $oldPaymentStatus,
                        ])
                        ->log("Payment status reset to Pending (Group Update)");
                } elseif ($request->payment_status === 'paid') {
                    $remaining = floatval($b->total_amount) - floatval($b->partial_amount ?? 0);
                    if ($remaining > 0) {
                        $count = $b->payments()->count() + 1;
                        $suffixes = [1 => 'st', 2 => 'nd', 3 => 'rd'];
                        $suffix = $suffixes[$count] ?? 'th';
                        $note = $count . $suffix . ' Payment (Group Final)';

                        $newPaymentRecord = $b->payments()->create([
                            'amount' => $remaining,
                            'note' => $note,
                        ]);
                    }

                    $b->update([
                        'payment_status' => 'paid',
                        'partial_amount' => null
                    ]);

                    activity('booking')
                        ->performedOn($b)
                        ->event('payment_updated')
                        ->withProperties([
                            'payment_status' => 'paid',
                            'old_payment_status' => $oldPaymentStatus,
                        ])
                        ->log("Payment status marked as Paid (Group Update)");
                    
                    // If newly paid, auto-schedule organized tours
                    if ($oldPaymentStatus !== 'paid') {
                        foreach ($b->items as $item) {
                            if ($item->bookable_type === TourismPackage::class) {
                                $package = $item->bookable;
                                if ($package && $package->package_type === 'scheduled' && $package->departure_date) {
                                    // Set to 9:00 AM on the departure date
                                    $scheduledAt = \Carbon\Carbon::parse($package->departure_date->toDateString() . ' 09:00:00');

                                    $b->update([
                                        'scheduled_at' => $scheduledAt,
                                        'driver_schedule_status' => 'pending',
                                        'customer_schedule_status' => 'pending'
                                    ]);
                                    
                                    // Notify Customer
                                    try {
                                        if ($b->user) $b->user->notify(new \App\Notifications\TripScheduledNotification($b, false));
                                    } catch (\Exception $e) { \Log::error("Auto-schedule notification failed: " . $e->getMessage()); }
                                }
                            }
                        }

                        // If has interest token, increase capacity for organized tours
                        if ($b->interest_token) {
                            foreach ($b->items as $item) {
                                if ($item->bookable_type === TourismPackage::class) {
                                    $package = $item->bookable;
                                    if ($package && $package->package_type === 'scheduled') {
                                        $package->increment('max_guests', $item->quantity);
                                        \Log::info("Special Booking: Increased capacity for {$package->title} by {$item->quantity} due to special interest booking #{$b->booking_reference}");
                                    }
                                }
                            }
                        }
                    }
                } elseif ($request->payment_status === 'partially_paid') {
                    // Sibling bookings can only be partially paid one-by-one.
                    // For bulk updates, we skip partial payment status on other siblings 
                    // and only apply to the representative booking if a valid amount is provided.
                    if ($b->id === $booking->id) {
                        continue;
                    }
                } else { // refund, refunded
                    $b->update([
                        'payment_status' => $request->payment_status,
                        'partial_amount' => null
                    ]);

                    activity('booking')
                        ->performedOn($b)
                        ->event('payment_updated')
                        ->withProperties([
                            'payment_status' => $request->payment_status,
                            'old_payment_status' => $oldPaymentStatus,
                        ])
                        ->log("Payment status updated to " . ucfirst($request->payment_status) . " (Group Update)");
                }
                $updatedCount++;
            }

            if ($request->payment_status !== 'partially_paid') {
                return back()->with('success', "Updated payment status of {$updatedCount} bookings in the group to " . ucfirst($request->payment_status));
            }
        }

        $remaining = floatval($booking->total_amount) - floatval($booking->partial_amount ?? 0);

        $request->validate([
            'payment_status' => 'required|in:pending,paid,partially_paid,refund,refunded',
            'partial_amount' => 'nullable|numeric|min:0|max:' . $remaining,
        ]);

        $oldPaymentStatus = $booking->payment_status;

        if ($request->payment_status === 'pending') {
            $booking->payments()->delete();
            $booking->update([
                'payment_status' => 'pending',
                'partial_amount' => null
            ]);

            activity('booking')
                ->performedOn($booking)
                ->event('payment_updated')
                ->withProperties([
                    'payment_status' => 'pending',
                    'old_payment_status' => $oldPaymentStatus,
                ])
                ->log("Payment status reset to Pending");
        } elseif ($request->payment_status === 'partially_paid') {
            $newInstallment = floatval($request->partial_amount ?: 0.00);
            if ($newInstallment <= 0) {
                return back()->with('error', 'Please enter a valid payment amount greater than 0.');
            }

            $currentPaid = floatval($booking->partial_amount ?? 0);
            $newTotalPaid = $currentPaid + $newInstallment;

            // If it equals or exceeds total amount, automatically mark as paid
            if ($newTotalPaid >= floatval($booking->total_amount)) {
                $finalInstallment = floatval($booking->total_amount) - $currentPaid;
                $booking->update([
                    'payment_status' => 'paid',
                    'partial_amount' => null
                ]);
                
                $count = $booking->payments()->count() + 1;
                $suffixes = [1 => 'st', 2 => 'nd', 3 => 'rd'];
                $suffix = $suffixes[$count] ?? 'th';
                $note = $count . $suffix . ' Payment (Final)';

                $newPaymentRecord = $booking->payments()->create([
                    'amount' => $finalInstallment,
                    'note' => $note,
                ]);
            } else {
                $booking->update([
                    'payment_status' => 'partially_paid',
                    'partial_amount' => $newTotalPaid
                ]);

                activity('booking')
                    ->performedOn($booking)
                    ->event('payment_updated')
                    ->withProperties([
                        'payment_status' => $booking->payment_status,
                        'old_payment_status' => $oldPaymentStatus,
                        'amount' => $newInstallment,
                        'total_paid' => $newTotalPaid,
                    ])
                    ->log("Received installment payment of ₵" . number_format($newInstallment, 2) . ". Payment status: " . ucfirst($booking->payment_status));

                $count = $booking->payments()->count() + 1;
                $suffixes = [1 => 'st', 2 => 'nd', 3 => 'rd'];
                $suffix = $suffixes[$count] ?? 'th';
                $note = $count . $suffix . ' Payment';

                $newPaymentRecord = $booking->payments()->create([
                    'amount' => $newInstallment,
                    'note' => $note,
                ]);
            }
        } elseif ($request->payment_status === 'paid') {
            $currentPaid = floatval($booking->partial_amount ?? 0);
            $finalInstallment = floatval($booking->total_amount) - $currentPaid;

            $booking->update([
                'payment_status' => 'paid',
                'partial_amount' => null
            ]);

            activity('booking')
                ->performedOn($booking)
                ->event('payment_updated')
                ->withProperties([
                    'payment_status' => 'paid',
                    'old_payment_status' => $oldPaymentStatus,
                    'amount' => isset($finalInstallment) ? $finalInstallment : null,
                ])
                ->log("Received final/full payment of " . (isset($finalInstallment) ? "₵" . number_format($finalInstallment, 2) : "₵" . number_format($booking->total_amount, 2)) . ". Payment status: Paid");

            if ($finalInstallment > 0) {
                $count = $booking->payments()->count() + 1;
                $suffixes = [1 => 'st', 2 => 'nd', 3 => 'rd'];
                $suffix = $suffixes[$count] ?? 'th';
                $note = $count . $suffix . ' Payment (Final)';

                $newPaymentRecord = $booking->payments()->create([
                    'amount' => $finalInstallment,
                    'note' => $note,
                ]);
            }
        } else {
            // refund, refunded, etc.
            $booking->update([
                'payment_status' => $request->payment_status,
                'partial_amount' => null
            ]);

            activity('booking')
                ->performedOn($booking)
                ->event('payment_updated')
                ->withProperties([
                    'payment_status' => $request->payment_status,
                    'old_payment_status' => $oldPaymentStatus,
                ])
                ->log("Payment status updated to " . ucfirst($request->payment_status));
        }

        // If newly paid, auto-schedule organized tours
        if ($request->payment_status === 'paid' && $oldPaymentStatus !== 'paid') {
            foreach ($booking->items as $item) {
                if ($item->bookable_type === TourismPackage::class) {
                    $package = $item->bookable;
                    if ($package && $package->package_type === 'scheduled' && $package->departure_date) {
                        // Set to 9:00 AM on the departure date
                        $scheduledAt = \Carbon\Carbon::parse($package->departure_date->toDateString() . ' 09:00:00');

                        $booking->update([
                            'scheduled_at' => $scheduledAt,
                            'driver_schedule_status' => 'pending',
                            'customer_schedule_status' => 'pending'
                        ]);
                        
                        // Notify Customer
                        try {
                            if ($booking->user) $booking->user->notify(new \App\Notifications\TripScheduledNotification($booking, false));
                        } catch (\Exception $e) { \Log::error("Auto-schedule notification failed: " . $e->getMessage()); }
                    }
                }
            }

            // If has interest token, increase capacity for organized tours
            if ($booking->interest_token) {
                foreach ($booking->items as $item) {
                    if ($item->bookable_type === TourismPackage::class) {
                        $package = $item->bookable;
                        if ($package && $package->package_type === 'scheduled') {
                            $package->increment('max_guests', $item->quantity);
                            \Log::info("Special Booking: Increased capacity for {$package->title} by {$item->quantity} due to special interest booking #{$booking->booking_reference}");
                        }
                    }
                }
            }
        }

        // Notify Customer if payment was received (partial or paid)
        if (isset($newPaymentRecord)) {
            try {
                $customerUser = $booking->user;
                if ($customerUser) {
                    $customerUser->notify(new \App\Notifications\PaymentReceivedNotification(
                        $booking,
                        floatval($newPaymentRecord->amount),
                        $newPaymentRecord->note
                    ));
                }
            } catch (\Exception $e) {
                \Log::error('Failed to send PaymentReceivedNotification: ' . $e->getMessage());
            }
        }

        return back()->with('success', 'Payment status updated to ' . ucfirst($request->payment_status));
    }

    public function create(Request $request)
    {
        $type = $request->get('type');
        $id = $request->get('id');
        $item = null;

        if ($type === 'tourism') {
            $item = TourismPackage::findOrFail($id);
        } elseif ($type === 'fleet') {
            $item = Vehicle::findOrFail($id);
        } elseif ($type === 'transfer') {
            $item = AirportTransfer::findOrFail($id);
        }

        return view('booking::create', compact('item', 'type'));
    }

    /**
     * Customer ends trip via offline code
     */
    public function customerEndTrip(Request $request, Booking $booking)
    {
        // Security check: must be owner
        if (!auth()->user()->hasAnyRole(['Super Admin', 'Operations Admin'])) {
            if ($booking->user_id !== auth()->id() && $booking->customer_email !== auth()->user()->email) {
                abort(403, 'Unauthorized action.');
            }
        }

        $request->validate([
            'trip_end_code' => 'required|string|size:6'
        ]);

        if (strtoupper($request->trip_end_code) !== $booking->trip_end_code) {
            return back()->with('error', 'Invalid security code. Please ask the driver for the correct 6-character code.');
        }

        $endTime = now();
        $startTime = $booking->trip_started_at;
        $durationText = "N/A";
        
        if ($startTime) {
            $diff = $endTime->diff($startTime);
            $durationText = $diff->format('%h hrs %i mins');
        }

        $booking->update([
            'trip_status' => 'completed',
            'trip_ended_at' => $endTime,
            'trip_duration' => $durationText,
            'status' => 'completed',
            'trip_end_code' => null // Clear code after use
        ]);

        // Release chauffeur status
        if ($booking->chauffeur_id) {
            \Modules\Fleet\Models\Chauffeur::where('id', $booking->chauffeur_id)->update(['status' => 'available']);
        }

        // Notify Admins
        $admins = \App\Models\User::role(['Super Admin', 'Operations Admin'])->get();
        \Illuminate\Support\Facades\Notification::send($admins, new \App\Notifications\TripEnded($booking));

        return back()->with('success', 'Trip ended successfully on behalf of the driver! Total duration: ' . $durationText);
    }

    public function checkDuplicate(Request $request)
    {
        $email = $request->email;
        $phone = $request->phone;
        
        $exists = false;
        $message = "";

        // Check for existing users
        if ($email && \App\Models\User::where('email', $email)->exists()) {
            $exists = true;
            $message = "This email is already registered to an account. Please login or use a different email.";
        } elseif ($phone && \App\Models\User::where('phone', $phone)->exists()) {
            $exists = true;
            $message = "This phone number is already registered. Please login or use a different number.";
        }
        
        // Check for pending bookings with same email/phone
        if (!$exists) {
            $pendingBooking = \Modules\Booking\Models\Booking::where('status', 'pending')
                ->where(function($q) use ($email, $phone) {
                    if ($email) $q->where('customer_email', $email);
                    if ($phone) $q->orWhere('customer_phone', $phone);
                })
                ->exists();
            
            if ($pendingBooking) {
                $exists = true;
                $message = "You already have a pending booking with these details. Please wait for confirmation.";
            }
        }

        return response()->json([
            'exists' => $exists,
            'message' => $message
        ]);
    }

    public function store(Request $request)
    {
        $bookableType = $request->bookable_type;
        $bookableId = $request->bookable_id;
        $isTourism = $bookableType === 'Modules\Tourism\Models\TourismPackage' || $bookableType === 'tourism';
        $isFleet = $bookableType === 'Modules\Fleet\Models\Vehicle' || $bookableType === 'fleet' || $bookableType === 'Modules\\Fleet\\Models\\Vehicle';
        
        $isFixedTour = false;
        if ($isTourism && $bookableId) {
            $package = \Modules\Tourism\Models\TourismPackage::find($bookableId);
            if ($package && $package->package_type === 'fixed') {
                $isFixedTour = true;
            }
        }

        $leadDays = (int) (\App\Models\SystemSetting::where('key', 'fleet_rental_lead_days')->value('value') ?? 2);
        $tourLeadDays = (int) (\App\Models\SystemSetting::where('key', 'tourism_fixed_lead_days')->value('value') ?? 7);

        $request->validate([
            'bookable_type' => 'required',
            'bookable_id' => 'required',
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'required|string|max:20',
            'country' => 'nullable|string|max:100',
            'guest_type' => 'nullable|string',
            'group_name' => 'required_if:guest_type,Corporate Group,Others,School|nullable|string|max:255',
            'quantity' => 'nullable|integer|min:1',
            'rental_unit' => 'nullable|string|in:Hour,Day,Week',
            'is_self_drive' => 'nullable|boolean',
            'notes' => 'nullable|string',
            'options' => 'nullable|array',
            'options.airline' => 'nullable|string|max:255',
            'options.flight_number' => 'nullable|string|max:255',
            'options.flight_time' => 'nullable|string',
            'options.terminal' => 'nullable|string|max:255',
            'options.destination' => 'nullable|string|max:255',
            'options.custom_location' => 'nullable|string|max:255',
            'options.zone_id' => 'nullable|exists:transfer_zones,id',
            'options.transfer_type' => 'nullable|string|in:pickup,dropoff,both',
            'scheduled_at' => [
                ($isFixedTour || $isFleet) ? 'required' : 'nullable',
                'date',
                function ($attribute, $value, $fail) use ($isFixedTour, $isFleet, $leadDays, $tourLeadDays) {
                    if ($value) {
                        if ($isFixedTour) {
                            $minDate = now()->addDays($tourLeadDays)->startOfDay();
                            $selectedDate = \Carbon\Carbon::parse($value)->startOfDay();
                            if ($selectedDate->lt($minDate)) {
                                $fail("The schedule date must be at least {$tourLeadDays} days after the date of booking.");
                            }
                        } elseif ($isFleet) {
                            $minDate = now()->addDays($leadDays)->startOfDay();
                            $selectedDate = \Carbon\Carbon::parse($value)->startOfDay();
                            if ($selectedDate->lt($minDate)) {
                                $fail("The schedule date must be at least {$leadDays} days after the date of booking.");
                            }
                        }
                    }
                }
            ],
        ]);

        // Secondary check for duplicate pending bookings
        $duplicate = \Modules\Booking\Models\Booking::where('status', 'pending')
            ->where(function($q) use ($request) {
                $q->where('customer_email', $request->customer_email)
                  ->orWhere('customer_phone', $request->customer_phone);
            })
            ->first();

        if ($duplicate) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You already have a pending booking. Please wait for confirmation.',
                    'errors' => ['customer_email' => ['You already have a pending booking. Please wait for confirmation.']]
                ], 422);
            }
            return back()->withInput()->withErrors(['customer_email' => 'You already have a pending booking. Please wait for confirmation.']);
        }

        $bookableType = $request->bookable_type;
        $bookableId = $request->bookable_id;
        $quantity = $request->get('quantity', 1);
        $isSelfDrive = $request->get('is_self_drive', false);
        
        $isTourism = $bookableType === 'Modules\Tourism\Models\TourismPackage' || $bookableType === 'tourism';
        $isFleet = $bookableType === 'Modules\Fleet\Models\Vehicle' || $bookableType === 'fleet';
        $isTransfer = $bookableType === 'Modules\Fleet\Models\AirportTransfer' || $bookableType === 'transfer';

        // Map frontend type to model
        if ($isTransfer) {
            $model = AirportTransfer::class;
        } elseif ($isTourism) {
            $model = TourismPackage::class;
        } else {
            $model = Vehicle::class;
        }

        $relations = [];
        if ($isTransfer) {
            $relations = ['vehicleType', 'vehicle'];
        } elseif ($isFleet) {
            $relations = ['vehicleType'];
        }
        
        $item = $model::with($relations)->findOrFail($bookableId);

        // Correction: No one can book for an ongoing tour
        if ($isTourism && $item->organized_status === 'ongoing') {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking for this tour is closed as it has already started.'
                ], 422);
            }
            return back()->with('error', 'Booking for this tour is closed as it has already started.');
        }

        $price = 0;
        if ($isTourism) {
            $price = $item->price;
        }

        if ($isTransfer) {
            $base = (float) ($item->price ?? 0);
            $zoneId = $request->input('options.zone_id');
            $zoneExtra = 0;
            if ($zoneId) {
                $zone = TransferZone::find($zoneId);
                if ($zone) $zoneExtra = (float) $zone->additional_price;
            }
            
            $multiplier = ($request->input('options.transfer_type') === 'both') ? 2 : 1;
            $price = ($base + $zoneExtra) * $multiplier;
        }
        
        if ($isFleet) {
            $unit = $request->get('rental_unit', 'Hour');
            if ($unit === 'Hour') {
                $price = $item->vehicleType->base_hourly_rate ?? 0;
            } elseif ($unit === 'Day') {
                $price = $item->vehicleType->base_daily_rate ?? 0;
            } elseif ($unit === 'Week') {
                $price = ($item->vehicleType->base_daily_rate ?? 0) * 7;
            }
        }
        
        $total = $price * $quantity;

        $user = User::where('email', $request->customer_email)->first();

        $booking = \Modules\Booking\Models\Booking::create([
            'user_id' => $user ? $user->id : null,
            'customer_name' => $request->customer_name,
            'customer_email' => $request->customer_email,
            'customer_phone' => $request->customer_phone,
            'country' => $request->country ?? 'Ghana',
            'guest_type' => $request->guest_type,
            'group_name' => $request->group_name,
            'booking_reference' => 'BHV-' . strtoupper(Str::random(8)),
            'total_amount' => $total,
            'status' => 'pending',
            'payment_status' => 'pending',
            'is_self_drive' => $isSelfDrive,
            'chauffeur_id' => (!$isSelfDrive && $bookableType === 'fleet') ? $item->chauffeur_id : null,
            'notes' => $request->notes,
            'interest_token' => $request->interest_token,
            'scheduled_at' => (($isTourism && $item->package_type === 'fixed') || $isFleet) ? $request->scheduled_at : null,
            'customer_schedule_status' => ((($isTourism && $item->package_type === 'fixed') || $isFleet) && $request->scheduled_at) ? 'accepted' : 'pending',
        ]);

        BookingItem::create([
            'booking_id' => $booking->id,
            'bookable_id' => $item->id,
            'bookable_type' => $model,
            'quantity' => $quantity,
            'price' => $price,
            'options' => $isTransfer ? $request->options : null,
        ]);

        // Send Notifications
        // 1. Send Confirmation to customer
        try {
            Mail::to($request->customer_email)->send(new BookingConfirmation($booking));
        } catch (\Exception $e) {
            \Log::error('Customer Booking Confirmation Mail failed: ' . $e->getMessage());
        }

        // 2. Notify Admins via System (Bell Icon) and Email
        try {
            $admins = User::role(['Super Admin', 'Operations Admin'])->get();
            \Illuminate\Support\Facades\Notification::send($admins, new \App\Notifications\NewBookingNotification($booking));
        } catch (\Exception $e) {
            \Log::error('Admin Booking Notification failed: ' . $e->getMessage());
        }

        // Clear special booking session if it exists
        if (session()->has('specialBooking')) {
            session()->forget('specialBooking');
        }
        
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'booking_reference' => $booking->booking_reference,
                'scheduled_at' => $booking->scheduled_at ? $booking->scheduled_at->format('M d, Y \a\t h:i A') : null,
                'booking_id' => $booking->id,
                'customer_name' => $booking->customer_name,
                'view_booking_url' => route('admin.bookings.index')
            ]);
        }

        return redirect()->route('bookings.success')->with('reference', $booking->booking_reference);
    }

     public function success()
     {
         $reference = session('reference');
         $isTourism = false;
         $booking = null;
         
         if ($reference) {
             $booking = Booking::where('booking_reference', $reference)->with('items')->first();
             if ($booking) {
                 $isTourism = $booking->items->contains(function ($item) {
                     return $item->bookable_type === TourismPackage::class;
                 });
             }
         }
         
         return view('booking::success', compact('isTourism', 'booking'));
     }

    /**
     * Remove the specified booking from storage.
     */
    public function destroy(Booking $booking)
    {
        if (!auth()->user()->hasRole('Super Admin')) {
            abort(403, 'Unauthorized action.');
        }

        // Release chauffeur if one was assigned
        if ($booking->chauffeur_id) {
            \Modules\Fleet\Models\Chauffeur::where('id', $booking->chauffeur_id)->update(['status' => 'available']);
        }

        $booking->delete();

        return redirect()->route('admin.bookings.index')->with('success', 'Booking deleted successfully.');
    }

    public function resolveComplaint(Request $request, \Modules\Booking\Models\TripComplaint $complaint)
    {
        if (!auth()->user()->hasAnyRole(['Super Admin', 'Operations Admin'])) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'admin_response' => 'required|string',
            'status' => 'required|in:resolved,open'
        ]);

        $complaint->update([
            'admin_response' => $request->admin_response, // Keep for backward compatibility/summary
            'status' => $request->status,
            'resolved_at' => $request->status === 'resolved' ? now() : null
        ]);

        // Add to conversation thread
        $complaint->messages()->create([
            'user_id' => auth()->id(),
            'message' => $request->admin_response,
        ]);

        return back()->with('success', 'Complaint updated successfully.');
    }

    public function addComplaintMessage(Request $request, \Modules\Booking\Models\TripComplaint $complaint)
    {
        // Check if user is the customer of this booking OR an admin
        $booking = $complaint->booking;
        $isCustomer = auth()->id() === $booking->user_id || auth()->user()->email === $booking->customer_email;
        $isAdmin = auth()->user()->hasAnyRole(['Super Admin', 'Operations Admin']);

        if (!$isCustomer && !$isAdmin) {
            abort(403, 'Unauthorized action.');
        }

        if ($complaint->status === 'resolved' && !$isAdmin) {
            return back()->with('error', 'This issue has been resolved and closed for further comments.');
        }

        $request->validate([
            'message' => 'required|string',
            'image' => 'nullable|image|max:5000'
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('complaints', 'public');
        }

        $complaint->messages()->create([
            'user_id' => auth()->id(),
            'message' => $request->message,
            'image_path' => $imagePath,
        ]);

        return back()->with('success', 'Message added to conversation.');
    }

    public function submitComplaint(Request $request, Booking $booking)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'image' => 'nullable|image|max:5000'
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('complaints', 'public');
        }

        $complaint = \Modules\Booking\Models\TripComplaint::create([
            'booking_id' => $booking->id,
            'user_id' => auth()->id(),
            'subject' => $request->subject,
            'message' => $request->message,
            'image_path' => $imagePath,
            'status' => 'open'
        ]);

        // Add as first message in thread
        $complaint->messages()->create([
            'user_id' => auth()->id(),
            'message' => $request->message,
            'image_path' => $imagePath,
        ]);

        $admins = \App\Models\User::role(['Super Admin', 'Operations Admin'])->get();
        \Illuminate\Support\Facades\Notification::send($admins, new \App\Notifications\NewTripComplaint($complaint));

        return back()->with('success', 'Your complaint has been filed successfully.');
    }

    public function submitRating(Request $request, Booking $booking)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string'
        ]);

        \Modules\Booking\Models\DriverRating::updateOrCreate(
            ['booking_id' => $booking->id],
            [
                'chauffeur_id' => $booking->chauffeur_id,
                'user_id' => auth()->id() ?? $booking->user_id,
                'rating' => $request->rating,
                'comment' => $request->comment,
            ]
        );

        return back()->with('success', 'Thank you for your feedback!');
    }

    public function confirmSchedule(Request $request, Booking $booking)
    {
        // Security check: must be the owner of the booking
        if ($booking->user_id !== auth()->id() && $booking->customer_email !== auth()->user()->email) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'status' => 'required|in:accepted,declined',
        ]);

        $booking->update([
            'customer_schedule_status' => $request->status
        ]);

        // Notify Admins and Driver if declined
        if ($request->status === 'declined') {
            $admins = User::role(['Super Admin', 'Operations Admin'])->get();
            // \Illuminate\Support\Facades\Notification::send($admins, new \App\Notifications\ScheduleDeclinedByCustomer($booking));
            
            if ($booking->chauffeur && $booking->chauffeur->user) {
                // $booking->chauffeur->user->notify(new \App\Notifications\ScheduleDeclinedByCustomer($booking));
            }
        }

        $msg = $request->status === 'accepted' ? 'Schedule confirmed successfully!' : 'Schedule declined. Operations will contact you shortly.';
        return back()->with('success', $msg);
    }

    /**
     * Admin sets the return trip schedule date/time.
     */
    public function scheduleReturn(Request $request, Booking $booking)
    {
        if (!auth()->user()->hasAnyRole(['Super Admin', 'Operations Admin'])) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'return_scheduled_at' => 'required|date|after:now',
        ]);

        $updates = [
            'return_scheduled_at' => $request->return_scheduled_at,
            // Reset confirmations when schedule changes
            'return_driver_schedule_status' => 'pending',
            'return_customer_schedule_status' => 'pending',
        ];

        if ($booking->isTourismBooking()) {
            $siblings = $booking->getSiblingBookings();
            foreach ($siblings as $sibling) {
                $sibling->update($updates);
            }
        } else {
            $booking->update($updates);
        }

        // Notify Customer
        if ($booking->user) {
            try { $booking->user->notify(new \App\Notifications\TripScheduledNotification($booking, true)); } catch (\Exception $e) {}
        }

        // Notify Driver
        if ($booking->chauffeur && $booking->chauffeur->user) {
            try { $booking->chauffeur->user->notify(new \App\Notifications\TripScheduledNotification($booking, true)); } catch (\Exception $e) {}
        }

        return back()->with('success', 'Return trip scheduled successfully.');
    }

    /**
     * Customer confirms or declines the return trip schedule.
     */
    public function confirmReturnSchedule(Request $request, Booking $booking)
    {
        // Security check: must be owner
        if (!auth()->user()->hasAnyRole(['Super Admin', 'Operations Admin'])) {
            if ($booking->user_id !== auth()->id() && $booking->customer_email !== auth()->user()->email) {
                abort(403, 'Unauthorized action.');
            }
        }

        $request->validate([
            'status' => 'required|in:accepted,declined',
        ]);

        $booking->update([
            'return_customer_schedule_status' => $request->status
        ]);

        $msg = $request->status === 'accepted' ? 'Return schedule confirmed!' : 'Return schedule declined. Operations will contact you.';
        return back()->with('success', $msg);
    }

    /**
     * Merge multiple bookings into one.
     */
    public function mergeBookings(Request $request)
    {
        if (!auth()->user()->hasRole('Super Admin')) {
            abort(403, 'Only Super Admin can merge bookings.');
        }

        $bookingIds = $request->input('booking_ids');
        if (!$bookingIds || count($bookingIds) < 2) {
            return back()->with('error', 'Select at least two bookings to merge.');
        }

        $bookings = Booking::whereIn('id', $bookingIds)->with('items')->get();

        // 1. Validate same user
        $userIds = $bookings->pluck('user_id')->unique();
        if ($userIds->count() > 1) {
            return back()->with('error', 'Bookings must belong to the same user to be merged.');
        }

        // 2. Validate same scheduled date (CRITICAL requirement)
        $dates = $bookings->map(function($b) {
            return $b->scheduled_at ? $b->scheduled_at->format('Y-m-d') : 'unscheduled';
        })->unique();

        if ($dates->count() > 1) {
            return back()->with('error', 'Only bookings with the exact same scheduled date can be merged.');
        }

        // 3. Validate same package
        $packageIds = $bookings->map(function($b) {
            return $b->items->first()?->bookable_id;
        })->unique();

        if ($packageIds->count() > 1) {
            return back()->with('error', 'Only bookings for the same tour package can be merged.');
        }

        // 4. Validate all are PAID (New Requirement)
        $unpaidCount = $bookings->where('payment_status', '!=', 'paid')->count();
        if ($unpaidCount > 0) {
            return back()->with('error', 'Only fully PAID bookings can be merged. One or more selected bookings are unpaid.');
        }

        $masterBooking = $bookings->first();
        $otherBookings = $bookings->slice(1);

        \Illuminate\Support\Facades\DB::transaction(function () use ($masterBooking, $otherBookings) {
            $totalNewQuantity = $masterBooking->items->sum('quantity');
            $totalNewAmount = $masterBooking->total_amount;
            $combinedNotes = $masterBooking->notes ? $masterBooking->notes . "\n" : "";

            foreach ($otherBookings as $booking) {
                $totalNewQuantity += $booking->items->sum('quantity');
                $totalNewAmount += $booking->total_amount;
                if ($booking->notes) {
                    $combinedNotes .= "[Merged Notes from {$booking->booking_reference}]: " . $booking->notes . "\n";
                }
                
                // Delete secondary booking
                $booking->delete();
            }

            $masterBooking->update([
                'total_amount' => $totalNewAmount,
                'notes' => $combinedNotes . "[System] Merged " . ($otherBookings->count()) . " bookings into this record. Final guest count: {$totalNewQuantity}."
            ]);

            $mergedRefs = $otherBookings->pluck('booking_reference')->implode(', ');
            activity('booking')
                ->performedOn($masterBooking)
                ->event('bookings_merged')
                ->withProperties([
                    'merged_references' => $mergedRefs,
                    'master_reference' => $masterBooking->booking_reference,
                    'total_guests' => $totalNewQuantity,
                    'total_amount' => $totalNewAmount,
                ])
                ->log("Merged bookings ({$mergedRefs}) into this record. Final guest count: {$totalNewQuantity}.");

            // Update main item quantity
            $masterBooking->items->first()?->update(['quantity' => $totalNewQuantity]);
        });

        return back()->with('success', 'Bookings merged successfully into ' . $masterBooking->booking_reference);
    }
}
