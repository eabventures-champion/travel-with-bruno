@extends('driver::layouts.master')

@section('content')
<div x-data="driverSchedule()">
    <h2 style="font-family: 'Outfit', sans-serif; color: var(--text-main); font-size: 1.5rem; margin-bottom: 20px;">My Schedule</h2>
 
    @if(session('success'))
    <div style="padding: 15px 20px; background: #ecfdf5; border: 1px solid #10b981; border-radius: 12px; color: #065f46; margin-bottom: 20px; font-weight: 600; display: flex; align-items: center; gap: 10px;">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
    @endif
 
    @if(session('error') || $errors->any())
    <div style="padding: 15px 20px; background: #fef2f2; border: 1px solid #ef4444; border-radius: 12px; color: #991b1b; margin-bottom: 20px; font-weight: 600;">
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: {{ $errors->any() ? '10px' : '0' }};">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') ?: 'There were some errors with your submission.' }}
        </div>
        @if($errors->any())
        <ul style="margin: 0; padding-left: 25px; font-size: 0.85rem;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        @endif
    </div>
    @endif

    <!-- Calendar Header -->
    <div class="card" style="padding: 15px; margin-bottom: 20px; text-align: center;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <button class="btn btn-outline" style="width: auto; padding: 5px 10px;"><i class="fas fa-chevron-left"></i></button>
            <div style="font-weight: 700; font-size: 1.1rem;">{{ $currentMonth }}</div>
            <button class="btn btn-outline" style="width: auto; padding: 5px 10px;"><i class="fas fa-chevron-right"></i></button>
        </div>
        
        <div style="display: flex; justify-content: space-between;">
            @foreach($weekDays as $day)
            <div style="display: flex; flex-direction: column; align-items: center;">
                <span style="font-size: 0.7rem; color: {{ $day['is_today'] ? 'var(--primary)' : 'var(--text-muted)' }}; font-weight: {{ $day['is_today'] ? '800' : '500' }};">{{ $day['day_name'] }}</span>
                <div style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; 
                    background: {{ $day['is_today'] ? 'var(--primary)' : ($day['is_cancelled'] ? '#ef4444' : ($day['is_accepted'] ? '#10b981' : ($day['is_declined'] ? '#ef4444' : 'transparent'))) }}; 
                    color: {{ ($day['is_today'] || $day['is_accepted'] || $day['is_declined'] || $day['is_cancelled']) ? 'white' : 'var(--text-main)' }}; 
                    border-radius: 50%; font-weight: 700; margin-top: 5px; position: relative;
                    box-shadow: {{ $day['is_today'] ? '0 4px 10px rgba(30, 58, 138, 0.3)' : ($day['is_cancelled'] ? '0 4px 10px rgba(239, 68, 68, 0.3)' : ($day['is_accepted'] ? '0 4px 10px rgba(16, 185, 129, 0.3)' : ($day['is_declined'] ? '0 4px 10px rgba(239, 68, 68, 0.3)' : 'none'))) }};
                    {{ $day['is_cancelled'] ? 'text-decoration: line-through;' : '' }}">
                    {{ $day['day_number'] }}
                    @if($day['has_trip'] && !$day['is_accepted'] && !$day['is_declined'] && !$day['is_cancelled'])
                        <div style="position: absolute; bottom: -6px; width: 4px; height: 4px; border-radius: 50%; background: var(--accent);"></div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <h3 style="font-family: 'Outfit', sans-serif; font-size: 1.1rem; margin-bottom: 15px; color: var(--text-main);">Upcoming Scheduled Trips</h3>
    
    <div style="display: flex; flex-direction: column; gap: 15px;">
        @forelse($scheduledTrips as $trip)
            <div class="card" style="padding: 20px; border-left: 5px solid {{ $trip->status === 'cancelled' ? '#ef4444' : 'var(--accent)' }};" x-data="{ showDetails: false }">
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div>
                        <div style="font-size: 0.85rem; font-weight: 700; color: var(--primary); margin-bottom: 5px;">
                            {{ $trip->scheduled_at->format('M d, Y @ h:i A') }}
                        </div>
                        <h4 style="font-family: 'Outfit', sans-serif; margin: 0 0 5px; color: var(--text-main);">{{ $trip->booking_reference }}</h4>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div style="font-size: 0.85rem; color: var(--text-muted);" x-data="{ openCustomers: false }">
                                <i class="fas fa-user" style="margin-right: 5px; color: var(--primary);"></i> 
                                @if(isset($trip->grouped_customers) && count($trip->grouped_customers) > 1)
                                    <span @click="openCustomers = !openCustomers" style="font-weight: 700; color: var(--primary); cursor: pointer;">
                                        {{ $trip->customer_name }} <i class="fas" :class="openCustomers ? 'fa-chevron-up' : 'fa-chevron-down'" style="font-size: 0.7rem;"></i>
                                    </span>
                                    <div x-show="openCustomers" x-transition x-cloak style="margin-top: 5px; background: var(--bg-card); border: 1px solid var(--border); border-radius: 8px; padding: 10px; min-width: 200px;">
                                        @foreach($trip->grouped_customers as $cust)
                                            <div style="padding: 5px 0; border-bottom: 1px solid var(--border); {{ $loop->last ? 'border-bottom: none; padding-bottom: 0;' : '' }} {{ $loop->first ? 'padding-top: 0;' : '' }}">
                                                <div style="font-weight: 700; font-size: 0.85rem; color: var(--text-main);">{{ $cust['name'] }}</div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    {{ $trip->customer_name }}
                                @endif
                            </div>
                            @if($trip->status === 'cancelled')
                                <span style="font-size: 0.65rem; font-weight: 800; color: #dc2626; background: #fef2f2; padding: 2px 8px; border-radius: 5px; text-transform: uppercase;">Cancelled</span>
                            @elseif($trip->driver_schedule_status === 'accepted')
                                <span style="font-size: 0.65rem; font-weight: 800; color: #059669; background: #ecfdf5; padding: 2px 8px; border-radius: 5px; text-transform: uppercase;">Accepted</span>
                            @elseif($trip->driver_schedule_status === 'declined')
                                <span style="font-size: 0.65rem; font-weight: 800; color: #dc2626; background: #fef2f2; padding: 2px 8px; border-radius: 5px; text-transform: uppercase;">Declined</span>
                            @else
                                <span style="font-size: 0.65rem; font-weight: 800; color: #d97706; background: #fff7ed; padding: 2px 8px; border-radius: 5px; text-transform: uppercase;">Action Required</span>
                            @endif
                        </div>
                    </div>
                    <button @click="showDetails = true" class="btn btn-outline btn-sm" style="width: auto;">View Details</button>
                </div>

                <!-- Trip Details Modal -->
                <div x-show="showDetails" x-cloak style="position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 9999; display: flex; align-items: center; justify-content: center; padding: 20px; backdrop-filter: blur(4px);">
                    <div @click.away="showDetails = false" class="card" style="width: 100%; max-width: 500px; max-height: 90vh; overflow-y: auto; margin-bottom: 0; padding: 30px; border-radius: 24px; box-shadow: 0 20px 50px rgba(0,0,0,0.3); border: 1px solid var(--border); background: var(--bg-card); scrollbar-width: thin;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                            <h3 style="font-family: 'Outfit', sans-serif; margin: 0; color: var(--text-main); font-size: 1.3rem;">Trip Assignment</h3>
                            <button @click="showDetails = false" style="background: var(--bg-main); border: none; width: 32px; height: 32px; border-radius: 50%; color: var(--text-muted); cursor: pointer;"><i class="fas fa-times"></i></button>
                        </div>

                        <div style="background: var(--bg-main); padding: 20px; border-radius: 18px; margin-bottom: 20px; border: 1px solid var(--border);">
                            <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 15px; border-bottom: 1px solid var(--border); padding-bottom: 15px;">
                                <div style="width: 45px; height: 45px; background: var(--primary); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.2rem;">
                                    {{ substr($trip->customer_name, 0, 1) }}
                                </div>
                                <div x-data="{ openCustomers: false }">
                                    <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">Customer</div>
                                    @if(isset($trip->grouped_customers) && count($trip->grouped_customers) > 1)
                                        <div @click="openCustomers = !openCustomers" style="font-weight: 800; color: var(--primary); font-size: 1.05rem; cursor: pointer; display: inline-flex; align-items: center; gap: 5px;">
                                            {{ $trip->customer_name }} <i class="fas" :class="openCustomers ? 'fa-chevron-up' : 'fa-chevron-down'" style="font-size: 0.7rem;"></i>
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
                                        <div style="font-weight: 800; color: var(--text-main); font-size: 1.05rem;">{{ $trip->customer_name }}</div>
                                    @endif
                                </div>
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                                <div>
                                    <div style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700; margin-bottom: 4px;">Reference</div>
                                    <div style="font-family: monospace; font-weight: 800; color: var(--primary);">{{ $trip->booking_reference }}</div>
                                </div>
                                <div>
                                    <div style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700; margin-bottom: 4px;">Schedule</div>
                                    <div style="font-weight: 700; color: var(--text-main); font-size: 0.9rem;">{{ $trip->scheduled_at->format('M d, h:i A') }}</div>
                                </div>
                                <div style="grid-column: span 2;">
                                    <div style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700; margin-bottom: 4px;">Service Details</div>
                                    <div style="font-weight: 600; color: var(--text-main); font-size: 0.9rem;">
                                        @php
                                            $item = $trip->items->first();
                                            $detail = 'Standard Service';
                                            if ($item) {
                                                if ($item->bookable_type === 'Modules\Fleet\Models\Vehicle') {
                                                    $detail = 'Car Hire: ' . $item->bookable->make . ' ' . $item->bookable->model;
                                                } elseif ($item->bookable_type === 'Modules\Fleet\Models\AirportTransfer') {
                                                    $detail = 'Transfer: ' . ($item->options['destination'] ?? $item->bookable->airport_name);
                                                } else {
                                                    $detail = 'Tour: ' . $item->bookable->title;
                                                }
                                            }
                                        @endphp
                                        {{ $detail }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if($trip->notes)
                        <div style="margin-bottom: 25px;">
                            <div style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700; margin-bottom: 8px;">Important Notes</div>
                            <div style="background: rgba(245,158,11,0.05); padding: 12px 15px; border-radius: 12px; border: 1px solid rgba(245,158,11,0.2); font-size: 0.85rem; color: var(--text-main); line-height: 1.5; font-style: italic;">
                                "{{ $trip->notes }}"
                            </div>
                        </div>
                        @endif

                        {{-- Shared Documents Section --}}
                        <div style="margin-bottom: 25px;">
                            <div style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700; margin-bottom: 12px;">Shared Documents</div>
                            <div style="display: flex; flex-direction: column; gap: 10px;">
                                @php
                                    $driverDocs = $trip->documents->filter(fn($d) => in_array($d->shared_with, ['driver', 'both']));
                                @endphp
                                @forelse($driverDocs as $doc)
                                <div style="display: flex; align-items: center; justify-content: space-between; background: var(--bg-main); padding: 12px 15px; border-radius: 12px; border: 1px solid var(--border);">
                                    <div style="display: flex; align-items: center; gap: 10px; overflow: hidden;">
                                        <i class="fas fa-file-{{ in_array($doc->file_type, ['jpg', 'png', 'jpeg']) ? 'image' : 'alt' }}" style="color: var(--primary); font-size: 1.1rem;"></i>
                                        <div style="overflow: hidden;">
                                            <div style="font-size: 0.85rem; font-weight: 700; color: var(--text-main); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $doc->title }}</div>
                                            <div style="font-size: 0.65rem; color: var(--text-muted); text-transform: uppercase;">{{ $doc->file_type }}</div>
                                        </div>
                                    </div>
                                    <a href="{{ route('bookings.documents.download', $doc) }}" class="btn btn-primary" style="width: 36px; height: 36px; padding: 0; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.8rem;">
                                        <i class="fas fa-download"></i>
                                    </a>
                                </div>
                                @empty
                                <div style="padding: 15px; background: var(--bg-main); border-radius: 12px; border: 1px dashed var(--border); text-align: center; color: var(--text-muted); font-size: 0.75rem;">
                                    No documents shared for this trip.
                                </div>
                                @endforelse
                            </div>
                        </div>

                        @if($trip->status === 'cancelled')
                            {{-- Cancellation Banner --}}
                            <div style="margin-top: 20px; border-top: 1px dashed var(--border); padding-top: 20px;">
                                <div style="padding: 20px; border-radius: 18px; background: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.2);">
                                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 15px;">
                                        <div style="width: 40px; height: 40px; background: #ef4444; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                            <i class="fas fa-ban" style="color: white; font-size: 1.1rem;"></i>
                                        </div>
                                        <div>
                                            <div style="font-weight: 800; color: #dc2626; font-size: 1rem; font-family: 'Outfit', sans-serif;">Booking Cancelled</div>
                                            <div style="font-size: 0.75rem; color: var(--text-muted);">This trip has been cancelled by the admin.</div>
                                        </div>
                                    </div>
                                    @if($trip->cancellation_reason)
                                    <div style="background: rgba(239, 68, 68, 0.05); padding: 15px; border-radius: 12px; border: 1px solid rgba(239, 68, 68, 0.1);">
                                        <div style="font-size: 0.7rem; color: #dc2626; text-transform: uppercase; font-weight: 700; margin-bottom: 6px;">Reason for Cancellation</div>
                                        <div style="font-size: 0.9rem; color: var(--text-main); line-height: 1.5; font-style: italic;">
                                            "{{ $trip->cancellation_reason }}"
                                        </div>
                                    </div>
                                    @endif
                                    <div style="margin-top: 12px; font-size: 0.8rem; color: var(--text-muted); display: flex; align-items: center; gap: 6px;">
                                        <i class="fas fa-info-circle"></i> You have been released from this assignment.
                                    </div>
                                </div>
                            </div>
                        @elseif($trip->trip_status === 'idle')
                            @if($trip->driver_schedule_status === 'pending')
                            <div x-data="{ showFeedback: false, loading: false }" style="margin-top: 20px; border-top: 1px dashed var(--border); padding-top: 20px;">
                                <div x-show="!showFeedback" style="display: flex; flex-direction: column; gap: 12px;">
                                    <form action="{{ route('driver.schedule.respond', $trip) }}" method="POST" @submit="loading = true" style="margin-bottom: 12px;">
                                        @csrf
                                        <input type="hidden" name="status" value="accepted">
                                        <button type="submit" class="btn btn-primary" :disabled="loading" style="width: 100%; gap: 10px; height: 50px; font-weight: 800;">
                                            <template x-if="!loading">
                                                <span><i class="fas fa-check-circle"></i> Accept Schedule</span>
                                            </template>
                                            <template x-if="loading">
                                                <span><i class="fas fa-spinner fa-spin"></i> Processing...</span>
                                            </template>
                                        </button>
                                    </form>
                                    <button @click="showFeedback = true" class="btn btn-outline" style="width: 100%; border-color: var(--danger); color: var(--danger); height: 50px; font-weight: 800;">
                                        <i class="fas fa-times-circle"></i> Decline / Conflict
                                    </button>
                                </div>

                                <div x-show="showFeedback" x-transition style="margin-top: 10px; background: rgba(239, 68, 68, 0.05); padding: 20px; border-radius: 18px; border: 1px solid rgba(239, 68, 68, 0.1);">
                                    <h4 style="font-family: 'Outfit', sans-serif; font-size: 0.9rem; margin-bottom: 12px; color: var(--danger);">Reason for declining?</h4>
                                    <form action="{{ route('driver.schedule.respond', $trip) }}" method="POST" @submit="loading = true">
                                        @csrf
                                        <input type="hidden" name="status" value="declined">
                                        <textarea name="feedback" placeholder="e.g. Too early, vehicle maintenance, personal conflict..." 
                                                  style="width: 100%; background: var(--bg-card); border: 1px solid var(--border); border-radius: 12px; padding: 12px; font-size: 0.85rem; min-height: 80px; margin-bottom: 15px;" required></textarea>
                                        <div style="display: flex; gap: 10px;">
                                            <button type="submit" class="btn" :disabled="loading" style="flex: 1; background: var(--danger); color: white; height: 45px; font-weight: 700;">
                                                <span x-show="!loading">Send Feedback</span>
                                                <span x-show="loading"><i class="fas fa-spinner fa-spin"></i> Sending...</span>
                                            </button>
                                            <button type="button" @click="showFeedback = false" class="btn btn-secondary" style="flex: 1; height: 45px;">Cancel</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            @else
                            <div style="margin-top: 20px; padding: 20px; border-radius: 18px; background: {{ $trip->driver_schedule_status === 'accepted' ? 'rgba(16, 185, 129, 0.1)' : 'rgba(239, 68, 68, 0.1)' }}; display: flex; align-items: center; justify-content: center; gap: 10px; color: {{ $trip->driver_schedule_status === 'accepted' ? '#059669' : '#dc2626' }}; font-weight: 800; font-size: 1rem; border: 1px solid {{ $trip->driver_schedule_status === 'accepted' ? 'rgba(16, 185, 129, 0.2)' : 'rgba(239, 68, 68, 0.2)' }};">
                                <i class="fas fa-{{ $trip->driver_schedule_status === 'accepted' ? 'check-circle' : 'exclamation-circle' }}" style="font-size: 1.2rem;"></i>
                                <span>Schedule {{ ucfirst($trip->driver_schedule_status) }}</span>
                            </div>
                            @endif
                        @endif

                        {{-- Return Trip Schedule Section --}}
                        @if($trip->isTourismBooking() && ($trip->trip_leg === 'return' || $trip->trip_status === 'in_progress') && $trip->return_scheduled_at)
                        <div style="margin-top: 20px; padding-top: 20px; border-top: 1px dashed var(--border);">
                            <div style="font-size: 0.75rem; font-weight: 800; color: #3b82f6; text-transform: uppercase; margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
                                <i class="fas fa-plane-arrival"></i> Return Trip — {{ $trip->return_scheduled_at->format('M d, Y @ h:i A') }}
                            </div>

                            @if($trip->return_driver_schedule_status === 'pending')
                            <div x-data="{ showReturnFeedback: false, retLoading: false }" style="display: flex; flex-direction: column; gap: 12px;">
                                <div x-show="!showReturnFeedback" style="display: flex; flex-direction: column; gap: 12px;">
                                    <form action="{{ route('driver.schedule.return-respond', $trip) }}" method="POST" @submit="retLoading = true" style="margin-bottom: 12px;">
                                        @csrf
                                        <input type="hidden" name="status" value="accepted">
                                        <button type="submit" class="btn" :disabled="retLoading" style="width: 100%; height: 45px; font-weight: 800; background: #3b82f6; color: white; border: none; border-radius: 12px;">
                                            <span x-show="!retLoading"><i class="fas fa-check-circle"></i> Accept Return Schedule</span>
                                            <span x-show="retLoading"><i class="fas fa-spinner fa-spin"></i> Processing...</span>
                                        </button>
                                    </form>
                                    <button @click="showReturnFeedback = true" class="btn btn-outline" style="width: 100%; border-color: var(--danger); color: var(--danger); height: 45px; font-weight: 800; border-radius: 12px;">
                                        <i class="fas fa-times-circle"></i> Decline Return
                                    </button>
                                </div>
                                <div x-show="showReturnFeedback" x-transition style="background: rgba(239, 68, 68, 0.05); padding: 15px; border-radius: 14px; border: 1px solid rgba(239, 68, 68, 0.1);">
                                    <form action="{{ route('driver.schedule.return-respond', $trip) }}" method="POST" @submit="retLoading = true">
                                        @csrf
                                        <input type="hidden" name="status" value="declined">
                                        <textarea name="feedback" placeholder="Reason for declining return..." style="width: 100%; background: var(--bg-card); border: 1px solid var(--border); border-radius: 10px; padding: 10px; font-size: 0.85rem; min-height: 60px; margin-bottom: 10px;" required></textarea>
                                        <div style="display: flex; gap: 10px;">
                                            <button type="submit" class="btn" :disabled="retLoading" style="flex: 1; background: var(--danger); color: white; height: 40px; font-weight: 700; border: none; border-radius: 10px;">Send</button>
                                            <button type="button" @click="showReturnFeedback = false" class="btn btn-secondary" style="flex: 1; height: 40px; border-radius: 10px;">Cancel</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            @else
                            <div style="padding: 15px; border-radius: 14px; background: {{ $trip->return_driver_schedule_status === 'accepted' ? 'rgba(16, 185, 129, 0.1)' : 'rgba(239, 68, 68, 0.1)' }}; display: flex; align-items: center; justify-content: center; gap: 8px; color: {{ $trip->return_driver_schedule_status === 'accepted' ? '#059669' : '#dc2626' }}; font-weight: 800; font-size: 0.9rem; border: 1px solid {{ $trip->return_driver_schedule_status === 'accepted' ? 'rgba(16, 185, 129, 0.2)' : 'rgba(239, 68, 68, 0.2)' }};">
                                <i class="fas fa-{{ $trip->return_driver_schedule_status === 'accepted' ? 'check-circle' : 'exclamation-circle' }}"></i>
                                Return {{ ucfirst($trip->return_driver_schedule_status) }}
                            </div>
                            @endif
                        </div>
                        @endif

                        <div style="display: flex; gap: 12px; margin-top: 30px; padding-top: 20px; border-top: 1px solid var(--border);">
                            <a href="tel:{{ $trip->customer_phone }}" class="btn btn-secondary" style="flex: 1; text-decoration: none; text-align: center; display: flex; align-items: center; justify-content: center; gap: 8px; height: 45px;">
                                <i class="fas fa-phone"></i> Call Customer
                            </a>
                            <button @click="showDetails = false" class="btn btn-outline" style="flex: 1; height: 45px;">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="card" style="text-align: center; padding: 40px 20px; color: var(--text-muted);">
                <i class="far fa-calendar-alt" style="font-size: 2.5rem; margin-bottom: 15px; opacity: 0.5;"></i>
                <p>No upcoming trips scheduled for you yet.</p>
            </div>
        @endforelse
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('driverSchedule', () => ({
            // State for schedule
        }));
    });
</script>
@endpush
@endsection
