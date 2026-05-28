<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Bruno Heights Ventures - Travel & Car Hiring')</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/main.css') }}?v={{ time() }}">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Swiper.js -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('bookingApp', () => ({
                mobileMenuOpen: false,
                lightboxOpen: false,
                lightboxImage: '',

                // Booking Modal State
                bookingOpen: false,
                bookingType: 'fleet',
                bookingItem: null,
                guestType: '',
                rentalUnit: 'Hour',
                isSelfDrive: false,
                hours: 1,
                notes: '',
                customerName: @js(auth()->user()->name ?? ""),
                customerEmail: @js(auth()->user()->email ?? ""),
                customerPhone: @js(auth()->user()->phone ?? ""),
                customerCountry: 'Ghana',
                fleetLeadDays: @js((int) (\App\Models\SystemSetting::where('key', 'fleet_rental_lead_days')->value('value') ?? 2)),
                tourLeadDays: @js((int) (\App\Models\SystemSetting::where('key', 'tourism_fixed_lead_days')->value('value') ?? 7)),
                get minScheduleDate() {
                    const d = new Date();
                    d.setDate(d.getDate() + this.tourLeadDays);
                    const year = d.getFullYear();
                    const month = String(d.getMonth() + 1).padStart(2, '0');
                    const day = String(d.getDate()).padStart(2, '0');
                    const hours = String(d.getHours()).padStart(2, '0');
                    const minutes = String(d.getMinutes()).padStart(2, '0');
                    return `${year}-${month}-${day}T${hours}:${minutes}`;
                },
                get minFleetScheduleDate() {
                    const d = new Date();
                    d.setDate(d.getDate() + this.fleetLeadDays);
                    const year = d.getFullYear();
                    const month = String(d.getMonth() + 1).padStart(2, '0');
                    const day = String(d.getDate()).padStart(2, '0');
                    const hours = String(d.getHours()).padStart(2, '0');
                    const minutes = String(d.getMinutes()).padStart(2, '0');
                    return `${year}-${month}-${day}T${hours}:${minutes}`;
                },


                // Flight Details State (For Transfers)
                flightNumber: '',
                airline: '',
                flightTime: '',
                terminal: '',
                destinationAddress: '',
                transferTypeSelection: '',
                transferZones: @js($transferZones ?? []),
                selectedZoneId: '',
                customLocation: '',

                // Itinerary Modal State
                itineraryOpen: false,
                activeTab: 'itinerary', // 'itinerary' or 'gallery'
                galleryImages: [],
                isUploading: false,
                uploadFiles: [], // Array for multiple files
                uploadCaption: '',

                // Duplicate Check State
                duplicateError: '',
                interestDuplicateError: '',
                interestToken: '',
                interestPackageId: null,

                async checkDuplicate() {
                    if (this.customerEmail.length < 5 && this.customerPhone.length < 5) {
                        this.duplicateError = '';
                        return;
                    }
                    try {
                        const response = await fetch('{{ route('bookings.check-duplicate') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                email: this.customerEmail,
                                phone: this.customerPhone
                            })
                        });
                        const data = await response.json();
                        this.duplicateError = data.exists ? data.message : '';
                    } catch (e) {
                        console.error('Duplicate check failed:', e);
                    }
                },

                async checkInterestDuplicate() {
                    if (this.customerEmail.length < 5 && this.customerPhone.length < 5) {
                        this.interestDuplicateError = '';
                        return;
                    }
                    try {
                        const response = await fetch('{{ route('tourism.tour-interest.check-duplicate') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                package_id: this.bookingItem?.id,
                                email: this.customerEmail,
                                phone: this.customerPhone
                            })
                        });
                        const data = await response.json();
                        this.interestDuplicateError = data.exists ? data.message : '';
                    } catch (e) {
                        console.error('Interest duplicate check failed:', e);
                    }
                },

                openBooking(item, type) {
                    this.bookingItem = item;
                    this.bookingType = type;
                    this.duplicateError = '';
                    this.interestDuplicateError = '';
                    this.guestType = '';
                    this.rentalUnit = 'Hour';
                    this.isSelfDrive = false;
                    this.hours = 1;
                    this.notes = '';
                    this.customerCountry = 'Ghana';
                    this.flightNumber = '';
                    this.airline = '';
                    this.flightTime = '';
                    this.terminal = '';
                    this.destinationAddress = '';
                    this.selectedZoneId = '';
                    this.customLocation = '';

                    if (type === 'transfer') {
                        this.transferTypeSelection = (item.transfer_type !== 'both') ? item.transfer_type : 'pickup';
                    } else {
                        this.transferTypeSelection = '';
                    }

                    // For scheduled tours, default guest count to 1 but ensure it doesn't exceed available spaces
                    if (type === 'tourism' && item.package_type === 'scheduled') {
                        const isSpecial = this.interestToken && item && this.interestPackageId && String(item.id) == String(this.interestPackageId);
                        this.hours = (item.available_spaces > 0 || isSpecial) ? 1 : 0;
                    }

                    this.bookingOpen = true;
                    this.itineraryOpen = false;
                    this.mobileMenuOpen = false;
                },

                updateGuests(val) {
                    if (this.bookingType === 'tourism' && this.guestType === 'Individual' && val > 1) {
                        return;
                    }
                    if (this.bookingType === 'tourism' && this.bookingItem?.package_type === 'scheduled') {
                        // Bypass capacity check if we have a special booking token for this package
                        const isSpecial = this.interestToken && this.bookingItem && this.interestPackageId && String(this.bookingItem.id) == String(this.interestPackageId);

                        if (!isSpecial) {
                            const max = this.bookingItem.available_spaces || 0;
                            if (val > max) {
                                alert(`Sorry, only ${max} spaces are remaining for this tour.`);
                                this.hours = max;
                                return;
                            }
                        }
                    }
                    this.hours = Math.max(1, val);
                },

                get totalPrice() {
                    if (!this.bookingItem) return 0;

                    if (this.bookingType === 'tourism') {
                        return (parseFloat(this.bookingItem.price) || 0) * this.hours;
                    }

                    if (this.bookingType === 'transfer') {
                        let base = parseFloat(this.bookingItem.price || 0);
                        let zone = this.transferZones.find(z => z.id == this.selectedZoneId);
                        let extra = zone ? parseFloat(zone.additional_price) : 0;
                        let mult = (this.transferTypeSelection === 'both') ? 2 : 1;
                        return (base + extra) * mult * this.hours;
                    }

                    let rate = 0;
                    let vType = this.bookingItem.vehicle_type || this.bookingItem.vehicleType;
                    if (this.rentalUnit === 'Hour') {
                        rate = vType?.base_hourly_rate || 0;
                    } else if (this.rentalUnit === 'Day') {
                        rate = vType?.base_daily_rate || 0;
                    } else if (this.rentalUnit === 'Week') {
                        rate = (vType?.base_daily_rate || 0) * 7;
                    }
                    return parseFloat(rate) * this.hours;
                },

                openItinerary(item) {
                    this.bookingItem = item;
                    this.bookingType = 'tourism';
                    this.activeTab = 'itinerary';
                    this.itineraryOpen = true;
                    this.bookingOpen = false;
                    this.mobileMenuOpen = false;
                    this.fetchGallery();
                },

                fetchGallery() {
                    if (!this.bookingItem) return;
                    fetch(`/tourism/packages/${this.bookingItem.id}/gallery`)
                        .then(res => res.json())
                        .then(data => {
                            this.galleryImages = data;
                        });
                },

                async handleUpload() {
                    if (this.uploadFiles.length === 0) return;
                    
                    this.isUploading = true;
                    const formData = new FormData();
                    Array.from(this.uploadFiles).forEach(file => {
                        formData.append('images[]', file);
                    });
                    formData.append('caption', this.uploadCaption);
                    formData.append('_token', '{{ csrf_token() }}');

                    try {
                        const response = await fetch(`/tourism/packages/${this.bookingItem.id}/gallery`, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: formData
                        });

                        const result = await response.json();
                        
                        if (response.ok) {
                            // result is an array of uploaded images
                            result.forEach(img => this.galleryImages.unshift(img));
                            this.uploadFiles = [];
                            this.uploadCaption = '';
                            document.getElementById('gallery-upload-input').value = '';
                        } else {
                            alert(result.message || 'Upload failed');
                        }
                    } catch (error) {
                        console.error('Upload error:', error);
                        alert('An error occurred during upload.');
                    } finally {
                        this.isUploading = false;
                    }
                }
            }))
        })
    </script>

    <style>
        .swiper {
            width: 100%;
            height: 100vh;
        }

        .swiper-slide {
            position: relative;
            overflow: hidden;
        }

        .slide-bg {
            position: absolute;
            inset: 0;
            background-size: cover;
            background-position: center;
            transition: transform 6s ease;
        }

        .swiper-slide-active .slide-bg {
            transform: scale(1.1);
        }

        .slide-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(rgba(15, 23, 42, 0.4), rgba(15, 23, 42, 0.6));
        }

        .slide-content {
            position: relative;
            z-index: 10;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
            padding: 0 20px;
        }

        .hover-lift {
            transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.3s ease;
        }

        .hover-lift:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }

        :root {
            --primary: #1e3a8a;
            --accent: #f59e0b;
            --secondary: #0f172a;
            --text-main: #0f172a;
            --text-slate: #64748b;
        }

        .swiper {
            width: 100%;
            height: 100vh;
        }

        .swiper-slide {
            position: relative;
            overflow: hidden;
        }

        .slide-bg {
            position: absolute;
            inset: 0;
            background-size: cover;
            background-position: center;
            transition: transform 6s ease;
        }

        .swiper-slide-active .slide-bg {
            transform: scale(1.1);
        }

        .slide-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(rgba(15, 23, 42, 0.6), rgba(15, 23, 42, 0.8));
        }

        .slide-content {
            position: relative;
            z-index: 10;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
            padding: 0 20px;
        }

        /* Lightbox Styles */
        .lightbox-modal {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.95);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
            cursor: pointer;
        }

        .lightbox-content {
            max-width: 95%;
            max-height: 90vh;
            border-radius: 12px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            transform: scale(0.9);
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: default;
        }

        .lightbox-modal[x-show="true"] .lightbox-content {
            transform: scale(1);
        }

        .lightbox-close {
            position: absolute;
            top: 30px;
            right: 30px;
            color: white;
            font-size: 2.5rem;
            background: none;
            border: none;
            cursor: pointer;
            z-index: 10000;
            transition: transform 0.2s;
        }

        .lightbox-close:hover {
            transform: scale(1.1);
        }

        /* Dropdown Styles */
        .nav-dropdown {
            position: relative;
            display: inline-block;
        }

        .dropdown-menu {
            position: absolute;
            top: 100%;
            left: 30px;
            background: white;
            min-width: 180px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            border-radius: 12px;
            padding: 10px 0;
            z-index: 100;
            margin-top: 15px;
            border: 1px solid var(--border);
            opacity: 0;
            visibility: hidden;
            transform: translateY(10px);
            transition: all 0.3s ease;
        }

        .nav-dropdown:hover .dropdown-menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .dropdown-menu a {
            display: block;
            margin: 0 !important;
            padding: 12px 20px;
            color: var(--secondary) !important;
            text-transform: none !important;
            font-size: 0.85rem !important;
            border-bottom: 1px solid #f1f5f9;
        }

        .dropdown-menu a:last-child {
            border-bottom: none;
        }

        .dropdown-menu a:hover {
            background: var(--bg-light);
            color: var(--primary) !important;
        }


    </style>
    @yield('styles')
