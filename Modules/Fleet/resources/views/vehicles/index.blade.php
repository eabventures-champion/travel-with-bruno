@extends('admin::layouts.master')
@section('title', 'Vehicle Fleet')

@section('content')
<div class="page-header" style="margin-bottom: 30px;">
    <div class="page-title">
        <h1 style="font-family: 'Outfit', sans-serif; font-weight: 800; font-size: 2rem;">Vehicle Fleet</h1>
        <p style="color: var(--text-muted);">Manage your company's transportation assets and availability.</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.fleet.vehicles.create') }}" class="btn btn-primary" style="border-radius: 12px; padding: 12px 24px; font-weight: 700; display: flex; align-items: center; gap: 8px;">
            <i class="fas fa-plus-circle"></i> Add New Vehicle
        </a>
    </div>
</div>

<div class="card" style="padding: 0; overflow: hidden; border-radius: 20px; border: none; box-shadow: var(--shadow-md);" x-data="{ 
    previewOpen: false, 
    previewUrl: '',
    openPreview(url) {
        this.previewUrl = url;
        this.previewOpen = true;
    }
}">
    <div class="table-container">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: rgba(30, 58, 138, 0.03); text-align: left; border-bottom: 1px solid rgba(0,0,0,0.05);">
                    <th style="padding: 20px;">VEHICLE DETAILS</th>
                    <th style="padding: 20px;">SPECIFICATIONS</th>
                    <th style="padding: 20px;">STATUS</th>
                    <th style="padding: 20px; text-align: right;">ACTIONS</th>
                </tr>
            </thead>
            <tbody>
                @forelse($vehicles as $vehicle)
                <tr style="border-bottom: 1px solid rgba(0,0,0,0.02); transition: background 0.2s;">
                    <td style="padding: 20px;">
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <div style="width: 60px; height: 45px; border-radius: 10px; overflow: hidden; background: var(--bg-main); border: 1px solid var(--border); cursor: {{ $vehicle->image ? 'pointer' : 'default' }};"
                                 @if($vehicle->image) @click="openPreview('{{ asset('storage/' . $vehicle->image) }}')" @endif>
                                @if($vehicle->image)
                                    <img src="{{ asset('storage/' . $vehicle->image) }}" alt="Vehicle" style="width: 100%; height: 100%; object-fit: cover;">
                                @else
                                    <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: #94a3b8;">
                                        <i class="fas fa-car"></i>
                                    </div>
                                @endif
                            </div>
                            <div>
                                <div style="font-weight: 700; color: var(--primary); font-size: 1.05rem;">{{ $vehicle->make }} {{ $vehicle->model }}</div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);">Year: {{ $vehicle->year ?? 'N/A' }}</div>
                                <div style="display: flex; gap: 6px; align-items: center; margin-top: 6px;">
                                    <span style="background: rgba(99, 102, 241, 0.1); color: #6366f1; padding: 2px 8px; border-radius: 6px; font-size: 0.7rem; font-weight: 700; border: 1px solid rgba(99, 102, 241, 0.2);">
                                        {{ $vehicle->vehicleType->name }}
                                    </span>
                                    <span style="font-family: monospace; background: #fffbeb; color: #92400e; border: 1px solid #fde68a; padding: 2px 8px; border-radius: 6px; font-size: 0.7rem; font-weight: 700;">
                                        {{ $vehicle->license_plate }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td style="padding: 20px;">
                        <div style="display: flex; flex-direction: column; gap: 5px;">
                            <div style="font-size: 0.85rem; font-weight: 600; color: #1e293b; display: flex; align-items: center; gap: 6px;">
                                <i class="fas fa-palette" style="color: #64748b; font-size: 0.75rem;"></i> {{ $vehicle->color ?? 'N/A' }}
                            </div>
                            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                                @if($vehicle->seats)
                                    <span style="font-size: 0.7rem; background: #f1f5f9; color: #475569; padding: 2px 6px; border-radius: 4px; display: flex; align-items: center; gap: 4px;">
                                        <i class="fas fa-users" style="font-size: 0.65rem;"></i> {{ $vehicle->seats }}
                                    </span>
                                @endif
                                @if($vehicle->transmission)
                                    <span style="font-size: 0.7rem; background: #f1f5f9; color: #475569; padding: 2px 6px; border-radius: 4px; display: flex; align-items: center; gap: 4px;">
                                        <i class="fas fa-cog" style="font-size: 0.65rem;"></i> {{ ucfirst($vehicle->transmission) }}
                                    </span>
                                @endif
                                @if($vehicle->fuel_type)
                                    <span style="font-size: 0.7rem; background: #f1f5f9; color: #475569; padding: 2px 6px; border-radius: 4px; display: flex; align-items: center; gap: 4px;">
                                        <i class="fas fa-gas-pump" style="font-size: 0.65rem;"></i> {{ ucfirst($vehicle->fuel_type) }}
                                    </span>
                                @endif
                            </div>
                            <div style="font-size: 0.65rem; color: #94a3b8; font-family: monospace; margin-top: 2px;">
                                VIN: {{ $vehicle->vin ?? 'N/A' }}
                            </div>
                        </div>
                    </td>
                    <td style="padding: 20px;">
                        <span style="padding: 6px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; 
                              background: {{ $vehicle->status === 'available' ? '#dcfce7' : ($vehicle->status === 'on_trip' ? '#e0f2fe' : '#fee2e2') }}; 
                              color: {{ $vehicle->status === 'available' ? '#166534' : ($vehicle->status === 'on_trip' ? '#0369a1' : '#991b1b') }};">
                            {{ ucfirst(str_replace('_', ' ', $vehicle->status)) }}
                        </span>
                    </td>
                    <td style="padding: 20px; text-align: right;">
                        <div style="display: flex; gap: 10px; justify-content: flex-end;">
                            <a href="{{ route('admin.fleet.vehicles.edit', $vehicle->id) }}" class="btn-icon" style="color: var(--primary); background: rgba(30, 58, 138, 0.05); width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; text-decoration: none; transition: all 0.2s;">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.fleet.vehicles.destroy', $vehicle->id) }}" method="POST" onsubmit="return confirm('Remove this vehicle from fleet?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-icon" style="color: #ef4444; background: rgba(239, 68, 68, 0.05); width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; border: none; cursor: pointer; transition: all 0.2s;">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" style="padding: 60px; text-align: center; color: var(--text-muted);">
                        <i class="fas fa-car-side" style="font-size: 3rem; margin-bottom: 20px; display: block; opacity: 0.2;"></i>
                        No vehicles found. Add one to build your fleet!
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div class="pagination-container" style="padding: 20px; border-top: 1px solid rgba(0,0,0,0.05);">
        {{ $vehicles->links() }}
    </div>

    <!-- Image Preview Modal -->
    <div x-show="previewOpen" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         style="position: fixed; inset: 0; background: rgba(15, 23, 42, 0.9); backdrop-filter: blur(10px); display: flex; align-items: center; justify-content: center; z-index: 99999; padding: 40px;"
         @click="previewOpen = false"
         x-cloak>
        
        <div style="position: relative; max-width: 90vw; max-height: 90vh; display: flex; align-items: center; justify-content: center;" @click.stop>
            <button @click="previewOpen = false" 
                    style="position: absolute; -top: 20px; -right: 20px; background: white; border: none; color: var(--primary); width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s; z-index: 10; box-shadow: 0 10px 15px rgba(0,0,0,0.2);">
                <i class="fas fa-times"></i>
            </button>
            <img :src="previewUrl" alt="Preview" 
                 style="max-width: 100%; max-height: 80vh; border-radius: 20px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5); border: 2px solid rgba(255,255,255,0.1);">
        </div>
    </div>
</div>

<style>
    .btn-icon:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }
    a, button {
        text-decoration: none !important;
    }
</style>
@endsection
