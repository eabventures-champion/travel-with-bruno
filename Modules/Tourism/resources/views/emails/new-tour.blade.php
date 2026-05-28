<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 20px auto; padding: 20px; border: 1px solid #e2e8f0; border-radius: 12px; }
        .header { text-align: center; margin-bottom: 30px; }
        .logo { font-size: 24px; font-weight: 800; color: #2563eb; text-decoration: none; }
        .hero-image { width: 100%; height: 200px; object-fit: cover; border-radius: 8px; margin-bottom: 20px; }
        .content { padding: 20px; }
        .title { font-size: 22px; font-weight: 700; color: #1e293b; margin-bottom: 10px; }
        .details { background: #f8fafc; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .detail-item { margin-bottom: 8px; font-size: 14px; }
        .btn { display: inline-block; padding: 12px 24px; background: #2563eb; color: white !important; text-decoration: none; border-radius: 8px; font-weight: 700; margin-top: 10px; }
        .footer { text-align: center; font-size: 12px; color: #64748b; margin-top: 30px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">BRUNO HEIGHTS VENTURES</div>
        </div>

        @if($package->image)
            <img src="{{ $message->embed(public_path('storage/' . $package->image)) }}" class="hero-image">
        @endif

        <div class="content">
            <h1 class="title">New Organized Tour Scheduled!</h1>
            <p>Hi there,</p>
            <p>We are excited to announce a new organized tour that we think you'll love. Get ready for your next adventure!</p>

            <div class="details">
                <div class="detail-item"><strong>Tour:</strong> {{ $package->title }}</div>
                <div class="detail-item"><strong>Destination:</strong> {{ $package->location }}</div>
                <div class="detail-item"><strong>Departure:</strong> {{ $package->departure_date->format('M d, Y') }}</div>
                <div class="detail-item"><strong>Price:</strong> ₵{{ number_format($package->price, 2) }} per person</div>
            </div>

            <p>{{ $package->short_description }}</p>

            <a href="{{ url('/group-tours') }}" class="btn">View Details & Book Now</a>
        </div>

        <div class="footer">
            &copy; {{ date('Year') }} Bruno Heights Ventures. All rights reserved.<br>
            If you have any questions, contact us at info@brunoheights.com
        </div>
    </div>
</body>
</html>
