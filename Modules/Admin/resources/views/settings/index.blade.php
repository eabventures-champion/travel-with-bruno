@extends('admin::layouts.master')

@section('title', 'System Settings')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>System Settings</h1>
        <p>Manage global configuration and site identity.</p>
    </div>
</div>

<div class="mt-20" x-data="{ tab: 'general' }">
    <div class="dashboard-card" style="padding: 0; overflow: hidden;">
        <!-- Tabs -->
        <div style="display: flex; background: var(--bg-main); border-bottom: 1px solid var(--border); overflow-x: auto; white-space: nowrap;">
            <button @click="tab = 'general'" :class="tab === 'general' ? 'tab-active' : ''" class="tab-btn">
                <i class="fas fa-cog"></i> General
            </button>
            <button @click="tab = 'branding'" :class="tab === 'branding' ? 'tab-active' : ''" class="tab-btn">
                <i class="fas fa-paint-brush"></i> Branding
            </button>
            <button @click="tab = 'contact'" :class="tab === 'contact' ? 'tab-active' : ''" class="tab-btn">
                <i class="fas fa-envelope"></i> Contact
            </button>
            <button @click="tab = 'social'" :class="tab === 'social' ? 'tab-active' : ''" class="tab-btn">
                <i class="fas fa-share-alt"></i> Social
            </button>
            <button @click="tab = 'localization'" :class="tab === 'localization' ? 'tab-active' : ''" class="tab-btn">
                <i class="fas fa-globe"></i> Localization
            </button>
            <button @click="tab = 'seo'" :class="tab === 'seo' ? 'tab-active' : ''" class="tab-btn">
                <i class="fas fa-search"></i> SEO
            </button>
            <button @click="tab = 'system'" :class="tab === 'system' ? 'tab-active' : ''" class="tab-btn">
                <i class="fas fa-server"></i> System
            </button>
        </div>

        <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" class="admin-form" style="padding: 30px;">
            @csrf
            
            <!-- General Tab -->
            <div x-show="tab === 'general'">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Site Name</label>
                        <input type="text" name="site_name" class="form-control" value="{{ $settings['site_name'] ?? 'Bruno Heights Ventures' }}">
                    </div>
                    <div class="form-group">
                        <label>Slogan</label>
                        <input type="text" name="site_slogan" class="form-control" value="{{ $settings['site_slogan'] ?? 'Premium Tourism & Logistics' }}">
                    </div>
                    <div class="form-group">
                        <label>Maintenance Mode</label>
                        <select name="maintenance_mode" class="form-control">
                            <option value="off" {{ ($settings['maintenance_mode'] ?? '') == 'off' ? 'selected' : '' }}>Off (Live)</option>
                            <option value="on" {{ ($settings['maintenance_mode'] ?? '') == 'on' ? 'selected' : '' }}>On (Under Maintenance)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Vehicle Rental Min Lead Days</label>
                        <input type="number" name="fleet_rental_lead_days" class="form-control" min="0" value="{{ $settings['fleet_rental_lead_days'] ?? '2' }}">
                    </div>
                    <div class="form-group">
                        <label>Fixed Tour Min Lead Days</label>
                        <input type="number" name="tourism_fixed_lead_days" class="form-control" min="0" value="{{ $settings['tourism_fixed_lead_days'] ?? '7' }}">
                    </div>
                </div>
            </div>

            <!-- Branding Tab -->
            <div x-show="tab === 'branding'">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Site Logo</label>
                        @if(isset($settings['site_logo']))
                            <div style="margin-bottom: 10px;">
                                <img src="{{ asset('storage/' . $settings['site_logo']) }}" alt="Logo" style="max-height: 50px; border: 1px solid var(--border); padding: 5px; border-radius: 5px;">
                            </div>
                        @endif
                        <input type="file" name="site_logo" class="form-control">
                        <small style="color: #64748b;">Recommended: PNG or SVG, transparent background.</small>
                    </div>
                    <div class="form-group">
                        <label>Favicon</label>
                        @if(isset($settings['site_favicon']))
                            <div style="margin-bottom: 10px;">
                                <img src="{{ asset('storage/' . $settings['site_favicon']) }}" alt="Favicon" style="max-height: 32px;">
                            </div>
                        @endif
                        <input type="file" name="site_favicon" class="form-control">
                        <small style="color: #64748b;">Recommended: ICO or PNG, 32x32px.</small>
                    </div>
                </div>
            </div>

            <!-- Contact Tab -->
            <div x-show="tab === 'contact'">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Official Email</label>
                        <input type="email" name="contact_email" class="form-control" value="{{ $settings['contact_email'] ?? 'info@brunoheights.com' }}">
                    </div>
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="text" name="contact_phone" class="form-control" value="{{ $settings['contact_phone'] ?? '+233 00 000 0000' }}">
                    </div>
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label>Office Address</label>
                        <textarea name="contact_address" class="form-control" rows="3">{{ $settings['contact_address'] ?? 'Accra, Ghana' }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Social Tab -->
            <div x-show="tab === 'social'">
                <div class="form-grid">
                    <div class="form-group">
                        <label><i class="fab fa-facebook"></i> Facebook URL</label>
                        <input type="url" name="social_facebook" class="form-control" value="{{ $settings['social_facebook'] ?? '' }}">
                    </div>
                    <div class="form-group">
                        <label><i class="fab fa-instagram"></i> Instagram URL</label>
                        <input type="url" name="social_instagram" class="form-control" value="{{ $settings['social_instagram'] ?? '' }}">
                    </div>
                    <div class="form-group">
                        <label><i class="fab fa-linkedin"></i> LinkedIn URL</label>
                        <input type="url" name="social_linkedin" class="form-control" value="{{ $settings['social_linkedin'] ?? '' }}">
                    </div>
                    <div class="form-group">
                        <label><i class="fab fa-twitter"></i> X (Twitter) URL</label>
                        <input type="url" name="social_twitter" class="form-control" value="{{ $settings['social_twitter'] ?? '' }}">
                    </div>
                </div>
            </div>

            <!-- Localization Tab -->
            <div x-show="tab === 'localization'">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Default Currency</label>
                        <select name="default_currency" class="form-control">
                            <option value="GHS" {{ ($settings['default_currency'] ?? 'GHS') == 'GHS' ? 'selected' : '' }}>GHS (₵)</option>
                            <option value="USD" {{ ($settings['default_currency'] ?? '') == 'USD' ? 'selected' : '' }}>USD ($)</option>
                            <option value="EUR" {{ ($settings['default_currency'] ?? '') == 'EUR' ? 'selected' : '' }}>EUR (€)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Timezone</label>
                        <select name="timezone" class="form-control">
                            <option value="UTC" {{ ($settings['timezone'] ?? '') == 'UTC' ? 'selected' : '' }}>UTC</option>
                            <option value="Africa/Accra" {{ ($settings['timezone'] ?? '') == 'Africa/Accra' ? 'selected' : '' }}>Africa/Accra</option>
                            <option value="Europe/London" {{ ($settings['timezone'] ?? '') == 'Europe/London' ? 'selected' : '' }}>Europe/London</option>
                            <option value="America/New_York" {{ ($settings['timezone'] ?? '') == 'America/New_York' ? 'selected' : '' }}>America/New_York</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Date Format</label>
                        <select name="date_format" class="form-control">
                            <option value="Y-m-d" {{ ($settings['date_format'] ?? '') == 'Y-m-d' ? 'selected' : '' }}>YYYY-MM-DD</option>
                            <option value="d/m/Y" {{ ($settings['date_format'] ?? '') == 'd/m/Y' ? 'selected' : '' }}>DD/MM/YYYY</option>
                            <option value="M d, Y" {{ ($settings['date_format'] ?? '') == 'M d, Y' ? 'selected' : '' }}>Month DD, YYYY</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- SEO Tab -->
            <div x-show="tab === 'seo'">
                <div class="form-grid">
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label>Meta Description</label>
                        <textarea name="meta_description" class="form-control" rows="3">{{ $settings['meta_description'] ?? 'Premium tourism and fleet services in Ghana.' }}</textarea>
                    </div>
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label>Meta Keywords</label>
                        <input type="text" name="meta_keywords" class="form-control" value="{{ $settings['meta_keywords'] ?? 'tourism, ghana, car rental, travel' }}">
                    </div>
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label>Google Analytics ID</label>
                        <input type="text" name="google_analytics_id" class="form-control" value="{{ $settings['google_analytics_id'] ?? '' }}" placeholder="UA-XXXXXXXXX-X or G-XXXXXXXXXX">
                    </div>
                </div>
            </div>

            <!-- System Tab -->
            <div x-show="tab === 'system'">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Mail Driver</label>
                        <select name="mail_driver" class="form-control">
                            <option value="smtp" {{ ($settings['mail_driver'] ?? '') == 'smtp' ? 'selected' : '' }}>SMTP</option>
                            <option value="mailgun" {{ ($settings['mail_driver'] ?? '') == 'mailgun' ? 'selected' : '' }}>Mailgun</option>
                            <option value="log" {{ ($settings['mail_driver'] ?? '') == 'log' ? 'selected' : '' }}>Log (Testing)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Mail Host</label>
                        <input type="text" name="mail_host" class="form-control" value="{{ $settings['mail_host'] ?? '' }}">
                    </div>
                    <div class="form-group">
                        <label>Mail Port</label>
                        <input type="text" name="mail_port" class="form-control" value="{{ $settings['mail_port'] ?? '587' }}">
                    </div>
                </div>
            </div>

            <div class="mt-30" style="padding-top: 20px; border-top: 1px solid var(--border); display: flex; justify-content: flex-end;">
                <button type="submit" class="btn btn-primary" style="padding: 12px 30px;">
                    <i class="fas fa-save"></i> Save All Settings
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<style>
    .tab-btn {
        padding: 15px 25px;
        border: none;
        background: none;
        cursor: pointer;
        font-weight: 600;
        color: var(--text-muted);
        font-size: 0.95rem;
        transition: all 0.3s;
        border-right: 1px solid var(--border);
    }
    .tab-btn i {
        margin-right: 8px;
    }
    .tab-btn:hover {
        background: var(--bg-main);
        color: var(--primary);
    }
    .tab-active {
        background: var(--bg-card) !important;
        color: var(--primary) !important;
        border-bottom: 2px solid var(--primary) !important;
    }
    .form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 25px;
    }
    .form-group label {
        display: block;
        margin-bottom: 10px;
        font-weight: 700;
        color: var(--text-main);
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .form-control {
        width: 100%;
        padding: 12px 16px;
        border-radius: 10px;
        border: 1px solid var(--border);
        background: var(--bg-main);
        color: var(--text-main);
        font-size: 1rem;
        transition: all 0.3s;
    }
    .form-control:focus {
        border-color: var(--primary);
        background: var(--bg-card);
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        outline: none;
    }
</style>
@endpush
