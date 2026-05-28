@extends('admin::layouts.master')

@section('title', 'Homepage Slides')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>Homepage Slides</h1>
        <p>Manage the slides displayed on the website's homepage hero section.</p>
    </div>
    <a href="{{ route('admin.slides.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add New Slide
    </a>
</div>

<div class="card">
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Title</th>
                    <th>Order</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($slides as $slide)
                <tr>
                    <td>
                        <img src="{{ asset('storage/' . $slide->image_path) }}" alt="" style="width: 100px; height: 60px; object-fit: cover; border-radius: 8px;">
                    </td>
                    <td>
                        <div style="font-weight: 700;">{{ $slide->title }}</div>
                        <div style="font-size: 0.8rem; color: var(--text-muted);">{{ $slide->subtitle }}</div>
                    </td>
                    <td>{{ $slide->order }}</td>
                    <td>
                        <form action="{{ route('admin.slides.toggle', $slide) }}" method="POST">
                            @csrf
                            <button type="submit" class="status-badge {{ $slide->is_active ? 'status-active' : 'status-inactive' }}" style="border: none; cursor: pointer;">
                                {{ $slide->is_active ? 'Active' : 'Inactive' }}
                            </button>
                        </form>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <a href="{{ route('admin.slides.edit', $slide) }}" class="action-btn edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.slides.destroy', $slide) }}" method="POST" onsubmit="return confirm('Are you sure?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="action-btn delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center">No slides found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
