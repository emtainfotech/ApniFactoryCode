@extends('layout')
@section('title', 'Admin Review: ' . $revision->revision_code)
@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <!--breadcrumb-->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Administration</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.pricing.revisions.queue') }}">Queue</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $revision->revision_code }}</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto d-flex gap-2">
                <a href="{{ route('admin.pricing.revisions.queue') }}" class="btn btn-outline-secondary px-3">
                    <i class="bx bx-arrow-back me-1"></i> Back to Queue
                </a>
                @if($revision->status === 'pending_approval')
                    <form method="POST" action="{{ route('admin.pricing.revisions.approve', $revision->id) }}" onsubmit="return confirm('Approve this price revision?');">
                        @csrf
                        <button type="submit" class="btn btn-success px-4">
                            <i class="bx bx-check-circle me-1"></i> Approve Revision
                        </button>
                    </form>
                    <button type="button" class="btn btn-danger px-4" data-bs-toggle="modal" data-bs-target="#rejectModal">
                        <i class="bx bx-x-circle me-1"></i> Reject Revision
                    </button>
                @endif
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

        <!-- Details Card -->
        <div class="card radius-10 mb-3">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 border-end">
                        <h5 class="fw-bold mb-2">{{ $revision->revision_code }}</h5>
                        <p class="mb-1"><strong>Seller:</strong> {{ $revision->company ? $revision->company->name : 'N/A' }}</p>
                        <p class="mb-1"><strong>Submitted By:</strong> {{ $revision->creator ? $revision->creator->name : 'N/A' }} ({{ $revision->creator ? $revision->creator->email : '' }})</p>
                        <p class="mb-1"><strong>Reason / Circular:</strong> {{ $revision->reason ?: 'None provided' }}</p>
                        @if($revision->rejection_reason)
                            <div class="alert alert-danger py-2 mt-2">
                                <strong>Rejection Reason:</strong> {{ $revision->rejection_reason }}
                            </div>
                        @endif
                    </div>
                    <div class="col-md-6 ps-md-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-secondary">Current Status:</span>
                            @if($revision->status === 'active')
                                <span class="badge bg-success">Active</span>
                            @elseif($revision->status === 'scheduled')
                                <span class="badge bg-warning text-dark">Scheduled</span>
                            @elseif($revision->status === 'pending_approval')
                                <span class="badge bg-info text-dark">Pending Approval</span>
                            @elseif($revision->status === 'cancelled')
                                <span class="badge bg-secondary">Cancelled</span>
                            @elseif($revision->status === 'rejected')
                                <span class="badge bg-danger">Rejected</span>
                            @endif
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-secondary">Method:</span>
                            <span class="fw-bold">{{ ucfirst($revision->revision_method) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-secondary">Adjustment Value:</span>
                            <span class="fw-bold">
                                @if($revision->revision_method === 'percentage')
                                    {{ $revision->revision_direction === 'increase' ? '+' : '-' }}{{ number_format($revision->revision_value, 2) }}%
                                @elseif($revision->revision_method === 'flat_per_unit')
                                    {{ $revision->revision_direction === 'increase' ? '+' : '-' }}₹{{ number_format($revision->revision_value, 2) }} / {{ $revision->price_basis_unit }}
                                @else
                                    Explicit SKU List
                                @endif
                            </span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-secondary">Rounding Rule:</span>
                            <span>{{ $revision->rounding_rule }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-secondary">Effective At (IST):</span>
                            <span class="fw-bold">{{ $revision->effective_at ? $revision->effective_at->setTimezone('Asia/Kolkata')->format('d M Y, h:i A') : 'Immediate' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <div class="card radius-10">
            <div class="card-header bg-transparent py-3">
                <h6 class="mb-0 font-weight-bold">Proposed SKU Prices ({{ $revision->items->count() }} items)</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Seller SKU</th>
                                <th>Product / Variant</th>
                                <th>Unit</th>
                                <th>Current Seller Price</th>
                                <th>Proposed Seller Price</th>
                                <th>Rounding Diff</th>
                                <th>Proposed Selling Price</th>
                                <th>Compatibility</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($revision->items as $item)
                                <tr class="{{ !$item->is_unit_compatible ? 'table-warning' : '' }}">
                                    <td class="fw-bold">{{ $item->seller_sku ?: 'ID#' . $item->seller_listing_id }}</td>
                                    <td>
                                        <div>{{ $item->product ? $item->product->name : 'N/A' }}</div>
                                        <div class="small text-muted">{{ $item->variant ? $item->variant->variant_sku : 'Standard' }}</div>
                                    </td>
                                    <td><span class="badge bg-light-dark text-dark">{{ $item->unit_of_measurement }}</span></td>
                                    <td>₹{{ number_format($item->old_seller_price, 2) }}</td>
                                    <td class="fw-bold text-primary">₹{{ number_format($item->new_seller_price, 2) }}</td>
                                    <td class="small text-muted">
                                        {{ $item->rounding_difference > 0 ? '+₹' . number_format($item->rounding_difference, 2) : ($item->rounding_difference < 0 ? '-₹' . number_format(abs($item->rounding_difference), 2) : '₹0.00') }}
                                    </td>
                                    <td class="fw-bold text-success">₹{{ number_format($item->new_selling_price, 2) }}</td>
                                    <td>
                                        @if($item->is_unit_compatible)
                                            <span class="badge bg-success">Eligible</span>
                                        @else
                                            <span class="badge bg-danger" title="{{ $item->error_message }}">Incompatible</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($item->status === 'applied')
                                            <span class="badge bg-success">Applied</span>
                                        @elseif($item->status === 'pending')
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        @elseif($item->status === 'skipped')
                                            <span class="badge bg-secondary">Skipped</span>
                                        @else
                                            <span class="badge bg-danger">Failed</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.pricing.revisions.reject', $revision->id) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Reject Price Revision</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Rejection Reason <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control" rows="4" required placeholder="Specify reason why this revision is rejected (e.g. exceeds agreed wholesale markup limits)..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Confirm Rejection</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
