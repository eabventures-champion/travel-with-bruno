@extends('admin::layouts.master')

@section('title', ($chauffeur->user->name ?? 'Chauffeur') . ' — Chauffeur Profile')

@section('content')
<div class="page-header">
    <div class="page-title">
        <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 10px;">
            <a href="{{ route('admin.chauffeur-management.index') }}" style="color: var(--text-muted); text-decoration: none; font-size: 0.9rem; transition: 0.2s;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='var(--text-muted)'">
                <i class="fas fa-arrow-left"></i> Back to Chauffeur Directory
            </a>
        </div>
        <h1><i class="fas fa-user-tie" style="color: var(--primary); margin-right: 10px;"></i>{{ $chauffeur->user->name ?? 'Chauffeur' }}</h1>
        <p>Full chauffeur profile, trip history, performance metrics, and customer reviews.</p>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px; margin-top: 25px;">
    {{-- Left Column --}}
    <div style="display: flex; flex-direction: column; gap: 30px;">

        {{-- Profile Header --}}
        <div class="card" style="padding: 35px; background: var(--bg-card); border-radius: 24px; box-shadow: var(--shadow-sm); border: 1px solid var(--border);">
            <div style="display: flex; align-items: center; gap: 25px; flex-wrap: wrap;">
                <div style="width: 90px; height: 90px; background: linear-gradient(135deg, #1e3a8a, #3b82f6); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; font-weight: 800; flex-shrink: 0; box-shadow: 0 8px 25px rgba(30, 58, 138, 0.25); position: relative;">
                    {{ strtoupper(substr($chauffeur->user->name ?? 'C', 0, 1)) }}
                    @if($chauffeur->is_online)
                        <div style="position: absolute; bottom: 2px; right: 2px; width: 18px; height: 18px; background: #10b981; border-radius: 50%; border: 3px solid var(--bg-card);"></div>
                    @endif
                </div>
                <div style="flex: 1; min-width: 200px;">
                    <h2 style="font-family: 'Outfit', sans-serif; margin: 0 0 5px; font-size: 1.5rem; color: var(--text-main);">{{ $chauffeur->user->name ?? 'Unknown' }}</h2>
                    <div style="display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 8px;">
                        @php
                            $csc = [
                                'available' => ['bg' => '#dcfce7', 'text' => '#166534'],
                                'engaged' => ['bg' => '#fef9c3', 'text' => '#854d0e'],
                                'schedule_accepted' => ['bg' => '#e0e7ff', 'text' => '#3730a3'],
                                'offline' => ['bg' => '#f3f4f6', 'text' => '#6b7280'],
                            ];
                            $sc = $csc[$chauffeur->status] ?? $csc['offline'];
                        @endphp
                        <span style="font-size: 0.7rem; font-weight: 800; text-transform: uppercase; padding: 3px 10px; border-radius: 8px; background: {{ $sc['bg'] }}; color: {{ $sc['text'] }};">
                            {{ ucfirst(str_replace('_', ' ', $chauffeur->status)) }}
                        </span>
                        <span style="font-size: 0.7rem; font-weight: 800; text-transform: uppercase; padding: 3px 10px; border-radius: 8px; background: {{ $chauffeur->is_online ? 'rgba(16,185,129,0.1)' : 'rgba(156,163,175,0.1)' }}; color: {{ $chauffeur->is_online ? '#10b981' : '#9ca3af' }};">
                            <i class="fas fa-circle" style="font-size: 0.4rem; vertical-align: middle; margin-right: 3px;"></i>
                            {{ $chauffeur->is_online ? 'Online' : 'Offline' }}
                        </span>
                    </div>
                    <div style="display: flex; gap: 20px; flex-wrap: wrap; font-size: 0.85rem; color: var(--text-muted);">
                        <span><i class="fas fa-envelope" style="color: var(--primary); margin-right: 5px;"></i>{{ $chauffeur->user->email ?? 'N/A' }}</span>
                        <span><i class="fas fa-phone-alt" style="color: var(--primary); margin-right: 5px;"></i>{{ $chauffeur->user->phone ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>
            @if($chauffeur->bio)
                <div style="margin-top: 20px; padding: 15px; background: var(--bg-main); border-radius: 12px; border: 1px solid var(--border); font-size: 0.9rem; color: var(--text-main); line-height: 1.6; font-style: italic;">
                    "{{ $chauffeur->bio }}"
                </div>
            @endif
        </div>

        {{-- License & Credentials --}}
        <div class="card" style="padding: 30px; background: var(--bg-card); border-radius: 24px; box-shadow: var(--shadow-sm); border: 1px solid var(--border);">
            <h3 style="font-family: 'Outfit', sans-serif; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; color: var(--text-main);">
                <i class="fas fa-id-card" style="color: var(--accent);"></i> License & Credentials
            </h3>
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; margin-bottom: 25px;">
                <div style="padding: 20px; background: var(--bg-main); border-radius: 12px; border: 1px solid var(--border); text-align: center;">
                    <div style="font-size: 0.7rem; color: var(--text-muted); font-weight: 800; text-transform: uppercase; margin-bottom: 8px;">License Number</div>
                    <div style="font-weight: 800; color: var(--primary); font-family: monospace; font-size: 1.1rem; letter-spacing: 1px;">{{ $chauffeur->license_number ?? 'N/A' }}</div>
                </div>
                <div style="padding: 20px; background: var(--bg-main); border-radius: 12px; border: 1px solid var(--border); text-align: center;">
                    <div style="font-size: 0.7rem; color: var(--text-muted); font-weight: 800; text-transform: uppercase; margin-bottom: 8px;">License Expiry</div>
                    <div style="font-weight: 700; color: var(--text-main); font-size: 0.95rem;">{{ $chauffeur->license_expiry ? \Carbon\Carbon::parse($chauffeur->license_expiry)->format('M d, Y') : 'N/A' }}</div>
                </div>
                <div style="padding: 20px; background: var(--bg-main); border-radius: 12px; border: 1px solid var(--border); text-align: center;">
                    <div style="font-size: 0.7rem; color: var(--text-muted); font-weight: 800; text-transform: uppercase; margin-bottom: 8px;">Experience</div>
                    <div style="font-weight: 800; color: var(--primary); font-size: 1.3rem;">{{ $chauffeur->years_of_experience ?? 0 }} <span style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted);">years</span></div>
                </div>
            </div>

            <h4 style="font-family: 'Outfit', sans-serif; font-size: 0.95rem; margin-bottom: 15px; color: var(--text-main);">Document Verification</h4>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                {{-- License Scan --}}
                <div style="padding: 15px; background: var(--bg-main); border-radius: 15px; border: 1px solid var(--border);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                        <span style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted);">Driving License</span>
                        @if($chauffeur->license_verified_at)
                            <span style="font-size: 0.65rem; font-weight: 800; color: #10b981; text-transform: uppercase;"><i class="fas fa-check-circle"></i> Verified</span>
                        @else
                            <span style="font-size: 0.65rem; font-weight: 800; color: #f59e0b; text-transform: uppercase;"><i class="fas fa-clock"></i> Pending</span>
                        @endif
                    </div>
                    @if($chauffeur->license_front_path)
                        <a href="{{ asset('storage/' . $chauffeur->license_front_path) }}" target="_blank">
                            <img src="{{ asset('storage/' . $chauffeur->license_front_path) }}" style="width: 100%; height: 120px; object-fit: cover; border-radius: 8px; border: 1px solid var(--border); margin-bottom: 10px;">
                        </a>
                        @if(!$chauffeur->license_verified_at)
                            <form action="{{ route('admin.chauffeur-management.verify-document', $chauffeur->id) }}" method="POST">
                                @csrf
                                <input type="hidden" name="type" value="license">
                                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 8px; font-size: 0.8rem;">Approve License</button>
                            </form>
                        @endif
                    @else
                        <div style="height: 120px; background: var(--bg-card); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: var(--text-muted); font-size: 0.8rem; border: 1px dashed var(--border);">No scan uploaded</div>
                    @endif
                </div>

                {{-- ID Card Scan --}}
                <div style="padding: 15px; background: var(--bg-main); border-radius: 15px; border: 1px solid var(--border);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                        <span style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted);">National ID</span>
                        @if($chauffeur->id_verified_at)
                            <span style="font-size: 0.65rem; font-weight: 800; color: #10b981; text-transform: uppercase;"><i class="fas fa-check-circle"></i> Verified</span>
                        @else
                            <span style="font-size: 0.65rem; font-weight: 800; color: #f59e0b; text-transform: uppercase;"><i class="fas fa-clock"></i> Pending</span>
                        @endif
                    </div>
                    @if($chauffeur->id_card_path)
                        <a href="{{ asset('storage/' . $chauffeur->id_card_path) }}" target="_blank">
                            <img src="{{ asset('storage/' . $chauffeur->id_card_path) }}" style="width: 100%; height: 120px; object-fit: cover; border-radius: 8px; border: 1px solid var(--border); margin-bottom: 10px;">
                        </a>
                        @if(!$chauffeur->id_verified_at)
                            <form action="{{ route('admin.chauffeur-management.verify-document', $chauffeur->id) }}" method="POST">
                                @csrf
                                <input type="hidden" name="type" value="id_card">
                                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 8px; font-size: 0.8rem;">Approve ID Card</button>
                            </form>
                        @endif
                    @else
                        <div style="height: 120px; background: var(--bg-card); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: var(--text-muted); font-size: 0.8rem; border: 1px dashed var(--border);">No scan uploaded</div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Full Trip History --}}
        <div class="card" style="padding: 30px; background: var(--bg-card); border-radius: 24px; box-shadow: var(--shadow-sm); border: 1px solid var(--border);">
            <h3 style="font-family: 'Outfit', sans-serif; margin-bottom: 25px; display: flex; align-items: center; gap: 10px; color: var(--text-main);">
                <i class="fas fa-route" style="color: var(--primary);"></i> Assigned Trips History
                <span style="font-size: 0.75rem; background: rgba(37, 99, 235, 0.1); color: var(--primary); padding: 3px 10px; border-radius: 20px; font-weight: 800;">{{ $bookings->count() }} Trips</span>
            </h3>
            <div class="table-container">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="text-align: left; border-bottom: 2px solid var(--border); color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase; font-weight: 800;">
                            <th style="padding: 12px 0;">Reference</th>
                            <th style="padding: 12px 0;">Customer</th>
                            <th style="padding: 12px 0;">Service</th>
                            <th style="padding: 12px 0;">Status</th>
                            <th style="padding: 12px 0;">Duration</th>
                            <th style="padding: 12px 0;">Amount</th>
                            <th style="padding: 12px 0;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bookings as $booking)
                            <tr style="border-bottom: 1px solid rgba(0,0,0,0.03); transition: background 0.2s;" onmouseover="this.style.background='var(--bg-main)'" onmouseout="this.style.background='transparent'">
                                <td style="padding: 14px 0;">
                                    <span style="font-weight: 800; color: var(--primary); font-family: monospace; font-size: 0.85rem;">{{ $booking->booking_reference }}</span>
                                    <div style="font-size: 0.7rem; color: var(--text-muted); margin-top: 2px;">{{ $booking->created_at->format('M d, Y') }}</div>
                                </td>
                                <td style="padding: 14px 0;">
                                    <span style="font-weight: 600; color: var(--text-main); font-size: 0.9rem;">{{ $booking->customer_name ?: ($booking->user->name ?? 'Guest') }}</span>
                                </td>
                                <td style="padding: 14px 0;">
                                    @php
                                        $fi = $booking->items->first();
                                        $tl = 'Service'; $tc = '#64748b';
                                        if ($fi) {
                                            if ($fi->bookable_type === 'Modules\Tourism\Models\TourismPackage') { $tl = 'Tourism'; $tc = '#0ea5e9'; }
                                            elseif ($fi->bookable_type === 'Modules\Fleet\Models\AirportTransfer') { $tl = 'Transfer'; $tc = '#10b981'; }
                                            else { $tl = 'Car Hire'; $tc = '#f59e0b'; }
                                        }
                                    @endphp
                                    <span style="font-size: 0.7rem; font-weight: 800; text-transform: uppercase; padding: 3px 8px; border-radius: 6px; background: {{ $tc }}15; color: {{ $tc }};">{{ $tl }}</span>
                                </td>
                                <td style="padding: 14px 0;">
                                    @php
                                        $bColors = ['confirmed'=>['#dcfce7','#166534'],'pending'=>['#fef9c3','#854d0e'],'completed'=>['#dbeafe','#1e40af'],'cancelled'=>['#fee2e2','#991b1b']];
                                        $bc = $bColors[$booking->status] ?? ['#f3f4f6','#6b7280'];
                                    @endphp
                                    <span style="padding: 3px 10px; border-radius: 20px; font-size: 0.7rem; font-weight: 700; background: {{ $bc[0] }}; color: {{ $bc[1] }};">{{ ucfirst($booking->status) }}</span>
                                    @if($booking->trip_status)
                                        <div style="font-size: 0.65rem; color: {{ $booking->trip_status === 'completed' ? '#10b981' : ($booking->trip_status === 'idle' ? '#6366f1' : '#f59e0b') }}; font-weight: 700; margin-top: 3px;">
                                            <i class="fas {{ $booking->trip_status === 'completed' ? 'fa-check-circle' : ($booking->trip_status === 'idle' ? 'fa-clock' : 'fa-spinner fa-spin') }}"></i> {{ $booking->trip_status === 'idle' ? 'Scheduled' : ucfirst(str_replace('_', ' ', $booking->trip_status)) }}
                                        </div>
                                    @endif
                                </td>
                                <td style="padding: 14px 0; font-size: 0.85rem; color: var(--text-main); font-weight: 600;">{{ $booking->trip_duration ?? '—' }}</td>
                                <td style="padding: 14px 0; font-weight: 800; color: var(--primary);">₵{{ number_format($booking->total_amount, 2) }}</td>
                                <td style="padding: 14px 0;">
                                    <a href="{{ route('admin.bookings.show', $booking) }}" style="color: var(--primary); font-size: 0.95rem;" title="View Booking"><i class="fas fa-external-link-alt"></i></a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" style="padding: 40px 0; text-align: center; color: var(--text-muted);"><i class="fas fa-inbox" style="font-size: 1.5rem; margin-bottom: 10px; display: block; opacity: 0.3;"></i>No trips assigned yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Trip Reports --}}
        @if($tripReports->count() > 0)
        <div class="card" style="padding: 30px; background: var(--bg-card); border-radius: 24px; box-shadow: var(--shadow-sm); border: 1px solid var(--border);">
            <h3 style="font-family: 'Outfit', sans-serif; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; color: var(--text-main);">
                <i class="fas fa-exclamation-triangle" style="color: #ef4444;"></i> Trip Reports Filed
                <span style="font-size: 0.75rem; background: rgba(239, 68, 68, 0.1); color: #ef4444; padding: 3px 10px; border-radius: 20px; font-weight: 800;">{{ $tripReports->count() }}</span>
            </h3>
            <div style="display: flex; flex-direction: column; gap: 12px;">
                @foreach($tripReports as $report)
                    <div style="padding: 15px; background: var(--bg-main); border-radius: 12px; border: 1px solid var(--border); border-left: 4px solid #ef4444;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            <span style="font-weight: 700; color: var(--text-main); font-size: 0.9rem;">{{ ucfirst($report->type) }}</span>
                            <span style="font-size: 0.75rem; color: var(--text-muted);">{{ $report->created_at->diffForHumans() }}</span>
                        </div>
                        <p style="margin: 0; font-size: 0.85rem; color: var(--text-muted); line-height: 1.5;">{{ $report->description }}</p>
                        @if($report->booking)
                            <div style="margin-top: 8px;"><a href="{{ route('admin.bookings.show', $report->booking) }}" style="font-size: 0.75rem; color: var(--primary); text-decoration: none; font-weight: 600;">{{ $report->booking->booking_reference }} →</a></div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    {{-- Right Column --}}
    <div style="display: flex; flex-direction: column; gap: 30px;">

        {{-- Performance Stats --}}
        <div class="card" style="padding: 30px; background: var(--bg-card); border-radius: 24px; box-shadow: var(--shadow-sm); border: 1px solid var(--border);">
            <h3 style="font-family: 'Outfit', sans-serif; margin-bottom: 20px; color: var(--text-main);">Performance</h3>
            <div style="display: flex; flex-direction: column; gap: 12px;">
                @php
                    $perfItems = [
                        ['icon' => 'fa-route', 'color' => 'var(--primary)', 'label' => 'Total Trips', 'value' => $stats['total_trips']],
                        ['icon' => 'fa-flag-checkered', 'color' => '#10b981', 'label' => 'Completed', 'value' => $stats['completed_trips']],
                        ['icon' => 'fa-percentage', 'color' => '#8b5cf6', 'label' => 'Completion Rate', 'value' => $stats['completion_rate'] . '%'],
                        ['icon' => 'fa-spinner', 'color' => '#f59e0b', 'label' => 'Active Trips', 'value' => $stats['active_trips']],
                        ['icon' => 'fa-coins', 'color' => '#f59e0b', 'label' => 'Revenue Handled', 'value' => '₵' . number_format($stats['total_revenue'], 2)],
                    ];
                @endphp
                @foreach($perfItems as $pi)
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 15px; background: var(--bg-main); border-radius: 12px; border: 1px solid var(--border);">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <div style="width: 36px; height: 36px; background: {{ $pi['color'] }}15; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: {{ $pi['color'] }};"><i class="fas {{ $pi['icon'] }}"></i></div>
                        <span style="font-size: 0.85rem; color: var(--text-muted); font-weight: 600;">{{ $pi['label'] }}</span>
                    </div>
                    <span style="font-size: 1.1rem; font-weight: 900; color: var(--text-main);">{{ $pi['value'] }}</span>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Rating Card --}}
        <div class="card" style="padding: 30px; background: var(--bg-card); border-radius: 24px; box-shadow: var(--shadow-sm); border: 1px solid var(--border);">
            <h3 style="font-family: 'Outfit', sans-serif; margin-bottom: 20px; color: var(--text-main);">Rating Overview</h3>
            <div style="text-align: center; padding: 20px; background: var(--bg-main); border-radius: 15px; border: 1px solid var(--border); margin-bottom: 20px;">
                <div style="font-size: 3rem; font-weight: 900; color: #eab308; line-height: 1;">{{ $stats['avg_rating'] > 0 ? number_format($stats['avg_rating'], 1) : '—' }}</div>
                <div style="color: #eab308; margin: 8px 0;">
                    @for($i = 1; $i <= 5; $i++)
                        <i class="fas fa-star" style="{{ $i <= round($stats['avg_rating']) ? '' : 'color: var(--border);' }} font-size: 1.1rem;"></i>
                    @endfor
                </div>
                <div style="font-size: 0.85rem; color: var(--text-muted);">{{ $stats['total_ratings'] }} {{ Str::plural('review', $stats['total_ratings']) }}</div>
            </div>

            {{-- Rating Distribution --}}
            @if($stats['total_ratings'] > 0)
            <div style="display: flex; flex-direction: column; gap: 8px;">
                @foreach($ratingDistribution as $star => $data)
                <div style="display: flex; align-items: center; gap: 10px; font-size: 0.85rem;">
                    <span style="width: 20px; text-align: right; font-weight: 700; color: var(--text-main);">{{ $star }}</span>
                    <i class="fas fa-star" style="color: #eab308; font-size: 0.7rem;"></i>
                    <div style="flex: 1; height: 8px; background: var(--bg-main); border-radius: 4px; overflow: hidden; border: 1px solid var(--border);">
                        <div style="height: 100%; width: {{ $data['percentage'] }}%; background: linear-gradient(90deg, #eab308, #f59e0b); border-radius: 4px; transition: width 0.5s;"></div>
                    </div>
                    <span style="width: 30px; font-size: 0.75rem; color: var(--text-muted); font-weight: 600;">{{ $data['count'] }}</span>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Recent Reviews --}}
        <div class="card" style="padding: 30px; background: var(--bg-card); border-radius: 24px; box-shadow: var(--shadow-sm); border: 1px solid var(--border);">
            <h3 style="font-family: 'Outfit', sans-serif; margin-bottom: 20px; color: var(--text-main);">Recent Reviews</h3>
            <div style="display: flex; flex-direction: column; gap: 15px;">
                @forelse($recentRatings as $rating)
                    <div style="padding: 15px; background: var(--bg-main); border-radius: 12px; border: 1px solid var(--border);">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            <div style="color: #eab308;">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="fas fa-star" style="{{ $i <= $rating->rating ? '' : 'color: var(--border);' }} font-size: 0.8rem;"></i>
                                @endfor
                            </div>
                            <span style="font-size: 0.7rem; color: var(--text-muted);">{{ $rating->created_at->diffForHumans() }}</span>
                        </div>
                        <p style="margin: 0 0 8px; font-size: 0.85rem; color: var(--text-main); font-style: italic; line-height: 1.5;">"{{ $rating->comment ?: 'No comment provided.' }}"</p>
                        <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600;">
                            <i class="fas fa-user" style="margin-right: 3px;"></i> {{ $rating->user->name ?? 'Customer' }}
                        </div>
                    </div>
                @empty
                    <div style="text-align: center; padding: 20px; color: var(--text-muted); font-size: 0.85rem;">No reviews yet.</div>
                @endforelse
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="card" style="padding: 30px; background: var(--bg-card); border-radius: 24px; box-shadow: var(--shadow-sm); border: 1px solid var(--border);">
            <h3 style="font-family: 'Outfit', sans-serif; margin-bottom: 20px; color: var(--text-main);">Quick Actions</h3>
            <div style="display: flex; flex-direction: column; gap: 10px;">
                @if($chauffeur->user)
                <a href="mailto:{{ $chauffeur->user->email }}" class="btn btn-secondary" style="width: 100%; display: block; text-align: center; text-decoration: none; border-radius: 12px; padding: 12px;"><i class="fas fa-envelope" style="margin-right: 8px;"></i> Send Email</a>
                @if($chauffeur->user->phone)
                <a href="tel:{{ $chauffeur->user->phone }}" class="btn btn-secondary" style="width: 100%; display: block; text-align: center; text-decoration: none; border-radius: 12px; padding: 12px;"><i class="fas fa-phone-alt" style="margin-right: 8px;"></i> Call Chauffeur</a>
                @endif
                <a href="{{ route('admin.fleet.chauffeurs.edit', $chauffeur->id) }}" class="btn btn-primary" style="width: 100%; display: block; text-align: center; text-decoration: none; border-radius: 12px; padding: 12px;"><i class="fas fa-user-edit" style="margin-right: 8px;"></i> Edit Profile</a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    @media (max-width: 768px) {
        div[style*="grid-template-columns: 2fr 1fr"] { grid-template-columns: 1fr !important; }
        div[style*="grid-template-columns: 1fr 1fr 1fr"] { grid-template-columns: 1fr !important; }
    }
</style>
@endpush
