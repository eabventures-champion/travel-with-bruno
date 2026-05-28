<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'Inter', sans-serif; line-height: 1.6; color: #1e293b; margin: 0; padding: 0; background-color: #f8fafc; }
        .container { max-width: 600px; margin: 20px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); }
        .header { background-color: #1e3a8a; color: #ffffff; padding: 40px 20px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; font-weight: 700; }
        .content { padding: 30px; }
        .account-box { background-color: #f1f5f9; border: 1px solid #e2e8f0; padding: 25px; border-radius: 10px; margin: 20px 0; border-left: 5px solid #1e3a8a; }
        .footer { text-align: center; padding: 20px; font-size: 12px; color: #94a3b8; background-color: #f8fafc; }
        .button { display: inline-block; padding: 14px 28px; background-color: #10b981; color: #ffffff; text-decoration: none; border-radius: 8px; font-weight: 700; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Account Activated!</h1>
            <p>Welcome to Bruno Heights Ventures</p>
        </div>
        <div class="content">
            <p>Hello {{ $user->name }},</p>
            <p>We're excited to have you with us! Following your recent booking, we have activated your personal dashboard account.</p>
            
            <p>You can now log in to track your trips, manage bookings, and access exclusive premium services.</p>
            
            <div class="account-box">
                <h3 style="margin-top: 0; color: #1e3a8a;">Your Login Details</h3>
                <div style="background: white; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0;">
                    <div style="margin-bottom: 10px;"><strong>Login URL:</strong> <a href="{{ config('app.url') }}/login">{{ config('app.url') }}/login</a></div>
                    <div style="margin-bottom: 10px;"><strong>Email Address:</strong> {{ $user->email }}</div>
                    <div><strong>Temporary Password:</strong> <code style="color: #1e3a8a; font-weight: 800; font-size: 1.1rem;">{{ $password }}</code></div>
                </div>
                <p style="font-size: 0.85rem; color: #64748b; margin-top: 15px;"><em>Note: For your security, please change your password immediately after your first login.</em></p>
            </div>

            <div style="text-align: center;">
                <a href="{{ config('app.url') }}/login" class="button">Access My Dashboard</a>
            </div>

            <p style="margin-top: 30px;">If you didn't request this account, please contact our support team immediately.</p>
            <p>Best Regards,<br>The Bruno Heights Ventures Team</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} Bruno Heights Ventures. All rights reserved.<br>
            Accra, Ghana
        </div>
    </div>
</body>
</html>
