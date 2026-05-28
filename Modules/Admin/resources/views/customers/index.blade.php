@extends('admin::layouts.master')

@section('title', 'Customer Directory')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h1><i class="fas fa-address-book" style="color: var(--primary); margin-right: 10px;"></i>Customer Directory</h1>
        <p>Complete overview of all registered customers, their profiles, and booking history.</p>
    </div>
</div>

{{-- Summary Stat Cards --}}
<div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 30px;">
    <div class="card stat-card" style="position: relative; overflow: hidden;">
        <div style="position: absolute; top: -15px; right: -15px; width: 70px; height: 70px; background: rgba(37, 99, 235, 0.08); border-radius: 50%;"></div>
        <span class="stat-label">Total Customers</span>
        <span class="stat-value">{{ $stats['total_customers'] }}</span>
        <span class="stat-trend"><i class="fas fa-users" style="color: var(--primary);"></i> Registered</span>
    </div>
    <div class="card stat-card" style="position: relative; overflow: hidden;">
        <div style="position: absolute; top: -15px; right: -15px; width: 70px; height: 70px; background: rgba(16, 185, 129, 0.08); border-radius: 50%;"></div>
        <span class="stat-label">Active Bookings</span>
        <span class="stat-value">{{ $stats['active_bookings'] }}</span>
        <span class="stat-trend trend-up"><i class="fas fa-ticket-alt" style="color: #10b981;"></i> In Progress</span>
    </div>
    <div class="card stat-card" style="position: relative; overflow: hidden;">
        <div style="position: absolute; top: -15px; right: -15px; width: 70px; height: 70px; background: rgba(245, 158, 11, 0.08); border-radius: 50%;"></div>
        <span class="stat-label">Total Revenue</span>
        <span class="stat-value">₵{{ number_format($stats['total_revenue'], 2) }}</span>
        <span class="stat-trend"><i class="fas fa-coins" style="color: #f59e0b;"></i> Lifetime</span>
    </div>
    <div class="card stat-card" style="position: relative; overflow: hidden;">
        <div style="position: absolute; top: -15px; right: -15px; width: 70px; height: 70px; background: rgba(139, 92, 246, 0.08); border-radius: 50%;"></div>
        <span class="stat-label">New This Month</span>
        <span class="stat-value">{{ $stats['new_this_month'] }}</span>
        <span class="stat-trend trend-up"><i class="fas fa-user-plus" style="color: #8b5cf6;"></i> Joined</span>
    </div>
</div>

<div x-data="{
    search: '',
    statusFilter: '',
    matches(name, email, phone, status) {
        const s = this.search.toLowerCase();
        const f = this.statusFilter.toLowerCase();
        const matchesSearch = s === '' || name.toLowerCase().includes(s) || email.toLowerCase().includes(s) || (phone && phone.toLowerCase().includes(s));
        const matchesStatus = f === '' || status.toLowerCase() === f;
        return matchesSearch && matchesStatus;
    }
}">
    {{-- Filter Bar --}}
    <div class="dashboard-card" style="margin-bottom: 25px; padding: 12px 20px !important; border: 1px solid var(--border); background: var(--bg-card);">
        <div style="display: flex; gap: 15px; align-items: center;">
            <div style="position: relative; flex: 1;">
                <i class="fas fa-search" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 0.9rem; pointer-events: none;"></i>
                <input type="text" x-model="search" placeholder="Search by name, email, or phone..." style="width: 100%; height: 45px; padding: 0 15px 0 42px; border-radius: 12px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main); font-size: 0.95rem; transition: all 0.3s ease;">
            </div>
            <div style="position: relative; flex: 0 0 200px;">
                <i class="fas fa-filter" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 0.9rem; pointer-events: none;"></i>
                <select x-model="statusFilter" style="width: 100%; height: 45px; padding: 0 15px 0 42px; border-radius: 12px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main); font-size: 0.95rem; cursor: pointer; appearance: none; background-image: url(&quot;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2364748b'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E&quot;); background-repeat: no-repeat; background-position: right 15px center; background-size: 15px;">
                    <option value="">All Statuses</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="suspended">Suspended</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Customer Table --}}
    <div class="dashboard-card">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Customer</th>

                        <th>Total Bookings</th>
                        <th>Total Spend</th>
                        <th>Status</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $customer)
                        <tr x-show="matches('{{ addslashes($customer->name) }}', '{{ addslashes($customer->email) }}', '{{ addslashes($customer->phone ?? '') }}', '{{ $customer->status ?? 'active' }}')"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 transform scale-95"
                            x-transition:enter-end="opacity-100 transform scale-100">
                            <td>
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <div style="width: 42px; height: 42px; background: linear-gradient(135deg, var(--primary), #6366f1); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1rem; flex-shrink: 0;">
                                        {{ strtoupper(substr($customer->name, 0, 1)) }}
                                    </div>
                                    <div style="display: flex; flex-direction: column;">
                                        <span style="font-weight: 700; color: var(--text-main);">{{ $customer->name }}</span>
                                        <span style="font-size: 0.8rem; color: var(--text-muted);">{{ $customer->email }}</span>
                                        @if($customer->phone)
                                            <span style="font-size: 0.75rem; font-weight: 700; color: var(--primary); margin-top: 2px;">
                                                <i class="fas fa-phone-alt" style="font-size: 0.65rem; margin-right: 4px;"></i> {{ $customer->phone }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <td>
                                <span style="font-weight: 800; color: var(--primary); font-size: 1.1rem;">{{ $customer->bookings_count }}</span>
                            </td>
                            <td>
                                <span style="font-weight: 800; color: var(--primary);">₵{{ number_format($customer->bookings_sum_total_amount ?? 0, 2) }}</span>
                            </td>
                            <td>
                                @php
                                    $status = $customer->status ?? 'active';
                                    $statusColors = [
                                        'active' => ['bg' => '#dcfce7', 'text' => '#166534'],
                                        'inactive' => ['bg' => '#f3f4f6', 'text' => '#6b7280'],
                                        'suspended' => ['bg' => '#fee2e2', 'text' => '#991b1b'],
                                    ];
                                    $sc = $statusColors[$status] ?? $statusColors['active'];
                                @endphp
                                <span style="padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; background: {{ $sc['bg'] }}; color: {{ $sc['text'] }};">
                                    {{ ucfirst($status) }}
                                </span>
                            </td>
                            <td style="color: var(--text-muted); font-size: 0.85rem;">{{ $customer->created_at->format('M d, Y') }}</td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                <a href="{{ route('admin.customers.show', $customer->id) }}" class="action-btn edit" title="View Profile" style="background: rgba(37, 99, 235, 0.1); color: var(--primary); width: 36px; height: 36px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; text-decoration: none; transition: all 0.2s;">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('chat.show', $customer->id) }}" class="action-btn" title="Send Message" style="background: rgba(16, 185, 129, 0.1); color: #10b981; width: 36px; height: 36px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; text-decoration: none; transition: all 0.2s; margin-left: 5px;">
                                    <i class="fas fa-comment-dots"></i>
                                </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 40px; color: var(--text-muted);">
                                <i class="fas fa-users" style="font-size: 2rem; margin-bottom: 10px; display: block; opacity: 0.3;"></i>
                                No customers found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
