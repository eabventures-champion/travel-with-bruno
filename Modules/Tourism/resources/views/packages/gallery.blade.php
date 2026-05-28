@extends('admin::layouts.master')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>Tour Gallery: {{ $package->title }}</h1>
        <p>Manage images shared in the live tour gallery.</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.tourism.packages.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Packages
        </a>
    </div>
</div>

<div class="grid-container" style="display: grid; grid-template-columns: 1fr 350px; gap: 30px; margin-top: 30px;">
    <!-- Gallery List -->
    <div class="dashboard-card">
        <h3 class="font-heading" style="margin-bottom: 20px;">Shared Moments</h3>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px;">
            @forelse($package->uploads as $image)
                <div class="gallery-item" style="background: var(--bg-main); border-radius: 15px; overflow: hidden; border: 1px solid var(--border); transition: transform 0.3s;">
                    <div style="aspect-ratio: 4/3; position: relative;">
                        <img src="{{ asset('storage/' . $image->image_path) }}" style="width: 100%; height: 100%; object-fit: cover;">
                        <div style="position: absolute; top: 10px; right: 10px;">
                            <form action="{{ route('tourism.packages.gallery.destroy', [$package->id, $image->id]) }}" method="POST" onsubmit="return confirm('Delete this image?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="action-btn delete" style="width: 30px; height: 30px; border-radius: 8px;">
                                    <i class="fas fa-trash" style="font-size: 0.8rem;"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                    <div style="padding: 15px;">
                        <div style="font-size: 0.85rem; font-weight: 700; color: var(--primary);">{{ $image->user->name ?? 'Guest' }}</div>
                        <div style="font-size: 0.7rem; color: var(--text-muted); margin-bottom: 8px;">{{ $image->created_at->diffForHumans() }}</div>
                        @if($image->caption)
                            <p style="font-size: 0.8rem; color: var(--text-slate); margin: 0; line-height: 1.4;">{{ $image->caption }}</p>
                        @endif
                    </div>
                </div>
            @empty
                <div style="grid-column: 1 / -1; text-align: center; padding: 60px; background: var(--bg-main); border-radius: 20px;">
                    <i class="fas fa-images" style="font-size: 3rem; color: var(--border); margin-bottom: 20px; display: block;"></i>
                    <p style="color: var(--text-muted);">No images in this gallery yet.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Upload Form -->
    <div class="dashboard-card" style="height: fit-content;">
        <h3 class="font-heading" style="margin-bottom: 20px;">Upload New Images</h3>
        
        <form action="{{ route('tourism.packages.gallery.store', $package->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group" style="margin-bottom: 20px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 700; color: var(--text-muted); margin-bottom: 8px;">Select Images (Max 2MB per image)</label>
                <div style="border: 2px dashed var(--border); padding: 30px; border-radius: 15px; text-align: center; cursor: pointer;" onclick="document.getElementById('images-upload').click()">
                    <i class="fas fa-cloud-upload-alt" style="font-size: 2rem; color: var(--accent); margin-bottom: 10px; display: block;"></i>
                    <span style="font-size: 0.85rem; color: var(--text-muted);">Click to browse or drag & drop</span>
                    <input type="file" id="images-upload" name="images[]" accept="image/*" multiple style="display: none;" onchange="previewImages(this)">
                </div>
                <div id="preview-container" style="margin-top: 15px; display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px;">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 700; color: var(--text-muted); margin-bottom: 8px;">Caption (Optional - applies to all)</label>
                <input type="text" name="caption" class="form-control" placeholder="Describe these moments..." style="width: 100%; padding: 12px; border-radius: 10px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);">
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 15px; border-radius: 12px; font-weight: 800;">
                <i class="fas fa-upload" style="margin-right: 8px;"></i> Upload to Gallery
            </button>
        </form>
    </div>
</div>

<script>
function previewImages(input) {
    const container = document.getElementById('preview-container');
    container.innerHTML = '';
    
    if (input.files) {
        Array.from(input.files).forEach(file => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.style.width = '100%';
                img.style.aspectRatio = '1';
                img.style.objectFit = 'cover';
                img.style.borderRadius = '8px';
                img.style.border = '1px solid var(--border)';
                container.appendChild(img);
            }
            reader.readAsDataURL(file);
        });
    }
}
</script>

<style>
.gallery-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
}
</style>
@endsection
