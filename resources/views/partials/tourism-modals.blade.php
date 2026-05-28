<style>[x-cloak] { display: none !important; }</style>
<!-- Lightbox Modal -->
<div x-show="lightboxOpen" 
     class="lightbox-modal" 
     @click="lightboxOpen = false"
     @keydown.escape.window="lightboxOpen = false"
     x-cloak>
    <button class="lightbox-close" @click.stop="lightboxOpen = false">
        <i class="fas fa-times"></i>
    </button>
    <img :src="lightboxImage" class="lightbox-content" @click.stop>
</div>

<!-- Itinerary Modal -->
<div x-show="itineraryOpen" 
     class="lightbox-modal" 
     style="background: rgba(15, 23, 42, 0.95); backdrop-filter: blur(12px);"
     @keydown.escape.window="itineraryOpen = false"
     x-cloak>
    <div class="lightbox-content" style="background: var(--bg-card); width: 100%; max-width: 800px; height: auto; max-height: 90vh; padding: 0; overflow: hidden; display: flex; flex-direction: column; transform: none; cursor: default; border-radius: 25px; border: 1px solid var(--border);" @click.stop>
        <!-- Modal Header -->
        <div style="background: linear-gradient(135deg, var(--primary), #1e3a8a); color: white; padding: 30px 40px; position: relative; flex-shrink: 0;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 25px;">
                <div style="flex: 1;">
                    <h2 class="font-heading" style="margin: 0; font-size: 1.6rem;">Planned Itinerary</h2>
                    <p x-text="bookingItem?.title" style="margin: 2px 0 0; opacity: 0.8; font-size: 0.9rem;"></p>
                </div>

                <!-- Schedule Info (Minimized & Positioned Right) -->
                <template x-if="bookingItem?.package_type === 'scheduled' || bookingItem?.max_guests">
                    <div style="display: flex; gap: 20px; background: rgba(255,255,255,0.08); padding: 8px 15px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.1); margin: 0 20px;">
                        <template x-if="bookingItem?.package_type === 'scheduled' && bookingItem?.departure_date">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <i class="fas fa-plane-departure" style="font-size: 0.7rem; opacity: 0.6;"></i>
                                <div>
                                    <span style="display: block; font-size: 0.5rem; opacity: 0.6; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px;">Departure</span>
                                    <span style="font-weight: 700; font-size: 0.75rem;" x-text="new Date(bookingItem.departure_date).toLocaleDateString(undefined, {month: 'short', day: 'numeric', year: 'numeric'})"></span>
                                </div>
                            </div>
                        </template>
                        <template x-if="bookingItem?.package_type === 'scheduled' && bookingItem?.return_date">
                            <div style="display: flex; align-items: center; gap: 8px; border-left: 1px solid rgba(255,255,255,0.1); padding-left: 15px;">
                                <i class="fas fa-plane-arrival" style="font-size: 0.7rem; opacity: 0.6;"></i>
                                <div>
                                    <span style="display: block; font-size: 0.5rem; opacity: 0.6; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px;">Return</span>
                                    <span style="font-weight: 700; font-size: 0.75rem;" x-text="new Date(bookingItem.return_date).toLocaleDateString(undefined, {month: 'short', day: 'numeric', year: 'numeric'})"></span>
                                </div>
                            </div>
                        </template>
                        <template x-if="bookingItem?.max_guests">
                            <div style="display: flex; align-items: center; gap: 8px; border-left: 1px solid rgba(255,255,255,0.1); padding-left: 15px;">
                                <i class="fas fa-users" style="font-size: 0.7rem; opacity: 0.6;"></i>
                                <div>
                                    <span style="display: block; font-size: 0.5rem; opacity: 0.6; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px;">Guests</span>
                                    <span style="font-weight: 700; font-size: 0.75rem;">
                                        <span x-text="bookingItem.guests_count || 0"></span> / <span x-text="bookingItem.max_guests"></span>
                                        <template x-if="bookingItem.package_type === 'scheduled'">
                                            <span style="font-size: 0.65rem; opacity: 0.8; margin-left: 5px;" :style="bookingItem.is_full ? 'color: #fecaca;' : ''">
                                                (<span x-text="bookingItem.available_spaces"></span> spaces left)
                                            </span>
                                        </template>
                                    </span>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>

                <button @click="itineraryOpen = false" style="background: rgba(255,255,255,0.1); border: none; color: white; width: 36px; height: 36px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: background 0.3s; flex-shrink: 0;">
                    <i class="fas fa-times" style="font-size: 0.9rem;"></i>
                </button>
            </div>
            
            <!-- Tabs -->
            <div style="display: flex; gap: 20px;">
                <button @click="activeTab = 'itinerary'" 
                        :style="activeTab === 'itinerary' ? 'background: white; color: var(--primary);' : 'background: rgba(255,255,255,0.1); color: white;'"
                        style="padding: 8px 20px; border-radius: 50px; font-weight: 700; font-size: 0.85rem; border: none; cursor: pointer; transition: all 0.3s;">
                    <i class="fas fa-calendar-alt" style="margin-right: 8px;"></i>Itinerary
                </button>
                <button @click="activeTab = 'gallery'" 
                        :style="activeTab === 'gallery' ? 'background: white; color: var(--primary);' : 'background: rgba(255,255,255,0.1); color: white;'"
                        style="padding: 8px 20px; border-radius: 50px; font-weight: 700; font-size: 0.85rem; border: none; cursor: pointer; transition: all 0.3s;">
                    <i class="fas fa-images" style="margin-right: 8px;"></i>Tour Gallery
                </button>
            </div>
        </div>
        
        <!-- Scrollable Content -->
        <div style="padding: 40px; overflow-y: auto; flex-grow: 1; background: var(--bg-card);">
            <!-- Itinerary Tab -->
            <div x-show="activeTab === 'itinerary'">
                <template x-if="bookingItem && bookingItem.itineraries && bookingItem.itineraries.length > 0">
                    <div style="position: relative; padding-left: 50px;">
                        <div style="position: absolute; left: 19px; top: 0; bottom: 0; width: 2px; background: var(--border);"></div>
                        
                        <template x-for="day in bookingItem.itineraries.sort((a,b) => a.day_number - b.day_number)" :key="day.id">
                            <div style="position: relative; margin-bottom: 40px;">
                                <div style="position: absolute; left: -50px; width: 40px; height: 40px; background: var(--accent); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; font-family: 'Outfit', sans-serif; z-index: 2; box-shadow: 0 4px 10px rgba(245, 158, 11, 0.3)" x-text="day.day_number"></div>
                                
                                <div style="background: var(--bg-main); padding: 25px; border-radius: 15px; border: 1px solid var(--border);">
                                    <h4 style="margin: 0 0 10px; color: var(--primary); font-family: 'Outfit', sans-serif; font-size: 1.2rem;" x-text="day.title"></h4>
                                    <p style="margin: 0; color: var(--text-main); opacity: 0.8; line-height: 1.6; font-size: 0.95rem;" x-text="day.description"></p>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>
                <template x-if="!bookingItem || !bookingItem.itineraries || bookingItem.itineraries.length === 0">
                    <div style="text-align: center; padding: 40px;">
                        <i class="fas fa-map-marked-alt" style="font-size: 3rem; color: var(--border); margin-bottom: 20px;"></i>
                        <p style="color: var(--text-muted);">Detailed itinerary for this package is coming soon!</p>
                    </div>
                </template>
            </div>

            <!-- Gallery Tab -->
            <div x-show="activeTab === 'gallery'">
                <!-- Upload Section (Super Admin only) -->
                @role('Super Admin')
                <div style="margin-bottom: 40px; padding: 25px; background: var(--bg-main); border-radius: 20px; border: 1px dashed var(--accent);">
                    <h4 style="margin: 0 0 15px; color: var(--primary); font-family: 'Outfit', sans-serif; font-size: 1rem;">Upload a Tour Moment</h4>
                    <div style="display: flex; gap: 15px; align-items: flex-end;">
                        <div style="flex: 1;">
                            <label style="display: block; font-size: 0.75rem; color: var(--text-muted); margin-bottom: 5px;">Images (Max 2MB per image)</label>
                            <input type="file" id="gallery-upload-input" @change="uploadFiles = $event.target.files" multiple accept="image/*" style="width: 100%; font-size: 0.85rem;">
                        </div>
                        <div style="flex: 1;">
                            <label style="display: block; font-size: 0.75rem; color: var(--text-muted); margin-bottom: 5px;">Caption (Optional)</label>
                            <input type="text" x-model="uploadCaption" placeholder="What's happening?" style="width: 100%; padding: 8px 12px; border-radius: 8px; border: 1px solid var(--border); font-size: 0.85rem;">
                        </div>
                        <button @click="handleUpload" :disabled="uploadFiles.length === 0 || isUploading" 
                                style="padding: 10px 25px; border-radius: 10px; background: var(--accent); color: white; font-weight: 700; border: none; cursor: pointer; display: flex; align-items: center; gap: 10px;"
                                :style="(uploadFiles.length === 0 || isUploading) ? 'opacity: 0.5; cursor: not-allowed;' : ''">
                            <i class="fas" :class="isUploading ? 'fa-spinner fa-spin' : 'fa-upload'"></i>
                            <span x-text="isUploading ? 'Uploading...' : 'Upload'"></span>
                        </button>
                    </div>
                </div>
                @endrole

                <!-- Gallery Grid -->
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 15px;">
                    <template x-for="img in galleryImages" :key="img.id">
                        <div style="position: relative; aspect-ratio: 1; border-radius: 12px; overflow: hidden; group;">
                            <img :src="img.url" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s;" class="hover-scale">
                            <div style="position: absolute; inset: 0; background: linear-gradient(to bottom, transparent, rgba(0,0,0,0.7)); padding: 10px; display: flex; flex-direction: column; justify-content: flex-end; opacity: 0; transition: opacity 0.3s;" onmouseover="this.style.opacity=1" onmouseout="this.style.opacity=0">
                                <span style="color: white; font-size: 0.65rem; font-weight: 700;" x-text="img.user_name"></span>
                                <span style="color: rgba(255,255,255,0.8); font-size: 0.6rem;" x-text="img.created_at"></span>
                            </div>
                        </div>
                    </template>
                </div>
                <template x-if="galleryImages.length === 0">
                    <div style="text-align: center; padding: 40px; background: var(--bg-main); border-radius: 20px;">
                        <i class="fas fa-camera" style="font-size: 3rem; color: var(--border); margin-bottom: 20px;"></i>
                        <p style="color: var(--text-muted);">No moments captured yet.</p>
                    </div>
                </template>
            </div>
        </div>

        <!-- Sticky Footer -->
        <div style="padding: 30px 40px; border-top: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; background: var(--bg-main); flex-shrink: 0;">
            <div>
                <span style="font-size: 0.8rem; color: var(--text-muted); display: block; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;" x-text="bookingItem?.package_type === 'scheduled' ? 'Per Person' : 'Interested in this tour?'"></span>
                <span style="font-size: 1.8rem; font-weight: 800; color: var(--primary);">₵<span x-text="new Number(bookingItem?.price).toLocaleString(undefined, {minimumFractionDigits: 2})"></span></span>
            </div>
            
            <template x-if="bookingItem?.organized_status === 'ongoing'">
                <div style="padding: 12px 25px; background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); border-radius: 12px; color: #ef4444; font-weight: 700; display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-calendar-times"></i>
                    <span>Booking Closed (Tour Ongoing)</span>
                </div>
            </template>
            
            <template x-if="bookingItem?.organized_status !== 'ongoing'">
                <button @click="openBooking(bookingItem, 'tourism')" class="btn" :class="bookingItem?.package_type === 'scheduled' ? '' : 'btn-primary'" :style="bookingItem?.package_type === 'scheduled' ? 'background: var(--accent); color: white; padding: 15px 40px; border-radius: 12px; font-weight: 800; border: none; cursor: pointer; font-size: 1rem;' : 'padding: 15px 40px; border-radius: 12px; font-weight: 800; cursor: pointer; font-size: 1rem;'">
                    <span x-text="bookingItem?.package_type === 'scheduled' ? 'Join This Tour' : 'Book This Tour Now'"></span>
                </button>
            </template>
        </div>
    </div>
