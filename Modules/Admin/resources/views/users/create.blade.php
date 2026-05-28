@extends('admin::layouts.master')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>Add New User</h1>
        <p>Create a new account for staff or administrators.</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </div>
</div>

<div class="dashboard-card mt-20" style="max-width: 800px;" x-data="{
    email: '',
    phone: '',
    duplicateError: '',
    async checkUniqueness() {
        if (this.email.length < 5 && this.phone.length < 5) {
            this.duplicateError = '';
            return;
        }
        try {
            const response = await fetch('{{ route('admin.users.check-uniqueness') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    email: this.email,
                    phone: this.phone
                })
            });
            const data = await response.json();
            this.duplicateError = data.exists ? data.message : '';
        } catch (e) {
            console.error('Uniqueness check failed:', e);
        }
    }
}">
    <form action="{{ route('admin.users.store') }}" method="POST" class="admin-form">
        @csrf

        @if ($errors->any())
            <div style="background: rgba(239, 68, 68, 0.1); color: #ef4444; padding: 15px; border-radius: 10px; margin-bottom: 25px; border: 1px solid rgba(239, 68, 68, 0.2);">
                <ul style="margin: 0; padding-left: 20px; font-size: 0.9rem;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Duplicate Error Message -->
        <div x-show="duplicateError" x-transition style="margin-bottom: 25px; padding: 15px; background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); border-radius: 10px; color: #ef4444; font-size: 0.9rem; font-weight: 600; display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-exclamation-circle"></i>
            <span x-text="duplicateError"></span>
        </div>

        <div class="form-grid">
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" class="form-control" placeholder="Enter full name" required>
            </div>
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="Enter email" required x-model="email" @input.debounce.500ms="checkUniqueness()">
            </div>
            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="text" id="phone" name="phone" class="form-control" placeholder="e.g. +233 20 000 0000" x-model="phone" @input.debounce.500ms="checkUniqueness()">
            </div>
            <div class="form-group">
                <label for="user_type">User Type</label>
                <select id="user_type" name="user_type" class="form-control">
                    @foreach($userTypes as $type)
                    <option value="{{ $type->slug }}">{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="Enter password" required>
            </div>
            <div class="form-group">
                <label for="password_confirmation">Confirm Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" placeholder="Repeat password" required>
            </div>
            <div class="form-group">
                <label for="status">Account Status</label>
                <select id="status" name="status" class="form-control">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="suspended">Suspended</option>
                </select>
            </div>
        </div>

        <div class="form-footer mt-20">
            <button type="submit" class="btn btn-primary" :disabled="duplicateError !== ''" :style="duplicateError !== '' ? 'opacity: 0.5; cursor: not-allowed;' : ''">
                <i class="fas fa-save"></i> Save User
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
