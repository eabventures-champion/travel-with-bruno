<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found | Bruno Heights Ventures</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&family=Inter:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #1e3a8a;
            --accent: #f59e0b;
            --bg: #0f172a;
            --text: #f8fafc;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            color: var(--text);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .container {
            text-align: center;
            padding: 40px;
            max-width: 600px;
            position: relative;
            z-index: 10;
        }

        .logo {
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            font-size: 1.5rem;
            margin-bottom: 60px;
            letter-spacing: -1px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            white-space: nowrap;
        }

        .logo i {
            color: var(--accent);
        }

        .error-code {
            font-family: 'Outfit', sans-serif;
            font-size: 10rem;
            font-weight: 800;
            line-height: 1;
            margin-bottom: 20px;
            background: linear-gradient(135deg, var(--accent) 0%, #d97706 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            opacity: 0.8;
            filter: drop-shadow(0 10px 20px rgba(245, 158, 11, 0.2));
        }

        h1 {
            font-family: 'Outfit', sans-serif;
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 20px;
            color: white;
        }

        p {
            font-size: 1.1rem;
            color: #94a3b8;
            line-height: 1.6;
            margin-bottom: 40px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: var(--primary);
            color: white;
            padding: 15px 35px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 700;
            transition: all 0.3s ease;
            box-shadow: 0 10px 20px rgba(30, 58, 138, 0.3);
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 25px rgba(30, 58, 138, 0.4);
            background: #2563eb;
        }

        .bg-glow {
            position: absolute;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(30, 58, 138, 0.2) 0%, rgba(15, 23, 42, 0) 70%);
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1;
        }

        .floating-icons {
            position: absolute;
            inset: 0;
            z-index: 2;
            pointer-events: none;
        }

        .floating-icons i {
            position: absolute;
            color: rgba(255, 255, 255, 0.03);
            font-size: 4rem;
        }

        @keyframes float {
            0% { transform: translate(0, 0) rotate(0deg); }
            50% { transform: translate(20px, -20px) rotate(10deg); }
            100% { transform: translate(0, 0) rotate(0deg); }
        }

        @media (max-width: 640px) {
            .error-code { font-size: 6rem; }
            h1 { font-size: 1.8rem; }
        }
    </style>
</head>
<body>
    <div class="bg-glow"></div>
    <div class="floating-icons">
        <i class="fas fa-map-marker-alt" style="top: 15%; left: 10%; animation: float 6s infinite;"></i>
        <i class="fas fa-car" style="top: 70%; left: 15%; animation: float 8s infinite;"></i>
        <i class="fas fa-plane" style="top: 20%; right: 15%; animation: float 7s infinite;"></i>
        <i class="fas fa-route" style="top: 75%; right: 10%; animation: float 9s infinite;"></i>
    </div>

    <div class="container">
        <div class="logo">
            <i class="fas fa-plane-departure"></i>
            BRUNO HEIGHTS VENTURES
        </div>
        
        <div class="error-code">404</div>
        <h1>Lost in Transit?</h1>
        <p>The destination you're looking for doesn't exist or has been moved. Let's get you back on the right track.</p>
        
        <a href="/" class="btn">
            <i class="fas fa-home"></i> Back to Homepage
        </a>

        <div style="margin-top: 60px; font-size: 0.85rem; color: #64748b;">
            &copy; {{ date('Y') }} Bruno Heights Ventures. All rights reserved.
        </div>
    </div>
</body>
</html>
