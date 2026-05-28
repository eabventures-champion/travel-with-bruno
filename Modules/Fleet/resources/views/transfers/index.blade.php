@extends('admin::layouts.master')
@section('title', 'Transfer Services')

@section('content')
<div class="page-header" style="margin-bottom: 30px;">
    <div class="page-title">
        <h1 style="font-family: 'Outfit', sans-serif; font-weight: 800; font-size: 2rem;">Transfer Services</h1>
        <p style="color: var(--text-muted);">Manage fixed-rate point-to-point transfer services between any locations.</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.fleet.transfers.create') }}" class="btn btn-primary" style="border-radius: 12px; padding: 12px 24px; font-weight: 700; display: flex; align-items: center; gap: 8px; text-decoration: none !important;">
            <i class="fas fa-plus-circle"></i> Add Transfer Service
        </a>
    </div>
</div>

<div class="card" style="padding: 0; overflow: hidden; border-radius: 20px; border: none; box-shadow: var(--shadow-md); background: var(--bg-card) !important;">
    <div class="table-container">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: rgba(30, 58, 138, 0.03); text-align: left; border-bottom: 1px solid var(--border);">
                    <th style="padding: 20px;">HUB / PICKUP</th>
                    <th style="padding: 20px;">TYPE</th>
                    <th style="padding: 20px;">ASSIGNED FLEET</th>
                    <th style="padding: 20px;">BASE PRICE</th>
                    <th style="padding: 20px;">STATUS</th>
                    <th style="padding: 20px; text-align: right;">ACTIONS</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transfers as $transfer)
                <tr style="border-bottom: 1px solid var(--border); transition: background 0.2s;">
                    <td style="padding: 20px;">
                        <div style="font-weight: 700; color: var(--primary); font-size: 1.1rem;">{{ $transfer->airport_name }}</div>
                        <div style="font-size: 0.75rem; color: var(--text-muted);">{{ Str::limit($transfer->description, 50) }}</div>
                    </td>
                    <td style="padding: 20px;">
                        <span style="padding: 4px 10px; border-radius: 6px; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px;
                              background: var(--bg-main); 
                              color: var(--text-main);
                              border: 1px solid var(--border);">
                            {{ $transfer->transfer_type }}
                        </span>
                    </td>
                    <td style="padding: 20px;">
                        @if($transfer->vehicle)
                            <div style="font-weight: 700; color: var(--text-main);">{{ $transfer->vehicle->make }} {{ $transfer->vehicle->model }}</div>
                            <div style="font-size: 0.7rem; font-family: monospace; background: var(--bg-main); color: var(--text-main); border: 1px solid var(--border); display: inline-block; padding: 2px 6px; border-radius: 4px; margin-top: 4px;">{{ $transfer->vehicle->license_plate }}</div>
                        @else
                            <span style="color: var(--text-muted); font-style: italic;">No specific vehicle</span>
                        @endif
                    </td>
                    <td style="padding: 20px;">
                        <div style="font-weight: 800; color: var(--accent); font-family: 'Outfit', sans-serif; font-size: 1.1rem;">
                            ₵{{ number_format($transfer->price, 2) }}
                        </div>
                    </td>
                    <td style="padding: 20px;">
                        <span style="padding: 6px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; 
                              background: {{ $transfer->is_active ? '#dcfce7' : '#fee2e2' }}; 
                              color: {{ $transfer->is_active ? '#166534' : '#991b1b' }};">
                            {{ $transfer->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td style="padding: 20px; text-align: right;">
                        <div style="display: flex; gap: 10px; justify-content: flex-end;">
                            <a href="{{ route('admin.fleet.transfers.edit', $transfer->id) }}" class="btn-icon" style="color: var(--primary); background: rgba(30, 58, 138, 0.05); width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; text-decoration: none; transition: all 0.2s;">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.fleet.transfers.destroy', $transfer->id) }}" method="POST" onsubmit="return confirm('Delete this transfer service?')">
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
                    <td colspan="6" style="padding: 60px; text-align: center; color: var(--text-muted);">
                        <i class="fas fa-route" style="font-size: 3rem; margin-bottom: 20px; display: block; opacity: 0.2;"></i>
                        No transfer services found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
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
