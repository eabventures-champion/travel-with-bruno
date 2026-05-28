@extends('layouts.main')

@section('title', 'Car Hiring Services - Bruno Heights Ventures')

@section('styles')
<style>
    .page-header {
        padding: 120px 0 60px;
        background: linear-gradient(rgba(15, 23, 42, 0.7), rgba(15, 23, 42, 0.7)), url('https://images.unsplash.com/photo-1549317661-bd32c8ce0db2?auto=format&fit=crop&q=80&w=2000');
        background-size: cover;
        background-position: center;
        color: white;
        text-align: center;
    }
    .vehicle-card {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
        border: 1px solid #f1f5f9;
    }
    .vehicle-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 40px rgba(0,0,0,0.1);
    }
</style>
@endsection

@section('content')
<section class="page-header">
    <div class="container">
        <h1 class="font-heading" style="font-size: 3rem; margin-bottom: 20px;">Premium Fleet Rentals</h1>
        <p style="font-size: 1.2rem; max-width: 700px; margin: 0 auto; opacity: 0.9;">Professional fleet management and vehicle rentals for corporate and personal transportation needs.</p>
    </div>
</section>

<section style="padding: 80px 20px; background: #f8fafc;">
    <div class="container" style="max-width: 1200px; margin: 0 auto;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 30px;">
            @forelse($vehicles as $vehicle)
            <div class="vehicle-card">
                <div style="height: 220px; background: url('{{ $vehicle->image ? asset('storage/' . $vehicle->image) : 'https://images.unsplash.com/photo-1549317661-bd32c8ce0db2?auto=format&fit=crop&q=80&w=1000' }}') center/cover; position: relative;">
                    @if($vehicle->status !== 'available')
                        <div style="position: absolute; top: 15px; right: 15px; background: #ef4444; color: white; padding: 4px 12px; border-radius: 8px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase;">Booked</div>
                        <div style="position: absolute; inset: 0; background: rgba(255,255,255,0.2); backdrop-filter: grayscale(1);"></div>
                    @else
                        <div style="position: absolute; top: 15px; right: 15px; background: #22c55e; color: white; padding: 4px 12px; border-radius: 8px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase;">Available</div>
                    @endif
                </div>
                <div style="padding: 25px; flex-grow: 1; display: flex; flex-direction: column;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                        <span style="font-size: 0.8rem; font-weight: 700; color: var(--primary); text-transform: uppercase; letter-spacing: 1px;">{{ $vehicle->vehicleType->name ?? 'Vehicle' }}</span>
                        <span style="font-size: 0.9rem; color: #64748b;"><i class="fas fa-users" style="margin-right: 5px;"></i>{{ $vehicle->seating_capacity }} Seats</span>
                    </div>
                    <h3 class="font-heading" style="font-size: 1.4rem; margin-bottom: 20px;">{{ $vehicle->make }} {{ $vehicle->model }}</h3>
                    


                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; padding-top: 20px; border-top: 1px solid #f1f5f9;">
                        <div>
                            <span style="font-size: 0.7rem; color: #64748b; display: block;">Hourly Rate</span>
                            <span style="font-size: 1.2rem; font-weight: 700; color: var(--primary);">₵{{ number_format($vehicle->vehicleType->base_hourly_rate ?? 0, 2) }}</span>
                        </div>
                        <div style="text-align: right;">
                            <span style="font-size: 0.7rem; color: #64748b; display: block;">Daily Rate</span>
                            <span style="font-size: 1.2rem; font-weight: 700; color: var(--primary);">₵{{ number_format($vehicle->vehicleType->base_daily_rate ?? 0, 2) }}</span>
                        </div>
                    </div>

                    <div style="margin-top: 25px;">
                        @if($vehicle->status === 'available')
                            <button type="button" @click="openBooking({{ json_encode($vehicle->load('vehicleType')) }}, 'fleet')" class="btn btn-primary" style="width: 100%; padding: 12px; border-radius: 10px; font-weight: 800;">Rent Now</button>
                        @else
                            <button class="btn btn-secondary" disabled style="width: 100%; padding: 12px; border-radius: 10px; cursor: not-allowed; opacity: 0.7; font-weight: 800;">Occupied</button>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div style="grid-column: 1 / -1; text-align: center; padding: 60px;">
                <p style="color: #64748b; font-size: 1.2rem;">Our fleet is currently fully engaged. Please check back soon.</p>
            </div>
            @endforelse
        </div>
    </div>
</section>
@endsection
