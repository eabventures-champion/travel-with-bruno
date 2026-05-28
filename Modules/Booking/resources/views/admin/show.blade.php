@extends('admin::layouts.master')

@section('content')
<div class="page-header">
    <div class="page-title">
        <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 10px;">
            <a href="{{ route('admin.bookings.index') }}" class="btn-back" style="color: var(--text-muted); text-decoration: none;"><i class="fas fa-arrow-left"></i> Back to Bookings</a>
        </div>
        <h1 style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
            Booking Details: <span style="color: var(--primary);">
                {{ $booking->booking_reference }}
                @if($booking->payment_status === 'paid')
                    <i class="fas fa-star" style="color: #eab308; margin-left: 5px;" title="Fully Paid"></i>
                @endif
            </span>
            @if($booking->payment_status === 'paid' && $booking->status === 'confirmed' && $booking->trip_status === 'idle')
                <span style="font-size: 0.75rem; font-weight: 800; text-transform: uppercase; padding: 6px 15px; border-radius: 8px; background: rgba(245, 158, 11, 0.1); color: #d97706; border: 1px solid rgba(245, 158, 11, 0.3); margin-left: 10px; display: inline-flex; align-items: center; gap: 5px;">
                    <i class="fas fa-calendar-alt"></i> Upcoming Trip
                </span>
            @endif
        </h1>
        <p>Comprehensive overview of reservation and customer information.</p>
    </div>
    <div class="page-actions" style="display: flex; gap: 10px; align-items: center;">
        @hasanyrole('Super Admin|Operations Admin')
        @if($booking->status === 'cancelled')
        <div style="display: flex; gap: 10px; align-items: center;">
            <span style="padding: 6px 15px; border-radius: 8px; font-weight: 800; font-size: 0.75rem; text-transform: uppercase; background: #fee2e2; color: #991b1b; border: 1px solid rgba(0,0,0,0.05); cursor: not-allowed;">
                Cancelled
            </span>
            <form action="{{ route('bookings.reverse-cancellation', $booking) }}" method="POST" style="margin: 0;" onsubmit="return confirm('Are you sure you want to reverse this cancellation and restore the booking to its previous status ({{ ucfirst($booking->previous_status ?: 'pending') }})?')">
                @csrf
                <button type="submit" class="btn btn-outline" style="border-color: #10b981; color: #10b981; border-radius: 10px; font-weight: 700; height: 40px; padding: 0 15px; display: inline-flex; align-items: center; gap: 6px; font-size: 0.8rem; background: transparent; cursor: pointer; transition: all 0.2s;">
                    <i class="fas fa-undo"></i> Reverse Cancellation
                </button>
            </form>
        </div>
        @else
        <form action="{{ route('bookings.update-status', $booking) }}" method="POST">
            @csrf
            <select name="status" onchange="if(this.value === 'cancelled') { this.value = '{{ $booking->status }}'; document.getElementById('cancel-reason-modal').style.display = 'flex'; } else { this.form.submit(); }" class="form-control-sm" style="height: 40px; padding: 0 15px; font-weight: 700; background: {{ $booking->status === 'confirmed' ? '#dcfce7' : ($booking->status === 'pending' ? '#fef9c3' : '#fee2e2') }}; color: {{ $booking->status === 'confirmed' ? '#166534' : ($booking->status === 'pending' ? '#854d0e' : '#991b1b') }}; border-radius: 10px; border: 1px solid var(--border);">
                <option value="pending" {{ $booking->status === 'pending' ? 'selected' : '' }}>Pending Approval</option>
                <option value="confirmed" {{ $booking->status === 'confirmed' ? 'selected' : '' }}>Approve & Confirm</option>
                <option value="cancelled" {{ $booking->status === 'cancelled' ? 'selected' : '' }}>Cancel Booking</option>
                <option value="completed" {{ $booking->status === 'completed' ? 'selected' : '' }}>Mark Completed</option>
            </select>
        </form>
        @endif
        @else
        <span style="padding: 6px 15px; border-radius: 8px; font-weight: 800; font-size: 0.75rem; text-transform: uppercase; background: {{ $booking->status === 'confirmed' ? '#dcfce7' : ($booking->status === 'pending' ? '#fef9c3' : '#fee2e2') }}; color: {{ $booking->status === 'confirmed' ? '#166534' : ($booking->status === 'pending' ? '#854d0e' : '#991b1b') }}; border: 1px solid rgba(0,0,0,0.05);">
            {{ $booking->status === 'confirmed' ? 'Booking Confirmed' : ($booking->status === 'pending' ? 'Pending Approval' : ucfirst($booking->status)) }}
        </span>
        @endhasanyrole
        <button class="btn btn-secondary" onclick="window.print()"><i class="fas fa-print"></i> Print Details</button>
    </div>
</div>

@if($booking->status === 'cancelled')
<div class="card" style="padding: 25px; background: rgba(239, 68, 68, 0.08); border-radius: 24px; border: 1px solid rgba(239, 68, 68, 0.25); box-shadow: var(--shadow-sm); margin-top: 30px; display: flex; align-items: flex-start; gap: 20px;">
    <div style="width: 50px; height: 50px; border-radius: 50%; background: rgba(239, 68, 68, 0.15); display: flex; align-items: center; justify-content: center; font-size: 1.3rem; color: #ef4444; flex-shrink: 0;">
        <i class="fas fa-times-circle"></i>
    </div>
    <div style="flex: 1;">
        <h3 style="font-family: 'Outfit', sans-serif; margin: 0 0 10px 0; color: #ef4444; font-size: 1.15rem; font-weight: 800; display: flex; align-items: center; gap: 8px;">
            Cancelled Booking
        </h3>
        <p style="margin: 0; color: var(--text-main); font-size: 0.95rem; line-height: 1.6;">
            <strong>Reason for Cancellation:</strong> 
            <span style="font-style: italic; color: var(--text-muted);">"{{ $booking->cancellation_reason ?: 'No reason provided' }}"</span>
        </p>
        @if($booking->previous_status)
            <div style="margin-top: 10px; font-size: 0.8rem; color: var(--text-muted);">
                <i class="fas fa-info-circle"></i> Restorable to previous status: <strong>{{ ucfirst($booking->previous_status) }}</strong>
            </div>
        @endif
    </div>
</div>
@endif

@hasanyrole('Super Admin|Operations Admin')
<div id="cancel-reason-modal" style="display: none; position: fixed; inset: 0; background: rgba(0, 0, 0, 0.6); z-index: 10000; align-items: center; justify-content: center; padding: 20px; backdrop-filter: blur(4px); transition: all 0.3s ease;">
    <div class="card" style="width: 100%; max-width: 500px; padding: 30px; border-radius: 24px; background: var(--bg-card); border: 1px solid var(--border); box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3), 0 10px 10px -5px rgba(0, 0, 0, 0.3);">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 20px; color: #ef4444;">
            <div style="width: 40px; height: 40px; border-radius: 50%; background: rgba(239, 68, 68, 0.1); display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h3 style="font-family: 'Outfit', sans-serif; margin: 0; font-size: 1.25rem; font-weight: 800;">Cancel Booking</h3>
        </div>
        <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 25px; line-height: 1.5; margin-left: 20px; margin-right: 20px;">
            Are you sure you want to cancel booking <strong>{{ $booking->booking_reference }}</strong>? Please state the reason for this cancellation.
        </p>
        <form action="{{ route('bookings.cancel', $booking) }}" method="POST">
            @csrf
            <div style="margin-top: 25px; margin-bottom: 25px; margin-left: 20px; margin-right: 20px;">
                <label for="cancellation_reason" style="display: block; font-size: 0.8rem; font-weight: 800; text-transform: uppercase; color: var(--text-muted); margin-bottom: 12px; letter-spacing: 0.5px;">Reason for Cancellation</label>
                <textarea name="cancellation_reason" id="cancellation_reason" rows="4" placeholder="Enter cancellation reason..." required style="width: 100%; padding: 15px; border-radius: 12px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main); font-size: 0.9rem; font-family: 'Inter', sans-serif; resize: vertical; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='var(--border)'"></textarea>
            </div>
            <div style="display: flex; justify-content: flex-end; margin-top: 25px; margin-left: 20px; margin-right: 20px; margin-bottom: 20px;">
                <button type="button" onclick="document.getElementById('cancel-reason-modal').style.display = 'none';" class="btn btn-outline" style="border-radius: 12px; font-weight: 700; height: 42px; padding: 0 20px; display: inline-flex; align-items: center; justify-content: center; border-color: var(--border); color: var(--text-muted); margin-right: 12px; margin-bottom: 10px;">Keep Booking</button>
                <button type="submit" class="btn btn-primary" style="background: #ef4444; border: none; border-radius: 12px; font-weight: 700; height: 42px; padding: 0 20px; display: inline-flex; align-items: center; justify-content: center; gap: 6px; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.2); margin-bottom: 10px;">
                    <i class="fas fa-trash-alt"></i> Cancel Booking
                </button>
            </div>
        </form>
    </div>
</div>
@endhasanyrole

