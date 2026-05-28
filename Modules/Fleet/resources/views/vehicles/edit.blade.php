@extends('admin::layouts.master')
@section('title', 'Edit Vehicle')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>Edit Vehicle: {{ $vehicle->make }} {{ $vehicle->model }}</h1>
        <p>Update vehicle details and status.</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.fleet.vehicles.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Fleet
        </a>
    </div>
</div>

<div class="dashboard-card mt-20">
    <form action="{{ route('admin.fleet.vehicles.update', $vehicle->id) }}" method="POST" class="admin-form" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="form-grid">
            <div class="form-group">
                <label for="vehicle_type_id">Vehicle Type</label>
                <select id="vehicle_type_id" name="vehicle_type_id" class="form-control" required>
                    <option value="">Select Type</option>
                    @foreach($types as $type)
                        <option value="{{ $type->id }}" {{ $vehicle->vehicle_type_id == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="make">Make / Brand</label>
                <input type="text" id="make" name="make" class="form-control" value="{{ $vehicle->make }}" required>
            </div>
            <div class="form-group">
                <label for="model">Model</label>
                <input type="text" id="model" name="model" class="form-control" value="{{ $vehicle->model }}" required>
            </div>
            <div class="form-group">
                <label for="year">Year</label>
                <input type="text" id="year" name="year" class="form-control" value="{{ $vehicle->year }}">
            </div>
            <div class="form-group">
                <label for="license_plate">License Plate</label>
                <input type="text" id="license_plate" name="license_plate" class="form-control" value="{{ $vehicle->license_plate }}" required>
            </div>
            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status" class="form-control" required>
                    <option value="available" {{ $vehicle->status == 'available' ? 'selected' : '' }}>Available</option>
                    <option value="on_trip" {{ $vehicle->status == 'on_trip' ? 'selected' : '' }}>On Trip</option>
                    <option value="maintenance" {{ $vehicle->status == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                    <option value="inactive" {{ $vehicle->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="form-group">
                <label for="chauffeur_id">Assigned Chauffeur (Default)</label>
                <select id="chauffeur_id" name="chauffeur_id" class="form-control">
                    <option value="">No Chauffeur Assigned</option>
                    @foreach($chauffeurs as $chauffeur)
                        <option value="{{ $chauffeur->id }}" {{ $vehicle->chauffeur_id == $chauffeur->id ? 'selected' : '' }}>
                            {{ $chauffeur->user->name ?? 'Unknown' }} ({{ $chauffeur->years_of_experience }} yrs exp)
                        </option>
                    @endforeach
                </select>
                <small style="color: var(--text-slate); font-size: 0.8rem;">This chauffeur will be shown to customers by default.</small>
            </div>
            <div class="form-group">
                <label for="image">Vehicle Image (Optional)</label>
                @if($vehicle->image)
                    <div style="margin-bottom: 10px;">
                        <img src="{{ asset('storage/' . $vehicle->image) }}" alt="Vehicle" style="width: 150px; height: 100px; object-fit: cover; border-radius: 8px; border: 1px solid var(--border);">
                    </div>
                @endif
                <input type="file" id="image" name="image" class="form-control" accept="image/*">
                <small style="color: var(--text-slate); font-size: 0.8rem;">Leave blank to keep current image. Supported: JPG, PNG, WEBP.</small>
            </div>
        </div>

        <div style="margin-top: 40px; padding: 25px; background: rgba(30, 58, 138, 0.02); border-radius: 15px; border: 1px solid var(--border);">
            <h3 style="font-family: 'Outfit', sans-serif; font-weight: 700; margin-bottom: 20px; color: var(--primary);">
                <i class="fas fa-tools" style="margin-right: 10px;"></i> Technical Specifications
            </h3>
            <div class="form-grid">
                <div class="form-group">
                    <label for="color">Exterior Color</label>
                    <input type="text" id="color" name="color" class="form-control" value="{{ $vehicle->color }}" placeholder="e.g. Metallic Black">
                </div>
                <div class="form-group">
                    <label for="vin">VIN Number</label>
                    <input type="text" id="vin" name="vin" class="form-control" value="{{ $vehicle->vin }}" placeholder="Vehicle Identification Number">
                </div>
                <div class="form-group">
                    <label for="transmission">Transmission</label>
                    <select id="transmission" name="transmission" class="form-control">
                        <option value="">Select Transmission</option>
                        <option value="automatic" {{ $vehicle->transmission == 'automatic' ? 'selected' : '' }}>Automatic</option>
                        <option value="manual" {{ $vehicle->transmission == 'manual' ? 'selected' : '' }}>Manual</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="fuel_type">Fuel Type</label>
                    <select id="fuel_type" name="fuel_type" class="form-control">
                        <option value="">Select Fuel Type</option>
                        <option value="petrol" {{ $vehicle->fuel_type == 'petrol' ? 'selected' : '' }}>Petrol</option>
                        <option value="diesel" {{ $vehicle->fuel_type == 'diesel' ? 'selected' : '' }}>Diesel</option>
                        <option value="electric" {{ $vehicle->fuel_type == 'electric' ? 'selected' : '' }}>Electric</option>
                        <option value="hybrid" {{ $vehicle->fuel_type == 'hybrid' ? 'selected' : '' }}>Hybrid</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="seats">Seating Capacity</label>
                    <input type="number" id="seats" name="seats" class="form-control" value="{{ $vehicle->seats }}" placeholder="e.g. 7" min="1">
                </div>
                <div class="form-group">
                    <label for="luggage_capacity">Luggage Capacity (Bags)</label>
                    <input type="number" id="luggage_capacity" name="luggage_capacity" class="form-control" value="{{ $vehicle->luggage_capacity }}" placeholder="e.g. 4" min="0">
                </div>
            </div>
        </div>

        <div class="form-footer mt-20">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Update Vehicle
            </button>
        </div>
    </form>
</div>
@endsection

@push('styles')
<style>
    .admin-form .form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 25px;
    }
    .form-footer {
        padding-top: 30px;
        border-top: 1px solid var(--border);
        display: flex;
        justify-content: flex-end;
    }
</style>
@endpush
