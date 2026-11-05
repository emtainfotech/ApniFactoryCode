@extends('layout')
@section('title', 'Order Commission Breakdown: #' . $orderId)
@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <!--breadcrumb-->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Administration</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.commissions.rules.index') }}">Commissions</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Order #{{ $orderId }} Breakdown</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto">
                <a href="{{ route('admin.commissions.rules.index') }}" class="btn btn-outline-secondary px-3">
                    <i class="bx bx-arrow-back me-1"></i> Back
                </a>
            </div>
        </div>

        <div class="card radius-10">
            <div class="card-header bg-transparent py-3">
                <h6 class="mb-0 font-weight-bold">Immutable Commission Snapshots for Order #{{ $orderId }}</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Order Detail / SKU</th>
                                <th>Seller Organization</th>
                                <th>Applicable Rule</th>
                                <th>Base Taxable Amount</th>
                                <th>Rate</th>
                                <th>Commission Fee</th>
                                <th>GST on Fee (18%)</th>
                                <th>Total Platform Fee</th>
                                <th>Seller Payable</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($snapshots as $snap)
                                <tr>
                                    <td>
                                        <div class="fw-bold">{{ $snap->listing ? $snap->listing->seller_sku : 'SKU#' . $snap->seller_listing_id }}</div>
                                        <div class="small text-muted">{{ $snap->product ? $snap->product->name : 'N/A' }} (Qty: {{ $snap->quantity }})</div>
                                    </td>
                                    <td>{{ $snap->company ? $snap->company->name : 'N/A' }}</td>
                                    <td>
                                        <span class="badge bg-light-dark text-dark">{{ $snap->commission_rule_code ?: 'Default Rule' }}</span>
                                        <div class="small text-muted">{{ ucfirst($snap->rule_scope_type) }}</div>
                                    </td>
                                    <td>₹{{ number_format($snap->base_amount, 2) }}</td>
                                    <td>{{ number_format($snap->commission_percentage, 2) }}%</td>
                                    <td class="fw-bold text-danger">₹{{ number_format($snap->commission_amount, 2) }}</td>
                                    <td class="small text-muted">₹{{ number_format($snap->gst_amount, 2) }}</td>
                                    <td class="fw-bold text-dark">₹{{ number_format($snap->total_platform_fee, 2) }}</td>
                                    <td class="fw-bold text-success">₹{{ number_format($snap->seller_payable, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4 text-muted">No commission snapshot records found for this order.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
