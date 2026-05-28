@extends('admin::layouts.master')

@section('content')
<div class="page-header" style="margin-bottom: 30px;">
    <div class="page-title">
        <h1 style="font-family: 'Outfit', sans-serif; font-weight: 800; font-size: 2rem;">Add Transfer Service</h1>
        <p style="color: var(--text-muted);">Define a new point-to-point transfer route or airport hub.</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.fleet.transfers.index') }}" class="btn btn-secondary" style="border-radius: 12px; padding: 12px 20px; font-weight: 700; display: flex; align-items: center; gap: 8px; background: var(--bg-card); color: var(--text-main); border: 1px solid var(--border); text-decoration: none !important;">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </div>
</div>

<div class="card" style="border-radius: 24px; border: none; box-shadow: var(--shadow-md); overflow: hidden; background: var(--bg-card) !important; padding: 0;">
    <div style="background: var(--primary); padding: 20px 30px; color: white;">
        <h3 style="margin: 0; font-family: 'Outfit', sans-serif; font-size: 1.1rem; opacity: 0.9;">Service Details</h3>
    </div>
    
    <form action="{{ route('admin.fleet.transfers.store') }}" method="POST" style="padding: 40px;">
        @csrf
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 30px; margin-bottom: 30px;">
            
            <div class="form-group">
                <label style="display: block; font-size: 0.85rem; font-weight: 700; color: var(--text-main); margin-bottom: 10px;">Pickup Point / Hub Title</label>
                <div style="position: relative;">
                    <i class="fas fa-map-marker-alt" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                    <input type="text" name="airport_name" required placeholder="e.g. Kotoka International Airport"
                           style="width: 100%; padding: 14px 14px 14px 45px; border-radius: 12px; border: 1px solid var(--border); background: var(--bg-main); font-size: 1rem; color: var(--text-main); transition: all 0.2s;">
                </div>
            </div>

            <div class="form-group">
                <label style="display: block; font-size: 0.85rem; font-weight: 700; color: var(--text-main); margin-bottom: 10px;">Primary Destination (Default)</label>
                <div style="position: relative;">
                    <i class="fas fa-location-arrow" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                    <input type="text" name="location" required placeholder="e.g. Accra City Center"
                           style="width: 100%; padding: 14px 14px 14px 45px; border-radius: 12px; border: 1px solid var(--border); background: var(--bg-main); font-size: 1rem; color: var(--text-main); transition: all 0.2s;">
                </div>
            </div>

            <div class="form-group">
                <label style="display: block; font-size: 0.85rem; font-weight: 700; color: var(--text-main); margin-bottom: 10px;">Service Arrangement</label>
                <div style="position: relative;">
                    <i class="fas fa-sync" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                    <select name="transfer_type" required style="width: 100%; padding: 14px 14px 14px 45px; border-radius: 12px; border: 1px solid var(--border); background: var(--bg-main); font-size: 1rem; color: var(--text-main); appearance: none; cursor: pointer;">
                        <option value="both">Both (Round Trip)</option>
                        <option value="pickup">Pickup Only (One-Way)</option>
                        <option value="dropoff">Drop-off Only (One-Way)</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label style="display: block; font-size: 0.85rem; font-weight: 700; color: var(--text-main); margin-bottom: 10px;">Service Category</label>
                <div style="position: relative;">
                    <i class="fas fa-tag" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                    <select name="category" required style="width: 100%; padding: 14px 14px 14px 45px; border-radius: 12px; border: 1px solid var(--border); background: var(--bg-main); font-size: 1rem; color: var(--text-main); appearance: none; cursor: pointer;">
                        <option value="airport">Airport Transfer</option>
                        <option value="other">Other / General Location</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label style="display: block; font-size: 0.85rem; font-weight: 700; color: var(--text-main); margin-bottom: 10px;">Assign Fleet Vehicle</label>
                <div style="position: relative;">
                    <i class="fas fa-car" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                    <select name="vehicle_id" required style="width: 100%; padding: 14px 14px 14px 45px; border-radius: 12px; border: 1px solid var(--border); background: var(--bg-main); font-size: 1rem; color: var(--text-main); appearance: none; cursor: pointer;">
                        <option value="">Select Specific Vehicle</option>
                        @foreach($vehicles as $vehicle)
                            <option value="{{ $vehicle->id }}">
                                {{ $vehicle->make }} {{ $vehicle->model }} ({{ $vehicle->license_plate }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label style="display: block; font-size: 0.85rem; font-weight: 700; color: var(--text-main); margin-bottom: 10px;">Base Price (₵)</label>
                <div style="position: relative;">
                    <span style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-weight: 800;">₵</span>
                    <input type="number" name="price" step="0.01" required placeholder="0.00"
                           style="width: 100%; padding: 14px 14px 14px 45px; border-radius: 12px; border: 1px solid var(--border); background: var(--bg-main); font-size: 1rem; font-weight: 700; color: var(--primary); transition: all 0.2s;">
                </div>
            </div>

            <div class="form-group" style="grid-column: 1 / -1;">
                <label style="display: block; font-size: 0.85rem; font-weight: 700; color: var(--text-main); margin-bottom: 10px;">Service Description</label>
                <textarea name="description" rows="3" placeholder="Describe the inclusions or specific details of this route..."
                          style="width: 100%; padding: 15px; border-radius: 12px; border: 1px solid var(--border); background: var(--bg-main); font-size: 1rem; color: var(--text-main); resize: vertical;"></textarea>
            </div>

            <div class="form-group" style="grid-column: 1 / -1;">
                <label style="display: inline-flex; align-items: center; gap: 12px; cursor: pointer; background: var(--bg-main); padding: 15px 25px; border-radius: 12px; border: 1px solid var(--border);">
                    <input type="checkbox" name="is_active" value="1" checked style="width: 18px; height: 18px; accent-color: var(--primary);">
                    <span style="font-weight: 700; color: var(--text-main); font-size: 0.9rem;">Mark this transfer service as active</span>
                </label>
            </div>
        </div>

        <div style="display: flex; justify-content: flex-end; padding-top: 30px; border-top: 1px solid var(--border);">
            <button type="submit" class="btn btn-primary" style="padding: 15px 40px; border-radius: 12px; background: var(--primary); color: white; border: none; font-weight: 800; font-size: 1rem; cursor: pointer; box-shadow: 0 10px 15px -3px rgba(30, 58, 138, 0.3); display: flex; align-items: center; gap: 10px; text-decoration: none !important;">
                <i class="fas fa-check-circle"></i> Save Transfer Service
            </button>
        </div>
    </form>
</div>

<style>
    input:focus, select:focus, textarea:focus {
        border-color: var(--primary) !important;
        box-shadow: 0 0 0 4px rgba(30, 58, 138, 0.08) !important;
        outline: none;
        background: var(--bg-card) !important;
    }
    .form-group label {
        transition: all 0.2s;
    }
    .form-group:focus-within label {
        color: var(--primary) !important;
    }
    a, button {
        text-decoration: none !important;
    }
</style>
@endsection
