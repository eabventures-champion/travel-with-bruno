@extends('booking::layouts.master')

@section('content')
<div class="booking-container">
    <div class="booking-card animate-fade-up">
        <div class="booking-header">
            <h1 class="font-heading">Confirm Your Booking</h1>
            <p>You're one step away from your next adventure with Bruno Heights Ventures.</p>
        </div>

        <div class="booking-content">
            <div class="item-preview">
                <div class="item-image" style="background-image: url('{{ $item->image ? asset('storage/' . $item->image) : 'https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?auto=format&fit=crop&q=80&w=1000' }}')"></div>
                <div class="item-details">
                    <span class="item-category">{{ $type === 'tourism' ? ($item->category->name ?? 'Package') : ($item->type->name ?? 'Vehicle') }}</span>
                    <h2 class="font-heading">{{ $type === 'tourism' ? $item->title : $item->make . ' ' . $item->model }}</h2>
                    <div class="item-meta">
                        <span><i class="fas fa-map-marker-alt"></i> {{ $item->location ?? 'Accra, Ghana' }}</span>
                        @if($type === 'tourism')
                            <span><i class="fas fa-clock"></i> {{ $item->duration }}</span>
                        @else
                            <span><i class="fas fa-users"></i> {{ $item->seating_capacity }} Seats</span>
                        @endif
                    </div>
                </div>
            </div>

            <form action="{{ route('bookings.store') }}" method="POST" class="booking-form">
                @csrf
                <input type="hidden" name="bookable_type" value="{{ $type }}">
                <input type="hidden" name="bookable_id" value="{{ $item->id }}">

                <div class="form-section">
                    <h3 class="font-heading">Booking Summary</h3>
                    <div class="summary-table">
                        <div class="summary-row">
                            <span>Base Price</span>
                            <span class="price-val">₵{{ number_format($type === 'tourism' ? $item->price : ($item->vehicleType->base_daily_rate ?? 0), 2) }}</span>
                        </div>
                        <div class="summary-row total">
                            <span>Total Amount</span>
                            <span class="price-val">₵{{ number_format($type === 'tourism' ? $item->price : ($item->vehicleType->base_daily_rate ?? 0), 2) }}</span>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="notes">Special Requirements or Notes (Optional)</label>
                    <textarea name="notes" id="notes" rows="4" placeholder="Any special requests, arrival times, etc."></textarea>
                </div>

                <div class="form-actions">
                    <a href="{{ url()->previous() }}" class="btn-back">Cancel</a>
                    <button type="submit" class="btn btn-booking">
                        Confirm & Book Now <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    :root {
        --primary: #1e3a8a;
        --accent: #f59e0b;
        --bg-main: #f8fafc;
        --text-main: #1e293b;
        --text-slate: #64748b;
        --border: #e2e8f0;
    }
    
    .booking-container {
        min-height: 100vh;
        background: var(--bg-main);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 40px 20px;
        font-family: 'Inter', sans-serif;
    }
    
    .booking-card {
        background: white;
        width: 100%;
        max-width: 800px;
        border-radius: 24px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.08);
        overflow: hidden;
    }
    
    .booking-header {
        background: var(--primary);
        color: white;
        padding: 40px;
        text-align: center;
    }
    
    .booking-header h1 {
        font-size: 2rem;
        margin-bottom: 10px;
        font-family: 'Outfit', sans-serif;
    }
    
    .booking-header p {
        opacity: 0.9;
        font-size: 1.1rem;
    }
    
    .booking-content {
        padding: 40px;
    }
    
    .item-preview {
        display: flex;
        gap: 25px;
        background: var(--bg-main);
        padding: 20px;
        border-radius: 20px;
        margin-bottom: 35px;
        align-items: center;
    }
    
    .item-image {
        width: 150px;
        height: 100px;
        border-radius: 12px;
        background-size: cover;
        background-position: center;
        flex-shrink: 0;
    }
    
    .item-category {
        font-size: 0.8rem;
        font-weight: 800;
        text-transform: uppercase;
        color: var(--accent);
        letter-spacing: 1px;
    }
    
    .item-details h2 {
        font-size: 1.5rem;
        margin: 5px 0 10px;
        color: var(--primary);
        font-family: 'Outfit', sans-serif;
    }
    
    .item-meta {
        display: flex;
        gap: 20px;
        font-size: 0.9rem;
        color: var(--text-slate);
    }
    
    .item-meta i {
        color: var(--primary);
        margin-right: 5px;
    }
    
    .summary-table {
        background: white;
        border: 1px solid var(--border);
        border-radius: 15px;
        padding: 20px;
        margin-top: 15px;
        margin-bottom: 30px;
    }
    
    .summary-row {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        color: var(--text-slate);
    }
    
    .summary-row.total {
        border-top: 2px dashed var(--border);
        margin-top: 10px;
        padding-top: 20px;
        color: var(--text-main);
        font-weight: 800;
        font-size: 1.25rem;
    }
    
    .price-val {
        color: var(--primary);
    }
    
    .form-group label {
        display: block;
        margin-bottom: 10px;
        font-weight: 600;
        color: var(--text-main);
    }
    
    .form-group textarea {
        width: 100%;
        padding: 15px;
        border-radius: 12px;
        border: 1px solid var(--border);
        background: var(--bg-main);
        font-family: inherit;
        resize: none;
    }
    
    .form-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 40px;
    }
    
    .btn-booking {
        background: var(--accent);
        color: white;
        padding: 15px 35px;
        border-radius: 12px;
        font-weight: 700;
        border: none;
        cursor: pointer;
        transition: all 0.3s;
        font-size: 1rem;
    }
    
    .btn-booking:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(245, 158, 11, 0.2);
    }
    
    .btn-back {
        color: var(--text-slate);
        text-decoration: none;
        font-weight: 600;
    }
    
    .animate-fade-up {
        animation: fadeUp 0.6s ease-out;
    }
    
    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endsection
