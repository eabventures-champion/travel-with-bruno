<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: 'Inter', 'Helvetica Neue', Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #1e293b;
            margin: 0;
            padding: 0;
            background-color: #f8fafc;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .header {
            background-color: #1e3a8a;
            color: #ffffff;
            padding: 40px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
        }
        .content {
            padding: 30px;
        }
        .booking-details {
            background-color: #f1f5f9;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 10px;
        }
        .detail-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        .label {
            font-weight: 600;
            color: #64748b;
        }
        .value {
            font-weight: 700;
            color: #1e3a8a;
        }
        .footer {
            text-align: center;
            padding: 20px;
            font-size: 12px;
            color: #94a3b8;
            background-color: #f8fafc;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #f59e0b;
            color: #ffffff;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 700;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Booking Received</h1>
            <p>Reference: {{ $booking->booking_reference }}</p>
        </div>
        <div class="content">
            <p>Hello {{ $booking->user->name ?? $booking->customer_name }},</p>
            <p>Thank you for booking with <strong>Bruno Heights Ventures</strong>. Your request has been received and is currently being processed by our operations team.</p>
            
            <div class="booking-details">
                <div class="detail-row">
                    <span class="label">Booking Date</span>
                    <span class="value">{{ $booking->created_at->format('M d, Y') }}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Status</span>
                    <span class="value" style="text-transform: capitalize;">{{ $booking->status }}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Total Amount</span>
                    <span class="value">₵{{ number_format($booking->total_amount, 2) }}</span>
                </div>
            </div>

            <p><strong>Next Steps:</strong></p>
            <ul>
                <li>Our team will confirm availability within 2 hours.</li>
                <li>You will receive a separate email with payment instructions.</li>
                <li>Your booking will be finalized once payment is confirmed.</li>
            </ul>

            <div style="text-align: center;">
                <a href="{{ config('app.url') }}/admin/dashboard" class="button">View Booking Status</a>
            </div>

            <p style="margin-top: 30px;">If you have any questions, feel free to reply to this email or contact our support team.</p>
            <p>Safe travels,<br>The Bruno Heights Ventures Team</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} Bruno Heights Ventures. All rights reserved.<br>
            Accra, Ghana
        </div>
    </div>
</body>
</html>
