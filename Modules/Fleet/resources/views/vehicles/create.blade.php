@extends('admin::layouts.master')
@section('title', 'Add New Vehicle')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>Add New Vehicle</h1>
        <p>Register a new vehicle to the company fleet.</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.fleet.vehicles.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Fleet
        </a>
    </div>
</div>

<div class="dashboard-card mt-20">
    <form action="{{ route('admin.fleet.vehicles.store') }}" method="POST" class="admin-form" enctype="multipart/form-data">
        @csrf
        <div class="form-grid">
            <div class="form-group">
                <label for="vehicle_type_id">Vehicle Type</label>
                <select id="vehicle_type_id" name="vehicle_type_id" class="form-control" required>
                    <option value="">Select Type</option>
                    @foreach($types as $type)
                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="make">Make / Brand</label>
                <input type="text" id="make" name="make" class="form-control" placeholder="e.g. Toyota" required>
            </div>
            <div class="form-group">
                <label for="model">Model</label>
                <input type="text" id="model" name="model" class="form-control" placeholder="e.g. Land Cruiser" required>
            </div>
            <div class="form-group">
                <label for="year">Year</label>
                <input type="text" id="year" name="year" class="form-control" placeholder="e.g. 2023">
            </div>
            <div class="form-group">
                <label for="license_plate">License Plate</label>
                <input type="text" id="license_plate" name="license_plate" class="form-control" placeholder="e.g. GX-1234-22" required>
            </div>
            <div class="form-group">
                <label for="status">Initial Status</label>
                <select id="status" name="status" class="form-control" required>
                    <option value="available">Available</option>
                    <option value="on_trip">On Trip</option>
                    <option value="maintenance">Maintenance</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <div class="form-group">
                <label for="chauffeur_id">Assigned Chauffeur (Default)</label>
                <select id="chauffeur_id" name="chauffeur_id" class="form-control">
                    <option value="">No Chauffeur Assigned</option>
                    @foreach($chauffeurs as $chauffeur)
                        <option value="{{ $chauffeur->id }}">{{ $chauffeur->user->name ?? 'Unknown' }} ({{ $chauffeur->years_of_experience }} yrs exp)</option>
                    @endforeach
                </select>
                <small style="color: var(--text-slate); font-size: 0.8rem;">This chauffeur will be shown to customers by default.</small>
            </div>
            <div class="form-group">
                <label for="image">Vehicle Image (Optional)</label>
                <input type="file" id="image" name="image" class="form-control" accept="image/*">
                <small style="color: var(--text-slate); font-size: 0.8rem;">Supported formats: JPG, PNG, WEBP. Max size: 2MB</small>
            </div>
        </div>

        <div style="margin-top: 40px; padding: 25px; background: rgba(30, 58, 138, 0.02); border-radius: 15px; border: 1px solid var(--border);">
            <h3 style="font-family: 'Outfit', sans-serif; font-weight: 700; margin-bottom: 20px; color: var(--primary);">
                <i class="fas fa-tools" style="margin-right: 10px;"></i> Technical Specifications
            </h3>
            <div class="form-grid">
                <div class="form-group">
                    <label for="color">Exterior Color</label>
                    <input type="text" id="color" name="color" class="form-control" placeholder="e.g. Metallic Black">
                </div>
                <div class="form-group">
                    <label for="vin">VIN Number</label>
                    <input type="text" id="vin" name="vin" class="form-control" placeholder="Vehicle Identification Number">
                </div>
                <div class="form-group">
                    <label for="transmission">Transmission</label>
                    <select id="transmission" name="transmission" class="form-control">
                        <option value="">Select Transmission</option>
                        <option value="automatic">Automatic</option>
                        <option value="manual">Manual</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="fuel_type">Fuel Type</label>
                    <select id="fuel_type" name="fuel_type" class="form-control">
                        <option value="">Select Fuel Type</option>
                        <option value="petrol">Petrol</option>
                        <option value="diesel">Diesel</option>
                        <option value="electric">Electric</option>
                        <option value="hybrid">Hybrid</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="seats">Seating Capacity</label>
                    <input type="number" id="seats" name="seats" class="form-control" placeholder="e.g. 7" min="1">
                </div>
                <div class="form-group">
                    <label for="luggage_capacity">Luggage Capacity (Bags)</label>
                    <input type="number" id="luggage_capacity" name="luggage_capacity" class="form-control" placeholder="e.g. 4" min="0">
                </div>
            </div>
        </div>

        <div class="form-footer mt-20">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Vehicle
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