</head>

<body x-data="bookingApp">

    @include('partials.tourism-modals')

    <header id="header" :class="{ 'scrolled': window.scrollY > 50 || mobileMenuOpen }"
        style="position: fixed; width: 100%; top: 0; z-index: 1000; transition: all 0.3s ease; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(15px); box-shadow: 0 2px 10px rgba(0,0,0,0.06);"
        :style="mobileMenuOpen ? 'color: var(--secondary) !important; background: white !important;' : ''">
        <div class="logo">
            <a href="/" style="text-decoration: none; color: #000000; font-weight: 800;">
                <span style="color: #dc2626;">BRUNO</span> HEIGHTS VENTURES
            </a>
        </div>

        <!-- Desktop Nav -->
        <nav class="desktop-nav">
            <a href="/#about">About</a>
            <div class="nav-dropdown">
                <a href="#" @click.prevent style="margin-left: 30px;">Tourism <i class="fas fa-chevron-down"
                        style="font-size: 0.7rem; margin-left: 5px; opacity: 0.7;"></i></a>
                <div class="dropdown-menu">
                    <a href="{{ route('tourism.destinations') }}">Destination tours</a>
                    <a href="{{ route('tourism.group-tours') }}">Organized tours</a>
                </div>
            </div>
            <a href="{{ route('car-hiring') }}">Car Hiring</a>
            <a href="{{ route('transfer-services') }}">Transfer Services</a>
            <a href="{{ route('tourism.gallery') }}">Gallery</a>
            @if (Route::has('login'))
                @auth
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-accent btn-nav">Dashboard</a>
                @else
                    <a href="{{ route('login') }}">Login</a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="btn btn-accent btn-nav">Get Started</a>
                    @endif
                @endauth
            @endif
        </nav>

        <!-- Mobile Toggle -->
        <button class="mobile-toggle" @click="mobileMenuOpen = !mobileMenuOpen" :class="{ 'active': mobileMenuOpen }">
            <span class="hamburger-line"></span>
            <span class="hamburger-line"></span>
            <span class="hamburger-line"></span>
        </button>

        <!-- Mobile Nav -->
        <div class="mobile-nav" x-show="mobileMenuOpen" x-transition:enter="transition ease-out duration-400"
            x-transition:enter-start="opacity-0 -translate-y-10" x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-10" x-cloak>
            <a href="/#about" @click="mobileMenuOpen = false">About Us</a>
            <div x-data="{ tourOpen: false }" style="border-bottom: 1px solid #f1f5f9;">
                <a @click="tourOpen = !tourOpen"
                    style="display: flex; justify-content: space-between; align-items: center; cursor: pointer; font-size: 1.5rem; font-weight: 800; font-family: 'Outfit', sans-serif; color: var(--secondary); padding: 15px 0; border: none;">
                    Tourism <i class="fas" :class="tourOpen ? 'fa-chevron-up' : 'fa-chevron-down'"
                        style="font-size: 1.1rem; opacity: 0.5;"></i>
                </a>
                <div x-show="tourOpen" x-cloak
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 transform -translate-y-2"
                    x-transition:enter-end="opacity-100 transform translate-y-0"
                    style="padding-left: 15px; border-left: 3px solid var(--accent); margin: 0 0 15px 5px; display: flex; flex-direction: column; gap: 5px;">
                    <a href="{{ route('tourism.destinations') }}" @click="mobileMenuOpen = false"
                        style="font-size: 1.2rem; font-weight: 600; padding: 10px 0; border: none; display: flex; align-items: center; gap: 10px; color: var(--secondary);">
                        <i class="fas fa-map-marked-alt" style="color: var(--accent); font-size: 1rem; width: 20px;"></i> Destination tours
                    </a>
                    <a href="{{ route('tourism.group-tours') }}" @click="mobileMenuOpen = false"
                        style="font-size: 1.2rem; font-weight: 600; padding: 10px 0; border: none; display: flex; align-items: center; gap: 10px; color: var(--secondary);">
                        <i class="fas fa-users" style="color: var(--accent); font-size: 1rem; width: 20px;"></i> Organized tours
                    </a>
                </div>
            </div>
            <a href="{{ route('car-hiring') }}" @click="mobileMenuOpen = false">Car Hiring Services</a>
            <a href="{{ route('transfer-services') }}" @click="mobileMenuOpen = false">Transfer Services</a>
            <a href="{{ route('tourism.gallery') }}" @click="mobileMenuOpen = false">Gallery</a>
            @if (Route::has('login'))
                @auth
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-accent">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" @click="mobileMenuOpen = false">Login</a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="btn btn-accent">Get Started</a>
                    @endif
                @endauth
            @endif
        </div>
    </header>

    @include('partials.announcement-bar')

    <main>
        @if(session('success'))
            <div class="alert alert-success" style="margin: 100px auto 0; max-width: 1200px; padding: 15px 25px; border-radius: 12px; background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; display: flex; align-items: center; gap: 10px; font-weight: 600; font-family: 'Inter', sans-serif; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
                <i class="fas fa-check-circle" style="color: #10b981; font-size: 1.2rem;"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger" style="margin: 100px auto 0; max-width: 1200px; padding: 15px 25px; border-radius: 12px; background: #fff1f2; border: 1px solid #fda4af; color: #9f1239; display: flex; align-items: center; gap: 10px; font-weight: 600; font-family: 'Inter', sans-serif; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
                <i class="fas fa-exclamation-circle" style="color: #ef4444; font-size: 1.2rem;"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger" style="margin: 100px auto 0; max-width: 1200px; padding: 15px 25px; border-radius: 12px; background: #fff1f2; border: 1px solid #fda4af; color: #9f1239; display: flex; align-items: center; gap: 10px; font-weight: 600; font-family: 'Inter', sans-serif; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
                <i class="fas fa-exclamation-triangle" style="color: #f59e0b; font-size: 1.2rem; flex-shrink: 0;"></i>
                <div style="display: flex; flex-direction: column; gap: 4px;">
                    @foreach($errors->all() as $error)
                        <span>{{ $error }}</span>
                    @endforeach
                </div>
            </div>
        @endif
        @yield('content')
    </main>

    <footer style="padding: 50px 20px; background: #0f172a; color: white; text-align: center;">
        <div class="container">
            <p>&copy; {{ date('Y') }} Bruno Heights Ventures. All Rights Reserved.</p>
        </div>
    </footer>

    <script>
        window.addEventListener('scroll', function () {
            const header = document.getElementById('header');
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });
    </script>
    @yield('scripts')
    @if(session('specialBooking'))
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Wait for Alpine to be ready
            setTimeout(() => {
                const alpineEl = document.querySelector('[x-data="bookingApp"]');
                if (alpineEl && window.Alpine) {
                    const data = window.Alpine.$data(alpineEl);
                    const special = @js(session('specialBooking'));
                    if (data && special) {
                        data.customerName = special.interest.name;
                        data.customerEmail = special.interest.email;
                        data.customerPhone = special.interest.phone;
                        data.interestToken = special.interest.token;
                        data.interestPackageId = String(special.package.id);
                        data.openBooking(special.package, 'tourism');
                    }
                }
            }, 100);
        });
    </script>
    @endif
</body>

</html>