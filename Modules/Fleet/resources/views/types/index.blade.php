@extends('admin::layouts.master')
@section('title', 'Vehicle Types')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>Vehicle Types</h1>
        <p>Define categories for your fleet and set base rates.</p>
    </div>
</div>

<div class="grid-2 mt-20" x-data="{
    editing: false,
    typeId: null,
    name: '',
    slug: '',
    capacity: '',
    hourlyRate: '',
    dailyRate: '',
    actionUrl: '{{ route('admin.fleet.types.store') }}',

    editType(type) {
        this.editing = true;
        this.typeId = type.id;
        this.name = type.name;
        this.slug = type.slug;
        this.capacity = type.capacity;
        this.hourlyRate = type.base_hourly_rate;
        this.dailyRate = type.base_daily_rate;
        this.actionUrl = `/admin/fleet/types/${type.id}`;
        
        window.scrollTo({ top: 0, behavior: 'smooth' });
    },

    resetForm() {
        this.editing = false;
        this.typeId = null;
        this.name = '';
        this.slug = '';
        this.capacity = '';
        this.hourlyRate = '';
        this.dailyRate = '';
        this.actionUrl = '{{ route('admin.fleet.types.store') }}';
    },

    generateSlug() {
        this.slug = this.name.toLowerCase()
            .replace(/[^\w ]+/g, '')
            .replace(/ +/g, '-');
    }
}">
    <!-- Type List -->
    <div class="dashboard-card">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Type Name</th>
                        <th>Capacity</th>
                        <th>Hourly Rate</th>
                        <th>Daily Rate</th>
                        <th>Vehicles</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($types as $type)
                        <tr :class="typeId == {{ $type->id }} ? 'bg-primary-light' : ''">
                            <td>
                                <div style="font-weight: 600;">{{ $type->name }}</div>
                                <div style="font-size: 0.8rem; color: var(--text-muted);">{{ $type->slug }}</div>
                            </td>
                            <td>{{ $type->capacity }} Seats</td>
                            <td>₵{{ number_format($type->base_hourly_rate, 2) }}</td>
                            <td>₵{{ number_format($type->base_daily_rate, 2) }}</td>
                            <td>{{ $type->vehicles_count }}</td>
                            <td>
                                <div class="action-buttons">
                                    <button type="button" @click="editType({{ json_encode($type) }})" class="action-btn edit" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="{{ route('admin.fleet.types.destroy', $type->id) }}" method="POST" onsubmit="return confirm('Delete this type?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="action-btn delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">No vehicle types defined.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="pagination-container mt-10">
            {{ $types->links() }}
        </div>
    </div>

    <!-- Quick Add/Edit Form -->
    <div class="dashboard-card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 x-text="editing ? 'Edit Vehicle Type' : 'Add Vehicle Type'"></h3>
            <button type="button" x-show="editing" @click="resetForm()" class="btn btn-secondary btn-sm" style="padding: 5px 10px; font-size: 0.8rem;">
                <i class="fas fa-plus"></i> New
            </button>
        </div>
        <form :action="actionUrl" method="POST" class="admin-form">
            @csrf
            <template x-if="editing">
                <input type="hidden" name="_method" value="PUT">
            </template>

            <div class="form-group">
                <label for="name">Type Name</label>
                <input type="text" id="name" name="name" class="form-control" placeholder="e.g. Luxury SUV" required
                       x-model="name" @input="generateSlug()">
            </div>
            <div class="form-group">
                <label for="slug">Slug</label>
                <input type="text" id="slug" name="slug" class="form-control" placeholder="e.g. luxury-suv" required x-model="slug">
            </div>
            <div class="form-group">
                <label for="capacity">Passenger Capacity</label>
                <input type="number" id="capacity" name="capacity" class="form-control" placeholder="e.g. 4" required x-model="capacity">
            </div>
            <div class="form-group">
                <label for="base_hourly_rate">Base Hourly Rate (₵)</label>
                <input type="number" step="0.01" id="base_hourly_rate" name="base_hourly_rate" class="form-control" placeholder="0.00" required x-model="hourlyRate">
            </div>
            <div class="form-group">
                <label for="base_daily_rate">Base Daily Rate (₵)</label>
                <input type="number" step="0.01" id="base_daily_rate" name="base_daily_rate" class="form-control" placeholder="0.00" required x-model="dailyRate">
            </div>
            <div class="mt-20">
                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    <i :class="editing ? 'fas fa-save' : 'fas fa-plus'"></i> 
                    <span x-text="editing ? 'Update Type' : 'Create Type'"></span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<style>
    .grid-2 {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 24px;
    }
    @media (max-width: 992px) {
        .grid-2 {
            grid-template-columns: 1fr;
        }
    }
    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: var(--text-main);
        font-size: 0.9rem;
    }
    .form-control {
        width: 100%;
        padding: 12px 15px;
        border-radius: 8px;
        border: 1px solid var(--border);
        background: var(--bg-main);
        color: var(--text-main);
        font-size: 1rem;
        transition: all 0.3s;
    }
</style>
@endpush
