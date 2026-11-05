@extends('layout')
@section('title', 'Payment Gateway & Reconciliation')
@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <!-- Breadcrumb -->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Finance</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.commissions.rules.index') }}"><i class="bx bx-home-alt"></i></a></li>
                        <li class="breadcrumb-item active" aria-current="page">Payment Reconciliation</li>
                    </ol>
                </nav>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success border-0 bg-success alert-dismissible fade show py-2">
                <div class="d-flex align-items-center">
                    <div class="font-35 text-white"><i class="bx bxs-check-circle"></i></div>
                    <div class="ms-3 text-white">{{ session('success') }}</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger border-0 bg-danger alert-dismissible fade show py-2">
                <div class="d-flex align-items-center">
                    <div class="font-35 text-white"><i class="bx bxs-message-square-x"></i></div>
                    <div class="ms-3 text-white">{{ session('error') }}</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Metric Cards -->
        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-4 mb-3">
            <div class="col">
                <div class="card radius-10 border-start border-0 border-4 border-info">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-secondary">Captured Volume</p>
                                <h4 class="my-1 text-info">₹{{ number_format($metrics['total_volume'], 2) }}</h4>
                                <p class="mb-0 font-13">{{ $metrics['total_count'] }} Total Transactions</p>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-light-info text-info ms-auto">
                                <i class="bx bx-wallet"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card radius-10 border-start border-0 border-4 border-success">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-secondary">Matched / Reconciled</p>
                                <h4 class="my-1 text-success">{{ $metrics['matched_count'] }}</h4>
                                <p class="mb-0 font-13">Authoritative match</p>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-light-success text-success ms-auto">
                                <i class="bx bx-check-shield"></i>
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
                                <p class="mb-0 text-secondary">Mismatched / Tampered</p>
                                <h4 class="my-1 text-danger">{{ $metrics['mismatch_count'] + $metrics['tampered_count'] }}</h4>
                                <p class="mb-0 font-13">{{ $metrics['tampered_count'] }} Signature Failures</p>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-light-danger text-danger ms-auto">
                                <i class="bx bx-error-alt"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card radius-10 border-start border-0 border-4 border-warning">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <p class="mb-0 text-secondary">Pending / Review</p>
                                <h4 class="my-1 text-warning">{{ $metrics['pending_count'] + $metrics['manual_review_count'] }}</h4>
                                <p class="mb-0 font-13">{{ $metrics['manual_review_count'] }} Under Investigation</p>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-light-warning text-warning ms-auto">
                                <i class="bx bx-time-five"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters Form -->
        <div class="card">
            <div class="card-header bg-transparent">
                <div class="d-flex align-items-center">
                    <div>
                        <h6 class="mb-0"><i class="bx bx-filter-alt me-1"></i> Filter Transactions</h6>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('admin.finance.payments.index') }}" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" class="form-control" placeholder="Ref / Order / Gateway ID..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Gateway</label>
                        <select name="gateway" class="form-select">
                            <option value="">All Gateways</option>
                            <option value="razorpay" {{ request('gateway') == 'razorpay' ? 'selected' : '' }}>Razorpay</option>
                            <option value="payu" {{ request('gateway') == 'payu' ? 'selected' : '' }}>PayU</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Payment Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="successful" {{ request('status') == 'successful' ? 'selected' : '' }}>Successful</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Reconciliation</label>
                        <select name="reconciliation_status" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="matched" {{ request('reconciliation_status') == 'matched' ? 'selected' : '' }}>Matched</option>
                            <option value="mismatch" {{ request('reconciliation_status') == 'mismatch' ? 'selected' : '' }}>Mismatch</option>
                            <option value="manual_review" {{ request('reconciliation_status') == 'manual_review' ? 'selected' : '' }}>Manual Review</option>
                            <option value="pending" {{ request('reconciliation_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary px-3"><i class="bx bx-search me-1"></i> Filter</button>
                        <a href="{{ route('admin.finance.payments.index') }}" class="btn btn-outline-secondary px-3">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Transactions Table -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table align-middle mb-0 table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Payment Ref</th>
                                <th>Order #</th>
                                <th>Customer / Buyer</th>
                                <th>Gateway</th>
                                <th>Amount</th>
                                <th>Verification</th>
                                <th>Reconciliation</th>
                                <th>Status</th>
                                <th>Initiated At</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $txn)
                                <tr>
                                    <td>
                                        <div class="fw-bold">{{ $txn->payment_ref }}</div>
                                        @if($txn->gateway_payment_id)
                                            <small class="text-muted d-block">{{ $txn->gateway_payment_id }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.finance.payments.show', $txn->id) }}" class="text-primary fw-bold">
                                            #{{ $txn->order_no }}
                                        </a>
                                    </td>
                                    <td>
                                        <div>{{ $txn->customer?->name ?? 'Guest Buyer' }}</div>
                                        <small class="text-muted">{{ $txn->customer?->mobile ?? '' }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-light-primary text-primary border border-primary px-2 py-1 text-uppercase">
                                            {{ $txn->gateway_name }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">₹{{ number_format($txn->amount, 2) }}</div>
                                        <small class="text-muted">{{ $txn->currency }}</small>
                                    </td>
                                    <td>
                                        @if($txn->verification_status === 'verified')
                                            <span class="badge bg-success"><i class="bx bx-check"></i> Verified</span>
                                        @elseif($txn->verification_status === 'tampered')
                                            <span class="badge bg-danger"><i class="bx bx-shield-x"></i> Tampered</span>
                                        @elseif($txn->verification_status === 'failed')
                                            <span class="badge bg-warning text-dark"><i class="bx bx-x"></i> Failed</span>
                                        @else
                                            <span class="badge bg-secondary">Unverified</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($txn->reconciliation_status === 'matched')
                                            <span class="badge bg-success">Matched</span>
                                        @elseif($txn->reconciliation_status === 'mismatch')
                                            <span class="badge bg-danger">Mismatch</span>
                                        @elseif($txn->reconciliation_status === 'manual_review')
                                            <span class="badge bg-warning text-dark">Review Required</span>
                                        @else
                                            <span class="badge bg-secondary">Pending</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($txn->isSuccessful())
                                            <span class="badge bg-success">Paid</span>
                                        @elseif($txn->isFailed())
                                            <span class="badge bg-danger">Failed</span>
                                        @else
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div>{{ $txn->initiated_at ? $txn->initiated_at->format('d M Y, h:i A') : $txn->created_at->format('d M Y, h:i A') }}</div>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group">
                                            <a href="{{ route('admin.finance.payments.show', $txn->id) }}" class="btn btn-sm btn-outline-primary" title="View Audit Trail">
                                                <i class="bx bx-show"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#reconcileModal-{{ $txn->id }}" title="Reconcile">
                                                <i class="bx bx-check-circle"></i>
                                            </button>
                                        </div>

                                        <!-- Quick Reconcile Modal -->
                                        <div class="modal fade" id="reconcileModal-{{ $txn->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered text-start">
                                                <div class="modal-content">
                                                    <form method="POST" action="{{ route('admin.finance.payments.reconcile', $txn->id) }}">
                                                        @csrf
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Manual Reconciliation: {{ $txn->payment_ref }}</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">Order Amount</label>
                                                                <div class="form-control-plaintext">₹{{ number_format($txn->amount, 2) }} ({{ $txn->gateway_name }})</div>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">Reconciliation Decision <span class="text-danger">*</span></label>
                                                                <select name="reconciliation_status" class="form-select" required>
                                                                    <option value="matched" {{ $txn->reconciliation_status == 'matched' ? 'selected' : '' }}>Matched (Verify & Approve)</option>
                                                                    <option value="mismatch" {{ $txn->reconciliation_status == 'mismatch' ? 'selected' : '' }}>Mismatch (Flag discrepancy)</option>
                                                                    <option value="manual_review" {{ $txn->reconciliation_status == 'manual_review' ? 'selected' : '' }}>Manual Review (Under bank investigation)</option>
                                                                </select>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">Audit Notes / Bank Reference <span class="text-danger">*</span></label>
                                                                <textarea name="notes" class="form-control" rows="3" placeholder="Provide bank statement reference or reason for reconciliation override..." required>{{ $txn->reconciliation_notes }}</textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-primary">Save Reconciliation</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center py-4 text-muted">
                                        <i class="bx bx-receipt fs-1 d-block mb-2"></i>
                                        No payment transactions found matching the selected criteria.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $transactions->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

