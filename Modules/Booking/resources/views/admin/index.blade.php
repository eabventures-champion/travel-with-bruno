@extends('admin::layouts.master')
@section('title', 'All Bookings')

@section('content')
<div x-data="{
    search: '{{ request('search') }}',
    loading: false,
    performSearch() {
        this.loading = true;
        let url = new URL(window.location.href);
        if (this.search.trim()) {
            url.searchParams.set('search', this.search.trim());
        } else {
            url.searchParams.delete('search');
        }
        url.searchParams.delete('page');

        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(response => response.text())
        .then(html => {
            let parser = new DOMParser();
            let doc = parser.parseFromString(html, 'text/html');
            let newContainer = doc.querySelector('#bookings-container');
            if (newContainer) {
                document.querySelector('#bookings-container').innerHTML = newContainer.innerHTML;
            }
            window.history.pushState({}, '', url);
            this.loading = false;
        });
    },
    clearSearch() {
        this.search = '';
        this.performSearch();
    }
}">
<div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;">
    <div class="page-title">
        <h1>All Bookings</h1>
        <p>Monitor and manage all tourism and fleet reservations.</p>
    </div>
    
    @hasanyrole('Super Admin|Operations Admin')
    <div class="page-actions" x-data="{ selectedCount: 0 }" @booking-selection-changed.window="selectedCount = $event.detail.count">
        <div style="display: flex; gap: 15px; align-items: center; margin: 0;">
            @role('Super Admin')
                <button type="submit" form="merge-form" class="btn btn-primary" x-show="selectedCount >= 2" x-cloak style="background: var(--accent); color: white; border: none; border-radius: 12px; font-weight: 800; display: flex; align-items: center; gap: 8px; padding: 10px 20px; box-shadow: 0 4px 12px rgba(249, 115, 22, 0.3);">
                    <i class="fas fa-object-group"></i> Merge Selected (<span x-text="selectedCount"></span>)
                </button>
            @endrole
            <div style="position: relative;">
                <i class="fas" :class="loading ? 'fa-spinner fa-spin' : 'fa-search'" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                <input type="text" x-model="search" @input.debounce.500ms="performSearch" placeholder="Search Ref, Name, Plate, Email..." style="padding: 10px 15px 10px 40px; border-radius: 12px; border: 1px solid var(--border); background: var(--bg-card); color: var(--text-main); min-width: 320px; font-size: 0.9rem; font-family: 'Inter', sans-serif;" autocomplete="off">
            </div>
            <button x-show="search.length > 0" @click="clearSearch" x-cloak class="btn btn-secondary" style="padding: 10px 15px; border-radius: 12px; color: var(--text-muted); cursor: pointer; border: none; outline: none; display: flex; align-items: center; justify-content: center;" title="Clear Search"><i class="fas fa-times"></i></button>
        </div>
    </div>
    @endhasanyrole
</div>

