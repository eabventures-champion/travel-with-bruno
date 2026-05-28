@extends('admin::layouts.master')

@section('title', 'Edit Slide')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>Edit Slide</h1>
        <p>Update existing hero slide information.</p>
    </div>
    <a href="{{ route('admin.slides.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to List
    </a>
</div>

<div class="card" style="max-width: 800px;">
    <form action="{{ route('admin.slides.update', $slide) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div style="grid-column: span 2;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Title</label>
                <input type="text" name="title" value="{{ $slide->title }}" required style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);">
            </div>

            <div style="grid-column: span 2;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Subtitle</label>
                <textarea name="subtitle" rows="3" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);">{{ $slide->subtitle }}</textarea>
            </div>

            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Button Text</label>
                <input type="text" name="button_text" value="{{ $slide->button_text }}" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);">
            </div>

            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Button Link</label>
                <input type="text" name="button_link" value="{{ $slide->button_link }}" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);">
            </div>

            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Change Image</label>
                <input type="file" name="image" style="width: 100%; padding: 8px;">
                <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 5px;">Leave empty to keep current image.</p>
            </div>

            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Display Order</label>
                <input type="number" name="order" value="{{ $slide->order }}" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);">
            </div>

            <div style="grid-column: span 2;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Current Image</label>
                <img src="{{ asset('storage/' . $slide->image_path) }}" alt="" style="width: 200px; height: 120px; object-fit: cover; border-radius: 12px; border: 1px solid var(--border);">
            </div>
        </div>

        <div style="margin-top: 30px; display: flex; gap: 15px;">
            <button type="submit" class="btn btn-primary">Update Slide</button>
            <a href="{{ route('admin.slides.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
