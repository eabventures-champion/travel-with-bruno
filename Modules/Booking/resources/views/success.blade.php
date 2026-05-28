@extends('booking::layouts.master')

@section('content')
<div class="booking-container">
    <div class="booking-card animate-fade-up" style="text-align: center; padding: 60px 40px;">
        <div class="success-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        
        <h1 class="font-heading" style="font-size: 2.5rem; color: var(--primary); margin-bottom: 15px;">Booking Received!</h1>
        <p style="font-size: 1.1rem; color: var(--text-slate); max-width: 500px; margin: 0 auto 20px; line-height: 1.6;">
            @if($booking && $booking->customer_name)
                Hi <strong>{{ $booking->customer_name }}</strong>, thank you for choosing {{ $isTourism ? 'Travel with Bruno' : 'Bruno Heights Ventures' }}. Your booking has been successfully submitted and is currently being reviewed by our team.
            @else
                Thank you for choosing {{ $isTourism ? 'Travel with Bruno' : 'Bruno Heights Ventures' }}. Your booking has been successfully submitted and is currently being reviewed by our team.
            @endif
        </p>

        <div class="reference-badge" style="margin-bottom: 15px;">
            Reference Number: <strong>{{ session('reference') }}</strong>
        </div>

        @if($booking && $booking->scheduled_at)
            <div style="display: block; margin: 10px auto 25px;">
                <div class="schedule-badge" style="padding: 12px 25px; border-radius: 12px; background: rgba(245, 158, 11, 0.08); border: 1px solid rgba(245, 158, 11, 0.2); display: inline-flex; align-items: center; gap: 10px; font-weight: 700; color: #b45309; font-size: 0.95rem; font-family: 'Inter', sans-serif;">
                    <i class="fas fa-calendar-alt" style="font-size: 1.1rem;"></i>
                    <span>Scheduled Date & Time: <strong>{{ $booking->scheduled_at->format('M d, Y \a\t h:i A') }}</strong></span>
                </div>
            </div>
        @elseif($booking && $booking->items->first() && isset($booking->items->first()->options['flight_time']))
            <div style="display: block; margin: 10px auto 25px;">
                <div class="schedule-badge" style="padding: 12px 25px; border-radius: 12px; background: rgba(59, 130, 246, 0.08); border: 1px solid rgba(59, 130, 246, 0.2); display: inline-flex; align-items: center; gap: 10px; font-weight: 700; color: #1e3a8a; font-size: 0.95rem; font-family: 'Inter', sans-serif;">
                    <i class="fas fa-plane-departure" style="font-size: 1.1rem;"></i>
                    <span>Pickup / Flight Time: <strong>{{ \Carbon\Carbon::parse($booking->items->first()->options['flight_time'])->format('M d, Y \a\t h:i A') }}</strong></span>
                </div>
            </div>
        @endif

        <div class="next-steps" style="margin-top: 40px; text-align: left; background: var(--bg-main); padding: 30px; border-radius: 20px;">
            <h3 class="font-heading" style="font-size: 1.2rem; margin-bottom: 15px;">What Happens Next?</h3>
            <ul style="color: var(--text-slate); line-height: 1.8; padding-left: 20px;">
                <li>You will receive a confirmation email signifying that your request has been confirmed.</li>
                <li>Once the selected service is confirmed after a follow up call, your booking status will be updated to "Confirmed", and then you can access your dashboard.</li>
            </ul>
        </div>

        <div class="form-actions" style="display: flex; justify-content: center; gap: 20px; margin-top: 40px; flex-wrap: wrap;">
            <a href="/" class="btn btn-booking" style="background: var(--accent); text-decoration: none; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(245, 158, 11, 0.2);">
                <i class="fas fa-home"></i> Return to Home
            </a>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-booking" style="background: var(--primary); text-decoration: none; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(30, 58, 138, 0.15);">
                <i class="fas fa-columns"></i> View Dashboard
            </a>
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
        max-width: 650px;
        border-radius: 32px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.08);
    }
    
    .success-icon {
        font-size: 5rem;
        color: #10b981;
        margin-bottom: 25px;
        animation: scaleIn 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    
    .reference-badge {
        display: inline-block;
        background: #ecfdf5;
        color: #065f46;
        padding: 10px 25px;
        border-radius: 50px;
        font-size: 1rem;
        border: 1px solid #a7f3d0;
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
    
    .btn-back {
        color: var(--text-slate);
        text-decoration: none;
        font-weight: 600;
    }
    
    @keyframes scaleIn {
        from { transform: scale(0); opacity: 0; }
        to { transform: scale(1); opacity: 1; }
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
