<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" 
      x-data="{ 
        darkMode: localStorage.getItem('theme') === 'dark', 
        sidebarOpen: window.innerWidth > 1024 
      }" 
      x-init="$watch('darkMode', val => localStorage.setItem('theme', val ? 'dark' : 'light'))"
      :data-theme="darkMode ? 'dark' : 'light'" 
      @resize.window="if (window.innerWidth > 1024) { sidebarOpen = true } else { sidebarOpen = false }">

<head>
    <meta charset="utf-8">
    <script>
        // Critical: Apply theme before anything renders to prevent white flash
        (function() {
            const theme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>

    {{-- Preloader & Submit Styles --}}
    <style>
        #admin-page-preloader {
            position: fixed;
            inset: 0;
            z-index: 999999;
            background: #0f172a;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            transition: opacity 0.4s cubic-bezier(0.4, 0, 0.2, 1), visibility 0.4s;
        }
        #admin-page-preloader.preloader-hidden {
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
        }
        .admin-preloader-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
        }
        .admin-preloader-icon {
            font-size: 2.5rem;
            color: #f59e0b;
            margin-bottom: 20px;
            animation: adminPreloaderFloat 2s ease-in-out infinite, adminPreloaderGlow 2s ease-in-out infinite;
        }
        @keyframes adminPreloaderFloat {
            0%, 100% { transform: translateY(0) rotate(-2deg); }
            50%      { transform: translateY(-10px) rotate(2deg); }
        }
        @keyframes adminPreloaderGlow {
            0%, 100% { filter: drop-shadow(0 0 10px rgba(245, 158, 11, 0.35)); }
            50%      { filter: drop-shadow(0 0 20px rgba(245, 158, 11, 0.6)); }
        }
        .admin-preloader-brand {
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            font-size: 1.1rem;
            letter-spacing: 0.2em;
            color: #ffffff;
            margin-bottom: 30px;
            text-transform: uppercase;
        }
        .admin-preloader-brand span {
            color: #ef4444;
        }
        .admin-preloader-bar-track {
            width: 180px;
            height: 3px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 6px;
            overflow: hidden;
            position: relative;
        }
        .admin-preloader-bar-fill {
            position: absolute;
            inset: 0;
            border-radius: 6px;
            background: linear-gradient(90deg, #2563eb, #f59e0b, #2563eb);
            background-size: 200% 100%;
            animation: adminPreloaderBar 1.4s ease-in-out infinite;
        }
        @keyframes adminPreloaderBar {
            0%   { background-position: 100% 0; }
            100% { background-position: -100% 0; }
        }
    </style>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@hasSection('title')@yield('title') - @endif{{ config('app.name', 'Bruno Heights Ventures') }} Admin</title>

    <!-- DNS Prefetch & Preconnect for CDN domains -->
    <link rel="dns-prefetch" href="https://unpkg.com">
    <link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- Google Fonts: Inter & Outfit -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" crossorigin="anonymous">

    <!-- Custom Admin CSS -->
    <link rel="stylesheet" href="{{ asset('assets/admin/css/admin.css') }}">

    <!-- Alpine.js (pinned version for fast CDN resolution) -->
    <script defer src="https://unpkg.com/alpinejs@3.14.9/dist/cdn.min.js"></script>

    @stack('styles')
</head>

<body>
    {{-- Admin Page Preloader --}}
    <div id="admin-page-preloader">
        <div class="admin-preloader-content">
            <div class="admin-preloader-icon">
                <i class="fas fa-plane-departure"></i>
            </div>
            <div class="admin-preloader-brand"><span>BRUNO</span> HEIGHTS ADMIN</div>
            <div class="admin-preloader-bar-track">
                <div class="admin-preloader-bar-fill"></div>
            </div>
        </div>
    </div>

    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="sidebar" :class="sidebarOpen ? '' : 'collapsed'">
            <div class="sidebar-logo">
                <div class="sidebar-logo-text">
                    <span class="logo-main"><i class="fas fa-plane-departure"></i> <span>BRUNO HEIGHTS</span></span>
                    <span class="logo-sub">VENTURES</span>
                </div>
            </div>

            <nav class="sidebar-nav">
                <a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.dashboard*') || request()->is('admin') ? 'active' : '' }}">
                    <i class="fas fa-th-large"></i>
                    <span>Dashboard</span>
                </a>

                @hasanyrole('Super Admin|Operations Admin')
                <a href="{{ route('admin.reports.index') }}" class="nav-item {{ request()->routeIs('admin.reports*') ? 'active' : '' }}">
                    <i class="fas fa-chart-line"></i>
                    <span>Reports</span>
                </a>

                <div class="nav-group-title">Tourism & Travel</div>
                <a href="{{ route('admin.tourism.packages.index') }}" class="nav-item {{ request()->routeIs('admin.tourism.packages*') ? 'active' : '' }}">
                    <i class="fas fa-map-marked-alt"></i>
                    <span>Tourism Packages</span>
                </a>
                <a href="{{ route('admin.tourism.categories.index') }}" class="nav-item {{ request()->routeIs('admin.tourism.categories*') ? 'active' : '' }}">
                    <i class="fas fa-tags"></i>
                    <span>Package Categories</span>
                </a>
                <a href="{{ route('admin.tourism.guest-types.index') }}" class="nav-item {{ request()->routeIs('admin.tourism.guest-types*') ? 'active' : '' }}">
                    <i class="fas fa-users-cog"></i>
                    <span>Customer Types</span>
                </a>
                <a href="{{ route('admin.tourism.interests') }}" class="nav-item {{ request()->routeIs('admin.tourism.interests*') ? 'active' : '' }}">
                    <i class="fas fa-heart"></i>
                    <span>Tour Interests</span>
                </a>

                <div class="nav-group-title">Fleet & Logistics</div>
                <a href="{{ route('admin.fleet.vehicles.index') }}" class="nav-item {{ request()->routeIs('admin.fleet.vehicles*') ? 'active' : '' }}">
                    <i class="fas fa-car"></i>
                    <span>Vehicle Fleet</span>
                </a>
                <a href="{{ route('admin.fleet.types.index') }}" class="nav-item {{ request()->routeIs('admin.fleet.types*') ? 'active' : '' }}">
                    <i class="fas fa-layer-group"></i>
                    <span>Vehicle Types</span>
                </a>
                <a href="{{ route('admin.fleet.chauffeurs.index') }}" class="nav-item {{ request()->routeIs('admin.fleet.chauffeurs*') ? 'active' : '' }}">
                    <i class="fas fa-user-tie"></i>
                    <span>Chauffeurs</span>
                </a>
                <a href="{{ route('admin.fleet.transfers.index') }}" class="nav-item {{ request()->routeIs('admin.fleet.transfers*') ? 'active' : '' }}">
                    <i class="fas fa-route"></i>
                    <span>Transfer Services</span>
                </a>
                <a href="{{ route('admin.fleet.zones.index') }}" class="nav-item {{ request()->routeIs('admin.fleet.zones*') ? 'active' : '' }}">
                    <i class="fas fa-map-marked-alt"></i>
                    <span>Pricing Zones</span>
                </a>
                <a href="{{ route('admin.documents.broadcast') }}" class="nav-item {{ request()->routeIs('admin.documents.broadcast') ? 'active' : '' }}">
                    <i class="fas fa-file-export"></i>
                    <span>Document Manager</span>
                </a>

                <div class="nav-group-title">Website Content</div>
                <a href="{{ route('admin.slides.index') }}" class="nav-item {{ request()->routeIs('admin.slides*') ? 'active' : '' }}">
                    <i class="fas fa-images"></i>
                    <span>Homepage Slides</span>
                </a>
                <a href="{{ route('admin.homepage.content') }}" class="nav-item {{ request()->routeIs('admin.homepage.content') ? 'active' : '' }}">
                    <i class="fas fa-edit"></i>
                    <span>Homepage Content</span>
                </a>
                @endhasanyrole

                <div class="nav-group-title">Operations</div>
                <a href="{{ route('admin.bookings.index') }}" class="nav-item {{ request()->routeIs('admin.bookings*') ? 'active' : '' }}">
                    <i class="fas fa-ticket-alt"></i>
                    <span>{{ auth()->user()->hasAnyRole(['Super Admin', 'Operations Admin']) ? 'Bookings' : 'My Bookings' }}</span>
                </a>

                @unless(auth()->user()->hasAnyRole(['Super Admin', 'Operations Admin']))
                <div class="nav-group-title">Book A Service</div>
                <a href="{{ route('customer.tourism.fixed') }}" class="nav-item {{ request()->routeIs('customer.tourism.fixed') ? 'active' : '' }}">
                    <i class="fas fa-umbrella-beach"></i>
                    <span>Fixed Tours</span>
                </a>
                <a href="{{ route('customer.tourism.organized') }}" class="nav-item {{ request()->routeIs('customer.tourism.organized') ? 'active' : '' }}">
                    <i class="fas fa-calendar-check"></i>
                    <span>Organized Tours</span>
                </a>
                <a href="{{ route('customer.fleet.hiring') }}" class="nav-item {{ request()->routeIs('customer.fleet.hiring') ? 'active' : '' }}">
                    <i class="fas fa-car-side"></i>
                    <span>Car Hiring</span>
                </a>
                <a href="{{ route('customer.fleet.transfers') }}" class="nav-item {{ request()->routeIs('customer.fleet.transfers') ? 'active' : '' }}">
                    <i class="fas fa-plane-departure"></i>
                    <span>Airport Transfers</span>
                </a>
                @endunless

                @hasanyrole('Super Admin|Operations Admin')
                <div class="nav-group-title">CRM & People</div>
                <a href="{{ route('admin.customers.index') }}" class="nav-item {{ request()->routeIs('admin.customers*') ? 'active' : '' }}">
                    <i class="fas fa-address-book"></i>
                    <span>Customer Directory</span>
                </a>
                <a href="{{ route('admin.chauffeur-management.index') }}" class="nav-item {{ request()->routeIs('admin.chauffeur-management*') ? 'active' : '' }}">
                    <i class="fas fa-id-badge"></i>
                    <span>Chauffeur Directory</span>
                </a>
                @endhasanyrole

                <div class="nav-group-title">Communication</div>
                <a href="{{ route('chat.index') }}" class="nav-item {{ request()->routeIs('chat.index') ? 'active' : '' }}">
                    <i class="fas fa-comments"></i>
                    <span>Support Chat</span>
                </a>

                @hasanyrole('Super Admin|Operations Admin')
                <a href="#" class="nav-item">
                    <i class="fas fa-credit-card"></i>
                    <span>Payments</span>
                </a>
                <a href="#" class="nav-item">
                    <i class="fas fa-store"></i>
                    <span>Vendors</span>
                </a>

                <div class="nav-group-title">System</div>
                <a href="{{ route('admin.users.index') }}" class="nav-item {{ request()->routeIs('admin.users*') ? 'active' : '' }}">
                    <i class="fas fa-users"></i>
                    <span>User Management</span>
                </a>
                <a href="{{ route('admin.user-types.index') }}" class="nav-item {{ request()->routeIs('admin.user-types*') ? 'active' : '' }}">
                    <i class="fas fa-user-tag"></i>
                    <span>User Types</span>
                </a>
                <a href="{{ route('admin.settings.index') }}" class="nav-item {{ request()->routeIs('admin.settings*') || request()->is('admin/settings*') ? 'active' : '' }}">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
                @endhasanyrole
            </nav>
        </aside>

        <!-- Mobile Overlay -->
        <div class="sidebar-overlay" x-show="sidebarOpen && window.innerWidth <= 1024" @click="sidebarOpen = false" x-transition.opacity></div>

        <!-- Main Content -->
        <main class="main-content" :class="sidebarOpen ? '' : 'expanded'">
            <header>
                <div class="header-left">
                    <button class="btn-icon" @click="sidebarOpen = !sidebarOpen">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>

                <div class="header-right" style="display: flex; align-items: center; gap: 15px;">
                    <!-- Notification Bell -->
                    <div style="position: relative;" x-data="{ openNotifications: false }">
                        <button @click="openNotifications = !openNotifications" class="btn-icon">
                            <i class="fas fa-bell"></i>
                            @if(auth()->user()->unreadNotifications->count() > 0)
                            <span style="position: absolute; top: 4px; right: 4px; background: #ef4444; color: white; font-size: 0.6rem; font-weight: 800; padding: 2px 5px; border-radius: 10px; line-height: 1; min-width: 16px; height: 16px; display: flex; align-items: center; justify-content: center;">
                                {{ auth()->user()->unreadNotifications->count() }}
                            </span>
                            @endif
                        </button>

                        <div x-show="openNotifications" @click.away="openNotifications = false" x-cloak
                             style="position: absolute; top: 40px; right: 0; width: 320px; background: var(--bg-card); border-radius: 12px; box-shadow: var(--shadow-md); border: 1px solid var(--border); z-index: 100;">
                            <div style="padding: 15px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
                                <h4 style="font-family: 'Outfit', sans-serif; margin: 0; font-size: 1rem;">Notifications</h4>
                                @if(auth()->user()->unreadNotifications->count() > 0)
                                <form action="{{ route('admin.notifications.read-all') }}" method="POST" style="margin: 0;">
                                    @csrf
                                    <button type="submit" style="background: none; border: none; color: var(--primary); font-size: 0.8rem; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 4px; padding: 0; outline: none;">
                                        <i class="fas fa-check-double"></i> Mark all read
                                    </button>
                                </form>
                                @endif
                            </div>
                            <div style="max-height: 350px; overflow-y: auto;">
                                @forelse(auth()->user()->notifications()->take(5)->get() as $notification)
                                <a href="{{ route('admin.notifications.read', $notification->id) }}" style="display: block; padding: 15px; border-bottom: 1px solid var(--border); text-decoration: none; background: {{ $notification->read_at ? 'transparent' : 'rgba(59, 130, 246, 0.05)' }}; transition: 0.2s;">
                                    <div style="font-weight: 700; font-size: 0.9rem; color: var(--text-main); margin-bottom: 3px;">
                                        {{ $notification->data['title'] ?? 'System Alert' }}
                                    </div>
                                    <div style="font-size: 0.8rem; color: var(--text-muted); line-height: 1.4;">
                                        {{ $notification->data['message'] ?? '' }}
                                    </div>
                                    <div style="font-size: 0.7rem; color: var(--primary); margin-top: 5px;">
                                        {{ $notification->created_at->diffForHumans() }}
                                    </div>
                                </a>
                                @empty
                                <div style="padding: 20px; text-align: center; color: var(--text-muted); font-size: 0.85rem;">
                                    No new notifications.
                                </div>
                                @endforelse
                            </div>
                            @if(auth()->user()->notifications()->count() > 0)
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

                    <button @click="darkMode = !darkMode" class="btn-icon">
                        <i class="fas" :class="darkMode ? 'fa-sun' : 'fa-moon'"></i>
                    </button>
                    
                    <div class="user-container" style="position: relative;" x-data="{ open: false }">
                        <div class="user-toggle" @click="open = !open">
                            <span class="user-name">{{ Auth::user()->name ?? 'Admin' }}</span>
                            <div class="user-avatar">
                                <i class="fas fa-user"></i>
                            </div>
                        </div>

                        <!-- User Dropdown -->
                        <div class="user-dropdown" x-show="open" @click.away="open = false" 
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-cloak>
                            
                            <div class="dropdown-header">
                                <div class="dropdown-name">{{ Auth::user()->name ?? 'Admin' }}</div>
                                <div class="dropdown-email">{{ Auth::user()->email ?? '' }}</div>
                            </div>

                            <a href="{{ route('admin.profile') }}" class="dropdown-item">
                                <i class="fas fa-user-circle"></i>
                                <span>My Profile</span>
                            </a>
                            @hasanyrole('Super Admin|Operations Admin')
                            <a href="{{ route('admin.settings.index') }}" class="dropdown-item">
                                <i class="fas fa-cog"></i>
                                <span>Settings</span>
                            </a>
                            @endhasanyrole
                            
                            <div class="dropdown-divider"></div>

                            <a href="javascript:void(0)" onclick="document.getElementById('logout-form').submit();" class="dropdown-item logout">
                                <i class="fas fa-sign-out-alt"></i>
                                <span>Logout</span>
                            </a>

                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                @csrf
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <div class="content-body">
                @if(session('success'))
                <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 7000)" x-show="show" x-transition.opacity.duration.500ms
                     style="background: rgba(16, 185, 129, 0.1); border-left: 4px solid #10b981; color: #047857; padding: 15px 20px; border-radius: 8px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; font-weight: 600; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                    <i class="fas fa-check-circle" style="font-size: 1.2rem;"></i>
                    <div style="flex: 1;">{{ session('success') }}</div>
                </div>
                @endif
                
                @if(session('error'))
                <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 7000)" x-show="show" x-transition.opacity.duration.500ms
                     style="background: rgba(239, 68, 68, 0.1); border-left: 4px solid #ef4444; color: #b91c1c; padding: 15px 20px; border-radius: 8px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; font-weight: 600; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                    <i class="fas fa-exclamation-circle" style="font-size: 1.2rem;"></i>
                    <div style="flex: 1;">{{ session('error') }}</div>
                </div>
                @endif

                @if($errors->any())
                <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 10000)" x-show="show" x-transition.opacity.duration.500ms
                     style="background: rgba(239, 68, 68, 0.1); border-left: 4px solid #ef4444; color: #b91c1c; padding: 15px 20px; border-radius: 8px; margin-bottom: 20px; font-weight: 600; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 5px;">
                        <i class="fas fa-exclamation-triangle" style="font-size: 1.2rem;"></i>
                        <span>Please fix the following errors:</span>
                    </div>
                    <ul style="margin: 0; padding-left: 30px; font-size: 0.9rem; font-weight: normal;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>

    @stack('scripts')
    
    <!-- Submit Overlay (Premium Glassmorphism Style) -->
    <div id="submit-overlay" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); z-index: 9999999; align-items: center; justify-content: center; flex-direction: column; color: white; font-family: 'Outfit', sans-serif; opacity: 0; transition: opacity 0.3s ease;">
        <div style="background: rgba(30, 41, 59, 0.85); padding: 40px; border-radius: 24px; border: 1px solid rgba(255, 255, 255, 0.1); box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); display: flex; align-items: center; justify-content: center; flex-direction: column; text-align: center; max-width: 420px; width: 90%;">
            <!-- Spinner -->
            <div style="position: relative; width: 80px; height: 80px; margin-bottom: 25px; display: flex; align-items: center; justify-content: center;">
                <div style="position: absolute; width: 100%; height: 100%; border: 4px solid rgba(37, 99, 235, 0.1); border-radius: 50%;"></div>
                <div style="position: absolute; width: 100%; height: 100%; border: 4px solid transparent; border-top-color: #2563eb; border-radius: 50%; animation: submit-spin 1s linear infinite;"></div>
                <i class="fas fa-plane-departure" style="font-size: 24px; color: #f59e0b; animation: submit-pulse 2s ease-in-out infinite;"></i>
            </div>
            <h3 id="submit-overlay-title" style="margin: 0 0 10px 0; font-size: 1.35rem; font-weight: 700; color: #f3f4f6; letter-spacing: 0.5px; font-family: 'Outfit', sans-serif;">Optimizing & Saving</h3>
            <p id="submit-overlay-text" style="margin: 0; font-size: 0.95rem; color: #94a3b8; line-height: 1.5; font-family: 'Inter', sans-serif;">Compressing files for ultra-fast upload...</p>
        </div>
    </div>

    <style>
    @keyframes submit-spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    @keyframes submit-pulse {
        0%, 100% { transform: scale(1); opacity: 0.8; }
        50% { transform: scale(1.15); opacity: 1; }
    }
    </style>

    {{-- Preloader hide & Form submission optimizer script --}}
    <script>
        // Smoothly hide page preloader
        window.addEventListener('load', function () {
            var p = document.getElementById('admin-page-preloader');
            if (p) {
                p.classList.add('preloader-hidden');
                setTimeout(function () { p.remove(); }, 500);
            }
        });

        // Sidebar active item scrolling
        document.addEventListener('DOMContentLoaded', function() {
            const activeItem = document.querySelector('.sidebar-nav .nav-item.active');
            if (activeItem) {
                activeItem.scrollIntoView({ behavior: 'auto', block: 'nearest' });
            }
        });

        // Form submit optimizer
        (function() {
            document.addEventListener('submit', function(e) {
                const form = e.target;
                
                // Allow explicit bypass via attribute or prevent double submit
                if (form.getAttribute('data-submitting') === 'true' || form.getAttribute('data-bypass-compress') === 'true') {
                    return;
                }
                
                // Don't intercept simple single-button delete forms or small action forms
                const methodInput = form.querySelector('input[name="_method"]');
                const isDelete = methodInput && methodInput.value.toUpperCase() === 'DELETE';
                const hasConfirmAttr = form.getAttribute('onsubmit') && form.getAttribute('onsubmit').includes('confirm');
                
                const fileInputs = Array.from(form.querySelectorAll('input[type="file"]'));
                const imagesToCompress = [];
                
                fileInputs.forEach(input => {
                    if (input.files && input.files.length > 0) {
                        for (let i = 0; i < input.files.length; i++) {
                            const file = input.files[i];
                            // Exclude animated GIFs to preserve animation
                            if (file.type.startsWith('image/') && file.type !== 'image/gif' && file.size > 400 * 1024) {
                                imagesToCompress.push({
                                    input: input,
                                    file: file,
                                    index: i
                                });
                            }
                        }
                    }
                });
                
                const overlay = document.getElementById('submit-overlay');
                const titleEl = document.getElementById('submit-overlay-title');
                const textEl = document.getElementById('submit-overlay-text');
                
                const showOverlay = (title, text) => {
                    if (overlay) {
                        titleEl.textContent = title;
                        textEl.textContent = text;
                        overlay.style.display = 'flex';
                        overlay.offsetHeight; // Force reflow
                        overlay.style.opacity = '1';
                    }
                };
                
                if (imagesToCompress.length > 0) {
                    e.preventDefault(); // Stop submission for compression
                    
                    showOverlay('Optimizing Images', 'Preparing files for ultra-fast upload...');
                    
                    const inputGroup = {};
                    imagesToCompress.forEach(item => {
                        const key = item.input.name || item.input.id;
                        if (!inputGroup[key]) {
                            inputGroup[key] = {
                                input: item.input,
                                files: Array.from(item.input.files),
                                originalFiles: Array.from(item.input.files)
                            };
                        }
                    });
                    
                    const processNext = (index) => {
                        if (index >= imagesToCompress.length) {
                            // Re-build file lists
                            Object.keys(inputGroup).forEach(key => {
                                const group = inputGroup[key];
                                const dataTransfer = new DataTransfer();
                                group.files.forEach(file => dataTransfer.items.add(file));
                                group.input.files = dataTransfer.files;
                            });
                            
                            showOverlay('Saving Data', 'Uploading optimized files...');
                            form.setAttribute('data-submitting', 'true');
                            form.submit();
                            return;
                        }
                        
                        const item = imagesToCompress[index];
                        const group = inputGroup[item.input.name || item.input.id];
                        
                        showOverlay('Optimizing Images', `Processing image ${index + 1} of ${imagesToCompress.length}...`);
                        
                        const reader = new FileReader();
                        reader.onload = function(event) {
                            const img = new Image();
                            img.onload = function() {
                                const canvas = document.createElement('canvas');
                                let width = img.width;
                                let height = img.height;
                                const maxDim = 1920; // Perfect standard full HD banner size
                                
                                if (width > maxDim || height > maxDim) {
                                    if (width > height) {
                                        height = Math.round((height * maxDim) / width);
                                        width = maxDim;
                                    } else {
                                        width = Math.round((width * maxDim) / height);
                                        height = maxDim;
                                    }
                                }
                                
                                canvas.width = width;
                                canvas.height = height;
                                const ctx = canvas.getContext('2d');
                                ctx.drawImage(img, 0, 0, width, height);
                                
                                canvas.toBlob(function(blob) {
                                    if (blob) {
                                        // Replace extension with .jpg
                                        const ext = item.file.name.substring(item.file.name.lastIndexOf('.'));
                                        const newName = item.file.name.replace(ext, '.jpg');
                                        const compressedFile = new File([blob], newName, {
                                            type: 'image/jpeg',
                                            lastModified: Date.now()
                                        });
                                        
                                        const fileIndex = group.originalFiles.indexOf(item.file);
                                        if (fileIndex !== -1) {
                                            group.files[fileIndex] = compressedFile;
                                        }
                                    }
                                    processNext(index + 1);
                                }, 'image/jpeg', 0.85);
                            };
                            img.onerror = function() { processNext(index + 1); };
                            img.src = event.target.result;
                        };
                        reader.onerror = function() { processNext(index + 1); };
                        reader.readAsDataURL(item.file);
                    };
                    
                    processNext(0);
                } else {
                    // Show premium loading spinner overlay for standard submission
                    // But don't show it for delete confirmation or simple inline action forms that confirm
                    if (isDelete || hasConfirmAttr) {
                        return;
                    }
                    showOverlay('Please Wait', 'Submitting your request...');
                    form.setAttribute('data-submitting', 'true');
                }
            });
        })();
    </script>
</body>
</html>
