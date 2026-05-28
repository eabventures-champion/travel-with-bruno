<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'Inter', sans-serif; line-height: 1.6; color: #1e293b; margin: 0; padding: 0; background-color: #f8fafc; }
        .container { max-width: 600px; margin: 20px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); }
        .header { background-color: #10b981; color: #ffffff; padding: 40px 20px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; font-weight: 700; }
        .content { padding: 30px; }
        .booking-details { background-color: #f1f5f9; border-radius: 8px; padding: 20px; margin: 20px 0; }
        .detail-row { display: flex; justify-content: space-between; margin-bottom: 10px; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px; }
        .label { font-weight: 600; color: #64748b; }
        .value { font-weight: 700; color: #065f46; }
        .account-box { background-color: #ecfdf5; border: 1px solid #a7f3d0; padding: 20px; border-radius: 10px; margin: 20px 0; border-left: 5px solid #10b981; }
        .footer { text-align: center; padding: 20px; font-size: 12px; color: #94a3b8; background-color: #f8fafc; }
        .button { display: inline-block; padding: 12px 24px; background-color: #1e3a8a; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: 700; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Booking Approved!</h1>
            <p>Reference: {{ $booking->booking_reference }}</p>
        </div>
        <div class="content">
            <p>Hello {{ $booking->customer_name ?: ($booking->user->name ?? 'Valued Customer') }},</p>
            <p>Great news! Your booking with <strong>Bruno Heights Ventures</strong> has been officially **Approved and Confirmed**.</p>
            
            <div class="booking-details">
                <div class="detail-row">
                    <span class="label">Booking Ref</span>
                    <span class="value">{{ $booking->booking_reference }}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Service</span>
                    <span class="value">{{ $booking->items->first()->bookable->name ?? $booking->items->first()->bookable->vehicle_name ?? 'Service' }}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Total Amount</span>
                    <span class="value">₵{{ number_format($booking->total_amount, 2) }}</span>
                </div>
            </div>

            @if($newAccount)
            <div class="account-box">
                <h3 style="margin-top: 0; color: #065f46;">Access Your Dashboard</h3>
                <p>We've created a secure account for you so you can manage your bookings and view your itinerary.</p>
                <div style="background: white; padding: 15px; border-radius: 8px;">
                    <div style="margin-bottom: 5px;"><strong>Email:</strong> {{ $booking->customer_email }}</div>
                    <div><strong>Temporary Password:</strong> <code style="color: #1e3a8a; font-weight: 800;">password</code></div>
                </div>
                <p style="font-size: 0.85rem; color: #065f46; margin-top: 10px;"><em>* We recommend changing your password after your first login.</em></p>
            </div>
            @endif

            <p>You can now log in to your personalized dashboard to view full details of your confirmed packages.</p>

            <div style="text-align: center;">
                <a href="{{ config('app.url') }}/login" class="button">Log In to Dashboard</a>
            </div>

            <p style="margin-top: 30px;">If you have any questions, our operations team is here to help.</p>
            <p>We look forward to serving you,<br>The Bruno Heights Ventures Team</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} Bruno Heights Ventures. All rights reserved.<br>
            Accra, Ghana
        </div>
    </div>
</body>
</html>
