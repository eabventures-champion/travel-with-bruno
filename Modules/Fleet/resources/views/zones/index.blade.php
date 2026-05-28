@extends('admin::layouts.master')
@section('title', 'Pricing Zones')

@section('content')
<div class="page-header" style="margin-bottom: 30px;">
    <div class="page-title">
        <h1 style="font-family: 'Outfit', sans-serif; font-weight: 800; font-size: 2rem;">Pricing Zones</h1>
        <p style="color: var(--text-muted);">Manage additional pricing for regional destinations.</p>
    </div>
    <div class="page-actions">
        <button type="button" class="btn btn-primary" onclick="openZoneModal()" style="border-radius: 12px; padding: 12px 24px; font-weight: 700; display: flex; align-items: center; gap: 8px; text-decoration: none !important;">
            <i class="fas fa-plus-circle"></i> Add New Zone
        </button>
    </div>
</div>

<div class="card" style="padding: 0; overflow: hidden; border-radius: 20px; border: none; box-shadow: var(--shadow-md); background: var(--bg-card) !important;">
    <div class="table-container">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: rgba(30, 58, 138, 0.03); text-align: left; border-bottom: 1px solid var(--border);">
                    <th style="padding: 20px;">ZONE NAME</th>
                    <th style="padding: 20px;">PRICE MODIFIER</th>
                    <th style="padding: 20px;">STATUS</th>
                    <th style="padding: 20px; text-align: right;">ACTIONS</th>
                </tr>
            </thead>
            <tbody>
                @forelse($zones as $zone)
                <tr style="border-bottom: 1px solid var(--border); transition: background 0.2s;">
                    <td style="padding: 20px;">
                        <div style="font-weight: 700; color: var(--primary); font-size: 1.1rem;">{{ $zone->name }}</div>
                        <div style="font-size: 0.75rem; color: var(--text-muted);">Regional Pricing modifier</div>
                    </td>
                    <td style="padding: 20px;">
                        <span style="background: rgba(245, 158, 11, 0.1); color: var(--accent); padding: 6px 12px; border-radius: 8px; font-weight: 800; font-family: 'Outfit', sans-serif;">
                            + ₵{{ number_format($zone->additional_price, 2) }}
                        </span>
                    </td>
                    <td style="padding: 20px;">
                        <span style="padding: 6px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; 
                              background: {{ $zone->is_active ? '#dcfce7' : '#fee2e2' }}; 
                              color: {{ $zone->is_active ? '#166534' : '#991b1b' }};">
                            {{ $zone->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td style="padding: 20px; text-align: right;">
                        <div style="display: flex; gap: 10px; justify-content: flex-end;">
                            <button type="button" class="btn-icon" onclick='editZone(@json($zone))' style="color: var(--primary); background: rgba(30, 58, 138, 0.05); width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; border: none; cursor: pointer; transition: all 0.2s;">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form action="{{ route('admin.fleet.zones.destroy', $zone->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this zone?')">
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
                        <i class="fas fa-map-marked-alt" style="font-size: 3rem; margin-bottom: 20px; display: block; opacity: 0.2;"></i>
                        No pricing zones found. Create one to get started!
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Premium Zone Modal -->
<div id="zoneModal" class="modal-wrapper" style="display:none; position: fixed; inset: 0; z-index: 9999; background: var(--glass); backdrop-filter: blur(8px); align-items: center; justify-content: center;">
    <div class="modal-container" style="background: var(--bg-card); width: 100%; max-width: 450px; border-radius: 24px; box-shadow: var(--shadow-lg); overflow: hidden; transform: translateY(0); transition: all 0.3s ease;">
        <div style="background: var(--primary); padding: 25px; color: white; display: flex; justify-content: space-between; align-items: center;">
            <h3 id="modalTitle" style="margin: 0; font-family: 'Outfit', sans-serif; font-size: 1.25rem;">Add Pricing Zone</h3>
            <button onclick="closeZoneModal()" style="background: rgba(255,255,255,0.1); border: none; color: white; width: 32px; height: 32px; border-radius: 50%; cursor: pointer;">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <form id="zoneForm" method="POST" action="{{ route('admin.fleet.zones.store') }}" style="padding: 30px;">
            @csrf
            <div id="methodField"></div>
            
            <div class="form-group" style="margin-bottom: 20px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 700; color: var(--text-main); margin-bottom: 8px;">Zone Name / Region</label>
                <div style="position: relative;">
                    <i class="fas fa-map-marker-alt" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                    <input type="text" name="name" id="zone_name" required placeholder="e.g. Accra Central"
                           style="width: 100%; padding: 12px 12px 12px 40px; border-radius: 12px; border: 1px solid var(--border); background: var(--bg-main); font-size: 1rem; color: var(--text-main); transition: all 0.2s;">
                </div>
            </div>
            
            <div class="form-group" style="margin-bottom: 30px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 700; color: var(--text-main); margin-bottom: 8px;">Additional Price (₵)</label>
                <div style="position: relative;">
                    <span style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-weight: 700;">₵</span>
                    <input type="number" name="additional_price" id="zone_price" step="0.01" required placeholder="0.00"
                           style="width: 100%; padding: 12px 12px 12px 40px; border-radius: 12px; border: 1px solid var(--border); background: var(--bg-main); font-size: 1rem; font-weight: 700; color: var(--primary);">
                </div>
                <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 8px;">This amount will be added to the base hub rate.</p>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <button type="button" class="btn" onclick="closeZoneModal()" style="padding: 12px; border-radius: 12px; border: 1px solid var(--border); background: var(--bg-card); color: var(--text-main); font-weight: 700; cursor: pointer; transition: all 0.2s; text-decoration: none !important;">
                    Cancel
                </button>
                <button type="submit" class="btn btn-primary" style="padding: 12px; border-radius: 12px; background: var(--primary); color: white; border: none; font-weight: 700; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 6px -1px rgba(30, 58, 138, 0.2); text-decoration: none !important;">
                    Save Zone
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openZoneModal() {
        const modal = document.getElementById('zoneModal');
        modal.style.display = 'flex';
        document.getElementById('modalTitle').innerText = 'Add Pricing Zone';
        document.getElementById('zoneForm').action = "{{ route('admin.fleet.zones.store') }}";
        document.getElementById('methodField').innerHTML = '';
        document.getElementById('zone_name').value = '';
        document.getElementById('zone_price').value = '';
        document.getElementById('zone_name').focus();
    }

    function editZone(zone) {
        const modal = document.getElementById('zoneModal');
        modal.style.display = 'flex';
        document.getElementById('modalTitle').innerText = 'Edit Pricing Zone';
        document.getElementById('zoneForm').action = "/admin/fleet/zones/" + zone.id;
        document.getElementById('methodField').innerHTML = '@method("PUT")';
        document.getElementById('zone_name').value = zone.name;
        document.getElementById('zone_price').value = zone.additional_price;
        document.getElementById('zone_name').focus();
    }

    function closeZoneModal() {
        document.getElementById('zoneModal').style.display = 'none';
    }

    // Close on outside click
    window.onclick = function(event) {
        const modal = document.getElementById('zoneModal');
        if (event.target == modal) {
            closeZoneModal();
        }
    }
</script>

<style>
    .btn-icon:hover {
        transform: translateY(-2px);
    }
    input:focus {
        border-color: var(--primary) !important;
        box-shadow: 0 0 0 3px rgba(30, 58, 138, 0.1) !important;
        outline: none;
    }
    a, button {
        text-decoration: none !important;
    }
</style>
@endsection
