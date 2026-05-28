@extends('admin::layouts.master')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>Create Tourism Package</h1>
        <p>Define a new travel experience for your customers.</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.tourism.packages.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </div>
</div>

<div class="dashboard-card mt-20">
    <form action="{{ route('admin.tourism.packages.store') }}" method="POST" class="admin-form" enctype="multipart/form-data"
          x-data="{ 
            packageType: 'fixed', 
            title: '', 
            slug: '',
            generateSlug() {
                this.slug = this.title.toLowerCase()
                    .replace(/[^\w ]+/g, '')
                    .replace(/ +/g, '-');
            }
          }">
        @csrf
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
                <input type="text" id="title" name="title" class="form-control" placeholder="e.g. 3-Day Safari in Mole National Park" required 
                       x-model="title" @input="generateSlug()">
            </div>
            <div class="form-group">
                <label for="slug">Slug</label>
                <input type="text" id="slug" name="slug" class="form-control" placeholder="e.g. mole-national-park-safari" required x-model="slug">
            </div>
            <div class="form-group">
                <label for="category_id">Category</label>
                <select id="category_id" name="category_id" class="form-control" required>
                    <option value="">Select Category</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="price">Base Price (₵)</label>
                <input type="number" step="0.01" id="price" name="price" class="form-control" placeholder="0.00" required>
            </div>
            <div class="form-group">
                <label for="duration">Duration</label>
                <input type="text" id="duration" name="duration" class="form-control" placeholder="e.g. 3 Days, 2 Nights" required>
            </div>
            <div class="form-group">
                <label for="location">Primary Location</label>
                <input type="text" id="location" name="location" class="form-control" placeholder="e.g. Northern Region, Ghana" required>
            </div>

            <template x-if="packageType === 'scheduled'">
                <div style="display: contents;">
                    <div class="form-group">
                        <label for="departure_date">Departure Date</label>
                        <input type="date" id="departure_date" name="departure_date" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="return_date">Return Date</label>
                        <input type="date" id="return_date" name="return_date" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="max_guests">Max Guests</label>
                        <input type="number" id="max_guests" name="max_guests" class="form-control" placeholder="e.g. 20" required>
                    </div>
                </div>
            </template>
            <div class="form-group full-width">
                <label for="short_description">Short Description</label>
                <textarea id="short_description" name="short_description" class="form-control" rows="2" placeholder="Brief overview for cards"></textarea>
            </div>
            <div class="form-group full-width">
                <label for="description">Full Description</label>
                <textarea id="description" name="description" class="form-control" rows="5" placeholder="Detailed package information"></textarea>
            </div>
            <div class="form-group">
                <label for="image">Featured Image</label>
                <input type="file" id="image" name="image" class="form-control" accept="image/*">
            </div>
            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status" class="form-control">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="archived">Archived</option>
                </select>
            </div>
        </div>

        <div class="form-footer mt-20">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Save Package
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
