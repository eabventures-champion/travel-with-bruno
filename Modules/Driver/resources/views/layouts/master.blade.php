<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Chauffeur Portal - Bruno Travel</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        :root {
            --primary: #1e3a8a; /* Deep Blue */
            --secondary: #3b82f6; /* Lighter Blue */
            --accent: #f59e0b; /* Orange/Gold */
            --bg-main: #f8fafc;
            --bg-card: #ffffff;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --shadow-sm: 0 2px 4px rgba(0,0,0,0.05);
            --shadow-md: 0 4px 6px -1px rgba(0,0,0,0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0,0,0,0.1);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-main);
            color: var(--text-main);
            -webkit-font-smoothing: antialiased;
            padding-bottom: 70px; /* Space for bottom nav */
        }

        /* Top App Bar */
        .app-bar {
            background: var(--primary);
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 50;
            box-shadow: var(--shadow-md);
        }

        .app-bar .brand {
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .app-bar .brand i { color: var(--accent); }

        .app-bar .actions {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .notification-bell {
            position: relative;
            cursor: pointer;
        }
        .notification-bell .badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: var(--danger);
            color: white;
            font-size: 0.6rem;
            font-weight: bold;
            padding: 2px 5px;
            border-radius: 10px;
        }

        /* SOS Button */
        .sos-btn {
            background: rgba(239, 68, 68, 0.2);
            color: #fca5a5;
            border: 1px solid rgba(239, 68, 68, 0.5);
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
        }
        .sos-btn:active { background: var(--danger); color: white; }

        /* Main Content */
        .content-area {
            padding: 20px;
            max-width: 600px; /* Optimal for mobile/tablet */
            margin: 0 auto;
        }

        /* Bottom Navigation */
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: var(--bg-card);
            box-shadow: 0 -2px 10px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-around;
            padding: 10px 5px 15px 5px;
            z-index: 50;
            border-top: 1px solid var(--border);
        }

        .nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none;
            color: var(--text-muted);
            font-size: 0.75rem;
            font-weight: 600;
            transition: all 0.2s;
            flex: 1;
        }

        .nav-item i {
            font-size: 1.2rem;
            margin-bottom: 4px;
        }

        .nav-item.active {
            color: var(--primary);
        }
        .nav-item.active i {
            color: var(--accent);
        }

        /* Toggle Switch */
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 26px;
        }
        .toggle-switch input { opacity: 0; width: 0; height: 0; }
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0; left: 0; right: 0; bottom: 0;
            background-color: var(--text-muted);
            transition: .4s;
            border-radius: 34px;
        }
        .slider:before {
            position: absolute;
            content: "";
            height: 20px; width: 20px;
            left: 3px; bottom: 3px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        input:checked + .slider { background-color: var(--success); }
        input:checked + .slider:before { transform: translateX(24px); }

        /* Cards & Utilities */
        .card {
            background: var(--bg-card);
            border-radius: 16px;
            padding: 20px;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border);
            margin-bottom: 20px;
        }

        .btn {
            display: inline-block;
            padding: 12px 20px;
            border-radius: 12px;
            font-weight: 600;
            text-align: center;
            cursor: pointer;
            border: none;
            width: 100%;
            font-family: 'Inter', sans-serif;
            font-size: 1rem;
            transition: opacity 0.2s;
        }
        .btn:active { opacity: 0.8; }
        .btn-primary { background: var(--primary); color: white; }
        .btn-accent { background: var(--accent); color: white; }
        .btn-outline { background: transparent; border: 2px solid var(--primary); color: var(--primary); }

        .badge {
            padding: 4px 10px;
            border-radius: 8px;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
        }
        .badge-pending { background: rgba(245, 158, 11, 0.1); color: var(--warning); }
        .badge-active { background: rgba(16, 185, 129, 0.1); color: var(--success); }
        .badge-completed { background: rgba(30, 58, 138, 0.1); color: var(--primary); }

    </style>
    @stack('styles')
