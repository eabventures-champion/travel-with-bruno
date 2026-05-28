@extends('admin::layouts.master')

@section('title', 'Document Manager')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>Document Manager</h1>
        <p>Broadcast documents to all customers, drivers, or specific users.</p>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 30px; margin-top: 30px;">
    <!-- Upload Form -->
    <div>
        <div class="card" style="padding: 25px; border-radius: 20px;">
            <h3 style="font-family: 'Outfit', sans-serif; margin-bottom: 20px; font-size: 1.2rem; color: var(--text-main);">Broadcast New Document</h3>
            <form action="{{ route('admin.documents.broadcast.store') }}" method="POST" enctype="multipart/form-data" x-data="{ target: 'all' }">
                @csrf
                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-size: 0.75rem; font-weight: 800; color: var(--text-muted); margin-bottom: 8px; text-transform: uppercase;">Document Title</label>
                    <input type="text" name="title" required placeholder="e.g. Terms of Service, Guide for Drivers" style="width: 100%; padding: 12px; border-radius: 12px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);">
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-size: 0.75rem; font-weight: 800; color: var(--text-muted); margin-bottom: 8px; text-transform: uppercase;">Target Audience</label>
                    <select name="target_audience" x-model="target" required style="width: 100%; padding: 12px; border-radius: 12px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main); font-weight: 600;">
                        <option value="all">Everyone (All Users)</option>
                        <option value="customers">All Customers</option>
                        <option value="drivers">All Drivers</option>
                        <option value="selected">Selected Users Only</option>
                    </select>
                </div>

                <div x-show="target === 'selected'" style="margin-bottom: 20px;" x-cloak>
                    <label style="display: block; font-size: 0.75rem; font-weight: 800; color: var(--text-muted); margin-bottom: 8px; text-transform: uppercase;">Select Users</label>
                    <div style="max-height: 200px; overflow-y: auto; padding: 15px; background: var(--bg-main); border: 1px solid var(--border); border-radius: 12px;">
                        <div style="margin-bottom: 10px; font-size: 0.7rem; font-weight: 800; color: var(--primary);">CUSTOMERS</div>
                        @foreach($customers as $customer)
                        <label style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px; cursor: pointer;">
                            <input type="checkbox" name="user_ids[]" value="{{ $customer->id }}">
                            <span style="font-size: 0.85rem; color: var(--text-main);">{{ $customer->name }}</span>
                        </label>
                        @endforeach
                        
                        <div style="margin-top: 15px; margin-bottom: 10px; font-size: 0.7rem; font-weight: 800; color: var(--primary);">CHAUFFEURS</div>
                        @foreach($chauffeurs as $driver)
                        <label style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px; cursor: pointer;">
                            <input type="checkbox" name="user_ids[]" value="{{ $driver->id }}">
                            <span style="font-size: 0.85rem; color: var(--text-main);">{{ $driver->name }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                <div style="margin-bottom: 25px;">
                    <label style="display: block; font-size: 0.75rem; font-weight: 800; color: var(--text-muted); margin-bottom: 8px; text-transform: uppercase;">Upload File (PDF, Image, DOC)</label>
                    <div style="border: 2px dashed var(--border); padding: 20px; border-radius: 12px; text-align: center;">
                        <input type="file" name="document" required style="width: 100%; font-size: 0.85rem;">
                        <div style="margin-top: 10px; font-size: 0.7rem; color: var(--text-muted);">Maximum size: 10MB</div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; height: 50px; border-radius: 12px; font-weight: 800;">
                    <i class="fas fa-paper-plane" style="margin-right: 8px;"></i> Broadcast Document
                </button>
            </form>
        </div>
    </div>

    <!-- History / Active Broadcasts -->
    <div>
        <div class="card" style="padding: 25px; border-radius: 20px;">
            <h3 style="font-family: 'Outfit', sans-serif; margin-bottom: 20px; font-size: 1.2rem; color: var(--text-main);">Active Broadcasts</h3>
            <div style="display: grid; grid-template-columns: 1fr; gap: 15px;">
                @forelse($documents as $doc)
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 15px; background: var(--bg-main); border-radius: 15px; border: 1px solid var(--border);">
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <div style="width: 45px; height: 45px; background: var(--bg-card); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; color: var(--primary);">
                            <i class="fas fa-file-{{ in_array($doc->file_type, ['jpg', 'png', 'jpeg']) ? 'image' : 'alt' }}"></i>
                        </div>
                        <div>
                            <div style="font-weight: 700; color: var(--text-main); font-size: 0.95rem;">{{ $doc->title }}</div>
                            <div style="display: flex; gap: 10px; align-items: center; margin-top: 3px;">
                                <span style="font-size: 0.65rem; padding: 2px 8px; border-radius: 6px; background: var(--primary); color: white; font-weight: 700; text-transform: uppercase;">
                                    {{ $doc->target_audience }}
                                </span>
                                <span style="font-size: 0.7rem; color: var(--text-muted);">
                                    By {{ $doc->creator->name }} • {{ $doc->created_at->diffForHumans() }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div style="display: flex; gap: 8px;">
                        <a href="{{ route('admin.documents.broadcast.download', $doc) }}" class="btn-icon" title="Download" style="background: var(--bg-card); border: 1px solid var(--border); color: var(--primary);">
                            <i class="fas fa-download"></i>
                        </a>
                        <form action="{{ route('admin.documents.broadcast.destroy', $doc) }}" method="POST" onsubmit="return confirm('Remove this broadcast? Users will no longer be able to see it.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-icon" style="background: var(--bg-card); border: 1px solid #fee2e2; color: #ef4444;">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
                @empty
                <div style="text-align: center; padding: 40px; color: var(--text-muted);">
                    <i class="fas fa-file-export" style="font-size: 2.5rem; margin-bottom: 15px; opacity: 0.3;"></i>
                    <p>No documents have been broadcasted yet.</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<style>
    [x-cloak] { display: none !important; }
    .btn-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: 0.2s;
        text-decoration: none;
    }
    .btn-icon:hover {
        transform: translateY(-2px);
    }
</style>
@endsection
