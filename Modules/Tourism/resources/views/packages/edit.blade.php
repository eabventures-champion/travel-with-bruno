@extends('admin::layouts.master')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>Edit Tourism Package</h1>
        <p>Update the travel experience details.</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.tourism.packages.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </div>
</div>

<div class="dashboard-card mt-20">
    <form action="{{ route('admin.tourism.packages.update', $package->id) }}" method="POST" class="admin-form" enctype="multipart/form-data" 
          x-data="{ 
            packageType: '{{ $package->package_type }}',
            title: '{{ addslashes($package->title) }}',
            slug: '{{ $package->slug }}',
            generateSlug() {
                this.slug = this.title.toLowerCase()
                    .replace(/[^\w ]+/g, '')
                    .replace(/ +/g, '-');
            }
          }">
        @csrf
        @method('PUT')
        <div class="form-grid">
            <div class="form-group">
                <label for="package_type">Package Type</label>
                <select id="package_type" name="package_type" class="form-control" required x-model="packageType">
                    <option value="fixed">Fixed Destination (Book Anytime)</option>
                    <option value="scheduled">Scheduled Group Tour (Event-Based)</option>
                </select>
            </div>
            <div class="form-group">
                <label for="title">Package Title</label>
                <input type="text" id="title" name="title" class="form-control" value="{{ $package->title }}" required 
                       x-model="title" @input="generateSlug()">
            </div>
            <div class="form-group">
                <label for="slug">Slug</label>
                <input type="text" id="slug" name="slug" class="form-control" value="{{ $package->slug }}" required x-model="slug">
            </div>
            <div class="form-group">
                <label for="category_id">Category</label>
                <select id="category_id" name="category_id" class="form-control" required>
                    <option value="">Select Category</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ $package->category_id == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="price">Base Price (₵)</label>
                <input type="number" step="0.01" id="price" name="price" class="form-control" value="{{ $package->price }}" required>
            </div>
            <div class="form-group">
                <label for="duration">Duration</label>
                <input type="text" id="duration" name="duration" class="form-control" value="{{ $package->duration }}" required>
            </div>
            <div class="form-group">
                <label for="location">Primary Location</label>
                <input type="text" id="location" name="location" class="form-control" value="{{ $package->location }}" required>
            </div>

            <template x-if="packageType === 'scheduled'">
                <div style="display: contents;">
                    <div class="form-group">
                        <label for="departure_date">Departure Date</label>
                        <input type="date" id="departure_date" name="departure_date" class="form-control" value="{{ $package->departure_date ? $package->departure_date->format('Y-m-d') : '' }}" required>
                    </div>
                    <div class="form-group">
                        <label for="return_date">Return Date</label>
                        <input type="date" id="return_date" name="return_date" class="form-control" value="{{ $package->return_date ? $package->return_date->format('Y-m-d') : '' }}" required>
                    </div>
                    <div class="form-group">
                        <label for="max_guests">Max Guests</label>
                        <input type="number" id="max_guests" name="max_guests" class="form-control" value="{{ $package->max_guests }}" required>
                    </div>
                </div>
            </template>

            <div class="form-group full-width">
                <label for="short_description">Short Description</label>
                <textarea id="short_description" name="short_description" class="form-control" rows="2">{{ $package->short_description }}</textarea>
            </div>
            <div class="form-group full-width">
                <label for="description">Full Description</label>
                <textarea id="description" name="description" class="form-control" rows="5">{{ $package->description }}</textarea>
            </div>
            <div class="form-group">
                <label for="image">Featured Image</label>
                @if($package->image)
                    <div style="margin-bottom: 10px;">
                        <img src="{{ asset('storage/' . $package->image) }}" alt="Current Image" style="width: 150px; height: 100px; object-fit: cover; border-radius: 8px; border: 1px solid #ddd;">
                        <p style="font-size: 0.75rem; color: #666; margin: 5px 0;">Current Image</p>
                    </div>
                @endif
                <input type="file" id="image" name="image" class="form-control" accept="image/*">
            </div>
            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status" class="form-control">
                    <option value="active" {{ $package->status == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ $package->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="archived" {{ $package->status == 'archived' ? 'selected' : '' }}>Archived</option>
                </select>
            </div>
        </div>

        <div class="form-footer mt-20">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Update Package
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
        gap: 25px;
    }
    .full-width {
        grid-column: 1 / -1;
    }
    .form-footer {
        padding-top: 30px;
        border-top: 1px solid var(--border);
        display: flex;
        justify-content: flex-end;
    }
</style>
@endpush
