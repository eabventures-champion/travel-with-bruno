@extends('admin::layouts.master')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>Upcoming Organized Tours</h1>
        <p>Join a group adventure on our scheduled upcoming trips.</p>
    </div>
</div>

<div style="display: flex; flex-wrap: wrap; gap: 25px; margin-top: 30px;" x-data="{
    bookingOpen: false,
    itineraryOpen: false,
    lightboxOpen: false,
    bookingItem: null,
    guestType: '',
    groupName: '',
    bookingType: 'tourism',
    rentalUnit: 'Hour',
    isSelfDrive: false,
    hours: 1,
    notes: '',
    customerName: '{{ auth()->user()->name }}',
    customerEmail: '{{ auth()->user()->email }}',
    customerPhone: '{{ auth()->user()->phone }}',
    customerCountry: 'Ghana',
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

    openBooking(item, type) {
        this.bookingItem = item;
        this.bookingType = type;
        this.groupName = '';
        this.rentalUnit = 'Hour';
        this.isSelfDrive = false;
        this.interestDuplicateError = '';
        
        // For scheduled tours, default guest count to 1 but ensure it doesn't exceed available spaces
        if (type === 'tourism' && item.package_type === 'scheduled') {
            this.hours = item.available_spaces > 0 ? 1 : 0;
        } else {
            this.hours = 1;
        }

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
        <div class="card" style="padding: 0; overflow: hidden; border-radius: 20px; background: var(--bg-card); border: 1px solid var(--border); display: flex; flex-direction: column; width: 320px; flex-shrink: 0;">
            <div style="height: 200px; position: relative;">
                <img src="{{ $package->image ? asset('storage/' . $package->image) : 'https://placehold.co/600x400?text=No+Image' }}" 
                     style="width: 100%; height: 100%; object-fit: cover;">
                <div style="position: absolute; top: 15px; left: 15px; padding: 5px 12px; background: #ef4444; color: white; border-radius: 20px; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; letter-spacing: 1px;">
                    <i class="fas fa-calendar-alt"></i> Scheduled
                </div>
            </div>
            <div style="padding: 20px; flex: 1; display: flex; flex-direction: column;">
                <h3 style="font-family: 'Outfit', sans-serif; margin-bottom: 10px; color: var(--text-main);">
                    {{ $package->title }}
                    @if($package->is_full)
                        <span style="color: #ef4444; font-size: 0.75rem; margin-left: 5px;">(Full)</span>
                    @endif
                </h3>
                <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 20px; line-height: 1.5;">{{ $package->short_description }}</p>
                
                <div style="margin-top: auto;">
                    <div style="display: flex; gap: 15px; margin-bottom: 15px; padding: 12px; background: var(--bg-main); border-radius: 12px;">
                        <div style="flex: 1.2;">
                            <div style="font-size: 0.65rem; color: var(--text-muted); font-weight: 800; text-transform: uppercase;">Tour Dates</div>
                            <div style="font-weight: 700; font-size: 0.85rem; color: var(--text-main);">
                                {{ $package->departure_date->format('M d') }}{{ $package->return_date ? ' - ' . ($package->return_date->format('M') == $package->departure_date->format('M') ? $package->return_date->format('M d, Y') : $package->return_date->format('M d, Y')) : $package->departure_date->format(', Y') }}
                                @if(!$package->return_date) {{ $package->departure_date->format('Y') }} @endif
                            </div>
                        </div>
                        <div style="flex: 1; text-align: right;">
                            <div style="font-size: 0.65rem; color: var(--text-muted); font-weight: 800; text-transform: uppercase;">Capacity</div>
                            <div style="font-weight: 800; font-size: 0.9rem; color: {{ $package->is_full ? '#ef4444' : 'var(--primary)' }};">
                                {{ $package->registered_guests ?? 0 }} / {{ $package->max_guests }}
                            </div>
                        </div>
                    </div>
                    
                    <div style="display: flex; gap: 8px;">
                        <button @click="openItinerary({{ $package->load('itineraries') }})" 
                                class="btn btn-secondary" style="flex: 1.2; border-radius: 10px; padding: 10px 8px; font-weight: 700; font-size: 0.75rem; white-space: nowrap;">
                            <i class="fas fa-calendar-alt"></i> Itinerary
                        </button>
                        @if($package->organized_status === 'ongoing')
                            <button class="btn btn-danger" style="flex: 1; border-radius: 10px; padding: 10px 8px; font-weight: 700; font-size: 0.75rem; cursor: not-allowed;" disabled>
                                Ongoing
                            </button>
                        @elseif($package->is_full)
                            <button @click="openBooking({{ $package }}, 'tourism')" 
                                    class="btn btn-primary" style="flex: 1; border-radius: 10px; padding: 10px 8px; font-weight: 700; font-size: 0.75rem; background: #000; border: none;">
                                <i class="fas fa-bullhorn"></i> Interest
                            </button>
                        @else
                            <button @click="openBooking({{ $package }}, 'tourism')" 
                                    class="btn btn-primary" style="flex: 1; border-radius: 10px; padding: 10px 8px; font-weight: 700; font-size: 0.75rem;">
                                <i class="fas fa-users"></i> Join
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div style="grid-column: 1 / -1; padding: 50px; text-align: center; background: var(--bg-card); border-radius: 20px; border: 1px dashed var(--border);">
            <i class="fas fa-calendar-times" style="font-size: 3rem; color: var(--text-muted); margin-bottom: 20px;"></i>
            <h3 style="color: var(--text-main);">No Scheduled Tours</h3>
            <p style="color: var(--text-muted);">Stay tuned for our next group adventure!</p>
        </div>
    @endforelse

    <!-- Booking Modal -->
    @include('partials.tourism-modals')
</div>

<div class="pagination-container" style="margin-top: 30px;">
    {{ $packages->links() }}
</div>
@endsection
