@extends('driver::layouts.master')

@section('content')
<div x-data="dashboardOverview()">
    <div style="margin-bottom: 20px;">
        <h2 style="font-family: 'Outfit', sans-serif; color: var(--text-main); font-size: 1.5rem;">Hello, {{ auth()->user()->name ?? 'Driver' }}</h2>
        <p style="color: var(--text-muted); font-size: 0.85rem;">Here's an overview of your day.</p>
    </div>

    <!-- Quick Stats -->
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 25px;">
        <div class="card" style="margin-bottom: 0; padding: 15px; text-align: center;">
            <div style="color: var(--primary); font-size: 1.5rem; margin-bottom: 5px;"><i class="fas fa-route"></i></div>
            <div style="font-size: 1.2rem; font-weight: 800; color: var(--text-main);">{{ $activeTripsCount }}</div>
            <div style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase;">Active Trips</div>
        </div>
        <div class="card" style="margin-bottom: 0; padding: 15px; text-align: center;">
            <div style="color: #3b82f6; font-size: 1.5rem; margin-bottom: 5px;"><i class="fas fa-flag-checkered"></i></div>
            <div style="font-size: 1.2rem; font-weight: 800; color: var(--text-main);">{{ $completedTripsCount }}</div>
            <div style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase;">Completed</div>
        </div>
        <div class="card" style="margin-bottom: 0; padding: 15px; text-align: center;">
            <div style="color: var(--success); font-size: 1.5rem; margin-bottom: 5px;"><i class="fas fa-wallet"></i></div>
            <div style="font-size: 1.2rem; font-weight: 800; color: var(--text-main);">₵{{ number_format($earnedToday, 2) }}</div>
            <div style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase;">Earned Today</div>
        </div>
    </div>

    <!-- Current/Next Trip -->
    <h3 style="font-family: 'Outfit', sans-serif; font-size: 1.1rem; margin-bottom: 10px; color: var(--text-main);">Current Assignment</h3>
    @if($currentTrip)
    <div class="card" style="border-left: 4px solid {{ $currentTrip->trip_status === 'in_progress' ? 'var(--success)' : 'var(--accent)' }};">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px;">
            <div>
                @php
                    $isTourism = $currentTrip->isTourismBooking();
                    $statusColor = 'var(--accent)';
                    $statusLabel = strtoupper($currentTrip->status);
                    if ($currentTrip->trip_status === 'in_progress') {
                        $statusColor = 'var(--success)';
                        $statusLabel = $isTourism ? 'OUTBOUND IN PROGRESS' : 'IN PROGRESS';
                    } elseif ($isTourism && $currentTrip->trip_leg === 'return' && $currentTrip->return_trip_status === 'in_progress') {
                        $statusColor = '#3b82f6';
                        $statusLabel = 'RETURN IN PROGRESS';
                    } elseif ($isTourism && $currentTrip->trip_leg === 'return' && $currentTrip->return_trip_status === 'idle') {
                        $statusColor = '#d97706';
                        $statusLabel = 'OUTBOUND ENDED — RETURN PENDING';
                    }
                @endphp
                <span class="badge" style="background: {{ $statusColor }}; color: white; padding: 4px 8px; border-radius: 6px; font-size: 0.65rem; font-weight: 800;">{{ $statusLabel }}</span>

                @if($isTourism)
                <div style="display: flex; align-items: center; gap: 6px; margin-top: 8px; font-size: 0.65rem; font-weight: 800;">
                    <span style="padding: 3px 8px; border-radius: 6px; {{ $currentTrip->trip_status === 'completed' ? 'background: #dcfce7; color: #166534;' : ($currentTrip->trip_status === 'in_progress' ? 'background: var(--primary); color: white;' : 'background: var(--bg-main); color: var(--text-muted);') }}">
                        <i class="fas fa-{{ $currentTrip->trip_status === 'completed' ? 'check' : 'plane-departure' }}"></i> Outbound
                    </span>
                    <i class="fas fa-arrow-right" style="color: var(--border); font-size: 0.5rem;"></i>
                    <span style="padding: 3px 8px; border-radius: 6px; {{ $currentTrip->return_trip_status === 'completed' ? 'background: #dcfce7; color: #166534;' : ($currentTrip->return_trip_status === 'in_progress' ? 'background: #3b82f6; color: white;' : ($currentTrip->trip_leg === 'return' ? 'background: #fef3c7; color: #92400e;' : 'background: var(--bg-main); color: var(--text-muted);')) }}">
                        <i class="fas fa-{{ $currentTrip->return_trip_status === 'completed' ? 'check' : 'plane-arrival' }}"></i> Return
                    </span>
                </div>
                @endif
                <div style="font-weight: 700; font-size: 1.1rem; margin-top: 5px;">
                    @php
                        $item = $currentTrip->items->first();
                        $serviceName = 'Service';
                        $destination = '';
                        
                        if ($item) {
                            if ($item->bookable_type === 'Modules\Fleet\Models\Vehicle') {
                                $serviceName = 'Vehicle Rental';
                                $destination = $item->bookable->make . ' ' . $item->bookable->model;
                            } elseif ($item->bookable_type === 'Modules\Fleet\Models\AirportTransfer') {
                                $serviceName = 'Airport Transfer';
                                $destination = $item->options['destination'] ?? ($item->options['custom_location'] ?? ($item->bookable->location ?? $item->bookable->airport_name));
                            } else {
                                $serviceName = 'Tourism Package';
                                $destination = $item->bookable->title;
                            }
                        }
                    @endphp
                    {{ $serviceName }}
                </div>
            </div>
            <div style="text-align: right; min-width: 100px;">
                <div style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 6px;">Ref: #{{ $currentTrip->booking_reference }}</div>
                @php
                    $displayDate = null;
                    $dateLabel = '';
                    if ($currentTrip->trip_status === 'completed' && in_array($currentTrip->return_trip_status, ['idle', 'pending', 'in_progress'])) {
                        $displayDate = $currentTrip->return_scheduled_at;
                        $dateLabel = 'Return';
                    } else {
                        $displayDate = $currentTrip->scheduled_at;
                        $dateLabel = 'Schedule';
                    }
                @endphp
                @if($displayDate)
                    <div style="font-size: 0.65rem; font-weight: 800; color: var(--primary); text-transform: uppercase; margin-bottom: 2px;">{{ $dateLabel }}</div>
                    <div style="font-weight: 700; color: var(--text-main); font-size: 0.85rem;">{{ $displayDate->format('M d, Y') }}</div>
                    <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $displayDate->format('h:i A') }}</div>
                @else
                    <div style="font-size: 0.65rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; margin-bottom: 2px;">Created</div>
                    <div style="font-weight: 700; color: var(--text-main); font-size: 0.85rem;">{{ $currentTrip->created_at->isToday() ? 'Today' : $currentTrip->created_at->format('M d, Y') }}</div>
                    <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $currentTrip->created_at->format('h:i A') }}</div>
                @endif
            </div>
        </div>

        <div style="position: relative; padding-left: 20px; margin-bottom: 15px;">
            <div style="position: absolute; left: 0; top: 0; bottom: 0; width: 2px; background: var(--border);"></div>
            <div style="position: absolute; left: -4px; top: 0; width: 10px; height: 10px; border-radius: 50%; background: var(--success);"></div>
            <div style="position: absolute; left: -4px; bottom: 0; width: 10px; height: 10px; border-radius: 50%; background: var(--primary);"></div>
            
            <div style="margin-bottom: 15px;" x-data="{ openCustomers: false }">
                <div style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase;">Customer</div>
                @if(isset($currentTrip->grouped_customers) && count($currentTrip->grouped_customers) > 1)
                    <div @click="openCustomers = !openCustomers" style="font-size: 0.9rem; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 5px; color: var(--primary);">
                        {{ $currentTrip->customer_name }} <i class="fas" :class="openCustomers ? 'fa-chevron-up' : 'fa-chevron-down'" style="font-size: 0.7rem;"></i>
                    </div>
                    <div x-show="openCustomers" x-transition x-cloak style="margin-top: 5px; background: var(--bg-main); border: 1px solid var(--border); border-radius: 8px; padding: 10px;">
                        @foreach($currentTrip->grouped_customers as $cust)
                            <div style="padding: 5px 0; border-bottom: 1px solid var(--border); {{ $loop->last ? 'border-bottom: none; padding-bottom: 0;' : '' }} {{ $loop->first ? 'padding-top: 0;' : '' }}">
                                <div style="font-weight: 700; font-size: 0.85rem; color: var(--text-main);">{{ $cust['name'] }}</div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);"><i class="fas fa-phone-alt" style="font-size: 0.65rem;"></i> {{ $cust['phone'] }} &nbsp;&middot;&nbsp; Ref: {{ $cust['ref'] }}</div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div style="font-size: 0.9rem; font-weight: 600;">{{ $currentTrip->customer_name }}</div>
                    <div style="font-size: 0.8rem; color: var(--text-muted);"><i class="fas fa-phone-alt" style="font-size: 0.7rem; margin-right: 3px;"></i> {{ $currentTrip->customer_phone }}</div>
                @endif
            </div>
            <div>
                <div style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase;">Destination / Task</div>
                <div style="font-size: 0.9rem; font-weight: 600;">{{ $destination }}</div>
                @if($currentTrip->notes)
                <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 4px;"><i class="fas fa-comment-alt" style="color: var(--accent); margin-right: 3px;"></i> {{ $currentTrip->notes }}</div>
                @endif
            </div>
        </div>

        {{-- Offline End Code (Outbound) --}}
        @if($currentTrip->trip_status === 'in_progress' && $currentTrip->trip_end_code)
        <div style="margin-bottom: 15px; padding: 12px; background: rgba(245,158,11,0.1); border: 1px dashed rgba(245,158,11,0.4); border-radius: 10px; text-align: center;">
            <div style="font-size: 0.7rem; color: #d97706; font-weight: 800; text-transform: uppercase; margin-bottom: 4px;">Outbound Offline End Code</div>
            <code style="font-size: 1.3rem; font-weight: 900; color: #b45309; letter-spacing: 3px;">{{ $currentTrip->trip_end_code }}</code>
            <div style="font-size: 0.7rem; color: var(--text-muted); margin-top: 4px; line-height: 1.2;">Provide this to the customer if you lose internet connection.</div>
        </div>
        @endif

        {{-- Offline End Code (Return) --}}
        @if($isTourism && $currentTrip->return_trip_status === 'in_progress' && $currentTrip->return_end_code)
        <div style="margin-bottom: 15px; padding: 12px; background: rgba(59,130,246,0.1); border: 1px dashed rgba(59,130,246,0.4); border-radius: 10px; text-align: center;">
            <div style="font-size: 0.7rem; color: #2563eb; font-weight: 800; text-transform: uppercase; margin-bottom: 4px;">Return Offline End Code</div>
            <code style="font-size: 1.3rem; font-weight: 900; color: #1d4ed8; letter-spacing: 3px;">{{ $currentTrip->return_end_code }}</code>
            <div style="font-size: 0.7rem; color: var(--text-muted); margin-top: 4px; line-height: 1.2;">Provide this to the customer if you lose internet connection.</div>
        </div>
        @endif

        <div style="display: flex; flex-direction: column; gap: 10px;">
            <div style="display: flex; gap: 10px;">
                {{-- OUTBOUND: Start Trip button --}}
                @if($currentTrip->trip_status === 'idle' && $currentTrip->trip_leg === 'outbound')
                    @php
                        $isAccepted = $currentTrip->driver_schedule_status === 'accepted';
                        $isCustAccepted = $currentTrip->customer_schedule_status === 'accepted';
                        $isTimeCorrect = !$currentTrip->scheduled_at || now()->addMinutes(30)->gte($currentTrip->scheduled_at);
                        $canStart = $isAccepted && $isCustAccepted && $isTimeCorrect;
                    @endphp
                    <form action="{{ route('driver.trips.start', $currentTrip) }}" method="POST" style="flex: 2; margin: 0;" onsubmit="return confirm('Are you sure you want to start the outbound trip?')">
                        @csrf
                        <button type="submit" class="btn btn-primary" @disabled(!$canStart) style="width: 100%; padding: 12px; font-weight: 800; font-size: 0.9rem; border-radius: 12px; {{ !$canStart ? 'background: #9ca3af; cursor: not-allowed; opacity: 0.7;' : '' }}">
                            @if(!$isAccepted)
                                <i class="fas fa-handshake-alt" style="margin-right: 5px;"></i> Accept Schedule First
                            @elseif(!$isCustAccepted)
                                <i class="fas fa-user-clock" style="margin-right: 5px;"></i> Awaiting Customer
                            @elseif($isTimeCorrect)
                                <i class="fas fa-play" style="margin-right: 5px;"></i> Start Outbound Trip
                            @else
                                <i class="fas fa-lock" style="margin-right: 5px;"></i> Starts at {{ $currentTrip->scheduled_at->format('g:i A') }}
                            @endif
                        </button>
                        @if(!$isAccepted)
                            <div style="font-size: 0.65rem; color: #ef4444; text-align: center; margin-top: 5px; font-weight: 700;">Go to "Schedule" menu to accept this trip.</div>
                        @elseif(!$isCustAccepted)
                            <div style="font-size: 0.65rem; color: #d97706; text-align: center; margin-top: 5px; font-weight: 700;">{{ isset($currentTrip->grouped_customers) && count($currentTrip->grouped_customers) > 1 ? 'Customers have' : 'Customer has' }} not confirmed yet.</div>
                        @elseif(!$isTimeCorrect)
                            <div style="font-size: 0.65rem; color: var(--text-muted); text-align: center; margin-top: 5px;">You can start 30 mins before schedule.</div>
                        @endif
                    </form>

                {{-- RETURN: Start Return Trip button --}}
                @elseif($isTourism && $currentTrip->trip_leg === 'return' && $currentTrip->return_trip_status === 'idle')
                    @php
                        $isRetScheduled = !empty($currentTrip->return_scheduled_at);
                        $retDriverOk = $currentTrip->return_driver_schedule_status === 'accepted';
                        $retCustOk = $currentTrip->return_customer_schedule_status === 'accepted';
                        $retTimeOk = $isRetScheduled && now()->addMinutes(30)->gte($currentTrip->return_scheduled_at);
                        $canStartReturn = $isRetScheduled && $retDriverOk && $retCustOk && $retTimeOk;
                    @endphp
                    <form action="{{ route('driver.trips.return-start', $currentTrip) }}" method="POST" style="flex: 2; margin: 0;" onsubmit="return confirm('Are you sure you want to start the return trip?')">
                        @csrf
                        <button type="submit" class="btn" @disabled(!$canStartReturn) style="width: 100%; padding: 12px; font-weight: 800; font-size: 0.9rem; border-radius: 12px; border: none; color: white; {{ $canStartReturn ? 'background: #3b82f6;' : 'background: #9ca3af; cursor: not-allowed; opacity: 0.7;' }}">
                            @if(!$isRetScheduled)
                                <i class="fas fa-clock" style="margin-right: 5px;"></i> Waiting for Schedule
                            @elseif(!$retDriverOk)
                                <i class="fas fa-handshake-alt" style="margin-right: 5px;"></i> Accept Return Schedule
                            @elseif(!$retCustOk)
                                <i class="fas fa-user-clock" style="margin-right: 5px;"></i> Awaiting Customer
                            @elseif($retTimeOk)
                                <i class="fas fa-play" style="margin-right: 5px;"></i> Start Return Trip
                            @else
                                <i class="fas fa-lock" style="margin-right: 5px;"></i> Returns at {{ $currentTrip->return_scheduled_at->format('g:i A') }}
                            @endif
                        </button>
                        @if(!$isRetScheduled)
                            <div style="font-size: 0.65rem; color: #d97706; text-align: center; margin-top: 5px; font-weight: 700;">Admin has not scheduled the return trip yet.</div>
                        @elseif(!$retDriverOk)
                            <div style="font-size: 0.65rem; color: #ef4444; text-align: center; margin-top: 5px; font-weight: 700;">Go to "Schedule" to accept return trip.</div>
                        @elseif(!$retCustOk)
                            <div style="font-size: 0.65rem; color: #d97706; text-align: center; margin-top: 5px; font-weight: 700;">{{ isset($currentTrip->grouped_customers) && count($currentTrip->grouped_customers) > 1 ? 'Customers have' : 'Customer has' }} not confirmed return yet.</div>
                        @elseif(!$retTimeOk)
                            <div style="font-size: 0.65rem; color: var(--text-muted); text-align: center; margin-top: 5px;">You can start 30 mins before schedule.</div>
                        @endif
                    </form>

                {{-- IN PROGRESS: Navigate button --}}
                @elseif($currentTrip->trip_status === 'in_progress' || ($isTourism && $currentTrip->return_trip_status === 'in_progress'))
                    <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($destination) }}" target="_blank" class="btn btn-accent" style="flex: 2; padding: 12px; font-size: 0.9rem; font-weight: 800; border-radius: 12px; text-decoration: none; text-align: center; display: flex; align-items: center; justify-content: center; gap: 8px;">
                        <i class="fas fa-location-arrow"></i> Navigate
                    </a>
                @endif
                <a href="tel:{{ $currentTrip->customer_phone }}" class="btn btn-outline" style="flex: 1; padding: 12px; font-size: 0.9rem; font-weight: 700; border-radius: 12px; text-decoration: none; text-align: center; display: flex; align-items: center; justify-content: center; gap: 8px;">
                    <i class="fas fa-phone"></i> Call
                </a>
            </div>

            {{-- OUTBOUND in progress actions --}}
            @if($currentTrip->trip_status === 'in_progress' && $currentTrip->trip_leg === 'outbound')
                <div style="display: flex; gap: 10px;">
                    <button @click="$dispatch('open-modal', 'report-issue')" class="btn btn-outline" style="flex: 1; padding: 10px; font-size: 0.8rem; border-radius: 10px; color: #ef4444; border-color: #fecaca;">
                        <i class="fas fa-exclamation-triangle"></i> Report Issue
                    </button>
                    <form action="{{ route('driver.trips.end', $currentTrip) }}" method="POST" style="flex: 1; margin: 0;" onsubmit="return confirm('End outbound trip? The customer has arrived at destination.')">
                        @csrf
                        <button type="submit" class="btn btn-success" style="width: 100%; padding: 10px; font-size: 0.8rem; font-weight: 800; border-radius: 10px; background: #10b981; border: none; color: white;">
                            <i class="fas fa-stop"></i> End Outbound
                        </button>
                    </form>
                </div>
            @endif

            {{-- RETURN in progress actions --}}
            @if($isTourism && $currentTrip->return_trip_status === 'in_progress')
                <div style="display: flex; gap: 10px;">
                    <button @click="$dispatch('open-modal', 'report-issue')" class="btn btn-outline" style="flex: 1; padding: 10px; font-size: 0.8rem; border-radius: 10px; color: #ef4444; border-color: #fecaca;">
                        <i class="fas fa-exclamation-triangle"></i> Report Issue
                    </button>
                    <form action="{{ route('driver.trips.return-end', $currentTrip) }}" method="POST" style="flex: 1; margin: 0;" onsubmit="return confirm('End return trip? Customer has returned to origin.')">
                        @csrf
                        <button type="submit" class="btn" style="width: 100%; padding: 10px; font-size: 0.8rem; font-weight: 800; border-radius: 10px; background: #3b82f6; border: none; color: white;">
                            <i class="fas fa-flag-checkered"></i> End Return Trip
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>

    <!-- Report Issue Modal (Using simple Alpine implementation) -->
    <template x-if="true">
        <div x-show="modal === 'report-issue'" x-cloak style="position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; display: flex; align-items: center; justify-content: center; padding: 20px;">
            <div @click.away="modal = null" class="card" style="width: 100%; max-width: 400px; margin-bottom: 0;">
                <h3 style="font-family: 'Outfit', sans-serif; font-size: 1.2rem; margin-bottom: 15px;">Report Trip Issue</h3>
                <form action="{{ route('driver.trips.report', $currentTrip) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; font-size: 0.75rem; font-weight: 700; margin-bottom: 5px;">Issue Type</label>
                        <select name="type" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);">
                            <option value="traffic">Heavy Traffic</option>
                            <option value="mechanical">Mechanical Fault</option>
                            <option value="weather">Bad Weather</option>
                            <option value="customer">Customer Issue</option>
                            <option value="other">Other Delay</option>
                        </select>
                    </div>
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; font-size: 0.75rem; font-weight: 700; margin-bottom: 5px;">Description</label>
                        <textarea name="description" required rows="3" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main); font-size: 0.9rem;" placeholder="Explain the situation..."></textarea>
                    </div>
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; font-size: 0.75rem; font-weight: 700; margin-bottom: 5px;">Proof Image (Optional)</label>
                        <input type="file" name="image" accept="image/*" style="width: 100%; font-size: 0.8rem;">
                    </div>
                    <div style="display: flex; gap: 10px;">
                        <button type="button" @click="modal = null" class="btn btn-outline" style="flex: 1;">Cancel</button>
                        <button type="submit" class="btn btn-primary" style="flex: 1;">Submit Report</button>
                    </div>
                </form>
            </div>
        </div>
    </template>
    @else
    <div class="card" style="text-align: center; padding: 30px 15px; color: var(--text-muted);">
        <i class="fas fa-map-marked-alt" style="font-size: 2.5rem; margin-bottom: 10px; color: var(--border);"></i>
        <p>No active trips assigned right now.</p>
    </div>
    @endif

    <!-- Assigned Vehicle -->
    <h3 style="font-family: 'Outfit', sans-serif; font-size: 1.1rem; margin-bottom: 10px; color: var(--text-main);">Assigned Status</h3>
    <div class="card" style="display: flex; align-items: center; gap: 15px;">
        <div style="width: 60px; height: 60px; background: var(--bg-main); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: var(--primary);">
            <i class="fas fa-id-badge"></i>
        </div>
        <div style="flex: 1;">
            <div style="font-weight: 700; font-size: 1rem;">License: {{ $chauffeur->license_number ?? 'N/A' }}</div>
            <div style="font-family: monospace; font-size: 0.8rem; background: var(--primary); color: white; padding: 2px 6px; border-radius: 4px; display: inline-block; margin-top: 4px;">{{ $chauffeur->years_of_experience ?? 0 }} Years Exp</div>
        </div>
        <div style="text-align: right; font-size: 0.8rem; color: var(--success); font-weight: 700;">
            <i class="fas fa-check-circle"></i> Ready
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('dashboardOverview', () => ({
            modal: null,
            init() {
                this.$on('open-modal', (name) => {
                    this.modal = name;
                });
            }
        }));
    });
</script>
@endpush
@endsection
