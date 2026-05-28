@extends('admin::layouts.master')
@section('title', 'User Management')

@section('content')
<div class="page-header">
    <div class="page-title">
        <div style="display: flex; align-items: center; gap: 12px;">
            <h1 style="margin: 0;">User Management</h1>
            <span class="badge badge-primary" style="background: var(--primary); color: white; padding: 4px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: 700;">
                {{ $users->count() }} Total
            </span>
        </div>
        <p>Manage all registered users and their roles.</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add New User
        </a>
    </div>
</div>

<div class="mt-20" x-data="{ 
    search: '', 
    roleFilter: '',
    matches(name, email, phone, roles, type) {
        const s = this.search.toLowerCase();
        const r = this.roleFilter.toLowerCase();
        
        const matchesSearch = name.toLowerCase().includes(s) || 
                              email.toLowerCase().includes(s) || 
                              (phone && phone.toLowerCase().includes(s));
        const matchesRole = r === '' || roles.toLowerCase().includes(r) || type.toLowerCase() === r;
        
        return matchesSearch && matchesRole;
    }
}">
    <!-- Filter Bar -->
    <div class="dashboard-card filter-bar">
        <div class="filter-flex">
            <div class="search-wrapper">
                <i class="fas fa-search search-icon"></i>
                <input type="text" x-model="search" placeholder="Search by name, email or contact..." class="filter-input search-input">
            </div>
            <div class="dropdown-wrapper">
                <i class="fas fa-filter dropdown-icon"></i>
                <select x-model="roleFilter" class="filter-input dropdown-select">
                    <option value="">All Users</option>
                    <optgroup label="Roles">
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}">{{ $role->name }}</option>
                        @endforeach
                    </optgroup>
                    <optgroup label="Types">
                        @foreach($userTypes as $type)
                            <option value="{{ $type->slug }}">{{ $type->name }}</option>
                        @endforeach
                    </optgroup>
                </select>
            </div>
        </div>
    </div>

    <div class="dashboard-card">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Phone</th>
                        <th>Role / Type</th>
                        <th>Status</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr x-show="matches('{{ addslashes($user->name) }}', '{{ addslashes($user->email) }}', '{{ $user->phone }}', '{{ $user->roles->pluck('name')->implode(',') }}', '{{ $user->user_type }}')"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 transform scale-95"
                            x-transition:enter-end="opacity-100 transform scale-100">
                            <td>
                                <div class="user-info">
                                    <div class="avatar-small">
                                        {{ substr($user->name, 0, 1) }}
                                    </div>
                                    <div style="display: flex; flex-direction: column;">
                                        <span style="font-weight: 600;">{{ $user->name }}</span>
                                        <span class="badge" style="background: #fff7ed; color: #ea580c; border: 1px solid #ffedd5; font-size: 0.75rem; padding: 2px 8px; border-radius: 6px; width: fit-content; margin-top: 4px; font-weight: 500;">
                                            {{ $user->email }}
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $user->phone ?? 'N/A' }}</td>
                            <td>
                                @php
                                    $displayValue = $user->roles->count() > 0 ? $user->roles->first()->name : ucfirst($user->user_type);
                                    $color = match(strtolower($displayValue)) {
                                        'super admin' => ['bg' => 'rgba(139, 92, 246, 0.1)', 'text' => '#8b5cf6', 'border' => 'rgba(139, 92, 246, 0.2)'],
                                        'operations admin', 'staff', 'admin' => ['bg' => 'rgba(37, 99, 235, 0.1)', 'text' => '#2563eb', 'border' => 'rgba(37, 99, 235, 0.2)'],
                                        'driver', 'chauffeur' => ['bg' => 'rgba(245, 158, 11, 0.1)', 'text' => '#d97706', 'border' => 'rgba(245, 158, 11, 0.2)'],
                                        'customer' => ['bg' => 'rgba(16, 185, 129, 0.1)', 'text' => '#059669', 'border' => 'rgba(16, 185, 129, 0.2)'],
                                        default => ['bg' => 'rgba(100, 116, 139, 0.1)', 'text' => '#64748b', 'border' => 'rgba(100, 116, 139, 0.2)']
                                    };
                                @endphp
                                <span class="badge" style="background: {{ $color['bg'] }}; color: {{ $color['text'] }}; border: 1px solid {{ $color['border'] }}; padding: 4px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 700;">
                                    {{ $displayValue }}
                                </span>
                            </td>
                            <td>
                                <span class="status-badge {{ $user->status === 'active' ? 'status-active' : 'status-inactive' }}">
                                    {{ ucfirst($user->status) }}
                                </span>
                            </td>
                            <td>{{ $user->created_at->format('M d, Y') }}</td>
                            <td>
                                <div class="action-buttons">
                                    <a href="{{ route('admin.users.edit', $user->id) }}" class="action-btn edit" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @unless($user->hasRole('Super Admin'))
                                    <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Are you sure?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="action-btn delete" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @endunless
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@push('styles')
<style>
    .filter-bar {
        margin-bottom: 25px;
        padding: 12px 20px !important;
        border: 1px solid var(--border);
        background: var(--bg-card);
    }
    .filter-flex {
        display: flex;
        gap: 15px;
        align-items: center;
    }
    .search-wrapper, .dropdown-wrapper {
        position: relative;
        flex: 1;
    }
    .dropdown-wrapper {
        flex: 0 0 250px;
    }
    .search-icon, .dropdown-icon {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-muted);
        font-size: 0.9rem;
        pointer-events: none;
    }
    .filter-input {
        width: 100%;
        height: 45px;
        padding: 0 15px 0 42px;
        border-radius: 12px;
        border: 1px solid var(--border);
        background: var(--bg-main);
        color: var(--text-main);
        font-size: 0.95rem;
        transition: all 0.3s ease;
        appearance: none;
    }
    .filter-input:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        background: var(--bg-card);
    }
    .dropdown-select {
        cursor: pointer;
        padding-right: 40px;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2364748b'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 15px center;
        background-size: 15px;
    }
</style>
@endpush
@endsection
