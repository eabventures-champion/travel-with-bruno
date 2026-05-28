@extends('admin::layouts.master')
@section('title', 'Tour Interests')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>Tour Interest Requests</h1>
        <p>Review and follow up with customers interested in fully booked tours.</p>
    </div>
</div>

<div class="dashboard-card mt-20">
        @php
            $groupedInterests = $interests->groupBy('package_id');
        @endphp

        <table style="border-collapse: separate; border-spacing: 0 10px;">
            <thead>
                <tr>
                    <th style="padding-left: 20px;">Customer</th>
                    <th>Interested In</th>
                    <th>Notes</th>
                    <th>Date Received</th>
                    <th>Actions</th>
                </tr>
            </thead>
            @forelse($groupedInterests as $packageId => $packageGroup)
                @php 
                    $package = $packageGroup->first()->package;
                    $packageTitle = $package->title ?? 'Unknown Package';
                @endphp
                <tbody x-data="{ expanded: false }">
                    <tr style="background: rgba(30, 58, 138, 0.05); cursor: pointer; border-left: 4px solid var(--primary);" @click="expanded = !expanded">
                        <td colspan="5" style="padding: 15px 20px; border-bottom: 1px solid var(--border);">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div style="display: flex; align-items: center; gap: 15px;">
                                    <div style="width: 40px; height: 40px; background: white; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: var(--primary); box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
                                        <i class="fas fa-map-marked-alt"></i>
                                    </div>
                                    <div>
                                        <div style="font-weight: 800; color: var(--text-main); font-size: 1.1rem; display: flex; align-items: center; gap: 8px;">
                                            {{ $packageTitle }}
                                            @if($package && $package->organized_status)
                                                @php
                                                    $statusLabel = ucfirst($package->organized_status);
                                                    $bg = 'rgba(59, 130, 246, 0.15)';
                                                    $color = '#3b82f6';
                                                    $border = '1px solid rgba(59, 130, 246, 0.3)';
                                                    if ($package->organized_status === 'ongoing') {
                                                        $bg = 'rgba(245, 158, 11, 0.15)';
                                                        $color = '#f59e0b';
                                                        $border = '1px solid rgba(245, 158, 11, 0.3)';
                                                    } elseif ($package->organized_status === 'completed') {
                                                        $bg = 'rgba(16, 185, 129, 0.15)';
                                                        $color = '#10b981';
                                                        $border = '1px solid rgba(16, 185, 129, 0.3)';
                                                    }
                                                @endphp
                                                <span style="background: {{ $bg }}; color: {{ $color }}; border: {{ $border }}; padding: 2px 8px; border-radius: 6px; font-size: 0.65rem; font-weight: 800; text-transform: uppercase; font-family: 'Outfit', sans-serif; display: inline-flex; align-items: center;">
                                                    {{ $statusLabel }}
                                                </span>
                                            @endif
                                        </div>
                                        <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">
                                            <i class="fas fa-users" style="margin-right: 5px;"></i> {{ $packageGroup->count() }} Interest Requests
                                        </div>
                                    </div>
                                </div>
                                <div style="display: flex; align-items: center; gap: 15px;">
                                    <span class="badge" style="background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2); font-size: 0.7rem; padding: 4px 12px;">
                                        {{ $package->is_booking_cutoff_reached ? 'Booking Closed' : 'Full Capacity' }}
                                    </span>
                                    <i class="fas" :class="expanded ? 'fa-chevron-up' : 'fa-chevron-down'" style="color: var(--text-muted); font-size: 0.9rem;"></i>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @foreach($packageGroup as $interest)
                        @php
                            $isApproved = $interest->booking && $interest->booking->status === 'confirmed';
                            $hasBooked = $interest->booking !== null;
                        @endphp
                        <tr x-show="expanded" x-cloak x-transition style="border-bottom: 1px solid var(--border);">
                            <td style="padding-left: 20px;">
                                <div style="font-weight: 700; color: var(--text-main); font-size: 1rem;">{{ $interest->name }}</div>
                                <div style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 8px;">ID: #INT-{{ $interest->id }}</div>
                                <div style="display: flex; flex-direction: column; gap: 4px; border-top: 1px dashed var(--border); pt: 8px; margin-top: 5px; padding-top: 5px;">
                                    <span style="font-size: 0.8rem; color: var(--text-muted);"><i class="fas fa-envelope" style="margin-right: 5px; color: var(--primary); width: 15px;"></i>{{ $interest->email }}</span>
                                    <span style="font-size: 0.8rem; color: var(--text-muted);"><i class="fas fa-phone" style="margin-right: 5px; color: var(--primary); width: 15px;"></i>{{ $interest->phone ?? 'N/A' }}</span>
                                </div>
                            </td>
                            <td>
                                <div style="display: flex; gap: 5px; align-items: center;">
                                    @if($isApproved)
                                        <span class="badge" style="background: rgba(34, 197, 94, 0.1); color: #16a34a; border: 1px solid rgba(34, 197, 94, 0.2); font-size: 0.6rem; padding: 1px 6px;">
                                            <i class="fas fa-check-circle" style="margin-right: 3px;"></i> Approved
                                        </span>
                                    @else
                                        <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600;">Awaiting Approval</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div style="max-width: 250px; font-size: 0.85rem; color: var(--text-muted); line-height: 1.4;">
                                    {{ $interest->notes ?: 'No notes provided' }}
                                </div>
                            </td>
                            <td>
                                <div style="font-size: 0.85rem;">{{ $interest->created_at->format('M d, Y') }}</div>
                                <div style="font-size: 0.7rem; color: var(--text-muted);">{{ $interest->created_at->format('h:i A') }}</div>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="mailto:{{ $interest->email }}" class="action-btn" style="background: rgba(59, 130, 246, 0.1); color: var(--primary);" title="Send Email">
                                        <i class="fas fa-paper-plane"></i>
                                    </a>
                                    @if($interest->phone)
                                    <a href="tel:{{ $interest->phone }}" class="action-btn" style="background: rgba(34, 197, 94, 0.1); color: #22c55e;" title="Call Customer">
                                        <i class="fas fa-phone-alt"></i>
                                    </a>
                                    @endif
                                    @if($interest->token)
                                        @if($hasBooked)
                                            <button class="action-btn" style="background: #f1f5f9; color: #94a3b8; cursor: not-allowed;" title="Link Already Used" disabled>
                                                <i class="fas fa-link"></i>
                                            </button>
                                        @else
                                            <button class="action-btn" 
                                                    style="background: rgba(139, 92, 246, 0.1); color: #8b5cf6;" 
                                                    title="Copy Special Booking Link"
                                                    onclick="copyBookingLink('{{ route('tourism.special-booking', $interest->token) }}')">
                                                <i class="fas fa-link"></i>
                                            </button>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            @empty
                <tbody>
                    <tr>
                        <td colspan="5" class="text-center" style="padding: 40px; color: var(--text-muted);">
                            <i class="fas fa-info-circle" style="font-size: 2rem; display: block; margin-bottom: 10px; opacity: 0.5;"></i>
                            No interest requests found.
                        </td>
                    </tr>
                </tbody>
            @endforelse
        </table>
    </div>
    <div class="pagination-container">
        {{ $interests->links() }}
    </div>
</div>
@endsection

@push('scripts')
<script>
    function copyBookingLink(url) {
        navigator.clipboard.writeText(url).then(() => {
            alert('Special booking link copied to clipboard!');
        }).catch(err => {
            console.error('Failed to copy: ', err);
            // Fallback for older browsers
            const el = document.createElement('textarea');
            el.value = url;
            document.body.appendChild(el);
            el.select();
            document.execCommand('copy');
            document.body.removeChild(el);
            alert('Special booking link copied to clipboard!');
        });
    }
</script>
@endpush
