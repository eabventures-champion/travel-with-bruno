@extends('layouts.main')

@section('title', 'Destinations - Bruno Heights Ventures')

@section('styles')
<style>
    .page-header {
        padding: 140px 0 80px;
        background: linear-gradient(rgba(15, 23, 42, 0.6), rgba(15, 23, 42, 0.6)), url('https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?auto=format&fit=crop&q=80&w=2000');
        background-size: cover;
        background-position: center;
        color: white;
        text-align: center;
    }
    .services-grid {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 30px;
    }
    .services-grid > * {
        flex: 0 1 300px;
        width: 100%;
        min-width: 280px;
    }
    @media (max-width: 640px) {
        .services-grid > * {
            flex: 1 1 100%;
            max-width: 100%;
        }
    }
    .tour-card {
        position: relative;
        height: 420px;
        border-radius: 25px;
        overflow: hidden;
        box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        cursor: pointer;
        border: none;
    }
    .tour-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 25px 50px rgba(0,0,0,0.2);
    }
    .tour-card-bg {
        position: absolute;
        inset: 0;
        background-size: cover;
        background-position: center;
        transition: transform 0.6s ease;
    }
    .tour-card:hover .tour-card-bg {
        transform: scale(1.1);
    }
    .tour-card-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(to bottom, rgba(0,0,0,0.2) 0%, rgba(0,0,0,0.5) 40%, rgba(0,0,0,0.95) 100%);
    }
    .tour-card-content {
        position: absolute;
        inset: 0;
        padding: 25px;
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
        color: white;
        z-index: 2;
    }
    .category-badge {
        position: absolute;
        top: 20px;
        left: 20px;
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(10px);
        color: white;
        padding: 5px 12px;
        border-radius: 50px;
        font-size: 0.65rem;
        font-weight: 700;
        text-transform: uppercase;
        border: 1px solid rgba(255,255,255,0.3);
    }
</style>
@endsection

@section('content')
<section class="page-header">
    <div class="container">
        <h1 class="font-heading" style="font-size: clamp(2.5rem, 8vw, 4rem); margin-bottom: 20px;">Explore Our Destinations</h1>
        <p style="font-size: 1.2rem; max-width: 700px; margin: 0 auto; opacity: 0.9;">Hand-picked experiences designed for those who seek the extraordinary.</p>
    </div>
</section>

<section style="padding: 100px 20px; background: #fdfdfd;">
    <div class="container" style="max-width: 1400px; margin: 0 auto;">
        <div class="services-grid">
            @foreach($packages as $package)
            <div class="tour-card" @click="openBooking({{ json_encode($package) }}, 'tourism')">
                <div class="tour-card-bg" style="background-image: url('{{ $package->image ? asset('storage/' . $package->image) : 'https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?auto=format&fit=crop&q=80&w=1000' }}')"></div>
                <div class="tour-card-overlay"></div>
                
                <div class="category-badge">
                    {{ $package->category->name ?? 'Destination' }}
                </div>

                <div class="tour-card-content">
                    <h3 class="font-heading" style="font-size: 1.6rem; margin-bottom: 8px; line-height: 1.2;">{{ $package->title }}</h3>
                    <p style="opacity: 0.8; font-size: 0.8rem; margin-bottom: 15px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.5;">
                        {{ $package->description }}
                    </p>
                    
                    <div style="display: flex; gap: 12px; margin-bottom: 15px; font-size: 0.75rem; opacity: 0.9;">
                        <span><i class="fas fa-clock" style="margin-right: 4px;"></i> {{ $package->duration }}</span>
                        <span><i class="fas fa-location-dot" style="margin-right: 4px;"></i> {{ $package->location }}</span>
                    </div>
                    
                    <div style="padding-top: 15px; border-top: 1px solid rgba(255,255,255,0.2);">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                            <div style="min-width: 0;">
                                <span style="font-size: 0.55rem; opacity: 0.7; display: block; text-transform: uppercase; letter-spacing: 0.5px;">Per Person</span>
                                <span style="font-size: 1.2rem; font-weight: 800; color: var(--accent); line-height: 1;">₵{{ number_format($package->price, 2) }}</span>
                            </div>
                            <button @click.stop="openItinerary({{ json_encode($package) }})" class="btn" style="padding: 6px 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.4); background: rgba(255,255,255,0.1); color: white; font-size: 0.6rem; font-weight: 700; cursor: pointer; white-space: nowrap; transition: all 0.2s;">View Itinerary</button>
                        </div>
                        <button @click.stop="openBooking({{ json_encode($package) }}, 'tourism')" class="btn btn-accent" style="width: 100%; padding: 8px; border-radius: 10px; font-weight: 800; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px;">Book Now</button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        
        @if($packages->hasPages())
        <div style="margin-top: 80px; display: flex; justify-content: center;">
            {{ $packages->links() }}
        </div>
        @endif
    </div>
</section>
@endsection
