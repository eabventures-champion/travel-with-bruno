@extends('layouts.main')

@section('title', 'Gallery - Bruno Heights Ventures')

@section('styles')
<style>
    .page-header {
        padding: 140px 0 80px;
        background: linear-gradient(rgba(15, 23, 42, 0.8), rgba(15, 23, 42, 0.8)), url('https://images.unsplash.com/photo-1469854523086-cc02fe5d8800?auto=format&fit=crop&q=80&w=2000');
        background-size: cover;
        background-position: center;
        color: white;
        text-align: center;
    }

    .gallery-section {
        padding: 100px 0;
        background: #fdfdfd;
    }

    .tour-group {
        margin-bottom: 100px;
    }

    .tour-group:last-child {
        margin-bottom: 0;
    }

    .tour-header {
        display: flex;
        align-items: center;
        gap: 20px;
        margin-bottom: 40px;
        position: relative;
    }

    .tour-header::after {
        content: '';
        flex-grow: 1;
        height: 1px;
        background: linear-gradient(to right, var(--border), transparent);
    }

    .tour-title-badge {
        background: white;
        padding: 10px 25px;
        border-radius: 50px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.05);
        border: 1px solid var(--border);
    }

    .tour-title-badge h2 {
        font-size: 1.5rem;
        margin: 0;
        color: var(--primary);
        font-family: 'Outfit', sans-serif;
        font-weight: 700;
    }

    .gallery-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 30px;
    }

    .gallery-card {
        position: relative;
        border-radius: 20px;
        overflow: hidden;
        aspect-ratio: 4/3;
        cursor: pointer;
        box-shadow: 0 10px 20px rgba(0,0,0,0.05);
        transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
    }

    .gallery-card:hover {
        transform: translateY(-8px) scale(1.02);
        box-shadow: 0 20px 40px rgba(0,0,0,0.15);
    }

    .gallery-card img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.6s ease;
    }

    .gallery-card:hover img {
        transform: scale(1.1);
    }

    .gallery-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(to bottom, transparent 40%, rgba(15, 23, 42, 0.9));
        opacity: 0;
        transition: opacity 0.3s ease;
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
        padding: 25px;
        color: white;
    }

    .gallery-card:hover .gallery-overlay {
        opacity: 1;
    }

    .gallery-info {
        transform: translateY(20px);
        transition: transform 0.4s ease;
    }

    .gallery-card:hover .gallery-info {
        transform: translateY(0);
    }

    .user-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(5px);
        padding: 4px 12px;
        border-radius: 50px;
        font-size: 0.75rem;
        margin-bottom: 10px;
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .caption {
        font-size: 0.9rem;
        font-weight: 500;
        margin: 0;
        line-height: 1.4;
    }

    /* Lightbox Customization */
    .lightbox-modal {
        backdrop-filter: blur(10px);
    }
</style>
@endsection

@section('content')
<section class="page-header">
    <div class="container">
        <span style="color: var(--accent); font-weight: 700; text-transform: uppercase; letter-spacing: 2px; font-size: 0.9rem; display: block; margin-bottom: 10px;">Visual Experiences</span>
        <h1 class="font-heading" style="font-size: clamp(2.5rem, 8vw, 4rem); margin-bottom: 20px;">Tour Gallery</h1>
        <p style="font-size: 1.2rem; max-width: 700px; margin: 0 auto; opacity: 0.9;">Capturing shared moments and unforgettable memories from our premium adventures.</p>
    </div>
</section>

<section class="gallery-section">
    <div class="container" style="max-width: 1300px; margin: 0 auto; padding: 0 20px;">
        @forelse($packages as $package)
            <div class="tour-group">
                <div class="tour-header">
                    <div class="tour-title-badge">
                        <h2>{{ $package->title }}</h2>
                    </div>
                </div>

                <div class="gallery-grid">
                    @foreach($package->uploads as $image)
                        <div class="gallery-card" @click="lightboxImage = '{{ asset('storage/' . $image->image_path) }}'; lightboxOpen = true">
                            <img src="{{ asset('storage/' . $image->image_path) }}" alt="{{ $image->caption ?? $package->title }}">
                            <div class="gallery-overlay">
                                <div class="gallery-info">
                                    <div class="user-badge">
                                        <i class="fas fa-user-circle"></i>
                                        <span>{{ $image->user->name ?? 'Guest' }}</span>
                                    </div>
                                    @if($image->caption)
                                        <p class="caption">{{ $image->caption }}</p>
                                    @endif
                                    <p style="font-size: 0.7rem; opacity: 0.7; margin-top: 5px;">{{ $image->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div style="text-align: center; padding: 100px 20px; background: white; border-radius: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.03);">
                <div style="width: 80px; height: 80px; background: #f1f5f9; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 25px; color: #94a3b8; font-size: 2rem;">
                    <i class="fas fa-camera-retro"></i>
                </div>
                <h3 class="font-heading" style="font-size: 1.8rem; margin-bottom: 15px;">No Shared Moments Yet</h3>
                <p style="color: var(--text-slate); max-width: 500px; margin: 0 auto;">We haven't shared any memories from our tours here yet. Check back soon for exciting updates from our live adventures!</p>
                <div style="margin-top: 40px;">
                    <a href="{{ route('tourism.destinations') }}" class="btn btn-primary" style="padding: 15px 35px; border-radius: 12px; font-weight: 800; text-decoration: none;">Explore Destinations</a>
                </div>
            </div>
        @endforelse
    </div>
</section>

<!-- Reusable Lightbox (using Alpine.js from layout) -->
<div class="lightbox-modal" 
     x-show="lightboxOpen" 
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     @click="lightboxOpen = false"
     x-cloak>
    <button class="lightbox-close" @click="lightboxOpen = false">&times;</button>
    <img :src="lightboxImage" class="lightbox-content" @click.stop>
</div>

@endsection
