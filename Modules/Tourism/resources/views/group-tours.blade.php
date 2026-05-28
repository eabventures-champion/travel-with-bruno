@extends('layouts.main')

@section('title', 'Organized Tours - Bruno Heights Ventures')

@section('styles')
<style>
    .page-header {
        padding: 120px 0 60px;
        background: linear-gradient(rgba(15, 23, 42, 0.8), rgba(15, 23, 42, 0.8)), url('https://images.unsplash.com/photo-1533105079780-92b9be482077?auto=format&fit=crop&q=80&w=2000');
        background-size: cover;
        background-position: center;
        color: white;
        text-align: center;
    }
    .tour-card {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        transition: transform 0.3s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    .tour-card:hover {
        transform: translateY(-10px);
    }
</style>
@endsection

@section('content')
<section class="page-header">
    <div class="container">
        <h1 class="font-heading" style="font-size: 3rem; margin-bottom: 20px;">Organized Group Tours</h1>
        <p style="font-size: 1.2rem; max-width: 700px; margin: 0 auto; opacity: 0.9;">Join our scheduled group adventures. Perfect for meeting new people and shared experiences.</p>
    </div>
</section>

<section style="padding: 80px 20px; background: #f8fafc;">
    <div class="container" style="max-width: 1200px; margin: 0 auto;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 30px;">
            @foreach($packages as $package)
            <div class="tour-card">
                <div style="height: 240px; background: url('{{ $package->image ? asset('storage/' . $package->image) : 'https://images.unsplash.com/photo-1533105079780-92b9be482077?auto=format&fit=crop&q=80&w=1000' }}') center/cover; position: relative;">
                    <div style="position: absolute; top: 20px; right: 20px; background: var(--accent); color: white; padding: 6px 15px; border-radius: 12px; font-weight: 700; z-index: 10; text-align: center;">
                        <span style="font-size: 0.7rem; display: block; text-transform: uppercase;">DEPARTING</span>
                        <span style="font-size: 1.1rem;">
                            {{ $package->departure_date->format('M d') }}{{ $package->return_date ? ' - ' . ($package->return_date->format('M') == $package->departure_date->format('M') ? $package->return_date->format('d') : $package->return_date->format('M d')) : '' }}
                        </span>
                    </div>
                </div>
                <div style="padding: 30px; display: flex; flex-direction: column; flex-grow: 1;">
                    <h3 class="font-heading" style="font-size: 1.5rem; margin-bottom: 15px;">
                        {{ $package->title }}
                        @if($package->is_booking_cutoff_reached)
                            <span style="color: #ef4444; font-size: 0.8rem; margin-left: 8px;">(Closed)</span>
                        @elseif($package->is_full)
                            <span style="color: #ef4444; font-size: 0.8rem; margin-left: 8px;">(Full)</span>
                        @endif
                    </h3>
                    <p style="color: var(--text-slate); font-size: 0.95rem; margin-bottom: 20px; flex-grow: 1;">
                        {{ Str::limit($package->description, 150) }}
                    </p>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 25px; font-size: 0.85rem; color: var(--text-slate);">
                        <span><i class="fas fa-clock" style="color: var(--accent); margin-right: 5px;"></i>{{ $package->duration }}</span>
                        <span><i class="fas fa-map-marker-alt" style="color: var(--accent); margin-right: 5px;"></i>{{ $package->location }}</span>
                        <span style="{{ $package->is_full ? 'color: #ef4444; font-weight: 700;' : '' }}">
                            <i class="fas fa-users" style="color: var(--accent); margin-right: 5px;"></i>
                            {{ $package->guests_count ?? 0 }} / {{ $package->max_guests }} Guests
                        </span>
                        <span><i class="fas fa-info-circle" style="color: var(--accent); margin-right: 5px;"></i>{{ $package->is_full ? 'Tour Full' : 'Limited Slots' }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 20px; border-top: 1px solid #f1f5f9;">
                        <div>
                            <span style="font-size: 0.75rem; color: var(--text-slate); display: block; font-weight: 600;">Per Person</span>
                            <span style="font-size: 1.4rem; font-weight: 800; color: var(--accent);">₵{{ number_format($package->price, 2) }}</span>
                        </div>
                        <div style="display: flex; gap: 10px;">
                            <button type="button" @click="openItinerary({{ json_encode($package) }})" class="btn btn-secondary" style="padding: 10px 15px; border-radius: 10px; font-size: 0.8rem;">Read More</button>
                            @if($package->organized_status === 'ongoing')
                                <button type="button" class="btn btn-danger" style="padding: 10px 15px; border-radius: 10px; font-size: 0.8rem; cursor: not-allowed;" disabled>Ongoing</button>
                            @elseif($package->is_full || $package->is_booking_cutoff_reached)
                                @if(session('specialBooking') && session('specialBooking')['package']['id'] == $package->id)
                                    <button type="button" @click="openBooking({{ json_encode($package) }}, 'tourism')" class="btn btn-accent" style="padding: 10px 15px; border-radius: 10px; font-size: 0.8rem;">Join Tour</button>
                                @else
                                    <button type="button" @click="openBooking({{ json_encode($package) }}, 'tourism')" class="btn btn-primary" style="padding: 10px 15px; border-radius: 10px; font-size: 0.8rem; background: #000; border: none;">Interested?</button>
                                @endif
                            @else
                                <button type="button" @click="openBooking({{ json_encode($package) }}, 'tourism')" class="btn btn-accent" style="padding: 10px 15px; border-radius: 10px; font-size: 0.8rem;">Join Tour</button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        <div style="margin-top: 50px;">
            {{ $packages->links() }}
        </div>
    </div>
</section>
@endsection
