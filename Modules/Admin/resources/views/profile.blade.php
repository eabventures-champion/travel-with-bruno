@extends('admin::layouts.master')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>My Profile</h1>
        <p>Manage your account settings and personal information.</p>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 30px; margin-top: 30px;">
    <!-- Profile Card -->
    <div style="display: flex; flex-direction: column; gap: 30px;">
        <div class="card" style="padding: 30px; background: var(--bg-card); border-radius: 24px; box-shadow: var(--shadow-sm); border: 1px solid var(--border); text-align: center;">
            <div style="width: 120px; height: 120px; background: var(--primary); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 4rem; font-weight: 800; margin: 0 auto 20px; box-shadow: 0 10px 20px rgba(30, 58, 138, 0.2);">
                {{ substr($user->name, 0, 1) }}
            </div>
            <h2 style="font-family: 'Outfit', sans-serif; margin: 0; color: var(--text-main);">{{ $user->name }}</h2>
            <p style="color: var(--text-muted); margin: 5px 0 20px;">{{ ucfirst($user->user_type ?? 'Member') }} Account</p>
            
            <div style="display: flex; flex-direction: column; gap: 10px; text-align: left; background: var(--bg-main); padding: 20px; border-radius: 15px; border: 1px solid var(--border);">
                <div>
                    <div style="font-size: 0.7rem; color: var(--text-muted); font-weight: 800; text-transform: uppercase;">Member Since</div>
                    <div style="font-weight: 600; font-size: 0.9rem; color: var(--text-main);">{{ $user->created_at->format('F d, Y') }}</div>
                </div>
                <div style="margin-top: 10px;">
                    <div style="font-size: 0.7rem; color: var(--text-muted); font-weight: 800; text-transform: uppercase;">Account Status</div>
                    <span style="display: inline-block; padding: 4px 12px; background: #dcfce7; color: #166534; border-radius: 20px; font-size: 0.75rem; font-weight: 700; margin-top: 5px;">{{ ucfirst($user->status ?? 'Active') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Details Card -->
    <div class="card" style="padding: 30px; background: var(--bg-card); border-radius: 24px; box-shadow: var(--shadow-sm); border: 1px solid var(--border);" x-data="{
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
        <h3 style="font-family: 'Outfit', sans-serif; margin-bottom: 25px; display: flex; align-items: center; gap: 10px; color: var(--text-main);">
            <i class="fas fa-user-edit" style="color: var(--primary);"></i> Personal Information
        </h3>

        <form action="{{ route('admin.profile.update') }}" method="POST" class="admin-form">
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

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px;">
                <div class="form-group">
                    <label style="display: block; font-size: 0.85rem; font-weight: 700; color: var(--text-muted); margin-bottom: 8px;">Full Name</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                           style="width: 100%; padding: 12px; border-radius: 10px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);">
                </div>
                <div class="form-group">
                    <label style="display: block; font-size: 0.85rem; font-weight: 700; color: var(--text-muted); margin-bottom: 8px;">Email Address</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required x-model="email" @input.debounce.500ms="checkUniqueness()"
                           style="width: 100%; padding: 12px; border-radius: 10px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);">
                </div>
                <div class="form-group">
                    <label style="display: block; font-size: 0.85rem; font-weight: 700; color: var(--text-muted); margin-bottom: 8px;">Phone Number</label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" x-model="phone" @input.debounce.500ms="checkUniqueness()"
                           style="width: 100%; padding: 12px; border-radius: 10px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);">
                </div>
                <div class="form-group">
                    <label style="display: block; font-size: 0.85rem; font-weight: 700; color: var(--text-muted); margin-bottom: 8px;">Update Password <small>(leave blank to keep current)</small></label>
                    <input type="password" name="password" placeholder="New Password"
                           style="width: 100%; padding: 12px; border-radius: 10px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);">
                </div>
                <div class="form-group">
                    <label style="display: block; font-size: 0.85rem; font-weight: 700; color: var(--text-muted); margin-bottom: 8px;">Confirm New Password</label>
                    <input type="password" name="password_confirmation" placeholder="Confirm Password"
                           style="width: 100%; padding: 12px; border-radius: 10px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);">
                </div>
            </div>

            @hasrole('Customer')
            <h3 style="font-family: 'Outfit', sans-serif; margin: 30px 0 20px; display: flex; align-items: center; gap: 10px; color: var(--text-main);">
                <i class="fas fa-info-circle" style="color: var(--primary);"></i> Additional Information
            </h3>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px;">
                <div class="form-group">
                    <label style="display: block; font-size: 0.85rem; font-weight: 700; color: var(--text-muted); margin-bottom: 8px;">Nationality</label>
                    <input type="text" name="nationality" value="{{ old('nationality', $user->nationality ?? '') }}" placeholder="Useful for international travel arrangements."
                           style="width: 100%; padding: 12px; border-radius: 10px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);">
                </div>
                <div class="form-group">
                    <label style="display: block; font-size: 0.85rem; font-weight: 700; color: var(--text-muted); margin-bottom: 8px;">Date of Birth</label>
                    <input type="date" name="date_of_birth" value="{{ old('date_of_birth', $user->dob ? $user->dob->format('Y-m-d') : '') }}"
                           style="width: 100%; padding: 12px; border-radius: 10px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);">
                </div>
                <div class="form-group">
                    <label style="display: block; font-size: 0.85rem; font-weight: 700; color: var(--text-muted); margin-bottom: 8px;">Emergency Contact</label>
                    <input type="text" name="emergency_contact" value="{{ old('emergency_contact', $user->emergency_contact ?? '') }}" placeholder="A critical safety detail for tours and long-distance travel."
                           style="width: 100%; padding: 12px; border-radius: 10px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);">
                </div>
                <div class="form-group">
                    <label style="display: block; font-size: 0.85rem; font-weight: 700; color: var(--text-muted); margin-bottom: 8px;">ID Document Number</label>
                    <input type="text" name="id_document_number" value="{{ old('id_document_number', $user->id_document ?? '') }}" placeholder="Displays the type (e.g., Passport or National ID) and the ID number."
                           style="width: 100%; padding: 12px; border-radius: 10px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);">
                </div>
                <div class="form-group" style="grid-column: span 2;">
                    <label style="display: block; font-size: 0.85rem; font-weight: 700; color: var(--text-muted); margin-bottom: 8px;">Address</label>
                    <textarea name="address" rows="2" placeholder="Their physical location for service coordination."
                               style="width: 100%; padding: 12px; border-radius: 10px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);">{{ old('address', $user->address ?? '') }}</textarea>
                </div>
                <div class="form-group" style="grid-column: span 2;">
                    <label style="display: block; font-size: 0.85rem; font-weight: 700; color: var(--text-muted); margin-bottom: 8px;">Bio</label>
                    <textarea name="bio" rows="3" placeholder="A brief personal introduction or specific notes about the traveler."
                               style="width: 100%; padding: 12px; border-radius: 10px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);">{{ old('bio', $user->bio ?? '') }}</textarea>
                </div>
                <div class="form-group" style="grid-column: span 2;">
                    <label style="display: block; font-size: 0.85rem; font-weight: 700; color: var(--text-muted); margin-bottom: 8px;">Travel Preferences</label>
                    <textarea name="travel_preferences" rows="3" placeholder="A list of specific needs (e.g., 'Vegetarian', 'Window Seat', 'Quiet Driver')."
                               style="width: 100%; padding: 12px; border-radius: 10px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);">{{ old('travel_preferences', is_array($user->travel_preferences) ? implode(', ', $user->travel_preferences) : ($user->travel_preferences ?? '')) }}</textarea>
                </div>
            </div>
            @endhasrole

            <div style="margin-top: 30px; padding-top: 25px; border-top: 1px solid var(--border); text-align: right;">
                <button type="submit" class="btn btn-primary" :disabled="duplicateError !== ''" :style="duplicateError !== '' ? 'opacity: 0.5; cursor: not-allowed;' : ''" style="padding: 12px 30px; border-radius: 12px; font-weight: 800;">
                    <i class="fas fa-save" style="margin-right: 8px;"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
