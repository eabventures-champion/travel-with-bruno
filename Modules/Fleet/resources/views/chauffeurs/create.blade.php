@extends('admin::layouts.master')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>Register Chauffeur</h1>
        <p>Create a professional profile for an existing driver.</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.fleet.chauffeurs.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </div>
</div>

<div class="dashboard-card mt-20">
    @if($users->isEmpty())
        <div style="text-align: center; padding: 40px;">
            <i class="fas fa-user-slash" style="font-size: 3rem; color: #cbd5e1; margin-bottom: 20px;"></i>
            <h3>No Drivers Available</h3>
            <p style="color: var(--text-slate); margin-bottom: 20px;">You need to create a user with the type 'driver' first, who doesn't already have a chauffeur profile.</p>
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary">Create New Driver</a>
        </div>
    @else
        <form action="{{ route('admin.fleet.chauffeurs.store') }}" method="POST" class="admin-form" x-data="{
            licenseNumber: '',
            duplicateError: '',
            async checkLicense() {
                if (this.licenseNumber.length < 5) {
                    this.duplicateError = '';
                    return;
                }
                try {
                    const response = await fetch('{{ route('admin.fleet.chauffeurs.check-license') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            license_number: this.licenseNumber
                        })
                    });
                    const data = await response.json();
                    this.duplicateError = data.exists ? data.message : '';
                } catch (e) {
                    console.error('License check failed:', e);
                }
            }
        }">
            @csrf

            <!-- Duplicate Error Message -->
            <div x-show="duplicateError" x-transition style="margin-bottom: 25px; padding: 15px; background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); border-radius: 10px; color: #ef4444; font-size: 0.9rem; font-weight: 600; display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-exclamation-circle"></i>
                <span x-text="duplicateError"></span>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label for="user_id">Select User (Driver)</label>
                    <select id="user_id" name="user_id" class="form-control" required>
                        <option value="">Select a driver</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="license_number">License Number</label>
                    <input type="text" id="license_number" name="license_number" class="form-control" placeholder="e.g. GHA-12345678-B" required x-model="licenseNumber" @input.debounce.500ms="checkLicense()">
                </div>
                <div class="form-group">
                    <label for="license_expiry">License Expiry Date</label>
                    <input type="date" id="license_expiry" name="license_expiry" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="years_of_experience">Years of Experience</label>
                    <input type="number" id="years_of_experience" name="years_of_experience" class="form-control" placeholder="e.g. 5" min="0" required>
                </div>
                <div class="form-group">
                    <label for="status">Initial Status</label>
                    <select id="status" name="status" class="form-control" required>
                        <option value="available">Available</option>
                        <option value="engaged">Engaged</option>
                        <option value="schedule_accepted">Schedule Accepted</option>
                        <option value="off_duty">Off Duty</option>
                        <option value="suspended">Suspended</option>
                    </select>
                </div>
            </div>

            <div class="form-footer mt-20">
                <button type="submit" class="btn btn-primary" :disabled="duplicateError !== ''" :style="duplicateError !== '' ? 'opacity: 0.5; cursor: not-allowed;' : ''">
                    <i class="fas fa-id-card"></i> Create Chauffeur Profile
                </button>
            </div>
        </form>
    @endif
</div>
@endsection

@push('styles')
<style>
    .admin-form .form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
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
        color-scheme: dark;
    }
    select.form-control option {
        background: var(--bg-card);
        color: var(--text-main);
    }
    .form-control:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(30, 58, 138, 0.1);
        outline: none;
    }
    .form-footer {
        padding-top: 20px;
        border-top: 1px solid var(--border);
        display: flex;
        justify-content: flex-end;
    }
</style>
@endpush
