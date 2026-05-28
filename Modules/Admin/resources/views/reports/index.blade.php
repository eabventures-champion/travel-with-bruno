@extends('admin::layouts.master')

@section('title', 'Reports & Analytics')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1>Reports & Analytics</h1>
        <p>Overview of business performance and operational metrics.</p>
    </div>
    <div class="page-actions">
        <button class="btn btn-secondary" onclick="window.print()"><i class="fas fa-download"></i> Export/Print</button>
        <button class="btn btn-primary"><i class="fas fa-calendar"></i> Last 30 Days</button>
    </div>
</div>

<div class="stats-grid">
    <!-- Tourism Section -->
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(30, 58, 138, 0.1); color: #1e3a8a;">
            <i class="fas fa-map-marked-alt"></i>
        </div>
        <div class="stat-info">
            <span class="stat-label">Tourism Bookings</span>
            <span class="stat-value">{{ number_format($stats['tourism_bookings']) }}</span>
            <span class="stat-trend positive"><i class="fas fa-arrow-up"></i> 12%</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">
            <i class="fas fa-wallet"></i>
        </div>
        <div class="stat-info">
            <span class="stat-label">Tourism Revenue</span>
            <span class="stat-value">{{ $stats['currency_symbol'] }}{{ number_format($stats['tourism_revenue'], 2) }}</span>
            <span class="stat-trend positive"><i class="fas fa-arrow-up"></i> 5.2%</span>
        </div>
    </div>

    <!-- Car Hire Section -->
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;">
            <i class="fas fa-car-side"></i>
        </div>
        <div class="stat-info">
            <span class="stat-label">Car Hire Bookings</span>
            <span class="stat-value">{{ number_format($stats['car_hire_bookings']) }}</span>
            <span class="stat-trend positive"><i class="fas fa-arrow-up"></i> 8%</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(139, 92, 246, 0.1); color: #8b5cf6;">
            <i class="fas fa-hand-holding-usd"></i>
        </div>
        <div class="stat-info">
            <span class="stat-label">Car Hire Revenue</span>
            <span class="stat-value">{{ $stats['currency_symbol'] }}{{ number_format($stats['car_hire_revenue'], 2) }}</span>
            <span class="stat-trend positive"><i class="fas fa-arrow-up"></i> 3.4%</span>
        </div>
    </div>
</div>


<div class="mt-30">
    <!-- Booking Trends Full Width -->
    <div class="dashboard-card">
        <div class="card-header">
            <h3 class="card-title">Booking Trends</h3>
            <div class="card-actions">
                <select class="form-control-sm">
                    <option>Weekly</option>
                    <option>Monthly</option>
                </select>
            </div>
        </div>
        <div class="chart-container" style="height: 350px; display: flex; align-items: flex-end; justify-content: space-around; padding: 20px 40px;">
            @foreach($bookingTrend as $trend)
                <div class="chart-bar-wrapper" style="display: flex; flex-direction: column; align-items: center; width: 8%;">
                    <div class="chart-bar" style="width: 45px; height: {{ max($trend->count * 30, 15) }}px; background: var(--primary); border-radius: 8px 8px 0 0; transition: height 0.5s ease;"></div>
                    <span style="font-size: 0.75rem; color: var(--text-muted); margin-top: 10px; font-weight: 600;">{{ date('D', strtotime($trend->date)) }}</span>
                    <span style="font-size: 0.7rem; color: var(--text-main); font-weight: 700; margin-top: 4px;">{{ $trend->count }}</span>
                </div>
            @endforeach
            @if(count($bookingTrend) == 0)
                <div style="color: #64748b; font-style: italic; width: 100%; text-align: center; padding-bottom: 50px;">No data available for the selected period.</div>
            @endif
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 20px;
        margin-top: 25px;
    }
    .stat-card {
        background: var(--bg-card);
        padding: 20px; /* Slightly reduced padding */
        border-radius: 20px;
        box-shadow: var(--shadow);
        display: flex;
        flex-direction: row;
        align-items: center;
        gap: 15px; /* Reduced gap */
        transition: transform 0.3s ease;
        min-height: 110px;
    }
    .stat-card:hover {
        transform: translateY(-5px);
    }
    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        flex-shrink: 0;
    }
    .stat-info {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 2px;
        flex: 1; /* Allow info to take remaining space */
        min-width: 0; /* Important for text-overflow in flexbox */
    }
    .stat-label {
        font-size: 0.85rem;
        color: var(--text-muted);
        font-weight: 600;
        margin-bottom: 0;
    }
    .stat-value {
        font-size: 1.25rem; /* Reduced from 1.5rem for better fit */
        font-weight: 800;
        color: var(--text-main);
        line-height: 1.1;
        word-break: break-word;
        max-width: 100%;
        overflow-wrap: break-word;
    }
    .stat-trend {
        font-size: 0.75rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 4px;
        margin-top: 4px;
    }
    .stat-trend.positive {
        color: #10b981;
    }
    .reports-grid {
        display: grid;
        grid-template-columns: 1.5fr 1fr;
        gap: 25px;
        align-items: start;
        margin-top: 40px; /* Explicit margin to prevent overlap */
    }
    .mt-30 { margin-top: 30px; }
    .dashboard-card {
        background: var(--bg-card);
        border-radius: 20px;
        box-shadow: var(--shadow);
        overflow: hidden;
        padding: 0; /* Override global padding to allow headers/tables to touch edges */
    }
    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px 25px;
        border-bottom: 1px solid var(--border);
    }
    .card-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--text-main);
    }
    .btn-link {
        font-size: 0.85rem;
        color: var(--primary);
        font-weight: 600;
        text-decoration: none;
        transition: color 0.2s;
    }
    .btn-link:hover {
        color: var(--primary-dark);
        text-decoration: underline;
    }
    .form-control-sm {
        padding: 5px 10px;
        border-radius: 8px;
        border: 1px solid var(--border);
        font-size: 0.8rem;
        color: var(--text-main);
        background: var(--bg-main);
        cursor: pointer;
    }
    .badge {
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    .badge-success { background: #dcfce7; color: #166534; }
    .badge-warning { background: #fef9c3; color: #854d0e; }
    .badge-danger { background: #fee2e2; color: #991b1b; }
    
    .admin-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }
    .admin-table thead th {
        padding: 15px 25px;
        background: rgba(30, 58, 138, 0.03);
        border-bottom: 1px solid var(--border);
    }
    .admin-table tbody td {
        padding: 15px 12px;
        border-bottom: 1px solid var(--border);
        transition: background 0.2s;
    }
    .admin-table tbody tr:hover td {
        background: rgba(37, 99, 235, 0.02);
    }
    .admin-table thead th:first-child,
    .admin-table tbody td:first-child {
        padding-left: 15px;
    }
    .admin-table thead th:last-child,
    .admin-table tbody td:last-child {
        padding-right: 15px;
    }
    .user-info .user-name {
        margin-bottom: 2px;
        font-size: 0.9rem;
    }
    .user-info div:last-child {
        font-size: 0.65rem !important;
        opacity: 0.8;
    }
    
    @media (max-width: 1024px) {
        .reports-grid {
            grid-template-columns: 1fr;
        }
    }
    .content-body { padding-bottom: 50px; }
</style>
@endpush
