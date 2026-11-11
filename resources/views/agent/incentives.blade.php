@extends('layout')
@section('title', 'My Eligible Orders & Incentive Progress')
@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Field Agent Portal</div>
            <div class="ps-3"><ol class="breadcrumb mb-0 p-0"><li class="breadcrumb-item active">Eligible Orders (0/3..3/3) &amp; Incentive Status</li></ol></div>
        </div>

        @if(!($incentiveSummary['financials_disclosed'] ?? false))
        <div class="alert alert-info py-2 small text-dark">
            <strong>Policy Notice:</strong> {{ $incentiveSummary['disclosure_notice'] }} Confidential seller pricing and financial settlement records remain restricted.
        </div>
        @endif

        <div class="row g-3 mb-3">
            <div class="col-md-3"><div class="card mb-0 border-start border-4 border-success"><div class="card-body py-2"><small class="text-muted">Counted Eligible Orders</small><h4 class="mb-0 text-success">{{ $incentiveSummary['eligible_orders_count'] }}</h4></div></div></div>
            <div class="col-md-3"><div class="card mb-0 border-start border-4 border-warning"><div class="card-body py-2"><small class="text-muted">Pending Qualification</small><h4 class="mb-0 text-warning">{{ $incentiveSummary['pending_orders_count'] }}</h4></div></div></div>
            <div class="col-md-3"><div class="card mb-0"><div class="card-body py-2"><small class="text-muted">Approved Incentives</small><h5 class="mb-0">{{ $incentiveSummary['financials_disclosed'] ? '₹'.number_format($incentiveSummary['approved_total'], 2) : 'Pending Approval' }}</h5></div></div></div>
            <div class="col-md-3"><div class="card mb-0"><div class="card-body py-2"><small class="text-muted">Paid Incentives</small><h5 class="mb-0">{{ $incentiveSummary['financials_disclosed'] ? '₹'.number_format($incentiveSummary['paid_total'], 2) : 'Pending Approval' }}</h5></div></div></div>
        </div>

        <div class="card">
            <div class="card-header bg-transparent"><h6 class="mb-0 fw-bold">Tracked Orders for My Assigned Buyers ({{ $trackedOrders->total() }})</h6></div>
            <div class="card-body p-0">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>Parent Order</th><th>Assigned Buyer</th><th>Sellers</th><th>Slot</th><th>Eligibility Status</th><th>Incentive Status</th></tr>
                    </thead>
                    <tbody>
                        @forelse($trackedOrders as $ord)
                        <tr>
                            <td class="fw-bold">{{ $ord->orderno }}</td>
                            <td>{{ $ord->customer?->name }}</td>
                            <td>{{ $ord->seller_count }}</td>
                            <td>{!! $ord->sequence_number ? "<span class='badge bg-dark'>{$ord->sequence_number}/3</span>" : '-' !!}</td>
                            <td><span class="badge bg-info text-dark">{{ ucwords(str_replace('_', ' ', $ord->eligibility_status)) }}</span></td>
                            <td class="small">{{ ucwords(str_replace('_', ' ', $ord->incentive_generation_status)) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center py-4 text-muted">No tracked orders yet for your assigned buyers.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

