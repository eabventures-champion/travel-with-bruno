@extends('driver::layouts.master')

@section('content')
<div style="padding-bottom: 20px;">
    <h2 style="font-family: 'Outfit', sans-serif; color: var(--text-main); font-size: 1.5rem; margin-bottom: 20px;">Shared Resources</h2>
    <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 25px;">Important documents and guides shared by the operations team.</p>

    <div style="display: grid; grid-template-columns: 1fr; gap: 15px;">
        @forelse($documents as $doc)
        <div class="card" style="margin-bottom: 0; padding: 20px; display: flex; align-items: center; justify-content: space-between; gap: 15px;">
            <div style="display: flex; align-items: center; gap: 15px;">
                <div style="width: 50px; height: 50px; background: rgba(30, 58, 138, 0.1); color: var(--primary); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                    <i class="fas fa-file-{{ in_array($doc->file_type, ['jpg', 'png', 'jpeg']) ? 'image' : 'alt' }}"></i>
                </div>
                <div>
                    <div style="font-weight: 700; color: var(--text-main); font-size: 1.05rem;">{{ $doc->title }}</div>
                    <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 4px;">
                        {{ strtoupper($doc->file_type) }} • Shared {{ $doc->created_at->diffForHumans() }}
                    </div>
                </div>
            </div>
            <a href="{{ route('admin.documents.broadcast.download', $doc) }}" class="btn btn-primary" style="width: 45px; height: 45px; padding: 0; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-download"></i>
            </a>
        </div>
        @empty
        <div class="card" style="text-align: center; padding: 60px 20px; opacity: 0.7;">
            <i class="fas fa-folder-open" style="font-size: 3rem; color: var(--text-muted); margin-bottom: 20px; display: block;"></i>
            <h3 style="font-family: 'Outfit', sans-serif; color: var(--text-main); margin-bottom: 10px;">No Resources Shared</h3>
            <p style="color: var(--text-muted); font-size: 0.85rem;">Check back later for any updates from the operations team.</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