</div>

<!-- Booking Modal -->
<div x-show="bookingOpen" 
     class="lightbox-modal" 
     style="background: rgba(15, 23, 42, 0.9); backdrop-filter: blur(8px);"
     @keydown.escape.window="bookingOpen = false"
     x-cloak>
    <div class="lightbox-content" style="background: var(--bg-card); width: 100%; max-width: 650px; padding: 0; overflow: hidden; display: block; transform: none; cursor: default; border-radius: 20px; border: 1px solid var(--border);" @click.stop>
        <div style="background: var(--primary); color: white; padding: 30px; position: relative;">
            <h2 class="font-heading" style="margin: 0; font-size: 1.8rem;">Confirm Your Booking</h2>
            <button @click="bookingOpen = false" style="position: absolute; top: 20px; right: 20px; background: none; border: none; color: white; font-size: 1.5rem; cursor: pointer;">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div style="padding: 40px; max-height: 70vh; overflow-y: auto;">
            <template x-if="bookingItem">
                <div>
                    <div style="display: flex; gap: 20px; margin-bottom: 30px; align-items: flex-start; background: var(--bg-main); padding: 20px; border-radius: 15px; position: relative; border: 1px solid var(--border);">
                        <img :src="bookingType === 'transfer' ? 'https://images.unsplash.com/photo-1436491865332-7a61a109cc05?auto=format&fit=crop&q=80&w=200' : (bookingItem.image ? '/storage/' + bookingItem.image : 'https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?auto=format&fit=crop&q=80&w=200')" 
                             style="width: 120px; height: 90px; object-fit: cover; border-radius: 10px;">
                        <div style="flex: 1;">
                            <span style="font-size: 0.75rem; font-weight: 800; color: var(--accent); text-transform: uppercase;" x-text="bookingType === 'transfer' ? 'Airport Transfer' : (bookingType === 'tourism' ? (bookingItem.package_type === 'scheduled' ? 'Scheduled Group Tour' : 'Custom Tourism Package') : 'Premium Vehicle')"></span>
                            <h3 class="font-heading" style="margin: 5px 0 10px; font-size: 1.3rem; color: var(--primary);" x-text="bookingType === 'transfer' ? bookingItem.airport_name : (bookingType === 'tourism' ? bookingItem.title : (bookingItem.make + ' ' + bookingItem.model))"></h3>
                            
                            <div x-show="bookingType !== 'fleet'" style="display: flex; flex-wrap: wrap; gap: 15px; font-size: 0.85rem; color: var(--text-muted); margin-top: 10px;">
                                <div x-show="bookingItem.location" style="display: flex; align-items: center; gap: 6px;">
                                    <i class="fas fa-map-marker-alt" style="color: var(--accent);"></i>
                                    <span x-text="bookingItem.location"></span>
                                </div>
                                <div x-show="bookingItem.duration" style="display: flex; align-items: center; gap: 6px;">
                                    <i class="fas fa-clock" style="color: var(--accent);"></i>
                                    <span x-text="bookingItem.duration"></span>
                                </div>
                                <div x-show="bookingItem.package_type === 'scheduled'" style="display: flex; align-items: center; gap: 6px; padding: 2px 8px; border-radius: 6px; background: rgba(30, 58, 138, 0.05);">
                                    <i class="fas fa-users" style="color: var(--accent);"></i>
                                    <span x-text="bookingItem.registered_guests || 0"></span> / <span x-text="bookingItem.max_guests"></span> guests
                                    <span style="font-weight: 700; color: var(--primary); margin-left: 5px;">(<span x-text="bookingItem.available_spaces"></span> left)</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Technical Specifications (For Fleet) -->
                    <template x-if="bookingType === 'fleet'">
                        <div style="margin-bottom: 25px;">
                            <h4 style="margin: 0 0 15px; color: var(--primary); font-family: 'Outfit', sans-serif; font-size: 1rem; display: flex; align-items: center; gap: 8px;">
                                <i class="fas fa-microchip" style="color: var(--accent);"></i> Technical Specifications
                            </h4>
                            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; background: rgba(30, 58, 138, 0.03); padding: 20px; border-radius: 15px; border: 1px solid var(--border);">
                                <div style="display: flex; flex-direction: column; gap: 4px;">
                                    <span style="font-size: 0.65rem; color: var(--text-muted); font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px;">Transmission</span>
                                    <div style="font-weight: 700; color: var(--primary); display: flex; align-items: center; gap: 8px; font-size: 0.9rem;">
                                        <i class="fas fa-cog" style="font-size: 0.8rem; color: var(--accent);"></i>
                                        <span x-text="bookingItem.transmission || 'Automatic'"></span>
                                    </div>
                                </div>
                                <div style="display: flex; flex-direction: column; gap: 4px;">
                                    <span style="font-size: 0.65rem; color: var(--text-muted); font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px;">Fuel Type</span>
                                    <div style="font-weight: 700; color: var(--primary); display: flex; align-items: center; gap: 8px; font-size: 0.9rem;">
                                        <i class="fas fa-gas-pump" style="font-size: 0.8rem; color: var(--accent);"></i>
                                        <span x-text="bookingItem.fuel_type || 'Petrol'"></span>
                                    </div>
                                </div>
                                <div style="display: flex; flex-direction: column; gap: 4px;">
                                    <span style="font-size: 0.65rem; color: var(--text-muted); font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px;">Seating Capacity</span>
                                    <div style="font-weight: 700; color: var(--primary); display: flex; align-items: center; gap: 8px; font-size: 0.9rem;">
                                        <i class="fas fa-users" style="font-size: 0.8rem; color: var(--accent);"></i>
                                        <span x-text="(bookingItem.seats || '5') + ' Passengers'"></span>
                                    </div>
                                </div>
                                <div style="display: flex; flex-direction: column; gap: 4px;">
                                    <span style="font-size: 0.65rem; color: var(--text-muted); font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px;">Luggage Capacity</span>
                                    <div style="font-weight: 700; color: var(--primary); display: flex; align-items: center; gap: 8px; font-size: 0.9rem;">
                                        <i class="fas fa-briefcase" style="font-size: 0.8rem; color: var(--accent);"></i>
                                        <span x-text="(bookingItem.luggage_capacity || '3') + ' Bags'"></span>
                                    </div>
                                </div>
                                <div x-show="bookingItem.color" style="display: flex; flex-direction: column; gap: 4px; grid-column: span 2; border-top: 1px solid var(--border); padding-top: 10px; margin-top: 5px;">
                                    <span style="font-size: 0.65rem; color: var(--text-muted); font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px;">Exterior Color</span>
                                    <div style="font-weight: 700; color: var(--primary); display: flex; align-items: center; gap: 8px; font-size: 0.9rem;">
                                        <i class="fas fa-palette" style="font-size: 0.8rem; color: var(--accent);"></i>
                                        <span x-text="bookingItem.color"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- Interest Request Form (When Full) -->
                    <template x-if="bookingType === 'tourism' && (bookingItem.is_full || bookingItem.is_booking_cutoff_reached) && !interestToken">
                        <div style="background: #f8fafc; padding: 30px; border-radius: 15px; border: 1px solid var(--border);">
                            <div style="text-align: center; margin-bottom: 25px;">
                                <div style="width: 60px; height: 60px; background: rgba(0,0,0,0.05); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px; color: var(--primary); font-size: 1.5rem;">
                                    <i class="fas fa-bullhorn"></i>
                                </div>
                                <h4 style="margin: 0; color: var(--primary); font-family: 'Outfit', sans-serif;" x-text="bookingItem.is_booking_cutoff_reached ? 'Booking is Officially Closed' : 'Tour is Currently Full'"></h4>
                                <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 5px;" x-text="bookingItem.is_booking_cutoff_reached ? 'The standard booking window has closed for this departure. Send a request of interest, and management may grant you special access!' : 'This tour has reached its capacity. Send a request of interest, and management may increase the size!'"></p>
                            </div>

                            <form action="{{ route('tourism.tour-interest.store') }}" method="POST">
                                @csrf
                                <input type="hidden" name="package_id" :value="bookingItem.id">
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                                    <div style="grid-column: span 2;">
                                        <label style="display: block; font-size: 0.8rem; font-weight: 700; color: var(--text-muted); margin-bottom: 5px;">Full Name</label>
                                        <input type="text" name="name" required placeholder="Your Name" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border);">
                                    </div>
                                    <div>
                                        <label style="display: block; font-size: 0.8rem; font-weight: 700; color: var(--text-muted); margin-bottom: 5px;">Email Address</label>
                                        <input type="email" name="email" x-model="customerEmail" @input.debounce.500ms="checkInterestDuplicate()" required placeholder="your@email.com" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border);">
                                    </div>
                                    <div>
                                        <label style="display: block; font-size: 0.8rem; font-weight: 700; color: var(--text-muted); margin-bottom: 5px;">Phone Number</label>
                                        <input type="text" name="phone" x-model="customerPhone" @input.debounce.500ms="checkInterestDuplicate()" placeholder="Contact number" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border);">
                                    </div>
                                    <!-- Interest Duplicate Error -->
                                    <div x-show="interestDuplicateError" x-transition style="grid-column: span 2; margin-top: 5px; padding: 10px; background: #fff1f2; border: 1px solid #fda4af; border-radius: 8px; color: #9f1239; font-size: 0.75rem; font-weight: 600; display: flex; align-items: center; gap: 8px;">
                                        <i class="fas fa-exclamation-circle"></i>
                                        <span x-text="interestDuplicateError"></span>
                                    </div>
                                    <div style="grid-column: span 2;">
                                        <label style="display: block; font-size: 0.8rem; font-weight: 700; color: var(--text-muted); margin-bottom: 5px;">Notes / Preferred Guest Count</label>
                                        <textarea name="notes" placeholder="Any additional information..." style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border); height: 80px;"></textarea>
                                    </div>
                                </div>
                                <button type="submit" class="btn" :disabled="interestDuplicateError !== ''"
                                        :style="interestDuplicateError !== '' ? 'opacity: 0.5; cursor: not-allowed;' : ''"
                                        style="width: 100%; background: #000; color: white; padding: 12px; border-radius: 10px; font-weight: 700; border: none; cursor: pointer;">
                                    Send Request of Interest
                                </button>
                            </form>
                        </div>
                    </template>

                    <template x-if="((bookingType === 'tourism' && !bookingItem.is_full && !bookingItem.is_booking_cutoff_reached) || bookingType !== 'tourism') || (interestToken && bookingItem && interestPackageId && String(bookingItem.id) == String(interestPackageId))">
                        <div style="display: contents;">
                            <template x-if="bookingItem.organized_status === 'ongoing'">
                                <div style="background: rgba(239, 68, 68, 0.1); color: #ef4444; padding: 15px; border-radius: 10px; margin-bottom: 20px; text-align: center; font-weight: 700;">
                                    <i class="fas fa-exclamation-circle"></i> This tour is currently ongoing and cannot be booked.
                                </div>
                            </template>
                            
                            <form x-data="{ isSubmitting: false }" 
                                  x-show="bookingItem.organized_status !== 'ongoing'" 
                                  action="{{ route('bookings.store') }}" 
                                  method="POST"
                                  x-on:submit.prevent="
                                      isSubmitting = true;
                                      const formData = new FormData($el);
                                      fetch('{{ route('bookings.store') }}', {
                                          method: 'POST',
                                          headers: {
                                              'X-Requested-With': 'XMLHttpRequest',
                                              'Accept': 'application/json'
                                          },
                                          body: formData
                                      })
                                      .then(res => res.json())
                                      .then(data => {
                                          isSubmitting = false;
                                          if (data.success) {
                                              bookingOpen = false;
                                              $dispatch('show-booking-success', {
                                                  reference: data.booking_reference,
                                                  scheduled_at: data.scheduled_at,
                                                  customer_name: data.customer_name,
                                                  url: data.view_booking_url
                                              });
                                          } else {
                                              if (data.errors) {
                                                  alert(Object.values(data.errors).flat().join('\n'));
                                              } else {
                                                  alert(data.message || 'Booking failed.');
                                              }
                                          }
                                      })
                                      .catch(err => {
                                          isSubmitting = false;
                                          console.error(err);
                                          alert('An error occurred. Please try again.');
                                      });
                                  ">
                        @csrf
                        <input type="hidden" name="bookable_id" :value="bookingItem.id">
                        <input type="hidden" name="bookable_type" :value="bookingType === 'transfer' ? 'Modules\\Fleet\\Models\\AirportTransfer' : (bookingType === 'tourism' ? 'Modules\\Tourism\\Models\\TourismPackage' : 'Modules\\Fleet\\Models\\Vehicle')">
                        <input type="hidden" name="rental_unit" :value="rentalUnit">
                        <input type="hidden" name="is_self_drive" :value="isSelfDrive ? 1 : 0">
                        <input type="hidden" name="interest_token" :value="interestToken">
                        
                        <!-- Special Booking Notice -->
                        <div x-show="interestToken && bookingItem && interestPackageId && String(bookingItem.id) == String(interestPackageId)" style="margin-bottom: 20px; padding: 15px; background: rgba(139, 92, 246, 0.1); border: 1px solid rgba(139, 92, 246, 0.2); border-radius: 12px; display: flex; align-items: center; gap: 12px;">
                            <div style="width: 40px; height: 40px; background: #8b5cf6; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0;">
                                <i class="fas fa-unlock-alt"></i>
                            </div>
                            <div>
                                <div style="font-weight: 800; color: #8b5cf6; font-size: 0.9rem;">Exclusive Booking Access</div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);">You have been granted special access to book this tour.</div>
                            </div>
                        </div>
                        
                        <!-- Chauffeur Selection (For Fleet) -->
                        <div x-show="bookingType === 'fleet'" style="margin-bottom: 25px;">
                            <h4 style="margin: 0 0 15px; color: var(--primary); font-family: 'Outfit', sans-serif; font-size: 1rem;">Driver Option</h4>
                            
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                                <div @click="isSelfDrive = false" 
                                     :class="!isSelfDrive ? 'selected-guest-card' : 'guest-card'" 
                                     style="padding: 15px; border-radius: 12px; border: 2px solid var(--border); text-align: center; transition: all 0.3s; cursor: pointer;">
                                    <i class="fas fa-user-tie" style="font-size: 1.2rem; margin-bottom: 8px; display: block;"></i>
                                    <span style="font-weight: 800; font-size: 0.8rem;">Chauffeur Included</span>
                                </div>
                                <div @click="isSelfDrive = true" 
                                     :class="isSelfDrive ? 'selected-guest-card' : 'guest-card'" 
                                     style="padding: 15px; border-radius: 12px; border: 2px solid var(--border); text-align: center; transition: all 0.3s; cursor: pointer;">
                                    <i class="fas fa-steering-wheel" style="font-size: 1.2rem; margin-bottom: 8px; display: block;"></i>
                                    <span style="font-weight: 800; font-size: 0.8rem;">Self Drive</span>
                                </div>
                            </div>

                            <!-- Chauffeur Details -->
                            <template x-if="!isSelfDrive && bookingItem && bookingItem.chauffeur">
                                <div style="background: var(--bg-main); padding: 20px; border-radius: 15px; border: 1px dashed var(--accent); display: flex; gap: 15px; align-items: center;">
                                    <div style="width: 50px; height: 50px; background: var(--accent); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0;">
                                        <i class="fas fa-user-check"></i>
                                    </div>
                                    <div>
                                        <div style="font-size: 0.65rem; font-weight: 800; color: var(--accent); text-transform: uppercase; letter-spacing: 1px;">Your Assigned Driver</div>
                                        <div style="font-weight: 800; color: var(--primary); font-size: 1.1rem;" x-text="bookingItem.chauffeur.user.name"></div>
                                        <div style="font-size: 0.8rem; color: var(--text-muted);">
                                            <span x-text="bookingItem.chauffeur.years_of_experience"></span>+ Years Experience • <span x-text="bookingItem.chauffeur.status"></span>
                                        </div>
                                    </div>
                                </div>
                            </template>
                            <template x-if="!isSelfDrive && bookingItem && !bookingItem.chauffeur">
                                <div style="padding: 15px; background: #fff7ed; border: 1px solid #fed7aa; border-radius: 12px; color: #9a3412; font-size: 0.8rem; display: flex; align-items: center; gap: 10px;">
                                    <i class="fas fa-info-circle"></i>
                                    <span>No default chauffeur assigned. We will assign one for your trip.</span>
                                </div>
                            </template>
                        </div>
                        
                        <!-- Flight Details (For Airport Transfers) -->
                        <template x-if="bookingType === 'transfer'">
                            <div style="margin-bottom: 25px; background: rgba(37, 99, 235, 0.05); padding: 20px; border-radius: 15px; border: 1px dashed rgba(37, 99, 235, 0.2);">
                                <h4 style="margin: 0 0 15px; color: var(--primary); font-family: 'Outfit', sans-serif; font-size: 1rem;"><i class="fas fa-route" style="margin-right: 8px;"></i>Transfer Logistics</h4>
                                
                                <!-- Transfer Type Selection -->
                                <div style="margin-bottom: 20px;">
                                    <label style="display: block; font-size: 0.85rem; font-weight: 700; color: var(--text-muted); margin-bottom: 10px;">Service Arrangement</label>
                                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 8px;">
                                        <button type="button" @click="transferTypeSelection = 'pickup'" 
                                                :class="transferTypeSelection === 'pickup' ? 'selected-guest-card' : 'guest-card'"
                                                style="padding: 10px; border-radius: 8px; font-size: 0.75rem; font-weight: 700; border: 2px solid var(--border); background: var(--bg-card); transition: all 0.2s;">
                                            One-Way (Pickup)
                                        </button>
                                        <button type="button" @click="transferTypeSelection = 'dropoff'" 
                                                :class="transferTypeSelection === 'dropoff' ? 'selected-guest-card' : 'guest-card'"
                                                style="padding: 10px; border-radius: 8px; font-size: 0.75rem; font-weight: 700; border: 2px solid var(--border); background: var(--bg-card); transition: all 0.2s;">
                                            One-Way (Drop-off)
                                        </button>
                                        <button type="button" @click="transferTypeSelection = 'both'" 
                                                :class="transferTypeSelection === 'both' ? 'selected-guest-card' : 'guest-card'"
                                                style="padding: 10px; border-radius: 8px; font-size: 0.75rem; font-weight: 700; border: 2px solid var(--border); background: var(--bg-card); transition: all 0.2s;">
                                            Round Trip (Both)
                                        </button>
                                    </div>
                                    <input type="hidden" name="options[transfer_type]" :value="transferTypeSelection">
                                </div>

                                <!-- Dynamic Location Input -->
                                <div style="margin-bottom: 15px;">
                                    <label style="display: block; font-size: 0.85rem; font-weight: 700; color: var(--text-muted); margin-bottom: 8px;">Specific Location / Destination</label>
                                    <input type="text" name="options[custom_location]" x-model="customLocation" :required="bookingType === 'transfer'" placeholder="e.g. Labadi Beach Hotel"
                                           style="width: 100%; padding: 12px; border-radius: 10px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);">
                                </div>

                                <!-- Pricing Zone Selection -->
                                <div style="margin-bottom: 20px;">
                                    <label style="display: block; font-size: 0.85rem; font-weight: 700; color: var(--text-muted); margin-bottom: 8px;">Destination Zone (For Auto-Pricing)</label>
                                    <select name="options[zone_id]" x-model="selectedZoneId" :required="bookingType === 'transfer'"
                                            style="width: 100%; padding: 12px; border-radius: 10px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);">
                                        <option value="">Select General Area</option>
                                        @foreach($transferZones as $zone)
                                            <option value="{{ $zone->id }}">{{ $zone->name }} (+₵{{ number_format($zone->additional_price, 2) }})</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                                    <div>
                                        <label style="display: block; font-size: 0.85rem; font-weight: 700; color: var(--text-muted); margin-bottom: 8px;">Airline</label>
                                        <input type="text" name="options[airline]" x-model="airline" :required="bookingType === 'transfer'" placeholder="e.g. Emirates"
                                               style="width: 100%; padding: 12px; border-radius: 10px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);">
                                    </div>
                                    <div>
                                        <label style="display: block; font-size: 0.85rem; font-weight: 700; color: var(--text-muted); margin-bottom: 8px;">Flight Number</label>
                                        <input type="text" name="options[flight_number]" x-model="flightNumber" :required="bookingType === 'transfer'" placeholder="e.g. EK787"
                                               style="width: 100%; padding: 12px; border-radius: 10px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);">
                                    </div>
                                    <div>
                                        <label style="display: block; font-size: 0.85rem; font-weight: 700; color: var(--text-muted); margin-bottom: 8px;">Airport Terminal</label>
                                        <input type="text" name="options[terminal]" x-model="terminal" placeholder="e.g. Terminal 3"
                                               style="width: 100%; padding: 12px; border-radius: 10px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);">
                                    </div>
                                    <div>
                                        <label style="display: block; font-size: 0.85rem; font-weight: 700; color: var(--text-muted); margin-bottom: 8px;" x-text="transferTypeSelection === 'dropoff' ? 'Pickup Time' : 'Arrival Time'"></label>
                                        <input type="datetime-local" name="options[flight_time]" x-model="flightTime" :required="bookingType === 'transfer'"
                                               style="width: 100%; padding: 12px; border-radius: 10px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);">
                                    </div>
                                    <div style="grid-column: 1 / -1;">
                                        <label style="display: block; font-size: 0.85rem; font-weight: 700; color: var(--text-muted); margin-bottom: 8px;" x-text="transferTypeSelection === 'dropoff' ? 'Pickup Location' : 'Destination Address'"></label>
                                        <input type="text" name="options[destination]" x-model="destinationAddress" :required="bookingType === 'transfer'" placeholder="e.g. Labadi Beach Hotel or Home Address"
                                               style="width: 100%; padding: 12px; border-radius: 10px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);">
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- Customer Info -->
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 25px;">
                            <div style="grid-column: 1 / -1;">
                                <label style="display: block; font-size: 0.85rem; font-weight: 700; color: var(--text-muted); margin-bottom: 8px;">Full Name</label>
                                <input type="text" name="customer_name" x-model="customerName" required 
                                       style="width: 100%; padding: 12px; border-radius: 10px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);">
                            </div>
                            <div>
                                <label style="display: block; font-size: 0.85rem; font-weight: 700; color: var(--text-muted); margin-bottom: 8px;">Email Address</label>
                                <input type="email" name="customer_email" x-model="customerEmail" required 
                                       @input.debounce.500ms="checkDuplicate()"
                                       style="width: 100%; padding: 12px; border-radius: 10px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);">
                            </div>
                            <div>
                                <label style="display: block; font-size: 0.85rem; font-weight: 700; color: var(--text-muted); margin-bottom: 8px;">Phone Number</label>
                                <input type="text" name="customer_phone" x-model="customerPhone" required 
                                       @input.debounce.500ms="checkDuplicate()"
                                       style="width: 100%; padding: 12px; border-radius: 10px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main);">
                            </div>
                            
                            <!-- Duplicate Error Message -->
                            <div x-show="duplicateError" x-transition style="grid-column: 1 / -1; margin-top: 10px; padding: 12px; background: #fff1f2; border: 1px solid #fda4af; border-radius: 10px; color: #9f1239; font-size: 0.8rem; font-weight: 600; display: flex; align-items: center; gap: 10px;">
                                <i class="fas fa-exclamation-circle"></i>
                                <span x-text="duplicateError"></span>
                            </div>
                            
                            <template x-if="bookingType === 'tourism'">
                                <div style="display: contents;">
                                    <div style="grid-column: 1 / -1;">
                                    <label style="display: block; font-size: 0.85rem; font-weight: 700; color: var(--text-muted); margin-bottom: 8px;">Your Country</label>
                                    <select name="country" x-model="customerCountry" required
                                            style="width: 100%; padding: 12px; border-radius: 10px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main); cursor: pointer;">
                                        <option value="Ghana">Ghana</option>
                                        <option value="Nigeria">Nigeria</option>
                                        <option value="United States">United States</option>
                                        <option value="United Kingdom">United Kingdom</option>
                                        <option value="Canada">Canada</option>
                                        <option value="Germany">Germany</option>
                                        <option value="France">France</option>
                                        <option value="China">China</option>
                                        <option value="South Africa">South Africa</option>
                                        <option value="Ivory Coast">Ivory Coast</option>
                                        <option value="Togo">Togo</option>
                                        <option value="Benin">Benin</option>
                                        <option value="Other">Other</option>
                                        <optgroup label="All Countries">
                                            <option value="Afghanistan">Afghanistan</option>
                                            <option value="Albania">Albania</option>
                                            <option value="Algeria">Algeria</option>
                                            <option value="Andorra">Andorra</option>
                                            <option value="Angola">Angola</option>
                                            <option value="Antigua and Barbuda">Antigua and Barbuda</option>
                                            <option value="Argentina">Argentina</option>
                                            <option value="Armenia">Armenia</option>
                                            <option value="Australia">Australia</option>
                                            <option value="Austria">Austria</option>
                                            <option value="Azerbaijan">Azerbaijan</option>
                                            <option value="Bahamas">Bahamas</option>
                                            <option value="Bahrain">Bahrain</option>
                                            <option value="Bangladesh">Bangladesh</option>
                                            <option value="Barbados">Barbados</option>
                                            <option value="Belarus">Belarus</option>
                                            <option value="Belgium">Belgium</option>
                                            <option value="Belize">Belize</option>
                                            <option value="Benin">Benin</option>
                                            <option value="Bhutan">Bhutan</option>
                                            <option value="Bolivia">Bolivia</option>
                                            <option value="Bosnia and Herzegovina">Bosnia and Herzegovina</option>
                                            <option value="Botswana">Botswana</option>
                                            <option value="Brazil">Brazil</option>
                                            <option value="Brunei">Brunei</option>
                                            <option value="Bulgaria">Bulgaria</option>
                                            <option value="Burkina Faso">Burkina Faso</option>
                                            <option value="Burundi">Burundi</option>
                                            <option value="Cabo Verde">Cabo Verde</option>
                                            <option value="Cambodia">Cambodia</option>
                                            <option value="Cameroon">Cameroon</option>
                                            <option value="Canada">Canada</option>
                                            <option value="Central African Republic">Central African Republic</option>
                                            <option value="Chad">Chad</option>
                                            <option value="Chile">Chile</option>
                                            <option value="China">China</option>
                                            <option value="Colombia">Colombia</option>
                                            <option value="Comoros">Comoros</option>
                                            <option value="Congo">Congo</option>
                                            <option value="Costa Rica">Costa Rica</option>
                                            <option value="Croatia">Croatia</option>
                                            <option value="Cuba">Cuba</option>
                                            <option value="Cyprus">Cyprus</option>
                                            <option value="Czechia">Czechia</option>
                                            <option value="Denmark">Denmark</option>
                                            <option value="Djibouti">Djibouti</option>
                                            <option value="Dominica">Dominica</option>
                                            <option value="Dominican Republic">Dominican Republic</option>
                                            <option value="Ecuador">Ecuador</option>
                                            <option value="Egypt">Egypt</option>
                                            <option value="El Salvador">El Salvador</option>
                                            <option value="Equatorial Guinea">Equatorial Guinea</option>
                                            <option value="Eritrea">Eritrea</option>
                                            <option value="Estonia">Estonia</option>
                                            <option value="Eswatini">Eswatini</option>
                                            <option value="Ethiopia">Ethiopia</option>
                                            <option value="Fiji">Fiji</option>
                                            <option value="Finland">Finland</option>
                                            <option value="France">France</option>
                                            <option value="Gabon">Gabon</option>
                                            <option value="Gambia">Gambia</option>
                                            <option value="Georgia">Georgia</option>
                                            <option value="Germany">Germany</option>
                                            <option value="Ghana">Ghana</option>
                                            <option value="Greece">Greece</option>
                                            <option value="Grenada">Grenada</option>
                                            <option value="Guatemala">Guatemala</option>
                                            <option value="Guinea">Guinea</option>
                                            <option value="Guinea-Bissau">Guinea-Bissau</option>
                                            <option value="Guyana">Guyana</option>
                                            <option value="Haiti">Haiti</option>
                                            <option value="Honduras">Honduras</option>
                                            <option value="Hungary">Hungary</option>
                                            <option value="Iceland">Iceland</option>
                                            <option value="India">India</option>
                                            <option value="Indonesia">Indonesia</option>
                                            <option value="Iran">Iran</option>
                                            <option value="Iraq">Iraq</option>
                                            <option value="Ireland">Ireland</option>
                                            <option value="Israel">Israel</option>
                                            <option value="Italy">Italy</option>
                                            <option value="Jamaica">Jamaica</option>
                                            <option value="Japan">Japan</option>
                                            <option value="Jordan">Jordan</option>
                                            <option value="Kazakhstan">Kazakhstan</option>
                                            <option value="Kenya">Kenya</option>
                                            <option value="Kiribati">Kiribati</option>
                                            <option value="Kuwait">Kuwait</option>
                                            <option value="Kyrgyzstan">Kyrgyzstan</option>
                                            <option value="Laos">Laos</option>
                                            <option value="Latvia">Latvia</option>
                                            <option value="Lebanon">Lebanon</option>
                                            <option value="Lesotho">Lesotho</option>
                                            <option value="Liberia">Liberia</option>
                                            <option value="Libya">Libya</option>
                                            <option value="Liechtenstein">Liechtenstein</option>
                                            <option value="Lithuania">Lithuania</option>
                                            <option value="Luxembourg">Luxembourg</option>
                                            <option value="Madagascar">Madagascar</option>
                                            <option value="Malawi">Malawi</option>
                                            <option value="Malaysia">Malaysia</option>
                                            <option value="Maldives">Maldives</option>
                                            <option value="Mali">Mali</option>
                                            <option value="Malta">Malta</option>
                                            <option value="Marshall Islands">Marshall Islands</option>
                                            <option value="Mauritania">Mauritania</option>
                                            <option value="Mauritius">Mauritius</option>
                                            <option value="Mexico">Mexico</option>
                                            <option value="Micronesia">Micronesia</option>
                                            <option value="Moldova">Moldova</option>
                                            <option value="Monaco">Monaco</option>
                                            <option value="Mongolia">Mongolia</option>
                                            <option value="Montenegro">Montenegro</option>
                                            <option value="Morocco">Morocco</option>
                                            <option value="Mozambique">Mozambique</option>
                                            <option value="Myanmar">Myanmar</option>
                                            <option value="Namibia">Namibia</option>
                                            <option value="Nauru">Nauru</option>
                                            <option value="Nepal">Nepal</option>
                                            <option value="Netherlands">Netherlands</option>
                                            <option value="New Zealand">New Zealand</option>
                                            <option value="Nicaragua">Nicaragua</option>
                                            <option value="Niger">Niger</option>
                                            <option value="Nigeria">Nigeria</option>
                                            <option value="North Korea">North Korea</option>
                                            <option value="North Macedonia">North Macedonia</option>
                                            <option value="Norway">Norway</option>
                                            <option value="Oman">Oman</option>
                                            <option value="Pakistan">Pakistan</option>
                                            <option value="Palau">Palau</option>
                                            <option value="Palestine State">Palestine State</option>
                                            <option value="Panama">Panama</option>
                                            <option value="Papua New Guinea">Papua New Guinea</option>
                                            <option value="Paraguay">Paraguay</option>
                                            <option value="Peru">Peru</option>
                                            <option value="Philippines">Philippines</option>
                                            <option value="Poland">Poland</option>
                                            <option value="Portugal">Portugal</option>
                                            <option value="Qatar">Qatar</option>
                                            <option value="Romania">Romania</option>
                                            <option value="Russia">Russia</option>
                                            <option value="Rwanda">Rwanda</option>
                                            <option value="Saint Kitts and Nevis">Saint Kitts and Nevis</option>
                                            <option value="Saint Lucia">Saint Lucia</option>
                                            <option value="Saint Vincent and the Grenadines">Saint Vincent and the Grenadines</option>
                                            <option value="Samoa">Samoa</option>
                                            <option value="San Marino">San Marino</option>
                                            <option value="Sao Tome and Principe">Sao Tome and Principe</option>
                                            <option value="Saudi Arabia">Saudi Arabia</option>
                                            <option value="Senegal">Senegal</option>
                                            <option value="Serbia">Serbia</option>
                                            <option value="Seychelles">Seychelles</option>
                                            <option value="Sierra Leone">Sierra Leone</option>
                                            <option value="Singapore">Singapore</option>
                                            <option value="Slovakia">Slovakia</option>
                                            <option value="Slovenia">Slovenia</option>
                                            <option value="Solomon Islands">Solomon Islands</option>
                                            <option value="Somalia">Somalia</option>
                                            <option value="South Africa">South Africa</option>
                                            <option value="South Korea">South Korea</option>
                                            <option value="South Sudan">South Sudan</option>
                                            <option value="Spain">Spain</option>
                                            <option value="Sri Lanka">Sri Lanka</option>
                                            <option value="Sudan">Sudan</option>
                                            <option value="Suriname">Suriname</option>
                                            <option value="Sweden">Sweden</option>
                                            <option value="Switzerland">Switzerland</option>
                                            <option value="Syria">Syria</option>
                                            <option value="Tajikistan">Tajikistan</option>
                                            <option value="Tanzania">Tanzania</option>
                                            <option value="Thailand">Thailand</option>
                                            <option value="Timor-Leste">Timor-Leste</option>
                                            <option value="Togo">Togo</option>
                                            <option value="Tonga">Tonga</option>
                                            <option value="Trinidad and Tobago">Trinidad and Tobago</option>
                                            <option value="Tunisia">Tunisia</option>
                                            <option value="Turkey">Turkey</option>
                                            <option value="Turkmenistan">Turkmenistan</option>
                                            <option value="Tuvalu">Tuvalu</option>
                                            <option value="Uganda">Uganda</option>
                                            <option value="Ukraine">Ukraine</option>
                                            <option value="United Arab Emirates">United Arab Emirates</option>
                                            <option value="United Kingdom">United Kingdom</option>
                                            <option value="United States of America">United States of America</option>
                                            <option value="Uruguay">Uruguay</option>
                                            <option value="Uzbekistan">Uzbekistan</option>
                                            <option value="Vanuatu">Vanuatu</option>
                                            <option value="Venezuela">Venezuela</option>
                                            <option value="Vietnam">Vietnam</option>
                                            <option value="Yemen">Yemen</option>
                                            <option value="Zambia">Zambia</option>
                                            <option value="Zimbabwe">Zimbabwe</option>
                                        </optgroup>
                                    </select>
                                </div>
                                <div x-show="bookingItem?.package_type === 'fixed'" style="grid-column: 1 / -1; margin-top: 15px;">
                                    <label style="display: block; font-size: 0.85rem; font-weight: 700; color: var(--text-muted); margin-bottom: 8px;">Select Tour Schedule Date & Time <span style="color: var(--danger);">*</span></label>
                                    <input type="datetime-local" name="scheduled_at" :required="bookingItem?.package_type === 'fixed'"
                                           :min="minScheduleDate"
                                           style="width: 100%; padding: 12px; border-radius: 10px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main); font-weight: 600;">
                                    <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 5px;">Tours must be scheduled at least <span x-text="tourLeadDays"></span> days after the date of booking.</p>
                                </div>
                                </div>
                            </template>
                        </div>

                        <!-- Booking Category Selection (Compulsory for All except Transfers) -->
                        <div x-show="bookingType !== 'transfer'" style="margin-bottom: 25px;">
                            <h4 style="margin: 0 0 15px; color: var(--primary); font-family: 'Outfit', sans-serif; font-size: 1rem;">Customer Category <span style="color: var(--danger);">*</span></h4>
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(100px, 1fr)); gap: 10px;">
                                @if(isset($guestTypes) && count($guestTypes) > 0)
                                    @foreach($guestTypes as $type)
                                        <label style="cursor: pointer; position: relative; display: block;">
                                            <input type="radio" name="guest_type" value="{{ $type->name }}" x-model="guestType" 
                                                   @change="if(bookingType === 'tourism' && guestType === 'Individual') hours = 1"
                                                   :required="bookingType !== 'transfer'" style="position: absolute; opacity: 0; width: 0; height: 0;">
                                            <div :class="guestType === '{{ $type->name }}' ? 'selected-guest-card' : 'guest-card'" 
                                                 style="padding: 12px 8px; border-radius: 12px; text-align: center; transition: all 0.3s; position: relative; border: 2px solid var(--border); background: var(--bg-card); height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                                                
                                                <div :style="guestType === '{{ $type->name }}' ? 'background: rgba(245, 158, 11, 0.1); color: var(--accent);' : 'background: var(--bg-main); color: var(--text-muted);'"
                                                     style="width: 35px; height: 35px; border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-bottom: 8px; transition: all 0.3s;">
                                                    <i class="fas {{ str_contains(strtolower($type->name), 'family') ? 'fa-users' : (str_contains(strtolower($type->name), 'individual') ? 'fa-user' : 'fa-city') }}" style="font-size: 1.1rem;"></i>
                                                </div>

                                                <span style="display: block; font-weight: 800; font-size: 0.75rem; font-family: 'Outfit', sans-serif; transition: all 0.3s;"
                                                      :style="guestType === '{{ $type->name }}' ? 'color: var(--primary);' : 'color: var(--text-muted);'">
                                                    {{ $type->name }}
                                                </span>
                                            </div>
                                        </label>
                                    @endforeach
                                    <style>
                                        .guest-card:hover {
                                            border-color: var(--accent) !important;
                                            transform: translateY(-2px);
                                        }
                                        .guest-card {
                                            cursor: pointer;
                                        }
                                        .selected-guest-card {
                                            border-color: var(--accent) !important;
                                            background: rgba(245, 158, 11, 0.05) !important;
                                            transform: translateY(-2px);
                                            cursor: pointer;
                                        }
                                    </style>
                                @else
                                    <div style="grid-column: 1 / -1; padding: 15px; background: #fff1f2; border: 1px solid #fda4af; border-radius: 10px; color: #9f1239; font-size: 0.85rem; font-weight: 600;">
                                        <i class="fas fa-exclamation-triangle" style="margin-right: 8px;"></i>
                                        No categories configured.
                                    </div>
                                @endif
                            </div>
                            <div x-show="['Corporate Group', 'School', 'Others'].includes(guestType)" 
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 -translate-y-2"
                                 x-transition:enter-end="opacity-100 translate-y-0"
                                 style="margin-top: 20px;">
                                <label style="display: block; font-size: 0.85rem; font-weight: 700; color: var(--text-muted); margin-bottom: 8px;" 
                                       x-text="guestType === 'School' ? 'School Name' : (guestType === 'Corporate Group' ? 'Organization Name' : 'Group Name')">
                                </label>
                                <input type="text" name="group_name" x-model="groupName" 
                                       :required="['Corporate Group', 'School', 'Others'].includes(guestType)"
                                       :placeholder="'Enter ' + (guestType === 'School' ? 'School Name' : (guestType === 'Corporate Group' ? 'Organization Name' : 'Group Name'))"
                                       style="width: 100%; padding: 12px 15px; border-radius: 12px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main); font-weight: 600;">
                            </div>

                            <div x-show="!guestType" 
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 -translate-y-2"
                                 x-transition:enter-end="opacity-100 translate-y-0"
                                 style="margin-top: 15px; padding: 12px 15px; background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.2); border-radius: 12px; display: flex; align-items: center; gap: 10px; color: var(--accent); font-size: 0.8rem; font-weight: 600;">
                                <i class="fas fa-info-circle"></i>
                                <span>Select your booking category to proceed.</span>
                            </div>
                        </div>

                        <!-- Rental Start Date Selection (For Fleet) -->
                        <div x-show="bookingType === 'fleet'" style="margin-bottom: 25px;">
                            <label style="display: block; font-size: 0.85rem; font-weight: 700; color: var(--text-muted); margin-bottom: 8px;">Select Rental Start Date & Time <span style="color: var(--danger);">*</span></label>
                            <input type="datetime-local" name="scheduled_at" :required="bookingType === 'fleet'"
                                   :min="minFleetScheduleDate"
                                   style="width: 100%; padding: 12px; border-radius: 10px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main); font-weight: 600;">
                            <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 5px;">Rental must be scheduled at least <span x-text="fleetLeadDays"></span> days after the date of booking.</p>
                        </div>

                        <!-- Duration Type Selection (For Fleet) -->
                        <div x-show="bookingType === 'fleet'" style="margin-bottom: 25px;">
                            <label style="display: block; font-size: 0.85rem; font-weight: 700; color: var(--text-muted); margin-bottom: 12px;">Select Rental Duration Type</label>
                            <div style="display: flex; gap: 10px;">
                                <template x-for="unit in ['Hour', 'Day', 'Week']">
                                    <button type="button" @click="rentalUnit = unit; hours = 1"
                                            :class="rentalUnit === unit ? 'btn-primary' : 'btn-secondary'"
                                            style="flex: 1; padding: 10px; border-radius: 10px; font-size: 0.85rem; font-weight: 700; transition: all 0.2s;"
                                            x-text="unit + (unit !== 'Hour' ? 's' : '')">
                                    </button>
                                </template>
                            </div>
                        </div>

                        <!-- Quantity Selector -->
                        <div style="margin-bottom: 25px;">
                            <label style="display: block; font-size: 0.85rem; font-weight: 700; color: var(--text-muted); margin-bottom: 12px;" x-text="bookingType === 'tourism' ? 'Number of Guests' : (bookingType === 'transfer' ? 'Number of Vehicles' : 'Enter Quantity (' + rentalUnit + 's)')"></label>
                            <div style="display: flex; align-items: center; gap: 20px;">
                                <div style="display: flex; align-items: center; background: var(--bg-main); padding: 5px; border-radius: 15px; border: 1px solid var(--border);">
                                    <button type="button" @click="updateGuests(hours - 1)" style="width: 35px; height: 35px; border-radius: 10px; border: none; background: var(--bg-card); color: var(--text-main); cursor: pointer; transition: all 0.2s; display: flex; align-items: center; justify-content: center;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='var(--text-main)'">
                                        <i class="fas fa-minus" style="font-size: 0.8rem;"></i>
                                    </button>
                                    <span style="width: 50px; text-align: center; font-weight: 800; font-size: 1.1rem; color: var(--text-main); display: inline-block;" x-text="hours"></span>
                                    <input type="hidden" name="quantity" :value="hours">
                                    <button type="button" @click="updateGuests(hours + 1)" 
                                            :disabled="(bookingType === 'tourism' && bookingItem?.package_type === 'scheduled' && hours >= bookingItem.available_spaces && !(interestToken && bookingItem && interestPackageId && String(bookingItem.id) == String(interestPackageId))) || (bookingType === 'tourism' && guestType === 'Individual' && hours >= 1)"
                                            :style="((bookingType === 'tourism' && bookingItem?.package_type === 'scheduled' && hours >= bookingItem.available_spaces && !(interestToken && bookingItem && interestPackageId && String(bookingItem.id) == String(interestPackageId))) || (bookingType === 'tourism' && guestType === 'Individual' && hours >= 1)) ? 'cursor: not-allowed; opacity: 0.5;' : 'cursor: pointer;'"
                                            style="width: 35px; height: 35px; border-radius: 10px; border: none; background: var(--bg-card); color: var(--text-main); transition: all 0.2s; display: flex; align-items: center; justify-content: center;" onmouseover="if(this.style.cursor !== 'not-allowed') this.style.color='var(--primary)'" onmouseout="this.style.color='var(--text-main)'">
                                        <i class="fas fa-plus" style="font-size: 0.8rem;"></i>
                                    </button>
                                </div>
                                <div style="font-size: 1rem; color: var(--text-muted);">
                                    <template x-if="bookingType === 'tourism'">
                                        <span>x ₵<span x-text="new Number(bookingItem.price).toLocaleString()"></span> / person</span>
                                    </template>
                                    <template x-if="bookingType === 'transfer'">
                                        <span>x ₵<span x-text="new Number(bookingItem.price).toLocaleString()"></span> / vehicle</span>
                                    </template>
                                    <template x-if="bookingType === 'fleet'">
                                        <span>
                                            x ₵<span x-text="new Number(rentalUnit === 'Hour' ? bookingItem.vehicle_type?.base_hourly_rate : (rentalUnit === 'Day' ? bookingItem.vehicle_type?.base_daily_rate : bookingItem.vehicle_type?.base_daily_rate * 7)).toLocaleString()"></span> 
                                            / <span x-text="rentalUnit.toLowerCase()"></span>
                                        </span>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div style="margin-bottom: 30px;">
                            <label style="display: block; font-size: 0.85rem; font-weight: 700; color: var(--text-muted); margin-bottom: 8px;">Special Requests or Notes</label>
                            <textarea name="notes" x-model="notes" placeholder="e.g. Pickup time, preferences..." style="width: 100%; padding: 15px; border-radius: 12px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main); min-height: 100px;"></textarea>
                        </div>

                        <!-- Price Summary -->
                        <div style="background: var(--bg-main); padding: 25px; border-radius: 20px; border: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
                            <div>
                                <span style="display: block; font-size: 0.75rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase;">Total Estimated Amount</span>
                                <span style="font-size: 2.2rem; font-weight: 900; color: var(--primary);">₵<span x-text="new Number(totalPrice).toLocaleString(undefined, {minimumFractionDigits: 2})"></span></span>
                            </div>
                            <div style="width: 60px; height: 60px; background: rgba(37, 99, 235, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--primary); font-size: 1.5rem;">
                                <i class="fas fa-cedi-sign"></i>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary" :disabled="duplicateError !== '' || isSubmitting"
                                :style="(duplicateError !== '' || isSubmitting) ? 'opacity: 0.5; cursor: not-allowed;' : 'cursor: pointer;'"
                                style="width: 100%; padding: 20px; border-radius: 15px; font-size: 1.1rem; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; box-shadow: 0 10px 20px rgba(37, 99, 235, 0.2);">
                            <span x-text="isSubmitting ? 'Submitting booking...' : 'Confirm & Submit Booking'"></span>
                        </button>
                    </form>
                        </div>
                    </template>
                </div>
            </template>
        </div>
    </div>
