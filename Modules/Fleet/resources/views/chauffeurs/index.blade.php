@extends('admin::layouts.master')
@section('title', 'Chauffeurs')

@section('content')
<div class="page-header" style="margin-bottom: 30px;">
    <div class="page-title">
        <h1 style="font-family: 'Outfit', sans-serif; font-weight: 800; font-size: 2rem;">Chauffeurs</h1>
        <p style="color: var(--text-muted);">Manage your team of professional drivers and track their status.</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.fleet.chauffeurs.create') }}" class="btn btn-primary" style="border-radius: 12px; padding: 12px 24px; font-weight: 700; display: flex; align-items: center; gap: 8px;">
            <i class="fas fa-user-plus"></i> Add New Chauffeur
        </a>
    </div>
</div>

<div class="card" style="padding: 0; overflow: hidden; border-radius: 20px; border: none; box-shadow: var(--shadow-md);">
    <div class="table-container">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: var(--bg-main); text-align: left; border-bottom: 1px solid var(--border);">
                    <th style="padding: 20px; color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px;">DRIVER IDENTITY</th>
                    <th style="padding: 20px; color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px;">LICENSE DETAILS</th>
                    <th style="padding: 20px; color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px;">EXPERIENCE</th>
                    <th style="padding: 20px; color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px;">STATUS</th>
                    <th style="padding: 20px; text-align: right; color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px;">ACTIONS</th>
                </tr>
            </thead>
            <tbody>
                @forelse($chauffeurs as $chauffeur)
                <tr style="border-bottom: 1px solid var(--border); transition: background 0.2s;">
                    <td style="padding: 20px;">
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <div style="width: 48px; height: 48px; border-radius: 12px; background: var(--primary); display: flex; align-items: center; justify-content: center; color: white; font-weight: 800; font-family: 'Outfit', sans-serif; font-size: 1.2rem; box-shadow: 0 4px 6px rgba(30, 58, 138, 0.15);">
                                {{ substr($chauffeur->user->name, 0, 1) }}
                            </div>
                            <div>
                                <div style="font-weight: 700; color: var(--primary); font-size: 1.05rem;">{{ $chauffeur->user->name }}</div>
                                <div style="font-size: 0.75rem; color: var(--text-muted); display: flex; align-items: center; flex-wrap: wrap; gap: 10px; margin-top: 5px;">
                                    <span><i class="far fa-envelope"></i> {{ $chauffeur->user->email }}</span>
                                    @if($chauffeur->user->phone)
                                        <span style="color: var(--accent); font-weight: 700;"><i class="fas fa-phone-alt"></i> {{ $chauffeur->user->phone }}</span>
                                    @endif
                                    
                                    @if($chauffeur->user->hasRole('Driver') || $chauffeur->user->hasRole('Driver/Chauffeur') || $chauffeur->user->hasRole('Chauffeur'))
                                        <span style="color: #10b981; font-weight: 700;" title="System Role Assigned"><i class="fas fa-check-circle"></i> Role Active</span>
                                    @else
                                        <span style="color: #ef4444; font-weight: 700;" title="Missing Driver Role"><i class="fas fa-exclamation-triangle"></i> Missing Role</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </td>
                    <td style="padding: 20px;">
                        <div style="font-family: monospace; background: var(--bg-main); color: var(--text-main); border: 1px solid var(--border); padding: 4px 10px; border-radius: 8px; display: inline-block; font-weight: 700; font-size: 0.85rem;">
                            {{ $chauffeur->license_number }}
                        </div>
                        <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 5px;">
                            <i class="far fa-calendar-times" style="margin-right: 4px;"></i> Expires: <span style="font-weight: 600;">{{ $chauffeur->license_expiry ? $chauffeur->license_expiry->format('d/m/Y') : 'N/A' }}</span>
                        </div>
                    </td>
                    <td style="padding: 20px;">
                        <div style="font-weight: 700; color: var(--text-main);">{{ $chauffeur->years_of_experience }} <span style="font-weight: 400; color: var(--text-muted);">Years</span></div>
                        <div style="font-size: 0.7rem; color: var(--text-muted);">Professional Driver</div>
                    </td>
                    <td style="padding: 20px;">
                        @php
                            $statusColors = [
                                'available' => ['bg' => 'rgba(16, 185, 129, 0.1)', 'color' => '#10b981'],
                                'engaged' => ['bg' => 'rgba(245, 158, 11, 0.1)', 'color' => '#f59e0b'],
                                'schedule_accepted' => ['bg' => 'rgba(99, 102, 241, 0.1)', 'color' => '#6366f1'],
                            ];
                            $sc = $statusColors[$chauffeur->status] ?? ['bg' => 'rgba(239, 68, 68, 0.1)', 'color' => '#ef4444'];
                        @endphp
                        @if($chauffeur->status === 'engaged' && $chauffeur->bookings->first())
                        <a href="{{ route('admin.bookings.show', $chauffeur->bookings->first()->id) }}" title="View Active Trip">
                            <span style="padding: 6px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; background: rgba(245, 158, 11, 0.15); color: #d97706; border: 1px dashed rgba(245, 158, 11, 0.5); cursor: pointer; transition: all 0.2s;">
                                <i class="fas fa-external-link-alt" style="margin-right: 4px; font-size: 0.65rem;"></i> Engaged
                            </span>
                        </a>
                        @elseif($chauffeur->status === 'schedule_accepted')
                        <span style="padding: 6px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; background: {{ $sc['bg'] }}; color: {{ $sc['color'] }};">
                            <i class="fas fa-calendar-check" style="margin-right: 4px; font-size: 0.65rem;"></i> Schedule Accepted
                        </span>
                        @else
                        <span style="padding: 6px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; background: {{ $sc['bg'] }}; color: {{ $sc['color'] }};">
                            {{ ucfirst(str_replace('_', ' ', $chauffeur->status)) }}
                        </span>
                        @endif
                    </td>
                    <td style="padding: 20px; text-align: right;">
                        <div style="display: flex; gap: 10px; justify-content: flex-end;">
                            <a href="{{ route('admin.fleet.chauffeurs.edit', $chauffeur->id) }}" class="btn-icon" style="color: var(--primary); background: rgba(30, 58, 138, 0.05); width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; text-decoration: none; transition: all 0.2s;">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.fleet.chauffeurs.destroy', $chauffeur->id) }}" method="POST" onsubmit="return confirm('Remove this chauffeur from records?')">
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
                    <td colspan="5" style="padding: 60px; text-align: center; color: var(--text-muted);">
                        <i class="fas fa-id-card-alt" style="font-size: 3rem; margin-bottom: 20px; display: block; opacity: 0.2;"></i>
                        No chauffeurs registered yet.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div class="pagination-container" style="padding: 20px; border-top: 1px solid rgba(0,0,0,0.05);">
        {{ $chauffeurs->links() }}
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
