@extends('admin::layouts.master')
@section('title', 'Package Categories')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>Tourism Categories</h1>
        <p>Group your travel packages for better organization.</p>
    </div>
</div>

<div class="grid-2 mt-20" x-data="{ 
    editing: false,
    categoryId: null,
    name: '',
    slug: '',
    description: '',
    isActive: true,
    actionUrl: '{{ route('admin.tourism.categories.store') }}',

    editCategory(category) {
        this.editing = true;
        this.categoryId = category.id;
        this.name = category.name;
        this.slug = category.slug;
        this.description = category.description || '';
        this.isActive = category.is_active;
        this.actionUrl = `/admin/tourism/categories/${category.id}`;
        
        // Scroll to form
        window.scrollTo({ top: 0, behavior: 'smooth' });
    },

    resetForm() {
        this.editing = false;
        this.categoryId = null;
        this.name = '';
        this.slug = '';
        this.description = '';
        this.isActive = true;
        this.actionUrl = '{{ route('admin.tourism.categories.store') }}';
    },

    generateSlug() {
        this.slug = this.name.toLowerCase()
            .replace(/[^\w ]+/g, '')
            .replace(/ +/g, '-');
    }
}">
    <!-- Category List -->
    <div class="dashboard-card">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Packages</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                        <tr :class="categoryId == {{ $category->id }} ? 'bg-primary-light' : ''">
                            <td>
                                <div style="font-weight: 600;">{{ $category->name }}</div>
                                <div style="font-size: 0.8rem; color: var(--text-muted);">{{ $category->slug }}</div>
                            </td>
                            <td>{{ $category->packages_count }}</td>
                            <td>
                                <span class="status-badge {{ $category->is_active ? 'status-active' : 'status-inactive' }}">
                                    {{ $category->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button type="button" @click="editCategory({{ json_encode($category) }})" class="action-btn edit" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="{{ route('admin.tourism.categories.destroy', $category->id) }}" method="POST" onsubmit="return confirm('Are you sure?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="action-btn delete" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">No categories found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="pagination-container mt-10">
            {{ $categories->links() }}
        </div>
    </div>

    <!-- Quick Add/Edit Form -->
    <div class="dashboard-card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 x-text="editing ? 'Edit Category' : 'Quick Add Category'"></h3>
            <button type="button" x-show="editing" @click="resetForm()" class="btn btn-secondary btn-sm" style="padding: 5px 10px; font-size: 0.8rem;">
                <i class="fas fa-plus"></i> New
            </button>
        </div>
        <form :action="actionUrl" method="POST" class="admin-form">
            @csrf
            <template x-if="editing">
                <input type="hidden" name="_method" value="PUT">
            </template>

            <div class="form-group">
                <label for="name">Category Name</label>
                <input type="text" id="name" name="name" class="form-control" placeholder="e.g. Adventure Tours" required
                       x-model="name" @input="generateSlug()">
            </div>
            <div class="form-group">
                <label for="slug">Slug</label>
                <input type="text" id="slug" name="slug" class="form-control" placeholder="e.g. adventure-tours" required x-model="slug">
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" class="form-control" rows="3" placeholder="Optional description" x-model="description"></textarea>
            </div>
            <div class="form-group">
                <label class="flex-items-center" style="display: flex; gap: 10px; cursor: pointer;">
                    <input type="checkbox" name="is_active" value="1" x-model="isActive">
                    <span>Active Category</span>
                </label>
            </div>
            <div class="mt-20">
                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    <i :class="editing ? 'fas fa-save' : 'fas fa-plus'"></i> 
                    <span x-text="editing ? 'Update Category' : 'Create Category'"></span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<style>
    .grid-2 {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 24px;
    }
    @media (max-width: 992px) {
        .grid-2 {
            grid-template-columns: 1fr;
        }
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
    }
    .form-control:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(30, 58, 138, 0.1);
        outline: none;
    }
</style>
@endpush
