@extends('admin::layouts.master')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>Car Hiring Services</h1>
        <p>Choose from our premium fleet for your transportation needs.</p>
    </div>
</div>

<div style="display: flex; flex-wrap: wrap; gap: 25px; margin-top: 30px; justify-content: center;" x-data="{
    bookingOpen: false,
    itineraryOpen: false,
    lightboxOpen: false,
    selectedPackage: null,
    bookingItem: null,
    guestType: '',
    bookingType: 'fleet',
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
    
    openBookingModal(item, type) {
        this.bookingItem = item;
        this.bookingType = type;
        this.guestType = '';
        this.rentalUnit = 'Hour';
        this.isSelfDrive = false;
        this.hours = 1;
        this.notes = '';

        // Reset transfer fields
        this.flightNumber = '';
        this.airline = '';
        this.flightTime = '';
        this.terminal = '';
        this.destinationAddress = '';
        this.selectedZoneId = '';
        this.customLocation = '';

        this.bookingOpen = true;
    },
    
    get totalPrice() {
        if (!this.bookingItem) return 0;
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
    @forelse($vehicles as $vehicle)
        <div class="card responsive-card" style="padding: 0; overflow: hidden; border-radius: 20px; background: var(--bg-card); border: 1px solid var(--border); display: flex; flex-direction: column; transition: all 0.3s ease;">
            <div style="height: 180px; position: relative;">
                <img src="{{ $vehicle->image ? asset('storage/' . $vehicle->image) : 'https://images.unsplash.com/photo-1549317661-bd32c8ce0db2?auto=format&fit=crop&q=80&w=1000' }}" 
                     style="width: 100%; height: 100%; object-fit: cover;">
                <div style="position: absolute; top: 15px; right: 15px; padding: 5px 12px; background: var(--accent); color: white; border-radius: 20px; font-size: 0.75rem; font-weight: 800; text-transform: uppercase;">
                    {{ $vehicle->vehicleType->name ?? 'Vehicle' }}
                </div>
            </div>
            <div style="padding: 20px; flex: 1; display: flex; flex-direction: column;">
                <h4 style="font-family: 'Outfit', sans-serif; margin: 0 0 5px; color: var(--text-main); font-size: 1.2rem;">{{ $vehicle->make }} {{ $vehicle->model }}</h4>
                <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase; margin-bottom: 20px; letter-spacing: 0.5px;">{{ $vehicle->year }} • {{ $vehicle->license_plate }}</div>
                
                <div style="margin-top: auto;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; padding: 10px; background: var(--bg-main); border-radius: 12px;">
                        <div>
                            <div style="font-size: 0.65rem; color: var(--text-muted); font-weight: 800; text-transform: uppercase;">Hourly</div>
                            <div style="font-weight: 800; color: var(--primary);">₵{{ number_format($vehicle->vehicleType->base_hourly_rate ?? 0, 2) }}</div>
                        </div>
                        <div style="text-align: right;">
                            <div style="font-size: 0.65rem; color: var(--text-muted); font-weight: 800; text-transform: uppercase;">Daily</div>
                            <div style="font-weight: 800; color: var(--primary);">₵{{ number_format($vehicle->vehicleType->base_daily_rate ?? 0, 2) }}</div>
                        </div>
                    </div>
                    
                    <button @click="openBookingModal({{ json_encode($vehicle->load('vehicleType')) }}, 'fleet')" 
                            class="btn btn-primary" style="width: 100%; border-radius: 12px; padding: 12px; font-weight: 700;">
                        <i class="fas fa-car" style="margin-right: 8px;"></i> Rent Now
                    </button>
                </div>
            </div>
        </div>
    @empty
        <div style="width: 100%; padding: 50px; text-align: center; background: var(--bg-card); border-radius: 20px; border: 1px dashed var(--border);">
            <i class="fas fa-car-side" style="font-size: 3rem; color: var(--text-muted); margin-bottom: 20px;"></i>
            <h3 style="color: var(--text-main);">No Vehicles Available</h3>
            <p style="color: var(--text-muted);">Currently all our vehicles are on trips. Please check back later.</p>
        </div>
    @endforelse

    <!-- Reuse the same modal partial -->
    @include('partials.tourism-modals')
</div>
@endsection