<div id="bookings-container" x-data="{ 
    showCompleted: localStorage.getItem('bookings_showCompleted') === 'true',
    showCancelled: localStorage.getItem('bookings_showCancelled') === 'true',
    showPending: (localStorage.getItem('bookings_showPending') !== 'false') && {{ $groupedBookings['pending']->count() > 0 ? 'true' : 'false' }},
    showConfirmed: (localStorage.getItem('bookings_showConfirmed') !== 'false') && {{ $groupedBookings['confirmed']->count() > 0 ? 'true' : 'false' }},
    showLive: (localStorage.getItem('bookings_showLive') !== 'false') && {{ ($groupedBookings['live_outbound']->count() + $groupedBookings['live_return']->count()) > 0 ? 'true' : 'false' }},
    init() {
        this.$watch('showCompleted', val => localStorage.setItem('bookings_showCompleted', val));
        this.$watch('showCancelled', val => localStorage.setItem('bookings_showCancelled', val));
        this.$watch('showPending', val => localStorage.setItem('bookings_showPending', val));
        this.$watch('showConfirmed', val => localStorage.setItem('bookings_showConfirmed', val));
        this.$watch('showLive', val => localStorage.setItem('bookings_showLive', val));
    },
    updateSelection() {
        const checked = document.querySelectorAll('input[name=\'booking_ids[]\']:checked').length;
        window.dispatchEvent(new CustomEvent('booking-selection-changed', { detail: { count: checked } }));
    }
}">
    <form id="merge-form" action="{{ route('bookings.merge') }}" method="POST" onsubmit="return confirm('Are you sure you want to merge these bookings? This will fuse them into one record and cannot be undone.')">
        @csrf
    @php
        ob_start();
    @endphp
    <!-- Pending Section -->
    <div class="dashboard-card mt-20" style="margin-bottom: 30px; border-left: 5px solid #f59e0b;">
        <div class="card-header" @click="showPending = !showPending" style="cursor: pointer; display: flex; justify-content: space-between; align-items: center; padding: 20px; background: rgba(245, 158, 11, 0.1); border-bottom: 1px solid var(--border);">
            <h3 style="margin: 0; color: #fbbf24; display: flex; align-items: center; gap: 10px; font-weight: 800; text-transform: uppercase; font-size: 1rem; letter-spacing: 1px;">
                <i class="fas fa-clock" style="color: #f59e0b;"></i> Pending Bookings 
                <span style="font-size: 0.8rem; background: #f59e0b; color: white; padding: 2px 12px; border-radius: 20px; font-weight: 900; box-shadow: 0 2px 4px rgba(245, 158, 11, 0.3);">{{ $groupedBookings['pending']->count() }}</span>
            </h3>
            <i class="fas" :class="showPending ? 'fa-chevron-up' : 'fa-chevron-down'" style="color: #f59e0b;"></i>
        </div>
        <div class="table-container" x-show="showPending" x-transition>
            @include('booking::admin.partials.bookings-table', ['bookings' => $groupedBookings['pending']])
        </div>
    </div>
    @php
        $pendingHtml = ob_get_clean();

        ob_start();
    @endphp
    <!-- Confirmed Section -->
    <div class="dashboard-card mt-20" style="margin-bottom: 30px; border-left: 5px solid #10b981;">
        <div class="card-header" @click="showConfirmed = !showConfirmed" style="cursor: pointer; display: flex; justify-content: space-between; align-items: center; padding: 20px; background: rgba(16, 185, 129, 0.1); border-bottom: 1px solid var(--border);">
            <h3 style="margin: 0; color: #34d399; display: flex; align-items: center; gap: 10px; font-weight: 800; text-transform: uppercase; font-size: 1rem; letter-spacing: 1px;">
                <i class="fas fa-check-circle" style="color: #10b981;"></i> Confirmed Bookings
                <span style="font-size: 0.8rem; background: #10b981; color: white; padding: 2px 12px; border-radius: 20px; font-weight: 900; box-shadow: 0 2px 4px rgba(16, 185, 129, 0.3);">{{ $groupedBookings['confirmed']->count() }}</span>
            </h3>
            <i class="fas" :class="showConfirmed ? 'fa-chevron-up' : 'fa-chevron-down'" style="color: #10b981;"></i>
        </div>
        <div class="table-container" x-show="showConfirmed" x-transition>
            @include('booking::admin.partials.bookings-table', ['bookings' => $groupedBookings['confirmed']])
        </div>
    </div>
    @php
        $confirmedHtml = ob_get_clean();

        ob_start();
    @endphp
    <!-- Live Trips Section -->
    <div class="dashboard-card mt-20" style="margin-bottom: 30px; border-left: 5px solid #3b82f6;">
        <div class="card-header" @click="showLive = !showLive" style="cursor: pointer; display: flex; justify-content: space-between; align-items: center; padding: 20px; background: rgba(59, 130, 246, 0.1); border-bottom: 1px solid var(--border);">
            <h3 style="margin: 0; color: #60a5fa; display: flex; align-items: center; gap: 10px; font-weight: 800; text-transform: uppercase; font-size: 1rem; letter-spacing: 1px;">
                <i class="fas fa-route" style="color: #3b82f6;"></i> Live Trips
                <span style="font-size: 0.8rem; background: #3b82f6; color: white; padding: 2px 12px; border-radius: 20px; font-weight: 900; box-shadow: 0 2px 4px rgba(59, 130, 246, 0.3);">{{ $groupedBookings['live_outbound']->count() + $groupedBookings['live_return']->count() }}</span>
            </h3>
            <i class="fas" :class="showLive ? 'fa-chevron-up' : 'fa-chevron-down'" style="color: #3b82f6;"></i>
        </div>
        <div class="table-container" x-show="showLive" x-transition style="padding: 20px; background: var(--bg-card);">
            <!-- Outbound Live Trips Sub-section -->
            <div style="margin-bottom: 25px;">
                <h4 style="margin: 0 0 15px 0; font-size: 0.95rem; color: var(--text-main); font-weight: 700; display: flex; align-items: center; gap: 8px; border-bottom: 1px solid var(--border); padding-bottom: 8px; font-family: 'Outfit', sans-serif;">
                    <i class="fas fa-sign-out-alt" style="color: #3b82f6; transform: rotate(-45deg);"></i> Outbound Trips 
                    <span style="font-size: 0.75rem; background: rgba(59, 130, 246, 0.2); color: #3b82f6; padding: 1px 8px; border-radius: 10px; font-weight: 800;">{{ $groupedBookings['live_outbound']->count() }}</span>
                </h4>
                @if($groupedBookings['live_outbound']->count() > 0)
                    @include('booking::admin.partials.bookings-table', ['bookings' => $groupedBookings['live_outbound'], 'hideAssign' => true, 'groupUnified' => true])
                @else
                    <div style="padding: 20px; text-align: center; color: var(--text-muted); font-style: italic; font-size: 0.9rem; border: 1px dashed var(--border); border-radius: 8px; background: var(--bg-panel, rgba(0,0,0,0.02));">No live outbound trips in progress.</div>
                @endif
            </div>

            <!-- Return Live Trips Sub-section -->
            <div>
                <h4 style="margin: 0 0 15px 0; font-size: 0.95rem; color: var(--text-main); font-weight: 700; display: flex; align-items: center; gap: 8px; border-bottom: 1px solid var(--border); padding-bottom: 8px; font-family: 'Outfit', sans-serif;">
                    <i class="fas fa-sign-in-alt" style="color: #8b5cf6; transform: rotate(135deg);"></i> Return Trips / At Destination
                    <span style="font-size: 0.75rem; background: rgba(139, 92, 246, 0.2); color: #8b5cf6; padding: 1px 8px; border-radius: 10px; font-weight: 800;">{{ $groupedBookings['live_return']->count() }}</span>
                </h4>
                @if($groupedBookings['live_return']->count() > 0)
                    @include('booking::admin.partials.bookings-table', ['bookings' => $groupedBookings['live_return'], 'hideAssign' => true, 'groupUnified' => true])
                @else
                    <div style="padding: 20px; text-align: center; color: var(--text-muted); font-style: italic; font-size: 0.9rem; border: 1px dashed var(--border); border-radius: 8px; background: var(--bg-panel, rgba(0,0,0,0.02));">No live return trips in progress.</div>
                @endif
            </div>
        </div>
    </div>
    @php
        $liveHtml = ob_get_clean();

        ob_start();
    @endphp
    <!-- Completed & Trip Ended Section -->
    <div class="dashboard-card mt-20">
        <div class="card-header" @click="showCompleted = !showCompleted" style="cursor: pointer; display: flex; justify-content: space-between; align-items: center; padding: 20px; background: rgba(248, 250, 252, 0.1); border-bottom: 1px solid var(--border);">
            <h3 style="margin: 0; color: var(--text-muted); display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-flag-checkered"></i> Completed & Trip Ended
                <span style="font-size: 0.8rem; background: #f1f5f9; color: #475569; padding: 2px 10px; border-radius: 20px; font-weight: 800;">{{ $groupedBookings['completed']->count() }}</span>
            </h3>
            <i class="fas" :class="showCompleted ? 'fa-chevron-up' : 'fa-chevron-down'" style="color: var(--text-muted);"></i>
        </div>
        <div class="table-container" x-show="showCompleted" x-transition x-cloak>
            @include('booking::admin.partials.bookings-table', ['bookings' => $groupedBookings['completed'], 'hideAssign' => true, 'groupUnified' => true])
        </div>
    </div>
    @php
        $completedHtml = ob_get_clean();

        ob_start();
    @endphp
    <!-- Cancelled Section -->
    <div class="dashboard-card mt-20">
        <div class="card-header" @click="showCancelled = !showCancelled" style="cursor: pointer; display: flex; justify-content: space-between; align-items: center; padding: 20px; background: rgba(239, 68, 68, 0.05); border-bottom: 1px solid var(--border);">
            <h3 style="margin: 0; color: #ef4444; display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-times-circle"></i> Cancelled Bookings
                <span style="font-size: 0.8rem; background: #fee2e2; color: #dc2626; padding: 2px 10px; border-radius: 20px; font-weight: 800;">{{ $groupedBookings['cancelled']->count() }}</span>
            </h3>
            <i class="fas" :class="showCancelled ? 'fa-chevron-up' : 'fa-chevron-down'" style="color: #ef4444;"></i>
        </div>
        <div class="table-container" x-show="showCancelled" x-transition x-cloak>
            @include('booking::admin.partials.bookings-table', ['bookings' => $groupedBookings['cancelled'], 'hideAssign' => true])
        </div>
    </div>
    @php
        $cancelledHtml = ob_get_clean();

        $sectionBlocks = [
            [
                'html' => $pendingHtml,
                'count' => $groupedBookings['pending']->count(),
                'order' => 1
            ],
            [
                'html' => $confirmedHtml,
                'count' => $groupedBookings['confirmed']->count(),
                'order' => 2
            ],
            [
                'html' => $liveHtml,
                'count' => $groupedBookings['live_outbound']->count() + $groupedBookings['live_return']->count(),
                'order' => 3
            ],
            [
                'html' => $completedHtml,
                'count' => $groupedBookings['completed']->count(),
                'order' => 4
            ],
            [
                'html' => $cancelledHtml,
                'count' => $groupedBookings['cancelled']->count(),
                'order' => 5
            ]
        ];

        // Sort: count > 0 comes first. If both have count > 0 or both have count == 0, keep their default order.
        usort($sectionBlocks, function($a, $b) {
            $aHasCount = $a['count'] > 0;
            $bHasCount = $b['count'] > 0;
            
            if ($aHasCount && !$bHasCount) return -1;
            if (!$aHasCount && $bHasCount) return 1;
            
            return $a['order'] <=> $b['order'];
        });
    @endphp

    @foreach($sectionBlocks as $block)
        {!! $block['html'] !!}
    @endforeach
    </form>
</div>
</div>
</div>
@endsection
