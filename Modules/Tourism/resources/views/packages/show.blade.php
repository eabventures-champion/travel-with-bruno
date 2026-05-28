@extends('admin::layouts.master')

@section('content')
<div class="page-header">
    <div class="page-title">
        <div style="display: flex; align-items: center; gap: 15px;">
            <div style="width: 50px; height: 50px; border-radius: 12px; background: var(--primary); display: flex; align-items: center; justify-content: center; color: white;">
                <i class="fas fa-map-marked-alt" style="font-size: 1.5rem;"></i>
            </div>
            <div>
                <h1>{{ $package->title }}</h1>
                <p>Package Overview & Details</p>
            </div>
        </div>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.tourism.packages.edit', $package->id) }}" class="btn btn-primary">
            <i class="fas fa-edit"></i> Edit Package
        </a>
        <a href="{{ route('admin.tourism.packages.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px; margin-top: 25px;">
    <!-- Main Content -->
    <div style="display: flex; flex-direction: column; gap: 30px;">
        <!-- Banner & Description -->
        <div class="dashboard-card" style="padding: 0; overflow: hidden;">
            <div style="height: 300px; background: url('{{ $package->image ? asset('storage/' . $package->image) : 'https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?auto=format&fit=crop&q=80&w=1000' }}') center/cover;"></div>
            <div style="padding: 30px;">
                <h3 style="font-family: 'Outfit', sans-serif; margin-bottom: 15px;">About this Package</h3>
                <p style="color: var(--text-slate); line-height: 1.8; font-size: 1.05rem;">
                    {{ $package->description ?? 'No detailed description provided.' }}
                </p>
            </div>
        </div>

        <!-- Itinerary Preview -->
        <div class="dashboard-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                <h3 style="font-family: 'Outfit', sans-serif;">Tour Itinerary</h3>
                <a href="{{ route('admin.tourism.packages.itineraries.index', $package->id) }}" style="color: var(--primary); font-weight: 600; text-decoration: none; font-size: 0.9rem;">
                    Manage Itinerary <i class="fas fa-external-link-alt" style="margin-left: 5px;"></i>
                </a>
            </div>
            
            <div class="show-itinerary-timeline">
                @forelse($package->itineraries->sortBy('day_number') as $item)
                    <div class="show-timeline-item">
                        <div class="day-label">Day {{ $item->day_number }}</div>
                        <div class="day-content">
                            <h4 style="font-family: 'Outfit', sans-serif; color: var(--primary);">{{ $item->title }}</h4>
                            <p style="color: var(--text-slate); margin-top: 8px; font-size: 0.95rem;">{{ $item->description }}</p>
                        </div>
                    </div>
                @empty
                    <div style="text-align: center; padding: 40px; background: #f8fafc; border-radius: 15px; border: 2px dashed #e2e8f0;">
                        <p style="color: var(--text-slate);">No itinerary steps have been added yet.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Sidebar Stats -->
    <div style="display: flex; flex-direction: column; gap: 30px;">
        <div class="dashboard-card">
            <h3 style="font-family: 'Outfit', sans-serif; margin-bottom: 20px; font-size: 1.1rem;">Quick Stats</h3>
            <div style="display: flex; flex-direction: column; gap: 20px;">
                <div class="stat-row">
                    <div class="stat-icon" style="background: #eff6ff; color: #1e40af;"><i class="fas fa-tag"></i></div>
                    <div class="stat-info">
                        <span>Price</span>
                        <strong>₵{{ number_format($package->price, 2) }}</strong>
                    </div>
                </div>
                <div class="stat-row">
                    <div class="stat-icon" style="background: #fdf2f8; color: #9d174d;"><i class="fas fa-clock"></i></div>
                    <div class="stat-info">
                        <span>Duration</span>
                        <strong>{{ $package->duration }}</strong>
                    </div>
                </div>
                <div class="stat-row">
                    <div class="stat-icon" style="background: #f0fdf4; color: #16a34a;"><i class="fas fa-map-marker-alt"></i></div>
                    <div class="stat-info">
                        <span>Location</span>
                        <strong>{{ $package->location }}</strong>
                    </div>
                </div>
                <div class="stat-row">
                    <div class="stat-icon" style="background: #fff7ed; color: #c2410c;"><i class="fas fa-layer-group"></i></div>
                    <div class="stat-info">
                        <span>Category</span>
                        <strong>{{ $package->category->name }}</strong>
                    </div>
                </div>
                <div class="stat-row">
                    <div class="stat-icon" style="background: #f5f3ff; color: #5b21b6;"><i class="fas fa-info-circle"></i></div>
                    <div class="stat-info">
                        <span>Type</span>
                        <strong style="text-transform: capitalize;">{{ $package->package_type }}</strong>
                    </div>
                </div>
            </div>
        </div>

        @if($package->package_type === 'scheduled')
        <div class="dashboard-card" style="background: linear-gradient(135deg, var(--primary), #1e3a8a); color: white;">
            <h3 style="font-family: 'Outfit', sans-serif; margin-bottom: 20px; font-size: 1.1rem;">Event Schedule</h3>
            <div style="display: flex; flex-direction: column; gap: 15px;">
                <div style="display: flex; align-items: center; gap: 12px; background: rgba(255,255,255,0.1); padding: 12px; border-radius: 10px;">
                    <i class="fas fa-plane-departure" style="font-size: 1.2rem; width: 25px;"></i>
                    <div>
                        <div style="font-size: 0.75rem; opacity: 0.8;">Departure</div>
                        <div style="font-weight: 700;">{{ $package->departure_date->format('M d, Y') }}</div>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 12px; background: rgba(255,255,255,0.1); padding: 12px; border-radius: 10px;">
                    <i class="fas fa-plane-arrival" style="font-size: 1.2rem; width: 25px;"></i>
                    <div>
                        <div style="font-size: 0.75rem; opacity: 0.8;">Return</div>
                        <div style="font-weight: 700;">{{ $package->return_date->format('M d, Y') }}</div>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 12px; background: rgba(255,255,255,0.1); padding: 12px; border-radius: 10px;">
                    <i class="fas fa-users" style="font-size: 1.2rem; width: 25px;"></i>
                    <div>
                        <div style="font-size: 0.75rem; opacity: 0.8;">Max Guests</div>
                        <div style="font-weight: 700;">{{ $package->max_guests }} People</div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
    .stat-row {
        display: flex;
        align-items: center;
        gap: 15px;
    }
    .stat-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
    }
    .stat-info span {
        display: block;
        font-size: 0.75rem;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .stat-info strong {
        font-size: 1rem;
        color: var(--text-main);
    }
    
    .show-itinerary-timeline {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }
    .show-timeline-item {
        display: flex;
        gap: 20px;
        border-bottom: 1px solid var(--border);
        padding-bottom: 20px;
    }
    .show-timeline-item:last-child {
        border-bottom: none;
    }
    .day-label {
        background: var(--bg-main);
        color: var(--text-main);
        border: 1px solid var(--border);
        padding: 5px 15px;
        border-radius: 50px;
        font-weight: 800;
        font-size: 0.75rem;
        height: fit-content;
        white-space: nowrap;
        box-shadow: var(--shadow-sm);
    }
    .day-content h4 {
        margin: 0;
        font-size: 1.15rem;
    }
    .day-content p {
        color: var(--text-muted);
        line-height: 1.6;
    }
</style>
@endpush
