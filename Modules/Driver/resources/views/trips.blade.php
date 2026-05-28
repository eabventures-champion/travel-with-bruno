@extends('driver::layouts.master')

@section('content')
<div x-data="driverTrips()">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
        <h2 style="font-family: 'Outfit', sans-serif; color: var(--text-main); font-size: 1.5rem; margin: 0;">My Trips</h2>
        <div style="display: flex; gap: 8px;">
            <button @click="tab = 'active'" :style="tab === 'active' ? 'background: linear-gradient(135deg, var(--primary), #7c3aed); color: white; box-shadow: 0 4px 15px rgba(99,102,241,0.35); transform: translateY(-1px);' : 'background: var(--bg-card); color: var(--text-muted); border: 1px solid var(--border);'" style="border: none; padding: 10px 20px; border-radius: 50px; font-weight: 800; font-size: 0.8rem; cursor: pointer; transition: all 0.3s ease; display: flex; align-items: center; gap: 6px; letter-spacing: 0.3px;">
                <i class="fas fa-bolt"></i> Active
                <span :style="tab === 'active' ? 'background: rgba(255,255,255,0.25); color: white;' : 'background: var(--bg-main); color: var(--text-muted);'" style="padding: 2px 8px; border-radius: 50px; font-size: 0.7rem; font-weight: 800; transition: all 0.3s;">{{ $activeTrips->count() }}</span>
            </button>
            <button @click="tab = 'history'" :style="tab === 'history' ? 'background: linear-gradient(135deg, var(--primary), #7c3aed); color: white; box-shadow: 0 4px 15px rgba(99,102,241,0.35); transform: translateY(-1px);' : 'background: var(--bg-card); color: var(--text-muted); border: 1px solid var(--border);'" style="border: none; padding: 10px 20px; border-radius: 50px; font-weight: 800; font-size: 0.8rem; cursor: pointer; transition: all 0.3s ease; display: flex; align-items: center; gap: 6px; letter-spacing: 0.3px;">
                <i class="fas fa-clock-rotate-left"></i> History
                <span :style="tab === 'history' ? 'background: rgba(255,255,255,0.25); color: white;' : 'background: var(--bg-main); color: var(--text-muted);'" style="padding: 2px 8px; border-radius: 50px; font-size: 0.7rem; font-weight: 800; transition: all 0.3s;">{{ $historyTrips->count() }}</span>
            </button>
        </div>
    </div>

    {{-- ===== ACTIVE TRIPS TAB ===== --}}
    <div x-show="tab === 'active'" x-cloak
         x-transition:enter="tab-transition-enter"
         x-transition:enter-start="tab-transition-start"
         x-transition:enter-end="tab-transition-end"
         x-transition:leave="tab-transition-leave"
         x-transition:leave-start="tab-transition-end"
         x-transition:leave-end="tab-transition-start">
        @forelse($activeTrips as $trip)
        @php
            $firstItem = $trip->items->first();
            $bookableType = $firstItem?->bookable_type ?? '';
            if (str_contains($bookableType, 'Vehicle')) {
                $serviceType = 'Car Hiring';
                $serviceIcon = 'fa-car';
                $serviceColor = '#f59e0b';
            } elseif (str_contains($bookableType, 'AirportTransfer')) {
                $serviceType = 'Airport Transfer';
                $serviceIcon = 'fa-plane';
                $serviceColor = '#10b981';
            } else {
                $serviceType = 'Tourism Package';
                $serviceIcon = 'fa-umbrella-beach';
                $serviceColor = '#0ea5e9';
            }
            $borderColor = ($trip->trip_status === 'in_progress' || $trip->return_trip_status === 'in_progress') ? '#10b981' : 'var(--primary)';
            
            $displayDate = null;
            $dateLabel = '';
            if ($trip->trip_status === 'completed' && in_array($trip->return_trip_status, ['idle', 'pending', 'in_progress'])) {
                $displayDate = $trip->return_scheduled_at;
                $dateLabel = 'Return';
            } else {
                $displayDate = $trip->scheduled_at;
                $dateLabel = 'Schedule';
            }
        @endphp
        <div class="card" style="border-left: 4px solid {{ $borderColor }}; position: relative; overflow: hidden;">
            {{-- Header --}}
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                <div>
                    <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                        <span style="display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; border-radius: 6px; font-size: 0.65rem; font-weight: 800; text-transform: uppercase; background: {{ $serviceColor }}; color: white;">
                            <i class="fas {{ $serviceIcon }}"></i> {{ $serviceType }}
                        </span>
                        @if($trip->trip_status === 'in_progress' || $trip->return_trip_status === 'in_progress')
                        <span style="display: inline-flex; align-items: center; gap: 6px; background: rgba(16,185,129,0.1); padding: 4px 10px; border-radius: 20px; border: 1px solid rgba(16,185,129,0.3);">
                            <span style="width: 8px; height: 8px; border-radius: 50%; background: #10b981; display: inline-block; animation: pulse-dot 1.5s infinite;"></span>
                            <span style="font-size: 0.65rem; font-weight: 800; color: #10b981; text-transform: uppercase; letter-spacing: 0.5px;">Live</span>
                        </span>
                        @endif
                    </div>
                    <div style="font-weight: 700; font-size: 1.05rem; margin-top: 8px; color: var(--text-main);">
                        {{ $firstItem?->bookable?->title ?? $firstItem?->bookable?->make ?? $firstItem?->bookable?->airport_name ?? 'Assigned Trip' }}
                    </div>
                    <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 2px;">
                        Ref: <code style="font-weight: 700;">{{ $trip->booking_reference }}</code>
                    </div>
                </div>
                <div style="text-align: right; min-width: 100px;">
                    @if($displayDate)
                        <div style="font-size: 0.65rem; font-weight: 800; color: var(--primary); text-transform: uppercase; margin-bottom: 2px;">{{ $dateLabel }}</div>
                        <div style="font-weight: 700; color: var(--text-main); font-size: 0.85rem;">{{ $displayDate->format('M d, Y') }}</div>
                        <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $displayDate->format('h:i A') }}</div>
                    @else
                        <div style="font-size: 0.65rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; margin-bottom: 2px;">Created</div>
                        <div style="font-weight: 700; color: var(--text-main); font-size: 0.85rem;">{{ $trip->created_at->isToday() ? 'Today' : $trip->created_at->format('M d') }}</div>
                        <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $trip->created_at->format('h:i A') }}</div>
                    @endif
                </div>
            </div>

            {{-- Customer Info --}}
            <div style="background: var(--bg-main); padding: 12px; border-radius: 10px; margin-bottom: 12px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 36px; height: 36px; background: linear-gradient(135deg, var(--primary), var(--secondary)); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.85rem;">
                        {{ substr($trip->customer_name ?? 'G', 0, 1) }}
                    </div>
                    <div style="flex: 1;" x-data="{ openCustomers: false }">
                        @if(isset($trip->grouped_customers) && count($trip->grouped_customers) > 1)
                            <div @click="openCustomers = !openCustomers" style="font-size: 0.9rem; font-weight: 700; color: var(--primary); cursor: pointer; display: inline-flex; align-items: center; gap: 5px;">
                                {{ $trip->customer_name ?? 'Guest' }} <i class="fas" :class="openCustomers ? 'fa-chevron-up' : 'fa-chevron-down'" style="font-size: 0.7rem;"></i>
                            </div>
                            <div x-show="openCustomers" x-transition x-cloak style="margin-top: 5px; background: var(--bg-card); border: 1px solid var(--border); border-radius: 8px; padding: 10px;">
                                @foreach($trip->grouped_customers as $cust)
                                    <div style="padding: 5px 0; border-bottom: 1px solid var(--border); {{ $loop->last ? 'border-bottom: none; padding-bottom: 0;' : '' }} {{ $loop->first ? 'padding-top: 0;' : '' }}">
                                        <div style="font-weight: 700; font-size: 0.85rem; color: var(--text-main);">{{ $cust['name'] }}</div>
                                        <div style="font-size: 0.75rem; color: var(--text-muted);"><i class="fas fa-phone-alt" style="font-size: 0.65rem;"></i> {{ $cust['phone'] }} &nbsp;&middot;&nbsp; Ref: {{ $cust['ref'] }}</div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div style="font-size: 0.9rem; font-weight: 700; color: var(--text-main);">{{ $trip->customer_name ?? 'Guest' }}</div>
                            <div style="font-size: 0.75rem; color: var(--text-muted);">
                                <i class="fas fa-phone-alt" style="color: var(--primary); margin-right: 3px; font-size: 0.65rem;"></i>
                                {{ $trip->customer_phone ?? 'N/A' }}
                            </div>
                        @endif
                    </div>
                    @if($trip->customer_phone)
                    <a href="tel:{{ $trip->customer_phone }}" style="width: 34px; height: 34px; background: rgba(16,185,129,0.1); color: #10b981; border-radius: 50%; display: flex; align-items: center; justify-content: center; text-decoration: none; border: 1px solid rgba(16,185,129,0.2);">
                        <i class="fas fa-phone"></i>
                    </a>
                    @endif
                </div>
            </div>

            {{-- Amount --}}
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; padding: 8px 12px; background: rgba(99,102,241,0.05); border-radius: 8px; border: 1px solid rgba(99,102,241,0.1);">
                <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600;">Trip Value</span>
                <span style="font-weight: 800; color: var(--primary); font-size: 1rem;">₵{{ number_format($trip->total_amount, 2) }}</span>
            </div>

            {{-- Action Button --}}
            @if($trip->trip_status === 'idle')
                @php
                    $canStart = !$trip->scheduled_at || now()->addMinutes(30)->gte($trip->scheduled_at);
                @endphp
                <form action="{{ route('driver.trips.start', $trip) }}" method="POST" style="margin: 0;" onsubmit="return confirm('Are you sure you want to start this trip?')">
                    @csrf
                    <button type="submit" @disabled(!$canStart) style="width: 100%; padding: 12px; font-size: 0.85rem; border-radius: 10px; border: none; background: {{ $canStart ? 'linear-gradient(135deg, var(--primary), #7c3aed)' : '#9ca3af' }}; color: white; font-weight: 800; cursor: {{ $canStart ? 'pointer' : 'not-allowed' }}; display: flex; align-items: center; justify-content: center; gap: 8px; box-shadow: {{ $canStart ? '0 4px 15px rgba(99,102,241,0.3)' : 'none' }}; transition: transform 0.2s; opacity: {{ $canStart ? '1' : '0.7' }};">
                        @if($canStart)
                            <i class="fas fa-play-circle"></i> Start Trip
                        @else
                            <i class="fas fa-lock"></i> Locked until {{ $trip->scheduled_at->format('g:i A') }}
                        @endif
                    </button>
                    @if(!$canStart)
                        <div style="font-size: 0.65rem; color: var(--text-muted); text-align: center; margin-top: 5px;">Activation window opens 30 mins before schedule.</div>
                    @endif
                </form>
            @elseif($trip->trip_status === 'in_progress')
                @if($trip->trip_end_code)
                <div style="margin-bottom: 12px; padding: 12px; background: rgba(245,158,11,0.1); border: 1px dashed rgba(245,158,11,0.4); border-radius: 10px; text-align: center;">
                    <div style="font-size: 0.7rem; color: #d97706; font-weight: 800; text-transform: uppercase; margin-bottom: 4px;">Offline End Code</div>
                    <code style="font-size: 1.3rem; font-weight: 900; color: #b45309; letter-spacing: 3px;">{{ $trip->trip_end_code }}</code>
                    <div style="font-size: 0.7rem; color: var(--text-muted); margin-top: 4px; line-height: 1.2;">Provide this to the customer if you lose internet connection so they can end the trip.</div>
                </div>
                @endif
                <a href="{{ route('driver.dashboard') }}" style="display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%; padding: 12px; font-size: 0.85rem; border-radius: 10px; text-decoration: none; background: linear-gradient(135deg, #059669, #10b981); color: white; font-weight: 800; box-shadow: 0 4px 15px rgba(16,185,129,0.3); transition: transform 0.2s;">
                    <i class="fas fa-location-arrow"></i> Manage Active Trip
                </a>
            @elseif($trip->trip_status === 'completed' && in_array($trip->return_trip_status, ['idle', 'pending']))
                <div style="margin-bottom: 12px; padding: 12px; background: rgba(245, 158, 11, 0.1); border: 1px dashed rgba(245, 158, 11, 0.4); border-radius: 10px; text-align: center;">
                    <div style="font-size: 0.75rem; color: #b45309; font-weight: 800; text-transform: uppercase; margin-bottom: 4px;">Outbound Completed</div>
                    <div style="font-size: 0.8rem; color: var(--text-muted); line-height: 1.3;">{{ $trip->return_trip_status === 'idle' ? 'Waiting for the return trip to be scheduled.' : 'Return trip scheduled. Check dashboard to confirm.' }}</div>
                </div>
                <a href="{{ route('driver.dashboard') }}" style="display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%; padding: 12px; font-size: 0.85rem; border-radius: 10px; text-decoration: none; background: var(--bg-main); color: var(--text-main); font-weight: 800; border: 1px solid var(--border); transition: transform 0.2s;">
                    <i class="fas fa-eye"></i> View on Dashboard
                </a>
            @elseif($trip->return_trip_status === 'in_progress')
                @if($trip->return_end_code)
                <div style="margin-bottom: 12px; padding: 12px; background: rgba(245,158,11,0.1); border: 1px dashed rgba(245,158,11,0.4); border-radius: 10px; text-align: center;">
                    <div style="font-size: 0.7rem; color: #d97706; font-weight: 800; text-transform: uppercase; margin-bottom: 4px;">Return Offline End Code</div>
                    <code style="font-size: 1.3rem; font-weight: 900; color: #b45309; letter-spacing: 3px;">{{ $trip->return_end_code }}</code>
                    <div style="font-size: 0.7rem; color: var(--text-muted); margin-top: 4px; line-height: 1.2;">Provide this to the customer if you lose internet connection so they can end the return trip.</div>
                </div>
                @endif
                <a href="{{ route('driver.dashboard') }}" style="display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%; padding: 12px; font-size: 0.85rem; border-radius: 10px; text-decoration: none; background: linear-gradient(135deg, #059669, #10b981); color: white; font-weight: 800; box-shadow: 0 4px 15px rgba(16,185,129,0.3); transition: transform 0.2s;">
                    <i class="fas fa-location-arrow"></i> Manage Return Trip
                </a>
            @endif
        </div>
        @empty
        <div class="card" style="text-align: center; padding: 40px 20px;">
            <div style="font-size: 2.5rem; margin-bottom: 10px; opacity: 0.3;"><i class="fas fa-route"></i></div>
            <div style="font-weight: 700; color: var(--text-main); margin-bottom: 5px;">No Active Trips</div>
            <p style="color: var(--text-muted); font-size: 0.85rem; margin: 0;">When a trip is assigned to you, it will appear here.</p>
        </div>
        @endforelse
    </div>

    {{-- ===== HISTORY TAB ===== --}}
    <div x-show="tab === 'history'" x-cloak
         x-transition:enter="tab-transition-enter"
         x-transition:enter-start="tab-transition-start"
         x-transition:enter-end="tab-transition-end"
         x-transition:leave="tab-transition-leave"
         x-transition:leave-start="tab-transition-end"
         x-transition:leave-end="tab-transition-start">
        @forelse($historyTrips as $trip)
        @php
            $firstItem = $trip->items->first();
            $bookableType = $firstItem?->bookable_type ?? '';
            if (str_contains($bookableType, 'Vehicle')) {
                $serviceType = 'Car Hiring';
                $serviceIcon = 'fa-car';
                $serviceColor = '#f59e0b';
            } elseif (str_contains($bookableType, 'AirportTransfer')) {
                $serviceType = 'Airport Transfer';
                $serviceIcon = 'fa-plane';
                $serviceColor = '#10b981';
            } else {
                $serviceType = 'Tourism Package';
                $serviceIcon = 'fa-umbrella-beach';
                $serviceColor = '#0ea5e9';
            }
            $isCancelled = $trip->status === 'cancelled';
        @endphp
        <div class="card" style="border-left: 4px solid {{ $isCancelled ? '#ef4444' : '#3b82f6' }};" x-data="{ expanded: false }">
            {{-- Header Row --}}
            <div @click="expanded = !expanded" style="cursor: pointer;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">
                    <div>
                        <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                            <span style="display: inline-flex; align-items: center; gap: 4px; padding: 3px 8px; border-radius: 6px; font-size: 0.6rem; font-weight: 800; text-transform: uppercase; background: {{ $isCancelled ? 'rgba(239,68,68,0.1)' : 'rgba(59,130,246,0.1)' }}; color: {{ $isCancelled ? '#ef4444' : '#3b82f6' }}; border: 1px solid {{ $isCancelled ? 'rgba(239,68,68,0.2)' : 'rgba(59,130,246,0.2)' }};">
                                <i class="fas {{ $isCancelled ? 'fa-times-circle' : 'fa-flag-checkered' }}"></i> {{ $isCancelled ? 'Cancelled' : 'Completed' }}
                            </span>
                            <span style="display: inline-flex; align-items: center; gap: 4px; padding: 3px 8px; border-radius: 6px; font-size: 0.6rem; font-weight: 800; text-transform: uppercase; background: {{ $serviceColor }}20; color: {{ $serviceColor }}; border: 1px solid {{ $serviceColor }}30;">
                                <i class="fas {{ $serviceIcon }}"></i> {{ $serviceType }}
                            </span>
                        </div>
                        <div style="font-weight: 700; font-size: 1rem; margin-top: 8px; color: var(--text-main);">
                            {{ $firstItem?->bookable?->title ?? $firstItem?->bookable?->make ?? $firstItem?->bookable?->airport_name ?? 'Trip' }}
                        </div>
                    </div>
                    <div style="text-align: right; min-width: 90px;">
                        <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $trip->trip_ended_at ? $trip->trip_ended_at->format('M d, Y') : $trip->updated_at->format('M d, Y') }}</div>
                        <div style="font-weight: 800; color: {{ $isCancelled ? '#ef4444' : '#10b981' }}; font-size: 1rem; margin-top: 2px;">
                            {{ $isCancelled ? '—' : '+₵' . number_format($trip->total_amount, 2) }}
                        </div>
                        <div style="margin-top: 6px;">
                            <i class="fas" :class="expanded ? 'fa-chevron-up' : 'fa-chevron-down'" style="font-size: 0.7rem; color: var(--text-muted);"></i>
                        </div>
                    </div>
                </div>

                {{-- Customer line --}}
                <div style="display: flex; align-items: center; gap: 8px;">
                    <div style="width: 28px; height: 28px; background: linear-gradient(135deg, var(--primary), var(--secondary)); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.7rem;">
                        {{ substr($trip->customer_name ?? 'G', 0, 1) }}
                    </div>
                    <span style="font-size: 0.85rem; font-weight: 600; color: var(--text-main);">{{ $trip->customer_name ?? 'Guest' }}</span>
                </div>
            </div>

            {{-- Expandable Details --}}
            <div x-show="expanded" x-transition style="margin-top: 15px; padding-top: 15px; border-top: 1px dashed var(--border);">
                {{-- Trip Timeline --}}
                    <div style="background: var(--bg-main); padding: 12px; border-radius: 12px; margin-bottom: 12px; border: 1px solid var(--border);">
                        <div style="font-size: 0.75rem; font-weight: 800; color: var(--text-main); margin-bottom: 8px;">Outbound Trip</div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                            <div>
                                <div style="font-size: 0.6rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">Started</div>
                                <div style="font-weight: 700; font-size: 0.8rem; color: var(--text-main);">{{ $trip->trip_started_at ? $trip->trip_started_at->format('h:i A') : '—' }}</div>
                            </div>
                            <div>
                                <div style="font-size: 0.6rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">Ended</div>
                                <div style="font-weight: 700; font-size: 0.8rem; color: var(--text-main);">{{ $trip->trip_ended_at ? $trip->trip_ended_at->format('h:i A') : '—' }}</div>
                            </div>
                        </div>
                        <div style="margin-top: 8px; font-size: 0.7rem; color: var(--text-muted); font-weight: 700;">
                            <i class="fas fa-stopwatch" style="color: #10b981; margin-right: 4px;"></i> Duration: {{ $trip->trip_duration ?? 'N/A' }}
                        </div>
                    </div>

                    @if($trip->return_trip_status)
                    <div style="background: var(--bg-main); padding: 12px; border-radius: 12px; margin-bottom: 12px; border: 1px solid var(--border);">
                        <div style="font-size: 0.75rem; font-weight: 800; color: var(--text-main); margin-bottom: 8px;">Return Trip</div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                            <div>
                                <div style="font-size: 0.6rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">Started</div>
                                <div style="font-weight: 700; font-size: 0.8rem; color: var(--text-main);">{{ $trip->return_started_at ? $trip->return_started_at->format('h:i A') : '—' }}</div>
                            </div>
                            <div>
                                <div style="font-size: 0.6rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">Ended</div>
                                <div style="font-weight: 700; font-size: 0.8rem; color: var(--text-main);">{{ $trip->return_ended_at ? $trip->return_ended_at->format('h:i A') : '—' }}</div>
                            </div>
                        </div>
                        <div style="margin-top: 8px; font-size: 0.7rem; color: var(--text-muted); font-weight: 700;">
                            <i class="fas fa-stopwatch" style="color: #3b82f6; margin-right: 4px;"></i> Duration: {{ $trip->return_duration ?? 'N/A' }}
                        </div>
                    </div>
                    @endif

                    {{-- Total Duration & Reference --}}
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 12px; background: rgba(99,102,241,0.05); border-radius: 10px; margin-bottom: 12px; border: 1px solid rgba(99,102,241,0.1);">
                        <div>
                            <div style="font-size: 0.65rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">Total Duration</div>
                            <div style="font-weight: 800; color: var(--primary); font-size: 0.95rem;">
                                <i class="fas fa-stopwatch" style="margin-right: 4px;"></i> {{ $trip->total_duration ?? 'N/A' }}
                            </div>
                        </div>
                        <div style="text-align: right;">
                            <div style="font-size: 0.65rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">Reference</div>
                            <code style="font-weight: 800; font-size: 0.85rem; color: var(--text-main);">{{ $trip->booking_reference }}</code>
                        </div>
                    </div>

                {{-- Customer Contact --}}
                <div style="display: flex; align-items: center; gap: 10px; padding: 10px 12px; background: var(--bg-main); border-radius: 10px; margin-bottom: 12px;">
                    <div style="flex: 1;" x-data="{ openCustomers: false }">
                        <div style="font-size: 0.65rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700; margin-bottom: 3px;">Customer</div>
                        @if(isset($trip->grouped_customers) && count($trip->grouped_customers) > 1)
                            <div @click="openCustomers = !openCustomers" style="font-weight: 700; font-size: 0.9rem; color: var(--primary); cursor: pointer; display: inline-flex; align-items: center; gap: 5px;">
                                {{ $trip->customer_name ?? 'Guest' }} <i class="fas" :class="openCustomers ? 'fa-chevron-up' : 'fa-chevron-down'" style="font-size: 0.7rem;"></i>
                            </div>
                            <div x-show="openCustomers" x-transition x-cloak style="margin-top: 5px; background: var(--bg-card); border: 1px solid var(--border); border-radius: 8px; padding: 10px;">
                                @foreach($trip->grouped_customers as $cust)
                                    <div style="padding: 5px 0; border-bottom: 1px solid var(--border); {{ $loop->last ? 'border-bottom: none; padding-bottom: 0;' : '' }} {{ $loop->first ? 'padding-top: 0;' : '' }}">
                                        <div style="font-weight: 700; font-size: 0.85rem; color: var(--text-main);">{{ $cust['name'] }}</div>
                                        <div style="font-size: 0.75rem; color: var(--text-muted);"><i class="fas fa-phone-alt" style="font-size: 0.65rem;"></i> {{ $cust['phone'] }} &nbsp;&middot;&nbsp; Ref: {{ $cust['ref'] }}</div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div style="font-weight: 700; font-size: 0.9rem; color: var(--text-main);">{{ $trip->customer_name ?? 'Guest' }}</div>
                            <div style="font-size: 0.75rem; color: var(--text-muted);">
                                <i class="fas fa-phone-alt" style="margin-right: 3px; font-size: 0.65rem;"></i> {{ $trip->customer_phone ?? 'N/A' }}
                                @if($trip->user?->email)
                                    &nbsp;·&nbsp; <i class="fas fa-envelope" style="margin-right: 3px; font-size: 0.65rem;"></i> {{ $trip->user->email }}
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Customer Rating --}}
                @if($trip->rating)
                <div style="padding: 10px 12px; background: rgba(234,179,8,0.05); border-radius: 10px; border: 1px solid rgba(234,179,8,0.15);">
                    <div style="font-size: 0.65rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700; margin-bottom: 6px;">Customer Rating</div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div style="font-size: 1.1rem;">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="fas fa-star" style="color: {{ $i <= $trip->rating->rating ? '#eab308' : 'var(--border)' }}; margin-right: 1px;"></i>
                            @endfor
                        </div>
                        <span style="font-size: 0.8rem; font-weight: 800; color: var(--text-main);">{{ $trip->rating->rating }}/5</span>
                    </div>
                    @if($trip->rating->comment)
                    <div style="margin-top: 8px; font-size: 0.8rem; color: var(--text-muted); font-style: italic; line-height: 1.4;">
                        "{{ $trip->rating->comment }}"
                    </div>
                    @endif
                </div>
                @else
                <div style="padding: 10px 12px; background: var(--bg-main); border-radius: 10px; text-align: center;">
                    <span style="font-size: 0.75rem; color: var(--text-muted);"><i class="fas fa-star" style="margin-right: 4px; opacity: 0.3;"></i> No rating received yet</span>
                </div>
                @endif
            </div>
        </div>
        @empty
        <div class="card" style="text-align: center; padding: 40px 20px;">
            <div style="font-size: 2.5rem; margin-bottom: 10px; opacity: 0.3;"><i class="fas fa-history"></i></div>
            <div style="font-weight: 700; color: var(--text-main); margin-bottom: 5px;">No Trip History</div>
            <p style="color: var(--text-muted); font-size: 0.85rem; margin: 0;">Your completed trips will appear here.</p>
        </div>
        @endforelse
    </div>
</div>

<style>
    @keyframes pulse-dot {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.5; transform: scale(1.5); }
    }

    /* Tab transition classes */
    .tab-transition-enter {
        transition: opacity 0.3s ease, transform 0.3s ease;
    }
    .tab-transition-leave {
        transition: opacity 0.15s ease, transform 0.15s ease;
    }
    .tab-transition-start {
        opacity: 0;
        transform: translateY(8px);
    }
    .tab-transition-end {
        opacity: 1;
        transform: translateY(0);
    }

    [x-cloak] { display: none !important; }
</style>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('driverTrips', () => ({
            tab: 'active'
        }));
    });
</script>
@endpush
@endsection