<div class="booking-detail-grid" style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px; margin-top: 30px;">
    <!-- Main Details Column -->
    <div style="display: flex; flex-direction: column; gap: 30px;" x-data="{ modal: null }">

        <!-- Pending Change Requests (Super Admin) -->
        @hasrole('Super Admin')
            @php $pendingRequests = $booking->changeRequests->where('status', 'pending'); @endphp
            @if($pendingRequests->count() > 0)
                <div class="card" style="padding: 25px; background: rgba(245, 158, 11, 0.1); border-radius: 24px; border: 1px solid rgba(245, 158, 11, 0.3); box-shadow: var(--shadow-sm);">
                    <h3 style="font-family: 'Outfit', sans-serif; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; color: #d97706; font-size: 1.1rem;">
                        <i class="fas fa-exclamation-circle"></i> Pending Change Requests ({{ $pendingRequests->count() }})
                    </h3>
                    <div style="display: flex; flex-direction: column; gap: 15px;">
                        @foreach($pendingRequests as $req)
                            <div style="padding: 15px; background: var(--bg-card); border-radius: 15px; border: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                                <div>
                                    <div style="font-size: 0.75rem; font-weight: 800; color: #d97706; text-transform: uppercase;">Type: {{ str_replace('_', ' ', ucfirst($req->type)) }}</div>
                                    <div style="font-size: 1rem; font-weight: 700; color: var(--text-main); margin-top: 5px;">
                                        Requested Change: <span style="color: #d97706;">{{ $req->old_value }} &rarr; {{ $req->new_value }}</span> Persons
                                    </div>
                                    @if($req->reason)
                                        <div style="font-size: 0.85rem; color: var(--text-muted); margin-top: 5px; font-style: italic;">"{{ $req->reason }}"</div>
                                    @endif
                                </div>
                                <div style="display: flex; gap: 10px;">
                                    <form action="{{ route('bookings.change-requests.approve', $req) }}" method="POST" style="margin: 0;">
                                        @csrf
                                        <button type="submit" class="btn btn-primary btn-sm" style="background: #10b981; border: none; font-weight: 700;">Approve</button>
                                    </form>
                                    <button @click="modal = 'reject-request-{{ $req->id }}'" class="btn btn-outline btn-sm" style="border-color: #ef4444; color: #ef4444; font-weight: 700;">Reject</button>
                                </div>
                            </div>

                            <!-- Reject Reason Modal -->
                            <template x-if="true">
                                <div x-show="modal === 'reject-request-{{ $req->id }}'" x-cloak style="position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 10000; display: flex; align-items: center; justify-content: center; padding: 20px;">
                                    <div class="card" style="width: 100%; max-width: 400px; padding: 25px; border-radius: 20px;" @click.away="modal = null">
                                        <h4 style="margin-bottom: 15px; color: #ef4444;">Reject Change Request</h4>
                                        <form action="{{ route('bookings.change-requests.reject', $req) }}" method="POST">
                                            @csrf
                                            <textarea name="admin_notes" rows="3" placeholder="Reason for rejection (sent to customer)..." required style="width: 100%; padding: 12px; border-radius: 12px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main); font-size: 0.85rem;"></textarea>
                                            <div style="display: flex; gap: 10px; margin-top: 20px;">
                                                <button type="button" @click="modal = null" class="btn btn-secondary" style="flex: 1;">Cancel</button>
                                                <button type="submit" class="btn btn-primary" style="flex: 1; background: #ef4444; border: none;">Confirm Reject</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </template>
                        @endforeach
                    </div>
                </div>
            @endif
        @endhasrole

        <!-- Booked Services Section -->
        <div class="card" style="padding: 30px; background: var(--bg-card); border-radius: 24px; box-shadow: var(--shadow-sm); border: 1px solid var(--border);">
            <h3 style="font-family: 'Outfit', sans-serif; margin-bottom: 25px; display: flex; align-items: center; justify-content: space-between; color: var(--text-main); flex-wrap: wrap; gap: 15px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-concierge-bell" style="color: var(--primary);"></i> Booked Services
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    @php 
                        $firstItem = $booking->items->first(); 
                        $isCustomer = auth()->check() && (auth()->id() === $booking->user_id || auth()->user()->email === $booking->customer_email);
                        $hasPendingRequest = $booking->changeRequests->where('status', 'pending')->count() > 0;
                    @endphp

                    @if($isCustomer && $booking->status === 'confirmed' && $booking->trip_status === 'idle')
                        <button @click="modal = 'request-guest-change'" class="btn btn-primary btn-sm" style="padding: 4px 12px; font-size: 0.65rem; border-radius: 8px; {{ $hasPendingRequest ? 'opacity: 0.7; cursor: not-allowed;' : '' }}" {{ $hasPendingRequest ? 'disabled' : '' }}>
                            <i class="fas fa-users-cog"></i> {{ $hasPendingRequest ? 'Request Pending' : 'Modify Group Size' }}
                        </button>
                    @endif

                    @if($firstItem && $firstItem->bookable_type === 'Modules\Tourism\Models\TourismPackage')
                        <span style="font-size: 0.65rem; font-weight: 800; text-transform: uppercase; padding: 4px 12px; border-radius: 8px; background: #0ea5e9; color: white; display: inline-flex; align-items: center; gap: 5px; box-shadow: 0 4px 6px rgba(14, 165, 233, 0.2);">
                            <i class="fas fa-umbrella-beach"></i>
                            {{ $firstItem->bookable->package_type === 'fixed' ? 'Fixed Tour' : 'Organized Tour' }}
                        </span>
                    @endif

                    <span style="font-size: 0.65rem; font-weight: 900; text-transform: uppercase; padding: 4px 12px; border-radius: 8px; background: var(--accent); color: white; display: inline-flex; align-items: center; gap: 5px; letter-spacing: 0.5px; box-shadow: 0 4px 6px rgba(245, 158, 11, 0.2);">
                        <i class="fas fa-tag" style="font-size: 0.6rem;"></i> {{ $booking->guest_type ?: 'Standard' }}
                    </span>
                    @if($booking->group_name)
                        <span style="font-size: 0.65rem; font-weight: 900; text-transform: uppercase; padding: 4px 12px; border-radius: 8px; background: var(--primary); color: white; display: inline-flex; align-items: center; gap: 5px; letter-spacing: 0.5px; box-shadow: 0 4px 6px rgba(30, 58, 138, 0.2);">
                            <i class="fas fa-building" style="font-size: 0.6rem;"></i> {{ $booking->group_name }}
                        </span>
                    @endif
                </div>
            </h3>
            
            <div style="display: flex; flex-direction: column; gap: 20px;">
                @foreach($booking->items as $item)
                    <div style="display: flex; gap: 20px; padding: 20px; background: var(--bg-main); border-radius: 20px; border: 1px solid var(--border);">
                        <div style="width: 120px; height: 90px; background: var(--bg-card); border-radius: 12px; overflow: hidden; display: flex; align-items: center; justify-content: center; font-size: 2rem; color: var(--text-muted); border: 1px solid var(--border);">
                            @if(str_contains($item->bookable_type, 'Tourism'))
                                <i class="fas fa-map-marked-alt"></i>
                            @else
                                <i class="fas fa-car"></i>
                            @endif
                        </div>
                        <div style="flex: 1;">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                                <div>
                                    <div style="margin-bottom: 8px; display: flex; flex-direction: column; gap: 8px; align-items: flex-start;">
                                        @if($item->bookable_type === 'Modules\Fleet\Models\AirportTransfer')
                                            <span style="font-size: 0.7rem; font-weight: 800; text-transform: uppercase; padding: 4px 12px; border-radius: 8px; background: #10b981; color: white; display: inline-block; box-shadow: 0 4px 6px rgba(16, 185, 129, 0.2);">
                                                <i class="fas fa-plane" style="margin-right: 5px;"></i>
                                                {{ ($item->bookable->category ?? 'airport') === 'airport' ? 'Airport Transfer' : 'General Transfer' }}
                                            </span>
                                        @elseif($item->bookable_type === 'Modules\Fleet\Models\Vehicle')
                                            <span style="font-size: 0.7rem; font-weight: 800; text-transform: uppercase; padding: 4px 12px; border-radius: 8px; background: #f59e0b; color: white; display: inline-block; box-shadow: 0 4px 6px rgba(245, 158, 11, 0.2);">
                                                <i class="fas fa-car" style="margin-right: 5px;"></i>
                                                Car Hiring Service
                                            </span>
                                        @endif
                                    </div>
                                    <h4 style="font-family: 'Outfit', sans-serif; margin: 5px 0; font-size: 1.3rem; color: var(--text-main); display: flex; flex-direction: column; align-items: flex-start; gap: 8px;">
                                        <span>{{ $item->bookable->title ?? ($item->bookable->airport_name ?? ($item->bookable->make ? $item->bookable->make . ' ' . $item->bookable->model : 'Service Name')) }}</span>
                                        @if(isset($item->bookable->license_plate))
                                            <span style="display: inline-block; font-size: 0.8rem; background: var(--secondary); color: white; padding: 3px 10px; border-radius: 6px; font-family: monospace; letter-spacing: 1px; font-weight: 800; line-height: 1;">{{ $item->bookable->license_plate }}</span>
                                        @endif
                                    </h4>
                                </div>
                                <div style="text-align: right;">
                                    <div style="font-size: 1.3rem; font-weight: 900; color: var(--primary);">₵{{ number_format($item->price, 2) }}</div>
                                    <div style="font-size: 0.85rem; color: var(--text-muted);">
                                        @if(str_contains($item->bookable_type, 'Tourism'))
                                            per person
                                        @elseif(str_contains($item->bookable_type, 'AirportTransfer'))
                                            per trip
                                        @else
                                            per hour
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            <div style="margin-top: 15px; display: flex; gap: 30px;">
                                <div>
                                    <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 700;">QUANTITY</div>
                                    <div style="font-weight: 700; color: var(--text-main);">{{ $item->quantity }} {{ str_contains($item->bookable_type, 'Tourism') ? 'Guests' : 'Units' }}</div>
                                </div>
                                <div>
                                    <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 700;">SUBTOTAL</div>
                                    <div style="font-weight: 700; color: var(--primary);">₵{{ number_format($item->price * $item->quantity, 2) }}</div>
                                </div>
                            </div>
                            
                            @if(!str_contains($item->bookable_type, 'Tourism') && (isset($item->options['flight_number']) || isset($item->options['airline']) || isset($item->options['flight_time']) || isset($item->options['terminal']) || isset($item->options['destination']) || isset($item->options['transfer_type'])))
                            <div style="margin-top: 15px; padding: 15px; background: rgba(37, 99, 235, 0.05); border-radius: 12px; border: 1px dashed rgba(37, 99, 235, 0.2);">
                                <h5 style="margin: 0 0 10px; color: var(--primary); font-size: 0.85rem; display: flex; align-items: center; gap: 8px;">
                                    <i class="fas fa-plane"></i> Flight & Transfer Logistics
                                </h5>
                                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 15px; font-size: 0.85rem;">
                                    @if(isset($item->options['transfer_type']))
                                    <div>
                                        <div style="color: var(--text-muted); font-weight: 700; font-size: 0.7rem; text-transform: uppercase;">Service Type</div>
                                        <div style="font-weight: 700; color: var(--accent); text-transform: capitalize;">{{ $item->options['transfer_type'] }}</div>
                                    </div>
                                    @endif
                                    @if(isset($item->options['airline']))
                                    <div>
                                        <div style="color: var(--text-muted); font-weight: 700; font-size: 0.7rem; text-transform: uppercase;">Airline</div>
                                        <div style="font-weight: 600; color: var(--text-main);">{{ $item->options['airline'] }}</div>
                                    </div>
                                    @endif
                                    @if(isset($item->options['flight_number']))
                                    <div>
                                        <div style="color: var(--text-muted); font-weight: 700; font-size: 0.7rem; text-transform: uppercase;">Flight No.</div>
                                        <div style="font-weight: 600; color: var(--text-main);">{{ $item->options['flight_number'] }}</div>
                                    </div>
                                    @endif
                                    @if(isset($item->options['terminal']))
                                    <div>
                                        <div style="color: var(--text-muted); font-weight: 700; font-size: 0.7rem; text-transform: uppercase;">Terminal</div>
                                        <div style="font-weight: 600; color: var(--text-main);">{{ $item->options['terminal'] }}</div>
                                    </div>
                                    @endif
                                    @if(isset($item->options['flight_time']))
                                    <div>
                                        <div style="color: var(--text-muted); font-weight: 700; font-size: 0.7rem; text-transform: uppercase;">Time</div>
                                        <div style="font-weight: 600; color: var(--text-main);">{{ \Carbon\Carbon::parse($item->options['flight_time'])->format('M d, Y @ h:i A') }}</div>
                                    </div>
                                    @endif
                                    @if(isset($item->options['zone_id']))
                                    @php $zone = \App\Models\TransferZone::find($item->options['zone_id']); @endphp
                                    <div>
                                        <div style="color: var(--text-muted); font-weight: 700; font-size: 0.7rem; text-transform: uppercase;">Pricing Zone</div>
                                        <div style="font-weight: 700; color: var(--primary);">{{ $zone->name ?? 'N/A' }}</div>
                                    </div>
                                    @endif
                                    @if(isset($item->options['custom_location']))
                                    <div style="grid-column: 1 / -1;">
                                        <div style="color: var(--text-muted); font-weight: 700; font-size: 0.7rem; text-transform: uppercase;">Specific Location</div>
                                        <div style="font-weight: 600; color: var(--text-main); font-size: 0.95rem;">{{ $item->options['custom_location'] }}</div>
                                    </div>
                                    @endif
                                    @if(isset($item->options['destination']))
                                    <div style="grid-column: 1 / -1;">
                                        <div style="color: var(--text-muted); font-weight: 700; font-size: 0.7rem; text-transform: uppercase;">Original Destination</div>
                                        <div style="font-weight: 600; color: var(--text-main); font-size: 0.95rem;">{{ $item->options['destination'] }}</div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
 
            <div style="margin-top: 30px; padding-top: 25px; border-top: 2px dashed var(--border); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                <div>
                    @if($booking->payment_status === 'paid')
                        <span style="display: inline-flex; align-items: center; gap: 6px; font-size: 0.8rem; font-weight: 800; text-transform: uppercase; padding: 6px 14px; border-radius: 10px; background: #dcfce7; color: #15803d; border: 1px solid rgba(21, 128, 61, 0.2); font-family: 'Outfit', sans-serif;">
                            <i class="fas fa-check-circle" style="font-size: 0.95rem;"></i> Fully Paid
                        </span>
                    @elseif($booking->payment_status === 'partially_paid')
                        <div style="display: flex; flex-direction: column; align-items: flex-start; gap: 4px;">
                            <span style="display: inline-flex; align-items: center; gap: 6px; font-size: 0.8rem; font-weight: 800; text-transform: uppercase; padding: 6px 14px; border-radius: 10px; background: #e0f2fe; color: #0369a1; border: 1px solid rgba(3, 105, 161, 0.2); font-family: 'Outfit', sans-serif;">
                                <i class="fas fa-chart-pie" style="font-size: 0.95rem;"></i> Partially Paid
                            </span>
                            <span style="font-size: 0.8rem; font-weight: 700; color: #0369a1; margin-left: 5px;">
                                Paid: ₵{{ number_format($booking->partial_amount ?? 0, 2) }}
                            </span>
                        </div>
                    @elseif($booking->payment_status === 'refund')
                        <span style="display: inline-flex; align-items: center; gap: 6px; font-size: 0.8rem; font-weight: 800; text-transform: uppercase; padding: 6px 14px; border-radius: 10px; background: #fee2e2; color: #b91c1c; border: 1px solid rgba(185, 28, 28, 0.2); font-family: 'Outfit', sans-serif;">
                            <i class="fas fa-undo-alt" style="font-size: 0.95rem;"></i> Refund Initiated
                        </span>
                    @elseif($booking->payment_status === 'refunded')
                        <span style="display: inline-flex; align-items: center; gap: 6px; font-size: 0.8rem; font-weight: 800; text-transform: uppercase; padding: 6px 14px; border-radius: 10px; background: #f1f5f9; color: #475569; border: 1px solid rgba(71, 85, 105, 0.2); font-family: 'Outfit', sans-serif;">
                            <i class="fas fa-check-double" style="font-size: 0.95rem;"></i> Fully Refunded
                        </span>
                    @else
                        <span style="display: inline-flex; align-items: center; gap: 6px; font-size: 0.8rem; font-weight: 800; text-transform: uppercase; padding: 6px 14px; border-radius: 10px; background: #fef9c3; color: #a16207; border: 1px solid rgba(161, 98, 7, 0.2); font-family: 'Outfit', sans-serif;">
                            <i class="fas fa-exclamation-circle" style="font-size: 0.95rem;"></i> Not Paid
                        </span>
                    @endif
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 0.9rem; font-weight: 700; color: var(--text-muted); margin-bottom: 5px;">GRAND TOTAL</div>
                    <div style="font-size: 2.2rem; font-weight: 900; color: var(--primary);">₵{{ number_format($booking->total_amount, 2) }}</div>
                </div>
            </div>

            @if($booking->payments->count() > 0)
            <div style="margin-top: 25px; padding: 20px; background: rgba(30, 58, 138, 0.02); border: 1px solid var(--border); border-radius: 16px;">
                <h4 style="font-family: 'Outfit', sans-serif; font-size: 0.85rem; color: var(--text-main); margin: 0 0 15px 0; display: flex; align-items: center; gap: 8px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 800;">
                    <i class="fas fa-history" style="color: var(--primary);"></i> Payment Installments History
                </h4>
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    @foreach($booking->payments as $payment)
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 15px; background: var(--bg-main); border: 1px solid var(--border); border-radius: 10px; flex-wrap: wrap; gap: 10px;">
                            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                                <div style="width: 28px; height: 28px; border-radius: 50%; background: rgba(37, 99, 235, 0.1); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 800;">
                                    {{ $loop->iteration }}
                                </div>
                                <div>
                                    <span style="font-weight: 700; color: var(--text-main); font-size: 0.85rem;">{{ $payment->note ?: $loop->iteration . ' Payment' }}</span>
                                    <span style="font-size: 0.75rem; color: var(--text-muted); margin-left: 10px;">
                                        <i class="far fa-clock"></i> {{ $payment->created_at->format('M d, Y @ h:i A') }}
                                    </span>
                                </div>
                            </div>
                            <div style="font-weight: 900; color: var(--primary); font-size: 0.95rem;">
                                ₵{{ number_format($payment->amount, 2) }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>



        <!-- Trip Schedule Information -->
        @if($booking->scheduled_at)
        <div class="card" style="padding: 30px; background: var(--bg-card); border-radius: 24px; box-shadow: var(--shadow-sm); border: 1px solid var(--border); border-left: 5px solid var(--primary);">
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 20px;">
                <div style="display: flex; align-items: center; gap: 20px;">
                    <div style="width: 60px; height: 60px; border-radius: 50%; background: rgba(30, 58, 138, 0.1); display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: var(--primary);">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div>
                        <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 800; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px;">Confirmed Schedule</div>
                        <div style="font-weight: 800; color: var(--text-main); font-size: 1.4rem;">
                            {{ $booking->scheduled_at->format('M d, Y') }} @ {{ $booking->scheduled_at->format('h:i A') }}
                        </div>
                        <div style="font-size: 0.85rem; color: var(--text-muted); margin-top: 5px;">
                            <i class="fas fa-clock"></i> Your trip is set to start at this time.
                        </div>
                    </div>
                </div>
                <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                    @if(auth()->check() && (auth()->id() === $booking->user_id || auth()->user()->email === $booking->customer_email) && $booking->customer_schedule_status === 'pending')
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <div style="font-size: 0.7rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase;">Action Required:</div>
                        <form action="{{ route('bookings.confirm-schedule', $booking) }}" method="POST" style="margin: 0; display: flex; gap: 10px;">
                            @csrf
                            <button type="submit" name="status" value="accepted" class="btn btn-primary" style="padding: 8px 15px; border-radius: 10px; font-size: 0.7rem; font-weight: 800; text-transform: uppercase;">
                                <i class="fas fa-check"></i> Accept Schedule
                            </button>
                            <button type="submit" name="status" value="declined" class="btn btn-outline" style="padding: 8px 15px; border-radius: 10px; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; border-color: #ef4444; color: #ef4444;" onclick="return confirm('Decline this schedule? Operations will contact you.')">
                                <i class="fas fa-times"></i> Decline
                            </button>
                        </form>
                    </div>
                    @else
                        @php
                            $isCustomer = auth()->check() && (auth()->id() === $booking->user_id || auth()->user()->email === $booking->customer_email);
                        @endphp
                        @if($booking->customer_schedule_status === 'accepted' && $booking->driver_schedule_status === 'accepted')
                            <div style="padding: 8px 15px; background: #dcfce7; color: #166534; border-radius: 10px; font-size: 0.75rem; font-weight: 800; text-transform: uppercase;">
                                <i class="fas fa-check-circle"></i> {{ $isCustomer ? 'Outbound Confirmed by You' : 'Outbound Fully Confirmed' }}
                            </div>
                        @elseif($booking->customer_schedule_status === 'accepted')
                            <div style="padding: 8px 15px; background: #dcfce7; color: #166534; border-radius: 10px; font-size: 0.75rem; font-weight: 800; text-transform: uppercase;">
                                <i class="fas fa-check-circle"></i> {{ $isCustomer ? 'Outbound Confirmed by You' : 'Outbound Confirmed by Customer' }}
                            </div>
                        @elseif($booking->customer_schedule_status === 'declined')
                            <div style="padding: 8px 15px; background: #fee2e2; color: #991b1b; border-radius: 10px; font-size: 0.75rem; font-weight: 800; text-transform: uppercase;">
                                <i class="fas fa-times-circle"></i> {{ $isCustomer ? 'Schedule Declined' : 'Declined by Customer' }}
                            </div>
                        @endif

                        @if($booking->status === 'confirmed' && $booking->customer_schedule_status === 'accepted' && $booking->driver_schedule_status === 'accepted')
                        <div style="padding: 8px 15px; background: var(--primary); color: white; border-radius: 10px; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; box-shadow: 0 4px 10px rgba(30, 58, 138, 0.2);">
                            <i class="fas fa-truck-pickup"></i> Ready for Pickup
                        </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
        @endif

        {{-- Return Trip Schedule Card (Tourism only) --}}
        @if($booking->isTourismBooking() && ($booking->trip_leg === 'return' || $booking->trip_status === 'in_progress') && $booking->return_scheduled_at)
        <div class="card" style="padding: 30px; background: var(--bg-card); border-radius: 24px; box-shadow: var(--shadow-sm); border: 1px solid var(--border); border-left: 5px solid #3b82f6;">
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 20px;">
                <div style="display: flex; align-items: center; gap: 20px;">
                    <div style="width: 60px; height: 60px; border-radius: 50%; background: rgba(59, 130, 246, 0.1); display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: #3b82f6;">
                        <i class="fas fa-plane-arrival"></i>
                    </div>
                    <div>
                        <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 800; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px;">Return Trip Schedule</div>
                        <div style="font-weight: 800; color: var(--text-main); font-size: 1.4rem;">
                            {{ $booking->return_scheduled_at->format('M d, Y') }} @ {{ $booking->return_scheduled_at->format('h:i A') }}
                        </div>
                        <div style="font-size: 0.85rem; color: var(--text-muted); margin-top: 5px;">
                            <i class="fas fa-undo-alt"></i> Your return trip is scheduled for this time.
                        </div>
                    </div>
                </div>
                <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                    @if(auth()->check() && (auth()->id() === $booking->user_id || auth()->user()->email === $booking->customer_email) && $booking->return_customer_schedule_status === 'pending')
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <div style="font-size: 0.7rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase;">Action Required:</div>
                        <form action="{{ route('bookings.confirm-return-schedule', $booking) }}" method="POST" style="margin: 0; display: flex; gap: 10px;">
                            @csrf
                            <button type="submit" name="status" value="accepted" class="btn" style="padding: 8px 15px; border-radius: 10px; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; background: #3b82f6; color: white; border: none;">
                                <i class="fas fa-check"></i> Accept Return
                            </button>
                            <button type="submit" name="status" value="declined" class="btn btn-outline" style="padding: 8px 15px; border-radius: 10px; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; border-color: #ef4444; color: #ef4444;" onclick="return confirm('Decline return schedule?')">
                                <i class="fas fa-times"></i> Decline
                            </button>
                        </form>
                    </div>
                    @else
                        @php
                            $isCustomer = auth()->check() && (auth()->id() === $booking->user_id || auth()->user()->email === $booking->customer_email);
                        @endphp
                        @if($booking->return_customer_schedule_status === 'accepted' && $booking->return_driver_schedule_status === 'accepted')
                            <div style="padding: 8px 15px; background: #dcfce7; color: #166534; border-radius: 10px; font-size: 0.75rem; font-weight: 800; text-transform: uppercase;">
                                <i class="fas fa-check-circle"></i> {{ $isCustomer ? 'Return Confirmed by You' : 'Return Fully Confirmed' }}
                            </div>
                        @elseif($booking->return_customer_schedule_status === 'accepted')
                            <div style="padding: 8px 15px; background: #dcfce7; color: #166534; border-radius: 10px; font-size: 0.75rem; font-weight: 800; text-transform: uppercase;">
                                <i class="fas fa-check-circle"></i> {{ $isCustomer ? 'Return Confirmed by You' : 'Return Confirmed by Customer' }}
                            </div>
                        @elseif($booking->return_customer_schedule_status === 'declined')
                            <div style="padding: 8px 15px; background: #fee2e2; color: #991b1b; border-radius: 10px; font-size: 0.75rem; font-weight: 800; text-transform: uppercase;">
                                <i class="fas fa-times-circle"></i> {{ $isCustomer ? 'Return Schedule Declined' : 'Return Declined by Customer' }}
                            </div>
                        @endif

                        @if($booking->return_customer_schedule_status === 'accepted' && $booking->return_driver_schedule_status === 'accepted')
                        <div style="padding: 8px 15px; background: #3b82f6; color: white; border-radius: 10px; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; box-shadow: 0 4px 10px rgba(59, 130, 246, 0.2);">
                            <i class="fas fa-truck-pickup"></i> Ready for Return
                        </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
        @endif

        <!-- Live Trip Status -->
        @if($booking->trip_status !== 'idle' || $booking->status === 'completed')
        <div class="card" style="padding: 30px; background: var(--bg-card); border-radius: 24px; box-shadow: var(--shadow-sm); border: 1px solid var(--border);">
            <h3 style="font-family: 'Outfit', sans-serif; margin-bottom: 25px; display: flex; align-items: center; gap: 10px; color: var(--text-main);">
                <i class="fas fa-satellite-dish" style="color: var(--accent);"></i> Live Trip Status
            </h3>
            
            <div style="display: flex; align-items: center; gap: 20px; padding: 20px; background: var(--bg-main); border-radius: 20px; border: 1px solid var(--border);">
                @if($booking->trip_status === 'in_progress' || ($booking->isTourismBooking() && $booking->return_trip_status === 'in_progress'))
                    @php
                        $isReturn = $booking->isTourismBooking() && $booking->return_trip_status === 'in_progress';
                        $startedAt = $isReturn ? $booking->return_started_at : $booking->trip_started_at;
                        $endCode = $isReturn ? $booking->return_end_code : $booking->trip_end_code;
                        $legText = $isReturn ? 'Return Trip' : ($booking->isTourismBooking() ? 'Outbound Trip' : 'Trip');
                    @endphp
                    <div style="width: 60px; height: 60px; border-radius: 50%; background: rgba(16, 185, 129, 0.1); display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: #10b981;">
                        <i class="fas fa-spinner fa-spin"></i>
                    </div>
                    <div style="flex: 1;">
                        <div style="font-weight: 800; color: var(--text-main); font-size: 1.1rem; margin-bottom: 5px;">{{ $legText }} is In Progress</div>
                        <div style="font-size: 0.85rem; color: var(--text-muted);">Your chauffeur has started the {{ strtolower($legText) }}. Have a safe journey!</div>
                        <div style="margin-top: 10px; font-size: 0.75rem; color: var(--text-muted);"><i class="fas fa-clock"></i> Started at: {{ $startedAt?->format('h:i A') }}</div>
                        @if($booking->chauffeur)
                        <div style="margin-top: 8px; font-size: 0.8rem; color: var(--text-main); font-weight: 600; display: flex; gap: 15px; flex-wrap: wrap;">
                            <span><i class="fas fa-user-tie" style="color: var(--primary);"></i> Chauffeur: {{ $booking->chauffeur->user->name }}</span>
                            @hasanyrole('Super Admin|Operations Admin')
                            <span style="color: var(--text-muted); font-weight: normal;"><i class="fas fa-phone-alt"></i> {{ $booking->chauffeur->user->phone ?? 'N/A' }}</span>
                            @endhasanyrole
                        </div>
                        @endif
                        @hasanyrole('Super Admin|Operations Admin')
                            @if($endCode)
                            <div style="margin-top: 8px; font-size: 0.8rem; color: #d97706; font-weight: 700; background: rgba(245,158,11,0.1); padding: 5px 10px; border-radius: 6px; display: inline-block;">
                                <i class="fas fa-key"></i> Offline End Code ({{ $isReturn ? 'Return' : 'Outbound' }}): {{ $endCode }}
                            </div>
                            @endif
                        @endhasanyrole
                    </div>
                    @if(auth()->check() && auth()->id() === $booking->user_id)
                    <div style="text-align: right; display: flex; flex-direction: column; gap: 10px;">
                        <button @click="modal = 'customer-end-trip'" class="btn btn-outline" style="border-color: #f59e0b; color: #d97706; border-radius: 12px; font-size: 0.85rem; padding: 10px 15px; display: flex; align-items: center; justify-content: center; gap: 5px;">
                            <i class="fas fa-power-off"></i> End Trip (Offline)
                        </button>
                        <button @click="modal = 'file-complaint'" class="btn btn-outline" style="border-color: #fecaca; color: #ef4444; border-radius: 12px; font-size: 0.85rem; padding: 10px 15px; display: flex; align-items: center; justify-content: center; gap: 5px;">
                            <i class="fas fa-exclamation-circle"></i> File Complaint
                        </button>
                    </div>
                    @endif

                @elseif($booking->isTourismBooking() && $booking->trip_status === 'completed' && $booking->trip_leg === 'return' && $booking->return_trip_status === 'idle')
                    <div style="width: 60px; height: 60px; border-radius: 50%; background: rgba(245, 158, 11, 0.1); display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: #d97706;">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div style="flex: 1;">
                        <div style="font-weight: 800; color: var(--text-main); font-size: 1.1rem; margin-bottom: 5px;">Arrived at Destination</div>
                        <div style="font-size: 0.85rem; color: var(--text-muted);">The outbound trip is complete. Enjoy your tour!</div>
                        <div style="margin-top: 10px; font-size: 0.75rem; color: var(--text-muted); display: flex; gap: 15px; flex-wrap: wrap;">
                            <span><i class="fas fa-play-circle" style="color: #10b981;"></i> Outbound Started: {{ $booking->trip_started_at?->format('h:i A') ?? 'N/A' }}</span>
                            <span><i class="fas fa-flag-checkered" style="color: #3b82f6;"></i> Outbound Ended: {{ $booking->trip_ended_at?->format('h:i A') }}</span>
                        </div>
                        @if($booking->chauffeur)
                        <div style="margin-top: 8px; font-size: 0.8rem; color: var(--text-main); font-weight: 600; display: flex; gap: 15px; flex-wrap: wrap;">
                            <span><i class="fas fa-user-tie" style="color: var(--primary);"></i> Chauffeur: {{ $booking->chauffeur->user->name }}</span>
                            @hasanyrole('Super Admin|Operations Admin')
                            <span style="color: var(--text-muted); font-weight: normal;"><i class="fas fa-phone-alt"></i> {{ $booking->chauffeur->user->phone ?? 'N/A' }}</span>
                            @endhasanyrole
                        </div>
                        @endif
                    </div>

                @elseif($booking->trip_status === 'completed' || $booking->status === 'completed')
                    <div style="width: 60px; height: 60px; border-radius: 50%; background: rgba(59, 130, 246, 0.1); display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: #3b82f6;">
                        <i class="fas fa-flag-checkered"></i>
                    </div>
                    <div style="flex: 1;">
                        <div style="font-weight: 800; color: var(--text-main); font-size: 1.1rem; margin-bottom: 5px;">{{ $booking->isTourismBooking() ? 'Entire Booking Completed' : 'Trip Completed' }}</div>
                        <div style="font-size: 0.85rem; color: var(--text-muted);">Thank you for traveling with us! Total Duration: {{ $booking->total_duration ?? 'N/A' }}</div>
                        
                        @if($booking->isTourismBooking() && $booking->return_ended_at)
                        <div style="margin-top: 10px; font-size: 0.75rem; color: var(--text-muted); display: flex; gap: 15px; flex-wrap: wrap; background: rgba(59,130,246,0.05); padding: 8px; border-radius: 8px;">
                            <span><strong>Return Leg:</strong></span>
                            <span><i class="fas fa-play-circle" style="color: #10b981;"></i> Started: {{ $booking->return_started_at?->format('h:i A') ?? 'N/A' }}</span>
                            <span><i class="fas fa-flag-checkered" style="color: #3b82f6;"></i> Ended: {{ $booking->return_ended_at?->format('h:i A') }}</span>
                            <span>Duration: {{ $booking->return_duration ?? 'N/A' }}</span>
                        </div>
                        @endif
                        
                        <div style="margin-top: 10px; font-size: 0.75rem; color: var(--text-muted); display: flex; gap: 15px; flex-wrap: wrap;">
                            <span><strong>Outbound Leg:</strong></span>
                            <span><i class="fas fa-play-circle" style="color: #10b981;"></i> Started: {{ $booking->trip_started_at?->format('h:i A') ?? 'N/A' }}</span>
                            <span><i class="fas fa-flag-checkered" style="color: #3b82f6;"></i> Ended: {{ $booking->trip_ended_at?->format('h:i A') }}</span>
                            <span>Duration: {{ $booking->trip_duration ?? 'N/A' }}</span>
                        </div>
                        
                        @if($booking->chauffeur)
                        <div style="margin-top: 8px; font-size: 0.8rem; color: var(--text-main); font-weight: 600; display: flex; gap: 15px; flex-wrap: wrap;">
                            <span><i class="fas fa-user-tie" style="color: var(--primary);"></i> Chauffeur: {{ $booking->chauffeur->user->name }}</span>
                            @hasanyrole('Super Admin|Operations Admin')
                            <span style="color: var(--text-muted); font-weight: normal;"><i class="fas fa-phone-alt"></i> {{ $booking->chauffeur->user->phone ?? 'N/A' }}</span>
                            @endhasanyrole
                        </div>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Customer Rating Section -->
            @if($booking->trip_status === 'completed' || $booking->status === 'completed')
                @if(!$booking->rating && auth()->check() && auth()->id() === $booking->user_id)
                <div style="margin-top: 25px; padding-top: 25px; border-top: 1px dashed var(--border);">
                    <h4 style="font-family: 'Outfit', sans-serif; margin-bottom: 15px; color: var(--text-main);">Rate Your Experience</h4>
                    <form action="{{ route('bookings.rating', $booking) }}" method="POST">
                        @csrf
                        <div style="display: flex; flex-direction: column; gap: 15px;">
                            <div style="display: flex; gap: 10px;" x-data="{ currentRating: 0, hoverRating: 0 }">
                                <template x-for="star in 5">
                                    <i class="fas fa-star" 
                                       @click="currentRating = star"
                                       @mouseover="hoverRating = star"
                                       @mouseleave="hoverRating = 0"
                                       :style="star <= (hoverRating || currentRating) ? 'color: #eab308; cursor: pointer; font-size: 1.5rem; transition: 0.2s;' : 'color: var(--border); cursor: pointer; font-size: 1.5rem; transition: 0.2s;'">
                                    </i>
                                </template>
                                <input type="hidden" name="rating" x-model="currentRating" required>
                            </div>
                            <textarea name="comment" rows="3" placeholder="Leave a comment about the chauffeur... (Optional)" style="width: 100%; padding: 15px; border-radius: 12px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main); font-size: 0.95rem; resize: vertical;"></textarea>
                            <div style="text-align: right;">
                                <button type="submit" class="btn btn-primary" style="padding: 12px 30px; border-radius: 12px; font-weight: 800;">Submit Rating</button>
                            </div>
                        </div>
                    </form>
                </div>
                @elseif($booking->rating)
                <div style="margin-top: 25px; padding-top: 25px; border-top: 1px dashed var(--border);">
                    <h4 style="font-family: 'Outfit', sans-serif; margin-bottom: 15px; color: var(--text-main);">Customer Feedback</h4>
                    <div style="padding: 15px; background: rgba(234, 179, 8, 0.05); border: 1px solid rgba(234, 179, 8, 0.2); border-radius: 12px;">
                        <div style="color: #eab308; margin-bottom: 10px; font-size: 1.2rem;">
                            @for($i=1; $i<=5; $i++)
                                <i class="fas fa-star" style="{{ $i <= $booking->rating->rating ? '' : 'color: var(--border);' }}"></i>
                            @endfor
                        </div>
                        <div style="font-size: 0.9rem; color: var(--text-main); font-style: italic;">"{{ $booking->rating->comment ?: 'No comment provided.' }}"</div>
                        <div style="margin-top: 10px; font-size: 0.75rem; color: var(--text-muted);">
                            Submitted {{ $booking->rating->created_at->diffForHumans() }}
                        </div>
                    </div>
                </div>
                @endif
            @endif
        </div>
        @endif

        <!-- Scheduling Section (Super Admin & Operations) -->
        @hasanyrole('Super Admin|Operations Admin')
        <div class="card" style="padding: 30px; background: var(--bg-card); border-radius: 24px; box-shadow: var(--shadow-sm); border: 1px solid var(--border);">
            <h3 style="font-family: 'Outfit', sans-serif; margin-bottom: 25px; display: flex; align-items: center; gap: 10px; color: var(--text-main);">
                <i class="fas fa-calendar-alt" style="color: var(--accent);"></i> {{ $booking->scheduled_at ? 'Adjust Trip Schedule' : 'Schedule Trip' }}
            </h3>
            
            <form action="{{ route('bookings.schedule', $booking) }}" method="POST">
                @csrf
                <div style="display: flex; gap: 20px; align-items: flex-end; flex-wrap: wrap;">
                    <div style="flex: 1; min-width: 250px;">
                        <label style="display: block; font-size: 0.75rem; font-weight: 800; color: var(--text-muted); margin-bottom: 8px; text-transform: uppercase;">Select Date & Time</label>
                        <input type="datetime-local" name="scheduled_at" value="{{ $booking->scheduled_at ? $booking->scheduled_at->format('Y-m-d\TH:i') : '' }}" required
                            style="width: 100%; padding: 12px; border-radius: 12px; border: 1px solid var(--border); background: {{ $booking->payment_status === 'pending' ? 'rgba(0,0,0,0.05)' : 'var(--bg-main)' }}; color: var(--text-main); font-weight: 600; cursor: {{ $booking->payment_status === 'pending' ? 'not-allowed' : 'text' }};"
                            {{ (in_array($booking->trip_status, ['in_progress', 'completed']) || $booking->payment_status === 'pending') ? 'disabled' : '' }}>
                    </div>
                    <button type="submit" class="btn" 
                        style="padding: 12px 30px; border-radius: 12px; font-weight: 800; height: 50px; background: {{ (in_array($booking->trip_status, ['in_progress', 'completed']) || $booking->payment_status === 'pending') ? '#9ca3af' : 'var(--primary)' }}; color: white; border: none; cursor: {{ (in_array($booking->trip_status, ['in_progress', 'completed']) || $booking->payment_status === 'pending') ? 'not-allowed' : 'pointer' }};" 
                        {{ (in_array($booking->trip_status, ['in_progress', 'completed']) || $booking->payment_status === 'pending') ? 'disabled' : '' }}>
                        {{ $booking->scheduled_at ? 'Update Schedule' : 'Schedule Now' }}
                    </button>
                </div>
                @if($booking->payment_status === 'pending')
                    <div style="margin-top: 15px; padding: 12px; background: #fff7ed; border: 1px solid #ffedd5; border-radius: 12px; display: flex; align-items: flex-start; gap: 8px;">
                        <i class="fas fa-exclamation-circle" style="color: #d97706; margin-top: 2px;"></i>
                        <span style="font-size: 0.75rem; color: #9a3412; font-weight: 700;">Payment must be confirmed before a trip can be scheduled.</span>
                    </div>
                @else
                    <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 10px;">
                        <i class="fas fa-info-circle"></i> This will notify both the customer and the assigned driver via email and system notification.
                    </p>
                @endif
            </form>

            {{-- Return Trip Schedule (Tourism only) --}}
            @if($booking->isTourismBooking() && ($booking->trip_leg === 'return' || $booking->trip_status === 'in_progress'))
            <div style="margin-top: 25px; padding-top: 25px; border-top: 1px dashed var(--border);">
                <h4 style="font-family: 'Outfit', sans-serif; margin-bottom: 15px; display: flex; align-items: center; gap: 10px; color: #3b82f6;">
                    <i class="fas fa-plane-arrival"></i> {{ $booking->return_scheduled_at ? 'Adjust Return Schedule' : 'Schedule Return Trip' }}
                </h4>
                <form action="{{ route('bookings.schedule-return', $booking) }}" method="POST">
                    @csrf
                    <div style="display: flex; gap: 20px; align-items: flex-end; flex-wrap: wrap;">
                        <div style="flex: 1; min-width: 250px;">
                            <label style="display: block; font-size: 0.75rem; font-weight: 800; color: var(--text-muted); margin-bottom: 8px; text-transform: uppercase;">Return Date & Time</label>
                            <input type="datetime-local" name="return_scheduled_at" value="{{ $booking->return_scheduled_at ? $booking->return_scheduled_at->format('Y-m-d\TH:i') : '' }}" required
                                style="width: 100%; padding: 12px; border-radius: 12px; border: 1px solid var(--border); background: {{ $booking->payment_status === 'pending' ? 'rgba(0,0,0,0.05)' : 'var(--bg-main)' }}; color: var(--text-main); font-weight: 600; cursor: {{ $booking->payment_status === 'pending' ? 'not-allowed' : 'text' }};"
                                {{ (in_array($booking->return_trip_status, ['in_progress', 'completed']) || $booking->payment_status === 'pending') ? 'disabled' : '' }}>
                        </div>
                        <button type="submit" class="btn" 
                            style="padding: 12px 30px; border-radius: 12px; font-weight: 800; height: 50px; background: {{ (in_array($booking->return_trip_status, ['in_progress', 'completed']) || $booking->payment_status === 'pending') ? '#9ca3af' : '#3b82f6' }}; color: white; border: none; cursor: {{ (in_array($booking->return_trip_status, ['in_progress', 'completed']) || $booking->payment_status === 'pending') ? 'not-allowed' : 'pointer' }};" 
                            {{ (in_array($booking->return_trip_status, ['in_progress', 'completed']) || $booking->payment_status === 'pending') ? 'disabled' : '' }}>
                            {{ $booking->return_scheduled_at ? 'Update Return' : 'Schedule Return' }}
                        </button>
                    </div>
                    <div style="display: flex; gap: 20px; margin-top: 12px; flex-wrap: wrap;">
                        <div style="font-size: 0.7rem; font-weight: 800; display: flex; align-items: center; gap: 4px; color: {{ $booking->return_driver_schedule_status === 'accepted' ? '#059669' : ($booking->return_driver_schedule_status === 'declined' ? '#dc2626' : '#d97706') }};">
                            <i class="fas fa-{{ $booking->return_driver_schedule_status === 'accepted' ? 'check-circle' : ($booking->return_driver_schedule_status === 'declined' ? 'times-circle' : 'clock') }}"></i>
                            Driver: {{ ucfirst($booking->return_driver_schedule_status) }}
                        </div>
                        <div style="font-size: 0.7rem; font-weight: 800; display: flex; align-items: center; gap: 4px; color: {{ $booking->return_customer_schedule_status === 'accepted' ? '#059669' : ($booking->return_customer_schedule_status === 'declined' ? '#dc2626' : '#d97706') }};">
                            <i class="fas fa-{{ $booking->return_customer_schedule_status === 'accepted' ? 'check-circle' : ($booking->return_customer_schedule_status === 'declined' ? 'times-circle' : 'clock') }}"></i>
                            Customer: {{ ucfirst($booking->return_customer_schedule_status) }}
                        </div>
                    </div>
                </form>
            </div>
            @endif
        </div>
        @endhasanyrole

        <!-- Customer Complaints & Feedback Section -->
        @if($booking->complaints->count() > 0 || (auth()->check() && (auth()->id() === $booking->user_id || auth()->user()->email === $booking->customer_email)))
        <div class="card" style="padding: 30px; background: var(--bg-card); border-radius: 24px; box-shadow: var(--shadow-sm); border: 1px solid var(--border); margin-bottom: 30px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                <h3 style="font-family: 'Outfit', sans-serif; margin: 0; display: flex; align-items: center; gap: 10px; color: #ef4444;">
                    <i class="fas fa-exclamation-triangle"></i> Customer Complaints
                </h3>
                @if(auth()->check() && (auth()->id() === $booking->user_id || auth()->user()->email === $booking->customer_email))
                    @if($booking->trip_started_at || ($booking->isTourismBooking() && $booking->trip_status !== 'idle'))
                    <button @click="modal = 'file-complaint'" class="btn btn-outline btn-sm" style="border-radius: 10px; padding: 8px 15px; border-color: #ef4444; color: #ef4444;">
                        <i class="fas fa-plus"></i> New Complaint
                    </button>
                    @endif
                @endif
            </div>

            <div style="display: flex; flex-direction: column; gap: 20px;">
                @forelse($booking->complaints as $complaint)
                <div style="padding: 20px; background: var(--bg-main); border-radius: 20px; border: 1px solid {{ $complaint->status === 'resolved' ? '#10b981' : '#ef4444' }}; position: relative; overflow: hidden;">
                    <div style="position: absolute; top: 0; right: 0; padding: 5px 15px; background: {{ $complaint->status === 'resolved' ? '#10b981' : '#ef4444' }}; color: white; font-size: 0.65rem; font-weight: 800; text-transform: uppercase; border-bottom-left-radius: 12px;">
                        {{ $complaint->status }}
                    </div>
                    
                    <div style="display: flex; flex-direction: column; gap: 15px;">
                        @foreach($complaint->messages as $message)
                        <div style="display: flex; flex-direction: column; gap: 5px; {{ $message->user_id === auth()->id() ? 'align-items: flex-end;' : 'align-items: flex-start;' }}">
                            <div style="max-width: 85%; padding: 12px 16px; border-radius: 15px; background: {{ $message->user_id === auth()->id() ? '#3b82f6' : 'var(--bg-card)' }}; color: {{ $message->user_id === auth()->id() ? 'white' : 'var(--text-main)' }}; border: 1px solid {{ $message->user_id === auth()->id() ? '#3b82f6' : 'var(--border)' }};">
                                <div style="font-size: 0.75rem; font-weight: 700; margin-bottom: 5px; opacity: 0.8;">
                                    {{ $message->user->name }} • {{ $message->created_at->diffForHumans() }}
                                </div>
                                <div style="font-size: 0.9rem; line-height: 1.5;">
                                    {{ $message->message }}
                                </div>
                                @if($message->image_path)
                                <div style="margin-top: 10px;">
                                    <a href="{{ asset('storage/' . $message->image_path) }}" target="_blank">
                                        <img src="{{ asset('storage/' . $message->image_path) }}" style="max-width: 100%; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2);">
                                    </a>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>

                    {{-- Reply Form --}}
                    @if($complaint->status !== 'resolved' || auth()->user()->hasAnyRole(['Super Admin', 'Operations Admin']))
                    <div style="margin-top: 20px; padding-top: 20px; border-top: 1px dashed var(--border);">
                        <form action="{{ route('bookings.complaints.message', $complaint) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div style="display: flex; gap: 10px; align-items: flex-start;">
                                <div style="flex: 1;">
                                    <textarea name="message" rows="2" style="width: 100%; padding: 12px; border-radius: 12px; border: 1px solid var(--border); background: var(--bg-card); color: var(--text-main); font-size: 0.85rem;" placeholder="Type your message here..." required></textarea>
                                    <div style="margin-top: 8px;">
                                        <label style="cursor: pointer; font-size: 0.75rem; color: #3b82f6; font-weight: 700; display: flex; align-items: center; gap: 5px;">
                                            <i class="fas fa-paperclip"></i> Attach Image
                                            <input type="file" name="image" style="display: none;">
                                        </label>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary" style="padding: 0 20px; border-radius: 12px; height: 42px; font-weight: 800;">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                    @else
                    <div style="margin-top: 20px; padding: 15px; background: rgba(16, 185, 129, 0.05); border-radius: 12px; border: 1px solid rgba(16, 185, 129, 0.2); text-align: center; color: #059669; font-size: 0.85rem; font-weight: 700;">
                        <i class="fas fa-lock"></i> This conversation has been closed by the Admin.
                    </div>
                    @endif

                    @hasanyrole('Super Admin|Operations Admin')
                    <div style="margin-top: 25px; padding: 20px; background: rgba(59, 130, 246, 0.05); border-radius: 15px; border: 1px solid rgba(59, 130, 246, 0.1);">
                        <div style="font-size: 0.75rem; font-weight: 800; color: #3b82f6; text-transform: uppercase; margin-bottom: 15px;">Admin Resolution Panel</div>
                        <form action="{{ route('bookings.complaints.resolve', $complaint) }}" method="POST">
                            @csrf
                            <div style="margin-bottom: 15px;">
                                <label style="display: block; font-size: 0.75rem; font-weight: 800; color: var(--text-muted); margin-bottom: 8px; text-transform: uppercase;">Official Final Feedback</label>
                                <textarea name="admin_response" required rows="2" style="width: 100%; padding: 12px; border-radius: 12px; border: 1px solid var(--border); background: var(--bg-card); color: var(--text-main); font-size: 0.85rem;" placeholder="Final words to resolve this issue...">{{ $complaint->admin_response }}</textarea>
                            </div>
                            <div style="display: flex; justify-content: flex-end; gap: 15px; align-items: center;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <span style="font-size: 0.75rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Status:</span>
                                    <select name="status" style="padding: 0 15px; border-radius: 12px; border: 1px solid var(--border); font-size: 0.8rem; font-weight: 700; background: var(--bg-card); color: var(--text-main); height: 42px; cursor: pointer;">
                                        <option value="open" {{ $complaint->status === 'open' ? 'selected' : '' }}>Open</option>
                                        <option value="resolved" {{ $complaint->status === 'resolved' ? 'selected' : '' }}>Resolved</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary" style="padding: 0 25px; border-radius: 12px; font-weight: 800; height: 42px; font-size: 0.85rem;">Resolve Issue</button>
                            </div>
                        </form>
                    </div>
                    @endhasanyrole
                </div>
                @empty
                <div style="text-align: center; padding: 40px; color: var(--text-muted); background: var(--bg-main); border-radius: 20px; border: 1px dashed var(--border);">
                    <i class="fas fa-check-circle" style="display: block; font-size: 2rem; margin-bottom: 10px; opacity: 0.3;"></i>
                    No complaints filed for this trip.
                </div>
                @endforelse
            </div>
        </div>
        @endif

        <!-- Shared Documents Section -->
        <div class="card" style="padding: 30px; background: var(--bg-card); border-radius: 24px; box-shadow: var(--shadow-sm); border: 1px solid var(--border); margin-bottom: 30px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                <h3 style="font-family: 'Outfit', sans-serif; margin: 0; display: flex; align-items: center; gap: 10px; color: var(--text-main);">
                    <i class="fas fa-file-pdf" style="color: var(--accent);"></i> Shared Documents
                </h3>
                @hasanyrole('Super Admin|Operations Admin')
                @if($booking->status !== 'cancelled')
                <button @click="modal = 'upload-document'" class="btn btn-primary btn-sm" style="border-radius: 10px; padding: 8px 15px;">
                    <i class="fas fa-plus"></i> Share New
                </button>
                @endif
                @endhasanyrole
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px;">
                @forelse($booking->documents as $doc)
                    @php
                        $user = auth()->user();
                        $canSee = false;
                        if ($user->hasAnyRole(['Super Admin', 'Operations Admin'])) {
                            $canSee = true;
                        } elseif ($user->id === $booking->user_id && ($doc->shared_with === 'customer' || $doc->shared_with === 'both')) {
                            $canSee = true;
                        } elseif ($booking->chauffeur && $user->id === $booking->chauffeur->user_id && ($doc->shared_with === 'driver' || $doc->shared_with === 'both')) {
                            $canSee = true;
                        }
                    @endphp

                    @if($canSee)
                    <div style="padding: 15px; background: var(--bg-main); border-radius: 15px; border: 1px solid var(--border); display: flex; flex-direction: column; gap: 10px;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <i class="fas fa-file-{{ in_array($doc->file_type, ['jpg', 'png', 'jpeg']) ? 'image' : 'alt' }}" style="font-size: 1.5rem; color: var(--primary);"></i>
                            <div style="overflow: hidden; flex: 1;">
                                <div style="font-weight: 700; font-size: 0.85rem; color: var(--text-main); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $doc->title }}">
                                    {{ $doc->title }}
                                </div>
                                <div style="font-size: 0.65rem; color: var(--text-muted); text-transform: uppercase;">
                                    {{ $doc->file_type }} • {{ $doc->shared_with }}
                                </div>
                            </div>
                        </div>
                        <div style="display: flex; gap: 5px; margin-top: auto;">
                            <a href="{{ route('bookings.documents.download', $doc) }}" class="btn btn-primary" style="flex: 1; padding: 6px; font-size: 0.8rem; border-radius: 8px;">
                                <i class="fas fa-download"></i>
                            </a>
                            @hasanyrole('Super Admin|Operations Admin')
                            <form action="{{ route('bookings.documents.destroy', $doc) }}" method="POST" onsubmit="return confirm('Delete this document?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline" style="padding: 6px; font-size: 0.8rem; border-radius: 8px; border-color: #ef4444; color: #ef4444;">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            @endhasanyrole
                        </div>
                    </div>
                    @endif
                @empty
                    <div style="grid-column: 1 / -1; text-align: center; padding: 20px; color: var(--text-muted); font-style: italic;">
                        No documents shared for this booking yet.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Complaint Modal -->
        <template x-if="true">
            <div x-show="modal === 'file-complaint'" x-cloak style="position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; display: flex; align-items: center; justify-content: center; padding: 20px;">
                <div class="card" style="width: 100%; max-width: 500px; padding: 30px; border-radius: 24px; position: relative;" @click.away="modal = null">
                    <button @click="modal = null" style="position: absolute; top: 20px; right: 20px; background: none; border: none; font-size: 1.2rem; color: var(--text-muted); cursor: pointer;">
                        <i class="fas fa-times"></i>
                    </button>
                    <h3 style="font-family: 'Outfit', sans-serif; margin-bottom: 25px; color: #ef4444;"><i class="fas fa-exclamation-triangle"></i> File a Complaint</h3>
                    <form action="{{ route('bookings.complaint', $booking) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div style="margin-bottom: 20px;">
                            <label style="display: block; font-size: 0.75rem; font-weight: 800; color: var(--text-muted); margin-bottom: 8px; text-transform: uppercase;">Issue Subject</label>
                            <input type="text" name="subject" required placeholder="E.g. Reckless driving, AC not working..." style="width: 100%; padding: 12px; border-radius: 12px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);">
                        </div>
                        <div style="margin-bottom: 20px;">
                            <label style="display: block; font-size: 0.75rem; font-weight: 800; color: var(--text-muted); margin-bottom: 8px; text-transform: uppercase;">Description</label>
                            <textarea name="message" required rows="4" style="width: 100%; padding: 12px; border-radius: 12px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);" placeholder="Describe the issue in detail..."></textarea>
                        </div>
                        <div style="margin-bottom: 25px;">
                            <label style="display: block; font-size: 0.75rem; font-weight: 800; color: var(--text-muted); margin-bottom: 8px; text-transform: uppercase;">Attachment (Optional)</label>
                            <input type="file" name="image" style="width: 100%; padding: 10px; border: 1px dashed var(--border); border-radius: 12px;">
                        </div>
                        <button type="submit" class="btn" style="width: 100%; height: 50px; border-radius: 12px; font-weight: 800; background: #ef4444; color: white; border: none;">Submit Complaint</button>
                    </form>
                </div>
            </div>
        </template>

        <!-- Upload Document Modal -->
        <template x-if="true">
            <div x-show="modal === 'upload-document'" x-cloak style="position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; display: flex; align-items: center; justify-content: center; padding: 20px;">
                <div class="card" style="width: 100%; max-width: 500px; padding: 30px; border-radius: 24px; position: relative;" @click.away="modal = null">
                    <button @click="modal = null" style="position: absolute; top: 20px; right: 20px; background: none; border: none; font-size: 1.2rem; color: var(--text-muted); cursor: pointer;">
                        <i class="fas fa-times"></i>
                    </button>
                    <h3 style="font-family: 'Outfit', sans-serif; margin-bottom: 25px;"><i class="fas fa-file-upload" style="color: var(--primary);"></i> Share Document</h3>
                    <form action="{{ route('bookings.documents.store', $booking) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div style="margin-bottom: 20px;">
                            <label style="display: block; font-size: 0.75rem; font-weight: 800; color: var(--text-muted); margin-bottom: 8px; text-transform: uppercase;">Document Title</label>
                            <input type="text" name="title" required placeholder="e.g. Trip Brochure, Route Map" style="width: 100%; padding: 12px; border-radius: 12px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);">
                        </div>
                        <div style="margin-bottom: 20px;">
                            <label style="display: block; font-size: 0.75rem; font-weight: 800; color: var(--text-muted); margin-bottom: 8px; text-transform: uppercase;">Share With</label>
                            <select name="shared_with" required style="width: 100%; padding: 12px; border-radius: 12px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);">
                                <option value="both">Both Customer & Driver</option>
                                <option value="customer">Customer Only</option>
                                <option value="driver">Driver Only</option>
                            </select>
                        </div>
                        <div style="margin-bottom: 25px;">
                            <label style="display: block; font-size: 0.75rem; font-weight: 800; color: var(--text-muted); margin-bottom: 8px; text-transform: uppercase;">File (PDF, Image, Doc)</label>
                            <input type="file" name="document" required style="width: 100%; padding: 10px; border: 1px dashed var(--border); border-radius: 12px;">
                        </div>
                        <button type="submit" class="btn btn-primary" style="width: 100%; height: 50px; border-radius: 12px; font-weight: 800;">Upload & Share</button>
                    </form>
                </div>
            </div>
        </template>

        <!-- Customer End Trip Modal -->
        <template x-if="true">
            <div x-show="modal === 'customer-end-trip'" x-cloak style="position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; display: flex; align-items: center; justify-content: center; padding: 20px;">
                <div @click.away="modal = null" class="card" style="width: 100%; max-width: 400px; margin-bottom: 0;">
                    <h3 style="font-family: 'Outfit', sans-serif; font-size: 1.3rem; margin-bottom: 15px; color: #d97706;"><i class="fas fa-power-off"></i> End Trip (Offline Driver)</h3>
                    <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 20px;">If your driver has lost internet connection, they can provide you with a 6-character code. Enter it here to officially end the trip.</p>
                    <form action="{{ route('bookings.customer-end', $booking) }}" method="POST">
                        @csrf
                        <div style="margin-bottom: 25px;">
                            <label style="display: block; font-size: 0.75rem; font-weight: 700; margin-bottom: 5px;">Offline Security Code</label>
                            <input type="text" name="trip_end_code" required maxlength="6" placeholder="E.g. TRP8X2" style="width: 100%; padding: 15px; border-radius: 8px; border: 2px solid var(--border); background: var(--bg-main); color: var(--text-main); font-family: monospace; font-size: 1.5rem; letter-spacing: 5px; text-transform: uppercase; text-align: center;">
                        </div>
                        <div style="display: flex; gap: 10px;">
                            <button type="button" @click="modal = null" class="btn btn-outline" style="flex: 1; padding: 12px; border-radius: 12px;">Cancel</button>
                            <button type="submit" class="btn" style="flex: 1; padding: 12px; border-radius: 12px; background: #d97706; color: white; border: none; font-weight: 800;">End Trip</button>
                        </div>
                    </form>
                </div>
            </div>
        </template>

        <!-- Request Guest Change Modal (Customer) -->
        <template x-if="true">
            <div x-show="modal === 'request-guest-change'" x-cloak style="position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; display: flex; align-items: center; justify-content: center; padding: 20px; backdrop-filter: blur(4px);">
                <div class="card" style="width: 100%; max-width: 450px; max-height: 90vh; overflow-y: auto; padding: 30px; border-radius: 24px; position: relative; scrollbar-width: thin; scrollbar-color: var(--primary) transparent;" @click.away="modal = null">
                    <button @click="modal = null" style="position: absolute; top: 20px; right: 20px; background: none; border: none; font-size: 1.2rem; color: var(--text-muted); cursor: pointer; z-index: 10;">
                        <i class="fas fa-times"></i>
                    </button>
                    <h3 style="font-family: 'Outfit', sans-serif; margin-bottom: 15px; color: var(--primary);">
                        <i class="fas fa-users-cog"></i> Modify Group Size
                    </h3>
                    <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 25px;">
                        Need to add or remove guests? Submit a request and our team will update your booking once approved.
                    </p>
                    
                    <form action="{{ route('bookings.request-increase', $booking) }}" method="POST">
                        @csrf
                        <div style="margin-bottom: 20px;">
                            <label style="display: block; font-size: 0.75rem; font-weight: 800; color: var(--text-muted); margin-bottom: 8px; text-transform: uppercase;">Current Guests</label>
                            <input type="text" value="{{ $booking->items->sum('quantity') }}" disabled style="width: 100%; padding: 12px; border-radius: 12px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-muted); font-weight: 700;">
                        </div>
                        <div style="margin-bottom: 20px;">
                            <label style="display: block; font-size: 0.75rem; font-weight: 800; color: var(--text-muted); margin-bottom: 8px; text-transform: uppercase;">New Total Guests</label>
                            <input type="number" name="new_guest_count" min="1" required style="width: 100%; padding: 12px; border-radius: 12px; border: 1px solid var(--primary); background: var(--bg-main); color: var(--text-main); font-weight: 700; font-size: 1.2rem;">
                        </div>
                        <div style="margin-bottom: 25px;">
                            <label style="display: block; font-size: 0.75rem; font-weight: 800; color: var(--text-muted); margin-bottom: 8px; text-transform: uppercase;">Reason / Message</label>
                            <textarea name="reason" rows="3" placeholder="Explain the change in guest count..." style="width: 100%; padding: 12px; border-radius: 12px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main); font-size: 0.9rem;"></textarea>
                        </div>
                        <div style="background: rgba(30, 58, 138, 0.05); padding: 15px; border-radius: 12px; border: 1px dashed var(--primary); margin-bottom: 25px;">
                            <div style="font-size: 0.75rem; color: var(--primary); font-weight: 700;">
                                <i class="fas fa-info-circle"></i> Note: The booking total will be recalculated based on the new guest count.
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary" style="width: 100%; height: 50px; border-radius: 12px; font-weight: 800; margin-bottom: 10px;">Submit Request</button>
                    </form>
                </div>
            </div>
        </template>

        <!-- Booking Activity Log Timeline Card -->
        @php
            $isAdmin = auth()->user()->hasAnyRole(['Super Admin', 'Operations Admin']);
            $activities = $booking->activities->sortByDesc('created_at');
            if (!$isAdmin) {
                $activities = $activities->filter(function($activity) {
                    return in_array($activity->event, [
                        'status_updated',
                        'guest_count_updated',
                        'payment_updated',
                        'schedule_updated',
                        'bookings_merged',
                        'booking_split'
                    ]) || ($activity->event === 'change_request_created' && $activity->causer_id === auth()->id());
                });
            }
        @endphp

        <div class="card" style="padding: 30px; background: var(--bg-card); border-radius: 24px; box-shadow: var(--shadow-sm); border: 1px solid var(--border); margin-top: 30px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                <h3 style="font-family: 'Outfit', sans-serif; margin: 0; display: flex; align-items: center; gap: 10px; color: var(--text-main);">
                    <i class="fas fa-history" style="color: var(--primary);"></i> Activity Log & History
                </h3>
                <span style="font-size: 0.75rem; background: rgba(59, 130, 246, 0.1); color: var(--primary); padding: 4px 10px; border-radius: 20px; font-weight: 700;">
                    {{ $activities->count() }} {{ Str::plural('Entry', $activities->count()) }}
                </span>
            </div>

            @if($activities->isEmpty())
                <div style="text-align: center; padding: 40px 20px; color: var(--text-muted); font-size: 0.9rem;">
                    <i class="fas fa-info-circle" style="display: block; font-size: 2rem; margin-bottom: 12px; opacity: 0.5; color: var(--primary);"></i>
                    No history recorded for this booking yet.
                </div>
            @else
                <!-- Timeline Container -->
                <div style="position: relative; padding-left: 30px; margin-top: 10px; max-height: 500px; overflow-y: auto; scrollbar-width: thin; scrollbar-color: var(--border) transparent; padding-right: 5px;">
                    <!-- Vertical Line -->
                    <div style="position: absolute; left: 11px; top: 10px; bottom: 10px; width: 2px; background: var(--border); z-index: 1;"></div>

                    @foreach($activities as $index => $activity)
                        @php
                            // Determine styling based on event
                            $icon = 'fa-edit';
                            $color = '#6b7280'; // gray-500
                            $bg = 'rgba(107, 114, 128, 0.1)';
                            
                            switch($activity->event) {
                                case 'status_updated':
                                    $status = $activity->properties['status'] ?? '';
                                    if($status === 'confirmed' || $status === 'completed') {
                                        $icon = 'fa-check-circle';
                                        $color = '#10b981'; // emerald-500
                                        $bg = 'rgba(16, 185, 129, 0.1)';
                                    } elseif($status === 'cancelled') {
                                        $icon = 'fa-times-circle';
                                        $color = '#ef4444'; // red-500
                                        $bg = 'rgba(239, 68, 68, 0.1)';
                                    } else {
                                        $icon = 'fa-hourglass-half';
                                        $color = '#f59e0b'; // amber-500
                                        $bg = 'rgba(245, 158, 11, 0.1)';
                                    }
                                    break;
                                case 'payment_updated':
                                    $paymentStatus = $activity->properties['payment_status'] ?? '';
                                    if($paymentStatus === 'paid') {
                                        $icon = 'fa-credit-card';
                                        $color = '#10b981';
                                        $bg = 'rgba(16, 185, 129, 0.1)';
                                    } elseif($paymentStatus === 'partially_paid') {
                                        $icon = 'fa-receipt';
                                        $color = '#d97706';
                                        $bg = 'rgba(217, 119, 6, 0.1)';
                                    } else {
                                        $icon = 'fa-wallet';
                                        $color = '#3b82f6'; // blue-500
                                        $bg = 'rgba(59, 130, 246, 0.1)';
                                    }
                                    break;
                                case 'guest_count_updated':
                                case 'change_request_approved':
                                    $icon = 'fa-users';
                                    $color = '#8b5cf6'; // violet-500
                                    $bg = 'rgba(139, 92, 246, 0.1)';
                                    break;
                                case 'change_request_created':
                                    $icon = 'fa-user-clock';
                                    $color = '#f59e0b';
                                    $bg = 'rgba(245, 158, 11, 0.1)';
                                    break;
                                case 'change_request_rejected':
                                    $icon = 'fa-user-times';
                                    $color = '#ef4444';
                                    $bg = 'rgba(239, 68, 68, 0.1)';
                                    break;
                                case 'schedule_updated':
                                    $icon = 'fa-calendar-alt';
                                    $color = '#0ea5e9'; // sky-500
                                    $bg = 'rgba(14, 165, 233, 0.1)';
                                    break;
                                case 'chauffeur_assigned':
                                    $icon = 'fa-id-card-alt';
                                    $color = '#ec4899'; // pink-500
                                    $bg = 'rgba(236, 72, 153, 0.1)';
                                    break;
                                case 'booking_split':
                                    $icon = 'fa-columns';
                                    $color = '#f43f5e'; // rose-500
                                    $bg = 'rgba(244, 63, 94, 0.1)';
                                    break;
                                case 'bookings_merged':
                                    $icon = 'fa-compress-alt';
                                    $color = '#06b6d4'; // cyan-500
                                    $bg = 'rgba(6, 182, 212, 0.1)';
                                    break;
                            }
                        @endphp

                        <!-- Timeline Item -->
                        <div style="position: relative; margin-bottom: 25px; z-index: 2;">
                            <!-- Timeline Dot (Icon) -->
                            <div style="position: absolute; left: -30px; top: 2px; width: 24px; height: 24px; border-radius: 50%; background: var(--bg-card); display: flex; align-items: center; justify-content: center; border: 2px solid {{ $color }}; box-shadow: 0 0 10px {{ $bg }};">
                                <i class="fas {{ $icon }}" style="font-size: 0.7rem; color: {{ $color }};"></i>
                            </div>

                            <!-- Timeline Content Card -->
                            <div style="background: var(--bg-main); border: 1px solid var(--border); border-radius: 16px; padding: 15px 20px; box-shadow: var(--shadow-sm); display: flex; flex-direction: column; gap: 8px;">
                                <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; flex-wrap: wrap;">
                                    <span style="font-weight: 700; font-size: 0.85rem; color: var(--text-main);">
                                        {{ $activity->description }}
                                    </span>
                                    <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600; display: inline-flex; align-items: center; gap: 4px; white-space: nowrap;">
                                        <i class="far fa-clock"></i> {{ $activity->created_at->diffForHumans() }}
                                    </span>
                                </div>

                                <!-- Extra properties details (old vs new value display) -->
                                @if($activity->event === 'payment_updated' && isset($activity->properties['amount']))
                                    <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600; background: rgba(0,0,0,0.02); padding: 8px 12px; border-radius: 8px; border-left: 3px solid {{ $color }}; display: inline-block;">
                                        <strong>Amount:</strong> ₵{{ number_format($activity->properties['amount'], 2) }}
                                        @if(isset($activity->properties['total_paid']))
                                            <span style="margin-left: 10px;">| <strong>Total Paid:</strong> ₵{{ number_format($activity->properties['total_paid'], 2) }}</span>
                                        @endif
                                    </div>
                                @elseif(in_array($activity->event, ['guest_count_updated', 'change_request_approved']) && isset($activity->properties['old_value']))
                                    <div style="display: flex; align-items: center; gap: 8px; font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">
                                        <span style="background: rgba(239, 68, 68, 0.1); color: #ef4444; padding: 2px 8px; border-radius: 6px;">{{ $activity->properties['old_value'] }} guests</span>
                                        <i class="fas fa-long-arrow-alt-right"></i>
                                        <span style="background: rgba(16, 185, 129, 0.1); color: #10b981; padding: 2px 8px; border-radius: 6px;">{{ $activity->properties['new_value'] }} guests</span>
                                    </div>
                                @elseif($activity->event === 'schedule_updated' && isset($activity->properties['scheduled_at']))
                                    <div style="display: flex; align-items: center; gap: 8px; font-size: 0.8rem; color: var(--text-muted); font-weight: 600; flex-wrap: wrap;">
                                        @if(isset($activity->properties['old_scheduled_at']) && $activity->properties['old_scheduled_at'])
                                            <span style="background: rgba(107, 114, 128, 0.1); color: var(--text-muted); padding: 2px 8px; border-radius: 6px;">
                                                {{ \Carbon\Carbon::parse($activity->properties['old_scheduled_at'])->format('M d, Y h:i A') }}
                                            </span>
                                            <i class="fas fa-long-arrow-alt-right"></i>
                                        @endif
                                        <span style="background: rgba(14, 165, 233, 0.1); color: #0ea5e9; padding: 2px 8px; border-radius: 6px;">
                                            {{ \Carbon\Carbon::parse($activity->properties['scheduled_at'])->format('M d, Y h:i A') }}
                                        </span>
                                    </div>
                                @endif

                                <!-- User (Causer) Details -->
                                <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600; display: flex; align-items: center; gap: 6px; border-top: 1px dashed var(--border); padding-top: 6px; margin-top: 4px;">
                                    <i class="fas fa-user-edit" style="color: var(--primary); font-size: 0.7rem;"></i>
                                    <span>By:</span>
                                    <span style="color: var(--text-main); font-weight: 700;">
                                        @if($activity->causer)
                                            {{ $activity->causer->name }} 
                                            <span style="font-size: 0.65rem; background: rgba(59, 130, 246, 0.1); color: var(--primary); padding: 2px 6px; border-radius: 4px; font-weight: 700; margin-left: 4px;">
                                                {{ $activity->causer->roles->first()?->name ?? 'User' }}
                                            </span>
                                        @else
                                            System Auto-Log
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- Sidebar Info Column -->
    <div style="display: flex; flex-direction: column; gap: 30px;">
        <!-- Customer Profile Card -->
        @hasanyrole('Super Admin|Operations Admin')
        <div class="card" style="padding: 30px; background: var(--bg-card); border-radius: 24px; box-shadow: var(--shadow-sm); border: 1px solid var(--border);">
            <h3 style="font-family: 'Outfit', sans-serif; margin-bottom: 25px; color: var(--text-main);">Customer Profile</h3>
            
            <div style="text-align: center; margin-bottom: 25px;">
                <div style="width: 80px; height: 80px; background: var(--primary); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; font-weight: 800; margin: 0 auto 15px;">
                    {{ substr($booking->customer_name ?: ($booking->user->name ?? 'G'), 0, 1) }}
                </div>
                <h4 style="font-family: 'Outfit', sans-serif; margin: 0; font-size: 1.2rem; color: var(--text-main);">{{ $booking->customer_name ?: ($booking->user->name ?? 'Guest User') }}</h4>
                <div style="font-size: 0.85rem; color: var(--text-muted);">{{ $booking->user ? 'Registered Account' : 'Guest Booking' }}</div>
            </div>

            <div style="display: flex; flex-direction: column; gap: 15px; padding: 20px; background: var(--bg-main); border-radius: 15px; border: 1px solid var(--border);">
                <div>
                    <div style="font-size: 0.7rem; color: var(--text-muted); font-weight: 800; text-transform: uppercase;">Email Address</div>
                    <div style="font-weight: 600; font-size: 0.9rem; overflow: hidden; text-overflow: ellipsis; color: var(--text-main);">{{ $booking->customer_email ?: ($booking->user->email ?? 'N/A') }}</div>
                </div>
                <div>
                    <div style="font-size: 0.7rem; color: var(--text-muted); font-weight: 800; text-transform: uppercase;">Phone Number</div>
                    <div style="font-weight: 600; font-size: 0.9rem; color: var(--text-main);">{{ $booking->customer_phone ?: ($booking->user->phone ?? 'N/A') }}</div>
                </div>
                @if($booking->guest_type || $booking->group_name)
                <div>
                    <div style="font-size: 0.7rem; color: var(--text-muted); font-weight: 800; text-transform: uppercase;">Category & Organization</div>
                    <div style="font-weight: 600; font-size: 0.9rem; color: var(--text-main);">
                        {{ $booking->guest_type ?: 'Standard' }}
                        @if($booking->group_name)
                            <span style="color: var(--accent);"> — {{ $booking->group_name }}</span>
                        @endif
                    </div>
                </div>
                @endif
                @if($booking->country)
                <div>
                    <div style="font-size: 0.7rem; color: var(--text-muted); font-weight: 800; text-transform: uppercase;">Country</div>
                    <div style="font-weight: 600; font-size: 0.9rem; color: var(--text-main);">{{ $booking->country }}</div>
                </div>
                @endif
                <div>
                    <div style="font-size: 0.7rem; color: var(--text-muted); font-weight: 800; text-transform: uppercase;">Booking Date</div>
                    <div style="font-weight: 600; font-size: 0.9rem; color: var(--text-main);">{{ $booking->created_at->format('M d, Y @ h:i A') }}</div>
                </div>
            </div>

            <div style="margin-top: 25px;">
                <a href="mailto:{{ $booking->customer_email }}" class="btn btn-secondary" style="width: 100%; display: block; text-align: center; text-decoration: none; margin-bottom: 10px;">
                    <i class="fas fa-envelope"></i> Send Email
                </a>
                <a href="tel:{{ $booking->customer_phone }}" class="btn btn-secondary" style="width: 100%; display: block; text-align: center; text-decoration: none;">
                    <i class="fas fa-phone-alt"></i> Call Customer
                </a>
            </div>
        </div>
        @endhasanyrole

        <!-- Special Notes -->
        <div class="card" style="padding: 30px; background: var(--bg-card); border-radius: 24px; box-shadow: var(--shadow-sm); border: 1px solid var(--border);">
            <h3 style="font-family: 'Outfit', sans-serif; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; color: var(--text-main);">
                <i class="fas fa-sticky-note" style="color: var(--accent);"></i> Customer Notes
            </h3>
            <div style="background: var(--bg-main); border: 1px solid var(--border); padding: 25px; border-radius: 20px; color: var(--text-main); line-height: 1.6; font-style: italic;">
                "{{ $booking->notes ?: 'No special notes or instructions provided for this booking.' }}"
            </div>
        </div>

        <!-- Chauffeur Assignment/Profile Card -->
        @if(!$booking->is_self_drive)
        <div class="card" style="padding: 30px; background: var(--bg-card); border-radius: 24px; box-shadow: var(--shadow-sm); border: 1px solid var(--border);">
            <h3 style="font-family: 'Outfit', sans-serif; margin-bottom: 20px; color: var(--text-main); display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-id-card" style="color: var(--primary);"></i> Chauffeur Details
            </h3>

            @hasanyrole('Super Admin|Operations Admin')
            @if($booking->status !== 'completed' && $booking->trip_status !== 'completed')
            <form action="{{ route('bookings.assign-chauffeur', $booking) }}" method="POST" style="margin-bottom: 20px;">
                @csrf
                <label style="display: block; font-size: 0.75rem; font-weight: 800; color: var(--text-muted); margin-bottom: 8px; text-transform: uppercase;">Assign Driver</label>
                <select name="chauffeur_id" onchange="this.form.submit()" 
                    {{ (in_array($booking->payment_status, ['pending', 'partially_paid']) || !$booking->scheduled_at) ? 'disabled' : '' }}
                    style="width: 100%; padding: 12px; border-radius: 12px; border: 1px solid var(--border); font-weight: 700; background: {{ (in_array($booking->payment_status, ['pending', 'partially_paid']) || !$booking->scheduled_at) ? 'rgba(0,0,0,0.05)' : 'var(--bg-main)' }}; color: var(--text-main); cursor: {{ (in_array($booking->payment_status, ['pending', 'partially_paid']) || !$booking->scheduled_at) ? 'not-allowed' : 'pointer' }};">
                    <option value="">No Driver Assigned</option>
                    @foreach($chauffeurs as $chauffeur)
                        <option value="{{ $chauffeur->id }}" {{ $booking->chauffeur_id == $chauffeur->id ? 'selected' : '' }}>
                            {{ $chauffeur->user->name }} ({{ ucfirst(str_replace('_', ' ', $chauffeur->status)) }})
                        </option>
                    @endforeach
                </select>
                @if(in_array($booking->payment_status, ['pending', 'partially_paid']))
                    <div style="margin-top: 10px; padding: 10px; background: #fff7ed; border: 1px solid #ffedd5; border-radius: 10px; display: flex; align-items: flex-start; gap: 8px;">
                        <i class="fas fa-exclamation-circle" style="color: #d97706; margin-top: 2px;"></i>
                        <span style="font-size: 0.75rem; color: #9a3412; font-weight: 600;">
                            @if($booking->payment_status === 'partially_paid')
                                Cannot assign driver to a booking with a partial payment. Full payment is required.
                            @else
                                Payment must be confirmed before assigning a chauffeur.
                            @endif
                        </span>
                    </div>
                @elseif(!$booking->scheduled_at)
                    <div style="margin-top: 10px; padding: 10px; background: #fff7ed; border: 1px solid #ffedd5; border-radius: 10px; display: flex; align-items: flex-start; gap: 8px;">
                        <i class="fas fa-exclamation-circle" style="color: #d97706; margin-top: 2px;"></i>
                        <span style="font-size: 0.75rem; color: #9a3412; font-weight: 600;">
                            Trip must be scheduled before assigning a chauffeur.
                        </span>
                    </div>
                @else
                    <small style="color: var(--text-muted); font-size: 0.75rem; display: block; margin-top: 5px;">Client will see the driver's profile once booking is confirmed.</small>
                @endif
            </form>
            @endif
            @endhasanyrole

            @if($booking->chauffeur)
                @if(in_array($booking->status, ['confirmed', 'completed']) || auth()->user()->hasAnyRole(['Super Admin', 'Operations Admin']))
                    <div style="display: flex; gap: 15px; align-items: center; padding: 15px; background: var(--bg-main); border-radius: 15px; margin-bottom: 15px; flex-wrap: wrap;">
                        <div style="width: 50px; height: 50px; background: var(--primary); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; font-weight: 800; flex-shrink: 0;">
                            {{ substr($booking->chauffeur->user->name, 0, 1) }}
                        </div>
                        <div style="flex: 1; min-width: 200px;">
                            <div style="font-weight: 700; color: var(--text-main); font-size: 1rem; margin-bottom: 4px;">{{ $booking->chauffeur->user->name }}</div>
                            <div style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 2px;"><i class="fas fa-envelope" style="margin-right: 5px; color: var(--primary);"></i> {{ $booking->chauffeur->user->email }}</div>
                            <div style="font-size: 0.8rem; color: var(--text-muted);"><i class="fas fa-phone-alt" style="margin-right: 5px; color: var(--primary);"></i> {{ $booking->chauffeur->user->phone ?? 'Contact unavailable' }}</div>
                        </div>
                        <div style="text-align: right;">
                            {{-- Unified Status Badge --}}
                            <div style="display: block; margin-bottom: 5px;">
                                @if($booking->scheduled_at)
                                    @if($booking->driver_schedule_status === 'accepted')
                                        <span style="font-size: 0.75rem; font-weight: 800; color: #059669; background: #ecfdf5; padding: 6px 12px; border-radius: 8px; border: 1px solid #10b981;">
                                            <i class="fas fa-check-circle"></i> Schedule Accepted
                                        </span>
                                    @elseif($booking->driver_schedule_status === 'declined')
                                        <span style="font-size: 0.75rem; font-weight: 800; color: #dc2626; background: #fef2f2; padding: 6px 12px; border-radius: 8px; border: 1px solid #ef4444;" title="Feedback: {{ $booking->driver_schedule_feedback }}">
                                            <i class="fas fa-times-circle"></i> Schedule Declined
                                        </span>
                                    @else
                                        <span style="font-size: 0.75rem; font-weight: 800; color: #d97706; background: #fff7ed; padding: 6px 12px; border-radius: 8px; border: 1px solid #f59e0b;">
                                            <i class="fas fa-clock"></i> Pending Response
                                        </span>
                                    @endif
                                @else
                                    <div style="font-size: 0.75rem; background: #fff7ed; padding: 4px 10px; border-radius: 8px; color: #d97706; font-weight: 700; display: inline-block; border: 1px solid #ffedd5;">
                                        <i class="fas fa-info-circle"></i> Status: {{ ucfirst(str_replace('_', ' ', $booking->chauffeur->status)) }}
                                    </div>
                                @endif
                            </div>

                            @hasanyrole('Super Admin|Operations Admin')
                            @if($booking->scheduled_at)
                            <div style="display: block; margin-top: 8px; border-top: 1px solid rgba(0,0,0,0.05); padding-top: 8px;">
                                <div style="font-size: 0.6rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; margin-bottom: 4px;">Customer Confirmation</div>
                                @if($booking->customer_schedule_status === 'accepted')
                                    <span style="font-size: 0.7rem; font-weight: 700; color: #059669;"><i class="fas fa-check-double"></i> Accepted</span>
                                @elseif($booking->customer_schedule_status === 'declined')
                                    <span style="font-size: 0.7rem; font-weight: 700; color: #dc2626;"><i class="fas fa-times-circle"></i> Declined</span>
                                @else
                                    <span style="font-size: 0.7rem; font-weight: 700; color: #d97706;"><i class="fas fa-clock"></i> Awaiting Response</span>
                                @endif
                            </div>
                            @endif
                            @endhasanyrole
                        </div>
                    </div>

                    @if($booking->driver_schedule_status === 'declined' && $booking->driver_schedule_feedback)
                    <div style="margin-bottom: 15px; padding: 12px; background: #fef2f2; border-radius: 12px; border: 1px solid #fee2e2; font-size: 0.8rem;">
                        <strong style="color: #dc2626; display: block; margin-bottom: 4px; font-size: 0.7rem; text-transform: uppercase;">Driver Feedback (Declined)</strong>
                        <span style="font-style: italic; color: #991b1b;">"{{ $booking->driver_schedule_feedback }}"</span>
                    </div>
                    @endif
                    <div style="font-size: 0.85rem; color: var(--text-main); background: var(--bg-main); padding: 15px; border-radius: 12px; border: 1px solid var(--border); margin-bottom: 15px;">
                        <strong style="display: block; margin-bottom: 5px; color: var(--primary); font-size: 0.7rem; text-transform: uppercase;">About Chauffeur</strong>
                        {{ $booking->chauffeur->bio ?: 'Highly professional and verified driver for your safety and comfort.' }}
                    </div>

                    @if($booking->chauffeur->vehicle)
                    <div style="padding: 20px; background: rgba(30, 58, 138, 0.03); border-radius: 20px; border: 1px solid var(--border); display: flex; flex-direction: column; gap: 15px;">
                        <div style="width: 100%; height: 160px; background: var(--bg-card); border-radius: 12px; overflow: hidden; border: 1px solid var(--border);">
                            <img src="{{ $booking->chauffeur->vehicle->image ? asset('storage/' . $booking->chauffeur->vehicle->image) : 'https://images.unsplash.com/photo-1549317661-bd32c8ce0db2?auto=format&fit=crop&q=80&w=400' }}" 
                                 style="width: 100%; height: 100%; object-fit: cover;">
                        </div>
                        <div style="flex: 1;">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">
                                <div>
                                    <div style="font-size: 0.65rem; color: var(--text-muted); font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px;">Assigned Vehicle</div>
                                    <div style="font-weight: 800; color: var(--text-main); font-size: 1.2rem;">{{ $booking->chauffeur->vehicle->make }} {{ $booking->chauffeur->vehicle->model }}</div>
                                </div>
                                <div style="text-align: right;">
                                    <span style="font-size: 0.8rem; color: var(--primary); font-family: monospace; font-weight: 800; background: rgba(30,58,138,0.1); padding: 4px 10px; border-radius: 6px; display: block; margin-bottom: 5px;">{{ $booking->chauffeur->vehicle->license_plate }}</span>
                                    <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600;"><i class="fas fa-palette"></i> {{ $booking->chauffeur->vehicle->color }}</span>
                                </div>
                            </div>
                            
                            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin-top: 15px; font-size: 0.8rem; color: var(--text-main); font-weight: 700; border-top: 1px dashed var(--border); padding-top: 15px;">
                                <div title="Transmission" style="display: flex; align-items: center; gap: 8px;"><i class="fas fa-cog" style="color: var(--primary); font-size: 0.9rem;"></i> {{ ucfirst($booking->chauffeur->vehicle->transmission) }}</div>
                                <div title="Fuel Type" style="display: flex; align-items: center; gap: 8px;"><i class="fas fa-gas-pump" style="color: var(--primary); font-size: 0.9rem;"></i> {{ ucfirst($booking->chauffeur->vehicle->fuel_type) }}</div>
                                <div title="Seats" style="display: flex; align-items: center; gap: 8px;"><i class="fas fa-users" style="color: var(--primary); font-size: 0.9rem;"></i> {{ $booking->chauffeur->vehicle->seats }} Seats</div>
                                <div title="Luggage" style="display: flex; align-items: center; gap: 8px;"><i class="fas fa-suitcase" style="color: var(--primary); font-size: 0.9rem;"></i> {{ $booking->chauffeur->vehicle->luggage_capacity }} Bags</div>
                            </div>
                        </div>
                    </div>
                    @endif
                @else
                    <div style="text-align: center; padding: 20px; background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.3); border-radius: 15px; color: var(--accent);">
                        <i class="fas fa-lock" style="display: block; font-size: 1.5rem; margin-bottom: 10px;"></i>
                        <p style="font-size: 0.85rem; font-weight: 600; margin: 0; color: var(--text-main);">Driver profile will be visible once your booking is approved and confirmed.</p>
                    </div>
                @endif
            @elseif($booking->status === 'confirmed')
                <div style="text-align: center; padding: 20px; background: var(--bg-main); border: 1px solid var(--border); border-radius: 15px;">
                    <i class="fas fa-info-circle" style="display: block; font-size: 1.5rem; margin-bottom: 10px; color: var(--primary);"></i>
                    <p style="font-size: 0.85rem; font-weight: 600; margin: 0; color: var(--text-main);">Driver assignment is in progress. Check back soon!</p>
                </div>
            @endif
        </div>
        @endif

        <!-- Payment Status Card -->
        <div class="card" style="padding: 30px; background: var(--bg-card); border-radius: 24px; box-shadow: var(--shadow-sm); border: 1px solid var(--border);">
            <h3 style="font-family: 'Outfit', sans-serif; margin-bottom: 20px; color: var(--text-main);">Financial Status</h3>
            
            @hasanyrole('Super Admin|Operations Admin')
            @if($booking->status === 'cancelled')
                <div style="margin-bottom: 15px;">
                    <label style="display: block; font-size: 0.75rem; font-weight: 800; color: var(--text-muted); margin-bottom: 8px; text-transform: uppercase;">Payment Tracking</label>
                    <div style="width: 100%; padding: 12px; border-radius: 12px; border: 1px solid var(--border); font-weight: 700; background: rgba(0,0,0,0.02); color: var(--text-muted); cursor: not-allowed;">
                        {{ $booking->payment_status === 'pending' ? 'Not Paid' : ucwords(str_replace('_', ' ', $booking->payment_status)) }}
                    </div>
                </div>
            @else
            <form action="{{ route('bookings.update-payment', $booking) }}" method="POST" id="payment-form-detail">
                @csrf
                <div style="margin-bottom: 15px;">
                    <label style="display: block; font-size: 0.75rem; font-weight: 800; color: var(--text-muted); margin-bottom: 8px; text-transform: uppercase;">Payment Tracking</label>
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        <select name="payment_status" id="payment-status-detail" onchange="onDetailPaymentSelectChange(this, '{{ $booking->total_amount }}')" style="width: 100%; padding: 12px; border-radius: 12px; border: 1px solid var(--border); font-weight: 700; background: var(--bg-main); color: var(--text-main); cursor: pointer;">
                            <option value="pending" {{ $booking->payment_status === 'pending' ? 'selected' : '' }}>Payment Pending</option>
                            <option value="partially_paid" {{ $booking->payment_status === 'partially_paid' ? 'selected' : '' }}>Partially Paid</option>
                            <option value="paid" {{ $booking->payment_status === 'paid' ? 'selected' : '' }}>Paid in Full</option>
                            <option value="refund" {{ $booking->payment_status === 'refund' ? 'selected' : '' }}>Refund</option>
                            <option value="refunded" {{ $booking->payment_status === 'refunded' ? 'selected' : '' }}>Refunded</option>
                        </select>
                        
                        <div id="partial-input-container-detail" style="display: {{ $booking->payment_status === 'partially_paid' ? 'flex' : 'none' }}; align-items: center; gap: 8px; margin-top: 5px;">
                            <span style="font-weight: 700; color: var(--text-main);">₵</span>
                            <input type="number" step="0.01" name="partial_amount" id="partial-amount-detail" 
                                   value="{{ $booking->partial_amount }}" 
                                   placeholder="Amount" 
                                   max="{{ $booking->total_amount }}"
                                   style="flex: 1; padding: 12px; border: 1px solid var(--border); border-radius: 12px; font-weight: 600; background: var(--bg-main); color: var(--text-main);">
                            <button type="submit" style="background: var(--primary); color: white; border: none; padding: 12px 20px; border-radius: 12px; font-weight: 800; cursor: pointer; height: 46px; display: inline-flex; align-items: center; justify-content: center;">Save</button>
                        </div>
                    </div>
                </div>
            </form>
            @endif
            <script>
                function onDetailPaymentSelectChange(select, totalAmount) {
                    var container = document.getElementById('partial-input-container-detail');
                    var input = document.getElementById('partial-amount-detail');
                    if (select.value === 'partially_paid') {
                        container.style.display = 'flex';
                        if (input) {
                            input.focus();
                            if (!input.value) {
                                input.value = (parseFloat(totalAmount) / 2).toFixed(2);
                            }
                        }
                    } else {
                        container.style.display = 'none';
                        select.form.submit();
                    }
                }
            </script>
            @else
            <div style="margin-bottom: 15px;">
                <label style="display: block; font-size: 0.75rem; font-weight: 800; color: var(--text-muted); margin-bottom: 8px; text-transform: uppercase;">Payment Status</label>
                <div style="padding: 12px; border-radius: 12px; font-weight: 700; background: var(--bg-main); color: var(--text-main); text-align: center; border: 1px solid var(--border);">
                    {{ str_replace('_', ' ', ucfirst($booking->payment_status)) }}
                </div>
            </div>
            @endhasanyrole
 
            <div style="margin-top: 20px; padding: 20px; border: 2px dashed var(--border); border-radius: 15px; text-align: center;">
                <div style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 5px;">Collection Reference</div>
                <div style="font-family: 'Courier New', Courier, monospace; font-weight: 700; letter-spacing: 2px; color: var(--text-main); margin-bottom: 12px;">
                    {{ $booking->booking_reference }}
                    @if($booking->payment_status === 'paid')
                        <i class="fas fa-star" style="color: #eab308; margin-left: 5px; font-size: 1.2rem;" title="Fully Paid"></i>
                    @endif
                </div>
                <span style="display: inline-block; padding: 4px 12px; background: var(--accent); color: white; border-radius: 8px; font-size: 0.7rem; font-weight: 900; text-transform: uppercase; letter-spacing: 1px; box-shadow: 0 4px 10px rgba(245, 158, 11, 0.2);">
                    <i class="fas fa-tag" style="margin-right: 5px;"></i> {{ $booking->guest_type ?: 'Standard' }}
                </span>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .btn-back:hover {
        color: var(--primary) !important;
    }
    @media print {
        .sidebar, .top-bar, .page-actions, .btn-back { display: none !important; }
        .main-content { margin: 0 !important; padding: 0 !important; }
        .card { box-shadow: none !important; border: 1px solid #eee !important; }
    }
</style>
@endpush