</div>

<style>
    .lightbox-modal {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.8);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10000;
        padding: 20px;
    }

    .lightbox-content {
        max-width: 90vw;
        max-height: 90vh;
        position: relative;
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: default;
    }

    .lightbox-modal[x-show="true"] .lightbox-content {
        transform: scale(1);
    }

    .lightbox-close {
        position: absolute;
        top: 30px;
        right: 30px;
        color: white;
        font-size: 2.5rem;
        background: none;
        border: none;
        cursor: pointer;
        z-index: 10000;
        transition: transform 0.2s;
    }

    .lightbox-close:hover {
        transform: scale(1.1);
        opacity: 0.8;
    }
</style>

<!-- Interest Success Modal -->
@if(session('interest_success'))
<div x-data="{ show: true }" 
     x-show="show" 
     x-init="setTimeout(() => { if(typeof bookingOpen !== 'undefined') bookingOpen = false; }, 100)"
     class="lightbox-modal" 
     style="background: rgba(15, 23, 42, 0.95); backdrop-filter: blur(10px); z-index: 10001;"
     x-cloak>
    <div class="lightbox-content" 
         @click.outside="show = false"
         style="background: white; width: 100%; max-width: 450px; padding: 40px; text-align: center; border-radius: 30px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);">
        
        <div style="width: 80px; height: 80px; background: #ecfdf5; color: #10b981; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 25px; font-size: 2.5rem;">
            <i class="fas fa-check-circle"></i>
        </div>
        
        <h2 class="font-heading" style="margin: 0 0 15px; color: #0f172a; font-size: 1.8rem;">Request Received!</h2>
        <p style="color: #64748b; font-size: 1rem; line-height: 1.6; margin-bottom: 30px;">
            {{ session('interest_success') }}
        </p>
        
        <button @click="show = false" 
                class="btn btn-primary" 
                style="width: 100%; padding: 15px; border-radius: 12px; font-weight: 800; font-size: 1rem; text-transform: uppercase; letter-spacing: 1px;">
            Great, Thanks!
        </button>
    </div>
