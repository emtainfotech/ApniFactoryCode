@extends('layout')
@section('title', 'Financial Ledger')
@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <!-- Breadcrumb -->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Finance</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.finance.payments.index') }}"><i class="bx bx-home-alt"></i></a></li>
                        <li class="breadcrumb-item active" aria-current="page">Financial Ledger</li>
                    </ol>
                </nav>
            </div>
        </div>

        <!-- Metric Cards -->
        <div class="row row-cols-1 row-cols-md-3 mb-3">
            <div class="col">
                <div class="card radius-10 border-start border-0 border-4 border-success">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-secondary">Total Credits</p>
                                <h4 class="my-1 text-success">₹{{ number_format($metrics['total_credits'], 2) }}</h4>
                                <p class="mb-0 font-13">Gross payable credits</p>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-light-success text-success ms-auto">
                                <i class="bx bx-trending-up"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card radius-10 border-start border-0 border-4 border-danger">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-secondary">Total Debits</p>
                                <h4 class="my-1 text-danger">₹{{ number_format($metrics['total_debits'], 2) }}</h4>
                                <p class="mb-0 font-13">Commissions & payouts</p>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-light-danger text-danger ms-auto">
                                <i class="bx bx-trending-down"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card radius-10 border-start border-0 border-4 border-info">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-secondary">Total Ledger Entries</p>
                                <h4 class="my-1 text-info">{{ $metrics['entry_count'] }}</h4>
                                <p class="mb-0 font-13">Double-entry audit log</p>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-light-info text-info ms-auto">
                                <i class="bx bx-book-content"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter -->
        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.finance.ledger.index') }}" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Entry Type</label>
                        <select name="entry_type" class="form-select">
                            <option value="">All Entry Types</option>
                            <option value="seller_payable" {{ request('entry_type') == 'seller_payable' ? 'selected' : '' }}>Seller Payable</option>
                            <option value="platform_commission" {{ request('entry_type') == 'platform_commission' ? 'selected' : '' }}>Platform Commission</option>
                            <option value="gst_on_commission" {{ request('entry_type') == 'gst_on_commission' ? 'selected' : '' }}>GST on Commission</option>
                            <option value="payout_released" {{ request('entry_type') == 'payout_released' ? 'selected' : '' }}>Payout Released</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Direction</label>
                        <select name="direction" class="form-select">
                            <option value="">All Directions</option>
                            <option value="credit" {{ request('direction') == 'credit' ? 'selected' : '' }}>Credit (+)</option>
                            <option value="debit" {{ request('direction') == 'debit' ? 'selected' : '' }}>Debit (-)</option>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary px-3"><i class="bx bx-search me-1"></i> Filter</button>
                        <a href="{{ route('admin.finance.ledger.index') }}" class="btn btn-outline-secondary px-3">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Table -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table align-middle mb-0 table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Entry Ref</th>
                                <th>Timestamp</th>
                                <th>Seller</th>
                                <th>Entry Type</th>
                                <th>Order #</th>
                                <th>Direction</th>
                                <th>Amount</th>
                                <th>Running Balance</th>
                                <th>Source Process</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($entries as $entry)
                                <tr>
                                    <td>
                                        <div class="fw-bold font-monospace text-primary">{{ $entry->entry_ref }}</div>
                                    </td>
                                    <td>
                                        <small>{{ $entry->created_at->format('d M Y, h:i A') }}</small>
                                    </td>
                                    <td>
                                        <div>{{ $entry->company?->name ?? $entry->seller?->name ?? 'Seller #' . $entry->seller_user_id }}</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light-secondary text-secondary text-capitalize">{{ str_replace('_', ' ', $entry->entry_type) }}</span>
                                    </td>
                                    <td>
                                        <div class="fw-bold">#{{ $entry->order?->orderno ?? $entry->order_id ?? 'N/A' }}</div>
                                    </td>
                                    <td>
                                        @if($entry->direction === 'credit')
                                            <span class="badge bg-success"><i class="bx bx-plus"></i> Credit</span>
                                        @else
                                            <span class="badge bg-danger"><i class="bx bx-minus"></i> Debit</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="fw-bold {{ $entry->direction === 'credit' ? 'text-success' : 'text-danger' }}">
                                            {{ $entry->direction === 'credit' ? '+' : '-' }}₹{{ number_format($entry->amount, 2) }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-bold font-monospace">₹{{ number_format($entry->running_balance, 2) }}</div>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $entry->source_process }}</small>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4 text-muted">
                                        No financial ledger records found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $entries->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

