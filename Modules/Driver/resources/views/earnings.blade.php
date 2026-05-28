@extends('driver::layouts.master')

@section('content')
<div x-data="driverEarnings()">
    <div style="margin-bottom: 20px; text-align: center;">
        <h2 style="font-family: 'Outfit', sans-serif; color: var(--text-main); font-size: 1.2rem; margin-bottom: 5px;">Wallet Balance</h2>
        <div style="font-size: 2.5rem; font-weight: 800; color: var(--primary);">₵{{ number_format($balance ?? 0, 2) }}</div>
        <div style="font-size: 0.8rem; color: var(--text-muted);">Available for payout</div>
    </div>

    <!-- Quick Stats -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 25px;">
        <div class="card" style="margin-bottom: 0; padding: 15px;">
            <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700; margin-bottom: 5px;">This Week</div>
            <div style="font-size: 1.2rem; font-weight: 800; color: var(--success);">+₵{{ number_format($weekEarnings ?? 0, 2) }}</div>
        </div>
        <div class="card" style="margin-bottom: 0; padding: 15px;">
            <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700; margin-bottom: 5px;">This Month</div>
            <div style="font-size: 1.2rem; font-weight: 800; color: var(--text-main);">₵{{ number_format($monthEarnings ?? 0, 2) }}</div>
        </div>
    </div>

    <button class="btn btn-primary" style="margin-bottom: 25px;" {{ ($balance ?? 0) <= 0 ? 'disabled' : '' }}>
        <i class="fas fa-money-check-alt" style="margin-right: 5px;"></i> Request Payout
    </button>

    <h3 style="font-family: 'Outfit', sans-serif; font-size: 1.1rem; margin-bottom: 15px; color: var(--text-main);">Recent Transactions</h3>
    
    <div class="card" style="padding: 0; overflow: hidden;">
        @forelse($transactions ?? [] as $transaction)
            <div style="padding: 15px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; gap: 10px; align-items: center;">
                    <div style="width: 40px; height: 40px; border-radius: 50%; background: {{ $transaction->transaction_type === 'credit' ? 'rgba(16, 185, 129, 0.1)' : 'rgba(239, 68, 68, 0.1)' }}; color: {{ $transaction->transaction_type === 'credit' ? 'var(--success)' : 'var(--danger)' }}; display: flex; justify-content: center; align-items: center;">
                        <i class="fas {{ $transaction->icon }}"></i>
                    </div>
                    <div>
                        <div style="font-weight: 700; font-size: 0.9rem;">{{ $transaction->title }}</div>
                        <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $transaction->created_at->format('M d, g:i A') }}</div>
                    </div>
                </div>
                <div style="font-weight: 800; color: {{ $transaction->transaction_type === 'credit' ? 'var(--success)' : 'var(--text-main)' }};">
                    {{ $transaction->transaction_type === 'credit' ? '+' : '-' }}₵{{ number_format($transaction->amount, 2) }}
                </div>
            </div>
        @empty
            <div style="padding: 40px; text-align: center; color: var(--text-muted);">
                <i class="fas fa-history" style="font-size: 2rem; margin-bottom: 10px; opacity: 0.5;"></i>
                <p>No transactions found.</p>
            </div>
        @endforelse
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('driverEarnings', () => ({
            // State for earnings
        }));
    });
</script>
@endpush
@endsection