</div>
@endif

<!-- Booking Success Modal -->
<div x-data="{ 
        open: false, 
        reference: '', 
        scheduled_at: '', 
        customer_name: '', 
        url: '' 
     }"
     x-show="open"
     x-on:show-booking-success.window="
        open = true;
        reference = $event.detail.reference;
        scheduled_at = $event.detail.scheduled_at;
        customer_name = $event.detail.customer_name;
        url = $event.detail.url;
     "
     class="lightbox-modal"
     style="background: rgba(15, 23, 42, 0.95); backdrop-filter: blur(10px); z-index: 10002;"
     x-cloak>
    <div class="lightbox-content animate-fade-up"
         x-on:click.outside="open = false"
         style="background: white; width: 100%; max-width: 500px; padding: 45px; text-align: center; border-radius: 30px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);">
         
        <div style="width: 80px; height: 80px; background: #ecfdf5; color: #10b981; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 25px; font-size: 2.5rem;">
            <i class="fas fa-check-circle"></i>
        </div>
        
        <h2 class="font-heading" style="margin: 0 0 15px; color: #0f172a; font-size: 1.8rem;">Booking Submitted!</h2>
        <p style="color: #64748b; font-size: 1rem; line-height: 1.6; margin-bottom: 25px;">
            Hi <strong style="color: #0f172a;" x-text="customer_name"></strong>, booking has been successfully submitted and is pending review by our operations team.
        </p>

        <div style="margin-bottom: 20px; background: #ecfdf5; color: #065f46; padding: 10px 25px; border-radius: 50px; font-size: 0.95rem; border: 1px solid #a7f3d0; display: inline-block; font-weight: 700;">
            Reference Number: <span x-text="reference"></span>
        </div>

        <template x-if="scheduled_at">
            <div style="display: block; margin: 5px auto 25px;">
                <div style="padding: 12px 25px; border-radius: 12px; background: rgba(245, 158, 11, 0.08); border: 1px solid rgba(245, 158, 11, 0.2); display: inline-flex; align-items: center; gap: 10px; font-weight: 700; color: #b45309; font-size: 0.9rem; font-family: 'Inter', sans-serif;">
                    <i class="fas fa-calendar-alt" style="font-size: 1rem;"></i>
                    <span>Scheduled Date & Time: <strong x-text="scheduled_at"></strong></span>
                </div>
            </div>
        </template>

        <div style="display: flex; gap: 15px; margin-top: 30px;">
            <a :href="url" 
               class="btn btn-primary"
               style="flex: 1; padding: 15px; border-radius: 12px; font-weight: 800; font-size: 0.95rem; text-transform: uppercase; letter-spacing: 0.5px; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 4px 12px rgba(30, 58, 138, 0.15); background: var(--primary); color: white;">
                <i class="fas fa-ticket-alt"></i> View Booking
            </a>
            <button @click="open = false" 
                    class="btn btn-secondary" 
                    style="flex: 1; padding: 15px; border-radius: 12px; font-weight: 800; font-size: 0.95rem; text-transform: uppercase; letter-spacing: 0.5px; border: 1px solid #cbd5e1; background: #f1f5f9; color: #334155; cursor: pointer;">
                Close
            </button>
        </div>
    </div>
</div>

