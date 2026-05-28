@extends('admin::layouts.master')

@section('title', 'Chauffeur Directory')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1><i class="fas fa-id-badge" style="color: var(--primary); margin-right: 10px;"></i>Chauffeur Directory</h1>
        <p>Complete overview of all chauffeurs, their performance, trip history, and current status.</p>
    </div>
</div>

{{-- Summary Stat Cards --}}
<div class="stats-grid" style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 15px; margin-bottom: 30px;">
    <div class="card stat-card" style="position: relative; overflow: hidden;">
        <div style="position: absolute; top: -15px; right: -15px; width: 70px; height: 70px; background: rgba(37, 99, 235, 0.08); border-radius: 50%;"></div>
        <span class="stat-label">Total Chauffeurs</span>
        <span class="stat-value">{{ $stats['total_chauffeurs'] }}</span>
        <span class="stat-trend"><i class="fas fa-user-tie" style="color: var(--primary);"></i> Registered</span>
    </div>
    <div class="card stat-card" style="position: relative; overflow: hidden;">
        <div style="position: absolute; top: -15px; right: -15px; width: 70px; height: 70px; background: rgba(16, 185, 129, 0.08); border-radius: 50%;"></div>
        <span class="stat-label">Currently Online</span>
        <span class="stat-value">{{ $stats['online_now'] }}</span>
        <span class="stat-trend trend-up"><i class="fas fa-wifi" style="color: #10b981;"></i> Active</span>
    </div>
    <div class="card stat-card" style="position: relative; overflow: hidden;">
        <div style="position: absolute; top: -15px; right: -15px; width: 70px; height: 70px; background: rgba(245, 158, 11, 0.08); border-radius: 50%;"></div>
        <span class="stat-label">Currently Engaged</span>
        <span class="stat-value">{{ $stats['engaged'] }}</span>
        <span class="stat-trend"><i class="fas fa-road" style="color: #f59e0b;"></i> On Trip</span>
    </div>
    <div class="card stat-card" style="position: relative; overflow: hidden;">
        <div style="position: absolute; top: -15px; right: -15px; width: 70px; height: 70px; background: rgba(99, 102, 241, 0.08); border-radius: 50%;"></div>
        <span class="stat-label">Schedule Accepted</span>
        <span class="stat-value">{{ $stats['schedule_accepted'] }}</span>
        <span class="stat-trend"><i class="fas fa-calendar-check" style="color: #6366f1;"></i> Awaiting Trip</span>
    </div>
    <div class="card stat-card" style="position: relative; overflow: hidden;">
        <div style="position: absolute; top: -15px; right: -15px; width: 70px; height: 70px; background: rgba(234, 179, 8, 0.08); border-radius: 50%;"></div>
        <span class="stat-label">Average Rating</span>
        <span class="stat-value">{{ $stats['avg_rating'] }}<i class="fas fa-star" style="font-size: 0.8rem; color: #eab308; margin-left: 5px;"></i></span>
        <span class="stat-trend"><i class="fas fa-star-half-alt" style="color: #eab308;"></i> Fleet-wide</span>
    </div>
</div>

