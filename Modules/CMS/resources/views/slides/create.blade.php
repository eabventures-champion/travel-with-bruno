@extends('admin::layouts.master')

@section('title', 'Add New Slide')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>Add New Slide</h1>
        <p>Create a new hero slide for the homepage.</p>
    </div>
    <a href="{{ route('admin.slides.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to List
    </a>
</div>

<div class="card" style="max-width: 800px;">
    <form action="{{ route('admin.slides.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div style="grid-column: span 2;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Title</label>
                <input type="text" name="title" required style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);" placeholder="e.g. Elevate Your Journey">
            </div>

            <div style="grid-column: span 2;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Subtitle</label>
                <textarea name="subtitle" rows="3" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);" placeholder="e.g. Experience the ultimate blend of luxury tourism..."></textarea>
            </div>

            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Button Text</label>
                <input type="text" name="button_text" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);" placeholder="e.g. Explore Packages">
            </div>

            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Button Link</label>
                <input type="text" name="button_link" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);" placeholder="e.g. #packages">
            </div>

            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Slide Image</label>
                <input type="file" name="image" required style="width: 100%; padding: 8px;">
                <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 5px;">Recommended size: 1920x1080px. Max 5MB.</p>
            </div>

            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Display Order</label>
                <input type="number" name="order" value="0" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);">
            </div>
        </div>

        <div style="margin-top: 30px; display: flex; gap: 15px;">
            <button type="submit" class="btn btn-primary">Create Slide</button>
            <button type="reset" class="btn btn-secondary">Reset Form</button>
        </div>
    </form>
</div>
@endsection
