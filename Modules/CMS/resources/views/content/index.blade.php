@extends('admin::layouts.master')

@section('title', 'Homepage Content Management')

@push('styles')
<style>
    /* Switch Toggles */
    .switch {
        position: relative;
        display: inline-block;
        width: 46px;
        height: 24px;
    }
    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #cbd5e1;
        transition: .4s;
    }
    .slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .4s;
    }
    input:checked + .slider {
        background-color: var(--accent);
    }
    input:focus + .slider {
        box-shadow: 0 0 1px var(--accent);
    }
    input:checked + .slider:before {
        transform: translateX(22px);
    }
    .slider.round {
        border-radius: 24px;
    }
    .slider.round:before {
        border-radius: 50%;
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>Homepage Content</h1>
        <p>Manage titles, descriptions, and section content for the homepage.</p>
    </div>
</div>

<form action="{{ route('admin.homepage.content.update') }}" method="POST" enctype="multipart/form-data">
    @csrf
    
    <div style="display: flex; flex-direction: column; gap: 30px;">
        <!-- Section Visibility -->
        <div class="card">
            <h3 class="font-heading" style="margin-bottom: 20px; border-bottom: 2px solid var(--accent); padding-bottom: 10px; display: inline-block;">Section Visibility</h3>
            <p style="color: var(--text-slate); margin-bottom: 20px; font-size: 0.9rem;">Toggle which sections should be visible on the homepage.</p>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                @php
                    $visibilitySections = [
                        'show_ventures' => 'Explore Our Ventures',
                        'show_destinations' => 'Explore Our Destinations',
                        'show_scheduled_tours' => 'Upcoming Group Tours',
                        'show_fleet' => 'Our Premium Fleet',
                        'show_transfers' => 'Airport Transfers'
                    ];
                @endphp

                @foreach($visibilitySections as $key => $label)
                <div style="display: flex; align-items: center; justify-content: space-between; background: var(--bg-main); padding: 15px; border-radius: 10px; border: 1px solid var(--border);">
                    <span style="font-weight: 600; font-size: 0.95rem;">{{ $label }}</span>
                    <label class="switch">
                        <input type="checkbox" name="{{ $key }}" value="1" {{ (!isset($settings[$key]) || $settings[$key] == '1') ? 'checked' : '' }}>
                        <span class="slider round"></span>
                    </label>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Services Section -->
        <div class="card">
            <h3 class="font-heading" style="margin-bottom: 20px; border-bottom: 2px solid var(--accent); padding-bottom: 10px; display: inline-block;">Explore Our Ventures Section</h3>
            
            <div style="margin-bottom: 30px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Main Heading</label>
                <input type="text" name="services_heading" value="{{ $settings['services_heading'] ?? 'Explore Our Ventures' }}" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);">
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 20px;">
                <!-- Venture 1: Destinations -->
                <div style="background: var(--bg-main); padding: 20px; border-radius: 15px; border: 1px solid var(--border);">
                    <h4 style="margin-bottom: 15px; color: var(--primary); font-family: 'Outfit', sans-serif; font-weight: 700;">Venture 1: On-Demand Destinations</h4>
                    <div style="display: flex; gap: 15px; margin-bottom: 15px;">
                        <div style="flex: 1;">
                            <label style="display: block; margin-bottom: 5px; font-size: 0.85rem;">Title</label>
                            <input type="text" name="venture_1_title" value="{{ $settings['venture_1_title'] ?? 'On-Demand Destinations' }}" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid var(--border); background: var(--bg-card); color: var(--text-main);">
                        </div>
                        <div style="width: 150px;">
                            <label style="display: block; margin-bottom: 5px; font-size: 0.85rem;">Venture Image</label>
                            <input type="file" name="venture_1_image" onchange="previewImage(this, 'preview_1')" style="width: 100%; font-size: 0.7rem;">
                        </div>
                    </div>
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; margin-bottom: 5px; font-size: 0.85rem;">Preview</label>
                        <img id="preview_1" src="{{ !empty($settings['venture_1_image'] ?? '') ? asset('storage/' . $settings['venture_1_image']) : 'https://images.unsplash.com/photo-1530789253516-ad160829c9ad?auto=format&fit=crop&q=80&w=1000' }}" alt="" style="width: 100%; height: 100px; object-fit: cover; border-radius: 10px; border: 1px solid var(--border);">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 5px; font-size: 0.85rem;">Description</label>
                        <textarea name="venture_1_desc" rows="2" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid var(--border); background: var(--bg-card); color: var(--text-main);">{{ $settings['venture_1_desc'] ?? 'Bespoke tour packages for individuals and corporate groups. Choose your destination and date.' }}</textarea>
                    </div>
                </div>

                <!-- Venture 2: Organized Tours -->
                <div style="background: var(--bg-main); padding: 20px; border-radius: 15px; border: 1px solid var(--border);">
                    <h4 style="margin-bottom: 15px; color: var(--primary); font-family: 'Outfit', sans-serif; font-weight: 700;">Venture 2: Organized Tours</h4>
                    <div style="display: flex; gap: 15px; margin-bottom: 15px;">
                        <div style="flex: 1;">
                            <label style="display: block; margin-bottom: 5px; font-size: 0.85rem;">Title</label>
                            <input type="text" name="venture_2_title" value="{{ $settings['venture_2_title'] ?? 'Organized Tours' }}" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid var(--border); background: var(--bg-card); color: var(--text-main);">
                        </div>
                        <div style="width: 150px;">
                            <label style="display: block; margin-bottom: 5px; font-size: 0.85rem;">Venture Image</label>
                            <input type="file" name="venture_2_image" onchange="previewImage(this, 'preview_2')" style="width: 100%; font-size: 0.7rem;">
                        </div>
                    </div>
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; margin-bottom: 5px; font-size: 0.85rem;">Preview</label>
                        <img id="preview_2" src="{{ !empty($settings['venture_2_image'] ?? '') ? asset('storage/' . $settings['venture_2_image']) : 'https://images.unsplash.com/photo-1533105079780-92b9be482077?auto=format&fit=crop&q=80&w=1000' }}" alt="" style="width: 100%; height: 100px; object-fit: cover; border-radius: 10px; border: 1px solid var(--border);">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 5px; font-size: 0.85rem;">Description</label>
                        <textarea name="venture_2_desc" rows="2" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid var(--border); background: var(--bg-card); color: var(--text-main);">{{ $settings['venture_2_desc'] ?? 'Join our organized group adventures with set departure dates and shared experiences.' }}</textarea>
                    </div>
                </div>

                <!-- Venture 3: Car Hiring -->
                <div style="background: var(--bg-main); padding: 20px; border-radius: 15px; border: 1px solid var(--border);">
                    <h4 style="margin-bottom: 15px; color: var(--primary); font-family: 'Outfit', sans-serif; font-weight: 700;">Venture 3: Car Hiring Services</h4>
                    <div style="display: flex; gap: 15px; margin-bottom: 15px;">
                        <div style="flex: 1;">
                            <label style="display: block; margin-bottom: 5px; font-size: 0.85rem;">Title</label>
                            <input type="text" name="venture_3_title" value="{{ $settings['venture_3_title'] ?? 'Car Hiring Services' }}" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid var(--border); background: var(--bg-card); color: var(--text-main);">
                        </div>
                        <div style="width: 150px;">
                            <label style="display: block; margin-bottom: 5px; font-size: 0.85rem;">Venture Image</label>
                            <input type="file" name="venture_3_image" onchange="previewImage(this, 'preview_3')" style="width: 100%; font-size: 0.7rem;">
                        </div>
                    </div>
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; margin-bottom: 5px; font-size: 0.85rem;">Preview</label>
                        <img id="preview_3" src="{{ !empty($settings['venture_3_image'] ?? '') ? asset('storage/' . $settings['venture_3_image']) : 'https://images.unsplash.com/photo-1549317661-bd32c8ce0db2?auto=format&fit=crop&q=80&w=1000' }}" alt="" style="width: 100%; height: 100px; object-fit: cover; border-radius: 10px; border: 1px solid var(--border);">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 5px; font-size: 0.85rem;">Description</label>
                        <textarea name="venture_3_desc" rows="2" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid var(--border); background: var(--bg-card); color: var(--text-main);">{{ $settings['venture_3_desc'] ?? 'Professional fleet management and vehicle rentals for corporate and personal transportation needs.' }}</textarea>
                    </div>
                </div>

                <!-- Venture 4: Transfer Services -->
                <div style="background: var(--bg-main); padding: 20px; border-radius: 15px; border: 1px solid var(--border);">
                    <h4 style="margin-bottom: 15px; color: var(--primary); font-family: 'Outfit', sans-serif; font-weight: 700;">Venture 4: Transfer Services</h4>
                    <div style="display: flex; gap: 15px; margin-bottom: 15px;">
                        <div style="flex: 1;">
                            <label style="display: block; margin-bottom: 5px; font-size: 0.85rem;">Title</label>
                            <input type="text" name="venture_4_title" value="{{ $settings['venture_4_title'] ?? 'Transfer Services' }}" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid var(--border); background: var(--bg-card); color: var(--text-main);">
                        </div>
                        <div style="width: 150px;">
                            <label style="display: block; margin-bottom: 5px; font-size: 0.85rem;">Venture Image</label>
                            <input type="file" name="venture_4_image" onchange="previewImage(this, 'preview_4')" style="width: 100%; font-size: 0.7rem;">
                        </div>
                    </div>
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; margin-bottom: 5px; font-size: 0.85rem;">Preview</label>
                        <img id="preview_4" src="{{ !empty($settings['venture_4_image'] ?? '') ? asset('storage/' . $settings['venture_4_image']) : 'https://images.unsplash.com/photo-1436491865332-7a61a109cc05?auto=format&fit=crop&q=80&w=1000' }}" alt="" style="width: 100%; height: 100px; object-fit: cover; border-radius: 10px; border: 1px solid var(--border);">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 5px; font-size: 0.85rem;">Description</label>
                        <textarea name="venture_4_desc" rows="2" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid var(--border); background: var(--bg-card); color: var(--text-main);">{{ $settings['venture_4_desc'] ?? 'Reliable airport pickups, drop-offs, and city-to-city transfers for seamless travel.' }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Explore Destinations Section -->
        <div class="card">
            <h3 class="font-heading" style="margin-bottom: 20px; border-bottom: 2px solid var(--accent); padding-bottom: 10px; display: inline-block;">Explore Our Destinations Section</h3>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 600;">Section Title</label>
                    <input type="text" name="destinations_title" value="{{ $settings['destinations_title'] ?? 'Explore Our Destinations' }}" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 600;">Section Subtitle</label>
                    <textarea name="destinations_subtitle" rows="2" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);">{{ $settings['destinations_subtitle'] ?? 'Bespoke tour packages available for individuals and corporate groups. Choose your destination and let us handle the rest.' }}</textarea>
                </div>
            </div>
        </div>

        <!-- Upcoming Group Tours Section -->
        <div class="card">
            <h3 class="font-heading" style="margin-bottom: 20px; border-bottom: 2px solid var(--accent); padding-bottom: 10px; display: inline-block;">Upcoming Group Tours Section</h3>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 600;">Section Badge (Small text above title)</label>
                    <input type="text" name="scheduled_tours_badge" value="{{ $settings['scheduled_tours_badge'] ?? 'Don\'t Miss Out' }}" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 600;">Section Title</label>
                    <input type="text" name="scheduled_tours_title" value="{{ $settings['scheduled_tours_title'] ?? 'Upcoming Group Tours' }}" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);">
                </div>
            </div>
            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Section Subtitle</label>
                <textarea name="scheduled_tours_subtitle" rows="2" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);">{{ $settings['scheduled_tours_subtitle'] ?? 'Join our organized group adventures. Perfect for meeting new people and shared experiences.' }}</textarea>
            </div>
        </div>

        <!-- Our Premium Fleet Section -->
        <div class="card">
            <h3 class="font-heading" style="margin-bottom: 20px; border-bottom: 2px solid var(--accent); padding-bottom: 10px; display: inline-block;">Our Premium Fleet Section</h3>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 600;">Section Title</label>
                    <input type="text" name="fleet_title" value="{{ $settings['fleet_title'] ?? 'Our Premium Fleet' }}" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 600;">Section Subtitle</label>
                    <textarea name="fleet_subtitle" rows="2" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);">{{ $settings['fleet_subtitle'] ?? 'Choose from our range of well-maintained vehicles for your personal or corporate travel needs.' }}</textarea>
                </div>
            </div>
        </div>

        <!-- Airport Transfers Section -->
        <div class="card">
            <h3 class="font-heading" style="margin-bottom: 20px; border-bottom: 2px solid var(--accent); padding-bottom: 10px; display: inline-block;">Airport Transfers Section</h3>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 600;">Section Badge (Small text above title)</label>
                    <input type="text" name="transfers_badge" value="{{ $settings['transfers_badge'] ?? 'Seamless Travel' }}" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 600;">Section Title</label>
                    <input type="text" name="transfers_title" value="{{ $settings['transfers_title'] ?? 'Airport Transfers' }}" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);">
                </div>
            </div>
            <div>
                <label style="display: block; margin-bottom: 8px; font-weight: 600;">Section Subtitle</label>
                <textarea name="transfers_subtitle" rows="2" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);">{{ $settings['transfers_subtitle'] ?? 'Reliable, comfortable, and fixed-rate point-to-point transfer services. Whether it\'s an airport run or a trip across town, we\'ve got you covered.' }}</textarea>
            </div>
        </div>

        <!-- About Section -->
        <div class="card">
            <h3 class="font-heading" style="margin-bottom: 20px; border-bottom: 2px solid var(--accent); padding-bottom: 10px; display: inline-block;">About Section</h3>
            <div style="display: grid; grid-template-columns: 1fr; gap: 20px;">
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 600;">About Title</label>
                    <input type="text" name="about_title" value="{{ $settings['about_title'] ?? 'About Bruno Heights' }}" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 600;">About Content</label>
                    <textarea name="about_content" rows="6" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);">{{ $settings['about_content'] ?? 'We provide premium services in tourism and logistics across West Africa.' }}</textarea>
                </div>
            </div>
        </div>
    </div>

    <div style="margin-top: 30px; text-align: center;">
        <button type="submit" class="btn btn-primary" style="padding: 15px 50px; font-size: 1.1rem;">
            <i class="fas fa-save"></i> Save All Changes
        </button>
    </div>
</form>

<script>
    function previewImage(input, previewId) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById(previewId).src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endsection
