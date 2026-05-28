@extends('admin::layouts.master')
@section('title', 'Customer Types')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>Customer Types</h1>
        <p>Define customer types like Individual, Family, or Corporate for tour bookings.</p>
    </div>
</div>

<div class="grid-2 mt-20" x-data="{ 
    editing: false,
    typeId: null,
    name: '',
    status: true,
    actionUrl: '{{ route('admin.tourism.guest-types.store') }}',

    editType(type) {
        this.editing = true;
        this.typeId = type.id;
        this.name = type.name;
        this.status = type.status === 'active';
        this.actionUrl = `/admin/tourism/guest-types/${type.id}`;
        
        window.scrollTo({ top: 0, behavior: 'smooth' });
    },

    resetForm() {
        this.editing = false;
        this.typeId = null;
        this.name = '';
        this.status = true;
        this.actionUrl = '{{ route('admin.tourism.guest-types.store') }}';
    }
}">
    <!-- Guest Type List -->
    <div class="dashboard-card">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Guest Type</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($guestTypes as $type)
                        <tr :class="typeId == {{ $type->id }} ? 'bg-primary-light' : ''">
                            <td>
                                <div style="font-weight: 600; font-size: 1rem;">{{ $type->name }}</div>
                            </td>
                            <td>
                                <span class="status-badge {{ $type->status === 'active' ? 'status-active' : 'status-inactive' }}">
                                    {{ ucfirst($type->status) }}
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button type="button" @click="editType({{ json_encode($type) }})" class="action-btn edit" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="{{ route('admin.tourism.guest-types.destroy', $type->id) }}" method="POST" onsubmit="return confirm('Are you sure?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="action-btn delete" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center">No guest types found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="pagination-container mt-10">
            {{ $guestTypes->links() }}
        </div>
    </div>

    <!-- Quick Add/Edit Form -->
    <div class="dashboard-card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 x-text="editing ? 'Edit Guest Type' : 'Quick Add Customer Type'"></h3>
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
                <input type="text" id="name" name="name" class="form-control" placeholder="e.g. Corporate Group" required x-model="name">
                <p style="font-size: 0.75rem; color: var(--text-muted); mt-5">This will be displayed as a radio button on the frontend.</p>
            </div>
            
            <div class="form-group" style="margin-top: 15px;">
                <label class="flex-items-center" style="display: flex; gap: 10px; cursor: pointer;">
                    <input type="checkbox" name="status" value="1" x-model="status">
                    <span>Active Type</span>
                </label>
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
        background: var(--bg-card);
        color: var(--text-main);
        font-size: 1rem;
        transition: all 0.3s;
    }
    .form-control:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        outline: none;
    }
    .bg-primary-light {
        background-color: rgba(37, 99, 235, 0.05);
    }
</style>
@endpush
