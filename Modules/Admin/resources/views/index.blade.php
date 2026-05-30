@extends('admin::layouts.master')

@section('title', 'Dashboard')

@section('content')
<div x-data="{ ongoingModal: false, completedModal: false, ongoingFixedModal: false, completedFixedModal: false }">
    <div class="dashboard-header" style="margin-bottom: 30px;">
        <h1 class="dashboard-welcome-title">Welcome back, {{ Auth::user()->name ?? 'Admin' }}!</h1>
        <p style="color: var(--text-muted);">Here's what's happening with Travel with Bruno today.</p>
    </div>

    @hasanyrole('Super Admin|Operations Admin')
    <div class="stats-grid" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; position: relative;">
        <div class="card" @click="ongoingModal = true" style="cursor: pointer; display: flex; align-items: center; gap: 20px; padding: 25px; border-radius: 20px; background: var(--bg-card); box-shadow: var(--shadow-sm); transition: transform 0.2s; border: 1px solid var(--border);" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
            <div style="width: 55px; height: 55px; border-radius: 15px; background: rgba(14, 165, 233, 0.1); color: #0ea5e9; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; flex-shrink: 0;">
                <i class="fas fa-running"></i>
            </div>
            <div style="display: flex; flex-direction: column;">
                <span style="font-size: 0.85rem; color: var(--text-muted); font-weight: 600;">Ongoing Tours</span>
                <span style="font-size: 1.8rem; font-weight: 800; color: var(--text-main); line-height: 1.2;">{{ $stats['ongoing_organized_tours'] }}</span>
                <span style="font-size: 0.75rem; color: #0ea5e9; font-weight: 700; display: flex; align-items: center; gap: 4px;">Organized</span>
            </div>
        </div>
        <div class="card" @click="completedModal = true" style="cursor: pointer; display: flex; align-items: center; gap: 20px; padding: 25px; border-radius: 20px; background: var(--bg-card); box-shadow: var(--shadow-sm); transition: transform 0.2s; border: 1px solid var(--border);" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
            <div style="width: 55px; height: 55px; border-radius: 15px; background: rgba(16, 185, 129, 0.1); color: #10b981; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; flex-shrink: 0;">
                <i class="fas fa-check-circle"></i>
            </div>
            <div style="display: flex; flex-direction: column;">
                <span style="font-size: 0.85rem; color: var(--text-muted); font-weight: 600;">Completed Tours</span>
                <span style="font-size: 1.8rem; font-weight: 800; color: var(--text-main); line-height: 1.2;">{{ $stats['completed_organized_tours'] }}</span>
                <span style="font-size: 0.75rem; color: #10b981; font-weight: 700; display: flex; align-items: center; gap: 4px;">Organized</span>
            </div>
        </div>
        <div class="card" @click="ongoingFixedModal = true" style="cursor: pointer; display: flex; align-items: center; gap: 20px; padding: 25px; border-radius: 20px; background: var(--bg-card); box-shadow: var(--shadow-sm); transition: transform 0.2s; border: 1px solid var(--border);" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
            <div style="width: 55px; height: 55px; border-radius: 15px; background: rgba(245, 158, 11, 0.1); color: #f59e0b; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; flex-shrink: 0;">
                <i class="fas fa-hiking"></i>
            </div>
            <div style="display: flex; flex-direction: column;">
                <span style="font-size: 0.85rem; color: var(--text-muted); font-weight: 600;">Ongoing Tours</span>
                <span style="font-size: 1.8rem; font-weight: 800; color: var(--text-main); line-height: 1.2;">{{ $stats['ongoing_fixed_tours'] }}</span>
                <span style="font-size: 0.75rem; color: #f59e0b; font-weight: 700; display: flex; align-items: center; gap: 4px;">Fixed Packages</span>
            </div>
        </div>
        <div class="card" @click="completedFixedModal = true" style="cursor: pointer; display: flex; align-items: center; gap: 20px; padding: 25px; border-radius: 20px; background: var(--bg-card); box-shadow: var(--shadow-sm); transition: transform 0.2s; border: 1px solid var(--border);" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
            <div style="width: 55px; height: 55px; border-radius: 15px; background: rgba(139, 92, 246, 0.1); color: #8b5cf6; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; flex-shrink: 0;">
                <i class="fas fa-history"></i>
            </div>
            <div style="display: flex; flex-direction: column;">
                <span style="font-size: 0.85rem; color: var(--text-muted); font-weight: 600;">Completed Tours</span>
                <span style="font-size: 1.8rem; font-weight: 800; color: var(--text-main); line-height: 1.2;">{{ $stats['completed_fixed_tours'] }}</span>
                <span style="font-size: 0.75rem; color: #8b5cf6; font-weight: 700; display: flex; align-items: center; gap: 4px;">Fixed Packages</span>
            </div>
        </div>
    </div>

    {{-- Unified Metrics Overview Bar --}}
    <div class="resource-overview" style="display: flex; gap: 40px; background: var(--bg-main); padding: 12px 25px; border-radius: 15px; border: 1px dashed var(--border); align-items: center; justify-content: center; margin: 15px 0 20px;">
        <div style="display: flex; flex-direction: column; align-items: center; gap: 2px;">
            <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Total Customers</span>
            <span style="font-weight: 800; color: var(--text-main); font-size: 1.1rem;">{{ $stats['total_customers'] }} <span style="font-size: 0.8rem; color: var(--accent); font-weight: 600;">Registered</span></span>
        </div>
        <div style="border-right: 1px solid var(--border); height: 30px;"></div>
        <div style="display: flex; flex-direction: column; align-items: center; gap: 2px;">
            <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Chauffeurs</span>
            <span style="font-weight: 800; color: var(--text-main); font-size: 1.1rem;">{{ $stats['total_chauffeurs'] }} <span style="font-size: 0.8rem; color: #8b5cf6; font-weight: 600;">Registered</span></span>
        </div>
        <div style="border-right: 1px solid var(--border); height: 30px;"></div>
        <div style="display: flex; flex-direction: column; align-items: center; gap: 2px;">
            <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Active Bookings</span>
            <span style="font-weight: 800; color: var(--text-main); font-size: 1.1rem;">{{ $stats['active_bookings'] }} <span style="font-size: 0.8rem; color: #f59e0b; font-weight: 600;">New Requests</span></span>
        </div>
        <div style="border-right: 1px solid var(--border); height: 30px;"></div>
        <div style="display: flex; flex-direction: column; align-items: center; gap: 2px;">
            <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Active Fleet</span>
            <span style="font-weight: 800; color: var(--text-main); font-size: 1.1rem;">{{ $stats['active_vehicles'] }} <span style="font-size: 0.8rem; color: #10b981; font-weight: 600;">Vehicles</span></span>
        </div>
        <div style="border-right: 1px solid var(--border); height: 30px;"></div>
        <div style="display: flex; flex-direction: column; align-items: center; gap: 2px;">
            <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Total Packages</span>
            <span style="font-weight: 800; color: var(--text-main); font-size: 1.1rem;">{{ $stats['total_packages'] }} <span style="font-size: 0.8rem; color: #0ea5e9; font-weight: 600;">Active</span></span>
        </div>
    </div>

    <!-- Ongoing Tours Modal -->
    <template x-teleport="body">
        <div x-show="ongoingModal" 
             class="modal-backdrop" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="ongoingModal = false"
             style="position: fixed; inset: 0; background: rgba(0,0,0,0.5); backdrop-filter: blur(4px); z-index: 9999; display: flex; align-items: center; justify-content: center; padding: 20px;"
             x-cloak>
            <div class="modal-content" @click.stop style="background: var(--bg-card); width: 100%; max-width: 600px; border-radius: 20px; overflow: hidden; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);">
                <div style="background: var(--primary); padding: 25px; color: white; display: flex; justify-content: space-between; align-items: center;">
                    <h3 style="margin: 0; font-family: 'Outfit', sans-serif;">Ongoing Organized Tours</h3>
                    <button @click="ongoingModal = false" style="background: none; border: none; color: white; cursor: pointer; font-size: 1.2rem;"><i class="fas fa-times"></i></button>
                </div>
                <div style="padding: 25px; max-height: 70vh; overflow-y: auto;">
                    @forelse($ongoingTours as $tour)
                    @php
                        $start = $tour->departure_date;
                        $end = $tour->return_date ?? $start->copy()->addDays(1);
                        $totalDays = (int) max($start->diffInDays($end), 1);
                        $elapsedDays = (int) $start->diffInDays(now());
                        $progress = min(round(($elapsedDays / $totalDays) * 100), 100);
                        $daysLeft = (int) max($end->diffInDays(now(), false), 0);
                        $currentDay = min($elapsedDays + 1, $totalDays + 1);
                    @endphp
                    <div style="padding: 20px; border-radius: 16px; background: var(--bg-main); margin-bottom: 15px; border: 1px solid var(--border);">
                        <div style="display: flex; gap: 15px; align-items: center; margin-bottom: 16px;">
                            <div style="width: 50px; height: 50px; border-radius: 10px; background: rgba(14, 165, 233, 0.1); color: #0ea5e9; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <i class="fas fa-running"></i>
                            </div>
                            <div style="flex: 1;">
                                <div style="font-weight: 700; color: var(--primary);">{{ $tour->title }}</div>
                                <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 4px;">
                                    <i class="fas fa-map-marker-alt"></i> {{ $tour->location }} | <i class="fas fa-calendar"></i> {{ $tour->formatted_date_range }}
                                </div>
                                <div style="font-size: 0.75rem; color: var(--accent); font-weight: 700; margin-top: 4px;">
                                    <i class="fas fa-users"></i> {{ $tour->guests_count ?? 0 }} / {{ $tour->max_guests }} Guests Currently
                                </div>
                            </div>
                            <div style="text-align: right;">
                                <span class="badge badge-primary" style="font-size: 0.65rem;">{{ $tour->category->name ?? 'Tour' }}</span>
                            </div>
                        </div>

                        {{-- Progress Bar --}}
                        <div style="margin-top: 4px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                <span style="font-size: 0.7rem; font-weight: 700; color: #0ea5e9;">Day {{ $currentDay }} of {{ $totalDays + 1 }}</span>
                                <span style="font-size: 0.7rem; font-weight: 700; color: {{ $daysLeft <= 1 ? '#ef4444' : '#64748b' }};">
                                    @if($daysLeft == 0)
                                        Final day!
                                    @else
                                        {{ $daysLeft }} {{ Str::plural('day', $daysLeft) }} left
                                    @endif
                                </span>
                            </div>
                            <div style="position: relative; height: 10px; background: #e2e8f0; border-radius: 10px; overflow: hidden;">
                                <div class="tour-progress-fill" style="height: 100%; width: {{ $progress }}%; background: linear-gradient(90deg, #0ea5e9, #6366f1, #8b5cf6); border-radius: 10px; transition: width 1.5s cubic-bezier(0.4, 0, 0.2, 1); position: relative;" data-progress="{{ $progress }}">
                                    <div style="position: absolute; right: -1px; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; background: white; border: 3px solid #6366f1; border-radius: 50%; box-shadow: 0 0 8px rgba(99, 102, 241, 0.5); animation: progressPulse 2s ease-in-out infinite;"></div>
                                </div>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-top: 6px;">
                                <span style="font-size: 0.65rem; color: #94a3b8;"><i class="fas fa-play-circle" style="margin-right: 2px;"></i> {{ $start->format('M d') }}</span>
                                <span style="font-size: 0.65rem; color: #94a3b8;"><i class="fas fa-flag-checkered" style="margin-right: 2px;"></i> {{ $end->format('M d') }}</span>
                            </div>
                        </div>

                        {{-- Chauffeurs assigned to this tour --}}
                        @php
                            $chauffeurs = $tour->items->map(fn($item) => $item->booking->chauffeur->user->name ?? null)->filter()->unique();
                        @endphp
                        @if($chauffeurs->count() > 0)
                        <div style="margin-top: 15px; padding-top: 12px; border-top: 1px dashed var(--border);">
                            <div style="font-size: 0.6rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700; margin-bottom: 5px;">Assigned Drivers</div>
                            <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                                @foreach($chauffeurs as $name)
                                <span style="background: rgba(30, 58, 138, 0.05); color: var(--primary); padding: 3px 10px; border-radius: 12px; font-size: 0.7rem; font-weight: 700; display: flex; align-items: center; gap: 5px; border: 1px solid var(--border);">
                                    <i class="fas fa-user-tie" style="font-size: 0.6rem;"></i> {{ $name }}
                                </span>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                    @empty
                    <div style="text-align: center; padding: 40px; color: var(--text-muted);">
                        <i class="fas fa-info-circle" style="font-size: 2rem; margin-bottom: 15px; opacity: 0.3;"></i>
                        <p>No tours are currently ongoing.</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </template>

    <!-- Ongoing Fixed Tours Modal -->
    <template x-teleport="body">
        <div x-show="ongoingFixedModal" 
             class="modal-backdrop" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="ongoingFixedModal = false"
             style="position: fixed; inset: 0; background: rgba(0,0,0,0.5); backdrop-filter: blur(4px); z-index: 9999; display: flex; align-items: center; justify-content: center; padding: 20px;"
             x-cloak>
            <div class="modal-content" @click.stop style="background: var(--bg-card); width: 100%; max-width: 650px; border-radius: 20px; overflow: hidden; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);">
                <div style="background: #f59e0b; padding: 25px; color: white; display: flex; justify-content: space-between; align-items: center;">
                    <h3 style="margin: 0; font-family: 'Outfit', sans-serif;">Ongoing Fixed Packages</h3>
                    <button @click="ongoingFixedModal = false" style="background: none; border: none; color: white; cursor: pointer; font-size: 1.2rem;"><i class="fas fa-times"></i></button>
                </div>
                <div style="padding: 25px; max-height: 70vh; overflow-y: auto;">
                    @php
                        $ongoingGroups = collect();
                        $ongoingSingle = collect();
                        
                        $groups = $ongoingFixedTours->filter(fn($b) => $b->chauffeur_id && $b->scheduled_at)
                            ->groupBy(function($b) {
                                return ($b->chauffeur_id ?? 0) . '_' . ($b->scheduled_at ? $b->scheduled_at->format('Y-m-d') : 'none');
                            });
                            
                        foreach ($groups as $key => $group) {
                            if ($group->count() > 1) {
                                $ongoingGroups->push($group);
                            } else {
                                $ongoingSingle->push($group->first());
                            }
                        }
                        
                        $noChauffeurOrSchedule = $ongoingFixedTours->filter(fn($b) => !$b->chauffeur_id || !$b->scheduled_at);
                        foreach ($noChauffeurOrSchedule as $b) {
                            $ongoingSingle->push($b);
                        }
                    @endphp

                    {{-- Render Grouped Unified Trips --}}
                    @foreach($ongoingGroups as $group)
                        @php
                            $firstBooking = $group->first();
                            $pkgTitle = $firstBooking->items->first()?->bookable->title ?? 'Tour';
                            $totalGuests = $group->sum(fn($b) => $b->items->sum('quantity'));
                            $groupColors = ['#2563eb', '#8b5cf6', '#0d9488', '#4f46e5', '#e11d48'];
                            $colorIndex = ($firstBooking->chauffeur_id ?? 0) % count($groupColors);
                            $groupColor = $groupColors[$colorIndex];
                        @endphp
                        <div style="padding: 20px; border-radius: 16px; background: var(--bg-main); margin-bottom: 15px; border: 2px solid {{ $groupColor }}40; position: relative;">
                            <!-- Unified Badge -->
                            <div style="position: absolute; top: 15px; right: 15px; display: flex; gap: 6px;">
                                <span style="background: {{ $groupColor }}1a; color: {{ $groupColor }}; padding: 3px 8px; border-radius: 6px; font-size: 0.65rem; font-weight: 800; border: 1px solid {{ $groupColor }}33;">
                                    <i class="fas fa-link"></i> Unified Trip Group
                                </span>
                            </div>

                            <div style="margin-bottom: 12px;">
                                <div style="font-weight: 800; color: var(--primary); font-size: 1rem; padding-right: 120px;">
                                    {{ $pkgTitle }} | {{ $firstBooking->customer_name ?: ($firstBooking->user->name ?? 'Guest') }} + {{ $group->count() - 1 }} others
                                </div>
                                <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 4px; display: flex; align-items: center; flex-wrap: wrap; gap: 8px;">
                                    <span style="color: var(--accent); font-weight: 700;"><i class="fas fa-users"></i> {{ $totalGuests }} Total {{ Str::plural('Person', $totalGuests) }}</span> | 
                                    <i class="fas fa-calendar-alt"></i> {{ $firstBooking->scheduled_at ? $firstBooking->scheduled_at->format('M d, Y') : 'N/A' }}
                                </div>
                            </div>

                            <!-- List of bookings inside the group -->
                            <div style="background: var(--bg-card); border-radius: 10px; border: 1px dashed var(--border); padding: 12px; margin-bottom: 15px; display: flex; flex-direction: column; gap: 10px;">
                                <div style="font-size: 0.65rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; margin-bottom: 2px;">Group Passengers List</div>
                                @foreach($group as $b)
                                    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.75rem; border-bottom: 1px solid rgba(0,0,0,0.03); padding-bottom: 6px; margin-bottom: 2px;">
                                        <div style="font-weight: 700; color: var(--text-main);">
                                            {{ $b->customer_name ?: ($b->user->name ?? 'Guest') }}
                                            <span style="font-weight: 400; color: var(--text-muted); font-size: 0.7rem; margin-left: 5px;">({{ $b->items->sum('quantity') }} people - {{ $b->booking_reference }})</span>
                                        </div>
                                        <a href="{{ route('admin.bookings.show', $b) }}" class="badge" style="background: rgba(245, 158, 11, 0.1); color: #d97706; text-decoration: none; padding: 2px 6px; font-size: 0.65rem;">View Booking</a>
                                    </div>
                                @endforeach
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; padding-top: 12px; border-top: 1px dashed var(--border);">
                                <div>
                                    <div style="font-size: 0.6rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">Leg Status</div>
                                    <div style="font-weight: 700; font-size: 0.8rem; color: var(--text-main); display: flex; align-items: center; gap: 6px;">
                                        <i class="fas fa-circle-notch fa-spin" style="color: #f59e0b;"></i>
                                        @if($firstBooking->return_trip_status === 'in_progress')
                                            LIVE: Return Leg <i class="fas fa-arrow-left"></i>
                                        @else
                                            LIVE: Outbound Leg <i class="fas fa-arrow-right"></i>
                                        @endif
                                    </div>
                                </div>
                                <div>
                                    <div style="font-size: 0.6rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">Driver & Vehicle</div>
                                    <div style="font-weight: 700; font-size: 0.8rem; color: var(--primary); display: flex; flex-direction: column; gap: 2px;">
                                        <div style="display: flex; align-items: center; gap: 4px;">
                                            <i class="fas fa-user-tie" style="font-size: 0.7rem;"></i>
                                            {{ $firstBooking->chauffeur->user->name ?? 'Not Assigned' }}
                                        </div>
                                        @if($firstBooking->chauffeur && $firstBooking->chauffeur->vehicle)
                                            <div style="font-size: 0.7rem; color: #64748b; display: flex; align-items: center; gap: 4px;">
                                                <i class="fas fa-car-side" style="font-size: 0.65rem;"></i>
                                                <span style="background: #e2e8f0; padding: 1px 6px; border-radius: 4px; font-family: monospace; font-weight: 800; border: 1px solid #cbd5e1;">{{ $firstBooking->chauffeur->vehicle->license_plate }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    {{-- Render Single Ongoing Trips --}}
                    @foreach($ongoingSingle as $booking)
                    <div style="padding: 20px; border-radius: 16px; background: var(--bg-main); margin-bottom: 15px; border: 1px solid var(--border);">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                            <div>
                                <div style="font-weight: 800; color: var(--primary); font-size: 1rem;">
                                    {{ $booking->items->first()->bookable->title ?? 'Tour' }}
                                    <span style="font-weight: 400; color: var(--text-muted); margin-left: 8px;">| {{ $booking->customer_name ?: ($booking->user->name ?? 'Guest') }}</span>
                                </div>
                                <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 4px; display: flex; align-items: center; flex-wrap: wrap; gap: 8px;">
                                    <i class="fas fa-hashtag"></i> {{ $booking->booking_reference }} | 
                                    <span style="color: var(--accent); font-weight: 700;"><i class="fas fa-users"></i> {{ $booking->items->sum('quantity') }} {{ Str::plural('Person', $booking->items->sum('quantity')) }}</span> | 
                                    <i class="fas fa-calendar-alt"></i> {{ $booking->scheduled_at ? $booking->scheduled_at->format('M d, Y') : 'N/A' }}
                                </div>
                            </div>
                            <a href="{{ route('admin.bookings.show', $booking) }}" class="badge" style="background: rgba(245, 158, 11, 0.1); color: #d97706; text-decoration: none;">View Booking</a>
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; padding-top: 12px; border-top: 1px dashed var(--border);">
                            <div>
                                <div style="font-size: 0.6rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">Reference</div>
                                <div style="font-weight: 700; font-size: 0.8rem; color: var(--text-main);">{{ $booking->booking_reference }}</div>
                            </div>
                            <div>
                                <div style="font-size: 0.6rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">Leg Status</div>
                                <div style="font-weight: 700; font-size: 0.8rem; color: var(--text-main); display: flex; align-items: center; gap: 6px;">
                                    <i class="fas fa-circle-notch fa-spin" style="color: #f59e0b;"></i>
                                    @if($booking->return_trip_status === 'in_progress')
                                        LIVE: Return Leg <i class="fas fa-arrow-left"></i>
                                    @else
                                        LIVE: Outbound Leg <i class="fas fa-arrow-right"></i>
                                    @endif
                                </div>
                            </div>
                            <div>
                                <div style="font-size: 0.6rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">Driver & Vehicle</div>
                                <div style="font-weight: 700; font-size: 0.8rem; color: var(--primary); display: flex; flex-direction: column; gap: 2px;">
                                    <div style="display: flex; align-items: center; gap: 4px;">
                                        <i class="fas fa-user-tie" style="font-size: 0.7rem;"></i>
                                        {{ $booking->chauffeur->user->name ?? 'Not Assigned' }}
                                    </div>
                                    @if($booking->chauffeur && $booking->chauffeur->vehicle)
                                        <div style="font-size: 0.7rem; color: #64748b; display: flex; align-items: center; gap: 4px;">
                                            <i class="fas fa-car-side" style="font-size: 0.65rem;"></i>
                                            <span style="background: #e2e8f0; padding: 1px 6px; border-radius: 4px; font-family: monospace; font-weight: 800; border: 1px solid #cbd5e1;">{{ $booking->chauffeur->vehicle->license_plate }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach

                    @if($ongoingFixedTours->isEmpty())
                    <div style="text-align: center; padding: 40px; color: var(--text-muted);">
                        <i class="fas fa-info-circle" style="font-size: 2rem; margin-bottom: 15px; opacity: 0.3;"></i>
                        <p>No fixed packages are currently ongoing.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </template>

    <!-- Completed Fixed Tours Modal -->
    <template x-teleport="body">
        <div x-show="completedFixedModal" 
             class="modal-backdrop" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="completedFixedModal = false"
             style="position: fixed; inset: 0; background: rgba(0,0,0,0.5); backdrop-filter: blur(4px); z-index: 9999; display: flex; align-items: center; justify-content: center; padding: 20px;"
             x-cloak>
            <div class="modal-content" @click.stop style="background: var(--bg-card); width: 100%; max-width: 650px; border-radius: 20px; overflow: hidden; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);">
                <div style="background: #8b5cf6; padding: 25px; color: white; display: flex; justify-content: space-between; align-items: center;">
                    <h3 style="margin: 0; font-family: 'Outfit', sans-serif;">Completed Fixed Packages</h3>
                    <button @click="completedFixedModal = false" style="background: none; border: none; color: white; cursor: pointer; font-size: 1.2rem;"><i class="fas fa-times"></i></button>
                </div>
                <div style="padding: 25px; max-height: 70vh; overflow-y: auto;">
                    @php
                        $completedGroups = collect();
                        $completedSingle = collect();
                        
                        $cGroups = $completedFixedTours->filter(fn($b) => $b->chauffeur_id && $b->scheduled_at)
                            ->groupBy(function($b) {
                                return ($b->chauffeur_id ?? 0) . '_' . ($b->scheduled_at ? $b->scheduled_at->format('Y-m-d') : 'none');
                            });
                            
                        foreach ($cGroups as $key => $group) {
                            if ($group->count() > 1) {
                                $completedGroups->push($group);
                            } else {
                                $completedSingle->push($group->first());
                            }
                        }
                        
                        $cNoChauffeurOrSchedule = $completedFixedTours->filter(fn($b) => !$b->chauffeur_id || !$b->scheduled_at);
                        foreach ($cNoChauffeurOrSchedule as $b) {
                            $completedSingle->push($b);
                        }
                    @endphp

                    {{-- Render Grouped Unified Completed Trips --}}
                    @foreach($completedGroups as $group)
                        @php
                            $firstBooking = $group->first();
                            $pkgTitle = $firstBooking->items->first()?->bookable->title ?? 'Tour';
                            $totalGuests = $group->sum(fn($b) => $b->items->sum('quantity'));
                            $groupColors = ['#2563eb', '#8b5cf6', '#0d9488', '#4f46e5', '#e11d48'];
                            $colorIndex = ($firstBooking->chauffeur_id ?? 0) % count($groupColors);
                            $groupColor = $groupColors[$colorIndex];
                        @endphp
                        <div style="padding: 20px; border-radius: 16px; background: var(--bg-main); margin-bottom: 15px; border: 2px solid {{ $groupColor }}40; position: relative;">
                            <!-- Unified Badge -->
                            <div style="position: absolute; top: 15px; right: 15px; display: flex; gap: 6px;">
                                <span style="background: {{ $groupColor }}1a; color: {{ $groupColor }}; padding: 3px 8px; border-radius: 6px; font-size: 0.65rem; font-weight: 800; border: 1px solid {{ $groupColor }}33;">
                                    <i class="fas fa-link"></i> Unified Trip Group
                                </span>
                            </div>

                            <div style="margin-bottom: 12px;">
                                <div style="font-weight: 800; color: var(--primary); font-size: 1rem; padding-right: 120px;">
                                    {{ $pkgTitle }} | {{ $firstBooking->customer_name ?: ($firstBooking->user->name ?? 'Guest') }} + {{ $group->count() - 1 }} others
                                </div>
                                <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 4px; display: flex; align-items: center; flex-wrap: wrap; gap: 8px;">
                                    <span style="color: var(--accent); font-weight: 700;"><i class="fas fa-users"></i> {{ $totalGuests }} Total {{ Str::plural('Person', $totalGuests) }}</span> | 
                                    <i class="fas fa-calendar-alt"></i> {{ $firstBooking->scheduled_at ? $firstBooking->scheduled_at->format('M d, Y') : 'N/A' }}
                                </div>
                            </div>

                            <!-- List of bookings inside the group -->
                            <div style="background: var(--bg-card); border-radius: 10px; border: 1px dashed var(--border); padding: 12px; margin-bottom: 15px; display: flex; flex-direction: column; gap: 10px;">
                                <div style="font-size: 0.65rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; margin-bottom: 2px;">Group Passengers List</div>
                                @foreach($group as $b)
                                    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.75rem; border-bottom: 1px solid rgba(0,0,0,0.03); padding-bottom: 6px; margin-bottom: 2px;">
                                        <div style="font-weight: 700; color: var(--text-main);">
                                            {{ $b->customer_name ?: ($b->user->name ?? 'Guest') }}
                                            <span style="font-weight: 400; color: var(--text-muted); font-size: 0.7rem; margin-left: 5px;">({{ $b->items->sum('quantity') }} people - {{ $b->booking_reference }})</span>
                                        </div>
                                        <a href="{{ route('admin.bookings.show', $b) }}" class="badge" style="background: rgba(245, 158, 11, 0.1); color: #d97706; text-decoration: none; padding: 2px 6px; font-size: 0.65rem;">View Booking</a>
                                    </div>
                                @endforeach
                            </div>

                            <div style="margin-top: 10px; padding-top: 10px; border-top: 1px dashed var(--border);">
                                <div style="font-size: 0.7rem; color: var(--text-muted); display: flex; align-items: center; gap: 5px; margin-bottom: 8px;">
                                    <i class="fas fa-id-badge" style="color: #8b5cf6;"></i> <span><strong>Driver:</strong> {{ $firstBooking->chauffeur->user->name ?? 'Not Assigned' }}</span>
                                </div>
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                                    <div style="background: rgba(139, 92, 246, 0.05); padding: 8px; border-radius: 6px; border: 1px solid rgba(139, 92, 246, 0.1);">
                                        <div style="font-size: 0.65rem; font-weight: 800; color: #8b5cf6; text-transform: uppercase; margin-bottom: 4px;">Outbound Trip</div>
                                        <div style="font-size: 0.65rem; color: var(--text-muted);"><i class="fas fa-play" style="width: 12px; color: #10b981;"></i> <strong>Started:</strong> {{ $firstBooking->trip_started_at ? $firstBooking->trip_started_at->format('M d, g:i A') : 'N/A' }}</div>
                                        <div style="font-size: 0.65rem; color: var(--text-muted); margin-top: 2px;"><i class="fas fa-stop" style="width: 12px; color: #ef4444;"></i> <strong>Ended:</strong> {{ $firstBooking->trip_ended_at ? $firstBooking->trip_ended_at->format('M d, g:i A') : 'N/A' }}</div>
                                    </div>
                                    <div style="background: rgba(139, 92, 246, 0.05); padding: 8px; border-radius: 6px; border: 1px solid rgba(139, 92, 246, 0.1);">
                                        <div style="font-size: 0.65rem; font-weight: 800; color: #8b5cf6; text-transform: uppercase; margin-bottom: 4px;">Return Trip</div>
                                        <div style="font-size: 0.65rem; color: var(--text-muted);"><i class="fas fa-play" style="width: 12px; color: #10b981;"></i> <strong>Started:</strong> {{ $firstBooking->return_started_at ? $firstBooking->return_started_at->format('M d, g:i A') : 'N/A' }}</div>
                                        <div style="font-size: 0.65rem; color: var(--text-muted); margin-top: 2px;"><i class="fas fa-stop" style="width: 12px; color: #ef4444;"></i> <strong>Ended:</strong> {{ $firstBooking->return_ended_at ? $firstBooking->return_ended_at->format('M d, g:i A') : 'N/A' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    {{-- Render Single Completed Trips --}}
                    @foreach($completedSingle as $booking)
                    <div style="display: flex; gap: 15px; padding: 15px; border-radius: 12px; background: var(--bg-main); margin-bottom: 15px; border: 1px solid var(--border);">
                        <div style="width: 50px; height: 50px; border-radius: 10px; background: rgba(139, 92, 246, 0.1); color: #8b5cf6; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <i class="fas fa-history"></i>
                        </div>
                        <div style="flex: 1;">
                            <div style="font-weight: 700; color: var(--primary);">
                                {{ $booking->items->first()->bookable->title ?? 'Tour' }}
                                <span style="font-weight: 400; color: var(--text-muted); margin-left: 8px;">| {{ $booking->customer_name ?: ($booking->user->name ?? 'Guest') }}</span>
                            </div>
                            <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 2px; display: flex; align-items: center; flex-wrap: wrap; gap: 8px;">
                                <i class="fas fa-hashtag"></i> {{ $booking->booking_reference }} | 
                                <span style="color: var(--accent); font-weight: 700;"><i class="fas fa-users"></i> {{ $booking->items->sum('quantity') }} {{ Str::plural('Person', $booking->items->sum('quantity')) }}</span> | 
                                <i class="fas fa-calendar-alt"></i> {{ $booking->scheduled_at ? $booking->scheduled_at->format('M d, Y') : 'N/A' }}
                            </div>
                            <div style="margin-top: 10px; padding-top: 10px; border-top: 1px dashed var(--border);">
                                <div style="font-size: 0.7rem; color: var(--text-muted); display: flex; align-items: center; gap: 5px; margin-bottom: 8px;">
                                    <i class="fas fa-id-badge" style="color: #8b5cf6;"></i> <span><strong>Driver:</strong> {{ $booking->chauffeur->user->name ?? 'Not Assigned' }}</span>
                                </div>
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                                    <div style="background: rgba(139, 92, 246, 0.05); padding: 8px; border-radius: 6px; border: 1px solid rgba(139, 92, 246, 0.1);">
                                        <div style="font-size: 0.65rem; font-weight: 800; color: #8b5cf6; text-transform: uppercase; margin-bottom: 4px;">Outbound Trip</div>
                                        <div style="font-size: 0.65rem; color: var(--text-muted);"><i class="fas fa-play" style="width: 12px; color: #10b981;"></i> <strong>Started:</strong> {{ $booking->trip_started_at ? $booking->trip_started_at->format('M d, g:i A') : 'N/A' }}</div>
                                        <div style="font-size: 0.65rem; color: var(--text-muted); margin-top: 2px;"><i class="fas fa-stop" style="width: 12px; color: #ef4444;"></i> <strong>Ended:</strong> {{ $booking->trip_ended_at ? $booking->trip_ended_at->format('M d, g:i A') : 'N/A' }}</div>
                                        <div style="font-size: 0.65rem; color: var(--text-muted); margin-top: 2px;"><i class="fas fa-stopwatch" style="width: 12px; color: #8b5cf6;"></i> <strong>Duration:</strong> {{ $booking->trip_duration ?: 'N/A' }}</div>
                                    </div>
                                    <div style="background: rgba(139, 92, 246, 0.05); padding: 8px; border-radius: 6px; border: 1px solid rgba(139, 92, 246, 0.1);">
                                        <div style="font-size: 0.65rem; font-weight: 800; color: #8b5cf6; text-transform: uppercase; margin-bottom: 4px;">Return Trip</div>
                                        <div style="font-size: 0.65rem; color: var(--text-muted);"><i class="fas fa-play" style="width: 12px; color: #10b981;"></i> <strong>Started:</strong> {{ $booking->return_started_at ? $booking->return_started_at->format('M d, g:i A') : 'N/A' }}</div>
                                        <div style="font-size: 0.65rem; color: var(--text-muted); margin-top: 2px;"><i class="fas fa-stop" style="width: 12px; color: #ef4444;"></i> <strong>Ended:</strong> {{ $booking->return_ended_at ? $booking->return_ended_at->format('M d, g:i A') : 'N/A' }}</div>
                                        <div style="font-size: 0.65rem; color: var(--text-muted); margin-top: 2px;"><i class="fas fa-stopwatch" style="width: 12px; color: #8b5cf6;"></i> <strong>Duration:</strong> {{ $booking->return_duration ?: 'N/A' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div style="text-align: right;">
                            <a href="{{ route('admin.bookings.show', $booking) }}" class="btn btn-primary" style="font-size: 0.65rem; padding: 5px 12px;">Details</a>
                        </div>
                    </div>
                    @endforeach

                    @if($completedFixedTours->isEmpty())
                    <div style="text-align: center; padding: 40px; color: var(--text-muted);">
                        <i class="fas fa-info-circle" style="font-size: 2rem; margin-bottom: 15px; opacity: 0.3;"></i>
                        <p>No completed fixed packages found.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </template>

    <!-- Completed Organized Tours Modal -->
    <template x-teleport="body">
        <div x-show="completedModal" 
             class="modal-backdrop" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="completedModal = false"
             style="position: fixed; inset: 0; background: rgba(0,0,0,0.5); backdrop-filter: blur(4px); z-index: 9999; display: flex; align-items: center; justify-content: center; padding: 20px;"
             x-cloak>
            <div class="modal-content" @click.stop style="background: var(--bg-card); width: 100%; max-width: 600px; border-radius: 20px; overflow: hidden; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);">
                <div style="background: #10b981; padding: 25px; color: white; display: flex; justify-content: space-between; align-items: center;">
                    <h3 style="margin: 0; font-family: 'Outfit', sans-serif;">Completed Organized Tours</h3>
                    <button @click="completedModal = false" style="background: none; border: none; color: white; cursor: pointer; font-size: 1.2rem;"><i class="fas fa-times"></i></button>
                </div>
                <div style="padding: 25px; max-height: 70vh; overflow-y: auto;">
                    @forelse($completedTours as $tour)
                    <div style="display: flex; gap: 15px; padding: 15px; border-radius: 12px; background: var(--bg-main); margin-bottom: 15px; border: 1px solid var(--border);">
                        <div style="width: 50px; height: 50px; border-radius: 10px; background: rgba(16, 185, 129, 0.1); color: #10b981; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div style="flex: 1;">
                            <div style="font-weight: 700; color: var(--primary);">{{ $tour->title }}</div>
                            <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 4px;">
                                <i class="fas fa-map-marker-alt"></i> {{ $tour->location }} | <i class="fas fa-calendar-check"></i> Ended {{ $tour->return_date ? $tour->return_date->format('M d, Y') : $tour->departure_date->format('M d, Y') }}
                            </div>
                        </div>
                        <div style="text-align: right;">
                            <span class="badge" style="font-size: 0.65rem; background: #d1fae5; color: #065f46;">{{ $tour->category->name ?? 'Tour' }}</span>
                        </div>
                    </div>
                    @empty
                    <div style="text-align: center; padding: 40px; color: var(--text-muted);">
                        <i class="fas fa-info-circle" style="font-size: 2rem; margin-bottom: 15px; opacity: 0.3;"></i>
                        <p>No completed tours found.</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </template>
    @endhasanyrole

    @hasrole('Driver/Chauffeur')
    <div class="card" style="padding: 30px; text-align: center; background: white; border-radius: 20px; box-shadow: var(--shadow-sm); margin-bottom: 30px;">
        <div style="width: 70px; height: 70px; background: rgba(30, 58, 138, 0.1); color: var(--primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px; font-size: 1.8rem;">
            <i class="fas fa-steering-wheel"></i>
        </div>
        <h2 style="font-family: 'Outfit', sans-serif; color: var(--primary); margin: 0;">Driver Portal</h2>
        <p style="color: var(--text-muted); max-width: 500px; margin: 10px auto 0;">Welcome to your driver dashboard. Soon you will be able to see your assigned trips and vehicle schedules here.</p>
    </div>
    @endrole

    @hasanyrole('Customer|Corporate Account')
    <div class="dashboard-welcome-card">
        <div style="width: 70px; height: 70px; background: rgba(245, 158, 11, 0.1); color: var(--accent); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px; font-size: 1.8rem;">
            <i class="fas fa-user-circle"></i>
        </div>
        <h2 style="font-family: 'Outfit', sans-serif; color: var(--primary); margin: 0;">Welcome to Bruno Heights</h2>
        <p style="color: var(--text-muted); max-width: 500px; margin: 10px auto 0;">Manage your travel bookings, view your itineraries, and explore our premium fleet services all in one place.</p>
        
        <div class="customer-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px; margin-top: 25px; text-align: left; padding: 10px 0;">
            <div style="background: var(--bg-main); padding: 25px; border-radius: 15px; border: 1px solid var(--border); border-left: 5px solid var(--primary); box-shadow: var(--shadow-sm); display: flex; flex-direction: column; justify-content: space-between; min-height: 220px;">
                <div>
                    <h4 style="margin: 0 0 10px; color: var(--primary); font-family: 'Outfit', sans-serif;"><i class="fas fa-umbrella-beach"></i> Fixed Tours</h4>
                    <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 15px; line-height: 1.5;">Discover breathtaking pre-packaged destinations and scheduled group adventures.</p>
                </div>
                <div>
                    <a href="{{ route('customer.tourism.fixed') }}" class="btn btn-primary" style="font-size: 0.85rem; padding: 10px 24px; border-radius: 8px; text-decoration: none; display: inline-block; box-shadow: 0 4px 10px rgba(37, 99, 235, 0.15);">Book a Tour</a>
                </div>
            </div>
            <div style="background: var(--bg-main); padding: 25px; border-radius: 15px; border: 1px solid var(--border); border-left: 5px solid #10b981; box-shadow: var(--shadow-sm); display: flex; flex-direction: column; justify-content: space-between; min-height: 220px;">
                <div>
                    <h4 style="margin: 0 0 10px; color: #10b981; font-family: 'Outfit', sans-serif;"><i class="fas fa-map-marked-alt"></i> Organized Tours</h4>
                    <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 15px; line-height: 1.5;">Plan your own custom itinerary or corporate getaway with our local experts.</p>
                </div>
                <div>
                    <a href="{{ route('customer.tourism.organized') }}" class="btn btn-primary" style="font-size: 0.85rem; padding: 10px 24px; border-radius: 8px; text-decoration: none; display: inline-block; background: #10b981; border: none; box-shadow: 0 4px 10px rgba(16, 185, 129, 0.15);">Plan a Tour</a>
                </div>
            </div>
            <div style="background: var(--bg-main); padding: 25px; border-radius: 15px; border: 1px solid var(--border); border-left: 5px solid var(--accent); box-shadow: var(--shadow-sm); display: flex; flex-direction: column; justify-content: space-between; min-height: 220px;">
                <div>
                    <h4 style="margin: 0 0 10px; color: var(--accent); font-family: 'Outfit', sans-serif;"><i class="fas fa-car"></i> Car Hiring</h4>
                    <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 15px; line-height: 1.5;">Rent premium vehicles with professional chauffeurs for your personal or corporate travel needs.</p>
                </div>
                <div>
                    <a href="{{ route('customer.fleet.hiring') }}" class="btn btn-primary" style="font-size: 0.85rem; padding: 10px 24px; border-radius: 8px; text-decoration: none; display: inline-block; background: var(--accent); border: none; box-shadow: 0 4px 10px rgba(245, 158, 11, 0.15);">Hire a Vehicle</a>
                </div>
            </div>
            <div style="background: var(--bg-main); padding: 25px; border-radius: 15px; border: 1px solid var(--border); border-left: 5px solid #8b5cf6; box-shadow: var(--shadow-sm); display: flex; flex-direction: column; justify-content: space-between; min-height: 220px;">
                <div>
                    <h4 style="margin: 0 0 10px; color: #8b5cf6; font-family: 'Outfit', sans-serif;"><i class="fas fa-plane-arrival"></i> Airport Transfers</h4>
                    <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 15px; line-height: 1.5;">Reliable, comfortable, and punctual airport pickup and drop-off services.</p>
                </div>
                <div>
                    <a href="{{ route('customer.fleet.transfers') }}" class="btn btn-primary" style="font-size: 0.85rem; padding: 10px 24px; border-radius: 8px; text-decoration: none; display: inline-block; background: #8b5cf6; border: none; box-shadow: 0 4px 10px rgba(139, 92, 246, 0.15);">Book a Transfer</a>
                </div>
            </div>
        </div>
    </div>
    @endhasanyrole

    @hasanyrole('Super Admin|Operations Admin|Customer|Corporate Account')
    <div class="row dashboard-row-grid">
        <div class="card">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="font-family: 'Outfit', sans-serif;">{{ auth()->user()->hasAnyRole(['Super Admin', 'Operations Admin']) ? 'Recent Bookings' : 'My Recent Bookings' }}</h3>
                <a href="{{ route('admin.bookings.index') }}" style="color: var(--primary); text-decoration: none; font-size: 0.9rem; font-weight: 600;">View All</a>
            </div>
            <div class="table-container dashboard-table-container">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="text-align: left; background: rgba(30, 58, 138, 0.03); color: var(--text-muted); font-size: 0.7rem; text-transform: uppercase; font-weight: 800; letter-spacing: 0.5px;">
                            <th style="padding: 12px 20px;">{{ auth()->user()->hasAnyRole(['Super Admin', 'Operations Admin']) ? 'Customer' : 'Reference' }}</th>
                            <th style="padding: 12px 20px;">Status</th>
                            <th style="padding: 12px 20px;">Amount</th>
                            <th style="padding: 12px 20px; text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentBookings as $booking)
                        <tr style="border-bottom: 1px solid var(--border); transition: background 0.2s;" onmouseover="this.style.background='rgba(37, 99, 235, 0.02)'" onmouseout="this.style.background='transparent'">
                            <td style="padding: 15px 20px;">
                                @if(auth()->user()->hasAnyRole(['Super Admin', 'Operations Admin']))
                                    <div style="font-weight: 700; color: var(--text-main); font-size: 0.9rem; display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                                        {{ $booking->customer_name ?: ($booking->user->name ?? 'Guest') }}
                                        <span style="font-size: 0.7rem; color: var(--text-muted); font-weight: 600; background: #e2e8f0; padding: 1px 6px; border-radius: 4px; display: inline-flex; align-items: center; gap: 4px;">
                                            <i class="fas fa-users" style="font-size: 0.65rem;"></i> {{ $booking->items->sum('quantity') }} {{ Str::plural('guest', $booking->items->sum('quantity')) }}
                                        </span>
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 8px; margin-top: 4px;">
                                        @php
                                            $firstItem = $booking->items->first();
                                            $typeLabel = 'Service';
                                            $typeColor = '#64748b';
                                            $title = 'N/A';
                                            if ($firstItem) {
                                                if ($firstItem->bookable_type === 'Modules\Tourism\Models\TourismPackage') {
                                                    $typeLabel = $firstItem->bookable->package_type === 'fixed' ? 'Fixed Tour' : 'Organized Tour';
                                                    $typeColor = '#0ea5e9';
                                                    $title = $firstItem->bookable->title;
                                                } elseif ($firstItem->bookable_type === 'Modules\Fleet\Models\AirportTransfer') {
                                                    $typeLabel = ($firstItem->bookable->category ?? 'airport') === 'airport' ? 'Airport Transfer' : 'General Transfer';
                                                    $typeColor = '#10b981';
                                                    $title = $firstItem->bookable->airport_name;
                                                } else {
                                                    $typeLabel = 'Car Hiring';
                                                    $typeColor = '#f59e0b';
                                                    $title = ($firstItem->bookable->make ?? '') . ' ' . ($firstItem->bookable->model ?? '');
                                                }
                                            }
                                        @endphp
                                        <span style="font-size: 0.65rem; font-weight: 800; text-transform: uppercase; color: {{ $typeColor }}; letter-spacing: 0.3px;">{{ $typeLabel }}</span>
                                    </div>
                                    <div style="margin-top: 5px;">
                                        <span style="background: #000; color: #fff; padding: 2px 8px; border-radius: 4px; font-size: 0.65rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; display: inline-block;">
                                            {{ $title }}
                                        </span>
                                    </div>
                                @else
                                    <a href="{{ route('admin.bookings.show', $booking) }}" style="text-decoration: none;">
                                        <div style="font-weight: 700; color: var(--primary); font-size: 0.9rem; display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                                            {{ $booking->booking_reference }}
                                            @if($booking->payment_status === 'paid')
                                                <i class="fas fa-star" style="color: #eab308;" title="Fully Paid"></i>
                                            @endif
                                            <span style="font-size: 0.7rem; color: var(--text-muted); font-weight: 600; background: #e2e8f0; padding: 1px 6px; border-radius: 4px; display: inline-flex; align-items: center; gap: 4px;">
                                                <i class="fas fa-users" style="font-size: 0.65rem;"></i> {{ $booking->items->sum('quantity') }} {{ Str::plural('guest', $booking->items->sum('quantity')) }}
                                            </span>
                                        </div>
                                        @php
                                            $firstItem = $booking->items->first();
                                            $typeLabel = 'Service';
                                            $typeColor = '#64748b';
                                            $title = 'N/A';
                                            if ($firstItem) {
                                                if ($firstItem->bookable_type === 'Modules\Tourism\Models\TourismPackage') {
                                                    $typeLabel = $firstItem->bookable->package_type === 'fixed' ? 'Fixed Tour' : 'Organized Tour';
                                                    $typeColor = '#0ea5e9';
                                                    $title = $firstItem->bookable->title;
                                                } elseif ($firstItem->bookable_type === 'Modules\Fleet\Models\AirportTransfer') {
                                                    $typeLabel = ($firstItem->bookable->category ?? 'airport') === 'airport' ? 'Airport Transfer' : 'General Transfer';
                                                    $typeColor = '#10b981';
                                                    $title = $firstItem->bookable->airport_name;
                                                } else {
                                                    $typeLabel = 'Car Hiring';
                                                    $typeColor = '#f59e0b';
                                                    $title = ($firstItem->bookable->make ?? '') . ' ' . ($firstItem->bookable->model ?? '');
                                                }
                                            }
                                        @endphp
                                        <span style="font-size: 0.65rem; font-weight: 800; text-transform: uppercase; color: {{ $typeColor }}; letter-spacing: 0.3px;">{{ $typeLabel }}</span>
                                        <div style="margin-top: 5px;">
                                            <span style="background: #000; color: #fff; padding: 2px 8px; border-radius: 4px; font-size: 0.65rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; display: inline-block;">
                                                {{ $title }}
                                            </span>
                                        </div>
                                    </a>
                                @endif
                            </td>
                            <td style="padding: 15px 20px;">
                                @if($booking->trip_status === 'in_progress' && $booking->trip_leg === 'outbound')
                                    <div style="display: flex; flex-direction: column; gap: 4px;">
                                        <div style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 20px; font-size: 0.65rem; font-weight: 800; text-transform: uppercase; background: rgba(16, 185, 129, 0.15); color: #059669; border: 1px solid rgba(16, 185, 129, 0.3);">
                                            <i class="fas fa-circle-notch fa-spin" style="font-size: 0.75rem; color: #f59e0b;"></i> Live Trip (Outbound)
                                        </div>
                                        @if($booking->chauffeur)
                                            <div style="font-size: 0.65rem; color: var(--text-muted); font-weight: 700; padding-left: 2px; display: flex; flex-direction: column; gap: 2px;">
                                                <div style="display: flex; align-items: center; gap: 4px;">
                                                    <i class="fas fa-user-tie" style="font-size: 0.6rem;"></i> {{ $booking->chauffeur->user->name ?? 'Driver' }}
                                                </div>
                                                @if($booking->chauffeur->vehicle)
                                                    <div style="font-size: 0.6rem; color: #64748b; font-family: monospace; font-weight: 800;">
                                                        <i class="fas fa-car-side" style="font-size: 0.55rem;"></i> {{ $booking->chauffeur->vehicle->license_plate }}
                                                    </div>
                                                @endif
                                                @if($recentBookings->where('chauffeur_id', $booking->chauffeur_id)->count() > 1)
                                                    @php
                                                        $groupColors = ['#2563eb', '#8b5cf6', '#0d9488', '#4f46e5', '#e11d48'];
                                                        $colorIndex = ($booking->chauffeur_id ?? 0) % count($groupColors);
                                                        $groupColor = $groupColors[$colorIndex];
                                                    @endphp
                                                    <div style="margin-top: 2px;">
                                                        <span style="background: {{ $groupColor }}1a; color: {{ $groupColor }}; padding: 1px 6px; border-radius: 4px; font-size: 0.55rem; font-weight: 800; border: 1px solid {{ $groupColor }}33;">
                                                            <i class="fas fa-link"></i> Unified
                                                        </span>
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                @elseif($booking->trip_status === 'completed' && $booking->trip_leg === 'return' && $booking->return_trip_status === 'idle')
                                    <div style="display: flex; flex-direction: column; gap: 4px;">
                                        <div style="display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; border-radius: 20px; font-size: 0.65rem; font-weight: 800; text-transform: uppercase; background: rgba(245, 158, 11, 0.15); color: #d97706; border: 1px solid rgba(245, 158, 11, 0.3);">
                                            <i class="fas fa-map-marker-alt" style="font-size: 0.55rem;"></i> At Destination
                                        </div>
                                    </div>
                                @elseif($booking->return_trip_status === 'in_progress')
                                    <div style="display: flex; flex-direction: column; gap: 4px;">
                                        <div style="display: inline-flex; align-items: center; gap: 6px; padding: 5px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 900; text-transform: uppercase; background: #2563eb; color: #ffffff; border: 1px solid #1e40af; box-shadow: 0 2px 4px rgba(37, 99, 235, 0.2);">
                                            <i class="fas fa-circle-notch fa-spin" style="font-size: 0.85rem; color: #facc15;"></i> LIVE TRIP (RETURN)
                                        </div>
                                        @if($booking->chauffeur)
                                            <div style="font-size: 0.65rem; color: var(--text-muted); font-weight: 700; padding-left: 2px; display: flex; flex-direction: column; gap: 2px;">
                                                <div style="display: flex; align-items: center; gap: 4px;">
                                                    <i class="fas fa-user-tie" style="font-size: 0.6rem;"></i> {{ $booking->chauffeur->user->name ?? 'Driver' }}
                                                </div>
                                                @if($booking->chauffeur->vehicle)
                                                    <div style="font-size: 0.6rem; color: #64748b; font-family: monospace; font-weight: 800;">
                                                        <i class="fas fa-car-side" style="font-size: 0.55rem;"></i> {{ $booking->chauffeur->vehicle->license_plate }}
                                                    </div>
                                                @endif
                                                @if($recentBookings->where('chauffeur_id', $booking->chauffeur_id)->count() > 1)
                                                    @php
                                                        $groupColors = ['#2563eb', '#8b5cf6', '#0d9488', '#4f46e5', '#e11d48'];
                                                        $colorIndex = ($booking->chauffeur_id ?? 0) % count($groupColors);
                                                        $groupColor = $groupColors[$colorIndex];
                                                    @endphp
                                                    <div style="margin-top: 2px;">
                                                        <span style="background: {{ $groupColor }}1a; color: {{ $groupColor }}; padding: 1px 6px; border-radius: 4px; font-size: 0.55rem; font-weight: 800; border: 1px solid {{ $groupColor }}33;">
                                                            <i class="fas fa-link"></i> Unified
                                                        </span>
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                @elseif($booking->trip_status === 'completed' && ($booking->return_trip_status === 'completed' || $booking->trip_leg === 'outbound'))
                                    <div style="display: flex; flex-direction: column; gap: 4px;">
                                        <div style="display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; border-radius: 20px; font-size: 0.65rem; font-weight: 800; text-transform: uppercase; background: rgba(59, 130, 246, 0.15); color: #2563eb; border: 1px solid rgba(59, 130, 246, 0.3);">
                                            <i class="fas fa-flag-checkered" style="font-size: 0.55rem;"></i> Trip Ended
                                        </div>
                                        @if($booking->chauffeur)
                                            <div style="font-size: 0.65rem; color: var(--text-muted); font-weight: 700; padding-left: 2px; display: flex; align-items: center; gap: 4px;">
                                                <i class="fas fa-user-tie" style="font-size: 0.6rem;"></i> {{ $booking->chauffeur->user->name ?? 'Driver' }}
                                            </div>
                                        @endif
                                    </div>
                                @elseif($booking->payment_status === 'paid' && $booking->status === 'confirmed' && $booking->trip_status === 'idle')
                                    <div style="display: flex; flex-direction: column; gap: 4px;">
                                        <div style="display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; border-radius: 20px; font-size: 0.65rem; font-weight: 800; text-transform: uppercase; background: rgba(245, 158, 11, 0.15); color: #d97706; border: 1px solid rgba(245, 158, 11, 0.3);">
                                            <i class="fas fa-calendar-alt" style="font-size: 0.55rem;"></i> Upcoming Trip
                                        </div>
                                        @if($booking->scheduled_at)
                                            <div style="font-size: 0.65rem; color: var(--text-muted); font-weight: 700; padding-left: 2px; display: flex; align-items: center; gap: 4px;">
                                                <i class="far fa-clock" style="font-size: 0.6rem;"></i> {{ \Carbon\Carbon::parse($booking->scheduled_at)->format('M d, Y') }}
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    @php
                                        $status = $booking->status;
                                        $bgColor = 'rgba(100, 116, 139, 0.1)';
                                        $color = '#64748b';
                                        
                                        if($status === 'confirmed') { $bgColor = 'rgba(16, 185, 129, 0.1)'; $color = '#10b981'; }
                                        elseif($status === 'pending') { $bgColor = 'rgba(245, 158, 11, 0.1)'; $color = '#f59e0b'; }
                                        elseif($status === 'completed') { $bgColor = 'rgba(37, 99, 235, 0.1)'; $color = '#2563eb'; }
                                        elseif($status === 'cancelled') { $bgColor = 'rgba(239, 68, 68, 0.1)'; $color = '#ef4444'; }
                                    @endphp
                                    <span class="badge" style="background: {{ $bgColor }}; color: {{ $color }}; text-transform: uppercase; font-size: 0.65rem; font-weight: 800; padding: 4px 10px; border-radius: 20px;">{{ $status }}</span>
                                @endif
                            </td>
                            <td style="padding: 15px 20px; font-weight: 700; color: var(--text-main);">
                                {{ $booking->currency_symbol }}{{ number_format($booking->total_amount, 2) }}
                            </td>
                            <td style="padding: 15px 20px; text-align: right;">
                                <a href="{{ route('admin.bookings.show', $booking) }}" style="width: 35px; height: 35px; border-radius: 10px; background: rgba(30, 58, 138, 0.05); color: var(--primary); display: inline-flex; align-items: center; justify-content: center; transition: all 0.2s; border: 1px solid var(--border);" onmouseover="this.style.background='var(--primary)'; this.style.color='white';" onmouseout="this.style.background='rgba(30, 58, 138, 0.05)'; this.style.color='var(--primary)';">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" style="padding: 40px; text-align: center; color: var(--text-muted);">No recent bookings found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header" style="margin-bottom: 20px;">
                <h3 style="font-family: 'Outfit', sans-serif;">Upcoming Organized Tours</h3>
            </div>
            <div style="display: flex; flex-direction: column; gap: 15px;">
                @forelse($upcomingTours as $tour)
                <div style="display: flex; gap: 15px; padding: 15px; border-radius: 12px; background: rgba(30, 58, 138, 0.03); border: 1px solid var(--border);">
                    <div style="width: 55px; height: 45px; border-radius: 10px; background: var(--bg-main); display: flex; flex-direction: column; align-items: center; justify-content: center; box-shadow: var(--shadow-sm); flex-shrink: 0; border: 1px solid var(--border);">
                        <span style="font-size: 0.6rem; font-weight: 800; color: var(--primary); text-transform: uppercase;">{{ $tour->departure_date->format('M') }}</span>
                        <span style="font-size: 0.9rem; font-weight: 800; color: var(--text-main);">
                            {{ $tour->departure_date->format('d') }}{{ $tour->return_date && $tour->return_date->format('M') == $tour->departure_date->format('M') ? '-' . $tour->return_date->format('d') : ($tour->return_date ? '+' : '') }}
                        </span>
                    </div>
                    <div style="flex: 1;">
                        <div style="font-weight: 700; color: var(--text-main); font-size: 0.9rem;">{{ $tour->title }}</div>
                        <div style="margin-top: 5px;">
                            <span class="badge" style="font-size: 0.6rem; background: var(--primary); color: white;">{{ $tour->category->name ?? 'TOUR' }}</span>
                        </div>
                        <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 5px;"><i class="fas fa-map-marker-alt"></i> {{ $tour->location }}</div>
                        <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 5px;"><i class="fas fa-users"></i> {{ $tour->guests_count ?? 0 }} / {{ $tour->max_guests }} guests</div>
                    </div>
                </div>
                @empty
                <div style="text-align: center; padding: 20px; color: var(--text-muted);">No upcoming organized tours.</div>
                @endforelse
            </div>
        </div>
    </div>
    @endhasanyrole
</div>
@endsection
