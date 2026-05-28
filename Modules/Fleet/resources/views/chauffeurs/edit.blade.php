@extends('admin::layouts.master')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>Edit Chauffeur: {{ $chauffeur->user->name }}</h1>
        <p>Update license details and professional status.</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.fleet.chauffeurs.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </div>
</div>

<div class="dashboard-card mt-20">
    <form action="{{ route('admin.fleet.chauffeurs.update', $chauffeur->id) }}" method="POST" class="admin-form" x-data="{
        licenseNumber: '{{ $chauffeur->license_number }}',
        duplicateError: '',
        chauffeurId: '{{ $chauffeur->id }}',
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
                        license_number: this.licenseNumber,
                        chauffeur_id: this.chauffeurId
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
        @method('PUT')

        <!-- Duplicate Error Message -->
        <div x-show="duplicateError" x-transition style="margin-bottom: 25px; padding: 15px; background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); border-radius: 10px; color: #ef4444; font-size: 0.9rem; font-weight: 600; display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-exclamation-circle"></i>
            <span x-text="duplicateError"></span>
        </div>

        <div class="form-grid">
            <div class="form-group">
                <label>Linked User</label>
                <input type="text" class="form-control" value="{{ $chauffeur->user->name }} ({{ $chauffeur->user->email }})" disabled>
            </div>
            <div class="form-group">
                <label for="license_number">License Number</label>
                <input type="text" id="license_number" name="license_number" class="form-control" value="{{ $chauffeur->license_number }}" required x-model="licenseNumber" @input.debounce.500ms="checkLicense()">
            </div>
            <div class="form-group">
                <label for="license_expiry">License Expiry Date</label>
                <input type="date" id="license_expiry" name="license_expiry" class="form-control" value="{{ $chauffeur->license_expiry ? $chauffeur->license_expiry->format('Y-m-d') : '' }}" required>
            </div>
            <div class="form-group">
                <label for="years_of_experience">Years of Experience</label>
                <input type="number" id="years_of_experience" name="years_of_experience" class="form-control" value="{{ $chauffeur->years_of_experience }}" min="0" required>
            </div>
            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status" class="form-control" required>
                    <option value="available" {{ $chauffeur->status == 'available' ? 'selected' : '' }}>Available</option>
                    <option value="engaged" {{ $chauffeur->status == 'engaged' ? 'selected' : '' }}>Engaged</option>
                    <option value="schedule_accepted" {{ $chauffeur->status == 'schedule_accepted' ? 'selected' : '' }}>Schedule Accepted</option>
                    <option value="off_duty" {{ $chauffeur->status == 'off_duty' ? 'selected' : '' }}>Off Duty</option>
                    <option value="suspended" {{ $chauffeur->status == 'suspended' ? 'selected' : '' }}>Suspended</option>
                </select>
            </div>
        </div>

        <div class="form-footer mt-20">
            <button type="submit" class="btn btn-primary" :disabled="duplicateError !== ''" :style="duplicateError !== '' ? 'opacity: 0.5; cursor: not-allowed;' : ''">
                <i class="fas fa-save"></i> Update Profile
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
