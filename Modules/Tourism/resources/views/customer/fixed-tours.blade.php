@extends('admin::layouts.master')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>Fixed Tours</h1>
        <p>Explore our curated list of destinations and book your next trip.</p>
    </div>
</div>

<div style="display: flex; flex-wrap: wrap; gap: 25px; margin-top: 30px; justify-content: center;" x-data="{
    bookingOpen: false,
    itineraryOpen: false,
    lightboxOpen: false,
    bookingItem: null,
    guestType: '',
    bookingType: 'tourism',
    rentalUnit: 'Hour',
    isSelfDrive: false,
    hours: 1,
    notes: '',
    customerName: '{{ auth()->user()->name }}',
    customerEmail: '{{ auth()->user()->email }}',
    customerPhone: '{{ auth()->user()->phone }}',
    duplicateError: '',
    interestDuplicateError: '',
    interestToken: '',
    interestPackageId: null,

    // Transfer Specifics (Shared Modal Requirements)
    flightNumber: '',
    airline: '',
    flightTime: '',
    terminal: '',
    destinationAddress: '',
    transferTypeSelection: 'pickup',
    transferZones: @js($transferZones ?? []),
    selectedZoneId: '',
    customLocation: '',

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
                    package_id: this.bookingItem?.id
                })
            });
            const data = await response.json();
            this.interestDuplicateError = data.exists ? data.message : '';
        } catch (e) {
            console.error('Interest duplicate check failed:', e);
        }
    },

    updateGuests(val) {
        if (this.bookingType === 'tourism' && this.bookingItem?.package_type === 'scheduled') {
            const max = this.bookingItem.available_spaces || 0;
            if (val > max) {
                alert(`Sorry, only ${max} spaces are remaining for this tour.`);
                this.hours = max;
                return;
            }
        }
        this.hours = Math.max(1, val);
    },

    openBooking(item, type) {
        this.bookingItem = item;
        this.bookingType = type;
        this.rentalUnit = 'Hour';
        this.isSelfDrive = false;
        this.hours = 1;

        // Reset transfer fields
        this.flightNumber = '';
        this.airline = '';
        this.flightTime = '';
        this.terminal = '';
        this.destinationAddress = '';
        this.selectedZoneId = '';
        this.customLocation = '';

        this.bookingOpen = true;
        this.itineraryOpen = false;
    },

    openItinerary(item) {
        this.bookingItem = item;
        this.itineraryOpen = true;
        this.bookingOpen = false;
        this.activeTab = 'itinerary';
    },
    
    get totalPrice() {
        if (!this.bookingItem) return 0;
        if (this.bookingType === 'tourism') {
            return (this.bookingItem.price || 0) * this.hours;
        }
        
        let rate = 0;
        if (this.rentalUnit === 'Hour') {
            rate = this.bookingItem.vehicle_type?.base_hourly_rate || 0;
        } else if (this.rentalUnit === 'Day') {
            rate = this.bookingItem.vehicle_type?.base_daily_rate || 0;
        } else if (this.rentalUnit === 'Week') {
            rate = (this.bookingItem.vehicle_type?.base_daily_rate || 0) * 7;
        }
        return rate * this.hours;
    }
}">
    @forelse($packages as $package)
        <div class="card responsive-card" style="padding: 0; overflow: hidden; border-radius: 20px; background: var(--bg-card); border: 1px solid var(--border); display: flex; flex-direction: column;">
            <div style="height: 200px; position: relative;">
                <img src="{{ $package->image ? asset('storage/' . $package->image) : 'https://placehold.co/600x400?text=No+Image' }}" 
                     style="width: 100%; height: 100%; object-fit: cover;">
                <div style="position: absolute; top: 15px; right: 15px; padding: 5px 12px; background: var(--accent); color: white; border-radius: 20px; font-size: 0.75rem; font-weight: 800; text-transform: uppercase;">
                    {{ $package->category->name ?? 'Tour' }}
                </div>
            </div>
            <div style="padding: 20px; flex: 1; display: flex; flex-direction: column;">
                <h3 style="font-family: 'Outfit', sans-serif; margin-bottom: 10px; color: var(--text-main);">{{ $package->title }}</h3>
                <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 20px; line-height: 1.5;">{{ $package->short_description }}</p>
                
                <div style="margin-top: auto;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                        <div>
                            <div style="font-size: 0.7rem; color: var(--text-muted); font-weight: 800; text-transform: uppercase;">Price</div>
                            <div style="font-size: 1.25rem; font-weight: 800; color: var(--primary);">₵{{ number_format($package->price, 2) }}</div>
                        </div>
                        <div style="text-align: right;">
                            <div style="font-size: 0.7rem; color: var(--text-muted); font-weight: 800; text-transform: uppercase;">Duration</div>
                            <div style="font-weight: 700; color: var(--text-main);">{{ $package->duration }}</div>
                        </div>
                    </div>
                    
                    <div style="display: flex; gap: 8px;">
                        <button @click="openItinerary({{ $package->load('itineraries') }})" 
                                class="btn btn-secondary" style="flex: 1.2; border-radius: 10px; padding: 10px 8px; font-weight: 700; font-size: 0.75rem; white-space: nowrap;">
                            <i class="fas fa-calendar-alt"></i> Planned Itinerary
                        </button>
                        <button @click="openBooking({{ $package }}, 'tourism')" 
                                class="btn btn-primary" style="flex: 1; border-radius: 10px; padding: 10px 8px; font-weight: 700; font-size: 0.75rem;">
                            <i class="fas fa-calendar-plus"></i> Book Now
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div style="grid-column: 1 / -1; padding: 50px; text-align: center; background: var(--bg-card); border-radius: 20px; border: 1px dashed var(--border);">
            <i class="fas fa-map-marked-alt" style="font-size: 3rem; color: var(--text-muted); margin-bottom: 20px;"></i>
            <h3 style="color: var(--text-main);">No Tours Available</h3>
            <p style="color: var(--text-muted);">Please check back later for new destinations.</p>
        </div>
    @endforelse

    <!-- Booking Modal -->
    @include('partials.tourism-modals')
</div>

<div class="pagination-container" style="margin-top: 30px;">
    {{ $packages->links() }}
</div>
@endsection
