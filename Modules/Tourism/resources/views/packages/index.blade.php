@extends('admin::layouts.master')
@section('title', 'Tourism Packages')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>Tourism Packages</h1>
        <p>Manage travel packages, pricing, and itineraries.</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.tourism.packages.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Create Package
        </a>
    </div>
</div>

<div class="dashboard-card mt-20">
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Package</th>
                    <!-- <th>Category</th> -->
                    <th>Price</th>
                    <th>Duration</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($packages as $package)
                    <tr>
                        <td>
                            <div class="user-info">
                                <div class="avatar-small" style="background: var(--accent);">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div>
                                    <div style="font-weight: 600;">{{ $package->title }}</div>
                                    @if($package->package_type === 'scheduled')
                                        <div style="display: flex; flex-direction: column; gap: 4px; margin-top: 6px;">
                                            <span class="badge" style="background: rgba(59, 130, 246, 0.1); color: var(--primary); border: 1px solid var(--border); font-size: 0.65rem; padding: 2px 8px; width: fit-content;">Scheduled</span>
                                            
                                            @php
                                                $orgStatus = $package->organized_status;
                                                $orgColor = $orgStatus === 'upcoming' ? '#0ea5e9' : ($orgStatus === 'ongoing' ? '#f59e0b' : '#10b981');
                                                $orgBg = $orgStatus === 'upcoming' ? 'rgba(14, 165, 233, 0.1)' : ($orgStatus === 'ongoing' ? 'rgba(245, 158, 11, 0.1)' : 'rgba(16, 185, 129, 0.1)');
                                            @endphp
                                            <span class="badge" style="background: {{ $orgBg }}; color: {{ $orgColor }}; border: 1px solid {{ $orgColor }}33; font-size: 0.65rem; padding: 2px 8px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; width: fit-content;">
                                                <i class="fas {{ $orgStatus === 'upcoming' ? 'fa-calendar-alt' : ($orgStatus === 'ongoing' ? 'fa-running' : 'fa-check-circle') }}" style="margin-right: 3px; font-size: 0.6rem;"></i>
                                                {{ $orgStatus }}
                                            </span>
                                        </div>
                                    @else
                                        <span class="badge" style="background: rgba(236, 72, 153, 0.1); color: #ec4899; border: 1px solid var(--border); font-size: 0.7rem; padding: 2px 8px; margin-top: 4px; display: inline-block;">Fixed</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <!-- <td>
                            <span class="badge badge-secondary">{{ $package->category->name }}</span>
                        </td> -->
                        <td style="font-weight: 700;">₵{{ number_format($package->price, 2) }}</td>
                        <td>
                            <div>{{ $package->duration }}</div>
                            @if($package->location)
                                <div style="font-size: 0.75rem; color: #64748b; margin-top: 4px; display: flex; align-items: center; gap: 4px;">
                                    <i class="fas fa-map-marker-alt" style="color: var(--accent);"></i>
                                    <span>{{ $package->location }}</span>
                                </div>
                            @endif
                        </td>
                        <td>
                            <span class="status-badge {{ $package->status === 'active' ? 'status-active' : 'status-inactive' }}">
                                {{ ucfirst($package->status) }}
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                @if($package->itineraries->count() > 0)
                                    <a href="{{ route('admin.tourism.packages.itineraries.index', $package->id) }}" class="action-btn" style="background: rgba(249, 115, 22, 0.1); color: #f97316;" title="Itinerary (Defined)">
                                        <i class="fas fa-calendar-day"></i>
                                    </a>
                                @else
                                <a href="{{ route('admin.tourism.packages.itineraries.index', $package->id) }}" class="action-btn" style="background: rgba(100, 116, 139, 0.1); color: #64748b;" title="Itinerary (Empty)">
                                        <i class="fas fa-calendar-day"></i>
                                    </a>
                                @endif
                                <a href="{{ route('admin.tourism.packages.gallery', $package->id) }}" class="action-btn" style="background: rgba(139, 92, 246, 0.1); color: #8b5cf6;" title="Manage Gallery">
                                    <i class="fas fa-images"></i>
                                </a>
                                <a href="{{ route('admin.tourism.packages.show', $package->id) }}" class="action-btn" style="background: rgba(34, 197, 94, 0.1); color: #22c55e;" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.tourism.packages.edit', $package->id) }}" class="action-btn edit" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.tourism.packages.clone', $package->id) }}" method="POST" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="action-btn" style="background: rgba(16, 185, 129, 0.1); color: #10b981;" title="Clone / Reschedule Tour">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </form>
                                <form action="{{ route('admin.tourism.packages.destroy', $package->id) }}" method="POST" onsubmit="return confirm('Are you sure?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="action-btn delete" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">No packages found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination-container">
        {{ $packages->links() }}
    </div>
</div>
@endsection
