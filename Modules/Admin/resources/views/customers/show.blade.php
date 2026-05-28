@extends('admin::layouts.master')

@section('title', $customer->name . ' — Customer Profile')

@section('content')
<div class="page-header">
    <div class="page-title">
        <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 10px;">
            <a href="{{ route('admin.customers.index') }}" style="color: var(--text-muted); text-decoration: none; font-size: 0.9rem; transition: 0.2s;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='var(--text-muted)'">
                <i class="fas fa-arrow-left"></i> Back to Customer Directory
            </a>
        </div>
        <h1><i class="fas fa-user-circle" style="color: var(--primary); margin-right: 10px;"></i>{{ $customer->name }}</h1>
        <p>Full customer profile, booking history, and account analytics.</p>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px; margin-top: 25px;">
    {{-- Left Column --}}
    <div style="display: flex; flex-direction: column; gap: 30px;">

        {{-- Profile Header Card --}}
        <div class="card" style="padding: 35px; background: var(--bg-card); border-radius: 24px; box-shadow: var(--shadow-sm); border: 1px solid var(--border);">
            <div style="display: flex; align-items: center; gap: 25px; flex-wrap: wrap;">
                <div style="width: 90px; height: 90px; background: linear-gradient(135deg, var(--primary), #6366f1); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; font-weight: 800; flex-shrink: 0; box-shadow: 0 8px 25px rgba(37, 99, 235, 0.25);">
                    {{ strtoupper(substr($customer->name, 0, 1)) }}
                </div>
                <div style="flex: 1; min-width: 200px;">
                    <h2 style="font-family: 'Outfit', sans-serif; margin: 0 0 5px; font-size: 1.5rem; color: var(--text-main);">{{ $customer->name }}</h2>
                    <div style="display: flex; flex-wrap: wrap; gap: 8px; align-items: center; margin-bottom: 8px;">
                        @foreach($customer->roles as $role)
                            <span style="font-size: 0.7rem; font-weight: 800; text-transform: uppercase; padding: 3px 10px; border-radius: 8px; background: rgba(139, 92, 246, 0.1); color: #8b5cf6; border: 1px solid rgba(139, 92, 246, 0.2);">
                                {{ $role->name }}
                            </span>
                        @endforeach
                        @php
                            $status = $customer->status ?? 'active';
                            $statusColors = [
                                'active' => ['bg' => '#dcfce7', 'text' => '#166534'],
                                'inactive' => ['bg' => '#f3f4f6', 'text' => '#6b7280'],
                                'suspended' => ['bg' => '#fee2e2', 'text' => '#991b1b'],
                            ];
                            $sc = $statusColors[$status] ?? $statusColors['active'];
                        @endphp
                        <span style="font-size: 0.7rem; font-weight: 800; text-transform: uppercase; padding: 3px 10px; border-radius: 8px; background: {{ $sc['bg'] }}; color: {{ $sc['text'] }};">
                            {{ ucfirst($status) }}
                        </span>
                    </div>
                    <div style="display: flex; gap: 20px; flex-wrap: wrap; font-size: 0.85rem; color: var(--text-muted);">
                        <span><i class="fas fa-envelope" style="color: var(--primary); margin-right: 5px;"></i>{{ $customer->email }}</span>
                        <span><i class="fas fa-phone-alt" style="color: var(--primary); margin-right: 5px;"></i>{{ $customer->phone ?? 'N/A' }}</span>
                        <span><i class="fas fa-calendar-alt" style="color: var(--primary); margin-right: 5px;"></i>Member since {{ $customer->created_at->format('M Y') }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Personal Details Card --}}
        <div class="card" style="padding: 30px; background: var(--bg-card); border-radius: 24px; box-shadow: var(--shadow-sm); border: 1px solid var(--border);">
            <h3 style="font-family: 'Outfit', sans-serif; margin-bottom: 25px; display: flex; align-items: center; gap: 10px; color: var(--text-main);">
                <i class="fas fa-id-card" style="color: var(--accent);"></i> Personal Details
            </h3>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div style="padding: 15px; background: var(--bg-main); border-radius: 12px; border: 1px solid var(--border);">
                    <div style="font-size: 0.7rem; color: var(--text-muted); font-weight: 800; text-transform: uppercase; margin-bottom: 5px;">Address</div>
                    <div style="font-weight: 600; color: var(--text-main); font-size: 0.9rem;">{{ $customer->address ?: 'Not provided' }}</div>
                </div>
                <div style="padding: 15px; background: var(--bg-main); border-radius: 12px; border: 1px solid var(--border);">
                    <div style="font-size: 0.7rem; color: var(--text-muted); font-weight: 800; text-transform: uppercase; margin-bottom: 5px;">Nationality</div>
                    <div style="font-weight: 600; color: var(--text-main); font-size: 0.9rem;">{{ $customer->nationality ?: 'Not provided' }}</div>
                </div>
                <div style="padding: 15px; background: var(--bg-main); border-radius: 12px; border: 1px solid var(--border);">
                    <div style="font-size: 0.7rem; color: var(--text-muted); font-weight: 800; text-transform: uppercase; margin-bottom: 5px;">Emergency Contact</div>
                    <div style="font-weight: 600; color: var(--text-main); font-size: 0.9rem;">{{ $customer->emergency_contact ?: 'Not provided' }}</div>
                </div>
                <div style="padding: 15px; background: var(--bg-main); border-radius: 12px; border: 1px solid var(--border);">
                    <div style="font-size: 0.7rem; color: var(--text-muted); font-weight: 800; text-transform: uppercase; margin-bottom: 5px;">Date of Birth</div>
                    <div style="font-weight: 600; color: var(--text-main); font-size: 0.9rem;">{{ $customer->dob ? $customer->dob->format('M d, Y') : 'Not provided' }}</div>
                </div>
                <div style="padding: 15px; background: var(--bg-main); border-radius: 12px; border: 1px solid var(--border);">
                    <div style="font-size: 0.7rem; color: var(--text-muted); font-weight: 800; text-transform: uppercase; margin-bottom: 5px;">ID Document</div>
                    <div style="font-weight: 600; color: var(--text-main); font-size: 0.9rem;">{{ $customer->id_document ?: 'Not provided' }}</div>
                </div>
                <div style="padding: 15px; background: var(--bg-main); border-radius: 12px; border: 1px solid var(--border);">
                    <div style="font-size: 0.7rem; color: var(--text-muted); font-weight: 800; text-transform: uppercase; margin-bottom: 5px;">Travel Preferences</div>
                    <div style="font-weight: 600; color: var(--text-main); font-size: 0.9rem;">
                        @if($customer->travel_preferences)
                            {{ is_array($customer->travel_preferences) ? implode(', ', $customer->travel_preferences) : $customer->travel_preferences }}
                        @else
                            Not specified
                        @endif
                    </div>
                </div>
            </div>
            @if($customer->bio)
            <div style="margin-top: 20px; padding: 15px; background: var(--bg-main); border-radius: 12px; border: 1px solid var(--border);">
                <div style="font-size: 0.7rem; color: var(--text-muted); font-weight: 800; text-transform: uppercase; margin-bottom: 5px;">Bio</div>
                <div style="font-weight: 500; color: var(--text-main); font-size: 0.9rem; line-height: 1.6; font-style: italic;">{{ $customer->bio }}</div>
            </div>
            @endif
        </div>

        {{-- Full Trip History --}}
        <div class="card" style="padding: 30px; background: var(--bg-card); border-radius: 24px; box-shadow: var(--shadow-sm); border: 1px solid var(--border);">
            <h3 style="font-family: 'Outfit', sans-serif; margin-bottom: 25px; display: flex; align-items: center; gap: 10px; color: var(--text-main);">
                <i class="fas fa-history" style="color: var(--primary);"></i> Complete Trip History
                <span style="font-size: 0.75rem; background: rgba(37, 99, 235, 0.1); color: var(--primary); padding: 3px 10px; border-radius: 20px; font-weight: 800;">{{ $bookings->count() }} Trips</span>
            </h3>
            <div class="table-container">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="text-align: left; border-bottom: 2px solid var(--border); color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase; font-weight: 800;">
                            <th style="padding: 12px 0;">Reference</th>
                            <th style="padding: 12px 0;">Service</th>
                            <th style="padding: 12px 0;">Status</th>
                            <th style="padding: 12px 0;">Amount</th>
                            <th style="padding: 12px 0;">Chauffeur</th>
                            <th style="padding: 12px 0;">Date</th>
                            <th style="padding: 12px 0;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bookings as $booking)
                            <tr style="border-bottom: 1px solid rgba(0,0,0,0.03); transition: background 0.2s;" onmouseover="this.style.background='var(--bg-main)'" onmouseout="this.style.background='transparent'">
                                <td style="padding: 14px 0;">
                                    <span style="font-weight: 800; color: var(--primary); font-family: monospace; font-size: 0.85rem;">{{ $booking->booking_reference }}</span>
                                    @if($booking->payment_status === 'paid')
                                        <i class="fas fa-star" style="color: #eab308; font-size: 0.7rem; margin-left: 3px;" title="Fully Paid"></i>
                                    @endif
                                </td>
                                <td style="padding: 14px 0;">
                                    @php
                                        $firstItem = $booking->items->first();
                                        $typeLabel = 'Service';
                                        $typeColor = '#64748b';
                                        $typeIcon = 'fas fa-concierge-bell';
                                        if ($firstItem) {
                                            if ($firstItem->bookable_type === 'Modules\Tourism\Models\TourismPackage') {
                                                $typeLabel = $firstItem->bookable->package_type === 'fixed' ? 'Fixed Tour' : 'Organized Tour';
                                                $typeColor = '#0ea5e9';
                                                $typeIcon = 'fas fa-umbrella-beach';
                                            } elseif ($firstItem->bookable_type === 'Modules\Fleet\Models\AirportTransfer') {
                                                $typeLabel = ($firstItem->bookable->category ?? 'airport') === 'airport' ? 'Airport Transfer' : 'General Transfer';
                                                $typeColor = '#10b981';
                                                $typeIcon = 'fas fa-plane';
                                            } else {
                                                $typeLabel = 'Car Hiring';
                                                $typeColor = '#f59e0b';
                                                $typeIcon = 'fas fa-car';
                                            }
                                        }
                                    @endphp
                                    <span style="font-size: 0.7rem; font-weight: 800; text-transform: uppercase; padding: 3px 10px; border-radius: 8px; background: {{ $typeColor }}15; color: {{ $typeColor }}; display: inline-flex; align-items: center; gap: 5px;">
                                        <i class="{{ $typeIcon }}"></i> {{ $typeLabel }}
                                    </span>
                                </td>
                                <td style="padding: 14px 0;">
                                    @php
                                        $statusColors = [
                                            'confirmed' => ['bg' => '#dcfce7', 'text' => '#166534'],
                                            'pending' => ['bg' => '#fef9c3', 'text' => '#854d0e'],
                                            'completed' => ['bg' => '#dbeafe', 'text' => '#1e40af'],
                                            'cancelled' => ['bg' => '#fee2e2', 'text' => '#991b1b'],
                                        ];
                                        $bsc = $statusColors[$booking->status] ?? ['bg' => '#f3f4f6', 'text' => '#6b7280'];
                                    @endphp
                                    <span style="padding: 3px 10px; border-radius: 20px; font-size: 0.7rem; font-weight: 700; background: {{ $bsc['bg'] }}; color: {{ $bsc['text'] }};">
                                        {{ ucfirst($booking->status) }}
                                    </span>
                                    @if($booking->trip_status && $booking->trip_status !== 'idle')
                                        <div style="font-size: 0.65rem; color: {{ $booking->trip_status === 'completed' ? '#10b981' : '#f59e0b' }}; font-weight: 700; margin-top: 3px;">
                                            <i class="fas {{ $booking->trip_status === 'completed' ? 'fa-flag-checkered' : 'fa-spinner fa-spin' }}"></i>
                                            {{ ucfirst(str_replace('_', ' ', $booking->trip_status)) }}
                                        </div>
                                    @endif
                                </td>
                                <td style="padding: 14px 0; font-weight: 800; color: var(--primary);">₵{{ number_format($booking->total_amount, 2) }}</td>
                                <td style="padding: 14px 0; font-size: 0.85rem; color: var(--text-main);">
                                    @if($booking->chauffeur)
                                        <span style="font-weight: 600;">{{ $booking->chauffeur->user->name ?? 'N/A' }}</span>
                                    @elseif($booking->is_self_drive)
                                        <span style="color: var(--text-muted); font-style: italic;">Self Drive</span>
                                    @else
                                        <span style="color: var(--text-muted);">—</span>
                                    @endif
                                </td>
                                <td style="padding: 14px 0; font-size: 0.8rem; color: var(--text-muted);">{{ $booking->created_at->format('M d, Y') }}</td>
                                <td style="padding: 14px 0;">
                                    <a href="{{ route('admin.bookings.show', $booking) }}" style="color: var(--primary); font-size: 0.95rem; transition: 0.2s;" title="View Booking">
                                        <i class="fas fa-external-link-alt"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="padding: 40px 0; text-align: center; color: var(--text-muted);">
                                    <i class="fas fa-inbox" style="font-size: 1.5rem; margin-bottom: 10px; display: block; opacity: 0.3;"></i>
                                    No bookings found for this customer.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Right Column --}}
    <div style="display: flex; flex-direction: column; gap: 30px;">

        {{-- Customer Stats Card --}}
        <div class="card" style="padding: 30px; background: var(--bg-card); border-radius: 24px; box-shadow: var(--shadow-sm); border: 1px solid var(--border);">
            <h3 style="font-family: 'Outfit', sans-serif; margin-bottom: 20px; color: var(--text-main);">Customer Analytics</h3>
            <div style="display: flex; flex-direction: column; gap: 15px;">
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 15px; background: var(--bg-main); border-radius: 12px; border: 1px solid var(--border);">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <div style="width: 40px; height: 40px; background: rgba(37, 99, 235, 0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: var(--primary);"><i class="fas fa-ticket-alt"></i></div>
                        <span style="font-size: 0.85rem; color: var(--text-muted); font-weight: 600;">Total Bookings</span>
                    </div>
                    <span style="font-size: 1.3rem; font-weight: 900; color: var(--primary);">{{ $stats['total_bookings'] }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 15px; background: var(--bg-main); border-radius: 12px; border: 1px solid var(--border);">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <div style="width: 40px; height: 40px; background: rgba(16, 185, 129, 0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #10b981;"><i class="fas fa-flag-checkered"></i></div>
                        <span style="font-size: 0.85rem; color: var(--text-muted); font-weight: 600;">Completed Trips</span>
                    </div>
                    <span style="font-size: 1.3rem; font-weight: 900; color: #10b981;">{{ $stats['completed_trips'] }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 15px; background: var(--bg-main); border-radius: 12px; border: 1px solid var(--border);">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <div style="width: 40px; height: 40px; background: rgba(245, 158, 11, 0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #f59e0b;"><i class="fas fa-coins"></i></div>
                        <span style="font-size: 0.85rem; color: var(--text-muted); font-weight: 600;">Total Spend</span>
                    </div>
                    <span style="font-size: 1.3rem; font-weight: 900; color: #f59e0b;">₵{{ number_format($stats['total_spend'], 2) }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 15px; background: var(--bg-main); border-radius: 12px; border: 1px solid var(--border);">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <div style="width: 40px; height: 40px; background: rgba(234, 179, 8, 0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #eab308;"><i class="fas fa-star"></i></div>
                        <span style="font-size: 0.85rem; color: var(--text-muted); font-weight: 600;">Avg Rating Given</span>
                    </div>
                    <span style="font-size: 1.3rem; font-weight: 900; color: #eab308;">{{ $stats['avg_rating_given'] ? number_format($stats['avg_rating_given'], 1) : 'N/A' }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 15px; background: var(--bg-main); border-radius: 12px; border: 1px solid var(--border);">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <div style="width: 40px; height: 40px; background: rgba(239, 68, 68, 0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #ef4444;"><i class="fas fa-times-circle"></i></div>
                        <span style="font-size: 0.85rem; color: var(--text-muted); font-weight: 600;">Cancellation Rate</span>
                    </div>
                    <span style="font-size: 1.3rem; font-weight: 900; color: {{ $stats['cancellation_rate'] > 20 ? '#ef4444' : 'var(--text-main)' }};">{{ $stats['cancellation_rate'] }}%</span>
                </div>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="card" style="padding: 30px; background: var(--bg-card); border-radius: 24px; box-shadow: var(--shadow-sm); border: 1px solid var(--border);">
            <h3 style="font-family: 'Outfit', sans-serif; margin-bottom: 20px; color: var(--text-main);">Quick Actions</h3>
            <div style="display: flex; flex-direction: column; gap: 10px;">
                <a href="mailto:{{ $customer->email }}" class="btn btn-secondary" style="width: 100%; display: block; text-align: center; text-decoration: none; border-radius: 12px; padding: 12px;">
                    <i class="fas fa-envelope" style="margin-right: 8px;"></i> Send Email
                </a>
                @if($customer->phone)
                <a href="tel:{{ $customer->phone }}" class="btn btn-secondary" style="width: 100%; display: block; text-align: center; text-decoration: none; border-radius: 12px; padding: 12px;">
                    <i class="fas fa-phone-alt" style="margin-right: 8px;"></i> Call Customer
                </a>
                @endif
                <a href="{{ route('admin.users.edit', $customer->id) }}" class="btn btn-primary" style="width: 100%; display: block; text-align: center; text-decoration: none; border-radius: 12px; padding: 12px;">
                    <i class="fas fa-user-edit" style="margin-right: 8px;"></i> Edit Account
                </a>
            </div>
        </div>

        {{-- Recent Activity --}}
        <div class="card" style="padding: 30px; background: var(--bg-card); border-radius: 24px; box-shadow: var(--shadow-sm); border: 1px solid var(--border);">
            <h3 style="font-family: 'Outfit', sans-serif; margin-bottom: 20px; color: var(--text-main);">Recent Activity</h3>
            <div style="display: flex; flex-direction: column; gap: 12px;">
                @forelse($recentBookings as $recent)
                    <a href="{{ route('admin.bookings.show', $recent) }}" style="display: flex; align-items: center; gap: 12px; padding: 12px; background: var(--bg-main); border-radius: 12px; border: 1px solid var(--border); text-decoration: none; transition: all 0.2s;" onmouseover="this.style.borderColor='var(--primary)'" onmouseout="this.style.borderColor='var(--border)'">
                        @php
                            $iconColor = $recent->status === 'completed' ? '#10b981' : ($recent->status === 'confirmed' ? '#3b82f6' : ($recent->status === 'cancelled' ? '#ef4444' : '#f59e0b'));
                        @endphp
                        <div style="width: 36px; height: 36px; background: {{ $iconColor }}15; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: {{ $iconColor }}; flex-shrink: 0;">
                            <i class="fas {{ $recent->status === 'completed' ? 'fa-check' : ($recent->status === 'cancelled' ? 'fa-times' : 'fa-clock') }}"></i>
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <div style="font-weight: 700; font-size: 0.85rem; color: var(--text-main);">{{ $recent->booking_reference }}</div>
                            <div style="font-size: 0.75rem; color: var(--text-muted);">{{ ucfirst($recent->status) }} · {{ $recent->created_at->diffForHumans() }}</div>
                        </div>
                        <div style="font-weight: 800; font-size: 0.85rem; color: var(--primary); flex-shrink: 0;">₵{{ number_format($recent->total_amount, 2) }}</div>
                    </a>
                @empty
                    <div style="text-align: center; padding: 20px; color: var(--text-muted); font-size: 0.85rem;">
                        No recent activity.
                    </div>
                @endforelse
            </div>
        </div>

    </div>
</div>

@endsection

@push('styles')
<style>
    @media (max-width: 768px) {
        div[style*="grid-template-columns: 2fr 1fr"] {
            grid-template-columns: 1fr !important;
        }
    }
</style>
@endpush
