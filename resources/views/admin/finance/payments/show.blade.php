@extends('layout')
@section('title', 'Payment Transaction #' . $transaction->payment_ref)
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
                        <li class="breadcrumb-item"><a href="{{ route('admin.finance.payments.index') }}">Payments</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $transaction->payment_ref }}</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto">
                <a href="{{ route('admin.finance.payments.index') }}" class="btn btn-outline-secondary px-3">
                    <i class="bx bx-arrow-back me-1"></i> Back to Payments
                </a>
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

        <div class="row">
            <!-- Left Column: Details -->
            <div class="col-lg-8">
                <!-- Transaction Overview -->
                <div class="card">
                    <div class="card-header bg-transparent">
                        <div class="d-flex align-items-center justify-content-between">
                            <h5 class="mb-0">
                                <i class="bx bx-receipt me-1"></i> Payment Transaction Overview
                            </h5>
                            <div>
                                @if($transaction->isSuccessful())
                                    <span class="badge bg-success fs-6">PAID / SUCCESSFUL</span>
                                @elseif($transaction->isFailed())
                                    <span class="badge bg-danger fs-6">FAILED</span>
                                @else
                                    <span class="badge bg-warning text-dark fs-6">PENDING</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <label class="text-muted small">Payment Reference</label>
                                <div class="fw-bold font-18 text-primary">{{ $transaction->payment_ref }}</div>
                            </div>
                            <div class="col-sm-6">
                                <label class="text-muted small">Payable Amount</label>
                                <div class="fw-bold font-18 text-dark">₹{{ number_format($transaction->amount, 2) }} {{ $transaction->currency }}</div>
                            </div>
                            <div class="col-sm-6">
                                <label class="text-muted small">Gateway</label>
                                <div><span class="badge bg-light-primary text-primary text-uppercase fs-7">{{ $transaction->gateway_name }}</span></div>
                            </div>
                            <div class="col-sm-6">
                                <label class="text-muted small">Gateway Order ID</label>
                                <div class="fw-bold font-monospace">{{ $transaction->gateway_order_id ?? 'N/A' }}</div>
                            </div>
                            <div class="col-sm-6">
                                <label class="text-muted small">Gateway Payment ID</label>
                                <div class="fw-bold font-monospace">{{ $transaction->gateway_payment_id ?? $transaction->txnid ?? 'N/A' }}</div>
                            </div>
                            <div class="col-sm-6">
                                <label class="text-muted small">Gateway Event ID</label>
                                <div class="font-monospace text-muted">{{ $transaction->gateway_event_id ?? 'N/A' }}</div>
                            </div>
                            <div class="col-sm-6">
                                <label class="text-muted small">Initiated At</label>
                                <div>{{ $transaction->initiated_at ? $transaction->initiated_at->format('d M Y, h:i:s A') : 'N/A' }}</div>
                            </div>
                            <div class="col-sm-6">
                                <label class="text-muted small">Verified At</label>
                                <div>{{ $transaction->verified_at ? $transaction->verified_at->format('d M Y, h:i:s A') : 'Not yet verified' }}</div>
                            </div>
                        </div>

                        @if($transaction->failure_reason)
                            <div class="alert alert-danger mt-3 mb-0">
                                <strong><i class="bx bx-error-circle"></i> Failure Code: {{ $transaction->failure_code }}</strong><br>
                                {{ $transaction->failure_reason }}
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Related Order and Child Fulfilment Orders -->
                <div class="card">
                    <div class="card-header bg-transparent">
                        <h6 class="mb-0"><i class="bx bx-package me-1"></i> Order & Fulfilment Hierarchy</h6>
                    </div>
                    <div class="card-body">
                        @if($transaction->order)
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="fw-bold">
                                        Parent Order #{{ $transaction->order->orderno }}
                                    </div>
                                    <div>
                                        <span class="badge {{ $transaction->order->isPaid() ? 'bg-success' : 'bg-warning text-dark' }}">
                                            Payment: {{ $transaction->order->payment_status ?? 'Pending' }}
                                        </span>
                                    </div>
                                </div>
                                <p class="text-muted small mb-1">
                                    Buyer: {{ $transaction->customer?->name ?? 'Guest' }} ({{ $transaction->customer?->mobile ?? 'N/A' }})
                                </p>
                                <p class="text-muted small mb-0">
                                    Total Order Value: ₹{{ number_format($transaction->order->grandtotal, 2) }}
                                </p>
                            </div>

                            @if($transaction->order->childOrders && $transaction->order->childOrders->count() > 0)
                                <h6 class="font-14 mt-4 mb-2">Child Seller Fulfilment Orders ({{ $transaction->order->childOrders->count() }})</h6>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Child Order #</th>
                                                <th>Seller ID</th>
                                                <th>Amount</th>
                                                <th>Fulfilment Status</th>
                                                <th>Payment Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($transaction->order->childOrders as $child)
                                                <tr>
                                                    <td>#{{ $child->orderno }}</td>
                                                    <td>Seller #{{ $child->user_id }}</td>
                                                    <td>₹{{ number_format($child->grandtotal, 2) }}</td>
                                                    <td>
                                                        <span class="badge bg-light-secondary text-secondary">
                                                            {{ $child->orderstatus ?? 'pending_acceptance' }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge {{ $child->isPaid() ? 'bg-success' : 'bg-warning text-dark' }}">
                                                            {{ $child->payment_status ?? 'Pending' }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        @else
                            <p class="text-muted mb-0">Order information not found.</p>
                        @endif
                    </div>
                </div>

                <!-- Audit Trail -->
                <div class="card">
                    <div class="card-header bg-transparent">
                        <h6 class="mb-0"><i class="bx bx-history me-1"></i> Audit Trail & Cryptographic Verification Log</h6>
                    </div>
                    <div class="card-body">
                        @forelse($transaction->auditLogs as $log)
                            <div class="d-flex align-items-start border-bottom pb-3 mb-3">
                                <div class="font-24 me-3 text-primary">
                                    <i class="bx bx-check-circle"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <div class="fw-bold text-dark">{{ ucwords(str_replace('_', ' ', $log->action)) }}</div>
                                        <small class="text-muted">{{ $log->created_at->format('d M Y, h:i:s A') }}</small>
                                    </div>
                                    <div class="small text-muted mb-2">
                                        Actor: <span class="fw-semibold">{{ $log->actor_name }}</span> ({{ $log->actor_role }}) | IP: {{ $log->ip_address ?? '127.0.0.1' }}
                                    </div>
                                    @if($log->previous_status || $log->new_status)
                                        <div class="small mb-2">
                                            Status: <span class="badge bg-secondary">{{ $log->previous_status ?? 'none' }}</span>
                                            <i class="bx bx-right-arrow-alt"></i>
                                            <span class="badge bg-primary">{{ $log->new_status }}</span>
                                        </div>
                                    @endif
                                    @if($log->payload)
                                        <pre class="bg-light p-2 rounded small font-monospace mb-0" style="max-height: 120px; overflow-y: auto;">{{ json_encode($log->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="text-muted mb-0">No audit logs recorded for this transaction.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Right Column: Status & Reconciliation Override -->
            <div class="col-lg-4">
                <!-- Status Summary -->
                <div class="card">
                    <div class="card-header bg-transparent">
                        <h6 class="mb-0"><i class="bx bx-shield-quarter me-1"></i> Status Indicators</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="text-muted small d-block">Verification Status</label>
                            @if($transaction->verification_status === 'verified')
                                <span class="badge bg-success fs-6"><i class="bx bx-check-circle"></i> VERIFIED</span>
                            @elseif($transaction->verification_status === 'tampered')
                                <span class="badge bg-danger fs-6"><i class="bx bx-shield-x"></i> TAMPERED</span>
                            @else
                                <span class="badge bg-secondary fs-6">UNVERIFIED</span>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label class="text-muted small d-block">Reconciliation Status</label>
                            @if($transaction->reconciliation_status === 'matched')
                                <span class="badge bg-success fs-6"><i class="bx bx-check"></i> MATCHED</span>
                            @elseif($transaction->reconciliation_status === 'mismatch')
                                <span class="badge bg-danger fs-6"><i class="bx bx-error"></i> MISMATCH</span>
                            @elseif($transaction->reconciliation_status === 'manual_review')
                                <span class="badge bg-warning text-dark fs-6"><i class="bx bx-search"></i> REVIEW</span>
                            @else
                                <span class="badge bg-secondary fs-6">PENDING</span>
                            @endif
                        </div>

                        @if($transaction->reconciled_at)
                            <div class="mb-3">
                                <label class="text-muted small d-block">Reconciled At</label>
                                <div>{{ $transaction->reconciled_at->format('d M Y, h:i A') }}</div>
                            </div>
                        @endif

                        @if($transaction->reconciliation_notes)
                            <div class="mb-3">
                                <label class="text-muted small d-block">Reconciliation Notes</label>
                                <div class="p-2 bg-light rounded text-dark small">{{ $transaction->reconciliation_notes }}</div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Manual Reconciliation Form -->
                <div class="card">
                    <div class="card-header bg-transparent">
                        <h6 class="mb-0"><i class="bx bx-edit-alt me-1"></i> Manual Reconciliation Override</h6>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.finance.payments.reconcile', $transaction->id) }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-bold">Reconciliation Decision <span class="text-danger">*</span></label>
                                <select name="reconciliation_status" class="form-select" required>
                                    <option value="matched" {{ $transaction->reconciliation_status == 'matched' ? 'selected' : '' }}>Matched (Verify & Approve)</option>
                                    <option value="mismatch" {{ $transaction->reconciliation_status == 'mismatch' ? 'selected' : '' }}>Mismatch (Discrepancy)</option>
                                    <option value="manual_review" {{ $transaction->reconciliation_status == 'manual_review' ? 'selected' : '' }}>Manual Review (Under Bank Inquiry)</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Audit Notes / Bank Reference <span class="text-danger">*</span></label>
                                <textarea name="notes" class="form-control" rows="4" placeholder="Mandatory notes explaining bank statement verification..." required>{{ $transaction->reconciliation_notes }}</textarea>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bx bx-save me-1"></i> Update Reconciliation
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

