@extends('admin::layouts.master')

@section('title', 'User Types')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>User Types</h1>
        <p>Manage user categories and roles available in the system.</p>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
    <!-- Add New Type -->
    <div class="dashboard-card">
        <h3 style="margin-bottom: 20px; font-size: 1.1rem;">Add New User Type</h3>
        <form action="{{ route('admin.user-types.store') }}" method="POST">
            @csrf
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 6px; font-weight: 600; font-size: 0.9rem;">Type Name</label>
                <input type="text" name="name" class="form-control" placeholder="e.g. Tour Guide" required style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);">
            </div>
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 6px; font-weight: 600; font-size: 0.9rem;">Description</label>
                <input type="text" name="description" class="form-control" placeholder="Brief description" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);">
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Type
            </button>
        </form>
    </div>

    <!-- Types List -->
    <div class="dashboard-card">
        <h3 style="margin-bottom: 20px; font-size: 1.1rem;">All User Types ({{ $userTypes->count() }})</h3>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($userTypes as $type)
                    <tr x-data="{ editing: false }">
                        <td>
                            <template x-if="!editing">
                                <div>
                                    <strong>{{ $type->name }}</strong>
                                    @if($type->description)
                                    <br><small style="color: var(--text-muted);">{{ $type->description }}</small>
                                    @endif
                                </div>
                            </template>
                            <template x-if="editing">
                                <form action="{{ route('admin.user-types.update', $type->id) }}" method="POST" style="display: flex; flex-direction: column; gap: 6px;">
                                    @csrf
                                    @method('PUT')
                                    <input type="text" name="name" value="{{ $type->name }}" style="padding: 6px 10px; border-radius: 6px; border: 1px solid var(--border); font-size: 0.85rem; background: var(--bg-main); color: var(--text-main);" required>
                                    <input type="text" name="description" value="{{ $type->description }}" placeholder="Description" style="padding: 6px 10px; border-radius: 6px; border: 1px solid var(--border); font-size: 0.8rem; background: var(--bg-main); color: var(--text-main);">
                                    <div style="display: flex; gap: 5px;">
                                        <button type="submit" class="btn btn-primary" style="padding: 4px 12px; font-size: 0.75rem;"><i class="fas fa-check"></i> Save</button>
                                        <button type="button" @click="editing = false" class="btn" style="padding: 4px 12px; font-size: 0.75rem; background: var(--bg-main); color: var(--text-main); border: 1px solid var(--border);">Cancel</button>
                                    </div>
                                </form>
                            </template>
                        </td>
                        <td><code style="font-size: 0.8rem; background: var(--bg-main); padding: 3px 8px; border-radius: 4px;">{{ $type->slug }}</code></td>
                        <td>
                            <span class="status-badge {{ $type->is_active ? 'status-active' : 'status-inactive' }}">
                                {{ $type->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button @click="editing = !editing" class="action-btn edit" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route('admin.user-types.toggle', $type->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="action-btn {{ $type->is_active ? 'delete' : 'edit' }}" title="{{ $type->is_active ? 'Deactivate' : 'Activate' }}">
                                        <i class="fas {{ $type->is_active ? 'fa-ban' : 'fa-check' }}"></i>
                                    </button>
                                </form>
                                <form action="{{ route('admin.user-types.destroy', $type->id) }}" method="POST" onsubmit="return confirm('Delete this user type?')">
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
                        <td colspan="4" class="text-center">No user types defined.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
