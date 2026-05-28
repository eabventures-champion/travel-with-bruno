@extends('layouts.main')

@section('title', 'Transfer Services - Bruno Heights Ventures')

@section('styles')
<style>
    .page-header {
        padding: 120px 0 60px;
        background: linear-gradient(rgba(15, 23, 42, 0.7), rgba(15, 23, 42, 0.7)), url('https://images.unsplash.com/photo-1449156001437-3a144f007355?auto=format&fit=crop&q=80&w=2000');
        background-size: cover;
        background-position: center;
        color: white;
        text-align: center;
    }
    .transfer-card {
        background: white;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        border: 1px solid #f1f5f9;
        height: 100%;
        display: flex;
        flex-direction: column;
        transition: transform 0.3s ease;
    }
    .transfer-card:hover {
        transform: translateY(-10px);
    }
</style>
@endsection

@section('content')
<section class="page-header">
    <div class="container">
        <h1 class="font-heading" style="font-size: 3rem; margin-bottom: 20px;">Seamless Transfer Services</h1>
        <p style="font-size: 1.2rem; max-width: 700px; margin: 0 auto; opacity: 0.9;">Professional, comfortable, and fixed-rate point-to-point transportation solutions.</p>
    </div>
</section>

<section style="padding: 80px 20px; background: #f8fafc;">
    <div class="container" style="max-width: 1200px; margin: 0 auto;">
        <!-- Airport Transfers -->
        <div style="margin-bottom: 60px;">
            <h2 class="font-heading" style="font-size: 2rem; margin-bottom: 30px; display: flex; align-items: center; gap: 15px;">
                <i class="fas fa-plane" style="color: var(--primary);"></i> Airport Transfers
            </h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 30px;">
                @foreach($transfers->where('category', 'airport') as $transfer)
                <div class="transfer-card">
                    <div style="padding: 30px; flex-grow: 1; position: relative;">
                        <div style="position: absolute; top: 20px; right: 20px; background: var(--accent); color: white; padding: 6px 15px; border-radius: 12px; font-weight: 700; z-index: 10; text-transform: capitalize; font-size: 0.7rem;">
                            {{ $transfer->transfer_type === 'both' ? 'Pickup & Drop-off' : $transfer->transfer_type . ' Only' }}
                        </div>
                        <div style="display: flex; align-items: center; justify-content: center; width: 50px; height: 50px; background: rgba(30, 58, 138, 0.1); color: var(--primary); border-radius: 50%; margin-bottom: 20px; font-size: 1.5rem;">
                            <i class="fas fa-plane-{{ $transfer->transfer_type === 'pickup' ? 'arrival' : ($transfer->transfer_type === 'dropoff' ? 'departure' : 'up') }}"></i>
                        </div>
                        <h3 class="font-heading" style="font-size: 1.5rem; margin-bottom: 10px;">{{ $transfer->airport_name }}</h3>
                        <p style="color: #64748b; font-size: 0.95rem; margin-bottom: 15px;">
                            <i class="fas fa-location-dot" style="color: var(--accent); margin-right: 5px;"></i> {{ $transfer->location }}
                        </p>
                        @if($transfer->description)
                        <p style="color: #64748b; font-size: 0.85rem; margin-bottom: 20px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                            {{ $transfer->description }}
                        </p>
                        @endif
                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 25px; padding: 10px; background: #f8fafc; border-radius: 10px; font-size: 0.85rem;">
                            <i class="fas fa-car" style="color: var(--primary);"></i>
                            <span>Vehicle: <strong>{{ $transfer->vehicleType->name ?? 'Standard' }}</strong></span>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 20px; border-top: 1px solid #f1f5f9;">
                            <div>
                                <span style="font-size: 0.75rem; color: #64748b; display: block;">Fixed Rate</span>
                                <span style="font-size: 1.5rem; font-weight: 800; color: var(--primary);">₵{{ number_format($transfer->price, 2) }}</span>
                            </div>
                            <button type="button" @click="openBooking({{ json_encode($transfer->load(['vehicle.vehicleType', 'vehicleType'])) }}, 'transfer')" class="btn btn-primary" style="padding: 10px 20px; border-radius: 10px; font-weight: 800;">Book Transfer</button>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        @if($transfers->where('category', 'other')->count() > 0)
        <!-- General Transfers -->
        <div>
            <h2 class="font-heading" style="font-size: 2rem; margin-bottom: 30px; display: flex; align-items: center; gap: 15px;">
                <i class="fas fa-map-marked-alt" style="color: var(--accent);"></i> Other Locations
            </h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 30px;">
                @foreach($transfers->where('category', 'other') as $transfer)
                <div class="transfer-card">
                    <div style="padding: 30px; flex-grow: 1; position: relative;">
                        <div style="position: absolute; top: 20px; right: 20px; background: var(--accent); color: white; padding: 6px 15px; border-radius: 12px; font-weight: 700; z-index: 10; text-transform: capitalize; font-size: 0.7rem;">
                            {{ $transfer->transfer_type === 'both' ? 'Pickup & Drop-off' : $transfer->transfer_type . ' Only' }}
                        </div>
                        <h3 class="font-heading" style="font-size: 1.5rem; margin-bottom: 10px;">{{ $transfer->airport_name }}</h3>
                        <p style="color: #64748b; font-size: 0.95rem; margin-bottom: 15px;">
                            <i class="fas fa-location-dot" style="color: var(--accent); margin-right: 5px;"></i> {{ $transfer->location }}
                        </p>
                        @if($transfer->description)
                        <p style="color: #64748b; font-size: 0.85rem; margin-bottom: 20px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                            {{ $transfer->description }}
                        </p>
                        @endif
                        <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 20px; border-top: 1px solid #f1f5f9;">
                            <div>
                                <span style="font-size: 0.75rem; color: #64748b; display: block;">Fixed Rate</span>
                                <span style="font-size: 1.5rem; font-weight: 800; color: var(--primary);">₵{{ number_format($transfer->price, 2) }}</span>
                            </div>
                            <button type="button" @click="openBooking({{ json_encode($transfer->load(['vehicle.vehicleType', 'vehicleType'])) }}, 'transfer')" class="btn btn-primary" style="padding: 10px 20px; border-radius: 10px; font-weight: 800;">Book Now</button>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</section>
@endsection
