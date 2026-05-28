@extends('driver::layouts.master')

@section('content')
<div style="margin-bottom: 25px; display: flex; align-items: center; gap: 15px;">
    <a href="{{ route('driver.profile') }}" style="width: 40px; height: 40px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--text-main); text-decoration: none; border: 1px solid var(--border);">
        <i class="fas fa-chevron-left"></i>
    </a>
    <h2 style="font-family: 'Outfit', sans-serif; color: var(--text-main); font-size: 1.5rem; margin: 0;">License & Documents</h2>
</div>

<div class="card">
    @if(session('success'))
        <div style="background: rgba(16, 185, 129, 0.1); color: var(--success); padding: 15px; border-radius: 12px; margin-bottom: 20px; font-size: 0.9rem; font-weight: 600;">
            <i class="fas fa-check-circle" style="margin-right: 8px;"></i> {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div style="background: rgba(239, 68, 68, 0.1); color: var(--danger); padding: 15px; border-radius: 12px; margin-bottom: 20px; font-size: 0.9rem;">
            <ul style="margin: 0; padding-left: 20px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div style="background: rgba(245, 158, 11, 0.05); border: 1px dashed var(--accent); padding: 15px; border-radius: 15px; margin-bottom: 25px; display: flex; gap: 15px; align-items: center;">
        <div style="width: 40px; height: 40px; background: var(--accent); color: white; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i class="fas fa-info-circle"></i>
        </div>
        <p style="font-size: 0.8rem; color: var(--text-main); margin: 0; line-height: 1.4;">
            Please ensure your license details are up to date. To upload new document scans, please contact the operations administrator.
        </p>
    </div>

    <form action="{{ route('driver.profile.documents.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div style="margin-bottom: 20px;">
            <label style="display: block; font-size: 0.85rem; font-weight: 700; color: var(--text-muted); margin-bottom: 8px;">Driving License Number</label>
            <input type="text" name="license_number" value="{{ old('license_number', $chauffeur->license_number ?? '') }}" required
                   style="width: 100%; padding: 12px; border-radius: 12px; border: 1px solid var(--border); font-size: 1rem; color: var(--text-main);">
        </div>

        <div style="margin-bottom: 30px;">
            <label style="display: block; font-size: 0.85rem; font-weight: 700; color: var(--text-muted); margin-bottom: 8px;">License Expiry Date</label>
            <input type="text" name="license_expiry" value="{{ old('license_expiry', $chauffeur->license_expiry ?? '') }}" required
                   placeholder="e.g. Dec 2026"
                   style="width: 100%; padding: 12px; border-radius: 12px; border: 1px solid var(--border); font-size: 1rem; color: var(--text-main);">
        </div>

        <h4 style="font-family: 'Outfit', sans-serif; font-size: 1rem; margin-bottom: 15px; color: var(--text-main);">Document Scans</h4>
        
        <div style="display: grid; gap: 20px; margin-bottom: 30px;">
            {{-- Driving License --}}
            <div style="padding: 20px; background: var(--bg-main); border-radius: 15px; border: 1px solid var(--border);">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 15px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <i class="fas fa-id-card" style="color: var(--primary);"></i>
                        <span style="font-size: 0.95rem; font-weight: 700; color: var(--text-main);">Driving License (Front)</span>
                    </div>
                    @if($chauffeur->license_verified_at)
                        <span style="padding: 4px 12px; background: rgba(16, 185, 129, 0.1); color: #10b981; border-radius: 20px; font-size: 0.75rem; font-weight: 700;">Verified</span>
                    @else
                        <span style="padding: 4px 12px; background: rgba(245, 158, 11, 0.1); color: #f59e0b; border-radius: 20px; font-size: 0.75rem; font-weight: 700;">Pending Verification</span>
                    @endif
                </div>
                
                @if($chauffeur->license_front_path)
                    <div style="margin-bottom: 15px;">
                        <img src="{{ asset('storage/' . $chauffeur->license_front_path) }}" alt="License" style="width: 100%; max-height: 200px; object-fit: contain; border-radius: 10px; border: 1px solid var(--border);">
                    </div>
                @endif
                
                <input type="file" name="license_front" accept="image/*" style="font-size: 0.85rem;">
            </div>
            
            {{-- National ID --}}
            <div style="padding: 20px; background: var(--bg-main); border-radius: 15px; border: 1px solid var(--border);">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 15px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <i class="fas fa-id-badge" style="color: var(--primary);"></i>
                        <span style="font-size: 0.95rem; font-weight: 700; color: var(--text-main);">National ID / Ghana Card</span>
                    </div>
                    @if($chauffeur->id_verified_at)
                        <span style="padding: 4px 12px; background: rgba(16, 185, 129, 0.1); color: #10b981; border-radius: 20px; font-size: 0.75rem; font-weight: 700;">Verified</span>
                    @else
                        <span style="padding: 4px 12px; background: rgba(245, 158, 11, 0.1); color: #f59e0b; border-radius: 20px; font-size: 0.75rem; font-weight: 700;">Pending Verification</span>
                    @endif
                </div>
                
                @if($chauffeur->id_card_path)
                    <div style="margin-bottom: 15px;">
                        <img src="{{ asset('storage/' . $chauffeur->id_card_path) }}" alt="ID Card" style="width: 100%; max-height: 200px; object-fit: contain; border-radius: 10px; border: 1px solid var(--border);">
                    </div>
                @endif
                
                <input type="file" name="id_card" accept="image/*" style="font-size: 0.85rem;">
            </div>
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%; padding: 15px; border-radius: 12px; font-weight: 700;">Update Profile & Documents</button>
    </form>
</div>
@endsection
