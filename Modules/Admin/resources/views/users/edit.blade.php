@extends('admin::layouts.master')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>Edit User</h1>
        <p>Update details for {{ $user->name }}.</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </div>
</div>

<div class="dashboard-card mt-20" style="max-width: 800px;" x-data="{
    email: '{{ $user->email }}',
    phone: '{{ $user->phone }}',
    duplicateError: '',
    userId: '{{ $user->id }}',
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
                    phone: this.phone,
                    user_id: this.userId
                })
            });
            const data = await response.json();
            this.duplicateError = data.exists ? data.message : '';
        } catch (e) {
            console.error('Uniqueness check failed:', e);
        }
    }
}">
    <form action="{{ route('admin.users.update', $user->id) }}" method="POST" class="admin-form">
        @csrf
        @method('PUT')

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
                <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
            </div>
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required x-model="email" @input.debounce.500ms="checkUniqueness()">
            </div>
            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="text" id="phone" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}" placeholder="e.g. +233 20 000 0000" x-model="phone" @input.debounce.500ms="checkUniqueness()">
            </div>
            <div class="form-group">
                <label for="user_type">User Type</label>
                <select id="user_type" name="user_type" class="form-control">
                    @foreach($userTypes as $type)
                    <option value="{{ $type->slug }}" {{ $user->user_type === $type->slug ? 'selected' : '' }}>{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="password">New Password <small style="color: var(--text-muted);">(leave blank to keep current)</small></label>
                <input type="password" id="password" name="password" class="form-control" placeholder="Enter new password">
            </div>
            <div class="form-group">
                <label for="password_confirmation">Confirm New Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" placeholder="Repeat new password">
            </div>
            <div class="form-group">
                <label for="status">Account Status</label>
                <select id="status" name="status" class="form-control">
                    <option value="active" {{ $user->status === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ $user->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="suspended" {{ $user->status === 'suspended' ? 'selected' : '' }}>Suspended</option>
                </select>
            </div>
        </div>

        @if($user->hasRole('Customer'))
        <h3 style="margin: 30px 0 20px; font-family: 'Outfit', sans-serif; color: var(--text-main); border-bottom: 1px solid var(--border); padding-bottom: 10px;">
            <i class="fas fa-id-card" style="color: var(--accent); margin-right: 10px;"></i>Profile Information
        </h3>
        
        <div class="form-grid">
            <div class="form-group">
                <label for="address">Address</label>
                <input type="text" id="address" name="address" class="form-control" value="{{ old('address', $user->address) }}" placeholder="Their physical location for service coordination.">
            </div>
            <div class="form-group">
                <label for="nationality">Nationality</label>
                <input type="text" id="nationality" name="nationality" class="form-control" value="{{ old('nationality', $user->nationality) }}" placeholder="Useful for international travel arrangements.">
            </div>
            <div class="form-group">
                <label for="emergency_contact">Emergency Contact</label>
                <input type="text" id="emergency_contact" name="emergency_contact" class="form-control" value="{{ old('emergency_contact', $user->emergency_contact) }}" placeholder="A critical safety detail for tours and long-distance travel.">
            </div>
            <div class="form-group">
                <label for="dob">Date of Birth</label>
                <input type="date" id="dob" name="dob" class="form-control" value="{{ old('dob', $user->dob ? $user->dob->format('Y-m-d') : '') }}">
            </div>
            <div class="form-group">
                <label for="id_document">ID Document Reference</label>
                <input type="text" id="id_document" name="id_document" class="form-control" value="{{ old('id_document', $user->id_document) }}" placeholder="Displays the type (e.g., Passport or National ID) and the ID number.">
            </div>
            <div class="form-group">
                <label for="travel_preferences">Travel Preferences</label>
                <input type="text" id="travel_preferences" name="travel_preferences" class="form-control" value="{{ old('travel_preferences', is_array($user->travel_preferences) ? implode(', ', $user->travel_preferences) : $user->travel_preferences) }}" placeholder="A list of specific needs (e.g., 'Vegetarian', 'Window Seat', 'Quiet Driver').">
            </div>
        </div>

        <div class="form-group mt-20">
            <label for="bio">Bio / Personal Note</label>
            <textarea id="bio" name="bio" class="form-control" rows="4" placeholder="A brief personal introduction or specific notes about the traveler.">{{ old('bio', $user->bio) }}</textarea>
        </div>
        @endif

        <div class="form-footer mt-20">
            <button type="submit" class="btn btn-primary" :disabled="duplicateError !== ''" :style="duplicateError !== '' ? 'opacity: 0.5; cursor: not-allowed;' : ''">
                <i class="fas fa-save"></i> Update User
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
