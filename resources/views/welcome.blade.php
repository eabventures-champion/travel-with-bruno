<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bruno Heights Ventures - Travel & Car Hiring</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
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
                groupName: '',
                rentalUnit: 'Hour',
                isSelfDrive: false,
                hours: 1,
                notes: '',
                customerName: @js(auth()->user()->name ?? ""),
                customerEmail: @js(auth()->user()->email ?? ""),
                customerPhone: @js(auth()->user()->phone ?? ""),
                customerCountry: 'Ghana',
                get minScheduleDate() {
                    const d = new Date();
                    d.setDate(d.getDate() + 7);
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
                transferZones: @js($transferZones),
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
                                email: this.customerEmail,
                                phone: this.customerPhone,
                                package_id: this.bookingItem.id
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
                    this.groupName = '';
                    this.rentalUnit = 'Hour';
                    this.isSelfDrive = false;
                    this.notes = '';
                    this.flightNumber = '';
                    this.airline = '';
                    this.flightTime = '';
                    this.terminal = '';
                    this.destinationAddress = '';
                    this.selectedZoneId = '';
                    this.customLocation = '';
                    
                    // Specific logic for transfers to prevent conflicts
                    if (type === 'transfer') {
                        this.transferTypeSelection = (item.transfer_type !== 'both') ? item.transfer_type : 'pickup';
                    } else {
                        this.transferTypeSelection = '';
                    }

                    // For scheduled tours, default guest count to 1 but ensure it doesn't exceed available spaces
                    if (type === 'tourism' && item.package_type === 'scheduled') {
                        const isSpecial = this.interestToken && item && this.interestPackageId && String(item.id) == String(this.interestPackageId);
                        this.hours = (item.available_spaces > 0 || isSpecial) ? 1 : 0;
                    } else {
                        this.hours = 1;
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
                                alert('Sorry, only ' + max + ' spaces are remaining for this tour.');
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
                    
                    // Fleet / Rental Logic
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
        .swiper { width: 100%; height: 100vh; }
        .swiper-slide { position: relative; overflow: hidden; }
        .slide-bg { position: absolute; inset: 0; background-size: cover; background-position: center; transition: transform 6s ease; }
        .swiper-slide-active .slide-bg { transform: scale(1.1); }
        .slide-overlay { position: absolute; inset: 0; background: linear-gradient(rgba(15, 23, 42, 0.6), rgba(15, 23, 42, 0.8)); }
        .slide-content { position: relative; z-index: 10; height: 100%; display: flex; align-items: center; justify-content: center; text-align: center; color: white; padding: 0 20px; }

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
        .lightbox-close:hover { transform: scale(1.1); }
        
        /* Dropdown Styles */
        .nav-dropdown { position: relative; display: inline-block; }
        .dropdown-menu { 
            position: absolute; 
            top: 100%; 
            left: 30px; 
            background: white; 
            min-width: 180px; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.1); 
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
        .dropdown-menu a:last-child { border-bottom: none; }
        .dropdown-menu a:hover { background: var(--bg-light); color: var(--primary) !important; }
        

    </style>
</head>

<body x-data="bookingApp">

    @include('partials.tourism-modals')
    
    @if(session('success'))
        <div class="alert alert-success" style="position: relative; z-index: 1001; margin: 90px auto 0; max-width: 1200px; padding: 15px 25px; border-radius: 12px; background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; display: flex; align-items: center; gap: 10px; font-weight: 600; font-family: 'Inter', sans-serif; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
            <i class="fas fa-check-circle" style="color: #10b981; font-size: 1.2rem;"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger" style="position: relative; z-index: 1001; margin: 90px auto 0; max-width: 1200px; padding: 15px 25px; border-radius: 12px; background: #fff1f2; border: 1px solid #fda4af; color: #9f1239; display: flex; align-items: center; gap: 10px; font-weight: 600; font-family: 'Inter', sans-serif; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
            <i class="fas fa-exclamation-circle" style="color: #ef4444; font-size: 1.2rem;"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger" style="position: relative; z-index: 1001; margin: 90px auto 0; max-width: 1200px; padding: 15px 25px; border-radius: 12px; background: #fff1f2; border: 1px solid #fda4af; color: #9f1239; display: flex; align-items: center; gap: 10px; font-weight: 600; font-family: 'Inter', sans-serif; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
            <i class="fas fa-exclamation-triangle" style="color: #f59e0b; font-size: 1.2rem; flex-shrink: 0;"></i>
            <div style="display: flex; flex-direction: column; gap: 4px;">
                @foreach($errors->all() as $error)
                    <span>{{ $error }}</span>
                @endforeach
            </div>
        </div>
    @endif

    <header id="header" :class="{ 'scrolled': window.scrollY > 50 || mobileMenuOpen }" style="background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(15px); box-shadow: 0 2px 10px rgba(0,0,0,0.06);" :style="mobileMenuOpen ? 'color: var(--secondary) !important; background: white !important;' : ''">
        <div class="logo">
            <a href="/" style="text-decoration: none; color: #000000; font-weight: 800;">
                <span style="color: #dc2626;">BRUNO</span> HEIGHTS VENTURES
            </a>
        </div>

        <!-- Desktop Nav -->
        <nav class="desktop-nav">
            <a href="#about">About</a>
            <div class="nav-dropdown">
                <a href="#" @click.prevent style="margin-left: 30px;">Tourism <i class="fas fa-chevron-down" style="font-size: 0.7rem; margin-left: 5px; opacity: 0.7;"></i></a>
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
        <div class="mobile-nav" x-show="mobileMenuOpen" 
             x-transition:enter="transition ease-out duration-400" 
             x-transition:enter-start="opacity-0 -translate-y-10" 
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-300" 
             x-transition:leave-start="opacity-100 translate-y-0" 
             x-transition:leave-end="opacity-0 -translate-y-10"
             x-cloak>
            <a href="#about" @click="mobileMenuOpen = false">About Us</a>
            <div x-data="{ tourOpen: false }" style="border-bottom: 1px solid #f1f5f9;">
                <a @click="tourOpen = !tourOpen" style="display: flex; justify-content: space-between; align-items: center; cursor: pointer; font-size: 1.5rem; font-weight: 800; font-family: 'Outfit', sans-serif; color: var(--secondary); padding: 15px 0; border: none;">
                    Tourism <i class="fas" :class="tourOpen ? 'fa-chevron-up' : 'fa-chevron-down'" style="font-size: 1.1rem; opacity: 0.5;"></i>
                </a>
                <div x-show="tourOpen" x-cloak 
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 transform -translate-y-2"
                     x-transition:enter-end="opacity-100 transform translate-y-0"
                     style="padding-left: 15px; border-left: 3px solid var(--accent); margin: 0 0 15px 5px; display: flex; flex-direction: column; gap: 5px;">
                    <a href="{{ route('tourism.destinations') }}" @click="mobileMenuOpen = false" style="font-size: 1.2rem; font-weight: 600; padding: 10px 0; border: none; display: flex; align-items: center; gap: 10px; color: var(--secondary);">
                        <i class="fas fa-map-marked-alt" style="color: var(--accent); font-size: 1rem; width: 20px;"></i> Destination tours
                    </a>
                    <a href="{{ route('tourism.group-tours') }}" @click="mobileMenuOpen = false" style="font-size: 1.2rem; font-weight: 600; padding: 10px 0; border: none; display: flex; align-items: center; gap: 10px; color: var(--secondary);">
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

    <!-- Hero Slider -->
    <section class="hero-slider">
        <div class="swiper">
            <div class="swiper-wrapper">
                @forelse($slides as $slide)
                <div class="swiper-slide">
                    <div class="slide-bg" style="background-image: url('{{ asset('storage/' . $slide->image_path) }}')"></div>
                    <div class="slide-overlay"></div>
                    <div class="slide-content">
                        <div class="animate-fade-up">
                            <h1 class="font-heading" style="font-size: clamp(2.5rem, 8vw, 5rem); font-weight: 800; line-height: 1.1; margin-bottom: 20px; color: white;">{!! nl2br(e($slide->title)) !!}</h1>
                            <p style="font-size: 1.25rem; max-width: 700px; margin: 0 auto 40px; opacity: 0.9;">{{ $slide->subtitle }}</p>
                            @if($slide->button_text)
                            <div class="hero-btns">
                                <a href="{{ $slide->button_link ?? '#' }}" class="btn btn-accent">{{ $slide->button_text }}</a>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @empty
                <!-- Fallback Slide -->
                <div class="swiper-slide">
                    <div class="slide-bg" style="background-image: url('https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?auto=format&fit=crop&q=80&w=2070')"></div>
                    <div class="slide-overlay"></div>
                    <div class="slide-content">
                        <div class="animate-fade-up">
                            <h1 class="font-heading" style="font-size: clamp(2.5rem, 8vw, 5rem); font-weight: 800; line-height: 1.1; margin-bottom: 20px; color: white;">Defining Premium<br>Excellence in Service</h1>
                            <p style="font-size: 1.25rem; max-width: 700px; margin: 0 auto 40px; opacity: 0.9;">Your ultimate partner for luxury tourism and professional car hiring services.</p>
                            <div class="hero-btns">
                                <a href="#tourism" class="btn btn-accent">Explore Our Ventures</a>
                            </div>
                        </div>
                    </div>
                </div>
                @endforelse
            </div>
            <!-- Navigation -->
            <div class="swiper-pagination"></div>
        </div>
    </section>

    <style>
        .hero-slider {
            height: 100vh;
            width: 100%;
            position: relative;
            overflow: hidden;
        }
        .swiper {
            width: 100%;
            height: 100%;
        }
        .swiper-slide {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
        }
        .slide-bg {
            position: absolute;
            inset: 0;
            background-size: cover;
            background-position: center;
            z-index: 1;
        }
        .slide-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(to bottom, rgba(15, 23, 42, 0.4), rgba(15, 23, 42, 0.7));
            z-index: 2;
        }
        .slide-content {
            position: relative;
            z-index: 3;
            padding: 0 20px;
            width: 100%;
            max-width: 1200px;
        }
        .services-grid {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 30px;
            width: 100%;
        }
        .services-grid > * {
            width: 300px;
            flex: 0 0 300px;
        }
        @media (max-width: 640px) {
            .services-grid > * {
                flex: 0 0 100%;
                width: 100%;
            }
        }
    </style>

    @if(($settings['show_ventures'] ?? '1') == '1')
    <section class="services-overview" style="padding: 100px 20px; background: white;">
        <div class="container" style="max-width: 1300px; margin: 0 auto; text-align: center;">
            <h2 class="font-heading" style="font-size: 2.5rem; margin-bottom: 50px;">{{ $settings['services_heading'] ?? 'Explore Our Ventures' }}</h2>
            <div class="services-grid">
                <!-- Venture 1: Destinations -->
                <div class="venture-card">
                    <div @click="window.location.href = '{{ route('tourism.destinations') }}'" 
                         style="height: 180px; background: url('{{ isset($settings['venture_1_image']) ? asset('storage/' . $settings['venture_1_image']) : 'https://images.unsplash.com/photo-1530789253516-ad160829c9ad?auto=format&fit=crop&q=80&w=1000' }}') center/cover; border-radius: 20px; margin-bottom: 20px; cursor: pointer; transition: transform 0.3s ease;" onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'"></div>
                    <h3 class="font-heading" style="font-size: 1.25rem;">{{ $settings['venture_1_title'] ?? 'On-Demand Destinations' }}</h3>
                    <p style="color: var(--text-slate); margin: 10px 0 20px; font-size: 0.9rem; line-height: 1.5;">{{ $settings['venture_1_desc'] ?? 'Bespoke tour packages for individuals and corporate groups.' }}</p>
                    <a href="{{ route('tourism.destinations') }}" style="color: var(--primary); font-weight: 700; text-decoration: none; font-size: 0.85rem;">View Destinations <i class="fas fa-arrow-right" style="font-size: 0.7rem;"></i></a>
                </div>

                <!-- Venture 2: Group Tours -->
                <div class="venture-card">
                    <div @click="window.location.href = '{{ route('tourism.group-tours') }}'" 
                         style="height: 180px; background: url('{{ isset($settings['venture_2_image']) ? asset('storage/' . $settings['venture_2_image']) : 'https://images.unsplash.com/photo-1533105079780-92b9be482077?auto=format&fit=crop&q=80&w=1000' }}') center/cover; border-radius: 20px; margin-bottom: 20px; cursor: pointer; transition: transform 0.3s ease;" onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'"></div>
                    <h3 class="font-heading" style="font-size: 1.25rem;">{{ $settings['venture_2_title'] ?? 'Organized Tours' }}</h3>
                    <p style="color: var(--text-slate); margin: 10px 0 20px; font-size: 0.9rem; line-height: 1.5;">{{ $settings['venture_2_desc'] ?? 'Join our organized group adventures with set departure dates.' }}</p>
                    <a href="{{ route('tourism.group-tours') }}" style="color: var(--accent); font-weight: 700; text-decoration: none; font-size: 0.85rem;">View Organized Tours <i class="fas fa-arrow-right" style="font-size: 0.7rem;"></i></a>
                </div>

                <!-- Venture 3: Car Hiring Services -->
                <div class="venture-card">
                    <div @click="window.location.href = '{{ route('car-hiring') }}'"
                         style="height: 180px; background: url('{{ isset($settings['venture_3_image']) ? asset('storage/' . $settings['venture_3_image']) : 'https://images.unsplash.com/photo-1549317661-bd32c8ce0db2?auto=format&fit=crop&q=80&w=1000' }}') center/cover; border-radius: 20px; margin-bottom: 20px; cursor: pointer; transition: transform 0.3s ease;" onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'"></div>
                    <h3 class="font-heading" style="font-size: 1.25rem;">{{ $settings['venture_3_title'] ?? 'Car Hiring Services' }}</h3>
                    <p style="color: var(--text-slate); margin: 10px 0 20px; font-size: 0.9rem; line-height: 1.5;">{{ $settings['venture_3_desc'] ?? 'Professional fleet management and vehicle rentals for all needs.' }}</p>
                    <a href="{{ route('car-hiring') }}" style="color: var(--primary); font-weight: 700; text-decoration: none; font-size: 0.85rem;">Browse Our Fleet <i class="fas fa-arrow-right" style="font-size: 0.7rem;"></i></a>
                </div>

                <!-- Venture 4: Transfer Services -->
                <div class="venture-card">
                    <div @click="window.location.href = '{{ route('transfer-services') }}'"
                         style="height: 180px; background: url('{{ isset($settings['venture_4_image']) ? asset('storage/' . $settings['venture_4_image']) : 'https://images.unsplash.com/photo-1436491865332-7a61a109cc05?auto=format&fit=crop&q=80&w=1000' }}') center/cover; border-radius: 20px; margin-bottom: 20px; cursor: pointer; transition: transform 0.3s ease;" onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'"></div>
                    <h3 class="font-heading" style="font-size: 1.25rem;">{{ $settings['venture_4_title'] ?? 'Transfer Services' }}</h3>
                    <p style="color: var(--text-slate); margin: 10px 0 20px; font-size: 0.9rem; line-height: 1.5;">{{ $settings['venture_4_desc'] ?? 'Reliable airport pickups, drop-offs, and city-to-city transfers.' }}</p>
                    <a href="{{ route('transfer-services') }}" style="color: var(--accent); font-weight: 700; text-decoration: none; font-size: 0.85rem;">Book a Transfer <i class="fas fa-arrow-right" style="font-size: 0.7rem;"></i></a>
                </div>
            </div>
        </div>
    </section>
    @endif

    @if(($settings['show_destinations'] ?? '1') == '1')
    <!-- Explore Destinations Section -->
    <section id="tourism" style="padding: 100px 20px; background: white;">
        <div class="container" style="max-width: 1300px; margin: 0 auto;">
            <div style="text-align: center; margin-bottom: 60px;">
                <h2 class="font-heading" style="font-size: 2.5rem; margin-bottom: 15px;">{{ $settings['destinations_title'] ?? 'Explore Our Destinations' }}</h2>
                <p style="color: var(--text-slate); max-width: 600px; margin: 0 auto;">{{ $settings['destinations_subtitle'] ?? 'Bespoke tour packages available for individuals and corporate groups. Choose your destination and let us handle the rest.' }}</p>
            </div>

            <div class="services-grid">
                @forelse($fixedPackages as $package)
                <div class="tour-card" @click="openBooking({{ json_encode($package) }}, 'tourism')" style="position: relative; height: 420px; border-radius: 25px; overflow: hidden; box-shadow: 0 15px 35px rgba(0,0,0,0.1); cursor: pointer; border: none;">
                    <div style="position: absolute; inset: 0; background: url('{{ $package->image ? asset('storage/' . $package->image) : 'https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?auto=format&fit=crop&q=80&w=1000' }}') center/cover; transition: transform 0.6s ease;" class="hover-scale"></div>
                    <div style="position: absolute; inset: 0; background: linear-gradient(to bottom, rgba(0,0,0,0.2) 0%, rgba(0,0,0,0.5) 40%, rgba(0,0,0,0.95) 100%);"></div>
                    
                    <div style="position: absolute; top: 25px; left: 25px; background: rgba(255,255,255,0.2); backdrop-filter: blur(10px); color: white; padding: 6px 15px; border-radius: 50px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; border: 1px solid rgba(255,255,255,0.3);">
                        {{ $package->category->name ?? 'Destination' }}
                    </div>

                    <div style="position: absolute; inset: 0; padding: 30px; display: flex; flex-direction: column; justify-content: flex-end; color: white;">
                        <h3 class="font-heading" style="font-size: 1.8rem; margin-bottom: 10px; line-height: 1.2;">{{ $package->title }}</h3>
                        <p style="opacity: 0.8; font-size: 0.85rem; margin-bottom: 20px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                            {{ Str::limit($package->description, 100) }}
                        </p>
                        
                        <div style="display: flex; gap: 15px; margin-bottom: 20px; font-size: 0.8rem; opacity: 0.9;">
                            <span><i class="fas fa-clock" style="margin-right: 5px;"></i>{{ $package->duration }}</span>
                            <span><i class="fas fa-map-marker-alt" style="margin-right: 5px;"></i>{{ $package->location }}</span>
                        </div>

                        <div style="padding-top: 15px; border-top: 1px solid rgba(255,255,255,0.2);">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                                <div style="min-width: 0;">
                                    <span style="font-size: 0.55rem; opacity: 0.7; display: block; text-transform: uppercase; letter-spacing: 0.5px;">Per Person</span>
                                    <span style="font-size: 1.25rem; font-weight: 800; color: var(--accent); line-height: 1;">₵{{ number_format($package->price, 2) }}</span>
                                </div>
                                <button type="button" @click.stop="openItinerary({{ json_encode($package) }})" class="btn" style="padding: 6px 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.4); background: rgba(255,255,255,0.1); color: white; font-size: 0.65rem; font-weight: 700; cursor: pointer; white-space: nowrap; transition: all 0.2s;">View Itinerary</button>
                            </div>
                            <button type="button" @click.stop="openBooking({{ json_encode($package) }}, 'tourism')" class="btn btn-accent" style="width: 100%; padding: 10px; border-radius: 12px; font-weight: 800; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px;">Book Now</button>
                        </div>
                    </div>
                </div>
                @empty
                <div style="grid-column: 1 / -1; text-align: center; padding: 40px;">
                    <p style="color: var(--text-slate);">No destinations available at the moment.</p>
                </div>
                @endforelse
            </div>

            <div style="text-align: center; margin-top: 50px;">
                <a href="{{ route('tourism.destinations') }}" class="btn btn-primary" style="padding: 15px 40px; border-radius: 15px; font-size: 1rem; font-weight: 800; text-decoration: none; box-shadow: 0 10px 20px rgba(30, 58, 138, 0.15);">
                    View All Destinations <i class="fas fa-arrow-right" style="margin-left: 10px;"></i>
                </a>
            </div>
        </div>
    </section>
    @endif

    @if(($settings['show_scheduled_tours'] ?? '1') == '1')
    <section id="scheduled-tours" style="padding: 100px 20px; background: #f8fafc; border-top: 1px solid #f1f5f9;">
        <div class="container" style="max-width: 1300px; margin: 0 auto;">
            <div style="text-align: center; margin-bottom: 60px;">
                <span style="color: var(--accent); font-weight: 700; text-transform: uppercase; letter-spacing: 2px; font-size: 0.9rem; display: block; margin-bottom: 10px;">{{ $settings['scheduled_tours_badge'] ?? "Don't Miss Out" }}</span>
                <h2 class="font-heading" style="font-size: 2.5rem; margin-bottom: 15px;">{{ $settings['scheduled_tours_title'] ?? 'Upcoming Group Tours' }}</h2>
                <p style="color: var(--text-slate); max-width: 600px; margin: 0 auto;">{{ $settings['scheduled_tours_subtitle'] ?? 'Join our organized group adventures. Perfect for meeting new people and shared experiences.' }}</p>
            </div>

            <div class="services-grid">
                @forelse($scheduledPackages as $package)
                <div class="tour-card" @click="openBooking({{ json_encode($package) }}, 'tourism')" style="position: relative; height: 500px; border-radius: 30px; overflow: hidden; box-shadow: 0 15px 35px rgba(0,0,0,0.1); cursor: pointer; border: none; {{ count($scheduledPackages) === 1 ? 'flex: 0 0 100%; max-width: 100%;' : '' }}">
                    <div style="position: absolute; inset: 0; background: url('{{ $package->image ? asset('storage/' . $package->image) : 'https://images.unsplash.com/photo-1533105079780-92b9be482077?auto=format&fit=crop&q=80&w=1000' }}') center/cover; transition: transform 0.6s ease;" class="hover-scale"></div>
                    <div style="position: absolute; inset: 0; background: linear-gradient(to bottom, rgba(0,0,0,0.2) 0%, rgba(0,0,0,0.5) 40%, rgba(0,0,0,0.95) 100%);"></div>
                    
                    <div style="position: absolute; top: 20px; right: 20px; background: var(--accent); color: white; padding: 8px 15px; border-radius: 12px; font-weight: 800; z-index: 10; text-align: center; box-shadow: 0 10px 20px rgba(0,0,0,0.2);">
                        <span style="font-size: 0.6rem; display: block; text-transform: uppercase; opacity: 0.9;">DEPARTING</span>
                        <span style="font-size: 1rem;">
                            {{ $package->departure_date->format('M d') }}{{ $package->return_date ? ' - ' . ($package->return_date->format('M') == $package->departure_date->format('M') ? $package->return_date->format('d') : $package->return_date->format('M d')) : '' }}
                        </span>
                    </div>

                    <div style="position: absolute; inset: 0; padding: 25px; display: flex; flex-direction: column; justify-content: flex-end; color: white;">
                        <h3 class="font-heading" style="font-size: 1.6rem; margin-bottom: 10px; line-height: 1.2;">
                            {{ $package->title }}
                            @if($package->is_full)
                                <span style="color: #ef4444; font-size: 0.8rem; margin-left: 8px;">(Full)</span>
                            @endif
                        </h3>
                        <p style="opacity: 0.8; font-size: 0.85rem; margin-bottom: 20px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.5;">
                            {{ $package->description }}
                        </p>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 20px; font-size: 0.75rem; opacity: 0.9;">
                            <span><i class="fas fa-clock" style="margin-right: 5px;"></i>{{ $package->duration }}</span>
                            <span><i class="fas fa-location-dot" style="margin-right: 5px;"></i>{{ $package->location }}</span>
                            <span><i class="fas fa-users" style="margin-right: 5px;"></i>{{ $package->registered_guests }}/{{ $package->max_guests }} ({{ $package->available_spaces }} left)</span>
                            <span><i class="fas fa-shield-heart" style="margin-right: 5px;"></i>Safe Travel</span>
                        </div>

                        <div style="padding-top: 15px; border-top: 1px solid rgba(255,255,255,0.2);">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                                <div style="min-width: 0;">
                                    <span style="font-size: 0.55rem; opacity: 0.7; display: block; text-transform: uppercase; letter-spacing: 0.5px;">Per Person</span>
                                    <span style="font-size: 1.3rem; font-weight: 800; color: var(--accent); line-height: 1;">₵{{ number_format($package->price, 2) }}</span>
                                </div>
                                <button type="button" @click.stop="openItinerary({{ json_encode($package) }})" class="btn" style="padding: 6px 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.4); background: rgba(255,255,255,0.1); color: white; font-size: 0.65rem; font-weight: 700; cursor: pointer; white-space: nowrap; transition: all 0.2s;">View Itinerary</button>
                            </div>
                            <button type="button" @click.stop="openBooking({{ json_encode($package) }}, 'tourism')" 
                                    class="btn {{ $package->organized_status === 'ongoing' ? 'btn-secondary' : (($package->is_full || $package->is_booking_cutoff_reached) && (!session('specialBooking') || session('specialBooking')['package']['id'] != $package->id) ? 'btn-secondary' : 'btn-accent') }}" 
                                    style="width: 100%; padding: 10px; border-radius: 12px; font-weight: 800; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; box-shadow: 0 10px 20px {{ $package->organized_status === 'ongoing' || (($package->is_full || $package->is_booking_cutoff_reached) && (!session('specialBooking') || session('specialBooking')['package']['id'] != $package->id)) ? 'rgba(0,0,0,0.1)' : 'rgba(245, 158, 11, 0.3)' }};">
                                @if($package->organized_status === 'ongoing')
                                    Booking Closed (Ongoing)
                                @elseif($package->is_full || $package->is_booking_cutoff_reached)
                                    @if(session('specialBooking') && session('specialBooking')['package']['id'] == $package->id)
                                        Join Tour
                                    @else
                                        {{ $package->is_booking_cutoff_reached ? 'Booking Closed - Interest' : 'Tour Full - Request' }}
                                    @endif
                                @else
                                    Join Tour
                                @endif
                            </button>
                        </div>
                    </div>
                </div>
                @empty
                <div style="grid-column: 1 / -1; text-align: center; padding: 60px; background: white; border-radius: 20px; border: 2px dashed #e2e8f0;">
                    <i class="fas fa-bullhorn" style="font-size: 3rem; color: #e2e8f0; margin-bottom: 20px;"></i>
                    <p style="color: var(--text-slate);">Stay tuned! We'll announce our next group adventure soon.</p>
                </div>
                @endforelse
            </div>

            <div style="text-align: center; margin-top: 50px;">
                <a href="{{ route('tourism.group-tours') }}" class="btn" style="background: var(--accent); color: white; padding: 15px 40px; border-radius: 15px; font-size: 1rem; font-weight: 800; text-decoration: none; box-shadow: 0 10px 20px rgba(245, 158, 11, 0.15);">
                    View All Group Tours <i class="fas fa-arrow-right" style="margin-left: 10px;"></i>
                </a>
            </div>
        </div>
    </section>
    @endif

    @if(($settings['show_fleet'] ?? '1') == '1')
    <!-- Car Hiring Section -->
    <section id="car-hiring" style="padding: 100px 20px; background: white; border-top: 1px solid #f1f5f9;">
        <div class="container" style="max-width: 1300px; margin: 0 auto;">
            <div style="text-align: center; margin-bottom: 60px;">
                <h2 class="font-heading" style="font-size: 2.5rem; margin-bottom: 15px;">{{ $settings['fleet_title'] ?? 'Our Premium Fleet' }}</h2>
                <p style="color: var(--text-slate); max-width: 600px; margin: 0 auto;">{{ $settings['fleet_subtitle'] ?? 'Choose from our range of well-maintained vehicles for your personal or corporate travel needs.' }}</p>
            </div>

            <div class="services-grid">
                @forelse($vehicles as $vehicle)
                <div class="vehicle-card" style="background: var(--bg-card); border-radius: 20px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.05); border: 1px solid var(--border); transition: all 0.3s ease; position: relative;">
                    <div style="height: 160px; background: url('{{ $vehicle->image ? asset('storage/' . $vehicle->image) : 'https://images.unsplash.com/photo-1549317661-bd32c8ce0db2?auto=format&fit=crop&q=80&w=1000' }}') center/cover; position: relative;">
                        @if($vehicle->status !== 'available')
                            <div style="position: absolute; top: 15px; right: 15px; background: #ef4444; color: white; padding: 4px 12px; border-radius: 8px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase;">Booked</div>
                            <div style="position: absolute; inset: 0; background: rgba(255,255,255,0.2); backdrop-filter: grayscale(1);"></div>
                        @else
                            <div style="position: absolute; top: 15px; right: 15px; background: #22c55e; color: white; padding: 4px 12px; border-radius: 8px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase;">Available</div>
                        @endif
                    </div>
                    <div style="padding: 25px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                            <span style="font-size: 0.8rem; font-weight: 700; color: var(--primary); text-transform: uppercase; letter-spacing: 1px;">{{ $vehicle->vehicleType->name ?? 'Vehicle' }}</span>
                            <span style="font-size: 0.9rem; color: var(--text-muted);"><i class="fas fa-users" style="margin-right: 5px;"></i>{{ $vehicle->seating_capacity }} Seats</span>
                        </div>
                        <h3 class="font-heading" style="font-size: 1.3rem; margin-bottom: 20px; color: var(--text-main);">{{ $vehicle->make }} {{ $vehicle->model }}</h3>
                        


                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; padding: 15px 0; border-top: 1px solid var(--border); margin-top: 10px;">
                            <div>
                                <span style="font-size: 0.75rem; color: var(--text-muted); display: block;">Hourly Rate</span>
                                <span style="font-size: 1.1rem; font-weight: 700; color: var(--primary);">₵{{ number_format($vehicle->vehicleType->base_hourly_rate ?? 0, 2) }}</span>
                            </div>
                            <div style="text-align: right;">
                                <span style="font-size: 0.75rem; color: var(--text-muted); display: block;">Daily Rate</span>
                                <span style="font-size: 1.1rem; font-weight: 700; color: var(--primary);">₵{{ number_format($vehicle->vehicleType->base_daily_rate ?? 0, 2) }}</span>
                            </div>
                        </div>

                        <div style="margin-top: 20px;">
                            @if($vehicle->status === 'available')
                                <button type="button" @click="openBooking({{ json_encode($vehicle->load('vehicleType')) }}, 'fleet')" class="btn btn-primary" style="width: 100%; padding: 12px; border-radius: 12px; font-weight: 800;">Rent Now</button>
                            @else
                                <button class="btn btn-secondary" disabled style="width: 100%; padding: 12px; border-radius: 12px; cursor: not-allowed; opacity: 0.7; font-weight: 800;">Unavailable</button>
                            @endif
                        </div>
                    </div>
                </div>
                @empty
                <div style="grid-column: 1 / -1; text-align: center; padding: 40px;">
                    <p style="color: var(--text-slate);">No vehicles currently available for rent.</p>
                </div>
                @endforelse
            </div>

            <div style="text-align: center; margin-top: 50px;">
                <a href="{{ route('car-hiring') }}" class="btn btn-primary" style="padding: 15px 40px; border-radius: 15px; font-size: 1rem; font-weight: 800; text-decoration: none; box-shadow: 0 10px 20px rgba(30, 58, 138, 0.15);">
                    See all Car Hiring <i class="fas fa-arrow-right" style="margin-left: 10px;"></i>
                </a>
            </div>
        </div>
    </section>
    @endif

    @if(($settings['show_transfers'] ?? '1') == '1')
    <!-- Airport Transfers Section -->
    <section id="airport-transfers" style="padding: 100px 20px; background: #f8fafc; border-top: 1px solid #f1f5f9;">
        <div class="container" style="max-width: 1300px; margin: 0 auto;">
            <div style="text-align: center; margin-bottom: 60px;">
                <span style="color: var(--accent); font-weight: 700; text-transform: uppercase; letter-spacing: 2px; font-size: 0.9rem; display: block; margin-bottom: 10px;">{{ $settings['transfers_badge'] ?? 'Seamless Travel' }}</span>
                <h2 class="font-heading" style="font-size: 2.5rem; margin-bottom: 15px;">{{ $settings['transfers_title'] ?? 'Airport Transfers' }}</h2>
                <p style="color: var(--text-slate); max-width: 600px; margin: 0 auto;">{{ $settings['transfers_subtitle'] ?? 'Reliable, comfortable, and fixed-rate point-to-point transfer services. Whether it\'s an airport run or a trip across town, we\'ve got you covered.' }}</p>
            </div>

            <div class="services-grid">
                @forelse($transfers as $transfer)
                <div class="transfer-card" style="background: white; border-radius: 20px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.05); position: relative; border: 1px solid var(--border);">
                    <div style="padding: 25px; text-align: center;">
                        <div style="position: absolute; top: 20px; right: 20px; background: var(--accent); color: white; padding: 5px 12px; border-radius: 10px; font-weight: 700; z-index: 10; text-transform: capitalize; font-size: 0.65rem;">
                            {{ $transfer->transfer_type === 'both' ? 'Pickup & Drop-off' : $transfer->transfer_type . ' Only' }}
                        </div>
                        <div style="display: flex; align-items: center; justify-content: center; width: 45px; height: 45px; background: rgba(30, 58, 138, 0.1); color: var(--primary); border-radius: 50%; margin: 0 auto 15px; font-size: 1.2rem;">
                            <i class="fas fa-plane-{{ $transfer->transfer_type === 'pickup' ? 'arrival' : ($transfer->transfer_type === 'dropoff' ? 'departure' : 'up') }}"></i>
                        </div>
                        
                        <h3 class="font-heading" style="font-size: 1.2rem; margin-bottom: 8px;">{{ $transfer->airport_name }}</h3>
                        <p style="color: var(--text-slate); font-size: 0.85rem; margin-bottom: 15px;">
                            <i class="fas fa-map-marker-alt" style="color: var(--accent); margin-right: 5px;"></i> {{ $transfer->location }}
                        </p>
                        
                        @if($transfer->description)
                        <p style="color: var(--text-muted); font-size: 0.8rem; margin-bottom: 15px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.5;">
                            {{ $transfer->description }}
                        </p>
                        @endif
                        
                        <div style="display: flex; align-items: center; justify-content: center; gap: 8px; margin-bottom: 20px; padding: 8px; background: var(--bg-main); border-radius: 10px; font-size: 0.8rem; color: var(--text-main);">
                            <i class="fas fa-car" style="color: var(--primary);"></i>
                            <span><strong>{{ $transfer->vehicle ? $transfer->vehicle->make . ' ' . $transfer->vehicle->model : ($transfer->vehicleType->name ?? 'Standard') }}</strong></span>
                        </div>

                        <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; text-align: left;">
                            <div>
                                <span style="font-size: 0.65rem; color: var(--text-slate); display: block; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Fixed Rate</span>
                                <span style="font-size: 1.3rem; font-weight: 800; color: var(--primary); line-height: 1;">₵{{ number_format($transfer->price, 2) }}</span>
                            </div>
                            <button type="button" @click="openBooking({{ json_encode($transfer->load(['vehicle.vehicleType', 'vehicleType'])) }}, 'transfer')" class="btn btn-primary" style="padding: 8px 15px; border-radius: 10px; font-weight: 800; font-size: 0.75rem; border: none; cursor: pointer; white-space: nowrap; transition: all 0.2s;">Book Transfer</button>
                        </div>
                    </div>
                </div>
                @empty
                <div style="grid-column: 1 / -1; text-align: center; padding: 60px; background: white; border-radius: 20px; border: 2px dashed #e2e8f0;">
                    <i class="fas fa-plane-slash" style="font-size: 3rem; color: #e2e8f0; margin-bottom: 20px;"></i>
                    <p style="color: var(--text-slate);">No airport transfer services are currently listed.</p>
                </div>
                @endforelse
            </div>

            <div style="text-align: center; margin-top: 50px;">
                <a href="{{ route('transfer-services') }}" class="btn btn-primary" style="padding: 15px 40px; border-radius: 15px; font-size: 1rem; font-weight: 800; text-decoration: none; box-shadow: 0 10px 20px rgba(30, 58, 138, 0.15);">
                    See all Transfer Services <i class="fas fa-arrow-right" style="margin-left: 10px;"></i>
                </a>
            </div>
        </div>
    </section>
    @endif

    <!-- About Section (Managed by CMS) -->
    <section id="about" style="padding: 100px 20px; background: var(--bg-light);">
        <div class="container" style="max-width: 900px; margin: 0 auto; text-align: center;">
            <h2 class="font-heading" style="font-size: 2.5rem; margin-bottom: 30px;">{{ $settings['about_title'] ?? 'About Bruno Heights Ventures' }}</h2>
            <p style="font-size: 1.2rem; color: var(--text-slate); line-height: 1.8;">{!! nl2br(e($settings['about_content'] ?? 'Experience the ultimate blend of luxury tourism and seamless transportation services across Ghana and beyond.')) !!}</p>
        </div>
    </section>

    <script>
        window.addEventListener('scroll', function() {
            const header = document.getElementById('header');
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });

        // Initialize Swiper
        const swiper = new Swiper('.swiper', {
            loop: true,
            effect: 'fade',
            speed: 1000,
            autoplay: {
                delay: 5000,
                disableOnInteraction: false,
            },
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
        });
    </script>

    <!-- Ongoing Tour Banner -->
    @if(isset($ongoingTour) && $ongoingTour)
    <div class="ongoing-tour-banner" style="position: fixed; bottom: 30px; right: 30px; z-index: 1000; display: flex; align-items: center; gap: 15px; background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.5); padding: 12px 20px; border-radius: 20px; box-shadow: 0 15px 35px rgba(0,0,0,0.15); animation: bannerSlideIn 0.8s cubic-bezier(0.34, 1.56, 0.64, 1); cursor: pointer;" @click="openItinerary({{ json_encode($ongoingTour) }})">
        <div style="position: relative;">
            <div style="width: 55px; height: 55px; border-radius: 14px; background: url('{{ $ongoingTour->image ? asset('storage/' . $ongoingTour->image) : 'https://images.unsplash.com/photo-1533105079780-92b9be482077?auto=format&fit=crop&q=80&w=100' }}') center/cover; box-shadow: 0 4px 10px rgba(0,0,0,0.1);"></div>
            <div style="position: absolute; top: -4px; right: -4px; width: 14px; height: 14px; background: #ef4444; border: 2.5px solid white; border-radius: 50%; animation: livePulse 2s infinite; z-index: 2;"></div>
        </div>
        <div>
            <div style="display: flex; align-items: center; gap: 6px; margin-bottom: 3px;">
                <span style="font-size: 0.6rem; font-weight: 800; color: #ef4444; letter-spacing: 1.5px; text-transform: uppercase;">Ongoing Tour</span>
                <span style="width: 4px; height: 4px; background: #cbd5e1; border-radius: 50%;"></span>
                <span style="font-size: 0.65rem; font-weight: 700; color: var(--primary);">Live Now</span>
            </div>
            <div style="font-weight: 800; font-size: 1rem; color: var(--primary); line-height: 1.1; font-family: 'Outfit', sans-serif;">{{ $ongoingTour->title }}</div>
            <div style="font-size: 0.8rem; color: var(--text-slate); margin-top: 3px; font-weight: 500;"><i class="fas fa-location-dot" style="margin-right: 5px; font-size: 0.7rem; color: var(--accent);"></i>{{ $ongoingTour->location }}</div>
        </div>
        <div class="banner-arrow" style="width: 32px; height: 32px; background: var(--primary); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-left: 5px; font-size: 0.8rem; transition: transform 0.3s ease;">
            <i class="fas fa-arrow-right"></i>
        </div>
    </div>
    <style>
        @keyframes bannerSlideIn {
            from { transform: translateX(120%) scale(0.9); opacity: 0; }
            to { transform: translateX(0) scale(1); opacity: 1; }
        }
        @keyframes livePulse {
            0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.8); }
            70% { box-shadow: 0 0 0 8px rgba(239, 68, 68, 0); }
            100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
        }
        .ongoing-tour-banner { transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
        .ongoing-tour-banner:hover {
            transform: translateY(-8px) scale(1.02);
            background: white;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }
        .ongoing-tour-banner:hover .banner-arrow { transform: translateX(4px); }
        @media (max-width: 640px) {
            .ongoing-tour-banner { bottom: 20px; right: 20px; left: 20px; width: auto; justify-content: space-between; }
        }
    </style>
    @endif
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
            }, 500);
        });
    </script>
    @endif
</body>

</html>
