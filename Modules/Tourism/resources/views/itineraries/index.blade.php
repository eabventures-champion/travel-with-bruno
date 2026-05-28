@extends('admin::layouts.master')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>Package Itinerary: {{ $package->title }}</h1>
        <p>Manage the day-by-day plan for this tour.</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.tourism.packages.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Packages
        </a>
    </div>
</div>

<div class="itinerary-container mt-20" x-data="{ 
    isEditing: false, 
    editId: null, 
    dayNumber: {{ $package->itineraries->count() + 1 }}, 
    title: '', 
    description: '',
    
    startEdit(item) {
        this.isEditing = true;
        this.editId = item.id;
        this.dayNumber = item.day_number;
        this.title = item.title;
        this.description = item.description;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    },
    
    resetForm() {
        this.isEditing = false;
        this.editId = null;
        this.dayNumber = {{ $package->itineraries->count() + 1 }};
        this.title = '';
        this.description = '';
    }
}">
    <!-- Itinerary List -->
    <div class="itinerary-list-section">
        <div class="dashboard-card h-full">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
                <h3 style="font-family: 'Outfit', sans-serif;">Planned Itinerary</h3>
                <span class="badge badge-secondary">{{ $package->itineraries->count() }} Days Total</span>
            </div>

            <div class="itinerary-timeline">
                @forelse($package->itineraries->sortBy('day_number') as $item)
                    <div class="timeline-item">
                        <div class="timeline-marker">
                            <div class="marker-circle">{{ $item->day_number }}</div>
                            <div class="marker-line"></div>
                        </div>
                        <div class="timeline-content card" :style="editId === {{ $item->id }} ? 'border-color: var(--primary); background: rgba(30, 58, 138, 0.05);' : ''">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 20px;">
                                <div style="flex: 1;">
                                    <h4 style="margin: 0; font-family: 'Outfit', sans-serif; font-size: 1.1rem; color: #3b82f6;">{{ $item->title }}</h4>
                                    <p style="margin-top: 10px; font-size: 0.95rem; line-height: 1.6;">{{ $item->description }}</p>
                                </div>
                                <div style="display: flex; gap: 10px;">
                                    <button @click="startEdit({{ json_encode($item) }})" class="action-btn edit" style="width: 32px; height: 32px; background: #e0f2fe; color: #0369a1; border-radius: 8px; border: none; cursor: pointer;">
                                        <i class="fas fa-edit" style="font-size: 0.8rem;"></i>
                                    </button>
                                    <form action="{{ route('admin.tourism.packages.itineraries.destroy', [$package->id, $item->id]) }}" method="POST" onsubmit="return confirm('Remove this day?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="action-btn delete" style="width: 32px; height: 32px; background: #fee2e2; color: #b91c1c; border-radius: 8px; border: none; cursor: pointer;">
                                            <i class="fas fa-trash" style="font-size: 0.8rem;"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="empty-itinerary">
                        <div class="empty-icon">
                            <i class="fas fa-route"></i>
                        </div>
                        <h4>No Itinerary Days Yet</h4>
                        <p>Start building the travel plan using the form on the right.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Add/Edit Day Form -->
    <div class="itinerary-form-section">
        <div class="dashboard-card sticky-form">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                <h3 style="margin: 0; font-family: 'Outfit', sans-serif;" x-text="isEditing ? 'Edit Itinerary Day' : 'Add Itinerary Day'"></h3>
                <button x-show="isEditing" @click="resetForm()" class="btn btn-secondary" style="padding: 5px 12px; font-size: 0.8rem;">Cancel</button>
            </div>

            <form :action="isEditing ? '{{ route('admin.tourism.packages.itineraries.index', $package->id) }}/' + editId : '{{ route('admin.tourism.packages.itineraries.store', $package->id) }}'" method="POST" class="admin-form">
                @csrf
                <template x-if="isEditing">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="day_number">Day Number</label>
                    <input type="number" id="day_number" name="day_number" x-model="dayNumber" class="form-control" required style="width: 100%;">
                </div>
                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="title">Day Title</label>
                    <input type="text" id="title" name="title" x-model="title" class="form-control" placeholder="e.g. Arrival & Orientation" required style="width: 100%;">
                </div>
                <div class="form-group" style="margin-bottom: 25px;">
                    <label for="description">Activities Description</label>
                    <textarea id="description" name="description" x-model="description" class="form-control" rows="6" placeholder="Describe the activities for this day" required style="width: 100%;"></textarea>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 15px; font-size: 1rem; border-radius: 12px; font-weight: 700; background: #3b82f6; border: none; color: white; cursor: pointer;">
                    <i class="fas" :class="isEditing ? 'fa-save' : 'fa-plus-circle'" style="margin-right: 8px;"></i>
                    <span x-text="isEditing ? 'Save Changes' : 'Add to Itinerary'"></span>
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .itinerary-container {
        display: grid;
        grid-template-columns: 1fr 380px;
        gap: 30px;
        align-items: start;
    }

    @media (max-width: 1200px) {
        .itinerary-container {
            grid-template-columns: 1fr;
        }
    }

    .sticky-form {
        position: sticky;
        top: 20px;
    }

    .dashboard-card {
        background: #1e293b;
        color: white;
        padding: 30px;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }

    /* Timeline Styling */
    .itinerary-timeline {
        padding: 10px 0;
    }

    .timeline-item {
        display: flex;
        gap: 20px;
        margin-bottom: 0;
    }

    .timeline-marker {
        display: flex;
        flex-direction: column;
        align-items: center;
        width: 40px;
    }

    .marker-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #3b82f6;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-family: 'Outfit', sans-serif;
        z-index: 2;
        box-shadow: 0 4px 10px rgba(59, 130, 246, 0.3);
    }

    .marker-line {
        flex: 1;
        width: 2px;
        background: rgba(255, 255, 255, 0.1);
        margin: 5px 0;
    }

    .timeline-item:last-child .marker-line {
        display: none;
    }

    .timeline-content {
        flex: 1;
        padding: 25px !important;
        margin-bottom: 30px !important;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        transition: transform 0.2s, box-shadow 0.2s;
        border-radius: 15px;
        color: rgba(255, 255, 255, 0.8);
    }

    .timeline-content p {
        color: rgba(255, 255, 255, 0.6);
    }

    .timeline-content:hover {
        transform: translateX(5px);
        background: rgba(255, 255, 255, 0.08);
    }

    /* Form Styling */
    .form-group label {
        display: block;
        margin-bottom: 10px;
        font-weight: 600;
        color: rgba(255, 255, 255, 0.7);
        font-size: 0.9rem;
    }

    .form-control {
        width: 100%;
        padding: 12px 16px;
        border-radius: 10px;
        border: 1px solid rgba(255, 255, 255, 0.1);
        background: rgba(255, 255, 255, 0.05);
        font-size: 1rem;
        transition: all 0.3s;
        color: white;
        box-sizing: border-box;
    }

    .form-control:focus {
        border-color: #3b82f6;
        background: rgba(255, 255, 255, 0.08);
        outline: none;
    }

    /* Empty State */
    .empty-itinerary {
        text-align: center;
        padding: 80px 20px;
        background: rgba(255, 255, 255, 0.02);
        border-radius: 20px;
        border: 2px dashed rgba(255, 255, 255, 0.1);
    }

    .empty-icon {
        width: 80px;
        height: 80px;
        background: rgba(255, 255, 255, 0.05);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
        font-size: 2rem;
        color: rgba(255, 255, 255, 0.2);
    }
</style>
@endpush
