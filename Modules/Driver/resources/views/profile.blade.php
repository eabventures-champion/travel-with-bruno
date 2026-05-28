@extends('driver::layouts.master')

@section('content')
<div x-data="driverProfile()">
    <h2 style="font-family: 'Outfit', sans-serif; color: var(--text-main); font-size: 1.5rem; margin-bottom: 20px;">My Profile</h2>

    {{-- Profile Card --}}
    <div class="card" style="text-align: center; margin-bottom: 25px;">
        <div style="position: relative; display: inline-block; margin-bottom: 15px;">
            <div style="width: 100px; height: 100px; background: linear-gradient(135deg, var(--primary), #7c3aed); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 3rem; font-weight: 800; margin: 0 auto; box-shadow: 0 8px 25px rgba(99,102,241,0.3);">
                {{ substr(auth()->user()->name ?? 'Driver', 0, 1) }}
            </div>
            <button class="btn btn-accent" style="position: absolute; bottom: 0; right: 0; width: 35px; height: 35px; padding: 0; border-radius: 50%; display: flex; justify-content: center; align-items: center;"><i class="fas fa-camera"></i></button>
        </div>
        <h3 style="font-weight: 700; font-size: 1.3rem;">{{ auth()->user()->name }}</h3>
        <div style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 12px;">{{ auth()->user()->email }}</div>

        {{-- Average Rating Display --}}
        @if($totalRatings > 0)
        <div style="display: flex; justify-content: center; align-items: center; gap: 8px; margin-bottom: 5px;">
            <div style="font-size: 1.2rem;">
                @for($i = 1; $i <= 5; $i++)
                    <i class="fas fa-star" style="color: {{ $i <= round($avgRating) ? '#eab308' : 'var(--border)' }}; margin-right: 1px;"></i>
                @endfor
            </div>
            <span style="font-weight: 800; color: var(--text-main); font-size: 1.1rem;">{{ $avgRating }}</span>
        </div>
        <div style="font-size: 0.8rem; color: var(--text-muted);">
            Based on <strong>{{ $totalRatings }}</strong> {{ $totalRatings === 1 ? 'rating' : 'ratings' }}
        </div>
        @else
        <div style="display: flex; justify-content: center; gap: 5px; color: var(--text-muted); font-size: 0.9rem;">
            <i class="far fa-star"></i>
            <span style="font-weight: 600;">No ratings yet</span>
        </div>
        @endif
    </div>

    {{-- Performance Tracking --}}
    <h3 style="font-family: 'Outfit', sans-serif; font-size: 1.1rem; margin-bottom: 15px; color: var(--text-main);">Performance Tracking</h3>
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 25px;">
        <div class="card" style="margin-bottom: 0; padding: 15px; display: flex; flex-direction: column; align-items: center; justify-content: center;">
            <div style="font-size: 1.5rem; font-weight: 800; color: var(--success); margin-bottom: 5px;">{{ $completionRate }}</div>
            <div style="font-size: 0.65rem; color: var(--text-muted); text-transform: uppercase; text-align: center; font-weight: 700;">Completion</div>
        </div>
        <div class="card" style="margin-bottom: 0; padding: 15px; display: flex; flex-direction: column; align-items: center; justify-content: center;">
            <div style="font-size: 1.5rem; font-weight: 800; color: var(--primary); margin-bottom: 5px;">{{ $totalCompleted }}</div>
            <div style="font-size: 0.65rem; color: var(--text-muted); text-transform: uppercase; text-align: center; font-weight: 700;">Trips Done</div>
        </div>
        <div class="card" style="margin-bottom: 0; padding: 15px; display: flex; flex-direction: column; align-items: center; justify-content: center;">
            <div style="font-size: 1.5rem; font-weight: 800; color: #eab308; margin-bottom: 5px;">{{ $avgRating > 0 ? $avgRating : '—' }}</div>
            <div style="font-size: 0.65rem; color: var(--text-muted); text-transform: uppercase; text-align: center; font-weight: 700;">Avg Rating</div>
        </div>
    </div>

    {{-- Recent Customer Reviews --}}
    <h3 style="font-family: 'Outfit', sans-serif; font-size: 1.1rem; margin-bottom: 15px; color: var(--text-main);">Customer Reviews</h3>
    @if($recentRatings->count() > 0)
        @foreach($recentRatings as $review)
        <div class="card" style="margin-bottom: 12px; padding: 15px;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 36px; height: 36px; background: linear-gradient(135deg, var(--primary), var(--secondary)); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.8rem;">
                        {{ substr($review->user->name ?? 'C', 0, 1) }}
                    </div>
                    <div>
                        <div style="font-weight: 700; font-size: 0.9rem; color: var(--text-main);">{{ $review->user->name ?? 'Customer' }}</div>
                        <div style="font-size: 0.7rem; color: var(--text-muted);">{{ $review->created_at->diffForHumans() }}</div>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 4px;">
                    @for($i = 1; $i <= 5; $i++)
                        <i class="fas fa-star" style="font-size: 0.75rem; color: {{ $i <= $review->rating ? '#eab308' : 'var(--border)' }};"></i>
                    @endfor
                </div>
            </div>
            @if($review->comment)
            <div style="font-size: 0.85rem; color: var(--text-muted); font-style: italic; line-height: 1.5; padding-left: 46px;">
                "{{ $review->comment }}"
            </div>
            @else
            <div style="font-size: 0.8rem; color: var(--text-muted); opacity: 0.6; padding-left: 46px;">
                No comment left.
            </div>
            @endif
            @if($review->booking)
            <div style="margin-top: 8px; padding-left: 46px;">
                <span style="font-size: 0.65rem; padding: 2px 8px; background: rgba(99,102,241,0.1); color: var(--primary); border-radius: 4px; font-weight: 700;">
                    Ref: {{ $review->booking->booking_reference }}
                </span>
            </div>
            @endif
        </div>
        @endforeach
    @else
    <div class="card" style="text-align: center; padding: 30px 20px;">
        <div style="font-size: 2rem; margin-bottom: 8px; opacity: 0.3;"><i class="fas fa-star-half-alt"></i></div>
        <div style="font-weight: 700; color: var(--text-main); margin-bottom: 5px;">No Reviews Yet</div>
        <p style="color: var(--text-muted); font-size: 0.8rem; margin: 0;">
            When customers rate their trips, their feedback will appear here.
        </p>
    </div>
    @endif

    {{-- Account Settings --}}
    <h3 style="font-family: 'Outfit', sans-serif; font-size: 1.1rem; margin-bottom: 15px; margin-top: 25px; color: var(--text-main);">Account Settings</h3>
    <div class="card" style="padding: 0; overflow: hidden;">
        <a href="{{ route('driver.profile.edit') }}" style="display: flex; justify-content: space-between; align-items: center; padding: 15px; text-decoration: none; color: var(--text-main); border-bottom: 1px solid var(--border);">
            <div style="display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-id-card" style="color: var(--text-muted); width: 20px;"></i>
                <span style="font-weight: 600;">Personal Information</span>
            </div>
            <i class="fas fa-chevron-right" style="color: var(--text-slate); font-size: 0.8rem;"></i>
        </a>
        <a href="{{ route('driver.profile.documents') }}" style="display: flex; justify-content: space-between; align-items: center; padding: 15px; text-decoration: none; color: var(--text-main); border-bottom: 1px solid var(--border);">
            <div style="display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-file-contract" style="color: var(--text-muted); width: 20px;"></i>
                <span style="font-weight: 600;">License & Documents</span>
            </div>
            <i class="fas fa-chevron-right" style="color: var(--text-slate); font-size: 0.8rem;"></i>
        </a>
        <a href="{{ route('driver.resources') }}" style="display: flex; justify-content: space-between; align-items: center; padding: 15px; text-decoration: none; color: var(--text-main); border-bottom: 1px solid var(--border);">
            <div style="display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-folder-open" style="color: var(--primary); width: 20px;"></i>
                <span style="font-weight: 600;">Shared Resources</span>
            </div>
            <i class="fas fa-chevron-right" style="color: var(--text-slate); font-size: 0.8rem;"></i>
        </a>
        <a href="{{ route('driver.profile.password') }}" style="display: flex; justify-content: space-between; align-items: center; padding: 15px; text-decoration: none; color: var(--text-main); border-bottom: 1px solid var(--border);">
            <div style="display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-lock" style="color: var(--text-muted); width: 20px;"></i>
                <span style="font-weight: 600;">Change Password</span>
            </div>
            <i class="fas fa-chevron-right" style="color: var(--text-slate); font-size: 0.8rem;"></i>
        </a>
        <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
            @csrf
            <button type="submit" style="display: flex; justify-content: space-between; align-items: center; padding: 15px; width: 100%; border: none; background: transparent; text-decoration: none; color: var(--danger); cursor: pointer; text-align: left;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-sign-out-alt" style="width: 20px;"></i>
                    <span style="font-weight: 600;">Logout</span>
                </div>
            </button>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('driverProfile', () => ({
            // State for profile
        }));
    });
</script>
@endpush
@endsection