<div x-data="{
    search: '',
    statusFilter: '',
    matches(name, email, status, isOnline) {
        const s = this.search.toLowerCase();
        const f = this.statusFilter.toLowerCase();
        const matchesSearch = s === '' || name.toLowerCase().includes(s) || email.toLowerCase().includes(s);
        let matchesStatus = true;
        if (f === 'online') matchesStatus = isOnline === '1';
        else if (f === 'offline') matchesStatus = isOnline === '0';
        else if (f !== '') matchesStatus = status.toLowerCase() === f;
        return matchesSearch && matchesStatus;
    }
}">
    {{-- Filter Bar --}}
    <div class="dashboard-card" style="margin-bottom: 25px; padding: 12px 20px !important; border: 1px solid var(--border); background: var(--bg-card);">
        <div style="display: flex; gap: 15px; align-items: center;">
            <div style="position: relative; flex: 1;">
                <i class="fas fa-search" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 0.9rem; pointer-events: none;"></i>
                <input type="text" x-model="search" placeholder="Search by name or email..." style="width: 100%; height: 45px; padding: 0 15px 0 42px; border-radius: 12px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main); font-size: 0.95rem; transition: all 0.3s ease;">
            </div>
            <div style="position: relative; flex: 0 0 220px;">
                <i class="fas fa-filter" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 0.9rem; pointer-events: none;"></i>
                <select x-model="statusFilter" style="width: 100%; height: 45px; padding: 0 15px 0 42px; border-radius: 12px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main); font-size: 0.95rem; cursor: pointer; appearance: none; background-image: url(&quot;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2364748b'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E&quot;); background-repeat: no-repeat; background-position: right 15px center; background-size: 15px;">
                    <option value="">All Chauffeurs</option>
                    <option value="available">Available</option>
                    <option value="engaged">Engaged</option>
                    <option value="schedule_accepted">Schedule Accepted</option>
                    <option value="online">Online</option>
                    <option value="offline">Offline</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Chauffeur Table --}}
    <div class="dashboard-card">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Chauffeur</th>
                        <th>Status</th>
                        <th>Active/Upcoming Trip</th>
                        <th>Total Trips</th>
                        <th>Rating</th>
                        <th>Online</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($chauffeurs as $chauffeur)
                        <tr x-show="matches('{{ addslashes($chauffeur->user->name ?? '') }}', '{{ addslashes($chauffeur->user->email ?? '') }}', '{{ $chauffeur->status }}', '{{ $chauffeur->is_online ? '1' : '0' }}')"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 transform scale-95"
                            x-transition:enter-end="opacity-100 transform scale-100">
                            <td>
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <div style="width: 42px; height: 42px; background: linear-gradient(135deg, #1e3a8a, #3b82f6); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1rem; flex-shrink: 0; position: relative;">
                                        {{ strtoupper(substr($chauffeur->user->name ?? 'C', 0, 1)) }}
                                        @if($chauffeur->is_online)
                                            <div style="position: absolute; bottom: -1px; right: -1px; width: 14px; height: 14px; background: #10b981; border-radius: 50%; border: 2px solid var(--bg-card);"></div>
                                        @endif
                                    </div>
                                    <div style="display: flex; flex-direction: column;">
                                        <span style="font-weight: 700; color: var(--text-main);">{{ $chauffeur->user->name ?? 'Unknown' }}</span>
                                        <span style="font-size: 0.8rem; color: var(--text-muted);">{{ $chauffeur->user->email ?? 'N/A' }}</span>
                                        @if($chauffeur->user->phone)
                                            <span style="font-size: 0.75rem; color: var(--text-muted);"><i class="fas fa-phone-alt" style="font-size: 0.65rem;"></i> {{ $chauffeur->user->phone }}</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                @php
                                    $chauffeurStatusColors = [
                                        'available' => ['bg' => '#dcfce7', 'text' => '#166534', 'dot' => '#10b981'],
                                        'engaged' => ['bg' => '#fef9c3', 'text' => '#854d0e', 'dot' => '#f59e0b'],
                                        'schedule_accepted' => ['bg' => '#e0e7ff', 'text' => '#3730a3', 'dot' => '#6366f1'],
                                        'offline' => ['bg' => '#f3f4f6', 'text' => '#6b7280', 'dot' => '#9ca3af'],
                                    ];
                                    $csc = $chauffeurStatusColors[$chauffeur->status] ?? $chauffeurStatusColors['offline'];
                                @endphp
                                <span style="padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; background: {{ $csc['bg'] }}; color: {{ $csc['text'] }}; display: inline-flex; align-items: center; gap: 6px;">
                                    <span style="width: 7px; height: 7px; border-radius: 50%; background: {{ $csc['dot'] }}; display: inline-block;"></span>
                                    {{ ucfirst(str_replace('_', ' ', $chauffeur->status)) }}
                                </span>
                            </td>
                            <td>
                                @if($chauffeur->active_booking)
                                    @php
                                        $booking = $chauffeur->active_booking;
                                        $firstItem = $booking->items->first();
                                        $title = $booking->getMainTitle();
                                        $customerName = $booking->customer_name ?: ($booking->user->name ?? 'Guest');
                                        $dateTime = $booking->scheduled_at ? \Carbon\Carbon::parse($booking->scheduled_at)->format('M d, Y @ h:i A') : 'N/A';
                                    @endphp
                                    <div style="display: flex; flex-direction: column; gap: 4px; max-width: 250px;">
                                        <div style="font-weight: 700; color: var(--text-main); font-size: 0.85rem; display: flex; align-items: center; gap: 6px;">
                                            <i class="fas fa-box-open" style="color: var(--primary); font-size: 0.75rem;"></i>
                                            {{ $title }}
                                        </div>
                                        <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600; display: flex; align-items: center; gap: 6px;">
                                            <i class="fas fa-user" style="font-size: 0.7rem;"></i>
                                            {{ $customerName }}
                                        </div>
                                        <div style="font-size: 0.7rem; color: var(--accent); font-weight: 700; display: flex; align-items: center; gap: 6px;">
                                            <i class="fas fa-clock" style="font-size: 0.65rem;"></i>
                                            {{ $dateTime }}
                                        </div>
                                    </div>
                                @else
                                    <span style="font-size: 0.8rem; color: var(--text-muted); font-style: italic;">No active trip</span>
                                @endif
                            </td>
                            <td>
                                <div style="display: flex; flex-direction: column; gap: 2px;">
                                    <span style="font-weight: 800; color: var(--primary); font-size: 1.1rem;">{{ $chauffeur->bookings_count }}</span>
                                    <span style="font-size: 0.7rem; color: #10b981; font-weight: 600;">{{ $chauffeur->completed_trips }} completed</span>
                                </div>
                            </td>
                            <td>
                                @if($chauffeur->total_ratings > 0)
                                    <div style="display: flex; align-items: center; gap: 5px;">
                                        <span style="font-weight: 800; color: #eab308; font-size: 1rem;">{{ number_format($chauffeur->avg_rating, 1) }}</span>
                                        <i class="fas fa-star" style="color: #eab308; font-size: 0.8rem;"></i>
                                    </div>
                                    <span style="font-size: 0.7rem; color: var(--text-muted);">{{ $chauffeur->total_ratings }} reviews</span>
                                @else
                                    <span style="font-size: 0.8rem; color: var(--text-muted); font-style: italic;">No ratings</span>
                                @endif
                            </td>
                            <td>
                                @if($chauffeur->is_online)
                                    <span style="font-size: 0.75rem; font-weight: 700; color: #10b981;"><i class="fas fa-circle" style="font-size: 0.5rem; margin-right: 3px;"></i> Online</span>
                                @else
                                    <span style="font-size: 0.75rem; font-weight: 700; color: #9ca3af;"><i class="fas fa-circle" style="font-size: 0.5rem; margin-right: 3px;"></i> Offline</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.chauffeur-management.show', $chauffeur->id) }}" class="action-btn edit" title="View Profile" style="background: rgba(37, 99, 235, 0.1); color: var(--primary); width: 36px; height: 36px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; text-decoration: none; transition: all 0.2s;">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 40px; color: var(--text-muted);">
                                <i class="fas fa-user-tie" style="font-size: 2rem; margin-bottom: 10px; display: block; opacity: 0.3;"></i>
                                No chauffeurs found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
