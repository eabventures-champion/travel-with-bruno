<table>
    <thead>
        <tr>
            @role('Super Admin')
                <th style="width: 40px; text-align: center;">
                    <i class="fas fa-check-square" style="color: var(--text-muted); font-size: 0.8rem;"></i>
                </th>
            @endrole
            <th>Package / Service</th>
            <th>Payment</th>
            <th>Status</th>
            <th>Date</th>
            <th>Actions</th>
        </tr>
    </thead>
    @php
        $groupedByPackage = $bookings->groupBy(function($booking) {
            return $booking->getMainTitle();
        })->sortBy(function($packageBookings) {
            $firstBooking = $packageBookings->first();
            $firstItem = $firstBooking->items->first();
            
            $typePriority = 2; // Default for others (Fleet, Transfers)
            if ($firstItem && $firstItem->bookable_type === 'Modules\Tourism\Models\TourismPackage') {
                $packageType = $firstItem->bookable->package_type ?? 'fixed';
                $typePriority = $packageType === 'scheduled' ? 0 : 1; // 0 for Organized, 1 for Fixed
            }

            $dateTimestamp = $firstBooking->scheduled_at ? \Carbon\Carbon::parse($firstBooking->scheduled_at)->timestamp : PHP_INT_MAX;
            
            return $typePriority . '_' . str_pad($dateTimestamp, 20, '0', STR_PAD_LEFT);
        });
    @endphp
    @forelse($groupedByPackage as $title => $packageBookings)
        @php 
            $firstBooking = $packageBookings->first(); 
            $firstItem = $firstBooking->items->first();
            $totalGuests = $packageBookings->sum(function($b) {
                return $b->items->sum('quantity');
            });
            $paidGuests = $packageBookings->where('payment_status', 'paid')->sum(function($b) {
                return $b->items->sum('quantity');
            });
        @endphp
        <tbody x-data="{ 
            @if(auth()->user()->hasAnyRole(['Super Admin', 'Operations Admin']))
                expanded: localStorage.getItem('pkg_expanded_{{ Str::slug($title) }}') === 'true' || localStorage.getItem('pkg_expanded_{{ Str::slug($title) }}') === null,
            @else
                expanded: true,
            @endif
            init() {
                @hasanyrole('Super Admin|Operations Admin')
                this.$watch('expanded', val => localStorage.setItem('pkg_expanded_{{ Str::slug($title) }}', val));
                @endhasanyrole
            }
        }">
            @hasanyrole('Super Admin|Operations Admin')
            <tr style="background: rgba(30, 58, 138, 0.08); cursor: pointer; border-left: 4px solid var(--primary);" @click="expanded = !expanded">
                <td colspan="{{ auth()->user()->hasRole('Super Admin') ? 7 : 6 }}" style="padding: 15px 20px; border-bottom: 1px solid var(--border);">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <div style="width: 45px; height: 45px; background: white; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--primary); box-shadow: 0 4px 10px rgba(0,0,0,0.05); border: 1px solid var(--border);">
                                @if($firstItem && $firstItem->bookable_type === 'Modules\Tourism\Models\TourismPackage')
                                    <i class="fas fa-umbrella-beach" style="font-size: 1.2rem;"></i>
                                @elseif($firstItem && $firstItem->bookable_type === 'Modules\Fleet\Models\AirportTransfer')
                                    <i class="fas fa-plane" style="font-size: 1.2rem;"></i>
                                @else
                                    <i class="fas fa-car" style="font-size: 1.2rem;"></i>
                                @endif
                            </div>
                            <div>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div style="font-weight: 800; color: var(--text-main); font-size: 1.1rem; letter-spacing: -0.2px;">{{ $title }}</div>
                                    @if($firstItem && $firstItem->bookable_type === 'Modules\Tourism\Models\TourismPackage')
                                        <span style="font-size: 0.6rem; font-weight: 800; text-transform: uppercase; padding: 2px 8px; border-radius: 4px; background: {{ ($firstItem->bookable->package_type ?? 'fixed') === 'fixed' ? '#6366f1' : '#0ea5e9' }}; color: white; letter-spacing: 0.5px;">
                                            {{ ($firstItem->bookable->package_type ?? 'fixed') === 'fixed' ? 'Fixed Tour' : 'Organized Tour' }}
                                        </span>
                                        @php
                                            $packageType = $firstItem->bookable->package_type ?? 'fixed';
                                            $displayDate = null;
                                            if ($packageType === 'scheduled') {
                                                $displayDate = $firstBooking->scheduled_at ? \Carbon\Carbon::parse($firstBooking->scheduled_at) : $firstItem->bookable->departure_date;
                                            }
                                        @endphp
                                        @if($displayDate)
                                            <span style="background: rgba(16, 185, 129, 0.15); color: #059669; border: 1px solid rgba(16, 185, 129, 0.3); padding: 2px 8px; border-radius: 12px; font-size: 0.6rem; font-weight: 800; display: inline-flex; align-items: center; gap: 4px;">
                                                <i class="fas fa-calendar-alt"></i> {{ \Carbon\Carbon::parse($displayDate)->format('M d, Y') }} @ {{ $firstBooking->scheduled_at ? \Carbon\Carbon::parse($firstBooking->scheduled_at)->format('h:i A') : '09:00 AM' }}
                                            </span>
                                        @endif
                                    @endif
                                    @if($firstItem && ($firstItem->bookable_type === 'Modules\Fleet\Models\Vehicle'))
                                        <span style="font-size: 0.6rem; font-weight: 800; text-transform: uppercase; padding: 2px 8px; border-radius: 4px; background: #f59e0b; color: white; letter-spacing: 0.5px;">Rental</span>
                                    @endif
                                    @if($firstItem && $firstItem->bookable_type === 'Modules\Fleet\Models\AirportTransfer')
                                        <span style="font-size: 0.6rem; font-weight: 800; text-transform: uppercase; padding: 2px 8px; border-radius: 4px; background: #14b8a6; color: white; letter-spacing: 0.5px;">Transfer</span>
                                    @endif
                                </div>
                                <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600; margin-top: 2px; display: flex; align-items: center; gap: 10px;">
                                    <span><i class="fas fa-file-invoice" style="margin-right: 4px;"></i> {{ $packageBookings->count() }} Bookings</span>
                                    @if($firstItem && $firstItem->bookable_type === 'Modules\Tourism\Models\TourismPackage')
                                        <span style="color: var(--accent);">
                                            <i class="fas fa-users" style="margin-right: 4px;"></i> 
                                            {{ $paidGuests }} / {{ $totalGuests }} Guests Paid{{ ($firstItem->bookable->package_type ?? '') === 'scheduled' ? ', max guest: ' . ($firstItem->bookable->max_guests ?? 0) : '' }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <div style="text-align: right; margin-right: 15px;">
                                <div style="font-size: 0.6rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase;">Estimated Total</div>
                                <div style="font-weight: 800; color: var(--primary); font-size: 1rem;">₵{{ number_format($packageBookings->sum('total_amount'), 2) }}</div>
                            </div>
                            @if($firstItem && $firstItem->bookable_type === 'Modules\Tourism\Models\TourismPackage' && !($hideAssign ?? false))
                                <button type="button" 
                                        onclick="event.stopPropagation(); document.getElementById('schedule-modal-{{ Str::slug($title) }}').style.display='flex';" 
                                        style="background: #10b981; color: white; border: none; padding: 6px 12px; border-radius: 6px; font-weight: 700; font-size: 0.75rem; cursor: pointer; display: flex; align-items: center; gap: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                    <i class="fas fa-calendar-alt"></i> Schedule Dates
                                </button>
                                <button type="button" 
                                        onclick="event.stopPropagation(); document.getElementById('driver-modal-{{ Str::slug($title) }}').style.display='flex';" 
                                        style="background: var(--primary); color: white; border: none; padding: 6px 12px; border-radius: 6px; font-weight: 700; font-size: 0.75rem; cursor: pointer; display: flex; align-items: center; gap: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                    <i class="fas fa-steering-wheel"></i> Assign Drivers
                                </button>
                            @endif
                            <div style="width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; background: white; border: 1px solid var(--border); border-radius: 50%; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                                <i class="fas" :class="expanded ? 'fa-chevron-up' : 'fa-chevron-down'" style="color: var(--text-muted);"></i>
                            </div>
                        </div>
                    </div>
                </td>
            </tr>
            @endhasanyrole
            @php
                $displayBookings = collect();
                $isOrganizedTour = $firstItem && $firstItem->bookable_type === 'Modules\Tourism\Models\TourismPackage' && ($firstItem->bookable->package_type ?? 'fixed') === 'scheduled';
                if ((isset($groupUnified) && $groupUnified) || $isOrganizedTour) {
                    $groups = $packageBookings->groupBy(function($b) {
                        if ($b->isTourismBooking() && $b->chauffeur_id && $b->scheduled_at) {
                            $date = \Carbon\Carbon::parse($b->scheduled_at)->format('Y-m-d');
                            return "unified_{$b->chauffeur_id}_{$date}";
                        }
                        return "single_{$b->id}";
                    });
                    foreach ($groups as $key => $group) {
                        if ($group->count() > 1) {
                            $first = $group->first();
                            $first->is_grouped = true;
                            $first->group_bookings = $group;
                            $displayBookings->push($first);
                        } else {
                            $first = $group->first();
                            $first->is_grouped = false;
                            $displayBookings->push($first);
                        }
                    }
                } else {
                    foreach ($packageBookings as $b) {
                        $b->is_grouped = false;
                        $displayBookings->push($b);
                    }
                }
            @endphp
            @foreach($displayBookings as $booking)
                @php
                    $isLive = $booking->trip_status === 'in_progress' || $booking->return_trip_status === 'in_progress';
                    $isClosedTour = false;
                    if ($booking->payment_status === 'pending' && $booking->isTourismBooking()) {
                        $firstItem = $booking->items->first();
                        if ($firstItem && $firstItem->bookable_type === 'Modules\Tourism\Models\TourismPackage') {
                            $package = $firstItem->bookable;
                            if ($package && $package->package_type === 'scheduled' && $package->departure_date) {
                                $depTime = \Carbon\Carbon::parse($package->departure_date)->setTime(9, 0, 0);
                                if (now()->greaterThanOrEqualTo($depTime)) {
                                    $isClosedTour = true;
                                }
                            }
                        }
                    }
                    $rowStyle = '';
                    if ($booking->is_grouped) {
                        $rowStyle = 'background: rgba(37, 99, 235, 0.015); border-bottom: none;';
                    }
                    if ($isClosedTour) {
                        $rowStyle .= ' background: rgba(220, 38, 38, 0.08);';
                    }
                @endphp
                <tr x-show="expanded" x-transition.opacity style="{{ $rowStyle }}">
                    @role('Super Admin')
                        <td style="text-align: center; vertical-align: middle; padding: 10px;">
                            @php
                                $canMerge = $booking->payment_status === 'paid' && !$isLive && !$booking->is_grouped && !$isClosedTour;
                                $lockReason = !$canMerge ? ($isClosedTour ? 'Tour is closed' : ($isLive ? 'Live trips cannot be merged' : ($booking->is_grouped ? 'Grouped bookings cannot be merged' : 'Only paid bookings can be merged'))) : '';
                            @endphp

                            @if($canMerge)
                                <input type="checkbox" name="booking_ids[]" value="{{ $booking->id }}" @change="updateSelection()" 
                                       style="width: 18px; height: 18px; border-radius: 4px; border: 2px solid var(--border); cursor: pointer; accent-color: var(--accent);">
                            @else
                                <div title="{{ $lockReason }}" style="width: 18px; height: 18px; border: 2px solid var(--border); border-radius: 4px; background: rgba(0,0,0,0.05); cursor: not-allowed; margin: 0 auto; display: flex; align-items: center; justify-content: center; opacity: 0.5;">
                                    <i class="fas fa-lock" style="font-size: 0.6rem; color: var(--text-muted);"></i>
                                </div>
                            @endif
                        </td>
                    @endrole
                    <td>
                        <div style="display: flex; align-items: stretch; gap: 12px; margin-bottom: 0px;">
                            @if($booking->is_grouped)


                                <div style="width: 4px; background: #2563eb; border-radius: 4px; align-self: stretch; min-height: 40px;"></div>
                            @endif
                            <div style="flex: 1;">
                                @hasanyrole('Super Admin|Operations Admin')
                                    @if($booking->is_grouped)
                                        @php
                                            $allAcceptedOutbound = $booking->group_bookings->every(fn($b) => $b->customer_schedule_status === 'accepted');
                                            $allAcceptedReturn = $booking->group_bookings->every(fn($b) => $b->return_customer_schedule_status === 'accepted');
                                        @endphp
                                        <div>
                                            <div style="font-weight: 800; color: #2563eb; font-size: 0.85rem; display: flex; align-items: center; gap: 6px; font-family: 'Outfit', sans-serif; text-transform: uppercase; letter-spacing: 0.5px;">
                                                <i class="fas fa-layer-group"></i> Unified Trip Group
                                            </div>
                                            <div style="display: flex; align-items: center; flex-wrap: wrap; gap: 8px; margin-top: 6px; margin-bottom: 6px;">
                                                @if($booking->scheduled_at)
                                                    <div style="display: inline-flex; align-items: center; gap: 4px;">
                                                        <span style="background: rgba(16, 185, 129, 0.15); color: #059669; border: 1px solid rgba(16, 185, 129, 0.3); padding: 2px 8px; border-radius: 12px; font-size: 0.7rem; font-weight: 800; display: inline-flex; align-items: center; gap: 4px; width: fit-content;">
                                                            <i class="fas fa-plane-departure" style="font-size: 0.6rem;"></i> Outbound: {{ \Carbon\Carbon::parse($booking->scheduled_at)->format('M d, Y @ h:i A') }}
                                                        </span>
                                                        @if($allAcceptedOutbound)
                                                            <span title="All customers accepted outbound schedule" style="background: #10b981; color: white; padding: 2px 6px; border-radius: 4px; font-size: 0.6rem; font-weight: 900; text-transform: uppercase;">
                                                                <i class="fas fa-check"></i> Accepted
                                                            </span>
                                                        @else
                                                            <span title="Outbound schedule confirmations pending" style="background: #f59e0b; color: white; padding: 2px 6px; border-radius: 4px; font-size: 0.6rem; font-weight: 900; text-transform: uppercase;">
                                                                <i class="fas fa-clock"></i> Pending
                                                            </span>
                                                        @endif
                                                    </div>
                                                @endif
                                                @if($booking->return_scheduled_at)
                                                    <div style="display: inline-flex; align-items: center; gap: 4px;">
                                                        <span style="background: rgba(59, 130, 246, 0.15); color: #2563eb; border: 1px solid rgba(59, 130, 246, 0.3); padding: 2px 8px; border-radius: 12px; font-size: 0.7rem; font-weight: 800; display: inline-flex; align-items: center; gap: 4px; width: fit-content;">
                                                            <i class="fas fa-plane-arrival" style="font-size: 0.6rem;"></i> Return: {{ \Carbon\Carbon::parse($booking->return_scheduled_at)->format('M d, Y @ h:i A') }}
                                                        </span>
                                                        @if($allAcceptedReturn)
                                                            <span title="All customers accepted return schedule" style="background: #2563eb; color: white; padding: 2px 6px; border-radius: 4px; font-size: 0.6rem; font-weight: 900; text-transform: uppercase;">
                                                                <i class="fas fa-check"></i> Accepted
                                                            </span>
                                                        @else
                                                            <span title="Return schedule confirmations pending" style="background: #f59e0b; color: white; padding: 2px 6px; border-radius: 4px; font-size: 0.6rem; font-weight: 900; text-transform: uppercase;">
                                                                <i class="fas fa-clock"></i> Pending
                                                            </span>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @else
                                        <div>
                                            <div style="font-weight: 700; color: var(--text-main); font-size: 0.95rem;">
                                                {{ $booking->customer_name ?: ($booking->user->name ?? 'Guest') }}
                                            </div>
                                        </div>
                                        @if($booking->scheduled_at || $booking->return_scheduled_at)
                                            @php
                                                $isOrganized = false;
                                                $bItem = $booking->items->first();
                                                if($bItem && $bItem->bookable_type === 'Modules\Tourism\Models\TourismPackage') {
                                                    if(($bItem->bookable->package_type ?? 'fixed') === 'scheduled') {
                                                        $isOrganized = true;
                                                    }
                                                }
                                                $isAcceptedOutbound = $booking->customer_schedule_status === 'accepted';
                                                $isAcceptedReturn = $booking->return_customer_schedule_status === 'accepted';
                                            @endphp
                                            @if(!$isOrganized)
                                                <div style="display: flex; align-items: center; flex-wrap: wrap; gap: 8px; margin-top: 6px; margin-bottom: 6px;">
                                                    @if($booking->scheduled_at)
                                                        <div style="display: inline-flex; align-items: center; gap: 4px;">
                                                            <span style="background: rgba(16, 185, 129, 0.15); color: #059669; border: 1px solid rgba(16, 185, 129, 0.3); padding: 2px 8px; border-radius: 12px; font-size: 0.7rem; font-weight: 800; display: inline-flex; align-items: center; gap: 4px; width: fit-content;">
                                                                <i class="fas fa-plane-departure" style="font-size: 0.6rem;"></i> Outbound: {{ \Carbon\Carbon::parse($booking->scheduled_at)->format('M d, Y @ h:i A') }}
                                                            </span>
                                                            @if($isAcceptedOutbound)
                                                                <span title="Customer accepted outbound schedule" style="background: #10b981; color: white; padding: 2px 6px; border-radius: 4px; font-size: 0.6rem; font-weight: 900; text-transform: uppercase;">
                                                                    <i class="fas fa-check"></i> Accepted
                                                                </span>
                                                            @else
                                                                <span title="Outbound schedule confirmation pending" style="background: #f59e0b; color: white; padding: 2px 6px; border-radius: 4px; font-size: 0.6rem; font-weight: 900; text-transform: uppercase;">
                                                                    <i class="fas fa-clock"></i> Pending
                                                                </span>
                                                            @endif
                                                        </div>
                                                    @endif
                                                    @if($booking->return_scheduled_at)
                                                        <div style="display: inline-flex; align-items: center; gap: 4px;">
                                                            <span style="background: rgba(59, 130, 246, 0.15); color: #2563eb; border: 1px solid rgba(59, 130, 246, 0.3); padding: 2px 8px; border-radius: 12px; font-size: 0.7rem; font-weight: 800; display: inline-flex; align-items: center; gap: 4px; width: fit-content;">
                                                                <i class="fas fa-plane-arrival" style="font-size: 0.6rem;"></i> Return: {{ \Carbon\Carbon::parse($booking->return_scheduled_at)->format('M d, Y @ h:i A') }}
                                                            </span>
                                                            @if($isAcceptedReturn)
                                                                <span title="Customer accepted return schedule" style="background: #2563eb; color: white; padding: 2px 6px; border-radius: 4px; font-size: 0.6rem; font-weight: 900; text-transform: uppercase;">
                                                                    <i class="fas fa-check"></i> Accepted
                                                                </span>
                                                            @else
                                                                <span title="Return schedule confirmation pending" style="background: #f59e0b; color: white; padding: 2px 6px; border-radius: 4px; font-size: 0.6rem; font-weight: 900; text-transform: uppercase;">
                                                                    <i class="fas fa-clock"></i> Pending
                                                                </span>
                                                            @endif
                                                        </div>
                                                    @endif
                                                </div>
                                            @endif
                                        @endif
                                        <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $booking->customer_phone ?: ($booking->user->phone ?? 'No Phone') }}</div>
                                         @if($isClosedTour)
                                             <div style="margin-top: 6px; display: inline-flex; align-items: center; gap: 5px; background: rgba(239, 68, 68, 0.18); color: #fecaca; border: 1px solid rgba(239, 68, 68, 0.4); padding: 4px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; font-family: 'Outfit', sans-serif;">
                                                 <i class="fas fa-exclamation-triangle"></i> Ongoing tour - Tour is closed
                                             </div>
                                         @endif
                                    @endif
                                @endhasanyrole
                                
                                @if(!$booking->is_grouped)
                                    @if($booking->group_name)
                                        <div style="font-size: 0.75rem; font-weight: 700; color: var(--accent); margin-top: 2px;">
                                            <i class="fas fa-building" style="margin-right: 4px;"></i> {{ $booking->group_name }}
                                        </div>
                                    @elseif($booking->guest_type)
                                        <div style="font-size: 0.75rem; font-weight: 700; color: var(--accent); margin-top: 2px; text-transform: capitalize;">
                                            <i class="fas fa-users" style="margin-right: 4px;"></i> {{ str_replace('_', ' ', $booking->guest_type) }}
                                        </div>
                                    @endif

                                    @foreach($booking->items as $item)
                                        <div style="margin-top: 4px;">
                                            @if($item->bookable_type === 'Modules\Tourism\Models\TourismPackage')
                                                <div style="font-weight: 700; color: var(--text-main); font-size: 0.9rem; margin-bottom: 4px; display: flex; align-items: center; gap: 6px;">
                                                    <i class="fas fa-map-marked-alt" style="color: var(--primary); font-size: 0.85rem;"></i> {{ $item->bookable->title ?? 'Tourism Package' }}
                                                </div>
                                                <div style="font-size: 0.75rem; font-weight: 600; color: var(--text-muted); display: flex; align-items: center; gap: 4px;">
                                                    <i class="fas fa-user-friends" style="width: 14px; text-align: center;"></i> {{ $item->quantity }} Person(s)
                                                </div>
                                            @elseif($item->bookable_type === 'Modules\Fleet\Models\AirportTransfer')
                                                <div style="display: flex; flex-direction: column; align-items: flex-start; gap: 2px;">
                                                    <span style="font-weight: 600; color: var(--text-muted); font-size: 0.85rem;">{{ $item->bookable->airport_name ?? 'Airport Transfer' }}</span>
                                                </div>
                                            @else
                                                <div style="display: flex; flex-direction: column; align-items: flex-start; gap: 2px;">
                                                    <span style="font-weight: 600; color: var(--text-muted); font-size: 0.85rem;">{{ ($item->bookable->make ?? '') . ' ' . ($item->bookable->model ?? 'Vehicle') }}</span>
                                                </div>
                                            @endif

                                            @if($item->bookable_type === 'Modules\Fleet\Models\Vehicle')
                                                <div style="font-size: 0.7rem; color: var(--text-muted); font-weight: 800; text-transform: uppercase; margin-top: 4px; display: flex; align-items: center; gap: 5px;">
                                                    <i class="fas {{ $booking->is_self_drive ? 'fa-steering-wheel' : 'fa-user-tie' }}" style="width: 14px; text-align: center;"></i> 
                                                    {{ $booking->is_self_drive ? 'Self Drive' : 'Chauffeur Included' }}
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach

                                    @if($booking->status === 'cancelled' && $booking->cancellation_reason)
                                        <div style="margin-top: 8px; padding: 8px 12px; background: rgba(239, 68, 68, 0.05); border: 1px solid rgba(239, 68, 68, 0.15); border-radius: 8px; font-size: 0.8rem; color: #dc2626;">
                                            <i class="fas fa-times-circle" style="margin-right: 4px;"></i>
                                            <strong>Reason:</strong> "{{ $booking->cancellation_reason }}"
                                        </div>
                                    @endif
                                @else
                                    <div style="margin-top: 4px;">
                                        <div style="font-size: 0.75rem; font-weight: 600; color: var(--text-muted); display: flex; align-items: center; gap: 4px;">
                                            <i class="fas fa-user-friends" style="width: 14px; text-align: center;"></i> {{ $booking->group_bookings->sum(fn($b) => $b->items->sum('quantity')) }} Person(s) Total
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td>
                        @hasanyrole('Super Admin|Operations Admin')
                            @if($booking->is_grouped)
                                @php
                                    $allPaid = $booking->group_bookings->every(fn($b) => $b->payment_status === 'paid');
                                    $allPending = $booking->group_bookings->every(fn($b) => $b->payment_status === 'pending');
                                    
                                    $paymentBg = '#e0f2fe';
                                    $paymentColor = '#0369a1';
                                    $grpPaymentStatus = 'partially_paid';
                                    if ($allPaid) {
                                        $paymentBg = '#dcfce7';
                                        $paymentColor = '#166534';
                                        $grpPaymentStatus = 'paid';
                                    } elseif ($allPending) {
                                        $paymentBg = '#fee2e2';
                                        $paymentColor = '#991b1b';
                                        $grpPaymentStatus = 'pending';
                                    }
                                @endphp
                                <form action="{{ route('bookings.update-payment', $booking) }}" method="POST" style="margin: 0; display: inline-flex; align-items: center; gap: 6px;" id="payment-form-{{ $booking->id }}">
                                    @csrf
                                    <input type="hidden" name="propagate_group" value="1">
                                    <div style="display: flex; flex-direction: column; gap: 4px; align-items: flex-start;">
                                        <select name="payment_status" id="payment-status-{{ $booking->id }}"
                                                onchange="if(confirm('Are you sure you want to update the payment status of all bookings in this unified trip group?')) { this.form.submit() } else { window.location.reload(); }"
                                                {{ $isLive ? 'disabled' : '' }}
                                                title="{{ $isLive ? 'Cannot update payment status of a live trip' : '' }}"
                                                style="width: auto; display: inline-block; padding: 4px 8px; border-radius: 6px; border: 1px solid var(--border); font-size: 0.8rem; background: {{ $paymentBg }}; color: {{ $paymentColor }}; font-weight: 600; cursor: {{ $isLive ? 'not-allowed' : 'pointer' }};">
                                            <option value="pending" {{ $grpPaymentStatus === 'pending' ? 'selected' : '' }}>Pending</option>
                                            <option value="partially_paid" {{ $grpPaymentStatus === 'partially_paid' ? 'selected' : '' }}>Partial</option>
                                            <option value="paid" {{ $grpPaymentStatus === 'paid' ? 'selected' : '' }}>Paid</option>
                                            <option value="refund" {{ $grpPaymentStatus === 'refund' ? 'selected' : '' }}>Refund</option>
                                            <option value="refunded" {{ $grpPaymentStatus === 'refunded' ? 'selected' : '' }}>Refunded</option>
                                        </select>
                                    </div>
                                </form>
                                <div style="font-weight: 800; color: var(--text-main); font-size: 0.95rem; margin-top: 8px;">₵{{ number_format($booking->group_bookings->sum('total_amount'), 2) }}</div>
                            @else
                                @if($booking->status === 'cancelled')
                                    <div style="display: flex; flex-direction: column; gap: 4px; align-items: flex-start;">
                                        <span class="status-badge" style="background: #f1f5f9; color: #94a3b8; border: 1px solid var(--border); font-size: 0.8rem; padding: 4px 8px; border-radius: 6px; font-weight: 600;">
                                            {{ $booking->payment_status === 'pending' ? 'Not Paid' : ucfirst(str_replace('_', ' ', $booking->payment_status)) }}
                                        </span>
                                    </div>
                                @else
                                <form action="{{ route('bookings.update-payment', $booking) }}" method="POST" style="margin: 0; display: inline-flex; align-items: center; gap: 6px;" id="payment-form-{{ $booking->id }}">
                                    @csrf
                                    @php
                                        $paymentBg = '#f8fafc';
                                        $paymentColor = '#64748b';
                                        
                                        if ($booking->status !== 'confirmed') {
                                            $paymentBg = '#f1f5f9';
                                            $paymentColor = '#94a3b8';
                                        } else {
                                            switch($booking->payment_status) {
                                                case 'paid': $paymentBg = '#dcfce7'; $paymentColor = '#166534'; break;
                                                case 'pending': $paymentBg = '#fef9c3'; $paymentColor = '#854d0e'; break;
                                                case 'partially_paid': $paymentBg = '#e0f2fe'; $paymentColor = '#0369a1'; break;
                                                case 'refund': $paymentBg = '#fee2e2'; $paymentColor = '#991b1b'; break;
                                                case 'refunded': $paymentBg = '#f1f5f9'; $paymentColor = '#475569'; break;
                                            }
                                        }
                                    @endphp
                                    <div style="display: flex; flex-direction: column; gap: 4px; align-items: flex-start;">
                                        <select name="payment_status" id="payment-status-{{ $booking->id }}"
                                                onchange="if('{{ $isClosedTour }}' === '1') { if(confirm('This tour is closed/ongoing. Are you sure you want to update payment status?')) { onTablePaymentSelectChange(this, '{{ $booking->id }}', '{{ $booking->total_amount }}') } else { window.location.reload(); } } else { onTablePaymentSelectChange(this, '{{ $booking->id }}', '{{ $booking->total_amount }}') }"
                                                {{ ($booking->status !== 'confirmed' || $isLive) ? 'disabled' : '' }}
                                                title="{{ $isClosedTour ? 'Tour is closed (Click to update anyway)' : ($isLive ? 'Cannot update payment status of a live trip' : ($booking->status !== 'confirmed' ? 'Confirm booking first to update payment' : '')) }}"
                                                style="width: auto; display: inline-block; padding: 4px 8px; border-radius: 6px; border: 1px solid var(--border); font-size: 0.8rem; background: {{ $paymentBg }}; color: {{ $paymentColor }}; font-weight: 600; cursor: {{ ($booking->status !== 'confirmed' || $isLive) ? 'not-allowed' : 'pointer' }};">
                                            <option value="pending" {{ $booking->payment_status === 'pending' ? 'selected' : '' }}>Pending</option>
                                            <option value="partially_paid" {{ $booking->payment_status === 'partially_paid' ? 'selected' : '' }}>Partial</option>
                                            <option value="paid" {{ $booking->payment_status === 'paid' ? 'selected' : '' }}>Paid</option>
                                            <option value="refund" {{ $booking->payment_status === 'refund' ? 'selected' : '' }}>Refund</option>
                                            <option value="refunded" {{ $booking->payment_status === 'refunded' ? 'selected' : '' }}>Refunded</option>
                                        </select>
                                        
                                        <div id="partial-input-container-{{ $booking->id }}" style="display: {{ $booking->payment_status === 'partially_paid' ? 'inline-flex' : 'none' }}; align-items: center; gap: 4px; margin-top: 2px;">
                                            <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 700;">₵</span>
                                            <input type="number" step="0.01" name="partial_amount" id="partial-amount-{{ $booking->id }}" 
                                                   value="" 
                                                   placeholder="New Payment" 
                                                   max="{{ $booking->total_amount - ($booking->partial_amount ?? 0) }}"
                                                   style="width: 80px; padding: 2px 4px; border: 1px solid var(--border); border-radius: 4px; font-size: 0.7rem; background: var(--bg-main); color: var(--text-main); font-weight: 600;">
                                            <button type="submit" style="background: var(--primary); color: white; border: none; padding: 2px 6px; border-radius: 4px; font-size: 0.7rem; font-weight: 800; cursor: pointer;">Save</button>
                                        </div>
                                    </div>
                                </form>
                                @endif
                                <div style="font-weight: 800; color: var(--text-main); font-size: 0.95rem; margin-top: 8px;">
                                    @if($booking->payment_status === 'partially_paid' && $booking->partial_amount > 0)
                                        <span style="color: #0284c7;">Paid: ₵{{ number_format($booking->partial_amount, 2) }}</span> <br><span style="font-weight: 600; font-size: 0.75rem; color: var(--text-muted);">Total: ₵{{ number_format($booking->total_amount, 2) }}</span>
                                    @else
                                        ₵{{ number_format($booking->total_amount, 2) }}
                                    @endif
                                </div>
                            @endif
                        @else
                            @php
                                $statusVal = $booking->is_grouped ? ($booking->group_bookings->every(fn($b) => $b->payment_status === 'paid') ? 'paid' : 'partially_paid') : $booking->payment_status;
                                $badgeClass = 'status-inactive';
                                if($statusVal === 'paid') $badgeClass = 'status-active';
                                elseif($statusVal === 'partially_paid') $badgeClass = 'status-suspended';
                                elseif($statusVal === 'refund') $badgeClass = 'status-inactive';
                            @endphp
                            <span class="status-badge {{ $badgeClass }}">
                                {{ ucfirst(str_replace('_', ' ', $statusVal)) }}
                            </span>
                            <div style="font-weight: 800; color: var(--text-main); font-size: 0.95rem; margin-top: 8px;">
                                @if($booking->is_grouped)
                                    @php
                                        $paidSum = $booking->group_bookings->sum('partial_amount') + $booking->group_bookings->where('payment_status', 'paid')->sum('total_amount');
                                        $totalSum = $booking->group_bookings->sum('total_amount');
                                    @endphp
                                    @if($statusVal === 'partially_paid' && $paidSum > 0)
                                        <span style="color: #0284c7; font-size: 0.8rem;">Paid: ₵{{ number_format($paidSum, 2) }}</span> <br>
                                        <span style="font-weight: 600; font-size: 0.75rem; color: var(--text-muted);">Total: ₵{{ number_format($totalSum, 2) }}</span>
                                    @else
                                        ₵{{ number_format($totalSum, 2) }}
                                    @endif
                                @else
                                    @if($booking->payment_status === 'partially_paid' && $booking->partial_amount > 0)
                                        <span style="color: #0284c7; font-size: 0.8rem;">Paid: ₵{{ number_format($booking->partial_amount, 2) }}</span> <br>
                                        <span style="font-weight: 600; font-size: 0.75rem; color: var(--text-muted);">Total: ₵{{ number_format($booking->total_amount, 2) }}</span>
                                    @else
                                        ₵{{ number_format($booking->total_amount, 2) }}
                                    @endif
                                @endif
                            </div>
                        @endhasanyrole
                    </td>
                    <td>
                        @if($booking->trip_status === 'in_progress' && $booking->trip_leg === 'outbound')
                            <div style="display: inline-flex; align-items: center; gap: 6px; margin-bottom: 8px; background: rgba(16, 185, 129, 0.15); color: #059669; padding: 5px 10px; border-radius: 6px; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; border: 1px solid rgba(16, 185, 129, 0.3);">
                                <i class="fas fa-circle-notch fa-spin" style="color: #f59e0b; font-size: 0.85rem;"></i> Live Trip{{ $booking->isTourismBooking() ? ' (Outbound)' : '' }}
                            </div><br>
                        @elseif($booking->trip_status === 'completed' && $booking->trip_leg === 'return' && $booking->return_trip_status === 'idle')
                            <div style="display: inline-flex; align-items: center; gap: 5px; margin-bottom: 8px; background: rgba(245, 158, 11, 0.15); color: #d97706; padding: 4px 8px; border-radius: 6px; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; border: 1px solid rgba(245, 158, 11, 0.3);">
                                <i class="fas fa-map-marker-alt"></i> Arrived at Destination
                            </div><br>
                        @elseif($booking->return_trip_status === 'in_progress')
                            <div style="display: inline-flex; align-items: center; gap: 6px; margin-bottom: 8px; background: rgba(37, 99, 235, 0.2); color: #60a5fa; padding: 5px 10px; border-radius: 6px; font-size: 0.7rem; font-weight: 900; text-transform: uppercase; border: 1px solid rgba(96, 165, 250, 0.5); box-shadow: 0 0 10px rgba(37, 99, 235, 0.1);">
                                <i class="fas fa-circle-notch fa-spin" style="color: #f59e0b; font-size: 0.85rem;"></i> Live Trip (Return)
                            </div><br>
                        @elseif($booking->trip_status === 'completed' && ($booking->return_trip_status === 'completed' || $booking->trip_leg === 'outbound'))
                            <div style="display: inline-flex; align-items: center; gap: 5px; margin-bottom: 8px; background: rgba(59, 130, 246, 0.2); color: #3b82f6; padding: 4px 8px; border-radius: 6px; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; border: 1px solid rgba(59, 130, 246, 0.4);">
                                <i class="fas fa-flag-checkered"></i> Trip Ended
                            </div><br>

                        @elseif($booking->payment_status === 'paid' && $booking->status === 'confirmed' && $booking->trip_status === 'idle')
                            <div style="display: inline-flex; align-items: center; gap: 5px; margin-bottom: 8px; background: rgba(245, 158, 11, 0.15); color: #d97706; padding: 4px 8px; border-radius: 6px; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; border: 1px solid rgba(245, 158, 11, 0.3);">
                                <i class="fas fa-calendar-alt"></i> Upcoming Trip
                            </div><br>
                        @endif

                        @hasanyrole('Super Admin|Operations Admin')
                            @if($booking->is_grouped)
                                <form action="{{ route('bookings.update-status', $booking) }}" method="POST" style="margin: 0;">
                                    @csrf
                                    <input type="hidden" name="propagate_group" value="1">
                                    <select name="status" onchange="
                                            if(this.value === 'cancelled') {
                                                this.value = '{{ $booking->status }}';
                                                document.getElementById('cancel-modal-{{ $booking->id }}').style.display = 'flex';
                                            } else if(confirm('Are you sure you want to update the status of all bookings in this unified trip group?')) {
                                                this.form.submit();
                                            } else {
                                                window.location.reload();
                                            }
                                        " 
                                            {{ ($booking->status === 'completed' || $isLive) ? 'disabled' : '' }} 
                                            title="{{ $isLive ? 'Cannot update status of a live trip' : '' }}"
                                            style="width: auto; display: inline-block; padding: 4px 8px; border-radius: 6px; border: 1px solid var(--border); font-size: 0.8rem; background: {{ $booking->status === 'confirmed' ? '#dcfce7' : ($booking->status === 'pending' ? '#fef9c3' : '#f8fafc') }}; color: {{ $booking->status === 'confirmed' ? '#166534' : ($booking->status === 'pending' ? '#854d0e' : '#64748b') }}; font-weight: 600; cursor: {{ ($booking->status === 'completed' || $isLive) ? 'not-allowed' : 'pointer' }};">
                                        <option value="pending" {{ $booking->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="confirmed" {{ $booking->status === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                        <option value="cancelled" {{ $booking->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                        <option value="completed" {{ $booking->status === 'completed' ? 'selected' : '' }}>Completed</option>
                                    </select>
                                </form>
                                                                <div style="margin-top: 8px; padding: 8px 12px; background: rgba(30, 41, 59, 0.03); border-radius: 8px; border: 1px solid rgba(30, 41, 59, 0.08); font-size: 0.7rem; display: flex; flex-direction: column; gap: 4px;">
                                                                    <div style="font-weight: 700; color: var(--text-main); display: flex; align-items: center; gap: 6px;">
                                                                        <i class="fas fa-steering-wheel" style="color: var(--primary);"></i>
                                                                        @if($booking->chauffeur)
                                                                            {{ $booking->chauffeur->user->name }}
                                                                        @else
                                                                            No Driver Assigned
                                                                        @endif
                                                                    </div>
                                                                    @if($booking->chauffeur && $booking->chauffeur->vehicle)
                                                                        <div style="display: flex; align-items: center; gap: 4px; color: var(--text-muted);">
                                                                            <i class="fas fa-car-side" style="font-size: 0.65rem;"></i>
                                                                            <span style="background: #e2e8f0; padding: 1px 5px; border-radius: 4px; font-family: monospace; font-weight: 800; font-size: 0.65rem; color: #334155; border: 1px solid #cbd5e1;">{{ $booking->chauffeur->vehicle->license_plate }}</span>
                                                                        </div>
                                                                    @endif
                                                                    <div style="display: flex; align-items: center; gap: 6px; margin-top: 2px;">
                                                                        <span style="background: {{ $booking->driver_schedule_status === 'accepted' ? '#ecfdf5' : ($booking->driver_schedule_status === 'declined' ? '#fef2f2' : '#fff7ed') }}; color: {{ $booking->driver_schedule_status === 'accepted' ? '#065f46' : ($booking->driver_schedule_status === 'declined' ? '#991b1b' : '#9a3412') }}; padding: 1px 6px; border-radius: 4px; font-size: 0.6rem; font-weight: 900; text-transform: uppercase; border: 1px solid {{ $booking->driver_schedule_status === 'accepted' ? '#a7f3d0' : ($booking->driver_schedule_status === 'declined' ? '#fecaca' : '#ffedd5') }};">
                                                                            Driver: {{ $booking->driver_schedule_status ?: 'Pending' }}
                                                                        </span>
                                                                        <span style="background: #dbeafe; color: #1e40af; padding: 1px 6px; border-radius: 4px; font-size: 0.6rem; font-weight: 900; text-transform: uppercase; border: 1px solid #bfdbfe;">
                                                                            Unified
                                                                        </span>
                                                                    </div>
                                                                </div>
                            @else
                                <form action="{{ route('bookings.update-status', $booking) }}" method="POST" style="margin: 0;">
                                    @csrf
                                    <select name="status" onchange="
                                            if(this.value === 'cancelled') {
                                                this.value = '{{ $booking->status }}';
                                                document.getElementById('cancel-modal-{{ $booking->id }}').style.display = 'flex';
                                            } else if('{{ $isClosedTour }}' === '1') {
                                                if(confirm('This tour is closed/ongoing. Are you sure you want to update its status?')) {
                                                    this.form.submit();
                                                } else {
                                                    window.location.reload();
                                                }
                                            } else {
                                                this.form.submit();
                                            }
                                        " 
                                            {{ ($booking->status === 'completed' || $isLive) ? 'disabled' : '' }} 
                                            title="{{ $isClosedTour ? 'Tour is closed (Click to update anyway)' : ($isLive ? 'Cannot update status of a live trip' : '') }}"
                                            style="width: auto; display: inline-block; padding: 4px 8px; border-radius: 6px; border: 1px solid var(--border); font-size: 0.8rem; background: {{ $booking->status === 'confirmed' ? '#dcfce7' : ($booking->status === 'pending' ? '#fef9c3' : '#f8fafc') }}; color: {{ $booking->status === 'confirmed' ? '#166534' : ($booking->status === 'pending' ? '#854d0e' : '#64748b') }}; font-weight: 600; cursor: {{ ($booking->status === 'completed' || $isLive) ? 'not-allowed' : 'pointer' }};">
                                        <option value="pending" {{ $booking->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="confirmed" {{ $booking->status === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                        <option value="cancelled" {{ $booking->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                        <option value="completed" {{ $booking->status === 'completed' ? 'selected' : '' }}>Completed</option>
                                    </select>
                                </form>
                                <div style="margin-top: 8px; padding: 8px 12px; background: rgba(30, 41, 59, 0.03); border-radius: 8px; border: 1px solid rgba(30, 41, 59, 0.08); font-size: 0.7rem; display: flex; flex-direction: column; gap: 4px;">
                                    <div style="font-weight: 700; color: var(--text-main); display: flex; align-items: center; gap: 6px;">
                                        <i class="fas fa-steering-wheel" style="color: var(--primary);"></i>
                                        @if($booking->chauffeur)
                                            {{ $booking->chauffeur->user->name }}
                                        @else
                                            No Driver Assigned
                                        @endif
                                    </div>
                                    @if($booking->chauffeur && $booking->chauffeur->vehicle)
                                        <div style="display: flex; align-items: center; gap: 4px; color: var(--text-muted);">
                                            <i class="fas fa-car-side" style="font-size: 0.65rem;"></i>
                                            <span style="background: #e2e8f0; padding: 1px 5px; border-radius: 4px; font-family: monospace; font-weight: 800; font-size: 0.65rem; color: #334155; border: 1px solid #cbd5e1;">{{ $booking->chauffeur->vehicle->license_plate }}</span>
                                        </div>
                                    @endif
                                    <div style="display: flex; align-items: center; gap: 6px; margin-top: 2px; flex-wrap: wrap;">
                                        <span style="background: {{ $booking->driver_schedule_status === 'accepted' ? '#ecfdf5' : ($booking->driver_schedule_status === 'declined' ? '#fef2f2' : '#fff7ed') }}; color: {{ $booking->driver_schedule_status === 'accepted' ? '#065f46' : ($booking->driver_schedule_status === 'declined' ? '#991b1b' : '#9a3412') }}; padding: 1px 6px; border-radius: 4px; font-size: 0.6rem; font-weight: 900; text-transform: uppercase; border: 1px solid {{ $booking->driver_schedule_status === 'accepted' ? '#a7f3d0' : ($booking->driver_schedule_status === 'declined' ? '#fecaca' : '#ffedd5') }};">
                                            Driver: {{ $booking->driver_schedule_status ?: 'Pending' }}
                                        </span>
                                        @if($booking->chauffeur_id && $packageBookings->where('chauffeur_id', $booking->chauffeur_id)->count() > 1)
                                            @php
                                                $groupColors = ['#2563eb', '#8b5cf6', '#0d9488', '#4f46e5', '#e11d48'];
                                                $colorIndex = ($booking->chauffeur_id ?? 0) % count($groupColors);
                                                $groupColor = $groupColors[$colorIndex];
                                            @endphp
                                            <span style="background: {{ $groupColor }}1a; color: {{ $groupColor }}; padding: 1px 6px; border-radius: 4px; font-size: 0.65rem; font-weight: 800; border: 1px solid {{ $groupColor }}33;">
                                                Unified
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        @else
                            @php
                                $showClosed = false;
                                if ($booking->payment_status === 'pending' && $booking->isTourismBooking()) {
                                    $firstItem = $booking->items->first();
                                    if ($firstItem && $firstItem->bookable_type === 'Modules\Tourism\Models\TourismPackage') {
                                        $package = $firstItem->bookable;
                                        if ($package && $package->package_type === 'scheduled' && $package->departure_date) {
                                            $depTime = \Carbon\Carbon::parse($package->departure_date)->setTime(9, 0, 0);
                                            if (now()->greaterThanOrEqualTo($depTime)) {
                                                $showClosed = true;
                                            }
                                        }
                                    }
                                }
                            @endphp

                            @if($showClosed)
                                <span class="status-badge status-inactive" style="background: #e2e8f0; color: #475569;" title="This tour has departed and is now closed because payment was not completed.">
                                    Closed
                                </span>
                            @else
                                <span class="status-badge {{ $booking->status === 'confirmed' ? 'status-active' : ($booking->status === 'cancelled' ? 'status-inactive' : 'status-suspended') }}">
                                    {{ ucfirst($booking->status) }}
                                </span>
                            @endif
                        @endhasanyrole
                    </td>
                    <td>{{ $booking->created_at->format('M d, Y') }}</td>
                    <td>
                        <div class="action-buttons">
                            @if($booking->is_grouped)
                                @if($booking->status === 'cancelled')
                                    @hasanyrole('Super Admin|Operations Admin')
                                        <form action="{{ route('bookings.reverse-cancellation', $booking) }}" method="POST" style="display: inline-block; margin: 0;" onsubmit="return confirm('Are you sure you want to reverse the cancellation and restore all bookings in this unified trip group?')">
                                            @csrf
                                            <input type="hidden" name="propagate_group" value="1">
                                            <button type="submit" class="action-btn" title="Reverse Cancellation" style="background: rgba(16, 185, 129, 0.1); color: #10b981; border: none; padding: 6px; border-radius: 6px; cursor: pointer; display: flex; align-items: center; justify-content: center; width: 32px; height: 32px;">
                                                <i class="fas fa-undo"></i>
                                            </button>
                                        </form>
                                    @endhasanyrole
                                @endif
                            @else
                                <a href="{{ route('admin.bookings.show', $booking) }}" class="action-btn edit" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if($booking->status === 'cancelled')
                                    @hasanyrole('Super Admin|Operations Admin')
                                        <form action="{{ route('bookings.reverse-cancellation', $booking) }}" method="POST" style="display: inline-block; margin: 0;" onsubmit="return confirm('Are you sure you want to reverse the cancellation and restore booking {{ $booking->booking_reference }} to its previous status ({{ ucfirst($booking->previous_status ?: 'pending') }})?')">
                                            @csrf
                                            <button type="submit" class="action-btn" title="Reverse Cancellation" style="background: rgba(16, 185, 129, 0.1); color: #10b981; border: none; padding: 6px; border-radius: 6px; cursor: pointer; display: flex; align-items: center; justify-content: center; width: 32px; height: 32px;">
                                                <i class="fas fa-undo"></i>
                                            </button>
                                        </form>
                                    @endhasanyrole
                                @endif

                            @if($booking->isTourismBooking() && $booking->payment_status === 'paid' && $booking->status === 'confirmed')
                                @hasanyrole('Super Admin|Operations Admin')
                                    @php
                                        $isLive = $booking->trip_status === 'in_progress' || $booking->return_trip_status === 'in_progress';
                                    @endphp

                                    @if($isLive)
                                        <button type="button" disabled title="Cannot edit guest count during a live trip"
                                                class="action-btn"
                                                style="background: rgba(0, 0, 0, 0.05); color: #94a3b8; border: none; padding: 6px; border-radius: 6px; cursor: not-allowed; display: flex; align-items: center; justify-content: center; width: 32px; height: 32px;">
                                            <i class="fas fa-user-plus"></i>
                                        </button>
                                        
                                        @if($totalGuests > 3)
                                            <button type="button" disabled title="Cannot split passengers during a live trip"
                                                    class="action-btn" 
                                                    style="background: rgba(0, 0, 0, 0.05); color: #94a3b8; border: none; padding: 6px; border-radius: 6px; cursor: not-allowed; display: flex; align-items: center; justify-content: center; width: 32px; height: 32px;">
                                                <i class="fas fa-scissors"></i>
                                            </button>
                                        @endif
                                    @else
                                        <button type="button" onclick="document.getElementById('edit-guests-modal-{{ $booking->id }}').style.display='flex'" 
                                                class="action-btn" title="Edit Guest Count"
                                                style="background: rgba(30, 58, 138, 0.1); color: var(--primary); border: none; padding: 6px; border-radius: 6px; cursor: pointer; display: flex; align-items: center; justify-content: center; width: 32px; height: 32px;">
                                            <i class="fas fa-user-plus"></i>
                                        </button>

                                        @if($totalGuests > 3)
                                            <button type="button" onclick="document.getElementById('split-modal-{{ $booking->id }}').style.display='flex'" 
                                                    class="action-btn" title="Split Passengers" 
                                                    style="background: rgba(245, 158, 11, 0.1); color: #d97706; border: none; padding: 6px; border-radius: 6px; cursor: pointer; display: flex; align-items: center; justify-content: center; width: 32px; height: 32px;">
                                                <i class="fas fa-scissors"></i>
                                            </button>
                                        @endif
                                    @endif
                                @endhasanyrole
                            @endif
                            
                            @role('Super Admin')
                                @php
                                    $isOngoing = $booking->trip_status === 'in_progress' || $booking->return_trip_status === 'in_progress';
                                    $isBtnDisabled = $isOngoing || $isClosedTour;
                                    $btnTitle = $isClosedTour ? 'Tour is closed' : ($isOngoing ? 'Ongoing trips cannot be deleted' : 'Delete Booking');
                                @endphp
                                <form action="{{ route('admin.bookings.destroy', $booking) }}" method="POST" 
                                      @if(!$isBtnDisabled) onsubmit="return confirm('Are you sure you want to delete this booking? This action cannot be undone.');" @endif
                                      style="display:inline-block; margin:0;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="action-btn delete" 
                                            {{ $isBtnDisabled ? 'disabled' : '' }}
                                            title="{{ $btnTitle }}" 
                                            style="background: none; border: none; cursor: {{ $isBtnDisabled ? 'not-allowed' : 'pointer' }}; opacity: {{ $isBtnDisabled ? '0.4' : '1' }}; transition: opacity 0.2s;">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            @endrole
                            @endif
                        </div>
                    </td>
                </tr>
                @if($booking->is_grouped)
                    <tr x-show="expanded" x-transition.opacity style="background: rgba(37, 99, 235, 0.015); border-bottom: 2px solid rgba(37, 99, 235, 0.1);">
                        @role('Super Admin')
                            <td style="border: none;"></td>
                        @endrole
                        <td colspan="5" style="padding: 0 15px 20px 15px; border: none;">
                            <div style="background: var(--bg-main); border-radius: 12px; border: 1px solid rgba(37, 99, 235, 0.15); font-size: 0.8rem; box-shadow: 0 4px 6px rgba(0,0,0,0.02); margin-left: 2px;">
                                <div style="padding: 12px 16px; border-bottom: 1px solid rgba(37, 99, 235, 0.1); background: rgba(37, 99, 235, 0.03); font-weight: 800; text-transform: uppercase; font-size: 0.7rem; color: #2563eb; display: flex; align-items: center; gap: 8px; border-radius: 12px 12px 0 0;">
                                    <i class="fas fa-users" style="font-size: 0.8rem;"></i> Unified Bookings Passenger List ({{ $booking->group_bookings->count() }} Bookings)
                                </div>
                                <div style="padding: 10px 16px;">
                                    <table style="width: 100%; border-collapse: collapse;">
                                        <tbody>
                                            @foreach($booking->group_bookings as $b)
                                                <tr style="border-bottom: 1px dashed rgba(0,0,0,0.05); background: transparent;">
                                                    <td style="padding: 10px 15px; width: 25%; border: none;">
                                                        <div style="font-weight: 800; color: var(--text-main); display: flex; align-items: center; gap: 8px;">
                                                            {{ $b->customer_name ?: ($b->user->name ?? 'Guest') }}
                                                            <span style="background: rgba(249, 115, 22, 0.1); color: #c2410c; padding: 2px 6px; border-radius: 4px; font-size: 0.65rem; font-weight: 800;">
                                                                {{ $b->items->sum('quantity') }} pax
                                                            </span>
                                                        </div>
                                                    </td>
                                                    <td style="padding: 10px 10px; width: 15%; color: var(--text-muted); font-size: 0.75rem; border: none; font-family: monospace;">
                                                        <i class="fas fa-hashtag" style="opacity: 0.5;"></i> {{ $b->booking_reference }}
                                                    </td>
                                                    <td style="padding: 10px 10px; width: 15%; color: var(--text-muted); font-size: 0.75rem; border: none;">
                                                        <i class="fas fa-phone" style="opacity: 0.5;"></i> {{ $b->customer_phone ?: ($b->user->phone ?? 'N/A') }}
                                                    </td>
                                                    <td style="padding: 10px 10px; width: 15%; border: none;">
                                                        @if($b->status === 'cancelled')
                                                            <span class="status-badge" style="background: #f1f5f9; color: #94a3b8; border: 1px solid var(--border); font-size: 0.75rem; padding: 4px 8px; border-radius: 6px; font-weight: 600;">
                                                                {{ $b->payment_status === 'pending' ? 'Not Paid' : ucfirst(str_replace('_', ' ', $b->payment_status)) }}
                                                            </span>
                                                        @else
                                                        <form action="{{ route('bookings.update-payment', $b) }}" method="POST" style="margin: 0; display: inline-flex; align-items: center; gap: 6px;" id="payment-form-{{ $b->id }}">
                                                            @csrf
                                                            @php
                                                                $bPaymentBg = '#f8fafc';
                                                                $bPaymentColor = '#64748b';
                                                                
                                                                if ($b->status !== 'confirmed') {
                                                                    $bPaymentBg = '#f1f5f9';
                                                                    $bPaymentColor = '#94a3b8';
                                                                } else {
                                                                    switch($b->payment_status) {
                                                                        case 'paid': $bPaymentBg = '#dcfce7'; $bPaymentColor = '#166534'; break;
                                                                        case 'pending': $bPaymentBg = '#fef9c3'; $bPaymentColor = '#854d0e'; break;
                                                                        case 'partially_paid': $bPaymentBg = '#e0f2fe'; $bPaymentColor = '#0369a1'; break;
                                                                        case 'refund': $bPaymentBg = '#fee2e2'; $bPaymentColor = '#991b1b'; break;
                                                                        case 'refunded': $bPaymentBg = '#f1f5f9'; $bPaymentColor = '#475569'; break;
                                                                    }
                                                                }
                                                            @endphp
                                                            <div style="display: flex; flex-direction: column; gap: 4px; align-items: flex-start;">
                                                                <select name="payment_status" id="payment-status-{{ $b->id }}"
                                                                        onchange="onTablePaymentSelectChange(this, '{{ $b->id }}', '{{ $b->total_amount }}')"
                                                                        {{ ($b->status !== 'confirmed' || $isLive) ? 'disabled' : '' }}
                                                                        title="{{ $isLive ? 'Cannot update payment status of a live trip' : ($b->status !== 'confirmed' ? 'Confirm booking first to update payment' : '') }}"
                                                                        style="width: auto; display: inline-block; padding: 2px 6px; border-radius: 6px; border: 1px solid var(--border); font-size: 0.75rem; background: {{ $bPaymentBg }}; color: {{ $bPaymentColor }}; font-weight: 600; cursor: {{ ($b->status !== 'confirmed' || $isLive) ? 'not-allowed' : 'pointer' }};">
                                                                    <option value="pending" {{ $b->payment_status === 'pending' ? 'selected' : '' }}>Pending</option>
                                                                    <option value="partially_paid" {{ $b->payment_status === 'partially_paid' ? 'selected' : '' }}>Partial</option>
                                                                    <option value="paid" {{ $b->payment_status === 'paid' ? 'selected' : '' }}>Paid</option>
                                                                    <option value="refund" {{ $b->payment_status === 'refund' ? 'selected' : '' }}>Refund</option>
                                                                    <option value="refunded" {{ $b->payment_status === 'refunded' ? 'selected' : '' }}>Refunded</option>
                                                                </select>
                                                                
                                                                <div id="partial-input-container-{{ $b->id }}" style="display: {{ $b->payment_status === 'partially_paid' ? 'inline-flex' : 'none' }}; align-items: center; gap: 4px; margin-top: 2px;">
                                                                    <span style="font-size: 0.7rem; color: var(--text-muted); font-weight: 700;">₵</span>
                                                                    <input type="number" step="0.01" name="partial_amount" id="partial-amount-{{ $b->id }}" 
                                                                           value="" 
                                                                           placeholder="New" 
                                                                           max="{{ $b->total_amount - ($b->partial_amount ?? 0) }}"
                                                                           style="width: 60px; padding: 1px 2px; border: 1px solid var(--border); border-radius: 4px; font-size: 0.65rem; background: var(--bg-main); color: var(--text-main); font-weight: 600;">
                                                                    <button type="submit" style="background: var(--primary); color: white; border: none; padding: 1px 4px; border-radius: 4px; font-size: 0.65rem; font-weight: 800; cursor: pointer;">Save</button>
                                                                </div>
                                                            </div>
                                                        </form>
                                                        @endif
                                                    </td>
                                                    <td style="padding: 10px 10px; width: 15%; border: none;">
                                                        @hasanyrole('Super Admin|Operations Admin')
                                                            <form action="{{ route('bookings.update-status', $b) }}" method="POST" style="margin: 0;">
                                                                @csrf
                                                                <select name="status" onchange="
                                                                        if(this.value === 'cancelled') {
                                                                            this.value = '{{ $b->status }}';
                                                                            document.getElementById('cancel-modal-{{ $b->id }}').style.display = 'flex';
                                                                        } else {
                                                                            this.form.submit();
                                                                        }
                                                                    " 
                                                                        {{ ($b->status === 'completed' || $isLive) ? 'disabled' : '' }} 
                                                                        style="width: auto; display: inline-block; padding: 2px 6px; border-radius: 6px; border: 1px solid var(--border); font-size: 0.75rem; background: {{ $b->status === 'confirmed' ? '#dcfce7' : ($b->status === 'pending' ? '#fef9c3' : '#f8fafc') }}; color: {{ $b->status === 'confirmed' ? '#166534' : ($b->status === 'pending' ? '#854d0e' : '#64748b') }}; font-weight: 600; cursor: {{ ($b->status === 'completed' || $isLive) ? 'not-allowed' : 'pointer' }};">
                                                                    <option value="pending" {{ $b->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                                                    <option value="confirmed" {{ $b->status === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                                                    <option value="cancelled" {{ $b->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                                                    <option value="completed" {{ $b->status === 'completed' ? 'selected' : '' }}>Completed</option>
                                                                </select>
                                                            </form>
                                                        @else
                                                            <span class="status-badge {{ $b->status === 'confirmed' ? 'status-active' : ($b->status === 'cancelled' ? 'status-inactive' : 'status-suspended') }}" style="font-size: 0.75rem; padding: 4px 8px; border-radius: 6px; font-weight: 600;">
                                                                {{ ucfirst($b->status) }}
                                                            </span>
                                                        @endhasanyrole
                                                    </td>
                                                    <td style="padding: 10px 15px; width: 15%; border: none;">
                                                        <div style="display: flex; gap: 6px; justify-content: flex-end; align-items: center;">
                                                            @hasanyrole('Super Admin|Operations Admin')
                                                                @php
                                                                    $isLive = $b->trip_status === 'in_progress' || $b->return_trip_status === 'in_progress';
                                                                    $bTotalGuests = $b->items->sum('quantity');
                                                                @endphp
                                                                @if($isLive)
                                                                    <button type="button" disabled title="Cannot edit guest count during a live trip" class="action-btn edit" style="padding: 4px 8px; height: auto; width: auto; background: rgba(0,0,0,0.05); color: #94a3b8; border: 1px solid rgba(0,0,0,0.05); border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.7rem; font-weight: 700; cursor: not-allowed;"><i class="fas fa-user-plus"></i></button>
                                                                    @if($bTotalGuests > 3)
                                                                        <button type="button" disabled title="Cannot split passengers during a live trip" class="action-btn edit" style="padding: 4px 8px; height: auto; width: auto; background: rgba(0,0,0,0.05); color: #94a3b8; border: 1px solid rgba(0,0,0,0.05); border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.7rem; font-weight: 700; cursor: not-allowed;"><i class="fas fa-scissors"></i></button>
                                                                    @endif
                                                                @else
                                                                    <button type="button" onclick="document.getElementById('edit-guests-modal-{{ $b->id }}').style.display='flex'" class="action-btn edit" style="padding: 4px 8px; height: auto; width: auto; background: rgba(30, 58, 138, 0.1); color: var(--primary); border: 1px solid rgba(30, 58, 138, 0.1); border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.7rem; font-weight: 700; cursor: pointer;" title="Edit Guest Count"><i class="fas fa-user-plus"></i></button>
                                                                    @if($bTotalGuests > 3)
                                                                        <button type="button" onclick="document.getElementById('split-modal-{{ $b->id }}').style.display='flex'" class="action-btn edit" style="padding: 4px 8px; height: auto; width: auto; background: rgba(245, 158, 11, 0.1); color: #d97706; border: 1px solid rgba(245, 158, 11, 0.1); border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.7rem; font-weight: 700; cursor: pointer;" title="Split Passengers"><i class="fas fa-scissors"></i></button>
                                                                    @endif
                                                                @endif
                                                                @if($b->status === 'cancelled')
                                                                    <form action="{{ route('bookings.reverse-cancellation', $b) }}" method="POST" style="display: inline-block; margin: 0;" onsubmit="return confirm('Are you sure you want to reverse the cancellation and restore booking {{ $b->booking_reference }}?')">
                                                                        @csrf
                                                                        <button type="submit" class="action-btn" title="Reverse Cancellation" style="background: rgba(16, 185, 129, 0.1); color: #10b981; border: none; padding: 4px; border-radius: 6px; cursor: pointer; display: flex; align-items: center; justify-content: center; width: 24px; height: 24px;">
                                                                            <i class="fas fa-undo" style="font-size: 0.7rem;"></i>
                                                                        </button>
                                                                    </form>
                                                                @endif
                                                            @endhasanyrole
                                                            <a href="{{ route('admin.bookings.show', $b) }}" class="action-btn edit" style="padding: 4px 8px; height: auto; width: auto; background: var(--bg-panel); color: var(--primary); border: 1px solid var(--border); border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.7rem; font-weight: 700;" title="View Details">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endif

            @endforeach

        @php
            $allModalBookings = collect();
            foreach($displayBookings as $b) {
                $allModalBookings->push($b);
                if ($b->is_grouped && $b->group_bookings) {
                    foreach($b->group_bookings as $child) {
                        $allModalBookings->push($child);
                    }
                }
            }
        @endphp
        @foreach($allModalBookings->unique('id') as $modalBooking)
            @php
                $modalGuests = $modalBooking->items->sum('quantity');
            @endphp
            <!-- Edit Guests Modal -->
            <div id="edit-guests-modal-{{ $modalBooking->id }}" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 99999; justify-content: center; align-items: center; backdrop-filter: blur(4px);">
                <div style="background: var(--bg-main, #ffffff); border-radius: 12px; width: 90%; max-width: 400px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); overflow: hidden;">
                    <div style="padding: 25px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
                        <h3 style="margin: 0; color: var(--text-main); font-size: 1.1rem; font-family: 'Outfit', sans-serif;"><i class="fas fa-user-edit" style="color: var(--primary);"></i> Edit Guest Count</h3>
                        <button type="button" onclick="document.getElementById('edit-guests-modal-{{ $modalBooking->id }}').style.display='none'" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: var(--text-muted);">&times;</button>
                    </div>
                    <form action="{{ route('bookings.update-guests', $modalBooking) }}" method="POST" style="margin: 0; padding: 30px 10px;">
                        @csrf
                        <div style="margin-bottom: 25px;">
                            <label style="display: block; font-weight: 700; color: var(--text-main); font-size: 0.85rem; margin-bottom: 12px; margin-left: 20px; margin-right: 20px;">New Guest Total</label>
                            <input type="number" name="new_guest_count" value="{{ $modalGuests }}" min="1" required 
                                   style="width: calc(100% - 40px); padding: 14px; border-radius: 12px; border: 1px solid var(--border); background: var(--bg-panel); color: var(--text-main); font-weight: 700; font-size: 1.1rem; margin-left: 20px; margin-right: 20px;">
                            <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 12px; margin-left: 20px; margin-right: 20px;">
                                <i class="fas fa-info-circle"></i> This will automatically update the booking price.
                            </p>
                        </div>
                        <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 20px; margin-right: 20px; margin-bottom: 20px;">
                            <button type="button" onclick="document.getElementById('edit-guests-modal-{{ $modalBooking->id }}').style.display='none'" 
                                    style="padding: 12px 20px; border: 1px solid var(--border); background: var(--bg-main); border-radius: 10px; font-weight: 600; cursor: pointer; color: var(--text-main);">Cancel</button>
                            <button type="submit" 
                                    style="padding: 12px 25px; border: none; background: var(--primary); color: white; border-radius: 10px; font-weight: 700; cursor: pointer; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
            @if($modalGuests > 3)
                <div id="split-modal-{{ $modalBooking->id }}" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 99999; justify-content: center; align-items: center; backdrop-filter: blur(4px);">
                    <div style="background: var(--bg-main, #ffffff); border-radius: 16px; width: 90%; max-width: 450px; padding: 0; box-shadow: 0 20px 40px rgba(0,0,0,0.3); overflow: hidden; border: 1px solid var(--border);">
                        <div style="padding: 20px 25px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; background: var(--bg-main);">
                            <h3 style="margin: 0; color: var(--text-main); font-size: 1.1rem; display: flex; align-items: center; gap: 10px; font-family: 'Outfit', sans-serif; font-weight: 700;">
                                <i class="fas fa-scissors" style="color: var(--accent);"></i> Split Booking
                            </h3>
                            <button type="button" onclick="document.getElementById('split-modal-{{ $modalBooking->id }}').style.display='none'" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--text-muted);">&times;</button>
                        </div>
                        
                        <form action="{{ route('bookings.split', $modalBooking) }}" method="POST" style="margin: 0; padding: 25px;">
                            @csrf
                            
                            <div style="background: var(--bg-panel, rgba(0,0,0,0.03)); padding: 15px; border-radius: 12px; border: 1px solid var(--border); margin: 0 15px 25px 15px;">
                                <div style="font-size: 0.7rem; color: var(--text-muted); font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">Booking Summary</div>
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <div style="font-weight: 700; color: var(--text-main); font-size: 0.95rem;">{{ $modalBooking->customer_name ?: ($modalBooking->user->name ?? 'Guest') }}</div>
                                    <div style="background: var(--primary); color: white; padding: 2px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 700;">
                                        {{ $modalGuests }} Persons
                                    </div>
                                </div>
                            </div>

                            <div style="margin: 0 15px 20px 15px;">
                                <label style="display: block; font-weight: 700; color: var(--text-main); font-size: 0.85rem; margin-bottom: 10px;">Number of persons to split off</label>
                                <div style="position: relative;">
                                    <input type="number" name="split_quantity" min="1" max="{{ $modalGuests - 1 }}" required 
                                           style="width: 100%; padding: 12px 15px; border-radius: 10px; border: 2px solid var(--border); background: var(--bg-panel); color: var(--text-main); font-weight: 700; font-size: 1.1rem; outline: none; transition: border-color 0.2s;"
                                           onfocus="this.style.borderColor='var(--accent)'" onblur="this.style.borderColor='var(--border)'">
                                    <div style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 0.8rem; pointer-events: none;">Persons</div>
                                </div>
                                <p style="margin-top: 10px; font-size: 0.75rem; color: var(--text-muted); line-height: 1.4; display: flex; gap: 8px; align-items: flex-start;">
                                    <i class="fas fa-info-circle" style="color: var(--accent); margin-top: 2px;"></i>
                                    <span>This will create a new booking record for the split portion, allowing you to assign a separate driver.</span>
                                </p>
                            </div>

                            <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 20px; margin-bottom: 10px; margin-right: 15px;">
                                <button type="button" onclick="document.getElementById('split-modal-{{ $modalBooking->id }}').style.display='none'" 
                                        style="padding: 10px 20px; border: 1px solid var(--border); background: var(--bg-main); border-radius: 10px; font-weight: 700; cursor: pointer; color: var(--text-main); font-size: 0.9rem; transition: all 0.2s;">Cancel</button>
                                <button type="submit" style="padding: 10px 25px; border: none; background: var(--accent); color: white; border-radius: 10px; font-weight: 800; cursor: pointer; box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3); font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px;">Perform Split</button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            <!-- Cancel Booking Modal -->
            <div id="cancel-modal-{{ $modalBooking->id }}" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 99999; justify-content: center; align-items: center; backdrop-filter: blur(4px);">
                <div style="background: var(--bg-main, #ffffff); border-radius: 16px; width: 90%; max-width: 450px; padding: 0; box-shadow: 0 20px 40px rgba(0,0,0,0.3); overflow: hidden; border: 1px solid var(--border);">
                    <div style="padding: 20px 25px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; background: var(--bg-main);">
                        <h3 style="margin: 0; color: #ef4444; font-size: 1.1rem; display: flex; align-items: center; gap: 10px; font-family: 'Outfit', sans-serif; font-weight: 700;">
                            <i class="fas fa-exclamation-triangle" style="color: #ef4444;"></i> Cancel Booking
                        </h3>
                        <button type="button" onclick="document.getElementById('cancel-modal-{{ $modalBooking->id }}').style.display='none'" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--text-muted);">&times;</button>
                    </div>
                    
                    <form action="{{ route('bookings.cancel', $modalBooking) }}" method="POST" style="margin: 0; padding: 25px;">
                        @csrf
                        @if($modalBooking->is_grouped)
                            <input type="hidden" name="propagate_group" value="1">
                        @endif
                        
                        <div style="background: var(--bg-panel, rgba(0,0,0,0.03)); padding: 15px; border-radius: 12px; border: 1px solid var(--border); margin-top: 10px; margin-bottom: 25px; margin-left: 20px; margin-right: 20px;">
                            <div style="font-size: 0.7rem; color: var(--text-muted); font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">Booking Reference</div>
                            <div style="font-weight: 700; color: var(--text-main); font-size: 0.95rem;">{{ $modalBooking->booking_reference }}</div>
                        </div>

                        <div style="margin-top: 25px; margin-bottom: 25px; margin-left: 20px; margin-right: 20px;">
                            <label style="display: block; font-weight: 700; color: var(--text-main); font-size: 0.85rem; margin-bottom: 12px;">Reason for Cancellation</label>
                            <textarea name="cancellation_reason" rows="4" placeholder="Enter cancellation reason..." required 
                                      style="width: 100%; padding: 15px; border-radius: 12px; border: 2px solid var(--border); background: var(--bg-panel); color: var(--text-main); font-size: 0.9rem; resize: vertical; outline: none; transition: border-color 0.2s;"
                                      onfocus="this.style.borderColor='#ef4444'" onblur="this.style.borderColor='var(--border)'"></textarea>
                        </div>

                        <div style="display: flex; justify-content: flex-end; margin-top: 25px; margin-left: 20px; margin-right: 20px; margin-bottom: 20px;">
                            <button type="button" onclick="document.getElementById('cancel-modal-{{ $modalBooking->id }}').style.display='none'" 
                                    style="padding: 12px 20px; border: 1px solid var(--border); background: var(--bg-main); border-radius: 10px; font-weight: 600; cursor: pointer; color: var(--text-main); margin-right: 12px; margin-bottom: 10px;">Keep Booking</button>
                            <button type="submit" 
                                    style="padding: 12px 25px; border: none; background: #ef4444; color: white; border-radius: 10px; font-weight: 700; cursor: pointer; box-shadow: 0 4px 10px rgba(239, 68, 68, 0.2); display: flex; align-items: center; gap: 6px; margin-bottom: 10px;">
                                <i class="fas fa-trash-alt"></i> Cancel Booking
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endforeach
        </tbody>
    @empty
        <tbody>
            <tr>
                <td colspan="6" class="text-center" style="padding: 40px; color: var(--text-muted); font-style: italic;">No bookings found in this category.</td>
            </tr>
        </tbody>
    @endforelse
</table>

<!-- Bulk Assignment Modals for Tourism Packages -->
@forelse($groupedByPackage as $title => $packageBookings)
    @php
        $firstItem = $packageBookings->first()->items->first();
    @endphp
    @if($firstItem && $firstItem->bookable_type === 'Modules\Tourism\Models\TourismPackage')
        <div id="driver-modal-{{ Str::slug($title) }}" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; justify-content: center; align-items: center; backdrop-filter: blur(4px);">
            <div style="background: var(--bg-main, #ffffff); border-radius: 12px; width: 90%; max-width: 600px; max-height: 90vh; overflow-y: auto; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
                <div style="padding: 20px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; background: var(--bg-main, #ffffff); z-index: 10;">
                    <h3 style="margin: 0; color: var(--text-main); font-size: 1.1rem; display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-users-cog" style="color: var(--primary);"></i>
                        Assign Drivers for {{ $title }}
                    </h3>
                    <button type="button" onclick="document.getElementById('driver-modal-{{ Str::slug($title) }}').style.display='none'" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: var(--text-muted);">&times;</button>
                </div>
                
                @php
                    $hasPending = $packageBookings->contains(fn($b) => $b->status !== 'confirmed');
                    $hasUnpaid = $packageBookings->contains(fn($b) => $b->payment_status !== 'paid');
                    $hasUnscheduled = $packageBookings->contains(fn($b) => !$b->scheduled_at);
                    $bulkDisabled = $hasPending || $hasUnpaid || $hasUnscheduled;
                @endphp

                <form action="{{ route('bookings.bulk-assign-chauffeur') }}" method="POST" style="margin: 0; padding: 30px 35px;">
                    @csrf
                    
                    <div style="background: var(--bg-panel, rgba(0,0,0,0.02)); padding: 20px; border-radius: 12px; border: 1px solid var(--border); margin-bottom: 25px; opacity: {{ $bulkDisabled ? '0.7' : '1' }};">
                        <label style="display: block; font-weight: 700; color: var(--text-main); font-size: 0.9rem; margin-bottom: 10px;">Master Dropdown: Assign to ALL Bookings (1 Bus)</label>
                        <select onchange="updateAllDropdowns(this.value, '{{ Str::slug($title) }}')" 
                                {{ $bulkDisabled ? 'disabled' : '' }}
                                title="{{ $bulkDisabled ? 'Master assignment is disabled because some bookings are unconfirmed or unpaid.' : '' }}"
                                style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid var(--border); font-size: 0.9rem; outline: none; background: var(--bg-main, #ffffff); color: var(--text-main); cursor: {{ $bulkDisabled ? 'not-allowed' : 'pointer' }};">
                            <option value="">-- Select a driver to apply to all --</option>
                            @foreach($chauffeurs as $chauffeur)
                                <option value="{{ $chauffeur->id }}">{{ $chauffeur->user->name }}</option>
                            @endforeach
                        </select>
                        @if($bulkDisabled)
                            <div style="font-size: 0.75rem; color: #dc2626; font-weight: 700; margin-top: 8px; display: flex; align-items: center; gap: 6px;">
                                <i class="fas fa-exclamation-triangle"></i> Master assignment disabled: All bookings must be confirmed, paid, and scheduled.
                            </div>
                        @else
                            <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 8px;"><i class="fas fa-info-circle"></i> Use this to instantly assign a single driver to all bookings below.</div>
                        @endif
                    </div>
                    
                    <h4 style="margin: 0 0 15px 0; font-size: 1rem; color: var(--text-main); border-bottom: 1px solid var(--border); padding-bottom: 10px; font-family: 'Outfit', sans-serif;">Individual Bookings</h4>
                    
                    <div style="display: flex; flex-direction: column; gap: 15px;">
                        @foreach($packageBookings as $booking)
                            @php
                                $totalGuests = $booking->items->sum('quantity');
                                $isIndividualDisabled = $booking->status !== 'confirmed' || $booking->payment_status !== 'paid' || !$booking->scheduled_at;
                                
                                $reasons = [];
                                if ($booking->status !== 'confirmed') $reasons[] = 'Confirmation';
                                if ($booking->payment_status !== 'paid') $reasons[] = 'Payment';
                                if (!$booking->scheduled_at) $reasons[] = 'Scheduling';
                            @endphp
                            <div style="display: flex; align-items: center; justify-content: space-between; padding: 15px; border: 1px solid var(--border); border-radius: 12px; background: {{ $isIndividualDisabled ? 'var(--bg-panel, rgba(0,0,0,0.02))' : 'var(--bg-main, #ffffff)' }}; opacity: {{ $isIndividualDisabled ? '0.8' : '1' }}; transition: all 0.2s;">
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <div>
                                        <div style="font-weight: 700; color: var(--text-main); font-size: 0.95rem;">{{ $booking->customer_name ?: ($booking->user->name ?? 'Guest') }}</div>
                                        <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600; margin-top: 2px;">
                                            {{ $totalGuests }} Person(s) • 
                                            @if($isIndividualDisabled)
                                                <span style="color: #dc2626;">Awaiting {{ implode(', ', $reasons) }}</span>
                                            @elseif($booking->chauffeur_id)
                                                <span style="color: #059669;"><i class="fas fa-check-circle"></i> Driver Assigned</span>
                                            @else
                                                <span style="color: #166534;"><i class="fas fa-clock"></i> Ready to Assign</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                
                                <div style="width: 220px;">
                                    @if(!$isIndividualDisabled)
                                        <input type="hidden" name="assignments[{{ $booking->id }}][booking_id]" value="{{ $booking->id }}">
                                        <select name="assignments[{{ $booking->id }}][chauffeur_id]" 
                                                class="driver-dropdown-{{ Str::slug($title) }}" 
                                                style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border); font-size: 0.9rem; outline: none; background: var(--bg-panel, rgba(0,0,0,0.02)); color: var(--text-main); cursor: pointer;">
                                            <option value="">No Driver Assigned</option>
                                            @foreach($chauffeurs as $chauffeur)
                                                <option value="{{ $chauffeur->id }}" {{ $booking->chauffeur_id == $chauffeur->id ? 'selected' : '' }}>
                                                    {{ $chauffeur->user->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    @else
                                        <div style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border); font-size: 0.9rem; background: var(--bg-panel, rgba(0,0,0,0.05)); color: var(--text-muted); cursor: not-allowed; text-align: center;">
                                            Locked
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <div style="margin: 30px 0 10px 0; display: flex; justify-content: flex-end; gap: 15px;">
                        <button type="button" onclick="document.getElementById('driver-modal-{{ Str::slug($title) }}').style.display='none'" style="padding: 12px 25px; border: 1px solid var(--border); background: var(--bg-main, #ffffff); border-radius: 10px; font-weight: 700; cursor: pointer; color: var(--text-main); font-size: 0.95rem;">Cancel</button>
                        <button type="submit" style="padding: 12px 30px; border: none; background: var(--primary); color: white; border-radius: 10px; font-weight: 800; cursor: pointer; box-shadow: 0 4px 12px rgba(0,0,0,0.15); font-size: 0.95rem;">Save Assignments</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Schedule Modal -->
        <div id="schedule-modal-{{ Str::slug($title) }}" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; justify-content: center; align-items: center; backdrop-filter: blur(4px);">
            <div style="background: var(--bg-main, #ffffff); border-radius: 12px; width: 95%; max-width: 750px; max-height: 90vh; overflow-y: auto; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
                <div style="padding: 20px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; background: var(--bg-main, #ffffff); z-index: 10;">
                    <h3 style="margin: 0; color: var(--text-main); font-size: 1.1rem; display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-calendar-alt" style="color: #10b981;"></i>
                        Schedule Dates for {{ $title }}
                    </h3>
                    <button type="button" onclick="document.getElementById('schedule-modal-{{ Str::slug($title) }}').style.display='none'" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: var(--text-muted);">&times;</button>
                </div>
                
                @php
                    $hasPendingOrUnpaid = $packageBookings->contains(fn($b) => $b->status !== 'confirmed' || $b->payment_status !== 'paid');
                @endphp

                <form action="{{ route('bookings.bulk-assign-schedule') }}" method="POST" style="margin: 0; padding: 30px 35px;">
                    @csrf
                    
                    <div style="background: var(--bg-panel, rgba(0,0,0,0.02)); padding: 20px; border-radius: 12px; border: 1px solid var(--border); margin-bottom: 25px; opacity: {{ $hasPendingOrUnpaid ? '0.7' : '1' }};">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <div>
                                <label style="display: block; font-weight: 700; color: var(--text-main); font-size: 0.8rem; margin-bottom: 8px;">Master: Outbound Date</label>
                                <input type="datetime-local" onchange="updateAllDates(this.value, '{{ Str::slug($title) }}', 'outbound')" 
                                        {{ $hasPendingOrUnpaid ? 'disabled' : '' }}
                                        style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border); font-size: 0.85rem; outline: none; background: var(--bg-main, #ffffff); color: var(--text-main);">
                            </div>
                            <div>
                                <label style="display: block; font-weight: 700; color: var(--text-main); font-size: 0.8rem; margin-bottom: 8px;">Master: Return Date</label>
                                <input type="datetime-local" onchange="updateAllDates(this.value, '{{ Str::slug($title) }}', 'return')" 
                                        {{ $hasPendingOrUnpaid ? 'disabled' : '' }}
                                        style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border); font-size: 0.85rem; outline: none; background: var(--bg-main, #ffffff); color: var(--text-main);">
                            </div>
                        </div>
                        @if($hasPendingOrUnpaid)
                            <div style="font-size: 0.75rem; color: #dc2626; font-weight: 700; margin-top: 8px; display: flex; align-items: center; gap: 6px;">
                                <i class="fas fa-exclamation-triangle"></i> Master assignment disabled: All bookings must be confirmed and paid.
                            </div>
                        @else
                            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 10px;"><i class="fas fa-info-circle"></i> Use these to instantly apply dates to all bookings below.</div>
                        @endif
                    </div>
                    
                    <h4 style="margin: 0 0 15px 0; font-size: 1rem; color: var(--text-main); border-bottom: 1px solid var(--border); padding-bottom: 10px; font-family: 'Outfit', sans-serif;">Individual Bookings</h4>
                    
                    <div style="display: flex; flex-direction: column; gap: 15px;">
                        @foreach($packageBookings as $booking)
                            @php
                                $totalGuests = $booking->items->sum('quantity');
                                $isIndividualDisabled = $booking->status !== 'confirmed' || $booking->payment_status !== 'paid';
                                
                                $reasons = [];
                                if ($booking->status !== 'confirmed') $reasons[] = 'Confirmation';
                                if ($booking->payment_status !== 'paid') $reasons[] = 'Payment';
                            @endphp
                            <div style="display: flex; align-items: center; justify-content: space-between; padding: 15px; border: 1px solid var(--border); border-radius: 12px; background: {{ $isIndividualDisabled ? 'var(--bg-panel, rgba(0,0,0,0.02))' : 'var(--bg-main, #ffffff)' }}; opacity: {{ $isIndividualDisabled ? '0.8' : '1' }}; transition: all 0.2s;">
                                <div style="flex: 1; min-width: 0;">
                                    <div>
                                        <div style="font-weight: 700; color: var(--text-main); font-size: 0.95rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $booking->customer_name ?: ($booking->user->name ?? 'Guest') }}</div>
                                        <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600; margin-top: 2px;">
                                            {{ $totalGuests }} Person(s) • 
                                            @if($isIndividualDisabled)
                                                <span style="color: #dc2626;">Awaiting {{ implode(', ', $reasons) }}</span>
                                            @elseif($booking->scheduled_at)
                                                <span style="color: #059669;"><i class="fas fa-check-circle"></i> Scheduled</span>
                                            @else
                                                <span style="color: #166534;"><i class="fas fa-clock"></i> Not Scheduled</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                
                                <div style="width: 380px; display: flex; gap: 10px; margin-left: 20px;">
                                    @if(!$isIndividualDisabled)
                                        <input type="hidden" name="assignments[{{ $booking->id }}][booking_id]" value="{{ $booking->id }}">
                                        <div style="flex: 1;">
                                            <div style="font-size: 0.6rem; font-weight: 800; color: #059669; text-transform: uppercase; margin-bottom: 3px;">Outbound</div>
                                            <input type="datetime-local" name="assignments[{{ $booking->id }}][scheduled_at]" 
                                                    class="date-input-{{ Str::slug($title) }}-outbound" 
                                                    value="{{ $booking->scheduled_at ? \Carbon\Carbon::parse($booking->scheduled_at)->format('Y-m-d\TH:i') : '' }}"
                                                    style="width: 100%; padding: 8px; border-radius: 6px; border: 1px solid var(--border); font-size: 0.75rem; outline: none; background: var(--bg-panel, rgba(0,0,0,0.02)); color: var(--text-main);">
                                        </div>
                                        <div style="flex: 1;">
                                            <div style="font-size: 0.6rem; font-weight: 800; color: #2563eb; text-transform: uppercase; margin-bottom: 3px;">Return</div>
                                            <input type="datetime-local" name="assignments[{{ $booking->id }}][return_scheduled_at]" 
                                                    class="date-input-{{ Str::slug($title) }}-return" 
                                                    value="{{ $booking->return_scheduled_at ? \Carbon\Carbon::parse($booking->return_scheduled_at)->format('Y-m-d\TH:i') : '' }}"
                                                    style="width: 100%; padding: 8px; border-radius: 6px; border: 1px solid var(--border); font-size: 0.75rem; outline: none; background: var(--bg-panel, rgba(0,0,0,0.02)); color: var(--text-main);">
                                        </div>
                                    @else
                                        <div style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border); font-size: 0.9rem; background: var(--bg-panel, rgba(0,0,0,0.05)); color: var(--text-muted); cursor: not-allowed; text-align: center;">
                                            Locked
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <div style="margin: 30px 0 10px 0; display: flex; justify-content: flex-end; gap: 15px;">
                        <button type="button" onclick="document.getElementById('schedule-modal-{{ Str::slug($title) }}').style.display='none'" style="padding: 12px 25px; border: 1px solid var(--border); background: var(--bg-main, #ffffff); border-radius: 10px; font-weight: 700; cursor: pointer; color: var(--text-main); font-size: 0.95rem;">Cancel</button>
                        <button type="submit" style="padding: 12px 30px; border: none; background: #10b981; color: white; border-radius: 10px; font-weight: 800; cursor: pointer; box-shadow: 0 4px 12px rgba(0,0,0,0.15); font-size: 0.95rem;">Save Schedule</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
@empty
@endforelse

<script>
    function updateAllDropdowns(value, packageSlug) {
        if (!value) return; // Do nothing if they selected the empty placeholder
        const dropdowns = document.querySelectorAll('.driver-dropdown-' + packageSlug);
        dropdowns.forEach(select => {
            select.value = value;
        });
    }

    function updateAllDates(value, packageSlug, type) {
        if (!value) return;
        const inputs = document.querySelectorAll('.date-input-' + packageSlug + '-' + type);
        inputs.forEach(input => {
            input.value = value;
        });
    }

    function onTablePaymentSelectChange(select, bookingId, totalAmount) {
        var container = document.getElementById('partial-input-container-' + bookingId);
        var input = document.getElementById('partial-amount-' + bookingId);
        if (select.value === 'partially_paid') {
            container.style.display = 'inline-flex';
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
