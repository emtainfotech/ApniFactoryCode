@extends('layout')
@section('title', 'Price Revision: ' . $revision->revision_code)
@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <!--breadcrumb-->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Pricing Engine</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="{{ route('seller.pricing.revisions.index') }}">Revisions</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $revision->revision_code }}</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto d-flex gap-2">
                <a href="{{ route('seller.pricing.revisions.index') }}" class="btn btn-outline-secondary px-3">
                    <i class="bx bx-arrow-back me-1"></i> Back
                </a>
                @if(in_array($revision->status, ['draft', 'pending_approval', 'scheduled']))
                    <form method="POST" action="{{ route('seller.pricing.revisions.cancel', $revision->id) }}" onsubmit="return confirm('Are you sure you want to cancel this scheduled price revision?');">
                        @csrf
                        <button type="submit" class="btn btn-danger px-3">
                            <i class="bx bx-x-circle me-1"></i> Cancel Revision
                        </button>
                    </form>
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

        <!-- Header Card -->
        <div class="card radius-10 mb-3">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-7">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <h4 class="mb-0 fw-bold">{{ $revision->revision_code }}</h4>
                            @if($revision->status === 'active')
                                <span class="badge bg-success fs-6">Active (Applied)</span>
                            @elseif($revision->status === 'scheduled')
                                <span class="badge bg-warning text-dark fs-6">Scheduled</span>
                            @elseif($revision->status === 'pending_approval')
                                <span class="badge bg-info text-dark fs-6">Awaiting Admin Approval</span>
                            @elseif($revision->status === 'cancelled')
                                <span class="badge bg-secondary fs-6">Cancelled</span>
                            @elseif($revision->status === 'rejected')
                                <span class="badge bg-danger fs-6">Rejected</span>
                            @endif
                        </div>
                        <p class="text-secondary mb-1"><strong>Reason:</strong> {{ $revision->reason ?: 'No circular reference provided.' }}</p>
                        @if($revision->rejection_reason)
                            <div class="alert alert-danger py-2 mb-1">
                                <strong>Rejection Reason:</strong> {{ $revision->rejection_reason }}
                            </div>
                        @endif
                    </div>
                    <div class="col-md-5 text-md-end border-start">
                        <div class="small text-secondary">
                            <div><strong>Initiator:</strong> {{ $revision->creator ? $revision->creator->name : 'System' }}</div>
                            @if($revision->approver)
                                <div><strong>Approver:</strong> {{ $revision->approver->name }}</div>
                            @endif
                            <div><strong>Effective (IST):</strong> {{ $revision->effective_at ? $revision->effective_at->setTimezone('Asia/Kolkata')->format('d M Y, h:i A') : 'Immediate' }}</div>
                            @if($revision->activated_at)
                                <div class="text-success"><strong>Activated At:</strong> {{ $revision->activated_at->setTimezone('Asia/Kolkata')->format('d M Y, h:i A') }}</div>
                            @endif
                            @if($revision->cancelled_at)
                                <div class="text-danger"><strong>Cancelled At:</strong> {{ $revision->cancelled_at->setTimezone('Asia/Kolkata')->format('d M Y, h:i A') }}</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Metric Cards -->
        <div class="row row-cols-1 row-cols-md-4 mb-3">
            <div class="col">
                <div class="card radius-10 border-start border-0 border-3 border-info">
                    <div class="card-body">
                        <p class="mb-0 text-secondary">Total Evaluated</p>
                        <h4 class="my-1 text-info">{{ $revision->total_skus }}</h4>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card radius-10 border-start border-0 border-3 border-success">
                    <div class="card-body">
                        <p class="mb-0 text-secondary">Eligible / Applied</p>
                        <h4 class="my-1 text-success">{{ $revision->affected_skus_count }}</h4>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card radius-10 border-start border-0 border-3 border-warning">
                    <div class="card-body">
                        <p class="mb-0 text-secondary">Incompatible Units</p>
                        <h4 class="my-1 text-warning">{{ $revision->incompatible_skus_count }}</h4>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card radius-10 border-start border-0 border-3 border-danger">
                    <div class="card-body">
                        <p class="mb-0 text-secondary">Failed / Skipped</p>
                        <h4 class="my-1 text-danger">{{ $revision->failed_skus_count }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Affected SKU Items Table -->
        <div class="card radius-10">
            <div class="card-header bg-transparent py-3">
                <h6 class="mb-0 font-weight-bold">Affected SKU Listings ({{ $revision->items->count() }} items)</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Seller SKU</th>
                                <th>Product / Variant</th>
                                <th>Unit</th>
                                <th>Old Seller Price</th>
                                <th>New Seller Price</th>
                                <th>Rounding Diff</th>
                                <th>New Selling Price</th>
                                <th>Status</th>
                                <th>Notes / Errors</th>
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
                                    <td>
                                        @if($item->error_message)
                                            <small class="text-danger">{{ $item->error_message }}</small>
                                        @else
                                            <small class="text-success">Verified compatible</small>
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
@endsection

