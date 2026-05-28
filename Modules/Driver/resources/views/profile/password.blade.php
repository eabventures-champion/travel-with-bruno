@extends('driver::layouts.master')

@section('content')
<div style="margin-bottom: 25px; display: flex; align-items: center; gap: 15px;">
    <a href="{{ route('driver.profile') }}" style="width: 40px; height: 40px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--text-main); text-decoration: none; border: 1px solid var(--border);">
        <i class="fas fa-chevron-left"></i>
    </a>
    <h2 style="font-family: 'Outfit', sans-serif; color: var(--text-main); font-size: 1.5rem; margin: 0;">Change Password</h2>
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

    <form action="{{ route('driver.profile.password.update') }}" method="POST">
        @csrf
        @method('PUT')

        <div style="margin-bottom: 20px;">
            <label style="display: block; font-size: 0.85rem; font-weight: 700; color: var(--text-muted); margin-bottom: 8px;">Current Password</label>
            <input type="password" name="current_password" required
                   style="width: 100%; padding: 12px; border-radius: 12px; border: 1px solid var(--border); font-size: 1rem; color: var(--text-main);">
        </div>

        <div style="margin-bottom: 20px;">
            <label style="display: block; font-size: 0.85rem; font-weight: 700; color: var(--text-muted); margin-bottom: 8px;">New Password</label>
            <input type="password" name="password" required
                   style="width: 100%; padding: 12px; border-radius: 12px; border: 1px solid var(--border); font-size: 1rem; color: var(--text-main);">
            <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 5px;">Must be at least 8 characters long.</p>
        </div>

        <div style="margin-bottom: 30px;">
            <label style="display: block; font-size: 0.85rem; font-weight: 700; color: var(--text-muted); margin-bottom: 8px;">Confirm New Password</label>
            <input type="password" name="password_confirmation" required
                   style="width: 100%; padding: 12px; border-radius: 12px; border: 1px solid var(--border); font-size: 1rem; color: var(--text-main);">
        </div>

        <button type="submit" class="btn btn-primary">Update Password</button>
    </form>
</div>
@endsection
