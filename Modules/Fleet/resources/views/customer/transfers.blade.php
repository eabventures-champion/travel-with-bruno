@extends('admin::layouts.master')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>Airport Transfer Services</h1>
        <p>Reliable and comfortable transfers for your travel needs.</p>
    </div>
</div>

<div style="display: flex; flex-wrap: wrap; gap: 25px; margin-top: 30px; justify-content: center;" x-data="{
    bookingOpen: false,
    itineraryOpen: false,
    lightboxOpen: false,
    selectedPackage: null,
    bookingItem: null,
    guestType: '',
    bookingType: 'transfer',
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

    // Transfer Specifics
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
        
        if (type === 'transfer') {
            this.transferTypeSelection = (item.transfer_type !== 'both') ? item.transfer_type : 'pickup';
        }
        
        this.bookingOpen = true;
    },
    
    get totalPrice() {
        if (!this.bookingItem) return 0;
        
        if (this.bookingType === 'transfer') {
            let base = parseFloat(this.bookingItem.price || 0);
            let zone = this.transferZones.find(z => z.id == this.selectedZoneId);
            let extra = zone ? parseFloat(zone.additional_price) : 0;
            let mult = (this.transferTypeSelection === 'both') ? 2 : 1;
            return (base + extra) * mult * this.hours;
        }
        
        return 0;
    }
}">
    @forelse($transfers as $transfer)
        <div class="card responsive-card" style="padding: 0; overflow: hidden; border-radius: 20px; background: var(--bg-card); border: 1px solid var(--border); display: flex; flex-direction: column; transition: all 0.3s ease; position: relative;">
            <div style="position: absolute; top: 15px; right: 15px; background: var(--accent); color: white; padding: 4px 12px; border-radius: 12px; font-weight: 800; z-index: 10; text-transform: capitalize; font-size: 0.65rem;">
                {{ $transfer->transfer_type === 'both' ? 'Pickup & Drop-off' : $transfer->transfer_type . ' Only' }}
            </div>
            
            <div style="padding: 30px; flex: 1; display: flex; flex-direction: column;">
                <div style="display: flex; align-items: center; justify-content: center; width: 50px; height: 50px; background: rgba(30, 58, 138, 0.1); color: var(--primary); border-radius: 50%; margin-bottom: 20px; font-size: 1.5rem;">
                    <i class="fas fa-plane-{{ $transfer->transfer_type === 'pickup' ? 'arrival' : ($transfer->transfer_type === 'dropoff' ? 'departure' : 'up') }}"></i>
                </div>
                
                <h4 style="font-family: 'Outfit', sans-serif; margin: 0 0 10px; color: var(--text-main); font-size: 1.2rem;">{{ $transfer->airport_name }}</h4>
                <div style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 20px;">
                    <i class="fas fa-map-marker-alt" style="color: var(--accent); margin-right: 5px;"></i> {{ $transfer->location }}
                </div>
                
                @if($transfer->description)
                <p style="color: var(--text-muted); font-size: 0.8rem; margin-bottom: 20px; line-height: 1.5; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                    {{ $transfer->description }}
                </p>
                @endif
                
                <div style="margin-top: auto;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding: 15px; background: var(--bg-main); border-radius: 12px;">
                        <div>
                            <div style="font-size: 0.65rem; color: var(--text-muted); font-weight: 800; text-transform: uppercase;">Fixed Rate</div>
                            <div style="font-weight: 800; color: var(--primary); font-size: 1.2rem;">₵{{ number_format($transfer->price, 2) }}</div>
                        </div>
                        <i class="fas fa-chevron-right" style="color: var(--border);"></i>
                    </div>
                    
                    <button @click="openBookingModal({{ json_encode($transfer->load('vehicleType')) }}, 'transfer')" 
                            class="btn btn-primary" style="width: 100%; border-radius: 12px; padding: 12px; font-weight: 700;">
                        <i class="fas fa-calendar-check" style="margin-right: 8px;"></i> Book Transfer
                    </button>
                </div>
            </div>
        </div>
    @empty
        <div style="width: 100%; padding: 50px; text-align: center; background: var(--bg-card); border-radius: 20px; border: 1px dashed var(--border);">
            <i class="fas fa-plane-slash" style="font-size: 3rem; color: var(--text-muted); margin-bottom: 20px;"></i>
            <h3 style="color: var(--text-main);">No Transfers Available</h3>
            <p style="color: var(--text-muted);">Please check back later for available airport transfer routes.</p>
        </div>
    @endforelse

    <!-- Reuse the same modal partial -->
    @include('partials.tourism-modals')
</div>
@endsection