</head>
<body x-data="driverApp()">

    <!-- App Bar -->
    <div class="app-bar">
        <div class="brand">
            <i class="fas fa-steering-wheel"></i> Bruno Driver
        </div>
        <div class="actions">
            <!-- Online/Offline Toggle -->
            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase;" x-text="isOnline ? 'Online' : 'Offline'"></span>
                <label class="toggle-switch">
                    <input type="checkbox" x-model="isOnline" @change="toggleStatus()">
                    <span class="slider"></span>
                </label>
            </div>
            
            <div class="notification-bell" x-data="{ openNotifications: false }" style="position: relative;">
                <button @click="openNotifications = !openNotifications" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: white; position: relative;">
                    <i class="fas fa-bell"></i>
                    @if(auth()->check() && auth()->user()->unreadNotifications->count() > 0)
                    <span class="badge">
                        {{ auth()->user()->unreadNotifications->count() }}
                    </span>
                    @endif
                </button>

                <div x-show="openNotifications" @click.away="openNotifications = false" x-cloak
                     style="position: absolute; top: 35px; right: 0; width: 300px; background: var(--bg-card); color: var(--text-main); border-radius: 12px; box-shadow: var(--shadow-md); border: 1px solid var(--border); z-index: 100;">
                    <div style="padding: 15px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
                        <h4 style="font-family: 'Outfit', sans-serif; margin: 0; font-size: 1rem;">Notifications</h4>
                        @if(auth()->check() && auth()->user()->unreadNotifications->count() > 0)
                        <form action="{{ route('admin.notifications.read-all') }}" method="POST" style="margin: 0;">
                            @csrf
                            <button type="submit" style="background: none; border: none; color: var(--primary); font-size: 0.8rem; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 4px; padding: 0; outline: none;">
                                <i class="fas fa-check-double"></i> Mark all read
                            </button>
                        </form>
                        @endif
                    </div>
                    <div style="max-height: 300px; overflow-y: auto;">
                        @if(auth()->check())
                            @forelse(auth()->user()->notifications()->take(5)->get() as $notification)
                            <a href="{{ route('admin.notifications.read', $notification->id) }}" style="display: block; padding: 15px; border-bottom: 1px solid var(--border); text-decoration: none; background: {{ $notification->read_at ? 'transparent' : 'rgba(59, 130, 246, 0.05)' }};">
                                <div style="font-weight: 700; font-size: 0.85rem; color: var(--text-main); margin-bottom: 3px;">
                                    {{ $notification->data['title'] ?? 'Alert' }}
                                </div>
                                <div style="font-size: 0.75rem; color: var(--text-muted); line-height: 1.3;">
                                    {{ $notification->data['message'] ?? '' }}
                                </div>
                                <div style="font-size: 0.65rem; color: var(--primary); margin-top: 5px;">
                                    {{ $notification->created_at->diffForHumans() }}
                                </div>
                            </a>
                            @empty
                            <div style="padding: 20px; text-align: center; color: var(--text-muted); font-size: 0.85rem;">
                                No new notifications.
                            </div>
                            @endforelse
                        @endif
                    </div>
                    @if(auth()->check() && auth()->user()->notifications()->count() > 0)
                    <div style="padding: 10px 15px; border-top: 1px solid var(--border); text-align: center; background: rgba(30, 58, 138, 0.01); border-radius: 0 0 12px 12px;">
                        <form action="{{ route('admin.notifications.clear-all') }}" method="POST" style="margin: 0; display: inline-block; width: 100%;">
                            @csrf
                            <button type="submit" style="background: none; border: none; color: #ef4444; font-size: 0.8rem; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px; width: 100%; padding: 5px 0; outline: none; transition: opacity 0.2s;" onmouseover="this.style.opacity='0.8'" onmouseout="this.style.opacity='1'">
                                <i class="fas fa-trash-alt"></i> Clear All Notifications
                            </button>
                        </form>
                    </div>
                    @endif
                </div>
            </div>
            <button class="sos-btn" @click="triggerSOS()">SOS</button>
        </div>
    </div>

    <!-- Main Content -->
    <div class="content-area">
        @yield('content')
    </div>

    <!-- Bottom Navigation -->
    <div class="bottom-nav">
        <a href="{{ route('driver.dashboard') }}" class="nav-item {{ request()->routeIs('driver.dashboard') ? 'active' : '' }}">
            <i class="fas fa-home"></i>
            <span>Home</span>
        </a>
        <a href="{{ route('driver.trips') }}" class="nav-item {{ request()->routeIs('driver.trips') ? 'active' : '' }}">
            <i class="fas fa-route"></i>
            <span>Trips</span>
        </a>
        <a href="{{ route('driver.earnings') }}" class="nav-item {{ request()->routeIs('driver.earnings') ? 'active' : '' }}">
            <i class="fas fa-wallet"></i>
            <span>Earnings</span>
        </a>
        <a href="{{ route('driver.schedule') }}" class="nav-item {{ request()->routeIs('driver.schedule') ? 'active' : '' }}">
            <i class="fas fa-calendar-alt"></i>
            <span>Schedule</span>
        </a>
        <a href="{{ route('driver.profile') }}" class="nav-item {{ request()->routeIs('driver.profile') ? 'active' : '' }}">
            <i class="fas fa-user-circle"></i>
            <span>Profile</span>
        </a>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('driverApp', () => ({
                isOnline: {{ auth()->user()->chauffeurProfile?->is_online ? 'true' : 'false' }},
                
                toggleStatus() {
                    fetch('{{ route('driver.status.toggle') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ is_online: this.isOnline })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if(data.success) {
                            // optionally show a toast
                            console.log('Status updated');
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        // Revert on failure
                        this.isOnline = !this.isOnline;
                    });
                },

                triggerSOS() {
                    if(confirm("EMERGENCY SOS: Are you sure you want to alert the operations team? This will send your live location immediately.")) {
                        fetch('{{ route('driver.sos') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            }
                        })
                        .then(res => res.json())
                        .then(data => {
                            if(data.success) {
                                alert("SOS Alert sent! The operations team is contacting you.");
                            }
                        })
                        .catch(err => {
                            console.error(err);
                            alert("Failed to send SOS. Please call the emergency number directly.");
                        });
                    }
                }
            }));
        });
    </script>
    @stack('scripts')
</body>
</html>
